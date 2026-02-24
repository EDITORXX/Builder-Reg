<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Visit;
use App\Services\AuditService;
use App\Services\LockService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function __construct(
        private OtpService $otpService,
        private LockService $lockService,
        private AuditService $auditService
    ) {}

    public function store(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('create', Visit::class);
        $validated = $request->validate(['scheduled_at' => 'required|date']);
        $visit = Visit::create([
            'lead_id' => $lead->id,
            'scheduled_at' => $validated['scheduled_at'],
            'status' => Visit::STATUS_SCHEDULED,
        ]);
        $lead->update(['visit_status' => Lead::VISIT_SCHEDULED]);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'created_by' => $request->user()->id,
            'type' => 'visit_scheduled',
            'payload' => ['scheduled_at' => $validated['scheduled_at']],
            'created_at' => now(),
        ]);
        $visit->load('lead');
        return response()->json(['data' => $visit, 'message' => 'Success'], 201);
    }

    public function reschedule(Request $request, Visit $visit): JsonResponse
    {
        $this->authorize('update', $visit);
        $validated = $request->validate(['scheduled_at' => 'required|date']);
        $visit->update(['scheduled_at' => $validated['scheduled_at'], 'status' => Visit::STATUS_RESCHEDULED]);
        return response()->json(['data' => $visit->fresh(), 'message' => 'Success']);
    }

    public function cancel(Request $request, Visit $visit): JsonResponse
    {
        $this->authorize('update', $visit);
        $validated = $request->validate(['reason' => 'nullable|string|max:500']);
        $visit->update(['status' => Visit::STATUS_CANCELLED, 'notes' => $validated['reason'] ?? null]);
        return response()->json(['data' => $visit->fresh(), 'message' => 'Success']);
    }

    public function sendOtp(Request $request, Visit $visit): JsonResponse
    {
        $this->authorize('sendOtp', $visit);
        $otp = $this->otpService->generateAndStore($visit);
        return response()->json([
            'data' => ['message' => 'OTP sent. Valid for ' . \App\Models\VisitOtp::VALIDITY_MINUTES . ' minutes.'],
            'message' => 'Success',
        ]);
    }

    public function verifyOtp(Request $request, Visit $visit): JsonResponse
    {
        $this->authorize('verifyOtp', $visit);
        if ($this->otpService->isMaxAttemptsReached($visit)) {
            return response()->json(['error' => 'Too many attempts. Use manual confirm.'], 429);
        }
        $validated = $request->validate(['otp' => 'required|string|size:6']);
        if (! $this->otpService->verify($visit, $validated['otp'])) {
            return response()->json(['error' => 'Invalid or expired OTP.'], 422);
        }
        $visit->update([
            'status' => Visit::STATUS_CONFIRMED,
            'confirmed_at' => now(),
            'confirm_method' => Visit::CONFIRM_METHOD_OTP,
            'confirmed_by' => $request->user()->id,
        ]);
        $lead = $visit->lead;
        $lead->update(['visit_status' => Lead::VISITED, 'verification_status' => Lead::VERIFIED_VISIT]);
        $lock = $this->lockService->createLockForVisit($visit);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'created_by' => $request->user()->id,
            'type' => 'visit_confirmed',
            'payload' => ['confirm_method' => 'otp', 'lock_id' => $lock->id],
            'created_at' => now(),
        ]);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'created_by' => $request->user()->id,
            'type' => 'lock_created',
            'payload' => ['lock_id' => $lock->id, 'end_at' => $lock->end_at->toIso8601String()],
            'created_at' => now(),
        ]);
        $this->auditService->log($request->user()->id, 'lock_created', 'LeadLock', $lock->id, null, $lock->toArray(), null, $request);
        if ($lead->channel_partner_id && $lead->channelPartner?->user) {
            $lead->load(['project.builderFirm', 'customer']);
            $lead->channelPartner->user->notify(new \App\Notifications\VisitConfirmedNotification(
                $lead->fresh(['customer']),
                $lock->end_at->diffInDays($lock->start_at),
                $lock->end_at->format('d/m/Y'),
                $lead->project?->builderFirm
            ));
        }
        return response()->json([
            'data' => [
                'visit' => $visit->fresh(),
                'lock' => $lock->load('project'),
            ],
            'message' => 'Visit confirmed. Lock active.',
        ]);
    }

    public function confirm(Request $request, Visit $visit): JsonResponse
    {
        $this->authorize('confirm', $visit);
        $validated = $request->validate(['notes' => 'nullable|string|max:500']);
        $visit->update([
            'status' => Visit::STATUS_CONFIRMED,
            'confirmed_at' => now(),
            'confirm_method' => Visit::CONFIRM_METHOD_MANUAL,
            'confirmed_by' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
        ]);
        $lead = $visit->lead;
        $lead->update(['visit_status' => Lead::VISITED, 'verification_status' => Lead::VERIFIED_VISIT]);
        $lock = $this->lockService->createLockForVisit($visit);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'created_by' => $request->user()->id,
            'type' => 'visit_confirmed',
            'payload' => ['confirm_method' => 'manual', 'lock_id' => $lock->id],
            'created_at' => now(),
        ]);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'created_by' => $request->user()->id,
            'type' => 'lock_created',
            'payload' => ['lock_id' => $lock->id],
            'created_at' => now(),
        ]);
        $this->auditService->log($request->user()->id, 'lock_created', 'LeadLock', $lock->id, null, $lock->toArray(), $validated['notes'] ?? null, $request);
        if ($lead->channel_partner_id && $lead->channelPartner?->user) {
            $lead->load(['project.builderFirm', 'customer']);
            $lead->channelPartner->user->notify(new \App\Notifications\VisitConfirmedNotification(
                $lead->fresh(['customer']),
                $lock->end_at->diffInDays($lock->start_at),
                $lock->end_at->format('d/m/Y'),
                $lead->project?->builderFirm
            ));
        }
        return response()->json([
            'data' => ['visit' => $visit->fresh(), 'lock' => $lock->load('project')],
            'message' => 'Visit confirmed. Lock active.',
        ]);
    }
}
