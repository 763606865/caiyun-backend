<?php

namespace App\Admin\Resources\PlatformSubscriptions\Pages;

use App\Admin\Resources\PlatformSubscriptions\PlatformSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePlatformSubscriptions extends ManageRecords
{
    protected static string $resource = PlatformSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
