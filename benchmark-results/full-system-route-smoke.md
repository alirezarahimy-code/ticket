# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated app-wide page/API/selected role and write-lifecycle smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 68; requester permission checks: 55; total checks: 155
- HTTP errors: 25 (unexpected: 4; expected authorization rejections: 21); JSON error payloads: 7; PHP runtime log lines: 0; DB/SQL log lines: 1
- Passed: 148; failed: 7
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Inventory agent create/update/read-back: not passed; traffic create/update/exit/delete: not passed
- Expected access rejections: 21
- Elapsed: 2.462 s; median latency: 10.19 ms; max latency: 76.87 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 8.95 |  |
| PASS | POST | `index.php?page=login` | 200 | 76.15 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 25.81 |  |
| PASS | GET | `index.php?page=profile` | 200 | 10.19 |  |
| PASS | GET | `index.php?page=queue` | 200 | 50.7 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 12.77 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 21.05 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 12.06 |  |
| PASS | GET | `index.php?page=settings` | 200 | 33.07 |  |
| PASS | GET | `index.php?page=reports` | 200 | 33.51 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 15.01 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 33.05 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 25.61 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 10.4 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 15.72 |  |
| PASS | GET | `index.php?page=assets` | 200 | 16.64 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 23.56 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 11.22 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 14.85 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 10.61 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 13.45 |  |
| PASS | GET | `index.php?page=governance` | 200 | 57.23 |  |
| PASS | GET | `index.php?page=organization` | 200 | 14.78 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 10.91 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 11.13 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 11.32 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 22.77 |  |
| PASS | GET | `index.php?page=backup` | 200 | 10.99 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 19.1 |  |
| PASS | GET | `index.php?page=services` | 200 | 12.44 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 13.66 |  |
| PASS | GET | `index.php?page=logs` | 200 | 19.83 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 12.81 |  |
| PASS | GET | `index.php?page=help` | 200 | 11.23 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 11.45 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 12.62 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 14.92 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 10.73 |  |
| PASS | GET | `index.php?page=audit` | 200 | 21.68 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 8.43 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 7.25 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 10.5 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 8.63 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 8.61 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 9.19 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 9.42 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 9.98 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 9.0 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 8.43 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-08` | 200 | 9.23 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 8.62 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-23` | 200 | 7.97 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 8.34 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 8.33 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-23&to=2026-10-23` | 200 | 8.23 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 8.68 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 13.94 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 7.75 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-23` | 200 | 8.97 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 7.49 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absent&from=2026-10-23&to=2026-10-23` | 200 | 8.79 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-requests-report&from=2026-10-23&to=2026-10-23&deputy_id=all` | 200 | 7.69 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-manual-attendance&date=2026-10-23` | 200 | 10.28 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders&date=2026-10-23` | 200 | 9.27 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/group-orders&date=2026-10-23&group_id=all` | 200 | 9.72 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/bulk-snapshot&date=2026-10-23&meal_type=lunch` | 200 | 11.66 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/emergency/people` | 200 | 7.81 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.41 |  |
| PASS | POST | `index.php?page=chatbot-api` | 400 | 13.25 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 13.13 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 11.8 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 8.07 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 10.55 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 10.41 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.32 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.82 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 18.35 |  |
| PASS | GET | `index.php?page=login` | 200 | 10.16 |  |
| PASS | POST | `index.php?page=login` | 200 | 76.4 |  |
| FAIL | GET | `index.php?page=food-ticket` | 403 | 11.66 | HTTP 403; unexpected status 403 |
| FAIL | GET | `index.php?page=food-ticket&food_api=food-groups` | 403 | 7.82 | HTTP 403; unexpected status 403; non-JSON response |
| FAIL | GET | `index.php?page=food-ticket&food_api=employees` | 403 | 7.83 | HTTP 403; unexpected status 403; non-JSON response |
| FAIL | GET | `index.php?page=food-ticket&food_api=config` | 403 | 7.67 | denied role did not receive a JSON authorization error |
| FAIL | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 403 | 7.51 | denied role did not receive a JSON authorization error |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.57 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.64 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 18.94 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.15 |  |
| PASS | POST | `index.php?page=login` | 200 | 63.43 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 11.35 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 10.85 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 8.82 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 7.94 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 8.71 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.38 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.35 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 18.22 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.68 |  |
| PASS | POST | `index.php?page=login` | 200 | 76.87 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 14.3 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 11.64 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 403 | 8.42 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 8.15 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 403 | 7.94 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.78 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.81 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 19.06 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.79 |  |
| PASS | POST | `index.php?page=login` | 200 | 62.96 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 10.83 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.71 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.32 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 16.43 |  |
| PASS | GET | `index.php?page=login` | 200 | 8.78 |  |
| PASS | POST | `index.php?page=login` | 200 | 76.41 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 11.16 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.1 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.62 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 18.43 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.08 |  |
| PASS | POST | `index.php?page=login` | 200 | 62.99 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 11.68 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 8.77 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.24 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 17.46 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.49 |  |
| PASS | POST | `index.php?page=login` | 200 | 60.11 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 8.28 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 7.03 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 7.78 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 8.14 |  |
| PASS | GET | `index.php?page=login` | 200 | 8.31 |  |
| PASS | POST | `index.php?page=login` | 200 | 65.37 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 8.29 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.32 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.97 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 8.11 |  |
| PASS | POST | `api/inventory.php` | 401 | 1.22 |  |
| PASS | POST | `api/inventory.php` | 422 | 0.57 |  |
| FAIL | POST | `api/inventory.php` | 500 | 3.13 | valid synthetic inventory payload was not stored |
| PASS | POST | `index.php?page=traffic-control` | 200 | 18.31 |  |
| FAIL | GET | `index.php?page=traffic-control&traffic_api=lookup&code=6017655736` | 200 | 7.89 | traffic lookup did not return the synthetic row created by the handler |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 15.52 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-23` | 200 | 10.82 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 12.15 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 8.96 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 9.9 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 9.47 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-23&to=2026-10-23` | 200 | 9.33 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.07 |  |
| PASS | POST | `index.php?page=login` | 200 | 72.43 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 17.54 |  |
| PASS | GET | `index.php?page=settings` | 403 | 11.63 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 11.25 |  |

## Explicit limitations
- All nine application roles receive representative permission-boundary requests, not a complete role-by-route/action Cartesian matrix.
- Inventory writes use a synthetic fixture payload; no WMI/CIM collection or real device was contacted.
- Traffic, inventory, food-order, food-group, ticket, and CD/DVD flows are tested selectively; this is not every write handler or complete module workflow.
- LDAP/AD, Access/ODBC, printers, physical hardware, external providers, cron/queue workers, and production/staging capacity are not exercised.
- No production endpoint or operational account was used.
