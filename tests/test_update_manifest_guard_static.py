from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_update_installer_checks_zip_open_and_extraction_results():
    source = (ROOT / "system-update.php").read_text(encoding="utf-8")
    assert "system_update_validate_zip_entries($zip)" in source
    assert "$zip->open($zipPath) !== true" in source
    assert "!$zip->extractTo($stage)" in source
    assert "system_update_verify_staged_package($stage, $manifest)" in source


def test_update_installer_rejects_traversal_duplicates_and_symlinks():
    source = (ROOT / "system-update.php").read_text(encoding="utf-8")
    assert "system_update_archive_path_is_safe" in source
    assert "$segment === '..'" in source
    assert "ناسازگار با Windows" in source
    assert "لینک نمادین" in source
    assert "512 * 1024 * 1024" in source


def test_manifest_file_list_and_optional_sha256_are_verified():
    source = (ROOT / "system-update.php").read_text(encoding="utf-8")
    assert "$manifest['files']" in source
    assert "$manifest['sha256']" in source
    assert "hash_file('sha256'" in source


def test_cumulative_update_contains_security_scope_runtime_overlays():
    source = (ROOT / "tools/build_cumulative_update.py").read_text(encoding="utf-8")
    for required in (
        "actions/cd-dvd-actions.php",
        "actions/notification-actions.php",
        "lib/auth-scope.php",
        "actions/ticket-actions.php",
        "tools/Run-Logging-Acceptance-1.62.83.ps1",
    ):
        assert f'"{required}"' in source



def test_cumulative_update_includes_all_logging_migrations_from_024_through_030():
    source = (ROOT / "tools/build_cumulative_update.py").read_text(encoding="utf-8")
    required = (
        "migrations/024-central-logging.sql",
        "migrations/025-logging-issues-alerts.sql",
        "migrations/026-logging-legacy-result-unknown.sql",
        "migrations/027-logging-correlation-ids.sql",
        "migrations/028-logging-fingerprint-v2.sql",
        "migrations/029-activity-result-default-unknown.sql",
        "migrations/030-logging-notification-idempotency-and-ticket-history.sql",
    )
    start = source.index("OVERLAY_MIGRATIONS = (")
    end = source.index("\n)", start)
    overlay = source[start:end]
    missing = [migration for migration in required if f'"{migration}"' not in overlay]
    assert not missing, f"Cumulative update omits required Logging migrations: {missing}"


def test_cumulative_package_title_matches_release_version():
    source = (ROOT / "tools/build_cumulative_update.py").read_text(encoding="utf-8")
    assert 'manifest["title"] = "سخت‌سازی نصب‌کننده، Logging و بازیابی (1.62.77 ← 1.62.83)"' in source
