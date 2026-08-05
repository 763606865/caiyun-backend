<?php

namespace App\Enums;

enum CategoryType: string
{
    case List = 'list';
    case Page = 'page';
    case Link = 'link';

    public function label(): string
    {
        return match ($this) {
            self::List => '列表',
            self::Page => '单页',
            self::Link => '外链',
        };
    }
}
