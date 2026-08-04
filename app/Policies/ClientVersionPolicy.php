<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\ClientVersion;

class ClientVersionPolicy
{
    public function viewAny(AdminUser $admin): bool
    {
        return $admin->can('client-versions.view');
    }

    public function view(AdminUser $admin, ClientVersion $clientVersion): bool
    {
        return $admin->can('client-versions.view');
    }

    public function create(AdminUser $admin): bool
    {
        return $admin->can('client-versions.create');
    }

    public function update(AdminUser $admin, ClientVersion $clientVersion): bool
    {
        return $admin->can('client-versions.update');
    }

    public function delete(AdminUser $admin, ClientVersion $clientVersion): bool
    {
        return $admin->can('client-versions.delete');
    }

    public function deleteAny(AdminUser $admin): bool
    {
        return $admin->can('client-versions.delete');
    }

    public function restore(AdminUser $admin, ClientVersion $clientVersion): bool
    {
        return false;
    }

    public function forceDelete(AdminUser $admin, ClientVersion $clientVersion): bool
    {
        return false;
    }
}
