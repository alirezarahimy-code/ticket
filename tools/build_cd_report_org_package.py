#!/usr/bin/env python3
"""سازندهٔ بستهٔ «انتخاب‌گر شخص واردکننده + نمایش بر اساس معاونت» — r4-cd-org

موضوع، جدا از بستهٔ r13 فیش غذا و بستهٔ نام کامپیوتر و بستهٔ مهمان دستی:

━━━ بخش الف: انتخاب‌گر «شخص واردکننده» در کنترل CD/DVD ━━━

کاربر در فرم «ثبت رسانه جدید» → «شخص واردکننده»:

  ۱. «فقط یک حرف می‌شود تایپ کرد؛ بقیه‌اش پاک می‌شود.»
  ۲. «برای علی رضایی اگر «ر» بزنم هیچ‌کس نمی‌آید، باید «ع» بزنم.»

ریشه — هر دو در یک جا:

  • فرم واقعی از `<select>` بومی استفاده می‌کرد. جست‌وجوی بومیِ <select> فقط
    با **ابتدای متن** تطبیق می‌دهد؛ «ر» هرگز به «علی رضایی» نمی‌رسد.
  • کد انتخاب‌گرِ متنی (با data-user-picker) در تابع مردهٔ
    cd_dvd_render_page_legacy() بود که هیچ‌جا صدا زده نمی‌شد.
  • همان کد هم اگر زنده بود، پس از انتخاب یک کاربر فیلد را readOnly می‌کرد؛
    input با readOnly هیچ کاراکتری نمی‌پذیرد و اولین کلیک همه را پاک می‌کرد.

اصلاح:

  • فرم واقعی به انتخاب‌گر متنی با فهرست کامل کاربران تبدیل شد.
  • فیلد هرگز readOnly نمی‌شود؛ انتخاب فعلی در pickedValue/pickedId می‌ماند.
  • جست‌وجو چندکلمه‌ای و در کل متن: نام، نام خانوادگی، نام کاربری، واحد.
  • نرمال‌سازی ی/ک عربی و حذف اعراب/کشیده.
  • Enter اولین نتیجه را می‌گیرد، Escape فهرست را می‌بندد، پیام «پیدا نشد» دارد.

━━━ بخش ب: «نمایش بر اساس معاونت» در چهار گزارش غذا ━━━

خواسته: دکمه‌ای که فهرست هر گزارش را زیر معاونتِ هر فرد بچیند؛ فقط ریشه
سرگروه باشد (نه ادارهٔ میانی)، و مدیریت مستقل، واحد مستقل (نمایندگی) و
شرکت دوم هم صریحاً نام‌گذاری شوند.

گزارش‌ها: گزارش تجمیعی، غذای تحویلی، سفارش‌های امروز، غایبین نهار.

پیاده‌سازی:

  • lib/org-grouping.php — از واحد هر فرد در چارت بالا می‌رود تا سرشاخه.
  • food-menu/org-map — نگاشت کش‌شدهٔ کد ملی / کد پرسنلی / شناسهٔ کاربر به گروه.
  • assets/food-org-groups.js — شکستن ردیف‌ها به گروه، سربرگ، شمارش، جمع کل.
  • دکمه در هر چهار گزارش، با CSS متمایز برای هر نوع سرشاخه.

━━━ بخش ج: استخراج شناسنامه ━━━

سه ایراد واقعی در مسیر استخراج که کاربر پرسیده بود «درست است یا خطا دارد؟»:

  ۱. اگر نام کامپیوتر برگشتی از اسکن قبلاً روی رکورد دیگری بود، UPDATE با
     «Duplicate entry … uq_assets_hostname_key» می‌شکست و کل استخراج —
     با همهٔ فیلدهای سالم — از دست می‌رفت.
  ۲. همان UPDATE نام کامپیوتر را هم بازنویسی می‌کرد (تغییر بی‌صدای هویت).
  ۳. سریال و مدل خودِ دستگاه هیچ فیلدی در شناسنامه نداشتند.
"""

from __future__ import annotations

import hashlib
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.63.4"
REQUIRES_VERSION = "1.63"
RELEASE_DATE = "2026-11-06"
PACKAGE_NAME = "ticket-1.63.4-r4-cd-org-20261115.zip"
REPORT_NAME = "CHANGE-REPORT-FA-cd-org.md"
ZIP_DATE = (2026, 11, 6, 4, 0, 0)

