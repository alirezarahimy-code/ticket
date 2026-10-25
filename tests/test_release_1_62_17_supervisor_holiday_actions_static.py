# -*- coding: utf-8 -*-
"""تست‌های ایستای ریلیز ۱.۶۲.۱۷ — بازسازی فاز ۲، برش ۶:
جداسازی ۴ اکشن «تصمیم‌های سرپرست و امتیازدهی» به actions/supervisor-actions.php
و ۴ اکشن «تقویم و تعطیلات» به actions/holiday-actions.php
بدون هیچ تغییر رفتاری + پین‌های بسته‌بندی/صفحهٔ دانلود طبق سیاست انتشار.
"""
import hashlib
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.17-supervisor-holiday-actions-split-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-17-supervisor-holiday-actions-split"
GUARD_END = "    exit;\n}\n\n"

SUPERVISOR_ACTIONS = [
    "food_order_cancel_decide",
    "supervisor_decision",
    "supervisor_reopen",
    "rate_ticket",
]
HOLIDAY_ACTIONS = [
    "toggle_holiday",
    "add_holiday",
    "import_holidays",
    "delete_holiday",
]


def read(rel):
    return (ROOT / rel).read_text(encoding="utf-8")


def test_index_requires_both_files_in_original_order():
    src = read("index.php")
    sup = "require __DIR__ . '/actions/supervisor-actions.php';"
    hol = "require __DIR__ . '/actions/holiday-actions.php';"
    assert src.count(sup) == 1 and src.count(hol) == 1
    # ترتیب اجرای قبلی حفظ شده: اعلان‌ها < سرپرستی/امتیازدهی < اموال < درخواست‌ها < خدمات < تعطیلات < ارتباط دارایی
    a = src.index("require __DIR__ . '/actions/asset-actions.php';")
    t = src.index("require __DIR__ . '/actions/ticket-actions.php';")
    s = src.index("require __DIR__ . '/actions/service-actions.php';")
    assert src.index(sup) < a < t < s < src.index(hol) < src.index("if ($action === 'add_asset_relation')")
    for action in SUPERVISOR_ACTIONS + HOLIDAY_ACTIONS:
        assert f"if ($action === '{action}')" not in src, action


def test_action_files_structure_and_persian_descriptions():
    for rel, actions in (("actions/supervisor-actions.php", SUPERVISOR_ACTIONS),
                         ("actions/holiday-actions.php", HOLIDAY_ACTIONS)):
        src = read(rel)
        assert src.startswith("<?php\n\ndeclare(strict_types=1);\n"), rel
        assert "if (!defined('APP_ROOT')) {" in src and GUARD_END in src, rel
        for action in actions:
            assert src.count(f"if ($action === '{action}')") == 1, (rel, action)
            assert re.search(rf"^ \*   {re.escape(action)}\s+\S", src, re.M), f"شرح فارسی سرآیند برای {action} نیست"
        body = src.split(GUARD_END, 1)[1]
        assert body.count("if ($action === ") == len(actions), rel
        # دام‌های include: تعریف تابع نام‌دار ممنوع (فراخوانی function_exists مجاز است)
        assert not re.search(r"\bfunction\s+[A-Za-z_]", body), rel
        assert body.count("{") == body.count("}"), rel


def test_version_manifest_and_package():
    # VERSION از ۱.۶۲.۱۸ جلوتر رفته؛ پین نسخهٔ جاری در تست ریلیز ۱.۶۲.۱۸ است.
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.17"
    assert manifest["requires_version"] == "1.61.9"
    assert manifest["migrations"] == ["009-traffic-destination-multi-approver.sql", "010-org-liaison-units.sql"]
    files = manifest["files"]
    assert len(files) == 51 and len(set(files)) == 51
    for need in ("VERSION", "index.php", "actions/supervisor-actions.php", "actions/holiday-actions.php",
                 "actions/org-actions.php", "actions/ticket-actions.php", "actions/asset-actions.php",
                 "actions/service-actions.php", "actions/role-actions.php", "actions/settings-actions.php",
                 "food-ticket-web/assets/food-capitalio-dashboard-r42.js"):
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
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.17"
    # index.php در ۱.۶۲.۱۸ (برش ۷) تغییر کرد؛ برابری بایت‌به‌بایت با مخزن برای فایل‌های همین برش:
    for rel in ("actions/supervisor-actions.php", "actions/holiday-actions.php"):
        assert zf.read(f"files/{rel}") == (ROOT / rel).read_bytes(), rel
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")
    # صفحهٔ دانلود اکنون بستهٔ ۱.۶۲.۱۸ را نشان می‌دهد؛ پین SHA صفحه در تست ریلیز ۱.۶۲.۱۸ است.


def test_panel_asset_refs_are_all_packaged():
    """قاعدهٔ دائمی fix1: هر asset ارجاع‌شده در index.html پنل غذا باید داخل همان بسته باشد."""
    zf = zipfile.ZipFile(ROOT / "downloads" / PACKAGE_NAME)
    names = set(zf.namelist())
    html = zf.read("files/food-ticket-web/index.html").decode("utf-8")
    for ref in sorted(set(re.findall(r'(?:src|href)="(assets/[^"?]+)', html))):
        assert f"files/food-ticket-web/{ref}" in names, f"ارجاع بدون فایل در بسته: {ref}"


def test_download_page_lists_only_latest_package():
    page = read("downloads/index.html")
    # از ۱.۶۲.۱۸ صفحهٔ دانلود فقط بستهٔ جدید را نشان می‌دهد.
    assert "ticket-update-1.62.18-account-misc-actions-split-20261025.zip" in page
    assert PACKAGE_NAME not in page
    assert "ticket-update-1.62.16-role-settings-actions-split-20261025.zip" not in page
    assert "ticket-update-1.62.15-service-modal-fix-20261025.zip" not in page
    assert "actions/supervisor-actions.php" in page and "actions/holiday-actions.php" in page
