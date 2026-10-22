# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP errors: 3 (unexpected: 0; expected authorization rejections: 3); JSON error payloads: 1; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 74; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 3
- Elapsed: 2.316 s; median latency: 22.33 ms; max latency: 179.89 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 18.16 |  |
| PASS | POST | `index.php?page=login` | 200 | 179.89 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 54.4 |  |
| PASS | GET | `index.php?page=profile` | 200 | 20.84 |  |
| PASS | GET | `index.php?page=queue` | 200 | 71.13 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 25.66 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 40.58 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 23.12 |  |
| PASS | GET | `index.php?page=settings` | 200 | 67.4 |  |
| PASS | GET | `index.php?page=reports` | 200 | 71.74 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 27.14 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 72.23 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 48.32 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 22.03 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 26.76 |  |
| PASS | GET | `index.php?page=assets` | 200 | 30.73 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 38.21 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 25.74 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 30.94 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 21.12 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 23.83 |  |
| PASS | GET | `index.php?page=governance` | 200 | 132.48 |  |
| PASS | GET | `index.php?page=organization` | 200 | 24.12 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 24.08 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 20.61 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 22.33 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 42.99 |  |
| PASS | GET | `index.php?page=backup` | 200 | 21.74 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 39.98 |  |
| PASS | GET | `index.php?page=services` | 200 | 24.78 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 27.38 |  |
| PASS | GET | `index.php?page=logs` | 200 | 37.85 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 28.94 |  |
| PASS | GET | `index.php?page=help` | 200 | 22.23 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 23.12 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 24.54 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 27.87 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 21.15 |  |
| PASS | GET | `index.php?page=audit` | 200 | 37.66 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 17.15 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 12.96 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 21.57 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 18.46 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 18.26 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 21.18 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 21.92 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 17.51 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 14.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 13.97 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-07` | 200 | 16.28 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 14.28 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-22` | 200 | 14.5 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 14.01 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 16.47 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-22&to=2026-10-22` | 200 | 14.1 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 15.18 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.57 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 13.6 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-22` | 200 | 16.3 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 12.95 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.45 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 27.36 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-22` | 200 | 23.95 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 33.59 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 21.52 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 58.36 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 18.36 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-22&to=2026-10-22` | 200 | 15.94 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.23 |  |
| PASS | POST | `index.php?page=login` | 200 | 97.88 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 37.92 |  |
| PASS | GET | `index.php?page=settings` | 403 | 21.9 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 22.09 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
