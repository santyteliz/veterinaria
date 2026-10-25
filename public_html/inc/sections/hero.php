<?php
/** Hero · Concepto A "La cruz se abre". Markup estático completo; GSAP se monta encima. */

$hero = c('hero', []);
$chips = (array) ($hero['chips'] ?? []);
$reviews = c('reviews', []);
?>
<section class="hero" data-hero data-variant="<?= e($hero['variant'] ?? 'A') ?>" aria-labelledby="hero-title">
  <div class="hero__glow" aria-hidden="true" data-hero-glow></div>
  <div class="grain" aria-hidden="true"></div>

  <div class="container hero__grid">
    <div class="hero__copy" data-hero-copy>
      <p class="chip chip--outline hero__overline"><?= cross('cross cross--xs') ?><?= e($hero['overline'] ?? '') ?></p>
      <h1 class="hero__title" id="hero-title" data-hero-title>
        <?= e($hero['title'] ?? '') ?> <span class="hero__accent"><?= e($hero['title_accent'] ?? '') ?></span>
      </h1>
      <p class="hero__sub" data-hero-fade><?= t($hero['subtitle'] ?? '') ?></p>
      <div class="hero__actions" data-hero-fade>
        <a class="btn btn--primary btn--lg btn--glow" href="<?= e(wa_url(wa_message('una consulta general'))) ?>" data-magnetic data-track="whatsapp" data-track-label="hero">
          Consultar por WhatsApp <?= icon('arrow') ?>
        </a>
        <a class="btn btn--ghost-light btn--lg" href="#servicios">Ver servicios</a>
      </div>
      <?php if (!empty($reviews['rating'])): ?>
        <p class="hero__trust" data-hero-fade>
          <?php /* El contador animado solo corre con datos confirmados en la ficha de Google. */ ?>
          <strong><span<?= !empty($reviews['verified']) ? ' data-count-to="' . e(number_format((float) $reviews['rating'], 1, '.', '')) . '"' : '' ?>><?= e(number_format((float) $reviews['rating'], 1, ',', '')) ?></span> <span aria-hidden="true">★</span></strong>
          <span>en Google<?php if (!empty($reviews['count'])): ?> · <?= e((int) $reviews['count']) ?> reseñas<?php endif; ?></span>
          <?php if (empty($reviews['verified'])): ?><mark class="tbc">[A CONFIRMAR: con la ficha oficial de Google]</mark><?php endif; ?>
        </p>
      <?php endif; ?>
    </div>

    <div class="hero__visual" data-hero-visual>
      <div class="hero__cross" data-hero-cross>
        <svg class="hero__arms" viewBox="0 0 120 120" aria-hidden="true" focusable="false">
          <rect class="hero__arm" data-arm="top" x="40" y="0" width="40" height="60" rx="10"/>
          <rect class="hero__arm" data-arm="bottom" x="40" y="60" width="40" height="60" rx="10"/>
          <rect class="hero__arm" data-arm="left" x="0" y="40" width="60" height="40" rx="10"/>
          <rect class="hero__arm" data-arm="right" x="60" y="40" width="60" height="40" rx="10"/>
          <rect class="hero__arm" data-arm="center" x="40" y="40" width="40" height="40"/>
        </svg>
        <div class="hero__media" data-hero-media<?php if (!empty($hero['video'])): ?> data-video="<?= e($hero['video']) ?>"<?php endif; ?>>
          <?= img_or_placeholder((string) ($hero['image'] ?? ''), (string) ($hero['image_alt'] ?? ''), 'foto o video de perro y gato reales', 'hero__img', false, '(min-width: 1024px) 36rem, 22rem') ?>
        </div>
        <div class="hero__fill" aria-hidden="true" data-hero-fill></div>
      </div>

      <?php /* Capa externa: la mueve el scroll. Capa interna: entrada + parallax. */ ?>
      <?php foreach (array_slice($chips, 0, 3) as $i => $chip): ?>
        <span class="hero__chip hero__chip--<?= $i + 1 ?>" data-hero-chip-pos>
          <span class="chip" data-hero-chip data-depth="<?= e(0.02 * ($i + 1)) ?>"><?= cross('cross cross--xs') ?><?= e($chip) ?></span>
        </span>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="hero__flood" aria-hidden="true" data-hero-flood></div>
</section>
