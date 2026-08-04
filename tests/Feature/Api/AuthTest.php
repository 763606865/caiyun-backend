<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_and_caches_an_sms_code(): void
    {
        $response = $this->postJson('/api/auth/sms-code', [
            'phone' => '13800138000',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'code' => 200,
                'data' => [
                    'message' => '验证码发送成功',
                    'expires_in' => 300,
                ],
            ])
            ->assertJsonStructure(['meta' => ['timestamp', 'response_time']]);

        $this->assertMatchesRegularExpression(
            '/^\d{6}$/',
            (string) Cache::get('auth:sms-code:13800138000'),
        );
    }

    public function test_sms_login_registers_a_new_user_and_returns_a_token(): void
    {
        Cache::put('auth:sms-code:13800138000', '123456', 300);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '13800138000',
            'code' => '123456',
            'device_name' => 'iPhone',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.phone', '13800138000')
            ->assertJsonStructure(['data' => ['access_token'], 'meta']);

        $this->assertDatabaseHas('users', ['phone' => '13800138000']);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertNull(Cache::get('auth:sms-code:13800138000'));
    }

    public function test_sms_login_reuses_an_existing_user(): void
    {
        $user = User::factory()->create(['phone' => '13800138000']);
        Cache::put('auth:sms-code:13800138000', '123456', 300);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '13800138000',
            'code' => '123456',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_sms_login_rejects_an_invalid_code(): void
    {
        Cache::put('auth:sms-code:13800138000', '123456', 300);

        $this->postJson('/api/auth/login', [
            'phone' => '13800138000',
            'code' => '654321',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('code', 422)
            ->assertJsonStructure(['message', 'errors' => ['code']])
            ->assertJsonValidationErrors('code');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_an_authenticated_user_can_get_their_profile(): void
    {
        $user = User::factory()->create(['phone' => '13800138000']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.phone', '13800138000')
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.email');
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')
            ->assertUnauthorized()
            ->assertJsonPath('code', 401)
            ->assertJsonStructure(['code', 'message'])
            ->assertJsonMissingPath('data');
    }
}
