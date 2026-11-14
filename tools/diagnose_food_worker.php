<?php
declare(strict_types=1);

/**
 * diagnose_food_worker.php — اسکن کامل و «فقط‌خواندنی» وضعیت Worker فیش غذا
 *
 * اجرا روی سرور ویندوز/XAMPP:
 *   C:\xampp\php\php.exe C:\xampp\htdocs\persian-ticketing\tools\diagnose_food_worker.php > diag-food-worker.txt 2>&1
 *
 * تضمین‌ها:
 *   - هیچ INSERT/UPDATE/DELETE یا تغییر فایلی انجام نمی‌دهد (تنها استثنا: ساخت و حذف
 *     یک فایل موقت کوچک در storage فقط برای تست «قابل‌نوشتن بودن» پوشه).
 *   - ODBC باز نمی‌کند (فایل Access را قفل نمی‌کند)؛ فقط وجود/حجم/mtime فایل بررسی می‌شود.
 *   - به پرینتر هیچ بایت چاپ نمی‌فرستد؛ فقط یک اتصال TCP تست و بسته می‌شود.
 *   - رمزهای دیتابیس/تردد را چاپ نمی‌کند.
 *
 * اگر خروجی فارسی به‌هم‌ریخته بود: اول `chcp 65001` را در cmd اجرا کنید.
 */

$root = dirname(__DIR__);
@chdir($root);

const DIAG_VERSION = '1.3.0';
$issues = 0;
$warns = 0;
$line = static function (string $s = ''): void { echo $s, PHP_EOL; };
$ok   = static function (string $s) use ($line): void { $line('  [OK]   ' . $s); };
$info = static function (string $s) use ($line): void { $line('  [i]    ' . $s); };
$warn = static function (string $s) use ($line, &$warns): void { $warns++; $line('  [WARN] ' . $s); };
$bad  = static function (string $s) use ($line, &$issues): void { $issues++; $line('  [FAIL] ' . $s); };

$line('==================================================');
$line(' تشخیص Worker فیش غذا — نسخهٔ اسکریپت ' . DIAG_VERSION);
$line(' زمان سرور: ' . date('Y-m-d H:i:s'));
$line(' ریشهٔ برنامه: ' . $root);
$line('==================================================');

// ── ۰) پیش‌شرط: config.php ──────────────────────────────────────────
$configFile = (string) (getenv('ITSM_CONFIG_PATH') ?: ($root . '/config.php'));
if (!is_file($configFile)) {
    fwrite(STDERR, "[FAIL] config.php پیدا نشد در: {$configFile}\n"
        . "این اسکریپت را داخل ریشهٔ برنامه (پوشهٔ tools در کنار index.php) اجرا کنید.\n");
    exit(1);
}

require $root . '/bootstrap.php';
foreach (['food-ticket.php', 'food-ticket-window.php', 'food-ticket-auto-receipts.php', 'food-ticket-runtime.php', 'food-ticket-engine.php'] as $f) {
    if (is_file($root . '/' . $f)) {
        require_once $root . '/' . $f;
    }
}
if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}

