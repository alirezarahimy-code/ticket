#!/usr/bin/env python3
"""Build the cumulative 1.63 -> 1.63.2 r24 installable update package.

Starts from the verified r23 cumulative package, preserves its fixes, moves the release/version banners into the global panel render, and rebuilds the installable archive.
No database migrations are added.
"""
from __future__ import annotations

import hashlib
import json
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DOWNLOADS = ROOT / "downloads"
BASE_PACKAGE = DOWNLOADS / "ticket-update-1.63-to-1.63.2-r23-panel-render-notice-fix-20261106.zip"
PACKAGE_NAME = "ticket-update-1.63-to-1.63.2-r24-global-notice-fix-20261106.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.63.2-r24-global-notice-fix.md"
GUIDE_NAME = "docs/GUIDE-FA-install-1.63.2.md"
RELEASE_VERSION = "1.63.2"
REQUIRES_VERSION = "1.63"
RELEASE_DATE = "2026-11-06"
ZIP_DATE = (2026, 11, 6, 16, 0, 0)

# Files whose current source must travel with the cumulative r23 payload.
# r24 refreshes global release-notice placement, panel version, and the revision-aware release note payload.
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
    return f"""# گزارش بستهٔ به‌روزرسانی ۱.۶۳.۲-r24 — نمایش سراسری اعلان تغییرات

- نسخهٔ مقصد: **{RELEASE_VERSION}**
- پیش‌نیاز: **{REQUIRES_VERSION} به بعد** (روی ۱.۶۳، ۱.۶۳.۱ و نصب r23 قابل نصب است)
- تاریخ ساخت: **{RELEASE_DATE}**
- فایل‌های نصب: **{file_count}**
- مهاجرت دیتابیس: **ندارد**
- پایه: بستهٔ تجمعی r23؛ اصلاحات پیشین در بسته حفظ شده‌اند.

## ایراد تأییدشده و اصلاح

بازبینی مستقیم کد r23 نشان داد محتوای بنر تغییرات و هشدار نسخه فقط در بدنهٔ `settingsForm()` ساخته می‌شد؛ داشبورد و نماهای دیگر از این تابع عبور نمی‌کنند و بنابراین اعلان را دریافت نمی‌کردند. r24 آن دو بنر را در `panelViewHtml()` مشترک قرار می‌دهد و `render()` محتوای همهٔ نماها را از آن عبور می‌دهد. بنر از داخل محتوای تنظیمات حذف شد تا در آن نما تکرار نشود.

این اصلاح دربارهٔ **نمایش اعلان** است؛ به‌تنهایی علت باقی‌ماندن پیام «در حال آماده‌سازی محیط مدیریت...» را ثابت یا رفع نمی‌کند. آن رفتار به اجرای واقعی PHP/مرورگر و بررسی خطاهای Console/Network روی سرور نیاز دارد.

## اعلان و نسخهٔ پنل

- شناسهٔ اعلان r24 مستقل (`1.63.2-r24`) است؛ دیده/بسته‌شدن اعلان‌های پیشین آن را پنهان نمی‌کند.
- شمارندهٔ نمایش اعلان برای هر بازدید صفحه یک‌بار ثبت می‌شود، نه به‌ازای هر رندر.
- نسخهٔ رابط و PHP سرور روی **1.37.57** تنظیم شده‌اند تا نسخهٔ کش‌شدهٔ قدیمی قابل تشخیص باشد.
- اصلاح چاپ گزارش غایبین r23 در بسته حفظ شده است؛ چاپ در این محیط مرورگر/چاپگر واقعی ندارد.

## آزمون‌های انجام‌شده و محدودیت

- `node tests/test_selftest_panel_render.js`: **۵۷ بررسی موفق، ۰ ناموفق**؛ از جمله تزریق بنر پیش از محتوای داشبورد، استفادهٔ `render()` از wrapper سراسری، و نبود بنر تکراری در تنظیمات. این آزمون توابع واقعی را با VM اجرا می‌کند، نه DOM/مرورگر کامل.
- `node tests/js/food-absent-report.test.js`: PASS؛ تابع `test_standalone_absent_report_panel_and_chatbot_contract` از `tests/test_food_absent_report_static.py` نیز با `runpy` اجرا و PASS شد.
- `node --check` روی هر سه اسکریپت inline استخراج‌شده از `food-ticket-web/index.html`: موفق؛ این بررسی فقط syntax است، نه اجرای مرورگر.
- سازندهٔ بسته ZIP را باز می‌کند، `testzip()`، فهرست manifest، نسخهٔ پایه، و SHA-256 تک‌تک فایل‌های داخل را بررسی می‌کند. تست ساختار r23 نیز به‌صورت historical در کنار تست r24 اجرا می‌شود.
- PHP CLI و pytest در محیط ساخت موجود نیستند؛ PHP/DB، نصب روی سرور مقصد، Console/Network مرورگر و دستگاه چاپ زنده آزموده نشده‌اند.

پیش از نصب از فایل‌ها و پایگاه‌داده پشتیبان بگیرید. پس از نصب، پنل را با Ctrl+F5 تازه کنید و نسخهٔ رابط را با نسخهٔ پاسخ `/api/config` مقایسه کنید.
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
<p>این بسته بر پایهٔ r23 است. علت مشخصِ نمایش‌ندادن اعلان در داشبورد را اصلاح می‌کند: اعلان قبلاً فقط در نمای تنظیمات ساخته می‌شد. r24 آن را برای همهٔ نماها نمایش می‌دهد، شناسهٔ مستقل دارد و نسخهٔ پنل را به 1.37.57 می‌رساند. اصلاح چاپ r23 نیز حفظ شده است؛ بسته migration دیتابیس ندارد.</p>

<div class="ok"><b>پیش‌نیاز:</b> نسخهٔ <code>1.63</code> یا جدیدتر (از جمله <code>1.63.1</code>). بسته {file_count} فایل نصب دارد و هیچ مهاجرت دیتابیسی اجرا نمی‌کند.</div>

<section class="package"><h2>به‌روزرسانی نصب موجود — ۱.۶۳.۲-r24</h2>
<p>پس از پشتیبان‌گیری، ZIP را در بخش «به‌روزرسانی و پشتیبان‌گیری» پنل مدیر اصلی بارگذاری کنید. راهنمای نصب و گزارش تغییرات نیز در دسترس است.</p>
<div class="actions">
<a class="button primary" href="{PACKAGE_NAME}" download>دانلود بستهٔ ۱.۶۳.۲ (ZIP)</a>
<a class="button secondary" href="https://github.com/alirezarahimy-code/ticket/releases/download/v1.63.2-r24/{PACKAGE_NAME}">دانلود از Release رسمی GitHub</a>
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
        raise SystemExit("VERSION مخزن با نسخهٔ مقصد r24 برابر نیست.")

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
                or relative in {"CHANGE-REPORT-FA-1.63.2-r22-reconciliation-audit.md", "CHANGE-REPORT-FA-1.63.2-r23-panel-render-notice-fix.md"}):
            del payload[relative]
            continue

    for relative in REFRESH_FILES + ADD_OR_REPLACE_FILES:
        payload[relative] = read_source(relative)

    if "1.63.2" not in payload["lib/release-notes.php"].decode("utf-8", "replace"):
        raise SystemExit("یادداشت انتشار ۱.۶۳.۲ در lib/release-notes.php نیست.")
    panel_text = payload["food-ticket-web/index.html"].decode("utf-8", "replace")
    server_text = payload["food-ticket.php"].decode("utf-8", "replace")
    release_text = payload["food-ticket-release-notes.php"].decode("utf-8", "replace")
    if "PANEL_VERSION='1.37.57'" not in panel_text or "FOOD_TICKET_PANEL_VERSION = '1.37.57'" not in server_text:
        raise SystemExit("نسخهٔ پنل r24 در HTML و PHP هم‌خوان نیست.")
    if "'notification_id' => '1.63.2-r24'" not in release_text or "n.notification_id||n.version" not in panel_text:
        raise SystemExit("شناسهٔ یکتای اعلان r24 در payload نیست.")
    if "'title' => 'اصلاح چاپ گزارش غایبین و اعلان تغییرات'" not in release_text:
        raise SystemExit("یادداشت تاریخی r23 هنوز اصلاح پنلِ تأییدنشده را ادعا می‌کند.")
    if "function panelViewHtml(content){return releaseNotesBanner()+versionBanner()+content}" not in panel_text:
        raise SystemExit("wrapper سراسری اعلان r24 در payload نیست.")
    if "screen.innerHTML=panelViewHtml((pages[view]||dashboardView)());" not in panel_text:
        raise SystemExit("render پنل از wrapper سراسری اعلان عبور نمی‌کند.")
    if "let content=pages[active]?pages[active]():'';" not in panel_text or "let content=releaseNotesBanner()+versionBanner()" in panel_text:
        raise SystemExit("تنظیمات هنوز بنر را جداگانه یا تکراری می‌سازد.")
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
        raise SystemExit("اصلاحات ممیزی گزارش تطبیق قبلی در payload حفظ نشده‌اند.")
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
        "name": "food-panel-r24-global-release-notice-fix",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "۱.۶۳.۲-r24 — نمایش سراسری اعلان تغییرات",
        "requires_version": REQUIRES_VERSION,
        "migrations": [],
        "files": files,
        "sha256": {path: sha256(payload[path]) for path in files},
        "release_status": "INSTALLABLE_OFFLINE_VERIFIED",
        "description": (
            "بستهٔ تجمعی ۱.۶۳.۲-r24 بر پایهٔ r23 است؛ اعلان تغییرات را در همهٔ نماهای پنل نشان می‌دهد و اصلاحات r23 را حفظ می‌کند. "
            "مهاجرت ندارد؛ آزمون زندهٔ PHP، پایگاه‌داده و مرورگر روی سرور مقصد در محیط ساخت ادعا نمی‌شود."
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
