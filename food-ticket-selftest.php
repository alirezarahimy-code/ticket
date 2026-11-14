<?php
declare(strict_types=1);

/**
 * food-ticket-selftest.php — آزمون خودکار «کارگر، چاپ و مدیریت سامانه».
 *
 * یک کتابخانهٔ مشترک: هم پنل (بخش «اتصال پایگاه‌ها») و هم ابزار خط فرمان
 * (tools/prewindow_check.php) از همین نتیجه استفاده می‌کنند تا حکمِ پنل و
 * حکمِ CLI هرگز با هم اختلاف پیدا نکنند.
 *
 * قواعد طراحی:
 *   • فقط‌خواندنی: هیچ INSERT/UPDATE/DELETE، هیچ تغییر تنظیمی، هیچ بایت به چاپگر.
 *   • هر بررسی یک «وضعیت» دارد: ok | warn | fail | info | skip
 *   • «درست بیکار» از «خراب» جدا می‌شود: بیرون از پنجرهٔ کاری، خالی بودن صف
 *     و قدیمی بودن heartbeat طبیعی است و نباید FAIL بدهد.
 */

if (!function_exists('food_ticket_selftest_sections')) {

/**
 * اجرای کامل آزمون و برگرداندن ساختار بخش‌ها/بررسی‌ها.
 *
 * @param array $opts  [
 *   'config'   => array   تنظیمات (اگر ندهید خودش می‌خواند)
 *   'at'       => ?DateTimeImmutable  زمان مرجع (پیش‌فرض: اکنون)
 *   'dry_print'=> bool    آزمون خشک رندر فیش (پیش‌فرض true)
 *   'source'   => string  'panel' | 'cli' (فقط برای عنوان گزارش)
 * ]
 * @return array{sections: array<int,array>, verdict: array, generated_at: string, elapsed_ms: int, source: string}
 */
function food_ticket_selftest_run(array $opts = []): array
{
    $startedAt = microtime(true);

    $config = $opts['config'] ?? null;
    if (!is_array($config)) {
        $config = function_exists('food_ticket_config') ? (array) food_ticket_config(true) : [];
    }

    $tz = function_exists('food_ticket_window_timezone')
        ? food_ticket_window_timezone()
        : new DateTimeZone('Asia/Tehran');
    $now = $opts['at'] ?? new DateTimeImmutable('now', $tz);
    if (!$now instanceof DateTimeImmutable) {
        $now = new DateTimeImmutable('now', $tz);
    }
    $now = $now->setTimezone($tz);

    $dryPrint = (bool) ($opts['dry_print'] ?? true);
    $source = (string) ($opts['source'] ?? 'panel');

    $sections = [];

    // ── ۱) تنظیمات پایه ────────────────────────────────────────────────
    $s = food_ticket_selftest_section('config', 'تنظیمات پایه و نصب');
    $processorEnabled = (int) ($config['enabled'] ?? 0) === 1;
    $s['checks'][] = food_ticket_selftest_check(
        'پردازشگر فیش غذا',
        $processorEnabled ? 'ok' : 'fail',
        $processorEnabled
            ? 'فعال است؛ Worker اجازهٔ ساخت فیش دارد.'
            : 'غیرفعال است؛ در این حالت هیچ فیشی ساخته یا چاپ نمی‌شود.',
        $processorEnabled ? '' : 'از همین صفحه (تنظیمات اتصال) پردازشگر را فعال کنید.'
    );

    $installed = function_exists('food_ticket_installed') ? (bool) food_ticket_installed() : null;
    if ($installed === null) {
        // بدون تابع نصب (مثلاً اجرای مستقل کتابخانه) نمی‌توان حکم داد؛ FAIL نادرست ممنوع.
        $s['checks'][] = food_ticket_selftest_check(
            'نصب جداول داخلی',
            'skip',
            'تابع food_ticket_installed در دسترس نیست (food-ticket.php بارگذاری نشده).',
            ''
        );
    } else {
        $s['checks'][] = food_ticket_selftest_check(
            'نصب جداول داخلی',
            $installed ? 'ok' : 'fail',
            $installed
                ? 'جداول food_ticket_config و food_ticket_events موجودند.'
                : 'جداول اصلی فیش غذا در پایگاه‌داده پیدا نشد.',
            $installed ? '' : 'مهاجرت upgrade-1.9-food-ticket.sql اجرا نشده است.'
        );
    }

    $appKeyOk = (string) cfg('app.key', '') !== '';
    $s['checks'][] = food_ticket_selftest_check(
        'کلید رمزنگاری (app.key)',
        $appKeyOk ? 'ok' : 'warn',
        $appKeyOk
            ? 'کلید برنامه تنظیم شده است؛ رمز Access قابل ذخیره/بازخوانی است.'
            : 'app.key خالی است؛ رمز Access ذخیره نمی‌شود و اتصال تردد شکست می‌خورد.',
        $appKeyOk ? '' : 'مقدار app.key را در config.php تنظیم کنید.'
    );

    // پنجرهٔ کاری: فقط اگر «فعال» باشد معنا دارد.
    if (function_exists('food_ticket_window_state')) {
        try {
            $ws = food_ticket_window_state($config, $now);
            $winEnabled = (bool) ($ws['enabled'] ?? false);
            $winOpen = (bool) ($ws['open'] ?? false);
            $range = trim((string) ($ws['start'] ?? '') . '–' . (string) ($ws['end'] ?? ''), '–');
            if (!$winEnabled) {
                $s['checks'][] = food_ticket_selftest_check(
                    'پنجرهٔ کاری تردد',
                    'warn',
                    'پنجره غیرفعال است؛ Worker شبانه‌روز (۲۴/۷) کار می‌کند نه فقط ۱۱ تا ۱۴.',
                    'اگر می‌خواهید فقط در بازهٔ تردد کار کند، پنجره را فعال و 11:00–14:00 تنظیم کنید.'
                );
            } else {
                $s['checks'][] = food_ticket_selftest_check(
                    'پنجرهٔ کاری تردد',
                    'ok',
                    'فعال است؛ بازه ' . ($range !== '' ? $range : '—')
                        . ' — همین حالا ' . ($winOpen ? 'باز' : 'بسته')
                        . (($ws['next_open_label'] ?? '') !== '' ? ' | باز شدن بعدی: ' . $ws['next_open_label'] : ''),
                    ''
                );
            }
        } catch (Throwable $e) {
            $s['checks'][] = food_ticket_selftest_check('پنجرهٔ کاری تردد', 'warn', 'محاسبه نشد: ' . $e->getMessage(), '');
        }
    } else {
        $s['checks'][] = food_ticket_selftest_check('پنجرهٔ کاری تردد', 'skip', 'فایل food-ticket-window.php بارگذاری نشده است.', '');
    }
    $sections[] = $s;

    // ── ۲) Worker: زنده است یا فقط بیکار؟ ──────────────────────────────
    $s = food_ticket_selftest_section('worker', 'Worker — زنده‌بودن و درستی بیکاری');
    $windowOpenNow = false;
    $windowEnabledNow = false;
    if (function_exists('food_ticket_window_state')) {
        try {
            $wsNow = food_ticket_window_state($config, $now);
            $windowOpenNow = (bool) ($wsNow['open'] ?? false);
            $windowEnabledNow = (bool) ($wsNow['enabled'] ?? false);
        } catch (Throwable) {
        }
    }
    // «درست بیکار»: پنجره فعال است، بسته است، و ردیف واجد شرایطی هم نمانده.
    $legitimatelyIdle = $windowEnabledNow && !$windowOpenNow;

    $workerScript = APP_ROOT . '/cron/food_ticket_worker.php';
    $s['checks'][] = food_ticket_selftest_check(
        'فایل Worker',
        is_file($workerScript) ? 'ok' : 'fail',
        is_file($workerScript) ? 'cron/food_ticket_worker.php موجود است.' : 'cron/food_ticket_worker.php پیدا نشد.',
        is_file($workerScript) ? '' : 'بستهٔ به‌روزرسانی ناقص کپی شده؛ فایل Worker را بازگردانید.'
    );

    // heartbeat فایل (Worker هر چرخه می‌نویسد)
    $hbFile = APP_ROOT . '/storage/food_worker.heartbeat';
    if (is_file($hbFile)) {
        $age = max(0, time() - (int) @filemtime($hbFile));
        $ageLabel = food_ticket_selftest_age_label($age);
        if ($age < 180) {
            $status = 'ok';
            $msg = 'Worker زنده است (آخرین چرخه ' . $ageLabel . ' پیش).';
        } elseif ($age < 900) {
            $status = $legitimatelyIdle ? 'ok' : 'warn';
            $msg = 'آخرین چرخه ' . $ageLabel . ' پیش بود'
                . ($legitimatelyIdle ? ' — بیرون از پنجره طبیعی است.' : '؛ دیرتر از حد معمول.');
        } else {
            $status = $legitimatelyIdle ? 'warn' : 'fail';
            $msg = 'Worker ' . $ageLabel . ' است که اثری ننوشته'
                . ($legitimatelyIdle ? ' (پنجره بسته است، ولی heartbeat هم باید باقی بماند).' : '.');
        }
        $s['checks'][] = food_ticket_selftest_check('ضربان Worker (فایل)', $status, $msg,
            $status === 'ok' ? '' : 'Task Scheduler را باز کنید و آخرین نتیجهٔ اجرای food-ticket-worker را ببینید.');
    } else {
        $s['checks'][] = food_ticket_selftest_check(
            'ضربان Worker (فایل)',
            $legitimatelyIdle ? 'warn' : 'fail',
            'فایل storage/food_worker.heartbeat نیست؛ Worker حداقل یک‌بار هم اجرا نشده.',
            'زمان‌بند (Task Scheduler) را بررسی کنید؛ این فایل با هر چرخه ساخته می‌شود.'
        );
    }

    // heartbeat پایگاه‌داده
    try {
        if (db_table_exists('food_ticket_worker_status')) {
            $row = db()->query('SELECT host_name, process_id, last_seen, status, last_message, processed_count, printed_count, error_count FROM food_ticket_worker_status WHERE id = 1')->fetch();
            if ($row) {
                $lastSeenTs = strtotime((string) $row['last_seen']);
                $dbAge = $lastSeenTs !== false ? max(0, time() - $lastSeenTs) : PHP_INT_MAX;
                $st = (string) ($row['status'] ?? '');
                $status = $st === 'error' ? 'fail' : ($dbAge < 300 ? 'ok' : ($legitimatelyIdle ? 'warn' : 'fail'));
                if ($st === 'error') {
                    $status = 'fail';
                }
                $s['checks'][] = food_ticket_selftest_check(
                    'ضربان Worker (پایگاه‌داده)',
                    $status,
                    'آخرین ثبت: ' . (string) $row['last_seen'] . ' | وضعیت: ' . ($st !== '' ? $st : '—')
                        . ' | پرینت: ' . (int) ($row['printed_count'] ?? 0) . ' | خطا: ' . (int) ($row['error_count'] ?? 0)
                        . ' | ' . mb_substr(trim((string) ($row['last_message'] ?? '')), 0, 160),
                    $status === 'ok' ? '' : 'پیام خطای Worker را در ستون last_message و لاگ storage ببینید.'
                );
            } else {
                $s['checks'][] = food_ticket_selftest_check(
                    'ضربان Worker (پایگاه‌داده)',
                    $legitimatelyIdle ? 'warn' : 'fail',
                    'جدول وضعیت Worker خالی است؛ Worker هرگز موفق به ثبت وضعیت نشده.',
                    'یک‌بار Worker را دستی اجرا کنید: php cron\\food_ticket_worker.php'
                );
            }
        } else {
            $s['checks'][] = food_ticket_selftest_check(
                'ضربان Worker (پایگاه‌داده)',
                'skip',
                'جدول food_ticket_worker_status نیست (مهاجرت ۰۳۱ اجرا نشده).',
                ''
            );
        }
    } catch (Throwable $e) {
        $s['checks'][] = food_ticket_selftest_check('ضربان Worker (پایگاه‌داده)', 'warn', 'خواندن وضعیت: ' . $e->getMessage(), '');
    }

    // فایل قفل نمونه (Worker در حال اجرا)
    foreach (['storage/food-worker-process.lock', 'storage/food_ticket_queue.lock'] as $rel) {
        $p = APP_ROOT . '/' . $rel;
        if (!is_file($p)) {
            continue;
        }
        $lockAge = max(0, time() - (int) @filemtime($p));
        if ($lockAge > 600) {
            $s['checks'][] = food_ticket_selftest_check(
                'قفل کهنهٔ Worker',
                'warn',
                basename($rel) . ' از ' . food_ticket_selftest_age_label($lockAge) . ' پیش مانده است.',
                'اگر Worker مطمئناً در حال اجرا نیست، فایل قفل را حذف کنید.'
            );
        }
    }
    $s['checks'][] = food_ticket_selftest_check(
        'وضعیت بیکاری',
        'info',
        $legitimatelyIdle
            ? 'خارج از پنجرهٔ کاری: Worker باید عمداً بیکار باشد — این «کارکرد درست» است، نه خرابی.'
            : ($windowEnabledNow
                ? 'داخل پنجرهٔ کاری: Worker باید فعال باشد و ترددها را بخواند.'
                : 'پنجره غیرفعال است: Worker شبانه‌روز فعال است.'),
        ''
    );
    $sections[] = $s;

    // ── ۳) صف چاپ ─────────────────────────────────────────────────────
    $s = food_ticket_selftest_section('queue', 'صف چاپ — کارهای مانده و گیرکرده');
    $queueDue = null;
    try {
        db()->query('SELECT 1');
        $s['checks'][] = food_ticket_selftest_check('اتصال پایگاه‌دادهٔ داخلی', 'ok', 'اتصال برقرار است.', '');

        if (db_table_exists('food_ticket_events')) {
            // همان شرط انتخابِ Worker — تا عددِ گزارش، همان عددِ واقعی باشد که Worker می‌گیرد.
            $claimable = "(print_status IN ('pending','print_error','failed') OR (print_status = 'printing' AND updated_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)))"
                . ' AND (next_retry_at IS NULL OR next_retry_at <= NOW())';
            $queueDue = (int) db()->query('SELECT COUNT(*) FROM food_ticket_events WHERE ' . $claimable)->fetchColumn();

            if ($queueDue > 0) {
                // اگر پنجره بسته است و Worker هم بیکار، این‌ها طبیعی‌اند و باید صبر کنند.
                $status = $windowOpenNow ? 'fail' : 'warn';
                $s['checks'][] = food_ticket_selftest_check(
                    'ردیف‌های واجد شرایط چاپ',
                    $status,
                    $queueDue . ' ردیف آمادهٔ چاپ مانده است'
                        . ($windowOpenNow ? ' — با Worker زنده یعنی چاپ گیر کرده.' : ' — پنجره بسته است، پس در بازهٔ کاری چاپ می‌شوند.'),
                    $windowOpenNow ? 'چاپگر و مسیر چاپ را بررسی کنید؛ سپس دکمهٔ «چاپ مجدد» صف را تخلیه می‌کند.' : ''
                );
            } else {
                $s['checks'][] = food_ticket_selftest_check('ردیف‌های واجد شرایط چاپ', 'ok', 'هیچ ردیف واجد شرایطی نمانده؛ صف تمیز است.', '');
            }

            $printing = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status = 'printing'")->fetchColumn();
            if ($printing > 0) {
                $oldest = (string) db()->query("SELECT MIN(updated_at) FROM food_ticket_events WHERE print_status = 'printing'")->fetchColumn();
                $stuckMinutes = 0;
                $ts = strtotime($oldest);
                if ($ts !== false) {
                    $stuckMinutes = (int) max(0, (time() - $ts) / 60);
                }
                $s['checks'][] = food_ticket_selftest_check(
                    'ردیف‌های گیرکرده در وضعیت printing',
                    $stuckMinutes > 30 ? 'fail' : 'warn',
                    $printing . ' ردیف در وضعیت «در حال چاپ» است؛ قدیمی‌ترین از ' . $oldest . ' (' . $stuckMinutes . ' دقیقه پیش).',
                    'ردیف بیش از ۳۰ دقیقه در چاپ گیرکرده، صف را قفل می‌کند؛ وضعیت آن را اصلاح کنید.'
                );
            } else {
                $s['checks'][] = food_ticket_selftest_check('ردیف‌های گیرکرده در وضعیت printing', 'ok', 'هیچ ردیف گیرکرده‌ای نیست.', '');
            }

            // توزیع تلاش مجدد
            try {
                $retryRows = db()->query("SELECT retry_count, COUNT(*) AS n FROM food_ticket_events WHERE print_status IN ('failed','print_error') GROUP BY retry_count ORDER BY retry_count DESC LIMIT 5")->fetchAll();
                $retryTotal = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE print_status IN ('failed','print_error')")->fetchColumn();
                if ($retryTotal > 0) {
                    $parts = [];
                    foreach ($retryRows as $r) {
                        $parts[] = 'تلاش ' . (int) $r['retry_count'] . ': ' . (int) $r['n'];
                    }
                    $maxRetry = (int) db()->query("SELECT COALESCE(MAX(retry_count),0) FROM food_ticket_events WHERE print_status IN ('failed','print_error')")->fetchColumn();
                    $s['checks'][] = food_ticket_selftest_check(
                        'فیش‌های ناموفق و تلاش مجدد',
                        $maxRetry >= 3 ? 'warn' : 'info',
                        $retryTotal . ' فیش در وضعیت ناموفق (' . implode(' | ', $parts) . ')'
                            . ($maxRetry >= 3 ? ' — بعضی به سقف تلاش رسیده‌اند.' : ''),
                        $maxRetry >= 3 ? 'علت را در last_print_error ببینید؛ اگر چاپگر بود، پس از اصلاح «چاپ مجدد» بزنید.' : ''
                    );
                } else {
                    $s['checks'][] = food_ticket_selftest_check('فیش‌های ناموفق و تلاش مجدد', 'ok', 'هیچ فیش ناموفقی در صف نیست.', '');
                }
            } catch (Throwable $e) {
                $s['checks'][] = food_ticket_selftest_check('فیش‌های ناموفق و تلاش مجدد', 'info', 'خوانده نشد: ' . $e->getMessage(), '');
            }

            // امروز: مقایسهٔ ساده
            try {
                $todayRows = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE punch_date = CURDATE()")->fetchColumn();
                $todayPrinted = (int) db()->query("SELECT COUNT(*) FROM food_ticket_events WHERE punch_date = CURDATE() AND print_status = 'printed'")->fetchColumn();
                $s['checks'][] = food_ticket_selftest_check(
                    'عملکرد امروز',
                    'info',
                    'امروز ' . $todayRows . ' رخداد ثبت و ' . $todayPrinted . ' فیش چاپ شده است'
                        . ($todayRows === 0 && $windowOpenNow ? ' — در پنجرهٔ باز، صفر بودن یعنی ترددی خوانده نشده.' : '.'),
                    ''
                );
            } catch (Throwable) {
            }
        } else {
            $s['checks'][] = food_ticket_selftest_check('جدول رخدادها', 'fail', 'جدول food_ticket_events نیست.', 'مهاجرت پایه را اجرا کنید.');
        }
    } catch (Throwable $e) {
        $s['checks'][] = food_ticket_selftest_check('اتصال پایگاه‌دادهٔ داخلی', 'fail', 'اتصال برقرار نشد: ' . $e->getMessage(), 'config.php و سرویس MySQL را بررسی کنید.');
    }
    $sections[] = $s;

    // ── ۴) منبع تردد (Access) ─────────────────────────────────────────
    $s = food_ticket_selftest_section('attendance', 'منبع تردد — Access');
    $attendancePath = trim((string) ($config['attendance_path'] ?? ''));
    if ($attendancePath === '') {
        $s['checks'][] = food_ticket_selftest_check(
            'مسیر فایل تردد',
            'fail',
            'مسیر فایل تردد در تنظیمات خالی است.',
            'از همین صفحه مسیر فایل Access را وارد و ذخیره کنید.'
        );
    } else {
        $s['checks'][] = food_ticket_selftest_check(
            'مسیر فایل تردد',
            'ok',
            'مسیر تنظیم شده: ' . $attendancePath,
            ''
        );
        // اگر مسیر روی همین سرور است، وجود فایل را بسنج (بدون باز کردن قفل Access).
        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $attendancePath) === 1 || str_starts_with($attendancePath, '\\\\')) {
            $s['checks'][] = food_ticket_selftest_check(
                'دسترسی به فایل Access از این سرور',
                is_file($attendancePath) ? 'ok' : 'warn',
                is_file($attendancePath) ? 'فایل روی همین سرور دیده می‌شود.' : 'فایل از این سرور دیده نشد (اگر سرور دیگری است، طبیعی است).',
                ''
            );
        }

        $srcDb = null;
        try {
            if (!function_exists('food_ticket_odbc')) {
                throw new RuntimeException('food-ticket-netprint.php بارگذاری نشده است.');
            }
            $srcDb = food_ticket_odbc($attendancePath, food_ticket_attendance_password($config));
            $rows = food_ticket_access_rows($srcDb, food_ticket_source_table(), 3);
            $count = is_array($rows) ? count($rows) : 0;
            $s['checks'][] = food_ticket_selftest_check(
                'اتصال و خواندن از Access',
                'ok',
                'اتصال برقرار شد و ' . $count . ' ردیف نمونه خوانده شد.',
                ''
            );
        } catch (Throwable $e) {
            $s['checks'][] = food_ticket_selftest_check(
                'اتصال و خواندن از Access',
                'fail',
                'اتصال ناموفق: ' . mb_substr($e->getMessage(), 0, 200),
                'مسیر، رمز، درایور ODBC Access و دسترسی پوشه را بررسی کنید.'
            );
        } finally {
            if (function_exists('food_ticket_access_close')) {
                food_ticket_access_close($srcDb);
            }
        }
    }
    $s['checks'][] = food_ticket_selftest_check(
        'حذف ردیف منبع پس از چاپ',
        !empty($config['cut_source_rows']) ? 'info' : 'ok',
        !empty($config['cut_source_rows'])
            ? 'فعال است: ردیف SOURCE_TABLE پس از ثبت حذف می‌شود (قابل بازگشت نیست).'
            : 'غیرفعال است: ردیف‌های منبع دست‌نخورده می‌مانند.',
        ''
    );
    $sections[] = $s;

    // ── ۵) چاپگر و مسیر چاپ ───────────────────────────────────────────
    $s = food_ticket_selftest_section('printer', 'چاپگر و مسیر چاپ');
    $mode = function_exists('food_ticket_normalize_printer_mode')
        ? food_ticket_normalize_printer_mode($config['printer_mode'] ?? 'tcp_raw')
        : (string) ($config['printer_mode'] ?? 'tcp_raw');
    $host = trim((string) ($config['printer_host'] ?? ''));
    $port = (int) ($config['printer_port'] ?? 9100);
    $share = trim((string) ($config['printer_share'] ?? ''));

    if ($mode === 'tcp_raw') {
        if ($host === '') {
            $s['checks'][] = food_ticket_selftest_check(
                'آدرس چاپگر شبکه',
                'fail',
                'حالت چاپ «شبکه (IP:9100)» است ولی IP چاپگر خالی است.',
                'در همین صفحه IP چاپگر را وارد و ذخیره کنید.'
            );
        } else {
            $s['checks'][] = food_ticket_selftest_check('آدرس چاپگر شبکه', 'ok', 'چاپگر: ' . $host . ':' . $port, '');
            $errNo = 0;
            $errStr = '';
            $sock = @fsockopen($host, $port > 0 ? $port : 9100, $errNo, $errStr, 3.0);
            if (is_resource($sock)) {
                fclose($sock);
                $s['checks'][] = food_ticket_selftest_check(
                    'اتصال TCP به چاپگر',
                    'ok',
                    'اتصال به ' . $host . ':' . ($port > 0 ? $port : 9100) . ' برقرار شد (هیچ بایتی ارسال نشد).',
                    ''
                );
            } else {
                $s['checks'][] = food_ticket_selftest_check(
                    'اتصال TCP به چاپگر',
                    'fail',
                    'به ' . $host . ':' . ($port > 0 ? $port : 9100) . ' وصل نشد (' . ($errStr !== '' ? $errStr : 'خطا ' . $errNo) . ').',
                    'روشن بودن چاپگر، کابل/شبکه و باز بودن پورت ۹۱۰۰ را بررسی کنید.'
                );
            }
        }
    } else {
        if ($share === '' && $host === '') {
            $s['checks'][] = food_ticket_selftest_check(
                'نام چاپگر ویندوز',
                'fail',
                'حالت چاپ «صف ویندوز» است ولی نام چاپگر تنظیم نشده.',
                'از فهرست چاپگرهای نصب‌شده یکی را انتخاب و ذخیره کنید.'
            );
        } else {
            $name = $share !== '' ? $share : $host;
            $isWindows = stripos(PHP_OS, 'WIN') === 0;
            $exists = false;
            if ($isWindows) {
                $out = [];
                $code = 0;
                @exec('powershell -NoProfile -Command "Get-Printer -Name ' . str_replace('\'', "''", $name) . ' | Select-Object -ExpandProperty Name" 2>&1', $out, $code);
                $exists = $code === 0 && trim(implode('', $out)) !== '';
            }
            $s['checks'][] = food_ticket_selftest_check(
                'چاپگر ویندوز',
                !$isWindows ? 'info' : ($exists ? 'ok' : 'fail'),
                !$isWindows
                    ? 'بررسی چاپگر ویندوز فقط روی سرور ویندوز معنا دارد (این سرور ' . PHP_OS . ' است).'
                    : ($exists ? 'چاپگر «' . $name . '» روی سرور نصب است.' : 'چاپگر «' . $name . '» در فهرست چاپگرهای ویندوز پیدا نشد.'),
                $exists ? '' : 'روی سرور ویندوز درایور چاپگر را نصب کنید یا به حالت IP:9100 بروید.'
            );
        }
    }

    // آزمون خشک مسیر رندر: همان تابعی که فیش واقعی را می‌سازد.
    if (!$dryPrint) {
        $s['checks'][] = food_ticket_selftest_check('آزمون خشک رندر فیش', 'skip', 'با تنظیمات غیرفعال شده است.', '');
    } elseif (!function_exists('food_ticket_render_from_template') && !function_exists('food_ticket_text')) {
        $s['checks'][] = food_ticket_selftest_check('آزمون خشک رندر فیش', 'warn', 'توابع رندر فیش بارگذاری نشده‌اند.', '');
    } else {
        try {
            $sample = [
                'full_name' => 'آزمون داخلی سیستم',
                'national_code' => '0000000000',
                'personnel_code' => 'TEST',
                'food_type' => 'چلوکباب کوبیده (آزمون)',
                'punch_date' => $now->format('Y-m-d'),
                'punch_time' => $now->format('H:i:s'),
                'event_type' => 'printed',
            ];
            $text = '';
            if (function_exists('food_ticket_render_from_template')) {
                $tpl = function_exists('food_ticket_template') ? food_ticket_template() : null;
                $text = (string) food_ticket_render_from_template($sample, $tpl);
            } elseif (function_exists('food_ticket_text')) {
                $text = (string) food_ticket_text($sample);
            }
            $bytes = strlen($text);
            if ($bytes > 40) {
                $s['checks'][] = food_ticket_selftest_check(
                    'آزمون خشک رندر فیش',
                    'ok',
                    'فیش نمونه ساخته شد (' . $bytes . ' بایت) — قالب و مسیر رندر سالم است. هیچ بایتی به چاپگر نرفت.',
                    ''
                );
            } else {
                $s['checks'][] = food_ticket_selftest_check(
                    'آزمون خشک رندر فیش',
                    'fail',
                    'فیش نمونه خالی یا ناقص ساخته شد (' . $bytes . ' بایت).',
                    'قالب فیش (storage/food_ticket_template.json) یا فایل‌های رندر را بررسی کنید.'
                );
            }
        } catch (Throwable $e) {
            $s['checks'][] = food_ticket_selftest_check('آزمون خشک رندر فیش', 'fail', 'رندر ناموفق: ' . mb_substr($e->getMessage(), 0, 200), '');
        }
    }

    // توان نوشتن در پوشهٔ آرشیو چاپ
    $printDir = APP_ROOT . '/storage/food_ticket_prints';
    $s['checks'][] = food_ticket_selftest_check(
        'قابل‌نوشتن بودن پوشهٔ آرشیو چاپ',
        is_dir($printDir) ? (is_writable($printDir) ? 'ok' : 'fail') : 'warn',
        is_dir($printDir)
            ? (is_writable($printDir) ? 'پوشهٔ storage/food_ticket_prints قابل نوشتن است.' : 'پوشهٔ آرشیو چاپ قابل نوشتن نیست؛ چاپ ویندوز شکست می‌خورد.')
            : 'پوشهٔ storage/food_ticket_prints هنوز ساخته نشده (اولین چاپ می‌سازد).',
        ''
    );
    $sections[] = $s;

    // ── ۶) زمان‌بند و مدیریت سامانه ────────────────────────────────────
    $s = food_ticket_selftest_section('management', 'مدیریت سامانه — زمان‌بند، لاگ و مجوز');
    $cronScripts = [
        'cron/food_ticket_worker.php' => 'Worker چاپ فیش',
        'cron/food_ticket_worker_status.php' => 'گزارش وضعیت Worker',
    ];
    foreach ($cronScripts as $rel => $label) {
        $p = APP_ROOT . '/' . $rel;
        if (is_file($p)) {
            $s['checks'][] = food_ticket_selftest_check($label, 'ok', $rel . ' موجود است.', '');
        }
    }

    // زمان‌بند ویندوز: وجود تسک
    if (stripos(PHP_OS, 'WIN') === 0) {
        $out = [];
        $code = 0;
        @exec('schtasks /Query /TN "food-ticket-worker" /FO LIST 2>&1', $out, $code);
        $text = trim(implode(' ', $out));
        if ($code === 0 && stripos($text, 'food-ticket-worker') !== false) {
            $s['checks'][] = food_ticket_selftest_check('زمان‌بند ویندوز (Task Scheduler)', 'ok', 'تسک food-ticket-worker ثبت شده است.', '');
        } else {
            $s['checks'][] = food_ticket_selftest_check(
                'زمان‌بند ویندوز (Task Scheduler)',
                'warn',
                'تسک food-ticket-worker با این نام پیدا نشد (ممکن است نام دیگری داشته باشد).',
                'خروجی schtasks /Query را ببینید؛ نام تسک را در تنظیمات بررسی کنید.'
            );
        }
    } else {
        $s['checks'][] = food_ticket_selftest_check(
            'زمان‌بند ویندوز (Task Scheduler)',
            'info',
            'این سرور ویندوز نیست (' . PHP_OS . ')؛ بررسی زمان‌بند انجام نشد.',
            ''
        );
    }

    // مجوز پوشه‌های حساس
    foreach (['storage', 'storage/food_ticket_logs'] as $rel) {
        $p = APP_ROOT . '/' . $rel;
        if (!is_dir($p)) {
            continue;
        }
        $writable = is_writable($p);
        $s['checks'][] = food_ticket_selftest_check(
            'دسترسی نوشتن: ' . $rel,
            $writable ? 'ok' : 'fail',
            $writable ? 'قابل نوشتن است.' : 'قابل نوشتن نیست؛ Worker نمی‌تواند heartbeat/لاگ بنویسد.',
            $writable ? '' : 'مجوز نوشتن کاربر سرویس وب و کاربر Worker را روی این پوشه بدهید.'
        );
    }

    // لاگ‌های اخیر با خطا
    try {
        $logDir = APP_ROOT . '/storage/food_ticket_logs';
        $recentErrors = 0;
        if (is_dir($logDir)) {
            $files = glob($logDir . '/*.log') ?: [];
            usort($files, static fn (string $a, string $b): int => (int) @filemtime($b) <=> (int) @filemtime($a));
            foreach (array_slice($files, 0, 3) as $f) {
                $tail = (string) @file_get_contents($f, false, null, 0, 262144);
                $recentErrors += preg_match_all('/\b(Fatal|Uncaught|SQLSTATE|Exception)\b/i', $tail);
            }
            $s['checks'][] = food_ticket_selftest_check(
                'خطاهای اخیر در لاگ‌ها',
                $recentErrors > 0 ? 'warn' : 'ok',
                $recentErrors > 0
                    ? $recentErrors . ' نشانهٔ خطا در سه لاگ آخر پیدا شد.'
                    : 'در سه لاگ آخر خطای جدی پیدا نشد.',
                $recentErrors > 0 ? 'متن خطا را در storage/food_ticket_logs ببینید.' : ''
            );
        }
    } catch (Throwable) {
    }

    // مجوز کاربر جاری
    $viewAllowed = !function_exists('user_can') || user_can($GLOBALS['user'] ?? [], 'food.db');
    $s['checks'][] = food_ticket_selftest_check(
        'مجوز مشاهدهٔ این بخش',
        'info',
        $viewAllowed ? 'کاربر جاری اجازهٔ «اتصال پایگاه‌ها» دارد.' : 'کاربر جاری اجازهٔ «اتصال پایگاه‌ها» را ندارد.',
        ''
    );
    $sections[] = $s;

    // ── ۷) آمادگی بازهٔ کاری بعدی ──────────────────────────────────────
    $s = food_ticket_selftest_section('readiness', 'آمادگی بازهٔ کاری بعدی');
    if (function_exists('food_ticket_window_state')) {
        $baseDay = $now->setTime(0, 0);
        $probes = [];
        for ($d = 0; $d <= 7; $d++) {
            $day = $baseDay->modify('+' . $d . ' day');
            try {
                $ws = food_ticket_window_state($config, $day->setTime(11, 0));
                $enabled = (bool) ($ws['enabled'] ?? false);
                $open = (bool) ($ws['open'] ?? false);
                $probes[] = [
                    'date' => $day->format('Y-m-d'),
                    'dow' => $day->format('D'),
                    'enabled' => $enabled,
                    'open' => $open,
                    'reason' => (string) ($ws['reason'] ?? ''),
                ];
            } catch (Throwable) {
            }
        }
        $nextOpen = null;
        foreach ($probes as $p) {
            if ($p['open']) {
                $nextOpen = $p;
                break;
            }
        }
        if ($nextOpen !== null) {
            $s['checks'][] = food_ticket_selftest_check(
                'روز کاری بعدی با پنجرهٔ باز',
                'ok',
                'اولین روز باز در ساعت ۱۱:۰۰: ' . $nextOpen['date'] . ' (' . $nextOpen['dow'] . ')',
                ''
            );
        } else {
            $s['checks'][] = food_ticket_selftest_check(
                'روز کاری بعدی با پنجرهٔ باز',
                'fail',
                'در ۷ روز آینده هیچ روزی ساعت ۱۱:۰۰ باز نیست — یعنی یک هفتهٔ کامل چاپ تعطیل است.',
                'تنظیمات روزهای هفته (window_days) و تعطیلات را بررسی کنید.'
            );
        }
        $holidayProbes = array_values(array_filter($probes, static fn (array $p): bool => $p['enabled'] && !$p['open']));
        if ($holidayProbes !== []) {
            $labels = array_map(static fn (array $p): string => $p['date'] . '(' . $p['dow'] . ')', array_slice($holidayProbes, 0, 4));
            $s['checks'][] = food_ticket_selftest_check(
                'روزهای بسته در ۷ روز آینده',
                'info',
                'این روزها ساعت ۱۱ بسته‌اند: ' . implode('، ', $labels),
                ''
            );
        }
    } else {
        $s['checks'][] = food_ticket_selftest_check('پیش‌بینی روزهای آینده', 'skip', 'ماژول پنجرهٔ کاری بارگذاری نشده.', '');
    }
    $sections[] = $s;

    // ── جمع‌بندی ──────────────────────────────────────────────────────
    $counts = ['ok' => 0, 'warn' => 0, 'fail' => 0, 'info' => 0, 'skip' => 0];
    foreach ($sections as $section) {
        foreach ($section['checks'] as $check) {
            $st = (string) ($check['status'] ?? 'info');
            $counts[$st] = ($counts[$st] ?? 0) + 1;
        }
    }
    if ($counts['fail'] > 0) {
        $level = 'fail';
        $headline = $counts['fail'] . ' ایراد مسدودکننده — چاپ کار نمی‌کند.';
    } elseif ($counts['warn'] > 0) {
        $level = 'warn';
        $headline = 'کار می‌کند ولی ' . $counts['warn'] . ' هشدار دارد.';
    } else {
        $level = 'ok';
        $headline = 'همهٔ بررسی‌ها سالم است.';
    }

    return [
        'sections' => $sections,
        'verdict' => ['level' => $level, 'headline' => $headline, 'counts' => $counts],
        'generated_at' => $now->format('Y-m-d H:i:s'),
        'elapsed_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        'source' => $source,
        'window' => ['enabled' => $windowEnabledNow, 'open' => $windowOpenNow],
        'queue_due' => $queueDue,
    ];
}

