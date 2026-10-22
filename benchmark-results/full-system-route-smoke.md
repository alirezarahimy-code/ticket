# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP errors: 3 (unexpected: 0; expected authorization rejections: 3); JSON error payloads: 1; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 74; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 3
- Elapsed: 2.172 s; median latency: 21.58 ms; max latency: 130.38 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 17.5 |  |
| PASS | POST | `index.php?page=login` | 200 | 130.38 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 56.72 |  |
| PASS | GET | `index.php?page=profile` | 200 | 21.5 |  |
| PASS | GET | `index.php?page=queue` | 200 | 101.17 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 25.32 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 39.78 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 21.75 |  |
| PASS | GET | `index.php?page=settings` | 200 | 62.15 |  |
| PASS | GET | `index.php?page=reports` | 200 | 73.16 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 26.03 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 70.8 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 48.58 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 20.81 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 26.36 |  |
| PASS | GET | `index.php?page=assets` | 200 | 32.41 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 40.91 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 21.58 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 28.54 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 23.28 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 23.42 |  |
| PASS | GET | `index.php?page=governance` | 200 | 129.26 |  |
| PASS | GET | `index.php?page=organization` | 200 | 22.61 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 21.54 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 21.56 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 21.74 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 41.82 |  |
| PASS | GET | `index.php?page=backup` | 200 | 20.76 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 38.8 |  |
| PASS | GET | `index.php?page=services` | 200 | 23.58 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 27.74 |  |
| PASS | GET | `index.php?page=logs` | 200 | 37.76 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 26.12 |  |
| PASS | GET | `index.php?page=help` | 200 | 21.33 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 21.76 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 24.08 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 27.52 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 19.57 |  |
| PASS | GET | `index.php?page=audit` | 200 | 38.77 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 15.4 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 12.36 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 20.23 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 13.92 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 14.66 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 17.45 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 16.08 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 17.32 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 14.4 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 13.72 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-07` | 200 | 16.02 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 15.31 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-22` | 200 | 14.2 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 14.24 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 14.79 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-22&to=2026-10-22` | 200 | 14.06 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 14.82 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.76 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 13.77 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-22` | 200 | 15.14 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 12.48 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.31 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 26.79 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-22` | 200 | 18.15 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 22.33 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 15.31 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 17.91 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 16.83 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-22&to=2026-10-22` | 200 | 15.56 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.62 |  |
| PASS | POST | `index.php?page=login` | 200 | 95.12 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 34.02 |  |
| PASS | GET | `index.php?page=settings` | 403 | 21.93 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 21.61 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
