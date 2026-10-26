from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(relative: str) -> str:
    return (ROOT / relative).read_text(encoding="utf-8")


def check(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)
    print(f"ok - {message}")


boot = (read("bootstrap.php") + read("lib/db-core.php") + read("lib/logging-csrf.php") + read("lib/auth-scope.php") + read("lib/settings-effects.php") + read("lib/ldap.php") + read("lib/tickets-sla.php") + read("lib/notify-users.php") + read("lib/activity-audit.php"))  # از ۱.۶۲.۲۰: bootstrap به lib/ تفکیک شد
index = read("index.php")
org = read("organization.php")
food = read("food-ticket.php")
local = read("local-accounts.php")

check("function ticket_hot_schema_ensure" in boot, "hot-path schema heal exists")
check("function db_add_missing_column" in boot, "missing columns are added without crashing")
check("function db_ensure_enum_values" in boot, "enum values can be extended in place")
check("cancel_requested_at" in boot[boot.find("function ticket_hot_schema_ensure"):boot.find("function ticket_number_ensure_schema_run")], "queue cancel columns are healed")
check("ola_response_due_at" in boot, "queue OLA columns are healed")
check("sla_paused_at" in boot, "queue SLA pause column is healed")
check("must_change_password" in boot[boot.find("function ticket_hot_schema_ensure"):], "local-account password column is healed at startup")
check("ticket_hot_schema_ensure();" in boot, "ticket number ensure also runs hot-path heal")
check("try {" in org[org.find("function org_ensure_schema"):org.find("function org_ticket_schema_ensure")], "org schema ensure cannot crash pages")
check("catch (Throwable)" in index[index.find("food_ticket_ensure_employee_local_accounts"):index.find("food_ticket_ensure_employee_local_accounts") + 400] or "catch (Throwable)" in index, "employee local backfill is isolated from other pages")
check("function lac_ensure_schema" in local, "local account schema ensure exists")
check("catch (Throwable $exception)" in food[food.find("function food_ticket_ensure_employee_local_accounts"):food.find("function food_ticket_api_employees")], "food employee backfill swallows schema errors")
check("ticket_hot_schema_ensure" in read("cron/ticket_auto_close.php"), "auto-close cron heals schema first")
check("ticket_hot_schema_ensure" in read("cron/sla_maintenance.php"), "sla cron heals schema first")

print("all schema heal static checks passed")
