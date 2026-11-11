<?php

namespace Tests\Feature\Models;

use App\Enums\OrganizationType;
use App\Models\PlatformOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrganizationTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_types_are_normalized_for_enum_casting(): void
    {
        $cases = [
            ['企业', OrganizationType::Enterprise],
            ['学校/教育机构', OrganizationType::School],
            ['department', OrganizationType::Other],
            ['ENTERPRISE', OrganizationType::Other],
            ['law_firm', OrganizationType::LawFirm],
            [null, null],
        ];
        $records = [];

        foreach ($cases as $index => [$legacyType, $expectedType]) {
            $record = PlatformOrganization::query()->create(['code' => 'legacy-'.$index, 'name' => '历史组织']);
            DB::table('platform_organizations')->where('id', $record->id)->update(['primary_type' => $legacyType]);
            $records[] = [$record->id, $expectedType];
        }

        $migration = require database_path('migrations/2026_11_12_000003_normalize_organization_primary_types.php');
        $migration->up();
        $migration->up();

        foreach ($records as [$id, $expectedType]) {
            $record = PlatformOrganization::query()->findOrFail($id);
            $this->assertSame($expectedType, $record->primary_type);
            $this->assertSame($expectedType?->value, $record->toArray()['primary_type']);
        }
    }
}
