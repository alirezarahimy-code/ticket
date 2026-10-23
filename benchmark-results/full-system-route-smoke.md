# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP errors: 3 (unexpected: 0; expected authorization rejections: 3); JSON error payloads: 1; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 74; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 3
- Elapsed: 1.14 s; median latency: 10.9 ms; max latency: 76.19 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 9.25 |  |
| PASS | POST | `index.php?page=login` | 200 | 76.19 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 24.75 |  |
| PASS | GET | `index.php?page=profile` | 200 | 11.5 |  |
| PASS | GET | `index.php?page=queue` | 200 | 50.74 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 12.61 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 21.32 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 12.98 |  |
| PASS | GET | `index.php?page=settings` | 200 | 30.42 |  |
| PASS | GET | `index.php?page=reports` | 200 | 32.61 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 13.81 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 30.39 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 25.13 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 11.39 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 13.78 |  |
| PASS | GET | `index.php?page=assets` | 200 | 15.58 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 26.33 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 12.84 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 14.87 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 10.2 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 11.35 |  |
| PASS | GET | `index.php?page=governance` | 200 | 54.2 |  |
| PASS | GET | `index.php?page=organization` | 200 | 10.98 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 9.93 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 10.09 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 10.19 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 20.85 |  |
| PASS | GET | `index.php?page=backup` | 200 | 9.88 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 20.34 |  |
| PASS | GET | `index.php?page=services` | 200 | 11.27 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 12.66 |  |
| PASS | GET | `index.php?page=logs` | 200 | 18.34 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 12.4 |  |
| PASS | GET | `index.php?page=help` | 200 | 10.34 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 10.79 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 12.32 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 13.38 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 9.82 |  |
| PASS | GET | `index.php?page=audit` | 200 | 18.24 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 8.04 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 6.61 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 10.11 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 7.68 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 7.84 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 8.69 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 8.89 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 9.41 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 13.07 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 9.63 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-08` | 200 | 8.82 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 8.31 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-23` | 200 | 7.76 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 7.85 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 8.19 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-23&to=2026-10-23` | 200 | 7.69 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 8.29 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.1 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 7.19 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-23` | 200 | 8.65 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 7.04 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 6.86 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 27.4 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-23` | 200 | 9.62 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 11.87 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 8.18 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 9.46 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 9.03 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-23&to=2026-10-23` | 200 | 8.41 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 8.29 |  |
| PASS | POST | `index.php?page=login` | 200 | 63.13 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 16.55 |  |
| PASS | GET | `index.php?page=settings` | 403 | 10.9 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 10.95 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
