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
    # ۱.۶۲.۸۸ سخت‌سازی امنیتی: گارد ورود، اعتبارسنجی نام مستعار و تراکنش.
    "actions/access-group-actions.php",
    "VERSION",
    "README.md",
    "index.php",
    "lib/activity-audit.php",
    # ۱.۶۲.۷۹ ستون‌های «بازهٔ کاری دستگاه تردد» را به schema.sql افزود تا نصب تازه هم
    # آن‌ها را داشته باشد (install.php پوشهٔ migrations/ را اجرا نمی‌کند).
    "schema.sql",
    "install-empty-db.sql",
    # ۱.۶۲.۸۰ بخش «کدام بستهٔ به‌روزرسانی را نصب کنم؟» را به راهنما افزود.
    "help-guide.php",
    # ۱.۶۲.۸۱ بند ۱۱ (ممیزی دورهٔ‌ای سامانه) را به سند معماری افزود.
    "docs/ARCHITECTURE-FA.md",
    # ۱.۶۲.۸۴ قاعدهٔ تفکیک وظایف را داخل ticket_handler_level() نشاند و دو تابع
    # ticket_view_mode() و ticket_can_see_internal() را افزود، تا نمای صفحه تابع
    # نسبت کاربر با همان درخواست باشد نه نقش سراسری او.
    "lib/auth-scope.php",
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

    def test_public_access_groups_archive_stays_intact(self) -> None:
        """آرشیو تاریخی ۱.۶۲.۷۵ باید سالم و با checksum ثبت‌شده باقی بماند.

        این تست قبلاً اصرار داشت صفحهٔ دانلود، این بستهٔ تاریخی را هم فهرست
        کند. آن سیاست کنار گذاشته شد: قاعدهٔ
        ``check_download_page_lists_only_latest_package`` در
        ``tests/download_page_helpers.py`` حکم می‌کند صفحه **فقط** تازه‌ترین
        بسته را نشان دهد تا کاربر بستهٔ اشتباه را نصب نکند. دو سیاست هم‌زمان
        ممکن نیستند و سیاست تازه‌تر برنده است. آنچه ارزش پایدار دارد و اینجا
        حفظ شده، تغییرناپذیری خودِ آرشیو است.
        """
        self.assertTrue(PACKAGE.is_file(), "آرشیو تاریخی ۱.۶۲.۷۵ حذف شده است")
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        recorded = (ROOT / "downloads" / (PACKAGE.name + ".sha256")).read_text(encoding="utf-8")
        self.assertIn(digest, recorded, "checksum ثبت‌شدهٔ آرشیو تاریخی با فایل نمی‌خواند")
        self.assertTrue(REPORT.is_file(), "گزارش تغییرات ۱.۶۲.۷۵ حذف شده است")


if __name__ == "__main__":
    unittest.main()
