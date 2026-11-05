from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_windows_logging_acceptance_assets_exist():
    required = [
        "tests/run_logging_staging_checks.ps1",
        "tests/test_logging_acl_windows.ps1",
        "tools/verify_logging_acl.ps1",
        "tools/set_logging_acl.ps1",
        "tools/verify_logging_web_protection.ps1",
    ]
    missing = [name for name in required if not (ROOT / name).is_file()]
    assert not missing, f"Missing Windows Logging acceptance assets: {missing}"


def test_acl_verifier_is_read_only_and_strict_mode_is_available():
    source = (ROOT / "tools/verify_logging_acl.ps1").read_text(encoding="utf-8")
    assert "Get-Acl" in source
    assert "Set-Acl" not in source
    assert "[switch]$Strict" in source
    assert "S-1-1-0" in source
