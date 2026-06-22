<?php

declare(strict_types=1);

namespace App\Support;

final class View
{
    public static function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $templateFile = dirname(__DIR__, 2) . '/templates/' . $template . '.php';
        if (!is_file($templateFile)) throw new \RuntimeException("No existe la vista {$template}.");
        require dirname(__DIR__, 2) . '/templates/layout.php';
    }
}
