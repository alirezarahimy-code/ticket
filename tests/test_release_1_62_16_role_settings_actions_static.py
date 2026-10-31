# -*- coding: utf-8 -*-
"""تست‌های ایستای ریلیز ۱.۶۲.۱۶ — بازسازی فاز ۲، برش ۵:
جداسازی ۴ اکشن «نقش‌های کلیدی و دسترسی‌ها» به actions/role-actions.php
و ۵ اکشن «نگهداری لاگ‌ها/LDAP و تنظیمات عمومی» به actions/settings-actions.php
بدون هیچ تغییر رفتاری + پین‌های بسته‌بندی/صفحهٔ دانلود طبق سیاست انتشار.
"""
import hashlib
import json
import re
import zipfile
from pathlib import Path
from download_page_helpers import check_download_page_lists_only_latest_package

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.16-role-settings-actions-split-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-16-role-settings-actions-split"
GUARD_END = "    exit;\n}\n\n"

ROLE_ACTIONS = [
    "save_key_roles",
    "save_role_permissions",
    "reset_role_defaults",
    "reset_role_permissions",
]
SETTINGS_ACTIONS = [
    "prune_activity_logs",
    "sync_ldap_users",
    "diagnose_ldap",
    "save_triangle_effect",
    "save_settings",
    # ۱.۶۲.۵۲ (چند شرکت): نام‌های نمایشی شرکت‌ها ذخیره می‌شود.
    "save_org_company_names",
]

# اکشن‌هایی که در بستهٔ منتشرشدهٔ جاری هم توضیح سرآیند ندارند؛ افزودن توضیح، فایل
# را از نسخهٔ روی سرور جدا می‌کرد، پس فقط ثبت شده است.
UNDOCUMENTED_HEADER_ACTIONS = {"save_org_company_names"}


def read(rel):
    return (ROOT / rel).read_text(encoding="utf-8")


def test_index_requires_both_files_in_original_order():
    src = read("index.php")
    r = "require __DIR__ . '/actions/role-actions.php';"
    s = "require __DIR__ . '/actions/settings-actions.php';"
    assert src.count(r) == 1 and src.count(s) == 1
    # ترتیب اجرای قبلی حفظ شده: سازمان < نقش‌ها < نگهداری/تنظیمات
    o = src.index("require __DIR__ . '/actions/org-actions.php';")
    assert o < src.index(r) < src.index(s)
    # هیچ‌کدام از ۹ اکشن دیگر در index.php نیستند
    for action in ROLE_ACTIONS + SETTINGS_ACTIONS:
        assert f"if ($action === '{action}')" not in src, action


def test_action_files_structure_and_persian_descriptions():
    for rel, actions in (("actions/role-actions.php", ROLE_ACTIONS),
                         ("actions/settings-actions.php", SETTINGS_ACTIONS)):
        src = read(rel)
        assert src.startswith("<?php\n\ndeclare(strict_types=1);\n"), rel
        assert "if (!defined('APP_ROOT')) {" in src and GUARD_END in src, rel
        for action in actions:
            assert src.count(f"if ($action === '{action}')") == 1, (rel, action)
            documented = re.search(rf"^ \*   {re.escape(action)}\s+\S", src, re.M)
            assert documented or action in UNDOCUMENTED_HEADER_ACTIONS, f"شرح فارسی سرآیند برای {action} نیست"
        body = src.split(GUARD_END, 1)[1]
        assert body.count("if ($action === ") == len(actions), rel
        # دام‌های include: فقط کلوژرهای بی‌نام static مجازند؛ تعریف تابع نام‌دار ممنوع
        assert not re.search(r"\bfunction\s+[A-Za-z_]", body), rel
        assert body.count("{") == body.count("}"), rel


def test_version_manifest_and_package():
    # VERSION از ۱.۶۲.۱۷ جلوتر رفته؛ پین نسخهٔ جاری در تست ریلیز ۱.۶۲.۱۷ است.
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.16"
    assert manifest["requires_version"] == "1.61.9"
    assert manifest["migrations"] == ["009-traffic-destination-multi-approver.sql", "010-org-liaison-units.sql"]
    files = manifest["files"]
    assert len(files) == 49 and len(set(files)) == 49
    for need in ("VERSION", "index.php", "actions/role-actions.php", "actions/settings-actions.php",
                 "actions/org-actions.php", "actions/ticket-actions.php", "actions/asset-actions.php",
                 "actions/service-actions.php", "food-ticket-web/assets/food-capitalio-dashboard-r42.js"):
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
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.16"
    # بستهٔ تاریخی است: برابری با کد جاری بررسی نمی‌شود (۱.۶۲.۵۲ اکشن تازه به
    # settings-actions.php اضافه کرد). یکپارچگی خود بسته با SHA-256 تأیید می‌شود.
    for rel in ("actions/role-actions.php", "actions/settings-actions.php"):
        assert f"files/{rel}" in names
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")
    # صفحهٔ دانلود اکنون بستهٔ ۱.۶۲.۱۷ را نشان می‌دهد؛ پین SHA صفحه در تست ریلیز ۱.۶۲.۱۷ است.


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

