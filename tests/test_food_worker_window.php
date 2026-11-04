<?php

declare(strict_types=1);

/**
 * tests/test_food_worker_window.php
 *
 * آزمون مستقل منطق «پنجرهٔ کاری Worker فیش غذا» (مهاجرت ۰۳۱).
 * بدون دیتابیس و بدون Access اجرا می‌شود؛ فقط تابع‌های خالص food-ticket-window.php.
 *
 * اجرا:  php tests/test_food_worker_window.php
 */

const APP_ROOT = __DIR__ . '/..';

function cfg($key, $default = null)
{
    return $key === 'app.timezone' ? 'Asia/Tehran' : $default;
}

/** تقویم تعطیلات ساختگی: فقط برای آزمون. */
$GLOBALS['TEST_HOLIDAYS'] = ['2026-11-10'];

function food_ticket_auto_receipt_holiday(string $date): array
{
    $weekend = in_array((int) (new DateTimeImmutable($date))->format('N'), [4, 5], true);
    return [
        'holiday' => $weekend || in_array($date, $GLOBALS['TEST_HOLIDAYS'], true),
        'title' => $weekend ? 'آخر هفته' : 'تعطیل رسمی',
    ];
}

require APP_ROOT . '/food-ticket-window.php';

$failed = 0;
function expect(string $name, bool $ok): void
{
    global $failed;
    echo ($ok ? 'PASS' : 'FAIL') . "  $name\n";
    if (!$ok) {
        $failed++;
    }
}

/** وضعیت پنجره در یک لحظهٔ مشخص تهران. */
function state_at(string $when, array $overrides = []): array
{
    $config = food_ticket_window_defaults();
    $config['window_enabled'] = 1;
    $config = array_merge($config, $overrides);
    return food_ticket_window_state($config, new DateTimeImmutable($when, new DateTimeZone('Asia/Tehran')));
}

// ── ۱) پارس روزهای هفته ───────────────────────────────────────────────────
expect('days_default_saturday_to_wednesday', food_ticket_window_days('6,7,1,2,3') === [1, 2, 3, 6, 7]);
expect('days_invalid_falls_back', food_ticket_window_days('abc') === [1, 2, 3, 6, 7]);
expect('days_empty_falls_back', food_ticket_window_days('') === [1, 2, 3, 6, 7]);
expect('days_drops_out_of_range', food_ticket_window_days('1;3;9;0;7') === [1, 3, 7]);
expect('days_deduplicates', food_ticket_window_days('2,2,2,5') === [2, 5]);

// ── ۲) پارس ساعت ──────────────────────────────────────────────────────────
expect('time_hhmmss', food_ticket_window_time('11:00:00', 0) === 660);
expect('time_hhmm', food_ticket_window_time('14:30', 0) === 870);
expect('time_invalid_uses_fallback', food_ticket_window_time('بدون‌ساعت', 777) === 777);
expect('time_out_of_range_uses_fallback', food_ticket_window_time('25:99', 42) === 42);

// ── ۳) مرزهای پنجره: ۱۱:۰۰–۱۴:۰۰ با حاشیهٔ ۳۰ و ۱۵ ⇒ مؤثر ۱۰:۳۰–۱۴:۱۵ ─────
$saturdayNoon = state_at('2026-11-07 12:00');
expect('effective_start_is_10_30', $saturdayNoon['start'] === '10:30');
expect('effective_end_is_14_15', $saturdayNoon['end'] === '14:15');
expect('open_inside_window', $saturdayNoon['open'] === true);
expect('reason_code_open', $saturdayNoon['reason_code'] === 'open');

expect('closed_one_minute_before_start', state_at('2026-11-07 10:29')['open'] === false);
expect('open_exactly_at_start', state_at('2026-11-07 10:30')['open'] === true);
expect('open_at_last_punch_minute', state_at('2026-11-07 13:59')['open'] === true);
expect('open_inside_after_margin', state_at('2026-11-07 14:10')['open'] === true);
expect('closed_exactly_at_effective_end', state_at('2026-11-07 14:15')['open'] === false);
expect('closed_code_after', state_at('2026-11-07 20:00')['reason_code'] === 'after');
expect('closed_code_before', state_at('2026-11-07 09:00')['reason_code'] === 'before');

