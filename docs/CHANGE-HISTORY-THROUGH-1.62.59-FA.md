# راهنمای ماندگار تاریخچه و مستندات تا نسخهٔ 1.62.59

این راهنما نقطهٔ ورود برای گفت‌وگوهای بعدی است؛ متن را دادهٔ تاریخی بدانید، نه مجوز اجرا یا اثبات نصب. دامنه عمداً به وضعیت شاخهٔ اصلی در commit زیر محدود است و موارد بعد از آن را دربرنمی‌گیرد.

- مرجع قطعی Git: `860185dc2f389adc4a58e13e6bc4837fe96eaf32` (ادغام PR #6).
- نسخهٔ فایل VERSION در این مرجع: `1.62.59`؛ این فایل راهنما نسخهٔ محصول را افزایش نمی‌دهد.
- تاریخ مرجع commit: `2026-10-31T20:53:13Z`. تاریخ‌ها در این سند زمان Git/GitHub هستند و لزوماً تاریخ استقرار واقعی نیستند.
- شمارش: **329 commit قابل‌دسترسی از مرجع**، **347 فایل مستند/مانیفست قابل‌مشاهده در tree مرجع** و **155 ZIP ردیابی‌شده در downloads**. هیچ ZIP استخراج یا اجرا نشده است.
- فهرست commit از تمام والدهای قابل‌دسترسی مرجع تولید شده، نه فقط مسیر first-parent؛ به همین دلیل commitهای ادغام‌شده در شاخه‌های فرعی نیز در آن هستند. اسناد و بسته‌هایی که در گذشته وجود داشته ولی در tree مرجع نیستند در فهرست فایل‌های فعلی نیستند؛ برای آنها تاریخچهٔ Git را بررسی کنید.
- متن عنوان و بدنهٔ PR و commit زیر بازتاب ادعاهای تاریخی صاحبان آنهاست؛ صحت همهٔ ادعاها، نتایج آزمون، امنیت یا استقرار در این نوبت مجدداً تأیید نشده است. نظرها یا مطالبی که در GitHub حذف شده‌اند یا هرگز در Git/GitHub ذخیره نشده‌اند، بازیابی نمی‌شوند.

## شروع جلسهٔ بعد

۱. این فایل و سپس `docs/RELEASE-1.62.59-FA.md` و `docs/TEST-ALIGNMENT-1.62.59-FA.md` را بخوانید؛ اینها گزارش‌های تاریخی‌اند و ممکن است با وضعیت بعدی فرق کنند.
۲. از `VERSION`، `git status` و نسخهٔ جاری شاخه اطمینان بگیرید؛ سپس برای ریشهٔ یک تغییر از فهرست commit زیر و `git show <sha>` استفاده کنید. برای مخزن shallow، تاریخچهٔ کامل ممکن است ابتدا fetch لازم داشته باشد.
۳. جزئیات هر نسخه از فایل‌های مانیفست و گزارش تغییرات در فهرست مستندات بخش ۴ و بسته‌های بخش ۵ قابل پیگیری است. فایل‌های واقعی تنظیمات/دادهٔ محرمانه نباید خوانده یا بازتولید شوند.
۴. این راهنما یا `AGENTS.md` صرفاً در صورتی وارد زمینهٔ جلسهٔ جدید می‌شود که ابزار یا عامل آن را بخواند؛ حافظهٔ خودکار یا فعال‌سازی دائمی تضمین نمی‌شود. از اجرای خودکار سازندهٔ نقشه بدون بررسی دامنهٔ فایل‌های حساس خودداری کنید.

## ۱. نقاط اصلی تاریخچه در مرجع

- PR #1، #2، #3، #5 و #6 در GitHub ادغام شده و merge commit هر یک در اجداد مرجع وجود دارد؛ جزئیات و بدنه‌ها در بخش ۲ ثبت شده‌اند.
- PR #4 پیش از تاریخ مرجع باز بوده اما طبق وضعیت دریافت‌شده ادغام نشده است؛ بنابراین نباید محتوای آن را خودکار جزو نسخهٔ اصلی فرض کرد.
- در گزارش تاریخی `docs/RELEASE-1.62.59-FA.md` اختلاف VERSION داخل ZIP تاریخی با manifest گزارش شده است؛ خود فایل `VERSION` مرجع `1.62.59` است. این مدرک به معنی نصب یا اجرای بسته نیست.

## ۲. درخواست‌های GitHub موجود تا مرجع؛ متن‌های اصلی

### درخواست #1: Expand offline regression coverage

- نشانی: https://github.com/alirezarahimy-code/ticket/pull/1
- وضعیت دریافت‌شده: MERGED
- ایجاد: 2026-10-22T23:44:34Z
- ادغام: 2026-10-26T13:45:15Z
- هدف: main
- commit ادغام: 4272b2cdcdf7eb5ae23e402409723d642271a217

متن درخواست در زمان برداشت (به‌صورت نقل‌قول):

> ## Summary
> - Align offline assertions with current role, request, template-hosting, personnel-link, and traffic-validation contracts.
> - Repair isolated fixtures for food groups, order duplicates, source deletion, and print logging; use unique synthetic SQLite files.
> - Run the org-role sync test only against the disposable CI database and scope its cleanup to randomized synthetic records.
> - Preserve the `rebuild-final.sql` drop-list correction; it was not executed.
> 
> ## Verification
> - Isolated full-system GitHub Actions run **passed**: [run 39874499699](https://github.com/alirezarahimy-code/ticket/actions/runs/39874499699).
> - PHP 8.2 syntax/offline gate passed; all 139 tracked PHP files linted, all test suites passed, and disposable MariaDB smoke checks passed.
> - Authenticated route/API smoke: 74/74 checks passed; 3 expected authorization rejections (403), 0 unexpected HTTP errors, 0 PHP runtime log lines, 0 DB/SQL log lines. Median latency 10.64 ms; max 164.05 ms.
> - Synthetic concurrency: 250 create/resolve ticket sessions plus 60 duplicate-state races; 1,870 logical operations, 98.73 ops/s, 0 operation failures, 0 unexpected application errors, 0 PHP warning/notice/fatal logs. Independently verified 250 tickets created/resolved and all 60 race invariants.
> - No production or staging traffic/data was used. LDAP/AD, Access/ODBC, physical printers/media, hardware collection, and external providers were not exercised.

### درخواست #2: Sync main to v1.62.20 (current stable)

- نشانی: https://github.com/alirezarahimy-code/ticket/pull/2
- وضعیت دریافت‌شده: MERGED
- ایجاد: 2026-10-26T13:45:06Z
- ادغام: 2026-10-26T13:45:13Z
- هدف: main
- commit ادغام: 7db20383d32cbcbb2631a668574eb5c511101e3a

متن درخواست در زمان برداشت (به‌صورت نقل‌قول):

> برگرداندن کامل سامانه به نسخهٔ پایدار 1.62.20 (بستهٔ ticket-update-1.62.20-backup-split-bootstrap-lib-20261025.zip، SHA-256: c169b7c188c6cc39396656b1417a8267d84aba7c356a6d89a2410387421625b2) و همگام‌سازی main با شاخهٔ نشست.

### درخواست #3: Release v1.62.36: reconciled source snapshot

- نشانی: https://github.com/alirezarahimy-code/ticket/pull/3
- وضعیت دریافت‌شده: MERGED
- ایجاد: 2026-10-27T15:52:47Z
- ادغام: 2026-10-27T16:01:21Z
- هدف: main
- commit ادغام: e2afc13a9e185c7dfcc1e9757675a3180cd98fee

متن درخواست در زمان برداشت (به‌صورت نقل‌قول):

> ## Summary
> - Promote the reconstructed 1.62.36 source tree to the release snapshot in `main`.
> - Add the full Persian content report at `docs/RELEASE-1.62.36-FA.md`.
> - Publish the same-version repair package with 71 source paths and migrations 009–013; preserve mainline inventory/profile/group-ticket enhancements in the payload.
> - Update the root `VERSION` to 1.62.36 and keep the incremental 1.62.36 package distinct (requires 1.62.35).
> 
> ## Verification
> - Focused release/snapshot checks: 13 passed.
> - PHP 8.2 syntax parser, security, architecture, and standalone worker CI checks: passed.
> - Node syntax checks: all 14 JavaScript payload files passed.
> - Full Python suite: 290 passed, 32 failed, 7 subtests passed; remaining failures are documented in the release report and include old release expectations and static checks needing separate review.
> 
> No live server was installed or modified. See the release report for the complete file inventory, migrations, checksum, test status, and install caveats.

### درخواست #4: Alirezarahimy code patch 1

- نشانی: https://github.com/alirezarahimy-code/ticket/pull/4
- وضعیت دریافت‌شده: OPEN
- ایجاد: 2026-10-29T18:11:52Z
- ادغام: ادغام نشده
- هدف: main
- commit ادغام: ندارد

متن درخواست در زمان برداشت (به‌صورت نقل‌قول):

> 1

### درخواست #5: Restore agent skill boot framework (31 skills + system map + boot tool)

- نشانی: https://github.com/alirezarahimy-code/ticket/pull/5
- وضعیت دریافت‌شده: MERGED
- ایجاد: 2026-10-31T20:34:42Z
- ادغام: 2026-10-31T20:45:48Z
- هدف: main
- commit ادغام: bb1c024a9f934b82e5fce64ad66d0ce3b8bbb569

متن درخواست در زمان برداشت (به‌صورت نقل‌قول):

> بازنشانی چارچوب عملیاتی ایجنت (مانیفست ۳۱ مهارت) که قبلاً در PR #4 ساخته شده بود، این بار فقط فایل‌های مهارت/نقشه/ابزار — بدون تغییر در کد محصول.
> 
> ## چه چیزی اضافه شد
> - `AGENTS.md`, `CLAUDE.md`, `.cursorrules`, `.cursor/rules/arena-boot.mdc`, `.github/copilot-instructions.md` — پوینترهای بارگذاری خودکار
> - `docs/SKILL-MANIFEST-FA.md` — ۳۱ مهارت (۲۳ اصلی + ۸ مکمل) با چک‌لیست و معیار موفقیت
> - `docs/SYSTEM-MAP-FA.md` + `docs/system-map/*.tsv` — ایندکس ۸۳۹ فایل / ۴۳۵۲ نماد / ۳۶۷ روت / ۷۰ جدول / ۱۱۰ بسته
> - `docs/SESSION-BOOT-FA.md` — راهنمای «به‌روز شو»
> - `tools/session-boot.py`, `tools/build_system_map.py`, `tools/find-in-system.py`
> - ۴ تست نگهبان: `test_skill_manifest_static.py`, `test_system_map_static.py`, `test_cache_pin_consistency_static.py`, `test_session_boot_static.py`
> 
> ## تست
> - `python3 tools/session-boot.py` → بوت کامل، نقشه تازه، ۳ از ۴ تست نگهبان PASS
> - `test_cache_pin_consistency_static.py` = FAIL؛ **از قبل روی `main` هم همان ۹ پین کهنه را دارد** (مرتبط با تغییرات دارایی‌ها در PR #4، نه این PR). طبق C02 فقط گزارش شد و بدون تأیید تغییر داده نشد.
> 
> ## ریسک
> - صفر روی کد محصول: هیچ فایل PHP/JS/CSS یا مهاجرت SQL لمس نشده است.

### درخواست #6: Align the whole test suite with 1.62.59 (66 stale tests, suite green)

- نشانی: https://github.com/alirezarahimy-code/ticket/pull/6
- وضعیت دریافت‌شده: MERGED
- ایجاد: 2026-10-31T20:53:08Z
- ادغام: 2026-10-31T20:53:13Z
- هدف: main
- commit ادغام: 860185dc2f389adc4a58e13e6bc4837fe96eaf32

متن درخواست در زمان برداشت (به‌صورت نقل‌قول):

> ## چه شد
> بعد از هم‌ترازی کد با `1.62.59` (PR #5 ادغام شد)، ۶۶ تست ایستا قرمز بود. همه اصلاح شدند:
> **`pytest tests` → 379 passed / 0 failed / 0 error** و هر ۵ تست نگهبان PASS.
> 
> | اندازه‌گیری | قبل | بعد |
> |---|---|---|
> | pytest | 40 failed (روی 1.62.56) → 66 failed (پس از 1.62.59) | **379 passed** |
> | تست‌های نگهبان (skill/system-map/cache-pin/version/session-boot) | 1 FAIL | **5 PASS** |
> 
> ## چرا قرمز بودند (نه باگ، بلکه تست کهنه)
> نسخه‌های `1.62.57`..`1.62.59` فقط ZIP منتشر شدند و تست‌هایشان هرگز کامیت نشد. نمونه‌ها:
> - تست انتظار `['department','deputy','liaison']` داشت؛ کد به‌درستی `'management'` هم دارد.
> - پین `food-menu.js?v=33` در حالی که پنل `v=38` است؛ `PANEL_VERSION` روی ۱.۳۷.۳۴ مانده بود.
> - مجوز گزارش‌ها از `food.order_close` به `food.reports` یکپارچه شده (۱.۶۲.۵۸).
> - ۱۴ تست «صفحهٔ دانلود» هرکدام نسخهٔ ثابت خودشان را چک می‌کردند.
> 
> ## یک بازسازی مهم
> تست‌های صفحهٔ دانلود و پین‌ها **نسخه‌آگنوستیک** شدند (`tests/download_page_helpers.py`):
> صفحه باید فقط جدیدترین بستهٔ `downloads/` را نشان دهد. دیگر با هر انتشار ده‌ها تست نمی‌شکند.
> 
> ## سه ایراد واقعی که همین کار پیدا کرد و اصلاح شد
> 1. **`downloads/index.html` روی بستهٔ ۱.۶۲.۳۶ گیر کرده بود** — علت سوم اینکه همه فکر می‌کردند آخرین نسخه ۱.۶۲.۳۶ است. حالا ۱.۶۲.۵۹؛ صفحهٔ تاریخی ترمیم به `downloads/repair-1.62.36.html` منتقل شد (هیچ چیز حذف نشد).
> 2. **پین ناهمگون `app.js`** — `index.php` روی `3.4.28` بود ولی `governance.php`, `ola.php`, `app/Views/layout/footer.php` روی `3.4.27` (کش کهنه در آن صفحات). یکسان شد.
> 3. **تست نگهبان نسخه** به زنجیرهٔ بوت اضافه شد (۵ تست نگهبان).
> 
> ## تغییرهای رفتاری که تست‌ها آشکار کردند (تصمیم محصول، نیاز به تأیید)
> | مورد | قبل | الان |
> |---|---|---|
> | سفارش نیابتی | مجوز `foodorder.proxy` | بازنشسته؛ کد ملی گیرنده + محدودیت نرخ + audit |
> | گزارش‌های غذا | `food.order_close` | `food.reports` / `food.menu_edit` |
> | مهلت اضطراری | فقط مدیر اصلی | مجوز نقش `food.emergency_window_edit` |
> 
> ## تأیید نشده (صادقانه)
> - **PHP در این محیط نیست** ⇒ lint، `SecurityTest`, `ArchitectureTest`, تست‌های DB **اجرا نشدند**. ۳۷۹ تست سبز همه ایستا هستند.
> - نصب روی سرور و آزمون مرورگری انجام نشده.
> - `files/VERSION` داخل ZIP نسخهٔ ۱.۶۲.۵۹ هنوز `1.62.58` است (باگ بسته).
> 
> مستند کامل: `docs/TEST-ALIGNMENT-1.62.59-FA.md`

### دیدگاه‌ها و بازبینی‌ها

بررسی GitHub API با صفحه‌بندی در این نوبت: دیدگاه‌های issue/PR، دیدگاه‌های review روی خط کد، دیدگاه‌های commit و reviewهای PRهای #1 تا #6 با زمان ایجاد/ارسال تا مرجع هر کدام **صفر مورد** بازگشتند. بدنهٔ درخواست‌ها نظر محسوب نشده و جداگانه در بالا حفظ شده است. GitHub ممکن است داده‌های حذف‌شده یا پنهان را برنگرداند؛ این ادعا فقط خروجی قابل‌دسترسی API را پوشش می‌دهد.


## ۳. همهٔ commitهای اجداد مرجع — تاریخچه و پیام کامل

ترتیب، توپولوژیکِ معکوس (قدیمی‌تر به جدیدتر) است. هر پیام به شکل نقل‌قول آمده؛ نام نویسنده و timestamp از Git گرفته شده‌اند. وجود پیام به معنی ادغام جداگانه، تأیید نصب یا صحت خودکار متن آن نیست.

### `fee7655d75f00d4fe8e30f2dadd29ace2a79d883`

- تاریخ commit: `2026-10-09T03:07:08+03:30` | نویسنده: Alireza Rahimy
- والدها: `ندارد`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/fee7655d75f00d4fe8e30f2dadd29ace2a79d883)

> Initial commit - Fresh start

### `40b58e89c6228fb9723698d8fcfe4dbc2e20c1b4`

- تاریخ commit: `2026-10-09T09:28:58+00:00` | نویسنده: Arena Agent
- والدها: `fee7655d75f00d4fe8e30f2dadd29ace2a79d883`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/40b58e89c6228fb9723698d8fcfe4dbc2e20c1b4)

> Add system architecture map with component tags

### `9f7130f1ec2e5af91302d2ff8d2152f2eaa88668`

- تاریخ commit: `2026-10-09T09:50:55+00:00` | نویسنده: Arena Agent
- والدها: `40b58e89c6228fb9723698d8fcfe4dbc2e20c1b4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/9f7130f1ec2e5af91302d2ff8d2152f2eaa88668)

> Internal-only food orders: remove Access orders dependency and .NET service
> 
> - Remove food-ticket-web .NET service (Program.cs, csproj, config, bat launchers)
> - Remove legacy Access orders reader food_ticket_orders() and orders_path/orders_table
>   from config save/API and fresh schemas
> - Update tests (smoke, static orders, config paths) and checklist
> - Add changelog 00_CHANGELOG_INTERNAL_ORDERS_ONLY_FA.txt
> - Update ARCHITECTURE.md
> Only external source left: TENTER attendance (Access).

### `67da1e935c334ddf100d3cefb6ce3e16404fb2aa`

- تاریخ commit: `2026-10-09T09:57:01+00:00` | نویسنده: Arena Agent
- والدها: `9f7130f1ec2e5af91302d2ff8d2152f2eaa88668`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/67da1e935c334ddf100d3cefb6ce3e16404fb2aa)

> Fix food_ticket_config INSERT placeholder count after orders column removal
> 
> The previous commit left 14 placeholders for 13 bound values, so saving
> food ticket settings failed with a column/value count mismatch. Verified
> against MariaDB: committed version fails, fixed version saves.

### `ef01cffe7c51fa703b03ebc7c278572c834c95c3`

- تاریخ commit: `2026-10-09T10:02:12+00:00` | نویسنده: Arena Agent
- والدها: `67da1e935c334ddf100d3cefb6ce3e16404fb2aa`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ef01cffe7c51fa703b03ebc7c278572c834c95c3)

> Remove remaining Access order readers from food orders module
> 
> - Remove dead Access order readers: food_ticket_order_map, food_ticket_orders_rows_for_keys,
>   food_ticket_date_text_variants, food_ticket_row_column (no callers)
> - Remove legacy 'test-orders' route (orders check is internal food_orders only)
> - Simplify orders view (no legacy source selector)

### `8c0b44825f3ed8bec4de936e51010a5e982448b0`

- تاریخ commit: `2026-10-09T10:02:20+00:00` | نویسنده: Arena Agent
- والدها: `ef01cffe7c51fa703b03ebc7c278572c834c95c3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8c0b44825f3ed8bec4de936e51010a5e982448b0)

> Remove accidentally committed pytest cache; ignore __pycache__

### `818b3b252f3cb3f475e0e57c68253fffdef4b19a`

- تاریخ commit: `2026-10-09T10:09:55+00:00` | نویسنده: Arena Agent
- والدها: `8c0b44825f3ed8bec4de936e51010a5e982448b0`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/818b3b252f3cb3f475e0e57c68253fffdef4b19a)

> Remove 'سفارش‌های غذا' page from food ticket print panel
> 
> - Remove nav entry and manager view entry
> - Remove orders()/ordersForm()/ordersView()/ordersReadonly() and the
>   removeOrderPersonnelCodeColumn overrides
> - Dashboard order count and /api/orders endpoint are kept (used by dashboard)
> - Syntax checked with acorn; pytest static tests pass

### `8ba9db7d026c941359e081235b32879f2ab354c6`

- تاریخ commit: `2026-10-09T10:19:27+00:00` | نویسنده: Arena Agent
- والدها: `818b3b252f3cb3f475e0e57c68253fffdef4b19a`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8ba9db7d026c941359e081235b32879f2ab354c6)

> Read all order-based panel views from internal food_orders
> 
> - /api/orders accepts optional 'to' for date ranges (internal food_orders)
> - Dashboard orders refresh on every navigation (no stale cache)
> - Orders are no longer persisted in browser storage
> - 'گزارش سفارش غذا' now lists food_orders for the selected range

### `5cf7dfac8d1e29a02952c47836e3d07599418ede`

- تاریخ commit: `2026-10-09T10:23:37+00:00` | نویسنده: Arena Agent
- والدها: `8ba9db7d026c941359e081235b32879f2ab354c6`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5cf7dfac8d1e29a02952c47836e3d07599418ede)

> Add SQL migration to drop legacy Access orders columns from food_ticket_config

### `20ec5b8acc14d20a595d51f7500f515b3cfa90e0`

- تاریخ commit: `2026-10-09T10:34:03+00:00` | نویسنده: Arena Agent
- والدها: `5cf7dfac8d1e29a02952c47836e3d07599418ede`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/20ec5b8acc14d20a595d51f7500f515b3cfa90e0)

> Fix panel stuck on boot screen after first save
> 
> - persist() no longer stores orders, so the reloaded state had no orders key;
>   the startup line state.orders.map threw before startPanel() and the page
>   stayed on 'در حال آماده‌سازی'. orders/reportOrders are now always initialised.
> - Reports load internal orders when the reports view is opened directly

### `c5c24dbdf976d98effa1b0b457ad0582388464b4`

- تاریخ commit: `2026-10-09T10:44:52+00:00` | نویسنده: Arena Agent
- والدها: `20ec5b8acc14d20a595d51f7500f515b3cfa90e0`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c5c24dbdf976d98effa1b0b457ad0582388464b4)

> Remove unused ordersConnection parameter; update docs to internal food_orders
> 
> - food-ticket-engine.php / food-ticket-groups.php: drop unused $ordersConnection
>   and dead $orders variable from decision/group helpers
> - README-fa.md and final test guide: orders come from internal food_orders;
>   Access is only the TENTER attendance source

### `5864d97f8b7a62132e2273708b2784197a47c60c`

- تاریخ commit: `2026-10-09T10:52:12+00:00` | نویسنده: Arena Agent
- والدها: `c5c24dbdf976d98effa1b0b457ad0582388464b4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5864d97f8b7a62132e2273708b2784197a47c60c)

> Add import of previous food orders (Access/Excel/CSV) into internal food_orders
> 
> - food-order-import.php: read cod_meli / nahar_entekhabi / tarikh_entekhabi,
>   match employee (active, by national code), calendar day and food;
>   preview first, nothing is written until confirmed; single transaction on apply
> - Existing identical orders are skipped, conflicting orders are left untouched,
>   cancelled orders are restored; every write is logged as order_import
> - Route: index.php?page=food-ticket&food_api=orders-import (food.orders permission)

### `fa8adcbe60ccd8ccae72dac1880ad91aa217202f`

- تاریخ commit: `2026-10-09T11:21:59+00:00` | نویسنده: Arena Agent
- والدها: `5864d97f8b7a62132e2273708b2784197a47c60c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/fa8adcbe60ccd8ccae72dac1880ad91aa217202f)

> Order import: full field set and main-menu entry
> 
> - Reads national code, first/last name, lunch choice, selected date,
>   reservation date (d) and reservation time (s); reservation stored in the audit log
> - Warns when the file name differs from the system name (matched by national code)
> - Adds 'ایمپورت سفارش‌های قبلی' to the main menu for food-order managers

### `95acb562ec9f55f5751f92c5904c50cf9f55b0da`

- تاریخ commit: `2026-10-09T11:32:58+00:00` | نویسنده: Arena Agent
- والدها: `fa8adcbe60ccd8ccae72dac1880ad91aa217202f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/95acb562ec9f55f5751f92c5904c50cf9f55b0da)

> Document order import procedure in food orders operations guide

### `f35bf717f170203935604c7581e5cf478d89bff9`

- تاریخ commit: `2026-10-09T15:50:24+00:00` | نویسنده: Agent Mode
- والدها: `95acb562ec9f55f5751f92c5904c50cf9f55b0da`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f35bf717f170203935604c7581e5cf478d89bff9)

> AD sync: assign personnel code automatically from 101 when employeeID is empty; fix ldap sync query

### `ea89ba53a3015152206a069d8501eb84d2af5cf7`

- تاریخ commit: `2026-10-09T16:12:45+00:00` | نویسنده: Agent Mode
- والدها: `f35bf717f170203935604c7581e5cf478d89bff9`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ea89ba53a3015152206a069d8501eb84d2af5cf7)

> Revert "AD sync: assign personnel code automatically from 101 when employeeID is empty; fix ldap sync query"
> 
> This reverts commit f35bf717f170203935604c7581e5cf478d89bff9.

### `8814ff6f55ba9939ce0e86be212ac574b4ae2cd0`

- تاریخ commit: `2026-10-09T16:16:19+00:00` | نویسنده: Agent Mode
- والدها: `ea89ba53a3015152206a069d8501eb84d2af5cf7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8814ff6f55ba9939ce0e86be212ac574b4ae2cd0)

> Enforce unique personnel code: UNIQUE KEY on users.employee_number, migration, app-level duplicate checks (admin save, AD sync/login, personnel import); empty codes stored as NULL

### `08fde460f61f9a7088ce4b4a16342009fe503cd1`

- تاریخ commit: `2026-10-09T17:43:09+00:00` | نویسنده: Agent Mode
- والدها: `8814ff6f55ba9939ce0e86be212ac574b4ae2cd0`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/08fde460f61f9a7088ce4b4a16342009fe503cd1)

> Food orders: any logged-in user (any role) can order for another employee with national code verification; drop foodorder.proxy/self permission gates

### `b172d7e5fafb0e5825635655ce7f02b0cc6a4e31`

- تاریخ commit: `2026-10-09T17:56:07+00:00` | نویسنده: Agent Mode
- والدها: `08fde460f61f9a7088ce4b4a16342009fe503cd1`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b172d7e5fafb0e5825635655ce7f02b0cc6a4e31)

> Food employees no longer create system login accounts: auth_source=food (hidden from users list, cannot log in); migration for existing rows

### `dc8662e47bbb6d2d35e7cce537801285e7440978`

- تاریخ commit: `2026-10-09T18:31:57+00:00` | نویسنده: Agent Mode
- والدها: `b172d7e5fafb0e5825635655ce7f02b0cc6a4e31`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/dc8662e47bbb6d2d35e7cce537801285e7440978)

