# -*- coding: utf-8 -*-
"""تست ایستای دائمی نسخهٔ ۱.۶۲.۲۱ — تب «تحویل‌شده‌ها» در برنامهٔ غذایی (هم‌شکل «سفارش‌های روز»).

قاعدهٔ گزارش:
- پرسنل: فقط رکوردهایی که print_status=printed و delivery_status=delivered دارند.
- مهمان: فیش‌های مهمانِ چاپ‌شدهٔ همان روز (بدون شرط تحویل).
- ذات و شکل مثل گزارش سفارش‌های روز: فیلدهای انتخابی با چک‌باکس، دو نمای
  «فهرست یکجا» / «نمایش بر اساس منو» (جداکنندهٔ رکورد با سرگروه هر منو)، تاریخ شمسی +
  «امروز»، استخراج Excel و چاپ — ولی با فیلدهای مخصوص خودش.
"""
import hashlib
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.21-food-delivered-report-20261026.zip"
UPDATE_DIR = "updates/2026-10-26-release-162-21-food-delivered-report"


def read(rel: str) -> str:
    return (ROOT / rel).read_text(encoding="utf-8")


def test_food_menu_has_delivered_tab_next_to_orders():
    js = read("food-ticket-web/assets/food-menu.js")
    # تب جدید دقیقاً کنار «سفارش‌های روز» و با همان شرط دسترسی (canClose)
    assert "tabs.push(['orders', 'سفارش‌های روز'], ['delivered', 'تحویل‌شده‌ها']," in js
    # گارد switchTab و دیسپچ پنل
    assert "(tab === 'orders' || tab === 'delivered' || tab === 'statistics' || tab === 'cancel-requests') && !canClose" in js
    assert "state.tab === 'delivered' && canClose ? renderDelivered() :" in js


def test_delivered_columns_and_field_checkboxes():
    js = read("food-ticket-web/assets/food-menu.js")
    assert "var DELIVERED_COLUMNS = [" in js
    for key in ("'row'", "'national_code'", "'employee_number'", "'full_name'", "'food_name'",
                "'food_date'", "'print_time'", "'deliver_time'", "'record_type'", "'menu_no'"):
        assert re.search(r"\{ key: " + key + r", label: '[^']+' \}", js), key
    # چک‌باکس فیلدها مثل سفارش‌های روز، با ذخیره در localStorage مخصوص خودش
    assert "data-fm-delivered-field=" in js
    assert "itsm-food-delivered-report-fields-v1:" in js
    assert "saveDeliveredReportFields(deliveredFields)" in js


def test_delivered_two_views_and_menu_grouping():
    js = read("food-ticket-web/assets/food-menu.js")
    # دو نما مثل سفارش‌های روز
    assert "data-fm-action=\"delivered-view\"" in js
    assert "state.deliveredView === 'menu' ? deliveredByMenuHtml(rows) : deliveredTableHtml(rows)" in js
    # گروه‌بندی بر اساس شماره منو؛ «بدون منو» در انتها
    body = js[js.index("function deliveredByMenuHtml"):]
    body = body[:body.index("\n  }\n") + 4]
    assert "Number(row.menu_no || 0)" in body
    assert "if (a === 0) return 1;" in body and "if (b === 0) return -1;" in body
    assert "'بدون منو'" in body and "فیش" in body
    # مرتب‌سازی مستقل از جدول سفارش‌ها
    assert "data-fm-dlv-sort=" in js and "state.deliveredSort" in js


def test_delivered_date_form_today_export_print():
    js = read("food-ticket-web/assets/food-menu.js")
    assert 'id="fm-delivered-date"' in js
    for action in ("delivered-today", "delivered-export", "delivered-print"):
        assert f'data-fm-action="{action}"' in js, action
    # خروجی XLSX با همان ابزار سفارش‌ها (قانون: همهٔ خروجی‌ها اکسل‌اند)
    assert "function downloadDelivered()" in js
    assert js.count("api.exportXlsx(") >= 2
    # چاپ همان سازوکار گزارش‌های پنل
    assert "printScopedReport('.fm-delivered-report .fm-table-wrap'" in js
    # سه شمارندهٔ خلاصه
    for label in ("نفرات تحویل‌گرفته (پرسنل)", "فیش پرسنلی تحویل‌شده", "فیش مهمان چاپ‌شده"):
        assert label in js, label


