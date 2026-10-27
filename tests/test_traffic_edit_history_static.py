from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_traffic_edit_form_includes_visit_date_and_both_times():
    source = (ROOT / "traffic-control.php").read_text()
    assert 'name="visit_date"' in source
    assert 'name="entry_time"' in source
    assert 'name="exit_time"' in source
    assert "traffic_update_record($user)" in source
    assert "traffic_can_manage($user)" in source


def test_update_and_exit_changes_are_written_transactionally_to_history():
    source = (ROOT / "traffic-control.php").read_text()
    assert "function traffic_record_history(" in source
    assert "traffic_record_history($record, $updated, $user, 'updated')" in source
    assert "traffic_record_history($record, $updated, $user, 'exit_set')" in source
    assert "traffic_record_history($record, null, $user, 'deleted')" in source
    assert "function traffic_history_records(" in source
    assert "function traffic_history_count(" in source
    assert "tab=changes" in source


def test_history_schema_exists_in_migration_and_fresh_install_schemas():
    sql = (ROOT / "migrations/007-traffic-visit-history.sql").read_text()
    assert "CREATE TABLE IF NOT EXISTS traffic_visit_history" in sql
    assert "before_json LONGTEXT" in sql
    assert "after_json LONGTEXT" in sql
    assert "changes_json LONGTEXT NOT NULL" in sql
    for name in ("schema.sql", "install-empty-db.sql"):
        assert "CREATE TABLE IF NOT EXISTS traffic_visit_history" in (ROOT / name).read_text()


def test_food_print_page_treats_layout_design_as_optional():
    source = (ROOT / "food-ticket.php").read_text()
    assert "if (function_exists('admin_layout_design_css_for_page')" in source
    assert "page layout enhancement skipped" in source
    assert "preg_replace('/<body\\\\b/'" in source


def test_shared_header_does_not_fail_when_layout_designer_is_absent_or_unavailable():
    source = (ROOT / "index.php").read_text()
    assert "if (is_file(__DIR__ . '/admin-layout-design.php'))" in source
    assert "if (function_exists('admin_layout_design_api_handle'))" in source
    assert "page layout enhancement skipped" in source


def test_food_panel_skips_invalid_empty_search_replacements_and_catches_transform_errors():
    source = (ROOT / "food-ticket.php").read_text()
    assert "function food_ticket_render_safe_replace(" in source
    assert "if ($search === '' || $subject === '')" in source
    assert "if ($legacy === '')" in source
    assert "HTML host transformation skipped" in source


def test_traffic_edit_times_use_unambiguous_24_hour_text_inputs():
    source = (ROOT / "traffic-control.php").read_text()
    assert 'class="traffic-time-24h" type="text"' in source
    assert 'name="entry_time"' in source and 'name="exit_time"' in source
    assert 'function traffic_normalize_time_input(' in source
    assert "'：' => ':'" in source
    assert 'type="time"' not in source
    assert 'قالب ۲۴ساعته' in source
    script = (ROOT / "assets/traffic-control.js").read_text()
    assert "querySelectorAll('.traffic-time-24h')" in script
    assert "digits.slice(0, 2) + ':' + digits.slice(2)" in script
    assert "padStart(2, '0')" in script
