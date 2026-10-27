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
index = read("index.php") + "\n" + read("actions/org-actions.php")
style = read("assets/style.css")
guide = read("help-guide.php")
chatbot = read("chatbot.php")

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
check(
    "data-org-zoom" in org_js
    and 'data-org-zoom="fit"' in index
    and "org-chart-wrap" in index
    and ".org-chart-wrap" in style,
    "org tree zoom keeps scroll size and can fit the canvas",
)
check(
    "نمودار" in guide and "کارشناس بعد از ساخت حداقل یک معاونت" in guide,
    "guide documents the current organization-chart workflow",
)

print("all org chart expert static checks passed")
