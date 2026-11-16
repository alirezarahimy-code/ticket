# نسخهٔ ۱.۶۲.۹۷ — کارت آزمون جامع و اصلاح هشدار مهاجرت

## آنچه در این نسخه درست شد

### ۱) هشدار کاذب «مهاجرت ۰۳۳ اجرا نشده»
`migrations/033-logging-legacy-context-collation.sql` در فهرست baseline اسکیما
ثبت نشده بود. نتیجه: هر نصب تازه بلافاصله پس از نصب، آژیر «مهاجرت اجرا نشده»
می‌داد. حالا در `schema.sql` و `install-empty-db.sql` ثبت شده است.

پیش از این اصلاح، `escalation_count`، ایندکس `idx_system_logs_level_time` و
عرض `escalation_label VARCHAR(64)` هم در baseline جا افتاده بود؛ هر سه اضافه شدند.

### ۲) کارت «آزمون جامع ورکر، چاپ، مدیریت سامانه»
در صفحهٔ تنظیمات سیستم ← اتصال پایگاه‌ها، انتهای صفحه. سه دکمه:
- **اجرای آزمون** — وضعیت worker، پوشهٔ اسپول، چاپگر و دسترسی مدیریت را می‌سنجد
- **کپی نتیجه** — خروجی را برای ارسال به پشتیبانی کپی می‌کند
- **بازخوانی** — بدون تازه‌سازی صفحه دوباره اجرا می‌کند

نتیجه همان‌جا، در همان کارت، نمایش داده می‌شود.

### ۳) ابزارهای تشخیصی
- `tools/explain_alarms.php` — هر آژیر سلامت را تفسیر می‌کند: `OK` (طبیعی)،
  `REAL` (ایراد واقعی)، `WARN` (بستگی به تصمیم شما)
- `tools/apply_migration_033.php` — ثبت مهاجرت ۰۳۳؛ `--dry-run` پیش‌فرض است
- `tools/prewindow_check.php` — بررسی پیش‌نیازهای ویندوز پیش از نصب
- `tools/verify_1_62_97.php` — تأیید نصب صحیح

## نصب

### از صفر
`ticket-full-1.62.97-20261116.zip` را روی پوشهٔ وب باز کنید.

### از ۱.۶۲.۹۶
`ticket-update-1.62.96-to-1.62.97-...zip` را روی نصب فعلی باز کنید.

سپس:
```
php tools\verify_1_62_97.php
php tools\explain_alarms.php
php tools\apply_migration_033.php --dry-run
php tools\apply_migration_033.php --apply
```

### پوشه‌های لازم
```
mkdir storage\food_ticket_spool
mkdir storage\food_ticket_prints
```

## بررسی سالم بودن فایل

```
Get-FileHash .\ticket-full-1.62.97-20261116.zip -Algorithm SHA256
```

| فایل | SHA-256 |
|---|---|
| `ticket-full-1.62.97-20261116.zip` | `55780637f038e881fa8bd5c6271bc71018717768f3bf98186f1da6d53f973206` |
| `ticket-update-1.62.96-to-1.62.97-food-worker-selftest-panel-20261115.zip` | `3b11639fde5d12a9a3a5b06175030a26769923dc19e1b35e7e337e21eb897c1d` |
| `ticket-update-1_62_96-to-1_63-request-center-rc-candidate-20261114-r16.zip` | `9f576e084ac8fb45e17951650127241c61e72aed363258fee83026142f7d6c7d` |

## محدودیت شناخته‌شده

تب «تغییرات نسخه‌ها» در راهنما و پاپ‌آپ داشبورد **در ۱.۶۲ فعال نمی‌شوند** —
این دو به `index.php` نیاز دارند که فقط در بستهٔ ۱.۶۳ (r16) عوض شده است.
در ۱.۶۲ تنها بنر «تغییرات» در پنل غذا فعال است (با سقف دو نمایش).
