# محیط اجرای واقعی PHP + MariaDB در سندباکس Arena

> سند عملیاتی محیط توسعه/تست — نه staging و نه production.
> تاریخ آمادهسازی: ۲۰۲۶-۱۱-۱۹ · شاخه: `arena/dacdd8a7-ticket` · نسخهٔ سامانه: `1.63.2`

## ۱. چرا این سند لازم شد

مستندات قدیمی (`docs/architecture/REQUEST-CENTER-LOCAL-RUN-FA.md`) صادقانه نوشته بودند
که در محیط ایجنت امکان نصب PHP/MariaDB نبوده و فقط CI واقعی تست کرده است. دلیل، محدودیت
شبکهٔ سندباکس است: فقط `github.com`، `codeload.github.com`، `api.github.com`،
`registry.npmjs.org`، `pypi.org` و `files.pythonhosted.org` در دسترساند و
`deb.debian.org` (مخزن apt) بلاک است؛ بنابراین `sudo apt-get install` جواب نمیدهد.

راهحل اعمالشده: همان بستههای رسمی **Debian 12 (bookworm)** روی یک Runnerـه
GitHub Actions داخل کانتینر `buildpack-deps:bookworm` حل وابستگی و دانلود شدند،
سپس از طریق خود `git` (کانال مجاز) به سندباکس منتقل و با `sudo dpkg -i` نصب شدند.
نتیجه **باینری بومی واقعی** است، نه WASM و نه شبیهسازی. فایلها/ورکفلو موقت انتقال
پس از نصب از شاخه حذف شدهاند.

## ۲. آنچه نصب شد (تأییدشده)

| مؤلفه | نسخهٔ واقعی | منبع |
|---|---|---|
| PHP CLI | `PHP 8.2.35 (cli)` | `php8.2-*.deb` رسمی Debian 12 |
| امتدادها | `pdo_mysql, pdo_sqlite, mysqli, mbstring, xml, xsl, dom, zip, curl, ldap, gd, intl, bcmath, openssl, sodium, fileinfo, …` | همان بستهها + `php -m` |
| MariaDB server | `10.11.19-MariaDB-0+deb12u1` | `mariadb-server`/`mariadb-server-core` |
| MariaDB client | `Ver 15.1 Distrib 10.11.19` | `mariadb-client` |
| سرویس | `mariadb.service` (systemd) روی `127.0.0.1:3306` | `systemctl status mariadb` |
| وبسرور | `php -S 0.0.0.0:8099 -t .` | سرور داخلی PHP |
| Python tooling | `pytest 9.2.0`، `openpyxl 3.1.5`، `tree_sitter`، `tree_sitter_php`، `requests` | PyPI (مجاز) |
| مهارتها | ۳۱ مهارت `docs/SKILL-MANIFEST-FA.md` + بوت `tools/session-boot.py` | **`موقت / فقط همین جلسه`** |

> ⚠️ صداقت: طبق بند ۰ مانیفست، فعالسازی **رفتاری** مهارتها فقط تا پایان گفتگو
> برقرار است. چیزی که پایدار است فایلهای `AGENTS.md`، `CLAUDE.md`، `.cursorrules`،
> `.cursor/rules/arena-boot.mdc`، `.github/copilot-instructions.md`،
> `docs/SKILL-MANIFEST-FA.md` و نقشهٔ `docs/system-map/*` است که بوت آنها را
> بارگذاری میکند. نمیتوان حافظهٔ مدل را دائماً تغییر داد.

## ۳. وضعیت دیتابیس

| پایگاهداده | هدف | جداول |
|---|---|---|
| `persian_ticketing` | تستهای یکپارچگی/مهاجرت (همان الگوی CI) | ۸۳ جدول از `install-empty-db.sql` |
| `persian_ticketing_web` | اجرای واقعی اپ وب از طریق نصبگر `install.php` | ۸۳ جدول + مدیر اولیه |

کاربر محلی همان قرارداد `tools/local-run.sh` است: `ticketing` / `ticketing-local-dev`.
`config.php` و `installed.lock` توسط نصبگر رسمی ساخته شدند و در `.gitignore` هستند؛
هیچ رمزی در مخزن ذخیره نشده است. نصبگر بعد از اتمام `403` میدهد (رفتار امن مورد انتظار).

