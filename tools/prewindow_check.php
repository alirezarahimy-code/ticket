<?php
declare(strict_types=1);

/**
 * prewindow_check.php — «آیا فردا ساعت ۱۱ چاپ سالم است؟» را همین حالا جواب می‌دهد.
 *
 * مسئله: پنجرهٔ فعالیت ۱۱:۰۰–۱۴:۰۰ است و Worker بیرون از آن (حالت سخت‌گیرانه) هیچ‌کاری
 * نمی‌کند. پس اگر ساعت ۱۱:۰۵ بفهمیم چاپ خراب است، وقتی برای تعمیر نمانده.
 *
 * این ابزار بدون تغییر هیچ تنظیمی و بدون چاپ واقعی:
 *   ۱) همان تابعِ Worker (`food_ticket_window_state`) را برای چند ساعت مختلف
 *      (از جمله ۱۱:۰۰ و ۱۴:۰۰ و بیرون از پنجره) ارزیابی می‌کند ⇒ «فردا ۱۱ در باز می‌شود؟»
 *   ۲) همان کوئریِ انتخابِ Worker را اجرا می‌کند ⇒ «فردا چه چیزی چاپ می‌شود؟»
 *   ۳) مسیر چاپ را «خشک» می‌آزماید (رندر فیش به PNG/داده، بدون ارسال به چاپگر)
 *   ۴) چاپگر را فقط با تست TCP بررسی می‌کند (هیچ بایتی نمی‌فرستد)
 *   ۵) قفل صف، heartbeat Worker، و ردیف‌های گیرکرده را می‌سنجد
 *   ۶) در پایان یک «حکم» می‌دهد: ✅ آمادهٔ فردا / ⚠️ با هشدار / ❌ چاپ فردا کار نمی‌کند
 *
 * اجرا (روی سرور ویندوز، از ریشهٔ برنامه):
 *   chcp 65001
 *   C:\xampp\php\php.exe tools\prewindow_check.php
 *   C:\xampp\php\php.exe tools\prewindow_check.php --at "11:00"    ارزیابی برای ساعت دیگر
 *   C:\xampp\php\php.exe tools\prewindow_check.php --at "2026-11-15 11:00"
 *
 * فقط‌خواندنی: هیچ INSERT/UPDATE/DELETE، هیچ تغییر تنظیمات، هیچ بایت به پرینتر.
 * تنها استثنا: ساخت/حذف فایل PNG آزمایشی در storage/food_ticket_prints (اگر --dry-print).
 */

$root = dirname(__DIR__);
@chdir($root);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only — این ابزار باید از خط فرمان اجرا شود.\n");
}

const PRE_VERSION = '1.0.0';
$fails = 0;
$warns = 0;
$line = static function (string $s = ''): void { echo $s, PHP_EOL; };
$ok   = static function (string $s) use ($line): void { $line('  [OK]   ' . $s); };
$info = static function (string $s) use ($line): void { $line('  [i]    ' . $s); };
$warn = static function (string $s) use ($line, &$warns): void { $warns++; $line('  [WARN] ' . $s); };
$bad  = static function (string $s) use ($line, &$fails): void { $fails++; $line('  [FAIL] ' . $s); };

// ── آرگومان‌ها ──────────────────────────────────────────────────────────
$args = array_slice($argv, 1);
$atRaw = '';
$dryPrint = true;
foreach ($args as $i => $a) {
    if ($a === '--at' && isset($args[$i + 1])) { $atRaw = (string) $args[$i + 1]; }
    if (str_starts_with($a, '--at=')) { $atRaw = substr($a, 5); }
    if ($a === '--no-dry-print') { $dryPrint = false; }
}

$configFile = (string) (getenv('ITSM_CONFIG_PATH') ?: ($root . '/config.php'));
if (!is_file($configFile)) {
    fwrite(STDERR, "[FAIL] config.php پیدا نشد در: {$configFile}\n"
        . "این ابزار را داخل ریشهٔ برنامه (پوشهٔ tools در کنار index.php) اجرا کنید.\n");
    exit(1);
}

$line('╔══════════════════════════════════════════════════════════════╗');
$line('║  تست پیش از پنجرهٔ چاپ — آیا فردا ۱۱:۰۰ چاپ سالم است؟        ║');
$line('║  نسخهٔ ' . PRE_VERSION . ' — فقط‌خواندنی، بدون چاپ واقعی                        ║');
$line('╚══════════════════════════════════════════════════════════════╝');
$line(' زمان اجرا: ' . date('Y-m-d H:i:s') . ' | ریشه: ' . $root);


