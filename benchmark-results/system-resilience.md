# سامانهٔ آزمون تاب‌آوری مسیرها و APIها (محیط مصنوعی)

- مقصد: `127.0.0.1 only; isolated ephemeral MariaDB; synthetic accounts and fixtures`
- کاربر هم‌زمان: 250; worker PHP: 32
- مسیرهای ثبت‌شده: 66؛ مسیرهای دارای ترافیک: 66; درخواست‌های خواندنی مورد انتظار/انجام‌شده: 3000/3000؛ کل HTTP با ورود: 3500
- وضعیت: PASS; خطاهای درخواست: 0; شکست worker: 0; مسیر بی‌ترافیک: 0
- HTTP error غیرمنتظره: 0; رد دسترسی مورد انتظار: 45; خطای payload/API: 0
- ورود: p95=7022.25 ms؛ خواندن مسیرها/API: p50/p95/p99/max=2257.35/3821.86/5059.61/5993.04 ms
- PHP runtime warning/notice/fatal log: 0; SQL/DB log: 0
- زمان: 39.984s; throughput: 75.03 req/s; latency p50/p95/p99/max: 2257.35/3821.86/5059.61/5993.04 ms
- اوج مجموع CPU پردازه‌های PHP: 216.3%; RSS: 1468.81 MB

