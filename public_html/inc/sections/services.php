<?php
/** Servicios: fondo magenta (recibe la "inundación" de la cruz del hero). */

$services = array_values(array_filter((array) c('services', []), fn ($s) => !empty($s['visible'])));
?>
<section class="section section--accent" id="servicios" aria-labelledby="servicios-title">
  <div class="container">
    <div class="section-head">
      <p class="overline">Servicios</p>
      <h2 id="servicios-title">Todo para tu pacientito, en un solo lugar.</h2>
      <p class="lead">Tocá “Consultar” y te abrimos WhatsApp con el mensaje listo.</p>
    </div>

    <ul class="cards" role="list">
      <?php foreach ($services as $service):
          $number = (string) ($service['whatsapp'] ?? '');
          $message = ($service['id'] ?? '') === 'domicilio' ? wa_message_visit() : wa_message((string) $service['name']);
      ?>
        <li class="card service" data-tilt>
          <svg class="service__cross" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><path pathLength="1" d="M48 4h24a6 6 0 0 1 6 6v32h32a6 6 0 0 1 6 6v24a6 6 0 0 1-6 6H78v32a6 6 0 0 1-6 6H48a6 6 0 0 1-6-6V78H10a6 6 0 0 1-6-6V48a6 6 0 0 1 6-6h32V10a6 6 0 0 1 6-6Z"/></svg>
          <span class="service__icon"><?= icon((string) ($service['icon'] ?? 'stethoscope')) ?></span>
          <h3 class="service__title"><?= e($service['name']) ?></h3>
          <p class="service__text"><?= t($service['description'] ?? '') ?></p>
          <div class="service__actions">
            <a class="btn btn--primary" href="<?= e(wa_url($message, $number)) ?>" data-track="whatsapp" data-track-label="servicio:<?= e($service['id']) ?>">
              <?= e($service['cta'] ?? 'Consultar') ?> <?= icon('arrow') ?>
            </a>
            <button class="link-btn" type="button" data-wa-open data-service="<?= e($service['name']) ?>" data-number="<?= e($number) ?>">Armar mensaje</button>
            <?php if (!empty($service['page'])): ?>
              <a class="link-arrow service__more" href="<?= e($service['page']) ?>">Más info<span class="visually-hidden"> sobre <?= e($service['name']) ?></span> <?= icon('arrow') ?></a>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
