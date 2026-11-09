<?php

namespace App\Admin\Resources\PlatformEnterprises\Pages;

use App\Admin\Resources\PlatformEnterprises\PlatformEnterpriseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ManagePlatformEnterprises extends ListRecords
{
    protected static string $resource = PlatformEnterpriseResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
