<?php

declare(strict_types=1);

use App\Config\Env;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    throw new RuntimeException('Faltan las dependencias. Ejecuta: composer install');
}

require $autoload;

Env::load(dirname(__DIR__) . '/.env');
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Europe/Madrid'));

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_name('xbox_shop_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => Env::bool('SESSION_SECURE', false),
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}
