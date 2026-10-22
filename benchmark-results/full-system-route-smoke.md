# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP errors: 3 (unexpected: 0; expected authorization rejections: 3); JSON error payloads: 1; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 74; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 3
- Elapsed: 2.027 s; median latency: 19.43 ms; max latency: 129.53 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 18.71 |  |
| PASS | POST | `index.php?page=login` | 200 | 129.53 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 49.62 |  |
| PASS | GET | `index.php?page=profile` | 200 | 19.36 |  |
| PASS | GET | `index.php?page=queue` | 200 | 94.09 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 22.5 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 35.95 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 21.63 |  |
| PASS | GET | `index.php?page=settings` | 200 | 58.21 |  |
| PASS | GET | `index.php?page=reports` | 200 | 64.7 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 23.59 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 60.56 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 43.65 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 19.16 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 23.98 |  |
| PASS | GET | `index.php?page=assets` | 200 | 31.76 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 40.27 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 20.4 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 25.97 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 21.27 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 21.34 |  |
| PASS | GET | `index.php?page=governance` | 200 | 109.94 |  |
| PASS | GET | `index.php?page=organization` | 200 | 22.16 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 19.14 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 19.43 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 19.3 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 39.48 |  |
| PASS | GET | `index.php?page=backup` | 200 | 19.24 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 37.24 |  |
| PASS | GET | `index.php?page=services` | 200 | 21.89 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 24.18 |  |
| PASS | GET | `index.php?page=logs` | 200 | 33.97 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 22.46 |  |
| PASS | GET | `index.php?page=help` | 200 | 19.21 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 19.49 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 21.88 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 24.23 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 20.36 |  |
| PASS | GET | `index.php?page=audit` | 200 | 33.86 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 14.46 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 11.73 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 18.4 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 13.41 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 14.42 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 16.13 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 15.33 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 16.63 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 13.91 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 13.38 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-07` | 200 | 15.17 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 14.27 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-22` | 200 | 13.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 13.5 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 13.96 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-22&to=2026-10-22` | 200 | 13.51 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 15.05 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.16 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 13.56 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-22` | 200 | 15.33 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 12.54 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 13.59 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 26.65 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-22` | 200 | 17.43 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 20.64 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 14.99 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 16.26 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 15.49 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-22&to=2026-10-22` | 200 | 14.59 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 15.51 |  |
| PASS | POST | `index.php?page=login` | 200 | 100.76 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 30.56 |  |
| PASS | GET | `index.php?page=settings` | 403 | 19.5 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 19.05 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
