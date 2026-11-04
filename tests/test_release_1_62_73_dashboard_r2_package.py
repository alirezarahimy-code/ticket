"""Validate the cumulative dashboard-cleanup updater revision 2."""
from __future__ import annotations

import hashlib
import json
import re
import unittest
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE = ROOT / "downloads/ticket-update-1.62.73-food-dashboard-cleanup-20261104-r2.zip"
BASE = ROOT / "downloads/ticket-update-1.62.73-food-auto-receipts-20261104.zip"
FULL = ROOT / "downloads/ticket-full-1.62.73-20261104-r2.zip"
REPORT = ROOT / "downloads/CHANGE-REPORT-FA-1.62.73-DASHBOARD-R2.md"
OLD_BASE_SHA256 = "00aa28a4a13f6a3552ac4fe3e38620f1c36ff8f39bd208ad4414b32a79252043"
FULL_R2_SHA256 = "899718d7b3523e2d83c7eb84c24d2b7b79efd291352a774f3904a95bdd24982e"
EXPECTED_ADDED_FILES = {"CHANGE-REPORT-FA-1.62.73-DASHBOARD-R2.md"}
REMOVED_CARD_IDS = {
    "cancelled-orders",
    "risk-score-dinner",
    "cancelled-dinner-orders",
    "auto-printed-lunch-today",
    "auto-printed-dinner-today",
    "auto-guests-lunch-today",
    "auto-guests-dinner-today",
}


class DashboardR2ReleasePackageTest(unittest.TestCase):
    def test_cumulative_package_contents_checksum_and_historical_artifacts(self) -> None:
        self.assertTrue(PACKAGE.is_file(), "dashboard cleanup updater ZIP missing")
        self.assertTrue(BASE.is_file(), "historical 1.62.73 updater ZIP missing")
        self.assertTrue(FULL.is_file(), "historical full r2 ZIP missing")
        self.assertEqual(hashlib.sha256(BASE.read_bytes()).hexdigest(), OLD_BASE_SHA256)
        self.assertEqual(hashlib.sha256(FULL.read_bytes()).hexdigest(), FULL_R2_SHA256)

        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        sidecar = PACKAGE.with_name(PACKAGE.name + ".sha256")
        self.assertEqual(sidecar.read_text(encoding="ascii").split(), [digest, PACKAGE.name])

        with zipfile.ZipFile(BASE) as base, zipfile.ZipFile(PACKAGE) as package, zipfile.ZipFile(FULL) as full:
            self.assertIsNone(package.testzip())
            manifest = json.loads(package.read("manifest.json"))
            base_manifest = json.loads(base.read("manifest.json"))
            self.assertEqual(manifest["version"], "1.62.73")
            self.assertEqual(manifest["requires_version"], "1.62.58")
            self.assertEqual(manifest["panel_version"], "1.37.52")
            self.assertEqual(manifest["date"], "2026-11-04")
            self.assertEqual(manifest["migrations"], base_manifest["migrations"])
            self.assertIn("نصب مجدد همان نسخه", manifest["notes"])
            self.assertEqual(package.read("files/VERSION"), b"1.62.73\n")

            old_files, new_files = set(base_manifest["files"]), set(manifest["files"])
            self.assertTrue(old_files <= new_files)
            self.assertEqual(new_files - old_files, EXPECTED_ADDED_FILES)
            expected_members = {
                "manifest.json",
                "migrations/019-traffic-destination-link.sql",
                "migrations/020-traffic-destination-upgrade.sql",
                "migrations/021-food-auto-receipts.sql",
            } | {"files/" + relative for relative in new_files}
            self.assertEqual(set(package.namelist()), expected_members)
            self.assertEqual(len(package.namelist()), len(set(package.namelist())))

            for relative in old_files - {"food-ticket-web/index.html"}:
                self.assertEqual(package.read("files/" + relative), base.read("files/" + relative), relative)
            self.assertEqual(
                package.read("files/food-ticket-web/index.html"),
                full.read("ticket/food-ticket-web/index.html"),
            )
            self.assertEqual(package.read("files/" + next(iter(EXPECTED_ADDED_FILES))), REPORT.read_bytes())
            panel = package.read("files/food-ticket-web/index.html").decode("utf-8")
            summary_start = panel.index("  const summaryCards=[")
            summary_end = panel.index("\n  ];", summary_start)
            summary = panel[summary_start:summary_end]
            for card_id in REMOVED_CARD_IDS:
                self.assertNotIn(card_id, summary)
            self.assertIn("<tr><th>فیش خودکار پرسنل</th>", panel)
            self.assertIn("<tr><th>فیش خودکار مهمان</th>", panel)

    def test_public_download_page_links_to_this_package_and_sha256(self) -> None:
        page = (ROOT / "downloads/index.html").read_text(encoding="utf-8")
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        base_url = "https://github.com/alirezarahimy-code/ticket/"
        self.assertIn(PACKAGE.name, page)
        self.assertIn(PACKAGE.name + ".sha256", page)
        self.assertIn(digest, page)
        self.assertIn(base_url + "raw/refs/heads/arena/642158e3-ticket/downloads/" + PACKAGE.name, page)
        self.assertIn(base_url + "raw/refs/heads/arena/642158e3-ticket/downloads/" + PACKAGE.name + ".sha256", page)
        self.assertIn(base_url + "blob/arena/642158e3-ticket/downloads/" + REPORT.name, page)
        self.assertNotIn("اصلاح داشبوردِ بازبینی دوم در این updater نیست", page)
        self.assertIsNotNone(re.search(r"ticket-update-1\.62\.73-food-dashboard-cleanup-20261104-r2\.zip", page))


if __name__ == "__main__":
    unittest.main()
