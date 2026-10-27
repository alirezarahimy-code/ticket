# نسخهٔ ۱.۶۲.۳۶ — همگام‌سازی رکوردهای دستی قبلی

این بستهٔ افزایشی از نسخهٔ ۱.۶۲.۳۵، فایل‌های مهاجرت `011`، `012` و اصلاح نهایی `013` را در مسیر `migrations/` داخل ZIP دارد؛ updater آن‌ها را به ترتیب manifest اجرا می‌کند. فرم شناسنامه و استخراج شبکه‌ای تغییر نمی‌کند.

**حداقل نسخه:** ۱.۶۲.۳۵

**اصلاح تعیین‌کننده:** `013-manual-attendance-record-backfill.sql`؛ idempotent و قابل اجرای مجدد.

## چه داده‌ای اصلاح می‌شود؟

- هر فیش صادرشدهٔ دستی با `source_uid=manual-attendance` و `event_type=printed`، مستقل از `print_status`، به `delivery_status=delivered` تغییر می‌کند و زمان تحویل می‌گیرد. اگر چاپ فیزیکی هنوز در صف باشد، وضعیت صف چاپ حفظ می‌شود تا Worker آن را پردازش کند.
- رویدادهای فیش چاپ‌شدهٔ کارکنان نیز با قاعدهٔ تحویل هماهنگ می‌شوند.
- رکورد غیبت همان روز برای ثبت دستی تردد (`printed` یا `no_food`) و نیز برای رویداد چاپ‌شدهٔ کارکنان حذف می‌شود.
- reconciliation هنگام خواندن فهرست ثبت دستی/غیبت و گزارش تحویل در بازهٔ انتخابی اجرا می‌شود؛ گزارش، فیش دستی تحویل‌شده را حتی با وضعیت چاپ فیزیکی غیرنهایی می‌شمارد.

## راستی‌آزمایی روی پایگاه‌داده

پس از نصب، همان تاریخ را در فهرست غیبت یا گزارش تحویل بازخوانی کنید؛ هر دو مسیر reconciliation را اجرا می‌کنند. این شمارنده‌ها باید صفر شوند:

```sql
SELECT COUNT(*) AS manual_tickets_not_delivered
FROM food_ticket_events
WHERE source_uid = 'manual-attendance'
  AND event_type = 'printed'
  AND (delivery_status IS NULL OR delivery_status <> 'delivered');

SELECT COUNT(*) AS contradictory_absences
FROM food_ticket_daily_absence a
JOIN food_ticket_events e
  ON e.user_id = a.user_id AND e.punch_date = a.absence_date
WHERE (e.source_uid = 'manual-attendance' AND e.event_type IN ('printed', 'no_food'))
   OR (e.event_type = 'printed' AND e.print_status = 'printed');
```

اگر شمارنده‌ها پس از نصب و بازخوانی صفر نشدند، نسخهٔ نصب‌شده، نتیجهٔ اجرای هر سه migration و دسترسی نوشتن پایگاه‌داده را بررسی کنید.

این بسته کد و migration را آماده می‌کند؛ به پایگاه‌دادهٔ سرور زنده دسترسی ندارم و ادعا نمی‌کنم رکوردهای تولید از این‌جا تغییر کرده‌اند. اگر نسخهٔ سرور پایین‌تر از ۱.۶۲.۳۵ است، این بسته را نصب نکنید.
