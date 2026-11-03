# -*- coding: utf-8 -*-
"""تست ایستای دائمی نسخهٔ ۱.۶۲.۲۰ — فاز ۲ بازسازی، برش ۹ + تفکیک bootstrap به lib/ + حذف توابع مرده.

سه تغییر این نسخه (همگی بدون تغییر رفتاری):
۱) ۳ اکشن پشتیبان‌گیری/به‌روزرسانی از index.php به actions/backup-actions.php (بایت‌به‌بایت).
۲) bootstrap.php به ۸ ماژول lib/ تفکیک شد؛ bootstrap فقط بارکننده است و ترتیب require عیناً ترتیب قدیمی است.
۳) ۸ تابع بدون هیچ فراخوانی حذف شدند (اثبات: جستجوی ماشینی کل مخزن، صفر ارجاع).
"""
import hashlib
import json
import re
import zipfile
from pathlib import Path

from download_page_helpers import check_download_page_lists_only_latest_package, version_tuple

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.20-backup-split-bootstrap-lib-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-20-backup-split-bootstrap-lib"
GUARD_END = "    exit;\n}\n\n"

LIB_FILES = [
    "lib/db-core.php",
    "lib/logging-csrf.php",
    "lib/auth-scope.php",
    "lib/settings-effects.php",
    "lib/ldap.php",
    "lib/tickets-sla.php",
    "lib/notify-users.php",
    "lib/activity-audit.php",
]
BACKUP_ACTIONS = ("create_backup", "restore_backup", "system_update")
DELETED_DEAD_FUNCTIONS = (
    "asset_lifecycle_status_label",
    "asset_lifecycle_transition_allowed",
    "particle_effect_default_config",
    "triangle_effect_default_config",
    "triangle_effect_enabled_for_current_page",
    "ticket_cancel_ensure_schema",
    "unread_notification_count",
    "latest_notification_id",
)


def read(rel: str) -> str:
    return (ROOT / rel).read_text(encoding="utf-8")


def test_backup_actions_moved_out_of_index():
    src = read("index.php")
    backup = read("actions/backup-actions.php")
    assert "require __DIR__ . '/actions/backup-actions.php';" in src
    for action in BACKUP_ACTIONS:
        assert f"if ($action === '{action}')" not in src, action
        assert backup.count(f"if ($action === '{action}')") == 1, action
        assert re.search(rf"^ \* - {re.escape(action)}:\s+\S", backup, re.M), f"شرح فارسی سرآیند برای {action} نیست"
    # از ۱.۶۲.۲۰ دیگر هیچ اکشن درون‌خطی در index نمانده است.
    assert src.count("if ($action === '") == 0
    assert backup.startswith("<?php\n/**")
    assert "if (!defined('APP_ROOT')) {" in backup and GUARD_END in backup
    body = backup.split(GUARD_END, 1)[1]
    assert not re.search(r"\bfunction\s+[A-Za-z_]", body)
    assert body.count("{") == body.count("}")


def test_index_require_order_keeps_backup_group_position():
    src = read("index.php")
    order = [
        "actions/cd-dvd-actions.php",
        "actions/backup-actions.php",
        "actions/notification-actions.php",
        "actions/supervisor-actions.php",
    ]
    positions = [src.index(f"require __DIR__ . '/{rel}';") for rel in order]
    assert positions == sorted(positions), "ترتیب require گروه پشتیبان‌گیری عوض شده است"


def test_bootstrap_is_loader_and_lib_order_is_preserved():
    boot = read("bootstrap.php")
    # bootstrap دیگر هیچ تعریف تابعی ندارد؛ فقط سر فایل + require ماژول‌ها.
    assert not re.search(r"^function ", boot, re.M)
    assert "نقشهٔ خانواده‌های توابع" in boot[:3000]
    assert "docs/ARCHITECTURE-FA.md" in boot[:3000]
    positions = [boot.index(f"require __DIR__ . '/{rel}';") for rel in LIB_FILES]
    assert positions == sorted(positions), "ترتیب require فایل‌های lib باید عیناً ترتیب قدیمی تعریف‌ها باشد"
    # سر فایل: APP_ROOT و نشست و config پیش از ماژول‌ها
    head = boot[: positions[0]]
    for needle in ("const APP_ROOT = __DIR__;", "app_session_start();", "$config = require $configFile;"):
        assert needle in head, needle


