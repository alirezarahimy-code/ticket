# دفتر شواهد و موانع فاز اول پروژه ارتقای مرکز درخواست‌ها

| مشخصه | مقدار |
|---|---|
| شناسه | RC-PHASE-01-EVIDENCE-REGISTER |
| تاریخ ثبت | 2026-11-07 |
| مخزن | `alirezarahimy-code/ticket` |
| شاخه اجرایی | `feature/request-center-module-1.62.88` |
| Commit کد اجرایی بررسی‌شده | `e1f7f9b581a9005f3a6e08a8dbf5ad9244a5b3b1` |
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
- CI مربوط به Request Center روی commit `e1f7f9b581a9005f3a6e08a8dbf5ad9244a5b3b1` اجرا شد. مقایسه آن تا HEAD فعلی نشان می‌دهد ۷ commit بعدی فقط سه سند ممیزی/نگاشت را تغییر داده‌اند و هیچ کد، تست یا workflowی تغییر نکرده است؛ بنابراین نتایج CI برای سورس و تست فعلی همچنان قابل استنادند.

## ۳. یافته‌های قطعی ممیزی فنی

### ۳.۱. مسیرهای DDL در درخواست وب — T04

1. در `index.php:29`، تابع `ticket_number_ensure_schema()` پیش از پردازش صفحه و عملیات فراخوانی می‌شود.
2. در `lib/tickets-sla.php`، این مسیر به `ticket_number_ensure_schema_run()` و `ticket_hot_schema_ensure()` می‌رسد.
3. `ticket_hot_schema_ensure()` می‌تواند DDL روی `tickets` و `users` اجرا کند و به `org_ensure_schema()`، `org_ticket_schema_ensure()` و `lac_ensure_schema()` شاخه می‌دهد.
4. `ticket_service_ensure_schema()` در مسیرهای Legacy فرم/ایجاد درخواست نیز فراخوانی می‌شود؛ تعریف آن در `lib/logging-csrf.php` است.
5. در `index.php` مسیرهای دیگری مانند `asset_profile_ensure_schema()` و `domain_scan_ensure_schema()` نیز وجود دارند. مالکیت این مسیرها خارج از Request Center است و طبق ADR-12 و ADR-19 حق اصلاح آن‌ها در این پروژه وجود ندارد.
6. ورودی مستقل `request-center.php` به‌صورت ایستا DDL اجرا نمی‌کند و `RequestCenterRepository::schemaProblems()` را برای preflight خواندنی به‌کار می‌برد؛ این امتیاز، مسیرهای Legacy را پاک نمی‌کند.

**نتیجه:** T04 در مسیر مستقل RC از نظر ایستا محدوداً PASS است؛ مسیرهای Legacy مرتبط با درخواست FAIL ایستا دارند؛ شمارش واقعی SQL/DDL با MariaDB general log انجام نشده است. T04 کل سامانه بسته نیست. حذف DDLهای متعلق به ماژول‌های دیگر خارج از مجوز این پروژه است و نباید انجام شود.

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

این یافته‌ها نشان می‌دهد T04 صرفاً «تعداد ALTERها» نیست: backfill شماره‌گذاری، seed/backfill کاتالوگ و بلع خطای DDL نیز باید در سناریوهای آزمون ثبت شوند. هیچ‌کدام در این مرحله تغییر داده نشده‌اند.

## ۴. وضعیت T01 تا T12 در زمان ثبت

