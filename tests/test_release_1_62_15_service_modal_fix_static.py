# -*- coding: utf-8 -*-
"""تست‌های ایستای ریلیز ۱.۶۲.۱۵ — اصلاح پاپ‌آپ‌های فرم خدمات:
۱) پاپ‌آپ نباید زیر هدر برود (z-index در قاعدهٔ اختصاصی تثبیت شده باشد)
۲) بستن با کلیک روی پس‌زمینه مثل بقیهٔ پاپ‌آپ‌های سامانه
+ پین‌های بسته‌بندی/صفحهٔ دانلود طبق سیاست انتشار.
"""
import hashlib
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.15-service-modal-fix-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-15-service-modal-fix"


def read(rel):
    return (ROOT / rel).read_text(encoding="utf-8")


def test_modal_layer_keeps_high_z_index_against_generic_rule():
    css = read("assets/style.css")
    # قاعدهٔ عمومی که z-index:1 تحمیل می‌کرد هنوز هست (نباید چیدمان بقیهٔ صفحات عوض شود)
    assert ".content-wrap > *:not(.ambient-orbits) { position: relative; z-index: 1; }" in css
    # قاعدهٔ اختصاصی با همان specificity و دیرتر در فایل، z-index را برمی‌گرداند
    generic = css.index(".content-wrap > *:not(.ambient-orbits)")
    specific = css.index(".content-wrap > .service-modal-layer { position: fixed; z-index: 1300; }")
    assert generic < specific
    # لایهٔ پایه هم مثل قبل fixed و 1300 است
    assert ".service-modal-layer { position: fixed; inset: 0; z-index: 1300;" in css


def test_backdrop_click_closes_service_modal_like_other_popups():
    js = read("assets/app.js")
    # کلیک روی خود لایه یا روی .service-modal (بیرونِ کارت) هر دو می‌بندند
    assert "event.target === modalLayer || (event.target.classList && event.target.classList.contains('service-modal'))" in js
    # Esc و دکمهٔ بستن مثل قبل
    assert "if (event.key === 'Escape' && !modalLayer.hidden) closeModal();" in js
    assert "modalLayer.querySelectorAll('[data-modal-close]')" in js


def test_cache_bust_versions_bumped():
    src = read("index.php")
    assert "assets/style.css?v=3.4.46" in src
    assert "assets/app.js?v=3.4.27" in src
    assert "assets/style.css?v=3.4.45" not in src
    assert "assets/app.js?v=3.4.26" not in src


def test_version_manifest_and_package():
    # VERSION از ۱.۶۲.۱۶ جلوتر رفته؛ پین نسخهٔ جاری در تست ریلیز ۱.۶۲.۱۶ است.
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.15"
    assert manifest["requires_version"] == "1.61.9"
    assert manifest["migrations"] == ["009-traffic-destination-multi-approver.sql", "010-org-liaison-units.sql"]
    files = manifest["files"]
    assert len(files) == 47 and len(set(files)) == 47
    for need in ("VERSION", "index.php", "assets/style.css", "assets/app.js",
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
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.15"
    # خود اصلاح داخل بسته هم هست
    assert ".content-wrap > .service-modal-layer { position: fixed; z-index: 1300; }" in zf.read("files/assets/style.css").decode("utf-8")
    assert "event.target.classList.contains('service-modal')" in zf.read("files/assets/app.js").decode("utf-8")
    # index.php در ۱.۶۲.۱۶ (برش ۵) تغییر کرد؛ برابری بایت‌به‌بایت با مخزن برای فایل‌های همین اصلاح:
    for rel in ("assets/style.css", "assets/app.js"):
        assert zf.read(f"files/{rel}") == (ROOT / rel).read_bytes(), rel
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")
    # صفحهٔ دانلود اکنون بستهٔ ۱.۶۲.۱۶ را نشان می‌دهد؛ پین SHA صفحه در تست ریلیز ۱.۶۲.۱۶ است.


def test_panel_asset_refs_are_all_packaged():
    """قاعدهٔ دائمی fix1: هر asset ارجاع‌شده در index.html پنل غذا باید داخل همان بسته باشد."""
    zf = zipfile.ZipFile(ROOT / "downloads" / PACKAGE_NAME)
    names = set(zf.namelist())
    html = zf.read("files/food-ticket-web/index.html").decode("utf-8")
    for ref in sorted(set(re.findall(r'(?:src|href)="(assets/[^"?]+)', html))):
        assert f"files/food-ticket-web/{ref}" in names, f"ارجاع بدون فایل در بسته: {ref}"


def test_download_page_lists_only_latest_package():
    page = read("downloads/index.html")
    # از ۱.۶۲.۱۶ صفحهٔ دانلود فقط بستهٔ جدید را نشان می‌دهد.
    assert "ticket-update-1.62.20-backup-split-bootstrap-lib-20261025.zip" in page
    assert PACKAGE_NAME not in page
    assert "ticket-update-1.62.14-service-actions-split-20261025.zip" not in page
    assert "ticket-update-1.62.13-ticket-asset-actions-split-fix1-20261025.zip" not in page
