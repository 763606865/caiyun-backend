<?php

namespace Tests\Feature\SApi;

use App\Models\PlatformApplication;
use App\Models\PlatformEmployee;
use App\Models\PlatformEnterprise;
use App\Models\PlatformOrganization;
use App\Models\User;
use App\Services\Platform\DirectoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class IntegrationFoundationTest extends TestCase
{
    use RefreshDatabase;

    private function application(string $code = 'OA'): PlatformApplication
    {
        return PlatformApplication::query()->create(['code' => $code, 'name' => $code, 'base_url' => 'https://example.test', 'is_enabled' => true]);
    }

    private function scope(PlatformApplication $app, string $scope, int $enterprise = 0): void
    {
        DB::table('platform_application_scopes')->insertOrIgnore(['application_id' => $app->id, 'enterprise_id' => $enterprise, 'scope' => $scope]);
    }

    private function signed(PlatformApplication $app, string $method, string $uri, array $data = [])
    {
        $body = $data ? json_encode($data) : '';
        $timestamp = (string) now()->timestamp;
        $nonce = Str::random(32);
        $signature = hash_hmac('sha256', implode("\n", [$method, $uri, $timestamp, $nonce, hash('sha256', $body)]), $app->app_secret);

        return $this->call($method, $uri, [], [], [], ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json', 'HTTP_X_APP_KEY' => $app->app_key, 'HTTP_X_TIMESTAMP' => $timestamp, 'HTTP_X_NONCE' => $nonce, 'HTTP_X_SIGNATURE' => $signature], $body);
    }

    private function directory(PlatformApplication $app, string $type, string $source, array $payload, ?string $enterprise = null, int $version = 1, ?string $key = null): array
    {
        return ['entity_type' => $type, 'source_system' => $app->code, 'source_id' => $source, 'source_version' => $version, 'enterprise_uuid' => $enterprise, 'idempotency_key' => $key ?? $type.':'.$source.':'.$version, 'payload' => $payload];
    }

    private function identity(PlatformApplication $app): array
    {
        $user = User::factory()->create();
        $enterprise = PlatformEnterprise::query()->create(['code' => Str::random(10), 'name' => 'A', 'admin_user_id' => $user->id]);
        $employee = PlatformEmployee::query()->create(['enterprise_id' => $enterprise->id, 'user_id' => $user->id]);
        $this->scope($app, 'auth.check', $enterprise->id);
        foreach ([0, $employee->id] as $id) {
            DB::table('platform_application_grants')->insert(['application_id' => $app->id, 'enterprise_id' => $enterprise->id, 'employee_id' => $id, 'is_enabled' => true, 'version' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }

        return [$user, $enterprise, $employee, ['user_uuid' => $user->uuid, 'enterprise_uuid' => $enterprise->uuid, 'employment_uuid' => $employee->uuid, 'application_code' => $app->code]];
    }

    public function test_default_credentials_cannot_enumerate_or_change_global_directory(): void
    {
        $app = $this->application();
        $this->signed($app, 'GET', '/sapi/users')->assertForbidden();
        $this->signed($app, 'GET', '/sapi/enterprises')->assertForbidden();
        $this->signed($app, 'POST', '/sapi/directory/upserts', $this->directory($app, 'enterprise', 'e1', ['code' => 'A', 'name' => 'A']))->assertForbidden();
    }

    public function test_directory_is_idempotent_and_rejects_old_versions_and_unknown_fields(): void
    {
        $app = $this->application();
        $this->scope($app, 'directory.bootstrap');
        $body = $this->directory($app, 'enterprise', 'e1', ['code' => 'A', 'name' => 'A']);
        $one = $this->signed($app, 'POST', '/sapi/directory/upserts', $body)->assertOk()->json('data');
        $this->signed($app, 'POST', '/sapi/directory/upserts', $body)->assertOk()->assertJsonPath('data.entity_uuid', $one['entity_uuid']);
        $this->assertDatabaseCount('platform_enterprises', 1);
        $this->assertDatabaseCount('platform_directory_changes', 1);
        $body['payload']['name'] = 'different';
        $this->signed($app, 'POST', '/sapi/directory/upserts', $body)->assertConflict();
        $next = $this->directory($app, 'enterprise', 'e1', ['name' => 'new'], $one['entity_uuid'], 2);
        $this->signed($app, 'POST', '/sapi/directory/upserts', $next)->assertOk();
        $body['idempotency_key'] = 'old-version';
        $this->signed($app, 'POST', '/sapi/directory/upserts', $body)->assertConflict();
        $next = $this->directory($app, 'enterprise', 'e1', ['salary' => 'secret'], $one['entity_uuid'], 3);
        $this->signed($app, 'POST', '/sapi/directory/upserts', $next)->assertUnprocessable();
    }

    public function test_directory_scopes_snapshots_changes_and_identity_resolution(): void
    {
        $oa = $this->application();
        $this->scope($oa, 'directory.bootstrap');
        $enterprise = $this->signed($oa, 'POST', '/sapi/directory/upserts', $this->directory($oa, 'enterprise', 'e1', ['code' => 'A', 'name' => 'A']))->json('data.entity_uuid');
        $user = $this->signed($oa, 'POST', '/sapi/directory/upserts', $this->directory($oa, 'user', 'u1', ['phone' => '13800138001', 'real_name' => '张三'], $enterprise))->assertOk()->json('data.entity_uuid');
        $organization = $this->signed($oa, 'POST', '/sapi/directory/upserts', $this->directory($oa, 'organization', 'o1', ['code' => 'sales-A', 'name' => '销售部'], $enterprise))->assertOk()->json('data.entity_uuid');
        $employment = $this->signed($oa, 'POST', '/sapi/directory/upserts', $this->directory($oa, 'employment', 'm1', ['user_uuid' => $user, 'organization_uuid' => $organization, 'is_enabled' => true], $enterprise))->assertOk()->json('data.entity_uuid');
        $crm = $this->application('CRM');
        $uri = '/sapi/directory/snapshots?enterprise_uuid='.$enterprise.'&entity_type=employment';
        $this->signed($crm, 'GET', $uri)->assertForbidden();
        $this->scope($crm, 'directory.read', PlatformEnterprise::query()->where('uuid', $enterprise)->sole()->id);
        $snapshot = $this->signed($crm, 'GET', $uri)->assertOk()->assertJsonPath('data.items.0.entity_uuid', $employment)->assertJsonPath('data.items.0.user_uuid', $user)->json('data');
        $this->signed($oa, 'POST', '/sapi/directory/upserts', $this->directory($oa, 'employment', 'm1', ['is_enabled' => false], $enterprise, 2))->assertOk();
        $this->signed($crm, 'GET', '/sapi/directory/changes?enterprise_uuid='.$enterprise.'&cursor='.$snapshot['cursor'])->assertOk()->assertJsonPath('data.items.0.payload.is_enabled', false);
        $this->signed($oa, 'POST', '/sapi/identity/resolve', ['enterprise_uuid' => $enterprise, 'entity_type' => 'employment', 'source_id' => 'm1'])->assertOk()->assertJsonPath('data.entity_uuid', $employment);
        $other = PlatformEnterprise::query()->create(['code' => 'B', 'name' => 'B']);
        $this->signed($crm, 'GET', '/sapi/directory/changes?enterprise_uuid='.$other->uuid.'&cursor=0')->assertForbidden();
    }

    public function test_cross_enterprise_organization_parent_is_rejected(): void
    {
        $app = $this->application();
        $this->scope($app, 'platform.admin');
        $a = PlatformEnterprise::query()->create(['code' => 'A', 'name' => 'A']);
        $b = PlatformEnterprise::query()->create(['code' => 'B', 'name' => 'B']);
        $parent = PlatformOrganization::query()->create(['enterprise_id' => $a->id, 'code' => 'a', 'name' => 'a']);
        $this->signed($app, 'POST', '/sapi/organizations', ['enterprise_id' => $b->id, 'code' => 'b', 'name' => 'b', 'parent_id' => $parent->id])->assertUnprocessable();
    }

    public function test_check_requires_all_grants_and_respects_per_enterprise_departure(): void
    {
        $app = $this->application('CRM');
        [$user, $enterprise, $employee, $data] = $this->identity($app);
        $this->signed($app, 'POST', '/sapi/auth/check', $data)->assertOk()->assertJsonPath('data.active', true)->assertJsonPath('data.user_uuid', $user->uuid);
        $employee->update(['is_enabled' => false]);
        $this->signed($app, 'POST', '/sapi/auth/check', $data)->assertOk()->assertJsonPath('data.active', false);
        $employee->update(['is_enabled' => true]);
        DB::table('platform_application_grants')->where('employee_id', $employee->id)->update(['is_enabled' => false]);
        $this->signed($app, 'POST', '/sapi/auth/check', $data)->assertOk()->assertJsonPath('data.active', false);
        $data['application_code'] = 'ERP';
        $this->signed($app, 'POST', '/sapi/auth/check', $data)->assertForbidden();
    }

    public function test_tickets_are_bound_to_user_application_redirect_and_consumed_once(): void
    {
        $app = $this->application('CRM');
        [$user, $enterprise, $employee, $data] = $this->identity($app);
        $uri = 'https://crm.example.test/callback';
        $app->forceFill(['redirect_uris' => [$uri]])->save();
        $this->actingAs($user, 'api');
        $body = ['enterprise_uuid' => $enterprise->uuid, 'application_code' => 'CRM', 'redirect_uri' => $uri, 'state' => Str::random(32)];
        $code = $this->postJson('/api/platform/login-tickets', $body)->assertOk()->json('data.code');
        $this->signed($app, 'POST', '/sapi/auth/exchange', ['code' => $code, 'redirect_uri' => 'https://evil.test'])->assertUnauthorized();
        $this->signed($app, 'POST', '/sapi/auth/exchange', ['code' => $code, 'redirect_uri' => $uri])->assertOk()->assertJsonPath('data.state', $body['state'])->assertJsonPath('data.active', true);
        $this->signed($app, 'POST', '/sapi/auth/exchange', ['code' => $code, 'redirect_uri' => $uri])->assertUnauthorized();
        $code = $this->postJson('/api/platform/login-tickets', $body)->assertOk()->json('data.code');
        $this->travel(61)->seconds();
        $this->signed($app, 'POST', '/sapi/auth/exchange', ['code' => $code, 'redirect_uri' => $uri])->assertUnauthorized();
    }

    public function test_another_user_cannot_issue_ticket_for_administrator_identity(): void
    {
        $app = $this->application('CRM');
        [$user, $enterprise] = $this->identity($app);
        $app->forceFill(['redirect_uris' => ['https://crm.example.test/callback']])->save();
        $this->actingAs(User::factory()->create(), 'api');
        $this->postJson('/api/platform/login-tickets', ['enterprise_uuid' => $enterprise->uuid, 'application_code' => 'CRM', 'redirect_uri' => 'https://crm.example.test/callback', 'state' => Str::random(32), 'user_uuid' => $user->uuid])->assertNotFound();
    }

    public function test_grants_require_application_scope_and_real_enterprise_administrator(): void
    {
        $crm = $this->application('CRM');
        [$owner, $enterprise, $employee] = $this->identity($crm);
        $oa = $this->application('OA');
        $uri = '/sapi/enterprises/'.$enterprise->uuid.'/employments/'.$employee->uuid.'/application-grants/CRM';
        $this->actingAs($owner, 'api');
        $this->signed($oa, 'PUT', $uri, ['is_enabled' => false])->assertForbidden();
        $this->scope($oa, 'grants.manage', $enterprise->id);
        $this->actingAs(User::factory()->create(), 'api');
        $this->signed($oa, 'PUT', $uri, ['is_enabled' => false])->assertForbidden();
        $this->actingAs($owner, 'api');
        $this->signed($oa, 'PUT', $uri, ['is_enabled' => false])->assertOk()->assertJsonPath('data.is_enabled', false);
    }

    public function test_uuid_subscription_route_only_exposes_callers_application(): void
    {
        $oa = $this->application();
        $crm = $this->application('CRM');
        $enterprise = PlatformEnterprise::query()->create(['code' => 'A', 'name' => 'A']);
        $this->scope($oa, 'subscription.read', $enterprise->id);
        $this->scope($oa, 'subscription.write', $enterprise->id);
        $uri = '/sapi/enterprises/'.$enterprise->uuid.'/subscriptions';
        $payload = ['application_code' => 'OA', 'package_name' => '基础', 'seat_limit' => 10, 'starts_at' => '2026-01-01', 'ends_at' => '2027-01-01'];
        $this->signed($oa, 'POST', $uri, $payload)->assertCreated();
        $this->signed($oa, 'GET', $uri)->assertOk()->assertJsonPath('data.total', 1);
        $payload['application_code'] = 'CRM';
        $this->signed($oa, 'POST', $uri, $payload)->assertForbidden();
    }

    public function test_bootstrap_does_not_allow_claiming_an_existing_enterprise(): void
    {
        $app = $this->application();
        $this->scope($app, 'directory.bootstrap');
        $existing = PlatformEnterprise::query()->create(['code' => 'unrelated', 'name' => 'Protected']);
        $body = $this->directory($app, 'enterprise', 'fake-source', ['name' => 'Hijacked']);
        $body['entity_uuid'] = $existing->uuid;
        $this->signed($app, 'POST', '/sapi/directory/upserts', $body)->assertForbidden();
        $this->assertSame('Protected', $existing->fresh()->name);
    }

    public function test_admin_updates_and_departure_are_visible_in_directory_changes(): void
    {
        $app = $this->application('CRM');
        [$user, $enterprise, $employee] = $this->identity($app);
        $this->scope($app, 'directory.read', $enterprise->id);
        $cursor = DB::table('platform_directory_changes')->max('id');
        $employee->update(['is_enabled' => false]);
        $this->signed($app, 'GET', '/sapi/directory/changes?enterprise_uuid='.$enterprise->uuid.'&cursor='.$cursor)
            ->assertOk()->assertJsonPath('data.items.0.payload.is_enabled', false);
    }

    public function test_employment_snapshot_eager_loads_directory_relations(): void
    {
        $enterprise = PlatformEnterprise::query()->create(['code' => 'eager', 'name' => 'Snapshot']);
        $organization = PlatformOrganization::query()->create(['enterprise_id' => $enterprise->id, 'code' => 'eager-org', 'name' => 'Sales']);
        for ($i = 0; $i < 8; $i++) {
            PlatformEmployee::query()->create(['enterprise_id' => $enterprise->id, 'organization_id' => $organization->id, 'user_id' => User::factory()->create()->id]);
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $items = app(DirectoryService::class)->items('employment', $enterprise);
            $this->assertCount(8, $items);
            $this->assertLessThanOrEqual(3, count(DB::getQueryLog()), 'Snapshot must load relations in batches, not once per employee.');
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_operator_commands_validate_and_configure_explicit_scopes_and_bindings(): void
    {
        $application = $this->application();
        $enterprise = PlatformEnterprise::query()->create(['code' => 'operator', 'name' => 'Operator']);
        $this->artisan('platform:integration', ['application' => $application->code, '--scope' => ['directory.bootstrap']])->assertSuccessful();
        $this->assertDatabaseHas('platform_application_scopes', ['application_id' => $application->id, 'enterprise_id' => 0, 'scope' => 'directory.bootstrap']);
        $this->artisan('platform:integration', ['application' => $application->code, '--scope' => ['directory.read']])->assertFailed();
        $this->artisan('platform:integration', ['application' => $application->code, '--enterprise' => $enterprise->uuid, '--grant' => true])->assertSuccessful();
        $this->assertDatabaseHas('platform_application_grants', ['application_id' => $application->id, 'enterprise_id' => $enterprise->id, 'employee_id' => 0, 'is_enabled' => true]);
        $arguments = ['application' => $application->code, 'entity_type' => 'enterprise', 'source_id' => 'operator:1', 'entity_uuid' => $enterprise->uuid, '--enterprise' => $enterprise->uuid];
        $this->artisan('platform:directory-bind', $arguments)->assertSuccessful();
        $this->assertDatabaseCount('platform_directory_sources', 0);
        $this->artisan('platform:directory-bind', [...$arguments, '--apply' => true])->assertSuccessful();
        $this->assertDatabaseHas('platform_directory_sources', ['source_id' => 'operator:1', 'entity_uuid' => $enterprise->uuid]);
    }
}
