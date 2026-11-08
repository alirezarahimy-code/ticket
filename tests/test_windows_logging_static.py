from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_windows_logging_acceptance_assets_exist():
    required = [
        "tests/run_logging_staging_checks.ps1",
        "tests/test_logging_acl_windows.ps1",
        "tools/verify_logging_acl.ps1",
        "tools/set_logging_acl.ps1",
        "tools/verify_logging_web_protection.ps1",
        "tools/Run-Logging-Acceptance-1.62.83.ps1",
        "tools/Run-Logging-Acceptance.ps1",
    ]
    missing = [name for name in required if not (ROOT / name).is_file()]
    assert not missing, f"Missing Windows Logging acceptance assets: {missing}"



def test_current_acceptance_script_reads_source_version_and_checks_034():
    source = (ROOT / "tools/Run-Logging-Acceptance.ps1").read_text(encoding="utf-8")
    assert "$ExpectedVersion = (Get-Content -LiteralPath $sourceVersionFile -Raw).Trim()" in source
    assert "migrations/034-logging-escalation.sql" in source
    assert "Get-FileHash" in source
    assert "-Strict -ReturnStatus -ScanFiles" in source
    assert "Set-Acl" not in source

def test_acl_verifier_is_read_only_and_strict_mode_is_available():
    source = (ROOT / "tools/verify_logging_acl.ps1").read_text(encoding="utf-8")
    assert "Get-Acl" in source
    assert "Set-Acl" not in source
    assert "[switch]$Strict" in source
    assert "S-1-1-0" in source


def test_acl_verifier_detects_interactive_and_guest_principals():
    source = (ROOT / "tools/verify_logging_acl.ps1").read_text(encoding="utf-8")
    for sid in ("S-1-5-32-546", "S-1-5-4", "S-1-5-2", "S-1-5-14"):
        assert sid in source


def test_web_protection_verifier_preserves_application_subpath():
    source = (ROOT / "tools/verify_logging_web_protection.ps1").read_text(encoding="utf-8")
    assert "$base.AbsoluteUri.TrimEnd('/') + '/'" in source
    assert "$base.Query" in source
    assert "[Uri]::new($base, $path)" in source


def test_acl_setter_uses_least_privilege_for_service_account():
    source = (ROOT / "tools/set_logging_acl.ps1").read_text(encoding="utf-8")
    assert "Rights = [System.Security.AccessControl.FileSystemRights]::Modify" in source
    assert "Rights = [System.Security.AccessControl.FileSystemRights]::FullControl" in source
    assert "Set-Acl" in source


def test_acl_setter_handles_script_whatif_without_null_pscmdlet():
    source = (ROOT / "tools/set_logging_acl.ps1").read_text(encoding="utf-8")
    assert "$WhatIfPreference" in source
    assert "ShouldProcess(" not in source
    assert "if (-not $Apply)" in source


def test_installed_acceptance_script_is_version_pinned_and_read_only():
    source = (ROOT / "tools/Run-Logging-Acceptance-1.62.83.ps1").read_text(encoding="utf-8")
    assert "$ExpectedVersion = '1.62.83'" in source
    assert "Get-FileHash" in source
    assert "-Strict -ReturnStatus -ScanFiles" in source
    assert "Set-Acl" not in source


def test_installed_acceptance_checks_complete_logging_migration_chain():
    source = (ROOT / "tools/Run-Logging-Acceptance-1.62.83.ps1").read_text(encoding="utf-8")
    required = (
        "migrations/024-central-logging.sql",
        "migrations/025-logging-issues-alerts.sql",
        "migrations/026-logging-legacy-result-unknown.sql",
        "migrations/027-logging-correlation-ids.sql",
        "migrations/028-logging-fingerprint-v2.sql",
        "migrations/029-activity-result-default-unknown.sql",
        "migrations/030-logging-notification-idempotency-and-ticket-history.sql",
        "migrations/031-food-worker-window.sql",
        "migrations/032-schema-updates-baseline.sql",
        "migrations/033-logging-legacy-context-collation.sql",
    )
    missing = [migration for migration in required if f"'{migration}'" not in source]
    assert not missing, f"Installed acceptance preflight omits migrations: {missing}"
