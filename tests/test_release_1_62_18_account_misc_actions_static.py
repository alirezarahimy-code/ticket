# -*- coding: utf-8 -*-
"""تست‌های ایستای ریلیز ۱.۶۲.۱۸ — بازسازی فاز ۲، برش ۷:
جداسازی ۹ اکشن در ۴ فایل موضوعی: ورود/پروفایل/تم، برند فیش غذا، آرشیو CD/DVD و اعلان‌ها
بدون هیچ تغییر رفتاری + پین‌های بسته‌بندی/صفحهٔ دانلود طبق سیاست انتشار.
گروه «پشتیبان‌گیری/به‌روزرسانی سامانه» عمداً در index.php مانده است (برش جداگانه با احتیاط ویژه).
"""
import hashlib
import json
import re
import zipfile
from pathlib import Path
from download_page_helpers import check_download_page_lists_only_latest_package

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.18-account-misc-actions-split-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-18-account-misc-actions-split"
GUARD_END = "    exit;\n}\n\n"

FILES_AND_ACTIONS = {
    "actions/account-actions.php": ["login", "save_theme", "save_profile"],
    "actions/food-brand-actions.php": ["save_food_ticket_brand"],
    "actions/cd-dvd-actions.php": ["cd_dvd_create", "cd_dvd_update", "cd_dvd_delete"],
    "actions/notification-actions.php": ["mark_all_notifications", "mark_notification"],
}


def read(rel):
    return (ROOT / rel).read_text(encoding="utf-8")


def test_index_requires_all_four_files_in_original_order():
    src = read("index.php")
    order = ["account-actions", "food-brand-actions", "cd-dvd-actions",
             "notification-actions", "supervisor-actions"]
    positions = []
    for name in order:
        line = f"require __DIR__ . '/actions/{name}.php';"
        assert src.count(line) == 1, name
        positions.append(src.index(line))
    assert positions == sorted(positions)
    # از ۱.۶۲.۲۰ (برش ۹) گروه پشتیبان‌گیری در فایل جدا است؛ جایگاهش همان است (بین CD/DVD و اعلان‌ها)
    backup = src.index("require __DIR__ . '/actions/backup-actions.php';")
    assert positions[2] < backup < positions[3]
    assert "if ($action === 'system_update')" in read("actions/backup-actions.php")
    for actions in FILES_AND_ACTIONS.values():
        for action in actions:
            assert f"if ($action === '{action}')" not in src, action


def test_action_files_structure_and_persian_descriptions():
    for rel, actions in FILES_AND_ACTIONS.items():
        src = read(rel)
        assert src.startswith("<?php\n\ndeclare(strict_types=1);\n"), rel
        assert "if (!defined('APP_ROOT')) {" in src and GUARD_END in src, rel
        for action in actions:
            assert src.count(f"if ($action === '{action}')") == 1, (rel, action)
            assert re.search(rf"^ \*   {re.escape(action)}\s+\S", src, re.M), f"شرح فارسی سرآیند برای {action} نیست"
        body = src.split(GUARD_END, 1)[1]
        assert body.count("if ($action === ") == len(actions), rel
        # تعریف تابع نام‌دار ممنوع (کلوژر static بی‌نام در بلوک ورود مجاز است)
        assert not re.search(r"\bfunction\s+[A-Za-z_]", body), rel
        assert body.count("{") == body.count("}"), rel


def test_version_manifest_and_package():
    # VERSION از ۱.۶۲.۱۹ جلوتر رفته؛ پین نسخهٔ جاری در تست ریلیز ۱.۶۲.۱۹ است.
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.18"
    assert manifest["requires_version"] == "1.61.9"
    assert manifest["migrations"] == ["009-traffic-destination-multi-approver.sql", "010-org-liaison-units.sql"]
    files = manifest["files"]
    assert len(files) == 55 and len(set(files)) == 55
    for need in list(FILES_AND_ACTIONS) + ["VERSION", "index.php",
                                           "actions/org-actions.php", "actions/supervisor-actions.php",
                                           "food-ticket-web/assets/food-capitalio-dashboard-r42.js"]:
        assert need in files, need
    for f in files:
        assert (ROOT / f).is_file(), f

    package = ROOT / "downloads" / PACKAGE_NAME
    assert package.is_file()
    zf = zipfile.ZipFile(package)
    names = set(zf.namelist())
    pattern = re.compile(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$")
    for name in names:
        if not name.endswith("/"):
            assert pattern.match(name), name
    for mig in manifest["migrations"]:
        assert f"migrations/{mig}" in names and f"files/migrations/{mig}" in names, mig
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.18"
    # index.php در ۱.۶۲.۱۹ (برش ۸) تغییر کرد؛ برابری بایت‌به‌بایت با مخزن برای فایل‌های همین برش:
    # ۱.۶۲.۸۸ سخت‌سازی امنیتی این فایل را پس از انتشار تغییر داد.
    SUPERSEDED_BY_LATER_RELEASE = {
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
    }
    for rel in list(FILES_AND_ACTIONS):
        if rel in SUPERSEDED_BY_LATER_RELEASE:
            continue
        assert zf.read(f"files/{rel}") == (ROOT / rel).read_bytes(), rel
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")
    # صفحهٔ دانلود اکنون بستهٔ ۱.۶۲.۱۹ را نشان می‌دهد؛ پین SHA صفحه در تست ریلیز ۱.۶۲.۱۹ است.


def test_panel_asset_refs_are_all_packaged():
    """قاعدهٔ دائمی fix1: هر asset ارجاع‌شده در index.html پنل غذا باید داخل همان بسته باشد."""
    zf = zipfile.ZipFile(ROOT / "downloads" / PACKAGE_NAME)
    names = set(zf.namelist())
    html = zf.read("files/food-ticket-web/index.html").decode("utf-8")
    for ref in sorted(set(re.findall(r'(?:src|href)="(assets/[^"?]+)', html))):
        assert f"files/food-ticket-web/{ref}" in names, f"ارجاع بدون فایل در بسته: {ref}"


def test_download_page_lists_only_latest_package():
    # قاعدهٔ پروژه: صفحهٔ دانلود فقط جدیدترین بستهٔ منتشرشده را نشان می‌دهد.
    # بررسی نسخه‌آگنوستیک است تا با هر انتشار، تست کهنه نشود.
    check_download_page_lists_only_latest_package()

