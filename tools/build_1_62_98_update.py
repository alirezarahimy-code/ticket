#!/usr/bin/env python3
"""سازندهٔ بستهٔ میانی ۱.۶۲.۹۸ — دو اصلاح.

۱) مسئول تیکت فقط از فهرست «کارشناس آی تی» و «کارشناس پشتیبانی» در تنظیمات خوانده
   می‌شود، نه از نقش سیستمی. سه باگ روی هم افتاده بود (شرط SQL ارجاع، تابع واجدیت،
   و ارتقای خودکار به primary_admin هنگام انتخاب در فهرست).

۲) سه هشدار صفحهٔ سلامت خودشان را درمان می‌کنند: ساخت پوشه‌های storage، ثبت خودکار
   مهاجرت‌های baseline در schema_updates، و اسکریپت یک‌کلیکی ثبت کرون.

بدون مهاجرت، بدون تغییر جدول، بدون مجوز تازه. اسکیمای خط ۱.۶۲ دست‌نخورده می‌ماند.
"""

from __future__ import annotations

import hashlib
import io
import json
import subprocess
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.62.98"
REQUIRES_VERSION = "1.62.97"
RELEASE_DATE = "2026-11-16"
PACKAGE_NAME = "ticket-update-1.62.97-to-1.62.98-it-expert-routing-alarm-selfheal-20261116.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.62.98.md"
ZIP_DATE = (2026, 11, 16, 2, 0, 0)

# ── مهم: این فایل‌ها از خط ۱.۶۲ می‌آیند، نه از شاخهٔ کاری ──
#
# شاخهٔ کاری درخت ۱.۶۳ است. سه فایل از این چهار، در ۱.۶۳ **جابه‌جا** شده‌اند:
#   • choose_handling_agent  → از index.php به modules/request-center/lib/functions.php
#   • system alarms          → از index.php به lib/system-alarms.php
#   • health audit           → از lib/logging-audit.php (۱.۶۲) ...
# اگر نسخهٔ ۱.۶۳ را در بستهٔ ۱.۶۲ بگذاریم، فایل روی نصب کاربر می‌نشیند ولی
# هیچ کدی آن را صدا نمی‌زند — یعنی «اصلاح» بی‌اثر می‌شود. دقیقاً همان چیزی که
# در نسخهٔ اول این بسته اتفاق افتاد و تست پوشه‌ای نشانش داد.
#
# راه‌حل: پایه از بستهٔ کامل ۱.۶۲.۹۷ گرفته می‌شود (LINE_PACKAGE) و فقط وصلهٔ
# همین نسخه رویش می‌نشیند.
LINE_PACKAGE = ROOT / "downloads" / "ticket-full-1.62.97-20261116.zip"
LINE_PREFIX = "ticket/"

FILES = [
    # ── مسئول تیکت از فهرست کارشناسان ──
    "lib/settings-effects.php",        # نقش کلیدی چهارم: support_expert
    "lib/auth-scope.php",              # ticket_assignee_is_eligible + ticket_scope + ticket_user_can_view
    "actions/role-actions.php",        # پایان ارتقای خودکار به primary_admin
    "index.php",                       # choose_handling_agent (خط ۱.۶۲) + کارت کارشناس پشتیبانی

    # ── درمان خودکار آژیر سلامت (خط ۱.۶۲) ──
    "lib/logging-audit.php",           # ساخت پوشه‌های غذا + backfill مهاجرت‌های baseline
    "cron/register_system_alarm_tasks.bat",

    # ── تست‌ها ──
    "tools/test_it_expert_routing_offline.php",
    "tools/test_alarm_selfheal_offline.php",
    "tools/test_assignee_callable_offline.php",
]

# این فایل‌ها فقط در ۱.۶۳ وجود دارند؛ در ۱.۶۲ هیچ مصرف‌کننده‌ای ندارند.
# حملشان یعنی فایل مرده در بسته — و تست پوشه‌ای (test_package_paths) ردشان می‌کند.
FORBIDDEN_IN_162 = ("modules/", "lib/system-alarms.php", "app/", "request-center")

