<?php

declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;

require dirname(__DIR__) . '/config/bootstrap.php';

$username = trim((string) ($argv[1] ?? ''));
$password = Env::get('ADMIN_PASSWORD', '');
if ($username === '' || mb_strlen($username) > 80 || mb_strlen($password) < 12) {
    fwrite(STDERR, "Usage: define ADMIN_PASSWORD (minimum 12 characters) in .env and run php bin/create-admin.php username\n");
    exit(1);
}

$statement = Connection::get()->prepare(
    'INSERT INTO administrador (usuario, password_hash) VALUES (:usuario, :hash)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
);
$statement->execute([':usuario' => $username, ':hash' => password_hash($password, PASSWORD_DEFAULT)]);
fwrite(STDOUT, "Administrator created or updated. Remove ADMIN_PASSWORD from .env.\n");
