<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadLock;
use App\Models\VisitCheckIn;
use App\Models\VisitSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerificationService
{
    private const PROTECTION_DAYS = 30;

    public function __construct(
        private LockService $lockService,
        private AuditService $auditService
    ) {}

    /**
     * Approve a pending visit check-in. Applies 30-day ownership protection.
     *
     * @return array{success: bool, message: string}
     */
    public function approveVisitCheckIn(Lead $lead, VisitCheckIn $checkIn, int $userId, ?Request $request = null): array
    {
        if (! $checkIn->isPending()) {
            return ['success' => false, 'message' => 'This visit is no longer pending verification.'];
        }

        $lead->load(['project', 'customer']);
        $project = $lead->project;
        $customerMobile = \App\Models\Customer::normalizeMobile($lead->customer->mobile);
        $visitOwnerCpId = (int) $checkIn->channel_partner_id;
        $currentOwnerCpId = $lead->channel_partner_id ? (int) $lead->channel_partner_id : null;

        $isRevisit = $lead->visitCheckIns()
            ->where('id', '!=', $checkIn->id)
            ->where('verification_status', VisitCheckIn::VERIFICATION_VERIFIED)
            ->exists();

        try {
            DB::transaction(function () use ($lead, $checkIn, $userId, $request, $project, $customerMobile, $visitOwnerCpId, $currentOwnerCpId, $isRevisit) {
                $now = now();

                if (! $isRevisit) {
                    // First visit: set ownership and create lock
                    $lead->update([
                        'channel_partner_id' => $visitOwnerCpId,
                        'verification_status' => Lead::VERIFIED_VISIT,
                        'visit_status' => Lead::VISITED,
                        'last_verified_visit_at' => $now,
                    ]);
                    $this->lockService->createLockForLead($lead);
                    $this->markCheckInVerified($checkIn, $userId);
                    if ($checkIn->visit_schedule_id) {
                        VisitSchedule::where('id', $checkIn->visit_schedule_id)->update(['status' => VisitSchedule::STATUS_APPROVED]);
                    }
                    $this->auditService->log($userId, 'visit_approved', 'Lead', $lead->id, null, ['verification_status' => Lead::VERIFIED_VISIT], 'First visit approved', $request);
                    $this->auditService->log($userId, 'lock_created', 'VisitCheckIn', $checkIn->id, null, ['lead_id' => $lead->id], null, $request);
                    return;
                }

                // Revisit
                $lastVerified = $lead->last_verified_visit_at;
                $withinProtection = $lastVerified && $lastVerified->diffInDays($now, false) <= self::PROTECTION_DAYS;

                if ($withinProtection && $visitOwnerCpId !== $currentOwnerCpId) {
                    // Block transfer: different CP within 30 days
                    $this->auditService->log($userId, 'block_transfer_attempt', 'Lead', $lead->id, [
                        'visit_owner_cp_id' => $visitOwnerCpId,
                        'current_owner_cp_id' => $currentOwnerCpId,
                    ], null, 'Protected lead within 30 days; cannot transfer to new CP.', $request);
                    throw new \RuntimeException('Protected lead within 30 days; cannot transfer to new CP.');
                }

                if ($withinProtection && $visitOwnerCpId === $currentOwnerCpId) {
                    // Same CP: reset lock, set REVISIT
                    $activeLock = LeadLock::active()
                        ->where('project_id', $project->id)
                        ->where('customer_mobile', $customerMobile)
                        ->first();
                    if ($activeLock) {
                        $this->lockService->resetLock($activeLock);
                        $this->auditService->log($userId, 'lock_reset', 'LeadLock', $activeLock->id, null, ['start_at' => $now->toIso8601String()], 'Revisit same CP within 30 days', $request);
                    }
                    $lead->update([
                        'verification_status' => Lead::VERIFIED_VISIT,
                        'visit_status' => Lead::REVISIT,
                        'last_verified_visit_at' => $now,
                    ]);
                    $this->markCheckInVerified($checkIn, $userId);
                    if ($checkIn->visit_schedule_id) {
                        VisitSchedule::where('id', $checkIn->visit_schedule_id)->update(['status' => VisitSchedule::STATUS_APPROVED]);
                    }
                    $this->auditService->log($userId, 'visit_approved', 'Lead', $lead->id, null, ['visit_status' => Lead::REVISIT], 'Revisit same CP approved', $request);
                    return;
                }

                // Revisit after 30 days: allow transfer to new CP
                $this->lockService->expireActiveLockFor($project->id, $customerMobile);
                $lead->update([
                    'channel_partner_id' => $visitOwnerCpId,
                    'verification_status' => Lead::VERIFIED_VISIT,
                    'visit_status' => Lead::REVISIT,
                    'last_verified_visit_at' => $now,
                ]);
                $this->lockService->createLockForLead($lead);
                $this->markCheckInVerified($checkIn, $userId);
                if ($checkIn->visit_schedule_id) {
                    VisitSchedule::where('id', $checkIn->visit_schedule_id)->update(['status' => VisitSchedule::STATUS_APPROVED]);
                }
                $this->auditService->log($userId, 'ownership_transfer', 'Lead', $lead->id, ['previous_cp_id' => $currentOwnerCpId], ['channel_partner_id' => $visitOwnerCpId], 'Revisit after 30 days; transferred to new CP', $request);
                $this->auditService->log($userId, 'visit_approved', 'Lead', $lead->id, null, ['verification_status' => Lead::VERIFIED_VISIT], 'Revisit transfer approved', $request);
            });

            return ['success' => true, 'message' => 'Visit approved. Customer is now locked to the CP for this project.'];
        } catch (\RuntimeException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Could not approve: ' . $e->getMessage()];
        }
    }

    private function markCheckInVerified(VisitCheckIn $checkIn, int $userId): void
    {
        $checkIn->update([
            'verification_status' => VisitCheckIn::VERIFICATION_VERIFIED,
            'verified_at' => now(),
            'verified_by' => $userId,
        ]);
    }
}
