# نگاشت دقیق نقاط نوشتن و گذار وضعیت برای T12

| مشخصه | مقدار |
|---|---|
| شناسه | RC-T12-SOURCE-MAP-01 |
| شاخه هدف | `feature/request-center-module-1.62.88` |
| HEAD مبنا | `e1f7f9b581a9005f3a6e08a8dbf5ad9244a5b3b1` |
| مبنای Legacy | `ab073ee9fffc56ccd1a2093d02725b95891e0d63` |
| سند آزمون مادر | `REQUEST-CENTER-T12-CHARACTERIZATION-WORKSHEET-FA.md` |
| وضعیت | نگاشت ایستای سورس تکمیل شد؛ اجرای رفتاری همچنان BLOCKED |

## ۱. شمارش صحیح

در کد مبنا، ۱۱ handler/مصرف‌کننده اصلی برای ایجاد یا تغییر وضعیت/داده چرخه عمر درخواست وجود دارد. تعداد سناریوهای برگه فعلی T12 برابر ۱۰ گروه است، چون چند handler یا شاخه در یک گروه تجمیع شده‌اند. بنابراین تعداد ردیف‌های سناریو نباید با تعداد handlerها اشتباه گرفته شود.

`rate_ticket` یک عملیات جانبی امتیازدهی است و transition وضعیت نیست؛ با این حال باید در آزمون side effect و بسته‌شدن لحاظ شود. `add_asset_relation` نیز تغییر رابطه دارایی است، نه transition وضعیت.

## ۲. نگاشت ۱۱ handler واقعی

| # | عملیات/handler | مسیر Legacy و محدوده خطوط | مسیر شاخه هدف و محدوده خطوط | وضعیت پیاده‌سازی فعلی |
|---|---|---|---|---|
| 1 | ایجاد درخواست — `create_ticket` | `actions/ticket-actions.php:30-217` | `modules/request-center/Actions/ticket-actions.php:30-217` | INSERT اولیه همچنان در Handler است؛ WorkflowService فرمان ایجاد را مالک نیست |
| 2 | لغو توسط درخواست‌دهنده — `cancel_ticket` | `actions/ticket-actions.php:222-265` | `modules/request-center/Actions/ticket-actions.php:222-282` | لغو مستقیم از WorkflowService عبور می‌کند؛ ثبت درخواست لغو، metadata مستقیم می‌نویسد |
| 3 | تصمیم درباره لغو — `decide_ticket_cancellation` | `actions/ticket-actions.php:271-330` | `modules/request-center/Actions/ticket-actions.php:284-360` | تأیید لغو از WorkflowService؛ رد لغو metadata را مستقیم پاک می‌کند و status ثابت می‌ماند |
| 4 | Claim — `claim_ticket` | `actions/ticket-actions.php:336-368` | `modules/request-center/Actions/ticket-actions.php:366-401` | transition از WorkflowService |
| 5 | بازگشایی درخواست‌دهنده — `reopen_ticket` | `actions/ticket-actions.php:373-403` | `modules/request-center/Actions/ticket-actions.php:406-451` | transition از WorkflowService |
| 6 | پاسخ/یادداشت و تغییر وضعیت — `reply` | `actions/ticket-actions.php:408-525` | `modules/request-center/Actions/ticket-actions.php:456-600` | transition از WorkflowService؛ پیام، Audit/Event و اعلان در Handler |
| 7 | ویرایش کارشناس/مدیر — `update_ticket` | `actions/ticket-actions.php:530-669` | `modules/request-center/Actions/ticket-actions.php:605-749` | در صورت تغییر وضعیت از WorkflowService؛ metadataهای خارج از allowlist/بدون transition باید جداگانه مشخصه‌نگاری شوند |
| 8 | تصمیم سوپروایزر — `supervisor_decision` | `actions/supervisor-actions.php:40-95` | `modules/request-center/Actions/supervisor-actions.php:40-111` | دو شاخه مستقل: بستن و برگشت برای اصلاح؛ هر دو از WorkflowService |
| 9 | بازگشایی سوپروایزر — `supervisor_reopen` | `actions/supervisor-actions.php:97-128` | `modules/request-center/Actions/supervisor-actions.php:114-153` | transition از WorkflowService |
| 10 | تغییر گروهی — `bulk_update_tickets` | `actions/bulk-actions.php:44-123` | `modules/request-center/Actions/bulk-actions.php:44-136` | هر درخواست از WorkflowService عبور می‌کند؛ سیاست all-or-nothing و رفتار partial failure باید با DB واقعی اثبات شود |
| 11 | بستن خودکار — `ticket_auto_close.php` | `cron/ticket_auto_close.php:1-78` | `cron/ticket_auto_close.php:1-85` | transition از WorkflowService؛ پیش از حلقه هنوز `ticket_hot_schema_ensure()` فراخوانی می‌شود |

