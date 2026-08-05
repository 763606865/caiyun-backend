<?php

namespace App\Enums;

enum RequirementStatus: int
{
    case Pending = 0;
    case FollowedUp = 1;
    case Completed = 2;
    case Closed = 3;

    public function label(): string
    {
        return match ($this) {
            self::Pending => '待跟进',
            self::FollowedUp => '已跟进',
            self::Completed => '已完成',
            self::Closed => '已关闭',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::FollowedUp => 'info',
            self::Completed => 'success',
            self::Closed => 'gray',
        };
    }

    /** @return array<int, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
