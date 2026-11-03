from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def test_cumulative_migration_only_backfills_when_019_has_not_been_logged():
    sql = (ROOT / 'migrations/020-traffic-destination-upgrade.sql').read_text()
    assert "migration IN ('019-traffic-destination-link.sql', '020-traffic-destination-upgrade.sql')" in sql
    assert 'IF(@traffic_link_previously_applied = 0' in sql
    assert 'WHERE v.destination_id IS NULL' in sql
    assert 'information_schema.COLUMNS' in sql and 'information_schema.STATISTICS' in sql
    assert 'SET v.meeting_with' not in sql and 'SET v.approved_by' not in sql

def test_report_has_cache_busted_popup_assets():
    php = (ROOT / 'traffic-control.php').read_text()
    grid = php.split('<div class="traffic-report-date-grid">', 1)[1].split('name="fields_present"', 1)[0]
    assert 'name="from_date"' in grid and 'name="to_date"' in grid
    assert 'traffic-destination-dropdown' in grid
    assert 'assets/traffic-report-1.62.70.css' in php
    assert 'assets/traffic-control.js?v=15' in php
