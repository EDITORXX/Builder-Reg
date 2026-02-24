<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBuilderAdmin();
    }

    public function view(User $user, Form $form): bool
    {
        return $user->isSuperAdmin() || ($user->builder_firm_id && (int) $user->builder_firm_id === (int) $form->builder_firm_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBuilderAdmin();
    }

    public function update(User $user, Form $form): bool
    {
        return $user->isSuperAdmin() || ($user->builder_firm_id && (int) $user->builder_firm_id === (int) $form->builder_firm_id);
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->isSuperAdmin() || ($user->builder_firm_id && (int) $user->builder_firm_id === (int) $form->builder_firm_id);
    }
}
