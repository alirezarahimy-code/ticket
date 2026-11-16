<?php
/**
 * explain_alarms.php — «این هشدارهای سلامت یعنی چه؟ کدام طبیعی است و کدام ایراد واقعی؟»
 *
 * اجرا (روی سرور، از ریشهٔ برنامه):
 *   chcp 65001
 *   C:\xampp\php\php.exe tools\explain_alarms.php
 *
 * هر هشدار «آژیر سلامت سامانه» را می‌گیرد و برای هرکدام می‌گوید:
 *   OK   = ایراد نیست (روی سیستم تست طبیعی است)
 *   REAL = ایراد واقعی است، باید رفع شود
 *   WARN = بستگی به تصمیم شما دارد
 *
 * فقط‌خواندنی: هیچ چیزی را تغییر نمی‌دهد و هیچ چیزی را «رفع» نمی‌کند.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only — این ابزار باید از خط فرمان اجرا شود.\n");
}

$root = dirname(__DIR__);
@chdir($root);

$real = 0;
$note = 0;
$ok = 0;
$line = static function (string $s = ''): void { echo $s, PHP_EOL; };
$good = static function (string $s) use (&$ok): void { $ok++; echo "  [OK]   {$s}\n"; };
$warn = static function (string $s) use (&$note): void { $note++; echo "  [WARN] {$s}\n"; };
$bad  = static function (string $s) use (&$real): void { $real++; echo "  [REAL] {$s}\n"; };

$line('╔══════════════════════════════════════════════════════════════╗');
$line('║  تفسیر هشدارهای «آژیر سلامت سامانه»                          ║');
$line('╚══════════════════════════════════════════════════════════════╝');
$line(' ریشه: ' . $root . ' | زمان: ' . date('Y-m-d H:i:s'));

$configFile = $root . '/config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "[FAIL] config.php پیدا نشد. از ریشهٔ برنامه اجرا کنید.\n");
    exit(1);
}
require_once $root . '/bootstrap.php';
foreach (['food-ticket-window.php', 'food-ticket.php'] as $f) {
    if (is_file($root . '/' . $f)) {
        require_once $root . '/' . $f;
    }
}

$dbOk = false;
$workerRows = 0;
$applied = [];
try {
    db()->query('SELECT 1');
    $dbOk = true;
} catch (Throwable $e) {
    $bad('اتصال پایگاه‌داده برقرار نشد: ' . $e->getMessage());
}
if ($dbOk) {
    try {
        if (db_table_exists('food_ticket_worker_status')) {
            $workerRows = (int) db()->query('SELECT COUNT(*) FROM food_ticket_worker_status')->fetchColumn();
        }
    } catch (Throwable) {
    }
    try {
        if (db_table_exists('schema_updates')) {
            $rows = db()->query('SELECT migration FROM schema_updates')->fetchAll(PDO::FETCH_COLUMN) ?: [];
            foreach ($rows as $r) {
                $applied[strtolower(basename((string) $r))] = true;
            }
        }
    } catch (Throwable) {
    }
}

/* ══ ۱) پوشه‌های ضروری ═══════════════════════════════════════════════ */
$line("\n۱) «پوشهٔ ضروری سامانه در دسترس نیست» (storage/food_ticket_spool و food_ticket_prints)");
$dirs = ['storage/food_ticket_spool', 'storage/food_ticket_prints', 'storage/logs', 'storage/sessions'];
$missing = [];
foreach ($dirs as $rel) {
    if (!is_dir($root . '/' . $rel)) {
        $missing[] = $rel;
    }
}
if ($missing === []) {
    $good('همهٔ پوشه‌های ضروری موجودند.');
} else {
    $line('  پوشه‌های غایب: ' . implode('، ', $missing));
    $line('');
    $line('  یعنی چه؟  این دو پوشه را خودِ سامانه «هنگام اولین نیاز» می‌سازد —');
    $line('            food_ticket_prints با اولین چاپ، و spool با اولین چاپ ویندوزی.');
    if ($workerRows === 0) {
        $good('روی سیستم تست این طبیعی است: هنوز هیچ فیشی چاپ نشده، پس پوشه‌ها ساخته نشده‌اند.');
        $line('            اگر می‌خواهید هشدار خاموش شود، همین حالا بسازید:');
        $line('              mkdir storage\\food_ticket_spool');
        $line('              mkdir storage\\food_ticket_prints');
        $line('            (روی سیستم عملیاتی این هشدار خودش با اولین چاپ برطرف می‌شود.)');
    } else {
        $warn('Worker قبلاً اجرا شده ولی این پوشه‌ها نیستند — اگر چاپ ویندوزی دارید، بررسی کنید.');
    }
}

