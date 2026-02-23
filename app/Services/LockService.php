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

    public function forceUnlock(LeadLock $lock, int $userId, string $reason): void
    {
        $lock->update([
            'status' => LeadLock::STATUS_FORCE_UNLOCKED,
            'unlocked_by' => $userId,
            'unlock_reason' => $reason,
        ]);
    }
}