// ── ۴) حاشیهٔ صفر هم باید کار کند (کاربر می‌تواند «تا ۱۴» بگذارد) ──────────
$noMargin = state_at('2026-11-07 14:00', ['window_margin_before_minutes' => 0, 'window_margin_after_minutes' => 0]);
expect('zero_margin_start_is_11_00', $noMargin['start'] === '11:00');
expect('zero_margin_end_is_14_00', $noMargin['end'] === '14:00');
expect('zero_margin_closed_at_14_00', $noMargin['open'] === false);
expect(
    'zero_margin_open_at_13_59',
    state_at('2026-11-07 13:59', ['window_margin_before_minutes' => 0, 'window_margin_after_minutes' => 0])['open'] === true
);

// ── ۵) روزهای غیرکاری و تعطیلات ───────────────────────────────────────────
expect('wednesday_is_working_day', state_at('2026-11-11 12:00')['open'] === true);
expect('thursday_is_holiday', state_at('2026-11-12 12:00')['reason_code'] === 'holiday');
expect('friday_is_holiday', state_at('2026-11-13 12:00')['reason_code'] === 'holiday');
expect('registered_holiday_closes_window', state_at('2026-11-10 12:00')['reason_code'] === 'holiday');
expect('holiday_is_closed', state_at('2026-11-10 12:00')['open'] === false);

// ── ۶) خاموش بودن پنجره = رفتار نسخهٔ قبلی (۲۴/۷) ─────────────────────────
$disabled = food_ticket_window_state(food_ticket_window_defaults(), new DateTimeImmutable('2026-11-13 03:00', new DateTimeZone('Asia/Tehran')));
expect('disabled_window_defaults_to_always_open', $disabled['open'] === true);
expect('disabled_window_reason_code', $disabled['reason_code'] === 'disabled');
expect('window_disabled_by_default_in_migration', (int) food_ticket_window_defaults()['window_enabled'] === 0);

// ── ۷) شروع بعدی ──────────────────────────────────────────────────────────
expect('next_open_today_when_before', state_at('2026-11-07 09:00')['next_open_at'] === '2026-11-07 10:30');
expect('next_open_tomorrow_when_after', state_at('2026-11-07 20:00')['next_open_at'] === '2026-11-08 10:30');
expect('next_open_skips_weekend', state_at('2026-11-12 12:00')['next_open_at'] === '2026-11-14 10:30');
expect('next_open_skips_registered_holiday', state_at('2026-11-09 20:00')['next_open_at'] === '2026-11-11 10:30');
expect('next_open_label_not_empty', state_at('2026-11-07 20:00')['next_open_label'] !== '');
expect('next_open_null_when_open', $saturdayNoon['next_open_at'] === null);

// ── ۸) ریتم پایش ──────────────────────────────────────────────────────────
expect('active_poll_default_500ms', $saturdayNoon['active_poll_ms'] === 500);
expect('idle_poll_default_60s', $saturdayNoon['idle_poll_seconds'] === 60);
expect('persistent_source_on_by_default', $saturdayNoon['persistent_source'] === true);
expect('active_poll_floor_is_200ms', state_at('2026-11-07 12:00', ['active_poll_ms' => 10])['active_poll_ms'] === 200);
expect('active_poll_ceiling_is_60s', state_at('2026-11-07 12:00', ['active_poll_ms' => 999999])['active_poll_ms'] === 60000);
expect(
    'legacy_poll_seconds_used_when_active_poll_missing',
    food_ticket_window_state(['active_poll_ms' => 0, 'poll_seconds' => 3])['active_poll_ms'] === 3000
);
expect('idle_poll_floor_is_5s', state_at('2026-11-07 12:00', ['idle_poll_seconds' => 1])['idle_poll_seconds'] === 5);

// ── ۹) پایداری: تنظیمات ناقص نباید fatal بدهد ─────────────────────────────
$empty = food_ticket_window_state([]);
expect('empty_config_is_safe', isset($empty['open'], $empty['reason'], $empty['active_poll_ms']));
expect('empty_config_is_open', $empty['open'] === true);
$garbage = food_ticket_window_state([
    'window_enabled' => 1, 'window_days' => null, 'window_start' => null,
    'window_end' => 'xx', 'window_margin_before_minutes' => -999, 'window_margin_after_minutes' => 99999,
]);
expect('garbage_config_is_safe', is_string($garbage['start']) && is_string($garbage['end']));
expect('garbage_margin_before_clamped', $garbage['start'] === '11:00');
expect('garbage_margin_after_clamped_to_midnight', $garbage['end'] === '24:00' || $garbage['end'] === '00:00');

echo $failed === 0
    ? "\nهمهٔ آزمون‌های پنجرهٔ کاری موفق بودند.\n"
    : "\n$failed آزمون ناموفق.\n";
exit($failed === 0 ? 0 : 1);
