<?php use App\Support\Csrf; ?>
<section class="auth-card">
    <p class="eyebrow">Private area</p>
    <h1>Admin</h1>
    <p>Access is not published. This area demonstrates session authentication and hashed passwords.</p>
    <form action="<?= e(url('admin/login')) ?>" method="post" class="stack-form">
        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
        <div><label for="usuario">User</label><input id="usuario" name="usuario" autocomplete="username" required></div>
        <div><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <button type="submit">Log in</button>
    </form>
</section>
