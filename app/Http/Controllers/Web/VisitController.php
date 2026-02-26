<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BuilderFirm;
use App\Models\LeadActivity;
use App\Models\Visit;
use App\Notifications\VisitConfirmedNotification;
use App\Services\AuditService;
use App\Services\LockService;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VisitController extends Controller
{
    public function __construct(
        private OtpService $otpService,
        private LockService $lockService,
        private AuditService $auditService
    ) {}

    /**
     * @return BuilderFirm|RedirectResponse
     */
    private function ensureVisitBelongsToTenant(string $slug, Visit $visit): BuilderFirm|RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403, 'You do not have access to this tenant.');
        }
        $visit->load('lead.project');
        if ((int) $visit->lead?->project?->builder_firm_id !== (int) $builder->id) {
            abort(404, 'Visit not found.');
        }
        return $builder;
    }

    public function reschedule(Request $request, string $slug, Visit $visit): RedirectResponse
    {
        $builder = $this->ensureVisitBelongsToTenant($slug, $visit);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        Gate::forUser(session('user'))->authorize('update', $visit);
        $validated = $request->validate(['scheduled_at' => 'required|date']);
        $visit->update(['scheduled_at' => $validated['scheduled_at'], 'status' => Visit::STATUS_RESCHEDULED]);
        LeadActivity::create([
            'lead_id' => $visit->lead_id,
            'created_by' => session('user')->id,
            'type' => 'visit_rescheduled',
            'payload' => ['scheduled_at' => $validated['scheduled_at']],
            'created_at' => now(),
        ]);
        return redirect()->route('tenant.visits.index', $slug)->with('success', 'Visit rescheduled.');
    }

    public function cancel(Request $request, string $slug, Visit $visit): RedirectResponse
    {
        $builder = $this->ensureVisitBelongsToTenant($slug, $visit);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        Gate::forUser(session('user'))->authorize('update', $visit);
        $validated = $request->validate(['reason' => 'nullable|string|max:500']);
        $visit->update(['status' => Visit::STATUS_CANCELLED, 'notes' => $validated['reason'] ?? null]);
        LeadActivity::create([
            'lead_id' => $visit->lead_id,
            'created_by' => session('user')->id,
            'type' => 'visit_cancelled',
            'payload' => ['reason' => $validated['reason'] ?? null],
            'created_at' => now(),
        ]);
        return redirect()->route('tenant.visits.index', $slug)->with('success', 'Visit cancelled.');
    }

    public function confirm(Request $request, string $slug, Visit $visit): RedirectResponse
    {
        $builder = $this->ensureVisitBelongsToTenant($slug, $visit);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        Gate::forUser(session('user'))->authorize('confirm', $visit);
        $validated = $request->validate(['notes' => 'nullable|string|max:500']);
        $visit->update([
            'status' => Visit::STATUS_CONFIRMED,
            'confirmed_at' => now(),
            'confirm_method' => Visit::CONFIRM_METHOD_MANUAL,
            'confirmed_by' => session('user')->id,
            'notes' => $validated['notes'] ?? null,
        ]);
        $lead = $visit->lead;
        $lead->update(['visit_status' => \App\Models\Lead::VISITED, 'verification_status' => \App\Models\Lead::VERIFIED_VISIT]);
        $lock = $this->lockService->createLockForVisit($visit);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'created_by' => session('user')->id,
            'type' => 'visit_confirmed',
            'payload' => ['confirm_method' => 'manual', 'lock_id' => $lock->id],
            'created_at' => now(),
        ]);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'created_by' => session('user')->id,
            'type' => 'lock_created',
            'payload' => ['lock_id' => $lock->id],
            'created_at' => now(),
        ]);
        $this->auditService->log(session('user')->id, 'lock_created', 'LeadLock', $lock->id, null, $lock->toArray(), $validated['notes'] ?? null, $request);
        if ($lead->channel_partner_id && $lead->channelPartner?->user) {
            $lead->load(['project.builderFirm', 'customer']);
            $lead->channelPartner->user->notify(new VisitConfirmedNotification(
                $lead->fresh(['customer']),
                $lock->end_at->diffInDays($lock->start_at),
                $lock->end_at->format('d/m/Y'),
                $lead->project?->builderFirm
            ));
        }
        return redirect()->route('tenant.visits.index', $slug)->with('success', 'Visit confirmed. Lock active.');
    }

    public function sendOtp(Request $request, string $slug, Visit $visit): RedirectResponse
    {
        $builder = $this->ensureVisitBelongsToTenant($slug, $visit);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        Gate::forUser(session('user'))->authorize('sendOtp', $visit);
        $this->otpService->generateAndStore($visit);
        return redirect()->route('tenant.visits.index', $slug)->with('success', 'OTP sent. Valid for ' . \App\Models\VisitOtp::VALIDITY_MINUTES . ' minutes.');
    }

    public function verifyOtp(Request $request, string $slug, Visit $visit): RedirectResponse
    {
        $builder = $this->ensureVisitBelongsToTenant($slug, $visit);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        Gate::forUser(session('user'))->authorize('verifyOtp', $visit);
        if ($this->otpService->isMaxAttemptsReached($visit)) {
            return redirect()->route('tenant.visits.index', $slug)->with('error', 'Too many attempts. Use manual confirm.');
        }
        $validated = $request->validate(['otp' => 'required|string|size:6']);
        if (! $this->otpService->verify($visit, $validated['otp'])) {
            return redirect()->route('tenant.visits.index', $slug)->with('error', 'Invalid or expired OTP.');
        }
        $visit->update([
            'status' => Visit::STATUS_CONFIRMED,
            'confirmed_at' => now(),
            'confirm_method' => Visit::CONFIRM_METHOD_OTP,
            'confirmed_by' => session('user')->id,
        ]);
        $lead = $visit->lead;
        $lead->update(['visit_status' => \App\Models\Lead::VISITED, 'verification_status' => \App\Models\Lead::VERIFIED_VISIT]);
        $lock = $this->lockService->createLockForVisit($visit);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'created_by' => session('user')->id,
            'type' => 'visit_confirmed',
            'payload' => ['confirm_method' => 'otp', 'lock_id' => $lock->id],
            'created_at' => now(),
        ]);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'created_by' => session('user')->id,
            'type' => 'lock_created',
            'payload' => ['lock_id' => $lock->id, 'end_at' => $lock->end_at->toIso8601String()],
            'created_at' => now(),
        ]);
        $this->auditService->log(session('user')->id, 'lock_created', 'LeadLock', $lock->id, null, $lock->toArray(), null, $request);
        if ($lead->channel_partner_id && $lead->channelPartner?->user) {
            $lead->load(['project.builderFirm', 'customer']);
            $lead->channelPartner->user->notify(new VisitConfirmedNotification(
                $lead->fresh(['customer']),
                $lock->end_at->diffInDays($lock->start_at),
                $lock->end_at->format('d/m/Y'),
                $lead->project?->builderFirm
            ));
        }
        return redirect()->route('tenant.visits.index', $slug)->with('success', 'Visit confirmed. Lock active.');
    }
}
