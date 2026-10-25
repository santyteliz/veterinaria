/**
 * La Guardia · interacciones base (sin dependencias).
 * La capa de animación (GSAP, Lenis, cursor) se suma en un archivo aparte.
 */
(() => {
  'use strict';

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const cfg = window.LG || {};

  /* ---- Analytics: un solo punto para WhatsApp, teléfono y "Cómo llegar" -- */
  const track = (event, label) => {
    const payload = { event_category: 'contacto', event_label: label };
    if (typeof window.gtag === 'function') window.gtag('event', event, payload);
    (window.dataLayer = window.dataLayer || []).push({ event: `click_${event}`, ...payload });
  };
  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-track]');
    if (el) track(el.dataset.track, el.dataset.trackLabel || '');
  });

  /* ---- Header compacto al scrollear ------------------------------------- */
  const header = $('[data-header]');
  if (header) {
    const sentinel = document.createElement('div');
    sentinel.style.cssText = 'position:absolute;top:0;height:1px;width:1px;';
    document.body.prepend(sentinel);
    new IntersectionObserver(([entry]) => {
      header.classList.toggle('is-compact', !entry.isIntersecting);
    }, { rootMargin: '80px 0px 0px 0px' }).observe(sentinel);
  }

  /* ---- Menú mobile ------------------------------------------------------- */
  const toggle = $('[data-nav-toggle]');
  const nav = $('[data-nav]');
  if (toggle && nav) {
    const setOpen = (open) => {
      toggle.setAttribute('aria-expanded', String(open));
      toggle.querySelector('.visually-hidden').textContent = open ? 'Cerrar menú' : 'Abrir menú';
      nav.classList.toggle('is-open', open);
    };
    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    nav.addEventListener('click', (e) => { if (e.target.closest('a')) setOpen(false); });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && nav.classList.contains('is-open')) { setOpen(false); toggle.focus(); }
    });
  }

  /* ---- Aviso superior (se cierra por sesión) ---------------------------- */
  const bar = $('[data-announcement]');
  if (bar) {
    const key = 'lg-announcement:' + bar.textContent.trim().slice(0, 80);
    try { if (sessionStorage.getItem(key)) bar.hidden = true; } catch (_) {}
    $('[data-announcement-close]', bar)?.addEventListener('click', () => {
      bar.hidden = true;
      try { sessionStorage.setItem(key, '1'); } catch (_) {}
    });
  }

  /* ---- Estado "Abierto ahora": se recalcula por si el HTML vino de caché - */
  const status = $('[data-open-status]');
  if (status) {
    const DAYS = ['', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
    let hours;
    try { hours = JSON.parse(status.dataset.hours); } catch (_) { hours = null; }

    const fmt = (hhmm) => {
      const [h, m] = hhmm.split(':').map(Number);
      return m ? `${h}:${String(m).padStart(2, '0')} h` : `${h} h`;
    };
    // Fecha y hora "de pared" en Argentina, sin depender del huso del visitante.
    // Argentina es UTC−3 todo el año (sin horario de verano): alcanza con restar
    // 3 h y leer los campos UTC. Es mucho más barato que Intl.DateTimeFormat.
    const pad = (n) => String(n).padStart(2, '0');
    const localParts = (date) => {
      const d = new Date(date.getTime() - 3 * 3600e3);
      return {
        ymd: `${d.getUTCFullYear()}-${pad(d.getUTCMonth() + 1)}-${pad(d.getUTCDate())}`,
        time: `${pad(d.getUTCHours())}:${pad(d.getUTCMinutes())}`,
        dow: d.getUTCDay() || 7,
      };
    };
    const rangesFor = ({ ymd, dow }) => {
      const special = (hours.special || []).find((s) => s.date === ymd);
      return special ? special.ranges || [] : hours.week[String(dow)] || [];
    };
    const compute = () => {
      const now = new Date();
      const today = localParts(now);
      for (const [from, to] of rangesFor(today)) {
        if (today.time >= from && today.time < to) {
          return { open: true, label: `Abierto ahora · hasta las ${fmt(to)}`, short: 'Abierto' };
        }
      }
      for (let i = 0; i <= 7; i++) {
        const day = i === 0 ? today : localParts(new Date(now.getTime() + i * 864e5));
        for (const [from] of rangesFor(day)) {
          if (i === 0 && from <= today.time) continue;
          const when = i === 0 ? 'abre a las ' : i === 1 ? 'abre mañana a las ' : `abre el ${DAYS[day.dow]} a las `;
          return { open: false, label: `Cerrado · ${when}${fmt(from)}`, short: 'Cerrado' };
        }
      }
      return { open: false, label: 'Cerrado', short: 'Cerrado' };
    };
    const render = () => {
      if (!hours) return;
      const s = compute();
      status.classList.toggle('is-open', s.open);
      status.classList.toggle('is-closed', !s.open);
      $('.status__label', status).textContent = s.label;
      $('.status__short', status).textContent = s.short;
    };
    // El HTML ya trae el estado correcto: se recalcula cuando el navegador está libre.
    (window.requestIdleCallback || ((fn) => setTimeout(fn, 1500)))(render);
    setInterval(render, 60_000);
  }

  /* ---- Antes / después --------------------------------------------------- */
  $$('[data-ba]').forEach((ba) => {
    const range = $('[data-ba-range]', ba);
    const frame = $('.ba__frame', ba);
    const update = () => frame.style.setProperty('--pos', `${range.value}%`);
    range.addEventListener('input', update);
    update();
  });

  /* ---- Mapa con carga diferida (facade) ---------------------------------- */
  $$('[data-map]').forEach((map) => {
    $('[data-map-load]', map)?.addEventListener('click', () => {
      const iframe = document.createElement('iframe');
      iframe.src = map.dataset.src;
      iframe.title = 'Mapa de ubicación de la clínica';
      iframe.loading = 'lazy';
      iframe.referrerPolicy = 'no-referrer-when-downgrade';
      iframe.allowFullscreen = true;
      $('.map__facade', map).replaceWith(iframe);
      track('map', 'ubicacion');
    }, { once: true });
  });

  /* ======================================================================
     Microinteracciones. Nada de esto corre con prefers-reduced-motion, y el
     cursor, el tilt y los botones magnéticos solo con mouse.
     ====================================================================== */
  const root = document.documentElement;
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const finePointer = matchMedia('(hover: hover) and (pointer: fine)').matches;

  /* ---- Revelado de secciones (clip-path vertical + fade) ----------------- */
  const countUp = (el) => {
    const to = parseFloat(el.dataset.countTo);
    const decimals = (el.dataset.countTo.split('.')[1] || '').length;
    const t0 = performance.now();
    const step = (t) => {
      const p = Math.min(1, (t - t0) / 1200);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = (to * eased).toFixed(decimals).replace('.', ',');
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };

  if (!reduceMotion && 'IntersectionObserver' in window) {
    const auto = '.section-head, .split__copy, .service, .shop__item, .emergency, .visit, .faq, .map, .ba, .prose > h2, .page-aside, .related li, .rating';
    $$(auto).forEach((el) => { if (!el.closest('.hero')) el.setAttribute('data-reveal', ''); });
    const items = $$('[data-reveal]');
    // Escalonado entre hermanos (cards de una grilla entran una detrás de otra).
    const perParent = new Map();
    items.forEach((el) => {
      const i = perParent.get(el.parentElement) || 0;
      perParent.set(el.parentElement, i + 1);
      el.style.setProperty('--reveal-i', String(Math.min(i, 6)));
    });
    root.classList.add('reveal-ready');
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-revealed');
        $$('[data-count-to]', entry.target).forEach(countUp);
        io.unobserve(entry.target);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0 });
    items.forEach((el) => io.observe(el));
    // Contadores fuera de bloques revelables (ej. puntaje del hero).
    $$('[data-count-to]').filter((el) => !el.closest('[data-reveal]')).forEach(countUp);
  }

  /* ---- Cards de servicio: toda la card consulta + tilt 3D sutil ---------- */
  $$('.service').forEach((card) => {
    const primary = card.querySelector('.btn');
    card.addEventListener('click', (e) => {
      if (primary && !e.target.closest('a, button')) primary.click();
    });
    if (!finePointer || reduceMotion) return;
    card.addEventListener('pointermove', (e) => {
      const r = card.getBoundingClientRect();
      const px = (e.clientX - r.left) / r.width - 0.5;
      const py = (e.clientY - r.top) / r.height - 0.5;
      card.style.transform = `perspective(900px) rotateX(${(-py * 6).toFixed(2)}deg) rotateY(${(px * 8).toFixed(2)}deg) translateY(-6px)`;
    });
    card.addEventListener('pointerleave', () => { card.style.transform = ''; });
  });

  /* ---- Botones magnéticos (CTAs principales, máx. 8 px) ------------------ */
  if (finePointer && !reduceMotion) {
    $$('[data-magnetic]').forEach((btn) => {
      btn.addEventListener('pointermove', (e) => {
        const r = btn.getBoundingClientRect();
        const clamp = (v) => Math.max(-8, Math.min(8, v));
        btn.style.translate = `${clamp((e.clientX - r.left - r.width / 2) * 0.3)}px ${clamp((e.clientY - r.top - r.height / 2) * 0.3)}px`;
      });
      btn.addEventListener('pointerleave', () => { btn.style.translate = ''; });
    });
  }

  /* ---- Cursor: punto magenta → cruz sobre links → "Consultar" sobre cards - */
  if (finePointer && !reduceMotion) {
    const cursor = document.createElement('div');
    cursor.className = 'cursor';
    cursor.setAttribute('aria-hidden', 'true');
    cursor.innerHTML = '<span class="cursor__dot"></span>'
      + '<svg class="cursor__cross" viewBox="0 0 120 120"><rect x="40" y="0" width="40" height="120" rx="10"/><rect x="0" y="40" width="120" height="40" rx="10"/></svg>'
      + '<span class="cursor__label">Consultar</span>';
    document.body.appendChild(cursor);
    root.classList.add('has-cursor');

    let x = -100, y = -100, tx = x, ty = y, raf = 0;
    const tick = () => {
      x += (tx - x) * 0.35;
      y += (ty - y) * 0.35;
      cursor.style.transform = `translate3d(${x.toFixed(1)}px, ${y.toFixed(1)}px, 0)`;
      raf = Math.abs(tx - x) + Math.abs(ty - y) > 0.2 ? requestAnimationFrame(tick) : 0;
    };
    addEventListener('pointermove', (e) => {
      if (e.pointerType !== 'mouse') return;
      tx = e.clientX;
      ty = e.clientY;
      const t = e.target instanceof Element ? e.target : null;
      cursor.dataset.state = !t ? ''
        : t.closest('input:not([type=range]), textarea, select, iframe') ? 'text'
        : t.closest('a, button, summary, label, [role="button"], input[type=range]') ? 'link'
        : t.closest('.service') ? 'card' : '';
      cursor.classList.add('is-active');
      if (!raf) raf = requestAnimationFrame(tick);
    }, { passive: true });
    document.addEventListener('pointerleave', () => cursor.classList.remove('is-active'));
    addEventListener('pointerdown', () => cursor.classList.add('is-pressed'));
    addEventListener('pointerup', () => cursor.classList.remove('is-pressed'));
  }

  /* ---- Easter egg: 5 clicks en el logo (en la home) y cruza un perrito ---- */
  const logo = $('[data-logo]');
  const dogTpl = $('#lg-dog');
  if (logo && dogTpl) {
    let clicks = 0;
    let timer = 0;
    logo.addEventListener('click', (e) => {
      e.preventDefault(); // en la home el logo no navega: sube arriba
      (window.LGMotion && window.LGMotion.lenis) ? window.LGMotion.lenis.scrollTo(0) : scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
      clicks += 1;
      clearTimeout(timer);
      timer = setTimeout(() => { clicks = 0; }, 2000);
      if (clicks < 5 || $('.dog-run')) return;
      clicks = 0;
      const dog = dogTpl.content.firstElementChild.cloneNode(true);
      if (reduceMotion) dog.classList.add('dog-run--still');
      document.body.appendChild(dog);
      dog.addEventListener('animationend', (ev) => { if (ev.target === dog) dog.remove(); });
      setTimeout(() => dog.remove(), 7000);
    });
  }

  /* ---- Mini formulario → WhatsApp (no guarda nada) ----------------------- */
  const dialog = $('[data-wa-dialog]');
  if (dialog && typeof dialog.showModal === 'function') {
    const form = $('[data-wa-form]', dialog);
    let opener = null;

    $$('[data-wa-open]').forEach((btn) => {
      btn.addEventListener('click', () => {
        opener = btn;
        dialog.returnValue = '';
        form.reset();
        $('[data-wa-service]', form).value = btn.dataset.service || '';
        $('[data-wa-number]', form).value = btn.dataset.number || '';
        $('[data-wa-service-label]', dialog).textContent = btn.dataset.service || '';
        dialog.showModal();
        $('#wa-pet', form).focus();
      });
    });

    dialog.addEventListener('close', () => {
      if (dialog.returnValue === 'send') {
        const data = new FormData(form);
        const fill = (v) => (String(v || '').trim() || '____');
        const species = String(data.get('species') || '').trim();
        const pet = fill(data.get('pet')) + (species ? ` (${species.toLowerCase()})` : '');
        const msg = `¡Hola La Guardia! Quiero consultar por: ${data.get('service')}. Mascota: ${pet} · Motivo: ${fill(data.get('reason'))}`;
        const number = String(data.get('number') || cfg.whatsapp || '').replace(/\D+/g, '');
        track('whatsapp', `form:${data.get('service')}`);
        window.open(`https://wa.me/${number}?text=${encodeURIComponent(msg)}`, '_blank', 'noopener');
      }
      opener?.focus();
    });
  }
})();
