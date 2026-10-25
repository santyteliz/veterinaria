<?php
declare(strict_types=1);

function review_row(string $key, array $r): string
{
    ob_start();
    ?>
    <div class="row">
      <div class="row__head">
        <span class="row__title" data-empty="Reseña nueva"><?= e(($r['name'] ?? '') !== '' ? $r['name'] : 'Reseña nueva') ?></span>
        <?= row_tools('reseña') ?>
      </div>
      <div class="grid-2">
        <?= str_replace('<input', '<input data-row-name', field_text("reviews[{$key}][name]", $r['name'] ?? '', 'Nombre', ['maxlength' => 60, 'required' => true])) ?>
        <?= field_select("reviews[{$key}][stars]", (string) ($r['stars'] ?? 5), 'Estrellas', ['5' => '★★★★★', '4' => '★★★★', '3' => '★★★', '2' => '★★', '1' => '★']) ?>
      </div>
      <?= field_textarea("reviews[{$key}][text]", $r['text'] ?? '', 'Texto de la reseña', ['maxlength' => 500, 'rows' => 4]) ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

function section_render(array $data): void
{
    $rv = $data['reviews'] ?? [];
    echo form_open('resenas');
    ?>
    <div class="flash flash--info">Cargá solo reseñas reales, copiadas tal cual de Google. Si no hay ninguna cargada, la sección no se muestra en la web.</div>
    <div class="panel">
      <h2 class="panel__title">Puntaje en Google</h2>
      <div class="grid-2">
        <?= field_text('rating', isset($rv['rating']) ? number_format((float) $rv['rating'], 1, '.', '') : '', 'Puntaje', ['inputmode' => 'decimal', 'placeholder' => '4.8']) ?>
        <?= field_text('count', $rv['count'] ?? '', 'Cantidad de reseñas', ['inputmode' => 'numeric', 'placeholder' => '200']) ?>
      </div>
      <?= field_check('verified', !empty($rv['verified']), 'Confirmé estos números en la ficha de Google', ['hint' => 'Mientras no lo marques, la web muestra un aviso “[A CONFIRMAR]” al lado del puntaje.']) ?>
      <?= field_text('google_url', $rv['google_url'] ?? '', 'Link a las reseñas en Google', ['type' => 'url']) ?>
    </div>
    <div class="rows" id="review-rows">
      <?php foreach ((array) ($rv['items'] ?? []) as $i => $r): ?>
        <?= review_row('k' . $i, (array) $r) ?>
      <?php endforeach; ?>
    </div>
    <template id="review-rows-template"><?= review_row('__key__', []) ?></template>
    <button class="btn btn--add" type="button" data-row-add="review-rows">+ Agregar reseña</button>
    <?php
    echo form_close();
}

function section_save(array $data): array
{
    $rating = (float) str_replace(',', '.', clean_line(posted('rating'), 4));
    $items = [];
    foreach (posted_rows('reviews') as $row) {
        $name = clean_line($row['name'] ?? '', 60);
        $text = clean_str($row['text'] ?? '', 500);
        if ($name === '' || $text === '') {
            continue;
        }
        $items[] = ['name' => $name, 'stars' => max(1, min(5, (int) ($row['stars'] ?? 5))), 'text' => $text];
    }
    $data['reviews'] = [
        'rating' => $rating >= 1 && $rating <= 5 ? round($rating, 1) : 0,
        'count' => max(0, (int) digits((string) posted('count'))),
        'verified' => !empty($_POST['verified']),
        'google_url' => clean_url(posted('google_url')),
        'items' => $items,
    ];
    return [$data, '¡Listo! Reseñas actualizadas.'];
}
