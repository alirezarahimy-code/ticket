# -*- coding: utf-8 -*-
"""آزمون‌های ایستای نسخهٔ ۱.۶۲.۹:

۱) ثبت دستی فیش برای روزهای قبل (فیلد تاریخ شمسی + تقویم؛ تاریخ آینده ممنوع)
۲) فیش مهمان دستی — ردیف «مهمان» همیشه اول فهرست، چندبار در روز قابل ثبت
۳) چهار فیلد فرم «مقاصد ملاقات و تأییدکنندگان» در یک خط
"""
import re
import json
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.9-manual-ticket-date-guest-20261025.zip"


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


# ───────────── بخش ۱: تاریخ گذشته در ثبت دستی ─────────────

def test_backend_accepts_past_dates_and_rejects_future():
    groups = read("food-ticket-groups.php")
    assert "function food_ticket_manual_attendance_date(string $date): string" in groups
    assert "ثبت دستی فیش برای تاریخ آینده مجاز نیست" in groups
    assert "if ($date > $today)" in groups
    # فهرست و ثبت هر دو از تاریخ نرمال‌شده استفاده می‌کنند؛ خطای «فقط امروز» حذف شده است.
    assert "ثبت دستی فیش فقط برای امروز مجاز است" not in groups
    assert "$date = food_ticket_manual_attendance_date($date);" in groups
    assert "$targetDate = food_ticket_manual_attendance_date($date);" in groups
    assert "food_ticket_decide_for_user($person, $targetDate" in groups


def test_route_passes_selected_date_instead_of_today_guard():
    ticket = read("food-ticket.php")
    assert "ثبت دستی فیش فقط برای امروز مجاز است" not in ticket
    assert "$body['date'] = $manualDate;" in ticket
    assert "food_ticket_api_manual_attendance_save_many($body, $user)" in ticket


def test_save_many_forwards_date_to_each_save():
    groups = read("food-ticket-groups.php")
    assert "$date = food_ticket_manual_attendance_date((string) ($body['date'] ?? ''));" in groups
    assert "$reason, $actor, $orderMaps, $date);" in groups


def test_ui_has_jalali_date_field_with_calendar():
    js = read("food-ticket-web/assets/food-manual-attendance.js")
    assert 'id="fg-manual-date"' in js
    assert "data-jalali" in js
    assert "ItsmJalali" in js and "enhanceAll" in js
    assert "applyManualDateInput" in js
    assert "تاریخ آینده مجاز نیست" in js
    assert "date: manualDateIso()" in js
    assert "date: isoToday()" not in js
    assert "فقط امروز" not in js


# ───────────── بخش ۲: فیش مهمان دستی ─────────────

def test_guest_row_is_first_and_guest_save_path_exists():
    groups = read("food-ticket-groups.php")
    assert "function food_ticket_manual_attendance_guest_row(): array" in groups
    assert "array_unshift($employees, food_ticket_manual_attendance_guest_row());" in groups
    assert "function food_ticket_api_manual_attendance_save_guest(" in groups
    # مهمان: بدون کاربر، نوع رویداد guest، نوع غذای «مهمان»، کلید یکتای تصادفی برای ثبت چندباره
    save_guest = groups[groups.index("function food_ticket_api_manual_attendance_save_guest("):]
    save_guest = save_guest[: save_guest.index("\nfunction ")]
    assert "'user_id' => null" in save_guest
    assert "'full_name' => 'مهمان'" in save_guest
    assert "'event_type' => $withTicket ? 'guest' : 'no_food'" in save_guest
    assert "'food_type' => $withTicket ? 'مهمان' : null" in save_guest
    assert "'ticket_key' => null" in save_guest
    assert "random_bytes" in save_guest
    assert "food_ticket_enqueue_print($eventId, $config)" in save_guest
    assert "food_ticket_audit('food_ticket_manual_attendance'" in save_guest


def test_save_many_accepts_guest_id_zero():
    groups = read("food-ticket-groups.php")
    assert "if ($userId === false || (int) $userId < 0)" in groups
    assert "$labels = [0 => ['name' => 'مهمان', 'pc' => '']];" in groups
    assert "if ($userId === 0) {" in groups
    assert "return food_ticket_api_manual_attendance_save_guest($withTicket, $physicalPrint, $reason, $actor, $targetDate);" in groups


