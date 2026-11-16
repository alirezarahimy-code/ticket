#!/usr/bin/env python3
"""Build a cumulative 1.63 -> 1.63.1 r20 install package.

r20 preserves the published r19 payload and refreshes the current asset-identity
and TENTER files. In particular it carries the newly required lib/asset-dedupe.php
plus the server-side SMB/diagnostic/repair files already present in r19.
"""
from __future__ import annotations

import hashlib
import json
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
BASE_PACKAGE = ROOT / "downloads" / "ticket-update-1.63-to-1.63.1-r19-asset-identity-reset-20261116.zip"
PACKAGE_NAME = "ticket-update-1.63-to-1.63.1-r20-assets-tenter-install-20261106.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.63.1-r20-assets-tenter.md"
INSTALL_DOC = "docs/INSTALL-R20-ASSET-TENTER-FA.md"
GUIDE_DOC = "docs/GUIDE-FA-install-1.63.1.md"
RELEASE_VERSION = "1.63.1"
REQUIRES_VERSION = "1.63"
RELEASE_DATE = "2026-11-06"
ZIP_DATE = (2026, 11, 6, 14, 0, 0)

# Existing files whose current source differs from the r19 archive.
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
    "tools/test_source_time_delete_offline.php",
    "docs/ASSET-SCAN-CLOSING-FA.md",
    "docs/NETWORK-INVENTORY-SETUP-FA.md",
]

# These were absent from r19 or are required by the newly refreshed files/docs.
ADD_FILES = [
    "lib/asset-dedupe.php",
    "docs/ASSET-IDENTITY-AND-LIVE-TEST-FA.md",
    "docs/ASSET-NAME-EXACT-FA.md",
    "tests/test_asset_inventory_flow_sqlite.php",
    "tools/test_source_delete_offline.php",
]

REQUIRED_INSTALL_FILES = [
    # Previously missing on the target server: remote collection and diagnostics.
    "tools/collect_server_inventory.ps1",
    "tools/collect_via_smb.ps1",
    "tools/diag_extract.php",
    "tools/RUN-DIAG-EXTRACT.bat",
    "tools/GET-DIAG-EXTRACT.bat",
    # TENTER recovery and identity dedupe runtime dependency.
    "tools/repair_source_rows.php",
    "tools/asset_dedupe.php",
    "lib/asset-dedupe.php",
]


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def report_text() -> str:
    return f"""# گزارش بستهٔ تکمیلی ۱.۶۳.۱-r20 — شناسنامه، TENTER و فایل‌های نصب

- نسخهٔ مقصد: **{RELEASE_VERSION}**
- پیش‌نیاز بسته: **{REQUIRES_VERSION}**
- تاریخ ساخت: **{RELEASE_DATE}**
- مهاجرت دیتابیس: **ندارد**
- پایهٔ بسته: محتوای تجمعی r19 به‌همراه فایل‌های جاری r20

## فایل‌های تکمیلی نصب

بسته فایل‌های ابزار استخراج شبکه را در ریشهٔ برنامه دارد: `tools/collect_server_inventory.ps1`،
`tools/collect_via_smb.ps1`، `tools/diag_extract.php` و فایل‌های اجرای تشخیص. وابستگی
جدید ابزار ادغام امن دارایی نیز اضافه شده است: `lib/asset-dedupe.php` در کنار
`tools/asset_dedupe.php`. ابزار تعمیر ردیف‌های باقی‌ماندهٔ منبع تردد،
`tools/repair_source_rows.php`، هم در بسته قرار دارد.

## اصلاحات محتوایی

- هم‌ترازی مسیرهای ورود موجودی، هویت پایدار، نمایش نام رایانه و استخراج سیستم‌ها با کد جاری.
- شرط حذف زمان در ردیف مصرف‌شدهٔ TENTER به بازهٔ نیم‌باز یک‌ثانیه‌ای تغییر کرده تا کسر ثانیهٔ Access باعث ماندن رکورد نشود؛ مسیر fallback نیز همین منطق را دارد.
- آزمون مرزی نیمه‌شب و رد امن تاریخ نامعتبر اضافه شده است.
- پاک‌سازی واقعی TENTER یا ادغام شناسنامه‌ها خودکار اجرا نمی‌شود.

## آزمون‌های ثبت‌شده

- `tools/test_source_delete_offline.php`: **۱۴ موفق**
- `tools/test_source_time_delete_offline.php`: **۲۶ موفق**
- آزمون‌های کامل Python: **۷۹۱ موفق، ۱۶ ردشده، ۲۵ زیرآزمون موفق**
- PHP lint و بررسی ZIP/manifest: موفق

آزمون‌ها آفلاین/ایستا هستند. این بسته، اتصال زنده به Access/TENTER یا SMB/WinRM شبکهٔ مقصد را ادعا نمی‌کند. پیش از هر پاک‌سازی واقعی، گزارش ابزار را بازبینی و نسخهٔ پشتیبان تهیه کنید.

## نصب

از راهنمای داخل بسته در `{INSTALL_DOC}` پیروی کنید. محتوای `files/` را روی ریشهٔ نصب کپی کنید؛ خود پوشهٔ `files/` را کپی نکنید.
"""


