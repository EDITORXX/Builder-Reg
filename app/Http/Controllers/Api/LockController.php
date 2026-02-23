<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LeadLock;
use App\Services\AuditService;
use App\Services\LockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LockController extends Controller
{
    public function __construct(
        private LockService $lockService,
        private AuditService $auditService
    ) {}

    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'mobile' => 'required|string|max:20',
        ]);
        $normalized = Customer::normalizeMobile($request->mobile);
        $result = $this->lockService->checkLock((int) $request->project_id, $normalized);
        return response()->json(['data' => $result, 'message' => 'Success']);
    }

    public function index(Request $request): JsonResponse
    {
        $query = LeadLock::with(['project', 'lead', 'channelPartner'])->active();
        $user = $request->user();
        if ($user->builder_firm_id && ! $user->isSuperAdmin()) {
            $query->whereHas('project', fn ($q) => $q->where('builder_firm_id', $user->builder_firm_id));
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        $locks = $query->orderBy('end_at')->paginate($request->input('per_page', 20));
        return response()->json([
            'data' => $locks->items(),
            'meta' => [
                'total' => $locks->total(),
                'page' => $locks->currentPage(),
                'per_page' => $locks->perPage(),
                'last_page' => $locks->lastPage(),
            ],
            'message' => 'Success',
        ]);
    }

    public function forceUnlock(Request $request, LeadLock $lock): JsonResponse
    {
        $this->authorize('forceUnlock', $lock);
        $validated = $request->validate(['reason' => 'required|string|max:1000']);
        $before = $lock->toArray();
        $this->lockService->forceUnlock($lock, $request->user()->id, $validated['reason']);
        $this->auditService->log(
            $request->user()->id,
            'force_unlock',
            'LeadLock',
            $lock->id,
            $before,
            $lock->fresh()->toArray(),
            $validated['reason'],
            $request
        );
        return response()->json(['data' => $lock->fresh(), 'message' => 'Success']);
    }
}
