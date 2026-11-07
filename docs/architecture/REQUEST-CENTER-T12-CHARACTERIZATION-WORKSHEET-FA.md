# برگه اجرایی آزمون مشخصه‌نگاری T12 ـ مرکز درخواست‌ها

| مشخصه | مقدار |
|---|---|
| شناسه | RC-T12-CHAR-01 |
| وضعیت | آماده‌سازی مستند؛ اجرای رفتاری هنوز انجام نشده |
| مبنای Legacy | `ab073ee9fffc56ccd1a2093d02725b95891e0d63` |
| شاخهٔ هدف | `feature/request-center-module-1.62.88` |
| commit کد هدف برای اجرا | `82ef7c1978c53a86ac31a5568d5e46417c42ede2` |
| نسخهٔ MariaDB هدف | 10.4.32 |
| محدوده | فقط رفتارهای چرخهٔ عمر Request Center؛ بدون تغییر کد اجرایی |

> این برگه ابزار برنامه‌ریزی و ثبت شواهد است، نه گزارش PASS. هیچ آزمون تغییردهنده‌ای نباید روی پایگاه‌دادهٔ عملیاتی اجرا شود. اجرای T12 فقط روی Clone غیرعملیاتی با Schema و نسخهٔ سازگار مجاز است.

## ۱. هدف و قاعدهٔ پذیرش

برای هر سناریو، رفتار نسخهٔ مبنا و رفتار شاخهٔ هدف باید با ورودی و وضعیت اولیهٔ معادل اجرا و مقایسه شود. معیار مقایسه فقط ستون `status` نیست؛ مقادیر SLA/OLA، تخصیص، تاریخچه، پیام‌ها، اعلان‌ها و اثرات جانبی نیز باید ثبت شوند.

**هیچ تفاوتی به‌صورت خودکار خطا یا مجاز تلقی نمی‌شود.** هر اختلاف باید به قاعدهٔ مصوب To-Be یا تصمیم صریح محصول ارجاع داده شود. اختلاف توضیح‌داده‌نشده = FAIL/نیازمند تصمیم، نه PASS.

## ۲. ده سناریوی اجرایی

| # | سناریو | مسیر Legacy در commit مبنا | مسیر فعلی شاخه | نکتهٔ مشخصه‌نگاری |
|---|---|---|---|---|
| 1 | ایجاد درخواست و تخصیص اولیه | `actions/ticket-actions.php`، INSERT در `tickets` و ثبت پیام/رویداد/اعلان | همان مسیر INSERT؛ هنوز فرمان ایجاد به WorkflowService منتقل نشده | وضعیت اولیه، assigned_to/assigned_at، ticket_no، due_at، تمام فیلدهای ola_*، پیام اولیه، رویداد ایجاد و اعلان |
| 2 | لغو مستقیم درخواستِ قبل از ارجاع | `actions/ticket-actions.php`، UPDATE مستقیم status به cancelled | `RequestCenterWorkflowService::transition(..., requester_cancel)` | شرط‌های requester، assigned_to، first_response_at و resolved_at؛ توقف SLA/OLA؛ پیام، Audit، Event، اعلان و asset history |
| 3 | ثبت درخواست لغو و تصمیم مسئول (تأیید/رد) | `actions/ticket-actions.php`، UPDATE مستقیم برای ثبت/پاک‌سازی cancel_requested_* و تأیید لغو | تأیید از WorkflowService؛ ثبت درخواست و رد همچنان UPDATE مستقیم metadata هستند | در حالت رد، status باید ثابت بماند؛ در تأیید، status به cancelled و resolved_at پاک شود؛ پیام، Event، Audit، اعلان و پاک‌سازی metadata تطبیق شود |
| 4 | Claim / برداشتن درخواست | `actions/ticket-actions.php`، UPDATE مستقیم assigned_to، assigned_at و status | WorkflowService با قفل و expectedFields | در رقابت چند کارشناس فقط یک برنده؛ عدم تغییر وضعیت/تخصیص در بازنده‌ها؛ رویداد و اعلان دقیقاً یک‌بار |
| 5 | بازگشایی توسط درخواست‌دهنده | `actions/ticket-actions.php`، UPDATE مستقیم از resolved | WorkflowService با نوع requester_reopen | پاک‌شدن resolved_at، بازتنظیم SLA/OLA، حفظ تخصیص یا بازگشت به manager_review مطابق قواعد؛ پیام/Event/اعلان |
| 6 | پاسخ کارشناس و تغییر وضعیت همراه پاسخ | `actions/ticket-actions.php`، UPDATE مستقیم status و SLA/OLA | WorkflowService؛ ثبت پیام و اثرات جانبی در Handler | پاسخ عمومی/داخلی، first_response_at، وضعیت مقصد، due_at، sla_paused_at/minutes، تمام ola_*، Event و اعلان؛ حالت cancellation-pending جداگانه بررسی شود |
| 7 | ویرایش کارشناس/مدیر و تغییر metadata یا وضعیت | `actions/ticket-actions.php`، UPDATE پویا روی tickets | WorkflowService برای ستون‌های allowlist‌شده | priority، department، service_group، asset، category/service، assigned_to و SLA/OLA؛ تغییرات غیرمجاز/خارج از scope رد شوند؛ تغییر صرفاً metadata با status ثابت جداگانه ثبت شود |
| 8 | تصمیم سوپروایزر: بستن، برگشت برای اصلاح، بازگشایی | `actions/supervisor-actions.php`، سه UPDATE مستقیم | WorkflowService با انواع supervisor_close، supervisor_rework و supervisor_reopen | closed_at، resolved_at، supervisor_id، supervisor_approved_at، supervisor_note، cancel_requested_* و SLA/OLA؛ هر سه شاخه جداگانه اجرا شوند |
| 9 | به‌روزرسانی گروهی وضعیت | `actions/bulk-actions.php`، UPDATE مستقیم برای هر درخواست | WorkflowService برای هر درخواست داخل تراکنش گروهی | شمارش changed/already/skipped، اتمیک‌بودن سیاست گروهی، جلوگیری از عبور از scope، پیام اختیاری، Event/Audit و اعلان هر رکورد |
| 10 | بستن خودکار درخواست حل‌شده | `cron/ticket_auto_close.php`، UPDATE مستقیم status به closed | WorkflowService با نوع auto_close و قفل ردیف | deadline، نبود لغو در انتظار، وضعیت rating، closed_at، supervisor_note، Event، اعلان درخواست‌دهنده/کارکنان و asset history |

