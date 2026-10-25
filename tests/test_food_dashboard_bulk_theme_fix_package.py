import hashlib
import json
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
PACKAGE = ROOT / "downloads/ticket-update-1.62.2-food-dashboard-bulk-theme-fixes-20261025.zip"
RELEASE = "2026-10-25-release-162-2-food-dashboard-bulk-theme-fixes"


def test_food_dashboard_bulk_theme_package_is_cumulative_local_and_integral():
    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    checksum = PACKAGE.with_suffix(PACKAGE.suffix + ".sha256").read_text(encoding="ascii").split()[0]
    assert digest == checksum

    with ZipFile(PACKAGE) as archive:
        assert archive.testzip() is None
        names = set(archive.namelist())
        expected = {
            "manifest.json",
            "files/VERSION",
            "files/bootstrap.php",
            "files/index.php",
            "files/food-order.php",
            "files/food-ticket.php",
            "files/assets/food-menu.js",
            "files/assets/food-menu.css",
            "files/food-ticket-web/assets/food-menu.js",
            "files/food-ticket-web/assets/food-menu.css",
            "files/food-ticket-web/index.html",
            "files/food-ticket-web/assets/food-capitalio-dashboard-r42.js",
            "files/food-ticket-web/assets/food-capitalio-dashboard-r42.css",
            "files/food-ticket-web/assets/food-ticket-global-theme-r42.css",
            f"files/updates/{RELEASE}/manifest.json",
            f"files/updates/{RELEASE}/CHANGE-REPORT-FA.md",
        }
        assert names == expected
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.2"
        assert manifest["migrations"] == []
        assert "offline" in manifest["notes"].lower()
        for name in expected:
            if name.startswith("files/") and not name.startswith("files/updates/"):
                source = ROOT / name.removeprefix("files/")
                assert archive.read(name) == source.read_bytes()
