-- Backfill historical meal tickets and reconcile conflicting absence rows.
-- An issued manual ticket was physically handed to the employee, even when an
-- older release left print_status='not_printed'. Delivery is independent of the
-- printer queue; a pending physical print stays pending for the worker.
ALTER TABLE food_ticket_events
    ADD COLUMN IF NOT EXISTS delivery_status ENUM('pending','delivered') NOT NULL DEFAULT 'pending' AFTER print_status;
ALTER TABLE food_ticket_events
    ADD COLUMN IF NOT EXISTS delivered_at DATETIME NULL AFTER delivery_status;
ALTER TABLE food_ticket_events
    ADD COLUMN IF NOT EXISTS delivered_by INT UNSIGNED NULL AFTER delivered_at;

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

UPDATE food_ticket_events
SET print_status = CASE
        WHEN source_uid = 'manual-attendance'
         AND event_type = 'printed'
         AND COALESCE(print_status, '') NOT IN ('pending', 'printing')
        THEN 'printed'
        ELSE print_status
    END,
    delivered_by = CASE WHEN delivery_status IS NULL OR delivery_status <> 'delivered' THEN NULL ELSE delivered_by END,
    delivery_status = 'delivered',
    delivered_at = COALESCE(delivered_at, NOW())
WHERE user_id IS NOT NULL
  AND (
        (source_uid = 'manual-attendance'
         AND event_type = 'printed')
        OR (event_type = 'printed' AND print_status = 'printed')
      )
  AND (
        delivery_status IS NULL
        OR delivery_status <> 'delivered'
        OR (source_uid = 'manual-attendance'
            AND event_type = 'printed'
            AND COALESCE(print_status, '') NOT IN ('printed', 'pending', 'printing'))
      );

DELETE a
FROM food_ticket_daily_absence AS a
INNER JOIN food_ticket_events AS e
    ON e.user_id = a.user_id
   AND e.punch_date = a.absence_date
WHERE e.user_id IS NOT NULL
  AND (
        (e.source_uid = 'manual-attendance' AND e.event_type IN ('printed', 'no_food'))
        OR (e.event_type = 'printed' AND e.print_status = 'printed')
      );
