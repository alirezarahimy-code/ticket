#!/usr/bin/env python3
"""سازندهٔ بستهٔ تجمیعی ۱.۶۲.۸۸ — از پایهٔ دقیق ۱.۶۲.۸۲ (نسخهٔ نصب‌شدهٔ کاربر).

تفاوت این سازنده با tools/build_cumulative_update.py:
  * پایه را ۱.۶۲.۷۷ نمی‌گیرد، بلکه دقیقاً کامیت ۱.۶۲.۸۲ است؛ پس بسته فقط «دلتا»
    را حمل می‌کند و فایلی که روی سیستم کاربر دست‌نخورده است دوباره کپی نمی‌شود.
  * مهاجرت‌های ۰۲۴ تا ۰۳۳ که هرگز در هیچ بسته‌ای ارسال نشده‌اند را صریح می‌آورد.
  * ترتیب فهرست migrations در manifest = ترتیب اجرا در system-update.php:363.

اجرا:  python3 tools/build_update_1_62_88.py
"""
from __future__ import annotations

import hashlib
import json
import subprocess
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent

RELEASE_VERSION = "1.62.88"
REQUIRES_VERSION = "1.62.82"          # دقیقاً نسخهٔ نصب‌شدهٔ کاربر
BASE_COMMIT = "b5711e79d5f5dc6d73f3a248a80c7a51d609c1c5"   # = ۱.۶۲.۸۲
RELEASE_DATE = "2026-11-05"
PACKAGE_NAME = f"ticket-update-1.62.82-to-{RELEASE_VERSION}-merged-20261105.zip"
REPORT_NAME = f"CHANGE-REPORT-FA-{RELEASE_VERSION}.md"
ZIP_DATE = (2026, 11, 5, 12, 0, 0)

# پوشه‌هایی که ارزش اجرایی روی سرور ندارند و عمداً از بسته بیرون می‌مانند.
EXCLUDED_PREFIXES = ("downloads/", "updates/", "tests/", "docs/system-map/", ".github/")

# مهاجرت‌ها به ترتیب اجرا. ۰۳۲ عمداً نیست — دلیل در گزارش تغییرات.
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
EXCLUDED_MIGRATIONS = {
    "032-schema-updates-baseline.sql": (
        "کلید یکتای جدول schema_updates روی (version, migration) است، نه فقط migration؛ "
        "بنابراین INSERT IGNORE این مهاجرت ردیف‌های تکراری با برچسب نسخهٔ نادرست «1.62.83» می‌سازد. "
        "ضمناً هدفش ثبت مهاجرت‌های «قبلاً اجراشده» است، در حالی که همین بسته ۰۲۴ تا ۰۳۱ را "
        "واقعاً اجرا می‌کند و system-update.php خودش آن‌ها را با نسخهٔ درست ثبت می‌کند."
    ),
}


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def git(*args: str) -> str:
    return subprocess.run(
        ["git", *args], cwd=ROOT, check=True, capture_output=True, text=True
    ).stdout


def changed_files() -> list[str]:
    """فایل‌های افزوده/تغییریافته نسبت به ۱.۶۲.۸۲ (حذف‌شده‌ها قابل تحویل نیستند)."""
    out = git("diff", "--diff-filter=ACMR", "--name-only", BASE_COMMIT, "HEAD")
    names = [n for n in out.splitlines() if n.strip()]
    keep = [n for n in names if not n.startswith(EXCLUDED_PREFIXES)]
    # مهاجرت کنارگذاشته‌شده نباید حتی به‌صورت فایل روی دیسک کپی شود، وگرنه بعداً
    # ممکن است دستی اجرا شود و همان ردیف‌های تکراری را بسازد.
    keep = [n for n in keep if Path(n).name not in EXCLUDED_MIGRATIONS]
    return sorted(set(keep))


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
        path = ROOT / "migrations" / name
        if not path.is_file():
            raise SystemExit(f"مهاجرت موجود نیست: {name}")
        data = path.read_bytes()
        migrations[name] = data
        files["migrations/" + name] = data        # نسخهٔ روی دیسک هم به‌روز شود

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
        "title": "ادغام سخت‌سازی امنیتی با اصلاحات هشت‌بسته‌ای — بستهٔ تجمیعی از ۱.۶۲.۸۲",
        "requires_version": REQUIRES_VERSION,
        "migrations": list(MIGRATIONS),           # ترتیب = ترتیب اجرا
        "files": sorted(files),
        "sha256": {name: sha256(files[name]) for name in sorted(files)},
        "description": "Cumulative merged update from 1.62.82 to 1.62.88",
    }

    out_dir = ROOT / "downloads"
    out_dir.mkdir(parents=True, exist_ok=True)
    package_path = out_dir / PACKAGE_NAME
    manifest_bytes = (json.dumps(manifest, ensure_ascii=False, indent=2) + "\n").encode("utf-8")

    def entry(name: str) -> zipfile.ZipInfo:
        info = zipfile.ZipInfo(name, date_time=ZIP_DATE)
        info.compress_type = zipfile.ZIP_DEFLATED
        info.external_attr = 0o644 << 16
        return info

    with zipfile.ZipFile(package_path, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as z:
        z.writestr(entry("manifest.json"), manifest_bytes)
        for name in MIGRATIONS:
            z.writestr(entry("migrations/" + name), migrations[name])
        for name in sorted(files):
            z.writestr(entry("files/" + name), files[name])

    # ---- اعتبارسنجی پس از ساخت ----
    with zipfile.ZipFile(package_path) as z:
        names = [i.filename for i in z.infolist()]
        if len(names) != len({n.casefold() for n in names}):
            raise SystemExit("ZIP مسیر تکراری دارد.")
        actual = {n[len("files/"):] for n in names if n.startswith("files/")}
        if actual != set(manifest["files"]):
            raise SystemExit("فهرست files با محتوای ZIP نمی‌خواند.")
        got = json.loads(z.read("manifest.json"))
        if got["version"] != RELEASE_VERSION or got["requires_version"] != REQUIRES_VERSION:
            raise SystemExit("قرارداد نسخهٔ manifest نامعتبر است.")
        for relative, expected in got["sha256"].items():
            if sha256(z.read("files/" + relative)) != expected:
                raise SystemExit(f"عدم تطابق SHA-256: {relative}")
        mig_in_zip = {n[len("migrations/"):] for n in names if n.startswith("migrations/")}
        if mig_in_zip != set(got["migrations"]):
            raise SystemExit("فهرست مهاجرت‌ها با محتوای ZIP نمی‌خواند.")
        if "032-schema-updates-baseline.sql" in mig_in_zip:
            raise SystemExit("مهاجرت ۰۳۲ نباید در بسته باشد.")

    checksum = sha256(package_path.read_bytes())
    sidecar = out_dir / (PACKAGE_NAME + ".sha256")
    sidecar.write_text(f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii")
    print(f"✅ {PACKAGE_NAME}")
    print(f"   فایل‌ها: {len(files)} | مهاجرت‌ها: {len(MIGRATIONS)} | حجم: {package_path.stat().st_size:,} بایت")
    print(f"   SHA-256: {checksum}")
    return package_path, sidecar


if __name__ == "__main__":
    build()