/**
 * ساخت یک بخش خالی.
 *
 * @return array{key: string, title: string, checks: array<int,array>}
 */
function food_ticket_selftest_section(string $key, string $title): array
{
    return ['key' => $key, 'title' => $title, 'checks' => []];
}

/**
 * ساخت یک بررسی.
 *
 * @param string $status ok | warn | fail | info | skip
 * @return array{name: string, status: string, message: string, fix: string}
 */
function food_ticket_selftest_check(string $name, string $status, string $message, string $fix = ''): array
{
    if (!in_array($status, ['ok', 'warn', 'fail', 'info', 'skip'], true)) {
        $status = 'info';
    }
    return ['name' => $name, 'status' => $status, 'message' => $message, 'fix' => $fix];
}

/**
 * برچسب فارسی سن.
 */
function food_ticket_selftest_age_label(int $seconds): string
{
    if ($seconds < 90) {
        return $seconds . ' ثانیه';
    }
    if ($seconds < 5400) {
        return (int) round($seconds / 60) . ' دقیقه';
    }
    if ($seconds < 172800) {
        return (string) round($seconds / 3600, 1) . ' ساعت';
    }
    return (int) round($seconds / 86400) . ' روز';
}

/**
 * متن خوانا (برای کپی/دانلود گزارش از پنل و خروجی CLI).
 */
