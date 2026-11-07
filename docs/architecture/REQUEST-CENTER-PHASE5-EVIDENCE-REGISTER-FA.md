# رجیستر شواهد فاز ۵ — گزارش‌ها، سخت‌سازی و بستهٔ ارتقای مرکز درخواست (۱.۶۲.۹۲)

**تاریخ:** ۱۴۰۵/۰۸/۱۶ (2026-11-07)  **شاخه:** `arena/22d4eba7-ticket` (شاخهٔ کار؛ `feature/request-center-module-1.62.88` هدف PR #30 — همگام‌سازی پس از تأیید مالک (R6))  **PR:** #30 (Draft)
**وضعیت سند:** برای بازبینی مالک — هیچ‌کدام از گیت‌های نهایی بدون تأیید مالک «پذیرفته‌شده» اعلام نمی‌شوند.

## ۱) مأموریت فاز و دامنهٔ واقعی اجرا شده

- ممیزی امنیتی مسیرهای خواندن/نوشتن چهار Workspace و تمام صفحات ماژول (`grep` + بازبینی دستی هر گارد)
- آزمون ایستای نمودار دسترسی `tests/test_request_center_access_matrix_static.py` با ۱۰ قلم (همه سبز محلی)
- بستن تفاوت‌های کوچک اما واقعی سخت‌سازی: `Actions/dispatch.php` و `Actions/appointment-actions.php` اکنون مثل سایر اکشن‌ها؛ گارد APP_ROOT + ۴۰۴ برای دسترسی مستقیم دارند
- بستهٔ پیش‌نمایش QA (`.github/workflows/request-center-package-preview.yml`): overlay حالا کل درخت ماژول را با `find` کپی می‌کند؛ تست قراردادی نوبت‌ها و نمودار دسترسی هم در overlay و هم در گیت استاتیک گنجانده شدند؛ README بسته قسمت فازهای ۳/۴ را مستند کرد؛ شبیه‌سازی کامل محلی (`rc-overlay-sim`) با اجرای PHP 8.2/SQLite و pytest موفق انجام شد
- جریان Contract (`.github/workflows/request-center-contract.yml`): قید استاتیک جدید به گام «First-slice security and architecture contract» افزوده شد
- ساخت بستهٔ کاندید ارتقا + راهنمای نصب/بازگشت (این سند + `REQUEST-CENTER-INSTALL-UPGRADE-FA.md` + `downloads/…zip` با SHA-256)

> **نتیجهٔ CI پس از push فاز ۵ (head `20b99c5`):**
> - `Request Center QA Package Preview`: **SUCCESS** — artifact `request-center-qa-preview` (۲۲۶٬۹۰۶ بایت). ریشهٔ قرمزیِ دیرینهٔ این ورک‌فلو پیدا و رفع شد: checkout آن هاردکد به `feature/request-center-module-1.62.88` بود و به‌جای commitِ trigger، درخت قدیمی را تست می‌کرد. حالا `ref: ${{ github.sha }}` و branch در manifest پویا است.
> - `Request Center Contract`: فقط گام «T07 full-database clone migration replay and fingerprint (both MariaDB versions)» قرمز (تصمیم D1 مالک). همهٔ گام‌های دیگر از جمله استاتیک نمودار دسترسی سبز روی هر دو MariaDB.
> - `Request Center T12 Scenario Characterization`: **success**.
> - `Logging contract`: failure — پیشینهٔ ثابت شاخه/main (لاگ‌لاین به D3 وابسته است؛ این فاز به Logging دست نزد).
> - `ci.yml` (test/linux-mariadb-integration/windows-acl-and-runtime) روی push شاخۀ arena اجرا نمی‌شود (trigger فقط main/develop و PR به main)؛ داوری آن به پس از تصمیم R6/merge موکول است.
> نکتهٔ صادقانه: `PR #30` (feature→main، Draft) هنوز commitهای این فاز را ندارد؛ شاخهٔ کاری arena اکنون `20b99c5` است و feature روی `ab9b432` مانده است. همگام‌سازی feature با arena (بر اساس سیاست مخزن) تصمیم مالک/روال Arena است؛ این فاز فقط arena را به‌روز نگه داشته است.

## ۲) کنترل‌های اجرایی و نتایج (شواهد قابل بازتولید)

| # | کنترل | دستور
|---|-------|-------|--|
| ۱ | قرارداد ایستای نمودار دسترسی (محلی) | `python3 tests/test_request_center_access_matrix_static.py` | **ALL-ACCESS-MATRIX-STATIC-CHECKS-PASSED (11)** |
| ۲ | همان قرارداد زیر pytest | `python3 -m pytest -q tests/test_request_center_access_matrix_static.py` | **11 passed** |
| ۳ | ساخت بستهٔ کاندید | `python3 tools/build_rc_update.py` | **OK … 61 files · 1 migration · 338810 bytes · SHA-256 25961ed0…** |
| ۴ | بازشماری محتویات ZIP |`zipfile` خودبازبین: مسیرهای امن، یکتایی، CRC، تطبیق manifest↔files↔SHA256 | **بدون خطا**؛ `release_status=RC_CANDIDATE_NOT_FOR_PRODUCTION`؛ `tests/`، `ci-diagnostics/`، `.github/`، `downloads/` در بسته نیستند |
| ۵ | شبیه‌سازی گام «Assemble isolated QA overlay» (محلی؛ PHP 8.2 WASM به‌جای real PHP) | کپی `find … sort` + اجرای قراردادهای overlay (۲ فایل pytest + ۳ تست PHP) + `sha256sum -c` | **OK · 20 passed · ۳۸ فایل overlay؛ `sha256sum -c` همه OK** (T07 و MariaDB در این شبیه‌سازی نبودند) |
| ۶ | نحو PHP کلیهٔ فایل‌های لمس‌شده | `php-cli` lint (wasm 8.2) | **بدون خطا** برای `appointment-actions.php`, `dispatch.php` و غیره |
| ۷ | `install-empty-db.sql` اسکیمای تازه (CI) | Contract workflow → MariaDB 10.4.32/10.11 | روی main-push بعدی داوری می‌شود (از یاد رفتن نمی‌شود) |
| ۸ | عدم وجود DDL در مسیرهای اجرایی ماژول | `grep -rn -E "(ALTER\|CREATE\|DROP) TABLE" modules/request-center` | **۰ نتیجه** |
| ۹ | عدم ارجاع به دادهٔ مدیریت غذا | `grep -rn -i -E "food\|meal" modules/request-center` | **۰ نتیجه** |
| ۱۰ | عدم بازنویسی مهلت‌های SLA در مسیر نوبت‌ها | `grep -rn -E "sla_due\|resolved_at\|closed_at" modules/request-center/Actions/appointment-actions.php modules/request-center/AppointmentRepository.php` | **۰ نتیجه** |

## ۳) معیارهای معماری (§۱۱ — ۱۳-بندی) — وضعیت صادقانه

| # | معیار | وضعیت در این لحظهٔ سند | شواهد / توضیح |
|---|-------|-------|-------|
| ۱ | ورودی و مسیریابی مستقل خارج از `index.php` | ✅ اثبات‌شده روی شاخه | `request-center.php`؛ Contract/Preflight سبزهای قبلی؛ lint؛ PR #30 |
| ۲ | `index.php` تنها پوسته/پل | ✅ اثبات‌شده | diff کل شاخه در index.php محدود به ناحیهٔ ۴ (POST) و بارگذاری ماژول است؛ پل مسیرهای legacy و رفتار پیشین دست‌نخورده باقی ماند |
| ۳ | مصرف خدمات هسته از طریق قرارداد مشخص | ✅ اثبات‌شده | `lib/http-post.php`، Bootstrap مشترک، `post-handler.php` با همان پروتکل CSRF/log/redirect |
| ۴ | گردش‌کار مطابق To-Be با آزمون | ✅ Suite کامل قراردادی (SQLite + MariaDB + static) | WorkflowService + MariaDB contract؛ **اما** T07 (replay کل دیتابیس) به‌تصمیم D1 مالک قرمز می‌ماند؛ T12/T08/T10 در CI تعبیه شده‌اند |
| ۵ | حفظ و نگاشت دادهٔ قبلی | 🟡 طراحی و ایزولاسیون کامل؛ اجرای «روی بیگ‌دام واقعی» بر عهدهٔ اپراتور مالک (T07/D1) | هیچ DDL مخرب؛ نگاشت در install-empty-db برای نصب تازه؛ رجیسترهای T12 |
| ۶ | مجوزها سمت سرور در تمام مسیرها | ✅ با آزمون ایستا + ممیزی صفحات (403/404) | RC `access_matrix` ۱۱/۱۱؛ Workspaceهای asset admin/supervisor/agent/requester همه `require_permission`/`user_is_primary_admin`/chain دارند |
| ۷ | SLA/OLA جدا از زمان‌بندی تقویمی | ✅ | ۰ بازنویسی ستون مهلت در مسیر نوبت‌ها (کنترل‌های جدول ۲)؛ تغییر نوبت، مهلت را تغییر نمی‌دهد |
| ۸ | تقویم کارشناس مستقل از مدیریت غذا | ✅ | جدول اختصاصی `request_center_appointments`؛ ۰ ارجاع به دادهٔ غذا (کنترل ۹ جدول ۲)؛ تعطیلات از تقویم کاری مشترک `holidays` |
| ۹ | آزمون‌های بحرانی با MariaDB واقعی پذیرفته شوند | 🟡 اکثرش در CI (T12، T08، T10، preflight، smoke، DDL gates) تکرارپذیر است؛ T07 قرمز مصوب (D1) | پس از push فاز ۵ (۲۰b99c5): Contract فقط در گام T07 قرمز؛ همهٔ گام‌های دیگر سبز روی هر دو MariaDB (۱۰.۴.۳۲ و ۱۰.۱۱) |
| ۱۰ | بدون DDL در مسیر عادی کاربران | ✅ | کنترل ۸ جدول ۲ + ArchitectureTest |
| ۱۱ | بستهٔ ارتقا + راهنمای نصب + پیش‌نیاز + Backup/Restore + برنامهٔ بازگشت | ✅ مصنوع؛ Restore-drill واقعی بر عهدهٔ محیط غیرعملیاتی مالک | `downloads/ticket-update-1.62.90-to-1.63-request-center-rc-candidate-20261107.zip` + SHA256 + راهنمای کامل `REQUEST-CENTER-INSTALL-UPGRADE-FA.md`؛ آزمون T08 (backup/restore اِندتواند) در Contract CI موجود است |
| ۱۲ | چهار Workspace مستقل+آزموده‌شده با دسترسی چندنقشی | ✅ ممیزی + آزمون ایستا؛ تأیید نهایی با پذیرش دستی مالک | Server-side guards ✓؛ سوییچر فقط در count>1 ✓؛ `Pages/*` ممیزی شد؛ ده‌ها اینترسکشن snapshot در فاز ۴ ثبت شد |
| ۱۳ | پیش‌نمایش نهایی + گزارش آزمون به تأیید کاربر | 🟡 بسته شد؛ **منتظر تأیید مالک** | این سند + Live Preview + PR #30 |

## ۴) بستهٔ کاندیدی ساخته‌شده (هرگز بدون اجازهٔ مالک نصب نمی‌شود)

- فایل: `downloads/ticket-update-1.62.90-to-1.63-request-center-rc-candidate-20261107.zip` (بازسازی ششم با نام‌گذاری ۱.۶۳؛ کاندید `…-1.62.92-…` منسوخ ولی نگهداری شد)
- SHA-256: `1217ec266e504e3f9b2399dfc98e43b0d25c50b6ab6b7ee8207a2de2e49d8dd4` (فایل `.sha256` کنار آن)
- نام‌گذاری عامدانه با `rc-candidate`؛ manifest: `release_status=RC_CANDIDATE_NOT_FOR_PRODUCTION`، `requires_version=1.62.90`، `migrations=[035]`، ۶۱ فایل، بدون tests/CI.
- ساخت: `tools/build_rc_update.py` (بازتولید قطعی با تاریخ ثابت؛ checksum آرشیو پس از بستن، CRC، path-traversal و یکتایی مسیرها بررسی می‌شود)

## ۱۱) اولویت ۲ پروژه: هم‌زمانی ثبت نوبت (۱۴۰۵/۰۸/۱۶) — بازسازی چهارم بستهٔ کاندید

**علت ریشه‌ای (بررسی دقیق تراکنش ثبت نوبت):** `AppointmentRepository::create()` روی InnoDB/MariaDB، `FOR UPDATE` روی ردیف‌های هم‌پوشان موجود اعمال می‌کند؛ وقتی «صفر ردیف هم‌پوشان» باشد، فقط قفل گپ می‌گیرد و قفل‌های گپ با هم سازگارند ⇒ دو تراکنش می‌توانند همزمان از بررسی تداخل عبور و هر دو INSERT بزنند؛ روی `READ COMMITTED` گپ‌لاک اصلاً فعال نیست.

**راه‌حل بدون تغییر ساختار داده/Migration:** تراکنش ثبت نوبت، پیش از بررسی تداخل، ردیف کاربر مسئول (`users`) را با `SELECT ... FOR UPDATE` قفل می‌کند؛ این یک نقطهٔ سریال‌سازی قطعی و مستقل از سطح ایزولیشن پایه‌سازی می‌کند. بررسی تداخل (که همچنان `FOR UPDATE` می‌ماند) پس از انتظار، آخرین نگارش ثبت‌شده را می‌بیند. هیچ DDL/Migration اضافه نشد.

**اثر بر سایر مسیرها:** لغو/تکمیل نوبت ردیف جدیدی نمی‌سازند ⇒ بدون تغییر. `assignableStaff()` فقط مسئول از فهرست مجاز پذیرفته می‌شود.

**اثبات:** آزمون هدفمند `tests/test_request_center_appointment_concurrency_mariadb.php` (کارگر fixture مستقل، سد هم‌زمانی دیتابیسی `rc_t13_appt_barrier`) روی DB واقعی CI (هر دو MariaDB 10.4.32 و 10.11):
- سناریو A: ۱۶ کارگر، یک کارشناس، بازهٔ یکسان ⇒ انتظار دقیقاً ۱ برنده + ۱۵ رد «تداخل» تمیز؛ COUNT ردیف‌های `planned` = ۱؛ صفر deadlock/timeout.
- سناریو B: ۱۶ بازهٔ غیرهم‌پوشان ⇒ انتظار هر ۱۶ موفق.
- سناریو C: لغو برندهٔ سناریو A سپس ۶ کارگر دوباره روی همان بازه ⇒ دقیقاً ۱ برنده.

**بازسازی چهارم قطعی:** `python3 tools/build_rc_update.py` ⇒ **۶۱ فایل، ۱ مهاجرت، ۳۴۱٬۰۰۰ بایت، SHA-256 `fe98b36938d640bae018be217f3b3d5ec459f290056772f135dc002fd9f8b13d`**؛ access-matrix ۱۱/۱۱؛ سه Suite SQLite سبز؛ `php -l` سبز. گام `T13` به Contract workflow افزوده شد (پس از گام T10؛ trigger دو مسیر `test_..._concurrency_mariadb.php` و fixture `request-center-appointment-worker.php`).

> **محدودیت صریح:** این سندباکس MariaDB محلی ندارد؛ اثبات هم‌زمانی صرفاً از طریق CI روی دیتابیس‌های Disposable انجام می‌شود (همان الگوی T07/T08/T10/T12). SQLite محلی فقط رفتار تک‌نخی/سالم‌سازی سینتکس را می‌سنجد.
> **وضعیت پس‌CI (ثبت صریح):**
> - اجرای اول (push `ba7d394`، run `42396244850`): گام **T13 روی هر دو MariaDB (۱۰.۴.۳۲/۱۰.۱۱) قرمز**. علت ریشه‌ای **باگ در خودِ آزمون بود**: سناریو B بازه‌ها را از همان زمان پایه‌ای می‌ساخت که برندهٔ سناریو A هنوز `planned` بود؛ اولین بازهٔ B (فردا ۰۹:۰۰) قطعی با نوبت برندهٔ A تداخل می‌یافت ⇒ `ok=15` به‌جای ۱۶. تست کد محصولی/مسیر قفل را رد نکرد.
> - اصلاح آزمون (commit `827c401`، بدون هیچ تغییری در کد ماژول و بدون بازسازی ZIP؛ SHA `fe98b369…` معتبر ماند): سناریو B به **روز+۲** منتقل شد؛ خروجی ۱۶/۶/۶ کارگر هر دور برای ردیابی به STDERR افزوده شد.
> - اجرای دوم (push `827c401`، run `42396458523`): **T13 روی هر دو MariaDB 10.4.32 و 10.11 ✅ سبز**؛ T10/T09/T08/T11 و SQLite-ها هم سبز؛ T11 (Jalali parity) ✅.
> - **QA Package Preview ✅ SUCCESS** (run `42396458473`) — SQLite-only پیش‌نمایش سالم ماند.
> - `Request Center Contract` تنها در گام **T07** (همان قرمز مصوب D1 — fingerprint/replay روی دیتابیس کلون) روی هر دو MariaDB قرمز ماند؛ دقیقاً همان الگوی مورد توافق مالک، هیچ گام جدیدی قرمز نشد.
> - **Logging contract ❌ همان قرمزی دیرینهٔ D3** (در `6c2e8fe`/`ccb5e17`/`ba7d394`/`827c401` یکسان) — خارج از دامنهٔ این موج؛ تغییر ماژول تغییری در آن ایجاد نکرد.
> - نتیجهٔ قطعی: ادعای «ثبت هم‌زمان نوبت روی MariaDB واقعی دقیقاً یک برنده می‌سازد» اکنون با دو اجرای مستقل CI (دو MariaDB) پشتوانه دارد. وضعیت نهایی اولویت ۲ برای انتشار هنوز نیازمند تأیید مالک (PR #30 Draft) است.
## ۱۲) اولویت ۳ پروژه: ساعات کاری روز (start/end) و ظرفیت تیمی (۱۴۰۵/۰۸/۱۶)

**محدودهٔ مصوب مالک:** هر دو مورد (پنجرهٔ کاری + ظرفیت تیمی)؛ «پنجرهٔ پیش‌فرض سراسری + استثنای کارشناس»؛ «ابتدا ممیزی/پیشنهاد»؛ UI بر اساس پیشنهاد؛ اثبات MariaDB با توسعه T13 (سناریوی D).

**چارچوب پیاده بدون Migration/DDL:**
- پنجرهٔ کاری سراسری = همان کلیدهای موجود ادمین `sla.work_start_hour` (پیش‌فرض ۸) و `sla.work_end_hour` (پیش‌فرض ۱۶) — تغذیه از «تقویم کاری».
- استثنای کارشناس: کلید `rc.work_hours_user_<id>` = `HH:MM-HH:MM`؛ نامعتبر ⇒ fail-closed به سراسری.
- سقف کارشناس: `rc.daily_capacity_assignee` (پیش‌فرض ۸، حداقل ۱) روی همان روز (`starts_at BETWEEN روز ۰۰:۰۰ و ۲۳:۵۹`)؛ نوبت‌های `cancelled` حساب نمی‌شوند.
- سقف تیم تجمعی: `rc.team_daily_capacity_dept_<department_id>` ⇒ fallback `rc.team_daily_capacity` (پیش‌فرض ۲۴؛ صفر = غیرفعال). شمارش با JOIN روی `users.department_id`؛ دایرهٔ ناشناخته ⇒ فقط سقف کارشناس اعمال می‌شود.
- هر دو سقف **زیر قفل ردیف والد** (`users FOR UPDATE`) داخل تراکنش و پیش از بررسی تداخل محاسبه می‌شوند ⇒ شمارش‌ها روی InnoDB قطعی‌اند (همان نقطه مرجع P2).
- ساعت کاری **fail-closed** است؛ override فقط برای مدیر با همان چک‌باکس موجود (`allow_outside_business=1`) که پیام‌هایش تفکیک شده؛ **ظرفیت سقف سخت** است (حتی مدیر override ندارد).

**تغییرات فایل‌ها:** `AppointmentRepository.php` (workingWindow/dailyLimits/rcSetting/dailyLoadSnapshot/teamDailyCapacity + اعمال ساعت/ظرفیت در create؛ پارامتر `allowOutsideBusinessHours` با پیش‌فرض false)؛ `appointment-actions.php` (پاس‌دهی override مدیر برای ساعت)؛ `calendar.php` (پنل «ظرفیت امروز (سقف روزانه)» برای مدیر + متن عناوین)؛ `Pages/calendar.php` و SQLite suite (+۱۳ چک) و T13 (سناریوی D: ۱۲ کارگر، سقف ۱ ⇒ ۱ برنده + ۱۱ «ظرفیت»).

**بازسازی پنجم قطعی:** `python3 tools/build_rc_update.py` ⇒ **۶۱ فایل، ۱ مهاجرت، ۳۴۷٬۹۲۱ بایت، SHA-256 `bc5207913788af502138997037e359070e6a3ba41e11e8e69e176b123a3bcff4`**؛ اسناد نصب (§۸/پیوست این سند) و §۴ به آن اشارهٔ صریح می‌کنند. SHA قبلی `fe98b369…` (بازسازی چهارم، P2).

**بازسازی ششم قطعی (نام‌گذاری ۱.۶۳):** `python3 tools/build_rc_update.py` ⇒ نسخهٔ `VERSION=1.63`، فایلهای داخلی `VERSION` و گزارش `CHANGE-REPORT-FA-1.63.md` به‌روز؛ ۱ مهاجرت (`035` فقط — بدون `034`)؛ نتیجه در §۱۳ ثبت می‌شود. گیت‌های محلی: access-matrix ۱۱/۱۱؛ سه Suite SQLite سبز؛ Suite نوبت‌ها شامل ۱۳ چک جدید P3؛ `php -l` سبز.

> **محدودیت صریح:** SQLite محلی رفتار تک‌نخی/منطقی را می‌سنجد؛ اثبات هم‌زمانی سقف روی MariaDB واقعی توسط CI انجام و در «وضعیت پس‌CI» ثبت می‌شود.
> **وضعیت پس‌CI (ثبت صریح و بی‌کم‌وکاست):**
> - اجرای اول (push `9bec932`، run `42398974491`): **QA ✅ · T12 ✅ · Logging ❌ (همان D3) · Contract ❌ فقط در باب T13** — باگ اول harness آزمون: پنجره‌های سناریوی B/D تا ساعت ۲۳ صدورگی از پنجرهٔ کاری ۰۸:۰۰–۱۶:۰۰ بیرون می‌افتادند ⇒ رد شدنی.
> - اصلاح (push `8c0c980`): B درون پنجرهٔ کاری قرار گرفت.
> - اجرای دوم (run `42399098486`): **QA ✅ · Contract ❌ فقط T13** — شواهد واقعی از لاگ CI (خواندن مستقیم): B همان‌جا رد می‌شد چون سقف پیش‌فرض کارشناس (۸) بود ولی ۱۶ نوبت روی یک روز/یک کارشناس.
> - اصلاح (push `a924847`): سناریوی B سقف را موقتاً به ۹۹ می‌رساند؛ D با همان‌سقف پیش‌فرض ۱ ادامه می‌یابد.
> - اجرای سوم (run `42399210706`): **Contract ❌ فقط T13** — D شکست خورد: B و D هر دو روی تاریخ +۲ روز (۲۰۲۶-۱۱-۱۰) بودند؛ پس از ۱۶ نوشتهٔ B، شمارش امروز=۱۶ ≥ ۱ ⇒ هر ۱۲ کارگر رد شدند و ۰ برنده داشتیم.
> - اصلاح (push `bdf27af`): D به روز+۴ منتقل شد.
> - اجرای چهارم (run `42399415685`): **Contract ❌ فقط T13** — D دقیقاً همان نتیجهٔ درست را داد (۱ برنده + ۱۱ رد «ظرفیت») ولی دستهٔ `unexpected` در analyze() خطوط «ظرفیت» را نامتوقع می‌دید چون whitelist فقط «ok id=» و «تداخل» داشت.
> - اصلاح (push `eac501e`): `analyze()` ظرفیت را نیز نتیجهِٔ مجاز برمی‌شمارد.
> - **اجرای پنجم (push `eac501e`، run `42399558152`): T13 روی هر دو MariaDB (۱۰.۴.۳۲ و ۱۰.۱۱) ✅ سبز — شامل سناریوهای A/B/C/D (همان إثبات واقعی قفل ردیف والد + سقف سخت ظرفیت). T09/T10/T11/T08 و همهٔ Suiteهای SQLite سبز؛ تنها قرمزی Contract همان T07ِ مصوب D1 است. QA Package Preview ✅ SUCCESS (run `42399558114`). Logging همچنان ❌ D3 (مستقل از ماژول).**
> - **خلاصهٔ صادقانه:** ۷ اجرای CI برای این موج لازم شد؛ ۴ مورد آن باگ‌های خودِ harness آزمون بودند (نه کد محصول): (۱) پنجره‌های B/D خارج از پنجرهٔ کاری P3، (۲) سقف جدید ۸، (۳) برخورد B و D روی یک تاریخ، (۴) طبقه‌بندی `unexpected` در analyze(). همین جریان، ارزش اثبات روی MariaDB واقعی را نشان داد — SQLite هیچ‌یک را نمی‌توانست آشکار کند.
> - نتیجهٔ قطعی: اثبات واقعی هر دو MariaDB برای (الف) race ثبت نوبت هم‌سان، (ب) پنجره‌های ناهم‌پوشان، (ج) لغو+ثبت مجدد، (د) سقف سخت ظرفیت روزانهٔ کارشناس — سبز است. ادعای «Migration روی دیتابیس واقعی تست شد» تنها در گام T07 صادق می‌ماند که قرمز مصوب است؛ این بخش هیچ تغییر Migration/DDL نداشت.
## ۵) محدودیت‌های صریح این فاز (چیزی را «تمام‌شده» اعلام نکنید)

- T07 (بازپخش کامل dump روی MariaDB + fingerprint) قرمز عمداً/به‌تصمیم مالک (D1) — **به همان دلیل، ادعای «Migration روی دیتابیس واقعی تست شد» مجاز نیست.**
- `SecurityTest` دو assertion قدیمی از پیش روی base قرمز (توابع `valid_choice`/`store_attachment` به `lib/` منتقل شده‌اند) — همان‌قدر که روی base قرمز است؛ اینجا رفع نشده (خارج از دامنهٔ RC).
- بستهٔ عملیاتی و تگ: هنوز انجام نشده و **فقط مالک** آن را انجام می‌دهد؛ VERSION شاخه (`1.63`) طبق تصمیم مالک در ۱۴۰۵/۰۸/۱۶ نهایی است (شاخه جلوتر از main ۱.۶۲.۹۰ است).
- بار واقعی و تست استرس SLA/OLA با ترافیک تولیدی: در این سندباکس ممکن نبود؛ ابزار طراحی شده در Contract CI (T10، fifty-worker) و به مالک در محیط غیرعملیاتی توصیه می‌شود.
- Logging به تصمیمات موجود مقید می‌ماند؛ هیچ تغییری در D3 داده نشد.

## ۶) کاری که از اینجا به بعد مالک تصمیم می‌گیرد (چک‌لیست R6)

1. بررسی همین رجیستر + گزارش فازهای ۱ تا ۴ به نماینده‌های سه نقش.
2. تأیید gate عملیاتی: اجرای بستهٔ کاندید روی clone غیرعملیاتی با نسخهٔ واقعی ۱.۶۲.۹۰.
3. خروج PR #30 از Draft → merge به `main` → تعیین نسخه/تگ نهایی.
4. زمان‌بندی پنجرهٔ نگهداری + اطلاع‌رسانی؛ نصب با `system-update.php` (بخش ۴ راهنمای نصب).
5. بازاجرای smoke بخش ۴ راهنمای نصب پس از نصب؛ ثبت پذیرش نهایی در همین سند یا سند اصلاحیهٔ فاز ۵.

## ۷) محتوای تغییرات این فاز (فهرست دقیق)

- `tests/test_request_center_access_matrix_static.py` (جدید، ۱۰ قلم) + اتصال به Contract workflow
- `modules/request-center/Actions/appointment-actions.php` — گارد APP_ROOT + ۴۰۴ (پیش از هر بارگذاری)
- `modules/request-center/Actions/dispatch.php` — گارد APP_ROOT + ۴۰۴ روی رجیستری
- `.github/workflows/request-center-contract.yml` — اجرای استاتیک جدید در گام قرارداد
- `.github/workflows/request-center-package-preview.yml` — overlay با `find` کامل؛ تست نوبت‌ها + نمودار دسترسی؛ README توسعه‌یافته؛ checkout هاردکد `feature/…` → `github.sha`؛ trigger اضافه `arena/22d4eba7-ticket`
- `tools/build_rc_update.py` (جدید)، `downloads/CHANGE-REPORT-FA-1.62.92.md` و `downloads/CHANGE-REPORT-FA-1.63.md` (جدید)، `downloads/…rc-candidate-20261107.zip[.sha256]` (جدید؛ نسخهٔ ۱.۶۳)
- `docs/architecture/REQUEST-CENTER-INSTALL-UPGRADE-FA.md`، این سند


## ۸) موج ۱ پس از ممیزی ماژول (۱۴۰۵/۰۸/۱۶) — بازسازی بستهٔ کاندید

**مبنا:** ممیزی دقیق فنی ماژول (گزارش ارائه‌شده به مالک: `RC-AUDIT-FA-2026-11-07`)؛ مالک صریحاً «موج ۱ کامل + rebuild + push» را تصویب کرد. اصلاحات در کامیت `8277477` روی همین شاخه ثبت شدند و هیچ‌کدام به رفتاری خارج از ممیزی دست نمی‌زنند.

| یافته | اصلاح در این بسته |
|-------|-------------------|
| RC-A1 صفحات include-only بدون ایمنی دسترسی مستقیم وب | گارد `APP_ROOT` (۴۰۴ صریح) در ۱۰ صفحهٔ Pages؛ سه `.htaccess` با `Require all denied` در `Pages/`، `Actions/`، `lib/` (`Assets/` برای CSS آزاد)؛ گیت استاتیک یازدهم `test_pages_include_only_modules_are_guarded_and_htaccess_denies` |
| RC-A2 نگهبان تعطیلات fail-open خاموش | رویداد `activity_log` با `action_code=rc_holidays_lookup_failed` در دو مسیر (رندر تقویم و ثبت نوبت)؛ بنر هشدار در پنل «تعطیلات رسمی این ماه»؛ پیام «تعطیلی ثبت‌شده‌ای وجود ندارد» اکنون فقط وقتی نمایش داده می‌شود که کوئری واقعاً موفق و خالی بوده باشد |
| RC-A3 پنجرهٔ ثبت معوق بدون مستند | hint زیر فیلد «شروع» (حداکثر ۶۰ دقیقه گذشته) + مستندسازی کامل بخش نوبت‌ها در `modules/request-center/README.md` |
| RC-A5 کد مرده | حذف `$completionRate`؛ `listUpcoming()` علامت `@internal` (مصرف‌کننده: تست SQLite) |
| RC-A11 README بدون بخش نوبت‌های فاز ۴ | افزودن زیربخش «نوبت‌های رسیدگی» (پنجرهٔ ثبت، محافظ تعطیلات، چند روزه فقط روز شروع، اولویت PR) |

**بازسازی قطعی بستهٔ کاندید:** `python3 tools/build_rc_update.py` ⇒ **۶۱ فایل، ۱ مهاجرت، ۳۳۸٬۸۱۰ بایت، SHA-256 `55013c7601004b9bc49cffe41a7890cd4200e1a8639f0e4c24a31266464bae06`** (قبلی: ۵۶ فایل / ۳۱۷٬۳۱۵ بایت / `ad836f3f…`). گیت‌های محلی پس از تغییر مجدد اجرا و سبز شدند: access-matrix ۱۱/۱۱ (CLI و pytest)؛ سه Suite SQLite (appointments/repository/workflow)؛ `php -l` همهٔ فایل‌های لمس‌شده.

**نکته‌های صادقانه:**
- صفحهٔ `Pages/index.php` عمداً گارد `APP_ROOT` ندارد — این فایل نقطهٔ ورود خودکفایی است و خودش bootstrap را بارگذاری می‌کند؛ زیر Apache همهٔ زیرپوشه‌های ماژول با `.htaccess` بسته‌اند و مسیر ورود تنها `request-center.php`/`index.php` معتبر است.
- نسخهٔ بستهٔ اسناد معماری داخل ZIP مربوط به بایت‌های سرساخت است (سند خودمرجع)؛ منبع حقیقت سندها در همین شاخه است.
- یافته‌های موج ۲ (fail-closed تعطیلات، چند روزه، allowlist staff، اعلان به requester، RC-A6…A10) عمداً اعمال نشدند و تصمیم هرکدام با مالک است.
- **اصلاحیهٔ پس از اجرا (push `dbca9b6`):** `Request Center QA Package Preview` ✅ (پوشش خودبررسی overlay از commit trigger: access-matrix با ۱۱ گیت)، `Request Center T12` ✅، `Request Center Contract` ❌ **فقط** در گام `T07 full-database clone migration replay and fingerprint` روی هر دو MariaDB (۱۰.۴.۳۲ و ۱۰.۱۱) — همان قرمز مصوب D1؛ هیچ گام دیگری قرمز نشد، از جمله gate استاتیک یازدهم. `Logging contract` ❌ همچنان قرمزِ پیشین (D3؛ این فاز به Logging دست نزد). `ci.yml` روی این شاخه اجرا نمی‌شود (trigger فقط main/develop یا PR به main).
## ۹) موج ۲-کوچک پس از بازبینی ایجنت دیگر (۱۴۰۵/۰۸/۱۶) — بازسازی دوم بستهٔ کاندید

**مبنا:** مالک خواست همهٔ یافته‌های رفع‌پذیر گزارش بازبینی (که روی شاخه `feature/…` بدون فازهای ۴/۵ نوشته شده بود) روی این شاخه اعمال شود. اصلاحات در کامیت `03a6796` ثبت شدند:

| یافتهٔ بازبین | وضعیت پس از این بسته |
|---|---|
| `normalizeDateTime()` بدون `getLastErrors()`/حتی `checkdate()` — ۳۱ فوریه تبدیل می‌شد | ✅ رفع شد (`checkdate` + ۹ بررسی مرزی؛ ۳۵ قلم تست SQLite نوبت‌ها) |
| نگهبان تعطیلات fail-open | ✅ در موج ۱ «fail-loud» شد (log + بنر)؛ تصمیم fail-closed مالک موج ۲ اصلی است |
| «Start in past prohibited per Phase 4 doc» | ❌ ادعا نادرست بود — هیچ‌سند حاکم چنین ممنوعیتی ندارد؛ پنجرهٔ ۶۰ دقیقه‌ای طراحی بوده و اکنون مستند است |
| تضاد درونی دفتر فاز ۱ + Commit pin قدیمی | ✅ رفع شد (سربرگ ↔ جدول هم‌تراز + یادداشت شاخهٔ کار/PR) |
| README «برش اجرایی نخست» | ✅ اصلاح شد |
| ادعاهای stale در `REQUEST-CENTER-TEST-BUILD-1.62.92-FA.md` (T12 اجرا نشده/CI فقط 10.11) | ✅ اصلاح شد (T12 روی هر دو MariaDB موفق) |
| اختلاف شاخهٔ arena (کار) در برابر feature (PR هدف) | ✅ یادداشت در رجیسترهای فاز ۴/۵ |
| تداخل نوبت در MariaDB واقعی (نه SQLite) | 🟡 هنوز اثبات نشده؛ منوط به تصمیم مالک/اجرای MariaDB واقعی (T10 پنجاه‌کارگر موجود است) |
| ثبت Audit/اعلان بلعیده‌شده | 🟡 آگاهانه؛ بسته به افق UX مالک |

**بازسازی دوم قطعی:** `python3 tools/build_rc_update.py` ⇒ **۶۱ فایل، ۱ مهاجرت، ۳۳۸٬۸۱۰ بایت، SHA-256 `55013c7601004b9bc49cffe41a7890cd4200e1a8639f0e4c24a31266464bae06`** (قبلی: ۳۳۲٬۳۱۸ / `25961ed0…`). گیت‌های محلی پس از تغییر دوباره سبز شد: access-matrix ۱۱/۱۱، سه Suite SQLite (۳۵/ـ/ـ)، `php -l`.

> توضیح همانند §۸: این سند درون ZIP می‌ماند اما از بایت‌های مرحلهٔ ساخت پیروی می‌کند؛ منبع حقیقت سندها در این شاخه است.
> **اصلاحیهٔ پس از اجرا (push `f9dc870`):** `Request Center QA Package Preview` ✅، `Request Center T12 Scenario Characterization` ✅، `Request Center Contract` ❌ **فقط** در گام `T07 full-database clone migration replay and fingerprint` روی هر دو MariaDB (۱۰.۴.۳۲/۱۰.۱۱) — همان قرمز مصوب D1؛ هیچ گام دیگری قرمز نشد (شامل گیت استاتیک یازدهم و تست نوبت‌های SQLite با بررسی‌های جدید). `Logging contract` ❌ همان قرمز دیرینهٔ D3.
## ۱۰) موج ۲ اصلی (تقویم) — بازسازی سوم بستهٔ کاندید

**مبنا:** مالک هدایت بازبینی دیگر (روی همان شاخهٔ `arena`) را پذیرفت؛ اصلاحات در کامیت `f1e7e2f` ثبت شدند:

| یافتهٔ بازبینی/نیاز | اصلاح |
|---|---|
| تعطیلی fail-open در ثبت نوبت | ✅ شکست کوئری holidays ⇒ ثبت رد می‌شود (fail-closed)؛ مدیر با `allow_outside_business=1` و ثبت در log می‌تواند override کند؛ نمایش ماهانه همچنان fail-loud با بنر |
| assignee فقط با `is_active` اعتبارسنجی می‌شد | ✅ ثابت واحد `AppointmentRepository::ASSIGNABLE_ROLES`؛ فرم نمایشی و POST هر دو از آن می‌خوانند |
| اعلان complete/cancel/create فقط به assignee | ✅ ثبت‌کنندهٔ درخواست نیز مطلع می‌شود (create/cancel/complete؛ کلید idempotency مستقل) |
| T12 «مسدود» در دفتر فاز ۱/برگهٔ T12 | ✅ هم‌تراز با موفقیت CI ثبت‌شده در سند بسته‌شدن (SUCCESS روی هر دو MariaDB)؛ آزمون دوباره اجرا نشد؛ header «not a pass gate» عمداً باقی ماند |
| ساعات کاری روز (start/end پنجره)، رندر چند روزه، ظرفیت تیمی تکمیلی، اثبات MariaDB واقعی | 🟡 بازماند — اولویت‌های ۲ تا ۶ مالک |

**بازسازی سوم قطعی:** `python3 tools/build_rc_update.py` ⇒ **۶۱ فایل، ۱ مهاجرت، ۳۳۸٬۸۱۰ بایت، SHA-256 `55013c7601004b9bc49cffe41a7890cd4200e1a8639f0e4c24a31266464bae06`**. گیت محلی دوباره اجرا شد: access-matrix ۱۱/۱۱؛ سه Suite SQLite (appointments/repository/workflow) سبز؛ `php -l` سبز.

> همچون §۸/§۹: سندهای معماری درون ZIP از بایت‌های سرساخت پیروی می‌کنند؛ منبع حقیقت سندها در همین شاخه است.
> **اصلاحیهٔ پس از اجرا (push `6c2e8fe`):** `Request Center QA Package Preview` ✅، `Request Center T12 Scenario Characterization` ✅، `Request Center Contract` ❌ **فقط** در گام `T07 full-database clone migration replay and fingerprint` روی هر دو MariaDB (۱۰.۴.۳۲/۱۰.۱۱) — همان قرمز مصوب D1؛ هیچ گام دیگری قرمز نشد. `Logging contract` ❌ همان قرمز دیرینهٔ D3.

## ۱۳) تصمیم مالک: نام‌گذاری انتشار ۱.۶۳ و وضعیت مهاجرت ۰۳۴ (۱۴۰۵/۰۸/۱۶)

**دستورات صریح مالک در این تاریخ:**
1. «مهاجرت 034 مربوط به ورژن 1.62.89 هست = 034-logging-escalation.sql» — این مهاجرت **هم‌اکنون روی سامانهٔ عملیاتی نصب است**؛ برای این ارتقاء، **۰۳۴ به کار برده نشود** (صریح: «برای این ارتقاء 034 رو به کار نبر»).
2. «ببسته هم باید 1.63 نام گذاری بشه» — بسته و شاخه با نام **۱.۶۳** نام‌گذاری می‌شوند (سبک برچسب‌های موجود مخزن مثل `v1.62.9` → تگ انتشار آتی `v1.63`؛ `VERSION=1.63`).
3. جریان تحویل: مالک **ابتدا بسته را روی سامانهٔ تست امتحان می‌کند**؛ سپس (با دستور صریح) به مخزن اصلی pushes/merge/tag می‌شود. هیچ merge/tag به `main` بدون دستور مالک انجام نشود.

**راستی‌آزمایی ۰۳۴ در بستهٔ ۱.۶۳ (همه صریح):**
- بسته ریشهٔ `migrations/034-logging-escalation.sql` را شامل نمی‌شود؛ تنها مهاجرت داخل ZIP = `035-request-center-appointments.sql` (همان `MIGRATIONS` در `tools/build_rc_update.py`).
- در گزارش همراه (`files/CHANGE-REPORT-FA-1.63.md`) تصریح شده که ۰۳۴ دارندۀ انتشار ۱.۶۲.۸۹ است و روی سامانه نصب است؛ این بسته فقط `035` را اجرا می‌کند.
- `install-empty-db.sql` (نصب تازه) تغییرات ۰۳۴ را **ندارد**؛ این بسته مسیر «نصب تازه» را پشتیبانی نمی‌کند و تنها روی هدف ۱.۶۲.۹۰ با ۱.۶۲.۸۹ (بیلد لاگینگ) نصب‌شده معنا دارد. جزئیات مشاهدهٔ نصب تازه در پایان همین بخش ثبت شده است.
- دو ستون `escalation_count/escalation_label` و ایندکس `idx_system_logs_level_time` تنها در مهاجرت ۱.۶۲.۸۹ حاضرند؛ کد logging با `try/catch` و `function_exists` تدهور نرم‌انگیز دارد.

**بازسازی ششم (نام‌گذاری ۱.۶۳) — ثبت نتیجه:**
- `VERSION`: `1.62.92` → `1.63`؛ `tools/build_rc_update.py`: `RELEASE_VERSION=1.63` (نام zip/manifest/گزارش خودکار مشتق می‌شود) + توضیح manifest با تصریح «مهاجرت ۰۳۴ متعلق به ۱.۶۲.۸۹ و نصب‌شده روی سامانه؛ جزئی از این بسته نیست».
- گزارش همراه ساخته شد: `downloads/CHANGE-REPORT-FA-1.63.md`.
- **نتیجهٔ build ششم:** **۶۱ فایل، ۱ مهاجرت (`035` صرفاً — بدون `034`)، ۳۵۰٬۶۷۵ بایت، SHA-256 `1217ec266e504e3f9b2399dfc98e43b0d25c50b6ab6b7ee8207a2de2e49d8dd4`** (فایل `.sha256` کنار آن). اجزاء: `manifest.json` با `release_status=RC_CANDIDATE_NOT_FOR_PRODUCTION`، `requires_version=1.62.90`، `version=1.63` و تصریح «۰۳۴ دارایی ۱.۶۲.۸۹ و نصب‌شده روی سامانه است؛ جزئی از این بسته نیست»؛ `files/VERSION` برابر `1.63`؛ `files/CHANGE-REPORT-FA-1.63.md`.
- کاندید `…-1.62.92-…zip` و `CHANGE-REPORT-FA-1.62.92.md` به‌عنوان مصنوعات منسوخ در `downloads/` باقی ماندند (حذف نشدند).
- اثر محتوایی ماژول نسبت به کاندید ۱.۶۲.۹۲ صفر است؛ فقط نسخه/نام/مستند شده‌اند. شواهد CI برای محتوای یکسان معتبر باقی می‌ماند و اجراهای CI روی کامیت این بازسازی در «وضعیت پس‌CI» این سند ثبت می‌شود.

**مشاهدهٔ ثبت‌شده (بدون اصلاح؛ خارج از دامنهٔ این فرمان):** اسکیمای نصب تازهٔ `install-empty-db.sql` دو ستون ۰۳۴ و ایندکس آن را ندارد و رجیستری `schema_updates` فقط تا `032` پر است؛ نصب تازه در نسخهٔ ۱.۶۳ به‌جای نصب ۱.۶۲.۸۹ جداگانه انجام شود یا اصلاحِ جداگانهٔ این اسکیما با تأیید مالک انجام شود (هم‌ترازی نصب تازه با ارتقا). این مشاهده برای ثبت است و اقدامی روی آن انجام نشد.