def test_ui_pins_guest_row_and_sends_id_zero():
    js = read("food-ticket-web/assets/food-manual-attendance.js")
    assert "isGuestRow" in js
    assert "selectableRow" in js
    assert "مهمان — چند بار قابل ثبت" in js
    assert "Number.isInteger(id) && id >= 0" in js
    assert ".filter((id) => id > 0)" not in js


def test_guest_event_schema_support_already_exists():
    schema = read("schema.sql")
    assert "'guest'" in schema  # ENUM رویدادها
    assert "user_id" in schema


# ───────────── بخش ۳: فرم مقصدها در یک خط ─────────────

def test_destination_forms_fit_four_fields_on_one_line():
    style = read("assets/style.css")
    assert style.count("grid-template-columns: repeat(4, minmax(0, 1fr)) auto") >= 2
    assert re.search(r"assets/style\.css\?v=3\.4\.\d+", read("index.php"))  # از ۱.۶۲.۱۰ نسخهٔ CSS جلوتر می‌رود
    # موبایل همچنان تک‌ستونه می‌ماند
    assert "@media (max-width: 760px)" in style


# ───────────── راهنما، نسخه و بستهٔ انتشار ─────────────

def test_help_guide_documents_new_manual_ticket_features():
    guide = read("help-guide.php")
    assert "فیش مهمان" in guide
    assert "تاریخ ثبت (شمسی)" in guide
    assert "تاریخ آینده مجاز نیست" in guide


def test_version_manifest_and_package():
    # نسخهٔ جاری مخزن از ۱.۶۲.۱۰ به بعد جلوتر است؛ پین VERSION به تست نسخهٔ جدید منتقل شد.
    manifest = json.loads(read("updates/2026-10-25-release-162-9-manual-ticket-date-guest/manifest.json"))
    assert manifest["version"] == "1.62.9"
    # این نسخه مهاجرت جدید ندارد؛ 009 فقط برای ارتقای مستقیم از ۱.۶۱.۹+ همراه بسته است.
    assert manifest["migrations"] == ["009-traffic-destination-multi-approver.sql"]
    for name in ("food-ticket-groups.php", "food-ticket-web/assets/food-manual-attendance.js", "assets/style.css"):
        assert name in manifest["files"]
    report = read("updates/2026-10-25-release-162-9-manual-ticket-date-guest/CHANGE-REPORT-FA.md")
    assert "مهمان" in report and "روز" in report

    package = ROOT / "downloads" / PACKAGE_NAME
    assert package.exists(), "بستهٔ ZIP نسخهٔ ۱.۶۲.۹ ساخته نشده است"
    zf = zipfile.ZipFile(package)
    names = zf.namelist()
    assert "manifest.json" in names
    pkg_manifest = json.loads(zf.read("manifest.json").decode("utf-8"))
    assert pkg_manifest["version"] == "1.62.9"
    assert pkg_manifest["migrations"] == ["009-traffic-destination-multi-approver.sql"]
    # قانون بسته‌بندی: هر مهاجرتِ manifest باید در ریشهٔ ZIP هم باشد (درس fix1 نسخهٔ ۱.۶۲.۸).
    assert "migrations/009-traffic-destination-multi-approver.sql" in names
    for required in (
        "files/food-ticket-groups.php",
        "files/food-ticket.php",
        "files/food-ticket-web/assets/food-manual-attendance.js",
        "files/food-ticket-web/index.html",
        "files/assets/style.css",
        "files/help-guide.php",
        "files/index.php",
        "files/VERSION",
    ):
        assert required in names, required
    # قرارداد آپدیتر: فقط manifest.json، migrations/*.sql و files/*
    import re
    pattern = re.compile(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$")
    for name in names:
        if name.endswith("/"):
            continue
        assert pattern.match(name), name
    packed_version = zf.read("files/VERSION").decode("utf-8").strip()
    assert packed_version == "1.62.9"


def test_download_page_lists_only_latest_package():
    page = read("downloads/index.html")
    # صفحهٔ دانلود فقط آخرین بسته را نشان می‌دهد؛ از ۱.۶۲.۱۰ بستهٔ جدید جایگزین شد.
    assert "ticket-update-1.62.13-ticket-asset-actions-split-20261025.zip" in page
    assert PACKAGE_NAME not in page
    assert "fix2-20261025.zip" not in page
