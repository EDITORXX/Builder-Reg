<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BuilderFirm;
use App\Models\Lead;
use App\Models\VisitSchedule;
use App\Services\LockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitVerificationController extends Controller
{
    public function __construct(
        private LockService $lockService
    ) {}

    private function ensureCanVerify(string $slug): BuilderFirm
    {
        if (! session('api_token') || ! session('user')) {
            abort(302, '', ['Location' => route('login')]);
        }
        $builder = BuilderFirm::where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403, 'You do not have access to this tenant.');
        }
        $allowed = in_array($user->role ?? '', ['super_admin', 'builder_admin', 'manager', 'sales_exec'], true);
        if (! $allowed) {
            abort(403, 'You cannot verify visits.');
        }
        return $builder;
    }

    public function approve(Request $request, string $slug, Lead $lead): RedirectResponse
    {
        $builder = $this->ensureCanVerify($slug);
        $lead->load(['project', 'customer']);
        if ((int) $lead->project?->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        if ($lead->status !== Lead::STATUS_PENDING_VERIFICATION) {
            return redirect()->route('tenant.visit-verifications.index', $slug)
                ->with('error', 'This lead is no longer pending verification.');
        }

        try {
            DB::transaction(function () use ($lead) {
                $this->lockService->createLockForLead($lead);
                $lead->update(['status' => Lead::STATUS_VERIFIED_VISIT]);
                VisitSchedule::where('lead_id', $lead->id)->update(['status' => VisitSchedule::STATUS_APPROVED]);
            });
        } catch (\Throwable $e) {
            return redirect()->route('tenant.visit-verifications.index', $slug)
                ->with('error', 'Could not approve: ' . $e->getMessage());
        }

        return redirect()->route('tenant.visit-verifications.index', $slug)
            ->with('success', 'Visit approved. Customer is now locked to the CP for this project.');
    }

    public function reject(Request $request, string $slug, Lead $lead): RedirectResponse
    {
        $builder = $this->ensureCanVerify($slug);
        $lead->load('project');
        if ((int) $lead->project?->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        if ($lead->status !== Lead::STATUS_PENDING_VERIFICATION) {
            return redirect()->route('tenant.visit-verifications.index', $slug)
                ->with('error', 'This lead is no longer pending verification.');
        }

        $validated = $request->validate(['reason' => 'nullable|string|max:1000']);
        $lead->update([
            'status' => Lead::STATUS_REJECTED,
            'verification_reject_reason' => $validated['reason'] ?? null,
        ]);
        VisitSchedule::where('lead_id', $lead->id)->update(['status' => VisitSchedule::STATUS_REJECTED]);

        return redirect()->route('tenant.visit-verifications.index', $slug)
            ->with('success', 'Visit rejected.');
    }
}
