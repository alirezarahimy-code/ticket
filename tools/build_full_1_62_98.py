#!/usr/bin/env python3
"""سازندهٔ «سورس کامل نصبی ۱.۶۲.۹۸» — بسته‌ای که برای **نصب از صفر** کافی است.

چرا این بسته؟
  تا پیش از این، آخرین سورس کامل نصبی، `ticket-full-1.62.95` بود و باید سه بستهٔ
  میانی (۹۶، ۹۷) رویش اعمال می‌شد. کسی که «فایل کامل برای نصب از صفر» می‌خواست
  مجبور بود سه مرحله را دستی انجام دهد. این بسته آن سه مرحله را از پیش انجام داده.

روش ساخت (بازتولیدپذیر):
  ۱) محتوای `ticket-full-1.62.95-20261111.zip` باز می‌شود — همان سورس کامل تولید،
     بایت‌به‌بایت، بدون بازسازی از گیت (پس هیچ چیزِ ناخواسته اضافه نمی‌شود).
  ۲) محتوای `files/` بستهٔ میانی ۱.۶۲.۹۶ روی آن می‌نشیند.
  ۳) محتوای `files/` بستهٔ میانی ۱.۶۲.۹۷ روی آن می‌نشیند — همان دو فایل
     `schema.sql`/`install-empty-db.sql` خط ۱.۶۲ با ثبت مهاجرت ۰۳۳ که در آن بسته
     ساخته می‌شوند (بدون هیچ اثر ۱.۶۳).
  ۴) `VERSION` = 1.62.97 و یادداشت provenance.

  چون لایه‌ها از خودِ بسته‌های منتشرشده خوانده می‌شوند، این بسته دقیقاً برابر
  «۹۵ + ۹۶ + ۹۷» است و امکان واگرایی ندارد.

قاعدهٔ سخت: هیچ اثری از خط ۱.۶۳ (request-center، rejected_at، جدول‌های rc_*)
داخل بسته نباشد؛ وگرنه ساخت با خطا متوقف می‌شود.

اجرا:
  python3 tools/build_full_1_62_97.py
"""

from __future__ import annotations

import hashlib
import re
import tempfile
import zipfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
BASE_ZIP = ROOT / "downloads" / "ticket-full-1.62.95-20261111.zip"

RELEASE_VERSION = "1.62.98"
PACKAGE_NAME = "ticket-full-1.62.98-20261116.zip"
NOTE_NAME = "00-FULL-PACKAGE-1.62.98-FA.txt"
ZIP_DATE = (2026, 11, 16, 0, 0, 0)
PREFIX = "ticket/"

# لایه‌ها از خودِ بسته‌های میانی خوانده می‌شوند (نه از شاخهٔ کاری).
# دلیل: شاخهٔ کاری درخت ۱.۶۳ است؛ اگر `schema.sql` را از آن برداریم، ستون
# `rejected_at` و جدول `request_center_appointments` به خط ۱.۶۲ نشت می‌کند.
# با خواندن از بسته‌های منتشرشده، این بسته دقیقاً همان «۹۵ + ۹۶ + ۹۷» می‌شود.
DELTA_96 = ROOT / "downloads" / "ticket-update-1.62.95-to-1.62.96-food-queue-head-of-line-20261114.zip"
DELTA_97 = ROOT / "downloads" / "ticket-update-1.62.96-to-1.62.97-food-worker-selftest-panel-20261115.zip"
DELTA_98 = ROOT / "downloads" / "ticket-update-1.62.97-to-1.62.98-it-expert-routing-alarm-selfheal-20261116.zip"

# هیچ‌یک از این‌ها نباید در بستهٔ خط ۱.۶۲ دیده شود.
FORBIDDEN_CONTENT = re.compile(
    r"request_center_appointments|rc_appt_|REQUEST_CENTER_INDEPENDENT_ENTRY"
    r"|request-center\.php|modules/request-center",
    re.IGNORECASE,
)
# گزارش‌های ۹۳/۹۴/۹۵ همان‌هایی هستند که در سورس پایه‌اند؛ فقط گزارش‌های
# دو نسخهٔ تازه اضافه می‌شوند تا بسته دقیقاً برابر «۹۵ + ۹۶ + ۹۷» بماند.
REPORT_VERSIONS = ("96", "97", "98")


def delta_files(zip_path: Path) -> dict[str, bytes]:
    """محتوای `files/` یک بستهٔ میانی را برمی‌گرداند (VERSION حذف می‌شود)."""
    if not zip_path.is_file():
        raise SystemExit(f"بستهٔ میانی پیدا نشد: {zip_path.name}")
    out: dict[str, bytes] = {}
    with zipfile.ZipFile(zip_path) as z:
        for name in z.namelist():
            if not name.startswith("files/") or name.endswith("/"):
                continue
            rel = name[len("files/"):]
            if not safe_archive_path(rel):
                raise SystemExit(f"مسیر نامعتبر در {zip_path.name}: {name}")
            if rel == "VERSION":
                continue
            out[rel] = z.read(name)
    return out


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