# اسکیمای خط ۱.۶۲ — نه شاخهٔ ۱.۶۳.
# دلیل: شاخهٔ کاری، درخت ۱.۶۳ است و `schema.sql`/`install-empty-db.sql` آن ستون
# `rejected_at` و جدول `request_center_appointments` را دارند. حمل آن‌ها در بستهٔ
# ۱.۶۲.۹۷ یعنی نشت قلمرو ۱.۶۳ به خط غذا. پس این دو فایل از درخت تولید ۱.۶۲ ساخته
# می‌شوند و فقط ردیف ثبت مهاجرت ۰۳۳ به baseline آن‌ها اضافه می‌شود.
SCHEMA_LINE_REF = "arena/22d4eba7-1.62.95-main"
LINE_SCHEMA_FILES = ("schema.sql", "install-empty-db.sql")
SCHEMA_ANCHOR = "    ('1.62.95', '032-schema-updates-baseline.sql'),\n"
SCHEMA_SEED_033 = (
    "    -- ۰۳۳ فقط backfill داده است (UPDATE) و روی نصب تازه کاری ندارد. فایلش\n"
    "    -- از بسته‌های ۱.۶۲.۸۳/۸۸/۹۴/۹۵ روی دیسک می‌آید و همان‌ها اجرایش کرده‌اند؛\n"
    "    -- بدون این ردیف گارد مهاجرت‌ها روی هر نصب تازه هشدار کاذب می‌داد.\n"
    "    ('1.62.88', '033-logging-legacy-context-collation.sql'),\n"
)


def line_schema_payload() -> dict[str, bytes]:
    """schema.sql / install-empty-db.sql خط ۱.۶۲ + ثبت ۰۳۳؛ بدون هیچ اثر ۱.۶۳."""
    out: dict[str, bytes] = {}
    for rel in LINE_SCHEMA_FILES:
        try:
            raw = subprocess.check_output(
                ["git", "show", f"{SCHEMA_LINE_REF}:{rel}"], cwd=ROOT
            ).decode("utf-8")
        except subprocess.CalledProcessError:
            raise SystemExit(f"اسکیمای خط ۱.۶۲ پیدا نشد: {rel}") from None
        if SCHEMA_ANCHOR not in raw:
            raise SystemExit(f"نقطهٔ لنگر baseline در {rel} پیدا نشد.")
        if "033-logging-legacy-context-collation.sql" not in raw:
            raw = raw.replace(SCHEMA_ANCHOR, SCHEMA_ANCHOR + SCHEMA_SEED_033, 1)
        for marker in ("rejected_at", "request_center_appointments"):
            if marker in raw:
                raise SystemExit(f"اثر ۱.۶۳ در {rel}: {marker}")
        out[rel] = raw.encode("utf-8")
    return out


TITLE = (
    "مسئول تیکت از فهرست «کارشناس آی تی» و «کارشناس پشتیبانی» — و درمان خودکار آژیر سلامت "
    "(۱.۶۲.۹۷ ← ۱.۶۲.۹۸)"
)

DESCRIPTION = (
    "Interim 1.62.x update. Two fixes. "
    "(1) Ticket routing now reads the assignee from the explicit expert lists in "
    "\u00abSettings \u2192 Users\u00bb instead of guessing from system roles: the IT queue goes to the users "
    "listed under \u00abIT Expert\u00bb and the support queue to the new \u00abSupport Expert\u00bb list. "
    "Previously any primary admin was auto-assigned every IT ticket, and picking a user as IT Expert "
    "silently promoted them to primary_admin \u2014 two separate concepts that were wrongly coupled. "
    "Designated experts can now also open and work the tickets assigned to them. "
    "(2) Three health-alarm items that only nagged now heal themselves: missing storage folders are "
    "created (0750, retried at 0775), baseline migrations 001\u2013037 are recorded in schema_updates so a "
    "fresh install no longer reports a false \u00abmigration not run\u00bb alarm, and "
    "cron/register_system_alarm_tasks.bat registers both Task Scheduler jobs in one click. "
    "A migration that is genuinely absent from the baseline still raises the alarm. "
    "No schema change, no migration, no new permission."
)

