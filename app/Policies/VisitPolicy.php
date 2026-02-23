<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Visit $visit): bool
    {
        $lead = $visit->lead;
        if ($user->isChannelPartner()) {
            return $lead->channel_partner_id && (int) $lead->channel_partner_id === (int) $user->channelPartner?->id;
        }
        return $user->isManager() || $user->isSalesExec() || $user->isBuilderAdmin() || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isChannelPartner() || $user->isSalesExec() || $user->isManager();
    }

    public function update(User $user, Visit $visit): bool
    {
        return $user->isChannelPartner() || $user->isSalesExec() || $user->isManager() || $user->isBuilderAdmin() || $user->isSuperAdmin();
    }

    public function confirm(User $user, Visit $visit): bool
    {
        return $user->isManager() || $user->isSalesExec() || $user->isBuilderAdmin() || $user->isSuperAdmin();
    }

    public function sendOtp(User $user, Visit $visit): bool
    {
        return $user->isManager() || $user->isSalesExec();
    }

    public function verifyOtp(User $user, Visit $visit): bool
    {
        return $user->isManager() || $user->isSalesExec();
    }
}
