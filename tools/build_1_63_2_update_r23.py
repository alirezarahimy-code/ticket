#!/usr/bin/env python3
"""Build the cumulative 1.63 -> 1.63.2 r23 installable update package.

Starts from the verified r22 cumulative package, preserves its fixes, refreshes
the food panel and release-notice files, and rebuilds the installable archive.
No database migrations are added.
"""
from __future__ import annotations

import hashlib
import json
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DOWNLOADS = ROOT / "downloads"
BASE_PACKAGE = DOWNLOADS / "ticket-update-1.63-to-1.63.2-r22-food-reconciliation-audit-20261106.zip"
PACKAGE_NAME = "ticket-update-1.63-to-1.63.2-r23-panel-render-notice-fix-20261106.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.63.2-r23-panel-render-notice-fix.md"
GUIDE_NAME = "docs/GUIDE-FA-install-1.63.2.md"
RELEASE_VERSION = "1.63.2"
REQUIRES_VERSION = "1.63"
RELEASE_DATE = "2026-11-06"
ZIP_DATE = (2026, 11, 6, 15, 0, 0)

# Files whose current source must travel with the cumulative r22 payload.
# r23 refreshes the panel host UI, version, and revision-aware release note payload.
REFRESH_FILES = [
    "api/inventory.php",
    "asset-inventory-page.php",
    "asset-names.php",
    "domain-scan.php",
    "inventory.php",
    "index.php",
    "tools/asset_dedupe.php",
    "tools/test_asset_dedupe_offline.php",
    "tools/test_asset_names_offline.php",
    "food-ticket.php",
    "food-ticket-release-notes.php",
    "food-reconciliation.php",
    "assets/food-menu.js",
    "help-guide.php",
    "chatbot.php",
    "tools/test_source_time_delete_offline.php",
    "docs/ASSET-SCAN-CLOSING-FA.md",
    "docs/NETWORK-INVENTORY-SETUP-FA.md",
    "food-ticket-web/assets/food-menu.css",
    "food-ticket-web/assets/food-menu.js",
    "food-ticket-web/assets/food-org-groups.js",
    "food-ticket-web/index.html",
    "modules/request-center/Pages/create.php",
    "lib/release-notes.php",
]

ADD_OR_REPLACE_FILES = [
    "VERSION",
    "lib/asset-dedupe.php",
    "docs/ASSET-IDENTITY-AND-LIVE-TEST-FA.md",
    "docs/ASSET-NAME-EXACT-FA.md",
    "tests/test_asset_inventory_flow_sqlite.php",
    "tools/test_food_reconciliation_disposition_offline.php",
    "tools/test_manual_guest_delivery_offline.php",
    "tests/test_food_reconciliation_audit_static.py",
    "tests/test_selftest_panel_render.js",
    "tests/js/food-absent-report.test.js",
    "tests/test_food_absent_report_static.py",
    "tools/test_food_aggregate_report_offline.php",
    "tools/test_source_delete_offline.php",
    GUIDE_NAME,
]

# The two old r20-only guides are intentionally not carried forward as current
# install instructions. The new package adds the versioned 1.63.2 guide instead.
REMOVE_FROM_PAYLOAD = {
    "docs/GUIDE-FA-install-1.63.1.md",
    "docs/INSTALL-R20-ASSET-TENTER-FA.md",
}
REQUIRED_INSTALL_FILES = [
    "VERSION",
    "lib/release-notes.php",
    "food-ticket.php",
    "food-ticket-release-notes.php",
    "food-reconciliation.php",
    "assets/food-menu.js",
    "food-ticket-web/assets/food-menu.js",
    "tools/test_food_reconciliation_disposition_offline.php",
    "tools/test_manual_guest_delivery_offline.php",
    "tests/test_food_reconciliation_audit_static.py",
    "tests/test_selftest_panel_render.js",
    "tests/js/food-absent-report.test.js",
    "tests/test_food_absent_report_static.py",
    "tools/test_food_aggregate_report_offline.php",
    "tools/repair_source_rows.php",
    "tools/test_source_delete_offline.php",
    "tools/test_source_time_delete_offline.php",
    "tools/asset_dedupe.php",
    "tools/test_asset_dedupe_offline.php",
    "tools/test_asset_names_offline.php",
    "lib/asset-dedupe.php",
    "tools/collect_server_inventory.ps1",
    "tools/collect_via_smb.ps1",
    "tools/diag_extract.php",
    "tools/RUN-DIAG-EXTRACT.bat",
    "tools/GET-DIAG-EXTRACT.bat",
    "modules/request-center/Pages/create.php",
    GUIDE_NAME,
]


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def read_source(relative: str) -> bytes:
    path = ROOT / relative
    if not path.is_file():
        raise SystemExit(f"فایل لازم در درخت کاری پیدا نشد: {relative}")
    return path.read_bytes()


