#!/usr/bin/env python3
"""سازندهٔ بستهٔ ۱.۶۳.۱ — چهار اصلاح روی خط ۱.۶۳.

خط ۱.۶۲ بسته شد. این بسته، همان چهار اصلاحی که در ۱.۶۲.۹۸ (لغوشده) بود را
**برای درخت ۱.۶۳** می‌رساند، به‌همراه قاعدهٔ تازهٔ «نقش اعطایی»:

۱) مسئول تیکت فقط از فهرست «کارشناس آی تی» / «کارشناس پشتیبانی» خوانده می‌شود
   (۱.۶۳ آن را نداشت: `choose_handling_agent` همچنان شرط primary_admin داشت و
   فهرست کارشناس پشتیبانی اصلاً وجود نداشت).

۲) `ticket_assignee_is_eligible()` با `callable|string|null` — در ۱.۶۳ امضایش
   `?callable` بود و هر چهار فراخوان در index.php رشته می‌فرستند ⇒ TypeError
   مرگبار روی strict_types.

۳) سه هشدار سلامت خودشان را درمان می‌کنند (پوشه‌های storage، ثبت مهاجرت‌های
   baseline، ثبت یک‌کلیکی کرون). در ۱.۶۳ این‌ها در lib/system-alarms.php هستند.

۴) نقش اعطایی، نه تبدیل نقش: کارشناس IT دیگر primary_admin نمی‌شود، سوپروایز
   دیگر نقش را عوض نمی‌کند، و کاربر می‌تواند هم‌زمان کارشناس IT و پشتیبانی باشد.

بدون مهاجرت، بدون تغییر جدول، بدون مجوز تازه.
"""

from __future__ import annotations

import hashlib
import json
import re
import subprocess
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.63.1"
REQUIRES_VERSION = "1.63"
RELEASE_DATE = "2026-11-06"
PACKAGE_NAME = "ticket-update-1.63-to-1.63.1-r17-smb-extract-20261115.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.63.1-r17.md"
ZIP_DATE = (2026, 11, 6, 14, 0, 0)

# ── پایهٔ نصب: ۱.۶۳ ──
#
# نصبِ هدف = main (00e7e759، نسخهٔ ۱.۶۲.۹۵) + بستهٔ r16. برای محاسبهٔ دقیق
# کدام فایل باید حمل شود، همین دو را روی هم می‌گذاریم و با درخت فعلی می‌سنجیم.
BASE_COMMIT = "00e7e759bda9460dcb245812e2e6b617670efade"
BASE_PACKAGE = ROOT / "downloads" / "ticket-update-1_62_96-to-1_63-request-center-rc-candidate-20261114-r16.zip"

# ── فایل‌هایی که این بسته حمل می‌کند ──
#
# فهرست دستی است، نه diff خودکار: بستهٔ افزایشی نباید فایل غیرمرتبط حمل کند.
# هر ردیف با دلیل.
FILES = [
    # ۱) قواعد نقش و دسترسی
    "permissions.php",                          # user_can: سوپروایز اعطایی
    "actions/role-actions.php",                 # پایان تبدیل نقش (IT + سوپروایز)
    "lib/settings-effects.php",                 # نقش کلیدی چهارم: support_expert
    "index.php",                                # متن راهنمای نقش‌های کلیدی + کارت چهارم

    # ۲) مسیر ارجاع و دیدن تیکت
    "modules/request-center/lib/functions.php",  # choose_handling_agent از فهرست، حذف لایهٔ ادمین
    "lib/auth-scope.php",                        # callable|string|null + هر دو حوزه + گروه تیکت

    # ۲.۵) ارجاع دستی: حوزه را فهرست تعیین می‌کند، نه نقش ادمین اصلی
    "modules/request-center/Actions/ticket-actions.php",

    # ۳) درمان خودکار آژیر (خط ۱.۶۳)
    "lib/system-alarms.php",                     # ساخت پوشه‌ها + backfill مهاجرت‌ها
    "cron/register_system_alarm_tasks.bat",      # ثبت یک‌کلیکی کرون

    # ۴) یادداشت نسخه (پاپ‌آپ «تغییرات این نسخه» + تاریخچه در راهنما)
    "lib/release-notes.php",

    # ۵) ابزار یک‌بارمصرف: اصلاح ارجاع‌های قدیمی به ادمین‌های اصلی
    "tools/fix_ticket_assignments.php",

    # ۶) ثبت خودکار فیش روزهای گذشته (شام هر روز + ناهار تعطیل‌ها)
    # پنل یک کارت تازه در «اتصال پایگاه‌ها» می‌گیرد و یک مسیر API تازه؛
    # ابزار CLI هم برای کسانی که از خط فرمان راحت‌ترند همراه است.
    "food-ticket.php",                          # مسیر backfill-auto-receipts + گارد مجوز
    "food-ticket-order-source.php",             # نقشهٔ سفارش وعده‌آگاه (ناهار + شام)
    "food-ticket-web/index.html",               # کارت «ثبت خودکار فیش روزهای گذشته»
    "tools/backfill_food_auto_receipts.php",    # همان موتور، از خط فرمان

    # ۶.۵) فیش‌های معطل در صف چاپ (پرینتر خراب) — قابل ثبت دستی و گروهی
    "food-ticket-groups.php",                   # گارد تکراری + تبدیل ردیف + دکمهٔ گروهی
    "food-ticket-engine.php",                   # تصمیم‌گیر: فیش معطل «repeat» نیست
    "food-ticket-web/assets/food-manual-attendance.js",

    # ۶.۶) تشخیص و اصلاح ناسازگاری «غایب» و «فیش» در دادهٔ موجود
    # (غایبی که باگ قدیمی از فهرست حذفش کرده بود، و غایبی که فیش چاپ‌شده دارد)
    "tools/fix_absence_ticket_mismatch.php",

    # ۷) گزارش‌ها و راهنما
    "CHANGE-REPORT-FA-1.63.1-r17.md",
    "docs/GUIDE-FA-install-1.63.1.md",           # راهنمای گام‌به‌گام نصب

    # ── ۸) r14: انتخاب‌گر «شخص واردکننده» در کنترل CD/DVD ──
    # <select> بومی با یک حرف متوقف می‌شد؛ حالا انتخاب‌گر متنی است.
    "cd-dvd.php",
    "assets/cd-dvd.js",

    # ── ۹) r14: سه ایراد استخراج شناسنامه ──
    # برخورد کلید یکتا، بازنویسی نام کامپیوتر، و نبود سریال/مدل/کیس.
    "domain-scan.php",
    "inventory.php",
    "actions/asset-actions.php",
    "actions/settings-actions.php",
    "lib/fa-digits.php",
    "asset-names.php",                          # نام کامپیوتر فقط نام کوتاه، IP در tooltip
    "asset-profile.php",                        # case_model خودکار + computer_type از OS هدف

    # ── ۱۰) r14: «نمایش بر اساس معاونت» — تیتر فقط نام + تفکیک غذا ──
    "food-order.php",
    "food-ticket-runtime.php",
    "assets/food-menu.css",
    "assets/food-menu.js",
    "food-ticket-web/assets/food-menu.css",
    "food-ticket-web/assets/food-menu.js",
    "assets/style.css",
    "assets/food-org-groups.js",                # [جدید] موتور گروه‌بندی
    "food-ticket-web/assets/food-org-groups.js",# [جدید] همان، برای پنل غذا
    "lib/org-grouping.php",                     # [جدید] نگاشت سازمانی سمت سرور

    # ── ۱۱) r15: خطای «درخواست نامعتبر است» و قفل‌شدن ورود ──
    "lib/logging-csrf.php",                     # require_csrf: بازگشت به فرم، نه بن‌بست ۴۱۹
    "session_bootstrap.php",                    # پذیرش نشست روی HTTP پس از HTTPS
    "actions/account-actions.php",               # پیام دقیق شکست ورود دامینی

    # ── ۱۲) r15: نام جعلی «client 192.168.x.x» ──
    "asset-inventory-page.php",                 # نمایش با asset_name_or
    "modules/request-center/Pages/create.php",
    "modules/request-center/Pages/detail.php",
    # ابزار اصلاح نام‌های جعلی موجود — با گزینهٔ تازهٔ --from-inventory.
    # مهم: بدون این ابزار، کاربر باید نام‌ها را دستی درست کند.
    "tools/normalize_asset_hostnames.php",
    # ابزار تشخیص سه ایراد r15. باید داخل بسته باشد وگرنه کاربر نمی‌تواند
    # بفهمد کدام شرط روی سرورش برقرار نیست. فقط می‌خواند.
    "tools/diagnose_login_and_assets.php",

    # ── ۱۳) r16: کار با هر دو پروتکل http و https ──
    "lib/db-core.php",                          # HSTS قابل تنظیم (پیش‌فرض ۵ دقیقه، نه یک سال)
    "actions/settings-actions.php",              # ذخیرهٔ تنظیم دو-پروتکلی + روش دوم SMB

    # ── ۱۴) r17: استخراج از راه SMB (همان روش TNI) و نامِ پاک از MAC ──
    "domain-scan.php",                          # مسیر SMB + خطاهای تفکیک‌شده + فالبک خودکار
    "asset-names.php",                          # حذف MAC از نام + IP هرگز نام نیست
    "actions/asset-actions.php",                # (بدون تغییر نهایی؛ برای هم‌ترازی نسخه)
    "tools/collect_via_smb.ps1",                # عامل: کپی روی C$ و اجرا با SYSTEM
    "tools/diag_extract.php",                   # سنجش لایه‌به‌لایهٔ مسیر استخراج
    # مستند اسکن شبکه: بررسی مستقیم TNI و ادمین‌یار از منبع فروشنده + نکتهٔ
    # عضویت دامنه. در ۱.۶۳ هست و بدون به‌روزش، کاربر همان متن کهنه را می‌خواند.
    "docs/NETWORK-INVENTORY-SETUP-FA.md",
    # دستور اجرای گام‌به‌گام ابزار تشخیص. کاربر باید بدون حدس، در پوشهٔ درست
    # cmd باز کند و php را پیدا کند؛ این سند همان است.
    "docs/DIAG-EXTRACT-COMMANDS-FA.md",
    # اجراکنندهٔ .bat: php.exe را خودش پیدا می‌کند (روی IIS در PATH نیست).
    "tools/RUN-DIAG-EXTRACT.bat",
    "tools/GET-DIAG-EXTRACT.bat",
]

