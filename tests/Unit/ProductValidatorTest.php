<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Validation\ProductValidator;
use PHPUnit\Framework\TestCase;

final class ProductValidatorTest extends TestCase
{
    public function testAcceptsACompleteProduct(): void
    {
        self::assertSame([], ProductValidator::validate([
            'nombre' => 'Signal Lost', 'descripcion' => 'Aventura narrativa.',
            'precio' => '19.95', 'stock' => '8', 'ean13' => '5901234123457', 'imagen' => '',
        ]));
    }

    public function testReturnsErrorsForInvalidFields(): void
    {
        $errors = ProductValidator::validate([
            'nombre' => '', 'descripcion' => '', 'precio' => '-1',
            'stock' => '2.5', 'ean13' => '123', 'imagen' => 'not-an-url',
        ]);
        self::assertEqualsCanonicalizing(['nombre', 'descripcion', 'precio', 'stock', 'ean13', 'imagen'], array_keys($errors));
    }
}
