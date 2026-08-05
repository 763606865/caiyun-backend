<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Content;

class ContentPolicy
{
    public function viewAny(AdminUser $u): bool
    {
        return $u->can('contents.view');
    }

    public function view(AdminUser $u, Content $m): bool
    {
        return $u->can('contents.view');
    }

    public function create(AdminUser $u): bool
    {
        return $u->can('contents.create');
    }

    public function update(AdminUser $u, Content $m): bool
    {
        return $u->can('contents.update');
    }

    public function delete(AdminUser $u, Content $m): bool
    {
        return $u->can('contents.delete');
    }

    public function deleteAny(AdminUser $u): bool
    {
        return $u->can('contents.delete');
    }

    public function restore(AdminUser $u, Content $m): bool
    {
        return $u->can('contents.restore');
    }

    public function restoreAny(AdminUser $u): bool
    {
        return $u->can('contents.restore');
    }

    public function forceDelete(AdminUser $u, Content $m): bool
    {
        return $u->can('contents.delete');
    }

    public function forceDeleteAny(AdminUser $u): bool
    {
        return $u->can('contents.delete');
    }
}