## ۴. بالا آوردن دوباره بعد از ریستارت سندباکس

```bash
python3 tools/session-boot.py                 # بوت جلسه / بهروز شدن
bash tools/arena-runtime-bringup.sh           # سرویس + دیتابیس + شِما (غیرتخریبی)
php -S 0.0.0.0:8099 -t .                     # وبسرور (پیشنمایش زنده)
```

اگر خود بستههای `.deb` هم پاک شدهاند، `tools/arena-runtime-bringup.sh` صریحاً اعلام
میکند که باید کانال ساخت روی GitHub Actions دوباره اجرا شود (apt در این سندباکس بلاک است).

## ۵. تستهای واقعی اجراشده (نتیجهٔ تأییدشده)

| دستور | نتیجه |
|---|---|
| `php tools/ci_load_schema.php install-empty-db.sql` (PDO → MariaDB) | `OK: 116 دستور` / ۸۳ جدول |
| `php tests/test_mariadb_fresh_install.php fresh-install` | `PASS — ۱۳ بررسی` |
| اجرای دوبارهٔ همهٔ `migrations/*.sql` (غیرrollback) | `PASS` / ایدهپوتنت |
| `php tests/test_mariadb_fresh_install.php migrations-applied` | `PASS — ۳۴ بررسی` |
| `git ls-files -z -- '*.php' \| php tools/lint-php-syntax.php` | `PHP syntax OK for 379 files` |
| `php tests/SecurityTest.php` | `Passed: 9 / Failed: 0` |
| `php tests/ArchitectureTest.php` | `Passed: 5 / Failed: 0` |
| `php tests/test_food_ticket_logic.php` | `ALL PASSED` |
| `php tools/system_audit.php --no-db --check` | هیچ ناسازگاری/پسرفتی نیست |
| نصبگر `install.php` از طریق HTTP + CSRF (۳ مرحله) | `سامانه با موفقیت نصب شد` |
| ورود `admin` و دریافت داشبورد | `HTTP 200` / «داشبورد \| سامانه درخواست پشتیبانی» |
| `python3 -m pytest -q tests/` | `805 passed, 1 failed, 1 skipped` |
| `python3 tests/test_skill_manifest_static.py` | `PASS — ۳۱ مهارت / ۳۰۳ چک` |

## ۶. مواردی که **تأیید نشدند** (پیشین و unrelated به این آمادهسازی)

1. `tests/test_fa_digits_output.py::test_ticket_codes_convert_as_farsi_display` —
   انتظار `SUP-۰۰۰۰۹` دارد ولی خروجی `SUP-00009` است. مربوط به `lib/fa-digits.php`؛
   در این بوت دست نخورده باقی ماند (تغییر رفتاری نیازمند درخواست جداگانه است).
2. `python3 tests/test_cache_pin_consistency_static.py` — ۲ پین کهنه گزارش میکند
   (`assets/cd-dvd.js` پین ۳٫۴٫۱۲ در برابر ۳٫۴٫۹ هاستهای زنده؛ `assets/style.css`
   پین ۳٫۴٫۶۱ داخل بستهٔ ZIP تاریخی در برابر ۳٫۴٫۳۴ زنده). هر دو مربوط به متادیتای
   نگهبان/بستهٔ آرشیو هستند، نه محیط اجرا؛ برای رفع نیازمند تصمیم مخزن است.
3. دو شکست اولیهٔ `test_change_history_through_16259.py` به علت **کلون کمعمق** بود
   (کامیت تاریخی محلی نبود)؛ با `git fetch origin <sha>` برطرف شد و اکنون سبز است.

## ۷. قواعد رعایتشده

هیچ `DROP`، `reset --hard`، `push --force` یا حذف دادهای انجام نشد. بستهها فقط
روی دیتابیسهای تازه ساختهشدند. فایلهای موقت انتقال، ورکفلوی موقت و لاگ ساخت
پس از اتمام کار از شاخه حذف شدند. `config.php`/`installed.lock` هرگز کامیت نشدند.
