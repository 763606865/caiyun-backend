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

class ContractApprovalExchangeTest extends TestCase
{
    use RefreshDatabase;

    private function setupExchange(): array
    {
        $user = User::factory()->create();
        $enterprise = PlatformEnterprise::create(['code' => 'EXCHANGE', 'name' => 'Exchange']);
        $employee = PlatformEmployee::create(['enterprise_id' => $enterprise->id, 'user_id' => $user->id]);
        $apps = [];
        foreach (['crm' => ['contracts.submit'], 'oa' => ['contracts.receive', 'auth.delegate']] as $code => $scopes) {
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

    private function base(): string
    {
        return '/sapi/integration/contract-approvals';
    }

    public function test_submission_replay_is_idempotent_and_cannot_replace_snapshot_or_version(): void
    {
        [$crm, $oa, $enterprise, $employee, $body] = $this->setupExchange();
        $this->signed($crm, 'POST', $this->base(), $body)->assertOk()->assertJsonPath('data.request.status', 'queued');
        $employee->update(['is_enabled' => false]);
        $this->signed($crm, 'POST', $this->base(), $body)->assertOk();
        $this->assertDatabaseCount('platform_contract_approvals', 1);
        $body['snapshot_json'] = str_replace('123.45', '999.99', $body['snapshot_json']);
        $body['snapshot_hash'] = hash('sha256', $body['snapshot_json']);
        $this->signed($crm, 'POST', $this->base(), $body)->assertConflict();
        $body['uuid'] = (string) Str::uuid();
        $this->signed($crm, 'POST', $this->base(), $body)->assertForbidden();
    }

    public function test_only_receiving_application_can_decide_and_final_decisions_cannot_be_overwritten(): void
    {
        [$crm, $oa, $enterprise, $employee, $body] = $this->setupExchange();
        $this->signed($crm, 'POST', $this->base(), $body)->assertOk();
        $result = ['enterprise_uuid' => $enterprise->uuid, 'snapshot_hash' => $body['snapshot_hash'], 'status' => 'approved',
            'oa_contract_uuid' => (string) Str::uuid(), 'oa_instance_uuid' => (string) Str::uuid(), 'comment' => '同意'];
        $path = $this->base().'/'.$body['uuid'].'/result';
        $this->signed($crm, 'POST', $path, $result)->assertForbidden();
        $this->signed($oa, 'POST', $path, $result)->assertOk();
        $this->signed($oa, 'POST', $path, $result)->assertOk();
        $result['status'] = 'rejected';
        $this->signed($oa, 'POST', $path, $result)->assertConflict();
        $this->signed($crm, 'POST', $this->base().'/'.$body['uuid'].'/cancel', ['enterprise_uuid' => $enterprise->uuid])
            ->assertOk()->assertJsonPath('data.request.status', 'approved');
    }

    public function test_cancellation_requires_receiver_acknowledgement_and_cannot_resurrect_approval(): void
    {
        [$crm, $oa, $enterprise, $employee, $body] = $this->setupExchange();
        $this->signed($crm, 'POST', $this->base(), $body)->assertOk();
        $this->signed($crm, 'POST', $this->base().'/'.$body['uuid'].'/cancel', ['enterprise_uuid' => $enterprise->uuid])
            ->assertOk()->assertJsonPath('data.request.status', 'cancelling');
        $result = ['enterprise_uuid' => $enterprise->uuid, 'snapshot_hash' => $body['snapshot_hash'], 'status' => 'cancelled'];
        $this->signed($oa, 'POST', $this->base().'/'.$body['uuid'].'/result', $result)->assertOk();
        $this->signed($oa, 'GET', $this->base().'?enterprise_uuid='.$enterprise->uuid)->assertOk()->assertJsonCount(0, 'data.items');
        $result += ['oa_contract_uuid' => (string) Str::uuid(), 'oa_instance_uuid' => (string) Str::uuid()];
        $result['status'] = 'approved';
        $this->signed($oa, 'POST', $this->base().'/'.$body['uuid'].'/result', $result)->assertConflict();
    }

    public function test_unsent_cancellation_can_be_reconciled_after_applicant_access_is_revoked(): void
    {
        [$crm, $oa, $enterprise, $employee, $body] = $this->setupExchange();
        $employee->update(['is_enabled' => false]);
        $this->signed($crm, 'POST', $this->base(), $body)->assertForbidden();
        $body['cancel_requested'] = true;
        $this->signed($crm, 'POST', $this->base(), $body)->assertOk()->assertJsonPath('data.request.status', 'cancelling');
        $this->signed($oa, 'GET', $this->base().'?enterprise_uuid='.$enterprise->uuid)
            ->assertOk()->assertJsonCount(1, 'data.items')->assertJsonPath('data.items.0.status', 'cancelling');
        $this->signed($oa, 'POST', $this->base().'/'.$body['uuid'].'/result', [
            'enterprise_uuid' => $enterprise->uuid, 'snapshot_hash' => $body['snapshot_hash'], 'status' => 'cancelled',
        ])->assertOk()->assertJsonPath('data.request.status', 'cancelled');
        $body['cancel_requested'] = false;
        $this->signed($crm, 'POST', $this->base(), $body)->assertOk()->assertJsonPath('data.request.status', 'cancelled');
        $this->assertDatabaseCount('platform_contract_approvals', 1);
    }

    public function test_receiver_can_reconcile_a_decision_committed_before_cancel_was_delivered(): void
    {
        [$crm, $oa, $enterprise, $employee, $body] = $this->setupExchange();
        $this->signed($crm, 'POST', $this->base(), $body)->assertOk();
        $this->signed($crm, 'POST', $this->base().'/'.$body['uuid'].'/cancel', ['enterprise_uuid' => $enterprise->uuid])->assertOk();
        $this->signed($oa, 'POST', $this->base().'/'.$body['uuid'].'/result', ['enterprise_uuid' => $enterprise->uuid,
            'snapshot_hash' => $body['snapshot_hash'], 'status' => 'approved', 'oa_contract_uuid' => (string) Str::uuid(),
            'oa_instance_uuid' => (string) Str::uuid()])->assertOk()->assertJsonPath('data.request.status', 'approved');
    }

    public function test_cross_enterprise_and_unscoped_applications_cannot_read_snapshots(): void
    {
        [$crm, $oa, $enterprise, $employee, $body] = $this->setupExchange();
        $this->signed($crm, 'POST', $this->base(), $body)->assertOk();
        $other = PlatformEnterprise::create(['code' => 'OTHER', 'name' => 'Other']);
        $this->signed($oa, 'GET', $this->base().'/'.$body['uuid'].'?enterprise_uuid='.$other->uuid)->assertForbidden();
        $this->signed($crm, 'GET', $this->base().'?enterprise_uuid='.$enterprise->uuid)->assertForbidden();
        DB::table('platform_application_scopes')->where('application_id', $oa->id)->where('scope', 'contracts.receive')->delete();
        $this->signed($oa, 'GET', $this->base().'/'.$body['uuid'].'?enterprise_uuid='.$enterprise->uuid)->assertForbidden();
    }

    public function test_oa_can_delegate_only_with_scoped_active_source_and_target_and_exact_redirect(): void
    {
        [$crm, $oa, $enterprise, $employee, $body] = $this->setupExchange();
        $data = ['enterprise_uuid' => $enterprise->uuid, 'user_uuid' => $body['applicant_user_uuid'],
            'employment_uuid' => $employee->uuid, 'application_code' => 'crm',
            'redirect_uri' => 'https://crm.test/integration/caiyun/callback', 'state' => Str::random(64)];
        $this->signed($crm, 'POST', '/sapi/auth/delegated-tickets', $data)->assertForbidden();
        $code = $this->signed($oa, 'POST', '/sapi/auth/delegated-tickets', $data)->assertOk()->json('data.code');
        $this->signed($crm, 'POST', '/sapi/auth/exchange', ['code' => $code, 'redirect_uri' => $data['redirect_uri']])->assertOk()->assertJsonPath('data.state', $data['state']);
        $this->signed($crm, 'POST', '/sapi/auth/exchange', ['code' => $code, 'redirect_uri' => $data['redirect_uri']])->assertUnauthorized();
        $data['redirect_uri'] = 'https://evil.test';
        $this->signed($oa, 'POST', '/sapi/auth/delegated-tickets', $data)->assertUnprocessable();
        $employee->update(['is_enabled' => false]);
        $data['redirect_uri'] = 'https://crm.test/integration/caiyun/callback';
        $this->signed($oa, 'POST', '/sapi/auth/delegated-tickets', $data)->assertForbidden();
    }
}
