#!/usr/bin/env python3
"""سازندهٔ «سورس کامل نصبی ۱.۶۳.۱» — بسته‌ای که برای **نصب از صفر** کافی است.

چرا این بسته؟ چون ۱.۶۳ یک «بستهٔ کاندید» بود که روی ۱.۶۲.۹۶ نصب می‌شد؛ کسی که
از صفر شروع می‌کند نباید سه مرحله (۹۵ → ۹۶ → ۱.۶۳) را دستی انجام دهد.

روش ساخت (بازتولیدپذیر، بی‌واگرایی):
  ۱) درخت گیت در `BASE_COMMIT` (= main، آخرین نسخهٔ ۱.۶۲.۹۵) استخراج می‌شود.
  ۲) محتوای `files/` بستهٔ r16 (۱.۶۳) روی آن می‌نشیند — یعنی همان چیزی که روی
     سامانهٔ مالک نصب است.
  ۳) محتوای `files/` بستهٔ ۱.۶۳.۱ روی آن می‌نشیند.
  ۴) `VERSION` = 1.63.1 و یادداشت provenance.

چون لایه‌ها از خودِ بسته‌های منتشرشده خوانده می‌شوند، نتیجه دقیقاً برابر
«main + ۱.۶۳ + ۱.۶۳.۱» است و امکان واگرایی ندارد.

اجرا:
  python3 tools/build_full_1_63_1.py
"""

from __future__ import annotations

import hashlib
import subprocess
import zipfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
BASE_COMMIT = "00e7e759bda9460dcb245812e2e6b617670efade"  # main — ۱.۶۲.۹۵
DELTA_163 = ROOT / "downloads" / "ticket-update-1_62_96-to-1_63-request-center-rc-candidate-20261114-r16.zip"
DELTA_1631 = ROOT / "downloads" / "ticket-update-1.63-to-1.63.1-r12-cancel-fix-20261115.zip"

RELEASE_VERSION = "1.63.1"
PACKAGE_NAME = "ticket-full-1.63.1-r12-20261115.zip"
NOTE_NAME = "00-FULL-PACKAGE-1.63.1-FA.txt"
ZIP_DATE = (2026, 11, 6, 4, 0, 0)
PREFIX = "ticket/"

# این‌ها هرگز نباید در بستهٔ نصبی باشند: خروجی ابزار، نه سورس.
EXCLUDED_PREFIXES = (
    "downloads/", "updates/", "patches/", "preview/",
    "docs/system-map/", "ci-diagnostics/", "benchmark-results/",
    ".github/", "node_modules/", "vendor/",
)
EXCLUDED_SUFFIXES = (".zip", ".sha256")


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


def layer(files: dict[str, bytes], zip_path: Path) -> None:
    """محتوای `files/` یک بستهٔ میانی را روی دیکشنری می‌نشاند."""
    if not zip_path.is_file():
        raise SystemExit(f"لایه پیدا نشد: {zip_path.name}")
    with zipfile.ZipFile(zip_path) as z:
        for name in z.namelist():
            if not name.startswith("files/") or name.endswith("/"):
                continue
            rel = name[len("files/"):]
            if not safe_archive_path(rel):
                raise SystemExit(f"مسیر نامعتبر در {zip_path.name}: {name}")
            if rel == "VERSION":
                continue
            files[rel] = z.read(name)
        # مهاجرت‌ها (۱.۶۳ یکی دارد)
        for name in z.namelist():
            if name.startswith("migrations/") and not name.endswith("/"):
                files[name] = z.read(name)


