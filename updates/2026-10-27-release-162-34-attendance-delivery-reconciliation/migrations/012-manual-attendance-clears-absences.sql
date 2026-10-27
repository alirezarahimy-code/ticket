-- یک ثبت دستیِ تردد (با فیش یا بدون فیش) یعنی فرد حاضر بوده است.
-- تضمین می‌کنیم جدول غیبت برای نصب‌های قدیمی هم موجود باشد، سپس رکوردهای متناقض
-- غیبت را برای ترددهای دستیِ ثبت‌شده حذف می‌کنیم. اجرای دوباره بی‌خطر است.
CREATE TABLE IF NOT EXISTS food_ticket_daily_absence (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    absence_date DATE NOT NULL,
    group_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NOT NULL,
    reason VARCHAR(300) NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_food_absence_day_user (absence_date, user_id),
    INDEX idx_food_absence_group_day (group_id, absence_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE absence
FROM food_ticket_daily_absence AS absence
INNER JOIN food_ticket_events AS event
    ON event.user_id = absence.user_id
   AND event.punch_date = absence.absence_date
WHERE event.source_uid = 'manual-attendance'
  AND event.user_id IS NOT NULL
  AND event.event_type IN ('printed', 'no_food');
