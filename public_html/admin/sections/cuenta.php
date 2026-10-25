<?php
declare(strict_types=1);

function section_render(array $data): void
{
    echo form_open('cuenta');
    ?>
    <div class="panel">
      <h2 class="panel__title">Cambiar contraseña</h2>
      <?= field_text('current', '', 'Contraseña actual', ['type' => 'password', 'required' => true, 'autocomplete' => 'current-password']) ?>
      <?= field_text('password', '', 'Contraseña nueva', ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password', 'hint' => 'Mínimo 10 caracteres.']) ?>
      <?= field_text('password2', '', 'Repetí la contraseña nueva', ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password']) ?>
    </div>
    <?php
    echo form_close('Cambiar contraseña');
}

function section_save(array $data): array
{
    $current = (string) posted('current');
    $new = (string) posted('password');
    if (!password_verify($current, auth_hash())) {
        throw new RuntimeException('La contraseña actual no es correcta.');
    }
    if ($new !== (string) posted('password2')) {
        throw new RuntimeException('Las dos contraseñas nuevas no coinciden.');
    }
    if ($problem = password_problem($new)) {
        throw new RuntimeException($problem);
    }
    auth_set_password($new);
    session_regenerate_id(true);
    return [null, '¡Listo! Contraseña cambiada.'];
}
