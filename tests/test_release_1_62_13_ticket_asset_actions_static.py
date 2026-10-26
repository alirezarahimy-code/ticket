# -*- coding: utf-8 -*-
"""تست ایستای نسخهٔ ۱.۶۲.۱۳ — برش‌های ۲ و ۳ فاز ۲: جداسازی اکشن‌های درخواست‌ها و اموال/اسکن دامین."""
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.13-ticket-asset-actions-split-fix1-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-13-ticket-asset-actions-split"
TICKET_ACTIONS = (
    "create_ticket", "cancel_ticket", "decide_ticket_cancellation",
    "claim_ticket", "reopen_ticket", "reply", "update_ticket",
)
ASSET_ACTIONS = (
    "collect_inventory", "asset_profile_selftest", "import_domain_assets",
    "domain_scan_start", "domain_scan_batch", "ping_assets", "domain_scan_retry",
    "domain_scan_test_selected", "domain_scan_selected", "save_inventory_form",
)
GUARD_END = "    exit;\n}\n\n"


def read(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


def body_of(name: str) -> str:
    src = read(name)
    assert src.count(GUARD_END) == 1, name
    return src.split(GUARD_END, 1)[1]


def test_index_requires_both_files_in_place_and_in_order():
    src = read("index.php")
    t = "require __DIR__ . '/actions/ticket-actions.php';"
    a = "require __DIR__ . '/actions/asset-actions.php';"
    assert t in src and a in src
    # ترتیب اجرای قبلی حفظ شده: اموال قبل از درخواست‌ها، هر دو قبل از گروه خدمات
    # (از ۱.۶۲.۱۴ گروه خدمات هم به actions/service-actions.php منتقل شد)
    assert src.index(a) < src.index(t) < src.index("require __DIR__ . '/actions/service-actions.php';")
    for action in TICKET_ACTIONS + ASSET_ACTIONS:
        assert f"if ($action === '{action}')" not in src, action


def test_ticket_actions_file_sections_and_persian_descriptions():
    src = read("actions/ticket-actions.php")
    for action in TICKET_ACTIONS:
        assert f"if ($action === '{action}')" in src, action
    # شرح فارسی بخش‌ها در سرآیند (خواستهٔ کاربر)
    assert "شرح فارسی بخش‌ها" in src
    assert "ثبت درخواست جدید" in src and "بازگشایی درخواست" in src
    assert "defined('APP_ROOT')" in src and "http_response_code(404)" in src
    body = body_of("actions/ticket-actions.php")
    assert body.count("{") == body.count("}") == 130
    # بدون دام include
    assert "?>" not in body
    for trap in (r"\breturn\b", r"\bfunction\s", r"\bgoto\b", r"\bbreak;", r"\bcontinue;", r"__FILE__", r"__DIR__"):
        assert not re.search(trap, body), trap


def test_asset_actions_file_sections_and_persian_descriptions():
    src = read("actions/asset-actions.php")
    for action in ASSET_ACTIONS:
        assert f"if ($action === '{action}')" in src, action
    assert "شرح فارسی بخش‌ها" in src
    assert "اسکن دامین" in src and "پینگ گروهی" in src
    assert "defined('APP_ROOT')" in src and "http_response_code(404)" in src
    body = body_of("actions/asset-actions.php")
    assert body.count("{") == body.count("}") == 64
    assert "?>" not in body
    # دام‌های قطعی ممنوع؛ کلوژر بی‌نام و continue داخل حلقه مجاز و شناخته‌شده‌اند
    for trap in (r"\breturn\b", r"\bgoto\b", r"\bbreak;", r"__FILE__", r"__DIR__"):
        assert not re.search(trap, body), trap
    assert body.count("static function") == 1  # فقط کلوژر $pingFail
    assert body.count("continue;") == 1        # فقط داخل حلقهٔ گزارش


def test_version_manifest_and_package():
    # VERSION از ۱.۶۲.۱۴ جلوتر رفته؛ پین نسخهٔ جاری در تست ریلیز ۱.۶۲.۱۴ است.
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.13"
    assert manifest["migrations"] == [
        "009-traffic-destination-multi-approver.sql",
        "010-org-liaison-units.sql",
    ]
    for name in ("actions/org-actions.php", "actions/ticket-actions.php",
                 "actions/asset-actions.php", "index.php", "bootstrap.php"):
        assert name in manifest["files"], name

    package = ROOT / "downloads" / PACKAGE_NAME
    assert package.exists(), "بستهٔ ZIP نسخهٔ ۱.۶۲.۱۳ ساخته نشده است"
    zf = zipfile.ZipFile(package)
    names = zf.namelist()
    for req in ("files/actions/org-actions.php", "files/actions/ticket-actions.php",
                "files/actions/asset-actions.php"):
        assert req in names, req
    assert json.loads(zf.read("manifest.json").decode("utf-8"))["version"] == "1.62.13"
    for mig in manifest["migrations"]:
        assert f"migrations/{mig}" in names, mig
    pattern = re.compile(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$")
    for name in names:
        if not name.endswith("/"):
            assert pattern.match(name), name
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.13"
    import hashlib
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")
    # صفحهٔ دانلود اکنون بستهٔ ۱.۶۲.۱۴ را نشان می‌دهد؛ پین SHA صفحه در تست ریلیز ۱.۶۲.۱۴ است.


def test_download_page_lists_only_latest_package():
    page = read("downloads/index.html")
    # از ۱.۶۲.۱۴ صفحهٔ دانلود فقط بستهٔ جدید را نشان می‌دهد؛ بسته‌های قبلی نباید در صفحه باشند.
    assert "ticket-update-1.62.20-backup-split-bootstrap-lib-20261025.zip" in page
    assert PACKAGE_NAME not in page
    assert "ticket-update-1.62.12-org-actions-split-20261025.zip" not in page
    assert "actions/ticket-actions.php" in page and "actions/asset-actions.php" in page


def test_fix1_dashboard_files_and_all_panel_asset_refs_are_packaged():
    """fix1: بستهٔ تجمعی باید فایل‌های داشبورد 1.62.2 و همهٔ assetهای ارجاع‌شدهٔ پنل غذا را حمل کند."""
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    for name in (
        "food-ticket-web/assets/food-capitalio-dashboard-r42.js",
        "food-ticket-web/assets/food-capitalio-dashboard-r42.css",
        "permissions.php", "chatbot.php", "food-order.php", "food-ticket-web-host.php",
    ):
        assert name in manifest["files"], name
    zf = zipfile.ZipFile(ROOT / "downloads" / PACKAGE_NAME)
    names = set(zf.namelist())
    html = zf.read("files/food-ticket-web/index.html").decode("utf-8")
    for ref in sorted(set(re.findall(r'(?:src|href)="(assets/[^"?]+)', html))):
        assert f"files/food-ticket-web/{ref}" in names, f"ارجاع بدون فایل در بسته: {ref}"
