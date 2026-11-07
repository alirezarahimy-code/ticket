# دفتر شواهد و موانع فاز اول پروژه ارتقای مرکز درخواست‌ها

| مشخصه | مقدار |
|---|---|
| شناسه | RC-PHASE-01-EVIDENCE-REGISTER |
| تاریخ ثبت | 2026-11-07 |
| مخزن | `alirezarahimy-code/ticket` |
| شاخه اجرایی | `feature/request-center-module-1.62.88` |
| Commit فعلی شاخه اجرایی/شواهد | `3dac5e8949953473abee9d59cff0fac993bd235d` |
| مبنای As-Is | `ab073ee9fffc56ccd1a2093d02725b95891e0d63` |
| سند معماری حاکم | `docs/architecture/REQUEST-CENTER-ARCHITECTURE.md` نسخه 1.14 |
| PR | #30 — Open / Draft |
| وضعیت فاز اول | **باز؛ تکمیل نشده؛ اجازه ورود به فاز دوم صادر نشده است** |

> این دفتر، وضعیت شواهد را ثبت می‌کند و سند معماری 1.14 را جایگزین یا اصلاح نمی‌کند. هیچ تغییر کد اجرایی، Schema، Migration یا داده در این ثبت انجام نشده است.

## ۱. قرارداد شماره‌گذاری پنج فاز پروژه

برای گزارش‌دهی و کنترل پیشرفت، پنج فاز مصوب پروژه به این صورت ملاک است. سند معماری 1.14 در بخش 10 از شماره‌گذاری داخلی صفر تا چهار استفاده می‌کند؛ نگاشت زیر فقط برای رفع اختلاف شماره‌گذاری است و محتوای مصوب معماری را تغییر نمی‌دهد.

| فاز رسمی پروژه | دامنه | نگاشت به بخش 10 سند 1.14 |
|---|---|---|
| فاز 1 | ممیزی As-Is، تطبیق To-Be، نقشه وابستگی و انتقال، خط مبنای آزمون و معیار پذیرش | فاز صفر سند |
| فاز 2 | استقلال واقعی ماژول و انتقال تدریجی صفحات/Actionها | فاز یک سند |
| فاز 3 | اصلاح کامل چرخه عمر و گردش‌کار درخواست | فاز دو سند |
| فاز 4 | تقویم کارشناسان و برنامه‌ریزی رسیدگی | فاز سه سند |
| فاز 5 | تکمیل UI/گزارش، سخت‌سازی، رگرسیون، نصب آزمایشی، Restore و بسته انتشار | فاز چهار سند |

گیت‌های T01 تا T12 آزمون‌های فنی درون فازها هستند و فازهای مستقل پروژه محسوب نمی‌شوند.

## ۲. اسناد و شواهد موجود

| قلم | شواهد موجود | ارزیابی |
|---|---|---|
| معماری To-Be | سند معماری 1.14، شامل ADRها، اصول دامنه، مرز ماژول، Workflow، Migration و معیارهای پذیرش | معماری تصویب شده؛ تصویب معماری معادل تکمیل ممیزی یا اثبات اجرا نیست |
| As-Is | `REQUEST-CENTER-AS-IS-AUDIT-FA.md` | ممیزی ایستای سورس و مسیرها انجام شده؛ خود سند صریحاً اجرای MariaDB/مرورگر واقعی را ادعا نمی‌کند |
| نقشه مسیرها | `docs/system-map/ROUTES.tsv` | نقشه موجود است؛ 416 خط در نسخه شاخه بررسی‌شده؛ نقشه باید با مبنای As-Is و تغییرات شاخه تفکیک شود |
| ارزیابی Dump | `REQUEST-CENTER-DATABASE-DUMP-ASSESSMENT-FA.md` | 82 جدول، 28 دستور INSERT و حدود 17,724 ردیف؛ داده عملیاتی tickets/messages/events/attachments ندارد |
| ممیزی DDL | `REQUEST-CENTER-T04-WEB-DDL-AUDIT-FA.md` | نتیجه صریح: T04 کل سامانه باز و از نظر ایستای سیاست صفر DDL مردود است |
| برگه T12 | `REQUEST-CENTER-T12-CHARACTERIZATION-WORKSHEET-FA.md` | ده گروه سناریو تعریف شده؛ اجرای Legacy/هدف روی دو Clone انجام نشده و T12 مسدود است |
| ماتریس To-Be | مصوبات معماری 1.14 و ماتریس اجرایی مصوب کاربر | لازم است هر ردیف ماتریس به فایل/تابع/جدول/آزمون و وضعیت شواهد این دفتر نگاشت شود؛ وجود سند به‌تنهایی اثبات تطبیق کامل نیست |

### ۲.۱. تثبیت مبنای کد و اعتبار CI نسبت به HEAD فعلی

