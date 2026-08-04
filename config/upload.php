<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API 文件上传
    |--------------------------------------------------------------------------
    |
    | 接口默认写入 OSS。测试或本地开发时可切换为其他 Laravel 文件系统磁盘。
    | max_size 单位为 KB，允许类型同时校验扩展名和文件真实 MIME。
    |
    */
    'disk' => env('UPLOAD_DISK', 'oss'),
    'max_size' => (int) env('UPLOAD_MAX_SIZE', 20 * 1024),
    'allowed_extensions' => [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'zip',
    ],
    'directories' => [
        'avatar',
        'image',
        'document',
        'other',
    ],
];
