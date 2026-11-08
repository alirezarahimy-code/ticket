"""بررسی ایستاهای بستهٔ میانی ۱.۶۲.۹۳ — تعیین تکلیف غذای باقیمانده و اصلاحات «تطبیق روزانه».

این تست محتوای مخزن و بستهٔ ساخته‌شده در downloads/ را بررسی می‌کند (بدون اجرای PHP).
"""
from __future__ import annotations

import hashlib
import json
import re
import unittest
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MIGRATION = ROOT / "migrations/036-food-meal-leftover-disposition.sql"
PHP = ROOT / "food-reconciliation.php"
JS_PRIMARY = ROOT / "assets/food-menu.js"
JS_MIRROR = ROOT / "food-ticket-web/assets/food-menu.js"
CSS_PRIMARY = ROOT / "assets/food-menu.css"
CSS_MIRROR = ROOT / "food-ticket-web/assets/food-menu.css"
ORDER_CSS_PRIMARY = ROOT / "assets/food-order.css"
ORDER_CSS_MIRROR = ROOT / "food-ticket-web/assets/food-order.css"
PACKAGE = ROOT / "downloads/ticket-update-1.62.90-to-1.62.93-food-reconciliation-leftover-20261106.zip"
SHA_SIDECAR = ROOT / "downloads/ticket-update-1.62.90-to-1.62.93-food-reconciliation-leftover-20261106.zip.sha256"
REPORT = ROOT / "downloads/CHANGE-REPORT-FA-1.62.93.md"


class Migration036Tests(unittest.TestCase):
    def test_migration_is_additive_and_idempotent(self) -> None:
        sql = MIGRATION.read_text(encoding="utf-8")
        self.assertIn("ALTER TABLE food_meal_reconciliations", sql)
        for column in (
            "leftover_staff_quantity",
            "leftover_waste_quantity",
            "leftover_keep_quantity",
            "leftover_other_quantity",
            "leftover_other_note",
        ):
            self.assertIn(f"ADD COLUMN IF NOT EXISTS {column}", sql)
        uppers = sql.upper()
        self.assertNotIn("DROP ", uppers)
        self.assertNotIn("TRUNCATE", uppers)
        self.assertNotIn("DELETE FROM", uppers)
        self.assertNotIn("MODIFY", uppers)

    def test_fresh_install_schemas_match_migrated_schema(self) -> None:
        for name in ("schema.sql", "install-empty-db.sql"):
            text = (ROOT / name).read_text(encoding="utf-8")
            for column in (
                "leftover_staff_quantity INT UNSIGNED NULL",
                "leftover_waste_quantity INT UNSIGNED NULL",
                "leftover_keep_quantity INT UNSIGNED NULL",
                "leftover_other_quantity INT UNSIGNED NULL",
                "leftover_other_note VARCHAR(500) NULL",
            ):
                self.assertIn(column, text, f"{name} is missing {column}")


class ReconciliationPhpTests(unittest.TestCase):
    def test_backend_exposes_new_contract_only(self) -> None:
        text = PHP.read_text(encoding="utf-8")
        for needle in (
            "food_reconciliation_disposition_parse",
            "food_reconciliation_served_counts",
            "food_reconciliation_absentee_counts",
            "food_reconciliation_disposition_summary",
            "leftover_staff_quantity",
            "leftover_disposition_summary",
            "remaining_quantity",
            "order_gap",
            "absentees",
            "تعیین تکلیف غذای باقیمانده",
        ):
            self.assertIn(needle, text)
        for removed in (
            "unserved_outside_registered_absence",
            "unserved_employee_demand",
            "shortage_quantity",
            "registered_absences",
            "demand_at_order",
            "food_ticket_daily_absence",
            "'غیبت ثبت‌شده'",
            "'دورریز ثبت‌شده'",
        ):
            self.assertNotIn(removed, text)

    def test_export_headers_use_new_columns(self) -> None:
        text = PHP.read_text(encoding="utf-8")
        for needle in ("'غایبین'", "'سرنوشت باقیمانده'", "'کسری/اضافهٔ سفارش'", "'پرس باقیمانده'", "'پرس تحویل واقعی'"):
            self.assertIn(needle, text)
        self.assertNotIn("'تقاضا هنگام ثبت خرید'", text)
        self.assertNotIn("'بی‌توزیع", text)

    def test_remaining_basis_is_received_minus_served(self) -> None:
        text = PHP.read_text(encoding="utf-8")
        self.assertIn("'remaining_quantity' => $receivedQuantity === null ? null : $receivedQuantity - $served", text)


