# راهنمای عملیاتی Logging — نسخه 1.62.76

## پیش‌نیاز

- PHP 8.1 یا جدیدتر؛ PHP 8.2/8.3/8.4 ترجیح دارد.
- افزونه PDO MySQL فعال باشد.
- افزونه‌های `mbstring` و `zip` برای امکانات عمومی برنامه توصیه می‌شوند؛ مسیر Logging برای کوتاه‌سازی متن، fallback به `iconv` و سپس `substr` دارد.
- پوشه‌های `storage/logs` و `storage/logs/spool` برای کاربر سرویس وب و کاربر Task Scheduler قابل نوشتن باشند؛ دسترسی عمومی وب به این پوشه‌ها باید مسدود باشد.
- قبل از Migration از دیتابیس و فایل‌ها نسخهٔ پشتیبان تهیه شود.

## ترتیب ارتقا

**بستهٔ Update نسخهٔ 1.62.76 فقط برای نسخهٔ پایهٔ دقیقاً 1.62.75 است و Migration جدیدی ندارد. ابتدا روی Clone/Staging اجرا شود؛ Full Source فقط برای نصب تازه است و نباید روی پوشهٔ برنامهٔ موجود Extract شود.

1. ابتدا روی Staging یک کپی از برنامه و دیتابیس تهیه کنید.
2. پیش از Update، Ledger جدول `schema_updates` و ساختار واقعی جدول‌های Logging را بررسی کنید؛ در نصب استاندارد 1.62.75، Migrationهای Logging قبلی باید در Ledger ثبت شده باشند. نسخه 1.62.76 Migration جدیدی اجرا نمی‌کند؛ پیش از ارتقا، تطبیق schema نسخه پایه را بررسی کنید.
3. بستهٔ Update نسخهٔ `1.62.76` با base دقیق `1.62.75` را از طریق Updater داخلی اعمال کنید؛ فایل‌های `config.php`، `storage/` و `uploads/` نباید بازنویسی شوند.
4. صفحهٔ «سلامت سامانه» را باز کنید. بخش «ساختار جدید Logging» و «تجمیع و هشدار Logging» باید سبز باشند.
5. خطای آزمایشی کنترل‌شده در Staging ایجاد کنید و بررسی کنید یک ردیف در `system_logs` و یک Issue در `log_issues` ساخته شود.
6. در Staging دیتابیس را موقتاً از دسترس خارج کنید؛ رخداد باید در JSONL صف شود. پس از بازگشت دیتابیس، Worker را اجرا و خالی‌شدن صف و ایجاد Issue را بررسی کنید.

## Windows Server Task Scheduler

### بازپخش صف و هشدار سلامت

- Program/script: `C:\Windows\System32\cmd.exe`
- Arguments: `/c "D:\ITSM\cron\run_logging_maintenance.bat"`
- Start in: `D:\ITSM\cron`
- Trigger: هر ۵ دقیقه، با اجرای task حتی اگر کاربر وارد سیستم نباشد.
- حساب اجراکننده باید به PHP CLI، فایل config و پوشهٔ `storage/logs` و دیتابیس دسترسی داشته باشد.
- در صورت نصب PHP در مسیر دیگر، متغیر محیطی `PHP_EXE` را برای حساب Task Scheduler تعریف کنید.

### پاک‌سازی دوره‌ای

- Program/script: `C:\Windows\System32\cmd.exe`
- Arguments: `/c "D:\ITSM\cron\run_logging_retention.bat"`
- Trigger: روزانه در ساعت کم‌ترافیک (برای نمونه 02:30).

## آستانه‌ها

- صف قدیمی‌تر از ۱۰ دقیقه: هشدار؛ قدیمی‌تر از ۳۰ دقیقه: بحرانی.
- رخداد ازدست‌رفته در ۲۴ ساعت اخیر: بحرانی.
- بازپخش موفق در ۱۵ دقیقهٔ اخیر در حالی که صف وجود دارد: هشدار.
- فضای آزاد دیسک کمتر از ۲۰٪: هشدار؛ کمتر از ۱۰٪: بحرانی.
- حجم جدول `system_logs` از ۱ گیگابایت: هشدار؛ از ۵ گیگابایت: بحرانی.
- خطای دیتابیس در ۲۴ ساعت اخیر: هشدار؛ ۵ مورد یا بیشتر: بحرانی.
- اعلان هر کلید هشدار برای مدیر اصلی حداکثر هر ۳۰ دقیقه یک‌بار ارسال می‌شود.
- وجود هر فایل در `storage/logs/spool/dead-letter` هشدار مستقل ایجاد می‌کند؛ این فایل‌ها در صف فعال و شمارندهٔ سن صف محاسبه نمی‌شوند.
- باقی‌ماندن فایل `*.rewrite.tmp` از اجرای قبلی هشدار جداگانه ایجاد می‌کند؛ فایل `.processing` منبع اصلی بازیابی است.

