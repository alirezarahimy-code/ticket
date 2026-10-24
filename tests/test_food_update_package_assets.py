from pathlib import Path
from zipfile import ZipFile


ROOT = Path(__file__).resolve().parents[1]
PACKAGE = ROOT / "downloads/ticket-update-1.61.10-food-ui-asset-hotfix-r32-20261024.zip"


def test_food_hotfix_contains_versioned_manual_and_bulk_ui_assets():
    required = [
        "files/food-ticket-web/index.html",
        "files/food-ticket-web/assets/food-manual-attendance.js",
        "files/food-ticket-web/assets/food-groups.js",
        "files/food-ticket-web/assets/food-menu.js",
        "files/food-ticket-web/assets/food-menu.css",
        "files/food-ticket-web/assets/food-order.css",
        "files/food-ticket-web/assets/food-ticket-theme.css",
        "files/food-ticket.php",
    ]
    assert PACKAGE.is_file(), f"food update package is missing: {PACKAGE}"
    with ZipFile(PACKAGE) as archive:
        names = set(archive.namelist())
        assert archive.testzip() is None
        assert all(name in names for name in required)
        page = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        assert 'src="assets/food-manual-attendance.js?v=2"' in page
        assert 'src="assets/food-groups.js?v=16"' in page
        assert 'src="assets/food-menu.js?v=25"' in page
        handler = archive.read("files/food-ticket.php").decode("utf-8")
        assert "food-manual-attendance" in handler
        assert "food-menu/bulk-apply" in handler


def test_hotfix_page_is_the_same_as_the_packaged_page():
    with ZipFile(PACKAGE) as archive:
        packaged = archive.read("files/food-ticket-web/index.html")
    current = (ROOT / "food-ticket-web/index.html").read_bytes()
    assert packaged == current
