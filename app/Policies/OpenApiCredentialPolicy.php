<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\OpenApiCredential;

class OpenApiCredentialPolicy
{
    public function viewAny(AdminUser $admin): bool
    {
        return $admin->can('openapi-credentials.view');
    }

    public function view(AdminUser $admin, OpenApiCredential $credential): bool
    {
        return $admin->can('openapi-credentials.view');
    }

    public function create(AdminUser $admin): bool
    {
        return $admin->can('openapi-credentials.create');
    }

    public function update(AdminUser $admin, OpenApiCredential $credential): bool
    {
        return $admin->can('openapi-credentials.update');
    }

    public function delete(AdminUser $admin, OpenApiCredential $credential): bool
    {
        return $admin->can('openapi-credentials.delete');
    }

    public function deleteAny(AdminUser $admin): bool
    {
        return $admin->can('openapi-credentials.delete');
    }

    public function restore(AdminUser $admin, OpenApiCredential $credential): bool
    {
        return false;
    }

    public function forceDelete(AdminUser $admin, OpenApiCredential $credential): bool
    {
        return false;
    }
}