require $root . '/bootstrap.php';
foreach (['food-ticket-window.php', 'food-ticket.php', 'food-ticket-runtime.php', 'food-ticket-engine.php', 'food-ticket-auto-receipts.php', 'food-ticket-netprint.php'] as $f) {
    $p = $root . '/' . $f;
    if (is_file($p)) { require_once $p; }
}



// ── ۱) پنجرهٔ کاری: فردا ساعت ۱۱ در باز می‌شود؟ ────────────────────────
$line("\n۱) پنجرهٔ کاری — «فردا ساعت ۱۱ در باز می‌شود؟»");
$config = [];
try {
    $config = function_exists('food_ticket_config') ? (array) food_ticket_config(true) : [];
} catch (Throwable $e) {
    $bad('خواندن تنظیمات فیش غذا: ' . $e->getMessage());
}

$tz = function_exists('food_ticket_window_timezone') ? food_ticket_window_timezone() : new DateTimeZone('Asia/Tehran');
$nowTz = new DateTimeImmutable('now', $tz);
$targetAt = null;
if ($atRaw !== '') {
    foreach (['Y-m-d H:i', 'Y-m-d H:i:s', 'H:i', 'H:i:s'] as $fmt) {
        try {
            if (in_array($fmt, ['H:i', 'H:i:s'], true)) {
                $d = DateTimeImmutable::createFromFormat('Y-m-d ' . $fmt, $nowTz->format('Y-m-d') . ' ' . $atRaw, $tz);
            } else {
                $d = DateTimeImmutable::createFromFormat($fmt, $atRaw, $tz);
            }
            if ($d instanceof DateTimeImmutable) { $targetAt = $d; break; }
        } catch (Throwable) {
        }
    }
    if ($targetAt === null) {
        $warn("مقدار --at «{$atRaw}» قابل‌خواندن نبود؛ از زمان فعلی استفاده می‌شود.");
    }
}
$targetAt ??= $nowTz;

// ── حالت مشترک: خروجی دقیقاً همان گزارش پنل (حکم یکسان، بدون دو پیاده‌سازی) ──
$shared = in_array('--shared', $args, true);
if ($shared) {
    $lib = $root . '/food-ticket-selftest.php';
    if (!is_file($lib)) {
        fwrite(STDERR, "[FAIL] food-ticket-selftest.php پیدا نشد — بستهٔ ۱.۶۳ را کامل کپی کنید.\n");
        exit(1);
    }
    require_once $lib;
    $result = food_ticket_selftest_run([
        'source' => 'cli',
        'dry_print' => $dryPrint,
        'at' => $targetAt,
    ]);
    $line(food_ticket_selftest_text($result));
    exit((int) ($result['verdict']['counts']['fail'] ?? 0) === 0 ? 0 : 1);
}

