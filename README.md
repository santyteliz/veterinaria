# Web Clínica Veterinaria La Guardia

Sitio en PHP 8 **sin base de datos**, hecho por Web Wisse para Clínica Veterinaria La Guardia
(Santo Tomé, Santa Fe). Todo el contenido vive en `public_html/content/site.json` y el cliente
lo edita desde el panel en `/admin`, desde el celular.

## Qué hay en esta carpeta

```
public_html/            ← lo único que se sube al hosting
  index.php             home
  servicio.php          4 páginas de servicio (/peluqueria-canina-santo-tome, etc.)
  politica-de-privacidad.php · 404.php · sitemap.php · robots.php
  .htaccess             URLs limpias, HTTPS, compresión, cache, seguridad
  admin/                panel de administración
  assets/               CSS, JS, fuentes, librerías (GSAP, Lenis) e imágenes
  content/site.json     todo el contenido (lo edita el panel)
  inc/                  plantillas y funciones PHP
  uploads/              fotos que sube el cliente
deploy/                 ZIPs listos para subir (los arma tools/build-zip.ps1)
tools/                  prueba automática y armado de ZIPs
dev-router.php          solo para probar en local (no se sube)
CHECKLIST-PRUEBAS.md    qué probar antes y después de publicar
```

## Requisitos del hosting

- Hostinger compartido (o cualquier hosting con Apache o LiteSpeed y `.htaccess`).
- **PHP 8.1 o más nuevo** (probado con 8.3).
- Extensiones `mbstring`, `gd` (con WebP), `fileinfo` y `exif`. En Hostinger vienen activadas.
- Certificado SSL activo en el dominio (Hostinger da uno gratis).

---

## Primera instalación (paso a paso)

### 1. Configurar PHP en hPanel

En hPanel, entrá a la configuración de PHP del sitio (buscá "PHP" en el buscador del panel):

- Versión: **8.2 o 8.3**.
- Extensiones: que estén tildadas `gd`, `fileinfo`, `mbstring`, `exif`.
- Opciones (para poder subir varias fotos del celular de una vez):

| Opción | Valor mínimo |
|---|---|
| `upload_max_filesize` | 8M |
| `post_max_size` | 64M |
| `max_file_uploads` | 20 |
| `memory_limit` | 256M |

### 2. Activar SSL

En hPanel, en la sección SSL del sitio (buscá "SSL" en el buscador del panel), instalá el
certificado gratuito para el dominio. El `.htaccess` ya
manda todo a `https://` y al dominio sin `www`.

### 3. Armar el ZIP

En la PC, desde esta carpeta:

```bash
powershell -ExecutionPolicy Bypass -File tools\build-zip.ps1
```

Genera en `deploy/`:

- `la-guardia-instalacion.zip`: para la primera subida (todo `public_html`, sin datos de prueba).
- `la-guardia-actualizacion.zip`: para actualizar después, sin `content/` ni `uploads/`.

> Usá estos ZIP y no "Enviar a › Carpeta comprimida" de Windows ni `Compress-Archive`:
> esos guardan las rutas con `\` y en el servidor los archivos quedan mal nombrados.

### 4. Subir y descomprimir

1. hPanel › Archivos › **Administrador de archivos** › carpeta `public_html` del dominio.
2. **Borrá lo que trae Hostinger por defecto** (`default.php`, `index.html` de "próximamente").
3. Subí `la-guardia-instalacion.zip` y descomprimilo **dentro de `public_html`** (no en una
   subcarpeta). Tienen que quedar `public_html/index.php`, `public_html/.htaccess`, etc.
4. Borrá el ZIP del servidor.
5. Si no ves el `.htaccess`, activá "Mostrar archivos ocultos": tiene que estar.

### 5. Código de instalación

Abrí `public_html/admin/config.php` en el Administrador de archivos y cambiá:

```php
const SETUP_CODE = 'CAMBIAR-ESTE-CODIGO';
```

por un código propio (una frase larga, sin compartirla). Sin este cambio, la instalación no
deja crear la contraseña, a propósito.

### 6. Permisos

- Carpetas `content/` y `uploads/`: **755** (si al guardar en el panel aparece un error de
  permisos, probá 775).
- Archivos: **644**.

### 7. Dominio

Si el dominio final no es `clinicalaguardia.com.ar`, cambiá `"url"` en `content/site.json`
(bloque `site`). Se usa para el canonical, el sitemap, Open Graph y los datos para Google.

### 8. Crear la contraseña del panel

Entrá a `https://tu-dominio/admin/`. La primera vez abre la instalación: poné el
`SETUP_CODE` y creá la contraseña (mínimo 10 caracteres). Después de eso la instalación
queda desactivada sola.

