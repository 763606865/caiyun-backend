<?php

namespace App\SApi\Middleware;

use App\Models\PlatformApplication;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApplication
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) $request->header('X-App-Key');
        $timestamp = (string) $request->header('X-Timestamp');
        $nonce = (string) $request->header('X-Nonce');
        $signature = (string) $request->header('X-Signature');
        if (! ctype_digit($timestamp) || abs(now()->timestamp - (int) $timestamp) > 300 || ! preg_match('/^[A-Za-z0-9_-]{16,128}$/', $nonce)) {
            return ApiResponse::error('认证请求头无效或已过期', 401);
        }
        $application = PlatformApplication::query()->where('app_key', $key)->first();
        if (! $application || ! $application->is_enabled || $application->expires_at?->isPast()) {
            return ApiResponse::error('应用凭证无效', 401);
        }
        if ($application->allowed_ips && ! IpUtils::checkIp($request->ip(), $application->allowed_ips)) {
            return ApiResponse::error('IP 不在白名单内', 403);
        }
        $canonical = implode("\n", [strtoupper($request->method()), $request->getRequestUri(), $timestamp, $nonce, hash('sha256', $request->getContent())]);
        if (! hash_equals(hash_hmac('sha256', $canonical, $application->app_secret), $signature)) {
            return ApiResponse::error('签名无效', 401);
        }
        if (! Cache::add('sapi:nonce:'.hash('sha256', $key."\0".$nonce), true, 600)) {
            return ApiResponse::error('重复请求', 409);
        }
        $request->attributes->set('sapi_application', $application);

        return $next($request);
    }
}
