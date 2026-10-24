<?php
declare(strict_types=1);

/** Start durable sessions and renew the browser cookie on every web request. */
function app_session_start(): void
{
    if (PHP_SAPI === 'cli' || session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $lifetime = 365 * 24 * 60 * 60;
    $sessionDir = __DIR__ . '/storage/sessions';
    if (!is_dir($sessionDir) && !@mkdir($sessionDir, 0700, true) && !is_dir($sessionDir)) {
        error_log('Could not create private session directory: ' . $sessionDir);
        $sessionDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'persian-ticket-sessions';
        if (!is_dir($sessionDir) && !@mkdir($sessionDir, 0700, true) && !is_dir($sessionDir)) {
            throw new RuntimeException('Unable to create a session storage directory.');
        }
    }
    if (!is_writable($sessionDir)) {
        error_log('Session directory is not writable: ' . $sessionDir);
        $sessionDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'persian-ticket-sessions';
        if (!is_dir($sessionDir) && !@mkdir($sessionDir, 0700, true) && !is_dir($sessionDir)) {
            throw new RuntimeException('Unable to create a writable session storage directory.');
        }
    }

    @chmod($sessionDir, 0700);
    ini_set('session.save_path', $sessionDir);
    ini_set('session.gc_maxlifetime', (string) $lifetime);
    ini_set('session.cookie_lifetime', (string) $lifetime);
    ini_set('session.use_strict_mode', '1');

    $secure = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    $sessionCookieParams = [
        'lifetime' => $lifetime,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    $cookieOptions = [
        'expires' => time() + $lifetime,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    session_set_cookie_params($sessionCookieParams);
    session_start();

    // PHP normally emits the session cookie only when creating a new session.
    // Renew it explicitly so an active user is not logged out due to idle time.
    if (!headers_sent() && session_id() !== '') {
        setcookie(session_name(), session_id(), $cookieOptions);
    }
}
