#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""سازندهٔ «سورس کامل نصبی ۱٫۶۳٫۳» — برای **نصب از صفر**.

چرا این لایه؟ چون سورس برنامه از ۱.۶۲.x تا ۱.۶۳.۳ را با همهٔ به‌روزرسانی‌ها
(بغیر از فایل‌های خودبخودتولیدشده) یکجا می‌خواهد. این بسته دقیقاً همان درخت
گیتِ `BASE_COMMIT` است (بدون هیچ تغییر دستی):

  • همهٔ اصلاحات تا ۱٫۶۳٫۳ (بازسازی‌های اولیه تا ششم) داخل خودشان هستند؛
    از جمله اعداد فارسی، پنل ۱٫۳۷٫۶۱، و schema.sql کامل (شامل food_guest_actuals
    و ردیف‌های schema_migrations برای ۰۳۸/۰۳۹/۰۴۰).
  • فقط ابزارها و آزمون‌ها از working tree هم overlay می‌شوند تا ابزارهایی که
    هم‌زمان با همین انتشار اضافه شده‌اند در بسته باشند (همان الگوی r14).

فایل‌های خروجی:
  downloads/ticket-full-1.63.3-20261120-r2.zip
  downloads/ticket-full-1.63.3-20261120-r2.zip.sha256

اجرا:
  python3 tools/build_full_1_63_3.py
"""

from __future__ import annotations

import hashlib
import subprocess
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
BASE_COMMIT = "8261df2e6c8419c37167ab5b86c6c7eb8c32f5b6"  # arena/dacdd8a7-ticket @ ۱٫۶۳٫۳ (بازسازی هفتم)

RELEASE_VERSION = "1.63.3"
PACKAGE_NAME = "ticket-full-1.63.3-20261120-r2.zip"
NOTE_NAME = "00-FULL-PACKAGE-1.63.3-FA.txt"
ZIP_DATE = (2026, 11, 20, 12, 0, 0)
PREFIX = "ticket/"

# هرگز در بستهٔ نصبی نیستند: خروجی ابزار یا فایل انتشار، نه سورس برنامه.
EXCLUDED_PREFIXES = (
    "downloads/", "updates/", "patches/", "preview/",
    "docs/system-map/", "ci-diagnostics/", "benchmark-results/",
    ".github/", "node_modules/", "vendor/",
)
EXCLUDED_SUFFIXES = (".zip", ".sha256")


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def git(*args: str) -> bytes:
    return subprocess.run(["git", *args], cwd=ROOT, check=True, capture_output=True).stdout


def excluded(name: str) -> bool:
    if name.endswith(EXCLUDED_SUFFIXES):
        return True
    return any(name.startswith(prefix) for prefix in EXCLUDED_PREFIXES)


def main() -> None:
    # ── ۱) درخت کامل از گیت در BASE_COMMIT
    listing = git("ls-tree", "-r", "-z", "--name-only", BASE_COMMIT).split(b"\0")
    content: dict[str, bytes] = {}
    for raw in listing:
        name = raw.decode("utf-8")
        if not name or excluded(name):
            continue
        content[name] = git("show", f"{BASE_COMMIT}:{name}")

    # ── ۲) ابزارها و آزمون‌ها از working tree (بعد از درخت پایه)
    for sub in ("tools", "tests"):
        for path in sorted((ROOT / sub).rglob("*")):
            if not path.is_file():
                continue
            rel = path.relative_to(ROOT).as_posix()
            if excluded(rel) or "__pycache__" in rel or rel.endswith(".pyc"):
                continue
            content[rel] = path.read_bytes()

    # ── ۳) VERSION و یادداشت نصب تازه
    content["VERSION"] = (RELEASE_VERSION + "\n").encode("utf-8")
    note = """بستهٔ سورس کامل ۱٫۶۳٫۳ — نصب از صفر
تاریخ ساخت: ۱۴۰۵/۰۸/۲۹ (2026-11-20)

