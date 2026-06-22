<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ProductRepository
{
    public function __construct(private readonly PDO $db) {}

    public function search(string $query = '', bool $includeOutOfStock = false): array
    {
        $sql = 'SELECT * FROM producto WHERE 1=1';
        $params = [];
        if (!$includeOutOfStock) $sql .= ' AND stock > 0';
        if ($query !== '') {
            $sql .= ' AND (nombre LIKE :query OR ean13 = :ean)';
            $params = [':query' => '%' . $query . '%', ':ean' => $query];
        }
        $sql .= ' ORDER BY nombre';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function find(int $id, bool $forUpdate = false): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM producto WHERE id = :id' . ($forUpdate ? ' FOR UPDATE' : ''));
        $statement->execute([':id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function findByEan(string $ean): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM producto WHERE ean13 = :ean');
        $statement->execute([':ean' => $ean]);
        return $statement->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO producto (nombre, descripcion, precio, stock, stock_inicial, ean13, imagen)
                VALUES (:nombre, :descripcion, :precio, :stock, :stock_inicial, :ean13, :imagen)';
        $statement = $this->db->prepare($sql);
        $params = $this->params($data);
        $params[':stock_inicial'] = (int) $data['stock'];
        $statement->execute($params);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE producto SET nombre=:nombre, descripcion=:descripcion, precio=:precio,
                stock=:stock, stock_inicial=:stock_inicial, ean13=:ean13, imagen=:imagen WHERE id=:id';
        $params = $this->params($data);
        $params[':id'] = $id;
        $params[':stock_inicial'] = (int) $data['stock'];
        $this->db->prepare($sql)->execute($params);
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM producto WHERE id = :id');
        $statement->execute([':id' => $id]);
        return $statement->rowCount() === 1;
    }

    private function params(array $data): array
    {
        return [
            ':nombre' => trim((string) $data['nombre']),
            ':descripcion' => trim((string) $data['descripcion']),
            ':precio' => number_format((float) $data['precio'], 2, '.', ''),
            ':stock' => (int) $data['stock'],
            ':ean13' => trim((string) $data['ean13']),
            ':imagen' => trim((string) ($data['imagen'] ?? '')) ?: null,
        ];
    }
}
