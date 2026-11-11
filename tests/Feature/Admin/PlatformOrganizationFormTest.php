<?php

namespace Tests\Feature\Admin;

use App\Admin\Resources\PlatformOrganizations\Pages\ManagePlatformOrganizations;
use App\Models\AdminUser;
use App\Models\PlatformOrganization;
use Database\Seeders\AdminAuthorizationSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformOrganizationFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(AdminAuthorizationSeeder::class);
        $admin = AdminUser::query()->create([
            'name' => 'Organization Admin',
            'email' => 'organization-admin@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $admin->assignRole('super-admin');
        $this->actingAs($admin, 'admin');
    }

    public function test_regions_hydrate_cascade_and_save_without_persisting_helper_fields(): void
    {
        $organization = $this->organization(['area_code' => '110101']);
        $form = Livewire::test(ManagePlatformOrganizations::class)
            ->mountAction(TestAction::make('edit')->table($organization))
            ->assertSchemaStateSet([
                'province_code' => '110000', 'city_code' => '110100',
                'district_code' => '110101', 'area_code' => '110101',
            ])
            ->set('mountedActions.0.data.province_code', '440000')
            ->assertSchemaStateSet(['city_code' => null, 'district_code' => null, 'area_code' => '440000'])
            ->assertSchemaComponentExists('city_code', checkComponentUsing: fn (Select $component): bool => isset($component->getOptions()['440100']) && ! isset($component->getOptions()['110100']))
            ->set('mountedActions.0.data.city_code', '440100')
            ->assertSchemaStateSet(['district_code' => null, 'area_code' => '440100'])
            ->set('mountedActions.0.data.district_code', '440106')
            ->assertSchemaStateSet(['area_code' => '440106'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertSame('440106', $organization->fresh()->area_code);

        $form->mountAction(TestAction::make('edit')->table($organization))
            ->assertSchemaStateSet(['province_code' => '440000', 'city_code' => '440100', 'district_code' => '440106'])
            ->set('mountedActions.0.data.city_code', null)
            ->assertSchemaStateSet(['district_code' => null, 'area_code' => '440000'])
            ->set('mountedActions.0.data.province_code', null)
            ->assertSchemaStateSet(['city_code' => null, 'district_code' => null, 'area_code' => null])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertNull($organization->fresh()->area_code);
    }

    public function test_province_only_codes_and_cities_without_districts_can_be_saved(): void
    {
        $organization = $this->organization(['area_code' => '310000']);
        $form = Livewire::test(ManagePlatformOrganizations::class)
            ->mountAction(TestAction::make('edit')->table($organization))
            ->assertSchemaStateSet(['province_code' => '310000', 'city_code' => null, 'district_code' => null])
            ->setActionData(['remark' => '保留原地区码'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertSame('310000', $organization->fresh()->area_code);

        $form->mountAction(TestAction::make('edit')->table($organization))
            ->set('mountedActions.0.data.province_code', '440000')
            ->set('mountedActions.0.data.city_code', '441900')
            ->assertSchemaComponentExists('district_code', checkComponentUsing: fn (Select $component): bool => $component->isDisabled())
            ->assertSchemaStateSet(['district_code' => null, 'area_code' => '441900'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertSame('441900', $organization->fresh()->area_code);
    }

    public function test_districts_from_a_different_city_are_rejected(): void
    {
        $organization = $this->organization(['area_code' => '110101']);
        Livewire::test(ManagePlatformOrganizations::class)
            ->mountAction(TestAction::make('edit')->table($organization))
            ->set('mountedActions.0.data.district_code', '440106')
            ->callMountedAction()
            ->assertHasActionErrors(['district_code']);

        $this->assertSame('110101', $organization->fresh()->area_code);
    }

    public function test_logo_images_and_verification_status_can_be_saved(): void
    {
        $organization = $this->organization();
        Livewire::test(ManagePlatformOrganizations::class)
            ->mountAction(TestAction::make('edit')->table($organization))
            ->setActionData(['logo' => UploadedFile::fake()->image('logo.png'), 'verified_status' => 'verified'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $record = $organization->fresh();
        $this->assertSame('verified', $record->verified_status);
        $this->assertStringStartsWith('organizations/logos/', $record->logo);
        Storage::disk('public')->assertExists($record->logo);

        Livewire::test(ManagePlatformOrganizations::class)
            ->assertTableColumnFormattedStateSet('verified_status', '已认证', $record)
            ->mountAction(TestAction::make('edit')->table($record))
            ->assertSchemaStateSet(['logo' => $record->logo, 'verified_status' => 'verified'])
            ->setActionData(['remark' => '保留已有图片'])
            ->callMountedAction()
            ->assertHasNoActionErrors();
        $this->assertSame($record->logo, $record->fresh()->logo);
    }

    public function test_non_image_logos_and_unknown_verification_statuses_are_rejected(): void
    {
        $organization = $this->organization();
        Livewire::test(ManagePlatformOrganizations::class)
            ->mountAction(TestAction::make('edit')->table($organization))
            ->setActionData(['logo' => UploadedFile::fake()->create('logo.txt', 1, 'text/plain'), 'verified_status' => 'unknown'])
            ->callMountedAction()
            ->assertHasActionErrors(['logo', 'verified_status']);

        $this->assertNull($organization->fresh()->logo);
        $this->assertSame('unverified', $organization->fresh()->verified_status);
    }

    public function test_existing_remote_logos_and_legacy_statuses_are_preserved(): void
    {
        $url = 'https://example.com/logo.png';
        $organization = $this->organization(['logo' => $url, 'verified_status' => 'legacy-status']);
        Livewire::test(ManagePlatformOrganizations::class)
            ->mountAction(TestAction::make('edit')->table($organization))
            ->assertSchemaStateSet(['logo' => $url, 'verified_status' => 'legacy-status'])
            ->assertSchemaComponentExists('logo', checkComponentUsing: fn (FileUpload $component): bool => array_values($component->getUploadedFiles())[0]['url'] === $url)
            ->setActionData(['remark' => '只更新备注'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertSame($url, $organization->fresh()->logo);
        $this->assertSame('legacy-status', $organization->fresh()->verified_status);
    }

    public function test_organizations_can_be_created_with_region_and_default_verification_status(): void
    {
        Livewire::test(ManagePlatformOrganizations::class)
            ->mountAction('create')
            ->assertSchemaStateSet(['verified_status' => 'unverified'])
            ->setActionData(['code' => 'new-org', 'name' => '新组织', 'primary_type' => 'school'])
            ->set('mountedActions.0.data.province_code', '110000')
            ->set('mountedActions.0.data.city_code', '110100')
            ->set('mountedActions.0.data.district_code', '110101')
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('platform_organizations', ['code' => 'new-org', 'area_code' => '110101', 'verified_status' => 'unverified', 'enterprise_id' => null]);
    }

    /** @param array<string, mixed> $attributes */
    private function organization(array $attributes = []): PlatformOrganization
    {
        return PlatformOrganization::query()->create([
            'code' => 'test-org', 'name' => '测试组织', 'verified_status' => 'unverified',
            ...$attributes,
        ]);
    }
}
