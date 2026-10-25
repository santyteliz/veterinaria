<?php
/** Cierre: footer, botón flotante, mini formulario de WhatsApp y scripts. */

declare(strict_types=1);

$credits = (string) c('credits.url');
?>
</main>

<footer class="site-footer" id="footer">
  <div class="container site-footer__grid">
    <div class="site-footer__brand">
      <img src="/assets/img/logo-la-guardia-150.webp" alt="Clínica Veterinaria La Guardia" width="88" height="88" loading="lazy" decoding="async">
      <p>Clínica general, peluquería canina, pet shop y farmacia en <?= e(c('contact.city')) ?>, <?= e(c('contact.region')) ?>.</p>
      <p class="site-footer__warn"><?= icon('alert') ?> No realizamos urgencias. <a href="/#urgencias">Ver clínicas de guardia</a></p>
    </div>

    <div>
      <h2 class="site-footer__title">Contacto</h2>
      <ul class="site-footer__list">
        <li><?= icon('whatsapp') ?><a href="<?= e(wa_url(wa_message('una consulta general'))) ?>" data-track="whatsapp" data-track-label="footer">WhatsApp <?= e(c('contact.phone_display')) ?></a></li>
        <li><?= icon('phone') ?><a href="tel:<?= e(c('contact.phone')) ?>" data-track="phone" data-track-label="footer">Llamar <?= e(c('contact.phone_display')) ?></a></li>
        <li><?= icon('mail') ?><a href="mailto:<?= e(c('contact.email')) ?>"><?= e(c('contact.email')) ?></a></li>
        <li><?= icon('pin') ?><a href="<?= e(maps_directions_url()) ?>" target="_blank" rel="noopener" data-track="directions" data-track-label="footer"><?= e(c('contact.address')) ?>, <?= e(c('contact.city')) ?></a></li>
      </ul>
    </div>

    <div>
      <h2 class="site-footer__title">Horarios</h2>
      <ul class="site-footer__list site-footer__list--plain">
        <?php foreach (hours_summary() as $row): ?>
          <li><span><?= e($row['days']) ?></span> <strong><?= e($row['hours']) ?></strong></li>
        <?php endforeach; ?>
        <li><?= e(c('hours.note')) ?></li>
      </ul>
      <div class="site-footer__social">
        <a href="<?= e(c('contact.instagram')) ?>" target="_blank" rel="noopener" aria-label="Instagram de La Guardia"><?= icon('instagram') ?></a>
        <a href="<?= e(c('contact.facebook')) ?>" target="_blank" rel="noopener" aria-label="Facebook de La Guardia"><?= icon('facebook') ?></a>
      </div>
    </div>
  </div>

  <div class="container site-footer__bottom">
    <p>© <?= date('Y') ?> <?= e(c('site.name')) ?></p>
    <p><a href="/politica-de-privacidad">Política de privacidad</a></p>
    <p><?php if ($credits !== '' && !is_placeholder($credits)): ?><a href="<?= e($credits) ?>" target="_blank" rel="noopener"><?= e(c('credits.label')) ?></a><?php else: ?><?= e(c('credits.label')) ?> <?= t($credits) ?><?php endif; ?></p>
  </div>
</footer>

<a class="wa-float" href="<?= e(wa_url(wa_message('una consulta general'))) ?>" aria-label="Escribinos por WhatsApp" data-track="whatsapp" data-track-label="flotante">
  <?= icon('whatsapp') ?>
  <span class="wa-float__tip" aria-hidden="true">¿Consultas? Escribinos</span>
</a>

<dialog class="wa-dialog" data-wa-dialog aria-labelledby="wa-dialog-title">
  <form method="dialog" class="wa-dialog__form" data-wa-form>
    <div class="wa-dialog__head">
      <h2 id="wa-dialog-title">Armá tu consulta</h2>
      <button class="wa-dialog__close" type="submit" value="cancel" aria-label="Cerrar" formnovalidate><?= icon('close') ?></button>
    </div>
    <p class="wa-dialog__service">Servicio: <strong data-wa-service-label></strong></p>
    <input type="hidden" name="service" data-wa-service>
    <input type="hidden" name="number" data-wa-number>
    <label for="wa-pet">Nombre de tu mascota</label>
    <input id="wa-pet" name="pet" type="text" autocomplete="off" placeholder="Ej.: Rocco">
    <label for="wa-species">Especie</label>
    <select id="wa-species" name="species">
      <option value="">Elegí una opción</option>
      <option>Perro</option>
      <option>Gato</option>
      <option>Otra</option>
    </select>
    <label for="wa-reason">Motivo</label>
    <textarea id="wa-reason" name="reason" rows="3" placeholder="Contanos brevemente qué necesitás"></textarea>
    <p class="wa-dialog__note">No guardamos estos datos: solo arman el mensaje de WhatsApp.</p>
    <button class="btn btn--primary btn--block" type="submit" value="send"><?= icon('whatsapp') ?> Abrir WhatsApp</button>
  </form>
</dialog>

<?php if (($page['path'] ?? '') === '/'): /* Easter egg: 5 clicks en el logo y cruza un perrito. */ ?>
<template id="lg-dog"><div class="dog-run" aria-hidden="true"><span class="dog-run__bubble">¡Guau!</span><?= dog_svg('dog dog--walk') ?></div></template>
<?php endif; ?>
<script>window.LG =<?= json_encode(['template' => c('whatsapp_template'), 'whatsapp' => c('contact.whatsapp')], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
<script src="<?= asset('js/app.js') ?>" defer></script>
<?php if (!empty($page['motion'])): /* Solo la home tiene hero animado. GSAP y sus plugins los carga motion.js cuando hacen falta. */ ?>
<script src="<?= asset('js/motion.js') ?>" defer></script>
<?php endif; ?>
</body>
</html>
