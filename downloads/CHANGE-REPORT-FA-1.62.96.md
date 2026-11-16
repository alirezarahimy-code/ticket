# CHANGE-REPORT-FA-1.62.96 — رفع «گرسنگی سرِ صف» در چاپ فیش غذا (۱.۶۲.۹۵ ← ۱.۶۲.۹۶)

- تاریخ: ۲۰۲۶‑۱۱‑۱۴ · نوع: بستهٔ میانی خط ۱.۶۲.x · `requires_version = 1.62.95`
- این بسته **هیچ ارتباطی با RC / «مرکز درخواست‌ها» / ۱٫۶۳ ندارد**؛ فقط دو تغییر در یک فایل (`food-ticket-runtime.php`) دارد و هیچ مهاجرتی اجرا نمی‌کند.
- `VERSION` ریشهٔ مخزن (`1.63`) دست‌نخورده می‌ماند؛ مقدار `VERSION` **داخل بسته** `1.62.96` است.

## ۱) ریشهٔ رخداد ۱۴۰۴/۰۸/۲۳ — چرا از ۱۱:۴۶ حتی یک فیش هم چاپ نشد

- از صبح امروز چاپگر `172.16.4.67` از شبکه خارج بود؛ ۸۰ تلاش چاپ بین ۰۸:۱۱ تا ۱۱:۴۶ ناموفق ماند و **۳۷ فیش** (idهای ۳۷۱۹ تا ۳۷۶۰) به سقف تلاش رسیدند (`retry_count = 5 = maxRetry`).
- کوئری انتخاب صف چاپ worker، با `ORDER BY id ASC LIMIT 30`، همیشه **۳۰ ردیف قدیمیِ قابل‌ادعا** را برمی‌داشت. این ۳۰ ردیف همان فیش‌های مرده بودند:
  - **۱۰ فیش انفرادی** ⇒ در شاخهٔ `retry >= maxRetry` بی‌صدا `skipped++` می‌شدند (بدون لاگ و بدون هیچ ردّ و نشانی).
  - **۲۰ فیش عضوِ یک اجرای گروهی** (`run_id` در payload) ⇒ وارد مسیر فیش گروهی می‌شدند؛ چون `max(retry_count/print_attempts)` اعضا = ۵، مسیر گروهی **بدون claim، بدون چاپ و بدون لاگ** برمی‌گشت (`return ['handled' => true, 'ids' => $ids]`).
  - نتیجه در هر چرخه: `claimed=0, printed=0, failed=0, skipped=10` — دقیقاً همان چیزی که در ۲۵۹ چرخهٔ آخر لاگ امروز دیده می‌شود.
- **۹۳ فیش `pending`** با id ≥ ۳۷۶۱ هرگز در ۳۰تایی برگزیده قرار نمی‌گرفتند ⇒ صفر claim، صفر چاپ، علی‌رغم Worker سالم (heartbeat تازه، ۴۲۷ چرخهٔ امروز)، پنجرهٔ باز (۱۰:۰۰ تا ۱۶:۱۵) و چاپگر جدید `172.16.4.95` (۰٫۹ms).
- ری‌استارت Apache/Worker (۱۴:۲۶) نمی‌توانست کمکی کند — مشکل قفل نبود؛ ترکیبِ «دادهٔ مرده + LIMIT + ORDER BY id ASC» بود.
- «چاپ مجدد خطاها» هم مشکل را حل نمی‌کرد (فیش‌های مرده را هم دوباره روی کاغذ می‌فرستاد؛ ساعت ناهار گذشته بود).

## ۲) تغییرات کد (فقط `food-ticket-runtime.php`)

1. **کوئری انتخاب صف** (هر دو شاخه: با فیلتر `next_retry_at` و fallback بدون آن): اکنون ردیف‌های «مرده» یعنی `GREATEST(COALESCE(retry_count,0), COALESCE(print_attempts,0)) >= maxRetry` (مقدار template؛ پیش‌فرض ۵) را **حذف** می‌کند:
   ```sql
   ... AND (print_status = "printing"
            OR GREATEST(COALESCE(retry_count,0), COALESCE(print_attempts,0)) < 5)
   ```
   ردیف‌های `printing` مانده (>۳۰ دقیقه) از این فیلتر **مستثنا** هستند تا مسیر «چاپ نیمه‌کارهٔ نیمه‌کاریده» همچنان کار کند (آن‌ها در کد به `failed` با پیام مشخص تبدیل می‌شوند).
   رفتار سایر شاخه‌ها (claim، delivery-guard، group path، retry شاخص) بدون تغییر است.
2. **مسیر فیش گروهی / شاخهٔ «اعضا به سقف تلاش رسیدند»:** اکنون یک خط `app_error_log('[food-print] group run=… skipped: …')` ثبت می‌شود تا این برگشتِ ساکت دیگر نامرئی نماند.

## ۳) اقدام دستی امروز (داخل بسته نیست؛ با تصمیم مالک اجرا شود)

انقضای فیش‌های ناهارِ امروز که هرگز چاپ نشدند (مالک: «چاپ نشوند / منقضی شوند» و «۳۷ فیش failed مرده، به‌عنوان چاپ‌نشده از ناهارِ امروز بی‌اعتبار شوند»). بک‌آپ ابتدا؛ سپس در phpMyAdmin یا `mysql`:

