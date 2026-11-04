from pathlib import Path
ROOT = Path(__file__).resolve().parents[1]
def test_diagnostics_engine_is_loaded_after_logging_core():
    s=(ROOT/'bootstrap.php').read_text(encoding='utf-8')
    assert s.index("lib/logging-core.php") < s.index("lib/logging-diagnostics.php")
def test_diagnostic_engine_has_stable_rules_and_confidence():
    s=(ROOT/'lib/logging-diagnostics.php').read_text(encoding='utf-8')
    for code in ['SYS-DB-001','SYS-PHP-001','SYS-PERF-001','INT-LDAP-001','FOOD-PRT-003','SEC-AUTH-001','SEC-ACL-001']:
        assert code in s
    assert "'confidence'" in s and 'logging_diagnostic_similarity' in s

def test_agent_bundle_is_cli_only_and_redacted():
    s=(ROOT/'cron/export_logging_agent_bundle.php').read_text(encoding='utf-8')
    assert "PHP_SAPI !== 'cli'" in s
    assert 'logging_diagnostic_redact_event' in s
    assert 'ZipArchive' in s and "0700" in s and "0600" in s
    assert 'passwords' in s and 'absolute server paths' in s

def test_release_preflight_is_read_only_and_reports_dependencies():
    s=(ROOT/'cron/verify_logging_release.php').read_text(encoding='utf-8')
    assert "SELECT 1" in s and 'pdo_mysql' in s and 'logging-worker-status.json' in s
    assert 'logging-release-preflight.json' in s
    assert 'DROP TABLE' not in s.upper() and 'DELETE FROM' not in s.upper()

def test_health_dashboard_displays_diagnostic_advice():
    s=(ROOT/'system-health.php').read_text(encoding='utf-8')
    assert 'logging_diagnostic_analyze' in s
    assert 'logging_diagnostic_similarity' in s
    assert 'شواهد و مراحل بررسی تشخیصی' in s
