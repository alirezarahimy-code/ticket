# گزارش همگام‌سازی بستهٔ Logging ‏1.62.75 → 1.62.76

- **تاریخ:** 2026-11-04
- **مبنای تغییرناپذیر:** کامیت `c1315c0c8e8f5eba4919f43a7cec13112df8dfeb` (tag `v1.62.75`)
- **بستهٔ ورودی (خام):** `ticket-update-1.62.75-to-1.62.76-logging-migrations-retained-20261104(1).zip`
  از شاخهٔ `work/logging-1.62.76-from-v1.62.75-20261104` (کامیت `19c7f63ea41f4f910b542cd0eec4b81064fbe680`)
  — sha256 `5f028aaac5aa163605f0fda119c45713a2e23163ff40f51cd35d48704da1702e`
- **خروجی (قابل نصب):** `downloads/ticket-update-1.62.75-to-1.62.76-logging-20261104.zip`
  — sha256 `7f5d7d02dbc2980ddc348188f1cf45e9abf349ecec6665d1123c9a0c7035609c` — ۱۸۲٬۲۱۸ بایت

> در تمام این فرایند هیچ اتصالی به پایگاه‌داده برقرار نشد و هیچ مهاجرتی اجرا نشد.

---

## ۱) حکم دربارهٔ بستهٔ خام: غیرقابل نصب بود

بستهٔ خام **۵ ایراد بحرانی** داشت که دو موردش در همان نخستین درخواست HTTP منجر به خطای مرگبار می‌شد.

| # | محل | شاهد | اثر |
|---|---|---|---|
| B1 | `files/lib/activity-audit.php` | ۱۵ فراخوانی `logging_*` با تنها ۳ گارد `function_exists`، درحالی‌که `bootstrap.php` در بسته نبود و هیچ‌جا `lib/logging-core.php` بارگذاری نمی‌شد | Fatal در هر درخواست |
| B2 | `files/index.php:24` | `require_once __DIR__ . '/logging-dashboard.php';` — این فایل نه در بسته بود نه در مخزن؛ `security_log_access_denied()` (۱۰ فراخوانی) و `logging_dashboard_render()` هیچ تعریفی نداشتند | Fatal |
| B3 | `files/index.php` | `access_group` = ۰ مورد (مخزن: ۱۵)، `user_access_group` = ۰ (مخزن: ۷) | حذف کامل قابلیت «گروه‌های دسترسی عمومی» ۱.۶۲.۷۵ |
| B4 | `files/lib/activity-audit.php` | `access_group` = ۰ (مخزن: ۳) | حذف ۳ برچسب فارسی رخداد در گزارش فعالیت |
| B5 | `manifest.migrations = []` | هستهٔ جدید **صفر** گارد ستون دارد ولی در `activity_logs` ۲۴ ستون (schema: ۱۷) و در `system_logs` ۱۵ ستون (schema: ۸) می‌نویسد | هر درج لاگ شکست می‌خورد |

پس‌رفت‌های دیگر: امضای `choose_handling_agent()` پارامتر سوم `$requesterId` را از دست داده بود (و `$routingTicket` به `requester_id => 0` سقوط کرده بود)، `notification_ticket_visibility()` از ۲ محل حذف شده بود (نشت دامنهٔ دید اعلان‌ها)، `README.md` از ۲۷٬۱۱۴ به ۳٬۵۷۴ بایت کوچک شده بود (هر ۲۰ سرفصل حذف) و `DELIVERY-MANIFEST-fa.txt` از ۲۵۶۸ به ۱۳۹۸ بایت.

## ۲) ۱۶ اصلاح جراحی‌شده در بستهٔ خروجی

