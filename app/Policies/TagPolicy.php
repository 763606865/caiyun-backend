<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Tag;

class TagPolicy
{
    public function viewAny(AdminUser $u): bool
    {
        return $u->can('tags.view');
    }

    public function view(AdminUser $u, Tag $m): bool
    {
        return $u->can('tags.view');
    }

    public function create(AdminUser $u): bool
    {
        return $u->can('tags.create');
    }

    public function update(AdminUser $u, Tag $m): bool
    {
        return $u->can('tags.update');
    }

    public function delete(AdminUser $u, Tag $m): bool
    {
        return $u->can('tags.delete');
    }

    public function deleteAny(AdminUser $u): bool
    {
        return $u->can('tags.delete');
    }
}
