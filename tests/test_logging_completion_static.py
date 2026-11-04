from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def read(path):
    return (ROOT / path).read_text(encoding='utf-8')

def test_catalog_contains_user_admin_and_technical_guidance():
    catalog = read('lib/log-catalog.php')
    for field in ('user_message', 'admin_explanation', 'probable_cause', 'recommended_action', 'allowed_context_keys'):
        assert field in catalog
    for code in ('SEC-AUTH-001', 'SEC-AUTH-002', 'SEC-CSRF-001', 'SEC-ACL-001', 'AUD-ACL-001', 'AUD-SET-001', 'SYS-PHP-001', 'SYS-DB-001', 'INT-LDAP-001'):
        assert code in catalog

def test_issue_aggregator_is_integrated_and_idempotent_replay_exists():
    core = read('lib/logging-core.php')
    assert 'function logging_issue_upsert' in core
    assert 'occurrences = occurrences + 1' in core
    assert "status = IF(status = 'resolved', 'open', status)" in core
    assert 'function logging_replay_insert' in core
    assert 'uq_log_issues_fingerprint' in read('migrations/025-logging-issues-alerts.sql')

def test_runtime_logging_does_not_execute_ddl_for_log_tables():
    core = read('lib/logging-csrf.php')
    activity = read('lib/activity-audit.php')
    health = read('system-health.php')
    assert 'CREATE TABLE IF NOT EXISTS system_logs' not in core
    assert 'CREATE TABLE IF NOT EXISTS activity_logs' not in activity
    assert 'CREATE TABLE IF NOT EXISTS system_incidents' not in health

def test_health_dashboard_has_issue_filters_and_health_thresholds():
    health = read('system-health.php')
    for key in ('issue_severity', 'issue_category', 'issue_status', 'issue_days', 'logging-worker-status.json', 'dropped_last_at', 'disk_total_space', 'NOW() - INTERVAL 24 HOUR'):
        assert key in health
    assert 'Issue Management' in health
    assert 'system_health_issue_rows($days)' in health

def test_windows_scheduled_jobs_and_retention_classes_are_documented():
    docs = read('docs/LOGGING-OPERATIONS-FA.md')
    assert 'Windows Server Task Scheduler' in docs
    assert '۱۸۳' in docs and '۹۰' in docs and '۷ روز' in docs
    assert (ROOT / 'cron/run_logging_maintenance.bat').is_file()
    assert (ROOT / 'cron/run_logging_retention.bat').is_file()
    assert (ROOT / 'cron/check_logging_health.php').is_file()

def test_program_version_is_user_requested_value():
    assert read('VERSION').strip() >= '1.62.76'  # نسخه‌های بعدی هم مجازند


def test_alerts_are_rate_limited_and_delivered_to_primary_admins():
    worker = read('cron/deliver_logging_issue_notifications.php')
    cron = read('cron/check_logging_health.php')
    assert 'HAVING COUNT(*) >= 5' in worker
    assert 'INTERVAL 15 MINUTE' in worker
    assert 'floor(time() / 1800)' in worker
    assert "role = 'primary_admin'" in worker
    assert 'INTERVAL 30 MINUTE' in cron
    assert "last_state = 'sending'" in cron
    assert 'notify_user' in worker and 'notify_user' in cron

def test_user_facing_error_text_and_fatal_output_are_safe():
    csrf = read('lib/logging-csrf.php')
    assert "ini_set('display_errors', '0')" in csrf
    assert "__logging_user_message" in csrf
    assert 'logging_utf8_substr($safeMessage, 0, 140)' in csrf

def test_replay_is_streamed_and_writer_lock_is_shared():
    replay = read('lib/logging-replay.php')
    core = read('lib/logging-core.php')
    assert 'while (($line = fgets($input)) !== false)' in replay
    assert '.writer.lock' in core
    assert '.processing' in replay
