<?php
/** Cómo atendemos: 3 pasos, el seguimiento por WhatsApp destacado. */

$steps = (array) c('steps', []);
?>
<section class="section section--light" id="como-atendemos" aria-labelledby="steps-title">
  <div class="container">
    <div class="section-head">
      <p class="overline">Cómo atendemos</p>
      <h2 id="steps-title">Sin turno, sin vueltas.</h2>
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
  </div>
</section>
