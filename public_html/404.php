<?php
/** Página 404: un perrito recorre la pantalla olfateando, buscando la página perdida. */

declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

http_response_code(404);

$page = [
    'title' => 'Página no encontrada | ' . c('site.name'),
    'description' => 'No encontramos esta página. Volvé al inicio o escribinos por WhatsApp.',
    'path' => '/404',
    'robots' => 'noindex, follow',
];

require __DIR__ . '/inc/layout-start.php';
?>
<section class="page-hero page-hero--404" aria-labelledby="page-title">
  <div class="grain" aria-hidden="true"></div>
  <div class="container page-hero__inner">
    <p class="overline">Error 404</p>
    <h1 id="page-title" class="page-hero__title">Esta página se nos escapó.</h1>
    <p class="page-hero__lead">Nuestro pacientito la está buscando por todos lados. Puede que el link esté mal escrito o que la página ya no exista: probá desde el inicio o escribinos.</p>
    <div class="lost" aria-hidden="true">
      <div class="lost__dog"><?= dog_svg('dog dog--walk dog--sniff') ?></div>
      <span class="lost__q">?</span>
    </div>
    <div class="button-row">
      <a class="btn btn--primary btn--lg" href="/">Volver al inicio</a>
      <a class="btn btn--ghost-light btn--lg" href="<?= e(wa_url(wa_message('una consulta general'))) ?>" data-track="whatsapp" data-track-label="404"><?= icon('whatsapp') ?> Escribinos</a>
    </div>
  </div>
</section>
<?php
require __DIR__ . '/inc/layout-end.php';
