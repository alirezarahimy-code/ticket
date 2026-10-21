from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(relative: str) -> str:
    return (ROOT / relative).read_text(encoding="utf-8")


def check(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)
    print(f"ok - {message}")


org_js = read("assets/organization.js")
org_php = read("organization.php")
index = read("index.php")
style = read("assets/style.css")
guide = read("help-guide.php")
chatbot = read("chatbot.php")

org_page = index.split("if ($page === 'organization')", 1)[1].split("if ($page === 'settings')", 1)[0]
check(
    "employee_number" not in org_page
    and "national_code" not in org_page
    and "'code' => (string) ($chartUser" not in org_page,
    "organization chart does not query or serialize personnel or national codes",
)
check(
    "codeOf" not in org_js
    and "org-node-code" not in org_js
    and "normalize(item.code)" not in org_js
    and "جستجو بر اساس نام یا کد ملی" not in index,
    "people lists, pickers, and every position card omit personal codes",
)

check(
    "function org_unit_accepts_experts" in org_php
    and "'department', 'deputy'" in org_php,
    "deputy and department both accept experts",
)

check(
    "COALESCE(u.auth_source, '') <> 'food'" in index
    and "NOT (u.auth_source = 'local' AND u.role IN ('local_user','guard'))" not in index.split("if ($page === 'organization')", 1)[1].split("if ($page ===", 1)[0],
    "org people list includes local users and excludes only food accounts",
)

org_assign = index.split("if ($action === 'save_org_assign_users')", 1)[1].split("if ($action ===", 1)[0]
org_remove = index.split("if ($action === 'remove_org_experts')", 1)[1].split("if ($action ===", 1)[0]
check(
    "org_unit_accepts_experts($unit)" in org_assign
    and "unit_type'] !== 'department'" not in org_assign,
    "assign experts accepts deputy or department",
)
check(
    "org_unit_accepts_experts($unit)" in org_remove
    and "unit_type'] !== 'department'" not in org_remove,
    "remove experts accepts deputy or department",
)

for action_name in ("save_org_node", "assign_org_manager", "save_org_unit"):
    action_block = index.split(f"if ($action === '{action_name}')", 1)[1].split("if ($action ===", 1)[0]
    check(
        'UPDATE users SET role = "primary_admin"' not in action_block,
        f"{action_name} cannot turn an organizational manager into a system primary admin",
    )
repair_action = index.split("if ($action === 'repair_org_manager_admin_roles')", 1)[1].split("if ($action ===", 1)[0]
org_page = index.split("if ($page === 'organization')", 1)[1].split("if ($page === 'settings')", 1)[0]
check(
    "if ($action === 'repair_org_manager_admin_roles')" in index
    and "role = \"manager\", is_primary_admin = 0" in repair_action
    and "org_unit_managers" in repair_action
    and "اصلاح مدیران سازمانی که ادمین اصلی شده‌اند" in org_page,
    "primary admin can safely restore mistakenly promoted org managers without breaking their unit assignments",
)
check(
    "$canRepairOrgAdminRoles = user_is_primary_admin($organizationUser)" in org_page
    and "__REPAIR_ROLE_LINK__" in org_page
    and "body.page-organization { height: auto; min-height: 100vh; overflow-x: hidden; overflow-y: auto; }" in style
    and "body.page-organization .content-wrap { height: auto; min-height: 100vh; overflow: visible; }" in style
    and "assets/style.css?v=3.4.41" in index,
    "admin repair panel is reachable by scrolling and has a visible header shortcut",
)

check(
    "add-department" in org_js
    and 'org-node-add-expert" data-org-action="add-experts"' in org_js
    and "org-node-add-row" in org_js,
    "deputy card has department and expert add buttons",
)
check(
    "if (deputyExperts.length) html += '<li>' + expertNode(deputy) + '</li>';" in org_js,
    "deputy expert box appears only after experts are assigned",
)
check(
    "expertHosts" in org_js
    and "ابتدا معاونت بسازید" in org_js
    and "کارشناس بعد از ساخت معاونت اضافه می‌شود" in org_js,
    "quick-add expert is available after a deputy exists",
)
check(
    "دامینی" in org_js and "محلی" in org_js and "sourceLabel" in org_js,
    "people picker labels domain and local users",
)
check(
    ".org-node-add-row" in style and ".org-node-add-expert" in style,
    "deputy dual-add buttons have layout styles",
)
check(
    "$bodyPageClass = ($page ?? '') === 'organization' ? 'page-organization' : '';" in index
    and "body class=" in index
    and "e($bodyPageClass)" in index
    and "body.page-organization" in style
    and "body:has(.organization-workspace)" not in style,
    "organization layout no longer depends on browser support for :has()",
)
check(
    "سمت راست «افزودن اداره»" in guide
    and "کاربران دامینی و محلی" in guide,
    "help guide describes deputy experts and local users",
)
check(
    "org_chart_experts" in chatbot
    and "index.php?page=organization" in chatbot
    and "سمت چپ «افزودن کارشناس»" in chatbot,
    "chatbot answers org-chart expert questions",
)

print("all org chart expert static checks passed")
