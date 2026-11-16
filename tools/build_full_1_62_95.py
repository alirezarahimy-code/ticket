#!/usr/bin/env python3
"""سازندهٔ «سورس کامل نصبی» روی آخرین نسخهٔ خط ۱٫۶۲ — **۱٫۶۲٫۹۵**.

محتوای این بسته از یک درختِ واحدِ گیت گرفته می‌شود (پیش‌فرض: `origin/main`؛ قابل تغییر با `--ref`)،
نه از ترکیب دستی بسته‌های میانی؛ بنابراین درخت مرجع باید همان درختی باشد که روی `main` می‌نشیند:
line ۱٫۶۲٫۹۰ (main) + بسته‌های میانی ۱٫۶۲٫۹۳ / ۱٫۶۲٫۹۴ / ارتقاء ۱٫۶۲٫۹۵ — با **صفر اثر** از خط ۱٫۶۳.

- خط ۱٫۶۳ / ارتقای «مرکز درخواست‌ها» **عمداً داخل این بسته نیست**؛ این بسته سورس کامل و قابل‌نصب
  سیستم تا ۱٫۶۲٫۹۵ است. سازنده اگر هر نشانهٔ آن خط را پیدا کند (مسیر/مهاجرت ۰۳۵/جدول زمانی/رشته‌های
  شناخته‌شده) با خطا متوقف می‌شود.
- `downloads/` و `updates/` (آرشیوهای مادر و بسته‌های قبلاً منتشرشده) عمداً داخل بسته نیستند؛
  **یک** دانهٔ داده‌ای از updates/ بایت‌به‌بایت کپی می‌شود: تعطیلات رسمی ۱۴۰۵/۱۴۰۶.
- `VERSION` داخل بسته `1.62.95` است؛ یادداشت provenance: `00-FULL-PACKAGE-1.62.95-FA.txt`.
- ZIP با پیشوند `ticket/` ساخته می‌شود؛ فایل جانبی `.sha256` کنار آن نوشته می‌شود.

اجرا:
  python3 tools/build_full_1_62_95.py                 # از origin/main (پس از fetch)
  python3 tools/build_full_1_62_95.py --ref <commit>  # از یک commit/شاخهٔ مشخص
"""

from __future__ import annotations

import argparse
import hashlib
import io
import re
import subprocess
import tarfile
import tempfile
import zipfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.62.95"
PACKAGE_NAME = "ticket-full-1.62.95-20261111.zip"
NOTE_NAME = "00-FULL-PACKAGE-1.62.95-FA.txt"
ZIP_DATE = (2026, 11, 12, 0, 0, 0)
PREFIX = "ticket/"
EXCLUDE_PREFIXES = ("downloads/", "updates/")

# نشانه‌های قطعی خط ارتقای ناتمام — اگر هر کدام دیده شد، ساخت متوقف می‌شود.
FORBIDDEN_PATHS = (
    "modules/request-center",
    "migrations/035-request-center-appointments.sql",
    "docs/architecture/REQUEST-CENTER-ARCHITECTURE.md",
    "request-center.php",
    ".github/workflows/request-center-contract.yml",
    ".github/workflows/request-center-package-preview.yml",
    ".github/workflows/refresh-request-center-map.yml",
    ".github/workflows/request-center-t12-characterization.yml",
    ".github/workflows/request-center-t12-scenarios.yml",
)
FORBIDDEN_CONTENT = re.compile(
    r"modules/request-center|request-center\.php|REQUEST_CENTER_INDEPENDENT_ENTRY"
    r"|request_center_|requestcenter",
    re.IGNORECASE,
)
TEXT_EXT = (".php", ".js", ".html", ".css", ".md", ".json", ".txt", ".sql",
            ".yml", ".yaml", ".csv", ".tsv", ".ics", ".htaccess", ".example", "")


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


def head_sha(ref: str) -> str:
    return subprocess.check_output(["git", "rev-parse", ref], cwd=ROOT, text=True).strip()