- Commit مبنای As-Is یعنی `ab073ee9fffc56ccd1a2093d02725b95891e0d63` ancestor شاخه `main` است؛ `main` در مقایسه ۱۳ commit جلوتر و صفر commit عقب‌تر است.
- فهرست تغییرات این ۱۳ commit فقط شامل workflow/AGENTS، مستندات، system-map، تست fresh-install و ابزارهای ساخت نقشه است؛ فایل اجرایی PHP برنامه در این فاصله تغییر نکرده است. بنابراین مبنای As-Is از نظر سورس اجرایی همچنان با main هم‌خوان است.
- اجرای کامل‌تر Request Center Contract روی commit آزمون `82ef7c1978c53a86ac31a5568d5e46417c42ede2` و سپس تکرار آن روی HEAD مستنداتی `16217e9e5597d64f4357df9bdc6711fcf9ed2083` با Workflow `42344188512` روی MariaDB 10.4.32 و 10.11 موفق شد. تغییرات اجرایی این پیگیری فقط در فایل آزمون `tests/test_request_center_jalali_parity.mjs` بوده‌اند؛ هیچ کد اجرایی PHP، Schema یا Migration تغییر نکرده است.

## ۳. یافته‌های قطعی ممیزی فنی

### ۳.۱. مسیرهای DDL در درخواست وب — T04

1. در `index.php:29`، تابع `ticket_number_ensure_schema()` پیش از پردازش صفحه و عملیات فراخوانی می‌شود.
2. در `lib/tickets-sla.php`، این مسیر به `ticket_number_ensure_schema_run()` و `ticket_hot_schema_ensure()` می‌رسد.
3. `ticket_hot_schema_ensure()` می‌تواند DDL روی `tickets` و `users` اجرا کند و به `org_ensure_schema()`، `org_ticket_schema_ensure()` و `lac_ensure_schema()` شاخه می‌دهد.
4. `ticket_service_ensure_schema()` در مسیرهای Legacy فرم/ایجاد درخواست نیز فراخوانی می‌شود؛ تعریف آن در `lib/logging-csrf.php` است.
5. در `index.php` مسیرهای دیگری مانند `asset_profile_ensure_schema()` و `domain_scan_ensure_schema()` نیز وجود دارند. مالکیت این مسیرها خارج از Request Center است و طبق ADR-12 و ADR-19 حق اصلاح آن‌ها در این پروژه وجود ندارد.
6. ورودی مستقل `request-center.php` به‌صورت ایستا DDL اجرا نمی‌کند و `RequestCenterRepository::schemaProblems()` را برای preflight خواندنی به‌کار می‌برد؛ این امتیاز، مسیرهای Legacy را پاک نمی‌کند.

**نتیجه:** T04 در مسیر مستقل RC از نظر ایستا و آزمون HTTP با MariaDB محدوداً PASS است: روی Schema سالم HTTP 200 و صفر DDL؛ روی ستون الزامی مفقود HTTP 503 و صفر DDL. مسیر Legacy در آزمون HTTP پنج دستور DDL و پنج DML روی Schema تازه اجرا کرد و در Schema ناقص نیز ستون را ترمیم کرد؛ بنابراین T04 کل سامانه/Legacy **FAIL** است. حذف DDLهای متعلق به ماژول‌های دیگر خارج از مجوز این پروژه است و نباید انجام شود.

### ۳.۲. نقاط نوشتن و گذار وضعیت

در شاخه فعلی، پیاده‌سازی Actionها در `modules/request-center/Actions/` قرار گرفته و فایل‌های قدیمی `actions/*.php` به Bridge تبدیل شده‌اند. بررسی سورس فعلی نشان می‌دهد:

- ایجاد اولیه درخواست هنوز INSERT جداگانه در `tickets` است و به‌عنوان transition در WorkflowService پیاده‌سازی نشده است.
- ثبت/رد درخواست لغو، metadata را مستقیماً به‌روزرسانی می‌کند؛ این عملیات با transition وضعیت یکی نیست.
- گذارهای لغو قطعی، Claim، بازگشایی، پاسخ/تغییر وضعیت، ویرایش دارای تغییر وضعیت، تصمیم‌های سوپروایزر، عملیات گروهی و auto-close از `RequestCenterWorkflowService::transition()` استفاده می‌کنند.
- `WorkflowService` نوشتن status را با UPDATE دارای شرط وضعیت انجام می‌دهد؛ Handler همچنان مسئول مجوز، Audit/Event، اعلان و سایر side effectهاست.
- `cron/ticket_auto_close.php` نیز WorkflowService را فراخوانی می‌کند، ولی پیش از آن `ticket_hot_schema_ensure()` را فراخوانی می‌کند؛ اثر این مسیر باید در ممیزی DDL/اجرای CLI جداگانه ثبت شود.
- تابع شماره‌گذاری در `lib/tickets-sla.php` مسیر نوشتن `ticket_no` را دارد و در شمارش نقاط نوشتن نباید با transition وضعیت اشتباه شود.

برگه T12 ده گروه سناریو دارد؛ برخی گروه‌ها چند عملیات مستقل را پوشش می‌دهند. در نتیجه شمارش گروه‌های سناریو با تعداد Action/نقطه نوشتن برابر نیست. دفتر نهایی باید برای هر فرمان واقعی یک شناسه مستقل داشته باشد و هیچ مسیر نوشتن/تغییر status را صرفاً به‌دلیل قرارگرفتن در یک ردیف تجمیعی حذف نکند.

### ۳.۳. پیمایش اولیه همه فایل‌های PHP اجرایی

