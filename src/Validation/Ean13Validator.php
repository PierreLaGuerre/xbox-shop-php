<?php

declare(strict_types=1);

namespace App\Validation;

final class Ean13Validator
{
    public static function isValid(string $ean): bool
    {
        if (!preg_match('/^\d{13}$/', $ean)) {
            return false;
        }
        $sum = 0;
        for ($index = 0; $index < 12; $index++) {
            $sum += (int) $ean[$index] * ($index % 2 === 0 ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10 === (int) $ean[12];
    }
}
