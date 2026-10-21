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

    def test_winrm_only_targets_are_detected_when_icmp_is_blocked(self) -> None:
        scan = read("domain-scan.php")
        self.assertIn("domain_scan_tcp_probe($tcpTargets, [445, 135, 3389, 5985]", scan)
        self.assertIn("'winrm_unavailable'", scan)
        self.assertIn("پورت پیش‌فرض این مسیر TCP 5985", scan)
        self.assertIn("WinRM و قاعدهٔ فایروال را روی کلاینت مقصد بررسی کنید", scan)

    def test_one_host_credential_test_does_not_write_inventory(self) -> None:
        index = read("index.php")
        start = index.index("if ($action === 'domain_scan_test_selected')")
        end = index.index("if ($action === 'domain_scan_selected')", start)
        action = index[start:end]
        page = read("asset-inventory-page.php")
        self.assertIn('value="domain_scan_test_selected"', page)
        self.assertIn('data-max-selection="1"', page)
        self.assertIn("count($testIds) !== 1", action)
        self.assertIn("domain_scan_remote_inventory($testHost, $testCredentials)", action)
        self.assertIn("چیزی در شناسنامه ذخیره نشد", action)
        self.assertNotIn("domain_scan_store_inventory(", action)
        self.assertNotIn("domain_scan_start_selected(", action)

    def test_winrm_guidance_configures_targets_not_only_the_web_server(self) -> None:
        scan = read("domain-scan.php")
        index = read("index.php")
        self.assertIn("WinRM باید روی کلاینت‌های مقصد (نه فقط سرور PHP) فعال باشد", scan)
        self.assertIn("نیازمند فعال‌بودن WinRM روی مقصدها", index)
        self.assertNotIn("Enable-PSRemoting روی سرور", index)

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


    def test_full_domain_scan_is_not_limited_to_a_single_host_or_manual_batch(self) -> None:
        scan = read("domain-scan.php")
        start = scan[scan.index("function domain_scan_start("):scan.index("function domain_scan_start_selected(")]
        ldap = scan[scan.index("function ldap_domain_computers("):scan.index("function domain_scan_asset_for(")]
        self.assertIn("ldap_domain_computers()", start)
        self.assertIn("foreach ($computers as $pc)", start)
        self.assertIn("function ldap_domain_computers(int $limit = 0)", ldap)
        self.assertIn("ldap_control_paged_result($connection, 500", ldap)
        self.assertIn("$limit > 0 && count($computers) >= $limit", ldap)

    def test_extended_windows_inventory_is_collected_and_persisted_without_command_lines_or_env_values(self) -> None:
        collector = read("tools/collect_server_inventory.ps1")
        inventory = read("inventory.php")
        for key in (
            "Win32_PnPSignedDriver", "Win32_Service", "Win32_Process", "Win32_StartupCommand",
            "Win32_Share", "Win32_UserAccount", "Win32_QuickFixEngineering", "MSFT_NetFirewallProfile",
            "MSFT_StorageReliabilityCounter", "MSStorageDriver_FailurePredictStatus", "WmiMonitorID",
        ):
            self.assertIn(key, collector)
        self.assertIn("pnp_devices = @($allDevices)", collector)
        self.assertIn("windows_updates = @($updates)", collector)
        self.assertIn("'extended_inventory' => $extendedInventory", inventory)
        self.assertNotIn("'Command'", collector)
        self.assertNotIn("'VariableValue'", collector)



    def test_domain_reinventory_refreshes_only_machine_derived_profile_fields(self) -> None:
        scan = read("domain-scan.php")
        start = scan.index("function domain_scan_store_inventory(")
        end = scan.index("function domain_scan_process(", start)
        store = scan[start:end]
        self.assertIn("asset_profile_auto_values($inventory), $userId, true", store)
        self.assertIn("asset_profile_auto_values($inventory)", store)
        self.assertIn("manually entered organizational/plaque data is untouched", store)

    def test_internet_access_is_not_inferred_from_a_default_gateway(self) -> None:
        profile = read("asset-profile.php")
        start = profile.index("function asset_profile_auto_values(")
        end = profile.index("function asset_profile_apply_auto(", start)
        auto = profile[start:end]
        self.assertNotIn("'connected_internet' =>", auto)
        self.assertNotIn("$gateway !== ''", auto)
        self.assertIn("'connected_network' => $ip !== '' ? 1 : 0", auto)
        self.assertIn("($value === 0 && !$overwrite)", profile)



if __name__ == "__main__":
    unittest.main()
