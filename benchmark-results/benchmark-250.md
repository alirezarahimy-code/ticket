# گزارش آزمون بار ۲۵۰ کاربر هم‌زمان

- زمان UTC: 2026-10-22T16:15:29.455786+00:00
- محیط: ephemeral GitHub Actions runner; isolated MariaDB; PHP CLI server with configured worker pool
- مقصد: 127.0.0.1 only; no production/staging host was contacted
- کاربران مجازی: 250; ramp-up: 30s; پایداری: 90s; think time: 1.0s
- کل درخواست‌ها: 10710; throughput کل: 86.84 درخواست/ثانیه; مشاهده‌های خطا: 0
- اوج مجموع CPU پردازه‌های PHP: 107.7%; اوج مجموع RSS پردازه‌ها: 1144.02 MB

| مسیر | تعداد | RPS | p50 ms | p95 ms | p99 ms | بیشینه ms | کدهای HTTP |
|---|---:|---:|---:|---:|---:|---:|---|
| asset-list | 865 | 7.01 | 1481.27 | 2005.11 | 2246.92 | 3043.67 | {'200': 865} |
| cd-dvd | 858 | 6.96 | 1428.69 | 1959.69 | 2204.82 | 2950.72 | {'200': 858} |
| dashboard | 868 | 7.04 | 1749.18 | 2311.54 | 2572.7 | 3180.18 | {'200': 868} |
| food-admin-config-api | 868 | 7.04 | 1356.6 | 1887.94 | 2148.35 | 2336.1 | {'200': 868} |
| food-admin-page | 826 | 6.7 | 1328.38 | 1861.07 | 2124.18 | 2585.1 | {'200': 826} |
| food-order-month-api | 852 | 6.91 | 1330.31 | 1837.52 | 2068.8 | 2409.08 | {'200': 852} |
| food-order-page | 828 | 6.71 | 1407.97 | 1922.19 | 2155.48 | 2478.78 | {'200': 828} |
| inventory | 838 | 6.8 | 2742.8 | 3463.1 | 3922.89 | 4838.75 | {'200': 838} |
| login | 250 | 2.03 | 1923.77 | 4692.97 | 4976.47 | 5507.04 | {'200': 250} |
| login-form | 250 | 2.03 | 403.34 | 1957.45 | 2358.46 | 2505.48 | {'200': 250} |
| new-ticket-form | 860 | 6.97 | 1527.41 | 2050.93 | 2275.84 | 2871.78 | {'200': 860} |
| notification-feed | 845 | 6.85 | 1294.09 | 1861.74 | 2146.4 | 2336.2 | {'200': 845} |
| reports | 827 | 6.71 | 1865.76 | 2399.26 | 2658.34 | 2993.28 | {'200': 827} |
| traffic-control | 875 | 7.1 | 1395.31 | 1899.52 | 2120.88 | 2693.07 | {'200': 875} |

## نمونهٔ خطاها
- موردی ثبت نشد.

## محدودیت‌ها
- LDAP/Active Directory authentication and Windows WMI collectors were not exercised; LDAP is disabled in the isolated config.
- The food management page and MySQL-backed APIs were exercised, but external Microsoft Access/ODBC attendance/order sources and physical printers were not available.
- Results describe this ephemeral CI runner and its PHP/MariaDB setup, not production capacity or production latency.
- All synthetic users and generated records exist only in the disposable CI database; no real operational data was used.
