<?php
/** Estructura HTML del panel. */

declare(strict_types=1);

const ADMIN_SECTIONS = [
    'aviso' => ['Aviso superior', 'Activar o cambiar el cartel de arriba'],
    'horarios' => ['Horarios', 'Días, turnos y feriados'],
    'contacto' => ['Contacto', 'WhatsApp, teléfono, dirección, redes'],
    'servicios' => ['Servicios', 'Textos, orden y cuáles se muestran'],
    'urgencias' => ['Clínicas de guardia', 'Teléfonos para urgencias'],
    'pacientitos' => ['Pacientitos', 'Galería de fotos'],
    'equipo' => ['Equipo', 'Fotos y textos de cada persona'],
    'resenas' => ['Reseñas', 'Reseñas reales de Google'],
    'versiones' => ['Versiones', 'Volver a un guardado anterior'],
    'cuenta' => ['Cuenta', 'Cambiar la contraseña'],
];

function admin_asset(string $file): string
{
    $mtime = @filemtime(ADMIN_DIR . '/assets/' . $file);
    return '/admin/assets/' . $file . '?v=' . ($mtime ?: '1');
}

function admin_header(string $title, string $active = '', bool $chrome = true): void
{
    ?><!doctype html>
<html lang="es-AR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#2C0C3D">
<title><?= e($title) ?> · Panel La Guardia</title>
<link rel="icon" href="/assets/img/favicon-48.png" type="image/png">
<link rel="stylesheet" href="<?= asset('css/tokens.css') ?>">
<link rel="stylesheet" href="<?= admin_asset('admin.css') ?>">
<script src="<?= admin_asset('admin.js') ?>" defer></script>
</head>
<body class="<?= $chrome ? 'has-chrome' : 'is-bare' ?>">
<?php if ($chrome): ?>
<header class="topbar">
  <a class="topbar__brand" href="/admin/">
    <img src="/assets/img/logo-cruz-128.webp" alt="" width="36" height="36">
    <span>Panel <strong>La Guardia</strong></span>
  </a>
  <a class="btn btn--light btn--sm" href="/" target="_blank" rel="noopener">Ver sitio ↗</a>
</header>
<nav class="sections-nav" aria-label="Secciones del panel">
  <ul>
    <li><a href="/admin/"<?= $active === '' ? ' aria-current="page"' : '' ?>>Inicio</a></li>
    <?php foreach (ADMIN_SECTIONS as $key => [$label]): ?>
      <li><a href="/admin/?s=<?= e($key) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
    <?php endforeach; ?>
  </ul>
</nav>
<?php endif; ?>
<main class="main" id="main">
<?php foreach (flash_take() as $f): ?>
  <div class="flash flash--<?= e($f['type']) ?>" role="<?= $f['type'] === 'error' ? 'alert' : 'status' ?>"><?= e($f['message']) ?></div>
<?php endforeach;
}

function admin_footer(bool $chrome = true): void
{
    ?>
</main>
<?php if ($chrome): ?>
<footer class="admin-footer">
  <form method="post" action="/admin/logout.php">
    <?= csrf_field() ?>
    <button class="link-btn" type="submit">Cerrar sesión</button>
  </form>
</footer>
<?php endif; ?>
</body>
</html>
<?php
}

/** Formulario de sección con barra de guardado fija abajo. */
function form_open(string $section, bool $files = false): string
{
    return '<form class="form" method="post" action="/admin/?s=' . e($section) . '"' . ($files ? ' enctype="multipart/form-data"' : '') . ' data-dirty-check>'
        . csrf_field();
}

function form_close(string $button = 'Guardar cambios'): string
{
    return '<div class="savebar"><button class="btn btn--primary btn--block" type="submit">' . e($button) . '</button></div></form>';
}
