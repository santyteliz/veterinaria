<?php
/**
 * Páginas de servicio para SEO local (/peluqueria-canina-santo-tome, etc.).
 * El .htaccess manda /<slug> acá con ?slug=<slug>. El contenido vive en site.json → pages.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$slug = (string) ($_GET['slug'] ?? '');
$pages = (array) c('pages', []);
if (!preg_match('/^[a-z0-9-]+$/', $slug) || !isset($pages[$slug])) {
    require __DIR__ . '/404.php';
    exit;
}

$p = $pages[$slug];
$service = [];
foreach ((array) c('services', []) as $s) {
    if (($s['id'] ?? '') === ($p['service'] ?? '')) {
        $service = $s;
    }
}
$number = (string) ($service['whatsapp'] ?? '');
$isVisit = ($p['service'] ?? '') === 'domicilio';
$waUrl = wa_url($isVisit ? wa_message_visit() : wa_message((string) ($p['wa_service'] ?? $service['name'] ?? '')), $number);
$path = '/' . $slug;
$crumbs = [['Inicio', '/'], [(string) $p['breadcrumb'], $path]];
$faq = (array) ($p['faq'] ?? []);
$status = open_status();

$page = [
    'title' => $p['title'],
    'description' => $p['description'],
    'path' => $path,
    'schema' => [
        schema_business(),
        schema_breadcrumb($crumbs),
        schema_faq($faq),
    ],
];

require __DIR__ . '/inc/layout-start.php';
?>
<section class="page-hero" aria-labelledby="page-title">
  <div class="grain" aria-hidden="true"></div>
  <div class="container page-hero__inner">
    <nav class="breadcrumbs" aria-label="Estás en">
      <ol>
        <li><a href="/">Inicio</a></li>
        <li aria-current="page"><?= e($p['breadcrumb']) ?></li>
      </ol>
    </nav>
    <h1 id="page-title" class="page-hero__title"><?= e($p['h1']) ?></h1>
    <p class="page-hero__lead"><?= t($p['lead']) ?></p>
    <ul class="chips" role="list">
      <?php foreach ((array) ($p['facts'] ?? []) as $fact): ?>
        <li class="chip chip--light"><?= cross('cross cross--xs') ?><?= e($fact) ?></li>
      <?php endforeach; ?>
    </ul>
    <div class="button-row">
      <a class="btn btn--primary btn--lg btn--glow" href="<?= e($waUrl) ?>" data-magnetic data-track="whatsapp" data-track-label="pagina:<?= e($slug) ?>">
        <?= icon('whatsapp') ?> <?= e($p['cta']) ?>
      </a>
      <?php if ($isVisit): ?>
        <a class="btn btn--ghost-light btn--lg" href="tel:<?= e(c('home_visits.phone')) ?>" data-track="phone" data-track-label="pagina:<?= e($slug) ?>"><?= icon('phone') ?> <?= e(c('home_visits.phone_display')) ?></a>
      <?php else: ?>
        <a class="btn btn--ghost-light btn--lg" href="<?= e(maps_directions_url()) ?>" target="_blank" rel="noopener" data-track="directions" data-track-label="pagina:<?= e($slug) ?>"><?= icon('pin') ?> Cómo llegar</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section section--light" aria-label="Información">
  <div class="container page-body">
    <article class="prose">
      <?php foreach ((array) ($p['sections'] ?? []) as $sec): ?>
        <h2><?= e($sec['title']) ?></h2>
        <?php foreach ((array) ($sec['paragraphs'] ?? []) as $para): ?>
          <p><?= t($para) ?></p>
        <?php endforeach; ?>
        <?php if (!empty($sec['list'])): ?>
          <ul class="prose__list">
            <?php foreach ((array) $sec['list'] as $item): ?>
              <li><?= cross('cross cross--sm') ?><span><?= t($item) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      <?php endforeach; ?>
    </article>

    <aside class="page-aside" aria-label="Datos de contacto">
      <?php if ($isVisit): ?>
        <h2 class="page-aside__title">Atención a domicilio</h2>
        <p class="page-aside__big"><?= e(c('home_visits.vet')) ?></p>
        <p>Coordiná directo con él por WhatsApp o por teléfono.</p>
      <?php else: ?>
        <h2 class="page-aside__title">Horarios</h2>
        <p class="status-line <?= $status['open'] ? 'is-open' : 'is-closed' ?>"><span class="status__dot" aria-hidden="true"></span><?= e($status['label']) ?></p>
        <ul class="page-aside__hours" role="list">
          <?php foreach (hours_summary() as $row): ?>
            <li><span><?= e($row['days']) ?></span><strong><?= e($row['hours']) ?></strong></li>
          <?php endforeach; ?>
        </ul>
        <p class="page-aside__note"><?= e(c('hours.note')) ?><?= ($p['service'] ?? '') === 'peluqueria' ? ' (peluquería con turno previo)' : '' ?></p>
        <h2 class="page-aside__title">Dónde estamos</h2>
        <address><?= e(c('contact.address')) ?>, <?= e(c('contact.city')) ?>, <?= e(c('contact.region')) ?></address>
      <?php endif; ?>
      <a class="btn btn--primary btn--block" href="<?= e($waUrl) ?>" data-track="whatsapp" data-track-label="aside:<?= e($slug) ?>"><?= icon('whatsapp') ?> <?= e($p['cta']) ?></a>
    </aside>
  </div>
</section>

<?php if ($faq): ?>
<section class="section section--white" aria-labelledby="faq-title">
  <div class="container split split--faq">
    <div class="split__copy">
      <p class="overline">Preguntas frecuentes</p>
      <h2 id="faq-title"><?= e($p['breadcrumb']) ?>: lo que más nos preguntan.</h2>
    </div>
    <div class="faq">
      <?php foreach ($faq as $item): ?>
        <details class="faq__item">
          <summary><?= e($item['q']) ?><span class="faq__icon" aria-hidden="true"><?= cross('cross') ?></span></summary>
          <div class="faq__answer"><p><?= t($item['a']) ?></p></div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/inc/sections/emergency.php'; ?>

<section class="section section--dark" aria-labelledby="related-title">
  <div class="grain" aria-hidden="true"></div>
  <div class="container">
    <div class="section-head">
      <p class="overline">Más servicios</p>
      <h2 id="related-title">También te podemos ayudar con…</h2>
    </div>
    <ul class="related" role="list">
      <?php foreach ($pages as $otherSlug => $other): if ($otherSlug === $slug) continue; ?>
        <li><a class="related__link" href="/<?= e($otherSlug) ?>"><span><?= e($other['h1']) ?></span><?= icon('arrow') ?></a></li>
      <?php endforeach; ?>
      <li><a class="related__link" href="/#servicios"><span>Todos los servicios de la clínica</span><?= icon('arrow') ?></a></li>
    </ul>
  </div>
</section>
<?php
require __DIR__ . '/inc/layout-end.php';