def report_text(file_count: int) -> str:
    return f"""# گزارش بستهٔ به‌روزرسانی ۱.۶۳.۲-r23 — رفع اختلال پنل و اعلان تغییرات

- نسخهٔ مقصد: **{RELEASE_VERSION}**
- پیش‌نیاز: **{REQUIRES_VERSION} به بعد** (روی ۱.۶۳، ۱.۶۳.۱ و نصب r22 قابل نصب است)
- تاریخ ساخت: **{RELEASE_DATE}**
- فایل‌های نصب: **{file_count}**
- مهاجرت دیتابیس: **ندارد**
- پایه: بستهٔ تجمعی r22؛ اصلاحات شناسنامه، TENTER، گزارش غذا و تطبیق خرید/توزیع r22 حفظ شده‌اند.

## رفع نمایش کد در پنل غذا

تابع چاپ گزارش غایبین، HTML پنجرهٔ چاپ را با یک تگ `<script>` توکار در رشتهٔ اسکریپت اصلی می‌ساخت. در خروجی HTML میزبانی‌شده، خاتمه‌دهندهٔ تگ می‌تواند با اسکریپت اصلی تداخل کند؛ پیام «در حال آماده‌سازی» باقی می‌ماند و ادامهٔ JavaScript به‌صورت متن دیده می‌شود. r23 تگ script توکار را حذف می‌کند و چاپ را از handler پنجرهٔ والد آغاز می‌کند. آزمون ایستا بررسی می‌کند که اسکریپت اصلی تا انتهای پنل باز می‌ماند.

## رفع اعلان تغییرات

- شناسهٔ اعلان r23 مستقل از نسخهٔ محصول (`1.63.2-r23`) است؛ دیده یا بسته‌شدن اعلان r21/r22 آن را پنهان نمی‌کند.
- شمارنده حداکثر دو نمایش، برای هر بار بازشدن صفحه یک‌بار ثبت می‌شود، نه با هر رندر ناشی از درخواست‌های API و به‌روزرسانی رابط.
- نسخهٔ رابط از **1.37.55** به **1.37.56** افزایش یافته تا نسخهٔ کش‌شدهٔ قبلی قابل تشخیص باشد.

## آزمون‌های ثبت‌شده

- `tests/test_selftest_panel_render.js`: **۵۴ بررسی موفق**؛ شناسهٔ revision در پاسخ `/api/config`، شمارش در هر بازدید، و عدم اثر dismissal قدیمی پوشش داده شد.
- `tests/js/food-absent-report.test.js` و `tests/test_food_absent_report_static.py`: موفق؛ تابع چاپ فاقد تگ script توکار است و مرز اسکریپت اصلی برقرار می‌ماند.
- `tools/test_web_host_offline.php`: همهٔ بررسی‌های میزبانی و تزریق nonce موفق.
- `food-ticket.php` و `food-ticket-release-notes.php`: lint با PHP-WASM موفق.
- ZIP، manifest و SHA-256 داخلی/جانبی بررسی می‌شوند؛ بسته **بدون migration** است.

آزمون زندهٔ MySQL/MariaDB، Access/TENTER، یا نصب روی سرور مقصد در این محیط انجام نشده است. پیش از جایگزینی فایل‌ها پشتیبان بگیرید؛ پس از نصب، پنل را با Ctrl+F5 تازه کنید.
"""


