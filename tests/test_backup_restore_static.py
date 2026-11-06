from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_destructive_restore_requires_primary_admin_and_pre_restore_backup():
    source = (ROOT / "actions/backup-actions.php").read_text(encoding="utf-8")
    start = source.index("if ($action === 'restore_backup')")
    end = source.index("if ($action === 'system_update')", start)
    block = source[start:end]
    assert block.index("require_primary_admin()") < block.index("restore_sql_dump(")
    assert "require_permission('backup.manage')" in block
    assert block.index("create_backup_bundle()") < block.index("restore_sql_dump(")
    assert block.index("restore_sql_dump(") < block.index("restore_upload_archive(")


def test_upload_restore_preflights_paths_sizes_collisions_and_symlinks():
    source = (ROOT / "backup.php").read_text(encoding="utf-8")
    assert "backup_validate_upload_archive($archive)" in source
    assert "getExternalAttributesIndex" in source
    assert "0120000" in source
    assert "strtolower(rtrim($relative, '/'))" in source
    assert "512 * 1024 * 1024" not in source  # restore has separate configurable ceilings
    assert "ITSM_MAX_RESTORE_UNPACKED_MB" in source
    assert "is_link($target)" in source
    assert "realpath($directory)" in source
    assert "stream_copy_to_stream" in source


def test_backup_download_path_is_confined_to_backup_root():
    source = (ROOT / "backup.php").read_text(encoding="utf-8")
    block = source[source.index("function backup_download_path"):source.index("function backup_upload_archive_entry")]
    assert "realpath(backup_root_path())" in block
    assert "realpath($root . DIRECTORY_SEPARATOR . $relative)" in block
    assert "str_starts_with($path, $prefix)" in block
