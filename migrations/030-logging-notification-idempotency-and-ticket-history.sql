-- Logging hardening 1.62.75: idempotent notification insert and immutable ticket event history.
-- Safe to re-run. Existing notifications remain unchanged.
SET @has_idempotency_column = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND COLUMN_NAME = 'idempotency_key'
);
SET @ddl = IF(@has_idempotency_column = 0,
  'ALTER TABLE notifications ADD COLUMN idempotency_key CHAR(64) NULL AFTER read_at',
  'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idempotency_index = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND INDEX_NAME = 'uq_notifications_idempotency'
);
SET @ddl = IF(@has_idempotency_index = 0,
  'ALTER TABLE notifications ADD UNIQUE KEY uq_notifications_idempotency (idempotency_key)',
  'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Preserve ticket event audit rows when a ticket is deleted. RESTRICT is deliberate:
-- a ticket with audit history cannot be hard-deleted until an approved archive workflow exists.
SET @delete_rule = (
  SELECT DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_events' AND CONSTRAINT_NAME = 'fk_events_ticket'
  LIMIT 1
);
SET @ddl = IF(@delete_rule IS NOT NULL AND @delete_rule <> 'RESTRICT',
  'ALTER TABLE ticket_events DROP FOREIGN KEY fk_events_ticket',
  'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET @has_fk = (
  SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_events' AND CONSTRAINT_NAME = 'fk_events_ticket'
);
SET @ddl = IF(@has_fk = 0,
  'ALTER TABLE ticket_events ADD CONSTRAINT fk_events_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE RESTRICT',
  'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
