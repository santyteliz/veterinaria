<?php
/** Nosotros / equipo. Sin foto cargada, cada persona muestra un avatar con sus iniciales. */

$team = (array) c('team', []);

/** "MV Ángel Emilio Nasso" → "ÁN" (sin el título profesional). */
function team_initials(string $name): string
{
    $words = array_values(array_filter(preg_split('/\s+/u', trim($name)) ?: [], fn ($w) => !preg_match('/^(MV|M\.V\.|Dr\.?|Dra\.?)$/iu', $w)));
    if (!$words) {
        return '';
    }
    $first = mb_substr($words[0], 0, 1);
    $last = count($words) > 1 ? mb_substr($words[count($words) - 1], 0, 1) : '';
    return mb_strtoupper($first . $last);
}
?>
<section class="section section--light" id="nosotros" aria-labelledby="nosotros-title">
  <div class="container">
    <div class="section-head">
      <p class="overline">Nosotros</p>
      <h2 id="nosotros-title">Una clínica de barrio, con nombre y apellido.</h2>
      <p class="lead">En <?= e(c('contact.city')) ?> te atiende siempre la misma gente, que conoce a tu pacientito y lo sigue de cerca.</p>
    </div>

    <ul class="team" role="list">
      <?php foreach ($team as $i => $member): ?>
        <li class="team__card" data-reveal>
          <div class="team__photo<?= empty($member['photo']) ? ' team__photo--avatar team__photo--' . ($i % 4 + 1) : '' ?>">
            <?php if (!empty($member['photo'])): ?>
              <?= img_or_placeholder((string) $member['photo'], 'Foto de ' . $member['name'], '', 'team__img', true, '(min-width: 1100px) 22vw, (min-width: 640px) 45vw, 100vw') ?>
            <?php else: ?>
              <span class="team__initials" aria-hidden="true"><?= e(team_initials((string) $member['name'])) ?></span>
            <?php endif; ?>
            <?= cross('cross team__badge') ?>
          </div>
          <div class="team__body">
            <p class="team__role"><?= t($member['role'] ?? '') ?></p>
            <h3 class="team__name"><?= e($member['name']) ?></h3>
            <?php if (!empty($member['detail'])): ?><p class="team__detail"><?= e($member['detail']) ?></p><?php endif; ?>
            <p class="team__bio"><?= t($member['bio'] ?? '') ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
