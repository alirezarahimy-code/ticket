from __future__ import annotations

import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8-sig")


class InventoryTruthStaticTests(unittest.TestCase):
    def test_login_identity_does_not_claim_hardware_collection(self) -> None:
        inventory = read("inventory.php")
        start = inventory.index("function inventory_collect_client_asset")
        end = inventory.index("function inventory_client_ip", start)
        identity = inventory[start:end]
        self.assertIn("client_request_identity", identity)
        self.assertIn('client_request_identity', identity)
        self.assertNotIn('last_inventory_at = NOW()', identity)
        self.assertIn('last_inventory_at = NULL', identity)
        self.assertIn('source) VALUES', identity)
        self.assertNotIn('INSERT INTO asset_inventory_history', identity)

    def test_agent_api_rejects_payload_without_hardware_rows(self) -> None:
        api = read("api/inventory.php")
        self.assertIn("if (inventory_hardware_score($payload) === 0)", api)
        self.assertIn("422", api)
        self.assertIn("هیچ ردیف سخت‌افزاری قابل‌استفاده‌ای ندارد", api)
        self.assertIn("[inventory-api] save failed:", api)

    def test_domain_scan_does_not_save_identity_only_response_as_success(self) -> None:
        scan = read("domain-scan.php")
        self.assertIn("if (inventory_hardware_score($inventory) === 0)", scan)
        self.assertIn("پاسخ اتصال به‌تنهایی Inventory موفق نیست", scan)
        self.assertIn("'hardware_empty'", scan)
        self.assertIn("'دادهٔ سخت‌افزاری قابل‌استفاده'", scan)

    def test_diagnostics_report_hardware_score_and_local_admin_guidance(self) -> None:
        inventory = read("inventory.php")
        scan = read("domain-scan.php")
        self.assertIn("امتیاز ردیف‌های سخت‌افزاری", inventory)
        self.assertIn("دسترسی رد شد (حساب اسکن ادمین محلی مقصد نیست)", scan)
        self.assertIn("عضو Administrators محلی هر کلاینت باشد", scan)

    def test_local_extraction_button_uses_php_host_and_only_targets_its_own_asset(self) -> None:
        index = read("index.php")
        self.assertIn("$action === 'collect_inventory'", index)
        self.assertIn("inventory_collect_server_reliable()", index)
        self.assertIn("inventory_upsert_server_asset($serverInventory", index)
        self.assertIn("inventory_asset_is_local($asset)", index)
        self.assertIn("function inventory_asset_is_local(array $asset)", read("inventory.php"))
        self.assertIn("استخراج محلی همین سیستم", index)
        self.assertIn("میزبانی اجرا می‌شود که PHP سامانه روی آن نصب است", index)
        page = read("asset-inventory-page.php")
        self.assertIn("bool $canExtractLocalServer", page)
        self.assertIn("name=\"action\" value=\"collect_inventory\"", page)
        action = index[index.index("if ($action === 'collect_inventory')"):index.index("if ($action === 'asset_profile_selftest')")]
        self.assertLess(action.index("if ($hardwareScore === 0)"), action.index("inventory_upsert_server_asset($serverInventory"))

    def test_scan_credentials_are_per_run_encrypted_and_not_read_from_settings(self) -> None:
        scan = read("domain-scan.php")
        index = read("index.php")
        self.assertIn("function domain_scan_store_run_credentials", scan)
        self.assertIn("aes-256-gcm", scan)
        self.assertIn("domain_scan_run_credentials($runId", index)
        self.assertIn("domain_scan_clear_run_credentials($runId)", index)
        self.assertNotIn("setting('domain_scan_password'", scan)
        self.assertNotIn("setting('domain_scan_username'", scan)
        self.assertIn("domain_scan_validate_credentials($scanUsername, $scanPassword)", index)
        self.assertIn("name=\"scan_password\"", index)
        self.assertIn("name=\"scan_password\"", read("asset-inventory-page.php"))
        self.assertIn("name=\"scan_password\"", read("index.php"))
        self.assertIn("domain_scan_validate_credentials", scan)


if __name__ == "__main__":
    unittest.main()
