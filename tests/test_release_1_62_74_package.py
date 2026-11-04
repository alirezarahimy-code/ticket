"""Validate the cumulative 1.62.74 food reconciliation updater and immutable base."""
from __future__ import annotations

import hashlib
import json
import unittest
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BASE = ROOT / "downloads/ticket-update-1.62.73-food-dashboard-cleanup-20261104-r2.zip"
PACKAGE = ROOT / "downloads/ticket-update-1.62.74-food-meal-reconciliation-20261104.zip"
REPORT = ROOT / "CHANGE-REPORT-FA-1.62.74.md"
EXPECTED_BASE_SHA256 = "2aba2ead127c9a69a96f113a4f4d401ac7bf033c567a2a6cc75fc4aff916fb2c"
EXPECTED_PACKAGE_SHA256 = "ff1d40fc9fa8f3d9e7aadb9aae5e3eeb8fafe504fec05b3048dffd1dee127391"
# 1.62.78 پنجرهٔ کاری Worker را به food-ticket.php اضافه کرد؛ بستهٔ ۱.۶۲.۷۴ دست‌نخورده می‌ماند.
POST_RELEASE_WORKTREE_CHANGES = {
    "VERSION",
    "README.md",
    "food-ticket.php",
    "food-ticket-web/index.html",
    "assets/app.js",
    "assets/style.css",
    "help-guide.php",
    "index.php",
    "install-empty-db.sql",
    "permissions.php",
    "schema.sql",
    "lib/auth-scope.php",
}
EXPECTED_ADDITIONS = {
    "food-reconciliation.php",
    "migrations/022-food-meal-reconciliation-invoices.sql",
    "CHANGE-REPORT-FA-1.62.74.md",
}
EXPECTED_REPLACEMENTS = {
    "VERSION",
    "assets/food-menu.js",
    "assets/food-order.css",
    "food-order.php",
    "food-ticket.php",
    "food-ticket-web/assets/food-menu.js",
    "food-ticket-web/assets/food-order.css",
    "food-ticket-web/index.html",
    "install-empty-db.sql",
    "schema.sql",
}


class FoodReconciliationReleasePackageTest(unittest.TestCase):
    def test_cumulative_package_manifest_payload_and_checksum(self) -> None:
        self.assertTrue(BASE.is_file(), "immutable 1.62.73 r2 baseline ZIP missing")
        self.assertTrue(PACKAGE.is_file(), "1.62.74 updater ZIP missing")
        self.assertEqual(hashlib.sha256(BASE.read_bytes()).hexdigest(), EXPECTED_BASE_SHA256)
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        self.assertEqual(digest, EXPECTED_PACKAGE_SHA256)
        checksum = PACKAGE.with_name(PACKAGE.name + ".sha256")
        self.assertEqual(checksum.read_text(encoding="ascii").split(), [digest, PACKAGE.name])

        with zipfile.ZipFile(BASE) as base, zipfile.ZipFile(PACKAGE) as package:
            self.assertIsNone(package.testzip())
            manifest = json.loads(package.read("manifest.json"))
            base_manifest = json.loads(base.read("manifest.json"))
            self.assertEqual(manifest["version"], "1.62.74")
            self.assertEqual(manifest["requires_version"], "1.62.73")
            self.assertEqual(manifest["panel_version"], "1.37.52")
            self.assertEqual(manifest["date"], "2026-11-04")
            self.assertEqual(manifest["migrations"], ["022-food-meal-reconciliation-invoices.sql"])
            self.assertEqual(package.read("files/VERSION"), b"1.62.74\n")

            old_files, new_files = set(base_manifest["files"]), set(manifest["files"])
            self.assertTrue(old_files <= new_files)
            self.assertEqual(new_files - old_files, EXPECTED_ADDITIONS)
            self.assertEqual(
                set(package.namelist()),
                {"manifest.json", "migrations/022-food-meal-reconciliation-invoices.sql"}
                | {"files/" + relative for relative in new_files},
            )
            self.assertEqual(len(package.namelist()), len(set(package.namelist())))

            changed: set[str] = set()
            for relative in old_files:
                before = base.read("files/" + relative)
                after = package.read("files/" + relative)
                if before != after:
                    changed.add(relative)
                if relative in POST_RELEASE_WORKTREE_CHANGES:
                    self.assertNotEqual(after, (ROOT / relative).read_bytes(), relative)
                elif relative in EXPECTED_REPLACEMENTS:
                    self.assertEqual(after, (ROOT / relative).read_bytes(), relative)
                else:
                    self.assertEqual(after, before, relative)
            self.assertEqual(changed, EXPECTED_REPLACEMENTS)

            for relative in EXPECTED_ADDITIONS:
                self.assertEqual(package.read("files/" + relative), (ROOT / relative).read_bytes(), relative)
            migration = package.read("migrations/022-food-meal-reconciliation-invoices.sql")
            self.assertEqual(package.read("files/migrations/022-food-meal-reconciliation-invoices.sql"), migration)
            self.assertEqual(package.read("files/food-reconciliation.php"), (ROOT / "food-reconciliation.php").read_bytes())
            self.assertEqual(package.read("files/assets/food-menu.js"), package.read("files/food-ticket-web/assets/food-menu.js"))
            self.assertEqual(package.read("files/assets/food-order.css"), package.read("files/food-ticket-web/assets/food-order.css"))
            self.assertEqual(package.read("files/CHANGE-REPORT-FA-1.62.74.md"), REPORT.read_bytes())

            mirror_manifest = json.loads((ROOT / "updates/2026-11-04-food-meal-reconciliation-1.62.74/manifest.json").read_text(encoding="utf-8"))
            self.assertEqual(mirror_manifest, manifest)

    def test_public_download_page_lists_new_and_historical_packages(self) -> None:
        page = (ROOT / "downloads/index.html").read_text(encoding="utf-8")
        preview = (ROOT / "previews/food-reconciliation-invoice-preview.html").read_text(encoding="utf-8")
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        self.assertIn(f'href="../downloads/{PACKAGE.name}" download', preview)
        self.assertIn(digest, preview)
        self.assertIn(f'href="{PACKAGE.name}" download', page)
        self.assertIn(f'href="{PACKAGE.name}.sha256" download', page)
        self.assertIn('href="CHANGE-REPORT-FA-1.62.74.md"', page)
        self.assertIn("ticket-update-1.62.73-food-dashboard-cleanup-20261104-r2.zip", page)
        self.assertIn(EXPECTED_BASE_SHA256, page)
        self.assertIn("مهاجرت افزایشی ۰۲۲", page)
        self.assertNotIn("فاکتور ماهانه در انتظار", page)


if __name__ == "__main__":
    unittest.main()
