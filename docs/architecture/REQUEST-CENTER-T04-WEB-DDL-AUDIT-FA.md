# ممیزی T04 ـ DDL در مسیر درخواست عادی وب

| مشخصه | مقدار |
|---|---|
| شناسه | RC-T04-WEB-DDL-01 |
| شاخه | `feature/request-center-module-1.62.88` |
| commit کد اجرایی ممیزی | `e1f7f9b581a9005f3a6e08a8dbf5ad9244a5b3b1` |
| commit اجرای آزمون runtime | `2504fae89a2fa8a87b35e11050b20785f94bb84e` |
| روش | ممیزی ایستا + MariaDB general_log روی دیتابیس دورریختنی با PHP built-in HTTP server |
| نتیجه | **FAIL در مسیر Legacy؛ runtime DDL در درخواست وب اثبات شد** |

## ۱. یافتهٔ قطعی در ورودی اصلی

در `index.php`، فراخوانی `ticket_number_ensure_schema()` در ابتدای بارگذاری و پیش از پردازش درخواست وجود دارد. این تابع در `lib/tickets-sla.php` دو مسیر را اجرا می‌کند:

1. `ticket_number_ensure_schema_run()`: در صورت نبود جدول `ticket_number_seq` یا ستون `tickets.ticket_no`، دستور `CREATE TABLE IF NOT EXISTS` یا `ALTER TABLE` اجرا می‌کند.
2. `ticket_hot_schema_ensure()`: ستون‌ها و مقادیر ENUM جدول‌های `tickets` و `users` را بررسی می‌کند و در صورت فقدان، DDL اجرا می‌کند؛ سپس به `org_ensure_schema()`، `org_ticket_schema_ensure()` و `lac_ensure_schema()` می‌رود.

پس در هر درخواست وب، مسیر بررسی Schema فراخوانی می‌شود و در صورت انحراف Schema از انتظار، امکان اجرای DDL در همان درخواست وجود دارد. گارد تراکنش از implicit commit داخل تراکنش جلوگیری می‌کند، اما به‌تنهایی DDL را از چرخهٔ درخواست وب حذف نمی‌کند.

## ۲. سایر مسیرهای صفحه‌ای

ممیزی `index.php` همچنین فراخوانی‌های مشروط زیر را در handlerهای صفحه/عملیات پیدا کرد:

- `asset_profile_ensure_schema()` در مسیر خروجی دارایی و چند مسیر دارایی؛
- `ticket_service_ensure_schema()` در مسیر فرم و نمایش خدمت؛
- `org_ensure_schema()` و `org_ticket_schema_ensure()` در مسیر ایجاد درخواست؛
- `domain_scan_ensure_schema()` در صفحهٔ اسکن دامنه.

این موارد خارج از تغییر اجرایی فعلی RC هستند و در این مرحله دست‌کاری نمی‌شوند؛ اما برای پذیرش T04 سراسری باید مالک و برنامهٔ حذف/انتقال DDL هر کدام مشخص شود.

## ۳. وضعیت مستقل Request Center

ورودی مستقل `request-center.php`:
- هیچ DDL یا تابع `*_ensure_schema()` را مستقیماً فراخوانی نمی‌کند؛
- فقط `RequestCenterRepository::schemaProblems()` را اجرا می‌کند؛
- در صورت ناقص بودن Schema با HTTP 503 متوقف می‌شود و صریحاً اعلام می‌کند که خودش Schema را تغییر نمی‌دهد.

این رفتار با اصل «ورودی RC بدون schema healing» سازگار است؛ اما چون مسیر Legacy در `index.php` هنوز schema ensure دارد، شرط T04 در سطح کل سامانه بسته نشده است.

## ۴. تصمیم و اقدام لازم

- در این commit هیچ کد اجرایی حذف یا جابه‌جا نشده است؛ حفظ رفتار Legacy رعایت شده است.
- آزمون runtime روی MariaDB 10.4.32 و 10.11 با دیتابیس دورریختنی انجام شد؛ جزئیات در بخش ۴ آمده است.
- اگر هدف سیاست صفر DDL در درخواست وب است، توابع ensure باید به Migration/CLI/installer منتقل شوند و مسیر وب فقط preflight خواندنی انجام دهد.
- انتقال باید خارج از تراکنش، با نسخهٔ پشتیبان و روی Clone انجام شود؛ اجرای آن روی سامانهٔ عملیاتی در این مرحله مجاز نیست.
- تغییرات خارج از محدودهٔ RC باید جداگانه تصویب شوند.

## ۴. شواهد runtime روی MariaDB دورریختنی

Workflow اختصاصی Request Center با PHP 8.2، PHP built-in HTTP server و MariaDB general log روی هر دو نسخهٔ 10.4.32 و 10.11 اجرا شد. هیچ اتصال یا تغییری به DB عملیاتی نداشت.

### ۴.۱. درخواست عادی Legacy به index.php با Schema نصب تازه

