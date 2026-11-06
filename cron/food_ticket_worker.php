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
$lastAutoReceiptRunDay = '';
$lastAutoReceiptAttemptAt = 0;

// ── پنجرهٔ کاری و کش تنظیمات ─────────────────────────────────────────────
// تنظیمات تا این تعداد ثانیه کش می‌شوند. قبلاً هر چرخه یک بار کامل از دیتابیس
// خوانده می‌شد؛ وقتی چرخه هر نیم ثانیه تکرار شود این هزینه خودش به تأخیر چاپ
// اضافه می‌شود. ۱۰ ثانیه آن‌قدر کوتاه هست که تغییر تنظیمات در پنل سریع اثر کند.
const FOOD_WORKER_CONFIG_TTL = 10;
$config = [];
$configLoadedAt = 0.0;
$previousWindowOpen = null;
$finalSweepPending = false;
// مقادیر اولیه تا بخش خواب حتی در مسیرهای خطا هم متغیر تعریف‌نشده نبیند.
$windowState = ['active_poll_ms' => 500, 'idle_poll_seconds' => 60];
$readSource = true;
$drainQueue = true;

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
    $autoReceiptCycle = null;
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

        // یک بار در شروع هر روز تهران، سفارش‌های روزِ تمام‌شده را فقط در دفتر رویداد ثبت می‌کنیم.
        // در صورت خاموشی Worker، اجرای نخست پس از بازگشت catch-up می‌کند؛ کلید یکتا در helper مانع تکرار است.
        $tehranNow = new DateTimeImmutable('now', new DateTimeZone('Asia/Tehran'));
        $tehranDay = $tehranNow->format('Y-m-d');
        if ($lastAutoReceiptRunDay !== $tehranDay && (time() - $lastAutoReceiptAttemptAt) >= 60) {
            $lastAutoReceiptAttemptAt = time();
            $autoTargetDate = $tehranNow->modify('-1 day')->format('Y-m-d');
            try {
                $autoReceiptCycle = food_ticket_auto_receipts_run($autoTargetDate);
                if (empty($autoReceiptCycle['locked'])) {
                    $lastAutoReceiptRunDay = $tehranDay;
                }
            } catch (Throwable $autoReceiptError) {
                $autoReceiptCycle = ['date' => $autoTargetDate, 'error' => $autoReceiptError->getMessage()];
                $cycle['errors']++;
                fwrite(STDERR, '[auto-receipts] date=' . $autoTargetDate . ' ' . $autoReceiptError->getMessage() . PHP_EOL);
            }
        }

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

        // جاروی پایانی: یک بار خواندن کاملِ اضافه، در دو حالت
        //   ۱) گذار از داخل پنجره به بیرون — ترددی که دقیقاً لحظهٔ بسته شدن نوشته شده جا نماند؛
        //   ۲) نخستین چرخه پس از هر بالا آمدن Worker (previousWindowOpen هنوز null است) —
        //      چون تسک ویندوز هر ۶ ساعت Worker را تازه می‌کند و ممکن است همان لحظه
        //      پنجره بسته شده باشد.
        // ردیف‌های روزهای قبل هرگز دوباره خوانده نمی‌شوند، پس این تنها فرصت آن ردیف است.
        if ($previousWindowOpen !== false && !$windowOpen) {
            $finalSweepPending = true;
        }
        $previousWindowOpen = $windowOpen;

        // با --queue-only اصلاً سراغ Access نمی‌رویم؛ آن حالت فقط صف چاپ را تخلیه می‌کند.
        $readSource = !$queueOnly && ($windowOpen || $finalSweepPending);
        $pendingPrints = ($readSource || $queueOnly) ? 0 : $foodWorkerPendingPrints();
        $drainQueue = $queueOnly || $readSource || $pendingPrints > 0;

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
            if ($finalSweepPending && $readSource) {
                $cycle['final_sweep'] = true;
                $finalSweepPending = false;
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

        if (is_array($autoReceiptCycle)) {
            $cycle['auto_receipts'] = $autoReceiptCycle;
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
