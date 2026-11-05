<?php
/**
 * tools/system_loadtest.php — آزمون جامع سامانه با داده‌های ساختگی
 * ---------------------------------------------------------------------------
 * این اسکریپت سامانه را در محیط واقعی می‌سنجد و یک گزارش دوقسمتی می‌سازد:
 *   storage/logs/loadtest-report-<timestamp>.txt    ← خواندنی برای شما
 *   storage/logs/loadtest-report-<timestamp>.json   ← برای تحلیل دقیق
 *
 * ⚠️ قاعدهٔ ایمنی: این اسکریپت به‌صورت پیش‌فرض **هیچ چیزی نمی‌نویسد**.
 *    بدون سوییچ، فقط بررسی‌های فقط‌خواندنی را اجرا می‌کند.
 *
 * نحوهٔ اجرا (در پوشهٔ نصب):
 *   php tools\system_loadtest.php
 *       → حالت ایمن: فقط بررسی، هیچ داده‌ای ساخته نمی‌شود
 *
 *   php tools\system_loadtest.php --write --confirm
 *       → ۲۵۰ کاربر و درخواست آزمایشی می‌سازد و آزمون کامل را اجرا می‌کند
 *
 *   php tools\system_loadtest.php --cleanup --confirm
 *       → فقط همان ردیف‌هایی را که خودش ساخته پاک می‌کند (از روی فایل مانیفست)
 *
 * سوییچ‌های اختیاری:
 *   --users=250      تعداد کاربر آزمایشی (پیش‌فرض ۲۵۰)
 *   --tickets=500    تعداد درخواست آزمایشی (پیش‌فرض ۲ برابر کاربر)
 *   --prefix=zztest_ پیشوند نام کاربری آزمایشی (پیش‌فرض zztest_)
 *
 * هیچ دادهٔ موجودی خوانده‌نشده تغییر نمی‌کند و هیچ ردیف غیرآزمایشی حذف نمی‌شود.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

$START = microtime(true);

// ─────────────────────────── پارامترها ───────────────────────────
$opt = [
    'write'   => false,
    'cleanup' => false,
    'confirm' => false,
    'users'   => 250,
    'tickets' => 0,
    'prefix'  => 'zztest_',
];
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--write')        { $opt['write'] = true;   continue; }
    if ($arg === '--cleanup')      { $opt['cleanup'] = true; continue; }
    if ($arg === '--confirm')      { $opt['confirm'] = true; continue; }
    if (preg_match('/^--users=(\d+)$/', $arg, $m))   { $opt['users']   = (int) $m[1]; continue; }
    if (preg_match('/^--tickets=(\d+)$/', $arg, $m)) { $opt['tickets'] = (int) $m[1]; continue; }
    if (preg_match('/^--prefix=([A-Za-z0-9_]+)$/', $arg, $m)) { $opt['prefix'] = $m[1]; continue; }
    fwrite(STDERR, "سوییچ ناشناخته: {$arg}\n");
    exit(2);
}
if ($opt['tickets'] === 0) {
    $opt['tickets'] = $opt['users'] * 2;
}

// ─────────────────────────── بوت‌استرپ ───────────────────────────
$bootError = null;
try {
    require dirname(__DIR__) . '/bootstrap.php';
} catch (Throwable $e) {
    $bootError = $e;
}
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

$LOGDIR = APP_ROOT . '/storage/logs';
if (!is_dir($LOGDIR)) {
    @mkdir($LOGDIR, 0775, true);
}
$STAMP        = date('Ymd-His');
$MANIFEST     = $LOGDIR . '/loadtest-manifest.json';
$REPORT_TXT   = $LOGDIR . "/loadtest-report-{$STAMP}.txt";
$REPORT_JSON  = $LOGDIR . "/loadtest-report-{$STAMP}.json";

// ─────────────────────────── ساختار گزارش ───────────────────────────
$R = [
    'meta' => [
        'generated_at' => date('c'),
        'mode'         => $opt['cleanup'] ? 'cleanup' : ($opt['write'] ? 'write' : 'readonly'),
        'options'      => $opt,
        'script'       => 'system_loadtest.php',
        'script_rev'   => 1,
    ],
    'sections' => [],
];
$LINES = [];

function out(string $s = ''): void
{
    global $LINES;
    $LINES[] = $s;
    echo $s . PHP_EOL;
}
function head(string $t): void
{
    out('');
    out('══════════════════════════════════════════════════════════════');
    out('  ' . $t);
    out('══════════════════════════════════════════════════════════════');
}
/**
 * هم‌ترازی آگاه از UTF-8. sprintf('%-42s') بایت می‌شمارد و چون هر حرف فارسی
 * دو بایت است، ستون‌ها با برچسب فارسی به‌هم می‌ریزند.
 */
