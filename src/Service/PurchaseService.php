<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ProductRepository;
use PDO;
use Throwable;

final class PurchaseService
{
    public function __construct(private readonly PDO $db, private readonly ProductRepository $products) {}

    public function purchase(int $productId, int $quantity): int
    {
        if ($quantity < 1 || $quantity > 10) throw new \DomainException('La cantidad debe estar entre 1 y 10.');

        $this->db->beginTransaction();
        try {
            $product = $this->products->find($productId, true);
            if ($product === null) throw new \DomainException('El producto no existe.');
            if ((int) $product['stock'] < $quantity) throw new \DomainException('No hay suficiente stock disponible.');

            $stock = (int) $product['stock'] - $quantity;
            $statement = $this->db->prepare('UPDATE producto SET stock = :stock WHERE id = :id');
            $statement->execute([':stock' => $stock, ':id' => $productId]);

            $total = number_format((float) $product['precio'] * $quantity, 2, '.', '');
            $this->db->prepare('INSERT INTO venta (total) VALUES (:total)')->execute([':total' => $total]);
            $saleId = (int) $this->db->lastInsertId();
            $this->db->prepare('INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario)
                VALUES (:venta, :producto, :cantidad, :precio)')->execute([
                    ':venta' => $saleId, ':producto' => $productId,
                    ':cantidad' => $quantity, ':precio' => $product['precio'],
                ]);
            $this->db->commit();
            return $saleId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }
}
