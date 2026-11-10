#!/usr/bin/env python3
"""سازندهٔ بستهٔ میانی ۱.۶۲.۹۴ — مرج «پرس سفارش/تحویل واقعی پیمانکار»، ستون‌های جدای مهمان در
«تطبیق روزانه»، کارت سوم «واحد مستقل» کنار مدیرعامل، اصلاح گروه‌های دسترسی (org.manage) و
آزادسازی ثبت سفارش در تعطیلات رسمی (۱.۶۲.۹۳ ← ۱.۶۲.۹۴).

- این بسته فقط اصلاحات همین دور (پنج محور تأییدی مالک) را دارد؛
- «مرکز درخواست‌ها» / RC / مهاجرت ۰۳۵ در این بسته نیست و مهاجرت تازه‌ای هم ندارد؛
- فایل VERSION داخل ZIP به مقدار «1.62.94» تزریق می‌شود (VERSION ریشهٔ مخزن دست‌نخورده و متعلق به RC می‌ماند)؛
- انتشار (تگ/merge به main) فقط با دستور صریح مالک انجام می‌شود.
"""

from __future__ import annotations

import hashlib
import json
import zipfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.62.94"
REQUIRES_VERSION = "1.62.93"
RELEASE_DATE = "2026-11-10"
PACKAGE_NAME = "ticket-update-1.62.93-to-1.62.94-food-guest-org-holiday-20261110.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.62.94.md"
ZIP_DATE = (2026, 11, 10, 0, 0, 0)

# فقط فایل‌های تولیدی این دور؛ هیچ فایلی از «مرکز درخواست‌ها» نباید وارد شود.
WHITELIST = [
    "food-reconciliation.php",
    "food-order.php",
    "assets/food-menu.js",
    "food-ticket-web/assets/food-menu.js",
    "assets/food-order.js",
    "food-ticket-web/index.html",
    "organization.php",
    "actions/org-actions.php",
    "assets/organization.js",
    "assets/style.css",
    "index.php",
    "lib/render.php",
]
MIGRATIONS: list[str] = []


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def safe_archive_path(name: str) -> bool:
    p = PurePosixPath(name)
    win = PureWindowsPath(name)
    return (
        bool(name)
        and not p.is_absolute()
        and not win.is_absolute()
        and not win.drive
        and "\\" not in name
        and "//" not in name
        and not name.endswith("/")
        and all(part not in ("", ".", "..") for part in p.parts)
    )


def validate_archive(path: Path, expected_manifest: dict) -> None:
    with zipfile.ZipFile(path, "r") as z:
        bad = z.testzip()
        if bad is not None:
            raise SystemExit(f"CRC خراب در ZIP: {bad}")
        names = z.infolist() and [i.filename for i in z.infolist()]
        if len(names) != len(set(n.casefold() for n in names)):
            raise SystemExit("ZIP مسیر تکراری یا تکرار بدون حساسیت به حروف دارد.")
        if any(not safe_archive_path(n) for n in names):
            raise SystemExit("ZIP مسیر مطلق یا traversal دارد.")
        lowered_names = [n.lower() for n in names]
        for token in ("request-center", "request_center", "requestcenter", "035-request", "rc-candidate"):
            if any(token in n for n in lowered_names):
                raise SystemExit(f"خطای حاکم: فایل مرتبط با «مرکز درخواست‌ها» در بسته پیدا شد: {token}")
        if "manifest.json" not in names:
            raise SystemExit("manifest.json در ZIP وجود ندارد.")
        got = json.loads(z.read("manifest.json"))
        if got != expected_manifest:
            raise SystemExit("manifest داخل ZIP با manifest ساخته‌شده یکسان نیست.")
        file_entries = {n[len("files/"):] for n in names if n.startswith("files/")}
        if file_entries != set(got["files"]):
            raise SystemExit("فهرست فایل‌های واقعی ZIP با manifest.files یکسان نیست.")
        migration_entries = {n[len("migrations/"):] for n in names if n.startswith("migrations/")}
        if migration_entries != set(got["migrations"]):
            raise SystemExit("فهرست مهاجرت‌های ZIP با manifest.migrations یکسان نیست.")
        hashes = got.get("sha256")
        if not isinstance(hashes, dict) or set(hashes) != set(got["files"]):
            raise SystemExit("کلیدهای manifest.sha256 باید دقیقاً با manifest.files برابر باشند.")
        for relative, expected in hashes.items():
            if sha256(z.read("files/" + relative)) != expected:
                raise SystemExit(f"عدم تطابق SHA-256 فایل: {relative}")
        if got.get("version") != RELEASE_VERSION or got.get("requires_version") != REQUIRES_VERSION:
            raise SystemExit("قرارداد نسخهٔ manifest نامعتبر است.")


