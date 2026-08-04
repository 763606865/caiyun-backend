<?php

namespace App\Admin\Resources\ClientVersions\Pages;

use App\Admin\Resources\ClientVersions\ClientVersionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClientVersion extends CreateRecord
{
    protected static string $resource = ClientVersionResource::class;
}
