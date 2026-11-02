<?php
declare(strict_types=1);

function benefit_row(string $key, array $b): string
{
    ob_start();
    ?>
    <div class="row">
      <div class="row__head">
        <span class="row__title" data-empty="Beneficio nuevo"><?= e(($b['name'] ?? '') !== '' ? $b['name'] : 'Beneficio nuevo') ?></span>
        <?= row_tools('beneficio') ?>
      </div>
      <?= str_replace('<input', '<input data-row-name', field_text("benefits[{$key}][name]", $b['name'] ?? '', 'Qué incluye', ['maxlength' => 50, 'required' => true, 'placeholder' => 'Ej.: Consultas'])) ?>
      <div class="grid-2">
        <?= field_text("benefits[{$key}][discount]", isset($b['discount']) ? (string) $b['discount'] : '', 'Descuento (%)', ['inputmode' => 'numeric', 'required' => true, 'placeholder' => '20']) ?>
        <?= field_text("benefits[{$key}][detail]", $b['detail'] ?? '', 'Aclaración (opcional)', ['maxlength' => 40, 'placeholder' => 'Ej.: Medicación aparte']) ?>
      </div>
    </div>
    <?php
    return (string) ob_get_clean();
}

function section_render(array $data): void
{
    $m = $data['membership'] ?? [];
    $p = $data['payments'] ?? [];
    echo form_open('socios');
    ?>
    <div class="panel">
      <h2 class="panel__title">Sistema de socios</h2>
      <?= field_text('title', $m['title'] ?? '', 'Título', ['maxlength' => 70]) ?>
      <?= field_textarea('text', $m['text'] ?? '', 'Texto', ['maxlength' => 200, 'rows' => 3]) ?>
      <?= field_text('note', $m['note'] ?? '', 'Aclaración', ['maxlength' => 120, 'hint' => 'Ej.: “Los beneficios empiezan a correr a partir del segundo mes de abono”.']) ?>
    </div>

    <h2 class="panel__title">Descuentos</h2>
    <div class="rows" id="benefit-rows">
      <?php foreach ((array) ($m['benefits'] ?? []) as $i => $b): ?>
        <?= benefit_row('k' . $i, (array) $b) ?>
      <?php endforeach; ?>
    </div>
    <template id="benefit-rows-template"><?= benefit_row('__key__', []) ?></template>
    <button class="btn btn--add" type="button" data-row-add="benefit-rows">+ Agregar descuento</button>

    <div class="panel">
      <h2 class="panel__title">Formas de pago</h2>
      <?= field_text('methods', implode(', ', (array) ($p['methods'] ?? [])), 'Medios de pago', ['maxlength' => 200, 'hint' => 'Separados por coma. Ej.: Efectivo, Transferencia, QR, Tarjeta de débito, Tarjeta de crédito.']) ?>
      <?= field_text('pay_note', $p['note'] ?? '', 'Para qué valen', ['maxlength' => 140]) ?>
      <?= field_text('exception', $p['exception'] ?? '', 'Excepción (opcional)', ['maxlength' => 120, 'hint' => 'Ej.: “Peluquería canina: solo efectivo o transferencia”.']) ?>
      <?= field_text('insurance', $p['insurance'] ?? '', 'Obras sociales', ['maxlength' => 140, 'hint' => 'Dejalo vacío para no mostrar el recuadro de obras sociales.']) ?>
    </div>
    <?php
    echo form_close();
}

function section_save(array $data): array
{
    $benefits = [];
    foreach (posted_rows('benefits') as $row) {
        $name = clean_line($row['name'] ?? '', 50);
        $discount = (int) digits((string) ($row['discount'] ?? ''));
        if ($name === '' || $discount < 1) {
            continue;
        }
        $benefits[] = ['name' => $name, 'detail' => clean_line($row['detail'] ?? '', 40), 'discount' => min(100, $discount)];
    }
    $methods = array_values(array_filter(array_map(fn ($m) => clean_line($m, 40), explode(',', (string) posted('methods')))));

    $data['membership'] = array_merge($data['membership'] ?? [], [
        'title' => clean_line(posted('title'), 70) ?: 'Hacete socio y ahorrá todo el año.',
        'text' => clean_line(posted('text'), 200),
        'note' => clean_line(posted('note'), 120),
        'benefits' => $benefits,
    ]);
    $data['payments'] = [
        'methods' => $methods,
        'note' => clean_line(posted('pay_note'), 140),
        'exception' => clean_line(posted('exception'), 120),
        'insurance' => clean_line(posted('insurance'), 140),
    ];
    return [$data, '¡Listo! Socios y formas de pago actualizados.'];
}
