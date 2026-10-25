#!/usr/bin/env node
/**
 * Exporta la web pública como HTML estático en dist/, para publicar una vista
 * previa en Vercel (que no ejecuta PHP). El sitio real va en Hostinger con PHP.
 *
 *   node tools/export-static.mjs
 *   PHP_BIN="C:/ruta/php.exe" node tools/export-static.mjs
 *
 * Levanta el PHP local con dev-router.php, guarda cada página (home, páginas de
 * servicio, privacidad, 404, sitemap), copia assets/ y uploads/ y escribe un
 * robots.txt que bloquea la indexación (es una vista previa con fotos de ejemplo).
 * El panel /admin no se exporta: necesita PHP y escribir archivos.
 */

import { spawn } from 'node:child_process';
import { cpSync, mkdirSync, rmSync, writeFileSync, existsSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const project = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const src = join(project, 'public_html');
const out = join(project, 'dist');
const port = 8765;
const base = `http://127.0.0.1:${port}`;
const php = process.env.PHP_BIN || 'php';
const phpArgs = ['-d', 'extension=mbstring', '-d', 'extension=gd', '-d', 'extension=fileinfo'];
if (process.env.PHP_EXT_DIR) phpArgs.unshift('-d', `extension_dir=${process.env.PHP_EXT_DIR}`);

const server = spawn(php, [...phpArgs, '-S', `127.0.0.1:${port}`, '-t', 'public_html', 'dev-router.php'], { cwd: project, stdio: 'ignore' });
server.on('error', (e) => { console.error(`No se pudo ejecutar PHP (${php}): ${e.message}. Usá PHP_BIN=...`); process.exit(1); });
const stop = () => { try { server.kill(); } catch {} };

const waitForServer = async () => {
  for (let i = 0; i < 50; i++) {
    try { await fetch(base + '/robots.txt'); return; } catch { await new Promise((r) => setTimeout(r, 200)); }
  }
  throw new Error('El servidor PHP no arrancó');
};

const save = (path, body) => {
  const file = join(out, path);
  mkdirSync(dirname(file), { recursive: true });
  writeFileSync(file, body);
};

try {
  await waitForServer();
  rmSync(out, { recursive: true, force: true });
  mkdirSync(out, { recursive: true });

  const sitemap = await (await fetch(base + '/sitemap.xml')).text();
  const paths = [...sitemap.matchAll(/<loc>https?:\/\/[^/]+(\/[^<]*)<\/loc>/g)].map((m) => m[1]);

  for (const path of paths) {
    const res = await fetch(base + path);
    if (res.status !== 200) throw new Error(`${path} respondió ${res.status}`);
    save(path === '/' ? 'index.html' : `${path.slice(1)}.html`, await res.text());
    console.log(`página  ${path}`);
  }
  save('404.html', await (await fetch(base + '/esta-pagina-no-existe')).text());
  console.log('página  /404');
  save('sitemap.xml', sitemap);

  // Vista previa: que Google no la indexe (el sitio real es el de Hostinger).
  save('robots.txt', 'User-agent: *\nDisallow: /\n');

  cpSync(join(src, 'assets'), join(out, 'assets'), { recursive: true });
  if (existsSync(join(src, 'uploads'))) {
    cpSync(join(src, 'uploads'), join(out, 'uploads'), { recursive: true, filter: (p) => !p.endsWith('.htaccess') });
  }
  console.log(`\nListo: ${out}`);
} catch (e) {
  console.error(e.message);
  process.exitCode = 1;
} finally {
  stop();
}
