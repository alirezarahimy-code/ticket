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
- Elapsed: 5.964 s; median latency: 16.42 ms; max latency: 1101.86 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 16.87 |  |
| PASS | POST | `index.php?page=login` | 200 | 130.27 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 55.28 |  |
| PASS | GET | `index.php?page=profile` | 200 | 20.42 |  |
| PASS | GET | `index.php?page=queue` | 200 | 100.8 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 24.8 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 42.33 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 22.23 |  |
| PASS | GET | `index.php?page=settings` | 200 | 60.67 |  |
| PASS | GET | `index.php?page=reports` | 200 | 69.48 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 25.9 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 69.53 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 44.54 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 20.44 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 25.09 |  |
| PASS | GET | `index.php?page=assets` | 200 | 32.63 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 37.15 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 22.35 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 27.97 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 21.96 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 23.41 |  |
| PASS | GET | `index.php?page=governance` | 200 | 135.31 |  |
| PASS | GET | `index.php?page=organization` | 200 | 22.31 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 20.88 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 20.52 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 23.09 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 43.58 |  |
| PASS | GET | `index.php?page=backup` | 200 | 21.65 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 36.18 |  |
| PASS | GET | `index.php?page=services` | 200 | 23.28 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 32.08 |  |
| PASS | GET | `index.php?page=logs` | 200 | 37.59 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 27.11 |  |
| PASS | GET | `index.php?page=help` | 200 | 22.55 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 19.69 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 23.6 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 27.13 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 20.61 |  |
| PASS | GET | `index.php?page=audit` | 200 | 36.54 |  |
| PASS | GET | `index.php?action=export_report` | 200 | 1101.86 |  |
| PASS | GET | `index.php?action=export_assets` | 200 | 143.4 |  |
| PASS | GET | `index.php?action=export_activity_logs` | 200 | 14.52 |  |
| PASS | GET | `index.php?action=export_cd_dvd` | 200 | 12.99 |  |
| PASS | GET | `index.php?action=export_traffic_report&from_date=1405-08-01&to_date=1405-08-02&all_destinations=1` | 200 | 12.92 |  |
| PASS | GET | `index.php?action=download_backup&file=missing-ci-backup.sql` | 404 | 11.37 |  |
| PASS | POST | `index.php?action=export_view_xlsx` | 419 | 13.08 |  |
| PASS | POST | `index.php?action=export_view_xlsx` | 200 | 11.55 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 200 | 11.5 |  |
| PASS | POST | `index.php?layout_design_api=1` | 422 | 11.55 |  |
| PASS | GET | `cron/food_ticket_worker.php` | 403 | 1.21 |  |
| PASS | GET | `cron/domain_scan_worker.php` | 403 | 0.82 |  |
| PASS | GET | `cron/ldap_sync.php` | 404 | 0.99 |  |
| PASS | GET | `cron/prune_activity_logs.php` | 404 | 1.05 |  |
| PASS | GET | `cron/sla_maintenance.php` | 404 | 5.76 |  |
| PASS | GET | `cron/ticket_auto_close.php` | 404 | 5.65 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 14.89 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 12.01 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 20.36 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 14.43 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 14.62 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 16.8 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 16.12 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 17.87 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 13.89 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=template` | 200 | 12.34 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees-sample` | 200 | 13.3 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 13.79 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-08` | 200 | 15.94 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 14.4 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-24&meal_type=lunch` | 200 | 13.9 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 13.97 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 14.39 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-24&to=2026-10-24` | 200 | 13.86 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders-export&date=2026-10-24&meal_type=lunch` | 200 | 15.45 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/export&from=2026-10-24&to=2026-10-24&mode=food&meal_type=lunch` | 200 | 16.43 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 15.06 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.61 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 13.73 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-24` | 200 | 16.33 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 12.93 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absent&from=2026-10-24&to=2026-10-24` | 200 | 15.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-requests-report&from=2026-10-24&to=2026-10-24&deputy_id=all` | 200 | 13.38 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-manual-attendance&date=2026-10-24` | 200 | 16.01 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders&date=2026-10-24` | 200 | 13.7 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/group-orders&date=2026-10-24&group_id=all` | 200 | 16.52 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/bulk-snapshot&date=2026-10-24&meal_type=lunch` | 200 | 20.63 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/emergency/people` | 200 | 13.14 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.05 |  |
| PASS | POST | `index.php?page=chatbot-api` | 400 | 17.46 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 19.27 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 16.77 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 13.19 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 20.05 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 14.52 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.65 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.19 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 200 | 11.49 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 38.64 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.19 |  |
| PASS | POST | `index.php?page=login` | 200 | 122.9 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 20.8 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 13.0 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.46 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.27 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.79 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.11 |  |
| PASS | POST | `index.php?page=login` | 200 | 97.59 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 19.76 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 17.68 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 14.38 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 13.01 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 15.35 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.53 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.06 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.01 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 35.46 |  |
| PASS | GET | `index.php?page=login` | 200 | 19.73 |  |
| PASS | POST | `index.php?page=login` | 200 | 125.43 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 19.23 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 17.89 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 403 | 12.93 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 12.66 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 403 | 13.07 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 13.07 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.03 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.23 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.52 |  |
| PASS | GET | `index.php?page=login` | 200 | 17.02 |  |
| PASS | POST | `index.php?page=login` | 200 | 94.59 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 20.78 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.98 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.36 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.11 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 34.21 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.5 |  |
| PASS | POST | `index.php?page=login` | 200 | 126.7 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 22.14 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 13.62 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.95 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.16 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 41.42 |  |
| PASS | GET | `index.php?page=login` | 200 | 18.46 |  |
| PASS | POST | `index.php?page=login` | 200 | 95.91 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 21.34 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 12.95 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.54 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.35 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 34.22 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.42 |  |
| PASS | POST | `index.php?page=login` | 200 | 90.88 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 15.55 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 13.66 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 13.95 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.69 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 15.17 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.69 |  |
| PASS | POST | `index.php?page=login` | 200 | 89.47 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 14.87 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 12.95 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 14.52 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 11.13 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 14.72 |  |
| PASS | POST | `api/inventory.php` | 401 | 1.71 |  |
| PASS | POST | `api/inventory.php` | 422 | 0.81 |  |
| PASS | POST | `api/inventory.php` | 200 | 11.06 |  |
| PASS | POST | `api/inventory.php` | 200 | 11.28 |  |
| PASS | GET | `index.php?page=asset&id=501` | 200 | 34.88 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 35.56 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=7973428259` | 200 | 12.79 |  |
| PASS | GET | `index.php?page=traffic-control-print&id=1` | 200 | 11.71 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 36.1 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=7973428259` | 200 | 12.57 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 36.76 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=7973428259` | 200 | 12.47 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 35.17 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=7973428259` | 200 | 12.98 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 28.75 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-24` | 200 | 18.52 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 20.26 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 14.53 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 18.11 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 17.82 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-24&to=2026-10-24` | 200 | 15.18 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 16.14 |  |
| PASS | POST | `index.php?page=login` | 200 | 94.43 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 36.36 |  |
| PASS | GET | `index.php?page=settings` | 403 | 21.37 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 21.4 |  |

## Explicit limitations
- All nine application roles receive representative permission-boundary requests, not a complete role-by-route/action Cartesian matrix.
- Inventory writes use a synthetic fixture payload; no WMI/CIM collection or real device was contacted.
- Traffic, inventory, food-order, food-group, ticket, and CD/DVD flows are tested selectively; this is not every write handler or complete module workflow.
- LDAP/AD, Access/ODBC, printers, physical hardware, and external providers are not exercised; selected synthetic cron/queue paths are covered separately, not every schedule and failure mode.
- No production endpoint or operational account was used.