| گیت | موضوع | وضعیت مستند فعلی |
|---|---|---|
| T01 | DDL داخل تراکنش و ROLLBACK | **PASS در CI روی MariaDB 10.4.32 و 10.11**؛ اجرای Probe روی DB دورریختنی |
| T02 | CREATE TABLE IF NOT EXISTS داخل تراکنش | **PASS در CI روی MariaDB 10.4.32 و 10.11**؛ implicit commit اثبات شد |
| T03 | ALTER ... ADD COLUMN IF NOT EXISTS | **PASS در CI روی MariaDB 10.4.32 و 10.11**؛ syntax، تکرارپذیری و حفظ داده Probe تأیید شد |
| T04 | DDL در درخواست وب | **FAIL ایستای مسیر Legacy؛ runtime count انجام نشده؛ باز** |
| T05 | شکست عمدی وسط Migration از CLI | نیازمند Clone، تزریق خطا و post-check |
| T06 | قطع فرایند بین SQL و ثبت رجیستری | نیازمند آزمون قطع/بازیابی و reconciliation |
| T07 | اجرای مجدد Migrationهای 001 تا 033 | نیازمند Clone کامل، fingerprint و بازبینی دستی Migrationهای پرریسک |
| T08 | Restore واقعی DB و فایل‌ها | اجرا و تطبیق کامل Restore ثبت نشده |
| T09 | ALTER هم‌زمان با تراکنش بلند | نیازمند آزمون lock/timeout روی نسخه هدف |
| T10 | 50 Claim هم‌زمان | آزمون هم‌زمانی واقعی ثبت نشده |
| T11 | تطبیق تاریخ PHP و ItsmJalali | نتیجه آزمون دامنه سال‌های 1300 تا 1500 در شواهد فعلی ثبت نشده |
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

## ۵. خط مبنای CI و رگرسیون شاخه فعلی

وضعیت‌ها در commit `e1f7f9b581a9005f3a6e08a8dbf5ad9244a5b3b1` بررسی شده‌اند:

| Workflow | نتیجه | جزئیات |
|---|---|---|
| Request Center Contract | PASS | اجرای قرارداد اختصاصی موفق |
| Logging contract | PASS | اجرای قرارداد اختصاصی موفق |
| CI | FAIL | `tools/test_access_coverage_offline.php`: دو شکست |
| Logging 1.62.88 Acceptance | FAIL | یک شکست در job ویندوز و یک شکست در job لینوکس |

جزئیات شکست‌های ثبت‌شده:

1. آزمون پوشش دسترسی، کدهای `supervisor.decide`، `supervisor.reopen`، `ticket.attach_asset` و `ticket.bulk` را بی‌استفاده تشخیص داده است؛ علت قابل مشاهده این است که پیمایش فایل‌های PHP آن فقط ریشه، `actions`، `api`، `cron`، `lib` و `tools` را پوشش می‌دهد و `modules/request-center/Actions` را نمی‌خواند.
2. همان آزمون فقط `actions/*.php` را برای وجود guard می‌سنجد؛ پس از تبدیل این فایل‌ها به Bridge، سه فایل `bulk-actions.php`، `supervisor-actions.php` و `ticket-actions.php` را بدون guard گزارش کرده است. در پیاده‌سازی ماژول، guardهای `APP_ROOT` و کنترل‌های ورود/مجوز در handlerها وجود دارند؛ آزمون فعلی مسیر جدید را بررسی نمی‌کند. این مورد فعلاً «شکست آزمون استاتیک به‌علت پوشش‌ندادن مسیر ماژول» ثبت می‌شود، نه اثبات نقص runtime و نه اثبات صحت runtime.
3. تست ویندوز `tools/test_ticket_txn_offline.php` هنوز بدنه `create_ticket` را از `actions/ticket-actions.php` می‌خواند؛ پس از انتقال پیاده‌سازی به `modules/request-center/Actions/ticket-actions.php` دو assertion مربوط به `db_commit_or_fail` و ساخت `ticketNo` شکست خورده‌اند.
4. تست `tests/test_logging_trace_coverage.php` مسیر `actions/ticket-actions.php` را برای وجود `log_ticket_event()` و `save_audit_required()` بررسی می‌کند و در شاخه فعلی شکست می‌خورد، چون پیاده‌سازی به مسیر ماژول منتقل شده است.

این شکست‌ها به تغییرات جابه‌جایی فیزیکی Actionها در شاخه فعلی مرتبط‌اند و باید در مسیر مجاز پروژه/آزمون‌های مصرف‌کننده تعیین تکلیف شوند. هیچ فایل Logging یا ماژول نامرتبطی نباید برای سبزکردن CI در این پروژه تغییر کند. سبز بودن دو workflow اختصاصی RC، جایگزین سبز بودن CI کامل نیست.

## ۶. موانع واقعی برای بستن فاز اول

1. T01 تا T03 در CI روی MariaDB 10.4.32 و 10.11 موفق‌اند. گیت‌های T04 تا T11 که نیازمند آزمون‌های جداگانه‌اند هنوز باید اجرا و مستند شوند؛ آزمون‌های CI فعلی جای آن‌ها را نمی‌گیرند.
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
