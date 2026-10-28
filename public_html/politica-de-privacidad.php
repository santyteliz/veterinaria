<?php
/**
 * /politica-de-privacidad · describe lo que la web hace realmente con los datos
 * (Ley 25.326 de Protección de Datos Personales, Argentina).
 * Si se suma analítica u otro servicio de terceros, actualizar esta página.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$updated = '24/10/2026';
$email = (string) c('contact.email');
$page = [
    'title' => 'Política de privacidad | ' . c('site.name'),
    'description' => 'Cómo trata los datos personales la web de ' . c('site.name') . ': qué se guarda, qué no, servicios de terceros y cómo ejercer tus derechos (Ley 25.326).',
    'path' => '/politica-de-privacidad',
    'schema' => [schema_breadcrumb([['Inicio', '/'], ['Política de privacidad', '/politica-de-privacidad']])],
];

require __DIR__ . '/inc/layout-start.php';
?>
<section class="page-hero" aria-labelledby="page-title">
  <div class="grain" aria-hidden="true"></div>
  <div class="container page-hero__inner">
    <nav class="breadcrumbs" aria-label="Estás en">
      <ol>
        <li><a href="/">Inicio</a></li>
        <li aria-current="page">Política de privacidad</li>
      </ol>
    </nav>
    <h1 id="page-title" class="page-hero__title">Política de privacidad</h1>
    <p class="page-hero__lead">En pocas palabras: esta web no te pide registrarte ni guarda tus datos. Acá te contamos el detalle.</p>
    <p class="legal__updated">Última actualización: <?= e($updated) ?></p>
  </div>
</section>

<section class="section section--light" aria-label="Detalle de la política">
  <div class="container">
    <article class="legal">
      <h2>Quién es responsable</h2>
      <p><?= e(c('site.name')) ?>, <?= e(c('contact.address')) ?>, <?= e(c('contact.city')) ?>, <?= e(c('contact.region')) ?>. Contacto: <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>. <mark class="tbc">[A CONFIRMAR: razón social y CUIT del responsable]</mark></p>

      <h2>Qué datos recolecta esta web</h2>
      <p>La web no tiene registro de usuarios ni formularios que guarden información.</p>
      <ul>
        <li><strong>Mini formulario “Armá tu consulta”.</strong> El nombre de tu mascota, la especie y el motivo se usan solo dentro de tu navegador para armar el mensaje de WhatsApp. No se envían ni se guardan en nuestro servidor.</li>
        <li><strong>WhatsApp y teléfono.</strong> Cuando tocás un botón de WhatsApp o de llamada, salís de la web. Lo que nos escribas lo recibimos por esa vía y lo usamos solo para responderte y hacer el seguimiento de tu mascota (por ejemplo, avisarte cuándo le toca una vacuna).</li>
        <li><strong>Datos técnicos.</strong> Como cualquier sitio, el servidor del hosting registra datos técnicos de las visitas (dirección IP, fecha y navegador) para su funcionamiento y seguridad.</li>
      </ul>

      <h2>Cookies y almacenamiento en tu navegador</h2>
      <ul>
        <li>La web pública no usa cookies propias.</li>
        <li>Si cerrás el aviso de arriba, tu navegador lo recuerda durante esa visita (con <em>sessionStorage</em>) para no volver a mostrártelo. Se borra al cerrar la pestaña.</li>
        <li>El panel de administración usa una cookie de sesión, solo para quien administra la web.</li>
      </ul>

      <h2>Servicios de terceros</h2>
      <ul>
        <li><strong>Google Maps.</strong> El mapa se carga recién cuando tocás “Ver mapa”. A partir de ahí, Google puede usar cookies según su <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">política de privacidad</a>.</li>
        <li><strong>WhatsApp.</strong> Las conversaciones se rigen por la <a href="https://www.whatsapp.com/legal/privacy-policy" target="_blank" rel="noopener">política de privacidad de WhatsApp</a>.</li>
        <li><strong>Instagram.</strong> Es un link a nuestra cuenta. Al abrirlo, aplica la <a href="https://privacycenter.instagram.com/policy" target="_blank" rel="noopener">política de privacidad de Instagram</a>.</li>
      </ul>

      <h2>Estadísticas de visitas</h2>
      <p>Hoy la web no usa herramientas de analítica de terceros. <mark class="tbc">[A CONFIRMAR: si se instala Google Analytics u otra herramienta, actualizar esta sección]</mark></p>

      <h2>Tus derechos</h2>
      <p>Podés pedir acceder, rectificar, actualizar o suprimir tus datos personales escribiéndonos a <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>.</p>
      <p>El titular de los datos personales tiene la facultad de ejercer el derecho de acceso a los mismos en forma gratuita a intervalos no inferiores a seis meses, salvo que se acredite un interés legítimo al efecto conforme lo establecido en el artículo 14, inciso 3 de la Ley N° 25.326.</p>
      <p>La Agencia de Acceso a la Información Pública, en su carácter de Órgano de Control de la Ley N° 25.326, tiene la atribución de atender las denuncias y reclamos que interpongan quienes resulten afectados en sus derechos por incumplimiento de las normas vigentes en materia de protección de datos personales.</p>

      <h2>Cambios en esta política</h2>
      <p>Si cambiamos cómo funciona la web (por ejemplo, si sumamos un formulario o estadísticas), actualizamos esta página y la fecha de arriba.</p>
    </article>
  </div>
</section>
<?php
require __DIR__ . '/inc/layout-end.php';
