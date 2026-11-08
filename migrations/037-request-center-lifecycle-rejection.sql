-- Request Center Phase-3 lifecycle: explicit pre-service rejection.
-- MariaDB 10.4 compatible and safe to replay.
-- Scope: tickets.status and tickets.rejected_at only; no shared role or other-module changes.

SET @rc_status_type = (
    SELECT COLUMN_TYPE
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tickets'
      AND COLUMN_NAME = 'status'
);
SET @rc_status_default = (
    SELECT COLUMN_DEFAULT
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tickets'
      AND COLUMN_NAME = 'status'
);
SET @rc_status_ddl = CASE
    WHEN @rc_status_type IS NULL THEN 'SELECT 1'
    WHEN LOCATE('''rejected''', @rc_status_type) > 0 THEN 'SELECT 1'
    WHEN LOWER(@rc_status_type) LIKE 'enum(%' THEN CONCAT(
        'ALTER TABLE `tickets` MODIFY COLUMN `status` ',
        LEFT(@rc_status_type, CHAR_LENGTH(@rc_status_type) - 1),
        ',''rejected'') NOT NULL DEFAULT ',
        QUOTE(TRIM(BOTH CHAR(39) FROM COALESCE(@rc_status_default, 'manager_review')))
    )
    ELSE 'SELECT 1'
END;
PREPARE rc_status_stmt FROM @rc_status_ddl;
EXECUTE rc_status_stmt;
DEALLOCATE PREPARE rc_status_stmt;

SET @rc_rejected_at_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tickets'
      AND COLUMN_NAME = 'rejected_at'
);
SET @rc_rejected_at_ddl = IF(
    @rc_rejected_at_exists > 0,
    'SELECT 1',
    'ALTER TABLE `tickets` ADD COLUMN `rejected_at` DATETIME NULL AFTER `closed_at`'
);
PREPARE rc_rejected_at_stmt FROM @rc_rejected_at_ddl;
EXECUTE rc_rejected_at_stmt;
DEALLOCATE PREPARE rc_rejected_at_stmt;
