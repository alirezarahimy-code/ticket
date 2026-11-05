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
