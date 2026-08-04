<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class HttpException extends Exception
{
    public function report(): void
    {
        //
    }

    public function render(Request $request): ?JsonResponse
    {
        try {
            return ApiResponse::error(
                $this->getMessage(),
                $this->getCode(),
                ['trace' => $this->getTrace()],
            );
        } catch (Exception $e) {
            return null;
        }
    }
}
