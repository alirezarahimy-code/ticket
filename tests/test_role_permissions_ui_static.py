from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


def check(cond: bool, msg: str) -> None:
    if not cond:
        raise AssertionError(msg)
    print(f"ok - {msg}")


index = read("index.php")
perms = read("permissions.php")
guide = read("help-guide.php")
chatbot = read("chatbot.php")

check("groupAllFood" not in index, "food permission group is not hidden per role in the roles form")
check("print_fish_allowed_roles(), true)) {\n                        continue;" not in index, "roles form does not skip groups by print-fish allowlist")
save_start = perms.index("function role_permissions_save(")
save_end = perms.index("\nfunction ", save_start + 10)
save = perms[save_start:save_end]
check("print_fish_allowed_roles" not in save, "saving a role keeps food.* ticks instead of stripping them")
check("نقش‌ها و سطوح دسترسی" in index and "برای همهٔ نقش‌ها قابل تنظیم است" in index, "roles page exposes food permissions to every role")
check("نقش‌ها و گردش تیکت" in guide and "دسترسی‌های سامانه قابل تنظیم‌اند" in guide, "Persian guide explains role-based permissions")
check("اگر این بخش را در منوی خود نمی‌بینید، دسترسی آن برای حساب شما فعال نیست" in chatbot, "chatbot directs users to the admin when their role lacks access")
print("all role-permission UI checks passed")
