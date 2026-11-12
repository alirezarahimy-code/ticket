from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent


def test_rc_package_requires_the_food_baseline_it_excludes():
    builder = (ROOT / 'tools' / 'build_rc_update.py').read_text(encoding='utf-8')
    assert 'REQUIRES_VERSION = "1.62.94"' in builder
    assert '"035-request-center-appointments.sql"' in builder
    assert '"038-org-role-sync-columns.sql"' in builder
    assert '"039-request-center-lifecycle-catchup.sql"' in builder
    assert '"migrations/037-food-auto-receipts-delivered.sql"' in builder


def test_rc_report_warns_that_known_production_is_older():
    report = (ROOT / 'downloads' / 'CHANGE-REPORT-FA-1.63.md').read_text(encoding='utf-8')
    assert '1.62.77' in report and '1.62.94' in report
