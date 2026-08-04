<?php

namespace App\Admin\Resources\ClientVersions\Pages;

use App\Admin\Resources\ClientVersions\ClientVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientVersions extends ListRecords
{
    protected static string $resource = ClientVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
