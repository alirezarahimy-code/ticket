# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP errors: 3 (unexpected: 0; expected authorization rejections: 3); JSON error payloads: 1; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 74; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 3
- Elapsed: 2.172 s; median latency: 21.33 ms; max latency: 128.79 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 18.21 |  |
| PASS | POST | `index.php?page=login` | 200 | 126.36 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 54.29 |  |
| PASS | GET | `index.php?page=profile` | 200 | 21.49 |  |
| PASS | GET | `index.php?page=queue` | 200 | 98.68 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 25.36 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 40.96 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 22.71 |  |
| PASS | GET | `index.php?page=settings` | 200 | 67.68 |  |
| PASS | GET | `index.php?page=reports` | 200 | 70.66 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 26.05 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 75.12 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 45.17 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 20.96 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 27.52 |  |
| PASS | GET | `index.php?page=assets` | 200 | 31.01 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 38.93 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 21.8 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 29.57 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 21.2 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 24.04 |  |
| PASS | GET | `index.php?page=governance` | 200 | 128.79 |  |
| PASS | GET | `index.php?page=organization` | 200 | 22.41 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 21.08 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 23.6 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 21.08 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 42.51 |  |
| PASS | GET | `index.php?page=backup` | 200 | 20.29 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 35.23 |  |
| PASS | GET | `index.php?page=services` | 200 | 23.6 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 27.87 |  |
| PASS | GET | `index.php?page=logs` | 200 | 36.77 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 25.49 |  |
| PASS | GET | `index.php?page=help` | 200 | 24.0 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 23.49 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 23.0 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 27.04 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 20.59 |  |
| PASS | GET | `index.php?page=audit` | 200 | 37.35 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 14.96 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 12.58 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 20.2 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 14.65 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 15.51 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 16.62 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 18.68 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 17.33 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 14.57 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 14.14 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-08` | 200 | 15.52 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 14.88 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-23` | 200 | 13.99 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 14.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 14.61 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-23&to=2026-10-23` | 200 | 13.87 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 15.08 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.58 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 13.81 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-23` | 200 | 15.2 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 12.7 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.19 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 28.74 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-23` | 200 | 18.54 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 22.16 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 15.43 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 17.61 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 17.46 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-23&to=2026-10-23` | 200 | 15.57 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.87 |  |
| PASS | POST | `index.php?page=login` | 200 | 95.98 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.38 |  |
| PASS | GET | `index.php?page=settings` | 403 | 21.33 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 20.55 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
