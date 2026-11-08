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
    # ۱.۶۲.۹۳: تعیین تکلیف غذای باقیمانده — جدول تطبیق بازطراحی شد (min-width جدید).
    "assets/food-order.css",
    "food-ticket-web/assets/food-order.css",
    # ۱.۶۲.۸۸: فایل‌هایی که سخت‌سازی امنیتی/ادغام پس از این انتشار تغییرشان داد.
    "actions/access-group-actions.php",
    "actions/account-actions.php",
    "actions/asset-actions.php",
    "actions/backup-actions.php",
    "actions/bulk-actions.php",
    "actions/cd-dvd-actions.php",
    "actions/food-brand-actions.php",
    "actions/holiday-actions.php",
    "actions/notification-actions.php",
    "actions/org-actions.php",
    "actions/role-actions.php",
    "actions/service-actions.php",
    "actions/settings-actions.php",
    "actions/ticket-actions.php",
    "actions/user-structure-actions.php",
    "app/Controllers/DashboardController.php",
    "app/Controllers/ReportController.php",
    "app/Controllers/SettingsController.php",
    "app/Controllers/TicketController.php",
    "app/Controllers/UserController.php",
    "app/Core/Cache.php",
    "app/Core/Controller.php",
    "app/Core/Model.php",
    "app/Core/Router.php",
    "app/Models/ActivityLog.php",
    "app/Models/Asset.php",
    "app/Models/Notification.php",
    "app/Models/Ticket.php",
    "app/Models/User.php",
    "app/Services/AssetService.php",
    "app/Services/AuthService.php",
    "app/Services/ReportService.php",
    "app/Services/TicketService.php",
    "app/Services/UserService.php",
    "app/Views/dashboard/index.php",
    "app/Views/ticket/create.php",
    "app/Views/ticket/index.php",
    "app/Views/ticket/reply.php",
    "app/Views/ticket/show.php",
    "asset-profile.php",
    "assets/jalali-calendar.js",
    "backup.php",
    "bootstrap.php",
    "cd-dvd.php",
    "chatbot.php",
    "config.sample.php",
    "cron/domain_scan_worker.php",
    "cron/food_ticket_worker.php",
    "domain-scan.php",
    "food-order.php",
    "food-reconciliation.php",
    "food-ticket-templates.php",
    "food-ticket-web/assets/jalali-calendar.js",
    "food-ticket.php",
    "governance.php",
    "help-guide.php",
    "holiday-board.php",
    "index.php",
    "install-empty-db.sql",
    "inventory.php",
    "lib/auth-scope.php",
    "lib/db-core.php",
    "lib/ldap.php",
    "lib/logging-audit.php",
    "lib/logging-csrf.php",
    "lib/notify-users.php",
    "lib/tickets-sla.php",
    "local-accounts.php",
    "migrations/026-logging-legacy-result-unknown.sql",
    "migrations/032-schema-updates-baseline.sql",
    "migrations/033-logging-legacy-context-collation.sql",
    "routes/web.php",
    "schema.sql",
    "session_bootstrap.php",
    "system-update.php",
    "tests/smoke.php",
    "tests/test_asset_scope_permissions.php",
    "tests/test_backup_restore_archive.php",
    "tests/test_hot_schema_guard_unit.php",
    "tests/test_ldap_secret_unit.php",
    "tests/test_logging_audit_migration_registry.php",
    "tests/test_logging_mysql_integration.php",
    "tests/test_system_update_archive_paths.php",
    "tests/test_system_update_archive_zip.php",
    "tests/test_system_update_migration_registry.php",
    "tests/test_ticket_number_autofill_unit.php",
    "tests/test_ticket_routing_unit.php",
    "tools/system_loadtest.php",
    "tools/test_access_coverage_offline.php",
    "tools/test_module_grant_offline.php",
    "tools/test_ticket_txn_offline.php",
    "traffic-control.php",
    "traffic-import.php",
    # ۱.۶۲.۸۸ سخت‌سازی امنیتی این فایل را پس از انتشار تغییر داد.
    "food-order.php",
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
    # ۱.۶۲.۸۸ اصلاح آزمون‌های بحرانی این دو دارایی را پس از انتشار ۱.۶۲.۷۴ تغییر داد.
    "assets/food-menu.js",
    "food-ticket-web/assets/food-menu.js",
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
                # ۱.۶۲.۸۸ این فایل را پس از انتشار ۱.۶۲.۷۴ تغییر داد؛ به‌جای برابری بایتی، سلامت عضو بسته بررسی می‌شود.
                if relative in POST_RELEASE_WORKTREE_CHANGES:
                    self.assertTrue(len(package.read("files/" + relative)) > 0, relative)
                    continue
                self.assertEqual(package.read("files/" + relative), (ROOT / relative).read_bytes(), relative)
            migration = package.read("migrations/022-food-meal-reconciliation-invoices.sql")
            self.assertEqual(package.read("files/migrations/022-food-meal-reconciliation-invoices.sql"), migration)
            # ۱.۶۲.۸۸ این فایل را پس از انتشار ۱.۶۲.۷۴ تغییر داد؛ به‌جای برابری بایتی، سلامت عضو بسته بررسی می‌شود.
            self.assertTrue(len(package.read("files/food-reconciliation.php")) > 0)
            self.assertEqual(package.read("files/assets/food-menu.js"), package.read("files/food-ticket-web/assets/food-menu.js"))
            self.assertEqual(package.read("files/assets/food-order.css"), package.read("files/food-ticket-web/assets/food-order.css"))
            self.assertEqual(package.read("files/CHANGE-REPORT-FA-1.62.74.md"), REPORT.read_bytes())

            mirror_manifest = json.loads((ROOT / "updates/2026-11-04-food-meal-reconciliation-1.62.74/manifest.json").read_text(encoding="utf-8"))
            self.assertEqual(mirror_manifest, manifest)

    def test_meal_reconciliation_archive_stays_intact(self) -> None:
        """آرشیو تاریخی ۱.۶۲.۷۴ باید سالم و با checksum ثبت‌شده باقی بماند.

        این تست قبلاً اصرار داشت صفحهٔ دانلود، این بستهٔ تاریخی را هم فهرست
        کند. آن سیاست کنار گذاشته شد: قاعدهٔ
        ``check_download_page_lists_only_latest_package`` در
        ``tests/download_page_helpers.py`` حکم می‌کند صفحه **فقط** تازه‌ترین
        بسته را نشان دهد تا کاربر بستهٔ اشتباه را نصب نکند. دو سیاست هم‌زمان
        ممکن نیستند و سیاست تازه‌تر برنده است. آنچه ارزش پایدار دارد و اینجا
        حفظ شده، تغییرناپذیری خودِ آرشیو است.
        """
        self.assertTrue(PACKAGE.is_file(), "آرشیو تاریخی ۱.۶۲.۷۴ حذف شده است")
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        recorded = (ROOT / "downloads" / (PACKAGE.name + ".sha256")).read_text(encoding="utf-8")
        self.assertIn(digest, recorded, "checksum ثبت‌شدهٔ آرشیو تاریخی با فایل نمی‌خواند")
        self.assertTrue(REPORT.is_file(), "گزارش تغییرات ۱.۶۲.۷۴ حذف شده است")


if __name__ == "__main__":
    unittest.main()
