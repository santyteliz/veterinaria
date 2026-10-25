# Fotos de ejemplo

Mientras la clínica no manda sus fotos, la web usa fotos libres de [Unsplash](https://unsplash.com)
(licencia Unsplash: uso gratuito, comercial incluido, sin atribución obligatoria) para mostrar
cómo queda. Arriba de la web hay un aviso que lo dice.

**Hay que reemplazarlas todas antes del lanzamiento**: no son pacientitos ni trabajos de la
clínica. Se cambian desde el panel (Pacientitos) o en `content/site.json` (hero, peluquería,
pet shop). Después, apagar el aviso en el panel › Aviso superior.

El equipo (Ángel, Cristian, Priscila) queda sin foto a propósito: no usamos caras de otras
personas con sus nombres.

| Lugar | Foto en Unsplash |
|---|---|
| Hero (dentro de la cruz) | https://unsplash.com/photos/9gz3wfHr65U |
| Pacientitos 1 | `photo-1518020382113-a7e8fc38eac9` |
| Pacientitos 2 | https://unsplash.com/photos/gKXKBY-C-Dk |
| Pacientitos 3 | `photo-1558788353-f76d92427f16` |
| Pacientitos 4 | https://unsplash.com/photos/ZCHj_2lJP00 |
| Pacientitos 5 | `photo-1517423440428-a5a00ad493e8` |
| Pacientitos 6 | https://unsplash.com/photos/mJaD10XeD7w |
| Pacientitos 7 | `photo-1537151608828-ea2b11777ee8` |
| Pacientitos 8 | https://unsplash.com/photos/so5nsYDOdxw |
| Pacientitos 9 | https://unsplash.com/photos/LGG5P7KCziU |
| Peluquería · antes / después 1 | `photo-1779899833023-d2f608715ffd` / `photo-1772437883387-8076cf660c65` |
| Peluquería · antes / después 2 | `photo-1581887936036-3f4f7f0b6679` / `photo-1588943211346-0908a1fb0b01` |
| Pet shop · balanceados | `photo-1676193866128-03a926df76ef` |
| Pet shop · antiparasitarios | https://unsplash.com/photos/ouo1hbizWwo |
| Pet shop · farmacia | `photo-1725409796872-8b41e8eca929` |
| Pet shop · accesorios | `photo-1652757558895-0c021232ecb4` |

Los códigos `photo-…` se abren como `https://images.unsplash.com/<código>`.

Para volver a cargarlas (por ejemplo en otra copia del sitio):
`php tools/import-example-photos.php <carpeta-con-los-jpg>` (ver comentario del script).
