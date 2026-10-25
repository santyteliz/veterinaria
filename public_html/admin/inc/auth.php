<?php
/**
 * Sesión, login, CSRF y límite de intentos. Un solo usuario: la contraseña
 * vive hasheada (password_hash) en content/auth.json, nunca en texto plano.
 */

declare(strict_types=1);

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function admin_session_start(): void
{
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('lg_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/admin',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

/* ---- Contraseña --------------------------------------------------------- */

function auth_exists(): bool
{
    return is_file(AUTH_FILE);
}

function auth_hash(): string
{
    $data = json_decode((string) @file_get_contents(AUTH_FILE), true);
    return is_array($data) ? (string) ($data['hash'] ?? '') : '';
}

function auth_set_password(string $password): void
{
    $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    $json = json_encode(['hash' => password_hash($password, $algo), 'updated' => date('c')], JSON_PRETTY_PRINT);
    write_file_atomic(AUTH_FILE, (string) $json);
}

function password_problem(string $password): ?string
{
    if (mb_strlen($password) < 10) {
        return 'La contraseña tiene que tener al menos 10 caracteres.';
    }
    return null;
}

/* ---- Límite de intentos (por IP, guardado en JSON) --------------------- */

function client_key(): string
{
    return hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

function attempts_load(): array
{
    $data = json_decode((string) @file_get_contents(ATTEMPTS_FILE), true);
    return is_array($data) ? $data : [];
}

function attempts_save(array $data): void
{
    $now = time();
    // Limpieza: solo se conservan registros recientes.
    $data = array_filter($data, fn ($r) => ($r['last'] ?? 0) > $now - 86400);
    write_file_atomic(ATTEMPTS_FILE, (string) json_encode($data));
}

/** Segundos de bloqueo restantes para esta IP (0 = puede intentar). */
function login_locked_for(): int
{
    $r = attempts_load()[client_key()] ?? null;
    return $r ? max(0, (int) ($r['locked_until'] ?? 0) - time()) : 0;
}

function login_register_fail(): void
{
    $all = attempts_load();
    $key = client_key();
    $r = $all[$key] ?? ['fails' => 0];
    $r['fails'] = (int) $r['fails'] + 1;
    $r['last'] = time();
    if ($r['fails'] >= LOGIN_MAX_FAILS) {
        $r['locked_until'] = time() + LOGIN_LOCK_SECONDS;
        $r['fails'] = 0;
    }
    $all[$key] = $r;
    attempts_save($all);
}

function login_clear_fails(): void
{
    $all = attempts_load();
    unset($all[client_key()]);
    attempts_save($all);
}

/* ---- Sesión ------------------------------------------------------------- */

function login(string $password): bool
{
    $hash = auth_hash();
    if ($hash === '' || !password_verify($password, $hash)) {
        return false;
    }
    if (password_needs_rehash($hash, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT)) {
        auth_set_password($password);
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = 'admin';
    $_SESSION['last'] = time();
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return true;
}

function logout(): void
{
    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Strict']);
    session_destroy();
}

function require_login(): void
{
    if (!auth_exists()) {
        redirect('/admin/setup.php');
    }
    if (empty($_SESSION['uid'])) {
        redirect('/admin/login.php');
    }
    if (time() - (int) ($_SESSION['last'] ?? 0) > SESSION_TIMEOUT) {
        logout();
        redirect('/admin/login.php?expirada=1');
    }
    $_SESSION['last'] = time();
}

/* ---- CSRF --------------------------------------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = (string) ($_POST['csrf'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('La sesión del formulario venció. Volvé atrás, recargá la página y probá de nuevo.');
    }
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

/* ---- Mensajes de confirmación (flash) ----------------------------------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_take(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}
