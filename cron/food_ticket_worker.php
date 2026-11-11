<?php
declare(strict_types=1);

/**
 * Background Worker — بدون Browser / Panel / Session
 * پیش‌نیاز روشن بودن: سرور + دستگاه حضور و غیاب (فایل منبع تردد) + فیش‌پرینتر + شبکه بین آن‌ها
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/food-ticket.php';
foreach (['food-ticket-runtime.php', 'food-ticket-engine.php'] as $extra) {
    $p = dirname(__DIR__) . '/' . $extra;
    if (is_file($p)) {
        require_once $p;
    }
}

$loop = in_array('--loop', array_slice($argv, 1), true);
$queueOnly = in_array('--queue-only', array_slice($argv, 1), true);

// --max-minutes=N : بعد از N دقیقه با کد ۰ خارج می‌شود تا wrapper دوباره آن را بالا بیاورد
// (چرخش فایل لاگ روزانه + جلوگیری از نشت حافظه). پیش‌فرض: بدون محدودیت.
$maxMinutes = 0;
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--max-minutes=(\d+)$/', (string) $arg, $m)) {
        $maxMinutes = max(0, (int) $m[1]);
    }
}
$startedAt = time();

$storageDir = dirname(__DIR__) . '/storage';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0750, true);
}

// فقط یک worker زنده مجاز است (قفل تا پایان عمر پردازش نگه داشته می‌شود).
// کد خروج ۳ = نمونهٔ دیگری در حال اجراست؛ wrapper در این حالت حلقه را رها می‌کند.
$instanceLock = null;
if ($loop) {
    $instanceLock = @fopen($storageDir . '/food-worker-process.lock', 'c');
    if (!$instanceLock || !@flock($instanceLock, LOCK_EX | LOCK_NB)) {
        fwrite(STDOUT, '[food-ticket-worker] ' . date('c') . ' another worker instance is already running; exiting.' . PHP_EOL);
        exit(3);
    }
    @ftruncate($instanceLock, 0);
    @fwrite($instanceLock, (string) getmypid());
}

// حذف لاگ‌های قدیمی‌تر از ۱۴ روز
try {
    foreach ((array) glob($storageDir . '/food_ticket_logs/worker-*.log') as $oldLog) {
        if (is_file($oldLog) && filemtime($oldLog) < time() - 14 * 86400) {
            @unlink($oldLog);
        }
    }
} catch (Throwable) {
}

$heartbeatFile = $storageDir . '/food_worker.heartbeat';
$lastPrintedSig = '';
$lastPrintedAt = 0;

// ── پنجرهٔ کاری و کش تنظیمات ─────────────────────────────────────────────
// تنظیمات تا این تعداد ثانیه کش می‌شوند. قبلاً هر چرخه یک بار کامل از دیتابیس
// خوانده می‌شد؛ وقتی چرخه هر نیم ثانیه تکرار شود این هزینه خودش به تأخیر چاپ
// اضافه می‌شود. ۱۰ ثانیه آن‌قدر کوتاه هست که تغییر تنظیمات در پنل سریع اثر کند.
const FOOD_WORKER_CONFIG_TTL = 10;
$config = [];
$configLoadedAt = 0.0;
// مقادیر اولیه تا بخش خواب حتی در مسیرهای خطا هم متغیر تعریف‌نشده نبیند.
$windowState = ['active_poll_ms' => 500, 'idle_poll_seconds' => 60];
$readSource = true;
$drainQueue = true;
$lastEndOfDayRunDay = '';
$lastEndOfDayAttemptAt = 0;

fwrite(STDOUT, '[food-ticket-worker] ' . date('c') . ' start loop=' . ($loop ? '1' : '0') . ' pid=' . getmypid() . ' max_minutes=' . $maxMinutes . PHP_EOL);

/**
 * چند فیش هنوز چاپ نشده‌اند؟ بیرون از بازهٔ تردد هم اگر صف خالی نباشد باید تخلیه شود،
 * وگرنه فیشی که چاپگر در لحظهٔ آخر نپذیرفته تا فردا معطل می‌ماند.
 */
