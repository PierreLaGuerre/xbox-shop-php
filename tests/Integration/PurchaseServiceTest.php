<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repository\ProductRepository;
use App\Service\PurchaseService;
use DomainException;
use PDO;
use PHPUnit\Framework\TestCase;

final class PurchaseServiceTest extends TestCase
{
    private PDO $db;
    private PurchaseService $service;

    protected function setUp(): void
    {
        if (getenv('TEST_DB_HOST') === false) self::markTestSkipped('MariaDB de integración no configurada.');
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', getenv('TEST_DB_HOST'), getenv('TEST_DB_PORT') ?: '3306', getenv('TEST_DB_NAME'));
        $this->db = new PDO($dsn, (string) getenv('TEST_DB_USER'), (string) getenv('TEST_DB_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $this->db->exec('SET FOREIGN_KEY_CHECKS=0; DROP TABLE IF EXISTS detalle_venta, venta, producto, administrador; SET FOREIGN_KEY_CHECKS=1');
        $this->db->exec((string) file_get_contents(dirname(__DIR__, 2) . '/database/schema.sql'));
        $this->db->exec((string) file_get_contents(dirname(__DIR__, 2) . '/database/seed.sql'));
        $this->service = new PurchaseService($this->db, new ProductRepository($this->db));
    }

    public function testPurchaseUpdatesStockAndCreatesSale(): void
    {
        $id = (int) $this->db->query("SELECT id FROM producto WHERE ean13='5901234123457'")->fetchColumn();
        $saleId = $this->service->purchase($id, 2);
        self::assertGreaterThan(0, $saleId);
        self::assertSame('28', $this->db->query("SELECT stock FROM producto WHERE id={$id}")->fetchColumn());
        self::assertSame('2', $this->db->query("SELECT cantidad FROM detalle_venta WHERE venta_id={$saleId}")->fetchColumn());
    }

    public function testInsufficientStockRollsBack(): void
    {
        $id = (int) $this->db->query("SELECT id FROM producto WHERE ean13='4006381333931'")->fetchColumn();
        try { $this->service->purchase($id, 10); $this->service->purchase($id, 10); }
        catch (DomainException $exception) { self::assertSame('No hay suficiente stock disponible.', $exception->getMessage()); }
        self::assertSame('1', $this->db->query('SELECT COUNT(*) FROM venta')->fetchColumn());
    }

    public function testUnknownProductRollsBack(): void
    {
        $this->expectException(DomainException::class);
        try { $this->service->purchase(999999, 1); }
        finally { self::assertSame('0', $this->db->query('SELECT COUNT(*) FROM venta')->fetchColumn()); }
    }
}
