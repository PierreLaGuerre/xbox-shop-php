<section class="empty-state">
    <h1><?= e($title ?? 'Error') ?></h1>
    <p><?= e($message ?? 'Something went wrong.') ?></p>
    <a class="button" href="<?= e(url('catalogo')) ?>">Back to catalogue</a>
</section>
