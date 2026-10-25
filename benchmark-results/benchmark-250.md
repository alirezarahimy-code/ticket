# گزارش آزمون بار ۲۵۰ کاربر هم‌زمان

- زمان UTC: 2026-10-22T16:27:00.568181+00:00
- محیط: ephemeral GitHub Actions runner; isolated MariaDB; PHP CLI server with configured worker pool
- مقصد: 127.0.0.1 only; no production/staging host was contacted
- کاربران مجازی: 250; ramp-up: 30s; پایداری: 90s; think time: 1.0s
- کل درخواست‌ها: 11698; throughput کل: 95.12 درخواست/ثانیه; مشاهده‌های خطا: 0
- اوج مجموع CPU پردازه‌های PHP: 112.2%; اوج مجموع RSS پردازه‌ها: 1145.48 MB

| مسیر | تعداد | RPS | p50 ms | p95 ms | p99 ms | بیشینه ms | کدهای HTTP |
|---|---:|---:|---:|---:|---:|---:|---|
| asset-list | 905 | 7.36 | 1265.21 | 1734.03 | 1923.67 | 2309.43 | {'200': 905} |
| cd-dvd | 982 | 7.98 | 1230.37 | 1725.93 | 1993.95 | 2479.29 | {'200': 982} |
| dashboard | 891 | 7.25 | 1508.22 | 2017.25 | 2286.66 | 2760.72 | {'200': 891} |
| food-admin-config-api | 950 | 7.72 | 1173.72 | 1665.59 | 1927.31 | 2266.32 | {'200': 950} |
| food-admin-page | 946 | 7.69 | 1133.96 | 1613.3 | 1848.57 | 2188.8 | {'200': 946} |
| food-order-month-api | 911 | 7.41 | 1134.06 | 1625.47 | 1902.33 | 2302.2 | {'200': 911} |
| food-order-page | 925 | 7.52 | 1213.88 | 1707.46 | 1935.89 | 2258.21 | {'200': 925} |
| inventory | 951 | 7.73 | 2362.08 | 3072.72 | 3560.48 | 4207.71 | {'200': 951} |
| login | 250 | 2.03 | 1371.41 | 4018.31 | 4351.24 | 4858.32 | {'200': 250} |
| login-form | 250 | 2.03 | 254.9 | 1708.02 | 2132.48 | 2721.67 | {'200': 250} |
| new-ticket-form | 909 | 7.39 | 1326.18 | 1800.76 | 2003.5 | 2264.04 | {'200': 909} |
| notification-feed | 950 | 7.72 | 1113.88 | 1595.58 | 1795.18 | 2291.98 | {'200': 950} |
| reports | 925 | 7.52 | 1611.54 | 2107.71 | 2311.79 | 2636.82 | {'200': 925} |
| traffic-control | 953 | 7.75 | 1179.78 | 1653.01 | 1867.63 | 2284.23 | {'200': 953} |

## زمان‌سنجی سمت API و SQL
- لاگ‌های API مدیریت غذا با زمان حداقل ۱ ثانیه: 0
- لاگ‌های ورود با زمان حداقل ۲ ثانیه: 0 (p95 ثبت‌شده: 0 ms)
- کوئری‌های ثبت‌شده با زمان حداقل ۵۰۰ ms: 1 (بیشینه: 509 ms)
- خطاهای ثبت‌شدهٔ برنامه: 0; پیام‌های PHP warning/notice: 0
توجه: لاگ SQL خود برنامه فقط کوئری‌های حداقل ۵۰۰ ms را ثبت می‌کند؛ بنابراین این شمارش، هیستوگرام کاملِ همهٔ کوئری‌ها نیست.
- SQL 509 ms — SHOW COLUMNS FROM traffic_visits

## نمونهٔ خطاها
- موردی ثبت نشد.

## محدودیت‌ها
- LDAP/Active Directory authentication and Windows WMI collectors were not exercised; LDAP is disabled in the isolated config.
- The food management page and MySQL-backed APIs were exercised, but external Microsoft Access/ODBC attendance/order sources and physical printers were not available.
- Results describe this ephemeral CI runner and its PHP/MariaDB setup, not production capacity or production latency.
- All synthetic users and generated records exist only in the disposable CI database; no real operational data was used.