function pad(string $s, int $width): string
{
    $len = function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
    return $s . str_repeat(' ', max(1, $width - $len));
}
function row(string $label, $value, string $note = ''): void
{
    $v = is_bool($value) ? ($value ? 'بله' : 'خیر') : (string) $value;
    out('  ' . pad($label, 44) . $v . ($note !== '' ? '   ← ' . $note : ''));
}
function sect(string $key, array $data): void
{
    global $R;
    $R['sections'][$key] = $data;
}
/** اجرای ایمن یک بررسی: استثنا گزارش می‌شود ولی اسکریپت را متوقف نمی‌کند. */
function safe(callable $fn, $fallback = null)
{
    try {
        return $fn();
    } catch (Throwable $e) {
        return $fallback instanceof Closure ? $fallback($e) : ['__error' => $e->getMessage()];
    }
}

out('آزمون جامع سامانه — ' . date('Y-m-d H:i:s'));
out('حالت: ' . $R['meta']['mode'] . ($opt['write'] && !$opt['confirm'] ? '  (⚠️ بدون --confirm چیزی نوشته نمی‌شود)' : ''));

// ═════════════════ بخش A — محیط ═════════════════
head('A — محیط اجرا');
$envData = [
    'php_version'   => PHP_VERSION,
    'os'            => PHP_OS_FAMILY,
    'app_root'      => APP_ROOT,
    'boot_ok'       => $bootError === null,
    'boot_error'    => $bootError ? $bootError->getMessage() : null,
    'version_file'  => is_file(APP_ROOT . '/VERSION') ? trim((string) @file_get_contents(APP_ROOT . '/VERSION')) : null,
    'memory_limit'  => ini_get('memory_limit'),
    'max_execution' => ini_get('max_execution_time'),
    'extensions'    => [],
];
foreach (['pdo_mysql', 'mbstring', 'openssl', 'json', 'zip', 'gd', 'ldap', 'curl'] as $ext) {
    $envData['extensions'][$ext] = extension_loaded($ext);
}
row('نسخهٔ PHP', $envData['php_version']);
row('سیستم‌عامل', $envData['os']);
row('فایل VERSION', $envData['version_file'] ?? '(ندارد)');
row('بوت‌استرپ موفق', $envData['boot_ok'], $bootError ? $bootError->getMessage() : '');
row('memory_limit', $envData['memory_limit']);
out('  افزونه‌ها:');
foreach ($envData['extensions'] as $e => $ok) {
    out('     ' . pad($e, 14) . ($ok ? '✅' : '❌'));
}
sect('environment', $envData);

