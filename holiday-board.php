<?php

declare(strict_types=1);

/**
 * holiday-board.php — رندر مشترک «تقویم تعطیلات».
 *
 * همان تابلو هم در صفحهٔ مستقل index.php?page=holidays و هم به‌صورت یک تب زیر
 * فرم تنظیمات نمایش داده می‌شود. markup فقط یک بار اینجا نگهداری می‌شود تا این دو
 * جا هیچ‌وقت از هم جدا نیفتند.
 */

if (!defined('APP_ROOT')) {
    http_response_code(403);
    exit('دسترسی مستقیم مجاز نیست.');
}

/**
 * تابلوی تعطیلات را چاپ می‌کند (بدون page-heading و بدون render_header/footer).
 *
 * @param array<string,mixed> $user کاربر جاری؛ برای تشخیص مجوز holidays.manage
 * @param string $returnTo اگر خالی نباشد، در فرم‌ها به‌عنوان return_to فرستاده می‌شود
 *                         تا پس از ثبت/حذف به همان صفحه برگردیم (مثلاً 'settings').
 */
/**
 * فرم تقویم کاری: ساعت شروع/پایان و روزهای تعطیل هفتگی.
 *
 * پیش‌تر اینجا یک بلوک ثابت بود که «پنج‌شنبه و جمعه» را تعطیلی تغییرناپذیر اعلام
 * می‌کرد، چون همین مقادیر در متن سه تابع SLA هاردکد بودند. اکنون مقادیر واقعی از
 * sla_business_hours() خوانده می‌شوند تا فرم همیشه وضعیت جاری را نشان دهد.
 */
function holiday_board_work_hours_form(string $returnField): string
{
    $hours = sla_business_hours();
    // شمارهٔ روزها بر پایهٔ ISO-8601 است (دوشنبه ۱ تا یکشنبه ۷)، ولی ترتیب نمایش
    // از شنبه شروع می‌شود چون هفتهٔ کاری ایران از شنبه است.
    $days = [6 => 'شنبه', 7 => 'یک‌شنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنج‌شنبه', 5 => 'جمعه'];
    $checkboxes = '';
    foreach ($days as $number => $label) {
        $checked = in_array($number, $hours['weekend'], true) ? ' checked' : '';
        $checkboxes .= '<label class="weekend-day"><input type="checkbox" name="weekend_days[]" value="' . $number . '"' . $checked . '> ' . e($label) . '</label>';
    }
    return '<section class="card form-card work-hours-card"><h2>تقویم کاری و ساعت محاسبهٔ SLA</h2>'
        . '<p class="muted">مهلت‌های SLA فقط در این ساعت‌ها و در روزهای غیرتعطیل جلو می‌روند. تغییر این مقادیر روی مهلت درخواست‌های جدید اثر می‌گذارد؛ مهلت‌های محاسبه‌شدهٔ قبلی دست‌نخورده می‌مانند.</p>'
        . '<form method="post"><input type="hidden" name="action" value="save_work_hours">' . csrf_field() . $returnField
        . '<div class="form-grid"><label>ساعت شروع کار<input type="number" name="work_start_hour" min="0" max="23" required value="' . (int) $hours['start'] . '"></label>'
        . '<label>ساعت پایان کار<input type="number" name="work_end_hour" min="1" max="24" required value="' . (int) $hours['end'] . '"><small class="field-help">باید بزرگ‌تر از ساعت شروع باشد.</small></label></div>'
        . '<fieldset class="weekend-picker"><legend>روزهای تعطیل هفتگی</legend>' . $checkboxes
        . '<small class="field-help">دست‌کم یک روز هفته باید کاری بماند.</small></fieldset>'
        . '<button class="button" type="submit">ذخیرهٔ تقویم کاری</button></form></section>';
}

