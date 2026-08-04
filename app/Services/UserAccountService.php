<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * 第三方账号登录与绑定业务。
 *
 * 平台通信由各平台 Service 处理，本服务仅维护站内用户与通用账号表，
 * 后续接入支付宝、抖音时可复用该服务的绑定规则。
 */
class UserAccountService
{
    public function __construct(
        private readonly WechatService $wechat,
    ) {}

    /**
     * 微信手机号授权登录；首次登录会自动创建用户并绑定微信账号。
     *
     * @return array{user: User, account: UserAccount}
     */
    public function loginWithWechat(string $loginCode, string $phoneCode): array
    {
        $identity = $this->wechat->getSession($loginCode);
        $phone = $this->wechat->getPhoneNumber($phoneCode);

        if (! preg_match('/^1[3-9]\d{9}$/', $phone)) {
            throw ValidationException::withMessages([
                'phone_code' => ['微信返回的手机号格式不受支持'],
            ]);
        }

        return DB::transaction(function () use ($identity, $phone): array {
            $account = UserAccount::query()
                ->where('provider', UserAccount::PROVIDER_WECHAT)
                ->where('app_id', $this->wechat->appId())
                ->where('provider_account_id', $identity['openid'])
                ->lockForUpdate()
                ->first();

            if ($account !== null && $account->user->phone !== $phone) {
                throw ValidationException::withMessages([
                    'phone_code' => ['该微信账号绑定的手机号与当前授权手机号不一致'],
                ]);
            }

            $user = $account === null
                ? $this->findOrCreateUserByPhone($phone)
                : $account->user;

            $account = UserAccount::query()->updateOrCreate(
                [
                    'provider' => UserAccount::PROVIDER_WECHAT,
                    'app_id' => $this->wechat->appId(),
                    'provider_account_id' => $identity['openid'],
                ],
                [
                    'user_id' => $user->id,
                    'union_id' => $identity['unionid'] ?? null,
                    'mobile' => $phone,
                    'last_login_at' => now(),
                ],
            );

            return ['user' => $user, 'account' => $account];
        });
    }

    /**
     * 根据微信认证手机号查找或创建站内用户。
     */
    private function findOrCreateUserByPhone(string $phone): User
    {
        return User::query()->firstOrCreate(
            ['phone' => $phone],
            [
                'nick_name' => '用户'.substr($phone, -4),
                'password' => Str::password(32),
            ],
        );
    }
}
