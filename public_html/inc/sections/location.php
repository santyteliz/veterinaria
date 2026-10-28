<?php
/** Horarios y ubicación, con mapa en facade (carga el iframe recién al hacer click). */

$todayN = (int) now_local()->format('N');
$week = (array) c('hours.week', []);

// Filas agrupadas ("Lunes a viernes", "Sábado y domingo") + feriados; marca la de hoy.
$rows = [];
$day = 1;
foreach (hours_summary() as $row) {
    $span = 1;
    for ($d = $day + 1; $d <= 7 && json_encode($week[(string) $d] ?? []) === json_encode($week[(string) $day] ?? []); $d++) {
        $span++;
    }
    $row['today'] = $todayN >= $day && $todayN < $day + $span;
    $rows[] = $row;
    $day += $span;
}
$rows[] = ['days' => 'Feriados', 'hours' => 'Cerrado', 'closed' => true, 'today' => false];
?>
<section class="section section--dark" id="contacto" aria-labelledby="contacto-title">
  <div class="grain" aria-hidden="true"></div>
  <div class="container split split--location">
    <div class="split__copy">
      <p class="overline">Horarios y ubicación</p>
      <h2 id="contacto-title">Vení cuando quieras, dentro del horario.</h2>
      <p class="lead"><?= e(c('hours.note')) ?>, sin turno.</p>

      <table class="hours">
        <caption class="visually-hidden">Horarios de atención</caption>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr class="<?= $row['today'] ? 'is-today' : '' ?><?= $row['closed'] ? ' is-closed' : '' ?>">
              <th scope="row"><?= e($row['days']) ?><?= $row['today'] ? ' <span class="hours__today">hoy</span>' : '' ?></th>
              <td><?= e($row['hours']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <ul class="contact-list" role="list">
        <li><?= icon('pin') ?><span><strong><?= e(c('contact.address')) ?></strong>, <?= e(c('contact.city')) ?>, <?= e(c('contact.region')) ?><?php if (c('contact.address_note') !== ''): ?><br><?= t(c('contact.address_note')) ?><?php endif; ?></span></li>
        <li><?= icon('whatsapp') ?><span><strong><?= e(c('contact.phone_display')) ?></strong><?php if (c('contact.phone_note') !== ''): ?><br><?= e(c('contact.phone_note')) ?><?php endif; ?></span></li>
        <?php if (c('contact.email') !== ''): ?>
          <li><?= icon('mail') ?><a href="mailto:<?= e(c('contact.email')) ?>"><?= e(c('contact.email')) ?></a></li>
        <?php endif; ?>
      </ul>

      <div class="button-row">
        <a class="btn btn--primary" href="<?= e(wa_url(wa_message('una consulta general'))) ?>" data-track="whatsapp" data-track-label="ubicacion"><?= icon('whatsapp') ?> Escribinos</a>
        <a class="btn btn--ghost-light" href="<?= e(maps_directions_url()) ?>" target="_blank" rel="noopener" data-track="directions" data-track-label="ubicacion"><?= icon('pin') ?> Cómo llegar</a>
        <a class="btn btn--ghost-light" href="tel:<?= e(c('contact.phone')) ?>" data-track="phone" data-track-label="ubicacion"><?= icon('phone') ?> Llamar</a>
      </div>
    </div>

    <div class="map" data-map data-src="<?= e(maps_embed_url()) ?>">
      <div class="map__facade">
        <svg class="map__streets" viewBox="0 0 400 300" aria-hidden="true" focusable="false" preserveAspectRatio="xMidYMid slice">
          <path d="M0 120h400M0 210h400M130 0v300M270 0v300M0 40l400 60" fill="none" stroke="currentColor" stroke-width="14"/>
        </svg>
        <span class="map__pin" aria-hidden="true"><?= cross('cross') ?></span>
        <p class="map__address"><?= e(c('contact.address')) ?> · <?= e(c('contact.city')) ?></p>
        <button class="btn btn--light map__button" type="button" data-map-load><?= icon('pin') ?> Ver mapa</button>
        <p class="map__note">Al tocar se carga Google Maps.</p>
      </div>
    </div>
  </div>
</section>
