import re
from pathlib import Path
from zipfile import ZipFile


ROOT = Path(__file__).resolve().parents[1]
PACKAGE = ROOT / "downloads/ticket-update-1.61.10-food-runtime-recovery-r33.1-20261024.zip"


REQUIRED_FILES = [
    "files/food-ticket.php",
    "files/food-order.php",
    "files/food-ticket-groups.php",
    "files/food-ticket-runtime.php",
    "files/food-ticket-engine.php",
    "files/food-ticket-env.php",
    "files/food-ticket-netprint.php",
    "files/food-ticket-order-source.php",
    "files/food-ticket-templates.php",
    "files/food-order-import.php",
    "files/food-ticket-import.php",
    "files/food-personnel-link.php",
    "files/food-ticket-web-host.php",
    "files/food-ticket-web/index.html",
    "files/food-ticket-web/assets/food-brand.js",
    "files/food-ticket-web/assets/food-designer.js",
    "files/food-ticket-web/assets/food-groups.js",
    "files/food-ticket-web/assets/food-manual-attendance.js",
    "files/food-ticket-web/assets/food-menu.css",
    "files/food-ticket-web/assets/food-menu.js",
    "files/food-ticket-web/assets/food-order-calendar.js",
    "files/food-ticket-web/assets/food-order.css",
    "files/food-ticket-web/assets/food-ticket-persistence.js",
    "files/food-ticket-web/assets/food-ticket-theme.css",
    "files/food-ticket-web/assets/jalali-calendar.js",
    "files/food-ticket-web/assets/list-sort.js",
    "files/migrations/006-food-meal-type.sql",
    "migrations/006-food-meal-type.sql",
]


def test_food_runtime_recovery_bundle_is_complete_and_integral():
    assert PACKAGE.is_file(), f"food runtime recovery package is missing: {PACKAGE}"
    with ZipFile(PACKAGE) as archive:
        names = set(archive.namelist())
        assert archive.testzip() is None
        assert all(name in names for name in REQUIRED_FILES)
        page = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        assert 'src="assets/food-manual-attendance.js?v=2"' in page
        assert 'src="assets/food-groups.js?v=16"' in page
        assert 'src="assets/food-menu.js?v=25"' in page
        for asset in re.findall(r'(?:src|href)="(assets/[^"?]+)', page):
            assert f"files/food-ticket-web/{asset}" in names, f"page asset missing from package: {asset}"
        groups = archive.read("files/food-ticket-groups.php").decode("utf-8")
        assert "function food_ticket_manual_attendance_today" in groups
        orders = archive.read("files/food-order.php").decode("utf-8")
        assert "food-menu/bulk-apply" in orders
        assert "function food_order_menu_api_handle" in orders


def test_hotfix_package_is_intact_and_panel_is_mirrored():
    """بستهٔ تاریخی است؛ برابری با کد جاری بررسی نمی‌شود، فقط سالم بودن و آینه‌بودن."""
    with ZipFile(PACKAGE) as archive:
        assert archive.testzip() is None
        assert archive.read("files/food-ticket-web/index.html")
    # آینهٔ فعلی پنل و دارایی‌ها باید یکسان باشد (قاعدهٔ همیشگی پروژه).
    assert (ROOT / "assets/food-menu.js").read_bytes() == (
        ROOT / "food-ticket-web/assets/food-menu.js"
    ).read_bytes()
