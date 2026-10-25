<?php
/** Nuestros pacientitos: galería masonry. Sin fotos cargadas muestra placeholders. */

$patients = (array) c('patients', []);
$isPlaceholder = !$patients;
if ($isPlaceholder) {
    $patients = array_fill(0, 6, ['image' => '', 'name' => '', 'alt' => 'Foto de un pacientito']);
}
?>
<section class="section section--dark" id="pacientitos" aria-labelledby="pacientitos-title">
  <div class="grain" aria-hidden="true"></div>
  <div class="container">
    <div class="section-head section-head--row">
      <div>
        <p class="overline">Nuestros pacientitos</p>
        <h2 id="pacientitos-title">Los que ya pasaron por acá.</h2>
      </div>
      <a class="btn btn--ghost-light" href="<?= e(c('contact.instagram')) ?>" target="_blank" rel="noopener"><?= icon('instagram') ?> Ver más en Instagram</a>
    </div>

    <ul class="gallery<?= $isPlaceholder ? ' gallery--empty' : '' ?>" role="list">
      <?php foreach ($patients as $i => $p): ?>
        <li class="gallery__item gallery__item--<?= $i % 3 + 1 ?>" data-reveal>
          <figure>
            <?= img_or_placeholder((string) ($p['image'] ?? ''), (string) ($p['alt'] ?? ($p['name'] ?? 'Pacientito')), 'foto de pacientito', 'gallery__img', true, '(min-width: 900px) 33vw, 50vw') ?>
            <?php if (!empty($p['name'])): ?><figcaption><?= e($p['name']) ?></figcaption><?php endif; ?>
          </figure>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
