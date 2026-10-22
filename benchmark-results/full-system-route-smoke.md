# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP errors: 3 (unexpected: 0; expected authorization rejections: 3); JSON error payloads: 1; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 74; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 3
- Elapsed: 1.974 s; median latency: 18.65 ms; max latency: 131.52 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 15.99 |  |
| PASS | POST | `index.php?page=login` | 200 | 131.52 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 49.97 |  |
| PASS | GET | `index.php?page=profile` | 200 | 18.65 |  |
| PASS | GET | `index.php?page=queue` | 200 | 92.02 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 25.22 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 38.27 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 20.65 |  |
| PASS | GET | `index.php?page=settings` | 200 | 58.63 |  |
| PASS | GET | `index.php?page=reports` | 200 | 63.92 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 23.57 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 61.02 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 44.21 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 21.25 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 22.9 |  |
| PASS | GET | `index.php?page=assets` | 200 | 28.49 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 35.71 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 19.58 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 28.1 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 18.26 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 20.55 |  |
| PASS | GET | `index.php?page=governance` | 200 | 108.56 |  |
| PASS | GET | `index.php?page=organization` | 200 | 22.37 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 18.36 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 18.28 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 18.65 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 40.93 |  |
| PASS | GET | `index.php?page=backup` | 200 | 19.35 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 33.89 |  |
| PASS | GET | `index.php?page=services` | 200 | 20.61 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 23.49 |  |
| PASS | GET | `index.php?page=logs` | 200 | 32.51 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 22.53 |  |
| PASS | GET | `index.php?page=help` | 200 | 18.59 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 19.13 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 21.19 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 23.93 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 17.76 |  |
| PASS | GET | `index.php?page=audit` | 200 | 32.25 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 13.79 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 11.37 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 17.89 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 12.98 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 12.76 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 15.22 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 14.66 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 16.06 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 12.93 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 12.82 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-07` | 200 | 14.74 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 13.61 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-22` | 200 | 13.16 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 13.3 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 13.36 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-22&to=2026-10-22` | 200 | 13.16 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 14.16 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 13.33 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 12.41 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-22` | 200 | 14.21 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 11.8 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 10.89 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 25.07 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-22` | 200 | 16.36 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 19.71 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 13.91 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 15.65 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 15.11 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-22&to=2026-10-22` | 200 | 13.63 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 14.91 |  |
| PASS | POST | `index.php?page=login` | 200 | 99.04 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 29.48 |  |
| PASS | GET | `index.php?page=settings` | 403 | 18.75 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 18.33 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
