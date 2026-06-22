<section class="hero">
    <p class="eyebrow">Colección independiente</p>
    <h1>Juegos para tu próxima aventura</h1>
    <p>Un catálogo ficticio construido para demostrar PHP, consultas seguras y transacciones reales.</p>
</section>

<form class="search" action="<?= e(url('catalogo')) ?>" method="get" role="search">
    <label for="q">Buscar por nombre o EAN-13</label>
    <div class="search-row">
        <input id="q" name="q" type="search" value="<?= e($query) ?>" placeholder="Ej. Neon Apex" maxlength="120">
        <button type="submit">Buscar</button>
    </div>
</form>

<div class="section-heading">
    <h2><?= $query !== '' ? 'Resultados' : 'Catálogo' ?></h2>
    <span><?= count($products) ?> <?= count($products) === 1 ? 'juego' : 'juegos' ?></span>
</div>

<?php if ($products === []): ?>
    <div class="empty-state">
        <h2>Sin resultados</h2>
        <p>Prueba con otro nombre o revisa el código EAN.</p>
        <a class="button button--secondary" href="<?= e(url('catalogo')) ?>">Ver todo el catálogo</a>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <article class="product-card">
                <a href="<?= e(url('producto?id=' . $product['id'])) ?>" class="cover" aria-label="Ver <?= e($product['nombre']) ?>">
                    <?php if ($product['imagen']): ?>
                        <img src="<?= e($product['imagen']) ?>" alt="Portada de <?= e($product['nombre']) ?>" loading="lazy">
                    <?php else: ?>
                        <span aria-hidden="true"><?= e(mb_substr($product['nombre'], 0, 1)) ?></span>
                    <?php endif; ?>
                </a>
                <div class="product-card__body">
                    <span class="stock stock--available">En stock · <?= e($product['stock']) ?></span>
                    <h3><a href="<?= e(url('producto?id=' . $product['id'])) ?>"><?= e($product['nombre']) ?></a></h3>
                    <p><?= e($product['descripcion']) ?></p>
                    <div class="product-card__footer">
                        <strong><?= number_format((float) $product['precio'], 2, ',', '.') ?> €</strong>
                        <a href="<?= e(url('producto?id=' . $product['id'])) ?>">Ver juego <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
