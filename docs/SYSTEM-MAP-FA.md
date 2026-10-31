# نقشهٔ کامل سامانه (SYSTEM MAP) — مرجع ناوبری ۰ تا ۱۰۰

**تاریخ تهیه:** ۲۰۲۶-۱۰-۲۷ · **شاخه:** `arena/38fea5b6-ticket`
**نسخهٔ سامانه:** `1.62.59` (هم‌تراز با بستهٔ منتشرشدهٔ `ticket-update-1.62.59-cumulative-org-card-drag-20261031`؛ آخرین تگ رسمی `v1.62.36` روی `e2afc13`)
**منبع حقیقت:** `tools/build_system_map.py` → `docs/system-map/*.tsv`
**تست تازگی:** `python3 tools/build_system_map.py --check`

> ⚠️ **چرا این سند وجود دارد:** اسناد قدیمی `ARCHITECTURE.md` (۲۰۲۶-۱۰-۰۹، شاخهٔ `arena-updates`) و
> `docs/ARCHITECTURE-FA.md` (پایهٔ ۱.۶۲.۲۰) **ناهماهنگاند**. مثال: `ARCHITECTURE.md` میگوید
> `bootstrap.php` حدود ۲۲۶۱ خط و `index.php` حدود ۴۶۱۹ خط است؛ اندازهٔ واقعی الان **۶۲** و **۳۴۵۴** خط است.
> **این سند + ایندکس ماشینی جایگزین بهروز آنهاست**؛ اعدادش از روی کد کنونی استخراج شدهاند.

---

## ۰. نحوهٔ استفاده (مهمترین بخش)

### 🚀 شروع یک جلسهٔ جدید — یک دستور کافی است

```bash
python3 tools/session-boot.py
```

این «دکمهٔ استارت» مانیفست ۳۱ مهارت + نقشه + قواعد ثابت کاربر + ایندکسها +
نتیجهٔ تستها را یکجا چاپ میکند و ایجنت آن را بهعنوان چارچوب عملیاتی همان جلسه
میپذیرد. اگر کاربر گفت «**بهروز شو**» یا «**بوت کن**» یعنی همین دستور را بزن.
راهنمای انسانی: [`docs/SESSION-BOOT-FA.md`](SESSION-BOOT-FA.md).
فایلهای بارگذاری خودکار: `AGENTS.md`, `CLAUDE.md`,
`.github/copilot-instructions.md`, `.cursor/rules/arena-boot.mdc`, `.cursorrules`.

### ابزار جستجوی سریع — «فلان جای سامانه کجاست؟»

```bash
python3 tools/find-in-system.py "ثبت دستی حضور"     # جستجوی آزاد فارسی/انگلیسی
python3 tools/find-in-system.py "فیش چاپ نشد"
python3 tools/find-in-system.py --symbol food_ticket_mark_delivered_on_print
python3 tools/find-in-system.py --page food-ticket
python3 tools/find-in-system.py --action save_org_node
python3 tools/find-in-system.py --table food_orders
python3 tools/find-in-system.py --package 1.62.36
python3 tools/find-in-system.py --stats
```

### ایندکسهای ماشینی در `docs/system-map/`

| فایل | محتوا | تعداد |
|---|---|---|
| `FILES.tsv` | هر فایل + دسته + خط + حجم | ۸۳۹ فایل / ۱۶۹٬۹۲۴ خط |
| `SYMBOLS.tsv` | تابع/کلاس/ثابت → `فایل:خط` | ۴٬۳۵۲ نماد |
| `ROUTES.tsv` | صفحات، اکشنهای POST، endpointهای API | ۳۶۷ روت |
| `TABLES.tsv` | جدول SQL → محل تعریف | ۷۰ جدول / ۴۰۹ تعریف |
| `UPDATES.tsv` | بستههای بهروزرسانی | ۱۱۰ بسته |
| `FEATURES.tsv` | ایندکس معکوس «ویژگی ↔ فایل» | ۳۶ حوزه |
| `summary.json` | آمار کلی | — |

**قاعدهٔ تازگی:** بعد از هر تغییر ساختاری، `python3 tools/build_system_map.py` را اجرا کن.
تست `tests/test_system_map_static.py` جلوی کهنهشدن نقشه را میگیرد.

### صداقت دربارهٔ حافظه

من **نمیتوانم ۱۶۸ هزار خط را در حافظهٔ رفتاریام نگه دارم**؛ آن حافظه محدود و فقط همین جلسه است.
اما **این نقشه پایدار است** و من قبل از هر کار آن را لود میکنم. پس «یادگیری» در **سیستم** انجام شده،
نه در سر من. اگر جلسهٔ جدیدی شروع شد، کافی است بگویی: «نقشه رو بخون» → `AGENTS.md` →
`docs/SYSTEM-MAP-FA.md` → `tools/find-in-system.py`.

---

## ۱. آمار واقعی سامانه (اندازهگیری شده)

