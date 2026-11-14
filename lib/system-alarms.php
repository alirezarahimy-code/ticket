<?php
declare(strict_types=1);

/**
 * lib/system-alarms.php — آژیر سلامت سامانه (فاز ۱: «توقف‌کار»ها)
 *
 * هدف (تصمیم و دستور مالک، ۱۴۰۵/۰۸/۲۴ — پس از رخداد خاموشی چاپ فیش غذا):
 * مشکلاتی که «کار سامانه یا خدمت را متوقف/معطل می‌کنند» وسط صفحه برای
 * مدیران نمایش داده شوند — چیزی که در آن روز هیچ‌جا فریاد نزند.
 *
 * اصول غیرقابل مذاکره:
 *  - فقط‌خواندنی از دیتابیس؛ هیچ خودکار-درمانی (چاپ/ابطال/expire) انجام نمی‌شود.
 *  - مسیر وب هیچ socket/اتصال خارجی نمی‌زند؛ فقط نتیجهٔ probe کرون را از
 *    فایل state می‌خواند + چند COUNT سبک روی دادهٔ همان روز (با کش ۶۰ ثانیه).
 *  - فقط نقش‌های مدیریتی (تنظیم system_alarm_roles) آن را می‌بینند؛
 *    با system_alarm_enabled=0 کاملاً خاموش می‌شود.
 *  - بستن هشدار = «تأیید مدیر»: پنجرهٔ «آیا مطمئن هستید؟» + ثبت سابقه در
 *    system_logs با کد SYS-ALARM-ACK. تأیید، علتِ باز را پاک نمی‌کند؛ فقط آن
 *    کلید را به‌مدت system_alarm_ack_snooze_minutes (پیش‌فرض ۶۰) ساکت می‌کند.
 *
 * فایل‌های وابسته:
 *  - api/system-alarms.php          (poll در مرورگر + تأیید ack)
 *  - assets/system-alarm.js         (نمایش وسط صفحه + پنجرهٔ تأیید)
 *  - cron/system_alarm_tick.php     (هر ۵ دقیقه؛ با --morning یعنی پیش‌چک ۰۸:۰۰)
 *  - cron/run_system_alarm_tick.bat / cron/run_system_alarm_morning.bat
 */

const SYSTEM_ALARM_CACHE_TTL = 60;
const SYSTEM_ALARM_STATE_MAX_AGE = 900;   // فایل state کرون بیش از ۱۵ دقیقه کهنه تلقی نمی‌شود
const SYSTEM_ALARM_NOTIFY_COOLDOWN = 1800; // ۳۰ دقیقه بین اعلان‌های هر کلید (مثل سياسة لاگینگ)

function system_alarm_root(): string
{
    return defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);
}

