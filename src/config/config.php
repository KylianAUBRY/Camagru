<?php

define('APP_URL',       rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'));
define('DB_HOST',       $_ENV['DB_HOST']       ?? 'db');
define('DB_NAME',       $_ENV['DB_NAME']       ?? 'camagru');
define('DB_USER',       $_ENV['DB_USER']       ?? 'camagru_user');
define('DB_PASS',       $_ENV['DB_PASS']       ?? '');
define('MAIL_HOST',     $_ENV['MAIL_HOST']     ?? 'mailhog');
define('MAIL_PORT',     (int)($_ENV['MAIL_PORT'] ?? 1025));
define('MAIL_FROM',     $_ENV['MAIL_FROM']     ?? 'noreply@camagru.local');
define('MAIL_FROM_NAME',$_ENV['MAIL_FROM_NAME'] ?? 'Camagru');

define('UPLOAD_DIR', dirname(__DIR__) . '/public/uploads/');
define('OVERLAY_DIR', dirname(__DIR__) . '/public/overlays/');
define('GALLERY_PER_PAGE', 5);
define('MAX_FILE_SIZE', 10 * 1024 * 1024);
