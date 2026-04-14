<?php $pageTitle = 'Login – Camagru'; ?>
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="auth-box">
    <h1>Login</h1>

    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/login" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus autocomplete="username">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
        <button type="submit" class="btn-primary">Log in</button>
    </form>

    <p class="auth-links">
        <a href="<?= APP_URL ?>/forgot-password">Forgot password?</a> &bull;
        <a href="<?= APP_URL ?>/register">Create account</a>
    </p>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
