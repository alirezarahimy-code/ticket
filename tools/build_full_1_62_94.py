#!/usr/bin/env python3
"""سازندهٔ «سورس کامل نصبی» روی آخرین نسخهٔ رسمی خط ۱٫۶۲ — **۱٫۶۲٫۹۴**.

این بسته معادل وضعیت واقعی سرور تولید بعد از اعمال همهٔ بسته‌های میانی است:
`git archive v1.62.88` + بستهٔ میانی ۱٫۶۲٫۹۰ + ۱٫۶۲٫۹۳ + ۱٫۶۲٫۹۴ (هر سه با SHA‑256 ثبت‌شده).

- نسخهٔ ۱٫۶۳ / RC / «مرکز درخواست‌ها» **عمداً داخل این بسته نیست**؛ این بسته سورس کامل و قابل‌نصب
  سیستم تا ۱٫۶۲٫۹۴ است.
- `downloads/` و `updates/` (حدود ۲۸۴ مگابایت آرشیو/ZIP مادر) عمداً داخل بسته نیستند؛ بقیهٔ
  فایل‌های ردی‌شدهٔ ریپازیتوری + همهٔ فایل‌های داخل بسته‌های میانی حاضرند.
- `VERSION` داخل بسته `1.62.94` است؛ یادداشت provenance: `00-FULL-PACKAGE-1.62.94-FA.txt`.
- ZIP با پیشوند `ticket/` ساخته می‌شود؛ فایل جانبی `.sha256` کنار آن نوشته می‌شود.
"""

from __future__ import annotations

import hashlib
import io
import json
import subprocess
import tarfile
import tempfile
import zipfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.62.94"
PACKAGE_NAME = "ticket-full-1.62.94-20261111.zip"
NOTE_NAME = "00-FULL-PACKAGE-1.62.94-FA.txt"
ZIP_DATE = (2026, 11, 11, 0, 0, 0)
BASE_TAG = "v1.62.88"
PREFIX = "ticket/"
EXCLUDE_PREFIXES = ("downloads/", "updates/")
INTERIM = [
    ("downloads/ticket-update-1.62.88-to-1.62.90-food-fixes-20261107.zip",
     "downloads/ticket-update-1.62.88-to-1.62.90-food-fixes-20261107.zip.sha256"),
    ("downloads/ticket-update-1.62.90-to-1.62.93-food-reconciliation-leftover-20261106.zip",
     "downloads/ticket-update-1.62.90-to-1.62.93-food-reconciliation-leftover-20261106.zip.sha256"),
    ("downloads/ticket-update-1.62.93-to-1.62.94-food-guest-org-holiday-20261110.zip",
     "downloads/ticket-update-1.62.93-to-1.62.94-food-guest-org-holiday-20261110.zip.sha256"),
]


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def verify_interim() -> list[Path]:
    out = []
    for pkg_rel, sidecar_rel in INTERIM:
        pkg, sidecar = ROOT / pkg_rel, ROOT / sidecar_rel
        if not pkg.is_file() or not sidecar.is_file():
            raise SystemExit(f"بستهٔ میانی ثبت‌شده موجود نیست: {pkg_rel}")
        recorded = sidecar.read_text(encoding="ascii").split()[0].strip().lower()
        if sha256(pkg.read_bytes()) != recorded:
            raise SystemExit(f"SHA-256 بستهٔ میانی با فایل .sha256 ثبت‌شده نمی‌خواند: {pkg_rel}")
        out.append(pkg)
    return out


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


def reconstruct_base(dest: Path) -> None:
    archive = subprocess.check_output(["git", "archive", BASE_TAG], cwd=ROOT)
    with tarfile.open(fileobj=io.BytesIO(archive)) as tar:
        for member in tar.getmembers():
            name = member.name
            pure = PurePosixPath(name)
            if pure.is_absolute() or any(part in ("", ".", "..") for part in pure.parts):
                raise SystemExit(f"git archive مسیر نامعتبر دارد: {name}")
            if any(name == p.rstrip("/") or name.startswith(p) for p in EXCLUDE_PREFIXES):
                continue
            if not (member.isfile() or member.isdir()):
                continue
            tar.extract(member, dest)
    for pkg in verify_interim():
        with zipfile.ZipFile(pkg) as z:
            for name in z.namelist():
                if not name.startswith("files/") or name.endswith("/"):
                    continue
                rel = name[len("files/"):]
                if not safe_archive_path(rel):
                    raise SystemExit(f"مسیر نامعتبر در بستهٔ میانی: {rel}")
                target = dest / rel
                target.parent.mkdir(parents=True, exist_ok=True)
                target.write_bytes(z.read(name))


