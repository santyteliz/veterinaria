<?php
declare(strict_types=1);

function member_row(string $key, array $m): string
{
    ob_start();
    ?>
    <div class="row">
      <div class="row__head">
        <span class="row__title" data-empty="Persona nueva"><?= e(($m['name'] ?? '') !== '' ? $m['name'] : 'Persona nueva') ?></span>
        <?= row_tools('persona') ?>
      </div>
      <?= field_photo("team_photo[{$key}]", "team[{$key}][photo]", "team[{$key}][photo_remove]", (string) ($m['photo'] ?? ''), 'Foto') ?>
      <?= str_replace('<input', '<input data-row-name', field_text("team[{$key}][name]", $m['name'] ?? '', 'Nombre', ['maxlength' => 60, 'required' => true])) ?>
      <?= field_text("team[{$key}][role]", $m['role'] ?? '', 'Rol', ['maxlength' => 60, 'placeholder' => 'Ej.: Médico veterinario']) ?>
      <?= field_textarea("team[{$key}][bio]", $m['bio'] ?? '', 'Presentación', ['maxlength' => 280, 'rows' => 3]) ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

function section_render(array $data): void
{
    echo form_open('equipo', true);
    ?>
    <div class="rows" id="team-rows">
      <?php foreach ((array) ($data['team'] ?? []) as $i => $m): ?>
        <?= member_row('k' . $i, (array) $m) ?>
      <?php endforeach; ?>
    </div>
    <template id="team-rows-template"><?= member_row('__key__', []) ?></template>
    <button class="btn btn--add" type="button" data-row-add="team-rows">+ Agregar persona</button>
    <?php
    echo form_close();
}

function section_save(array $data): array
{
    $files = uploaded_files('team_photo');
    $team = [];
    foreach (posted_rows_keyed('team') as $key => $row) {
        $name = clean_line($row['name'] ?? '', 60);
        if ($name === '') {
            continue;
        }
        $photo = clean_line($row['photo'] ?? '', 200);
        if (!str_starts_with($photo, '/uploads/') || !empty($row['photo_remove'])) {
            $photo = '';
        }
        if (upload_given($files[$key] ?? null)) {
            try {
                $photo = process_image($files[$key]);
            } catch (RuntimeException $e) {
                flash('error', $e->getMessage());
            }
        }
        $team[] = [
            'name' => $name,
            'role' => clean_line($row['role'] ?? '', 60),
            'bio' => clean_line($row['bio'] ?? '', 280),
            'photo' => $photo,
        ];
    }
    $data['team'] = $team;
    return [$data, '¡Listo! Equipo actualizado.'];
}