| مورد | مقدار |
|---|---|
| فایلهای tracked در Git | ۱٬۱۰۶ |
| فایلهای متنی ایندکسشده | ۸۳۹ (باینریها و ZIPها کنار گذاشته شدند) |
| کل خطوط ایندکسشده | ۱۶۹٬۹۲۴ |
| فایل PHP | ۲۱۰ فایل / ۹۲٬۶۰۲ خط |
| فایل JS | ۶۱ / ۲۶٬۷۷۴ خط |
| فایل CSS | ۲۶ / ۷٬۶۴۸ خط |
| فایل SQL | ۸۷ / ۹٬۲۴۴ خط |
| تست Python | ۷۷ / ۹٬۱۸۲ خط |
| توابع/کلاسها | ۴٬۳۵۲ |
| صفحات `?page=` | **۴۰** |
| اکشنهای POST | **۹۶** |
| endpointهای API | **۸۳** |
| جداول دیتابیس | **۷۰** (در `schema.sql`) |
| migration | ۱۳ (`migrations/`) + ۴۲ فایل `upgrade-*.sql` قدیمی |
| بستههای آپدیت | ۱۱۰ پوشه در `updates/` |
| کلیدهای تنظیمات | ۹۰ کلید `setting('...')` |
| فونت محلی | ۱۰ فونت `woff2` در `assets/fonts/` |

---

## ۲. نمای کلی معماری

```
                    ┌──────────────────────────────────────┐
                    │  MySQL (persian_ticketing) — 70 جدول │
                    └──────────▲───────────────▲───────────┘
                               │               │
        ┌──────────────────────┴───┐   ┌───────┴──────────────────────────┐
        │  [ITSM] درخواست و دارایی │   │  [FOOD] سامانه مدیریت غذا        │
        │  index.php (روتر تکفایلی)│   │  food-ticket.php + food-order.php│
        │  bootstrap.php → lib/    │   │  + ۱۲ ماژول food-ticket-*.php   │
        │  actions/ (۱۵ فایل)      │   │  + food-ticket-web/ (پنل فرانت)  │
        └──────────────────────────┘   └───────▲──────────────────────────┘
                                               │ محرک چاپ
                                  ┌────────────┴─────────────┐
                                  │ فایل Access تردد (TENTER)│
                                  │ تنها منبع خارجی باقیمانده│
                                  └──────────────────────────┘
```

- **ITSM**: درخواست، دارایی، سازمان، تردد عمومی، CD/DVD، گزارش، ادمین.
- **FOOD**: سفارش غذا، تطبیق تردد با سفارش، صف چاپ فیش، چاپگر، گروهها، ثبت دستی.
- **اشتراک واقعی**: دیتابیس MySQL + `bootstrap.php`. کد مشترک جدی بین دو بخش وجود ندارد.
- ❗ **وابستگی Access سفارش حذف شده**؛ فقط Access تردد (TENTER) باقی است.

---

## ۳. جریان یک درخواست وب (از ورود تا خروج)

`index.php` (۳۴۵۴ خط) روتر تکفایلهای است — **MVC استفاده نمیشود**.

```
مرورگر → index.php
  ├─ require bootstrap.php      → سشن + config + ۸ ماژول lib/ (به ترتیب، تغییر ترتیب ممنوع)
  ├─ require ۱۹ فایل فرعی       → inventory, asset-profile, domain-scan, organization,
  │                                chatbot, food-ticket, traffic-control, system-health, ...
  ├─ ناحیهٔ ۱: logout
  ├─ ناحیهٔ ۲: $page = $_GET['page'] (پیشفرض dashboard/login) + پیششرط تغییر رمز
  ├─ ناحیهٔ ۳: ماژولهای خودگردان — food-ticket / food-order / traffic-control
  │            (POST و رندر خودشان را کامل انجام میدهند؛ `?food_api=` / `?traffic_api=`)
  ├─ ناحیهٔ ۴: if (REQUEST_METHOD === 'POST') → ۹۶ اکشن
  │            require در همان نقطه از actions/*.php (همان حوزهٔ متغیرها)
  │            الگوی هر اکشن: csrf → require_permission → valid_choice/post_value
  │                          → prepare/execute → save_audit → flash → redirect (PRG)
  └─ ناحیهٔ ۵: روتر صفحات (۴۰ صفحه) → render_header() … render_footer()
```

**نکات کلیدی:**
- خطاها با `RuntimeException` بالا میروند و به `flash('danger', …)` تبدیل میشوند؛ `catch` در `index.php:1322` حدود.
- بانرهای کامنت `═══ ناحیهٔ n ═══` و `───── گروه اکشن: … ─────` برای پرش سریعاند.
- `render_header()` در `index.php:670`، منوی اصلی در همان حوالی، `render_footer()` در `index.php:852`.
- تعیین `$page` در `index.php:911`.

---

## ۴. نقشهٔ پوشهها

