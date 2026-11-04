from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

EXPECTED = (
    "024-central-logging.sql",
    "025-logging-issues-alerts.sql",
    "026-logging-legacy-result-unknown.sql",
    "027-logging-correlation-ids.sql",
    "028-logging-fingerprint-v2.sql",
    "029-activity-result-default-unknown.sql",
    "030-logging-notification-idempotency-and-ticket-history.sql",
)


def test_historical_logging_migrations_are_retained_in_source():
    for name in EXPECTED:
        assert (ROOT / "migrations" / name).is_file(), name


def test_update_migration_policy_is_explicit_and_non_executing():
    report = (ROOT / "docs/DATABASE-MIGRATION-REPORT-1.62.76-FA.md").read_text(encoding="utf-8")
    detail = (ROOT / "docs/MIGRATION-COMPATIBILITY-1.62.76-FA.md").read_text(encoding="utf-8")
    assert "Migration جدید برای این انتشار: **ندارد**" in report
    assert "manifest.migrations" in report
    assert "اجرا نمی‌کند" in report
    assert "schema_updates" in detail
    assert "025" in detail
