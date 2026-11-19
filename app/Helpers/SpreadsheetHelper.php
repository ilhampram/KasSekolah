<?php

namespace App\Helpers;

class SpreadsheetHelper
{
    /**
     * Prevent CSV/Excel formula injection (CWE-1236) by prepending a single quote
     * if the cell content begins with formula trigger characters (=, +, -, @, tab, CR).
     */
    public static function sanitize(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $trimmed = ltrim($value);
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
