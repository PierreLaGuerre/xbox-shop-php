<section class="empty-state">
    <p class="eyebrow">Error <?= http_response_code() ?></p>
    <h1><?= e($title) ?></h1>
    <p><?= e($message) ?></p>
    <a class="button" href="<?= e(url('catalogo')) ?>">Volver al catálogo</a>
</section>
