<?php

namespace Caiyun\Sms\Facades;

use Caiyun\Sms\SmsManager;
use Caiyun\Sms\SmsResult;
use Illuminate\Support\Facades\Facade;

/**
 * @method static SmsResult send(string $mobile, string $template, array $parameters = [])
 * @method static SmsManager driver(?string $driver = null)
 *
 * @see SmsManager
 */
class Sms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sms';
    }
}
