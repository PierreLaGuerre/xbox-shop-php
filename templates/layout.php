<?php

use App\Config\Env;
use App\Service\AuthService;
use App\Support\Csrf;
use App\Support\Flash;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Xbox Shop: proyecto educativo realizado con PHP, PDO y MariaDB.">
    <title><?= e($title ?? 'Xbox Shop') ?> · Xbox Shop</title>
    <link rel="stylesheet" href="<?= e(url('assets/app.css')) ?>">
    <script src="<?= e(url('assets/app.js')) ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<header class="site-header">
    <a class="brand" href="<?= e(url('catalogo')) ?>" aria-label="Xbox Shop, inicio">
        <span class="brand-mark" aria-hidden="true">X</span>
        <span>Xbox Shop</span>
    </a>
    <nav aria-label="Navegación principal">
        <a href="<?= e(url('catalogo')) ?>">Catálogo</a>
        <a href="<?= e(url('api/productos')) ?>">API</a>
        <?php if (AuthService::check()): ?>
            <a href="<?= e(url('admin')) ?>">Administración</a>
            <form action="<?= e(url('admin/logout')) ?>" method="post" class="inline-form">
                <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                <button class="link-button" type="submit">Salir</button>
            </form>
        <?php else: ?>
            <a href="<?= e(url('admin/login')) ?>">Acceso</a>
        <?php endif; ?>
    </nav>
</header>

<main id="contenido" class="container">
    <?php if (Env::bool('DEMO_MODE', true)): ?>
        <p class="demo-notice"><strong>Demo educativa:</strong> las compras son simuladas y los datos se restauran periódicamente.</p>
    <?php endif; ?>
    <div class="flash-region" aria-live="polite">
        <?php foreach (Flash::consume() as $flash): ?>
            <p class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
        <?php endforeach; ?>
    </div>
    <?php require $templateFile; ?>
</main>

<footer class="site-footer">
    <p>Proyecto educativo de portfolio · PHP 8 · PDO · MariaDB</p>
    <p>Fan project no oficial, sin afiliación con Microsoft o Xbox.</p>
</footer>
</body>
</html>