> Cleanup: hide orders import, remove legacy admin role, settings UI cleanup, print-engine fixes
> 
> - Orders import: removed from main menu (route still reachable by direct URL, primary admin / food.orders only).
> - Roles: legacy "admin" removed from role list; migration upgrade-remove-legacy-admin-role.sql
>   (admin+primary -> primary_admin, other admin -> supervisor).
> - Settings > connections: removed browse (file input) under attendance path, removed Excel export path field.
> - Engine:
>   * duplicate national code on today's orders blocks only that person (config_error), not the whole day.
>   * yesterday's TENTER rows are read too (decide accepts -1 day).
>   * inactive calendar items / catalog foods are excluded from the print order map.
>   * device identity no longer falls back to national code (personnel code or username only).

### `6ef6f18661d1327b49dc06c79cb0a876c9e9d1d4`

- تاریخ commit: `2026-10-09T19:07:09+00:00` | نویسنده: Agent
- والدها: `dc8662e47bbb6d2d35e7cce537801285e7440978`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/6ef6f18661d1327b49dc06c79cb0a876c9e9d1d4)

> UI: day-editor food list as 2-col checkboxes; proxy dropdown in-flow + Enter confirms national code; guest list refresh in settings; food groups moved to own menu tab

### `335d305ea22bde981b5060293da2b2e9d81c09fc`

- تاریخ commit: `2026-10-09T19:19:30+00:00` | نویسنده: Agent
- والدها: `6ef6f18661d1327b49dc06c79cb0a876c9e9d1d4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/335d305ea22bde981b5060293da2b2e9d81c09fc)

> Day editor: full-width 2-col checkbox list; fix checkbox styling; cache-bust food-menu/food-order assets; transfer password box read-only

### `72cc4eb9843e13fa475ac097bf57d0a9ec878156`

- تاریخ commit: `2026-10-09T19:27:20+00:00` | نویسنده: Agent
- والدها: `335d305ea22bde981b5060293da2b2e9d81c09fc`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/72cc4eb9843e13fa475ac097bf57d0a9ec878156)

> Bank of foods: keep new-food input focused after Enter (cleared on success); bump food-menu.js cache version

### `db1fcdc94778d847bc7d0c23bcaa6e0153577936`

- تاریخ commit: `2026-10-09T19:38:38+00:00` | نویسنده: Agent
- والدها: `72cc4eb9843e13fa475ac097bf57d0a9ec878156`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/db1fcdc94778d847bc7d0c23bcaa6e0153577936)

> Day editor back beside calendar (wider editor, narrower calendar); status select options forced light; css cache bump

### `928c6ba2e7f8dd1819abd10222790a7424786916`

- تاریخ commit: `2026-10-09T19:52:08+00:00` | نویسنده: Agent
- والدها: `db1fcdc94778d847bc7d0c23bcaa6e0153577936`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/928c6ba2e7f8dd1819abd10222790a7424786916)

> Food menu: wider page (1480px), calendar beside wider day editor, order status as radio buttons (no native select popup), cache bumps

### `5b4abf073810d68a23d61cd6b271c5b54e4a78d3`

- تاریخ commit: `2026-10-09T20:00:37+00:00` | نویسنده: Agent
- والدها: `928c6ba2e7f8dd1819abd10222790a7424786916`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5b4abf073810d68a23d61cd6b271c5b54e4a78d3)

> Food menu: day editor at half its previous width, placed beside calendar

### `7e7ed23e17c3329595aaa91b0546704574e41569`

- تاریخ commit: `2026-10-09T20:16:51+00:00` | نویسنده: Agent
- والدها: `5b4abf073810d68a23d61cd6b271c5b54e4a78d3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7e7ed23e17c3329595aaa91b0546704574e41569)

> Access: org positions auto-set manager role (exempt roles kept); no legacy admin writes; ticket view/bulk/supervisor/asset.edit/page gates wired to matrix codes; org sync tests

### `e99f99a886d43d14fd345d8d611082538e0bd7a4`

- تاریخ commit: `2026-10-09T20:31:55+00:00` | نویسنده: agent
- والدها: `7e7ed23e17c3329595aaa91b0546704574e41569`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/e99f99a886d43d14fd345d8d611082538e0bd7a4)

> Permissions: food sections follow role codes; food.groups_view read-only groups

### `ed55fede06588f864c488aa542f9f62ca61a221f`

- تاریخ commit: `2026-10-09T21:11:29+00:00` | نویسنده: agent
- والدها: `e99f99a886d43d14fd345d8d611082538e0bd7a4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ed55fede06588f864c488aa542f9f62ca61a221f)

> Permissions: section + edit tick (default on), edit requires section, food groups edit code, one-time migration

### `7dc7ef6025301a2bc707d1798da44541ce4af50f`

- تاریخ commit: `2026-10-09T21:17:55+00:00` | نویسنده: agent
- والدها: `ed55fede06588f864c488aa542f9f62ca61a221f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7dc7ef6025301a2bc707d1798da44541ce4af50f)

> Permissions: edit ticks for food sections, OLA, governance, holidays, backup; view codes gate pages, edit codes gate writes

### `d90e11419b260a2bc2cf3b5d7f79f4f2499a1f2d`

- تاریخ commit: `2026-10-09T21:23:41+00:00` | نویسنده: agent
- والدها: `7dc7ef6025301a2bc707d1798da44541ce4af50f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d90e11419b260a2bc2cf3b5d7f79f4f2499a1f2d)

> Permissions: settings sections get edit ticks; writes need edit code; read-only panels are inert

### `649579aa15e367adb7bec343fe6caffe1fabd1e3`

- تاریخ commit: `2026-10-09T21:28:34+00:00` | نویسنده: agent
- والدها: `d90e11419b260a2bc2cf3b5d7f79f4f2499a1f2d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/649579aa15e367adb7bec343fe6caffe1fabd1e3)

> Permissions: ticket edit tick under view_unit; reply and rate enforced by code; migration keeps prior access

### `d62c75682a701fa1da048a8f678d660bb8575909`

- تاریخ commit: `2026-10-09T21:32:48+00:00` | نویسنده: agent
- والدها: `649579aa15e367adb7bec343fe6caffe1fabd1e3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d62c75682a701fa1da048a8f678d660bb8575909)

> Split remaining edit permissions: bulk, decide, remote_extract; dashboard gate
> 
> - queue.view -> ticket.bulk, supervisor.panel -> supervisor.decide, domain.scan -> asset.remote_extract (labels: ویرایش)
> - domain_scan_* actions gated by asset.remote_extract; domain-scan panel inert without it
> - dashboard page gated by dash.view; migration grants dash.view to stored roles
> - domain.scan holders get asset.remote_extract (migration)
> - access-coverage baseline: dash.view and asset.remote_extract are now used

### `6f014155d46bbe86a75c7c28f3b961230326149c`

- تاریخ commit: `2026-10-09T21:38:31+00:00` | نویسنده: agent
- والدها: `d62c75682a701fa1da048a8f678d660bb8575909`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/6f014155d46bbe86a75c7c28f3b961230326149c)

> Food menu: editor beside calendar, narrower calendar, wider editor (1101px+)

### `73671c6be941ed93c64d13e9b85ee1e13c39e7a8`

- تاریخ commit: `2026-10-09T21:39:22+00:00` | نویسنده: agent
- والدها: `6f014155d46bbe86a75c7c28f3b961230326149c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/73671c6be941ed93c64d13e9b85ee1e13c39e7a8)

> Remove legacy admin role: migrate rows (primary->primary_admin, others->supervisor, counts stored), drop admin from role lists and tests

### `042e41f68b99f932450f4d852efd7d0ba206b96d`

- تاریخ commit: `2026-10-09T21:40:20+00:00` | نویسنده: agent
- والدها: `73671c6be941ed93c64d13e9b85ee1e13c39e7a8`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/042e41f68b99f932450f4d852efd7d0ba206b96d)

> Test: module-level grant limits print-fish to food groups (routes + nav)

### `94bd0cf431098aee56280b7bbab87a4cc25e66c4`

- تاریخ commit: `2026-10-09T21:41:12+00:00` | نویسنده: agent
- والدها: `042e41f68b99f932450f4d852efd7d0ba206b96d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/94bd0cf431098aee56280b7bbab87a4cc25e66c4)

> Holidays and backup: write areas inert without holidays.manage / backup.manage (server still enforces)

### `76e21fda9e51f1cebddcee46ff196d16e9826a14`

- تاریخ commit: `2026-10-09T21:52:03+00:00` | نویسنده: agent
- والدها: `94bd0cf431098aee56280b7bbab87a4cc25e66c4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/76e21fda9e51f1cebddcee46ff196d16e9826a14)

> Default user role: remove print-fish food, governance, cddvd/traffic codes (foodorder.self kept); migrate stored user rows unless customized

### `2b9635aaca5c716fda3043c06cd603d74e411ce8`

- تاریخ commit: `2026-10-09T22:05:41+00:00` | نویسنده: agent
- والدها: `76e21fda9e51f1cebddcee46ff196d16e9826a14`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/2b9635aaca5c716fda3043c06cd603d74e411ce8)

> Print-fish limited to primary_admin, support_manager (no settings), inspector (dashboard+groups); remove food.orders codes; editor column 400px

### `0cba9a33c57a242da3969d4172f9c361dc4a0138`

- تاریخ commit: `2026-10-09T22:30:19+00:00` | نویسنده: agent
- والدها: `2b9635aaca5c716fda3043c06cd603d74e411ce8`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/0cba9a33c57a242da3969d4172f9c361dc4a0138)

> Guest meal cap follows the number of visitors inside at punch time (traffic_visits); fixed cap is fallback only

### `03f2d57b8de046e3de8731cd8e78186dea76397c`

- تاریخ commit: `2026-10-09T22:43:49+00:00` | نویسنده: agent
- والدها: `0cba9a33c57a242da3969d4172f9c361dc4a0138`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/03f2d57b8de046e3de8731cd8e78186dea76397c)

> Guest cap: variable = entries - exits before cutoff (after-cutoff exits do not reduce); fixed/variable switch and cutoff in guest form

### `e728b200ac3d3261beee147f1fff5a8c98392598`

- تاریخ commit: `2026-10-09T22:51:08+00:00` | نویسنده: agent
- والدها: `03f2d57b8de046e3de8731cd8e78186dea76397c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/e728b200ac3d3261beee147f1fff5a8c98392598)

> Guest variable cap: active daily 11:00-15:00 (0 outside), cutoff default 12:01

### `66177d92a639c1e68d80f64351d1bb16a2c24b98`

- تاریخ commit: `2026-10-09T23:32:22+00:00` | نویسنده: agent
- والدها: `e728b200ac3d3261beee147f1fff5a8c98392598`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/66177d92a639c1e68d80f64351d1bb16a2c24b98)

> Guest cap: configurable start (11:00), cutoff (12:01) and end (15:00) times in guest card form

### `b8348082de288b52922dd956b96cd8caf83a975f`

- تاریخ commit: `2026-10-09T23:47:30+00:00` | نویسنده: agent
- والدها: `66177d92a639c1e68d80f64351d1bb16a2c24b98`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b8348082de288b52922dd956b96cd8caf83a975f)

> Traffic: field-level error above form with focus; open-visits list with quick exit on dashboard; exit not overwritten; guest max label

### `e32022fe32d098b1e0f03ce835b96468689e2713`

- تاریخ commit: `2026-10-10T00:03:32+00:00` | نویسنده: agent
- والدها: `b8348082de288b52922dd956b96cd8caf83a975f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/e32022fe32d098b1e0f03ce835b96468689e2713)

> Traffic: open-visits panel below charts. Printer: print type shown from saved UI mode (fixes network/Windows flip after save)

### `4f3d0fbbe786f5942928a98c35d170d4b8cdb5b0`

- تاریخ commit: `2026-10-10T00:32:57+00:00` | نویسنده: agent
- والدها: `e32022fe32d098b1e0f03ce835b96468689e2713`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4f3d0fbbe786f5942928a98c35d170d4b8cdb5b0)

> Particle network background (black on cream) on login and dashboard; open-visits list covers all days; past-day exit needs manual time

### `2d8e95aecfaef10a60de60325d57128df993da29`

- تاریخ commit: `2026-10-10T00:43:55+00:00` | نویسنده: agent
- والدها: `4f3d0fbbe786f5942928a98c35d170d4b8cdb5b0`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/2d8e95aecfaef10a60de60325d57128df993da29)

> Guest form: 24h HH:MM fields with validation; live variable-cap box refreshed every minute (GET guest-cap); more particles

### `530d61ea2a00ca8fb0f9ddb612f1da37467228f0`

- تاریخ commit: `2026-10-10T00:56:43+00:00` | نویسنده: agent
- والدها: `2d8e95aecfaef10a60de60325d57128df993da29`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/530d61ea2a00ca8fb0f9ddb612f1da37467228f0)

> Help guide page (page=help): step-by-step system guide with real panel screenshots and examples; linked from support menu

### `dcbce59587c73008a34f4bdc43c3e986ca3239ef`

- تاریخ commit: `2026-10-10T05:17:22+00:00` | نویسنده: agent
- والدها: `530d61ea2a00ca8fb0f9ddb612f1da37467228f0`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/dcbce59587c73008a34f4bdc43c3e986ca3239ef)

> Ticket form: hide 'سیستم مرتبط' and support fields by service group (CSS label display overrode hidden); bump style v3.4.24

### `5df1a29a95d54d19a6023a250c0985c7b9d3ba9e`

- تاریخ commit: `2026-10-10T05:36:42+00:00` | نویسنده: agent
- والدها: `dcbce59587c73008a34f4bdc43c3e986ca3239ef`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5df1a29a95d54d19a6023a250c0985c7b9d3ba9e)

> Food orders list: default = today to end of current Jalali month (past days drop off); date-range filter for self (my-orders) and proxy (proxy-orders); Jalali date validation with round-trip check; test 16/16

### `dfd4d3aef10621ea396d694629d0f062f09818ed`

- تاریخ commit: `2026-10-10T06:14:08+00:00` | نویسنده: agent
- والدها: `5df1a29a95d54d19a6023a250c0985c7b9d3ba9e`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/dfd4d3aef10621ea396d694629d0f062f09818ed)

> Food groups: fix ltrim TypeError on numeric national keys (order map); fix SQL quoting in L_UID duplicate check; friendly duplicate messages for L_UID/card/member; employee save rejects duplicate national_code (insert and edit); test 9/9

### `2b143e4263b87c075068eca562a95792cd7c1602`

- تاریخ commit: `2026-10-10T06:41:53+00:00` | نویسنده: agent
- والدها: `dfd4d3aef10621ea396d694629d0f062f09818ed`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/2b143e4263b87c075068eca562a95792cd7c1602)

> راهنمای کامل سامانه با ۳۶ تصویر؛ لینک راهنما در منوی اصلی

### `ef9bf6f897f54a1a895411b8bfafd8667c0ed488`

- تاریخ commit: `2026-10-10T07:24:42+00:00` | نویسنده: agent
- والدها: `2b143e4263b87c075068eca562a95792cd7c1602`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ef9bf6f897f54a1a895411b8bfafd8667c0ed488)

> راهنمای تکمیلی؛ فیلتر بازه با تقویم شمسی؛ نام غذا در یک خط تقویم؛ انیمیشن ورود از چپ به راست و پررنگ؛ ویرایش معاونت و اداره؛ منوی واحد بالا باز می‌شود

### `f110ca6e1b3d2202f0790b1368ac8018dad73b99`

- تاریخ commit: `2026-10-10T08:03:54+00:00` | نویسنده: agent
- والدها: `ef9bf6f897f54a1a895411b8bfafd8667c0ed488`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f110ca6e1b3d2202f0790b1368ac8018dad73b99)

> کارایی: بررسی وضعیت نصب بدون COUNT(*) روی جداول بزرگ؛ فهرست جداول یک‌بار خوانده شود؛ DDL جدول activity_logs فقط وقتی جدول نیست

### `f80d09478a47315849b9fbcd40c90bee7fad5efb`

- تاریخ commit: `2026-10-10T08:38:05+00:00` | نویسنده: agent
- والدها: `f110ca6e1b3d2202f0790b1368ac8018dad73b99`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f80d09478a47315849b9fbcd40c90bee7fad5efb)

> feat: guest meal requests by managers + requested guest cap mode
> 
> - New table food_guest_requests (upgrade-1.39-food-guest-requests.sql; no data loss)
> - Managers (primary_admin/supervisor/support_manager/manager) can request guest meals for future days
> - Routes: guest-requests (GET), guest-request (POST), guest-request-cancel (POST)
> - Guest cap mode 'requested': cap per day = sum of active manager guest requests for that day
> - Admin radio in food-ticket SPA to switch fixed/variable/requested
> - Functional test: tools/test_food_guest_requests.php (15/15)

### `118bad447dce0f992f8ca4f73f26aab18726b9ce`

- تاریخ commit: `2026-10-10T09:52:18+00:00` | نویسنده: agent
- والدها: `f80d09478a47315849b9fbcd40c90bee7fad5efb`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/118bad447dce0f992f8ca4f73f26aab18726b9ce)

> feat: معاونت درخواست‌کننده برای مهمان + گزارش سفارش مهمان با فیلتر معاونت
> 
> - food_guest_requests.deputy_unit_id (upgrade-1.40؛ فقط ADD COLUMN)، الزامی در فرم؛ فهرست از چارت سازمانی
> - بازرسی هم جزو نقش‌های مدیر برای سفارش مهمان شد
> - گزارش جدید «سفارش مهمان (درخواست مدیران)» با لیست کشویی معاونت‌ها + «همه»
> - API: food-ticket guest-requests-report (food.reports)
> - تست: 25/25

### `de188aa1de806410580058d3dacad4aa87c99f47`

- تاریخ commit: `2026-10-10T09:59:24+00:00` | نویسنده: agent
- والدها: `118bad447dce0f992f8ca4f73f26aab18726b9ce`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/de188aa1de806410580058d3dacad4aa87c99f47)

> feat: معاونت درخواست‌کننده خودکار از چارت سازمانی کاربر
> 
> - food_guest_user_deputy_id: از واحد کاربر و واحدهای مدیریتی او به سمت بالا تا واحد deputy
> - ثبت درخواست مهمان، معاونت را از سرور می‌گیرد؛ مقدار ارسالی از مرورگر نادیده گرفته می‌شود
> - کاربر بدون جایگاه در چارت: ثبت غیرفعال با پیام روشن
> - فرم: نمایش فقط‌خواندنی معاونت؛ بدون لیست کشویی
> - تست: 30/30

### `b02e02233b4352f2433c0f20b69d362da9376923`

- تاریخ commit: `2026-10-10T14:40:47+00:00` | نویسنده: agent
- والدها: `de188aa1de806410580058d3dacad4aa87c99f47`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b02e02233b4352f2433c0f20b69d362da9376923)

> fix: صفحهٔ سفارش غذا با نبودن جدول سفارش مهمان خطای 500 نمی‌دهد
> 
> - food_order_schema_ensure دیگر جدول food_guest_requests را اجباری نمی‌کند
> - food_guest_schema_ready(): بررسی بی‌خطر جدول و ستون معاونت؛ بخش مهمان فقط وقتی آماده است نمایش داده می‌شود
> - API سفارش مهمان بدون migration پیام فارسی روشن برمی‌گرداند
> - تست: 30/30، دسترسی 52/52، بازهٔ غذا 16/16

### `ab0a65c94ed99d13e7a2cd2dd0c625eaac436f24`

- تاریخ commit: `2026-10-10T14:52:17+00:00` | نویسنده: agent
- والدها: `b02e02233b4352f2433c0f20b69d362da9376923`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ab0a65c94ed99d13e7a2cd2dd0c625eaac436f24)

> fix: نمایش دلیل نبودن بخش سفارش مهمان برای مدیران
> 
> - food_guest_schema_missing(): جدول/ستون‌های ناقص و نام دیتابیس فعلی
> - مدیر به‌جای نبود بی‌صدای بخش، پیام قرمز با موارد ناقص می‌بیند
> - کش اسکریپت سفارش مهمان: v=2
> - تست: 30/30

### `ad25ed70ef05a146c1741f99a0226d6139e5bb6e`

- تاریخ commit: `2026-10-10T15:06:52+00:00` | نویسنده: agent
- والدها: `ab0a65c94ed99d13e7a2cd2dd0c625eaac436f24`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ad25ed70ef05a146c1741f99a0226d6139e5bb6e)

> feat: سفارش غذای مهمان در پاپ‌آپ از کنار دکمهٔ ماه قبل؛ حذف انتخاب نوع غذا
> 
> - دکمهٔ «سفارش غذای مهمان» کنار «ماه قبل» (فقط مدیران با جدول آماده)
> - فرم و فهرست درخواست‌ها در پاپ‌آپ؛ بعد از ثبت بسته می‌شود
> - فیلد نوع غذا حذف شد؛ غذای مهمان را سلف طبق تشخیص مدیر تعیین می‌کند
> - تست: 30/30 (سفارش مهمان)، 52/52 (دسترسی)

### `7a16c145cf74da0b5f6e18714e65c086535a89fb`

- تاریخ commit: `2026-10-10T15:16:14+00:00` | نویسنده: agent
- والدها: `ad25ed70ef05a146c1741f99a0226d6139e5bb6e`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7a16c145cf74da0b5f6e18714e65c086535a89fb)

> حذف متن توضیحی اضافه از پاپ‌آپ سفارش مهمان

### `ba3b0d1888c41ba0f73c70f355fcd2c12a4c3e04`

- تاریخ commit: `2026-10-10T17:23:34+00:00` | نویسنده: agent
- والدها: `7a16c145cf74da0b5f6e18714e65c086535a89fb`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ba3b0d1888c41ba0f73c70f355fcd2c12a4c3e04)

> دسترسی سفارش مهمان بر اساس جایگاه مدیریتی چارت؛ پنهان در حالت نیابتی

### `2a2c86696b528fee5421eafd2655041651e154f7`

- تاریخ commit: `2026-10-09T15:49:10+00:00` | نویسنده: Agent Mode
- والدها: `fee7655d75f00d4fe8e30f2dadd29ace2a79d883`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/2a2c86696b528fee5421eafd2655041651e154f7)

> Add project report (docs/REPORT-FA.md)

### `b9df3d4118acde90a76859f076bc4450b88d2a2d`

- تاریخ commit: `2026-10-09T15:50:26+00:00` | نویسنده: Agent Mode
- والدها: `2a2c86696b528fee5421eafd2655041651e154f7 f35bf717f170203935604c7581e5cf478d89bff9`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b9df3d4118acde90a76859f076bc4450b88d2a2d)

> Merge arena-updates: full source with latest fixes into main

### `50d3312dc9b035bb73df0c73bf2025f6fc85e744`

- تاریخ commit: `2026-10-09T16:12:51+00:00` | نویسنده: Agent Mode
- والدها: `b9df3d4118acde90a76859f076bc4450b88d2a2d ea89ba53a3015152206a069d8501eb84d2af5cf7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/50d3312dc9b035bb73df0c73bf2025f6fc85e744)

> Merge arena-updates: revert automatic personnel code assignment in AD sync

### `bc48cbf071d2e18dca9ff4261ad6f010fc341bf6`

- تاریخ commit: `2026-10-09T16:16:20+00:00` | نویسنده: Agent Mode
- والدها: `50d3312dc9b035bb73df0c73bf2025f6fc85e744 8814ff6f55ba9939ce0e86be212ac574b4ae2cd0`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/bc48cbf071d2e18dca9ff4261ad6f010fc341bf6)

> Merge arena-updates: unique personnel code

### `4c337c7f1b05a5f8e4e461975b350388d00c5cdc`

- تاریخ commit: `2026-10-09T17:43:15+00:00` | نویسنده: Agent Mode
- والدها: `bc48cbf071d2e18dca9ff4261ad6f010fc341bf6 08fde460f61f9a7088ce4b4a16342009fe503cd1`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4c337c7f1b05a5f8e4e461975b350388d00c5cdc)

> Merge arena-updates: proxy ordering open to all roles

### `6e0f13f63e91822953c6525178d3e8070620e331`

- تاریخ commit: `2026-10-09T17:56:16+00:00` | نویسنده: Agent Mode
- والدها: `4c337c7f1b05a5f8e4e461975b350388d00c5cdc b172d7e5fafb0e5825635655ce7f02b0cc6a4e31`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/6e0f13f63e91822953c6525178d3e8070620e331)

> Merge arena-updates: food employees are not system login accounts

### `4055d949c320064c9e31f9ea19df1d6d6c6fa1c9`

- تاریخ commit: `2026-10-09T18:31:59+00:00` | نویسنده: Agent Mode
- والدها: `6e0f13f63e91822953c6525178d3e8070620e331 dc8662e47bbb6d2d35e7cce537801285e7440978`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4055d949c320064c9e31f9ea19df1d6d6c6fa1c9)

> Merge arena-updates: cleanup and print-engine fixes

### `92dd25d9b129c3308e74f642de5074ae80fa7573`

- تاریخ commit: `2026-10-10T17:26:02+00:00` | نویسنده: agent
- والدها: `ba3b0d1888c41ba0f73c70f355fcd2c12a4c3e04 4055d949c320064c9e31f9ea19df1d6d6c6fa1c9`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/92dd25d9b129c3308e74f642de5074ae80fa7573)

> Merge remote-tracking branch 'rem/main' into arena-updates

### `82957b3bd310ddf29f94d7b7d79a98049f23c58a`

- تاریخ commit: `2026-10-10T19:31:32+00:00` | نویسنده: agent
- والدها: `92dd25d9b129c3308e74f642de5074ae80fa7573`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/82957b3bd310ddf29f94d7b7d79a98049f23c58a)

> گزارش سفارش مهمان معاونت‌ها در فهرست نوع گزارش؛ ستون‌های ردیف/معاونت/درخواست‌کننده/سازمان/تعداد

### `ffffc27767a3071ad3a793d7c5aeca9521e93979`

- تاریخ commit: `2026-10-10T19:45:21+00:00` | نویسنده: agent
- والدها: `82957b3bd310ddf29f94d7b7d79a98049f23c58a`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ffffc27767a3071ad3a793d7c5aeca9521e93979)

> سقف مهمان دو حالته (درخواست معاونت‌ها در روز جاری / نفرات داخل ساختمان)؛ لیست مهمان‌های پاپ‌آپ فقط امروز تا پس‌فردا

### `0aab446ab14df8ec4048d55e285987ca347aeff3`

- تاریخ commit: `2026-10-10T19:53:03+00:00` | نویسنده: agent
- والدها: `ffffc27767a3071ad3a793d7c5aeca9521e93979`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/0aab446ab14df8ec4048d55e285987ca347aeff3)

> لیست مهمان‌های پاپ‌آپ: روزهای گذشته حذف شد؛ روزهای آینده بدون محدودیت نمایش داده می‌شوند

### `cefe9122e05eed9c343412515512ec87fe554b0d`

- تاریخ commit: `2026-10-10T20:52:53+00:00` | نویسنده: agent
- والدها: `0aab446ab14df8ec4048d55e285987ca347aeff3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/cefe9122e05eed9c343412515512ec87fe554b0d)

