<?php

namespace App\SApi\Controllers;

use Illuminate\Http\JsonResponse;

/** 应用签名鉴权后的连通性检测。 */
class PingController extends Controller
{
    /**
     * 返回中台接口在线状态。
     *
     * GET /sapi/ping
     *
     * 入参：无查询参数或请求体，需携带应用签名认证请求头。
     *
     * @return JsonResponse HTTP 200，返回 code、data.message（pong）、meta；凭证无效返回 401，IP 不允许返回 403，重放返回 409
     */
    public function __invoke(): JsonResponse
    {
        return $this->success(['message' => 'pong']);
    }
}
