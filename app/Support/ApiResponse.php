<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * API 统一响应生成器。
 *
 * 控制器、中间件和异常处理必须通过此类生成响应，避免结构漂移。
 */
class ApiResponse
{
    /**
     * @param  array<string, string>  $headers
     */
    public static function success(
        mixed $data = [],
        int $status = 200,
        array $headers = [],
        int $options = 0,
    ): JsonResponse {
        $now = microtime(true);
        $startedAt = defined('LARAVEL_START')
            ? (float) constant('LARAVEL_START')
            : (float) ($_SERVER['REQUEST_TIME_FLOAT'] ?? $now);

        return response()->json([
            'code' => $status,
            'data' => $data === null ? (object) [] : $data,
            'meta' => [
                'timestamp' => $now,
                'response_time' => $now - $startedAt,
            ],
        ], $status, $headers, $options);
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public static function error(string $message, int $status, array $errors = []): JsonResponse
    {
        $response = [
            'code' => $status,
            'message' => $message,
        ];

        if (config('app.debug') && $errors !== []) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