# استثنای مستند: اسکریپت‌هایی که در ۱.۶۳ نبودند ولی این بسته **می‌سازد**.
# هر کدام باید خودکفا باشد (وابسته به فایل دیگری که حمل نمی‌شود نباشد).
#   cron/register_system_alarm_tasks.bat — فقط schtasks صدا می‌زند و مسیر PHP را
#   از %~dp0.. و where php پیدا می‌کند. هیچ وابستگی به کد برنامهٔ جدید ندارد.
# آزمون‌هایی که در ۱.۶۳ نسخهٔ کهنه دارند و باید به‌روز برسند.
# چرا در بستهٔ افزایشی؟ چون خودِ ۱.۶۳ آن‌ها را حمل می‌کند و اگر به‌روز نشوند،
# کاربر روی نصب خودش تست قدیمی را اجرا می‌کند و نتیجهٔ قرمز می‌گیرد.
TEST_FILES = [
    "tools/test_key_roles_composition_offline.php",
    "tools/test_it_expert_routing_offline.php",
    "tools/test_assignee_callable_offline.php",
    "tools/test_alarm_selfheal_offline.php",
    "tools/test_release_notes_offline.php",
    "tools/test_supervisor_grant_runtime.php",
    "tools/test_expert_scope_runtime.php",
    "tools/test_primary_admin_routing_runtime.php",
    "tools/test_backfill_auto_receipts_offline.php",
    "tools/test_orphan_ticket_offline.php",
    # r14 — آزمون‌های سه اصلاح آخر
    "tools/test_food_org_groups_offline.php",
    "tools/test_cd_dvd_user_picker_offline.php",
    "tools/test_asset_extraction_offline.php",
    "tools/test_asset_names_offline.php",
    # r15
    "tools/test_login_and_asset_names_r15_offline.php",
]

ALLOWED_NEW_FILES = {
    "cron/register_system_alarm_tasks.bat",
    # ابزار پرکردن روزهای گذشته. به هیچ فایل تازهٔ دیگری وابسته نیست:
    # food-ticket-auto-receipts.php از ۱.۶۳ روی نصب هست و همین را صدا می‌زند.
    "tools/backfill_food_auto_receipts.php",
    # ابزار تشخیص/اصلاح ناسازگاری «غایب» و «فیش». فقط bootstrap و جدول‌ها را
    # می‌خواند؛ به فایل تازهٔ دیگری وابسته نیست.
    "tools/fix_absence_ticket_mismatch.php",
    # ابزار یک‌بارمصرف اصلاح ارجاع‌های قدیمی. مستقل است و به فایل جدیدی
    # وابسته نیست (فقط bootstrap، ماژول مرکز درخواست‌ها و خودِ جداول).
    "tools/fix_ticket_assignments.php",
    # راهنمای گام‌به‌گام نصب — فقط مستندات، هیچ وابستگی کدی ندارد.
    "docs/GUIDE-FA-install-1.63.1.md",
    # r14 — موتور گروه‌بندی. خودکفاست: فقط fetch به food-menu/org-map
    # می‌زند (که food-ticket.php همین بسته تعریف می‌کند) و هیچ importی ندارد.
    "assets/food-org-groups.js",
    "food-ticket-web/assets/food-org-groups.js",
    # r14 — نگاشت سازمانی سمت سرور. فقط به bootstrap و جداول موجود وابسته
    # است؛ food-order.php آن را require_once می‌کند.
    "lib/org-grouping.php",
    # r15 — ابزار تشخیص سه ایراد. فقط می‌خواند و چیزی را تغییر نمی‌دهد؛
    # به bootstrap و دو ماژول موجود وابسته است که هر دو در ۱.۶۳ هستند.
    "tools/diagnose_login_and_assets.php",
    # r15 — ابزار اصلاح نام‌های جعلی. در ۱.۶۳ نبود؛ خودکفاست (فقط bootstrap
    # و asset-names.php را می‌خواند که همان بسته هم به‌روزش می‌کند).
    "tools/normalize_asset_hostnames.php",
    # r17 — عامل اجرای محلی روی مقصد. فقط schtasks/PowerShell سیستم را صدا
    # می‌زند و به collect_server_inventory.ps1 وابسته است که در ۱.۶۳ هست.
    "tools/collect_via_smb.ps1",
    # r17 — ابزار تشخیص استخراج. فقط bootstrap و توابع موجود در ۱.۶۳ را
    # می‌خواند و هیچ چیز را تغییر نمی‌دهد.
    "tools/diag_extract.php",
    # r17-ب — سند دستور اجرا و اجراکنندهٔ batch. هیچ وابستگی کدی ندارند:
    # سند فقط مستندات است و .bat فقط همان diag_extract.php را صدا می‌زند که
    # همین بسته حمل می‌کند.
    "docs/DIAG-EXTRACT-COMMANDS-FA.md",
    "tools/RUN-DIAG-EXTRACT.bat",
    "tools/GET-DIAG-EXTRACT.bat",
}

# این‌ها فقط برای آزمون‌اند و مسیر runtime ندارند؛ در بسته نمی‌آیند اما
# بازسازی‌پذیرند و در مخزن می‌مانند.
FORBIDDEN_IN_PACKAGE = ("downloads/", "patches/", "updates/", "preview/",
                        "docs/system-map/", "ci-diagnostics/", "benchmark-results/")

TITLE = (
    "نسخهٔ ۱.۶۳.۱ (r14) — نقش اعطایی، مسئول تیکت، آژیر خودترمیم، "
    "چرخهٔ فیش غذا، انتخاب‌گر شخص، گروه‌بندی معاونت، و شناسنامهٔ کامل"
)

DESCRIPTION = (
    "1.63.1 — a corrective release on the 1.63 line. Four fixes:\n"
    "(1) Key roles are now grants, not role conversions. Picking a user as IT Expert no longer "
    "promotes them to primary_admin, and adding someone to the Supervisor list no longer rewrites "
    "their role to supervisor. Removing them from a list no longer demotes them either. "
    "Supervisor-list membership now grants exactly four codes (supervisor.panel, supervisor.decide, "
    "supervisor.reopen, queue.view) through user_can(); everything else still comes from the role.\n"
    "(2) Ticket assignment reads the owner exclusively from the explicit IT Expert / Support Expert "
    "lists in Settings, never from system roles. The fourth list, Support Expert, is new. Primary "
    "admins are excluded from automatic assignment entirely and can only be assigned manually.\n"
    "(3) A fatal TypeError is fixed: ticket_assignee_is_eligible() was declared ?callable under "
    "declare(strict_types=1) while all four call sites pass a function name as a string. Every "
    "automatic assignment request returned HTTP 500. The signature is now callable|string|null; "
    "strict_types stays on.\n"
    "(4) Three system-health alarms heal themselves: missing storage folders are created, baseline "
    "migrations are recorded in schema_updates, and cron/register_system_alarm_tasks.bat registers "
    "both Task Scheduler jobs. A migration genuinely absent from the baseline still raises the alarm.\n"
    "(5) The CD/DVD \"recorded by\" picker is a text input with a scrollable button list instead of a "
    "native <select>: typing several characters filters on first/last name, username and unit, not just "
    "a prefix. A native select cannot search mid-string and blocked further keystrokes.\n"
    "(6) Asset extraction no longer aborts on a duplicate identity key, no longer overwrites the computer "
    "name, and now fills serial, model, case model and the remaining 30 profile fields. computer_type is "
    "derived from the target OS (Windows client/server), not from the PHP host.\n"
    "(7) Food reports get \"group by deputy\": each group header is the deputy's own name, followed "
    "immediately by that deputy's members - no type badge, no department path, no summary chip row. "
    "Independent units show only their own name. Each group also lists the foods its members ordered.\n"
    "No schema change, no migration, no new permission. Profile columns are created on demand by "
    "asset_profile_ensure_schema(), which is idempotent."
)


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def sha256_path(p: Path) -> str:
    return sha256_bytes(p.read_bytes())


def installed_163() -> dict[str, bytes]:
    """بازسازی محتوای نصب ۱.۶۳ = main + بستهٔ r16."""
    files: dict[str, bytes] = {}
    listing = subprocess.check_output(
        ["git", "ls-tree", "-r", "--name-only", BASE_COMMIT], cwd=ROOT
    ).decode().splitlines()
    for rel in listing:
        try:
            files[rel] = subprocess.check_output(
                ["git", "show", f"{BASE_COMMIT}:{rel}"], cwd=ROOT
            )
        except subprocess.CalledProcessError:
            continue
    if not BASE_PACKAGE.is_file():
        raise SystemExit(f"بستهٔ پایه پیدا نشد: {BASE_PACKAGE.name}")
    with zipfile.ZipFile(BASE_PACKAGE) as z:
        for name in z.namelist():
            if name.startswith("files/") and not name.endswith("/"):
                files[name[len("files/"):]] = z.read(name)
            elif name.startswith("migrations/") and not name.endswith("/"):
                files[name] = z.read(name)
    return files


def main() -> None:
    out_dir = ROOT / "downloads"
    out_dir.mkdir(exist_ok=True)

    base = installed_163()

    payload: dict[str, bytes] = {}
    for rel in FILES:
        if rel == REPORT_NAME:
            continue
        p = ROOT / rel
        if not p.is_file():
            raise SystemExit(f"فایل در درخت نیست: {rel}")
        if any(tok in rel for tok in FORBIDDEN_IN_PACKAGE):
            raise SystemExit(f"مسیر ممنوع در بسته: {rel}")
        cur = p.read_bytes()
        if rel in base and base[rel] == cur and rel not in TEST_FILES:
            raise SystemExit(f"بی‌تغییر نسبت به ۱.۶۳ است و نباید حمل شود: {rel}")
        if rel not in base and rel not in ALLOWED_NEW_FILES and rel not in TEST_FILES:
            raise SystemExit(f"در نصب ۱.۶۳ نیست — افزودن فایل تازه به بستهٔ افزایشی: {rel}")
        # ── نگهبانِ ارجاع به نسخهٔ کهنه ──────────────────────────────────
        # یک بار پیش آمد که راهنمای داخل بستهٔ r12 هنوز به فایل r11 اشاره
        # می‌کرد؛ آن فایل هم روی تگ باقی مانده بود، پس لینک کار می‌کرد و
        # کاربر می‌توانست نسخهٔ قدیمی را دانلود کند. این نگهبان همان تله را
        # می‌بندد: هر ارجاع به rN قدیمی‌تر داخل محتوای بسته = شکست ساخت.
        if rel.endswith(('.md', '.txt', '.html', '.php', '.js')):
            stale = sorted(set(re.findall(r'ticket-[A-Za-z0-9._-]*-?r(\d+)[A-Za-z0-9._-]*\.zip', cur.decode('utf-8', 'ignore'))))
            current = re.search(r'-r(\d+)-', PACKAGE_NAME)
            current_n = int(current.group(1)) if current else 0
            old_ones = [n for n in stale if int(n) < current_n]
            if old_ones:
                raise SystemExit(f"ارجاع به بستهٔ کهنه r{','.join(old_ones)} در {rel} — قبل از ساخت اصلاح کن")
        payload[rel] = cur

    # آزمون‌ها (به‌روز‌شده، حتی اگر در ۱.۶۳ نبودند)
    for rel in TEST_FILES:
        p_test = ROOT / rel
        if not p_test.is_file():
            raise SystemExit(f"فایل آزمون نیست: {rel}")
        payload[rel] = p_test.read_bytes()

    # هیچ مهاجرتی در این بسته نیست.
    if any(r.endswith(".sql") for r in payload):
        raise SystemExit("SQL نباید در بسته باشد: این نسخه مهاجرت ندارد.")
    if any(r.startswith("migrations/") for r in payload):
        raise SystemExit("پوشهٔ migrations نباید در بسته باشد.")

    report = build_report()
    payload[REPORT_NAME] = report.encode("utf-8")
    payload["VERSION"] = (RELEASE_VERSION + "\n").encode("utf-8")

    manifest = {
        "name": "smb-extract-r17",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": TITLE,
        "requires_version": REQUIRES_VERSION,
        "migrations": [],
        "files": sorted(payload.keys()),
        "sha256": {k: sha256_bytes(v) for k, v in sorted(payload.items())},
        "release_status": "RELEASE",
        "description": DESCRIPTION,
    }
    manifest_bytes = json.dumps(manifest, ensure_ascii=False, indent=2).encode("utf-8")

    package_path = out_dir / PACKAGE_NAME
    tmp_path = out_dir / (PACKAGE_NAME + ".building")
    with zipfile.ZipFile(tmp_path, "w", zipfile.ZIP_DEFLATED) as zf:
        def add(name: str, data: bytes) -> None:
            info = zipfile.ZipInfo(name, date_time=ZIP_DATE)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o644 << 16
            zf.writestr(info, data)

        add("manifest.json", manifest_bytes)
        for name, data in sorted(payload.items()):
            add("files/" + name, data)

    checksum = sha256_path(tmp_path)
    # پیش از جایگزینی بستهٔ منتشرشده، اسناد را با همین هش/حجم بسنج؛
    # اگر کهنه باشند، بستهٔ سالم قبلی دست‌نخورده می‌ماند.
    try:
        check_doc_checksums(checksum, tmp_path.stat().st_size)
    except SystemExit:
        tmp_path.unlink(missing_ok=True)
        raise
    tmp_path.replace(package_path)
    (out_dir / (PACKAGE_NAME + ".sha256")).write_text(
        f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii"
    )
    (out_dir / REPORT_NAME).write_bytes(report.encode("utf-8"))
    (ROOT / "VERSION").write_text(RELEASE_VERSION + "\n", encoding="utf-8")

    print(f"OK {PACKAGE_NAME}")
    print(f"Files: {len(payload)} | migrations: 0 | bytes: {package_path.stat().st_size}")
    print(f"SHA-256: {checksum}")


