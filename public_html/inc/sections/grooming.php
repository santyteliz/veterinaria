<?php
/** Peluquería canina: antes/después arrastrable, turno previo, medios de pago. */

$g = c('grooming', []);
$pairs = (array) ($g['before_after'] ?? []);
if (!$pairs) {
    $pairs = [['before' => '', 'after' => '', 'alt' => 'Perro antes y después del baño y corte']];
}
?>
<section class="section section--dark" id="peluqueria" aria-labelledby="peluqueria-title">
  <div class="grain" aria-hidden="true"></div>
  <div class="container split">
    <div class="split__copy">
      <p class="overline">Peluquería canina</p>
      <h2 id="peluqueria-title"><?= e($g['title'] ?? 'Peluquería canina') ?></h2>
      <p class="lead"><?= t($g['text'] ?? '') ?></p>
      <ul class="chips" role="list">
        <?php foreach ((array) ($g['facts'] ?? []) as $fact): ?>
          <li class="chip chip--light"><?= cross('cross cross--xs') ?><?= e($fact) ?></li>
        <?php endforeach; ?>
      </ul>
      <a class="btn btn--primary btn--lg" href="<?= e(wa_url(wa_message('un turno de peluquería canina'))) ?>" data-track="whatsapp" data-track-label="peluqueria">
        Pedir turno de peluquería <?= icon('arrow') ?>
      </a>
    </div>

    <div class="ba-list<?= count($pairs) > 1 ? ' ba-list--multi' : '' ?>">
      <?php foreach ($pairs as $i => $pair): ?>
        <figure class="ba" data-ba>
          <div class="ba__frame">
            <div class="ba__layer ba__layer--after">
              <?= img_or_placeholder((string) ($pair['after'] ?? ''), (string) ($pair['alt'] ?? '') . ' (después)', 'foto después', 'ba__img', true, '(min-width: 1024px) 50vw, 100vw') ?>
            </div>
            <div class="ba__layer ba__layer--before" data-ba-before>
              <?= img_or_placeholder((string) ($pair['before'] ?? ''), (string) ($pair['alt'] ?? '') . ' (antes)', 'foto antes', 'ba__img', true, '(min-width: 1024px) 50vw, 100vw') ?>
            </div>
            <span class="ba__tag ba__tag--before" aria-hidden="true">Antes</span>
            <span class="ba__tag ba__tag--after" aria-hidden="true">Después</span>
            <span class="ba__handle" aria-hidden="true"><?= icon('drag') ?></span>
            <input class="ba__range" type="range" min="0" max="100" value="50" step="1" aria-label="Deslizá para comparar antes y después" data-ba-range>
          </div>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
