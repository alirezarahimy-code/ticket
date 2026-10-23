# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated app-wide page/API/selected role and write-lifecycle smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 71; requester permission checks: 60; total checks: 189
- HTTP errors: 37 (unexpected: 0; expected authorization rejections: 28); JSON error payloads: 7; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 189; failed: 0
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Inventory agent create/update/read-back: passed; traffic create/update/exit/delete: passed
- Export/CSRF smoke: passed; layout API: passed; cron HTTP isolation: passed
- Expected access rejections: 28
- Elapsed: 5.91 s; median latency: 14.19 ms; max latency: 744.26 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 12.07 |  |
| PASS | POST | `index.php?page=login` | 200 | 97.05 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 35.76 |  |
| PASS | GET | `index.php?page=profile` | 200 | 14.03 |  |
| PASS | GET | `index.php?page=queue` | 200 | 70.01 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 16.99 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 34.86 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 15.56 |  |
| PASS | GET | `index.php?page=settings` | 200 | 259.44 |  |
| PASS | GET | `index.php?page=reports` | 200 | 45.44 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 17.88 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 48.41 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 33.88 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 15.7 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 36.01 |  |
| PASS | GET | `index.php?page=assets` | 200 | 22.42 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 26.79 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 14.19 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 18.77 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 15.84 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 15.63 |  |
| PASS | GET | `index.php?page=governance` | 200 | 81.89 |  |
| PASS | GET | `index.php?page=organization` | 200 | 16.46 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 13.98 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 16.2 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 14.15 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 29.15 |  |
| PASS | GET | `index.php?page=backup` | 200 | 13.99 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 25.67 |  |
| PASS | GET | `index.php?page=services` | 200 | 15.95 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 18.98 |  |
| PASS | GET | `index.php?page=logs` | 200 | 24.39 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 16.9 |  |
| PASS | GET | `index.php?page=help` | 200 | 14.18 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 14.48 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 15.72 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 17.86 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 13.44 |  |
| PASS | GET | `index.php?page=audit` | 200 | 24.75 |  |
| PASS | GET | `index.php?action=export_report` | 200 | 744.26 |  |
| PASS | GET | `index.php?action=export_assets` | 200 | 114.85 |  |
| PASS | GET | `index.php?action=export_activity_logs` | 200 | 13.36 |  |
| PASS | GET | `index.php?action=export_cd_dvd` | 200 | 67.31 |  |
| PASS | GET | `index.php?action=export_traffic_report&from_date=1405-08-01&to_date=1405-08-02&all_destinations=1` | 200 | 63.07 |  |
| PASS | GET | `index.php?action=download_backup&file=missing-ci-backup.sql` | 404 | 8.73 |  |
| PASS | POST | `index.php?action=export_view_xlsx` | 419 | 8.21 |  |
| PASS | POST | `index.php?action=export_view_xlsx` | 200 | 45.15 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 200 | 8.67 |  |
| PASS | POST | `index.php?layout_design_api=1` | 422 | 8.15 |  |
| PASS | GET | `cron/food_ticket_worker.php` | 403 | 0.93 |  |
| PASS | GET | `cron/domain_scan_worker.php` | 403 | 0.76 |  |
| PASS | GET | `cron/ldap_sync.php` | 404 | 1.58 |  |
| PASS | GET | `cron/prune_activity_logs.php` | 404 | 0.72 |  |
| PASS | GET | `cron/sla_maintenance.php` | 404 | 4.25 |  |
| PASS | GET | `cron/ticket_auto_close.php` | 404 | 4.15 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 11.27 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 25.41 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 27.44 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 10.3 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 10.08 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 11.45 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 11.21 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 12.12 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 9.95 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=template` | 200 | 8.95 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees-sample` | 200 | 9.65 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 9.75 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-08` | 200 | 11.51 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 10.1 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-23&meal_type=lunch` | 200 | 9.85 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 9.94 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 10.29 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-23&to=2026-10-23` | 200 | 9.96 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders-export&date=2026-10-23&meal_type=lunch` | 200 | 30.25 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/export&from=2026-10-23&to=2026-10-23&mode=food&meal_type=lunch` | 200 | 13.58 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 10.77 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 10.27 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 9.49 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-23` | 200 | 11.4 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 10.65 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absent&from=2026-10-23&to=2026-10-23` | 200 | 11.0 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-requests-report&from=2026-10-23&to=2026-10-23&deputy_id=all` | 200 | 26.37 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-manual-attendance&date=2026-10-23` | 200 | 17.62 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders&date=2026-10-23` | 200 | 18.55 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/group-orders&date=2026-10-23&group_id=all` | 200 | 32.55 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/bulk-snapshot&date=2026-10-23&meal_type=lunch` | 200 | 14.77 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/emergency/people` | 200 | 20.3 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.91 |  |
| PASS | POST | `index.php?page=chatbot-api` | 400 | 12.02 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 14.21 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 12.3 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 9.99 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 13.53 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 10.55 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.93 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 10.31 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 200 | 8.25 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 25.37 |  |
| PASS | GET | `index.php?page=login` | 200 | 11.64 |  |
| PASS | POST | `index.php?page=login` | 200 | 246.45 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 14.63 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 91.96 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 46.79 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.48 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 24.41 |  |
| PASS | GET | `index.php?page=login` | 200 | 12.64 |  |
| PASS | POST | `index.php?page=login` | 200 | 82.55 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 14.39 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 14.19 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 20.16 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 16.16 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 32.46 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 48.04 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.83 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.38 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 23.34 |  |
| PASS | GET | `index.php?page=login` | 200 | 11.88 |  |
| PASS | POST | `index.php?page=login` | 200 | 97.36 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 14.45 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 122.13 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 403 | 51.43 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 15.59 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 403 | 20.16 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 76.42 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 18.86 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.3 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 23.95 |  |
| PASS | GET | `index.php?page=login` | 200 | 10.94 |  |
| PASS | POST | `index.php?page=login` | 200 | 179.57 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 14.44 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 76.98 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 69.3 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.44 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 23.24 |  |
| PASS | GET | `index.php?page=login` | 200 | 11.63 |  |
| PASS | POST | `index.php?page=login` | 200 | 167.19 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 14.17 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 9.88 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 11.4 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.17 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 24.18 |  |
| PASS | GET | `index.php?page=login` | 200 | 11.13 |  |
| PASS | POST | `index.php?page=login` | 200 | 83.49 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 14.85 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 9.88 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 11.66 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.55 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 21.91 |  |
| PASS | GET | `index.php?page=login` | 200 | 11.32 |  |
| PASS | POST | `index.php?page=login` | 200 | 112.56 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 10.66 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 85.21 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 11.1 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.01 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 10.71 |  |
| PASS | GET | `index.php?page=login` | 200 | 11.05 |  |
| PASS | POST | `index.php?page=login` | 200 | 74.17 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 10.31 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 9.04 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 10.39 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.05 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 10.38 |  |
| PASS | POST | `api/inventory.php` | 401 | 1.36 |  |
| PASS | POST | `api/inventory.php` | 422 | 0.63 |  |
| PASS | POST | `api/inventory.php` | 200 | 7.56 |  |
| PASS | POST | `api/inventory.php` | 200 | 7.64 |  |
| PASS | GET | `index.php?page=asset&id=501` | 200 | 24.35 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 25.53 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=6807841434` | 200 | 9.2 |  |
| PASS | GET | `index.php?page=traffic-control-print&id=1` | 200 | 8.3 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 24.63 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=6807841434` | 200 | 14.91 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 155.73 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=6807841434` | 200 | 8.98 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 25.34 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=6807841434` | 200 | 9.11 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 19.2 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-23` | 200 | 21.64 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 14.87 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 10.66 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 12.11 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 12.38 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-23&to=2026-10-23` | 200 | 10.77 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 11.41 |  |
| PASS | POST | `index.php?page=login` | 200 | 76.96 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 23.85 |  |
| PASS | GET | `index.php?page=settings` | 403 | 13.98 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 14.26 |  |

## Explicit limitations
- All nine application roles receive representative permission-boundary requests, not a complete role-by-route/action Cartesian matrix.
- Inventory writes use a synthetic fixture payload; no WMI/CIM collection or real device was contacted.
- Traffic, inventory, food-order, food-group, ticket, and CD/DVD flows are tested selectively; this is not every write handler or complete module workflow.
- LDAP/AD, Access/ODBC, printers, physical hardware, and external providers are not exercised; selected synthetic cron/queue paths are covered separately, not every schedule and failure mode.
- No production endpoint or operational account was used.
