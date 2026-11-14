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

const DIAG_VERSION = '1.0.0';
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

// ── ۷) جمع‌بندی ─────────────────────────────────────────────────────
$line("\n==================================================");
$line(" جمع‌بندی: FAIL = {$issues} | WARN = {$warns}");
$line(' خروجی کامل این اسکریپت را (بدون حذف بخش‌ها) برای تحلیلگر بفرستید.');
$line('==================================================');
