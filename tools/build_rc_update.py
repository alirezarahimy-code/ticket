#!/usr/bin/env python3
"""سازندهٔ بستهٔ کاندید ارتقاء مرکز درخواست (۱.۶۲.۹۰ ← ۱.۶۳-کاندید).

پیش‌نیاز هدف: بسته‌های غذا تا ۱٫۶۲٫۹۴ (با مهاجرت ۰۳۶) پیشاپیش اعمال شده باشند؛
فایل‌های قلمرو ۱٫۶۲٫۹۴ و پیش‌نمایش انتشار غذا عمداً از این بسته حذف می‌شوند.

تفاوت این بسته با بسته‌های prod: این بستهٔ «کاندید» است؛ انتشار آن (تگ، merge به main
و نصب) فقط با تأیید صریح مالک انجام می‌شود. محتوای آن از تفاوت دقیق main → این شاخه
ساخته می‌شود و مهاجرت‌های RC شامل ۰۳۵، ۰۳۷ و ۰۳۸ هستند؛ ۰۳۸ دلتاهای lifecycle را برای نصب‌هایی که ۰۳۵ قدیمی را ثبت کرده‌اند تکمیل می‌کند — سایر مهاجرت‌ها
(۰۳۲–۰۳۴) متعلق به main/Logging هستند و قبلاً روی هدف اعمال شده‌اند.

تصمیم مالک (۲۰۲۶-۱۱-۰۹): این بسته **فقط به‌روزرسانی مرکز درخواست** را حمل می‌کند.
هدف نصب، پیش‌تر تمام بسته‌های غذایی موجود را (تا ۱.۶۲.۹۳، با مهاجرت ۰۳۶) اعمال کرده
است؛ بنابراین فایل‌های موج غذا و مهاجرت ۰۳۶ عمداً از این بسته حذف می‌شوند.
"""

from __future__ import annotations

import hashlib
import json
import subprocess
import zipfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.63"
REQUIRES_VERSION = "1.62.94"
BASE_COMMIT = "d30d7dfa031173535bd750b4e8a7af35593db244"
RELEASE_DATE = "2026-11-11"
PACKAGE_NAME = f"ticket-update-{REQUIRES_VERSION}-to-{RELEASE_VERSION}-request-center-rc-candidate-{RELEASE_DATE.replace('-', '')}.zip"
REPORT_NAME = f"CHANGE-REPORT-FA-{RELEASE_VERSION}.md"
ZIP_DATE = (2026, 11, 11, 12, 0, 0)

EXCLUDED_PREFIXES = ("downloads/", "updates/", "tests/", "docs/system-map/", ".github/", "ci-diagnostics/", "benchmark-results/", "preview/")
MIGRATIONS = [
    "035-request-center-appointments.sql",
    "038-org-role-sync-columns.sql",
    "039-request-center-lifecycle-catchup.sql",
]
EXCLUDED_FILES = {
    "tools/build_rc_update.py",
    "tools/build_cumulative_update.py",
    "tools/build_update_1_62_88.py",
    # ابزار کمکی محلیِ بررسی نحوی (برای محیط توسعه؛ بخشی از محصول نیست)
    "tools/php_syntax_check.py",
}

# ۲۰۲۶-۱۱-۰۹ — تصمیم مالک: بسته فقط RC است. هدف نصب، بسته‌های غذای تحویل‌شده
# (تا ۱.۶۲.۹۳) را پیشاپیش اعمال کرده است؛ این فایل‌ها متعلق به بستهٔ غذا هستند.
EXCLUDED_FOOD_PATHS = {
    # قلمرو ۱٫۶۲٫۹۴ (بستهٔ میانی غذا/سازمان) که شاخه پس از کاندید ۲۰۲۶‑۱۱‑۰۹ وارد شد؛
    # هدف نصب، بستهٔ ۱٫۶۲٫۹۴ را جداگانه اعمال می‌کند؛ این فایل‌ها نباید از مسیر RC بیایند.
    "actions/org-actions.php",
    "assets/organization.js",
    "assets/style.css",
    "organization.php",
    "tools/build_1_62_94_update.py",
    "actions/food-order-actions.php",
    "assets/food-menu.css",
    "assets/food-menu.js",
    "assets/food-order.css",
    "assets/food-order.js",
    "food-order.php",
    "food-reconciliation.php",
    "food-ticket-groups.php",
    "food-ticket-runtime.php",
    "food-ticket-templates.php",
    "food-ticket.php",
    "migrations/036-food-meal-leftover-disposition.sql",
    "migrations/037-food-auto-receipts-delivered.sql",
    "tools/build_1_62_93_food_reconciliation_update.py",
    # ورودیِ ignoreِ یک ابزار محلیِ نقشهٔ پیشرفت؛ نه قابلیت RC و نه تغییر رفتار سامانه.
    ".gitignore",
}
EXCLUDED_FOOD_PREFIXES = ("food-ticket-web/",)


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
    keep = [n for n in keep if n not in EXCLUDED_FOOD_PATHS and not n.startswith(EXCLUDED_FOOD_PREFIXES)]
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

    unlisted_migrations = sorted(
        name for name in files
        if name.startswith("migrations/") and name.endswith(".sql")
        and Path(name).name not in MIGRATIONS
    )
    if unlisted_migrations:
        raise SystemExit("RC package contains SQL migrations absent from manifest.migrations: " + ", ".join(unlisted_migrations))

    manifest = {
        "name": "request-center-rc-candidate",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "کاندید بستهٔ ارتقاء مرکز درخواست — ماژول مستقل + گردش‌کار + نوبت‌ها و تقویم شمسی (پیش‌نیاز ۱.۶۲.۹۴ ← ۱.۶۳)",
        "requires_version": REQUIRES_VERSION,
        "migrations": list(MIGRATIONS),
        "files": sorted(files),
        "sha256": {name: sha256(files[name]) for name in sorted(files)},
        "release_status": "RC_CANDIDATE_NOT_FOR_PRODUCTION",
        "architecture_reference": "docs/architecture/REQUEST-CENTER-ARCHITECTURE.md",
        "description": "Request Center RC candidate sourced from main commit d30d7dfa (1.62.90) to 1.63. Minimum installed version is 1.62.94 because food 1.62.94 and migration 036 are intentionally excluded and must already be installed. Not compatible with the known production version 1.62.77 or with 1.62.90. Migrations 035, 037 and 038 run through the official updater; 038 is an idempotent catch-up for installs that already recorded an older appointments-only 035. Migration 034 belongs to release 1.62.89 and is not included.",
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
