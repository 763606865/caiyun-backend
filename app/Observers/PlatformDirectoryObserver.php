<?php

namespace App\Observers;

use App\Models\PlatformEmployee;
use App\Models\PlatformEnterprise;
use App\Models\PlatformOrganization;
use App\Models\User;
use App\Services\Platform\DirectoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** 补齐 Admin/旧 CRUD 的目录变化；批量 SQL 必须走目录服务或显式补发。 */
class PlatformDirectoryObserver
{
    public function saved(PlatformEnterprise|PlatformOrganization|PlatformEmployee|User $record): void
    {
        $this->record($record, false);
    }

    public function deleted(PlatformEnterprise|PlatformOrganization|PlatformEmployee|User $record): void
    {
        $this->record($record, true);
    }

    private function record(PlatformEnterprise|PlatformOrganization|PlatformEmployee|User $record, bool $deleted): void
    {
        if (DirectoryService::$writing || ! Schema::hasTable('platform_directory_changes')) {
            return;
        }
        $type = match (true) {
            $record instanceof PlatformEnterprise => 'enterprise',
            $record instanceof PlatformOrganization => 'organization',
            $record instanceof PlatformEmployee => 'employment',
            $record instanceof User => 'user',
        };
        $enterpriseIds = match (true) {
            $record instanceof PlatformEnterprise => [$record->id],
            $record instanceof User => PlatformEmployee::query()->where('user_id', $record->id)->pluck('enterprise_id')->all(),
            default => [$record->enterprise_id],
        };
        foreach (array_filter($enterpriseIds) as $enterpriseId) {
            $enterprise = PlatformEnterprise::query()->whereKey($enterpriseId)->first();
            if (! $enterprise) {
                continue;
            }
            $payload = app(DirectoryService::class)->project($type, $record, $enterprise->uuid);
            if ($deleted) {
                $payload['is_enabled'] = false;
                $payload['deleted'] = true;
            }
            DB::table('platform_directory_changes')->insert(['enterprise_id' => $enterpriseId, 'entity_type' => $type,
                'entity_uuid' => $record->uuid, 'payload' => json_encode($payload), 'created_at' => now()]);
        }
    }
}
