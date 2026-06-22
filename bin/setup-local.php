<?php

declare(strict_types=1);

use App\Config\Env;

require dirname(__DIR__) . '/config/bootstrap.php';

$database = Env::get('DB_NAME');
$username = Env::get('DB_USER');
$password = Env::get('DB_PASS');
if (!preg_match('/^[a-zA-Z0-9_]+$/', $database) || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    fwrite(STDERR, "DB_NAME y DB_USER solo pueden contener letras, números y guion bajo.\n");
    exit(1);
}

try {
    $root = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4', Env::get('DB_HOST'), Env::get('DB_PORT', '3306')),
        Env::get('ROOT_DB_USER', 'root'),
        Env::get('ROOT_DB_PASS', ''),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $root->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $quotedPassword = $root->quote($password);
    $root->exec("CREATE USER IF NOT EXISTS '{$username}'@'localhost' IDENTIFIED BY {$quotedPassword}");
    $root->exec("GRANT ALL PRIVILEGES ON `{$database}`.* TO '{$username}'@'localhost'");

    $db = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', Env::get('DB_HOST'), Env::get('DB_PORT', '3306'), $database),
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $db->exec((string) file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
    $db->exec((string) file_get_contents(dirname(__DIR__) . '/database/seed.sql'));
    fwrite(STDOUT, "Base local preparada con " . $db->query('SELECT COUNT(*) FROM producto')->fetchColumn() . " productos.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "No se pudo preparar la base local: {$exception->getMessage()}\n");
    exit(1);
}
