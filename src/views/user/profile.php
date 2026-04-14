<?php $pageTitle = 'Profile – Camagru'; ?>
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="auth-box">
    <h1>My Profile</h1>

    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/profile" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <label for="username">Username</label>
        <input type="text" id="username" name="username" required
               value="<?= htmlspecialchars($user['username']) ?>"
               pattern="[a-zA-Z0-9_]{3,50}" title="3–50 chars: letters, numbers, underscore">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required
               value="<?= htmlspecialchars($user['email']) ?>">

        <label class="checkbox-label">
            <input type="checkbox" name="notify_comments" <?= $user['notify_comments'] ? 'checked' : '' ?>>
            Notify me by email when someone comments on my photos
        </label>

        <hr>
        <p class="form-hint">Leave password fields empty to keep your current password.</p>

        <label for="password">New password</label>
        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password">
        <small>Min. 8 characters, one uppercase letter, one number.</small>

        <label for="confirm">Confirm new password</label>
        <input type="password" id="confirm" name="confirm" minlength="8" autocomplete="new-password">

        <button type="submit" class="btn-primary">Save changes</button>
    </form>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
