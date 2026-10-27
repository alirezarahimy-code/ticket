from __future__ import annotations

import hashlib
import json
import unittest
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
NAME = "ticket-update-1.62.37-central-food-reports-20261027.zip"
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
]


class CentralFoodReportsUpdatePackageTests(unittest.TestCase):
    def test_update_package_is_incremental_complete_and_integral(self):
        self.assertTrue(PACKAGE.is_file())
        with ZipFile(PACKAGE) as archive:
            names = archive.namelist()
            self.assertEqual(len(names), len(set(names)))
            self.assertIsNone(archive.testzip())
            manifest = json.loads(archive.read("manifest.json"))
            self.assertEqual(manifest["version"], "1.62.37")
            self.assertEqual(manifest["requires_version"], "1.62.36")
            self.assertEqual(manifest["migrations"], [])
            self.assertEqual(set(manifest["files"]), {"VERSION", *RUNTIME_FILES})
            self.assertEqual(set(names), {"manifest.json", "files/VERSION", *(f"files/{name}" for name in RUNTIME_FILES)})
            self.assertEqual(archive.read("files/VERSION").decode().strip(), "1.62.37")
            for rel in RUNTIME_FILES:
                self.assertEqual(archive.read(f"files/{rel}"), (ROOT / rel).read_bytes())
            for name in names:
                self.assertNotIn("..", name)
                self.assertFalse(name.startswith("/"))
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        sidecar = (ROOT / "downloads" / f"{NAME}.sha256").read_text(encoding="ascii")
        self.assertIn(digest, sidecar)

    def test_update_download_page_identifies_this_as_an_incremental_package(self):
        page = (ROOT / "downloads" / "ticket-update-1.62.37-central-food-reports-20261027.html").read_text(encoding="utf-8")
        self.assertIn(NAME, page)
        self.assertIn(f"{NAME}.sha256", page)
        self.assertIn("کل سورس پروژه را شامل نمی", page)
        self.assertIn("۱.۶۲.۳۶", page)
        self.assertIn("هیچ migration یا اصلاح دادهٔ زنده", page)


if __name__ == "__main__":
    unittest.main()
