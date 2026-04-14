<?php
http_response_code(404);
$pageTitle = '404 – Camagru';
?>
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="auth-box" style="text-align:center">
    <h1>404</h1>
    <p>Page not found.</p>
    <a href="<?= APP_URL ?>/" class="btn-primary">Go home</a>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
