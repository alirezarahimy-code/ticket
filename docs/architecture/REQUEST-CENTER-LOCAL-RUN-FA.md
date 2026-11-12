# راه‌اندازی و اجرای محلی سامانه (توسعه و تست — نه staging و نه production)

> این راهنما یک **نصب تازه روی دستگاه توسعه** را توصیف می‌کند.
> برای **ارتقای یک سامانهٔ موجود** به نسخهٔ ۱٫۶۳ (مرکز درخواست)، از بستهٔ RC و
> راهنمای `docs/architecture/REQUEST-CENTER-INSTALL-UPGRADE-FA.md` استفاده کنید؛
> هرگز روی محیط عملیاتی اجرا نکنید.

## پیش‌نیازها (Debian 12 / Ubuntu 22.04+)

حداقل ۲ گیگ RAM و دسترسی sudo. همه‌چیز از مخازن رسمی نصب می‌شود:

```bash
sudo apt-get update
sudo apt-get install -y mariadb-server mariadb-client \
  php8.2-cli php8.2-mysql php8.2-sqlite3 php8.2-mbstring php8.2-xml php8.2-zip php8.2-curl
```

> PHP همان نسخه‌ای است که CI پروژه استفاده می‌کند (8.2) و امتدادهای لازم دقیقاً
> همان‌های workflow است: `pdo_mysql, pdo_sqlite, mbstring, xml, zip`.
> `php-ldap` فقط اگر اتصال به Active Directory لازم است.

## روش سریع (اسکریپت آماده)

```bash
bash tools/local-run.sh          # نصب وابستگی‌ها، ساخت دیتابیس و کاربر محلی، راهنمای نهایی
bash tools/local-run.sh --reset  # پاکسازی کامل دیتابیس محلی و ساخت دوباره (تخریبی — فقط با تأیید)
```

سپس:

```bash
cd <ریشهٔ پروژه>
php -S 127.0.0.1:8099 -t .
# مرورگر: http://127.0.0.1:8099/install.php
```

## روش دستی

```bash
# ۱) ساخت دیتابیس و کاربر محلی
sudo service mariadb start
sudo mariadb <<'SQL'
CREATE DATABASE IF NOT EXISTS persian_ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'ticketing'@'localhost' IDENTIFIED BY 'ticketing-local-dev';
CREATE USER IF NOT EXISTS 'ticketing'@'127.0.0.1' IDENTIFIED BY 'ticketing-local-dev';
GRANT ALL PRIVILEGES ON persian_ticketing.* TO 'ticketing'@'localhost';
GRANT ALL PRIVILEGES ON persian_ticketing.* TO 'ticketing'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

# ۲) اجرای وب‌سرور داخلی PHP از ریشهٔ مخزن
php -S 127.0.0.1:8099 -t .
```

## نصب اولیه (install.php)

1. مرورگر را روی `http://127.0.0.1:8099/install.php` باز کنید (فقط از `127.0.0.1` مجاز است).
2. در مرحلهٔ «اتصال دامین» اطلاعات دیتابیس را وارد کنید:
   `host=127.0.0.1` ، `port=3306` ، `name=persian_ticketing` ، `user=ticketing` ، `pass=ticketing-local-dev`
   (یا برای نصب تولیدی، کاربر root با دسترسی محدود — هرگز root با رمز خالی روی ۹۲۲ یا عمومی).
3. در مرحلهٔ «مدیر اولیه» نام کاربری (پیش‌فرض `admin`) و رمز عبور را انتخاب و «ساخت سامانه و ورود» را بزنید.
   - نصاب: دیتابیس را می‌سازد، `schema.sql` را کامل اعمال می‌کند و `config.php` و `installed.lock` را در ریشهٔ پروژه می‌نویسد.
   - `config.php` در `.gitignore` است — آن را هرگز commit نکنید (شامل رمزهای سامانه است).
4. ورود بعدی: `http://127.0.0.1:8099/` — مرکز درخواست: `http://127.0.0.1:8099/request-center.php`

