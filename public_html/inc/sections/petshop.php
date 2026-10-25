<?php
/** Pet Shop y balanceados: grilla editable, sin e-commerce; cada ítem consulta stock. */

$shop = c('petshop', []);
$items = (array) ($shop['items'] ?? []);
?>
<section class="section section--white" id="pet-shop" aria-labelledby="petshop-title">
  <div class="container">
    <div class="section-head section-head--row">
      <div>
        <p class="overline">Pet Shop</p>
        <h2 id="petshop-title"><?= e($shop['title'] ?? 'Pet Shop y balanceados') ?></h2>
      </div>
      <p class="lead"><?= t($shop['text'] ?? '') ?><?php if (!empty($shop['brands_note'])): ?><br><?= t($shop['brands_note']) ?><?php endif; ?></p>
    </div>

    <ul class="shop" role="list">
      <?php foreach ($items as $item): ?>
        <li class="shop__item">
          <div class="shop__media">
            <?= img_or_placeholder((string) ($item['image'] ?? ''), (string) $item['name'], 'foto de ' . mb_strtolower((string) $item['name']), 'shop__img', true, '(min-width: 1280px) 19rem, (min-width: 640px) 45vw, 100vw') ?>
          </div>
          <div class="shop__body">
            <h3 class="shop__title"><?= e($item['name']) ?></h3>
            <a class="link-arrow" href="<?= e(wa_url('¡Hola La Guardia! Quiero consultar stock de: ' . $item['name'] . '. ¿Qué tienen disponible?')) ?>" data-track="whatsapp" data-track-label="stock:<?= e($item['name']) ?>">
              Consultar stock <?= icon('arrow') ?>
            </a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