def reconstruct_base(dest: Path, ref: str) -> str:
    sha = head_sha(ref)
    archive = subprocess.check_output(["git", "archive", sha], cwd=ROOT)
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
    return sha


def check_no_foreign_line(dest: Path) -> None:
    """هیچ نشانه‌ای از خط ارتقای ناتمام داخل بسته نباشد؛ وگرنه ساخت متوقف می‌شود."""
    for path in sorted(dest.rglob("*")):
        rel = path.relative_to(dest).as_posix()
        lowered = rel.lower()
        for forbidden in FORBIDDEN_PATHS:
            if lowered == forbidden or lowered.startswith(forbidden.lower() + "/"):
                raise SystemExit(f"اثر خط ارتقای ناتمام در مسیر: {rel}")
        if not path.is_file():
            continue
        if path.suffix.lower() not in TEXT_EXT:
            continue
        try:
            text = path.read_text(encoding="utf-8", errors="ignore")
        except OSError:
            continue
        hit = FORBIDDEN_CONTENT.search(text)
        if hit:
            raise SystemExit(f"اثر خط ارتقای ناتمام در محتوای {rel}: {hit.group(0)}")


def add_reports(base: Path, ref: str) -> None:
    """گزارش‌های فارسی خط تولید داخل بسته — بایت‌به‌بایت همان‌هایی که در درختِ مرجع (downloads/) هستند.

    مخزن گزارش‌ها را زیر downloads/ نگه می‌دارد ولی خودِ downloads/ داخل بسته نیست؛
    بستهٔ کامل، گزارش‌ها را طبق عرف بسته‌های قبلی در ریشه می‌گذارد.
    """
    sha = head_sha(ref)
    for version in ("93", "94", "95"):
        rel = f"downloads/CHANGE-REPORT-FA-1.62.{version}.md"
        try:
            data = subprocess.check_output(["git", "show", f"{sha}:{rel}"], cwd=ROOT)
        except subprocess.CalledProcessError:
            raise SystemExit(f"گزارش فارسی ۱٫۶۲٫{version} در {ref} پیدا نشد: {rel}") from None
        (base / f"CHANGE-REPORT-FA-1.62.{version}.md").write_bytes(data)


def add_seeds(base: Path) -> None:
    """دانه‌های داده‌ای تولید که در `updates/` ردی‌شده‌اند ولی بخشی از schema/migrations نیستند.

    ۱) تعطیلات رسمی ۱۴۰۵/۱۴۰۶ (نسخهٔ ۱٫۵۵؛ ۵۴ ردیف؛ INSERT IGNORE). فقط همین‌جا بایت‌به‌بایت کپی می‌شود؛
       فایل ریشهٔ updates/ دست‌نخورده می‌ماند. در نصب تازه اختیاری ولی توصیه‌شده است.
       توجه: تعطیلات قمری ۱۴۰۶ برآوردند؛ قابل اصلاح از صفحهٔ «تعطیلات».
    """
    seeds = base / "seeds"
    seeds.mkdir(parents=True, exist_ok=True)
    src = ROOT / "updates" / "2026-10-14-release-155" / "migrations" / "002-holidays-1405-1406.sql"
    if not src.is_file():
        raise SystemExit(f"فایل seed تعطیلات پیدا نشد: {src}")
    (seeds / "holidays-1405-1406.sql").write_bytes(src.read_bytes())