درخت Git شاخه بررسی شد و ۱۰۱ فایل PHP اجرایی (ریشه، Actions، API، Cron، Library و Module؛ بدون پوشه‌های tests/tools و آرشیو updates) از نظر ارجاع SQL به جدول‌های چرخه درخواست و فراخوانی توابع ensure پیمایش شدند. این یک جست‌وجوی ایستای متنی است؛ برای queryهای چندخطی/پویا و مسیرهای ساخته‌شده در زمان اجرا هنوز بازبینی معنایی لازم است.

یافته‌های مستقیم:

- تنها INSERT مستقیم به جدول `tickets` در مسیر `create_ticket` داخل `modules/request-center/Actions/ticket-actions.php` است.
- UPDATEهای metadata مستقیم در همان Handler برای ثبت درخواست لغو و رد درخواست لغو باقی مانده‌اند؛ این‌ها status را تغییر نمی‌دهند.
- UPDATE متمرکز status در `modules/request-center/WorkflowService.php:174` انجام می‌شود.
- UPDATE مستقیم `ticket_no` در `lib/tickets-sla.php:393` متعلق به شماره‌گذاری/تکمیل داده قدیمی است، نه transition وضعیت.
- `lib/activity-audit.php` و `lib/logging-replay.php` در جدول مشترک `ticket_events` رویداد درج می‌کنند؛ این مسیرها مالک تغییر status در `tickets` نیستند.
- `cron/sla_maintenance.php` فقط درخواست‌ها را برای هشدار SLA/OLA می‌خواند و در جداول هشدار درج می‌کند؛ با این حال قبل از کنترل CLI، `ticket_hot_schema_ensure()` را فراخوانی می‌کند.
- مصرف‌کنندگان خواندنی مهم شامل `governance.php`، `chatbot.php`، `lib/auth-scope.php`، `lib/notify-users.php`، `system-health.php` و `modules/request-center/RequestCenterRepository.php` هستند. این مصرف‌کنندگان باید در رگرسیون visibility، scope و side effect پوشش داده شوند.

**نتیجهٔ این پیمایش:** در جست‌وجوی ایستای ۱۰۱ فایل، نویسندهٔ مستقیم دیگری برای ستون `tickets.status` بیرون از WorkflowService و مسیرهای metadata/شماره‌گذاری فوق مشاهده نشد. این نتیجه جایگزین بازبینی queryهای چندخطی/پویا، SQL داخل داده‌ها، consumerهای غیر-PHP یا اجرای runtime نیست.

### ۳.۴. اثر جانبی داده‌ای در مسیر وب و ریسک پنهان DDL

دو مسیر مهم فراتر از صرفاً وجود DDL شناسایی شد:

1. `index.php:29-30` در هر درخواست ابتدا `ticket_number_ensure_schema()` و سپس `ticket_number_autofill()` را صدا می‌زند. اگر نشانگر `ticket_autofill_done` ثبت نشده باشد، `ticket_number_autofill()` می‌تواند در همان درخواست وب تا ۵۰ batch با حداکثر ۵۰۰ ردیف در هر batch را برای پرکردن `tickets.ticket_no` به‌روزرسانی کند. این یک backfill داده‌ای در مسیر وب است و باید از نظر حجم، زمان اجرا، رقابت با ثبت هم‌زمان و اثر بر درخواست ورود/صف مشخصه‌نگاری شود.
2. در `lib/tickets-sla.php`، `ticket_hot_schema_ensure()` پرچم static `$ready` را پیش از اجرای ALTERها true می‌کند و خطاهای داخل بلوک DDL را با `catch (Throwable) { }` بی‌صدا می‌بلعد. در نتیجه یک اجرای ناقص می‌تواند در همان درخواست به‌عنوان آماده علامت بخورد، بدون اینکه نتیجهٔ post-check ثبت شود. این مورد ریسک partial schema و observability است؛ در فاز ممیزی فقط ثبت می‌شود و اصلاح اجرایی آن منوط به طراحی/مجوز و آزمون است.
3. `ticket_service_ensure_schema()` در `lib/logging-csrf.php` علاوه بر ALTER روی `categories` و `service_catalog`، seed دسته‌ها و backfill `service_catalog.category_id` انجام می‌دهد. این تابع از مسیر Legacy ایجاد/فرم درخواست فراخوانی می‌شود؛ پس باید به‌عنوان DDL + DML زمان اجرا در خط مبنا ثبت شود.
4. در `cron/ticket_auto_close.php` و `cron/sla_maintenance.php`، `ticket_hot_schema_ensure()` پیش از شرط `PHP_SAPI === 'cli'` فراخوانی می‌شود. مخزن برای این مسیر `cron/.htaccess` با دستور `Require all denied` دارد؛ بنابراین در Apacheی که `AllowOverride` و مجوزهای این فایل را اعمال می‌کند، مسیر HTTP مسدود می‌شود. مؤثر بودن این حفاظت روی XAMPP نصب‌شده هنوز با درخواست HTTP واقعی آزموده نشده است؛ تا آن زمان وضعیت «نیازمند آزمون تنظیم Apache» است، نه اثبات دسترسی‌پذیری یا مسدودبودن.

