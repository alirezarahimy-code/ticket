"""Validate the 1.62.73 cumulative archive against 1.62.72 and its checksum."""
from __future__ import annotations

import hashlib
import json
import unittest
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE = ROOT / "downloads/ticket-update-1.62.73-food-auto-receipts-20261104.zip"
PREVIOUS = ROOT / "downloads/ticket-update-1.62.72-food-reports-traffic-20261103.zip"
EXPECTED_REPLACEMENTS = {
    "VERSION",
    "README.md",
    "assets/food-menu.js",
    "assets/food-order-guest.js",
    "cron/food_ticket_worker.php",
    "food-order.php",
    "food-ticket.php",
    "food-ticket-web/assets/food-menu.js",
    "food-ticket-web/index.html",
    "help-guide.php",
    "install-empty-db.sql",
    "schema.sql",
}
EXPECTED_ADDITIONS = {
    "CHANGE-REPORT-FA-1.62.73.md",
    "food-ticket-auto-receipts.php",
    "migrations/021-food-auto-receipts.sql",
}
OLD_ARCHIVE_SHA256 = "d749b961b6aa8722fad1aab92ff8f00f6d6a5cb24109de46ffe76b23da234502"
EXPECTED_PACKAGE_SHA256 = "00aa28a4a13f6a3552ac4fe3e38620f1c36ff8f39bd208ad4414b32a79252043"
# The published 1.62.73 updater is immutable; later worktree updates belong to R2, 1.62.74, and 1.62.75.
POST_RELEASE_WORKTREE_CHANGES = {
    "VERSION",
    "README.md",
    "assets/app.js",
    "assets/food-menu.js",
    "assets/style.css",
    "food-order.php",
    "food-ticket.php",
    "food-ticket-web/assets/food-menu.js",
    "food-ticket-web/index.html",
    "help-guide.php",
    "index.php",
    "install-empty-db.sql",
    "permissions.php",
    "schema.sql",
    "lib/auth-scope.php",
    "assets/food-order.css",
    "food-ticket-web/assets/food-order.css",
}


class ReleasePackageTest(unittest.TestCase):
    def test_cumulative_manifest_contents_and_immutable_baseline(self) -> None:
        self.assertTrue(PACKAGE.is_file(), "cumulative ZIP missing")
        self.assertTrue(PREVIOUS.is_file(), "1.62.72 baseline ZIP missing")
        checksum_path = PACKAGE.with_name(PACKAGE.name + ".sha256")
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        self.assertEqual(digest, EXPECTED_PACKAGE_SHA256)
        checksum_parts = checksum_path.read_text(encoding="ascii").split()
        self.assertEqual(checksum_parts[0], digest)
        self.assertEqual(checksum_parts[1], PACKAGE.name)
        self.assertEqual(hashlib.sha256(PREVIOUS.read_bytes()).hexdigest(), OLD_ARCHIVE_SHA256)

        with zipfile.ZipFile(PREVIOUS) as old, zipfile.ZipFile(PACKAGE) as new:
            self.assertIsNone(new.testzip())
            manifest = json.loads(new.read("manifest.json"))
            old_manifest = json.loads(old.read("manifest.json"))
            self.assertEqual(manifest["version"], "1.62.73")
            self.assertEqual(manifest["requires_version"], "1.62.58")
            self.assertEqual(manifest["panel_version"], "1.37.52")
            self.assertEqual(manifest["date"], "2026-11-04")
            self.assertEqual(
                manifest["migrations"],
                ["020-traffic-destination-upgrade.sql", "021-food-auto-receipts.sql"],
            )
            self.assertEqual(new.read("files/VERSION"), b"1.62.73\n")

            old_files = set(old_manifest["files"])
            new_files = set(manifest["files"])
            self.assertTrue(old_files <= new_files)
            self.assertEqual(new_files - old_files, EXPECTED_ADDITIONS)
            self.assertEqual(
                set(new.namelist()),
                {"manifest.json", "migrations/019-traffic-destination-link.sql",
                 "migrations/020-traffic-destination-upgrade.sql",
                 "migrations/021-food-auto-receipts.sql"}
                | {"files/" + relative for relative in new_files},
            )
            self.assertEqual(len(new.namelist()), len(set(new.namelist())))

            actually_changed: set[str] = set()
            for relative in old_files:
                before = old.read("files/" + relative)
                after = new.read("files/" + relative)
                if before != after:
                    actually_changed.add(relative)
                if relative in POST_RELEASE_WORKTREE_CHANGES:
                    self.assertNotEqual(after, (ROOT / relative).read_bytes(), relative)
                elif relative not in EXPECTED_REPLACEMENTS:
                    self.assertEqual(after, before, relative)
                else:
                    self.assertEqual(after, (ROOT / relative).read_bytes(), relative)
            self.assertEqual(actually_changed, EXPECTED_REPLACEMENTS)

            for relative in EXPECTED_ADDITIONS:
                self.assertEqual(new.read("files/" + relative), (ROOT / relative).read_bytes(), relative)
            self.assertEqual(
                new.read("files/migrations/021-food-auto-receipts.sql"),
                new.read("migrations/021-food-auto-receipts.sql"),
            )
            self.assertEqual(
                new.read("files/assets/food-menu.js"),
                new.read("files/food-ticket-web/assets/food-menu.js"),
            )

    def test_download_page_points_to_latest_dashboard_cleanup_archive(self) -> None:
        page = (ROOT / "downloads/index.html").read_text(encoding="utf-8")
        latest = ROOT / "downloads/ticket-update-1.62.73-food-dashboard-cleanup-20261104-r2.zip"
        self.assertTrue(latest.is_file())
        digest = hashlib.sha256(latest.read_bytes()).hexdigest()
        self.assertIn(latest.name, page)
        self.assertIn(latest.name + ".sha256", page)
        self.assertIn(digest, page)
        github_branch = "https://github.com/alirezarahimy-code/ticket/"
        self.assertIn(github_branch + "raw/refs/heads/arena/642158e3-ticket/downloads/" + latest.name, page)
        self.assertIn(github_branch + "raw/refs/heads/arena/642158e3-ticket/downloads/" + latest.name + ".sha256", page)
        self.assertIn(github_branch + "blob/arena/642158e3-ticket/downloads/CHANGE-REPORT-FA-1.62.73-DASHBOARD-R2.md", page)
        self.assertNotIn("پس از ساخت بسته درج می‌شود", page)


if __name__ == "__main__":
    unittest.main()
