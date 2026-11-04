<?php
/**
 * Importa fotos (JPG/PNG/WEBP) a public_html/uploads/ con el mismo proceso que el
 * panel: enderezadas, WebP de hasta 1920 px + versiones de 480/960/1440 px.
 * Imprime "archivo → /uploads/…webp" para pegar las rutas en content/site.json.
 *
 *   php tools/import-photos.php foto1.jpg foto2.jpg …
 *
 * Fotos de iPhone (.HEIC): convertirlas antes a JPG, por ejemplo con ffmpeg:
 *   ffmpeg -i IMG_0001.HEIC -frames:v 1 -q:v 2 IMG_0001.jpg
 */

declare(strict_types=1);

$files = array_slice($argv, 1);
if (!$files) {
    fwrite(STDERR, "Uso: php tools/import-photos.php <foto> [<foto> …]\n");
    exit(1);
}

require dirname(__DIR__) . '/public_html/inc/bootstrap.php';
require ROOT . '/admin/config.php';
require ROOT . '/admin/inc/store.php';
require ROOT . '/admin/inc/upload.php';

foreach ($files as $path) {
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    $src = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($path),
        'image/png' => imagecreatefrompng($path),
        'image/webp' => imagecreatefromwebp($path),
        default => null,
    };
    if (!$src) {
        fwrite(STDERR, "$path: formato no soportado ($mime)\n");
        continue;
    }
    if ($mime === 'image/jpeg') {
        $src = fix_orientation($src, $path);
    }
    $mainW = (int) round(imagesx($src) * min(1, UPLOAD_MAX_SIDE / max(imagesx($src), imagesy($src))));
    $sub = date('Y/m');
    $dir = UPLOAD_DIR . '/' . $sub;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $base = bin2hex(random_bytes(10));
    save_resized($src, $mainW, "$dir/$base.webp");
    foreach (UPLOAD_VARIANTS as $vw) {
        if ($vw < $mainW) {
            save_resized($src, $vw, "$dir/$base-$vw.webp");
        }
    }
    imagedestroy($src);
    echo basename($path), ' → ', UPLOAD_URL, "/$sub/$base.webp\n";
}