def strip_rc_leak(base: Path) -> None:
    """حذف «نشتی RC» از اسکیمای نصب تازه — فقط داخل همین بسته؛ فایل ریپازیتوری دست نمی‌خورد.

    بستهٔ میانی ۱٫۶۲٫۹۳ (که مالک روی سرور اعمال کرده) داخل install-empty-db.sql یک بلوک
    CREATE TABLE request_center_appointments جاسازی کرده بود (همتای مهاجرت ۰۳۵ خط RC).
    نسخهٔ ۱٫۶۳ / RC جزو سامانه نیست؛ بنابراین در بستهٔ سورس نصبی این بلوک حذف می‌شود.
    تضمین: دقیقاً یک بلوک پیدا و حذف شود، وگرنه ساخت متوقف می‌شود.
    """
    import re
    path = base / "install-empty-db.sql"
    text = path.read_text(encoding="utf-8")
    pattern = re.compile(
        r"\n-- برنامهٔ نوبت‌های رسیدگی کارشناسان \(مرکز درخواست‌ها — فاز ۴\)\n"
        r"-- همتای افزایشی [^\n]*\n"
        r"CREATE TABLE IF NOT EXISTS request_center_appointments \(\n"
        r".*?\n\) ENGINE=InnoDB[^\n]*\n",
        re.DOTALL,
    )
    new, count = pattern.subn("\n", text)
    if count != 1:
        raise SystemExit(f"بلوک request_center_appointments دقیقاً یک‌بار یافت نشد (count={count})؛ ساخت متوقف شد.")
    path.write_text(new, encoding="utf-8")


def check_no_request_center(dest: Path) -> None:
    import re
    for path in dest.rglob("*"):
        if not path.is_file():
            continue
        rel = path.relative_to(dest).as_posix()
        if "request-center" in rel.lower():
            raise SystemExit(f"اثر «مرکز درخواست‌ها» در مسیر: {rel}")
        if path.suffix.lower() not in (".php", ".js", ".html", ".css", ".md", ".json", ".txt", ".sql", ".yml", ".csv"):
            continue
        hit = re.search(r"modules/request-center|request_center_|REQUEST_CENTER_INDEPENDENT_ENTRY", path.read_text(encoding="utf-8", errors="ignore"), re.IGNORECASE)
        if hit:
            raise SystemExit(f"اثر «مرکز درخواست‌ها» در محتوای {rel}: {hit.group(0)}")


