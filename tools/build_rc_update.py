#!/usr/bin/env python3
"""سازندهٔ بستهٔ کاندید ارتقاء مرکز درخواست (۱.۶۲.۹۰ ← ۱.۶۳-کاندید).

پیش‌نیاز هدف (r11، ۲۰۲۶-۱۱-۱۴): نسخهٔ **۱٫۶۲٫۹۶** روی سامانه نصب باشد (۱٫۶۲٫۹۵ متحد + فیکس سر-صفِ غذا)؛
تا r10 پیش‌نیاز ۱٫۶۲٫۹۵ بود. فایل‌های قلمرو ۱٫۶۲٫۹۴/۱٫۶۲٫۹۵/۱٫۶۲٫۹۶ و پیش‌نمایش انتشار غذا عمداً از این بسته حذف می‌شوند.
بازسازی r6 (۲۰۲۶-۱۱-۱۲): پایهٔ diff روی main متحدِ ۱٫۶۲٫۹۵ (نه d30d7dfa) +
جداسازی کامل نقش «مدیر واحدِ صرف» + حذف rebuild-final.sql مخرب از بسته.

تفاوت این بسته با بسته‌های prod: این بستهٔ «کاندید» است؛ انتشار آن (تگ، merge به main
و نصب) فقط با تأیید صریح مالک انجام می‌شود. محتوای آن از تفاوت دقیق main → این شاخه
ساخته می‌شود و مهاجرت فقط ۰۳۵ (request_center_appointments) است — سایر مهاجرت‌ها
(۰۳۲–۰۳۴) متعلق به main/Logging هستند و قبلاً روی هدف اعمال شده‌اند.
بازسازی r7 (۲۰۲۶-۱۱-۱۳): لایهٔ نمایشی سراسریِ اعداد فارسی در کل سامانه
(دیت‌لیجیتس: lib/fa-digits.php + ob_start در bootstrap.php) + انتساب
«معاونت سازمانیِ درخواست‌کننده» به‌عنوان مرز دومِ دید مدیر واحد (۲a0d8ff2) +
تلرانس گارد استاتیک QA overlay (d5c33d7c) + رفع دو نقصِ تشخیصی/بایت‌ایِ لایهٔ
نمایشی و هم‌راستایی آزمون‌های قراردادی (a8e8d014).
بازسازی r8 (۲۰۲۶-۱۱-۱۴): پنج اصلاح رابط/ماژول پس از بازخورد مالک روی r7 —
فشرده‌سازی ردیف برند (حذف دکمهٔ غول‌آسای «بازگشت»)، جایگزینی «خروج» RC با «بازگشت به سامانه»
(لینک خنثی به index.php)، انتقال دکمهٔ «حالت شب» و پیلِ هویتی کاربر به ردیف برندِ هر دو پوسته
(قواعد جدید assets/topbar-chrome.css خارج از قلمرو غذا)، یک‌خطی‌شدن «روزهای تعطیل هفتگی» (شنبه→جمعه)
و تجمیع نهایی governance.php/ola.php در خودِ ماژول (Pages+Actions+lib) با حذف فایل‌های ریشه‌ای.
گیت‌ها: پذیرش پاک ۱۲۰/۱۲۰؛ پوشش دسترسی ۵۴/۵۴؛ صفر رگرسیون pytest نسبت به پایه؛ تنها قرمزی
test_version_source_of_truth بود که با همین بازسازی رفع می‌شود.
بازسازی r9 (۲۰۲۶-۱۱-۱۴): تعیین تکلیف نهاییِ باقی‌ماندهٔ «ثبت درخواست» در سامانهٔ اصلی
(همهٔ مسیرها به ماژول می‌روند؛ پل index.php?page=new-ticket کوئری‌استرینگ را کامل حفظ می‌کند) و
حذف کامل لایهٔ MVC مرده (app/Views/layout/header.php، app/Views/layout/footer.php، routes/web.php —
هیچ فایلی require نمی‌کرد) + هم‌راستایی تست‌های قراردادی/ایستا و مستندات (SYSTEM-MAP-FA/ARCHITECTURE).
بسته‌های ارتقا فایل‌های مقصد را پاک نمی‌کنند: owner باید app/Views/* و routes/web.php را دستی حذف کند.
بازسازی r10 (۲۰۲۶-۱۱-۱۴): صفحهٔ عمومی «تغییرات سامانه» (index.php?page=release-notes؛ فقط ورود، بدون مجوز اضافه)
با محتوا از lib/release-notes.php + کارت «تغییرات این نسخه» در داشبورد (قابل‌بستن با localStorage به‌ازای هر نسخه)
+ لینک «تغییرات سامانه» در فوتر هر دو پوسته برای کاربران واردشده — تصمیم مالک ۲۰۲۶-۱۱-۱۴ («برای عموم آزاد»).
هیچ رفتار/مجوز/مهاجرت دیگری تغییر نکرد.
بازسازی r11 (۲۰۲۶-۱۱-۱۴) — «وابستگی به ۱٫۶۲٫۹۶»: مالک پس از نصب ۱٫۶۲٫۹۶ دستور داد «ورژن ۱٫۶۳ را فردا (۱۴۰۵/۰۸/۲۴) نصب می‌کنم؛
۱٫۶۳ را وابسته به ۱٫۶۲٫۹۶ آماده کن تا در سامانه نصب شود». تنها تغییر قراردادی: REQUIRES_VERSION از ۱٫۶۲٫۹۵ به ۱٫۶۲٫۹۶ (رانر نصب روی نسخهٔ پایین‌تر fail-closed می‌کند)؛
نام بسته به …-1_62_96-to-1_63-…-r11 تغییر کرد؛ محتوای RUNTIME عمداً یکسان با r10 است (هیچ فایل محصول پس از r10 تغییر نکرده جز قلمرو غذا که ۱٫۶۲٫۹۶ آن را جداگانه رسانده)؛
مهاجرت همان ۰۳۵؛ وضعیت RC_CANDIDATE_NOT_FOR_PRODUCTION بی‌تغییر.
بازسازی r12 (۲۰۲۶-۱۱-۱۴) — «آژیر سلامت سامانه» طبق دستور صریح مالک (۱۴۰۵/۰۸/۲۴) به بسته اضافه شد: ماژول هشدار وسط صفحه
برای مدیران در «توقف‌کار»ها (صف بی‌حرکت فیش، پرینتر ناموجود/قطع، dead-letter، پوشه‌های ضروری، heartbeat Worker،
رخدادهای critical لاگینگ، مهاجرت اجرا‌نشده، پایش خودِ دیده‌بانی)؛ بستن با پنجرهٔ «آیا مطمئن هستید؟» + ثبت SYS-ALARM-ACK؛
زمان‌بندی پیش‌چک ۰۸:۰۰ و tick هر ۵ دقیقه (کرون‌های Task Scheduler — جدا ثبت می‌شوند)؛ اعلام فقط داخل سامانه؛
فوتر مشترک (سامانه + مرکز درخواست‌ها)؛ بدون مهاجرت؛ استایل inline (مستقل از assets/style.css که این بسته حمل نمی‌کند)؛
پیش‌نیاز همان ۱٫۶۲٫۹۶؛ مهاجرت همان ۰۳۵.
بازسازی r13 (۲۰۲۶-۱۱-۱۴) — «شناسنامه‌ها» و استخراج اطلاعات بر اساس دستور مالک («حتی یک دونه سیستم هم استخراج نشده… دقیق اجراش کن تستش کن و بررسی کامل رو انجام بده») و اجرای واقعی سامانه در پس‌زمینه تعمیر شد:
(۱) دکمهٔ «استخراج محلی همین سیستم» هرگز در صفحهٔ «شناسنامه‌ها» رندر نمی‌شد (آرگومان هفتم فراخوانی ۱۲آرگومانی index.php یعنی canExtractLocalServer در شاخهٔ legacy سازگاری نادیده گرفته می‌شد و $canExtractLocalServer تعریف‌نشده می‌ماند)؛ اکنون در شاخهٔ legacy به‌صورت canExtractLocalServer تفسیر می‌شود و شاخهٔ ۱۱آرگومانیِ قدیمی بی‌تغییر است؛
(۲) اکشن asset_profile_selftest نتیجهٔ تشخیص و فلش را در نشست بسته می‌نوشت و هر دو بی‌صدا دور ریخته می‌شدند؛ اکنون نشست پیش از نوشتن دوباره باز می‌شود؛
(۳) شمارش «فیلد به‌دست‌آمده» کلیدهای داخلی (_clear_fields/_field_provenance) را می‌شمرد و مقادیر Array را چاپ می‌کرد؛ اکنون حذف شدند.
راستی‌آزمایی: صفحهٔ شناسنامه‌ها ۲۰۰ بدون warning؛ خروجی Excel معتبر؛ استخراج ترکیبی ویندوز (score ۸، ۲۰ فیلد) و عامل کلاینت end-to-end روی PHP 8.2.35 + MariaDB سبز (دادهٔ تست پاک شد)؛ مسیر شبکه/AD فقط روی ویندوز + PowerShell CIM + HTTPS + php_ldap قابل اجراست (گیت‌های HTTP/LDAP روی لینوکس تست شدند؛ بدون ردیف یتیم صف). پیش‌نیاز همان ۱٫۶۲٫۹۶؛ مهاجرت همان ۰۳۵؛ بدون اسکیما/مجوز/نشست جدید.

تصمیم مالک (۲۰۲۶-۱۱-۰۹): این بسته **فقط به‌روزرسانی مرکز درخواست** را حمل می‌کند.
هدف نصب، پیش‌تر تمام بسته‌های غذایی موجود را (تا ۱.۶۲.۹۴ با مهاجرت ۰۳۶ و سپس ۱.۶۲.۹۵ کامل)
اعمال کرده است؛ بنابراین فایل‌های موج غذا و مهاجرت ۰۳۶ عمداً از این بسته حذف می‌شوند.
استثنا: actions/food-order-actions.php (هندلر food_order_cancel_decide) در mainِ متحد
غایب است؛ طبق تصمیم r5 این فایل به‌صراحت حمل می‌شود.
"""

