# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated app-wide page/API/selected role and write-lifecycle smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 67; requester permission checks: 51; total checks: 160
- HTTP errors: 20 (unexpected: 0; expected authorization rejections: 20); JSON error payloads: 7; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 160; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Inventory agent create/update/read-back: passed; traffic create/update/exit/delete: passed
- Expected access rejections: 20
- Elapsed: 4.654 s; median latency: 18.19 ms; max latency: 138.89 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 18.19 |  |
| PASS | POST | `index.php?page=login` | 200 | 128.56 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 55.37 |  |
| PASS | GET | `index.php?page=profile` | 200 | 21.75 |  |
| PASS | GET | `index.php?page=queue` | 200 | 101.77 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 25.33 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 45.52 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 23.83 |  |
| PASS | GET | `index.php?page=settings` | 200 | 66.22 |  |
| PASS | GET | `index.php?page=reports` | 200 | 76.73 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 27.17 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 72.8 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 50.15 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 22.03 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 27.49 |  |
| PASS | GET | `index.php?page=assets` | 200 | 31.42 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 41.81 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 23.27 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 29.83 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 21.88 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 24.51 |  |
| PASS | GET | `index.php?page=governance` | 200 | 138.89 |  |
| PASS | GET | `index.php?page=organization` | 200 | 23.91 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 22.31 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 22.46 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 22.48 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 62.67 |  |
| PASS | GET | `index.php?page=backup` | 200 | 21.37 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 37.89 |  |
| PASS | GET | `index.php?page=services` | 200 | 23.52 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 25.98 |  |
| PASS | GET | `index.php?page=logs` | 200 | 40.24 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 26.66 |  |
| PASS | GET | `index.php?page=help` | 200 | 22.33 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 21.21 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 25.02 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 27.84 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 21.89 |  |
| PASS | GET | `index.php?page=audit` | 200 | 40.64 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 16.2 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 13.21 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 21.39 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 14.66 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 13.99 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 17.03 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 17.01 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 18.95 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 15.03 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 14.62 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-08` | 200 | 16.6 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 14.89 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-23` | 200 | 14.92 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 14.97 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 15.47 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-23&to=2026-10-23` | 200 | 14.97 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 15.59 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 15.3 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 13.99 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-23` | 200 | 16.12 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 15.32 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absent&from=2026-10-23&to=2026-10-23` | 200 | 15.38 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-requests-report&from=2026-10-23&to=2026-10-23&deputy_id=all` | 200 | 13.51 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-manual-attendance&date=2026-10-23` | 200 | 17.66 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders&date=2026-10-23` | 200 | 14.8 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/group-orders&date=2026-10-23&group_id=all` | 200 | 16.93 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/bulk-snapshot&date=2026-10-23&meal_type=lunch` | 200 | 20.75 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/emergency/people` | 200 | 14.44 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.81 |  |
| PASS | POST | `index.php?page=chatbot-api` | 400 | 18.28 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 20.02 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 18.09 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 14.25 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 21.1 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 15.38 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 13.17 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 15.3 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 38.25 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.98 |  |
| PASS | POST | `index.php?page=login` | 200 | 126.02 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 21.68 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 13.68 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 15.43 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 37.76 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.08 |  |
| PASS | POST | `index.php?page=login` | 200 | 100.13 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 20.69 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 19.68 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 15.87 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 13.88 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 16.77 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 13.87 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 17.45 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 37.3 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.9 |  |
| PASS | POST | `index.php?page=login` | 200 | 125.53 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 21.17 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 19.96 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 403 | 13.98 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 13.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 403 | 14.61 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 13.92 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 15.69 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 38.23 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.54 |  |
| PASS | POST | `index.php?page=login` | 200 | 98.37 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 22.43 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 14.04 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 15.14 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.6 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.54 |  |
| PASS | POST | `index.php?page=login` | 200 | 128.21 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 22.31 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 14.25 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 15.79 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 39.03 |  |
| PASS | GET | `index.php?page=login` | 200 | 18.19 |  |
| PASS | POST | `index.php?page=login` | 200 | 98.1 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 22.83 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 14.05 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 15.48 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.46 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.9 |  |
| PASS | POST | `index.php?page=login` | 200 | 92.48 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 16.3 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 14.03 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 15.48 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 16.89 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.73 |  |
| PASS | POST | `index.php?page=login` | 200 | 92.31 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 16.52 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 14.26 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 15.64 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 16.05 |  |
| PASS | POST | `api/inventory.php` | 401 | 1.96 |  |
| PASS | POST | `api/inventory.php` | 422 | 0.91 |  |
| PASS | POST | `api/inventory.php` | 200 | 12.78 |  |
| PASS | POST | `api/inventory.php` | 200 | 12.91 |  |
| PASS | GET | `index.php?page=asset&id=501` | 200 | 36.21 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 36.7 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=7537800200` | 200 | 13.37 |  |
| PASS | GET | `index.php?page=traffic-control-print&id=1` | 200 | 12.29 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 39.43 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=7537800200` | 200 | 13.67 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 42.24 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=7537800200` | 200 | 13.6 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 37.07 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=7537800200` | 200 | 14.13 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 28.98 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-23` | 200 | 20.1 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 22.2 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 16.0 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 18.15 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 17.98 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-23&to=2026-10-23` | 200 | 16.48 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.18 |  |
| PASS | POST | `index.php?page=login` | 200 | 98.42 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 37.92 |  |
| PASS | GET | `index.php?page=settings` | 403 | 21.87 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 21.98 |  |

## Explicit limitations
- All nine application roles receive representative permission-boundary requests, not a complete role-by-route/action Cartesian matrix.
- Inventory writes use a synthetic fixture payload; no WMI/CIM collection or real device was contacted.
- Traffic, inventory, food-order, food-group, ticket, and CD/DVD flows are tested selectively; this is not every write handler or complete module workflow.
- LDAP/AD, Access/ODBC, printers, physical hardware, external providers, cron/queue workers, and production/staging capacity are not exercised.
- No production endpoint or operational account was used.
