<?php
declare(strict_types=1);

require __DIR__ . '/inc/boot.php';

if (!auth_exists()) {
    redirect('/admin/setup.php');
}
if (!empty($_SESSION['uid'])) {
    redirect('/admin/');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $wait = login_locked_for();
    if ($wait > 0) {
        $error = 'Demasiados intentos. Esperá ' . (int) ceil($wait / 60) . ' minutos y probá de nuevo.';
    } elseif (login((string) ($_POST['password'] ?? ''))) {
        login_clear_fails();
        redirect('/admin/');
    } else {
        login_register_fail();
        $wait = login_locked_for();
        $error = $wait > 0
            ? 'Demasiados intentos. Esperá ' . (int) ceil($wait / 60) . ' minutos y probá de nuevo.'
            : 'Contraseña incorrecta.';
    }
}

admin_header('Ingresar', '', false);
?>
<div class="auth">
  <img class="auth__logo" src="/assets/img/logo-completo-oscuro-480.webp" alt="Clínica Veterinaria La Guardia" width="180" height="154">
  <h1 class="page-title">Panel de la web</h1>
  <?php if (isset($_GET['expirada'])): ?>
    <div class="flash flash--info" role="status">Cerramos la sesión por inactividad. Ingresá de nuevo.</div>
  <?php endif; ?>
  <?php if ($error !== ''): ?>
    <div class="flash flash--error" role="alert"><?= e($error) ?></div>
  <?php endif; ?>
  <form class="form" method="post" action="/admin/login.php">
    <?= csrf_field() ?>
    <div class="field">
      <label for="password">Contraseña</label>
      <input id="password" name="password" type="password" autocomplete="current-password" required autofocus>
    </div>
    <button class="btn btn--primary btn--block" type="submit">Ingresar</button>
  </form>
</div>
<?php
admin_footer(false);