from __future__ import annotations

import hashlib
import json
import subprocess
import zipfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.63"
# ۲۰۲۶-۱۱-۱۴ (r11): مالک ۱٫۶۲٫۹۶ را روی سامانه نصب کرد و ۱٫۶۳ را «وابسته به ۱٫۶۲٫۹۶» می‌خواهد؛
# روی نسخهٔ پایین‌تر نصب باید fail-closed رد شود (system-update.php).
REQUIRES_VERSION = "1.62.96"
# هدفِ ارتقا، "main متحدِ ۱٫۶۲٫۹۵" (00e7e759) است؛ شاخه فقط بالادستِ همین محتواست.
# پایهٔ diff روی همان main است تا موج غذایی/سی‌دی‌دی‌وی‌دیِ ۱٫۶۲٫۹۵ وارد بسته نشود.
# فیکس سر-صفِ غذا (۱٫۶۲٫۹۶) فقط food-ticket-runtime.php را لمس کرد که در EXCLUDED_FOOD_PATHS است؛
# هدف، ۱٫۶۲٫۹۶ را پیشاپیش دارد، پس محتوای RUNTIME بسته یکسان با r10 می‌ماند.
BASE_COMMIT = "00e7e759bda9460dcb245812e2e6b617670efade"
RELEASE_DATE = "2026-11-14"
PACKAGE_NAME = "ticket-update-1_62_96-to-1_63-request-center-rc-candidate-20261114-r13.zip"
REPORT_NAME = f"CHANGE-REPORT-FA-{RELEASE_VERSION}.md"
ZIP_DATE = (2026, 11, 14, 22, 0, 0)

