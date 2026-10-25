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
check("'visitor' => $isVisitor" in groups and "مهمان امروز" in admin_js
      and "today_visitors" in groups and "today_visitors" in admin_js
      and "temporary_transfer" in admin_js,
      "kitchen group list shows today's visitors separately from permanent members")
css = (ROOT / "food-ticket-web/assets/food-menu.css").read_text(encoding="utf-8")
check("grid-template-columns:repeat(4,minmax(0,1fr))" in css
      and "data-fg-member" in admin_js
      and "reload() {" in admin_js,
      "group members are a 4-column checklist with checkbox before the name")
check("$route === 'day-visit'" in order
      and "$route === 'day-visit-cancel'" in order
      and "function food_order_day_visit_subject" in order
      and "food_order_require_permission($user, 'foodorder.self')" in order,
      "order API exposes visit routes for self and verified proxy subjects")
ticket = read("food-ticket.php")
check("'food-visit-cancel' => 'food.groups'" in ticket
      and "food_ticket_api_cancel_day_visit" in ticket
      and "FOOD_TICKET_CAN_CANCEL_VISIT" in ticket,
      "food panel exposes support-manager cafeteria-cancel API")
check("'foodorder.self'" in perms, "existing self-order permission is reused; no extra grant needed")
check("'food_day_visit_set'" in bootstrap and "'food_day_visit_cleared'" in bootstrap,
      "audit labels exist for set/clear")
check("function renderVisitDialog" in js
      and "data-fo-action=\"change-cafeteria\"" in js
      and "data-fo-action=\"visit-save\"" in js
      and "data-fo-action=\"visit-cancel\"" in js
      and "visitSaving = false" in js
      and "request('day-visit'" in js,
      "cafeteria change is a popup next to person-change, for self and proxy")
check("تغییر سلف امروز" in guide
      and "کنار «تغییر شخص»" in guide
      and "عضو دائم یک گروه نمی‌تواند سلف را عوض کند" in guide,
      "Persian guide documents the cafeteria popup")
check("visit_change_until" in groups
      and "function food_ticket_visit_change_is_open" in groups
      and "food_ticket_visit_change_guard" in groups
      and "function food_ticket_can_cancel_day_visit" in groups
      and "function food_ticket_api_cancel_day_visit" in groups
      and "مهلت تغییر سلف" in admin_js
      and 'id="fg-visit-until"' in admin_js
      and "data-fg=\"visit-cancel\"" in admin_js
      and "لغو تغییر سلف" in admin_js,
      "each food group has a visible cafeteria-change deadline and support can cancel visits")
check("visit_change_until TIME NULL" in schema
      and "visit_change_until TIME NULL" in install,
      "fresh-install schemas include cafeteria-change deadline")
check('data-fg="members"' in admin_js
      and "اعضای دائم گروه" in admin_js
      and "اعضای مهمان یا موقت امروز" in admin_js,
      "food groups have a members-list popup next to today's absence")

print("all day-visit checks passed")
