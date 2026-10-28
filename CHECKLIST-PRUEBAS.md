# Checklist de pruebas · Web Clínica Veterinaria La Guardia

Cómo leerlo: **✅ Local** = probado el 24/10/2026 en la PC de desarrollo (PHP 8.3 + Chrome).
Las casillas `[ ]` son lo que falta hacer, sobre todo **en Hostinger** y **en un celular real**.

Prueba automática (repetila después de cada subida):

```bash
node tools/smoke-test.mjs https://clinicalaguardia.com.ar
```

Resultado local (`http://localhost:8000`): **65 OK · 0 fallas** · avisos: marcadores `[A CONFIRMAR]`
visibles y HTTPS/compresión/cache (esos solo se pueden probar en el sitio publicado).

---

## 0. Antes de lanzar: datos que faltan

La web muestra un recuadro `[A CONFIRMAR: …]` donde falta un dato. Ninguno se publica en los
datos estructurados para Google, pero hay que completarlos antes de anunciar la web.

Confirmado por la clínica el 27/10/2026 y ya cargado: WhatsApp 342 486-0753 (también línea
para llamadas), dirección Gaboto 2696, horarios (lunes a viernes 9 a 12 h y 17 a 20 h;
sábados, domingos y feriados cerrado), email e Instagram (Facebook no lo usan), lista de
servicios y cirugías, marcas de balanceado, envíos, peluquería tercerizada (Romina Manso),
equipo (Ángel Emilio Nasso, Noelia, Tamara Silvetti, Romina Manso) y logos en alta. Cristian
López ya no figura como servicio: solo como contacto de urgencias.

- [ ] `SETUP_CODE` cambiado en `admin/config.php` (sin esto no se puede crear la contraseña).
- [ ] **Aprobar los textos de presentación del equipo** (los propuse yo, la clínica pidió que los redacte).
- [ ] Fotos reales: hero, equipo, pacientitos, antes/después de peluquería y pet shop (hoy son de ejemplo).
  Después, apagar el aviso "Versión de prueba" en panel › Aviso superior.
- [ ] Coordenadas de la clínica → panel › Contacto (suma la ubicación exacta para Google).
- [ ] Puntaje de Google confirmado en la ficha oficial → panel › Reseñas (marcar "Confirmé…"). Hasta entonces el hero muestra el horario.
- [ ] Reseñas reales copiadas de Google y link "Ver todas" → panel › Reseñas.
- [ ] Nombres de las clínicas de guardia (hoy solo tienen nombre Azul Veterinaria 24 h y Cristian López) → panel › Clínicas de guardia.
- [ ] Razón social y CUIT del responsable → `politica-de-privacidad.php`. Revisión de la política por un abogado.
- [ ] Que el veterinario confirme el texto de la página de vacunación (incluye "la antirrábica es obligatoria en Argentina").
- [ ] URL de Web Wisse para el crédito del footer → `site.json` (`credits.url`).
- [ ] Dominio definitivo: si no es `clinicalaguardia.com.ar`, cambiar `site.url` en `site.json`.

## 1. WhatsApp y teléfono

| Prueba | Local | Hostinger / celular |
|---|---|---|
| Todos los links `wa.me` van al WhatsApp de la clínica | ✅ | [ ] |
| Cada mensaje arranca con el saludo y nombra el servicio | ✅ | [ ] |
| Cristian López (a domicilio) figura solo en urgencias, con link `tel:` | ✅ | [ ] |
| Links `tel:` con formato +54… | ✅ | [ ] |
| Mini formulario "Armar mensaje" arma el texto con mascota, especie y motivo | ✅ | [ ] |

En el celular:

- [ ] Tocar "Consultar por WhatsApp" del hero: abre la app con el mensaje listo.
- [ ] Tocar "Consultar" de 2 o 3 servicios: el mensaje cambia según el servicio.
- [ ] Botón flotante verde: abre WhatsApp.
- [ ] "Llamar" en una clínica de guardia: abre el marcador con el número correcto.
- [ ] "Cómo llegar": abre Google Maps en la clínica.

## 2. Panel de administración en el celular

| Prueba | Local | Hostinger / celular |
|---|---|---|
| Instalación pide `SETUP_CODE` y no deja crear contraseña con el código de fábrica | ✅ | [ ] |
| Login y cierre de sesión | ✅ | [ ] |
| Guardar el aviso superior y verlo al instante en la web | ✅ | [ ] |
| Subir varias fotos: se convierten a WebP, máx. 1920 px, + versiones para `srcset` | ✅ | [ ] |
| Archivo falso con extensión .jpg rechazado | ✅ | [ ] |
| Bloqueo de 15 min tras 5 contraseñas incorrectas | ✅ (lógica) | [ ] |
| Formulario con token CSRF inválido rechazado (HTTP 400) | ✅ | [ ] |
| Pantallas usables a 375 px, barra "Guardar cambios" fija abajo | ✅ | [ ] |

En el celular del cliente:

