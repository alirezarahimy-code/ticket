"""بررسی ایستای بستهٔ ۱.۶۳.۳ — بستن روز، پاپ‌آپ انتساب مهمان و یک‌دست‌سازی ۶ بخش سازمانی (بند ۱۷).

این تست فقط محتوای مخزن و بستهٔ ساخته‌شده در downloads/ را بررسی می‌کند (بدون اجرای PHP).
"""
from __future__ import annotations

import hashlib
import json
import re
import unittest
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MIGRATION_038 = ROOT / "migrations/038-food-guest-actuals.sql"
MIGRATION_039 = ROOT / "migrations/039-liaison-manager-role-sync.sql"
MIGRATION_040 = ROOT / "migrations/040-org-routing-departments.sql"
RECON = ROOT / "food-reconciliation.php"
ORDER_PHP = ROOT / "food-order.php"
TICKET_PHP = ROOT / "food-ticket.php"
ORG_PHP = ROOT / "organization.php"
JS_PRIMARY = ROOT / "assets/food-menu.js"
JS_MIRROR = ROOT / "food-ticket-web/assets/food-menu.js"
CSS_PRIMARY = ROOT / "assets/food-menu.css"
CSS_MIRROR = ROOT / "food-ticket-web/assets/food-menu.css"
GROUPS_PRIMARY = ROOT / "assets/food-org-groups.js"
GROUPS_MIRROR = ROOT / "food-ticket-web/assets/food-org-groups.js"
INDEX_HTML = ROOT / "food-ticket-web/index.html"
GUIDE = ROOT / "help-guide.php"
BUILD_PY = ROOT / "updates/2026-11-19-release-1.63.3-absent-lunch-report/build.py"
PACKAGE = ROOT / "downloads/ticket-update-1.63.2-to-1.63.3-absent-lunch-report-20261119.zip"
SHA_SIDECAR = ROOT / "downloads/ticket-update-1.63.2-to-1.63.3-absent-lunch-report-20261119.zip.sha256"
REPORT = ROOT / "updates/2026-11-19-release-1.63.3-absent-lunch-report/src/CHANGE-REPORT-FA.md"


def read(path: Path) -> str:
    return path.read_text(encoding="utf-8")


class Migration038Tests(unittest.TestCase):
    def test_additive_and_idempotent(self) -> None:
        sql = read(MIGRATION_038)
        self.assertIn("CREATE TABLE IF NOT EXISTS food_guest_actuals", sql)
        self.assertIn("UNIQUE KEY", sql)
        self.assertIn("(food_date, meal_type, root_unit_id)", sql.replace("\n", " "))
        uppers = sql.upper()
        self.assertNotIn("DROP ", uppers)
        self.assertNotIn("TRUNCATE", uppers)
        self.assertNotIn("DELETE FROM", uppers)

    def test_fresh_install_schemas_match(self) -> None:
        for name in ("schema.sql", "install-empty-db.sql"):
            text = read(ROOT / name)
            self.assertIn("CREATE TABLE IF NOT EXISTS food_guest_actuals", text)
            self.assertIn("uq_food_guest_actuals_day", text)


class Migration039Tests(unittest.TestCase):
    def test_additive_and_idempotent(self) -> None:
        sql = read(MIGRATION_039)
        self.assertIn("ADD COLUMN IF NOT EXISTS org_auto_role", sql)
        self.assertIn("ADD COLUMN IF NOT EXISTS org_prev_role", sql)
        self.assertNotIn("DROP ", sql.upper())

    def test_only_user_agent_promoted(self) -> None:
        sql = read(MIGRATION_039)
        self.assertEqual(sql.count("role IN ('user', 'agent')"), 2)
        self.assertNotIn("supervisor', 'agent", sql)

    def test_liaison_managers_targeted(self) -> None:
        sql = read(MIGRATION_039)
        self.assertEqual(sql.count("unit_type = 'liaison'"), 2)