class ReconciliationJsTests(unittest.TestCase):
    def test_js_copies_stay_byte_identical(self) -> None:
        self.assertEqual(JS_PRIMARY.read_bytes(), JS_MIRROR.read_bytes())
        self.assertEqual(CSS_PRIMARY.read_bytes(), CSS_MIRROR.read_bytes())
        self.assertEqual(ORDER_CSS_PRIMARY.read_bytes(), ORDER_CSS_MIRROR.read_bytes())

    def test_modal_and_new_columns_present(self) -> None:
        for path in (JS_PRIMARY, JS_MIRROR):
            text = path.read_text(encoding="utf-8")
            for needle in (
                "renderLeftoverModal",
                "confirmLeftoverModal",
                "تعیین تکلیف غذای باقیمانده",
                "امروز برابر با",
                "توزیع بین پرسنل همین شرکت",
                "دورریز (ضایعات)",
                "نگه‌داری برای وعدهٔ بعد",
                "سایر",
                "کسر سفارش",
                "اضافه سفارش",
                "غایبین",
                "سرنوشت باقیمانده",
                "پرس باقیمانده",
            ):
                self.assertIn(needle, text, f"{path} is missing {needle}")
            self.assertNotIn('name="waste_quantity"', text)
            self.assertNotIn('name="employee_served_quantity"', text)
            self.assertNotIn('name="guest_served_quantity"', text)
            self.assertNotIn("غیبت ثبت‌شده", text)
            self.assertNotIn("بی‌توزیع", text)


class PackageTests(unittest.TestCase):
    def test_package_exists_and_is_consistent(self) -> None:
        self.assertTrue(PACKAGE.exists(), f"missing package: {PACKAGE}")
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        if SHA_SIDECAR.exists():
            sidecar = SHA_SIDECAR.read_text(encoding="utf-8").split()[0].strip()
            self.assertEqual(digest, sidecar)
        with zipfile.ZipFile(PACKAGE) as zf:
            names = zf.namelist()
            self.assertIn("manifest.json", names)
            self.assertIn("migrations/036-food-meal-leftover-disposition.sql", names)
            self.assertIn("files/migrations/036-food-meal-leftover-disposition.sql", names)
            manifest = json.loads(zf.read("manifest.json"))
        self.assertEqual(manifest["version"], "1.62.93")
        self.assertEqual(manifest["requires_version"], "1.62.90")
        self.assertEqual(manifest["migrations"], ["036-food-meal-leftover-disposition.sql"])
        for required in (
            "food-reconciliation.php",
            "assets/food-menu.js",
            "food-ticket-web/assets/food-menu.js",
            "assets/food-menu.css",
            "food-ticket-web/assets/food-menu.css",
            "assets/food-order.css",
            "food-ticket-web/assets/food-order.css",
            "schema.sql",
            "install-empty-db.sql",
            "VERSION",
            "CHANGE-REPORT-FA-1.62.93.md",
        ):
            self.assertIn(required, manifest["files"])
        with zipfile.ZipFile(PACKAGE) as zf:
            self.assertEqual(zf.read("files/VERSION").decode().strip(), "1.62.93")
        # بستهٔ غذا نباید هیچ اثری از ارتقاء مرکز درخواست داشته باشد.
        for name in names:
            lowered = name.lower()
            self.assertNotIn("request-center", lowered)
            self.assertNotIn("request_center", lowered)
            self.assertNotIn("035-request", lowered)

    def test_change_report_present(self) -> None:
        self.assertTrue(REPORT.exists())
        text = REPORT.read_text(encoding="utf-8")
        self.assertIn("1.62.93", text)
        self.assertIn("036", text)


if __name__ == "__main__":
    unittest.main()
