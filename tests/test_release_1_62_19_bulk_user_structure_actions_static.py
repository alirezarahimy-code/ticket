# -*- coding: utf-8 -*-
"""تست‌های ایستای ریلیز ۱.۶۲.۱۹ — بازسازی فاز ۲، برش ۸:
جداسازی ۲ اکشن «ارتباط دارایی و عملیات گروهی» به actions/bulk-actions.php
و ۶ اکشن «ساختارهای پایه و کاربران» به actions/user-structure-actions.php
بدون هیچ تغییر رفتاری + پین‌های بسته‌بندی/صفحهٔ دانلود طبق سیاست انتشار.
"""
import hashlib
import json
import re
import zipfile
from pathlib import Path
from download_page_helpers import check_download_page_lists_only_latest_package

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.19-bulk-user-structure-actions-split-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-19-bulk-user-structure-actions-split"
GUARD_END = "    exit;\n}\n\n"

FILES_AND_ACTIONS = {
    "actions/bulk-actions.php": ["add_asset_relation", "bulk_update_tickets"],
    "actions/user-structure-actions.php": [
        "add_category", "add_department", "link_domain_food_by_code",
        "update_user_role", "save_department_members", "save_cd_dvd_settings",
    ],
}


def read(rel):
    return (ROOT / rel).read_text(encoding="utf-8")


def test_index_requires_both_files_in_original_order():
    src = read("index.php")
    b = "require __DIR__ . '/actions/bulk-actions.php';"
    u = "require __DIR__ . '/actions/user-structure-actions.php';"
    assert src.count(b) == 1 and src.count(u) == 1
    # ترتیب اجرای قبلی حفظ شده: تعطیلات < ارتباط دارایی/گروهی < ساختار پایه/کاربران < سازمان
    hol = src.index("require __DIR__ . '/actions/holiday-actions.php';")
    org = src.index("require __DIR__ . '/actions/org-actions.php';")
    assert hol < src.index(b) < src.index(u) < org
    for actions in FILES_AND_ACTIONS.values():
        for action in actions:
            assert f"if ($action === '{action}')" not in src, action


def test_action_files_structure_and_persian_descriptions():
    for rel, actions in FILES_AND_ACTIONS.items():
        src = read(rel)
        assert src.startswith("<?php\n\ndeclare(strict_types=1);\n"), rel
        assert "if (!defined('APP_ROOT')) {" in src and GUARD_END in src, rel
        for action in actions:
            assert src.count(f"if ($action === '{action}')") == 1, (rel, action)
            assert re.search(rf"^ \*   {re.escape(action)}\s+\S", src, re.M), f"شرح فارسی سرآیند برای {action} نیست"
        body = src.split(GUARD_END, 1)[1]
        assert body.count("if ($action === ") == len(actions), rel
        # تعریف تابع نام‌دار ممنوع (کلوژر static بی‌نام و function_exists مجازند)
        assert not re.search(r"\bfunction\s+[A-Za-z_]", body), rel
        assert body.count("{") == body.count("}"), rel


def test_version_manifest_and_package():
    # از ۱.۶۲.۲۰ نسخهٔ جاری جلوتر است؛ پین VERSION فقط در تست نسخهٔ جاری نگه داشته می‌شود.
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.19"
    assert manifest["requires_version"] == "1.61.9"
    assert manifest["migrations"] == ["009-traffic-destination-multi-approver.sql", "010-org-liaison-units.sql"]
    files = manifest["files"]
    assert len(files) == 57 and len(set(files)) == 57
    for need in list(FILES_AND_ACTIONS) + ["VERSION", "index.php",
                                           "actions/account-actions.php", "actions/org-actions.php",
                                           "food-ticket-web/assets/food-capitalio-dashboard-r42.js"]:
        assert need in files, need
    for f in files:
        assert (ROOT / f).is_file(), f

    package = ROOT / "downloads" / PACKAGE_NAME
    assert package.is_file()
    zf = zipfile.ZipFile(package)
    names = set(zf.namelist())
    pattern = re.compile(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$")
    for name in names:
        if not name.endswith("/"):
            assert pattern.match(name), name
    for mig in manifest["migrations"]:
        assert f"migrations/{mig}" in names and f"files/migrations/{mig}" in names, mig
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.19"
    # بستهٔ تاریخی است: مقایسهٔ بایتی با کد جاری انجام نمی‌شود (این فایل‌ها در
    # ۱.۶۲.۵۲..۱.۶۲.۵۹ تغییر کرده‌اند). یکپارچگی بسته با SHA-256 تأیید می‌شود.
    for rel in list(FILES_AND_ACTIONS):
        assert f"files/{rel}" in names
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")
    # پین SHA در صفحهٔ دانلود از ۱.۶۲.۲۰ حذف شد (صفحه فقط بستهٔ جاری را نشان می‌دهد).


def test_backup_group_moved_in_1_62_20():
    """از ۱.۶۲.۲۰ (برش ۹) گروه پشتیبان‌گیری/به‌روزرسانی در فایل جدا است."""
    src = read("index.php")
    assert "require __DIR__ . '/actions/backup-actions.php';" in src
    backup = read("actions/backup-actions.php")
    for action in ("create_backup", "restore_backup", "system_update"):
        assert f"if ($action === '{action}')" not in src, action
        assert f"if ($action === '{action}')" in backup, action


def test_panel_asset_refs_are_all_packaged():
    """قاعدهٔ دائمی fix1: هر asset ارجاع‌شده در index.html پنل غذا باید داخل همان بسته باشد."""
    zf = zipfile.ZipFile(ROOT / "downloads" / PACKAGE_NAME)
    names = set(zf.namelist())
    html = zf.read("files/food-ticket-web/index.html").decode("utf-8")
    for ref in sorted(set(re.findall(r'(?:src|href)="(assets/[^"?]+)', html))):
        assert f"files/food-ticket-web/{ref}" in names, f"ارجاع بدون فایل در بسته: {ref}"


def test_download_page_lists_only_latest_package():
    # قاعدهٔ پروژه: صفحهٔ دانلود فقط جدیدترین بستهٔ منتشرشده را نشان می‌دهد.
    # بررسی نسخه‌آگنوستیک است تا با هر انتشار، تست کهنه نشود.
    check_download_page_lists_only_latest_package()

