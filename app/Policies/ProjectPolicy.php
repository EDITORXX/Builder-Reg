<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBuilderAdmin() || $user->isManager()
            || $user->isSalesExec() || $user->isViewer();
    }

    public function view(User $user, Project $project): bool
    {
        return $this->belongsToBuilder($user, $project) || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBuilderAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isSuperAdmin() || ($user->isBuilderAdmin() && $this->belongsToBuilder($user, $project));
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isSuperAdmin() || ($user->isBuilderAdmin() && $this->belongsToBuilder($user, $project));
    }

    private function belongsToBuilder(User $user, Project $project): bool
    {
        return $user->builder_firm_id && (int) $user->builder_firm_id === (int) $project->builder_firm_id;
    }
}