function holiday_board_render(array $user, string $returnTo = ''): void
{
    // فقط کاربر با holidays.manage می‌تواند تعطیلی ثبت/حذف کند؛ بقیه فهرست را فقط می‌بینند.
    $holidayInert = user_can($user, 'holidays.manage') ? '' : ' inert';
    $returnField = $returnTo === ''
        ? ''
        : '<input type="hidden" name="return_to" value="' . e($returnTo) . '">';
    $holidayQuery = db()->query('SELECT h.*, u.full_name FROM holidays h LEFT JOIN users u ON u.id = h.created_by ORDER BY h.holiday_date');
    echo '<div class="holiday-guard"' . $holidayInert . '><section class="card form-card"><h2>افزودن تعطیلی</h2><form method="post"><input type="hidden" name="action" value="add_holiday">' . csrf_field() . $returnField . '<div class="form-grid"><label class="date-picker-field">تاریخ جلالی<div class="jalali-picker" data-jalali-picker><input id="holiday-date" name="holiday_date" required autocomplete="off" placeholder="۱۴۰۵/۰۱/۰۱"><button type="button" class="calendar-trigger" aria-label="بازکردن تقویم">▦</button><div class="jalali-calendar" hidden></div></div><small class="field-help">روزهای تعطیل هفتگی (طبق تقویم کاری بالا) در انتخابگر غیرفعال‌اند و خودکار تعطیل حساب می‌شوند.</small></label><label>عنوان تعطیلی<input name="holiday_title" required placeholder="نوروز"></label></div><button class="button" type="submit">افزودن به تقویم</button></form>' . holiday_board_work_hours_form($returnField) . '<hr><h2>تقویم سالانه — تیک روز تعطیل</h2>
<p class="muted">روی هر روز کلیک کنید تا به‌عنوان تعطیل ثبت یا حذف شود. رنگ <strong style="color:#dc2626">قرمز</strong> = تعطیل. روزهای تعطیل هفتگی طبق تقویم کاری بالا مشخص می‌شوند.</p>
<div class="year-holiday-board" data-year-holiday-board>
<div class="board-head"><label>سال جلالی <select data-year-board-year>' .
implode('', array_map(static fn ($y) => '<option value="' . $y . '"' . ($y === (int) (gregorian_to_jalali((int) date('Y'), (int) date('n'), (int) date('j'))[0]) ? ' selected' : '') . '>' . $y . '</option>', range(1400, 1415))) .
'</select></label></div>
<div class="year-grid" data-year-board-grid></div>
<form method="post" data-toggle-holiday class="hidden-toggle" style="position:absolute;left:-9999px">' . csrf_field() . $returnField . '<input type="hidden" name="action" value="toggle_holiday"><input type="hidden" name="holiday_date" value=""><input type="hidden" name="holiday_title" value="تعطیل رسمی"></form>
</div>
<hr><h2>ورود تقویم سالانه</h2><p class="muted">فرمت‌های استاندارد CSV و ICS از منابع تقویم قابل استفاده‌اند. در CSV ستون‌های date و title یا ردیف‌های تاریخ و عنوان را وارد کنید؛ تاریخ جلالی ۱۴۰۵/۰۱/۰۱ و میلادی 2026-03-21 هر دو پذیرفته می‌شوند. روزهای تعطیل هفتگی از ورود فایل نیز حذف می‌شوند.</p><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="import_holidays">' . csrf_field() . $returnField . '<label class="file-input">فایل تقویم<input type="file" name="calendar_file" accept=".csv,.ics,.txt,text/csv,text/calendar" required><small>حداکثر ۲ مگابایت؛ تاریخ‌های تکراری به‌روزرسانی می‌شوند.</small></label><button class="button secondary" type="submit">واردکردن تقویم</button></form></section><section class="card category-list holiday-list">';
    foreach ($holidayQuery->fetchAll() as $holiday) {
        echo '<div><span><strong>' . e(jalali_date($holiday['holiday_date'], false)) . ' • ' . e($holiday['title']) . '</strong><small>ثبت‌کننده: ' . e($holiday['full_name'] ?: 'سامانه') . '</small></span><form method="post" data-confirm="این روز تعطیل حذف شود؟">' . csrf_field() . $returnField . '<input type="hidden" name="action" value="delete_holiday"><input type="hidden" name="holiday_id" value="' . (int) $holiday['id'] . '"><button class="mini-button danger" type="submit">حذف</button></form></div>';
    }
    echo '</div></section>';
}
