<?php

declare(strict_types=1);

/**
 * food-ticket-window.php — پنجرهٔ کاری Worker فیش غذا (نسخهٔ ۱.۶۲.۷۸).
 *
 * چرا این فایل وجود دارد؟
 *   دستگاه تردد فقط در بازهٔ مشخصی از روز تردد قبول و ثبت می‌کند. بیرون از آن بازه
 *   هیچ ردیف تازه‌ای در فایل Access ساخته نمی‌شود، پس باز کردن اتصال Access و اجرای
 *   چرخهٔ کامل، کارِ اثبات‌شده بی‌فایده است. این فایل تصمیم «الان داخل پنجره هستیم یا نه»
 *   را در یک جای واحد و قابل آزمون متمرکز می‌کند تا هم Worker و هم پنل از همان منطق
 *   استفاده کنند.
 *
 * نکتهٔ مهم: این ماژول هرگز Worker را «خاموش» نمی‌کند؛ فقط می‌گوید چرخه باید کامل باشد
 * یا سبک. خاموش کردن پروسه باعث حلقهٔ ری‌استارت تسک ویندوز و کهنه شدن heartbeat می‌شود.
 */

if (!function_exists('food_ticket_window_timezone')) {
    function food_ticket_window_timezone(): DateTimeZone
    {
        $name = 'Asia/Tehran';
        if (function_exists('cfg')) {
            $configured = trim((string) cfg('app.timezone', 'Asia/Tehran'));
            if ($configured !== '') {
                $name = $configured;
            }
        }
        try {
            return new DateTimeZone($name);
        } catch (Throwable) {
            return new DateTimeZone('Asia/Tehran');
        }
    }
}

if (!function_exists('food_ticket_window_defaults')) {
    /** پیش‌فرض‌ها دقیقاً برابر ستون‌های مهاجرت ۰۳۱ هستند. */
    function food_ticket_window_defaults(): array
    {
        return [
            'window_enabled' => 0,
            'window_days' => '6,7,1,2,3',
            'window_start' => '11:00:00',
            'window_end' => '14:00:00',
            'window_margin_before_minutes' => 30,
            'window_margin_after_minutes' => 15,
            'idle_poll_seconds' => 60,
            'active_poll_ms' => 500,
            'persistent_source_connection' => 1,
        ];
    }
}

if (!function_exists('food_ticket_window_days')) {
    /**
     * فهرست روزهای فعال به‌صورت ISO-8601 (دوشنبه=1 … یکشنبه=7).
     * ورودی نامعتبر یا خالی ⇒ پیش‌فرض شنبه تا چهارشنبه.
     *
     * @return array<int,int>
     */
    function food_ticket_window_days(mixed $raw): array
    {
        $days = [];
        foreach (preg_split('/[^0-9]+/', (string) $raw) ?: [] as $part) {
            if ($part === '') {
                continue;
            }
            $day = (int) $part;
            if ($day >= 1 && $day <= 7 && !in_array($day, $days, true)) {
                $days[] = $day;
            }
        }
        if ($days === []) {
            $days = [6, 7, 1, 2, 3];
        }
        sort($days);
        return $days;
    }
}

if (!function_exists('food_ticket_window_time')) {
    /** «۱۱:۰۰» یا «11:00:00» ⇒ دقیقه از نیمه‌شب. ورودی نامعتبر ⇒ مقدار جایگزین. */
    function food_ticket_window_time(mixed $raw, int $fallbackMinutes): int
    {
        if (preg_match('/^\s*(\d{1,2})\s*:\s*(\d{1,2})/', (string) $raw, $m)) {
            $hour = (int) $m[1];
            $minute = (int) $m[2];
            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return $hour * 60 + $minute;
            }
        }
        return $fallbackMinutes;
    }
}

