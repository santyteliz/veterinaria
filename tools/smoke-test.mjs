#!/usr/bin/env node
/**
 * Prueba automática del sitio (local o ya publicado).
 *
 *   node tools/smoke-test.mjs http://localhost:8000
 *   node tools/smoke-test.mjs https://clinicalaguardia.com.ar
 *
 * Revisa: páginas y códigos HTTP, SEO básico (title, description, H1, canonical,
 * Open Graph, JSON-LD), links de WhatsApp y teléfono, sitemap y robots, carpetas
 * protegidas, panel con noindex, y (en el sitio publicado) HTTPS, compresión,
 * cache y cabeceras de seguridad. Necesita Node 18+ (usa fetch). No modifica nada.
 */

const base = (process.argv[2] || 'http://localhost:8000').replace(/\/$/, '');
const CLINIC_WA = process.env.CLINIC_WA || '5493424860753';
const VISIT_WA = process.env.VISIT_WA || '5493426131922';
const isLive = base.startsWith('https://');

const results = [];
const pass = (area, msg) => results.push(['OK', area, msg]);
const fail = (area, msg) => results.push(['FALLA', area, msg]);
const warn = (area, msg) => results.push(['AVISO', area, msg]);

const get = async (path, opts = {}) => {
  const res = await fetch(base + path, { redirect: 'manual', headers: { 'Accept-Encoding': 'br, gzip', 'User-Agent': 'LaGuardia-SmokeTest/1.0' }, ...opts });
  const body = opts.method === 'HEAD' ? '' : await res.text();
  return { res, body };
};

const attr = (html, re) => (html.match(re) || [])[1] || '';
const all = (html, re) => [...html.matchAll(re)].map((m) => m[1]);
const decode = (s) => s.replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&#0?39;/g, "'");

async function checkPage(path, { expect404 = false } = {}) {
  const area = path === '/' ? 'Home' : path;
  const { res, body } = await get(path);
  if (res.status !== (expect404 ? 404 : 200)) {
    fail(area, `HTTP ${res.status} (esperado ${expect404 ? 404 : 200})`);
    return null;
  }
  pass(area, `HTTP ${res.status}`);

  if (!/<html lang="es-AR">/.test(body)) fail(area, 'falta lang="es-AR"');
  const h1s = (body.match(/<h1[\s>]/g) || []).length;
  h1s === 1 ? pass(area, 'un solo H1') : fail(area, `${h1s} H1 (tiene que haber 1)`);

  const title = attr(body, /<title>([^<]*)<\/title>/);
  const desc = decode(attr(body, /<meta name="description" content="([^"]*)"/));
  title ? pass(area, `title: “${decode(title)}” (${decode(title).length} car.)`) : fail(area, 'sin <title>');
  if (!desc) fail(area, 'sin meta description');
  else if (desc.length > 165) warn(area, `description larga (${desc.length} car.)`);

  if (expect404) {
    /noindex/.test(body) ? pass(area, 'noindex en la 404') : fail(area, 'la 404 debería tener noindex');
    return body;
  }

  const canonical = attr(body, /<link rel="canonical" href="([^"]+)"/);
  canonical ? pass(area, `canonical ${canonical}`) : fail(area, 'sin canonical');
  if (!/property="og:image" content="[^"]+og-la-guardia\.jpg"/.test(body)) fail(area, 'sin og:image');

  for (const json of all(body, /<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)) {
    try {
      const data = JSON.parse(json);
      if (json.includes('A CONFIRMAR')) fail(area, `JSON-LD ${data['@type']} publica un “[A CONFIRMAR]”`);
      else pass(area, `JSON-LD ${data['@type']} válido`);
    } catch {
      fail(area, 'JSON-LD inválido');
    }
  }

  const tbc = (body.match(/\[A CONFIRMAR/g) || []).length;
  if (tbc) warn(area, `${tbc} marcador(es) [A CONFIRMAR] visibles: completar antes de lanzar`);
  return body;
}

function checkContactLinks(area, html) {
  const wa = all(html, /href="(https:\/\/wa\.me\/[^"]+)"/g).map(decode);
  if (!wa.length) return fail(area, 'no hay links de WhatsApp');
  let bad = 0;
  for (const href of wa) {
    const url = new URL(href);
    const number = url.pathname.slice(1);
    const text = url.searchParams.get('text') || '';
    const isVisit = /domicilio/i.test(text);
    const expected = isVisit ? VISIT_WA : CLINIC_WA;
    if (number !== expected) { bad++; fail(area, `WhatsApp a ${number} (esperado ${expected}) · “${text.slice(0, 50)}…”`); }
    if (!text.startsWith('¡Hola')) { bad++; fail(area, `mensaje de WhatsApp sin saludo: “${text.slice(0, 50)}”`); }
  }
  if (!bad) pass(area, `${wa.length} links de WhatsApp con número y mensaje correctos`);

  const tels = all(html, /href="tel:([^"]+)"/g);
  const badTel = tels.filter((t) => !/^\+54\d{10}$/.test(t));
  badTel.length ? fail(area, `teléfonos mal formados: ${badTel.join(', ')}`) : pass(area, `${tels.length} links tel: correctos`);
}

