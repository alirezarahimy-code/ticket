# دامنهٔ بستهٔ Logging 1.62.76 — چه چیزی هست و چه چیزی نیست

این سند توسط فرایند همگام‌سازی ۱.۶۲.۷۵ → ۱.۶۲.۷۶ تولید شده است تا جلوی برداشت اشتباه از دیگر
سندهای این پوشه گرفته شود. برخی از آن سندها بخش‌هایی از برنامهٔ Logging را شرح می‌دهند که
**در بستهٔ خام تحویلی وجود نداشتند** و بنابراین نصب هم نمی‌شوند.

## در این بسته هست

- هستهٔ لاگ‌گیری: `lib/logging-core.php` و کاتالوگ رخداد `lib/log-catalog.php`
- یکپارچه‌سازی ممیزی: `lib/activity-audit.php` و `actions/holiday-actions.php`
- بارگذاری هسته در `bootstrap.php` و رابط گزارش‌ها در `index.php`
- هفت مهاجرت پایگاه‌داده با شماره‌های ۰۲۴ تا ۰۳۰

## در این بسته نیست (در بستهٔ خام هم نبود)

| ماژول غایب | سندی که به آن ارجاع می‌دهد |
|---|---|
| `lib/logging-replay.php` | `LOGGING-OPERATIONS-FA.md` |
| `logging-dashboard.php` (داشبورد مانیتورینگ) | — (فقط `index.php` آن را صدا می‌زد) |
| `cron/check_logging_health.php` | `LOGGING-OPERATIONS-FA.md` |
| `cron/deliver_logging_issue_notifications.php` | `LOGGING-OPERATIONS-FA.md` |
| `cron/export_logging_integrity.php` | `LOGGING-OPERATIONS-FA.md` |
| `cron/run_logging_maintenance.bat` و `cron/run_logging_retention.bat` | `LOGGING-OPERATIONS-FA.md` |
| `tests/run_logging_staging_checks.ps1`، `tests/test_logging_acl_windows.ps1` | هارنس پذیرش |
| `tests/logging-acceptance/run-mysql-integration.sh` | هارنس پذیرش |

به همین دلیل این اقلام از بستهٔ نهایی حذف شدند تا پیوسته قرمز نباشند:

- `tests/test_logging_completion_static.py`، `tests/test_logging_phase73_static.py`،
  `tests/test_logging_phase74_package.py`، `tests/test_logging_acceptance_harness_static.py`
  (همگی ماژول‌های غایب بالا را می‌آزمایند)
- `tests/test_logging_mysql_integration.php` (به `lib/logging-replay.php` وابسته است)
- `.github/workflows/logging-acceptance.yml` (گردش‌کار CI برنامهٔ Logging؛ به اسکریپت‌های غایب
  و شماره‌های قدیمی مهاجرت ارجاع می‌دهد)

تست‌هایی که در بسته ماندند و سبز هستند: `tests/test_logging_phase76_static.py` (۸ آزمون) و
`tests/test_migration_retention_1_62_76.py` (۲ آزمون)، به‌علاوهٔ `tests/test_logging_audit_required_transaction.php`
که فقط به ماژول‌های موجود وابسته است.

اگر داشبورد مانیتورینگ یا کارهای زمان‌بندی‌شدهٔ نگهداشت لاگ را لازم دارید، باید در یک بستهٔ
تکمیلی جداگانه تحویل شوند.
