# مسیر اجرای آزمون‌های محیطی Logging — نسخه 1.62.76

## هدف

این نسخه بر پایه 1.62.75 است و Migration جدید ندارد. برای اثبات سازگاری تراکنش و ACL باید Integration دیتابیس و آزمون Windows روی محیط‌های موقت اجرا شوند؛ هیچ‌یک روی Production اجرا نمی‌شوند.

## مسیر A — MySQL/MariaDB واقعی و موقت

پیش‌نیاز: Docker Engine یا Docker Desktop، Docker Compose v2، PHP CLI با `pdo_mysql`.

از ریشه Full Source اجرا کنید:

```bash
bash tests/logging-acceptance/run-mysql-integration.sh
```

اسکریپت فقط MariaDB 10.11 را با دیتابیس اختصاصی `itsm_logging_test` و پورت محلی 33306 اجرا می‌کند. داده‌ها در tmpfs قرار می‌گیرند و پس از پایان آزمون Container و Volumeها حذف می‌شوند. تست Integration جدول‌های Logging همین دیتابیس اختصاصی را حذف و دوباره ایجاد می‌کند؛ به دیتابیس عملیاتی متصل نشوید.

تست Integration باید علاوه بر سناریوهای موجود، موارد زیر را در MySQL/InnoDB واقعی اثبات کند:

- درج Audit موفق در تراکنش باز و حذف همان ردیف با Rollback.
- در Circuit Open، عدم ایجاد Spool مستقل برای Audit موفق تراکنش‌دار و بازگشت خطا به caller.
- حفظ مسیر Spool مستقل برای failure/denied.
- درج و فیلتر فراداده و ستون‌های Module/Target.

## مسیر B — Windows و NTFS ACL

Workflow فایل `.github/workflows/logging-acceptance.yml` یک Windows Runner واقعی می‌سازد. تست `tests/test_logging_acl_windows.ps1` فقط ACEهای صریح و کنترل‌شده را می‌سنجد؛ جایگزین ارزیابی Effective Access حساب واقعی IIS AppPool، گروه‌های تو در تو، Deny ACEهای به‌ارث‌رسیده و Task Scheduler روی سرور مقصد نیست.

## اجرای CI

فایل `.github/workflows/logging-acceptance.yml` دو Job مستقل دارد:

- `linux-mariadb-integration`: PHP lint، تست‌های PHP آفلاین، Integration واقعی MariaDB و تست‌های Python قرارداد Logging.
- `windows-acl-and-runtime`: PHP lint، تست‌های آفلاین و ACL واقعی NTFS روی Windows Runner.

Workflow از مسیر Actions → Logging 1.62.76 Acceptance → Run workflow قابل اجراست.

## حدود پذیرش

سبزشدن Jobها فقط آزمون DB و ACL کنترل‌شده را تأیید می‌کند. برای `STAGING ACCEPTED` باید Update با Updater واقعی روی Clone نسخه 1.62.75، IIS identity، Worker/Task Scheduler، Restore و Rollback روی محیط مقصد اجرا و مستند شود. هیچ Job یا اسکریپتی Production را تغییر نمی‌دهد.
