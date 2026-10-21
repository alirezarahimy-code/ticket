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
check("همهٔ گروه‌های دسترسی" in index, "roles page tells the admin every group is listed")
check("نقش‌ها و سطوح دسترسی" in guide and "همهٔ گروه‌های دسترسی" in guide, "Persian guide documents full permission groups per role")
check("role_permissions_form" in chatbot, "chatbot documents the full roles permission form")
print("all role-permission UI checks passed")