def build() -> tuple[Path, Path]:
    files: dict[str, bytes] = {}
    for relative in WHITELIST:
        source = ROOT / relative
        if not source.is_file():
            raise SystemExit(f"فایل اعلام‌شده موجود نیست: {relative}")
        data = source.read_bytes()
        files[relative] = data

    migrations: dict[str, bytes] = {}

    report = ROOT / "downloads" / REPORT_NAME
    if not report.is_file():
        raise SystemExit(f"گزارش تغییرات موجود نیست: {report}")
    files[REPORT_NAME] = report.read_bytes()
    files["VERSION"] = f"{RELEASE_VERSION}\n".encode("utf-8")

    manifest = {
        "name": "food-guest-org-groups-holiday",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "مرج «پرس سفارش/تحویل واقعی پیمانکار»، ستون‌های جدای مهمان در «تطبیق روزانه»، کارت سوم «واحد مستقل» کنار مدیرعامل، اصلاح گروه‌های دسترسی و تعطیل رسمی (۱.۶۲.۹۳ ← ۱.۶۲.۹۴)",
        "requires_version": REQUIRES_VERSION,
        "migrations": list(MIGRATIONS),
        "files": sorted(files),
        "sha256": {name: sha256(files[name]) for name in sorted(files)},
        "release_status": "INTERIM_UPDATE_TEST_ON_STAGING_FIRST",
        "description": (
            "Interim 1.62.x update: (1) merge of supplier order/issued portion into a single portion_quantity field "
            "(legacy two-field payloads still accepted; received_quantity is written only at day close); "
            "(2) guest columns split out in the daily food reconciliation table and Excel export "
            "(new total_employee_orders column; removed mixed demand column and cancelled-guest column; 22 export columns); "
            "(3) third independent (liaison) unit slot rendered beside the CEO (chart_side='center'), no other layout changes; "
            "(4) access-group grant of org.manage now actually enables org-tree editing (user_can_manage_organization is permission-driven); "
            "(5) official holidays no longer block employee food ordering (label only, owner decision). "
            "Contains NO Request Center content and NO new migrations. Install on staging first; production only with owner sign-off."
        ),
    }

    out_dir = ROOT / "downloads"
    out_dir.mkdir(parents=True, exist_ok=True)
    package_path = out_dir / PACKAGE_NAME
    temporary = package_path.with_suffix(package_path.suffix + ".tmp")

    def entry(name: str) -> zipfile.ZipInfo:
        info = zipfile.ZipInfo(name, date_time=ZIP_DATE)
        info.compress_type = zipfile.ZIP_DEFLATED
        info.external_attr = 0o100644 << 16
        return info

    try:
        with zipfile.ZipFile(temporary, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as z:
            z.writestr(entry("manifest.json"), (json.dumps(manifest, ensure_ascii=False, indent=2) + "\n").encode("utf-8"))
            for name in sorted(files):
                z.writestr(entry("files/" + name), files[name])
        validate_archive(temporary, manifest)
        temporary.replace(package_path)
        checksum = sha256(package_path.read_bytes())
        sidecar = out_dir / (PACKAGE_NAME + ".sha256")
        sidecar_tmp = sidecar.with_suffix(sidecar.suffix + ".tmp")
        sidecar_tmp.write_text(f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii")
        sidecar_tmp.replace(sidecar)
        recorded = sidecar.read_text(encoding="ascii").split()[0]
        if recorded != sha256(package_path.read_bytes()):
            raise SystemExit("SHA-256 فایل جانبی با ZIP نهایی تطبیق ندارد.")
        print(f"OK {PACKAGE_NAME}")
        print(f"Files: {len(files)} | migrations: {len(MIGRATIONS)} | bytes: {package_path.stat().st_size}")
        print(f"SHA-256: {checksum}")
        return package_path, sidecar
    finally:
        if temporary.exists():
            temporary.unlink()


if __name__ == "__main__":
    build()
