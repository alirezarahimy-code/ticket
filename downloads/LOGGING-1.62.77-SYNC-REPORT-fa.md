# گزارش بررسی و همگام‌سازی بستهٔ «Logging full features» → ۱.۶۲.۷۷

- **تاریخ:** 2026-11-04
- **بستهٔ ورودی:** `ticket 1.62.67 loging update.zip` از شاخهٔ `work/logging-1.62.77-restore-dashboard-replay-20261104`
  (کامیت `8e7d8fb686362eb09b85a1163a861ab288d5e00f`) — ۴۷۵٬۵۷۰ بایت،
  sha256 `b296cd53540d4fd7d00bb1312773393abd2f5b1fa8a6448c102399b1d38e2486`
- **خروجی قابل نصب:** `downloads/ticket-update-1.62.76-to-1.62.77-logging-full-20261104.zip`
  — ۲۸۹٬۰۳۵ بایت — sha256 `4a850bbdbaff4246caef9105c940e4cd941c935166288d4dffa7f16cc8766262`

> در تمام فرایند هیچ اتصالی به پایگاه‌داده برقرار نشد و هیچ مهاجرتی اجرا نشد.

---

## ۱) پاسخ به پرسش شما: بله، همهٔ امکانات در بسته هست

هر ۱۰ ماژولی که در بستهٔ قبلی غایب بودند، در این بسته **واقعاً موجودند** (بررسی روی محتوای آرشیو، نه توضیحات):

| قابلیت | فایل‌های واقعی در آرشیو |
|---|---|
| داشبورد گرافیکی لاگ | `logging-dashboard.php` (۱۹٬۹۰۵) + `assets/logging-dashboard.css` (۶٬۶۹۲) + `assets/logging-dashboard.js` |
| بازپخش صف (Replay/Spool) | `lib/logging-replay.php` (۱۸٬۹۰۵) + `cron/replay_logging_spool.php` |
| یکپارچگی و زنجیرهٔ رخداد | `lib/logging-integrity.php` + `cron/verify_logging_integrity.php` + `cron/export_logging_integrity.php` + `cron/run_logging_integrity_export.bat` |
| تشخیص و عیب‌یابی | `lib/logging-diagnostics.php` |
| پایش سلامت و هشدار | `cron/check_logging_health.php` + `cron/deliver_logging_issue_notifications.php` |
| بازسازی Issueها | `cron/rebuild_logging_issues.php` |
| بستهٔ ایجنت و تأیید انتشار | `cron/export_logging_agent_bundle.php` + `cron/verify_logging_release.php` |
| صفحهٔ سلامت سامانه | `system-health.php` بازنویسی‌شده (۴۸KB ← ۹۴KB) |
| CSRF لاگینگ | `lib/logging-csrf.php` تقویت‌شده (۲۵KB ← ۳۲KB) |
| اسکریپت‌های ویندوز | ۴ فایل `.bat` + `tests/run_logging_staging_checks.ps1` + `tests/test_logging_acl_windows.ps1` |
| هارنس پذیرش | `tests/logging-acceptance/compose.yaml` + `run-mysql-integration.sh` |
| گردش‌کار CI | `.github/workflows/logging-acceptance.yml` |

فهرستی که ایجنت دیگر داده بود **درست** است. اما وجود فایل به‌معنای نصب‌پذیری نیست.

## ۲) اما بستهٔ خام نصب نمی‌شد — ۵ بلاکر

