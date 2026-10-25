/**
 * La Guardia · capa de motion (GSAP 3 + ScrollTrigger + SplitText + Lenis).
 * Hero Concepto A "La cruz se abre": coreografía del canvas de diseño.
 *
 * Reglas: solo transform y opacity; el HTML ya llegó completo del servidor y
 * esto se monta encima. Con prefers-reduced-motion no corre nada (todo queda
 * estático, sin pin ni smooth scroll).
 */
(() => {
  'use strict';

  const root = document.documentElement;
  const release = () => root.classList.remove('motion-pending');
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) {
    release();
    return;
  }

  const isDesktop = matchMedia('(min-width: 1024px)').matches;
  const finePointer = matchMedia('(hover: hover) and (pointer: fine)').matches;
  window.LGMotion = { lenis: null };

  /* Librerías bajo demanda: en desktop GSAP + SplitText antes de la entrada; en
     mobile GSAP recién con el primer scroll; ScrollTrigger + Lenis siempre con el
     primer scroll. Cargarlas de entrada bloqueaba el hilo principal en celulares. */
  const loaded = {};
  const load = (file) => loaded[file] || (loaded[file] = new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = '/assets/vendor/' + file;
    s.async = false; // respeta el orden: gsap antes que sus plugins
    s.onload = resolve;
    s.onerror = reject;
    document.head.appendChild(s);
  }));
  const GSAP = 'gsap-3.13.0.min.js';
  let gsap = null;

  /* ---- Smooth scroll ----------------------------------------------------- */
  // Solo con mouse: en pantallas táctiles el scroll nativo ya es suave y Lenis
  // solo sumaría trabajo al hilo principal.
  const setupLenis = () => {
    const { Lenis, ScrollTrigger } = window;
    if (!Lenis || !finePointer) return;
    const lenis = new Lenis({ autoRaf: false, lerp: 0.1, anchors: { offset: -96 } });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);
    window.LGMotion.lenis = lenis;

    // Con el mini formulario abierto, el fondo no scrollea.
    const dialog = document.querySelector('[data-wa-dialog]');
    if (dialog) {
      new MutationObserver(() => (dialog.open ? lenis.stop() : lenis.start()))
        .observe(dialog, { attributes: true, attributeFilter: ['open'] });
    }
  };

  /* ---- Hero -------------------------------------------------------------- */
  const hero = document.querySelector('[data-hero]');
  if (!hero || hero.dataset.variant !== 'A') {
    release();
    return;
  }

  const one = (sel) => hero.querySelector(sel);
  const all = (sel) => Array.from(hero.querySelectorAll(sel));

  const el = {
    headerInner: document.querySelector('.site-header__inner'),
    glow: one('[data-hero-glow]'),
    title: one('[data-hero-title]'),
    copy: one('[data-hero-copy]'),
    fades: all('[data-hero-fade]'),
    cross: one('[data-hero-cross]'),
    arm: Object.fromEntries(all('[data-arm]').map((a) => [a.dataset.arm, a])),
    media: one('[data-hero-media]'),
    fill: one('[data-hero-fill]'),
    chips: all('[data-hero-chip]'),
    chipPos: all('[data-hero-chip-pos]'),
    flood: one('[data-hero-flood]'),
  };
  const mediaInner = el.media.firstElementChild;

  /* 1 · Entrada (≈1,5 s) ---------------------------------------------------- */
  const playIntro = () => {
    // Solo desktop: en mobile el hero ya se ve completo desde el HTML.
    const k = 1;
    // Los brazos son <rect> del SVG: x/y van en unidades del viewBox (120), no en px.
    const unit = 120 / (el.cross.offsetWidth || 120);
    const vw = innerWidth * unit;
    const vh = innerHeight * unit;

    // Mobile: el titular ya está visible desde el HTML (es el LCP), no se parte en palabras.
    const split = isDesktop && window.SplitText
      ? window.SplitText.create(el.title,{ type: 'lines,words', mask: 'lines', linesClass: 'split-line', wordsClass: 'split-word' })
      : { words: [], revert: () => {} };

    if (split.words.length) {
      gsap.set(el.headerInner, { autoAlpha: 0, y: -20 });
      gsap.set(split.words, { yPercent: 110, rotate: 4 });
    }
    gsap.set(el.glow, { autoAlpha: 0 });
    gsap.set(el.fades, { autoAlpha: 0, y: 16 });
    gsap.set(el.chips, { autoAlpha: 0, scale: 0.6 });
    gsap.set(el.media, { autoAlpha: 0 });
    gsap.set(el.arm.top, { y: -vh });
    gsap.set(el.arm.bottom, { y: vh });
    gsap.set(el.arm.left, { x: -vw });
    gsap.set(el.arm.right, { x: vw });
    gsap.set(el.arm.center, { scale: 0, transformOrigin: '50% 50%' });
    release();

    const arm = { duration: 1 * k, ease: 'expo.out' };

    const tl = window.LGMotion.intro = gsap.timeline({
      defaults: { ease: 'power3.out' },
      onComplete: () => {
        split.revert(); // devuelve el titular original: responsive y limpio
        startIdle();
        // Desktop con mouse: Lenis tiene que estar listo antes del primer scroll.
        if (isDesktop && finePointer) {
          (window.requestIdleCallback || ((fn) => setTimeout(fn, 200)))(buildScrollOnce);
        }
      },
    });
    if (split.words.length) {
      tl.to(el.headerInner, { autoAlpha: 1, y: 0, duration: 0.4, ease: 'power2.out' }, 0)
        .to(split.words, { yPercent: 0, rotate: 0, duration: 0.8, stagger: 0.06, ease: 'power4.out' }, 0.15);
    }
    tl.to(el.glow, { autoAlpha: 1, duration: 0.6, ease: 'none' }, 0)
      .to(el.arm.top, { y: 0, ...arm }, 0)
      .to(el.arm.bottom, { y: 0, ...arm }, 0.08)
      .to(el.arm.left, { x: 0, ...arm }, 0.16)
      .to(el.arm.right, { x: 0, ...arm }, 0.24)
      .to(el.arm.center, { scale: 1, duration: 0.5 * k, ease: 'expo.out' }, 0.4 * k)
      .to(el.media, { autoAlpha: 1, duration: 0.4, ease: 'none' }, 0.55 * k)
      .fromTo(mediaInner, { scale: 1.25 }, { scale: 1, duration: 0.7 }, 0.55 * k)
      .to(el.fades, { autoAlpha: 1, y: 0, duration: 0.45, stagger: 0.08 }, 0.7 * k)
      .to(el.chips, { autoAlpha: 1, scale: 1, duration: 0.6, stagger: 0.1, ease: 'back.out(1.7)' }, 1 * k);
  };

  /* 2 · Reposo: brillo que sigue al cursor y chips en parallax ------------- */
  const startIdle = () => {
    loadVideo();
    if (!finePointer || !isDesktop) return;

    const glowX = gsap.quickTo(el.glow, 'x', { duration: 1.2, ease: 'power3' });
    const glowY = gsap.quickTo(el.glow, 'y', { duration: 1.2, ease: 'power3' });
    const chipMoves = el.chips.map((chip) => ({
      depth: parseFloat(chip.dataset.depth) || 0.02,
      x: gsap.quickTo(chip, 'x', { duration: 0.8, ease: 'power3' }),
      y: gsap.quickTo(chip, 'y', { duration: 0.8, ease: 'power3' }),
    }));

    hero.addEventListener('pointermove', (e) => {
      const r = hero.getBoundingClientRect();
      const anchorX = r.width * 0.72; // mismo punto que el CSS del brillo
      const anchorY = r.height * 0.48;
      glowX((e.clientX - r.left - anchorX) * 0.6);
      glowY((e.clientY - r.top - anchorY) * 0.6);

      const nx = (e.clientX - r.left) / r.width - 0.5;
      const ny = (e.clientY - r.top) / r.height - 0.5;
      chipMoves.forEach((m) => {
        m.x(nx * m.depth * 1200);
        m.y(ny * m.depth * 1200);
      });
    });
    hero.addEventListener('pointerleave', () => {
      glowX(0);
      glowY(0);
      chipMoves.forEach((m) => { m.x(0); m.y(0); });
    });
    // El CTA magnético vive en app.js (también lo usan las páginas de servicio).
  };

  /* Video solo en desktop y recién después de la entrada (el poster es el LCP). */
  const loadVideo = () => {
    const src = el.media.dataset.video;
    if (!src || !isDesktop) return;
    const video = Object.assign(document.createElement('video'), {
      src, muted: true, loop: true, playsInline: true, autoplay: true, preload: 'auto',
    });
    video.className = 'hero__img';
    video.setAttribute('aria-hidden', 'true');
    video.addEventListener('loadeddata', () => {
      el.media.firstElementChild.replaceWith(video);
      video.play().catch(() => {});
    }, { once: true });
  };

  /* 3 · Scroll: pin, la cruz viaja al centro, crece e inunda de magenta ----- */
  const buildScroll = () => {
    // Posición de la cruz dentro del hero, sin transforms (offsets de layout).
    const crossBox = () => {
      let x = 0;
      let y = 0;
      for (let n = el.cross; n && n !== hero; n = n.offsetParent) {
        x += n.offsetLeft;
        y += n.offsetTop;
      }
      return { x, y, w: el.cross.offsetWidth, h: el.cross.offsetHeight };
    };
    // Top del hero en pantalla mientras está pineado (start: bottom bottom, mínimo 0).
    const pinnedTop = () => {
      const box = hero.parentElement.classList.contains('pin-spacer') ? hero.parentElement : hero;
      const docTop = box.getBoundingClientRect().top + scrollY;
      const start = Math.max(0, docTop + hero.offsetHeight - innerHeight);
      return docTop - start;
    };
    const target = () => {
      const c = crossBox();
      return {
        dx: innerWidth / 2 - (c.x + c.w / 2),
        dy: innerHeight / 2 - pinnedTop() - (c.y + c.h / 2),
        // El cuadrado central (1/3 del ancho) tiene que tapar todo el viewport.
        scale: (Math.max(innerWidth, innerHeight) * 3) / c.w * 1.15,
      };
    };
    const chipAway = (i, chipEl, axis) => {
      const c = crossBox();
      const cx = c.x + c.w / 2;
      const cy = c.y + c.h / 2;
      let px = 0;
      let py = 0;
      for (let n = chipEl; n && n !== hero; n = n.offsetParent) {
        px += n.offsetLeft;
        py += n.offsetTop;
      }
      const vx = px + chipEl.offsetWidth / 2 - cx;
      const vy = py + chipEl.offsetHeight / 2 - cy;
      const len = Math.hypot(vx, vy) || 1;
      return ((axis === 'x' ? vx : vy) / len) * 320;
    };

    gsap.timeline({
      defaults: { ease: 'none' },
      scrollTrigger: {
        trigger: hero,
        start: 'clamp(bottom bottom)',
        end: isDesktop ? '+=100%' : '+=60%',
        pin: true,
        // Sin espaciador: marquee y Servicios (magenta) suben por encima del hero
        // mientras la cruz crece, así no queda una pantalla magenta vacía.
        pinSpacing: false,
        scrub: 0.6,
        anticipatePin: 1,
        invalidateOnRefresh: true,
      },
    })
      .to(el.copy, { y: -60, autoAlpha: 0, duration: 0.3 }, 0)
      .to(el.chipPos, {
        x: (i, t) => chipAway(i, t, 'x'),
        y: (i, t) => chipAway(i, t, 'y'),
        autoAlpha: 0,
        duration: 0.4,
      }, 0)
      .to(el.cross, { x: () => target().dx, y: () => target().dy, duration: 0.35, ease: 'power1.inOut' }, 0.05)
      .to(el.cross, { scale: () => target().scale, duration: 0.75, ease: 'power2.in' }, 0.1)
      // Desde ~scale 3 la foto se funde a magenta: no se rasteriza a 8×.
      .to(el.fill, { opacity: 1, duration: 0.2 }, 0.3)
      .to(el.flood, { opacity: 1, duration: 0.12 }, 0.75)
      .set({}, {}, 1);
  };

  /* Arranque: esperar fuentes (métricas correctas para SplitText), con tope. */
  const fontsReady = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
  // El pin del scroll mide todo el layout (tarea larga en celulares): no se arma
  // durante la carga sino con la primera intención de scrollear.
  const triggers = ['scroll', 'wheel', 'touchstart', 'keydown', 'pointerdown'];
  let scrollBuilt = false;
  function buildScrollOnce() {
    if (scrollBuilt) return;
    scrollBuilt = true;
    triggers.forEach((t) => removeEventListener(t, buildScrollOnce));
    Promise.all([
      load(GSAP),
      load('ScrollTrigger-3.13.0.min.js'),
      finePointer ? load('lenis-1.3.4.min.js').catch(() => {}) : null,
    ]).then(() => {
      gsap = window.gsap;
      gsap.registerPlugin(window.ScrollTrigger);
      setupLenis();
      buildScroll();
    }).catch(() => {}); // sin ScrollTrigger la página scrollea normal, sin pin
  }
  triggers.forEach((t) => addEventListener(t, buildScrollOnce, { passive: true }));
  if (scrollY > 0) buildScrollOnce(); // recarga con la página ya scrolleada

  // La entrada animada es solo de desktop.
  if (isDesktop) {
    Promise.all([
      Promise.race([fontsReady, new Promise((r) => setTimeout(r, 800))]),
      load(GSAP),
      load('SplitText-3.13.0.min.js').catch(() => {}),
    ]).then(() => {
      gsap = window.gsap;
      if (window.SplitText) gsap.registerPlugin(window.SplitText);
      playIntro();
    }).catch(release); // sin GSAP, el hero queda visible y estático
  }
})();
