<section class="hero">
    <div class="hero-orb" aria-hidden="true">
        <span class="hero-orb__core">X</span>
        <i></i><i></i><i></i>
    </div>
    <div class="hero-console">
        <p class="eyebrow">System / game library</p>
        <h1>Xbox<br>Shop</h1>
        <p>Fictional game catalogue powered by PHP, PDO and MariaDB.</p>
        <form class="search" action="<?= e(url('catalogo')) ?>" method="get" role="search">
            <label for="q"><span aria-hidden="true">›</span> Search by name or EAN-13</label>
            <div class="search-row">
                <input id="q" name="q" type="search" value="<?= e($query) ?>" placeholder="NEON APEX_" maxlength="120">
                <button type="submit">Run</button>
            </div>
        </form>
        <p class="system-status"><span></span> SYSTEM ONLINE · DATABASE CONNECTED</p>
    </div>
</section>

<div class="section-heading">
    <h2><?= $query !== '' ? 'Results' : 'Catalogue' ?></h2>
    <span><?= count($products) ?> <?= count($products) === 1 ? 'game' : 'games' ?></span>
</div>

<?php if ($products === []): ?>
    <div class="empty-state">
        <h2>No results</h2>
        <p>Try another name or check the EAN code.</p>
        <a class="button button--secondary" href="<?= e(url('catalogo')) ?>">View full catalogue</a>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <article class="product-card">
                <a href="<?= e(url('producto?id=' . $product['id'])) ?>" class="cover" aria-label="View <?= e($product['nombre']) ?>">
                    <?php if ($product['imagen']): ?>
                        <img src="<?= e($product['imagen']) ?>" alt="Cover art for <?= e($product['nombre']) ?>" loading="lazy">
                    <?php else: ?>
                        <span aria-hidden="true"><?= e(mb_substr($product['nombre'], 0, 1)) ?></span>
                    <?php endif; ?>
                </a>
                <div class="product-card__body">
                    <span class="stock stock--available">In stock · <?= e($product['stock']) ?></span>
                    <h3><a href="<?= e(url('producto?id=' . $product['id'])) ?>"><?= e($product['nombre']) ?></a></h3>
                    <p><?= e($product['descripcion']) ?></p>
                    <div class="product-card__footer">
                        <strong><?= number_format((float) $product['precio'], 2, '.', ',') ?> €</strong>
                        <a href="<?= e(url('producto?id=' . $product['id'])) ?>">View game <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