| نسخه DB | HTTP | DDL ثبت‌شده | DML ثبت‌شده |
|---|---:|---:|---:|
| MariaDB 10.4.32 | 200 | 5 | 5 |
| MariaDB 10.11 | 200 | 5 | 5 |

پنج دستور DDL ثبت‌شده:
- `CREATE TABLE IF NOT EXISTS org_units`
- `CREATE TABLE IF NOT EXISTS org_unit_managers`
- `ALTER TABLE users ADD COLUMN IF NOT EXISTS org_unit_id`
- `ALTER TABLE users ADD COLUMN IF NOT EXISTS manager_user_id`
- `ALTER TABLE org_units ADD COLUMN IF NOT EXISTS chart_side`

این دستورات از مسیر `organization.php` و `ticket_hot_schema_ensure()` به درخواست عادی Legacy می‌رسند. فرم IF NOT EXISTS در SQL وجود دارد؛ general_log اجرای دستور را ثابت می‌کند، اما آزمون فعلی snapshot ساختار قبل/بعد برای تعیین اینکه هر دستور در آن اجرای خاص واقعاً ساختار را تغییر داده یا no-op بوده، ثبت نکرده است.

پنج DML نیز ثبت شد؛ از جمله:
- `UPDATE tickets SET ticket_no = 'IT-00001' WHERE id = '1' ...`
- درج/به‌روزرسانی `ticket_number_seq`
- درج نشانگر `ticket_autofill_done`
- ثبت رویداد در `system_logs`
- درج نشانگر `role_permissions_reconciled`

بنابراین اولین درخواست وب پس از نبود نشانگر autofill، واقعاً دادهٔ یک درخواست موجود را شماره‌گذاری و به‌روزرسانی کرد؛ این رفتار DML است و نباید با DDL اشتباه شود. تابع در هر درخواست فراخوانی می‌شود، ولی نشانگر آن را به backfill اولیه محدود می‌کند.

### ۴.۲. Schema ناقص و ترمیم از مسیر Legacy

روی همان DB دورریختنی، ستون `tickets.cancel_requested_at` حذف شد و درخواست HTTP به `index.php` ارسال شد. در هر دو نسخه MariaDB، پاسخ HTTP 200 بود و general_log دستور زیر را ثبت کرد:

`ALTER TABLE tickets ADD COLUMN cancel_requested_at DATETIME NULL`

ستون پس از درخواست بازسازی شد. این شاهد قطعی است که مسیر Legacy در درخواست وب می‌تواند Schema را تغییر دهد؛ T04 سراسری را نمی‌توان PASS اعلام کرد.

### ۴.۳. اثر داده‌ای ticket_service_ensure_schema

تابع `ticket_service_ensure_schema()` از یک endpoint تشخیصی CI روی HTTP فراخوانی شد. در هر دو نسخه:
- DDL: صفر
- DML: ۴۵ دستور
- ۱۷ دستور INSERT/UPSERT برای دسته‌های IT/Support
- ۲۸ دستور UPDATE برای نگاشت `service_catalog.category_id`

این endpoint فقط برای آزمون ساخته شده و زیر `tests/.htaccess` در Apache مسدود است. بااین‌حال تابع در مسیرهای Legacy فرم/ایجاد درخواست نیز فراخوانی می‌شود؛ بنابراین seed/backfill کاتالوگ در زمان درخواست وب باید در تحلیل side effect باقی بماند.

### ۴.۴. حدود نتیجه

- این آزمون PHP built-in HTTP server است؛ رفتار واقعی Apache/XAMPP و اعمال `cron/.htaccess` هنوز جداگانه آزموده نشده است.
- نتیجه پنج DDL روی index.php متعلق به مسیر Legacy/سازمان است و مجوز تغییر ماژول سازمان یا سایر بخش‌های خارج از RC ایجاد نمی‌کند.
- آزمون مستقل `request-center.php` با نشست احراز‌شده و Schema ناقص در نسخهٔ بعدی همین harness اضافه شده و نتیجه آن هنوز در این سند ثبت نشده است.
- نتیجه فعلی: Legacy FAIL runtime؛ RC مستقل PASS ایستای محدود؛ runtime RC مستقل در انتظار نتیجهٔ harness تکمیلی.

## ۵. وضعیت T04

| جزء | نتیجه |
|---|---|
| RC مستقل `request-center.php` | PASS ایستای محدود: DDL مستقیم/ensure ندارد |
| Legacy `index.php` | FAIL ایستای سیاست صفر DDL: مسیرهای ensure فراخوانی می‌شوند |
| شمارش DDL واقعی در درخواست کامل | **FAIL: پنج دستور DDL در هر دو نسخه MariaDB ثبت شد** |
| T04 کل سامانه | **باز / قابل پذیرش نیست** |

این گزارش ادعای اجرای runtime یا شمارش SQL ثبت‌شده ندارد.
