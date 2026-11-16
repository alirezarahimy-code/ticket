# راهنمای نصب، ارتقاء و بازگشت — مرکز درخواست (Request Center)

**نسخهٔ ماژول:** ۱.۶۳ (کاندید)  **آخرین به‌روزرسانی:** ۱۴۰۵/۰۸/۱۸ (2026-11-09)
**وضعیت سند:** معتبر برای بستهٔ کاندید موجود در `downloads/ticket-update-1.62.90-to-1.63-request-center-rc-candidate-20261109.zip` (SHA-256: `52257379aaa0ae58cd14444fdad8e8fe3c3a84ff1f5f2492a55713f05347d67b`).

> این راهنما جایگزین تصمیم‌های حاکمیتی (R6، T07، copy قانونی، تگ انتشار) نیست.
> تمام نصب‌ها روی سامانهٔ عملیاتی فقط پس از تأیید مالک و در یک پنجرهٔ نگهداری مشخص انجام می‌شود.

## ۱) دامنه و پیش‌نیازها

- PHP 8.2 با `pdo_mysql`، `pdo_sqlite`، `mbstring`، `xml`، `zip` (نصب SQLite برای محیط‌های آزمایشی)
- MariaDB 10.4+ / 10.11 (در CI هر دو بررسی می‌شوند)، `utf8mb4`، timezone معادل `Asia/Tehran` در اپ
- دسترسی `mariadb-dump`/`mysqldump` و `mariadb`/`mysql` برای بکاپ و اعمال مهاجرت
- گواهی `request-center.php` در دسترس وب (مجوز اجرای PHP مثل `index.php`)
- نسخهٔ پایهٔ هدف باید **۱.۶۲.۹۰** (موجودی `main`، کامیت `d30d7df`) باشد؛ مهاجرت لاگینگ `034` دارایی انتشار **۱.۶۲.۸۹** است و طبق اعلام مالک روی سامانه نصب شده؛ این بسته به هیچ‌وجه `034` را اجرا نمی‌کند.

## ۲) اصل ایمنی پیش از هر تغییری

همهٔ مراحل با این سه قانون شروع می‌شوند؛ هیچ‌کدام را رد نکنید:

1. بکاپ کامل دیتابیس و فایل کد مقصد را بگیرید و **بازآوری آن را روی یک دیتابیس/مسیر disposable آزمایشی سبز کنید.**
2. روی نسخهٔ نصب‌شده `cat VERSION` بزنید و مطمئن شوید `1.62.90` (یا همان نسخه‌ای که manifest می‌خواهد) است.
3. هیچ فایلی را دستی کپی نکنید؛ فقط بستهٔ رسمی (`system-update.php` یا overlay) و فقط از PR/کامیت ثبت‌شده استفاده کنید.

## ۳) نصب روی نسخهٔ تازه (Fresh Install)

1. سورس را از تگ/شاخهٔ تأییدشدهٔ مالک چیده کنید.
2. دیتابیس را با `install-empty-db.sql` بسازید (این مسیر شامل `request_center_appointments` نیز هست — قابلیت تکرار دارد).
3. `config.sample.php` را به `config.php` کپی و مقداردهی کنید (هر config محلی، هرگز در git ثبت نمی‌شود).
4. بررسی نصب:
   - `SHOW TABLES LIKE 'request_center_appointments';` → ۱ ردیف
   - `SHOW TABLES LIKE 'holidays';` → ۱ ردیف
   - `php -l request-center.php` و راهیابی صفحات `request-center.php` برای هر ۴ نقش با یک حساب تست.
5. دادهٔ اولیه را با بستهٔ `install.php` ادی‌گپ/پنل عادی انجام دهید؛ هیچ seed مصنوعی در این ماژول تصمیم گرفته نشده است.

## ۴) ارتقاء از ۱.۶۲.۹۰ به ۱.۶۳ (مسیر پیش‌فرض)

### مرحلهٔ ۱ — بکاپ (قبل از هر چیزی)
```bash
mariadb-dump --single-transaction --quick --routines <dbname> | gzip > rc-upgrade-backup-$(date +%F).sql.gz
git -C <target> log --oneline -1   # باید کامیت دقیق ۱.۶۲.۹۰ را نشان دهد
```

