<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Camagru') ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= APP_URL ?>/" class="logo">&#128247; Camagru</a>
        <nav>
            <a href="<?= APP_URL ?>/">Gallery</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= APP_URL ?>/edit">Edit</a>
                <a href="<?= APP_URL ?>/profile">Profile</a>
                <a href="<?= APP_URL ?>/logout">Logout</a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/login">Login</a>
                <a href="<?= APP_URL ?>/register">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="site-main">
