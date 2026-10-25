<?php
declare(strict_types=1);

function section_render(array $data): void
{
    $patients = (array) ($data['patients'] ?? []);
    echo form_open('pacientitos', true);
    ?>
    <label class="dropzone" for="new-photos">
      <strong>+ Subir fotos</strong>
      <span>Podés elegir varias a la vez. JPG, PNG o WEBP de hasta 5 MB. Las achicamos solas.</span>
      <span class="dropzone__list" id="new-photos-list" aria-live="polite"></span>
    </label>
    <input class="visually-hidden" id="new-photos" type="file" name="new_photos[]" accept="image/jpeg,image/png,image/webp" multiple data-dropzone="new-photos-list">

    <?php if (!$patients): ?>
      <p class="panel__help">Todavía no hay fotos. Mientras tanto la web muestra recuadros de ejemplo.</p>
    <?php else: ?>
      <p class="panel__help">El nombre es opcional y aparece sobre la foto. Ordená con ← →.</p>
    <?php endif; ?>

    <div class="rows gallery-admin" id="patient-rows">
      <?php foreach ($patients as $i => $p): $key = 'k' . $i; ?>
        <div class="row">
          <div class="thumb"><img src="<?= e($p['image'] ?? '') ?>" alt="<?= e($p['alt'] ?? '') ?>" loading="lazy"></div>
          <input type="hidden" name="patients[<?= $key ?>][image]" value="<?= e($p['image'] ?? '') ?>">
          <?= field_text("patients[{$key}][name]", $p['name'] ?? '', 'Nombre', ['maxlength' => 30, 'placeholder' => 'Ej.: Rocco']) ?>
          <div class="row__tools">
            <button type="button" class="icon-btn" data-row-up aria-label="Mover antes">←</button>
            <button type="button" class="icon-btn" data-row-down aria-label="Mover después">→</button>
            <button type="button" class="icon-btn icon-btn--danger" data-row-remove aria-label="Borrar foto">✕</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php
    echo form_close();
}

function section_save(array $data): array
{
    $patients = [];
    foreach (posted_rows('patients') as $row) {
        $image = clean_line($row['image'] ?? '', 200);
        if (!str_starts_with($image, '/uploads/')) {
            continue;
        }
        $name = clean_line($row['name'] ?? '', 30);
        $patients[] = [
            'image' => $image,
            'name' => $name,
            'alt' => $name !== '' ? $name . ', pacientito de La Guardia' : 'Pacientito de La Guardia',
        ];
    }

    $added = 0;
    foreach (uploaded_files('new_photos') as $file) {
        if (!upload_given($file)) {
            continue;
        }
        try {
            $patients[] = ['image' => process_image($file), 'name' => '', 'alt' => 'Pacientito de La Guardia'];
            $added++;
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        }
    }

    $data['patients'] = $patients;
    $msg = $added ? "¡Listo! Subimos {$added} foto" . ($added > 1 ? 's' : '') . '.' : '¡Listo! Galería actualizada.';
    return [$data, $msg];
}
