<?php

namespace Database\Seeders;

use App\Models\AdminPermission;
use App\Models\AdminRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AdminAuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'admin-users.view',
            'admin-users.create',
            'admin-users.update',
            'admin-users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'client-versions.view',
            'client-versions.create',
            'client-versions.update',
            'client-versions.delete',
            'system-settings.view',
            'system-settings.create',
            'system-settings.update',
        ];

        foreach ($permissions as $permission) {
            AdminPermission::findOrCreate($permission, 'admin');
        }

        AdminRole::findOrCreate('super-admin', 'admin')
            ->syncPermissions(AdminPermission::query()->where('guard_name', 'admin')->get());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