def provenance_note() -> str:
    lines = [
        "بستهٔ سورس کامل نصبی — ۱.۶۲.۹۷ (آخرین نسخهٔ خط ۱.۶۲)",
        "",
        "تاریخ ساخت: ۲۰۲۶‑۱۱‑۱۶",
        "ساخته‌شده با: tools/build_full_1_62_97.py",
        "",
        "این بسته برای «نصب از صفر» کافی است. نیازی به اعمال بسته‌های میانی ۹۶ و ۹۷",
        "روی آن نیست — هر دو از پیش داخلش هست.",
        "",
        "محتوا = سورس کامل ۱.۶۲.۹۵ (همان بستهٔ منتشرشده) + لایهٔ ۱.۶۲.۹۶ + لایهٔ ۱.۶۲.۹۷",
        "  • ۱.۶۲.۹۶: رفع «گرسنگی سرِ صف» در چاپ فیش غذا (food-ticket-runtime.php)",
        "  • ۱.۶۲.۹۷: آزمون جامع Worker/چاپ/مدیریت در انتهای «تنظیمات سیستم ← اتصال پایگاه‌ها»،",
        "    کارت «تغییرات این به‌روزرسانی»، ابزارهای تفسیر هشدار و ثبت مهاجرت ۰۳۳",
        "",
        "مهاجرت‌ها:",
        "  داخل: ۰۰۱ تا ۰۳۷ (به‌جز ۰۳۵). همه در baseline اسکیمای نصب تازه ثبت شده‌اند.",
        "  ثبت‌شده‌ها: ۰۰۱…۰۳۲ (۱.۶۲.۹۵) + ۰۳۳ و ۰۳۴ (۱.۶۲.۸۸) + ۰۳۶ و ۰۳۷ (۱.۶۲.۹۵)",
        "    → روی نصب تازه، صفحهٔ سلامت هیچ هشدار کاذب «مهاجرت اجرا نشده» نمی‌دهد.",
        "  خارج: ۰۳۵ (جدول نوبت‌های مرکز درخواست‌ها) — متعلق به بستهٔ ۱.۶۳ است و آنجا",
        "    می‌آید. این بسته هیچ فایل/جدول/ستونی از ۱.۶۳ ندارد.",
        "",
        "دانهٔ داده:",
        "  seeds/holidays-1405-1406.sql — تعطیلات رسمی ۱۴۰۵/۱۴۰۶ (۵۴ ردیف؛ INSERT IGNORE).",
        "  اختیاری ولی توصیه‌شده؛ بعد از اجرای اسکیما یک‌بار اجرا کنید.",
        "  تعطیلات قمری ۱۴۰۶ برآوردی‌اند؛ از صفحهٔ «تعطیلات» قابل اصلاح‌اند.",
        "",
        "نصب از صفر:",
        "  1) محتوای پوشهٔ ticket/ را روی ریشهٔ وب سرور (XAMPP/Apache) کپی کنید.",
        "  2) از دیتابیس مقصد بک‌آپ بگیرید؛ سپس یکی از این دو:",
        "       الف) install.php را در مرورگر باز کنید (schema.sql را خودش اجرا می‌کند)، یا",
        "       ب) install-empty-db.sql را روی دیتابیس خالی اجرا کنید.",
        "  3) .env.example را به .env تبدیل و تنظیم کنید (اطلاعات دیتابیس و …).",
        "  4) (اختیاری، توصیه‌شده) seeds/holidays-1405-1406.sql را یک‌بار اجرا کنید.",
        "  5) سرویس Worker غذا را طبق START-FOOD-WORKER.txt نصب، اجرا و ری‌استارت کنید.",
        "  6) پنل غذا ← تنظیمات سیستم ← اتصال پایگاه‌ها ← «اجرای آزمون جامع».",
        "",
        "تأیید پس از نصب:",
        "  php tools\\verify_1_62_97.php      (باید ✅ بدهد)",
        "  php tools\\explain_alarms.php      (باید «ایراد واقعی: 0» بدهد)",
        "",
        "نکته: این بسته هیچ فایل محرمانه/دیتابیس زنده ندارد؛ مقادیر محیط تولید را خودتان پر کنید.",
        "",
    ]
    return "\n".join(lines)


