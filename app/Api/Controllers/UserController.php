<?php

namespace App\Api\Controllers;

use App\Enums\UserGender;
use App\Models\User;
use Caiyun\Sms\Contracts\SmsSender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Random\RandomException;

class UserController extends Controller
{
    private const PHONE_CODE_TTL_SECONDS = 300;

    /**
     * 编辑当前用户的基础资料。
     *
     * PATCH /api/user
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'real_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nick_name' => ['sometimes', 'nullable', 'string', 'max:50'],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'gender' => ['sometimes', 'nullable', Rule::enum(UserGender::class)],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['prohibited'],
            'status' => ['prohibited'],
        ]);

        $user = $this->user();
        $user->fill($validated)->save();

        return $this->success([
            'message' => '用户信息更新成功',
            'user' => $this->userData($user->refresh()),
        ]);
    }

    /**
     * 向新手机号发送修改手机号验证码。
     *
     * POST /api/user/phone/sms-code
     *
     * @throws RandomException
     */
    public function sendPhoneCode(Request $request, SmsSender $sms): JsonResponse
    {
        $user = $this->user();
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^1[3-9]\d{9}$/',
                Rule::notIn(array_filter([$user->phone])),
                Rule::unique(User::class, 'phone'),
            ],
        ], [
            'phone.not_in' => '新手机号不能与当前手机号相同',
            'phone.unique' => '该手机号已被其他用户使用',
        ]);
        $code = (string) random_int(100000, 999999);

        $sms->send($validated['phone'], 'verification_code', [
            'code' => $code,
        ]);
        Cache::put(
            $this->phoneCodeCacheKey($user, $validated['phone']),
            $code,
            self::PHONE_CODE_TTL_SECONDS,
        );

        return $this->success([
            'message' => '验证码发送成功',
            'expires_in' => self::PHONE_CODE_TTL_SECONDS,
        ]);
    }

    /**
     * 验证短信验证码并修改手机号。
     *
     * PATCH /api/user/phone
     */
    public function updatePhone(Request $request): JsonResponse
    {
        $user = $this->user();
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^1[3-9]\d{9}$/',
                Rule::notIn(array_filter([$user->phone])),
                Rule::unique(User::class, 'phone'),
            ],
            'code' => ['required', 'string', 'size:6'],
        ], [
            'phone.not_in' => '新手机号不能与当前手机号相同',
            'phone.unique' => '该手机号已被其他用户使用',
        ]);
        $cachedCode = Cache::pull(
            $this->phoneCodeCacheKey($user, $validated['phone']),
        );

        if (! is_string($cachedCode) || ! hash_equals($cachedCode, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => ['验证码错误或已过期'],
            ]);
        }

        $user->update(['phone' => $validated['phone']]);

        return $this->success([
            'message' => '手机号修改成功',
            'user' => $this->userData($user->refresh()),
        ]);
    }

    /**
     * 生成用户修改手机号专用缓存键，避免与登录验证码混用。
     */
    private function phoneCodeCacheKey(User $user, string $phone): string
    {
        return sprintf('user:phone-change:%d:%s', $user->id, $phone);
    }

    /**
     * 输出用户公开资料。
     *
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
            'email' => $user->email,
            'status' => $user->status->value,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
