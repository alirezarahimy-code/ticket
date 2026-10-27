from __future__ import annotations

import hashlib
import json
import unittest
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
NAME = "ticket-update-1.62.38-food-reports-inside-food-manager-20261027.zip"
PACKAGE = ROOT / "downloads" / NAME
RUNTIME_FILES = [
    "assets/food-menu.css",
    "assets/food-menu.js",
    "assets/style.css",
    "food-order.php",
    "food-ticket-order-source.php",
    "food-ticket.php",
    "food-ticket-web/assets/food-menu.css",
    "food-ticket-web/assets/food-menu.js",
    "food-ticket-web/index.html",
    "index.php",
    "system-update.php",
]


class FoodReportRecoveryPackageTests(unittest.TestCase):
    def test_package_contents_and_checksum(self):
        self.assertTrue(PACKAGE.is_file())
        with ZipFile(PACKAGE) as archive:
            names = archive.namelist()
            self.assertEqual(len(names), len(set(names)))
            self.assertIsNone(archive.testzip())
            manifest = json.loads(archive.read("manifest.json"))
            self.assertEqual(manifest["version"], "1.62.38")
            self.assertEqual(manifest["requires_version"], "1.62.36")
            self.assertEqual(manifest["migrations"], [])
            self.assertEqual(set(manifest["files"]), {"VERSION", *RUNTIME_FILES})
            expected = {"manifest.json", "files/VERSION", *(f"files/{rel}" for rel in RUNTIME_FILES)}
            self.assertEqual(set(names), expected)
            self.assertEqual(archive.read("files/VERSION").decode().strip(), "1.62.38")
            for rel in RUNTIME_FILES:
                self.assertEqual(archive.read(f"files/{rel}"), (ROOT / rel).read_bytes())
            for name in names:
                self.assertNotIn("..", name)
                self.assertFalse(name.startswith("/"))
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        sidecar = (ROOT / "downloads" / f"{NAME}.sha256").read_text(encoding="ascii")
        self.assertIn(digest, sidecar)

    def test_package_restores_dashboard_file_and_keeps_reports_inside_manager(self):
        with ZipFile(PACKAGE) as archive:
            index = archive.read("files/index.php").decode("utf-8")
            panel = archive.read("files/food-ticket-web/index.html").decode("utf-8")
            menu = archive.read("files/assets/food-menu.js").decode("utf-8")
        self.assertNotIn("reportTabFrames", index)
        self.assertNotIn("report-hub-frame", index)
        self.assertIn("dashboard-hero", index)
        self.assertIn("data-report-section", panel)
        self.assertIn("report-module-tabs", panel)
        self.assertIn("FOOD_TICKET_REPORT_EMBED", panel)
        self.assertNotIn("page=reports", menu)
        self.assertNotIn("report_view", panel)

    def test_updater_checks_manifest_minimum_version(self):
        updater = (ROOT / "system-update.php").read_text(encoding="utf-8")
        self.assertIn("$manifest['requires_version']", updater)
        self.assertIn("version_compare($current, $requiredVersion) < 0", updater)
        page = (ROOT / "downloads" / "ticket-update-1.62.38-food-reports-inside-food-manager-20261027.html").read_text(encoding="utf-8")
        self.assertIn("فایل‌های فهرست‌شده در ZIP به‌صورت کامل جایگزین می‌شوند", page)
        self.assertIn("بسته migration یا تغییر schema ندارد", page)


if __name__ == "__main__":
    unittest.main()
