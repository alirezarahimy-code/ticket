"""بررسی ایستاهای بستهٔ ۱.۶۲.۹۴ — مهمان جدا، کارت سوم «واحد مستقل»، اصلاحات گروه‌های دسترسی و تعطیلات رسمی.

این تست محتوای مخزن را بررسی می‌کند (بدون اجرای PHP). بستهٔ ساخته‌شدهٔ ۱.۶۲.۹۴
توسط تست بستهٔ نهایی پوشش داده می‌شود.
"""

from __future__ import annotations

import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
RECON_PHP = ROOT / "food-reconciliation.php"
JS_PRIMARY = ROOT / "assets/food-menu.js"
JS_MIRROR = ROOT / "food-ticket-web/assets/food-menu.js"
ORDER_JS = ROOT / "assets/food-order.js"
ORDER_PHP = ROOT / "food-order.php"
ORG_PHP = ROOT / "organization.php"
ORG_JS = ROOT / "assets/organization.js"
ORG_CSS = ROOT / "assets/style.css"
ORG_ACTIONS = ROOT / "actions/org-actions.php"

EXPECTED_XLSX_HEADERS = [
    'تاریخ', 'وعده', 'کل سفارشات پرسنل', 'تقاضای فعال پرسنل', 'لغوشدهٔ پرسنل',
    'درخواست مهمان (نفرات)', 'پرس سفارش/تحویل واقعی پیمانکار', 'توزیع پرسنل',
    'مهمان توزیع‌شده', 'کل توزیع', 'پرس باقیمانده', 'کسری/اضافهٔ سفارش',
    'غایبین', 'سرنوشت باقیمانده', 'وضعیت', 'یادداشت',
    'ماه فاکتور', 'تعداد فاکتور ماه', 'پرس طبق فاکتور ماه',
    'جمع تحویل واقعی روزانهٔ ماه', 'اختلاف فاکتور − تحویل ماه', 'وضعیت مقایسهٔ ماهانه',
]
EXPECTED_DAILY_TH = [
    'تاریخ', 'وعده', 'کل سفارشات پرسنل', 'تقاضای فعال پرسنل', 'لغوشدهٔ پرسنل',
    'درخواست مهمان (نفرات)', 'پرس سفارش/تحویل پیمانکار', 'توزیع پرسنل',
    'مهمان توزیع‌شده', 'کل توزیع', 'پرس باقیمانده', 'کسری/اضافهٔ سفارش',
    'غایبین', 'سرنوشت باقیمانده', 'وضعیت', 'ثبت/ویرایش',
]


def read(path: Path) -> str:
    return path.read_text(encoding="utf-8")