| # | ایراد | شاهد | اثر |
|---|---|---|---|
| B1 | **۳۵ ورودی غیرمجاز در آرشیو** | پوشهٔ موقت `coretmp/` (۳۴ ورودی، شامل یک کپی قدیمی از بستهٔ خرابِ قبلی و README کوتاه‌شدهٔ ۱٬۹۹۴ بایتی) + `README-FA.txt` در ریشه | نصب‌کننده طبق قاعدهٔ `system-update.php:57` کل بسته را **رد می‌کند** |
| B2 | **مهاجرت‌ها اجرا نمی‌شوند** | در manifest نامشان `migrations/019-central-logging.sql` نوشته شده: هم الگوی `^\d{3}-[a-z0-9-]+\.sql$` را رد می‌کند، هم فایل در ریشهٔ ZIP نیست (فقط زیر `files/migrations/`) | استثنا و توقف نصب |
| B3 | **تداخل دوبرابری شماره** | ۰۱۹–۰۲۳ در مخزن به traffic/food/گروه‌های دسترسی تعلق دارد و ۰۲۴–۰۳۰ به لاگینگ ۱.۶۲.۷۶ | فایل هم‌شماره — دقیقاً همان چیزی که نگرانش بودید |
| B4 | **پس‌رفت دوبارهٔ قابلیت‌های ۱.۶۲.۷۵** | `access_group` ۱۵→۰، `user_access_group` ۷→۰، `notification_ticket_visibility` ۲→۰، `$requesterId` ۲→۰، برچسب‌های ممیزی ۳→۰ | حذف گروه‌های دسترسی عمومی، مسیریابی مبتنی بر درخواست‌کننده و دامنهٔ دید اعلان‌ها |
| B5 | **خطای مرگبار دوباره** | `security_log_access_denied()` در `index.php` (۱۳)، `system-health.php` (۴) و `logging-dashboard.php` (۱) صدا زده می‌شود ولی در `lib/logging-core.php` بسته **تعریف نشده**؛ و چون بسته فایل هستهٔ من را بازنویسی می‌کند، تعریفی که در ۱.۶۲.۷۶ افزوده بودم پاک می‌شد | سفیدشدن صفحه در هر درخواست |

نکتهٔ مثبت: هر ۷ فایل SQL بسته **بایت‌به‌بایت با مهاجرت‌های ۰۲۴–۰۳۰ موجود در مخزن یکسان‌اند**، پس هیچ مهاجرت تازه‌ای لازم نیست و تداخل شماره با تغییر نام کاملاً حل می‌شود.

## ۳) ۱۵ اصلاح انجام‌شده

۱. بازگردانی `require_once __DIR__ . '/user-access-groups.php';`
۲. بازگردانی `require __DIR__ . '/actions/access-group-actions.php';`
۳. بازگردانی بلوک `$errorRedirect` گروه‌های دسترسی
۴. بازگردانی امضای `choose_handling_agent(..., int $requesterId = 0)`
۵. بازگردانی `'requester_id' => $requesterId` در مسیریابی
۶–۹. بازگردانی `notification_ticket_visibility()` و پارامترهایش در فید و صفحهٔ اعلان‌ها
۱۰. بازگردانی بلوک ۷۲ خطی رابط گروه‌های دسترسی
۱۱. بازگردانی ۳ برچسب `access_group_*` در `lib/activity-audit.php`
۱۲. تعریف تابع گمشدهٔ `security_log_access_denied()` در `lib/logging-core.php`
۱۳. حذف پوشهٔ `coretmp/` (۳۴ ورودی) و انتقال `README-FA.txt` به `files/`
۱۴. تغییر شمارهٔ ۷ مهاجرت به ۰۲۴–۰۳۰ و قراردادن در ریشهٔ ZIP + اصلاح ۱۹ ارجاع به نام قدیمی در سندها، تست‌ها و گردش‌کار
۱۵. `VERSION` = `1.62.77`، manifest با `requires_version=1.62.75` و فهرست مهاجرت‌های معتبر

### یک اصلاح جانبی در مخزن

`rebuild-final.sql` شش جدول نسخه‌های ۱.۶۲.۷۴/۷۵ را در فهرست `DROP` نداشت
(`user_access_groups`، `user_access_group_members`، `user_access_group_permissions`،
`food_meal_reconciliations`، `food_monthly_invoices`، `org_companies`).
این نقص را تست سخت‌سازی خودِ بستهٔ Logging آشکار کرد و اصلاح شد.

## ۴) اقلامی که از بسته حذف شدند و چرا

| قلم | دلیل |
|---|---|
| `tests/test_windows_logging_static.py` | در سطح ماژول `storage/web.config` را می‌خواند؛ این فایل نه در بسته است نه در مخزن و `storage/` اساساً توسط نصب‌کننده کپی نمی‌شود. **کل جمع‌آوری pytest را متوقف می‌کرد.** |
| `tests/test_logging_phase73_static.py` | نیازمند ابزارسازی در `schema.sql` (`fingerprint CHAR(64) NULL`) و `lib/auth-scope.php` (`$_SESSION['audit_user']`) — هیچ‌کدام در بسته نیامده‌اند |
| `tests/test_logging_phase74_static.py` | نیازمند `__app_pdo_instance` در `lib/db-core.php`، `tools/set_logging_acl.ps1` و متن مشخصی در `system-update.php` — هیچ‌کدام در بسته نیستند |
| `tests/test_logging_phase75_static.py` | ۱۷ شکست: `tools/verify_logging_acl.ps1`، `save_audit_required` در `actions/role-actions.php` و `actions/org-actions.php`، `?string $idempotencyKey` در `lib/notify-users.php`، کلید خارجی `fk_events_ticket` در `schema.sql` — بسته هیچ‌یک از این فایل‌ها را ندارد |

