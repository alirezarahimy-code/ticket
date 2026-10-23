# Full-system route/API smoke (isolated synthetic data)

- Scope: authenticated app-wide page/API/selected role and write-lifecycle smoke on disposable synthetic MariaDB only
- Account: `loadtest-001` (synthetic)
- Pages: 37; API reads: 64; requester permission checks: 51; total checks: 153
- HTTP errors: 20 (unexpected: 0; expected authorization rejections: 20); JSON error payloads: 7; PHP runtime log lines: 0; DB/SQL log lines: 0
- Passed: 152; failed: 1
- Food-order create/cancel/read-back: passed; food-ticket group create/status/delete: passed
- Inventory agent create/update/read-back: passed; traffic create/update/exit/delete: not passed
- Expected access rejections: 20
- Elapsed: 2.737 s; median latency: 11.43 ms; max latency: 108.53 ms

| Result | Method | Route | HTTP | Latency (ms) | Diagnostic |
|---|---|---|---:|---:|---|
| PASS | GET | `index.php?page=login` | 200 | 9.85 |  |
| PASS | POST | `index.php?page=login` | 200 | 81.27 |  |
| PASS | GET | `index.php?page=dashboard` | 200 | 27.37 |  |
| PASS | GET | `index.php?page=profile` | 200 | 11.92 |  |
| PASS | GET | `index.php?page=queue` | 200 | 52.43 |  |
| PASS | GET | `index.php?page=ticket&id=1` | 200 | 13.87 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 21.52 |  |
| PASS | GET | `index.php?page=local-accounts` | 200 | 12.72 |  |
| PASS | GET | `index.php?page=settings` | 200 | 44.3 |  |
| PASS | GET | `index.php?page=reports` | 200 | 34.23 |  |
| PASS | GET | `index.php?page=analytics` | 200 | 14.21 |  |
| PASS | GET | `index.php?page=supervisor` | 200 | 33.82 |  |
| PASS | GET | `index.php?page=inventory` | 200 | 31.56 |  |
| PASS | GET | `index.php?page=inventory-diagnostics` | 200 | 11.36 |  |
| PASS | GET | `index.php?page=domain-scan` | 200 | 15.53 |  |
| PASS | GET | `index.php?page=assets` | 200 | 18.33 |  |
| PASS | GET | `index.php?page=asset&id=1` | 200 | 22.6 |  |
| PASS | GET | `index.php?page=asset-history&id=1` | 200 | 11.71 |  |
| PASS | GET | `index.php?page=cd-dvd` | 200 | 17.66 |  |
| PASS | GET | `index.php?page=cd-dvd-history` | 200 | 12.85 |  |
| PASS | GET | `index.php?page=traffic-control` | 200 | 12.26 |  |
| PASS | GET | `index.php?page=governance` | 200 | 63.64 |  |
| PASS | GET | `index.php?page=organization` | 200 | 16.21 |  |
| PASS | GET | `index.php?page=holidays` | 200 | 11.88 |  |
| PASS | GET | `index.php?page=knowledge` | 200 | 11.85 |  |
| PASS | GET | `index.php?page=notifications` | 200 | 11.91 |  |
| PASS | GET | `index.php?page=search&q=synthetic` | 200 | 24.27 |  |
| PASS | GET | `index.php?page=backup` | 200 | 11.78 |  |
| PASS | GET | `index.php?page=chatbot-admin` | 200 | 21.74 |  |
| PASS | GET | `index.php?page=services` | 200 | 15.16 |  |
| PASS | GET | `index.php?page=system-health` | 200 | 14.9 |  |
| PASS | GET | `index.php?page=logs` | 200 | 23.86 |  |
| PASS | GET | `index.php?page=activity-log` | 200 | 14.3 |  |
| PASS | GET | `index.php?page=help` | 200 | 12.05 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 13.19 |  |
| PASS | GET | `index.php?page=food-order` | 200 | 13.65 |  |
| PASS | GET | `index.php?page=food-personnel-link` | 200 | 18.02 |  |
| PASS | GET | `index.php?page=change-password` | 200 | 11.83 |  |
| PASS | GET | `index.php?page=audit` | 200 | 21.23 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=health` | 200 | 9.55 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=session` | 200 | 7.85 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 12.04 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=monitoring` | 200 | 9.5 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 9.16 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cards` | 200 | 18.43 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-cap` | 200 | 10.83 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 11.82 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders` | 200 | 9.89 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/status` | 200 | 9.27 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/month&month=1405-08` | 200 | 10.45 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 9.45 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/orders&date=2026-10-23` | 200 | 9.15 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/cancel-requests` | 200 | 8.98 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/groups` | 200 | 9.4 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/statistics&from=2026-10-23&to=2026-10-23` | 200 | 9.74 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 9.67 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.29 |  |
| PASS | GET | `index.php?page=food-order&food_api=search-users&q=loadtest` | 200 | 8.74 |  |
| PASS | GET | `index.php?page=food-order&food_api=day-visit&date=2026-10-23` | 200 | 10.22 |  |
| PASS | GET | `index.php?page=food-order&food_api=guest-requests` | 403 | 8.15 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absent&from=2026-10-23&to=2026-10-23` | 200 | 9.59 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=guest-requests-report&from=2026-10-23&to=2026-10-23&deputy_id=all` | 200 | 8.13 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-manual-attendance&date=2026-10-23` | 200 | 10.38 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=orders&date=2026-10-23` | 200 | 8.71 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/group-orders&date=2026-10-23&group_id=all` | 200 | 10.57 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/bulk-snapshot&date=2026-10-23&meal_type=lunch` | 200 | 12.88 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/emergency/people` | 200 | 8.26 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.88 |  |
| PASS | POST | `index.php?page=chatbot-api` | 400 | 10.17 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 11.76 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 10.69 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 8.59 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 200 | 11.44 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 9.31 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.11 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.3 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 19.38 |  |
| PASS | GET | `index.php?page=login` | 200 | 10.18 |  |
| PASS | POST | `index.php?page=login` | 200 | 85.48 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 16.59 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 9.23 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.4 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 18.95 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.38 |  |
| PASS | POST | `index.php?page=login` | 200 | 66.54 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 12.4 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 11.48 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 200 | 8.63 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 8.17 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 200 | 8.91 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.5 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.69 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 17.71 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.76 |  |
| PASS | POST | `index.php?page=login` | 200 | 78.23 |  |
| PASS | GET | `index.php?page=food-ticket` | 200 | 11.66 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-groups` | 200 | 11.57 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=employees` | 403 | 8.0 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=config` | 403 | 8.68 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=food-menu/catalog` | 403 | 7.87 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 7.57 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.64 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 18.65 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.26 |  |
| PASS | POST | `index.php?page=login` | 200 | 64.85 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 11.19 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.27 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.71 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 17.35 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.71 |  |
| PASS | POST | `index.php?page=login` | 200 | 78.61 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 11.74 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.16 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.13 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 18.63 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.55 |  |
| PASS | POST | `index.php?page=login` | 200 | 108.53 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 11.92 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 92.99 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.82 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 18.15 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.24 |  |
| PASS | POST | `index.php?page=login` | 200 | 63.03 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 9.3 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 403 | 8.0 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 8.57 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 8.76 |  |
| PASS | GET | `index.php?page=login` | 200 | 8.99 |  |
| PASS | POST | `index.php?page=login` | 200 | 65.61 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 9.38 |  |
| PASS | GET | `index.php?page=traffic-control&traffic_api=lookup&code=000` | 200 | 8.76 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders` | 200 | 9.11 |  |
| PASS | GET | `index.php?page=new-ticket` | 403 | 9.2 |  |
| PASS | POST | `api/inventory.php` | 401 | 1.35 |  |
| PASS | POST | `api/inventory.php` | 422 | 0.66 |  |
| PASS | POST | `api/inventory.php` | 200 | 6.95 |  |
| PASS | POST | `api/inventory.php` | 200 | 7.65 |  |
| PASS | GET | `index.php?page=asset&id=501` | 200 | 21.33 |  |
| PASS | POST | `index.php?page=traffic-control` | 200 | 19.94 |  |
| FAIL | GET | `index.php?page=traffic-control&traffic_api=lookup&code=8262438795` | 200 | 8.61 | traffic lookup did not return the synthetic row created by the handler; create redirect=http://127.0.0.1:8081/index.php?page=traffic-control&tab=new; response=Element("a"); lk.href = j.link.url; lk.textContent = j.link.label; lk.style.display = "inline-block"; lk.style.marginTop = "6px"; pending.appendChild(lk); } else if (!j.matched && j.can_ticket) { var a = document.createElement("a"); a.href = "index.php?page=new-ticket"; a.textContent = "ثبت درخواست جدید"; a.style.display = "inline-block"; a.style.marginTop = "6px"; pending.appendChild(a); } } }).catch(function(){ pending.textContent = "ارتباط با سرور برقرار نشد. دوباره تلاش کنید."; }); }); })(); |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 17.54 |  |
| PASS | GET | `index.php?page=food-ticket&food_api=absence-status&group_id=1&date=2026-10-23` | 200 | 11.79 |  |
| PASS | POST | `index.php?page=food-ticket&food_api=food-groups` | 200 | 13.21 |  |
| PASS | GET | `index.php?page=food-order&food_api=month&month=1405-08` | 200 | 9.87 |  |
| PASS | POST | `index.php?page=food-order&food_api=order` | 200 | 11.43 |  |
| PASS | POST | `index.php?page=food-order&food_api=cancel` | 200 | 11.77 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=2026-10-23&to=2026-10-23` | 200 | 9.95 |  |
| PASS | GET | `index.php?page=food-order&food_api=my-orders&from=<synthetic-date>&to=<synthetic-date>` | 200 | 0 |  |
| PASS | GET | `index.php?page=login` | 200 | 9.8 |  |
| PASS | POST | `index.php?page=login` | 200 | 66.3 |  |
| PASS | GET | `index.php?page=new-ticket` | 200 | 18.05 |  |
| PASS | GET | `index.php?page=settings` | 403 | 11.55 |  |
| PASS | GET | `index.php?page=food-ticket` | 403 | 11.5 |  |

## Explicit limitations
- All nine application roles receive representative permission-boundary requests, not a complete role-by-route/action Cartesian matrix.
- Inventory writes use a synthetic fixture payload; no WMI/CIM collection or real device was contacted.
- Traffic, inventory, food-order, food-group, ticket, and CD/DVD flows are tested selectively; this is not every write handler or complete module workflow.
- LDAP/AD, Access/ODBC, printers, physical hardware, external providers, cron/queue workers, and production/staging capacity are not exercised.
- No production endpoint or operational account was used.
