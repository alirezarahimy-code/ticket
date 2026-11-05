from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def section(source: str, start: str, end: str) -> str:
    begin = source.index(start)
    finish = source.index(end, begin + len(start))
    return source[begin:finish]


def test_new_ticket_page_requires_ticket_create_permission():
    index = read("index.php")
    page = section(index, "if ($page === 'new-ticket') {", "\nif ($page === 'ticket') {")
    assert "$user = require_permission('ticket.create');" in page


def test_post_action_dispatch_enforces_csrf_before_handlers():
    index = read("index.php")
    dispatch = section(index, "if ($_SERVER['REQUEST_METHOD'] === 'POST') {", "\nif ($page === 'chatbot-api')")
    assert "require_csrf();" in dispatch
    assert dispatch.index("require_csrf();") < dispatch.index("$action = (string) ($_POST['action'] ?? '');")


def test_food_ticket_api_requires_login_and_csrf_for_post():
    food = read("food-ticket.php")
    api = section(food, "function food_ticket_api_handle(", "\nfunction ")
    assert "require_food_ticket_access()" in api
    assert "if ($method === 'POST') {" in api
    assert "require_csrf();" in api


def test_food_order_api_requires_login_and_csrf_for_post():
    food = read("food-order.php")
    api = section(food, "function food_order_api_handle(", "\nfunction ")
    assert "require_login()" in api
    assert "if ($method === 'POST') {" in api
    assert "require_csrf();" in api


def test_sensitive_download_and_export_actions_have_explicit_permissions():
    index = read("index.php")
    backup = section(index, "if (($_GET['action'] ?? '') === 'download_backup')", "\nif (($_GET['action'] ?? '') === 'export_activity_logs')")
    activity = section(index, "if (($_GET['action'] ?? '') === 'export_activity_logs')", "\nif (($_GET['action'] ?? '') === 'export_report')")
    assert "require_primary_admin();" in backup
    assert "require_permission('logs.activity_export')" in activity


def test_action_handlers_refuse_direct_http_access():
    action_files = sorted((ROOT / "actions").rglob("*.php"))
    assert action_files, "No action handlers found."
    unguarded = []
    for path in action_files:
        source = path.read_text(encoding="utf-8")
        head = source[:2500]
        if "defined('APP_ROOT')" not in head and 'defined("APP_ROOT")' not in head:
            unguarded.append(path.relative_to(ROOT).as_posix())
    assert not unguarded, "Action handlers missing front-controller guard: " + ", ".join(unguarded)