def provenance_note() -> str:
    lines = [
        "بستهٔ سورس کامل نصبی — آخرین نسخهٔ رسمی خط ۱٫۶۲: 1.62.94",
        "",
        "تاریخ ساخت: ۲۰۲۶‑۱۱‑۱۱",
        "ساخته‌شده با: tools/build_full_1_62_94.py",
        "",
        "پایهٔ این بسته (دقیقاً معادل سرور تولید بعد از اعمال همهٔ بسته‌های میانی):",
        "  1) git archive v1.62.88 (بدون پوشه‌های downloads/ و updates/)",
        "  2) پوشش بستهٔ میانی ۱٫۶۲٫۹۰  (ticket-update-1.62.88-to-1.62.90-food-fixes-20261107.zip)",
        "  3) پوشش بستهٔ میانی ۱٫۶۲٫۹۳  (ticket-update-1.62.90-to-1.62.93-food-reconciliation-leftover-20261106.zip)",
        "  4) پوشش بستهٔ میانی ۱٫۶۲٫۹۴  (ticket-update-1.62.93-to-1.62.94-food-guest-org-holiday-20261110.zip)",
        "",
        "SHA-256 بسته‌های میانی در فایل‌های .sha256 کنار آن‌ها در ریپازیتوری ثبت است و هنگام ساخت تأیید شد.",
        "",
        "نسخهٔ ۱٫۶۳ / RC / «مرکز درخواست‌ها» عمداً داخل این بسته نیست و بخشی از سامانهٔ تولید نیست.",
        "انحراف آگاهانه (اعلام صریح): بستهٔ میانی ۱٫۶۲٫۹۳ inside install-empty-db.sql یک بلوک"
        " request_center_appointments (همتای مهاجرت ۰۳۵ خط RC) جاسازی کرده بود؛ طبق تصمیم مالک"
        " (۱٫۶۳ جزو سامانه نیست) آن بلوک فقط داخل این بستهٔ نصبی حذف شده است. نصب تازه هیچ جدول"
        " RC نمی‌سازد؛ نصب‌های ارتقایی از قبل هم به این جدول نیازی نداشته‌اند (بی‌استفاده و idempotent).",
        "فایل ریپازیتوری install-empty-db.sql تغییر نکرده است.",
        "",
        "نصب (تازه):",
        "  1) محتوای پوشهٔ ticket/ را روی ریشهٔ وب سرور (XAMPP/Apache) کپی کنید.",
        "  2) از دیتابیس مقصد بک‌آپ بگیرید؛ install-empty-db.sql (یا schema.sql) را روی دیتابیس خالی اجرا کنید.",
        "  3) .env.example را به .env تنظیم کنید (اطلاعات دیتابیس و …)؛ config.sample.php راهنمای تنظیمات است.",
        "  4) سرویس Worker غذا را طبق START-HERE.txt / install-food-worker-SERVICE.bat نصب و اجرا کنید.",
        "  5) مهاجرت‌های داخل migrations/ را به‌ترتیب شماره روی دیتابیس اعمال کنید (۰۳۶ داخل schema است؛ این بسته شامل ۱٫۶۲٫۹۴ است و ۰۳۷ را ندارد).",
        "",
        "نکته: این بسته هیچ فایل محرمانه/دیتابیس زنده ندارد؛ مقادیر محیط تولید را خودتان پر کنید.",
        "",
    ]
    return "\n".join(lines)


def build() -> tuple[Path, Path]:
    with tempfile.TemporaryDirectory(prefix="full-1.62.94-") as tmp:
        base = Path(tmp)
        reconstruct_base(base)
        strip_rc_leak(base)
        check_no_request_center(base)
        (base / "VERSION").write_text(f"{RELEASE_VERSION}\n", encoding="utf-8")
        (base / NOTE_NAME).write_text(provenance_note(), encoding="utf-8")

        files = sorted(path for path in base.rglob("*") if path.is_file())
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
                for path in files:
                    rel = path.relative_to(base).as_posix()
                    z.writestr(entry(PREFIX + rel), path.read_bytes())
            with zipfile.ZipFile(temporary, "r") as z:
                if z.testzip() is not None:
                    raise SystemExit("CRC خراب در ZIP ساخته‌شده.")
                names = z.namelist()
                if len(names) != len(set(n.casefold() for n in names)):
                    raise SystemExit("ZIP مسیر تکراری دارد.")
                if any(not safe_archive_path(n[len(PREFIX):]) for n in names if n.startswith(PREFIX)):
                    raise SystemExit("ZIP مسیر نامعتبر دارد.")
                if z.read(PREFIX + "VERSION").decode().strip() != RELEASE_VERSION:
                    raise SystemExit("VERSION داخل بسته نامعتبر است.")
            temporary.replace(package_path)
            checksum = sha256(package_path.read_bytes())
            sidecar = out_dir / (PACKAGE_NAME + ".sha256")
            sidecar.write_text(f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii")
            print(f"OK {PACKAGE_NAME}")
            print(f"Files: {len(files)} | bytes: {package_path.stat().st_size}")
            print(f"SHA-256: {checksum}")
            return package_path, sidecar
        finally:
            if temporary.exists():
                temporary.unlink()


if __name__ == "__main__":
    build()
