<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Notifications\NewCustomerRegisteredNotification;
use App\Services\AuditService;
use App\Services\LockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function __construct(
        private LockService $lockService,
        private AuditService $auditService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Lead::with(['project', 'customer', 'channelPartner', 'assignedTo']);
        $user = $request->user();
        if ($user->isChannelPartner()) {
            $query->where('channel_partner_id', $user->channelPartner->id);
        } elseif ($user->isSalesExec()) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->builder_firm_id && ! $user->isSuperAdmin()) {
            $query->whereHas('project', fn ($q) => $q->where('builder_firm_id', $user->builder_firm_id));
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $statuses = array_map('trim', explode(',', $request->status));
            $query->whereIn('status', $statuses);
        }
        if ($request->filled('channel_partner_id')) {
            $query->where('channel_partner_id', $request->channel_partner_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$s}%")->orWhere('mobile', 'like', "%{$s}%"));
            });
        }
        if ($request->filled('lock_status')) {
            if ($request->lock_status === 'active') {
                $query->whereHas('leadLocks', fn ($q) => $q->where('status', 'active'));
            } elseif ($request->lock_status === 'expired') {
                $query->whereHas('leadLocks', fn ($q) => $q->where('status', 'expired'));
            } elseif ($request->lock_status === 'none') {
                $query->whereDoesntHave('leadLocks', fn ($q) => $q->where('status', 'active'));
            }
        }
        $sort = $request->input('sort', 'created_at');
        $dir = $request->input('dir', 'desc');
        $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        $leads = $query->paginate($request->input('per_page', 20));
        return response()->json([
            'data' => $leads->items(),
            'meta' => [
                'total' => $leads->total(),
                'page' => $leads->currentPage(),
                'per_page' => $leads->perPage(),
                'last_page' => $leads->lastPage(),
            ],
            'message' => 'Success',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Lead::class);
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0',
            'property_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'source' => 'nullable|in:channel_partner,direct,online,referral',
        ]);
        $project = \App\Models\Project::findOrFail($validated['project_id']);
        $builderFirmId = $project->builder_firm_id;
        $user = $request->user();
        if ($user->isChannelPartner()) {
            if (! $user->channelPartner->isApprovedForBuilder($builderFirmId)) {
                return response()->json(['error' => 'You are not approved for this builder.'], 403);
            }
        } elseif ($user->builder_firm_id && (int) $user->builder_firm_id !== (int) $builderFirmId) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $builder = $project->builderFirm;
        $builder->load('plan');
        $leadCount = Lead::whereHas('project', fn ($q) => $q->where('builder_firm_id', $builderFirmId))->count();
        if ($leadCount >= $builder->getMaxLeads()) {
            return response()->json([
                'error' => 'Lead limit reached for this tenant. Plan allows ' . $builder->getMaxLeads() . ' leads.',
            ], 422);
        }

        $normalizedMobile = Customer::normalizeMobile($validated['mobile']);
        $currentCpId = $request->user()->isChannelPartner() ? $request->user()->channelPartner->id : null;
        $lockCheck = $this->lockService->checkLockAndDuplicate(
            (int) $validated['project_id'],
            $normalizedMobile,
            $currentCpId
        );
        if (! $lockCheck['allowed']) {
            return response()->json([
                'error' => $lockCheck['message'],
                'lock_expires_at' => $lockCheck['lock_expires_at'] ?? null,
                'days_remaining' => $lockCheck['days_remaining'] ?? null,
            ], 422);
        }

        $source = $lockCheck['is_revisit']
            ? Lead::SOURCE_REVISIT
            : ($validated['source'] ?? Lead::SOURCE_CHANNEL_PARTNER);

        try {
            $lead = DB::transaction(function () use ($request, $validated, $normalizedMobile, $project, $source) {
                $customer = Customer::firstOrCreate(
                    ['mobile' => $normalizedMobile],
                    [
                        'name' => $validated['name'],
                        'email' => $validated['email'] ?? null,
                        'city' => $validated['city'] ?? null,
                    ]
                );
                if ($customer->wasRecentlyCreated === false) {
                    $customer->update([
                        'name' => $validated['name'],
                        'email' => $validated['email'] ?? $customer->email,
                        'city' => $validated['city'] ?? $customer->city,
                    ]);
                }
                $lead = Lead::create([
                    'project_id' => $validated['project_id'],
                    'customer_id' => $customer->id,
                    'channel_partner_id' => $request->user()->isChannelPartner() ? $request->user()->channelPartner->id : null,
                    'created_by' => $request->user()->id,
                    'status' => Lead::STATUS_NEW,
                    'source' => $source,
                    'budget' => $validated['budget'] ?? null,
                    'property_type' => $validated['property_type'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
                \App\Models\LeadActivity::create([
                    'lead_id' => $lead->id,
                    'created_by' => $request->user()->id,
                    'type' => 'lead_created',
                    'payload' => [],
                    'created_at' => now(),
                ]);
                $this->auditService->log(
                    $request->user()->id,
                    'lead_created',
                    'Lead',
                    $lead->id,
                    null,
                    $lead->toArray(),
                    null,
                    $request
                );
                return $lead;
            });
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate') || $e->getCode() === '23000') {
                return response()->json(['error' => 'Conflict. This lead may already exist.'], 409);
            }
            throw $e;
        }

        $lead->load(['project', 'customer', 'channelPartner']);
        if ($lead->channel_partner_id) {
            $lead->load(['channelPartner.user', 'project', 'customer']);
            $builder = $project->builderFirm;
            $cpUser = $lead->channelPartner?->user;
            if ($cpUser && $cpUser->email) {
                $cpUser->notify(new NewCustomerRegisteredNotification($builder, $lead));
            }
        }
        return response()->json(['data' => $lead, 'message' => 'Success'], 201);
    }

    public function show(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);
        $lead->load(['project', 'customer', 'channelPartner', 'assignedTo', 'visits', 'leadActivities.createdBy', 'leadLocks']);
        return response()->json(['data' => $lead, 'message' => 'Success']);
    }

    public function updateStatus(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('updateStatus', $lead);
        $validated = $request->validate(['status' => 'required|in:new,contacted,visit_scheduled,visit_done,negotiation,booked,lost']);
        $oldStatus = $lead->status;
        $lead->update(['status' => $validated['status']]);
        \App\Models\LeadActivity::create([
            'lead_id' => $lead->id,
            'created_by' => $request->user()->id,
            'type' => 'status_changed',
            'payload' => ['from' => $oldStatus, 'to' => $validated['status']],
            'created_at' => now(),
        ]);
        $this->auditService->log($request->user()->id, 'status_changed', 'Lead', $lead->id, ['status' => $oldStatus], ['status' => $validated['status']], null, $request);
        return response()->json(['data' => $lead->fresh(), 'message' => 'Success']);
    }

    public function assign(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('assign', $lead);
        $validated = $request->validate(['assigned_to' => 'required|exists:users,id']);
        $lead->update(['assigned_to' => $validated['assigned_to']]);
        return response()->json(['data' => $lead->fresh(['assignedTo']), 'message' => 'Success']);
    }
}
