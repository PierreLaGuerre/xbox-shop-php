<?php use App\Support\Csrf; ?>
<a class="back-link" href="<?= e(url('catalogo')) ?>">← Back to catalogue</a>
<article class="product-detail">
    <div class="cover cover--large">
        <?php if ($product['imagen']): ?>
            <img src="<?= e($product['imagen']) ?>" alt="Cover art for <?= e($product['nombre']) ?>">
        <?php else: ?>
            <span aria-hidden="true"><?= e(mb_substr($product['nombre'], 0, 1)) ?></span>
        <?php endif; ?>
    </div>
    <div>
        <p class="eyebrow">EAN <?= e($product['ean13']) ?></p>
        <h1><?= e($product['nombre']) ?></h1>
        <p class="lead"><?= e($product['descripcion']) ?></p>
        <p class="detail-price"><?= number_format((float) $product['precio'], 2, '.', ',') ?> €</p>
        <?php if ((int) $product['stock'] > 0): ?>
            <p class="stock stock--available">Available · <?= e($product['stock']) ?> units</p>
            <form class="purchase-form" action="<?= e(url('comprar')) ?>" method="post">
                <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="producto_id" value="<?= e($product['id']) ?>">
                <div>
                    <label for="cantidad">Quantity</label>
                    <input id="cantidad" name="cantidad" type="number" min="1" max="<?= min(10, (int) $product['stock']) ?>" value="1" required>
                </div>
                <button type="submit">Simulate purchase</button>
            </form>
        <?php else: ?>
            <p class="stock stock--empty">Temporarily out of stock</p>
        <?php endif; ?>
    </div>
</article>
