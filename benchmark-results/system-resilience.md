# سامانهٔ آزمون تاب‌آوری مسیرها و APIها (محیط مصنوعی)

- مقصد: `127.0.0.1 only; isolated ephemeral MariaDB; synthetic accounts and fixtures`
- کاربر هم‌زمان: 250; worker PHP: 32
- مسیرهای ثبت‌شده: 66؛ مسیرهای دارای ترافیک: 66; درخواست‌های خواندنی مورد انتظار/انجام‌شده: 3000/3000؛ کل HTTP با ورود: 3500
- وضعیت: PASS; خطاهای درخواست: 0; شکست worker: 0; مسیر بی‌ترافیک: 0
- HTTP error غیرمنتظره: 0; رد دسترسی مورد انتظار: 45; خطای payload/API: 0
- ورود: p95=6557.64 ms؛ خواندن مسیرها/API: p50/p95/p99/max=2230.31/4003.1/5139.17/7902.08 ms
- PHP runtime warning/notice/fatal log: 0; SQL/DB log: 0
- زمان: 41.274s; throughput: 72.68 req/s; latency p50/p95/p99/max: 2230.31/4003.1/5139.17/7902.08 ms
- اوج مجموع CPU پردازه‌های PHP: 197.6%; RSS: 1473.46 MB

