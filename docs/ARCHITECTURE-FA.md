# نقشهٔ معماری سامانه — برای توسعه‌دهنده

تاریخ تهیه: ۱۴۰۵/۰۸/۰۴ (2026-10-26) · نسخهٔ مبنا: 1.62.21

این سند «نقشهٔ راهِ کد» است: هر چیزی کجاست و یک درخواست چه مسیری را طی می‌کند. قبل از هر تغییری اول این سند، بعد `docs/HANDOFF-FA.md` (قواعد ثابت) را بخوانید.

---

## ۱. جریان یک درخواست (از ورود تا خروج)

```
مرورگر → index.php
  ├─ require bootstrap.php   (سشن، PDO، احراز هویت، ۱۴۶ تابع کمکی)
  ├─ ناحیهٔ ۱: logout (قبل از هر چیز)
  ├─ ناحیهٔ ۲: تعیین $page از ?page=... + پیش‌شرط‌ها (تغییر رمز اجباری و …)
  ├─ ناحیهٔ ۳: ماژول‌های خودگردان — food-ticket / food-order / traffic-control
  │            (این‌ها POST و رندر خودشان را کامل خودشان انجام می‌دهند)
  ├─ ناحیهٔ ۴: if (REQUEST_METHOD === 'POST') → ۷۴ اکشن
  │            (برش‌های فاز ۲ — require در همان نقطه، همان حوزهٔ متغیرها:
  │             سازمان → actions/org-actions.php | درخواست‌ها → actions/ticket-actions.php
  │             اموال/اسکن دامین → actions/asset-actions.php | خدمات/دانش → actions/service-actions.php؛ شرح فارسی بخش‌ها در سرآیند هر فایل)
  │            الگوی هر اکشن: csrf → require_permission → valid_choice/post_value
  │                          → prepare/execute → save_audit → flash → redirect
  └─ ناحیهٔ ۵: روتر صفحات (۳۸ صفحه) → render_header() … render_footer()
```

- همهٔ اکشن‌های POST بعد از موفقیت `redirect()` می‌کنند (الگوی PRG)؛ خطاها با `RuntimeException` بالا می‌روند و به `flash('error', …)` تبدیل می‌شوند.
- بانرهای کامنت `═══ ناحیهٔ n ═══` و `───── گروه اکشن: … ─────` داخل `index.php` برای پرش سریع گذاشته شده‌اند.

## ۲. فایل‌های ریشه و نقش هرکدام

