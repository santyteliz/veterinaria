<?php
declare(strict_types=1);

function pair_row(string $key, array $p): string
{
    $name = (string) ($p['name'] ?? '');
    ob_start();
    ?>
    <div class="row">
      <div class="row__head">
        <span class="row__title" data-empty="Antes y después"><?= e($name !== '' ? $name : 'Antes y después') ?></span>
        <?= row_tools('antes y después') ?>
      </div>
      <div class="grid-2">
        <?= field_photo("ba_before[{$key}]", "pairs[{$key}][before]", "pairs[{$key}][before_remove]", (string) ($p['before'] ?? ''), 'Foto de antes') ?>
        <?= field_photo("ba_after[{$key}]", "pairs[{$key}][after]", "pairs[{$key}][after_remove]", (string) ($p['after'] ?? ''), 'Foto de después') ?>
      </div>
      <?= str_replace('<input', '<input data-row-name', field_text("pairs[{$key}][name]", $name, 'Nombre de la mascota (opcional)', ['maxlength' => 30, 'placeholder' => 'Ej.: Rocco'])) ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

function section_render(array $data): void
{
    $g = $data['grooming'] ?? [];
    echo form_open('peluqueria', true);
    ?>
    <div class="panel">
      <h2 class="panel__title">Textos</h2>
      <?= field_text('title', $g['title'] ?? '', 'Título', ['maxlength' => 60]) ?>
      <?= field_textarea('text', $g['text'] ?? '', 'Texto', ['maxlength' => 300, 'rows' => 4]) ?>
      <?= field_text('facts', implode(', ', (array) ($g['facts'] ?? [])), 'Etiquetas', ['maxlength' => 160, 'hint' => 'Separadas por coma. Ej.: Turnos por WhatsApp, Efectivo o transferencia.']) ?>
    </div>

    <h2 class="panel__title">Fotos de antes y después</h2>
    <p class="panel__help">Subí la foto de antes y la de después de la misma mascota. En la web se comparan deslizando. Mejor si las dos están sacadas desde el mismo ángulo. Lo que mejor queda son 2 o 4 pares.</p>
    <div class="rows" id="pair-rows">
      <?php foreach ((array) ($g['before_after'] ?? []) as $i => $p): ?>
        <?= pair_row('k' . $i, (array) $p) ?>
      <?php endforeach; ?>
    </div>
    <template id="pair-rows-template"><?= pair_row('__key__', []) ?></template>
    <button class="btn btn--add" type="button" data-row-add="pair-rows">+ Agregar antes y después</button>
    <?php
    echo form_close();
}

function section_save(array $data): array
{
    $before = uploaded_files('ba_before');
    $after = uploaded_files('ba_after');
    $pairs = [];
    foreach (posted_rows_keyed('pairs') as $key => $row) {
        $photos = [];
        foreach (['before' => $before, 'after' => $after] as $side => $files) {
            $photo = clean_line($row[$side] ?? '', 200);
            if (!str_starts_with($photo, '/uploads/') || !empty($row[$side . '_remove'])) {
                $photo = '';
            }
            if (upload_given($files[$key] ?? null)) {
                try {
                    $photo = process_image($files[$key]);
                } catch (RuntimeException $e) {
                    flash('error', $e->getMessage());
                }
            }
            $photos[$side] = $photo;
        }
        if ($photos['before'] === '' && $photos['after'] === '') {
            continue;
        }
        $name = clean_line($row['name'] ?? '', 30);
        $pairs[] = [
            'before' => $photos['before'],
            'after' => $photos['after'],
            'name' => $name,
            'alt' => ($name !== '' ? $name : 'Perro') . ' antes y después de la peluquería',
        ];
    }

    $facts = array_values(array_filter(array_map(fn ($f) => clean_line($f, 40), explode(',', (string) posted('facts')))));
    $data['grooming'] = array_merge($data['grooming'] ?? [], [
        'title' => clean_line(posted('title'), 60) ?: 'Peluquería canina',
        'text' => clean_line(posted('text'), 300),
        'facts' => $facts,
        'before_after' => $pairs,
    ]);
    return [$data, '¡Listo! Peluquería actualizada.'];
}
