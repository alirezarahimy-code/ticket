# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP errors: 3 (unexpected: 0; expected authorization rejections: 3); JSON error payloads: 1; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 74; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 3
- Elapsed: 2.151 s; median latency: 21.07 ms; max latency: 131.63 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 17.45 |  |
| PASS | POST | `index.php?page=login` | 200 | 127.24 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 54.59 |  |
| PASS | GET | `index.php?page=profile` | 200 | 21.07 |  |
| PASS | GET | `index.php?page=queue` | 200 | 100.65 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 24.81 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 39.19 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 21.54 |  |
| PASS | GET | `index.php?page=settings` | 200 | 62.9 |  |
| PASS | GET | `index.php?page=reports` | 200 | 72.55 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 25.79 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 69.47 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 44.33 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 20.57 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 25.52 |  |
| PASS | GET | `index.php?page=assets` | 200 | 33.9 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 41.43 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 21.46 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 28.14 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 20.19 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 25.84 |  |
| PASS | GET | `index.php?page=governance` | 200 | 131.63 |  |
| PASS | GET | `index.php?page=organization` | 200 | 24.08 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 21.43 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 20.96 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 20.65 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 44.94 |  |
| PASS | GET | `index.php?page=backup` | 200 | 20.79 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 38.57 |  |
| PASS | GET | `index.php?page=services` | 200 | 23.07 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 25.03 |  |
| PASS | GET | `index.php?page=logs` | 200 | 36.5 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 23.79 |  |
| PASS | GET | `index.php?page=help` | 200 | 21.08 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 22.32 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 23.62 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 27.19 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 20.43 |  |
| PASS | GET | `index.php?page=audit` | 200 | 36.9 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 16.68 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 11.8 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 20.2 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 14.47 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 14.04 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 15.99 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 15.88 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 16.78 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 13.62 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 13.65 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-08` | 200 | 16.08 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 14.37 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-23` | 200 | 14.13 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 13.84 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 14.99 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-23&to=2026-10-23` | 200 | 14.07 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 15.3 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.27 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 13.83 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-23` | 200 | 15.72 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 12.11 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 11.8 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 27.52 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-23` | 200 | 18.09 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 22.27 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 14.46 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 17.39 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 16.21 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-23&to=2026-10-23` | 200 | 15.07 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.17 |  |
| PASS | POST | `index.php?page=login` | 200 | 96.43 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.32 |  |
| PASS | GET | `index.php?page=settings` | 403 | 20.73 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 21.58 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
