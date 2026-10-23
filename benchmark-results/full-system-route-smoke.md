# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated app-wide page/API/selected role and write-lifecycle smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 72; requester permission checks: 60; total checks: 190
- HTTP errors: 39 (unexpected: 2; expected authorization rejections: 28); JSON error payloads: 8; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 188; failed: 2
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Inventory agent create/update/read-back: passed; traffic create/update/exit/delete: passed
- Export/CSRF smoke: not passed; layout API: passed; cron HTTP isolation: passed
- Expected access rejections: 28
- Elapsed: 5.853 s; median latency: 16.14 ms; max latency: 1112.29 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 17.81 |  |
| PASS | POST | `index.php?page=login` | 200 | 125.83 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 51.91 |  |
| PASS | GET | `index.php?page=profile` | 200 | 19.51 |  |
| PASS | GET | `index.php?page=queue` | 200 | 95.72 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 26.34 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 37.95 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 24.27 |  |
| PASS | GET | `index.php?page=settings` | 200 | 64.02 |  |
| PASS | GET | `index.php?page=reports` | 200 | 68.91 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 25.61 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 67.85 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 42.17 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 20.35 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 25.22 |  |
| PASS | GET | `index.php?page=assets` | 200 | 28.89 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 39.45 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 21.28 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 28.64 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 21.09 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 22.93 |  |
| PASS | GET | `index.php?page=governance` | 200 | 127.69 |  |
| PASS | GET | `index.php?page=organization` | 200 | 21.85 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 20.63 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 22.13 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 20.8 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 41.1 |  |
| PASS | GET | `index.php?page=backup` | 200 | 20.46 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 35.61 |  |
| PASS | GET | `index.php?page=services` | 200 | 22.11 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 23.67 |  |
| PASS | GET | `index.php?page=logs` | 200 | 34.6 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 24.39 |  |
| PASS | GET | `index.php?page=help` | 200 | 19.73 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 19.73 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 22.87 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 25.93 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 20.15 |  |
| PASS | GET | `index.php?page=audit` | 200 | 35.41 |  |
| PASS | GET | `index.php?action=export_report` | 200 | 1112.29 |  |
| PASS | GET | `index.php?action=export_assets` | 200 | 142.15 |  |
| PASS | GET | `index.php?action=export_activity_logs` | 200 | 14.2 |  |
| PASS | GET | `index.php?action=export_cd_dvd` | 200 | 12.51 |  |
| FAIL | GET | `index.php?action=export_traffic_report` | 422 | 11.11 | HTTP 422; unexpected status 422 |
| PASS | GET | `index.php?action=download_backup&file=missing-ci-backup.sql` | 404 | 10.71 |  |
| PASS | POST | `index.php?action=export_view_xlsx` | 419 | 10.65 |  |
| PASS | POST | `index.php?action=export_view_xlsx` | 200 | 12.34 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 200 | 12.88 |  |
| PASS | POST | `index.php?layout_design_api=1` | 422 | 11.69 |  |
| PASS | GET | `cron/food_ticket_worker.php` | 403 | 1.05 |  |
| PASS | GET | `cron/domain_scan_worker.php` | 403 | 0.79 |  |
| PASS | GET | `cron/ldap_sync.php` | 404 | 0.78 |  |
| PASS | GET | `cron/prune_activity_logs.php` | 404 | 0.96 |  |
| PASS | GET | `cron/sla_maintenance.php` | 404 | 6.75 |  |
| PASS | GET | `cron/ticket_auto_close.php` | 404 | 5.54 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 14.67 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 11.69 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 19.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 13.89 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 13.29 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 16.46 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 15.98 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 16.87 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 13.48 |  |
| FAIL | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=0&date=2026-10-23` | 400 | 15.98 | HTTP 400; unexpected status 400 |
| PASS | GET | `index.php?page=food-ticket&food_api=template` | 200 | 11.87 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees-sample` | 200 | 12.22 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 13.22 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-08` | 200 | 15.61 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 14.22 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-23&meal_type=lunch` | 200 | 13.3 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 12.95 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 14.53 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-23&to=2026-10-23` | 200 | 14.23 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders-export&date=2026-10-23&meal_type=lunch` | 200 | 16.09 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/export&from=2026-10-23&to=2026-10-23&mode=food&meal_type=lunch` | 200 | 15.53 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 14.89 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 13.87 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 13.62 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-23` | 200 | 15.72 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 12.49 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absent&from=2026-10-23&to=2026-10-23` | 200 | 14.77 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-requests-report&from=2026-10-23&to=2026-10-23&deputy_id=all` | 200 | 12.74 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-manual-attendance&date=2026-10-23` | 200 | 16.25 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders&date=2026-10-23` | 200 | 14.02 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/group-orders&date=2026-10-23&group_id=all` | 200 | 16.46 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/bulk-snapshot&date=2026-10-23&meal_type=lunch` | 200 | 20.13 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/emergency/people` | 200 | 12.87 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.0 |  |
| PASS | POST | `index.php?page=chatbot-api` | 400 | 18.45 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 18.79 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 16.94 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 13.49 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 19.0 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 14.51 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.08 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.1 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 200 | 11.28 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.77 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.14 |  |
| PASS | POST | `index.php?page=login` | 200 | 122.62 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 20.83 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 13.12 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 13.9 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 10.95 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 34.98 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.17 |  |
| PASS | POST | `index.php?page=login` | 200 | 96.45 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 19.53 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 17.21 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 14.35 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 12.97 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 15.13 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.69 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.35 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 10.71 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 35.51 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.19 |  |
| PASS | POST | `index.php?page=login` | 200 | 121.11 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 18.08 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 17.83 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 403 | 12.54 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 12.71 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 403 | 12.41 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.83 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.16 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.02 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.93 |  |
| PASS | GET | `index.php?page=login` | 200 | 15.49 |  |
| PASS | POST | `index.php?page=login` | 200 | 93.74 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 20.99 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.53 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.04 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.0 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 34.53 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.27 |  |
| PASS | POST | `index.php?page=login` | 200 | 122.57 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 20.88 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 13.0 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.22 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.22 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.96 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.17 |  |
| PASS | POST | `index.php?page=login` | 200 | 93.4 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 20.58 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 12.98 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.5 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.29 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 35.31 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.29 |  |
| PASS | POST | `index.php?page=login` | 200 | 88.31 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 15.41 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 13.17 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.23 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.21 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 15.25 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.22 |  |
| PASS | POST | `index.php?page=login` | 200 | 88.09 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 14.22 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.83 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 13.82 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.19 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 14.4 |  |
| PASS | POST | `api/inventory.php` | 401 | 1.56 |  |
| PASS | POST | `api/inventory.php` | 422 | 0.77 |  |
| PASS | POST | `api/inventory.php` | 200 | 10.93 |  |
| PASS | POST | `api/inventory.php` | 200 | 10.54 |  |
| PASS | GET | `index.php?page=asset&id=501` | 200 | 34.41 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 33.46 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=2881423787` | 200 | 12.3 |  |
| PASS | GET | `index.php?page=traffic-control-print&id=1` | 200 | 11.43 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 34.39 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=2881423787` | 200 | 12.26 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 36.28 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=2881423787` | 200 | 12.13 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 34.41 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=2881423787` | 200 | 12.42 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 26.83 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-23` | 200 | 18.46 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 20.69 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 15.05 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 16.18 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 17.2 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-23&to=2026-10-23` | 200 | 14.52 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 15.6 |  |
| PASS | POST | `index.php?page=login` | 200 | 93.54 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 34.56 |  |
| PASS | GET | `index.php?page=settings` | 403 | 19.86 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 20.77 |  |

## Explicit limitations
- All nine application roles receive representative permission-boundary requests, not a complete role-by-route/action Cartesian matrix.
- Inventory writes use a synthetic fixture payload; no WMI/CIM collection or real device was contacted.
- Traffic, inventory, food-order, food-group, ticket, and CD/DVD flows are tested selectively; this is not every write handler or complete module workflow.
- LDAP/AD, Access/ODBC, printers, physical hardware, and external providers are not exercised; selected synthetic cron/queue paths are covered separately, not every schedule and failure mode.
- No production endpoint or operational account was used.
