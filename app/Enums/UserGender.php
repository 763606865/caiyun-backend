<?php

namespace App\Enums;

/**
 * 用户性别。
 */
enum UserGender: int
{
    /** 未知。 */
    case Unknown = 0;

    /** 男性。 */
    case Male = 1;

    /** 女性。 */
    case Female = 2;

    /**
     * 获取用于界面展示的性别名称。
     */
    public function label(): string
    {
        return match ($this) {
            self::Unknown => '未知',
            self::Male => '男',
            self::Female => '女',
        };
    }
}
