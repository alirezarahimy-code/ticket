# سامانهٔ آزمون تاب‌آوری مسیرها و APIها (محیط مصنوعی)

- مقصد: `127.0.0.1 only; isolated ephemeral MariaDB; synthetic accounts and fixtures`
- کاربر هم‌زمان: 250; worker PHP: 32
- مسیرهای ثبت‌شده: 66؛ مسیرهای دارای ترافیک: 66; درخواست‌های خواندنی مورد انتظار/انجام‌شده: 3000/3000؛ کل HTTP با ورود: 3500
- وضعیت: PASS; خطاهای درخواست: 0; شکست worker: 0; مسیر بی‌ترافیک: 0
- HTTP error غیرمنتظره: 0; رد دسترسی مورد انتظار: 45; خطای payload/API: 0
- ورود: p95=4282.99 ms؛ خواندن مسیرها/API: p50/p95/p99/max=1287.73/2351.43/2862.13/3307.55 ms
- PHP runtime warning/notice/fatal log: 0; SQL/DB log: 0
- زمان: 23.964s; throughput: 125.19 req/s; latency p50/p95/p99/max: 1287.73/2351.43/2862.13/3307.55 ms
- اوج مجموع CPU پردازه‌های PHP: 214.5%; RSS: 1469.9 MB

