<?php
declare(strict_types=1);

function clinic_row(string $key, array $c): string
{
    ob_start();
    ?>
    <div class="row">
      <div class="row__head">
        <span class="row__title" data-empty="Clínica"><?= e(($c['phone_display'] ?? '') !== '' ? $c['phone_display'] : 'Clínica nueva') ?></span>
        <?= row_tools('clínica') ?>
      </div>
      <div class="grid-2">
        <?= field_text("clinics[{$key}][name]", $c['name'] ?? '', 'Nombre (opcional)', ['maxlength' => 60]) ?>
        <?= str_replace('<input', '<input data-row-name', field_text("clinics[{$key}][phone_display]", $c['phone_display'] ?? '', 'Teléfono', ['inputmode' => 'tel', 'required' => true, 'placeholder' => '342 530-2300'])) ?>
      </div>
    </div>
    <?php
    return (string) ob_get_clean();
}

function section_render(array $data): void
{
    $em = $data['emergency'] ?? [];
    echo form_open('urgencias');
    ?>
    <div class="panel">
      <?= field_text('title', $em['title'] ?? '', 'Título', ['maxlength' => 60]) ?>
      <?= field_textarea('text', $em['text'] ?? '', 'Texto', ['maxlength' => 160, 'rows' => 2]) ?>
    </div>
    <div class="rows" id="clinic-rows">
      <?php foreach ((array) ($em['clinics'] ?? []) as $i => $c): ?>
        <?= clinic_row('k' . $i, (array) $c) ?>
      <?php endforeach; ?>
    </div>
    <template id="clinic-rows-template"><?= clinic_row('__key__', []) ?></template>
    <button class="btn btn--add" type="button" data-row-add="clinic-rows">+ Agregar clínica</button>
    <?php
    echo form_close();
}

function section_save(array $data): array
{
    $clinics = [];
    foreach (posted_rows('clinics') as $row) {
        $display = clean_line($row['phone_display'] ?? '', 30);
        if (strlen(digits($display)) < 6) {
            continue;
        }
        $clinics[] = [
            'name' => clean_line($row['name'] ?? '', 60),
            'phone' => tel_from_display($display),
            'phone_display' => $display,
        ];
    }
    $data['emergency'] = [
        'title' => clean_line(posted('title'), 60) ?: '¿Es una urgencia?',
        'text' => clean_line(posted('text'), 160) ?: 'No realizamos urgencias. Comunicate con estas clínicas de guardia:',
        'clinics' => $clinics,
    ];
    return [$data, '¡Listo! Clínicas de guardia actualizadas.'];
}
