<?php

namespace App\Policies;

use App\Models\AdminUser;

class AdminUserPolicy
{
    public function viewAny(AdminUser $admin): bool
    {
        return $admin->can('admin-users.view');
    }

    public function view(AdminUser $admin, AdminUser $model): bool
    {
        return $admin->can('admin-users.view');
    }

    public function create(AdminUser $admin): bool
    {
        return $admin->can('admin-users.create');
    }

    public function update(AdminUser $admin, AdminUser $model): bool
    {
        return $admin->can('admin-users.update');
    }

    public function delete(AdminUser $admin, AdminUser $model): bool
    {
        return $admin->isNot($model) && $admin->can('admin-users.delete');
    }

    public function deleteAny(AdminUser $admin): bool
    {
        return false;
    }
}
