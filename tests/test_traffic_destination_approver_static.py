from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_destination_approver_is_stored_in_all_schema_sources_and_migration():
    migration = (ROOT / "migrations/008-traffic-destination-approver.sql").read_text()
    assert "ADD COLUMN IF NOT EXISTS approver_name VARCHAR(190) NULL" in migration
    for name in ("schema.sql", "install-empty-db.sql", "migrations/003-sync-schema-151.sql"):
        assert "approver_name VARCHAR(190) NULL" in (ROOT / name).read_text()


def test_destination_admin_can_create_edit_and_resolve_approver_server_side():
    source = (ROOT / "traffic-control.php").read_text()
    assert "SELECT id, title, approver_name, is_active FROM traffic_destinations" in source
    assert "function traffic_destination_approver(" in source
    assert "$payload['approved_by'] = traffic_destination_approver((string) $payload['meeting_with']);" in source
    assert "$payload['approved_by'] = traffic_destination_approver((string) $payload['meeting_with'], $record);" in source
    assert "traffic_add_destination" in source and "traffic_update_destination" in source
    assert "عنوان مقصد و نام تأییدکننده را وارد کنید." in source
    assert "نام تأییدکننده را به‌درستی وارد کنید." in source
    assert "name=\"approver_name\"" in source and "maxlength=\"190\" required" in source
    assert "name=\"approved_by\"" not in source
    assert "مقاصد ملاقات و تأییدکنندگان" in source
    assert "name=\"approver_name\"" in source


def test_approver_autofills_for_new_and_edit_forms_and_history_is_two_column():
    source = (ROOT / "traffic-control.php").read_text()
    script = (ROOT / "assets/traffic-control.js").read_text()
    style = (ROOT / "assets/style.css").read_text()
    assert "data-approver=\"<?= e((string) ($destination['approver_name'] ?? '')) ?>\"" in source
    assert "id=\"traffic-edit-approved-by\"" in source
    assert "syncNewDestinationApprover" in script
    assert "traffic-edit-meeting-with" in script
    assert ".traffic-change-list { grid-template-columns: repeat(2, minmax(0, 1fr));" in style
    assert "@media (max-width: 760px)" in style
    assert 'assets/traffic-control.js?v=9' in source


def test_release_manifest_and_download_page_list_new_destination_migration():
    manifest = (ROOT / "updates/2026-10-20-release-161-10/manifest.json").read_text()
    assert '"008-traffic-destination-approver.sql"' in manifest
    page = (ROOT / "downloads/index.html").read_text()
    package = "ticket-update-1.61.10-traffic-destination-approver-r7-20261021.zip"
    assert package in page and "CHANGE-REPORT-FA.md" in page
    report = (ROOT / "docs/REPORT-FA.md").read_text()
    assert package in report
    assert "نسخه‌های پیشین در این صفحه نمایش داده نمی‌شوند" in report
    assert "گزارش نسخه‌ها" not in page
    for name in ("docs/HANDOFF-FA.md", "docs/SYSTEM-DETAILS-FA.txt"):
        assert package in (ROOT / name).read_text()