| پوشه | تعداد | نقش |
|---|---|---|
| `actions/` | ۱۵ | گروههای اکشن POST (برشهای فاز ۲) |
| `lib/` | ۸ | هستهٔ مشترک (پیکربندی، لاگ، احراز، LDAP، SLA، اعلان، حسابرسی) |
| `cron/` | ۶ + ۱ | کرونها و Worker همیشهروشن |
| `api/` | ۱ | `inventory.php` — API برداشت اینونتوری |
| `assets/` | ۲۵ | CSS/JS مرورگر و فونتهای محلی |
| `food-ticket-web/` | ۳۳ | پنل فرانت غذا (HTML تکصفحهای + JS) |
| `migrations/` | ۱۳ | مهاجرتهای idempotent |
| `tests/` | ۹۱ | تستهای ایستا (Python) + تستهای PHP |
| `tools/` | ۸۰ | ابزارهای CLI، عیبیابی، بنچمارک، نصب Worker |
| `docs/` | ۱۳ | مستندات فارسی |
| `app/` + `routes/` | ۲۷ | ⚠️ **لایهٔ MVC بلااستفاده** — هیچ فایلی requireشان نمیکند |
| `updates/` | ۳۱۰ فایل | تاریخچهٔ ۱۱۰ بستهٔ انتشار |
| `downloads/` | ۲۰۵ فایل | ZIPها و SHA256ها |
| `benchmark-results/` | ۱۳ | نتایج بنچمارک |
| `client-agent/` | ۱ | `Collect-Inventory.ps1` |

---

## ۵. فایلهای ریشه (اندازه واقعی)

| فایل | خط | نقش |
|---|---:|---|
| `food-ticket.php` | ۴۸۲۱ | قلب سامانه مدیریت غذا: تنظیمات، API، صفحهٔ ادمین، پایش، تست، چاپ |
| `index.php` | ۳۴۵۴ | روتر اصلی ITSM |
| `chatbot.php` | ۳۴۱۲ | چتبات سخنگو (دانش را از `help-guide.php` ایندکس میکند) |
| `food-order.php` | ۳۲۸۲ | سفارش غذا (کاربر، نیابت، لغو، تقویم) |
| `food-ticket-groups.php` | ۲۹۸۱ | گروههای غذایی، نماینده، غیبت، **ثبت دستی حضور/فیش** |
| `food-ticket-templates.php` | ۲۳۵۴ | قالب فیش، طراحی، لوگو |
| `food-ticket-runtime.php` | ۱۳۲۶ | صف چاپ، وضعیت تحویل، قوانین وعده |
| `traffic-control.php` | ۱۳۲۲ | کنترل تردد عمومی |
| `food-ticket-netprint.php` | ۱۲۹۸ | چاپ TCP 9100، کالیبراسیون، حاشیه |
| `domain-scan.php` | ۱۲۷۶ | اسکن دامنه / شبکه |
| `asset-profile.php` | ۱۱۴۷ | شناسنامهٔ فنی دارایی |
| `food-ticket-engine.php` | ۱۰۷۰ | موتور پایش: خواندن TENTER → تطبیق → ثبت رویداد → چاپ |
| `traffic-import.php` | ۱۰۲۸ | ورود اکسل سوابق تردد |
| `cd-dvd.php` | ۸۸۴ | آرشیو CD/DVD |
| `permissions.php` | ۸۴۳ | نقشها، کاتالوگ دسترسیها، پیشفرضها |
| `inventory.php` | ۸۲۴ | اینونتوری / برداشت سختافزاری |
| `food-personnel-link.php` | ۸۱۷ | اتصال کد پرسنلی غذا به حساب دامین |
| `system-health.php` | ۷۶۶ | سلامت سامانه، لاگ خوانا، خروجی امن |
| `help-guide.php` | ۶۵۸ | راهنمای فارسی (منبع دانش چتبات) |
| `food-ticket-import.php` | ۵۷۳ | ایمپورت لیست کارکنان از Excel |
| `governance.php` | ۵۲۱ | مدیریت تغییر و مشکل (صفحهای مستقل) |
| `food-order-import.php` | ۴۷۶ | ورود اکسل سفارش |
| `organization.php` | ۴۶۷ | چارت سازمانی |
| `install.php` | ۳۶۷ | نصب تازه، ساخت `config.php` و `installed.lock` |
| `admin-layout-design.php` | ۳۶۱ | طراحی چیدمان صفحه |
| `backup.php` | ۲۳۹ | پشتیبانگیری / بازیابی |
| `food-ticket-env.php` | ۲۳۴ | مقادیر سازگاری (⚠️ شامل رمز پیشفرض Access بهصورت Base64) |
| `xlsx-writer.php` | ۲۲۰ | خروجی XLSX همهٔ گزارشها |
| `local-accounts.php` | ۲۱۶ | حسابهای محلی |
| `food-ticket-web-host.php` | ۲۰۰ | هاست پنل وب غذا |
| `system-update.php` | ۱۸۸ | نصب بستهٔ بهروزرسانی |
| `asset-names.php` | ۱۳۱ | نام نمایشی کامپیوترها |
| `global-search.php` | ۱۲۱ | جستجوی سراسری |
| `ola.php` | ۱۱۱ | سیاستهای OLA |
| `food-ticket-order-source.php` | ۹۶ | نقشهٔ سفارش روز (فقط MySQL داخلی) |
| `asset-inventory-page.php` | ۸۹ | صفحهٔ اینونتوری |
| `bootstrap.php` | ۶۲ | بارکنندهٔ هسته (نقشهٔ خانوادهٔ توابع در سرآیندش) |
| `session_bootstrap.php` | ۵۷ | شروع امن سشن (۳۶۵ روز، HttpOnly, SameSite=Lax) |
| `chatbot-tts-lexicon.php` | ۱۴ | واژهنامهٔ تلفظ TTS |

---

