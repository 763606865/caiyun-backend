<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_api_request_does_not_query_or_update_device_table(): void
    {
        config(['client.request_log_enabled' => true]);

        $user = User::factory()->create();
        $requestId = (string) Str::uuid();

        $this->actingAs($user)
            ->withHeaders([
                'X-Client-Type' => 'app-ios',
                'X-App-Version' => '1.4.2',
                'X-App-Build' => '10402',
                'X-Platform' => 'ios',
                'X-OS-Version' => '18.2',
                'X-Device-ID' => 'ios-installation-1',
                'X-Channel' => 'appstore',
                'X-Request-ID' => $requestId,
            ])
            ->getJson('/api/me')
            ->assertOk()
            ->assertHeader('X-Request-ID', $requestId);

        $this->assertDatabaseCount('user_devices', 0);
        $this->assertDatabaseHas('api_request_logs', [
            'request_id' => $requestId,
            'user_id' => $user->id,
            'user_device_id' => null,
            'method' => 'GET',
            'path' => 'api/me',
            'status_code' => 200,
        ]);
    }

    public function test_server_generates_request_id_when_header_is_missing(): void
    {
        config(['client.request_log_enabled' => true]);

        $response = $this->postJson('/api/auth/sms-code', [
            'phone' => '13800138000',
        ])->assertOk();

        $requestId = $response->headers->get('X-Request-ID');

        $this->assertIsString($requestId);
        $this->assertTrue(Str::isUuid($requestId));
        $this->assertDatabaseHas('api_request_logs', [
            'request_id' => $requestId,
        ]);
    }

    public function test_api_rejects_missing_client_headers(): void
    {
        $this->flushHeaders()
            ->getJson('/api/me')
            ->assertUnprocessable()
            ->assertJsonPath('code', 422)
            ->assertJsonPath('message', '客户端信息请求头校验失败')
            ->assertJsonValidationErrors([
                'client_type',
                'app_version',
                'app_build',
                'platform',
                'os_version',
                'device_id',
                'channel',
            ])
            ->assertHeader('X-Request-ID');
    }

    public function test_request_database_log_is_disabled_by_default(): void
    {
        config(['client.request_log_enabled' => false]);

        $this->postJson('/api/auth/sms-code', [
            'phone' => '13800138000',
        ])->assertOk();

        $this->assertDatabaseCount('user_devices', 0);
        $this->assertDatabaseCount('api_request_logs', 0);
    }

    public function test_not_found_api_response_uses_the_unified_error_structure(): void
    {
        $this->getJson('/api/not-found')
            ->assertNotFound()
            ->assertJson([
                'code' => 404,
                'message' => '请求的资源不存在',
            ])
            ->assertJsonMissingPath('data');
    }

    public function test_device_sync_upserts_device_and_associates_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->withHeaders([
                'X-Client-Type' => 'app-android',
                'X-App-Version' => '2.1.0',
                'X-App-Build' => '20100',
                'X-Platform' => 'android',
                'X-OS-Version' => '15',
                'X-Device-ID' => 'android-installation-1',
                'X-Channel' => 'huawei',
            ])
            ->postJson('/api/devices/sync')
            ->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonPath('data.message', '设备信息同步成功')
            ->assertJsonPath('data.device.device_id', 'android-installation-1');

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'device_id' => 'android-installation-1',
            'client_type' => 'app-android',
            'app_version' => '2.1.0',
            'app_build' => '20100',
            'platform' => 'android',
            'os_version' => '15',
            'channel' => 'huawei',
        ]);

        $this->withoutToken()->withHeaders([
            'X-Client-Type' => 'app-android',
            'X-App-Version' => '2.1.1',
            'X-App-Build' => '20101',
            'X-Platform' => 'android',
            'X-OS-Version' => '15',
            'X-Device-ID' => 'android-installation-1',
            'X-Channel' => 'huawei',
        ])->postJson('/api/devices/sync')->assertOk();

        $this->assertDatabaseCount('user_devices', 1);
        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'device_id' => 'android-installation-1',
            'app_version' => '2.1.1',
            'app_build' => '20101',
        ]);
    }
}
