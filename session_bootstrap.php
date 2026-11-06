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

    // توکن CSRF همین‌جا کاشته می‌شود، در حالی که نشست تازه باز شده و قابل نوشتن است.
    // چند مسیر پرکار (actions/asset-actions.php، domain-scan.php، food-ticket.php)
    // برای آزاد کردن قفل فایل نشست، session_write_close() صدا می‌زنند؛ اگر توکن بعد
    // از آن لحظه ساخته شود، نوشتنش در نشست از بین می‌رود. راه‌حل قبلی این بود که توکن
    // از کوکی itsm_csrf خوانده شود — یعنی مرورگر تعیین می‌کرد توکن چه باشد و محافظت
    // CSRF عملاً بی‌اثر می‌شد. کاشت زودهنگام همان نیاز را بدون آن ریسک برطرف می‌کند.
    if (!isset($_SESSION['csrf']) || preg_match('/^[a-f0-9]{64}$/', (string) $_SESSION['csrf']) !== 1) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
}

/**
 * نشستِ بی‌استفاده را منقضی می‌کند.
 *
 * عمر کوکی نشست ۳۶۵ روز است و در هر درخواست تمدید می‌شود، پس تا پیش از این یک نشست
 * رهاشده روی رایانه‌ای که کسی از آن استفاده نمی‌کرد هم یک سال معتبر می‌ماند.
 *
 * این تابع «آخرین فعالیت» را دنبال می‌کند، نه عمر مطلق نشست را؛ یعنی کاربری که هر
 * روز کار می‌کند هیچ‌وقت بیرون انداخته نمی‌شود.
 *
 * @param int $idleDays شمار روزهای بی‌فعالیت تا انقضا. صفر یا منفی = غیرفعال
 *                      (رفتار پیش از نسخهٔ ۱.۶۲.۸۳).
 */
function app_session_enforce_idle(int $idleDays): void
{
    if (PHP_SAPI === 'cli' || session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    if ($idleDays <= 0) {
        return;
    }
    // نشست مهمان چیزی برای منقضی کردن ندارد و پاک کردنش فقط پیام‌های flash را می‌پراند.
    if (empty($_SESSION['user_id'])) {
        return;
    }

    $now = time();
    $lastSeen = (int) ($_SESSION['last_seen'] ?? 0);

    // $lastSeen === 0 یعنی نشستی که پیش از فعال شدن این سازوکار ساخته شده؛ آن را
    // منقضی نمی‌کنیم تا کسی با نصب این نسخه بی‌خبر بیرون نیفتد؛ فقط ساعت را می‌زنیم.
    if ($lastSeen > 0 && ($now - $lastSeen) > $idleDays * 86400) {
        $_SESSION = [];
        // شناسهٔ قدیمی باید بمیرد، وگرنه همان شناسه دوباره قابل استفاده است.
        session_regenerate_id(true);
        return;
    }

    // نوشتن در نشست را به هر پنج دقیقه محدود می‌کنیم تا درخواست‌های پرتکرار
    // (polling اعلان‌ها) هر بار فایل نشست را بازنویسی نکنند.
    if ($now - $lastSeen >= 300) {
        $_SESSION['last_seen'] = $now;
    }
}