def provenance_note() -> str:
    lines = [
        "بستهٔ سورس کامل نصبی — ۱.۶۳.۱",
        "",
        "تاریخ ساخت: ۲۰۲۶‑۱۱‑۰۶",
        "ساخته‌شده با: tools/build_full_1_63_1.py",
        "",
        "این بسته برای «نصب از صفر» کافی است. نیازی به اعمال ۱.۶۲.۹۵، ۱.۶۲.۹۶،",
        "۱.۶۳ یا ۱.۶۳.۱ روی آن نیست — همه از پیش داخلش هست.",
        "",
        "محتوا = main (۱.۶۲.۹۵) + لایهٔ ۱.۶۳ (r16) + لایهٔ ۱.۶۳.۱",
        "",
        "  • خط ۱.۶۲: همهٔ اصلاحات ۹۵/۹۶/۹۷ در main هستند و همین‌جا می‌آیند.",
        "  • ۱.۶۳: مرکز درخواست‌ها (فاز ۱…۴)، مهاجرت ۰۳۵ (جدول نوبت‌ها)،",
        "    آژیر سلامت در lib/system-alarms.php.",
        "  • ۱.۶۳.۱: نقش اعطایی به‌جای تبدیل نقش؛ مسئول تیکت فقط از فهرست",
        "    کارشناسان؛ رفع TypeError مرگبار در ticket_assignee_is_eligible؛",
        "    خوددرمانی سه آژیر؛ اعطای دسترسی «رسیدگی» به کارشناسان برگزیده.",
        "",
        "مهاجرت‌ها:",
        "  داخل: ۰۰۱ تا ۰۳۷. همه در baseline اسکیمای نصب تازه ثبت شده‌اند.",
        "  روی نصب تازه، صفحهٔ سلامت هیچ هشدار کاذب «مهاجرت اجرا نشده» نمی‌دهد.",
        "",
        "دانهٔ داده:",
        "  seeds/holidays-1405-1406.sql — تعطیلات رسمی (۵۴ ردیف؛ INSERT IGNORE).",
        "  اختیاری ولی توصیه‌شده؛ بعد از اجرای اسکیما یک‌بار اجرا کنید.",
        "",
        "نصب از صفر:",
        "  1) محتوای پوشهٔ ticket/ را روی ریشهٔ وب سرور کپی کنید.",
        "  2) از دیتابیس مقصد بک‌آپ بگیرید؛ سپس install.php را در مرورگر باز کنید",
        "     (schema.sql را خودش اجرا می‌کند).",
        "  3) config.php را از روی .env.example بسازید و اطلاعات دیتابیس را پر کنید.",
        "  4) (اختیاری، توصیه‌شده) seeds/holidays-1405-1406.sql را یک‌بار اجرا کنید.",
        "  5) cron\\register_system_alarm_tasks.bat را اجرا کنید (یک‌بار، با دسترسی مدیر)",
        "     تا دو وظیفهٔ زمان‌بندی‌شدهٔ آژیر سلامت ساخته شوند.",
        "  6) صفحهٔ «سلامت سامانه» را باز کنید — باید بدون هشدار باشد.",
        "",
        "تأیید پس از نصب:",
        "  php tools\\explain_alarms.php      (باید «ایراد واقعی: 0» بدهد)",
        "",
        "نکته: این بسته هیچ فایل محرمانه/دیتابیس زنده ندارد؛ مقادیر محیط تولید را خودتان پر کنید.",
        "",
    ]
    return "\n".join(lines)


def build() -> Path:
    files: dict[str, bytes] = {}

    # ۱) main — آخرین نسخهٔ خط ۱.۶۲
    listing = subprocess.check_output(
        ["git", "ls-tree", "-r", "--name-only", BASE_COMMIT], cwd=ROOT
    ).decode().splitlines()
    for rel in listing:
        if any(rel.startswith(p) for p in EXCLUDED_PREFIXES):
            continue
        if rel.endswith(EXCLUDED_SUFFIXES):
            continue
        if not safe_archive_path(rel):
            continue
        try:
            files[rel] = subprocess.check_output(
                ["git", "show", f"{BASE_COMMIT}:{rel}"], cwd=ROOT
            )
        except subprocess.CalledProcessError:
            continue

    # ۲) لایهٔ ۱.۶۳
    layer(files, DELTA_163)
    # ۳) لایهٔ ۱.۶۳.۱
    layer(files, DELTA_1631)

    # ۴) ابزارها و آزمون‌های مخزن از HEAD — **بعد از** لایه‌ها.
    # چرا بعد و نه قبل: بستهٔ r16 خودش `tools/` دارد و اگر ابزارها را قبل از آن
    # بریزیم، نسخهٔ کهنهٔ r16 روی نسخهٔ به‌روز می‌نشیند. ترتیب مهم است.
    for sub in ("tools", "tests"):
        for path in sorted((ROOT / sub).rglob("*")):
            if not path.is_file():
                continue
            rel = path.relative_to(ROOT).as_posix()
            if rel.startswith(EXCLUDED_PREFIXES) or rel.endswith((".zip", ".sha256")):
                continue
            files[rel] = path.read_bytes()

    # ۴) نسخه و یادداشت
    files["VERSION"] = (RELEASE_VERSION + "\n").encode("utf-8")
    files[NOTE_NAME] = provenance_note().encode("utf-8")

    # دروازه‌ها
    if "CHANGE-REPORT-FA-1.63.1.md" not in files:
        raise SystemExit("گزارش ۱.۶۳.۱ در بسته نیست.")
    if files.get("VERSION", b"").strip() != RELEASE_VERSION.encode():
        raise SystemExit("VERSION نادرست است.")
    for rel in files:
        if rel.startswith(EXCLUDED_PREFIXES) or rel.endswith(EXCLUDED_SUFFIXES):
            raise SystemExit(f"فایل غیرمجاز در بسته: {rel}")

    out_dir = ROOT / "downloads"
    out_dir.mkdir(exist_ok=True)
    package_path = out_dir / PACKAGE_NAME

    with zipfile.ZipFile(package_path, "w", zipfile.ZIP_DEFLATED) as zf:
        for rel in sorted(files):
            info = zipfile.ZipInfo(PREFIX + rel, date_time=ZIP_DATE)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o644 << 16
            zf.writestr(info, files[rel])

    checksum = sha256(package_path.read_bytes())
    (out_dir / (PACKAGE_NAME + ".sha256")).write_text(
        f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii"
    )

    print(f"OK {PACKAGE_NAME}")
    print(f"Files: {len(files)} | bytes: {package_path.stat().st_size}")
    print(f"SHA-256: {checksum}")
    return package_path


if __name__ == "__main__":
    build()
