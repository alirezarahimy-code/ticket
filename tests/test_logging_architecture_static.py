from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_central_core_is_loaded_before_legacy_logging_wrappers():
    bootstrap = (ROOT / 'bootstrap.php').read_text(encoding='utf-8')
    assert "require_once __DIR__ . '/lib/logging-core.php';" in bootstrap
    core = bootstrap.index("require_once __DIR__ . '/lib/logging-core.php';")
    legacy = bootstrap.index("require __DIR__ . '/lib/logging-csrf.php';")
    assert core < legacy


def test_database_migration_adds_correlation_to_all_event_streams():
    migration = (ROOT / 'migrations/024-central-logging.sql').read_text(encoding='utf-8')
    for table in ['system_logs', 'activity_logs', 'ticket_events']:
        assert f'ALTER TABLE {table}' in migration
        assert 'ADD COLUMN event_id CHAR(36)' in migration
        assert 'ADD COLUMN request_id CHAR(36)' in migration


def test_spool_worker_is_cli_only_and_replay_is_idempotent():
    worker = (ROOT / 'cron/replay_logging_spool.php').read_text(encoding='utf-8')
    assert worker.index("if (PHP_SAPI !== 'cli')") < worker.index("require dirname(__DIR__) . '/bootstrap.php';")
    assert 'flock($lock, LOCK_EX | LOCK_NB)' in worker
    replay = (ROOT / 'lib/logging-replay.php').read_text(encoding='utf-8')
    core = (ROOT / 'lib/logging-core.php').read_text(encoding='utf-8')
    assert 'logging_replay_insert' in core
    assert 'logging_replay_process_directory' in replay
    assert "/lib/logging-replay.php" in worker
    assert 'INSERT IGNORE' not in worker
    assert '$maxPerRun = 500' in worker


def test_request_timeline_combines_system_activity_and_ticket_events():
    health = (ROOT / 'system-health.php').read_text(encoding='utf-8')
    start = health.index('function system_health_request_timeline')
    end = health.index('function system_health_render', start)
    timeline = health[start:end]
    for table in ['system_logs', 'activity_logs', 'ticket_events']:
        assert f'FROM {table} WHERE request_id = ?' in timeline


def test_untrusted_forwarded_headers_are_not_used_as_client_ip():
    activity = (ROOT / 'lib/activity-audit.php').read_text(encoding='utf-8')
    start = activity.index('function activity_client_ip')
    end = activity.index('function activity_computer_name', start)
    resolver = activity[start:end]
    assert "$_SERVER['REMOTE_ADDR']" in resolver
    assert "$_SERVER['HTTP_X_FORWARDED_FOR']" in resolver
    assert 'app.trusted_proxies' in resolver
    assert "$_SERVER['HTTP_CLIENT_IP']" not in resolver
