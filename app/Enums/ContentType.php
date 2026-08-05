<?php

namespace App\Enums;

enum ContentType: string
{
    case Article = 'article';
    case Page = 'page';
    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Article => '文章',
            self::Page => '单页',
            self::External => '外链',
        };
    }
}
