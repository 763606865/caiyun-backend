<?php

namespace App\Admin\Resources\CustomerRequirements\Pages;

use App\Admin\Resources\CustomerRequirements\CustomerRequirementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerRequirements extends ListRecords
{
    protected static string $resource = CustomerRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
