# ممیزی As-Is مرکز درخواست‌ها — نسخه مبنا 1.62.88

## ۱. دامنه و روش

- مخزن: alirezarahimy-code/ticket
- شاخه ممیزی/اجرا: feature/request-center-module-1.62.88
- commit مبنا: ab073ee9fffc56ccd1a2093d02725b95891e0d63
- نسخه اعلام‌شده در این شاخه: 1.62.88
- دامنه: چرخه درخواست پشتیبانی، صف رسیدگی، جزئیات، گردش وضعیت، ارجاع، لغو، پاسخ، تأیید نهایی، گزارش، اعلان، SLA/OLA و auto-close.
- روش: بررسی فایل‌های شاخه، نقشه ROUTES.tsv و تست‌های موجود. این گزارش ادعای اجرای کد روی MariaDB یا مرورگر واقعی ندارد.

## ۲. نقشه صفحات و عملیات موجود

| قابلیت | مسیر فعلی | قرارداد/نکته |
|---|---|---|
| ثبت درخواست | index.php بخش new-ticket؛ actions/ticket-actions.php: create_ticket | ساخت رکورد، شماره، پیام آغازین، پیوست، audit/event، اعلان و تاریخچه دارایی |
| مشاهده جزئیات | index.php بخش ticket | fetch_ticket و can_view_ticket؛ پیام‌ها، پیوست، تاریخچه رخداد و کنترل داخلی |
| صف کاری | index.php بخش queue | queue.view و ticket_scope؛ فیلترهای all/unassigned/overdue/mine/waiting_user/resolved/cancellation_requested/cancelled؛ عملیات گروهی |
| لغو توسط درخواست‌کننده | actions/ticket-actions.php: cancel_ticket | لغو مستقیم قبل از ارجاع یا درخواست لغو پس از ارجاع |
| تصمیم لغو | actions/ticket-actions.php: decide_ticket_cancellation | تأیید/رد با قفل و ثبت رویداد |
| برداشتن از صف | actions/ticket-actions.php: claim_ticket | بررسی صلاحیت مسئول و وضعیت جاری |
| بازگشایی توسط درخواست‌کننده | actions/ticket-actions.php: reopen_ticket | بازگشایی پس از حل‌شده و ثبت دلیل |
| پاسخ/یادداشت داخلی | actions/ticket-actions.php: reply | تفکیک requester/handler، کنترل internal note و گذارهای مجاز |
| ویرایش/ارجاع/اولویت | actions/ticket-actions.php: update_ticket | مدیر رسیدگی در برابر کارشناس مسئول؛ lock و بررسی وضعیت/مسئول/اولویت |
| تصمیم سوپروایزر | actions/supervisor-actions.php: supervisor_decision | تأیید و بستن یا برگشت برای اصلاح |
| بازگشایی سوپروایزر | actions/supervisor-actions.php: supervisor_reopen | مسیر رسمی بازگشایی |
| امتیاز رضایت | actions/supervisor-actions.php: rate_ticket | امتیاز مربوط به آخرین چرخه حل |
| تغییر گروهی | actions/bulk-actions.php: bulk_update_tickets | چند رکورد؛ باید همه قیود workflow به ازای هر درخواست رعایت شود |
| بستن خودکار | cron/ticket_auto_close.php | مصرف‌کننده مستقل workflow؛ lock و بررسی امتیاز پیش از بستن |
| گزارش و تحلیل | index.php بخش reports/analytics/supervisor | همه queryها باید از همان ticket_scope استفاده کنند |
| اعلان | lib/notify-users.php | recipients با ticket_user_can_view و فیلتر internal/staff |
| SLA/OLA | lib/tickets-sla.php و ola.php | تقویم کاری/تعطیلات و سیاست OLA؛ انتقال وضعیت روی deadline اثر دارد |

نقشه مسیرهای ماشینی در docs/system-map/ROUTES.tsv است. این جدول، جایگزین کد نیست؛ هر تغییر route باید در نقشه نیز منعکس شود.

## ۳. قراردادهای سرویس مشترک

