<?php use App\Support\Csrf; ?>
<div class="section-heading admin-heading">
    <div><p class="eyebrow">Private area</p><h1>Products</h1></div>
    <a class="button" href="<?= e(url('admin/productos/nuevo')) ?>">New product</a>
</div>
<div class="table-wrap">
<table>
    <thead><tr><th>Product</th><th>EAN-13</th><th>Price</th><th>Stock</th><th><span class="sr-only">Actions</span></th></tr></thead>
    <tbody>
    <?php foreach ($products as $product): ?>
        <tr>
            <td><strong><?= e($product['nombre']) ?></strong></td>
            <td><?= e($product['ean13']) ?></td>
            <td><?= number_format((float) $product['precio'], 2, '.', ',') ?> €</td>
            <td><?= e($product['stock']) ?></td>
            <td class="actions">
                <a href="<?= e(url('admin/productos/editar?id=' . $product['id'])) ?>">Edit</a>
                <form action="<?= e(url('admin/productos/eliminar')) ?>" method="post" data-confirm="Delete this product?">
                    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                    <input type="hidden" name="id" value="<?= e($product['id']) ?>">
                    <button class="danger-link" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