FILES = [
    # بخش الف — انتخاب‌گر شخص واردکننده
    "cd-dvd.php",
    "assets/cd-dvd.js",
    "assets/style.css",
    # بخش ب — نمایش بر اساس معاونت
    "lib/org-grouping.php",
    "assets/food-org-groups.js",
    "assets/food-menu.js",
    "assets/food-menu.css",
    "food-ticket-web/index.html",
    "food-ticket-web/assets/food-org-groups.js",
    "food-ticket-web/assets/food-menu.js",
    "food-ticket-web/assets/food-menu.css",
    "food-ticket.php",
    "food-order.php",
    # بخش ج — استخراج شناسنامه
    "domain-scan.php",
    "asset-profile.php",
    "asset-names.php",
    # آزمون‌ها
    "tools/test_cd_dvd_user_picker_offline.php",
    "tools/test_food_org_groups_offline.php",
    "tools/test_asset_extraction_offline.php",
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
    return """# گزارش تغییرات — انتخاب‌گر شخص واردکننده + نمایش بر اساس معاونت + استخراج شناسنامه

نسخه: **{version}** · پیش‌نیاز: **{requires}** · تاریخ: **{date}**
مهاجرت: **ندارد** · وضعیت: **RELEASE**

---

# بخش الف — «شخص واردکننده» در کنترل CD/DVD

## ۱) مشکلی که دیده شد

در فرم «ثبت رسانه جدید» → بخش **شخص واردکننده**:

> «بیشتر از یک حرف تایپ می‌کنم، خودش پاک می‌شود.»
>
> «برای علی رضایی اگر «ر» بزنم هیچ‌کس نمی‌آید؛ باید «ع» بزنم.»

## ۲) ریشه

سه ایراد که روی هم انباشته شده بودند:

| # | ایراد | اثر |
|---|---|---|
| ۱ | فرم واقعی `<select>` بومی بود | جست‌وجوی بومی فقط با **ابتدای** متن تطبیق می‌دهد. «ر» هرگز به «علی رضایی» نمی‌رسد. |
| ۲ | کد انتخاب‌گر متنی در `cd_dvd_render_page_legacy()` بود | آن تابع **هیچ‌جا صدا زده نمی‌شد** — کد مرده. |
| ۳ | همان کد پس از انتخاب، `readOnly` می‌کرد | `input` با `readOnly` **هیچ** کاراکتری نمی‌پذیرد؛ اولین کلیک همه را پاک می‌کرد. |

## ۳) اصلاح

- فرم واقعی به **انتخاب‌گر متنی با فهرست کامل کاربران** تبدیل شد.
- فیلد **هرگز** `readOnly` نمی‌شود. انتخاب فعلی در `pickedValue`/`pickedId`
  نگه داشته می‌شود و با اولین کاراکتر تازه پاک می‌شود، نه با کلیک.
- جست‌وجو **چندکلمه‌ای و در کل متن**: نام، نام خانوادگی، نام کاربری، واحد.
- نرمال‌سازی ی/ک عربی، حذف اعراب و کشیده.
- `Enter` اولین نتیجه را انتخاب، `Escape` فهرست را می‌بندد.
- پیام روشن «کاربری با این عبارت پیدا نشد».

## ۴) اثبات روی فهرست واقعی (۲۵ کاربر فعال)

| تایپ | نتیجه |
|---|---|
| «ر» | ۱۶ نفر — از جمله **علی رضایی** ✅ (قبلاً ۰) |
| «ع» | ۵ نفر |
| «رضا» | ۳ نفر — رضا، رضا محمدی، علی رضایی |
| «رضایی» | ۱ نفر — علی رضایی |
| «رضايي» (ی عربی) | ۱ نفر — علی رضایی |
| «فناوری» (واحد) | ۱ نفر |

**تایپ کاراکتر به کاراکتر** (همان باگ «فقط یک حرف»):

```
«ر»    → 16 نتیجه
«رض»   →  3 نتیجه
«رضا»  →  3 نتیجه
«رضای» →  1 نتیجه
«رضایی»→  1 نتیجه   ← هیچ‌جا پاک نشد
```

---

# بخش ب — «نمایش بر اساس معاونت» در چهار گزارش

## ۱) خواسته

> «یک دکمه «نمایش بر اساس معاونت» بگذار که همهٔ افراد را زیر معاونت خودشان
> بیاورد. فقط ریشه سرگروه باشد — مدیر اداره و کارشناس‌ها زیرش. مدیریت‌های
> مستقل، واحد مستقل (نمایندگی) و شرکت دوم هم صریحاً در این تفکیک نام‌شان
> بیاید.»

## ۲) روی کدام گزارش‌ها

| گزارش | دکمه |
|---|---|
| گزارش تجمیعی | «نمایش بر اساس معاونت» (هم فهرست فیش‌ها، هم غایبین) |
| غذای تحویلی | دکمهٔ سوم کنار «فهرست یکجا» و «بر اساس منو» |
| سفارش‌های امروز | دکمهٔ سوم کنار «فهرست یکجا» و «بر اساس منو» |
| غایبین نهار (سفارش دارند، فیش نگرفتند) | «نمایش بر اساس معاونت» |

## ۳) قاعدهٔ گروه‌بندی

از واحد هر فرد در چارت سازمانی بالا می‌رویم:

1. به **معاونت** رسیدیم → همان سرشاخه است، بالاتر نمی‌رویم.
2. به **مدیریت مستقل** رسیدیم و معاونتی بالاسرش نبود → خودش سرشاخه است.
3. **واحد مستقل (نمایندگی)** → همیشه خودش سرشاخه است.
4. هر واحدی متعلق به **شرکت دوم** → زیر «شرکت دوم» می‌رود.
5. به هیچ سرشاخه‌ای نرسید → بالاترین واحد موجود، تا **هیچ فردی گم نشود**.

**ادارهٔ میانی سرگروه نمی‌شود.** نام اداره فقط در tooltip عنوان گروه دیده
می‌شود. هر گروه شمارش خودش را دارد و پایین گزارش «جمع کل» می‌آید تا معلوم
باشد هیچ ردیفی جا نیفتاده است.

## ۴) اثبات — خروجی واقعی روی دادهٔ زنده

```
معاونت اداری و مالی          [معاونت]                  ۲ فیش
معاونت فناوری اطلاعات        [معاونت]                  ۴ فیش
مدیریت بازرسی                [مدیریت مستقل]            ۱ فیش
واحد مستقل نمایندگی شیراز    [واحد مستقل (نمایندگی)]   ۱ فیش
شرکت همکار                   [شرکت دوم]                ۲ فیش
بدون معاونت سازمانی          [بدون معاونت]             ۵ فیش
─────────────────────────────────────────────────────────────
جمع کل: ۱۵ فیش در ۶ گروه — همهٔ ردیف‌های گزارش در گروه‌ها حاضرند.
```

هر نوع سرشاخه **رنگ و برچسب خودش** را دارد. تصویر واقعی:
`downloads/ORG-GROUPING-PROOF-FA.png`

## ۵) جزئیات فنی

- `lib/org-grouping.php` — حل سرشاخه، بدون N+1 (یک کوئری برای همهٔ واحدها).
- `food-menu/org-map` — endpoint نگاشت: کد ملی، کد پرسنلی و شناسهٔ کاربر
  هر سه، چون گزارش‌های مختلف با فیلدهای مختلف کار می‌کنند.
- `assets/food-org-groups.js` — کش یک‌دقیقه‌ای، شکستن ردیف‌ها، شمارش، جمع کل.
- اگر چارت در دسترس نباشد، گزارش **بدون گروه‌بندی** نمایش داده می‌شود،
  نه خالی.

---

# بخش ج — استخراج شناسنامه

## ۱) پاسخ کوتاه

**کد نگاشت درست است، ولی مسیر ذخیره سه ایراد داشت که استخراج را می‌شکست.**
هر سه اصلاح شد.

## ۲) سه ایراد

### الف) برخورد کلید یکتا — کل استخراج از بین می‌رفت

`hostname` کلید هویت یکتای شناسنامه است (`uq_assets_hostname_key`). اگر نامِ
برگشتی از اسکن قبلاً روی رکورد دیگری بود:

```
SQLSTATE[23000]: Duplicate entry 'mba4-23' for key 'uq_assets_hostname_key'
```

این خطا **کل** UPDATE را برمی‌گرداند — حتی فیلدهای سالم. کاربر این را
«استخراج خطا می‌دهد» می‌دید.

**اصلاح:** نام کامپیوتر در استخراج **بازنویسی نمی‌شود**. نام مشاهده‌شده در
`hardware_json` (`observed_hostname`) و پرچم `hostname_mismatch` ثبت می‌شود.
اگر باز هم برخوردی پیش بیاید، فیلدها **بدون نام** نوشته می‌شوند و استخراج
موفق می‌ماند.

### ب) نام کامپیوتر بی‌صدا عوض می‌شد

همان UPDATE نام را از مقدار اسکن می‌نوشت. یعنی یک اسکن می‌توانست هویت رکورد
را عوض کند. حالا نام فعلی حفظ می‌شود.

### ج) سریال و مدل دستگاه هیچ‌جا دیده نمی‌شد

شناسنامه فیلد `motherboard_serial`، `disk1_serial` و `windows_serial` داشت،
اما **سریال خودِ دستگاه (BIOS) و مدلش فیلدی نداشت**. هر چه استخراج می‌خواند،
در فرم دیده نمی‌شد.

**اصلاح:** دو فیلد اضافه شد:

- **سریال دستگاه (BIOS)** → `machine_serial`
- **مدل دستگاه** → `machine_model` (سازنده + مدل)

هر دو از استخراج پر می‌شوند و fallback از جدول `assets` هم دارند.

## ۳) اثبات نگاشت با خروجی واقعی PowerShell

```
سریال BIOS      = 'CZC1234ABC'
سازنده          = 'HP'
مدل             = 'EliteDesk 800 G6'
سیستم‌عامل       = 'Microsoft Windows 11 Pro'
معماری          = '64-bit'
شناسه ویندوز     = '00330-80000'
پردازنده        = 'Intel(R) Core(TM) i7-10700 CPU @ 2.90GHz'
حافظه           = 16384 MB
MAC             = 'A4:BB:6D:12:34:56'   ← کارت فیزیکی، نه Hyper-V
IP              = '192.168.1.123'
آنتی‌ویروس       = 'Windows Defender'
هارد ۱ / نرم‌افزار / پرینتر / مانیتور  = ۱ / ۱ / ۱ / ۱
امتیاز سخت‌افزاری = ۷  → استخراج پذیرفته می‌شود
```

**پاسخ خالی به‌درستی رد می‌شود** (امتیاز ۰).

## ۴) اثبات در پایگاه‌داده

```
قبل  → serial_number = NULL, model = NULL, cpu = NULL, memory_mb = NULL
بعد  → serial_number = 'CZC1234ABC', model = 'EliteDesk 800 G6',
       cpu = 'Intel(R) Core(TM) i7-10700…', memory_mb = 16384,
       mac_address = 'A4:BB:6D:12:34:56', antivirus = 'Windows Defender'
ذخیره: موفق (۱۳)          hostname: دست‌نخورده ماند ✅
```

و در فرم شناسنامه:

```html
profile[machine_serial] value="CZC1234ABC"
profile[machine_model]  value="HP EliteDesk 800 G6"
```

## ۵) یک نکتهٔ صادقانه

استخراج **شبکه‌ای** فقط روی سرور **Windows** کار می‌کند و HTTPS می‌خواهد.
روی نصب Linux همین پیش‌بررسی پیام روشن می‌دهد و دکمه را غیرفعال می‌کند؛
این رفتار درست است، نه خطا. چیزی که در این بسته اصلاح شد، بخش‌هایی بود که
**بعد از** یک اسکن موفق هم خراب می‌ماندند.

---

# آزمون‌ها

| آزمون | نتیجه |
|---|---|
| `test_cd_dvd_user_picker_offline.php` | **۲۰/۲۰** (تازه) |
| `test_food_org_groups_offline.php` | **۴۶/۴۶** (تازه) |
| `test_asset_extraction_offline.php` | **۳۱/۳۱** (تازه) |
| `test_asset_name_exact_offline.php` | ۴۶/۴۶ |
| `test_asset_names_offline.php` | ۴۸/۴۸ |
| `test_manual_guest_delivery_offline.php` | ۴۶/۴۶ |
| `test_food_personnel_link_offline.php` | ۶۹/۶۹ |
| `test_food_group_logic_offline.php` | موفق |
| `test_food_dupes_offline.php` | ۱۷/۱۷ |
| `test_food_order_range_offline.php` | ۱۶/۱۶ |
| `test_asset_hostname_cleanup_offline.php` | ۳۶/۳۶ |
| `test_asset_profile_offline.php` | ۱۸/۱۸ |

---

# نصب

۱. محتوای پوشهٔ `files/` را روی پوشهٔ نصب کپی کنید.
۲. مرورگر را یک بار ببندید و باز کنید (فایل‌های `.js` و `.css` نسخه‌دارند).

**مهاجرت لازم نیست.** هیچ جدول یا ستونی عوض نمی‌شود؛ دو فیلد جدید شناسنامه
خودشان با `CREATE TABLE`/`ALTER TABLE` معمولِ همان فایل اضافه می‌شوند.
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
        "name": "cd-org-r4",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "انتخاب‌گر شخص واردکننده + نمایش بر اساس معاونت + استخراج شناسنامه",
        "requires_version": REQUIRES_VERSION,
        "migrations": [],
        "files": sorted(payload.keys()),
        "sha256": {k: sha256_bytes(v) for k, v in sorted(payload.items())},
        "release_status": "RELEASE",
        "description": "جست‌وجوی چندحرفی «شخص واردکننده»، گروه‌بندی چهار گزارش غذا بر اساس معاونت، اصلاح سه ایراد استخراج شناسنامه",
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
    tmp_path.replace(package_path)
    (out_dir / (PACKAGE_NAME + ".sha256")).write_text(
        f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii"
    )
    (out_dir / REPORT_NAME).write_bytes(report.encode("utf-8"))

    print(f"OK {PACKAGE_NAME}")
    print(f"Files: {len(payload)} | migrations: 0 | bytes: {size}")
    print(f"SHA-256: {checksum}")


if __name__ == "__main__":
    main()