### مرحلهٔ ۲ — اعمال بستهٔ ارتقا
دو مسیر، هر دو معتبر؛ یکی را انتخاب کنید:

- **مسیر رسمی (پیشنهادی):** از شمارهٔ کاربر Admin → «به‌روزرسانی سامانه» → بارگذاری `…rc-candidate-20261109.zip`.
  `system-update.php` به‌صورت خودکار ایمن‌زدایی مسیرها، بررسی `requires_version >= 1.62.90`، اعمال مهاجرت `035` (در صورت عدم ثبت در `schema_updates`) و ثبت نسخهٔ جدید را انجام می‌دهد. ساختار ZIP: `manifest.json` + `migrations/035-request-center-appointments.sql` + `files/…`.
  **هشدار «۰۳۵ ثبت‌شدهٔ قدیمی»:** نسخهٔ فعلی `035-request-center-appointments.sql` یک فایل **ادغام‌شده** است (محتوای پیشین `037-request-center-lifecycle-rejection.sql` عیناً داخل آن است). ثبت مهاجرت در `schema_updates` فقط با «نام فایل» انجام می‌شود؛ اگر هدفِ شما قبلاً نسخهٔ قدیمی ۰۳۵ را اجرا/ثبت کرده باشد، `system-update.php` کل فایل ادغام‌شده را **رد می‌کند** و ستون‌های lifecycle (مثل `tickets.rejected_at`) هرگز ساخته نمی‌شوند و همهٔ صفحات RC با 503 متوقف می‌شوند. در این حالت **باید یک‌بار** فایل ادغام‌شده را دستی اعمال کنید:
  ```bash
  mariadb <dbname> < migrations/035-request-center-appointments.sql   # idempotent؛ تکرار امن است
  # یا: DELETE FROM schema_updates WHERE migration = '035-request-center-appointments.sql';  -- سپس بارگذاری مجدد بسته
  ```
  برای یک ۱.۶۲.۹۰ تازه که هرگز RC نصب نکرده، همین مسیر عادی کافی است.
- **مسیر دستی (فقط برای کارشناسان ارشد):**
  ```bash
  cd <target>
  git fetch origin && git checkout <release-commit>   # تگ/کامیت تأییدشدهٔ مالک، نه کاریهٔ محلی
  mariadb <dbname> < migrations/035-request-center-appointments.sql   # امن برای تکرار
  INSERT IGNORE INTO schema_updates (version, migration) VALUES ('1.63','035-request-center-appointments.sql');
  ```

### مرحلهٔ ۳ — بررسی پس از نصب (Regression smoke)
1. **ورود و خروج** با سه نقش (requester، agent، supervisor) و یک حساب primary_admin.
2. **چهار Workspace:** از منوی ماژول، هر Workspace بارگذاری شود؛ تعویض Workspace توسط کاربر چندنقشی کار کند و ناوبری با مجوزهای جدید نوسازی شود.
3. **ثبت یک درخواست واقعی-تست** (مثلاً «تست ارتقای مرکز درخواست») → تایید شود و `id` بازگردد.
4. **انتقال وضعیت**: ثبت → ارجاع → در انتظار کارشناس → بستن — و در هر مرحله تاریخچه (`activity_log`) رشد کند.
5. **تقویم شمسی**: باز کردن Workspace کارشناس → صفحهٔ تقویم → ثبت یک نوبت آزمایشی روی درخواست تست → لغو آن؛ شاهد `request_center_appointments` (۲ ردیف audit) و بروزرسانی SLA/OLA باشد.
6. **خروجی‌ها:** گزارش Excel از خروجی همان صفحه (مسیر `action=export_report`) باید ۲۰۰ و فایل قابل بازکردن بدهد.
7. **اعلان‌ها:** دست‌کم یک نوتیفیکیشن ناشی از نوبت به assignee رسیده باشد (جدول `notifications`) با كلیدهای `dedup_key` یکتا.
8. **بدون خطای PHP:** لاگ سطحِ هر Workspace خالی از `Fatal`/`Uncaught` باشد.

در صورت شکست هرکدام از ۲ تا ۷: **همان‌جا متوقف شوید و به بخش ۶ (بازگشت) بروید.**

## ۵) چک‌لیست پیش‌نیاز امنیتی

