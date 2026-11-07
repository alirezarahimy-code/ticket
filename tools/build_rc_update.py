#!/usr/bin/env python3
"""سازندهٔ بستهٔ کاندید ارتقاء مرکز درخواست (۱.۶۲.۹۰ ← ۱.۶۲.۹۲-کاندید).

تفاوت این بسته با بسته‌های prod: این بستهٔ «کاندید» است؛ انتشار آن (تگ، merge به main
و نصب) فقط با تأیید صریح مالک انجام می‌شود. محتوای آن از تفاوت دقیق main → این شاخه
ساخته می‌شود و مهاجرت فقط ۰۳۵ (request_center_appointments) است — سایر مهاجرت‌ها
(۰۳۲–۰۳۴) متعلق به main/Logging هستند و قبلاً روی هدف اعمال شده‌اند.
"""

from __future__ import annotations

import hashlib
import json
import subprocess
import zipfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.63"
REQUIRES_VERSION = "1.62.90"
BASE_COMMIT = "d30d7dfa031173535bd750b4e8a7af35593db244"
RELEASE_DATE = "2026-11-07"
PACKAGE_NAME = f"ticket-update-{REQUIRES_VERSION}-to-{RELEASE_VERSION}-request-center-rc-candidate-{RELEASE_DATE.replace('-', '')}.zip"
REPORT_NAME = f"CHANGE-REPORT-FA-{RELEASE_VERSION}.md"
ZIP_DATE = (2026, 11, 7, 13, 0, 0)

EXCLUDED_PREFIXES = ("downloads/", "updates/", "tests/", "docs/system-map/", ".github/", "ci-diagnostics/", "benchmark-results/")
MIGRATIONS = [
    "035-request-center-appointments.sql",
]
EXCLUDED_FILES = {
    "tools/build_rc_update.py",
    "tools/build_cumulative_update.py",
    "tools/build_update_1_62_88.py",
}


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def git(*args: str) -> str:
    return subprocess.run(
        ["git", *args], cwd=ROOT, check=True, capture_output=True, text=True
    ).stdout


def changed_files() -> list[str]:
    # Fail closed if the exact base is absent.
    git("cat-file", "-e", f"{BASE_COMMIT}^{{commit}}")
    names = git("diff", "--diff-filter=ACMR", "--name-only", BASE_COMMIT, "HEAD").splitlines()
    keep = [n for n in names if n.strip() and not n.startswith(EXCLUDED_PREFIXES)]
    keep = [n for n in keep if n not in EXCLUDED_FILES]
    return sorted(set(keep))


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


def validate_archive(path: Path, expected_manifest: dict) -> str:
    with zipfile.ZipFile(path, "r") as z:
        bad = z.testzip()
        if bad is not None:
            raise SystemExit(f"CRC خراب در ZIP: {bad}")
        infos = z.infolist()
        names = [i.filename for i in infos]
        if len(names) != len(set(n.casefold() for n in names)):
            raise SystemExit("ZIP مسیر تکراری یا تکرار بدون حساسیت به حروف دارد.")
        if any(not safe_archive_path(n) for n in names):
            raise SystemExit("ZIP مسیر مطلق یا traversal دارد.")
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
    return sha256(path.read_bytes())


def build() -> tuple[Path, Path]:
    current = (ROOT / "VERSION").read_text(encoding="utf-8").strip()
    if current != RELEASE_VERSION:
        raise SystemExit(f"VERSION باید {RELEASE_VERSION} باشد، ولی {current!r} است.")

    files: dict[str, bytes] = {}
    for relative in changed_files():
        source = ROOT / relative
        if not source.is_file():
            raise SystemExit(f"فایل اعلام‌شده موجود نیست: {relative}")
        files[relative] = source.read_bytes()

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
    if files.get("VERSION", b"").decode("utf-8", "replace").strip() != RELEASE_VERSION:
        raise SystemExit("files/VERSION با نسخهٔ کاندید نمی‌خواند.")

    manifest = {
        "name": "request-center-rc-candidate",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "کاندید بستهٔ ارتقاء مرکز درخواست — ماژول مستقل + گردش‌کار + نوبت‌ها و تقویم شمسی (۱.۶۲.۹۰ ← ۱.۶۳)",
        "requires_version": REQUIRES_VERSION,
        "migrations": list(MIGRATIONS),
        "files": sorted(files),
        "sha256": {name: sha256(files[name]) for name in sorted(files)},
        "release_status": "RC_CANDIDATE_NOT_FOR_PRODUCTION",
        "architecture_reference": "docs/architecture/REQUEST-CENTER-ARCHITECTURE.md",
        "description": "Request Center module upgrade candidate from 1.62.90 (main) to 1.63. Install only after owner sign-off; requires migration 035 via the official runner. Migration 034 is owned by release 1.62.89 (already installed) and is NOT part of this package.",
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
        checksum = validate_archive(temporary, manifest)
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
