<?php
/**
 * Panel de administración · router de secciones.
 * Cada archivo de sections/ define section_render($data) y section_save($data).
 * section_save devuelve [contenido nuevo o null si no hay que guardar, mensaje].
 */

declare(strict_types=1);

require __DIR__ . '/inc/boot.php';
require_login();

$s = (string) ($_GET['s'] ?? '');
if ($s !== '' && !isset(ADMIN_SECTIONS[$s])) {
    $s = '';
}

if ($s === '') {
    $data = store_load();
    $status = open_status();
    $backups = backups_list();
    admin_header('Inicio');
    ?>
    <h1 class="page-title">¡Hola! ¿Qué querés cambiar?</h1>
    <ul class="status-list" role="list">
      <li><span class="dot <?= $status['open'] ? 'dot--on' : '' ?>"></span><span>La web dice: <strong><?= e($status['label']) ?></strong></span></li>
      <li><span class="dot <?= !empty($data['announcement']['enabled']) ? 'dot--on' : '' ?>"></span><span>Aviso superior: <strong><?= !empty($data['announcement']['enabled']) ? 'activado' : 'desactivado' ?></strong></span></li>
      <li><span class="dot"></span><span>Último guardado: <strong><?= e($backups[0]['date'] ?? 'todavía no hay') ?></strong></span></li>
    </ul>
    <ul class="cards" role="list">
      <?php foreach (ADMIN_SECTIONS as $key => [$label, $desc]): ?>
        <li><a class="card-link" href="/admin/?s=<?= e($key) ?>"><strong><?= e($label) ?></strong><span><?= e($desc) ?></span></a></li>
      <?php endforeach; ?>
    </ul>
    <?php
    admin_footer();
    exit;
}

require __DIR__ . '/sections/' . $s . '.php';
[$title, $desc] = ADMIN_SECTIONS[$s];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        [$new, $message] = section_save(store_load());
        if ($new !== null) {
            store_save($new, $title);
        }
        uploads_gc();
        flash('ok', $message);
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
    }
    redirect('/admin/?s=' . $s);
}

admin_header($title, $s);
?>
<div class="page-head">
  <h1 class="page-title"><?= e($title) ?></h1>
  <p class="page-desc"><?= e($desc) ?></p>
</div>
<?php
section_render(store_load());
admin_footer();
