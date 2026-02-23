<?php

namespace App\Policies;

use App\Models\BuilderFirm;
use App\Models\User;

class BuilderFirmPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, BuilderFirm $builderFirm): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, BuilderFirm $builderFirm): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, BuilderFirm $builderFirm): bool
    {
        return $user->isSuperAdmin();
    }
}
