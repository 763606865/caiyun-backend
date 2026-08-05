<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\CustomerRequirement;

class CustomerRequirementPolicy
{
    public function viewAny(AdminUser $admin): bool
    {
        return $admin->can('customer-requirements.view');
    }

    public function view(AdminUser $admin, CustomerRequirement $requirement): bool
    {
        return $admin->can('customer-requirements.view');
    }

    public function create(AdminUser $admin): bool
    {
        return $admin->can('customer-requirements.create');
    }

    public function update(AdminUser $admin, CustomerRequirement $requirement): bool
    {
        return $admin->can('customer-requirements.update');
    }

    public function delete(AdminUser $admin, CustomerRequirement $requirement): bool
    {
        return $admin->can('customer-requirements.delete');
    }

    public function deleteAny(AdminUser $admin): bool
    {
        return $admin->can('customer-requirements.delete');
    }

    public function restore(AdminUser $admin, CustomerRequirement $requirement): bool
    {
        return false;
    }

    public function forceDelete(AdminUser $admin, CustomerRequirement $requirement): bool
    {
        return false;
    }
}
