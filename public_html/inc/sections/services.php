<?php
/** Servicios: fondo magenta (recibe la "inundación" de la cruz del hero), agrupados. */

$services = array_values(array_filter((array) c('services', []), fn ($s) => !empty($s['visible'])));
$groupNames = (array) c('service_groups', []);
$groups = [];
foreach ($services as $service) {
    $groups[(string) ($service['group'] ?? 'clinica')][] = $service;
}
?>
<section class="section section--accent" id="servicios" aria-labelledby="servicios-title">
  <div class="container">
    <div class="section-head">
      <p class="overline">Servicios</p>
      <h2 id="servicios-title">Todo para tu pacientito, en un solo lugar.</h2>
      <p class="lead">Tocá “Consultar” y te abrimos WhatsApp con el mensaje listo.</p>
    </div>

    <?php foreach ($groups as $key => $items): ?>
      <div class="service-group">
        <?php if (count($groups) > 1 && !empty($groupNames[$key])): ?>
          <h3 class="service-group__title"><?= cross('cross') ?><?= e($groupNames[$key]) ?></h3>
        <?php endif; ?>
        <ul class="cards" role="list">
          <?php foreach ($items as $service):
              $number = (string) ($service['whatsapp'] ?? '');
              $message = wa_message((string) $service['name']);
          ?>
            <li class="card service" data-tilt>
              <svg class="service__cross" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><path pathLength="1" d="M48 4h24a6 6 0 0 1 6 6v32h32a6 6 0 0 1 6 6v24a6 6 0 0 1-6 6H78v32a6 6 0 0 1-6 6H48a6 6 0 0 1-6-6V78H10a6 6 0 0 1-6-6V48a6 6 0 0 1 6-6h32V10a6 6 0 0 1 6-6Z"/></svg>
              <span class="service__icon"><?= icon((string) ($service['icon'] ?? 'stethoscope')) ?></span>
              <h4 class="service__title"><?= e($service['name']) ?></h4>
              <p class="service__text"><?= t($service['description'] ?? '') ?></p>
              <div class="service__actions">
                <a class="btn btn--primary btn--sm" href="<?= e(wa_url($message, $number)) ?>" data-track="whatsapp" data-track-label="servicio:<?= e($service['id']) ?>">
                  <?= e($service['cta'] ?? 'Consultar') ?> <?= icon('arrow') ?>
                </a>
                <?php if (!empty($service['page'])): ?>
                  <a class="link-arrow service__more" href="<?= e($service['page']) ?>">Más info<span class="visually-hidden"> sobre <?= e($service['name']) ?></span></a>
                <?php else: ?>
                  <button class="link-btn" type="button" data-wa-open data-service="<?= e($service['name']) ?>" data-number="<?= e($number) ?>">Armar mensaje</button>
                <?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>
</section>
