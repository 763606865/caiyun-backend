<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\OperationLog;

class OperationLogPolicy
{
    public function viewAny(AdminUser $u): bool
    {
        return $u->can('operation-logs.view');
    }

    public function view(AdminUser $u, OperationLog $m): bool
    {
        return $u->can('operation-logs.view');
    }

    public function create(AdminUser $u): bool
    {
        return false;
    }

    public function update(AdminUser $u, OperationLog $m): bool
    {
        return false;
    }

    public function delete(AdminUser $u, OperationLog $m): bool
    {
        return false;
    }

    public function deleteAny(AdminUser $u): bool
    {
        return false;
    }
}
