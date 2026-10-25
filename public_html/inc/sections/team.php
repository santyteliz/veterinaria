<?php
/** Nosotros / equipo + bloque de atención a domicilio. */

$team = (array) c('team', []);
$visit = c('home_visits', []);
?>
<section class="section section--light" id="nosotros" aria-labelledby="nosotros-title">
  <div class="container">
    <div class="section-head">
      <p class="overline">Nosotros</p>
      <h2 id="nosotros-title">Una clínica de barrio, con nombre y apellido.</h2>
      <p class="lead">En <?= e(c('contact.city')) ?> te atiende siempre la misma gente, que conoce a tu pacientito y lo sigue de cerca.</p>
    </div>

    <ul class="team" role="list">
      <?php foreach ($team as $member): ?>
        <li class="team__card" data-reveal>
          <div class="team__photo">
            <?= img_or_placeholder((string) ($member['photo'] ?? ''), 'Foto de ' . $member['name'], 'foto de ' . $member['name'], 'team__img', true, '(min-width: 900px) 30vw, 100vw') ?>
            <?= cross('cross team__badge') ?>
          </div>
          <h3 class="team__name"><?= e($member['name']) ?></h3>
          <p class="team__role"><?= t($member['role'] ?? '') ?></p>
          <p class="team__bio"><?= t($member['bio'] ?? '') ?></p>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="visit">
      <div class="visit__icon" aria-hidden="true"><?= icon('home') ?></div>
      <div class="visit__copy">
        <h3>Atención a domicilio</h3>
        <p>¿No podés venir? El <?= e($visit['vet'] ?? '') ?> va a tu casa. Coordiná directo con él, tiene su propio número.</p>
      </div>
      <div class="visit__actions">
        <a class="btn btn--primary" href="<?= e(wa_url(wa_message_visit(),(string) ($visit['whatsapp'] ?? ''))) ?>" data-track="whatsapp" data-track-label="domicilio"><?= icon('whatsapp') ?> WhatsApp</a>
        <a class="btn btn--ghost-dark" href="tel:<?= e($visit['phone'] ?? '') ?>" data-track="phone" data-track-label="domicilio"><?= icon('phone') ?> <?= e($visit['phone_display'] ?? '') ?></a>
      </div>
    </div>
  </div>
</section>
