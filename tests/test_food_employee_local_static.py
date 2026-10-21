from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(relative: str) -> str:
    return (ROOT / relative).read_text(encoding="utf-8")


def check(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)
    print(f"ok - {message}")


save = read("food-ticket.php")
start = save.find("function food_ticket_api_save_employee")
end = save.find("function food_ticket_api_save_guest_card", start)
fn = save[start:end]
imp = read("food-ticket-import.php")
guide = read("help-guide.php")
chatbot = read("chatbot.php")

check("food_employee_" not in fn, "manual employee save no longer creates food-only stubs")
check("'local_user'" in fn and "'local'" in fn, "manual employee save creates a local login account")
check("must_change_password" in fn and "lac_default_hash" in fn, "new local account uses default password and first-login change")
check("auth_source'] ?? '') === 'food'" in fn, "existing food stubs convert to local on edit")
check("'local_user', 'local'" in imp, "excel import still creates local accounts")
check("نام کاربری = کد پرسنلی" in guide, "help guide documents local account on manual employee add")
check("food_employee_local" in chatbot, "chatbot answers local account for new employees")
check("function food_ticket_ensure_employee_local_accounts" in save, "existing food-only employees are backfilled to local accounts")
check("food_ticket_ensure_employee_local_accounts()" in read("index.php"), "backfill runs automatically when the app loads")
check("با باز شدن سامانه" in guide and "با باز شدن سامانه" in chatbot, "help and chatbot say existing employees get local accounts automatically")
check("function lac_ensure_schema" in read("local-accounts.php"), "local account schema is ensured before convert")
check("lac_ensure_schema" in save, "employee backfill waits for local account columns")
check("catch (Throwable $exception)" in save[save.find("function food_ticket_ensure_employee_local_accounts"):save.find("function food_ticket_api_employees")], "backfill never crashes other pages")
check("function ticket_cancel_ensure_schema" in read("bootstrap.php"), "queue cancel columns are auto-added if missing")

index = read("index.php")
bootstrap = read("bootstrap.php")
link = read("food-personnel-link.php")
check("ldapMatchId" in fn and "user_absorb_personnel_stub" in fn, "manual employee save attaches codes to matching domain user")
check("function fpl_is_personnel_stub" in link and "fpl_stub_where" in link, "personnel link treats leftover local_user as mergeable stub")
check("fpl_is_personnel_stub($food)" in link, "merge accepts leftover local accounts not only food stubs")
check("function user_admin_delete" in bootstrap and "function user_absorb_personnel_stub" in bootstrap, "admin delete and stub absorb helpers exist")
check("action\" value=\"delete_user\"" in index or 'value="delete_user"' in index, "settings users has admin-only delete button")
check("if ($action === 'delete_user')" in index, "delete_user post action is handled")
check("require_primary_admin()" in index[index.find("if ($action === 'delete_user')"):index.find("if ($action === 'delete_user')") + 400], "user delete is primary-admin only")
check("pendingStubId" in bootstrap, "ldap upsert absorbs leftover personnel code into the domain user")
check("domain_user_register" in chatbot, "chatbot answers domain user registration")
check("کاربر دامینی ثبت نمی‌شود" in guide, "help guide documents domain user registration and leftover delete")
check("ادمین اصلی در تنظیمات ← کاربران دکمهٔ حذف" in guide, "help guide documents admin-only user delete")

print("all food employee local static checks passed")
