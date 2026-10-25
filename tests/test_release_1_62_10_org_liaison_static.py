# -*- coding: utf-8 -*-
"""تست ایستای نسخهٔ ۱.۶۲.۱۰ — واحدهای مستقل (نمایندگی) در دو سمت مدیرعامل."""
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.10-org-liaison-units-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-10-org-liaison-units"


def read(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


def test_migration_adds_chart_side_idempotently():
    sql = read("migrations/010-org-liaison-units.sql")
    assert "org_units" in sql
    assert "chart_side" in sql
    assert "IF NOT EXISTS" in sql.upper() or "information_schema" in sql.lower()
    assert "DROP" not in sql.upper() and "DELETE" not in sql.upper()
    # ستون در اسکیمای نصب تازه هم هست
    for name in ("schema.sql", "install-empty-db.sql"):
        src = read(name)
        assert "chart_side" in src, name
    # ترمیم خودکار اسکیما
    assert "chart_side" in read("organization.php")


def test_index_php_supports_liaison_node():
    # از ۱.۶۲.۱۲ اکشن‌های سازمان در actions/org-actions.php هستند (برش ۱ فاز ۲).
    src = read("index.php") + "\n" + read("actions/org-actions.php")
    # save_org_node نوع liaison را می‌پذیرد
    assert "'ceo', 'deputy', 'department', 'member', 'liaison'" in src
    # هر سمت فقط یک واحد فعال
    assert "در این سمت مدیرعامل قبلاً یک واحد مستقل تعریف شده است" in src
    # انتصاب نماینده بدون تغییر نقش
    assert "نقش او تغییر نمی‌کند" in src or "نقش کاربر دست‌نخورده می‌ماند" in src
    # assign/remove manager نوع liaison را می‌پذیرند (دو بار)
    assert src.count("'ceo', 'deputy', 'department', 'liaison'") >= 2
    # دادهٔ چارت سمت واحد را به مرورگر می‌فرستد
    assert "'chartSide' => (string) ($unit['chart_side'] ?? '')" in src
    # نسخهٔ کش دارایی‌ها
    assert "assets/organization.js?v=7" in src
    assert "assets/style.css?v=3.4.46" in src


def test_organization_js_renders_liaison_band():
    js = read("assets/organization.js")
    assert "liaisonAt" in js
    assert "org-liaison-band" in js
    assert "org-liaison-side-right" in js and "org-liaison-side-left" in js
    assert "data-liaison-side" in js
    assert "add-liaison" in js
    assert "chart_side" in js
    # نماینده در آمار کارشناس/بدون جایگاه نمی‌افتد و عنوانش درست است
    assert "managed.type === 'liaison'" in js
    assert "نمایندهٔ " in js
    assert "واحد مستقل — خارج از زیرمجموعهٔ مدیرعامل" in js


def test_css_positions_liaisons_without_connectors():
    css = read("assets/style.css")
    band = re.search(r"\.org-liaison-band\s*\{([^}]*)\}", css)
    assert band and "position: absolute" in band.group(1)
    assert "pointer-events: none" in band.group(1)
    pos = re.search(r"\.org-liaison-pos\s*\{([^}]*)\}", css)
    assert pos and "position: absolute" in pos.group(1)
    assert "pointer-events: auto" in pos.group(1)
    # کمی پایین‌تر از کادر مدیرعامل
    assert re.search(r"top:\s*\d+px", pos.group(1))
    assert ".org-liaison-side-right { left: calc(50%" in css
    assert ".org-liaison-side-left { right: calc(50%" in css


def test_help_guide_covers_liaison_units():
    guide = read("help-guide.php")
    assert "واحدهای مستقل (نمایندگی) کنار مدیرعامل" in guide
    assert "هیچ خط اتصالی" in guide
    assert 'data-kw="واحد مستقل نمایندگی نماینده مستقل' in guide


def test_version_manifest_and_package():
    # پین VERSION از ۱.۶۲.۱۱ به تست نسخهٔ جدید منتقل شد.
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.10"
    assert manifest["migrations"] == ["010-org-liaison-units.sql"]
    for name in ("index.php", "organization.php", "assets/organization.js", "assets/style.css", "help-guide.php"):
        assert name in manifest["files"], name
    report = read(f"{UPDATE_DIR}/CHANGE-REPORT-FA.md")
    assert "واحد" in report and "مدیرعامل" in report

    package = ROOT / "downloads" / PACKAGE_NAME
    assert package.exists(), "بستهٔ ZIP نسخهٔ ۱.۶۲.۱۰ ساخته نشده است"
    zf = zipfile.ZipFile(package)
    names = zf.namelist()
    pkg_manifest = json.loads(zf.read("manifest.json").decode("utf-8"))
    assert pkg_manifest["version"] == "1.62.10"
    # قانون بسته‌بندی: مهاجرتِ manifest باید در ریشهٔ ZIP هم باشد.
    assert "migrations/010-org-liaison-units.sql" in names
    assert "files/migrations/010-org-liaison-units.sql" in names
    pattern = re.compile(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$")
    for name in names:
        if not name.endswith("/"):
            assert pattern.match(name), name
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.10"
    # checksum
    import hashlib
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")


def test_download_page_lists_only_latest_package():
    page = read("downloads/index.html")
    # از ۱.۶۲.۱۱ صفحهٔ دانلود فقط بستهٔ جدید را نشان می‌دهد.
    assert "ticket-update-1.62.15-service-modal-fix-20261025.zip" in page
    assert PACKAGE_NAME not in page
    assert "ticket-update-1.62.9-manual-ticket-date-guest-20261025.zip" not in page