آزمون runtime در Workflow `42342168721` روی MariaDB 10.4.32 و 10.11 این موارد را اثبات کرد:
- `index.php` روی Schema نصب تازه: پنج دستور DDL و پنج DML؛ DDLها شامل دو CREATE TABLE IF NOT EXISTS و سه ALTER ... ADD COLUMN IF NOT EXISTS برای سازمان هستند.
- همان درخواست، یک `UPDATE tickets.ticket_no` روی ردیف fixture و درج `ticket_number_seq` و نشانگر `ticket_autofill_done` انجام داد؛ پس backfill شماره‌گذاری واقعاً در درخواست وب اجرا می‌شود.
- ورودی مستقل `request-center.php` با نشست مدیر اصلی روی Schema سالم HTTP 200 و صفر DDL داد.
- پس از حذف `tickets.requesting_unit_id`، ورودی مستقل HTTP 503 و صفر DDL داد و ستون را دست‌نخورده گذاشت؛ Legacy `index.php` همان ستون را با ALTER بازسازی کرد.
- فراخوانی HTTP تابع `ticket_service_ensure_schema()`، صفر DDL و ۴۵ DML ثبت کرد: ۱۷ UPSERT دسته و ۲۸ UPDATE روی `service_catalog.category_id`.

بنابراین T04 برای ورودی مستقل RC به‌صورت runtime محدود PASS است؛ مسیر Legacy به‌صورت runtime FAIL است. آزمون Apache واقعی برای اثربخشی `cron/.htaccess` هنوز انجام نشده است. هیچ‌کدام از این مسیرها در این مرحله اصلاح نشده‌اند.

## ۴. وضعیت T01 تا T12 در زمان ثبت

| گیت | موضوع | وضعیت مستند فعلی |
|---|---|---|
| T01 | DDL داخل تراکنش و ROLLBACK | **PASS در CI روی MariaDB 10.4.32 و 10.11**؛ اجرای Probe روی DB دورریختنی |
| T02 | CREATE TABLE IF NOT EXISTS داخل تراکنش | **PASS در CI روی MariaDB 10.4.32 و 10.11**؛ implicit commit اثبات شد |
| T03 | ALTER ... ADD COLUMN IF NOT EXISTS | **PASS در CI روی MariaDB 10.4.32 و 10.11**؛ syntax، تکرارپذیری و حفظ داده Probe تأیید شد |
| T04 | DDL در درخواست وب | **RC مستقل: PASS محدود runtime؛ Legacy index.php: FAIL runtime (5 DDL + 5 DML روی Schema تازه و ترمیم DDL روی Schema ناقص)** |
| T05 | شکست عمدی وسط Migration از CLI | **PASS روی MariaDB 10.4.32 و 10.11**؛ CREATE/DML قبلی پس از خطا باقی می‌ماند و Migration ناموفق ثبت نمی‌شود |
| T06 | قطع فرایند بین SQL و ثبت رجیستری | **PASS به‌صورت fault-injection در نقطه INSERT رجیستری روی هر دو نسخه**؛ SQL/DML باقی می‌ماند و Migration اعمال‌شده گزارش نمی‌شود؛ reconciliation واقعی پس از قطع فرایند هنوز مکمل لازم دارد |
| T07 | اجرای مجدد Migrationهای 001 تا 033 | **باز**؛ CI نصب تازه را دوبار migrate می‌کند، اما اجرای روی Clone کاملِ Dump با fingerprint قبل/بعد هنوز انجام نشده |
| T08 | Restore واقعی DB و فایل‌ها | **PASS روی MariaDB 10.4.32 و 10.11**؛ آزمون end-to-end بازیابی DB، uploads و system-files در Workflow `42343952546` موفق |
| T09 | ALTER هم‌زمان با تراکنش بلند | **PASS روی MariaDB 10.4.32 و 10.11**؛ آزمون metadata-lock timeout موفق |
| T10 | 50 Claim هم‌زمان | **PASS روی MariaDB 10.4.32 و 10.11**؛ ۵۰ worker مستقل، دقیقاً یک Claim موفق و ۴۹ بازنده |
| T11 | تطبیق تاریخ PHP و ItsmJalali | **PASS روی MariaDB 10.4.32 و 10.11**؛ همه روزهای معتبر سال‌های 1300 تا 1500، رفت‌وبرگشت و ورودی ارقام فارسی آزموده شدند |
| T12 | Characterization رفتار Legacy در برابر شاخه هدف | **BLOCKED؛ دو Clone هم‌ساخت، fixture و Snapshot مقایسه‌ای موجود نیست** |

### ۴.۱. شاهد جدید T01 تا T03

اجرای موفق Workflow با شناسه `42340253824` و Job شناسه `128306122728` روی سرویس دورریختنی `mariadb:10.4.32` ثبت شد. خروجی `SELECT VERSION()` برابر `10.4.32-MariaDB-1:10.4.32+maria~ubu2004` بود. هر سه آزمون زیر PASS شدند:

- T01: ایجاد جدول داخل تراکنش، DML قبلی را implicit commit می‌کند و ROLLBACK آن را برنمی‌گرداند.
- T02: `CREATE TABLE IF NOT EXISTS` نیز implicit commit دارد؛ عبارت IF NOT EXISTS آن را transactional نمی‌کند.
- T03: `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` روی این نسخه parse و دوباره اجرا شد و داده Probe حفظ شد.

