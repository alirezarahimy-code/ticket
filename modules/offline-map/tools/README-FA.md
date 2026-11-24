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