> به‌روزرسانی سامانه از پنل تنظیمات (ZIP، فقط مدیر اصلی): بکاپ، مهاجرت‌های ثبت‌شده در schema_updates، کپی فایل؛ راهنمای ورود برای مهمان؛ مجوزهای update.view/update.apply

### `e3c0608609ae8097a9eeeac03673aa471bc2dbd2`

- تاریخ commit: `2026-10-10T20:59:11+00:00` | نویسنده: agent
- والدها: `cefe9122e05eed9c343412515512ec87fe554b0d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/e3c0608609ae8097a9eeeac03673aa471bc2dbd2)

> راهنما فقط بعد از ورود؛ حذف دکمهٔ راهنما از فرم ورود (سامانه فقط برای کاربران دامین است)

### `5fa20ef4e59cdf6e45ebce48dd3fcffc5239c4a1`

- تاریخ commit: `2026-10-11T06:39:16+00:00` | نویسنده: agent
- والدها: `e3c0608609ae8097a9eeeac03673aa471bc2dbd2`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5fa20ef4e59cdf6e45ebce48dd3fcffc5239c4a1)

> بات پاسخ‌گوی سامانه (فاز ۱): پرسش‌های متداول با تطبیق کلیدواژه و غلط املایی، لاگ و بازخورد، صفحه مدیریت، ویجت گفتگو، آپدیت ۱.۴۱ (updates/2026-10-11-chatbot)

### `a152468de5796b214ed3f1e45fd826d72b273cca`

- تاریخ commit: `2026-10-11T06:55:32+00:00` | نویسنده: agent
- والدها: `5fa20ef4e59cdf6e45ebce48dd3fcffc5239c4a1`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/a152468de5796b214ed3f1e45fd826d72b273cca)

> بات پاسخ‌گو فاز ۲: ثبت تیکت از بات (انتقال به فرم موجود با موضوع و شرح)، پیگیری تیکت‌های من و وضعیت تیکت با ticket_scope؛ آپدیت ۱.۴۲ (updates/2026-10-12-chatbot-tickets)

### `5a365e7bc70e0d34f4feb110f8f5225afa0dd866`

- تاریخ commit: `2026-10-11T07:08:38+00:00` | نویسنده: agent
- والدها: `a152468de5796b214ed3f1e45fd826d72b273cca`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5a365e7bc70e0d34f4feb110f8f5225afa0dd866)

> بات: رفع مسدودشدن اسکریپت ویجت توسط CSP (nonce)

### `88d0d1443667e38b7554fa028a310be49e772831`

- تاریخ commit: `2026-10-11T07:14:19+00:00` | نویسنده: agent
- والدها: `5a365e7bc70e0d34f4feb110f8f5225afa0dd866`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/88d0d1443667e38b7554fa028a310be49e772831)

> بات: گزارش بازه‌ای در پنل مدیریت، لینک جزئیات تیکت در پاسخ وضعیت، رفع nonce در اسکریپت تعطیلات footer، راهنمای گزارش

### `5432d488894bafc349a916f6e1b4cccfa1a4013e`

- تاریخ commit: `2026-10-14T20:05:31+00:00` | نویسنده: agent
- والدها: `88d0d1443667e38b7554fa028a310be49e772831`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5432d488894bafc349a916f6e1b4cccfa1a4013e)

> نسخه ۱.۵۵: تعطیلات رسمی ۱۴۰۵/۱۴۰۶، اصلاح تردد خاص (خودترمیمی ستون‌ها و نمایش فیلدها)، فاصلهٔ بالای فیش (قالب و شبکه)، ابزار tools/set_print_gap.php، لیست‌های مرتب‌سازی و خروجی، بستهٔ آپدیت ۱.۵۵

### `f995ae56b0154b200ae33f4d6516b270917aa79f`

- تاریخ commit: `2026-10-14T20:56:48+00:00` | نویسنده: agent
- والدها: `5432d488894bafc349a916f6e1b4cccfa1a4013e`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f995ae56b0154b200ae33f4d6516b270917aa79f)

> سند ادامهٔ کار (Handoff): وضعیت نسخه ۱.۵۵، مشکلات باز، ماژول‌های آینده، قواعد

### `bf02da69e42ceee15e208560de44f95e972a0305`

- تاریخ commit: `2026-10-14T20:57:00+00:00` | نویسنده: agent
- والدها: `f995ae56b0154b200ae33f4d6516b270917aa79f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/bf02da69e42ceee15e208560de44f95e972a0305)

> سند ادامه‌کار: اصلاح یادداشت remote

### `befed1815a3516460554dbd33d2201b1a0471f97`

- تاریخ commit: `2026-10-16T11:03:38+00:00` | نویسنده: alirezarahimy-code
- والدها: `bf02da69e42ceee15e208560de44f95e972a0305`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/befed1815a3516460554dbd33d2201b1a0471f97)

> افزودن افکت نمایشی انتخابی و بسته آپدیت ۱.۵۶
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `8e4fb89cc5d43ef8c0ad164eee2268646b6746e5`

- تاریخ commit: `2026-10-16T12:05:02+00:00` | نویسنده: alirezarahimy-code
- والدها: `befed1815a3516460554dbd33d2201b1a0471f97`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8e4fb89cc5d43ef8c0ad164eee2268646b6746e5)

> تکمیل تب ذرات معلق در افکت نمایشی نسخه ۱.۵۶
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `647004ca97072cc5d3e4a721cfa47c992300cf4d`

- تاریخ commit: `2026-10-16T21:30:53+00:00` | نویسنده: alirezarahimy-code
- والدها: `8e4fb89cc5d43ef8c0ad164eee2268646b6746e5`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/647004ca97072cc5d3e4a721cfa47c992300cf4d)

> Release 1.56: same-day guest orders and traffic import
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d054f286fb30bb562e931087d8b22875cc810c5a`

- تاریخ commit: `2026-10-16T21:33:18+00:00` | نویسنده: alirezarahimy-code
- والدها: `647004ca97072cc5d3e4a721cfa47c992300cf4d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d054f286fb30bb562e931087d8b22875cc810c5a)

> Add downloadable 1.56 update archive
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `c4c4e3825e3032e22d0121294189500bbc87a349`

- تاریخ commit: `2026-10-17T13:01:43+00:00` | نویسنده: alirezarahimy-code
- والدها: `d054f286fb30bb562e931087d8b22875cc810c5a`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c4c4e3825e3032e22d0121294189500bbc87a349)

> نسخه ۱.۵۷: تکمیل گردش درخواست، فارسی‌سازی و پیام‌های متحرک ورود
> 
> - تکمیل گردش درخواست از ممیزی ITSM: لغو قابل‌ردیابی با قفل تغییر وضعیت،
>   پیام اجباری انتظار/حل‌شده، بستن خودکار قابل‌تنظیم، صفحه‌بندی صف/گزارش/
>   سوپروایزر، رفع گرسنگی کرون SLA/OLA، escalation سطح ۲ به ادمین اصلی،
>   محدوده مدیر پشتیبانی، یکسان‌سازی pending (مهاجرت‌های ۰۰۴ و ۰۰۵)
> - فارسی‌سازی «تیکت» به «درخواست» در متن‌های کاربرپسند؛ شناسه‌های فنی لاتین
>   ماندند؛ «تیکت» در چت‌بات/جست‌وجو/راهنما alias است
> - صفحه ورود: تا ۵ پیام چرخشی با سرعت (۵ تا ۶۰ ثانیه) و اندازه قلم
>   (۱۱ تا ۲۲ پیکسل) قابل‌تنظیم از مدیریت صفحه ورود
> - بسته: downloads/ticket-update-1.57.zip + updates/2026-10-17-release-157/
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `c6bec575e6dab18738be2ff777d23a4d93079bbe`

- تاریخ commit: `2026-10-17T14:08:42+00:00` | نویسنده: alirezarahimy-code
- والدها: `c4c4e3825e3032e22d0121294189500bbc87a349`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c6bec575e6dab18738be2ff777d23a4d93079bbe)

> انتشار ۱٫۵۸: آریا، بات سخنگوی آفلاین (گویندگی eSpeak سرور + مدیریت کامل)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `aee564ca38f43d36e21e34e840c6e366ccfb4d87`

- تاریخ commit: `2026-10-17T15:30:08+00:00` | نویسنده: alirezarahimy-code
- والدها: `c6bec575e6dab18738be2ff777d23a4d93079bbe`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/aee564ca38f43d36e21e34e840c6e366ccfb4d87)

> هات‌فیکس ۱٫۵۸٫۱: حذف تکه‌خطای ته index.php (رفع Parse error خط ۵۴۸۶)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `7c5c3775edaf237fe59a6db85463c9f17f64d1c3`

- تاریخ commit: `2026-10-17T16:30:03+00:00` | نویسنده: alirezarahimy-code
- والدها: `aee564ca38f43d36e21e34e840c6e366ccfb4d87`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7c5c3775edaf237fe59a6db85463c9f17f64d1c3)

> هات‌فیکس ۱٫۵۸٫۲: اجرای امن موتور صوتی (خروجی فایل، خاتمهٔ اجباری، راستی‌آزمایی مسیر)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `fa171353e5e516963c640785553e737b835f5cfb`

- تاریخ commit: `2026-10-17T16:41:00+00:00` | نویسنده: alirezarahimy-code
- والدها: `7c5c3775edaf237fe59a6db85463c9f17f64d1c3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/fa171353e5e516963c640785553e737b835f5cfb)

> هات‌فیکس ۱٫۵۸٫۳: اعتبارسنجی WAV، خودترمیمی کش و دانلود نمونه صدا
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `6ee48ce49c9b2c734d52a43fb28f0cbef40f592b`

- تاریخ commit: `2026-10-17T16:53:30+00:00` | نویسنده: alirezarahimy-code
- والدها: `fa171353e5e516963c640785553e737b835f5cfb`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/6ee48ce49c9b2c734d52a43fb28f0cbef40f592b)

> هات‌فیکس ۱٫۵۸٫۴: پخش مستقیم صدا، بررسی صدای فارسی و سرعت شمرده‌تر
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `27fef605ee5228fb0e721a3db456ceff1b7345ad`

- تاریخ commit: `2026-10-17T17:57:48+00:00` | نویسنده: alirezarahimy-code
- والدها: `6ee48ce49c9b2c734d52a43fb28f0cbef40f592b`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/27fef605ee5228fb0e721a3db456ceff1b7345ad)

> نسخهٔ ۱٫۵۹: صدای طبیعی آفلاین Piper و خواندن محاوره‌ای
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `df88aafe370df4977a485a99528305216cb3d316`

- تاریخ commit: `2026-10-17T18:29:52+00:00` | نویسنده: alirezarahimy-code
- والدها: `27fef605ee5228fb0e721a3db456ceff1b7345ad`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/df88aafe370df4977a485a99528305216cb3d316)

> هات‌فیکس ۱٫۵۹٫۱: صدای مرد، کسرهٔ اضافه و تلفظ درست س
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `fe70799ad3f46a1916be1677014b73903535e7c5`

- تاریخ commit: `2026-10-17T19:09:24+00:00` | نویسنده: alirezarahimy-code
- والدها: `df88aafe370df4977a485a99528305216cb3d316`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/fe70799ad3f46a1916be1677014b73903535e7c5)

> هات‌فیکس ۱٫۵۹٫۲: اعداد با حروف و کسره فقط بین واژه‌ها
> 
> قواعد نرمال‌ساز TTS فارسی: عدد→حروف، ی/ک عربی، مخفف‌های IT، کسره فقط بین دو کلمهٔ جدا بدون اعراب داخل واژه.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `916f651293381dd0cf91d4e90a08c2f75256a171`

- تاریخ commit: `2026-10-17T19:38:40+00:00` | نویسنده: alirezarahimy-code
- والدها: `fe70799ad3f46a1916be1677014b73903535e7c5`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/916f651293381dd0cf91d4e90a08c2f75256a171)

> نسخهٔ ۱٫۵۹٫۳: ربط پاسخ بات و خوداظهاری محل تحویل غذا
> 
> بات بخش بی‌ربط راهنما را حدس نمی‌زند. کاربر بدون گروه دائم می‌تواند محل تحویل امروز را اعلام کند؛ بعد از چاپ فیش گروه قفل می‌شود.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `6a219a55742344940f18aec229c31fc42f0f8a49`

- تاریخ commit: `2026-10-17T20:15:56+00:00` | نویسنده: alirezarahimy-code
- والدها: `916f651293381dd0cf91d4e90a08c2f75256a171`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/6a219a55742344940f18aec229c31fc42f0f8a49)

> نسخهٔ ۱٫۵۹٫۴: بستهٔ تجمعی از ۱٫۵۷، حذف بوق ربات و پاپ‌آپ تغییر سلف
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `3f0f56b229285c87730e8666bf5e2e1b539c3773`

- تاریخ commit: `2026-10-17T20:19:50+00:00` | نویسنده: alirezarahimy-code
- والدها: `6a219a55742344940f18aec229c31fc42f0f8a49`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/3f0f56b229285c87730e8666bf5e2e1b539c3773)

> آرشیو کامل سورس نسخهٔ ۱٫۵۹٫۴ برای نصب یا جایگزینی پوشه
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `8ae7f32b4fd9bef1ff184fdc8089929a5da750a5`

- تاریخ commit: `2026-10-17T21:19:16+00:00` | نویسنده: alirezarahimy-code
- والدها: `3f0f56b229285c87730e8666bf5e2e1b539c3773`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8ae7f32b4fd9bef1ff184fdc8089929a5da750a5)

> نسخهٔ ۱.۵۹.۵: تلفظ آریا (واژه‌نامه، کسره، آزمایشگاه) و اصلاح UI غذا
> 
> قواعد متنی TTS مستقل از موتور، حذف سَ همگانی، حفظ نیم‌فاصله و هٔ، آزمایشگاه در مدیریت آریا. مهاجرت دیتابیس ندارد.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `23615e9add77ec2874a1341f96e4f2dded8175e9`

- تاریخ commit: `2026-10-17T21:44:34+00:00` | نویسنده: alirezarahimy-code
- والدها: `8ae7f32b4fd9bef1ff184fdc8089929a5da750a5`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/23615e9add77ec2874a1341f96e4f2dded8175e9)

> نسخهٔ ۱.۵۹.۶: هات‌فیکس کسرهٔ اضافه (فقط فهرست دوواژه‌ای)
> 
> کسرهٔ حدسی پیش‌فرض خاموش شد. متن بلند تکه‌تکه ساخته می‌شود. منفی فقط با فاصله/آغاز.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `f64d032a9827d69879f625219379e6e302d8cc0f`

- تاریخ commit: `2026-10-17T21:57:03+00:00` | نویسنده: alirezarahimy-code
- والدها: `23615e9add77ec2874a1341f96e4f2dded8175e9`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f64d032a9827d69879f625219379e6e302d8cc0f)

> نسخهٔ کامل و آپدیت تجمعی ۱.۵۹.۶ از ۱.۵۷
> 
> بستهٔ ticket-full-1.59.6.zip برای جایگزینی پوشه یا نصب از صفر،
> و ticket-update-1.59.6.zip با همهٔ فایل‌های تغییرکرده از ۱.۵۷.
> مهاجرت دیتابیس ندارد.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `12bc1f8dde02f9c9fff2e515e17d7b4655360027`

- تاریخ commit: `2026-10-18T07:21:16+00:00` | نویسنده: alirezarahimy-code
- والدها: `f64d032a9827d69879f625219379e6e302d8cc0f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/12bc1f8dde02f9c9fff2e515e17d7b4655360027)

> نسخهٔ ۱.۵۹.۷: مهلت سلف، درخواست لغو روز بسته، فهرست ماه بعد
> 
> بستهٔ تجمعی از ۱.۵۷. مهاجرت دیتابیس جدا ندارد.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `4f40b97c37af54355e7bfa349295d3e96eb28bf9`

- تاریخ commit: `2026-10-18T09:59:09+00:00` | نویسنده: alirezarahimy-code
- والدها: `12bc1f8dde02f9c9fff2e515e17d7b4655360027`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4f40b97c37af54355e7bfa349295d3e96eb28bf9)

> نسخهٔ ۱.۵۹.۸: دیده شدن درخواست لغو، لیست اعضا، ردیف پایش
> 
> بستهٔ تجمعی از ۱.۵۷. مهاجرت دیتابیس جدا ندارد.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `60b90973f8724dcc25e9580007773b17018eed6b`

- تاریخ commit: `2026-10-18T10:44:06+00:00` | نویسنده: alirezarahimy-code
- والدها: `4f40b97c37af54355e7bfa349295d3e96eb28bf9`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/60b90973f8724dcc25e9580007773b17018eed6b)

> نسخهٔ ۱.۶۰: کارشناس مستقیم زیر معاونت و نفرات دامینی/محلی
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `a6d8ce26ba864988bcf01080daf405ca2c5a66cd`

- تاریخ commit: `2026-10-18T11:29:11+00:00` | نویسنده: alirezarahimy-code
- والدها: `60b90973f8724dcc25e9580007773b17018eed6b`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/a6d8ce26ba864988bcf01080daf405ca2c5a66cd)

> نسخهٔ ۱.۶۰.۱: اصلاح لغو غذا، مهلت سلف و کادر کارشناس معاونت
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `3e77792b1b52515e938e5ad3a952cc2115428446`

- تاریخ commit: `2026-10-18T13:41:10+00:00` | نویسنده: alirezarahimy-code
- والدها: `a6d8ce26ba864988bcf01080daf405ca2c5a66cd`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/3e77792b1b52515e938e5ad3a952cc2115428446)

> نسخهٔ ۱.۶۰.۲: حساب محلی برای کارمند دستی و تبدیل خودکار رکوردهای قدیمی غذا
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `7003e62e936d5c421520fac3f5d96065c723a098`

- تاریخ commit: `2026-10-18T17:24:55+00:00` | نویسنده: alirezarahimy-code
- والدها: `3e77792b1b52515e938e5ad3a952cc2115428446`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7003e62e936d5c421520fac3f5d96065c723a098)

> نسخهٔ ۱.۶۰.۲: ترمیم صف کاری و ستون‌های جاافتاده (نصب مجدد همان نسخه)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `57beb3b443c64d74aa27a2cfc4e5243f730ba40f`

- تاریخ commit: `2026-10-19T09:35:57+00:00` | نویسنده: alirezarahimy-code
- والدها: `7003e62e936d5c421520fac3f5d96065c723a098`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/57beb3b443c64d74aa27a2cfc4e5243f730ba40f)

> نسخهٔ ۱.۶۰.۳: نام بازرسی به‌جای نگهبان، اعلان لغو غذا و مهلت سلف
> 
> نام نگهبان در رابط سامانه به بازرسی تبدیل شد. اعلان درخواست لغو غذا فقط به مدیر پشتیبانی و مدیر اصلی می‌رسد. سقف سفارش نیابتی شل شد. فیلد مهلت تغییر سلف در ویرایش گروه واضح است و مدیر پشتیبانی می‌تواند اعلام را لغو کند.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `5e5e6a23f15de67bead2bc2eb15477e7983cbf27`

- تاریخ commit: `2026-10-21T14:27:16+00:00` | نویسنده: alirezarahimy-code
- والدها: `57beb3b443c64d74aa27a2cfc4e5243f730ba40f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5e5e6a23f15de67bead2bc2eb15477e7983cbf27)

> نسخهٔ ۱.۶۰.۴: ثبت قطعی کاربر دامین و حذف حساب محلی باقی‌مانده
> 
> همگام‌سازی دامین و ذخیرهٔ کد پرسنلی حساب محلی extra را به کاربر دامین وصل می‌کند و فقط همان ردیف extra را حذف می‌کند. ادمین اصلی دکمهٔ حذف کاربر دارد.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `dc7c28b78557487939e27c201f4886ab036748e6`

- تاریخ commit: `2026-10-26T21:31:42+00:00` | نویسنده: alirezarahimy-code
- والدها: `5e5e6a23f15de67bead2bc2eb15477e7983cbf27`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/dc7c28b78557487939e27c201f4886ab036748e6)

> Publish cumulative 1.62.24 update bundle
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `34d88adb6b5be68e09666b8e1bea6e06979f0cac`

- تاریخ commit: `2026-10-26T17:53:50+00:00` | نویسنده: alirezarahimy-code
- والدها: `5e5e6a23f15de67bead2bc2eb15477e7983cbf27`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/34d88adb6b5be68e09666b8e1bea6e06979f0cac)

> Add 1.62.21 delivery report and safe health log export
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `dcec57bb9b8872e24183df568392d8ce8c78f904`

- تاریخ commit: `2026-10-26T18:07:07+00:00` | نویسنده: alirezarahimy-code
- والدها: `34d88adb6b5be68e09666b8e1bea6e06979f0cac`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/dcec57bb9b8872e24183df568392d8ce8c78f904)

> Make 1.62.22 a cumulative safe-log update
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `0e22b39e168c8c2a06dab9c8e73f6b4f4f2e8f22`

- تاریخ commit: `2026-10-26T18:29:07+00:00` | نویسنده: alirezarahimy-code
- والدها: `dcec57bb9b8872e24183df568392d8ce8c78f904`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/0e22b39e168c8c2a06dab9c8e73f6b4f4f2e8f22)

> Add safe health export button to system logs
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `80ac3ca61e5f2a9933650bce436fd613b49cd39f`

- تاریخ commit: `2026-10-26T18:53:11+00:00` | نویسنده: alirezarahimy-code
- والدها: `0e22b39e168c8c2a06dab9c8e73f6b4f4f2e8f22`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/80ac3ca61e5f2a9933650bce436fd613b49cd39f)

> Improve readability of system health logs and export
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `76f0cd2474666ccd933e58b9c939b6b294d483a2`

- تاریخ commit: `2026-10-26T19:34:07+00:00` | نویسنده: alirezarahimy-code
- والدها: `80ac3ca61e5f2a9933650bce436fd613b49cd39f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/76f0cd2474666ccd933e58b9c939b6b294d483a2)

> Add organization tree source bundle
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d9e762a34c3683744df3ec32ada56ce06686175b`

- تاریخ commit: `2026-10-26T21:33:02+00:00` | نویسنده: alirezarahimy-code
- والدها: `dc7c28b78557487939e27c201f4886ab036748e6 76f0cd2474666ccd933e58b9c939b6b294d483a2`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d9e762a34c3683744df3ec32ada56ce06686175b)

> Merge remote-tracking branch 'origin/arena/d57ac025-ticket' into arena/d57ac025-ticket
> 
> # Conflicts:
> #	downloads/index.html
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `3add70a3686b841d6d6f667337a8e401aca7fe14`

- تاریخ commit: `2026-10-27T04:57:20+00:00` | نویسنده: alirezarahimy-code
- والدها: `d9e762a34c3683744df3ec32ada56ce06686175b`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/3add70a3686b841d6d6f667337a8e401aca7fe14)

> Fix system health log counts and safe references
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `8af85bace4378743afc52c57c545b86e18bbe3bd`

- تاریخ commit: `2026-10-27T05:35:20+00:00` | نویسنده: alirezarahimy-code
- والدها: `3add70a3686b841d6d6f667337a8e401aca7fe14`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8af85bace4378743afc52c57c545b86e18bbe3bd)

> Fix technical inventory form TypeError
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `95aa22ca583970ba8ec6932907bc6a5e5fc986f7`

- تاریخ commit: `2026-10-27T05:50:42+00:00` | نویسنده: alirezarahimy-code
- والدها: `8af85bace4378743afc52c57c545b86e18bbe3bd`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/95aa22ca583970ba8ec6932907bc6a5e5fc986f7)

> Correct food print diagnostic log severity
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `16e33ad83d9ebce80da6e712a4d4499790e6e326`

- تاریخ commit: `2026-10-27T06:39:01+00:00` | نویسنده: alirezarahimy-code
- والدها: `95aa22ca583970ba8ec6932907bc6a5e5fc986f7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/16e33ad83d9ebce80da6e712a4d4499790e6e326)

> Ship technical asset profile form update 1.62.28
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `cb7cdb72cdd5f249252ebc512b658d1efe912730`

- تاریخ commit: `2026-10-27T08:34:32+00:00` | نویسنده: alirezarahimy-code
- والدها: `16e33ad83d9ebce80da6e712a4d4499790e6e326`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/cb7cdb72cdd5f249252ebc512b658d1efe912730)

> Add manual food ticket issue without physical print
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `41a3089d8bd7b67b942c51262096f29d26d7e658`

- تاریخ commit: `2026-10-27T08:48:51+00:00` | نویسنده: alirezarahimy-code
- والدها: `cb7cdb72cdd5f249252ebc512b658d1efe912730`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/41a3089d8bd7b67b942c51262096f29d26d7e658)

> Move manual ticket issuance to admin settings
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `de7094a5f04b3ef8c5d5c0d5a471814a5ebaaa1b`

- تاریخ commit: `2026-10-27T09:05:05+00:00` | نویسنده: alirezarahimy-code
- والدها: `41a3089d8bd7b67b942c51262096f29d26d7e658`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/de7094a5f04b3ef8c5d5c0d5a471814a5ebaaa1b)

> Fix existing manual attendance ticket flow
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `a5b828fac01a75e4d03aa433a6a977aa06bf7b65`

- تاریخ commit: `2026-10-27T09:17:06+00:00` | نویسنده: alirezarahimy-code
- والدها: `de7094a5f04b3ef8c5d5c0d5a471814a5ebaaa1b`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/a5b828fac01a75e4d03aa433a6a977aa06bf7b65)

> Record manual ticket issuance as food delivery
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `1dbc006a005693acde0754c01371d17282c89c46`

- تاریخ commit: `2026-10-27T09:45:14+00:00` | نویسنده: alirezarahimy-code
- والدها: `a5b828fac01a75e4d03aa433a6a977aa06bf7b65`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/1dbc006a005693acde0754c01371d17282c89c46)

> Bundle profile form with ticket delivery correction
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `025dbbf9c8caf88c0d1f08c3c56d799a0113dcea`

- تاریخ commit: `2026-10-27T10:00:00+00:00` | نویسنده: alirezarahimy-code
- والدها: `1dbc006a005693acde0754c01371d17282c89c46`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/025dbbf9c8caf88c0d1f08c3c56d799a0113dcea)

> Reconcile manual attendance with delivery and absence records
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `8bfb5f61de7fb7b35d8a4422ca2ea1777124e230`

- تاریخ commit: `2026-10-27T10:18:53+00:00` | نویسنده: alirezarahimy-code
- والدها: `025dbbf9c8caf88c0d1f08c3c56d799a0113dcea`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8bfb5f61de7fb7b35d8a4422ca2ea1777124e230)

> Fix profile helpers and legacy inventory call from logs
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `3622a773dd9338b09c7ae7478ef4c96ee3e43518`

- تاریخ commit: `2026-10-27T10:41:44+00:00` | نویسنده: alirezarahimy-code
- والدها: `8bfb5f61de7fb7b35d8a4422ca2ea1777124e230`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/3622a773dd9338b09c7ae7478ef4c96ee3e43518)

> Reconcile legacy manual food tickets and absences
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `62dc7498c57ca7ea8323c65aa8b0b818c7b31675`

