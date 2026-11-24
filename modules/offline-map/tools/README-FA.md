# آماده‌سازی بستهٔ نقشهٔ آفلاین ایران

این راهنما برای ساخت محلی بستهٔ کاشی برداری از فایل `iran-latest.osm.pbf` است. فایل حجیم داده وارد Git نمی‌شود.

## ساختار ورودی

```text
modules/offline-map/
├── data/
│   └── source/
│       └── iran-latest.osm.pbf
└── tools/
    ├── Build-OfflineIranMap.ps1
    └── bin/
        └── planetiler.exe
```

## ابزار تبدیل

از نسخهٔ Windows x64 لانچر Planetiler استفاده کنید. لانچر مستقل برای ویندوز منتشر می‌شود و به نصب جداگانهٔ Java نیاز ندارد. صفحهٔ رسمی: https://github.com/onthegomap/planetiler

## اجرا در PowerShell

```powershell
Set-ExecutionPolicy -Scope Process Bypass -Force
.\modules\offline-map\tools\Build-OfflineIranMap.ps1
```

یا مسیر ابزار و فایل ورودی را صریح بدهید:

```powershell
.\modules\offline-map\tools\Build-OfflineIranMap.ps1 `
  -SourcePbf "C:\GIS\iran-latest.osm.pbf" `
  -PlanetilerExe "C:\GIS\planetiler\planetiler.exe"
```

اسکریپت فضای آزاد دیسک، وجود فایل‌ها، کد خروج Planetiler، وجود خروجی و امضای PMTiles را کنترل می‌کند و SHA-256 ورودی/خروجی و مشخصات ابزار را در `data/manifest.json` می‌نویسد. در صورت شکست، manifest موفقیت تولید نمی‌شود.

Planetiler از OSM PBF ورودی و PMTiles خروجی پشتیبانی می‌کند. راهنمای پروژه برای پردازش، فضای SSD آزاد حدود ۵ تا ۱۰ برابر حجم PBF و حافظهٔ آزاد حداقل حدود نصف حجم PBF را توصیه می‌کند. اسکریپت محافظه‌کارانه ۱۰ برابر حجم ورودی را بررسی می‌کند.

## مرز این مرحله

این اسکریپت فقط archive کاشی برداری را تولید می‌کند. موارد زیر باید جداگانه تهیه/تولید/اعتبارسنجی شوند: استایل سازگار و محلی، glyph/font و sprite، شاخص جست‌وجوی فارسی، مرزهای رسمی استان/شهرستان/شهرداری، پوشش حمل‌ونقل عمومی و گزارش پوشش واقعی خیابان/کوچه/ساختمان/نام‌ها.

پس از تولید، فایل `iran.pmtiles` به تنهایی برای فعال شدن نقشه کافی نیست. صفحهٔ سامانه باید تا نصب style و دارایی‌های محلی و عبور از کنترل سلامت، وضعیت «بسته ناقص» نشان دهد.

## مجوز و انتساب

دادهٔ OSM تحت ODbL ارائه می‌شود. در رابط نقشه باید انتساب قابل مشاهدهٔ `© OpenStreetMap contributors` درج شود و الزامات مجوز برای توزیع داده رعایت گردد. مجوز Planetiler جایگزین مجوز دادهٔ OSM نیست.

## آماده‌سازی کامل دارایی‌های مرورگر

### ۱) نصب کتابخانه‌های محلی

Node.js LTS و npm را فقط برای مرحلهٔ آماده‌سازی نصب کنید، سپس از ریشهٔ مخزن اجرا کنید:

```powershell
.\modules\offline-map\tools\Install-OfflineMapLibraries.ps1
```

نسخه‌های قفل‌شده در اسکریپت: MapLibre GL JS 6.18.0 و PMTiles 4.5.0. فایل‌ها در `modules/offline-map/vendor/` کپی می‌شوند؛ در زمان اجرای سامانه CDN یا npm فراخوانی نمی‌شود.

