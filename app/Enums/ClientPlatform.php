<?php

namespace App\Enums;

enum ClientPlatform: string
{
    case Ios = 'ios';
    case Android = 'android';
    case Wechat = 'wechat';
    case Web = 'web';
}