if (!function_exists('food_ticket_window_is_holiday')) {
    /**
     * تعطیلی رسمی یا پنج‌شنبه/جمعه. از همان تابع و همان جدولی استفاده می‌کند که
     * صفحهٔ «تعطیلات رسمی و توقف SLA» می‌نویسد، تا تعطیلات در دو جا نگهداری نشوند.
     * نتیجه برای هر تاریخ یک بار خوانده و کش می‌شود (Worker روزها زنده می‌ماند).
     */
    function food_ticket_window_is_holiday(string $date): bool
    {
        static $cache = [];
        if (array_key_exists($date, $cache)) {
            return $cache[$date];
        }
        $weekend = false;
        try {
            $weekend = in_array((int) (new DateTimeImmutable($date, new DateTimeZone('UTC')))->format('N'), [4, 5], true);
        } catch (Throwable) {
            $weekend = false;
        }
        $holiday = $weekend;
        if (!$holiday && function_exists('food_ticket_auto_receipt_holiday')) {
            try {
                $holiday = !empty(food_ticket_auto_receipt_holiday($date)['holiday']);
            } catch (Throwable) {
                // خطای خواندن تقویم هرگز نباید یک روز کاری را تعطیل جا بزند.
                $holiday = $weekend;
            }
        }
        if (count($cache) > 400) {
            $cache = [];
        }
        $cache[$date] = $holiday;
        return $holiday;
    }
}

if (!function_exists('food_ticket_window_day_is_active')) {
    /** آیا این تاریخ هم جزو روزهای انتخابی است و هم تعطیل رسمی نیست؟ */
    function food_ticket_window_day_is_active(DateTimeImmutable $day, array $activeDays): bool
    {
        if (!in_array((int) $day->format('N'), $activeDays, true)) {
            return false;
        }
        return !food_ticket_window_is_holiday($day->format('Y-m-d'));
    }
}

