<?php
declare(strict_types=1);

function section_render(array $data): void
{
    $a = $data['announcement'] ?? [];
    echo form_open('aviso');
    ?>
    <div class="panel">
      <?= field_check('enabled', !empty($a['enabled']), 'Mostrar el aviso en la web') ?>
      <?= field_textarea('text', $a['text'] ?? '', 'Texto del aviso', ['maxlength' => 140, 'rows' => 2, 'hint' => 'Corto y claro. Ej.: “Viernes 4: consultorio solo de 9 a 12 h”.']) ?>
      <?= field_select('style', $a['style'] ?? 'magenta', 'Color', ['magenta' => 'Magenta (llama la atención)', 'plum' => 'Ciruela (más sobrio)']) ?>
      <?= field_text('expires', $a['expires'] ?? '', 'Se apaga solo después del día…', ['type' => 'date', 'hint' => 'Opcional. Dejalo vacío para que quede hasta que lo desactives.']) ?>
    </div>
    <?php
    echo form_close();
}

function section_save(array $data): array
{
    $text = clean_line(posted('text'), 140);
    $enabled = !empty($_POST['enabled']);
    if ($enabled && $text === '') {
        throw new RuntimeException('Escribí el texto del aviso antes de activarlo.');
    }
    $data['announcement'] = [
        'enabled' => $enabled,
        'text' => $text,
        'style' => in_array(posted('style'), ['magenta', 'plum'], true) ? posted('style') : 'magenta',
        'expires' => clean_date(posted('expires')),
    ];
    return [$data, $enabled ? '¡Listo! El aviso ya se ve en la web.' : '¡Listo! Aviso guardado (está desactivado).'];
}
