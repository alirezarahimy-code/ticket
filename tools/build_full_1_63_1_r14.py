#!/usr/bin/env python3
"""سازندهٔ «سورس کامل نصبی ۱.۶۳.۱ — r14» — بسته‌ای که برای **نصب از صفر** کافی است.

روش ساخت (بازتولیدپذیر، بی‌واگرایی) — روی زنجیرهٔ r13 با یک لایهٔ تازه:

  ۱) درخت گیت در `BASE_COMMIT` (= main، آخرین نسخهٔ ۱.۶۲.۹۵) استخراج می‌شود.
  ۲) محتوای `files/` بستهٔ r16 (۱.۶۳) روی آن می‌نشیند.
  ۳) محتوای `files/` بستهٔ ۱.۶۳.۱ (r13) روی آن می‌نشیند.
  ۴) **لایهٔ تازه (r14):** فقط فایل‌هایی که پس از r13 در همین درخت عوض شده‌اند،
     از working tree روی نتیجه می‌نشینند. یعنی دقیقاً همان کد آزموده‌شده.
  ۵) `VERSION` = 1.63.1 و یادداشت provenance.

چرا این لایه و نه یک بستهٔ جدا؟ چون کاربر خواست:

> «تمام این موارد اصلاحی رو اضافه کن به بسته ۱.۶۳.۱»

پس بستهٔ کامل ۱.۶۳.۱ حالا همهٔ این‌ها را دارد:
  • انتخاب‌گر «شخص واردکننده» (چندحرفی + جست‌وجو در نام/نام خانوادگی/نام کاربری/واحد)
  • «نمایش بر اساس معاونت» در چهار گزارش (فقط عنوان ریشه، بدون ادارهٔ میانی)
  • تفکیک غذای هر معاونت
  • اصلاح سه ایراد استخراج شناسنامه (کلید یکتا، نام کامپیوتر، سریال/مدل/کیس)

مهاجرت: **هیچ فایل SQL تازه‌ای لازم نیست.** ستون‌های تازهٔ شناسنامه
(`machine_serial`, `machine_model`) و ستون تازهٔ `asset_profiles` را همان
`asset_profile_ensure_schema()` با `CREATE TABLE IF NOT EXISTS` / `ALTER TABLE
ADD COLUMN` می‌سازد که در اولین بازکردن صفحه اجرا می‌شود — همان الگویی که از
۱.۶۲.۲۶ برای همهٔ فیلدهای شناسنامه به کار رفته است. اگر جایی ستون از قبل باشد،
`information_schema` می‌بیند و دوباره اضافه نمی‌کند (بی‌خطر برای اجرای مکرر).

اجرا:
  python3 tools/build_full_1_63_1_r14.py
"""

from __future__ import annotations

import hashlib
import subprocess
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
BASE_COMMIT = "00e7e759bda9460dcb245812e2e6b617670efade"  # main — ۱.۶۲.۹۵
DELTA_163 = ROOT / "downloads" / "ticket-update-1_62_96-to-1_63-request-center-rc-candidate-20261114-r16.zip"
DELTA_1631 = ROOT / "downloads" / "ticket-update-1.63-to-1.63.1-r13-cancel-fix-20261115.zip"

RELEASE_VERSION = "1.63.1"
PACKAGE_NAME = "ticket-full-1.63.1-r14-20261115.zip"
NOTE_NAME = "00-FULL-PACKAGE-1.63.1-r14-FA.txt"
ZIP_DATE = (2026, 11, 6, 4, 30, 0)
PREFIX = "ticket/"

# لایهٔ r14 — همین فایل‌ها پس از r13 در درخت عوض شده‌اند.
R14_FILES = [
    "asset-names.php",
    "asset-profile.php",
    "assets/cd-dvd.js",
    "assets/food-menu.css",
    "assets/food-menu.js",
    "assets/food-org-groups.js",
    "assets/style.css",
    "cd-dvd.php",
    "domain-scan.php",
    "food-order.php",
    "food-ticket-web/assets/food-menu.css",
    "food-ticket-web/assets/food-menu.js",
    "food-ticket-web/assets/food-org-groups.js",
    "food-ticket-web/index.html",
    "food-ticket.php",
    "lib/org-grouping.php",
    "tools/test_asset_extraction_offline.php",
    "tools/test_cd_dvd_user_picker_offline.php",
    "tools/test_food_org_groups_offline.php",
]

# این‌ها هرگز نباید در بستهٔ نصبی باشند: خروجی ابزار، نه سورس.
EXCLUDED_PREFIXES = (
    "downloads/", "updates/", "patches/", "preview/",
    "docs/system-map/", "ci-diagnostics/", "benchmark-results/",
    ".github/", "node_modules/", "vendor/",
)
EXCLUDED_SUFFIXES = (".zip", ".sha256")


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def git(*args: str) -> bytes:
    return subprocess.run(["git", *args], cwd=ROOT, check=True, capture_output=True).stdout


