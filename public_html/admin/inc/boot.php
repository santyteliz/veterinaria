<?php
/** Arranque común de todas las páginas del panel. */

declare(strict_types=1);

require dirname(__DIR__, 2) . '/inc/bootstrap.php';
require dirname(__DIR__) . '/config.php';
require __DIR__ . '/store.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/upload.php';
require __DIR__ . '/form.php';
require __DIR__ . '/layout.php';

define('ADMIN_DIR', dirname(__DIR__));
define('AUTH_FILE', ROOT . '/content/auth.json');
define('ATTEMPTS_FILE', ROOT . '/content/login-attempts.json');

// Cabeceras: el panel no se indexa, no se cachea ni se puede embeber.
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

admin_session_start();