| قرارداد | منبع فعلی | مصرف مرکز درخواست | محدودیت پروژه |
|---|---|---|---|
| اتصال DB | bootstrap.php و lib/db-core.php: db() | همه queryها و تراکنش‌ها | مصرف؛ تغییر هسته ممنوع |
| نشست و احراز هویت | bootstrap.php، session_bootstrap.php، lib/auth-scope.php | require_login/current_user | مصرف؛ تغییر احراز هویت ممنوع |
| مجوز | permissions.php و user_can/require_permission | ticket.create/view/reply/edit/assign/rate، queue.view و گزارش | مصرف مجوزهای موجود؛ role/engine سراسری تغییر نمی‌کند |
| دامنه مشاهده | lib/auth-scope.php: ticket_scope, ticket_user_can_view | فهرست، جزئیات، اعلان، پیوست و عملیات | تنها منبع مجاز scope؛ ساخت فیلتر role موازی ممنوع |
| اختیار رسیدگی | lib/auth-scope.php: ticket_handler_level, ticket_view_mode, ticket_assignee_is_eligible | UI و server-side actions | باید در WorkflowService مجدداً server-side اعمال شود |
| وضعیت مجاز | index.php: ticket_allowed_transitions و قواعد actions | update/reply/supervisor/cron | منطق باید به WorkflowService منتقل شود؛ status update پراکنده ممنوع |
| شماره‌گذاری | lib/tickets-sla.php: ticket_number_* | ثبت، فهرست و گزارش | مصرف؛ حفظ uniqueness و عدم تکرار شماره |
| SLA | lib/tickets-sla.php | create/update/reply/reopen/cancel | مصرف؛ محاسبه deadline فقط از قرارداد فعلی تا طراحی تقویم کارشناسان |
| OLA | ola.php و lib/tickets-sla.php | deadline پاسخ/حل و escalation | مصرف؛ تقویم OLA مستقل از ماژول غذا |
| اعلان | lib/notify-users.php | notify_ticket_parties/staff/resolved | مصرف؛ visibility و idempotency حفظ شود |
| Audit | lib/activity-audit.php: save_audit_required | عملیات حساس پیش از commit | failure باید transaction را fail کند |
| Ticket event | lib/activity-audit.php: log_ticket_event | تاریخچه و replay | داخل transaction خطای قطعی نباید پنهان شود |
| پیوست | توابع store_attachment و download_attachment در index.php | ثبت و دانلود فایل | انتقال با کنترل MIME/size/path و مجوز مشاهده |
| دارایی | inventory.php و record_asset_history | درخواست IT و رخدادهای دارایی | مصرف API فعلی؛ تغییر inventory ممنوع |
| کاتالوگ خدمت | service_catalog و ticket_service_* در lib/logging-csrf.php | category/service/custom fields | مصرف؛ DDL/seed فعلی باید پیش از ورود مستقل به migration منتقل شود |
| ساختار سازمان | organization.php و org_ticket_schema_ensure | requesting_unit_id و routing department | مصرف read contract؛ تغییر org module ممنوع |
| CD/DVD | cd-dvd.php و cd_dvd_* | ثبت درخواست و اتصال به رکورد رسانه | فقط از رابط فعلی؛ تغییر ماژول CD/DVD ممنوع |

## ۴. گره‌های فنی و ریسک‌های As-Is

### ۴.۱. ورودی و بارگذاری
- index.php در ابتدای کار bootstrap.php و مجموعه‌ای از ماژول‌های سراسری را بارگذاری می‌کند.
- در ناحیهٔ POST، فایل‌های action به‌صورت include در همان scope اجرا می‌شوند؛ actions/ticket-actions.php صریحاً به APP_ROOT و متغیرهای مشترک محیط index وابسته است.
- بنابراین انتقال ساده فایل action به یک entrypoint جدید، استقلال ایجاد نمی‌کند و احتمال خطای متغیر/تابع/ترتیب include دارد.

### ۴.۲. Schema در زمان اجرا
- ابتدای index.php توابع ticket_number_ensure_schema و ticket_number_autofill را اجرا می‌کند.
- ticket_hot_schema_ensure در lib/tickets-sla.php روی tickets و users ستون/ENUM می‌افزاید و به org_ensure_schema، org_ticket_schema_ensure و lac_ensure_schema شاخه می‌دهد.
- create_ticket توابع ticket_service_ensure_schema و org_ticket_schema_ensure را فراخوانی می‌کند.
- ticket_service_ensure_schema در lib/logging-csrf.php ساختار categories و service_catalog را تغییر می‌دهد و seed/backfill انجام می‌دهد.
- این DDLها بخشی از updater ماژول نیستند و باید پیش از انتقال کامل workflow از مسیر عادی وب حذف/جایگزین شوند. پروژه حق تغییر updater عمومی یا schema ماژول‌های دیگر را ندارد؛ هر وابستگی به آن‌ها باید Change Request جداگانه شود.

### ۴.۳. چند مالک برای گذار وضعیت
- status در ticket-actions.php، supervisor-actions.php، bulk-actions.php و cron/ticket_auto_close.php تغییر می‌کند.
- همه گذارها باید به WorkflowService واحد منتقل شوند؛ consumerها فقط فرمان‌های دامنه‌ای را صدا بزنند.
- منطق SLA/OLA، audit/event و notification باید بخشی از قرارداد همان فرمان و transaction باشد، نه side effect فراموش‌شدنی.

