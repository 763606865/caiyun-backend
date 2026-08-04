<?php

namespace App\Admin\Resources\ClientVersions\Pages;

use App\Admin\Resources\ClientVersions\ClientVersionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientVersion extends EditRecord
{
    protected static string $resource = ClientVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
