# سامانهٔ آزمون تاب‌آوری مسیرها و APIها (محیط مصنوعی)

- مقصد: `127.0.0.1 only; isolated ephemeral MariaDB; synthetic accounts and fixtures`
- کاربر هم‌زمان: 250; worker PHP: 32
- مسیرهای ثبت‌شده: 66؛ مسیرهای دارای ترافیک: 66; درخواست‌های خواندنی مورد انتظار/انجام‌شده: 3000/3000؛ کل HTTP با ورود: 3500
- وضعیت: PASS; خطاهای درخواست: 0; شکست worker: 0; مسیر بی‌ترافیک: 0
- HTTP error غیرمنتظره: 0; رد دسترسی مورد انتظار: 45; خطای payload/API: 0
- ورود: p95=5574.54 ms؛ خواندن مسیرها/API: p50/p95/p99/max=1601.09/2835.67/3545.96/4096.58 ms
- PHP runtime warning/notice/fatal log: 0; SQL/DB log: 0
- زمان: 30.178s; throughput: 99.41 req/s; latency p50/p95/p99/max: 1601.09/2835.67/3545.96/4096.58 ms
- اوج مجموع CPU پردازه‌های PHP: 216.3%; RSS: 1482.44 MB

| مسیر | تعداد | HTTP مورد انتظار | p50 ms | p95 ms | p99 ms | بیشینه ms |
|---|---:|---:|---:|---:|---:|---:|
| page:dashboard | 46 | 200 | 1369.56 | 1793.57 | 1974.5 | 1974.5 |
| page:profile | 46 | 200 | 1646.46 | 1847.71 | 1916.86 | 1916.86 |
| page:queue | 46 | 200 | 1873.35 | 2315.14 | 2383.75 | 2383.75 |
| page:ticket detail | 46 | 200 | 1691.84 | 1944.99 | 1988.94 | 1988.94 |
| page:new ticket | 46 | 200 | 1647.99 | 1904.69 | 1948.7 | 1948.7 |
| page:local accounts | 46 | 200 | 1348.67 | 1552.74 | 1675.45 | 1675.45 |
| page:settings | 46 | 200 | 1402.87 | 1820.53 | 2488.34 | 2488.34 |
| page:reports | 46 | 200 | 1807.76 | 1992.53 | 2184.37 | 2184.37 |
| page:analytics | 46 | 200 | 1686.46 | 2027.79 | 2371.2 | 2371.2 |
| page:supervisor | 46 | 200 | 1950.17 | 2191.95 | 2228.26 | 2228.26 |
| page:inventory form | 46 | 200 | 2883.92 | 3167.32 | 3241.01 | 3241.01 |
| page:inventory diagnostics | 46 | 200 | 1219.0 | 1645.19 | 1656.18 | 1656.18 |
| page:domain scan page | 46 | 200 | 1578.08 | 1896.4 | 2174.23 | 2174.23 |
| page:asset list | 46 | 200 | 1684.27 | 1879.84 | 2069.56 | 2069.56 |
| page:asset detail | 46 | 200 | 3410.83 | 3721.38 | 3852.65 | 3852.65 |
| page:asset history | 46 | 200 | 1409.47 | 1641.06 | 1642.73 | 1642.73 |
| page:CD/DVD | 46 | 200 | 1224.4 | 1511.66 | 1712.52 | 1712.52 |
| page:CD/DVD history | 46 | 200 | 1061.73 | 1634.04 | 1676.38 | 1676.38 |
| page:traffic control | 46 | 200 | 1567.84 | 1811.14 | 1972.31 | 1972.31 |
| page:governance | 46 | 200 | 3518.76 | 3921.72 | 4096.58 | 4096.58 |
| page:organization | 46 | 200 | 1646.93 | 1906.92 | 2030.97 | 2030.97 |
| page:holidays | 46 | 200 | 1450.16 | 1647.12 | 1672.17 | 1672.17 |
| page:knowledge | 46 | 200 | 1259.24 | 1514.04 | 1687.45 | 1687.45 |
| page:notifications | 46 | 200 | 1069.56 | 1623.3 | 1711.97 | 1711.97 |
| page:search | 46 | 200 | 1653.62 | 1947.81 | 2772.65 | 2772.65 |
| page:backup page (read-only) | 46 | 200 | 1584.94 | 1798.28 | 1960.23 | 1960.23 |
| page:chatbot administration | 46 | 200 | 1856.48 | 2100.09 | 2170.4 | 2170.4 |
| page:services | 46 | 200 | 1655.13 | 1919.09 | 1970.4 | 1970.4 |
| page:system health | 46 | 200 | 1453.74 | 1684.39 | 1713.04 | 1713.04 |
| page:logs | 46 | 200 | 2285.14 | 3076.11 | 3270.57 | 3270.57 |
| page:activity log | 45 | 200 | 1564.36 | 1790.77 | 1985.19 | 1985.19 |
| page:help | 45 | 200 | 1588.75 | 1770.16 | 1862.44 | 1862.44 |
| page:food ticket | 45 | 200 | 1747.22 | 2045.29 | 2171.76 | 2171.76 |
| page:food order | 45 | 200 | 1684.24 | 1940.61 | 2011.23 | 2011.23 |
| page:food personnel link | 45 | 200 | 1470.28 | 1676.59 | 1679.05 | 1679.05 |
| page:change password | 45 | 200 | 1229.56 | 1472.02 | 1632.84 | 1632.84 |
| page:audit | 45 | 200 | 2897.82 | 3405.12 | 3524.53 | 3524.53 |
| food-ticket:health | 45 | 200 | 1715.99 | 2110.24 | 2329.34 | 2329.34 |
| food-ticket:session | 45 | 200 | 1675.64 | 1902.21 | 1928.9 | 1928.9 |
| food-ticket:config | 45 | 200 | 1636.56 | 1855.37 | 1967.43 | 1967.43 |
| food-ticket:monitoring | 45 | 200 | 1403.82 | 1651.33 | 1739.16 | 1739.16 |
| food-ticket:employees | 45 | 200 | 1256.83 | 1510.69 | 1671.51 | 1671.51 |
| food-ticket:guest-cards | 45 | 200 | 1582.35 | 1922.18 | 2873.68 | 2873.68 |
| food-ticket:guest-cap | 45 | 200 | 1614.52 | 1890.7 | 2174.4 | 2174.4 |
| food-ticket:food-groups | 45 | 200 | 1768.58 | 2277.28 | 2282.28 | 2282.28 |
| food-ticket:orders | 90 | 200 | 1661.44 | 1910.55 | 2069.01 | 2072.6 |
| food-ticket:absent | 45 | 200 | 1461.76 | 1676.64 | 1816.78 | 1816.78 |
| food-ticket:guest-requests-report | 45 | 200 | 1253.88 | 1614.51 | 1672.03 | 1672.03 |
| food-ticket:food-manual-attendance | 45 | 200 | 1362.77 | 1668.39 | 1753.82 | 1753.82 |
| food-ticket:orders | 90 | 200 | 1661.44 | 1910.55 | 2069.01 | 2072.6 |
| food-ticket:food-menu/status | 45 | 200 | 1757.7 | 2049.26 | 2141.98 | 2141.98 |
| food-ticket:food-menu/month | 45 | 200 | 1671.79 | 1949.23 | 2003.26 | 2003.26 |
| food-ticket:food-menu/catalog | 45 | 200 | 1592.34 | 1954.56 | 2111.8 | 2111.8 |
| food-ticket:food-menu/orders | 45 | 200 | 1380.77 | 1658.06 | 1787.33 | 1787.33 |
| food-ticket:food-menu/cancel-requests | 45 | 200 | 1281.67 | 1596.84 | 1610.81 | 1610.81 |
| food-ticket:food-menu/groups | 45 | 200 | 1626.34 | 1819.68 | 2250.65 | 2250.65 |
| food-ticket:food-menu/statistics | 45 | 200 | 1692.16 | 2181.49 | 2280.53 | 2280.53 |
| food-ticket:food-menu/group-orders | 45 | 200 | 1673.99 | 1996.95 | 2144.22 | 2144.22 |
| food-ticket:food-menu/bulk-snapshot | 45 | 200 | 1646.2 | 1955.81 | 2058.73 | 2058.73 |
| food-ticket:food-menu/emergency/people | 45 | 200 | 1397.52 | 1660.47 | 1746.8 | 1746.8 |
| food-order:month | 45 | 200 | 1334.25 | 1631.89 | 1644.07 | 1644.07 |
| food-order:my-orders | 45 | 200 | 1629.09 | 1801.06 | 1891.26 | 1891.26 |
| food-order:search-users | 45 | 200 | 1639.14 | 2139.3 | 2267.54 | 2267.54 |
| food-order:day-visit | 45 | 200 | 1715.69 | 2033.19 | 2062.41 | 2062.41 |
| food-order:guest-requests (expected deny) | 45 | 403 | 1597.02 | 1812.05 | 2065.93 | 2065.93 |
| traffic:invalid-code lookup | 45 | 200 | 1379.72 | 1624.21 | 1658.04 | 1658.04 |

## محدودیت‌ها
- All 250 VUs are synthetic primary-admin accounts; this is not a complete role/permission matrix.
- The 3,000-request phase is read-only apart from synthetic login sessions; write stress is separately covered by the ticket/CD-DVD and food lifecycle tests.
- LDAP/AD, Access/ODBC, printers, physical media, hardware collection, external providers, and production/staging capacity are not exercised.
