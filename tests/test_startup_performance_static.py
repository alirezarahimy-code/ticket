from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


index = read("index.php")
food_page = read("food-ticket-web/index.html")
inventory = read("inventory.php")

# Food employee conversion is a panel concern, not work on every login/page request.
assert "food_ticket_ensure_employee_local_accounts();" not in index
employees = read("food-ticket.php")
employees_endpoint = employees[employees.index("function food_ticket_api_employees"):]
assert "food_ticket_ensure_employee_local_accounts()" in employees_endpoint

# Slow inventory extraction remains available by explicit/manual action, but not inline with login.
login = index[index.index("if ($action === 'login')"):index.index("$user = require_login();", index.index("if ($action === 'login')"))]
assert "inventory_auto_extract_server(" not in login
assert "ITSM slow login request:" in login
assert "stages=' . implode(',', $stageSummary)" in login

# The automatic client-identity write must not synchronously perform reverse DNS.
client_identity = inventory[inventory.index("function inventory_collect_client_asset"):inventory.index("function inventory_client_ip")]
assert "inventory_reverse_name(" not in client_identity
assert "CLIENT-' . str_replace" in client_identity
assert "SELECT hostname, hardware_json" in client_identity
assert "if ($storedHostname !== '')" in client_identity

# Render the panel shell before its sequential, optional API hydration starts.
start_panel = food_page[food_page.index("async function startPanel(){"):food_page.index("function updateAbsentRangeLabel()")]
assert start_panel.index("shell();") < start_panel.index("panelStartupApi('config','/api/config')")
assert "const [printerResult,employeeResult]=await Promise.all" in start_panel
assert "__FOOD_PANEL_STARTUP_TIMINGS" in food_page
assert "document-ttfb" in food_page
assert "document-download" in food_page
assert "shell-render" in food_page
assert "ITSM slow food-ticket render: duration_ms=" in read("food-ticket.php")
assert "ITSM slow food-ticket API: route=" in read("food-ticket.php")
assert "ITSM LDAP login timing: duration_ms=" in read("bootstrap.php")
assert start_panel.rstrip().endswith("}")

print("all startup performance static checks passed")