Job متناظر MariaDB 10.11 نیز موفق شد. این آزمون‌ها فقط جدول‌های Probe در دیتابیس disposable را ایجاد/حذف کردند و روی Dump یا DB عملیاتی اجرا نشدند. نتیجه، T01–T03 را می‌بندد؛ نه T04 یا آزمون‌های Migration/Restore/Workflow را.

### ۴.۲. حدود پوشش آزمون‌های موفق MariaDB فعلی

در همان Workflow، این آزمون‌ها نیز روی MariaDB 10.4.32 موفق شده‌اند:
- Schema preflight روی `install-empty-db.sql` با fixtureهای مصنوعی requester/agent/ticket/message/attachment و یک transition واقعی؛ 33 assertion در این مرحله موفق شد.
- آزمون Repository و فیلتر scope، پیام داخلی و پیوست با دیتابیس موقت.
- آزمون WorkflowService با MariaDB؛ اما فایل `tests/test_request_center_workflow_service_mariadb.php` یک `TEMPORARY TABLE tickets` مختص همان اتصال می‌سازد. این آزمون پذیرش SQL `FOR UPDATE`، شرط وضعیت، allowlist و rollback همان اتصال را اثبات می‌کند؛ دو اتصال/worker رقابتی ایجاد نمی‌کند.
- آزمون SQLite نیز guardهای سیاست را پوشش می‌دهد؛ SQLite جایگزین رفتار lock و metadata-lock در MariaDB نیست.

بنابراین این نتایج، شواهد معتبر برای سازگاری syntax و یکپارچگی محدود Repository/WorkflowService در MariaDB 10.4.32 هستند؛ اما T09 (metadata lock با تراکنش بلند)، T10 (۵۰ Claim هم‌زمان با workerهای مستقل) و T12 (مقایسه Legacy و شاخه هدف) را PASS نمی‌کنند.

### ۴.۱. الزامات آزمون Migration که هنوز باید بسته شوند

پیش از هر اجرای تغییردهنده روی Clone، نسخه دقیق DB با `SELECT VERSION()`، Schema فعال با `SELECT DATABASE()`، Engine جدول‌های درگیر و fingerprint ثبت شود. Migrationهای زیر نیازمند بازبینی دستی و post-check اختصاصی‌اند: `003`، `005`، `011`، `012`، `026`، `032` و `033`. نبود رکورد رجیستری 032 در Dump، به‌تنهایی اثبات اجرا نشدن SQL نیست. اجرای کور یا retry بدون reconciliation ممنوع است.

### ۴.۳. بازبینی دستی Migrationهای پرریسک درخواستی

هفت فایل زیر از نظر SQL و اثر داده‌ای بازبینی شدند. هیچ‌کدام در این ممیزی اجرا یا ویرایش نشده‌اند.

