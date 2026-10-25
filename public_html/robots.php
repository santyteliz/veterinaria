<?php
/** /robots.txt generado (el dominio sale de site.json). */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\n";
echo "Disallow: /admin/\n";
echo "Disallow: /content/\n";
echo "Disallow: /inc/\n";
echo "Disallow: /servicio.php\n";
echo "\n";
echo 'Sitemap: ' . site_url('/sitemap.xml') . "\n";