/* ══ ۲) Worker ═══════════════════════════════════════════════════════ */
$line("\n۲) «هیچ اجرای موفقی از Worker چاپ غذا ثبت نشده است»");
if ($workerRows === 0) {
    $line('  جدول food_ticket_worker_status خالی است.');
    $winEnabled = false;
    $winOpen = false;
    if (function_exists('food_ticket_window_state')) {
        try {
            $ws = food_ticket_window_state(function_exists('food_ticket_config') ? food_ticket_config(true) : []);
            $winEnabled = (bool) ($ws['enabled'] ?? false);
            $winOpen = (bool) ($ws['open'] ?? false);
            $line('  پنجرهٔ کاری: ' . ($winEnabled ? ('فعال، همین حالا ' . ($winOpen ? 'باز' : 'بسته')) : 'غیرفعال'));
        } catch (Throwable $e) {
            $line('  پنجرهٔ کاری خوانده نشد: ' . $e->getMessage());
        }
    }
    $hb = $root . '/storage/food_worker.heartbeat';
    $hbAge = is_file($hb) ? (time() - (int) filemtime($hb)) : null;
    if ($hbAge !== null) {
        $line('  heartbeat فایل: ' . ($hbAge < 120 ? $hbAge . ' ثانیه پیش' : round($hbAge / 60) . ' دقیقه پیش'));
    }
    if ($hbAge !== null) {
        $good('Worker اجرا شده (heartbeat تازه است)؛ فقط جدول وضعیت هنوز اولین ثبت را ندارد. چرخهٔ بعدی پرش می‌کند.');
    } elseif ($winEnabled && !$winOpen) {
        $good('بیرون از پنجرهٔ ۱۱–۱۴ است و Worker طبق طراحی هیچ کاری نمی‌کند — طبیعی است.');
        $line('            مقدار ستون last_message بعد از اولین چرخهٔ داخل پنجره پر می‌شود.');
    } else {
        $warn('Worker در پنجرهٔ کاری هم اثری نگذاشته — سرویس/تسک «Food Ticket PHP Worker» را بررسی کنید.');
        $line('            اجرای دستی برای یک‌بار آزمایش:');
        $line('              C:\\xampp\\php\\php.exe cron\\food_ticket_worker.php --queue-only');
    }
} else {
    try {
        $row = db()->query('SELECT last_seen, status, last_message FROM food_ticket_worker_status WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        if (is_array($row) && !empty($row['last_seen'])) {
            $good('Worker ثبت شده — آخرین اجرا: ' . $row['last_seen'] . ' | وضعیت: ' . ($row['status'] ?? '—'));
        }
    } catch (Throwable) {
    }
}