## ۶. هستهٔ مشترک `lib/` — نقشهٔ خانوادهٔ توابع

`bootstrap.php` این هشت فایل را **دقیقاً به همین ترتیب** لود میکند (تغییر ترتیب ممنوع):

| فایل | خط | خانوادهٔ توابع | جستجو با |
|---|---:|---|---|
| `lib/db-core.php` | ۵۷۸ | پیکربندی `cfg`، `AppPDO`, `db_*`، نرخ ورود/API، نسخه، تم، `redirect` | `function db_`, `function cfg` |
| `lib/logging-csrf.php` | ۴۲۴ | `flash`, `system_log`, `app_error_log`, `csrf_*` | `function csrf_`, `function system_` |
| `lib/auth-scope.php` | ۱۴۳ | `current_user`, `require_*`, دامنهٔ دید `ticket_scope` / `asset_scope` | `function current_user` |
| `lib/settings-effects.php` | ۳۸۵ | `setting`, افکتهای `triangle_*` / `particle_*`, نقشهای کلیدی | `function setting` |
| `lib/ldap.php` | ۳۲۹ | پیکربندی و همگامسازی دامین `ldap_*` | `function ldap_` |
| `lib/tickets-sla.php` | ۶۳۶ | شمارهٔ درخواست `ticket_number_*`, تقویم `jalali_*`, SLA/OLA | `function jalali_`, `function ticket_` |
| `lib/notify-users.php` | ۴۴۸ | اعلانها `notify_*` / `notification_*`, ورود دامین, upsert کاربران | `function notify_` |
| `lib/activity-audit.php` | ۴۰۳ | ثبت فعالیت `activity_*`, حسابرسی `save_audit`, رویداد درخواست | `function activity_` |

`permissions.php` جداگانه قبل از اینها لود میشود و ۳۳+ تابع دارد: `permission_catalog`,
`permission_defaults`, `user_can`, `require_permission`, `role_permission_codes`, ...

---

## ۷. اکشنهای POST — `actions/` (۱۵ فایل، ۹۶ اکشن)

| فایل | خط | گروه |
|---|---:|---|
| `actions/ticket-actions.php` | ۶۴۶ | چرخهٔ درخواستها (۷ اکشن): create_ticket, reply, claim_ticket, change_transition, rate_ticket, cancel_ticket, decide_ticket_cancellation |
| `actions/org-actions.php` | ۵۶۵ | چارت سازمانی (۱۳ اکشن): save_org_node, save_org_unit, delete_org_unit, assign/remove_org_manager, reset_org_structure, ... |
| `actions/asset-actions.php` | ۴۱۸ | اموال و اسکن دامین (۱۰): domain_scan_*, ping_assets, collect_inventory, asset_profile_selftest |
| `actions/user-structure-actions.php` | ۳۶۷ | ساختار پایه و کاربران (۶): add_department, save_department_members, save_inventory_form |
| `actions/settings-actions.php` | ۲۴۵ | لاگ/LDAP و تنظیمات (۵): save_settings, diagnose_ldap, sync_ldap_users, prune_activity_logs |
| `actions/service-actions.php` | ۱۶۵ | خدمات/دسته/دانش (۷): add_service, edit_service, add_knowledge, ... |
| `actions/supervisor-actions.php` | ۱۵۴ | تصمیمهای سرپرست و امتیازدهی (۴) |
| `actions/role-actions.php` | ۱۳۵ | نقشهای کلیدی و دسترسیها (۴) |
| `actions/account-actions.php` | ۱۲۴ | ورود / پروفایل / تم (۳) |
| `actions/bulk-actions.php` | ۱۱۸ | ارتباط دارایی + عملیات گروهی (۲) |
| `actions/holiday-actions.php` | ۹۳ | تقویم و تعطیلات (۴) |
| `actions/backup-actions.php` | ۵۲ | پشتیبانگیری و بهروزرسانی (۳) |
| `actions/food-brand-actions.php` | ۴۵ | برند فیش غذا (۱) |
| `actions/cd-dvd-actions.php` | ۳۸ | آرشیو CD/DVD (۳) |
| `actions/notification-actions.php` | ۳۲ | اعلانها (۲) |

**جستجوی دقیق:** `python3 tools/find-in-system.py --action save_org_node`

---

## ۸. صفحات و روتها

### ۴۰ صفحهٔ `?page=`

`dashboard` (پیشفرض), `login`, `new-ticket`, `ticket`, `queue`, `supervisor`, `reports`,
`analytics`, `search`, `services`, `knowledge`, `help`, `notifications`, `settings`, `profile`,
`change-password`, `organization`, `holidays`, `local-accounts`, `backup`, `system-health`,
`logs`, `activity-log`, `audit`, `inventory`, `inventory-diagnostics`, `assets`, `asset`,
`asset-history`, `domain-scan`, `cd-dvd`, `cd-dvd-history`, `governance`, `chatbot-admin`,
`chatbot-api`, `food-ticket`, `food-order`, `food-personnel-link`, `traffic-control`,
`traffic-control-print`.

### ۸۳ endpoint API (بخش غذا، از طریق `?food_api=`)

