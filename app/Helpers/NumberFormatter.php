<?php

namespace App\Helpers;

class NumberFormatter
{
    public static function format($value, $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $value = (float) $value;

        if ($value == floor($value)) {
            return number_format($value, 0);
        }

        return rtrim(number_format($value, $decimals), '0');
    }

    public static function formatCurrency($value, $symbol = '$'): string
    {
        if ($value === null || $value === '') {
            return $symbol . '-';
        }

        $formatted = self::format($value);
        return $formatted === '-' ? $symbol . '-' : $symbol . $formatted;
    }

    public static function formatStock($value): string
    {
        return self::format($value, 2);
    }
}
