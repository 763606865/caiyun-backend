<?php

namespace Tests\Feature\SApi;

use App\Enums\OrganizationType;
use App\Models\PlatformApplication;
use App\Models\PlatformOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function signed(string $method, string $uri, array $data = [])
    {
        $app = PlatformApplication::query()->firstOrCreate(['code' => 'test'], ['name' => 'Test', 'base_url' => '', 'is_enabled' => true]);
        $body = $data ? json_encode($data) : '';
        $timestamp = (string) now()->timestamp;
        $nonce = Str::random(32);
        $signature = hash_hmac('sha256', implode("\n", [$method, $uri, $timestamp, $nonce, hash('sha256', $body)]), $app->app_secret);

        return $this->call($method, $uri, [], [], [], ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json', 'HTTP_X_APP_KEY' => $app->app_key, 'HTTP_X_TIMESTAMP' => $timestamp, 'HTTP_X_NONCE' => $nonce, 'HTTP_X_SIGNATURE' => $signature], $body);
    }

    public function test_users_sync_without_password_and_return_uuid(): void
    {
        $response = $this->signed('POST', '/sapi/users', ['phone' => '13800138001', 'email' => 'sync@example.com'])->assertCreated();
        $user = User::query()->where('phone', '13800138001')->sole();
        $response->assertExactJson(array_replace($response->json(), ['data' => ['uuid' => $user->uuid]]));
        $password = $user->password;
        $this->assertNotEmpty($password);
        $this->signed('PATCH', '/sapi/users/'.$user->id, ['email' => 'updated@example.com', 'password' => 'ignored-password'])
            ->assertOk()->assertJsonPath('data.uuid', $user->uuid);
        $this->assertSame($password, $user->fresh()->password);
        $this->assertSame('updated@example.com', $user->fresh()->email);
        $this->signed('POST', '/sapi/users', ['phone' => '13800138001'])->assertUnprocessable();
    }

    public function test_directory_records_return_stable_uuids(): void
    {
        $enterprise = $this->signed('POST', '/sapi/enterprises', ['code' => 'uuid-corp', 'name' => '企业'])->assertCreated();
        $enterpriseId = $enterprise->json('data.id');
        $enterprise->assertJsonPath('data.organization.primary_type', 'enterprise');
        $this->assertSame($enterprise->json('data.organization.id'), $enterprise->json('data.organization_id'));
        $this->assertTrue(Str::isUuid($enterprise->json('data.organization.uuid')));
        $this->assertDatabaseHas('platform_organizations', ['id' => $enterprise->json('data.organization_id'), 'enterprise_id' => null]);
        $enterpriseUuid = $enterprise->json('data.uuid');
        $this->assertTrue(Str::isUuid($enterpriseUuid));
        $organization = $this->signed('POST', '/sapi/organizations', ['enterprise_id' => $enterpriseId, 'code' => 'uuid-org', 'name' => '组织'])->assertCreated();
        $organizationId = $organization->json('data.id');
        $organizationUuid = $organization->json('data.uuid');
        $this->assertTrue(Str::isUuid($organizationUuid));
        $this->assertNotSame($enterpriseUuid, $organizationUuid);
        foreach (['enterprises' => [$enterpriseId, $enterpriseUuid], 'organizations' => [$organizationId, $organizationUuid]] as $resource => [$id, $uuid]) {
            $this->signed('GET', '/sapi/'.$resource.'/'.$id)->assertOk()->assertJsonPath('data.uuid', $uuid);
            $this->signed('PATCH', '/sapi/'.$resource.'/'.$id, ['name' => '新名称', 'uuid' => (string) Str::uuid7()])->assertOk()->assertJsonPath('data.uuid', $uuid);
            $this->signed('GET', '/sapi/'.$resource)->assertOk()->assertJsonFragment(['uuid' => $uuid]);
        }
    }

    public function test_organization_primary_types_are_validated_and_returned_as_codes(): void
    {
        $enterprise = $this->signed('POST', '/sapi/enterprises', ['code' => 'type-corp', 'name' => '企业'])->assertCreated()->json('data.id');
        $payload = ['enterprise_id' => $enterprise, 'code' => 'school-org', 'name' => '学校', 'primary_type' => 'school'];
        $id = $this->signed('POST', '/sapi/organizations', $payload)
            ->assertCreated()->assertJsonPath('data.primary_type', 'school')->json('data.id');

        $this->assertDatabaseHas('platform_organizations', ['id' => $id, 'primary_type' => 'school']);
        $this->assertSame(OrganizationType::School, PlatformOrganization::query()->findOrFail($id)->primary_type);
        $this->signed('GET', '/sapi/organizations/'.$id)->assertOk()->assertJsonPath('data.primary_type', 'school');
        $this->signed('GET', '/sapi/organizations')->assertOk()->assertJsonFragment(['primary_type' => 'school']);
        $this->signed('PATCH', '/sapi/organizations/'.$id, ['name' => '新学校'])
            ->assertOk()->assertJsonPath('data.primary_type', 'school');

        foreach (['department', '企业', 123, ['school']] as $invalidType) {
            $this->signed('POST', '/sapi/organizations', array_replace($payload, ['code' => 'invalid-org', 'primary_type' => $invalidType]))
                ->assertUnprocessable()->assertJsonValidationErrors('primary_type');
            $this->signed('PATCH', '/sapi/organizations/'.$id, ['primary_type' => $invalidType])
                ->assertUnprocessable()->assertJsonValidationErrors('primary_type');
        }

        $this->assertDatabaseMissing('platform_organizations', ['code' => 'invalid-org']);
        $this->signed('PATCH', '/sapi/organizations/'.$id, ['primary_type' => 'government'])
            ->assertOk()->assertJsonPath('data.primary_type', 'government');
        $this->signed('PATCH', '/sapi/organizations/'.$id, ['primary_type' => null])
            ->assertOk()->assertJsonPath('data.primary_type', null);
        $this->assertNull(PlatformOrganization::query()->findOrFail($id)->primary_type);
    }

    public function test_enterprise_employees_and_application_subscriptions(): void
    {
        $user = $this->signed('POST', '/sapi/users', ['phone' => '13800138002', 'password' => 'password123'])->assertCreated()->json('data.uuid');
        $user = User::query()->where('phone', '13800138002')->sole()->id;
        $enterprise = $this->signed('POST', '/sapi/enterprises', ['code' => 'corp', 'name' => '企业'])->assertCreated()->json('data.id');
        $other = $this->signed('POST', '/sapi/enterprises', ['code' => 'other', 'name' => '其他企业'])->assertCreated()->json('data.id');
        $employeeBody = ['user_id' => $user, 'employee_no' => 'E001', 'position' => '工程师', 'is_enabled' => true];
        $employeeResponse = $this->signed('POST', "/sapi/enterprises/$enterprise/employees", $employeeBody)->assertCreated()->assertJsonPath('data.user.phone', '13800138002');
        $employee = $employeeResponse->json('data.id');
        $this->signed('POST', "/sapi/enterprises/$enterprise/employees", $employeeBody)->assertUnprocessable();
        $this->signed('GET', "/sapi/enterprises/$enterprise/employees")->assertOk()->assertJsonPath('data.total', 1);
        $this->signed('GET', "/sapi/enterprises/$other/employees/$employee")->assertNotFound();
        $this->signed('PATCH', "/sapi/enterprises/$other/employees/$employee", ['position' => '无权限'])->assertNotFound();
        $this->signed('DELETE', "/sapi/enterprises/$other/employees/$employee")->assertNotFound();
        $this->signed('PATCH', "/sapi/enterprises/$enterprise/employees/$employee", ['position' => '主管'])->assertOk()->assertJsonPath('data.position', '主管');
        foreach (['OA' => '旗舰版', 'CRM' => '基础版', 'ERP' => '进阶版'] as $code => $package) {
            $application = PlatformApplication::query()->create(['code' => $code, 'name' => $code, 'base_url' => '']);
            $payload = ['application_id' => $application->id, 'package_name' => $package, 'seat_limit' => 100, 'starts_at' => '2026-01-01T00:00:00+08:00', 'ends_at' => '2027-01-01T00:00:00+08:00'];
            $response = $this->signed('POST', "/sapi/enterprises/$enterprise/subscriptions", $payload)->assertCreated()->assertJsonPath('data.package_name', $package);
            $id = $response->json('data.id');
            $this->assertArrayNotHasKey('app_secret', $response->json('data.application'));
            $this->assertArrayNotHasKey('app_key', $response->json('data.application'));
            $this->signed('POST', "/sapi/enterprises/$enterprise/subscriptions", $payload)->assertUnprocessable();
            $this->signed('GET', "/sapi/enterprises/$enterprise/subscriptions/$id")->assertOk();
            $this->signed('GET', "/sapi/enterprises/$other/subscriptions/$id")->assertNotFound();
            $this->signed('PATCH', "/sapi/enterprises/$enterprise/subscriptions/$id", ['ends_at' => '2025-01-01'])->assertUnprocessable();
            $this->signed('PATCH', "/sapi/enterprises/$enterprise/subscriptions/$id", ['seat_limit' => 200])->assertOk()->assertJsonPath('data.seat_limit', 200);
        }
        $this->signed('GET', "/sapi/enterprises/$enterprise/subscriptions")->assertOk()->assertJsonPath('data.total', 3);
        $this->signed('DELETE', "/sapi/enterprises/$other/subscriptions/$id")->assertNotFound();
        $this->signed('DELETE', "/sapi/enterprises/$enterprise/subscriptions/$id")->assertOk();
        $this->signed('DELETE', "/sapi/enterprises/$enterprise/employees/$employee")->assertOk();
        $this->signed('GET', '/sapi/users/'.$user)->assertOk();
    }
}