نمونههای پرکاربرد: `config`, `monitoring`, `employees`, `orders`, `order`, `cancel`,
`food-menu/day`, `food-menu/month`, `food-menu/catalog`, `food-menu/orders`,
`food-menu/emergency/*`, `food-menu/printed-delivery`, `food-groups`, `food-manual-attendance`,
`absence-*`, `guest-*`, `templates/*`, `printers`, `reprint-errors`, `calibrate-print`,
`reports/export`, `process`, `health`, `session`, `self-test`.

> `traffic_api` جداگانه در `traffic-control.php` و `chatbot-api` در `chatbot.php` هستند.

### نقشهای دسترسی

`user`، `agent` (کارشناس)، `manager` (مدیر واحد)، `supervisor` (سوپروایزر)،
`primary_admin` (ادمین اصلی)، `support_manager`، `inspector`.
کاتالوگ در `permissions.php:30`، پیشفرضها در `permissions.php:337`.

---

## ۹. پایگاهداده — ۷۰ جدول

| دامنه | جدولها |
|---|---|
| **هویت/سازمان** | `users`, `departments`, `org_units`, `org_unit_managers`, `handling_units`, `role_permissions`, `login_attempts`, `settings` |
| **درخواست** | `tickets`, `ticket_messages`, `ticket_events`, `ticket_attachments`, `ticket_ratings`, `ticket_number_seq`, `ticket_sla_alerts`, `ticket_ola_alerts`, `categories`, `service_catalog`, `service_catalog_fields`, `knowledge_articles`, `notifications`, `holidays` |
| **تغییر/مشکل** | `change_records`, `change_approvals`, `change_ticket_links`, `problem_records`, `problem_ticket_links`, `ola_policies` |
| **دارایی** | `assets`, `asset_profiles`, `asset_relations`, `asset_history_events`, `asset_inventory_history`, `asset_memory_modules`, `asset_storage_devices`, `asset_graphics_adapters`, `asset_peripherals`, `domain_scan_runs`, `domain_scan_queue` |
| **تردد/CD** | `traffic_visits`, `traffic_destinations`, `traffic_visit_history`, `cd_dvd_records`, `cd_dvd_types` |
| **لاگ** | `activity_logs`, `system_logs`, `api_rate_limits`, `system_incidents`, `chatbot_faq`, `chatbot_logs` |
| **فیش/تردد غذا** | `food_ticket_config`, `food_ticket_events` (صف چاپ), `food_ticket_print_log`, `food_ticket_templates`, `food_ticket_card_map`, `food_ticket_guest_cards`, `food_ticket_groups`, `food_ticket_group_members`, `food_ticket_group_runs`, `food_ticket_group_uid_history`, `food_ticket_daily_absence`, `food_ticket_day_visits`, `food_ticket_worker_status` |
| **سفارش غذا** | `food_catalog`, `food_calendar`, `food_calendar_items`, `food_orders`, `food_order_logs`, `food_order_cancel_requests`, `food_guest_requests` |

### قواعد اسکیما (بسیار مهم)

- نصب تازه: `schema.sql` (۷۰ جدول) **و** `install-empty-db.sql` (۶۶ جدول) — هر دو باید همگام باشند.
- ✅ اختلاف ۴ جدول (`chatbot_faq`, `chatbot_logs`, `system_incidents`, `food_guest_requests`) در ۲۰۲۶-۱۰-۲۷ رفع شد.
- ارتقا: `migrations/NNN-*.sql` — **idempotent و افزایشی**، ددوپ در `schema_updates`.
- شبکهٔ ایمنی: توابع `*_ensure_schema()` در `bootstrap`/`organization`/`food`/`traffic` ستونهای جاافتاده
  را در زمان اجرا `ALTER` میکنند. یعنی **اسکیما فقط با اجرای کد کامل میشود**.
- قاعدهٔ طلایی: هر ستون جدید در **سه** جا → `schema.sql` + `install-empty-db.sql` + `ensure_schema()` + migration.

### ۱۳ migration فعلی

`001` حساب محلی/بازرسی · `002` تردد خاص · `003` همگامسازی ۱.۵۱ · `004` لغو درخواست ·
`005` نرمالسازی pending · `006` تفکیک وعده غذا · `007` تاریخچه تردد · `008` تأییدکننده مقصد ·
`009` چند تأییدکننده (۱–۳) · `010` واحد نمایندگی مستقل ·
`011` تحویل فیش دستی · `012` حذف غیبت متناقض · `013` backfill نهایی فیش/غیبت (نسخهٔ فعلی).

---

## ۱۰. بخش FOOD — سامانه مدیریت غذا

> نام قدیمی «چاپ فیش غذا» **منسوخ** است. نام درست: **سامانه مدیریت غذا**.

### جریان داده

```
Access TENTER (C_Date/C_Time/UID)
   ↓ food-ticket-engine.php (موتور پایش)
تطبیق با food_orders (MySQL داخلی — food-ticket-order-source.php)
   ↓ food-ticket-runtime.php (ثبت رویداد + صف چاپ)
food-ticket-templates.php (قالب) → netprint (TCP 9100) یا صف ویندوز (PowerShell)
   ↓
food_ticket_events / food_ticket_print_log
```

### فایلها

