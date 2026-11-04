from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def read(path):
    return (ROOT / path).read_text(encoding="utf-8")

def test_full_source_package_is_complete_and_versioned():
    assert read("VERSION").strip() >= "1.62.76"  # نسخه‌های بعدی هم مجازند
    for rel in (
        "README.md", "system-update.php", "lib/logging-core.php",
        "lib/logging-replay.php", "cron/export_logging_integrity.php",
        "lib/activity-audit.php", "tests/run_logging_staging_checks.ps1",
        "docs/CHANGE-REPORT-1.62.76-FA.md", "docs/TEST-REPORT-1.62.76-FA.md",
        "docs/STAGING-ACCEPTANCE-REPORT-1.62.76-FA.md",
    ):
        assert (ROOT / rel).is_file(), rel
    assert '- Base: 1.62.75' in read("docs/RELEASE-MANIFEST-1.62.76-FA.md")