### 9. Probar

```bash
node tools/smoke-test.mjs https://tu-dominio
```

Tiene que terminar con **0 fallas**. Después seguí `CHECKLIST-PRUEBAS.md` (WhatsApp en el
celular, panel, PageSpeed, etc.).

---

## Actualizar la web más adelante

1. Armá los ZIP otra vez (`tools\build-zip.ps1`).
2. Subí **`la-guardia-actualizacion.zip`** y descomprimilo en `public_html`, reemplazando.
3. **Nunca subas `content/` ni `uploads/` encima de los del servidor**: ahí están los textos,
   horarios, fotos y la contraseña que cargó el cliente.
4. Corré `node tools/smoke-test.mjs https://tu-dominio`.

CSS y JS propios llevan `?v=<fecha>` y se actualizan solos en el navegador. Si cambiás una
fuente o una librería de `assets/vendor/`, cambiá también el nombre del archivo: están
cacheadas un año como inmutables.

## Copias de seguridad

- El panel guarda solo las últimas 10 versiones de `site.json` (*Versiones* › Restaurar).
- Activá los backups automáticos de Hostinger (en hPanel buscá "Copias de seguridad"; según
  el plan son diarios o semanales).
- Una vez por mes, descargá `content/` y `uploads/` como copia propia.

## Vista previa en Vercel (sin panel)

Vercel no ejecuta PHP, así que ahí se publica una **copia estática** de la web pública
(`dist/`), para mostrarle el diseño al cliente. El sitio real, con panel, va en Hostinger.

1. Regenerar el export después de cada cambio:
   ```bash
   node tools/export-static.mjs
   ```
   (si `php` no está en el PATH: `PHP_BIN="ruta/a/php.exe" PHP_EXT_DIR="ruta/a/ext" node tools/export-static.mjs`)
2. Revisarlo en local: `node tools/serve-dist.mjs` → http://localhost:5173
3. Commit y push. En Vercel: **Add New › Project › importar el repo de GitHub**, framework
   **Other**, sin build command. El `vercel.json` ya le dice que publique `dist/` con URLs
   limpias.

La vista previa no se indexa en Google (`robots.txt` bloquea todo y Vercel manda
`X-Robots-Tag: noindex`), y los canonical apuntan al dominio real. El estado "Abierto
ahora" se recalcula en el navegador, así que sigue funcionando aunque el HTML sea estático.

Las fotos actuales son de ejemplo (ver `FOTOS-DE-EJEMPLO.md`) y hay un aviso arriba que lo
dice.

---

## El panel

- **Sin base de datos.** Cada guardado reescribe `content/site.json` de forma atómica
  (archivo temporal + rename, con lock) y antes guarda una copia en `content/backups/`.
- **Fotos.** JPG, PNG o WEBP de hasta 5 MB. Se enderezan (EXIF), se achican a 1920 px y se
  convierten a WebP (~150–300 KB), con versiones de 480, 960 y 1440 px para `srcset`, en
  `uploads/AAAA/MM/`. Las que ya no usa ni el contenido actual ni ninguna versión guardada se
  borran solas.
- **Seguridad.** Contraseña con `password_hash` (Argon2id/bcrypt) en `content/auth.json`;
  sesión `httponly` + `SameSite=Strict` (+ `secure` con HTTPS), cierre a los 30 min sin uso;
  token CSRF en todos los formularios; bloqueo de 15 min tras 5 intentos fallidos; `content/`,
  `inc/` y `admin/config.php` no se pueden abrir por URL; en `uploads/` no se ejecuta PHP.

**Qué se edita desde el panel:** aviso superior · horarios y feriados · contacto, redes y
coordenadas · servicios (texto, ícono, orden, ocultar) · clínicas de guardia · galería de
pacientitos · equipo · reseñas de Google · versiones · contraseña.

