<?php

namespace Tests\Feature\Models;

use App\Admin\Resources\SystemSettings\SystemSettingResource;
use App\Models\AdminUser;
use App\Models\SystemSetting;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_and_casts_system_settings(): void
    {
        $setting = SystemSetting::query()->create([
            'site_name' => '测试站点',
            'customer_service_phone' => '400-123-4567',
            'icp_number' => '京ICP备12345678号',
            'sms_enabled' => false,
            'registration_enabled' => true,
            'upload_max_size_mb' => 20,
            'payment_enabled' => true,
            'maintenance_message' => '系统维护中',
        ]);

        $this->assertSame(SystemSetting::DEFAULT_KEY, $setting->key);
        $this->assertFalse($setting->sms_enabled);
        $this->assertTrue($setting->registration_enabled);
        $this->assertSame(20, $setting->upload_max_size_mb);
        $this->assertTrue($setting->payment_enabled);
        $this->assertTrue($setting->is(SystemSetting::current()));
    }

    public function test_model_defaults_and_admin_singleton_creation_rule(): void
    {
        $this->seed(AdminAuthorizationSeeder::class);
        $admin = AdminUser::query()->create([
            'name' => 'System Admin',
            'email' => 'system-setting@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $admin->assignRole('super-admin');
        $this->actingAs($admin, 'admin');

        $setting = SystemSetting::current();

        $this->assertFalse($setting->exists);
        $this->assertTrue($setting->sms_enabled);
        $this->assertTrue($setting->registration_enabled);
        $this->assertSame(10, $setting->upload_max_size_mb);
        $this->assertFalse($setting->payment_enabled);
        $this->assertTrue(SystemSettingResource::canCreate());

        SystemSetting::query()->create(['site_name' => '唯一站点']);

        $this->assertFalse(SystemSettingResource::canCreate());
    }
}