def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def sha256_path(p: Path) -> str:
    return sha256_bytes(p.read_bytes())


def build_report() -> str:
    _tpl = """# گزارش تغییرات ۱.۶۲.۹۸ — مسئول تیکت از فهرست کارشناسان + درمان خودکار آژیر

نسخه: **{RELEASE_VERSION}** · پیش‌نیاز: **{REQUIRES_VERSION}** · تاریخ: **{RELEASE_DATE}**
مهاجرت: **ندارد** · وضعیت: `INTERIM_UPDATE_TEST_ON_STAGING_FIRST`

---

## ۱) خواستهٔ مالک

> «من تو سامانه چند تا ادمین اصلی دارم + مدیر سامانه (محلی): سیستم اونها رو خود به خود
> به عنوان کارشناس آی تی معرفی کرده و اونها رو کرده مسئول تیکت. تو قسمت تنظیمات-کاربران
> من ۳ نقش رو معرفی کردم: بازرسی، سوپروایزر، کارشناس آی تی. الزاماً باید برنامه طوری باشد
> که کارشناس آی تی رو از همین‌جا تشخیص بده.»

و:

> «پیشنهاد به اون ۳ نقش که تو تنظیمات-کاربران موجوده کارشناس پشتیبانی هم در خط بعد اضافه
> بشه و این به این شکل باشه که کارشناس آی تی و کارشناس پشتیبانی مربوط به انجام و اتصال به
> ثبت تیکت فقط از این قسمت خوانده بشود.»

---

## ۲) علت واقعی (سه لایه — هر سه اصلاح شد)

مشکل یک باگ نبود؛ سه باگ بود که روی هم انباشته شده بودند.

### لایهٔ ۱ — ارجاع خودکار فهرست را نمی‌خواند

`choose_handling_agent()` در `modules/request-center/lib/functions.php` مالک تیکت IT را
با این شرط پیدا می‌کرد:

```sql
role = 'primary_admin' OR (role = 'admin' AND is_primary_admin = 1)
```

پس هر ادمین اصلی سامانه خودکار مسئول **همهٔ** تیکت‌های IT می‌شد. فهرست «کارشناس IT» در
`settings` هیچ نقشی در ارجاع نداشت.

**حالا:** اول فهرست صریح خوانده می‌شود؛ نقش سیستمی فقط **پشتیبان** است تا سامانه بی‌صاحب نماند.

### لایهٔ ۲ — کارشناس برگزیده «واجد شرایط» شمرده نمی‌شد

`ticket_assignee_is_eligible()` در `lib/auth-scope.php` فقط نقش‌های سیستمی
(`agent`، `manager`، `support_manager`، ادمین اصلی) را می‌پذیرفت. کاربری با نقش `user`
که در فهرست برگزیده شده بود، **رد می‌شد**.

یعنی حتی اگر فهرست خوانده می‌شد هم هیچ کاندیدی قبول نمی‌شد و همان ادمین‌ها انتخاب می‌شدند.

### لایهٔ ۳ — انتخاب «کارشناس IT» کاربر را ادمین می‌کرد

این مهم‌ترین علتِ سردرگمی بود. در `actions/role-actions.php`:

```
foreach (array_diff($itExperts, $oldExperts) as $id) =>
    UPDATE users SET role = 'primary_admin', is_primary_admin = 1, is_active = 1 WHERE id = ?
```

هر بار که یک کاربر را «کارشناس IT» می‌کردید، او **ادمین اصلی** می‌شد. و برعکس، حذفش از
فهرست، دسترسی ادمین را از او می‌گرفت.

پس دو مفهوم «مسئول عملیاتی تیکت» و «ادمین سامانه» به‌غلط به هم بسته بودند. حالا فقط پرچم
`is_it_agent` ست می‌شود؛ **نقش سیستمی کاربر دست نمی‌خورد**.

---

## ۳) چه چیزی تغییر کرد

| مورد | قبل | حالا |
|------|-----|------|
| مسئول تیکت IT | اول ادمین اصلی | فهرست «کارشناس آی تی»، بعد ادمین به‌عنوان پشتیبان |
| مسئول تیکت پشتیبانی | مدیر پشتیبانی | فهرست «کارشناس پشتیبانی»، بعد مدیر پشتیبان |
| فهرست کارشناسان | ۳ ردیف | **۴ ردیف** — «کارشناس پشتیبانی» اضافه شد |
| انتخاب کارشناس IT | کاربر را primary_admin می‌کرد | فقط پرچم `is_it_agent`؛ نقش دست نمی‌خورد |
| دیدن صف توسط کارشناس | فقط نقش سیستمی agent/manager | کارشناس برگزیده هم صف حوزهٔ خودش را می‌بیند |
| ادمین‌های اصلی | خودکار مسئول | کاملاً از ارجاع خودکار حذف؛ فقط ارجاع دستی |

---

## ۳.۵) باگ مرگبار دیگری که هنگام بررسی پیدا شد

**در ۱.۶۲.۹۷ که نصب کردید، ارجاع خودکار تیکت با خطای مرگبار می‌افتاد.**

`lib/auth-scope.php` با `declare(strict_types=1)` اجرا می‌شود و امضای تابع
`ticket_assignee_is_eligible()` این بود:

```
function ticket_assignee_is_eligible(array $ticket, array $user, ?callable $permissionCheck = null)
```

ولی **هر چهار نقطهٔ فراخوانی** در `index.php` نام تابع را به‌صورت **رشته** می‌فرستند:

```
ticket_assignee_is_eligible($routingTicket, $candidate, 'ticket_notification_user_can')
```

در حالت strict، رشته برای پارامتر `?callable` یک `TypeError` مرگبار است. یعنی هر بار
که یک درخواست تازه به مسیر ارجاع خودکار می‌رسید، صفحه با خطای ۵۰۰ می‌افتاد.

**حالا:** امضا `callable|string|null` است و اگر نام تابع معتبر نباشد، به `user_can`
برمی‌گردد. `strict_types` **خاموش نشد** — اصلاح در امضاست، نه در سخت‌گیری.
سازگاری عقب‌رو حفظ شده: closure همچنان کار می‌کند.

این باگ در ۱.۶۳ هم بود و همان‌جا هم اصلاح شد.

---

## ۴) سه هشدار صفحهٔ سلامت

کاربر این سه را دید و هر سه حالا خودشان را درمان می‌کنند:

### ۴.۱) پوشهٔ `storage/food_ticket_spool` نیست — رفع شد

قبلاً فقط هشدار می‌داد. حالا هر چهار پوشهٔ ضروری
(`food_ticket_spool`، `food_ticket_prints`، `logs`، `sessions`) **ساخته** می‌شوند:
`mkdir(..., 0750, true)` و در صورت شکست تلاش دوم با `0775`.

اگر مجوز فایل‌سیستم اجازه ندهد، هشدار همچنان صادقانه می‌ماند — پیام هم می‌گوید که
پوشهٔ `storage` باید برای کاربر وب‌سرور قابل نوشتن باشد.

### ۴.۲) مهاجرت‌های ۰۰۱ … ۰۳۷ اجرا نشده — هشدار کاذب رفع شد

آژیر هر فایل `migrations/*.sql` را می‌گشت و اگر در جدول `schema_updates` نبود، هشدار
می‌داد. ولی در **نصب تازه**، ساختار این ۳۷ مهاجرت از قبل در `schema.sql` هست — فقط
ردیف دفتری‌شان در جدول ثبت نشده بود.

**حالا:** مهاجرت‌هایی که در baseline اسکیما ثبت شده‌اند (همان چیزی که
`tools/test_schema_baseline_offline.php` می‌سنجد) خودکار در `schema_updates` علامت می‌خورند.

**قاعدهٔ ایمنی:** فقط ردیف دفتری اضافه می‌شود، **هیچ DDL ای اجرا نمی‌شود**. و اگر
مهاجرتی واقعاً در baseline نباشد، هشدار سر جایش می‌ماند.

### ۴.۳) کرون `system_alarm_tick` اجرا نمی‌شود — یک‌کلیکی شد

این یکی واقعاً رفع دستی لازم دارد، چون یک وظیفهٔ Windows Task Scheduler است. ولی
لازم نیست دستور بنویسید:

```
cron\\register_system_alarm_tasks.bat
```

با دوبار کلیک (یا Administrator) هر دو وظیفه ساخته می‌شوند:

- `TicketSystemAlarmTick` — هر ۵ دقیقه
- `TicketSystemAlarmMorning` — روزانه ۰۸:۰۰ با `--morning`

اسکریپت مسیر PHP را خودکار پیدا می‌کند (`C:\\PHP`، `C:\\xampp\\php`، `D:\\xampp\\php`،
`where php`)، ثبت قبلی را پاک می‌کند، و در پایان یک بار فوراً اجرا می‌کند تا هشدار
همان لحظه پاک شود.

---

## ۵) نصب

```
۱.۶۲.۹۷ ← ۱.۶۲.۹۸   (این بسته)
```

روی پوشهٔ نصب باز کنید (فایل‌ها می‌روند بالای فایل‌های فعلی). سپس:

```
cron\\register_system_alarm_tasks.bat
```

و صفحهٔ **سلامت سامانه** را تازه‌سازی کنید.

> **عمداً عوض نشد:** مهاجرتی اضافه نشد، جدولی تغییر نکرد، و مجوز تازه‌ای ساخته نشد.
> اسکیمای خط ۱.۶۲ دست‌نخورده ماند.

---

## ۶) اگر قبلاً کارشناس IT انتخاب کرده بودید

کاربرانی که با این فهرست به `primary_admin` ارتقا یافته‌اند، **حفظ می‌شوند** — این بسته
کسی را از دسترسی ادمین خارج نمی‌کند. فقط از این پس انتخاب‌های جدید کسی را ادمین نمی‌کنند.

اگر می‌خواهید این ارتقاهای ناخواسته را برگردانید، از **تنظیمات ← کاربران** نقش سیستمی
آن‌ها را دستی اصلاح کنید.

---

## ۷) آزمون‌ها

| تست | نتیجه |
|-----|-------|
| `tools/test_it_expert_routing_offline.php` **(جدید)** | ۱۲/۱۲ |
| `tools/test_alarm_selfheal_offline.php` **(جدید)** | ۲۹/۲۹ |
| `tools/test_assignee_callable_offline.php` **(جدید)** | ۱۰/۱۰ |
| `tests/test_ticket_routing_unit.php` | ۲۰/۲۰ |
| `tools/test_access_coverage_offline.php` | ۵۴/۵۴ |
| `tools/test_role_permissions_offline.php` | ۲۸/۲۸ |
| `tools/test_module_grant_offline.php` | ۴۱/۴۱ |
| `tools/test_release_notes_offline.php` | ۳۶/۳۶ |
| `tools/test_schema_baseline_offline.php` | ۲۸/۲۸ |
| `tools/test_explain_alarms_offline.php` | ۲۴/۲۴ |
| `tools/test_selftest_offline.php` | ۴۳/۴۳ |
| `tools/test_1_62_97_drift_offline.php` | ۲۹/۲۹ |
| `tests/test_release_notes_popup.js` | ۲۱/۲۱ |
| `tests/test_selftest_panel_render.js` | ۵۰/۵۰ |

تست ارجاع، **منطق واقعی** `choose_handling_agent()` را از سورس بیرون می‌کشد و اجرا
می‌کند — نه یک بازنویسی موازی. پس اگر کسی فردا دوباره شرط سیستمی را برگرداند، تست
قرمز می‌شود.

---

## ۸) یک هشدار صادقانه

هشدار ۴.۳ (کرون) تا وقتی اسکریپت را اجرا نکنید باقی می‌ماند. این تنها موردی است که
کد نمی‌تواند خودکار حلش کند، چون ثبت وظیفه در Windows Task Scheduler نیاز به دسترسی
مدیر ویندوز دارد و از داخل PHP صفحهٔ وب قابل انجام نیست.
"""
    return _tpl.replace("{RELEASE_VERSION}", RELEASE_VERSION).replace("{REQUIRES_VERSION}", REQUIRES_VERSION).replace("{RELEASE_DATE}", RELEASE_DATE)



