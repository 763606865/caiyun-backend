<?php

namespace Database\Seeders;

use App\Models\PlatformApplication;
use Illuminate\Database\Seeder;

class PlatformApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'code' => 'oa',
                'name' => '彩云OA',
                'base_url' => 'https://caiyun-oa.codingcloud.com.cn',
            ],
            [
                'code' => 'crm',
                'name' => '彩云CRM',
                'base_url' => 'https://caiyun-crm.codingcloud.com.cn',
            ],
            [
                'code' => 'erp',
                'name' => '彩云ERP',
                'base_url' => 'https://caiyun-erp.codingcloud.com.cn',
            ],
            [
                'code' => 'mes',
                'name' => '彩云MES',
                'base_url' => 'https://caiyun-mes.codingcloud.com.cn',
            ],
            [
                'code' => 'wms',
                'name' => '彩云WMS',
                'base_url' => 'https://caiyun-wms.codingcloud.com.cn',
            ],
        ];
        foreach ($data as $app) {
            if (PlatformApplication::query()->where('code', $app['code'])->exists()) {
                continue;
            }

            // DatabaseSeeder 会关闭模型事件，因此显式生成凭证。
            $application = new PlatformApplication;
            $application->forceFill([
                'code' => $app['code'],
                'name' => $app['name'],
                'base_url' => $app['base_url'],
                'is_enabled' => true,
                'app_key' => bin2hex(random_bytes(16)),
                'app_secret' => bin2hex(random_bytes(32)),
            ])->save();
        }
    }
}
