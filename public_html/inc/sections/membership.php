<?php
/** Sistema de socios (descuentos), medios de pago y obras sociales. */

$m = c('membership', []);
$benefits = (array) ($m['benefits'] ?? []);
$pay = c('payments', []);
if (!$benefits && !$pay) {
    return;
}
$maxDiscount = $benefits ? max(array_map(fn ($b) => (int) ($b['discount'] ?? 0), $benefits)) : 0;

/** Ícono según el medio de pago. */
function payment_icon(string $method): string
{
    $m = mb_strtolower($method);
    return match (true) {
        str_contains($m, 'efectivo') => 'cash',
        str_contains($m, 'transfer') => 'transfer',
        str_contains($m, 'qr') => 'qr',
        default => 'card',
    };
}
?>
<section class="section section--light" id="socios" aria-labelledby="socios-title">
  <div class="container">
    <div class="section-head">
      <p class="overline">Socios y formas de pago</p>
      <h2 id="socios-title"><?= e($m['title'] ?? 'Hacete socio') ?></h2>
      <p class="lead"><?= t($m['text'] ?? '') ?></p>
    </div>

    <div class="membership">
      <?php if ($benefits): ?>
        <div class="membership__card" data-reveal>
          <div class="membership__head">
            <span class="membership__badge"><?= icon('percent') ?></span>
            <div>
              <p class="membership__eyebrow">Beneficios para socios</p>
              <p class="membership__max">Hasta <strong><?= $maxDiscount ?>%</strong> de descuento</p>
            </div>
          </div>

          <ul class="benefits" role="list">
            <?php foreach ($benefits as $b): ?>
              <li class="benefits__row">
                <span class="benefits__name"><?= e($b['name']) ?><?php if (!empty($b['detail'])): ?> <small>(<?= e($b['detail']) ?>)</small><?php endif; ?></span>
                <span class="benefits__dots" aria-hidden="true"></span>
                <strong class="benefits__value"><?= (int) $b['discount'] ?>%</strong>
              </li>
            <?php endforeach; ?>
          </ul>

          <ul class="membership__notes" role="list">
            <?php foreach (['note' => 'clock', 'combine_note' => 'shield', 'price_note' => 'whatsapp'] as $key => $ic): ?>
              <?php if (!empty($m[$key])): ?>
                <li><?= icon($ic) ?><span><?= t($m[$key]) ?></span></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
          <a class="btn btn--primary btn--lg btn--block" href="<?= e(wa_url('¡Hola La Guardia! Quiero información para hacerme socio: cuánto es la cuota y cómo me anoto.')) ?>" data-track="whatsapp" data-track-label="socios">
            <?= icon('whatsapp') ?> <?= e($m['cta'] ?? 'Quiero ser socio') ?>
          </a>
        </div>
      <?php endif; ?>

      <div class="membership__side">
        <?php if (!empty($pay['methods'])): ?>
          <div class="pay-card" data-reveal>
            <h3 class="pay-card__title">Formas de pago</h3>
            <?php if (!empty($pay['note'])): ?><p class="pay-card__text"><?= t($pay['note']) ?></p><?php endif; ?>
            <ul class="pay-methods" role="list">
              <?php foreach ((array) $pay['methods'] as $method): ?>
                <li><?= icon(payment_icon((string) $method)) ?><?= e($method) ?></li>
              <?php endforeach; ?>
            </ul>
            <?php if (!empty($pay['exception'])): ?>
              <p class="pay-card__exception"><?= icon('scissors') ?><span><?= t($pay['exception']) ?></span></p>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($pay['insurance'])): ?>
          <div class="pay-card pay-card--insurance" data-reveal>
            <span class="pay-card__icon" aria-hidden="true"><?= icon('shield') ?></span>
            <div>
              <h3 class="pay-card__title">Obras sociales</h3>
              <p class="pay-card__text"><?= t($pay['insurance']) ?></p>
              <?php if (!empty($pay['insurance_list'])): ?>
                <ul class="insurance-list" role="list">
                  <?php foreach ((array) $pay['insurance_list'] as $name): ?><li><?= e($name) ?></li><?php endforeach; ?>
                </ul>
              <?php endif; ?>
              <a class="link-arrow" href="<?= e(wa_url('¡Hola La Guardia! Quiero consultar por la cobertura de mi obra social: ____')) ?>" data-track="whatsapp" data-track-label="obra-social">Consultar cobertura <?= icon('arrow') ?></a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