$foodWorkerPendingPrints = static function (): int {
    try {
        return (int) db()->query(
            'SELECT COUNT(*) FROM food_ticket_events
             WHERE print_status IN ("pending", "print_error", "failed")
               AND (next_retry_at IS NULL OR next_retry_at <= NOW())'
        )->fetchColumn();
    } catch (Throwable) {
        // ستون next_retry_at روی نصب‌های قدیمی نیست.
        try {
            return (int) db()->query(
                'SELECT COUNT(*) FROM food_ticket_events WHERE print_status IN ("pending", "print_error", "failed")'
            )->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }
};

do {
    if (function_exists('logging_begin_execution')) {
        logging_begin_execution('worker:food-ticket:cycle');
    }
    $cycle = ['processed' => 0, 'printed' => 0, 'errors' => 0, 'message' => ''];
    try {
        // اتصال دیتابیس فقط وقتی دوباره ساخته می‌شود که واقعاً قطع شده باشد.
        // نسخه‌های قبلی هر چرخه db(true) صدا می‌زدند یعنی یک TCP connect + احراز هویت
        // کامل در هر چرخه؛ وقتی چرخه هر نیم ثانیه اجرا شود این خودش بخشی از تأخیر است.
        try {
            db()->query('SELECT 1')->fetchColumn();
        } catch (Throwable) {
            try {
                db(true);
                db()->query('SELECT 1')->fetchColumn();
            } catch (Throwable $dbEx) {
                fwrite(STDERR, '[db-reconnect-wait] ' . $dbEx->getMessage() . PHP_EOL);
                if ($loop) {
                    sleep(5);
                    continue;
                }
                throw $dbEx;
            }
        }

        $tehranNow = new DateTimeImmutable('now', new DateTimeZone('Asia/Tehran'));
        $tehranDay = $tehranNow->format('Y-m-d');
        $tehranHour = (int) $tehranNow->format('G');

        // تنظیمات با TTL خوانده می‌شوند، نه در هر چرخه. setting_forget_cache() هم
        // به همان ریتم گره خورده تا کش تنظیمات عمومی بیش از TTL کهنه نماند.
        $nowFloat = microtime(true);
        if ($config === [] || ($nowFloat - $configLoadedAt) >= FOOD_WORKER_CONFIG_TTL) {
            if (function_exists('setting_forget_cache')) {
                setting_forget_cache();
            }
            $config = food_ticket_config(true);
            $configLoadedAt = $nowFloat;
        }

        // ── دروازهٔ پنجرهٔ کاری ──────────────────────────────────────────
        // بیرون از بازه‌ای که دستگاه تردد قبول می‌کند، هیچ ردیف تازه‌ای در Access
        // ساخته نمی‌شود؛ پس باز کردن اتصال Access کار اثبات‌شده بی‌فایده است.
        $windowState = function_exists('food_ticket_window_state')
            ? food_ticket_window_state($config, $tehranNow)
            : ['open' => true, 'enabled' => false, 'reason' => '', 'reason_code' => 'disabled',
               'start' => '', 'end' => '', 'next_open_label' => '', 'active_poll_ms' => 500,
               'idle_poll_seconds' => 60, 'persistent_source' => false];
        $windowOpen = (bool) $windowState['open'];

        // ── حالت سختگیرانهٔ خارج از پنجره (خواستهٔ مالک) ─────────────
        // وقتی پنجرهٔ کاری فعال ولی بسته است، Worker هیچ کاری انجام نمی‌دهد:
        // نه خواندن SOURCE_TABLE (حتی اسکن یک‌بارهٔ ری‌استارت)، نه تخلیهٔ صف چاپ،
        // نه ثبت خودکار. تنها یک استثنا: یک گذر پایان‌روز در ساعت ۲۳ به بعد
        // (اول خواندن/چاپ فیش‌های واقعیِ باقی‌ماندهٔ روز، سپس ثبت خودکار
        // تحویل برای وعده‌هایی که سلف تعطیل است). با --force این حالت دور زده می‌شود
        // (مداخلهٔ دستیِ آگاهانهٔ ادمین؛ در لاگ ثبت می‌شود).
        $windowEnabled = (bool) ($windowState['enabled'] ?? false);
        $forceOutsideWindow = in_array('--force', array_slice($argv, 1), true);
        $processorEnabled = (int) ($config['enabled'] ?? 0) === 1;
        $strictClosed = $windowEnabled && !$windowOpen && !$forceOutsideWindow;
        $endOfDayDue = $processorEnabled
            && $tehranHour >= 23
            && $lastEndOfDayRunDay !== $tehranDay
            && (time() - $lastEndOfDayAttemptAt) >= 600;
        if ($strictClosed && !$endOfDayDue) {
            $readSource = false;
            $drainQueue = false;
            $cycle['strict_window_idle'] = true;
        } else {
            // با --queue-only اصلاً سراغ Access نمی‌رویم؛ آن حالت فقط صف چاپ را تخلیه می‌کند.
            $readSource = !$queueOnly && ($windowOpen || $endOfDayDue || $forceOutsideWindow);
            $pendingPrints = ($readSource || $queueOnly) ? 0 : $foodWorkerPendingPrints();
            $drainQueue = $queueOnly || $readSource || $pendingPrints > 0;
        }

        // اتصال پایدار Access فقط داخل پنجره؛ هنگام خروج بسته می‌شود تا قفل
        // .laccdb روی فایل Access آزاد شود و پشتیبان‌گیری شبانه گیر نکند.
        if (function_exists('food_ticket_odbc_pool')) {
            food_ticket_odbc_pool($readSource && !empty($windowState['persistent_source']));
        }

        $cycle['window'] = [
            'enabled' => (bool) ($windowState['enabled'] ?? false),
            'open' => $windowOpen,
            'reason' => (string) ($windowState['reason'] ?? ''),
            'range' => trim((string) ($windowState['start'] ?? '') . '–' . (string) ($windowState['end'] ?? ''), '–'),
            'next_open' => (string) ($windowState['next_open_label'] ?? ''),
        ];

        if (!(int) ($config['enabled'] ?? 0)) {
            $cycle['message'] = 'پردازشگر چاپ فیش غیرفعال است.';
        } elseif (!$readSource && !$drainQueue) {
            // حالت سبک: نه اتصال Access، نه اتصال چاپگر. فقط heartbeat.
            $cycle['idle'] = true;
            $cycle['message'] = (string) ($windowState['reason'] ?? 'خارج از بازهٔ تردد.')
                . (($windowState['next_open_label'] ?? '') !== '' ? ' شروع بعدی: ' . $windowState['next_open_label'] : '');
        } else {
            if ($readSource) {
                try {
                    $batchCycle = food_ticket_process_batch();
                    $cycle = array_merge($cycle, $batchCycle);
                } catch (Throwable $batchEx) {
                    fwrite(STDERR, '[batch] ' . $batchEx->getMessage() . PHP_EOL);
                    $cycle['errors'] = 1;
                    $cycle['message'] = $batchEx->getMessage();
                }
            }
            if ($drainQueue) {
                try {
                    $q = food_ticket_process_print_queue_locked($config);
                    $cycle['queue'] = $q;
                    $cycle['printed'] = (int) ($cycle['printed'] ?? 0) + (int) ($q['printed'] ?? 0);
                    $cycle['queue_printed'] = (int) ($cycle['queue_printed'] ?? 0) + (int) ($q['printed'] ?? 0);
                    $cycle['errors'] = (int) ($cycle['errors'] ?? 0) + (int) ($q['failed'] ?? 0);
                } catch (Throwable $qEx) {
                    fwrite(STDERR, '[queue] ' . $qEx->getMessage() . PHP_EOL);
                    $cycle['errors'] = (int) ($cycle['errors'] ?? 0) + 1;
                }
            }
        }

        // چرخهٔ خطادار ⇒ اتصال نگه‌داشته‌شدهٔ Access دور انداخته شود تا چرخهٔ بعد
        // با هندل سالم شروع کند (مثلاً وقتی فایل‌سرور لحظه‌ای قطع شده است).
        if ((int) ($cycle['errors'] ?? 0) > 0 && function_exists('food_ticket_odbc_release')) {
            food_ticket_odbc_release();
        }

        // ── گذر پایان‌روز: یک‌بار در روز، ساعت ۲۳ به بعد ──────────────
        // ترتیب مهم است: اول SOURCE_TABLE برای امروز خوانده می‌شود (بالا، چون
        // $endOfDayDue خوانش را فعال می‌کند) تا اثر انگشت‌های واقعیِ ساعت‌های
        // پایانی چاپ/ثبت شوند؛ فقط سفارش‌های بدون فیشِ تحویل برایشان ثبت خودکار
        // ساخته می‌شود. اگر پردازش خطا داد، ثبت خودکار امروز/دیروز انجام نمی‌شود
        // تا سفارش واقعی زیرِ سایهٔ ثبت خودکار جا نماند؛ حداقل ۱۰ دقیقه بعد دوباره تلاش می‌کند.
        // در حالت --queue-only یا وقتی پردازشگر غیرفعال است اصلاً اجرا نمی‌شود.
        if ($endOfDayDue && $readSource && $processorEnabled && (int) ($cycle['errors'] ?? 0) === 0) {
            $lastEndOfDayAttemptAt = time();
            try {
                $autoResults = [];
                $autoResults['yesterday'] = food_ticket_auto_receipts_run($tehranNow->modify('-1 day')->format('Y-m-d'));
                $autoResults['today'] = food_ticket_auto_receipts_run($tehranDay, true);
                $cycle['auto_receipts_end_of_day'] = $autoResults;
                $lastEndOfDayRunDay = $tehranDay;
            } catch (Throwable $autoReceiptError) {
                $cycle['errors'] = (int) ($cycle['errors'] ?? 0) + 1;
                $cycle['auto_receipts_error'] = $autoReceiptError->getMessage();
                fwrite(STDERR, '[auto-receipts] ' . $autoReceiptError->getMessage() . PHP_EOL);
                // lastEndOfDayRunDay تنظیم نمی‌شود؛ چرخهٔ بعد دوباره تلاش می‌کند.
            }
        }

        try {
            food_ticket_worker_heartbeat($cycle);
        } catch (Throwable) {
        }

        @file_put_contents($heartbeatFile, date('c') . ' pid=' . getmypid() . PHP_EOL, LOCK_EX);

        // چرخه‌های خالی و تکراری هر ۲ ثانیه لاگ نمی‌شوند؛ فقط هنگام تغییر یا هر ۵ دقیقه یک بار.
        $json = (string) json_encode($cycle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sig = md5($json);
        if ($sig !== $lastPrintedSig || (time() - $lastPrintedAt) >= 300) {
            fwrite(STDOUT, '[' . date('H:i:s') . '] ' . $json . PHP_EOL);
            $lastPrintedSig = $sig;
            $lastPrintedAt = time();
        }
    } catch (Throwable $fatal) {
        if (function_exists('system_log')) {
            try { system_log('error', 'food-ticket-worker', $fatal->getMessage(), ['exception' => get_class($fatal)]); } catch (Throwable) {}
        }
        fwrite(STDERR, '[cycle] ' . $fatal->getMessage() . PHP_EOL);
        try {
            food_ticket_worker_heartbeat([], $fatal);
        } catch (Throwable) {
        }
        if (!$loop) {
            exit(1);
        }
        sleep(5);
        continue;
    }

    if ($loop) {
        if ($maxMinutes > 0 && (time() - $startedAt) >= $maxMinutes * 60) {
            fwrite(STDOUT, '[food-ticket-worker] ' . date('c') . ' max runtime reached; exiting for clean restart.' . PHP_EOL);
            break;
        }
        if (memory_get_usage(true) > 256 * 1024 * 1024) {
            fwrite(STDOUT, '[food-ticket-worker] ' . date('c') . ' memory limit reached; exiting for clean restart.' . PHP_EOL);
            break;
        }
        // سه ریتم: در حال کار / داخل پنجره و بیکار / بیرون از پنجره.
        $busy = ((int) ($cycle['processed'] ?? 0) + (int) ($cycle['queued'] ?? 0) + (int) ($cycle['queue_printed'] ?? 0)) > 0;
        $activeUs = max(200, (int) ($windowState['active_poll_ms'] ?? 500)) * 1000;
        if ($busy) {
            // رگبار تردد: سریع‌تر برگرد تا نفر دوم پشت نفر اول معطل نماند.
            usleep(min(200000, $activeUs));
        } elseif (!$readSource && !$drainQueue) {
            // بیرون از پنجره: خواب طولانی اما تکه‌تکه، تا --max-minutes و خاموشی
            // تمیز بیش از یک ثانیه عقب نیفتند.
            $idleSeconds = max(5, (int) ($windowState['idle_poll_seconds'] ?? 60));
            for ($slept = 0; $slept < $idleSeconds; $slept++) {
                sleep(1);
                if ($maxMinutes > 0 && (time() - $startedAt) >= $maxMinutes * 60) {
                    break;
                }
            }
        } else {
            usleep($activeUs);
        }
    }
} while ($loop);

if (function_exists('food_ticket_odbc_release')) {
    food_ticket_odbc_release();
}

exit(0);