def load_base_payload() -> dict[str, bytes]:
    if not BASE_PACKAGE.is_file():
        raise SystemExit(f"بستهٔ مبنا پیدا نشد: {BASE_PACKAGE}")
    with zipfile.ZipFile(BASE_PACKAGE) as archive:
        bad = archive.testzip()
        if bad is not None:
            raise SystemExit(f"بستهٔ مبنا خراب است: {bad}")
        manifest = json.loads(archive.read("manifest.json"))
        if manifest.get("version") != RELEASE_VERSION:
            raise SystemExit("نسخهٔ بستهٔ مبنا با نسخهٔ r20 یکی نیست.")
        if manifest.get("requires_version") != REQUIRES_VERSION:
            raise SystemExit("پیش‌نیاز بستهٔ مبنا با پیش‌نیاز r20 یکی نیست.")
        payload = {
            name[len("files/"):]: archive.read(name)
            for name in archive.namelist()
            if name.startswith("files/") and not name.endswith("/")
        }
    # Do not carry forward the old release report as if it described r20.
    for old_report in list(payload):
        if old_report.startswith("CHANGE-REPORT-FA-1.63.1-"):
            del payload[old_report]
    return payload


def main() -> None:
    payload = load_base_payload()
    for relative in REFRESH_FILES + ADD_FILES:
        source = ROOT / relative
        if not source.is_file():
            raise SystemExit(f"فایل لازم در درخت کاری نیست: {relative}")
        payload[relative] = source.read_bytes()

    report = report_text().encode("utf-8")
    install_guide = (ROOT / INSTALL_DOC).read_bytes()
    payload[REPORT_NAME] = report
    payload[INSTALL_DOC] = install_guide
    # Replace the old r19 guide embedded in the cumulative archive with the
    # r20 install steps; keep the repository's historical guide untouched.
    payload[GUIDE_DOC] = install_guide
    payload["VERSION"] = (RELEASE_VERSION + "\n").encode("ascii")

    missing = [relative for relative in REQUIRED_INSTALL_FILES if relative not in payload]
    if missing:
        raise SystemExit("فایل‌های ضروری نصب در payload نیستند: " + ", ".join(missing))
    if "lib/asset-dedupe.php" not in payload["tools/asset_dedupe.php"].decode("utf-8", "replace"):
        raise SystemExit("tools/asset_dedupe.php وابستگی lib/asset-dedupe.php را ندارد.")
    if "food-ticket.php" not in payload or "tools/test_source_delete_offline.php" not in payload:
        raise SystemExit("اصلاح یا آزمون TENTER در payload نیست.")
    if any(path == "config.php" or path == ".env" or path.startswith(("storage/", "uploads/")) for path in payload):
        raise SystemExit("فایل حساس یا دادهٔ محیط نصب نباید بسته‌بندی شود.")

    files = sorted(payload)
    manifest = {
        "name": "asset-tenter-install-r20",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "۱.۶۳.۱-r20 — شناسنامه، پاک‌سازی TENTER و فایل‌های تکمیلی نصب",
        "requires_version": REQUIRES_VERSION,
        "migrations": [],
        "files": files,
        "sha256": {path: sha256(payload[path]) for path in files},
        "release_status": "INSTALLABLE_OFFLINE_VERIFIED",
        "description": (
            "بستهٔ تجمعی بر پایهٔ r19 با فایل‌های تکمیلی استخراج شبکه، "
            "وابستگی dedupe شناسنامه، و اصلاح/آزمون حذف ردیف‌های TENTER. "
            "اتصال زندهٔ شبکه یا Access در این محیط آزموده نشده است."
        ),
    }
    manifest_bytes = json.dumps(manifest, ensure_ascii=False, indent=2).encode("utf-8")

    out_dir = ROOT / "downloads"
    out_dir.mkdir(exist_ok=True)
    output = out_dir / PACKAGE_NAME
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
    checksum = sha256(output.read_bytes())
    (out_dir / (PACKAGE_NAME + ".sha256")).write_text(
        f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii"
    )
    (out_dir / REPORT_NAME).write_bytes(report)

    # Verify the final archive and all manifest content hashes before reporting success.
    with zipfile.ZipFile(output) as archive:
        bad = archive.testzip()
        if bad is not None:
            raise SystemExit(f"ZIP نهایی خراب است: {bad}")
        final_manifest = json.loads(archive.read("manifest.json"))
        if final_manifest["files"] != files:
            raise SystemExit("فهرست manifest با payload نهایی برابر نیست.")
        for relative in files:
            actual = archive.read("files/" + relative)
            if sha256(actual) != final_manifest["sha256"][relative]:
                raise SystemExit(f"هش فایل داخل ZIP نمی‌خواند: {relative}")
    print(f"OK {output.relative_to(ROOT)}")
    print(f"Files: {len(files)} | migrations: 0 | bytes: {output.stat().st_size}")
    print(f"SHA-256: {checksum}")


if __name__ == "__main__":
    main()
