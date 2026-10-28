<?php
/**
 * Instalación: crea la contraseña del panel. Funciona una sola vez (mientras
 * no exista content/auth.json) y pide el SETUP_CODE de admin/config.php.
 */

declare(strict_types=1);

require __DIR__ . '/inc/boot.php';

if (auth_exists()) {
    redirect('/admin/login.php');
}

$codeReady = SETUP_CODE !== '' && SETUP_CODE !== 'CAMBIAR-ESTE-CODIGO';
$error = '';

if ($codeReady && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $code = (string) ($_POST['code'] ?? '');
    $pass = (string) ($_POST['password'] ?? '');
    $wait = login_locked_for();
    if ($wait > 0) {
        $error = 'Demasiados intentos. Esperá ' . (int) ceil($wait / 60) . ' minutos.';
    } elseif (!hash_equals(SETUP_CODE, $code)) {
        login_register_fail();
        $error = 'El código de instalación no es correcto.';
    } elseif ($pass !== (string) ($_POST['password2'] ?? '')) {
        $error = 'Las dos contraseñas no coinciden.';
    } elseif ($problem = password_problem($pass)) {
        $error = $problem;
    } else {
        try {
            auth_set_password($pass);
            login_clear_fails();
            login($pass);
            flash('ok', '¡Listo! Contraseña creada. Ya podés editar la web.');
            redirect('/admin/');
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
}

admin_header('Instalación', '', false);
?>
<div class="auth">
  <img class="auth__logo" src="/assets/img/logo-completo-oscuro-480.webp" alt="Clínica Veterinaria La Guardia" width="180" height="154">
  <h1 class="page-title">Crear contraseña del panel</h1>
  <?php if (!$codeReady): ?>
    <div class="flash flash--error" role="alert">
      Falta configurar el código de instalación. Editá <code>admin/config.php</code>, cambiá
      <code>SETUP_CODE</code> por un código propio y volvé a esta página.
    </div>
  <?php else: ?>
    <?php if ($error !== ''): ?><div class="flash flash--error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form class="form" method="post" action="/admin/setup.php">
      <?= csrf_field() ?>
      <?= field_text('code', '', 'Código de instalación', ['type' => 'password', 'required' => true, 'hint' => 'Es el SETUP_CODE de admin/config.php.']) ?>
      <?= field_text('password', '', 'Contraseña nueva', ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password', 'hint' => 'Mínimo 10 caracteres. Mejor una frase fácil de recordar.']) ?>
      <?= field_text('password2', '', 'Repetí la contraseña', ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password']) ?>
      <button class="btn btn--primary btn--block" type="submit">Crear contraseña</button>
    </form>
  <?php endif; ?>
</div>
<?php
admin_footer(false);
