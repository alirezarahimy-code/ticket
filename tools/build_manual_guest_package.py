#!/usr/bin/env python3
"""سازندهٔ بستهٔ «فیش مهمانِ ثبت‌دستی در گزارش‌های تحویل» — r2-manual-guest

موضوع، جدا از بستهٔ r13 فیش غذا و بستهٔ نام کامپیوتر:

کاربر ۶ مهمان را در «ثبت دستی فیش» ثبت کرد. در فهرست ثبت دستی با برچسب زرد
«صادر شد؛ چاپ فیزیکی انجام نشد» دیده شدند، اما گزارش «غذای تحویلی» هیچ
مهمانی نشان نداد و «تطبیق خرید و توزیع» تعداد پرس تحویلی مهمان را ۰ گذاشت.

ریشه: مسیر مهمان ردیف را با print_status='not_printed' و
delivery_status='pending' می‌ساخت، در حالی که هر دو گزارش شرط
print_status='printed' دارند. مسیر «کارمند» همین دو مقدار را درست می‌نوشت.

سه ایراد هم‌زمان:
  ۱. print_status مهمانِ بدون‌چاپ «printed» نمی‌شد → در هیچ گزارشی نبود.
  ۲. delivery_status مهمان «delivered» نمی‌شد → ستون تحویل خالی می‌ماند.
  ۳. meal_type هیچ‌کدام از دو مسیر نوشته نمی‌شد → همه «ناهار» فرض می‌شدند
     (و در نصب‌هایی که ستون NOT NULL است، ردیف «بدون فیش» خطا می‌داد).

بدون مهاجرت، بدون تغییر جدول.
"""

from __future__ import annotations

import hashlib
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.63.1"
REQUIRES_VERSION = "1.63"
RELEASE_DATE = "2026-11-06"
PACKAGE_NAME = "ticket-1.63.1-r3-manual-guest-20261115.zip"
REPORT_NAME = "CHANGE-REPORT-FA-manual-guest.md"
ZIP_DATE = (2026, 11, 6, 3, 30, 0)

FILES = [
    "food-ticket-groups.php",
    "food-ticket-web/assets/food-manual-attendance.js",
    "tools/fix_manual_guest_tickets.php",
    "tools/test_manual_guest_delivery_offline.php",
    "docs/FOOD-MANUAL-GUEST-FA.md",
]

