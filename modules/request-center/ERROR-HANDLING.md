# مرکز درخواست‌ها — قرارداد یکدست‌سازی خطاها (403 / 404 / 503 / 500) و دو مسیر UI

این سند رفتار خطای ماژول را در هر چهار فضای کاری (Requester، Agent، Supervisor، Admin)
تعریف می‌کند و تنها مرجع برای این قرارداد است. هر تغییر در کد باید با این ماتریس تطبیق داشته باشد.

## ماتریس خطاها

| کد | معنا | شرایط وقوع | نوع خروجی | چرام سایت؟ | محل |
|----|------|-------------|-----------|------------|-----|
| 403 | دسترسی مجاز نیست | گیت نقش/مجوز Workspaceها: ادمین مرکز (`user_is_primary_admin`)، سوپروایزر (`user_can('supervisor.panel')`)، صف (`queue.view`)، گزارش (`reports.view`)، تحلیل (`analytics.view`)، ایجاد (`ticket.create`)، تقویم (ترکیبی)، فضای کاری عمومی بدون هیچ گزینهٔ مجاز | صفحهٔ حداقلی self-contained از `request_center_error_page()` | خیر | `lib/http-errors.php` + Callers در `Pages/*.php` |
| 403 | دسترسی مجاز نیست (endpointهای POST) | `require_permission()` هسته در اکشن‌های PRG (مثلاً `supervisor.panel` برای تخصیص) | خروجی متن‌سادهٔ هسته (قرارداد هسته، عامدانه حفظ شده) | خیر | `Actions/*.php` — تغییر هسته مجاز نیست (قلمرو مشترک) |
| 404 | دسترسی مستقیم به فایل ماژول | فراخوانی مستقیم URL داخل `Pages/` یا `Actions/` بدون `APP_ROOT` | متن سادهٔ ۴۰۴ (گارد ابتدایی، بدون هیچ وابستگی) | خیر | گارد ابتدای هر فایل — عمداً helper صدا نمی‌زند تا هرگز Fatal نشود |
| 404 | درخواست پیدا نشد | شناسهٔ نامعتبر/خارج از دامنه در `detail.php` | صفحهٔ با چرام (`render_header` + empty-state) با دکمهٔ بازگشت | بله | `Pages/detail.php` |
| 503 | اسکیما کامل نیست | `schemaProblems()` غیرخالی قبل از رندر (ورودی مستقل و bridge) یا `db_missing_columns` در گزارش | صفحهٔ حداقلی + فهرست موارد ناقص | خیر | `request-center.php`، `Pages/index.php`، `Pages/reports.php` (پیش‌نیاز گزارش) |
| 503 | نشست ثبت در دسترس نیست | تخصیص `rc_create_tokens` غیرممکن (session) | صفحهٔ حداقلی | خیر | `Pages/create.php` |
| 500 | خطای غیرمنتظره | هر `Throwable` رله‌نشده در هر مسیر `request-center.php` | `set_exception_handler` → صفحهٔ حداقلی + `error_log` (بدون افشای پیام فنی به کاربر) | خیر | `request-center.php` |

## قواعد الزامی

1. صفحات خطا **هرگز** به چرام سایت (`render_header`/`render_footer`) وابسته نیستند، مگر همان مسیر
   ۴۰۴ با چرام که بعد از ورودی سالم اجرا می‌شود. علت: چرام ممکن است به همان وابستگی‌های ناقص تکیه کند
   (نمونه: کوئری ستون اسکیماناقص) و ۵۰۳ آگاهانه به ۵۰۰ رله بشود.
2. هیچ خطایی نباید اطلاعات حساس (پیام Exception، مسیر فایل، SQL) را به مرورگر بفرستد؛ فقط `error_log`.
3. هر پاسخ خطا `Cache-Control: private, no-store` و `X-Content-Type-Options: nosniff` می‌گیرد.
4. گاردهای دسترسی مستقیم (ابتدای فایل‌ها) ساده می‌مانند؛ آن‌ها لایهٔ امنیت ثانویه‌اند نه UI.
5. Endpointهای POST رفتار ۴۰۳ هسته (`require_permission`) را حفظ می‌کنند؛ یک‌دست‌سازی آن‌ها تغییر
   قرارداد هستهٔ مشترک (غذا، ابزارها) است و در قلمرو این ماژول نیست.

## دو مسیر UI — وضعیت نهایی

- **مسیر رسمی (تنها مسیر GET):** `request-center.php` — تمام صفحه‌های چرخه‌عمر (`new-ticket`، `ticket`،
  `queue`، `supervisor`، `services`، `reports`، `analytics`) و هر چهار Workspace داخل مرز ماژول رندر می‌شوند.
- **پل سازگاری (legacy bridge) در `index.php`:** هر GET بدون `action` که `page=<همان هفت‌مقدار>` باشد،
  با **302** به `request-center.php?<همان query string>` هدایت می‌شود (بالای فایل، قبل از لود ماژول‌های هسته).
  این پل توسط تست‌های Contract و smoke (مثلاً `test_request_center_phase2_smoke_mariadb.php`) پین شده است
  و حذف آن **بدون** شکستن تست‌های Legacy مجاز نیست.
- لینک‌های داخلی ماژول و داشبورد/جست‌وجوی سراسری مستقیماً به `request-center.php` اشاره می‌کنند.
  استثناهای عمدی: لینک‌های stat-cardهای داشبورد هسته که در تست انتشار `tools/test_release_134_offline.php`
  پین شده‌اند و همچنان از همین پل عبور می‌کنند (عمداً تغییر نکرده‌اند).
- **POST یک مسیر است:** همهٔ اکشن‌های RC از `post-handler.php`/`Actions/*.php` عبور می‌کنند؛ بعد از هر
  عملیات موفق با Redirect به URL مناسب بازمی‌گردد (PRG). پل `index.php` برای POSTهای RC قدیمی صرفاً
  dispatcher هسته را اجرا می‌کند و مالک منطق نیست (هیچ منطق جدیدی در پل اضافه نمی‌شود).
- `require` فایل‌های `Pages/*.php` در انتهای `index.php` فقط شبکهٔ ایمنی مسیر POST قدیمی است؛
  برای GET هرگز اجرا نمی‌شود (قبلش `exit` پل فعال است).

## نگهبان‌های عدم رگرسیون

- `tests/test_request_center_access_matrix_static.py` — وجود حداقل یک گارد سرور-محور و یک 403/404 صریح در هر صفحه.
- `tools/test_release_134_offline.php`، `tests/test_request_center_phase2_smoke_mariadb.php` — قرارداد پل 302.
- `tests/test_request_center_t07_migration_clone.php` — اسکیما؛ ۵۰۳ نباید به ۵۰۰ رله شود.
