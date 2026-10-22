from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]


def read(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


profile = read("asset-profile.php")
inventory = read("inventory.php")
domain_scan = read("domain-scan.php")
index = read("index.php")

# Profile field registry and automatic mapper must stay aligned with the source inventory.
field_registry = profile[:profile.index("function asset_profile_all_columns")]
auto_mapper = profile[profile.index("function asset_profile_auto_values"):profile.index("function asset_profile_apply_auto")]
field_keys = set(re.findall(r"\['key'\s*=>\s*'([^']+)'", field_registry))
auto_values_block = auto_mapper[:auto_mapper.index("$collectionStatus =")]
auto_keys = set(re.findall(r"^\s*'([^']+)'\s*=>", auto_values_block, re.MULTILINE))
assert auto_keys <= field_keys
assert "'os_serial'" in inventory[inventory.index("function inventory_apply_powershell_data"):inventory.index("function collect_server_inventory")]
assert "inventory_value($os, ['SerialNumber', 'serial_number'], ($inventory['os_serial'] ?? ''))" in auto_mapper

# Keep network-configuration rows; the former mapper discarded adapter IP/gateway rows and
# selected SERVER_ADDR / the first NIC, which could give a remote computer the scanner's address.
assert "'network_configurations' => $network" in inventory
assert "network_configurations" in auto_mapper
assert "function inventory_merge_missing_values" in inventory
assert "inventory_merge_missing_values($inventory, $previous)" in domain_scan
assert "inventory_merge_missing_values($payload, $previous)" in read("api/inventory.php")
assert "inventory_merge_missing_values($inventory, $previous)" in inventory
assert "asset_profile_auto_values([" in profile
assert "json_decode((string) ($asset['hardware_json'] ?? ''), true)" in profile
assert "foreach ($fallbacks as $key => $value)" in profile
remote_start = domain_scan.index("// Remote targets must never inherit fallback values")
remote_end = domain_scan.index("$inventory = inventory_apply_powershell_data($inventory, $decoded);", remote_start)
remote_base = domain_scan[remote_start:remote_end]
assert "$inventory['operating_system'] = '';" in remote_base
assert "$inventory['hardware'] = [];" in remote_base
assert "$inventory['domain_username'] = '';" in remote_base
assert "$preferredIp = $usableIp($inventory['ip_address'] ?? '')" in auto_mapper
assert "$serverAddr" not in auto_mapper
assert "DefaultIPGateway" in auto_mapper
assert "$mac = trim((string) inventory_value(is_array($selectedNetwork)" in auto_mapper

# Do not claim the first historical local profile or OS RegisteredUser is the active user.
user_mapper = auto_mapper[auto_mapper.index("'user_login'"):auto_mapper.index("'os_version'")]
assert "RegisteredUser" not in user_mapper
assert "$localUsers" not in user_mapper
assert "LocalPath" in auto_mapper and "basename(str_replace('\\\\', '/', $path))" in auto_mapper

# Values derived from WMI are labelled as identifiers rather than product keys / CPU serials.
assert "SerialNumber؛ نه کلید محصول" in field_registry
assert "ProcessorId" in field_registry
assert "تعداد پروفایل‌های کاربری" in field_registry

# A profile page must expose the age/source of its last successful hardware inventory.
assert "آخرین برداشت سخت‌افزاری:" in index
assert "هنوز برداشت دارای دادهٔ سخت‌افزاری ثبت نشده است" in index
assert "asset_profile_fill_from_asset(asset_profile_fetch($inventoryId), $asset)" in index
assert "asset_profile_fill_from_asset($profileMap[(int) $row['id']] ?? [], $row)" in index
assert "'os_name' => $asset['operating_system'] ?? null" in profile

print("all asset profile mapping static checks passed")
