from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


names = read("asset-names.php")
request_form = read("modules/request-center/Pages/create.php")
profile_page = read("index.php")
inventory_list = read("asset-inventory-page.php")
domain_scan = read("domain-scan.php")

# Request choices show only the configured display name, not a tag or suffix.
assert "e(asset_name_or($asset['hostname'])) . '</option>'" in request_form
assert "asset_tag_label($asset['asset_tag'], $asset['hostname'])" not in request_form
assert "(سیستم فعلی)" not in request_form

# Asset name/title regions do not append an asset tag as visible secondary text.
assert "asset-tag-chip" not in profile_page[profile_page.index("if ($page === 'inventory')"):profile_page.index("if ($page === 'assets')")]
assert "e(asset_name_title($asset['hostname'], $asset['asset_tag']) ?: $asset['asset_tag'])" not in profile_page
assert "asset-tag-chip" not in inventory_list
assert "(asset_is_auto_domain_tag($asset['asset_tag']) ? '' : '<b>'" not in inventory_list
assert "asset_name_attr($asset['hostname'])" in inventory_list

# Real queue fallback maps each pending hostname (FQDN included) to its asset IP.
assert "function domain_scan_queue_fallback_ips(array $items, array $assetIpsById): array" in domain_scan
assert "$fallbackIps = domain_scan_queue_fallback_ips($items, $assetIpsById);" in domain_scan
assert "SELECT id, ip_address FROM assets WHERE id IN (" in domain_scan

# Machine identity order and validation are explicit: serial, valid MAC, name.
assert "function asset_identity_serial_value(?string $serial): string" in names
assert "function asset_identity_mac_value(?string $mac): string" in names
assert "if ($serialValue !== '')" in names
assert "if ($macValue !== '')" in names
assert "'match' => 'serial'" in names
assert "'match' => 'mac'" in names
assert "'match' => 'hostname'" in names
assert "asset_resolve_by_machine" in names

print("asset identity and name-only UI static checks passed")
