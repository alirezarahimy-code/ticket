# -*- coding: utf-8 -*-
"""تست‌های ایستای ریلیز ۱.۶۲.۱۴ — بازسازی فاز ۲، برش ۴:
جداسازی ۷ اکشن «خدمات، دسته‌ها و پایگاه دانش» به actions/service-actions.php
بدون هیچ تغییر رفتاری + پین‌های بسته‌بندی/صفحهٔ دانلود طبق سیاست انتشار.
"""
import hashlib
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.14-service-actions-split-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-14-service-actions-split"
GUARD_END = "    exit;\n}\n\n"

SERVICE_ACTIONS = [
    "add_service",
    "add_service_field",
    "edit_service",
    "delete_service",
    "save_service_category",
    "delete_service_category",
    "add_knowledge",
]

REQUIRE_LINE = "require __DIR__ . '/actions/service-actions.php';"
BANNER = "// ───── گروه اکشن: خدمات، دسته‌ها و پایگاه دانش — ۷ اکشن از ۱.۶۲.۱۴ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────"


def read(rel):
    return (ROOT / rel).read_text(encoding="utf-8")


def test_index_requires_service_actions_in_original_order():
    src = read("index.php")
    assert src.count(REQUIRE_LINE) == 1
    assert BANNER in src
    # ترتیب اجرای قبلی حفظ شده: اموال < درخواست‌ها < خدمات < چارت سازمانی
    a = src.index("require __DIR__ . '/actions/asset-actions.php';")
    t = src.index("require __DIR__ . '/actions/ticket-actions.php';")
    s = src.index(REQUIRE_LINE)
    o = src.index("require __DIR__ . '/actions/org-actions.php';")
    assert a < t < s < o
    # هیچ‌کدام از ۷ اکشن دیگر در index.php نیستند
    for action in SERVICE_ACTIONS:
        assert f"if ($action === '{action}')" not in src, action


def test_service_actions_file_structure_and_persian_descriptions():
    src = read("actions/service-actions.php")
    assert src.startswith("<?php\n\ndeclare(strict_types=1);\n")
    # محافظ دسترسی مستقیم
    assert "if (!defined('APP_ROOT')) {" in src and GUARD_END in src
    # شرح فارسی تک‌تک اکشن‌ها در سرآیند
    for action in SERVICE_ACTIONS:
        assert f"if ($action === '{action}')" in src, action
        assert src.count(f"if ($action === '{action}')") == 1, action
        assert re.search(rf"^ \*   {re.escape(action)}\s+\S", src, re.M), f"شرح فارسی سرآیند برای {action} نیست"
    # بدنه دقیقاً ۷ اکشن دارد و تمیز است (بدون تعریف تابع/continue)
    body = src.split(GUARD_END, 1)[1]
    assert body.count("if ($action === ") == 7
    assert "function " not in body and "continue;" not in body
    assert body.count("{") == body.count("}")


def test_version_manifest_and_package():
    # VERSION از ۱.۶۲.۱۵ جلوتر رفته؛ پین نسخهٔ جاری در تست ریلیز ۱.۶۲.۱۵ است.
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.14"
    assert manifest["requires_version"] == "1.61.9"
    assert manifest["migrations"] == ["009-traffic-destination-multi-approver.sql", "010-org-liaison-units.sql"]
    files = manifest["files"]
    assert len(files) == 47 and len(set(files)) == 47
    for need in ("VERSION", "index.php", "actions/service-actions.php", "actions/org-actions.php",
                 "actions/ticket-actions.php", "actions/asset-actions.php",
                 "food-ticket-web/assets/food-capitalio-dashboard-r42.js",
                 "food-ticket-web/assets/food-capitalio-dashboard-r42.css"):
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
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.14"
    assert json.loads(zf.read("manifest.json").decode("utf-8"))["version"] == "1.62.14"
    # فایل‌های داخل ZIP بایت‌به‌بایت با مخزن یکی هستند (نمونه‌های حساس)
    # index.php در ۱.۶۲.۱۵ (cache-bust) تغییر کرد؛ برابری بایت‌به‌بایت با مخزن فقط برای فایل برش:
    assert zf.read("files/actions/service-actions.php") == (ROOT / "actions/service-actions.php").read_bytes()
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")
    # صفحهٔ دانلود اکنون بستهٔ ۱.۶۲.۱۵ را نشان می‌دهد؛ پین SHA صفحه در تست ریلیز ۱.۶۲.۱۵ است.


def test_panel_asset_refs_are_all_packaged():
    """قاعدهٔ دائمی fix1: هر asset ارجاع‌شده در index.html پنل غذا باید داخل همان بسته باشد."""
    zf = zipfile.ZipFile(ROOT / "downloads" / PACKAGE_NAME)
    names = set(zf.namelist())
    html = zf.read("files/food-ticket-web/index.html").decode("utf-8")
    for ref in sorted(set(re.findall(r'(?:src|href)="(assets/[^"?]+)', html))):
        assert f"files/food-ticket-web/{ref}" in names, f"ارجاع بدون فایل در بسته: {ref}"


def test_download_page_lists_only_latest_package():
    page = read("downloads/index.html")
    # از ۱.۶۲.۱۵ صفحهٔ دانلود فقط بستهٔ جدید را نشان می‌دهد.
    assert "ticket-update-1.62.19-bulk-user-structure-actions-split-20261025.zip" in page
    assert PACKAGE_NAME not in page
    assert "ticket-update-1.62.13-ticket-asset-actions-split-fix1-20261025.zip" not in page
    assert "ticket-update-1.62.13-ticket-asset-actions-split-20261025.zip" not in page
    assert "actions/service-actions.php" in page
