from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


def check(cond: bool, msg: str) -> None:
    if not cond:
        raise AssertionError(msg)
    print(f"ok - {msg}")


save = read("food-ticket.php")
start = save.find("function food_ticket_api_save_employee")
end = save.find("function food_ticket_api_save_guest_card", start)
fn = save[start:end]
imp = read("food-ticket-import.php")
guide = read("help-guide.php")
chatbot = read("chatbot.php")
local = read("local-accounts.php")

check("food_employee_" not in fn, "manual employee save no longer creates food-only stubs")
check("'local_user'" in fn and "'local'" in fn, "manual employee save creates a local login account")
check("must_change_password" in fn and "lac_default_hash" in fn, "new local account uses default password and first-login change")
check("auth_source'] ?? '') === 'food'" in fn, "existing food stubs convert to local on edit")
check("'local_user', 'local'" in imp, "excel import still creates local accounts")
check("نام کاربری = کد پرسنلی" in guide, "help guide documents local account on manual employee add")
check("food_employee_local" in chatbot, "chatbot answers local account for new employees")
check("function food_ticket_ensure_employee_local_accounts" in save, "existing food-only employees have a local-account backfill")
check("food_ticket_ensure_employee_local_accounts();" in save, "employee backfill runs from the food module")
check("با باز شدن سامانه" in guide and "با باز شدن سامانه" in chatbot, "help and chatbot explain automatic local-account setup")
check("function lac_ensure_schema" in local, "local account schema is ensured before conversion")
check("lac_ensure_schema" in save, "employee backfill waits for local account columns")
check("catch (Throwable $exception)" in save[save.find("function food_ticket_ensure_employee_local_accounts"):save.find("function food_ticket_api_employees")], "backfill never crashes other pages")

print("all food employee local static checks passed")