if ($bootError !== null) {
    out('');
    out('🔴 بوت‌استرپ شکست خورد؛ ادامهٔ آزمون ممکن نیست.');
    @file_put_contents($REPORT_TXT, implode(PHP_EOL, $LINES));
    @file_put_contents($REPORT_JSON, json_encode($R, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    exit(1);
}

// ═════════════════ بخش B — کاوش قابلیت‌ها ═════════════════
// این بخش مهم‌ترین قسمت برای تشخیص نسخه است: دقیقاً می‌گوید کدام
// اصلاحات روی این نصب حاضرند و کدام نیستند.
head('B — قابلیت‌های موجود روی این نصب');
$probe = [
    'db()'                          => 'اتصال پایگاه داده',
    'ticket_number_next'            => 'تولید شمارهٔ درخواست',
    'ticket_number_autofill'        => 'ترمیم خودکار شماره',
    'ticket_number_peek'            => 'پیش‌نمایش شمارهٔ بعدی',
    'ticket_number_sequence_floor'  => 'کف شمارندهٔ شماره',
    'ticket_handler_level'          => 'سطح رسیدگی (سه نما)',
    'ticket_view_mode'              => 'حالت نمایش صفحهٔ درخواست',
    'ticket_assignee_is_eligible'   => 'صلاحیت ارجاع',
    'sla_business_hours'            => 'ساعت کاری قابل تنظیم',
    'db_in_transaction'             => 'تشخیص تراکنش باز',
    'db_commit_or_fail'             => 'commit با پرتاب استثنا',
    'ticket_hot_schema_ensure'      => 'ترمیم اسکیمای درخواست',
    'logging_audit_run_and_record'  => 'ممیزی سلامت سامانه',
    'logging_audit_heartbeat_path'  => 'ضربان ممیزی',
    'app_session_enforce_idle'      => 'انقضای نشست بی‌استفاده',
    'csrf_token'                    => 'توکن CSRF',
    'system_log'                    => 'لاگ سامانه',
    'cfg'                           => 'خواندن تنظیمات',
];
$caps = [];
foreach ($probe as $fn => $label) {
    $name = rtrim($fn, '()');
    $exists = function_exists($name);
    $caps[$name] = $exists;
    row($label . "  [{$name}]", $exists ? '✅ هست' : '❌ نیست');
}
sect('capabilities', $caps);

// ═════════════════ بخش C — پایگاه داده و اسکیما ═════════════════
head('C — پایگاه داده و اسکیما');
$schema = safe(static function () {
    $pdo = db();
    $info = [
        'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'driver'         => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
        'tables'         => [],
        'ticket_columns' => [],
        'counts'         => [],
    ];
    foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $t) {
        $info['tables'][] = $t;
    }
    foreach ($pdo->query('SHOW COLUMNS FROM tickets')->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $info['ticket_columns'][$c['Field']] = [
            'type'    => $c['Type'],
            'null'    => $c['Null'],
            'default' => $c['Default'],
            'extra'   => $c['Extra'],
        ];
    }
    foreach (['users', 'tickets', 'ticket_messages', 'ticket_events', 'departments',
              'org_units', 'categories', 'service_catalog', 'system_logs', 'activity_logs'] as $t) {
        try {
            $info['counts'][$t] = (int) $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        } catch (Throwable $e) {
            $info['counts'][$t] = null;
        }
    }
    return $info;
});
if (isset($schema['__error'])) {
    out('  🔴 خطا: ' . $schema['__error']);
} else {
    row('نسخهٔ موتور', $schema['server_version']);
    row('تعداد جدول', count($schema['tables']));
    row('تعداد ستون جدول tickets', count($schema['ticket_columns']));
    out('  تعداد ردیف‌ها:');
    foreach ($schema['counts'] as $t => $n) {
        out('     ' . pad($t, 24) . ($n === null ? '(جدول نیست)' : number_format($n)));
    }
}
sect('schema', $schema);

// ═════════════════ بخش D — یکپارچگی شمارهٔ درخواست (فقط‌خواندنی) ═════════════════
// این بخش همان باگی را می‌سنجد که بیشترین اثر را روی کندی و داده داشت.
head('D — یکپارچگی شمارهٔ درخواست (فقط‌خواندنی)');
$numbers = safe(static function () {
    $pdo = db();
    $r = [];
    $r['total']            = (int) $pdo->query('SELECT COUNT(*) FROM tickets')->fetchColumn();
    $r['null_or_empty']    = (int) $pdo->query("SELECT COUNT(*) FROM tickets WHERE ticket_no IS NULL OR ticket_no = ''")->fetchColumn();
    $dup = $pdo->query('SELECT ticket_no, COUNT(*) c FROM tickets WHERE ticket_no IS NOT NULL AND ticket_no <> "" GROUP BY ticket_no HAVING c > 1 ORDER BY c DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
    $r['duplicate_groups'] = count($dup);
    $r['duplicate_sample'] = $dup;
    $r['duplicate_rows']   = (int) $pdo->query('SELECT COALESCE(SUM(c),0) FROM (SELECT COUNT(*) c FROM tickets WHERE ticket_no IS NOT NULL AND ticket_no <> "" GROUP BY ticket_no HAVING c > 1) x')->fetchColumn();
    try {
        $r['sequence_rows'] = $pdo->query('SELECT * FROM ticket_number_seq')->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $r['sequence_rows'] = ['__error' => $e->getMessage()];
    }
    // بالاترین شمارهٔ واقعی به تفکیک پیشوند، برای مقایسه با شمارنده
    $r['max_per_prefix'] = $pdo->query(
        "SELECT SUBSTRING_INDEX(ticket_no,'-',1) p, COUNT(*) c, MAX(CAST(SUBSTRING_INDEX(ticket_no,'-',-1) AS UNSIGNED)) mx
         FROM tickets WHERE ticket_no LIKE '%-%' GROUP BY p ORDER BY c DESC LIMIT 10"
    )->fetchAll(PDO::FETCH_ASSOC);
    return $r;
});
if (isset($numbers['__error'])) {
    out('  🔴 خطا: ' . $numbers['__error']);
} else {
    row('کل درخواست‌ها', number_format($numbers['total']));
    row('شمارهٔ خالی یا NULL', $numbers['null_or_empty'], $numbers['null_or_empty'] > 0 ? '🔴 نیاز به ترمیم' : '');
    row('گروه‌های شمارهٔ تکراری', $numbers['duplicate_groups'], $numbers['duplicate_groups'] > 0 ? '🔴 داده خراب است' : '✅');
    row('ردیف‌های درگیر تکرار', $numbers['duplicate_rows']);
    if (!empty($numbers['max_per_prefix'])) {
        out('  بیشترین شماره به تفکیک پیشوند:');
        foreach ($numbers['max_per_prefix'] as $p) {
            out('     ' . pad((string) $p['p'], 16) . 'تعداد ' . pad(number_format((int) $p['c']), 10) . 'بیشینه ' . $p['mx']);
        }
    }
    if (is_array($numbers['sequence_rows']) && !isset($numbers['sequence_rows']['__error'])) {
        out('  جدول شمارنده (ticket_number_seq):');
        foreach ($numbers['sequence_rows'] as $s) {
            out('     ' . json_encode($s, JSON_UNESCAPED_UNICODE));
        }
    }
}
sect('ticket_numbers', $numbers);

// ═════════════════ بخش E — کارایی صفحه‌ها (فقط‌خواندنی) ═════════════════
// الگوی کوئری‌های فهرست را با داده‌های واقعی زمان می‌گیرد تا N+1 دیده شود.
head('E — کارایی کوئری‌های فهرست (فقط‌خواندنی)');
$perf = safe(static function () {
    $pdo = db();
    $out = [];
    $bench = static function (string $label, string $sql) use ($pdo, &$out) {
        $t0 = microtime(true);
        $rows = 0;
        try {
            $st = $pdo->query($sql);
            $rows = count($st->fetchAll(PDO::FETCH_ASSOC));
            $err = null;
        } catch (Throwable $e) {
            $err = $e->getMessage();
        }
        $ms = round((microtime(true) - $t0) * 1000, 1);
        $out[$label] = ['ms' => $ms, 'rows' => $rows, 'error' => $err];
    };
    $bench('فهرست ۵۰ درخواست اخیر',
        'SELECT * FROM tickets ORDER BY id DESC LIMIT 50');
    $bench('فهرست با اتصال کاربر و دسته',
        'SELECT t.*, u.full_name, c.name AS cat FROM tickets t
         LEFT JOIN users u ON u.id = t.requester_id
         LEFT JOIN categories c ON c.id = t.category_id
         ORDER BY t.id DESC LIMIT 50');
    $bench('شمارش صف باز',
        "SELECT COUNT(*) c FROM tickets WHERE status NOT IN ('closed','cancelled')");
    $bench('آخرین پیام هر درخواست (الگوی N+1)',
        'SELECT t.id, (SELECT COUNT(*) FROM ticket_messages m WHERE m.ticket_id = t.id) n
         FROM tickets t ORDER BY t.id DESC LIMIT 50');
    $bench('گروه‌بندی بر اساس وضعیت',
        'SELECT status, COUNT(*) c FROM tickets GROUP BY status');
    return $out;
});
if (isset($perf['__error'])) {
    out('  🔴 خطا: ' . $perf['__error']);
} else {
    foreach ($perf as $label => $d) {
        $flag = $d['error'] ? '🔴 ' . $d['error'] : ($d['ms'] > 1000 ? '🔴 کند' : ($d['ms'] > 300 ? '🟠 قابل بهبود' : '✅'));
        out('  ' . pad($label, 42) . str_pad((string) $d['ms'], 9, ' ', STR_PAD_LEFT) . ' ms   ' . $flag);
    }
}
sect('performance_readonly', $perf);

// ═════════════════ بخش F — ایندکس‌های جدول درخواست ═════════════════
head('F — ایندکس‌های جدول tickets');
$idx = safe(static function () {
    $rows = db()->query('SHOW INDEX FROM tickets')->fetchAll(PDO::FETCH_ASSOC);
    $byName = [];
    foreach ($rows as $r) {
        $byName[$r['Key_name']][] = $r['Column_name'];
    }
    return $byName;
});
if (isset($idx['__error'])) {
    out('  🔴 خطا: ' . $idx['__error']);
} else {
    foreach ($idx as $name => $cols) {
        out('  ' . pad((string) $name, 30) . '(' . implode(', ', $cols) . ')');
    }
    foreach (['status', 'requester_id', 'assigned_to', 'created_at', 'ticket_no'] as $want) {
        $has = false;
        foreach ($idx as $cols) {
            if (in_array($want, $cols, true)) { $has = true; break; }
        }
        if (!$has) {
            out("  🟠 ستون پرکاربرد «{$want}» ایندکس ندارد");
        }
    }
}
sect('indexes', $idx);

// ═════════════════ بخش G — سلامت لاگ و ممیزی ═════════════════
head('G — سلامت لاگ و ممیزی');
$logs = [
    'heartbeat_dir'     => is_dir($LOGDIR . '/worker-heartbeats'),
    'audit_heartbeat'   => is_file($LOGDIR . '/worker-heartbeats/system-audit.json'),
    'audit_baseline'    => is_file($LOGDIR . '/audit-baseline.json'),
    'logs_writable'     => is_writable($LOGDIR),
    'cron_files'        => [],
    'task_logs'         => [],
];
foreach (['check_logging_health.php', 'replay_logging_spool.php', 'rebuild_logging_issues.php',
          'deliver_logging_issue_notifications.php', 'reconcile_update_audit.php',
          'run_logging_maintenance.bat'] as $f) {
    $logs['cron_files'][$f] = is_file(APP_ROOT . '/cron/' . $f);
}
foreach (['logging-health-task.log', 'logging-worker-task.log', 'update-audit-reconcile-task.log'] as $f) {
    $p = $LOGDIR . '/' . $f;
    $logs['task_logs'][$f] = is_file($p)
        ? ['size' => filesize($p), 'mtime' => date('Y-m-d H:i:s', (int) filemtime($p))]
        : null;
}
row('پوشهٔ storage/logs قابل نوشتن', $logs['logs_writable']);
row('فایل ضربان ممیزی', $logs['audit_heartbeat'], $logs['audit_heartbeat'] ? '' : '🔴 ممیزی هرگز اجرا نشده');
row('فایل خط مبنای ممیزی', $logs['audit_baseline'], $logs['audit_baseline'] ? '' : '🔴 ساخته نشده');
out('  فایل‌های cron:');
foreach ($logs['cron_files'] as $f => $ok) {
    out('     ' . pad($f, 48) . ($ok ? '✅' : '❌ نیست'));
}
out('  لاگ اجرای Scheduled Task:');
foreach ($logs['task_logs'] as $f => $d) {
    out('     ' . pad($f, 42) . ($d === null ? '❌ وجود ندارد (یعنی هرگز اجرا نشده)' : number_format($d['size']) . ' بایت، آخرین: ' . $d['mtime']));
}
sect('logging_health', $logs);

// ═════════════════ بخش H — منطق دسترسی (فقط‌خواندنی، بدون نوشتن) ═════════════════
head('H — منطق دسترسی روی دادهٔ واقعی');
$access = safe(static function () use ($caps) {
    if (!$caps['ticket_handler_level']) {
        return ['skipped' => 'تابع ticket_handler_level روی این نسخه وجود ندارد'];
    }
    $pdo = db();
    $t = $pdo->query('SELECT * FROM tickets ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    if (!$t) {
        return ['skipped' => 'هیچ درخواستی در سامانه نیست'];
    }
    $res = ['ticket_id' => $t['id'], 'cases' => []];
    $users = $pdo->query('SELECT * FROM users WHERE is_active = 1 ORDER BY id LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        $case = [
            'user_id'  => $u['id'],
            'role'     => $u['role'] ?? null,
            'is_requester' => ((int) ($t['requester_id'] ?? 0) === (int) $u['id']),
            'is_assignee'  => ((int) ($t['assigned_to'] ?? 0) === (int) $u['id']),
        ];
        $case['handler_level'] = safe(static fn() => ticket_handler_level($t, $u), '(خطا)');
        if (function_exists('ticket_view_mode')) {
            $case['view_mode'] = safe(static fn() => ticket_view_mode($t, $u), '(خطا)');
        }
        $res['cases'][] = $case;
    }
    return $res;
});
if (isset($access['skipped'])) {
    out('  ⏭️  ' . $access['skipped']);
} elseif (isset($access['__error'])) {
    out('  🔴 خطا: ' . $access['__error']);
} else {
    out('  درخواست نمونه: #' . $access['ticket_id']);
    foreach ($access['cases'] as $c) {
        out('     کاربر ' . pad((string) $c['user_id'], 7)
            . 'نقش ' . pad((string) $c['role'], 18)
            . 'ثبت‌کننده:' . pad($c['is_requester'] ? 'بله' : 'خیر', 6)
            . 'مسئول:' . pad($c['is_assignee'] ? 'بله' : 'خیر', 6)
            . '⇒ سطح «' . (is_string($c['handler_level']) ? ($c['handler_level'] === '' ? '(بدون اختیار)' : $c['handler_level']) : '?') . '»'
            . (isset($c['view_mode']) ? '  نما: ' . $c['view_mode'] : ''));
    }
    // تفکیک وظایف: آیا کسی روی درخواست خودش اختیار مدیریتی دارد؟
    foreach ($access['cases'] as $c) {
        if ($c['is_requester'] && $c['handler_level'] === 'manager') {
            out('  🔴 تفکیک وظایف نقض شده: ثبت‌کننده روی درخواست خودش اختیار مدیریتی دارد.');
        }
    }
}
sect('access_logic', $access);

// ═════════════════ بخش I — حالت نوشتن: ساخت کاربر و درخواست ═════════════════
$writeResult = ['executed' => false, 'reason' => ''];

if ($opt['cleanup']) {
    head('I — پاک‌سازی دادهٔ آزمایشی');
    if (!$opt['confirm']) {
        out('  ⏭️  برای پاک‌سازی باید --confirm را هم بدهید. هیچ چیزی حذف نشد.');
        $writeResult['reason'] = 'cleanup بدون confirm';
    } elseif (!is_file($MANIFEST)) {
        out('  ⏭️  فایل مانیفست پیدا نشد؛ یعنی این اسکریپت چیزی نساخته که پاک کند.');
        $writeResult['reason'] = 'مانیفست نیست';
    } else {
        $man = json_decode((string) @file_get_contents($MANIFEST), true) ?: [];
        $delTickets = $delUsers = 0;
        $errs = [];
        $pdo = db();
        // فقط شناسه‌هایی که خودِ اسکریپت ثبت کرده حذف می‌شوند.
        foreach (array_chunk($man['ticket_ids'] ?? [], 200) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            try {
                $pdo->prepare("DELETE FROM ticket_messages WHERE ticket_id IN ($in)")->execute($chunk);
                $pdo->prepare("DELETE FROM ticket_events WHERE ticket_id IN ($in)")->execute($chunk);
            } catch (Throwable $e) { $errs[] = $e->getMessage(); }
            try {
                $st = $pdo->prepare("DELETE FROM tickets WHERE id IN ($in)");
                $st->execute($chunk);
                $delTickets += $st->rowCount();
            } catch (Throwable $e) { $errs[] = $e->getMessage(); }
        }
        foreach (array_chunk($man['user_ids'] ?? [], 200) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            try {
                $st = $pdo->prepare("DELETE FROM users WHERE id IN ($in) AND username LIKE ?");
                $st->execute(array_merge($chunk, [($man['prefix'] ?? $opt['prefix']) . '%']));
                $delUsers += $st->rowCount();
            } catch (Throwable $e) { $errs[] = $e->getMessage(); }
        }
        // ردیف شمارندهٔ اختصاصی آزمون هم برداشته می‌شود تا هیچ ردی نماند.
        // این ردیف فقط مال پیشوند آزمایشی است و به شمارندهٔ واقعی کاری ندارد.
        $delSeq = 0;
        if (!empty($man['test_prefix'])) {
            try {
                $st = $pdo->prepare('DELETE FROM ticket_number_seq WHERE prefix = ?');
                $st->execute([$man['test_prefix']]);
                $delSeq = $st->rowCount();
            } catch (Throwable $e) { $errs[] = $e->getMessage(); }
        }

        row('درخواست حذف‌شده', $delTickets);
        row('کاربر حذف‌شده', $delUsers);
        row('ردیف شمارندهٔ آزمون حذف‌شده', $delSeq);
        if ($errs) {
            out('  خطاها:');
            foreach (array_unique($errs) as $e) { out('     ' . $e); }
        }
        @unlink($MANIFEST);
        $writeResult = ['executed' => true, 'deleted_tickets' => $delTickets, 'deleted_users' => $delUsers, 'deleted_seq' => $delSeq, 'errors' => $errs];
    }
} elseif ($opt['write']) {
    head('I — ساخت دادهٔ آزمایشی و سنجش زیر بار');
    if (!$opt['confirm']) {
        out('  ⏭️  حالت نوشتن فعال است ولی --confirm داده نشده. هیچ داده‌ای ساخته نشد.');
        out('     برای اجرای واقعی:  php tools\\system_loadtest.php --write --confirm');
        $writeResult['reason'] = 'write بدون confirm';
    } else {
        $pdo = db();
        // اگر اجرای قبلی پاک نشده باشد، مانیفست را *ادامه* می‌دهیم نه بازنویسی.
        // بازنویسی یعنی ردیف‌های اجرای قبلی یتیم شوند و دیگر با --cleanup
        // پاک نشوند — یعنی آشغال دائمی در پایگاه دادهٔ عملیاتی.
        $man = ['prefix' => $opt['prefix'], 'created_at' => date('c'), 'user_ids' => [], 'ticket_ids' => []];
        if (is_file($MANIFEST)) {
            $prev = json_decode((string) @file_get_contents($MANIFEST), true);
            if (is_array($prev)) {
                $man['user_ids']    = array_values(array_unique(array_map('intval', $prev['user_ids'] ?? [])));
                $man['ticket_ids']  = array_values(array_unique(array_map('intval', $prev['ticket_ids'] ?? [])));
                $man['created_at']  = $prev['created_at'] ?? $man['created_at'];
                $man['test_prefix'] = $prev['test_prefix'] ?? null;
                out('  ℹ️  مانیفست اجرای قبلی پیدا شد: '
                    . count($man['user_ids']) . ' کاربر و ' . count($man['ticket_ids'])
                    . ' درخواست از قبل ثبت است. این اجرا به آن اضافه می‌شود.');
                out('     اگر می‌خواهید از صفر شروع کنید، اول --cleanup --confirm را بزنید.');
            }
        }
        $w = ['users' => ['created' => 0, 'ms' => 0, 'errors' => []],
              'tickets' => ['created' => 0, 'ms' => 0, 'errors' => [], 'numbers' => []]];

        // ── کاربران ──
        $cols = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_ASSOC);
        $have = [];
        foreach ($cols as $c) { $have[$c['Field']] = $c; }
        $hash = password_hash('Test@12345', PASSWORD_DEFAULT);
        $t0 = microtime(true);
        for ($i = 1; $i <= $opt['users']; $i++) {
            $username = $opt['prefix'] . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $fields = ['username' => $username, 'full_name' => 'کاربر آزمایشی ' . $i];
            if (isset($have['password_hash'])) { $fields['password_hash'] = $hash; }
            if (isset($have['role']))          { $fields['role'] = 'employee'; }
            if (isset($have['auth_source']))   { $fields['auth_source'] = 'local'; }
            if (isset($have['is_active']))     { $fields['is_active'] = 1; }
            $names  = implode(',', array_map(static fn($k) => "`$k`", array_keys($fields)));
            $marks  = implode(',', array_fill(0, count($fields), '?'));
            try {
                $st = $pdo->prepare("INSERT INTO users ($names) VALUES ($marks)");
                $st->execute(array_values($fields));
                $man['user_ids'][] = (int) $pdo->lastInsertId();
                $w['users']['created']++;
            } catch (Throwable $e) {
                if (count($w['users']['errors']) < 5) { $w['users']['errors'][] = $e->getMessage(); }
            }
        }
        $w['users']['ms'] = round((microtime(true) - $t0) * 1000, 1);
        row('کاربر ساخته‌شده', $w['users']['created'] . ' از ' . $opt['users']);
        row('زمان ساخت کاربران', $w['users']['ms'] . ' ms',
            $w['users']['created'] ? round($w['users']['ms'] / max(1, $w['users']['created']), 2) . ' ms به ازای هر کاربر' : '');
        foreach ($w['users']['errors'] as $e) { out('     🔴 ' . $e); }
        @file_put_contents($MANIFEST, json_encode($man, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        // ── درخواست‌ها ──
        // از مسیر واقعی تولید شماره استفاده می‌کنیم تا باگ شماره دیده شود.
        $tcols = $pdo->query('SHOW COLUMNS FROM tickets')->fetchAll(PDO::FETCH_ASSOC);
        $thave = [];
        foreach ($tcols as $c) { $thave[$c['Field']] = $c; }
        $userIds = $man['user_ids'] ?: $pdo->query('SELECT id FROM users LIMIT 50')->fetchAll(PDO::FETCH_COLUMN);
        $nextFn   = function_exists('ticket_number_next');

        // ⚠️ ایمنی حیاتی: ticket_number_next() شمارندهٔ واقعی را با
        // «UPDATE ticket_number_seq SET seq = ?» جلو می‌برد. اگر اینجا پیشوند
        // واقعی سازمان را بدهیم، حتی پس از پاک‌سازیِ درخواست‌های آزمایشی
        // شمارنده عقب نمی‌گردد و شماره‌های واقعی برای همیشه می‌پرند.
        // پس عمداً یک پیشوند اختصاصی آزمون می‌سازیم: همان مسیر کد اجرا و
        // سنجیده می‌شود، ولی ردیف شمارندهٔ واقعی دست‌نخورده می‌ماند.
        $TESTPFX = strtoupper(substr(rtrim($opt['prefix'], '_'), 0, 8));
        if ($TESTPFX === '') { $TESTPFX = 'ZZTEST'; }
        $man['test_prefix'] = $TESTPFX;
        out('  پیشوند اختصاصی آزمون: ' . $TESTPFX . '   (شمارندهٔ واقعی دست نمی‌خورد)');
        $t0 = microtime(true);
        $slowest = 0.0;
        for ($i = 1; $i <= $opt['tickets'] && $userIds; $i++) {
            $uid = (int) $userIds[array_rand($userIds)];
            $no = null;
            $q0 = microtime(true);
            try {
                if ($nextFn) {
                    $no = ticket_number_next($TESTPFX);
                }
            } catch (Throwable $e) {
                if (count($w['tickets']['errors']) < 5) { $w['tickets']['errors'][] = 'شماره: ' . $e->getMessage(); }
            }
            $f = ['subject' => 'آزمون خودکار ' . $i, 'description' => 'ردیف ساختگی برای سنجش سامانه.'];
            if (isset($thave['ticket_no']) && $no !== null) { $f['ticket_no'] = $no; }
            if (isset($thave['requester_id'])) { $f['requester_id'] = $uid; }
            if (isset($thave['status']))       { $f['status'] = $thave['status']['Default'] ?: 'manager_review'; }
            if (isset($thave['priority']))     { $f['priority'] = $thave['priority']['Default'] ?: 'medium'; }
            if (isset($thave['ticket_type']))  { $f['ticket_type'] = $thave['ticket_type']['Default'] ?: 'incident'; }
            $names = implode(',', array_map(static fn($k) => "`$k`", array_keys($f)));
            $marks = implode(',', array_fill(0, count($f), '?'));
            try {
                $st = $pdo->prepare("INSERT INTO tickets ($names) VALUES ($marks)");
                $st->execute(array_values($f));
                $man['ticket_ids'][] = (int) $pdo->lastInsertId();
                $w['tickets']['created']++;
                if ($no !== null) { $w['tickets']['numbers'][] = $no; }
            } catch (Throwable $e) {
                if (count($w['tickets']['errors']) < 5) { $w['tickets']['errors'][] = $e->getMessage(); }
            }
            $slowest = max($slowest, (microtime(true) - $q0) * 1000);
            if ($i % 100 === 0) {
                @file_put_contents($MANIFEST, json_encode($man, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
        }
        $w['tickets']['ms'] = round((microtime(true) - $t0) * 1000, 1);
        $w['tickets']['slowest_ms'] = round($slowest, 1);
        @file_put_contents($MANIFEST, json_encode($man, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        row('درخواست ساخته‌شده', $w['tickets']['created'] . ' از ' . $opt['tickets']);
        row('زمان کل ثبت', $w['tickets']['ms'] . ' ms',
            $w['tickets']['created'] ? round($w['tickets']['ms'] / max(1, $w['tickets']['created']), 2) . ' ms به ازای هر ثبت' : '');
        row('کندترین ثبت', $w['tickets']['slowest_ms'] . ' ms',
            $w['tickets']['slowest_ms'] > 500 ? '🔴 جهش زمانی — نشانهٔ ترمیم شماره در مسیر درخواست' : '');
        foreach ($w['tickets']['errors'] as $e) { out('     🔴 ' . $e); }

        // ── تکرار شماره در همین اجرا ──
        $nums = $w['tickets']['numbers'];
        $dups = array_filter(array_count_values($nums), static fn($c) => $c > 1);
        $w['tickets']['duplicate_in_run'] = count($dups);
        row('شمارهٔ تکراری در همین اجرا', count($dups),
            $dups ? '🔴 ' . implode(', ', array_slice(array_keys($dups), 0, 5)) : '✅ هیچ');

        // ── سنجش فهرست بعد از افزایش حجم ──
        $after = [];
        foreach ([
            'فهرست ۵۰ درخواست اخیر' => 'SELECT * FROM tickets ORDER BY id DESC LIMIT 50',
            'فهرست با اتصال‌ها'      => 'SELECT t.*, u.full_name FROM tickets t LEFT JOIN users u ON u.id = t.requester_id ORDER BY t.id DESC LIMIT 50',
            'شمارش صف باز'          => "SELECT COUNT(*) FROM tickets WHERE status NOT IN ('closed','cancelled')",
        ] as $label => $sql) {
            $q0 = microtime(true);
            try { $pdo->query($sql)->fetchAll(); $err = null; } catch (Throwable $e) { $err = $e->getMessage(); }
            $after[$label] = ['ms' => round((microtime(true) - $q0) * 1000, 1), 'error' => $err];
        }
        out('  کارایی پس از افزایش حجم:');
        foreach ($after as $label => $d) {
            $before = $perf[$label]['ms'] ?? null;
            out('     ' . pad($label, 34) . str_pad((string) $d['ms'], 9, ' ', STR_PAD_LEFT) . ' ms' . ($before !== null ? '   (قبل: ' . $before . ' ms)' : ''));
        }
        $w['performance_after'] = $after;
        $writeResult = ['executed' => true] + $w;

        out('');
        out('  ℹ️  دادهٔ آزمایشی باقی ماند تا بتوانید در رابط کاربری هم ببینید.');
        out('     برای پاک‌کردن:  php tools\\system_loadtest.php --cleanup --confirm');
        out('     مانیفست: ' . $MANIFEST);
    }
} else {
    head('I — حالت نوشتن اجرا نشد');
    out('  این اجرا فقط‌خواندنی بود؛ هیچ داده‌ای ساخته یا تغییر نکرد.');
    out('  برای آزمون کامل با ' . $opt['users'] . ' کاربر:');
    out('     php tools\\system_loadtest.php --write --confirm');
    $writeResult['reason'] = 'حالت پیش‌فرض فقط‌خواندنی';
}
sect('write_phase', $writeResult);

// ═════════════════ جمع‌بندی ═════════════════
head('جمع‌بندی');
$issues = [];
if (!empty($numbers['null_or_empty']))     { $issues[] = 'شمارهٔ خالی: ' . $numbers['null_or_empty'] . ' ردیف'; }
if (!empty($numbers['duplicate_groups']))  { $issues[] = 'شمارهٔ تکراری: ' . $numbers['duplicate_groups'] . ' گروه'; }
if (empty($logs['audit_heartbeat']))       { $issues[] = 'ممیزی سلامت هرگز اجرا نشده'; }
if (empty($logs['audit_baseline']))        { $issues[] = 'خط مبنای ممیزی ساخته نشده'; }
if (empty($logs['cron_files']['reconcile_update_audit.php'])) { $issues[] = 'cron/reconcile_update_audit.php وجود ندارد ولی bat صدایش می‌زند'; }
if (empty($logs['logs_writable']))         { $issues[] = 'پوشهٔ storage/logs قابل نوشتن نیست'; }
foreach (($perf ?? []) as $label => $d) {
    if (is_array($d) && !empty($d['ms']) && $d['ms'] > 1000) { $issues[] = "کوئری کند: {$label} ({$d['ms']} ms)"; }
}
if (!empty($writeResult['tickets']['duplicate_in_run'])) {
    $issues[] = 'تولید شمارهٔ تکراری زیر بار: ' . $writeResult['tickets']['duplicate_in_run'] . ' مورد';
}
if ($issues) {
    out('  مشکلات یافت‌شده:');
    foreach ($issues as $i => $t) { out('   ' . ($i + 1) . '. ' . $t); }
} else {
    out('  ✅ هیچ مشکلی در بررسی‌های این اجرا دیده نشد.');
}
$R['summary'] = ['issue_count' => count($issues), 'issues' => $issues];
$R['meta']['duration_ms'] = round((microtime(true) - $START) * 1000, 1);

out('');
out('مدت اجرا: ' . $R['meta']['duration_ms'] . ' ms');
out('گزارش متنی: ' . $REPORT_TXT);
out('گزارش JSON: ' . $REPORT_JSON);

@file_put_contents($REPORT_TXT, implode(PHP_EOL, $LINES) . PHP_EOL);
@file_put_contents($REPORT_JSON, json_encode($R, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

exit(0);
