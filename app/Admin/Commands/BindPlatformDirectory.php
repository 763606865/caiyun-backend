<?php

namespace App\Admin\Commands;

use App\Admin\Commands\Concerns\ReadsIntegrationOptions;
use App\Models\PlatformApplication;
use App\Models\PlatformEmployee;
use App\Models\PlatformOrganization;
use App\Services\Platform\DirectoryService;
use App\Services\Platform\IntegrationAccess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/** 历史绑定必须由本地运维显式确认，不以手机号或姓名自动合并。 */
class BindPlatformDirectory extends Command
{
    use ReadsIntegrationOptions;

    protected $signature = 'platform:directory-bind {application} {entity_type} {source_id} {entity_uuid} {--enterprise= : 所属企业UUID} {--apply : 不传时只预检}';

    protected $description = '预检或显式确认历史目录来源与统一身份的绑定';

    public function handle(IntegrationAccess $access, DirectoryService $directory): int
    {
        $enterpriseUuid = $this->stringOption('enterprise');
        if (! in_array($this->stringArgument('entity_type'), ['enterprise', 'user', 'organization', 'employment'], true) || $enterpriseUuid === null) {
            $this->error('需要合法类型和所属企业');

            return self::FAILURE;
        }
        $app = PlatformApplication::query()->where('code', $this->stringArgument('application'))->firstOrFail();
        $enterprise = $access->enterprise($enterpriseUuid);
        $type = $this->stringArgument('entity_type');
        $record = $directory->record($type, $this->stringArgument('entity_uuid'));
        if (($type === 'enterprise' && $enterprise->id !== $record->id) || (($record instanceof PlatformOrganization || $record instanceof PlatformEmployee) && $enterprise->id !== $record->enterprise_id)) {
            $this->error('绑定对象不属于该企业');

            return self::FAILURE;
        }
        $keys = ['application_id' => $app->id, 'entity_type' => $type, 'source_id' => $this->stringArgument('source_id')];
        $existing = DB::table('platform_directory_sources')->where($keys)->first();
        if ($existing && $existing->entity_uuid !== $record->uuid) {
            $this->error('来源已绑定其他统一身份，禁止覆盖');

            return self::FAILURE;
        }
        if ($this->option('apply') && ! $existing) {
            DB::transaction(function () use ($keys, $enterprise, $record, $access, $app): void {
                DB::table('platform_directory_sources')->insert([...$keys, 'enterprise_id' => $enterprise->id, 'entity_uuid' => $record->uuid, 'source_version' => 0, 'payload_hash' => hash('sha256', '')]);
                $access->audit($app, 'directory.bind', $enterprise->id, 200, null, $record->uuid);
            });
        }
        $this->info($this->option('apply') ? '已确认绑定；未修改业务记录' : '预检通过；未写入，使用 --apply 确认');

        return self::SUCCESS;
    }
}
