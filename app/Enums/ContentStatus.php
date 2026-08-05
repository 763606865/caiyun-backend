<?php

namespace App\Enums;

enum ContentStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Offline = 'offline';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Pending => '待发布',
            self::Published => '已发布',
            self::Offline => '已下线',
        };
    }
}