def render_download_page(package_size: int, digest: str, file_count: int) -> str:
    return f'''<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>بستهٔ به‌روزرسانی ۱.۶۳.۲ — سامانهٔ درخواست پشتیبانی</title>
<style>
:root{{color-scheme:light;--ink:#132b3a;--muted:#5c7180;--line:#d9e4e8;--teal:#087f78;--amber:#d69623}}*{{box-sizing:border-box}}body{{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:linear-gradient(145deg,#e7f4f2,#f4f7fa 60%,#e8eff4);color:var(--ink);font:16px/1.9 Tahoma,"Segoe UI",sans-serif}}main{{width:min(100%,900px);padding:32px;background:#fff;border:1px solid var(--line);border-radius:20px;box-shadow:0 18px 50px #173d4a18}}.eyebrow{{color:var(--teal);font-size:13px;font-weight:700}}h1{{margin:4px 0 8px;font-size:26px}}h2{{margin:0 0 4px;font-size:19px}}p{{margin:8px 0 16px;color:var(--muted)}}.package{{margin:20px 0;padding:18px;border:1px solid var(--line);border-radius:14px;background:#fbfdfe}}.actions{{display:flex;flex-wrap:wrap;gap:10px;margin:14px 0}}.button{{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:9px 18px;border-radius:10px;text-decoration:none;font-weight:700}}.primary{{background:var(--teal);color:#fff}}.secondary{{border:1px solid var(--line);color:var(--ink);background:#fff}}.warning,.ok{{margin-top:16px;padding:13px 15px;border-radius:8px;font-size:14px}}.warning{{border-right:4px solid var(--amber);background:#fff8e8;color:#684b16}}.ok{{border-right:4px solid var(--teal);background:#effaf8;color:#12554f}}.filename{{direction:ltr;text-align:left;overflow-wrap:anywhere;font:13px/1.7 monospace}}ul{{margin:8px 0 14px}}@media(max-width:520px){{main{{padding:22px}}h1{{font-size:22px}}.actions a{{width:100%}}}}
</style></head>
<body><main><span class="eyebrow">صفحهٔ دانلود — آخرین نسخه</span>
<h1>بستهٔ به‌روزرسانی نسخهٔ ۱.۶۳.۲</h1>
<p>این بسته r22 را حفظ می‌کند و علاوه بر آن اختلال نمایش کد JavaScript در پنل غذا را با حذف script توکار از HTML چاپ برطرف می‌کند. شناسهٔ اعلان r23 از کلید نسخه‌های قبلی جداست و شمارندهٔ نمایش با هر رندر افزایش نمی‌یابد. نسخهٔ فایل پنل به 1.37.56 می‌رسد تا کش قدیمی قابل تشخیص باشد؛ بسته migration دیتابیس ندارد.</p>

<div class="ok"><b>پیش‌نیاز:</b> نسخهٔ <code>1.63</code> یا جدیدتر (از جمله <code>1.63.1</code>). بسته {file_count} فایل نصب دارد و هیچ مهاجرت دیتابیسی اجرا نمی‌کند.</div>

<section class="package"><h2>به‌روزرسانی نصب موجود — ۱.۶۳.۲-r23</h2>
<p>پس از پشتیبان‌گیری، ZIP را در بخش «به‌روزرسانی و پشتیبان‌گیری» پنل مدیر اصلی بارگذاری کنید. راهنمای نصب و گزارش تغییرات نیز در دسترس است.</p>
<div class="actions">
<a class="button primary" href="{PACKAGE_NAME}" download>دانلود بستهٔ ۱.۶۳.۲ (ZIP)</a>
<a class="button secondary" href="https://github.com/alirezarahimy-code/ticket/releases/download/v1.63.2/{PACKAGE_NAME}">دانلود از Release رسمی GitHub</a>
<a class="button secondary" href="{PACKAGE_NAME}.sha256" download>دریافت SHA-256</a>
<a class="button secondary" href="{REPORT_NAME}">گزارش تغییرات و آزمون‌ها</a>
<a class="button secondary" href="../{GUIDE_NAME}">راهنمای نصب</a></div>
<p class="filename">{PACKAGE_NAME}<br>حجم: {package_size:,} بایت<br>SHA-256: {digest}</p></section>

<h2>نکتهٔ ایمنی TENTER</h2>
<p>برای ردیف‌های قبلی ابتدا <code>php tools\\repair_source_rows.php</code> را بدون <code>--apply</code> اجرا و گزارش را بازبینی کنید. حذف واقعی فایل Access فقط پس از تهیهٔ پشتیبان، توقف Worker و افزودن صریح <code>--apply</code> انجام شود.</p>
<div class="warning"><b>آزمون زنده لازم است:</b> آزمون‌های داخل بسته آفلاین‌اند و دسترسی واقعی به Access/TENTER، SMB یا WinRM شبکهٔ مقصد را تأیید نمی‌کنند.</div>
</main></body></html>
'''