| Migration | یافتهٔ دقیق | دامنه/ریسک | نتیجه ممیزی |
|---|---|---|---|
| `003-sync-schema-151.sql` | ۱۷۷۱ خط؛ ۶۷ دستور CREATE TABLE، ۶۸۱ دستور ALTER TABLE و یک UPDATE. از جمله ۴۲ ALTER روی `tickets`، شش روی `ticket_messages`، هفت روی `ticket_ratings`، هفت روی `ticket_attachments`، هشت روی `ticket_events`، شانزده روی `service_catalog` و شش روی `categories`. علاوه بر RC، تعداد زیادی جدول/ستون غذا، تردد، CD/DVD، دارایی، سازمان، Logging و Chatbot را تغییر می‌دهد. در بخش تیکت، ENUM وضعیت در خط ۱۲۲۲ MODIFY می‌شود و در خط ۱۱۹۲ یک backfill روی `service_catalog.requires_asset` وجود دارد. | بسیار پرریسک و چنددامنه‌ای؛ تعدادی ADD COLUMN از نوع NOT NULL بدون DEFAULT در جدول‌های پرشده، و MODIFY ENUM می‌تواند به SQL mode و داده قدیمی حساس باشد. اجرای موفق دوباره روی fresh schema، رفتار روی DB قدیمی/پرشده را اثبات نمی‌کند. | بازبینی ایستای فایل انجام شد؛ آزمون upgrade روی Clone و بررسی تک‌تک ستون‌های NOT NULL/ENUM لازم است. خارج از محدوده، نباید اصلاح شود. |
| `005-normalize-pending-status.sql` | همهٔ `tickets.status='pending'` را به `waiting_user` تغییر می‌دهد و `updated_at` را به NOW() می‌زند؛ هیچ `ticket_events` متناظری ثبت نمی‌کند. | تغییر داده و معنای وضعیت تاریخی؛ زمان آخرین تغییر را بازنویسی می‌کند، حتی اگر درخواست قدیمی باشد. | باید تعداد ردیف‌های قبل/بعد، وضعیت SLA/OLA و گزارش‌های تاریخی در Clone ثبت شود؛ idempotent بودن به‌تنهایی کافی نیست. |
| `011-manual-attendance-delivery.sql` | فقط `food_ticket_events` مربوط به `source_uid='manual-attendance'`، `event_type='printed'` و `print_status='printed'` را delivered می‌کند. | صرفاً ماژول غذا؛ خارج از دامنه RC. | رفتار شرط‌ها قابل فهم است؛ هیچ تغییری در این پروژه مجاز نیست. |
| `012-manual-attendance-clears-absences.sql` | جدول `food_ticket_daily_absence` را در صورت نبود می‌سازد و سپس با JOIN، ردیف‌های غیبت متناقض با رویداد دستی `printed/no_food` را DELETE می‌کند. | حذف داده در ماژول غذا؛ اجرای مجدد ممکن است بی‌اثر باشد، اما حذف برگشت‌پذیر نیست مگر با backup. | قبل از اجرای هرگز باید تعداد DELETE و نمونه تطبیق روی Clone ثبت شود؛ خارج از دامنه RC و بدون مجوز اجرا نمی‌شود. |
| `026-logging-legacy-result-unknown.sql` | نتیجه‌های قدیمی activity_logs را از success به unknown تغییر می‌دهد؛ DDL ستون `log_issues.context` را به‌شکل dynamic SQL با PREPARE/EXECUTE انجام می‌دهد؛ دو backfill در log_issues/log_issue_events دارد و سه جدول Logging می‌سازد. | Migration چندمرحله‌ای Logging؛ جست‌وجوی ساده‌ای که فقط خطوط ALTER را می‌بیند، DDL پویا را از دست می‌دهد. | خارج از دامنه RC؛ تغییر/اجرای مجدد آن در این پروژه ممنوع. |
| `032-schema-updates-baseline.sql` | با INSERT IGNORE، ۳۲ نام Migration از 001 تا 032 را با version برابر 1.62.83 در `schema_updates` ثبت می‌کند. این فایل در بستهٔ 1.62.83 overlay شده بود، اما `tools/build_update_1_62_88.py` آن را عمداً از بستهٔ 1.62.88 حذف می‌کند؛ گزارش 1.62.88 دلیل را خطر baseline کاذب با updater مبتنی بر نام فایل می‌داند. بنابراین نبود 032 در Dump فعلی مورد انتظار است، نه نشانهٔ قطعی Migration ناقص. توضیح فایل، اجرای تاریخی را از روی نسخه نصب‌شده معتبر فرض می‌کند و schema/data هر Migration را مستقل راستی‌آزمایی نمی‌کند. | Bootstrap رجیستری بر پایه ادعای نسخه؛ در دیتابیسی با اجرای ناقص قبلی ممکن است فایل را «اعمال‌شده» نشان دهد، بی‌آنکه اثر واقعی SQL اثبات شده باشد. | باید همراه base version/hash، snapshot ساختار و post-checkها بررسی شود؛ صرف وجود رجیستری مدرک برابری Schema نیست. |
| `033-logging-legacy-context-collation.sql` | فقط context رکوردهای `log_issues` را از `system_logs` با JOIN باینری روی event_id backfill می‌کند؛ برای DBهایی است که 026 را پیش از اصلاح collation ثبت کرده‌اند. | فقط Logging؛ خارج از دامنه RC. | فایل forward-fix است؛ دست‌کاری آن در این پروژه ممنوع. |

**یافتهٔ رجیستری:** `schema_updates` در fresh-install دارای UNIQUE KEY روی `(version, migration)` است؛ اما `system_update_migration_applied()` در `system-update.php:36-42` فقط نام فایل Migration را جست‌وجو می‌کند و ستون hash/checksum ندارد. بنابراین سامانه فعلی تغییر محتوای یک فایل تاریخی با نام ثابت را از روی رجیستری تشخیص نمی‌دهد. این یک شکاف ثبت اصالت Migration است و اصلاح updater عمومی خارج از محدوده RC و نیازمند تصمیم مستقل است.

**حد آزمون CI:** workflow عمومی `CI` تمام Migrationهای forward را روی fresh-install schema دوبار اجرا می‌کند و سپس رجیستری را بررسی می‌کند. این آزمون برای idempotency روی نصب تازه مفید است؛ اما جای اجرای ارتقا روی Dump/Clone واقعی، شمارش ردیف‌های تغییرکرده در 005/011/012، و سناریوی شکست/قطع وسط 003/026 را نمی‌گیرد. Migrationهای 011/012/026/033 متعلق به ماژول‌های دیگرند و در این پروژه نباید برای رفع مشکل RC تغییر کنند.

### ۴.۴. تطبیق مستقیم Dump ارائه‌شده با رجیستری Migrationها

Dump موجود مستقیماً از Git blob خوانده و فقط ساختار/رجیستری آن استخراج شد؛ محتوای حساس ردیف‌ها در گزارش بازنشر نمی‌شود.

| شاهد | نتیجه |
|---|---|
| مسیر | `work/hardening-audit-1.62.82/persian_ticketing.sql` |
| Git blob SHA | `99658e87946ba79359dc825bf1c600d39a52d10e` |
| اندازه | 8,244,183 بایت؛ 3,041 خط |
| جدول‌ها | 82 دستور CREATE TABLE |
| داده | 28 دستور INSERT و حدود 17,724 ردیف |
| رجیستری | 26 ردیف در `schema_updates` |
| داده عملیاتی درخواست | صفر INSERT برای `tickets`، `ticket_messages`، `ticket_events` و `ticket_attachments` |