## کلاس‌های نگهداری

| کلاس | مدت نگهداری |
|---|---:|
| Debug | ۷ روز |
| Info / Notice | ۳۰ روز |
| رخدادهای کسب‌وکار | ۳۰ روز حداقل؛ پیش‌فرض ۶۰ روز و قابل تنظیم تا ۳۶۵۰ روز |
| Warning / Error / Critical فنی | حداقل ۹۰ روز |
| امنیت، حسابرسی و عملیات حساس | حداقل ۱۸۳ روز |
| Issueهای resolved | ۳۶۵ روز پس از آخرین مشاهده؛ موارد باز/درحال بررسی حذف نمی‌شوند |

مقدار فرم نگهداری فقط می‌تواند دورهٔ خطاهای فنی را افزایش دهد؛ امکان کوتاه‌کردن کلاس‌های محافظت‌شده وجود ندارد.

## تست‌های Staging

- اجرای `php tests/test_logging_core_offline.php`
- اجرای `php tests/test_logging_issue_aggregator.php`
- اجرای `php tests/test_logging_spool_capacity.php` برای آزمون سقف صف و مسیر اضطراری
- اجرای `php tests/test_logging_replay_offline.php` برای retry و Dead Letter
- اجرای `php tests/test_logging_spool_concurrency.php` (در محیط دارای pcntl)
- اجرای `php tests/test_logging_mysql_integration.php` با DSN پایگاه‌دادهٔ آزمایشی مجزا و تأیید متغیر `ITSM_TEST_ALLOW_DESTRUCTIVE=YES`.
- اجرای `php cron/replay_logging_spool.php` و سپس `php cron/check_logging_health.php`.
- بررسی فایل‌های `logging-worker-task.log`، `logging-health-task.log` و `logging-retention-task.log`.

## Rollback

1. Task Scheduler را موقتاً غیرفعال کنید.
2. نسخهٔ پشتیبان فایل‌ها و دیتابیس را بازیابی کنید.
3. Migration شمارهٔ 020 جدول‌های افزوده‌شده را حذف نمی‌کند؛ برای بازگشت معمولاً حذف آن‌ها لازم نیست. اگر حذف الزامی شد، ابتدا از `log_issues` و `logging_health_alerts` خروجی بگیرید و سپس فقط در پنجرهٔ نگهداری و با تأیید DBA حذف کنید.
4. فایل‌های `storage/logs/spool` را حذف نکنید؛ تا پایان بازیابی باید حفظ شوند.
5. پس از Rollback، Task Scheduler را با نسخهٔ سازگار دوباره فعال کنید.

---

## افزودهٔ سخت‌سازی و عملیات مستقل (مهاجرت‌های 021 و 022)

### مهاجرت‌ها و ترتیب اجرا

پس از مهاجرت‌های 019 و 020، در محیط Staging این دو فایل نیز اجرا شوند:

1. `migrations/026-logging-legacy-result-unknown.sql` — نتیجهٔ سوابق قدیمی فاقد Event ID/Request ID را `unknown` می‌کند؛ context مدل Issue را کامل می‌کند؛ تاریخچهٔ رسیدگی، شناسهٔ پردازش رخداد و صف اعلان قابل‌بازیابی را می‌سازد.
2. `migrations/027-logging-correlation-ids.sql` — ستون‌های Trace ID، Operation ID و Parent Event ID را به لاگ فنی، فعالیت و تاریخچهٔ درخواست اضافه می‌کند.

هر دو مهاجرت با کنترل وجود ستون/شاخص قابل اجرای مجدد طراحی شده‌اند. قبل از اجرا، Backup دیتابیس لازم است.

### Workerهای جدید

