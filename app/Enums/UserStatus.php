<?php

namespace App\Enums;

/**
 * 用户状态。
 */
enum UserStatus: int
{
    /** 禁用。 */
    case Disabled = 0;

    /** 正常。 */
    case Normal = 1;

    /**
     * 获取用于界面展示的状态名称。
     */
    public function label(): string
    {
        return match ($this) {
            self::Disabled => '禁用',
            self::Normal => '正常',
        };
    }
}
