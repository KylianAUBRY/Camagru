<?php $pageTitle = 'Reset Password – Camagru'; ?>
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="auth-box">
    <h1>Reset Password</h1>

    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/reset-password">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <label for="password">New password</label>
        <input type="password" id="password" name="password" required minlength="8" autofocus>
        <small>Min. 8 characters, one uppercase letter, one number.</small>
        <label for="confirm">Confirm new password</label>
        <input type="password" id="confirm" name="confirm" required minlength="8">
        <button type="submit" class="btn-primary">Update password</button>
    </form>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