محتوای رجیستری:
- نسخه‌های 1.62.76 و 1.62.77: مهاجرت‌های 024 تا 030
- نسخه‌های 1.62.79، 1.62.80 و 1.62.82: مهاجرت 031
- نسخهٔ 1.62.88: مهاجرت‌های 024 تا 031 و 033
- مهاجرت 032 در رجیستری وجود ندارد؛ بررسی سازنده و گزارش انتشار 1.62.88 نشان می‌دهد این حذف عمدی است، چون بستهٔ 1.62.88 آن را وارد ZIP نمی‌کند.

**عدم تطبیق baseline:** فایل `VERSION` در همان شاخهٔ Git با نام `work/hardening-audit-1.62.82` مقدار `1.62.83` دارد، درحالی‌که رجیستری خود Dump ردیف‌های نسخهٔ 1.62.88 دارد. پس نام شاخه و فایل VERSION مبنای قابل اتکایی برای تعیین نسخهٔ runtime دیتابیس نیستند؛ این Dump برای شناخت Schema و رجیستری مفید است، اما زوج دقیق «کد 1.62.82 + DB 1.62.82» برای T12 نیست.

**نتیجه T12:** Dump به‌دلیل نداشتن ردیف‌های عملیاتی tickets/messages/events/attachments امکان replay رفتار واقعی را فراهم نمی‌کند. نیازی به درخواست مجدد همین فایل نیست؛ برای T12 باید دو Clone هم‌ساخت با fixtureهای مصنوعی کنترل‌شده ساخته شود و منبع دقیق Schema/base hash آن‌ها ثبت شود.

## ۵. خط مبنای CI و رگرسیون شاخه فعلی

وضعیت‌ها در commit کد/تست `0466b7c585f83fd0db0707d2bc4bd3172aa7df27` بررسی شده‌اند؛ پس از آن فقط مستندات تغییر کرده‌اند:

| Workflow | نتیجه | جزئیات |
|---|---|---|
| Request Center Contract | **PASS** | Run `42343952546`: هر دو Job MariaDB 10.4.32 و 10.11؛ T01–T06، T08–T11 موفق (T04 diagnostic موفق است اما سیاست صفر DDL در Legacy همچنان FAIL است) |
| Logging contract | **PASS** | Run `42344188597` موفق |
| CI | **FAIL** | Run `42344188568`: `tools/test_access_coverage_offline.php` پوشه `modules/request-center/Actions` را نمی‌پیماید و مجوزها/guardهای ماژول را به‌اشتباه بی‌استفاده یا بدون guard گزارش می‌کند |
| Logging 1.62.88 Acceptance | **FAIL** | Run `42344188563`: `tools/test_ticket_txn_offline.php` و `tests/test_logging_trace_coverage.php` هنوز بدنهٔ Ticket را فقط در مسیر Legacy می‌جویند؛ فایل‌های Logging/سایر ماژول‌ها برای رفع آن تغییر داده نشده‌اند |
| Refresh Request Center system map | FAIL | اکشن‌های `create_ticket` و `supervisor_decision` در index تولیدی ثبت نشده‌اند |
| Request Center QA Package Preview | FAIL | دو تست، فایل‌های `modules/request-center/Actions/*.php` را در overlay بسته پیدا نمی‌کنند |

جزئیات شکست‌های ثبت‌شده:

1. آزمون پوشش دسترسی، کدهای `supervisor.decide`، `supervisor.reopen`، `ticket.attach_asset` و `ticket.bulk` را بی‌استفاده تشخیص داده است؛ علت قابل مشاهده این است که پیمایش فایل‌های PHP آن فقط ریشه، `actions`، `api`، `cron`، `lib` و `tools` را پوشش می‌دهد و `modules/request-center/Actions` را نمی‌خواند.
2. همان آزمون فقط `actions/*.php` را برای وجود guard می‌سنجد؛ پس از تبدیل این فایل‌ها به Bridge، سه فایل `bulk-actions.php`، `supervisor-actions.php` و `ticket-actions.php` را بدون guard گزارش کرده است. در پیاده‌سازی ماژول، guardهای `APP_ROOT` و کنترل‌های ورود/مجوز در handlerها وجود دارند؛ آزمون فعلی مسیر جدید را بررسی نمی‌کند. این مورد فعلاً «شکست آزمون استاتیک به‌علت پوشش‌ندادن مسیر ماژول» ثبت می‌شود، نه اثبات نقص runtime و نه اثبات صحت runtime.
3. تست ویندوز `tools/test_ticket_txn_offline.php` هنوز بدنه `create_ticket` را از `actions/ticket-actions.php` می‌خواند؛ پس از انتقال پیاده‌سازی به `modules/request-center/Actions/ticket-actions.php` دو assertion مربوط به `db_commit_or_fail` و ساخت `ticketNo` شکست خورده‌اند.
4. تست `tests/test_logging_trace_coverage.php` مسیر `actions/ticket-actions.php` را برای وجود `log_ticket_event()` و `save_audit_required()` بررسی می‌کند و در شاخه فعلی شکست می‌خورد، چون پیاده‌سازی به مسیر ماژول منتقل شده است.

