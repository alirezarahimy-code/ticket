# سامانهٔ آزمون تاب‌آوری مسیرها و APIها (محیط مصنوعی)

- مقصد: `127.0.0.1 only; isolated ephemeral MariaDB; synthetic accounts and fixtures`
- کاربر هم‌زمان: 250; worker PHP: 32
- مسیرهای ثبت‌شده: 66؛ مسیرهای دارای ترافیک: 66; درخواست‌های خواندنی مورد انتظار/انجام‌شده: 3000/3000؛ کل HTTP با ورود: 3500
- وضعیت: PASS; خطاهای درخواست: 0; شکست worker: 0; مسیر بی‌ترافیک: 0
- HTTP error غیرمنتظره: 0; رد دسترسی مورد انتظار: 45; خطای payload/API: 0
- ورود: p95=7432.52 ms؛ خواندن مسیرها/API: p50/p95/p99/max=2165.29/3862.85/4813.65/5482.21 ms
- PHP runtime warning/notice/fatal log: 0; SQL/DB log: 0
- زمان: 39.348s; throughput: 76.24 req/s; latency p50/p95/p99/max: 2165.29/3862.85/4813.65/5482.21 ms
- اوج مجموع CPU پردازه‌های PHP: 217.8%; RSS: 1482.77 MB

