<?php

namespace App\OpenApi\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * @throws \Exception
     */
    protected function success(mixed $data = [], int $status = 200): JsonResponse
    {
        return ApiResponse::success($data, $status);
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    protected function error(string $message, int $status, array $errors = [], ?string $reason = null): JsonResponse
    {
        $response = ApiResponse::error($message, $status, $errors);

        // reason 是 OpenAPI 调用方用于区分业务拒绝类型的稳定机器码。
        if ($reason !== null) {
            $payload = $response->getData(true);
            $payload['reason'] = $reason;
            $response->setData($payload);
        }

        return $response;
    }
}