### ۲) نصب قلم فارسی آفلاین

از ابزار رسمی MapLibre Font Maker یک بستهٔ glyph برای قلم Noto Sans Arabic بسازید/دریافت کنید و ZIP خروجی را به اسکریپت بدهید:

```powershell
.\modules\offline-map\tools\Install-OfflineMapGlyphs.ps1 -GlyphArchive "C:\GIS\noto-arabic-glyphs.zip"
```

نام font stack باید دقیقاً `Noto Sans Arabic Regular` باشد. حداقل بازه‌های Latin و Arabic کنترل می‌شوند. استایل از مسیر محلی استفاده می‌کند؛ هیچ فونت راه‌دوری بارگذاری نمی‌شود.

### ۳) ساخت جست‌وجوی آفلاین فارسی

Python 3.10+ و pyosmium لازم است:

```powershell
python -m pip install -r .\modules\offline-map\tools\requirements-search.txt
python .\modules\offline-map\tools\build-search-index.py --pbf .\modules\offline-map\data\source\iran-latest.osm.pbf
```

خروجی `data/search.sqlite` شامل نام مکان‌ها، POIها و راه‌های نام‌دار موجود در OSM است. برای راه‌ها مختصات میانگین هندسه به‌عنوان نقطهٔ هدایت جست‌وجو ذخیره می‌شود؛ این نقطه آدرس پستی یا هندسهٔ دقیق معبر نیست. جست‌وجو از ی/ي، ک/ك، ارقام فارسی/عربی و نیم‌فاصله نرمال‌سازی انجام می‌دهد.

### ۴) مرزهای رسمی و حمل‌ونقل

فایل‌های تکمیلی در `data/overlays/` قرار می‌گیرند. ساختار و نام فایل‌ها در `data/overlays/README-FA.md` آمده است. کد هیچ مرز شهرداری یا مسیر حمل‌ونقل رسمی را جعل نمی‌کند؛ فقط دادهٔ واقعی و دارای منبع را نمایش می‌دهد.

### ۵) کنترل بسته

```powershell
.\modules\offline-map\tools\Test-OfflineIranMapPackage.ps1
```

کنترل موفق این اسکریپت به معنی کامل بودن پوشش جغرافیایی نیست؛ فقط سلامت فایل‌ها، checksum، ساختار استایل، عدم ارجاع اینترنتی و وجود قلم‌ها/کتابخانه‌ها را بررسی می‌کند.

## اعتبارسنجی لایه‌های رسمی

پس از قرار دادن GeoJSONها و تکمیل منبع، مجوز، تاریخ دریافت، اندازه و SHA-256 در `data/overlays/manifest.json` اجرا کنید:

```powershell
.\modules\offline-map\tools\Validate-OfflineMapOverlays.ps1
```

صفحهٔ نقشه فقط overlayهایی را بارگذاری می‌کند که فایل آن‌ها و اطلاعات منبع/مجوز/تاریخ/اندازه در manifest وجود داشته باشد. اعتبارسنجی checksum نیز با همین اسکریپت انجام می‌شود.

## اجرای یکپارچهٔ آماده‌سازی

پس از قرار دادن PBF، فایل اجرایی Planetiler و ZIP قلم فارسی، همهٔ مراحل آماده‌سازی را با یک فرمان اجرا کنید:

```powershell
.\modules\offline-map\tools\Prepare-OfflineIranMap.ps1 -GlyphArchive "C:\GIS\noto-arabic-glyphs.zip"
```

اگر کتابخانه‌ها از قبل در `vendor/` نصب شده‌اند، گزینهٔ `-SkipLibraryInstall` را بدهید. این فرمان PMTiles، شاخص جست‌وجو و کنترل بسته را اجرا می‌کند. فایل‌های مرز رسمی و حمل‌ونقل اختیاری هستند و نبود آن‌ها با عنوان «نصب نشده» باقی می‌ماند.