- `php cron/rebuild_logging_issues.php 500` — بازسازی دسته‌ای Issueهایی که رخداد خام آن‌ها ثبت شده اما Aggregator موفق نشده است. پردازش با Event ID یکتا است و شمارنده را دوباره افزایش نمی‌دهد. وضعیت آخرین اجرا در `storage/logs/logging-issue-rebuild-status.json` ثبت می‌شود.
- `php cron/deliver_logging_issue_notifications.php 100` — صف اعلان‌های Issue را از روی Issueهای واجدشرایط بازیابی می‌کند، اعلان‌ها را ارسال می‌کند و فقط پس از موفقیت، زمان ارسال را ثبت می‌کند. خطاها با Backoff افزایشی دوباره تلاش می‌شوند؛ پس از ۸ تلاش ناموفق به وضعیت failed می‌روند.
- `php cron/replay_logging_spool.php` — همان Worker بازپخش صف اضطراری است.
- `php cron/check_logging_health.php` — بررسی و اعلان سلامت.

در Windows Task Scheduler این وظایف را با حساب سرویس محدود و مجوزهای لازم برای اجرای PHP، دیتابیس و پوشهٔ `storage` تعریف کنید. برای Workerهای پرتکرار، اجرای هم‌زمان همان Task را ممنوع کنید. لاگ خروجی و Last Run Result هر Task باید پایش شود.

### هشدار مستقل از دیتابیس در Windows Event Log

هستهٔ Logging در شکست‌های اضطراری، علاوه بر `error_log`، فقط کد ثابت رخداد و زمان را در `storage/logs/emergency.log` ثبت می‌کند؛ متن خام خطا یا رازها در این مسیر نوشته نمی‌شود. فایل اضطراری در ۵ MiB چرخش می‌کند و چهار نسخهٔ قبلی را نگه می‌دارد.

یک‌بار در PowerShell با دسترسی Administrator منبع رخداد را ایجاد کنید:

```powershell
New-EventLog -LogName Application -Source TicketLogging
```

سپس Task زمان‌بندی‌شدهٔ زیر را هر یک دقیقه با حسابی که مجوز نوشتن در Application Event Log دارد اجرا کنید:

```bat
cron\run_logging_emergency_eventlog.bat
```

اسکریپت `cron/Write-LoggingEmergencyEvent.ps1` رخدادهای جدید را به Windows Application Log می‌فرستد و Offset محلی را نگه می‌دارد. این مسیر به اتصال دیتابیس نیاز ندارد؛ اما برای مشاهدهٔ آن باید Event Source و Task Scheduler روی Windows Server واقعی آزموده شوند.

### ظرفیت صف اضطراری

سقف پیش‌فرض صف اضطراری ۲۵۶ MiB، حداکثر ۲۵۶ فایل و حداقل فضای آزاد ۲۵۶ MiB است. در صورت عبور از سقف، ثبت در صف رد می‌شود، شمارندهٔ رخداد ازدست‌رفته افزایش می‌یابد و مسیر emergency ثبت می‌شود. این سقف‌ها با ثابت‌های `LOGGING_SPOOL_MAX_BYTES`، `LOGGING_SPOOL_MAX_FILES` و `LOGGING_SPOOL_MIN_FREE_BYTES` قابل تنظیم‌اند. تغییر سقف باید با ظرفیت دیسک، نرخ رخداد و توان Worker هماهنگ شود.

### صف Dead Letter و بازیابی دستی

فقط رکوردهای ساختاری معیوب (JSON نامعتبر، envelope ناسازگار، شناسه نامعتبر یا stream ناشناخته) پس از ۸ چرخه به `storage/logs/spool/dead-letter` منتقل می‌شوند. خط‌های معتبر در خطای PDO/شبکه هرگز صرفاً به‌علت تعداد retry به Dead Letter نمی‌روند و در صف فعال باقی می‌مانند. خط JSONL معیوب بدون تغییر نگهداری می‌شود؛ فایل‌های این پوشه خودکار حذف یا بازپخش نمی‌شوند و وجودشان هشدار سلامت ایجاد می‌کند. ابتدا علت (Schema/Migration، دادهٔ نامعتبر یا خطای پایدار DB) را بررسی و رفع کنید. برای بازپخش کنترل‌شده، یک کپی از فایل انتخابی را با نام یکتا و پسوند `.jsonl` به پوشهٔ اصلی `spool` برگردانید؛ فایل اصلی Dead Letter را تا تأیید تحویل موفق نگه دارید. Event ID باعث می‌شود رخدادهای قبلاً ثبت‌شده دوباره درج یا در Issue دوباره‌شماری نشوند. از انتقال گروهی بدون بررسی و از حذف دستی فایل‌های `.processing` خودداری کنید.