| فایل | خط | نقش |
|---|---|---|
| `index.php` | ~3443 | روتر اصلی: ۳۸ صفحه + رسیدگی POST (از ۱.۶۲.۲۰ هیچ اکشن درون‌خطی ندارد؛ همه در actions/) |
| `actions/org-actions.php` | ~560 | ۱۳ اکشن چارت سازمانی (برش ۱) — شرح فارسی در سرآیند |
| `actions/ticket-actions.php` | ~640 | ۷ اکشن چرخهٔ درخواست‌ها (برش ۲) — شرح فارسی در سرآیند |
| `actions/asset-actions.php` | ~420 | ۱۰ اکشن اموال/اسکن دامین (برش ۳) — شرح فارسی در سرآیند |
| `actions/service-actions.php` | ~165 | ۷ اکشن خدمات/دسته‌ها/پایگاه دانش (برش ۴) — شرح فارسی در سرآیند |
| `actions/role-actions.php` | ~135 | ۴ اکشن نقش‌های کلیدی/دسترسی‌ها (برش ۵) — شرح فارسی در سرآیند |
| `actions/settings-actions.php` | ~245 | ۵ اکشن نگهداری لاگ/LDAP و تنظیمات (برش ۵) — شرح فارسی در سرآیند |
| `actions/supervisor-actions.php` | ~155 | ۴ اکشن تصمیم‌های سرپرست و امتیازدهی (برش ۶) — شرح فارسی در سرآیند |
| `actions/holiday-actions.php` | ~95 | ۴ اکشن تقویم و تعطیلات (برش ۶) — شرح فارسی در سرآیند |
| `actions/account-actions.php` | ~125 | ۳ اکشن ورود/پروفایل/تم (برش ۷) — شرح فارسی در سرآیند |
| `actions/food-brand-actions.php` | ~45 | ۱ اکشن برند فیش غذا (برش ۷) — شرح فارسی در سرآیند |
| `actions/cd-dvd-actions.php` | ~40 | ۳ اکشن آرشیو CD/DVD (برش ۷) — شرح فارسی در سرآیند |
| `actions/notification-actions.php` | ~32 | ۲ اکشن اعلان‌ها (برش ۷) — شرح فارسی در سرآیند |
| `actions/bulk-actions.php` | ~118 | ۲ اکشن ارتباط دارایی/عملیات گروهی (برش ۸) — شرح فارسی در سرآیند |
| `actions/user-structure-actions.php` | ~367 | ۶ اکشن ساختار پایه و کاربران (برش ۸) — شرح فارسی در سرآیند |
| `actions/backup-actions.php` | ~55 | ۳ اکشن پشتیبان‌گیری/به‌روزرسانی سامانه (برش ۹، ۱.۶۲.۲۰) — شرح فارسی در سرآیند |
| `bootstrap.php` | ~64 | بارکنندهٔ هسته (از ۱.۶۲.۲۰): سر فایل (APP_ROOT، سشن، config) + require هشت ماژول lib/ به ترتیب قدیمی |
| `lib/db-core.php` | ~565 | هسته، پیکربندی (cfg)، AppPDO، db_*، نرخ ورود/API، نسخه، تم، redirect |
| `lib/logging-csrf.php` | ~400 | flash، لاگ سیستمی و ثبت خطای فارسی، CSRF |
| `lib/auth-scope.php` | ~140 | current_user، require_*، دامنهٔ دید ticket_scope/asset_scope |
| `lib/settings-effects.php` | ~375 | setting، افکت‌های نمایشی، نقش‌های کلیدی |
| `lib/ldap.php` | ~315 | پیکربندی، تشخیص و همگام‌سازی دامین (LDAP) |
| `lib/tickets-sla.php` | ~620 | شمارهٔ درخواست، تقویم شمسی، SLA/OLA، تاریخچهٔ دارایی |
| `lib/notify-users.php` | ~435 | اعلان‌ها، ورود دامین (ldap_authenticate)، upsert کاربران |
| `lib/activity-audit.php` | ~390 | ثبت فعالیت، حسابرسی save_audit، رویداد درخواست‌ها |
| `permissions.php` | — | تعریف دسترسی‌ها + نقش‌های پیش‌فرض (هر قابلیت جدید باید اینجا هم ثبت شود) |
| `session_bootstrap.php` | — | شروع امن سشن (مشترک بین index و install) |
| `organization.php` | — | چارت سازمانی: org_units/org_unit_managers، واحد مستقل (liaison)، همگام‌سازی نقش‌ها |
| `food-ticket.php` + `food-ticket-*.php` | ~4800+ | «سامانه مدیریت غذا» — زیرماژول‌ها: engine، runtime، groups (ثبت دستی فیش)، templates، netprint، import، web-host، env، order-source |
| `food-order.php`, `food-order-import.php` | — | سفارش غذا و ورود اکسل |
| `traffic-control.php`, `traffic-import.php` | — | کنترل تردد + ورود اکسل (مقصدها و ۱–۳ تأییدکننده) |
| `chatbot.php` | ~3400 | چت‌بات (دانش را خودکار از `help-guide.php` ایندکس می‌کند: h2/h3 + `data-kw`) |
| `help-guide.php` | — | راهنمای فارسی — هر قابلیت جدید باید اینجا بخش و کلیدواژه بگیرد |
| `inventory.php`, `asset-*.php`, `domain-scan.php` | — | اموال/دارایی‌ها، پروفایل دارایی، اسکن دامین |
| `backup.php`, `system-update.php`, `system-health.php` | — | پشتیبان‌گیری، نصب بستهٔ به‌روزرسانی (قرارداد ZIP پایین ↓)، سلامت |
| `local-accounts.php`, `food-personnel-link.php` | — | حساب‌های محلی و اتصال پرسنل غذا |
| `cd-dvd.php`, `admin-layout-design.php`, `global-search.php`, `xlsx-writer.php` | — | آرشیو رسانه، طراحی چیدمان، جستجوی سراسری، خروجی XLSX (همهٔ گزارش‌ها XLSX) |
| `governance.php`, `ola.php` | — | صفحه‌های مستقل (require مستقیم bootstrap؛ خارج از روتر index) |
| `install.php`, `install-empty-db.sql`, `schema.sql` | — | نصب تازه؛ هر ستون جدید باید در هر دو SQL + ترمیم اسکیما (`*_ensure_schema`) بیاید |
| `cron/prune_activity_logs.php` | — | کران هرس لاگ (از `ops_low_value_tables_prune` استفاده می‌کند) |