EXCLUDED_PREFIXES = ("downloads/", "updates/", "tests/", "docs/system-map/", ".github/", "ci-diagnostics/", "benchmark-results/", "preview/")
MIGRATIONS = [
    "035-request-center-appointments.sql",
]
EXCLUDED_FILES = {
    "tools/build_rc_update.py",
    "tools/build_cumulative_update.py",
    "tools/build_update_1_62_88.py",
    # ابزار کمکی محلیِ بررسی نحوی (برای محیط توسعه؛ بخشی از محصول نیست)
    "tools/php_syntax_check.py",
    # ابزار توسعه/مستندسازی نقشهٔ سامانه؛ بخشی از رفتار RUNTIME نیست (هدف ارتقا نسخهٔ main را دارد).
    "tools/build_system_map.py",
    # ابزارهای بیلد/انتشار موج‌های غذا و سی‌دی‌دی‌وی‌دی که فقط در تاریخ شاخه وجود دارند؛ RUNTIME نیستند.
    "tools/build_1_62_95_update.py",
    "tools/build_full_1_62_94.py",
    "tools/build_full_1_62_95.py",
    "tools/Run-Logging-Acceptance-1.62.88.ps1",
    # اسکریپتِ بازسازی مخرب دیتابیس؛ با تصمیم مالک به docs/architecture منتقل شد و
    # در هیچ بستهٔ ارتقاء نباید حمل شود (نصب عادی آن را لازم ندارد).
    "rebuild-final.sql",
    "docs/architecture/rebuild-final.sql",
    # ابزارهای راه‌اندازی/توسعهٔ محلی؛ پیش‌نیاز استقرار روی دستگاه توسعه‌دهنده‌اند و
    # بخشی از مسیر ارتقای یک سامانهٔ موجود نیستند.
    "tools/local-run.sh",
    "docs/architecture/REQUEST-CENTER-LOCAL-RUN-FA.md",
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
    # استثناءِ dقیدی: food_order_cancel_decide در mainِ متحد هم غایب است ولی index.php
    # آن را require می‌کند؛ r4 به همین خاطر با ۵۰۳ می‌افتاد. r5 آن را حمل کرد؛ r6 هم حمل می‌کند.
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

    manifest = {
        "name": "request-center-rc-candidate",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "کاندید بستهٔ ارتقاء مرکز درخواست — ماژول مستقل + گردش‌کار + نوبت‌ها و تقویم شمسی + جداسازی نقش مدیر واحدِ صرف + لایهٔ اعداد فارسیِ سراسری + اصلاحات رابط r8 + حذف لایهٔ MVC مرده r9 + صفحهٔ عمومی «تغییرات سامانه» r10 + وابستگی به ۱٫۶۲٫۹۶ r11 + آژیر سلامت سامانه r12 + تعمیر «شناسنامه‌ها» و استخراج اطلاعات r13 (۱.۶۲.۹۶ ← ۱.۶۳) — r13",
        "requires_version": REQUIRES_VERSION,
        "migrations": list(MIGRATIONS),
        "files": sorted(files),
        "sha256": {name: sha256(files[name]) for name in sorted(files)},
        "release_status": "RC_CANDIDATE_NOT_FOR_PRODUCTION",
        "architecture_reference": "docs/architecture/REQUEST-CENTER-ARCHITECTURE.md",
        "description": "r13 (۲۰۲۶-۱۱-۱۴): «شناسنامه‌ها» و استخراج اطلاعات طبق دستور مالک تعمیر شد — (۱) دکمهٔ «استخراج محلی همین سیستم» در صفحهٔ شناسنامه‌ها هرگز رندر نمی‌شد (آرگومان هفتم canExtractLocalServer در شاخهٔ legacy سازگاری نادیده گرفته می‌شد)؛ اکنون شاخهٔ legacy آن را به‌صورت canExtractLocalServer تفسیر می‌کند و callerهای ۱۱آرگومانیِ قدیمی بی‌تغییرند؛ (۲) اکشن asset_profile_selftest گزارش تشخیص و فلش را در نشست بسته می‌نوشت و هر دو بی‌صدا دور ریخته می‌شدند؛ اکنون نشست پیش از نوشتن دوباره باز می‌شود (صفحهٔ «تست استخراج شناسنامه» نتیجه را نشان می‌دهد — راستی‌آزمایی شد: ۹ فیلد)؛ (۳) شمارش «فیلد به‌دست‌آمده» کلیدهای داخلی (_clear_fields/_field_provenance) را می‌شمرد و Array چاپ می‌کرد؛ حذف شدند. راستی‌آزمایی: صفحهٔ شناسنامه‌ها ۲۰۰ بدون warning، خروجی Excel معتبر، upsert+apply با امتیاز ۸ و ۲۰ فیلدِ ویندوز و عامل کلاینت end-to-end سبز (دادهٔ تست پاک شد)؛ مسیرهای شبکه/AD فقط روی ویندوز + PowerShell CIM + HTTPS + php_ldap قابل اجراست (گیت‌های HTTP/LDAP تست شدند، بدون ردیف یتیم صف). پیش‌نیاز همان ۱٫۶۲٫۹۶؛ مهاجرت همان ۰۳۵؛ بدون اسکیما/مجوز/نشست جدید. — r12 (۲۰۲۶-۱۱-۱۴): «آژیر سلامت سامانه» طبق دستور صریح مالک (۱۴۰۵/۰۸/۲۴، پس از رخداد خاموشی چاپ فیش) به بسته اضافه شد — مدال وسط صفحه فقط برای نقش‌های مدیریتی (system_alarm_roles) در ۱۱ «توقف‌کار» (صف بی‌حرکت فیش، پرینتر قطع/پیکربندی‌نشده با تست اتصال TCP فقط در کرون، dead-letter امروز، پوشه‌های ضروری، heartbeat Worker غذا، رخدادهای critical باز لاگینگ، هشدارهای active سلامت، مهاجرت اجرا‌نشده، و پایش خودِ کرون دیده‌بانی watch_cron_stale)؛ بستن = پنجرهٔ «آیا مطمئن هستید؟» + ثبت SYS-ALARM-ACK در system_logs (علت بازنویسی نمی‌شود؛ ۶۰ دقیقه ساکت می‌شود؛ با رفع علت خودبه‌خود می‌رود)؛ زمان‌بندی پیش‌چک ۰۸:۰۰ و tick هر ۵ دقیقه از طریق cron/system_alarm_tick.php (Task Scheduler ویندوز — جداگانه ثبت می‌شود؛ اعلام فقط داخل سامانه)؛ مسیر وب فقط‌خواندنی با کش فایل ۶۰ ثانیه و صفر socket؛ تحویل در فوتر مشترک (سامانه + مرکز درخواست‌ها)؛ استایل inline مستقل از assets/style.css (که این بسته عمداً حمل نمی‌کند)؛ بدون مهاجرت؛ بدون تغییر مسیر چاپ/ثبت فیش؛ پیش‌نیاز همان ۱٫۶۲٫۹۶. مستند کامل: docs/SYSTEM-ALARM-FA.md. — r11 (۲۰۲۶-۱۱-۱۴): باز-انتشار همان کاندیدِ r10 با پیش‌نیاز ۱٫۶۲٫۹۶ — مالک ۱٫۶۲٫۹۶ را روی سامانه نصب کرد و ۱٫۶۳ را «وابسته به ۱٫۶۲٫۹۶» خواست؛ system-update.php روی نسخهٔ پایین‌تر fail-closed می‌کند. هیچ فایل RUNTIME نسبت به r10 تغییر نکرده و مهاجرت همان ۰۳۵ است؛ تنها افزوده‌های بسته نسبت به r10 دو ابزار تشخیصیِ فقط‌خواندنی هستند (tools/diagnose_food_full.php v2 و tools/diagnose_food_worker.php، اسکن/شبیه‌سازی روی سرور، خارج از runtime سامانه) که پس از r10 وارد شاخه شده‌اند. (قلمرو غذا عمداً خارج از بسته؛ فیکس سر-صفِ ۱٫۶۲٫۹۶ توسط بستهٔ ۱٫۶۲٫۹۶ رسیده است). — r10 (۲۰۲۶-۱۱-۱۴): شمار تغییرات این نسخه برای همهٔ کاربران قابل‌مشاهده شد — صفحهٔ index.php?page=release-notes (فقط ورود لازم است؛ بدون مجوز اضافه) با محتوا از lib/release-notes.php (کلید=VERSION؛ متن ۱.۶۳ غیر فنی: چه عوض شد/چه درست شد)؛ کارت «تغییرات این نسخه» بالای داشبورد اصلی (۴ تغییر کلیدی + لینک «همهٔ تغییرات این نسخه») با بستن قابل‌حفظ در localStorage به‌ازای هر نسخه؛ لینک «تغییرات سامانه» در فوتر هر دو پوسته (اصلی و مرکز درخواست‌ها) برای کاربران واردشده؛ بدون هیچ تغییر رفتاری/مجوزی/مهاجرتی دیگر. — r9 (۲۰۲۶-۱۱-۱۴): تعیین تکلیف باقی‌مانده‌های «ثبت درخواست» در سامانهٔ اصلی — فرم/هندلر ساخت تیکت از قبل فقط در ماژول RC بود (Pages/create.php + Actions/ticket-actions.php)؛ همهٔ لینک‌های هسته (داشبورد، جست‌وجو، cd-dvd «درخواست اصلاح»، ماژول) به request-center.php?page=new-ticket می‌روند و پل index.php با حفظ کامل کوئری‌استرینگ باقی است؛ chatbot کاربر را به مرکز درخواست‌ها ارجاع می‌دهد؛ برچسب‌های new-ticket در admin-layout-design/system-health/settings-effects فقط کلیدهای نمایشی سازگاری‌اند. لایهٔ MVC مرده (app/Views/layout/header.php، app/Views/layout/footer.php، routes/web.php) کاملاً حذف شد؛ pytest پس از حذف: صفر شکست جدید نسبت به پایه (همهٔ قرمزی‌ها دقیقاً همانِ پایه‌اند؛ test_version_source_of_truth سبز ماند)؛ build_system_map --check PASS. توجه: بسته فایل‌های مقصد را پاک نمی‌کند — پس از ارتقا app/Views/* و routes/web.php را دستی حذف کنید (در کنار governance.php و ola.php ریشه‌ای r8). — r8 (۲۰۲۶-۱۱-۱۴): پنج اصلاح رابط/ماژول پس از بازخورد مالک روی r7 — فشرده‌سازی ردیف برند (حذف دکمهٔ غول‌آسای «بازگشت»)، جایگزینی «خروج» RC با «بازگشت به سامانه» (لینک خنثی به index.php)، انتقال دکمهٔ «حالت شب» و پیلِ هویتی کاربر به ردیف برندِ هر دو پوسته (قواعد جدید assets/topbar-chrome.css خارج از قلمرو غذا)، یک‌خطی‌شدن «روزهای تعطیل هفتگی» (شنبه→جمعه) و تجمیع نهایی governance.php/ola.php در خودِ ماژول (Pages+Actions+lib) با حذف فایل‌های ریشه‌ای؛ راستی‌آزمایی: پذیرش پاک ۱۲۰/۱۲۰، پوشش دسترسی ۵۴/۵۴، صفر رگرسیون pytest نسبت به پایه. Request Center module upgrade candidate from 1.62.96 to 1.63. Install only after owner sign-off; requires migration 035 via the official runner. Migration 034 is owned by release 1.62.89 (already installed) and is NOT part of this package. By owner decisions (2026-11-09, 2026-11-11 and 2026-11-12) this package is RC-only: food/1.62.94/1.62.95 files, the release preview harness (preview/) and migration 036 are intentionally excluded; the target must already have the food updates (through 1.62.94 + migration 036, then 1.62.95) applied. r5 (2026-11-12): افزودن actions/food-order-actions.php که index.php آن را require می‌کند ولی در r4 غایب بود. r6 (2026-11-12): جداسازی کامل نقش «مدیر واحدِ صرف» (رئیس چارت بدون عاملیت کارشناسی/پرسنل پشتیبانی): دامنهٔ دید فقط چارت (حذف فیلتر service_group و حذف «ارجاع‌شده‌های به‌خودش»)، بدون اختیار کارشناسی (نه برداشتن درخواست، نه شروع پاسخ اول، نه تغییر وضعیت/موعد، نه تقویم=۴۰۳، نه bulk، نه انتقال)، اصلاح عنوان/توضیح/اولویت روی درخواستِ بازِ هر دو وضعیت ارجاع‌شده و نشده با مسیر اختصاصی unit_head_ticket_update (حسابرس + رویداد + اعلان)، ارجاع فقط تا پیش از تخصیص (پس از آن قفل)، «درخواست لغو» برای زیرمجموعه (هرگز لغو مستقیم)، تصمیم لغو فقط manager/assignee یا سوپروایزر؛ rebuild-final.sql مخرب از بسته حذف شد. r7 (۲۰۲۶-۱۱-۱۳): لایهٔ نمایشی سراسریِ اعداد فارسی در کل سامانه (lib/fa-digits.php + ثبت ob_start در bootstrap.php): فقط «متن نمایشیِ داخل تگ‌ها» تبدیل می‌شود؛ اتربیوت‌ها، فرم‌ها، اسکریپت/استایل/pre/code/textarea و کامنت‌ها دست‌نخورده‌اند؛ IP و ایمیل لاتین می‌مانند؛ فقط Content-Type: text/html یا application/xhtml+xml (یا نبودِ Content-Type — صفحات PHP) تبدیل می‌شود، پس JSON، XLSX، text/plain و همهٔ دانلودها بایت‌به‌بایت عبور می‌کنند؛ راه برگشت اضطراری: تنظیم display.fa_digits=0. علت ریشه‌ایِ گزارش مالک («همه اعداد لاتین است») نبودِ هرگونه لایهٔ یکپارچهٔ اعداد در خروجی بود (اعداد با sprintf/number_format/echo لاتین چاپ می‌شدند و فقط تقویم/برنامهٔ غذا دستی تبدیل می‌کردند). همین نسخه: انتساب «معاونت سازمانیِ درخواست‌کننده» به‌عنوان مرز دومِ نمایش مدیر واحد (۲a0d8ff2)، تلرانس گارد استاتیک QA overlay برای permissions.php (d5c33d7c) و هم‌راستایی آزمون‌های قراردادیِ ارقام‌محور (a8e8d014). CI روی a8e8d014: Contract سبز (MariaDB 10.4.32 و 10.11)، T12 سبز (۲۰ سناریو)، QA Package Preview سبز؛ Logging contract قرمزِ ازپیش‌موجود (قلمرو D3)؛ pytest با HEAD پایه یک‌به‌یک (۷۸ شکست ازپیش‌موجود، صفر رگرسیون)؛ پذیرش نصب پاک ۱۲۰/۱۲۰.",
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
