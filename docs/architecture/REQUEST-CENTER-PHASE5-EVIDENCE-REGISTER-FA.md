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
| ۱۱ | بستهٔ ارتقا + راهنمای نصب + پیش‌نیاز + Backup/Restore + برنامهٔ بازگشت | ✅ مصنوع؛ Restore-drill واقعی بر عهدهٔ محیط غیرعملیاتی مالک | `downloads/ticket-update-1.62.90-to-1.62.92-request-center-rc-candidate-20261107.zip` + SHA256 + راهنمای کامل `REQUEST-CENTER-INSTALL-UPGRADE-FA.md`؛ آزمون T08 (backup/restore اِندتواند) در Contract CI موجود است |
| ۱۲ | چهار Workspace مستقل+آزموده‌شده با دسترسی چندنقشی | ✅ ممیزی + آزمون ایستا؛ تأیید نهایی با پذیرش دستی مالک | Server-side guards ✓؛ سوییچر فقط در count>1 ✓؛ `Pages/*` ممیزی شد؛ ده‌ها اینترسکشن snapshot در فاز ۴ ثبت شد |
| ۱۳ | پیش‌نمایش نهایی + گزارش آزمون به تأیید کاربر | 🟡 بسته شد؛ **منتظر تأیید مالک** | این سند + Live Preview + PR #30 |

## ۴) بستهٔ کاندیدی ساخته‌شده (هرگز بدون اجازهٔ مالک نصب نمی‌شود)

- فایل: `downloads/ticket-update-1.62.90-to-1.62.92-request-center-rc-candidate-20261107.zip`
- SHA-256: `55013c7601004b9bc49cffe41a7890cd4200e1a8639f0e4c24a31266464bae06` (فایل `.sha256` کنار آن)
- نام‌گذاری عامدانه با `rc-candidate`؛ manifest: `release_status=RC_CANDIDATE_NOT_FOR_PRODUCTION`، `requires_version=1.62.90`، `migrations=[035]`، ۶۱ فایل، بدون tests/CI.
- ساخت: `tools/build_rc_update.py` (بازتولید قطعی با تاریخ ثابت؛ checksum آرشیو پس از بستن، CRC، path-traversal و یکتایی مسیرها بررسی می‌شود)

## ۵) محدودیت‌های صریح این فاز (چیزی را «تمام‌شده» اعلام نکنید)

- T07 (بازپخش کامل dump روی MariaDB + fingerprint) قرمز عمداً/به‌تصمیم مالک (D1) — **به همان دلیل، ادعای «Migration روی دیتابیس واقعی تست شد» مجاز نیست.**
- `SecurityTest` دو assertion قدیمی از پیش روی base قرمز (توابع `valid_choice`/`store_attachment` به `lib/` منتقل شده‌اند) — همان‌قدر که روی base قرمز است؛ اینجا رفع نشده (خارج از دامنهٔ RC).
- بستهٔ عملیاتی و تگ: هنوز انجام نشده و **فقط مالک** آن را انجام می‌دهد؛ VERSION شاخه (`1.62.92`) تصمیم مالک را انتظار می‌کشد (شاخه جلوتر از main ۱.۶۲.۹۰ است).
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
- `tools/build_rc_update.py` (جدید)، `downloads/CHANGE-REPORT-FA-1.62.92.md` (جدید)، `downloads/…rc-candidate-20261107.zip[.sha256]` (جدید)
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
> **نتیجهٔ CI پس از این push در انتهای همین بخش (اصلاحیهٔ پس از اجرا) ثبت می‌شود.**
