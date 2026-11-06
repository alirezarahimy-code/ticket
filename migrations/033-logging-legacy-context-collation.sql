-- Re-run the legacy issue-context backfill for databases where migration 026
-- was already recorded before its collation-safe JOIN was introduced.
-- This forward migration is intentionally separate: edited historical migrations
-- are not re-executed by existing installations.
UPDATE log_issues i
JOIN system_logs s ON BINARY s.event_id = BINARY i.last_event_id
SET i.context = s.context
WHERE i.context = 'system';