- [ ] Instalar la contraseña desde `/admin/` (paso 4 del README).
- [ ] Cambiar un horario, guardar y ver "Abierto ahora / Cerrado" actualizado en la web.
- [ ] Subir una foto sacada con la cámara del celular: no aparece girada.
- [ ] Agregar, ordenar (↑ ↓) y ocultar un servicio.
- [ ] Dejar el panel 30 min sin usar: pide ingresar de nuevo.

## 3. Backups (Versiones)

| Prueba | Local | Hostinger |
|---|---|---|
| Cada guardado deja una copia de cómo estaba antes | ✅ | [ ] |
| Se guardan solo las últimas 10 | ✅ 13 guardados → 10 versiones | [ ] |
| "Restaurar" vuelve a la versión elegida (la más nueva y la más vieja) | ✅ | [ ] |
| Orden correcto aunque haya dos guardados en el mismo segundo | ✅ (corregido: ahora con milisegundos) | [ ] |
| Las fotos que se usaban en una versión guardada no se borran | ✅ | [ ] |

- [ ] Activar los backups automáticos de Hostinger (en hPanel, "Copias de seguridad").
- [ ] Una vez por mes, descargar `content/` y `uploads/` como copia propia.

## 4. Lighthouse (mobile)

Local, con el router que imita gzip y cache (CPU emulada 4× más lenta, varía entre corridas):

| Página | Performance | Accesibilidad | Buenas prácticas | SEO |
|---|---|---|---|---|
| Home | 83–86 | 100 | 100 | 100 |
| Páginas de servicio | 94–95 | 100 | 100 | 100 |
| Política de privacidad | 96 | 100 | 100 | 100 |

LCP ~2,0 s · CLS 0 en todas. Objetivo del brief: Performance ≥ 90, Accesibilidad ≥ 95, Buenas prácticas ≥ 95, SEO 100.

- [ ] Medir en Hostinger con [PageSpeed Insights](https://pagespeed.web.dev/) (home + 1 página de servicio), mobile.
- [ ] Si la home queda < 90 en Hostinger: revisar el costo de estilos de la home (ver README, Rendimiento).

## 5. Movimiento reducido (prefers-reduced-motion)

| Prueba | Local |
|---|---|
| Sin pin, sin smooth scroll, sin cursor ni tilt, sin entrada animada | ✅ Chrome con `--force-prefers-reduced-motion` |
| Todo el contenido visible de entrada (nada queda oculto esperando una animación) | ✅ |
| No se descarga GSAP | ✅ |

- [ ] En un celular con "Reducir movimiento" activado (iPhone: Accesibilidad › Movimiento; Android: Accesibilidad › Quitar animaciones): la web se ve completa y quieta.

## 6. Página 404

| Prueba | Local | Hostinger |
|---|---|---|
| `/cualquier-cosa` responde **404** (no 200) con `noindex` | ✅ | [ ] |
| Se ve el perrito buscando y los botones "Volver al inicio" / "Escribinos" | ✅ | [ ] |

## 7. Seguridad y SEO técnico

| Prueba | Local | Hostinger |
|---|---|---|
| `/content/site.json`, `/content/auth.json`, `/inc/`, `/admin/config.php` bloqueados | ✅ (router local) | [ ] **importante: confirma que el `.htaccess` funciona** |
| `/admin/` con `noindex` y bloqueado en `robots.txt` | ✅ | [ ] |
| `sitemap.xml` con las 6 URLs y `robots.txt` generados | ✅ | [ ] |
| JSON-LD válido y sin `[A CONFIRMAR]` (VeterinaryCare, FAQPage, BreadcrumbList) | ✅ | [ ] |
| `http://` redirige a `https://` y `www.` al dominio sin www | — | [ ] |
| CSS/JS comprimidos (Brotli o gzip) y con cache de 1 año | ✅ (simulado) | [ ] |
| HSTS y `X-Content-Type-Options: nosniff` | — | [ ] |

- [ ] [Prueba de resultados enriquecidos de Google](https://search.google.com/test/rich-results) en la home y en una página de servicio.

## 8. Navegadores, teclado y accesibilidad

- [ ] Chrome Android y Safari iPhone: menú, hero, scroll con la cruz, botones.
- [ ] Chrome, Edge, Firefox y Safari en compu: hero animado, cursor, Lenis.
- [ ] Navegar toda la home solo con Tab: el foco magenta se ve siempre y se llega a todos los botones.
- [ ] Easter egg: 5 clicks en el logo de la home y cruza el perrito.

## 9. Después de publicar

- [ ] Google Search Console: agregar el dominio, enviar `https://clinicalaguardia.com.ar/sitemap.xml`.
- [ ] Ficha de Google Business: poner el link de la web y revisar que horarios y dirección coincidan con el panel.
- [ ] Inspeccionar en Search Console la home y las 4 páginas de servicio y pedir indexación.
- [ ] Volver a correr `node tools/smoke-test.mjs https://clinicalaguardia.com.ar` y quedarse con **0 fallas**.
