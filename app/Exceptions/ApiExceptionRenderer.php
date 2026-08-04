<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/**
 * 将 API 异常转换为统一错误响应。
 */
class ApiExceptionRenderer
{
    public function __invoke(Throwable $exception, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        return match (true) {
            $exception instanceof ValidationException => ApiResponse::error(
                $exception->getMessage(),
                422,
                $exception->errors(),
            ),
            $exception instanceof AuthenticationException => ApiResponse::error(
                'Token expired or invalid.',
                401,
            ),
            $exception instanceof ModelNotFoundException,
            $exception instanceof NotFoundHttpException => ApiResponse::error(
                '请求的资源不存在',
                404,
            ),
            $exception instanceof TooManyRequestsHttpException => ApiResponse::error(
                '请求过于频繁，请稍后再试',
                429,
            ),
            $exception instanceof HttpException => ApiResponse::error(
                $exception->getMessage(),
                $exception->getCode(),
                ['trace' => $exception->getTrace()],
            ),
            $exception instanceof HttpExceptionInterface => ApiResponse::error(
                $exception->getMessage() ?: '请求失败',
                $exception->getStatusCode(),
            ),
            default => ApiResponse::error(
                config('app.debug') ? $exception->getMessage() : '服务器内部错误',
                500,
                ['trace' => $exception->getTrace()],
            ),
        };
    }
}
