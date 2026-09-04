<?php

namespace App\Services;

use JsonException;
use OpenSSLAsymmetricKey;
use RuntimeException;

class LicenseSigner
{
    public const LICENSE_TYP = 'caiyun-license';

    public const LICENSE_VERSION = 2;

    /**
     * 签发短期租约（两段式 payload.signature，保持客户端兼容）。
     *
     * @param  array<string, mixed>  $claims
     */
    public function sign(array $claims): string
    {
        $payload = $this->encodeJson($claims);
        if (! openssl_sign($payload, $signature, $this->privateKey(), OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('授权包签名失败，请检查 RSA 私钥。');
        }

        return $payload.'.'.$this->base64UrlEncode($signature);
    }

    /**
     * 签发 v2 授权书（header.payload.signature），内含 OpenAPI 凭据与核验端点。
     *
     * @param  array<string, mixed>  $claims
     */
    public function signLicense(array $claims): string
    {
        $header = $this->encodeJson([
            'typ' => self::LICENSE_TYP,
            'alg' => 'RS256',
            'v' => self::LICENSE_VERSION,
        ]);
        $payload = $this->encodeJson($claims);
        $signingInput = $header.'.'.$payload;

        if (! openssl_sign($signingInput, $signature, $this->privateKey(), OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('授权书签名失败，请检查 RSA 私钥。');
        }

        return $signingInput.'.'.$this->base64UrlEncode($signature);
    }

    /** @param  array<string, mixed>  $data */
    private function encodeJson(array $data): string
    {
        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new RuntimeException('授权内容无法编码。', previous: $exception);
        }

        return $this->base64UrlEncode($json);
    }

    private function privateKey(): OpenSSLAsymmetricKey
    {
        $configured = config('licensing.private_key');
        $pem = is_string($configured) && trim($configured) !== ''
            ? (base64_decode($configured, true) ?: $configured)
            : $this->readKeyFile();
        $key = openssl_pkey_get_private($pem, (string) config('licensing.private_key_passphrase', ''));

        if (! $key instanceof OpenSSLAsymmetricKey) {
            throw new RuntimeException('授权服务 RSA 私钥无效或密码错误。');
        }

        return $key;
    }

    private function readKeyFile(): string
    {
        $path = config('licensing.private_key_path');
        if (! is_string($path) || ! is_readable($path) || ($pem = file_get_contents($path)) === false) {
            throw new RuntimeException('未配置授权服务 RSA 私钥，请设置 LICENSE_SERVER_PRIVATE_KEY_PATH。');
        }

        return $pem;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
