# 🚨 رفع فوری خطای ۵۰۰ بعد از آپدیت ۱.۶۲.۳۷

## علت
در نسخهٔ ۱.۶۲.۳۷ فایل `index.php` به یک فایل تازه به نام `lib/asset-identity.php`
وابسته شد، و همزمان نحوهٔ بارگذاری `asset-names.php` طوری شد که روی بعضی نصبها
تابعها دوبار تعریف میشدند (`Cannot redeclare ...`) → خطای ۵۰۰ در کل سامانه.

## راه حل (به ترتیب، یکی از دو حالت)

### حالت الف: پنل هنوز بالا میآید (حتی با خطا)
بستهٔ `ticket-update-1.62.38-HOTFIX-500-v13736-20261028.zip` را از
منوی «بهروزرسانی سامانه» نصب کنید. سپس `Ctrl+F5`.

### حالت ب: پنل اصلاً بالا نمیآید ← دستی (فقط ۲ فایل)
دو فایل زیر را از مخزن دانلود و جایگزین همنامشان در ریشهٔ سامانه کنید
(از طریق FTP / فایلمنیجر هاست / دسترسی سرور):

1. `index.php`
2. `asset-names.php`

لینکهای مستقیم (raw)، شاخهٔ `arena/38fea5b6-ticket`:

- https://raw.githubusercontent.com/alirezarahimy-code/ticket/arena/38fea5b6-ticket/index.php
- https://raw.githubusercontent.com/alirezarahimy-code/ticket/arena/38fea5b6-ticket/asset-names.php

> ⚠️ حتماً **هر دو** فایل را جایگزین کنید. فقط یکی کافی نیست.
> بعد از جایگزینی، `VERSION` را هم دستی برابر `1.62.38` قرار دهید
> (یا بعداً بستهٔ ۱.۶۲.۳۸ را نصب کنید تا خودش انجام دهد).

### برگشت کامل (اگر خواستید کلاً به قبل برگردید)
`system-update.php` قبل از نصب یک بکاپ کامل در `storage/backups/` ساخته است
(شامل دامپ SQL و پوشهٔ uploads). نام پوشه، تاریخ و ساعت نصب است.
همچنین میتوانید فایلهای زیر را از کامیت `2dee9bf` (نسخهٔ ۱.۶۲.۳۶) برگردانید:
`index.php`, `inventory.php`, `api/inventory.php`, `domain-scan.php`,
`asset-profile.php`, `asset-names.php`, `food-ticket.php`, `schema.sql`.

## بعد از بالا آمدن سامانه
1. `Ctrl+F5` بزنید.
2. لاگ خطای PHP را چک کنید که پیام دقیق ۵۰۰ چه بوده (اختیاری، ولی به من بگویید
   تا اگر علت دیگهای بود، دقیقاً همان را درست کنم).
3. برای شناسنامهها: `php tools/asset_dedupe.php` (فقط گزارش) سپس
   `php tools/asset_dedupe.php --apply --enforce` — ولی فعلاً عجله نکنید؛
   اول سامانه پایدار شود.

## تغییرات فنی این هاتفیکس
- تمام توابع هویت (`asset_identity_key`, `asset_resolve_id`,
  `asset_sync_identity_key`) به `asset-names.php` منتقل شدند.
- `lib/asset-identity.php` فقط یک شیم ۳خطی شد و هیچ فایلی به آن وابسته نیست.
- تمام `require`های مربوطه `require_once` شدند تا تعریف مجدد رخ ندهد.
- migration جدید `015` هیچ `ALTER TABLE ... MODIFY` روی enum اجرا نمیکند
  (که روی بعضی نصبها شکست میخورد) و کلاً additive/idempotent است.