class GuestReconciliationColumnTests(unittest.TestCase):
    def test_daily_table_headers_exact(self) -> None:
        text = read(JS_PRIMARY)
        match = re.search(r"<th>تاریخ</th><th>وعده</th>(.*?)</thead>", text, re.S)
        self.assertIsNotNone(match, "تگ سرستون‌های تطبیق روزانه پیدا نشد")
        ths = re.findall(r"<th>(.*?)</th>", "<th>تاریخ</th><th>وعده</th>" + match.group(1))
        self.assertEqual(ths, EXPECTED_DAILY_TH)
        self.assertNotIn("لغوشدهٔ مهمان", match.group(1))
        self.assertNotRegex(match.group(1), r"<th>تقاضای فعال</th>")

    def test_daily_table_row_cell_count_matches_headers(self) -> None:
        text = read(JS_PRIMARY)
        match = re.search(r"// begin daily-row.*?// end daily-row", text, re.S)
        # شمارش خانه‌های بدنه از روی ساخت‌دهندهٔ ردیف انجام می‌شود
        row = re.search(r"return '<tr><td>' \+ rowDate \+ '</td><td>' \+ \(item\.meal_type.*?ثبت/ویرایش", text, re.S)
        self.assertIsNotNone(row)
        segment = row.group(0)
        # شمارش تگ <td> در یک ردیف (هر td یا از helper می‌آید که دقیقاً یک td برمی‌گرداند)
        helper_calls = segment.count("reconciliationNumber(")
        self.assertGreaterEqual(helper_calls, 7)
        for needle in (
            "item.total_employee_orders",
            "item.active_employee_orders",
            "item.cancelled_employee_orders",
            "item.active_guest_meals",
            "reconciliationRemainingCell(item)",
            "reconciliationGapCell(item)",
            "reconciliationLeftoverCell(item)",
        ):
            self.assertIn(needle, segment)
        self.assertNotIn("cancelled_guest_meals", segment)

    def test_export_headers_exact_22_columns(self) -> None:
        text = read(RECON_PHP)
        match = re.search(r"\$headers = \[(.*?)\];", text, re.S)
        self.assertIsNotNone(match)
        headers = [h.strip().strip("'") for h in match.group(1).split(",") if h.strip()]
        self.assertEqual(headers, EXPECTED_XLSX_HEADERS)
        self.assertEqual(len(headers), 22)
        self.assertNotIn("لغوشدهٔ مهمان", match.group(1))
        self.assertNotRegex(match.group(1), r"'تقاضای فعال'")

    def test_export_monthly_row_indices_fit_22_columns(self) -> None:
        text = read(RECON_PHP)
        for index in re.findall(r"\$monthlyRow\[(\d+)\]", text):
            self.assertLess(int(index), 22, f"monthlyRow[{index}] out of range")
        for needle in (
            "$monthlyRow[16] = $report['month'];",
            "$monthlyRow[17] = $monthSummary['invoice_count'];",
            "$monthlyRow[18] = $monthSummary['invoice_portions'];",
            "$monthlyRow[19] = $monthSummary['daily_received_portions'];",
            "$monthlyRow[20] = $monthSummary['invoice_difference'] ?? '—';",
            "$monthlyRow[21] = $comparisonStatus;",
        ):
            self.assertIn(needle, text)
        self.assertNotIn("$monthlyRow[22]", text)

    def test_total_employee_orders_exposed_in_report_and_export(self) -> None:
        text = read(RECON_PHP)
        self.assertIn("'total_employee_orders' => $currentEmployees + (int) $row['cancelled_employee_orders']", text)
        self.assertGreaterEqual(text.count("$item['total_employee_orders']"), 1)

    def test_gap_basis_text_documents_total_demand(self) -> None:
        text = read(JS_PRIMARY)
        self.assertIn("تقاضای کل = سفارش فعال پرسنل + درخواست فعال مهمان (نفرات)", text)
        self.assertIn("کسری/اضافهٔ سفارش روی همین تقاضای کل است", text)
        php = read(RECON_PHP)
        self.assertIn("تقاضای کل = سفارش فعال پرسنل + درخواست فعال مهمان (نفرات)", php)

    def test_js_copies_stay_byte_identical(self) -> None:
        self.assertEqual(JS_PRIMARY.read_bytes(), JS_MIRROR.read_bytes())


class OrgTreeAccessGroupTests(unittest.TestCase):
    def test_org_edit_gate_honors_permissions_and_groups(self) -> None:
        text = read(ORG_PHP)
        match = re.search(r"function user_can_manage_organization\(array \$user\): bool\s*\{(.*?)\n\}", text, re.S)
        self.assertIsNotNone(match)
        body = match.group(1)
        # پرچم قدیمی نگه داشته می‌شود تا دادهٔ قدیمی نشکند
        self.assertIn("is_primary_admin", body)
        # مجوز org.manage از نقش یا گروه اعمال می‌شود (اصلاح گروه‌های دسترسی ۱.۶۲.۹۴)
        self.assertIn("user_can($user, 'org.manage')", body)

    def test_liaison_third_slot_beside_ceo(self) -> None:
        js = read(ORG_JS)
        self.assertIn("renderLiaisonSide('center')", js)
        self.assertIn("org-liaison-cell-center", js)
        self.assertIn("org-top-row--with-center", js)
        css = read(ORG_CSS)
        self.assertIn(".org-top-row--with-center", css)
        self.assertIn("grid-template-columns: minmax(210px,1fr) auto auto auto minmax(210px,1fr)", css)
        actions = read(ORG_ACTIONS)
        self.assertIn("valid_choice(post_value('chart_side'), ['right', 'left', 'center'], 'right')", actions)
        php = read(ORG_PHP)
        self.assertIn("in_array($side, ['right', 'left', 'center'], true)", php)


