<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * 微信小程序服务端 API。
 *
 * 负责换取 openid 及通过微信 phone_code 获取真实手机号。业务层不得接受
 * 客户端直接提交的手机号，以免绕过微信手机号授权。
 */
class WechatService
{
    /**
     * 使用 wx.login 产生的 code 换取微信身份。
     *
     * @return array{openid: string, unionid?: string}
     */
    public function getSession(string $code): array
    {
        $response = $this->http()->get('/sns/jscode2session', [
            'appid' => $this->appId(),
            'secret' => $this->appSecret(),
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ])->throw()->json();

        if (! is_array($response) || ! isset($response['openid'])) {
            $this->throwWechatError($response, '微信登录凭证无效');
        }

        /** @var array{openid: string, unionid?: string} $response */
        return $response;
    }

    /**
     * 使用 getPhoneNumber 回调中的动态 code 获取微信认证手机号。
     */
    public function getPhoneNumber(string $phoneCode): string
    {
        $response = $this->http()
            ->withToken($this->getAccessToken())
            ->post('/wxa/business/getuserphonenumber', [
                'code' => $phoneCode,
            ])
            ->throw()
            ->json();

        $phone = is_array($response) ? data_get($response, 'phone_info.phoneNumber') : null;

        if (! is_string($phone) || $phone === '') {
            $this->throwWechatError($response, '未能获取微信手机号，请重新授权');
        }

        return $phone;
    }

    /**
     * 获取并缓存小程序 access_token。
     */
    private function getAccessToken(): string
    {
        return Cache::remember(
            'wechat:mini-program:access-token:'.$this->appId(),
            now()->addSeconds(7000),
            function (): string {
                $response = $this->http()->get('/cgi-bin/token', [
                    'grant_type' => 'client_credential',
                    'appid' => $this->appId(),
                    'secret' => $this->appSecret(),
                ])->throw()->json();

                if (! is_array($response) || ! isset($response['access_token'])) {
                    $this->throwWechatError($response, '获取微信 access_token 失败');
                }

                return (string) $response['access_token'];
            },
        );
    }

    /**
     * 创建统一配置的微信 HTTP 客户端。
     */
    private function http(): PendingRequest
    {
        return Http::baseUrl((string) config('services.wechat.base_url'))
            ->acceptJson()
            ->timeout(10);
    }

    /**
     * 获取并校验微信 AppID。
     */
    public function appId(): string
    {
        $appId = config('services.wechat.app_id');

        if (! is_string($appId) || $appId === '') {
            throw ValidationException::withMessages([
                'login_code' => ['服务端未配置微信 AppID'],
            ]);
        }

        return $appId;
    }

    /**
     * 获取并校验微信 AppSecret。
     */
    private function appSecret(): string
    {
        $secret = config('services.wechat.app_secret');

        if (! is_string($secret) || $secret === '') {
            throw ValidationException::withMessages([
                'login_code' => ['服务端未配置微信 AppSecret'],
            ]);
        }

        return $secret;
    }

    /**
     * 将微信业务错误转换为统一的 422 参数错误。
     */
    private function throwWechatError(mixed $response, string $fallback): never
    {
        $message = is_array($response) && isset($response['errmsg'])
            ? $fallback.'：'.(string) $response['errmsg']
            : $fallback;

        throw ValidationException::withMessages([
            'login_code' => [$message],
        ]);
    }
}
