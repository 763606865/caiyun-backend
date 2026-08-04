<?php

namespace Tests\Feature\Api;

use App\Enums\UserGender;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patchJson('/api/user', [
                'real_name' => '张三',
                'nick_name' => '小张',
                'avatar' => 'avatars/zhang-san.png',
                'gender' => UserGender::Male->value,
                'email' => 'zhangsan@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonPath('data.message', '用户信息更新成功')
            ->assertJsonPath('data.user.real_name', '张三')
            ->assertJsonPath('data.user.gender', UserGender::Male->value);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'real_name' => '张三',
            'nick_name' => '小张',
            'gender' => UserGender::Male->value,
            'email' => 'zhangsan@example.com',
        ]);
    }

    public function test_profile_update_does_not_allow_phone_or_status(): void
    {
        $user = User::factory()->create([
            'phone' => '13800138000',
        ]);

        $this->actingAs($user)
            ->patchJson('/api/user', [
                'phone' => '13900139000',
                'status' => 0,
                'nick_name' => '新昵称',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone', 'status']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '13800138000',
            'status' => 1,
            'nick_name' => $user->nick_name,
        ]);
    }

    public function test_user_can_send_code_to_an_unused_new_phone(): void
    {
        $user = User::factory()->create(['phone' => '13800138000']);

        $this->actingAs($user)
            ->postJson('/api/user/phone/sms-code', [
                'phone' => '13900139000',
            ])
            ->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonPath('data.expires_in', 300);

        $this->assertMatchesRegularExpression(
            '/^\d{6}$/',
            (string) Cache::get("user:phone-change:{$user->id}:13900139000"),
        );
    }

    public function test_user_can_change_phone_with_valid_code(): void
    {
        $user = User::factory()->create(['phone' => '13800138000']);
        Cache::put("user:phone-change:{$user->id}:13900139000", '123456', 300);

        $this->actingAs($user)
            ->patchJson('/api/user/phone', [
                'phone' => '13900139000',
                'code' => '123456',
            ])
            ->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonPath('data.user.phone', '13900139000');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '13900139000',
        ]);
        $this->assertNull(Cache::get("user:phone-change:{$user->id}:13900139000"));
    }

    public function test_phone_change_rejects_invalid_code_and_used_phone(): void
    {
        $user = User::factory()->create(['phone' => '13800138000']);
        User::factory()->create(['phone' => '13900139000']);

        $this->actingAs($user)
            ->patchJson('/api/user/phone', [
                'phone' => '13700137000',
                'code' => '654321',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('code', 422)
            ->assertJsonValidationErrors('code');

        $this->actingAs($user)
            ->patchJson('/api/user/phone', [
                'phone' => '13900139000',
                'code' => '123456',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('phone');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '13800138000',
        ]);
    }
}