- تاریخ commit: `2026-10-27T11:02:45+00:00` | نویسنده: alirezarahimy-code
- والدها: `3622a773dd9338b09c7ae7478ef4c96ee3e43518`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/62dc7498c57ca7ea8323c65aa8b0b818c7b31675)

> Bundle all manual attendance reconciliation migrations
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `b90b2e9f3631e5a23904e74e456da20e9452f789`

- تاریخ commit: `2026-10-27T11:05:15+00:00` | نویسنده: alirezarahimy-code
- والدها: `62dc7498c57ca7ea8323c65aa8b0b818c7b31675`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b90b2e9f3631e5a23904e74e456da20e9452f789)

> Verify updater migration handling for 1.62.36
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `07e120a71af4e877287ca31413fe94e2113e130c`

- تاریخ commit: `2026-10-27T12:46:58+00:00` | نویسنده: alirezarahimy-code
- والدها: `b90b2e9f3631e5a23904e74e456da20e9452f789`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/07e120a71af4e877287ca31413fe94e2113e130c)

> Centralize food reports and reconcile daily counts
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `e289a7900e450b4564acaf562186293a71cc1cb7`

- تاریخ commit: `2026-10-27T13:12:22+00:00` | نویسنده: alirezarahimy-code
- والدها: `07e120a71af4e877287ca31413fe94e2113e130c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/e289a7900e450b4564acaf562186293a71cc1cb7)

> Package incremental food reports update 1.62.37
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `86c3309245a43fb5a4970b719848d3d053765dcc`

- تاریخ commit: `2026-10-27T13:47:41+00:00` | نویسنده: alirezarahimy-code
- والدها: `e289a7900e450b4564acaf562186293a71cc1cb7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/86c3309245a43fb5a4970b719848d3d053765dcc)

> Restore report placement inside food manager
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `44a48f8721203591931fe7623da6ad461773dab0`

- تاریخ commit: `2026-10-27T13:55:15+00:00` | نویسنده: alirezarahimy-code
- والدها: `86c3309245a43fb5a4970b719848d3d053765dcc`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/44a48f8721203591931fe7623da6ad461773dab0)

> Revert "Restore report placement inside food manager"
> 
> This reverts commit 86c3309245a43fb5a4970b719848d3d053765dcc.

### `db56c8879953e726ba2cbe77d836a5cb35447169`

- تاریخ commit: `2026-10-27T13:55:15+00:00` | نویسنده: alirezarahimy-code
- والدها: `44a48f8721203591931fe7623da6ad461773dab0`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/db56c8879953e726ba2cbe77d836a5cb35447169)

> Revert "Package incremental food reports update 1.62.37"
> 
> This reverts commit e289a7900e450b4564acaf562186293a71cc1cb7.

### `b919d751bb71d1b16da323a856a999f7cfb04772`

- تاریخ commit: `2026-10-27T13:55:15+00:00` | نویسنده: alirezarahimy-code
- والدها: `db56c8879953e726ba2cbe77d836a5cb35447169`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b919d751bb71d1b16da323a856a999f7cfb04772)

> Revert "Centralize food reports and reconcile daily counts"
> 
> This reverts commit 07e120a71af4e877287ca31413fe94e2113e130c.

### `6fed4fb0b28e6b2a6fa0134f63f9487e91e18728`

- تاریخ commit: `2026-10-27T14:07:13+00:00` | نویسنده: alirezarahimy-code
- والدها: `b919d751bb71d1b16da323a856a999f7cfb04772`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/6fed4fb0b28e6b2a6fa0134f63f9487e91e18728)

> Add complete same-version 1.62.36 file restore package
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `c6009f6ab82f9328b7889f3ef04003ddd90779f5`

- تاریخ commit: `2026-10-27T15:06:13+00:00` | نویسنده: alirezarahimy-code
- والدها: `6fed4fb0b28e6b2a6fa0134f63f9487e91e18728`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c6009f6ab82f9328b7889f3ef04003ddd90779f5)

> Rebuild complete 1.62.36 restore package from release archives
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `4249b16a5de80910463f6edef92e7c60a707c0f7`

- تاریخ commit: `2026-10-27T15:49:50+00:00` | نویسنده: alirezarahimy-code
- والدها: `c6009f6ab82f9328b7889f3ef04003ddd90779f5`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4249b16a5de80910463f6edef92e7c60a707c0f7)

> Publish reconstructed 1.62.36 source snapshot and report
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `2963c4b6ad6e571f364203543ed5c351f9bf3368`

- تاریخ commit: `2026-10-25T07:22:09+00:00` | نویسنده: alirezarahimy-code
- والدها: `57beb3b443c64d74aa27a2cfc4e5243f730ba40f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/2963c4b6ad6e571f364203543ed5c351f9bf3368)

> Commit accumulated ticket and food system updates
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `7866ebc6304ae3e3ee82fe2331e0a0e8a085feaa`

- تاریخ commit: `2026-10-25T07:22:53+00:00` | نویسنده: alirezarahimy-code
- والدها: `2963c4b6ad6e571f364203543ed5c351f9bf3368`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7866ebc6304ae3e3ee82fe2331e0a0e8a085feaa)

> Preserve generated benchmark and smoke reports
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `03207153d17f4db6c01860144e9e2cff5ea79326`

- تاریخ commit: `2026-10-19T21:02:23+00:00` | نویسنده: alirezarahimy-code
- والدها: `57beb3b443c64d74aa27a2cfc4e5243f730ba40f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/03207153d17f4db6c01860144e9e2cff5ea79326)

> Document current system and complete group ticket typography
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `aadaa1502723f29aa2cfe50046cfb08588420daf`

- تاریخ commit: `2026-10-19T21:09:41+00:00` | نویسنده: alirezarahimy-code
- والدها: `03207153d17f4db6c01860144e9e2cff5ea79326`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/aadaa1502723f29aa2cfe50046cfb08588420daf)

> Make group ticket preview update live
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `a7412c548ec70f61bcd564f6a70bf046d04853e6`

- تاریخ commit: `2026-10-20T21:11:08+00:00` | نویسنده: alirezarahimy-code
- والدها: `aadaa1502723f29aa2cfe50046cfb08588420daf`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/a7412c548ec70f61bcd564f6a70bf046d04853e6)

> انتشار بستهٔ R7 کنترل تردد و صفحهٔ دانلود جاری
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `ea54634dc332e17b4dd56ce55f1810c3b4ba528b`

- تاریخ commit: `2026-10-20T21:15:49+00:00` | نویسنده: alirezarahimy-code
- والدها: `a7412c548ec70f61bcd564f6a70bf046d04853e6`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ea54634dc332e17b4dd56ce55f1810c3b4ba528b)

> ثبت قاعدهٔ دائمی انتشار بسته‌های دانلود
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `319acf62879b0841c05c38ce0f348014044470e3`

- تاریخ commit: `2026-10-20T21:34:21+00:00` | نویسنده: alirezarahimy-code
- والدها: `ea54634dc332e17b4dd56ce55f1810c3b4ba528b`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/319acf62879b0841c05c38ce0f348014044470e3)

> ثبت شماره تلفن ۱۱ رقمی و یادآور روزانهٔ بازرسی R8
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `981b337f90ad9ffec98b2a4c259ee3fdf2205743`

- تاریخ commit: `2026-10-20T21:39:30+00:00` | نویسنده: alirezarahimy-code
- والدها: `319acf62879b0841c05c38ce0f348014044470e3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/981b337f90ad9ffec98b2a4c259ee3fdf2205743)

> افزودن کنترل ادمین برای یادآور بازرسی R9
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d5cc5d1285458b65dc4177ede938a23c55f8c84f`

- تاریخ commit: `2026-10-21T06:27:36+00:00` | نویسنده: alirezarahimy-code
- والدها: `981b337f90ad9ffec98b2a4c259ee3fdf2205743`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d5cc5d1285458b65dc4177ede938a23c55f8c84f)

> افزودن عملیات گروهی برنامه غذایی R10
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d7ee59a3f57af56706ae989e5d0ff09f19870596`

- تاریخ commit: `2026-10-21T06:51:27+00:00` | نویسنده: alirezarahimy-code
- والدها: `d5cc5d1285458b65dc4177ede938a23c55f8c84f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d7ee59a3f57af56706ae989e5d0ff09f19870596)

> رفع محدودیت جابه‌جایی اجزای طراحی R11
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `bcfb7ae88cde3b713f7b39c9a0bbe03a6fd9dc50`

- تاریخ commit: `2026-10-21T07:53:18+00:00` | نویسنده: alirezarahimy-code
- والدها: `d7ee59a3f57af56706ae989e5d0ff09f19870596`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/bcfb7ae88cde3b713f7b39c9a0bbe03a6fd9dc50)

> Add audited manual food ticket registration
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `4c948e9c1cf9e5a0879bfd9088a16a4ed666b954`

- تاریخ commit: `2026-10-21T08:07:06+00:00` | نویسنده: alirezarahimy-code
- والدها: `bcfb7ae88cde3b713f7b39c9a0bbe03a6fd9dc50`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4c948e9c1cf9e5a0879bfd9088a16a4ed666b954)

> Support temporary canteen group transfers
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `acad329cc30a79195ac099448dc10d5bfae2d24a`

- تاریخ commit: `2026-10-21T10:41:59+00:00` | نویسنده: alirezarahimy-code
- والدها: `4c948e9c1cf9e5a0879bfd9088a16a4ed666b954`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/acad329cc30a79195ac099448dc10d5bfae2d24a)

> Hide personal identifiers in org chart (R14)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `c0d4cdb1f2249fefc51a1bfddfbabac8135d9a29`

- تاریخ commit: `2026-10-21T10:51:48+00:00` | نویسنده: alirezarahimy-code
- والدها: `acad329cc30a79195ac099448dc10d5bfae2d24a`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c0d4cdb1f2249fefc51a1bfddfbabac8135d9a29)

> Move manual food attendance into settings (R15)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `bdc3515702463752ea793492a954abfae1738a72`

- تاریخ commit: `2026-10-21T13:08:22+00:00` | نویسنده: alirezarahimy-code
- والدها: `c0d4cdb1f2249fefc51a1bfddfbabac8135d9a29`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/bdc3515702463752ea793492a954abfae1738a72)

> Add food order reports and local account linking R16
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `826e83ff1cf493be0715538177a506272f58188b`

- تاریخ commit: `2026-10-21T13:45:29+00:00` | نویسنده: alirezarahimy-code
- والدها: `bdc3515702463752ea793492a954abfae1738a72`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/826e83ff1cf493be0715538177a506272f58188b)

> Prioritize manual employee codes and restrict attendance to orderers R17
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `ef5a5649279dcdb4b1d99e1f5d36a609f9e8d6a7`

- تاریخ commit: `2026-10-21T14:41:45+00:00` | نویسنده: alirezarahimy-code
- والدها: `826e83ff1cf493be0715538177a506272f58188b`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ef5a5649279dcdb4b1d99e1f5d36a609f9e8d6a7)

> Add primary-admin employee-code link action R18
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `e010c6bcb5ea2eefd726ce54199c2a8c2a2c26cf`

- تاریخ commit: `2026-10-21T15:13:42+00:00` | نویسنده: alirezarahimy-code
- والدها: `ef5a5649279dcdb4b1d99e1f5d36a609f9e8d6a7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/e010c6bcb5ea2eefd726ce54199c2a8c2a2c26cf)

> Release R19 with selectable code linking and org role repair
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `ceff6d607a36c49636679560b14957e34133b8d0`

- تاریخ commit: `2026-10-21T15:29:52+00:00` | نویسنده: alirezarahimy-code
- والدها: `e010c6bcb5ea2eefd726ce54199c2a8c2a2c26cf`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ceff6d607a36c49636679560b14957e34133b8d0)

> Fix local guard account code lookup in R20
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `f9f9d5b36e15b20cd3b4f831778623ab399393e7`

- تاریخ commit: `2026-10-21T15:53:28+00:00` | نویسنده: alirezarahimy-code
- والدها: `ceff6d607a36c49636679560b14957e34133b8d0`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f9f9d5b36e15b20cd3b4f831778623ab399393e7)

> Make organization role repair panel visible in R21
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `58dc1f464a89477e4f98425cedfcc697886e42c6`

- تاریخ commit: `2026-10-21T16:22:54+00:00` | نویسنده: alirezarahimy-code
- والدها: `f9f9d5b36e15b20cd3b4f831778623ab399393e7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/58dc1f464a89477e4f98425cedfcc697886e42c6)

> Improve food reports and group orders in R22
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `7ed4099f480210518a1f2db4ff21aa3369406d76`

- تاریخ commit: `2026-10-21T16:54:19+00:00` | نویسنده: alirezarahimy-code
- والدها: `58dc1f464a89477e4f98425cedfcc697886e42c6`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7ed4099f480210518a1f2db4ff21aa3369406d76)

> Fix report layout and printer orientation (R23)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d8d6a3cc82a8402fb1ec3f201bbdd2991ccf0cd1`

- تاریخ commit: `2026-10-21T16:56:36+00:00` | نویسنده: alirezarahimy-code
- والدها: `7ed4099f480210518a1f2db4ff21aa3369406d76`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d8d6a3cc82a8402fb1ec3f201bbdd2991ccf0cd1)

> Balance employee report row blocks in R23 package
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `39d0c9db8915af9b6a355e8579d053c2ee13f59d`

- تاریخ commit: `2026-10-21T17:10:40+00:00` | نویسنده: alirezarahimy-code
- والدها: `d8d6a3cc82a8402fb1ec3f201bbdd2991ccf0cd1`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/39d0c9db8915af9b6a355e8579d053c2ee13f59d)

> Restore horizontal food stats and report dividers in R24
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `611933b24259cde84528b2dd04d14a276cbcfc64`

- تاریخ commit: `2026-10-21T17:13:31+00:00` | نویسنده: alirezarahimy-code
- والدها: `39d0c9db8915af9b6a355e8579d053c2ee13f59d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/611933b24259cde84528b2dd04d14a276cbcfc64)

> Keep food statistics in horizontal day cards for R25
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `10c30236a43da1e0b0de89b6a668cea3a4802a68`

- تاریخ commit: `2026-10-21T21:33:21+00:00` | نویسنده: alirezarahimy-code
- والدها: `611933b24259cde84528b2dd04d14a276cbcfc64`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/10c30236a43da1e0b0de89b6a668cea3a4802a68)

> Release R26 local inventory extraction
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `0681c1bd92ec2b4b0d781003e3ce4c03477fdfa9`

- تاریخ commit: `2026-10-21T21:45:39+00:00` | نویسنده: alirezarahimy-code
- والدها: `10c30236a43da1e0b0de89b6a668cea3a4802a68`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/0681c1bd92ec2b4b0d781003e3ce4c03477fdfa9)

> Release R27 local asset helper hotfix
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `761cb99897a370df4cdc9b2fbc4df005c7c74f61`

- تاریخ commit: `2026-10-21T22:45:41+00:00` | نویسنده: alirezarahimy-code
- والدها: `0681c1bd92ec2b4b0d781003e3ce4c03477fdfa9`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/761cb99897a370df4cdc9b2fbc4df005c7c74f61)

> Complete network inventory scan workflow in R28
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `36440e847b564d57994190a5da7a6a0ee37c6206`

- تاریخ commit: `2026-10-21T22:57:36+00:00` | نویسنده: alirezarahimy-code
- والدها: `761cb99897a370df4cdc9b2fbc4df005c7c74f61`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/36440e847b564d57994190a5da7a6a0ee37c6206)

> Expand full-domain Windows inventory in R29
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d9dbc9c1fc49dace8eaca6fd5d42fd02e3806f44`

- تاریخ commit: `2026-10-21T23:05:56+00:00` | نویسنده: alirezarahimy-code
- والدها: `36440e847b564d57994190a5da7a6a0ee37c6206`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d9dbc9c1fc49dace8eaca6fd5d42fd02e3806f44)

> Refresh auto-filled asset profiles after domain scans
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `0a7ae5228f0a12497cebab10acc456b4b585c9aa`

- تاریخ commit: `2026-10-22T15:44:11+00:00` | نویسنده: alirezarahimy-code
- والدها: `d9dbc9c1fc49dace8eaca6fd5d42fd02e3806f44`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/0a7ae5228f0a12497cebab10acc456b4b585c9aa)

> Prepare R31 inventory provenance and performance update
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `c6197bdef8dff8f1b573fee2fd318dab2bc3d8b3`

- تاریخ commit: `2026-10-22T15:46:43+00:00` | نویسنده: alirezarahimy-code
- والدها: `0a7ae5228f0a12497cebab10acc456b4b585c9aa`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c6197bdef8dff8f1b573fee2fd318dab2bc3d8b3)

> Finalize R31 download links and package checksum
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `3f2b07a2861d6260e19b2a1d06c92b41e94b9d28`

- تاریخ commit: `2026-10-22T16:03:33+00:00` | نویسنده: alirezarahimy-code
- والدها: `c6197bdef8dff8f1b573fee2fd318dab2bc3d8b3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/3f2b07a2861d6260e19b2a1d06c92b41e94b9d28)

> Add isolated 250-user full-stack benchmark workflow
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `7d11b35ebe8b69d3319b110bf8b28f51549604a5`

- تاریخ commit: `2026-10-22T16:07:07+00:00` | نویسنده: alirezarahimy-code
- والدها: `3f2b07a2861d6260e19b2a1d06c92b41e94b9d28`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7d11b35ebe8b69d3319b110bf8b28f51549604a5)

> Expose load benchmark results in workflow summary
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `bbb07fe1c544e01613986a59c2984a99c8dbc4b7`

- تاریخ commit: `2026-10-22T16:10:33+00:00` | نویسنده: alirezarahimy-code
- والدها: `7d11b35ebe8b69d3319b110bf8b28f51549604a5`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/bbb07fe1c544e01613986a59c2984a99c8dbc4b7)

> Persist isolated benchmark results for analysis
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `df1913182069efc517e0a7826472fd2d289eb2bf`

- تاریخ commit: `2026-10-22T16:13:16+00:00` | نویسنده: github-actions[bot]
- والدها: `bbb07fe1c544e01613986a59c2984a99c8dbc4b7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/df1913182069efc517e0a7826472fd2d289eb2bf)

> Record isolated 250-VU benchmark run 39811718011

### `58d5bc12db635a9d4e26b4e596228bd7be8d7ab0`

- تاریخ commit: `2026-10-22T16:15:04+00:00` | نویسنده: alirezarahimy-code
- والدها: `df1913182069efc517e0a7826472fd2d289eb2bf`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/58d5bc12db635a9d4e26b4e596228bd7be8d7ab0)

> Fix benchmark route validation and Jalali month data
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `b53f1afccc17e18626032bad0846e4b25e3925e2`

- تاریخ commit: `2026-10-22T16:17:35+00:00` | نویسنده: github-actions[bot]
- والدها: `58d5bc12db635a9d4e26b4e596228bd7be8d7ab0`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b53f1afccc17e18626032bad0846e4b25e3925e2)

> Record isolated 250-VU benchmark run 39812398833

### `78ffb58d3715bd9f89780f6aee10dabc54e8e752`

- تاریخ commit: `2026-10-22T16:26:35+00:00` | نویسنده: alirezarahimy-code
- والدها: `b53f1afccc17e18626032bad0846e4b25e3925e2`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/78ffb58d3715bd9f89780f6aee10dabc54e8e752)

> Capture API and SQL timing details in load benchmark
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `18480ccf5a49ad2d5fe249967d043f5d4e14a69f`

- تاریخ commit: `2026-10-22T16:29:17+00:00` | نویسنده: github-actions[bot]
- والدها: `78ffb58d3715bd9f89780f6aee10dabc54e8e752`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/18480ccf5a49ad2d5fe249967d043f5d4e14a69f)

> Record isolated 250-VU benchmark run 39814115001

### `168d12054e828b33fb17339b926dad341623c997`

- تاریخ commit: `2026-10-22T21:13:39+00:00` | نویسنده: alirezarahimy-code
- والدها: `18480ccf5a49ad2d5fe249967d043f5d4e14a69f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/168d12054e828b33fb17339b926dad341623c997)

> Add isolated concurrent ticket and CD-DVD operations stress test
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `cee7f2833fe4089d49603b9ab8377c0ea0ff1817`

- تاریخ commit: `2026-10-22T21:14:32+00:00` | نویسنده: github-actions[bot]
- والدها: `168d12054e828b33fb17339b926dad341623c997`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/cee7f2833fe4089d49603b9ab8377c0ea0ff1817)

> Record isolated ticket and CD-DVD stress run 39855522080

### `2206f34c57e4bfe19143776cadc36e359a64bab7`

- تاریخ commit: `2026-10-22T21:16:10+00:00` | نویسنده: alirezarahimy-code
- والدها: `cee7f2833fe4089d49603b9ab8377c0ea0ff1817`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/2206f34c57e4bfe19143776cadc36e359a64bab7)

> Correct synthetic ticket resolution and race metrics
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `02149fd2152a306259bdd989fd86c0489ab7e07b`

- تاریخ commit: `2026-10-22T21:17:22+00:00` | نویسنده: github-actions[bot]
- والدها: `2206f34c57e4bfe19143776cadc36e359a64bab7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/02149fd2152a306259bdd989fd86c0489ab7e07b)

> Record isolated ticket and CD-DVD stress run 39855871831

### `495599273830adad05c326df6a252a0804d9a46d`

- تاریخ commit: `2026-10-22T21:18:48+00:00` | نویسنده: alirezarahimy-code
- والدها: `02149fd2152a306259bdd989fd86c0489ab7e07b`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/495599273830adad05c326df6a252a0804d9a46d)

> Serialize concurrent CD-DVD record creation
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `ccbaa05c0b8fd120460ec9f344c60b9b4b5fadf4`

- تاریخ commit: `2026-10-22T21:20:01+00:00` | نویسنده: github-actions[bot]
- والدها: `495599273830adad05c326df6a252a0804d9a46d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ccbaa05c0b8fd120460ec9f344c60b9b4b5fadf4)

> Record isolated ticket and CD-DVD stress run 39856232709

### `de27d0c020c29fad1397c378499ecfb031262b4f`

- تاریخ commit: `2026-10-22T21:22:26+00:00` | نویسنده: alirezarahimy-code
- والدها: `ccbaa05c0b8fd120460ec9f344c60b9b4b5fadf4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/de27d0c020c29fad1397c378499ecfb031262b4f)

> Make concurrent ticket numbers transaction-safe
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d5434c4a11867327654ee83456dcf9e2b5bd8b2f`

- تاریخ commit: `2026-10-22T21:23:35+00:00` | نویسنده: github-actions[bot]
- والدها: `de27d0c020c29fad1397c378499ecfb031262b4f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d5434c4a11867327654ee83456dcf9e2b5bd8b2f)

> Record isolated ticket and CD-DVD stress run 39856733517

### `2a1ae4e3528c9b0e0e2cbef5fc871f36903749de`

- تاریخ commit: `2026-10-22T21:26:25+00:00` | نویسنده: alirezarahimy-code
- والدها: `d5434c4a11867327654ee83456dcf9e2b5bd8b2f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/2a1ae4e3528c9b0e0e2cbef5fc871f36903749de)

> Reserve ticket numbers before transactional ticket writes
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `5378e276b32ec56c122b72507ced25530662c321`

- تاریخ commit: `2026-10-22T21:27:32+00:00` | نویسنده: github-actions[bot]
- والدها: `2a1ae4e3528c9b0e0e2cbef5fc871f36903749de`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5378e276b32ec56c122b72507ced25530662c321)

> Record isolated ticket and CD-DVD stress run 39857279765

### `96c434cd63120da5b767d73b8f95f3840bcdb674`

- تاریخ commit: `2026-10-22T21:30:41+00:00` | نویسنده: alirezarahimy-code
- والدها: `5378e276b32ec56c122b72507ced25530662c321`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/96c434cd63120da5b767d73b8f95f3840bcdb674)

> Document synthetic role coverage in operations benchmark
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `54db0ded3744393f8626eb6155d211e174614162`

- تاریخ commit: `2026-10-22T21:31:48+00:00` | نویسنده: github-actions[bot]
- والدها: `96c434cd63120da5b767d73b8f95f3840bcdb674`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/54db0ded3744393f8626eb6155d211e174614162)

> Record isolated ticket and CD-DVD stress run 39857853260

### `f890fa90a39c414d98efdea316a34632b27fb1c7`

- تاریخ commit: `2026-10-22T21:38:28+00:00` | نویسنده: alirezarahimy-code
- والدها: `54db0ded3744393f8626eb6155d211e174614162`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f890fa90a39c414d98efdea316a34632b27fb1c7)

> Add Persian management report for operations stress test
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `8e8ac368d5ed7a614f012f79f55d45f45c27db5c`

- تاریخ commit: `2026-10-22T21:57:22+00:00` | نویسنده: alirezarahimy-code
- والدها: `f890fa90a39c414d98efdea316a34632b27fb1c7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8e8ac368d5ed7a614f012f79f55d45f45c27db5c)

> Add isolated full-system route and test coverage
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `b282181b79e6d523ed73b5838579bf5ff175ffa7`

- تاریخ commit: `2026-10-22T21:58:44+00:00` | نویسنده: github-actions[bot]
- والدها: `8e8ac368d5ed7a614f012f79f55d45f45c27db5c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b282181b79e6d523ed73b5838579bf5ff175ffa7)

> Record isolated full-system test run 39861311768

### `1e08039258925beb3606904b53f8119290f6bc58`

- تاریخ commit: `2026-10-22T22:01:10+00:00` | نویسنده: alirezarahimy-code
- والدها: `b282181b79e6d523ed73b5838579bf5ff175ffa7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/1e08039258925beb3606904b53f8119290f6bc58)

> Fix synthetic full-system smoke route fixtures
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `82c7274c88e600243b05fc0bf40b52c0b8c69b40`

- تاریخ commit: `2026-10-22T22:02:36+00:00` | نویسنده: github-actions[bot]
- والدها: `1e08039258925beb3606904b53f8119290f6bc58`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/82c7274c88e600243b05fc0bf40b52c0b8c69b40)

> Record isolated full-system test run 39861793171

### `5a4910f32f138ddafbde9cb4e626e4d7adf328d8`

- تاریخ commit: `2026-10-22T22:04:39+00:00` | نویسنده: alirezarahimy-code
- والدها: `82c7274c88e600243b05fc0bf40b52c0b8c69b40`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5a4910f32f138ddafbde9cb4e626e4d7adf328d8)

> Use valid food menu API smoke query parameters
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `03fe741287681fa393a8308fd2f24166f2696f91`

- تاریخ commit: `2026-10-22T22:06:05+00:00` | نویسنده: github-actions[bot]
- والدها: `5a4910f32f138ddafbde9cb4e626e4d7adf328d8`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/03fe741287681fa393a8308fd2f24166f2696f91)

> Record isolated full-system test run 39862237034

### `5e9b89ad016f2ff303cf1e21950b03b8fde29ed9`

- تاریخ commit: `2026-10-22T22:08:17+00:00` | نویسنده: alirezarahimy-code
- والدها: `03fe741287681fa393a8308fd2f24166f2696f91`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5e9b89ad016f2ff303cf1e21950b03b8fde29ed9)

> Add Persian full-system test management report
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `a71d6931f9def7e2a117cc29514776464197e36a`

- تاریخ commit: `2026-10-22T22:34:09+00:00` | نویسنده: alirezarahimy-code
- والدها: `5e9b89ad016f2ff303cf1e21950b03b8fde29ed9`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/a71d6931f9def7e2a117cc29514776464197e36a)

> Align stale tests and release docs with current system
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `8e3fd024b58e4c25d7c31aa8649a146b789200e8`

- تاریخ commit: `2026-10-22T22:35:52+00:00` | نویسنده: github-actions[bot]
- والدها: `a71d6931f9def7e2a117cc29514776464197e36a`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8e3fd024b58e4c25d7c31aa8649a146b789200e8)

> Record isolated full-system test run 39865916579

### `4a659b757abcbe56e45089aec30861d1e8ee7e2d`

- تاریخ commit: `2026-10-22T22:37:26+00:00` | نویسنده: alirezarahimy-code
- والدها: `8e3fd024b58e4c25d7c31aa8649a146b789200e8`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4a659b757abcbe56e45089aec30861d1e8ee7e2d)

> Expose isolated suite outcomes in CI annotations
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `e1072a36e9e4027e2a34c46890486cbd3e739cc8`

- تاریخ commit: `2026-10-22T22:39:05+00:00` | نویسنده: github-actions[bot]
- والدها: `4a659b757abcbe56e45089aec30861d1e8ee7e2d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/e1072a36e9e4027e2a34c46890486cbd3e739cc8)

