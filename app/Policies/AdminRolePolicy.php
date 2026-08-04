<?php

namespace App\Policies;

use App\Models\AdminRole;
use App\Models\AdminUser;

class AdminRolePolicy
{
    public function viewAny(AdminUser $admin): bool
    {
        return $admin->can('roles.view');
    }

    public function view(AdminUser $admin, AdminRole $role): bool
    {
        return $admin->can('roles.view');
    }

    public function create(AdminUser $admin): bool
    {
        return $admin->can('roles.create');
    }

    public function update(AdminUser $admin, AdminRole $role): bool
    {
        return $role->name !== 'super-admin' && $admin->can('roles.update');
    }

    public function delete(AdminUser $admin, AdminRole $role): bool
    {
        return $role->name !== 'super-admin' && $admin->can('roles.delete');
    }

    public function deleteAny(AdminUser $admin): bool
    {
        return false;
    }
}