if (!function_exists('food_ticket_window_state')) {
    $bad('تابع food_ticket_window_state پیدا نشد — food-ticket-window.php بارگذاری نشد.');
} else {
    // ردیف‌های آزمایش، هم برای «فردا» (روزی که مالک می‌خواهد چاپ کند) و هم برای دیروز
    // تا اگر فردا تعطیل است، معلوم شود پنجره در یک روز کاری معمولی چطور رفتار می‌کند.
    $baseDay = $targetAt->setTime(0, 0);
    $probeTimes = [
        'الان' => $nowTz,
        'امروز ۱۰:۵۹ (قبل از باز شدن)' => $baseDay->setTime(10, 59),
        'امروز ۱۱:۰۰ (باز شدن)' => $baseDay->setTime(11, 0),
        'امروز ۱۲:۳۰ (وسط)' => $baseDay->setTime(12, 30),
        'امروز ۱۳:۵۹ (آخرین دقیقه)' => $baseDay->setTime(13, 59),
        'امروز ۱۴:۰۱ (بعد از بسته شدن)' => $baseDay->setTime(14, 1),
        'فردا ۱۱:۰۰' => $baseDay->modify('+1 day')->setTime(11, 0),
        'پس‌فردا ۱۱:۰۰' => $baseDay->modify('+2 day')->setTime(11, 0),
    ];
    foreach ($probeTimes as $label => $when) {
        try {
            $ws = food_ticket_window_state($config, $when);
            $enabled = (bool) ($ws['enabled'] ?? false);
            // وقتی پنجره غیرفعال است، food_ticket_window_state همیشه open=true برمی‌گرداند
            // (Worker ۲۴/۷ کار می‌کند). نمایش «باز» در آن حالت گمراه‌کننده است.
            $tag = !$enabled ? 'غیرفعال' : ((bool) ($ws['open'] ?? false) ? 'باز' : 'بسته');
            $range = trim((string) ($ws['start'] ?? '') . '–' . (string) ($ws['end'] ?? ''), '–');
            $reason = $enabled ? '' : ' ← Worker شبانه‌روز کار می‌کند';
            $line(sprintf('  %-30s %s  %-9s %s%s', $label, $when->format('D Y-m-d H:i'), $tag, $range !== '' ? '(' . $range . ')' : '', $reason));
        } catch (Throwable $e) {
            $bad('محاسبهٔ پنجره برای ' . $label . ': ' . $e->getMessage());
        }
    }
    $atCheck = food_ticket_window_state($config, $targetAt->setTime(11, 0));
    if (!(bool) ($atCheck['enabled'] ?? false)) {
        $bad('پنجرهٔ کاری «غیرفعال» است ⇒ Worker تمام شبانه‌روز کار می‌کند (برخلاف خواستهٔ شما).');
    } elseif ((bool) ($atCheck['open'] ?? false)) {
        $ok('پنجره فعال است و ساعت ۱۱:۰۰ باز می‌شود ⇒ تردد و فیش در آن ساعت پردازش می‌شود.');
    } else {
        $bad('پنجره فعال است اما ساعت ۱۱:۰۰ «باز نمی‌شود» ⇒ فردا هیچ فیشی چاپ نخواهد شد. تنظیمات روز/ساعت را بررسی کنید.');
    }
    // فردا: فقط وقتی پنجره «فعال» است معنا دارد؛ با پنجرهٔ غیرفعال همیشه کار می‌کند.
    if ((bool) ($atCheck['enabled'] ?? false)) {
        try {
            $tomorrow = $targetAt->modify('+1 day');
            $wsT = food_ticket_window_state($config, $tomorrow->setTime(11, 0));
            $isHoliday = (bool) ($wsT['enabled'] ?? false) && !(bool) ($wsT['open'] ?? false);
            if ((bool) ($wsT['open'] ?? false)) {
                $ok('فردا (' . $tomorrow->format('D Y-m-d') . ') ساعت ۱۱:۰۰ باز است ⇒ چاپ انجام می‌شود.');
            } else {
                $bad('فردا (' . $tomorrow->format('D Y-m-d') . ') ساعت ۱۱:۰۰ بسته است: '
                    . (string) ($wsT['reason'] ?? '') . ' ⇒ فردا چاپی نخواهد بود.');
                if ($isHoliday) {
                    $info('اگر فردا «تعطیل رسمی» یا آخر هفته است، این طبیعی است؛ برای روز کاری بعد هم تست کنید.');
                }
            }
        } catch (Throwable $e) {
            $warn('بررسی فردا: ' . $e->getMessage());
        }
    } else {
        $info('پنجره غیرفعال است، پس «فردا باز می‌شود؟» معنا ندارد — Worker ۲۴/۷ کار می‌کند.');
    }
}

// ── ۲) پردازشگر و Worker ────────────────────────────────────────────────
$line("\n۲) پردازشگر و Worker");
$processorEnabled = (int) ($config['enabled'] ?? 0) === 1;
$processorEnabled
    ? $ok('پردازشگر فیش غذا فعال است (config.enabled=1).')
    : $bad('پردازشگر فیش غذا «غیرفعال» است (config.enabled=0) ⇒ هیچ فیشی چاپ نمی‌شود. از پنل فیش غذا فعال کنید.');

$workerScript = $root . '/cron/food_ticket_worker.php';
is_file($workerScript) ? $ok('فایل Worker موجود است.') : $bad('cron/food_ticket_worker.php پیدا نشد.');

// heartbeat: Worker باید هر چند ثانیه/دقیقه بنویسد
$hbCandidates = [
    $root . '/storage/food_ticket_worker_heartbeat.json',
    $root . '/storage/food_ticket_worker.heartbeat',
    $root . '/storage/food_ticket_logs/worker-' . date('Ymd') . '.log',
];
$hbFound = null;
foreach ($hbCandidates as $candidate) {
    if (is_file($candidate)) { $hbFound = $candidate; break; }
}
if ($hbFound === null) {
    $warn('فایل heartbeat یا لاگ امروز Worker پیدا نشد ⇒ ممکن است Worker امروز اجرا نشده باشد.');
} else {
    $age = time() - (int) @filemtime($hbFound);
    $ageLabel = $age < 120 ? $age . ' ثانیه' : ($age < 7200 ? round($age / 60) . ' دقیقه' : round($age / 3600, 1) . ' ساعت');
    if ($age < 180) {
        $ok('Worker زنده است (آخرین اثر: ' . $ageLabel . ' پیش) — ' . basename($hbFound));
    } elseif ($age < 900) {
        $warn('آخرین اثر Worker ' . $ageLabel . ' پیش است — دیرتر از حد معمول؛ Task Scheduler را چک کنید.');
    } else {
        $bad('Worker ' . $ageLabel . ' است که هیچ اثری ننوشته ⇒ Worker در حال اجرا نیست؛ فردا چاپی نخواهد بود.');
    }
}

