# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP errors: 3 (unexpected: 0; expected authorization rejections: 3); JSON error payloads: 1; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 74; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 3
- Elapsed: 2.154 s; median latency: 21.01 ms; max latency: 129.03 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 16.93 |  |
| PASS | POST | `index.php?page=login` | 200 | 125.81 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 56.56 |  |
| PASS | GET | `index.php?page=profile` | 200 | 22.93 |  |
| PASS | GET | `index.php?page=queue` | 200 | 99.56 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 23.77 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 37.76 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 24.52 |  |
| PASS | GET | `index.php?page=settings` | 200 | 62.77 |  |
| PASS | GET | `index.php?page=reports` | 200 | 68.21 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 25.5 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 71.28 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 47.04 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 19.83 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 27.13 |  |
| PASS | GET | `index.php?page=assets` | 200 | 29.54 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 38.13 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 22.58 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 30.18 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 20.8 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 23.38 |  |
| PASS | GET | `index.php?page=governance` | 200 | 129.03 |  |
| PASS | GET | `index.php?page=organization` | 200 | 23.58 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 21.72 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 21.15 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 21.37 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 41.08 |  |
| PASS | GET | `index.php?page=backup` | 200 | 20.28 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 36.59 |  |
| PASS | GET | `index.php?page=services` | 200 | 25.34 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 25.54 |  |
| PASS | GET | `index.php?page=logs` | 200 | 37.43 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 24.77 |  |
| PASS | GET | `index.php?page=help` | 200 | 20.54 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 19.37 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 23.7 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 26.06 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 19.82 |  |
| PASS | GET | `index.php?page=audit` | 200 | 35.6 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 14.87 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 12.02 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 19.53 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 13.81 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 15.74 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 16.94 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 16.92 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 18.11 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 14.68 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 14.15 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-07` | 200 | 16.64 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 14.78 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-22` | 200 | 14.8 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 14.49 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 15.58 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-22&to=2026-10-22` | 200 | 15.18 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 15.5 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.24 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 13.52 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-22` | 200 | 15.54 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 12.36 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 11.94 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 27.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-22` | 200 | 18.63 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 28.2 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 17.43 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 19.1 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 16.72 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-22&to=2026-10-22` | 200 | 15.48 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.37 |  |
| PASS | POST | `index.php?page=login` | 200 | 95.52 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 35.55 |  |
| PASS | GET | `index.php?page=settings` | 403 | 21.01 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 20.33 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
