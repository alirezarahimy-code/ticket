# سامانهٔ آزمون تاب‌آوری مسیرها و APIها (محیط مصنوعی)

- مقصد: `127.0.0.1 only; isolated ephemeral MariaDB; synthetic accounts and fixtures`
- کاربر هم‌زمان: 250; worker PHP: 32
- مسیرهای ثبت‌شده: 66؛ مسیرهای دارای ترافیک: 66; درخواست‌های خواندنی مورد انتظار/انجام‌شده: 3000/3000؛ کل HTTP با ورود: 3500
- وضعیت: PASS; خطاهای درخواست: 0; شکست worker: 0; مسیر بی‌ترافیک: 0
- HTTP error غیرمنتظره: 0; رد دسترسی مورد انتظار: 45; خطای payload/API: 0
- ورود: p95=3850.3 ms؛ خواندن مسیرها/API: p50/p95/p99/max=1176.11/2140.91/2691.32/2974.26 ms
- PHP runtime warning/notice/fatal log: 0; SQL/DB log: 0
- زمان: 22.195s; throughput: 135.17 req/s; latency p50/p95/p99/max: 1176.11/2140.91/2691.32/2974.26 ms
- اوج مجموع CPU پردازه‌های PHP: 214.5%; RSS: 1468.77 MB

