<?php

namespace App\Admin\Resources\Media\Pages;

use App\Admin\Resources\Media\MediaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $disk = Storage::disk('public');
        $path = $data['path'];
        $data += ['disk' => 'public', 'name' => basename($path), 'mime_type' => $disk->mimeType($path) ?: null, 'extension' => pathinfo($path, PATHINFO_EXTENSION), 'size' => $disk->size($path), 'hash' => hash_file('sha256', $disk->path($path)), 'admin_user_id' => Auth::guard('admin')->id()];

        return $data;
    }
}
