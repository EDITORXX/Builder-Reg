<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitSchedule;

class VisitSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChannelPartner();
    }

    public function view(User $user, VisitSchedule $visitSchedule): bool
    {
        return $user->isChannelPartner()
            && (int) $visitSchedule->channel_partner_id === (int) $user->channelPartner?->id;
    }

    public function create(User $user): bool
    {
        return $user->isChannelPartner();
    }

    public function update(User $user, VisitSchedule $visitSchedule): bool
    {
        return $user->isChannelPartner()
            && (int) $visitSchedule->channel_partner_id === (int) $user->channelPartner?->id;
    }

    public function delete(User $user, VisitSchedule $visitSchedule): bool
    {
        return $user->isChannelPartner()
            && (int) $visitSchedule->channel_partner_id === (int) $user->channelPartner?->id;
    }
}
