<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\LicensePackage;

class LicensePackagePolicy
{
    public function viewAny(AdminUser $admin): bool
    {
        return $admin->can('licenses.view');
    }

    public function view(AdminUser $admin, LicensePackage $license): bool
    {
        return $admin->can('licenses.view');
    }

    public function create(AdminUser $admin): bool
    {
        return $admin->can('licenses.create');
    }

    public function update(AdminUser $admin, LicensePackage $license): bool
    {
        return $admin->can('licenses.update');
    }

    public function delete(AdminUser $admin, LicensePackage $license): bool
    {
        return $admin->can('licenses.delete');
    }

    public function deleteAny(AdminUser $admin): bool
    {
        return $admin->can('licenses.delete');
    }

    public function restore(AdminUser $admin, LicensePackage $license): bool
    {
        return false;
    }

    public function forceDelete(AdminUser $admin, LicensePackage $license): bool
    {
        return false;
    }
}
