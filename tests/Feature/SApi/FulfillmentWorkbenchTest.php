<?php

namespace Tests\Feature\SApi;

use App\Admin\Pages\FulfillmentWorkbench;
use App\Models\AdminUser;
use App\Models\PlatformApplication;
use App\Models\PlatformEnterprise;
use App\Services\Platform\FulfillmentOperations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

class FulfillmentWorkbenchTest extends TestCase
{
    use RefreshDatabase;

    private const EXCHANGE = '/sapi/integration/messages';

    private const MONITOR = '/sapi/integration/fulfillment';

    private PlatformEnterprise $enterprise;

    private PlatformApplication $wms;

    private PlatformApplication $erp;

    private AdminUser $operator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enterprise = PlatformEnterprise::create(['code' => 'PILOT', 'name' => '履约试点']);
        $this->erp = $this->application('erp', ['business.send.warehouse_delivery', 'business.receive.delivery_result']);
        $this->wms = $this->application('wms', ['business.receive.warehouse_delivery', 'business.send.delivery_result']);
        $this->operator = AdminUser::create(['name' => '履约操作员', 'email' => 'operator@workbench.test', 'password' => Str::random(32), 'is_active' => true]);
        DB::table('platform_fulfillment_operators')->insert(['enterprise_id' => $this->enterprise->id, 'admin_user_id' => $this->operator->id, 'can_retry' => true]);
    }

    public function test_warehouse_evidence_separates_human_work_waiting_and_a_real_ledger_difference(): void
    {
        $root = (string) Str::uuid();
        $request = $this->envelope($this->erp, $this->wms, 'warehouse_delivery', $root);
        $product = (string) Str::uuid();
        $warehouse = (string) Str::uuid();
        $wms = ['uuid' => $request['uuid'], 'side' => 'left', 'status' => 'draft', 'complete' => false, 'warehouse_uuid' => $warehouse, 'quantities' => []];
        $this->report($this->wms, 'inventory', $wms)->assertOk();
        $this->assertDatabaseHas('platform_fulfillment_checks', ['subject_uuid' => $request['uuid'], 'status' => 'awaiting']);

        $wms = [...$wms, 'status' => 'posted', 'complete' => true, 'quantities' => [$product => '-2.000000']];
        $this->report($this->wms, 'inventory', $wms)->assertOk();
        $this->assertDatabaseHas('platform_fulfillment_checks', ['subject_uuid' => $request['uuid'], 'status' => 'pending']);
        $this->report($this->erp, 'inventory', $wms)->assertForbidden();
        $this->report($this->wms, 'inventory', [...$wms, 'private_note' => 'private'])->assertUnprocessable();
        $this->report($this->wms, 'inventory', [...$wms, 'status' => 'draft'], now()->subMinute()->toISOString())->assertOk();
        $this->assertDatabaseHas('platform_fulfillment_checks', ['subject_uuid' => $request['uuid'], 'status' => 'pending']);

        $erp = [...$wms, 'side' => 'right', 'quantities' => [$product => '-1.00']];
        $this->report($this->erp, 'inventory', $erp)->assertOk();
        $this->assertDatabaseHas('platform_fulfillment_checks', ['subject_uuid' => $request['uuid'], 'status' => 'mismatch']);
        $this->report($this->erp, 'inventory', [...$erp, 'quantities' => [$product => '-2']])->assertOk();
        $this->assertDatabaseHas('platform_fulfillment_checks', ['subject_uuid' => $request['uuid'], 'status' => 'matched']);
    }

    public function test_retry_targets_one_original_envelope_and_uses_central_confirmation_to_close(): void
    {
        $root = (string) Str::uuid();
        $reference = $this->envelope($this->wms, $this->erp, 'delivery_result', $root);
        $this->envelope($this->wms, $this->erp, 'delivery_result', $root);
        $failure = ['uuid' => $reference['uuid'], 'direction' => 'receive', 'reason' => 'conflict'];
        $this->report($this->erp, 'failure', $failure)->assertOk();
        $this->actingAs($this->operator, 'admin');
        $this->get(FulfillmentWorkbench::getUrl(['enterprise' => $this->enterprise->id], panel: 'admin'))
            ->assertOk()->assertSee('应用交换失败')->assertSee($reference['uuid'])->assertDontSee('sensitive-detail');
        Livewire::test(FulfillmentWorkbench::class)->set('enterprise', $this->enterprise->id)->call('retry', $reference['uuid'], 'receive')->assertNotified();
        $operations = app(FulfillmentOperations::class);
        $this->assertFalse($operations->requestRetry($this->operator, $this->enterprise->id, $reference['uuid'], 'receive'));

        $query = http_build_query(['enterprise_uuid' => $this->enterprise->uuid, 'direction' => 'receive', 'kind' => 'delivery_result']);
        $poll = self::MONITOR.'/retries?'.$query;
        $retry = $this->signed($this->erp, 'GET', $poll)->assertOk()->assertJsonCount(1, 'data.items')->json('data.items.0');
        $this->signed($this->wms, 'GET', $poll)->assertOk()->assertJsonCount(0, 'data.items');
        $finish = self::MONITOR.'/retries/'.$retry['uuid'].'/finish';
        $tenant = ['enterprise_uuid' => $this->enterprise->uuid];
        $this->signed($this->wms, 'POST', $finish, $tenant)->assertNotFound();
        $this->signed($this->erp, 'POST', $finish, $tenant)->assertOk();
        $this->assertDatabaseHas('platform_fulfillment_retries', ['uuid' => $retry['uuid'], 'status' => 'failed']);

        $this->assertTrue($operations->requestRetry($this->operator, $this->enterprise->id, $reference['uuid'], 'receive'));
        $retry = $this->signed($this->erp, 'GET', $poll)->assertOk()->assertJsonCount(1, 'data.items')->json('data.items.0');
        $message = self::EXCHANGE.'?'.http_build_query([...$tenant, 'kind' => 'delivery_result', 'uuid' => $retry['message_uuid']]);
        $this->signed($this->erp, 'GET', $message)->assertOk()->assertJsonCount(1, 'data.items')->assertJsonPath('data.items.0.payload_hash', $reference['payload_hash']);
        $ack = self::EXCHANGE.'/'.$reference['uuid'].'/ack';
        $this->signed($this->erp, 'POST', $ack, [...$tenant, 'result' => ['received' => true]])->assertOk();
        $this->signed($this->erp, 'POST', self::MONITOR.'/retries/'.$retry['uuid'].'/finish', $tenant)->assertOk();
        $this->report($this->erp, 'failure', $failure)->assertOk();
        $this->assertDatabaseCount('platform_business_messages', 2);
        $this->assertSame(0, $operations->failures($this->enterprise->id)->count());
        $this->assertDatabaseHas('platform_fulfillment_retries', ['uuid' => $retry['uuid'], 'status' => 'completed', 'admin_user_id' => $this->operator->id]);
    }

    public function test_send_recovery_uses_original_uuid_and_requires_its_enterprise_scope(): void
    {
        $root = (string) Str::uuid();
        $uuid = (string) Str::uuid();
        $failure = ['uuid' => $uuid, 'direction' => 'send', 'kind' => 'delivery_result',
            'correlation_uuid' => $root, 'reason' => 'unavailable', 'attempts' => 1];
        DB::table('platform_application_scopes')->insert(['application_id' => $this->erp->id, 'enterprise_id' => 0, 'scope' => 'platform.admin']);
        DB::table('platform_application_scopes')->where('application_id', $this->erp->id)->where('scope', 'fulfillment.observe')->delete();
        $this->report($this->erp, 'failure', $failure)->assertForbidden();
        $this->report($this->wms, 'failure', $failure)->assertOk();
        $operations = app(FulfillmentOperations::class);
        $this->assertTrue($operations->requestRetry($this->operator, $this->enterprise->id, $uuid, 'send'));

        $tenant = ['enterprise_uuid' => $this->enterprise->uuid];
        $url = self::MONITOR.'/retries?'.http_build_query([...$tenant, 'direction' => 'send']);
        $this->signed($this->erp, 'GET', $url)->assertOk()->assertJsonCount(0, 'data.items');
        $retry = $this->signed($this->wms, 'GET', $url)->assertOk()->assertJsonPath('data.items.0.message_uuid', $uuid)->json('data.items.0');
        $payload = '{"consumed":true}';
        $message = [...$tenant, 'uuid' => $uuid, 'target_application_code' => $this->erp->code, 'correlation_uuid' => $root,
            'kind' => 'delivery_result', 'payload_json' => $payload, 'payload_hash' => hash('sha256', $payload)];
        $this->signed($this->wms, 'POST', self::EXCHANGE, $message)->assertOk();
        $this->signed($this->wms, 'POST', self::MONITOR.'/retries/'.$retry['uuid'].'/finish', $tenant)->assertOk();

        $this->assertDatabaseHas('platform_fulfillment_retries', ['uuid' => $retry['uuid'], 'status' => 'completed']);
        $this->assertDatabaseHas('platform_business_messages', ['uuid' => $uuid, 'status' => 'queued']);
        $this->assertSame(0, $operations->failures($this->enterprise->id)->count());
    }

    public function test_read_access_and_application_scopes_are_enterprise_specific(): void
    {
        $another = PlatformEnterprise::create(['code' => 'SECOND', 'name' => '另一个企业']);
        $this->actingAs($this->operator, 'admin')->get(FulfillmentWorkbench::getUrl(['enterprise' => $another->id], panel: 'admin'))->assertForbidden();
        $this->signed($this->erp, 'POST', self::MONITOR.'/reports', ['enterprise_uuid' => $another->uuid, 'topic' => 'failure', 'observed_at' => now()->toISOString(), 'records' => [['uuid' => (string) Str::uuid()]]])->assertForbidden();

        $reference = $this->envelope($this->wms, $this->erp, 'delivery_result', (string) Str::uuid());
        DB::table('platform_fulfillment_operators')->where('admin_user_id', $this->operator->id)->update(['can_retry' => false]);
        Livewire::test(FulfillmentWorkbench::class)->set('enterprise', $this->enterprise->id)->call('retry', $reference['uuid'], 'receive')->assertForbidden();
        $this->assertDatabaseCount('platform_fulfillment_retries', 0);
    }

    /** @param list<string> $scopes */
    private function application(string $code, array $scopes): PlatformApplication
    {
        $app = PlatformApplication::create(['code' => $code, 'name' => $code, 'base_url' => 'https://'.$code.'.test', 'is_enabled' => true]);
        foreach ([...$scopes, 'fulfillment.observe', 'fulfillment.retry'] as $scope) {
            DB::table('platform_application_scopes')->insert(['application_id' => $app->id, 'enterprise_id' => $this->enterprise->id, 'scope' => $scope]);
        }
        DB::table('platform_application_grants')->insert(['application_id' => $app->id, 'enterprise_id' => $this->enterprise->id, 'employee_id' => 0, 'is_enabled' => true, 'version' => 1]);

        return $app;
    }

    /** @param array<string, mixed> $record */
    private function report(PlatformApplication $app, string $topic, array $record, ?string $observed = null): TestResponse
    {
        return $this->signed($app, 'POST', self::MONITOR.'/reports', [
            'enterprise_uuid' => $this->enterprise->uuid, 'topic' => $topic,
            'observed_at' => $observed ?? now()->toISOString(), 'records' => [$record],
        ]);
    }

    /** @return array<string, string> */
    private function envelope(PlatformApplication $source, PlatformApplication $target, string $kind, string $root): array
    {
        $payload = '{"note":"sensitive-detail"}';
        $data = ['uuid' => (string) Str::uuid(), 'enterprise_uuid' => $this->enterprise->uuid, 'kind' => $kind,
            'target_application_code' => $target->code, 'correlation_uuid' => $root, 'payload_json' => $payload, 'payload_hash' => hash('sha256', $payload)];
        $this->signed($source, 'POST', self::EXCHANGE, $data)->assertOk();

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function signed(PlatformApplication $app, string $method, string $uri, array $data = []): TestResponse
    {
        $body = $data ? json_encode($data, JSON_THROW_ON_ERROR) : '';
        $timestamp = (string) now()->timestamp;
        $nonce = Str::random(32);
        $signature = hash_hmac('sha256', implode("\n", [$method, $uri, $timestamp, $nonce, hash('sha256', $body)]), $app->app_secret);

        return $this->call($method, $uri, [], [], [], [
            'HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json',
            'HTTP_X_APP_KEY' => $app->app_key, 'HTTP_X_TIMESTAMP' => $timestamp,
            'HTTP_X_NONCE' => $nonce, 'HTTP_X_SIGNATURE' => $signature,
        ], $body);
    }
}