/* ══ ۳) مهاجرت ۰۳۳ ═══════════════════════════════════════════════════ */
$line("\n۳) «مهاجرت دیتابیس اجرا نشده است — 033-logging-legacy-context-collation.sql»");
$m033 = '033-logging-legacy-context-collation.sql';
if (isset($applied[strtolower($m033)])) {
    $good('۰۳۳ در schema_updates ثبت شده است.');
} else {
    $line('  ۰۳۳ در جدول schema_updates نیست.');
    $line('');
    $line('  یعنی چه؟  این مهاجرت فقط یک UPDATE است: ستون context ردیف‌های');
    $line('            log_issues را از مقدار «system» با مقدار واقعی system_logs هم‌تراز می‌کند.');
    $line('            ساختار جدول را عوض نمی‌کند و تکرارش بی‌خطر است (idempotent).');
    $line('');
    // آیا اصلاً ردیفی هست که این مهاجرت تغییر دهد؟
    $affected = null;
    try {
        $affected = (int) db()->query("SELECT COUNT(*) FROM log_issues WHERE context = 'system'")->fetchColumn();
    } catch (Throwable) {
    }
    if ($affected === 0) {
        $good('هیچ ردیفی با context=\'system\' نیست ⇒ این مهاجرت روی این پایگاه‌داده کاری برای انجام ندارد.');
        $line('            هشدار «کاذب» است: گارد فقط می‌بیند فایل روی دیسک هست و در جدول نیست.');
        $line('            برای ثبت یک‌باره (بدون تغییر داده):');
        $line('              php tools\\apply_migration_033.php --apply');
    } elseif ($affected !== null) {
        $warn($affected . ' ردیف log_issues با context=\'system\' پیدا شد — اجرای مهاجرت داده را هم‌تراز می‌کند.');
        $line('            اجرا: php tools\\apply_migration_033.php --apply');
    } else {
        $warn('جدول log_issues خوانده نشد (ممکن است مرکزی‌سازی لاگینگ نصب نشده باشد).');
    }
    $line('');
    $line('  نکتهٔ مهم: این هشدار ربطی به ۱.۶۲.۹۷ یا ۱.۶۳ ندارد — از قبل هم بود.');
}

/* ══ ۴) کرون آژیر ════════════════════════════════════════════════════ */
$line("\n۴) «دیده‌بانی خودکار سلامت به‌روز نیست — system_alarm_tick / system_alarm_morning»");
$tickState = $root . '/storage/system_alarm_state.json';
if (is_file($tickState)) {
    $age = time() - (int) filemtime($tickState);
    $line('  آخرین اثر tick: ' . ($age < 3600 ? round($age / 60) . ' دقیقه پیش' : round($age / 3600, 1) . ' ساعت پیش'));
    if ($age < 900) {
        $good('کرون tick به‌تازگی اجرا شده است.');
    } else {
        $warn('کرون tick قدیمی است — روی سیستم تست اگر تسک زمان‌بندی‌شده ثبت نشده، طبیعی است.');
    }
} else {
    $line('  فایل وضعیت آژیر پیدا نشد: storage/system_alarm_state.json');
    $good('روی سیستم تست طبیعی است: کرون آژیر ثبت نشده، پس هیچ‌وقت اجرا نشده.');
    $line('            برای سیستم عملیاتی، دستور ثبت در docs/SYSTEM-ALARM-FA.md آمده است:');
    $line('              schtasks /Create /TN "SystemAlarmTick" /SC MINUTE /MO 5 ...');
}
if (stripos(PHP_OS, 'WIN') === 0) {
    $out = [];
    $code = 0;
    @exec('schtasks /Query /TN "SystemAlarmTick" /FO LIST 2>&1', $out, $code);
    if ($code === 0) {
        $good('تسک SystemAlarmTick در Task Scheduler ثبت شده است.');
    } else {
        $line('  تسک SystemAlarmTick در Task Scheduler نیست.');
    }
}

/* ══ خلاصه ═══════════════════════════════════════════════════════════ */
$line('');
$line('══════════════════════════════════════════════════════════════');
$line(' ایراد واقعی: ' . $real . ' | نیازمند تصمیم/ثبت: ' . $note . ' | طبیعی: ' . $ok);
if ($real === 0) {
    $line(' ✅ هیچ‌کدام از این چهار هشدار، خرابی واقعی نیست.');
} else {
    $line(' ❌ ' . $real . ' مورد ایراد واقعی است — بخش مربوطه را ببینید.');
}
$line(' برای سلامتی مسیر چاپ: php tools\\verify_1_62_97.php');
$line('══════════════════════════════════════════════════════════════');
exit($real === 0 ? 0 : 1);
