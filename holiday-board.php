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
function holiday_board_render(array $user, string $returnTo = ''): void
{
    // فقط کاربر با holidays.manage می‌تواند تعطیلی ثبت/حذف کند؛ بقیه فهرست را فقط می‌بینند.
    $holidayInert = user_can($user, 'holidays.manage') ? '' : ' inert';
    $returnField = $returnTo === ''
        ? ''
        : '<input type="hidden" name="return_to" value="' . e($returnTo) . '">';
    $holidayQuery = db()->query('SELECT h.*, u.full_name FROM holidays h LEFT JOIN users u ON u.id = h.created_by ORDER BY h.holiday_date');
    echo '<div class="holiday-guard"' . $holidayInert . '><section class="card form-card"><h2>افزودن تعطیلی</h2><form method="post"><input type="hidden" name="action" value="add_holiday">' . csrf_field() . $returnField . '<div class="form-grid"><label class="date-picker-field">تاریخ جلالی<div class="jalali-picker" data-jalali-picker><input id="holiday-date" name="holiday_date" required autocomplete="off" placeholder="۱۴۰۵/۰۱/۰۱"><button type="button" class="calendar-trigger" aria-label="بازکردن تقویم">▦</button><div class="jalali-calendar" hidden></div></div><small class="field-help">پنج‌شنبه و جمعه در تقویم غیرفعال هستند و خودکار تعطیل محسوب می‌شوند.</small></label><label>عنوان تعطیلی<input name="holiday_title" required placeholder="نوروز"></label></div><button class="button" type="submit">افزودن به تقویم</button></form><div class="fixed-holidays"><span class="tag">تعطیلی ثابت</span><b>پنج‌شنبه و جمعه</b><small>در محاسبه SLA به‌صورت خودکار از روزهای کاری حذف می‌شوند.</small></div><hr><h2>تقویم سالانه — تیک روز تعطیل</h2>
<p class="muted">روی هر روز کلیک کنید تا به‌عنوان تعطیل ثبت یا حذف شود. رنگ <strong style="color:#dc2626">قرمز</strong> = تعطیل. پنج‌شنبه و جمعه ثابت‌اند.</p>
<div class="year-holiday-board" data-year-holiday-board>
<div class="board-head"><label>سال جلالی <select data-year-board-year>' .
implode('', array_map(static fn ($y) => '<option value="' . $y . '"' . ($y === (int) (gregorian_to_jalali((int) date('Y'), (int) date('n'), (int) date('j'))[0]) ? ' selected' : '') . '>' . $y . '</option>', range(1400, 1415))) .
'</select></label></div>
<div class="year-grid" data-year-board-grid></div>
<form method="post" data-toggle-holiday class="hidden-toggle" style="position:absolute;left:-9999px">' . csrf_field() . $returnField . '<input type="hidden" name="action" value="toggle_holiday"><input type="hidden" name="holiday_date" value=""><input type="hidden" name="holiday_title" value="تعطیل رسمی"></form>
</div>
<hr><h2>ورود تقویم سالانه</h2><p class="muted">فرمت‌های استاندارد CSV و ICS از منابع تقویم قابل استفاده‌اند. در CSV ستون‌های date و title یا ردیف‌های تاریخ و عنوان را وارد کنید؛ تاریخ جلالی ۱۴۰۵/۰۱/۰۱ و میلادی 2026-03-21 هر دو پذیرفته می‌شوند. پنج‌شنبه و جمعه از ورود فایل نیز حذف می‌شوند.</p><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="import_holidays">' . csrf_field() . $returnField . '<label class="file-input">فایل تقویم<input type="file" name="calendar_file" accept=".csv,.ics,.txt,text/csv,text/calendar" required><small>حداکثر ۲ مگابایت؛ تاریخ‌های تکراری به‌روزرسانی می‌شوند.</small></label><button class="button secondary" type="submit">واردکردن تقویم</button></form></section><section class="card category-list holiday-list">';
    foreach ($holidayQuery->fetchAll() as $holiday) {
        echo '<div><span><strong>' . e(jalali_date($holiday['holiday_date'], false)) . ' • ' . e($holiday['title']) . '</strong><small>ثبت‌کننده: ' . e($holiday['full_name'] ?: 'سامانه') . '</small></span><form method="post" data-confirm="این روز تعطیل حذف شود؟">' . csrf_field() . $returnField . '<input type="hidden" name="action" value="delete_holiday"><input type="hidden" name="holiday_id" value="' . (int) $holiday['id'] . '"><button class="mini-button danger" type="submit">حذف</button></form></div>';
    }
    echo '</div></section>';
}
