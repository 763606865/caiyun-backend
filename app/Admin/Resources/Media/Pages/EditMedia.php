<?php

namespace App\Admin\Resources\Media\Pages;

use App\Admin\Resources\Media\MediaResource;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;
}
