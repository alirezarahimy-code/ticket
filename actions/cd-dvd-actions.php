<?php

declare(strict_types=1);

/**
 * actions/cd-dvd-actions.php — ۳ اکشن POST آرشیو CD/DVD (فاز ۲ بازسازی، برش ۷ — نسخهٔ ۱.۶۲.۱۸).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   cd_dvd_create           ثبت دیسک جدید در آرشیو
 *   cd_dvd_update           ویرایش مشخصات دیسک موجود
 *   cd_dvd_delete           حذف دیسک از آرشیو
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده است. بلوک اسکن شد: بدون function/continue/کلوژر.
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: آرشیو CD/DVD ─────
        if ($action === 'cd_dvd_create') {
            $id = cd_dvd_create_record($user);
            flash('success', 'گردش رسانه با شماره ثبت ' . $id . ' ذخیره شد.');
            redirect('index.php?page=cd-dvd');
        }
        if ($action === 'cd_dvd_update') {
            cd_dvd_update_record($user);
            flash('success', 'ثبت رسانه به‌روزرسانی شد.');
            redirect('index.php?page=cd-dvd');
        }
        if ($action === 'cd_dvd_delete') {
            cd_dvd_delete_record($user);
            flash('success', 'ثبت رسانه حذف شد.');
            redirect('index.php?page=cd-dvd');
        }