**Qué se edita en `content/site.json`** (por ahora): textos del hero y de las secciones, FAQ,
SEO (title y description), pet shop, fotos del hero y del antes/después de peluquería, y el
texto de las 4 páginas de servicio (`pages`). Si se edita a mano, validá el JSON antes de
guardar (por ejemplo en jsonlint.com): un JSON roto deja la web en error 500.

## Problemas frecuentes

| Problema | Solución |
|---|---|
| Me olvidé la contraseña | Borrá `content/auth.json` y entrá a `/admin/`: se abre la instalación (pide el `SETUP_CODE`). |
| "Demasiados intentos" en el login | Esperá 15 min, o borrá `content/login-attempts.json`. |
| "No se pudo escribir el archivo" al guardar | Permisos de `content/` (755 o 775). |
| Las fotos no suben o tiran error | Revisá `upload_max_filesize`, `post_max_size` y `memory_limit` (paso 1) y que `gd` esté activa. |
| Error 500 en toda la web | `content/site.json` roto (restaurá una copia de `content/backups/`) o versión de PHP menor a 8.1. |
| Las páginas de servicio dan 404 | No se subió el `.htaccess` o no está en `public_html`. |
| Se ve la web vieja | Caché del navegador o del hosting: en hPanel buscá "caché" y limpiala (LiteSpeed Cache). |
| `/content/site.json` se puede abrir | Falta `content/.htaccess`: volvé a subir esa carpeta solo con ese archivo. |

---

## Probar en local

```bash
php -S localhost:8000 -t public_html dev-router.php
```

`dev-router.php` imita las reglas del `.htaccess` (URLs limpias, sitemap, robots, carpetas
bloqueadas, gzip y cache) porque el servidor de PHP las ignora. Necesita las extensiones
`mbstring`, `gd`, `fileinfo` y `exif` (ver `.claude/launch.json`). No se sube al hosting.

- Prueba automática: `node tools/smoke-test.mjs http://localhost:8000`
- Lighthouse mobile: `npx lighthouse@12.6.0 http://localhost:8000 --view`

## Rendimiento

- Todo self-hosted, sin CDNs: GSAP 3.13.0 y Lenis 1.3.4 en `assets/vendor/` y fuentes en
  `assets/fonts/` (subset latin, `font-display: swap`, fallbacks con métricas medidas para que
  no salte el layout). Bricolage Grotesque va en 2 instancias estáticas de 41 KB.
- Solo la home carga JS de animación, y bajo demanda: en desktop GSAP + SplitText para la
  entrada; en mobile GSAP recién con el primer scroll; ScrollTrigger y Lenis siempre con el
  primer scroll. Las páginas de servicio no cargan GSAP.
- Mobile: el hero se ve completo de entrada (el titular es el LCP); el pin y la inundación
  magenta al scrollear siguen. Desktop mantiene la coreografía completa.
- Microinteracciones en `assets/js/app.js`, sin librerías: revelado de secciones, cursor y
  tilt (solo con mouse), botones magnéticos, contador del puntaje (solo con datos
  confirmados) y el easter egg (5 clicks en el logo de la home).
- Con `prefers-reduced-motion` no corre ninguna animación ni se descarga GSAP.
- `content-visibility: auto` en las secciones de abajo del pliegue.
- `.htaccess`: Brotli/gzip, cache de 1 año para CSS/JS/fuentes y 30 días para imágenes, HSTS
  y cabeceras de seguridad.

Lighthouse mobile local: home 83–86, páginas de servicio 94–95; Accesibilidad, Buenas
prácticas y SEO 100; CLS 0. Medir otra vez en Hostinger (ver checklist).

## SEO

- Datos estructurados `VeterinaryCare` (dirección, teléfono, horarios y feriados del panel,
  redes, coordenadas si se cargan, puntaje solo si está confirmado), `FAQPage` en la home y
  en cada página de servicio, y `BreadcrumbList` en las páginas internas. Los marcadores
  `[A CONFIRMAR]` nunca se publican en esos datos.
- `/sitemap.xml` y `/robots.txt` se generan solos desde `site.json`.
- Open Graph y Twitter Card con `assets/img/og-la-guardia.jpg` (1200×630).
- Al publicar: Google Search Console (enviar el sitemap), link de la web en la ficha de
  Google Business y coordenadas en el panel (Contacto).