def test_backend_delivered_route_and_rules():
    php = read("food-order.php")
    assert "function food_order_delivered_report(string $date): array" in php
    # قاعدهٔ محتوا: پرسنلی = چاپ+تحویل؛ مهمان = فقط چاپ
    assert "print_status = 'printed'" in php
    assert "(event_type = 'guest') OR (event_type <> 'guest' AND delivery_status = 'delivered')" in php
    # نگاشت شماره منو با تطبیق نام غذا با برنامهٔ همان روز
    assert "JOIN food_calendar_items i ON i.calendar_id = cal.id" in php
    assert "$route === 'food-menu/delivered'" in php
    assert php.count("food_order_require_permission($user, 'food.order_close')") >= 2
    # نگاشت دسترسی در روتر food-ticket.php
    assert "'food-menu/delivered'" in read("food-ticket.php")


def test_panel_version_and_cache_bust():
    page = read("food-ticket-web/index.html")
    assert "const PANEL_VERSION='1.37.26'" in page
    assert "const FOOD_TICKET_PANEL_VERSION = '1.37.26';" in read("food-ticket.php")
    # درس 1.62.15: با هر تغییر JS، شمارهٔ کش باید بالا برود
    assert "assets/food-menu.js?v=32" in page
    assert "assets/food-menu.js?v=31" not in page


def test_help_guide_covers_new_tab():
    guide = read("help-guide.php")
    assert "تب «تحویل‌شده‌ها»" in guide
    assert "نفرات تحویل‌گرفته (پرسنل)" in guide
    assert 'data-kw="تحویل شده ها گزارش تحویل' in guide
    chatbot = read("chatbot.php")
    assert "help-guide" in chatbot or "data-kw" in chatbot  # سازوکار ایندکس خودکار سر جای خود است


def test_version_manifest_and_package():
    assert read("VERSION").strip() == "1.62.21"
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.21"
    assert manifest["requires_version"] == "1.61.9"
    assert manifest["migrations"] == ["009-traffic-destination-multi-approver.sql", "010-org-liaison-units.sql"]
    files = manifest["files"]
    assert len(files) == 66 and len(set(files)) == 66
    for need in ("food-ticket-web/index.html", "food-ticket-web/assets/food-menu.js", "food-ticket.php",
                 "food-order.php", "help-guide.php", "chatbot.php",
                 "VERSION", "index.php", "bootstrap.php", "lib/db-core.php", "actions/backup-actions.php"):
        assert need in files, need
    for f in files:
        assert (ROOT / f).is_file(), f

    package = ROOT / "downloads" / PACKAGE_NAME
    assert package.is_file()
    zf = zipfile.ZipFile(package)
    names = zf.namelist()
    assert len(names) == len(set(names))
    nameset = set(names)
    pattern = re.compile(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$")
    for name in nameset:
        if not name.endswith("/"):
            assert pattern.match(name), name
    for mig in manifest["migrations"]:
        assert f"migrations/{mig}" in nameset and f"files/migrations/{mig}" in nameset, mig
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.21"
    for rel in ("food-ticket-web/index.html", "food-ticket-web/assets/food-menu.js", "food-ticket.php",
                "food-order.php", "help-guide.php", "index.php", "bootstrap.php"):
        assert zf.read(f"files/{rel}") == (ROOT / rel).read_bytes(), rel
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")
    assert digest in read("downloads/index.html")


def test_panel_asset_refs_are_all_packaged():
    """قاعدهٔ دائمی fix1: هر asset ارجاع‌شده در index.html پنل غذا باید داخل همان بسته باشد."""
    zf = zipfile.ZipFile(ROOT / "downloads" / PACKAGE_NAME)
    names = set(zf.namelist())
    html = zf.read("files/food-ticket-web/index.html").decode("utf-8")
    for ref in sorted(set(re.findall(r'(?:src|href)="(assets/[^"?]+)', html))):
        assert f"files/food-ticket-web/{ref}" in names, f"ارجاع بدون فایل در بسته: {ref}"


def test_download_page_lists_only_latest_package():
    page = read("downloads/index.html")
    assert PACKAGE_NAME in page
    assert "ticket-update-1.62.20-backup-split-bootstrap-lib-20261025.zip" not in page
    assert "ticket-update-1.62.19-bulk-user-structure-actions-split-20261025.zip" not in page
    assert "تحویل‌شده‌ها" in page