## ۳. فیلدهای Snapshot الزامی

برای هر ticket، پیش و پس از سناریو حداقل این ستون‌ها ثبت شوند:

- هویت و وضعیت: `id`, `status`, `updated_at`
- تخصیص: `requester_id`, `assigned_to`, `assigned_at`
- SLA: `sla_minutes`, `due_at`, `sla_paused_at`, `sla_pause_minutes`, `first_response_at`, `resolved_at`, `closed_at`
- OLA: `ola_policy_id`, `ola_response_due_at`, `ola_due_at`, `ola_escalation_1_at`, `ola_escalation_2_at`
- لغو: `cancel_requested_at`, `cancel_requested_by`, `cancel_request_reason`
- سوپروایزر: `supervisor_id`, `supervisor_approved_at`, `supervisor_note`
- داده‌های مؤثر بر SLA/OLA: `priority`, `department_id`, `service_group`, `service_id`, `category_id`

برای جداول جانبی، قبل/بعد و تعداد ردیف‌های مرتبط با ticket در این منابع ثبت شود: `ticket_messages`, `ticket_events`, `notifications`, `ticket_ratings`, `asset_history_events`. ساختار واقعی ستون‌ها ابتدا از Schema همان Clone خوانده شود؛ این برگه فرضی دربارهٔ ستون‌های این جداول تحمیل نمی‌کند.

## ۴. قالب ثبت هر اجرا

هر سناریو باید یک پوشهٔ شواهد مستقل داشته باشد:

1. شناسهٔ سناریو، تاریخ، commit، نسخهٔ PHP و خروجی `SELECT VERSION()` و `SELECT DATABASE()`.
2. شناسهٔ Clone و تأیید صریح غیرعملیاتی‌بودن آن؛ بدون ثبت credential.
3. Fixture و Snapshot اولیهٔ قابل بازتولید.
4. actor، نقش/مجوز، ورودی درخواست و وضعیت HTTP/CLI.
5. Snapshot نهایی ستون‌های بخش ۳.
6. اختلاف ردیف‌های جانبی و محتوای Event/اعلان با حذف اطلاعات شخصی.
7. نتیجهٔ اجرای Legacy و شاخهٔ هدف، به‌همراه Diff ماشینی.
8. تصمیم برای هر اختلاف: مطابق To-Be / نقص / نیازمند تصمیم.
9. نتیجه: PASS فقط با شواهد کامل؛ در غیر این صورت FAIL یا BLOCKED.

## ۵. نکات ایمنی و کنترل آزمون

