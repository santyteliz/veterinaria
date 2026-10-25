<?php
/** ¿Es una urgencia? Bloque de alto contraste con las clínicas de guardia. */

$em = c('emergency', []);
$clinics = (array) ($em['clinics'] ?? []);
?>
<section class="section section--white section--tight" id="urgencias" aria-labelledby="urgencias-title">
  <div class="container">
    <div class="emergency">
      <div class="emergency__head">
        <span class="emergency__icon" aria-hidden="true"><?= icon('alert') ?></span>
        <div>
          <h2 id="urgencias-title"><?= e($em['title'] ?? '¿Es una urgencia?') ?></h2>
          <p><?= t($em['text'] ?? '') ?></p>
        </div>
      </div>
      <ul class="emergency__list" role="list">
        <?php foreach ($clinics as $clinic): ?>
          <li>
            <a class="emergency__item" href="tel:<?= e($clinic['phone']) ?>" data-track="phone" data-track-label="guardia">
              <span class="emergency__name"><?= e(($clinic['name'] ?? '') !== '' ? $clinic['name'] : 'Clínica de guardia') ?></span>
              <span class="emergency__phone"><?= e($clinic['phone_display'] ?? $clinic['phone']) ?></span>
              <span class="emergency__call"><?= icon('phone') ?> Llamar</span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
