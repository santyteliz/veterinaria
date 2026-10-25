<?php
/**
 * Carga las fotos de ejemplo (Unsplash) en la web, procesadas igual que las del panel:
 * WebP de hasta 1920 px + versiones de 480/960/1440 px en public_html/uploads/.
 *
 *   php tools/import-example-photos.php <carpeta-con-los-jpg>
 *
 * Espera los archivos hero.jpg, p1..p9.jpg (pacientitos), ba1b/ba1a, ba2b/ba2a
 * (antes/después de peluquería) y s1..s4.jpg (pet shop). Deja un aviso arriba en la
 * web avisando que son fotos de ejemplo. Para volver atrás: panel › Versiones.
 */

declare(strict_types=1);

$srcDir = rtrim($argv[1] ?? '', '/\\');
if ($srcDir === '' || !is_dir($srcDir)) {
    fwrite(STDERR, "Uso: php tools/import-example-photos.php <carpeta>\n");
    exit(1);
}

require dirname(__DIR__) . '/public_html/inc/bootstrap.php';
require ROOT . '/admin/config.php';
require ROOT . '/admin/inc/store.php';
require ROOT . '/admin/inc/upload.php';

/** Misma conversión que process_image() del panel, pero desde un archivo local. */
function import_photo(string $path): string
{
    $src = imagecreatefromjpeg($path);
    if (!$src) {
        throw new RuntimeException("No se pudo abrir $path");
    }
    $src = fix_orientation($src, $path);
    $mainW = min(imagesx($src), (int) round(imagesx($src) * min(1, UPLOAD_MAX_SIDE / max(imagesx($src), imagesy($src)))));
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
    return UPLOAD_URL . "/$sub/$base.webp";
}

$f = fn (string $key) => $srcDir . '/' . $key . '.jpg';
$data = store_load();

$data['hero']['image'] = import_photo($f('hero'));
$data['hero']['image_alt'] = 'Perro blanco y gato abrazados (foto de ejemplo)';

$data['patients'] = [];
for ($i = 1; $i <= 9; $i++) {
    if (is_file($f("p$i"))) {
        $data['patients'][] = ['image' => import_photo($f("p$i")), 'name' => '', 'alt' => 'Pacientito (foto de ejemplo)'];
    }
}

$data['grooming']['before_after'] = [];
foreach (['ba1', 'ba2'] as $pair) {
    $data['grooming']['before_after'][] = [
        'before' => import_photo($f($pair . 'b')),
        'after' => import_photo($f($pair . 'a')),
        'alt' => 'Perro antes y después de la peluquería (foto de ejemplo)',
    ];
}

foreach ((array) ($data['petshop']['items'] ?? []) as $i => $item) {
    $file = $f('s' . ($i + 1));
    if (is_file($file)) {
        $data['petshop']['items'][$i]['image'] = import_photo($file);
    }
}

// Aviso honesto mientras la web muestre fotos de ejemplo.
$data['announcement'] = [
    'enabled' => true,
    'text' => 'Versión de prueba: las fotos son de ejemplo y se reemplazan por las de la clínica.',
    'style' => 'plum',
    'expires' => '',
];

store_save($data, 'fotos de ejemplo');
echo "Listo: hero, " . count($data['patients']) . " pacientitos, " . count($data['grooming']['before_after']) . " antes/después y pet shop.\n";
