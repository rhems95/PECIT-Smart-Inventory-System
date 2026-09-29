<?php

namespace App\Support;

final class Qty
{
    public const SCALE = 4;

    public static function of(mixed $value): float
    {
        return round((float) $value, self::SCALE);
    }

    public static function add(mixed $a, mixed $b): float
    {
        return self::of(self::of($a) + self::of($b));
    }

    public static function sub(mixed $a, mixed $b): float
    {
        return self::of(self::of($a) - self::of($b));
    }

    public static function format(mixed $value): string
    {
        $formatted = number_format(self::of($value), self::SCALE, '.', '');
        $trimmed = rtrim(rtrim($formatted, '0'), '.');

        return $trimmed === '' ? '0' : $trimmed;
    }
}
