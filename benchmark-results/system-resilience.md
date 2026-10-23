# سامانهٔ آزمون تاب‌آوری مسیرها و APIها (محیط مصنوعی)

- مقصد: `127.0.0.1 only; isolated ephemeral MariaDB; synthetic accounts and fixtures`
- کاربر هم‌زمان: 250; worker PHP: 32
- مسیرهای ثبت‌شده: 59؛ مسیرهای دارای ترافیک: 59; درخواست‌های خواندنی مورد انتظار/انجام‌شده: 3000/3000؛ کل HTTP با ورود: 3500
- وضعیت: PASS; خطاهای درخواست: 0; شکست worker: 0; مسیر بی‌ترافیک: 0
- HTTP error غیرمنتظره: 0; رد دسترسی مورد انتظار: 50; خطای payload/API: 0
- ورود: p95=6022.81 ms؛ خواندن مسیرها/API: p50/p95/p99/max=2223.4/4295.84/4761.02/5419.77 ms
- PHP runtime warning/notice/fatal log: 0; SQL/DB log: 0
- زمان: 40.997s; throughput: 73.18 req/s; latency p50/p95/p99/max: 2223.4/4295.84/4761.02/5419.77 ms
- اوج مجموع CPU پردازه‌های PHP: 198.9%; RSS: 1463.45 MB