### محافظت مسیرهای Storage در IIS و Apache

- Apache: فایل `storage/.htaccess` دسترسی وب را رد می‌کند.
- IIS: فایل `storage/web.config` دسترسی HTTP و Directory Browsing را مسدود می‌کند و Handlerهای قابل اجرا را از آن مسیر حذف می‌کند.
- پس از نصب، آزمون واقعی HTTP را اجرا کنید:

```powershell
.\tests\verify_storage_web_protection.ps1 -BaseUrl 'https://staging.example.local'
```

برای تمام مسیرهای storage باید پاسخ 401، 403 یا 404 دریافت شود؛ پاسخ 200 به معنی شکست کنترل است. وجود `web.config` به‌تنهایی اثبات تنظیم درست IIS نیست.

### تاریخچه و هم‌بستگی

- `log_issues` مرجع اصلی وضعیت Issue است؛ `system_incidents` فقط برای سازگاری با داده‌های قدیمی باقی می‌ماند و دیگر نباید منبع تغییر وضعیت جدید باشد.
- `log_issue_history` تغییرات وضعیت، مسئول و یادداشت را با before/after ثبت می‌کند.
- `log_issue_events` از دوباره‌شماری Event ID در بازسازی و بازپخش جلوگیری می‌کند.
- `log_issue_notifications` وضعیت تحویل، تعداد تلاش و خطای آخر را نگه می‌دارد.
- Request ID، Trace ID و Operation ID در لاگ فنی، فعالیت و تاریخچهٔ درخواست مشترک‌اند؛ Parent Event ID برای پیوند رویداد فرزند در دسترس است.

### آزمون‌های اجباری محیط مقصد

این موارد با تست آفلاین جایگزین نمی‌شوند: اجرای Migrationها روی نسخهٔ کپی دیتابیس مقصد، MySQL/MariaDB واقعی، دو Worker هم‌زمان، توقف ناگهانی Worker، قطع DB، کمبود فضای دیسک، بازیابی spool، ارسال اعلان با حساب‌های واقعی، Event Viewer ویندوز، ACL پوشه‌ها و آزمون HTTP روی IIS/Apache.

### آرشیو مستقل و شواهد دست‌کاری

برای آرشیو روزانهٔ قابل‌راستی‌آزمایی، دو متغیر محیطی سیستمی در Windows Server تنظیم کنید:

- `LOGGING_INTEGRITY_KEY` — کلید تصادفی حداقل ۳۲ کاراکتری؛ آن را در فایل پروژه، دیتابیس یا پوشهٔ وب ذخیره نکنید.
- `LOGGING_INTEGRITY_BACKUP_DIR` — مسیر روی Volume جدا از مسیر برنامه، مانند `E:\ITSM-Logging-Integrity`؛ ACL آن فقط برای حساب سرویس و مدیران مجاز باشد.

پس از تنظیم متغیرها و راه‌اندازی مجدد سرویس PHP/Web، Task روزانهٔ زیر را اجرا کنید:

```bat
cron\run_logging_integrity_export.bat
```

برای راستی‌آزمایی یک آرشیو:

```bat
php cron\verify_logging_integrity.php E:\ITSM-Logging-Integrity\logging-integrity-YYYY-MM-DD.jsonl
```

آرشیو روزانه immutable است و در صورت وجود فایل همان تاریخ، روی آن overwrite نمی‌کند. آرشیو JSONL دارای زنجیرهٔ SHA-256 درون‌فایلی و Manifest قالب ۲ دارای HMAC-SHA256 و پیوند HMAC به snapshot روز قبل است. Manifest، `key_id` غیرمحرمانه دارد؛ تغییر کلید بدون فرایند چرخش مستند متوقف می‌شود و نبود snapshot روز قبل در حضور آرشیو قدیمی‌تر به‌عنوان gap رد می‌شود. کلید HMAC باید جدا از Volume آرشیو نگهداری شود؛ اگر کلید و آرشیو هر دو در اختیار یک مهاجم باشند، HMAC به‌تنهایی مانع جعل نیست. این ابزار snapshot روزانه می‌سازد؛ جایگزین Backup دیتابیس یا سیاست نسخه‌بندی/Offline rotation سازمان نیست.


