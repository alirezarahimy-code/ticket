#!/usr/bin/env python3
"""سازندهٔ بستهٔ میانی ۱.۶۲.۹۷ — «آزمون جامع Worker / چاپ / مدیریت» داخل پنل،
در انتهای صفحهٔ «تنظیمات سیستم ← اتصال پایگاه‌ها».

چرا بستهٔ جدا؟  بستهٔ ۱.۶۳ (RC) عمداً فایل‌های قلمرو غذا را حمل نمی‌کند
(food-ticket.php و کل food-ticket-web/ در EXCLUDED_FOOD_PATHS/EXCLUDED_FOOD_PREFIXES
سازندهٔ RC هستند). آزمون درون‌پنلی، بخشی از پنل غذاست؛ پس جای درستش بستهٔ غذاست.

محتوا (فقط افزودنی، بدون تغییر مسیر چاپ/ثبت فیش و بدون مهاجرت):
  • food-ticket-selftest.php   — کتابخانهٔ مشترک آزمون (پنل + CLI)
  • food-ticket.php            — بارگذاری کتابخانه + مسیر API worker-selftest
  • food-ticket-web/index.html — کارت آزمون در انتهای صفحهٔ اتصال پایگاه‌ها
  • tools/prewindow_check.php  — پرچم --shared: دقیقاً همان گزارش پنل
  • CHANGE-REPORT + VERSION
"""

from __future__ import annotations

import hashlib
import io
import json
import subprocess
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.62.97"
REQUIRES_VERSION = "1.62.96"
RELEASE_DATE = "2026-11-15"
PACKAGE_NAME = "ticket-update-1.62.96-to-1.62.97-food-worker-selftest-panel-20261115.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.62.97.md"
ZIP_DATE = (2026, 11, 15, 2, 0, 0)

FILES = [
    "food-ticket-selftest.php",
    "food-ticket.php",
    "food-ticket-web/index.html",
    "tools/prewindow_check.php",
]

TITLE = (
    "آزمون جامع Worker/چاپ/مدیریت داخل پنل — در انتهای «تنظیمات سیستم ← اتصال پایگاه‌ها» "
    "(۱.۶۲.۹۶ ← ۱.۶۲.۹۷)"
)

DESCRIPTION = (
    "Interim 1.62.x update. Adds an in-panel self-test card at the bottom of "
    "\u00abSystem Settings \u2192 Database Connections\u00bb that runs 32 read-only checks in 7 sections: "
    "base config/installation, worker liveness (file + DB heartbeat, stale locks, and a dedicated "
    "\u00abcorrectly idle\u00bb verdict), print queue (same claim predicate the worker uses, stuck printing rows, "
    "retry distribution, today's throughput), attendance Access source (path, file reachability, real "
    "connect + read), printer and print path (IP/TCP probe with zero bytes sent, Windows printer presence, "
    "dry render of the real ticket template, archive dir writable), system management (Windows scheduled "
    "task, directory permissions, recent log errors, current user permission) and readiness (which of the "
    "next 7 days open at 11:00, computed by the same food_ticket_window_state function the worker calls). "
    "Strictly read-only: no INSERT/UPDATE/DELETE, no settings change, no byte sent to the printer. "
    "\u00abCorrectly idle\u00bb never reports FAIL \u2014 outside the 11:00\u201314:00 window with an empty queue the "
    "card explains that this is expected. Panel and CLI share one library (food-ticket-selftest.php); "
    "tools/prewindow_check.php --shared prints the identical report. API route worker-selftest requires "
    "only the food.db (view) permission. No schema changes, no migrations, no RC/1.63 content. "
    "No change to the print or ticket-registration path."
)


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def sha256_path(p: Path) -> str:
    return sha256_bytes(p.read_bytes())