if (!function_exists('food_ticket_window_state')) {
    /**
     * وضعیت پنجرهٔ کاری در یک لحظهٔ مشخص.
     *
     * @return array{
     *   enabled:bool, open:bool, reason:string, reason_code:string,
     *   start:string, end:string, now:string,
     *   next_open_at:?string, next_open_label:string,
     *   active_poll_ms:int, idle_poll_seconds:int, persistent_source:bool
     * }
     */
    function food_ticket_window_state(array $config, ?DateTimeImmutable $now = null): array
    {
        $defaults = food_ticket_window_defaults();
        $now ??= new DateTimeImmutable('now', food_ticket_window_timezone());
        $now = $now->setTimezone(food_ticket_window_timezone());

        $activePollMs = (int) ($config['active_poll_ms'] ?? $defaults['active_poll_ms']);
        if ($activePollMs <= 0) {
            // نصب‌های قدیمی که فقط poll_seconds دارند.
            $activePollMs = max(1, (int) ($config['poll_seconds'] ?? 2)) * 1000;
        }
        $activePollMs = max(200, min(60000, $activePollMs));
        $idlePoll = max(5, min(600, (int) ($config['idle_poll_seconds'] ?? $defaults['idle_poll_seconds'])));
        $persistent = (int) ($config['persistent_source_connection'] ?? $defaults['persistent_source_connection']) === 1;

        $marginBefore = max(0, min(720, (int) ($config['window_margin_before_minutes'] ?? $defaults['window_margin_before_minutes'])));
        $marginAfter = max(0, min(720, (int) ($config['window_margin_after_minutes'] ?? $defaults['window_margin_after_minutes'])));
        $startMinutes = food_ticket_window_time($config['window_start'] ?? $defaults['window_start'], 11 * 60) - $marginBefore;
        $endMinutes = food_ticket_window_time($config['window_end'] ?? $defaults['window_end'], 14 * 60) + $marginAfter;
        $startMinutes = max(0, $startMinutes);
        $endMinutes = min(24 * 60, $endMinutes);

        $format = static fn (int $minutes): string => sprintf('%02d:%02d', intdiv($minutes, 60) % 24, $minutes % 60);
        $base = [
            'enabled' => (int) ($config['window_enabled'] ?? $defaults['window_enabled']) === 1,
            'start' => $format($startMinutes),
            'end' => $format($endMinutes),
            'now' => $now->format('Y-m-d H:i'),
            'next_open_at' => null,
            'next_open_label' => '',
            'active_poll_ms' => $activePollMs,
            'idle_poll_seconds' => $idlePoll,
            'persistent_source' => $persistent,
        ];

        if (!$base['enabled']) {
            return $base + [
                'open' => true,
                'reason' => 'پنجرهٔ کاری غیرفعال است؛ Worker تمام شبانه‌روز پایش می‌کند.',
                'reason_code' => 'disabled',
            ];
        }

        $activeDays = food_ticket_window_days($config['window_days'] ?? $defaults['window_days']);
        $nowMinutes = ((int) $now->format('G')) * 60 + (int) $now->format('i');
        $dayActive = food_ticket_window_day_is_active($now, $activeDays);

        if ($dayActive && $nowMinutes >= $startMinutes && $nowMinutes < $endMinutes) {
            return $base + [
                'open' => true,
                'reason' => 'داخل بازهٔ تردد (' . $base['start'] . ' تا ' . $base['end'] . ').',
                'reason_code' => 'open',
            ];
        }

        [$nextAt, $nextLabel] = food_ticket_window_next_open($now, $activeDays, $startMinutes, $endMinutes);
        $base['next_open_at'] = $nextAt;
        $base['next_open_label'] = $nextLabel;

        if (!$dayActive) {
            $reason = food_ticket_window_is_holiday($now->format('Y-m-d'))
                ? 'امروز تعطیل است؛ ترددی ثبت نمی‌شود.'
                : 'امروز جزو روزهای کاری تعریف‌شده نیست.';
            $code = 'holiday';
        } elseif ($nowMinutes < $startMinutes) {
            $reason = 'هنوز به بازهٔ تردد نرسیده‌ایم (شروع ' . $base['start'] . ').';
            $code = 'before';
        } else {
            $reason = 'بازهٔ تردد امروز پایان یافته است (پایان ' . $base['end'] . ').';
            $code = 'after';
        }

        return $base + ['open' => false, 'reason' => $reason, 'reason_code' => $code];
    }
}

