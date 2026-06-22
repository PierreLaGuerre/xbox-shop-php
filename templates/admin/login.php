<?php use App\Support\Csrf; ?>
<section class="auth-card">
    <p class="eyebrow">Área privada</p>
    <h1>Administración</h1>
    <p>El acceso no se publica. Sirve para demostrar autenticación con sesiones y contraseñas hasheadas.</p>
    <form action="<?= e(url('admin/login')) ?>" method="post" class="stack-form">
        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
        <div><label for="usuario">Usuario</label><input id="usuario" name="usuario" autocomplete="username" required></div>
        <div><label for="password">Contraseña</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <button type="submit">Iniciar sesión</button>
    </form>
</section>
