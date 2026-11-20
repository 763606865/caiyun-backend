<?php

namespace Tests\Feature\SApi;

use App\Models\PlatformApplication;
use App\Models\PlatformEmployee;
use App\Models\PlatformEnterprise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessExchangeTest extends TestCase
{
    use RefreshDatabase;

    private function setupExchange(): array
    {
        $user = User::factory()->create();
        $enterprise = PlatformEnterprise::create(['code' => 'EXCHANGE', 'name' => 'Exchange']);
        $employee = PlatformEmployee::create(['enterprise_id' => $enterprise->id, 'user_id' => $user->id]);
        $apps = [];
        foreach (['crm' => ['contracts.submit', 'masters.read', 'masters.bind', 'masters.write.customer', 'business.send.sales_order'], 'oa' => ['contracts.receive', 'auth.delegate', 'masters.read', 'masters.bind', 'business.receive.sales_order']] as $code => $scopes) {
            $app = PlatformApplication::create(['code' => $code, 'name' => $code, 'base_url' => 'https://'.$code.'.test', 'is_enabled' => true,
                'redirect_uris' => ['https://'.$code.'.test/integration/caiyun/callback']]);
            foreach ([...$scopes, 'auth.check'] as $scope) {
                DB::table('platform_application_scopes')->insert(['application_id' => $app->id, 'enterprise_id' => $enterprise->id, 'scope' => $scope]);
            }
            foreach ([0, $employee->id] as $id) {
                DB::table('platform_application_grants')->insert(['application_id' => $app->id, 'enterprise_id' => $enterprise->id,
                    'employee_id' => $id, 'is_enabled' => true, 'version' => 1, 'created_at' => now(), 'updated_at' => now()]);
            }
            $apps[$code] = $app;
        }
        $snapshot = json_encode(['name' => '销售合同', 'number' => 'CRM-1', 'customer_name' => '客户甲', 'amount' => '123.45', 'currency' => 'CNY']);
        $body = ['uuid' => (string) Str::uuid(), 'enterprise_uuid' => $enterprise->uuid, 'contract_uuid' => (string) Str::uuid(),
            'revision' => 1, 'target_application_code' => 'oa', 'applicant_user_uuid' => $user->uuid,
            'applicant_employment_uuid' => $employee->uuid, 'snapshot_json' => $snapshot, 'snapshot_hash' => hash('sha256', $snapshot)];

        return [$apps['crm'], $apps['oa'], $enterprise, $employee, $body];
    }

    private function signed(PlatformApplication $app, string $method, string $uri, array $data = [])
    {
        $body = $data ? json_encode($data) : '';
        $timestamp = (string) now()->timestamp;
        $nonce = Str::random(32);
        $signature = hash_hmac('sha256', implode("\n", [$method, $uri, $timestamp, $nonce, hash('sha256', $body)]), $app->app_secret);

        return $this->call($method, $uri, [], [], [], ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json',
            'HTTP_X_APP_KEY' => $app->app_key, 'HTTP_X_TIMESTAMP' => $timestamp, 'HTTP_X_NONCE' => $nonce, 'HTTP_X_SIGNATURE' => $signature], $body);
    }

    public function test_message_replay_preserves_original_contents_and_receiver_ack_is_immutable(): void
    {
        [$source,$target,$enterprise,$employee,$approval] = $this->setupExchange();
        $this->signed($source, 'POST', '/sapi/integration/contract-approvals', $approval)->assertOk();
        $this->signed($target, 'POST', '/sapi/integration/contract-approvals/'.$approval['uuid'].'/result', ['enterprise_uuid' => $enterprise->uuid, 'snapshot_hash' => $approval['snapshot_hash'], 'status' => 'approved', 'oa_contract_uuid' => (string) Str::uuid(), 'oa_instance_uuid' => (string) Str::uuid()])->assertOk();
        $payload = json_encode(['approval_uuid' => $approval['uuid'], 'contract_revision' => $approval['revision'], 'snapshot_hash' => $approval['snapshot_hash'], 'snapshot_json' => $approval['snapshot_json']]);
        $body = ['uuid' => (string) Str::uuid(), 'enterprise_uuid' => $enterprise->uuid, 'target_application_code' => 'oa', 'kind' => 'sales_order', 'correlation_uuid' => $approval['contract_uuid'], 'payload_json' => $payload, 'payload_hash' => hash('sha256', $payload)];
        $base = '/sapi/integration/messages';
        $this->signed($source, 'POST', $base, $body)->assertOk();
        $this->signed($source, 'POST', $base, $body)->assertOk();
        $this->assertDatabaseCount('platform_business_messages', 1);
        $extra = json_decode($payload, true);
        $extra['extra'] = 'changed';
        $body['payload_json'] = json_encode($extra);
        $body['payload_hash'] = hash('sha256', $body['payload_json']);
        $this->signed($source, 'POST', $base, $body)->assertConflict();
        $ack = ['enterprise_uuid' => $enterprise->uuid, 'result' => ['order_uuid' => (string) Str::uuid()]];
        $this->signed($source, 'POST', $base.'/'.$body['uuid'].'/ack', $ack)->assertForbidden();
        $this->signed($target, 'POST', $base.'/'.$body['uuid'].'/ack', $ack)->assertOk();
        $this->signed($target, 'POST', $base.'/'.$body['uuid'].'/ack', $ack)->assertOk();
        $ack['result'] = ['different' => 'result'];
        $this->signed($target, 'POST', $base.'/'.$body['uuid'].'/ack', $ack)->assertConflict();
    }

    public function test_master_bindings_are_explicit_tenant_scoped_and_cannot_be_repointed(): void
    {
        [$source,$target,$enterprise] = $this->setupExchange();
        $base = '/sapi/integration/masters';
        $body = ['enterprise_uuid' => $enterprise->uuid, 'kind' => 'customer', 'source_id' => '1', 'version' => 1, 'payload' => ['code' => 'C-1', 'name' => '同名客户', 'is_active' => true]];
        $uuid = $this->signed($source, 'POST', $base, $body)->assertOk()->json('data.entity.uuid');
        $body['source_id'] = '2';
        $other = $this->signed($source, 'POST', $base, $body)->assertOk()->json('data.entity.uuid');
        $this->assertNotSame($uuid, $other);
        $bind = ['enterprise_uuid' => $enterprise->uuid, 'local_id' => '77'];
        $this->signed($target, 'POST', $base.'/'.$uuid.'/bind', $bind)->assertOk();
        $this->signed($target, 'POST', $base.'/'.$other.'/bind', $bind)->assertConflict();
        $tenant = PlatformEnterprise::create(['code' => 'OTHER', 'name' => '其他企业']);
        $this->signed($target, 'POST', $base.'/resolve', ['enterprise_uuid' => $tenant->uuid, 'uuids' => [$uuid]])->assertForbidden();
        $body['version'] = 1;
        $body['payload']['name'] = '篡改同版本';
        $this->signed($source,'POST',$base,$body)->assertConflict();
    }
}
