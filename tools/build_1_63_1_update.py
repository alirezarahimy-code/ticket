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
import subprocess
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.63.1"
REQUIRES_VERSION = "1.63"
RELEASE_DATE = "2026-11-06"
PACKAGE_NAME = "ticket-update-1.63-to-1.63.1-r11-cancel-fix-20261115.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.63.1.md"
ZIP_DATE = (2026, 11, 6, 3, 0, 0)

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
    "CHANGE-REPORT-FA-1.63.1.md",
    "docs/GUIDE-FA-install-1.63.1.md",           # راهنمای گام‌به‌گام نصب
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
}

# این‌ها فقط برای آزمون‌اند و مسیر runtime ندارند؛ در بسته نمی‌آیند اما
# بازسازی‌پذیرند و در مخزن می‌مانند.
FORBIDDEN_IN_PACKAGE = ("downloads/", "patches/", "updates/", "preview/",
                        "docs/system-map/", "ci-diagnostics/", "benchmark-results/")

TITLE = (
    "نسخهٔ ۱.۶۳.۱ — نقش اعطایی، مسئول تیکت، آژیر خودترمیم، "
    "و اصلاح کامل چرخهٔ فیش غذا (لغو انتخابی، غایب/فیش‌دار، ثبت دستی)"
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
    "No schema change, no migration, no new permission."
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
        "name": "cancel-fix-r11",
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
    with zipfile.ZipFile(package_path, "w", zipfile.ZIP_DEFLATED) as zf:
        def add(name: str, data: bytes) -> None:
            info = zipfile.ZipInfo(name, date_time=ZIP_DATE)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o644 << 16
            zf.writestr(info, data)

        add("manifest.json", manifest_bytes)
        for name, data in sorted(payload.items()):
            add("files/" + name, data)

    checksum = sha256_path(package_path)
    (out_dir / (PACKAGE_NAME + ".sha256")).write_text(
        f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii"
    )
    (out_dir / REPORT_NAME).write_bytes(report.encode("utf-8"))
    (ROOT / "VERSION").write_text(RELEASE_VERSION + "\n", encoding="utf-8")

    print(f"OK {PACKAGE_NAME}")
    print(f"Files: {len(payload)} | migrations: 0 | bytes: {package_path.stat().st_size}")
    print(f"SHA-256: {checksum}")


def build_report() -> str:
    tpl = """# گزارش تغییرات ۱.۶۳.۱

نسخه: **{RELEASE_VERSION}** · پیش‌نیاز: **{REQUIRES_VERSION}** · تاریخ: **{RELEASE_DATE}**
مهاجرت: **ندارد** · وضعیت: **RELEASE**

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

تست ارجاع **منطق واقعی** `choose_handling_agent()` را از سورس بیرون می‌کشد و
اجرا می‌کند — نه یک بازنویسی موازی. اگر کسی فردا دوباره شرط سیستمی را برگرداند،
تست قرمز می‌شود.

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
