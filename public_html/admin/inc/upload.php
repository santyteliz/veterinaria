<?php
/**
 * Subida de fotos: solo JPG/PNG/WEBP (MIME real con finfo), máx. 5 MB,
 * nombre aleatorio, redimensionado a 1920 px y conversión a WebP con GD.
 * Se guardan en /uploads/AAAA/MM/ (sin ejecución de PHP, ver .htaccess).
 */

declare(strict_types=1);

const UPLOAD_DIR = ROOT . '/uploads';
const UPLOAD_URL = '/uploads';
const UPLOAD_MIME = ['image/jpeg', 'image/png', 'image/webp'];
const UPLOAD_VARIANTS = [480, 960, 1440]; // anchos extra para srcset

/** Normaliza $_FILES['x'] o $_FILES['x'][clave] a una lista de archivos. */
function uploaded_files(string $field): array
{
    $f = $_FILES[$field] ?? null;
    if (!$f) {
        return [];
    }
    if (!is_array($f['name'])) {
        return [$f];
    }
    $out = [];
    foreach ($f['name'] as $key => $name) {
        $out[$key] = [
            'name' => $name,
            'type' => $f['type'][$key],
            'tmp_name' => $f['tmp_name'][$key],
            'error' => $f['error'][$key],
            'size' => $f['size'][$key],
        ];
    }
    return $out;
}

function upload_given(?array $file): bool
{
    return $file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}

/** Procesa una foto y devuelve su URL pública. Lanza RuntimeException con un mensaje claro. */
function process_image(array $file): string
{
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('“' . $file['name'] . '” pesa más de 5 MB.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('No se pudo subir “' . $file['name'] . '”. Probá de nuevo.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, UPLOAD_MIME, true)) {
        throw new RuntimeException('“' . $file['name'] . '” no es una foto JPG, PNG o WEBP.');
    }
    $info = @getimagesize($file['tmp_name']);
    if (!$info || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 50_000_000) {
        throw new RuntimeException('“' . $file['name'] . '” no se pudo leer como imagen.');
    }
    if (!function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('El servidor no tiene la extensión GD activada para procesar fotos.');
    }

    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png' => @imagecreatefrompng($file['tmp_name']),
        'image/webp' => @imagecreatefromwebp($file['tmp_name']),
    };
    if (!$src) {
        throw new RuntimeException('“' . $file['name'] . '” está dañada o no se pudo abrir.');
    }
    if ($mime === 'image/jpeg') {
        $src = fix_orientation($src, $file['tmp_name']);
    }

    $w = imagesx($src);
    $h = imagesy($src);
    $ratio = min(1, UPLOAD_MAX_SIDE / max($w, $h));
    $mainW = max(1, (int) round($w * $ratio));

    $sub = date('Y/m');
    $dir = UPLOAD_DIR . '/' . $sub;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        imagedestroy($src);
        throw new RuntimeException('No se pudo crear la carpeta de fotos. Revisá los permisos de uploads/.');
    }
    $base = bin2hex(random_bytes(10));
    $ext = function_exists('imagewebp') ? 'webp' : 'jpg';

    // Archivo principal (≤ 1920 px) + versiones chicas para srcset: base-480.webp, base-960.webp…
    $ok = save_resized($src, $mainW, "{$dir}/{$base}.{$ext}");
    foreach (UPLOAD_VARIANTS as $vw) {
        if ($ok && $vw < $mainW) {
            $ok = save_resized($src, $vw, "{$dir}/{$base}-{$vw}.{$ext}");
        }
    }
    imagedestroy($src);
    if (!$ok) {
        foreach (glob("{$dir}/{$base}*") ?: [] as $partial) {
            @unlink($partial);
        }
        throw new RuntimeException('No se pudo guardar “' . $file['name'] . '”.');
    }
    return UPLOAD_URL . '/' . $sub . '/' . $base . '.' . $ext;
}

/** Guarda $src redimensionada a $targetW de ancho (manteniendo proporción). */
function save_resized($src, int $targetW, string $path): bool
{
    $w = imagesx($src);
    $h = imagesy($src);
    $nh = max(1, (int) round($h * $targetW / $w));
    $dst = imagecreatetruecolor($targetW, $nh);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetW, $nh, $w, $h);
    $ok = str_ends_with($path, '.webp') ? imagewebp($dst, $path, 80) : imagejpeg($dst, $path, 84);
    imagedestroy($dst);
    return $ok;
}

/** Las fotos de celular vienen rotadas por EXIF: se enderezan antes de guardar. */
function fix_orientation($img, string $path)
{
    if (!function_exists('exif_read_data')) {
        return $img;
    }
    $exif = @exif_read_data($path);
    $angle = match ((int) ($exif['Orientation'] ?? 1)) {
        3 => 180,
        6 => -90,
        8 => 90,
        default => 0,
    };
    if ($angle === 0) {
        return $img;
    }
    $rotated = imagerotate($img, $angle, 0);
    if ($rotated) {
        imagedestroy($img);
        return $rotated;
    }
    return $img;
}

/**
 * Borra las fotos que ya no usa nadie: ni el contenido actual ni ninguna de
 * las versiones guardadas (así "Restaurar versión anterior" no pierde fotos).
 * Mantiene liviano el espacio en el hosting sin borrar nada recuperable.
 */
function uploads_gc(): int
{
    $base = realpath(UPLOAD_DIR);
    if (!$base) {
        return 0;
    }
    $refs = (string) @file_get_contents(CONTENT_FILE);
    foreach (glob(BACKUP_DIR . '/site-*.json') ?: [] as $backup) {
        $refs .= (string) @file_get_contents($backup);
    }
    $refs = str_replace('\\/', '/', $refs);

    $deleted = 0;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        $name = $file->getFilename();
        if (!$file->isFile() || !preg_match('/^([a-f0-9]{20})(-\d{3,4})?\.(webp|jpg)$/', $name, $m)) {
            continue; // .htaccess y cualquier archivo ajeno al panel no se tocan
        }
        // Las variantes (-480, -960…) viven mientras viva su foto principal.
        $mainPath = dirname($file->getPathname()) . DIRECTORY_SEPARATOR . $m[1] . '.' . $m[3];
        $url = UPLOAD_URL . str_replace(DIRECTORY_SEPARATOR, '/', substr($mainPath, strlen($base)));
        if (!str_contains($refs, $url) && @unlink($file->getPathname())) {
            $deleted++;
        }
    }
    return $deleted;
}
