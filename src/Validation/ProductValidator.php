<?php

declare(strict_types=1);

namespace App\Validation;

final class ProductValidator
{
    /** @return array<string, string> */
    public static function validate(array $data): array
    {
        $errors = [];
        $name = trim((string) ($data['nombre'] ?? ''));
        $description = trim((string) ($data['descripcion'] ?? ''));
        $ean = trim((string) ($data['ean13'] ?? ''));
        $price = filter_var($data['precio'] ?? null, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($data['stock'] ?? null, FILTER_VALIDATE_INT);

        if ($name === '' || mb_strlen($name) > 120) $errors['nombre'] = 'Enter a name up to 120 characters.';
        if ($description === '' || mb_strlen($description) > 1000) $errors['descripcion'] = 'Enter a description up to 1000 characters.';
        if ($price === false || $price <= 0 || $price > 99999.99) $errors['precio'] = 'Enter a valid price greater than zero.';
        if ($stock === false || $stock < 0 || $stock > 99999) $errors['stock'] = 'Enter a valid whole-number stock value.';
        if (!Ean13Validator::isValid($ean)) $errors['ean13'] = 'Enter a valid EAN-13 code.';
        if (trim((string) ($data['imagen'] ?? '')) !== '' && !filter_var($data['imagen'], FILTER_VALIDATE_URL)) $errors['imagen'] = 'The image must be a valid URL.';
        return $errors;
    }
}