$tehranNow   = new DateTimeImmutable('now', new DateTimeZone('Asia/Tehran'));
$tehranToday = $tehranNow->format('Y-m-d');
$dayNames    = ['', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه', 'یکشنبه'];

// توابع کمکی دیتابیس (اطلاعات فقط از information_schema)
function diag_table_exists(string $table): bool {
    try {
        $stmt = db()->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable) {
        return false;
    }
}
function diag_columns(string $table): array {
    try {
        $stmt = db()->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $stmt->execute([$table]);
        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    } catch (Throwable) {
        return [];
    }
}
function diag_mask_row(array $row): array {
    foreach ($row as $k => $v) {
        if (preg_match('/pass|secret|token|enc/i', (string) $k)) {
            $row[$k] = '***';
        }
    }
    return $row;
}

// ── ۱) PHP و محیط ─────────────────────────────────────────────────────
$line("\n۱) PHP و محیط اجرا");
$line('  PHP ' . PHP_VERSION . ' | SAPI: ' . PHP_SAPI . ' | OS: ' . PHP_OS);
foreach (['pdo_mysql', 'odbc', 'openssl', 'exec', 'mbstring'] as $ext) {
    in_array($ext, get_loaded_extensions(), true) ? $ok("ext {$ext} فعال است") : $warn("ext {$ext} فعال نیست");
}
$line('  ساعت تهران: ' . $tehranNow->format('Y-m-d H:i:s') . ' (' . ($dayNames[(int) $tehranNow->format('N')] ?? '?') . ')');
$line('  opcache.enable_cli=' . var_export(ini_get('opcache.enable_cli'), true)
    . ' | opcache.validate_timestamps=' . var_export(ini_get('opcache.validate_timestamps'), true)
    . ' | opcache.revalidate_freq=' . var_export(ini_get('opcache.revalidate_freq'), true));
if (strtolower((string) ini_get('opcache.enable_cli')) === '1' && strtolower((string) ini_get('opcache.validate_timestamps')) === '0') {
    $warn('opcache برای CLI فعال و بدون بررسی تغییر فایل است — فایل‌های آپدیتی ممکن است دیده نشوند.');
}
if (function_exists('disk_free_space')) {
    $free = @disk_free_space($root);
    if ($free !== false) {
        $info('فضای خالی دیسک: ' . number_format($free / 1024 / 1024, 1) . ' MB');
    }
}

// ── ۲) فایل‌های کلیدی و قفل‌ها ─────────────────────────────────────────
$line("\n۲) فایل‌های کلیدی روی دیسک (mtime مهم است)");
foreach (['cron/food_ticket_worker.php', 'food-ticket-window.php', 'food-ticket-engine.php', 'food-ticket-runtime.php', 'food-ticket-auto-receipts.php', 'food-ticket.php', 'VERSION', 'config.php'] as $f) {
    $p = $root . '/' . $f;
    if (!is_file($p)) {
        $bad("فایل وجود ندارد: {$f}");
        continue;
    }
    $ageDays = (time() - filemtime($p)) / 86400;
    $info(sprintf('%-38s %9s B | mtime %s | %0.1f روز پیش', $f, number_format((float) filesize($p)), date('Y-m-d H:i:s', filemtime($p)), $ageDays));
}
if (is_file($root . '/VERSION')) {
    $info('VERSION = ' . trim((string) file_get_contents($root . '/VERSION')));
}
$line('  پوشهٔ storage:');
if (!is_dir($root . '/storage')) {
    $bad('پوشهٔ storage وجود ندارد');
} else {
    $tmp = $root . '/storage/._diag_write_' . getmypid() . '.tmp';
    if (@file_put_contents($tmp, 'diag') !== false) {
        @unlink($tmp);
        $ok('storage قابل نوشتن است');
    } else {
        $bad('storage قابل نوشتن نیست!');
    }
    foreach (['food_ticket_logs', 'food_ticket_spool', 'food_ticket_prints', 'logs'] as $d) {
        is_dir($root . '/storage/' . $d) ? $info('dir storage/' . $d . ' موجود است') : $warn('dir storage/' . $d . ' وجود ندارد');
    }
    $logDir = $root . '/storage/food_ticket_logs';
    if (is_dir($logDir)) {
        $files = glob($logDir . '/*.log') ?: [];
        usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
        foreach (array_slice($files, 0, 3) as $f) {
            $info('لاگ: ' . basename($f) . ' | mtime ' . date('Y-m-d H:i:s', filemtime($f)));
        }
    }
}
foreach (['food-worker-process.lock' => 'قفل نمونهٔ Worker', 'food-print-queue.lock' => 'قفل صف چاپ', 'food-ticket.lock' => 'قفل retry دستی'] as $lf => $label) {
    $p = $root . '/storage/' . $lf;
    if (is_file($p)) {
        $pid = trim((string) @file_get_contents($p));
        $info($label . ': موجود | mtime ' . date('Y-m-d H:i:s', filemtime($p)) . ' | pid=' . ($pid !== '' ? $pid : '?'));
    } else {
        $info($label . ': وجود ندارد');
    }
}

// ── ۳) خودِ Worker: heartbeat / تسک / لاگ ─────────────────────────────
$line("\n۳) وضعیت زندهٔ Worker");
$hb = $root . '/storage/food_worker.heartbeat';
$hbPid = null;
if (is_file($hb)) {
    $ageS = time() - filemtime($hb);
    $content = trim((string) @file_get_contents($hb));
    if ($ageS <= 120) {
        $ok("heartbeat تازه است ({$ageS} ثانیه پیش): {$content}");
    } elseif ($ageS <= 600) {
        $warn("heartbeat {$ageS} ثانیه قدیمی است: {$content}");
    } else {
        $bad('heartbeat ' . number_format($ageS / 60, 1) . ' دقیقه قدیمی است — Worker احتمالاً زنده نیست! محتوا: ' . $content);
    }
    if (preg_match('/pid=(\d+)/', $content, $m)) {
        $hbPid = (int) $m[1];
        $info('pid از heartbeat: ' . $hbPid);
    }
} else {
    $bad('فایل storage/food_worker.heartbeat وجود ندارد — Worker هرگز اجرا نشده یا پاک شده است.');
}

if (stripos(PHP_OS, 'WIN') === 0 && function_exists('shell_exec')) {
    $out = @shell_exec('schtasks /Query /TN "Food Ticket PHP Worker" /FO LIST /V 2>&1');
    if ($out) {
        $interesting = [];
        foreach (preg_split('/\r\n|\n|\r/', (string) $out) as $l) {
            if (preg_match('/^\s*(Status|Task To Run|Last Run Time|Last Result|Next Run Time|Scheduled Task State)\s*:/i', $l)) {
                $interesting[] = trim($l);
            }
        }
        if ($interesting) {
            $info('تسک زمان‌بندی‌شدهٔ «Food Ticket PHP Worker»:');
            foreach ($interesting as $l) {
                $line('        ' . $l);
            }
        } else {
            $warn('خروجی خوانا از schtasks گرفته نشد؛ دستی اجرا کنید: schtasks /Query /TN "Food Ticket PHP Worker" /V');
        }
    } else {
        $warn('schtasks خروجی نداد (یا اجرا نشد).');
    }

    $out2 = @shell_exec('powershell -NoProfile -Command "Get-CimInstance Win32_Process -Filter \'Name=\"php.exe\"\' | ForEach-Object { $_.ProcessId.ToString() + \' | \' + ($_.CommandLine -replace \'\\s+\',\' \') }" 2>&1');
    if ($out2) {
        $found = false;
        foreach (preg_split('/\r\n|\n|\r/', (string) $out2) as $l) {
            if (stripos($l, 'food_ticket_worker') !== false) {
                $found = true;
                $info('پروسهٔ فعال Worker یافت شد: ' . trim($l));
            }
        }
        if (!$found) {
            // جستجوی راه دوم: wmic (قدیمی‌تر ولی معمولاً موجود)
            $out3 = @shell_exec('wmic process where "CommandLine like \'%food_ticket_worker%\'" get ProcessId,CommandLine /FORMAT:LIST 2>&1');
            if ($out3 && stripos((string) $out3, 'food_ticket_worker') !== false) {
                $found = true;
                $info('پروسهٔ Worker (wmic): ' . trim(mb_substr((string) $out3, 0, 300)));
            }
        }
        if (!$found) {
            $bad('هیچ پروسهٔ php.exe با آرگومان food_ticket_worker پیدا نشد!');
        }
    }

    if ($hbPid) {
        $out4 = @shell_exec('tasklist /FI "PID eq ' . (int) $hbPid . '" /FO CSV /NH 2>&1');
        if ($out4 !== null && $out4 !== false) {
            if (stripos((string) $out4, 'php.exe') !== false || stripos((string) $out4, 'php-cgi.exe') !== false) {
                $ok('pid اعلام‌شده در heartbeat هنوز زنده است (tasklist).');
            } else {
                $bad('pid اعلام‌شده در heartbeat (' . $hbPid . ') زنده نیست: ' . trim((string) $out4));
            }
        }
    }
} else {
    $info('بررسی تسک/پروسه فقط روی ویندوز انجام می‌شود.');
}

$logToday = $root . '/storage/food_ticket_logs/worker-' . $tehranNow->format('Ymd') . '.log';
if (is_file($logToday)) {
    $logLines = @file($logToday, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $info('لاگ امروز: ' . basename($logToday) . ' (' . count($logLines) . ' خط)');
    $line('  ── آخرین خطوط ──');
    foreach (array_slice($logLines, -12) as $l) {
        $line('   ' . mb_substr($l, 0, 300));
    }
    $joined = implode("\n", $logLines);
    foreach ([
        'strict_window_idle'    => 'چرخه‌های «خارج از پنجره / سختگیرانه»',
        '[queue]'               => 'خطاهای تخلیهٔ صف چاپ',
        '[batch]'               => 'خطاهای خواندن تردد/Access',
        '[auto-receipts]'       => 'خطاهای ثبت خودکار پایان‌روز',
        'start loop='           => 'شروع/ری‌استارت Worker',
        'another worker instance' => 'اخطار نمونه موازی',
        'max runtime reached'   => 'خروج تمیز (max-minutes)',
        'memory limit reached'  => 'خروج تمیز (حافظه)',
        '[cycle]'               => 'خطاهای چرخشی',
    ] as $needle => $label) {
        $info($label . ': ' . substr_count($joined, $needle) . ' بار');
    }
} else {
    $warn('لاگ امروز پیدا نشد: ' . basename($logToday) . ' — احتمال دارد Worker امروز اصلاً بالا نیامده باشد.');
}

foreach (['app.log', 'error.log'] as $name) {
    $p = $root . '/storage/logs/' . $name;
    if (!is_file($p)) {
        continue;
    }
    $c = @file_get_contents($p);
    if ($c === false) {
        continue;
    }
    $all = [];
    foreach (explode("\n", $c) as $l) {
        if (str_contains($l, '[food-print] FAILED')) {
            $all[] = $l;
        }
    }
    $todayCount = 0;
    foreach ($all as $l) {
        if (str_contains($l, $tehranToday)) {
            $todayCount++;
        }
    }
    $info("storage/logs/{$name}: [food-print] FAILED کل=" . count($all) . ' | امروز(تهران)=' . $todayCount);
    foreach (array_slice($all, -10) as $l) {
        $line('   ' . mb_substr($l, 0, 260));
    }
}

// ── ۳ب) probe قفل‌ها ─────────────────────────────────────────────────
// اگر قفل print-queue در دست کسی باشد، Worker ساکت (بدون لاگ) صف را تخلیه نمی‌کند.
$line("\n۳ب) probe قفل‌ها (بدون نگه‌داشتن قفل)");
foreach (['food-print-queue.lock' => 'قفل صف چاپ', 'food-ticket.lock' => 'قفل retry دستی'] as $lf => $label) {
    $p = $root . '/storage/' . $lf;
    $fh = @fopen($p, 'c');
    if (!$fh) {
        $warn($label . ': فایل باز نشد');
        continue;
    }
    if (@flock($fh, LOCK_EX | LOCK_NB)) {
        @flock($fh, LOCK_UN);
        fclose($fh);
        $ok($label . ': آزاد است');
    } else {
        fclose($fh);
        $bad($label . ': هم‌اکنون قفل است — یک پروسهٔ گیرکرده آن را نگه داشته؛ تخلیهٔ صف چاپ تا آزادی قفل بی‌نتیجه است.');
    }
}
// food-worker-process.lock نمونهٔ عادی است که Worker زنده آن را نگه می‌دارد؛
// «قفل است» در اینجا یعنی Worker حداقل یک‌بار اجرا شده (نشانهٔ خوب)؛ «آزاد» یعنی Worker زنده نیست.
$fh = @fopen($root . '/storage/food-worker-process.lock', 'c');
if ($fh) {
    if (@flock($fh, LOCK_EX | LOCK_NB)) {
        @flock($fh, LOCK_UN);
        fclose($fh);
        $warn('قفل نمونهٔ Worker آزاد است — یعنی هیچ Worker در حال اجرایی آن را نگه نداشته (Worker زنده نیست؟)');
    } else {
        fclose($fh);
        $ok('قفل نمونهٔ Worker در دست Worker زنده است (رفتار عادی).');
    }
}

// ── ۴) دیتابیس (فقط SELECT) ───────────────────────────────────────────
$line("\n۴) دیتابیس");
$dbOk = true;
try {
    db()->query('SELECT 1');
    $info('اتصال دیتابیس برقرار است | نام پایگاه: ' . (string) db()->query('SELECT DATABASE()')->fetchColumn());
} catch (Throwable $e) {
    $dbOk = false;
    $bad('اتصال دیتابیس برقرار نشد: ' . $e->getMessage());
}

$configRT = null;
try {
    $configRT = function_exists('food_ticket_config') ? food_ticket_config(true) : null;
} catch (Throwable $e) {
    $warn('خواندن food_ticket_config با خطا مواجه شد: ' . $e->getMessage());
}

if ($dbOk) {
    // مهاجرت‌ها
    if (diag_table_exists('schema_updates')) {
        try {
            $migrations = db()->query('SELECT migration FROM schema_updates ORDER BY migration')->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $line('  مهاجرت‌های ثبت‌شده (' . count($migrations) . ' عدد):');
            foreach (['031', '035', '036', '037', '038'] as $w) {
                $hit = array_filter($migrations, static fn ($m) => str_starts_with((string) $m, $w));
                $hit ? $ok("مهاجرت {$w}* ثبت شده است") : $warn("مهاجرت {$w}* ثبت نشده است");
            }
            $info('فهرست کامل: ' . implode(', ', array_map('strval', $migrations)));
        } catch (Throwable $e) {
            $warn('خواندن schema_updates: ' . $e->getMessage());
        }
    } else {
        $warn('جدول schema_updates وجود ندارد.');
    }

    // تعطیلی امروز
    if (diag_table_exists('holidays')) {
        try {
            $st = db()->prepare('SELECT title FROM holidays WHERE holiday_date = ? AND is_active = 1 LIMIT 1');
            $st->execute([$tehranToday]);
            $holidayTitle = trim((string) ($st->fetchColumn() ?: ''));
            $weekend = in_array((int) $tehranNow->format('N'), [4, 5], true);
            if ($holidayTitle !== '') {
                $bad("امروز ({$tehranToday}) «تعطیل رسمی» ثبت است: «{$holidayTitle}» — با پنجرهٔ فعال، Worker هیچ چاپی انجام نمی‌دهد.");
            } elseif ($weekend) {
                $warn('امروز پنج‌شنبه/جمعه (تعطیلی آخر هفته) است — پنجره بسته است.');
            } else {
                $ok('امروز (' . ($dayNames[(int) $tehranNow->format('N')] ?? '?') . ') تعطیل رسمی ثبت نشده است.');
            }
        } catch (Throwable $e) {
            $warn('خواندن جدول holidays: ' . $e->getMessage());
        }
    } else {
        $info('جدول holidays وجود ندارد.');
    }

    // تنظیمات خام دیتابیس
    if (diag_table_exists('food_ticket_config')) {
        $cfgCols = diag_columns('food_ticket_config');
        $want = ['enabled', 'printer_mode', 'printer_host', 'printer_port', 'printer_share', 'poll_seconds', 'max_batch',
                 'window_enabled', 'window_days', 'window_start', 'window_end', 'window_margin_before_minutes', 'window_margin_after_minutes',
                 'idle_poll_seconds', 'active_poll_ms', 'persistent_source_connection', 'cut_source_rows', 'updated_by'];
        $sel = array_values(array_intersect($want, $cfgCols));
        $line('  تنظیمات food_ticket_config (خام دیتابیس):');
        try {
            $row = db()->query('SELECT ' . implode(',', array_map(static fn ($c) => '`' . $c . '`', $sel)) . ' FROM food_ticket_config WHERE id = 1')->fetch() ?: [];
            foreach (diag_mask_row($row) as $k => $v) {
                $line('        ' . $k . ' = ' . (string) $v);
            }
        } catch (Throwable $e) {
            $warn('خواندن food_ticket_config: ' . $e->getMessage());
        }
        if (!in_array('window_enabled', $cfgCols, true)) {
            $warn('ستون‌های پنجرهٔ کاری (مهاجرت ۰۳۱) روی جدول food_ticket_config نیستند — پنجره‌ای فعال نیست و Worker ۲۴/۷ کار می‌کند.');
        }
    } else {
        $bad('جدول food_ticket_config وجود ندارد!');
    }

    // وضعیت پنجره — دقیقاً همان تابعی که Worker صدا می‌زند
    if ($configRT !== null && function_exists('food_ticket_window_state')) {
        try {
            $ws = food_ticket_window_state($configRT, $tehranNow);
            $line('  ── وضعیت پنجرهٔ کاری الان (همان تابعِ Worker) ──');
            $line('        open=' . ($ws['open'] ? 'true' : 'false') . ' | enabled=' . ($ws['enabled'] ? 'true' : 'false') . ' | reason_code=' . (string) ($ws['reason_code'] ?? ''));
            $line('        reason: ' . (string) ($ws['reason'] ?? ''));
            $line('        بازه: ' . (string) ($ws['start'] ?? '') . ' تا ' . (string) ($ws['end'] ?? '') . ' | بازشدن بعدی: ' . (string) ($ws['next_open_label'] ?? '-'));
            if (!$ws['enabled']) {
                $ok('پنجره غیرفعال است ⇒ Worker شبانه‌روز فعال است.');
            } elseif ($ws['open']) {
                $ok('الان داخل پنجره هستیم ⇒ Worker باید در حال کار باشد.');
            } else {
                $bad('الان پنجره بسته است ⇒ با کد ۱٫۶۲٫۹۵ به بعد، Worker هیچ‌کاری نمی‌کند (حالت سختگیرانه).');
            }
        } catch (Throwable $e) {
            $warn('محاسبهٔ وضعیت پنجره: ' . $e->getMessage());
        }
    }

    // رویدادهای دیروز/امروز/۷ روز گذشته
    $evCols = diag_table_exists('food_ticket_events') ? diag_columns('food_ticket_events') : [];
    if ($evCols !== []) {
        $has = static fn (string $c): bool => in_array($c, $evCols, true);

        $line('  ── هفت روز گذشته (punch_date × print_status) ──');
        try {
            $rows = db()->query('SELECT punch_date, COALESCE(print_status, \'(null)\') AS st, COUNT(*) AS c FROM food_ticket_events WHERE punch_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY punch_date, print_status ORDER BY punch_date DESC, st')->fetchAll();
            if ($rows === []) {
                $info('هیچ رویدادی در ۷ روز گذشته ثبت نشده است!');
            }
            foreach ($rows as $r) {
                $line('        ' . $r['punch_date'] . ' | ' . str_pad((string) $r['st'], 12) . ' | ' . $r['c']);
            }
        } catch (Throwable $e) {
            $warn('گزارش هفتگی رویدادها: ' . $e->getMessage());
        }

        try {
            $st = db()->prepare('SELECT COALESCE(print_status, \'(null)\') AS st, COUNT(*) AS c FROM food_ticket_events WHERE punch_date = ? GROUP BY print_status');
            $st->execute([$tehranToday]);
            $agg = [];
            foreach ($st->fetchAll() as $r) {
                $agg[$r['st']] = (int) $r['c'];
            }
            $line('  امروز (' . $tehranToday . '): ' . json_encode($agg, JSON_UNESCAPED_UNICODE));
            $pending = (int) ($agg['pending'] ?? 0);
            if ($pending > 0) {
                $old = db()->query("SELECT MIN(updated_at) AS oldest FROM food_ticket_events WHERE print_status = 'pending'")->fetch();
                $ageMin = !empty($old['oldest']) ? round((time() - strtotime((string) $old['oldest'])) / 60, 1) : null;
                $bad("{$pending} ردیف «در صف چاپ» باقی مانده‌اند؛ قدیمی‌ترین به‌روزرسانی: " . ($ageMin !== null ? $ageMin . ' دقیقه پیش' : 'نامشخص') . '. Worker آن‌ها را برنمی‌دارد.');
            }
            $printing = (int) ($agg['printing'] ?? 0);
            if ($printing > 0) {
                $warn("{$printing} ردیف در وضعیت «printing» گیر کرده‌اند.");
            }
        } catch (Throwable $e) {
            $warn('آمار امروز: ' . $e->getMessage());
        }

        if ($has('last_error')) {
            try {
                $sel = ['id', 'punch_time', 'print_status'];
                foreach (['retry_count', 'print_attempts', 'next_retry_at', 'last_error'] as $c) {
                    if ($has($c)) {
                        $sel[] = $c;
                    }
                }
                $rows = db()->query('SELECT ' . implode(',', $sel) . " FROM food_ticket_events WHERE print_status IN ('print_error','failed') ORDER BY id DESC LIMIT 10")->fetchAll();
                $line('  ── آخرین ۱۰ ردیف خطای چاپ ──');
                if ($rows === []) {
                    $ok('هیچ ردیف print_error/failed وجود ندارد.');
                }
                foreach ($rows as $r) {
                    $line('        #' . $r['id'] . ' ' . ($r['punch_time'] ?? '') . ' | status=' . $r['print_status']
                        . (isset($r['retry_count']) ? ' | retry=' . $r['retry_count'] : '')
                        . (isset($r['print_attempts']) ? ' | attempts=' . $r['print_attempts'] : '')
                        . (isset($r['next_retry_at']) ? ' | next_retry=' . $r['next_retry_at'] : '')
                        . ' | ' . mb_substr((string) ($r['last_error'] ?? ''), 0, 220));
                }
            } catch (Throwable $e) {
                $warn('خواندن last_error: ' . $e->getMessage());
            }
        }

        try {
            $stuck = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status = 'printing' AND updated_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)")->fetchColumn();
            if ($stuck > 0) {
                $warn("{$stuck} ردیف «printing» بیش از ۳۰ دقیقه گیر کرده‌اند (احتمال قطع Worker وسط چاپ).");
            }
        } catch (Throwable) {
        }
    } else {
        $bad('جدول food_ticket_events وجود ندارد!');
    }

    // لاگ چاپ
    if (diag_table_exists('food_ticket_print_log')) {
        try {
            $cols = diag_columns('food_ticket_print_log');
            $sel = array_values(array_intersect(['id', 'event_id', 'printer', 'status', 'retry_count', 'message', 'created_at'], $cols));
            if ($sel !== []) {
                $order = in_array('id', $cols, true) ? 'id' : $sel[0];
                $rows = db()->query('SELECT ' . implode(',', $sel) . ' FROM food_ticket_print_log ORDER BY ' . $order . ' DESC LIMIT 12')->fetchAll();
                $line('  ── آخرین ۱۲ رکورد food_ticket_print_log ──');
                foreach ($rows as $r) {
                    $line('        ' . json_encode(diag_mask_row($r), JSON_UNESCAPED_UNICODE));
                }
            }
        } catch (Throwable $e) {
            $warn('خواندن food_ticket_print_log: ' . $e->getMessage());
        }
    } else {
        $info('جدول food_ticket_print_log وجود ندارد.');
    }
}

// ── ۵) چاپگر (فقط تست اتصال، بدون چاپ) ──────────────────────────────
$line("\n۵) چاپگر");
$cfgSafe  = is_array($configRT) ? $configRT : [];
$mode     = (string) ($cfgSafe['printer_mode'] ?? 'tcp_raw');
$host     = trim((string) ($cfgSafe['printer_host'] ?? ''));
$port     = (int) ($cfgSafe['printer_port'] ?? 9100);
$share    = trim((string) ($cfgSafe['printer_share'] ?? ''));
$line('  mode=' . $mode . ' | host=' . ($host !== '' ? $host : '(خالی)') . ' | port=' . $port . ' | share=' . ($share !== '' ? $share : '(خالی)'));
if ($mode === 'tcp_raw') {
    if ($host === '') {
        $bad('printer_host خالی است — Worker/چاپ ساکت انجام نمی‌شود تا تنظیم نشود.');
    } else {
        $t = microtime(true);
        $errno = 0; $errstr = '';
        $fp = @fsockopen($host, $port, $errno, $errstr, 4.0);
        $ms = round((microtime(true) - $t) * 1000, 1);
        if (is_resource($fp)) {
            fclose($fp);
            $ok("اتصال TCP به {$host}:{$port} برقرار شد ({$ms} ms)");
        } else {
            $bad("اتصال TCP به {$host}:{$port} برقرار نشد ({$ms} ms): [{$errno}] {$errstr}");
        }
    }
} else {
    $share !== '' ? $info('حالت ویندوز/Share — دسترسی از سرور را جدا تست کنید: Test-NetConnection <host> -Port 445 و دسترسی به \\' . preg_replace('#^\\\\+#', '', $share)) : $bad('حالت ویندوز ولی printer_share خالی است.');
}

// ── ۶) منبع تردد (فقط وجود فایل) ──────────────────────────────────────
$line("\n۶) منبع تردد (فایل Access — فقط تست وجود، بدون باز کردن)");
$ap = trim((string) ($cfgSafe['attendance_path'] ?? ''));
if ($ap === '') {
    $bad('attendance_path تنظیم نشده است.');
} elseif (!is_file($ap)) {
    $bad('فایل تردد پیدا نشد: ' . $ap);
} else {
    $ok('فایل تردد موجود است: ' . $ap . ' (' . number_format((float) filesize($ap) / 1024, 1) . ' KB | mtime ' . date('Y-m-d H:i:s', filemtime($ap)) . ')');
}

// ── ۴ب) تحلیل عمیق «در صف چاپ» ────────────────────────────────────────
$line("\n۴ب) تحلیل عمیق صف چاپ (فقط‌خواندنی)");
if ($dbOk && diag_table_exists('food_ticket_events')) {
    $evCols2 = diag_columns('food_ticket_events');
    $has2 = static fn (string $c): bool => in_array($c, $evCols2, true);
    try {
        $claimableWhere = "(print_status IN (\"pending\",\"print_error\",\"failed\") OR (print_status = \"printing\" AND updated_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)))"
            . ' AND (next_retry_at IS NULL OR next_retry_at <= NOW())';
        $dueAll    = (int) db()->query('SELECT COUNT(*) FROM food_ticket_events WHERE ' . $claimableWhere)->fetchColumn();
        $duePending = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status = 'pending' AND (next_retry_at IS NULL OR next_retry_at <= NOW())")->fetchColumn();
        $nullRetry = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status = 'pending' AND next_retry_at IS NULL")->fetchColumn();
        $futureRetry = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status = 'pending' AND next_retry_at IS NOT NULL AND next_retry_at > NOW()")->fetchColumn();
        $totalPend = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status = 'pending'")->fetchColumn();
        $line("  pending کل={$totalPend} | بدون next_retry={$nullRetry} | next_retry آینده={$futureRetry} | pending واجد شرایط={$duePending} | کل قابل‌ادعا (همهٔ وضعیت‌ها)={$dueAll}");
        if ($futureRetry > 0) {
            $mx = db()->query("SELECT MAX(next_retry_at) FROM food_ticket_events WHERE print_status = 'pending' AND next_retry_at IS NOT NULL")->fetchColumn();
            $warn('بیشترین next_retry_at در pending: ' . $mx);
        }
        if ($duePending > 0) {
            $bad("{$duePending} ردیف pending واجد شرایط‌اند — Worker باید آن‌ها را همین الان چاپ کند؛ اگر چاپ نمی‌کند، علت در قفل صف/مسیر اجراست.");
        }
    } catch (Throwable $e) {
        $warn('تحلیل واجد شرایط: ' . $e->getMessage());
    }
    if ($has2('retry_count')) {
        try {
            $rows = db()->query("SELECT retry_count, COUNT(*) AS c FROM food_ticket_events WHERE print_status = 'pending' GROUP BY retry_count ORDER BY retry_count")->fetchAll();
            $rep = [];
            foreach ($rows as $r) {
                $rep[(string) $r['retry_count']] = (int) $r['c'];
            }
            $line('  retry_count در pending: ' . json_encode($rep, JSON_UNESCAPED_UNICODE));
        } catch (Throwable $e) {
            $warn('توزیع retry: ' . $e->getMessage());
        }
    }
    try {
        $rows = db()->query("SELECT COALESCE(event_type,'?') AS et, COALESCE(meal_type,'?') AS meal, COUNT(*) AS c FROM food_ticket_events WHERE print_status = 'pending' GROUP BY event_type, meal_type ORDER BY c DESC")->fetchAll();
        $line('  pending بر اساس event_type / meal:');
        foreach ($rows as $r) {
            $line('        ' . $r['et'] . ' | ' . $r['meal'] . ' | ' . $r['c']);
        }
    } catch (Throwable $e) {
        $warn('تفکیک event_type: ' . $e->getMessage());
    }
    if ($has2('source_uid')) {
        try {
            $rows = db()->query("SELECT CASE WHEN source_uid LIKE 'manual%' THEN 'manual' WHEN source_uid LIKE 'auto%' THEN 'auto' ELSE COALESCE(source_uid,'?') END AS grp, COUNT(*) AS c FROM food_ticket_events WHERE print_status = 'pending' GROUP BY grp ORDER BY c DESC")->fetchAll();
            $rep = [];
            foreach ($rows as $r) {
                $rep[(string) $r['grp']] = (int) $r['c'];
            }
            $line('  pending بر اساس منبع: ' . json_encode($rep, JSON_UNESCAPED_UNICODE));
        } catch (Throwable $e) {
            $warn('تفکیک منبع: ' . $e->getMessage());
        }
    }
    try {
        $st = db()->query("SELECT MIN(created_at) AS oldest, MAX(created_at) AS newest FROM food_ticket_events WHERE print_status = 'pending'")->fetch();
        $line('  created_at اولین/آخرین pending: ' . ($st['oldest'] ?? '?') . ' / ' . ($st['newest'] ?? '?'));
    } catch (Throwable) {
    }
    try {
        $st = db()->query("SELECT COUNT(*) AS c, MIN(updated_at) AS oldest FROM food_ticket_events WHERE print_status = 'printing'")->fetch();
        $c = (int) ($st['c'] ?? 0);
        $c > 0 ? $warn('ردیف‌های printing مانده: ' . $c . ' (قدیمی‌ترین: ' . ($st['oldest'] ?? '?') . ')') : $ok('هیچ ردیف printing مانده‌ای نیست.');
    } catch (Throwable) {
    }
    try {
        $rows = db()->query("SELECT HOUR(punch_time) AS hr, print_status, COUNT(*) AS c FROM food_ticket_events WHERE punch_date = '" . $tehranToday . "' GROUP BY HOUR(punch_time), print_status ORDER BY hr")->fetchAll();
        $byHour = [];
        foreach ($rows as $r) {
            $byHour[(int) $r['hr']][(string) $r['print_status']] = (int) $r['c'];
        }
        ksort($byHour);
        $line('  امروز به تفکیک ساعت (TIME):');
        foreach ($byHour as $hr => $agg) {
            $line('        ' . str_pad((string) $hr, 2, '0', STR_PAD_LEFT) . ':00 → ' . json_encode($agg, JSON_UNESCAPED_UNICODE));
        }
    } catch (Throwable $e) {
        $warn('تفکیک ساعتی: ' . $e->getMessage());
    }
}
// لاگ چاپ امروز به تفکیک وضعیت
if ($dbOk && diag_table_exists('food_ticket_print_log')) {
    try {
        $plc = diag_columns('food_ticket_print_log');
        $createdCol = in_array('created_at', $plc, true) ? 'created_at' : null;
        if ($createdCol && in_array('status', $plc, true)) {
            $rows = db()->query("SELECT COALESCE(status,'?') AS st, COUNT(*) AS c FROM food_ticket_print_log WHERE DATE({$createdCol}) = CURDATE() GROUP BY status")->fetchAll();
            $rep = [];
            foreach ($rows as $r) {
                $rep[(string) $r['st']] = (int) $r['c'];
            }
            $line('  food_ticket_print_log امروز به تفکیک status: ' . json_encode($rep, JSON_UNESCAPED_UNICODE));
        }
    } catch (Throwable $e) {
        $warn('تحلیل print_log: ' . $e->getMessage());
    }
}

// ── ۴پ) ردیابی «فیش گروهی» پشت ردیف‌های pending ──────────────────────
// فرضیهٔ اصلی: ردیف‌های pending اعضای اجراهای گروهی‌اند که به وضعیت done نرسیده‌اند؛
// مسیر چاپ گروهی برای runهای غیر done ساکت برمی‌گردد (هیچ لاگی نمی‌زند).
$line("\n۴پ) ردیابی «فیش گروهی» پشت ردیف‌های pending");
if ($dbOk && diag_table_exists('food_ticket_events')) {
    try {
        $rows = db()->query('SELECT source_payload LIKE \'%"run_id":%\' AS has_run, COUNT(*) AS c FROM food_ticket_events WHERE print_status = \'pending\' GROUP BY source_payload LIKE \'%"run_id":%\'')->fetchAll();
        foreach ($rows as $r) {
            $line('  pending با run_id در payload: ' . ((int) $r['has_run'] === 1 ? 'بله' : 'خیر') . ' → ' . $r['c'] . ' ردیف');
        }
    } catch (Throwable $e) {
        $warn('تفکیک run_id: ' . $e->getMessage());
    }
    $runIds = [];
    try {
        $rows = db()->query('SELECT DISTINCT CAST(JSON_UNQUOTE(JSON_EXTRACT(source_payload, \'$.run_id\')) AS UNSIGNED) AS rid FROM food_ticket_events WHERE print_status = \'pending\' AND source_payload LIKE \'%"run_id":%\'')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($rows as $rid) {
            if ((int) $rid > 0) {
                $runIds[] = (int) $rid;
            }
        }
        sort($runIds);
        $line('  run_id های درگیر: ' . ($runIds ? implode(', ', $runIds) : '(هیچ)'));
    } catch (Throwable $e) {
        $warn('JSON_EXTRACT روی run_id: ' . $e->getMessage());
    }
    if ($runIds !== [] && diag_table_exists('food_ticket_group_runs')) {
        $marks = implode(',', array_fill(0, count($runIds), '?'));
        try {
            $st = db()->prepare('SELECT id, group_title, run_kind, status, members_total, printed_count, repeat_count, no_food_count, error_count, punch_date, punch_time FROM food_ticket_group_runs WHERE id IN (' . $marks . ')');
            $st->execute($runIds);
            $runRows = $st->fetchAll() ?: [];
            $line('  وضعیت اجراهای گروهی مالک این pendingها:');
            $stuck = 0;
            foreach ($runRows as $r) {
                if ((string) $r['status'] !== 'done') {
                    $stuck++;
                }
                $line('    run #' . $r['id'] . ' | ' . mb_substr((string) ($r['group_title'] ?? ''), 0, 40)
                    . ' | kind=' . $r['run_kind'] . ' | status=' . $r['status']
                    . ' | اعضا=' . $r['members_total'] . ' | printed=' . $r['printed_count']
                    . ' | no_food=' . $r['no_food_count'] . ' | repeat=' . $r['repeat_count']
                    . ' | error=' . $r['error_count'] . ' | ' . $r['punch_date'] . ' ' . $r['punch_time']);
            }
            if ($stuck > 0) {
                $bad("runهای status != done: {$stuck} اجرا — فیش‌های اعضای این اجراها به همین دلیل ساکت چاپ نمی‌شوند (قانون کد: run باید done باشد).");
            }
            $missing = count($runIds) - count($runRows);
            if ($missing > 0) {
                $bad("{$missing} run_id به هیچ اجرایی در food_ticket_group_runs وصل نیست — دادهٔ ناسازگار.");
            }
        } catch (Throwable $e) {
            $warn('خواندن food_ticket_group_runs: ' . $e->getMessage());
        }
    } elseif ($runIds !== []) {
        $warn('جدول food_ticket_group_runs وجود ندارد.');
    }
    try {
        $rows = db()->query('SELECT COALESCE(status, \'?\') AS st, COUNT(*) AS c FROM food_ticket_group_runs WHERE punch_date = \'' . $tehranToday . '\' GROUP BY status')->fetchAll();
        $rep = [];
        foreach ($rows as $r) {
            $rep[(string) $r['st']] = (int) $r['c'];
        }
        $line('  اجراهای گروهی امروز به تفکیک status: ' . json_encode($rep, JSON_UNESCAPED_UNICODE));
    } catch (Throwable $e) {
        $warn('وضعیت اجراهای امروز: ' . $e->getMessage());
    }
}
// [food-groups] در app.log (اگر خطای عضوی رخ داده باشد اینجا ثبت می‌شود)
$line("  ── خطوط [food-groups] در app.log امروز ──");
$appLog = $root . '/storage/logs/app.log';
if (is_file($appLog)) {
    $c = (string) @file_get_contents($appLog);
    $hits = [];
    foreach (explode("\n", $c) as $l) {
        if (str_contains($l, '[food-groups]')) {
            $hits[] = $l;
        }
    }
    $hitsToday = array_filter($hits, static fn ($l) => str_contains($l, $tehranToday));
    $info('تعداد [food-groups] (کل): ' . count($hits) . ' | امروز: ' . count($hitsToday));
    foreach (array_slice($hits, -10) as $l) {
        $line('   | ' . mb_substr((string) $l, 0, 300));
    }
}

// ── ۴ت) دقیقاً همان کوئری انتخاب Worker (read-only؛ LIMIT 30) ─────────
// هدف: ببینیم ۳۰ ردیف قدیمیِ قابل‌ادعا کدام‌اند و طبق شاخه‌های کد چه سرنوشتی دارند.
$line("\n۴ت) نتیجهٔ واقعیِ کوئری انتخاب Worker (LIMIT 30، مرتب id ASC)");
if ($dbOk && diag_table_exists('food_ticket_events')) {
    $hasDeliveryCol = false;
    try {
        $cols = db()->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_events' AND COLUMN_NAME = 'delivery_status'")->fetchColumn();
        $hasDeliveryCol = ((int) $cols) > 0;
        $info('ستون delivery_status در جدول events: ' . ($hasDeliveryCol ? 'موجود است' : 'وجود ندارد'));
    } catch (Throwable $e) {
        $warn('بررسی ستون delivery_status: ' . $e->getMessage());
    }
    try {
        $claimableSql = '(print_status IN ("pending", "print_error", "failed") OR (print_status = "printing" AND updated_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)))';
        $total = db()->query('SELECT COUNT(*) FROM food_ticket_events WHERE ' . $claimableSql . ' AND (next_retry_at IS NULL OR next_retry_at <= NOW())')->fetchColumn();
        $line('  کل قابل‌ادعا (کوئری کد): ' . (int) $total);
        $sql = 'SELECT id, print_status, event_type, retry_count, print_attempts, next_retry_at, created_at'
            . ($hasDeliveryCol ? ', delivery_status' : '')
            . ', (source_payload LIKE \'%"run_id":%\') AS has_run'
            . ' FROM food_ticket_events WHERE ' . $claimableSql
            . ' AND (next_retry_at IS NULL OR next_retry_at <= NOW()) ORDER BY id ASC LIMIT 30';
        $sel = db()->query($sql)->fetchAll();
        $line('  ردیف‌های برگزیدهٔ Worker این اجرا (' . count($sel) . ' ردیف):');
        $skipRetry = 0;
        $attemptable = 0;
        $runRows = 0;
        $delivered = 0;
        foreach ($sel as $r) {
            $retry = max((int) ($r['retry_count'] ?? 0), (int) ($r['print_attempts'] ?? 0));
            $delivery = (string) ($r['delivery_status'] ?? 'pending');
            $pred = '';
            if ((int) ($r['has_run'] ?? 0) === 1) {
                $pred = 'مسیر فیش گروهی';
                $runRows++;
            } elseif ($hasDeliveryCol && $delivery === 'delivered') {
                $pred = 'guard تحویل‌شده → چاپ نمی‌شود، printed علامت می‌خورد';
                $delivered++;
            } elseif ($retry >= 5) {
                $pred = 'retry>=5 → skip بی‌صدا';
                $skipRetry++;
            } else {
                $pred = 'قابل چاپ (claim باید موفق شود)';
                $attemptable++;
            }
            $line('    #' . $r['id'] . ' | ' . ($r['print_status'] ?? '?') . '/' . ($r['event_type'] ?? '?')
                . ' | retry=' . ($r['retry_count'] ?? '?') . ' att=' . ($r['print_attempts'] ?? '?')
                . ' | delivery=' . $delivery . ' | run=' . ($r['has_run'] ? 'بله' : 'خیر')
                . ' | next_retry=' . ($r['next_retry_at'] ?? 'NULL') . ' | ' . ($r['created_at'] ?? '')
                . '  ⇒ ' . $pred);
        }
        $line('  خلاصه: قابل‌چاپ=' . $attemptable . ' | retry>=5 (skip)=' . $skipRetry
            . ' | مسیر گروهی=' . $runRows . ' | delivery-guard=' . $delivered);
        if ($sel === []) {
            $info('صف برگزیده خالی است (در این لحظه چیزی برای چاپ نیست یا همهٔ ردیف‌ها شرط next_retry را رعایت نمی‌کنند).');
        } elseif ($attemptable === 0 && $runRows === 0) {
            $bad('همهٔ ردیف‌های برگزیده یا retry≥5 هستند یا delivery-guard دارند ⇒ Worker چیزی claim نمی‌کند (head-of-line starvation محتمل است).');
        } elseif ($attemptable === 0 && $runRows > 0) {
            $warn('برگزیده‌ها فقط از مسیر گروهی‌اند؛ چاپشان وابسته به وضعیت اجرای گروهی است.');
        } else {
            $info('حداقل ' . $attemptable . ' ردیفِ برگزیده باید همین حالا claim/print شوند؛ اگر نمی‌شوند مشکل جای دیگری است.');
        }
        // توزیع وضعیتِ کل صف قابل‌ادعا
        $rows = db()->query('SELECT print_status, COUNT(*) AS c FROM food_ticket_events WHERE ' . $claimableSql
            . ' AND (next_retry_at IS NULL OR next_retry_at <= NOW()) GROUP BY print_status')->fetchAll();
        $rep = [];
        foreach ($rows as $r) {
            $rep[(string) $r['print_status']] = (int) $r['c'];
        }
        $line('  توزیع کل صف قابل‌ادعا بر اساس print_status: ' . json_encode($rep, JSON_UNESCAPED_UNICODE));
        $rows = db()->query('SELECT SUM(CASE WHEN GREATEST(COALESCE(retry_count,0), COALESCE(print_attempts,0)) >= 5 THEN 1 ELSE 0 END) AS dead, COUNT(*) AS total FROM food_ticket_events WHERE ' . $claimableSql
            . ' AND (next_retry_at IS NULL OR next_retry_at <= NOW())')->fetch();
        $line('  ردیف‌های retry≥5 (غیرقابل‌چاپ با منطق فعلی): ' . (int) ($rows['dead'] ?? 0) . ' از ' . (int) ($rows['total'] ?? 0));
        if ($hasDeliveryCol) {
            $cnt = db()->query('SELECT COUNT(*) FROM food_ticket_events WHERE ' . $claimableSql
                . ' AND (next_retry_at IS NULL OR next_retry_at <= NOW()) AND delivery_status = "delivered"')->fetchColumn();
            $line('  ردیف‌های delivery_status=delivered در صف: ' . (int) $cnt);
        }
    } catch (Throwable $e) {
        $warn('کوئری شبیه‌سازی انتخاب: ' . $e->getMessage());
    }
}

// ── ۷) پروسه‌های php/httpd با زمان شروع ──────────────────────────────
$line("\n۷) پروسه‌های php.exe و httpd.exe (+ زمان بوت سرور)");
if (stripos(PHP_OS, 'WIN') === 0 && function_exists('shell_exec')) {
    $outU = @shell_exec('powershell -NoProfile -Command "(Get-CimInstance Win32_OperatingSystem).LastBootUpTime.ToString(\'yyyy-MM-dd HH:mm:ss\')" 2>&1');
    if ($outU !== null && trim((string) $outU) !== '' && !str_contains((string) $outU, 'Exception')) {
        $info('آخرین روشن‌شدن سرور: ' . trim((string) $outU));
    }
    $outP = @shell_exec('powershell -NoProfile -Command "Get-CimInstance Win32_Process -Filter \"Name=\'php.exe\' OR Name=\'httpd.exe\'\" | Select-Object ProcessId,Name,@{n=\'Start\';e={$_.CreationDate.ToString(\'yyyy-MM-dd HH:mm:ss\')}},@{n=\'Cmd\';e={if($_.CommandLine){$_.CommandLine -replace \'\\s+\',\' \'}}} | Format-List | Out-String -Width 500" 2>&1');
    if ($outP !== null && trim((string) $outP) !== '' && !str_contains((string) $outP, 'Exception')) {
        foreach (preg_split('/\r\n|\n|\r/', (string) $outP) as $l) {
            if (trim($l) !== '') {
                $line('    ' . mb_substr($l, 0, 320));
            }
        }
    } else {
        // راه دوم: جدول فرمت‌شده
        $outP2 = @shell_exec('wmic process where "Name=\'php.exe\' or Name=\'httpd.exe\'" get ProcessId,Name,CommandLine /FORMAT:LIST 2>&1');
        $line('    ' . mb_substr((string) $outP2, 0, 3000));
    }
} else {
    $info('بررسی پروسه‌ها فقط روی ویندوز انجام می‌شود.');
}

// ── ۸) لاگ امروز سیستم (۱۵ خط آخر app.log + خط خام آخر worker + همهٔ شروع‌ها) ──
// خط کامل آخر را بدون برش چاپ می‌کنیم تا کلید "queue" داخل JSON دیده شود.
$line("\n۸) آخرین ۱۵ خط app.log (همهٔ محتوا)");
foreach (['app.log'] as $name) {
    $p = $root . '/storage/logs/' . $name;
    if (!is_file($p)) {
        $info('app.log وجود ندارد.');
        break;
    }
    $c = @file_get_contents($p);
    if ($c === false) {
        $warn('app.log خوانده نشد.');
        break;
    }
    $line('  حجم app.log: ' . number_format(filesize($p) / 1024, 1) . ' KB');
    $nonEmpty = array_filter(preg_split('/\r\n|\n|\r/', $c) ?: [], static fn ($l) => trim((string) $l) !== '');
    foreach (array_slice($nonEmpty, -15) as $l) {
        $line('   | ' . mb_substr((string) $l, 0, 280));
    }
}

// خط خام آخر لاگ worker (بدون برش) — کلید "queue" داخل JSON دیده می‌شود
$line("\n۹) خط خام آخر لاگ worker امروز + همهٔ خطوط start loop");
if (is_file($logToday)) {
    $logLinesRaw = @file($logToday, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $lastRaw = '';
    foreach ($logLinesRaw as $l) {
        if (trim((string) $l) !== '') {
            $lastRaw = (string) $l;
        }
    }
    $line('  آخرین خط (خام، تا 4000 کاراکتر):');
    $line('   ' . mb_substr($lastRaw, 0, 4000));
    $line('  همهٔ خطوط start loop امروز:');
    foreach ($logLinesRaw as $l) {
        if (str_contains((string) $l, 'start loop=')) {
            $line('   ' . mb_substr((string) $l, 0, 180));
        }
    }
} else {
    $info('لاگ امروز موجود نیست.');
}

// ── ۱۰) جمع‌بندی ─────────────────────────────────────────────────────
$line("\n==================================================");
$line(" جمع‌بندی: FAIL = {$issues} | WARN = {$warns}");
$line(' خروجی کامل این اسکریپت را (بدون حذف بخش‌ها) برای تحلیلگر بفرستید.');
$line('==================================================');
