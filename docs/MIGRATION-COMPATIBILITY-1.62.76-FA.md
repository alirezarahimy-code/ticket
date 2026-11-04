# سازگاری Migrationهای Logging — نسخه 1.62.76

## مبنا

این بسته Update فقط برای نصب دقیقاً نسخه `1.62.75` ساخته شده است. در Full Source نسخه 1.62.75 و 1.62.76، هفت فایل Migration زیر وجود دارند و محتوای آن‌ها در دو نسخه یکسان است:

| فایل | نقش |
|---|---|
| `024-central-logging.sql` | ایجاد/تکمیل هسته ثبت رخدادهای System، Audit و Ticket |
| `025-logging-issues-alerts.sql` | جداول تجمیع Issueها، اعلان سلامت و شاخص‌های مرتبط |
| `026-logging-legacy-result-unknown.sql` | اصلاح محافظه‌کارانه نتیجه رخدادهای قدیمی و جداول تاریخچه/اعلان Issue |
| `027-logging-correlation-ids.sql` | افزودن Trace/Operation/Parent Event ID و شاخص‌های هم‌بستگی |
| `028-logging-fingerprint-v2.sql` | ارتقای fingerprint به SHA-256 |
| `029-activity-result-default-unknown.sql` | پیش‌فرض امن `unknown` برای نتیجه Audit |
| `030-logging-notification-idempotency-and-ticket-history.sql` | idempotency اعلان‌ها و محافظت از تاریخچه رخداد Ticket |

## رفتار بسته Update

- هر هفت فایل SQL در ZIP زیر `files/migrations/` قرار گرفته‌اند و در `manifest.files` فهرست شده‌اند؛ بنابراین Updater آن‌ها را به پوشه `migrations/` نصب‌شده کپی می‌کند.
- این فایل‌ها در `manifest.migrations` قرار نگرفته‌اند؛ بنابراین این Update آن‌ها را اجرا نمی‌کند.
- `manifest.migrations` عمداً `[]` است: نسخه 1.62.76 تغییر Schema جدیدی ندارد و اجرای مجدد Migrationهای نسخه‌های قبل در ارتقای 1.62.75 به 1.62.76 مجاز نیست.
- وجود فایل SQL روی دیسک به‌تنهایی اثبات اجرای آن روی دیتابیس نیست. وضعیت اجرا باید از جدول `schema_updates` و وضعیت واقعی Schema بررسی شود.
- Migration `025` متعلق به ارتقای 1.62.74 به 1.62.75 بوده و در بسته Update آن نسخه به‌عنوان Migration اجرایی تعریف شده است. اگر این بسته قبلاً با موفقیت اجرا نشده یا سابقه `schema_updates` ناقص است، پیش از هر اقدام باید روی Clone/Staging بررسی شود؛ این بسته 1.62.76 عمداً آن را خودکار اجرا نمی‌کند.

## مهارت‌ها و کنترل‌های پروژه که بازبینی شدند

- `AGENTS.md`: گردشکار هفت‌مرحله‌ای، backup قبل از SQL، عدم اجرای مخرب بدون مجوز، و الزام staging/rollback.
- `docs/SKILL-MANIFEST-FA.md`: مهارت `C01` (Migration امن و idempotent)، `C02` (Version Drift)، `C03` (قرارداد Manifest)، `C05` (Logging/Observability) و `C06` (دروازه‌های تست/CI).
- `tests/test_skill_manifest_static.py`: آزمون ساختار 31 مهارت و پیوند AGENTS/Manifest.

در بسته اصلاح‌شده، `AGENTS.md`، `docs/SKILL-MANIFEST-FA.md` و `tests/test_skill_manifest_static.py` نیز به‌صورت فایل‌های عیناً یکسان با Base در Update گنجانده شده‌اند؛ محتوای آن‌ها در Full Source 1.62.75 و 1.62.76 byte-identical است و صرفاً نسخه مرجع مهارت‌ها را حفظ می‌کند.

## پیش‌پرواز دیتابیس

پیش از ارتقای محیط مقصد، روی Clone/Staging این پرس‌وجوی فقط‌خواندنی اجرا و خروجی آن نگهداری شود:

```sql
SELECT version, migration, applied_at
FROM schema_updates
WHERE migration IN (
  '024-central-logging.sql',
  '025-logging-issues-alerts.sql',
  '026-logging-legacy-result-unknown.sql',
  '027-logging-correlation-ids.sql',
  '028-logging-fingerprint-v2.sql',
  '029-activity-result-default-unknown.sql',
  '030-logging-notification-idempotency-and-ticket-history.sql'
)
ORDER BY version, migration;
```

این گزارش فقط سابقه ثبت‌شده در Updater را نشان می‌دهد و جای بررسی Schema را نمی‌گیرد. هیچ SQL را مستقیماً روی Production اجرا نکنید. اگر رکوردی غایب است، ابتدا ستون‌ها، شاخص‌ها، FKها و داده‌ها را در Clone تطبیق دهید و برنامه جبرانی جداگانه تهیه کنید.