| فایل | نقش |
|---|---|
| `food-ticket.php` | مرکز: تنظیمات، ۸۳ API، صفحهٔ ادمین، پایش، تست، ذخیره |
| `food-ticket-engine.php` | موتور پایش تردد و تطبیق |
| `food-ticket-runtime.php` | صف چاپ، `print_status`, `delivery_status`, قوانین وعده |
| `food-ticket-templates.php` | قالب فیش، رندر، صف ویندوز |
| `food-ticket-netprint.php` | چاپ RAW روی پورت ۹۱۰۰، trim/align/kalibrasi |
| `food-ticket-groups.php` | گروهها، L_UID، نماینده، غیبت، ثبت دستی |
| `food-ticket-import.php` / `food-order-import.php` | ورود Excel کارکنان/سفارش |
| `food-ticket-env.php` | مقادیر سازگاری (⚠️ Base64 رمز پیشفرض Access) |
| `food-ticket-order-source.php` | `food_order_internal_map()` — فقط MySQL |
| `food-order.php` | سفارش کاربر، نیابت، لغو، درخواست مهمان، مهلت اضطراری |
| `food-personnel-link.php` | اتصال کد پرسنلی ↔ حساب دامین |
| `cron/food_ticket_worker.php` | **Worker همیشهروشن** (۱۶۳ خط) — بدون سشن/پنل |
| `food-ticket-web/` | پنل فرانت تکصفحهای (`index.html`) + ۲۷ فایل JS/CSS |

### تنظیمات پرینتر

`storage/food_ticket_netprint.json` شامل: `top_gap_mm`, `head_offset_mm`, `edge_align`,
`reverse_feed_cmd`, `win_top_shift_mm`, `cut_mode`, `line_dots`, `tail_mm`.
ابزارها: `tools/set_print_gap.php`, `tools/print_calibration.php`, `tools/diag_print_margin.php`.

### نسخهٔ ۱.۶۲.۳۶ (آخرین تغییر)

migration `013` فیشهای دستی قدیمی (`source_uid=manual-attendance`, `event_type=printed`)
را بدون توجه به `print_status` به `delivered` تبدیل میکند، غیبت متناقض همان روز را حذف
میکند، و reconciliation هنگام خواندن گزارش تحویل/غیبت اجرا میشود.

---

## ۱۱. کرونها و سرویسها

| فایل | خط | دوره | نقش |
|---|---:|---|---|
| `cron/food_ticket_worker.php` | ۱۶۳ | پیوسته | Worker چاپ فیش (همیشهروشن) |
| `cron/domain_scan_worker.php` | ۶۴ | `--loop` | Worker اسکن دامنه |
| `cron/sla_maintenance.php` | ۹۳ | زمانبندی | هشدار SLA/OLA |
| `cron/ticket_auto_close.php` | ۵۸ | زمانبندی | بستن خودکار |
| `cron/ldap_sync.php` | ۲۷ | زمانبندی | همگامسازی AD |
| `cron/prune_activity_logs.php` | ۲۳ | زمانبندی | هرس لاگ (`ops_low_value_tables_prune`) |

نصب سرویس ویندوز: `install-food-worker-SERVICE.ps1`, `install_food_ticket_worker.ps1`,
`START-FOOD-WORKER.txt`, `00_WORKER_RUNBOOK_FA.txt`.

---

## ۱۲. داراییها، تم و قاعدهٔ کش

- فونتها **همه محلی**اند (`assets/fonts/`: Vazirmatn, Shabnam, Samim, Vazir-Code) — بدون CDN.
- UI راستچین (`dir=rtl`) + تقویم جلالی (`assets/jalali-calendar.js`).
- تم شب/روز: `assets/theme-mode.css`, `assets/app.js`.
- ⚠️ **قاعدهٔ کش:** هر تغییر CSS/JS باید `?v=` را در `index.php` **و/یا** `food-ticket-web/index.html`
  بالا ببرد وگرنه کاربر نسخهٔ کهنه میگیرد.

نسخههای فعلی در `index.php`: `style.css?v=3.4.47`، `app.js?v=3.4.27`، `organization.js?v=7`.
در پنل غذا: `food-menu.js?v=32`، `food-groups.js?v=16`، `food-manual-attendance.js?v=5`،
`food-ticket-global-theme-r42.css?v=45`.

> 🐛 مثال واقعی نقض این قاعده: تست `tests/test_food_manual_attendance_static.py`
> همچنان `?v=4` را پین کرده ولی فایل `?v=5` است → ۲ تست از قبل FAIL دارند.

---

## ۱۳. قرارداد بستهٔ بهروزرسانی

- ZIP فقط این سه نوع ورودی را میپذیرد: `manifest.json` | `migrations/*.sql` | `files/...`
  (هر چیز دیگر = خطا).
- `manifest.json`: `name`, `version`, `date`, `title`, `requires_version`, `migrations[]`,
  `files[]`, `notes`.
- ⚠️ طبق `README.fa.md` بستهٔ ترمیم: **updater نسخهٔ پایه را از روی `requires_version`
  اعتبارسنجی نمیکند** — پس بررسی دستی الزامی است.
- هر انتشار: `updates/<تاریخ-نام>/` → ZIP یکتا + `.sha256` در `downloads/` →
  `downloads/index.html` فقط آخرین بسته → تگ + GitHub Release «Latest».
