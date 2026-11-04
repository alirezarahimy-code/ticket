-- هم‌بستگی سراسری رخدادها در درخواست‌های وب، عملیات و Workerها.
-- هر ستون/شاخص مستقل و قابل اجرای مجدد است.

SET @t = 'system_logs';
SET @c = 'trace_id';
SET @x = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@t AND COLUMN_NAME=@c);
SET @q = IF(@x=0, 'ALTER TABLE system_logs ADD COLUMN trace_id CHAR(36) NULL AFTER request_id', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;
SET @c = 'operation_id';
SET @x = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@t AND COLUMN_NAME=@c);
SET @q = IF(@x=0, 'ALTER TABLE system_logs ADD COLUMN operation_id CHAR(36) NULL AFTER trace_id', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;
SET @c = 'parent_event_id';
SET @x = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@t AND COLUMN_NAME=@c);
SET @q = IF(@x=0, 'ALTER TABLE system_logs ADD COLUMN parent_event_id CHAR(36) NULL AFTER operation_id', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;

SET @t = 'activity_logs';
SET @c = 'trace_id';
SET @x = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@t AND COLUMN_NAME=@c);
SET @q = IF(@x=0, 'ALTER TABLE activity_logs ADD COLUMN trace_id CHAR(36) NULL AFTER request_id', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;
SET @c = 'operation_id';
SET @x = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@t AND COLUMN_NAME=@c);
SET @q = IF(@x=0, 'ALTER TABLE activity_logs ADD COLUMN operation_id CHAR(36) NULL AFTER trace_id', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;
SET @c = 'parent_event_id';
SET @x = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@t AND COLUMN_NAME=@c);
SET @q = IF(@x=0, 'ALTER TABLE activity_logs ADD COLUMN parent_event_id CHAR(36) NULL AFTER operation_id', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;

SET @t = 'ticket_events';
SET @c = 'trace_id';
SET @x = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@t AND COLUMN_NAME=@c);
SET @q = IF(@x=0, 'ALTER TABLE ticket_events ADD COLUMN trace_id CHAR(36) NULL AFTER request_id', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;
SET @c = 'operation_id';
SET @x = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@t AND COLUMN_NAME=@c);
SET @q = IF(@x=0, 'ALTER TABLE ticket_events ADD COLUMN operation_id CHAR(36) NULL AFTER trace_id', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;
SET @c = 'parent_event_id';
SET @x = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=@t AND COLUMN_NAME=@c);
SET @q = IF(@x=0, 'ALTER TABLE ticket_events ADD COLUMN parent_event_id CHAR(36) NULL AFTER operation_id', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;

SET @x = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='system_logs' AND INDEX_NAME='idx_system_logs_trace');
SET @q = IF(@x=0, 'ALTER TABLE system_logs ADD INDEX idx_system_logs_trace (trace_id, operation_id)', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;
SET @x = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='activity_logs' AND INDEX_NAME='idx_activity_trace');
SET @q = IF(@x=0, 'ALTER TABLE activity_logs ADD INDEX idx_activity_trace (trace_id, operation_id)', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;
SET @x = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ticket_events' AND INDEX_NAME='idx_ticket_events_trace');
SET @q = IF(@x=0, 'ALTER TABLE ticket_events ADD INDEX idx_ticket_events_trace (trace_id, operation_id)', 'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;

UPDATE system_logs SET trace_id=request_id, operation_id=request_id WHERE trace_id IS NULL AND request_id IS NOT NULL;
UPDATE activity_logs SET trace_id=request_id, operation_id=request_id WHERE trace_id IS NULL AND request_id IS NOT NULL;
UPDATE ticket_events SET trace_id=request_id, operation_id=request_id WHERE trace_id IS NULL AND request_id IS NOT NULL;
