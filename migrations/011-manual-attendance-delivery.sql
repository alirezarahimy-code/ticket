-- فیش دستیِ قبلاً صادرشده و ثبت‌شده بدون چاپ فیزیکی نیز طبق سیاست سامانه تحویل محسوب می‌شود.
-- تنها رویدادهای دستی‌ای که به وضعیت چاپ‌شده رسیده‌اند backfill می‌شوند؛ چاپ‌های فیزیکیِ در صف دست‌نخورده می‌مانند.
UPDATE food_ticket_events
SET delivery_status = 'delivered',
    delivered_at = COALESCE(delivered_at, NOW()),
    delivered_by = NULL
WHERE source_uid = 'manual-attendance'
  AND event_type = 'printed'
  AND print_status = 'printed'
  AND (delivery_status IS NULL OR delivery_status <> 'delivered');
