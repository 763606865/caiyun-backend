<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Media;

class MediaPolicy
{
    public function viewAny(AdminUser $u): bool
    {
        return $u->can('media.view');
    }

    public function view(AdminUser $u, Media $m): bool
    {
        return $u->can('media.view');
    }

    public function create(AdminUser $u): bool
    {
        return $u->can('media.create');
    }

    public function update(AdminUser $u, Media $m): bool
    {
        return $u->can('media.update');
    }

    public function delete(AdminUser $u, Media $m): bool
    {
        return $u->can('media.delete');
    }

    public function deleteAny(AdminUser $u): bool
    {
        return $u->can('media.delete');
    }
}
