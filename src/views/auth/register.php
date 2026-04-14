<?php $pageTitle = 'Register – Camagru'; ?>
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="auth-box">
    <h1>Create Account</h1>

    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/register" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus
               pattern="[a-zA-Z0-9_]{3,50}" title="3–50 chars: letters, numbers, underscore">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">
        <small>Min. 8 characters, one uppercase letter, one number.</small>
        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" required minlength="8">
        <button type="submit" class="btn-primary">Register</button>
    </form>

    <p class="auth-links">
        Already have an account? <a href="<?= APP_URL ?>/login">Log in</a>
    </p>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
