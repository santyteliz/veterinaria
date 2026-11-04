<?php
/** Cómo atendemos: 3 pasos (el seguimiento por WhatsApp destacado) + cómo traer a la mascota. */

$steps = (array) c('steps', []);
$tip = c('visit_tip', []);
?>
<section class="section section--light" id="como-atendemos" aria-labelledby="steps-title">
  <div class="container">
    <div class="section-head">
      <p class="overline">Cómo atendemos</p>
      <h2 id="steps-title"><?= e(c('steps_title', 'Un cuidado integral, de principio a fin.')) ?></h2>
    </div>
    <ol class="steps">
      <?php foreach ($steps as $i => $step): ?>
        <li class="step<?= !empty($step['highlight']) ? ' step--highlight' : '' ?>" data-reveal>
          <span class="step__marker" aria-hidden="true"><?= cross('cross') ?><span><?= $i + 1 ?></span></span>
          <h3 class="step__title"><?= e($step['title']) ?></h3>
          <p><?= t($step['text']) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>

    <?php if (!empty($tip['title'])): ?>
      <div class="visit-tip" data-reveal>
        <?php if (!empty($tip['image'])): ?>
          <div class="visit-tip__media">
            <?= img_or_placeholder((string) $tip['image'], (string) ($tip['image_alt'] ?? ''), '', 'visit-tip__img', true, '(min-width: 900px) 26rem, 100vw') ?>
          </div>
        <?php endif; ?>
        <div class="visit-tip__body">
          <p class="overline"><?= e($tip['overline'] ?? '') ?></p>
          <h3 class="visit-tip__title"><?= e($tip['title']) ?></h3>
          <p class="visit-tip__text"><?= t($tip['text'] ?? '') ?></p>
          <ul class="visit-tip__list" role="list">
            <?php foreach ((array) ($tip['items'] ?? []) as $item): ?>
              <li><span class="visit-tip__label"><?= cross('cross') ?><?= e($item['label']) ?></span><span><?= t($item['text']) ?></span></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
