<?php use App\Support\Csrf; ?>
<a class="back-link" href="<?= e(url('admin')) ?>">← Back to products</a>
<section class="form-page">
    <p class="eyebrow">Private area</p>
    <h1><?= $product ? 'Edit product' : 'New product' ?></h1>
    <form action="<?= e(url($product ? 'admin/productos/editar?id=' . $product['id'] : 'admin/productos/nuevo')) ?>" method="post" class="stack-form">
        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
        <?php foreach ([
            'nombre' => ['Name', 'text'], 'ean13' => ['EAN-13', 'text'],
            'precio' => ['Price (€)', 'number'], 'stock' => ['Stock', 'number'],
            'imagen' => ['Image URL (optional)', 'url']
        ] as $name => [$label, $type]): ?>
            <div>
                <label for="<?= e($name) ?>"><?= e($label) ?></label>
                <input id="<?= e($name) ?>" name="<?= e($name) ?>" type="<?= e($type) ?>" value="<?= e($old[$name] ?? '') ?>"
                    <?= $name !== 'imagen' ? 'required' : '' ?>
                    <?= $name === 'precio' ? 'min="0.01" step="0.01"' : '' ?>
                    <?= $name === 'stock' ? 'min="0" step="1"' : '' ?>
                    <?= isset($errors[$name]) ? 'aria-invalid="true" aria-describedby="error-' . e($name) . '"' : '' ?>>
                <?php if (isset($errors[$name])): ?><p class="field-error" id="error-<?= e($name) ?>"><?= e($errors[$name]) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div>
            <label for="descripcion">Description</label>
            <textarea id="descripcion" name="descripcion" rows="5" required <?= isset($errors['descripcion']) ? 'aria-invalid="true" aria-describedby="error-descripcion"' : '' ?>><?= e($old['descripcion'] ?? '') ?></textarea>
            <?php if (isset($errors['descripcion'])): ?><p class="field-error" id="error-descripcion"><?= e($errors['descripcion']) ?></p><?php endif; ?>
        </div>
        <button type="submit"><?= $product ? 'Save changes' : 'Create product' ?></button>
    </form>
</section>