| مسیر | تعداد | HTTP مورد انتظار | p50 ms | p95 ms | p99 ms | بیشینه ms |
|---|---:|---:|---:|---:|---:|---:|
| page:dashboard | 46 | 200 | 1183.35 | 1510.99 | 1524.04 | 1524.04 |
| page:profile | 46 | 200 | 1324.34 | 1550.7 | 1557.58 | 1557.58 |
| page:queue | 46 | 200 | 1496.87 | 1859.64 | 2193.38 | 2193.38 |
| page:ticket detail | 46 | 200 | 1320.94 | 1590.26 | 1755.11 | 1755.11 |
| page:new ticket | 46 | 200 | 1328.4 | 1592.44 | 1627.3 | 1627.3 |
| page:local accounts | 46 | 200 | 1102.71 | 1248.41 | 1314.92 | 1314.92 |
| page:settings | 46 | 200 | 1242.47 | 1480.67 | 1978.55 | 1978.55 |
| page:reports | 46 | 200 | 1459.28 | 1627.68 | 1811.25 | 1811.25 |
| page:analytics | 46 | 200 | 1407.55 | 1683.18 | 1911.73 | 1911.73 |
| page:supervisor | 46 | 200 | 1437.66 | 1696.08 | 1879.18 | 1879.18 |
| page:inventory form | 46 | 200 | 2366.08 | 2576.1 | 2750.91 | 2750.91 |
| page:inventory diagnostics | 46 | 200 | 1014.6 | 1372.05 | 1511.75 | 1511.75 |
| page:domain scan page | 46 | 200 | 1265.79 | 1444.39 | 1936.36 | 1936.36 |
| page:asset list | 46 | 200 | 1501.24 | 1936.83 | 2008.47 | 2008.47 |
| page:asset detail | 46 | 200 | 2640.91 | 2873.36 | 2994.08 | 2994.08 |
| page:asset history | 46 | 200 | 1175.31 | 1292.11 | 1335.74 | 1335.74 |
| page:CD/DVD | 46 | 200 | 1040.11 | 1257.43 | 1313.7 | 1313.7 |
| page:CD/DVD history | 46 | 200 | 679.83 | 1328.86 | 1385.9 | 1385.9 |
| page:traffic control | 46 | 200 | 1263.01 | 1441.94 | 1506.96 | 1506.96 |
| page:governance | 46 | 200 | 2910.34 | 3257.42 | 3307.55 | 3307.55 |
| page:organization | 46 | 200 | 1343.54 | 1464.13 | 1509.18 | 1509.18 |
| page:holidays | 46 | 200 | 1185.7 | 1346.09 | 1363.18 | 1363.18 |
| page:knowledge | 46 | 200 | 1063.77 | 1173.87 | 1226.58 | 1226.58 |
| page:notifications | 46 | 200 | 754.38 | 1356.02 | 1366.54 | 1366.54 |
| page:search | 46 | 200 | 1298.96 | 1503.26 | 2202.52 | 2202.52 |
| page:backup page (read-only) | 46 | 200 | 1416.08 | 1838.55 | 1961.43 | 1961.43 |
| page:chatbot administration | 46 | 200 | 1347.88 | 1587.75 | 1766.11 | 1766.11 |
| page:services | 46 | 200 | 1362.25 | 1505.27 | 1527.48 | 1527.48 |
| page:system health | 46 | 200 | 1200.98 | 1311.53 | 1452.14 | 1452.14 |
| page:logs | 46 | 200 | 1644.33 | 2447.43 | 2486.84 | 2486.84 |
| page:activity log | 45 | 200 | 1243.5 | 1415.49 | 1745.63 | 1745.63 |
| page:help | 45 | 200 | 1422.72 | 1823.84 | 2175.17 | 2175.17 |
| page:food ticket | 45 | 200 | 1340.7 | 1669.71 | 1772.43 | 1772.43 |
| page:food order | 45 | 200 | 1387.51 | 1525.73 | 1547.38 | 1547.38 |
| page:food personnel link | 45 | 200 | 1191.16 | 1286.4 | 1345.12 | 1345.12 |
| page:change password | 45 | 200 | 1049.86 | 1220.54 | 1265.71 | 1265.71 |
| page:audit | 45 | 200 | 2408.03 | 2800.75 | 2975.84 | 2975.84 |
| food-ticket:health | 45 | 200 | 1341.21 | 1554.85 | 1932.66 | 1932.66 |
| food-ticket:session | 45 | 200 | 1383.13 | 1636.92 | 1908.31 | 1908.31 |
| food-ticket:config | 45 | 200 | 1284.64 | 1471.29 | 1566.47 | 1566.47 |
| food-ticket:monitoring | 45 | 200 | 1156.26 | 1298.74 | 1392.68 | 1392.68 |
| food-ticket:employees | 45 | 200 | 1050.23 | 1323.58 | 1421.34 | 1421.34 |
| food-ticket:guest-cards | 45 | 200 | 1250.08 | 1422.98 | 1574.31 | 1574.31 |
| food-ticket:guest-cap | 45 | 200 | 1419.89 | 1581.62 | 1873.65 | 1873.65 |
| food-ticket:food-groups | 45 | 200 | 1384.97 | 1665.27 | 1786.28 | 1786.28 |
| food-ticket:orders | 90 | 200 | 1329.14 | 1544.65 | 1573.88 | 1617.34 |
| food-ticket:absent | 45 | 200 | 1208.51 | 1382.2 | 1410.42 | 1410.42 |
| food-ticket:guest-requests-report | 45 | 200 | 1064.73 | 1215.94 | 1284.93 | 1284.93 |
| food-ticket:food-manual-attendance | 45 | 200 | 1123.33 | 1417.25 | 1483.22 | 1483.22 |
| food-ticket:orders | 90 | 200 | 1329.14 | 1544.65 | 1573.88 | 1617.34 |
| food-ticket:food-menu/status | 45 | 200 | 1350.95 | 1600.11 | 1706.4 | 1706.4 |
| food-ticket:food-menu/month | 45 | 200 | 1373.68 | 1717.98 | 1898.76 | 1898.76 |
| food-ticket:food-menu/catalog | 45 | 200 | 1261.0 | 1550.16 | 1692.08 | 1692.08 |
| food-ticket:food-menu/orders | 45 | 200 | 1138.72 | 1275.68 | 1302.97 | 1302.97 |
| food-ticket:food-menu/cancel-requests | 45 | 200 | 1070.3 | 1315.49 | 1446.49 | 1446.49 |
| food-ticket:food-menu/groups | 45 | 200 | 1338.79 | 1551.27 | 1632.98 | 1632.98 |
| food-ticket:food-menu/statistics | 45 | 200 | 1334.44 | 1566.49 | 1844.89 | 1844.89 |
| food-ticket:food-menu/group-orders | 45 | 200 | 1421.55 | 1773.01 | 1842.77 | 1842.77 |
| food-ticket:food-menu/bulk-snapshot | 45 | 200 | 1329.04 | 1530.45 | 1582.39 | 1582.39 |
| food-ticket:food-menu/emergency/people | 45 | 200 | 1120.0 | 1250.81 | 1339.67 | 1339.67 |
| food-order:month | 45 | 200 | 1103.84 | 1484.42 | 1650.32 | 1650.32 |
| food-order:my-orders | 45 | 200 | 1314.72 | 1515.82 | 1537.41 | 1537.41 |
| food-order:search-users | 45 | 200 | 1347.15 | 1454.45 | 1581.03 | 1581.03 |
| food-order:day-visit | 45 | 200 | 1404.07 | 1687.86 | 1991.25 | 1991.25 |
| food-order:guest-requests (expected deny) | 45 | 403 | 1256.63 | 1469.31 | 1536.69 | 1536.69 |
| traffic:invalid-code lookup | 45 | 200 | 1133.69 | 1226.3 | 1335.08 | 1335.08 |

## محدودیت‌ها
- All 250 VUs are synthetic primary-admin accounts; this is not a complete role/permission matrix.
- The 3,000-request phase is read-only apart from synthetic login sessions; write stress is separately covered by the ticket/CD-DVD and food lifecycle tests.
- LDAP/AD, Access/ODBC, printers, physical media, hardware collection, external providers, and production/staging capacity are not exercised.