def provenance_note(source_sha: str, ref: str) -> str:
    lines = [
        "بستهٔ سورس کامل نصبی — آخرین نسخهٔ خط ۱٫۶۲: 1.62.95",
        "",
        "تاریخ ساخت: ۲۰۲۶‑۱۱‑۱۲",
        "ساخته‌شده با: tools/build_full_1_62_95.py",
        f"درخت مرجع بسته: {ref} (commit {source_sha})",
        "محتوا = خط تولید ۱٫۶۲ تا امروز: ۱٫۶۲٫۹۰ (شاخهٔ اصلی) + بسته‌های میانی ۱٫۶۲٫۹۳ / ۱٫۶۲٫۹۴ + ارتقاء ۱٫۶۲٫۹۵",
        "(مهاجرت‌های ۰۳۶ و ۰۳۷؛ اصلاحات رابط کاربری غذا و حفظ فوکوس؛ پنجرهٔ سختگیرانهٔ Worker غذا؛",
        "  ثبت خودکار پایان‌روز؛ پذیرفتن «تعطیل رسمی» هنگام ثبت سفارش؛ CD/DVD: شماره برگه خروج اجباری؛",
        "  لاگینگ تشدیدشده/خلاصهٔ هوشمند (۰۳۴)؛ نقشهٔ ماشین‌خوان docs/system-map؛ پایش یکپارچی نصب تازه).",
        "",
        "دانه‌های داده‌ای (داخل بسته):",
        "  seeds/holidays-1405-1406.sql — تعطیلات رسمی ۱۴۰۵/۱۴۰۶ (۵۴ ردیف؛ از نسخهٔ ۱٫۵۵؛ INSERT IGNORE؛",
        "  فقط همین‌جا کپی شده؛ فایل ریشهٔ updates/ دست‌نخورده است). نصب تازه: اختیاری ولی توصیه‌شده؛",
        "  بعد از اجرای install-empty-db.sql یک‌بار اجرا کنید. تعطیلات قمری ۱۴۰۶ برآوردند؛ از صفحهٔ",
        "  «تعطیلات» در سامانه قابل اصلاح‌اند.",
        "",
        "کدام مهاجرت‌ها داخل بسته‌اند و کدام نیستند (و چرا):",
        "  داخل: ۰۰۱ تا ۰۳۴ (ثبت‌شده در اسکیمای نصب تازه) + ۰۳۶ + ۰۳۷ — همهٔ مهاجرت‌های خط تولید.",
        "  خارج: مهاجرت ۰۳۵ — متعلق به یک خط ارتقای ناتمام و خارج از سامانهٔ تولید؛ طبق تصمیم مالک",
        "  آن خط جزو این سامانه نیست. هیچ ماژول/فایل/ workflow ای از آن خط داخل این بسته نیست.",
        "  (اسکیمای نصب تازه هم همتای ساختاری آن مهاجرت را ندارد؛ اگر یک نسخهٔ قدیمی روی سرورت",
        "  اعمال شده باشد، بی‌استفاده می‌ماند و هیچ جای کد به آن ارجاع نمی‌دهد.)",
        "  خارج: خودِ پوشهٔ updates/ و downloads/ (آرشیوهای مادر)؛ فقط seed تعطیلات بالا به‌صورت انتخابی",
        "  در seeds/ آورده شد.",
        "  هیچ دادهٔ زنده‌ای (کاربران/سازمان/غذاها/…) داخل بسته نیست؛ آن‌ها داده‌اند نه سورس؛ با صفحهٔ",
        "  «تعطیلات» و سایر صفحات مدیریت وارد کنید.",
        "",
        "اصلاحات تازه در ۱٫۶۲٫۹۵ (که سورس ۱٫۶۲٫۹۴ ندارد):",
        "  - CD/DVD: «شماره برگه خروج» برای ثبت خروج اجباری است (فکوس + پیام؛ سرور هم بررسی می‌کند).",
        "  - غذا: «پنجرهٔ سختگیرانهٔ Worker» — خارج از پنجرهٔ کاری = صفر فعالیت؛ تنها استثنا گذر پایان‌روز ≥۲۳ تهران،",
        "    یک‌بار در روز (اول خواندن/چاپ تردد واقعی، سپس ثبت خودکار برای امروز و دیروز؛ idempotent؛ آف‌تر-خطای ۱۰ دقیقه).",
        "  - غذا: ردیف‌های ثبت خودکار پرسنل وعده‌های سلف‌بسته (شام + ناهار پنجشنبه/جمعه/تعطیلات رسمی) با delivery_status='delivered'",
        "    در گزارش تحویل (و Excel/چاپ) با برچسب «چاپ خودکار»/«چاپ خودکار مهمان»؛ مهمان‌ها بدون تحویل؛ ناهار روز عادی هرگز.",
        "  - غذا: حذف رفرش خودکار ۳۰ ثانیه‌ای گزارش‌ها (تازه‌سازی فقط با دکمه؛ رفرش زنده/داشبورد دست‌نخورده).",
        "  - غذا: تأیید قطعی «این روز تعطیل است» هنگام ثبت سفارش در تعطیل.",
        "",
        "مهاجرت ۰۳۷-food-auto-receipts-delivered.sql داخل migrations/ هست (idempotent؛ فقط auto_printed → delivered؛ auto_guest دست‌نخورده).",
        "در نصب تازه بی‌خطر است (ردیفی برای به‌روزرسانی وجود ندارد).",
        "",
        "۱٫۶۳ / خط ارتقای ناتمام عمداً داخل این بسته نیست و بخشی از سامانهٔ تولید نیست.",
        "یادداشت cache-buster: اسکریپت پنل غذا در food-ticket-web/index.html با ?v=1.62.96",
        "بارگذاری می‌شود؛ نام بسته و VERSION داخل بسته 1.62.95 هستند.",
        "",
        "نصب (تازه):",
        "  1) محتوای پوشهٔ ticket/ را روی ریشهٔ وب سرور (XAMPP/Apache) کپی کنید.",
        "  2) از دیتابیس مقصد بک‌آپ بگیرید؛ install-empty-db.sql (یا schema.sql) را روی دیتابیس خالی اجرا کنید.",
        "  3) .env.example را به .env تنظیم کنید (اطلاعات دیتابیس و …)؛ config.sample.php راهنمای تنظیمات است.",
        "  4) (اختیاری، توصیه‌شده) seeds/holidays-1405-1406.sql را یک‌بار اجرا کنید.",
        "  5) ./migrations را به‌ترتیب شماره‌ای روی دیتابیس اعمال کنید (۰۳۶ و ۰۳۷ هر دو idempotent هستند؛ در نصب تازه بی‌خطرند).",
        "  6) سرویس Worker غذا را طبق START-HERE.txt / install-food-worker-SERVICE.bat نصب، اجرا و سپس یک‌بار ری‌استارت کنید.",
        "",
        "نکته: این بسته هیچ فایل محرمانه/دیتابیس زنده ندارد؛ مقادیر محیط تولید را خودتان پر کنید.",
        "",
    ]
    return "\n".join(lines)


