# وضعیت نهایی تحویل و پذیرش ارتقاء Logging

تاریخ: ۲۰۲۶-۱۱-۰۴  
نسخه داخلی: `1.62.75`  
محیط ساخت: Linux / PHP 8.4.24؛ بدون `pdo_mysql` و `ZipArchive`

## وضعیت فازها

| فاز | وضعیت کد/ابزار | پذیرش عملیاتی مقصد |
|---|---|---|
| ۰ — تثبیت مبنا | بررسی Full Source، Update، manifest و checksum؛ Git history در دسترس نبود | تطبیق با نسخه واقعی نصب‌شده باید روی Staging انجام شود |
| ۱ — امنیت و صحت داده | پالایش رخدادمحور، کنترل مجوز، نتیجه `unknown` برای سوابق فاقد شواهد و آزمون‌های آفلاین | تراکنش و مجوز با MySQL و حساب‌های واقعی مقصد باز است |
| ۲ — دوام و بازیابی | Spool، retry/backoff، processing recovery، Dead Letter، بازسازی Issue و صف اعلان پیاده‌سازی شده‌اند | قطع/بازگشت MySQL، توقف Worker، رقابت چند Worker و کمبود دیسک در Windows Staging باز است |
| ۳ — پوشش و هم‌بستگی | Request/Trace/Operation/Parent IDs و پوشش مسیرهای اصلی؛ تست ایستای پوشش اجرا شده | سناریوهای end-to-end با ترافیک واقعی همه ماژول‌ها باز است |
| ۴ — داشبورد | صفحه سلامت فنی قبلی حفظ شد؛ داشبورد گرافیکی جدید با نمودار ساعتی/روزانه، KPI، Issue، صف، Worker، اعلان و سلامت اجزا اضافه شد | تطبیق عددی با Queryهای مرجع و آزمون نقش‌های واقعی باز است |
| ۵ — تشخیص آفلاین و Agent | قواعد deterministic، شواهد، confidence، مشابه‌یابی، redaction و CLI bundle پیاده‌سازی شده‌اند | ZIP واقعی Agent به `ZipArchive` مقصد و بررسی DLP/ACL روی Staging نیاز دارد |
| ۶ — سخت‌سازی و تحویل | preflight فقط‌خواندنی، اسکریپت‌ها، راهنمای پذیرش، backup/restore و rollback runbook آماده‌اند | Windows Server، IIS، Task Scheduler، Event Viewer، ACL، بار، Backup/Restore و Rollback هنوز اجرا/امضا نشده‌اند |

## آزمون‌های اجراشده در این محیط

- مجموعه منتخب Python برای Logging، System Health، Windows static، داشبورد، بسته و منبع نسخه: **۵۲ passed، ۴ skipped**.
- PHP lint برای فایل‌های PHP موجود در manifest و آزمون داشبورد: **۴۵ از ۴۵ بدون خطای نحوی**.
- آزمون مستقل داشبورد آفلاین: **۶ از ۶ موفق**؛ شامل تولید SVG و حفظ دید صف محلی در قطع DB.
- هسته Logging: ۲۲ تست موفق.
- رگرسیون Audit و پالایش: ۱۳ تست موفق.
- هم‌بستگی System/Activity/Ticket: ۵ تست موفق.
- تشخیص آفلاین و پالایش Agent: ۹ تست موفق.
- تجمیع Issue: ۸ تست موفق.
- بازپخش و بازیابی صف: ۹ تست موفق.
- پوشش Trace: ۱۷ تست موفق.
- ظرفیت Spool: ۳ تست موفق.
- رقابت نویسندگان Spool: ۴۰۰ از ۴۰۰ رکورد JSONL کامل.
- بسته Update و SHA-256: ساختار، فهرست فایل‌ها و checksumها اعتبارسنجی شدند.
- ZIP Full Source و Update: آزمون سلامت آرشیو موفق.

## محدودیت‌ها و موارد باز

1. محیط حاضر افزونه `pdo_mysql` ندارد؛ بنابراین اتصال واقعی MySQL/MariaDB و آزمون تراکنش‌ها اجرا نشده‌اند.
2. افزونه `ZipArchive` در PHP این محیط موجود نیست؛ تولید ZIP Agent روی مقصد باید آزمایش شود.
3. Windows Server، IIS، Task Scheduler، Event Viewer و ACL مقصد در دسترس نبودند.
4. آزمون کامل Backup/Restore و Rollback روی Clone انجام نشده است.
5. مجموعه کامل تاریخی پروژه در این Full Source همه ZIPهای قدیمی را همراه ندارد؛ اجرای یک‌باره کل pytest به‌دلیل ارجاع برخی تست‌های تاریخی به آرشیوهای غایب و تفاوت snapshotهای قدیمی، شکست‌هایی دارد. مجموعه منتخب مرتبط با این تحویل جداگانه اجرا و نتیجه آن در بالا ثبت شده است.

