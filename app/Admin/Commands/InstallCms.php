<?php

namespace App\Admin\Commands;

use App\Models\Category;
use App\Models\SystemSetting;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Console\Command;

class InstallCms extends Command
{
    protected $signature = 'cms:install {--site-name= : 站点名称} {--no-admin : 不创建管理员}';

    protected $description = '初始化 CMS 配置、权限、默认栏目和管理员';

    public function handle(): int
    {
        $this->call('migrate', ['--force' => true]);
        $this->callSilent('db:seed', ['--class' => AdminAuthorizationSeeder::class]);
        $siteName = $this->option('site-name') ?: $this->ask('站点名称', config('app.name'));
        SystemSetting::query()->updateOrCreate(['key' => SystemSetting::DEFAULT_KEY], ['site_name' => $siteName]);
        Category::withTrashed()->firstOrCreate(['slug' => 'news'], ['name' => '新闻资讯', 'is_enabled' => true]);
        $this->call('storage:link');
        if (! $this->option('no-admin')) {
            $this->call('admin:create');
        }
        $this->info('CMS 初始化完成，后台地址：'.url('/admin'));

        return self::SUCCESS;
    }
}