| مسیر | تعداد | HTTP مورد انتظار | p50 ms | p95 ms | p99 ms | بیشینه ms |
|---|---:|---:|---:|---:|---:|---:|
| page:dashboard | 46 | 200 | 2040.87 | 2438.48 | 2777.37 | 2777.37 |
| page:profile | 46 | 200 | 2244.12 | 2590.45 | 2924.15 | 2924.15 |
| page:queue | 46 | 200 | 2783.91 | 3201.78 | 3357.82 | 3357.82 |
| page:ticket detail | 46 | 200 | 2352.82 | 2585.64 | 2723.66 | 2723.66 |
| page:new ticket | 46 | 200 | 2241.64 | 2514.83 | 2918.82 | 2918.82 |
| page:local accounts | 46 | 200 | 1749.86 | 1972.71 | 2112.09 | 2112.09 |
| page:settings | 46 | 200 | 1991.65 | 2372.58 | 2506.94 | 2506.94 |
| page:reports | 46 | 200 | 2565.05 | 2845.86 | 3130.6 | 3130.6 |
| page:analytics | 46 | 200 | 2502.89 | 2818.39 | 2919.99 | 2919.99 |
| page:supervisor | 46 | 200 | 2675.99 | 2941.93 | 3013.01 | 3013.01 |
| page:inventory form | 46 | 200 | 3697.17 | 4261.92 | 4346.92 | 4346.92 |
| page:inventory diagnostics | 46 | 200 | 1534.85 | 2176.21 | 2301.26 | 2301.26 |
| page:domain scan page | 46 | 200 | 2162.94 | 2484.23 | 2634.29 | 2634.29 |
| page:asset list | 46 | 200 | 2458.19 | 2796.81 | 2966.49 | 2966.49 |
| page:asset detail | 46 | 200 | 4706.64 | 5034.46 | 5186.6 | 5186.6 |
| page:asset history | 46 | 200 | 1889.76 | 2328.54 | 2362.38 | 2362.38 |
| page:CD/DVD | 46 | 200 | 1647.89 | 1940.9 | 2033.32 | 2033.32 |
| page:CD/DVD history | 46 | 200 | 1155.55 | 2185.94 | 2266.46 | 2266.46 |
| page:traffic control | 46 | 200 | 2040.88 | 2399.38 | 2532.94 | 2532.94 |
| page:governance | 46 | 200 | 5100.64 | 5578.85 | 5993.04 | 5993.04 |
| page:organization | 46 | 200 | 2359.71 | 2532.07 | 2673.61 | 2673.61 |
| page:holidays | 46 | 200 | 1939.22 | 2287.93 | 2607.65 | 2607.65 |
| page:knowledge | 46 | 200 | 1669.32 | 1871.7 | 1902.25 | 1902.25 |
| page:notifications | 46 | 200 | 1271.36 | 2083.6 | 2267.09 | 2267.09 |
| page:search | 46 | 200 | 2225.37 | 2546.58 | 3519.09 | 3519.09 |
| page:backup page (read-only) | 46 | 200 | 2402.75 | 2670.62 | 2774.14 | 2774.14 |
| page:chatbot administration | 46 | 200 | 2499.2 | 2751.53 | 2897.99 | 2897.99 |
| page:services | 46 | 200 | 2384.87 | 2725.11 | 2832.06 | 2832.06 |
| page:system health | 46 | 200 | 1982.11 | 2269.21 | 2419.03 | 2419.03 |
| page:logs | 46 | 200 | 2958.15 | 3989.93 | 4177.79 | 4177.79 |
| page:activity log | 45 | 200 | 2169.38 | 2667.91 | 3237.62 | 3237.62 |
| page:help | 45 | 200 | 2413.97 | 2738.6 | 2893.25 | 2893.25 |
| page:food ticket | 45 | 200 | 2433.18 | 2823.05 | 2944.99 | 2944.99 |
| page:food order | 45 | 200 | 2379.35 | 2716.9 | 2805.94 | 2805.94 |
| page:food personnel link | 45 | 200 | 1887.37 | 2376.94 | 2479.97 | 2479.97 |
| page:change password | 45 | 200 | 1650.24 | 1834.58 | 1863.0 | 1863.0 |
| page:audit | 45 | 200 | 4050.72 | 4862.98 | 5573.29 | 5573.29 |
| food-ticket:health | 45 | 200 | 2495.66 | 2785.66 | 2803.02 | 2803.02 |
| food-ticket:session | 45 | 200 | 2374.99 | 2533.34 | 2779.95 | 2779.95 |
| food-ticket:config | 45 | 200 | 2195.33 | 2527.01 | 2680.35 | 2680.35 |
| food-ticket:monitoring | 45 | 200 | 1747.46 | 1950.08 | 2195.67 | 2195.67 |
| food-ticket:employees | 45 | 200 | 1710.77 | 2139.2 | 2282.61 | 2282.61 |
| food-ticket:guest-cards | 45 | 200 | 2036.54 | 2481.01 | 2515.14 | 2515.14 |
| food-ticket:guest-cap | 45 | 200 | 2359.35 | 2603.42 | 2794.39 | 2794.39 |
| food-ticket:food-groups | 45 | 200 | 2428.36 | 2666.6 | 2758.67 | 2758.67 |
| food-ticket:orders | 90 | 200 | 2313.97 | 2683.7 | 2742.51 | 2918.29 |
| food-ticket:absent | 45 | 200 | 1946.2 | 2327.86 | 2338.18 | 2338.18 |
| food-ticket:guest-requests-report | 45 | 200 | 1658.26 | 1798.89 | 2006.65 | 2006.65 |
| food-ticket:food-manual-attendance | 45 | 200 | 1805.21 | 2261.03 | 2504.84 | 2504.84 |
| food-ticket:orders | 90 | 200 | 2313.97 | 2683.7 | 2742.51 | 2918.29 |
| food-ticket:food-menu/status | 45 | 200 | 2453.45 | 2747.71 | 2994.29 | 2994.29 |
| food-ticket:food-menu/month | 45 | 200 | 2427.64 | 2856.8 | 2874.77 | 2874.77 |
| food-ticket:food-menu/catalog | 45 | 200 | 2187.93 | 2551.17 | 2697.77 | 2697.77 |
| food-ticket:food-menu/orders | 45 | 200 | 1777.81 | 2042.5 | 2500.98 | 2500.98 |
| food-ticket:food-menu/cancel-requests | 45 | 200 | 1820.4 | 2195.68 | 2887.98 | 2887.98 |
| food-ticket:food-menu/groups | 45 | 200 | 2239.85 | 2466.12 | 2694.33 | 2694.33 |
| food-ticket:food-menu/statistics | 45 | 200 | 2437.03 | 2915.53 | 3056.07 | 3056.07 |
| food-ticket:food-menu/group-orders | 45 | 200 | 2426.4 | 2840.68 | 2914.29 | 2914.29 |
| food-ticket:food-menu/bulk-snapshot | 45 | 200 | 2270.05 | 2525.17 | 2964.58 | 2964.58 |
| food-ticket:food-menu/emergency/people | 45 | 200 | 1750.35 | 2129.22 | 2234.45 | 2234.45 |
| food-order:month | 45 | 200 | 1858.6 | 2482.48 | 3052.13 | 3052.13 |
| food-order:my-orders | 45 | 200 | 2224.47 | 2577.15 | 2658.85 | 2658.85 |
| food-order:search-users | 45 | 200 | 2490.81 | 2738.7 | 2916.83 | 2916.83 |
| food-order:day-visit | 45 | 200 | 2416.37 | 2633.55 | 2815.54 | 2815.54 |
| food-order:guest-requests (expected deny) | 45 | 403 | 2173.55 | 2548.72 | 2771.24 | 2771.24 |
| traffic:invalid-code lookup | 45 | 200 | 1730.81 | 2202.23 | 2272.37 | 2272.37 |

## محدودیت‌ها
- All 250 VUs are synthetic primary-admin accounts; this is not a complete role/permission matrix.
- The 3,000-request phase is read-only apart from synthetic login sessions; write stress is separately covered by the ticket/CD-DVD and food lifecycle tests.
- LDAP/AD, Access/ODBC, printers, physical media, hardware collection, external providers, and production/staging capacity are not exercised.
