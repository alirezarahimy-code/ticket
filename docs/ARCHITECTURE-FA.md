# نقشهٔ معماری سامانه — برای توسعه‌دهنده

تاریخ تهیه: ۱۴۰۵/۰۸/۰۳ (2026-10-25) · نسخهٔ مبنا: 1.62.11

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
  │            الگوی هر اکشن: csrf → require_permission → valid_choice/post_value
  │                          → prepare/execute → save_audit → flash → redirect
  └─ ناحیهٔ ۵: روتر صفحات (۳۸ صفحه) → render_header() … render_footer()
```

- همهٔ اکشن‌های POST بعد از موفقیت `redirect()` می‌کنند (الگوی PRG)؛ خطاها با `RuntimeException` بالا می‌روند و به `flash('error', …)` تبدیل می‌شوند.
- بانرهای کامنت `═══ ناحیهٔ n ═══` و `───── گروه اکشن: … ─────` داخل `index.php` برای پرش سریع گذاشته شده‌اند.

## ۲. فایل‌های ریشه و نقش هرکدام

| فایل | خط | نقش |
|---|---|---|
| `index.php` | ~6200 | روتر اصلی: ۷۴ اکشن POST + ۳۸ صفحه (بانرگذاری‌شده) |
| `bootstrap.php` | ~3300 | هستهٔ مشترک: DB، سشن، احراز هویت، رندر، ابزارها (TOC در ابتدای فایل) |
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

۱. احراز هویت (login) ۲. پروفایل/تم ۳. برند فیش غذا ۴. CD/DVD (۳ اکشن) ۵. پشتیبان‌گیری/به‌روزرسانی (۳) ۶. اعلان‌ها (۲) ۷. تصمیم‌های سرپرست (۳) ۸. امتیازدهی ۹. اموال و اسکن دامین (۹) ۱۰. چرخهٔ درخواست‌ها: create/cancel/decide-cancel/claim/reopen/reply/update (۷) ۱۱. خدمات/دسته/دانش (۷) ۱۲. تعطیلات (۴) ۱۳. ارتباط دارایی + عملیات گروهی (۲) ۱۴. ساختار پایه (۲) ۱۵. کاربران و اتصال غذا (۴) ۱۶. سازمان: save_org_node (شامل liaison)، undo، assign/remove manager، experts، assign_users، reset، ceo، unit، delete_unit، user_org، دو فرم ترمیم نقش (۱۳) ۱۷. نقش‌ها و دسترسی‌ها (۴) ۱۸. لاگ/LDAP (۳) ۱۹. افکت نمایشی + تنظیمات (۲)

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

## ۹. چک‌لیست «هر قابلیت جدید»

۱) کد + ترمیم اسکیما ۲) `permissions.php` و نقش‌های پیش‌فرض (اگر دسترسی جدید دارد) ۳) بخش راهنما در `help-guide.php` با `data-kw` (چت‌بات خودکار ایندکس می‌کند) ۴) مهاجرت idempotent ۵) تست ایستای جدید + ری‌پین تست‌های نسخه ۶) بستهٔ ZIP و انتشار طبق بند ۷ ۷) واژگان رابط کاربر: **«درخواست»** نه «تیکت».
