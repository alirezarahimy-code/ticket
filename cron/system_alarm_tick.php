<?php
declare(strict_types=1);

/**
 * cron/system_alarm_tick.php — دیده‌بانی آژیر سلامت (فاز ۱)
 *
 *   php cron/system_alarm_tick.php            → tick: probeها را اجرا + state ذخیره + اعلان (با cooldown)
 *   php cron/system_alarm_tick.php --morning  → پیش‌چک ۰۸:۰۰: همان tick + اعلان اجباری در صورت وجود مشکل
 *
 * فقط‌خواندنی + اعلان داخل سامانه (notify_user). هیچ چاپ/ابطال خودکاری انجام نمی‌دهد.
 * ثبت در Task Scheduler ویندوز: cron/run_system_alarm_tick.bat (هر ۵ دقیقه)
 *                              cron/run_system_alarm_morning.bat (روزانه ۰۸:۰۰)
 * خروجی در STDOUT برای Task Scheduler / لاگ task نوشته می‌شود.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

require dirname(__DIR__) . '/bootstrap.php';
$foodFile = dirname(__DIR__) . '/food-ticket.php';
if (is_file($foodFile)) {
    require_once $foodFile; // برای food_ticket_config() در probe پرینتر
}
require_once dirname(__DIR__) . '/lib/system-alarms.php';

$isMorning = in_array('--morning', array_slice($argv ?? [], 1), true);

// ۱) probeهای سنگین (مثل تست اتصال پرینتر) — نتیجه در state ذخیره می‌شود و مسیر وب می‌خواند
$probeAlarms = function_exists('system_alarm_probe_printer') ? system_alarm_probe_printer() : [];
system_alarm_write_json_atomic(system_alarm_state_path(), [
    'generated_at' => time(),
    'mode' => $isMorning ? 'morning' : 'tick',
    'alarms' => $probeAlarms,
]);

// ۲) heartbeat برای پایش خودِ دیده‌بانی (فایل-بیس، هم‌نام الگوی کرون‌های logging)
$hbDir = dirname(__DIR__) . '/storage/logs/worker-heartbeats';
system_alarm_write_json_atomic($hbDir . '/system-alarm.json', [
    'last_finished_at' => date('c'),
    'state' => ($probeAlarms !== [] ? 'alert' : 'ok'),
    'mode' => $isMorning ? 'morning' : 'tick',
    'alarm_count' => count($probeAlarms),
]);

// ۳) جمع‌آوری آلارم‌ها (cheap + probe) و اعلان داخل سامانه به ادمین اصلی‌ها
$all = array_merge(system_alarm_collect_cheap(), $probeAlarms);
$seen = [];
$unique = [];
foreach ($all as $alarm) {
    $key = (string) ($alarm['key'] ?? '');
    if ($key === '' || isset($seen[$key])) {
        continue;
    }
    $seen[$key] = true;
    $unique[] = $alarm;
}

if ($unique === []) {
    fwrite(STDOUT, ($isMorning ? 'MORNING' : 'TICK') . " OK no critical alarms\n");
    exit(0);
}

$adminIds = [];
try {
    $adminIds = db()->query("SELECT id FROM users WHERE role = 'primary_admin'")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    if (function_exists('db_column_present') && db_column_present('users', 'is_primary_admin')) {
        $legacy = db()->query("SELECT id FROM users WHERE role = 'admin' AND is_primary_admin = 1")->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $adminIds = array_values(array_unique(array_merge($adminIds, $legacy)));
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Cannot resolve primary administrators: ' . $e->getMessage() . "\n");
    exit(2);
}

$notifyState = (array) (system_alarm_read_json(system_alarm_notify_path()) ?? []);
$now = time();
$sent = 0;
foreach ($unique as $alarm) {
    $key = (string) $alarm['key'];
    $last = (int) ($notifyState[$key] ?? 0);
    // tick: cooldown هر کلید؛ morning: اعلان اجباری (یک‌بار در روز طبق زمان‌بندی)
    if (!$isMorning && $last > 0 && ($now - $last) < SYSTEM_ALARM_NOTIFY_COOLDOWN) {
        continue;
    }
    foreach ($adminIds as $adminId) {
        $window = (int) floor($now / 1800);
        $idempotencyKey = hash('sha256', 'system-alarm|' . $key . '|' . (int) $adminId . '|' . $window);
        try {
            notify_user(
                (int) $adminId,
                'system_health_alert',
                ($isMorning ? 'پیش‌چک ۰۸:۰۰ — ' : '') . 'آژیر سلامت: ' . (string) $alarm['title'],
                (string) ($alarm['detail'] ?? ''),
                null,
                $idempotencyKey
            );
            $sent++;
        } catch (Throwable $e) {
            if (function_exists('app_error_log')) {
                app_error_log('system_alarm notify failed: ' . $e->getMessage());
            }
        }
    }
    $notifyState[$key] = $now;
}
system_alarm_write_json_atomic(system_alarm_notify_path(), $notifyState);

$titles = implode('، ', array_map(static fn ($a) => (string) ($a['title'] ?? $a['key']), $unique));
fwrite(STDOUT, ($isMorning ? 'MORNING' : 'TICK') . ' ALARM active=' . count($unique) . ' notified=' . $sent . ' → ' . $titles . "\n");
exit(0);