def test_lib_files_structure_and_strictness():
    for rel in LIB_FILES:
        src = read(rel)
        assert src.startswith("<?php\ndeclare(strict_types=1);\n"), rel  # حفظ دقیق رفتار strict قبلی
        assert "if (!defined('APP_ROOT')) {" in src and GUARD_END in src, rel
        assert "جداشده از bootstrap.php در نسخهٔ ۱.۶۲.۲۰" in src, rel
        body = src.split(GUARD_END, 1)[1]
        assert body.count("{") == body.count("}"), rel
    # شمارش دقیق مربوط به بستهٔ تاریخی است؛ کد جاری می‌تواند توابع جدید داشته باشد.
    with zipfile.ZipFile(ROOT / "downloads" / PACKAGE_NAME) as archive:
        total = sum(len(re.findall(r"^function ", archive.read(f"files/{rel}").decode("utf-8"), re.M)) for rel in LIB_FILES)
    assert total == 138, total


def test_key_function_families_live_in_expected_lib_modules():
    checks = {
        "lib/db-core.php": ("function db(", "function cfg(", "class AppPDO extends PDO"),
        "lib/logging-csrf.php": ("function flash(", "function csrf_token(", "function system_log("),
        "lib/auth-scope.php": ("function current_user(", "function require_login(", "function ticket_scope("),
        "lib/settings-effects.php": ("function setting(", "function triangle_effect_script_tag("),
        "lib/ldap.php": ("function ldap_cfg(", "function ldap_sync_all_users("),
        "lib/tickets-sla.php": ("function ticket_number(", "function jalali_date(", "function ticket_hot_schema_ensure("),
        "lib/notify-users.php": ("function notify_user(", "function ldap_authenticate(", "function upsert_domain_user("),
        "lib/activity-audit.php": ("function activity_log(", "function save_audit(", "function log_ticket_event("),
    }
    for rel, needles in checks.items():
        src = read(rel)
        for needle in needles:
            assert needle in src, (rel, needle)


def test_dead_functions_are_fully_removed():
    runtime = read("bootstrap.php") + read("index.php") + "".join(read(rel) for rel in LIB_FILES)
    for php in sorted((ROOT / "actions").glob("*.php")):
        runtime += php.read_text(encoding="utf-8")
    for name in DELETED_DEAD_FUNCTIONS:
        assert f"function {name}(" not in runtime, name
        assert f"{name}(" not in runtime.replace(f"function {name}(", ""), f"ارجاع زنده به {name}"
    # ترمیم ستون‌های لغو هنوز سر جای خود است (ticket_cancel_ensure_schema فقط alias بود).
    sla = read("lib/tickets-sla.php")
    assert "cancel_requested_at" in sla and "function ticket_hot_schema_ensure" in sla


def test_version_manifest_and_package():
    # این تست پین نسخهٔ جاری نیست؛ فقط نباید از بستهٔ خودش عقب‌تر باشد.
    assert version_tuple(read("VERSION").strip()) >= version_tuple("1.62.20")
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.20"
    assert manifest["requires_version"] == "1.61.9"
    assert manifest["migrations"] == ["009-traffic-destination-multi-approver.sql", "010-org-liaison-units.sql"]
    files = manifest["files"]
    assert len(files) == 66 and len(set(files)) == 66
    for need in LIB_FILES + ["VERSION", "index.php", "bootstrap.php", "actions/backup-actions.php",
                             "actions/org-actions.php", "actions/user-structure-actions.php"]:
        assert need in files, need
    for f in files:
        assert (ROOT / f).is_file(), f

    package = ROOT / "downloads" / PACKAGE_NAME
    assert package.is_file()
    zf = zipfile.ZipFile(package)
    names = zf.namelist()
    assert len(names) == len(set(names)), "ورودی تکراری در بسته"
    nameset = set(names)
    pattern = re.compile(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$")
    for name in nameset:
        if not name.endswith("/"):
            assert pattern.match(name), name
    for mig in manifest["migrations"]:
        assert f"migrations/{mig}" in nameset and f"files/migrations/{mig}" in nameset, mig
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.20"
    # بستهٔ تاریخی است: برابری با کد جاری بررسی نمی‌شود؛ یکپارچگی با SHA-256.
    for rel in ["index.php", "bootstrap.php", "actions/backup-actions.php"] + LIB_FILES:
        assert f"files/{rel}" in nameset
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")


def test_panel_asset_refs_are_all_packaged():
    """قاعدهٔ دائمی fix1: هر asset ارجاع‌شده در index.html پنل غذا باید داخل همان بسته باشد."""
    zf = zipfile.ZipFile(ROOT / "downloads" / PACKAGE_NAME)
    names = set(zf.namelist())
    html = zf.read("files/food-ticket-web/index.html").decode("utf-8")
    for ref in sorted(set(re.findall(r'(?:src|href)="(assets/[^"?]+)', html))):
        assert f"files/food-ticket-web/{ref}" in names, f"ارجاع بدون فایل در بسته: {ref}"


def test_download_page_lists_only_latest_package():
    # بررسی نسخه‌آگنوستیک: صفحه فقط جدیدترین بسته را نشان می‌دهد.
    check_download_page_lists_only_latest_package()
