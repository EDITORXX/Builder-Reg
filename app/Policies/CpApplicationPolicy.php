<?php

namespace App\Policies;

use App\Models\CpApplication;
use App\Models\User;

class CpApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBuilderAdmin() || $user->isManager();
    }

    public function view(User $user, CpApplication $cpApplication): bool
    {
        if ($user->isChannelPartner()) {
            return (int) $cpApplication->channel_partner_id === (int) $user->channelPartner?->id;
        }
        return $this->builderScope($user, $cpApplication);
    }

    public function create(User $user): bool
    {
        return $user->isChannelPartner();
    }

    public function update(User $user, CpApplication $cpApplication): bool
    {
        return false;
    }

    public function delete(User $user, CpApplication $cpApplication): bool
    {
        return false;
    }

    public function approve(User $user, CpApplication $cpApplication): bool
    {
        return $user->isSuperAdmin() || $user->isBuilderAdmin() || $user->isManager();
    }

    public function reject(User $user, CpApplication $cpApplication): bool
    {
        return $user->isSuperAdmin() || $user->isBuilderAdmin() || $user->isManager();
    }

    private function builderScope(User $user, CpApplication $cpApplication): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->belongsToBuilderFirm($cpApplication->builder_firm_id);
    }
}