// ── ۳) صف چاپ: چه چیزی فردا چاپ می‌شود؟ ────────────────────────────────
$line("\n۳) صف چاپ — ردیف‌های آمادهٔ چاپ و گیرکرده‌ها");
$dbOk = false;
try {
    db()->query('SELECT 1');
    $dbOk = true;
    $ok('اتصال دیتابیس برقرار است.');
} catch (Throwable $e) {
    $bad('اتصال دیتابیس: ' . $e->getMessage());
}

if ($dbOk) {
    $tableExists = static function (string $t): bool {
        try {
            $q = db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1');
            $q->execute([$t]);
            return (bool) $q->fetchColumn();
        } catch (Throwable) {
            return false;
        }
    };
    if (!$tableExists('food_ticket_events')) {
        $bad('جدول food_ticket_events نیست — یعنی هیچ رخدادی ثبت نمی‌شود.');
    } else {
        try {
            $claimable = "(print_status IN ('pending','print_error','failed') OR (print_status = 'printing' AND updated_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)))"
                . ' AND (next_retry_at IS NULL OR next_retry_at <= NOW())';
            $due = (int) db()->query('SELECT COUNT(*) FROM food_ticket_events WHERE ' . $claimable)->fetchColumn();
            if ($due > 0) {
                // اگر Worker زنده است و این‌ها مانده‌اند، یعنی چیزی جلوی چاپش را گرفته
                $bad($due . ' ردیف «واجد شرایط چاپ» همین حالا مانده است.'
                    . ' اگر Worker زنده است، یعنی چاپ گیر کرده؛ با --dry-print و بررسی پرینتر ادامه دهید.');
                $rows = db()->query("SELECT id, print_status, retry_count, COALESCE(last_print_error,'') AS err FROM food_ticket_events WHERE " . $claimable . ' ORDER BY id ASC LIMIT 5')->fetchAll();
                foreach ($rows as $r) {
                    $line('        #' . (int) $r['id'] . ' | ' . (string) $r['print_status']
                        . ' | retry=' . (int) ($r['retry_count'] ?? 0)
                        . ' | ' . mb_substr(trim((string) ($r['err'] ?? '')), 0, 100));
                }
            } else {
                $ok('هیچ ردیف واجد شرایطی مانده نیست ⇒ صف تمیز است.');
            }
            $printing = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status = 'printing'")->fetchColumn();
            if ($printing > 0) {
                $oldest = (string) db()->query("SELECT MIN(updated_at) FROM food_ticket_events WHERE print_status = 'printing'")->fetchColumn();
                $bad($printing . ' ردیف در وضعیت «printing» گیرکرده (قدیمی‌ترین: ' . $oldest . ') ⇒ صف را قفل می‌کند.');
            } else {
                $ok('هیچ ردیف گیرکردهٔ printing نیست.');
            }
            $todayRows = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE punch_date = CURDATE()")->fetchColumn();
            $info('رخدادهای چاپ امروز: ' . $todayRows . ' ردیف (برای مقایسهٔ فردا).');
        } catch (Throwable $e) {
            $warn('تحلیل صف چاپ: ' . $e->getMessage());
        }
    }

    // قفل صف: اگر فایل قفلی مانده باشد Worker نمی‌تواند کار کند
    foreach (['storage/food_ticket_queue.lock', 'storage/food_ticket_worker.lock', 'storage/food_ticket.lock'] as $lockRel) {
        $lockPath = $root . '/' . $lockRel;
        if (is_file($lockPath)) {
            $lockAge = time() - (int) @filemtime($lockPath);
            if ($lockAge > 600) {
                $bad('قفل کهنهٔ ' . $lockRel . ' (' . round($lockAge / 60) . ' دقیقه) مانده ⇒ ممکن است Worker نتواند صف را بردارد.');
            } else {
                $warn('فایل قفل ' . $lockRel . ' هست (' . $lockAge . ' ثانیه پیش) — معمولاً یعنی Worker همین حالا در حال کار است.');
            }
        }
    }
}