> Record isolated full-system test run 39866299747

### `999b187b0fe0d966d81a568f0ed98337fb10beb3`

- تاریخ commit: `2026-10-22T22:40:12+00:00` | نویسنده: alirezarahimy-code
- والدها: `e1072a36e9e4027e2a34c46890486cbd3e739cc8`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/999b187b0fe0d966d81a568f0ed98337fb10beb3)

> Include PHP and DB failures in CI annotations
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `4946c34d95ab13beb437cf74509ab45ef4e8b242`

- تاریخ commit: `2026-10-22T22:41:37+00:00` | نویسنده: github-actions[bot]
- والدها: `999b187b0fe0d966d81a568f0ed98337fb10beb3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4946c34d95ab13beb437cf74509ab45ef4e8b242)

> Record isolated full-system test run 39866622692

### `b621ca01d7b86fc03ac7bffae8b16a8ef5cb80ee`

- تاریخ commit: `2026-10-22T22:43:20+00:00` | نویسنده: alirezarahimy-code
- والدها: `4946c34d95ab13beb437cf74509ab45ef4e8b242`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b621ca01d7b86fc03ac7bffae8b16a8ef5cb80ee)

> Fix stale smoke assertions and expose failing checks
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `de665682792b510472508457bb3bd9a270a41d54`

- تاریخ commit: `2026-10-22T22:44:52+00:00` | نویسنده: github-actions[bot]
- والدها: `b621ca01d7b86fc03ac7bffae8b16a8ef5cb80ee`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/de665682792b510472508457bb3bd9a270a41d54)

> Record isolated full-system test run 39866986761

### `c679ca13c97eb50d60c6ecd71060c41d6eb50f0a`

- تاریخ commit: `2026-10-22T22:47:17+00:00` | نویسنده: alirezarahimy-code
- والدها: `de665682792b510472508457bb3bd9a270a41d54`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c679ca13c97eb50d60c6ecd71060c41d6eb50f0a)

> Record post-fix CI validation and remaining failures
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `fe7a86c538e36477449c0687ca8f5394660f52ed`

- تاریخ commit: `2026-10-22T23:20:50+00:00` | نویسنده: alirezarahimy-code
- والدها: `c679ca13c97eb50d60c6ecd71060c41d6eb50f0a`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/fe7a86c538e36477449c0687ca8f5394660f52ed)

> Align smoke checks with current contracts
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `4e09b4151ab88c8df0248d9614d58c6572699ca8`

- تاریخ commit: `2026-10-22T23:22:23+00:00` | نویسنده: github-actions[bot]
- والدها: `fe7a86c538e36477449c0687ca8f5394660f52ed`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4e09b4151ab88c8df0248d9614d58c6572699ca8)

> Record isolated full-system test run 39871171396

### `8277d7024c46c1a8cd8e9db0fdc8b8b4038828f8`

- تاریخ commit: `2026-10-22T23:44:15+00:00` | نویسنده: alirezarahimy-code
- والدها: `4e09b4151ab88c8df0248d9614d58c6572699ca8`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8277d7024c46c1a8cd8e9db0fdc8b8b4038828f8)

> Expand offline coverage and align fixtures
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `993762ade8d2137ff98a3592cd6c9516e5ea686d`

- تاریخ commit: `2026-10-22T23:45:58+00:00` | نویسنده: github-actions[bot]
- والدها: `8277d7024c46c1a8cd8e9db0fdc8b8b4038828f8`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/993762ade8d2137ff98a3592cd6c9516e5ea686d)

> Record isolated full-system test run 39873754503

### `080833c4ca10e45a1fefc123603b09423893e26c`

- تاریخ commit: `2026-10-22T23:51:30+00:00` | نویسنده: alirezarahimy-code
- والدها: `993762ade8d2137ff98a3592cd6c9516e5ea686d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/080833c4ca10e45a1fefc123603b09423893e26c)

> Run org role sync against disposable CI DB
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `42ea95d583bfe728453a044682181daf03131dd2`

- تاریخ commit: `2026-10-22T23:52:50+00:00` | نویسنده: github-actions[bot]
- والدها: `080833c4ca10e45a1fefc123603b09423893e26c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/42ea95d583bfe728453a044682181daf03131dd2)

> Record isolated full-system test run 39874499699

### `2b6de3e488b8d8a01ebade61cd466f07ed2fac11`

- تاریخ commit: `2026-10-23T00:18:58+00:00` | نویسنده: alirezarahimy-code
- والدها: `42ea95d583bfe728453a044682181daf03131dd2`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/2b6de3e488b8d8a01ebade61cd466f07ed2fac11)

> Stress all authenticated routes with synthetic load
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `169eaab58bf6586f74036b41c161afd9665019c3`

- تاریخ commit: `2026-10-23T00:20:20+00:00` | نویسنده: github-actions[bot]
- والدها: `2b6de3e488b8d8a01ebade61cd466f07ed2fac11`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/169eaab58bf6586f74036b41c161afd9665019c3)

> Record isolated full-system test run 39877392749

### `37b409941445afaead772e9b83dcdcf12e4b04b0`

- تاریخ commit: `2026-10-23T00:22:28+00:00` | نویسنده: alirezarahimy-code
- والدها: `169eaab58bf6586f74036b41c161afd9665019c3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/37b409941445afaead772e9b83dcdcf12e4b04b0)

> Persist route resilience failure diagnostics
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `25671f95fdce863b6c406be3ec4a2faffe24746f`

- تاریخ commit: `2026-10-23T00:24:07+00:00` | نویسنده: github-actions[bot]
- والدها: `37b409941445afaead772e9b83dcdcf12e4b04b0`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/25671f95fdce863b6c406be3ec4a2faffe24746f)

> Record isolated full-system test run 39877741881

### `1d28190ccb9770bf6b3d8499cad3212fb15371ed`

- تاریخ commit: `2026-10-23T00:24:52+00:00` | نویسنده: alirezarahimy-code
- والدها: `25671f95fdce863b6c406be3ec4a2faffe24746f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/1d28190ccb9770bf6b3d8499cad3212fb15371ed)

> Fix route resilience HTTP client bootstrap
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `5510aef4ec7307fe27ad80ba8a5fcad2c96d84d2`

- تاریخ commit: `2026-10-23T00:27:21+00:00` | نویسنده: github-actions[bot]
- والدها: `1d28190ccb9770bf6b3d8499cad3212fb15371ed`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5510aef4ec7307fe27ad80ba8a5fcad2c96d84d2)

> Record isolated full-system test run 39877976647

### `607a483944c0391720d9a250fbd2040b5d4a7b1f`

- تاریخ commit: `2026-10-23T01:17:31+00:00` | نویسنده: alirezarahimy-code
- والدها: `5510aef4ec7307fe27ad80ba8a5fcad2c96d84d2`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/607a483944c0391720d9a250fbd2040b5d4a7b1f)

> Expand isolated full-system role and module coverage
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `66f8c3bc5e5f38797ca5fdb5ad2b83ba338b4684`

- تاریخ commit: `2026-10-23T01:19:11+00:00` | نویسنده: github-actions[bot]
- والدها: `607a483944c0391720d9a250fbd2040b5d4a7b1f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/66f8c3bc5e5f38797ca5fdb5ad2b83ba338b4684)

> Record isolated full-system test run 39883162388

### `fb862acb63a2f88c0f098d55010817069163f145`

- تاریخ commit: `2026-10-23T01:21:15+00:00` | نویسنده: alirezarahimy-code
- والدها: `66f8c3bc5e5f38797ca5fdb5ad2b83ba338b4684`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/fb862acb63a2f88c0f098d55010817069163f145)

> Fix inventory agent insert and refine synthetic role checks
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d0a1ee70cc68d04bc012d2224de09adef7b22fae`

- تاریخ commit: `2026-10-23T01:22:54+00:00` | نویسنده: github-actions[bot]
- والدها: `fb862acb63a2f88c0f098d55010817069163f145`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d0a1ee70cc68d04bc012d2224de09adef7b22fae)

> Record isolated full-system test run 39883510568

### `4b5d36d33eb5376aa008c0ae6751047f291bfafa`

- تاریخ commit: `2026-10-23T01:23:50+00:00` | نویسنده: alirezarahimy-code
- والدها: `d0a1ee70cc68d04bc012d2224de09adef7b22fae`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4b5d36d33eb5376aa008c0ae6751047f291bfafa)

> Use seeded traffic destination in lifecycle smoke
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `8703b992a15e69b4c578b65c21b843740cddf179`

- تاریخ commit: `2026-10-23T01:26:19+00:00` | نویسنده: github-actions[bot]
- والدها: `4b5d36d33eb5376aa008c0ae6751047f291bfafa`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8703b992a15e69b4c578b65c21b843740cddf179)

> Record isolated full-system test run 39883757690

### `85c872ad7edd9ef869a924c6320b170a892e4855`

- تاریخ commit: `2026-10-23T01:55:15+00:00` | نویسنده: alirezarahimy-code
- والدها: `8703b992a15e69b4c578b65c21b843740cddf179`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/85c872ad7edd9ef869a924c6320b170a892e4855)

> Expand synthetic route, export, and cron coverage
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `9f1d00b1938b053e72f4d0369c42ec6204c94d30`

- تاریخ commit: `2026-10-23T01:57:33+00:00` | نویسنده: github-actions[bot]
- والدها: `85c872ad7edd9ef869a924c6320b170a892e4855`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/9f1d00b1938b053e72f4d0369c42ec6204c94d30)

> Record isolated full-system test run 39886705167

### `310f5682d43aa132ee98b44869e10a99bbe9dfe8`

- تاریخ commit: `2026-10-23T01:58:24+00:00` | نویسنده: alirezarahimy-code
- والدها: `9f1d00b1938b053e72f4d0369c42ec6204c94d30`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/310f5682d43aa132ee98b44869e10a99bbe9dfe8)

> Correct synthetic export and absence API fixtures
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `1fe3b539b43ff1dc695e7f39699288565e1de181`

- تاریخ commit: `2026-10-23T02:00:11+00:00` | نویسنده: github-actions[bot]
- والدها: `310f5682d43aa132ee98b44869e10a99bbe9dfe8`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/1fe3b539b43ff1dc695e7f39699288565e1de181)

> Record isolated full-system test run 39886988048

### `8c12029df7cba8e7742c5f6a01de57e04bfa3f5b`

- تاریخ commit: `2026-10-23T02:00:50+00:00` | نویسنده: alirezarahimy-code
- والدها: `1fe3b539b43ff1dc695e7f39699288565e1de181`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8c12029df7cba8e7742c5f6a01de57e04bfa3f5b)

> Persist synthetic cron integration results
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `254dadf3fde95b1ee77368a18146541baa1e97d1`

- تاریخ commit: `2026-10-23T02:02:55+00:00` | نویسنده: github-actions[bot]
- والدها: `8c12029df7cba8e7742c5f6a01de57e04bfa3f5b`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/254dadf3fde95b1ee77368a18146541baa1e97d1)

> Record isolated full-system test run 39887223768

### `4256a82da26a935aaa29c03249bf9c1fe6c1b57c`

- تاریخ commit: `2026-10-23T02:03:48+00:00` | نویسنده: alirezarahimy-code
- والدها: `254dadf3fde95b1ee77368a18146541baa1e97d1`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4256a82da26a935aaa29c03249bf9c1fe6c1b57c)

> Document current synthetic test coverage and gaps
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `f0d179961820e4dad3d01bc237a984864f5150f4`

- تاریخ commit: `2026-10-23T02:24:54+00:00` | نویسنده: alirezarahimy-code
- والدها: `4256a82da26a935aaa29c03249bf9c1fe6c1b57c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f0d179961820e4dad3d01bc237a984864f5150f4)

> Add isolated food management stress suite
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `1344564af9a7d59b3082035502e774d965d0dd04`

- تاریخ commit: `2026-10-23T02:26:08+00:00` | نویسنده: alirezarahimy-code
- والدها: `f0d179961820e4dad3d01bc237a984864f5150f4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/1344564af9a7d59b3082035502e774d965d0dd04)

> Capture food-only Python test diagnostics
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `9a6eba0d44eed54b3902045de956b02cba7e2dd6`

- تاریخ commit: `2026-10-23T02:27:52+00:00` | نویسنده: alirezarahimy-code
- والدها: `1344564af9a7d59b3082035502e774d965d0dd04`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/9a6eba0d44eed54b3902045de956b02cba7e2dd6)

> Expose isolated food stress diagnostics
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `c9f1bc8ab6898c24cb2f63130f01807760d94061`

- تاریخ commit: `2026-10-23T02:29:15+00:00` | نویسنده: alirezarahimy-code
- والدها: `9a6eba0d44eed54b3902045de956b02cba7e2dd6`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c9f1bc8ab6898c24cb2f63130f01807760d94061)

> Use fixed-width synthetic employee codes
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `b2a13eba7760b0546efc94a37f9db26a29fffc96`

- تاریخ commit: `2026-10-23T02:30:18+00:00` | نویسنده: alirezarahimy-code
- والدها: `c9f1bc8ab6898c24cb2f63130f01807760d94061`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b2a13eba7760b0546efc94a37f9db26a29fffc96)

> Report verified food stress results
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `766051e74b6f0b157f9905421d9239c3a6d09dd7`

- تاریخ commit: `2026-10-23T02:31:33+00:00` | نویسنده: alirezarahimy-code
- والدها: `b2a13eba7760b0546efc94a37f9db26a29fffc96`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/766051e74b6f0b157f9905421d9239c3a6d09dd7)

> Simplify isolated food stress workflow status
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `3a6f289ba6e872aa1526ce09c2c95d3a7ad52064`

- تاریخ commit: `2026-10-23T02:32:50+00:00` | نویسنده: alirezarahimy-code
- والدها: `766051e74b6f0b157f9905421d9239c3a6d09dd7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/3a6f289ba6e872aa1526ce09c2c95d3a7ad52064)

> Keep food stress diagnostics available on static failures
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `18ec114e66a3fb0e915364ffce3d3db869bd0fbd`

- تاریخ commit: `2026-10-23T02:34:10+00:00` | نویسنده: alirezarahimy-code
- والدها: `3a6f289ba6e872aa1526ce09c2c95d3a7ad52064`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/18ec114e66a3fb0e915364ffce3d3db869bd0fbd)

> Seed food migration manifest for static tests
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `20697440ac0ec967f2c50f8798917b9d8901e347`

- تاریخ commit: `2026-10-24T12:25:44+00:00` | نویسنده: alirezarahimy-code
- والدها: `18ec114e66a3fb0e915364ffce3d3db869bd0fbd`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/20697440ac0ec967f2c50f8798917b9d8901e347)

> Ship food management UI asset hotfix R32
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `b5d3b6fd84f0b88da7623db252979a5ef3f79cb4`

- تاریخ commit: `2026-10-24T12:28:15+00:00` | نویسنده: github-actions[bot]
- والدها: `20697440ac0ec967f2c50f8798917b9d8901e347`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b5d3b6fd84f0b88da7623db252979a5ef3f79cb4)

> Record isolated full-system test run 40093888263

### `c74fc0ca841f2e4220815d15c6fc202b56cbad5c`

- تاریخ commit: `2026-10-24T12:44:39+00:00` | نویسنده: alirezarahimy-code
- والدها: `b5d3b6fd84f0b88da7623db252979a5ef3f79cb4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c74fc0ca841f2e4220815d15c6fc202b56cbad5c)

> Ship food runtime dependency recovery R33 [skip ci]
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `88737806676426a984d801c657b73a2cd104a321`

- تاریخ commit: `2026-10-24T12:57:06+00:00` | نویسنده: alirezarahimy-code
- والدها: `c74fc0ca841f2e4220815d15c6fc202b56cbad5c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/88737806676426a984d801c657b73a2cd104a321)

> [skip ci] Fix R33 updater migration archive layout
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `12d65dfa14ad222027d0c1c578f062bacd74bf43`

- تاریخ commit: `2026-10-25T07:24:55+00:00` | نویسنده: alirezarahimy-code
- والدها: `7866ebc6304ae3e3ee82fe2331e0a0e8a085feaa 88737806676426a984d801c657b73a2cd104a321`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/12d65dfa14ad222027d0c1c578f062bacd74bf43)

> Merge remote-tracking branch 'origin/arena/8edbdf95-ticket' into arena/8edbdf95-ticket
> 
> # Conflicts:
> #	VERSION
> #	assets/food-menu.css
> #	assets/food-menu.js
> #	assets/food-order.js
> #	benchmark-results/benchmark-operations.json
> #	benchmark-results/benchmark-operations.md
> #	benchmark-results/full-system-route-smoke.json
> #	benchmark-results/full-system-route-smoke.md
> #	benchmark-results/operations-container-stats.txt
> #	benchmark-results/system-resilience.json
> #	benchmark-results/system-resilience.md
> #	food-order.php
> #	food-ticket-web/assets/food-menu.css
> #	food-ticket-web/assets/food-menu.js
> #	food-ticket-web/index.html
> #	food-ticket.php
> #	index.php
> #	tests/test_food_bulk_operations_static.py
> #	tests/test_food_orders_static.py
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `924e82978e17a09d2b17500e1798840296c2d5d3`

- تاریخ commit: `2026-10-25T07:27:36+00:00` | نویسنده: github-actions[bot]
- والدها: `12d65dfa14ad222027d0c1c578f062bacd74bf43`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/924e82978e17a09d2b17500e1798840296c2d5d3)

> Record isolated 250-VU benchmark run 40172681254

### `4272b2cdcdf7eb5ae23e402409723d642271a217`

- تاریخ commit: `2026-10-25T07:31:39+00:00` | نویسنده: alirezarahimy-code
- والدها: `5e5e6a23f15de67bead2bc2eb15477e7983cbf27 924e82978e17a09d2b17500e1798840296c2d5d3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4272b2cdcdf7eb5ae23e402409723d642271a217)

> ادامهٔ کار از شاخهٔ arena/8edbdf95-ticket: پذیرش کامل وضعیت نسخهٔ ۱.۶۲.۳
> 
> کل درخت شاخهٔ arena/8edbdf95-ticket (کامیت 924e829، نسخهٔ ۱.۶۲.۳ با انتشارهای R7 تا R42 و 162.x) جایگزین وضعیت ۱.۶۰.۴ شد تا این نشست از همان‌جا ادامه یابد.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `9211c01491de0f0cec4fec3cf9f02e5ca5cb8c47`

- تاریخ commit: `2026-10-25T08:11:42+00:00` | نویسنده: alirezarahimy-code
- والدها: `4272b2cdcdf7eb5ae23e402409723d642271a217`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/9211c01491de0f0cec4fec3cf9f02e5ca5cb8c47)

> Release 1.62.4: food page dark mode coverage, traffic edit-history filter, centered food panel pages, manual ticket select-all
> 
> - Dark mode now covers all food planning page surfaces (forms, person lists,
>   bank, bulk tools, recipient banner) in both assets/theme-mode.css and
>   food-ticket-web/assets/food-ticket-global-theme-r42.css (cache-busted v3/v45).
> - Traffic-control edit history tab lists only genuinely edited records
>   (action='updated'); exit/delete still audited but hidden from the list.
> - Food panel .page is centered with margin-inline auto; dashboard host stays
>   full-bleed (food-ticket-theme.css v10).
> - Manual ticket registration opens with no pre-checked names and gains a
>   select-all checkbox in the «انتخاب» header (food-manual-attendance.js v3,
>   PANEL_VERSION 1.37.25).
> - Persian help guide gained sections for traffic edit history and manual
>   ticket select-all (chatbot indexes the guide automatically).
> - New static test tests/test_release_1_62_4_fixes_static.py; stale version
>   pins updated in existing static tests; docs/REPORT-FA, SYSTEM-DETAILS,
>   HANDOFF and downloads/index.html point to the new unique package
>   ticket-update-1.62.4-food-theme-traffic-history-20261025.zip (+ sha256).
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `cf851a7d1c698212929b9f5b1147374f137e3a60`

- تاریخ commit: `2026-10-25T08:29:06+00:00` | نویسنده: alirezarahimy-code
- والدها: `9211c01491de0f0cec4fec3cf9f02e5ca5cb8c47`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/cf851a7d1c698212929b9f5b1147374f137e3a60)

> Release 1.62.5: ship assets/app.js so the host day/night toggle works outside the food panel
> 
> Root cause: the global theme toggle handler lives in assets/app.js, but no
> 1.62.x package ever shipped it, so updated installs rendered the button with
> no behaviour (food panel worked because its toggle is inline).
> 
> - Package now ships assets/app.js (cumulative with the full 1.62.4 fix set).
> - app.js bootstrap runs even when loaded after DOMContentLoaded.
> - Theme toggle click handled via delegation so late-rendered buttons work.
> - Cache-bust app.js?v=3.4.26&theme-mode=2 in index.php, governance.php,
>   ola.php and app/Views/layout/footer.php.
> - jsdom verification (normal load, late load, dynamic button) + new static
>   test tests/test_release_1_62_5_theme_toggle_static.py; docs and the
>   download page point to ticket-update-1.62.5-host-theme-toggle-appjs-20261025.zip.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `26412a78d2bab94ccc8ea6030b69faf74e1cdd92`

- تاریخ commit: `2026-10-25T09:50:55+00:00` | نویسنده: alirezarahimy-code
- والدها: `cf851a7d1c698212929b9f5b1147374f137e3a60`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/26412a78d2bab94ccc8ea6030b69faf74e1cdd92)

> Release 1.62.6: keep org-chart managers out of automatic ticket assignment
> 
> User report: ticket assignees constantly became unrelated users (org deputies/
> office heads) in both IT and support. Root cause: the historical org-manager
> promotion bug left org-chart managers holding primary_admin/support_manager
> roles, and auto-assignment rotated new tickets among them by least open load.
> 
> Per user decision the assignment design stays (IT -> primary admin, support ->
> support manager, least load, same-unit fallback); fixes:
> - ticket_org_manager_rank_sql(): org-chart managers rank last in both the
>   owner and fallback queries of choose_handling_agent; safely skipped on
>   installs without org chart tables.
> - Organization page: new diagnostics card showing the current auto-assign
>   target per domain plus all candidates (role, unit, open load, org-manager
>   badge), and a second repair form demoting org managers wrongly holding
>   support_manager back to manager (repair_org_manager_support_roles).
> - Persian help gained 'who is the ticket assignee' section (chatbot indexes
>   it); wording rule «درخواست/تیکت» kept in index.php user texts.
> - New static test tests/test_release_1_62_6_auto_assign_static.py; cumulative
>   package ticket-update-1.62.6-ticket-auto-assign-org-guard-20261025.zip
>   (+sha256); downloads page and docs updated.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d7e004b6be75834fadc82b19f7f72789a7a2a866`

- تاریخ commit: `2026-10-25T10:10:57+00:00` | نویسنده: alirezarahimy-code
- والدها: `26412a78d2bab94ccc8ea6030b69faf74e1cdd92`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d7e004b6be75834fadc82b19f7f72789a7a2a866)

> Release 1.62.7: add all-events toggle to traffic edit-history (exit/delete rows were filtered, never deleted)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `21ec671c8aefbdee6d06d18f66622a40897ad439`

- تاریخ commit: `2026-10-25T10:47:24+00:00` | نویسنده: alirezarahimy-code
- والدها: `d7e004b6be75834fadc82b19f7f72789a7a2a866`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/21ec671c8aefbdee6d06d18f66622a40897ad439)

> Release 1.62.8: traffic form auto-fill by name/phone + 1-3 approvers per meeting destination
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `3fe616b920fbac48401b06fbb336cb4be46a57fb`

- تاریخ commit: `2026-10-25T10:49:46+00:00` | نویسنده: alirezarahimy-code
- والدها: `21ec671c8aefbdee6d06d18f66622a40897ad439`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/3fe616b920fbac48401b06fbb336cb4be46a57fb)

> Add filled sample Excel for traffic history import (requested by user)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `1880fe7ba0c9f62e15d827edcf37155d4e66b7e4`

- تاریخ commit: `2026-10-25T11:04:37+00:00` | نویسنده: alirezarahimy-code
- والدها: `3fe616b920fbac48401b06fbb336cb4be46a57fb`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/1880fe7ba0c9f62e15d827edcf37155d4e66b7e4)

> Fix 1.62.8 package: ship migration 009 at ZIP root where system-update.php executes it (fix1)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `21d107283daac75239ed9762b907fff962d88eeb`