def excluded(name: str) -> bool:
    if name.endswith(EXCLUDED_SUFFIXES):
        return True
    return any(name.startswith(prefix) for prefix in EXCLUDED_PREFIXES)


def main() -> None:
    for required in (DELTA_163, DELTA_1631):
        if not required.is_file():
            raise SystemExit(f"بستهٔ پایه پیدا نشد: {required.name}")

    # ── ۱) درخت پایه از گیت
    listing = git("ls-tree", "-r", "-z", "--name-only", BASE_COMMIT).split(b"\0")
    content: dict[str, bytes] = {}
    for raw in listing:
        name = raw.decode("utf-8")
        if not name or excluded(name):
            continue
        content[name] = git("show", f"{BASE_COMMIT}:{name}")

    # ── ۲ و ۳) لایه‌های منتشرشده
    for layer in (DELTA_163, DELTA_1631):
        with zipfile.ZipFile(layer) as archive:
            for info in archive.infolist():
                if info.is_dir() or not info.filename.startswith("files/"):
                    continue
                rel = info.filename[len("files/"):]
                if excluded(rel):
                    continue
                content[rel] = archive.read(info)

    # ── ۴) لایهٔ r14 از working tree (همان کدی که آزمون‌ها دیدند)
    for rel in R14_FILES:
        path = ROOT / rel
        if not path.is_file():
            raise SystemExit(f"فایل لایهٔ r14 در درخت نیست: {rel}")
        content[rel] = path.read_bytes()

    # ── ۴.۵) ابزارها و آزمون‌های مخزن از working tree — **بعد از** لایه‌ها.
    # چرا بعد و نه قبل: بستهٔ r16 خودش `tools/` دارد و اگر ابزارها را قبل از آن
    # بریزیم، نسخهٔ کهنهٔ r16 روی نسخهٔ به‌روز می‌نشیند. ترتیب مهم است.
    # (همان منطق build_full_1_63_1.py — وگرنه ابزارهایی که بعد از BASE_COMMIT
    #  اضافه شده‌اند، مثل tools/build_1_62_95_update.py، از بسته می‌افتند.)
    for sub in ("tools", "tests"):
        for path in sorted((ROOT / sub).rglob("*")):
            if not path.is_file():
                continue
            rel = path.relative_to(ROOT).as_posix()
            if excluded(rel) or "__pycache__" in rel or rel.endswith(".pyc"):
                continue
            content[rel] = path.read_bytes()

    # ── ۵) VERSION و یادداشت
    content["VERSION"] = (RELEASE_VERSION + "\n").encode("utf-8")
    note = f"""بستهٔ سورس کامل ۱.۶۳.۱ — r14
تاریخ ساخت: ۱۴۰۵/۰۸/۱۵

این بسته = main + ۱.۶۳ + ۱.۶۳.۱ (r13) + لایهٔ r14.

لایهٔ r14 چه چیزی اضافه می‌کند:
  ۱) کنترل CD/DVD — «شخص واردکننده»: چند حرف تایپ می‌شود، جست‌وجو در نام،
     نام خانوادگی، نام کاربری و واحد (نه فقط ابتدای متن).
  ۲) چهار گزارش غذا — دکمهٔ «نمایش بر اساس معاونت»: فقط عنوان معاونت، بعد
     فهرست اعضای همان معاونت؛ بدون ادارهٔ میانی. تفکیک غذای هر معاونت هم
     بالای فهرست می‌آید.
  ۳) شناسنامه — سه ایراد استخراج: برخورد کلید یکتا، بازنویسی نام کامپیوتر،
     و نبود فیلد سریال/مدل/کیس دستگاه.

مهاجرت: ندارد. ستون‌های تازه با asset_profile_ensure_schema() در اولین
بازکردن صفحهٔ شناسنامه ساخته می‌شوند (CREATE TABLE IF NOT EXISTS + ALTER TABLE
ADD COLUMN با بررسی information_schema — اجرای مکرر بی‌خطر است).

فایل‌های آزمون همراه بسته هستند:
  tools/test_cd_dvd_user_picker_offline.php
  tools/test_food_org_groups_offline.php
  tools/test_asset_extraction_offline.php
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

    digest = hashlib.sha256()
    with tmp_path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    checksum = digest.hexdigest()
    size = tmp_path.stat().st_size
    tmp_path.replace(package_path)
    (out_dir / (PACKAGE_NAME + ".sha256")).write_text(
        f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii"
    )

    print(f"OK {PACKAGE_NAME}")
    print(f"Files: {len(content)} | bytes: {size}")
    print(f"SHA-256: {checksum}")
    missing = [rel for rel in R14_FILES if rel not in content]
    if missing:
        raise SystemExit("لایهٔ r14 ناقص است: " + ", ".join(missing))


if __name__ == "__main__":
    main()
