<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\SystemSetting;

class SystemSettingPolicy
{
    public function viewAny(AdminUser $admin): bool
    {
        return $admin->can('system-settings.view');
    }

    public function view(AdminUser $admin, SystemSetting $systemSetting): bool
    {
        return $admin->can('system-settings.view');
    }

    public function create(AdminUser $admin): bool
    {
        return $admin->can('system-settings.create');
    }

    public function update(AdminUser $admin, SystemSetting $systemSetting): bool
    {
        return $admin->can('system-settings.update');
    }

    public function delete(AdminUser $admin, SystemSetting $systemSetting): bool
    {
        return false;
    }

    public function deleteAny(AdminUser $admin): bool
    {
        return false;
    }

    public function restore(AdminUser $admin, SystemSetting $systemSetting): bool
    {
        return false;
    }

    public function forceDelete(AdminUser $admin, SystemSetting $systemSetting): bool
    {
        return false;
    }
}