- Legacy و شاخهٔ هدف را روی دو Clone مستقل و هم‌ساخت اجرا کنید؛ اجرای پشت‌سرهم روی یک DB می‌تواند وضعیت اولیه را آلوده کند.
- ساعت سیستم، تنظیمات SLA/OLA، کاربرها، نقش‌ها، مجوزها، تنظیمات اعلان و داده‌های مرجع باید در دو Clone یکسان باشند.
- سناریوهای زمان‌محور با ساعت کنترل‌شده یا fixtureهای ثابت اجرا شوند؛ اختلاف صرفاً ناشی از NOW() نباید با اختلاف رفتاری اشتباه شود.
- آزمون Claim هم‌زمان، bulk، auto-close و تصمیم لغو باید با workerهای واقعی و ثبت نتیجهٔ هر درخواست انجام شود.
- شکست یک سناریو نباید به‌عنوان موفقیت کل T12 گزارش شود.
- تا تکمیل و تأیید همهٔ ده سناریو، انتقال نوشتن‌های بیشتر به Command Layer ممنوع می‌ماند.

## ۶. وضعیت شواهد هنگام تهیهٔ این برگه

| مورد | وضعیت |
|---|---|
| مقایسهٔ ایستای مسیرهای Legacy و شاخهٔ هدف در چهار فایل اصلی | انجام شد؛ فقط بررسی سورس |
| نگاشت ده سناریو به handlerها | آماده شد |
| اجرای Legacy روی Clone MariaDB 10.4.32 | انجام نشده |
| اجرای شاخهٔ هدف روی Clone هم‌ساخت | انجام نشده |
| Snapshot مقایسه‌ای status/SLA/OLA | موجود نیست |
| تطبیق Event/notification و سایر side effectها | انجام نشده |
| نتیجهٔ T12 | **BLOCKED ـ نیازمند اجرای رفتاری روی دو Clone** |

## ۷. شواهد سورس

- مبنای Legacy: `actions/ticket-actions.php`، `actions/supervisor-actions.php`، `actions/bulk-actions.php` و `cron/ticket_auto_close.php` در commit `ab073ee9fffc56ccd1a2093d02725b95891e0d63`.
- شاخهٔ هدف: همان فایل‌ها در commit `49370a027d146176aa107db1a9ec813a419ca277`.
- پیاده‌سازی فعلی گذار: `modules/request-center/WorkflowService.php`.
- معیار حاکم: بخش ۱۷.۴ سند `REQUEST-CENTER-ARCHITECTURE.md` نسخهٔ ۱٫۱۴.

این برگه هیچ تغییر اجرایی، DDL، Migration یا مجوز نصب/ادغام ایجاد نمی‌کند.


## نتیجهٔ Preflight زیرساخت T12 — 2026-11-07

<badge>PASS — فقط زیرساخت</badge>

Run `42352805129` روی MariaDB 10.4.32 و 10.11 موفق شد:

- کد Legacy از commit ثابت `2176d2aa57758a0fbef426636c9943879646b165` در Worktree مستقل اجرا شد؛ کد Feature از شاخهٔ `feature/request-center-module-1.62.88` اجرا شد.
- دو دیتابیس مستقل `rc_t12_legacy` و `rc_t12_feature` از Schema مرجع `install-empty-db.sql` ساخته شدند.
- هر دو وب‌سرور PHP مستقل بالا آمدند؛ fixture نشست، کاربر مورد انتظار، CSRF token و نام دیتابیس متصل‌شده را برای هر برنامه تأیید کرد.
- شناسهٔ نشست Legacy و Feature مستقل بود؛ fingerprint ساختار پس از bootstrap در دو Clone یکسان بود و هر دو ۸۲ جدول داشتند.

**محدودیت قطعی:** این اجرا هیچ‌یک از ده سناریوی رفتاری را اجرا نکرده است (۰ از ۱۰). بنابراین T12 هنوز **BLOCKED / OPEN** است و این PASS فقط آمادگی محیط، جداسازی Cloneها و مسیر HTTP/Session را تأیید می‌کند.

### گام بعدی T12

در همین Harness باید ده سناریوی مصوب با ورودی یکسان روی هر دو نسخه اجرا و side effectهای `tickets`، `ticket_messages`، `ticket_events`، `activity_logs`، `notifications`، `ticket_attachments` و فایل‌های `storage/uploads`، روابط دارایی و اجرای Cron مقایسه شوند. هر اختلاف باید با نام جدول/فیلد و اثر قابل بازتولید ثبت شود؛ تا آن زمان هیچ ادعای برابری رفتاری یا PASS برای T12 مجاز نیست.
