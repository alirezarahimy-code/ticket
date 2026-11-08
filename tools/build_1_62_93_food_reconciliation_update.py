#!/usr/bin/env python3
"""سازندهٔ بستهٔ میانی ۱.۶۲.۹۳ — تعیین تکلیف غذای باقیمانده (۱.۶۲.۹۰ ← ۱.۶۲.۹۳).

این بستهٔ میانی فقط اصلاحات ماژول غذای این شاخه را شامل می‌شود؛

- «مرکز درخواست‌ها» (RC، کاندید ۱.۶۳/۱.۶۲.۹۲) هیچ اثری در این بسته ندارد؛
- مهاجرت فقط ۰۳۶-food-meal-leftover-disposition.sql است؛
- فایل VERSION داخل ZIP به مقدار «1.62.93» تزریق می‌شود (VERSION ریشهٔ مخزن دست‌نخورده و متعلق به RC می‌ماند)؛
- انتشار (تگ/merge به main) فقط با دستور صریح مالک انجام می‌شود.
"""

from __future__ import annotations

import hashlib
import json
import zipfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.62.93"
REQUIRES_VERSION = "1.62.90"
RELEASE_DATE = "2026-11-06"
PACKAGE_NAME = f"ticket-update-{REQUIRES_VERSION}-to-{RELEASE_VERSION}-food-reconciliation-leftover-{RELEASE_DATE.replace('-', '')}.zip"
REPORT_NAME = f"CHANGE-REPORT-FA-{RELEASE_VERSION}.md"
ZIP_DATE = (2026, 11, 6, 0, 0, 0)

# فقط فایل‌های تولیدی این اصلاح؛ هیچ فایلی از «مرکز درخواست‌ها» نباید وارد شود.
WHITELIST = [
    "food-reconciliation.php",
    "assets/food-menu.js",
    "food-ticket-web/assets/food-menu.js",
    "assets/food-menu.css",
    "food-ticket-web/assets/food-menu.css",
    "assets/food-order.css",
    "food-ticket-web/assets/food-order.css",
    "schema.sql",
    "install-empty-db.sql",
]
MIGRATIONS = [
    "036-food-meal-leftover-disposition.sql",
]


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
        if any("request" in n.lower() for n in names):
            raise SystemExit("خطای حاکم: فایل مرتبط با «مرکز درخواست‌ها» در بستهٔ غذا پیدا شد.")
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
        if b"request" in relative.encode():
            raise SystemExit(f"مسیر نامعتبر در بستهٔ غذا: {relative}")
        files[relative] = data

    migrations: dict[str, bytes] = {}
    for name in MIGRATIONS:
        source = ROOT / "migrations" / name
        if not source.is_file():
            raise SystemExit(f"مهاجرت موجود نیست: {name}")
        data = source.read_bytes()
        migrations[name] = data
        files["migrations/" + name] = data

    report = ROOT / "downloads" / REPORT_NAME
    if not report.is_file():
        raise SystemExit(f"گزارش تغییرات موجود نیست: {report}")
    files[REPORT_NAME] = report.read_bytes()
    files["VERSION"] = f"{RELEASE_VERSION}\n".encode("utf-8")

    manifest = {
        "name": "food-reconciliation-leftover-disposition",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "تعیین تکلیف غذای باقیمانده و اصلاحات «تطبیق روزانه» غذا (۱.۶۲.۹۰ ← ۱.۶۲.۹۳)",
        "requires_version": REQUIRES_VERSION,
        "migrations": list(MIGRATIONS),
        "files": sorted(files),
        "sha256": {name: sha256(files[name]) for name in sorted(files)},
        "release_status": "INTERIM_UPDATE_TEST_ON_STAGING_FIRST",
        "description": (
            "Interim food-module update for the daily food reconciliation report: leftover disposition popup at day close, "
            "new automatic columns (remaining = received - served, dynamic کسر/اضافهٔ سفارش label, غایبین), monthly invoice untouched. "
            "Migration 036 is additive and idempotent. Contains NO Request Center content and does NOT ship migration 035. "
            "Install on staging first; production only with owner sign-off."
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
            for name in MIGRATIONS:
                z.writestr(entry("migrations/" + name), migrations[name])
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
