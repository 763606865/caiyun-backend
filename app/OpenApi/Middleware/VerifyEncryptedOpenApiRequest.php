<?php

namespace App\OpenApi\Middleware;

use App\Models\OpenApiCredential;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use JsonException;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class VerifyEncryptedOpenApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $clientId = (string) $request->header('X-OpenApi-Client-Id');
        $timestamp = (string) $request->header('X-OpenApi-Timestamp');
        $nonce = (string) $request->header('X-OpenApi-Nonce');
        $signature = (string) $request->header('X-OpenApi-Signature');

        if ($clientId === '' || ! ctype_digit($timestamp) || ! preg_match('/^[A-Za-z0-9_-]{16,128}$/', $nonce) || $signature === '') {
            return $this->reject('invalid_request', 'OpenAPI 认证请求头不完整', 401);
        }
        if (abs(now()->timestamp - (int) $timestamp) > max(1, (int) config('openapi.timestamp_tolerance', 300))) {
            return $this->reject('timestamp_expired', '请求时间戳已过期', 401);
        }

        $credential = OpenApiCredential::query()->where('client_id', $clientId)->first();
        if (! $credential || ! $credential->is_active || $credential->expires_at?->isPast()) {
            return $this->reject('invalid_client', 'OpenAPI 调用凭据无效', 401);
        }
        if (! $this->ipIsAllowed($request->ip(), $credential->allowed_ips)) {
            return $this->reject('ip_forbidden', '当前 IP 不允许调用此凭据', 403);
        }

        $rawBody = $request->getContent();
        $canonical = implode("\n", [
            strtoupper($request->method()),
            '/'.ltrim($request->path(), '/'),
            $timestamp,
            $nonce,
            hash('sha256', $rawBody),
        ]);
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $canonical, $this->signingKey($credential->client_secret), true));
        if (! hash_equals($expected, $signature)) {
            return $this->reject('invalid_signature', '请求签名无效', 401);
        }

        $nonceKey = 'openapi:nonce:'.hash('sha256', $clientId."\0".$nonce);
        if (! Cache::add($nonceKey, true, now()->addSeconds(max(1, (int) config('openapi.nonce_ttl', 600))))) {
            return $this->reject('replayed_request', '请求已被使用', 409);
        }

        $payload = $this->decrypt($rawBody, $credential->client_secret, $clientId, $timestamp, $nonce);
        if ($payload === null) {
            Cache::forget($nonceKey);

            return $this->reject('decryption_failed', '请求内容无法解密', 400);
        }

        $request->replace($payload);
        $request->attributes->set('openapi_credential', $credential);
        $credential->forceFill(['last_used_at' => now(), 'last_used_ip' => $request->ip()])->saveQuietly();

        return $next($request);
    }

    /** @param list<string>|null $allowedIps */
    private function ipIsAllowed(?string $ip, ?array $allowedIps): bool
    {
        return $allowedIps === null || $allowedIps === [] || ($ip !== null && IpUtils::checkIp($ip, $allowedIps));
    }

    /** @return array<string, mixed>|null */
    private function decrypt(string $rawBody, string $secret, string $clientId, string $timestamp, string $nonce): ?array
    {
        try {
            $envelope = json_decode($rawBody, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }
        if (! is_array($envelope) || ! isset($envelope['ciphertext'], $envelope['iv'], $envelope['tag'])) {
            return null;
        }

        $ciphertext = $this->base64UrlDecode($envelope['ciphertext']);
        $iv = $this->base64UrlDecode($envelope['iv']);
        $tag = $this->base64UrlDecode($envelope['tag']);
        if ($ciphertext === null || $iv === null || $tag === null || strlen($iv) !== 12 || strlen($tag) !== 16) {
            return null;
        }

        $plain = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $this->encryptionKey($secret),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            implode("\n", [$clientId, $timestamp, $nonce]),
        );
        if (! is_string($plain)) {
            return null;
        }

        try {
            $payload = json_decode($plain, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($payload) ? $payload : null;
    }

    private function signingKey(string $secret): string
    {
        return hash_hkdf('sha256', $secret, 32, 'caiyun-openapi-signing-v1');
    }

    private function encryptionKey(string $secret): string
    {
        return hash_hkdf('sha256', $secret, 32, 'caiyun-openapi-encryption-v1');
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);

        return is_string($decoded) ? $decoded : null;
    }

    private function reject(string $reason, string $message, int $status): JsonResponse
    {
        return response()->json(['reason' => $reason, 'message' => $message], $status);
    }
}
