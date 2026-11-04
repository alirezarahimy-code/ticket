from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_spool_stream_is_allowlisted_and_partial_writes_are_completed():
    core = (ROOT / "lib/logging-core.php").read_text(encoding="utf-8")
    start = core.index("function logging_spool_write")
    end = core.index("function logging_spool_stats", start)
    writer = core[start:end]
    assert "in_array($stream, ['system', 'activity', 'ticket_event'], true)" in writer
    assert "while ($writtenTotal < $length)" in writer
    assert "ftruncate($handle, $offset)" in writer
    assert "!fflush($handle)" in writer


def test_activity_normalization_keeps_safe_values_and_ip_validation():
    core = (ROOT / "lib/logging-core.php").read_text(encoding="utf-8")
    activity = (ROOT / "lib/activity-audit.php").read_text(encoding="utf-8")
    assert "function logging_normalize_activity_event(array $event): array" in core
    assert "filter_var($ip, FILTER_VALIDATE_IP)" in core
    assert "in_array($result, ['success', 'failure', 'denied', 'unknown'], true)" in core
    start = activity.index("function activity_log(array $data)")
    end = activity.index("function activity_log_view", start)
    logger = activity[start:end]
    assert "$ip = activity_client_ip();" in logger
    assert "$data['ip_address']" not in logger


def test_logging_transaction_contracts_are_preserved():
    core = (ROOT / "lib/logging-core.php").read_text(encoding="utf-8")
    start_system = core.index("function logging_store_system")
    start_activity = core.index("function logging_normalize_activity_event")
    system_store = core[start_system:start_activity]
    start_activity_store = core.index("function logging_store_activity")
    end_activity_store = core.index("function log_event", start_activity_store)
    activity_store = core[start_activity_store:end_activity_store]
    assert "$pdo->inTransaction()" in system_store
    assert "logging_record_failure('system', $event)" in system_store
    assert "if ($transactional)" in activity_store
    assert "throw $exception;" in activity_store


def test_retention_and_migration_remain_conservative_and_rerunnable():
    csrf = (ROOT / "lib/logging-csrf.php").read_text(encoding="utf-8")
    migration = (ROOT / "migrations/024-central-logging.sql").read_text(encoding="utf-8")
    assert "function system_logs_prune(int $days = 90)" in csrf
    assert "INTERVAL 183 DAY" in csrf and "INTERVAL 7 DAY" in csrf and "INTERVAL 30 DAY" in csrf and "INTERVAL 60 DAY" in csrf
    assert "information_schema.COLUMNS" in migration
    assert "information_schema.STATISTICS" in migration


def test_unknown_error_title_has_mbstring_fallback():
    csrf = (ROOT / "lib/logging-csrf.php").read_text(encoding="utf-8")
    assert "logging_utf8_substr($safeMessage, 0, 140)" in csrf


def test_full_rebuild_drops_every_schema_table():
    schema = (ROOT / "schema.sql").read_text(encoding="utf-8")
    rebuild = (ROOT / "rebuild-final.sql").read_text(encoding="utf-8")
    import re
    tables = set(re.findall(r"CREATE TABLE IF NOT EXISTS\s+([a-z][a-z0-9_]*)", schema, re.I))
    drop_match = re.search(r"DROP TABLE IF EXISTS(.*?);", rebuild, re.S | re.I)
    assert drop_match is not None
    dropped = set(re.findall(r"^\s*([a-z][a-z0-9_]*)\s*,?\s*$", drop_match.group(1), re.M | re.I))
    assert tables <= dropped, f"Tables not dropped by full rebuild: {sorted(tables - dropped)}"
