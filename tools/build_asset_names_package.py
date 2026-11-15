#!/usr/bin/env python3
"""سازندهٔ بستهٔ «نام کامپیوتر در شناسنامه» — r1-asset-names

موضوع، جدا از بستهٔ r13 فیش غذاست: روی شبکه، reverse DNS نامی مثل
«clint 192.168.1.65» برمی‌گرداند و آن مقدار در `assets.hostname` ذخیره شده
بود. قاعدهٔ نمایش قدیمی هرچه بعد از اولین نقطه بود «دامنه» حساب می‌کرد و
نتیجه «clint 192» می‌شد — نه اسم کامپیوتر، نه IP. کاربر IP خودش را نمی‌داند،
پس کمبوی الزامی «سیستم مرتبط» در فرم تیکت IT قابل استفاده نبود.

این بسته فقط همین موضوع را می‌رساند:

۱) `asset-names.php` — قاعدهٔ نمایش بازنویسی شد: اول IP از رشته برداشته
   می‌شود، بعد قاعدهٔ دامنه اعمال می‌شود. ip خالص (تنها شناسهٔ موجود)
   دست‌نخورده می‌ماند.
۲) `actions/asset-actions.php` + `inventory.php` + `domain-scan.php` —
   `hostname_key` پس از تغییر نام به‌روز می‌شود. پیش‌تر به‌روز نمی‌شد و
   اسکن بعدی رکورد تکراری می‌ساخت.
۳) `tools/normalize_asset_hostnames.php` — ابزار یک‌بارمصرف برای پاک‌کردن
   نام‌های موجود (پیش‌فرض گزارش؛ با --apply اعمال).
۴) مستندات و آزمون.

بدون مهاجرت، بدون تغییر جدول.
"""

from __future__ import annotations

import hashlib
import json
import re
import sys
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.63.1"
REQUIRES_VERSION = "1.63"
RELEASE_DATE = "2026-11-06"
PACKAGE_NAME = "ticket-1.63.1-r2-asset-names-20261115.zip"
REPORT_NAME = "CHANGE-REPORT-FA-asset-names.md"
ZIP_DATE = (2026, 11, 6, 3, 0, 0)

BASE_PACKAGE = ROOT / "downloads" / "ticket-full-1.63.1-r13-20261115.zip"

