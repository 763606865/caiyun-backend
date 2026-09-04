<?php

return [
    'private_key' => env('LICENSE_SERVER_PRIVATE_KEY'),
    'private_key_path' => env('LICENSE_SERVER_PRIVATE_KEY_PATH', storage_path('app/license/private.pem')),
    'public_key_path' => env('LICENSE_SERVER_PUBLIC_KEY_PATH', storage_path('app/license/public.pem')),
    'private_key_passphrase' => env('LICENSE_SERVER_PRIVATE_KEY_PASSPHRASE'),
    'lease_hours' => (int) env('LICENSE_SERVER_LEASE_HOURS', 72),
    'heartbeat_abnormal_days' => (int) env('LICENSE_HEARTBEAT_ABNORMAL_DAYS', 7),
    /** 写入授权书 endpoints 的对外基址；未设置时回退 APP_URL */
    'public_base_url' => env('LICENSE_PUBLIC_BASE_URL', env('APP_URL')),
];
