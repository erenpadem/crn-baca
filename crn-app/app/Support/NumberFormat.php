<?php

namespace App\Support;

class NumberFormat
{
    /** Türkçe: virgül ondalık, gereksiz sıfırları kaldırır. 15.0000 → "15", 15.5 → "15,5" */
    public static function formatForInput(?float $value, int $maxDecimals = 4): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = (float) $value;
        $rounded = round($value, $maxDecimals);
        if ($rounded == (int) $rounded) {
            return (string) (int) $rounded;
        }
        $formatted = number_format($rounded, $maxDecimals, ',', '');
        return rtrim(rtrim($formatted, '0'), ',');
    }

    /** Girdiyi float'a çevirir: "15,5" veya "15.5" veya "15" veya 15 → 15.5. Null/boş için null döner. */
    public static function parseInput(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        if (is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/\s+/', '', $value);
        if (! is_numeric($value)) {
            return null;
        }
        return (float) $value;
    }
}
