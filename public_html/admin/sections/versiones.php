<?php
declare(strict_types=1);

function section_render(array $data): void
{
    $backups = backups_list();
    ?>
    <p class="panel__help">Cada vez que guardás algo, se guarda una copia de cómo estaba antes (las últimas <?= BACKUPS_KEEP ?>). Si algo salió mal, volvé a una de estas versiones.</p>
    <?php if (!$backups): ?>
      <div class="flash flash--info">Todavía no hay versiones guardadas.</div>
    <?php else: ?>
      <ul class="versions" role="list">
        <?php foreach ($backups as $b): ?>
          <li class="version">
            <div>
              <strong><?= e($b['date']) ?></strong>
              <p class="version__label">Antes de cambiar: <?= e($b['label']) ?></p>
            </div>
            <form method="post" action="/admin/?s=versiones" data-confirm="¿Volver la web a como estaba el <?= e($b['date']) ?>? Lo que hay ahora queda guardado como otra versión.">
              <?= csrf_field() ?>
              <input type="hidden" name="file" value="<?= e($b['file']) ?>">
              <button class="btn btn--ghost btn--sm" type="submit">Restaurar</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif;
}

function section_save(array $data): array
{
    backup_restore(clean_line(posted('file'), 120));
    return [null, '¡Listo! La web volvió a la versión elegida.'];
}