function system_alarm_setting(string $key, string $default): string
{
    if (function_exists('setting')) {
        try {
            return (string) setting($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }
    return $default;
}

/** نقش‌هایی که آژیر را می‌بینند؛ پیش‌فرض: مدیران اصلی سامانه. */
function system_alarm_roles(): array
{
    $raw = system_alarm_setting('system_alarm_roles', 'primary_admin,admin,supervisor');
    $roles = array_values(array_filter(array_map('trim', explode(',', $raw)), static fn ($r) => $r !== ''));
    return $roles === [] ? ['primary_admin'] : $roles;
}

function system_alarm_user_can_see(?array $user): bool
{
    if (!$user) {
        return false;
    }
    if (system_alarm_setting('system_alarm_enabled', '1') === '0') {
        return false;
    }
    $role = trim((string) ($user['role'] ?? ''));
    return $role !== '' && in_array($role, system_alarm_roles(), true);
}

function system_alarm_cache_path(): string  { return system_alarm_root() . '/storage/logs/system-alarm-cache.json'; }
function system_alarm_state_path(): string  { return system_alarm_root() . '/storage/logs/system-alarm-state.json'; }
function system_alarm_ack_path(): string    { return system_alarm_root() . '/storage/logs/system-alarm-ack.json'; }
function system_alarm_notify_path(): string { return system_alarm_root() . '/storage/logs/system-alarm-notify.json'; }

function system_alarm_read_json(string $path): ?array
{
    try {
        if (!is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    } catch (Throwable) {
        return null;
    }
}

function system_alarm_write_json_atomic(string $path, array $data): bool
{
    try {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        $tmp = $path . '.' . getmypid() . '.tmp';
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || @file_put_contents($tmp, $json, LOCK_EX) === false) {
            return false;
        }
        @chmod($tmp, 0660);
        return @rename($tmp, $path);
    } catch (Throwable) {
        return false;
    }
}

function system_alarm_item(string $key, string $title, string $detail, string $link = 'system-health.php'): array
{
    return ['key' => $key, 'severity' => 'critical', 'title' => $title, 'detail' => $detail, 'link' => $link];
}

function system_alarm_self_failed(string $check, Throwable $e): array
{
    return system_alarm_item(
        'alarm_self_failed_' . preg_replace('/[^a-z0-9_]/i', '_', $check),
        'دیده‌بانی سلامت نتوانست بررسی «' . $check . '» را انجام دهد',
        'خطای داخلی دیده‌بانی: ' . $e->getMessage(),
        'system-health.php'
    );
}

/** چک‌های ارزان و فقط‌خواندنی؛ مسیر وب هم با کش ۶۰ ثانیه‌ای صدا می‌زند. */
function system_alarm_collect_cheap(): array
{
    $alarms = [];

    // ۱) صف چاپ غذا: فیشی بیش از ۱۰ دقیقه در «در انتظار چاپ» مانده است (امروز)
    try {
        $n = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE event_type <> 'unknown' AND print_status = 'pending' AND created_at < (NOW() - INTERVAL 10 MINUTE) AND created_at >= CURDATE()")->fetchColumn();
        if ($n > 0) {
            $alarms[] = system_alarm_item(
                'food_queue_stuck',
                'صف چاپ فیش غذا در حال پیشروی نیست',
                $n . ' فیش بیش از ۱۰ دقیقه «در انتظار چاپ» مانده است؛ چاپگر یا سرویس Worker را بررسی کنید.',
                'index.php?page=food-ticket'
            );
        }
    } catch (Throwable $e) {
        $alarms[] = system_alarm_self_failed('food_queue_stuck', $e);
    }

    // ۲) فیشی بیش از ۵ دقیقه در وضعیت «در حال چاپ» گیر کرده است
    try {
        $n = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status = 'printing' AND updated_at < (NOW() - INTERVAL 5 MINUTE)")->fetchColumn();
        if ($n > 0) {
            $alarms[] = system_alarm_item(
                'food_printing_stuck',
                'فیشی بیش از ۵ دقیقه در حال چاپ گیر کرده است',
                $n . ' فیش در وضعیت «در حال چاپ» قفل مانده است؛ Worker وسط چاپ از کار افتاده است.',
                'index.php?page=food-ticket'
            );
        }
    } catch (Throwable $e) {
        $alarms[] = system_alarm_self_failed('food_printing_stuck', $e);
    }

    // ۳) امروز فیش‌هایی به بن‌بست خوردند (آستانه تنظیم‌پذیر، پیش‌فرض ۵)
    try {
        $min = max(1, (int) system_alarm_setting('system_alarm_dead_letter_min', '5'));
        $n = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE created_at >= CURDATE() AND print_status IN ('print_error','failed')")->fetchColumn();
        if ($n >= $min) {
            $alarms[] = system_alarm_item(
                'food_dead_letter',
                $n . ' فیش امروز به بن‌بست چاپ خوردند',
                'این فیش‌ها هرگز چاپ نشده‌اند. تصمیم بگیرید: چاپ / ابطال / ثبت تحویل — سامانه خودکار چاپ نمی‌کند.',
                'index.php?page=food-ticket'
            );
        }
    } catch (Throwable $e) {
        $alarms[] = system_alarm_self_failed('food_dead_letter', $e);
    }

    // ۴) پوشه‌های ضروری storage موجود و قابل نوشتن نیستند
    $badDirs = [];
    foreach (['storage/food_ticket_spool', 'storage/food_ticket_prints', 'storage/logs', 'storage/sessions'] as $rel) {
        $full = system_alarm_root() . '/' . $rel;
        if (!is_dir($full)) {
            $badDirs[] = $rel . ' (موجود نیست)';
            continue;
        }
        if (!is_writable($full)) {
            $badDirs[] = $rel . ' (غیرقابل نوشتن)';
        }
    }
    if ($badDirs !== []) {
        $alarms[] = system_alarm_item(
            'storage_dirs_unwritable',
            'پوشهٔ ضروری سامانه در دسترس نیست',
            implode('، ', $badDirs) . '. بدون این پوشه‌ها چاپ، لاگ یا نشست‌ها می‌خوابند.',
            'system-health.php'
        );
    }

    // ۵) heartbeat Worker چاپ غذا (جدول food_ticket_worker_status)
    try {
        $row = db()->query('SELECT last_seen, status, last_message FROM food_ticket_worker_status WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row) || empty($row['last_seen'])) {
            $alarms[] = system_alarm_item(
                'food_worker_heartbeat',
                'هنوز هیچ اجرای موفقی از Worker چاپ غذا ثبت نشده است',
                'جدول وضعیت Worker خالی است؛ سرویس «Food Ticket PHP Worker» را بررسی یا ری‌استارت کنید.',
                'system-health.php'
            );
        } elseif (strtotime((string) $row['last_seen']) < time() - 600) {
            $alarms[] = system_alarm_item(
                'food_worker_heartbeat',
                'Worker چاپ غذا بیش از ۱۰ دقیقه بی‌حرکت است',
                'آخرین ضربان: ' . (string) $row['last_seen'] . '. سرویس ویندوزی Worker را بررسی یا ری‌استارت کنید.',
                'system-health.php'
            );
        } elseif ((string) ($row['status'] ?? '') === 'error') {
            $alarms[] = system_alarm_item(
                'food_worker_error',
                'Worker چاپ غذا با خطا متوقف شد',
                'آخرین خطا: ' . (string) ($row['last_message'] ?? ''),
                'system-health.php'
            );
        }
    } catch (Throwable $e) {
        $alarms[] = system_alarm_self_failed('food_worker_heartbeat', $e);
    }

    // ۶) رخدادهای شدید (critical)ِ باز در سامانهٔ لاگینگ
    try {
        if (function_exists('db_table_exists') && db_table_exists('log_issues')) {
            $n = (int) db()->query("SELECT COUNT(*) FROM log_issues WHERE status = 'open' AND severity = 'critical'")->fetchColumn();
            if ($n > 0) {
                $alarms[] = system_alarm_item(
                    'logging_critical_open',
                    'رخداد شدیدِ باز در سامانهٔ لاگینگ وجود دارد',
                    $n . ' رخداد با شدت بحرانی در وضعیت «باز» است؛ «سلامت سامانه» را باز کنید.',
                    'system-health.php'
                );
            }
        }
    } catch (Throwable $e) {
        $alarms[] = system_alarm_self_failed('logging_critical_open', $e);
    }

    // ۷) هشدارهای فعال سلامت لاگینگ (ثبت‌شده توسط کرون check_logging_health)
    try {
        if (function_exists('db_table_exists') && db_table_exists('logging_health_alerts')) {
            $rows = db()->query("SELECT alert_key FROM logging_health_alerts WHERE last_state IN ('active','retry') ORDER BY updated_at DESC LIMIT 8")->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $keys = array_values(array_filter(array_map('strval', $rows), static fn ($k) => $k !== ''));
            if ($keys !== []) {
                $alarms[] = system_alarm_item(
                    'logging_health_active',
                    'هشدار سلامت لاگینگ فعال است',
                    implode('، ', $keys),
                    'system-health.php'
                );
            }
        }
    } catch (Throwable $e) {
        $alarms[] = system_alarm_self_failed('logging_health_active', $e);
    }

    // ۸) مهاجرت‌های اجرا‌نشده (ساختار دیتابیس عقب‌تر از نسخهٔ کد)
    try {
        $pending = [];
        $files = glob(system_alarm_root() . '/migrations/*.sql') ?: [];
        $applied = [];
        if (function_exists('db_table_exists') && db_table_exists('schema_updates')) {
            $rows = db()->query('SELECT migration FROM schema_updates')->fetchAll(PDO::FETCH_COLUMN) ?: [];
            foreach ($rows as $row) {
                $applied[strtolower(basename((string) $row))] = true;
            }
        }
        foreach ($files as $path) {
            $name = basename($path);
            $low = strtolower($name);
            if (str_contains($low, 'rollback')) {
                continue;
            }
            if (!isset($applied[$low])) {
                $pending[] = $name;
            }
        }
        if ($pending !== []) {
            sort($pending);
            $shown = array_slice($pending, 0, 6);
            $alarms[] = system_alarm_item(
                'pending_migrations',
                'مهاجرت دیتابیس اجرا نشده است',
                implode('، ', $shown) . (count($pending) > 6 ? '، …' : '') . ' — ساختار پایگاه داده از نسخهٔ کد عقب است؛ به‌روزرسانی را طبق راهنما اعمال کنید.',
                'system-health.php'
            );
        }
    } catch (Throwable $e) {
        $alarms[] = system_alarm_self_failed('pending_migrations', $e);
    }

    return $alarms;
}

/** probeهای سنگین (socket) فقط در کرون اجرا می‌شوند؛ نتیجه در فایل state ذخیره می‌شود. */
function system_alarm_probe_printer(): array
{
    $alarms = [];
    try {
        if (!function_exists('food_ticket_config')) {
            return []; // ماژول غذا بارگذاری نشده؛ کرونی که این فایل را require نمی‌کند تست پرینتر ندارد.
        }
        $config = food_ticket_config(true);
        if (!is_array($config) || empty($config['enabled'])) {
            return []; // ماژول غذا خاموش است؛ وضعیت بحرانی نیست.
        }
        $mode = function_exists('food_ticket_normalize_printer_mode')
            ? (string) food_ticket_normalize_printer_mode($config['printer_mode'] ?? 'tcp_raw')
            : (string) ($config['printer_mode'] ?? 'tcp_raw');
        $host = trim((string) ($config['printer_host'] ?? ''));
        $port = (int) ($config['printer_port'] ?? 9100);
        $share = trim((string) ($config['printer_share'] ?? ''));

        if ($mode === 'tcp_raw' || $mode === 'socket') {
            if ($host === '') {
                $alarms[] = system_alarm_item(
                    'food_printer_unreachable',
                    'پرینتر فیش غذا پیکربندی نشده است',
                    'حالت چاپ «tcp_raw» است ولی آدرس پرینتر خالی است؛ هیچ فیشی چاپ نمی‌شود.',
                    'index.php?page=food-ticket'
                );
            } else {
                $errno = 0;
                $errstr = '';
                $fp = @fsockopen($host, $port, $errno, $errstr, 2);
                if (is_resource($fp)) {
                    fclose($fp);
                } else {
                    $alarms[] = system_alarm_item(
                        'food_printer_unreachable',
                        'پرینتر فیش غذا در دسترس نیست',
                        sprintf('اتصال به %s:%d برقرار نشد (%s). تا وصل‌شدن پرینتر، هیچ فیشی چاپ نمی‌شود.', $host, $port, $errstr !== '' ? $errstr : 'timeout'),
                        'index.php?page=food-ticket'
                    );
                }
            }
        } elseif ($share === '' && $host === '') {
            // حالت اشتراک ویندوز: socket قابل تست نیست؛ فقط پیکربندی را می‌سنجیم.
            $alarms[] = system_alarm_item(
                'food_printer_unreachable',
                'پرینتر فیش غذا پیکربندی نشده است',
                'حالت چاپ اشتراکی است ولی نه نام اشتراک و نه آدرس پرینتر تنظیم شده است.',
                'index.php?page=food-ticket'
            );
        }
    } catch (Throwable $e) {
        $alarms[] = system_alarm_self_failed('food_printer_probe', $e);
    }
    return $alarms;
}

/** همهٔ آلارم‌های فعلی (cheap + probe کرون + پایش خودِ کرون)؛ بدون کش. */
function system_alarm_collect(): array
{
    $alarms = system_alarm_collect_cheap();

    // ۱۰) نتیجهٔ probe کرون (مثلاً تست اتصال پرینتر) فقط اگر تازه است
    $state = system_alarm_read_json(system_alarm_state_path());
    $generated = is_array($state) ? (int) ($state['generated_at'] ?? 0) : 0;
    if (is_array($state) && $generated > 0 && (time() - $generated) <= SYSTEM_ALARM_STATE_MAX_AGE && is_array($state['alarms'] ?? null)) {
        foreach ($state['alarms'] as $probe) {
            if (is_array($probe) && isset($probe['key'], $probe['title'])) {
                $alarms[] = $probe;
            }
        }
    }

    // ۱۱) خودِ دیده‌بانی کرون باید پایش شود (اگر کرون ثبت نشده/کهنه است، همین خودش بحرانی است)
    if (system_alarm_setting('system_alarm_tick_expected', '1') === '1') {
        if ($generated === 0 || (time() - $generated) > SYSTEM_ALARM_STATE_MAX_AGE) {
            $alarms[] = system_alarm_item(
                'watch_cron_stale',
                'دیده‌بانی خودکار سلامت به‌روز نیست',
                'کرون «system_alarm_tick» (هر ۵ دقیقه) یا «system_alarm_morning» (۰۸:۰۰) اجرا نمی‌شود؛ تست پرینتر و پایش خاموش و بی‌صداست. دستور ثبت کرون در docs/SYSTEM-ALARM-FA.md آمده است.',
                'system-health.php'
            );
        }
    }

    // یکتاسازی بر اساس کلید (اولین برنده)
    $seen = [];
    $out = [];
    foreach ($alarms as $alarm) {
        $key = (string) ($alarm['key'] ?? '');
        if ($key === '' || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $out[] = $alarm;
    }
    return $out;
}

/** با کش فایل ۶۰ ثانیه‌ای (سراسری؛ نه per-user). */
function system_alarms_cached(): array
{
    $cached = system_alarm_read_json(system_alarm_cache_path());
    if (is_array($cached) && (int) ($cached['generated_at'] ?? 0) > 0
        && (time() - (int) $cached['generated_at']) < SYSTEM_ALARM_CACHE_TTL
        && is_array($cached['alarms'] ?? null)) {
        return $cached['alarms'];
    }
    try {
        $alarms = system_alarm_collect();
    } catch (Throwable $e) {
        $alarms = [system_alarm_self_failed('engine', $e)];
    }
    system_alarm_write_json_atomic(system_alarm_cache_path(), ['generated_at' => time(), 'alarms' => $alarms]);
    return $alarms;
}

/** آلارم‌هایی که مدیر ندیده/تأیید نکرده (ack فعالِ هر کلید به‌مدت snooze ساکت می‌شود). */
function system_alarms_visible(): array
{
    $alarms = system_alarms_cached();
    if ($alarms === []) {
        return [];
    }
    $acks = (array) (system_alarm_read_json(system_alarm_ack_path()) ?? []);
    $now = time();
    $snooze = 60 * max(1, (int) system_alarm_setting('system_alarm_ack_snooze_minutes', '60'));
    return array_values(array_filter($alarms, static function (array $alarm) use ($acks, $now, $snooze) {
        $key = (string) ($alarm['key'] ?? '');
        $ackAt = (int) ($acks[$key] ?? 0);
        return $ackAt === 0 || ($now - $ackAt) >= $snooze;
    }));
}

/**
 * «تأیید بستن» مدیر: کلیدها را ساکت می‌کند (نه علتِ باز را پاک می‌کند) + سابقه در system_logs.
 * @param array<int,mixed> $keys
 * @return array{ok:bool, acknowledged?:array<int,string>, error?:string}
 */
function system_alarm_acknowledge(array $keys, array $user): array
{
    $cleanMap = [];
    foreach ($keys as $key) {
        $key = trim((string) $key);
        if ($key !== '' && preg_match('/^[A-Za-z0-9_.\-]{1,64}$/', $key) === 1) {
            $cleanMap[$key] = true;
        }
    }
    $cleanKeys = array_keys($cleanMap);
    if ($cleanKeys === []) {
        return ['ok' => false, 'error' => 'هیچ کلید هشدار معتبری ارسال نشد.'];
    }

    // ادغام با ackهای قبلی؛ موارد قدیمی‌تر از ۷ روز حذف می‌شوند
    $acks = (array) (system_alarm_read_json(system_alarm_ack_path()) ?? []);
    $now = time();
    foreach ($acks as $k => $ts) {
        if (($now - (int) $ts) > 7 * 86400) {
            unset($acks[$k]);
        }
    }
    foreach ($cleanKeys as $key) {
        $acks[$key] = $now;
    }
    system_alarm_write_json_atomic(system_alarm_ack_path(), $acks);

    // سابقهٔ مدیریتی — «لاگ‌های سامانه» با کد SYS-ALARM-ACK (مسیر کارِ امروز: ثبت جزئیات از طریق لاگ‌ها)
    $titles = [];
    try {
        $byKey = array_column(system_alarms_cached(), 'title', 'key');
    } catch (Throwable) {
        $byKey = [];
    }
    foreach ($cleanKeys as $key) {
        $titles[] = (string) ($byKey[$key] ?? $key);
    }
    $who = trim((string) ($user['full_name'] ?? ''));
    if ($who === '') {
        $who = (string) ($user['username'] ?? ('#' . (int) ($user['id'] ?? 0)));
    }
    if (function_exists('system_log')) {
        try {
            system_log('info', 'آژیر سلامت', $who . ' هشدار سلامت سامانه را تأیید و بست: ' . implode('؛ ', $titles), [
                'code' => 'SYS-ALARM-ACK',
                'category' => 'system',
                'alarm_keys' => $cleanKeys,
                'user_id' => (int) ($user['id'] ?? 0),
                'acked_at' => date('c'),
            ]);
        } catch (Throwable) {
            // ثبت سابقه نباید پاسخ مرورگر را بخواباند
        }
    }

    return ['ok' => true, 'acknowledged' => $cleanKeys];
}

/** نشانه‌گذاری در فوتر — فقط برای نقش‌های مدیریتی و وقتی صبح/کرون‌ها کار می‌کنند. */
function system_alarm_chrome(?array $user): string
{
    if (!system_alarm_user_can_see($user)) {
        return '';
    }
    // استایل مخصوص این ماژول عمداً inline است تا در هر دو پوسته (سامانه و مرکز درخواست‌ها)
    // و روی هر نسخه‌ای از assets/style.css مستقل از مسیر بسته عمل کند (CSP: style-src اجازهٔ unsafe-inline می‌دهد).
    $css = '<style>'
        . '.system-alarm-list{list-style:none;margin:0 0 18px;padding:0;text-align:right;max-height:40vh;overflow:auto}'
        . '.system-alarm-list li{padding:8px 12px;border:1px solid #fecaca;border-radius:10px;background:#fff;margin:0 0 8px;font-size:13px;line-height:1.9}'
        . '.system-alarm-list li strong{color:#b91c1c}'
        . '.system-alarm-link{color:#1d4ed8;font-weight:700;white-space:nowrap}'
        . '.system-alarm-actions{display:flex;justify-content:center;gap:10px}'
        . '.system-alarm-confirm{margin-top:12px;border-top:1px dashed #ef4444;padding-top:12px}'
        . '.system-alarm-confirm p{margin:0 0 10px;font-size:13px;color:#7f1d1d;line-height:1.9}'
        . '.system-alarm-confirm-actions{display:flex;justify-content:center;gap:10px}'
        . '@media print{.system-alarm{display:none!important}}'
        . '</style>';
    return $css
        . '<div class="inspection-food-reminder system-alarm" id="system-alarm" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="system-alarm-title" data-endpoint="api/system-alarms.php">'
        . '<section class="inspection-food-reminder-card system-alarm-card">'
        . '<div class="inspection-food-reminder-icon" aria-hidden="true">!</div>'
        . '<p class="inspection-food-reminder-heading">هشدار سلامت سامانه</p>'
        . '<h2 id="system-alarm-title">مشکلی کار سامانه را معطل کرده است.</h2>'
        . '<ul class="system-alarm-list" id="system-alarm-list"></ul>'
        . '<div class="system-alarm-actions"><button type="button" class="button inspection-food-reminder-dismiss" id="system-alarm-close">بستن هشدار…</button></div>'
        . '<div class="system-alarm-confirm" id="system-alarm-confirm" hidden>'
        . '<p>آیا مطمئن هستید؟ این هشدار به‌مدت محدود (پیش‌فرض ۶۰ دقیقه) بسته می‌شود و یک ردیف سابقه در «لاگ‌های سامانه» ثبت می‌گردد. اگر علت رفع شود، هشدار کلاً ناپدید می‌شود.</p>'
        . '<div class="system-alarm-confirm-actions">'
        . '<button type="button" class="button" id="system-alarm-confirm-no">خیر</button>'
        . '<button type="button" class="button inspection-food-reminder-dismiss" id="system-alarm-confirm-yes">بله، ببند</button>'
        . '</div></div>'
        . '</section></div>';
}