| مسیر | تعداد | HTTP مورد انتظار | p50 ms | p95 ms | p99 ms | بیشینه ms |
|---|---:|---:|---:|---:|---:|---:|
| page:dashboard | 46 | 200 | 1852.51 | 2503.56 | 2526.44 | 2526.44 |
| page:profile | 46 | 200 | 2178.13 | 2382.72 | 2452.56 | 2452.56 |
| page:queue | 46 | 200 | 2648.42 | 3109.21 | 3373.42 | 3373.42 |
| page:ticket detail | 46 | 200 | 2204.68 | 2617.63 | 2832.86 | 2832.86 |
| page:new ticket | 46 | 200 | 2244.34 | 2751.15 | 3086.69 | 3086.69 |
| page:local accounts | 46 | 200 | 1750.64 | 1978.44 | 2123.44 | 2123.44 |
| page:settings | 46 | 200 | 2087.33 | 2420.35 | 2617.31 | 2617.31 |
| page:reports | 46 | 200 | 2515.14 | 2814.51 | 2997.11 | 2997.11 |
| page:analytics | 46 | 200 | 2386.96 | 2916.19 | 3046.42 | 3046.42 |
| page:supervisor | 46 | 200 | 2558.1 | 2917.74 | 2995.27 | 2995.27 |
| page:inventory form | 46 | 200 | 3845.04 | 4196.23 | 4296.06 | 4296.06 |
| page:inventory diagnostics | 46 | 200 | 1422.42 | 2292.66 | 2332.03 | 2332.03 |
| page:domain scan page | 46 | 200 | 2141.21 | 2457.88 | 2590.01 | 2590.01 |
| page:asset list | 46 | 200 | 2201.18 | 2488.64 | 2589.21 | 2589.21 |
| page:asset detail | 46 | 200 | 4646.42 | 5133.79 | 5174.38 | 5174.38 |
| page:asset history | 46 | 200 | 1789.92 | 2169.9 | 2213.04 | 2213.04 |
| page:CD/DVD | 46 | 200 | 1436.35 | 1979.8 | 2088.13 | 2088.13 |
| page:CD/DVD history | 46 | 200 | 915.0 | 2308.43 | 2428.49 | 2428.49 |
| page:traffic control | 46 | 200 | 2111.02 | 2486.43 | 2636.9 | 2636.9 |
| page:governance | 46 | 200 | 4821.85 | 5367.67 | 5482.21 | 5482.21 |
| page:organization | 46 | 200 | 2263.96 | 2608.32 | 2964.8 | 2964.8 |
| page:holidays | 46 | 200 | 1836.5 | 2196.03 | 2320.4 | 2320.4 |
| page:knowledge | 46 | 200 | 1586.43 | 1882.65 | 2038.17 | 2038.17 |
| page:notifications | 46 | 200 | 999.84 | 2399.64 | 2437.73 | 2437.73 |
| page:search | 46 | 200 | 2224.44 | 2553.99 | 2643.02 | 2643.02 |
| page:backup page (read-only) | 46 | 200 | 2206.57 | 2420.34 | 2474.14 | 2474.14 |
| page:chatbot administration | 46 | 200 | 2463.7 | 2933.84 | 3135.44 | 3135.44 |
| page:services | 46 | 200 | 2283.69 | 2687.2 | 2896.76 | 2896.76 |
| page:system health | 46 | 200 | 2008.16 | 2174.21 | 2443.51 | 2443.51 |
| page:logs | 46 | 200 | 2594.28 | 4114.43 | 4312.01 | 4312.01 |
| page:activity log | 45 | 200 | 2130.38 | 2471.62 | 2633.71 | 2633.71 |
| page:help | 45 | 200 | 2203.22 | 2544.64 | 2711.42 | 2711.42 |
| page:food ticket | 45 | 200 | 2353.25 | 2591.71 | 2876.26 | 2876.26 |
| page:food order | 45 | 200 | 2426.35 | 2644.04 | 2818.16 | 2818.16 |
| page:food personnel link | 45 | 200 | 1940.65 | 2170.74 | 2193.6 | 2193.6 |
| page:change password | 45 | 200 | 1648.81 | 1958.31 | 2088.83 | 2088.83 |
| page:audit | 45 | 200 | 4002.27 | 4523.99 | 4660.1 | 4660.1 |
| food-ticket:health | 45 | 200 | 2328.81 | 2757.42 | 2801.73 | 2801.73 |
| food-ticket:session | 45 | 200 | 2207.58 | 2588.39 | 2756.6 | 2756.6 |
| food-ticket:config | 45 | 200 | 2194.16 | 2745.02 | 2779.89 | 2779.89 |
| food-ticket:monitoring | 45 | 200 | 1726.25 | 2051.21 | 2127.27 | 2127.27 |
| food-ticket:employees | 45 | 200 | 1721.44 | 2219.3 | 2351.19 | 2351.19 |
| food-ticket:guest-cards | 45 | 200 | 2078.42 | 2411.81 | 2594.0 | 2594.0 |
| food-ticket:guest-cap | 45 | 200 | 2194.47 | 2440.82 | 2501.63 | 2501.63 |
| food-ticket:food-groups | 45 | 200 | 2324.79 | 2816.07 | 2908.72 | 2908.72 |
| food-ticket:orders | 90 | 200 | 2231.53 | 2682.17 | 2786.59 | 2863.04 |
| food-ticket:absent | 45 | 200 | 1981.51 | 2191.48 | 2329.73 | 2329.73 |
| food-ticket:guest-requests-report | 45 | 200 | 1584.77 | 1858.49 | 1923.44 | 1923.44 |
| food-ticket:food-manual-attendance | 45 | 200 | 1769.81 | 2370.72 | 2616.06 | 2616.06 |
| food-ticket:orders | 90 | 200 | 2231.53 | 2682.17 | 2786.59 | 2863.04 |
| food-ticket:food-menu/status | 45 | 200 | 2264.29 | 2706.66 | 3002.18 | 3002.18 |
| food-ticket:food-menu/month | 45 | 200 | 2258.73 | 2651.01 | 2778.1 | 2778.1 |
| food-ticket:food-menu/catalog | 45 | 200 | 2182.99 | 2587.14 | 2736.75 | 2736.75 |
| food-ticket:food-menu/orders | 45 | 200 | 1747.75 | 2138.16 | 2461.83 | 2461.83 |
| food-ticket:food-menu/cancel-requests | 45 | 200 | 1796.19 | 2314.31 | 2375.91 | 2375.91 |
| food-ticket:food-menu/groups | 45 | 200 | 2201.21 | 2457.8 | 2598.04 | 2598.04 |
| food-ticket:food-menu/statistics | 45 | 200 | 2282.52 | 2704.47 | 2835.73 | 2835.73 |
| food-ticket:food-menu/group-orders | 45 | 200 | 2305.99 | 2642.63 | 2897.62 | 2897.62 |
| food-ticket:food-menu/bulk-snapshot | 45 | 200 | 2301.19 | 2606.25 | 2612.95 | 2612.95 |
| food-ticket:food-menu/emergency/people | 45 | 200 | 1706.91 | 2033.18 | 2120.04 | 2120.04 |
| food-order:month | 45 | 200 | 1910.5 | 2416.28 | 2577.93 | 2577.93 |
| food-order:my-orders | 45 | 200 | 2233.37 | 2477.79 | 2515.54 | 2515.54 |
| food-order:search-users | 45 | 200 | 2265.58 | 2639.7 | 2919.51 | 2919.51 |
| food-order:day-visit | 45 | 200 | 2220.1 | 2658.66 | 2836.21 | 2836.21 |
| food-order:guest-requests (expected deny) | 45 | 403 | 2116.52 | 2655.3 | 2709.89 | 2709.89 |
| traffic:invalid-code lookup | 45 | 200 | 1724.96 | 2080.46 | 2148.68 | 2148.68 |

## محدودیت‌ها
- All 250 VUs are synthetic primary-admin accounts; this is not a complete role/permission matrix.
- The 3,000-request phase is read-only apart from synthetic login sessions; write stress is separately covered by the ticket/CD-DVD and food lifecycle tests.
- LDAP/AD, Access/ODBC, printers, physical media, hardware collection, external providers, and production/staging capacity are not exercised.
