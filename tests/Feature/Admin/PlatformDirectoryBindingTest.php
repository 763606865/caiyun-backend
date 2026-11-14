<?php

namespace Tests\Feature\Admin;

use App\Admin\Resources\PlatformApplicationGrants\PlatformApplicationGrantsResource;
use App\Admin\Resources\PlatformApplicationScopes\PlatformApplicationScopesResource;
use App\Admin\Resources\PlatformDirectorySources\Pages\ManagePlatformDirectorySources;
use App\Admin\Resources\PlatformDirectorySources\PlatformDirectorySourcesResource;
use App\Admin\Resources\PlatformIntegrationAudits\PlatformIntegrationAuditsResource;
use App\Models\AdminUser;
use App\Models\PlatformApplication;
use App\Models\PlatformDirectorySource;
use App\Models\PlatformEnterprise;
use App\Models\PlatformOrganization;
use App\Models\User;
use App\Services\Platform\ManualDirectoryBinding;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PlatformDirectoryBindingTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(bool $super = true): AdminUser
    {
        $this->seed(AdminAuthorizationSeeder::class);
        $admin = AdminUser::query()->create(['name' => 'Directory Admin', 'email' => ($super ? 'super' : 'ordinary').'@example.test', 'password' => 'password', 'is_active' => true]);
        if ($super) {
            $admin->assignRole('super-admin');
        }

        return $admin;
    }

    private function data(): array
    {
        $app = PlatformApplication::query()->create(['code' => 'test-source', 'name' => 'Test Source', 'base_url' => '', 'is_enabled' => true]);
        $enterprise = PlatformEnterprise::query()->create(['code' => 'A', 'name' => 'Enterprise A']);
        $user = User::factory()->create();

        return ['application_id' => $app->id, 'enterprise_uuid' => $enterprise->uuid, 'entity_type' => 'user', 'source_id' => 'user:42', 'entity_uuid' => $user->uuid, 'apply' => false];
    }

    public function test_preflight_never_creates_mapping_and_confirm_is_idempotent(): void
    {
        $admin = $this->administrator();
        $data = $this->data();
        $service = app(ManualDirectoryBinding::class);
        $this->assertFalse($service->execute($admin, $data)['applied']);
        $this->assertDatabaseCount('platform_directory_sources', 0);
        $this->assertDatabaseHas('platform_integration_audits', ['action' => 'admin.directory.preflight', 'status' => 200]);
        $data['apply'] = true;
        $this->assertFalse($service->execute($admin, $data)['already_bound']);
        $this->assertTrue($service->execute($admin, $data)['already_bound']);
        $this->assertDatabaseCount('platform_directory_sources', 1);
        $this->assertDatabaseHas('platform_integration_audits', ['action' => 'admin.directory.bind', 'object_key' => 'admin:'.$admin->id.':user:user:42']);
    }

    public function test_binding_conflicts_are_rejected_and_audit_survives_rollback(): void
    {
        $admin = $this->administrator();
        $data = [...$this->data(), 'apply' => true];
        $service = app(ManualDirectoryBinding::class);
        $service->execute($admin, $data);
        $original = $data['entity_uuid'];
        $data['entity_uuid'] = User::factory()->create()->uuid;
        try {
            $service->execute($admin, $data);
            $this->fail('conflict was not rejected');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('source_id', $exception->errors());
        }
        $this->assertDatabaseHas('platform_directory_sources', ['source_id' => 'user:42', 'entity_uuid' => $original]);
        $this->assertDatabaseCount('platform_directory_sources', 1);
        $this->assertDatabaseHas('platform_integration_audits', ['action' => 'admin.directory.bind.rejected', 'status' => 409]);
    }

    public function test_cannot_bind_cross_enterprise_organization(): void
    {
        $admin = $this->administrator();
        $data = $this->data();
        $b = PlatformEnterprise::query()->create(['code' => 'B', 'name' => 'Enterprise B']);
        $org = PlatformOrganization::query()->create(['enterprise_id' => $b->id, 'code' => 'B-sales', 'name' => 'B销售']);
        $data = [...$data, 'entity_type' => 'organization', 'source_id' => 'org:42', 'entity_uuid' => $org->uuid, 'apply' => true];
        try {
            app(ManualDirectoryBinding::class)->execute($admin, $data);
            $this->fail('cross-enterprise binding was not rejected');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('entity_uuid', $exception->errors());
        }
        $this->assertDatabaseCount('platform_directory_sources', 0);
    }

    public function test_non_super_admin_cannot_access_pages_or_execute_service(): void
    {
        $ordinary = $this->administrator(false);
        $data = $this->data();
        $this->actingAs($ordinary, 'admin');
        foreach ([PlatformDirectorySourcesResource::class, PlatformApplicationScopesResource::class, PlatformApplicationGrantsResource::class, PlatformIntegrationAuditsResource::class] as $resource) {
            $this->assertFalse($resource::canAccess());
        }
        Livewire::test(ManagePlatformDirectorySources::class)->assertForbidden();
        $this->expectException(HttpException::class);
        app(ManualDirectoryBinding::class)->execute($ordinary, [...$data, 'apply' => true]);
    }

    public function test_admin_can_preflight_and_confirm_through_filament_without_generic_editing(): void
    {
        $admin = $this->administrator();
        $data = $this->data();
        $this->actingAs($admin, 'admin');
        $page = Livewire::test(ManagePlatformDirectorySources::class)
            ->mountAction('bindSource')->setActionData($data)->callMountedAction()->assertHasNoActionErrors();
        $this->assertDatabaseCount('platform_directory_sources', 0);
        $page->mountAction('bindSource')->setActionData([...$data, 'apply' => true])->callMountedAction()->assertHasNoActionErrors();
        $record = PlatformDirectorySource::query()->sole();
        $page->assertCanSeeTableRecords([$record]);
        $this->assertFalse(PlatformDirectorySourcesResource::canCreate());
        $this->assertFalse(PlatformDirectorySourcesResource::canEdit($record));
        $this->assertFalse(PlatformDirectorySourcesResource::canDelete($record));
    }
}
