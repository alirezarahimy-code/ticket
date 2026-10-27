<?php

declare(strict_types=1);

/**
 * actions/holiday-actions.php — ۴ اکشن POST تقویم و تعطیلات (فاز ۲ بازسازی، برش ۶ — نسخهٔ ۱.۶۲.۱۷).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   toggle_holiday    تغییر وضعیت تعطیلی یک روز از روی تقویم
 *   add_holiday       افزودن تعطیلی جدید با تاریخ شمسی
 *   import_holidays   ورود گروهی تعطیلات (هر سطر یک تاریخ)
 *   delete_holiday    حذف یک تعطیلی ثبت‌شده
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده؛ تنها مورد
 * continue داخل بلوک، continue داخل حلقهٔ import است که برای include کاملاً بی‌خطر است.
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

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
                redirect('index.php?page=holidays');
            }
            $exists = db()->prepare('SELECT id, is_active FROM holidays WHERE holiday_date = ? LIMIT 1');
            $exists->execute([$day]);
            $row = $exists->fetch();
            if ($row && (int) $row['is_active'] === 1) {
                db()->prepare('UPDATE holidays SET is_active = 0 WHERE id = ?')->execute([(int) $row['id']]);
                flash('success', 'تعطیلی حذف شد.');
            } elseif ($row) {
                db()->prepare('UPDATE holidays SET is_active = 1, title = ?, created_by = ? WHERE id = ?')->execute([post_value('holiday_title') ?: 'تعطیل رسمی', $holidayAdmin['id'], (int) $row['id']]);
                flash('success', 'تعطیلی فعال شد.');
            } else {
                db()->prepare('INSERT INTO holidays (holiday_date, title, created_by, is_active) VALUES (?, ?, ?, 1)')->execute([$day, post_value('holiday_title') ?: 'تعطیل رسمی', $holidayAdmin['id']]);
                flash('success', 'تعطیلی ثبت شد.');
            }
            redirect('index.php?page=holidays');
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
            db()->prepare('INSERT INTO holidays (holiday_date, title, created_by) VALUES (?, ?, ?)')->execute([substr($holidayDate, 0, 10), post_value('holiday_title'), $holidayAdmin['id']]);
            flash('success', 'تعطیلی به تقویم SLA اضافه شد.');
            redirect('index.php?page=holidays');
        }

        if ($action === 'import_holidays') {
            $holidayAdmin = require_permission('holidays.manage');
            $events = parse_calendar_import($_FILES['calendar_file'] ?? []);
            $insert = db()->prepare('INSERT INTO holidays (holiday_date, title, created_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), is_active = 1, created_by = VALUES(created_by)');
            db()->beginTransaction();
            $importedCount = 0;
            foreach ($events as $event) {
                $eventDayOfWeek = (int) (new DateTimeImmutable(substr($event['date'], 0, 10), new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran'))))->format('N');
                if (in_array($eventDayOfWeek, [4, 5], true)) {
                    continue;
                }
                $insert->execute([substr($event['date'], 0, 10), $event['title'], $holidayAdmin['id']]);
                $importedCount++;
            }
            db_commit('index.php');
            flash('success', $importedCount . ' تعطیلی از فایل تقویم وارد شد؛ پنج‌شنبه و جمعه نادیده گرفته شدند.');
            redirect('index.php?page=holidays');
        }

        if ($action === 'delete_holiday') {
            require_permission('holidays.manage');
            db()->prepare('DELETE FROM holidays WHERE id = ?')->execute([(int) ($_POST['holiday_id'] ?? 0)]);
            flash('success', 'تعطیلی حذف شد.');
            redirect('index.php?page=holidays');
        }
