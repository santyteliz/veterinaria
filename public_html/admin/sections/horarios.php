<?php
declare(strict_types=1);

/** Un día: turno mañana y tarde, cada uno con desde/hasta. */
function shifts_html(string $prefix, array $ranges): string
{
    $html = '';
    foreach (['m' => 'Mañana', 't' => 'Tarde'] as $i => $label) {
        $r = $ranges[$i === 'm' ? 0 : 1] ?? ['', ''];
        $html .= '<div class="shift"><span class="shift__label">' . $label . '</span>'
            . field_text("{$prefix}[{$i}_from]", $r[0] ?? '', 'Desde', ['type' => 'time'])
            . field_text("{$prefix}[{$i}_to]", $r[1] ?? '', 'Hasta', ['type' => 'time'])
            . '</div>';
    }
    return $html;
}

/** Lee los dos turnos del POST; descarta los incompletos o al revés. */
function shifts_from(array $row): array
{
    $ranges = [];
    foreach (['m', 't'] as $i) {
        $from = clean_time($row["{$i}_from"] ?? '');
        $to = clean_time($row["{$i}_to"] ?? '');
        if ($from !== '' && $to !== '') {
            if ($from >= $to) {
                throw new RuntimeException('Hay un turno donde la hora “hasta” es igual o anterior a “desde”. Revisalo.');
            }
            $ranges[] = [$from, $to];
        }
    }
    usort($ranges, fn ($a, $b) => strcmp($a[0], $b[0]));
    return $ranges;
}

function special_row(string $key, array $s): string
{
    $closed = empty($s['ranges']);
    ob_start();
    ?>
    <div class="row day<?= $closed ? ' is-closed' : '' ?>">
      <div class="row__head">
        <span class="row__title" data-empty="Día especial"><?= e(($s['label'] ?? '') !== '' ? $s['label'] : 'Día especial') ?></span>
        <?= row_tools('día especial') ?>
      </div>
      <div class="grid-2">
        <?= field_text("special[{$key}][date]", $s['date'] ?? '', 'Fecha', ['type' => 'date', 'required' => true]) ?>
        <?= field_text("special[{$key}][label]", $s['label'] ?? '', 'Motivo', ['maxlength' => 60, 'placeholder' => 'Ej.: Feriado']) ?>
      </div>
      <?= str_replace('<input', '<input data-day-closed', field_check("special[{$key}][closed]", $closed, 'Cerrado todo el día')) ?>
      <?= shifts_html("special[{$key}]", $s['ranges'] ?? []) ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

function section_render(array $data): void
{
    $week = $data['hours']['week'] ?? [];
    echo form_open('horarios');
    ?>
    <div class="panel">
      <h2 class="panel__title">Semana normal</h2>
      <p class="panel__help">Con estos horarios la web calcula “Abierto ahora / Cerrado”.</p>
      <?php for ($d = 1; $d <= 7; $d++): $ranges = (array) ($week[(string) $d] ?? []); ?>
        <div class="day<?= !$ranges ? ' is-closed' : '' ?>">
          <div class="day__head">
            <span class="day__name"><?= e(ucfirst(DAY_NAMES[$d])) ?></span>
            <?= str_replace('<input', '<input data-day-closed', field_check("day[{$d}][closed]", !$ranges, 'Cerrado', ['id' => "closed-{$d}"])) ?>
          </div>
          <?= shifts_html("day[{$d}]", $ranges) ?>
        </div>
      <?php endfor; ?>
    </div>

    <div class="panel">
      <h2 class="panel__title">Aclaración</h2>
      <?= field_text('note', $data['hours']['note'] ?? '', 'Cómo se atiende', ['maxlength' => 80, 'hint' => 'Ej.: “Por orden de llegada”.']) ?>
      <?= field_textarea('pending', $data['hours']['pending'] ?? '', 'Texto extra (opcional)', ['maxlength' => 160, 'rows' => 2, 'hint' => 'Aparece debajo del título de Horarios. Dejalo vacío si no hace falta.']) ?>
    </div>

    <div class="panel">
      <h2 class="panel__title">Feriados y días especiales</h2>
      <p class="panel__help">Para un día puntual con otro horario o cerrado. Pisa al horario normal solo ese día.</p>
      <div class="rows" id="special-rows">
        <?php foreach ((array) ($data['hours']['special'] ?? []) as $i => $s): ?>
          <?= special_row('s' . $i, (array) $s) ?>
        <?php endforeach; ?>
      </div>
      <template id="special-rows-template"><?= special_row('__key__', ['ranges' => []]) ?></template>
      <button class="btn btn--add" type="button" data-row-add="special-rows">+ Agregar día especial</button>
    </div>
    <?php
    echo form_close();
}

function section_save(array $data): array
{
    $week = [];
    $days = (array) posted('day', []);
    for ($d = 1; $d <= 7; $d++) {
        $row = (array) ($days[$d] ?? []);
        $week[(string) $d] = !empty($row['closed']) ? [] : shifts_from($row);
    }

    $special = [];
    foreach (posted_rows('special') as $row) {
        $date = clean_date($row['date'] ?? '');
        if ($date === '') {
            continue;
        }
        $special[] = [
            'date' => $date,
            'label' => clean_line($row['label'] ?? '', 60),
            'ranges' => !empty($row['closed']) ? [] : shifts_from($row),
        ];
    }
    usort($special, fn ($a, $b) => strcmp($a['date'], $b['date']));
    // Los días especiales que ya pasaron se descartan solos.
    $today = now_local()->format('Y-m-d');
    $special = array_values(array_filter($special, fn ($s) => $s['date'] >= $today));

    $data['hours']['week'] = $week;
    $data['hours']['special'] = $special;
    $data['hours']['note'] = clean_line(posted('note'), 80);
    $data['hours']['pending'] = clean_line(posted('pending'), 160);

    return [$data, '¡Listo! Horarios actualizados.'];
}
