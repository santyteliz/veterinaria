<?php
/** Reseñas reales de Google cargadas desde el panel. Sin reseñas, la sección no existe. */

$reviews = c('reviews', []);
$items = (array) ($reviews['items'] ?? []);
if (!$items) {
    return;
}
?>
<section class="section section--dark" id="resenas" aria-labelledby="resenas-title">
  <div class="grain" aria-hidden="true"></div>
  <div class="container">
    <div class="section-head section-head--row">
      <div>
        <p class="overline">Reseñas</p>
        <h2 id="resenas-title">Lo que dicen en Google.</h2>
      </div>
      <div class="rating">
        <strong class="rating__value"<?= !empty($reviews['verified']) ? ' data-count-to="' . e(number_format((float) ($reviews['rating'] ?? 0), 1, '.', '')) . '"' : '' ?>><?= e(number_format((float) ($reviews['rating'] ?? 0), 1, ',', '')) ?></strong>
        <span class="rating__stars" aria-hidden="true"><?= str_repeat(icon('star'), 5) ?></span>
        <?php if (!empty($reviews['google_url'])): ?>
          <a class="link-arrow link-arrow--light" href="<?= e($reviews['google_url']) ?>" target="_blank" rel="noopener">Ver todas en Google <?= icon('external') ?></a>
        <?php endif; ?>
      </div>
    </div>

    <ul class="reviews" role="list">
      <?php foreach ($items as $r): $stars = max(1, min(5, (int) ($r['stars'] ?? 5))); ?>
        <li class="review" data-reveal>
          <p class="review__stars" aria-label="<?= $stars ?> de 5 estrellas"><?= str_repeat(icon('star'), $stars) ?></p>
          <blockquote><p><?= e($r['text'] ?? '') ?></p></blockquote>
          <p class="review__author"><?= e($r['name'] ?? '') ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
