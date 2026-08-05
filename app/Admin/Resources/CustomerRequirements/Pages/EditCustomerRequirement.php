<?php

namespace App\Admin\Resources\CustomerRequirements\Pages;

use App\Admin\Resources\CustomerRequirements\CustomerRequirementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerRequirement extends EditRecord
{
    protected static string $resource = CustomerRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