| مسیر | تعداد | HTTP مورد انتظار | p50 ms | p95 ms | p99 ms | بیشینه ms |
|---|---:|---:|---:|---:|---:|---:|
| page:dashboard | 46 | 200 | 1083.58 | 1352.88 | 2126.23 | 2126.23 |
| page:profile | 46 | 200 | 1205.84 | 1375.9 | 1615.4 | 1615.4 |
| page:queue | 46 | 200 | 1426.39 | 1674.26 | 1788.32 | 1788.32 |
| page:ticket detail | 46 | 200 | 1315.69 | 1509.58 | 1729.11 | 1729.11 |
| page:new ticket | 46 | 200 | 1168.79 | 1445.22 | 1471.0 | 1471.0 |
| page:local accounts | 46 | 200 | 1002.61 | 1119.49 | 1204.25 | 1204.25 |
| page:settings | 46 | 200 | 1103.41 | 1287.09 | 1628.1 | 1628.1 |
| page:reports | 46 | 200 | 1324.46 | 1507.93 | 1563.26 | 1563.26 |
| page:analytics | 46 | 200 | 1291.62 | 1622.01 | 1685.18 | 1685.18 |
| page:supervisor | 46 | 200 | 1420.19 | 1618.14 | 1677.7 | 1677.7 |
| page:inventory form | 46 | 200 | 2130.43 | 2262.74 | 2336.39 | 2336.39 |
| page:inventory diagnostics | 46 | 200 | 904.85 | 1229.59 | 1245.15 | 1245.15 |
| page:domain scan page | 46 | 200 | 1124.24 | 1307.37 | 1355.35 | 1355.35 |
| page:asset list | 46 | 200 | 1253.41 | 1417.85 | 1490.88 | 1490.88 |
| page:asset detail | 46 | 200 | 2596.88 | 2907.42 | 2954.66 | 2954.66 |
| page:asset history | 46 | 200 | 1060.49 | 1165.48 | 1206.88 | 1206.88 |
| page:CD/DVD | 46 | 200 | 905.64 | 1126.11 | 1134.4 | 1134.4 |
| page:CD/DVD history | 46 | 200 | 477.33 | 1207.24 | 1283.87 | 1283.87 |
| page:traffic control | 46 | 200 | 1178.78 | 1304.51 | 1867.33 | 1867.33 |
| page:governance | 46 | 200 | 2601.97 | 2902.28 | 2974.26 | 2974.26 |
| page:organization | 46 | 200 | 1266.17 | 1511.14 | 1551.82 | 1551.82 |
| page:holidays | 46 | 200 | 1063.28 | 1219.6 | 1259.84 | 1259.84 |
| page:knowledge | 46 | 200 | 951.5 | 1050.01 | 1098.85 | 1098.85 |
| page:notifications | 46 | 200 | 674.37 | 1171.16 | 1255.96 | 1255.96 |
| page:search | 46 | 200 | 1200.1 | 1409.46 | 1488.08 | 1488.08 |
| page:backup page (read-only) | 46 | 200 | 1168.43 | 1314.84 | 1350.48 | 1350.48 |
| page:chatbot administration | 46 | 200 | 1374.24 | 1705.53 | 1785.05 | 1785.05 |
| page:services | 46 | 200 | 1256.55 | 1577.58 | 1640.95 | 1640.95 |
| page:system health | 46 | 200 | 1103.31 | 1194.47 | 1216.02 | 1216.02 |
| page:logs | 46 | 200 | 1571.5 | 2282.55 | 2333.7 | 2333.7 |
| page:activity log | 45 | 200 | 1172.5 | 1295.85 | 1431.78 | 1431.78 |
| page:help | 45 | 200 | 1220.91 | 1326.92 | 1408.13 | 1408.13 |
| page:food ticket | 45 | 200 | 1317.69 | 1528.8 | 1544.73 | 1544.73 |
| page:food order | 45 | 200 | 1318.33 | 1666.81 | 1685.06 | 1685.06 |
| page:food personnel link | 45 | 200 | 1078.87 | 1212.8 | 1295.82 | 1295.82 |
| page:change password | 45 | 200 | 953.51 | 1074.03 | 1109.66 | 1109.66 |
| page:audit | 45 | 200 | 2167.26 | 2377.92 | 2386.8 | 2386.8 |
| food-ticket:health | 45 | 200 | 1261.18 | 1596.3 | 1647.94 | 1647.94 |
| food-ticket:session | 45 | 200 | 1243.34 | 1527.33 | 1607.68 | 1607.68 |
| food-ticket:config | 45 | 200 | 1169.85 | 1366.98 | 1445.85 | 1445.85 |
| food-ticket:monitoring | 45 | 200 | 1031.32 | 1135.78 | 1204.59 | 1204.59 |
| food-ticket:employees | 45 | 200 | 992.78 | 1197.22 | 1232.41 | 1232.41 |
| food-ticket:guest-cards | 45 | 200 | 1163.46 | 1301.28 | 1349.59 | 1349.59 |
| food-ticket:guest-cap | 45 | 200 | 1181.11 | 1301.01 | 1403.57 | 1403.57 |
| food-ticket:food-groups | 45 | 200 | 1335.37 | 1645.18 | 1694.73 | 1694.73 |
| food-ticket:orders | 90 | 200 | 1225.77 | 1525.4 | 1585.83 | 1593.88 |
| food-ticket:absent | 45 | 200 | 1074.14 | 1183.95 | 1212.94 | 1212.94 |
| food-ticket:guest-requests-report | 45 | 200 | 932.28 | 1032.98 | 1083.95 | 1083.95 |
| food-ticket:food-manual-attendance | 45 | 200 | 998.69 | 1323.4 | 2240.96 | 2240.96 |
| food-ticket:orders | 90 | 200 | 1225.77 | 1525.4 | 1585.83 | 1593.88 |
| food-ticket:food-menu/status | 45 | 200 | 1227.39 | 1651.49 | 1674.55 | 1674.55 |
| food-ticket:food-menu/month | 45 | 200 | 1259.81 | 1583.37 | 1720.33 | 1720.33 |
| food-ticket:food-menu/catalog | 45 | 200 | 1153.05 | 1360.52 | 1370.69 | 1370.69 |
| food-ticket:food-menu/orders | 45 | 200 | 1009.36 | 1113.58 | 1167.65 | 1167.65 |
| food-ticket:food-menu/cancel-requests | 45 | 200 | 1056.93 | 1203.39 | 2420.71 | 2420.71 |
| food-ticket:food-menu/groups | 45 | 200 | 1222.42 | 1320.23 | 1628.3 | 1628.3 |
| food-ticket:food-menu/statistics | 45 | 200 | 1242.44 | 1665.19 | 1698.59 | 1698.59 |
| food-ticket:food-menu/group-orders | 45 | 200 | 1363.62 | 1607.43 | 1714.71 | 1714.71 |
| food-ticket:food-menu/bulk-snapshot | 45 | 200 | 1192.43 | 1406.58 | 1469.93 | 1469.93 |
| food-ticket:food-menu/emergency/people | 45 | 200 | 1001.58 | 1137.65 | 1244.65 | 1244.65 |
| food-order:month | 45 | 200 | 1055.94 | 1256.07 | 1533.45 | 1533.45 |
| food-order:my-orders | 45 | 200 | 1206.4 | 1320.99 | 1352.63 | 1352.63 |
| food-order:search-users | 45 | 200 | 1225.46 | 1700.03 | 1789.14 | 1789.14 |
| food-order:day-visit | 45 | 200 | 1234.49 | 1561.17 | 1669.65 | 1669.65 |
| food-order:guest-requests (expected deny) | 45 | 403 | 1121.13 | 1366.76 | 1443.11 | 1443.11 |
| traffic:invalid-code lookup | 45 | 200 | 1001.2 | 1105.63 | 1154.17 | 1154.17 |

## محدودیت‌ها
- All 250 VUs are synthetic primary-admin accounts; this is not a complete role/permission matrix.
- The 3,000-request phase is read-only apart from synthetic login sessions; write stress is separately covered by the ticket/CD-DVD and food lifecycle tests.
- LDAP/AD, Access/ODBC, printers, physical media, hardware collection, external providers, and production/staging capacity are not exercised.
