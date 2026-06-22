<?php

declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;

require dirname(__DIR__) . '/config/bootstrap.php';

$username = trim((string) ($argv[1] ?? ''));
$password = Env::get('ADMIN_PASSWORD', '');
if ($username === '' || mb_strlen($username) > 80 || mb_strlen($password) < 12) {
    fwrite(STDERR, "Uso: define ADMIN_PASSWORD (mínimo 12 caracteres) en .env y ejecuta php bin/create-admin.php usuario\n");
    exit(1);
}

$statement = Connection::get()->prepare(
    'INSERT INTO administrador (usuario, password_hash) VALUES (:usuario, :hash)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
);
$statement->execute([':usuario' => $username, ':hash' => password_hash($password, PASSWORD_DEFAULT)]);
fwrite(STDOUT, "Administrador creado o actualizado. Retira ADMIN_PASSWORD de .env.\n");
