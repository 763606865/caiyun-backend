<?php

namespace App\Admin\Resources\CustomerRequirements\Pages;

use App\Admin\Resources\CustomerRequirements\CustomerRequirementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerRequirement extends CreateRecord
{
    protected static string $resource = CustomerRequirementResource::class;
}
