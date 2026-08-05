<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Category;

class CategoryPolicy
{
    public function viewAny(AdminUser $u): bool
    {
        return $u->can('categories.view');
    }

    public function view(AdminUser $u, Category $m): bool
    {
        return $u->can('categories.view');
    }

    public function create(AdminUser $u): bool
    {
        return $u->can('categories.create');
    }

    public function update(AdminUser $u, Category $m): bool
    {
        return $u->can('categories.update');
    }

    public function delete(AdminUser $u, Category $m): bool
    {
        return $u->can('categories.delete');
    }

    public function deleteAny(AdminUser $u): bool
    {
        return $u->can('categories.delete');
    }
}
