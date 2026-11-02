from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def test_migration_is_additive_idempotent_and_does_not_replace_snapshots():
    sql = (ROOT / 'migrations/019-traffic-destination-link.sql').read_text()
    assert 'information_schema.COLUMNS' in sql and 'information_schema.STATISTICS' in sql
    assert 'ADD COLUMN destination_id' in sql and 'ADD INDEX idx_traffic_destination_id' in sql
    assert 'JOIN traffic_destinations d ON v.meeting_with = d.title' in sql
    assert 'WHERE v.destination_id IS NULL' in sql
    assert 'SET v.meeting_with' not in sql and 'SET v.approved_by' not in sql
    for name in ('schema.sql', 'install-empty-db.sql'):
        assert 'destination_id INT UNSIGNED DEFAULT NULL' in (ROOT / name).read_text()

def test_shared_preview_export_and_real_destination_source():
    php = (ROOT / 'traffic-control.php').read_text()
    assert "traffic_destinations($tab !== 'report')" in php
    assert 'v.destination_id IN (' in php
    assert 'v.destination_id IS NULL AND v.meeting_with IN (' in php
    assert 'traffic_report_columns()' in php and 'traffic_report_item($record)' in php
    assert "array_map('traffic_report_item', $rows)" in php
    assert "traffic_report_selected_fields($_GET)" in php
    history = php.split('function traffic_history_rows_html', 1)[1].split('function traffic_api_handle', 1)[0]
    assert 'traffic-control-print' not in history
