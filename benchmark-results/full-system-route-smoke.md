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
- Elapsed: 4.29 s; median latency: 11.88 ms; max latency: 739.05 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 10.82 |  |
| PASS | POST | `index.php?page=login` | 200 | 90.89 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 32.44 |  |
| PASS | GET | `index.php?page=profile` | 200 | 14.09 |  |
| PASS | GET | `index.php?page=queue` | 200 | 80.48 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 16.73 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 27.44 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 15.46 |  |
| PASS | GET | `index.php?page=settings` | 200 | 44.14 |  |
| PASS | GET | `index.php?page=reports` | 200 | 51.05 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 22.13 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 46.27 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 32.26 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 14.77 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 19.15 |  |
| PASS | GET | `index.php?page=assets` | 200 | 20.75 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 28.48 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 17.84 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 20.14 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 14.62 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 16.56 |  |
| PASS | GET | `index.php?page=governance` | 200 | 94.23 |  |
| PASS | GET | `index.php?page=organization` | 200 | 17.52 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 14.87 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 15.63 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 15.82 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 29.67 |  |
| PASS | GET | `index.php?page=backup` | 200 | 13.67 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 27.59 |  |
| PASS | GET | `index.php?page=services` | 200 | 15.25 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 15.09 |  |
| PASS | GET | `index.php?page=logs` | 200 | 22.55 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 14.98 |  |
| PASS | GET | `index.php?page=help` | 200 | 14.86 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 14.27 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 18.95 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 20.26 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 15.3 |  |
| PASS | GET | `index.php?page=audit` | 200 | 24.72 |  |
| PASS | GET | `index.php?action=export_report` | 200 | 739.05 |  |
| PASS | GET | `index.php?action=export_assets` | 200 | 121.88 |  |
| PASS | GET | `index.php?action=export_activity_logs` | 200 | 12.8 |  |
| PASS | GET | `index.php?action=export_cd_dvd` | 200 | 19.2 |  |
| PASS | GET | `index.php?action=export_traffic_report&from_date=1405-08-01&to_date=1405-08-02&all_destinations=1` | 200 | 9.08 |  |
| PASS | GET | `index.php?action=download_backup&file=missing-ci-backup.sql` | 404 | 8.64 |  |
| PASS | POST | `index.php?action=export_view_xlsx` | 419 | 9.43 |  |
| PASS | POST | `index.php?action=export_view_xlsx` | 200 | 9.09 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 200 | 8.85 |  |
| PASS | POST | `index.php?layout_design_api=1` | 422 | 9.35 |  |
| PASS | GET | `cron/food_ticket_worker.php` | 403 | 1.07 |  |
| PASS | GET | `cron/domain_scan_worker.php` | 403 | 0.74 |  |
| PASS | GET | `cron/ldap_sync.php` | 404 | 0.81 |  |
| PASS | GET | `cron/prune_activity_logs.php` | 404 | 0.91 |  |
| PASS | GET | `cron/sla_maintenance.php` | 404 | 4.01 |  |
| PASS | GET | `cron/ticket_auto_close.php` | 404 | 4.27 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 12.03 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 7.97 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 15.93 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 9.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 9.75 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 11.74 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 11.96 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 14.85 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 10.7 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=template` | 200 | 10.58 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees-sample` | 200 | 9.39 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 9.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-08` | 200 | 11.35 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 9.71 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-23&meal_type=lunch` | 200 | 24.31 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 11.7 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 9.84 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-23&to=2026-10-23` | 200 | 11.41 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders-export&date=2026-10-23&meal_type=lunch` | 200 | 14.38 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/export&from=2026-10-23&to=2026-10-23&mode=food&meal_type=lunch` | 200 | 11.64 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 10.96 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.95 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 9.44 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-23` | 200 | 12.66 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 9.11 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absent&from=2026-10-23&to=2026-10-23` | 200 | 11.15 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-requests-report&from=2026-10-23&to=2026-10-23&deputy_id=all` | 200 | 8.58 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-manual-attendance&date=2026-10-23` | 200 | 11.18 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders&date=2026-10-23` | 200 | 9.68 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/group-orders&date=2026-10-23&group_id=all` | 200 | 12.76 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/bulk-snapshot&date=2026-10-23&meal_type=lunch` | 200 | 13.92 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/emergency/people` | 200 | 10.74 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.48 |  |
| PASS | POST | `index.php?page=chatbot-api` | 400 | 12.14 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 13.79 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 23.76 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 9.35 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 13.92 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 9.66 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.46 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 11.51 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 200 | 8.95 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 29.68 |  |
| PASS | GET | `index.php?page=login` | 200 | 13.39 |  |
| PASS | POST | `index.php?page=login` | 200 | 89.5 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 12.93 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.6 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.19 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.1 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 23.15 |  |
| PASS | GET | `index.php?page=login` | 200 | 11.43 |  |
| PASS | POST | `index.php?page=login` | 200 | 75.37 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 13.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 11.88 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 10.69 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 9.58 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 10.11 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.89 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.68 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.52 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 21.93 |  |
| PASS | GET | `index.php?page=login` | 200 | 13.54 |  |
| PASS | POST | `index.php?page=login` | 200 | 87.6 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 13.67 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 11.55 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 403 | 8.42 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 8.16 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 403 | 8.39 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.4 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 11.17 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 7.62 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 23.95 |  |
| PASS | GET | `index.php?page=login` | 200 | 13.07 |  |
| PASS | POST | `index.php?page=login` | 200 | 95.09 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 13.38 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 10.67 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 10.21 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 7.99 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 23.28 |  |
| PASS | GET | `index.php?page=login` | 200 | 10.64 |  |
| PASS | POST | `index.php?page=login` | 200 | 88.46 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 15.06 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.35 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.5 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 7.06 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 24.71 |  |
| PASS | GET | `index.php?page=login` | 200 | 11.19 |  |
| PASS | POST | `index.php?page=login` | 200 | 73.39 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 12.76 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 8.93 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 10.9 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 7.79 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 23.84 |  |
| PASS | GET | `index.php?page=login` | 200 | 10.87 |  |
| PASS | POST | `index.php?page=login` | 200 | 73.41 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 10.95 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 9.47 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.22 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 8.46 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 9.91 |  |
| PASS | GET | `index.php?page=login` | 200 | 11.74 |  |
| PASS | POST | `index.php?page=login` | 200 | 68.92 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 9.68 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 10.01 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.94 |  |
| PASS | GET | `index.php?layout_design_api=1&layout_page=dashboard` | 403 | 7.93 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 10.38 |  |
| PASS | POST | `api/inventory.php` | 401 | 1.53 |  |
| PASS | POST | `api/inventory.php` | 422 | 0.79 |  |
| PASS | POST | `api/inventory.php` | 200 | 9.82 |  |
| PASS | POST | `api/inventory.php` | 200 | 9.25 |  |
| PASS | GET | `index.php?page=asset&id=501` | 200 | 26.03 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 25.38 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=3563435987` | 200 | 9.97 |  |
| PASS | GET | `index.php?page=traffic-control-print&id=1` | 200 | 9.63 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 24.76 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=3563435987` | 200 | 8.85 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 24.32 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=3563435987` | 200 | 10.1 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 24.84 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=3563435987` | 200 | 9.4 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 18.92 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-23` | 200 | 12.29 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 15.7 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 10.6 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 14.55 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 14.44 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-23&to=2026-10-23` | 200 | 10.45 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 13.0 |  |
| PASS | POST | `index.php?page=login` | 200 | 72.26 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 23.11 |  |
| PASS | GET | `index.php?page=settings` | 403 | 13.03 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 15.37 |  |

## Explicit limitations
- All nine application roles receive representative permission-boundary requests, not a complete role-by-route/action Cartesian matrix.
- Inventory writes use a synthetic fixture payload; no WMI/CIM collection or real device was contacted.
- Traffic, inventory, food-order, food-group, ticket, and CD/DVD flows are tested selectively; this is not every write handler or complete module workflow.
- LDAP/AD, Access/ODBC, printers, physical hardware, and external providers are not exercised; selected synthetic cron/queue paths are covered separately, not every schedule and failure mode.
- No production endpoint or operational account was used.