FILES = [
    "asset-names.php",
    "actions/asset-actions.php",
    "actions/settings-actions.php",
    "inventory.php",
    "domain-scan.php",
    "index.php",
    # ۱.۶۳.۳ — لایهٔ فارسی‌سازی ارقام نباید نام کامپیوتر را عوض کند
    "lib/fa-digits.php",
    "asset-inventory-page.php",
    "tools/normalize_asset_hostnames.php",
    "tools/test_asset_hostname_cleanup_offline.php",
    "tools/test_asset_names_offline.php",
    "tools/test_asset_name_exact_offline.php",
    "docs/ASSET-HOSTNAME-DISPLAY-FA.md",
    "docs/ASSET-NAME-EXACT-FA.md",
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
    return """# گزارش تغییرات — نام کامپیوتر در شناسنامه‌ها

نسخه: **{version}** · پیش‌نیاز: **{requires}** · تاریخ: **{date}**
مهاجرت: **ندارد** · وضعیت: **RELEASE**

---

## ۱) مشکلی که دیده شد

در کمبوی **«سیستم مرتبط»** فرم ثبت تیکت IT و در فهرست شناسنامه‌ها، جای نام
کامپیوتر این دیده می‌شد:

```
clint 192.168.1.65
```

### چرا مهم بود

۱. **ظاهر:** شناسنامه‌ها باید تمیز باشند، نه پر از IP.
۲. **ثبت تیکت:** برای همهٔ خدمات IT انتخاب «سیستم مرتبط» الزامی است و کاربر
   باید سیستمی را انتخاب کند که نامش روی برچسب کیس نوشته شده. هیچ کاربری
   IP خودش را نمی‌داند — پس این فهرست قابل استفاده نبود.

---

## ۲) ریشهٔ واقعی

| # | ریشه | نتیجه |
|---|---|---|
| ۱ | reverse DNS روی شبکه نامی مثل `clint 192.168.1.65` برمی‌گرداند | همین مقدار در `assets.hostname` ذخیره شد |
| ۲ | قاعدهٔ نمایش هر چیز بعد از **اولین نقطه** را «دامنه» حساب می‌کرد | `clint 192.168.1.65` می‌شد `clint 192` |
| ۳ | رکوردهای فقط-IP (`CLIENT-192-168-1-65`) | رشتهٔ خط‌تیره‌ای بی‌معنا |

مورد دوم بدترین بود: نمایش از خودِ مقدار خام **گمراه‌کننده‌تر** می‌شد.

---

## ۳) اصلاح

### الف) قاعدهٔ نمایش

اول IP از رشته برداشته می‌شود، بعد قاعدهٔ دامنه اعمال می‌شود.

| ورودی | نمایش |
|---|---|
| `clint 192.168.1.65` | `clint` |
| `clint-192.168.1.65` | `clint` |
| `clint 192.168.1.65.corp.local` | `clint` |
| `CLIENT 192.168.1.65` | `client` |
| `pc1 10.1.2.3` | `pc1` |
| `pc1 192.168.1.5 CORP` | `pc1` |
| `CLIENT-192-168-1-65` | `192.168.1.65` |
| `reza-pc` | `reza-pc` (بدون تغییر) |
| `ADMINIT2.DOMAIN.LOCAL` | `adminit2` (بدون تغییر) |
| `192.168.1.65` | `192.168.1.65` (تنها شناسه — دست‌نخورده) |

### IP در پس‌زمینه می‌ماند

هیچ اطلاعاتی گم نشد:

- **tooltip:** نام کامل روی نام نمایش داده می‌شود.
- **جستجو:** تایپ IP یا نام کامل، رکورد را پیدا می‌کند.
- **ستون IP:** آدرس کامل در فهرست شناسنامه‌ها هست.

### ب) ابزار تعمیر رکوردهای موجود

نمایش درست، داده را عوض نمی‌کند. برای پاک‌کردن دائمی:

```powershell
php tools\\normalize_asset_hostnames.php           # گزارش
php tools\\normalize_asset_hostnames.php --apply   # اعمال
```

ابزار رکوردی که **فقط IP** است را دست نمی‌زند و هشدار می‌دهد — آن رکوردها
باید از برچسب کیس یا با اسکن دامنه نام بگیرند.

### ج) باگ `hostname_key` که همان‌جا پیدا شد

کلید تشخیص تکراری پس از تغییر نام به‌روز نمی‌شد. نتیجه: ویرایش نام، و بعد
اسکن دامنه، **رکورد تکراری** می‌ساخت. در ۶ نقطه رفع شد:

| فایل | نقطه |
|---|---|
| `actions/asset-actions.php` | ذخیرهٔ فرم شناسنامه |
| `inventory.php` | ۴ نقطهٔ به‌روزرسانی خودکار |
| `domain-scan.php` | ثبت و به‌روزرسانی از اسکن |

---

## ۴) آزمون

| آزمون | نتیجه |
|---|---|
| `tools/test_asset_names_offline.php` | ۴۸/۴۸ |
| `tools/test_asset_hostname_cleanup_offline.php` | ۳۶/۳۶ |
| `tools/test_asset_profile_offline.php` | ۱۸/۱۸ |
| UI: کمبوی «سیستم مرتبط» | «clint» |
| UI: فهرست شناسنامه‌ها | «clint» |
| UI: صفحهٔ شناسنامه | «شناسنامه رایانه clint» |
| ابزار تعمیر روی ۱۲ رکورد | ۴ پاک‌سازی، ۱ هشدار، ۰ خطا |
| تشخیص تکراری با ۵ شکل نام | همه یک رکورد |

---

## ۵) نصب

۱. محتوای پوشهٔ `files/` را روی پوشهٔ نصب کپی کنید.
۲. اگر نام‌های موجود را هم می‌خواهید پاک کنید:

```powershell
php tools\\normalize_asset_hostnames.php
php tools\\normalize_asset_hostnames.php --apply
```

۳. مرورگر را یک بار ببندید و باز کنید.

مهاجرتی در کار نیست. راهنمای کامل: `docs/ASSET-HOSTNAME-DISPLAY-FA.md`
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
        "name": "asset-names-r1",
        "version": RELEASE_VERSION,
        "date": RELEASE_DATE,
        "title": "نام کامپیوتر در شناسنامه‌ها — حذف IP از نمایش",
        "requires_version": REQUIRES_VERSION,
        "migrations": [],
        "files": sorted(payload.keys()),
        "sha256": {k: sha256_bytes(v) for k, v in sorted(payload.items())},
        "release_status": "RELEASE",
        "description": "نمایش نام کامپیوتر بدون IP؛ تعمیر رکوردهای موجود؛ رفع باگ hostname_key",
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
    """نگهبانِ هش/حجم در اسناد تحویل.

    همان درسی که در بستهٔ فیش غذا گرفتیم: نام فایل عوض شد ولی هش/حجم کهنه
    ماند و کاربر هش را بررسی می‌کرد و رد می‌شد. هر سندی که نام همین بسته را
    می‌آورد باید همین هش و همین حجم را بیاورد.
    """
    docs = [
        ROOT / "downloads" / "README-FA-downloads.md",
        ROOT / "downloads" / "RELEASE-ASSETS-1.63.1.txt",
        ROOT / "downloads" / "ASSET-NAMES-README-FA.md",
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
