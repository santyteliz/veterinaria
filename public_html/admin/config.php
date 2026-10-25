<?php
/**
 * Configuración del panel. Antes de subir el sitio, cambiá SETUP_CODE por un
 * código propio: se pide una sola vez en /admin/setup.php para crear la
 * contraseña. Una vez creada, setup.php queda desactivado.
 */

declare(strict_types=1);

const SETUP_CODE = 'CAMBIAR-ESTE-CODIGO';

const SESSION_TIMEOUT = 30 * 60;   // cierre por inactividad (segundos)
const LOGIN_MAX_FAILS = 5;         // intentos fallidos antes del bloqueo
const LOGIN_LOCK_SECONDS = 15 * 60;
const BACKUPS_KEEP = 10;
const UPLOAD_MAX_BYTES = 5 * 1024 * 1024;
const UPLOAD_MAX_SIDE = 1920;
