/** Panel La Guardia · interacciones (sin dependencias). */
(() => {
  'use strict';

  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  /* ---- Contador de caracteres ------------------------------------------- */
  const counters = (root) => $$('[data-count]', root).forEach((input) => {
    if (input.dataset.countReady) return;
    input.dataset.countReady = '1';
    const max = Number(input.getAttribute('maxlength'));
    const out = document.createElement('p');
    out.className = 'counter';
    out.setAttribute('aria-live', 'polite');
    input.insertAdjacentElement('afterend', out);
    const update = () => {
      out.textContent = `${input.value.length} / ${max}`;
      out.classList.toggle('is-over', input.value.length >= max);
    };
    input.addEventListener('input', update);
    update();
  });

  /* ---- Vista previa de fotos -------------------------------------------- */
  const previews = (root) => $$('[data-photo-input]', root).forEach((input) => {
    if (input.dataset.previewReady) return;
    input.dataset.previewReady = '1';
    input.addEventListener('change', () => {
      const box = input.closest('.photo')?.querySelector('.photo__box');
      const file = input.files && input.files[0];
      if (!box || !file) return;
      const img = box.querySelector('[data-preview]');
      box.querySelector('[data-preview-empty]')?.remove();
      img.hidden = false;
      img.src = URL.createObjectURL(file);
      const pick = input.closest('.photo').querySelector('.photo__pick');
      if (pick) pick.textContent = 'Cambiar foto';
    });
  });

  /* ---- Repetidores: agregar, subir, bajar, borrar ------------------------ */
  document.addEventListener('click', (e) => {
    const add = e.target.closest('[data-row-add]');
    if (add) {
      const list = document.getElementById(add.dataset.rowAdd);
      const tpl = document.getElementById(add.dataset.rowAdd + '-template');
      if (!list || !tpl) return;
      const key = 'n' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
      const wrap = document.createElement('div');
      wrap.innerHTML = tpl.innerHTML.replaceAll('__key__', key);
      const row = wrap.firstElementChild;
      list.appendChild(row);
      counters(row);
      previews(row);
      markDirty();
      row.querySelector('input, textarea, select')?.focus();
      return;
    }

    const row = e.target.closest('.row');
    if (!row) return;
    if (e.target.closest('[data-row-up]') && row.previousElementSibling) {
      row.parentElement.insertBefore(row, row.previousElementSibling);
      e.target.closest('button').focus();
      markDirty();
    } else if (e.target.closest('[data-row-down]') && row.nextElementSibling) {
      row.parentElement.insertBefore(row.nextElementSibling, row);
      e.target.closest('button').focus();
      markDirty();
    } else if (e.target.closest('[data-row-remove]')) {
      const name = row.querySelector('.row__title')?.textContent.trim();
      if (confirm(name ? `¿Borrar “${name}”? Se aplica al guardar.` : '¿Borrar este elemento? Se aplica al guardar.')) {
        row.remove();
        markDirty();
      }
    }
  });

  // El título de la fila sigue al primer campo (así se entiende qué se borra).
  document.addEventListener('input', (e) => {
    const row = e.target.closest('.row');
    const title = row?.querySelector('.row__title');
    if (title && e.target.matches('[data-row-name]')) {
      title.textContent = e.target.value.trim() || title.dataset.empty || 'Nuevo';
    }
  });

  /* ---- Horarios: "cerrado" oculta los turnos ----------------------------- */
  const syncDay = (box) => box.closest('.day')?.classList.toggle('is-closed', box.checked);
  document.addEventListener('change', (e) => {
    if (e.target.matches('[data-day-closed]')) syncDay(e.target);
    if (e.target.matches('[data-visible-toggle]')) {
      e.target.closest('.row')?.classList.toggle('is-hidden-service', !e.target.checked);
    }
  });
  $$('[data-day-closed]').forEach(syncDay);

  /* ---- Subida múltiple: lista de archivos elegidos ----------------------- */
  $$('[data-dropzone]').forEach((input) => {
    const list = document.getElementById(input.dataset.dropzone);
    input.addEventListener('change', () => {
      const n = input.files.length;
      list.textContent = n ? `${n} foto${n > 1 ? 's' : ''} lista${n > 1 ? 's' : ''} para subir. Tocá “Guardar cambios”.` : '';
    });
  });

  /* ---- Confirmaciones ---------------------------------------------------- */
  $$('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (!confirm(form.dataset.confirm)) e.preventDefault();
    });
  });

  /* ---- Aviso si te vas sin guardar --------------------------------------- */
  let dirty = false;
  function markDirty() { dirty = true; }
  $$('[data-dirty-check]').forEach((form) => {
    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);
    form.addEventListener('submit', () => {
      dirty = false;
      const btn = form.querySelector('.savebar button');
      if (btn) { btn.disabled = true; btn.textContent = 'Guardando…'; }
    });
  });
  window.addEventListener('beforeunload', (e) => {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });

  counters(document);
  previews(document);
})();
