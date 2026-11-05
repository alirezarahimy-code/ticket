from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BUILDER = (ROOT / "tools/build_update_1_62_88.py").read_text(encoding="utf-8")
REPORT = (ROOT / "downloads/CHANGE-REPORT-FA-1.62.88.md").read_text(encoding="utf-8")


def test_builder_requires_exact_base_and_versions():
    assert 'BASE_COMMIT = "b5711e79d5f5dc6d73f3a248a80c7a51d609c1c5"' in BUILDER
    assert 'RELEASE_VERSION = "1.62.88"' in BUILDER
    assert 'REQUIRES_VERSION = "1.62.82"' in BUILDER
    assert 'git("cat-file", "-e", f"{BASE_COMMIT}^{{commit}}")' in BUILDER


def test_manifest_and_archive_are_checked_bidirectionally():
    for marker in (
        "z.testzip()",
        "file_entries != set(got[\"files\"])",
        "migration_entries != set(got[\"migrations\"])",
        "set(hashes) != set(got[\"files\"])",
        "sha256(z.read(\"files/\" + relative))",
        "safe_archive_path",
    ):
        assert marker in BUILDER


def test_checksum_is_created_from_closed_final_archive():
    assert "with zipfile.ZipFile(temporary" in BUILDER
    assert "checksum = sha256(package_path.read_bytes())" in BUILDER
    assert "recorded != sha256(package_path.read_bytes())" in BUILDER


def test_032_is_excluded_from_executable_migrations():
    assert '"032-schema-updates-baseline.sql" in migration_entries' in BUILDER
    assert "032" in REPORT
    assert "baseline" in REPORT.lower()


def test_report_does_not_claim_all_migrations_are_additive():
    assert "صرفاً افزایشی‌اند" not in REPORT
    assert "داده‌های موجود را به‌روزرسانی می‌کنند" in REPORT
