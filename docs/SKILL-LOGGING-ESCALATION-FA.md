# سند مهارت — ارتقاء سیستم لاگینگ (1.62.88 → 1.62.89)

**نسخه:** 1.62.89
**تاریخ:** 2026-11-07
**نوع تغییر:** ارتقاء لاگینگ — تشدید شدت + خلاصهٔ هوشمند

> این سند رفتار **واقعی و تست‌شدهٔ کد** را توضیح می‌دهد. در نسخهٔ پیشین، جدول آستانه‌ها با کد هم‌خوان نبود؛ اصلاح شد.

---

## خلاصه

دو قابلیت جدید به سیستم لاگینگ اضافه شد:

1. **تشدید شدت (Severity Escalation)** — هر خطا/هشدار تکراری، شدتش بالاتر می‌رود
2. **خلاصهٔ هوشمند (Smart Summary)** — کارت جمع‌بندی بالای داشبورد لاگ

---

## ۱. تشدید شدت (Severity Escalation)

### نحوهٔ کار
وقتی `system_log()` فراخوانی می‌شود (فقط برای سطح‌های `error`، `critical`، `warning`):

1. اثر انگشت (`fingerprint`) رویداد محاسبه می‌شود.
2. تعداد رویدادهای **قبلی** با همان fingerprint در **۱ ساعت گذشته** شمرده می‌شود (`SELECT COUNT(*) FROM system_logs WHERE fingerprint = ? AND created_at >= NOW() - INTERVAL 1 HOUR`).
   - شمارش روی ایندکس موجود `idx_system_logs_fingerprint_time` اجرا می‌شود.
   - اگر دیتابیس در دسترس نباشد، شمارش صفر در نظر گرفته و ثبت لاگ ادامه می‌یابد.
3. **برچسب** طبق آستانه‌ها تعیین می‌شود؛ **ترفیع سطح** طبق دو قاعدهٔ زیر انجام می‌شود.

### برچسب نمایشی (بر اساس شمارهٔ تکرارِ قبل از رویداد جاری)

| شمارهٔ تکرار قبلی | برچسب |
|---|---|
| ≥۱ | مشکل جزئی |
| ≥۳ | مشکل تکراری — نیاز به بررسی |
| ≥۱۰ | بحرانی — احتمالاً سرویس قطع شده |
| ≥۵۰ | فاجعه — بررسی فوری لازم است |

> برچسب «فاجعه» فقط یک برچسب است؛ **هیچ alert خودکاری ارسال نمی‌شود.**

### ترفیع واقعیِ سطح (آنچه در `system_logs.level` و خط app.log می‌نشیند)

| سطح اولیه | شرط | سطح ذخیره‌شده |
|---|---|---|
| `warning` | شمار ≥ ۱۰ | `error` |
| `error` | شمار ≥ ۵۰ | `critical` |
| سایر | — | بدون تغییر |

نکات:
- شمارش «پیش از» رویداد جاری انجام می‌شود؛ سطح ذخیره‌شده برای ردیف‌های قدیمی‌تر retroactively عوض نمی‌شود.
- آستانهٔ ۳ و ۱۰ فقط برای **برچسب** استفاده می‌شوند؛ سطح را عوض نمی‌کنند.
- `escalation_count` ذخیره‌شده در ردیف، همان شمارِ قبل از رویداد است.

### ذخیره در دیتابیس
دو ستون جدید در `system_logs`:
- `escalation_count` (INT UNSIGNED, NOT NULL, DEFAULT 0) — شمارِ تکرارهای قبلی در پنجرهٔ ۱ ساعت
- `escalation_label` (VARCHAR(64), NULL) — برچسب فارسی

هر دو ستون در `meta_json` نیز برای مشاهدهٔ راحت‌تر ذخیره می‌شوند.

### نمایش در app.log
کنار هر لاگ، برچسب و شمارش نمایش داده می‌شود:
```
[2026-11-07T04:20:00+03:30] [error] db — دیتابیس قطع شد [مشکل تکراری — نیاز به بررسی ×3]
```

### فایل‌های مرتبط
- `lib/logging-escalation.php` — تابع‌های `logging_escalation_count`، `logging_escalation_is_critical`، `logging_smart_summary`
- `lib/logging-csrf.php` — `system_log`: شمارش + تعیین برچسب + ترفیع سطح (با گارد `function_exists`)
- `lib/logging-core.php` — `logging_store_system`: INSERT با دو ستون جدید
- `bootstrap.php` — بارگذاری **اختیاری و گارد‌شده** `lib/logging-escalation.php` (وجود فایل الزامی نیست)
- `migrations/034-logging-escalation.sql` — مهاجرت دیتابیس

---

## ۲. خلاصهٔ هوشمند (Smart Summary)

### رفتار
بالای داشبورد لاگ (`logging-dashboard.php`) یک کارت نمایش داده می‌شود که آخرین ۶۰ دقیقه را خلاصه می‌کند:

