<?php

namespace App\Api\Controllers;

use App\Support\ClientVersionChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientVersionController extends Controller
{
    /**
     * 校验当前客户端是否需要更新。
     *
     * GET /api/client/version/check
     */
    public function check(Request $request, ClientVersionChecker $checker): JsonResponse
    {
        /** @var array<string, string> $context */
        $context = $request->attributes->get('client_context');

        $result = $checker->check($context);
        unset($result['below_minimum']);

        return $this->success($result);
    }
}
