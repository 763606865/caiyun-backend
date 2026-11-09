<?php

namespace Tests\Feature\SApi;

use App\Models\PlatformApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SApiTest extends TestCase
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

    public function test_ping_requires_valid_signature(): void
    {
        $this->getJson('/sapi/ping')->assertUnauthorized();
        $this->signed('GET', '/sapi/ping')->assertOk()->assertJsonPath('data.message', 'pong');
    }

    public function test_directory_crud(): void
    {
        $user = $this->signed('POST', '/sapi/users', ['phone' => '13800138000', 'password' => 'password123', 'nick_name' => '测试'])->assertCreated()->json('data.id');
        $enterprise = $this->signed('POST', '/sapi/enterprises', ['code' => 'test', 'name' => '企业', 'admin_user_id' => $user])->assertCreated()->json('data.id');
        $organization = $this->signed('POST', '/sapi/organizations', ['code' => 'test', 'name' => '组织', 'enterprise_id' => $enterprise])->assertCreated()->json('data.id');
        foreach (['users' => $user, 'enterprises' => $enterprise, 'organizations' => $organization] as $resource => $id) {
            $this->signed('GET', '/sapi/'.$resource)->assertOk();
            $this->signed('GET', '/sapi/'.$resource.'/'.$id)->assertOk();
            $this->signed('PATCH', '/sapi/'.$resource.'/'.$id, $resource === 'users' ? ['nick_name' => '更新'] : ['name' => '更新'])->assertOk();
        }
        $this->signed('DELETE', '/sapi/enterprises/'.$enterprise)->assertStatus(409);
        $this->signed('DELETE', '/sapi/organizations/'.$organization)->assertOk();
        $this->signed('DELETE', '/sapi/enterprises/'.$enterprise)->assertOk();
        $this->signed('DELETE', '/sapi/users/'.$user)->assertOk();
        $this->signed('GET', '/sapi/users/'.$user)->assertNotFound();
        $this->signed('POST', '/sapi/enterprises', [])->assertUnprocessable();
    }

    public function test_validation_uses_chinese_attributes_and_uniform_errors(): void
    {
        config(['app.debug' => true]);

        $this->signed('POST', '/sapi/users', ['phone' => '13800138001', 'password' => 'short'])
            ->assertUnprocessable()
            ->assertJsonPath('code', 422)
            ->assertJsonPath('errors.password.0', '密码不能少于8个字符');

        $this->signed('POST', '/sapi/enterprises', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.code.0', '编码不能为空');

        $this->signed('GET', '/sapi/users/999999')
            ->assertNotFound()
            ->assertJsonPath('code', 404)
            ->assertJsonPath('message', '请求的资源不存在');
    }
}
