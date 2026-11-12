# اصلاحات r2 پس از بازبینی بسته (۲۰۲۶-۱۱-۱۱)

مبنای بازبینی: مقایسهٔ ایستا با ۱.۶۲.۸۸. هیچ کدی اجرا نشده؛ فقط تحلیل ایستا و بررسی ثبات تعریف توابع/ساختار manifest.

## اصلاح‌شده در r2
| # | مشکل r1 | اصلاح r2 | فایل | نیاز به آزمون |
|---|---------|----------|------|----------------|
| ۱ | لینک اعلان‌های تیکتی قطع بود؛ صفحهٔ اعلان‌ها به خودش لینک می‌داد | لینک به `request-center.php?page=ticket&id=N` | `lib/notify-users.php`, `assets/app.js` | کلیک اعلان در مرورگر |
| ۲ | منوی governance و لینک تیکت‌های مرتبط حذف شده بود | بازگردانی با آدرس ورودی RC | `governance.php` | باز کردن صفحهٔ تغییر/مشکل |
| ۳ | هسته با Fatal به ماژول وابسته بود | گارد نرم `is_file` در ۴ نقطه | `index.php` | حذف موقت پوشهٔ ماژول روی staging |
| ۴ | `rejected` در چند فهرست نهایی نبود | افزوده شد (بار باز، پنل مدیریت، لغو/پاک‌سازی) | `index.php`, `Pages/detail.php`, `Actions/ticket-actions.php` | آزمون T12 |
| ۵ | ۵ دستور DDL در هر درخواست (`org_ensure_schema`) | فقط وقتی لازم است (`ticket_org_schema_ready()`) | `lib/tickets-sla.php`, `Pages/create.php` | شمارش با general_log |
| ۶ | ریشهٔ ماژول و docs/architecture بدون منع دسترسی مستقیم | `.htaccess` | دو فایل جدید | درخواست مستقیم باید ۴۰۳ شود (Apache) |
| ۷ | `schema.sql` جدول نوبت‌ها را نداشت | جدول افزوده شد | `schema.sql` | نصب تازه با install.php |
| ۸ | 035: بازنویسی دسته‌ها، ادعای نادرست «بدون تغییر داده»، نبود rollback، پیش‌نیاز 036 غیرقابل‌اجرا | درج فقط-ناموجود، هدر دقیق، اسکریپت rollback، گارد پیش‌نیاز | `migrations/035`, rollback | اجرای دوباره روی MariaDB 10.4/10.11 و MySQL 8 |
| ۹ | CHANGE-REPORT با diff نمی‌خواند | بازنویسی بخش‌های «فایل‌های مشترک» و «مهاجرت‌ها» | `CHANGE-REPORT-FA-1.63.md` | — |
| ۱۰ | افزودن جدول به `schema.sql` بدون DROP در بازسازی کامل (کشف‌شده با اجرای آزمون‌های ایستای پروژه) | افزودن به فهرست DROP | `rebuild-final.sql` | اجرای `SOURCE rebuild-final.sql` روی کلون |
| ۱۱ | تغییر خط pinشدهٔ `export_report` در `index.php` (شکست آزمون مسیر امنیتی) | خط اصلی بدون تغییر؛ گارد داخل بلوک با پاسخ ۵۰۳ | `index.php` | — |

## شواهد اجرایی (آنچه واقعاً اجرا شد)
- lexer مستقل PHP روی ۲۱۸ فایل مبنا و ۴۹ فایل بسته: صفر مشکل ساختار آکولاد/پرانتز/رشته/heredoc. (PHP واقعی در محیط تحلیل نیست؛ این جایگزین `php -l` نیست.)
- `node --check` روی `app.js` و `request-center.js`: موفق.
- آزمون‌های Python ایستای پروژه (۱۳۰ فایل، با اجراکنندهٔ ساده بدون fixtureهای pytest): مقایسهٔ r1 با r2 — r2 دو آزمون parity را سبز کرد و دو رگرسیون خودش را (index.php و rebuild-final.sql) پیدا و رفع کرد.
- **اجرا نشده:** هر چیز نیازمند PHP/MySQL/MariaDB (آزمون‌های PHP، 035، rollback، قفل نوبت‌ها).

## اصلاح‌نشده / نیازمند تصمیم یا آزمون
1. گیت‌های قرمز (T07 بازپخش مهاجرت، Logging، ۸۵ شکست ایستا، ۲ assertion CI، ۲ شکست `test_route_security_static`) و اجرا نشدن Contract/T12/QA روی HEAD جدید.
2. `ticket_number_ensure_schema()` در `index.php` همچنان در مسیر legacy هر درخواست اجرا می‌شود (بخش ensure ستون‌ها فقط SELECT می‌فرستد؛ `lac_ensure_schema` و `org_ticket_schema_ensure` شرط‌دارند). حذف کامل نیازمند ویرایش هسته و اثبات برابری اسکیما است.
3. `ticket_service_ensure_schema()` (در `lib/logging-csrf.php`، فایل هسته که در این بسته نیست) هنوز در مسیر legacy دسته‌ها را upsert و فعال می‌کند — رفتار قدیمی، نه جدید.
4. تغییر معنای دید مدیر در `lib/auth-scope.php` (≈۵۸ خط): باید جدا تأیید و با `tools/test_access_coverage_offline.php` و آزمون دسترسی واقعی سنجیده شود.
5. `chatbot.php` و `global-search.php`: محتوای تغییرات کامل بازبینی نشد.
6. پیش‌نیاز نسخهٔ غذا: ۱.۶۲.۹۳ یا ۱.۶۲.۹۴؟ (مستندات ناهمخوان). `requires_version` در manifest عمداً تغییر نکرد.
7. FK برای `request_center_appointments` (ticket_id / assignee_id): نیازمند تصمیم مالک و همترازی `schema.sql` و `install-empty-db.sql`.
8. بازگشت: اسکریپت rollback آزموده نشده.
9. مبنای مقایسه ۱.۶۲.۸۸ است؛ تطبیق با ۱.۶۲.۹۰ لازم است.