> اگر `config.php` قبلاً ساخته شده باشد، نصب‌گر `403` می‌دهد. برای نصب دوباره،
> `config.php` و `installed.lock` را **بعد از بکاپ** حذف کنید یا دیتابیس را `DROP` کنید
> (اسکریپت `tools/local-run.sh --reset` همین کار را با تأیید صریح انجام می‌دهد).

## ارتقا به ۱٫۶۳ با بستهٔ RC (روی staging — نه این راهنما)

1. از تغییرات فعلی و پایگاه‌دادهٔ staging **بکاپ کامل** بگیرید.
2. بستهٔ `downloads/ticket-update-1_62_95-to-1_63-request-center-rc-candidate-20261112-r6.zip` را
   در ریشهٔ سامانه باز کنید (فایل‌های `files/` عیناً جایگزین می‌شوند + `migrations/035-request-center-appointments.sql`).
3. مهاجرت را از طریق runner رسمی (system-update.php) اعمال کنید:
   `mariadb -u root -p persian_ticketing < migrations/035-request-center-appointments.sql`
   — فقط ۰۳۵ است؛ فایل `rebuild-final.sql` داخل بسته نیست و نباید دستی اجرا شود.
4. سناریوهای پذیرش (طبق `REQUEST-CENTER-INSTALL-UPGRADE-FA.md`):
   دید مدیر واحد صرف فقط چارت (هر دو حوزه)، عدم رؤیت «ارجاع‌شده‌های به‌خودش»،
   اصلاح عنوان/توضیح/اولویت در هر دو وضعیت، ارجاع فقط تا پیش از تخصیص،
   «درخواست لغو» برای زیرمجموعه، ۴۰۳ تقویم برای صرف.

## تست‌ها بعد از اجرا

```bash
# تست‌های ایستای RC (بدون دیتابیس)
python3 -m pytest tests/ -q
# تست‌های SQLite (بدون MariaDB)
php tests/test_request_center_repository_sqlite.php
php tests/test_request_center_workflow_service_sqlite.php
php tests/test_request_center_appointments_sqlite.php
# قرارداد HTTP واقعی (CI همین را اجرا می‌کند)
php tests/test_request_center_phase2_smoke_mariadb.php
```

## عیب‌یابی

| علامت | علت احتمالی | راه‌حل |
|---|---|---|
| صفحهٔ سفید / خطای ۵۰۰ | امتداد PHP کسر است | `php -m \| grep -E 'pdo_mysql\|mbstring\|zip\|xml'` و نصب همان امتداد |
| `Unknown column 'u.org_auto_role'` | شِمای قدیمیِ بدون ستون‌های چارت | کد مرکز درخواست از ۲۰۲۶-۱۱-۱۲ ستون را اختیاری می‌داند؛ فایل `modules/request-center/lib/functions.php` را به‌روز کنید |
| نصاب `403` می‌دهد | `config.php` وجود دارد | بکاپ بگیرید و حذف کنید (یا `--reset`) |
| `SQLSTATE[HY000] [2002]` | MariaDB بالا نیست یا سوکت/پورت اشتباه | `sudo service mariadb start` و چک `127.0.0.1:3306` |
| خطای `Access denied` برای `root@localhost` | احراز سوکت یونیکسی روی Debian | از کاربر محلی `ticketing` استفاده کنید (اسکریپت خودش می‌سازد) |
| خطا در `install.php` هنگام بارگذاری شِما | کاربر DB دسترسی `CREATE` ندارد | نام کاربری را با `GRANT ALL` روی همان DB بسازید |

## نکتهٔ صادقانه دربارهٔ خودکارسازی

در محیط توسعه‌ای که این راهنما از آن بیرون آمده است، امکان نصب PHP/MariaDB از
شبکه فراهم نبود (فقط چند هاست محدود allowlist شده بود)؛ اعتبارسنجی نهایی بستهٔ
۱٫۶۳ روی **CI واقعی GitHub روی همان کامیت** انجام شد: Request Center Contract
روی هر دو MariaDB 10.4.32 و 10.11 سبز است (شامل Phase-2 smoke با HTTP واقعی و
T07)، QA Package Preview سبز و T12 سبز است. این راهنما برای اجرای محلیٔ همان
سناریوها روی دستگاه شماست.