### قفل و هم‌زمانی Worker بازپخش

Worker فقط هنگام انتقال اتمیک هر فایل از `.jsonl` به `.processing` قفل نویسنده را می‌گیرد. خواندن، درج دیتابیس و ساخت فایل retry خارج از قفل نویسنده انجام می‌شود؛ بنابراین نوشتن رخدادهای جدید در همان زمان متوقف نمی‌شود. فایل‌های retry نام یکتا و شمارندهٔ تلاش دارند و هیچ‌گاه فایل روزانه‌ای را که writer دوباره ساخته است overwrite نمی‌کنند. پس از ۸ تلاش فقط خط‌های ساختاری معیوب به Dead Letter منتقل می‌شوند؛ خطاهای گذرای دیتابیس در صف فعال باقی می‌مانند و Circuit Breaker زمان Probe بعدی را کنترل می‌کند. `replay.lock` همچنان اجرای هم‌زمان دو Worker بازپخش را منع می‌کند.

### زمان‌بندی پیشنهادی در Windows Task Scheduler

- هر ۱ دقیقه: `cron\run_logging_maintenance.bat` (بازپخش، بازسازی Issueهای جاافتاده، تحویل اعلان‌های Issue و بررسی سلامت).
- هر ۱ دقیقه: `cron\run_logging_emergency_eventlog.bat` (ارسال مسیر اضطراری به Windows Application Event Log).
- روزانه: `cron\run_logging_retention.bat` (Retention لاگ‌ها و جداول جانبی).
- روزانه پس از پایان روز کاری: `cron\run_logging_integrity_export.bat` (آرشیو امضاشده روی Volume مستقل).

Taskهای روزانه باید پس از پایان عملیات پرترافیک اجرا شوند. برای هر Task گزینهٔ «Do not start a new instance» فعال باشد. خروجی هر Task در فایل‌های `storage/logs/*-task.log` ثبت می‌شود. اجرای واقعی و مجوز حساب سرویس باید روی Windows Server مقصد تأیید شود.

برای مشاهدهٔ ACL بدون تغییر: `powershell -File tools\verify_logging_acl.ps1 -ApplicationRoot <APP_ROOT>`. اسکریپت تغییر ACL به‌صورت پیش‌فرض فقط طرح را چاپ می‌کند؛ ابتدا `-Apply -WhatIf` و پس از تأیید DBA/Windows Admin، اجرای واقعی با `-Apply` و تأیید تعاملی انجام شود. نام Service Account باید از سرویس واقعی استخراج شود؛ placeholder را جایگزین کنید.


## ردیابی چرخه‌های Worker (فاز ۳)

- هر درخواست HTTP شناسه‌های Request و Trace و Operation مستقل دارد و `X-Request-Id` و `X-Trace-Id` در پاسخ صادر می‌شوند. Header ورودی کاربر برای ساخت شناسه پذیرفته نمی‌شود.
- هر عملیات POST می‌تواند Operation ID جداگانه داشته باشد، در حالی که Request ID و Trace ID همان درخواست ثابت می‌مانند.
- Workerهای طولانی‌مدت غذا و اسکن دامنه در ابتدای هر چرخه `logging_begin_execution()` را اجرا می‌کنند؛ بنابراین هر چرخه Request/Trace مستقل دارد.
- برای بررسی خطای Worker، timestamp و نام Worker را از خروجی CLI بردارید و با Request ID ثبت‌شده در رخدادهای مرکزی و فایل‌های spool تطبیق دهید.
- `logging_correlation_context()` شناسه‌های جاری را برای انتقال کنترل‌شده به پردازش داخلی برمی‌گرداند؛ مقادیر دریافتی از کاربر را بدون اعتبارسنجی به‌عنوان شناسهٔ قابل اعتماد استفاده نکنید.

## تشخیص آفلاین و بسته Agent — فازهای ۵ و ۶

- داشبورد Issueها علت احتمالی، درجه اطمینان، بررسی‌های پیشنهادی و تعداد Issueهای مشابه را نشان می‌دهد.
- برای تولید بسته Agent از ریشه پروژه اجرا کنید:

