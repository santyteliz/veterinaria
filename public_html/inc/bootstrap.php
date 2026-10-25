<?php
/**
 * Bootstrap del sitio público: carga el contenido de /content/site.json
 * y expone helpers para las plantillas.
 */

declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('CONTENT_FILE', ROOT . '/content/site.json');
define('ASSET_VERSION', '1');

date_default_timezone_set('America/Argentina/Cordoba');

require __DIR__ . '/icons.php';
require __DIR__ . '/seo.php';
require __DIR__ . '/dog.php';

function content(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $raw = @file_get_contents(CONTENT_FILE);
    $data = $raw !== false ? json_decode($raw, true) : null;
    if (!is_array($data)) {
        http_response_code(500);
        exit('No se pudo leer el contenido del sitio.');
    }
    return $data;
}

/** Valor anidado con notación de puntos: c('contact.whatsapp'). */
function c(string $path, $default = '')
{
    $node = content();
    foreach (explode('.', $path) as $key) {
        if (!is_array($node) || !array_key_exists($key, $node)) {
            return $default;
        }
        $node = $node[$key];
    }
    return $node;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Escapa texto y resalta los marcadores "[A CONFIRMAR: ...]" para que
 * se vean en la web hasta que el cliente cargue el dato real.
 */
function t($value): string
{
    $html = e($value);
    return preg_replace('/\[A CONFIRMAR[^\]]*\]/u', '<mark class="tbc">$0</mark>', $html) ?? $html;
}

function is_placeholder($value): bool
{
    return is_string($value) && str_starts_with(trim($value), '[A CONFIRMAR');
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    $mtime = @filemtime(ROOT . '/assets/' . $path);
    return '/assets/' . $path . '?v=' . ($mtime ?: ASSET_VERSION);
}

/* ---- WhatsApp --------------------------------------------------------- */

function wa_message(string $service): string
{
    return str_replace('{servicio}', $service, (string) c('whatsapp_template'));
}

/** Mensaje para el veterinario a domicilio (es otro número: no se saluda a "La Guardia"). */
function wa_message_visit(): string
{
    return '¡Hola! Te escribo desde la web de La Guardia. Quiero consultar por atención a domicilio. Mascota: ____ · Motivo: ____ · Barrio: ____';
}

function wa_url(string $message = '', string $number = ''): string
{
    $number = preg_replace('/\D+/', '', $number !== '' ? $number : (string) c('contact.whatsapp'));
    $url = 'https://wa.me/' . $number;
    return $message !== '' ? $url . '?text=' . rawurlencode($message) : $url;
}

function maps_directions_url(): string
{
    $custom = (string) c('contact.maps_url');
    if ($custom !== '') {
        return $custom;
    }
    return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode((string) c('contact.maps_query'));
}

function maps_embed_url(): string
{
    return 'https://www.google.com/maps?q=' . rawurlencode((string) c('contact.maps_query')) . '&output=embed';
}

/* ---- Horarios --------------------------------------------------------- */

const DAY_NAMES = [1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sábado', 7 => 'domingo'];

function fmt_hour(string $hhmm): string
{
    [$h, $m] = array_map('intval', explode(':', $hhmm));
    return $m === 0 ? $h . ' h' : sprintf('%d:%02d h', $h, $m);
}

function fmt_range(array $range): string
{
    [$from, $to] = $range;
    $fromLabel = rtrim(fmt_hour($from), ' h');
    return $fromLabel . ' a ' . fmt_hour($to);
}

/** Tramos del día: los "días especiales" del panel pisan la semana tipo. */
function ranges_for(DateTimeImmutable $date): array
{
    foreach ((array) c('hours.special', []) as $special) {
        if (($special['date'] ?? '') === $date->format('Y-m-d')) {
            return (array) ($special['ranges'] ?? []);
        }
    }
    return (array) c('hours.week.' . $date->format('N'), []);
}

function now_local(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone((string) c('site.timezone', 'America/Argentina/Cordoba')));
}

/**
 * Estado "Abierto ahora / Cerrado · abre a las 17 h". Se calcula acá para
 * que el HTML llegue completo, y assets/js/app.js lo recalcula en el
 * navegador por si la página quedó en caché.
 */
function open_status(?DateTimeImmutable $now = null): array
{
    $now = $now ?? now_local();
    $time = $now->format('H:i');

    foreach (ranges_for($now) as [$from, $to]) {
        if ($time >= $from && $time < $to) {
            return ['open' => true, 'label' => 'Abierto ahora · hasta las ' . fmt_hour($to), 'short' => 'Abierto'];
        }
    }

    for ($i = 0; $i <= 7; $i++) {
        $day = $now->modify("+{$i} day");
        foreach (ranges_for($day) as [$from]) {
            if ($i === 0 && $from <= $time) {
                continue;
            }
            $when = match ($i) {
                0 => 'abre a las ',
                1 => 'abre mañana a las ',
                default => 'abre el ' . DAY_NAMES[(int) $day->format('N')] . ' a las ',
            };
            return ['open' => false, 'label' => 'Cerrado · ' . $when . fmt_hour($from), 'short' => 'Cerrado'];
        }
    }

    return ['open' => false, 'label' => 'Cerrado', 'short' => 'Cerrado'];
}

/** Agrupa días consecutivos con el mismo horario: "Lunes a viernes". */
function hours_summary(): array
{
    $week = (array) c('hours.week', []);
    $rows = [];
    $start = null;
    $prevKey = null;
    for ($d = 1; $d <= 8; $d++) {
        $ranges = $d <= 7 ? (array) ($week[(string) $d] ?? []) : null;
        $key = $ranges === null ? null : json_encode($ranges);
        if ($key !== $prevKey) {
            if ($start !== null) {
                $end = $d - 1;
                $label = DAY_NAMES[$start] . ($end > $start ? ($end - $start === 1 ? ' y ' : ' a ') . DAY_NAMES[$end] : '');
                $prevRanges = json_decode((string) $prevKey, true);
                $rows[] = [
                    'days' => ucfirst($label),
                    'hours' => $prevRanges ? implode(' y ', array_map('fmt_range', $prevRanges)) : 'Cerrado',
                    'closed' => !$prevRanges,
                ];
            }
            $start = $d;
            $prevKey = $key;
        }
    }
    return $rows;
}

/* ---- Aviso superior --------------------------------------------------- */

function announcement_active(): bool
{
    if (!c('announcement.enabled', false) || trim((string) c('announcement.text')) === '') {
        return false;
    }
    $expires = (string) c('announcement.expires');
    return $expires === '' || now_local()->format('Y-m-d') <= $expires;
}

/* ---- Imágenes --------------------------------------------------------- */

/**
 * srcset de una foto subida: el panel guarda base.webp (≤1920 px) y variantes
 * base-480.webp, base-960.webp… Devuelve [srcset, ancho, alto] o vacío.
 */
function upload_srcset(string $src): array
{
    if (!str_starts_with($src, '/uploads/') || !preg_match('#^(/uploads/[\d/]+/[a-f0-9]{20})\.(webp|jpg)$#', $src, $m)) {
        return ['', 0, 0];
    }
    $file = ROOT . $src;
    $info = @getimagesize($file);
    if (!$info) {
        return ['', 0, 0];
    }
    $candidates = [];
    foreach (glob(ROOT . $m[1] . '-*.' . $m[2]) ?: [] as $variant) {
        if (preg_match('/-(\d{3,4})\.' . $m[2] . '$/', $variant, $w)) {
            $candidates[(int) $w[1]] = $m[1] . '-' . $w[1] . '.' . $m[2];
        }
    }
    $candidates[(int) $info[0]] = $src;
    ksort($candidates);
    $srcset = implode(', ', array_map(fn ($w, $url) => $url . ' ' . $w . 'w', array_keys($candidates), $candidates));
    return [$srcset, (int) $info[0], (int) $info[1]];
}

/**
 * Imagen subida desde el panel (con srcset, sizes y dimensiones para que no
 * haya saltos de layout), o un placeholder visible con su descripción.
 * $lazy = false para la imagen del hero (LCP): carga con prioridad alta.
 */
function img_or_placeholder(string $src, string $alt, string $label, string $class = '', bool $lazy = true, string $sizes = '100vw'): string
{
    if ($src !== '') {
        [$srcset, $w, $h] = upload_srcset($src);
        return '<img class="' . e($class) . '" src="' . e($src) . '" alt="' . e($alt) . '"'
            . ($srcset !== '' ? ' srcset="' . e($srcset) . '" sizes="' . e($sizes) . '"' : '')
            . ($w ? ' width="' . $w . '" height="' . $h . '"' : '')
            . ($lazy ? ' loading="lazy"' : ' fetchpriority="high"')
            . ' decoding="async">';
    }
    return sprintf(
        '<div class="ph %s" role="img" aria-label="%s"><span>[A CONFIRMAR: %s]</span></div>',
        e($class),
        e($alt),
        e($label)
    );
}
