<?php
/**
 * Apertura del documento + aviso superior + header.
 * Espera $page = ['title' => ..., 'description' => ..., 'path' => '/'].
 */

declare(strict_types=1);

$status = open_status();
$isHome = ($page['path'] ?? '/') === '/';
$nav = [
    '#servicios' => 'Servicios',
    '#peluqueria' => 'Peluquería',
    '#pet-shop' => 'Pet Shop',
    '#nosotros' => 'Nosotros',
    '#contacto' => 'Contacto',
];
?><!doctype html>
<html lang="es-AR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page['title']) ?></title>
<?= meta_tags($page) ?>
<meta name="theme-color" content="#2C0C3D">
<link rel="icon" href="/assets/img/logo-la-guardia-150.png" type="image/png">
<?php if ($isHome && ($heroImg = (string) c('hero.image')) !== ''): [$heroSrcset] = upload_srcset($heroImg); ?>
<link rel="preload" as="image" href="<?= e($heroImg) ?>"<?= $heroSrcset !== '' ? ' imagesrcset="' . e($heroSrcset) . '" imagesizes="(min-width: 1024px) 36rem, 22rem"' : '' ?> fetchpriority="high">
<?php endif; ?>
<link rel="preload" href="/assets/fonts/bricolage-grotesque-800-condensed-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/plus-jakarta-sans-latin-var.woff2" as="font" type="font/woff2" crossorigin>
<?php /* fonts.css y tokens.css son chicos: van inline para ahorrar dos requests que bloquean el render. */ ?>
<style><?= str_replace('../fonts/', '/assets/fonts/', (string) file_get_contents(ROOT . '/assets/css/fonts.css')) . file_get_contents(ROOT . '/assets/css/tokens.css') ?></style>
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<script>
(function (d) {
  d.classList.add('js');
  // Solo desktop espera a GSAP (el titular entra palabra por palabra). En mobile el
  // hero se muestra de entrada: el titular es el LCP y no tiene que esperar JS.
  if (!matchMedia('(prefers-reduced-motion: reduce)').matches && matchMedia('(min-width: 1024px)').matches) {
    d.classList.add('motion-pending', 'motion-full');
    setTimeout(function () { d.classList.remove('motion-pending'); }, 2500);
  }
})(document.documentElement);
</script>
</head>
<body>
<a class="skip-link" href="#main">Saltar al contenido</a>

<?php if (announcement_active()): ?>
<div class="announcement announcement--<?= e(c('announcement.style', 'magenta')) ?>" role="region" aria-label="Aviso" data-announcement>
  <p><?= cross('cross cross--xs') ?><span><?= t(c('announcement.text')) ?></span></p>
  <button class="announcement__close" type="button" aria-label="Cerrar aviso" data-announcement-close><?= icon('close') ?></button>
</div>
<?php endif; ?>

<header class="site-header" data-header>
  <div class="site-header__inner container">
    <a class="brand" href="/" data-logo>
      <img class="brand__logo" src="/assets/img/logo-la-guardia-104.webp" alt="" width="52" height="52">
      <span class="brand__text"><small>Clínica veterinaria</small><strong>LA GUARDIA</strong></span>
    </a>

    <nav class="site-nav" id="site-nav" aria-label="Principal" data-nav>
      <ul>
        <?php foreach ($nav as $href => $label): ?>
          <li><a href="<?= $isHome ? $href : '/' . $href ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <a class="btn btn--primary site-nav__cta" href="<?= e(wa_url(wa_message('una consulta general'))) ?>" data-track="whatsapp" data-track-label="menu">Escribinos por WhatsApp</a>
    </nav>

    <div class="site-header__actions">
      <span class="status <?= $status['open'] ? 'is-open' : 'is-closed' ?>" data-open-status data-hours='<?= e(json_encode(['week' => c('hours.week', []), 'special' => c('hours.special', [])], JSON_UNESCAPED_UNICODE)) ?>'>
        <span class="status__dot" aria-hidden="true"></span>
        <span class="status__label"><?= e($status['label']) ?></span>
        <span class="status__short" aria-hidden="true"><?= e($status['short']) ?></span>
      </span>
      <a class="btn btn--primary btn--sm site-header__cta" href="<?= e(wa_url(wa_message('una consulta general'))) ?>" data-track="whatsapp" data-track-label="header">Escribinos</a>
      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
        <span class="visually-hidden">Abrir menú</span>
        <?= icon('menu', 'icon nav-toggle__open') ?><?= icon('close', 'icon nav-toggle__close') ?>
      </button>
    </div>
  </div>
</header>

<main id="main">