### ۴.۴. سازگاری داده و مصرف‌کنندگان بیرونی
- tickets و ticket_events توسط گزارش‌ها، اعلان‌ها، asset history، governance و jobها مصرف می‌شوند.
- این پروژه schema موجود را مالکیت نمی‌کند و نباید نام/نوع ستون‌های مصرف‌شده را بدون قرارداد سازگاری تغییر دهد.
- URLهای index.php?page=ticket و index.php?page=queue و endpointهای download باید تا پایان انتقال سازگار بمانند.

## ۵. وضعیت تست‌های مشخصه‌نگاری موجود

| تست | پوشش فعلی | استفاده در انتقال |
|---|---|---|
| tests/test_ticket_workflow_safety_static.py | گارد visibility، eligibility، reply، rating/auto-close، cancel/bulk stale state | نگهبان workflow |
| tests/test_ticket_routing_unit.php | ticket_scope، notification recipients، assignee eligibility، handler/view mode و self-assignment | نگهبان scope/routing |
| tests/test_ticket_scope_sql_node.js | اجرای predicate تولیدی روی fixture relational | نگهبان SQL scope |
| tests/test_ticket_cancellation_static.py | قراردادهای لغو | نگهبان cancellation |
| tests/test_ticket_sla_unit.php | محاسبه و گذار SLA | نگهبان deadline |
| tests/test_ticket_number_autofill_unit.php | شماره‌گذاری و autofill | نگهبان شماره |
| tests/test_ticket_three_views_static.py | جداسازی نماهای requester/assignee/supervisor | نگهبان UI authorization |
| tests/test_ticket_lifecycle_guide_static.py | مستند چرخه درخواست | نگهبان مستندات |

این تست‌ها همه رفتارهای end-to-end را اثبات نمی‌کنند. تست MariaDB واقعی برای transaction/lock، upload، notification و workflow کامل هنوز نیازمند اجرا در محیط clone است.

## ۶. مدل وضعیت و قواعد انتقال

وضعیت‌های شناخته‌شده در کد:
- new
- manager_review
- assigned
- in_progress
- waiting_user
- pending (legacy alias که در بخش‌هایی مثل waiting_user رفتار می‌کند)
- resolved
- closed
- cancelled

قواعد حیاتی که باید حفظ شوند:
- درخواست‌کننده فقط درخواست خود را می‌بیند مگر مجوز مؤثر دیگری داشته باشد.
- internal note برای درخواست‌کننده قابل مشاهده نیست.
- کارشناس/مدیر روی درخواست خودش به‌صورت خودکار اختیار سرپرستی نمی‌گیرد.
- درخواست بدون مسئول نمی‌تواند به وضعیت حل‌شده برود.
- بستن نهایی پس از امتیاز جاری کاربر و تصمیم سوپروایزر انجام می‌شود.
- لغو در انتظار تصمیم، تغییر وضعیت و ارجاع را متوقف می‌کند اما گفت‌وگو ممکن است ادامه یابد.
- هر تغییر حساس باید Audit پایدار و ticket event متناظر داشته باشد.
- هم‌زمانی با lock و revalidation روی دادهٔ قفل‌شده کنترل می‌شود.
- SLA/OLA در هر transition با status/priority/cycle دوباره محاسبه می‌شود.

## ۷. ترتیب انتقال و معیار خروج فازها

> **یادداشت شماره‌گذاری:** این بخش برای حفظ ارجاع‌های تاریخی، شماره‌گذاری داخلی سند معماری 1.14 (فاز صفر تا چهار) را بازتاب می‌دهد. شماره‌گذاری رسمی و حاکم گزارش پیشرفت پروژه پنج فاز است: فاز 1 ممیزی و طراحی، فاز 2 استقلال ماژول، فاز 3 اصلاح گردش‌کار، فاز 4 تقویم کارشناسان و فاز 5 تکمیل و انتشار. نگاشت دقیق در `REQUEST-CENTER-PHASE1-EVIDENCE-REGISTER-FA.md` ثبت شده است. بنابراین «فاز صفر» در متن زیر همان فاز 1 رسمی پروژه است و به معنی وجود یک فاز ششم یا تغییر ترتیب مصوب نیست.

### فاز صفر — ممیزی و baseline
خروجی: همین سند، نقشه سرویس‌ها، فهرست سناریوهای مشخصه‌نگاری و baseline تست‌ها.
معیار خروج: همه actionها و consumerهای مستقیم شناخته شده؛ هر ابهام ثبت‌شده دارای آزمون پیشنهادی.