| مسیر | تعداد | HTTP مورد انتظار | p50 ms | p95 ms | p99 ms | بیشینه ms |
|---|---:|---:|---:|---:|---:|---:|
| page:dashboard | 51 | 200 | 2427.68 | 2773.45 | 2923.5 | 2923.5 |
| page:profile | 51 | 200 | 2161.48 | 2461.13 | 2509.72 | 2509.72 |
| page:queue | 51 | 200 | 2487.35 | 2793.93 | 3002.46 | 3002.46 |
| page:ticket detail | 51 | 200 | 2210.01 | 2565.56 | 2894.25 | 2894.25 |
| page:new ticket | 51 | 200 | 2309.98 | 2562.61 | 2745.99 | 2745.99 |
| page:local accounts | 51 | 200 | 2212.06 | 2469.96 | 2793.1 | 2793.1 |
| page:settings | 51 | 200 | 2366.57 | 2628.23 | 2686.08 | 2686.08 |
| page:reports | 51 | 200 | 2481.42 | 2891.68 | 2951.0 | 2951.0 |
| page:analytics | 51 | 200 | 2215.45 | 2507.63 | 2735.62 | 2735.62 |
| page:supervisor | 51 | 200 | 2516.36 | 2842.07 | 2894.05 | 2894.05 |
| page:inventory form | 51 | 200 | 4324.09 | 5002.55 | 5017.58 | 5017.58 |
| page:inventory diagnostics | 51 | 200 | 2186.98 | 2487.96 | 2515.55 | 2515.55 |
| page:domain scan page | 51 | 200 | 2174.21 | 2599.88 | 2769.22 | 2769.22 |
| page:asset list | 51 | 200 | 2261.0 | 2661.26 | 2746.1 | 2746.1 |
| page:asset detail | 51 | 200 | 4328.05 | 4945.27 | 5419.77 | 5419.77 |
| page:asset history | 51 | 200 | 2171.67 | 2479.58 | 2605.15 | 2605.15 |
| page:CD/DVD | 51 | 200 | 2243.93 | 2501.87 | 2626.93 | 2626.93 |
| page:CD/DVD history | 51 | 200 | 2174.97 | 2417.06 | 2487.76 | 2487.76 |
| page:traffic control | 51 | 200 | 2184.73 | 2522.91 | 2654.9 | 2654.9 |
| page:governance | 51 | 200 | 4515.56 | 4896.12 | 5032.02 | 5032.02 |
| page:organization | 51 | 200 | 2183.25 | 2484.83 | 2614.05 | 2614.05 |
| page:holidays | 51 | 200 | 2143.42 | 2568.83 | 2662.88 | 2662.88 |
| page:knowledge | 51 | 200 | 2141.09 | 2496.49 | 2654.13 | 2654.13 |
| page:notifications | 51 | 200 | 2152.18 | 2405.0 | 2495.38 | 2495.38 |
| page:search | 51 | 200 | 2244.33 | 2684.21 | 2721.34 | 2721.34 |
| page:backup page (read-only) | 51 | 200 | 2153.9 | 2440.5 | 2596.9 | 2596.9 |
| page:chatbot administration | 51 | 200 | 2200.99 | 2626.42 | 2876.17 | 2876.17 |
| page:services | 51 | 200 | 2189.8 | 2515.23 | 2637.56 | 2637.56 |
| page:system health | 51 | 200 | 2215.52 | 2492.3 | 2576.82 | 2576.82 |
| page:logs | 51 | 200 | 4337.45 | 4855.51 | 5138.55 | 5138.55 |
| page:activity log | 51 | 200 | 2196.08 | 2536.12 | 2691.08 | 2691.08 |
| page:help | 51 | 200 | 2177.84 | 2536.23 | 2569.65 | 2569.65 |
| page:food ticket | 51 | 200 | 2169.23 | 2415.74 | 2593.89 | 2593.89 |
| page:food order | 51 | 200 | 2220.97 | 2604.27 | 2716.97 | 2716.97 |
| page:food personnel link | 51 | 200 | 2194.38 | 2438.76 | 2487.75 | 2487.75 |
| page:change password | 51 | 200 | 2157.58 | 2516.13 | 2549.05 | 2549.05 |
| page:audit | 51 | 200 | 4378.78 | 4862.96 | 4902.75 | 4902.75 |
| food-ticket:health | 51 | 200 | 2177.73 | 2440.91 | 2566.78 | 2566.78 |
| food-ticket:session | 51 | 200 | 2148.42 | 2611.28 | 2670.55 | 2670.55 |
| food-ticket:config | 51 | 200 | 2191.91 | 2491.32 | 2650.6 | 2650.6 |
| food-ticket:monitoring | 51 | 200 | 2179.5 | 2572.85 | 2820.36 | 2820.36 |
| food-ticket:employees | 51 | 200 | 2125.77 | 2407.59 | 2462.67 | 2462.67 |
| food-ticket:guest-cards | 51 | 200 | 2157.93 | 2436.91 | 2555.72 | 2555.72 |
| food-ticket:guest-cap | 51 | 200 | 2222.92 | 2552.7 | 2666.48 | 2666.48 |
| food-ticket:food-groups | 51 | 200 | 2267.92 | 2506.74 | 2653.01 | 2653.01 |
| food-ticket:orders | 51 | 200 | 2215.66 | 2464.77 | 2594.15 | 2594.15 |
| food-ticket:food-menu/status | 51 | 200 | 2162.49 | 2569.2 | 2686.28 | 2686.28 |
| food-ticket:food-menu/month | 51 | 200 | 2229.87 | 2497.08 | 2555.19 | 2555.19 |
| food-ticket:food-menu/catalog | 51 | 200 | 2136.14 | 2541.32 | 2703.68 | 2703.68 |
| food-ticket:food-menu/orders | 51 | 200 | 2179.13 | 2618.6 | 2734.79 | 2734.79 |
| food-ticket:food-menu/cancel-requests | 50 | 200 | 2170.47 | 2533.68 | 2594.1 | 2594.1 |
| food-ticket:food-menu/groups | 50 | 200 | 2224.76 | 2556.6 | 2604.64 | 2604.64 |
| food-ticket:food-menu/statistics | 50 | 200 | 2172.18 | 2400.02 | 2666.08 | 2666.08 |
| food-order:month | 50 | 200 | 2205.63 | 2522.34 | 2627.02 | 2627.02 |
| food-order:my-orders | 50 | 200 | 2176.79 | 2509.96 | 2775.45 | 2775.45 |
| food-order:search-users | 50 | 200 | 2154.27 | 2421.71 | 2615.9 | 2615.9 |
| food-order:day-visit | 50 | 200 | 2185.15 | 2484.66 | 2964.65 | 2964.65 |
| food-order:guest-requests (expected deny) | 50 | 403 | 2140.84 | 2465.01 | 2558.2 | 2558.2 |
| traffic:invalid-code lookup | 50 | 200 | 2117.54 | 2435.82 | 2540.94 | 2540.94 |

## محدودیت‌ها
- All 250 VUs are synthetic primary-admin accounts; this is not a complete role/permission matrix.
- The 3,000-request phase is read-only apart from synthetic login sessions; write stress is separately covered by the ticket/CD-DVD and food lifecycle tests.
- LDAP/AD, Access/ODBC, printers, physical media, hardware collection, external providers, and production/staging capacity are not exercised.
