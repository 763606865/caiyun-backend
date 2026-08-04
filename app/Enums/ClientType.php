<?php

namespace App\Enums;

enum ClientType: string
{
    case Web = 'web';
    case WechatMini = 'wechat-mini';
    case AppIos = 'app-ios';
    case AppAndroid = 'app-android';
}