- [ ] CSRF روی همهٔ فرم‌ها و رجیستری RC سبز (`lib/http-post.php` الزامی است)
- [ ] دسترسی مستقیم به `modules/request-center/*` بدون bootstrap → ۴۰۴
- [ ] `Pages/*.php` بدون مجوز → ۴۰۳/۴۰۴ (تست ایستای `tests/test_request_center_access_matrix_static.py` در CI قفل شده)
- [ ] `detail.php` برای درخواست خارج از حوزهٔ کاربر → ۴۰۴ (نه «وجود دارد ولی ممنوع»)
- [ ] پیوست‌ها فقط از مسیرِ تأییدشدهٔ upload/redownload؛ هیچ مسیر ارجاعی فایل به کاربر نمی‌رود
- [ ] لاگ‌های حساس (رمز، توکن) در `activity_log`/`system_logs` هرگز ذخیره نمی‌شوند

## ۶) بازگشت (Rollback)

**سطح داده (قابل‌برگشت، بهتر):**
```bash
gunzip -c rc-upgrade-backup-YYYY-MM-DD.sql.gz | mariadb <dbname>   # روی هدف (یا clone آن) بازآوری کامل
```
**سطح کد (سریع):**
```bash
git -C <target> checkout <previous-release-tag-or-commit> && <redeploy/restart>
```
نکته‌های عملی:
- مهاجرت ۰۳۵ ادغام‌شده علاوه‌بر `request_center_appointments`، enumِ `tickets.status` را با `rejected` کامل می‌کند، `rejected_at`، `ticket_number_seq`، `tickets.ticket_no` و یکتایی آن، `categories.code` و بذرهای دسته‌بندی و `service_catalog.category_id` (+FK در صورت نبود orphan) را نیز آماده می‌کند؛ بازگشت کد بدون restore هم سامانه را خراب نمی‌کند (ستون/ENUM افزوده برگشت‌ناپذیر نیستند ولی بی‌اثر می‌مانند) ولی **برای بهره‌برداری پیش‌فرض restore کامل + checkout قبلی است.**
- اگر نوبت‌های واقعی روی نسخهٔ جدید ثبت شده‌اند، آن‌ها را قبل از restore استخراج (`SELECT * …` یا خروجی اکسل) کنید؛restore آن‌ها را حذف می‌کند.
- پس از rollback، همان smoke بخش ۴-مطابق نسخهٔ قبلی را تکرار کنید تا ثِبات تأیید شود.

## ۷) موارد صریحاً بر عهدهٔ مالک (خارج از این راهنما)

- خروج PR #30 از Draft، merge به `main`، انتخاب تگ انتشار (نام انتشار طبق تصمیم مالک **۱.۶۳** است؛ VERSION شاخه ۱.۶۳، جلوتر از main ۱.۶۲.۹۰) و تگ انتشار.
- تصمیم T07/D1 و هرگونه کژتویی سیاست Logging (D3).
- بارگذاری بسته در محیط عملیاتی، برنامهٔ نگهداری، ارتباط با بهره‌برداران.
- دریافت امضای پذیرش (پذیرش کاربران نمایندهٔ نقش‌ها) پس از نصب.

## ۸) پیوست: شواهد ساخت بستهٔ کاندید

- مسیر: `downloads/ticket-update-1.62.90-to-1.63-request-center-rc-candidate-20261109.zip`
- SHA-256: `52257379aaa0ae58cd14444fdad8e8fe3c3a84ff1f5f2492a55713f05347d67b` (فایل کناری `.sha256` در همان پوشه)
- اجزاء: `manifest.json` با `release_status=RC_CANDIDATE_NOT_FOR_PRODUCTION`، `migrations/035-request-center-appointments.sql` (واحد، ادغام‌شده؛ فایل ۰۳۷ جدا وجود ندارد)، VERSION (۱.۶۳) و مستندات معماری
- توجه: `034` (Logging) و `036` (غذا) عمداً بیرون بسته‌اند.
- ساخت و اعتبارسنجی لوکال: `python3 tools/build_rc_update.py` (CRC، مسیرهای امن، یکتایی مسیرها، تطبیق بایت‌به‌بایت manifest و sha256 هر فایل)

