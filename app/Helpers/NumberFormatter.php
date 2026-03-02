<?php

namespace App\Helpers;

class NumberFormatter
{
    public static function format($value, $decimals = 0): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return number_format((int) $value, 0);
    }

    public static function formatCurrency($value, $symbol = 'K '): string
    {
        if ($value === null || $value === '') {
            return $symbol . '-';
        }

        return $symbol . number_format((int) $value);
    }

    public static function formatStock($value): string
    {
        return self::format($value);
    }
}
