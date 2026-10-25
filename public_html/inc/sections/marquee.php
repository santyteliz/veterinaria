<?php
/** Marquee infinito de servicios (CSS puro; se pausa con reduced-motion). */

$items = (array) c('marquee', []);
if (!$items) {
    return;
}
?>
<div class="marquee" aria-label="Servicios: <?= e(implode(', ', $items)) ?>">
  <?php for ($copy = 0; $copy < 2; $copy++): ?>
    <ul class="marquee__track" <?= $copy ? 'aria-hidden="true"' : '' ?>>
      <?php foreach ($items as $item): ?>
        <li><?= e($item) ?></li>
        <li class="marquee__sep"><?= cross('cross cross--sm') ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endfor; ?>
</div>
