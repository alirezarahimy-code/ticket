# گزارش آزمون بار ۲۵۰ کاربر هم‌زمان

- زمان UTC: 2026-10-22T16:11:10.753682+00:00
- محیط: ephemeral GitHub Actions runner; isolated MariaDB; PHP CLI server with configured worker pool
- مقصد: 127.0.0.1 only; no production/staging host was contacted
- کاربران مجازی: 250; ramp-up: 30s; پایداری: 90s; think time: 1.0s
- کل درخواست‌ها: 17760; throughput کل: 145.08 درخواست/ثانیه; مشاهده‌های خطا: 3699
- اوج مجموع CPU پردازه‌های PHP: 110.8%; اوج مجموع RSS پردازه‌ها: 1144.16 MB

| مسیر | تعداد | RPS | p50 ms | p95 ms | p99 ms | بیشینه ms | کدهای HTTP |
|---|---:|---:|---:|---:|---:|---:|---|
| asset-list | 1195 | 9.76 | 707.08 | 1073.53 | 1415.16 | 1877.04 | {'200': 1195} |
| cd-dvd | 1263 | 10.32 | 689.0 | 1061.69 | 1245.63 | 1553.27 | {'200': 1263} |
| dashboard | 1184 | 9.67 | 914.43 | 1240.41 | 1436.57 | 1730.37 | {'200': 1184} |
| food-admin-config-api | 1255 | 10.25 | 645.3 | 1025.05 | 1183.36 | 1485.22 | {'200': 1255} |
| food-admin-page | 1277 | 10.43 | 633.91 | 981.83 | 1141.21 | 1326.38 | {'200': 1277} |
| food-admin-page-validation | 1277 | 10.43 | 0.0 | 0.0 | 0.0 | 0.0 | {'200': 1277} |
| food-order-month-api | 1211 | 9.89 | 609.71 | 1004.93 | 1183.43 | 1534.1 | {'400': 1211} |
| food-order-month-api-validation | 1211 | 9.89 | 0.0 | 0.0 | 0.0 | 0.0 | {'400': 1211} |
| food-order-page | 1160 | 9.48 | 673.08 | 1037.51 | 1178.95 | 1464.63 | {'200': 1160} |
| inventory | 1271 | 10.38 | 1324.77 | 1842.48 | 2168.08 | 2689.02 | {'200': 1271} |
| login | 250 | 2.04 | 763.44 | 2498.64 | 2848.88 | 3213.16 | {'200': 250} |
| login-form | 250 | 2.04 | 124.41 | 1045.73 | 1285.13 | 1787.04 | {'200': 250} |
| new-ticket-form | 1307 | 10.68 | 762.47 | 1132.12 | 1374.18 | 2029.72 | {'200': 1307} |
| notification-feed | 1211 | 9.89 | 604.14 | 971.32 | 1141.9 | 1498.24 | {'200': 1211} |
| reports | 1217 | 9.94 | 993.05 | 1380.36 | 1567.05 | 1863.62 | {'200': 1217} |
| traffic-control | 1221 | 9.97 | 654.13 | 1007.89 | 1150.76 | 1611.77 | {'200': 1221} |

## نمونهٔ خطاها
- food-order-month-api — HTTP 400: HTTP 400
- food-order-month-api-validation — HTTP 400: expected HTTP 200
- food-order-month-api — HTTP 400: HTTP 400
- food-order-month-api-validation — HTTP 400: expected HTTP 200
- food-admin-page-validation — HTTP 200: session unexpectedly returned to the login page
- food-order-month-api — HTTP 400: HTTP 400
- food-order-month-api-validation — HTTP 400: expected HTTP 200
- food-order-month-api — HTTP 400: HTTP 400
- food-order-month-api-validation — HTTP 400: expected HTTP 200
- food-admin-page-validation — HTTP 200: session unexpectedly returned to the login page
- food-admin-page-validation — HTTP 200: session unexpectedly returned to the login page
- food-admin-page-validation — HTTP 200: session unexpectedly returned to the login page
- food-order-month-api — HTTP 400: HTTP 400
- food-order-month-api-validation — HTTP 400: expected HTTP 200
- food-order-month-api — HTTP 400: HTTP 400
- food-order-month-api-validation — HTTP 400: expected HTTP 200
- food-admin-page-validation — HTTP 200: session unexpectedly returned to the login page
- food-order-month-api — HTTP 400: HTTP 400
- food-order-month-api-validation — HTTP 400: expected HTTP 200
- food-order-month-api — HTTP 400: HTTP 400
- food-order-month-api-validation — HTTP 400: expected HTTP 200
- food-admin-page-validation — HTTP 200: session unexpectedly returned to the login page
- food-order-month-api — HTTP 400: HTTP 400
- food-order-month-api-validation — HTTP 400: expected HTTP 200
- food-admin-page-validation — HTTP 200: session unexpectedly returned to the login page
- food-admin-page-validation — HTTP 200: session unexpectedly returned to the login page
- food-admin-page-validation — HTTP 200: session unexpectedly returned to the login page
- food-order-month-api — HTTP 400: HTTP 400
- food-order-month-api-validation — HTTP 400: expected HTTP 200
- food-order-month-api — HTTP 400: HTTP 400

## محدودیت‌ها
- LDAP/Active Directory authentication and Windows WMI collectors were not exercised; LDAP is disabled in the isolated config.
- The food management page and MySQL-backed APIs were exercised, but external Microsoft Access/ODBC attendance/order sources and physical printers were not available.
- Results describe this ephemeral CI runner and its PHP/MariaDB setup, not production capacity or production latency.
- All synthetic users and generated records exist only in the disposable CI database; no real operational data was used.