class GuestActualsBackendTests(unittest.TestCase):
    def test_functions_exist(self) -> None:
        php = read(RECON)
        for fn in ("food_guest_actuals_schema_ensure", "food_guest_actuals_report", "food_guest_actuals_save"):
            self.assertIn(f"function {fn}(", php)

    def test_exact_equality_enforced(self) -> None:
        php = read(RECON)
        self.assertIn("$sum !== $printed", php)

    def test_zero_printed_blocks_save(self) -> None:
        php = read(RECON)
        self.assertIn("فیش مهمان چاپ‌شده‌ای برای این روز/وعده ثبت نشده است", php)

    def test_closed_day_guard(self) -> None:
        php = read(RECON)
        save_start = php.index("function food_guest_actuals_save(")
        save_body = php[save_start:php.index("function food_reconciliation_api_handle(", save_start)]
        self.assertIn("food_meal_closed_guard(", save_body)

    def test_actual_routes_registered(self) -> None:
        recon = read(RECON)
        self.assertIn("$route === 'food-menu/guest-actuals'", recon)
        ticket = read(TICKET_PHP)
        self.assertEqual(ticket.count("'food-menu/guest-actuals'"), 2)
        order = read(ORDER_PHP)
        self.assertIn("'food-menu/guest-actuals'", order)

    def test_xlsx_column_added(self) -> None:
        recon = read(RECON)
        self.assertIn("'مهمان واقعی (به‌تفکیک بخش)'", recon)
        self.assertIn("'guest_actuals_summary'", recon)

    def test_leftover_exact_match_kept(self) -> None:
        recon = read(RECON)
        self.assertIn("food_reconciliation_disposition_parse", recon)

    def test_served_counts_guest_source(self) -> None:
        recon = read(RECON)
        section = recon[recon.index("function food_reconciliation_served_counts("):]
        self.assertIn("'guest','auto_guest'", section[:2000].replace(" ", ""))


class GuestActualsFrontendTests(unittest.TestCase):
    def test_popup_renders_before_leftover(self) -> None:
        js = read(JS_PRIMARY)
        match = re.search(r"renderGuestActualModal\(\)\s*\+?\s*renderLeftoverModal\(\)", js)
        self.assertIsNotNone(match, "پاپ‌آپ انتساب مهمان باید قبل از پاپ‌آپ سرنوشت باقیمانده رندر شود")

    def test_leftover_flow_preceded_by_actual_flow(self) -> None:
        js = read(JS_PRIMARY)
        self.assertIn("function proceedToLeftover(", js)
        self.assertIn("guest-actual-confirm", js)
        self.assertIn("guest-actual-cancel", js)
        self.assertIn("food-menu/guest-actuals", js)

    def test_client_side_sum_check(self) -> None:
        js = read(JS_PRIMARY)
        self.assertIn("guestActualSumFromDom", js)
        self.assertIn("باید دقیقاً برابر", js)

    def test_mirror_identical(self) -> None:
        self.assertEqual(JS_PRIMARY.read_bytes(), JS_MIRROR.read_bytes())
        self.assertEqual(CSS_PRIMARY.read_bytes(), CSS_MIRROR.read_bytes())

    def test_popup_css_classes(self) -> None:
        css = read(CSS_PRIMARY)
        for cls in (".fm-ga-row", ".fm-ga-input", ".fm-ga-sum", ".fm-ga-head", ".fm-ga-modal"):
            self.assertIn(cls, css)
        self.assertNotIn("@import", css)

    def test_none_row_chart_accuracy_warning(self) -> None:
        js = read(JS_PRIMARY)
        self.assertIn("fm-ga-row-none", js)
        self.assertIn("fm-ga-none-note", js)
        self.assertIn("unit_type || '') === 'none' || Number(section.id) === 0", js)
        css = read(CSS_PRIMARY)
        for cls in (".fm-ga-row-none", ".fm-ga-none-mark", ".fm-ga-none-note"):
            self.assertIn(cls, css)

    def test_guest_requests_report_row_scoped(self) -> None:
        ticket = read(TICKET_PHP)
        self.assertIn("function food_ticket_api_guest_requests_report(string $from, string $to, string $deputy, array $user = []): array", ticket)
        self.assertIn("food_guest_visible_root_ids($user)", ticket)
        self.assertIn("food_guest_stored_root_cached($r['deputy_unit_id'] ?? 0)", ticket)
        self.assertIn("'scope' => $scope", ticket)
        self.assertIn("!in_array((int) $deputy, $visibleRoots, true)", ticket)
        self.assertIn("$totalActual", read(RECON))

    def test_visible_root_ids_includes_enclosing_section_of_managed_units(self) -> None:
        # تصمیم کاربر (۲۰۲۶-۱۱-۲۰): مدیر اداره همان معاونت/بخش بالادستی خود را می‌بیند؛ اداره موضوعیت ندارد.
        order = read(ORDER_PHP)
        self.assertIn("function food_guest_managed_unit_ids(", order)
        self.assertIn("function food_guest_nearest_deputy_id(", order)
        self.assertIn("function food_guest_umbrella_id(", order)
        self.assertIn("function food_guest_umbrella_root_ids(", order)
        section = order[order.index("function food_guest_visible_root_ids("):]
        self.assertIn("food_guest_managed_unit_ids($user)", section[:2500])
        self.assertIn("food_guest_umbrella_id((int) $unit)", section[:2500])

    def test_toggle_labels_use_combined_section_wording(self) -> None:
        for source in (JS_PRIMARY, JS_MIRROR):
            js = source.read_text(encoding="utf-8")
            self.assertNotIn(">نمایش بر اساس معاونت<", js)
            self.assertIn("نمایش بر اساس معاونت / بخش", js)
        html = read(INDEX_HTML)
        self.assertIn("نمایش بر اساس معاونت / بخش", html)


