#!/usr/bin/env node
/**
 * Sirve dist/ en local imitando a Vercel (cleanUrls + 404.html), para revisar el
 * export estático antes de subirlo:   node tools/serve-dist.mjs [puerto]
 */

import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { extname, join, normalize, resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..', 'dist');
const port = Number(process.argv[2] || 5173);
const types = {
  '.html': 'text/html; charset=utf-8', '.css': 'text/css; charset=utf-8', '.js': 'application/javascript; charset=utf-8',
  '.svg': 'image/svg+xml', '.png': 'image/png', '.jpg': 'image/jpeg', '.webp': 'image/webp', '.woff2': 'font/woff2',
  '.xml': 'application/xml; charset=utf-8', '.txt': 'text/plain; charset=utf-8',
};

const tryFile = async (p) => { try { return (await stat(p)).isFile() ? p : null; } catch { return null; } };

createServer(async (req, res) => {
  const path = normalize(decodeURIComponent(new URL(req.url, 'http://x').pathname)).replace(/\\/g, '/');
  const safe = join(root, path);
  if (!safe.startsWith(root)) { res.writeHead(400).end(); return; }
  const file = (await tryFile(safe)) || (await tryFile(safe + '.html')) || (await tryFile(join(safe, 'index.html')));
  const target = file || join(root, '404.html');
  res.writeHead(file ? 200 : 404, { 'Content-Type': types[extname(target)] || 'application/octet-stream' });
  res.end(req.method === 'HEAD' ? undefined : await readFile(target));
}).listen(port, () => console.log(`dist/ en http://localhost:${port}`));
