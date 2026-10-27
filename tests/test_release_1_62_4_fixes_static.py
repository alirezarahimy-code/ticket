# -*- coding: utf-8 -*-
"""Static checks for the 1.62.4 fixes:
dark-mode coverage of food planning forms, traffic edit-history filtering,
full-width food dashboard, and manual ticket select-all behaviour."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def test_dark_mode_covers_food_planning_forms_in_both_theme_files():
    for path in ["assets/theme-mode.css", "food-ticket-web/assets/food-ticket-global-theme-r42.css"]:
        css = read(path)
        for selector in [".fg-person", ".fm-bulk-controls", ".fm-bank-field input",
                         ".fm-cat-toggle", ".fm-radio", ".fm-confirm-box", ".fm-bulk-mode.active",
                         ".fo-recipient-avatar", ".fo-choice-icon"]:
            assert selector in css, f"{selector} missing in {path}"
        # گرادیان روشن بنر گیرنده باید با background (نه فقط background-color) خنثی شود.
        assert '.fo-recipient-banner { background:#18231e !important' in css, path


def test_theme_css_cache_versions_are_bumped():
    assert "assets/theme-mode.css?v=3" in read("index.php")
    assert "'theme_mode_href' => 'assets/theme-mode.css?v=3'" in read("food-ticket.php")
    panel = read("food-ticket-web/index.html")
    assert "food-ticket-global-theme-r42.css?v=45" in panel
    assert "food-ticket-theme.css?v=10" in panel
    assert "food-manual-attendance.js?v=5" in panel
    assert "const PANEL_VERSION='1.37.27'" in panel
    assert "const FOOD_TICKET_PANEL_VERSION = '1.37.27';" in read("food-ticket.php")


def test_traffic_edit_history_lists_only_real_updates():
    source = read("traffic-control.php")
    assert "WHERE h.action = 'updated'" in source
    assert "SELECT COUNT(*) FROM traffic_visit_history WHERE action = 'updated'" in source
    # ثبت خروج و حذف همچنان برای ممیزی در جدول ذخیره می‌شوند.
    assert "traffic_record_history($record, $updated, $user, 'exit_set')" in source
    assert "traffic_record_history($record, null, $user, 'deleted')" in source


def test_food_dashboard_host_keeps_full_width_and_no_side_stick():
    css = read("food-ticket-web/assets/food-ticket-theme.css")
    assert ".page.food-dashboard-host" in css
    block = css.split(".page.food-dashboard-host", 1)[1].split("}")[0]
    assert "width: 100% !important" in block
    assert "margin: 0 !important" in block
    assert "max-width: none !important" in block
    # صفحه‌های عادی پنل وسط‌چین می‌مانند.
    assert "margin-inline: auto !important" in css


def test_manual_ticket_list_has_select_all_and_no_default_selection():
    js = read("food-ticket-web/assets/food-manual-attendance.js")
    assert "manualSelected: []" in js
    assert "data-manual-select-all" in js
    assert "allEligibleSelected" in js
    assert "e.target.matches('[data-manual-select-all]')" in js
    assert "S.manualSelected = []" in js


if __name__ == "__main__":
    test_dark_mode_covers_food_planning_forms_in_both_theme_files()
    test_theme_css_cache_versions_are_bumped()
    test_traffic_edit_history_lists_only_real_updates()
    test_food_dashboard_host_keeps_full_width_and_no_side_stick()
    test_manual_ticket_list_has_select_all_and_no_default_selection()
    print("ok")
