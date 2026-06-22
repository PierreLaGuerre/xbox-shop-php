<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Validation\Ean13Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Ean13ValidatorTest extends TestCase
{
    #[DataProvider('validCodes')]
    public function testAcceptsValidCodes(string $code): void
    {
        self::assertTrue(Ean13Validator::isValid($code));
    }

    public static function validCodes(): array
    {
        return [['4006381333931'], ['5901234123457'], ['5012345678900']];
    }

    #[DataProvider('invalidCodes')]
    public function testRejectsInvalidCodes(string $code): void
    {
        self::assertFalse(Ean13Validator::isValid($code));
    }

    public static function invalidCodes(): array
    {
        return [['4006381333932'], ['123'], ['abcdefghijklm'], [' 4006381333931']];
    }
}