def check_doc_checksums(checksum: str, size: int) -> None:
    """نگهبانِ هش/حجم در اسناد تحویل.

    یک بار پیش آمد که نام بسته در راهنما به r12 عوض شد ولی حجم و SHA-256
    همان r11 ماند؛ کاربر هش را بررسی می‌کرد و رد می‌شد. حالا هر سند تحویلی
    که نام همین بسته را می‌آورد، باید همین هش و همین حجم را هم بیاورد.
    """
    # هرگز راهنمای داخل بسته را نسنج: آن فایل خودش داخل زیپ است و
    # نمی‌تواند هشِ زیپِ خودش را درست حمل کند (تناقض ذاتی). فقط اسنادِ
    # بیرونِ بسته — همان‌هایی که کاربر پیش از دانلود می‌بیند.
    docs = [
        ROOT / "downloads" / "README-FA-downloads.md",
        ROOT / "downloads" / "RELEASE-ASSETS-1.63.1.txt",
    ]
    size_str = f"{size:,}"
    # فقط بررسی؛ هیچ سندی خودکار بازنویسی نمی‌شود. دو سند بیرونی جدا از
    # بستهٔ کامل‌اند و هش/حجم خودشان را دارند — بازنویسی خودکار، هش بستهٔ
    # کامل را با هش بستهٔ میانی یکی می‌کرد.
    for doc in docs:
        if not doc.is_file():
            continue
        text = doc.read_text(encoding="utf-8")
        if PACKAGE_NAME not in text:
            continue
        if checksum not in text:
            raise SystemExit(
                f"هش SHA-256 بستهٔ میانی در {doc.relative_to(ROOT)} نیست یا کهنه است — "
                f"هش درست: {checksum}"
            )
        # سند فارسی است: ارقام و جداکنندهٔ هزارگان فارسی‌اند.
        norm = text.translate(str.maketrans("۰۱۲۳۴۵۶۷۸۹٬", "0123456789,"))
        if size_str not in norm:
            raise SystemExit(
                f"حجم بستهٔ میانی ({size_str} بایت) در {doc.relative_to(ROOT)} نیست یا کهنه است."
            )
        # لینک release/download فقط وقتی کار می‌کند که دارایی روی همان انتشار
        # آپلود شده باشد. از این محیط آپلود دارایی ممکن نیست، پس سند نباید
        # کاربر را به ۴۰۴ بفرستد؛ مسیر raw/tag همیشه فایل را می‌دهد.
        bad_link = re.search(
            r"releases/download/[^)\"'\s]*/" + re.escape(PACKAGE_NAME), text
        )
        if bad_link:
            raise SystemExit(
                f"لینک releases/download برای همین بسته در {doc.relative_to(ROOT)} — از raw/<tag>/ استفاده کن."
            )