class OrgSectionsTests(unittest.TestCase):
    def test_liaison_in_position_types(self) -> None:
        php = read(ORG_PHP)
        section = php[php.index("function org_position_unit_types("):]
        self.assertIn("'liaison'", section[:600])

    def test_routing_accepts_all_six_roots(self) -> None:
        php = read(ORG_PHP)
        section = php[php.index("function org_unit_routing_department_id("):]
        self.assertIn("org_root_types()", section[:800])

    def test_guest_create_blocked_without_chart(self) -> None:
        php = read(ORDER_PHP)
        create_start = php.index("function food_guest_request_create(")
        create_body = php[create_start:php.index("function food_guest_request_cancel(", create_start)]
        self.assertIn("food_guest_user_deputy_id($user, true)", create_body)
        self.assertIn("جایگاه فعالی ندارد", create_body)

    def test_guest_update_owner_blocked_without_chart(self) -> None:
        php = read(ORDER_PHP)
        update_start = php.index("function food_guest_request_update(")
        update_body = php[update_start:php.index("function food_guest_request_delete(", update_start)]
        self.assertIn("$ownerOnly && food_guest_user_deputy_id($user, true) <= 0", update_body)

    def test_guest_list_scoped_visibility(self) -> None:
        php = read(ORDER_PHP)
        self.assertIn("function food_guest_visible_root_ids(", php)
        self.assertIn("$isScopedViewer", php)
        self.assertIn("تابع food_guest_stored_root_cached".replace("تابع ", "function "), php)

    def test_guest_report_uses_six_roots(self) -> None:
        php = read(TICKET_PHP)
        section = php[php.index("function food_ticket_api_guest_requests_report("):]
        self.assertIn("org_root_units()", section[:1200])
        self.assertNotIn("org_deputy_units()", section[:1200])

    def test_report_rows_normalized_to_roots(self) -> None:
        php = read(TICKET_PHP)
        section = php[php.index("function food_guest_actuals_requested_map"):] if "function food_guest_actuals_requested_map" in php else ""
        # food_guest_actuals_requested_map lives in food-reconciliation.php
        recon = read(RECON)
        self.assertIn("org_stored_attribution_root_id", recon)

    def test_groups_js_mirror_identical(self) -> None:
        self.assertEqual(GROUPS_PRIMARY.read_bytes(), GROUPS_MIRROR.read_bytes())
        js = read(GROUPS_PRIMARY)
        self.assertIn("'بدون جایگاه'", js)

    def test_detail_uses_no_an_anchor(self) -> None:
        detail = read(ROOT / "modules/request-center/Pages/detail.php")
        self.assertIn("بدون جایگاه", detail)
        self.assertNotIn("بدون بخش", detail)

    def test_guide_mentions_chart_requirement(self) -> None:
        guide = read(GUIDE)
        self.assertIn("عضو نبودن در چارت", guide)

    def test_no_dangling_org_group_root_types(self) -> None:
        for rel in ("lib/org-grouping.php", "organization.php", "food-order.php", "food-ticket.php",
                    "assets/food-org-groups.js", "assets/food-menu.js"):
            self.assertNotIn("org_group_root_types", read(ROOT / rel))


