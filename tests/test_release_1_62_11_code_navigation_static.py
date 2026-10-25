# -*- coding: utf-8 -*-
"""تست ایستای نسخهٔ ۱.۶۲.۱۱ — فاز ۱ بهینه‌سازی کدها (بدون تغییر رفتاری)."""
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.11-code-navigation-phase1-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-11-code-navigation-phase1"


def read(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


def test_index_has_navigation_banners():
    src = read("index.php")
    # ۳ بانر ناحیه + ۱۹ بانر گروه اکشن
    assert src.count("═══ ناحیهٔ") >= 3
    # از ۱.۶۲.۱۶ (برش ۵): سه گروه در دو بنر require ادغام شدند → ۱۸
    # از ۱.۶۲.۱۷ (برش ۶): سه گروه در دو بنر require ادغام شدند → ۱۷
    # از ۱.۶۲.۱۸ (برش ۷): پنج گروه (ورود، پروفایل، برند، CD/DVD، اعلان‌ها) در چهار بنر require ادغام شدند → ۱۶
    assert src.count("گروه اکشن:") == 16
    # از ۱.۶۲.۱۸ بنر ورود به «احراز هویت، پروفایل و تم» تغییر نام داد (برش ۷)
    for banner in ("احراز هویت، پروفایل و تم", "ساختار سازمانی (چارت)", "چرخهٔ کامل درخواست‌ها"):
        assert banner in src, banner


def test_bootstrap_has_function_family_toc():
    src = read("bootstrap.php")
    head = src[:3000]
    assert "نقشهٔ خانواده‌های توابع" in head
    assert "docs/ARCHITECTURE-FA.md" in head


def test_architecture_and_audit_docs_exist():
    arch = read("docs/ARCHITECTURE-FA.md")
    assert "جریان یک درخواست" in arch
    assert "قرارداد بستهٔ به‌روزرسانی" in arch
    audit = read("docs/CODE-AUDIT-FA.md")
    assert "snapshot-pre-refactor-20261025" in audit
    assert "هیچ کدی حذف نشده" in audit


def test_safety_snapshot_documented_in_handoff():
    handoff = read("docs/HANDOFF-FA.md")
    assert "snapshot-pre-refactor-20261025" in handoff


def test_version_manifest_and_package():
    # پین VERSION از ۱.۶۲.۱۲ به تست نسخهٔ جدید منتقل شد.
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.11"
    # مهاجرت جدید ندارد؛ 009 و 010 برای ارتقای مستقیم همراه بسته‌اند.
    assert manifest["migrations"] == [
        "009-traffic-destination-multi-approver.sql",
        "010-org-liaison-units.sql",
    ]
    # بستهٔ کاملاً تجمعی: فایل‌های کلیدی همهٔ نسخه‌های 1.62.4 تا 1.62.11
    for name in (
        "index.php", "bootstrap.php", "organization.php", "traffic-control.php",
        "food-ticket.php", "food-ticket-groups.php", "assets/app.js",
        "assets/theme-mode.css", "assets/organization.js", "assets/style.css",
        "app/Views/layout/header.php", "app/Views/layout/footer.php",
    ):
        assert name in manifest["files"], name

    package = ROOT / "downloads" / PACKAGE_NAME
    assert package.exists(), "بستهٔ ZIP نسخهٔ ۱.۶۲.۱۱ ساخته نشده است"
    zf = zipfile.ZipFile(package)
    names = zf.namelist()
    assert json.loads(zf.read("manifest.json").decode("utf-8"))["version"] == "1.62.11"
    # قانون بسته‌بندی: مهاجرت‌های manifest در ریشهٔ ZIP
    for mig in manifest["migrations"]:
        assert f"migrations/{mig}" in names, mig
    pattern = re.compile(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$")
    for name in names:
        if not name.endswith("/"):
            assert pattern.match(name), name
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.11"
    import hashlib
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")


def test_download_page_lists_only_latest_package():
    page = read("downloads/index.html")
    # از ۱.۶۲.۱۲ صفحهٔ دانلود فقط بستهٔ جدید را نشان می‌دهد.
    assert "ticket-update-1.62.18-account-misc-actions-split-20261025.zip" in page
    assert PACKAGE_NAME not in page
    assert "ticket-update-1.62.10-org-liaison-units-20261025.zip" not in page
    assert "بدون تغییر رفتاری" in page