def build_report() -> str:
    tpl = """# گزارش تغییرات ۱.۶۳.۱ — r17

نسخه: **{RELEASE_VERSION}** · پیش‌نیاز: **{REQUIRES_VERSION}** · تاریخ: **{RELEASE_DATE}**
مهاجرت: **ندارد** · وضعیت: **RELEASE**

## ★ r17 — سه ایراد استخراج، یک بار برای همیشه

### ۱) «استخراج محلی» اطلاعات سرور را می‌آورد

این باگ نیست، محدودیت معماری است و برچسبش گمراه‌کننده بود. کد PHP روی **سرور** اجرا
می‌شود؛ «محلی» یعنی همان میزبان. هیچ سایتی نمی‌تواند از داخل مرورگر سخت‌افزار رایانهٔ
کاربر را بخواند — این محدودیت امنیتی مرورگر است، نه کد ما.

از این نسخه، برچسب دکمه **«استخراج محلی سرور سامانه»** است و زیرش نوشته شده که فقط
سرور را می‌خواند. برای خواندن رایانه‌های دیگر، از «استخراج اطلاعات سیستم‌های انتخاب‌شده»
یا صفحهٔ «اسکن دامنه» استفاده کنید.

### ۲) MAC آدرس در ستون «نام»

ریشه: reverse DNS معیوب شبکه، نام را به شکل `bazresi-pc 192.168.1.65 00-1A-2B-3C-4D-5E`
برمی‌گرداند. قاعدهٔ حذف، فقط IPv4 را می‌شناخت؛ IP حذف می‌شد و MAC می‌ماند و به‌عنوان
«نام کامپیوتر» چاپ می‌شد.

حالا هر سه شکل MAC (خط‌تیره، دونقطه، بدون جداکننده) حذف می‌شود و MAC تنها هرگز به‌عنوان
نام نمایش داده نمی‌شود. نام‌هایی مثل `PC-12AB34CD` و `MBA4-01` دست‌نخورده می‌مانند.

برای اصلاح رکوردهای موجود:

```
php tools/normalize_asset_hostnames.php --from-inventory
php tools/normalize_asset_hostnames.php --from-inventory --apply
```

### ۳) «Access is denied» در استخراج از شبکه — و چرا TNI کار می‌کند

**این مهم‌ترین بخش این نسخه است. خطا را درست بخوانید:**

| پیام | معنا | درمان |
|---|---|---|
| `RPC server is unavailable` | بسته به مقصد **نرسیده** | فایروال مقصد، سرویس WMI، پورت ۱۳۵ و RPC |
| `Access is denied` | بسته رسیده و احراز هویت شده، ولی **توکن مدیریتی فیلتر شده** | UAC از راه دور، یا حساب روی مقصد ادمین محلی نیست |
| `The client cannot connect…` | خودِ مقصد WinRM ندارد | `winrm quickconfig` روی مقصد |

پیام شما `Access is denied` بود — یعنی شبکه و فایروال **سالم‌اند** و مشکل مجوزدهی
ویندوز است. TNI هم همین حساب ادمین را دارد، ولی عامل را روی خودِ مقصد اجرا می‌کند، پس
با WMI محلی و توکن کامل کار می‌کند. همان مسیر در این نسخه اضافه شده است:

`tools/collect_via_smb.ps1` → اتصال `C$` با SMB → کپی عامل → اجرا با `/ru SYSTEM`
→ خواندن نتیجه → پاک‌سازی. **خودکار است:** هر مقصدی که DCOM/WinRM ردش کند، در همان
نوبت بار دوم از راه SMB آزموده می‌شود.

شرطش فقط دو چیز است — همان دو شرطی که TNI هم دارد: حساب روی مقصد در **Administrators
محلی** باشد و **TCP 445** باز باشد.

سوئیچ: **تنظیمات ← اسکن دامنه ← «روش دوم خودکار (SMB)»** (پیش‌فرض روشن).

### بررسی مستقیم TNI و ادمین‌یار — و یک اصلاح در حرف قبلی من

پیش‌تر فقط مستندات TNI را خوانده بودم و دربارهٔ ادمین‌یار حدس زده بودم. حالا هر دو از منبع خودشان بررسی شد:

**ادمین‌یار سه روش دارد و «روش پیشنهادی» خودش، اسکن شبکه نیست:**

۱. **(پیشنهاد فروشنده)** پوشهٔ نرم‌افزار را در شبکه به اشتراک بگذارید؛ کاربر روی هر کلاینت باز و اجرا می‌کند و اطلاعات **روی همان کلاینت** در `pcinfo.mdb` ذخیره می‌شود. هیچ مجوز از راه دوری لازم نیست.
۲. فلش پرتابل برای رایانه‌های خارج از شبکه.
۳. اسکن دامنه: «در صورتی که **ریموت رایانه مقصد باز** بوده و تنظیمات فایروال امکان دسترسی بدهد، اطلاعات استخراج خواهد شد.»

**پس حتی ادمین‌یار هم برای اسکن از راه دور به «ریموت باز» و مجوز روی مقصد نیاز دارد.** چیزی که آن‌ها واقعاً برتری دارند روش ۱ است — اجرای محلی روی هر کلاینت — و Ticket دقیقاً همین را دارد: `client-agent/Collect-Inventory.ps1` + `api/inventory.php`.

**TNI:** مستنداتش می‌گوید روش اصلی، گذاشتن عامل روی `ADMIN$` و اجرای آن با سرویس روی مقصد است؛ WMI/RPC روش دوم. مسیر SMB در این نسخه همان است.

**نکتهٔ تعیین‌کننده — اگر سرور سامانه عضو دامنه نباشد، هیچ ابزاری با WMI راه‌دور موفق نمی‌شود.** ویندوز توکن حساب محلی را در اتصال شبکه‌ای فیلتر می‌کند (UAC remote restrictions). ترتیب درست: ① سامانه روی سرور **عضو دامنه** ② حساب اسکن، حساب **دامنه** و ادمین محلی روی کلاینت‌ها ③ سپس SMB یا WinRM کار می‌کند. اگر سامانه روی یک کلاینت Workgroup است، مسیر درست `client-agent` است نه اسکن از راه دور. `tools/diag_extract.php` اولین چیزی که می‌سنجد همین عضویت دامنه است.

### سنجش، نه حدس

`tools/diag_extract.php` هر لایه را جدا می‌سنجد و می‌گوید دقیقاً کدام شکسته است:

```
php tools/diag_extract.php BAZRESI-PC DOMAIN\\user
```

DNS → TCP 445 → TCP 135 → TCP 5985 → اتصال واقعی CIM با هر پروتکل → بازکردن `C$` با
اعتبارنامه (اثبات ادمین محلی بودن). خروجی، متن فارسی خوانا برای فرستادن به پشتیبانی است.

---

## ★ r16 (همچنان در این بسته) — کار با هر دو http و https

**پاسخ کوتاه:** یک چک‌باکس در **تنظیمات ← اطلاعات سامانه**:

> ☑ **کار با هر دو پروتکل http و https با همان ورود**

آن را روشن کنید. تمام. جزئیات کامل در بخش ۸.۱۵.

---

## ⚠️ سه ایراد r15 — بخوانید اول

| گزارش شما | ریشهٔ واقعی | بخش |
|---|---|---|
| «درخواست نامعتبر است، صفحه را تازه‌سازی کنید» | نشست ساخته‌شده زیر HTTPS، با کوکی `Secure`؛ روی HTTP مرورگر آن کوکی را نمی‌فرستد | ۸.۱۲ |
| «با نام کاربری دامینی وارد نمی‌شوم، فقط ادمین محلی» | سه علت جدا — هر سه پیام «رمز اشتباه» می‌گرفتند | ۸.۱۳ |
| «هنوز `client 192.168.1.25` نشان می‌دهد» | نام واقعی از ویندوز خوانده می‌شد ولی هرگز ذخیره نمی‌شد | ۸.۱۴ |

**پیش از هر چیز این را اجرا کنید** تا دقیقاً ببینید روی سرور شما کدام شرط
برقرار نیست (فقط می‌خواند، چیزی را تغییر نمی‌دهد):

```
php tools\\diagnose_login_and_assets.php
```

---

> **این نسخه r14 هم هست.** علاوه بر کارهای r13 (بخش‌های ۲ تا ۵)، سه اصلاح آخر
> هم داخل همین ۱.۶۳.۱ آمده: انتخاب‌گر «شخص واردکننده»، گروه‌بندی معاونت با
> تیتر مینیمال + تفکیک غذا، و تکمیل شناسنامهٔ فنی. بخش‌های ۱۰ تا ۱۲.

| موضوع r14 | بخش |
|---|---|
| انتخاب‌گر «شخص واردکننده» در CD/DVD | ۸.۹ |
| گروه‌بندی گزارش‌ها: تیتر فقط نام معاونت + تفکیک غذا | ۸.۱۰ |
| شناسنامهٔ فنی: سه ایراد + تکمیل ۳۰ فیلد | ۸.۱۱ |

---

## ۱) خلاصه

۱.۶۳ روی سامانهٔ شما نصب است. این بسته چهار اصلاح می‌آورد — همان چهار موردی که
در بستهٔ ۱.۶۲.۹۸ بود، این بار برای درخت ۱.۶۳ و به‌همراه قاعدهٔ تازهٔ نقش.

| # | موضوع | پیش از ۱.۶۳.۱ | حالا |
|---|-------|----------------|------|
| ۱ | نقش کلیدی | تبدیل نقش | **اعطای دسترسی** |
| ۲ | مسئول تیکت | نقش سیستمی / ادمین اصلی | فهرست کارشناسان |
| ۳ | ارجاع خودکار | خطای مرگبار ۵۰۰ | کار می‌کند |
| ۴ | آژیر سلامت | هشدار بی‌درمان | خودترمیم |

---

## ۰) وضعیت فعلی این بسته (آخرین به‌روزرسانی)

این بسته علاوه بر چهار اصلاح اصلی زیر، کل چرخهٔ **فیش غذا** را هم اصلاح
می‌کند. همهٔ موارد با اجرای واقعی روی سامانه تأیید شده‌اند:

| # | گزارش کاربر | ریشهٔ واقعی | وضعیت |
|---|---|---|---|
| ۱ | «دو نفر با وجود تردد، در فهرست غایبان ماندند» | فیش‌دار بودن، پیش از غیب‌بودن بررسی نمی‌شد | ✅ رفع |
| ۲ | «لغو فیش همهٔ افراد روز را لغو می‌کرد» | یک دکمهٔ گروهی بی‌قید | ✅ انتخابی شد |
| ۳ | «۵ نفر با فیش، غیرقابل‌انتخاب بودند» | شرط لغو فقط `printed` را می‌پذیرفت | ✅ رفع |
| ۴ | «لغو زدم ولی فیش چاپ‌نشده نشد» | رکورد `no_food` می‌شد، فیشی برای صدور نمی‌ماند | ✅ رفع |
| ۵ | «لغو زدم، به لیست غایبین اضافه نشدند» | مسیر API در نگاشت دسترسی نبود (۴۰۳ بی‌صدا) + رکورد بدون `group_id` | ✅ رفع |
| ۶ | «ثبت فیش زدم، گفت قبلاً ثبت شده» | فیش خودکارِ تحویل‌نشده «فیش واقعی» شمرده می‌شد | ✅ رفع |
| ۷ | «بعد از لغو، ری‌آیشو کار نمی‌کرد» | متغیر پیش از تعریف استفاده می‌شد | ✅ رفع |
| ۸ | «علامت زرد تردد روی ردیف دارای فیش» | `auto_printed` در JS پوشش نداشت | ✅ رفع |
| ۹ | «غیبت ثبت شد ولی جدول خالی ماند» | هم‌ترازی خودکار، رکورد تازهٔ ادمین را پاک می‌کرد | ✅ رفع |
| ۱۰ | «بعد از لغو، شخص در فهرست غایبان دیده نمی‌شد» | فیلتر نمایش، فیشِ لغوشده را «حضور» می‌شمرد | ✅ رفع |
| ۱۱ | «لغو می‌زنم، صفحه را دوباره باز می‌کنم — لغو باطل شده» | خواندن پنل (GET) ردیف را دوباره `delivered` می‌کرد | ✅ رفع |
| ۱۲ | «برای کسی که در این روز فیش دارد، تاریخ آینده رد می‌شود» | پیام اشتباه روی ردیف چاپ‌شده | ✅ پیام صریح |
| ۱۳ | «لغو دوباره انجام می‌شد و رکورد غیبت تکراری می‌ساخت» | نبود محافظ لغو تکراری | ✅ رفع |
| ۱۴ | «لغو کرده‌ام ولی «تردّد بدون فیش» هم رد می‌شود» | ردیف یتیم جلوی مسیر بی‌فیش را می‌گرفت | ✅ رفع |
| ۱۵ | «دو نفر تردد داشتند ولی در فهرست غایبین بودند؛ لغو هم رد شد» | لغو «آخرین ردیف» (repeat) را برمی‌داشت نه ردیف فیش | ✅ رفع |
| ۱۶ | «پیام «قبلاً لغو شده» برای فیشِ سالم» | محافظ لغو، ردیف repeat را لغوشده می‌دید | ✅ رفع |

### همان دو نفر — چرا دو ردیف داشتند؟

```
auto_printed / printed / pending   ← فیش خودکار صبح (ticket_key دارد)
repeat / not_printed / pending     ← تردد نمایندهٔ گروه (فیش نیست)
```

لغو، «آخرین ردیف» را برمی‌داشت، یعنی ردیف `repeat` را. نتیجه: هیچ فیشی
لغو نمی‌شد؛ و چون آن ردیف `not_printed` و بی‌کلید بود، محافظ لغو دوباره
هم آن را «از قبل لغوشده» می‌دید.

**حالا:** لغو ردیفِ فیشِ واقعی را انتخاب می‌کند و `repeat` را دور می‌زند؛
محافظ لغو دوباره هم فقط بر سابقهٔ واقعی `cancelled_ticket` تکیه می‌کند.

| گام روی همان سناریو | نتیجه |
|---|---|
| لغو نفر ۱ و ۲ | ✅ «فیش لغو شد و شخص غایب ثبت شد» |
| ۴ بار بازخوانی پنل | ✅ لغو پابرجا، هر دو در فهرست غایبین |
| ری‌آیشو با فیش | ✅ `printed/delivered`، از غایبین خارج |
| همان آزمون روی HTTP واقعی | ✅ ۲/۲ |

---

## ۲) اصلاح اول — نقش اعطایی، نه تبدیل نقش

این مهم‌ترین تغییر است و دو باگ قدیمی را با هم می‌بندد.

### ۲.۱) انتخاب «کارشناس IT» کاربر را ادمین اصلی می‌کرد

در `actions/role-actions.php`:

```
foreach (array_diff($itExperts, $oldExperts) as $id) =>
    UPDATE users SET role = 'primary_admin', is_primary_admin = 1
```

هر بار که یک کاربر را کارشناس IT می‌کردید، او **ادمین اصلی سامانه** می‌شد و
دسترسی کامل می‌گرفت. دو مفهوم بی‌ربط به‌هم بسته شده بودند.

**حالا:** فقط پرچم `is_it_agent` ست می‌شود. نقش دست نمی‌خورد.

### ۲.۲) انتخاب «سوپروایز» نقش را عوض می‌کرد

```
foreach (array_diff($supervisors, $oldSupervisors) as $id) =>
    UPDATE users SET role = 'supervisor'
```

و حذف از فهرست: `UPDATE users SET role = 'user'`. یعنی یک «کارشناس» که از فهرست
سوپروایزرها حذف می‌شد، **کارشناسی‌اش هم از دست می‌رفت**.

**حالا:** `role` دست نمی‌خورد. حذف از فهرست هم کسی را تنزل نمی‌دهد — فقط
نقش خالصِ `supervisor` به `user` برمی‌گردد (مسیر مهاجرت).

### ۲.۳) دسترسی سوپروایز از کجا می‌آید؟

از `user_can()` در `permissions.php`. عضویت در فهرست سوپروایزرها **دقیقاً چهار**
کد را می‌دهد:

```
supervisor.panel   ·   supervisor.decide   ·   supervisor.reopen   ·   queue.view
```

بقیهٔ دسترسی‌ها همچنان از نقش اصلی می‌آید. عمدی است: سوپروایز شدن نباید یعنی
«همه‌چیز». تفکیک وظایف نمی‌شکند.

> **چرا این نکته حیاتی بود:** اگر `supervisor.panel` فقط از نقش می‌آمد، برداشتن
> تبدیل نقش، سوپروایزرها را از پنلشان بیرون می‌انداخت. دقیقاً همین علت باعث شد
> این اصلاح یک بار متوقف و درست بازنویسی شود.

### ۲.۴) کارشناس برگزیده حالا واقعاً می‌تواند رسیدگی کند

این مورد هنگام آزمون با پایگاه‌دادهٔ واقعی پیدا شد و اگر پیدا نمی‌شد، اصلاح
قبلی نیمه‌کاره می‌ماند.

`ticket.edit` و `queue.view` فقط در نقش‌های `agent`/`manager` بود. کاربری با نقش
`user` که در فهرست کارشناس تعیین می‌شد:

- مسئول تیکت **شناخته** می‌شد،
- صف حوزه‌اش را **می‌دید**،
- ولی `ticket.edit` نداشت، پس **نمی‌توانست تیکت را باز کند یا پاسخ دهد**.

یعنی «مسئول تیکت» بود که هیچ تیکتی نمی‌توانست بردارد. حالا عضویت در فهرست
کارشناسان، هم‌اندازهٔ کارِ یک رسیدگی‌کننده دسترسی می‌دهد:

```
ticket.edit  ·  ticket.view_unit  ·  ticket.reply  ·  ticket.view_own  ·  queue.view
```

**عمداً نیست:** `ticket.view_all` (دیدن همه‌چیز) و `ticket.assign` (تخصیص به دیگران).
کارشناس فقط حوزهٔ خدمت خودش را می‌بیند و تخصیص کارِ سرپرست/مدیر است.

---

## ۳) اصلاح دوم — مسئول تیکت فقط از فهرست کارشناسان

### ۳.۱) علت واقعی: سه لایه

**لایهٔ ۱ —** `choose_handling_agent()` در `modules/request-center/lib/functions.php`
مالک تیکت IT را با `role = 'primary_admin'` پیدا می‌کرد. هر ادمین اصلی، خودکار
مسئول **همهٔ** تیکت‌های IT می‌شد.

**لایهٔ ۲ —** `ticket_assignee_is_eligible()` در `lib/auth-scope.php` فقط نقش‌های
سیستمی را می‌پذیرفت. کاربری با نقش `user` که در فهرست برگزیده شده بود **رد** می‌شد.

**لایهٔ ۳ —** همان باگ ۲.۱: انتخاب کارشناس، کاربر را ادمین می‌کرد.

### ۳.۲) فهرست چهارم آمد

| فهرست | نقش |
|-------|-----|
| بازرسی | کنترل CD/DVD |
| سوپروایز | پنل و صف تصمیم |
| کارشناس آی تی | مسئول تیکت‌های IT |
| **کارشناس پشتیبانی** | **مسئول تیکت‌های پشتیبانی** |

### ۳.۳) ادمین‌های اصلی — قانون لازم‌الاجرا

**هیچ ادمین اصلی، به‌خودی‌خود، مسئول انجام تیکت نمی‌شود.** این قانون در سه جا
اعمال شد:

| جا | قبل | حالا |
|----|-----|------|
| لایهٔ پشتیبانِ ارجاع خودکار | با فهرست خالی، ادمین اصلی مسئول می‌شد | حذف شد |
| لایهٔ کارشناس/مدیر واحد | ادمین اصلی با واحد هم‌نام رد می‌شد؟ نه | صریحاً رد می‌شود |
| سه مسیر ارجاع دستی | شرط «ادمین اصلی ⇒ حوزهٔ IT» | تابع مشترک `ticket_assignee_group_ok()` |

پیش از این، لایهٔ پشتیبان این شرط را داشت:

```sql
u.role = "primary_admin" OR (u.role = "admin" AND u.is_primary_admin = 1)
```

یعنی با خالی بودن فهرست، **هر ادمین اصلی فعال** خودکار مسئول همهٔ تیکت‌های IT
می‌شد. این بلوک حذف شد.

**استثنای قانونی (مورد «علی»):** اگر ادمین اصلی **در فهرست کارشناس آی تی یا
کارشناس پشتیبانی باشد**، نقش ادمینش حفظ می‌شود و تیکت‌های همان حوزه به او
ارجاع داده می‌شود. این دو با هم منافات ندارند: «نقش ادمین» و «مسئول تیکت»
دو چیز جدا هستند.

پیش از این، ادمین اصلی حتی وقتی در فهرست بود، فقط برای حوزهٔ **IT** پذیرفته
می‌شد (`return $group === 'it'`) — یعنی ادمین اصلی‌ای که کارشناس پشتیبانی
بود، برای تیکت پشتیبانی رد می‌شد. حالا حوزه را فهرست تعیین می‌کند، نه نقش.

### ۳.۴) کاربر دو-حوزه‌ای

یک کاربر می‌تواند **هم‌زمان** در فهرست IT و پشتیبانی باشد. در ۱.۶۳ کد با
`if/elseif` یکی را می‌گرفت و دیگری را دور می‌ریخت. حالا هر دو حوزه با
`service_group IN (?, ?)` گرفته می‌شود.

---

## ۴) اصلاح سوم — باگ مرگبار TypeError

`lib/auth-scope.php` با `declare(strict_types=1)` اجرا می‌شود و امضا این بود:

```
function ticket_assignee_is_eligible(array $ticket, array $user, ?callable $permissionCheck = null)
```

ولی هر چهار نقطهٔ فراخوانی در `index.php` **رشته** می‌فرستند:

```
ticket_assignee_is_eligible($routingTicket, $candidate, 'ticket_notification_user_can')
```

در حالت strict، رشته برای `?callable` یک `TypeError` است. یعنی **هر درخواست
تازه‌ای که به مسیر ارجاع خودکار می‌رسید، صفحهٔ ۵۰۰ می‌داد.**

**حالا:** امضا `callable|string|null` است. `strict_types` **خاموش نشد** — اصلاح
در امضاست، نه در سخت‌گیری. closure همچنان کار می‌کند.

---

## ۵) اصلاح چهارم — سه آژیر خودترمیم

### ۵.۱) پوشهٔ `storage/food_ticket_spool` نیست

هر چهار پوشهٔ ضروری (`food_ticket_spool`، `food_ticket_prints`، `logs`،
`sessions`) ساخته می‌شوند: `mkdir(..., 0750, true)` و در صورت شکست تلاش دوم با
`0775`. اگر مجوز فایل‌سیستم اجازه ندهد، هشدار صادقانه سر جایش می‌ماند.

### ۵.۲) مهاجرت‌های baseline «اجرا نشده» — هشدار کاذب

در نصب تازه، ساختار این مهاجرت‌ها از قبل در `schema.sql` هست؛ فقط ردیف دفتری‌شان
در `schema_updates` نبود. حالا خودکار ثبت می‌شوند.

**قاعدهٔ ایمنی:** فقط ردیف دفتری اضافه می‌شود، **هیچ DDL ای اجرا نمی‌شود**. اگر
مهاجرتی واقعاً در baseline نباشد، هشدار سر جایش می‌ماند.

### ۵.۳) کرون `system_alarm_tick`

```
cron\\\\register_system_alarm_tasks.bat
```

با دوبار کلیک هر دو وظیفه ساخته می‌شوند (`TicketSystemAlarmTick` هر ۵ دقیقه،
`TicketSystemAlarmMorning` روزانه ۰۸:۰۰). مسیر PHP خودکار پیدا می‌شود و در پایان
یک بار فوراً اجرا می‌شود تا هشدار همان لحظه پاک شود.

> این یکی از داخل صفحهٔ وب قابل انجام نیست (نیاز به دسترسی مدیر ویندوز)، پس
> تنها موردی است که یک قدم دستی دارد.

---

## ۵.۵) تیکت‌هایی که قبلاً اشتباه ارجاع شده‌اند

**این انتقال خودکار نیست.** نسخهٔ ۱.۶۳.۱ فقط جلوی ارجاع‌های *تازه* را می‌گیرد.
تیکتی که دیروز به یک ادمین اصلی ارجاع داده شده، تاریخچه و اعلان دارد و سامانه
نباید بی‌اجازهٔ شما آن را جابه‌جا کند.

برای اصلاح، یک ابزار همراه بسته است:

```
php tools\fix_ticket_assignments.php            ← فقط نشان می‌دهد
php tools\fix_ticket_assignments.php --apply    ← اعمال می‌کند
```

- فقط تیکت‌های **باز** را می‌بیند؛ حل‌شده و بسته دست نمی‌خورند.
- مسئولی که **در فهرست کارشناسان هست** را رد می‌کند (مثل علی).
- اگر کارشناس واجدی پیدا نشود، هشدار می‌دهد و دست نمی‌زند.
- هر تغییر در تاریخچهٔ تیکت ثبت می‌شود.
- همه‌چیز در یک تراکنش: اگر خطایی بدهد، هیچ تغییری ذخیره نمی‌شود.
- `--ticket 123` فقط همان تیکت را.

---

## ۵.۶) ثبت خودکار فیش روزهای گذشته — شام هر روز و ناهار تعطیل‌ها

### مشکل

شام هر روز و ناهار پنجشنبه/جمعه/تعطیل رسمی به‌صورت خودکار ثبت می‌شوند — اما
**فقط یک بار**: در گذر پایان‌روز ساعت ۲۳ همان روز، و فقط برای «دیروز» و «امروز».

هر شبی که Worker خاموش بوده، نصب نبوده، یا آن نسخه این قابلیت را نداشته، آن روز
برای همیشه خالی می‌ماند. سامانه هیچ سازوکاری برای پر کردن گذشته نداشت، پس باید
دستی فیش می‌زدید.

### راه‌حل

دو راه به یک موتور واحد وصل شده‌اند:

**۱) داخل پنل** — «تنظیمات سیستم ← اتصال پایگاه‌ها»، پایین صفحه، کارت
**«ثبت خودکار فیش روزهای گذشته»**:

```
از تاریخ  [۱۴۰۵/۰۸/۰۱]   تا تاریخ  [۱۴۰۵/۰۸/۱۵]
[پیش‌نمایش]  [ثبت خودکار]
```

«پیش‌نمایش» فقط می‌گوید هر روز چند شام و چند ناهار کاندید دارد و کدام روزها تعطیل‌اند.
«ثبت خودکار» واقعاً می‌نویسد و برای هر روز گزارش می‌دهد: چند تا تازه، چند تا از قبل
بوده، چند تا غیبت، چند تا فیش واقعی.

**۲) از خط فرمان** — همان کار، برای بازه‌های بلند یا اسکریپت‌نویسی:

```
php tools\backfill_food_auto_receipts.php --days 30            ← فقط گزارش
php tools\backfill_food_auto_receipts.php --days 30 --apply    ← ثبت واقعی
php tools\backfill_food_auto_receipts.php --from 2026-08-01 --to 2026-10-30 --apply
```

### چه چیزی را رعایت می‌کند

هر دو راه **همان تابع `food_ticket_auto_receipts_run()`** سامانه را صدا می‌زنند —
نه یک نسخهٔ موازی. پس تمام قواعدی که گذر شبانه دارد، این‌جا هم برقرار است:

| قاعده | رفتار |
|-------|-------|
| ناهار فقط تعطیل | ناهار در روز کاری رد می‌شود (پنجشنبه، جمعه، `holidays`) |
| غیبت | کاربری که آن روز `food_ticket_daily_absence` دارد رد می‌شود |
| فیش واقعی | اگر فیش `printed`/`print`/`guest` برای همان وعده هست، دست نمی‌خورد |
| تکرار | کلید یکتای `sha256` — اجرای دوباره **صفر** ردیف می‌سازد |
| چاپ فیزیکی | **هیچ.** فیش فقط در سامانه ثبت می‌شود (`auto_printed`) |
| امروز | **رد می‌شود.** سفارش‌ها تا ۲۳:۰۰ می‌توانند تغییر کنند؛ گذر شبانه خودش می‌پوشاند |
| قفل هم‌زمانی | `GET_LOCK` روزانه؛ اگر Worker همان لحظه مشغول باشد، «قفل بود» می‌گوید نه اینکه صبر کند |

### دسترسی

مسیر API فقط با مجوز **`food.menu_edit`** و فقط با **POST** باز است. کاربر عادی
`403` می‌گیرد و درخواست بدون توکن CSRF `419`. سقف بازه ۴۰۰ روز است تا اشتباه تایپی
مثل `۱۴۰۰-۰۱-۰۱` کل جدول را نگردد.

### آنچه عوض **نشد**

هیچ مهاجرتی اضافه نشد، هیچ ستونی از `food_ticket_events` تغییر نکرد، و قواعد
گذر شبانهٔ Worker دست‌نخورده ماند. این یک راه **پر کردن گذشته** است، نه تغییر
رفتار آینده.

---

## ۵.۷) فیش‌های معطل در صف چاپ — دیگر «ثبت‌شده» حساب نمی‌شوند

### مشکل (رخداد ۱۴۰۵/۰۸/۲۵)

پرینتر خراب بود. کارکنانی که تردد زدند در صف چاپ ماندند — نه چاپ شدند، نه تحویل
ثبت شد. بعد نوبت حساب‌وکتاب بود و سه راه بسته بود:

| کجا | چه می‌دید |
|-----|-----------|
| ثبت تردد | «دارای فیش این روز» — چک‌باکس **قفل** |
| ثبت دستی (سرور) | «ثبت تکراری مجاز نیست» |
| گزارش تحویل غذا | **غایب** — فقط چند نفری که دستی ثبت شده بودند |

### ریشه — چهار گارد هم‌زمان

موتور چاپ رویداد را با `event_type='printed'` و `print_status='pending'` می‌سازد.
هر چهار لایه فقط به `event_type` نگاه می‌کردند، نه به این‌که **واقعاً چاپ شد یا نه**:

1. `food-manual-attendance.js` — هر `event_type='printed'` را «دارای فیش» می‌دانست.
2. `food_ticket_api_manual_attendance_save()` — هر رویداد غیر از `no_food` را تکراری می‌دانست.
3. گارد `ticket_key` — هر فیش موجود را رد می‌کرد.
4. `food_ticket_decide_for_user()` — `repeat` برمی‌گرداند.

هیچ‌کدام `print_status` را نمی‌سنجید. نتیجه: فیش معطل، از نظر سامانه «فیش کامل».

### اصلاح

یک تابع مشترک، `food_ticket_event_is_orphan_ticket()`، که «فیش یتیم» را تعریف می‌کند:

> فیشی که **هنوز چاپ نشده** (`print_status` ∈ pending/printing/print_error/failed/held_absent/خالی)
> **و** تحویل هم ثبت نشده (`delivery_status` ≠ delivered).

هر چهار لایه از همین یک تابع استفاده می‌کنند — نه چهار تعریف جدا.

| رفتار | قبل | بعد |
|-------|-----|-----|
| چک‌باکس فیش معطل | قفل | **فعال** — برچسب «در صف چاپ ماند — قابل ثبت دستی» |
| ثبت دستی | «تکراری» | همان ردیف **تبدیل** می‌شود (نه ردیف دوم) |
| فیش واقعاً چاپ‌شده | قفل | **همچنان قفل** — دست نمی‌خورد |
| گزارش تحویل | غایب | **حاضر** — چون حالا تحویل ثبت می‌شود |

### دکمهٔ ثبت گروهی

رخداد واقعی یعنی ده‌ها نفر یک‌جا. کلیک نفر‌به‌نفر همان ثبت دستی است که نمی‌خواهید.
در «ثبت تردد» دکمهٔ **«ثبت گروهی N فیشِ چاپ‌نشده»** ظاهر می‌شود که:

- فقط ردیف‌های همان روز را که فیش‌شان معطل است انتخاب می‌کند؛
- حالت «با فیش» را روشن می‌کند (وگرنه تبدیل انجام نمی‌شود)؛
- علت پیش‌فرض را پر می‌کند تا فقط «ثبت» را بزنید.

### چرا گزارش تحویل دست نخورد

گزارش همچنان فقط `delivery_status = 'delivered'` را نشان می‌دهد. این **درست** است —
غذایی که تحویل نشده نباید در گزارش تحویل باشد. اصلاح این است که بتوانید تحویلش را
ثبت کنید، نه این‌که گزارش دروغ بگوید.

### آنچه عوض نشد

بدون مهاجرت، بدون ستون تازه، بدون مجوز تازه. ردیف قبلی تبدیل می‌شود و سابقهٔ صف
در `source_payload` با کلید `converted_from_orphan_ticket` می‌ماند.

---

## ۶) نصب

```
۱.۶۳ ← ۱.۶۳.۱   (این بسته)
```

روی پوشهٔ نصب باز کنید (فایل‌ها می‌روند بالای فایل‌های فعلی). سپس:

```
cron\\\\register_system_alarm_tasks.bat
```

و صفحهٔ **سلامت سامانه** را تازه‌سازی کنید.

سپس (توصیه‌شده) نام‌های جعلی موجود را اصلاح کنید:

```
php tools\normalize_asset_hostnames.php --from-inventory
php tools\normalize_asset_hostnames.php --from-inventory --apply
```

> **عمداً عوض نشد:** مهاجرتی اضافه نشد، جدولی تغییر نکرد، مجوز تازه‌ای ساخته نشد،
> و اسکیما دست‌نخورده ماند.

---

## ۷) اگر قبلاً کارشناس IT انتخاب کرده بودید

کاربرانی که با نسخه‌های پیشین به `primary_admin` ارتقا یافته‌اند **حفظ می‌شوند** —
این بسته کسی را از دسترسی ادمین خارج نمی‌کند. فقط از این پس انتخاب‌های جدید
کسی را ادمین نمی‌کنند.

برای برگرداندن ارتقاهای ناخواسته: **تنظیمات ← کاربران**، نقش سیستمی را دستی اصلاح کنید.

---

## ۸) آزمون‌ها

| تست | نتیجه |
|-----|-------|
| `tools/test_key_roles_composition_offline.php` **(جدید)** | ۲۵/۲۵ |
| `tools/test_supervisor_grant_runtime.php` **(جدید، با پایگاه‌داده)** | ۱۴/۱۴ |
| `tools/test_expert_scope_runtime.php` **(جدید، با پایگاه‌داده)** | ۶/۶ |
| `tools/test_it_expert_routing_offline.php` | ۱۲/۱۲ |
| `tools/test_assignee_callable_offline.php` | ۱۰/۱۰ |
| `tools/test_alarm_selfheal_offline.php` | ۲۹/۲۹ |
| `tests/test_ticket_routing_unit.php` | ۴۵/۴۵ |
| `tools/test_access_coverage_offline.php` | ۵۴/۵۴ |
| `tools/test_role_permissions_offline.php` | ۲۸/۲۸ |
| `tools/test_module_grant_offline.php` | ۴۱/۴۱ |
| `tools/test_release_notes_offline.php` | ۳۶/۳۶ |
| `tools/test_schema_baseline_offline.php` | ۲۸/۲۸ |
| `tools/test_explain_alarms_offline.php` | ۲۴/۲۴ |
| `tools/test_selftest_offline.php` | ۴۳/۴۳ |
| `tools/test_1_62_97_drift_offline.php` | ۲۹/۲۹ |
| `tools/test_backfill_auto_receipts_offline.php` **(جدید)** | ۴۳/۴۳ |
| `tools/test_orphan_ticket_offline.php` **(جدید)** | ۴۱/۴۱ |
| `tests/test_release_notes_popup.js` | ۲۱/۲۱ |
| `tests/test_selftest_panel_render.js` | ۵۰/۵۰ |
| `tools/test_food_org_groups_offline.php` **(جدید، r14)** | ۵۶/۵۶ |
| `tools/test_cd_dvd_user_picker_offline.php` **(جدید، r14)** | ۲۰/۲۰ |
| `tools/test_asset_extraction_offline.php` **(جدید، r14)** | ۳۱/۳۱ |
| `tools/test_asset_profile_offline.php` | ۱۸/۱۸ |
| `tools/test_asset_names_offline.php` | ۵۰/۵۰ |
| `tools/test_login_and_asset_names_r15_offline.php` **(جدید، r15+r16+r17)** | ۸۳/۸۳ |
| `tools/test_asset_name_exact_offline.php` | ۴۶/۴۶ |
| `tools/test_asset_hostname_cleanup_offline.php` | ۳۷/۳۷ |

**بدون رگرسیون:** امضای هر ۵۰ آزمون آفلاین روی HEAD تمیز و روی درخت r14
گرفته و مقایسه شد. تنها تفاوت، رشد ۴۶→۵۶ در `test_food_org_groups_offline`
است (افزودن assertions تازه)؛ بقیه عیناً همان‌اند.

تست ارجاع **منطق واقعی** `choose_handling_agent()` را از سورس بیرون می‌کشد و
اجرا می‌کند — نه یک بازنویسی موازی. اگر کسی فردا دوباره شرط سیستمی را برگرداند،
تست قرمز می‌شود.

---

## ۸.۸) چرخهٔ کامل صف چاپ — آزمون با چاپگر واقعی (r12)

این بار سامانه با یک **چاپگر حرارتی شبیه‌سازی‌شده روی TCP 9100** اجرا شد
(نه استاب). هر بایت واقعاً از سوکت رد شد.

### ۸.۸.۱) فیشِ «در صف چاپ» بی‌چاپ «چاپ‌شده» می‌شد

کاربر «ثبت دستی با فیش» را با تیک «چاپ فیزیکی» می‌زد. فیش در صف قرار
می‌گرفت (`pending`)، ولی به‌محض باز شدن پنل، هم‌ترازی آن را `delivered`
می‌کرد. بعد Worker محافظِ خودش را می‌دید و بی‌درنگ `printed` می‌کرد —
یعنی فیش در سامانه «چاپ‌شده» می‌شد ولی **هیچ کاغذی از چاپگر بیرون
نمی‌آمد**.

**حالا:** هم‌ترازی ردیفِ `pending` را دست نمی‌زند؛ تحویل فقط پس از چاپ
موفقِ واقعی در Worker ثبت می‌شود.

### ۸.۸.۲) سفارش شام دیده نمی‌شد

`food_order_internal_map()` در SQL خود `AND o.meal_type = 'lunch'` داشت.
نتیجه: برای شام (و ناهار پنجشنبه/جمعه/تعطیل که خودکار صادر می‌شود) نقشه
خالی برمی‌گشت و ثبت دستی می‌گفت «سفارش فعال ناهار پیدا نشد».

**حالا:** هر دو وعده خوانده می‌شود. اگر کسی هر دو را دارد، **ناهار اولویت**
دارد و شام فقط برای کسی که ناهار ندارد استفاده می‌شود.
`food_order_internal_food_for_employee()` هم همین قاعده را دارد و دیگر
برای دو وعده خطای «چند سفارش فعال» نمی‌دهد.

### ۸.۸.۳) نتیجهٔ آزمون سرتاسری با چاپگر واقعی

| گام | نتیجه |
|---|---|
| ۳ سفارش ناهار روز جمعه | ✅ |
| چاپ خودکار (holiday=true) | ✅ ۳ فیش ساخته شد |
| ثبت دستی با فیش + چاپ فیزیکی | ✅ `pending/pending` |
| ۳ بار بازخوانی پنل | ✅ همچنان `pending/pending` |
| اجرای Worker | ✅ `printed=1` + **بایت‌های واقعی روی سوکت چاپگر** |
| فیش پس از چاپ | ✅ `printed/delivered` |
| خرابی چاپگر (پورت خاموش) | ✅ `failed` + خطا ثبت + تحویل `pending` |
| «چاپ مجدد خطاها» پس از رفع خرابی | ✅ چاپ شد + `printed/delivered` |

آزمون تکمیلی: ۵ تلاش پیاپی تا سقف، سپس توقف (بدون گرسنگی صف)؛
`held_absent` هنگام اعلام غیبت و بازگشت خودکار به صف هنگام «حاضر».

---

## ۸.۹) انتخاب‌گر «شخص واردکننده» — کنترل CD/DVD

**گزارش شما:** «اسم شخص رو که مینویسم، فقط یک حرف قبول میکنه.»

**ریشه:** فیلد «شخص واردکننده» یک `<select>` بومی بود. `<select>` بومی
جست‌وجوی میان‌متن ندارد — فقط با پیشوندِ نام گزینه‌ها تطبیق می‌دهد. پس
تایپ حرف دوم فیلتر را بازنشانی می‌کرد و فهرست به ابتدا برمی‌گشت.

**حالا:** فیلد متنی + فهرست دکمه‌ای. هر تعداد حرف تایپ کنید، فیلتر روی
**نام، نام خانوادگی، نام کاربری و واحد** اعمال می‌شود.

| گام | پیش از r14 | حالا |
|---|---|---|
| تایپ «ر» | فقط گزینه‌های شروع‌شده با «ر» | ۱۶ نفر، از جمله علی رضایی |
| تایپ «رضایی» | فهرست ریست می‌شد | ۱ نفر |
| «رضايي» با ی عربی | پیدا نمی‌شد | ۱ نفر (نرمال‌سازی) |
| تایپ «فناوری» | چیزی پیدا نمی‌شد | ۱ نفر (جست‌وجو در واحد) |
| حرف‌به‌حرف ادامه دادن | هر حرف فیلتر را می‌بست | هرگز بازنشانی نمی‌شود |

فایل‌ها: `cd-dvd.php` (فرم زندهٔ `data-cd-dvd-panel="new"`)، `assets/cd-dvd.js`.
آزمون: `tools/test_cd_dvd_user_picker_offline.php` — ۲۰/۲۰.

---

## ۸.۱۰) گزارش‌ها بر اساس معاونت — تیتر فقط نام

**گزارش شما:** «کافی تیترش اسم معاونت باشه و بینشون دیگه هیچی نباشه …
مثل نمایش بر اساس منو.»

### ۸.۱۰.۱) ساختار تازه

```
معاونت فناوری اطلاعات                ۴ فیش
────────────────────────────────────────────
                    [چلوکباب ۴]
رضا محمدی     چلوکباب    ۴۰۱۰۴    تحویل‌شده
سارا کریمی    چلوکباب    ۴۰۱۰۲    تحویل‌شده
علی رضایی     چلوکباب    ۴۰۱۰۳    تحویل‌شده
مهدی احمدی    چلوکباب    ۴۰۱۰۱    تحویل‌شده
```

**حذف شد:** برچسب نوع (معاونت / مدیریت مستقل / شرکت)، مسیر اداره‌ها،
نوار چیپ خلاصه، و فوتر مجموع. بین تیتر و فهرست اعضا **هیچ چیز** نیست.

**واحد مستقل** فقط نام خودش را می‌نویسد — «واحد بازرسی»،
«واحد مستقل نمایندگی شیراز» — نه برچسب نوع.

### ۸.۱۰.۲) تفکیک غذای هر معاونت

تابع تازهٔ `foodBreakdown()` بالای فهرست هر معاونت، شمارش هر غذا را
می‌گذارد: «چلوکباب ۴ · قیمه ۲»، مرتب از پرشمارتر.

| گزارش | منبع غذا | واحد شمارش |
|---|---|---|
| غذای تحویلی | `food_name` | فیش |
| سفارش‌های امروز | `food_name` | سفارش |
| تجمیعی | `food` | ردیف |
| غایبین ناهار | `food` | نفر |

غایبین هم گروه‌بندی می‌شوند: می‌بینید غایبِ کدام معاونت است
(«معاونت فناوری اطلاعات — ۲ نفر»).

### ۸.۱۰.۳) یک باگ واقعی که در همین مسیر پیدا شد

`loadOrgMap()` و `aggOrgList()` در `food-ticket-web/index.html` در دو جا
(`:707` و `:708`) **صدا زده می‌شدند ولی هیچ‌جا تعریف نشده بودند.**
یعنی تب تجمیعی و غایبین در پنل وب غذا با خطای JavaScript متوقف می‌شدند.
هر دو تابع نوشته و اضافه شدند؛ هر سه بلوک `<script>` سالم‌اند.

فایل‌ها: `assets/food-org-groups.js`، `assets/food-menu.css`،
`assets/food-menu.js`، `food-ticket-web/index.html`، `lib/org-grouping.php`.
آزمون: `tools/test_food_org_groups_offline.php` — ۵۶/۵۶.

---

## ۸.۱۱) شناسنامهٔ فنی — سه ایراد و تکمیل

**گزارش شما:** «شناسنامه‌های فنی رو هم دقیق تکمیل کن.»

| # | ایراد | ریشهٔ واقعی | حالا |
|---|---|---|---|
| ۱ | استخراج با خطا تمام می‌شد | برخورد کلید یکتای هویت هنگام ثبت | کلید مقاوم شد |
| ۲ | نام کامپیوتر بازنویسی می‌شد | نوشتن `hostname` در اسکن ⇒ `1062 … uq_assets_hostname_key` | هرگز نوشته نمی‌شود |
| ۳ | سریال/مدل/کیس خالی بود | نگاشت ناقص + `case_model` دستی | خودکار پر می‌شود |

### ۸.۱۱.۱) تکمیل فیلدها

- `case_model` از `Win32_SystemEnclosure` مشتق می‌شود — سه مسیر جایگزین:
  `hardware.system_enclosure` / `hardware.enclosure` / `hardware.extended_inventory.enclosure`.
- `power_model` **دستی ماند** — منبع WMI ندارد؛ خودکار کردنش دروغ بود.
- `asset_profile_computer_type()` تازه: نوع کامپیوتر از **OS هدف** مشتق می‌شود
  (Windows client / Windows Server از روی `ProductType`)، نه از `PHP_OS_FAMILY`
  میزبان PHP. قبلاً روی سرور لینوکس همه «Linux Server» می‌شدند.
- نگاشت `hardware.system_enclosure` و `hardware.system_product` به `inventory.php`.

### ۸.۱۱.۲) نتیجهٔ اجرای واقعی

| | |
|---|---|
| فیلدهای پر | **۳۰** |
| خالی | فقط `netcard` |
| `computer_type` | `Windows client` (قبلاً به‌غلط `Linux Server`) |

نمونهٔ مقادیر: `machine_serial CZC1234ABC` · `machine_model HP EliteDesk 800 G6` ·
`case_model HP 103C_53307F G-D` · `motherboard_product 8704` · `cpu_model i7-10700` ·
`ram_total 16384 MB` · `disk1 Samsung SSD 970 / 476.9 GB` · `mac A4:BB:6D:12:34:56`.

فایل‌ها: `asset-profile.php`، `inventory.php`، `domain-scan.php`، `asset-names.php`.
آزمون‌ها: `test_asset_extraction_offline` ۳۱/۳۱ · `test_asset_profile_offline` ۱۸/۱۸.

### ۸.۱۱.۳) مهاجرت لازم نیست

`asset_profile_ensure_schema()` ستون‌ها را خودش می‌سازد:
`CREATE TABLE IF NOT EXISTS asset_profiles` + `ALTER TABLE … ADD COLUMN` با
بررسی `information_schema`. اجرای مکرر بی‌خطر است. ۷۷ ستون شناسنامه پوشش
داده شده‌اند. **هیچ فایل SQL تازه‌ای لازم نیست.**

---

## ۸.۱۲) «درخواست نامعتبر است» — چرا و چه شد

### ۸.۱۲.۱) ریشه

شما یک بار سامانه را با **HTTPS** باز کردید. مرورگر کوکی نشست را با پرچم
`Secure` ذخیره کرد. بعد که با **HTTP** باز کردید، مرورگر آن کوکی را
**نمی‌فرستد** (قانون خود مرورگر است، نه باگ سامانه). نتیجه:

```
HTTPS:  مرورگر کوکی Secure می‌گیرد
HTTP :  همان کوکی فرستاده نمی‌شود ⇒ PHP نشست تازه می‌سازد
        صفحهٔ قدیمی توکن قدیمی را در فرم دارد
        ارسال فرم ⇒ توکن ناهم‌خوان ⇒ «درخواست نامعتبر است»
```

و چون پاسخ قبلی یک صفحهٔ ۴۱۹ تمام‌شده بود، **راه برگشتی نبود** — بن‌بست.

### ۸.۱۲.۲) سه اصلاح

**الف) دیگر بن‌بست نیست.** به‌جای صفحهٔ ۴۱۹، کاربر با پیام روشن به همان
فرمی برمی‌گردد که داشت پر می‌کرد و توکن تازه کاشته می‌شود:

> «به‌دلیل پایان اعتبار نشست امنیتی، آن عملیات انجام نشد. صفحه تازه شد؛
> همین کار را یک‌بار دیگر بزنید.»

**ب) محافظت CSRF دست‌نخورده است.** درخواست مشکوک **اجرا نمی‌شود** — با
توکن غلط تست شد و ورود انجام نشد. فقط مسیر «بازگشت به فرم» عوض شد، نه
درستی‌سنجی. مسیرهای API و AJAX همان ۴۱۹ با بدنهٔ JSON می‌گیرند.

**ج) شناسهٔ نشست ورودی پذیرفته می‌شود.** اگر مرورگر کوکی‌ای فرستاد که
شناسه‌اش معتبر است، همان شناسه استفاده می‌شود و پرچم `Secure` برداشته
می‌شود؛ یعنی بازگشت از HTTPS به HTTP دیگر نشست را نمی‌شکند.

### ۸.۱۲.۳) کاری که خودتان باید بکنید

اگر همین حالا گیر کرده‌اید، یکی از این دو:

- **بهترین:** همان آدرس HTTPS را ادامه دهید. (`https://...`)
- **یا:** یک بار کوکی‌های سایت را در مرورگر پاک کنید (یا حالت ناشناس).

بعد از نصب این بسته، این حالت دیگر بن‌بست نمی‌سازد.

---

## ۸.۱۳) ورود دامینی — چرا رد می‌شد

### ۸.۱۳.۱) پیام قبلی دروغ می‌گفت

قبلاً **هر** شکستی — حتی وقتی دامنه اصلاً پیکربندی نشده بود — پیام
«نام کاربری یا رمز عبور صحیح نیست» می‌داد. شما رمزتان را عوض می‌کردید و
باز رد می‌شدید، چون مشکل اصلاً رمز نبود.

### ۸.۱۳.۲) سه علت واقعی — و پیام تازهٔ هرکدام

| علت | پیام تازهٔ سامانه |
|---|---|
| ورود دامینی در تنظیمات خاموش است | «ورود دامینی فعال نیست: در تنظیمات ← دامنه (LDAP) گزینهٔ فعال و آدرس سرور را پر کنید. ورود محلی (ادمین) کار می‌کند.» |
| پسوند LDAP در PHP بارگذاری نشده | «پسوند LDAP در PHP فعال نیست؛ ورود دامینی ممکن نیست. در php.ini خط `extension=ldap` را فعال و سرویس را ری‌استارت کنید.» |
| آدرس سرور دامنه خالی است | «آدرس سرور دامنه (LDAP) در تنظیمات خالی است.» |
| همین نام کاربری به‌صورت «محلی» ثبت شده | «این نام کاربری روی سامانه به‌صورت «محلی» ثبت شده است، نه دامینی.» |
| سرور دامنه در دسترس نیست | «... یا سرور دامنه در دسترس نیست. جزئیات فنی در `storage/logs/app.log` با برچسب `ITSM LDAP authentication` ثبت شد.» |

### ۸.۱۳.۳) چرا ادمین محلی کار می‌کرد

انتخاب حساب محلی و دامینی **دو مسیر جدا** دارد:

```
مسیر ۱: users WHERE username = ? AND auth_source = 'local'
        ⇐ ادمین محلی اینجا پیدا می‌شود (به LDAP کاری ندارد)
مسیر ۲: ldap_authenticate()  ⇐ فقط اگر مسیر ۱ شکست بخورد
```

پس «ادمین کار می‌کند ولی دامینی نه» یعنی مسیر ۲ شکسته بوده — یا خاموش، یا
بدون پسوند، یا بدون آدرس، یا شبکه بسته. ابزار تشخیص دقیقاً می‌گوید کدام.

---

## ۸.۱۴) `client 192.168.1.25` — چرا هنوز می‌ماند

این سومین بار است که گزارش می‌کنید، پس باید صادق باشم: **دو اصلاح قبلی
کافی نبودند.**

### ۸.۱۴.۱) آنچه قبلاً (اشتباه) فرض شده بود

اصلاح قبلی «نمایش» را درست کرد (`asset_name_core`) و آزمونش هم سبز بود —
ولی فقط **هنگام نمایش**. مقدار جعلی در ستون `assets.hostname` می‌ماند.

و بدتر: یک محدودیت عمدی هم وجود داشت — «نام موجود را حفظ کن، مگر رکورد
نام نداشته باشد». یعنی اگر رکورد **اولین بار** با نام جعلی ساخته می‌شد،
آن نام جعلی **هرگز** با نام واقعی جایگزین نمی‌شد.

### ۸.۱۴.۲) دو باگ واقعی که پیدا شد

**باگ ۱ — حالت `full` رشتهٔ خام را برمی‌گرداند.**
در `asset_display_name()`، اگر «ظاهر نام کامپیوترها» روی «نام کامل دامنه»
بود، تابع همان مقدار خام را بی‌هیچ پاک‌سازی برمی‌گرداند. یعنی قاعدهٔ «IP
هرگز در نام دیده نمی‌شود» به یک تنظیم ظاهری وابسته بود. **رفع شد.**

**باگ ۲ — نام واقعی ویندوز خوانده می‌شد ولی ذخیره نمی‌شد.**
PowerShell نام واقعی ماشین را می‌آورد (`Win32_ComputerSystem.Name`)، شناسنامه
هم آن را داشت، ولی مسیر ذخیره (`domain-scan.php`) آن را نادیده می‌گرفت و
همان نام جعلی AD را نگه می‌داشت. **رفع شد:** نام واقعی ویندوز بالاترین
اولویت را دارد.

### ۸.۱۴.۳) قاعدهٔ تازه

```
اگر نام واقعی ویندوز هست        ⇒ همان نوشته می‌شود
وگرنه اگر نام جاری «واقعی» است   ⇒ دست نمی‌خورد
وگرنه اگر نام صف واقعی است      ⇒ آن نوشته می‌شود
وگرنه                           ⇒ نام جاری می‌ماند (چیزی خراب نمی‌شود)
```

«نام جعلی» یعنی: IP خالص، یا `CLIENT-<ip>`، یا مقداری که IP داخلش چسبیده.
و در نمایش، **هیچ‌کدام** دیگر دیده نمی‌شوند:

| مقدار در پایگاه‌داده | آنچه می‌بینید |
|---|---|
| `client 192.168.1.25` | `client` |
| `CLIENT-192-168-1-25` | نام ثبت نشده |
| `192.168.1.99` | نام ثبت نشده (آدرس در tooltip) |
| `MBA4-01` | `MBA4-01` — دقیقاً همان، بدون یک حرف کم یا زیاد |

### ۸.۱۴.۴) اصلاح رکوردهای موجود

بسته فقط جلوی رکورد جدید را می‌گیرد. برای رکوردهای فعلی، دو فرمان:

```
php tools\\normalize_asset_hostnames.php --from-inventory          گزارش
php tools\\normalize_asset_hostnames.php --from-inventory --apply   اعمال
```

`--from-inventory` تازه است: نام واقعی را از `hardware_json` برمی‌دارد
(اگر اسکن آن را ثبت کرده باشد). پیش‌تر فقط IP را از نام می‌برد و
`clint 192.168.1.65` را به `clint` تبدیل می‌کرد — که هنوز نام واقعی نیست.

برای رکوردی که **هیچ‌وقت** نام واقعی‌اش خوانده نشده، یک‌بار اسکن دامنه را
برای همان سیستم‌ها اجرا کنید؛ این بار نام واقعی ویندوز ذخیره می‌شود.

---

## ۸.۱۵) کار با هر دو پروتکل http و https

### ۸.۱۵.۱) چرا این مشکل پیش آمد

دو چیز جدا، هر دو از سمت مرورگر:

**۱) پرچم `Secure` روی کوکی نشست.**
وقتی یک بار با HTTPS وارد می‌شوید، مرورگر کوکی نشست را با پرچم `Secure`
ذخیره می‌کند. آن پرچم یعنی «این کوکی را فقط روی HTTPS بفرست». پس روی
`http://` مرورگر آن را **نمی‌فرستد** — این رفتار خودِ مرورگر است و هیچ کدی
نمی‌تواند عوضش کند. نتیجه: نشست تازه، توکن کهنه، «درخواست نامعتبر است».

**۲) HSTS.**
سامانه هدر `Strict-Transport-Security: max-age=31536000; includeSubDomains`
می‌فرستاد — یعنی **یک سال**، و همهٔ زیردامنه‌ها. مرورگر آن را ذخیره می‌کند و
از آن پس خودش هر `http://` را به `https://` پرت می‌کند. **پاک‌کردن کوکی هم
کافی نیست**؛ این حالت جدا از کوکی‌ها ذخیره می‌شود.

### ۸.۱۵.۲) راه‌حل — یک چک‌باکس

**تنظیمات ← اطلاعات سامانه ← «کار با هر دو پروتکل http و https با همان ورود»**

| حالت | چه می‌شود |
|---|---|
| **خاموش** (پیش‌فرض) | کوکی فقط روی HTTPS. امن‌ترین. اگر فقط از HTTPS استفاده می‌کنید، همین را نگه دارید. |
| **روشن** | کوکی روی هر دو پروتکل فرستاده می‌شود. ورود با http و https هر دو کار می‌کند و یک نشست مشترک دارید. |

### ۸.۱۵.۳) هزینهٔ صادقانه

این یک **معاوضهٔ امنیتی** است، نه تنظیم بی‌هزینه:

> روی `http://`، کوکی نشست رمزنگاری نمی‌شود و در شبکهٔ محلی برای هر کسی که
> ترافیک را می‌بیند قابل خواندن است — یعنی می‌تواند نشست شما را بردارد و
> جای شما وارد شود.

پس این گزینه برای **شبکهٔ داخلی مطمئن** است. اگر از اینترنت دسترسی دارید،
خاموش بگذارید و همه‌جا از HTTPS استفاده کنید. همین هشدار داخل خودِ صفحه هم
نوشته شده.

### ۸.۱۵.۴) HSTS هم کنترل‌پذیر شد

مقدار قبلی یک سال بود و گذاشتنش اشتباه بود — چون اگر کاربر یکی بار با HTTPS
بیاید و بعد بخواهد http را امتحان کند، یک سال قفل می‌شد. حالا:

| در `config.php` | هدر | کاربرد |
|---|---|---|
| `'hsts' => 'short'` **(پیش‌فرض تازه)** | `max-age=300` | محافظت دارد، ولی اگر اشتباه شد در ۵ دقیقه برمی‌گردد |
| `'hsts' => 'off'` | هیچ | برای کسی که صریحاً هم http و هم https می‌خواهد |
| `'hsts' => 'standard'` | `max-age=31536000` | یک سال، بدون زیردامنه‌ها |
| `'hsts' => 'strict'` | `max-age=31536000; includeSubDomains` | رفتار قبلی |

اگر صریحاً هر دو پروتکل را می‌خواهید، `off` را بگذارید:

```php
'security' => [
    'hsts' => 'off',
    ...
],
```

### ۸.۱۵.۵) اگر همین حالا گیر کرده‌اید

HSTS ممکن است مرورگر شما را قفل کرده باشد. راه‌های خروج:

**Chrome / Edge:** آدرس `chrome://net-internals/#hsts` → بخش
*Delete domain security policies* → دامنه را بنویسید و Delete

**Firefox:** `about:preferences#privacy` → پایین صفحه History →
*Clear…* (یا از `about:support` پروفایل را باز کنید)

**ساده‌ترین راه:** دامنه را با یک نام دیگر یا IP باز کنید — HSTS به نام
دامنه بسته است، نه به سرور.

### ۸.۱۵.۶) به‌جای این کار، راه بهتر

اگر هدف «دسترسی از چند جا» است و می‌توانید سرور را تنظیم کنید، این بهتر است:

- روی وب‌سرور یک **گواهی خودامضا یا داخلی** بگذارید (IIS: *Server
  Certificates* → *Create Self-Signed Certificate* → *Bindings*).
- یک بار آن را در مرورگر بپذیرید.
- از HTTPS برای همه استفاده کنید، http را کلاً ببندید.

این هم امن است و هم هیچ معاوضه‌ای ندارد. ولی اگر نمی‌توانید، چک‌باکس بالا
کار را راه می‌اندازد.

---

## ۹) یک هشدار صادقانه

هشدار ۵.۳ (کرون) تا وقتی `register_system_alarm_tasks.bat` را اجرا نکنید باقی
می‌ماند. کد نمی‌تواند خودکار حلش کند چون ثبت وظیفه در Windows Task Scheduler
نیاز به دسترسی مدیر ویندوز دارد و از داخل PHP صفحهٔ وب قابل انجام نیست.
"""
    return (
        tpl.replace("{RELEASE_VERSION}", RELEASE_VERSION)
        .replace("{REQUIRES_VERSION}", REQUIRES_VERSION)
        .replace("{RELEASE_DATE}", RELEASE_DATE)
    )


if __name__ == "__main__":
    main()
