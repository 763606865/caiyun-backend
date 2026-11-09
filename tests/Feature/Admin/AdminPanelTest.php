<?php

namespace Tests\Feature\Admin;

use App\Admin\Resources\PlatformEnterprises\Pages\EditPlatformEnterprise;
use App\Admin\Resources\PlatformEnterprises\RelationManagers\EmployeesRelationManager;
use App\Admin\Resources\PlatformEnterprises\RelationManagers\SubscriptionsRelationManager;
use App\Models\AdminUser;
use App\Models\PlatformEnterprise;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_available(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_inactive_admin_cannot_access_the_panel(): void
    {
        $admin = AdminUser::query()->create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@example.com',
            'password' => 'password',
            'is_active' => false,
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_super_admin_can_access_all_management_resources(): void
    {
        $this->seed(AdminAuthorizationSeeder::class);

        $admin = AdminUser::query()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $admin->assignRole('super-admin');

        $this->actingAs($admin, 'admin');

        $this->get('/admin')->assertOk();
        foreach (['platform-applications', 'platform-enterprises', 'platform-organizations'] as $resource) {
            $this->get('/admin/'.$resource)->assertOk();
        }
        $this->get('/admin/admin-users')->assertOk();
        $this->get('/admin/admin-roles')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/client-versions')->assertOk();
        $this->get('/admin/system-settings')->assertOk();
        $this->get('/admin/system-settings/create')->assertOk();

        $enterprise = PlatformEnterprise::query()->create([
            'code' => 'admin-test',
            'name' => '测试企业',
        ]);
        $this->get('/admin/platform-enterprises/'.$enterprise->id.'/edit')->assertOk();

        foreach ([
            EmployeesRelationManager::class,
            SubscriptionsRelationManager::class,
        ] as $manager) {
            Livewire::test($manager, [
                'ownerRecord' => $enterprise,
                'pageClass' => EditPlatformEnterprise::class,
            ])->assertStatus(200)->mountAction('create')->assertStatus(200);
        }

    }

    public function test_admin_create_command_creates_a_super_admin(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'System Admin',
            '--email' => 'system@example.com',
            '--password' => 'password123',
        ])->assertSuccessful();

        $admin = AdminUser::query()->where('email', 'system@example.com')->firstOrFail();

        $this->assertTrue($admin->hasRole('super-admin'));
    }
}
