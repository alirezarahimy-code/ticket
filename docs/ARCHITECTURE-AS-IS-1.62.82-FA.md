# ممیزی معماری موجود (As-Is) — سامانه تیکت

- نسخه مرجع: 1.62.82 (فایل VERSION در شاخه main)
- دامنه: فقط مخزن GitHub؛ نسخه نصب‌شده و محیط عملیاتی خارج از این ممیزی‌اند.
- روش: ممیزی ایستا بر اساس فایل‌ها و ایندکس سیستم؛ اجرای زنده روی Windows/XAMPP یا دیتابیس انجام نشده است.
- هدف: ثبت معماری واقعی و ریسک‌ها برای ارتقای تدریجی بدون شکستن رفتار فعلی.

## 1. خلاصه مدیریتی

سامانه یک برنامه PHP/MySQL با استقرار هدفمند Apache/XAMPP است. معماری را باید «مونولیت ماژولارِ در حال گذار» دانست؛ نه MVC کامل و نه مجموعه سرویس‌های مستقل. ورودی اصلی وب index.php است؛ bootstrap.php نشست، پیکربندی و کتابخانه‌های مشترک lib/ را بارگذاری می‌کند. منطق میان فایل‌های ریشه، actions، lib، cron، tools و زیرسامانه‌های تخصصی توزیع شده است.

آمار ثبت‌شده در docs/system-map/summary.json:

| شاخص | مقدار |
|---|---:|
| فایل‌های ایندکس‌شده | 1,131 |
| خطوط ایندکس‌شده | 202,320 |
| نمادها | 5,418 |
| مسیرهای ثبت‌شده | 413 |
| تعریف جدول در فایل‌های مختلف | 455 |
| جدول یکتای شناسایی‌شده | 84 |
| جدول‌های schema.sql | 82 |
| جدول‌های install-empty-db.sql | 82 |
| بسته‌های به‌روزرسانی | 118 |
| قابلیت‌های ثبت‌شده | 36 |

این اعداد شاخص ایندکس‌اند، نه تعداد routeهای فعال runtime یا جدول‌های موجود در یک نصب مشخص.

## 2. ساختار و جریان اجرا