## ۳. نقاط دقیق WorkflowService در شاخه هدف

| عملیات | محل فراخوانی transition |
|---|---|
| لغو مستقیم | `modules/request-center/Actions/ticket-actions.php:243-244` |
| تأیید لغو | `modules/request-center/Actions/ticket-actions.php:317` |
| Claim | `modules/request-center/Actions/ticket-actions.php:391` |
| بازگشایی درخواست‌دهنده | `modules/request-center/Actions/ticket-actions.php:428` |
| پاسخ/تغییر وضعیت | `modules/request-center/Actions/ticket-actions.php:575` |
| ویرایش دارای تغییر وضعیت | `modules/request-center/Actions/ticket-actions.php:718` |
| تصمیم سوپروایزر ـ بستن | `modules/request-center/Actions/supervisor-actions.php:71` |
| تصمیم سوپروایزر ـ برگشت برای اصلاح | `modules/request-center/Actions/supervisor-actions.php:89` |
| بازگشایی سوپروایزر | `modules/request-center/Actions/supervisor-actions.php:131` |
| تغییر گروهی | `modules/request-center/Actions/bulk-actions.php:103` |
| بستن خودکار | `cron/ticket_auto_close.php:59` |

پیاده‌سازی مرکزی در `modules/request-center/WorkflowService.php:95-174` قرار دارد؛ نوشتن status در خط 174 با شرط وضعیت مبدأ انجام می‌شود. این شواهد فقط مالکیت و مسیر کد را نشان می‌دهد؛ صحت قفل، isolation، اثر side effectها و برابری رفتاری با Legacy را اثبات نمی‌کند.

## ۴. داده‌هایی که باید در T12 مقایسه شوند

برای هر handler، قبل و بعد از اجرا حداقل این داده‌ها باید Snapshot شوند:
- `tickets`: وضعیت، مسئول و زمان تخصیص، priority، due_at، SLA pause fields، first_response_at، resolved_at، closed_at، تمام ستون‌های `ola_*`، cancellation metadata و supervisor fields.
- `ticket_messages`: تعداد و محتوای پیام عمومی/داخلی با حذف اطلاعات شخصی و رازها.
- `ticket_events` و Audit: نوع رویداد، actor، from/to، metadata و ترتیب ثبت.
- `notifications`: تعداد، گیرندگان مؤثر، نوع اعلان، متن/لینک مجاز و عدم افشای یادداشت داخلی.
- `ticket_ratings` و `asset_history_events` در سناریوهای مرتبط.
- خروجی HTTP/CLI، کد خطا، flash/success و rollback واقعی.

## ۵. مواردی که باید جداگانه در آزمون پوشش داده شوند

1. در `cancel_ticket`، لغو مستقیم و درخواست لغو دو شاخه با پیش‌شرط و اثر متفاوت‌اند.
2. در `decide_ticket_cancellation`، تأیید و رد لغو باید جداگانه اجرا شوند؛ رد نباید status را تغییر دهد.
3. در `supervisor_decision`، بستن و برگشت برای اصلاح دو transition متفاوت‌اند.
4. `update_ticket` هم تغییر صرفاً metadata و هم تغییر status/priority/assignee را شامل می‌شود؛ این‌ها یک سناریوی واحد قابل قیاس نیستند.
5. `reply` باید actor درخواست‌دهنده/کارشناس و پیام عمومی/یادداشت داخلی را جدا کند.
6. `bulk_update_tickets` باید ترکیب مجوزها، وضعیت stale، خطا در میانه batch و سیاست اتمیک‌بودن را پوشش دهد.
7. `ticket_auto_close.php` باید با cancellation pending، rating هم‌زمان و دو worker هم‌زمان آزمایش شود.

## ۶. وضعیت شواهد

| شاهد | وضعیت |
|---|---|
| نگاشت ۱۱ handler در Legacy و شاخه هدف | PASS ایستای سورس |
| نگاشت فراخوانی‌های WorkflowService در شاخه هدف | PASS ایستای سورس |
| اثبات عدم وجود writer پنهان در تمام مصرف‌کنندگان بیرونی | نیازمند ممیزی تکمیلی |
| اجرای هر ۱۱ handler روی Clone Legacy | انجام نشده |
| اجرای هر ۱۱ handler روی Clone شاخه هدف | انجام نشده |
| مقایسه Snapshot و side effectها | انجام نشده |
| نتیجه T12 | **BLOCKED؛ هیچ PASS رفتاری صادر نمی‌شود** |

این سند مجوز گسترش WorkflowService، تغییر رفتار یا انتقال نوشتن‌های بیشتر نیست. تا تکمیل T12، فقط ممیزی و آماده‌سازی آزمون در محدوده مصوب مجاز است.
