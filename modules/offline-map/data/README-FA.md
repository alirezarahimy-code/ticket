# بستهٔ دادهٔ نقشهٔ آفلاین ایران

این پوشه محل استقرار دادهٔ پایه و لایه‌های تکمیلی است. فایل خام PBF و فایل SQLite در Git قرار نمی‌گیرند.

## ساختار نهایی

```text
data/
├── source/iran-latest.osm.pbf
├── iran.pmtiles
├── style.json
├── manifest.json
├── search.sqlite
├── fonts/
│   └── Noto Sans Arabic Regular/
│       ├── 0-255.pbf
│       ├── 1536-1791.pbf
│       └── ... سایر بازه‌های Unicode
└── overlays/
    ├── official-boundaries.geojson
    ├── municipal-districts.geojson
    └── transit.geojson
```

## تولید داده

1. فایل `source/iran-latest.osm.pbf` را دریافت کنید.
2. با `tools/Build-OfflineIranMap.ps1` بستهٔ `iran.pmtiles` را بسازید.
3. با `tools/build-search-index.py` شاخص جست‌وجوی فارسی را در `search.sqlite` بسازید.
4. استایل محلی و قلم‌ها را مطابق `tools/README-FA.md` نصب کنید.
5. داده‌های رسمی مرزها و حمل‌ونقل را در `overlays/` قرار دهید.
6. `tools/Test-OfflineIranMapPackage.ps1` را اجرا کنید.

## استایل و قلم

استایل `style.json` به PMTiles محلی ارجاع می‌دهد. کد مرورگر مسیر مطلق محلی glyph را متناسب با محل نصب برنامه تولید می‌کند تا استقرار در زیرپوشه‌ای مانند `/ticket/` نیز کار کند. نام پوشهٔ font stack باید دقیقاً با مقدار `text-font` در استایل برابر باشد.

## لایه‌های تکمیلی

ساختار GeoJSON، محدودهٔ مختصات و الزامات منبع در `overlays/README-FA.md` تشریح شده است. دادهٔ رسمیِ فاقد منبع معتبر در این بسته تولید نمی‌شود.

## مجوز و انتساب

دادهٔ OSM تحت ODbL ارائه می‌شود. انتساب `© OpenStreetMap contributors` باید در رابط نقشه قابل مشاهده باشد. مجوز Planetiler، MapLibre، PMTiles و قلم‌ها جداگانه رعایت و فایل‌های LICENSE همراه بسته نگهداری شوند.
