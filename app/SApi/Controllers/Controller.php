<?php

namespace App\SApi\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/** SAPI 公共控制器，统一成功及错误响应结构。 */
abstract class Controller
{
    /**
     * 返回统一成功响应。
     *
     * @param  mixed  $data  业务数据
     * @param  int  $status  HTTP 状态码，默认 200，创建记录使用 201
     * @return JsonResponse 包含 code、data 和 meta（timestamp、response_time）
     */
    protected function success(mixed $data = [], int $status = 200): JsonResponse
    {
        return ApiResponse::success($data, $status);
    }

    /**
     * 返回统一错误响应。
     *
     * @param  string  $message  错误说明
     * @param  int  $status  HTTP 错误状态码
     * @param  array<string, mixed>  $errors  字段错误或诊断信息，仅调试模式输出
     * @return JsonResponse 包含 code、message，调试模式下可包含 errors
     */
    protected function error(string $message, int $status, array $errors = []): JsonResponse
    {
        return ApiResponse::error($message, $status, $errors);
    }
}