| # | فایل | اصلاح |
|---|---|---|
| ۱ | `bootstrap.php` *(تازه به بسته افزوده شد)* | نسخهٔ مخزن + `require_once __DIR__ . '/lib/logging-core.php';` میان `lib/db-core.php` و `lib/logging-csrf.php` |
| ۲ | `index.php` | حذف `require` فایل ناموجود `logging-dashboard.php` |
| ۳ | `index.php` | بازگردانی `require_once __DIR__ . '/user-access-groups.php';` |
| ۴ | `index.php` | حذف لینک منوی «داشبورد مانیتورینگ لاگینگ» (صفحه‌اش وجود نداشت) |
| ۵ | `index.php` | حذف بلوک صفحهٔ `logging-dashboard` با فراخوانی تابع تعریف‌نشده |
| ۶ | `index.php` | بازگردانی `require __DIR__ . '/actions/access-group-actions.php';` |
| ۷ | `index.php` | بازگردانی بلوک `$errorRedirect` برای `create/save/delete_access_group` |
| ۸ | `index.php` | بازگردانی امضای `choose_handling_agent(..., int $requesterId = 0)` |
| ۹ | `index.php` | بازگردانی `'requester_id' => $requesterId` در `$routingTicket` |
| ۱۰ | `index.php` | بازگردانی `notification_ticket_visibility($user, 'n')` در فید اعلان |
| ۱۱ | `index.php` | بازگردانی `array_merge(...)` پارامترهای فید |
| ۱۲ | `index.php` | بازگردانی `notification_ticket_visibility($notificationUser, 'n')` در صفحهٔ اعلان‌ها |
| ۱۳ | `index.php` | بازگردانی `array_merge(...)` پارامترهای صفحهٔ اعلان‌ها |
| ۱۴ | `index.php` | بازگردانی بلوک ۷۲ خطی رابط گروه‌های دسترسی (`user_access_groups_schema_ready/_all/_admin_users` و `$selectedAccessGroupId`) |
| ۱۵ | `lib/logging-core.php` | افزودن تابع گمشدهٔ `security_log_access_denied()` — پوشش امن روی `activity_log()` با `result='denied'`؛ همهٔ استثناها بلعیده می‌شوند تا ثبت لاگ هرگز درخواست کاربر را نشکند |
| ۱۶ | `lib/activity-audit.php` | بازگردانی ۳ برچسب `access_group_created/updated/deleted` |

**یک تغییر بسته که عمداً حفظ شد:** حذف `system_log('error','request',…)` از handler استثنا. بررسی نشان داد `app_error_log()` خودش همان رخداد را در `system_logs` ثبت می‌کند، پس خط حذف‌شده دوباره‌شماری بود — نه پس‌رفت. به‌جای آن بسته یک `activity_log('post_action_failed')` گاردشده افزوده که حفظ شد.

**افزوده‌های مفید بسته که دست‌نخورده ماندند:** `log_bounded_date_range()` (سقف ۳۶۵ روز)، فیلترهای `event_code`/`result`/`request_id`، سطوح `notice`/`critical`، شرط `l.category='audit'` به‌جای wildcard ماژول NULL، جست‌وجوی رویداد تیکت و جزئیات ماسک‌شده، و سخت‌سازی تراکنشی `actions/holiday-actions.php` (۶ هانک، همگی افزایشی و بدون تغییر امضا).

## ۳) رفع تداخل شماره‌های مهاجرت (دغدغهٔ صریح شما)

شماره‌های ۰۱۹–۰۲۳ در مخزن **قبلاً اشغال‌اند** (traffic/food/access-groups). بستهٔ خام همان شماره‌ها را دوباره به کار می‌برد ⇒ دو فایل با یک شماره. هر ۷ فایل تغییر شماره یافتند:

| نام در بستهٔ خام (ممنوع) | نام در بستهٔ خروجی | محتوا |
|---|---|---|
| `019-central-logging.sql` | `024-central-logging.sql` | ALTER روی `activity_logs`, `system_logs`, `ticket_events` |
| `020-logging-issues-alerts.sql` | `025-logging-issues-alerts.sql` | ساخت `log_issues`, `logging_health_alerts` |
| `021-logging-legacy-result-unknown.sql` | `026-logging-legacy-result-unknown.sql` | ساخت `log_issue_history/_events/_notifications` |
| `022-logging-correlation-ids.sql` | `027-logging-correlation-ids.sql` | `trace_id` / `operation_id` |
| `023-logging-fingerprint-v2.sql` | `028-logging-fingerprint-v2.sql` | `fingerprint` روی `system_logs` |
| `024-activity-result-default-unknown.sql` | `029-activity-result-default-unknown.sql` | پیش‌فرض `result` |
| `025-logging-notification-idempotency-and-ticket-history.sql` | `030-logging-notification-idempotency-and-ticket-history.sql` | idempotency اعلان + تاریخچهٔ `ticket_events` |

