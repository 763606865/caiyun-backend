<?php

namespace Tests\Feature\SApi;

use App\Models\PlatformApplication;
use App\Models\PlatformEnterprise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SubscriptionApplicationCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_and_updates_a_subscription_by_application_code(): void
    {
        $oa = $this->application('OA');
        $crm = $this->application('CRM');
        $enterprise = PlatformEnterprise::query()->create(['code' => 'code-company', 'name' => '编码测试企业']);
        $uri = '/sapi/enterprises/'.$enterprise->id.'/subscriptions';
        $response = $this->signed('POST', $uri, $this->payload(['application_code' => 'OA', 'application_id' => $crm->id]))
            ->assertCreated()->assertJsonPath('data.application_id', $oa->id)->assertJsonPath('data.application.code', 'OA');
        $id = $response->json('data.id');
        $this->assertArrayNotHasKey('app_key', $response->json('data.application'));
        $this->assertArrayNotHasKey('app_secret', $response->json('data.application'));
        $this->signed('POST', $uri, $this->payload(['application_code' => 'OA']))->assertUnprocessable();
        $this->signed('PATCH', $uri.'/'.$id, ['application_code' => 'CRM', 'package_name' => '基础版'])
            ->assertOk()->assertJsonPath('data.application_id', $crm->id)->assertJsonPath('data.application.code', 'CRM');
        $this->signed('PATCH', $uri.'/'.$id, ['seat_limit' => 200])
            ->assertOk()->assertJsonPath('data.seat_limit', 200)->assertJsonPath('data.application.code', 'CRM');
        $this->assertDatabaseCount('platform_subscriptions', 1);
    }

    #[DataProvider('invalidCodes')]
    public function test_invalid_codes_cannot_fall_back_to_a_supplied_application_id(mixed $code): void
    {
        $application = $this->application('OA');
        $enterprise = PlatformEnterprise::query()->create(['code' => 'invalid-code-company', 'name' => '测试企业']);
        $this->signed('POST', '/sapi/enterprises/'.$enterprise->id.'/subscriptions', $this->payload([
            'application_code' => $code, 'application_id' => $application->id,
        ]))->assertUnprocessable()->assertJsonValidationErrors('application_code');
        $this->assertDatabaseCount('platform_subscriptions', 0);
    }

    public static function invalidCodes(): array
    {
        return [['missing-code'], [''], [null], [123], [['OA']]];
    }

    public function test_existing_numeric_id_clients_remain_supported(): void
    {
        $application = $this->application('OA');
        $enterprise = PlatformEnterprise::query()->create(['code' => 'legacy-code-company', 'name' => '测试企业']);
        $this->signed('POST', '/sapi/enterprises/'.$enterprise->id.'/subscriptions', $this->payload([
            'application_id' => $application->id,
        ]))->assertCreated()->assertJsonPath('data.application.code', 'OA');
    }

    private function application(string $code): PlatformApplication
    {
        return PlatformApplication::query()->create(['code' => $code, 'name' => $code, 'base_url' => '', 'is_enabled' => true]);
    }

    private function payload(array $overrides): array
    {
        return [...$overrides, 'package_name' => '专业版', 'seat_limit' => 100,
            'starts_at' => '2026-11-01T00:00:00+08:00', 'ends_at' => '2027-11-01T00:00:00+08:00'];
    }

    private function signed(string $method, string $uri, array $payload)
    {
        $application = PlatformApplication::query()->firstOrCreate(['code' => 'sapi-code-test'], [
            'name' => 'SAPI', 'base_url' => '', 'is_enabled' => true,
        ]);
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = (string) now()->timestamp;
        $nonce = Str::random(32);
        $signature = hash_hmac('sha256', implode("\n", [$method, $uri, $timestamp, $nonce, hash('sha256', $body)]), $application->app_secret);

        return $this->call($method, $uri, [], [], [], [
            'HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json',
            'HTTP_X_APP_KEY' => $application->app_key, 'HTTP_X_TIMESTAMP' => $timestamp,
            'HTTP_X_NONCE' => $nonce, 'HTTP_X_SIGNATURE' => $signature,
        ], $body);
    }
}
