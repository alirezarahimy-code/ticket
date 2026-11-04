from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_ci_provisions_disposable_mariadb_and_windows_runners():
    workflow = (ROOT / ".github/workflows/logging-acceptance.yml").read_text(encoding="utf-8")
    assert "mariadb:10.11" in workflow
    assert "itsm_logging_test" in workflow
    assert "ITSM_TEST_ALLOW_DESTRUCTIVE: 'YES'" in workflow
    assert "runs-on: windows-latest" in workflow
    assert "test_logging_acl_windows.ps1" in workflow


def test_local_database_harness_is_ephemeral_and_cleans_up():
    compose = (ROOT / "tests/logging-acceptance/compose.yaml").read_text(encoding="utf-8")
    runner = (ROOT / "tests/logging-acceptance/run-mysql-integration.sh").read_text(encoding="utf-8")
    assert "127.0.0.1:33306:3306" in compose
    assert "tmpfs:" in compose and "/var/lib/mysql" in compose
    assert "trap cleanup EXIT INT TERM" in runner
    assert "down --volumes --remove-orphans" in runner
    assert "itsm_logging_test" in runner


def test_windows_acl_harness_has_positive_negative_and_restore_controls():
    source = (ROOT / "tests/test_logging_acl_windows.ps1").read_text(encoding="utf-8")
    assert "Everyone" in source or "S-1-1-0" in source
    assert "expected exit 2" in source
    assert "controlled baseline" in source
    assert "restored ACL" in source
    assert "finally" in source and "Remove-Item" in source


def test_acceptance_docs_do_not_equate_ci_with_target_staging_acceptance():
    doc = (ROOT / "docs/LOGGING-ACCEPTANCE-ENVIRONMENTS-1.62.76-FA.md").read_text(encoding="utf-8")
    assert "جایگزین ارزیابی Effective Access" in doc
    assert "جایگزین پذیرش Staging" in doc or "STAGING ACCEPTED" in doc
    assert "Production" in doc
