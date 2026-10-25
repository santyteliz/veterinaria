<?php
/** Campos de formulario y limpieza de datos que llegan por POST. */

declare(strict_types=1);

/* ---- Limpieza ----------------------------------------------------------- */

function clean_str($value, int $max = 300): string
{
    $s = is_scalar($value) ? (string) $value : '';
    $s = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s);
    $s = str_replace("\r\n", "\n", trim($s));
    return mb_substr($s, 0, $max);
}

function clean_line($value, int $max = 160): string
{
    return trim((string) preg_replace('/\s+/u', ' ', clean_str($value, $max)));
}

function clean_time($value): string
{
    $v = clean_line($value, 5);
    return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v) ? $v : '';
}

function clean_date($value): string
{
    $v = clean_line($value, 10);
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $v);
    return $d && $d->format('Y-m-d') === $v ? $v : '';
}

function clean_url($value): string
{
    $v = clean_line($value, 500);
    if ($v === '') {
        return '';
    }
    if (!preg_match('#^https?://#i', $v)) {
        $v = 'https://' . $v;
    }
    return filter_var($v, FILTER_VALIDATE_URL) ? $v : '';
}

function clean_email($value): string
{
    $v = clean_line($value, 160);
    return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : '';
}

function digits(string $s): string
{
    return (string) preg_replace('/\D+/', '', $s);
}

/**
 * Acepta "342 486-0753", "3424860753", "+54 9 342 486 0753"…
 * y devuelve el formato internacional de WhatsApp: 5493424860753.
 */
function normalize_whatsapp($value): string
{
    $d = digits(clean_line($value, 40));
    $d = ltrim($d, '0');
    if (str_starts_with($d, '549')) {
        return $d;
    }
    if (str_starts_with($d, '54')) {
        return '549' . substr($d, 2);
    }
    if (strlen($d) === 10) {
        return '549' . $d;
    }
    return $d;
}

/** "342 486-0753" → "+543424860753" para links tel:. */
function tel_from_display(string $display): string
{
    $d = ltrim(digits($display), '0');
    if ($d === '') {
        return '';
    }
    return str_starts_with($d, '54') ? '+' . $d : '+54' . $d;
}

function posted(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

/** Filas de un repetidor, en el orden en que quedaron en pantalla. */
function posted_rows(string $key): array
{
    $rows = $_POST[$key] ?? [];
    return is_array($rows) ? array_values(array_filter($rows, 'is_array')) : [];
}

/** Igual que posted_rows pero conserva la clave (para cruzar con $_FILES). */
function posted_rows_keyed(string $key): array
{
    $rows = $_POST[$key] ?? [];
    return is_array($rows) ? array_filter($rows, 'is_array') : [];
}

/* ---- Campos ------------------------------------------------------------- */

function field_id(string $name): string
{
    return 'f-' . trim((string) preg_replace('/[^a-z0-9]+/i', '-', $name), '-');
}

function field_text(string $name, $value, string $label, array $o = []): string
{
    $id = $o['id'] ?? field_id($name);
    $attrs = '';
    foreach (['type' => 'text', 'maxlength' => null, 'placeholder' => null, 'inputmode' => null, 'autocomplete' => 'off', 'pattern' => null] as $k => $def) {
        $v = $o[$k] ?? $def;
        if ($v !== null) {
            $attrs .= ' ' . $k . '="' . e($v) . '"';
        }
    }
    if (!empty($o['required'])) {
        $attrs .= ' required';
    }
    if (isset($o['maxlength'])) {
        $attrs .= ' data-count';
    }
    return '<div class="field">'
        . '<label for="' . e($id) . '">' . e($label) . '</label>'
        . '<input id="' . e($id) . '" name="' . e($name) . '" value="' . e($value) . '"' . $attrs . '>'
        . (!empty($o['hint']) ? '<p class="hint">' . e($o['hint']) . '</p>' : '')
        . '</div>';
}

function field_textarea(string $name, $value, string $label, array $o = []): string
{
    $id = $o['id'] ?? field_id($name);
    $max = $o['maxlength'] ?? null;
    return '<div class="field">'
        . '<label for="' . e($id) . '">' . e($label) . '</label>'
        . '<textarea id="' . e($id) . '" name="' . e($name) . '" rows="' . (int) ($o['rows'] ?? 3) . '"'
        . ($max ? ' maxlength="' . (int) $max . '" data-count' : '') . '>' . e($value) . '</textarea>'
        . (!empty($o['hint']) ? '<p class="hint">' . e($o['hint']) . '</p>' : '')
        . '</div>';
}

function field_check(string $name, bool $checked, string $label, array $o = []): string
{
    $id = $o['id'] ?? field_id($name);
    return '<div class="field field--check">'
        . '<input type="checkbox" id="' . e($id) . '" name="' . e($name) . '" value="1"' . ($checked ? ' checked' : '') . '>'
        . '<label for="' . e($id) . '">' . e($label) . '</label>'
        . (!empty($o['hint']) ? '<p class="hint">' . e($o['hint']) . '</p>' : '')
        . '</div>';
}

function field_select(string $name, $value, string $label, array $options, array $o = []): string
{
    $id = $o['id'] ?? field_id($name);
    $html = '<div class="field"><label for="' . e($id) . '">' . e($label) . '</label><select id="' . e($id) . '" name="' . e($name) . '">';
    foreach ($options as $v => $text) {
        $html .= '<option value="' . e($v) . '"' . ((string) $v === (string) $value ? ' selected' : '') . '>' . e($text) . '</option>';
    }
    return $html . '</select>' . (!empty($o['hint']) ? '<p class="hint">' . e($o['hint']) . '</p>' : '') . '</div>';
}

/** Campo de foto: vista previa, cambiar y quitar. */
function field_photo(string $fileName, string $keepName, string $removeName, string $current, string $label): string
{
    $id = field_id($fileName);
    $preview = $current !== ''
        ? '<img src="' . e($current) . '" alt="" data-preview>'
        : '<span class="photo__empty" data-preview-empty>Sin foto</span><img alt="" data-preview hidden>';
    return '<div class="field photo">'
        . '<span class="label">' . e($label) . '</span>'
        . '<div class="photo__box">' . $preview . '</div>'
        . '<input type="hidden" name="' . e($keepName) . '" value="' . e($current) . '">'
        . '<label class="btn btn--ghost btn--sm photo__pick" for="' . e($id) . '">' . ($current !== '' ? 'Cambiar foto' : 'Elegir foto') . '</label>'
        . '<input class="visually-hidden" type="file" id="' . e($id) . '" name="' . e($fileName) . '" accept="image/jpeg,image/png,image/webp" data-photo-input>'
        . ($current !== '' ? field_check($removeName, false, 'Quitar foto') : '')
        . '</div>';
}

/** Botones de una fila de repetidor (subir, bajar, borrar). */
function row_tools(string $what): string
{
    return '<div class="row__tools">'
        . '<button type="button" class="icon-btn" data-row-up aria-label="Subir ' . e($what) . '">↑</button>'
        . '<button type="button" class="icon-btn" data-row-down aria-label="Bajar ' . e($what) . '">↓</button>'
        . '<button type="button" class="icon-btn icon-btn--danger" data-row-remove aria-label="Borrar ' . e($what) . '">✕</button>'
        . '</div>';
}