```sql
-- ۰) بک‌آپ (روی ویندوز XAMPP):
--    mysqldump -u root -p persian_ticketing food_ticket_events food_ticket_print_log food_ticket_group_runs > C:\xampp\htdocs\ticket\backup-food-20261114.sql

-- ۱) پیش‌نمایش — باید pending=93 و failed=37 نشان دهد:
SELECT print_status, COUNT(*) FROM food_ticket_events
 WHERE punch_date = '2026-11-14' AND meal_type = 'lunch'
   AND print_status IN ('pending','failed','print_error','printing')
 GROUP BY print_status;

-- ۲) انقضا در تراکنش؛ اگر تعداد غیر از ۱۳۰ شد، ROLLBACK کنید:
START TRANSACTION;
UPDATE food_ticket_events
   SET print_status = 'not_printed',
       next_retry_at = NULL,
       processed_at  = NOW(),
       last_error    = TRIM(TRAILING ' | ' FROM CONCAT(COALESCE(last_error,''),
         ' | void-2026-11-14: lunch ticket never printed (printer outage); expired by owner decision'))
 WHERE punch_date = '2026-11-14' AND meal_type = 'lunch'
   AND print_status IN ('pending','failed','print_error','printing');
SELECT ROW_COUNT();  -- باید ۱۳۰ باشد (۹۳ pending + ۳۷ failed) — در غیر این‌صورت ROLLBACK
COMMIT;

-- ۳) راستی‌آزمایی — باید ۰ برگرداند:
SELECT COUNT(*) FROM food_ticket_events
 WHERE punch_date = '2026-11-14'
   AND (print_status IN ('pending','print_error','failed')
        OR (print_status = 'printing' AND updated_at < NOW() - INTERVAL 30 MINUTE));
```

- دو فیش مهمان (`event_type='guest'`) هم در همین انقضا می‌افتند؛ اگر می‌خواهید مهمان‌ها در گزارش چاپ‌نشده حفظ شوند، در هر سه عبارت `AND event_type <> 'guest'` اضافه کنید.
- `print_log` و اجراهای گروهی دست‌نخورده می‌مانند.
- اگر قصد دارید ۹۳ فیش امروز واقعاً چاپ شوند، به‌جای انقضا بسته را **پیش از پایان پنجره (۱۶:۱۵)** اعمال کنید؛ فیش‌ها در چرخهٔ بعدی چاپ می‌شوند. (امروز مالک گزینهٔ انقضا را برگزید.)

## ۴) ترتیب اعمال روی سرور تولید

1. بک‌آپ پوشهٔ وب + دیتابیس (حداقل جداول بالا).
2. SQL بخش ۳ (فقط اگر تأیید امروز را می‌خواهید).
3. محتوای `files/` داخل ZIP را روی ریشهٔ وب کپی کنید (جایگزین می‌شوند؛ چیزی حذف نمی‌شود).
4. **Worker غذا را ری‌استارت کنید** (`schtasks /End /TN "Food Ticket PHP Worker"` سپس `schtasks /Run /TN "Food Ticket PHP Worker"`) — این سرویس طولانی‌مدت است و کد جدید را تا ری‌استارت نمی‌بیند.
5. راستی‌آزمایی: `VERSION` روی سرور باید `1.62.96` شود؛ سپس `tools\diagnose_food_full.php` را اجرا و در بخش «تاریخچهٔ queue» ببینید چرخه‌های `claimed>0` فقط وقتی وجود دارند که فیش واقعی در صف باشد (امروز باید صف خالی و `queue_print=0` باشد) و دیگر چرخهٔ `skipped=10` پایدار نبینید.

## ۵) چیزهایی که عمداً تغییر نکردند

- `VERSION` ریشه و شاخهٔ RC (۱.۶۳) و خودِ بستهٔ ۱.۶۲.۹۵؛ هیچ مهاجرتی افزوده نشد (سرعت اصلاح باگ بدون تغییر اسکیما).
- معنای `not_printed`/`held_absent`، دکمهٔ «چاپ مجدد خطاها»، منطق delivery-guard، سقف زمانی ۲۰ ثانیه (`$queueStarted`)، حد `max_batch`.
- ردیف‌های مردهٔ امروز؛ «failed» بودنشان حفظ می‌شود (گزارش/بازرسی). فقط از کوئری انتخاب حذف شدند.

## برگشت (Rollback)

- فایل `food-ticket-runtime.php` را از بک‌آپ برگردانید و `VERSION` را به `1.62.95` بازگردانید؛ مهاجرتی اعمال نشده که لازم باشد برگردد.
- انقضای SQL قابل بازگشت است: برای ردیف‌های امروز با `last_error LIKE '%void-2026-11-14%'` وضعیت را به `failed` (فیش‌های ۳۷۱۹–۳۷۶۰) و `pending` (۳۷۶۱–۳۸۶۱) بازگردانید — فقط اگر آگاهانه می‌خواهید چاپ دوباره انجام شود.

## تست‌ها

- `php -l food-ticket-runtime.php` تمیز است.
- `php tests/test_food_queue_reconciliation_unit.php` — همه PASS (شامل چاپ گروهی و عدم چاپ صف روی فیش‌های چاپ‌شده).

## فایل‌های داخل بسته

`food-ticket-runtime.php` — به‌همراه این گزارش (`CHANGE-REPORT-FA-1.62.96.md`) و `VERSION` با مقدار `1.62.96`.

داخل ZIP: `manifest.json` (SHA‑256 همهٔ فایل‌ها) + فایل جانبی `.sha256` کنار ZIP.

## دستورهای بررسی/تأیید

- `php -l food-ticket-runtime.php`
- `php tests/test_food_queue_reconciliation_unit.php`
- پس از اعمال و ری‌استارت Worker، خروجی `diag-food-full.txt` را برای تأیید نهایی بفرستید.