function food_ticket_selftest_text(array $result): string
{
    $labels = ['ok' => '[OK]', 'warn' => '[WARN]', 'fail' => '[FAIL]', 'info' => '[i]', 'skip' => '[--]'];
    $lines = [];
    $lines[] = 'آزمون جامع Worker / چاپ / مدیریت سامانه';
    $lines[] = 'زمان اجرا: ' . (string) ($result['generated_at'] ?? '') . ' | مدت: ' . (int) ($result['elapsed_ms'] ?? 0) . 'ms';
    $lines[] = str_repeat('=', 66);
    foreach ((array) ($result['sections'] ?? []) as $i => $section) {
        $lines[] = '';
        $lines[] = (string) ($i + 1) . ') ' . (string) ($section['title'] ?? '');
        foreach ((array) ($section['checks'] ?? []) as $check) {
            $st = (string) ($check['status'] ?? 'info');
            $lines[] = '  ' . str_pad($labels[$st] ?? '[i]', 7) . ' ' . (string) ($check['name'] ?? '') . ': ' . (string) ($check['message'] ?? '');
            if (($check['fix'] ?? '') !== '') {
                $lines[] = '          ↳ ' . (string) $check['fix'];
            }
        }
    }
    $lines[] = '';
    $lines[] = str_repeat('=', 66);
    $c = (array) ($result['verdict']['counts'] ?? []);
    $lines[] = 'حکم: ' . (string) ($result['verdict']['headline'] ?? '');
    $lines[] = 'OK=' . (int) ($c['ok'] ?? 0)
        . ' | WARN=' . (int) ($c['warn'] ?? 0)
        . ' | FAIL=' . (int) ($c['fail'] ?? 0)
        . ' | INFO=' . (int) ($c['info'] ?? 0)
        . ' | SKIP=' . (int) ($c['skip'] ?? 0);
    return implode(PHP_EOL, $lines);
}

} // function_exists guard
