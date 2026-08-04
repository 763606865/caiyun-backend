<?php

namespace App\Api\Controllers;

use App\Exceptions\UnauthenticatedException;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * @throws \Exception
     */
    protected function success(mixed $data = []): JsonResponse
    {
        return ApiResponse::success($data);
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    protected function error(string $message, int $status, array $errors = []): JsonResponse
    {
        return ApiResponse::error($message, $status, $errors);
    }

    public function user(): User
    {
        $user = auth()->guard('api')->user();
        if (! $user instanceof User) {
            throw new UnauthenticatedException('Token Expired。');
        }

        return $user;
    }
}