def main() -> None:
    if not BASE_PACKAGE.is_file():
        raise SystemExit(f"بستهٔ پایه پیدا نشد: {BASE_PACKAGE}")
    if (ROOT / "VERSION").read_text(encoding="ascii").strip() != RELEASE_VERSION:
        raise SystemExit("VERSION مخزن با نسخهٔ مقصد r23 برابر نیست.")

    with zipfile.ZipFile(BASE_PACKAGE) as archive:
        bad = archive.testzip()
        if bad is not None:
            raise SystemExit(f"بستهٔ پایه خراب است: {bad}")
        base_manifest = json.loads(archive.read("manifest.json"))
        if base_manifest.get("version") != "1.63.2" or base_manifest.get("requires_version") != REQUIRES_VERSION:
            raise SystemExit("نسخه یا پیش‌نیاز بستهٔ پایه غیرمنتظره است.")
        payload = {
            name[len("files/"):]: archive.read(name)
            for name in archive.namelist()
            if name.startswith("files/") and not name.endswith("/")
        }

    for relative in list(payload):
        if (relative in REMOVE_FROM_PAYLOAD
                or relative.startswith("CHANGE-REPORT-FA-1.63.1-")
                or relative == "CHANGE-REPORT-FA-1.63.2-r22-reconciliation-audit.md"):
            del payload[relative]
            continue

    for relative in REFRESH_FILES + ADD_OR_REPLACE_FILES:
        payload[relative] = read_source(relative)

    if "1.63.2" not in payload["lib/release-notes.php"].decode("utf-8", "replace"):
        raise SystemExit("یادداشت انتشار ۱.۶۳.۲ در lib/release-notes.php نیست.")
    panel_text = payload["food-ticket-web/index.html"].decode("utf-8", "replace")
    server_text = payload["food-ticket.php"].decode("utf-8", "replace")
    release_text = payload["food-ticket-release-notes.php"].decode("utf-8", "replace")
    if "PANEL_VERSION='1.37.56'" not in panel_text or "FOOD_TICKET_PANEL_VERSION = '1.37.56'" not in server_text:
        raise SystemExit("نسخهٔ پنل r23 در HTML و PHP هم‌خوان نیست.")
    if "'notification_id' => '1.63.2-r23'" not in release_text or "n.notification_id||n.version" not in panel_text:
        raise SystemExit("شناسهٔ یکتای اعلان r23 در payload نیست.")
    print_start = panel_text.find("function absentReportPrint()")
    print_end = panel_text.find("function reportClassicView()", print_start)
    if print_start < 0 or print_end <= print_start or "<script" in panel_text[print_start:print_end]:
        raise SystemExit("قالب چاپ هنوز تگ script توکار یا مرز نامعتبر دارد.")
    if "printWindow.addEventListener('load',printWhenReady,{once:true})" not in panel_text:
        raise SystemExit("شروع چاپ از handler پنجرهٔ والد در payload نیست.")
    if "food_ticket_source_minimal_where" not in payload["food-ticket.php"].decode("utf-8", "replace"):
        raise SystemExit("مسیر fallback اصلاح‌شدهٔ TENTER در payload نیست.")
    if "food_reconciliation_expected_remaining_assert" not in payload["food-reconciliation.php"].decode("utf-8", "replace"):
        raise SystemExit("بررسی ماندهٔ کهنه و تعیین تکلیف دقیق در payload نیست.")
    reconciliation_text = payload["food-reconciliation.php"].decode("utf-8", "replace")
    if "food_reconciliation_received_missing" not in reconciliation_text or "e.user_id = o.employee_id" not in reconciliation_text:
        raise SystemExit("اصلاحات ممیزی گزارش تطبیق r23 در payload نیست.")
    if "food_ticket_aggregate_identity_sets" not in payload["food-ticket.php"].decode("utf-8", "replace"):
        raise SystemExit("تطبیق هویت‌محور غایبین در payload نیست.")
    if "leftover_expected_remaining: m.remaining" not in payload["food-ticket-web/assets/food-menu.js"].decode("utf-8", "replace"):
        raise SystemExit("نمایش/ارسال ماندهٔ کاربر در payload نیست.")
    if "tools/test_source_delete_offline.php" not in payload:
        raise SystemExit("آزمون TENTER در payload نیست.")
    if "tests/test_food_reconciliation_audit_static.py" not in payload or "tools/test_manual_guest_delivery_offline.php" not in payload:
        raise SystemExit("آزمون‌های ممیزی گزارش غذا در payload نیستند.")
    missing = [relative for relative in REQUIRED_INSTALL_FILES if relative not in payload]
    if missing:
        raise SystemExit("فایل‌های ضروری در payload نیستند: " + ", ".join(missing))
    if payload["VERSION"].decode("ascii").strip() != RELEASE_VERSION:
        raise SystemExit("فایل VERSION داخل payload با نسخهٔ manifest برابر نیست.")
    if any(path == "config.php" or path == ".env" or path.startswith(("storage/", "uploads/")) for path in payload):
        raise SystemExit("فایل حساس یا دادهٔ محیط نصب نباید بسته‌بندی شود.")

    report_file_count = len(payload) + 1
    report = report_text(report_file_count).encode("utf-8")
    payload[REPORT_NAME] = report
    files = sorted(payload)
    manifest = {
        "name": "food-panel-r23-render-and-release-note-hotfix",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "۱.۶۳.۲-r23 — رفع اختلال پنل و اعلان تغییرات",
        "requires_version": REQUIRES_VERSION,
        "migrations": [],
        "files": files,
        "sha256": {path: sha256(payload[path]) for path in files},
        "release_status": "INSTALLABLE_OFFLINE_VERIFIED",
        "description": (
            "بستهٔ تجمعی ۱.۶۳.۲-r23 بر پایهٔ r22 است؛ مشکل نمایش متن JavaScript پنل و شناسه/شمارندهٔ اعلان تغییرات را اصلاح می‌کند. "
            "مهاجرت ندارد؛ آزمون زندهٔ پایگاه‌داده و Office در محیط ساخت ادعا نمی‌شود."
        ),
    }
    manifest_bytes = json.dumps(manifest, ensure_ascii=False, indent=2).encode("utf-8")

    output = DOWNLOADS / PACKAGE_NAME
    temporary = output.with_suffix(output.suffix + ".building")
    with zipfile.ZipFile(temporary, "w", compression=zipfile.ZIP_DEFLATED) as archive:
        def add(path: str, data: bytes) -> None:
            info = zipfile.ZipInfo(path, date_time=ZIP_DATE)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o644 << 16
            archive.writestr(info, data)

        add("manifest.json", manifest_bytes)
        for relative in files:
            add("files/" + relative, payload[relative])
    temporary.replace(output)

    package_bytes = output.read_bytes()
    digest = sha256(package_bytes)
    (DOWNLOADS / (PACKAGE_NAME + ".sha256")).write_text(f"{digest}  {PACKAGE_NAME}\n", encoding="ascii")
    (DOWNLOADS / REPORT_NAME).write_bytes(report)

    with zipfile.ZipFile(output) as archive:
        bad = archive.testzip()
        if bad is not None:
            raise SystemExit(f"ZIP نهایی خراب است: {bad}")
        final_manifest = json.loads(archive.read("manifest.json"))
        actual = {name[len("files/"):] for name in archive.namelist() if name.startswith("files/") and not name.endswith("/")}
        if actual != set(files) or final_manifest["files"] != files:
            raise SystemExit("فهرست manifest با payload نهایی برابر نیست.")
        for relative in files:
            data = archive.read("files/" + relative)
            if sha256(data) != final_manifest["sha256"][relative]:
                raise SystemExit(f"هش فایل داخل ZIP نمی‌خواند: {relative}")

    # The publish-release workflow consumes one asset filename per non-comment
    # line; keep this generated manifest machine-readable.
    (DOWNLOADS / "RELEASE-ASSETS-1.63.2.txt").write_text(
        f"{PACKAGE_NAME}\n",
        encoding="ascii",
    )
    (DOWNLOADS / "index.html").write_text(
        render_download_page(len(package_bytes), digest, len(files)), encoding="utf-8"
    )

    print(f"OK {output.relative_to(ROOT)}")
    print(f"Files: {len(files)} | migrations: 0 | bytes: {len(package_bytes):,}")
    print(f"SHA-256: {digest}")


if __name__ == "__main__":
    main()
