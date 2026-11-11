<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use UnexpectedValueException;

/** 本地省、市、区县字典，地区码统一使用六位字符串。 */
class ChinaRegions
{
    /**
     * @var array{
     *     provinces: array<array-key, string>,
     *     cities: array<array-key, array<array-key, string>>,
     *     districts: array<array-key, array<array-key, string>>
     * }|null
     */
    private static ?array $data = null;

    /** @return array<array-key, string> */
    public static function provinces(): array
    {
        return self::data()['provinces'];
    }

    /** @return array<array-key, string> */
    public static function cities(?string $province): array
    {
        return self::data()['cities'][$province ?? ''] ?? [];
    }

    /** @return array<array-key, string> */
    public static function districts(?string $city): array
    {
        return self::data()['districts'][$city ?? ''] ?? [];
    }

    /** @return array{province: ?string, city: ?string, district: ?string} */
    public static function path(?string $code): array
    {
        $path = ['province' => null, 'city' => null, 'district' => null];

        if ($code === null || ! preg_match('/^\d{6}$/', $code)) {
            return $path;
        }

        $province = substr($code, 0, 2).'0000';
        $city = substr($code, 0, 4).'00';

        if (! isset(self::provinces()[$province])) {
            return $path;
        }

        $path['province'] = $province;

        if (isset(self::cities($province)[$city])) {
            $path['city'] = $city;

            if (isset(self::districts($city)[$code])) {
                $path['district'] = $code;
            }
        }

        return $path;
    }

    /**
     * @return array{
     *     provinces: array<array-key, string>,
     *     cities: array<array-key, array<array-key, string>>,
     *     districts: array<array-key, array<array-key, string>>
     * }
     */
    private static function data(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $data = File::json(resource_path('data/china-regions.json'), JSON_THROW_ON_ERROR);

        return self::$data = [
            'provinces' => self::readOptions($data['provinces'] ?? null),
            'cities' => self::readChildren($data['cities'] ?? null),
            'districts' => self::readChildren($data['districts'] ?? null),
        ];
    }

    /** @return array<array-key, string> */
    private static function readOptions(mixed $options): array
    {
        if (! is_array($options)) {
            throw new UnexpectedValueException('地区字典必须是地区码与名称的映射。');
        }

        $result = [];

        foreach ($options as $code => $name) {
            if (! is_string($name)) {
                throw new UnexpectedValueException('地区名称必须是字符串。');
            }

            $result[$code] = $name;
        }

        return $result;
    }

    /** @return array<array-key, array<array-key, string>> */
    private static function readChildren(mixed $children): array
    {
        if (! is_array($children)) {
            throw new UnexpectedValueException('地区字典必须包含父级与子级的映射。');
        }

        return array_map(self::readOptions(...), $children);
    }
}
