from pathlib import Path
import hashlib
import json
import zipfile

ROOT = Path(__file__).resolve().parents[1]
PACKAGE = ROOT / "downloads/ticket-update-1.61.10-food-orders-surname-emergency-window-r43-20261025.zip"
RELEASE = "2026-10-25-release-161-10-r43-food-emergency-order-window"


def test_r43_package_is_limited_and_integral():
    data = PACKAGE.read_bytes()
    checksum = hashlib.sha256(data).hexdigest()
    sha_file = PACKAGE.with_suffix(PACKAGE.suffix + ".sha256").read_text(encoding="utf-8").split()[0]
    assert checksum == sha_file

    with zipfile.ZipFile(PACKAGE) as archive:
        assert archive.testzip() is None
        names = set(archive.namelist())
        expected = {
            "files/food-order.php",
            "files/food-ticket.php",
            "files/permissions.php",
            "files/assets/food-menu.js",
            "files/assets/food-order.js",
            "files/food-ticket-web/assets/food-menu.js",
            "files/food-ticket-web/index.html",
            "manifest.json",
            "CHANGE-REPORT-FA.md",
            f"files/updates/{RELEASE}/manifest.json",
            f"files/updates/{RELEASE}/CHANGE-REPORT-FA.md",
        }
        assert names == expected
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["release"] == "R43"
        assert manifest["migrations"] == []
        assert "food_order_logs" in manifest["notes"]


if __name__ == "__main__":
    test_r43_package_is_limited_and_integral()
    print("R43 food order package assertions: PASS")
