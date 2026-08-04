<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.wechat.app_id' => 'wx-test-app',
            'services.wechat.app_secret' => 'test-secret',
            'services.wechat.base_url' => 'https://api.weixin.qq.com',
        ]);
    }

    public function test_wechat_login_requires_a_phone_code(): void
    {
        $this->postJson('/api/auth/wechat/login', [
            'login_code' => 'login-code',
        ])->assertUnprocessable()->assertJsonValidationErrors('phone_code');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('user_accounts', 0);
        Http::assertNothingSent();
    }

    public function test_wechat_login_gets_mobile_from_wechat_and_creates_account(): void
    {
        Http::fake([
            '*/sns/jscode2session*' => Http::response([
                'openid' => 'openid-1',
                'unionid' => 'unionid-1',
            ]),
            '*/cgi-bin/token*' => Http::response([
                'access_token' => 'wechat-access-token',
                'expires_in' => 7200,
            ]),
            '*/wxa/business/getuserphonenumber' => Http::response([
                'errcode' => 0,
                'phone_info' => [
                    'phoneNumber' => '13800138000',
                    'purePhoneNumber' => '13800138000',
                    'countryCode' => '86',
                ],
            ]),
        ]);

        $response = $this->postJson('/api/auth/wechat/login', [
            'login_code' => 'login-code',
            'phone_code' => 'phone-code',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.phone', '13800138000')
            ->assertJsonPath('data.account.provider', 'wechat')
            ->assertJsonPath('data.account.mobile', '13800138000')
            ->assertJsonStructure(['data' => ['access_token'], 'meta']);

        $this->assertDatabaseHas('user_accounts', [
            'provider' => 'wechat',
            'app_id' => 'wx-test-app',
            'provider_account_id' => 'openid-1',
            'union_id' => 'unionid-1',
            'mobile' => '13800138000',
        ]);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);

        Http::assertSent(fn ($request): bool => str_contains(
            $request->url(),
            '/wxa/business/getuserphonenumber',
        ) && $request['code'] === 'phone-code');
    }

    public function test_wechat_login_rejects_failure_to_get_a_mobile(): void
    {
        Http::fake([
            '*/sns/jscode2session*' => Http::response(['openid' => 'openid-1']),
            '*/cgi-bin/token*' => Http::response(['access_token' => 'token']),
            '*/wxa/business/getuserphonenumber' => Http::response([
                'errcode' => 40029,
                'errmsg' => 'code been used',
            ]),
        ]);

        $this->postJson('/api/auth/wechat/login', [
            'login_code' => 'login-code',
            'phone_code' => 'used-phone-code',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('user_accounts', 0);
    }
}
