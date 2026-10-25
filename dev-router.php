<?php
/**
 * Router SOLO para probar en local con `php -S localhost:8000 -t public_html dev-router.php`.
 * Imita las reglas de public_html/.htaccess (el servidor de PHP las ignora): URLs limpias,
 * carpetas bloqueadas, y también gzip + cache de estáticos, para que Lighthouse mida algo
 * parecido a Hostinger. No se sube.
 */

$uri = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = __DIR__ . '/public_html';
$gzip = str_contains((string) ($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''), 'gzip') && function_exists('gzencode');

/** Ejecuta un .php del sitio con compresión gzip (como mod_deflate). */
$runPhp = function (string $file) use ($gzip): bool {
    if ($gzip) {
        ob_start('ob_gzhandler');
    }
    chdir(dirname($file));
    require $file;
    return true;
};

if (preg_match('#^/(content|inc|admin/inc|admin/sections)(/|$)#', $uri) || $uri === '/admin/config.php' || preg_match('#\.(json|md|lock)$#i', $uri)) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}
if ($uri === '/sitemap.xml') {
    return $runPhp($root . '/sitemap.php');
}
if ($uri === '/robots.txt') {
    return $runPhp($root . '/robots.php');
}

// Estáticos: tipo, cache largo y gzip para texto.
$file = realpath($root . $uri);
if ($file && is_file($file) && str_starts_with($file, realpath($root)) && !str_ends_with($file, '.php')) {
    $types = [
        'css' => 'text/css; charset=utf-8', 'js' => 'application/javascript; charset=utf-8',
        'svg' => 'image/svg+xml', 'woff2' => 'font/woff2', 'webp' => 'image/webp',
        'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'txt' => 'text/plain',
    ];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    header(in_array($ext, ['css', 'js', 'woff2'], true) ? 'Cache-Control: public, max-age=31536000, immutable' : 'Cache-Control: public, max-age=2592000');
    $body = (string) file_get_contents($file);
    if ($gzip && in_array($ext, ['css', 'js', 'svg', 'txt'], true)) {
        header('Content-Encoding: gzip');
        header('Vary: Accept-Encoding');
        $body = gzencode($body, 6);
    }
    header('Content-Length: ' . strlen($body));
    echo $body;
    return true;
}

// Carpetas con index.php (/, /admin/).
if (is_dir($root . $uri) && is_file(rtrim($root . $uri, '/') . '/index.php')) {
    return $runPhp(rtrim($root . $uri, '/') . '/index.php');
}
if (str_ends_with($uri, '.php') && is_file($root . $uri)) {
    return $runPhp($root . $uri);
}
if ($uri === '/politica-de-privacidad') {
    return $runPhp($root . '/politica-de-privacidad.php');
}
if (preg_match('#^/([a-z0-9-]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    return $runPhp($root . '/servicio.php');
}
return $runPhp($root . '/404.php');