if (!function_exists('food_ticket_window_save')) {
    /**
     * ذخیرهٔ تنظیمات پنجرهٔ کاری در جدول food_ticket_config.
     *
     * جدا از food_ticket_save_config() اجرا می‌شود و فقط ستون‌هایی را می‌نویسد که
     * واقعاً در جدول هستند؛ نصبی که مهاجرت ۰۳۱ را اجرا نکرده باشد بی‌سروصدا رد می‌شود
     * و ذخیرهٔ بقیهٔ تنظیمات چاپگر به خاطر آن شکست نمی‌خورد.
     *
     * @param array<string,mixed> $data ورودی خام فرم/JSON (هم snake_case و هم camelCase)
     * @return array<string,mixed> مقادیر نوشته‌شده (برای لاگ)
     */
    function food_ticket_window_save(array $data): array
    {
        if (!function_exists('db') || !function_exists('food_ticket_config_has_column')) {
            return [];
        }
        $pick = static function (array $src, string ...$keys): mixed {
            foreach ($keys as $key) {
                if (array_key_exists($key, $src)) {
                    return $src[$key];
                }
            }
            return null;
        };
        $bool = static fn (mixed $v): int => (!empty($v) && $v !== 'false' && $v !== '0') ? 1 : 0;
        $clamp = static fn (mixed $v, int $lo, int $hi, int $def): int => max($lo, min($hi, (int) ($v ?? $def)));
        $time = static function (mixed $v, string $def): string {
            return preg_match('/^\s*(\d{1,2}):(\d{2})/', (string) $v, $m) && (int) $m[1] <= 23 && (int) $m[2] <= 59
                ? sprintf('%02d:%02d:00', (int) $m[1], (int) $m[2])
                : $def;
        };

        $candidates = [];
        if (($raw = $pick($data, 'window_enabled', 'windowEnabled')) !== null) {
            $candidates['window_enabled'] = $bool($raw);
        }
        if (($raw = $pick($data, 'window_days', 'windowDays')) !== null) {
            $candidates['window_days'] = implode(',', food_ticket_window_days(is_array($raw) ? implode(',', $raw) : $raw));
        }
        if (($raw = $pick($data, 'window_start', 'windowStart')) !== null) {
            $candidates['window_start'] = $time($raw, '11:00:00');
        }
        if (($raw = $pick($data, 'window_end', 'windowEnd')) !== null) {
            $candidates['window_end'] = $time($raw, '14:00:00');
        }
        if (($raw = $pick($data, 'window_margin_before_minutes', 'windowMarginBefore')) !== null) {
            $candidates['window_margin_before_minutes'] = $clamp($raw, 0, 720, 30);
        }
        if (($raw = $pick($data, 'window_margin_after_minutes', 'windowMarginAfter')) !== null) {
            $candidates['window_margin_after_minutes'] = $clamp($raw, 0, 720, 15);
        }
        if (($raw = $pick($data, 'idle_poll_seconds', 'idlePollSeconds')) !== null) {
            $candidates['idle_poll_seconds'] = $clamp($raw, 5, 600, 60);
        }
        if (($raw = $pick($data, 'active_poll_ms', 'activePollMs')) !== null) {
            $candidates['active_poll_ms'] = $clamp($raw, 200, 60000, 500);
        }
        if (($raw = $pick($data, 'persistent_source_connection', 'persistentSourceConnection')) !== null) {
            $candidates['persistent_source_connection'] = $bool($raw);
        }

        $sets = [];
        $params = [];
        foreach ($candidates as $column => $value) {
            if (!food_ticket_config_has_column($column)) {
                continue;
            }
            $sets[] = '`' . $column . '` = ?';
            $params[] = $value;
        }
        if ($sets === []) {
            return [];
        }
        db()->prepare('UPDATE food_ticket_config SET ' . implode(', ', $sets) . ' WHERE id = 1')->execute($params);
        return $candidates;
    }
}

if (!function_exists('food_ticket_window_next_open')) {
    /**
     * نزدیک‌ترین زمان بازشدن پنجره از «اکنون» به بعد. تا ۱۴ روز جلو می‌رود؛
     * اگر هیچ روز فعالی پیدا نشود (مثلاً همهٔ روزها تعطیل شده‌اند) خالی برمی‌گردد.
     *
     * @param array<int,int> $activeDays
     * @return array{0:?string,1:string}
     */
    function food_ticket_window_next_open(DateTimeImmutable $now, array $activeDays, int $startMinutes, int $endMinutes): array
    {
        $names = [1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنج‌شنبه', 5 => 'جمعه', 6 => 'شنبه', 7 => 'یکشنبه'];
        $nowMinutes = ((int) $now->format('G')) * 60 + (int) $now->format('i');
        for ($offset = 0; $offset <= 14; $offset++) {
            $day = $now->modify('+' . $offset . ' day');
            if (!food_ticket_window_day_is_active($day, $activeDays)) {
                continue;
            }
            if ($offset === 0 && $nowMinutes >= $startMinutes) {
                continue;
            }
            $at = $day->setTime(intdiv($startMinutes, 60), $startMinutes % 60, 0);
            $label = ($offset === 0 ? 'امروز' : ($offset === 1 ? 'فردا' : $names[(int) $day->format('N')] ?? ''))
                . ' ساعت ' . sprintf('%02d:%02d', intdiv($startMinutes, 60), $startMinutes % 60);
            return [$at->format('Y-m-d H:i'), trim($label)];
        }
        return [null, 'روز کاری فعالی در دو هفتهٔ آینده تعریف نشده است.'];
    }
}
