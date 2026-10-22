# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 26; requester permission checks: 3; total checks: 74
- HTTP error responses: 5; JSON application errors: 3; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 72; failed: 2
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Expected access rejections: 1
- Elapsed: 2.277 s; median latency: 22.86 ms; max latency: 135.88 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 18.17 |  |
| PASS | POST | `index.php?page=login` | 200 | 135.88 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 58.21 |  |
| PASS | GET | `index.php?page=profile` | 200 | 21.27 |  |
| PASS | GET | `index.php?page=queue` | 200 | 105.2 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 26.07 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 40.4 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 23.8 |  |
| PASS | GET | `index.php?page=settings` | 200 | 64.54 |  |
| PASS | GET | `index.php?page=reports` | 200 | 71.28 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 27.3 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 72.42 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 46.67 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 21.9 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 27.75 |  |
| PASS | GET | `index.php?page=assets` | 200 | 31.82 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 40.6 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 22.86 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 30.36 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 23.99 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 26.53 |  |
| PASS | GET | `index.php?page=governance` | 200 | 134.92 |  |
| PASS | GET | `index.php?page=organization` | 200 | 24.93 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 22.07 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 24.25 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 21.3 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 43.98 |  |
| PASS | GET | `index.php?page=backup` | 200 | 21.9 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 39.49 |  |
| PASS | GET | `index.php?page=services` | 200 | 24.96 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 27.62 |  |
| PASS | GET | `index.php?page=logs` | 200 | 38.83 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 28.53 |  |
| PASS | GET | `index.php?page=help` | 200 | 24.83 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 22.03 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 24.85 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 28.76 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 22.73 |  |
| PASS | GET | `index.php?page=audit` | 200 | 39.16 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 16.2 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 12.97 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 21.54 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 15.14 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 16.41 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 25.15 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 22.92 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 20.46 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 14.65 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 14.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-07` | 200 | 16.34 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 15.14 |  |
| FAIL | GET | `index.php?page=food-ticket&food_api=food-menu/orders` | 400 | 13.41 | HTTP 400; unexpected status 400 |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 15.18 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 15.59 |  |
| FAIL | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&month=1405-07` | 400 | 12.78 | HTTP 400; unexpected status 400 |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 17.62 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 15.29 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 14.44 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-22` | 200 | 16.84 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 13.43 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.58 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 29.81 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-22` | 200 | 19.7 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 23.74 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 16.3 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 18.69 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 18.34 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-22&to=2026-10-22` | 200 | 16.24 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.56 |  |
| PASS | POST | `index.php?page=login` | 200 | 98.27 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.78 |  |
| PASS | GET | `index.php?page=settings` | 403 | 22.43 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 22.57 |  |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