```powershell
php .\cron\export_logging_agent_bundle.php
# یا برای یک Issue مشخص:
php .\cron\export_logging_agent_bundle.php <64-character-fingerprint>
```

- ZIP در `storage/logs/agent-bundles` ساخته می‌شود. این پوشه باید فقط برای حساب سرویس و کارشناسان مجاز ACL شود و از HTTP قابل دسترسی نباشد.
- بسته JSON/Markdown/HTML شامل رخدادهای پالایش‌شده و خلاصه تشخیصی است. تولید ZIP نیازمند PHP ZipArchive است.
- برای preflight فقط‌خواندنی Staging اجرا کنید: `php .\cron\verify_logging_release.php`.
- راهنمای کامل سناریوها و معیار پذیرش: `docs/PHASE-06-STAGING-ACCEPTANCE-FA.md`.


## قابلیت‌های پایهٔ نسخه 1.62.73 — حفظ‌شده در 1.62.74

- Fingerprint نسخهٔ ۲ با SHA-256 و تابع مرکزی `logging_fingerprint_v2` در ثبت رویداد، بازپخش و تجمیع Issue استفاده می‌شود. رکوردهای قدیمی SHA-1 حذف یا بازنویسی نمی‌شوند؛ ستون `system_logs.fingerprint` در Migration 023 به ۶۴ نویسه گسترش می‌یابد.
- هندلرهای خطا برای HTTP و CLI فعال‌اند. Workerهای CLI خطا را در Spool ثبت می‌کنند و خلاصهٔ امن را در STDERR می‌نویسند؛ خروجی HTML تولید نمی‌شود.
- Stack Trace استثنا تا ۱۲ فریم، بدون آرگومان تابع و با پالایش مسیر و پیام، در `meta_json` ذخیره می‌شود. برای خطاهای Fatal زمان Shutdown ممکن است Trace در دسترس PHP نباشد.
- نتیجهٔ Audit با وضعیت‌های `success`، `failure`، `denied` و `unknown` ذخیره می‌شود؛ رخداد فاقد نتیجهٔ قابل‌استنباط در لایهٔ ذخیره‌سازی `unknown` می‌ماند و رخدادهای `access_denied`/`csrf_419` به‌عنوان `denied` و رخدادهای `_failed` به‌عنوان `failure` طبقه‌بندی می‌شوند.
- Audit پیش از هر وابستگی به DB، payload کامل را از Session و ورودی معتبر فراخواننده می‌سازد. `current_user()` و مسیر ورود، Snapshot حداقلی شناسه/نام‌کاربری/نام/نقش را در Session نگه می‌دارند؛ در قطع DB همان payload با Event ID ثابت به JSONL منتقل می‌شود.
- Circuit Breaker محلی با حالت Open و یک Probe نیمه‌باز، تلاش‌های اتصال پیاپی را هنگام اختلال DB محدود می‌کند. وضعیت در `storage/logs/db-circuit.json` نگهداری می‌شود.
- آمار Spool تا ۵ ثانیه cache می‌شود و با نوشتن/بازپخش صف invalidation می‌شود تا داشبورد در هر بار بازدید کل JSONL را بی‌جهت اسکن نکند.

### Migration جدید

`migrations/028-logging-fingerprint-v2.sql` طول ستون Fingerprint جدول `system_logs` را از ۴۰ به ۶۴ افزایش می‌دهد. `migrations/029-activity-result-default-unknown.sql` مقدار پیش‌فرض `activity_logs.result` را به `unknown` تغییر می‌دهد. هر دو افزایشی و قابل اجرای مجدد هستند و داده‌های قدیمی را بازنویسی نمی‌کنند. اجرای آن‌ها روی MySQL/MariaDB واقعی Staging باید قبل از Production آزموده شود.

### تست‌های جدید

- `php tests/test_logging_phase73_regressions.php`
- `php tests/test_logging_audit_outage_spool.php`
- `php tests/test_logging_replay_offline.php`
- `php tests/test_logging_mysql_integration.php` فقط با دیتابیس تست اختصاصی و متغیرهای حفاظتی مستندشده.


- `migrations/029-activity-result-default-unknown.sql` — مقدار پیش‌فرض ستون `activity_logs.result` را برای رخدادهای جدید به `unknown` تغییر می‌دهد؛ داده‌های موجود را بازنویسی نمی‌کند.


