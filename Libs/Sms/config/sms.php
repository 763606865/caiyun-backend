<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    */
    'default' => env('SMS_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Template Aliases
    |--------------------------------------------------------------------------
    |
    | Business code should use aliases instead of provider template IDs.
    */
    'templates' => [
        'verification_code' => env('SMS_VERIFICATION_CODE_TEMPLATE', 'verification_code'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Drivers
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        'log' => [
            'driver' => 'log',
            'channel' => env('SMS_LOG_CHANNEL'),
        ],

        'aliyun' => [
            'driver' => 'aliyun',
            'access_key_id' => env('ALIYUN_SMS_ACCESS_KEY_ID'),
            'access_key_secret' => env('ALIYUN_SMS_ACCESS_KEY_SECRET'),
            'sign_name' => env('ALIYUN_SMS_SIGN_NAME'),
            'endpoint' => env('ALIYUN_SMS_ENDPOINT', 'dysmsapi.aliyuncs.com'),
        ],

        'tencent' => [
            'driver' => 'tencent',
            'secret_id' => env('TENCENT_SMS_SECRET_ID'),
            'secret_key' => env('TENCENT_SMS_SECRET_KEY'),
            'sdk_app_id' => env('TENCENT_SMS_SDK_APP_ID'),
            'sign_name' => env('TENCENT_SMS_SIGN_NAME'),
            'region' => env('TENCENT_SMS_REGION', 'ap-guangzhou'),
        ],
    ],
];
