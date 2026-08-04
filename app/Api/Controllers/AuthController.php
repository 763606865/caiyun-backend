<?php

namespace App\Api\Controllers;

use App\Models\User;
use App\Models\UserAccount;
use App\Services\UserAccountService;
use Caiyun\Sms\Contracts\SmsSender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Random\RandomException;

class AuthController extends Controller
{
    private const SMS_CODE_TTL_SECONDS = 300;

    /**
     * 发送短信验证码
     *
     * POST /api/auth/sms-code
     *
     * @throws RandomException
     */
    public function sendSmsCode(Request $request, SmsSender $sms): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^1[3-9]\d{9}$/'],
        ]);

        $code = (string) random_int(100000, 999999);

        $sms->send($validated['phone'], 'verification_code', [
            'code' => $code,
        ]);

        Cache::put(
            $this->smsCodeCacheKey($validated['phone']),
            $code,
            self::SMS_CODE_TTL_SECONDS,
        );

        return $this->success([
            'message' => '验证码发送成功',
            'expires_in' => self::SMS_CODE_TTL_SECONDS,
        ]);
    }

    /**
     * 短信验证码登录
     *
     * 手机号未注册时自动创建用户，并返回访问令牌。
     *
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^1[3-9]\d{9}$/'],
            'code' => ['required', 'string', 'size:6'],
            'device_name' => ['sometimes', 'string', 'max:100'],
        ]);
        if (config('auth.sms.valid_enabled')) {
            $cachedCode = Cache::pull($this->smsCodeCacheKey($validated['phone']));

            if (! is_string($cachedCode) || ! hash_equals($cachedCode, $validated['code'])) {
                throw ValidationException::withMessages([
                    'code' => ['验证码错误或已过期'],
                ]);
            }
        }

        $user = User::query()->firstOrCreate(
            ['phone' => $validated['phone']],
            [
                'nick_name' => '用户'.substr($validated['phone'], -4),
                'password' => Str::password(32),
            ],
        );

        $token = $user->createToken($validated['device_name'] ?? 'api')->plainTextToken;

        return $this->success([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userData($user),
        ]);
    }

    /**
     * 微信小程序手机号授权登录
     *
     * 手机号必须由服务端使用 phone_code 向微信获取，不接受客户端直接传入。
     *
     * POST /api/auth/wechat/login
     */
    public function wechatLogin(Request $request, UserAccountService $accounts): JsonResponse
    {
        $validated = $request->validate([
            'login_code' => ['required', 'string', 'max:200'],
            'phone_code' => ['required', 'string', 'max:200'],
            'device_name' => ['sometimes', 'string', 'max:100'],
        ]);

        $result = $accounts->loginWithWechat(
            $validated['login_code'],
            $validated['phone_code'],
        );
        $token = $result['user']
            ->createToken($validated['device_name'] ?? 'wechat-mini-program')
            ->plainTextToken;

        return $this->success([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userData($result['user']),
            'account' => $this->accountData($result['account']),
        ]);
    }

    /**
     * 获取当前登录用户信息
     *
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success([
            'user' => $this->userData($user),
        ]);
    }

    private function smsCodeCacheKey(string $phone): string
    {
        return 'auth:sms-code:'.$phone;
    }

    /**
     * @return array<string, mixed>
     */
    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'real_name' => $user->real_name,
            'nick_name' => $user->nick_name,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'gender' => $user->gender?->value,
            'status' => $user->status->value,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    /**
     * 输出第三方账号的非敏感字段。
     *
     * @return array<string, mixed>
     */
    private function accountData(UserAccount $account): array
    {
        return [
            'id' => $account->id,
            'provider' => $account->provider,
            'app_id' => $account->app_id,
            'mobile' => $account->mobile,
            'nickname' => $account->nickname,
            'avatar_url' => $account->avatar_url,
            'last_login_at' => $account->last_login_at,
        ];
    }
}