هیچ‌یک از این SQLها به شمارهٔ مهاجرت یا جدول `schema_updates` ارجاع نمی‌دهد، پس تغییر شماره بی‌خطر است. همه idempotent‌اند (گارد `information_schema`، `CREATE TABLE IF NOT EXISTS`، `UPDATE … WHERE … IS NULL`، `INSERT IGNORE`) و برخلاف ادعای بستهٔ خام، در `manifest.migrations` ثبت شده‌اند تا واقعاً اجرا شوند.

## ۴) نتایج اعتبارسنجی

| آزمون | نتیجه |
|---|---|
| ۱۰ قاعدهٔ پیش‌پرواز `system-update.php` (امنیت مسیر، whitelist، قالب نسخه، الگوی نام مهاجرت، وجود فایل مهاجرت، سقف ۲۰۰MB، نبود `storage/`، تطابق manifest↔ZIP، `requires_version`، نبود تداخل نام) | **۱۰/۱۰ PASS** |
| اسکن پس‌رفت روی ۷ نشانگر ۱.۶۲.۷۵ | **۰ پس‌رفت** (`access_group` 15/15، `user_access_group` 7/7، `notification_ticket_visibility` 2/2، `$requesterId` 2/2، `activity-audit` 3/3) |
| تحلیل ساختاری PHP با lexer (phply) روی ۸ فایل | **۰ ایراد**، توازن کامل `{}` `()` `[]` (کنترل سالم: `HEAD:index.php`) |
| شبیه‌سازی نصب: ۴۹ ارجاع `require`/`include` | **۰ ارجاع شکسته** |
| توابع حل‌نشده در payload | **۰** (`security_log_access_denied` تعریف شد، `logging_dashboard_render` حذف شد) |
| پوشش ستون پس از مهاجرت | `activity_logs` ۲۴ نیاز / ۱۷ پایه + ۸ افزوده ⇒ **کسری ندارد**؛ `system_logs` ۱۵ نیاز / ۸ پایه + ۸ افزوده ⇒ **کسری ندارد** |

## ۵) ترکیب بستهٔ خروجی

۳۶ فایل payload + ۷ مهاجرت + `manifest.json`. نسبت به بستهٔ خام این‌ها **حذف** شدند:

- `README.md` و `DELIVERY-MANIFEST-fa.txt` — نسخه‌های کوتاه‌شده بودند و نسخهٔ کامل مخزن را خراب می‌کردند.
- `AGENTS.md`، `docs/SKILL-MANIFEST-FA.md`، `tests/test_skill_manifest_static.py` — بایت‌به‌بایت با مخزن یکسان بودند (کپی بی‌اثر).
- `files/migrations/*` با شماره‌های قدیمی — جای درست مهاجرت ریشهٔ ZIP است تا updater اجرایشان کند؛ نسخهٔ شماره‌گذاری‌شده هم زیر `files/migrations/` نگهداری می‌شود.

## ۶) دستور نصب روی سامانه

۱. از پایگاه‌داده و پوشهٔ برنامه نسخهٔ پشتیبان بگیرید.
۲. مطمئن شوید نسخهٔ نصب‌شده دقیقاً `1.62.75` است (مقدار فایل `VERSION`).
۳. در پنل مدیریت → به‌روزرسانی سامانه، فایل ZIP را بارگذاری کنید.
۴. updater ابتدا ۷ مهاجرت ۰۲۴–۰۳۰ را به ترتیب اجرا و در `schema_updates` ثبت می‌کند، سپس فایل‌ها را کپی می‌کند.
۵. پس از نصب بررسی کنید: `VERSION` = `1.62.76`، صفحهٔ «گروه‌های دسترسی» در تنظیمات باز شود، و در «گزارش فعالیت» رخدادهای جدید با مقدار `request_id` ثبت شوند.

### موارد تأییدنشده (نیازمند Staging)

- در این محیط PHP CLI و MySQL در دسترس نبود؛ اعتبارسنجی ساختاری و ایستا انجام شد، اما `php -l` واقعی و اجرای مهاجرت‌ها انجام **نشده** است.
- تست `tests/test_logging_mysql_integration.php` نیازمند `ITSM_TEST_DSN` و پایگاه‌دادهٔ تستی است و اجرا نشده.
- داشبورد مانیتورینگ لاگینگ در این بسته وجود ندارد (فایلش در بستهٔ خام نبود)؛ اگر لازمش دارید باید جداگانه تحویل شود.