class CacheBustTests(unittest.TestCase):
    def test_panel_assets_busted_for_1633(self) -> None:
        html = read(INDEX_HTML)
        for asset in ("assets/food-menu.css?v=1.63.3", "assets/food-menu.js?v=1.63.3", "assets/food-org-groups.js?v=1.63.3"):
            self.assertIn(asset, html)

    def test_order_guest_busted(self) -> None:
        php = read(ORDER_PHP)
        self.assertIn('src="assets/food-order-guest.js?v=1.63.3"', php)


class PackageTests(unittest.TestCase):
    def test_manifest_and_zip(self) -> None:
        self.assertTrue(PACKAGE.is_file(), "بستهٔ ۱.۶۳.۳ ساخته نشده است")
        with zipfile.ZipFile(PACKAGE) as zf:
            names = set(zf.namelist())
            self.assertIn("manifest.json", names)
            self.assertIn("migrations/038-food-guest-actuals.sql", names)
            self.assertIn("migrations/039-liaison-manager-role-sync.sql", names)
            self.assertIn("migrations/040-org-routing-departments.sql", names)
            self.assertIn("files/food-reconciliation.php", names)
            self.assertIn("files/organization.php", names)
            self.assertIn("files/modules/request-center/Pages/detail.php", names)
            manifest = json.loads(zf.read("manifest.json").decode("utf-8"))
            self.assertEqual(manifest["version"], "1.63.3")
            self.assertEqual(manifest["panel_version"], "1.37.61")
            self.assertEqual(manifest["migrations"], ["038-food-guest-actuals.sql", "039-liaison-manager-role-sync.sql", "040-org-routing-departments.sql"])
            archived = {n[len("files/"):] for n in names if n.startswith("files/")}
            self.assertEqual(archived, set(manifest["files"]))
            for rel, digest in manifest["sha256"].items():
                self.assertEqual(hashlib.sha256(zf.read("files/" + rel)).hexdigest(), digest)

    def test_sha256_sidecar_matches(self) -> None:
        self.assertTrue(SHA_SIDECAR.is_file())
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        self.assertIn(digest, SHA_SIDECAR.read_text(encoding="utf-8").split()[0])

    def test_packaged_verbatim_files_match_repo(self) -> None:
        # فایل‌های verbatim بسته باید دقیقاً همان نسخهٔ مخزن باشند؛ فایل‌های تبدیل‌شده جداگانه بررسی می‌شوند.
        verbatim = ("assets/food-menu.css", "assets/food-menu.js", "assets/food-order-guest.js",
                    "assets/food-org-groups.js", "cd-dvd.php", "food-order-import.php",
                    "food-order.php", "food-reconciliation.php", "food-ticket-auto-receipts.php",
                    "food-ticket-groups.php", "food-ticket-runtime.php",
                    "food-ticket-web/assets/food-menu.css", "food-ticket-web/assets/food-menu.js",
                    "food-ticket-web/assets/food-org-groups.js",
                    "lib/org-grouping.php", "organization.php",
                    "modules/request-center/Actions/ticket-actions.php",
                    "modules/request-center/Pages/create.php",
                    "modules/request-center/Pages/detail.php",
                    "modules/request-center/lib/functions.php")
        with zipfile.ZipFile(PACKAGE) as zf:
            for rel in verbatim:
                self.assertEqual(zf.read("files/" + rel), (ROOT / rel).read_bytes(), rel)
            self.assertIn(b"FOOD_TICKET_PANEL_VERSION = '1.37.61'", zf.read("files/food-ticket.php"))
            self.assertIn(b"PANEL_VERSION='1.37.61'", zf.read("files/food-ticket-web/index.html"))
            self.assertIn("absent-lunch".encode(), zf.read("files/food-ticket-web/index.html"))
            self.assertIn("food_absent_lunch_report".encode(), zf.read("files/chatbot.php"))
            self.assertIn("غایبین ناهار".encode(), zf.read("files/help-guide.php"))

    def test_build_py_panel_version(self) -> None:
        self.assertIn("PANEL_VERSION = '1.37.61'", read(BUILD_PY))

    def test_change_report_mentions_migrations(self) -> None:
        report = read(REPORT)
        self.assertIn("038-food-guest-actuals.sql", report)
        self.assertIn("1.37.61", report)


if __name__ == "__main__":
    unittest.main()
