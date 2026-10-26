# -*- coding: utf-8 -*-
"""Static checks for release 1.62.8:
1) the previous-visit auto-fill works from national code AND full name AND phone;
2) each meeting destination stores 1 to 3 approvers, selectable at registration."""
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def test_version_is_1_62_8():
    # نسخهٔ جاری جلوتر رفته است؛ manifest همان انتشار ۱.۶۲.۸ باید پین بماند.
    import json
    manifest = json.loads(read("updates/2026-10-25-release-162-8-traffic-autofill-multi-approver/manifest.json"))
    assert manifest["version"] == "1.62.8"


# ---------- بخش ۱: تکمیل خودکار با کد ملی / نام / تلفن ----------

def test_lookup_api_accepts_code_phone_and_name():
    source = read("traffic-control.php")
    assert "function traffic_find_last_visit_by(string $column, string $value, int $excludeId = 0): ?array" in source
    assert "['national_code', 'phone', 'full_name']" in source
    assert "traffic_find_last_visit_by('phone', (string) $phone)" in source
    assert "traffic_find_last_visit_by('full_name', $name)" in source
    # ردیف‌های «تردد خاص» در جست‌وجو نمی‌آیند و پاسخ، کد ملی را هم برمی‌گرداند.
    assert "COALESCE(is_notice, 0) = 0" in source
    assert "'national_code' => $last['national_code']," in source


def test_lookup_javascript_triggers_on_name_and_phone():
    script = read("assets/traffic-control.js")
    assert "function lookupBy(query, quiet)" in script
    assert "lookupBy('phone=' + encodeURIComponent('09' + tail), true)" in script
    assert "lookupBy('name=' + encodeURIComponent(name), true)" in script
    # پر کردن کد ملی وقتی جست‌وجو با نام/تلفن انجام شده است.
    assert "data.national_code" in script


# ---------- بخش ۲: ۱ تا ۳ تأییدکننده برای هر مقصد ----------

def test_multi_approver_columns_exist_in_schema_and_migration():
    migration = read("migrations/009-traffic-destination-multi-approver.sql")
    assert "ADD COLUMN IF NOT EXISTS approver_name2 VARCHAR(190) NULL" in migration
    assert "ADD COLUMN IF NOT EXISTS approver_name3 VARCHAR(190) NULL" in migration
    for name in ("schema.sql", "install-empty-db.sql"):
        src = read(name)
        assert "approver_name2 VARCHAR(190) NULL" in src, name
        assert "approver_name3 VARCHAR(190) NULL" in src, name


def test_destination_forms_accept_three_approvers():
    source = read("traffic-control.php")
    assert "function traffic_destination_posted_approvers(): array" in source
    assert 'name="approver_name2"' in source
    assert 'name="approver_name3"' in source
    assert "تأییدکنندهٔ ۱ (اصلی)" in source
    assert "تأییدکنندهٔ ۲ (اختیاری)" in source
    assert "تأییدکنندهٔ ۳ (اختیاری)" in source
    assert "INSERT INTO traffic_destinations (title, approver_name, approver_name2, approver_name3)" in source
    assert "UPDATE traffic_destinations SET title = ?, approver_name = ?, approver_name2 = ?, approver_name3 = ? WHERE id = ?" in source


def test_server_validates_approver_against_destination_list():
    source = read("traffic-control.php")
    assert "function traffic_destination_approvers(" in source
    assert "function traffic_destination_row_approvers(" in source
    assert "تأییدکننده باید یکی از تأییدکنندگان تعریف‌شدهٔ همان مقصد باشد." in source
    # رکورد قدیمی با تأییدکنندهٔ متفاوت هنگام ویرایش همان مقصد همچنان مجاز است.
    assert "رکورد قدیمی یا واردشده از Excel" in source


def test_forms_render_approver_select_with_data_approvers():
    source = read("traffic-control.php")
    assert 'select name="approved_by" id="traffic-approved-by"' in source
    assert 'select name="approved_by" id="traffic-edit-approved-by"' in source
    assert "data-approvers=" in source
    assert 'assets/traffic-control.js?v=12' in source
    script = read("assets/traffic-control.js")
    assert "function parseApprovers(option)" in script
    assert "traffic-edit-approved-by" in script


def test_bulk_preview_button_moved_next_to_clear_selection():
    """fix2: دکمهٔ «پیش‌نمایش و بررسی» در نوار بالای فهرست کنار «پاک‌کردن انتخاب» است، نه در فوتر."""
    for path in ("assets/food-menu.js", "food-ticket-web/assets/food-menu.js"):
        script = read(path)
        tools = script.split('fm-bulk-tools', 1)[1].split('</div></div>', 1)[0]
        assert "پاک‌کردن انتخاب" in tools and "پیش‌نمایش و بررسی" in tools, path
        footer = script.split('fm-bulk-footer', 1)[1].split("'", 1)[0] + script.split('fm-bulk-footer', 1)[1]
        assert "پیش‌نمایش و بررسی" not in script.split('fm-bulk-footer', 1)[1].split('</div>', 1)[0], path
        assert "var previewDisabled" in script, path
    assert 'food-menu.js?v=31' in read("food-ticket-web/index.html")


def test_help_guide_documents_both_features():
    guide = read("help-guide.php")
    assert "۱ تا ۳ تأییدکننده" in guide
    assert "تکمیل خودکار با" in guide


def test_update_manifest_lists_migration():
    manifest = json.loads(read("updates/2026-10-25-release-162-8-traffic-autofill-multi-approver/manifest.json"))
    assert manifest["version"] == "1.62.8"
    assert "009-traffic-destination-multi-approver.sql" in manifest["migrations"]


def test_current_package_passes_system_update_rules():
    """system-update.php مهاجرت را در ریشهٔ ZIP (migrations/NNN.sql) می‌خواهد؛
    نبود آن باعث خطای «مهاجرت نامعتبر یا فایل آن در پک نیست» می‌شود (اشکال بستهٔ اول 1.62.8)."""
    import re
    import zipfile
    package = ROOT / "downloads/ticket-update-1.62.8-traffic-autofill-multi-approver-fix2-20261025.zip"
    assert package.is_file()
    zf = zipfile.ZipFile(package)
    names = zf.namelist()
    manifest = json.loads(zf.read("manifest.json"))
    for migration in manifest["migrations"]:
        assert re.match(r"^\d{3}-[a-z0-9-]+\.sql$", migration), migration
        assert f"migrations/{migration}" in names, f"root migration missing: {migration}"
    for name in names:
        assert name.endswith("/") or re.match(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$", name), name