این شکست‌ها به جابه‌جایی فیزیکی Actionها در شاخه فعلی مرتبط‌اند: پیمایش access coverage و system-map پوشه ماژول را نمی‌خوانند؛ تست‌های transaction/trace هنوز مسیر قدیمی را فرض می‌کنند؛ QA overlay نیز فایل‌های ماژول را بسته‌بندی نمی‌کند. این‌ها نقص‌های واقعی خط مبنای شاخه‌اند، اما از روی شکست تست استاتیک به‌تنهایی نقص مجوز runtime اثبات نمی‌شود. تعیین تکلیف اصلاحات بسته‌بندی و system-map باید در دامنه مجاز Request Center انجام شود؛ هیچ فایل Logging یا ماژول نامرتبطی نباید برای سبزکردن CI تغییر کند. سبز بودن Request Center Contract جایگزین سبز بودن CI کامل و QA Package Preview نیست.

## ۶. موانع واقعی برای بستن فاز اول

1. T01 تا T03، T05 تا T06 و T08 تا T11 روی MariaDB 10.4.32 و 10.11 در Workflow `42343813940` موفق‌اند. T04 برای ورودی مستقل RC به‌صورت runtime محدود PASS و برای مسیر Legacy `index.php` به‌صورت runtime FAIL است؛ آزمون Apache/XAMPP و تعیین تکلیف اثر مسیرهای متعلق به سازمان/هسته هنوز باز است. T07 به اجرای Migrationها روی Clone کامل همراه fingerprint نیاز دارد و T12 همچنان BLOCKED است.
2. اجرای T12 روی دو Clone مستقل Legacy/feature با Schema و تنظیمات یکسان، fixtureهای نقش/مجوز/درخواست و Snapshot کامل status، تخصیص، SLA/OLA، پیام، Event، Audit، notification و asset history.
3. Dump موجود داده عملیاتی ticket ندارد. برای T12 باید fixture کنترل‌شده ساخته شود یا Clone نماینده‌ای که داده مجاز و غیرعملیاتی دارد فراهم شود. هیچ import یا تغییری روی DB عملیاتی انجام نمی‌شود.
4. تکمیل نگاشت As-Is به To-Be: هر صفحه، route، Action، consumer، تابع، جدول، مجوز، Cron و side effect باید شناسه و شاهد داشته باشد؛ هر ابهام باید آزمون پیشنهادی و مالک مشخص داشته باشد.
5. تکمیل ماتریس انتقال وضعیت و تطبیق آن با تمام نقاط نوشتن واقعی، شامل ایجاد، metadata درخواست لغو، لغو، Claim، پاسخ، ویرایش، سوپروایزر، bulk، auto-close و مصرف‌کنندگان بیرونی.
6. تکمیل ارزیابی روش پایه نسخه/Hash و رجیستری bootstrap Migrationها و مستندسازی رفتار خطا، timeout، lock، maintenance/drain، ظرفیت و بازیابی مطابق ADRهای 15 تا 23.
7. تعیین تکلیف اختلاف‌های CI ناشی از جابه‌جایی Actionها بدون تغییر دامنه و بدون دست‌کاری فایل‌های خارج از Request Center.

## ۷. مرز کار مجاز تا بسته‌شدن فاز اول

- مجاز: تکمیل ممیزی سورس/DB، نگاشت ماتریس‌ها، تکمیل مستندات شواهد، افزودن تست‌های مشخصه‌نگاری و harness آزمایشگاهی در محدوده RC، اجرای آزمون روی DB دورریختنی/Clone غیرعملیاتی.
- غیرمجاز: گسترش انتقال نوشتن‌ها، تغییر رفتار کاربر، تغییر Schema عملیاتی، اجرای Migration روی سامانه عملیاتی، تغییر `main`، تغییر Logging/غذا/تردد/CD/DVD/سازمان/هسته یا هر فایل نامرتبط.
- هر تغییر آزمایشگاهی باید در شاخه توسعه مستقل و با ثبت commit، فایل‌های متأثر و نتیجه CI انجام شود.
- PR #30 باید Open/Draft بماند؛ ادغام، آماده‌سازی انتشار و نصب عملیاتی ممنوع است.

## ۸. معیار بسته‌شدن فاز اول

فاز اول فقط وقتی قابل اعلام تکمیل است که:
1. گزارش As-Is کامل و تمام ردیف‌هایش با ارجاع دقیق به سورس/Schema راستی‌آزمایی شده باشند.
2. To-Be و ماتریس‌های نقش/Workspace، وضعیت، SLA/OLA، اعلان، داده و انتقال به‌صورت کامل با As-Is تطبیق داده شده باشند.
3. تمام مسیرهای نوشتن و مصرف‌کنندگان مستقیم/غیرمستقیم شناخته و تعیین تکلیف شده باشند.
4. T01 تا T12 دارای خروجی واقعی، ثبت‌شده و قابل تکرار باشند؛ هر استثنا باید تصمیم مصوب و محدودیت روشن داشته باشد.
5. baseline تست کامل و شکست‌های ناشی از شاخه تعیین تکلیف شده باشند.
6. سند نقشه انتقال، معیار پذیرش هر فاز و فهرست ریسک‌های باز به‌روز باشند.
7. هیچ اقدام عملیاتی یا تغییر خارج از دامنه انجام نشده باشد.

**نتیجه فعلی: فاز اول باز است. هیچ‌یک از موارد فوق به‌تنهایی یا با موفقیت Request Center Contract قابل جایگزینی نیست.**