def build(ref: str) -> tuple[Path, Path]:
    with tempfile.TemporaryDirectory(prefix="full-1.62.95-") as tmp:
        base = Path(tmp)
        source_sha = reconstruct_base(base, ref)
        print(f"درخت مرجع: {ref} → {source_sha}")
        (base / "VERSION").read_text(encoding="utf-8")  # وجودش الزامی است
        version = (base / "VERSION").read_text(encoding="utf-8").strip()
        if version != RELEASE_VERSION:
            raise SystemExit(f"VERSION در {ref} = {version}؛ انتظار {RELEASE_VERSION} می‌رفت.")
        # حذف نشتی خط ارتقای ناتمام — اگر بود، ساخت متوقف می‌شود (هرگز بی‌صدا حذف نمی‌شود).
        check_no_foreign_line(base)
        add_seeds(base)
        add_reports(base, ref)
        (base / NOTE_NAME).write_text(provenance_note(source_sha, ref), encoding="utf-8")

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
                if PREFIX + "seeds/holidays-1405-1406.sql" not in set(names):
                    raise SystemExit("seed تعطیلات داخل بسته نیست.")
                forbidden = [n for n in names if "request-center" in n.lower() or "request_center" in n.lower()]
                if forbidden:
                    raise SystemExit(f"نشانهٔ خط ارتقای ناتمام در نام فایل‌ها: {forbidden[:3]}")
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
    parser = argparse.ArgumentParser(description="ساخت سورس کامل ۱٫۶۲٫۹۵ از یک درخت گیت")
    parser.add_argument("--ref", default="origin/main", help="commit/شاخهٔ مرجع (پیش‌فرض: origin/main)")
    args = parser.parse_args()
    build(args.ref)