### ورودی و مسیریابی
- index.php حدود 3,727 خط دارد و ورودی اصلی وب است.
- این فایل bootstrap.php و تعداد زیادی فایل حوزه‌ای را بارگذاری می‌کند: سازمان، دارایی، جست‌وجو، پشتیبان‌گیری، چت‌بات، به‌روزرسانی، غذا، تردد، سلامت و داشبورد Logging.
- انتخاب صفحه عمدتاً با پارامتر صفحه و شرط‌های درون index.php انجام می‌شود.
- عملیات POST به فایل‌های actions/*-actions.php واگذار شده‌اند؛ این فایل‌ها در context فراخواننده اجرا می‌شوند و به قرارداد متغیرهای مشترک وابسته‌اند.
- برخی زیرسامانه‌ها مانند غذا، تردد و Governance مسیرهای مستقل یا نیمه‌مستقل دارند؛ همه درخواست‌ها از یک dispatch واحد عبور نمی‌کنند.

### Bootstrap و هسته
bootstrap.php:
1. APP_ROOT را تعریف می‌کند.
2. permissions.php و session_bootstrap.php را بارگذاری و نشست را آغاز می‌کند.
3. پیکربندی را از ITSM_CONFIG_PATH یا config.php می‌خواند.
4. تنظیمات خطا و CSP nonce را آماده می‌کند.
5. ماژول‌های lib را با ترتیب مشخص بارگذاری می‌کند.

ماژول‌های مشترک:
- lib/db-core.php: پیکربندی، PDO، توابع دیتابیس، rate limit و پاسخ HTTP.
- lib/logging-csrf.php: پیام‌ها، لاگ سیستمی، خطا و CSRF.
- lib/auth-scope.php: کاربر جاری، کنترل دسترسی و دامنه مشاهده تیکت/دارایی.
- lib/settings-effects.php: تنظیمات و نقش‌های کلیدی.
- lib/ldap.php: اتصال و همگام‌سازی Active Directory.
- lib/tickets-sla.php: شماره درخواست، تقویم جلالی، SLA/OLA و تاریخچه دارایی.
- lib/notify-users.php: اعلان و عملیات مرتبط با کاربران.
- lib/activity-audit.php: ثبت فعالیت و audit.

ترتیب requireها در bootstrap قرارداد ضمنی برنامه است؛ تغییر ترتیب بدون تحلیل وابستگی‌ها پرریسک است.

### عملیات و منطق حوزه
- actions/: فرمان‌های ثبت، ویرایش، حذف، ارجاع، تغییر وضعیت و عملیات مدیریتی.
- فایل‌های حوزه‌ای ریشه مانند organization.php، permissions.php، inventory.php، asset-profile.php، food-ticket.php، traffic-control.php، governance.php، ola.php و system-update.php ترکیبی از منطق حوزه، دیتابیس و ارائه هستند.
- cron/: پردازش‌های زمان‌بندی‌شده مانند SLA، worker غذا و عملیات Logging.
- tools/: ابزارهای CLI، ممیزی، نگهداری و اسکریپت‌های عملیاتی.
- app/ و scaffold موجود MVC مسیر اصلی dispatch در index.php نیستند؛ تست معماری نیز نبود dispatch مبتنی بر Controller را بخشی از وضعیت فعلی فرض می‌کند.

### رابط کاربری
- رابط اصلی عمدتاً PHP-rendered و متکی بر JS/CSS محلی است.
- زیرسامانه food-ticket-web رابط مستقل‌تری دارد.
- فارسی/RTL، تقویم جلالی، نمودارها و صفحات مدیریتی بخشی از قرارداد رفتاری‌اند.
- URL، نام action، پارامتر POST، redirect، flash و DOM در عمل قرارداد سازگاری محسوب می‌شوند.

## 3. دامنه‌های کسب‌وکار
1. هویت، نشست و ورود محلی/LDAP.
2. سازمان، شرکت، واحدها و انتساب کاربران.
3. تیکت، گفتگو، ارجاع، وضعیت، لغو و تأیید.
4. کاتالوگ خدمات و فرم پویا.
5. SLA/OLA، تقویم کاری و escalation.
6. دارایی، Inventory و CMDB.
7. Change و Problem Management.
8. اعلان داخلی و صف اعلان.
9. دانش‌نامه و چت‌بات.
10. Logging، health، replay و integrity.
11. Backup/Restore و System Update.
12. غذا، سفارش و چاپ.
13. کنترل تردد.
14. کنترل CD/DVD.
15. گزارش، تحلیل، جست‌وجو و خروجی.

این‌ها مرزهای تحلیلی دامنه‌اند، نه ادعای وجود 15 سرویس deployable مستقل.

## 4. داده و پایگاه داده
- MySQL/MariaDB و PDO فناوری persistence اصلی است.
- schema.sql و install-empty-db.sql هرکدام 82 جدول طبق system map دارند؛ برابر بودن تعداد، برابری ستون‌ها، indexها، FKها و defaultها را اثبات نمی‌کند.
- نقشه کل مخزن 84 جدول یکتا را تشخیص داده است؛ فایل‌های تاریخی و بسته‌ها نیز در این inventory دیده می‌شوند.
- migrations/، مهاجرت‌های updates/ و SQLهای ریشه تاریخچه چندنسلی schema را تشکیل می‌دهند.
- updater اجرای migration را با schema_updates ثبت می‌کند.
- مدل سازمان شامل شرکت، واحد سلسله‌مراتبی، مدیران و ارتباط با users است.
- مدل مجوز ترکیبی است: نقش و role_permissions پایه‌اند؛ گروه‌های عمومی دسترسی مجوزهای افزایشی می‌دهند و مجوز نقش را حذف نمی‌کنند.
- فیلدهای حوزه‌ای متعدد در users و tickets نگهداری می‌شوند؛ این انتخاب برای سازگاری فعلی مفید اما coupling و پیچیدگی داده ایجاد می‌کند.

## 5. امنیت و کنترل دسترسی
شواهد موجود در کد:
- PDO و prepared statements در هسته دیتابیس.
- کنترل CSRF و نشست متمرکز.
- کاتالوگ مجوزها و scope در permissions.php و lib/auth-scope.php.
- کنترل دسترسی دانلود پیوست و محدودیت نوع/حجم فایل در مسیرهای مربوط.
- قواعد Apache/.htaccess برای مسیرهای حساس.
- محدودسازی نرخ API/ورود.
- ثبت رویدادهای audit.

محدودیت: وجود تابع یا رشته CSRF در تست ایستا اثبات اجرای درست آن در تمام مسیرهای POST نیست. SecurityTest.php عمدتاً وجود فایل/تابع/الگوی متنی را بررسی می‌کند. آزمون end-to-end برای CSRF واقعی، IDOR، upload bypass، session fixation و مجوز سطح رکورد در این ممیزی اجرا نشده است. بنابراین این سند گواهی امنیتی یا اعلام نبود آسیب‌پذیری نیست.

## 6. نصب و به‌روزرسانی
- نصب تازه از install.php و SQLهای نصب انجام می‌شود.
- انتشار افزایشی ZIP شامل manifest.json، migrations و files است.
- requires_version در updater بررسی می‌شود.
- قبل از اعمال، بکاپ ساخته می‌شود و مسیرهای محافظت‌شده مانند config و storage نباید جایگزین شوند.
- migrationها به ترتیب manifest اجرا و در schema_updates ثبت می‌شوند.

### ریسک‌های updater
1. در system_update_apply() نتیجه ZipArchive::open() و extractTo() کنترل نمی‌شود؛ stage ناقص می‌تواند وارد مراحل بعدی شود.
2. migration و کپی فایل‌ها یک تراکنش اتمیک واحد نیستند؛ شکست میانه کار rollback خودکار کامل برای schema و فایل‌ها در همین تابع دیده نمی‌شود.
3. VERSION پس از کپی فایل‌ها نوشته می‌شود؛ این نکته مثبت است اما صحت کامل بسته و وجود تمام فایل‌های manifest را اثبات نمی‌کند.
4. تطبیق فهرست فایل‌های manifest با ZIP، hash فایل‌ها، اندازه کل استخراج، ورودی‌های تکراری و مسیر مقصد باید تقویت شود.
5. بکاپ قبل از تغییر ساخته می‌شود، اما restore خودکار و تمرین بازیابی در همین مسیر اثبات نشده است.

## 7. CI و آزمون
- ci.yml: PHP lint، system audit بدون DB، SecurityTest، ArchitectureTest، تست routing و SLA تیکت، تست‌های غذا و تست پیش‌نیاز updater.
- logging-contract.yml: قرارداد ایستای Logging و guard نسخه/بسته.
- logging-acceptance.yml: گردش دستی جداگانه برای MariaDB و Windows.

در workflow پذیرش و tests/run_logging_staging_checks.ps1 ارجاع به این فایل‌ها وجود دارد که در main بررسی‌شده پیدا نشدند:
- tests/test_windows_logging_static.py
- tests/test_update_manifest_guard_static.py
- tools/verify_logging_acl.ps1
- cron/reconcile_update_audit.php
- tools/verify_logging_web_protection.ps1

tests/test_logging_acl_windows.ps1 وجود دارد، اما جایگزین verifier عملیاتی tools/verify_logging_acl.ps1 نیست. این ارجاع‌های مفقود باعث می‌شوند مسیر Windows و بخش Operational پیش از تکمیل تست‌ها متوقف شوند. اجرای واقعی workflow در این ممیزی انجام نشده؛ نتیجه از بررسی مسیرها و فایل‌هاست.

تست‌های ایستا مفیدند اما جایگزین تست integration، security behavior، migration و acceptance روی Windows/XAMPP نیستند. Parity نصب تازه باید ساختار کامل schema را مقایسه کند، نه فقط تعداد جدول‌ها.

## 8. یافته‌ها و اولویت‌ها

| شناسه | اولویت | یافته | پیامد | اقدام |
|---|---|---|---|---|
| ASIS-01 | P0 | کنترل‌نشدن open/extract در updater | stage ناقص و نصب غیرقابل اتکا | کنترل return، fail-closed، hash و manifest validation |
| ASIS-02 | P1 | ارجاع Logging acceptance به فایل‌های مفقود | Windows/Operational acceptance ناقص | هم‌ترازسازی workflow و runner |
| ASIS-03 | P1 | ترمیم schema در مسیر درخواست عادی | DDL، قفل و شکست خاموش | انتقال تدریجی به migration |
| ASIS-04 | P1 | index.php و bootstrap پرمسئولیت | تغییر دشوار و regression | Router و compatibility adapter |
| ASIS-05 | P1 | مرز نامشخص Use Case/Repository | coupling منطق و persistence | Application API و repository per module |
| ASIS-06 | P1 | تست امنیتی عمدتاً ساختاری | پوشش رفتاری ناکافی | integration و authorization matrix |
| ASIS-07 | P2 | سند معماری قبلی قدیمی | نقشه نادرست برای توسعه‌دهنده | سند نسخه‌دار و تولید خودکار inventory |
| ASIS-08 | P2 | چند منبع schema و بسته‌های تاریخی | schema drift | baseline و parity gate |
| ASIS-09 | P2 | قرارداد UI/POST ضمنی | شکستن رفتار در refactor | characterization و golden tests |

## 9. محدودیت‌های ممیزی
این کار هنوز شامل اجرای Windows/XAMPP، تست با حساب‌های واقعی، مرور دستی همه 413 route، اجرای چندنسلی migration، تست concurrency/load، بررسی همه فایل‌های binary، تمرین restore و تأیید سامانه نصب‌شده نیست. این موارد باید در فاز پویا و staging تکمیل شوند.

## 10. شواهد اصلی
VERSION؛ docs/system-map/summary.json، FILES.tsv و ROUTES.tsv؛ index.php و bootstrap.php؛ lib/* و actions/*؛ schema.sql و install-empty-db.sql؛ migrations/* و updates/*/manifest.json؛ system-update.php؛ .github/workflows/* و tests/*؛ docs/ARCHITECTURE-FA.md (سند قدیمی با خط مبنای نسخه‌ای عقب‌تر).