## ۳. گروه‌های اکشن POST در index.php (۷۴ اکشن)

به ترتیب فایل — هر گروه در کد بانر دارد:

۱–۲. ورود + پروفایل/تم: ۳ اکشن — **در `actions/account-actions.php`** ۳. برند فیش غذا — **در `actions/food-brand-actions.php`** ۴. CD/DVD: ۳ اکشن — **در `actions/cd-dvd-actions.php`** ۵. پشتیبان‌گیری/به‌روزرسانی: ۳ اکشن — **در `actions/backup-actions.php`** (برش ۹) ۶. اعلان‌ها: ۲ اکشن — **در `actions/notification-actions.php`** ۷–۸. تصمیم‌های سرپرست + امتیازدهی: ۴ اکشن — **در `actions/supervisor-actions.php`** ۹. اموال و اسکن دامین: ۱۰ اکشن — **در `actions/asset-actions.php`** ۱۰. چرخهٔ درخواست‌ها: ۷ اکشن — **در `actions/ticket-actions.php`** ۱۱. خدمات/دسته/دانش: ۷ اکشن — **در `actions/service-actions.php`** ۱۲. تعطیلات: ۴ اکشن — **در `actions/holiday-actions.php`** ۱۳. ارتباط دارایی + عملیات گروهی: ۲ اکشن — **در `actions/bulk-actions.php`** ۱۴–۱۵. ساختار پایه + کاربران: ۶ اکشن — **در `actions/user-structure-actions.php`** ۱۶. سازمان: ۱۳ اکشن — **در فایل جدای `actions/org-actions.php`** (save_org_node شامل liaison، undo، assign/remove manager، experts، assign_users، reset، ceo، unit، delete_unit، user_org، دو فرم ترمیم نقش) ۱۷. نقش‌ها و دسترسی‌ها: ۴ اکشن — **در `actions/role-actions.php`** ۱۸–۱۹. لاگ/LDAP + افکت/تنظیمات: ۵ اکشن — **در `actions/settings-actions.php`**

## ۴. صفحه‌ها (۳۸ مقدار `?page=`)

`dashboard` (پیش‌فرض)، `new-ticket`، `ticket`، `queue`، `supervisor`، `reports`، `analytics`، `search`، `services`، `knowledge`، `help`، `notifications`، `settings`، `profile`، `change-password`، `organization`، `holidays`، `local-accounts`، `backup`، `system-health`، `logs`، `activity-log`، `audit`، `inventory`، `inventory-diagnostics`، `assets`، `asset`، `asset-history`، `domain-scan`، `cd-dvd`، `cd-dvd-history`، `governance`، `chatbot-admin`، `chatbot-api`، `food-ticket`، `food-order`، `food-personnel-link`، `traffic-control` (+`traffic-control-print`).

## ۵. دارایی‌های مرورگر (assets/)

| فایل | مصرف‌کننده | نکته |
|---|---|---|
| `style.css` | همهٔ صفحات | نسخهٔ کش فعلی `?v=3.4.45` — هر تغییر = افزایش نسخه در `index.php` |
| `app.js` | سراسری | تم شب/روز، منو، ابزارهای عمومی |
| `organization.js` | صفحهٔ سازمان | `?v=7`؛ دادهٔ ورودی: `window.PERSIAN_ORG_DATA` |
| `food-menu.js/.css`, `food-order.js` | غذا | — |
| `traffic-control.js` | تردد | `?v=12` |
| `triangle-mesh.js`, `particle-network.js` | افکت نمایشی | فعال‌سازی صفحه‌ای از تنظیمات |
| `admin-layout-design.js`, `cd-dvd.js` | ماژول‌های خاص | — |

