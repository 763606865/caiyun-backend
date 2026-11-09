<?php

namespace App\Admin\Resources\PlatformApplications\Pages;

use App\Admin\Resources\PlatformApplications\PlatformApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePlatformApplications extends ManageRecords
{
    protected static string $resource = PlatformApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
