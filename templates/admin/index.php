<?php use App\Support\Csrf; ?>
<div class="section-heading admin-heading">
    <div><p class="eyebrow">Área privada</p><h1>Productos</h1></div>
    <a class="button" href="<?= e(url('admin/productos/nuevo')) ?>">Nuevo producto</a>
</div>
<div class="table-wrap">
<table>
    <thead><tr><th>Producto</th><th>EAN-13</th><th>Precio</th><th>Stock</th><th><span class="sr-only">Acciones</span></th></tr></thead>
    <tbody>
    <?php foreach ($products as $product): ?>
        <tr>
            <td><strong><?= e($product['nombre']) ?></strong></td>
            <td><?= e($product['ean13']) ?></td>
            <td><?= number_format((float) $product['precio'], 2, ',', '.') ?> €</td>
            <td><?= e($product['stock']) ?></td>
            <td class="actions">
                <a href="<?= e(url('admin/productos/editar?id=' . $product['id'])) ?>">Editar</a>
                <form action="<?= e(url('admin/productos/eliminar')) ?>" method="post" data-confirm="¿Eliminar este producto?">
                    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                    <input type="hidden" name="id" value="<?= e($product['id']) ?>">
                    <button class="danger-link" type="submit">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
