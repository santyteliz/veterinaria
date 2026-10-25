<?php
/** Preguntas frecuentes (el schema FAQPage se suma en la etapa de SEO). */

$faq = (array) c('faq', []);
if (!$faq) {
    return;
}
?>
<section class="section section--light" id="preguntas" aria-labelledby="faq-title">
  <div class="container split split--faq">
    <div class="split__copy">
      <p class="overline">Preguntas frecuentes</p>
      <h2 id="faq-title">Lo que más nos preguntan.</h2>
      <p class="lead">¿Te quedó alguna duda? Escribinos y te respondemos.</p>
      <a class="btn btn--primary" href="<?= e(wa_url(wa_message('una duda'))) ?>" data-track="whatsapp" data-track-label="faq"><?= icon('whatsapp') ?> Escribinos</a>
    </div>
    <div class="faq">
      <?php foreach ($faq as $item): ?>
        <details class="faq__item">
          <summary><?= e($item['q']) ?><span class="faq__icon" aria-hidden="true"><?= cross('cross') ?></span></summary>
          <div class="faq__answer"><p><?= t($item['a']) ?></p></div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
