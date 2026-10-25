<?php
declare(strict_types=1);

const SERVICE_ICONS = [
    'stethoscope' => 'Estetoscopio (clínica)',
    'syringe' => 'Jeringa (vacunas)',
    'scissors' => 'Tijera (peluquería)',
    'bag' => 'Bolsa (pet shop)',
    'pill' => 'Pastilla (farmacia)',
    'bowl' => 'Plato (alimento)',
    'home' => 'Casa (domicilio)',
    'phone' => 'Teléfono',
    'clock' => 'Reloj',
    'star' => 'Estrella',
];

function service_row(string $key, array $s): string
{
    $visible = !array_key_exists('visible', $s) || !empty($s['visible']);
    ob_start();
    ?>
    <div class="row<?= $visible ? '' : ' is-hidden-service' ?>">
      <div class="row__head">
        <span class="row__title" data-empty="Servicio nuevo"><?= e(($s['name'] ?? '') !== '' ? $s['name'] : 'Servicio nuevo') ?></span>
        <?= row_tools('servicio') ?>
      </div>
      <input type="hidden" name="services[<?= e($key) ?>][id]" value="<?= e($s['id'] ?? '') ?>">
      <?= str_replace('<input', '<input data-visible-toggle', field_check("services[{$key}][visible]", $visible, 'Mostrar en la web')) ?>
      <?= str_replace('<input', '<input data-row-name', field_text("services[{$key}][name]", $s['name'] ?? '', 'Nombre', ['maxlength' => 50, 'required' => true])) ?>
      <?= field_textarea("services[{$key}][description]", $s['description'] ?? '', 'Descripción', ['maxlength' => 180, 'rows' => 3]) ?>
      <?= field_select("services[{$key}][icon]", $s['icon'] ?? 'stethoscope', 'Ícono', SERVICE_ICONS) ?>
      <?= field_text("services[{$key}][cta]", $s['cta'] ?? '', 'Texto del botón (opcional)', ['maxlength' => 30, 'placeholder' => 'Consultar']) ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

function section_render(array $data): void
{
    echo form_open('servicios');
    ?>
    <p class="panel__help">Cada servicio tiene su botón que abre WhatsApp con el mensaje “Quiero consultar por: <em>nombre</em>”. Ordenalos con ↑ ↓.</p>
    <div class="rows" id="service-rows">
      <?php foreach ((array) ($data['services'] ?? []) as $i => $s): ?>
        <?= service_row('k' . $i, (array) $s) ?>
      <?php endforeach; ?>
    </div>
    <template id="service-rows-template"><?= service_row('__key__', []) ?></template>
    <button class="btn btn--add" type="button" data-row-add="service-rows">+ Agregar servicio</button>
    <?php
    echo form_close();
}

function slugify(string $s): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
    return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii)), '-') ?: 'servicio';
}

function section_save(array $data): array
{
    $old = [];
    foreach ((array) ($data['services'] ?? []) as $s) {
        $old[$s['id'] ?? ''] = $s;
    }

    $services = [];
    $used = [];
    foreach (posted_rows('services') as $row) {
        $name = clean_line($row['name'] ?? '', 50);
        if ($name === '') {
            continue;
        }
        $id = slugify((string) ($row['id'] ?? '') !== '' ? (string) $row['id'] : $name);
        while (isset($used[$id])) {
            $id .= '-2';
        }
        $used[$id] = true;

        $service = array_merge($old[$id] ?? [], [
            'id' => $id,
            'name' => $name,
            'description' => clean_line($row['description'] ?? '', 180),
            'icon' => array_key_exists($row['icon'] ?? '', SERVICE_ICONS) ? $row['icon'] : 'stethoscope',
            'visible' => !empty($row['visible']),
        ]);
        $cta = clean_line($row['cta'] ?? '', 30);
        if ($cta !== '') {
            $service['cta'] = $cta;
        } else {
            unset($service['cta']);
        }
        $services[] = $service;
    }

    if (!$services) {
        throw new RuntimeException('Tiene que quedar al menos un servicio.');
    }
    $data['services'] = $services;
    return [$data, '¡Listo! Servicios actualizados.'];
}
