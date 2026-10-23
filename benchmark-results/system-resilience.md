# سامانهٔ آزمون تاب‌آوری مسیرها و APIها (محیط مصنوعی)

- مقصد: `127.0.0.1 only; isolated ephemeral MariaDB; synthetic accounts and fixtures`
- کاربر هم‌زمان: 250; worker PHP: 32
- مسیرهای ثبت‌شده: 66؛ مسیرهای دارای ترافیک: 66; درخواست‌های خواندنی مورد انتظار/انجام‌شده: 3000/3000؛ کل HTTP با ورود: 3500
- وضعیت: PASS; خطاهای درخواست: 0; شکست worker: 0; مسیر بی‌ترافیک: 0
- HTTP error غیرمنتظره: 0; رد دسترسی مورد انتظار: 45; خطای payload/API: 0
- ورود: p95=3922.81 ms؛ خواندن مسیرها/API: p50/p95/p99/max=1158.32/2063.4/2659.28/3003.09 ms
- PHP runtime warning/notice/fatal log: 0; SQL/DB log: 0
- زمان: 22.292s; throughput: 134.58 req/s; latency p50/p95/p99/max: 1158.32/2063.4/2659.28/3003.09 ms
- اوج مجموع CPU پردازه‌های PHP: 230.6%; RSS: 1474.38 MB