- تاریخ commit: `2026-10-25T11:17:50+00:00` | نویسنده: alirezarahimy-code
- والدها: `1880fe7ba0c9f62e15d827edcf37155d4e66b7e4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/21d107283daac75239ed9762b907fff962d88eeb)

> 1.62.8 fix2: move bulk-operations preview button next to clear-selection (food menu); package with root migration
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `a8673461767cdf323dc111b65c7b96764ebe2673`

- تاریخ commit: `2026-10-25T11:57:25+00:00` | نویسنده: alirezarahimy-code
- والدها: `21d107283daac75239ed9762b907fff962d88eeb`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/a8673461767cdf323dc111b65c7b96764ebe2673)

> 1.62.9: manual food-ticket for past days (Jalali date picker) + manual guest ticket pinned first + one-line destination approver forms
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `7c6f4ff9f78919fb1b59613c8cd3263c10200e2a`

- تاریخ commit: `2026-10-25T12:18:48+00:00` | نویسنده: alirezarahimy-code
- والدها: `a8673461767cdf323dc111b65c7b96764ebe2673`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7c6f4ff9f78919fb1b59613c8cd3263c10200e2a)

> release 1.62.10: org-chart liaison units beside CEO (no hierarchy connectors)
> 
> - org_units.chart_side column (migration 010, idempotent) + schema/install/ensure-schema
> - save_org_node/assign/remove manager support unit_type 'liaison' (one active per side, no role promotion)
> - organization.js v7: absolute-positioned liaison band right/left of CEO, inline add/edit, menu, stats
> - style.css v3.4.45: liaison placement slightly below CEO, zero connector lines
> - help guide section + keywords; cumulative ZIP + checksum; downloads page shows only 1.62.10
> - tests: new test_release_1_62_10_org_liaison_static; re-pinned 1.62.9/traffic/triangle pins
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `f0d7697083ab3d54096f35a89c32505ac56c57e5`

- تاریخ commit: `2026-10-25T18:04:11+00:00` | نویسنده: alirezarahimy-code
- والدها: `7c6f4ff9f78919fb1b59613c8cd3263c10200e2a`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f0d7697083ab3d54096f35a89c32505ac56c57e5)

> docs: record pre-refactor safety snapshot tag + restore instructions in HANDOFF
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `82f107621a022f613ec614a661fb6ccfe41277b2`

- تاریخ commit: `2026-10-25T18:16:13+00:00` | نویسنده: alirezarahimy-code
- والدها: `f0d7697083ab3d54096f35a89c32505ac56c57e5`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/82f107621a022f613ec614a661fb6ccfe41277b2)

> release 1.62.11: code cleanup phase 1 — navigation banners + architecture/audit docs (zero behavior change)
> 
> - index.php: 3 region banners + 19 action-group banners (comments only, machine-verified no code diff)
> - bootstrap.php: function-family TOC header comment
> - docs/ARCHITECTURE-FA.md: full architecture map; docs/CODE-AUDIT-FA.md: dead-code audit with proof + phase 2/3 roadmap
> - packaging fix: 1.62.11 ZIP is fully cumulative 1.62.4..1.62.11 (1.62.10 missed some 1.62.4-6 files like assets/app.js)
> - tests: new 1_62_11 static test; re-pinned 1_62_9/1_62_10/traffic/triangle pins
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `ee1addc4aac851d58d82c8ef87e625d42273b710`

- تاریخ commit: `2026-10-25T18:26:35+00:00` | نویسنده: alirezarahimy-code
- والدها: `82f107621a022f613ec614a661fb6ccfe41277b2`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ee1addc4aac851d58d82c8ef87e625d42273b710)

> release 1.62.12: refactor phase 2 cut 1 — org actions extracted to actions/org-actions.php (byte-identical move, zero behavior change)
> 
> - 13 org-chart POST actions moved verbatim from index.php to actions/org-actions.php, loaded via require at the exact same point (same variable scope)
> - pre-move hazard scan: no return/function/goto/break/continue/__DIR__/?> in block; braces balanced 102/102
> - machine proof: substituting file body for the require line reconstructs previous index.php byte-for-byte (verified in repo AND inside shipped ZIP; made a permanent test)
> - direct-access guard (404 without APP_ROOT); updater creates new dir recursively (verified)
> - cumulative package 1.62.4..1.62.12; re-pinned org_chart_experts/1_62_6/1_62_9/1_62_10/1_62_11/traffic/triangle tests; new 1_62_12 static test
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `4a6a41dcab51d51ec5c9a560ab2d5d0b26a25927`

- تاریخ commit: `2026-10-25T18:36:56+00:00` | نویسنده: alirezarahimy-code
- والدها: `ee1addc4aac851d58d82c8ef87e625d42273b710`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4a6a41dcab51d51ec5c9a560ab2d5d0b26a25927)

> release 1.62.13: refactor phase 2 cuts 2+3 — ticket & asset/domain-scan actions extracted (byte-identical, zero behavior change)
> 
> - cut 2: 7 ticket-lifecycle actions -> actions/ticket-actions.php (no include traps, braces 130/130)
> - cut 3: 10 asset/domain-scan actions -> actions/asset-actions.php (only anonymous closure + loop continue, both include-safe; braces 64/64)
> - Persian per-action descriptions in each file header (user request); Persian banners at require points
> - machine byte-proof: substituting both bodies for the two require lines reconstructs previous index.php exactly (verified in repo AND inside ZIP; permanent tests added incl. execution-order check)
> - index.php: 6210 -> 4712 lines since phase start; direct-access guards; cumulative package 1.62.4..1.62.13
> - snapshot refreshed: tag snapshot-1.62.12-20261025 (ee1addc) pushed & verified; older snapshot kept
> - re-pinned inventory_truth(4x)/ticket_cancellation/ticket_auto_assignment(2x) + release-pin chain; new 1_62_13 static test
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `df58ba45267907fb716c3033c7cc42a60a3ac64d`

- تاریخ commit: `2026-10-25T18:44:08+00:00` | نویسنده: alirezarahimy-code
- والدها: `4a6a41dcab51d51ec5c9a560ab2d5d0b26a25927`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/df58ba45267907fb716c3033c7cc42a60a3ac64d)

> 1.62.13 fix1: بستهٔ تجمعی کامل 1.62.0..1.62.13 (۴۶ فایل) — رفع جاافتادن فایل‌های داشبورد غذا (r42) و assetهای ارجاع‌شدهٔ پنل + تست دائمی ارجاع‌سنجی
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `9c23e88573101ac2f5b4e50e95e30733f20f0499`

- تاریخ commit: `2026-10-25T18:57:35+00:00` | نویسنده: alirezarahimy-code
- والدها: `df58ba45267907fb716c3033c7cc42a60a3ac64d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/9c23e88573101ac2f5b4e50e95e30733f20f0499)

> release 1.62.14: refactor phase 2 cut 4 — service/category/knowledge actions extracted to actions/service-actions.php (byte-identical, zero behavior change; cumulative 1.62.0..1.62.14 package)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `6b04745afb997f0911cf7580183a0e1c94b13597`

- تاریخ commit: `2026-10-25T19:08:49+00:00` | نویسنده: alirezarahimy-code
- والدها: `9c23e88573101ac2f5b4e50e95e30733f20f0499`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/6b04745afb997f0911cf7580183a0e1c94b13597)

> release 1.62.15: service page modals fixed — stay above sticky header (z-index specificity) + close on backdrop click/Esc like other system popups; cache-bust style.css v3.4.46 / app.js v3.4.27 across all shells
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `730133f35ec710ae2e2ec5f9e9114a16d68ef48e`

- تاریخ commit: `2026-10-25T19:15:32+00:00` | نویسنده: alirezarahimy-code
- والدها: `6b04745afb997f0911cf7580183a0e1c94b13597`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/730133f35ec710ae2e2ec5f9e9114a16d68ef48e)

> release 1.62.16: refactor phase 2 cut 5 — role/permission actions to actions/role-actions.php and log/LDAP/settings actions to actions/settings-actions.php (byte-identical, zero behavior change; cumulative 1.62.0..1.62.16)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d9c37be1a9fbe56fa8dda065ee701b3de4c58e18`

- تاریخ commit: `2026-10-25T19:29:16+00:00` | نویسنده: alirezarahimy-code
- والدها: `730133f35ec710ae2e2ec5f9e9114a16d68ef48e`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d9c37be1a9fbe56fa8dda065ee701b3de4c58e18)

> release 1.62.17: refactor phase 2 cut 6 — supervisor/rating actions to actions/supervisor-actions.php and calendar/holiday actions to actions/holiday-actions.php (byte-identical, zero behavior change; cumulative 1.62.0..1.62.17)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `14e6dc2c95eb0c54c7d8629372dcb2a2ba8ffe18`

- تاریخ commit: `2026-10-25T19:40:55+00:00` | نویسنده: alirezarahimy-code
- والدها: `d9c37be1a9fbe56fa8dda065ee701b3de4c58e18`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/14e6dc2c95eb0c54c7d8629372dcb2a2ba8ffe18)

> release 1.62.18: refactor phase 2 cut 7 — login/profile/theme, food-brand, CD/DVD and notification actions extracted to four thematic files (byte-identical, zero behavior change; backup/system_update intentionally untouched; cumulative 1.62.0..1.62.18)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `201669599a59423241a7deca9900aa50adec2518`

- تاریخ commit: `2026-10-25T19:50:27+00:00` | نویسنده: alirezarahimy-code
- والدها: `14e6dc2c95eb0c54c7d8629372dcb2a2ba8ffe18`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/201669599a59423241a7deca9900aa50adec2518)

> release 1.62.19: refactor phase 2 cut 8 — asset-relation/bulk actions to actions/bulk-actions.php and base-structure/user actions to actions/user-structure-actions.php (byte-identical, zero behavior change; only backup/system_update group intentionally remains in index; cumulative 1.62.0..1.62.19)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `cd5d1d2d395bb903e838bfb769ea828fae190ec4`

- تاریخ commit: `2026-10-25T20:12:21+00:00` | نویسنده: alirezarahimy-code
- والدها: `201669599a59423241a7deca9900aa50adec2518`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/cd5d1d2d395bb903e838bfb769ea828fae190ec4)

> release 1.62.20: refactor phase 2 main part complete — cut 9 (backup/system_update actions -> actions/backup-actions.php, byte-identical), bootstrap.php split into 8 lib/ modules (order preserved, machine-proved), 8 dead functions removed (zero-reference proved); zero behavior change; cumulative 1.62.0..1.62.20 (66 files)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `994498dc48df40daf35cf3cf311457bd9cc2e7fe`

- تاریخ commit: `2026-10-26T13:34:42+00:00` | نویسنده: alirezarahimy-code
- والدها: `cd5d1d2d395bb903e838bfb769ea828fae190ec4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/994498dc48df40daf35cf3cf311457bd9cc2e7fe)

> release 1.62.21: new 'delivered' tab in food menu module — exact clone of day-orders report shape (selectable field checkboxes, list/menu-grouped views with per-menu separators, Jalali date + today, XLSX export, print) but content = printed+delivered personnel tickets + printed guest tickets; new API route food-menu/delivered (food.order_close), menu_no mapped by matching food name to that day's menu; panel 1.37.26, food-menu.js v=32; cumulative 1.62.0..1.62.21 (66 files)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `9ad0740f6657f762ba46d6fa752873722fd575f5`

- تاریخ commit: `2026-10-26T13:44:30+00:00` | نویسنده: alirezarahimy-code
- والدها: `994498dc48df40daf35cf3cf311457bd9cc2e7fe`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/9ad0740f6657f762ba46d6fa752873722fd575f5)

> Revert "release 1.62.21: new 'delivered' tab in food menu module — exact clone of day-orders report shape (selectable field checkboxes, list/menu-grouped views with per-menu separators, Jalali date + today, XLSX export, print) but content = printed+delivered personnel tickets + printed guest tickets; new API route food-menu/delivered (food.order_close), menu_no mapped by matching food name to that day's menu; panel 1.37.26, food-menu.js v=32; cumulative 1.62.0..1.62.21 (66 files)"
> 
> This reverts commit 994498dc48df40daf35cf3cf311457bd9cc2e7fe.

### `7db20383d32cbcbb2631a668574eb5c511101e3a`

- تاریخ commit: `2026-10-26T17:15:12+03:30` | نویسنده: alirezarahimy-code
- والدها: `bf02da69e42ceee15e208560de44f95e972a0305 9ad0740f6657f762ba46d6fa752873722fd575f5`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7db20383d32cbcbb2631a668574eb5c511101e3a)

> Merge pull request #2 from alirezarahimy-code/arena/dba0774e-ticket
> 
> Sync main to v1.62.20 (current stable)

### `21eb9b3ae12db79f853127e6fb5f8f8d13688bf9`

- تاریخ commit: `2026-10-27T15:52:27+00:00` | نویسنده: alirezarahimy-code
- والدها: `4249b16a5de80910463f6edef92e7c60a707c0f7 7db20383d32cbcbb2631a668574eb5c511101e3a`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/21eb9b3ae12db79f853127e6fb5f8f8d13688bf9)

> Merge remote-tracking branch 'origin/main' into arena/d57ac025-ticket
> 
> # Conflicts:
> #	tests/test_role_permissions_ui_static.py
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `1e31356b322db2759ca09339ca6cd1f21c6d614c`

- تاریخ commit: `2026-10-27T15:54:18+00:00` | نویسنده: alirezarahimy-code
- والدها: `21eb9b3ae12db79f853127e6fb5f8f8d13688bf9`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/1e31356b322db2759ca09339ca6cd1f21c6d614c)

> Parallelize PHP syntax lint for release tree
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `150bcc9b465eee03944d54fa4c8220c0920bab0b`

- تاریخ commit: `2026-10-27T15:58:34+00:00` | نویسنده: alirezarahimy-code
- والدها: `1e31356b322db2759ca09339ca6cd1f21c6d614c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/150bcc9b465eee03944d54fa4c8220c0920bab0b)

> Use one-pass PHP syntax parsing in CI
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `51bc3fb361b8fa828a8383053885ca8dbecdcc6e`

- تاریخ commit: `2026-10-27T15:59:53+00:00` | نویسنده: alirezarahimy-code
- والدها: `150bcc9b465eee03944d54fa4c8220c0920bab0b`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/51bc3fb361b8fa828a8383053885ca8dbecdcc6e)

> Update security checks for modular PHP sources
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `e2afc13a9e185c7dfcc1e9757675a3180cd98fee`

- تاریخ commit: `2026-10-27T19:31:21+03:30` | نویسنده: alirezarahimy-code
- والدها: `7db20383d32cbcbb2631a668574eb5c511101e3a 51bc3fb361b8fa828a8383053885ca8dbecdcc6e`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/e2afc13a9e185c7dfcc1e9757675a3180cd98fee)

> Merge pull request #3 from alirezarahimy-code/arena/d57ac025-ticket
> 
> Release v1.62.36: reconciled source snapshot

### `37edf6ffe9d81c6d6e1ff2b69b9d6b901a0cb689`

- تاریخ commit: `2026-10-31T20:34:29+00:00` | نویسنده: alirezarahimy-code
- والدها: `e2afc13a9e185c7dfcc1e9757675a3180cd98fee`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/37edf6ffe9d81c6d6e1ff2b69b9d6b901a0cb689)

> Restore agent skill boot framework (31 skills + system map + boot tool)
> 
> بازنشانی چارچوب عملیاتی ایجنت (نسخهٔ ۱.۰.۰ مانیفست) از شاخهٔ
> alirezarahimy-code-patch-1 (PR #4) روی همان نسخهٔ محصول 1.62.36:
> 
> - AGENTS.md / CLAUDE.md / .cursorrules / .cursor/rules/arena-boot.mdc /
>   .github/copilot-instructions.md  (pointerهای بارگذاری خودکار)
> - docs/SKILL-MANIFEST-FA.md  (۳۱ مهارت: ۲۳ اصلی + ۸ مکمل)
> - docs/SYSTEM-MAP-FA.md + docs/system-map/*.tsv (۷ ایندکس، بازتولیدشده)
> - docs/SESSION-BOOT-FA.md  (راهنمای «به‌روز شو»)
> - tools/session-boot.py، tools/build_system_map.py، tools/find-in-system.py
> - ۴ تست نگهبان: skill_manifest / system_map / cache_pin / session_boot
> 
> بدون تغییر در کد محصول: هیچ فایل PHP/JS/CSS یا مهاجرت SQL عوض نشده است.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `0039c3191355a8c0f0f4aa84cec54d07021a39e3`

- تاریخ commit: `2026-10-27T17:06:52+00:00` | نویسنده: alirezarahimy-code
- والدها: `e2afc13a9e185c7dfcc1e9757675a3180cd98fee`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/0039c3191355a8c0f0f4aa84cec54d07021a39e3)

>  docs: add persistent system map, skill manifest and operational tooling
> 
> Install the coding-agent operating framework as real, versioned files:
> 
> - AGENTS.md: session entrypoint rules + pointer to the system map
> - docs/SKILL-MANIFEST-FA.md: 23 core + 8 complementary skills, each with
>   observable behaviour, checklist, workflow stage and success criteria
> - docs/SYSTEM-MAP-FA.md: up-to-date 0-to-100 navigation reference
> - docs/system-map/*.tsv: machine-generated indexes (832 files, 4338 symbols,
>   367 routes, 70 tables, 110 update packages, 36 feature areas)
> - tools/build_system_map.py: regenerates indexes with --check freshness gate
> - tools/find-in-system.py: fast Persian/English lookup -> exact file:line
> - tests/test_skill_manifest_static.py, test_system_map_static.py
> 
> Memory status is documented honestly: behavioural activation is
> session-only; the files are the persistent, reproducible memory.
> 
> Reconcile known drift (73 -> 74 static tests, 7 FAIL -> 0 FAIL):
> 
> - README.md: version 1.8-final -> 1.62.36, clarify upgrade-1.8-* is history
> - install-empty-db.sql: add the 4 tables that existed only in schema.sql
>   (chatbot_faq, chatbot_logs, system_incidents, food_guest_requests) using
>   CREATE TABLE IF NOT EXISTS only; no DROP/ALTER/DELETE, no data removal
> - repin 13 stale assertions across 10 test files: ?v= cache versions,
>   PANEL_VERSION 1.37.25 -> 1.37.27, refactored function signatures and the
>   rewritten printed-delivery menu query. Historical ZIP package pins are
>   intentionally left untouched.
> - add tests/test_cache_pin_consistency_static.py to prevent regression of
>   stale cache pins; verified it fails when a pin is deliberately broken.
> 
> Not touched (needs user decision): SecurityTest.php rebuild-final.sql table
> coverage, PHP/CI tests (PHP is not installed in this environment), app/ and
> routes/ unused MVC layer, food-ticket-env.php factory Access password.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `4e7d53269e80c806ec9d3ed6da6102339f191930`

- تاریخ commit: `2026-10-27T17:25:19+00:00` | نویسنده: alirezarahimy-code
- والدها: `0039c3191355a8c0f0f4aa84cec54d07021a39e3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4e7d53269e80c806ec9d3ed6da6102339f191930)

> feat: one-command session boot for persistent skill/context restore
> 
> Adds a "start button" so a new chat never has to re-learn the repo:
> 
> - tools/session-boot.py: in one run prints memory status, all 31 skills,
>   the 7-stage workflow, always-on rules, the user's standing rules from
>   HANDOFF-FA.md, system map stats, guard-test results and git state.
>   Auto-refreshes stale indexes, supports --no-refresh / --pull / --json /
>   --skip-tests (avoids recursion when called from its own guard test).
> - docs/SESSION-BOOT-FA.md: plain-language guide. The trigger phrases
>   "بهروز شو" / "بوت کن" map to running the boot command.
> - Auto-load pointer files for common agent harnesses: AGENTS.md (updated,
>   boot command in the first lines), CLAUDE.md (@AGENTS.md import),
>   .github/copilot-instructions.md, .cursor/rules/arena-boot.mdc,
>   .cursorrules.
> - tests/test_session_boot_static.py: verifies pointer files, core docs,
>   skill manifest completeness, boot output sections and --json output.
> 
> Honest limitation documented: the files are permanent in Git but the
> behavioural activation is still session-only. The boot command restores
> full context on demand; it does not modify any future system prompt.
> 
> Also updates SKILL-MANIFEST-FA.md install-status table, SYSTEM-MAP-FA.md
> section 0 with the boot command, and regenerates docs/system-map indexes
> (839 files, 4352 symbols). All 75 static tests pass.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `8bd0ae8ad3fb0e82f65ed5da21c02067e6d00e3f`

- تاریخ commit: `2026-10-27T18:38:25+00:00` | نویسنده: alirezarahimy-code
- والدها: `4e7d53269e80c806ec9d3ed6da6102339f191930`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8bd0ae8ad3fb0e82f65ed5da21c02067e6d00e3f)

> build: add downloadable full source package 1.62.36 with aggregate report
> 
> Full source ZIP (924 files, 12.76 MB) including the new aggregate report tab,
> ready to upload to the server. Includes SHA256 checksum.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `f4940aeefccff5ed4eee309671443e8439245389`

- تاریخ commit: `2026-10-27T18:38:54+00:00` | نویسنده: alirezarahimy-code
- والدها: `8bd0ae8ad3fb0e82f65ed5da21c02067e6d00e3f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f4940aeefccff5ed4eee309671443e8439245389)

> feat: add aggregate report tab to food reports
> 
> Adds a second tab «گزارش تجمیعی» next to «گزارشگیری» in the food panel
> reports page (food-ticket-web/index.html).
> 
> Server (food-ticket.php):
> - New read-only API routes aggregate-report and aggregate-report-export,
>   both restricted to food.reports permission.
> - Counts active food_orders, printed guest tickets, final printed staff
>   tickets and absentees for a date range; computes mismatch flag.
> - Returns separate name lists for printed people and absentees.
> - Real XLSX export honouring the user-selected columns.
> 
> UI:
> - Turns the existing report view into a two-tab layout; the classic
>   report is unchanged.
> - Five numeric cards (total orders / guest printed / final printed /
>   absentees / mismatch), date range picker, column selection persisted
>   in localStorage, XLSX export, print preview, and two detail tables.
> 
> Tests:
> - tests/js/food-aggregate-report.test.js (10 behavioural groups, jsdom-free).
> - tests/test_food_aggregate_report_static.py (53 static checks).
> - Panel version bumped 1.37.27 -> 1.37.28 in both host and panel, with
>   the three pinned static tests updated accordingly.
> - docs/aggregate-report-preview.html is a standalone visual preview.
> 
> All 76 static Python tests and the behavioural JS test pass.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `af93c8d59a87ecb6f47d4bbd84e2355e823e887c`

- تاریخ commit: `2026-10-27T18:47:17+00:00` | نویسنده: alirezarahimy-code
- والدها: `f4940aeefccff5ed4eee309671443e8439245389`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/af93c8d59a87ecb6f47d4bbd84e2355e823e887c)

> build: add small 1.62.36 update package for aggregate report tab
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `cda08547f679d1e1eeb7766684b24f2b06a35adc`

- تاریخ commit: `2026-10-27T18:54:16+00:00` | نویسنده: alirezarahimy-code
- والدها: `af93c8d59a87ecb6f47d4bbd84e2355e823e887c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/cda08547f679d1e1eeb7766684b24f2b06a35adc)

