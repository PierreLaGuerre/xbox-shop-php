<?php

declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;

require dirname(__DIR__) . '/config/bootstrap.php';

if (!Env::bool('DEMO_MODE', true)) {
    fwrite(STDERR, "Reset is only allowed with DEMO_MODE=true.\n");
    exit(1);
}

$db = Connection::get();
$db->beginTransaction();
try {
    $db->exec('DELETE FROM detalle_venta');
    $db->exec('DELETE FROM venta');
    $db->exec('UPDATE producto SET stock = stock_inicial');
    $db->commit();
    fwrite(STDOUT, "Demo restored successfully.\n");
} catch (Throwable $exception) {
    if ($db->inTransaction()) $db->rollBack();
    fwrite(STDERR, "Could not restore the demo: {$exception->getMessage()}\n");
    exit(1);
}
