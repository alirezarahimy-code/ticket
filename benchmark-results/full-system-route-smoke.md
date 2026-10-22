# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated representative route/API smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 27; requester permission checks: 3; total checks: 73
- HTTP error responses: 12; JSON application errors: 10; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 61; failed: 12
- Food order create/cancel: passed
- Elapsed: 2.206 s; median latency: 21.74 ms; max latency: 158.97 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 17.52 |  |
| PASS | POST | `index.php?page=login` | 200 | 158.97 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 54.27 |  |
| PASS | GET | `index.php?page=profile` | 200 | 21.11 |  |
| PASS | GET | `index.php?page=queue` | 200 | 69.59 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 25.46 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 38.77 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 23.06 |  |
| PASS | GET | `index.php?page=settings` | 200 | 65.71 |  |
| PASS | GET | `index.php?page=reports` | 200 | 73.58 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 27.37 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 71.02 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 51.51 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 22.47 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 28.95 |  |
| PASS | GET | `index.php?page=assets` | 200 | 31.92 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 42.17 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 23.16 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 29.22 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 21.18 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 23.98 |  |
| PASS | GET | `index.php?page=governance` | 200 | 126.84 |  |
| PASS | GET | `index.php?page=organization` | 200 | 23.95 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 22.95 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 21.59 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 21.94 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 65.72 |  |
| PASS | GET | `index.php?page=backup` | 200 | 25.68 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 40.91 |  |
| PASS | GET | `index.php?page=services` | 200 | 23.06 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 26.26 |  |
| PASS | GET | `index.php?page=logs` | 200 | 36.84 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 26.29 |  |
| PASS | GET | `index.php?page=help` | 200 | 21.74 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 21.09 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 25.4 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 27.68 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 20.95 |  |
| PASS | GET | `index.php?page=audit` | 200 | 39.95 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 15.43 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 12.75 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 20.86 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 14.89 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 13.87 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 24.4 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 19.01 |  |
| FAIL | GET | `index.php?page=food-ticket&food_api=absence-status` | 400 | 21.12 | HTTP 400; unexpected status 400 |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 21.09 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 14.02 |  |
| FAIL | GET | `index.php?page=food-ticket&food_api=dashboard` | 404 | 12.43 | HTTP 404; unexpected status 404 |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 17.6 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 19.77 |  |
| FAIL | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 14.13 | HTTP 403; unexpected status 403 |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 14.68 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-22` | 200 | 16.22 |  |
| FAIL | GET | `index.php?page=food-order&food_api=food-menu/status` | 404 | 12.71 | HTTP 404; unexpected status 404 |
| FAIL | GET | `index.php?page=food-order&food_api=food-menu/month&month=1405-07` | 404 | 12.6 | HTTP 404; unexpected status 404 |
| FAIL | GET | `index.php?page=food-order&food_api=food-menu/catalog` | 404 | 12.25 | HTTP 404; unexpected status 404 |
| FAIL | GET | `index.php?page=food-order&food_api=food-menu/orders` | 404 | 12.54 | HTTP 404; unexpected status 404 |
| FAIL | GET | `index.php?page=food-order&food_api=food-menu/cancel-requests` | 404 | 13.21 | HTTP 404; unexpected status 404 |
| FAIL | GET | `index.php?page=food-order&food_api=food-menu/groups` | 404 | 13.13 | HTTP 404; unexpected status 404 |
| FAIL | GET | `index.php?page=food-order&food_api=food-menu/statistics&month=1405-07` | 404 | 12.52 | HTTP 404; unexpected status 404 |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.1 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-07` | 200 | 15.88 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 17.9 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 19.29 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-22&to=2026-10-22` | 200 | 15.34 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.72 |  |
| PASS | POST | `index.php?page=login` | 200 | 99.67 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.34 |  |
| FAIL | GET | `index.php?page=settings` | 403 | 24.38 | HTTP 403 |
| FAIL | GET | `index.php?page=food-ticket` | 403 | 21.51 | HTTP 403 |

## Explicit limitations
- No real LDAP/domain, Access/ODBC, printer, hardware inventory, email/SMS, or external AI provider was exercised.
- Only primary-admin and generic requester permissions are runtime-smoked; this is not a complete role matrix or exhaustive write-action test.
- Cron/queue worker execution, printable output, and full inventory collection are not exercised by this route smoke.
- No production endpoint or operational account was used.
