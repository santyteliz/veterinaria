<?php
/** Horarios y ubicación, con mapa en facade (carga el iframe recién al hacer click). */

$todayN = (int) now_local()->format('N');
$week = (array) c('hours.week', []);
?>
<section class="section section--dark" id="contacto" aria-labelledby="contacto-title">
  <div class="grain" aria-hidden="true"></div>
  <div class="container split split--location">
    <div class="split__copy">
      <p class="overline">Horarios y ubicación</p>
      <h2 id="contacto-title">Vení cuando quieras, dentro del horario.</h2>
      <p class="lead"><?= e(c('hours.note')) ?>, sin turno. <?= t(c('hours.pending')) ?></p>

      <table class="hours">
        <caption class="visually-hidden">Horarios de atención</caption>
        <tbody>
          <?php for ($d = 1; $d <= 7; $d++): $ranges = (array) ($week[(string) $d] ?? []); ?>
            <tr<?= $d === $todayN ? ' class="is-today"' : '' ?>>
              <th scope="row"><?= e(ucfirst(DAY_NAMES[$d])) ?><?= $d === $todayN ? ' <span class="hours__today">hoy</span>' : '' ?></th>
              <td><?= $ranges ? e(implode(' y ', array_map('fmt_range', $ranges))) : 'Cerrado' ?></td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>

      <address class="address">
        <?= icon('pin') ?>
        <span><?= e(c('contact.address')) ?>, <?= e(c('contact.city')) ?>, <?= e(c('contact.region')) ?><br><?= t(c('contact.address_note')) ?></span>
      </address>

      <div class="button-row">
        <a class="btn btn--primary" href="<?= e(maps_directions_url()) ?>" target="_blank" rel="noopener" data-track="directions" data-track-label="ubicacion"><?= icon('pin') ?> Cómo llegar</a>
        <a class="btn btn--ghost-light" href="tel:<?= e(c('contact.phone')) ?>" data-track="phone" data-track-label="ubicacion"><?= icon('phone') ?> Llamar</a>
      </div>
    </div>

    <div class="map" data-map data-src="<?= e(maps_embed_url()) ?>">
      <div class="map__facade">
        <svg class="map__streets" viewBox="0 0 400 300" aria-hidden="true" focusable="false" preserveAspectRatio="xMidYMid slice">
          <path d="M0 120h400M0 210h400M130 0v300M270 0v300M0 40l400 60" fill="none" stroke="currentColor" stroke-width="14"/>
        </svg>
        <span class="map__pin" aria-hidden="true"><?= cross('cross') ?></span>
        <button class="btn btn--light map__button" type="button" data-map-load><?= icon('pin') ?> Ver mapa</button>
        <p class="map__note">Al tocar se carga Google Maps.</p>
      </div>
    </div>
  </div>
</section>
