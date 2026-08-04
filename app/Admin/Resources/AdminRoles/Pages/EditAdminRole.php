<?php

namespace App\Admin\Resources\AdminRoles\Pages;

use App\Admin\Resources\AdminRoles\AdminRoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdminRole extends EditRecord
{
    protected static string $resource = AdminRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
