<?php

namespace App\Traits;

trait SanitizesExcelFormulas
{
    /**
     * A leading =, +, -, @, tab or CR makes spreadsheet apps treat the cell as a
     * formula. User-entered text (names, notes, ...) must never be exported as-is.
     */
    private function looksLikeFormula($value): bool
    {
        return is_string($value)
            && $value !== ''
            && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true);
    }
}