### فاز یک — جداسازی خواندن
خروجی: entrypoint مستقل، repository خواندنی، فهرست و جزئیات با scope و internal-note filtering.
معیار خروج: عدم DDL/side effect، حفظ 404 برای out-of-scope، تست static و آزمون MariaDB.

### فاز دو — WorkflowService
خروجی: سرویس واحد گذار وضعیت و فرمان‌های create/cancel/decide-cancel/claim/reopen/reply/update/supervisor/bulk/auto-close.
معیار خروج: تمام writerها از سرویس عبور کنند؛ هیچ UPDATE status خارج از سرویس نماند؛ تست concurrency و transaction.

### فاز سه — رابط‌های نقش‌محور
خروجی: Requester، Agent، Supervisor، Request Center Admin؛ bridge برای URL قدیمی.
معیار خروج: parity با فرم‌ها و رفتارهای قبلی، دسترسی server-side و رگرسیون کامل.

### فاز چهار — SLA/OLA، تقویم کارشناسان و hardening
خروجی: تقویم کارشناسان مستقل از تقویم غذا، performance/security/accessibility و بسته نصب.
معیار خروج: آزمون clone، restore، load و smoke test.

## ۸. سناریوهای رگرسیون اجباری

1. ثبت درخواست IT با دارایی معتبر و auto-assignment.
2. ثبت درخواست پشتیبانی با محل خدمت و خدمت فعال.
3. ثبت درخواست متصل به CD/DVD و اعتبارسنجی visibility رکورد مرجع.
4. رد خدمت غیرفعال، دسته ناسازگار، فیلد سفارشی نامعتبر و فایل نامجاز.
5. شماره یکتا پس از rollback/هم‌زمانی.
6. ثبت پیام اولیه، audit required، event و notification در تراکنش.
7. ثبت درخواست با نبود کارشناس و notify فقط به افراد واقعاً مجاز.
8. فهرست برای requester، agent، manager، support_manager، supervisor و primary_admin.
9. رد مشاهده cross-service-group و cross-department.
10. جلوگیری از دیدن internal note و attachment آن توسط requester.
11. دانلود attachment با دسترسی معتبر؛ رد شناسهٔ attachment متعلق به درخواست دیگر.
12. cancel مستقیم قبل از assignment.
13. cancel request پس از assignment؛ block transition تا تصمیم.
14. approve/reject cancellation با actor مجاز و stale-state protection.
15. claim فقط توسط agent واجد شرایط و جلوگیری از self-assignment طبق تنظیم فعلی.
16. reply عمومی و internal note با تفکیک actor.
17. پاسخ requester در waiting_user و race با cancellation.
18. پاسخ handler و اولین پاسخ SLA.
19. update status/priority/assignee و reject assignment خارج از گروه/واحد.
20. جلوگیری از close مستقیم و status transition غیرمجاز.
21. resolved نیازمند پیام راه‌حل؛ rating فقط برای requester و چرخه جاری.
22. supervisor approve فقط پس از rating جاری؛ rework با note.
23. supervisor reopen و حفظ تاریخچه.
24. bulk update: mixed permissions، stale status، partial failure و all-or-nothing policy.
25. auto-close: lock، rating در همان ثانیه، cancellation pending و event ordering.
26. SLA/OLA در تغییر priority، waiting_user، reopen، cancel و holidays.
27. audit/event/notification در خطای DB؛ هیچ success flash بدون commit.
28. pagination/filter/sort و عدم N+1 در فهرست‌های ۱۰۰ رکوردی.
29. legacy URLs، notification deep links، search result links و attachment downloads.
30. cron و API/jobها در زمان انتقال و بعد از آن.
31. XSS در subject/body/custom field/filename و SQL injection.
32. CSRF و method enforcement برای همه write commands.
33. clone restore قبل و بعد Migration.
34. duplicate concurrent submit و duplicate request number.
35. load روی صف و جزئیات در کنار write contention.

## ۹. موارد نیازمند آزمون محیط واقعی

- نسخه و engine دقیق MariaDB با SELECT VERSION() و SHOW TABLE STATUS.
- رفتار واقعی FOR UPDATE و isolation level در محیط نصب.
- metadata lock و زمان ALTER روی tickets.
- upload/download روی Windows ACL و مسیر storage/uploads.
- SMTP/notification delivery و failure semantics.
- cron auto-close با رقابت workerها.
- query plans/index usage روی داده با حجم واقعی.
- Restore کامل DB و فایل‌ها و smoke test.
- زمان پاسخ p95/p99 و concurrency با Apache/PHP/XAMPP واقعی.

تا اجرای این آزمون‌ها، هیچ ادعای تأیید عملیاتی یا کارایی نهایی صادر نمی‌شود.
