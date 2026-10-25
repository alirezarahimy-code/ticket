# گزارش آزمون بار ۲۵۰ کاربر هم‌زمان

- زمان UTC: 2026-10-25T07:25:30.726658+00:00
- محیط: ephemeral GitHub Actions runner; isolated MariaDB; PHP CLI server with configured worker pool
- مقصد: 127.0.0.1 only; no production/staging host was contacted
- کاربران مجازی: 250; ramp-up: 30s; پایداری: 90s; think time: 1.0s
- کل درخواست‌ها: 18306; throughput کل: 149.81 درخواست/ثانیه; مشاهده‌های خطا: 0
- اوج مجموع CPU پردازه‌های PHP: 88.0%; اوج مجموع RSS پردازه‌ها: 1158.44 MB

| مسیر | تعداد | RPS | p50 ms | p95 ms | p99 ms | بیشینه ms | کدهای HTTP |
|---|---:|---:|---:|---:|---:|---:|---|
| asset-list | 1488 | 12.18 | 469.03 | 690.31 | 844.67 | 1241.38 | {'200': 1488} |
| cd-dvd | 1386 | 11.34 | 445.32 | 676.16 | 799.57 | 1109.37 | {'200': 1386} |
| dashboard | 1453 | 11.89 | 531.7 | 743.45 | 868.72 | 1420.75 | {'200': 1453} |
| food-admin-config-api | 1559 | 12.76 | 428.85 | 644.67 | 811.35 | 1345.5 | {'200': 1559} |
| food-admin-page | 1419 | 11.61 | 419.97 | 636.35 | 831.14 | 1089.33 | {'200': 1419} |
| food-order-month-api | 1493 | 12.22 | 418.06 | 636.94 | 741.66 | 951.89 | {'200': 1493} |
| food-order-page | 1483 | 12.14 | 437.82 | 652.21 | 767.64 | 1107.04 | {'200': 1483} |
| inventory | 1556 | 12.73 | 874.56 | 1219.56 | 1427.54 | 1706.87 | {'200': 1556} |
| login | 250 | 2.05 | 182.66 | 1505.18 | 1763.64 | 1944.61 | {'200': 250} |
| login-form | 250 | 2.05 | 32.71 | 681.69 | 811.7 | 829.46 | {'200': 250} |
| new-ticket-form | 1421 | 11.63 | 484.18 | 698.45 | 816.65 | 1117.32 | {'200': 1421} |
| notification-feed | 1471 | 12.04 | 401.52 | 608.42 | 730.12 | 1122.81 | {'200': 1471} |
| reports | 1508 | 12.34 | 565.2 | 781.4 | 904.19 | 1108.79 | {'200': 1508} |
| traffic-control | 1569 | 12.84 | 426.76 | 635.47 | 761.75 | 925.43 | {'200': 1569} |

## زمان‌سنجی سمت API و SQL
- لاگ‌های API مدیریت غذا با زمان حداقل ۱ ثانیه: 0
- لاگ‌های ورود با زمان حداقل ۲ ثانیه: 0 (p95 ثبت‌شده: 0 ms)
- کوئری‌های ثبت‌شده با زمان حداقل ۵۰۰ ms: 0 (بیشینه: 0 ms)
- خطاهای ثبت‌شدهٔ برنامه: 0; پیام‌های PHP warning/notice: 0
توجه: لاگ SQL خود برنامه فقط کوئری‌های حداقل ۵۰۰ ms را ثبت می‌کند؛ بنابراین این شمارش، هیستوگرام کاملِ همهٔ کوئری‌ها نیست.

## نمونهٔ خطاها
- موردی ثبت نشد.

## محدودیت‌ها
- LDAP/Active Directory authentication and Windows WMI collectors were not exercised; LDAP is disabled in the isolated config.
- The food management page and MySQL-backed APIs were exercised, but external Microsoft Access/ODBC attendance/order sources and physical printers were not available.
- Results describe this ephemeral CI runner and its PHP/MariaDB setup, not production capacity or production latency.
- All synthetic users and generated records exist only in the disposable CI database; no real operational data was used.
