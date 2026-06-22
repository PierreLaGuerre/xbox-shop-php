<?php

declare(strict_types=1);

namespace App\Service;

use PDO;

final class AuthService
{
    public function __construct(private readonly PDO $db) {}

    public function attempt(string $username, string $password): bool
    {
        $statement = $this->db->prepare('SELECT id, usuario, password_hash FROM administrador WHERE usuario = :usuario');
        $statement->execute([':usuario' => trim($username)]);
        $admin = $statement->fetch();
        if (!$admin || !password_verify($password, $admin['password_hash'])) return false;
        session_regenerate_id(true);
        $_SESSION['admin'] = ['id' => (int) $admin['id'], 'usuario' => $admin['usuario']];
        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['admin']['id']);
    }

    public static function logout(): void
    {
        unset($_SESSION['admin']);
        session_regenerate_id(true);
    }
}
