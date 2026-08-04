<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\User;

class UserPolicy
{
    public function viewAny(AdminUser $admin): bool
    {
        return $admin->can('users.view');
    }

    public function view(AdminUser $admin, User $user): bool
    {
        return $admin->can('users.view');
    }

    public function create(AdminUser $admin): bool
    {
        return $admin->can('users.create');
    }

    public function update(AdminUser $admin, User $user): bool
    {
        return $admin->can('users.update');
    }

    public function delete(AdminUser $admin, User $user): bool
    {
        return $admin->can('users.delete');
    }

    public function deleteAny(AdminUser $admin): bool
    {
        return $admin->can('users.delete');
    }
}