def build() -> Path:
    if not BASE_ZIP.is_file():
        raise SystemExit(f"سورس کامل ۱.۶۲.۹۵ پیدا نشد: {BASE_ZIP}")

    with tempfile.TemporaryDirectory(prefix="full-1.62.97-") as tmp:
        base = Path(tmp)
        with zipfile.ZipFile(BASE_ZIP) as z:
            for info in z.infolist():
                name = info.filename
                if not name.startswith(PREFIX) or name.endswith("/"):
                    continue
                rel = name[len(PREFIX):]
                if not safe_archive_path(rel):
                    raise SystemExit(f"مسیر نامعتبر در سورس پایه: {name}")
                dest = base / rel
                dest.parent.mkdir(parents=True, exist_ok=True)
                dest.write_bytes(z.read(info))

        # لایهٔ ۹۶ سپس ۹۷ — دقیقاً همان ترتیبی که کاربر دستی اعمال می‌کند.
        for layer in (DELTA_96, DELTA_97, DELTA_98):
            for rel, data in delta_files(layer).items():
                dest = base / rel
                dest.parent.mkdir(parents=True, exist_ok=True)
                dest.write_bytes(data)

        # گزارش‌های فارسی
        for version in REPORT_VERSIONS:
            rel = f"downloads/CHANGE-REPORT-FA-1.62.{version}.md"
            src = ROOT / rel
            if not src.is_file():
                raise SystemExit(f"گزارش پیدا نشد: {rel}")
            (base / f"CHANGE-REPORT-FA-1.62.{version}.md").write_bytes(src.read_bytes())

        (base / "VERSION").write_text(RELEASE_VERSION + "\n", encoding="utf-8")
        (base / NOTE_NAME).write_text(provenance_note(), encoding="utf-8")

        # ── دروازه‌های ایمنی ─────────────────────────────────────────────
        if (base / "migrations" / "035-request-center-appointments.sql").exists():
            raise SystemExit("مهاجرت ۰۳۵ (۱.۶۳) داخل بسته دیده شد.")
        for path in base.rglob("*"):
            if not path.is_file():
                continue
            rel = path.relative_to(base).as_posix()
            if "request-center" in rel.lower() or "request_center" in rel.lower():
                raise SystemExit(f"اثر خط ۱.۶۳ در مسیر: {rel}")
            # ابزارهای آزمون و توضیح مجازند نام ۱.۶۳ را *ذکر* کنند (مثلاً برای
            # اثبات نبودش). آنچه ممنوع است، وجود واقعی جدول/ستون/ماژول است — و
            # آن با بازرسی schema.sql/install-empty-db.sql در ادامه سنجیده می‌شود.
            is_test_or_doc = (
                rel.startswith("tools/")
                or rel.startswith("CHANGE-REPORT")
                or rel == NOTE_NAME
            )
            if not is_test_or_doc and path.suffix.lower() in (
                ".php", ".sql", ".html", ".js", ".json"
            ):
                text = path.read_text(encoding="utf-8", errors="ignore")
                hit = FORBIDDEN_CONTENT.search(text)
                if hit:
                    raise SystemExit(f"اثر خط ۱.۶۳ در محتوای {rel}: {hit.group(0)}")

        files = sorted(p for p in base.rglob("*") if p.is_file())
        out_dir = ROOT / "downloads"
        package_path = out_dir / PACKAGE_NAME
        temporary = package_path.with_suffix(package_path.suffix + ".tmp")

        def entry(name: str) -> zipfile.ZipInfo:
            info = zipfile.ZipInfo(name, date_time=ZIP_DATE)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o100644 << 16
            return info

        try:
            with zipfile.ZipFile(temporary, "w", zipfile.ZIP_DEFLATED, compresslevel=9) as z:
                for path in files:
                    z.writestr(entry(PREFIX + path.relative_to(base).as_posix()), path.read_bytes())
            with zipfile.ZipFile(temporary, "r") as z:
                if z.testzip() is not None:
                    raise SystemExit("CRC خراب در ZIP ساخته‌شده.")
                names = z.namelist()
                if len(names) != len(set(n.casefold() for n in names)):
                    raise SystemExit("ZIP مسیر تکراری دارد.")
                if z.read(PREFIX + "VERSION").decode().strip() != RELEASE_VERSION:
                    raise SystemExit("VERSION داخل بسته نامعتبر است.")
                schema = z.read(PREFIX + "schema.sql").decode("utf-8")
                if "033-logging-legacy-context-collation.sql" not in schema:
                    raise SystemExit("۰۳۳ در schema.sql ثبت نشده است.")
                if "rejected_at" in schema:
                    raise SystemExit("اثر ۱.۶۳ در schema.sql.")
                if PREFIX + "seeds/holidays-1405-1406.sql" not in set(names):
                    raise SystemExit("seed تعطیلات داخل بسته نیست.")
                if PREFIX + "food-ticket-selftest.php" not in set(names):
                    raise SystemExit("کتابخانهٔ آزمون جامع داخل بسته نیست.")
                if any("request-center" in n.lower() or "request_center" in n.lower() for n in names):
                    raise SystemExit("اثر خط ۱.۶۳ در نام فایل‌ها.")
            temporary.replace(package_path)
            checksum = sha256(package_path.read_bytes())
            sidecar = out_dir / (PACKAGE_NAME + ".sha256")
            sidecar.write_text(f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii")
            print(f"OK {PACKAGE_NAME}")
            print(f"Files: {len(files)} | bytes: {package_path.stat().st_size}")
            print(f"SHA-256: {checksum}")
            return package_path
        finally:
            if temporary.exists():
                temporary.unlink()


if __name__ == "__main__":
    build()