این بستهٔ کاملِ برنامه است؛ شامل همهٔ اصلاحات تا ۱٫۶۳٫۳ (بازسازی‌های اولیه تا هفتم — گزارش کامل ۱٫۶۳٫۳ و پاپ‌آپ یک‌بار rev-scoped):
  • تب «غایبین ناهار» + scope بند ۱۷ + گزارش یک‌بار به‌روزرسانی (پاپ‌آپ rev-scoped برای هر بسته)
  • اعداد دائماً فارسی (نمایش، تایپ — حتی با کیبورد لاتین — و خروجی Excel)؛
    ذخیرهٔ canonical لاتین می‌ماند؛ IP/ایمیل/نام هاست لاتین می‌مانند
  • چینش رابط (کارکنان، گزارش تجمیعی/غذای تحویلی/آمار سفارشها/گزارش‌گیری، غایبین ناهار)
  • کارت «ریست شناسنامه‌ها» فقط در تب «عمومی» تنظیمات
  • نسخهٔ پنل: ۱٫۳۷٫۶۱ · کش دارایی‌ها: style.css=3.4.63 / app.js=3.4.30
  • گزارش کامل به‌روزرسانی ۱٫۶۳٫۳ + راهنمای به‌روزشده (آریا، چت‌بات، همهٔ موارد ۱٫۶۳٫۳ را می‌داند)

نصب (روی ویندوز/XAMPP):
  ۱) پوشهٔ برنامه را حذف یا به ticket_old تغییر نام دهید (config.php و installed.lock همراه آن می‌روند).
  ۲) این ZIP را داخل htdocs استخراج کنید تا مسیر ticket\\index.php ساخته شود.
  ۳) Apache و MySQL را روشن کنید و از روی خود سرور باز کنید: http://localhost/ticket/install.php
     - اطلاعات MySQL را بدهید؛ schema.sql همهٔ جدول‌ها (۸۴ جدول، شامل food_guest_actuals
       و ردیف‌های مهاجرت ۰۳۸/۰۳۹/۰۴۰) را کامل می‌سازد؛ هیچ فایل upgrade-*.sql لازم نیست.
     - مراحل بعد (LDAP/AD، مدیر سامانه) را کامل کنید و «ساخت سامانه و ورود» را بزنید.
  ۴) بعد از ورود: تنظیمات ← نگاشت گروه‌های AD و «همگام‌سازی همه کاربران دامین»
     (بدون آن فیش چاپ نمی‌شود؛ کد پرسنلی از همین همگام‌سازی می‌آید).
  ۵) «سامانه مدیریت غذا» ← تنظیمات اتصال (مسیر فایل منبع تردد و رمز، مسیر سفارش غذا،
     چاپگر tcp_raw آی‌پی:9100) ← تیک «فعال» ← «بررسی اتصال».
  ۶) Worker همیشه‌روشن: install-food-worker-SERVICE.bat را Run as administrator اجرا کنید.
  ۷) Ctrl+F5 بزنید (الزامی؛ وگرنه فایل‌های قدیمی از کش مرورگر می‌آیند).

نکته‌ها:
  • فایل config.php و داده‌های storage/ عمداً در بسته نیستند (اسرار و داده‌های واقعی).
  • برای نصب تازه هیچ بستهٔ مهاجرتی لازم نیست؛ schema.sql کامل است.
  • برای ترتیب کامل و هشدارهای امنیتی (حذف کل دیتابیس) راهنمای ریشه: 00_CLEAN_INSTALL_FA.txt
    و راهنمای تازه‌نصب اسکیما: FRESH-INSTALL-SCHEMA-1.62.82-UPDATE-NOTE-fa.txt
  • فایل‌های آزمون همراه بسته هستند (tests/ و tools/).
"""
    content[NOTE_NAME] = note.encode("utf-8")

    out_dir = ROOT / "downloads"
    out_dir.mkdir(exist_ok=True)
    package_path = out_dir / PACKAGE_NAME
    tmp_path = out_dir / (PACKAGE_NAME + ".building")

    with zipfile.ZipFile(tmp_path, "w", zipfile.ZIP_DEFLATED) as zf:
        for name in sorted(content):
            info = zipfile.ZipInfo(PREFIX + name, date_time=ZIP_DATE)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o644 << 16
            zf.writestr(info, content[name])

    checksum = sha256_file(tmp_path)
    size = tmp_path.stat().st_size
    tmp_path.replace(package_path)
    (out_dir / (PACKAGE_NAME + ".sha256")).write_text(
        f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii"
    )

    print(f"OK {PACKAGE_NAME}")
    print(f"Files: {len(content)} | bytes: {size}")
    print(f"SHA-256: {checksum}")


if __name__ == "__main__":
    main()
