<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadLock;
use App\Models\Customer;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class LockService
{
    public function checkLock(int $projectId, string $normalizedMobile): array
    {
        $bookedBlock = Lead::where('project_id', $projectId)
            ->whereHas('customer', fn ($q) => $q->where('mobile', $normalizedMobile))
            ->where('status', Lead::STATUS_BOOKED)
            ->exists();

        if ($bookedBlock) {
            return [
                'locked' => true,
                'reason' => 'booked',
                'lock_expires_at' => null,
                'days_remaining' => null,
            ];
        }

        $active = LeadLock::active()
            ->where('project_id', $projectId)
            ->where('customer_mobile', $normalizedMobile)
            ->first();

        if ($active) {
            return [
                'locked' => true,
                'reason' => 'active_lock',
                'lock_expires_at' => $active->end_at->toIso8601String(),
                'days_remaining' => max(0, (int) now()->diffInDays($active->end_at, false)),
            ];
        }

        return [
            'locked' => false,
            'lock_expires_at' => null,
            'days_remaining' => null,
        ];
    }

    /**
     * Check lock and duplicate for (project, mobile) considering current CP.
     * Returns whether to allow (first-time or same-CP revisit) or block (different CP / locked).
     *
     * @param int $projectId
     * @param string $normalizedMobile
     * @param int|null $currentChannelPartnerId null = walk-in
     * @return array{allowed: bool, is_revisit: bool, locked: bool, duplicate: bool, reason: string|null, lock_expires_at: string|null, days_remaining: int|null, message: string}
     */
    public function checkLockAndDuplicate(int $projectId, string $normalizedMobile, ?int $currentChannelPartnerId): array
    {
        $bookedBlock = Lead::where('project_id', $projectId)
            ->whereHas('customer', fn ($q) => $q->where('mobile', $normalizedMobile))
            ->where('status', Lead::STATUS_BOOKED)
            ->first();

        if ($bookedBlock) {
            return [
                'allowed' => false,
                'is_revisit' => false,
                'locked' => true,
                'duplicate' => false,
                'reason' => 'booked',
                'lock_expires_at' => null,
                'days_remaining' => null,
                'message' => 'Ye customer is project par pehle se booked hai.',
            ];
        }

        $activeLock = LeadLock::active()
            ->where('project_id', $projectId)
            ->where('customer_mobile', $normalizedMobile)
            ->first();

        if ($activeLock) {
            $sameCp = $this->sameChannelPartner($currentChannelPartnerId, $activeLock->channel_partner_id);
            if ($sameCp) {
                return [
                    'allowed' => true,
                    'is_revisit' => true,
                    'locked' => false,
                    'duplicate' => false,
                    'reason' => null,
                    'lock_expires_at' => $activeLock->end_at->toIso8601String(),
                    'days_remaining' => max(0, (int) now()->diffInDays($activeLock->end_at, false)),
                    'message' => '',
                ];
            }
            $dateStr = $activeLock->end_at->format('d/m/Y');
            return [
                'allowed' => false,
                'is_revisit' => false,
                'locked' => true,
                'duplicate' => true,
                'reason' => 'active_lock',
                'lock_expires_at' => $activeLock->end_at->toIso8601String(),
                'days_remaining' => max(0, (int) now()->diffInDays($activeLock->end_at, false)),
                'message' => "Customer is locked for this project until {$dateStr}.",
            ];
        }

        $existingLead = Lead::where('project_id', $projectId)
            ->whereHas('customer', fn ($q) => $q->where('mobile', $normalizedMobile))
            ->orderByDesc('id')
            ->first();

        if ($existingLead) {
            $sameCp = $this->sameChannelPartner($currentChannelPartnerId, $existingLead->channel_partner_id);
            // No active lock = lock period over. Same CP → revisit; different CP → allowed (new lead, will show in that CP's lead section).
            return [
                'allowed' => true,
                'is_revisit' => $sameCp,
                'locked' => false,
                'duplicate' => false,
                'reason' => null,
                'lock_expires_at' => null,
                'days_remaining' => null,
                'message' => '',
            ];
        }

        return [
            'allowed' => true,
            'is_revisit' => false,
            'locked' => false,
            'duplicate' => false,
            'reason' => null,
            'lock_expires_at' => null,
            'days_remaining' => null,
            'message' => '',
        ];
    }

    private function sameChannelPartner(?int $a, ?int $b): bool
    {
        if ($a === null && $b === null) {
            return true;
        }
        if ($a === null || $b === null) {
            return false;
        }
        return $a === $b;
    }

    public function createLockForVisit(Visit $visit): LeadLock
    {
        $lead = $visit->lead;
        $project = $lead->project;
        $customerMobile = Customer::normalizeMobile($lead->customer->mobile);
        $lockDays = $project->getLockDays();
        $confirmedAt = $visit->confirmed_at ?? now();

        return DB::transaction(function () use ($visit, $lead, $project, $customerMobile, $lockDays, $confirmedAt) {
            $existing = LeadLock::active()
                ->where('project_id', $project->id)
                ->where('customer_mobile', $customerMobile)
                ->lockForUpdate()
                ->first();
            if ($existing) {
                throw new \RuntimeException('Active lock already exists for this project and customer.');
            }

            return LeadLock::create([
                'project_id' => $project->id,
                'customer_mobile' => $customerMobile,
                'lead_id' => $lead->id,
                'channel_partner_id' => $lead->channel_partner_id,
                'start_at' => $confirmedAt,
                'end_at' => $confirmedAt->copy()->addDays($lockDays),
                'status' => LeadLock::STATUS_ACTIVE,
            ]);
        });
    }

    /**
     * Create lock for a lead (e.g. after manager approves visit verification).
     */
    public function createLockForLead(Lead $lead): LeadLock
    {
        $lead->load(['project', 'customer']);
        $project = $lead->project;
        $customerMobile = Customer::normalizeMobile($lead->customer->mobile);
        $lockDays = $project->getLockDays();

        return DB::transaction(function () use ($lead, $project, $customerMobile, $lockDays) {
            $existing = LeadLock::active()
                ->where('project_id', $project->id)
                ->where('customer_mobile', $customerMobile)
                ->lockForUpdate()
                ->first();
            if ($existing) {
                throw new \RuntimeException('Active lock already exists for this project and customer.');
            }

            return LeadLock::create([
                'project_id' => $project->id,
                'customer_mobile' => $customerMobile,
                'lead_id' => $lead->id,
                'channel_partner_id' => $lead->channel_partner_id,
                'start_at' => now(),
                'end_at' => now()->addDays($lockDays),
                'status' => LeadLock::STATUS_ACTIVE,
            ]);
        });
    }

    public function forceUnlock(LeadLock $lock, int $userId, string $reason): void
    {
        $lock->update([
            'status' => LeadLock::STATUS_FORCE_UNLOCKED,
            'unlocked_by' => $userId,
            'unlock_reason' => $reason,
        ]);
    }
}
