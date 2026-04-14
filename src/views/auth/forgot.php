<?php $pageTitle = 'Forgot Password – Camagru'; ?>
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="auth-box">
    <h1>Forgot Password</h1>

    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/forgot-password">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <label for="email">Your email address</label>
        <input type="email" id="email" name="email" required autofocus>
        <button type="submit" class="btn-primary">Send reset link</button>
    </form>

    <p class="auth-links">
        <a href="<?= APP_URL ?>/login">Back to login</a>
    </p>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
