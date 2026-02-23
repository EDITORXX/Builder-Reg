<?php

namespace App\Policies;

use App\Models\LeadLock;
use App\Models\User;

class LeadLockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBuilderAdmin() || $user->isManager() || $user->isSalesExec() || $user->isViewer();
    }

    public function view(User $user, LeadLock $leadLock): bool
    {
        return $this->builderScope($user, $leadLock);
    }

    public function forceUnlock(User $user, LeadLock $leadLock): bool
    {
        return $user->isSuperAdmin() || $user->isBuilderAdmin();
    }

    private function builderScope(User $user, LeadLock $leadLock): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        $builderFirmId = $leadLock->project?->builder_firm_id;
        return $builderFirmId && $user->belongsToBuilderFirm($builderFirmId);
    }
}
