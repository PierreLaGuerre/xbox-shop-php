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

        if ($name === '' || mb_strlen($name) > 120) $errors['nombre'] = 'Escribe un nombre de hasta 120 caracteres.';
        if ($description === '' || mb_strlen($description) > 1000) $errors['descripcion'] = 'Escribe una descripción de hasta 1000 caracteres.';
        if ($price === false || $price <= 0 || $price > 99999.99) $errors['precio'] = 'Introduce un precio válido mayor que cero.';
        if ($stock === false || $stock < 0 || $stock > 99999) $errors['stock'] = 'Introduce un stock entero válido.';
        if (!Ean13Validator::isValid($ean)) $errors['ean13'] = 'Introduce un código EAN-13 válido.';
        if (trim((string) ($data['imagen'] ?? '')) !== '' && !filter_var($data['imagen'], FILTER_VALIDATE_URL)) $errors['imagen'] = 'La imagen debe ser una URL válida.';
        return $errors;
    }
}
