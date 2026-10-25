<?php
/**
 * Lectura y escritura de content/site.json.
 * Escritura atómica (archivo temporal + rename) bajo lock, con backup
 * automático de las últimas BACKUPS_KEEP versiones en content/backups/.
 */

declare(strict_types=1);

const BACKUP_DIR = ROOT . '/content/backups';

function write_file_atomic(string $path, string $contents): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('No se pudo crear la carpeta ' . basename($dir) . '.');
    }
    $tmp = $dir . '/.' . basename($path) . '.' . bin2hex(random_bytes(6)) . '.tmp';
    if (file_put_contents($tmp, $contents, LOCK_EX) === false) {
        throw new RuntimeException('No se pudo escribir el archivo. Revisá los permisos de la carpeta content/.');
    }
    if (!rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException('No se pudo guardar el archivo.');
    }
}

function store_load(): array
{
    $data = json_decode((string) @file_get_contents(CONTENT_FILE), true);
    if (!is_array($data)) {
        throw new RuntimeException('El contenido del sitio está dañado. Restaurá una versión anterior.');
    }
    return $data;
}

/** Guarda el contenido completo. $label describe el cambio para el historial. */
function store_save(array $data, string $label): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('No se pudo preparar el contenido para guardar.');
    }

    $lock = fopen(ROOT . '/content/.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX)) {
        throw new RuntimeException('Otro guardado está en curso. Probá de nuevo en unos segundos.');
    }
    try {
        backup_current($label);
        write_file_atomic(CONTENT_FILE, $json . "\n");
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function backup_current(string $label): void
{
    if (!is_file(CONTENT_FILE)) {
        return;
    }
    if (!is_dir(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
    }
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $label) ?: 'cambio')), '-');
    // Con milisegundos: dos guardados en el mismo segundo tienen que ordenarse bien.
    $name = sprintf('site-%s-%s-%s.json', (new DateTimeImmutable())->format('Ymd-His-v'), substr(bin2hex(random_bytes(3)), 0, 4), $slug ?: 'cambio');
    copy(CONTENT_FILE, BACKUP_DIR . '/' . $name);

    $files = backups_list();
    foreach (array_slice($files, BACKUPS_KEEP) as $old) {
        @unlink(BACKUP_DIR . '/' . $old['file']);
    }
}

/** Backups, del más nuevo al más viejo. */
function backups_list(): array
{
    $files = glob(BACKUP_DIR . '/site-*.json') ?: [];
    rsort($files);
    return array_map(function (string $path): array {
        $file = basename($path);
        preg_match('/^site-(\d{8})-(\d{6})(?:-\d{3})?-[a-f0-9]{4}-(.*)\.json$/', $file, $m);
        $date = $m ? DateTimeImmutable::createFromFormat('Ymd His', $m[1] . ' ' . $m[2]) : false;
        return [
            'file' => $file,
            'date' => $date ? $date->format('d/m/Y H:i:s') : '',
            'label' => $m ? str_replace('-', ' ', $m[3]) : '',
        ];
    }, $files);
}

function backup_restore(string $file): void
{
    if (!preg_match('/^site-[\w-]+\.json$/', $file) || !is_file(BACKUP_DIR . '/' . $file)) {
        throw new RuntimeException('No se encontró esa versión.');
    }
    $data = json_decode((string) file_get_contents(BACKUP_DIR . '/' . $file), true);
    if (!is_array($data)) {
        throw new RuntimeException('Esa versión está dañada y no se puede restaurar.');
    }
    store_save($data, 'antes de restaurar');
}