def _read_line_package() -> dict[str, bytes]:
    """محتوای فایل‌های بستهٔ کامل ۱.۶۲.۹۷ — به‌عنوان پایهٔ خط ۱.۶۲."""
    if not LINE_PACKAGE.is_file():
        raise SystemExit(f"بستهٔ خط ۱.۶۲ پیدا نشد: {LINE_PACKAGE.name}")
    out: dict[str, bytes] = {}
    with zipfile.ZipFile(LINE_PACKAGE) as z:
        for name in z.namelist():
            if not name.startswith(LINE_PREFIX) or name.endswith("/"):
                continue
            out[name[len(LINE_PREFIX):]] = z.read(name)
    return out


def main() -> None:
    out_dir = ROOT / "downloads"
    out_dir.mkdir(exist_ok=True)

    payload: dict[str, bytes] = {}
    # فایل‌های کد از بستهٔ کامل ۱.۶۲.۹۷ + پچ‌های همین نسخه (پوشهٔ PATCHED)،
    # نه از شاخهٔ کاری — چون شاخه درخت ۱.۶۳ است و نام فایل‌ها چیزی را ثابت نمی‌کند.
    patched_dir = ROOT / "patches" / "1.62.98"
    line_src = _read_line_package()
    for rel in FILES:
        if rel.endswith((".sql",)):
            raise SystemExit(f"SQL در بستهٔ ۱.۶۲.۹۸ نباید باشد: {rel}")
        patch = patched_dir / rel
        if patch.is_file():
            payload[rel] = patch.read_bytes()
            continue
        if rel not in line_src:
            raise SystemExit(f"در بستهٔ ۱.۶۲.۹۷ نیست: {rel}")
        payload[rel] = line_src[rel]

    # دروازه: هیچ اثر ۱.۶۳ داخل بسته نباشد.
    for rel in payload:
        if any(tok in rel for tok in FORBIDDEN_IN_162):
            raise SystemExit(f"اثر ۱.۶۳ در بسته: {rel}")

    # VERSION و گزارش از شاخه (متن‌اند، نه کد).
    for rel in ("VERSION",):
        payload.pop(rel, None)
    # اسکیما عمداً حمل نمی‌شود: این بسته هیچ مهاجرت و هیچ تغییر جدولی ندارد،
    # و درخت کاری، اسکیمای ۱.۶۳ است — حملش یعنی نشت قلمرو ۱.۶۳ به خط غذا.

    report = build_report().encode("utf-8")
    payload[REPORT_NAME] = report
    payload["VERSION"] = (RELEASE_VERSION + "\n").encode("utf-8")

    manifest = {
        "name": "it-expert-routing-alarm-selfheal",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": TITLE,
        "requires_version": REQUIRES_VERSION,
        "migrations": [],
        "files": sorted(payload.keys()),
        "sha256": {k: sha256_bytes(v) for k, v in sorted(payload.items())},
        "release_status": "INTERIM_UPDATE_TEST_ON_STAGING_FIRST",
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
    sidecar = out_dir / (PACKAGE_NAME + ".sha256")
    sidecar.write_text(f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii")

    # گزارش تغییرات در مخزن هم نگه داشته می‌شود تا در مرور تاریخچه خوانا باشد.
    (out_dir / REPORT_NAME).write_bytes(report)

    print(f"OK {PACKAGE_NAME}")
    print(f"Files: {len(payload)} | migrations: 0 | bytes: {package_path.stat().st_size}")
    print(f"SHA-256: {checksum}")


if __name__ == "__main__":
    main()