**قاعدهٔ کش:** هر تغییر JS/CSS باید `?v=` را در `index.php` (یا میزبان مربوط) بالا ببرد وگرنه کاربر نسخهٔ کهنه می‌گیرد.

## ۶. پایگاه داده

- نصب تازه: `schema.sql` / `install-empty-db.sql` (هر دو باید همگام باشند).
- ارتقا: `migrations/NNN-نام.sql` — **idempotent**، بدون حذف داده؛ در `schema_updates` ددوپ می‌شود.
- شبکهٔ ایمنی: توابع `*_ensure_schema()` (bootstrap/organization/food) ستون‌های جاافتاده را در زمان اجرا ترمیم می‌کنند.

## ۷. قرارداد بستهٔ به‌روزرسانی (system-update.php)

- ZIP فقط این ورودی‌ها را می‌پذیرد: `manifest.json` | `migrations/*.sql` | `files/...` (هر چیز دیگر = خطا).
- مهاجرت‌های manifest باید **در ریشهٔ ZIP** (`migrations/…`) باشند؛ کپی `files/migrations/…` هم برای کامل‌بودن مخزن سرور.
- هر انتشار: پوشهٔ `updates/<تاریخ-نام>/` با `manifest.json` + `CHANGE-REPORT-FA.md`؛ ZIP یکتا + `.sha256` در `downloads/`؛ صفحهٔ `downloads/index.html` فقط آخرین بسته؛ تگ و GitHub Release «Latest». (جزئیات: `docs/DOWNLOAD-PUBLISHING-POLICY-FA.md` و `/home/user/UPDATE-RULES-FA.md`)

## ۸. تست‌ها و اعتبارسنجی

- `tests/` (pytest): تست‌های ایستا — پین نسخه/manifest/بسته + قواعد بسته‌بندی. قبل از قضاوت: `rm -rf tests/__pycache__ .pytest_cache`.
- خط پایهٔ فعلی: **۲۰۶ قبولی + ۱۸ زیرتست + ۵ خطای شناخته‌شدهٔ قدیمی** (فهرست در HANDOFF — دست نزنید مگر خواسته شود).
- اعتبارسنجی PHP بدون سرور: php-wasm (`token_get_all`)؛ JS: `node --check` + jsdom.

## ۹. قاعدهٔ برش‌های فاز ۲ (تفکیک index.php)

هر برش باید این شرایط را داشته باشد (الگوی برش ۱ — اکشن‌های سازمان):
۱) بلوک قبل از انتقال اسکن شود: بدون `return`/`function`/`goto`/`break`/`continue`/`__DIR__`/`__FILE__`/`?>` در سطح بلوک؛ آکولادها متوازن.
۲) انتقال **بایت‌به‌بایت** + `require` در همان نقطه؛ اثبات ماشینی بازسازی فایل قبلی.
۳) محافظ `defined('APP_ROOT')` در ابتدای فایل جدید.
۴) تست ایستای مخصوص برش + به‌روزرسانی تست‌های پین‌شده؛ هر برش یک نسخه و آزمایش روی سرور تست قبل از اصلی.

## ۱۰. چک‌لیست «هر قابلیت جدید»

۱) کد + ترمیم اسکیما ۲) `permissions.php` و نقش‌های پیش‌فرض (اگر دسترسی جدید دارد) ۳) بخش راهنما در `help-guide.php` با `data-kw` (چت‌بات خودکار ایندکس می‌کند) ۴) مهاجرت idempotent ۵) تست ایستای جدید + ری‌پین تست‌های نسخه ۶) بستهٔ ZIP و انتشار طبق بند ۷ ۷) واژگان رابط کاربر: **«درخواست»** نه «تیکت».
