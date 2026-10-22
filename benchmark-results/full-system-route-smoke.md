# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP errors: 3 (unexpected: 0; expected authorization rejections: 3); JSON error payloads: 1; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 74; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 3
- Elapsed: 1.574 s; median latency: 15.44 ms; max latency: 88.92 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 11.33 |  |
| PASS | POST | `index.php?page=login` | 200 | 88.92 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 35.13 |  |
| PASS | GET | `index.php?page=profile` | 200 | 13.78 |  |
| PASS | GET | `index.php?page=queue` | 200 | 77.22 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 16.57 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 31.12 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 15.99 |  |
| PASS | GET | `index.php?page=settings` | 200 | 62.04 |  |
| PASS | GET | `index.php?page=reports` | 200 | 47.26 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 20.32 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 47.31 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 30.04 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 16.51 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 20.88 |  |
| PASS | GET | `index.php?page=assets` | 200 | 19.66 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 27.89 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 17.07 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 19.33 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 14.4 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 15.44 |  |
| PASS | GET | `index.php?page=governance` | 200 | 82.19 |  |
| PASS | GET | `index.php?page=organization` | 200 | 17.53 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 16.5 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 19.67 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 15.26 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 30.14 |  |
| PASS | GET | `index.php?page=backup` | 200 | 13.26 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 25.58 |  |
| PASS | GET | `index.php?page=services` | 200 | 15.3 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 19.84 |  |
| PASS | GET | `index.php?page=logs` | 200 | 25.51 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 15.91 |  |
| PASS | GET | `index.php?page=help` | 200 | 14.74 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 14.48 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 21.41 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 19.52 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 15.6 |  |
| PASS | GET | `index.php?page=audit` | 200 | 23.84 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 14.14 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 8.53 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 13.37 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 10.38 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 9.71 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 11.43 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 12.93 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 12.36 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 13.77 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 11.84 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-07` | 200 | 12.91 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 12.4 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-22` | 200 | 9.88 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 8.76 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 10.02 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-22&to=2026-10-22` | 200 | 12.3 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 9.82 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 10.39 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 28.18 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-22` | 200 | 11.0 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 8.63 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 9.55 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 18.81 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-22` | 200 | 14.64 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 16.29 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 11.44 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 13.05 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 11.79 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-22&to=2026-10-22` | 200 | 10.65 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 14.99 |  |
| PASS | POST | `index.php?page=login` | 200 | 72.17 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 23.9 |  |
| PASS | GET | `index.php?page=settings` | 403 | 14.54 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 15.85 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
