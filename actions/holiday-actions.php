<?php

declare(strict_types=1);

/**
 * actions/holiday-actions.php — ۵ اکشن POST تقویم و تعطیلات (فاز ۲ بازسازی، برش ۶ — نسخهٔ ۱.۶۲.۱۷).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   toggle_holiday    تغییر وضعیت تعطیلی یک روز از روی تقویم
 *   add_holiday       افزودن تعطیلی جدید با تاریخ شمسی
 *   import_holidays   ورود گروهی تعطیلات (هر سطر یک تاریخ)
 *   delete_holiday    حذف یک تعطیلی ثبت‌شده
 *   save_work_hours   ذخیرهٔ ساعت کاری و روزهای تعطیل هفتگی مبنای SLA
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها جز مقصد بازگشت (return_to) بدون تغییر منتقل شده؛ تنها مورد
 * continue داخل بلوک، continue داخل حلقهٔ import است که برای include کاملاً بی‌خطر است.
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

// مقصد بازگشت پس از هر اکشن: صفحهٔ مستقل تقویم، یا تب «تقویم و تعطیلات» زیر تنظیمات
// وقتی فرم از همان تب ارسال شده باشد. هر مقدار دیگری نادیده گرفته می‌شود.
$holidayReturnTo = static function (): string {
    return (string) ($_POST['return_to'] ?? '') === 'settings'
        ? 'index.php?page=settings#holidays'
        : 'index.php?page=holidays';
};

        // ───── گروه اکشن: تقویم و تعطیلات ─────
        if ($action === 'toggle_holiday') {
            $holidayAdmin = require_permission('holidays.manage');
            $holidayDate = jalali_input_to_gregorian(post_value('holiday_date'));
            if ($holidayDate === null) {
                throw new RuntimeException('تاریخ تعطیل معتبر نیست.');
            }
            $day = substr($holidayDate, 0, 10);
            $dow = (int) (new DateTimeImmutable($day, new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))))->format('N');
            if (in_array($dow, [4, 5], true)) {
                flash('warning', 'پنج‌شنبه و جمعه به‌صورت ثابت تعطیل هستند.');
                redirect($holidayReturnTo());
            }
            db()->beginTransaction();
            try {
                $exists = db()->prepare('SELECT id, is_active, title FROM holidays WHERE holiday_date = ? LIMIT 1 FOR UPDATE');
                $exists->execute([$day]);
                $row = $exists->fetch();
                if ($row && (int) $row['is_active'] === 1) {
                    db()->prepare('UPDATE holidays SET is_active = 0 WHERE id = ?')->execute([(int) $row['id']]);
                    save_audit_required((int) $holidayAdmin['id'], 'holiday_deactivated', null, ['holiday_id' => (int) $row['id'], 'holiday_date' => $day, 'title' => (string) $row['title'], 'is_active' => 0]);
                    flash('success', 'تعطیلی غیرفعال شد.');
                } elseif ($row) {
                    $title = post_value('holiday_title') ?: 'تعطیل رسمی';
                    db()->prepare('UPDATE holidays SET is_active = 1, title = ?, created_by = ? WHERE id = ?')->execute([$title, $holidayAdmin['id'], (int) $row['id']]);
                    save_audit_required((int) $holidayAdmin['id'], 'holiday_activated', null, ['holiday_id' => (int) $row['id'], 'holiday_date' => $day, 'title' => $title, 'is_active' => 1]);
                    flash('success', 'تعطیلی فعال شد.');
                } else {
                    $title = post_value('holiday_title') ?: 'تعطیل رسمی';
                    db()->prepare('INSERT INTO holidays (holiday_date, title, created_by, is_active) VALUES (?, ?, ?, 1)')->execute([$day, $title, $holidayAdmin['id']]);
                    $holidayId = (int) db()->lastInsertId();
                    save_audit_required((int) $holidayAdmin['id'], 'holiday_created', null, ['holiday_id' => $holidayId, 'holiday_date' => $day, 'title' => $title, 'is_active' => 1]);
                    flash('success', 'تعطیلی ثبت شد.');
                }
                db_commit('index.php');
            } catch (Throwable $exception) {
                if (db()->inTransaction()) db()->rollBack();
                throw $exception;
            }
            redirect($holidayReturnTo());
        }

        if ($action === 'add_holiday') {
            $holidayAdmin = require_permission('holidays.manage');
            $holidayDate = jalali_input_to_gregorian(post_value('holiday_date'));
            if ($holidayDate === null || post_value('holiday_title') === '') {
                throw new RuntimeException('تاریخ جلالی و عنوان تعطیلی را وارد کنید.');
            }
            $holidayDayOfWeek = (int) (new DateTimeImmutable(substr($holidayDate, 0, 10), new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))))->format('N');
            if (in_array($holidayDayOfWeek, [4, 5], true)) {
                throw new RuntimeException('پنج‌شنبه و جمعه به‌صورت خودکار تعطیل هستند و نیازی به ثبت جداگانه ندارند.');
            }
            $day = substr($holidayDate, 0, 10);
            $title = post_value('holiday_title');
            db()->beginTransaction();
            try {
                db()->prepare('INSERT INTO holidays (holiday_date, title, created_by) VALUES (?, ?, ?)')->execute([$day, $title, $holidayAdmin['id']]);
                $holidayId = (int) db()->lastInsertId();
                save_audit_required((int) $holidayAdmin['id'], 'holiday_created', null, ['holiday_id' => $holidayId, 'holiday_date' => $day, 'title' => $title, 'is_active' => 1]);
                db_commit('index.php');
            } catch (Throwable $exception) {
                if (db()->inTransaction()) db()->rollBack();
                throw $exception;
            }
            flash('success', 'تعطیلی به تقویم SLA اضافه شد.');
            redirect($holidayReturnTo());
        }

        if ($action === 'import_holidays') {
            $holidayAdmin = require_permission('holidays.manage');
            $events = parse_calendar_import($_FILES['calendar_file'] ?? []);
            $insert = db()->prepare('INSERT INTO holidays (holiday_date, title, created_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), is_active = 1, created_by = VALUES(created_by)');
            db()->beginTransaction();
            $importedCount = 0;
            try {
                foreach ($events as $event) {
                    $eventDayOfWeek = (int) (new DateTimeImmutable(substr($event['date'], 0, 10), new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))))->format('N');
                    if (in_array($eventDayOfWeek, [4, 5], true)) {
                        continue;
                    }
                    $insert->execute([substr($event['date'], 0, 10), $event['title'], $holidayAdmin['id']]);
                    $importedCount++;
                }
                save_audit_required((int) $holidayAdmin['id'], 'holidays_imported', null, ['count' => $importedCount, 'skipped_weekends' => count($events) - $importedCount, 'source' => 'calendar_import']);
                db_commit('index.php');
            } catch (Throwable $exception) {
                if (db()->inTransaction()) db()->rollBack();
                throw $exception;
            }
            flash('success', $importedCount . ' تعطیلی از فایل تقویم وارد شد؛ پنج‌شنبه و جمعه نادیده گرفته شدند.');
            redirect($holidayReturnTo());
        }

        // ───── تقویم کاری: ساعت و روزهای تعطیل هفتگی مبنای محاسبهٔ SLA ─────
        // اعتبارسنجی اینجا سخت‌گیرانه است چون مقدار نامعتبر مستقیم وارد حلقه‌های
        // business_due_at() و next_business_start() می‌شود. sla_business_hours()
        // هم لایهٔ دوم مهار را دارد، ولی خطا باید همین‌جا به کاربر گفته شود نه
        // اینکه بی‌صدا به پیش‌فرض برگردد.
        if ($action === 'save_work_hours') {
            $holidayAdmin = require_permission('holidays.manage');
            $workStart = (int) ($_POST['work_start_hour'] ?? 8);
            $workEnd = (int) ($_POST['work_end_hour'] ?? 16);
            if ($workStart < 0 || $workStart > 23) {
                throw new RuntimeException('ساعت شروع کار باید بین ۰ تا ۲۳ باشد.');
            }
            if ($workEnd > 24 || $workEnd <= $workStart) {
                throw new RuntimeException('ساعت پایان کار باید بزرگ‌تر از ساعت شروع و حداکثر ۲۴ باشد.');
            }
            $weekendDays = [];
            foreach ((array) ($_POST['weekend_days'] ?? []) as $rawDay) {
                $day = (int) $rawDay;
                if ($day >= 1 && $day <= 7 && !in_array($day, $weekendDays, true)) {
                    $weekendDays[] = $day;
                }
            }
            sort($weekendDays);
            if (count($weekendDays) >= 7) {
                throw new RuntimeException('دست‌کم یک روز هفته باید روز کاری بماند.');
            }
            db()->beginTransaction();
            try {
                save_setting('sla.work_start_hour', (string) $workStart);
                save_setting('sla.work_end_hour', (string) $workEnd);
                save_setting('sla.weekend_days', implode(',', $weekendDays));
                save_audit_required((int) $holidayAdmin['id'], 'work_hours_updated', null, [
                    'work_start_hour' => $workStart,
                    'work_end_hour' => $workEnd,
                    'weekend_days' => implode(',', $weekendDays),
                ]);
                db_commit_or_fail('holiday-actions.php:save_work_hours');
            } catch (Throwable $exception) {
                if (db()->inTransaction()) db()->rollBack();
                throw $exception;
            }
            setting_forget_cache();
            sla_business_hours_forget_cache();
            flash('success', 'تقویم کاری ذخیره شد. مهلت درخواست‌های جدید با این ساعت‌ها حساب می‌شود.');
            redirect($holidayReturnTo());
        }

        if ($action === 'delete_holiday') {
            $holidayAdmin = require_permission('holidays.manage');
            $holidayId = max(0, (int) ($_POST['holiday_id'] ?? 0));
            db()->beginTransaction();
            try {
                $lookup = db()->prepare('SELECT id, holiday_date, title FROM holidays WHERE id = ? LIMIT 1 FOR UPDATE');
                $lookup->execute([$holidayId]);
                $holiday = $lookup->fetch();
                if ($holiday) {
                    db()->prepare('DELETE FROM holidays WHERE id = ?')->execute([$holidayId]);
                    save_audit_required((int) $holidayAdmin['id'], 'holiday_deleted', null, ['holiday_id' => $holidayId, 'holiday_date' => (string) $holiday['holiday_date'], 'title' => (string) $holiday['title']]);
                }
                db_commit('index.php');
            } catch (Throwable $exception) {
                if (db()->inTransaction()) db()->rollBack();
                throw $exception;
            }
            flash('success', 'تعطیلی حذف شد.');
            redirect($holidayReturnTo());
        }
