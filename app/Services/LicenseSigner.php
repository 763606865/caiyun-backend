<?php

namespace App\Services;

use JsonException;
use OpenSSLAsymmetricKey;
use RuntimeException;

class LicenseSigner
{
    /** @param array<string, mixed> $claims */
    public function sign(array $claims): string
    {
        try {
            $json = json_encode($claims, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new RuntimeException('授权内容无法编码。', previous: $exception);
        }

        $payload = $this->base64UrlEncode($json);
        if (! openssl_sign($payload, $signature, $this->privateKey(), OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('授权包签名失败，请检查 RSA 私钥。');
        }

        return $payload.'.'.$this->base64UrlEncode($signature);
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
