<?php

namespace App\Admin\Resources\PlatformApplicationScopes\Pages;

use App\Admin\Resources\PlatformApplicationScopes\PlatformApplicationScopesResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePlatformApplicationScopes extends ManageRecords
{
    protected static string $resource = PlatformApplicationScopesResource::class;
}
