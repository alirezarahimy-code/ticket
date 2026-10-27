<?php

declare(strict_types=1);

/**
 * actions/notification-actions.php — ۲ اکشن POST اعلان‌ها (فاز ۲ بازسازی، برش ۷ — نسخهٔ ۱.۶۲.۱۸).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   mark_all_notifications  علامت‌گذاری همهٔ اعلان‌های کاربر به‌عنوان خوانده‌شده
 *   mark_notification       علامت‌گذاری یک اعلان مشخص به‌عنوان خوانده‌شده
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده است. بلوک اسکن شد: بدون function/continue/کلوژر.
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: اعلان‌ها ─────
        if ($action === 'mark_all_notifications') {
            db()->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0')->execute([(int) $user['id']]);
            flash('success', 'همه اعلان‌ها خوانده‌شده علامت‌گذاری شدند.');
            redirect('index.php?page=notifications');
        }
        if ($action === 'mark_notification') {
            $notificationId = (int) ($_POST['notification_id'] ?? 0);
            db()->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?')->execute([$notificationId, (int) $user['id']]);
            redirect('index.php?page=notifications');
        }