## نتیجه صادقانه

**کدنویسی و بسته‌بندی نسخه 1.62.75 انجام شده است، اما پذیرش عملیاتی نهایی نیست. Migration 025 برای idempotency اعلان و جلوگیری از حذف آبشاری تاریخچه Ticket اضافه شده است. آزمون‌های MySQL/Windows و Clone/Staging مقصد باید اجرا شوند؛ نصب Production تا ثبت شواهد پذیرش مجاز نیست.**


## الحاقیهٔ اصلاحات نسخه 1.62.75

اصلاحات کد در این نسخه: Audit قبل از اتصال DB آماده و در outage به Spool منتقل می‌شود؛ Stack Trace پالایش‌شده به خطاهای Exception افزوده شده؛ handlerهای CLI فعال شده‌اند؛ Fingerprint v2 مرکزی SHA-256 استفاده می‌شود؛ خطاهای transient دیتابیس از Dead Letter مستثنا هستند؛ Circuit Breaker محلی با Probe نیمه‌باز اضافه شده؛ آمار Spool پنج ثانیه Cache می‌شود؛ Migration 023 و schema تازه‌نصب با CHAR(64) هماهنگ شده‌اند؛ Migration 024 و schema تازه‌نصب default نتیجهٔ Audit را `unknown` می‌کنند.

تست‌های افزوده: `test_logging_phase73_regressions.php` و `test_logging_audit_outage_spool.php`. نتایج واقعی اجرای مجموعه در فایل `docs/TEST-REPORT-1.62.73-FA.md` ثبت شده‌اند.

**محدودیت:** در این محیط MySQL/MariaDB در دسترس نیست؛ بنابراین اجرای واقعی Migration، آزمون تراکنش و بازیابی DB، و پذیرش Windows Server هنوز انجام نشده‌اند.

## الحاقیهٔ اصلاحات تکمیلی 1.62.75

- Migration 025: افزودن `notifications.idempotency_key` با قید یکتا؛ Worker اعلان Issue و Health از کلید پایدار استفاده می‌کنند تا retry پس از crash رکورد اعلان داخلی را تکرار نکند.
- Worker heartbeat مستقل از DB در `storage/logs/worker-heartbeats` برای replay، rebuild، notification و health-check؛ Health Monitor قدیمی/ناموفق بودن Workerها را هشدار می‌دهد.
- FK جدول `ticket_events` از `ON DELETE CASCADE` به `ON DELETE RESTRICT` تغییر کرد؛ حذف سخت Ticket دارای سابقهٔ رویداد تا ایجاد فرایند آرشیو مصوب مسدود است.
- اسکریپت Set ACL در حالت Apply، در صورت مشاهدهٔ ACE گسترده، قبل از تغییر متوقف می‌شود.
- Full suite تاریخی پروژه با suite منتخب Logging یکی نیست؛ PASSهای منتخب نباید به‌عنوان سبزشدن کل پروژه گزارش شوند.


## الحاقیه نسخه 1.62.76 — اصلاحات هسته و اتصال ماژول‌ها

- نسخه هدف: `1.62.76`، پایه دقیق Update: `1.62.75`؛ Migration جدید ندارد.
- Circuit Breaker در تراکنش فعال دیگر اجازه نمی‌دهد Audit موفق به‌صورت مستقل در Spool ذخیره شود؛ شکست ثبت موفقیت، تراکنش عملیات را fail-closed می‌کند.
- `save_audit_required()` و `save_audit()` ماژول و هدف را به‌صورت یکنواخت تعیین می‌کنند.
- Holiday، CD/DVD، Governance/CAB، LDAP، Profile و Ticket از نظر ثبت، طبقه‌بندی و فراداده پوشش تکمیلی گرفته‌اند.
- فیلتر مدیریتی از wildcard مبتنی بر Module خالی پاک‌سازی شده و تاریخچه Ticket دارای جست‌وجو، جزئیات پالایش‌شده و صفحه‌بندی شده است.
- آزمون‌های آفلاین PHP و Python قابل اجرا هستند؛ آزمون MySQL واقعی، Windows ACL و Staging مقصد در این محیط NOT RUN باقی می‌مانند.
- نتیجه: آماده تحویل به پایلوت کنترل‌شده پس از پذیرش مالک سامانه؛ Production برای حسابرسی حساس NOT APPROVED.