// ── ۴) پرینتر: فقط تست TCP، بدون ارسال بایت ────────────────────────────
$line("\n۴) چاپگر — فقط تست اتصال (هیچ بایتی ارسال نمی‌شود)");
$printerHost = '';
$printerPort = 9100;
foreach (['printer_host', 'netprint_host', 'food_printer_host'] as $k) {
    if (trim((string) ($config[$k] ?? '')) !== '') { $printerHost = trim((string) $config[$k]); break; }
}
foreach (['printer_port', 'netprint_port', 'food_printer_port'] as $k) {
    if ((int) ($config[$k] ?? 0) > 0) { $printerPort = (int) $config[$k]; break; }
}
if ($printerHost === '') {
    $warn('آدرس پرینتر در تنظیمات پیدا نشد (کلیدهای printer_host/netprint_host) — اتصال تست نشد.');
} else {
    $socket = @fsockopen($printerHost, $printerPort, $errNo, $errStr, 3.0);
    if ($socket !== false) {
        fclose($socket);
        $ok('پرینتر ' . $printerHost . ':' . $printerPort . ' پاسخ می‌دهد (اتصال TCP باز شد؛ بایتی ارسال نشد).');
    } else {
        $bad('به پرینتر ' . $printerHost . ':' . $printerPort . ' وصل نشد (' . $errStr . ' / ' . $errNo . ') ⇒ چاپ فردا کار نمی‌کند.');
    }
}

// ── ۵) آزمایش خشکِ مسیر چاپ (رندر فیش، بدون ارسال) ─────────────────────
$line("\n۵) آزمون خشکِ مسیر چاپ — رندر فیش نمونه، بدون ارسال به پرینتر");
if (!$dryPrint) {
    $info('با --no-dry-print رد شد.');
} elseif (!function_exists('food_ticket_text') && !function_exists('food_ticket_netprint_preview')) {
    $warn('توابع رندر فیش پیدا نشد — آزمون خشک انجام نشد.');
} else {
    try {
        $sample = [
            'full_name' => 'تست پیش از پنجره', 'national_code' => '0000000000', 'personnel_code' => 'TEST',
            'food_type' => 'چلوکباب کوبیده (ویژه)', 'punch_date' => date('Y-m-d'), 'punch_time' => date('H:i:s'),
            'event_type' => 'printed',
        ];
        if (function_exists('food_ticket_text')) {
            $text = food_ticket_text($sample);
            strlen((string) $text) > 20
                ? $ok('متن فیش ساخته شد (' . strlen((string) $text) . ' بایت) — مسیر رندر سالم است.')
                : $bad('متن فیش خالی یا ناقص ساخته شد ⇒ رندر فیش خراب است.');
        }
        if (function_exists('food_ticket_netprint_render_png') || function_exists('food_ticket_print_png')) {
            $ok('تابع رندر PNG در دسترس است.');
        } else {
            $info('تابع رندر PNG پیدا نشد (نام‌ها متفاوت‌اند) — متنی تست شد.');
        }
    } catch (Throwable $e) {
        $bad('آزمون خشک چاپ: ' . $e->getMessage());
    }
}

// ── ۶) حکم نهایی ────────────────────────────────────────────────────────
$line("\n══════════════════════════════════════════════════════════════");
if ($fails === 0 && $warns === 0) {
    $line(' ✅ آمادهٔ فردا — هیچ ایرادی پیدا نشد.');
    $line(' انتظار: ساعت ۱۱:۰۰ پنجره باز می‌شود، ترددها خوانده و فیش‌ها چاپ می‌شوند.');
} elseif ($fails === 0) {
    $line(' ⚠️ کار می‌کند ولی ' . $warns . ' هشدار دارد — قبل از ساعت ۱۱ مرور کنید.');
} else {
    $line(' ❌ ' . $fails . ' ایراد مسدودکننده — چاپ فردا کار نمی‌کند. همین حالا اصلاح کنید.');
    $line(' (زمان باقی‌مانده تا ساعت ۱۱: ' . max(0, 11 - (int) $nowTz->format('G')) . ' ساعت)');
}
$line(' FAIL = ' . $fails . ' | WARN = ' . $warns);
$line('══════════════════════════════════════════════════════════════');
$line(' اگر FAIL بود، خروجی کامل را برای بررسی بفرستید: prewindow-check.txt');
$line(' برای اسکن عمیق‌تر:  php tools\\diagnose_food_full.php > diag-food-full.txt 2>&1');
exit($fails === 0 ? 0 : 1);
