<?php
/**
 * ¿Es una urgencia? Bloque de alto contraste. La clínica no publica contactos de
 * guardia (los pasa por WhatsApp); si en el panel se cargan teléfonos, se listan.
 */

$em = c('emergency', []);
$clinics = (array) ($em['clinics'] ?? []);
?>
<section class="section section--white section--tight" id="urgencias" aria-labelledby="urgencias-title">
  <div class="container">
    <div class="emergency<?= $clinics ? '' : ' emergency--compact' ?>">
      <div class="emergency__head">
        <span class="emergency__icon" aria-hidden="true"><?= icon('alert') ?></span>
        <div>
          <h2 id="urgencias-title"><?= e($em['title'] ?? '¿Es una urgencia?') ?></h2>
          <p><?= t($em['text'] ?? '') ?></p>
        </div>
      </div>
      <?php if ($clinics): ?>
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
      <?php else: ?>
        <a class="btn btn--dark btn--lg emergency__cta" href="<?= e(wa_url('¡Hola La Guardia! Tengo una urgencia, ¿me pasan los contactos de guardia?')) ?>" data-track="whatsapp" data-track-label="urgencias">
          <?= icon('whatsapp') ?> Pedir contactos de guardia
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>
