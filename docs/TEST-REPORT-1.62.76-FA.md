# گزارش آزمون Logging — نسخه 1.62.76

## آزمون‌های هدفمند Logging

- Python Logging/static: 95 passed.
- آزمون PHP جدید `tests/test_logging_audit_required_transaction.php`: 7/7 passed.
- از ۲۱ فایل `tests/test_logging_*.php`، تعداد ۲۰ فایل PASS و یک فایل MySQL Integration به‌دلیل نبود DSN و `pdo_mysql` با وضعیت SKIP خارج شد؛ هیچ‌کدام FAIL نشدند.
- PHP lint برای ۲۰۹ فایل PHP خارج از آرشیوهای تاریخی `updates/`، `vendor/`، `backup/` و `release-archive/` برابر ۲۰۹/۲۰۹ PASS است.

## سناریوهای جدید پوشش‌داده‌شده

1. درج Audit موفق روی تراکنش باز و عدم ثبت قطعی پیش از Commit.
2. حذف Audit موفق همراه Rollback کسب‌وکار.
3. جلوگیری از Spool مستقل Audit موفق در Circuit Open و تراکنش فعال.
4. طبقه‌بندی CAB، LDAP و Profile.
5. حفظ فراداده CD/DVD و پالایش مقدار کلید حساس در داده تو در تو.
6. تکمیل Module و Target برای Governance و CD/DVD.
7. قراردادهای ایستای Holiday، فیلتر مدیریتی و جست‌وجو/صفحه‌بندی Ticket؛ کنترل استفاده از `db_commit()` محافظت‌شده در هر چهار مسیر Holiday.

## محدودیت‌ها

- MySQL/MariaDB Integration واقعی: NOT RUN؛ PHP این محیط `pdo_mysql` ندارد و Docker/MariaDB در دسترس نیست.
- Windows ACL/NTFS: NOT RUN؛ محیط اجرا Linux است و PowerShell/Windows Runner در دسترس نیست.
- Staging مقصد، IIS، Task Scheduler، Backup/Restore و Rollback عملیاتی: NOT RUN.
- آزمون‌های static و fake-PDO، اثبات Integration واقعی InnoDB نیستند.

## آزمون کل مخزن

- کل Python suite: 430 passed، 54 failed، 10 skipped و 18 subtests passed.
- شکست‌های کل مخزن در آزمون‌های Logging نیستند؛ 95 آزمون Python Logging/static همگی PASS هستند. 54 شکست عمدتاً به بسته‌های ZIP تاریخی و صفحات دانلودی اشاره دارند که در Full Source ورودی وجود ندارند؛ چند assertion قدیمی نیز با وضعیت فعلی سورس تجمعی هم‌خوان نیستند. این موارد در محدوده اصلاحات Logging 1.62.76 نیستند و عمداً حذف یا پنهان نشده‌اند.