class OfficialHolidayOrderTests(unittest.TestCase):
    def test_official_holiday_no_longer_blocks_menu_render(self) -> None:
        order_js = read(ORDER_JS)
        # پیامِ متناقض قبلی (ادغام رفتار بستن روز با متن «تعطیلی مانع سفارش نیست») حذف شده است
        self.assertNotIn("if (holiday) return 'این روز تعطیل رسمی است؛ تعطیلی مانع سفارش نیست.';", order_js)
        # برچسب تعطیلی در تقویم/مودال همچنان نمایش داده می‌شود
        self.assertIn("تعطیل رسمی", order_js)
        order_php = read(ORDER_PHP)
        self.assertIn("food-order.js?v=24", order_php)


if __name__ == "__main__":
    unittest.main()


PACKAGE = ROOT / "downloads" / "ticket-update-1.62.93-to-1.62.94-food-guest-org-holiday-20261110.zip"
SHA_SIDECAR = ROOT / "downloads" / "ticket-update-1.62.93-to-1.62.94-food-guest-org-holiday-20261110.zip.sha256"
REPORT = ROOT / "downloads" / "CHANGE-REPORT-FA-1.62.94.md"


class PackageTests(unittest.TestCase):
    def test_package_exists_and_is_consistent(self) -> None:
        import hashlib
        import json
        import zipfile

        self.assertTrue(PACKAGE.exists(), f"missing package: {PACKAGE}")
        digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
        if SHA_SIDECAR.exists():
            sidecar = SHA_SIDECAR.read_text(encoding="utf-8").split()[0].strip()
            self.assertEqual(digest, sidecar)
        with zipfile.ZipFile(PACKAGE) as zf:
            names = zf.namelist()
            self.assertIn("manifest.json", names)
            manifest = json.loads(zf.read("manifest.json"))
            self.assertEqual(zf.read("files/VERSION").decode().strip(), "1.62.94")
        self.assertEqual(manifest["version"], "1.62.94")
        self.assertEqual(manifest["requires_version"], "1.62.93")
        self.assertEqual(manifest["migrations"], [])
        self.assertEqual(manifest["files"], sorted(manifest["files"]))
        expected = [
            "food-reconciliation.php",
            "food-order.php",
            "assets/food-menu.js",
            "food-ticket-web/assets/food-menu.js",
            "assets/food-order.js",
            "food-ticket-web/index.html",
            "organization.php",
            "actions/org-actions.php",
            "assets/organization.js",
            "assets/style.css",
            "index.php",
            "lib/render.php",
            "CHANGE-REPORT-FA-1.62.94.md",
            "VERSION",
        ]
        self.assertEqual(manifest["files"], sorted(expected))
        # هیچ اثری از «مرکز درخواست‌ها»/RC نباید وارد بسته شده باشد.
        for name in names:
            lowered = name.lower()
            for token in ("request-center", "request_center", "requestcenter", "035-request", "rc-candidate"):
                self.assertNotIn(token, lowered)
        # همهٔ فایل‌های مقصود داخل بسته هستند
        for name in ("organization.php", "assets/organization.js", "food-reconciliation.php"):
            self.assertIn("files/" + name, names)

    def test_change_report_present_and_explicit(self) -> None:
        self.assertTrue(REPORT.exists())
        text = REPORT.read_text(encoding="utf-8")
        for needle in (
            "requires_version = 1.62.93",
            "1.62.94",
            "کل سفارشات پرسنل",
            "درخواست مهمان (نفرات)",
            "مهمان توزیع‌شده",
            "org.manage",
            "تعطیل رسمی مانع سفارش نیست",
            "received_quantity",
            "انحراف on-order",
        ):
            self.assertIn(needle, text)