```
📊 خلاصهٔ هوشمند — ۶۰ دقیقهٔ اخیر — ۱۰ رخداد مهم
  ❌ ۳ بار — SYS-DB-001 — db
  ❌ ۱ بار — SYS-APP-001 — test
  ⚠️ ۲ بار — SYS-AUTH-001 — auth
```

نحوهٔ محاسبه: `GROUP BY fingerprint, code, context, level` فقط برای `level IN ('error','warning','critical')`، مرتب بر اساس تکرار (حداکثر ۲۰ گروه). عدد «تعداد رخداد مهم» برابر جمع `count(*)` گروه‌هاست.

### رنگ کارت
رنگ بر اساس **تعدادِ گروه‌های خطا** (نه تعداد کل رخدادها):
- **سبز** — بدون گروه خطا
- **زرد** — ۱ تا ۹ گروه خطا
- **قرمز** — ۱۰ گروه خطای یکتای به بالا

### فایل‌های مرتبط
- `lib/logging-escalation.php` — تابع `logging_smart_summary()`
- `logging-dashboard.php` — داده + نمایش (خروجی‌ها با `e()` escape می‌شوند)
- `assets/logging-dashboard.css` — استایل `.ld-smart-*`

---

## ۳. مهاجرت دیتابیس

**فایل:** `migrations/034-logging-escalation.sql`
**نوع:** افزایشی و idempotent — قابل اجرای مجدد (گارد `information_schema`)

| عمل | جزئیات |
|---|---|
| `ADD COLUMN` | `escalation_count INT UNSIGNED NOT NULL DEFAULT 0` (بعد از `meta_json`) |
| `ADD COLUMN` | `escalation_label VARCHAR(64) NULL` (بعد از `escalation_count`) |
| `ADD INDEX` | `idx_system_logs_level_time (level, created_at)` |

نکتهٔ ایندکس: کوئری شمارش از ایندکس موجود `idx_system_logs_fingerprint_time (fingerprint, created_at)` استفاده می‌کند (از ۱.۶۲.۲۴) و به ایندکس جدید نیازی ندارد؛ ایندکس جدید فقط برای گزارش «سطح×زمان» مفید است.

### نصب از طریق پنل «به‌روزرسانی و پشتیبان‌گیری»
خودکار و به‌ترتیب درست انجام می‌شود: **ابتدا مهاجرت‌ها (به‌ترتیب manifest + ثبت schema_updates + فقط یک‌بار)، سپس کپی فایل‌ها**. نیازی به اجرای دستی نیست.

### نصب دستی (فقط اگر پنل قابل استفاده نیست)
```bash
mysql -u <user> -p <database> < migrations/034-logging-escalation.sql
# سپس فایل‌های files/ را روی ریشهٔ سامانه کپی و VERSION را 1.62.89 کنید
```
⚠️ **ترتیب را رعایت کنید: اول مهاجرت، بعد فایل‌ها.** اگر فایل‌ها قبل از مهاجرت روی سامانه بشینند، INSERTهای `system_logs` با «Unknown column» شکست می‌خورند و سامانه به حالت degrade (فقط spool/file) می‌رود — بدون کرش، ولی بدون لاگ پایدار در دیتابیس تا اجرای مهاجرت.

---

## ۴. چه چیزهایی از قبل داشتیم (و نیازی به تغییر نبود)

| قابلیت | کجا |
|---|---|
| تشخیص ریشهٔ خطا (۴۰+ الگو) | `lib/log-catalog.php` → `system_error_explain()` |
| نمای زنجیره‌ای (Timeline) | `assets/logging-dashboard.js` |
| تشخیص ناهنجاری (Baseline) | `lib/logging-health-baseline.php` |
| گزارش ایمیلی روزانه | `cron/log_notify.php` |
| Spool + Emergency | `lib/logging-core.php` |
| ممیزی سلامت | `lib/logging-audit.php` |
| بازپخش Spool | `lib/logging-replay.php` |
| فعالیت کاربر | `lib/activity-audit.php` |

---

## ۵. فهرست کامل تغییرات

### فایل جدید
```
+ lib/logging-escalation.php            (تشدید شدت + خلاصهٔ هوشمند)
+ migrations/034-logging-escalation.sql  (مهاجرت دیتابیس)
+ CHANGE-REPORT-FA-1.62.89.md            (گزارش انتشار)
+ docs/SKILL-LOGGING-ESCALATION-FA.md    (این سند)
```

### فایل تغییر‌داده‌شده
```
~ bootstrap.php                          (بارگذاری اختیاری و گارد‌شدهٔ logging-escalation.php)
~ lib/logging-csrf.php                   (system_log: شمارش + برچسب + ترفیع سطح)
~ lib/logging-core.php                   (INSERT با escalation_count/label)
~ logging-dashboard.php                  (+smart_summary به داده و نمایش)
~ assets/logging-dashboard.css           (+استایل کارت خلاصه)
~ VERSION                                (1.62.88 → 1.62.89)
```
