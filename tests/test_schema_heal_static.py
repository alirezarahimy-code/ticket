from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(relative: str) -> str:
    return (ROOT / relative).read_text(encoding="utf-8")


def check(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)
    print(f"ok - {message}")


boot = "\n".join(read(name) for name in (
    "bootstrap.php", "lib/tickets-sla.php", "lib/db-core.php", "lib/auth-scope.php"
))
index = read("index.php")
org = read("organization.php")
food = read("food-ticket.php")
local = read("local-accounts.php")

check("function ticket_hot_schema_ensure" in boot, "hot-path schema heal exists in the SLA module")
check("function db_add_missing_column" in boot, "missing columns are added without crashing")
check("function db_ensure_enum_values" in boot, "enum values can be extended in place")
heal_start = boot.find("function ticket_hot_schema_ensure")
heal_end = boot.find("function ticket_number_ensure_schema_run", heal_start)
heal = boot[heal_start:heal_end]
check("cancel_requested_at" in heal and "cancel_requested_by" in heal, "queue cancellation columns are healed")
check("ola_response_due_at" in heal, "queue OLA columns are healed")
check("sla_paused_at" in heal, "queue SLA pause column is healed")
check("must_change_password" in heal, "local-account password column is healed at startup")
check("ticket_hot_schema_ensure();" in boot, "ticket number ensure also runs hot-path heal")
check("try {" in org[org.find("function org_ensure_schema"):org.find("function org_ticket_schema_ensure")], "org schema ensure cannot crash pages")
backfill = food[food.find("function food_ticket_ensure_employee_local_accounts"):food.find("function food_ticket_api_employees")]
check("catch (Throwable $exception)" in backfill, "employee local backfill is isolated from other pages")
check("function lac_ensure_schema" in local, "local account schema ensure exists")
check("lac_ensure_schema" in food, "employee backfill waits for local account columns")
check("ticket_hot_schema_ensure" in read("cron/ticket_auto_close.php"), "auto-close cron heals schema first")
check("ticket_hot_schema_ensure" in read("cron/sla_maintenance.php"), "sla cron heals schema first")

print("all schema heal static checks passed")
