<?php

declare(strict_types=1);

/**
 * actions/food-brand-actions.php — ۱ اکشن POST برند فیش غذا (فاز ۲ بازسازی، برش ۷ — نسخهٔ ۱.۶۲.۱۸).
 *
 * شرح فارسی بخش‌ها (به ترتیب داخل فایل):
 *   save_food_ticket_brand  ذخیرهٔ لوگو و متن برند چاپ‌شده روی فیش غذا
 *
 * این فایل فقط از دل بلوک try در «ناحیهٔ ۴» index.php با require بارگذاری می‌شود و در
 * همان حوزهٔ متغیرها اجرا می‌شود. متن اکشن‌ها بایت‌به‌بایت منتقل شده است. بلوک اسکن شد: بدون function/continue/کلوژر.
 */

// محافظ: این فایل فقط از طریق index.php بارگذاری می‌شود؛ دسترسی مستقیم ممنوع.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

        // ───── گروه اکشن: برند فیش غذا ─────
        if ($action === 'save_food_ticket_brand') {
            $settingsAdmin = require_permission('settings.users_edit');
            $brandName = post_value('food_ticket_brand_name');
            if ($brandName === '') {
                throw new RuntimeException('نام سامانه چاپ فیش نمی‌تواند خالی باشد.');
            }
            save_setting('food_ticket_brand_name', $brandName);
            if (!empty($_FILES['food_ticket_logo']['tmp_name']) && is_uploaded_file($_FILES['food_ticket_logo']['tmp_name'])) {
                $mime = mime_content_type($_FILES['food_ticket_logo']['tmp_name']) ?: '';
                $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg'];
                if (!isset($allowed[$mime]) || (int) $_FILES['food_ticket_logo']['size'] > 2 * 1024 * 1024) {
                    throw new RuntimeException('لوگوی پنل فیش باید PNG یا JPG و حداکثر ۲ مگابایت باشد.');
                }
                @mkdir(APP_ROOT . '/assets/uploads', 0755, true);
                $filename = 'food-ticket-logo-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                if (!move_uploaded_file($_FILES['food_ticket_logo']['tmp_name'], APP_ROOT . '/assets/uploads/' . $filename)) {
                    throw new RuntimeException('ذخیره لوگوی پنل فیش انجام نشد.');
                }
                save_setting('food_ticket_brand_logo', 'assets/uploads/' . $filename);
            }
            save_audit((int) $settingsAdmin['id'], 'food_ticket_brand_updated');
            flash('success', 'هویت بصری پنل چاپ فیش ذخیره شد.');
            redirect('index.php?page=settings#food-ticket-brand');
        }