async function checkAssets(html) {
  const urls = new Set([
    ...all(html, /<link[^>]+href="(\/assets\/[^"]+)"/g),
    ...all(html, /<script[^>]+src="(\/assets\/[^"]+)"/g),
    ...all(html, /<img[^>]+src="(\/[^"]+)"/g),
  ].map(decode));
  let broken = 0;
  for (const u of urls) {
    const { res } = await get(u, { method: 'HEAD' });
    if (res.status !== 200) { broken++; fail('Archivos', `${u} → HTTP ${res.status}`); }
  }
  if (!broken) pass('Archivos', `${urls.size} CSS/JS/imágenes de la home responden 200`);

  // Librerías que motion.js carga bajo demanda.
  for (const lib of ['gsap-3.13.0.min.js', 'ScrollTrigger-3.13.0.min.js', 'SplitText-3.13.0.min.js', 'lenis-1.3.4.min.js']) {
    const { res } = await get('/assets/vendor/' + lib, { method: 'HEAD' });
    res.status === 200 ? null : fail('Archivos', `/assets/vendor/${lib} → HTTP ${res.status}`);
  }
}

async function checkProtected() {
  const paths = ['/content/site.json', '/content/auth.json', '/content/backups/', '/inc/bootstrap.php', '/admin/config.php', '/admin/inc/auth.php', '/admin/sections/aviso.php'];
  for (const p of paths) {
    const { res, body } = await get(p);
    const leaked = res.status === 200 && (body.includes('"contact"') || body.includes('<?php') || body.includes('"hash"'));
    if (res.status === 403 || res.status === 404 || (res.status === 200 && !leaked && body.length < 50)) pass('Seguridad', `${p} bloqueado (HTTP ${res.status})`);
    else fail('Seguridad', `${p} accesible (HTTP ${res.status})`);
  }
  const { res } = await get('/admin/');
  const loc = res.headers.get('location') || '';
  [301, 302, 303].includes(res.status) && /login|setup/.test(loc) ? pass('Panel', `/admin/ pide ingresar (→ ${loc})`) : fail('Panel', `/admin/ respondió HTTP ${res.status}`);
  const login = await get('/admin/login.php');
  const xr = login.res.headers.get('x-robots-tag') || '';
  if (login.res.status === 200 || [301, 302, 303].includes(login.res.status)) {
    /noindex/.test(xr) ? pass('Panel', 'el panel envía X-Robots-Tag: noindex') : fail('Panel', 'el panel no envía noindex');
  }
}

async function checkSeoFiles(servicePaths) {
  const sm = await get('/sitemap.xml');
  if (sm.res.status !== 200) return fail('SEO', `sitemap.xml HTTP ${sm.res.status}`);
  const locs = all(sm.body, /<loc>([^<]+)<\/loc>/g);
  pass('SEO', `sitemap.xml con ${locs.length} URLs`);
  for (const p of servicePaths) if (!locs.some((l) => l.endsWith(p))) fail('SEO', `${p} no está en el sitemap`);

  const rb = await get('/robots.txt');
  rb.res.status === 200 && /Disallow: \/admin\//.test(rb.body) && /Sitemap: /.test(rb.body)
    ? pass('SEO', 'robots.txt bloquea /admin/ y apunta al sitemap')
    : fail('SEO', 'robots.txt incompleto');
  return locs;
}

async function checkLiveServer() {
  // HTTPS forzado.
  const http = base.replace('https://', 'http://');
  try {
    const r = await fetch(http + '/', { redirect: 'manual' });
    const loc = r.headers.get('location') || '';
    [301, 308].includes(r.status) && loc.startsWith('https://') ? pass('Servidor', 'http:// redirige a https://') : fail('Servidor', `http:// respondió ${r.status} sin redirigir a HTTPS`);
  } catch { warn('Servidor', 'no se pudo probar http:// → https://'); }

  const { res } = await get('/');
  /max-age/.test(res.headers.get('strict-transport-security') || '') ? pass('Servidor', 'HSTS activo') : warn('Servidor', 'sin cabecera HSTS');
  res.headers.get('x-content-type-options') === 'nosniff' ? pass('Servidor', 'X-Content-Type-Options: nosniff') : warn('Servidor', 'falta X-Content-Type-Options');

  const css = await get('/assets/css/app.css');
  const enc = css.res.headers.get('content-encoding') || '';
  /br|gzip/.test(enc) ? pass('Servidor', `CSS comprimido (${enc})`) : fail('Servidor', 'CSS sin compresión (revisar .htaccess)');
  /immutable|max-age=31536000/.test(css.res.headers.get('cache-control') || '') ? pass('Servidor', 'CSS con cache de 1 año') : warn('Servidor', 'CSS sin cache largo');
}

(async () => {
  console.log(`\nProbando ${base}\n`);
  let home;
  try {
    home = await checkPage('/');
  } catch (e) {
    console.error(`No se pudo conectar con ${base}: ${e.message}`);
    process.exit(2);
  }
  if (home) {
    checkContactLinks('Home', home);
    await checkAssets(home);
  }

  const sm = await get('/sitemap.xml');
  const servicePaths = all(sm.body, /<loc>[^<]+?(\/[a-z0-9-]+)<\/loc>/g).filter((p) => p !== '/politica-de-privacidad');
  for (const p of [...servicePaths, '/politica-de-privacidad']) {
    const html = await checkPage(p);
    if (html && p !== '/politica-de-privacidad') checkContactLinks(p, html);
  }
  await checkPage('/esta-pagina-no-existe', { expect404: true });
  await checkSeoFiles(servicePaths);
  await checkProtected();
  if (isLive) await checkLiveServer();
  else warn('Servidor', 'HTTPS, compresión y cache se prueban contra el sitio publicado (https://…)');

  const pad = (s, n) => (s + ' '.repeat(n)).slice(0, n);
  for (const [status, area, msg] of results) console.log(`${pad(status, 6)} ${pad(area, 38)} ${msg}`);
  const fails = results.filter((r) => r[0] === 'FALLA').length;
  const warns = results.filter((r) => r[0] === 'AVISO').length;
  console.log(`\n${results.length - fails - warns} OK · ${warns} avisos · ${fails} fallas\n`);
  process.exit(fails ? 1 : 0);
})();