- Updater پیش از اعمال، backup میگیرد؛ migrationها فقط در صورت ثبتنشدن اجرا میشوند.
- سیاست کامل: `docs/DOWNLOAD-PUBLISHING-POLICY-FA.md`.

---

## ۱۴. تستها، CI و کیفیت

| مورد | مسیر |
|---|---|
| CI | `.github/workflows/ci.yml` (PHP 8.2): `lint-php-syntax.php`, `SecurityTest.php`, `ArchitectureTest.php`, `test_food_ticket_logic.php` |
| بنچمارک | `benchmark-250.yml`, `benchmark-operations.yml`, `food-system-stress.yml`, `benchmark-results/` |
| تست ایستا | `tests/test_*_static.py` (بدون PHP/DB)، `tests/js/` (Node) |
| راهنمای تست | `tests/README.md` (۵۶ سناریوی دستی) |
| ماتریس پوشش | `docs/TEST_COVERAGE_MATRIX_FA.md` |

⚠️ **PHP/MySQL در محیط فعلی نصب نیست**؛ فقط Python 3.11 موجود است. تستهای PHP اجرا نمیشوند؛
جایگزین ایستا بنویس و محدودیت را اعلام کن.

---

## ۱۵. قواعد ثابت کاربر (از `docs/HANDOFF-FA.md` بند ۱) — حتماً رعایت شود

1. هر قابلیت جدید باید **راهنما** (`help-guide.php` with `data-kw`) + **چتبات** +
   **دسترسیها** (`permissions.php` و نقشهای پیشفرض) را هم بهروز کند.
2. اصلاحهای همان نسخه شماره نمیگیرند؛ شماره فقط با انتشار واقعی عوض میشود.
3. هر آپدیت: پوشهٔ `updates/` + `manifest.json` + `CHANGE-REPORT-FA.md`،
   migrationهای **idempotent و افزایشی** (ستون nullable یا جدول جدید).
4. **هیچ دادهای روی سرور حذف نمیشود**؛ حذف فقط با دستور صریح و محدود.
5. لاگها بدون تاریخ قطع مشخص و تأیید کاربر حذف نمیشوند.
6. خروجی همهٔ گزارشها **XLSX** است (نه CSV).
7. مرتبسازی روی **کل دادههای فیلترشده** قبل از صفحهبندی؛ ترتیب ذخیرهشدهٔ DB تغییر نکند.
8. ترافیک چاپ/تردد ناشناس در پایش ذخیره یا شمرده نمیشود.
9. پاسخ به کاربر **فارسی** باشد؛ گزارش دقیق بگوید چه چیزی **تأیید شده** و چه چیزی **تأیید نشده**.
10. واژگان: **«درخواست»** (نه «تیکت») · **«سامانه مدیریت غذا»** (نه «چاپ فیش غذا») ·
    **«گزارشساز»** (لغو شده و حذف شده) · **«famili»** (ستون ایمپورت Access حفظ شود).

---

## ۱۶. ریسکها و ناهماهنگیهای شناختهشده

| مورد | وضعیت |
|---|---|
| `README.md` نسخهٔ `1.8-final` | ✅ **رفع شد** (۲۰۲۶-۱۰-۲۷) → `1.62.36` + توضیح تاریخی بودن `upgrade-1.8-*` |
| `schema.sql` vs `install-empty-db.sql` | ✅ **رفع شد** → هر دو ۷۰ جدول؛ ۴ جدول `chatbot_faq`, `chatbot_logs`, `system_incidents`, `food_guest_requests` فقط با `CREATE TABLE IF NOT EXISTS` اضافه شدند (بدون حذف داده) |
| تستهای پینشده (۷ تست / ۱۳ پین) | ✅ **رفع شد** → ۷۳ تست استاتیک، همگی PASS. پینهای `?v=` و نسخهٔ پنل (`1.37.27`) و رشتههای بازآراییشده بهروز شدند. بستههای ZIP آرشیو عمداً دستنخورده ماندند |
| اسناد `ARCHITECTURE.md` / `ARCHITECTURE-FA.md` | ⚠️ همچنان قدیمی و ناهماهنگ (اعدادشان مربوط به قبل از تفکیک `bootstrap.php` است). **از این سند استفاده کنید** |
| `app/` + `routes/web.php` | ⚠️ لایهٔ MVC بلااستفاده؛ گیجکننده (تصمیم حذف با کاربر) |
| `SecurityTest.php` | ⚠️ شکست قدیمی مربوط به پوشش `rebuild-final.sql` — نیاز به **تصمیم کاربر** (طبق `HANDOFF-FA.md` بند ۴) |
| `food-ticket-env.php` | ⚠️ رمز پیشفرض Access بهصورت Base64 (ریسک امنیتی) |
| runtime `ALTER TABLE` | ⚠️ اسکیمای واقعی فقط با اجرای کد ساخته میشود |
| مخزن پابلیک | ⚠️ شامل کد داخلی و ساختار سازمانی |
| `install.php?force=1` / حذف دیتابیس | ⚠️ بدون تأیید صریح اجرا نشود |
| تستهای PHP / CI | ⚠️ در این محیط PHP نصب نیست؛ `ci.yml`, `SecurityTest.php`, `ArchitectureTest.php` اجرا نشدند |