| مسیر | تعداد | HTTP مورد انتظار | p50 ms | p95 ms | p99 ms | بیشینه ms |
|---|---:|---:|---:|---:|---:|---:|
| page:dashboard | 46 | 200 | 1067.29 | 1376.23 | 1645.89 | 1645.89 |
| page:profile | 46 | 200 | 1179.83 | 1398.9 | 1400.64 | 1400.64 |
| page:queue | 46 | 200 | 1358.54 | 1623.06 | 1710.65 | 1710.65 |
| page:ticket detail | 46 | 200 | 1213.35 | 1448.32 | 1580.74 | 1580.74 |
| page:new ticket | 46 | 200 | 1175.78 | 1368.94 | 1487.0 | 1487.0 |
| page:local accounts | 46 | 200 | 973.51 | 1073.92 | 1233.39 | 1233.39 |
| page:settings | 46 | 200 | 1068.76 | 1296.43 | 1842.95 | 1842.95 |
| page:reports | 46 | 200 | 1346.32 | 1525.06 | 1629.72 | 1629.72 |
| page:analytics | 46 | 200 | 1256.42 | 1423.3 | 1527.99 | 1527.99 |
| page:supervisor | 46 | 200 | 1414.6 | 1677.26 | 1748.86 | 1748.86 |
| page:inventory form | 46 | 200 | 2053.41 | 2307.2 | 2535.03 | 2535.03 |
| page:inventory diagnostics | 46 | 200 | 991.82 | 1163.31 | 1288.04 | 1288.04 |
| page:domain scan page | 46 | 200 | 1083.71 | 1350.58 | 1412.91 | 1412.91 |
| page:asset list | 46 | 200 | 1273.2 | 1443.87 | 1518.32 | 1518.32 |
| page:asset detail | 46 | 200 | 2470.95 | 2686.19 | 2837.49 | 2837.49 |
| page:asset history | 46 | 200 | 988.17 | 1131.82 | 1206.7 | 1206.7 |
| page:CD/DVD | 46 | 200 | 927.43 | 1041.75 | 1147.11 | 1147.11 |
| page:CD/DVD history | 46 | 200 | 506.54 | 1185.42 | 1234.53 | 1234.53 |
| page:traffic control | 46 | 200 | 1086.66 | 1278.56 | 1511.44 | 1511.44 |
| page:governance | 46 | 200 | 2673.53 | 2927.81 | 3003.09 | 3003.09 |
| page:organization | 46 | 200 | 1233.26 | 1386.48 | 1498.84 | 1498.84 |
| page:holidays | 46 | 200 | 1013.18 | 1194.94 | 1266.27 | 1266.27 |
| page:knowledge | 46 | 200 | 931.72 | 1098.66 | 1217.9 | 1217.9 |
| page:notifications | 46 | 200 | 447.63 | 1184.36 | 1198.65 | 1198.65 |
| page:search | 46 | 200 | 1124.51 | 1324.14 | 1340.73 | 1340.73 |
| page:backup page (read-only) | 46 | 200 | 1223.84 | 1362.35 | 1418.53 | 1418.53 |
| page:chatbot administration | 46 | 200 | 1281.56 | 1474.1 | 1601.92 | 1601.92 |
| page:services | 46 | 200 | 1215.51 | 1429.49 | 1496.87 | 1496.87 |
| page:system health | 46 | 200 | 1051.15 | 1242.18 | 1327.68 | 1327.68 |
| page:logs | 46 | 200 | 1401.74 | 2151.35 | 2273.55 | 2273.55 |
| page:activity log | 45 | 200 | 1090.11 | 1323.59 | 1471.36 | 1471.36 |
| page:help | 45 | 200 | 1216.82 | 1383.79 | 1417.37 | 1417.37 |
| page:food ticket | 45 | 200 | 1210.45 | 1487.63 | 1566.07 | 1566.07 |
| page:food order | 45 | 200 | 1263.62 | 1402.43 | 1567.68 | 1567.68 |
| page:food personnel link | 45 | 200 | 1050.2 | 1215.32 | 1258.88 | 1258.88 |
| page:change password | 45 | 200 | 903.79 | 1055.23 | 1116.31 | 1116.31 |
| page:audit | 45 | 200 | 2119.3 | 2441.33 | 2623.77 | 2623.77 |
| food-ticket:health | 45 | 200 | 1196.7 | 1379.96 | 1426.35 | 1426.35 |
| food-ticket:session | 45 | 200 | 1228.32 | 1355.18 | 1456.82 | 1456.82 |
| food-ticket:config | 45 | 200 | 1158.03 | 1306.97 | 1346.84 | 1346.84 |
| food-ticket:monitoring | 45 | 200 | 990.43 | 1183.72 | 1207.7 | 1207.7 |
| food-ticket:employees | 45 | 200 | 928.05 | 1151.92 | 1202.52 | 1202.52 |
| food-ticket:guest-cards | 45 | 200 | 1101.81 | 1323.5 | 1444.53 | 1444.53 |
| food-ticket:guest-cap | 45 | 200 | 1196.38 | 1375.15 | 1444.72 | 1444.72 |
| food-ticket:food-groups | 45 | 200 | 1237.76 | 1355.89 | 1407.43 | 1407.43 |
| food-ticket:orders | 90 | 200 | 1210.69 | 1362.81 | 1377.21 | 1469.24 |
| food-ticket:absent | 45 | 200 | 1057.57 | 1195.19 | 1239.02 | 1239.02 |
| food-ticket:guest-requests-report | 45 | 200 | 915.57 | 1003.5 | 1112.91 | 1112.91 |
| food-ticket:food-manual-attendance | 45 | 200 | 1014.96 | 1219.22 | 1348.75 | 1348.75 |
| food-ticket:orders | 90 | 200 | 1210.69 | 1362.81 | 1377.21 | 1469.24 |
| food-ticket:food-menu/status | 45 | 200 | 1202.07 | 1474.07 | 1529.23 | 1529.23 |
| food-ticket:food-menu/month | 45 | 200 | 1222.46 | 1465.22 | 1535.18 | 1535.18 |
| food-ticket:food-menu/catalog | 45 | 200 | 1159.41 | 1309.54 | 1517.88 | 1517.88 |
| food-ticket:food-menu/orders | 45 | 200 | 974.95 | 1123.85 | 1197.59 | 1197.59 |
| food-ticket:food-menu/cancel-requests | 45 | 200 | 1040.41 | 1315.47 | 1783.45 | 1783.45 |
| food-ticket:food-menu/groups | 45 | 200 | 1164.73 | 1319.61 | 1492.45 | 1492.45 |
| food-ticket:food-menu/statistics | 45 | 200 | 1204.61 | 1392.29 | 1473.6 | 1473.6 |
| food-ticket:food-menu/group-orders | 45 | 200 | 1229.23 | 1403.3 | 1507.24 | 1507.24 |
| food-ticket:food-menu/bulk-snapshot | 45 | 200 | 1200.35 | 1416.68 | 1497.84 | 1497.84 |
| food-ticket:food-menu/emergency/people | 45 | 200 | 946.65 | 1144.3 | 1233.46 | 1233.46 |
| food-order:month | 45 | 200 | 968.45 | 1325.48 | 1521.72 | 1521.72 |
| food-order:my-orders | 45 | 200 | 1178.87 | 1350.22 | 1424.6 | 1424.6 |
| food-order:search-users | 45 | 200 | 1173.38 | 1468.63 | 1503.62 | 1503.62 |
| food-order:day-visit | 45 | 200 | 1240.94 | 1441.42 | 1489.34 | 1489.34 |
| food-order:guest-requests (expected deny) | 45 | 403 | 1134.77 | 1284.14 | 1625.53 | 1625.53 |
| traffic:invalid-code lookup | 45 | 200 | 959.45 | 1156.01 | 1199.38 | 1199.38 |

## محدودیت‌ها
- All 250 VUs are synthetic primary-admin accounts; this is not a complete role/permission matrix.
- The 3,000-request phase is read-only apart from synthetic login sessions; write stress is separately covered by the ticket/CD-DVD and food lifecycle tests.
- LDAP/AD, Access/ODBC, printers, physical media, hardware collection, external providers, and production/staging capacity are not exercised.
