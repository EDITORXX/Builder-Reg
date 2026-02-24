<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lead $lead): bool
    {
        if ($user->isChannelPartner()) {
            return $lead->channel_partner_id && (int) $lead->channel_partner_id === (int) $user->channelPartner?->id;
        }
        if ($user->isSalesExec()) {
            return $lead->assigned_to && (int) $lead->assigned_to === (int) $user->id;
        }
        if ($user->isViewer() || $user->isManager() || $user->isBuilderAdmin() || $user->isSuperAdmin()) {
            return $this->builderScope($user, $lead);
        }
        return false;
    }

    public function create(User $user): bool
    {
        return ! $user->isViewer();
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($user->isChannelPartner()) {
            return $lead->channel_partner_id && (int) $lead->channel_partner_id === (int) $user->channelPartner?->id;
        }
        return $user->isManager() || $user->isSalesExec() || $user->isBuilderAdmin() || $user->isSuperAdmin();
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->isSuperAdmin() || $user->isBuilderAdmin();
    }

    public function updateStatus(User $user, Lead $lead): bool
    {
        return $user->isManager() || $user->isSalesExec() || $user->isBuilderAdmin() || $user->isSuperAdmin();
    }

    public function updateSalesStatus(User $user, Lead $lead): bool
    {
        if ($user->isChannelPartner()) {
            return $lead->channel_partner_id && (int) $lead->channel_partner_id === (int) $user->channelPartner?->id;
        }
        return $user->isManager() || $user->isSalesExec() || $user->isBuilderAdmin() || $user->isSuperAdmin();
    }

    public function assign(User $user, Lead $lead): bool
    {
        return $user->isManager() || $user->isBuilderAdmin() || $user->isSuperAdmin();
    }

    private function builderScope(User $user, Lead $lead): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        $builderFirmId = $lead->project?->builder_firm_id;
        return $builderFirmId && $user->belongsToBuilderFirm($builderFirmId);
    }
}