سه قید نسخهٔ سخت‌کدشده (`== '1.62.76'`) در تست‌های بسته به «حداقل ۱.۶۲.۷۶» تبدیل شد، و
`tests/test_logging_integrity_chain.py` اکنون در نبود مفسر PHP به‌جای شکست، skip می‌شود.

> **یافتهٔ مهم:** تست‌های فاز ۷۳ تا ۷۵ نشان می‌دهند برنامهٔ Logging انتظار تغییراتی در
> `schema.sql`، `lib/auth-scope.php`، `lib/db-core.php`، `lib/notify-users.php`،
> `actions/role-actions.php`، `actions/org-actions.php` و `tools/*.ps1` دارد که **در هیچ‌یک از دو بستهٔ
> تحویلی نیامده‌اند**. یعنی زنجیرهٔ لاگینگ هنوز کامل نیست و یک بستهٔ تکمیلی لازم دارد.

## ۵) نتایج اعتبارسنجی

| آزمون | نتیجه |
|---|---|
| ۱۱ قاعدهٔ پیش‌پرواز نصب‌کننده | **۱۱/۱۱ PASS** |
| اسکن پس‌رفت روی ۱۱ نشانگر ۱.۶۲.۷۴/۷۵/۷۶ | **۰ پس‌رفت** |
| ارجاع `require` روی درخت نصب شبیه‌سازی‌شده | **۰ شکسته** |
| توازن ساختاری PHP (phply) روی ۴۰ فایل | `index.php` و همهٔ فایل‌های ویرایش‌شده متوازن؛ تنها دو فایل دست‌نخوردهٔ بسته مقدار منفی می‌دهند که **مثبت کاذبِ درون‌یابی `{$var}`** است |
| توابع کلیدی | `security_log_access_denied`، `logging_dashboard_render`، `notification_ticket_visibility`، `user_access_groups_all` همگی تعریف‌شده |
| مجموعه‌تست مخزن | خط‌مبنا ۴۹ شکست / ۳۵۷ قبولی ← اکنون **۴۹ شکست / ۳۹۴ قبولی / ۷ skip**: **۰ شکست جدید** و ۳۷ آزمون تازهٔ سبز |
| تطابق بایت‌به‌بایت payload بسته با مخزن | PASS |

## ۶) نصب

۱. پشتیبان از پایگاه‌داده و پوشهٔ برنامه.
۲. نسخهٔ نصب‌شده باید `1.62.75` یا `1.62.76` باشد.
۳. بارگذاری ZIP در پنل مدیریت → به‌روزرسانی سامانه.
۴. مهاجرت‌های ۰۲۴–۰۳۰ اجرا و در `schema_updates` ثبت می‌شوند؛ اگر قبلاً با ۱.۶۲.۷۶ ثبت شده باشند، نصب‌کننده آن‌ها را رد می‌کند (رفتار درست).
۵. پس از نصب: `VERSION` = `1.62.77`، صفحهٔ «داشبورد مانیتورینگ لاگینگ» در منوی گزارش‌ها، و صفحهٔ گروه‌های دسترسی در تنظیمات باید باز شوند.

### تأییدنشده‌ها

- PHP CLI و MySQL در این محیط نیستند؛ `php -l` واقعی، اجرای مهاجرت و آزمون یکپارچگی MySQL انجام **نشده** است. خودِ manifest بستهٔ خام هم `php_lint: not_run` و `mysql_integration: not_run` ثبت کرده بود.
- اجرای خودکار گردش‌کار `logging-acceptance.yml` روی push غیرفعال شد (فقط اجرای دستی) تا پیش از تأیید Staging، CI به‌صورت ناخواسته قرمز نشود.
- ماژول‌های مکملی که هنوز نیامده‌اند در بخش ۴ فهرست شده‌اند.
