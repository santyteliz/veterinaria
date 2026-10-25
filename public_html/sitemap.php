<?php
/** /sitemap.xml generado desde site.json (home + páginas de servicio). */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$lastmod = date('Y-m-d', (int) (@filemtime(CONTENT_FILE) ?: time()));
$urls = [['/', '1.0']];
foreach (array_keys((array) c('pages', [])) as $slug) {
    $urls[] = ['/' . $slug, '0.8'];
}
$urls[] = ['/politica-de-privacidad', '0.2'];

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as [$path, $priority]): ?>
  <url>
    <loc><?= e(site_url($path)) ?></loc>
    <lastmod><?= $lastmod ?></lastmod>
    <priority><?= $priority ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