def build_report() -> str:
    return f"""# گزارش تغییرات ۱.۶۲.۹۷ — آزمون جامع Worker / چاپ / مدیریت داخل پنل

نسخه: **{RELEASE_VERSION}** · پیش‌نیاز: **{REQUIRES_VERSION}** · تاریخ: **{RELEASE_DATE}**
مهاجرت: **ندارد** · وضعیت: `INTERIM_UPDATE_TEST_ON_STAGING_FIRST`

---

## ۱) خواستهٔ مالک

> «این ممکنه؟؟؟» — یک آزمون جامع (worker، چاپ، مدیریت سامانه) که **داخل خود پنل**،
> در **انتهای صفحهٔ «تنظیمات سیستم ← اتصال پایگاه‌ها»** اجرا شود و نتیجه را **همان‌جا** نشان دهد.

بله. و انجام شد: یک دکمه در انتهای همان صفحه، و ۳۲ بررسی در ۷ بخش، همان‌جا رندر می‌شود.

---

## ۲) چرا این بسته جداست

بستهٔ ۱.۶۳ (`ticket-update-1_62_96-to-1_63-…`) یک بستهٔ **RC مرکز درخواست‌ها** است و
طبق تصمیم مالک، فایل‌های قلمرو غذا را عمداً حمل **نمی‌کند**
(`food-ticket.php` و کل `food-ticket-web/` در فهرست استثناهای سازندهٔ آن هستند).
آزمون درون‌پنلی بخشی از پنل غذاست؛ پس باید از مسیر بستهٔ غذا برسد — همین بسته.

نصب: **۱.۶۲.۹۶ ← ۱.۶۲.۹۷** (مستقل از ۱.۶۳؛ هر ترتیبی مجاز است، ولی هر دو نیاز به ۱.۶۲.۹۶ دارند).

---

## ۳) هفت بخش آزمون

| # | بخش | چه چیزی سنجیده می‌شود |
|---|-----|------------------------|
| ۱ | تنظیمات پایه و نصب | پردازشگر فعال/غیرفعال، جداول `food_ticket_config` و `food_ticket_events`، مقدار `app.key`، وضعیت پنجرهٔ کاری |
| ۲ | Worker | وجود فایل Worker، ضربان فایل (`storage/food_worker.heartbeat`)، ضربان پایگاه‌داده (`food_ticket_worker_status`)، قفل کهنه، و **«وضعیت بیکاری»** |
| ۳ | صف چاپ | ردیف‌های واجد شرایط با **همان شرط انتخابِ Worker**، ردیف‌های گیرکرده در `printing`، توزیع `retry_count`، عملکرد امروز |
| ۴ | منبع تردد Access | مسیر تنظیم‌شده، دیده‌شدن فایل روی همین سرور، اتصال واقعی و خواندن نمونه، وضعیت حذف ردیف منبع |
| ۵ | چاپگر و مسیر چاپ | آدرس/IP، **اتصال TCP بدون ارسال هیچ بایتی**، وجود چاپگر ویندوز، **آزمون خشکِ رندر فیش واقعی**، قابل‌نوشتن بودن پوشهٔ آرشیو |
| ۶ | مدیریت سامانه | زمان‌بند ویندوز (`schtasks`)، مجوز نوشتن پوشه‌های `storage`، خطاهای اخیر لاگ، مجوز کاربر جاری |
| ۷ | آمادگی بازهٔ کاری | در ۷ روز آینده کدام روزها ساعت ۱۱:۰۰ باز/بسته‌اند (از همان تابع پنجرهٔ Worker) |

---

## ۴) قواعد طراحی (همان چیزی که مالک خواست)

- **فقط‌خواندنی مطلق:** هیچ `INSERT`/`UPDATE`/`DELETE`، هیچ تغییر تنظیمی، **هیچ بایتی به چاپگر**.
  آزمایش چاپگر فقط `fsockopen` است و بلافاصله بسته می‌شود.
- **«درست بیکار» ≠ «خراب»:** بیرون از پنجرهٔ ۱۱:۰۰–۱۴:۰۰ و با صف خالی، Worker باید بیکار باشد.
  این حالت با برچسب توضیحی نمایش داده می‌شود و **هرگز FAIL نمی‌دهد**.
- **یک منطق، دو مصرف‌کننده:** پنل و CLI هر دو `food-ticket-selftest.php` را صدا می‌زنند؛
  `php tools\\prewindow_check.php --shared` دقیقاً همان گزارش پنل را چاپ می‌کند.
  پس حکم پنل و حکم خط فرمان هرگز نمی‌توانند اختلاف داشته باشند.
- **هر FAIL یک راه‌حل دارد:** کنار هر ایراد، یک جملهٔ فارسیِ «چه کار کنم» نمایش داده می‌شود.
- **مجوز:** مسیر API `worker-selftest` فقط `food.db` (مشاهده) می‌خواهد؛ برای دیدن آزمون
  نیازی به مجوز ویرایش نیست. اجرای آزمون چیزی را تغییر نمی‌دهد.
- **گزارش قابل ارسال:** دکمهٔ «کپی گزارش» متن کامل را کپی می‌کند تا برای پشتیبانی بفرستید.

---

## ۵) نحوهٔ استفاده

1. پنل فیش غذا → **تنظیمات سیستم** → **اتصال پایگاه‌ها**.
2. تا انتهای صفحه بروید؛ کارت **«آزمون جامع Worker، چاپ و مدیریت سامانه»**.
3. **اجرای آزمون جامع** را بزنید (چند ثانیه؛ اتصال Access و چاپگر هرکدام حداکثر ۳ ثانیه).
4. نتیجه همان‌جا باز می‌شود: حکم بالا، و بخش‌هایی که ایراد/هشدار دارند خودکار باز هستند.
5. اگر خواستید برای بررسی بفرستید: **کپی گزارش**.

از خط فرمان (روی سرور ویندوز، از ریشهٔ برنامه):

```
chcp 65001
C:\\xampp\\php\\php.exe tools\\prewindow_check.php --shared
```

---

## ۶) فایل‌های بسته

| فایل | تغییر |
|------|-------|
| `food-ticket-selftest.php` | **جدید** — کتابخانهٔ مشترک آزمون (۷ بخش، ۳۲ بررسی) |
| `food-ticket.php` | بارگذاری کتابخانه + مسیر API `worker-selftest` با مجوز `food.db` |
| `food-ticket-web/index.html` | کارت آزمون در انتهای صفحهٔ «اتصال پایگاه‌ها» + سیم‌کشی دکمه‌ها |
| `tools/prewindow_check.php` | پرچم `--shared` برای خروجی یکسان با پنل |

بدون فایل حذف‌شده، بدون مهاجرت، بدون تغییر اسکیما، بدون تغییر مسیر چاپ یا ثبت فیش.

---

## ۷) آزمون‌های خودکار این نسخه

| آزمون | نتیجه |
|-------|-------|
| `php tools/test_selftest_offline.php` (منطق کتابخانه) | **۴۳/۴۳** |
| `node tests/test_selftest_panel_render.js` (رندر پنل + escape امنیتی) | **۳۲/۳۲** |
| `php tools/test_prewindow_check_offline.php` (بدون رگرسیون) | **۱۶/۱۶** |

پوشش تست کتابخانه: «درست بیکار» FAIL نمی‌دهد · صف مانده در پنجرهٔ باز FAIL می‌دهد ·
صف مانده بیرون از پنجره فقط WARN می‌دهد · پردازشگر غیرفعال FAIL می‌دهد ·
پنجرهٔ غیرفعال WARN می‌دهد · مسیر تردد خالی FAIL می‌دهد · IP چاپگر خالی FAIL می‌دهد ·
بازرسی متن سورس برای نبود هر عملیات نوشتنی.

---

## ۸) پس از نصب

1. سرویس وب را ری‌استارت کنید (فایل `food-ticket.php` عوض شده است).
2. یک‌بار صفحهٔ «اتصال پایگاه‌ها» را باز کنید و آزمون را اجرا کنید.
3. اگر Worker را هم ری‌استارت می‌کنید، عادی است؛ این بسته رفتار Worker را تغییر نداده است.

> **محدودیت صادقانه:** این آزمون سالم بودن *مسیر* چاپ را می‌سنجد (رندر، اتصال، صف، مجوز، پنجره)،
> ولی نمی‌تواند تضمین کند که فردا ساعت ۱۱:۰۰ دستگاه تردد ردیف تازه تولید می‌کند.
> برای آن، `tools/prewindow_check.php` قبل از ساعت ۱۱ و اسکن عمیق‌تر `tools/diagnose_food_full.php` را ببینید.
"""


def main() -> None:
    out_dir = ROOT / "downloads"
    out_dir.mkdir(exist_ok=True)

    payload: dict[str, bytes] = {}
    for rel in FILES:
        p = ROOT / rel
        if not p.is_file():
            raise SystemExit(f"missing required file: {rel}")
        payload[rel] = p.read_bytes()

    report = build_report().encode("utf-8")
    payload[REPORT_NAME] = report
    payload["VERSION"] = (RELEASE_VERSION + "\n").encode("utf-8")

    manifest = {
        "name": "food-worker-selftest-panel",
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
