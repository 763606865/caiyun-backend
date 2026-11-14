<?php

namespace App\Admin\Resources\PlatformApplicationGrants\Pages;

use App\Admin\Resources\PlatformApplicationGrants\PlatformApplicationGrantsResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePlatformApplicationGrants extends ManageRecords
{
    protected static string $resource = PlatformApplicationGrantsResource::class;
}
