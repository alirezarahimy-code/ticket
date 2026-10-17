from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


def check(cond: bool, msg: str) -> None:
    if not cond:
        raise AssertionError(msg)
    print(f"ok - {msg}")


groups = read("food-ticket-groups.php")
order = read("food-order.php")
js = read("assets/food-order.js")
guide = read("help-guide.php")
schema = read("schema.sql")
install = read("install-empty-db.sql")
perms = read("permissions.php")
bootstrap = read("bootstrap.php")
admin_js = read("food-ticket-web/assets/food-groups.js")

check("CREATE TABLE IF NOT EXISTS food_ticket_day_visits" in groups
      and "CREATE TABLE IF NOT EXISTS food_ticket_day_visits" in schema
      and "CREATE TABLE IF NOT EXISTS food_ticket_day_visits" in install,
      "day-visit table is created at runtime and in fresh-install schemas")
check("uq_food_day_visit_user_date" in groups, "one visit per user per day")
check("function food_ticket_day_visit_state" in groups
      and "function food_ticket_day_visit_save" in groups
      and "function food_ticket_day_visit_clear" in groups
      and "function food_ticket_group_members_for_date" in groups,
      "visit helpers and dated member list exist")
check("عضو دائم گروه نمی‌تواند محل تحویل را عوض کند" in groups
      and "فیش این گروه برای امروز چاپ شده است" in groups
      and "خوداظهاری فقط برای امروز مجاز است" in groups,
      "ungrouped-only and lock-after-print rules are in the save path")
check("food_ticket_group_members_for_date($groupId, $date)" in groups, "group print includes today's visitors")
check("'visitor' => true" in groups and "مهمان امروز" in admin_js,
      "kitchen absence list shows today's visitors as read-only")
check("$route === 'day-visit'" in order
      and "$route === 'day-visit-cancel'" in order
      and "food_order_require_permission($user, 'foodorder.self')" in order,
      "order API exposes visit routes behind foodorder.self")
check("'foodorder.self'" in perms, "existing self-order permission is reused; no extra grant needed")
check("'food_day_visit_set'" in bootstrap and "'food_day_visit_cleared'" in bootstrap,
      "audit labels exist for set/clear")
check("function renderVisitCard" in js
      and "data-fo-action=\"visit-save\"" in js
      and "data-fo-action=\"visit-cancel\"" in js
      and "request('day-visit'" in js,
      "food-order page has the today-visit card")
check("خوداظهاری محل تحویل امروز" in guide
      and "عضو دائم یک گروه نمی‌تواند خوداظهاری کند" in guide,
      "Persian guide documents the self-declaration")

print("all day-visit checks passed")