| مسیر | تعداد | HTTP مورد انتظار | p50 ms | p95 ms | p99 ms | بیشینه ms |
|---|---:|---:|---:|---:|---:|---:|
| page:dashboard | 46 | 200 | 2078.77 | 3789.46 | 7660.95 | 7660.95 |
| page:profile | 46 | 200 | 2253.15 | 2660.32 | 2891.82 | 2891.82 |
| page:queue | 46 | 200 | 2732.54 | 3338.16 | 3698.33 | 3698.33 |
| page:ticket detail | 46 | 200 | 2345.28 | 2723.77 | 2919.46 | 2919.46 |
| page:new ticket | 46 | 200 | 2271.28 | 2748.1 | 2856.43 | 2856.43 |
| page:local accounts | 46 | 200 | 1932.84 | 2363.35 | 2756.93 | 2756.93 |
| page:settings | 46 | 200 | 2197.55 | 3470.28 | 5981.38 | 5981.38 |
| page:reports | 46 | 200 | 2677.79 | 3046.61 | 3143.29 | 3143.29 |
| page:analytics | 46 | 200 | 2407.77 | 2968.65 | 3110.15 | 3110.15 |
| page:supervisor | 46 | 200 | 2666.18 | 2908.53 | 3262.15 | 3262.15 |
| page:inventory form | 46 | 200 | 3935.98 | 4760.48 | 4796.12 | 4796.12 |
| page:inventory diagnostics | 46 | 200 | 1558.05 | 2580.72 | 2674.06 | 2674.06 |
| page:domain scan page | 46 | 200 | 2279.01 | 2856.54 | 4313.93 | 4313.93 |
| page:asset list | 46 | 200 | 2335.11 | 2839.24 | 3517.6 | 3517.6 |
| page:asset detail | 46 | 200 | 4729.43 | 5159.11 | 5329.74 | 5329.74 |
| page:asset history | 46 | 200 | 1941.14 | 2321.65 | 2360.33 | 2360.33 |
| page:CD/DVD | 46 | 200 | 1585.29 | 2100.05 | 2324.21 | 2324.21 |
| page:CD/DVD history | 46 | 200 | 1007.85 | 2255.11 | 2679.8 | 2679.8 |
| page:traffic control | 46 | 200 | 2101.21 | 2791.52 | 2996.4 | 2996.4 |
| page:governance | 46 | 200 | 5082.15 | 5429.84 | 5574.5 | 5574.5 |
| page:organization | 46 | 200 | 2267.37 | 2767.99 | 2889.12 | 2889.12 |
| page:holidays | 46 | 200 | 2074.13 | 2326.31 | 2374.39 | 2374.39 |
| page:knowledge | 46 | 200 | 1750.67 | 1964.67 | 2047.59 | 2047.59 |
| page:notifications | 46 | 200 | 1203.15 | 2286.35 | 2400.46 | 2400.46 |
| page:search | 46 | 200 | 2416.8 | 2868.12 | 6623.03 | 6623.03 |
| page:backup page (read-only) | 46 | 200 | 2214.84 | 2575.11 | 2711.82 | 2711.82 |
| page:chatbot administration | 46 | 200 | 2484.74 | 3137.48 | 3195.98 | 3195.98 |
| page:services | 46 | 200 | 2397.65 | 2793.01 | 2852.23 | 2852.23 |
| page:system health | 46 | 200 | 2044.33 | 2437.97 | 2453.18 | 2453.18 |
| page:logs | 46 | 200 | 2823.26 | 4385.7 | 4843.82 | 4843.82 |
| page:activity log | 45 | 200 | 2279.58 | 2784.63 | 3251.83 | 3251.83 |
| page:help | 45 | 200 | 2241.02 | 2733.83 | 2934.12 | 2934.12 |
| page:food ticket | 45 | 200 | 2341.09 | 2759.82 | 2982.24 | 2982.24 |
| page:food order | 45 | 200 | 2470.52 | 2956.41 | 3393.35 | 3393.35 |
| page:food personnel link | 45 | 200 | 2070.11 | 2302.42 | 2405.37 | 2405.37 |
| page:change password | 45 | 200 | 1765.99 | 2111.15 | 2221.07 | 2221.07 |
| page:audit | 45 | 200 | 3991.02 | 4859.6 | 5258.17 | 5258.17 |
| food-ticket:health | 45 | 200 | 2406.59 | 2981.19 | 3076.91 | 3076.91 |
| food-ticket:session | 45 | 200 | 2329.3 | 2734.63 | 2936.92 | 2936.92 |
| food-ticket:config | 45 | 200 | 2228.43 | 2600.32 | 2686.21 | 2686.21 |
| food-ticket:monitoring | 45 | 200 | 1968.97 | 2192.23 | 2349.3 | 2349.3 |
| food-ticket:employees | 45 | 200 | 1882.37 | 2224.33 | 2422.08 | 2422.08 |
| food-ticket:guest-cards | 45 | 200 | 2114.26 | 3274.86 | 6861.98 | 6861.98 |
| food-ticket:guest-cap | 45 | 200 | 2274.91 | 2606.71 | 2787.15 | 2787.15 |
| food-ticket:food-groups | 45 | 200 | 2366.99 | 2973.46 | 3010.15 | 3010.15 |
| food-ticket:orders | 90 | 200 | 2271.56 | 2791.93 | 3095.22 | 3145.04 |
| food-ticket:absent | 45 | 200 | 2096.64 | 2286.34 | 2316.42 | 2316.42 |
| food-ticket:guest-requests-report | 45 | 200 | 1751.17 | 2106.56 | 2480.73 | 2480.73 |
| food-ticket:food-manual-attendance | 45 | 200 | 1855.74 | 2483.34 | 5320.37 | 5320.37 |
| food-ticket:orders | 90 | 200 | 2271.56 | 2791.93 | 3095.22 | 3145.04 |
| food-ticket:food-menu/status | 45 | 200 | 2480.88 | 2908.86 | 2922.78 | 2922.78 |
| food-ticket:food-menu/month | 45 | 200 | 2355.18 | 2702.87 | 2915.71 | 2915.71 |
| food-ticket:food-menu/catalog | 45 | 200 | 2306.15 | 2640.27 | 2838.91 | 2838.91 |
| food-ticket:food-menu/orders | 45 | 200 | 1905.93 | 2189.96 | 2657.29 | 2657.29 |
| food-ticket:food-menu/cancel-requests | 45 | 200 | 1954.87 | 2534.16 | 7902.08 | 7902.08 |
| food-ticket:food-menu/groups | 45 | 200 | 2258.3 | 2725.98 | 2942.91 | 2942.91 |
| food-ticket:food-menu/statistics | 45 | 200 | 2427.71 | 2941.56 | 3263.58 | 3263.58 |
| food-ticket:food-menu/group-orders | 45 | 200 | 2351.48 | 2698.23 | 3056.83 | 3056.83 |
| food-ticket:food-menu/bulk-snapshot | 45 | 200 | 2349.09 | 2750.84 | 2925.24 | 2925.24 |
| food-ticket:food-menu/emergency/people | 45 | 200 | 1905.0 | 2142.34 | 2651.95 | 2651.95 |
| food-order:month | 45 | 200 | 1950.77 | 2592.48 | 3857.97 | 3857.97 |
| food-order:my-orders | 45 | 200 | 2184.82 | 2565.95 | 2638.42 | 2638.42 |
| food-order:search-users | 45 | 200 | 2362.56 | 2698.65 | 2982.98 | 2982.98 |
| food-order:day-visit | 45 | 200 | 2311.99 | 2757.01 | 2998.1 | 2998.1 |
| food-order:guest-requests (expected deny) | 45 | 403 | 2178.02 | 2571.99 | 2663.68 | 2663.68 |
| traffic:invalid-code lookup | 45 | 200 | 1895.39 | 2203.53 | 2267.31 | 2267.31 |

## محدودیت‌ها
- All 250 VUs are synthetic primary-admin accounts; this is not a complete role/permission matrix.
- The 3,000-request phase is read-only apart from synthetic login sessions; write stress is separately covered by the ticket/CD-DVD and food lifecycle tests.
- LDAP/AD, Access/ODBC, printers, physical media, hardware collection, external providers, and production/staging capacity are not exercised.