> fix: rebuild update package to match system-update.php structure
> 
> The previous full ZIP had a top-level ticket/ folder and the small ZIP had
> CHANGE-REPORT-FA.md at the root; both are rejected by
> system_update_read_package() which only allows manifest.json, migrations/*.sql
> and files/*. Rebuilt the small package with the correct layout, added VERSION
> and CHANGE-REPORT-FA.md under files/, removed the invalid full ZIP, and
> validated every entry against the exact regex used by system-update.php.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `86a15e68ab9e7507d260b5b5f17180ba1a13f9da`

- تاریخ commit: `2026-10-27T19:42:31+00:00` | نویسنده: alirezarahimy-code
- والدها: `cda08547f679d1e1eeb7766684b24f2b06a35adc`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/86a15e68ab9e7507d260b5b5f17180ba1a13f9da)

> fix(aggregate): one-line counters/fields, 2-3 column person cards, surname sort
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `15da70a51ae72f777ce15d3d17639ff7de289fee`

- تاریخ commit: `2026-10-27T19:53:29+00:00` | نویسنده: alirezarahimy-code
- والدها: `86a15e68ab9e7507d260b5b5f17180ba1a13f9da`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/15da70a51ae72f777ce15d3d17639ff7de289fee)

> fix(aggregate): one-line counters/fields, 2-3 col cards, surname sort
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `39aa8d3e0524c3b361ed8172b75bce99058b84cb`

- تاریخ commit: `2026-10-27T19:57:55+00:00` | نویسنده: alirezarahimy-code
- والدها: `15da70a51ae72f777ce15d3d17639ff7de289fee`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/39aa8d3e0524c3b361ed8172b75bce99058b84cb)

> build: fresh update package (unique name to bypass CDN cache)
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `4ae559f19408f8a163a7fcfb16eace61baf56f6f`

- تاریخ commit: `2026-10-27T20:04:25+00:00` | نویسنده: alirezarahimy-code
- والدها: `39aa8d3e0524c3b361ed8172b75bce99058b84cb`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/4ae559f19408f8a163a7fcfb16eace61baf56f6f)

> fix(aggregate): block table layout matching printed-delivery report
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `7d637ed84d83baf526a4d69d0ab4e09864f807cd`

- تاریخ commit: `2026-10-27T20:11:11+00:00` | نویسنده: alirezarahimy-code
- والدها: `4ae559f19408f8a163a7fcfb16eace61baf56f6f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7d637ed84d83baf526a4d69d0ab4e09864f807cd)

> fix(aggregate): clickable column sorting like printed-delivery report
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `620d592332fe41e71f698ed2325687b1f73ea96d`

- تاریخ commit: `2026-10-27T20:18:58+00:00` | نویسنده: alirezarahimy-code
- والدها: `7d637ed84d83baf526a4d69d0ab4e09864f807cd`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/620d592332fe41e71f698ed2325687b1f73ea96d)

> fix(aggregate): exact printed-delivery table markup plus data-no-sort
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `6dc589cbc7e776a47a52d61a93ea25d376c1410f`

- تاریخ commit: `2026-10-27T20:48:50+00:00` | نویسنده: alirezarahimy-code
- والدها: `620d592332fe41e71f698ed2325687b1f73ea96d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/6dc589cbc7e776a47a52d61a93ea25d376c1410f)

> feat(reports): move delivery/food-stats/today-orders into new report tabs
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `486eda7bd2ee3390b47faf046b633227aeda1ed3`

- تاریخ commit: `2026-10-27T21:11:11+00:00` | نویسنده: alirezarahimy-code
- والدها: `6dc589cbc7e776a47a52d61a93ea25d376c1410f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/486eda7bd2ee3390b47faf046b633227aeda1ed3)

> fix(reports): row column wrap, delivery view buttons, manual ticket permission
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `2dee9bf623c6f60a36a6a06640038f712daf0483`

- تاریخ commit: `2026-10-27T21:33:33+00:00` | نویسنده: alirezarahimy-code
- والدها: `486eda7bd2ee3390b47faf046b633227aeda1ed3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/2dee9bf623c6f60a36a6a06640038f712daf0483)

> feat(delivery): single-date picker; add read-only asset duplicate diagnostic
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `43c74dd6d3580f09cee7ad280be2a665a6d052a4`

- تاریخ commit: `2026-10-27T21:59:08+00:00` | نویسنده: Arena Agent
- والدها: `2dee9bf623c6f60a36a6a06640038f712daf0483`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/43c74dd6d3580f09cee7ad280be2a665a6d052a4)

> release 1.62.37: equal-width report columns, today-orders print/export, asset identity dedupe
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `3686221f08dc362cec3b6483563d04a0045cba16`

- تاریخ commit: `2026-10-27T22:16:18+00:00` | نویسنده: Arena Agent
- والدها: `43c74dd6d3580f09cee7ad280be2a665a6d052a4`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/3686221f08dc362cec3b6483563d04a0045cba16)

> hotfix 1.62.38: remove lib/asset-identity.php dependency, fix 500 on update
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d137b36bba45bccf7eb40cf246c72aef4dc4043e`

- تاریخ commit: `2026-10-27T22:37:02+00:00` | نویسنده: Arena Agent
- والدها: `3686221f08dc362cec3b6483563d04a0045cba16`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d137b36bba45bccf7eb40cf246c72aef4dc4043e)

> hotfix 1.62.39: guard asset identity functions, remove duplicate asset-profile definition
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `e5d6fc4183abc9812f2aee79c9175ea47c3773fe`

- تاریخ commit: `2026-10-27T22:58:21+00:00` | نویسنده: Arena Agent
- والدها: `d137b36bba45bccf7eb40cf246c72aef4dc4043e`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/e5d6fc4183abc9812f2aee79c9175ea47c3773fe)

> hotfix 1.62.40: eliminate all duplicate function declarations, add guards, require_once everywhere
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `cc1cb7f216dfc664fbabe277652284d8902e7aea`

- تاریخ commit: `2026-10-27T23:04:53+00:00` | نویسنده: Arena Agent
- والدها: `e5d6fc4183abc9812f2aee79c9175ea47c3773fe`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/cc1cb7f216dfc664fbabe277652284d8902e7aea)

> add regression test: no duplicate PHP function declarations + reload guards
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `79b4b30ed5eb501c675224552c24cb8280710492`

- تاریخ commit: `2026-10-27T23:25:46+00:00` | نویسنده: Arena Agent
- والدها: `cc1cb7f216dfc664fbabe277652284d8902e7aea`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/79b4b30ed5eb501c675224552c24cb8280710492)

> release 1.62.41: auto file backup on update + fully automatic asset dedupe migration
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `c7b277a8c4b919c5d4521e1e057c147a56e261e7`

- تاریخ commit: `2026-10-28T07:03:37+00:00` | نویسنده: Arena Agent
- والدها: `79b4b30ed5eb501c675224552c24cb8280710492`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c7b277a8c4b919c5d4521e1e057c147a56e261e7)

> fix food calendar order counts for support managers
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `85f0335bfc51a756526c34f11dac322ac3878ac3`

- تاریخ commit: `2026-10-28T08:29:06+00:00` | نویسنده: alirezarahimy-code
- والدها: `c7b277a8c4b919c5d4521e1e057c147a56e261e7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/85f0335bfc51a756526c34f11dac322ac3878ac3)

> release group-ticket design and save repair 1.62.43

### `7581f6fc71de242cbb1bf22d8cd0634961abf707`

- تاریخ commit: `2026-10-28T09:58:37+00:00` | نویسنده: alirezarahimy-code
- والدها: `85f0335bfc51a756526c34f11dac322ac3878ac3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/7581f6fc71de242cbb1bf22d8cd0634961abf707)

> add manager actions for guest food requests 1.62.44

### `d957f4bf44bcc5cd2ba7c46a77789407ea5a9fee`

- تاریخ commit: `2026-10-28T09:59:25+00:00` | نویسنده: alirezarahimy-code
- والدها: `7581f6fc71de242cbb1bf22d8cd0634961abf707`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d957f4bf44bcc5cd2ba7c46a77789407ea5a9fee)

> make 1.62.44 update cumulative from 1.62.42

### `0a98e3efec3114d056a88c8bdc94d41fb9fbc0c2`

- تاریخ commit: `2026-10-28T10:33:36+00:00` | نویسنده: alirezarahimy-code
- والدها: `d957f4bf44bcc5cd2ba7c46a77789407ea5a9fee`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/0a98e3efec3114d056a88c8bdc94d41fb9fbc0c2)

> add guest request management tab to group food operations 1.62.45

### `51fbf0d45e643a61a717e69270bda90db9d4646c`

- تاریخ commit: `2026-10-28T10:57:27+00:00` | نویسنده: alirezarahimy-code
- والدها: `0a98e3efec3114d056a88c8bdc94d41fb9fbc0c2`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/51fbf0d45e643a61a717e69270bda90db9d4646c)

> fix aggregate food report reconciliation diagnostics
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `f13eb1008ad7e30d5dbe7af3fe61717797176121`

- تاریخ commit: `2026-10-28T11:18:20+00:00` | نویسنده: alirezarahimy-code
- والدها: `51fbf0d45e643a61a717e69270bda90db9d4646c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f13eb1008ad7e30d5dbe7af3fe61717797176121)

> allow deleting historical guest food requests
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `d539980a670f2f4ca46195fe9f578ded1e185cc8`

- تاریخ commit: `2026-10-29T17:45:21+00:00` | نویسنده: alirezarahimy-code
- والدها: `e2afc13a9e185c7dfcc1e9757675a3180cd98fee`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d539980a670f2f4ca46195fe9f578ded1e185cc8)

> Add 1.62.48 access policy update package
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `37fa5679b19aa4cfd166115e8281240526e3358a`

- تاریخ commit: `2026-10-29T17:52:56+00:00` | نویسنده: alirezarahimy-code
- والدها: `f13eb1008ad7e30d5dbe7af3fe61717797176121 d539980a670f2f4ca46195fe9f578ded1e185cc8`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/37fa5679b19aa4cfd166115e8281240526e3358a)

> Publish update package 1.62.48

### `f18bee9aa0b7d4f112567ea5a4b7abba537aa9ac`

- تاریخ commit: `2026-10-30T14:32:14+00:00` | نویسنده: alirezarahimy-code
- والدها: `37fa5679b19aa4cfd166115e8281240526e3358a`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/f18bee9aa0b7d4f112567ea5a4b7abba537aa9ac)

> Add organization tree update package 1.62.49

### `9a2e719285e91293399bb0e70d17a80baf23b871`

- تاریخ commit: `2026-10-30T16:57:08+00:00` | نویسنده: alirezarahimy-code
- والدها: `f18bee9aa0b7d4f112567ea5a4b7abba537aa9ac`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/9a2e719285e91293399bb0e70d17a80baf23b871)

> Publish organization chart hotfix package 1.62.50

### `783fc8cef1093e121f63b18fddfeffe8a2c1537a`

- تاریخ commit: `2026-10-30T18:42:45+00:00` | نویسنده: alirezarahimy-code
- والدها: `e2afc13a9e185c7dfcc1e9757675a3180cd98fee`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/783fc8cef1093e121f63b18fddfeffe8a2c1537a)

> Add 1.62.52 multi-company update package
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `b7e831ae3b6b81bbd39a1e191a34bae045357840`

- تاریخ commit: `2026-10-30T18:43:55+00:00` | نویسنده: alirezarahimy-code
- والدها: `9a2e719285e91293399bb0e70d17a80baf23b871 783fc8cef1093e121f63b18fddfeffe8a2c1537a`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/b7e831ae3b6b81bbd39a1e191a34bae045357840)

> Publish 1.62.52 multi-company update package

### `d81538c5af1186cffa379ef8b378bbebcad5a7c3`

- تاریخ commit: `2026-10-30T19:07:46+00:00` | نویسنده: alirezarahimy-code
- والدها: `b7e831ae3b6b81bbd39a1e191a34bae045357840`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/d81538c5af1186cffa379ef8b378bbebcad5a7c3)

> Add 1.62.53 organization chart fixes package
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `c07490f4a731270709a333f703a71f8060b1d387`

- تاریخ commit: `2026-10-30T19:32:08+00:00` | نویسنده: alirezarahimy-code
- والدها: `d81538c5af1186cffa379ef8b378bbebcad5a7c3`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/c07490f4a731270709a333f703a71f8060b1d387)

> Publish organization chart update package 1.62.54
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `aeb2c049cd1ba4e2ba495fea4556a5f8b8b721c7`

- تاریخ commit: `2026-10-30T19:53:50+00:00` | نویسنده: alirezarahimy-code
- والدها: `c07490f4a731270709a333f703a71f8060b1d387`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/aeb2c049cd1ba4e2ba495fea4556a5f8b8b721c7)

> Publish tree-derived company membership update 1.62.55
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `e1ec795d0712da6abdb729aa6ac4cdd7445b7e99`

- تاریخ commit: `2026-10-30T20:06:58+00:00` | نویسنده: alirezarahimy-code
- والدها: `aeb2c049cd1ba4e2ba495fea4556a5f8b8b721c7`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/e1ec795d0712da6abdb729aa6ac4cdd7445b7e99)

> Publish cumulative organization access update 1.62.56
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `56fe0e05cd9ff20d505b952bbf8ae9c874a97bbb`

- تاریخ commit: `2026-10-31T06:32:01+00:00` | نویسنده: alirezarahimy-code
- والدها: `e1ec795d0712da6abdb729aa6ac4cdd7445b7e99`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/56fe0e05cd9ff20d505b952bbf8ae9c874a97bbb)

> Publish 1.62.57 requested fixes package

### `5519711549f125a64ea6652b19bd972cca0a389d`

- تاریخ commit: `2026-10-31T07:15:02+00:00` | نویسنده: alirezarahimy-code
- والدها: `56fe0e05cd9ff20d505b952bbf8ae9c874a97bbb`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/5519711549f125a64ea6652b19bd972cca0a389d)

> Publish 1.62.58 food permission and groups package

### `ebd8c6b36b0202223a99e0a2477a5f9374ef3082`

- تاریخ commit: `2026-10-31T14:54:57+00:00` | نویسنده: alirezarahimy-code
- والدها: `5519711549f125a64ea6652b19bd972cca0a389d`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ebd8c6b36b0202223a99e0a2477a5f9374ef3082)

> Add 1.62.59 role update package

### `57f9e02b92b918d26afc9e84dbc7af7100c98a24`

- تاریخ commit: `2026-10-31T15:42:41+00:00` | نویسنده: alirezarahimy-code
- والدها: `ebd8c6b36b0202223a99e0a2477a5f9374ef3082`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/57f9e02b92b918d26afc9e84dbc7af7100c98a24)

> Publish cumulative 1.62.59 update package
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `a2678eae2ea48b5bc0bc235b9eca15db9198dfc2`

- تاریخ commit: `2026-10-31T15:59:20+00:00` | نویسنده: alirezarahimy-code
- والدها: `57f9e02b92b918d26afc9e84dbc7af7100c98a24`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/a2678eae2ea48b5bc0bc235b9eca15db9198dfc2)

> Publish visual-fix update and logging source archive
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `3e1544abaa6e3c75137c96f2f7e8787ad8cbec6c`

- تاریخ commit: `2026-10-31T16:25:31+00:00` | نویسنده: alirezarahimy-code
- والدها: `a2678eae2ea48b5bc0bc235b9eca15db9198dfc2`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/3e1544abaa6e3c75137c96f2f7e8787ad8cbec6c)

> Publish cumulative free org card drag update
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `ba944d0ad01ac2658e8a2be66b0789a57acb4234`

- تاریخ commit: `2026-10-31T16:41:17+00:00` | نویسنده: alirezarahimy-code
- والدها: `3e1544abaa6e3c75137c96f2f7e8787ad8cbec6c`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/ba944d0ad01ac2658e8a2be66b0789a57acb4234)

> Publish masked logging review bundle
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `8ade224d1924b8af4f674a1d72fa21827f185dee`

- تاریخ commit: `2026-10-31T20:40:32+00:00` | نویسنده: alirezarahimy-code
- والدها: `37edf6ffe9d81c6d6e1ff2b69b9d6b901a0cb689 ba944d0ad01ac2658e8a2be66b0789a57acb4234`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/8ade224d1924b8af4f674a1d72fa21827f185dee)

> Merge source of truth branch arena/38fea5b6-ticket into main line
> 
> ادغام شاخهٔ arena/38fea5b6-ticket (نسخهٔ محصول 1.62.56 + چارچوب مهارت) که
> تنها شاخه‌ای بود که تغییرات واقعی 1.62.37..1.62.59 در آن بود ولی هرگز به
> main نرسیده بود. تعارض‌ها فقط در اسناد چارچوب مهارت (add/add) بود که نسخهٔ
> شاخه (کامل‌تر) پذیرفته شد.
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `552411ab4f11ed0f44910e665f4c6cff132dd938`

- تاریخ commit: `2026-10-31T20:42:29+00:00` | نویسنده: alirezarahimy-code
- والدها: `8ade224d1924b8af4f674a1d72fa21827f185dee`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/552411ab4f11ed0f44910e665f4c6cff132dd938)

> Align repository to live version 1.62.59 (source of truth)
> 
> - اعمال ۱۸ فایل برنامه از تازه‌ترین بستهٔ منتشرشده
>   downloads/ticket-update-1.62.59-cumulative-org-card-drag-20261031.zip
>   (تأییدشده با sha256: بایت‌به‌بایت یکسان)
> - VERSION: 1.62.56 → 1.62.59 و هماهنگ‌سازی README/نقشه سامانه
> - تست نگهبان تازه: tests/test_version_source_of_truth_static.py
>   (عقب‌ماندن مخزن از تازه‌ترین بسته، ناهم‌خوانی محتوا، نسخهٔ کهنه در README
>    و ناهم‌خوانی VERSION داخل بسته‌ها را می‌گیرد)
> - ثبت ناهم‌خوانی شناخته‌شده: files/VERSION داخل بسته 1.62.58 است ولی
>   manifest آن 1.62.59 (باگ بسته، در KNOWN_PACKAGE_VERSION_DRIFT ثبت شد)
> - docs/RELEASE-1.62.59-FA.md: علت ریشه‌ای، کاری که شد، وضعیت واقعی تست‌ها
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `bb1c024a9f934b82e5fce64ad66d0ce3b8bbb569`

- تاریخ commit: `2026-11-01T00:15:48+03:30` | نویسنده: alirezarahimy-code
- والدها: `e2afc13a9e185c7dfcc1e9757675a3180cd98fee 552411ab4f11ed0f44910e665f4c6cff132dd938`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/bb1c024a9f934b82e5fce64ad66d0ce3b8bbb569)

> Merge pull request #5 from alirezarahimy-code/arena/fb881d13-ticket
> 
> Restore agent skill boot framework (31 skills + system map + boot tool)

### `914eaa8c450f96d89e973f0acb88a0586795da8f`

- تاریخ commit: `2026-10-31T20:52:59+00:00` | نویسنده: alirezarahimy-code
- والدها: `bb1c024a9f934b82e5fce64ad66d0ce3b8bbb569`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/914eaa8c450f96d89e973f0acb88a0586795da8f)

> Align the whole test suite with 1.62.59 (66 stale tests fixed, suite green)
> 
> پس از هم‌ترازی کد با ۱.۶۲.۵۹، ۶۶ تست ایستا قرمز بود (۴۰ مورد از قبل روی
> شاخهٔ ۱.۶۲.۵۶ هم قرمز بود). همه اصلاح شدند: pytest tests → 379 passed, 0 failed.
> 
> اصلاحات تست (بدون تغییر رفتار محصول):
> - پین‌های کش منقضی → مقادیر واقعی زنده
> - صفحهٔ دانلود → بررسی نسخه‌آگنوستیک با tests/download_page_helpers.py
> - بسته‌های تاریخی → بررسی سلامت آرشیو/SHA-256 به‌جای برابری با کد جاری
> - مجوزهای غذا → مدل ۱.۶۲.۵۸ (food.menu / food.menu_edit / food.reports)
> - نمودار سازمانی → مدیریت مستقل/دفتر، ردیف‌بندی متغیر، اکسل به تفکیک شرکت
> - نگهبان پین کش → نشانهٔ صریح cache-pin-guard: legacy-zip
> 
> سه ایراد واقعی که همین کار آشکار کرد و اصلاح شد:
> - downloads/index.html روی بستهٔ ۱.۶۲.۳۶ گیر کرده بود؛ حالا ۱.۶۲.۵۹ و صفحهٔ
>   تاریخی ترمیم به downloads/repair-1.62.36.html منتقل شد (بدون حذف).
> - پین ناهمگون app.js: governance.php/ola.php/footer.php روی 3.4.27 مانده
>   بودند در حالی که index.php نسخهٔ 3.4.28 را لود می‌کرد → یکسان شد.
> - تست نگهبان نسخه به زنجیرهٔ بوت اضافه شد (۵ تست نگهبان).
> 
> مستند: docs/TEST-ALIGNMENT-1.62.59-FA.md (شامل تغییرهای رفتاری تأییدشده و
> محدودیت‌های تأییدنشده: PHP در این محیط نیست).
> 
> Co-authored-by: arena-agent <297053741+arena-agent@users.noreply.github.com>

### `860185dc2f389adc4a58e13e6bc4837fe96eaf32`

- تاریخ commit: `2026-11-01T00:23:13+03:30` | نویسنده: alirezarahimy-code
- والدها: `bb1c024a9f934b82e5fce64ad66d0ce3b8bbb569 914eaa8c450f96d89e973f0acb88a0586795da8f`
- [نمایش commit](https://github.com/alirezarahimy-code/ticket/commit/860185dc2f389adc4a58e13e6bc4837fe96eaf32)

> Merge pull request #6 from alirezarahimy-code/arena/fb881d13-ticket
> 
> Align the whole test suite with 1.62.59 (66 stale tests, suite green)


## ۴. فهرست مستندات و مانیفست‌های موجود در tree مرجع

محدودهٔ فهرست: اسناد متنی/نقشه در docs، گزارش‌ها و مانیفست‌ها در updates، اسناد/صفحه‌ها در downloads، و اسناد ریشه. این فهرست مسیر است؛ متن و فایل‌های اصلی همچنان در خود مخزن‌اند.

- `00_APPLY_PATCH_FA.txt`
- `00_BUNDLE_INFO_FA.txt`
- `00_CHANGELOG_ABSENT_REPORT_PRINT_DESIGN_FA.txt`
- `00_CHANGELOG_ACTIVITY_LOG_FA.txt`
- `00_CHANGELOG_ASSET_EXTRACT_FA.txt`
- `00_CHANGELOG_ASSET_INVENTORY_FA.txt`
- `00_CHANGELOG_AUTO_EXTRACT_FA.txt`
- `00_CHANGELOG_CDDVD_MEDIA_FORM_FA.txt`
- `00_CHANGELOG_CSRF_ROLE_FA.txt`
- `00_CHANGELOG_DOMAIN_SCAN_FA.txt`
- `00_CHANGELOG_FOOD_ADMIN_ROLE_FA.txt`
- `00_CHANGELOG_FOOD_FISH_TABLE_PRINTER_FIX_FA.txt`
- `00_CHANGELOG_FOOD_GROUPS_FA.txt`
- `00_CHANGELOG_FOOD_TEMPLATES_FA.txt`
- `00_CHANGELOG_FOOD_TICKET_FIX.txt`
- `00_CHANGELOG_FOOD_TICKET_FIXED_DB_FA.txt`
- `00_CHANGELOG_FOOD_TICKET_PASSWORD_TEST_FA.txt`
- `00_CHANGELOG_FOOD_TICKET_PHP_WORKER_FIX_FA.txt`
- `00_CHANGELOG_FULL_EXPORT_FA.txt`
- `00_CHANGELOG_GROUP_REPRESENTATIVE_L_UID_FA.txt`
- `00_CHANGELOG_INSTALL_DROP_FA.txt`
- `00_CHANGELOG_INTERNAL_ORDERS_ONLY_FA.txt`
- `00_CHANGELOG_KEY_ROLES_FA.txt`
- `00_CHANGELOG_LDAP_FIX_FA.txt`
- `00_CHANGELOG_NAV_SUBMENUS_FA.txt`
- `00_CHANGELOG_NETPRINT_ASSETS_FA.txt`
- `00_CHANGELOG_ORG_TREE_FA.txt`
- `00_CHANGELOG_PERFORMANCE_FA.txt`
- `00_CHANGELOG_PING_SELECT_FA.txt`
- `00_CHANGELOG_PRINTER_FILTER_FA.txt`
- `00_CHANGELOG_PRINT_ACCESS_1_37_9_FA.txt`
- `00_CHANGELOG_PRINT_MONITOR_SCAN_FA.txt`
- `00_CHANGELOG_ROLES_FA.txt`
- `00_CHANGELOG_SAVE_FIX_FA.txt`
- `00_CHANGELOG_SERVICES_TICKET_FORMS_FA.txt`
- `00_CHANGELOG_SETTINGS_SERVICES_ORG_FA.txt`
- `00_CHANGELOG_SOURCE_DELETE_TENTER_1_37_9_FA.txt`
- `00_CHANGELOG_TICKET_TRAFFIC_PERF_FA.txt`
- `00_CHANGELOG_TICKET_WORKFLOW_FA.txt`
- `00_CHANGELOG_TRAFFIC_CONTROL_FA.txt`
- `00_CHANGELOG_WORKER_ALWAYS_ON_FA.txt`
- `00_CLEAN_INSTALL_FA.txt`
- `00_FULL_PACKAGE_1.59.4_FA.txt`
- `00_FULL_PACKAGE_1.59.5_FA.txt`
- `00_FULL_PACKAGE_1.59.6_FA.txt`
- `00_FULL_PACKAGE_1.59.7_FA.txt`
- `00_FULL_PACKAGE_1.59.8_FA.txt`
- `00_FULL_PACKAGE_1.60.1_FA.txt`
- `00_FULL_PACKAGE_1.60.2_FA.txt`
- `00_FULL_PACKAGE_1.60.3_FA.txt`
- `00_FULL_PACKAGE_1.60_FA.txt`
- `00_INSTALL_1.32_GROUP_L_UID_FA.txt`
- `00_INSTALL_WORKER_V5_FA.txt`
- `00_MERGE_REPORT_GROUPS_FA.txt`
- `00_NETPRINT_FA.txt`
- `00_PLAN_ACTIVITY_LOG_FA.txt`
- `00_PLAN_ORGANIZATION_FA.txt`
- `00_PLAN_PERMISSIONS_FA.txt`
- `00_PLAN_ROLE_DELEGATION_FA.txt`
- `00_WORKER_RUNBOOK_FA.txt`
- `AGENTS.md`
- `ARCHITECTURE.md`
- `CLAUDE.md`
- `DELIVERY-MANIFEST-fa.txt`
- `FOOD-ORDERS-1.38-OPERATIONS-fa.md`
- `FOOD-ORDERS-1.38-PATCH-2-NOTES-fa.md`
- `FOOD-ORDERS-1.38-PATCH-3-NOTES-fa.md`
- `FOOD-ORDERS-1.38-PATCH-4-NOTES-fa.md`
- `FOOD-ORDERS-1.38-PATCH-5-NOTES-fa.md`
- `FOOD-ORDERS-1.38-PATCH-6-NOTES-fa.md`
- `OPTIMIZATION_SUMMARY.md`
- `README-FA.txt`
- `README.md`
- `START-FOOD-WORKER.txt`
- `START-HERE.txt`
- `UPDATE-WORKER-V4.txt`
- `docs/ARCHITECTURE-FA.md`
- `docs/CODE-AUDIT-FA.md`
- `docs/DATA-RETENTION-POLICY-FA.md`
- `docs/DB-PERFORMANCE-AUDIT-FA.md`
- `docs/DOWNLOAD-PUBLISHING-POLICY-FA.md`
- `docs/HANDOFF-FA.md`
- `docs/NETWORK-INVENTORY-SETUP-FA.md`
- `docs/PLAN-CODE-PROTECTION-FA.md`
- `docs/README-FIX-FOOD-CALENDAR-COUNT-FA.md`
- `docs/README-RECOVERY-FA.md`
- `docs/RELEASE-1.62.36-FA.md`
- `docs/RELEASE-1.62.59-FA.md`
- `docs/REPORT-FA.md`
- `docs/SESSION-BOOT-FA.md`
- `docs/SKILL-MANIFEST-FA.md`
- `docs/SYSTEM-DETAILS-FA.txt`
- `docs/SYSTEM-MAP-FA.md`
- `docs/TEST-ALIGNMENT-1.62.59-FA.md`
- `docs/TEST_COVERAGE_MATRIX_FA.md`
- `docs/aggregate-report-preview.html`
- `docs/system-map/FEATURES.tsv`
- `docs/system-map/FILES.tsv`
- `docs/system-map/ROUTES.tsv`
- `docs/system-map/SYMBOLS.tsv`
- `docs/system-map/TABLES.tsv`
- `docs/system-map/UPDATES.tsv`
- `docs/system-map/summary.json`
- `downloads/CHANGE-REPORT-FA-1.62.43.md`
- `downloads/CHANGE-REPORT-FA-1.62.44.md`
- `downloads/CHANGE-REPORT-FA-1.62.45.md`
- `downloads/CHANGE-REPORT-FA-1.62.46.md`
- `downloads/CHANGE-REPORT-FA-1.62.47.md`
- `downloads/CHANGE-REPORT-FA-1.62.56-CUMULATIVE.md`
- `downloads/CHANGE-REPORT-FA-1.62.57.md`
- `downloads/CHANGE-REPORT-FA-1.62.58.md`
- `downloads/CHANGE-REPORT-FA-1.62.59-CUMULATIVE.md`
- `downloads/CHANGE-REPORT-FA-1.62.59.md`
- `downloads/ORG-TREE-UPDATE-README-FA-1.62.56-CUMULATIVE.md`
- `downloads/README-AGGREGATE-UPDATE.md`
- `downloads/README-RECOVERY-FA.md`
- `downloads/URGENT-FIX-1.62.38-FA.md`
- `downloads/index.html`
- `downloads/repair-1.62.36.html`
- `downloads/ticket-repair-1.62.36-full-source-restore-20261027.html`
- `updates/2026-10-11-chatbot/manifest.json`
- `updates/2026-10-12-chatbot-tickets/manifest.json`
- `updates/2026-10-13-food-auto-delivery/manifest.json`
- `updates/2026-10-13-food-catalog/manifest.json`
- `updates/2026-10-13-release-154/manifest.json`
- `updates/2026-10-14-release-155/CHANGE-REPORT-FA.md`
- `updates/2026-10-14-release-155/manifest.json`
- `updates/2026-10-14-release-155/migrations/README-FA.txt`
- `updates/2026-10-16-release-156/CHANGE-REPORT-FA.md`
- `updates/2026-10-16-release-156/manifest.json`
- `updates/2026-10-17-release-157/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-157/manifest.json`
- `updates/2026-10-17-release-158-1/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-158-1/manifest.json`
- `updates/2026-10-17-release-158-2/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-158-2/manifest.json`
- `updates/2026-10-17-release-158-3/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-158-3/manifest.json`
- `updates/2026-10-17-release-158-4/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-158-4/manifest.json`
- `updates/2026-10-17-release-158/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-158/manifest.json`
- `updates/2026-10-17-release-159-1/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-159-1/manifest.json`
- `updates/2026-10-17-release-159-2/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-159-2/manifest.json`
- `updates/2026-10-17-release-159-3/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-159-3/manifest.json`
- `updates/2026-10-17-release-159-4/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-159-4/manifest.json`
- `updates/2026-10-17-release-159/CHANGE-REPORT-FA.md`
- `updates/2026-10-17-release-159/manifest.json`
- `updates/2026-10-18-release-159-5/CHANGE-REPORT-FA.md`
- `updates/2026-10-18-release-159-5/manifest.json`
- `updates/2026-10-18-release-159-6/CHANGE-REPORT-FA.md`
- `updates/2026-10-18-release-159-6/manifest.json`
- `updates/2026-10-18-release-159-7/CHANGE-REPORT-FA.md`
- `updates/2026-10-18-release-159-7/manifest.json`
- `updates/2026-10-18-release-159-8/CHANGE-REPORT-FA.md`
- `updates/2026-10-18-release-159-8/manifest.json`
- `updates/2026-10-18-release-160-1/CHANGE-REPORT-FA.md`
- `updates/2026-10-18-release-160-1/manifest.json`
- `updates/2026-10-18-release-160-2/CHANGE-REPORT-FA.md`
- `updates/2026-10-18-release-160-2/manifest.json`
- `updates/2026-10-18-release-160/CHANGE-REPORT-FA.md`
- `updates/2026-10-18-release-160/manifest.json`
- `updates/2026-10-19-release-160-3/CHANGE-REPORT-FA.md`
- `updates/2026-10-19-release-160-3/manifest.json`
- `updates/2026-10-19-release-160-4/CHANGE-REPORT-FA.md`
- `updates/2026-10-19-release-160-4/manifest.json`
- `updates/2026-10-19-release-160-5/CHANGE-REPORT-FA.md`
- `updates/2026-10-19-release-160-5/manifest.json`
- `updates/2026-10-20-release-161-10/CHANGE-REPORT-FA.md`
- `updates/2026-10-20-release-161-10/manifest.json`
- `updates/2026-10-20-release-161-6/CHANGE-REPORT-FA.md`
- `updates/2026-10-20-release-161-6/manifest.json`
- `updates/2026-10-20-release-161-7/CHANGE-REPORT-FA.md`
- `updates/2026-10-20-release-161-7/manifest.json`
- `updates/2026-10-20-release-161-8/CHANGE-REPORT-FA.md`
- `updates/2026-10-20-release-161-8/manifest.json`
- `updates/2026-10-20-release-161-9/CHANGE-REPORT-FA.md`
- `updates/2026-10-20-release-161-9/manifest.json`
- `updates/2026-10-21-release-161-10-r10/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r10/manifest.json`
- `updates/2026-10-21-release-161-10-r11/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r11/manifest.json`
- `updates/2026-10-21-release-161-10-r12/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r12/manifest.json`
- `updates/2026-10-21-release-161-10-r13/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r13/manifest.json`
- `updates/2026-10-21-release-161-10-r14/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r14/manifest.json`
- `updates/2026-10-21-release-161-10-r15/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r15/manifest.json`
- `updates/2026-10-21-release-161-10-r16/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r16/manifest.json`
- `updates/2026-10-21-release-161-10-r17/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r17/manifest.json`
- `updates/2026-10-21-release-161-10-r18/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r18/manifest.json`
- `updates/2026-10-21-release-161-10-r19/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r19/manifest.json`
- `updates/2026-10-21-release-161-10-r20/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r20/manifest.json`
- `updates/2026-10-21-release-161-10-r21/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r21/manifest.json`
- `updates/2026-10-21-release-161-10-r22/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r22/manifest.json`
- `updates/2026-10-21-release-161-10-r23/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r23/manifest.json`
- `updates/2026-10-21-release-161-10-r24/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r24/manifest.json`
- `updates/2026-10-21-release-161-10-r25/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r25/manifest.json`
- `updates/2026-10-21-release-161-10-r8/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r8/manifest.json`
- `updates/2026-10-21-release-161-10-r9/CHANGE-REPORT-FA.md`
- `updates/2026-10-21-release-161-10-r9/manifest.json`
- `updates/2026-10-22-release-161-10-r26/CHANGE-REPORT-FA.md`
- `updates/2026-10-22-release-161-10-r26/manifest.json`
- `updates/2026-10-22-release-161-10-r27/CHANGE-REPORT-FA.md`
- `updates/2026-10-22-release-161-10-r27/manifest.json`
- `updates/2026-10-22-release-161-10-r28/CHANGE-REPORT-FA.md`
- `updates/2026-10-22-release-161-10-r28/manifest.json`
- `updates/2026-10-22-release-161-10-r29/CHANGE-REPORT-FA.md`
- `updates/2026-10-22-release-161-10-r29/manifest.json`
- `updates/2026-10-22-release-161-10-r30/CHANGE-REPORT-FA.md`
- `updates/2026-10-22-release-161-10-r30/manifest.json`
- `updates/2026-10-22-release-161-10-r31/CHANGE-REPORT-FA.md`
- `updates/2026-10-22-release-161-10-r31/manifest.json`
- `updates/2026-10-24-release-161-10-r32-food-ui-hotfix/CHANGE-REPORT-FA.md`
- `updates/2026-10-24-release-161-10-r32-food-ui-hotfix/manifest.json`
- `updates/2026-10-24-release-161-10-r33-food-runtime-recovery/CHANGE-REPORT-FA.md`
- `updates/2026-10-24-release-161-10-r33-food-runtime-recovery/manifest.json`
- `updates/2026-10-24-release-161-10-r33.1-installer-fix/CHANGE-REPORT-FA.md`
- `updates/2026-10-24-release-161-10-r33.1-installer-fix/manifest.json`
- `updates/2026-10-25-release-161-10-r34-food-capitalio-dashboard/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r34-food-capitalio-dashboard/manifest.json`
- `updates/2026-10-25-release-161-10-r35-food-capitalio-dashboard/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r35-food-capitalio-dashboard/manifest.json`
- `updates/2026-10-25-release-161-10-r36-food-capitalio-dashboard/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r36-food-capitalio-dashboard/manifest.json`
- `updates/2026-10-25-release-161-10-r37-food-capitalio-dashboard/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r37-food-capitalio-dashboard/manifest.json`
- `updates/2026-10-25-release-161-10-r38-food-dashboard-runtime-hotfix/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r38-food-dashboard-runtime-hotfix/manifest.json`
- `updates/2026-10-25-release-161-10-r39-final-food-dashboard/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r39-final-food-dashboard/manifest.json`
- `updates/2026-10-25-release-161-10-r39-food-dashboard-color-status/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r39-food-dashboard-color-status/manifest.json`
- `updates/2026-10-25-release-161-10-r40-food-dashboard-grid-cascade-fix/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r40-food-dashboard-grid-cascade-fix/manifest.json`
- `updates/2026-10-25-release-161-10-r41-food-dashboard-month-guests-theme/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r41-food-dashboard-month-guests-theme/manifest.json`
- `updates/2026-10-25-release-161-10-r42-system-wide-day-night-theme/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r42-system-wide-day-night-theme/manifest.json`
- `updates/2026-10-25-release-161-10-r43-food-emergency-order-window/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-161-10-r43-food-emergency-order-window/manifest.json`
- `updates/2026-10-25-release-162-1-emergency-settings-primary-admin/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-1-emergency-settings-primary-admin/manifest.json`
- `updates/2026-10-25-release-162-10-org-liaison-units/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-10-org-liaison-units/manifest.json`
- `updates/2026-10-25-release-162-11-code-navigation-phase1/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-11-code-navigation-phase1/manifest.json`
- `updates/2026-10-25-release-162-12-org-actions-split/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-12-org-actions-split/manifest.json`
- `updates/2026-10-25-release-162-13-ticket-asset-actions-split/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-13-ticket-asset-actions-split/manifest.json`
- `updates/2026-10-25-release-162-14-service-actions-split/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-14-service-actions-split/manifest.json`
- `updates/2026-10-25-release-162-15-service-modal-fix/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-15-service-modal-fix/manifest.json`
- `updates/2026-10-25-release-162-16-role-settings-actions-split/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-16-role-settings-actions-split/manifest.json`
- `updates/2026-10-25-release-162-17-supervisor-holiday-actions-split/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-17-supervisor-holiday-actions-split/manifest.json`
- `updates/2026-10-25-release-162-18-account-misc-actions-split/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-18-account-misc-actions-split/manifest.json`
- `updates/2026-10-25-release-162-19-bulk-user-structure-actions-split/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-19-bulk-user-structure-actions-split/manifest.json`
- `updates/2026-10-25-release-162-2-food-dashboard-bulk-theme-fixes/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-2-food-dashboard-bulk-theme-fixes/manifest.json`
- `updates/2026-10-25-release-162-20-backup-split-bootstrap-lib/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-20-backup-split-bootstrap-lib/manifest.json`
- `updates/2026-10-25-release-162-3-food-staff-selection-theme/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-3-food-staff-selection-theme/manifest.json`
- `updates/2026-10-25-release-162-4-food-theme-traffic-history-fixes/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-4-food-theme-traffic-history-fixes/manifest.json`
- `updates/2026-10-25-release-162-5-host-theme-toggle-appjs/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-5-host-theme-toggle-appjs/manifest.json`
- `updates/2026-10-25-release-162-6-ticket-auto-assign-org-guard/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-6-ticket-auto-assign-org-guard/manifest.json`
- `updates/2026-10-25-release-162-7-traffic-history-all-events/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-7-traffic-history-all-events/manifest.json`
- `updates/2026-10-25-release-162-8-traffic-autofill-multi-approver/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-8-traffic-autofill-multi-approver/manifest.json`
- `updates/2026-10-25-release-162-9-manual-ticket-date-guest/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-9-manual-ticket-date-guest/manifest.json`
- `updates/2026-10-25-release-162-food-selected-orders-ticket-scope/CHANGE-REPORT-FA.md`
- `updates/2026-10-25-release-162-food-selected-orders-ticket-scope/manifest.json`
- `updates/2026-10-26-release-162-21-food-printed-delivery/CHANGE-REPORT-FA.md`
- `updates/2026-10-26-release-162-21-food-printed-delivery/manifest.json`
- `updates/2026-10-26-release-162-22-safe-system-health-export/README.fa.md`
- `updates/2026-10-26-release-162-22-safe-system-health-export/manifest.json`
- `updates/2026-10-26-release-162-23-safe-export-from-system-logs/README.fa.md`
- `updates/2026-10-26-release-162-23-safe-export-from-system-logs/manifest.json`
- `updates/2026-10-26-release-162-24-readable-system-health-logs/README.fa.md`
- `updates/2026-10-26-release-162-24-readable-system-health-logs/manifest.json`
- `updates/2026-10-27-release-162-24-cumulative/README.fa.md`
- `updates/2026-10-27-release-162-24-cumulative/manifest.json`
- `updates/2026-10-27-release-162-25-system-health-accuracy/README.fa.md`
- `updates/2026-10-27-release-162-25-system-health-accuracy/manifest.json`
- `updates/2026-10-27-release-162-26-asset-profile-form-fix/README.fa.md`
- `updates/2026-10-27-release-162-26-asset-profile-form-fix/manifest.json`
- `updates/2026-10-27-release-162-27-food-log-levels/README.fa.md`
- `updates/2026-10-27-release-162-27-food-log-levels/manifest.json`
- `updates/2026-10-27-release-162-28-technical-profile-form-refresh/README.fa.md`
- `updates/2026-10-27-release-162-28-technical-profile-form-refresh/manifest.json`
- `updates/2026-10-27-release-162-29-manual-ticket-without-physical-print/README.fa.md`
- `updates/2026-10-27-release-162-29-manual-ticket-without-physical-print/manifest.json`
- `updates/2026-10-27-release-162-30-admin-settings-manual-ticket/README.fa.md`
- `updates/2026-10-27-release-162-30-admin-settings-manual-ticket/manifest.json`
- `updates/2026-10-27-release-162-31-manual-attendance-existing-no-food-conversion/README.fa.md`
- `updates/2026-10-27-release-162-31-manual-attendance-existing-no-food-conversion/manifest.json`
- `updates/2026-10-27-release-162-32-manual-ticket-counts-as-delivered/README.fa.md`
- `updates/2026-10-27-release-162-32-manual-ticket-counts-as-delivered/manifest.json`
- `updates/2026-10-27-release-162-33-manual-delivery-and-technical-profile/README.fa.md`
- `updates/2026-10-27-release-162-33-manual-delivery-and-technical-profile/manifest.json`
- `updates/2026-10-27-release-162-34-attendance-delivery-reconciliation/README.fa.md`
- `updates/2026-10-27-release-162-34-attendance-delivery-reconciliation/manifest.json`
- `updates/2026-10-27-release-162-35-profile-functions-and-attendance-reconciliation/README.fa.md`
- `updates/2026-10-27-release-162-35-profile-functions-and-attendance-reconciliation/manifest.json`
- `updates/2026-10-27-release-162-36-manual-attendance-record-backfill/README.fa.md`
- `updates/2026-10-27-release-162-36-manual-attendance-record-backfill/manifest.json`
- `updates/2026-10-27-repair-162-36-full-source-restore/README.fa.md`
- `updates/2026-10-27-repair-162-36-full-source-restore/manifest.json`
- `updates/2026-10-30-release-162-56-cumulative-org-access-chart-multicompany/CHANGE-REPORT-FA-1.62.56-CUMULATIVE.md`
- `updates/2026-10-30-release-162-56-cumulative-org-access-chart-multicompany/ORG-TREE-UPDATE-README-FA-1.62.56-CUMULATIVE.md`
- `updates/2026-10-30-release-162-56-cumulative-org-access-chart-multicompany/manifest.json`
- `updates/2026-10-31-release-162-59-cumulative-post59/CHANGE-REPORT-FA-1.62.59-CUMULATIVE.md`
- `updates/2026-10-31-release-162-59-cumulative-post59/CHANGE-REPORT-FA-1.62.59.md`
- `updates/2026-10-31-release-162-59-cumulative-post59/README.fa.md`
- `updates/2026-10-31-release-162-59-cumulative-post59/manifest.json`
- `updates/2026-10-31-release-162-59-cumulative-visual-fix/CHANGE-REPORT-FA-1.62.59-CUMULATIVE.md`
- `updates/2026-10-31-release-162-59-cumulative-visual-fix/CHANGE-REPORT-FA-1.62.59.md`
- `updates/2026-10-31-release-162-59-cumulative-visual-fix/README.fa.md`
- `updates/2026-10-31-release-162-59-cumulative-visual-fix/manifest.json`

## ۵. فهرست ZIPهای downloads موجود در tree مرجع

فهرست فقط نام فایل‌های ردیابی‌شده است؛ ZIPها در این کار باز یا تست نشده‌اند. انتخاب، استخراج، نصب و بازیابی به بررسی جداگانه نیاز دارد.

- `downloads/R37.zip`
- `downloads/logging-review-bundle.zip`
- `downloads/organization-tree-source-20261026.zip`
- `downloads/system-logging-source-archive-20261031.zip`
- `downloads/ticket-full-1.59.4.zip`
- `downloads/ticket-full-1.59.6.zip`
- `downloads/ticket-full-1.59.7.zip`
- `downloads/ticket-full-1.59.8.zip`
- `downloads/ticket-full-1.60.1.zip`
- `downloads/ticket-full-1.60.2.zip`
- `downloads/ticket-full-1.60.3.zip`
- `downloads/ticket-full-1.60.zip`
- `downloads/ticket-repair-1.62.36-full-source-restore-20261027.zip`
- `downloads/ticket-update-1.56.zip`
- `downloads/ticket-update-1.57.zip`
- `downloads/ticket-update-1.58.1.zip`
- `downloads/ticket-update-1.58.2.zip`
- `downloads/ticket-update-1.58.3.zip`
- `downloads/ticket-update-1.58.4.zip`
- `downloads/ticket-update-1.58.zip`
- `downloads/ticket-update-1.59.1.zip`
- `downloads/ticket-update-1.59.2.zip`
- `downloads/ticket-update-1.59.3.zip`
- `downloads/ticket-update-1.59.4.zip`
- `downloads/ticket-update-1.59.5.zip`
- `downloads/ticket-update-1.59.6.zip`
- `downloads/ticket-update-1.59.7.zip`
- `downloads/ticket-update-1.59.8.zip`
- `downloads/ticket-update-1.59.zip`
- `downloads/ticket-update-1.60.1.zip`
- `downloads/ticket-update-1.60.2.zip`
- `downloads/ticket-update-1.60.3.zip`
- `downloads/ticket-update-1.60.4.zip`
- `downloads/ticket-update-1.60.5.zip`
- `downloads/ticket-update-1.60.zip`
- `downloads/ticket-update-1.61.10-asset-profile-refresh-r30-20261022.zip`
- `downloads/ticket-update-1.61.10-asset-quality-performance-r31-final-20261022.zip`
- `downloads/ticket-update-1.61.10-browser-layout-r5-20261020.zip`
- `downloads/ticket-update-1.61.10-code-335-guard-account-diagnostics-r20-20261021.zip`
- `downloads/ticket-update-1.61.10-code-335-org-manager-role-repair-r19-20261021.zip`
- `downloads/ticket-update-1.61.10-domain-code-merge-order-only-attendance-r18-20261021.zip`
- `downloads/ticket-update-1.61.10-domain-inventory-depth-r29-20261022.zip`
- `downloads/ticket-update-1.61.10-food-bulk-operations-r10-20261021.zip`
- `downloads/ticket-update-1.61.10-food-code-priority-manual-attendance-orders-r17-20261021.zip`
- `downloads/ticket-update-1.61.10-food-dashboard-FINAL-r38-20261025.zip`
- `downloads/ticket-update-1.61.10-food-dashboard-capitalio-r34-20261025.zip`
- `downloads/ticket-update-1.61.10-food-dashboard-capitalio-r35-20261025.zip`
- `downloads/ticket-update-1.61.10-food-dashboard-capitalio-r36-20261025.zip`
- `downloads/ticket-update-1.61.10-food-dashboard-capitalio-r37-20261025.zip`
- `downloads/ticket-update-1.61.10-food-dashboard-r39-20261025.zip`
- `downloads/ticket-update-1.61.10-food-dashboard-r39-final-20261025.zip`
- `downloads/ticket-update-1.61.10-food-dashboard-r40-layout-fix-20261025.zip`
- `downloads/ticket-update-1.61.10-food-dashboard-r41-month-guests-theme-20261025.zip`
- `downloads/ticket-update-1.61.10-food-dashboard-runtime-hotfix-r38-20261025.zip`
- `downloads/ticket-update-1.61.10-food-order-reports-local-account-linking-r16-20261021.zip`
- `downloads/ticket-update-1.61.10-food-orders-surname-emergency-window-r43-20261025.zip`
- `downloads/ticket-update-1.61.10-food-reports-group-orders-r22-20261021.zip`
- `downloads/ticket-update-1.61.10-food-reports-print-layout-r23-20261021.zip`
- `downloads/ticket-update-1.61.10-food-reports-print-layout-r23b-20261021.zip`
- `downloads/ticket-update-1.61.10-food-runtime-recovery-r33-20261024.zip`
- `downloads/ticket-update-1.61.10-food-runtime-recovery-r33.1-20261024.zip`
- `downloads/ticket-update-1.61.10-food-statistics-horizontal-cards-r25-20261021.zip`
- `downloads/ticket-update-1.61.10-food-statistics-horizontal-r24-20261021.zip`
- `downloads/ticket-update-1.61.10-food-ui-asset-hotfix-r32-20261024.zip`
- `downloads/ticket-update-1.61.10-global-day-night-theme-r42-20261025.zip`
- `downloads/ticket-update-1.61.10-inspection-alarm-admin-toggle-r9-20261021.zip`
- `downloads/ticket-update-1.61.10-layout-fix-r2-20261020.zip`
- `downloads/ticket-update-1.61.10-layout-fix-r3-20261020.zip`
- `downloads/ticket-update-1.61.10-layout-offset-unlimited-r11-20261021.zip`
- `downloads/ticket-update-1.61.10-local-asset-helper-fix-r27-20261022.zip`
- `downloads/ticket-update-1.61.10-local-inventory-button-r26-20261022.zip`
- `downloads/ticket-update-1.61.10-manual-attendance-settings-r15-20261021.zip`
- `downloads/ticket-update-1.61.10-manual-food-ticket-r12-20261021.zip`
- `downloads/ticket-update-1.61.10-network-inventory-workflow-r28-20261022.zip`
- `downloads/ticket-update-1.61.10-org-chart-no-personal-codes-r14-20261021.zip`
- `downloads/ticket-update-1.61.10-org-role-repair-panel-visible-r21-20261021.zip`
- `downloads/ticket-update-1.61.10-panel-movable-r4-20261020.zip`
- `downloads/ticket-update-1.61.10-revised.zip`
- `downloads/ticket-update-1.61.10-temporary-canteen-transfer-r13-20261021.zip`
- `downloads/ticket-update-1.61.10-ticket-lifecycle-guide-r6-20261020.zip`
- `downloads/ticket-update-1.61.10-traffic-destination-approver-r7-20261021.zip`
- `downloads/ticket-update-1.61.10-traffic-phone-inspection-reminder-r8-20261021.zip`
- `downloads/ticket-update-1.61.10.zip`
- `downloads/ticket-update-1.61.6.zip`
- `downloads/ticket-update-1.61.7.zip`
- `downloads/ticket-update-1.61.8.zip`
- `downloads/ticket-update-1.61.9.zip`
- `downloads/ticket-update-1.62.0-food-report-ticket-scope-20261025.zip`
- `downloads/ticket-update-1.62.1-food-emergency-settings-primary-admin-20261025.zip`
- `downloads/ticket-update-1.62.10-org-liaison-units-20261025.zip`
- `downloads/ticket-update-1.62.11-code-navigation-phase1-20261025.zip`
- `downloads/ticket-update-1.62.12-org-actions-split-20261025.zip`
- `downloads/ticket-update-1.62.13-ticket-asset-actions-split-20261025.zip`
- `downloads/ticket-update-1.62.13-ticket-asset-actions-split-fix1-20261025.zip`
- `downloads/ticket-update-1.62.14-service-actions-split-20261025.zip`
- `downloads/ticket-update-1.62.15-service-modal-fix-20261025.zip`
- `downloads/ticket-update-1.62.16-role-settings-actions-split-20261025.zip`
- `downloads/ticket-update-1.62.17-supervisor-holiday-actions-split-20261025.zip`
- `downloads/ticket-update-1.62.18-account-misc-actions-split-20261025.zip`
- `downloads/ticket-update-1.62.19-bulk-user-structure-actions-split-20261025.zip`
- `downloads/ticket-update-1.62.2-food-dashboard-bulk-theme-fixes-20261025.zip`
- `downloads/ticket-update-1.62.20-backup-split-bootstrap-lib-20261025.zip`
- `downloads/ticket-update-1.62.21-food-printed-delivery-20261026.zip`
- `downloads/ticket-update-1.62.22-safe-system-health-export-20261026.zip`
- `downloads/ticket-update-1.62.23-safe-export-from-system-logs-20261026.zip`
- `downloads/ticket-update-1.62.24-cumulative-20261027.zip`
- `downloads/ticket-update-1.62.24-readable-system-health-logs-20261026.zip`
- `downloads/ticket-update-1.62.25-system-health-accuracy-20261027.zip`
- `downloads/ticket-update-1.62.26-asset-profile-form-fix-20261027.zip`
- `downloads/ticket-update-1.62.27-food-log-levels-20261027.zip`
- `downloads/ticket-update-1.62.28-technical-profile-form-refresh-20261027.zip`
- `downloads/ticket-update-1.62.3-food-staff-report-theme-20261025.zip`
- `downloads/ticket-update-1.62.35-profile-and-attendance-reconciliation-20261027.zip`
- `downloads/ticket-update-1.62.36-aggregate-report-20261027.zip`
- `downloads/ticket-update-1.62.36-aggregate-v13729-20261027.zip`
- `downloads/ticket-update-1.62.36-aggregate-v13730-20261027.zip`
- `downloads/ticket-update-1.62.36-aggregate-v13731-20261027.zip`
- `downloads/ticket-update-1.62.36-aggregate-v13732-20261027.zip`
- `downloads/ticket-update-1.62.36-delivery-date-v13734-20261028.zip`
- `downloads/ticket-update-1.62.36-manual-ticket-record-reconciliation-20261027.zip`
- `downloads/ticket-update-1.62.36-report-fixes-v13734-20261027.zip`
- `downloads/ticket-update-1.62.36-reports-moved-v13733-20261027.zip`
- `downloads/ticket-update-1.62.37-asset-identity-v13735-20261028.zip`
- `downloads/ticket-update-1.62.38-HOTFIX-500-v13736-20261028.zip`
- `downloads/ticket-update-1.62.39-HOTFIX-FINAL-v13737-20261028.zip`
- `downloads/ticket-update-1.62.4-food-theme-traffic-history-20261025.zip`
- `downloads/ticket-update-1.62.40-HOTFIX-FINAL2-v13738-20261028.zip`
- `downloads/ticket-update-1.62.41-AUTO-EVERYTHING-v13739-20261028.zip`
- `downloads/ticket-update-1.62.42-food-calendar-order-count-v13740-20261028.zip`
- `downloads/ticket-update-1.62.43-group-ticket-design-save-print-20261028.zip`
- `downloads/ticket-update-1.62.44-food-guest-request-actions-20261028.zip`
- `downloads/ticket-update-1.62.45-food-guest-management-tab-20261028.zip`
- `downloads/ticket-update-1.62.46-aggregate-reconciliation-20261028.zip`
- `downloads/ticket-update-1.62.47-guest-history-delete-20261028.zip`
- `downloads/ticket-update-1.62.48-access-policy-20261029.zip`
- `downloads/ticket-update-1.62.49-org-tree-dynamic-20261030.zip`
- `downloads/ticket-update-1.62.5-host-theme-toggle-appjs-20261025.zip`
- `downloads/ticket-update-1.62.50-org-chart-hotfix-20261030.zip`
- `downloads/ticket-update-1.62.52-org-multi-company-20261030.zip`
- `downloads/ticket-update-1.62.53-org-chart-print-search-20261030.zip`
- `downloads/ticket-update-1.62.54-org-chart-print-cards-company-names-20261030.zip`
- `downloads/ticket-update-1.62.55-org-chart-derived-company-membership-20261030.zip`
- `downloads/ticket-update-1.62.56-cumulative-org-access-chart-multicompany-20261030.zip`
- `downloads/ticket-update-1.62.57-requested-fixes-20261031.zip`
- `downloads/ticket-update-1.62.58-food-permissions-groups-20261031.zip`
- `downloads/ticket-update-1.62.59-cumulative-org-card-drag-20261031.zip`
- `downloads/ticket-update-1.62.59-cumulative-post59-20261031.zip`
- `downloads/ticket-update-1.62.59-cumulative-visual-fix-20261031.zip`
- `downloads/ticket-update-1.62.59-role-save-and-org-sync-diagnostics-20261031.zip`
- `downloads/ticket-update-1.62.6-ticket-auto-assign-org-guard-20261025.zip`
- `downloads/ticket-update-1.62.7-traffic-history-all-events-20261025.zip`
- `downloads/ticket-update-1.62.8-traffic-autofill-multi-approver-20261025.zip`
- `downloads/ticket-update-1.62.8-traffic-autofill-multi-approver-fix1-20261025.zip`
- `downloads/ticket-update-1.62.8-traffic-autofill-multi-approver-fix2-20261025.zip`
- `downloads/ticket-update-1.62.9-manual-ticket-date-guest-20261025.zip`

---
این سند یک snapshot تاریخی است؛ تغییرات آینده باید جداگانه تاریخ و منبع خود را مشخص کنند.