> 📌 **درس آموخته (قاعدهٔ کش):** تستهایی که `?v=` را پین میکنند با هر انتشار میشکنند.
> هنگام تغییر نسخهٔ یک Asset، همهٔ تستهای مرتبط را در همان کامیت ریپین کنید.
> ابزار کمکی: `python3 tools/find-in-system.py "نسخه کش"`.

---

## ۱۷. «اگر گفتی… برو به» (ایندکس معکوس سریع)

| اگر گفتی… | فایلها |
|---|---|
| درخواست، ارجاع، پاسخ، SLA | `index.php`, `actions/ticket-actions.php`, `lib/tickets-sla.php` |
| صف کاری، عملیات گروهی | `index.php` (page=queue), `actions/bulk-actions.php` |
| سوپروایزر، تأیید نهایی | `actions/supervisor-actions.php`, `index.php` |
| خدمات، کاتالوگ، فرم پویا | `index.php` (page=services), `actions/service-actions.php` |
| دانشنامه، راهنما، چتبات | `help-guide.php`, `chatbot.php`, `index.php` |
| دارایی، شناسنامهٔ فنی، RAM/GPU | `asset-profile.php`, `inventory.php`, `actions/asset-actions.php` |
| دامنه، اسکن، پینگ، LDAP | `domain-scan.php`, `cron/domain_scan_worker.php`, `lib/ldap.php` |
| سازمان، چارت، معاونت، واحد نمایندگی | `organization.php`, `actions/org-actions.php`, `assets/organization.js` |
| تغییر، مشکل، CAB | `governance.php` |
| تردد عمومی، مقصد، تأییدکننده | `traffic-control.php`, `traffic-import.php`, `assets/traffic-control.js` |
| CD/DVD، رسانه | `cd-dvd.php`, `actions/cd-dvd-actions.php` |
| سفارش غذا، نیابت، لغو، مهمان | `food-order.php`, `food-order-import.php` |
| سامانه مدیریت غذا، پنل ادمین | `food-ticket.php`, `food-ticket-web/`, `food-ticket-web-host.php` |
| موتور پایش، TENTER، Access، UID | `food-ticket-engine.php`, `food-ticket-env.php`, `cron/food_ticket_worker.php` |
| صف چاپ، فیش چاپ نشد، پرینتر | `food-ticket-runtime.php`, `food-ticket-netprint.php`, `food-ticket-templates.php` |
| قالب فیش، لوگو، طراحی | `food-ticket-templates.php`, `food-ticket-web/assets/food-designer.js` |
| گروه غذایی، غیبت، ثبت دستی حضور | `food-ticket-groups.php`, `food-ticket-web/assets/food-manual-attendance.js`, `migrations/013*` |
| پرسنل، کد پرسنلی، ایمپورت | `food-personnel-link.php`, `food-ticket-import.php`, `local-accounts.php` |
| چتبات سخنگو، TTS، Piper | `chatbot.php`, `chatbot-tts-lexicon.php`, `assets/chatbot-drag.js` |
| جستجوی سراسری | `global-search.php` |
| گزارش، خروجی Excel، چارت | `index.php` (reports/analytics), `xlsx-writer.php` |
| پشتیبان، بازیابی، آپدیت سامانه | `backup.php`, `system-update.php`, `actions/backup-actions.php` |
| سلامت سامانه، لاگ، حسابرسی | `system-health.php`, `lib/activity-audit.php`, `lib/logging-csrf.php` |
| نصب، config، دیتابیس خالی | `install.php`, `schema.sql`, `install-empty-db.sql`, `config.sample.php` |
| migration، ستون جدید، ارتقا | `migrations/`, `*_ensure_schema()`, `upgrade-*.sql` |
| کرون، Worker، سرویس ویندوز | `cron/`, `START-FOOD-WORKER.txt`, `00_WORKER_RUNBOOK_FA.txt` |
| تم، شب/روز، RTL، فونت، طراحی صفحه | `assets/style.css`, `assets/app.js`, `assets/theme-mode.css`, `admin-layout-design.php` |
| نقش، دسترسی، ادمین اصلی | `permissions.php`, `actions/role-actions.php` |
| تست، CI، بنچمارک | `tests/`, `.github/workflows/`, `benchmark-results/` |
| نسخه، انتشار، changelog | `VERSION`, `updates/`, `downloads/`, `docs/RELEASE-1.62.36-FA.md` |
| قواعد ثابت کاربر | `docs/HANDOFF-FA.md` بند ۱, `AGENTS.md` |

---

## ۱۸. نگهداری نقشه

```bash
# بعد از هر تغییر ساختاری (فایل/تابع/روت/جدول/بسته جدید):
python3 tools/build_system_map.py

# بررسی تازگی (در CI یا قبل از تحویل):
python3 tools/build_system_map.py --check
python3 tests/test_system_map_static.py

# جستجو:
python3 tools/find-in-system.py "عبارت مورد نظر"
```

> **آخرین راستیآزمایی:** ۲۰۲۶-۱۰-۲۷ — branched from `e2afc13` (v1.62.36).
> در صورت تغییر نسخه، تاریخ بالا و فایل `summary.json` را بازتولید کن.
