# -*- coding: utf-8 -*-
"""Static checks for release 1.62.7:
the traffic edit-history tab keeps the real-edits default filter from 1.62.4
but gains an "all events" toggle so exit_set/deleted audit rows stay visible.
No history rows are ever deleted — this release only changes display filtering."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def test_version_is_1_62_7():
    assert read("VERSION").strip() == "1.62.7"


def test_history_helpers_accept_all_events_flag():
    source = read("traffic-control.php")
    assert "function traffic_history_records(int $limit = 50, int $offset = 0, bool $includeAllEvents = false): array" in source
    assert "function traffic_history_count(bool $includeAllEvents = false): int" in source
    # فیلتر پیش‌فرض ۱.۶۲.۴ حفظ شده است (رشته‌های پین‌شدهٔ تست ۱.۶۲.۴ هم باید بمانند).
    assert "WHERE h.action = 'updated'" in source
    assert "SELECT COUNT(*) FROM traffic_visit_history WHERE action = 'updated'" in source
    # در حالت «همهٔ رویدادها» شمارش بدون WHERE انجام می‌شود.
    assert "'SELECT COUNT(*) FROM traffic_visit_history'" in source


def test_changes_tab_reads_events_param_and_passes_it_through():
    source = read("traffic-control.php")
    assert "$changeShowAll = (string) ($_GET['events'] ?? '') === 'all';" in source
    assert "traffic_history_count($changeShowAll)" in source
    assert "traffic_history_records($changePerPage, ($changePage - 1) * $changePerPage, $changeShowAll)" in source


def test_changes_tab_has_filter_toggle_and_pagination_keeps_mode():
    source = read("traffic-control.php")
    assert 'class="traffic-change-filter"' in source
    assert "فقط ویرایش‌های واقعی" in source
    assert "نمایش همهٔ رویدادها (ویرایش، ثبت خروج، حذف)" in source
    assert "tab=changes&amp;events=all" in source
    # صفحه‌بندی باید حالت انتخابی را حفظ کند.
    assert "$changeEventsParam = $changeShowAll ? '&amp;events=all' : '';" in source
    assert "tab=changes<?= $changeEventsParam ?>&amp;p=" in source


def test_notes_promise_no_data_is_deleted():
    source = read("traffic-control.php")
    assert "هیچ‌کدام از این رویدادها هرگز از پایگاه داده پاک نمی‌شوند" in source
    assert "با دکمهٔ «نمایش همهٔ رویدادها» قابل مشاهده‌اند" in source


def test_help_guide_documents_the_toggle():
    guide = read("help-guide.php")
    assert "نمایش همهٔ رویدادها" in guide
    assert "هیچ رویدادی حذف نمی‌شود" in guide


def test_filter_buttons_have_styles():
    css = read("assets/style.css")
    assert ".traffic-change-filter" in css


def test_update_manifest_is_present_and_consistent():
    import json
    manifest = json.loads(read("updates/2026-10-25-release-162-7-traffic-history-all-events/manifest.json"))
    assert manifest["version"] == "1.62.7"
    assert manifest["migrations"] == []
    assert "traffic-control.php" in manifest["files"]
