<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Client Version Policy Cache
    |--------------------------------------------------------------------------
    |
    | 客户端最新版本策略的缓存秒数。后台通过 Eloquent 修改版本时会主动失效缓存。
    |
    */
    'version_cache_ttl' => env('CLIENT_VERSION_CACHE_TTL', 300),

    /* 版本过低时仍需放行的 API 路径。 */
    'version_check_excluded_paths' => [
        'api/client/version/check',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Request Database Log
    |--------------------------------------------------------------------------
    |
    | 是否将每次 API 请求写入 api_request_logs。高流量环境建议保持关闭，
    | 如需短期排障或审计，可通过环境变量临时开启。
    |
    */
    'request_log_enabled' => env('API_REQUEST_LOG_ENABLED', false),
];
