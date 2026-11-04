"""Validate the cumulative 1.62.75 public access-groups updater."""
from __future__ import annotations

import hashlib
import json
import unittest
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BASE = ROOT / "downloads/ticket-update-1.62.74-food-meal-reconciliation-20261104.zip"
PACKAGE = ROOT / "downloads/ticket-update-1.62.75-public-access-groups-20261104.zip"
REPORT = ROOT / "CHANGE-REPORT-FA-1.62.75.md"
EXPECTED_BASE_SHA256 = "ff1d40fc9fa8f3d9e7aadb9aae5e3eeb8fafe504fec05b3048dffd1dee127391"
EXPECTED_ADDITIONS = {
    "actions/access-group-actions.php",
    "user-access-groups.php",
    "migrations/023-user-access-groups.sql",
    "lib/activity-audit.php",
    "docs/ARCHITECTURE-FA.md",
    "docs/USER-ACCESS-GROUPS-FA.md",
    "CHANGE-REPORT-FA-1.62.75.md",
}
# فایل‌هایی که نسخهٔ بعدی (۱.۶۲.۷۶ — لاگ‌گیری مرکزی) دوباره آن‌ها را تغییر داده است.
# بستهٔ ۱.۶۲.۷۵ تغییرناپذیر می‌ماند و همچنان بررسی می‌شود، ولی محتوای مخزن برای این
# فایل‌ها باید با تازه‌ترین بسته یکی باشد نه با ۱.۶۲.۷۵ (قاعدهٔ test_version_source_of_truth).
SUPERSEDED_BY_LATER_RELEASE = {
    "VERSION",
    "README.md",
    "index.php",
    "lib/activity-audit.php",
    # ۱.۶۲.۷۹ ستون‌های «بازهٔ کاری دستگاه تردد» را به schema.sql افزود تا نصب تازه هم
    # آن‌ها را داشته باشد (install.php پوشهٔ migrations/ را اجرا نمی‌کند).
    "schema.sql",
    "install-empty-db.sql",
}
EXPECTED_REPLACEMENTS = {
    "VERSION",
    "README.md",
    "assets/app.js",
    "assets/style.css",
    "help-guide.php",
    "index.php",
    "install-empty-db.sql",
    "permissions.php",
    "schema.sql",
    "lib/auth-scope.php",
}


class PublicAccessGroupsReleasePackageTest(unittest.TestCase):
    def test_cumulative_payload_migration_and_checksum(self) -> None:
        self.assertTrue(BASE.is_file(), "immutable 1.62.74 base ZIP missing")
        self.assertTrue(PACKAGE.is_file(), "1.62.75 updater ZIP missing")
        self.assertEqual(hashlib.sha256(BASE.read_bytes()).hexdigest(), EXPECTED_BASE_SHA256)
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        checksum = PACKAGE.with_name(PACKAGE.name + ".sha256")
        self.assertEqual(checksum.read_text(encoding="ascii").split(), [digest, PACKAGE.name])

        with zipfile.ZipFile(BASE) as base, zipfile.ZipFile(PACKAGE) as package:
            self.assertIsNone(package.testzip())
            manifest = json.loads(package.read("manifest.json"))
            base_manifest = json.loads(base.read("manifest.json"))
            self.assertEqual(manifest["name"], "public-user-access-groups")
            self.assertEqual(manifest["version"], "1.62.75")
            self.assertEqual(manifest["requires_version"], "1.62.74")
            self.assertEqual(manifest["panel_version"], "1.37.52")
            self.assertEqual(manifest["date"], "2026-11-04")
            self.assertEqual(manifest["migrations"], ["023-user-access-groups.sql"])
            self.assertEqual(package.read("files/VERSION"), b"1.62.75\n")

            old_files, new_files = set(base_manifest["files"]), set(manifest["files"])
            self.assertTrue(old_files <= new_files)
            self.assertEqual(new_files - old_files, EXPECTED_ADDITIONS)
            self.assertEqual(
                set(package.namelist()),
                {"manifest.json", "migrations/023-user-access-groups.sql"}
                | {"files/" + relative for relative in new_files},
            )
            self.assertEqual(len(package.namelist()), len(set(package.namelist())))

            changed: set[str] = set()
            for relative in old_files:
                before = base.read("files/" + relative)
                after = package.read("files/" + relative)
                if before != after:
                    changed.add(relative)
                if relative in EXPECTED_REPLACEMENTS:
                    if relative not in SUPERSEDED_BY_LATER_RELEASE:
                        self.assertEqual(after, (ROOT / relative).read_bytes(), relative)
                else:
                    self.assertEqual(after, before, relative)
            self.assertEqual(changed, EXPECTED_REPLACEMENTS)

            for relative in EXPECTED_ADDITIONS:
                if relative in SUPERSEDED_BY_LATER_RELEASE:
                    continue
                self.assertEqual(package.read("files/" + relative), (ROOT / relative).read_bytes(), relative)
            migration = package.read("migrations/023-user-access-groups.sql")
            self.assertEqual(package.read("files/migrations/023-user-access-groups.sql"), migration)
            self.assertEqual(package.read("files/CHANGE-REPORT-FA-1.62.75.md"), REPORT.read_bytes())

            mirror_manifest = json.loads(
                (ROOT / "updates/2026-11-04-user-access-groups-1.62.75/manifest.json").read_text(encoding="utf-8")
            )
            self.assertEqual(mirror_manifest, manifest)
            mirror_migration = (ROOT / "updates/2026-11-04-user-access-groups-1.62.75/migrations/023-user-access-groups.sql").read_bytes()
            self.assertEqual(mirror_migration, migration)

    def test_download_page_lists_current_and_historical_packages(self) -> None:
        page = (ROOT / "downloads/index.html").read_text(encoding="utf-8")
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        self.assertIn(f'href="{PACKAGE.name}" download', page)
        self.assertIn(f'href="{PACKAGE.name}.sha256" download', page)
        self.assertIn('href="CHANGE-REPORT-FA-1.62.75.md"', page)
        self.assertIn(digest, page)
        self.assertIn("مهاجرت افزایشی ۰۲۳", page)
        self.assertIn("ticket-update-1.62.74-food-meal-reconciliation-20261104.zip", page)
        self.assertIn(EXPECTED_BASE_SHA256, page)


if __name__ == "__main__":
    unittest.main()
