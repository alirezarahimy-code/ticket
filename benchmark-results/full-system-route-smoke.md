# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP errors: 3 (unexpected: 0; expected authorization rejections: 3); JSON error payloads: 1; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 74; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 3
- Elapsed: 1.23 s; median latency: 10.64 ms; max latency: 164.05 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 9.5 |  |
| PASS | POST | `index.php?page=login` | 200 | 164.05 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 33.31 |  |
| PASS | GET | `index.php?page=profile` | 200 | 10.76 |  |
| PASS | GET | `index.php?page=queue` | 200 | 50.77 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 12.14 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 20.95 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 11.38 |  |
| PASS | GET | `index.php?page=settings` | 200 | 30.61 |  |
| PASS | GET | `index.php?page=reports` | 200 | 33.39 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 12.92 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 34.17 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 24.46 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 10.42 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 14.67 |  |
| PASS | GET | `index.php?page=assets` | 200 | 17.63 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 22.52 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 10.64 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 13.73 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 10.28 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 11.32 |  |
| PASS | GET | `index.php?page=governance` | 200 | 55.18 |  |
| PASS | GET | `index.php?page=organization` | 200 | 11.71 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 10.48 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 10.15 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 10.25 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 20.71 |  |
| PASS | GET | `index.php?page=backup` | 200 | 9.94 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 17.86 |  |
| PASS | GET | `index.php?page=services` | 200 | 11.92 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 13.86 |  |
| PASS | GET | `index.php?page=logs` | 200 | 19.95 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 12.88 |  |
| PASS | GET | `index.php?page=help` | 200 | 11.15 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 11.81 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 12.8 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 14.24 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 10.12 |  |
| PASS | GET | `index.php?page=audit` | 200 | 18.84 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 15.01 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 6.67 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 10.46 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 7.89 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 7.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 8.85 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 9.02 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 9.5 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 7.75 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 7.81 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-07` | 200 | 8.84 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 8.24 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-22` | 200 | 7.81 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 8.07 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 8.22 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-22&to=2026-10-22` | 200 | 7.58 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 8.05 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.16 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 7.34 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-22` | 200 | 8.42 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 7.01 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 6.82 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 14.55 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-22` | 200 | 16.43 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 12.51 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 8.47 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 9.64 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 9.31 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-22&to=2026-10-22` | 200 | 8.65 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 8.55 |  |
| PASS | POST | `index.php?page=login` | 200 | 59.57 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 16.3 |  |
| PASS | GET | `index.php?page=settings` | 403 | 10.19 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 10.29 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