FORBIDDEN_IN_PACKAGE = ("downloads/", "patches/", "updates/", "preview/",
                        "docs/system-map/", "ci-diagnostics/", "benchmark-results/")


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def sha256_path(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def build_report() -> str:
    return """# گزارش تغییرات — فیش مهمانِ ثبت‌دستی در گزارش‌های تحویل

نسخه: **{version}** · پیش‌نیاز: **{requires}** · تاریخ: **{date}**
مهاجرت: **ندارد** · وضعیت: **RELEASE**

---

## ۱) مشکلی که دیده شد

در «ثبت دستی فیش» ۶ مهمان تک‌تک ثبت شدند. در فهرست ثبت دستی با رنگ زرد
دیده شدند:

> «صادر شد؛ چاپ فیزیکی انجام نشد»

اما:

- در **«غذای تحویلی»** آن روز **هیچ مهمانی** نشان داده نشد.
- در **«تطبیق خرید و توزیع»** فقط تعداد مهمانِ درخواست‌شده آمد و **پرس
  تحویلی مهمان ۰** بود.

یعنی مهمان‌ها در پایگاه‌داده بودند ولی از دید هر دو گزارش **نامرئی**.

---

## ۲) ریشه

مسیر «مهمان» و مسیر «کارمند» دو مقدار متفاوت می‌نوشتند:

| فیلد | مسیر کارمند | مسیر مهمان (باگ) |
|---|---|---|
| `print_status` | `printed` | **`not_printed`** |
| `delivery_status` | `delivered` | **`pending`** |
| `meal_type` | (نوشته نمی‌شد) | (نوشته نمی‌شد) |

هر دو گزارش تحویل شرط `print_status = 'printed'` دارند. چون مهمان این شرط
را نداشت، از فهرست بیرون می‌افتاد — و چون `delivery_status` هم `pending`
بود، شمارش تحویل صفر می‌شد.

**ایراد سوم:** `meal_type` در هیچ‌کدام از دو مسیر نوشته نمی‌شد. یعنی حتی
مهمانِ شام هم «ناهار» شمرده می‌شد (گزارش‌ها با `COALESCE(meal_type,'lunch')`
کار می‌کنند). در نصب‌هایی که این ستون `NOT NULL` است، ثبت «بدون فیش» هم
خطای SQL می‌داد.

---

## ۳) اصلاح

### الف) مسیر مهمان

- `print_status` = **`printed`** وقتی فیش صادر می‌شود و چاپ فیزیکی نخواسته‌اند.
  (معنای این ستون در سامانه «فیش در سامانه صادر شد» است، نه «کاغذ چاپ شد».)
- `delivery_status` = **`delivered`** در همان لحظه (همان کاری که مسیر کارمند
  با `food_ticket_mark_delivered_on_print` می‌کرد).
- **چاپ فیزیکی:** ردیف همچنان `not_printed` می‌ماند تا وارد صف چاپ شود.
  ⚠️ این نکته حیاتی است: `food_ticket_enqueue_print` گارد
  `print_status <> "printed"` دارد؛ اگر همان‌جا `printed` می‌نوشتیم، فیش
  هیچ‌وقت وارد صف چاپ نمی‌شد.

### ب) وعدهٔ غذایی (ناهار / شام)

- هر دو مسیر (کارمند و مهمان) وعده را می‌گیرند، اعتبارسنجی می‌کنند و در
  ستون `meal_type` می‌نویسند.
- در «ثبت دستی فیش» یک **انتخابگر وعده** اضافه شد: ناهار / شام.
- ستون «وعده» در تاریخچهٔ ثبت دستی حالا واقعاً ناهار/شام را نشان می‌دهد.

### ج) برچسب گنگ

برچسب زرد «صادر شد؛ چاپ فیزیکی انجام نشد» گمراه‌کننده بود — کاربر فکر
می‌کرد فیش بی‌اثر است. حالا:

> **«ثبت و تحویل در سامانه؛ بدون چاپ کاغذی»**

---

## ۴) ابزار تعمیر ردیف‌های قدیمی

۶ مهمانی که **قبلاً** ثبت شده‌اند در پایگاه‌داده با مقادیر غلط مانده‌اند.
این ابزار همان‌ها را درست می‌کند:

```powershell
php tools\\fix_manual_guest_tickets.php            # گزارش
php tools\\fix_manual_guest_tickets.php --apply    # اعمال
```

فقط ردیف‌هایی را لمس می‌کند که **همهٔ** این شرط‌ها را دارند:

- `source_uid = 'manual-attendance'` (ثبت دستی، نه کارت‌خوان)
- `event_type = 'guest'` (مهمان، نه کارمند)
- `print_status = 'not_printed'` (هرگز چاپ نشد)
- `delivery_status = 'pending'` (تحویل نشد)
- `print_job_id IS NULL` (هیچ‌وقت در صف چاپ نبود)
- چاپ فیزیکی درخواست نشده بود

یعنی ردیف «مهمان با چاپ فیزیکی» که واقعاً در صف چاپ است **دست نمی‌خورد**.

نمونهٔ خروجی:

```
  کل فیش مهمانِ دستی ثبت‌شده   : 18
  قابل تعمیر (تحویل‌نشده، بی‌چاپ): 6

  شناسه  تاریخ        وعده   ساعت   توضیح
  303    2026-11-05   ناهار 23:01   not_printed → printed، pending → delivered
  ...
```

بعد از `--apply` خودش گزارش تحویلی را دوباره می‌خواند و عدد را نشان می‌دهد.

---

## ۵) نتیجهٔ آزمون

| آزمون | نتیجه |
|---|---|
| `tools/test_manual_guest_delivery_offline.php` | **۳۶/۳۶** (تازه) |
| `tools/test_food_dupes_offline.php` | ۱۷/۱۷ |
| `tools/test_food_group_logic_offline.php` | موفق |
| `tools/test_food_guest_request_deadline_offline.php` | ۱۳/۱۳ |
| `tools/test_food_order_range_offline.php` | ۱۶/۱۶ |
| `tools/test_food_personnel_link_offline.php` | ۶۹/۶۹ |
| `tools/test_key_roles_composition_offline.php` | ۳۴/۳۴ |

### اثبات زنده روی سامانه (نه فقط کد)

**قبل از اصلاح** — ۶ مهمان ثبت شد:

```
  شمار مهمان (guest_count): 0
  ناهار مهمان: 0
  ردیف‌های فهرست: 0
  تطبیق — تحویل مهمان ناهار: 0
  حقیقت در پایگاه‌داده: 6
```

**بعد از اصلاح** — ۶ مهمان ناهار + ۶ مهمان جدید:

```
  غذای تحویلی → شمار مهمان: 12
  تطبیق خرید و توزیع → guest_served_quantity: 12
```

**شام و ناهار جدا شمرده می‌شوند:**

```
  ۴ مهمان شام + ۲ مهمان ناهار
  غذای تحویلی → ناهار: 2 | شام: 4
  تطبیق        → ناهار: 2 | شام: 4
```

**مسیر کارمند همچنان سالم:** ۲ کارمند ثبت دستی → ۲ پرس تحویل در هر دو گزارش.

---

## ۶) نصب

۱. محتوای پوشهٔ `files/` را روی پوشهٔ نصب کپی کنید.
۲. اگر مهمان‌های قبلی را هم می‌خواهید در گزارش ببینید:

```powershell
php tools\\fix_manual_guest_tickets.php
php tools\\fix_manual_guest_tickets.php --apply
```

۳. مرورگر را یک بار ببندید و باز کنید.

مهاجرتی در کار نیست. راهنمای کامل: `docs/FOOD-MANUAL-GUEST-FA.md`
""".format(version=RELEASE_VERSION, requires=REQUIRES_VERSION, date=RELEASE_DATE)


def main() -> None:
    out_dir = ROOT / "downloads"
    out_dir.mkdir(exist_ok=True)

    payload: dict[str, bytes] = {}
    for rel in FILES:
        path = ROOT / rel
        if not path.is_file():
            raise SystemExit(f"فایل در درخت نیست: {rel}")
        if any(tok in rel for tok in FORBIDDEN_IN_PACKAGE):
            raise SystemExit(f"مسیر ممنوع در بسته: {rel}")
        payload[rel] = path.read_bytes()

    if any(r.endswith(".sql") for r in payload) or any(r.startswith("migrations/") for r in payload):
        raise SystemExit("این بسته مهاجرت ندارد.")

    report = build_report()
    payload[REPORT_NAME] = report.encode("utf-8")
    payload["VERSION"] = (RELEASE_VERSION + "\n").encode("utf-8")

    manifest = {
        "name": "manual-guest-r3",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "فیش مهمانِ ثبت‌دستی در گزارش‌های تحویل",
        "requires_version": REQUIRES_VERSION,
        "migrations": [],
        "files": sorted(payload.keys()),
        "sha256": {k: sha256_bytes(v) for k, v in sorted(payload.items())},
        "release_status": "RELEASE",
        "description": "ثبت print_status/delivery_status برای مهمان + وعدهٔ غذایی + ابزار تعمیر ردیف‌های قدیمی",
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
    size = tmp_path.stat().st_size
    check_doc_checksums(checksum, size)
    tmp_path.replace(package_path)
    (out_dir / (PACKAGE_NAME + ".sha256")).write_text(
        f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii"
    )
    (out_dir / REPORT_NAME).write_bytes(report.encode("utf-8"))

    print(f"OK {PACKAGE_NAME}")
    print(f"Files: {len(payload)} | migrations: 0 | bytes: {size}")
    print(f"SHA-256: {checksum}")


def check_doc_checksums(checksum: str, size: int) -> None:
    """نگهبان هش/حجم در اسناد تحویل — درسِ بستهٔ r13."""
    docs = [
        ROOT / "downloads" / "README-FA-downloads.md",
        ROOT / "downloads" / "MANUAL-GUEST-README-FA.md",
    ]
    size_str = f"{size:,}"
    for doc in docs:
        if not doc.is_file():
            continue
        text = doc.read_text(encoding="utf-8")
        if PACKAGE_NAME not in text:
            continue
        if checksum not in text:
            raise SystemExit(
                f"هش SHA-256 بسته در {doc.relative_to(ROOT)} نیست یا کهنه است — "
                f"هش درست: {checksum}"
            )
        norm = text.translate(str.maketrans("۰۱۲۳۴۵۶۷۸۹٬", "0123456789,"))
        if size_str not in norm:
            raise SystemExit(
                f"حجم بسته ({size_str} بایت) در {doc.relative_to(ROOT)} نیست یا کهنه است."
            )
        bad_link = re.search(
            r"releases/download/[^)\"'\s]*/" + re.escape(PACKAGE_NAME), text
        )
        if bad_link:
            raise SystemExit(
                f"لینک releases/download برای همین بسته در {doc.relative_to(ROOT)} — از raw/<tag>/ استفاده کن."
            )


if __name__ == "__main__":
    main()
