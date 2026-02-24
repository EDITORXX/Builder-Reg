<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BuilderFirm;
use App\Models\Lead;
use App\Models\VisitCheckIn;
use App\Models\VisitSchedule;
use App\Services\AuditService;
use App\Services\VerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VisitVerificationController extends Controller
{
    public function __construct(
        private VerificationService $verificationService,
        private AuditService $auditService
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
        $lead->load(['project', 'customer', 'visitCheckIns']);
        if ((int) $lead->project?->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        if ($lead->verification_status !== Lead::PENDING_VERIFICATION) {
            return redirect()->route('tenant.visit-verifications.index', $slug)
                ->with('error', 'This lead is no longer pending verification.');
        }

        $pendingCheckIn = $lead->visitCheckIns()
            ->where('verification_status', VisitCheckIn::VERIFICATION_PENDING)
            ->orderByDesc('submitted_at')
            ->first();

        if (! $pendingCheckIn) {
            return redirect()->route('tenant.visit-verifications.index', $slug)
                ->with('error', 'No pending visit check-in found for this lead.');
        }

        $user = session('user');
        $result = $this->verificationService->approveVisitCheckIn($lead, $pendingCheckIn, (int) $user->id, $request);

        if (! $result['success']) {
            return redirect()->route('tenant.visit-verifications.index', $slug)
                ->with('error', $result['message']);
        }

        return redirect()->route('tenant.visit-verifications.index', $slug)
            ->with('success', $result['message']);
    }

    public function reject(Request $request, string $slug, Lead $lead): RedirectResponse
    {
        $builder = $this->ensureCanVerify($slug);
        $lead->load(['project', 'visitCheckIns']);
        if ((int) $lead->project?->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        if ($lead->verification_status !== Lead::PENDING_VERIFICATION) {
            return redirect()->route('tenant.visit-verifications.index', $slug)
                ->with('error', 'This lead is no longer pending verification.');
        }

        $validated = $request->validate(['reason' => 'nullable|string|max:1000']);
        $reason = $validated['reason'] ?? null;

        $lead->update([
            'verification_status' => Lead::REJECTED_VERIFICATION,
            'verification_reject_reason' => $reason,
        ]);

        $lead->visitCheckIns()
            ->where('verification_status', VisitCheckIn::VERIFICATION_PENDING)
            ->update([
                'verification_status' => VisitCheckIn::VERIFICATION_REJECTED,
                'rejection_reason' => $reason,
            ]);

        VisitSchedule::where('lead_id', $lead->id)->update(['status' => VisitSchedule::STATUS_REJECTED]);

        $user = session('user');
        $this->auditService->log((int) $user->id, 'visit_rejected', 'Lead', $lead->id, null, ['verification_status' => Lead::REJECTED_VERIFICATION], $reason, $request);

        return redirect()->route('tenant.visit-verifications.index', $slug)
            ->with('success', 'Visit rejected.');
    }
}