## Integrity export — صف و بازپخش

پیش از ساخت Snapshot روزانه، Exporter قفل‌های `replay.lock` و `.writer.lock` را به‌صورت non-blocking می‌گیرد. اگر Worker/Writer فعال باشد یا هر فایل غیرخالی در صف JSONL، حالت processing، فایل rewrite موقت یا Dead Letter باقی مانده باشد، Export با کد خروج ۳ متوقف می‌شود. ابتدا صف را بررسی و تخلیه کنید؛ رکورد Dead Letter را بدون بررسی دستی حذف نکنید. این Gate از Snapshot در حضور backlog جلوگیری می‌کند، اما جایگزین کنترل عملیاتی زمان‌بندی و بررسی دیررس‌های خارج از Spool نیست.

## ACL بررسی پوشهٔ لاگ

`tools/verify_logging_acl.ps1` فقط ACL را می‌خواند و ACEهای Allow برای Everyone، Authenticated Users، Users و Domain Users را هشدار می‌دهد. با `-Strict` در صورت یافتن چنین ACEهایی کد خروج ۲ می‌دهد. این بررسی محاسبهٔ کامل Effective Access یا گروه‌های تو در تو نیست و اسکریپت Set نیز ACEهای گسترده را خودکار حذف نمی‌کند؛ تغییر ACL فقط پس از بررسی هویت واقعی سرویس و برنامهٔ بازگشت مجاز است.

## Idempotency اعلان‌ها و Heartbeat مستقل Workerها

- Migration 025 ستون nullable `notifications.idempotency_key CHAR(64)` و Unique Index آن را اضافه می‌کند. فراخوانی‌های عادی اعلان بدون تغییر رفتار باقی می‌مانند؛ Workerهای Logging برای اعلان Issue و Health کلید پایدار می‌فرستند تا retry پس از crash اعلان داخلی تکراری نسازد.
- Workerهای replay، issue-rebuild، issue-notification و health-check وضعیت اجرای خود را به‌صورت اتمیک در `storage/logs/worker-heartbeats/*.json` می‌نویسند. این فایل‌ها به DB وابسته نیستند؛ مجوز پوشه باید فقط به حساب سرویس و مدیران داده شود.
- Health Monitor در صورت قدیمی یا ناموفق بودن heartbeat اعلان و بازسازی Issue هشدار ایجاد می‌کند؛ بازپخش فقط هنگام وجود backlog پایش stale می‌شود.
- Migration 025 حذف Ticket دارای سابقهٔ `ticket_events` را با FK نوع RESTRICT مسدود می‌کند. تا زمان ارائهٔ آرشیو و Restore پذیرفته‌شده، hard-delete چنین Ticketهایی مجاز نیست.

## Late-arrival reconciliation برای Integrity

- اجرای روزانهٔ `cron/run_logging_integrity_export.bat` ابتدا Snapshot اصلی روز قبل را ایجاد می‌کند، سپس هفت تاریخ گذشته را با `--reconcile-only` بررسی می‌کند. تاریخ فاقد Snapshot پایه در این مرحله skip می‌شود و به‌اشتباه به‌عنوان genesis chain ساخته نمی‌شود.
- اگر ردیف append-only با شناسهٔ بزرگ‌تر پس از Snapshot اولیه وارد DB شود، یا نسخهٔ جدیدی از state قابل‌تغییر دیده شود، Exporter یک فایل `logging-integrity-YYYY-MM-DD.late-000001-....jsonl` می‌سازد. Snapshot اصلی immutable باقی می‌ماند.
- هر supplement دارای HMAC مستقل، ارجاع به HMAC و final hash Snapshot پایه، شمارهٔ ترتیبی و HMAC supplement قبلی است. حذف، جابه‌جایی، تغییر محتوا یا شکستن توالی supplementها در Verify رد می‌شود.
- برای بازبینی یک تاریخ خارج از پنجرهٔ هفت‌روزه: `php cron/export_logging_integrity.php YYYY-MM-DD --reconcile-only`.
- Verify فایل supplement با همان دستور `php cron/verify_logging_integrity.php <supplement.jsonl>` انجام می‌شود؛ زنجیرهٔ پایه و تمام supplementهای پیشین نیز بررسی می‌شوند.
