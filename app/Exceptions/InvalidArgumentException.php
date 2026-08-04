<?php

namespace App\Exceptions;

use Throwable;

class InvalidArgumentException extends HttpException
{
    public function __construct(string $message = '参数错误.', ?Throwable $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }
}
