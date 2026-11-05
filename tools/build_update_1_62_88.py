#!/usr/bin/env python3
"""سازندهٔ بستهٔ تجمیعی ۱.۶۲.۸۸ — از پایهٔ دقیق ۱.۶۲.۸۲.

این ابزار بسته را به‌صورت deterministic می‌سازد، محتوای ZIP را پس از ساخت
از نظر CRC، مسیرها، manifest، فهرست مهاجرت‌ها و SHA-256 تک‌تک فایل‌ها
اعتبارسنجی می‌کند و checksum آرشیو را فقط پس از بستن ZIP تولید می‌کند.
"""
from __future__ import annotations

import hashlib
import json
import subprocess
import zipfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.62.88"
REQUIRES_VERSION = "1.62.82"
BASE_COMMIT = "b5711e79d5f5dc6d73f3a248a80c7a51d609c1c5"
RELEASE_DATE = "2026-11-05"
PACKAGE_NAME = f"ticket-update-1.62.82-to-{RELEASE_VERSION}-merged-20261105.zip"
REPORT_NAME = f"CHANGE-REPORT-FA-{RELEASE_VERSION}.md"
ZIP_DATE = (2026, 11, 5, 12, 0, 0)

EXCLUDED_PREFIXES = ("downloads/", "updates/", "tests/", "docs/system-map/", ".github/")
MIGRATIONS = [
    "024-central-logging.sql",
    "025-logging-issues-alerts.sql",
    "026-logging-legacy-result-unknown.sql",
    "027-logging-correlation-ids.sql",
    "028-logging-fingerprint-v2.sql",
    "029-activity-result-default-unknown.sql",
    "030-logging-notification-idempotency-and-ticket-history.sql",
    "031-food-worker-window.sql",
    "033-logging-legacy-context-collation.sql",
]
EXCLUDED_MIGRATIONS = {"032-schema-updates-baseline.sql"}
EXCLUDED_FILES = {
    "tools/Run-Logging-Acceptance-1.62.83.ps1",
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
    # Fail closed if the exact release base is absent.
    git("cat-file", "-e", f"{BASE_COMMIT}^{{commit}}")
    names = git("diff", "--diff-filter=ACMR", "--name-only", BASE_COMMIT, "HEAD").splitlines()
    keep = [n for n in names if n.strip() and not n.startswith(EXCLUDED_PREFIXES)]
    keep = [n for n in keep if Path(n).name not in EXCLUDED_MIGRATIONS]
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
        if "032-schema-updates-baseline.sql" in migration_entries:
            raise SystemExit("مهاجرت ۰۳۲ نباید در بسته باشد.")
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
        raise SystemExit("files/VERSION با نسخهٔ انتشار نمی‌خواند.")

    manifest = {
        "name": f"release-{RELEASE_VERSION.replace('.', '-')}-merged-hardening",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "بستهٔ تجمیعی اصلاحات امنیتی، Logging و ماژول‌های سامانه",
        "requires_version": REQUIRES_VERSION,
        "migrations": list(MIGRATIONS),
        "files": sorted(files),
        "sha256": {name: sha256(files[name]) for name in sorted(files)},
        "description": "Cumulative update from 1.62.82 to 1.62.88",
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
        # Hash the final path, not an in-memory pre-write representation.
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
