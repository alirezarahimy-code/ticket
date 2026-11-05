# نقشهٔ کد سامانه — فهرست آدرس‌دهی

> این فایل **تولید می‌شود**. دستی ویرایشش نکنید.
>
> بازتولید: `php tools/code_index.php > docs/code-index.json` سپس `python tools/render_code_map.py`

## وضعیت بازبینی

| سنجه | مقدار |
|---|---|
| فایل‌های کد تولیدی | 178 |
| خطوط | 80,141 |
| آدرس‌ها (بلوک) | 2,819 |
| بازبینی‌شده | 0 آدرس (0.0%) — 0 خط (0.0%) |
| باقی‌مانده | 2,819 آدرس |

## چطور از این فهرست استفاده کنم؟

هر آدرس شکل `ناحیه-شمارهٔ‌فایل.شمارهٔ‌بلوک` دارد؛ مثلاً `CORE-008.14`.

- **`CORE`** ناحیه است (جدول زیر).
- **`008`** شمارهٔ فایل در آن ناحیه.
- **`14`** شمارهٔ بلوک داخل فایل، به ترتیب خط.

برای رفتن به یک آدرس، در همین فایل `Ctrl+F` بزنید و شناسه را بچسبانید؛ مسیر فایل و شمارهٔ خط کنارش نوشته شده.

**لنگر پایدار، شمارهٔ خط کمکی:** ستون «لنگر» نام تابع یا عنوان بلوک است و با جابه‌جا شدن کد عوض نمی‌شود. شمارهٔ خط فقط برای پرش سریع است و با هر بازتولید تازه می‌شود.

## ناحیه‌ها

| ناحیه | معنی | فایل | بلوک | خط |
|---|---|---|---|---|
| `PAGE` | صفحات (ریشه) | 74 | 1,355 | 45,027 |
| `ASSET` | دارایی‌های ایستا (assets/) | 30 | 676 | 13,879 |
| `WEB` | پنل وب غذا (food-ticket-web/) | 28 | 378 | 9,002 |
| `ACT` | کنش‌ها (actions/) | 16 | 100 | 3,779 |
| `CORE` | موتور مشترک (lib/) | 15 | 289 | 6,808 |
| `CRON` | زمان‌بندها (cron/) | 14 | 14 | 1,440 |
| `API` | رابط برنامه (api/) | 1 | 7 | 206 |

## نشانه‌های ریسک

امتیاز ریسک فقط **ترتیب بازبینی** را تعیین می‌کند؛ قضاوت نهایی نیست. یک بلوک پرامتیاز می‌تواند کاملاً سالم باشد و یک بلوک کم‌امتیاز باگ حیاتی داشته باشد.

| نشانه | معنی | بلوک |
|---|---|---|
| `write_no_tx` | نوشتن بدون تراکنش | 235 |
| `sql_concat` | الحاق در SQL | 160 |
| `error_suppression` | سرکوب @ | 159 |
| `raw_input` | ورودی خام | 150 |
| `silent_catch` | catch بی‌صدا | 108 |
| `long` | >۱۲۰ خط | 90 |
| `dangerous_call` | فراخوانی خطرناک | 75 |
| `very_long` | >۳۰۰ خط | 11 |
| `unescaped_echo` | چاپ بدون فرار | 6 |

## ترتیب بازبینی — ۴۰ آدرس پرخطر

| # | آدرس | مسیر:خط | لنگر | ریسک | نشانه‌ها | وضعیت |
|---|---|---|---|---|---|---|
| 1 | `PAGE-053.127` | `food-ticket.php:4735` | food_ticket_api_upload_access_file | 15 | الحاق در SQL، ورودی خام، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 2 | `CRON-006.01` | `cron/food_ticket_worker.php:1` | بلوک رویه‌ای 1-313 | 14 | catch بی‌صدا، سرکوب @، فراخوانی خطرناک، >۳۰۰ خط | ⬜ در نوبت |
| 3 | `PAGE-049.29` | `food-ticket-runtime.php:1193` | food_ticket_events_ensure_source_delet | 14 | catch بی‌صدا، الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 4 | `PAGE-053.82` | `food-ticket.php:2377` | food_ticket_cleanup_source_rows | 14 | catch بی‌صدا، الحاق در SQL، سرکوب @، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 5 | `PAGE-053.128` | `food-ticket.php:4805` | food_ticket_api_handle | 14 | الحاق در SQL، ورودی خام، سرکوب @، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 6 | `CRON-001.01` | `cron/check_logging_health.php:1` | بلوک رویه‌ای 1-240 | 13 | catch بی‌صدا، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 7 | `PAGE-049.22` | `food-ticket-runtime.php:815` | food_ticket_send_windows_graphic | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 8 | `PAGE-053.37` | `food-ticket.php:793` | food_ticket_access_rows_powershell | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 9 | `PAGE-053.39` | `food-ticket.php:1011` | food_ticket_access_delete_powershell_e | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 10 | `PAGE-053.83` | `food-ticket.php:2581` | food_ticket_retry_failed | 13 | catch بی‌صدا، الحاق در SQL، سرکوب @، نوشتن بدون تراکنش | ⬜ در نوبت |
| 11 | `PAGE-035.27` | `chatbot.php:700` | chatbot_api_handle | 12 | ورودی خام، چاپ بدون فرار، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 12 | `CRON-009.01` | `cron/rebuild_logging_issues.php:1` | بلوک رویه‌ای 1-53 | 12 | الحاق در SQL، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 13 | `PAGE-049.18` | `food-ticket-runtime.php:539` | food_ticket_process_print_queue | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 14 | `WEB-003.01` | `food-ticket-web/assets/food-capitalio-dashboard-4179.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 15 | `WEB-005.01` | `food-ticket-web/assets/food-capitalio-dashboard-r37.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 16 | `WEB-005.02` | `food-ticket-web/assets/food-capitalio-dashboard-r37.js:159` | fetchData | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 17 | `WEB-007.01` | `food-ticket-web/assets/food-capitalio-dashboard-r38.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 18 | `WEB-007.02` | `food-ticket-web/assets/food-capitalio-dashboard-r38.js:159` | fetchData | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 19 | `WEB-009.01` | `food-ticket-web/assets/food-capitalio-dashboard-r39.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 20 | `WEB-009.02` | `food-ticket-web/assets/food-capitalio-dashboard-r39.js:159` | fetchData | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 21 | `WEB-012.01` | `food-ticket-web/assets/food-capitalio-dashboard-r41.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 22 | `WEB-012.02` | `food-ticket-web/assets/food-capitalio-dashboard-r41.js:159` | fetchData | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 23 | `WEB-014.01` | `food-ticket-web/assets/food-capitalio-dashboard-r42.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 24 | `WEB-014.02` | `food-ticket-web/assets/food-capitalio-dashboard-r42.js:159` | fetchData | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 25 | `PAGE-058.90` | `index.php:3197` | مسیر: settings | 11 | الحاق در SQL، ورودی خام، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 26 | `CORE-008.43` | `lib/logging-core.php:1025` | logging_emergency_failure | 11 | catch بی‌صدا، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 27 | `PAGE-045.03` | `food-ticket-groups.php:62` | food_ticket_groups_ensure | 10 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 28 | `PAGE-045.52` | `food-ticket-groups.php:1737` | food_ticket_group_try_unknown | 10 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش | ⬜ در نوبت |
| 29 | `PAGE-059.13` | `install.php:152` | مسیر: test_ldap | 10 | ورودی خام، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 30 | `CORE-015.10` | `lib/tickets-sla.php:263` | ticket_number_autofill | 10 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش | ⬜ در نوبت |
| 31 | `PAGE-030.03` | `asset-profile.php:26` | asset_profile_ensure_schema | 9 | catch بی‌صدا، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 32 | `PAGE-045.61` | `food-ticket-groups.php:2320` | food_ticket_api_manual_attendance_save | 9 | catch بی‌صدا، الحاق در SQL | ⬜ در نوبت |
| 33 | `PAGE-050.06` | `food-ticket-templates.php:112` | food_ticket_tpl_ensure | 9 | catch بی‌صدا، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 34 | `PAGE-050.62` | `food-ticket-templates.php:2225` | food_ticket_tpl_handle_logo_upload | 9 | ورودی خام، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 35 | `PAGE-053.73` | `food-ticket.php:2061` | food_ticket_delete_source | 9 | الحاق در SQL، سرکوب @، نوشتن بدون تراکنش | ⬜ در نوبت |
| 36 | `PAGE-055.29` | `governance.php:386` | مسیر: link_governance_ticket | 9 | الحاق در SQL، ورودی خام، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 37 | `PAGE-058.37` | `index.php:938` | مسیر: traffic-control|traffic_api | 9 | الحاق در SQL، ورودی خام، سرکوب @، >۱۲۰ خط | ⬜ در نوبت |
| 38 | `CORE-008.58` | `lib/logging-core.php:1338` | logging_db_circuit_success | 9 | catch بی‌صدا، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 39 | `CORE-012.03` | `lib/logging-replay.php:45` | logging_replay_process_directory | 9 | سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 40 | `PAGE-064.02` | `organization.php:4` | org_ensure_schema | 9 | catch بی‌صدا، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |

## فهرست کامل آدرس‌ها


### صفحات (ریشه) — `PAGE`


<details>
<summary><b>PAGE-001</b> — <code>admin-layout-design.php</code> (362 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-001.01` | 1–9 | ناحیه | `بلوک رویه‌ای 1-9` | 0 | ⬜ |
| `PAGE-001.02` | 10–16 | تابع | `admin_layout_design_is_admin` | 0 | ⬜ |
| `PAGE-001.03` | 18–28 | تابع | `admin_layout_design_page_key` | 0 | ⬜ |
| `PAGE-001.04` | 30–35 | تابع | `admin_layout_design_config` | 0 | ⬜ |
| `PAGE-001.05` | 37–80 | تابع | `admin_layout_design_validate_path` | 0 | ⬜ |
| `PAGE-001.06` | 82–95 | تابع | `admin_layout_design_css_length` | 0 | ⬜ |
| `PAGE-001.07` | 97–164 | تابع | `admin_layout_design_validate_styles` | 0 | ⬜ |
| `PAGE-001.08` | 166–190 | تابع | `admin_layout_design_validate_rules` | 0 | ⬜ |
| `PAGE-001.09` | 192–220 | تابع | `admin_layout_design_stored_rules` | 0 | ⬜ |
| `PAGE-001.10` | 222–240 | تابع | `admin_layout_design_selector` | 0 | ⬜ |
| `PAGE-001.11` | 242–281 | تابع | `admin_layout_design_css_for_page` | 0 | ⬜ |
| `PAGE-001.12` | 283–298 | تابع | `admin_layout_design_page_label` | 0 | ⬜ |
| `PAGE-001.13` | 300–361 | تابع | `admin_layout_design_api_handle` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-002</b> — <code>app/Controllers/DashboardController.php</code> (25 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-002.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-002.02` | 8–24 | کلاس | `DashboardController` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-003</b> — <code>app/Controllers/ReportController.php</code> (40 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-003.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-003.02` | 8–39 | کلاس | `ReportController` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-004</b> — <code>app/Controllers/SettingsController.php</code> (40 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-004.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-004.02` | 8–39 | کلاس | `SettingsController` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-005</b> — <code>app/Controllers/TicketController.php</code> (86 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-005.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-005.02` | 8–85 | کلاس | `TicketController` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-006</b> — <code>app/Controllers/UserController.php</code> (55 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-006.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-006.02` | 8–54 | کلاس | `UserController` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-007</b> — <code>app/Core/Cache.php</code> (100 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-007.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-007.02` | 6–99 | کلاس | `Cache` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-008</b> — <code>app/Core/Controller.php</code> (40 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-008.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-008.02` | 6–39 | کلاس | `Controller` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-009</b> — <code>app/Core/Model.php</code> (69 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-009.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-009.02` | 6–68 | کلاس | `Model` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-010</b> — <code>app/Core/Router.php</code> (64 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-010.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-010.02` | 6–63 | کلاس | `Router` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-011</b> — <code>app/Models/ActivityLog.php</code> (66 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-011.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-011.02` | 8–65 | کلاس | `ActivityLog` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-012</b> — <code>app/Models/Asset.php</code> (95 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-012.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-012.02` | 8–94 | کلاس | `Asset` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-013</b> — <code>app/Models/Notification.php</code> (59 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-013.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-013.02` | 8–58 | کلاس | `Notification` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-014</b> — <code>app/Models/Ticket.php</code> (97 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-014.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-014.02` | 8–96 | کلاس | `Ticket` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-015</b> — <code>app/Models/User.php</code> (86 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-015.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-015.02` | 8–85 | کلاس | `User` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-016</b> — <code>app/Services/AssetService.php</code> (101 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-016.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-016.02` | 8–100 | کلاس | `AssetService` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-017</b> — <code>app/Services/AuthService.php</code> (135 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-017.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-017.02` | 8–134 | کلاس | `AuthService` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-018</b> — <code>app/Services/ReportService.php</code> (127 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-018.01` | 1–10 | ناحیه | `بلوک رویه‌ای 1-10` | 0 | ⬜ |
| `PAGE-018.02` | 11–126 | کلاس | `ReportService` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-019</b> — <code>app/Services/TicketService.php</code> (144 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-019.01` | 1–9 | ناحیه | `بلوک رویه‌ای 1-9` | 0 | ⬜ |
| `PAGE-019.02` | 10–143 | کلاس | `TicketService` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-020</b> — <code>app/Services/UserService.php</code> (100 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-020.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-020.02` | 8–99 | کلاس | `UserService` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-021</b> — <code>app/Views/dashboard/index.php</code> (34 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-021.01` | 1–34 | ناحیه | `بلوک رویه‌ای 1-34` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-022</b> — <code>app/Views/layout/footer.php</code> (9 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-022.01` | 1–9 | ناحیه | `بلوک رویه‌ای 1-9` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-023</b> — <code>app/Views/layout/header.php</code> (57 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-023.01` | 1–57 | ناحیه | `بلوک رویه‌ای 1-57` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-024</b> — <code>app/Views/ticket/create.php</code> (47 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-024.01` | 1–47 | ناحیه | `بلوک رویه‌ای 1-47` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-025</b> — <code>app/Views/ticket/index.php</code> (40 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-025.01` | 1–40 | ناحیه | `بلوک رویه‌ای 1-40` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-026</b> — <code>app/Views/ticket/reply.php</code> (32 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-026.01` | 1–32 | ناحیه | `بلوک رویه‌ای 1-32` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-027</b> — <code>app/Views/ticket/show.php</code> (36 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-027.01` | 1–36 | ناحیه | `بلوک رویه‌ای 1-36` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-028</b> — <code>asset-inventory-page.php</code> (90 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-028.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-028.02` | 4–89 | تابع | `inventory_render_assets_content` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-029</b> — <code>asset-names.php</code> (199 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-029.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `PAGE-029.02` | 25–30 | تابع | `asset_hostname_style` | 0 | ⬜ |
| `PAGE-029.03` | 32–62 | تابع | `asset_name_core` | 0 | ⬜ |
| `PAGE-029.04` | 64–80 | تابع | `asset_display_name` | 0 | ⬜ |
| `PAGE-029.05` | 82–93 | تابع | `asset_is_auto_domain_tag` | 0 | ⬜ |
| `PAGE-029.06` | 95–108 | تابع | `asset_name_title` | 0 | ⬜ |
| `PAGE-029.07` | 110–115 | تابع | `asset_name_attr` | 0 | ⬜ |
| `PAGE-029.08` | 117–131 | تابع | `asset_tag_label` | 0 | ⬜ |
| `PAGE-029.09` | 132–134 | ناحیه | `بلوک رویه‌ای 132-134` | 0 | ⬜ |
| `PAGE-029.10` | 135–151 | تابع | `asset_identity_key` | 0 | ⬜ |
| `PAGE-029.11` | 153–185 | تابع | `asset_resolve_id` | 0 | ⬜ |
| `PAGE-029.12` | 187–197 | تابع | `asset_sync_identity_key` | 1 | ⬜ |
| `PAGE-029.13` | 198–199 | ناحیه | `بلوک رویه‌ای 198-199` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-030</b> — <code>asset-profile.php</code> (1,035 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-030.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-030.02` | 6–24 | تابع | `asset_profile_column_sql` | 0 | ⬜ |
| `PAGE-030.03` | 26–63 | تابع | `asset_profile_ensure_schema` | **9** | ⬜ |
| `PAGE-030.04` | 65–211 | تابع | `asset_profile_sections` | 1 | ⬜ |
| `PAGE-030.05` | 213–226 | تابع | `asset_profile_all_columns` | 0 | ⬜ |
| `PAGE-030.06` | 228–238 | تابع | `asset_profile_field` | 0 | ⬜ |
| `PAGE-030.07` | 240–258 | تابع | `asset_profile_export_columns` | 0 | ⬜ |
| `PAGE-030.08` | 260–273 | تابع | `asset_profile_export_value` | 0 | ⬜ |
| `PAGE-030.09` | 275–283 | تابع | `asset_profile_fetch` | 0 | ⬜ |
| `PAGE-030.10` | 285–343 | تابع | `asset_profile_fill_from_asset` | 0 | ⬜ |
| `PAGE-030.11` | 345–390 | تابع | `asset_profile_collection_status_summary` | 0 | ⬜ |
| `PAGE-030.12` | 392–418 | تابع | `asset_profile_map` | 5 | ⬜ |
| `PAGE-030.13` | 420–429 | تابع | `asset_profile_input_value` | 0 | ⬜ |
| `PAGE-030.14` | 431–480 | تابع | `asset_profile_render_fields` | 5 | ⬜ |
| `PAGE-030.15` | 482–490 | تابع | `asset_profile_render_sections` | 0 | ⬜ |
| `PAGE-030.16` | 492–544 | تابع | `asset_profile_save` | 6 | ⬜ |
| `PAGE-030.17` | 546–557 | تابع | `asset_profile_printer_port_is_physical` | 0 | ⬜ |
| `PAGE-030.18` | 559–590 | تابع | `asset_profile_is_virtual_printer` | 0 | ⬜ |
| `PAGE-030.19` | 592–602 | تابع | `asset_profile_printer_kind` | 0 | ⬜ |
| `PAGE-030.20` | 604–622 | تابع | `asset_profile_real_printers` | 0 | ⬜ |
| `PAGE-030.21` | 624–960 | تابع | `asset_profile_auto_values` | 5 | ⬜ |
| `PAGE-030.22` | 962–1034 | تابع | `asset_profile_apply_auto` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-031</b> — <code>backup.php</code> (277 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-031.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-031.02` | 4–8 | تابع | `backup_root_path` | 0 | ⬜ |
| `PAGE-031.03` | 10–28 | تابع | `backup_mysql_binary` | 0 | ⬜ |
| `PAGE-031.04` | 30–74 | تابع | `backup_run_process` | 4 | ⬜ |
| `PAGE-031.05` | 76–100 | تابع | `backup_uploads` | 0 | ⬜ |
| `PAGE-031.06` | 103–134 | تابع | `backup_system_files_zip` | 0 | ⬜ |
| `PAGE-031.07` | 136–170 | تابع | `create_backup_bundle` | 0 | ⬜ |
| `PAGE-031.08` | 172–188 | تابع | `backup_runs` | 0 | ⬜ |
| `PAGE-031.09` | 190–203 | تابع | `backup_directory_size` | 0 | ⬜ |
| `PAGE-031.10` | 205–212 | تابع | `backup_download_path` | 0 | ⬜ |
| `PAGE-031.11` | 214–255 | تابع | `restore_upload_archive` | 0 | ⬜ |
| `PAGE-031.12` | 257–276 | تابع | `restore_sql_dump` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-032</b> — <code>bootstrap.php</code> (67 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-032.01` | 1–67 | ناحیه | `بلوک رویه‌ای 1-67` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-033</b> — <code>cd-dvd.php</code> (893 خط، 44 آدرس، بازبینی‌شده 0/44)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-033.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-033.02` | 4–7 | تابع | `cd_dvd_media_bounds` | 0 | ⬜ |
| `PAGE-033.03` | 9–13 | تابع | `cd_dvd_normalize_serial` | 0 | ⬜ |
| `PAGE-033.04` | 15–18 | تابع | `cd_dvd_internal_number` | 0 | ⬜ |
| `PAGE-033.05` | 20–23 | تابع | `cd_dvd_serial_valid` | 0 | ⬜ |
| `PAGE-033.06` | 25–33 | تابع | `cd_dvd_serial_matches_media` | 0 | ⬜ |
| `PAGE-033.07` | 35–38 | تابع | `cd_dvd_is_primary_admin` | 0 | ⬜ |
| `PAGE-033.08` | 40–50 | تابع | `cd_dvd_inspection_user_ids` | 0 | ⬜ |
| `PAGE-033.09` | 52–56 | تابع | `cd_dvd_inspection_user_id` | 0 | ⬜ |
| `PAGE-033.10` | 58–61 | تابع | `cd_dvd_is_inspection_user` | 0 | ⬜ |
| `PAGE-033.11` | 63–69 | تابع | `cd_dvd_can_see_all` | 0 | ⬜ |
| `PAGE-033.12` | 71–83 | تابع | `cd_dvd_can_edit_records` | 0 | ⬜ |
| `PAGE-033.13` | 85–91 | تابع | `cd_dvd_can_export` | 0 | ⬜ |
| `PAGE-033.14` | 93–104 | تابع | `cd_dvd_can_view_record` | 0 | ⬜ |
| `PAGE-033.15` | 106–116 | تابع | `cd_dvd_audit_snapshot` | 0 | ⬜ |
| `PAGE-033.16` | 118–124 | تابع | `cd_dvd_can_register_in` | 0 | ⬜ |
| `PAGE-033.17` | 126–132 | تابع | `cd_dvd_can_submit_out` | 0 | ⬜ |
| `PAGE-033.18` | 134–149 | تابع | `cd_dvd_available` | 0 | ⬜ |
| `PAGE-033.19` | 151–160 | تابع | `cd_dvd_scope` | 5 | ⬜ |
| `PAGE-033.20` | 162–167 | تابع | `cd_dvd_can_manage_record` | 0 | ⬜ |
| `PAGE-033.21` | 169–172 | تابع | `cd_dvd_types` | 0 | ⬜ |
| `PAGE-033.22` | 174–177 | تابع | `cd_dvd_active_users` | 0 | ⬜ |
| `PAGE-033.23` | 179–189 | تابع | `cd_dvd_deputy_options` | 0 | ⬜ |
| `PAGE-033.24` | 191–200 | تابع | `cd_dvd_routing_department_id` | 0 | ⬜ |
| `PAGE-033.25` | 202–220 | تابع | `cd_dvd_next_serial` | 0 | ⬜ |
| `PAGE-033.26` | 222–234 | تابع | `cd_dvd_last_record` | 0 | ⬜ |
| `PAGE-033.27` | 236–241 | تابع | `cd_dvd_fetch_record` | 0 | ⬜ |
| `PAGE-033.28` | 243–257 | تابع | `cd_dvd_parse_date` | 0 | ⬜ |
| `PAGE-033.29` | 259–263 | تابع | `cd_dvd_default_date` | 0 | ⬜ |
| `PAGE-033.30` | 265–378 | تابع | `cd_dvd_validate_payload` | 0 | ⬜ |
| `PAGE-033.31` | 380–389 | تابع | `cd_dvd_acquire_write_lock` | 0 | ⬜ |
| `PAGE-033.32` | 391–399 | تابع | `cd_dvd_release_write_lock` | 4 | ⬜ |
| `PAGE-033.33` | 401–449 | تابع | `cd_dvd_create_record` | 7 | ⬜ |
| `PAGE-033.34` | 451–472 | تابع | `cd_dvd_update_record` | 2 | ⬜ |
| `PAGE-033.35` | 474–492 | تابع | `cd_dvd_delete_record` | 2 | ⬜ |
| `PAGE-033.36` | 494–529 | تابع | `cd_dvd_query_records` | 0 | ⬜ |
| `PAGE-033.37` | 531–536 | تابع | `cd_dvd_personal_incoming` | 0 | ⬜ |
| `PAGE-033.38` | 538–548 | تابع | `cd_dvd_latest_records` | 0 | ⬜ |
| `PAGE-033.39` | 550–569 | تابع | `cd_dvd_stats` | 5 | ⬜ |
| `PAGE-033.40` | 571–584 | تابع | `cd_dvd_export` | 0 | ⬜ |
| `PAGE-033.41` | 586–614 | تابع | `cd_dvd_render_surface` | 2 | ⬜ |
| `PAGE-033.42` | 616–731 | تابع | `cd_dvd_render_page_legacy` | 2 | ⬜ |
| `PAGE-033.43` | 733–870 | تابع | `cd_dvd_render_page` | **8** | ⬜ |
| `PAGE-033.44` | 872–892 | تابع | `cd_dvd_render_history` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-034</b> — <code>chatbot-tts-lexicon.php</code> (15 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-034.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-035</b> — <code>chatbot.php</code> (3,413 خط، 123 آدرس، بازبینی‌شده 0/123)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-035.01` | 1–14 | ناحیه | `بلوک رویه‌ای 1-14` | 0 | ⬜ |
| `PAGE-035.02` | 15–27 | تابع | `chatbot_normalize` | 0 | ⬜ |
| `PAGE-035.03` | 29–33 | تابع | `chatbot_compact` | 0 | ⬜ |
| `PAGE-035.04` | 35–46 | تابع | `chatbot_keywords` | 0 | ⬜ |
| `PAGE-035.05` | 48–87 | تابع | `chatbot_score` | 0 | ⬜ |
| `PAGE-035.06` | 88–100 | ناحیه | `بلوک رویه‌ای 88-100` | 0 | ⬜ |
| `PAGE-035.07` | 101–111 | تابع | `chatbot_has_any` | 0 | ⬜ |
| `PAGE-035.08` | 113–125 | تابع | `chatbot_reply` | 1 | ⬜ |
| `PAGE-035.09` | 127–138 | تابع | `chatbot_flow_active` | 0 | ⬜ |
| `PAGE-035.10` | 140–168 | تابع | `chatbot_flow_step` | 0 | ⬜ |
| `PAGE-035.11` | 170–182 | تابع | `chatbot_ticket_ref` | 0 | ⬜ |
| `PAGE-035.12` | 184–196 | تابع | `chatbot_ticket_status` | 5 | ⬜ |
| `PAGE-035.13` | 198–202 | تابع | `chatbot_ticket_line` | 0 | ⬜ |
| `PAGE-035.14` | 204–407 | تابع | `chatbot_answer` | 1 | ⬜ |
| `PAGE-035.15` | 408–448 | ناحیه | `بلوک رویه‌ای 408-448` | 0 | ⬜ |
| `PAGE-035.16` | 449–471 | تابع | `chatbot_expand_words` | 0 | ⬜ |
| `PAGE-035.17` | 473–487 | تابع | `chatbot_edit_distance` | 0 | ⬜ |
| `PAGE-035.18` | 489–502 | تابع | `chatbot_typo_match` | 0 | ⬜ |
| `PAGE-035.19` | 503–506 | ناحیه | `بلوک رویه‌ای 503-506` | 0 | ⬜ |
| `PAGE-035.20` | 507–514 | تابع | `chatbot_contains_word` | 0 | ⬜ |
| `PAGE-035.21` | 516–530 | تابع | `chatbot_guide_tokens` | 0 | ⬜ |
| `PAGE-035.22` | 532–615 | تابع | `chatbot_guide_index` | 1 | ⬜ |
| `PAGE-035.23` | 617–649 | تابع | `chatbot_guide_search` | 0 | ⬜ |
| `PAGE-035.24` | 651–676 | تابع | `chatbot_guide_suggestions` | 0 | ⬜ |
| `PAGE-035.25` | 678–683 | تابع | `chatbot_guide_reply` | 0 | ⬜ |
| `PAGE-035.26` | 685–698 | تابع | `chatbot_guide_answer` | 0 | ⬜ |
| `PAGE-035.27` | 700–783 | تابع | `chatbot_api_handle` | **12** | ⬜ |
| `PAGE-035.28` | 785–815 | تابع | `chatbot_save_faq` | 1 | ⬜ |
| `PAGE-035.29` | 817–872 | تابع | `chatbot_save_tts` | 0 | ⬜ |
| `PAGE-035.30` | 874–896 | تابع | `chatbot_widget_defaults` | 0 | ⬜ |
| `PAGE-035.31` | 898–946 | تابع | `chatbot_widget_config` | 0 | ⬜ |
| `PAGE-035.32` | 948–990 | تابع | `chatbot_save_widget` | 0 | ⬜ |
| `PAGE-035.33` | 992–1006 | تابع | `chatbot_tts_config` | 0 | ⬜ |
| `PAGE-035.34` | 1008–1016 | تابع | `chatbot_tts_proc_available` | 0 | ⬜ |
| `PAGE-035.35` | 1018–1044 | تابع | `chatbot_tts_find_espeak` | 0 | ⬜ |
| `PAGE-035.36` | 1046–1069 | تابع | `chatbot_tts_find_piper` | 0 | ⬜ |
| `PAGE-035.37` | 1071–1104 | تابع | `chatbot_tts_piper_model_rank` | 0 | ⬜ |
| `PAGE-035.38` | 1106–1147 | تابع | `chatbot_tts_piper_model` | 1 | ⬜ |
| `PAGE-035.39` | 1149–1179 | تابع | `chatbot_tts_active` | 0 | ⬜ |
| `PAGE-035.40` | 1181–1187 | تابع | `chatbot_tts_length_scale` | 0 | ⬜ |
| `PAGE-035.41` | 1189–1216 | تابع | `chatbot_tts_status` | 0 | ⬜ |
| `PAGE-035.42` | 1218–1221 | تابع | `chatbot_tts_rules_version` | 0 | ⬜ |
| `PAGE-035.43` | 1223–1232 | تابع | `chatbot_tts_word_replace` | 0 | ⬜ |
| `PAGE-035.44` | 1234–1241 | تابع | `chatbot_tts_ascii_digits` | 0 | ⬜ |
| `PAGE-035.45` | 1243–1249 | تابع | `chatbot_tts_ascii_letters` | 0 | ⬜ |
| `PAGE-035.46` | 1251–1265 | تابع | `chatbot_tts_digits_spoken` | 0 | ⬜ |
| `PAGE-035.47` | 1267–1293 | تابع | `chatbot_tts_three_digit` | 0 | ⬜ |
| `PAGE-035.48` | 1295–1320 | تابع | `chatbot_tts_int_to_words` | 0 | ⬜ |
| `PAGE-035.49` | 1322–1339 | تابع | `chatbot_tts_number_token` | 0 | ⬜ |
| `PAGE-035.50` | 1341–1351 | تابع | `chatbot_tts_number_int_string` | 0 | ⬜ |
| `PAGE-035.51` | 1353–1365 | تابع | `chatbot_tts_ordinal` | 0 | ⬜ |
| `PAGE-035.52` | 1367–1371 | تابع | `chatbot_tts_month_name` | 0 | ⬜ |
| `PAGE-035.53` | 1373–1406 | تابع | `chatbot_tts_dates_times` | 0 | ⬜ |
| `PAGE-035.54` | 1408–1448 | تابع | `chatbot_tts_ids` | 0 | ⬜ |
| `PAGE-035.55` | 1450–1478 | تابع | `chatbot_tts_numbers_to_words` | 0 | ⬜ |
| `PAGE-035.56` | 1480–1490 | تابع | `chatbot_tts_letter_name` | 0 | ⬜ |
| `PAGE-035.57` | 1492–1503 | تابع | `chatbot_tts_log_unknown` | 2 | ⬜ |
| `PAGE-035.58` | 1505–1528 | تابع | `chatbot_tts_spell_acronyms` | 0 | ⬜ |
| `PAGE-035.59` | 1530–1555 | تابع | `chatbot_tts_unknown_latin` | 0 | ⬜ |
| `PAGE-035.60` | 1557–1570 | تابع | `chatbot_tts_symbols` | 0 | ⬜ |
| `PAGE-035.61` | 1572–1579 | تابع | `chatbot_tts_strip_markdown` | 0 | ⬜ |
| `PAGE-035.62` | 1581–1586 | تابع | `chatbot_tts_zwnj_policy` | 1 | ⬜ |
| `PAGE-035.63` | 1588–1598 | تابع | `chatbot_tts_apply_zwnj` | 1 | ⬜ |
| `PAGE-035.64` | 1600–1607 | تابع | `chatbot_tts_he_yezafe` | 0 | ⬜ |
| `PAGE-035.65` | 1609–1635 | تابع | `chatbot_tts_lexicon` | 0 | ⬜ |
| `PAGE-035.66` | 1637–1641 | تابع | `chatbot_tts_lexicon_hash` | 0 | ⬜ |
| `PAGE-035.67` | 1643–1654 | تابع | `chatbot_tts_lexicon_apply` | 0 | ⬜ |
| `PAGE-035.68` | 1656–1659 | تابع | `chatbot_tts_spoken_path` | 0 | ⬜ |
| `PAGE-035.69` | 1661–1669 | تابع | `chatbot_tts_spoken_map` | 1 | ⬜ |
| `PAGE-035.70` | 1671–1688 | تابع | `chatbot_tts_spoken_set` | 2 | ⬜ |
| `PAGE-035.71` | 1690–1701 | تابع | `chatbot_tts_spoken_for` | 0 | ⬜ |
| `PAGE-035.72` | 1703–1714 | تابع | `chatbot_tts_clean` | 0 | ⬜ |
| `PAGE-035.73` | 1716–1730 | تابع | `chatbot_tts_trim_length` | 0 | ⬜ |
| `PAGE-035.74` | 1732–1777 | تابع | `chatbot_tts_colloquial` | 0 | ⬜ |
| `PAGE-035.75` | 1779–1796 | تابع | `chatbot_tts_phrase` | 0 | ⬜ |
| `PAGE-035.76` | 1798–1832 | تابع | `chatbot_tts_limit_phrase` | 0 | ⬜ |
| `PAGE-035.77` | 1834–1841 | تابع | `chatbot_tts_vocalize` | 0 | ⬜ |
| `PAGE-035.78` | 1843–1856 | تابع | `chatbot_tts_ezafe_skip_first` | 0 | ⬜ |
| `PAGE-035.79` | 1858–1869 | تابع | `chatbot_tts_ezafe_skip_second` | 0 | ⬜ |
| `PAGE-035.80` | 1871–1882 | تابع | `chatbot_tts_silent_heh` | 0 | ⬜ |
| `PAGE-035.81` | 1884–1895 | تابع | `chatbot_tts_ezafe_nouns` | 0 | ⬜ |
| `PAGE-035.82` | 1897–1908 | تابع | `chatbot_tts_ezafe_pairs_builtin` | 0 | ⬜ |
| `PAGE-035.83` | 1910–1930 | تابع | `chatbot_tts_overlay_map` | 0 | ⬜ |
| `PAGE-035.84` | 1932–1944 | تابع | `chatbot_tts_ezafe_pairs` | 0 | ⬜ |
| `PAGE-035.85` | 1946–1953 | تابع | `chatbot_tts_ezafe_mode` | 0 | ⬜ |
| `PAGE-035.86` | 1955–1959 | تابع | `chatbot_tts_word_bare` | 0 | ⬜ |
| `PAGE-035.87` | 1961–1980 | تابع | `chatbot_tts_mark_ezafe_first` | 0 | ⬜ |
| `PAGE-035.88` | 1982–2032 | تابع | `chatbot_tts_ezafe` | 0 | ⬜ |
| `PAGE-035.89` | 2034–2038 | تابع | `chatbot_tts_colloquial_enabled` | 0 | ⬜ |
| `PAGE-035.90` | 2040–2072 | تابع | `chatbot_tts_prepare` | 0 | ⬜ |
| `PAGE-035.91` | 2074–2093 | تابع | `chatbot_tts_lab_variant` | 0 | ⬜ |
| `PAGE-035.92` | 2095–2121 | تابع | `chatbot_tts_lab_harakat` | 0 | ⬜ |
| `PAGE-035.93` | 2123–2161 | تابع | `chatbot_tts_stages` | 0 | ⬜ |
| `PAGE-035.94` | 2163–2179 | تابع | `chatbot_tts_espeak_data_path` | 0 | ⬜ |
| `PAGE-035.95` | 2181–2210 | تابع | `chatbot_tts_ipa` | 0 | ⬜ |
| `PAGE-035.96` | 2212–2263 | تابع | `chatbot_tts_model_json_report` | 0 | ⬜ |
| `PAGE-035.97` | 2265–2269 | تابع | `chatbot_tts_lab_filename` | 0 | ⬜ |
| `PAGE-035.98` | 2271–2297 | تابع | `chatbot_tts_lab_run` | 2 | ⬜ |
| `PAGE-035.99` | 2299–2303 | تابع | `chatbot_tts_voice_id` | 0 | ⬜ |
| `PAGE-035.100` | 2305–2318 | تابع | `chatbot_tts_wait_exit` | 2 | ⬜ |
| `PAGE-035.101` | 2320–2350 | تابع | `chatbot_tts_taskkill` | 7 | ⬜ |
| `PAGE-035.102` | 2352–2440 | تابع | `chatbot_tts_exec` | 7 | ⬜ |
| `PAGE-035.103` | 2442–2446 | تابع | `chatbot_tts_is_valid_wav` | 0 | ⬜ |
| `PAGE-035.104` | 2448–2470 | تابع | `chatbot_tts_run_espeak` | 6 | ⬜ |
| `PAGE-035.105` | 2472–2519 | تابع | `chatbot_tts_run_piper` | 7 | ⬜ |
| `PAGE-035.106` | 2521–2535 | تابع | `chatbot_tts_probe_piper` | 0 | ⬜ |
| `PAGE-035.107` | 2537–2549 | تابع | `chatbot_tts_probe_engine` | 0 | ⬜ |
| `PAGE-035.108` | 2550–2551 | ناحیه | `بلوک رویه‌ای 2550-2551` | 0 | ⬜ |
| `PAGE-035.109` | 2552–2598 | تابع | `chatbot_tts_voice_status` | 3 | ⬜ |
| `PAGE-035.110` | 2600–2626 | تابع | `chatbot_tts_chunks` | 0 | ⬜ |
| `PAGE-035.111` | 2628–2663 | تابع | `chatbot_tts_wav_parse` | 0 | ⬜ |
| `PAGE-035.112` | 2665–2704 | تابع | `chatbot_tts_wav_concat` | 0 | ⬜ |
| `PAGE-035.113` | 2706–2763 | تابع | `chatbot_tts_say` | 7 | ⬜ |
| `PAGE-035.114` | 2765–2794 | تابع | `chatbot_tts_lab_default_phrases` | 0 | ⬜ |
| `PAGE-035.115` | 2796–2856 | تابع | `chatbot_tts_render_lab` | 0 | ⬜ |
| `PAGE-035.116` | 2858–2879 | تابع | `chatbot_tts_write_overlay` | 2 | ⬜ |
| `PAGE-035.117` | 2881–2904 | تابع | `chatbot_tts_corpus_texts` | 4 | ⬜ |
| `PAGE-035.118` | 2906–2910 | تابع | `chatbot_tts_propose_pair` | 0 | ⬜ |
| `PAGE-035.119` | 2912–2951 | تابع | `chatbot_tts_mine_pairs` | 0 | ⬜ |
| `PAGE-035.120` | 2953–2988 | تابع | `chatbot_tts_save_mined_pairs` | 0 | ⬜ |
| `PAGE-035.121` | 2990–3010 | تابع | `chatbot_tts_render_pair_miner` | 0 | ⬜ |
| `PAGE-035.122` | 3012–3204 | تابع | `chatbot_render_admin` | **8** | ⬜ |
| `PAGE-035.123` | 3206–3412 | تابع | `chatbot_render_widget` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-036</b> — <code>config.sample.php</code> (50 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-036.01` | 1–50 | ناحیه | `بلوک رویه‌ای 1-50` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-037</b> — <code>domain-scan.php</code> (1,279 خط، 40 آدرس، بازبینی‌شده 0/40)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-037.01` | 1–4 | ناحیه | `بلوک رویه‌ای 1-4` | 0 | ⬜ |
| `PAGE-037.02` | 5–46 | تابع | `domain_scan_ensure_schema` | 5 | ⬜ |
| `PAGE-037.03` | 48–52 | تابع | `domain_scan_timeout_ms` | 0 | ⬜ |
| `PAGE-037.04` | 54–57 | تابع | `domain_scan_account_label` | 0 | ⬜ |
| `PAGE-037.05` | 59–62 | تابع | `domain_scan_https_request` | 0 | ⬜ |
| `PAGE-037.06` | 64–73 | تابع | `domain_scan_validate_credentials` | 0 | ⬜ |
| `PAGE-037.07` | 75–82 | تابع | `domain_scan_credential_key` | 0 | ⬜ |
| `PAGE-037.08` | 84–111 | تابع | `domain_scan_store_run_credentials` | 0 | ⬜ |
| `PAGE-037.09` | 113–144 | تابع | `domain_scan_run_credentials` | 0 | ⬜ |
| `PAGE-037.10` | 146–158 | تابع | `domain_scan_clear_run_credentials` | 0 | ⬜ |
| `PAGE-037.11` | 160–175 | تابع | `domain_scan_ad_time` | 0 | ⬜ |
| `PAGE-037.12` | 177–245 | تابع | `ldap_domain_computers` | 3 | ⬜ |
| `PAGE-037.13` | 247–272 | تابع | `domain_scan_asset_for` | 1 | ⬜ |
| `PAGE-037.14` | 274–283 | تابع | `domain_scan_ad_values` | 0 | ⬜ |
| `PAGE-037.15` | 285–307 | تابع | `domain_scan_start` | 1 | ⬜ |
| `PAGE-037.16` | 309–345 | تابع | `domain_scan_start_selected` | 6 | ⬜ |
| `PAGE-037.17` | 347–378 | تابع | `domain_scan_import` | 1 | ⬜ |
| `PAGE-037.18` | 380–386 | تابع | `domain_scan_powershell_path` | 0 | ⬜ |
| `PAGE-037.19` | 388–464 | تابع | `domain_scan_run_processes` | 7 | ⬜ |
| `PAGE-037.20` | 466–473 | تابع | `domain_scan_resolve_ipv4` | 1 | ⬜ |
| `PAGE-037.21` | 475–482 | تابع | `domain_scan_ping_command` | 0 | ⬜ |
| `PAGE-037.22` | 484–502 | تابع | `domain_scan_icmp_round` | 0 | ⬜ |
| `PAGE-037.23` | 504–576 | تابع | `domain_scan_tcp_probe` | 3 | ⬜ |
| `PAGE-037.24` | 578–650 | تابع | `domain_scan_ping_hosts` | 1 | ⬜ |
| `PAGE-037.25` | 652–660 | تابع | `domain_scan_online` | 0 | ⬜ |
| `PAGE-037.26` | 662–680 | تابع | `domain_scan_mark_reachability` | 1 | ⬜ |
| `PAGE-037.27` | 682–714 | تابع | `domain_scan_friendly_error` | 1 | ⬜ |
| `PAGE-037.28` | 716–846 | تابع | `domain_scan_error_catalog` | 1 | ⬜ |
| `PAGE-037.29` | 848–880 | تابع | `domain_scan_error_code` | 0 | ⬜ |
| `PAGE-037.30` | 882–919 | تابع | `domain_scan_failure_summary` | 0 | ⬜ |
| `PAGE-037.31` | 921–941 | تابع | `domain_scan_retry_failed` | 1 | ⬜ |
| `PAGE-037.32` | 943–966 | تابع | `domain_scan_local_names` | 2 | ⬜ |
| `PAGE-037.33` | 968–984 | تابع | `domain_scan_preflight` | 0 | ⬜ |
| `PAGE-037.34` | 986–1091 | تابع | `domain_scan_remote_inventory_many` | 5 | ⬜ |
| `PAGE-037.35` | 1093–1104 | تابع | `domain_scan_remote_inventory` | 0 | ⬜ |
| `PAGE-037.36` | 1106–1153 | تابع | `domain_scan_store_inventory` | 1 | ⬜ |
| `PAGE-037.37` | 1155–1252 | تابع | `domain_scan_process` | **8** | ⬜ |
| `PAGE-037.38` | 1254–1261 | تابع | `domain_scan_run` | 0 | ⬜ |
| `PAGE-037.39` | 1263–1270 | تابع | `domain_scan_queue_rows` | 5 | ⬜ |
| `PAGE-037.40` | 1272–1278 | تابع | `domain_scan_latest_run` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-038</b> — <code>food-order-import.php</code> (477 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-038.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-038.02` | 4–35 | تابع | `food_order_import_column_keys` | 0 | ⬜ |
| `PAGE-038.03` | 36–38 | ناحیه | `بلوک رویه‌ای 36-38` | 0 | ⬜ |
| `PAGE-038.04` | 39–46 | تابع | `food_order_import_key` | 0 | ⬜ |
| `PAGE-038.05` | 48–113 | تابع | `food_order_import_read` | 0 | ⬜ |
| `PAGE-038.06` | 115–130 | تابع | `food_order_import_date` | 0 | ⬜ |
| `PAGE-038.07` | 132–151 | تابع | `food_order_import_time` | 0 | ⬜ |
| `PAGE-038.08` | 153–159 | تابع | `food_order_import_norm_food` | 0 | ⬜ |
| `PAGE-038.09` | 161–312 | تابع | `food_order_import_plan` | 6 | ⬜ |
| `PAGE-038.10` | 314–367 | تابع | `food_order_import_apply` | 0 | ⬜ |
| `PAGE-038.11` | 369–372 | تابع | `food_order_import_h` | 0 | ⬜ |
| `PAGE-038.12` | 374–476 | تابع | `food_order_import_handle` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-039</b> — <code>food-order.php</code> (3,682 خط، 101 آدرس، بازبینی‌شده 0/101)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-039.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-039.02` | 4–40 | تابع | `food_order_schema_ensure` | 5 | ⬜ |
| `PAGE-039.03` | 42–50 | تابع | `food_order_normalize_digits` | 0 | ⬜ |
| `PAGE-039.04` | 52–55 | تابع | `food_order_digits` | 0 | ⬜ |
| `PAGE-039.05` | 57–93 | تابع | `food_order_iso_date` | 0 | ⬜ |
| `PAGE-039.06` | 95–103 | تابع | `food_order_timezone` | 0 | ⬜ |
| `PAGE-039.07` | 105–108 | تابع | `food_order_now` | 0 | ⬜ |
| `PAGE-039.08` | 110–113 | تابع | `food_order_today` | 0 | ⬜ |
| `PAGE-039.09` | 115–123 | تابع | `food_order_jalali_label` | 0 | ⬜ |
| `PAGE-039.10` | 125–154 | تابع | `food_order_jalali_month_range` | 0 | ⬜ |
| `PAGE-039.11` | 156–161 | تابع | `food_order_is_primary_admin` | 0 | ⬜ |
| `PAGE-039.12` | 163–166 | تابع | `food_order_user_can` | 0 | ⬜ |
| `PAGE-039.13` | 168–173 | تابع | `food_order_require_permission` | 0 | ⬜ |
| `PAGE-039.14` | 175–184 | تابع | `food_order_can_view_order_counts` | 0 | ⬜ |
| `PAGE-039.15` | 186–191 | تابع | `food_order_require_primary_admin` | 0 | ⬜ |
| `PAGE-039.16` | 193–206 | تابع | `food_order_day_visit_subject` | 0 | ⬜ |
| `PAGE-039.17` | 208–219 | تابع | `food_order_log` | 1 | ⬜ |
| `PAGE-039.18` | 221–236 | تابع | `food_order_audit` | 0 | ⬜ |
| `PAGE-039.19` | 238–244 | تابع | `food_order_raw_mode` | 0 | ⬜ |
| `PAGE-039.20` | 246–265 | تابع | `food_order_mode` | 0 | ⬜ |
| `PAGE-039.21` | 267–276 | تابع | `food_order_user_row` | 0 | ⬜ |
| `PAGE-039.22` | 278–284 | تابع | `food_order_full_name` | 0 | ⬜ |
| `PAGE-039.23` | 286–307 | تابع | `food_order_employee_search` | 5 | ⬜ |
| `PAGE-039.24` | 309–333 | تابع | `food_order_report_employee_search` | 5 | ⬜ |
| `PAGE-039.25` | 335–339 | تابع | `food_order_name_key` | 0 | ⬜ |
| `PAGE-039.26` | 341–355 | تابع | `food_order_catalog_has_category` | 0 | ⬜ |
| `PAGE-039.27` | 357–373 | تابع | `food_order_catalog` | 0 | ⬜ |
| `PAGE-039.28` | 375–385 | تابع | `food_order_meal_type` | 0 | ⬜ |
| `PAGE-039.29` | 387–516 | تابع | `food_order_month_status` | 6 | ⬜ |
| `PAGE-039.30` | 518–551 | تابع | `food_order_list_date` | 0 | ⬜ |
| `PAGE-039.31` | 553–609 | تابع | `food_order_list_range` | 4 | ⬜ |
| `PAGE-039.32` | 611–664 | تابع | `food_order_my_orders` | 5 | ⬜ |
| `PAGE-039.33` | 666–701 | تابع | `food_order_report_descriptions` | 0 | ⬜ |
| `PAGE-039.34` | 703–745 | تابع | `food_order_manager_orders` | 0 | ⬜ |
| `PAGE-039.35` | 747–788 | تابع | `food_order_report_rows_range` | 0 | ⬜ |
| `PAGE-039.36` | 790–854 | تابع | `food_order_group_orders` | 0 | ⬜ |
| `PAGE-039.37` | 856–882 | تابع | `food_order_report_summary` | 5 | ⬜ |
| `PAGE-039.38` | 884–946 | تابع | `food_order_statistics_range` | 0 | ⬜ |
| `PAGE-039.39` | 948–1075 | تابع | `food_order_printed_delivery_report` | 5 | ⬜ |
| `PAGE-039.40` | 1077–1128 | تابع | `food_order_export_printed_delivery_report` | 0 | ⬜ |
| `PAGE-039.41` | 1130–1180 | تابع | `food_order_selected_orders_range` | 5 | ⬜ |
| `PAGE-039.42` | 1182–1224 | تابع | `food_order_export_selected_orders_range` | 0 | ⬜ |
| `PAGE-039.43` | 1226–1243 | تابع | `food_order_emergency_window_payload` | 0 | ⬜ |
| `PAGE-039.44` | 1245–1257 | تابع | `food_order_emergency_window_state` | 0 | ⬜ |
| `PAGE-039.45` | 1259–1280 | تابع | `food_order_emergency_window_states` | 5 | ⬜ |
| `PAGE-039.46` | 1282–1335 | تابع | `food_order_emergency_window_grant` | 0 | ⬜ |
| `PAGE-039.47` | 1337–1372 | تابع | `food_order_emergency_window_revoke` | 0 | ⬜ |
| `PAGE-039.48` | 1374–1397 | تابع | `food_order_ensure_orderable_day` | 1 | ⬜ |
| `PAGE-039.49` | 1399–1422 | تابع | `food_order_lock_cancellable_day` | 1 | ⬜ |
| `PAGE-039.50` | 1424–1439 | تابع | `food_order_verify_employee_national_code` | 1 | ⬜ |
| `PAGE-039.51` | 1441–1548 | تابع | `food_order_create_or_change` | 0 | ⬜ |
| `PAGE-039.52` | 1550–1594 | تابع | `food_order_cancel` | 0 | ⬜ |
| `PAGE-039.53` | 1596–1665 | تابع | `food_order_cancel_proxy` | 0 | ⬜ |
| `PAGE-039.54` | 1667–1697 | تابع | `food_order_cancel_request_ensure` | 5 | ⬜ |
| `PAGE-039.55` | 1699–1704 | تابع | `food_order_require_cancel_reviewer` | 0 | ⬜ |
| `PAGE-039.56` | 1706–1709 | تابع | `food_order_cancel_inbox_url` | 0 | ⬜ |
| `PAGE-039.57` | 1711–1728 | تابع | `food_order_user_is_cancel_reviewer` | 0 | ⬜ |
| `PAGE-039.58` | 1730–1745 | تابع | `food_order_cancel_reviewer_ids` | 0 | ⬜ |
| `PAGE-039.59` | 1747–1779 | تابع | `food_order_notify_cancel_reviewers` | 0 | ⬜ |
| `PAGE-039.60` | 1781–1784 | تابع | `food_order_notify_support_managers` | 0 | ⬜ |
| `PAGE-039.61` | 1786–1819 | تابع | `food_order_render_pending_cancels_card` | 0 | ⬜ |
| `PAGE-039.62` | 1821–1846 | تابع | `food_order_pending_cancel_map` | 0 | ⬜ |
| `PAGE-039.63` | 1848–1932 | تابع | `food_order_cancel_request_create` | 4 | ⬜ |
| `PAGE-039.64` | 1934–1967 | تابع | `food_order_cancel_request_list` | 0 | ⬜ |
| `PAGE-039.65` | 1969–2042 | تابع | `food_order_cancel_request_decide` | 4 | ⬜ |
| `PAGE-039.66` | 2044–2126 | تابع | `food_order_catalog_save` | 0 | ⬜ |
| `PAGE-039.67` | 2128–2256 | تابع | `food_order_save_day` | 6 | ⬜ |
| `PAGE-039.68` | 2258–2278 | تابع | `food_order_status_summary` | 0 | ⬜ |
| `PAGE-039.69` | 2280–2288 | تابع | `food_order_api_body` | 0 | ⬜ |
| `PAGE-039.70` | 2290–2299 | تابع | `food_order_send_json` | 0 | ⬜ |
| `PAGE-039.71` | 2301–2308 | تابع | `food_order_safe_error` | 0 | ⬜ |
| `PAGE-039.72` | 2310–2318 | تابع | `food_order_export_safe_value` | 0 | ⬜ |
| `PAGE-039.73` | 2320–2385 | تابع | `food_order_export_manager_orders` | 0 | ⬜ |
| `PAGE-039.74` | 2387–2408 | تابع | `food_order_send_xlsx` | 0 | ⬜ |
| `PAGE-039.75` | 2410–2461 | تابع | `food_order_export_statistics_range` | 5 | ⬜ |
| `PAGE-039.76` | 2463–2467 | تابع | `food_guest_request_roles` | 0 | ⬜ |
| `PAGE-039.77` | 2469–2478 | تابع | `food_guest_request_is_reviewer` | 0 | ⬜ |
| `PAGE-039.78` | 2480–2485 | تابع | `food_guest_request_allowed` | 0 | ⬜ |
| `PAGE-039.79` | 2487–2499 | تابع | `food_guest_request_can_manage` | 0 | ⬜ |
| `PAGE-039.80` | 2501–2510 | تابع | `food_guest_request_change_window_open` | 0 | ⬜ |
| `PAGE-039.81` | 2512–2526 | تابع | `food_guest_request_same_day_cutoff` | 0 | ⬜ |
| `PAGE-039.82` | 2528–2533 | تابع | `food_guest_request_same_day_is_open` | 0 | ⬜ |
| `PAGE-039.83` | 2535–2553 | تابع | `food_guest_request_date_error` | 0 | ⬜ |
| `PAGE-039.84` | 2555–2569 | تابع | `food_guest_requested_total` | 0 | ⬜ |
| `PAGE-039.85` | 2571–2599 | تابع | `food_guest_schema_missing` | 0 | ⬜ |
| `PAGE-039.86` | 2601–2605 | تابع | `food_guest_schema_ready` | 0 | ⬜ |
| `PAGE-039.87` | 2607–2613 | تابع | `food_guest_schema_ensure` | 0 | ⬜ |
| `PAGE-039.88` | 2615–2625 | تابع | `food_guest_deputies` | 0 | ⬜ |
| `PAGE-039.89` | 2627–2676 | تابع | `food_guest_user_deputy_id` | 4 | ⬜ |
| `PAGE-039.90` | 2678–2772 | تابع | `food_guest_request_list` | 0 | ⬜ |
| `PAGE-039.91` | 2774–2822 | تابع | `food_guest_request_create` | 1 | ⬜ |
| `PAGE-039.92` | 2824–2856 | تابع | `food_guest_request_cancel` | 0 | ⬜ |
| `PAGE-039.93` | 2858–2921 | تابع | `food_guest_request_update` | 0 | ⬜ |
| `PAGE-039.94` | 2923–2957 | تابع | `food_guest_request_delete` | 0 | ⬜ |
| `PAGE-039.95` | 2959–3128 | تابع | `food_order_api_handle` | 4 | ⬜ |
| `PAGE-039.96` | 3131–3202 | تابع | `food_order_emergency_handle` | 2 | ⬜ |
| `PAGE-039.97` | 3204–3314 | تابع | `food_order_bulk_snapshot` | 0 | ⬜ |
| `PAGE-039.98` | 3316–3401 | تابع | `food_order_bulk_apply` | 0 | ⬜ |
| `PAGE-039.99` | 3403–3418 | تابع | `food_order_bulk_api_handle` | 2 | ⬜ |
| `PAGE-039.100` | 3420–3648 | تابع | `food_order_menu_api_handle` | 4 | ⬜ |
| `PAGE-039.101` | 3650–3681 | تابع | `food_order_render_page` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-040</b> — <code>food-personnel-link.php</code> (818 خط، 27 آدرس، بازبینی‌شده 0/27)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-040.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `PAGE-040.02` | 20–27 | تابع | `fpl_norm_name` | 0 | ⬜ |
| `PAGE-040.03` | 29–37 | تابع | `fpl_person_name` | 0 | ⬜ |
| `PAGE-040.04` | 39–62 | تابع | `fpl_reference_columns` | 4 | ⬜ |
| `PAGE-040.05` | 64–86 | تابع | `fpl_manual_reference_columns` | 0 | ⬜ |
| `PAGE-040.06` | 88–97 | تابع | `fpl_column_exists` | 0 | ⬜ |
| `PAGE-040.07` | 99–187 | تابع | `fpl_candidates` | 0 | ⬜ |
| `PAGE-040.08` | 189–203 | تابع | `fpl_pair_is_allowed` | 0 | ⬜ |
| `PAGE-040.09` | 205–315 | تابع | `fpl_merge` | 5 | ⬜ |
| `PAGE-040.10` | 317–323 | تابع | `fpl_backup_users` | 4 | ⬜ |
| `PAGE-040.11` | 325–330 | تابع | `fpl_normalize_code` | 0 | ⬜ |
| `PAGE-040.12` | 332–345 | تابع | `fpl_codes_equal` | 0 | ⬜ |
| `PAGE-040.13` | 347–353 | تابع | `fpl_is_local_food_account` | 0 | ⬜ |
| `PAGE-040.14` | 355–364 | تابع | `fpl_food_personnel_code` | 0 | ⬜ |
| `PAGE-040.15` | 366–379 | تابع | `fpl_food_matches_code` | 0 | ⬜ |
| `PAGE-040.16` | 381–401 | تابع | `fpl_code_candidate_rows` | 0 | ⬜ |
| `PAGE-040.17` | 403–425 | تابع | `fpl_food_code_matches` | 0 | ⬜ |
| `PAGE-040.18` | 427–437 | تابع | `fpl_find_food_by_code` | 0 | ⬜ |
| `PAGE-040.19` | 439–461 | تابع | `fpl_select_food_matches` | 0 | ⬜ |
| `PAGE-040.20` | 463–541 | تابع | `fpl_apply` | 0 | ⬜ |
| `PAGE-040.21` | 543–548 | تابع | `fpl_manual_input` | 0 | ⬜ |
| `PAGE-040.22` | 550–661 | تابع | `fpl_render_request` | 2 | ⬜ |
| `PAGE-040.23` | 663–684 | تابع | `fpl_backup_tables` | 0 | ⬜ |
| `PAGE-040.24` | 686–768 | تابع | `fpl_report_rows` | 0 | ⬜ |
| `PAGE-040.25` | 770–790 | تابع | `fpl_similar_food_names` | 0 | ⬜ |
| `PAGE-040.26` | 792–811 | تابع | `fpl_report_xlsx_bytes` | 0 | ⬜ |
| `PAGE-040.27` | 813–817 | تابع | `fpl_report_csv` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-041</b> — <code>food-reconciliation.php</code> (864 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-041.01` | 1–10 | ناحیه | `بلوک رویه‌ای 1-10` | 0 | ⬜ |
| `PAGE-041.02` | 11–24 | تابع | `food_reconciliation_schema_ensure` | 5 | ⬜ |
| `PAGE-041.03` | 26–70 | تابع | `food_reconciliation_calculate` | 0 | ⬜ |
| `PAGE-041.04` | 72–90 | تابع | `food_reconciliation_count` | 0 | ⬜ |
| `PAGE-041.05` | 92–108 | تابع | `food_reconciliation_month` | 0 | ⬜ |
| `PAGE-041.06` | 110–125 | تابع | `food_reconciliation_month_range` | 0 | ⬜ |
| `PAGE-041.07` | 127–137 | تابع | `food_reconciliation_month_key` | 0 | ⬜ |
| `PAGE-041.08` | 139–151 | تابع | `food_reconciliation_date_range` | 0 | ⬜ |
| `PAGE-041.09` | 153–160 | تابع | `food_reconciliation_meal_filter` | 0 | ⬜ |
| `PAGE-041.10` | 162–183 | تابع | `food_reconciliation_order_counts` | 0 | ⬜ |
| `PAGE-041.11` | 185–283 | تابع | `food_reconciliation_rows` | 0 | ⬜ |
| `PAGE-041.12` | 285–307 | تابع | `food_reconciliation_invoice_rows` | 0 | ⬜ |
| `PAGE-041.13` | 309–402 | تابع | `food_reconciliation_report` | 0 | ⬜ |
| `PAGE-041.14` | 404–411 | تابع | `food_reconciliation_audit` | 0 | ⬜ |
| `PAGE-041.15` | 413–551 | تابع | `food_reconciliation_save` | 1 | ⬜ |
| `PAGE-041.16` | 553–556 | تابع | `food_reconciliation_upload_directory` | 0 | ⬜ |
| `PAGE-041.17` | 558–636 | تابع | `food_reconciliation_validate_file` | 1 | ⬜ |
| `PAGE-041.18` | 638–722 | تابع | `food_reconciliation_upload_invoice` | **8** | ⬜ |
| `PAGE-041.19` | 724–756 | تابع | `food_reconciliation_download_invoice` | 0 | ⬜ |
| `PAGE-041.20` | 758–831 | تابع | `food_reconciliation_export` | 0 | ⬜ |
| `PAGE-041.21` | 833–863 | تابع | `food_reconciliation_api_handle` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-042</b> — <code>food-ticket-auto-receipts.php</code> (352 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-042.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-042.02` | 4–21 | تابع | `food_ticket_auto_receipt_label` | 0 | ⬜ |
| `PAGE-042.03` | 23–35 | تابع | `food_ticket_auto_receipt_date` | 0 | ⬜ |
| `PAGE-042.04` | 37–53 | تابع | `food_ticket_auto_receipt_holiday` | 0 | ⬜ |
| `PAGE-042.05` | 55–69 | تابع | `food_ticket_auto_receipt_table_exists` | 0 | ⬜ |
| `PAGE-042.06` | 71–84 | تابع | `food_ticket_auto_receipt_columns` | 0 | ⬜ |
| `PAGE-042.07` | 86–120 | تابع | `food_ticket_auto_receipt_schema_ready` | 0 | ⬜ |
| `PAGE-042.08` | 122–163 | تابع | `food_ticket_auto_receipt_insert` | 1 | ⬜ |
| `PAGE-042.09` | 165–351 | تابع | `food_ticket_auto_receipts_run` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-043</b> — <code>food-ticket-engine.php</code> (1,102 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-043.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `PAGE-043.02` | 13–20 | تابع | `food_ticket_norm_personnel` | 0 | ⬜ |
| `PAGE-043.03` | 22–52 | تابع | `food_ticket_user_index` | 0 | ⬜ |
| `PAGE-043.04` | 54–84 | تابع | `food_ticket_find_user_strict` | 0 | ⬜ |
| `PAGE-043.05` | 86–131 | تابع | `food_ticket_engine_delete_source` | 0 | ⬜ |
| `PAGE-043.06` | 133–142 | تابع | `food_ticket_build_order_map` | 0 | ⬜ |
| `PAGE-043.07` | 144–154 | تابع | `food_ticket_order_map_lazy` | 0 | ⬜ |
| `PAGE-043.08` | 156–178 | تابع | `food_ticket_card_map_ensure` | 5 | ⬜ |
| `PAGE-043.09` | 180–200 | تابع | `food_ticket_card_map_load` | 0 | ⬜ |
| `PAGE-043.10` | 202–217 | تابع | `food_ticket_card_map_lookup` | 0 | ⬜ |
| `PAGE-043.11` | 219–237 | تابع | `food_ticket_card_map_learn` | 1 | ⬜ |
| `PAGE-043.12` | 239–270 | تابع | `food_ticket_process_print_queue_locked` | 3 | ⬜ |
| `PAGE-043.13` | 272–324 | تابع | `food_ticket_find_twin_event` | 0 | ⬜ |
| `PAGE-043.14` | 326–347 | تابع | `food_ticket_sort_punches` | 0 | ⬜ |
| `PAGE-043.15` | 348–352 | ناحیه | `بلوک رویه‌ای 348-352` | 1 | ⬜ |
| `PAGE-043.16` | 353–370 | تابع | `food_guest_cap_mode` | 0 | ⬜ |
| `PAGE-043.17` | 372–380 | تابع | `food_guest_cap_clock` | 0 | ⬜ |
| `PAGE-043.18` | 382–387 | تابع | `food_guest_cap_start` | 0 | ⬜ |
| `PAGE-043.19` | 389–394 | تابع | `food_guest_cap_end` | 0 | ⬜ |
| `PAGE-043.20` | 396–401 | تابع | `food_guest_cap_cutoff` | 0 | ⬜ |
| `PAGE-043.21` | 403–411 | تابع | `food_guest_variable_cap_from_counts` | 0 | ⬜ |
| `PAGE-043.22` | 413–426 | تابع | `food_guest_variable_cap_at` | 0 | ⬜ |
| `PAGE-043.23` | 428–448 | تابع | `food_ticket_guest_variable_cap` | 0 | ⬜ |
| `PAGE-043.24` | 449–454 | ناحیه | `بلوک رویه‌ای 449-454` | 0 | ⬜ |
| `PAGE-043.25` | 455–461 | تابع | `food_ticket_guest_issued_total` | 0 | ⬜ |
| `PAGE-043.26` | 463–471 | تابع | `food_ticket_guest_cap_reached` | 0 | ⬜ |
| `PAGE-043.27` | 473–651 | تابع | `food_ticket_decide_punch` | 7 | ⬜ |
| `PAGE-043.28` | 653–769 | تابع | `food_ticket_decide_for_user` | 1 | ⬜ |
| `PAGE-043.29` | 771–838 | تابع | `food_ticket_read_source_rows_today` | 0 | ⬜ |
| `PAGE-043.30` | 840–1101 | تابع | `food_ticket_process_batch_v2` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-044</b> — <code>food-ticket-env.php</code> (267 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-044.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `PAGE-044.02` | 25–59 | تابع | `food_ticket_env_value` | 0 | ⬜ |
| `PAGE-044.03` | 61–65 | تابع | `food_ticket_env_source_table` | 0 | ⬜ |
| `PAGE-044.04` | 68–72 | تابع | `food_ticket_legacy_key` | 0 | ⬜ |
| `PAGE-044.05` | 74–80 | تابع | `food_ticket_legacy_display_map` | 0 | ⬜ |
| `PAGE-044.06` | 82–92 | تابع | `food_ticket_legacy_brand_tokens` | 0 | ⬜ |
| `PAGE-044.07` | 94–100 | تابع | `food_ticket_legacy_brand_words` | 0 | ⬜ |
| `PAGE-044.08` | 102–115 | تابع | `food_ticket_is_legacy_brand_name` | 0 | ⬜ |
| `PAGE-044.09` | 117–121 | تابع | `food_ticket_route_is_test_attendance` | 0 | ⬜ |
| `PAGE-044.10` | 123–131 | تابع | `food_ticket_printer_profile` | 0 | ⬜ |
| `PAGE-044.11` | 133–175 | تابع | `food_ticket_db_column` | 0 | ⬜ |
| `PAGE-044.12` | 177–207 | تابع | `food_ticket_config_has_column` | 0 | ⬜ |
| `PAGE-044.13` | 209–227 | تابع | `food_ticket_config_alias_row` | 0 | ⬜ |
| `PAGE-044.14` | 229–241 | تابع | `food_ticket_setting_password_enc` | 0 | ⬜ |
| `PAGE-044.15` | 243–256 | تابع | `food_ticket_source_table` | 0 | ⬜ |
| `PAGE-044.16` | 258–266 | تابع | `food_ticket_env_legacy_paths` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-045</b> — <code>food-ticket-groups.php</code> (2,956 خط، 70 آدرس، بازبینی‌شده 0/70)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-045.01` | 1–30 | ناحیه | `بلوک رویه‌ای 1-30` | 0 | ⬜ |
| `PAGE-045.02` | 31–60 | تابع | `food_ticket_audit` | 0 | ⬜ |
| `PAGE-045.03` | 62–157 | تابع | `food_ticket_groups_ensure` | **10** | ⬜ |
| `PAGE-045.04` | 159–219 | تابع | `food_ticket_groups_migrate` | 4 | ⬜ |
| `PAGE-045.05` | 220–222 | ناحیه | `بلوک رویه‌ای 220-222` | 0 | ⬜ |
| `PAGE-045.06` | 223–249 | تابع | `food_ticket_absence_ensure` | 5 | ⬜ |
| `PAGE-045.07` | 251–308 | تابع | `food_ticket_manual_attendance_reconcile_range` | 1 | ⬜ |
| `PAGE-045.08` | 310–313 | تابع | `food_ticket_manual_attendance_reconcile_date` | 0 | ⬜ |
| `PAGE-045.09` | 315–342 | تابع | `food_ticket_absence_load` | 0 | ⬜ |
| `PAGE-045.10` | 344–362 | تابع | `food_ticket_group_absence_locked` | 0 | ⬜ |
| `PAGE-045.11` | 364–436 | تابع | `food_ticket_absence_set` | 1 | ⬜ |
| `PAGE-045.12` | 438–484 | تابع | `food_ticket_absence_clear_group` | 1 | ⬜ |
| `PAGE-045.13` | 486–509 | تابع | `food_ticket_absence_held_count` | 5 | ⬜ |
| `PAGE-045.14` | 511–517 | تابع | `food_ticket_can_override_absence` | 0 | ⬜ |
| `PAGE-045.15` | 518–520 | ناحیه | `بلوک رویه‌ای 518-520` | 0 | ⬜ |
| `PAGE-045.16` | 521–550 | تابع | `food_ticket_groups_load` | 0 | ⬜ |
| `PAGE-045.17` | 552–568 | تابع | `food_ticket_group_find_by_uid` | 0 | ⬜ |
| `PAGE-045.18` | 570–588 | تابع | `food_ticket_group_find_by_card` | 0 | ⬜ |
| `PAGE-045.19` | 590–608 | تابع | `food_ticket_group_resolve` | 0 | ⬜ |
| `PAGE-045.20` | 610–622 | تابع | `food_ticket_group_find_by_card_any` | 0 | ⬜ |
| `PAGE-045.21` | 624–634 | تابع | `food_ticket_group_date_ok` | 0 | ⬜ |
| `PAGE-045.22` | 636–639 | تابع | `food_ticket_group_member_key` | 0 | ⬜ |
| `PAGE-045.23` | 641–659 | تابع | `food_ticket_group_event_ids_for_run` | 0 | ⬜ |
| `PAGE-045.24` | 660–662 | ناحیه | `بلوک رویه‌ای 660-662` | 0 | ⬜ |
| `PAGE-045.25` | 663–735 | تابع | `food_ticket_group_plan` | 0 | ⬜ |
| `PAGE-045.26` | 737–755 | تابع | `food_ticket_group_members` | 0 | ⬜ |
| `PAGE-045.27` | 757–772 | تابع | `food_ticket_user_group_id` | 0 | ⬜ |
| `PAGE-045.28` | 774–795 | تابع | `food_ticket_day_visit_users` | 0 | ⬜ |
| `PAGE-045.29` | 797–835 | تابع | `food_ticket_group_members_for_date` | 0 | ⬜ |
| `PAGE-045.30` | 837–855 | تابع | `food_ticket_day_visit_row` | 0 | ⬜ |
| `PAGE-045.31` | 857–882 | تابع | `food_ticket_visit_change_hhmm` | 0 | ⬜ |
| `PAGE-045.32` | 884–894 | تابع | `food_ticket_visit_change_now` | 0 | ⬜ |
| `PAGE-045.33` | 896–905 | تابع | `food_ticket_visit_change_is_open` | 0 | ⬜ |
| `PAGE-045.34` | 907–915 | تابع | `food_ticket_visit_change_guard` | 0 | ⬜ |
| `PAGE-045.35` | 917–923 | تابع | `food_ticket_can_cancel_day_visit` | 0 | ⬜ |
| `PAGE-045.36` | 925–991 | تابع | `food_ticket_day_visit_state` | 0 | ⬜ |
| `PAGE-045.37` | 993–1114 | تابع | `food_ticket_day_visit_save` | 2 | ⬜ |
| `PAGE-045.38` | 1116–1175 | تابع | `food_ticket_day_visit_clear` | 1 | ⬜ |
| `PAGE-045.39` | 1177–1193 | تابع | `food_ticket_api_cancel_day_visit` | 0 | ⬜ |
| `PAGE-045.40` | 1195–1226 | تابع | `food_ticket_group_ticketed_keys` | 5 | ⬜ |
| `PAGE-045.41` | 1228–1248 | تابع | `food_ticket_group_order_map` | 0 | ⬜ |
| `PAGE-045.42` | 1250–1264 | تابع | `food_ticket_group_food_lookup` | 0 | ⬜ |
| `PAGE-045.43` | 1266–1304 | تابع | `food_ticket_group_overview` | 0 | ⬜ |
| `PAGE-045.44` | 1305–1307 | ناحیه | `بلوک رویه‌ای 1305-1307` | 0 | ⬜ |
| `PAGE-045.45` | 1308–1555 | تابع | `food_ticket_group_try_process` | 2 | ⬜ |
| `PAGE-045.46` | 1557–1620 | تابع | `food_ticket_hold_ticket_for_absence` | 6 | ⬜ |
| `PAGE-045.47` | 1622–1671 | تابع | `food_ticket_release_held_ticket` | 6 | ⬜ |
| `PAGE-045.48` | 1673–1700 | تابع | `food_ticket_events_ensure_hold_status` | **8** | ⬜ |
| `PAGE-045.49` | 1701–1703 | ناحیه | `بلوک رویه‌ای 1701-1703` | 0 | ⬜ |
| `PAGE-045.50` | 1704–1718 | تابع | `food_ticket_group_uid_history_record` | 1 | ⬜ |
| `PAGE-045.51` | 1720–1735 | تابع | `food_ticket_group_uid_history_has` | 0 | ⬜ |
| `PAGE-045.52` | 1737–1802 | تابع | `food_ticket_group_try_unknown` | **10** | ⬜ |
| `PAGE-045.53` | 1803–1806 | ناحیه | `بلوک رویه‌ای 1803-1806` | 0 | ⬜ |
| `PAGE-045.54` | 1807–1811 | تابع | `food_ticket_manual_attendance_today` | 0 | ⬜ |
| `PAGE-045.55` | 1813–1831 | تابع | `food_ticket_manual_attendance_date` | 0 | ⬜ |
| `PAGE-045.56` | 1833–1844 | تابع | `food_ticket_manual_attendance_guest_row` | 0 | ⬜ |
| `PAGE-045.57` | 1846–1929 | تابع | `food_ticket_api_manual_attendance` | 0 | ⬜ |
| `PAGE-045.58` | 1931–1981 | تابع | `food_ticket_manual_attendance_clear_absence` | 1 | ⬜ |
| `PAGE-045.59` | 1983–2228 | تابع | `food_ticket_api_manual_attendance_save` | 2 | ⬜ |
| `PAGE-045.60` | 2230–2318 | تابع | `food_ticket_api_manual_attendance_save_guest` | 0 | ⬜ |
| `PAGE-045.61` | 2320–2396 | تابع | `food_ticket_api_manual_attendance_save_many` | **9** | ⬜ |
| `PAGE-045.62` | 2398–2481 | تابع | `food_ticket_api_groups` | 0 | ⬜ |
| `PAGE-045.63` | 2483–2712 | تابع | `food_ticket_api_save_group` | 5 | ⬜ |
| `PAGE-045.64` | 2713–2717 | ناحیه | `بلوک رویه‌ای 2713-2717` | 0 | ⬜ |
| `PAGE-045.65` | 2718–2736 | تابع | `food_ticket_jalali_label` | 0 | ⬜ |
| `PAGE-045.66` | 2738–2811 | تابع | `food_ticket_api_absence_status` | 0 | ⬜ |
| `PAGE-045.67` | 2813–2878 | تابع | `food_ticket_api_absence_save` | 0 | ⬜ |
| `PAGE-045.68` | 2880–2896 | تابع | `food_ticket_api_absence_clear` | 0 | ⬜ |
| `PAGE-045.69` | 2898–2924 | تابع | `food_ticket_api_delivery` | 0 | ⬜ |
| `PAGE-045.70` | 2926–2955 | تابع | `food_ticket_api_delete_group` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-046</b> — <code>food-ticket-import.php</code> (574 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-046.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `PAGE-046.02` | 18–28 | تابع | `food_ticket_import_aliases` | 0 | ⬜ |
| `PAGE-046.03` | 30–41 | تابع | `food_ticket_import_normalize` | 0 | ⬜ |
| `PAGE-046.04` | 43–49 | تابع | `food_ticket_import_key` | 0 | ⬜ |
| `PAGE-046.05` | 51–55 | تابع | `food_ticket_import_digits` | 0 | ⬜ |
| `PAGE-046.06` | 57–69 | تابع | `food_ticket_import_column_index` | 0 | ⬜ |
| `PAGE-046.07` | 71–96 | تابع | `food_ticket_import_shared_strings` | 1 | ⬜ |
| `PAGE-046.08` | 98–171 | تابع | `food_ticket_import_read_xlsx` | 1 | ⬜ |
| `PAGE-046.09` | 173–204 | تابع | `food_ticket_import_csv_delimiter_counts` | 0 | ⬜ |
| `PAGE-046.10` | 206–242 | تابع | `food_ticket_import_read_csv` | 2 | ⬜ |
| `PAGE-046.11` | 244–276 | تابع | `food_ticket_import_map_columns` | 0 | ⬜ |
| `PAGE-046.12` | 278–321 | تابع | `food_ticket_import_parse_file` | 0 | ⬜ |
| `PAGE-046.13` | 323–354 | تابع | `food_ticket_import_validate_row` | 0 | ⬜ |
| `PAGE-046.14` | 356–476 | تابع | `food_ticket_import_apply` | 2 | ⬜ |
| `PAGE-046.15` | 478–508 | تابع | `food_ticket_import_summary` | 0 | ⬜ |
| `PAGE-046.16` | 510–573 | تابع | `food_ticket_employees_import_handle` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-047</b> — <code>food-ticket-netprint.php</code> (1,299 خط، 35 آدرس، بازبینی‌شده 0/35)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-047.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `PAGE-047.02` | 17–75 | تابع | `food_ticket_np_settings` | 1 | ⬜ |
| `PAGE-047.03` | 77–95 | تابع | `food_ticket_np_find_font` | 0 | ⬜ |
| `PAGE-047.04` | 96–98 | ناحیه | `بلوک رویه‌ای 96-98` | 0 | ⬜ |
| `PAGE-047.05` | 99–137 | تابع | `food_ticket_np_tables` | 0 | ⬜ |
| `PAGE-047.06` | 139–152 | تابع | `food_ticket_np_codepoints` | 0 | ⬜ |
| `PAGE-047.07` | 154–204 | تابع | `food_ticket_np_shape` | 0 | ⬜ |
| `PAGE-047.08` | 206–222 | تابع | `food_ticket_np_class` | 0 | ⬜ |
| `PAGE-047.09` | 224–288 | تابع | `food_ticket_np_visual` | 0 | ⬜ |
| `PAGE-047.10` | 289–291 | ناحیه | `بلوک رویه‌ای 289-291` | 0 | ⬜ |
| `PAGE-047.11` | 292–301 | تابع | `food_ticket_np_measure` | 0 | ⬜ |
| `PAGE-047.12` | 303–417 | تابع | `food_ticket_np_render` | 0 | ⬜ |
| `PAGE-047.13` | 419–431 | تابع | `food_ticket_print_trim_info` | 0 | ⬜ |
| `PAGE-047.14` | 433–523 | تابع | `food_ticket_np_trim_edges` | 0 | ⬜ |
| `PAGE-047.15` | 525–535 | تابع | `food_ticket_np_trim_top` | 0 | ⬜ |
| `PAGE-047.16` | 537–542 | تابع | `food_ticket_np_json_file` | 0 | ⬜ |
| `PAGE-047.17` | 544–602 | تابع | `food_ticket_np_write_json` | 3 | ⬜ |
| `PAGE-047.18` | 604–675 | تابع | `food_ticket_np_align_state` | 0 | ⬜ |
| `PAGE-047.19` | 677–712 | تابع | `food_ticket_np_reverse_feed` | 0 | ⬜ |
| `PAGE-047.20` | 714–722 | تابع | `food_ticket_print_align_info` | 0 | ⬜ |
| `PAGE-047.21` | 724–834 | تابع | `food_ticket_np_escpos` | 0 | ⬜ |
| `PAGE-047.22` | 836–854 | تابع | `food_ticket_np_payload` | 2 | ⬜ |
| `PAGE-047.23` | 856–896 | تابع | `food_ticket_np_send` | 3 | ⬜ |
| `PAGE-047.24` | 897–899 | ناحیه | `بلوک رویه‌ای 897-899` | 0 | ⬜ |
| `PAGE-047.25` | 900–933 | تابع | `food_ticket_np_find_font_for` | 0 | ⬜ |
| `PAGE-047.26` | 935–945 | تابع | `food_ticket_np_metrics` | 0 | ⬜ |
| `PAGE-047.27` | 947–978 | تابع | `food_ticket_np_wrap` | 0 | ⬜ |
| `PAGE-047.28` | 980–999 | تابع | `food_ticket_np_layout` | 0 | ⬜ |
| `PAGE-047.29` | 1001–1143 | تابع | `food_ticket_np_render_layout` | 2 | ⬜ |
| `PAGE-047.30` | 1145–1188 | تابع | `food_ticket_np_target` | 4 | ⬜ |
| `PAGE-047.31` | 1190–1235 | تابع | `food_ticket_np_calibration_image` | 2 | ⬜ |
| `PAGE-047.32` | 1237–1266 | تابع | `food_ticket_np_calibration_print` | 2 | ⬜ |
| `PAGE-047.33` | 1268–1283 | تابع | `food_ticket_np_image_payload` | 2 | ⬜ |
| `PAGE-047.34` | 1285–1291 | تابع | `food_ticket_np_send_event` | 0 | ⬜ |
| `PAGE-047.35` | 1293–1298 | تابع | `food_ticket_np_send_text` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-048</b> — <code>food-ticket-order-source.php</code> (116 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-048.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-048.02` | 4–52 | تابع | `food_order_internal_map` | 0 | ⬜ |
| `PAGE-048.03` | 54–71 | تابع | `food_order_internal_food_for_employee` | 0 | ⬜ |
| `PAGE-048.04` | 73–115 | تابع | `food_order_internal_items` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-049</b> — <code>food-ticket-runtime.php</code> (1,368 خط، 35 آدرس، بازبینی‌شده 0/35)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-049.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-049.02` | 4–24 | تابع | `food_ticket_setting` | 0 | ⬜ |
| `PAGE-049.03` | 26–32 | تابع | `food_ticket_set_setting` | 6 | ⬜ |
| `PAGE-049.04` | 34–47 | تابع | `food_ticket_meal_windows` | 0 | ⬜ |
| `PAGE-049.05` | 49–64 | تابع | `food_ticket_active_meal` | 0 | ⬜ |
| `PAGE-049.06` | 66–94 | تابع | `food_ticket_template_default` | 0 | ⬜ |
| `PAGE-049.07` | 96–110 | تابع | `food_ticket_template` | 0 | ⬜ |
| `PAGE-049.08` | 112–124 | تابع | `food_ticket_save_template` | 1 | ⬜ |
| `PAGE-049.09` | 126–210 | تابع | `food_ticket_render_from_template` | 0 | ⬜ |
| `PAGE-049.10` | 212–250 | تابع | `food_ticket_print_log` | 1 | ⬜ |
| `PAGE-049.11` | 252–255 | تابع | `food_ticket_new_job_id` | 0 | ⬜ |
| `PAGE-049.12` | 257–290 | تابع | `food_ticket_enqueue_print` | 1 | ⬜ |
| `PAGE-049.13` | 292–295 | تابع | `food_ticket_can_delete_source_after_print` | 0 | ⬜ |
| `PAGE-049.14` | 297–328 | تابع | `food_ticket_prepare_queued_food` | 0 | ⬜ |
| `PAGE-049.15` | 330–343 | تابع | `food_ticket_apply_queued_food` | 1 | ⬜ |
| `PAGE-049.16` | 345–353 | تابع | `food_ticket_verify_queued_food` | 0 | ⬜ |
| `PAGE-049.17` | 355–537 | تابع | `food_ticket_print_group_run` | 7 | ⬜ |
| `PAGE-049.18` | 539–693 | تابع | `food_ticket_process_print_queue` | **11** | ⬜ |
| `PAGE-049.19` | 695–702 | تابع | `food_ticket_fa_digits` | 0 | ⬜ |
| `PAGE-049.20` | 704–777 | تابع | `food_ticket_graphic_rows` | 0 | ⬜ |
| `PAGE-049.21` | 779–813 | تابع | `food_ticket_template_logo_base64` | 2 | ⬜ |
| `PAGE-049.22` | 815–1013 | تابع | `food_ticket_send_windows_graphic` | **13** | ⬜ |
| `PAGE-049.23` | 1015–1069 | تابع | `food_ticket_send_raw_payload` | 0 | ⬜ |
| `PAGE-049.24` | 1070–1075 | ناحیه | `بلوک رویه‌ای 1070-1075` | 0 | ⬜ |
| `PAGE-049.25` | 1076–1128 | تابع | `food_ticket_table_has_column` | 4 | ⬜ |
| `PAGE-049.26` | 1130–1142 | تابع | `food_ticket_delivery_supported` | 0 | ⬜ |
| `PAGE-049.27` | 1144–1171 | تابع | `food_ticket_events_ensure_delivery` | 4 | ⬜ |
| `PAGE-049.28` | 1173–1191 | تابع | `food_ticket_source_delete_supported` | 0 | ⬜ |
| `PAGE-049.29` | 1193–1225 | تابع | `food_ticket_events_ensure_source_delete` | **14** | ⬜ |
| `PAGE-049.30` | 1226–1227 | ناحیه | `بلوک رویه‌ای 1226-1227` | 0 | ⬜ |
| `PAGE-049.31` | 1228–1241 | تابع | `food_ticket_mark_delivered_on_print` | 1 | ⬜ |
| `PAGE-049.32` | 1243–1279 | تابع | `food_ticket_deliver_event` | 5 | ⬜ |
| `PAGE-049.33` | 1281–1312 | تابع | `food_ticket_undeliver_event` | 5 | ⬜ |
| `PAGE-049.34` | 1314–1345 | تابع | `food_ticket_status_counts` | 5 | ⬜ |
| `PAGE-049.35` | 1347–1367 | تابع | `food_ticket_self_test_logic` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-050</b> — <code>food-ticket-templates.php</code> (2,362 خط، 63 آدرس، بازبینی‌شده 0/63)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-050.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `PAGE-050.02` | 27–52 | تابع | `food_ticket_tpl_catalog` | 0 | ⬜ |
| `PAGE-050.03` | 54–67 | تابع | `food_ticket_tpl_font_files` | 0 | ⬜ |
| `PAGE-050.04` | 69–108 | تابع | `food_ticket_tpl_find_font` | 0 | ⬜ |
| `PAGE-050.05` | 109–111 | ناحیه | `بلوک رویه‌ای 109-111` | 0 | ⬜ |
| `PAGE-050.06` | 112–141 | تابع | `food_ticket_tpl_ensure` | **9** | ⬜ |
| `PAGE-050.07` | 143–149 | تابع | `food_ticket_tpl_num` | 0 | ⬜ |
| `PAGE-050.08` | 151–220 | تابع | `food_ticket_tpl_normalize` | 0 | ⬜ |
| `PAGE-050.09` | 222–269 | تابع | `food_ticket_tpl_default_template` | 0 | ⬜ |
| `PAGE-050.10` | 271–283 | تابع | `food_ticket_tpl_default_row` | 0 | ⬜ |
| `PAGE-050.11` | 285–298 | تابع | `food_ticket_tpl_row_decode` | 0 | ⬜ |
| `PAGE-050.12` | 300–308 | تابع | `food_ticket_tpl_list` | 0 | ⬜ |
| `PAGE-050.13` | 310–320 | تابع | `food_ticket_tpl_get` | 0 | ⬜ |
| `PAGE-050.14` | 322–330 | تابع | `food_ticket_tpl_force` | 0 | ⬜ |
| `PAGE-050.15` | 332–360 | تابع | `food_ticket_tpl_active` | 0 | ⬜ |
| `PAGE-050.16` | 362–373 | تابع | `food_ticket_tpl_seed_if_empty` | 0 | ⬜ |
| `PAGE-050.17` | 375–427 | تابع | `food_ticket_tpl_save` | 4 | ⬜ |
| `PAGE-050.18` | 429–446 | تابع | `food_ticket_tpl_activate` | 4 | ⬜ |
| `PAGE-050.19` | 448–474 | تابع | `food_ticket_tpl_delete` | 0 | ⬜ |
| `PAGE-050.20` | 475–477 | ناحیه | `بلوک رویه‌ای 475-477` | 0 | ⬜ |
| `PAGE-050.21` | 478–489 | تابع | `food_ticket_tpl_setting` | 0 | ⬜ |
| `PAGE-050.22` | 491–498 | تابع | `food_ticket_tpl_system_name` | 0 | ⬜ |
| `PAGE-050.23` | 500–514 | تابع | `food_ticket_tpl_logo_setting` | 0 | ⬜ |
| `PAGE-050.24` | 516–550 | تابع | `food_ticket_tpl_logo_bytes` | 2 | ⬜ |
| `PAGE-050.25` | 551–553 | ناحیه | `بلوک رویه‌ای 551-553` | 0 | ⬜ |
| `PAGE-050.26` | 554–557 | تابع | `food_ticket_tpl_fa` | 0 | ⬜ |
| `PAGE-050.27` | 559–586 | تابع | `food_ticket_tpl_department` | 4 | ⬜ |
| `PAGE-050.28` | 588–635 | تابع | `food_ticket_tpl_values` | 0 | ⬜ |
| `PAGE-050.29` | 637–644 | تابع | `food_ticket_tpl_qr_payload` | 0 | ⬜ |
| `PAGE-050.30` | 645–647 | ناحیه | `بلوک رویه‌ای 645-647` | 0 | ⬜ |
| `PAGE-050.31` | 648–995 | تابع | `food_ticket_tpl_qr_matrix` | 3 | ⬜ |
| `PAGE-050.32` | 996–998 | ناحیه | `بلوک رویه‌ای 996-998` | 0 | ⬜ |
| `PAGE-050.33` | 999–1014 | تابع | `food_ticket_tpl_text_height_mm` | 0 | ⬜ |
| `PAGE-050.34` | 1016–1299 | تابع | `food_ticket_tpl_model` | 7 | ⬜ |
| `PAGE-050.35` | 1301–1320 | تابع | `food_ticket_tpl_first_ink_mm` | 0 | ⬜ |
| `PAGE-050.36` | 1322–1342 | تابع | `food_ticket_tpl_last_ink_mm` | 0 | ⬜ |
| `PAGE-050.37` | 1344–1381 | تابع | `food_ticket_tpl_fit_bottom` | 0 | ⬜ |
| `PAGE-050.38` | 1383–1417 | تابع | `food_ticket_tpl_trim_top` | 0 | ⬜ |
| `PAGE-050.39` | 1419–1427 | تابع | `food_ticket_tpl_last_info` | 0 | ⬜ |
| `PAGE-050.40` | 1429–1433 | تابع | `food_ticket_tpl_log_suffix` | 0 | ⬜ |
| `PAGE-050.41` | 1434–1436 | ناحیه | `بلوک رویه‌ای 1434-1436` | 0 | ⬜ |
| `PAGE-050.42` | 1437–1466 | تابع | `food_ticket_tpl_net_cfg` | 1 | ⬜ |
| `PAGE-050.43` | 1468–1480 | تابع | `food_ticket_tpl_head_dots` | 0 | ⬜ |
| `PAGE-050.44` | 1482–1523 | تابع | `food_ticket_tpl_dither` | 0 | ⬜ |
| `PAGE-050.45` | 1525–1684 | تابع | `food_ticket_tpl_render_gd` | 2 | ⬜ |
| `PAGE-050.46` | 1686–1699 | تابع | `food_ticket_tpl_binarize` | 0 | ⬜ |
| `PAGE-050.47` | 1701–1734 | تابع | `food_ticket_tpl_network_payload` | 0 | ⬜ |
| `PAGE-050.48` | 1736–1741 | تابع | `food_ticket_tpl_print_network` | 0 | ⬜ |
| `PAGE-050.49` | 1743–1762 | تابع | `food_ticket_tpl_preview` | 0 | ⬜ |
| `PAGE-050.50` | 1763–1765 | ناحیه | `بلوک رویه‌ای 1763-1765` | 0 | ⬜ |
| `PAGE-050.51` | 1766–1982 | تابع | `food_ticket_tpl_windows_script` | 6 | ⬜ |
| `PAGE-050.52` | 1984–2044 | تابع | `food_ticket_tpl_print_windows` | 7 | ⬜ |
| `PAGE-050.53` | 2045–2047 | ناحیه | `بلوک رویه‌ای 2045-2047` | 0 | ⬜ |
| `PAGE-050.54` | 2048–2055 | تابع | `food_ticket_tpl_asset_url` | 0 | ⬜ |
| `PAGE-050.55` | 2057–2069 | تابع | `food_ticket_tpl_brand_payload` | 0 | ⬜ |
| `PAGE-050.56` | 2071–2136 | تابع | `food_ticket_tpl_group_ensure_title` | 0 | ⬜ |
| `PAGE-050.57` | 2138–2160 | تابع | `food_ticket_tpl_group_template` | 0 | ⬜ |
| `PAGE-050.58` | 2162–2177 | تابع | `food_ticket_tpl_list_payload` | 0 | ⬜ |
| `PAGE-050.59` | 2179–2186 | تابع | `food_ticket_group_print_mode` | 0 | ⬜ |
| `PAGE-050.60` | 2188–2206 | تابع | `food_ticket_tpl_sample_group_event` | 0 | ⬜ |
| `PAGE-050.61` | 2208–2223 | تابع | `food_ticket_tpl_sample_event` | 0 | ⬜ |
| `PAGE-050.62` | 2225–2265 | تابع | `food_ticket_tpl_handle_logo_upload` | **9** | ⬜ |
| `PAGE-050.63` | 2267–2361 | تابع | `food_ticket_templates_api` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-051</b> — <code>food-ticket-web-host.php</code> (201 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-051.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `PAGE-051.02` | 21–44 | تابع | `food_ticket_web_host_bootstrap` | 0 | ⬜ |
| `PAGE-051.03` | 46–200 | تابع | `food_ticket_web_host_html` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-052</b> — <code>food-ticket-window.php</code> (336 خط، 19 آدرس، بازبینی‌شده 0/19)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-052.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `PAGE-052.02` | 20–34 | تابع | `food_ticket_window_timezone` | 0 | ⬜ |
| `PAGE-052.03` | 35–37 | ناحیه | `بلوک رویه‌ای 35-37` | 0 | ⬜ |
| `PAGE-052.04` | 38–52 | تابع | `food_ticket_window_defaults` | 0 | ⬜ |
| `PAGE-052.05` | 53–55 | ناحیه | `بلوک رویه‌ای 53-55` | 0 | ⬜ |
| `PAGE-052.06` | 56–79 | تابع | `food_ticket_window_days` | 0 | ⬜ |
| `PAGE-052.07` | 80–82 | ناحیه | `بلوک رویه‌ای 80-82` | 0 | ⬜ |
| `PAGE-052.08` | 83–94 | تابع | `food_ticket_window_time` | 0 | ⬜ |
| `PAGE-052.09` | 95–97 | ناحیه | `بلوک رویه‌ای 95-97` | 0 | ⬜ |
| `PAGE-052.10` | 98–129 | تابع | `food_ticket_window_is_holiday` | 0 | ⬜ |
| `PAGE-052.11` | 130–132 | ناحیه | `بلوک رویه‌ای 130-132` | 0 | ⬜ |
| `PAGE-052.12` | 133–140 | تابع | `food_ticket_window_day_is_active` | 0 | ⬜ |
| `PAGE-052.13` | 141–143 | ناحیه | `بلوک رویه‌ای 141-143` | 0 | ⬜ |
| `PAGE-052.14` | 144–227 | تابع | `food_ticket_window_state` | 0 | ⬜ |
| `PAGE-052.15` | 228–230 | ناحیه | `بلوک رویه‌ای 228-230` | 0 | ⬜ |
| `PAGE-052.16` | 231–305 | تابع | `food_ticket_window_save` | 1 | ⬜ |
| `PAGE-052.17` | 306–308 | ناحیه | `بلوک رویه‌ای 306-308` | 0 | ⬜ |
| `PAGE-052.18` | 309–334 | تابع | `food_ticket_window_next_open` | 0 | ⬜ |
| `PAGE-052.19` | 335–336 | ناحیه | `بلوک رویه‌ای 335-336` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-053</b> — <code>food-ticket.php</code> (5,676 خط، 130 آدرس، بازبینی‌شده 0/130)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-053.01` | 1–52 | ناحیه | `بلوک رویه‌ای 1-52` | 0 | ⬜ |
| `PAGE-053.02` | 53–56 | تابع | `food_ticket_panel_version` | 0 | ⬜ |
| `PAGE-053.03` | 58–61 | تابع | `food_ticket_is_primary_admin` | 0 | ⬜ |
| `PAGE-053.04` | 63–66 | تابع | `food_ticket_is_support_manager` | 0 | ⬜ |
| `PAGE-053.05` | 68–74 | تابع | `food_ticket_is_allowed` | 0 | ⬜ |
| `PAGE-053.06` | 76–115 | تابع | `food_ticket_route_edit_permission` | 1 | ⬜ |
| `PAGE-053.07` | 117–121 | تابع | `food_ticket_route_edit_always` | 1 | ⬜ |
| `PAGE-053.08` | 123–264 | تابع | `food_ticket_route_allowed` | 2 | ⬜ |
| `PAGE-053.09` | 266–274 | تابع | `require_food_ticket_access` | 0 | ⬜ |
| `PAGE-053.10` | 276–287 | تابع | `food_ticket_web_url` | 0 | ⬜ |
| `PAGE-053.11` | 289–297 | تابع | `food_ticket_windows_service_base_url` | 0 | ⬜ |
| `PAGE-053.12` | 299–312 | تابع | `food_ticket_sso_token` | 0 | ⬜ |
| `PAGE-053.13` | 314–336 | تابع | `food_ticket_sso_consume_jti` | 7 | ⬜ |
| `PAGE-053.14` | 338–349 | تابع | `food_ticket_main_url` | 0 | ⬜ |
| `PAGE-053.15` | 351–368 | تابع | `food_ticket_brand_params` | 0 | ⬜ |
| `PAGE-053.16` | 370–385 | تابع | `food_ticket_resolve_path` | 0 | ⬜ |
| `PAGE-053.17` | 387–397 | تابع | `food_ticket_attendance_default_path` | 0 | ⬜ |
| `PAGE-053.18` | 399–402 | تابع | `food_ticket_attendance_factory_password` | 0 | ⬜ |
| `PAGE-053.19` | 404–463 | تابع | `food_ticket_config` | 5 | ⬜ |
| `PAGE-053.20` | 466–478 | تابع | `food_ticket_normalize_printer_mode` | 0 | ⬜ |
| `PAGE-053.21` | 480–497 | تابع | `food_ticket_normalize_printer_host` | 0 | ⬜ |
| `PAGE-053.22` | 499–509 | تابع | `food_ticket_printer_label` | 0 | ⬜ |
| `PAGE-053.23` | 511–532 | تابع | `food_ticket_ensure_printer_mode_column` | 4 | ⬜ |
| `PAGE-053.24` | 534–542 | تابع | `food_ticket_installed` | 0 | ⬜ |
| `PAGE-053.25` | 544–547 | تابع | `food_ticket_key` | 0 | ⬜ |
| `PAGE-053.26` | 549–558 | تابع | `food_ticket_encrypt` | 0 | ⬜ |
| `PAGE-053.27` | 560–571 | تابع | `food_ticket_decrypt` | 0 | ⬜ |
| `PAGE-053.28` | 573–621 | تابع | `food_ticket_attendance_password` | 4 | ⬜ |
| `PAGE-053.29` | 623–639 | تابع | `food_ticket_attendance_password_source` | 4 | ⬜ |
| `PAGE-053.30` | 641–647 | تابع | `food_ticket_ident` | 0 | ⬜ |
| `PAGE-053.31` | 649–669 | تابع | `food_ticket_odbc_pool` | 0 | ⬜ |
| `PAGE-053.32` | 671–682 | تابع | `food_ticket_odbc_release` | 1 | ⬜ |
| `PAGE-053.33` | 684–713 | تابع | `food_ticket_odbc_pool_handle` | 0 | ⬜ |
| `PAGE-053.34` | 715–771 | تابع | `food_ticket_odbc` | 1 | ⬜ |
| `PAGE-053.35` | 773–776 | تابع | `food_ticket_is_ps_access` | 0 | ⬜ |
| `PAGE-053.36` | 778–791 | تابع | `food_ticket_access_close` | 1 | ⬜ |
| `PAGE-053.37` | 793–1002 | تابع | `food_ticket_access_rows_powershell` | **13** | ⬜ |
| `PAGE-053.38` | 1005–1009 | تابع | `food_ticket_access_delete_powershell` | 0 | ⬜ |
| `PAGE-053.39` | 1011–1127 | تابع | `food_ticket_access_delete_powershell_ex` | **13** | ⬜ |
| `PAGE-053.40` | 1129–1132 | تابع | `food_ticket_normalize_column` | 0 | ⬜ |
| `PAGE-053.41` | 1134–1143 | تابع | `food_ticket_row_value` | 0 | ⬜ |
| `PAGE-053.42` | 1146–1205 | تابع | `food_ticket_access_rows` | **8** | ⬜ |
| `PAGE-053.43` | 1207–1212 | تابع | `food_ticket_normalize_digits` | 0 | ⬜ |
| `PAGE-053.44` | 1214–1245 | تابع | `food_ticket_iso_date_input` | 0 | ⬜ |
| `PAGE-053.45` | 1247–1301 | تابع | `food_ticket_jalali_to_gregorian` | 0 | ⬜ |
| `PAGE-053.46` | 1303–1402 | تابع | `food_ticket_parse_date` | 0 | ⬜ |
| `PAGE-053.47` | 1404–1453 | تابع | `food_ticket_parse_time` | 0 | ⬜ |
| `PAGE-053.48` | 1455–1509 | تابع | `food_ticket_source_key` | 0 | ⬜ |
| `PAGE-053.49` | 1511–1537 | تابع | `food_ticket_code` | 0 | ⬜ |
| `PAGE-053.50` | 1539–1542 | تابع | `food_ticket_digits` | 0 | ⬜ |
| `PAGE-053.51` | 1544–1559 | تابع | `food_ticket_find_user` | 0 | ⬜ |
| `PAGE-053.52` | 1562–1586 | تابع | `food_ticket_guest_cards` | 4 | ⬜ |
| `PAGE-053.53` | 1588–1599 | تابع | `food_ticket_card_key` | 0 | ⬜ |
| `PAGE-053.54` | 1601–1618 | تابع | `food_ticket_card_equals` | 0 | ⬜ |
| `PAGE-053.55` | 1620–1646 | تابع | `food_ticket_guest_card` | 4 | ⬜ |
| `PAGE-053.56` | 1648–1651 | تابع | `food_ticket_is_guest` | 0 | ⬜ |
| `PAGE-053.57` | 1653–1658 | تابع | `food_ticket_existing_source` | 0 | ⬜ |
| `PAGE-053.58` | 1660–1665 | تابع | `food_ticket_existing_ticket` | 0 | ⬜ |
| `PAGE-053.59` | 1667–1691 | تابع | `food_ticket_normalize_event_type` | 1 | ⬜ |
| `PAGE-053.60` | 1693–1701 | تابع | `food_ticket_normalize_print_status` | 0 | ⬜ |
| `PAGE-053.61` | 1703–1720 | تابع | `food_ticket_event_meal_type_supported` | 0 | ⬜ |
| `PAGE-053.62` | 1722–1751 | تابع | `food_ticket_add_event` | 6 | ⬜ |
| `PAGE-053.63` | 1753–1762 | تابع | `food_ticket_update_event` | 1 | ⬜ |
| `PAGE-053.64` | 1764–1833 | تابع | `food_ticket_source_delete_where` | 0 | ⬜ |
| `PAGE-053.65` | 1835–1875 | تابع | `food_ticket_source_primary_key` | 0 | ⬜ |
| `PAGE-053.66` | 1877–1899 | تابع | `food_ticket_source_type_kind` | 0 | ⬜ |
| `PAGE-053.67` | 1901–1916 | تابع | `food_ticket_source_is_temporal` | 0 | ⬜ |
| `PAGE-053.68` | 1918–1933 | تابع | `food_ticket_source_time_literal` | 0 | ⬜ |
| `PAGE-053.69` | 1935–1950 | تابع | `food_ticket_source_sql_literal` | 0 | ⬜ |
| `PAGE-053.70` | 1952–1967 | تابع | `food_ticket_source_column_sql_literal` | 0 | ⬜ |
| `PAGE-053.71` | 1969–1998 | تابع | `food_ticket_source_delete_candidates` | 0 | ⬜ |
| `PAGE-053.72` | 2000–2059 | تابع | `food_ticket_source_minimal_where` | 0 | ⬜ |
| `PAGE-053.73` | 2061–2155 | تابع | `food_ticket_delete_source` | **9** | ⬜ |
| `PAGE-053.74` | 2157–2187 | تابع | `food_ticket_mark_source_delete` | 6 | ⬜ |
| `PAGE-053.75` | 2189–2210 | تابع | `food_ticket_text` | 0 | ⬜ |
| `PAGE-053.76` | 2211–2216 | ناحیه | `بلوک رویه‌ای 2211-2216` | 0 | ⬜ |
| `PAGE-053.77` | 2217–2264 | تابع | `food_ticket_list_windows_printers` | 7 | ⬜ |
| `PAGE-053.78` | 2266–2284 | تابع | `food_ticket_send_to_printer` | 0 | ⬜ |
| `PAGE-053.79` | 2286–2341 | تابع | `food_ticket_send_windows` | 7 | ⬜ |
| `PAGE-053.80` | 2343–2366 | تابع | `food_ticket_print_event` | 1 | ⬜ |
| `PAGE-053.81` | 2368–2375 | تابع | `food_ticket_process_batch` | 0 | ⬜ |
| `PAGE-053.82` | 2377–2579 | تابع | `food_ticket_cleanup_source_rows` | **14** | ⬜ |
| `PAGE-053.83` | 2581–2633 | تابع | `food_ticket_retry_failed` | **13** | ⬜ |
| `PAGE-053.84` | 2635–2650 | تابع | `food_ticket_worker_heartbeat` | 1 | ⬜ |
| `PAGE-053.85` | 2652–2688 | تابع | `food_ticket_test_connections` | 0 | ⬜ |
| `PAGE-053.86` | 2690–2757 | تابع | `food_ticket_save_config` | 6 | ⬜ |
| `PAGE-053.87` | 2759–2782 | تابع | `food_ticket_handle_post` | 0 | ⬜ |
| `PAGE-053.88` | 2784–2792 | تابع | `food_ticket_label` | 0 | ⬜ |
| `PAGE-053.89` | 2794–2944 | تابع | `food_ticket_cp1256_to_utf8` | 6 | ⬜ |
| `PAGE-053.90` | 2946–2992 | تابع | `food_ticket_utf8` | 2 | ⬜ |
| `PAGE-053.91` | 2994–3015 | تابع | `food_ticket_api_json` | 3 | ⬜ |
| `PAGE-053.92` | 3017–3025 | تابع | `food_ticket_api_body` | 0 | ⬜ |
| `PAGE-053.93` | 3027–3054 | تابع | `food_ticket_api_printer_state` | 0 | ⬜ |
| `PAGE-053.94` | 3056–3102 | تابع | `food_ticket_api_print_diag` | 2 | ⬜ |
| `PAGE-053.95` | 3104–3198 | تابع | `food_ticket_api_config` | 0 | ⬜ |
| `PAGE-053.96` | 3199–3204 | ناحیه | `بلوک رویه‌ای 3199-3204` | 0 | ⬜ |
| `PAGE-053.97` | 3205–3269 | تابع | `food_ticket_api_guest_requests_report` | 0 | ⬜ |
| `PAGE-053.98` | 3270–3275 | ناحیه | `بلوک رویه‌ای 3270-3275` | 0 | ⬜ |
| `PAGE-053.99` | 3276–3283 | تابع | `food_ticket_aggregate_last_name` | 0 | ⬜ |
| `PAGE-053.100` | 3284–3293 | تابع | `food_ticket_aggregate_sort_people` | 0 | ⬜ |
| `PAGE-053.101` | 3294–3443 | تابع | `food_ticket_aggregate_diagnostics` | 6 | ⬜ |
| `PAGE-053.102` | 3445–3605 | تابع | `food_ticket_api_aggregate_report` | 1 | ⬜ |
| `PAGE-053.103` | 3607–3621 | تابع | `food_ticket_aggregate_columns` | 0 | ⬜ |
| `PAGE-053.104` | 3623–3680 | تابع | `food_ticket_aggregate_export` | 5 | ⬜ |
| `PAGE-053.105` | 3682–3703 | تابع | `food_ticket_aggregate_row` | 0 | ⬜ |
| `PAGE-053.106` | 3705–3812 | تابع | `food_ticket_api_absent` | 0 | ⬜ |
| `PAGE-053.107` | 3814–3923 | تابع | `food_ticket_api_monitoring` | 5 | ⬜ |
| `PAGE-053.108` | 3926–3938 | تابع | `food_ticket_date_keys_for_iso` | 0 | ⬜ |
| `PAGE-053.109` | 3941–3967 | تابع | `food_ticket_order_date_key` | 0 | ⬜ |
| `PAGE-053.110` | 3969–3981 | تابع | `food_ticket_api_orders` | 0 | ⬜ |
| `PAGE-053.111` | 3983–4087 | تابع | `food_ticket_ensure_employee_local_accounts` | 6 | ⬜ |
| `PAGE-053.112` | 4089–4110 | تابع | `food_ticket_api_employees` | 0 | ⬜ |
| `PAGE-053.113` | 4112–4115 | تابع | `food_ticket_normalize_guest_card` | 0 | ⬜ |
| `PAGE-053.114` | 4117–4136 | تابع | `food_ticket_api_guest_cards` | 1 | ⬜ |
| `PAGE-053.115` | 4138–4145 | تابع | `food_ticket_sync_guest_card_config` | 1 | ⬜ |
| `PAGE-053.116` | 4147–4221 | تابع | `food_ticket_api_save_employee` | 1 | ⬜ |
| `PAGE-053.117` | 4223–4244 | تابع | `food_ticket_api_save_guest_card` | 1 | ⬜ |
| `PAGE-053.118` | 4246–4269 | تابع | `food_ticket_api_delete_guest_card` | 1 | ⬜ |
| `PAGE-053.119` | 4271–4281 | تابع | `food_ticket_save_guest_cap_mode` | 0 | ⬜ |
| `PAGE-053.120` | 4283–4439 | تابع | `food_ticket_api_save_config` | 1 | ⬜ |
| `PAGE-053.121` | 4440–4446 | ناحیه | `بلوک رویه‌ای 4440-4446` | 0 | ⬜ |
| `PAGE-053.122` | 4447–4533 | تابع | `food_ticket_browse_list` | 3 | ⬜ |
| `PAGE-053.123` | 4535–4559 | تابع | `food_ticket_php_native_browse` | 2 | ⬜ |
| `PAGE-053.124` | 4561–4568 | تابع | `food_ticket_windows_browse` | 0 | ⬜ |
| `PAGE-053.125` | 4571–4639 | تابع | `food_ticket_api_health` | 0 | ⬜ |
| `PAGE-053.126` | 4641–4733 | تابع | `food_ticket_api_self_test` | 0 | ⬜ |
| `PAGE-053.127` | 4735–4803 | تابع | `food_ticket_api_upload_access_file` | **15** | ⬜ |
| `PAGE-053.128` | 4805–5479 | تابع | `food_ticket_api_handle` | **14** | ⬜ |
| `PAGE-053.129` | 5481–5495 | تابع | `food_ticket_render_safe_replace` | 0 | ⬜ |
| `PAGE-053.130` | 5497–5675 | تابع | `food_ticket_render_page` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-054</b> — <code>global-search.php</code> (122 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-054.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `PAGE-054.02` | 16–29 | تابع | `global_search_normalize` | 0 | ⬜ |
| `PAGE-054.03` | 31–73 | تابع | `global_search_catalog` | 0 | ⬜ |
| `PAGE-054.04` | 75–121 | تابع | `global_search_sections` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-055</b> — <code>governance.php</code> (522 خط، 29 آدرس، بازبینی‌شده 0/29)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-055.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-055.02` | 6–9 | تابع | `gov_post` | 2 | ⬜ |
| `PAGE-055.03` | 11–14 | تابع | `gov_choice` | 0 | ⬜ |
| `PAGE-055.04` | 16–19 | تابع | `gov_change_number` | 0 | ⬜ |
| `PAGE-055.05` | 21–24 | تابع | `gov_problem_number` | 0 | ⬜ |
| `PAGE-055.06` | 26–29 | تابع | `gov_risk_label` | 0 | ⬜ |
| `PAGE-055.07` | 31–34 | تابع | `gov_change_status_label` | 0 | ⬜ |
| `PAGE-055.08` | 36–39 | تابع | `gov_problem_status_label` | 0 | ⬜ |
| `PAGE-055.09` | 41–52 | تابع | `gov_parse_datetime` | 0 | ⬜ |
| `PAGE-055.10` | 54–67 | تابع | `gov_scope` | 5 | ⬜ |
| `PAGE-055.11` | 69–77 | تابع | `gov_fetch_ticket` | 0 | ⬜ |
| `PAGE-055.12` | 79–88 | تابع | `gov_can_view_ticket` | 0 | ⬜ |
| `PAGE-055.13` | 90–95 | تابع | `gov_fetch_change` | 0 | ⬜ |
| `PAGE-055.14` | 97–102 | تابع | `gov_fetch_problem` | 0 | ⬜ |
| `PAGE-055.15` | 104–113 | تابع | `gov_can_view_record` | 0 | ⬜ |
| `PAGE-055.16` | 115–118 | تابع | `gov_datetime_input` | 0 | ⬜ |
| `PAGE-055.17` | 120–131 | تابع | `gov_ticket_options` | 0 | ⬜ |
| `PAGE-055.18` | 133–143 | تابع | `gov_notify_linked_tickets` | 5 | ⬜ |
| `PAGE-055.19` | 145–163 | تابع | `gov_header` | 0 | ⬜ |
| `PAGE-055.20` | 165–169 | تابع | `gov_footer` | 0 | ⬜ |
| `PAGE-055.21` | 170–206 | ناحیه | `بلوک رویه‌ای 170-206` | 0 | ⬜ |
| `PAGE-055.22` | 207–244 | ناحیه | `مسیر: create_change` | 2 | ⬜ |
| `PAGE-055.23` | 245–266 | ناحیه | `مسیر: update_change` | 3 | ⬜ |
| `PAGE-055.24` | 267–285 | ناحیه | `مسیر: submit_change` | 2 | ⬜ |
| `PAGE-055.25` | 286–308 | ناحیه | `مسیر: cab_decision` | 2 | ⬜ |
| `PAGE-055.26` | 309–332 | ناحیه | `مسیر: change_transition` | 7 | ⬜ |
| `PAGE-055.27` | 333–363 | ناحیه | `مسیر: create_problem` | 2 | ⬜ |
| `PAGE-055.28` | 364–385 | ناحیه | `مسیر: update_problem` | 3 | ⬜ |
| `PAGE-055.29` | 386–522 | ناحیه | `مسیر: link_governance_ticket` | **9** | ⬜ |

</details>

<details>
<summary><b>PAGE-056</b> — <code>help-guide.php</code> (722 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-056.01` | 1–722 | ناحیه | `بلوک رویه‌ای 1-722` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-057</b> — <code>holiday-board.php</code> (48 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-057.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `PAGE-057.02` | 18–47 | تابع | `holiday_board_render` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-058</b> — <code>index.php</code> (3,727 خط، 92 آدرس، بازبینی‌شده 0/92)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-058.01` | 1–37 | ناحیه | `بلوک رویه‌ای 1-37` | 3 | ⬜ |
| `PAGE-058.02` | 38–41 | تابع | `post_value` | 2 | ⬜ |
| `PAGE-058.03` | 43–47 | تابع | `csv_cell` | 0 | ⬜ |
| `PAGE-058.04` | 49–52 | تابع | `excel_cell` | 0 | ⬜ |
| `PAGE-058.05` | 54–115 | تابع | `excel_download` | 4 | ⬜ |
| `PAGE-058.06` | 117–120 | تابع | `valid_choice` | 0 | ⬜ |
| `PAGE-058.07` | 122–130 | تابع | `log_sort_order` | 0 | ⬜ |
| `PAGE-058.08` | 132–151 | تابع | `log_sort_header_row` | 0 | ⬜ |
| `PAGE-058.09` | 153–172 | تابع | `log_bounded_date_range` | 0 | ⬜ |
| `PAGE-058.10` | 174–239 | تابع | `activity_log_filters` | 2 | ⬜ |
| `PAGE-058.11` | 241–261 | تابع | `activity_log_meta_lines` | 0 | ⬜ |
| `PAGE-058.12` | 263–277 | تابع | `imported_calendar_date` | 0 | ⬜ |
| `PAGE-058.13` | 279–282 | تابع | `calendar_unescape` | 0 | ⬜ |
| `PAGE-058.14` | 284–355 | تابع | `parse_calendar_import` | 0 | ⬜ |
| `PAGE-058.15` | 357–410 | تابع | `store_attachment` | 6 | ⬜ |
| `PAGE-058.16` | 412–435 | تابع | `store_profile_photo` | 1 | ⬜ |
| `PAGE-058.17` | 437–449 | تابع | `valid_iranian_national_code` | 0 | ⬜ |
| `PAGE-058.18` | 451–456 | تابع | `fetch_ticket` | 0 | ⬜ |
| `PAGE-058.19` | 458–461 | تابع | `can_view_ticket` | 0 | ⬜ |
| `PAGE-058.20` | 463–482 | تابع | `ticket_org_manager_rank_sql` | 0 | ⬜ |
| `PAGE-058.21` | 484–526 | تابع | `choose_handling_agent` | 5 | ⬜ |
| `PAGE-058.22` | 528–546 | تابع | `ticket_allowed_transitions` | 0 | ⬜ |
| `PAGE-058.23` | 548–552 | تابع | `ticket_requester_cancelable_statuses` | 0 | ⬜ |
| `PAGE-058.24` | 554–560 | تابع | `ticket_requester_can_cancel_directly` | 0 | ⬜ |
| `PAGE-058.25` | 562–568 | تابع | `ticket_requester_can_cancel` | 0 | ⬜ |
| `PAGE-058.26` | 570–579 | تابع | `notify_ticket_resolved` | 0 | ⬜ |
| `PAGE-058.27` | 580–634 | تابع | `report_query_filters` | 2 | ⬜ |
| `PAGE-058.28` | 636–785 | تابع | `render_header` | 4 | ⬜ |
| `PAGE-058.29` | 787–794 | تابع | `list_sort_page_off` | 2 | ⬜ |
| `PAGE-058.30` | 796–817 | تابع | `inspection_food_reminder_markup` | 0 | ⬜ |
| `PAGE-058.31` | 819–850 | تابع | `render_footer` | 0 | ⬜ |
| `PAGE-058.32` | 851–886 | ناحیه | `بلوک رویه‌ای 851-886` | 2 | ⬜ |
| `PAGE-058.33` | 887–897 | ناحیه | `مسیر: must_change_password\|change-password` | 2 | ⬜ |
| `PAGE-058.34` | 898–911 | ناحیه | `مسیر: REQUEST_METHOD\|POST\|organization` | 0 | ⬜ |
| `PAGE-058.35` | 912–925 | ناحیه | `مسیر: food-ticket\|food_api` | 2 | ⬜ |
| `PAGE-058.36` | 926–937 | ناحیه | `مسیر: food-order\|food_api` | 2 | ⬜ |
| `PAGE-058.37` | 938–1196 | ناحیه | `مسیر: traffic-control\|traffic_api` | **9** | ⬜ |
| `PAGE-058.38` | 1197–1206 | ناحیه | `مسیر: food-personnel-link` | 2 | ⬜ |
| `PAGE-058.39` | 1207–1317 | ناحیه | `مسیر: logging_begin_operation` | 6 | ⬜ |
| `PAGE-058.40` | 1318–1328 | ناحیه | `مسیر: settings\|create_access_group\|save_access_g` | 2 | ⬜ |
| `PAGE-058.41` | 1329–1384 | ناحیه | `مسیر: chatbot-api` | 0 | ⬜ |
| `PAGE-058.42` | 1385–1392 | ناحیه | `مسیر: food-ticket\|food_ticket_is_allowed\|food.mo` | 0 | ⬜ |
| `PAGE-058.43` | 1393–1400 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `PAGE-058.44` | 1401–1409 | ناحیه | `مسیر: settings\|settings.general\|settings.domain` | 0 | ⬜ |
| `PAGE-058.45` | 1410–1412 | ناحیه | `مسیر: change-password` | 0 | ⬜ |
| `PAGE-058.46` | 1413–1415 | ناحیه | `مسیر: local-accounts` | 0 | ⬜ |
| `PAGE-058.47` | 1416–1433 | ناحیه | `مسیر: profile` | 0 | ⬜ |
| `PAGE-058.48` | 1434–1437 | ناحیه | `مسیر: governance` | 0 | ⬜ |
| `PAGE-058.49` | 1438–1442 | ناحیه | `مسیر: food-ticket` | 0 | ⬜ |
| `PAGE-058.50` | 1443–1450 | ناحیه | `مسیر: food-order\|foodorder.self` | 0 | ⬜ |
| `PAGE-058.51` | 1451–1454 | ناحیه | `مسیر: food-order` | 0 | ⬜ |
| `PAGE-058.52` | 1455–1458 | ناحیه | `مسیر: cd-dvd` | 0 | ⬜ |
| `PAGE-058.53` | 1459–1462 | ناحیه | `مسیر: cd-dvd-history` | 0 | ⬜ |
| `PAGE-058.54` | 1463–1465 | ناحیه | `مسیر: traffic-control` | 0 | ⬜ |
| `PAGE-058.55` | 1466–1469 | ناحیه | `مسیر: traffic-control-print` | 2 | ⬜ |
| `PAGE-058.56` | 1470–1555 | ناحیه | `مسیر: services` | 7 | ⬜ |
| `PAGE-058.57` | 1556–1564 | ناحیه | `سربرگ‌ها (تب‌ها)` | 0 | ⬜ |
| `PAGE-058.58` | 1565–1574 | ناحیه | `تب ۱: تعریف خدمت (فقط فرم ایجاد خدمت)` | 0 | ⬜ |
| `PAGE-058.59` | 1575–1598 | ناحیه | `تب ۲: لیست خدمات + فیلتر` | 6 | ⬜ |
| `PAGE-058.60` | 1599–1612 | ناحیه | `تب ۳: دسته‌بندی‌ها` | 6 | ⬜ |
| `PAGE-058.61` | 1613–1640 | ناحیه | `پنجره‌های ویرایش/فیلد (rendered outside the tab pa` | 5 | ⬜ |
| `PAGE-058.62` | 1641–1648 | ناحیه | `مسیر: help` | 0 | ⬜ |
| `PAGE-058.63` | 1649–1685 | ناحیه | `مسیر: knowledge` | 2 | ⬜ |
| `PAGE-058.64` | 1686–1693 | ناحیه | `مسیر: notifications\|notif.view` | 0 | ⬜ |
| `PAGE-058.65` | 1694–1711 | ناحیه | `مسیر: notifications` | 5 | ⬜ |
| `PAGE-058.66` | 1712–1720 | ناحیه | `مسیر: audit` | 0 | ⬜ |
| `PAGE-058.67` | 1721–1724 | ناحیه | `مسیر: logging-dashboard` | 0 | ⬜ |
| `PAGE-058.68` | 1725–1728 | ناحیه | `مسیر: system-health` | 0 | ⬜ |
| `PAGE-058.69` | 1729–1734 | ناحیه | `مسیر: logs` | 0 | ⬜ |
| `PAGE-058.70` | 1735–1877 | ناحیه | `مسیر: activity-log` | **8** | ⬜ |
| `PAGE-058.71` | 1878–1916 | ناحیه | `مسیر: backup` | 6 | ⬜ |
| `PAGE-058.72` | 1917–1924 | ناحیه | `مسیر: search\|search.global` | 0 | ⬜ |
| `PAGE-058.73` | 1925–2015 | ناحیه | `مسیر: search` | 7 | ⬜ |
| `PAGE-058.74` | 2016–2059 | ناحیه | `مسیر: queue` | 7 | ⬜ |
| `PAGE-058.75` | 2060–2162 | ناحیه | `مسیر: chatbot-admin` | 5 | ⬜ |
| `PAGE-058.76` | 2163–2173 | ناحیه | `مسیر: holidays` | 0 | ⬜ |
| `PAGE-058.77` | 2174–2181 | ناحیه | `مسیر: new-ticket\|ticket.create` | 0 | ⬜ |
| `PAGE-058.78` | 2182–2316 | ناحیه | `مسیر: new-ticket` | **8** | ⬜ |
| `PAGE-058.79` | 2317–2548 | ناحیه | `مسیر: ticket` | **8** | ⬜ |
| `PAGE-058.80` | 2549–2592 | ناحیه | `مسیر: analytics` | 5 | ⬜ |
| `PAGE-058.81` | 2593–2653 | ناحیه | `مسیر: reports` | 7 | ⬜ |
| `PAGE-058.82` | 2654–2697 | ناحیه | `مسیر: supervisor` | 7 | ⬜ |
| `PAGE-058.83` | 2698–2798 | ناحیه | `مسیر: inventory` | 7 | ⬜ |
| `PAGE-058.84` | 2799–2830 | ناحیه | `مسیر: inventory-diagnostics` | 2 | ⬜ |
| `PAGE-058.85` | 2831–2848 | ناحیه | `مسیر: asset-history` | 7 | ⬜ |
| `PAGE-058.86` | 2849–2926 | ناحیه | `مسیر: domain-scan` | 2 | ⬜ |
| `PAGE-058.87` | 2927–2971 | ناحیه | `مسیر: assets` | 7 | ⬜ |
| `PAGE-058.88` | 2972–3005 | ناحیه | `مسیر: asset` | 7 | ⬜ |
| `PAGE-058.89` | 3006–3196 | ناحیه | `مسیر: organization` | 3 | ⬜ |
| `PAGE-058.90` | 3197–3666 | ناحیه | `مسیر: settings` | **11** | ⬜ |
| `PAGE-058.91` | 3667–3688 | ناحیه | `مسیر: logs` | 2 | ⬜ |
| `PAGE-058.92` | 3689–3727 | ناحیه | `مسیر: dashboard` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-059</b> — <code>install.php</code> (368 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-059.01` | 1–23 | ناحیه | `بلوک رویه‌ای 1-23` | 2 | ⬜ |
| `PAGE-059.02` | 24–27 | تابع | `ih` | 0 | ⬜ |
| `PAGE-059.03` | 29–35 | تابع | `install_csrf` | 0 | ⬜ |
| `PAGE-059.04` | 37–47 | تابع | `install_layout` | 0 | ⬜ |
| `PAGE-059.05` | 49–52 | تابع | `install_error` | 0 | ⬜ |
| `PAGE-059.06` | 54–59 | تابع | `take_install_error` | 0 | ⬜ |
| `PAGE-059.07` | 61–66 | تابع | `mysql_from_session` | 0 | ⬜ |
| `PAGE-059.08` | 68–87 | تابع | `ldap_probe` | 2 | ⬜ |
| `PAGE-059.09` | 88–97 | ناحیه | `بلوک رویه‌ای 88-97` | 2 | ⬜ |
| `PAGE-059.10` | 98–126 | ناحیه | `شرط مسیریابی` | 3 | ⬜ |
| `PAGE-059.11` | 127–137 | ناحیه | `مسیر: test_db` | 0 | ⬜ |
| `PAGE-059.12` | 138–151 | ناحیه | `شرط مسیریابی` | 2 | ⬜ |
| `PAGE-059.13` | 152–351 | ناحیه | `مسیر: test_ldap` | **10** | ⬜ |
| `PAGE-059.14` | 352–360 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `PAGE-059.15` | 361–368 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-060</b> — <code>inventory.php</code> (834 خط، 33 آدرس، بازبینی‌شده 0/33)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-060.01` | 1–4 | ناحیه | `بلوک رویه‌ای 1-4` | 0 | ⬜ |
| `PAGE-060.02` | 5–12 | تابع | `inventory_command` | 5 | ⬜ |
| `PAGE-060.03` | 14–25 | تابع | `inventory_json` | 0 | ⬜ |
| `PAGE-060.04` | 27–31 | تابع | `inventory_last_error` | 0 | ⬜ |
| `PAGE-060.05` | 33–39 | تابع | `inventory_set_error` | 0 | ⬜ |
| `PAGE-060.06` | 41–44 | تابع | `inventory_server_script_path` | 0 | ⬜ |
| `PAGE-060.07` | 46–54 | تابع | `inventory_local_hostnames` | 0 | ⬜ |
| `PAGE-060.08` | 56–60 | تابع | `inventory_server_asset_tag` | 0 | ⬜ |
| `PAGE-060.09` | 62–70 | تابع | `inventory_asset_is_local` | 0 | ⬜ |
| `PAGE-060.10` | 72–94 | تابع | `inventory_run_powershell_file` | 0 | ⬜ |
| `PAGE-060.11` | 96–125 | تابع | `inventory_diagnostics` | 0 | ⬜ |
| `PAGE-060.12` | 127–135 | تابع | `inventory_value` | 0 | ⬜ |
| `PAGE-060.13` | 137–143 | تابع | `inventory_rows` | 0 | ⬜ |
| `PAGE-060.14` | 145–204 | تابع | `inventory_collection_status_keys` | 0 | ⬜ |
| `PAGE-060.15` | 206–218 | تابع | `inventory_collection_category_succeeded` | 0 | ⬜ |
| `PAGE-060.16` | 220–256 | تابع | `inventory_merge_missing_values` | 0 | ⬜ |
| `PAGE-060.17` | 258–286 | تابع | `inventory_saved_asset_snapshot` | 0 | ⬜ |
| `PAGE-060.18` | 288–332 | تابع | `inventory_apply_collection_status_metadata` | 0 | ⬜ |
| `PAGE-060.19` | 334–342 | تابع | `inventory_asset_exists` | 0 | ⬜ |
| `PAGE-060.20` | 344–380 | تابع | `sync_inventory_rows` | 1 | ⬜ |
| `PAGE-060.21` | 382–392 | تابع | `inventory_hardware_score` | 0 | ⬜ |
| `PAGE-060.22` | 394–408 | تابع | `inventory_collect_server_reliable` | 1 | ⬜ |
| `PAGE-060.23` | 410–422 | تابع | `inventory_server_asset_id` | 0 | ⬜ |
| `PAGE-060.24` | 424–442 | تابع | `inventory_server_profile_is_empty` | 0 | ⬜ |
| `PAGE-060.25` | 444–463 | تابع | `inventory_auto_extract_server` | 0 | ⬜ |
| `PAGE-060.26` | 465–500 | تابع | `inventory_base_inventory` | 0 | ⬜ |
| `PAGE-060.27` | 502–614 | تابع | `inventory_apply_powershell_data` | 0 | ⬜ |
| `PAGE-060.28` | 616–632 | تابع | `collect_server_inventory` | 0 | ⬜ |
| `PAGE-060.29` | 634–684 | تابع | `inventory_upsert_server_asset` | 6 | ⬜ |
| `PAGE-060.30` | 686–700 | تابع | `inventory_collect_initial_asset` | 0 | ⬜ |
| `PAGE-060.31` | 702–754 | تابع | `inventory_reverse_name` | 7 | ⬜ |
| `PAGE-060.32` | 756–827 | تابع | `inventory_collect_client_asset` | 1 | ⬜ |
| `PAGE-060.33` | 829–833 | تابع | `inventory_client_ip` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-061</b> — <code>local-accounts.php</code> (217 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-061.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `PAGE-061.02` | 17–21 | تابع | `lac_default_hash` | 0 | ⬜ |
| `PAGE-061.03` | 23–71 | تابع | `lac_ensure_schema` | 4 | ⬜ |
| `PAGE-061.04` | 73–77 | تابع | `lac_is_local_role` | 0 | ⬜ |
| `PAGE-061.05` | 79–122 | تابع | `lac_change_password_request` | 3 | ⬜ |
| `PAGE-061.06` | 124–216 | تابع | `lac_admin_request` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-062</b> — <code>logging-dashboard.php</code> (163 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-062.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-062.02` | 4–93 | تابع | `logging_dashboard_fetch` | 1 | ⬜ |
| `PAGE-062.03` | 95–110 | تابع | `logging_dashboard_svg_trend` | 0 | ⬜ |
| `PAGE-062.04` | 112–162 | تابع | `logging_dashboard_render` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-063</b> — <code>ola.php</code> (112 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-063.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-063.02` | 6–9 | تابع | `ola_post` | 2 | ⬜ |
| `PAGE-063.03` | 11–22 | تابع | `ola_header` | 0 | ⬜ |
| `PAGE-063.04` | 24–28 | تابع | `ola_footer` | 0 | ⬜ |
| `PAGE-063.05` | 29–50 | ناحیه | `بلوک رویه‌ای 29-50` | 0 | ⬜ |
| `PAGE-063.06` | 51–72 | ناحیه | `مسیر: save_ola_policy` | 3 | ⬜ |
| `PAGE-063.07` | 73–112 | ناحیه | `مسیر: toggle_ola_policy` | **8** | ⬜ |

</details>

<details>
<summary><b>PAGE-064</b> — <code>organization.php</code> (787 خط، 52 آدرس، بازبینی‌شده 0/52)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-064.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-064.02` | 4–66 | تابع | `org_ensure_schema` | **9** | ⬜ |
| `PAGE-064.03` | 69–90 | تابع | `org_ticket_schema_ensure` | 4 | ⬜ |
| `PAGE-064.04` | 92–103 | تابع | `org_has_company_schema` | 0 | ⬜ |
| `PAGE-064.05` | 105–114 | تابع | `org_companies_all` | 0 | ⬜ |
| `PAGE-064.06` | 116–125 | تابع | `org_company_display_name` | 0 | ⬜ |
| `PAGE-064.07` | 127–134 | تابع | `org_company_user_label` | 0 | ⬜ |
| `PAGE-064.08` | 136–144 | تابع | `org_company_by_id` | 0 | ⬜ |
| `PAGE-064.09` | 146–152 | تابع | `org_user_company_memberships` | 0 | ⬜ |
| `PAGE-064.10` | 154–158 | تابع | `org_user_company_id` | 0 | ⬜ |
| `PAGE-064.11` | 160–164 | تابع | `org_user_available_for_company` | 0 | ⬜ |
| `PAGE-064.12` | 166–171 | تابع | `org_user_company_membership_join_sql` | 5 | ⬜ |
| `PAGE-064.13` | 173–178 | تابع | `org_company_member_user_ids` | 5 | ⬜ |
| `PAGE-064.14` | 180–195 | تابع | `org_company_context_id` | 0 | ⬜ |
| `PAGE-064.15` | 197–206 | تابع | `org_current_company_id` | 0 | ⬜ |
| `PAGE-064.16` | 208–212 | تابع | `org_company_root_type` | 0 | ⬜ |
| `PAGE-064.17` | 214–218 | تابع | `org_company_root_title` | 0 | ⬜ |
| `PAGE-064.18` | 220–233 | تابع | `org_has_sort_order` | 0 | ⬜ |
| `PAGE-064.19` | 235–243 | تابع | `org_next_sort_order` | 0 | ⬜ |
| `PAGE-064.20` | 245–254 | تابع | `org_units_all` | 0 | ⬜ |
| `PAGE-064.21` | 256–272 | تابع | `org_deputy_units` | 5 | ⬜ |
| `PAGE-064.22` | 274–299 | تابع | `org_liaison_units` | 4 | ⬜ |
| `PAGE-064.23` | 301–334 | تابع | `org_unit_routing_department_id` | 1 | ⬜ |
| `PAGE-064.24` | 336–349 | تابع | `org_ceo_unit_id` | 0 | ⬜ |
| `PAGE-064.25` | 351–359 | تابع | `org_unit_accepts_experts` | 0 | ⬜ |
| `PAGE-064.26` | 361–367 | تابع | `org_unit_by_id` | 0 | ⬜ |
| `PAGE-064.27` | 369–375 | تابع | `org_unit_by_company` | 0 | ⬜ |
| `PAGE-064.28` | 377–381 | تابع | `org_company_redirect_url` | 0 | ⬜ |
| `PAGE-064.29` | 383–389 | تابع | `org_unit_children` | 5 | ⬜ |
| `PAGE-064.30` | 391–406 | تابع | `org_unit_subtree_ids` | 0 | ⬜ |
| `PAGE-064.31` | 408–422 | تابع | `org_unit_path_names` | 0 | ⬜ |
| `PAGE-064.32` | 424–429 | تابع | `org_unit_manager_ids` | 0 | ⬜ |
| `PAGE-064.33` | 431–435 | تابع | `org_unit_is_primary_admin` | 0 | ⬜ |
| `PAGE-064.34` | 437–440 | تابع | `user_org_unit_id` | 0 | ⬜ |
| `PAGE-064.35` | 442–447 | تابع | `user_is_unit_manager` | 0 | ⬜ |
| `PAGE-064.36` | 449–465 | تابع | `user_managed_unit_ids` | 0 | ⬜ |
| `PAGE-064.37` | 467–476 | تابع | `user_org_scope_unit_ids` | 0 | ⬜ |
| `PAGE-064.38` | 478–484 | تابع | `user_is_org_manager_of` | 0 | ⬜ |
| `PAGE-064.39` | 486–492 | تابع | `user_can_manage_organization` | 0 | ⬜ |
| `PAGE-064.40` | 494–517 | تابع | `org_snapshot` | 5 | ⬜ |
| `PAGE-064.41` | 519–526 | تابع | `org_snapshot_maps` | 0 | ⬜ |
| `PAGE-064.42` | 528–549 | تابع | `org_snapshot_row_signature` | 0 | ⬜ |
| `PAGE-064.43` | 551–568 | تابع | `org_snapshot_delta` | 0 | ⬜ |
| `PAGE-064.44` | 570–583 | تابع | `org_record_undo` | 0 | ⬜ |
| `PAGE-064.45` | 585–591 | تابع | `org_undo_state` | 0 | ⬜ |
| `PAGE-064.46` | 593–668 | تابع | `org_restore_snapshot` | 5 | ⬜ |
| `PAGE-064.47` | 670–679 | تابع | `org_position_unit_types` | 0 | ⬜ |
| `PAGE-064.48` | 681–684 | تابع | `org_exempt_roles` | 0 | ⬜ |
| `PAGE-064.49` | 686–704 | تابع | `org_ensure_role_sync_columns` | **8** | ⬜ |
| `PAGE-064.50` | 706–722 | تابع | `org_position_user_ids` | 4 | ⬜ |
| `PAGE-064.51` | 724–729 | تابع | `org_expert_user_ids` | 0 | ⬜ |
| `PAGE-064.52` | 731–786 | تابع | `org_sync_position_roles` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-065</b> — <code>permissions.php</code> (912 خط، 50 آدرس، بازبینی‌شده 0/50)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-065.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-065.02` | 4–26 | تابع | `permission_roles` | 0 | ⬜ |
| `PAGE-065.03` | 28–214 | تابع | `permission_catalog` | 2 | ⬜ |
| `PAGE-065.04` | 216–225 | تابع | `permission_all_codes` | 0 | ⬜ |
| `PAGE-065.05` | 226–230 | ناحیه | `بلوک رویه‌ای 226-230` | 0 | ⬜ |
| `PAGE-065.06` | 231–235 | تابع | `permission_cddvd_direct_edit_roles` | 1 | ⬜ |
| `PAGE-065.07` | 237–244 | تابع | `permission_filter_role_codes` | 0 | ⬜ |
| `PAGE-065.08` | 246–279 | تابع | `permission_edit_map` | 0 | ⬜ |
| `PAGE-065.09` | 281–291 | تابع | `role_permissions_add_parents` | 0 | ⬜ |
| `PAGE-065.10` | 292–295 | ناحیه | `بلوک رویه‌ای 292-295` | 0 | ⬜ |
| `PAGE-065.11` | 296–300 | تابع | `print_fish_allowed_roles` | 0 | ⬜ |
| `PAGE-065.12` | 302–311 | تابع | `print_fish_role_targets` | 0 | ⬜ |
| `PAGE-065.13` | 313–323 | تابع | `print_fish_role_codes` | 0 | ⬜ |
| `PAGE-065.14` | 325–342 | تابع | `permission_defaults` | 0 | ⬜ |
| `PAGE-065.15` | 344–348 | تابع | `user_default_removed_codes` | 0 | ⬜ |
| `PAGE-065.16` | 350–370 | تابع | `permission_defaults_raw` | 0 | ⬜ |
| `PAGE-065.17` | 372–385 | تابع | `permission_table_ready` | 0 | ⬜ |
| `PAGE-065.18` | 386–389 | ناحیه | `بلوک رویه‌ای 386-389` | 0 | ⬜ |
| `PAGE-065.19` | 390–406 | تابع | `role_permissions_custom` | 0 | ⬜ |
| `PAGE-065.20` | 408–414 | تابع | `role_permissions_mark_custom` | 0 | ⬜ |
| `PAGE-065.21` | 416–420 | تابع | `role_permissions_normalize_codes` | 0 | ⬜ |
| `PAGE-065.22` | 422–448 | تابع | `permission_food_legacy_codes` | 0 | ⬜ |
| `PAGE-065.23` | 450–464 | تابع | `role_permission_effective_codes` | 0 | ⬜ |
| `PAGE-065.24` | 466–474 | تابع | `role_permissions_same_set` | 0 | ⬜ |
| `PAGE-065.25` | 476–485 | تابع | `role_permissions_flush_cache` | 0 | ⬜ |
| `PAGE-065.26` | 487–573 | تابع | `role_permissions_migrate_once` | **9** | ⬜ |
| `PAGE-065.27` | 575–596 | تابع | `role_permission_codes` | 0 | ⬜ |
| `PAGE-065.28` | 598–611 | تابع | `role_permission_set` | 0 | ⬜ |
| `PAGE-065.29` | 613–655 | تابع | `user_access_group_permission_set` | 0 | ⬜ |
| `PAGE-065.30` | 657–668 | تابع | `user_access_group_permissions_flush_cache` | 1 | ⬜ |
| `PAGE-065.31` | 670–680 | تابع | `role_permissions_seeded` | 0 | ⬜ |
| `PAGE-065.32` | 682–716 | تابع | `role_permissions_seed` | 0 | ⬜ |
| `PAGE-065.33` | 718–751 | تابع | `role_permissions_save` | 1 | ⬜ |
| `PAGE-065.34` | 753–772 | تابع | `role_permissions_reset` | 1 | ⬜ |
| `PAGE-065.35` | 774–777 | تابع | `user_role_code` | 0 | ⬜ |
| `PAGE-065.36` | 779–783 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `PAGE-065.37` | 785–799 | تابع | `user_can` | 0 | ⬜ |
| `PAGE-065.38` | 801–809 | تابع | `user_can_any` | 0 | ⬜ |
| `PAGE-065.39` | 811–820 | تابع | `require_permission` | 0 | ⬜ |
| `PAGE-065.40` | 822–830 | تابع | `require_any_permission` | 0 | ⬜ |
| `PAGE-065.41` | 832–859 | تابع | `permission_icon` | 0 | ⬜ |
| `PAGE-065.42` | 861–865 | تابع | `staff_role_codes` | 0 | ⬜ |
| `PAGE-065.43` | 867–870 | تابع | `is_staff_role` | 0 | ⬜ |
| `PAGE-065.44` | 872–876 | تابع | `admin_role_codes` | 0 | ⬜ |
| `PAGE-065.45` | 878–881 | تابع | `is_admin_role` | 0 | ⬜ |
| `PAGE-065.46` | 883–888 | تابع | `global_ticket_role_codes` | 0 | ⬜ |
| `PAGE-065.47` | 890–893 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `PAGE-065.48` | 895–900 | تابع | `is_department_scoped_role` | 0 | ⬜ |
| `PAGE-065.49` | 902–906 | تابع | `assignable_role_codes` | 0 | ⬜ |
| `PAGE-065.50` | 908–911 | تابع | `is_assignable_role` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-066</b> — <code>preview/food-release-download/vite.config.js</code> (57 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-066.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `PAGE-066.02` | 14–57 | بلوک | `pathname` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-067</b> — <code>routes/web.php</code> (36 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-067.01` | 1–36 | ناحیه | `بلوک رویه‌ای 1-36` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-068</b> — <code>session_bootstrap.php</code> (58 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-068.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-068.02` | 4–57 | تابع | `app_session_start` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-069</b> — <code>system-health.php</code> (1,284 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-069.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `PAGE-069.02` | 13–23 | تابع | `system_health_ensure` | 0 | ⬜ |
| `PAGE-069.03` | 25–41 | تابع | `system_health_issue_filter` | 5 | ⬜ |
| `PAGE-069.04` | 43–57 | تابع | `system_health_issue_rows` | 5 | ⬜ |
| `PAGE-069.05` | 59–69 | تابع | `system_health_issue_total` | 0 | ⬜ |
| `PAGE-069.06` | 71–84 | تابع | `system_health_issue_history` | 5 | ⬜ |
| `PAGE-069.07` | 86–94 | تابع | `system_health_issue_history_total` | 0 | ⬜ |
| `PAGE-069.08` | 96–99 | تابع | `system_health_fingerprint` | 0 | ⬜ |
| `PAGE-069.09` | 101–121 | تابع | `system_health_table_label` | 0 | ⬜ |
| `PAGE-069.10` | 123–162 | تابع | `system_health_human_module` | 0 | ⬜ |
| `PAGE-069.11` | 164–174 | تابع | `system_health_human_level` | 0 | ⬜ |
| `PAGE-069.12` | 176–225 | تابع | `system_health_humanize_event` | 0 | ⬜ |
| `PAGE-069.13` | 227–250 | تابع | `system_health_event_reference` | 0 | ⬜ |
| `PAGE-069.14` | 252–270 | تابع | `system_health_request_label` | 0 | ⬜ |
| `PAGE-069.15` | 272–319 | تابع | `system_health_summary` | 0 | ⬜ |
| `PAGE-069.16` | 321–338 | تابع | `system_health_apply_check_state` | 0 | ⬜ |
| `PAGE-069.17` | 340–403 | تابع | `system_health_groups` | 0 | ⬜ |
| `PAGE-069.18` | 405–430 | تابع | `system_health_event_totals` | 0 | ⬜ |
| `PAGE-069.19` | 432–644 | تابع | `system_health_checks` | 4 | ⬜ |
| `PAGE-069.20` | 645–650 | ناحیه | `بلوک رویه‌ای 645-650` | 0 | ⬜ |
| `PAGE-069.21` | 651–681 | تابع | `sys_maint_cache_candidates` | 0 | ⬜ |
| `PAGE-069.22` | 683–699 | تابع | `sys_maint_cache_purge` | 5 | ⬜ |
| `PAGE-069.23` | 701–719 | تابع | `sys_maint_analyze_tables` | 0 | ⬜ |
| `PAGE-069.24` | 721–729 | تابع | `sys_maint_table_overview` | 0 | ⬜ |
| `PAGE-069.25` | 731–810 | تابع | `system_health_handle_post` | 2 | ⬜ |
| `PAGE-069.26` | 812–828 | تابع | `system_health_export_safe_text` | 0 | ⬜ |
| `PAGE-069.27` | 830–888 | تابع | `system_health_export_technical_xlsx` | 4 | ⬜ |
| `PAGE-069.28` | 891–1035 | تابع | `system_health_export_safe_xlsx` | 7 | ⬜ |
| `PAGE-069.29` | 1038–1060 | تابع | `system_health_request_timeline` | 0 | ⬜ |
| `PAGE-069.30` | 1062–1283 | تابع | `system_health_render` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-070</b> — <code>system-update.php</code> (257 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-070.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `PAGE-070.02` | 16–21 | تابع | `system_update_current_version` | 0 | ⬜ |
| `PAGE-070.03` | 23–32 | تابع | `system_update_ensure_table` | 4 | ⬜ |
| `PAGE-070.04` | 34–39 | تابع | `system_update_migration_applied` | 0 | ⬜ |
| `PAGE-070.05` | 41–72 | تابع | `system_update_requires_violation` | 0 | ⬜ |
| `PAGE-070.06` | 74–137 | تابع | `system_update_read_package` | 0 | ⬜ |
| `PAGE-070.07` | 138–140 | ناحیه | `بلوک رویه‌ای 138-140` | 0 | ⬜ |
| `PAGE-070.08` | 141–165 | تابع | `system_update_files_snapshot` | 1 | ⬜ |
| `PAGE-070.09` | 167–256 | تابع | `system_update_apply` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-071</b> — <code>traffic-control.php</code> (1,427 خط، 62 آدرس، بازبینی‌شده 0/62)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-071.01` | 1–8 | ناحیه | `بلوک رویه‌ای 1-8` | 0 | ⬜ |
| `PAGE-071.02` | 9–12 | تابع | `traffic_can_manage` | 0 | ⬜ |
| `PAGE-071.03` | 14–17 | تابع | `traffic_can_view_destinations` | 0 | ⬜ |
| `PAGE-071.04` | 19–22 | تابع | `traffic_can_manage_destinations` | 0 | ⬜ |
| `PAGE-071.05` | 24–28 | تابع | `traffic_destinations` | 0 | ⬜ |
| `PAGE-071.06` | 30–33 | تابع | `traffic_approvers` | 0 | ⬜ |
| `PAGE-071.07` | 35–46 | تابع | `traffic_destination_row_approvers` | 0 | ⬜ |
| `PAGE-071.08` | 48–73 | تابع | `traffic_destination_approvers` | 0 | ⬜ |
| `PAGE-071.09` | 75–83 | تابع | `traffic_destination_id` | 0 | ⬜ |
| `PAGE-071.10` | 85–99 | تابع | `traffic_destination_approver` | 0 | ⬜ |
| `PAGE-071.11` | 100–101 | ناحیه | `بلوک رویه‌ای 100-101` | 0 | ⬜ |
| `PAGE-071.12` | 102–110 | تابع | `traffic_next_serial` | 0 | ⬜ |
| `PAGE-071.13` | 112–115 | تابع | `traffic_find_last_visit` | 0 | ⬜ |
| `PAGE-071.14` | 117–127 | تابع | `traffic_find_last_visit_by` | 5 | ⬜ |
| `PAGE-071.15` | 129–135 | تابع | `traffic_fetch_record` | 0 | ⬜ |
| `PAGE-071.16` | 137–153 | تابع | `traffic_search_condition` | 0 | ⬜ |
| `PAGE-071.17` | 155–169 | تابع | `traffic_query_records` | 5 | ⬜ |
| `PAGE-071.18` | 171–189 | تابع | `traffic_stats` | 0 | ⬜ |
| `PAGE-071.19` | 191–199 | تابع | `traffic_empty_stats` | 0 | ⬜ |
| `PAGE-071.20` | 201–224 | تابع | `traffic_report_criteria` | 0 | ⬜ |
| `PAGE-071.21` | 226–239 | تابع | `traffic_report_records` | 0 | ⬜ |
| `PAGE-071.22` | 241–248 | تابع | `traffic_report_columns` | 0 | ⬜ |
| `PAGE-071.23` | 250–258 | تابع | `traffic_report_selected_fields` | 0 | ⬜ |
| `PAGE-071.24` | 260–273 | تابع | `traffic_report_item` | 0 | ⬜ |
| `PAGE-071.25` | 275–299 | تابع | `traffic_export_report` | 0 | ⬜ |
| `PAGE-071.26` | 301–311 | کلاس | `TrafficInputException` | 0 | ⬜ |
| `PAGE-071.27` | 313–321 | تابع | `traffic_phone_normalized` | 0 | ⬜ |
| `PAGE-071.28` | 323–326 | تابع | `traffic_phone_digits` | 0 | ⬜ |
| `PAGE-071.29` | 328–341 | تابع | `traffic_phone_tail` | 0 | ⬜ |
| `PAGE-071.30` | 343–371 | تابع | `traffic_validate_payload` | 0 | ⬜ |
| `PAGE-071.31` | 373–381 | تابع | `traffic_visit_snapshot` | 0 | ⬜ |
| `PAGE-071.32` | 383–405 | تابع | `traffic_record_history` | 1 | ⬜ |
| `PAGE-071.33` | 407–417 | تابع | `traffic_history_records` | 5 | ⬜ |
| `PAGE-071.34` | 419–422 | تابع | `traffic_history_count` | 0 | ⬜ |
| `PAGE-071.35` | 424–433 | تابع | `traffic_history_field_label` | 0 | ⬜ |
| `PAGE-071.36` | 435–444 | تابع | `traffic_normalize_time_input` | 0 | ⬜ |
| `PAGE-071.37` | 446–464 | تابع | `traffic_history_value` | 0 | ⬜ |
| `PAGE-071.38` | 466–478 | تابع | `traffic_create_record` | 3 | ⬜ |
| `PAGE-071.39` | 480–534 | تابع | `traffic_update_record` | 2 | ⬜ |
| `PAGE-071.40` | 536–559 | تابع | `traffic_delete_record` | 2 | ⬜ |
| `PAGE-071.41` | 561–610 | تابع | `traffic_set_exit` | 2 | ⬜ |
| `PAGE-071.42` | 612–638 | تابع | `traffic_ensure_notice_schema` | 4 | ⬜ |
| `PAGE-071.43` | 639–641 | ناحیه | `بلوک رویه‌ای 639-641` | 0 | ⬜ |
| `PAGE-071.44` | 642–663 | تابع | `traffic_notice_create` | 3 | ⬜ |
| `PAGE-071.45` | 665–676 | تابع | `traffic_destination_posted_approvers` | 2 | ⬜ |
| `PAGE-071.46` | 678–696 | تابع | `traffic_save_destination` | 3 | ⬜ |
| `PAGE-071.47` | 698–724 | تابع | `traffic_update_destination` | 3 | ⬜ |
| `PAGE-071.48` | 726–734 | تابع | `traffic_delete_destination` | 3 | ⬜ |
| `PAGE-071.49` | 736–745 | تابع | `traffic_form_old_values` | 2 | ⬜ |
| `PAGE-071.50` | 747–760 | تابع | `traffic_remember_form_error` | 2 | ⬜ |
| `PAGE-071.51` | 762–766 | تابع | `traffic_open_visits` | 0 | ⬜ |
| `PAGE-071.52` | 768–839 | تابع | `traffic_handle_post` | 2 | ⬜ |
| `PAGE-071.53` | 841–842 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-071.54` | 843–843 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-071.55` | 844–868 | تابع | `traffic_history_rows_html` | 0 | ⬜ |
| `PAGE-071.56` | 870–958 | تابع | `traffic_api_handle` | 2 | ⬜ |
| `PAGE-071.57` | 960–961 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-071.58` | 962–962 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-071.59` | 963–1359 | تابع | `traffic_render_page` | 6 | ⬜ |
| `PAGE-071.60` | 1361–1362 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-071.61` | 1363–1363 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-071.62` | 1364–1426 | تابع | `traffic_render_print` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-072</b> — <code>traffic-import.php</code> (1,030 خط، 27 آدرس، بازبینی‌شده 0/27)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-072.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `PAGE-072.02` | 16–39 | تابع | `traffic_import_column_aliases` | 0 | ⬜ |
| `PAGE-072.03` | 41–80 | تابع | `traffic_import_detect_header` | 0 | ⬜ |
| `PAGE-072.04` | 82–98 | تابع | `traffic_import_date_from_parts` | 0 | ⬜ |
| `PAGE-072.05` | 100–105 | تابع | `traffic_import_seconds_to_time` | 0 | ⬜ |
| `PAGE-072.06` | 107–164 | تابع | `traffic_import_parse_date_value` | 0 | ⬜ |
| `PAGE-072.07` | 166–218 | تابع | `traffic_import_parse_time` | 0 | ⬜ |
| `PAGE-072.08` | 220–249 | تابع | `traffic_import_parse_bool` | 0 | ⬜ |
| `PAGE-072.09` | 251–254 | تابع | `traffic_import_text_length` | 0 | ⬜ |
| `PAGE-072.10` | 256–262 | تابع | `traffic_import_optional_marker` | 0 | ⬜ |
| `PAGE-072.11` | 264–417 | تابع | `traffic_import_parse_sheet` | 1 | ⬜ |
| `PAGE-072.12` | 419–451 | تابع | `traffic_import_xlsx_archive_error` | 0 | ⬜ |
| `PAGE-072.13` | 453–519 | تابع | `traffic_import_csv_shape_error` | 1 | ⬜ |
| `PAGE-072.14` | 521–586 | تابع | `traffic_import_read_upload` | 0 | ⬜ |
| `PAGE-072.15` | 588–596 | تابع | `traffic_import_identity` | 0 | ⬜ |
| `PAGE-072.16` | 598–601 | تابع | `traffic_import_serial_key` | 0 | ⬜ |
| `PAGE-072.17` | 603–610 | تابع | `traffic_import_generated_serial` | 0 | ⬜ |
| `PAGE-072.18` | 612–616 | تابع | `traffic_import_generated_serial_prefix` | 0 | ⬜ |
| `PAGE-072.19` | 618–728 | تابع | `traffic_import_build_plan` | 1 | ⬜ |
| `PAGE-072.20` | 730–799 | تابع | `traffic_import_plan_from_database` | 6 | ⬜ |
| `PAGE-072.21` | 801–834 | تابع | `traffic_import_preview_session_data` | 0 | ⬜ |
| `PAGE-072.22` | 836–849 | تابع | `traffic_import_current_preview` | 0 | ⬜ |
| `PAGE-072.23` | 851–940 | تابع | `traffic_import_handle_post` | 2 | ⬜ |
| `PAGE-072.24` | 942–983 | تابع | `traffic_import_apply_plan_in_transaction` | 1 | ⬜ |
| `PAGE-072.25` | 985–991 | تابع | `traffic_import_template_headers` | 0 | ⬜ |
| `PAGE-072.26` | 993–1004 | تابع | `traffic_import_template_csv_bytes` | 0 | ⬜ |
| `PAGE-072.27` | 1006–1029 | تابع | `traffic_import_download_template` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-073</b> — <code>user-access-groups.php</code> (209 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-073.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-073.02` | 4–28 | تابع | `user_access_groups_schema_ready` | 0 | ⬜ |
| `PAGE-073.03` | 30–49 | تابع | `user_access_groups_all` | 0 | ⬜ |
| `PAGE-073.04` | 51–60 | تابع | `user_access_groups_admin_users` | 0 | ⬜ |
| `PAGE-073.05` | 62–76 | تابع | `user_access_group_member_set` | 0 | ⬜ |
| `PAGE-073.06` | 78–96 | تابع | `user_access_groups_member_sets` | 0 | ⬜ |
| `PAGE-073.07` | 98–117 | تابع | `user_access_groups_permission_sets` | 0 | ⬜ |
| `PAGE-073.08` | 119–133 | تابع | `user_access_group_permission_set_for_group` | 0 | ⬜ |
| `PAGE-073.09` | 135–138 | تابع | `user_access_group_role_label` | 0 | ⬜ |
| `PAGE-073.10` | 140–199 | تابع | `user_access_group_permission_matrix_markup` | 0 | ⬜ |
| `PAGE-073.11` | 201–208 | تابع | `user_access_groups_redirect` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-074</b> — <code>xlsx-writer.php</code> (221 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-074.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-074.02` | 4–27 | تابع | `xlsx_col_name` | 0 | ⬜ |
| `PAGE-074.03` | 29–33 | تابع | `xlsx_clean_text` | 0 | ⬜ |
| `PAGE-074.04` | 35–42 | تابع | `xlsx_sheet_name` | 0 | ⬜ |
| `PAGE-074.05` | 44–137 | تابع | `xlsx_build` | 6 | ⬜ |
| `PAGE-074.06` | 139–142 | تابع | `xlsx_xml` | 0 | ⬜ |
| `PAGE-074.07` | 144–207 | تابع | `xlsx_sheet_xml` | 0 | ⬜ |
| `PAGE-074.08` | 209–220 | تابع | `xlsx_send` | 3 | ⬜ |

</details>

### دارایی‌های ایستا (assets/) — `ASSET`


<details>
<summary><b>ASSET-001</b> — <code>assets/admin-layout-design.css</code> (79 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-001.01` | 1–79 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |

</details>

<details>
<summary><b>ASSET-002</b> — <code>assets/admin-layout-design.js</code> (898 خط، 55 آدرس، بازبینی‌شده 0/55)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-002.01` | 1–58 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `ASSET-002.02` | 59–59 | بلوک | `esc` | 0 | ⬜ |
| `ASSET-002.03` | 60–61 | بلوک | `pathKey` | 0 | ⬜ |
| `ASSET-002.04` | 62–86 | بلوک | `setPanelPosition` | 4 | ⬜ |
| `ASSET-002.05` | 87–94 | بلوک | `restorePanelPosition` | 4 | ⬜ |
| `ASSET-002.06` | 95–112 | بلوک | `startPanelDrag` | 4 | ⬜ |
| `ASSET-002.07` | 113–123 | بلوک | `onPanelPointerMove` | 0 | ⬜ |
| `ASSET-002.08` | 124–131 | بلوک | `finishPanelDrag` | 0 | ⬜ |
| `ASSET-002.09` | 132–138 | بلوک | `movePanelBy` | 0 | ⬜ |
| `ASSET-002.10` | 139–159 | بلوک | `makePath` | 0 | ⬜ |
| `ASSET-002.11` | 160–170 | بلوک | `selectorFor` | 0 | ⬜ |
| `ASSET-002.12` | 171–184 | بلوک | `elementForPath` | 4 | ⬜ |
| `ASSET-002.13` | 185–198 | بلوک | `applyAll` | 0 | ⬜ |
| `ASSET-002.14` | 199–203 | بلوک | `removeRule` | 0 | ⬜ |
| `ASSET-002.15` | 204–213 | بلوک | `selectedRule` | 0 | ⬜ |
| `ASSET-002.16` | 214–231 | بلوک | `previewProperty` | 1 | ⬜ |
| `ASSET-002.17` | 232–239 | بلوک | `selectedDescription` | 0 | ⬜ |
| `ASSET-002.18` | 240–249 | بلوک | `propertyControl` | 0 | ⬜ |
| `ASSET-002.19` | 250–257 | بلوک | `selectedOffset` | 0 | ⬜ |
| `ASSET-002.20` | 258–272 | بلوک | `renderPositionControls` | 0 | ⬜ |
| `ASSET-002.21` | 273–299 | بلوک | `setSelectedOffset` | 1 | ⬜ |
| `ASSET-002.22` | 300–306 | بلوک | `nudgeSelected` | 0 | ⬜ |
| `ASSET-002.23` | 307–327 | بلوک | `renderControls` | 0 | ⬜ |
| `ASSET-002.24` | 328–335 | بلوک | `updateButtons` | 0 | ⬜ |
| `ASSET-002.25` | 336–350 | بلوک | `ensureDragHandle` | 0 | ⬜ |
| `ASSET-002.26` | 351–368 | بلوک | `ensureResizeHandles` | 0 | ⬜ |
| `ASSET-002.27` | 369–394 | بلوک | `updateDragHandle` | 0 | ⬜ |
| `ASSET-002.28` | 395–411 | بلوک | `startOffsetDrag` | 4 | ⬜ |
| `ASSET-002.29` | 412–417 | بلوک | `resizeExtra` | 0 | ⬜ |
| `ASSET-002.30` | 418–432 | بلوک | `setDimensionFromOuterSize` | 0 | ⬜ |
| `ASSET-002.31` | 433–455 | بلوک | `startResize` | 4 | ⬜ |
| `ASSET-002.32` | 456–465 | بلوک | `resizeSelected` | 0 | ⬜ |
| `ASSET-002.33` | 466–498 | بلوک | `onOffsetPointerMove` | 0 | ⬜ |
| `ASSET-002.34` | 499–506 | بلوک | `finishOffsetDrag` | 0 | ⬜ |
| `ASSET-002.35` | 507–587 | بلوک | `createPanel` | 0 | ⬜ |
| `ASSET-002.36` | 588–601 | بلوک | `chooseTarget` | 0 | ⬜ |
| `ASSET-002.37` | 602–612 | بلوک | `layoutContainerForSelection` | 0 | ⬜ |
| `ASSET-002.38` | 613–625 | بلوک | `collectLayoutItems` | 0 | ⬜ |
| `ASSET-002.39` | 626–638 | بلوک | `orderableSiblings` | 0 | ⬜ |
| `ASSET-002.40` | 639–645 | بلوک | `elementCaption` | 0 | ⬜ |
| `ASSET-002.41` | 646–674 | بلوک | `renderOrderControls` | 0 | ⬜ |
| `ASSET-002.42` | 675–688 | بلوک | `writeOrderRule` | 0 | ⬜ |
| `ASSET-002.43` | 689–696 | بلوک | `applyOrder` | 0 | ⬜ |
| `ASSET-002.44` | 697–706 | بلوک | `moveOrderItem` | 0 | ⬜ |
| `ASSET-002.45` | 707–713 | بلوک | `moveSelected` | 0 | ⬜ |
| `ASSET-002.46` | 714–721 | بلوک | `onPointerOver` | 0 | ⬜ |
| `ASSET-002.47` | 722–728 | بلوک | `focusSelectedOrderRow` | 0 | ⬜ |
| `ASSET-002.48` | 729–739 | بلوک | `selectDesignElement` | 0 | ⬜ |
| `ASSET-002.49` | 740–749 | بلوک | `onDocumentClick` | 0 | ⬜ |
| `ASSET-002.50` | 750–770 | بلوک | `onControlChange` | 0 | ⬜ |
| `ASSET-002.51` | 771–783 | بلوک | `request` | 0 | ⬜ |
| `ASSET-002.52` | 784–810 | بلوک | `saveDesign` | 0 | ⬜ |
| `ASSET-002.53` | 811–825 | بلوک | `resetDesign` | 0 | ⬜ |
| `ASSET-002.54` | 826–843 | بلوک | `closeDesign` | 0 | ⬜ |
| `ASSET-002.55` | 844–898 | بلوک | `openDesign` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-003</b> — <code>assets/app.js</code> (1,466 خط، 66 آدرس، بازبینی‌شده 0/66)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-003.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-003.02` | 3–5 | بلوک | `__itsmBootstrapInit` | 0 | ⬜ |
| `ASSET-003.03` | 6–8 | بلوک | `themeCookie` | 4 | ⬜ |
| `ASSET-003.04` | 9–19 | بلوک | `updateThemeButtons` | 0 | ⬜ |
| `ASSET-003.05` | 20–39 | بلوک | `applyThemeMode` | 4 | ⬜ |
| `ASSET-003.06` | 40–42 | بلوک | `setThemeMode` | 0 | ⬜ |
| `ASSET-003.07` | 43–75 | بلوک | `update` | 1 | ⬜ |
| `ASSET-003.08` | 76–83 | بلوک | `updateBadge` | 0 | ⬜ |
| `ASSET-003.09` | 84–98 | بلوک | `showNotification` | 0 | ⬜ |
| `ASSET-003.10` | 99–135 | بلوک | `pollNotifications` | 4 | ⬜ |
| `ASSET-003.11` | 136–142 | بلوک | `submit` | 0 | ⬜ |
| `ASSET-003.12` | 143–146 | بلوک | `bindCdDvdSurface` | 0 | ⬜ |
| `ASSET-003.13` | 147–166 | بلوک | `loadSurface` | 0 | ⬜ |
| `ASSET-003.14` | 167–178 | بلوک | `submit` | 0 | ⬜ |
| `ASSET-003.15` | 179–185 | بلوک | `bindCdDvdHistorySurface` | 0 | ⬜ |
| `ASSET-003.16` | 186–208 | بلوک | `load` | 0 | ⬜ |
| `ASSET-003.17` | 209–226 | بلوک | `bindSettingsTabs` | 0 | ⬜ |
| `ASSET-003.18` | 227–247 | بلوک | `activate` | 0 | ⬜ |
| `ASSET-003.19` | 248–254 | بلوک | `bindRolesBoard` | 0 | ⬜ |
| `ASSET-003.20` | 255–268 | بلوک | `activate` | 0 | ⬜ |
| `ASSET-003.21` | 269–285 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-003.22` | 286–303 | بلوک | `refreshGroup` | 0 | ⬜ |
| `ASSET-003.23` | 304–317 | بلوک | `syncEdits` | 0 | ⬜ |
| `ASSET-003.24` | 318–432 | بلوک | `refreshChanges` | 0 | ⬜ |
| `ASSET-003.25` | 433–493 | بلوک | `refreshMembers` | 0 | ⬜ |
| `ASSET-003.26` | 494–497 | بلوک | `activeInputs` | 0 | ⬜ |
| `ASSET-003.27` | 498–558 | بلوک | `applyCopy` | 0 | ⬜ |
| `ASSET-003.28` | 559–571 | بلوک | `bindSettingsUserSearch` | 0 | ⬜ |
| `ASSET-003.29` | 572–574 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-003.30` | 575–592 | بلوک | `update` | 1 | ⬜ |
| `ASSET-003.31` | 593–598 | بلوک | `lacNorm` | 0 | ⬜ |
| `ASSET-003.32` | 599–627 | بلوک | `lacFilter` | 0 | ⬜ |
| `ASSET-003.33` | 628–647 | بلوک | `getGroup` | 0 | ⬜ |
| `ASSET-003.34` | 648–677 | بلوک | `rebuildServiceSelect` | 0 | ⬜ |
| `ASSET-003.35` | 678–682 | بلوک | `toggleServiceFields` | 0 | ⬜ |
| `ASSET-003.36` | 683–686 | بلوک | `updateTicketNumber` | 0 | ⬜ |
| `ASSET-003.37` | 687–695 | بلوک | `filterCategories` | 0 | ⬜ |
| `ASSET-003.38` | 696–759 | بلوک | `syncTicketForm` | 0 | ⬜ |
| `ASSET-003.39` | 760–774 | بلوک | `comboNormalize` | 0 | ⬜ |
| `ASSET-003.40` | 775–780 | بلوک | `comboClose` | 0 | ⬜ |
| `ASSET-003.41` | 781–787 | بلوک | `comboHighlight` | 0 | ⬜ |
| `ASSET-003.42` | 788–803 | بلوک | `comboPick` | 0 | ⬜ |
| `ASSET-003.43` | 804–944 | بلوک | `comboRender` | 1 | ⬜ |
| `ASSET-003.44` | 945–945 | بلوک | `pad` | 0 | ⬜ |
| `ASSET-003.45` | 946–963 | بلوک | `jalaliToGregorian` | 0 | ⬜ |
| `ASSET-003.46` | 964–969 | بلوک | `leap` | 0 | ⬜ |
| `ASSET-003.47` | 970–991 | بلوک | `gregorianToJalali` | 0 | ⬜ |
| `ASSET-003.48` | 992–998 | بلوک | `monthLength` | 0 | ⬜ |
| `ASSET-003.49` | 999–1007 | بلوک | `parseJalali` | 0 | ⬜ |
| `ASSET-003.50` | 1008–1010 | بلوک | `renderCalendar` | 0 | ⬜ |
| `ASSET-003.51` | 1011–1016 | بلوک | `startIndex` | 0 | ⬜ |
| `ASSET-003.52` | 1017–1044 | بلوک | `weekdayIndex` | 0 | ⬜ |
| `ASSET-003.53` | 1045–1057 | بلوک | `activate` | 4 | ⬜ |
| `ASSET-003.54` | 1058–1072 | بلوک | `hash` | 0 | ⬜ |
| `ASSET-003.55` | 1073–1078 | بلوک | `visibleBoxes` | 0 | ⬜ |
| `ASSET-003.56` | 1079–1105 | بلوک | `sync` | 0 | ⬜ |
| `ASSET-003.57` | 1106–1116 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-003.58` | 1117–1153 | بلوک | `apply` | 0 | ⬜ |
| `ASSET-003.59` | 1154–1186 | بلوک | `updateSelection` | 0 | ⬜ |
| `ASSET-003.60` | 1187–1248 | بلوک | `syncOrgFields` | 1 | ⬜ |
| `ASSET-003.61` | 1249–1286 | بلوک | `closeModal` | 0 | ⬜ |
| `ASSET-003.62` | 1287–1309 | بلوک | `activate` | 0 | ⬜ |
| `ASSET-003.63` | 1310–1353 | بلوک | `syncItem` | 0 | ⬜ |
| `ASSET-003.64` | 1354–1360 | بلوک | `setBadge` | 0 | ⬜ |
| `ASSET-003.65` | 1361–1378 | بلوک | `refreshOnlineTotal` | 0 | ⬜ |
| `ASSET-003.66` | 1379–1466 | بلوک | `next` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-004</b> — <code>assets/cd-dvd.js</code> (354 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-004.01` | 1–43 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-004.02` | 44–47 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-004.03` | 48–59 | بلوک | `filterUsers` | 0 | ⬜ |
| `ASSET-004.04` | 60–78 | بلوک | `selectUser` | 0 | ⬜ |
| `ASSET-004.05` | 79–114 | بلوک | `initializeUserPicker` | 0 | ⬜ |
| `ASSET-004.06` | 115–118 | بلوک | `getSelectedMedia` | 0 | ⬜ |
| `ASSET-004.07` | 119–122 | بلوک | `mediaBounds` | 0 | ⬜ |
| `ASSET-004.08` | 123–198 | بلوک | `updateForm` | 0 | ⬜ |
| `ASSET-004.09` | 199–258 | بلوک | `handleMediaChange` | 0 | ⬜ |
| `ASSET-004.10` | 259–288 | بلوک | `activateTab` | 0 | ⬜ |
| `ASSET-004.11` | 289–303 | بلوک | `printCdDvdList` | 0 | ⬜ |
| `ASSET-004.12` | 304–321 | بلوک | `cleanup` | 0 | ⬜ |
| `ASSET-004.13` | 322–354 | بلوک | `printCdDvdHistory` | 4 | ⬜ |

</details>

<details>
<summary><b>ASSET-005</b> — <code>assets/chatbot-drag.js</code> (174 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-005.01` | 1–23 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-005.02` | 24–28 | بلوک | `size` | 0 | ⬜ |
| `ASSET-005.03` | 29–34 | بلوک | `clampTop` | 0 | ⬜ |
| `ASSET-005.04` | 35–40 | بلوک | `clampLeft` | 0 | ⬜ |
| `ASSET-005.05` | 41–47 | بلوک | `place` | 0 | ⬜ |
| `ASSET-005.06` | 48–56 | بلوک | `snapToSide` | 0 | ⬜ |
| `ASSET-005.07` | 57–61 | بلوک | `positionPanel` | 0 | ⬜ |
| `ASSET-005.08` | 62–136 | بلوک | `onRight` | 0 | ⬜ |
| `ASSET-005.09` | 137–162 | بلوک | `endDrag` | 1 | ⬜ |
| `ASSET-005.10` | 163–174 | بلوک | `onRight` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-006</b> — <code>assets/custom.css</code> (218 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-006.01` | 1–218 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |

</details>

<details>
<summary><b>ASSET-007</b> — <code>assets/food-menu.css</code> (354 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-007.01` | 1–227 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `ASSET-007.02` | 228–240 | بلوک | `لیست کارکنان (غیبت و اعضای گروه): ۴ ستون، تیک قبل ` | 3 | ⬜ |
| `ASSET-007.03` | 241–244 | بلوک | `جعبهٔ انتخاب غذا: هم‌اندازهٔ فرم غیبت` | 0 | ⬜ |
| `ASSET-007.04` | 245–354 | بلوک | `بانک غذا: هر غذا در یک خط (نام \| دسته‌بندی \| وضع` | 3 | ⬜ |

</details>

<details>
<summary><b>ASSET-008</b> — <code>assets/food-menu.js</code> (2,439 خط، 149 آدرس، بازبینی‌شده 0/149)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-008.01` | 1–100 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-008.02` | 101–105 | بلوک | `esc` | 0 | ⬜ |
| `ASSET-008.03` | 106–106 | بلوک | `faDigits` | 0 | ⬜ |
| `ASSET-008.04` | 107–111 | بلوک | `asciiDigits` | 0 | ⬜ |
| `ASSET-008.05` | 112–121 | بلوک | `isoFromJalali` | 0 | ⬜ |
| `ASSET-008.06` | 122–128 | بلوک | `jalaliFromIso` | 0 | ⬜ |
| `ASSET-008.07` | 129–135 | بلوک | `normalizeIsoDate` | 0 | ⬜ |
| `ASSET-008.08` | 136–139 | بلوک | `jalaliMonthFromIso` | 0 | ⬜ |
| `ASSET-008.09` | 140–143 | بلوک | `localTodayIso` | 0 | ⬜ |
| `ASSET-008.10` | 144–144 | بلوک | `monthKey` | 0 | ⬜ |
| `ASSET-008.11` | 145–145 | بلوک | `monthTitle` | 0 | ⬜ |
| `ASSET-008.12` | 146–146 | بلوک | `getDay` | 0 | ⬜ |
| `ASSET-008.13` | 147–150 | بلوک | `activeOrderCount` | 0 | ⬜ |
| `ASSET-008.14` | 151–160 | بلوک | `holiday` | 0 | ⬜ |
| `ASSET-008.15` | 161–166 | بلوک | `buildUrl` | 0 | ⬜ |
| `ASSET-008.16` | 167–184 | بلوک | `request` | 0 | ⬜ |
| `ASSET-008.17` | 185–191 | بلوک | `notify` | 0 | ⬜ |
| `ASSET-008.18` | 192–197 | بلوک | `navTabs` | 0 | ⬜ |
| `ASSET-008.19` | 198–207 | بلوک | `cancelCount` | 0 | ⬜ |
| `ASSET-008.20` | 208–237 | بلوک | `renderCalendar` | 0 | ⬜ |
| `ASSET-008.21` | 238–255 | بلوک | `renderDayEditor` | 0 | ⬜ |
| `ASSET-008.22` | 256–265 | بلوک | `choiceHtml` | 0 | ⬜ |
| `ASSET-008.23` | 266–291 | بلوک | `foods` | 0 | ⬜ |
| `ASSET-008.24` | 292–297 | بلوک | `categoryOptions` | 0 | ⬜ |
| `ASSET-008.25` | 298–306 | بلوک | `catalogCategoryNames` | 0 | ⬜ |
| `ASSET-008.26` | 307–316 | بلوک | `foodCardHtml` | 0 | ⬜ |
| `ASSET-008.27` | 317–358 | بلوک | `renderCatalog` | 0 | ⬜ |
| `ASSET-008.28` | 359–368 | بلوک | `readSavedOrderReportFields` | 0 | ⬜ |
| `ASSET-008.29` | 369–374 | بلوک | `saveOrderReportFields` | 4 | ⬜ |
| `ASSET-008.30` | 375–379 | بلوک | `selectedOrderColumns` | 0 | ⬜ |
| `ASSET-008.31` | 380–396 | بلوک | `orderFieldValue` | 0 | ⬜ |
| `ASSET-008.32` | 397–411 | بلوک | `orderSortValue` | 0 | ⬜ |
| `ASSET-008.33` | 412–427 | بلوک | `sortOrderRows` | 0 | ⬜ |
| `ASSET-008.34` | 428–465 | بلوک | `orderTableHtml` | 0 | ⬜ |
| `ASSET-008.35` | 466–501 | بلوک | `orderByMenuHtml` | 0 | ⬜ |
| `ASSET-008.36` | 502–511 | بلوک | `readDeliveredReportFields` | 0 | ⬜ |
| `ASSET-008.37` | 512–516 | بلوک | `saveDeliveredReportFields` | 4 | ⬜ |
| `ASSET-008.38` | 517–530 | بلوک | `deliveredReportFieldValue` | 0 | ⬜ |
| `ASSET-008.39` | 531–534 | بلوک | `selectedDeliveredReportColumns` | 0 | ⬜ |
| `ASSET-008.40` | 535–547 | بلوک | `sortDeliveredReportRows` | 0 | ⬜ |
| `ASSET-008.41` | 548–578 | بلوک | `deliveredReportTableHtml` | 0 | ⬜ |
| `ASSET-008.42` | 579–581 | بلوک | `deliveredReportByMenuHtml` | 0 | ⬜ |
| `ASSET-008.43` | 582–603 | بلوک | `key` | 0 | ⬜ |
| `ASSET-008.44` | 604–608 | بلوک | `deliveredReportOrderedRows` | 0 | ⬜ |
| `ASSET-008.45` | 609–621 | بلوک | `key` | 0 | ⬜ |
| `ASSET-008.46` | 622–625 | بلوک | `deliveryDateForReport` | 0 | ⬜ |
| `ASSET-008.47` | 626–645 | بلوک | `renderPrintedDeliveryReport` | 0 | ⬜ |
| `ASSET-008.48` | 646–681 | بلوک | `loadPrintedDeliveryReport` | 0 | ⬜ |
| `ASSET-008.49` | 682–698 | بلوک | `exportPrintedDeliveryReport` | 0 | ⬜ |
| `ASSET-008.50` | 699–703 | بلوک | `printPrintedDeliveryReport` | 0 | ⬜ |
| `ASSET-008.51` | 704–709 | بلوک | `summaryHtml` | 0 | ⬜ |
| `ASSET-008.52` | 710–713 | بلوک | `rowSummary` | 0 | ⬜ |
| `ASSET-008.53` | 714–719 | بلوک | `renderOrders` | 0 | ⬜ |
| `ASSET-008.54` | 720–735 | بلوک | `viewButton` | 0 | ⬜ |
| `ASSET-008.55` | 736–744 | بلوک | `renderGroupOrders` | 0 | ⬜ |
| `ASSET-008.56` | 745–769 | بلوک | `tableFor` | 0 | ⬜ |
| `ASSET-008.57` | 770–786 | بلوک | `renderCancelRequests` | 0 | ⬜ |
| `ASSET-008.58` | 787–830 | بلوک | `renderStatistics` | 0 | ⬜ |
| `ASSET-008.59` | 831–856 | بلوک | `foods` | 0 | ⬜ |
| `ASSET-008.60` | 857–865 | بلوک | `normalizeJalaliMonth` | 0 | ⬜ |
| `ASSET-008.61` | 866–869 | بلوک | `reconciliationNumber` | 0 | ⬜ |
| `ASSET-008.62` | 870–874 | بلوک | `reconciliationAmount` | 0 | ⬜ |
| `ASSET-008.63` | 875–952 | بلوک | `renderReconciliation` | 0 | ⬜ |
| `ASSET-008.64` | 953–993 | بلوک | `loadReconciliation` | 0 | ⬜ |
| `ASSET-008.65` | 994–999 | بلوک | `downloadReconciliation` | 0 | ⬜ |
| `ASSET-008.66` | 1000–1000 | بلوک | `selectReconciliationRow` | 0 | ⬜ |
| `ASSET-008.67` | 1001–1021 | بلوک | `item` | 0 | ⬜ |
| `ASSET-008.68` | 1022–1066 | بلوک | `saveReconciliationForm` | 0 | ⬜ |
| `ASSET-008.69` | 1067–1103 | بلوک | `uploadReconciliationInvoice` | 0 | ⬜ |
| `ASSET-008.70` | 1104–1107 | بلوک | `bulkOperationTitle` | 0 | ⬜ |
| `ASSET-008.71` | 1108–1112 | بلوک | `bulkSelectedPeople` | 0 | ⬜ |
| `ASSET-008.72` | 1113–1116 | بلوک | `bulkTargetValue` | 0 | ⬜ |
| `ASSET-008.73` | 1117–1123 | بلوک | `bulkCanSelect` | 0 | ⬜ |
| `ASSET-008.74` | 1124–1134 | بلوک | `group` | 0 | ⬜ |
| `ASSET-008.75` | 1135–1162 | بلوک | `loadBulkSnapshot` | 0 | ⬜ |
| `ASSET-008.76` | 1163–1177 | بلوک | `bulkTargetOptions` | 0 | ⬜ |
| `ASSET-008.77` | 1178–1193 | بلوک | `bulkPersonStatus` | 0 | ⬜ |
| `ASSET-008.78` | 1194–1215 | بلوک | `group` | 0 | ⬜ |
| `ASSET-008.79` | 1216–1221 | بلوک | `renderBulkResults` | 0 | ⬜ |
| `ASSET-008.80` | 1222–1229 | بلوک | `renderBulkModal` | 0 | ⬜ |
| `ASSET-008.81` | 1230–1244 | بلوک | `renderBulkOperations` | 0 | ⬜ |
| `ASSET-008.82` | 1245–1250 | بلوک | `rows` | 0 | ⬜ |
| `ASSET-008.83` | 1251–1263 | بلوک | `selected` | 0 | ⬜ |
| `ASSET-008.84` | 1264–1274 | بلوک | `previewDisabled` | 0 | ⬜ |
| `ASSET-008.85` | 1275–1285 | بلوک | `bulkTogglePerson` | 1 | ⬜ |
| `ASSET-008.86` | 1286–1287 | بلوک | `bulkSelectEligible` | 0 | ⬜ |
| `ASSET-008.87` | 1288–1293 | بلوک | `eligible` | 0 | ⬜ |
| `ASSET-008.88` | 1294–1296 | بلوک | `submitBulkOperation` | 0 | ⬜ |
| `ASSET-008.89` | 1297–1316 | بلوک | `ids` | 0 | ⬜ |
| `ASSET-008.90` | 1317–1322 | بلوک | `mealTypeSwitch` | 0 | ⬜ |
| `ASSET-008.91` | 1323–1330 | بلوک | `embeddedPanel` | 0 | ⬜ |
| `ASSET-008.92` | 1331–1345 | بلوک | `loadEmbedded` | 0 | ⬜ |
| `ASSET-008.93` | 1346–1357 | بلوک | `render` | 0 | ⬜ |
| `ASSET-008.94` | 1358–1362 | بلوک | `notices` | 0 | ⬜ |
| `ASSET-008.95` | 1363–1374 | بلوک | `loadStatus` | 0 | ⬜ |
| `ASSET-008.96` | 1375–1381 | بلوک | `loadCatalog` | 0 | ⬜ |
| `ASSET-008.97` | 1382–1389 | بلوک | `loadMonth` | 0 | ⬜ |
| `ASSET-008.98` | 1390–1411 | بلوک | `loadOrders` | 0 | ⬜ |
| `ASSET-008.99` | 1412–1432 | بلوک | `loadFoodGroups` | 0 | ⬜ |
| `ASSET-008.100` | 1433–1463 | بلوک | `loadGroupOrders` | 0 | ⬜ |
| `ASSET-008.101` | 1464–1480 | بلوک | `loadCancelRequests` | 0 | ⬜ |
| `ASSET-008.102` | 1481–1499 | بلوک | `loadGuestRequests` | 0 | ⬜ |
| `ASSET-008.103` | 1500–1500 | بلوک | `beginGuestEdit` | 0 | ⬜ |
| `ASSET-008.104` | 1501–1516 | بلوک | `row` | 0 | ⬜ |
| `ASSET-008.105` | 1517–1522 | بلوک | `cancelGuestEdit` | 0 | ⬜ |
| `ASSET-008.106` | 1523–1554 | بلوک | `saveGuestEdit` | 1 | ⬜ |
| `ASSET-008.107` | 1555–1555 | بلوک | `guestRequestAction` | 0 | ⬜ |
| `ASSET-008.108` | 1556–1578 | بلوک | `row` | 1 | ⬜ |
| `ASSET-008.109` | 1579–1581 | بلوک | `renderGuestRequestManager` | 0 | ⬜ |
| `ASSET-008.110` | 1582–1604 | بلوک | `rows` | 1 | ⬜ |
| `ASSET-008.111` | 1605–1619 | بلوک | `decideCancelRequest` | 0 | ⬜ |
| `ASSET-008.112` | 1620–1626 | بلوک | `loadStatistics` | 0 | ⬜ |
| `ASSET-008.113` | 1627–1660 | بلوک | `selectedIds` | 0 | ⬜ |
| `ASSET-008.114` | 1661–1688 | بلوک | `searchSelectedReportEmployees` | 0 | ⬜ |
| `ASSET-008.115` | 1689–1715 | بلوک | `refreshAll` | 0 | ⬜ |
| `ASSET-008.116` | 1716–1760 | بلوک | `saveDay` | 0 | ⬜ |
| `ASSET-008.117` | 1761–1786 | بلوک | `saveCatalog` | 0 | ⬜ |
| `ASSET-008.118` | 1787–1806 | بلوک | `orderExportRows` | 0 | ⬜ |
| `ASSET-008.119` | 1807–1823 | بلوک | `downloadOrdersCsv` | 0 | ⬜ |
| `ASSET-008.120` | 1824–1828 | بلوک | `downloadOrders` | 0 | ⬜ |
| `ASSET-008.121` | 1829–1830 | بلوک | `printScopedReport` | 0 | ⬜ |
| `ASSET-008.122` | 1831–1841 | بلوک | `target` | 0 | ⬜ |
| `ASSET-008.123` | 1842–1859 | بلوک | `cleanup` | 0 | ⬜ |
| `ASSET-008.124` | 1860–1864 | بلوک | `printOrders` | 0 | ⬜ |
| `ASSET-008.125` | 1865–1871 | بلوک | `downloadStatistics` | 0 | ⬜ |
| `ASSET-008.126` | 1872–1876 | بلوک | `printStatistics` | 0 | ⬜ |
| `ASSET-008.127` | 1877–1884 | بلوک | `selectDay` | 0 | ⬜ |
| `ASSET-008.128` | 1885–1890 | بلوک | `changeMonth` | 0 | ⬜ |
| `ASSET-008.129` | 1891–1927 | بلوک | `switchTab` | 0 | ⬜ |
| `ASSET-008.130` | 1928–1941 | بلوک | `emergencyEnsurePeople` | 0 | ⬜ |
| `ASSET-008.131` | 1942–1950 | بلوک | `emergencyPanel` | 0 | ⬜ |
| `ASSET-008.132` | 1951–1985 | بلوک | `msgs` | 0 | ⬜ |
| `ASSET-008.133` | 1986–1991 | بلوک | `emergencyLoadOrders` | 0 | ⬜ |
| `ASSET-008.134` | 1992–2036 | بلوک | `daySearchInput` | 4 | ⬜ |
| `ASSET-008.135` | 2037–2047 | بلوک | `emergencyClick` | 0 | ⬜ |
| `ASSET-008.136` | 2048–2076 | بلوک | `person` | 0 | ⬜ |
| `ASSET-008.137` | 2077–2115 | بلوک | `eventClick` | 0 | ⬜ |
| `ASSET-008.138` | 2116–2211 | بلوک | `person` | 1 | ⬜ |
| `ASSET-008.139` | 2212–2220 | بلوک | `dayPickFor` | 0 | ⬜ |
| `ASSET-008.140` | 2221–2227 | بلوک | `trackDayPick` | 0 | ⬜ |
| `ASSET-008.141` | 2228–2235 | بلوک | `orderedDayFoodIds` | 0 | ⬜ |
| `ASSET-008.142` | 2236–2259 | بلوک | `eventChange` | 0 | ⬜ |
| `ASSET-008.143` | 2260–2293 | بلوک | `person` | 0 | ⬜ |
| `ASSET-008.144` | 2294–2376 | بلوک | `eventSubmit` | 0 | ⬜ |
| `ASSET-008.145` | 2377–2382 | بلوک | `spanDays` | 0 | ⬜ |
| `ASSET-008.146` | 2383–2411 | بلوک | `mount` | 0 | ⬜ |
| `ASSET-008.147` | 2412–2412 | بلوک | `reportInputFocused` | 0 | ⬜ |
| `ASSET-008.148` | 2413–2429 | بلوک | `safeReportRender` | 0 | ⬜ |
| `ASSET-008.149` | 2430–2439 | بلوک | `html` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-009</b> — <code>assets/food-order-calendar.js</code> (103 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-009.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-009.02` | 8–14 | بلوک | `jalali` | 0 | ⬜ |
| `ASSET-009.03` | 15–16 | بلوک | `pad` | 0 | ⬜ |
| `ASSET-009.04` | 17–22 | بلوک | `isoDate` | 0 | ⬜ |
| `ASSET-009.05` | 23–28 | بلوک | `jalaliDateFromIso` | 0 | ⬜ |
| `ASSET-009.06` | 29–38 | بلوک | `monthLength` | 0 | ⬜ |
| `ASSET-009.07` | 39–42 | بلوک | `previousMonth` | 0 | ⬜ |
| `ASSET-009.08` | 43–46 | بلوک | `nextMonth` | 0 | ⬜ |
| `ASSET-009.09` | 47–52 | بلوک | `monthCells` | 0 | ⬜ |
| `ASSET-009.10` | 53–83 | بلوک | `leading` | 0 | ⬜ |
| `ASSET-009.11` | 84–84 | بلوک | `shiftMonth` | 0 | ⬜ |
| `ASSET-009.12` | 85–86 | بلوک | `total` | 0 | ⬜ |
| `ASSET-009.13` | 87–103 | بلوک | `m` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-010</b> — <code>assets/food-order-guest.js</code> (310 خط، 18 آدرس، بازبینی‌شده 0/18)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-010.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-010.02` | 8–27 | بلوک | `apiBase` | 0 | ⬜ |
| `ASSET-010.03` | 28–32 | بلوک | `esc` | 0 | ⬜ |
| `ASSET-010.04` | 33–33 | بلوک | `faDigits` | 0 | ⬜ |
| `ASSET-010.05` | 34–38 | بلوک | `normDigits` | 0 | ⬜ |
| `ASSET-010.06` | 39–53 | بلوک | `request` | 0 | ⬜ |
| `ASSET-010.07` | 54–64 | بلوک | `toast` | 0 | ⬜ |
| `ASSET-010.08` | 65–78 | بلوک | `readForm` | 0 | ⬜ |
| `ASSET-010.09` | 79–85 | بلوک | `render` | 0 | ⬜ |
| `ASSET-010.10` | 86–153 | بلوک | `mealCount` | 1 | ⬜ |
| `ASSET-010.11` | 154–179 | بلوک | `ensureDialog` | 1 | ⬜ |
| `ASSET-010.12` | 180–200 | بلوک | `load` | 0 | ⬜ |
| `ASSET-010.13` | 201–238 | بلوک | `save` | 1 | ⬜ |
| `ASSET-010.14` | 239–249 | بلوک | `cancel` | 0 | ⬜ |
| `ASSET-010.15` | 250–267 | بلوک | `beginEdit` | 0 | ⬜ |
| `ASSET-010.16` | 268–274 | بلوک | `cancelEdit` | 0 | ⬜ |
| `ASSET-010.17` | 275–288 | بلوک | `removeRequest` | 1 | ⬜ |
| `ASSET-010.18` | 289–310 | بلوک | `open` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-011</b> — <code>assets/food-order.css</code> (213 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-011.01` | 1–213 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>ASSET-012</b> — <code>assets/food-order.js</code> (1,115 خط، 64 آدرس، بازبینی‌شده 0/64)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-012.01` | 1–14 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-012.02` | 15–63 | بلوک | `apiBase` | 0 | ⬜ |
| `ASSET-012.03` | 64–68 | بلوک | `esc` | 0 | ⬜ |
| `ASSET-012.04` | 69–69 | بلوک | `faDigits` | 0 | ⬜ |
| `ASSET-012.05` | 70–75 | بلوک | `normalizedDigits` | 0 | ⬜ |
| `ASSET-012.06` | 76–79 | بلوک | `normalizeSearch` | 0 | ⬜ |
| `ASSET-012.07` | 80–85 | بلوک | `employeeMatches` | 0 | ⬜ |
| `ASSET-012.08` | 86–86 | بلوک | `monthKey` | 0 | ⬜ |
| `ASSET-012.09` | 87–87 | بلوک | `monthLabel` | 0 | ⬜ |
| `ASSET-012.10` | 88–93 | بلوک | `isoToJalali` | 0 | ⬜ |
| `ASSET-012.11` | 94–102 | بلوک | `holidayTitle` | 0 | ⬜ |
| `ASSET-012.12` | 103–105 | بلوک | `isPast` | 0 | ⬜ |
| `ASSET-012.13` | 106–118 | بلوک | `request` | 0 | ⬜ |
| `ASSET-012.14` | 119–128 | بلوک | `toast` | 0 | ⬜ |
| `ASSET-012.15` | 129–132 | بلوک | `visitParams` | 0 | ⬜ |
| `ASSET-012.16` | 133–140 | بلوک | `visitPayload` | 0 | ⬜ |
| `ASSET-012.17` | 141–152 | بلوک | `loadVisit` | 0 | ⬜ |
| `ASSET-012.18` | 153–159 | بلوک | `openVisitDialog` | 0 | ⬜ |
| `ASSET-012.19` | 160–163 | بلوک | `closeVisitDialog` | 0 | ⬜ |
| `ASSET-012.20` | 164–180 | بلوک | `saveVisit` | 0 | ⬜ |
| `ASSET-012.21` | 181–194 | بلوک | `cancelVisit` | 0 | ⬜ |
| `ASSET-012.22` | 195–246 | بلوک | `renderVisitDialog` | 0 | ⬜ |
| `ASSET-012.23` | 247–266 | بلوک | `loadMonth` | 0 | ⬜ |
| `ASSET-012.24` | 267–283 | بلوک | `loadMyOrders` | 0 | ⬜ |
| `ASSET-012.25` | 284–313 | بلوک | `loadProxyOrders` | 0 | ⬜ |
| `ASSET-012.26` | 314–321 | بلوک | `applyListRange` | 0 | ⬜ |
| `ASSET-012.27` | 322–335 | بلوک | `renderListFilter` | 0 | ⬜ |
| `ASSET-012.28` | 336–345 | بلوک | `dayStatus` | 0 | ⬜ |
| `ASSET-012.29` | 346–351 | بلوک | `guestOrderButton` | 0 | ⬜ |
| `ASSET-012.30` | 352–384 | بلوک | `renderCalendar` | 0 | ⬜ |
| `ASSET-012.31` | 385–397 | بلوک | `renderOrders` | 0 | ⬜ |
| `ASSET-012.32` | 398–399 | بلوک | `renderCancelRequestControl` | 0 | ⬜ |
| `ASSET-012.33` | 400–416 | بلوک | `closed` | 0 | ⬜ |
| `ASSET-012.34` | 417–428 | بلوک | `renderProxyCancelControl` | 0 | ⬜ |
| `ASSET-012.35` | 429–430 | بلوک | `renderProxyOrders` | 0 | ⬜ |
| `ASSET-012.36` | 431–462 | بلوک | `rows` | 0 | ⬜ |
| `ASSET-012.37` | 463–466 | بلوک | `currentDay` | 0 | ⬜ |
| `ASSET-012.38` | 467–476 | بلوک | `modalReadOnlyMessage` | 0 | ⬜ |
| `ASSET-012.39` | 477–488 | بلوک | `renderEmployeeChoices` | 0 | ⬜ |
| `ASSET-012.40` | 489–512 | بلوک | `renderRecipientDialog` | 0 | ⬜ |
| `ASSET-012.41` | 513–523 | بلوک | `renderRecipientBanner` | 0 | ⬜ |
| `ASSET-012.42` | 524–565 | بلوک | `renderModal` | 0 | ⬜ |
| `ASSET-012.43` | 566–600 | بلوک | `render` | 0 | ⬜ |
| `ASSET-012.44` | 601–606 | بلوک | `setMonth` | 0 | ⬜ |
| `ASSET-012.45` | 607–615 | بلوک | `openDay` | 0 | ⬜ |
| `ASSET-012.46` | 616–624 | بلوک | `closeModal` | 0 | ⬜ |
| `ASSET-012.47` | 625–629 | بلوک | `setModalError` | 0 | ⬜ |
| `ASSET-012.48` | 630–640 | بلوک | `focusSearchInput` | 4 | ⬜ |
| `ASSET-012.49` | 641–658 | بلوک | `activateRecipient` | 0 | ⬜ |
| `ASSET-012.50` | 659–678 | بلوک | `startRecipientChoice` | 0 | ⬜ |
| `ASSET-012.51` | 679–697 | بلوک | `dismissRecipientFlow` | 0 | ⬜ |
| `ASSET-012.52` | 698–715 | بلوک | `beginProxyChoice` | 0 | ⬜ |
| `ASSET-012.53` | 716–735 | بلوک | `loadEmployeeChoices` | 0 | ⬜ |
| `ASSET-012.54` | 736–749 | بلوک | `selectProxyPerson` | 0 | ⬜ |
| `ASSET-012.55` | 750–769 | بلوک | `updateProxySearch` | 4 | ⬜ |
| `ASSET-012.56` | 770–799 | بلوک | `confirmProxyRecipient` | 0 | ⬜ |
| `ASSET-012.57` | 800–843 | بلوک | `saveOrder` | 0 | ⬜ |
| `ASSET-012.58` | 844–855 | بلوک | `requestProxyCancelConfirmation` | 0 | ⬜ |
| `ASSET-012.59` | 856–861 | بلوک | `dismissProxyCancelConfirmation` | 0 | ⬜ |
| `ASSET-012.60` | 862–894 | بلوک | `cancelProxyOrder` | 0 | ⬜ |
| `ASSET-012.61` | 895–908 | بلوک | `cancelOrder` | 0 | ⬜ |
| `ASSET-012.62` | 909–911 | بلوک | `markCancelPending` | 0 | ⬜ |
| `ASSET-012.63` | 912–923 | بلوک | `patch` | 0 | ⬜ |
| `ASSET-012.64` | 924–1115 | بلوک | `requestCancelOrder` | 5 | ⬜ |

</details>

<details>
<summary><b>ASSET-013</b> — <code>assets/inspection-food-reminder.js</code> (114 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-013.01` | 1–17 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-013.02` | 18–21 | بلوک | `nowSeconds` | 0 | ⬜ |
| `ASSET-013.03` | 22–39 | بلوک | `dayKey` | 0 | ⬜ |
| `ASSET-013.04` | 40–47 | بلوک | `wasShown` | 0 | ⬜ |
| `ASSET-013.05` | 48–53 | بلوک | `markShown` | 4 | ⬜ |
| `ASSET-013.06` | 54–76 | بلوک | `playAlarm` | 4 | ⬜ |
| `ASSET-013.07` | 77–86 | بلوک | `show` | 0 | ⬜ |
| `ASSET-013.08` | 87–92 | بلوک | `hide` | 0 | ⬜ |
| `ASSET-013.09` | 93–114 | بلوک | `schedule` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-014</b> — <code>assets/jalali-calendar.js</code> (335 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-014.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-014.02` | 13–14 | بلوک | `pad` | 0 | ⬜ |
| `ASSET-014.03` | 15–44 | بلوک | `j2g` | 0 | ⬜ |
| `ASSET-014.04` | 45–66 | بلوک | `g2j` | 0 | ⬜ |
| `ASSET-014.05` | 67–73 | بلوک | `monthLength` | 0 | ⬜ |
| `ASSET-014.06` | 74–81 | بلوک | `parseJalali` | 0 | ⬜ |
| `ASSET-014.07` | 82–85 | بلوک | `formatJalali` | 0 | ⬜ |
| `ASSET-014.08` | 86–95 | بلوک | `holidaySet` | 0 | ⬜ |
| `ASSET-014.09` | 96–103 | بلوک | `isWeekendJalali` | 0 | ⬜ |
| `ASSET-014.10` | 104–108 | بلوک | `todayJ` | 0 | ⬜ |
| `ASSET-014.11` | 109–122 | بلوک | `bindPicker` | 0 | ⬜ |
| `ASSET-014.12` | 123–123 | بلوک | `close` | 0 | ⬜ |
| `ASSET-014.13` | 124–125 | بلوک | `open` | 0 | ⬜ |
| `ASSET-014.14` | 126–131 | بلوک | `render` | 0 | ⬜ |
| `ASSET-014.15` | 132–203 | بلوک | `startIndex` | 0 | ⬜ |
| `ASSET-014.16` | 204–215 | بلوک | `installOutsideClickHandler` | 0 | ⬜ |
| `ASSET-014.17` | 216–222 | بلوک | `bindYearBoard` | 0 | ⬜ |
| `ASSET-014.18` | 223–231 | بلوک | `render` | 0 | ⬜ |
| `ASSET-014.19` | 232–268 | بلوک | `startIndex` | 0 | ⬜ |
| `ASSET-014.20` | 269–335 | بلوک | `enhanceAllDateFields` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-015</b> — <code>assets/list-sort.js</code> (323 خط، 23 آدرس، بازبینی‌شده 0/23)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-015.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-015.02` | 16–19 | بلوک | `collator` | 0 | ⬜ |
| `ASSET-015.03` | 20–32 | بلوک | `injectStyle` | 0 | ⬜ |
| `ASSET-015.04` | 33–41 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-015.05` | 42–44 | بلوک | `pad` | 0 | ⬜ |
| `ASSET-015.06` | 45–55 | بلوک | `sortKey` | 4 | ⬜ |
| `ASSET-015.07` | 56–64 | بلوک | `compare` | 0 | ⬜ |
| `ASSET-015.08` | 65–84 | بلوک | `collect` | 0 | ⬜ |
| `ASSET-015.09` | 85–98 | بلوک | `hasAny` | 0 | ⬜ |
| `ASSET-015.10` | 99–112 | بلوک | `anchorFor` | 0 | ⬜ |
| `ASSET-015.11` | 113–140 | بلوک | `applySort` | 0 | ⬜ |
| `ASSET-015.12` | 141–156 | بلوک | `updateHeaders` | 0 | ⬜ |
| `ASSET-015.13` | 157–190 | بلوک | `attach` | 0 | ⬜ |
| `ASSET-015.14` | 191–205 | بلوک | `enhance` | 0 | ⬜ |
| `ASSET-015.15` | 206–215 | بلوک | `scheduleEnhance` | 0 | ⬜ |
| `ASSET-015.16` | 216–234 | بلوک | `buildView` | 0 | ⬜ |
| `ASSET-015.17` | 235–241 | بلوک | `fileLabel` | 0 | ⬜ |
| `ASSET-015.18` | 242–252 | بلوک | `saveBlob` | 0 | ⬜ |
| `ASSET-015.19` | 253–269 | بلوک | `exportXlsx` | 0 | ⬜ |
| `ASSET-015.20` | 270–277 | بلوک | `exportContainer` | 0 | ⬜ |
| `ASSET-015.21` | 278–291 | بلوک | `activeListFor` | 0 | ⬜ |
| `ASSET-015.22` | 292–302 | بلوک | `onExportClick` | 0 | ⬜ |
| `ASSET-015.23` | 303–323 | بلوک | `start` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-016</b> — <code>assets/logging-dashboard.css</code> (6 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-016.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>ASSET-017</b> — <code>assets/logging-dashboard.js</code> (20 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-017.01` | 1–20 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-018</b> — <code>assets/organization.js</code> (1,036 خط، 104 آدرس، بازبینی‌شده 0/104)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-018.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-018.02` | 6–7 | بلوک | `users` | 0 | ⬜ |
| `ASSET-018.03` | 8–8 | بلوک | `$` | 0 | ⬜ |
| `ASSET-018.04` | 9–9 | بلوک | `$$` | 0 | ⬜ |
| `ASSET-018.05` | 10–14 | بلوک | `esc` | 0 | ⬜ |
| `ASSET-018.06` | 15–15 | بلوک | `initial` | 0 | ⬜ |
| `ASSET-018.07` | 16–37 | بلوک | `iconFor` | 1 | ⬜ |
| `ASSET-018.08` | 38–38 | بلوک | `unit` | 0 | ⬜ |
| `ASSET-018.09` | 39–39 | بلوک | `person` | 0 | ⬜ |
| `ASSET-018.10` | 40–40 | بلوک | `rootType` | 0 | ⬜ |
| `ASSET-018.11` | 41–41 | بلوک | `rootLabel` | 0 | ⬜ |
| `ASSET-018.12` | 42–48 | بلوک | `root` | 4 | ⬜ |
| `ASSET-018.13` | 49–59 | بلوک | `freeNodeKey` | 0 | ⬜ |
| `ASSET-018.14` | 60–64 | بلوک | `freePosition` | 0 | ⬜ |
| `ASSET-018.15` | 65–74 | بلوک | `applyFreePositions` | 0 | ⬜ |
| `ASSET-018.16` | 75–77 | بلوک | `saveFreePositions` | 4 | ⬜ |
| `ASSET-018.17` | 78–81 | بلوک | `children` | 0 | ⬜ |
| `ASSET-018.18` | 82–86 | بلوک | `descendantIds` | 0 | ⬜ |
| `ASSET-018.19` | 87–88 | بلوک | `managedUnit` | 0 | ⬜ |
| `ASSET-018.20` | 89–91 | بلوک | `liaisonAt` | 0 | ⬜ |
| `ASSET-018.21` | 92–102 | بلوک | `roleOf` | 0 | ⬜ |
| `ASSET-018.22` | 103–114 | بلوک | `positionOf` | 0 | ⬜ |
| `ASSET-018.23` | 115–118 | بلوک | `sourceLabel` | 0 | ⬜ |
| `ASSET-018.24` | 119–119 | بلوک | `allDepartments` | 0 | ⬜ |
| `ASSET-018.25` | 120–122 | بلوک | `expertHosts` | 0 | ⬜ |
| `ASSET-018.26` | 123–123 | بلوک | `expertsIn` | 0 | ⬜ |
| `ASSET-018.27` | 124–124 | بلوک | `poolUsers` | 0 | ⬜ |
| `ASSET-018.28` | 125–125 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-018.29` | 126–129 | بلوک | `match` | 0 | ⬜ |
| `ASSET-018.30` | 130–138 | بلوک | `toast` | 0 | ⬜ |
| `ASSET-018.31` | 139–178 | بلوک | `submit` | 0 | ⬜ |
| `ASSET-018.32` | 179–182 | بلوک | `closeConfirm` | 0 | ⬜ |
| `ASSET-018.33` | 183–193 | بلوک | `askConfirm` | 0 | ⬜ |
| `ASSET-018.34` | 194–197 | بلوک | `closeModal` | 0 | ⬜ |
| `ASSET-018.35` | 198–211 | بلوک | `openModal` | 0 | ⬜ |
| `ASSET-018.36` | 212–247 | بلوک | `renderModal` | 0 | ⬜ |
| `ASSET-018.37` | 248–257 | بلوک | `renderLeftSidebar` | 0 | ⬜ |
| `ASSET-018.38` | 258–258 | بلوک | `stat` | 0 | ⬜ |
| `ASSET-018.39` | 259–263 | بلوک | `quick` | 0 | ⬜ |
| `ASSET-018.40` | 264–284 | بلوک | `renderPoolSidebar` | 0 | ⬜ |
| `ASSET-018.41` | 285–285 | بلوک | `nodeMatch` | 0 | ⬜ |
| `ASSET-018.42` | 286–286 | بلوک | `nodeMenuButton` | 0 | ⬜ |
| `ASSET-018.43` | 287–290 | بلوک | `renderCeoAddActions` | 0 | ⬜ |
| `ASSET-018.44` | 291–296 | بلوک | `renderCeo` | 0 | ⬜ |
| `ASSET-018.45` | 297–310 | بلوک | `renderLiaisonSide` | 0 | ⬜ |
| `ASSET-018.46` | 311–316 | بلوک | `renderOffice` | 0 | ⬜ |
| `ASSET-018.47` | 317–322 | بلوک | `renderLiaisonBand` | 0 | ⬜ |
| `ASSET-018.48` | 323–333 | بلوک | `renderDeputy` | 0 | ⬜ |
| `ASSET-018.49` | 334–343 | بلوک | `renderManagement` | 0 | ⬜ |
| `ASSET-018.50` | 344–350 | بلوک | `renderDepartment` | 0 | ⬜ |
| `ASSET-018.51` | 351–355 | بلوک | `expertNode` | 0 | ⬜ |
| `ASSET-018.52` | 356–363 | بلوک | `renderExperts` | 0 | ⬜ |
| `ASSET-018.53` | 364–364 | بلوک | `branchColorAt` | 0 | ⬜ |
| `ASSET-018.54` | 365–374 | بلوک | `renderUnitBranch` | 0 | ⬜ |
| `ASSET-018.55` | 375–389 | بلوک | `renderCeoBranch` | 0 | ⬜ |
| `ASSET-018.56` | 390–396 | بلوک | `markTree` | 0 | ⬜ |
| `ASSET-018.57` | 397–412 | بلوک | `markOrphanTree` | 0 | ⬜ |
| `ASSET-018.58` | 413–434 | بلوک | `expandCanvasForNegativeCards` | 0 | ⬜ |
| `ASSET-018.59` | 435–446 | بلوک | `preserveAxis` | 0 | ⬜ |
| `ASSET-018.60` | 447–453 | بلوک | `drawOrgWires` | 0 | ⬜ |
| `ASSET-018.61` | 454–459 | بلوک | `rel` | 0 | ⬜ |
| `ASSET-018.62` | 460–468 | بلوک | `path` | 0 | ⬜ |
| `ASSET-018.63` | 469–495 | بلوک | `officeJoinX` | 0 | ⬜ |
| `ASSET-018.64` | 496–509 | بلوک | `bindDeputyReorder` | 0 | ⬜ |
| `ASSET-018.65` | 510–517 | بلوک | `onDeputyMove` | 0 | ⬜ |
| `ASSET-018.66` | 518–522 | بلوک | `clearDeputyDrag` | 0 | ⬜ |
| `ASSET-018.67` | 523–535 | بلوک | `onDeputyUp` | 0 | ⬜ |
| `ASSET-018.68` | 536–536 | بلوک | `cancelDeputyDrag` | 0 | ⬜ |
| `ASSET-018.69` | 537–557 | بلوک | `renderChart` | 0 | ⬜ |
| `ASSET-018.70` | 558–561 | بلوک | `openManagerPicker` | 0 | ⬜ |
| `ASSET-018.71` | 562–567 | بلوک | `addExperts` | 0 | ⬜ |
| `ASSET-018.72` | 568–573 | بلوک | `quickAddDepartment` | 0 | ⬜ |
| `ASSET-018.73` | 574–585 | بلوک | `quickAddExpert` | 0 | ⬜ |
| `ASSET-018.74` | 586–616 | بلوک | `showInline` | 0 | ⬜ |
| `ASSET-018.75` | 617–658 | بلوک | `showInlineEdit` | 0 | ⬜ |
| `ASSET-018.76` | 659–659 | بلوک | `showInlineAddDeputy` | 0 | ⬜ |
| `ASSET-018.77` | 660–661 | بلوک | `showInlineAddDepartment` | 0 | ⬜ |
| `ASSET-018.78` | 662–669 | بلوک | `openExpertManager` | 0 | ⬜ |
| `ASSET-018.79` | 670–680 | بلوک | `close` | 0 | ⬜ |
| `ASSET-018.80` | 681–686 | بلوک | `removeAllExperts` | 0 | ⬜ |
| `ASSET-018.81` | 687–692 | بلوک | `removeUnit` | 0 | ⬜ |
| `ASSET-018.82` | 693–697 | بلوک | `assigned` | 1 | ⬜ |
| `ASSET-018.83` | 698–704 | بلوک | `addOrEditOffice` | 0 | ⬜ |
| `ASSET-018.84` | 705–727 | بلوک | `renderMenu` | 1 | ⬜ |
| `ASSET-018.85` | 728–732 | بلوک | `removeManager` | 0 | ⬜ |
| `ASSET-018.86` | 733–747 | بلوک | `handleAction` | 0 | ⬜ |
| `ASSET-018.87` | 748–819 | بلوک | `bindDropTargets` | 0 | ⬜ |
| `ASSET-018.88` | 820–834 | بلوک | `beginFreeMove` | 4 | ⬜ |
| `ASSET-018.89` | 835–838 | بلوک | `moveFreeCard` | 0 | ⬜ |
| `ASSET-018.90` | 839–848 | بلوک | `dx` | 0 | ⬜ |
| `ASSET-018.91` | 849–869 | بلوک | `endFreeCard` | 0 | ⬜ |
| `ASSET-018.92` | 870–903 | بلوک | `cancelFreeCard` | 0 | ⬜ |
| `ASSET-018.93` | 904–909 | بلوک | `endPan` | 0 | ⬜ |
| `ASSET-018.94` | 910–915 | بلوک | `askReset` | 0 | ⬜ |
| `ASSET-018.95` | 916–918 | بلوک | `downloadBlob` | 0 | ⬜ |
| `ASSET-018.96` | 919–923 | بلوک | `exportJson` | 0 | ⬜ |
| `ASSET-018.97` | 924–929 | بلوک | `exportExcel` | 0 | ⬜ |
| `ASSET-018.98` | 930–960 | بلوک | `createChartImageBlob` | 0 | ⬜ |
| `ASSET-018.99` | 961–968 | بلوک | `exportPng` | 0 | ⬜ |
| `ASSET-018.100` | 969–996 | بلوک | `printWholeChart` | 0 | ⬜ |
| `ASSET-018.101` | 997–997 | بلوک | `closeExportMenu` | 0 | ⬜ |
| `ASSET-018.102` | 998–1012 | بلوک | `loadBackupFile` | 0 | ⬜ |
| `ASSET-018.103` | 1013–1019 | بلوک | `exportByType` | 0 | ⬜ |
| `ASSET-018.104` | 1020–1036 | بلوک | `renderAll` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-019</b> — <code>assets/particle-network.js</code> (480 خط، 32 آدرس، بازبینی‌شده 0/32)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-019.01` | 1–34 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `ASSET-019.02` | 35–38 | بلوک | `clamp` | 0 | ⬜ |
| `ASSET-019.03` | 39–51 | بلوک | `asBoolean` | 0 | ⬜ |
| `ASSET-019.04` | 52–83 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-019.05` | 84–88 | بلوک | `parseColor` | 0 | ⬜ |
| `ASSET-019.06` | 89–90 | بلوک | `inertInstance` | 0 | ⬜ |
| `ASSET-019.07` | 91–91 | بلوک | `update` | 1 | ⬜ |
| `ASSET-019.08` | 92–95 | بلوک | `destroy` | 0 | ⬜ |
| `ASSET-019.09` | 96–147 | بلوک | `create` | 0 | ⬜ |
| `ASSET-019.10` | 148–155 | بلوک | `dimensions` | 0 | ⬜ |
| `ASSET-019.11` | 156–167 | بلوک | `makeParticle` | 0 | ⬜ |
| `ASSET-019.12` | 168–189 | بلوک | `render` | 0 | ⬜ |
| `ASSET-019.13` | 190–190 | بلوک | `midX` | 0 | ⬜ |
| `ASSET-019.14` | 191–193 | بلوک | `midY` | 0 | ⬜ |
| `ASSET-019.15` | 194–216 | بلوک | `alpha` | 0 | ⬜ |
| `ASSET-019.16` | 217–241 | بلوک | `cursorAlpha` | 0 | ⬜ |
| `ASSET-019.17` | 242–258 | بلوک | `resize` | 0 | ⬜ |
| `ASSET-019.18` | 259–262 | بلوک | `canAnimate` | 0 | ⬜ |
| `ASSET-019.19` | 263–270 | بلوک | `stop` | 0 | ⬜ |
| `ASSET-019.20` | 271–288 | بلوک | `animate` | 0 | ⬜ |
| `ASSET-019.21` | 289–297 | بلوک | `start` | 0 | ⬜ |
| `ASSET-019.22` | 298–305 | بلوک | `localPosition` | 0 | ⬜ |
| `ASSET-019.23` | 306–321 | بلوک | `onMove` | 0 | ⬜ |
| `ASSET-019.24` | 322–328 | بلوک | `onLeave` | 0 | ⬜ |
| `ASSET-019.25` | 329–332 | بلوک | `onResize` | 0 | ⬜ |
| `ASSET-019.26` | 333–342 | بلوک | `onVisibility` | 0 | ⬜ |
| `ASSET-019.27` | 343–377 | بلوک | `onMotionPreference` | 0 | ⬜ |
| `ASSET-019.28` | 378–405 | بلوک | `update` | 1 | ⬜ |
| `ASSET-019.29` | 406–443 | بلوک | `destroy` | 0 | ⬜ |
| `ASSET-019.30` | 444–455 | بلوک | `bootBackground` | 0 | ⬜ |
| `ASSET-019.31` | 456–469 | بلوک | `mount` | 0 | ⬜ |
| `ASSET-019.32` | 470–480 | بلوک | `boot` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-020</b> — <code>assets/particle-settings.js</code> (158 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-020.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-020.02` | 5–40 | بلوک | `init` | 0 | ⬜ |
| `ASSET-020.03` | 41–45 | بلوک | `activeEffect` | 0 | ⬜ |
| `ASSET-020.04` | 46–66 | بلوک | `readConfig` | 0 | ⬜ |
| `ASSET-020.05` | 67–84 | بلوک | `updateOutputs` | 4 | ⬜ |
| `ASSET-020.06` | 85–90 | بلوک | `mountPreview` | 0 | ⬜ |
| `ASSET-020.07` | 91–97 | بلوک | `destroyPreview` | 0 | ⬜ |
| `ASSET-020.08` | 98–104 | بلوک | `syncPreview` | 1 | ⬜ |
| `ASSET-020.09` | 105–158 | بلوک | `applyPreset` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-021</b> — <code>assets/server-sort.js</code> (67 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-021.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-021.02` | 9–14 | بلوک | `regionOf` | 0 | ⬜ |
| `ASSET-021.03` | 15–20 | بلوک | `sortableSections` | 0 | ⬜ |
| `ASSET-021.04` | 21–67 | بلوک | `findFresh` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-022</b> — <code>assets/style.css</code> (1,865 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-022.01` | 1–606 | بلوک | `مقدمهٔ فایل` | 6 | ⬜ |
| `ASSET-022.02` | 607–1865 | بلوک | `تقویم جلالی مشترک` | 7 | ⬜ |

</details>

<details>
<summary><b>ASSET-023</b> — <code>assets/theme-mode.css</code> (252 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-023.01` | 1–252 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>ASSET-024</b> — <code>assets/traffic-control.js</code> (488 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-024.01` | 1–20 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-024.02` | 21–30 | بلوک | `fireChange` | 0 | ⬜ |
| `ASSET-024.03` | 31–36 | بلوک | `setHint` | 0 | ⬜ |
| `ASSET-024.04` | 37–41 | بلوک | `clearHint` | 0 | ⬜ |
| `ASSET-024.05` | 42–49 | بلوک | `asciiDigitsOnly` | 0 | ⬜ |
| `ASSET-024.06` | 50–99 | بلوک | `lookupBy` | 0 | ⬜ |
| `ASSET-024.07` | 100–116 | بلوک | `lookup` | 0 | ⬜ |
| `ASSET-024.08` | 117–141 | بلوک | `lookupByPhone` | 0 | ⬜ |
| `ASSET-024.09` | 142–156 | بلوک | `parseApprovers` | 4 | ⬜ |
| `ASSET-024.10` | 157–196 | بلوک | `populate` | 0 | ⬜ |
| `ASSET-024.11` | 197–241 | بلوک | `updateDestinationSummary` | 0 | ⬜ |
| `ASSET-024.12` | 242–262 | بلوک | `liveList` | 4 | ⬜ |
| `ASSET-024.13` | 263–263 | بلوک | `selectedFields` | 0 | ⬜ |
| `ASSET-024.14` | 264–290 | بلوک | `renderReport` | 4 | ⬜ |
| `ASSET-024.15` | 291–299 | بلوک | `schedule` | 0 | ⬜ |
| `ASSET-024.16` | 300–392 | بلوک | `load` | 1 | ⬜ |
| `ASSET-024.17` | 393–420 | بلوک | `apply` | 0 | ⬜ |
| `ASSET-024.18` | 421–458 | بلوک | `asciiDigits` | 4 | ⬜ |
| `ASSET-024.19` | 459–466 | بلوک | `asciiDigits` | 0 | ⬜ |
| `ASSET-024.20` | 467–488 | بلوک | `sync` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-025</b> — <code>assets/traffic-history-1.62.72.css</code> (7 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-025.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-026</b> — <code>assets/traffic-records-1.62.68.css</code> (21 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-026.01` | 1–21 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-027</b> — <code>assets/traffic-records-1.62.69.css</code> (50 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-027.01` | 1–50 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>ASSET-028</b> — <code>assets/traffic-report-1.62.70.css</code> (22 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-028.01` | 1–22 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |

</details>

<details>
<summary><b>ASSET-029</b> — <code>assets/triangle-mesh.js</code> (645 خط، 38 آدرس، بازبینی‌شده 0/38)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-029.01` | 1–33 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `ASSET-029.02` | 34–37 | بلوک | `clamp` | 0 | ⬜ |
| `ASSET-029.03` | 38–46 | بلوک | `parseColor` | 4 | ⬜ |
| `ASSET-029.04` | 47–59 | بلوک | `asBoolean` | 0 | ⬜ |
| `ASSET-029.05` | 60–87 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-029.06` | 88–89 | بلوک | `inertInstance` | 0 | ⬜ |
| `ASSET-029.07` | 90–90 | بلوک | `update` | 1 | ⬜ |
| `ASSET-029.08` | 91–91 | بلوک | `ripple` | 0 | ⬜ |
| `ASSET-029.09` | 92–95 | بلوک | `destroy` | 0 | ⬜ |
| `ASSET-029.10` | 96–164 | بلوک | `create` | 0 | ⬜ |
| `ASSET-029.11` | 165–170 | بلوک | `applyMask` | 0 | ⬜ |
| `ASSET-029.12` | 171–178 | بلوک | `size` | 0 | ⬜ |
| `ASSET-029.13` | 179–225 | بلوک | `rebuild` | 0 | ⬜ |
| `ASSET-029.14` | 226–239 | بلوک | `below` | 0 | ⬜ |
| `ASSET-029.15` | 240–307 | بلوک | `render` | 0 | ⬜ |
| `ASSET-029.16` | 308–326 | بلوک | `hasMotion` | 0 | ⬜ |
| `ASSET-029.17` | 327–370 | بلوک | `physics` | 0 | ⬜ |
| `ASSET-029.18` | 371–376 | بلوک | `elapsed` | 0 | ⬜ |
| `ASSET-029.19` | 377–391 | بلوک | `amplitude` | 0 | ⬜ |
| `ASSET-029.20` | 392–399 | بلوک | `stop` | 0 | ⬜ |
| `ASSET-029.21` | 400–415 | بلوک | `animate` | 0 | ⬜ |
| `ASSET-029.22` | 416–424 | بلوک | `start` | 0 | ⬜ |
| `ASSET-029.23` | 425–432 | بلوک | `localPosition` | 0 | ⬜ |
| `ASSET-029.24` | 433–440 | بلوک | `addRipple` | 0 | ⬜ |
| `ASSET-029.25` | 441–457 | بلوک | `onMove` | 0 | ⬜ |
| `ASSET-029.26` | 458–469 | بلوک | `onDown` | 0 | ⬜ |
| `ASSET-029.27` | 470–476 | بلوک | `onUp` | 0 | ⬜ |
| `ASSET-029.28` | 477–481 | بلوک | `onLeave` | 0 | ⬜ |
| `ASSET-029.29` | 482–488 | بلوک | `onOut` | 0 | ⬜ |
| `ASSET-029.30` | 489–496 | بلوک | `onVisibility` | 0 | ⬜ |
| `ASSET-029.31` | 497–502 | بلوک | `onResize` | 0 | ⬜ |
| `ASSET-029.32` | 503–538 | بلوک | `onMotionPreference` | 0 | ⬜ |
| `ASSET-029.33` | 539–562 | بلوک | `update` | 1 | ⬜ |
| `ASSET-029.34` | 563–565 | بلوک | `ripple` | 0 | ⬜ |
| `ASSET-029.35` | 566–605 | بلوک | `destroy` | 0 | ⬜ |
| `ASSET-029.36` | 606–617 | بلوک | `bootBackground` | 0 | ⬜ |
| `ASSET-029.37` | 618–632 | بلوک | `mount` | 0 | ⬜ |
| `ASSET-029.38` | 633–645 | بلوک | `boot` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-030</b> — <code>assets/triangle-settings.js</code> (267 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-030.01` | 1–35 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-030.02` | 36–52 | بلوک | `init` | 0 | ⬜ |
| `ASSET-030.03` | 53–74 | بلوک | `readConfig` | 0 | ⬜ |
| `ASSET-030.04` | 75–93 | بلوک | `updateOutputs` | 0 | ⬜ |
| `ASSET-030.05` | 94–97 | بلوک | `activeEffect` | 0 | ⬜ |
| `ASSET-030.06` | 98–103 | بلوک | `mountTrianglePreview` | 0 | ⬜ |
| `ASSET-030.07` | 104–110 | بلوک | `destroyTrianglePreview` | 0 | ⬜ |
| `ASSET-030.08` | 111–117 | بلوک | `syncPreview` | 1 | ⬜ |
| `ASSET-030.09` | 118–151 | بلوک | `activateEffectTab` | 0 | ⬜ |
| `ASSET-030.10` | 152–157 | بلوک | `next` | 0 | ⬜ |
| `ASSET-030.11` | 158–172 | بلوک | `updatePageCount` | 4 | ⬜ |
| `ASSET-030.12` | 173–178 | بلوک | `filterPages` | 0 | ⬜ |
| `ASSET-030.13` | 179–267 | بلوک | `text` | 0 | ⬜ |

</details>

### پنل وب غذا (food-ticket-web/) — `WEB`


<details>
<summary><b>WEB-001</b> — <code>food-ticket-web/assets/food-brand.js</code> (255 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-001.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-001.02` | 9–23 | بلوک | `serviceUrl` | 0 | ⬜ |
| `WEB-001.03` | 24–24 | بلوک | `escapeRegExp` | 0 | ⬜ |
| `WEB-001.04` | 25–32 | بلوک | `cleanBrandName` | 0 | ⬜ |
| `WEB-001.05` | 33–60 | بلوک | `cleanLegacyText` | 0 | ⬜ |
| `WEB-001.06` | 61–70 | بلوک | `safeUrl` | 0 | ⬜ |
| `WEB-001.07` | 71–72 | بلوک | `visibleText` | 0 | ⬜ |
| `WEB-001.08` | 73–81 | بلوک | `applyLogo` | 0 | ⬜ |
| `WEB-001.09` | 82–91 | بلوک | `addReturnLink` | 0 | ⬜ |
| `WEB-001.10` | 92–111 | بلوک | `hydrateServiceFields` | 0 | ⬜ |
| `WEB-001.11` | 112–138 | بلوک | `apply` | 0 | ⬜ |
| `WEB-001.12` | 139–161 | بلوک | `loadServiceBrand` | 4 | ⬜ |
| `WEB-001.13` | 162–169 | بلوک | `readThemeMode` | 0 | ⬜ |
| `WEB-001.14` | 170–191 | بلوک | `syncThemeMode` | 4 | ⬜ |
| `WEB-001.15` | 192–197 | بلوک | `changeThemeMode` | 0 | ⬜ |
| `WEB-001.16` | 198–236 | بلوک | `update` | 1 | ⬜ |
| `WEB-001.17` | 237–255 | بلوک | `boot` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-002</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-4179.css</code> (3 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-002.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>WEB-003</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-4179.js</code> (319 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-003.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-003.02` | 159–319 | بلوک | `fetchData` | 6 | ⬜ |

</details>

<details>
<summary><b>WEB-004</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r37.css</code> (3 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-004.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>WEB-005</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r37.js</code> (359 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-005.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-005.02` | 159–359 | بلوک | `fetchData` | **11** | ⬜ |

</details>

<details>
<summary><b>WEB-006</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r38.css</code> (3 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-006.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>WEB-007</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r38.js</code> (359 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-007.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-007.02` | 159–359 | بلوک | `fetchData` | **11** | ⬜ |

</details>

<details>
<summary><b>WEB-008</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r39.css</code> (25 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-008.01` | 1–25 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>WEB-009</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r39.js</code> (359 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-009.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-009.02` | 159–359 | بلوک | `fetchData` | **11** | ⬜ |

</details>

<details>
<summary><b>WEB-010</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r40.css</code> (36 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-010.01` | 1–36 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>WEB-011</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r41.css</code> (46 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-011.01` | 1–46 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>WEB-012</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r41.js</code> (359 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-012.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-012.02` | 159–359 | بلوک | `fetchData` | **11** | ⬜ |

</details>

<details>
<summary><b>WEB-013</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r42.css</code> (46 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-013.01` | 1–46 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>WEB-014</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r42.js</code> (359 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-014.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-014.02` | 159–359 | بلوک | `fetchData` | **11** | ⬜ |

</details>

<details>
<summary><b>WEB-015</b> — <code>food-ticket-web/assets/food-capitalio-dashboard.css</code> (336 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-015.01` | 1–336 | بلوک | `مقدمهٔ فایل` | 6 | ⬜ |

</details>

<details>
<summary><b>WEB-016</b> — <code>food-ticket-web/assets/food-designer.js</code> (882 خط، 64 آدرس، بازبینی‌شده 0/64)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-016.01` | 1–27 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-016.02` | 28–28 | بلوک | `esc` | 0 | ⬜ |
| `WEB-016.03` | 29–29 | بلوک | `say` | 4 | ⬜ |
| `WEB-016.04` | 30–30 | بلوک | `callApi` | 0 | ⬜ |
| `WEB-016.05` | 31–31 | بلوک | `root` | 0 | ⬜ |
| `WEB-016.06` | 32–32 | بلوک | `clone` | 0 | ⬜ |
| `WEB-016.07` | 33–33 | بلوک | `r1` | 0 | ⬜ |
| `WEB-016.08` | 34–34 | بلوک | `num` | 0 | ⬜ |
| `WEB-016.09` | 35–36 | بلوک | `faDigits` | 0 | ⬜ |
| `WEB-016.10` | 37–40 | بلوک | `FIELD_BY_KEY` | 0 | ⬜ |
| `WEB-016.11` | 41–115 | بلوک | `el` | 0 | ⬜ |
| `WEB-016.12` | 116–116 | بلوک | `current` | 0 | ⬜ |
| `WEB-016.13` | 117–117 | بلوک | `selected` | 0 | ⬜ |
| `WEB-016.14` | 118–125 | بلوک | `paperH` | 0 | ⬜ |
| `WEB-016.15` | 126–129 | بلوک | `workFrom` | 0 | ⬜ |
| `WEB-016.16` | 130–139 | بلوک | `setWork` | 0 | ⬜ |
| `WEB-016.17` | 140–142 | بلوک | `activeRow` | 0 | ⬜ |
| `WEB-016.18` | 143–163 | بلوک | `applyList` | 0 | ⬜ |
| `WEB-016.19` | 164–175 | بلوک | `load` | 0 | ⬜ |
| `WEB-016.20` | 176–178 | بلوک | `html` | 0 | ⬜ |
| `WEB-016.21` | 179–185 | بلوک | `mount` | 0 | ⬜ |
| `WEB-016.22` | 186–190 | بلوک | `draw` | 0 | ⬜ |
| `WEB-016.23` | 191–203 | بلوک | `sampleText` | 0 | ⬜ |
| `WEB-016.24` | 204–208 | بلوک | `logoUrl` | 0 | ⬜ |
| `WEB-016.25` | 209–210 | بلوک | `elementHtml` | 0 | ⬜ |
| `WEB-016.26` | 211–229 | بلوک | `pxPt` | 0 | ⬜ |
| `WEB-016.27` | 230–233 | بلوک | `imgAlign` | 0 | ⬜ |
| `WEB-016.28` | 234–255 | بلوک | `groupPreviewLines` | 0 | ⬜ |
| `WEB-016.29` | 256–264 | بلوک | `groupTextHeight` | 0 | ⬜ |
| `WEB-016.30` | 265–312 | بلوک | `groupCanvasHtml` | 0 | ⬜ |
| `WEB-016.31` | 313–326 | بلوک | `canvasHtml` | 0 | ⬜ |
| `WEB-016.32` | 327–330 | بلوک | `designTabsHtml` | 0 | ⬜ |
| `WEB-016.33` | 331–339 | بلوک | `tabsHtml` | 0 | ⬜ |
| `WEB-016.34` | 340–352 | بلوک | `fieldListHtml` | 0 | ⬜ |
| `WEB-016.35` | 353–360 | بلوک | `layersHtml` | 0 | ⬜ |
| `WEB-016.36` | 361–363 | بلوک | `groupTypographyPropsHtml` | 0 | ⬜ |
| `WEB-016.37` | 364–377 | بلوک | `inp` | 0 | ⬜ |
| `WEB-016.38` | 378–384 | بلوک | `propsHtml` | 0 | ⬜ |
| `WEB-016.39` | 385–394 | بلوک | `inp` | 0 | ⬜ |
| `WEB-016.40` | 395–406 | بلوک | `labelBlock` | 1 | ⬜ |
| `WEB-016.41` | 407–410 | بلوک | `paperHtml` | 0 | ⬜ |
| `WEB-016.42` | 411–411 | بلوک | `logoThumb` | 0 | ⬜ |
| `WEB-016.43` | 412–432 | بلوک | `warn58` | 0 | ⬜ |
| `WEB-016.44` | 433–441 | بلوک | `groupModeHtml` | 0 | ⬜ |
| `WEB-016.45` | 442–491 | بلوک | `groupLivePreviewHtml` | 0 | ⬜ |
| `WEB-016.46` | 492–523 | بلوک | `previewHtml` | 0 | ⬜ |
| `WEB-016.47` | 524–528 | بلوک | `body` | 0 | ⬜ |
| `WEB-016.48` | 529–547 | بلوک | `isActive` | 0 | ⬜ |
| `WEB-016.49` | 548–563 | بلوک | `css` | 1 | ⬜ |
| `WEB-016.50` | 564–570 | بلوک | `touch` | 0 | ⬜ |
| `WEB-016.51` | 571–576 | بلوک | `schedulePreview` | 0 | ⬜ |
| `WEB-016.52` | 577–593 | بلوک | `runPreview` | 0 | ⬜ |
| `WEB-016.53` | 594–597 | بلوک | `guardDirty` | 0 | ⬜ |
| `WEB-016.54` | 598–610 | بلوک | `switchDesignTab` | 0 | ⬜ |
| `WEB-016.55` | 611–627 | بلوک | `addElement` | 0 | ⬜ |
| `WEB-016.56` | 628–646 | بلوک | `saveGroup` | 0 | ⬜ |
| `WEB-016.57` | 647–663 | بلوک | `save` | 0 | ⬜ |
| `WEB-016.58` | 664–673 | بلوک | `postAction` | 0 | ⬜ |
| `WEB-016.59` | 674–690 | بلوک | `uploadLogo` | 4 | ⬜ |
| `WEB-016.60` | 691–821 | بلوک | `within` | 6 | ⬜ |
| `WEB-016.61` | 822–822 | بلوک | `dx` | 0 | ⬜ |
| `WEB-016.62` | 823–856 | بلوک | `snap` | 0 | ⬜ |
| `WEB-016.63` | 857–868 | بلوک | `openNewDialog` | 0 | ⬜ |
| `WEB-016.64` | 869–882 | بلوک | `key` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-017</b> — <code>food-ticket-web/assets/food-groups.js</code> (512 خط، 36 آدرس، بازبینی‌شده 0/36)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-017.01` | 1–20 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-017.02` | 21–21 | بلوک | `esc` | 0 | ⬜ |
| `WEB-017.03` | 22–22 | بلوک | `say` | 4 | ⬜ |
| `WEB-017.04` | 23–23 | بلوک | `box` | 0 | ⬜ |
| `WEB-017.05` | 24–24 | بلوک | `callApi` | 0 | ⬜ |
| `WEB-017.06` | 25–31 | بلوک | `todayIso` | 0 | ⬜ |
| `WEB-017.07` | 32–48 | بلوک | `draw` | 4 | ⬜ |
| `WEB-017.08` | 49–75 | بلوک | `load` | 4 | ⬜ |
| `WEB-017.09` | 76–87 | بلوک | `apply` | 0 | ⬜ |
| `WEB-017.10` | 88–88 | بلوک | `userLabel` | 0 | ⬜ |
| `WEB-017.11` | 89–94 | بلوک | `badges` | 0 | ⬜ |
| `WEB-017.12` | 95–98 | بلوک | `form` | 0 | ⬜ |
| `WEB-017.13` | 99–100 | بلوک | `q` | 0 | ⬜ |
| `WEB-017.14` | 101–106 | بلوک | `mine` | 0 | ⬜ |
| `WEB-017.15` | 107–127 | بلوک | `visitors` | 0 | ⬜ |
| `WEB-017.16` | 128–144 | بلوک | `list` | 1 | ⬜ |
| `WEB-017.17` | 145–148 | بلوک | `isoToday` | 0 | ⬜ |
| `WEB-017.18` | 149–159 | بلوک | `jalaliLabel` | 0 | ⬜ |
| `WEB-017.19` | 160–160 | بلوک | `isoFromJalaliInput` | 0 | ⬜ |
| `WEB-017.20` | 161–174 | بلوک | `raw` | 0 | ⬜ |
| `WEB-017.21` | 175–185 | بلوک | `mergeAbsence` | 0 | ⬜ |
| `WEB-017.22` | 186–200 | بلوک | `absencePanel` | 0 | ⬜ |
| `WEB-017.23` | 201–201 | بلوک | `rows` | 0 | ⬜ |
| `WEB-017.24` | 202–242 | بلوک | `disabled` | 0 | ⬜ |
| `WEB-017.25` | 243–252 | بلوک | `runs` | 0 | ⬜ |
| `WEB-017.26` | 253–257 | بلوک | `personLine` | 0 | ⬜ |
| `WEB-017.27` | 258–264 | بلوک | `membersPopup` | 0 | ⬜ |
| `WEB-017.28` | 265–286 | بلوک | `block` | 0 | ⬜ |
| `WEB-017.29` | 287–295 | بلوک | `body` | 0 | ⬜ |
| `WEB-017.30` | 296–305 | بلوک | `html` | 0 | ⬜ |
| `WEB-017.31` | 306–317 | بلوک | `collect` | 0 | ⬜ |
| `WEB-017.32` | 318–393 | بلوک | `loadAbsence` | 0 | ⬜ |
| `WEB-017.33` | 394–414 | بلوک | `guest` | 0 | ⬜ |
| `WEB-017.34` | 415–454 | بلوک | `openEdit` | 1 | ⬜ |
| `WEB-017.35` | 455–481 | بلوک | `reason` | 0 | ⬜ |
| `WEB-017.36` | 482–512 | بلوک | `reason` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-018</b> — <code>food-ticket-web/assets/food-manual-attendance.js</code> (333 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-018.01` | 1–11 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-018.02` | 12–12 | بلوک | `esc` | 0 | ⬜ |
| `WEB-018.03` | 13–13 | بلوک | `say` | 4 | ⬜ |
| `WEB-018.04` | 14–14 | بلوک | `callApi` | 0 | ⬜ |
| `WEB-018.05` | 15–20 | بلوک | `isoToday` | 0 | ⬜ |
| `WEB-018.06` | 21–21 | بلوک | `isGuestRow` | 0 | ⬜ |
| `WEB-018.07` | 22–22 | بلوک | `canIssueManualTicket` | 0 | ⬜ |
| `WEB-018.08` | 23–26 | بلوک | `isReplaceableNoTicket` | 0 | ⬜ |
| `WEB-018.09` | 27–29 | بلوک | `selectableRow` | 0 | ⬜ |
| `WEB-018.10` | 30–30 | بلوک | `manualDateIso` | 0 | ⬜ |
| `WEB-018.11` | 31–37 | بلوک | `jalaliFromIso` | 0 | ⬜ |
| `WEB-018.12` | 38–44 | بلوک | `isoFromDateInput` | 4 | ⬜ |
| `WEB-018.13` | 45–74 | بلوک | `loadManualAttendance` | 0 | ⬜ |
| `WEB-018.14` | 75–86 | بلوک | `manualStatus` | 0 | ⬜ |
| `WEB-018.15` | 87–94 | بلوک | `manualAttendancePanel` | 0 | ⬜ |
| `WEB-018.16` | 95–169 | بلوک | `canSelect` | 0 | ⬜ |
| `WEB-018.17` | 170–173 | بلوک | `manualAttendanceHtml` | 0 | ⬜ |
| `WEB-018.18` | 174–181 | بلوک | `drawManualAttendance` | 4 | ⬜ |
| `WEB-018.19` | 182–273 | بلوک | `applyManualDateInput` | 1 | ⬜ |
| `WEB-018.20` | 274–290 | بلوک | `reason` | 0 | ⬜ |
| `WEB-018.21` | 291–333 | بلوک | `message` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-019</b> — <code>food-ticket-web/assets/food-menu.css</code> (354 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-019.01` | 1–227 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `WEB-019.02` | 228–240 | بلوک | `لیست کارکنان (غیبت و اعضای گروه): ۴ ستون، تیک قبل ` | 3 | ⬜ |
| `WEB-019.03` | 241–244 | بلوک | `جعبهٔ انتخاب غذا: هم‌اندازهٔ فرم غیبت` | 0 | ⬜ |
| `WEB-019.04` | 245–354 | بلوک | `بانک غذا: هر غذا در یک خط (نام \| دسته‌بندی \| وضع` | 3 | ⬜ |

</details>

<details>
<summary><b>WEB-020</b> — <code>food-ticket-web/assets/food-menu.js</code> (2,439 خط، 149 آدرس، بازبینی‌شده 0/149)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-020.01` | 1–100 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-020.02` | 101–105 | بلوک | `esc` | 0 | ⬜ |
| `WEB-020.03` | 106–106 | بلوک | `faDigits` | 0 | ⬜ |
| `WEB-020.04` | 107–111 | بلوک | `asciiDigits` | 0 | ⬜ |
| `WEB-020.05` | 112–121 | بلوک | `isoFromJalali` | 0 | ⬜ |
| `WEB-020.06` | 122–128 | بلوک | `jalaliFromIso` | 0 | ⬜ |
| `WEB-020.07` | 129–135 | بلوک | `normalizeIsoDate` | 0 | ⬜ |
| `WEB-020.08` | 136–139 | بلوک | `jalaliMonthFromIso` | 0 | ⬜ |
| `WEB-020.09` | 140–143 | بلوک | `localTodayIso` | 0 | ⬜ |
| `WEB-020.10` | 144–144 | بلوک | `monthKey` | 0 | ⬜ |
| `WEB-020.11` | 145–145 | بلوک | `monthTitle` | 0 | ⬜ |
| `WEB-020.12` | 146–146 | بلوک | `getDay` | 0 | ⬜ |
| `WEB-020.13` | 147–150 | بلوک | `activeOrderCount` | 0 | ⬜ |
| `WEB-020.14` | 151–160 | بلوک | `holiday` | 0 | ⬜ |
| `WEB-020.15` | 161–166 | بلوک | `buildUrl` | 0 | ⬜ |
| `WEB-020.16` | 167–184 | بلوک | `request` | 0 | ⬜ |
| `WEB-020.17` | 185–191 | بلوک | `notify` | 0 | ⬜ |
| `WEB-020.18` | 192–197 | بلوک | `navTabs` | 0 | ⬜ |
| `WEB-020.19` | 198–207 | بلوک | `cancelCount` | 0 | ⬜ |
| `WEB-020.20` | 208–237 | بلوک | `renderCalendar` | 0 | ⬜ |
| `WEB-020.21` | 238–255 | بلوک | `renderDayEditor` | 0 | ⬜ |
| `WEB-020.22` | 256–265 | بلوک | `choiceHtml` | 0 | ⬜ |
| `WEB-020.23` | 266–291 | بلوک | `foods` | 0 | ⬜ |
| `WEB-020.24` | 292–297 | بلوک | `categoryOptions` | 0 | ⬜ |
| `WEB-020.25` | 298–306 | بلوک | `catalogCategoryNames` | 0 | ⬜ |
| `WEB-020.26` | 307–316 | بلوک | `foodCardHtml` | 0 | ⬜ |
| `WEB-020.27` | 317–358 | بلوک | `renderCatalog` | 0 | ⬜ |
| `WEB-020.28` | 359–368 | بلوک | `readSavedOrderReportFields` | 0 | ⬜ |
| `WEB-020.29` | 369–374 | بلوک | `saveOrderReportFields` | 4 | ⬜ |
| `WEB-020.30` | 375–379 | بلوک | `selectedOrderColumns` | 0 | ⬜ |
| `WEB-020.31` | 380–396 | بلوک | `orderFieldValue` | 0 | ⬜ |
| `WEB-020.32` | 397–411 | بلوک | `orderSortValue` | 0 | ⬜ |
| `WEB-020.33` | 412–427 | بلوک | `sortOrderRows` | 0 | ⬜ |
| `WEB-020.34` | 428–465 | بلوک | `orderTableHtml` | 0 | ⬜ |
| `WEB-020.35` | 466–501 | بلوک | `orderByMenuHtml` | 0 | ⬜ |
| `WEB-020.36` | 502–511 | بلوک | `readDeliveredReportFields` | 0 | ⬜ |
| `WEB-020.37` | 512–516 | بلوک | `saveDeliveredReportFields` | 4 | ⬜ |
| `WEB-020.38` | 517–530 | بلوک | `deliveredReportFieldValue` | 0 | ⬜ |
| `WEB-020.39` | 531–534 | بلوک | `selectedDeliveredReportColumns` | 0 | ⬜ |
| `WEB-020.40` | 535–547 | بلوک | `sortDeliveredReportRows` | 0 | ⬜ |
| `WEB-020.41` | 548–578 | بلوک | `deliveredReportTableHtml` | 0 | ⬜ |
| `WEB-020.42` | 579–581 | بلوک | `deliveredReportByMenuHtml` | 0 | ⬜ |
| `WEB-020.43` | 582–603 | بلوک | `key` | 0 | ⬜ |
| `WEB-020.44` | 604–608 | بلوک | `deliveredReportOrderedRows` | 0 | ⬜ |
| `WEB-020.45` | 609–621 | بلوک | `key` | 0 | ⬜ |
| `WEB-020.46` | 622–625 | بلوک | `deliveryDateForReport` | 0 | ⬜ |
| `WEB-020.47` | 626–645 | بلوک | `renderPrintedDeliveryReport` | 0 | ⬜ |
| `WEB-020.48` | 646–681 | بلوک | `loadPrintedDeliveryReport` | 0 | ⬜ |
| `WEB-020.49` | 682–698 | بلوک | `exportPrintedDeliveryReport` | 0 | ⬜ |
| `WEB-020.50` | 699–703 | بلوک | `printPrintedDeliveryReport` | 0 | ⬜ |
| `WEB-020.51` | 704–709 | بلوک | `summaryHtml` | 0 | ⬜ |
| `WEB-020.52` | 710–713 | بلوک | `rowSummary` | 0 | ⬜ |
| `WEB-020.53` | 714–719 | بلوک | `renderOrders` | 0 | ⬜ |
| `WEB-020.54` | 720–735 | بلوک | `viewButton` | 0 | ⬜ |
| `WEB-020.55` | 736–744 | بلوک | `renderGroupOrders` | 0 | ⬜ |
| `WEB-020.56` | 745–769 | بلوک | `tableFor` | 0 | ⬜ |
| `WEB-020.57` | 770–786 | بلوک | `renderCancelRequests` | 0 | ⬜ |
| `WEB-020.58` | 787–830 | بلوک | `renderStatistics` | 0 | ⬜ |
| `WEB-020.59` | 831–856 | بلوک | `foods` | 0 | ⬜ |
| `WEB-020.60` | 857–865 | بلوک | `normalizeJalaliMonth` | 0 | ⬜ |
| `WEB-020.61` | 866–869 | بلوک | `reconciliationNumber` | 0 | ⬜ |
| `WEB-020.62` | 870–874 | بلوک | `reconciliationAmount` | 0 | ⬜ |
| `WEB-020.63` | 875–952 | بلوک | `renderReconciliation` | 0 | ⬜ |
| `WEB-020.64` | 953–993 | بلوک | `loadReconciliation` | 0 | ⬜ |
| `WEB-020.65` | 994–999 | بلوک | `downloadReconciliation` | 0 | ⬜ |
| `WEB-020.66` | 1000–1000 | بلوک | `selectReconciliationRow` | 0 | ⬜ |
| `WEB-020.67` | 1001–1021 | بلوک | `item` | 0 | ⬜ |
| `WEB-020.68` | 1022–1066 | بلوک | `saveReconciliationForm` | 0 | ⬜ |
| `WEB-020.69` | 1067–1103 | بلوک | `uploadReconciliationInvoice` | 0 | ⬜ |
| `WEB-020.70` | 1104–1107 | بلوک | `bulkOperationTitle` | 0 | ⬜ |
| `WEB-020.71` | 1108–1112 | بلوک | `bulkSelectedPeople` | 0 | ⬜ |
| `WEB-020.72` | 1113–1116 | بلوک | `bulkTargetValue` | 0 | ⬜ |
| `WEB-020.73` | 1117–1123 | بلوک | `bulkCanSelect` | 0 | ⬜ |
| `WEB-020.74` | 1124–1134 | بلوک | `group` | 0 | ⬜ |
| `WEB-020.75` | 1135–1162 | بلوک | `loadBulkSnapshot` | 0 | ⬜ |
| `WEB-020.76` | 1163–1177 | بلوک | `bulkTargetOptions` | 0 | ⬜ |
| `WEB-020.77` | 1178–1193 | بلوک | `bulkPersonStatus` | 0 | ⬜ |
| `WEB-020.78` | 1194–1215 | بلوک | `group` | 0 | ⬜ |
| `WEB-020.79` | 1216–1221 | بلوک | `renderBulkResults` | 0 | ⬜ |
| `WEB-020.80` | 1222–1229 | بلوک | `renderBulkModal` | 0 | ⬜ |
| `WEB-020.81` | 1230–1244 | بلوک | `renderBulkOperations` | 0 | ⬜ |
| `WEB-020.82` | 1245–1250 | بلوک | `rows` | 0 | ⬜ |
| `WEB-020.83` | 1251–1263 | بلوک | `selected` | 0 | ⬜ |
| `WEB-020.84` | 1264–1274 | بلوک | `previewDisabled` | 0 | ⬜ |
| `WEB-020.85` | 1275–1285 | بلوک | `bulkTogglePerson` | 1 | ⬜ |
| `WEB-020.86` | 1286–1287 | بلوک | `bulkSelectEligible` | 0 | ⬜ |
| `WEB-020.87` | 1288–1293 | بلوک | `eligible` | 0 | ⬜ |
| `WEB-020.88` | 1294–1296 | بلوک | `submitBulkOperation` | 0 | ⬜ |
| `WEB-020.89` | 1297–1316 | بلوک | `ids` | 0 | ⬜ |
| `WEB-020.90` | 1317–1322 | بلوک | `mealTypeSwitch` | 0 | ⬜ |
| `WEB-020.91` | 1323–1330 | بلوک | `embeddedPanel` | 0 | ⬜ |
| `WEB-020.92` | 1331–1345 | بلوک | `loadEmbedded` | 0 | ⬜ |
| `WEB-020.93` | 1346–1357 | بلوک | `render` | 0 | ⬜ |
| `WEB-020.94` | 1358–1362 | بلوک | `notices` | 0 | ⬜ |
| `WEB-020.95` | 1363–1374 | بلوک | `loadStatus` | 0 | ⬜ |
| `WEB-020.96` | 1375–1381 | بلوک | `loadCatalog` | 0 | ⬜ |
| `WEB-020.97` | 1382–1389 | بلوک | `loadMonth` | 0 | ⬜ |
| `WEB-020.98` | 1390–1411 | بلوک | `loadOrders` | 0 | ⬜ |
| `WEB-020.99` | 1412–1432 | بلوک | `loadFoodGroups` | 0 | ⬜ |
| `WEB-020.100` | 1433–1463 | بلوک | `loadGroupOrders` | 0 | ⬜ |
| `WEB-020.101` | 1464–1480 | بلوک | `loadCancelRequests` | 0 | ⬜ |
| `WEB-020.102` | 1481–1499 | بلوک | `loadGuestRequests` | 0 | ⬜ |
| `WEB-020.103` | 1500–1500 | بلوک | `beginGuestEdit` | 0 | ⬜ |
| `WEB-020.104` | 1501–1516 | بلوک | `row` | 0 | ⬜ |
| `WEB-020.105` | 1517–1522 | بلوک | `cancelGuestEdit` | 0 | ⬜ |
| `WEB-020.106` | 1523–1554 | بلوک | `saveGuestEdit` | 1 | ⬜ |
| `WEB-020.107` | 1555–1555 | بلوک | `guestRequestAction` | 0 | ⬜ |
| `WEB-020.108` | 1556–1578 | بلوک | `row` | 1 | ⬜ |
| `WEB-020.109` | 1579–1581 | بلوک | `renderGuestRequestManager` | 0 | ⬜ |
| `WEB-020.110` | 1582–1604 | بلوک | `rows` | 1 | ⬜ |
| `WEB-020.111` | 1605–1619 | بلوک | `decideCancelRequest` | 0 | ⬜ |
| `WEB-020.112` | 1620–1626 | بلوک | `loadStatistics` | 0 | ⬜ |
| `WEB-020.113` | 1627–1660 | بلوک | `selectedIds` | 0 | ⬜ |
| `WEB-020.114` | 1661–1688 | بلوک | `searchSelectedReportEmployees` | 0 | ⬜ |
| `WEB-020.115` | 1689–1715 | بلوک | `refreshAll` | 0 | ⬜ |
| `WEB-020.116` | 1716–1760 | بلوک | `saveDay` | 0 | ⬜ |
| `WEB-020.117` | 1761–1786 | بلوک | `saveCatalog` | 0 | ⬜ |
| `WEB-020.118` | 1787–1806 | بلوک | `orderExportRows` | 0 | ⬜ |
| `WEB-020.119` | 1807–1823 | بلوک | `downloadOrdersCsv` | 0 | ⬜ |
| `WEB-020.120` | 1824–1828 | بلوک | `downloadOrders` | 0 | ⬜ |
| `WEB-020.121` | 1829–1830 | بلوک | `printScopedReport` | 0 | ⬜ |
| `WEB-020.122` | 1831–1841 | بلوک | `target` | 0 | ⬜ |
| `WEB-020.123` | 1842–1859 | بلوک | `cleanup` | 0 | ⬜ |
| `WEB-020.124` | 1860–1864 | بلوک | `printOrders` | 0 | ⬜ |
| `WEB-020.125` | 1865–1871 | بلوک | `downloadStatistics` | 0 | ⬜ |
| `WEB-020.126` | 1872–1876 | بلوک | `printStatistics` | 0 | ⬜ |
| `WEB-020.127` | 1877–1884 | بلوک | `selectDay` | 0 | ⬜ |
| `WEB-020.128` | 1885–1890 | بلوک | `changeMonth` | 0 | ⬜ |
| `WEB-020.129` | 1891–1927 | بلوک | `switchTab` | 0 | ⬜ |
| `WEB-020.130` | 1928–1941 | بلوک | `emergencyEnsurePeople` | 0 | ⬜ |
| `WEB-020.131` | 1942–1950 | بلوک | `emergencyPanel` | 0 | ⬜ |
| `WEB-020.132` | 1951–1985 | بلوک | `msgs` | 0 | ⬜ |
| `WEB-020.133` | 1986–1991 | بلوک | `emergencyLoadOrders` | 0 | ⬜ |
| `WEB-020.134` | 1992–2036 | بلوک | `daySearchInput` | 4 | ⬜ |
| `WEB-020.135` | 2037–2047 | بلوک | `emergencyClick` | 0 | ⬜ |
| `WEB-020.136` | 2048–2076 | بلوک | `person` | 0 | ⬜ |
| `WEB-020.137` | 2077–2115 | بلوک | `eventClick` | 0 | ⬜ |
| `WEB-020.138` | 2116–2211 | بلوک | `person` | 1 | ⬜ |
| `WEB-020.139` | 2212–2220 | بلوک | `dayPickFor` | 0 | ⬜ |
| `WEB-020.140` | 2221–2227 | بلوک | `trackDayPick` | 0 | ⬜ |
| `WEB-020.141` | 2228–2235 | بلوک | `orderedDayFoodIds` | 0 | ⬜ |
| `WEB-020.142` | 2236–2259 | بلوک | `eventChange` | 0 | ⬜ |
| `WEB-020.143` | 2260–2293 | بلوک | `person` | 0 | ⬜ |
| `WEB-020.144` | 2294–2376 | بلوک | `eventSubmit` | 0 | ⬜ |
| `WEB-020.145` | 2377–2382 | بلوک | `spanDays` | 0 | ⬜ |
| `WEB-020.146` | 2383–2411 | بلوک | `mount` | 0 | ⬜ |
| `WEB-020.147` | 2412–2412 | بلوک | `reportInputFocused` | 0 | ⬜ |
| `WEB-020.148` | 2413–2429 | بلوک | `safeReportRender` | 0 | ⬜ |
| `WEB-020.149` | 2430–2439 | بلوک | `html` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-021</b> — <code>food-ticket-web/assets/food-order-calendar.js</code> (103 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-021.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-021.02` | 8–14 | بلوک | `jalali` | 0 | ⬜ |
| `WEB-021.03` | 15–16 | بلوک | `pad` | 0 | ⬜ |
| `WEB-021.04` | 17–22 | بلوک | `isoDate` | 0 | ⬜ |
| `WEB-021.05` | 23–28 | بلوک | `jalaliDateFromIso` | 0 | ⬜ |
| `WEB-021.06` | 29–38 | بلوک | `monthLength` | 0 | ⬜ |
| `WEB-021.07` | 39–42 | بلوک | `previousMonth` | 0 | ⬜ |
| `WEB-021.08` | 43–46 | بلوک | `nextMonth` | 0 | ⬜ |
| `WEB-021.09` | 47–52 | بلوک | `monthCells` | 0 | ⬜ |
| `WEB-021.10` | 53–83 | بلوک | `leading` | 0 | ⬜ |
| `WEB-021.11` | 84–84 | بلوک | `shiftMonth` | 0 | ⬜ |
| `WEB-021.12` | 85–86 | بلوک | `total` | 0 | ⬜ |
| `WEB-021.13` | 87–103 | بلوک | `m` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-022</b> — <code>food-ticket-web/assets/food-order.css</code> (213 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-022.01` | 1–213 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>WEB-023</b> — <code>food-ticket-web/assets/food-ticket-global-theme-r42.css</code> (169 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-023.01` | 1–169 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |

</details>

<details>
<summary><b>WEB-024</b> — <code>food-ticket-web/assets/food-ticket-persistence.js</code> (24 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-024.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-024.02` | 6–9 | بلوک | `apiGuestCards` | 0 | ⬜ |
| `WEB-024.03` | 10–24 | بلوک | `apiEmployees` | 1 | ⬜ |

</details>

<details>
<summary><b>WEB-025</b> — <code>food-ticket-web/assets/food-ticket-theme.css</code> (421 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-025.01` | 1–421 | بلوک | `مقدمهٔ فایل` | 6 | ⬜ |

</details>

<details>
<summary><b>WEB-026</b> — <code>food-ticket-web/assets/jalali-calendar.js</code> (335 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-026.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-026.02` | 13–14 | بلوک | `pad` | 0 | ⬜ |
| `WEB-026.03` | 15–44 | بلوک | `j2g` | 0 | ⬜ |
| `WEB-026.04` | 45–66 | بلوک | `g2j` | 0 | ⬜ |
| `WEB-026.05` | 67–73 | بلوک | `monthLength` | 0 | ⬜ |
| `WEB-026.06` | 74–81 | بلوک | `parseJalali` | 0 | ⬜ |
| `WEB-026.07` | 82–85 | بلوک | `formatJalali` | 0 | ⬜ |
| `WEB-026.08` | 86–95 | بلوک | `holidaySet` | 0 | ⬜ |
| `WEB-026.09` | 96–103 | بلوک | `isWeekendJalali` | 0 | ⬜ |
| `WEB-026.10` | 104–108 | بلوک | `todayJ` | 0 | ⬜ |
| `WEB-026.11` | 109–122 | بلوک | `bindPicker` | 0 | ⬜ |
| `WEB-026.12` | 123–123 | بلوک | `close` | 0 | ⬜ |
| `WEB-026.13` | 124–125 | بلوک | `open` | 0 | ⬜ |
| `WEB-026.14` | 126–131 | بلوک | `render` | 0 | ⬜ |
| `WEB-026.15` | 132–203 | بلوک | `startIndex` | 0 | ⬜ |
| `WEB-026.16` | 204–215 | بلوک | `installOutsideClickHandler` | 0 | ⬜ |
| `WEB-026.17` | 216–222 | بلوک | `bindYearBoard` | 0 | ⬜ |
| `WEB-026.18` | 223–231 | بلوک | `render` | 0 | ⬜ |
| `WEB-026.19` | 232–268 | بلوک | `startIndex` | 0 | ⬜ |
| `WEB-026.20` | 269–335 | بلوک | `enhanceAllDateFields` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-027</b> — <code>food-ticket-web/assets/list-sort.js</code> (323 خط، 23 آدرس، بازبینی‌شده 0/23)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-027.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-027.02` | 16–19 | بلوک | `collator` | 0 | ⬜ |
| `WEB-027.03` | 20–32 | بلوک | `injectStyle` | 0 | ⬜ |
| `WEB-027.04` | 33–41 | بلوک | `normalize` | 0 | ⬜ |
| `WEB-027.05` | 42–44 | بلوک | `pad` | 0 | ⬜ |
| `WEB-027.06` | 45–55 | بلوک | `sortKey` | 4 | ⬜ |
| `WEB-027.07` | 56–64 | بلوک | `compare` | 0 | ⬜ |
| `WEB-027.08` | 65–84 | بلوک | `collect` | 0 | ⬜ |
| `WEB-027.09` | 85–98 | بلوک | `hasAny` | 0 | ⬜ |
| `WEB-027.10` | 99–112 | بلوک | `anchorFor` | 0 | ⬜ |
| `WEB-027.11` | 113–140 | بلوک | `applySort` | 0 | ⬜ |
| `WEB-027.12` | 141–156 | بلوک | `updateHeaders` | 0 | ⬜ |
| `WEB-027.13` | 157–190 | بلوک | `attach` | 0 | ⬜ |
| `WEB-027.14` | 191–205 | بلوک | `enhance` | 0 | ⬜ |
| `WEB-027.15` | 206–215 | بلوک | `scheduleEnhance` | 0 | ⬜ |
| `WEB-027.16` | 216–234 | بلوک | `buildView` | 0 | ⬜ |
| `WEB-027.17` | 235–241 | بلوک | `fileLabel` | 0 | ⬜ |
| `WEB-027.18` | 242–252 | بلوک | `saveBlob` | 0 | ⬜ |
| `WEB-027.19` | 253–269 | بلوک | `exportXlsx` | 0 | ⬜ |
| `WEB-027.20` | 270–277 | بلوک | `exportContainer` | 0 | ⬜ |
| `WEB-027.21` | 278–291 | بلوک | `activeListFor` | 0 | ⬜ |
| `WEB-027.22` | 292–302 | بلوک | `onExportClick` | 0 | ⬜ |
| `WEB-027.23` | 303–323 | بلوک | `start` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-028</b> — <code>food-ticket-web/template-bridge.js</code> (27 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-028.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-028.02` | 2–2 | بلوک | `apiBase` | 0 | ⬜ |
| `WEB-028.03` | 3–3 | بلوک | `csrf` | 0 | ⬜ |
| `WEB-028.04` | 4–13 | بلوک | `loadTemplate` | 0 | ⬜ |
| `WEB-028.05` | 14–27 | بلوک | `saveTemplate` | 0 | ⬜ |

</details>

### کنش‌ها (actions/) — `ACT`


<details>
<summary><b>ACT-001</b> — <code>actions/access-group-actions.php</code> (182 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-001.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 1 | ⬜ |
| `ACT-001.02` | 14–38 | ناحیه | `مسیر: create_access_group` | 3 | ⬜ |
| `ACT-001.03` | 39–161 | ناحیه | `مسیر: save_access_group` | 3 | ⬜ |
| `ACT-001.04` | 162–182 | ناحیه | `مسیر: delete_access_group` | 3 | ⬜ |

</details>

<details>
<summary><b>ACT-002</b> — <code>actions/account-actions.php</code> (125 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-002.01` | 1–23 | ناحیه | `بلوک رویه‌ای 1-23` | 0 | ⬜ |
| `ACT-002.02` | 24–94 | ناحیه | `مسیر: login` | 3 | ⬜ |
| `ACT-002.03` | 95–102 | ناحیه | `مسیر: save_theme` | 0 | ⬜ |
| `ACT-002.04` | 103–125 | ناحیه | `مسیر: save_profile` | 1 | ⬜ |

</details>

<details>
<summary><b>ACT-003</b> — <code>actions/asset-actions.php</code> (419 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-003.01` | 1–32 | ناحیه | `بلوک رویه‌ای 1-32` | 0 | ⬜ |
| `ACT-003.02` | 33–63 | ناحیه | `مسیر: collect_inventory` | 1 | ⬜ |
| `ACT-003.03` | 64–116 | ناحیه | `مسیر: asset_profile_selftest` | 4 | ⬜ |
| `ACT-003.04` | 117–124 | ناحیه | `مسیر: import_domain_assets` | 0 | ⬜ |
| `ACT-003.05` | 125–140 | ناحیه | `مسیر: domain_scan_start` | 2 | ⬜ |
| `ACT-003.06` | 141–177 | ناحیه | `مسیر: domain_scan_batch` | 2 | ⬜ |
| `ACT-003.07` | 178–254 | ناحیه | `مسیر: ping_assets` | **8** | ⬜ |
| `ACT-003.08` | 255–272 | ناحیه | `مسیر: domain_scan_retry` | 2 | ⬜ |
| `ACT-003.09` | 273–330 | ناحیه | `مسیر: domain_scan_test_selected` | **8** | ⬜ |
| `ACT-003.10` | 331–348 | ناحیه | `مسیر: domain_scan_selected` | 2 | ⬜ |
| `ACT-003.11` | 349–419 | ناحیه | `مسیر: save_inventory_form` | 7 | ⬜ |

</details>

<details>
<summary><b>ACT-004</b> — <code>actions/backup-actions.php</code> (53 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-004.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 1 | ⬜ |
| `ACT-004.02` | 18–24 | ناحیه | `مسیر: create_backup` | 0 | ⬜ |
| `ACT-004.03` | 25–39 | ناحیه | `مسیر: restore_backup` | 0 | ⬜ |
| `ACT-004.04` | 40–53 | ناحیه | `مسیر: system_update` | 3 | ⬜ |

</details>

<details>
<summary><b>ACT-005</b> — <code>actions/bulk-actions.php</code> (121 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-005.01` | 1–23 | ناحیه | `بلوک رویه‌ای 1-23` | 0 | ⬜ |
| `ACT-005.02` | 24–43 | ناحیه | `مسیر: add_asset_relation` | **8** | ⬜ |
| `ACT-005.03` | 44–121 | ناحیه | `مسیر: bulk_update_tickets` | 7 | ⬜ |

</details>

<details>
<summary><b>ACT-006</b> — <code>actions/cd-dvd-actions.php</code> (39 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-006.01` | 1–23 | ناحیه | `بلوک رویه‌ای 1-23` | 0 | ⬜ |
| `ACT-006.02` | 24–28 | ناحیه | `مسیر: cd_dvd_create` | 0 | ⬜ |
| `ACT-006.03` | 29–33 | ناحیه | `مسیر: cd_dvd_update` | 0 | ⬜ |
| `ACT-006.04` | 34–39 | ناحیه | `مسیر: cd_dvd_delete` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-007</b> — <code>actions/food-brand-actions.php</code> (46 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-007.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `ACT-007.02` | 22–46 | ناحیه | `مسیر: save_food_ticket_brand` | 1 | ⬜ |

</details>

<details>
<summary><b>ACT-008</b> — <code>actions/holiday-actions.php</code> (146 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-008.01` | 1–33 | ناحیه | `بلوک رویه‌ای 1-33` | 2 | ⬜ |
| `ACT-008.02` | 34–74 | ناحیه | `مسیر: toggle_holiday` | 0 | ⬜ |
| `ACT-008.03` | 75–100 | ناحیه | `مسیر: add_holiday` | 0 | ⬜ |
| `ACT-008.04` | 101–125 | ناحیه | `مسیر: import_holidays` | 0 | ⬜ |
| `ACT-008.05` | 126–146 | ناحیه | `مسیر: delete_holiday` | 2 | ⬜ |

</details>

<details>
<summary><b>ACT-009</b> — <code>actions/notification-actions.php</code> (33 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-009.01` | 1–22 | ناحیه | `بلوک رویه‌ای 1-22` | 0 | ⬜ |
| `ACT-009.02` | 23–27 | ناحیه | `مسیر: mark_all_notifications` | 1 | ⬜ |
| `ACT-009.03` | 28–33 | ناحیه | `مسیر: mark_notification` | 3 | ⬜ |

</details>

<details>
<summary><b>ACT-010</b> — <code>actions/org-actions.php</code> (843 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-010.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `ACT-010.02` | 25–46 | ناحیه | `شرط مسیریابی` | 2 | ⬜ |
| `ACT-010.03` | 47–68 | ناحیه | `شرط مسیریابی` | 7 | ⬜ |
| `ACT-010.04` | 69–205 | ناحیه | `مسیر: save_org_node` | 4 | ⬜ |
| `ACT-010.05` | 206–223 | ناحیه | `مسیر: undo_org_action` | 0 | ⬜ |
| `ACT-010.06` | 224–242 | ناحیه | `مسیر: redo_org_action` | 0 | ⬜ |
| `ACT-010.07` | 243–272 | ناحیه | `مسیر: sort_org_root_units` | 2 | ⬜ |
| `ACT-010.08` | 273–309 | ناحیه | `مسیر: sort_org_deputies` | 2 | ⬜ |
| `ACT-010.09` | 310–354 | ناحیه | `مسیر: assign_org_manager` | 0 | ⬜ |
| `ACT-010.10` | 355–381 | ناحیه | `مسیر: remove_org_manager` | 0 | ⬜ |
| `ACT-010.11` | 382–408 | ناحیه | `مسیر: remove_org_experts` | **8** | ⬜ |
| `ACT-010.12` | 409–445 | ناحیه | `مسیر: save_org_assign_users` | 7 | ⬜ |
| `ACT-010.13` | 446–564 | ناحیه | `مسیر: restore_org_backup` | 5 | ⬜ |
| `ACT-010.14` | 565–588 | ناحیه | `مسیر: reset_org_structure` | 5 | ⬜ |
| `ACT-010.15` | 589–616 | ناحیه | `مسیر: save_org_ceo` | 1 | ⬜ |
| `ACT-010.16` | 617–695 | ناحیه | `مسیر: save_org_unit` | **8** | ⬜ |
| `ACT-010.17` | 696–731 | ناحیه | `مسیر: delete_org_unit` | 5 | ⬜ |
| `ACT-010.18` | 732–765 | ناحیه | `مسیر: save_user_org` | 1 | ⬜ |
| `ACT-010.19` | 766–805 | ناحیه | `مسیر: repair_org_manager_admin_roles` | 7 | ⬜ |
| `ACT-010.20` | 806–843 | ناحیه | `مسیر: repair_org_manager_support_roles` | 7 | ⬜ |

</details>

<details>
<summary><b>ACT-011</b> — <code>actions/role-actions.php</code> (141 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-011.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `ACT-011.02` | 26–99 | ناحیه | `مسیر: save_key_roles` | 7 | ⬜ |
| `ACT-011.03` | 100–116 | ناحیه | `مسیر: save_role_permissions` | 2 | ⬜ |
| `ACT-011.04` | 117–129 | ناحیه | `مسیر: reset_role_defaults` | 0 | ⬜ |
| `ACT-011.05` | 130–141 | ناحیه | `مسیر: reset_role_permissions` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-012</b> — <code>actions/service-actions.php</code> (166 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-012.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `ACT-012.02` | 29–58 | ناحیه | `مسیر: add_service` | 3 | ⬜ |
| `ACT-012.03` | 59–73 | ناحیه | `مسیر: add_service_field` | 3 | ⬜ |
| `ACT-012.04` | 74–104 | ناحیه | `مسیر: edit_service` | 1 | ⬜ |
| `ACT-012.05` | 105–118 | ناحیه | `مسیر: delete_service` | 1 | ⬜ |
| `ACT-012.06` | 119–140 | ناحیه | `مسیر: save_service_category` | 3 | ⬜ |
| `ACT-012.07` | 141–152 | ناحیه | `مسیر: delete_service_category` | 1 | ⬜ |
| `ACT-012.08` | 153–166 | ناحیه | `مسیر: add_knowledge` | 3 | ⬜ |

</details>

<details>
<summary><b>ACT-013</b> — <code>actions/settings-actions.php</code> (265 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-013.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 0 | ⬜ |
| `ACT-013.02` | 28–46 | ناحیه | `مسیر: save_org_company_names` | 3 | ⬜ |
| `ACT-013.03` | 47–60 | ناحیه | `مسیر: prune_activity_logs` | 0 | ⬜ |
| `ACT-013.04` | 61–68 | ناحیه | `مسیر: sync_ldap_users` | 0 | ⬜ |
| `ACT-013.05` | 69–82 | ناحیه | `مسیر: diagnose_ldap` | 0 | ⬜ |
| `ACT-013.06` | 83–156 | ناحیه | `مسیر: save_triangle_effect` | 2 | ⬜ |
| `ACT-013.07` | 157–265 | ناحیه | `مسیر: save_settings` | 4 | ⬜ |

</details>

<details>
<summary><b>ACT-014</b> — <code>actions/supervisor-actions.php</code> (172 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-014.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `ACT-014.02` | 26–39 | ناحیه | `مسیر: food_order_cancel_decide` | 2 | ⬜ |
| `ACT-014.03` | 40–96 | ناحیه | `مسیر: supervisor_decision` | 7 | ⬜ |
| `ACT-014.04` | 97–130 | ناحیه | `مسیر: supervisor_reopen` | 2 | ⬜ |
| `ACT-014.05` | 131–172 | ناحیه | `مسیر: rate_ticket` | 2 | ⬜ |

</details>

<details>
<summary><b>ACT-015</b> — <code>actions/ticket-actions.php</code> (660 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-015.01` | 1–29 | ناحیه | `بلوک رویه‌ای 1-29` | 0 | ⬜ |
| `ACT-015.02` | 30–208 | ناحیه | `مسیر: create_ticket` | 3 | ⬜ |
| `ACT-015.03` | 209–257 | ناحیه | `مسیر: cancel_ticket` | 7 | ⬜ |
| `ACT-015.04` | 258–322 | ناحیه | `مسیر: decide_ticket_cancellation` | 2 | ⬜ |
| `ACT-015.05` | 323–359 | ناحیه | `مسیر: claim_ticket` | 2 | ⬜ |
| `ACT-015.06` | 360–394 | ناحیه | `مسیر: reopen_ticket` | 7 | ⬜ |
| `ACT-015.07` | 395–516 | ناحیه | `مسیر: reply` | **8** | ⬜ |
| `ACT-015.08` | 517–660 | ناحیه | `مسیر: update_ticket` | **8** | ⬜ |

</details>

<details>
<summary><b>ACT-016</b> — <code>actions/user-structure-actions.php</code> (368 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-016.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `ACT-016.02` | 29–39 | ناحیه | `مسیر: add_category` | 1 | ⬜ |
| `ACT-016.03` | 40–53 | ناحیه | `مسیر: add_department` | 1 | ⬜ |
| `ACT-016.04` | 54–159 | ناحیه | `مسیر: link_domain_food_by_code` | 7 | ⬜ |
| `ACT-016.05` | 160–318 | ناحیه | `مسیر: update_user_role` | 3 | ⬜ |
| `ACT-016.06` | 319–352 | ناحیه | `مسیر: save_department_members` | 7 | ⬜ |
| `ACT-016.07` | 353–368 | ناحیه | `مسیر: save_cd_dvd_settings` | 2 | ⬜ |

</details>

### موتور مشترک (lib/) — `CORE`


<details>
<summary><b>CORE-001</b> — <code>lib/activity-audit.php</code> (600 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-001.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-001.02` | 14–27 | تابع | `activity_logs_ensure` | 0 | ⬜ |
| `CORE-001.03` | 29–58 | تابع | `activity_client_ip` | 0 | ⬜ |
| `CORE-001.04` | 60–88 | تابع | `activity_computer_name` | 1 | ⬜ |
| `CORE-001.05` | 90–299 | تابع | `activity_action_label` | 1 | ⬜ |
| `CORE-001.06` | 301–309 | تابع | `activity_default_result` | 0 | ⬜ |
| `CORE-001.07` | 311–353 | تابع | `activity_log` | 0 | ⬜ |
| `CORE-001.08` | 354–360 | ناحیه | `بلوک رویه‌ای 354-360` | 0 | ⬜ |
| `CORE-001.09` | 361–395 | تابع | `activity_log_view` | 2 | ⬜ |
| `CORE-001.10` | 397–409 | تابع | `activity_logs_prune` | 0 | ⬜ |
| `CORE-001.11` | 410–416 | ناحیه | `بلوک رویه‌ای 410-416` | 0 | ⬜ |
| `CORE-001.12` | 417–448 | تابع | `ops_low_value_tables_prune` | 0 | ⬜ |
| `CORE-001.13` | 450–454 | تابع | `activity_log_retention_days` | 0 | ⬜ |
| `CORE-001.14` | 456–508 | تابع | `activity_audit_context` | 0 | ⬜ |
| `CORE-001.15` | 510–525 | تابع | `save_audit` | 0 | ⬜ |
| `CORE-001.16` | 527–548 | تابع | `save_audit_required` | 0 | ⬜ |
| `CORE-001.17` | 550–599 | تابع | `log_ticket_event` | 1 | ⬜ |

</details>

<details>
<summary><b>CORE-002</b> — <code>lib/asset-identity.php</code> (4 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-002.01` | 1–4 | ناحیه | `بلوک رویه‌ای 1-4` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-003</b> — <code>lib/auth-scope.php</code> (347 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-003.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-003.02` | 14–28 | تابع | `current_user` | 0 | ⬜ |
| `CORE-003.03` | 30–44 | تابع | `login_user` | 0 | ⬜ |
| `CORE-003.04` | 46–53 | تابع | `require_login` | 0 | ⬜ |
| `CORE-003.05` | 55–63 | تابع | `require_admin` | 0 | ⬜ |
| `CORE-003.06` | 65–76 | تابع | `require_primary_admin` | 0 | ⬜ |
| `CORE-003.07` | 78–86 | تابع | `require_staff` | 0 | ⬜ |
| `CORE-003.08` | 88–96 | تابع | `require_supervisor` | 0 | ⬜ |
| `CORE-003.09` | 98–132 | تابع | `ticket_manager_scope_unit_ids` | 0 | ⬜ |
| `CORE-003.10` | 134–143 | تابع | `ticket_requester_tree_predicate` | 5 | ⬜ |
| `CORE-003.11` | 145–193 | تابع | `ticket_user_can_view` | 5 | ⬜ |
| `CORE-003.12` | 196–224 | تابع | `ticket_handler_level` | 0 | ⬜ |
| `CORE-003.13` | 226–251 | تابع | `ticket_assignee_is_eligible` | 0 | ⬜ |
| `CORE-003.14` | 253–282 | تابع | `ticket_rating_is_current` | 4 | ⬜ |
| `CORE-003.15` | 284–288 | تابع | `ticket_user_is_requester` | 0 | ⬜ |
| `CORE-003.16` | 290–298 | تابع | `ticket_reply_starts_work` | 0 | ⬜ |
| `CORE-003.17` | 300–320 | تابع | `ticket_scope` | 5 | ⬜ |
| `CORE-003.18` | 322–326 | تابع | `ticket_is_waiting_status` | 0 | ⬜ |
| `CORE-003.19` | 328–333 | تابع | `user_service_group` | 0 | ⬜ |
| `CORE-003.20` | 335–345 | تابع | `asset_scope` | 5 | ⬜ |

</details>

<details>
<summary><b>CORE-004</b> — <code>lib/db-core.php</code> (579 خط، 34 آدرس، بازبینی‌شده 0/34)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-004.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-004.02` | 14–18 | تابع | `csp_nonce` | 0 | ⬜ |
| `CORE-004.03` | 19–44 | ناحیه | `بلوک رویه‌ای 19-44` | 0 | ⬜ |
| `CORE-004.04` | 45–56 | تابع | `cfg` | 0 | ⬜ |
| `CORE-004.05` | 57–59 | ناحیه | `بلوک رویه‌ای 57-59` | 0 | ⬜ |
| `CORE-004.06` | 60–86 | کلاس | `AppPDO` | 4 | ⬜ |
| `CORE-004.07` | 88–115 | کلاس | `AppSlowStatement` | 4 | ⬜ |
| `CORE-004.08` | 117–165 | تابع | `db` | **8** | ⬜ |
| `CORE-004.09` | 167–193 | تابع | `db_table_exists` | 0 | ⬜ |
| `CORE-004.10` | 195–214 | تابع | `db_table_columns` | 0 | ⬜ |
| `CORE-004.11` | 216–220 | تابع | `db_missing_columns` | 0 | ⬜ |
| `CORE-004.12` | 222–230 | تابع | `db_column_present` | 0 | ⬜ |
| `CORE-004.13` | 232–248 | تابع | `db_add_missing_column` | 4 | ⬜ |
| `CORE-004.14` | 250–292 | تابع | `db_ensure_enum_values` | **8** | ⬜ |
| `CORE-004.15` | 294–297 | تابع | `client_ip` | 0 | ⬜ |
| `CORE-004.16` | 298–300 | ناحیه | `بلوک رویه‌ای 298-300` | 0 | ⬜ |
| `CORE-004.17` | 301–328 | تابع | `api_rate_limit` | 1 | ⬜ |
| `CORE-004.18` | 330–343 | تابع | `check_api_rate_limit` | 0 | ⬜ |
| `CORE-004.19` | 345–348 | تابع | `login_rate_key` | 0 | ⬜ |
| `CORE-004.20` | 350–369 | تابع | `login_rate_allowed` | 1 | ⬜ |
| `CORE-004.21` | 371–399 | تابع | `record_login_failure` | 1 | ⬜ |
| `CORE-004.22` | 401–408 | تابع | `clear_login_failures` | 1 | ⬜ |
| `CORE-004.23` | 410–419 | تابع | `app_version` | 1 | ⬜ |
| `CORE-004.24` | 421–424 | تابع | `e` | 0 | ⬜ |
| `CORE-004.25` | 426–431 | تابع | `app_theme_mode` | 2 | ⬜ |
| `CORE-004.26` | 433–439 | تابع | `app_theme_toggle_markup` | 0 | ⬜ |
| `CORE-004.27` | 441–445 | تابع | `redirect` | 0 | ⬜ |
| `CORE-004.28` | 447–475 | تابع | `db_install_token` | 4 | ⬜ |
| `CORE-004.29` | 477–481 | تابع | `db_install_token_forget` | 0 | ⬜ |
| `CORE-004.30` | 483–488 | تابع | `db_install_fresh_tables` | 0 | ⬜ |
| `CORE-004.31` | 490–527 | تابع | `db_install_state` | 5 | ⬜ |
| `CORE-004.32` | 529–544 | تابع | `db_in_transaction` | 0 | ⬜ |
| `CORE-004.33` | 546–562 | تابع | `db_commit` | 0 | ⬜ |
| `CORE-004.34` | 564–577 | تابع | `db_rollback` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-005</b> — <code>lib/ldap.php</code> (330 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-005.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-005.02` | 14–34 | تابع | `ldap_cfg` | 0 | ⬜ |
| `CORE-005.03` | 36–51 | تابع | `ldap_role_for_groups` | 0 | ⬜ |
| `CORE-005.04` | 53–63 | تابع | `ldap_person_filter` | 0 | ⬜ |
| `CORE-005.05` | 65–80 | تابع | `ldap_auth_log` | 1 | ⬜ |
| `CORE-005.06` | 82–158 | تابع | `ldap_diagnose` | 3 | ⬜ |
| `CORE-005.07` | 160–328 | تابع | `ldap_sync_all_users` | 5 | ⬜ |

</details>

<details>
<summary><b>CORE-006</b> — <code>lib/log-catalog.php</code> (49 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-006.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `CORE-006.02` | 4–48 | تابع | `logging_catalog_definitions` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-007</b> — <code>lib/logging-audit.php</code> (643 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-007.01` | 1–45 | ناحیه | `بلوک رویه‌ای 1-45` | 1 | ⬜ |
| `CORE-007.02` | 46–50 | تابع | `logging_audit_baseline_path` | 0 | ⬜ |
| `CORE-007.03` | 52–59 | تابع | `logging_audit_structural_checks` | 0 | ⬜ |
| `CORE-007.04` | 61–82 | تابع | `logging_audit_required_tables` | 0 | ⬜ |
| `CORE-007.05` | 84–100 | تابع | `logging_audit_required_columns` | 0 | ⬜ |
| `CORE-007.06` | 102–106 | تابع | `logging_audit_finding` | 0 | ⬜ |
| `CORE-007.07` | 108–124 | تابع | `logging_audit_table_exists` | 0 | ⬜ |
| `CORE-007.08` | 126–136 | تابع | `logging_audit_table_columns` | 0 | ⬜ |
| `CORE-007.09` | 138–139 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.10` | 140–141 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.11` | 142–157 | تابع | `logging_audit_check_db_schema_tables` | 0 | ⬜ |
| `CORE-007.12` | 159–180 | تابع | `logging_audit_check_db_schema_columns` | 0 | ⬜ |
| `CORE-007.13` | 182–223 | تابع | `logging_audit_check_db_pending_migrations` | 0 | ⬜ |
| `CORE-007.14` | 225–248 | تابع | `logging_audit_check_filesystem_writable` | 1 | ⬜ |
| `CORE-007.15` | 250–251 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.16` | 252–253 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.17` | 254–276 | تابع | `logging_audit_check_spool_backlog` | 0 | ⬜ |
| `CORE-007.18` | 278–317 | تابع | `logging_audit_check_unresolved_issues` | 0 | ⬜ |
| `CORE-007.19` | 319–342 | تابع | `logging_audit_check_worker_heartbeats` | 1 | ⬜ |
| `CORE-007.20` | 344–345 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.21` | 346–347 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.22` | 348–352 | تابع | `logging_audit_heartbeat_path` | 0 | ⬜ |
| `CORE-007.23` | 354–382 | تابع | `logging_audit_check_audit_continuity` | 1 | ⬜ |
| `CORE-007.24` | 384–398 | تابع | `logging_audit_write_heartbeat` | 2 | ⬜ |
| `CORE-007.25` | 400–432 | تابع | `logging_audit_summarize` | 0 | ⬜ |
| `CORE-007.26` | 434–447 | تابع | `logging_audit_checks` | 0 | ⬜ |
| `CORE-007.27` | 449–457 | تابع | `logging_audit_load_baseline` | 1 | ⬜ |
| `CORE-007.28` | 459–472 | تابع | `logging_audit_save_baseline` | 2 | ⬜ |
| `CORE-007.29` | 474–506 | تابع | `logging_audit_run` | 0 | ⬜ |
| `CORE-007.30` | 508–642 | تابع | `logging_audit_run_and_record` | 7 | ⬜ |

</details>

<details>
<summary><b>CORE-008</b> — <code>lib/logging-core.php</code> (1,559 خط، 65 آدرس، بازبینی‌شده 0/65)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-008.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `CORE-008.02` | 21–36 | تابع | `logging_uuid` | 0 | ⬜ |
| `CORE-008.03` | 38–45 | تابع | `logging_deterministic_uuid` | 0 | ⬜ |
| `CORE-008.04` | 47–50 | تابع | `logging_is_uuid_v4` | 0 | ⬜ |
| `CORE-008.05` | 52–55 | تابع | `logging_is_uuid` | 0 | ⬜ |
| `CORE-008.06` | 57–63 | تابع | `logging_request_id` | 0 | ⬜ |
| `CORE-008.07` | 65–69 | تابع | `logging_trace_id` | 0 | ⬜ |
| `CORE-008.08` | 71–75 | تابع | `logging_operation_id` | 0 | ⬜ |
| `CORE-008.09` | 77–87 | تابع | `logging_begin_execution` | 0 | ⬜ |
| `CORE-008.10` | 89–96 | تابع | `logging_begin_operation` | 0 | ⬜ |
| `CORE-008.11` | 98–101 | تابع | `logging_operation_name` | 0 | ⬜ |
| `CORE-008.12` | 103–113 | تابع | `logging_correlation_context` | 0 | ⬜ |
| `CORE-008.13` | 115–119 | تابع | `logging_parent_event_id` | 0 | ⬜ |
| `CORE-008.14` | 121–131 | تابع | `logging_bootstrap` | 0 | ⬜ |
| `CORE-008.15` | 133–141 | تابع | `logging_catalog_aliases` | 0 | ⬜ |
| `CORE-008.16` | 143–151 | تابع | `logging_event_catalog` | 0 | ⬜ |
| `CORE-008.17` | 153–173 | تابع | `logging_catalog_resolve` | 0 | ⬜ |
| `CORE-008.18` | 175–319 | تابع | `logging_action_event_definition` | 1 | ⬜ |
| `CORE-008.19` | 321–328 | تابع | `logging_normalize_digits` | 0 | ⬜ |
| `CORE-008.20` | 330–340 | تابع | `logging_is_valid_iranian_national_code` | 0 | ⬜ |
| `CORE-008.21` | 342–345 | تابع | `logging_strict_national_code_enabled` | 0 | ⬜ |
| `CORE-008.22` | 347–368 | تابع | `logging_sensitive_key` | 0 | ⬜ |
| `CORE-008.23` | 370–378 | تابع | `logging_utf8_substr` | 0 | ⬜ |
| `CORE-008.24` | 380–388 | تابع | `logging_utf8_strlen` | 0 | ⬜ |
| `CORE-008.25` | 390–408 | تابع | `logging_sanitize_text` | 0 | ⬜ |
| `CORE-008.26` | 410–416 | تابع | `logging_sanitize_single_line` | 0 | ⬜ |
| `CORE-008.27` | 417–418 | ناحیه | `بلوک رویه‌ای 417-418` | 0 | ⬜ |
| `CORE-008.28` | 419–428 | تابع | `logging_fingerprint_v2` | 0 | ⬜ |
| `CORE-008.29` | 430–435 | تابع | `logging_fingerprint_v3` | 0 | ⬜ |
| `CORE-008.30` | 437–442 | تابع | `logging_fingerprint_current` | 0 | ⬜ |
| `CORE-008.31` | 444–468 | تابع | `logging_exception_trace` | 0 | ⬜ |
| `CORE-008.32` | 470–631 | تابع | `logging_sanitize_event_meta` | 1 | ⬜ |
| `CORE-008.33` | 633–671 | تابع | `logging_sanitize_value` | 0 | ⬜ |
| `CORE-008.34` | 673–681 | تابع | `logging_safe_request_uri` | 0 | ⬜ |
| `CORE-008.35` | 683–779 | تابع | `logging_spool_write` | 3 | ⬜ |
| `CORE-008.36` | 781–785 | تابع | `logging_spool_stats_cache_invalidate` | 5 | ⬜ |
| `CORE-008.37` | 787–805 | تابع | `logging_spool_stats` | 3 | ⬜ |
| `CORE-008.38` | 807–868 | تابع | `logging_spool_stats_uncached` | 3 | ⬜ |
| `CORE-008.39` | 870–924 | تابع | `logging_increment_counter` | 3 | ⬜ |
| `CORE-008.40` | 926–958 | تابع | `logging_health_counters` | 1 | ⬜ |
| `CORE-008.41` | 960–981 | تابع | `logging_atomic_replace_file` | 7 | ⬜ |
| `CORE-008.42` | 983–1023 | تابع | `logging_worker_heartbeat` | 7 | ⬜ |
| `CORE-008.43` | 1025–1072 | تابع | `logging_emergency_failure` | **11** | ⬜ |
| `CORE-008.44` | 1074–1077 | تابع | `logging_emergency_failure_rotation_warning` | 0 | ⬜ |
| `CORE-008.45` | 1079–1083 | تابع | `logging_persistence_status` | 0 | ⬜ |
| `CORE-008.46` | 1085–1102 | تابع | `logging_record_failure` | 0 | ⬜ |
| `CORE-008.47` | 1104–1108 | تابع | `logging_json` | 0 | ⬜ |
| `CORE-008.48` | 1111–1153 | تابع | `logging_append_legacy_file` | 7 | ⬜ |
| `CORE-008.49` | 1155–1173 | تابع | `logging_replay_insert` | 6 | ⬜ |
| `CORE-008.50` | 1175–1236 | تابع | `logging_issue_upsert` | **8** | ⬜ |
| `CORE-008.51` | 1238–1251 | تابع | `logging_db_error_is_transient` | 0 | ⬜ |
| `CORE-008.52` | 1253–1269 | تابع | `logging_replay_error_classify` | 0 | ⬜ |
| `CORE-008.53` | 1271–1275 | تابع | `logging_db_circuit_path` | 0 | ⬜ |
| `CORE-008.54` | 1277–1282 | تابع | `logging_db_circuit_release_probe` | 2 | ⬜ |
| `CORE-008.55` | 1284–1296 | تابع | `logging_db_circuit_with_lock` | 3 | ⬜ |
| `CORE-008.56` | 1298–1319 | تابع | `logging_db_circuit_should_skip` | 3 | ⬜ |
| `CORE-008.57` | 1321–1336 | تابع | `logging_db_circuit_failure` | 7 | ⬜ |
| `CORE-008.58` | 1338–1344 | تابع | `logging_db_circuit_success` | **9** | ⬜ |
| `CORE-008.59` | 1346–1362 | تابع | `logging_duplicate_event_already_stored` | 5 | ⬜ |
| `CORE-008.60` | 1364–1410 | تابع | `logging_store_system` | 1 | ⬜ |
| `CORE-008.61` | 1412–1443 | تابع | `logging_normalize_activity_event` | 0 | ⬜ |
| `CORE-008.62` | 1445–1513 | تابع | `logging_store_activity` | 1 | ⬜ |
| `CORE-008.63` | 1515–1529 | تابع | `log_event` | 0 | ⬜ |
| `CORE-008.64` | 1530–1532 | ناحیه | `بلوک رویه‌ای 1530-1532` | 0 | ⬜ |
| `CORE-008.65` | 1533–1558 | تابع | `security_log_access_denied` | 4 | ⬜ |

</details>

<details>
<summary><b>CORE-009</b> — <code>lib/logging-csrf.php</code> (512 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-009.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-009.02` | 14–17 | تابع | `flash` | 0 | ⬜ |
| `CORE-009.03` | 19–54 | تابع | `ticket_service_ensure_schema` | 5 | ⬜ |
| `CORE-009.04` | 56–76 | تابع | `ticket_service_categories` | 0 | ⬜ |
| `CORE-009.05` | 78–98 | تابع | `ticket_service_category_map` | 0 | ⬜ |
| `CORE-009.06` | 100–112 | تابع | `system_logs_ensure` | 0 | ⬜ |
| `CORE-009.07` | 114–137 | تابع | `system_log` | 0 | ⬜ |
| `CORE-009.08` | 139–186 | تابع | `system_error_explain` | 0 | ⬜ |
| `CORE-009.09` | 188–192 | تابع | `system_error_mask` | 0 | ⬜ |
| `CORE-009.10` | 194–238 | تابع | `system_error_record` | 0 | ⬜ |
| `CORE-009.11` | 239–328 | ناحیه | `بلوک رویه‌ای 239-328` | 2 | ⬜ |
| `CORE-009.12` | 329–348 | تابع | `app_error_log` | 0 | ⬜ |
| `CORE-009.13` | 350–376 | تابع | `system_log_meta_html` | 0 | ⬜ |
| `CORE-009.14` | 378–401 | تابع | `db_delete_in_batches` | 6 | ⬜ |
| `CORE-009.15` | 403–427 | تابع | `system_logs_prune` | 0 | ⬜ |
| `CORE-009.16` | 429–450 | تابع | `system_logs_recent` | 5 | ⬜ |
| `CORE-009.17` | 452–457 | تابع | `take_flash` | 0 | ⬜ |
| `CORE-009.18` | 459–462 | تابع | `csrf_field` | 0 | ⬜ |
| `CORE-009.19` | 464–497 | تابع | `csrf_token` | 2 | ⬜ |
| `CORE-009.20` | 499–510 | تابع | `require_csrf` | 6 | ⬜ |

</details>

<details>
<summary><b>CORE-010</b> — <code>lib/logging-diagnostics.php</code> (118 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-010.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `CORE-010.02` | 4–16 | تابع | `logging_diagnostic_rules` | 0 | ⬜ |
| `CORE-010.03` | 18–51 | تابع | `logging_diagnostic_analyze` | 0 | ⬜ |
| `CORE-010.04` | 53–63 | تابع | `logging_diagnostic_safe_value` | 1 | ⬜ |
| `CORE-010.05` | 65–77 | تابع | `logging_diagnostic_safe_stack` | 0 | ⬜ |
| `CORE-010.06` | 79–96 | تابع | `logging_diagnostic_similarity` | 0 | ⬜ |
| `CORE-010.07` | 98–117 | تابع | `logging_diagnostic_redact_event` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-011</b> — <code>lib/logging-integrity.php</code> (238 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-011.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `CORE-011.02` | 4–31 | تابع | `logging_integrity_manifest_hmac` | 0 | ⬜ |
| `CORE-011.03` | 33–55 | تابع | `logging_integrity_verify_archive_file` | 1 | ⬜ |
| `CORE-011.04` | 57–64 | تابع | `logging_integrity_has_older_snapshot` | 0 | ⬜ |
| `CORE-011.05` | 66–99 | تابع | `logging_integrity_verify_predecessor_chain` | 1 | ⬜ |
| `CORE-011.06` | 101–114 | تابع | `logging_integrity_verify_daily_manifest_error` | 0 | ⬜ |
| `CORE-011.07` | 116–119 | تابع | `logging_integrity_verify_daily_manifest` | 0 | ⬜ |
| `CORE-011.08` | 121–159 | تابع | `logging_integrity_read_archive_index` | 1 | ⬜ |
| `CORE-011.09` | 161–178 | تابع | `logging_integrity_merge_archive_indexes` | 0 | ⬜ |
| `CORE-011.10` | 180–198 | تابع | `logging_integrity_row_is_late` | 0 | ⬜ |
| `CORE-011.11` | 200–237 | تابع | `logging_integrity_verify_supplement_chain` | 2 | ⬜ |

</details>

<details>
<summary><b>CORE-012</b> — <code>lib/logging-replay.php</code> (261 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-012.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `CORE-012.02` | 6–43 | تابع | `logging_replay_poison_attempts` | 7 | ⬜ |
| `CORE-012.03` | 45–260 | تابع | `logging_replay_process_directory` | **9** | ⬜ |

</details>

<details>
<summary><b>CORE-013</b> — <code>lib/notify-users.php</code> (510 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-013.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-013.02` | 14–20 | تابع | `notify_user` | 1 | ⬜ |
| `CORE-013.03` | 22–34 | تابع | `notification_target_url` | 0 | ⬜ |
| `CORE-013.04` | 36–59 | تابع | `ticket_notification_user_can` | 0 | ⬜ |
| `CORE-013.05` | 61–81 | تابع | `ticket_notification_recipients` | 5 | ⬜ |
| `CORE-013.06` | 83–88 | تابع | `ticket_unassigned_notify_candidates` | 0 | ⬜ |
| `CORE-013.07` | 90–110 | تابع | `notify_ticket_parties` | 0 | ⬜ |
| `CORE-013.08` | 112–131 | تابع | `notify_ticket_staff` | 0 | ⬜ |
| `CORE-013.09` | 133–158 | تابع | `notification_user_sees_food_cancel` | 0 | ⬜ |
| `CORE-013.10` | 160–167 | تابع | `notification_visibility_sql` | 0 | ⬜ |
| `CORE-013.11` | 169–177 | تابع | `notification_ticket_visibility` | 5 | ⬜ |
| `CORE-013.12` | 179–200 | تابع | `notification_header_summary` | 5 | ⬜ |
| `CORE-013.13` | 202–207 | تابع | `is_sla_holiday` | 0 | ⬜ |
| `CORE-013.14` | 209–237 | تابع | `ldap_authenticate` | 0 | ⬜ |
| `CORE-013.15` | 239–411 | تابع | `ldap_authenticate_measured` | 4 | ⬜ |
| `CORE-013.16` | 413–418 | تابع | `normalize_employee_number` | 0 | ⬜ |
| `CORE-013.17` | 419–420 | ناحیه | `بلوک رویه‌ای 419-420` | 0 | ⬜ |
| `CORE-013.18` | 421–429 | تابع | `employee_number_owner` | 0 | ⬜ |
| `CORE-013.19` | 431–497 | تابع | `upsert_domain_user` | 1 | ⬜ |
| `CORE-013.20` | 499–508 | تابع | `link_agent_assets_to_user` | 1 | ⬜ |

</details>

<details>
<summary><b>CORE-014</b> — <code>lib/settings-effects.php</code> (386 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-014.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-014.02` | 14–36 | تابع | `setting` | 0 | ⬜ |
| `CORE-014.03` | 38–44 | تابع | `setting_forget_cache` | 0 | ⬜ |
| `CORE-014.04` | 46–55 | تابع | `save_setting` | 1 | ⬜ |
| `CORE-014.05` | 57–113 | تابع | `triangle_effect_page_catalog` | 0 | ⬜ |
| `CORE-014.06` | 115–124 | تابع | `triangle_effect_valid_keys` | 0 | ⬜ |
| `CORE-014.07` | 126–142 | تابع | `triangle_effect_config_spec` | 0 | ⬜ |
| `CORE-014.08` | 144–175 | تابع | `triangle_effect_sanitize_config` | 0 | ⬜ |
| `CORE-014.09` | 177–181 | تابع | `display_effect_type` | 0 | ⬜ |
| `CORE-014.10` | 183–196 | تابع | `particle_effect_config_spec` | 0 | ⬜ |
| `CORE-014.11` | 198–227 | تابع | `particle_effect_sanitize_config` | 0 | ⬜ |
| `CORE-014.12` | 229–234 | تابع | `particle_effect_config` | 0 | ⬜ |
| `CORE-014.13` | 236–244 | تابع | `particle_effect_enabled_for_current_page` | 0 | ⬜ |
| `CORE-014.14` | 246–251 | تابع | `triangle_effect_config` | 0 | ⬜ |
| `CORE-014.15` | 253–275 | تابع | `triangle_effect_enabled_pages` | 0 | ⬜ |
| `CORE-014.16` | 277–289 | تابع | `triangle_effect_current_page_key` | 2 | ⬜ |
| `CORE-014.17` | 291–321 | تابع | `triangle_effect_script_tag` | 0 | ⬜ |
| `CORE-014.18` | 323–353 | تابع | `particle_effect_script_tag` | 0 | ⬜ |
| `CORE-014.19` | 355–362 | تابع | `key_role_setting_map` | 0 | ⬜ |
| `CORE-014.20` | 364–379 | تابع | `key_role_user_ids` | 0 | ⬜ |
| `CORE-014.21` | 381–384 | تابع | `user_is_key_role` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-015</b> — <code>lib/tickets-sla.php</code> (672 خط، 31 آدرس، بازبینی‌شده 0/31)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-015.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `CORE-015.02` | 20–24 | تابع | `ticket_number_cache_set` | 0 | ⬜ |
| `CORE-015.03` | 26–46 | تابع | `ticket_number` | 0 | ⬜ |
| `CORE-015.04` | 48–57 | تابع | `ticket_number_prefix` | 0 | ⬜ |
| `CORE-015.05` | 59–80 | تابع | `ticket_number_ensure_schema` | 0 | ⬜ |
| `CORE-015.06` | 82–153 | تابع | `ticket_hot_schema_ensure` | 4 | ⬜ |
| `CORE-015.07` | 155–209 | تابع | `ticket_number_ensure_schema_run` | **8** | ⬜ |
| `CORE-015.08` | 211–247 | تابع | `ticket_number_next` | 0 | ⬜ |
| `CORE-015.09` | 249–261 | تابع | `ticket_number_peek` | 0 | ⬜ |
| `CORE-015.10` | 263–312 | تابع | `ticket_number_autofill` | **10** | ⬜ |
| `CORE-015.11` | 314–318 | تابع | `is_it_agent` | 0 | ⬜ |
| `CORE-015.12` | 320–348 | تابع | `jalali_to_gregorian` | 0 | ⬜ |
| `CORE-015.13` | 350–376 | تابع | `gregorian_to_jalali` | 0 | ⬜ |
| `CORE-015.14` | 378–387 | تابع | `jalali_date` | 0 | ⬜ |
| `CORE-015.15` | 389–408 | تابع | `jalali_input_to_gregorian` | 0 | ⬜ |
| `CORE-015.16` | 410–425 | تابع | `find_or_create_department` | 1 | ⬜ |
| `CORE-015.17` | 427–430 | تابع | `persian_date` | 0 | ⬜ |
| `CORE-015.18` | 432–435 | تابع | `priority_sla_minutes` | 0 | ⬜ |
| `CORE-015.19` | 437–460 | تابع | `ensure_default_ola_policies` | 5 | ⬜ |
| `CORE-015.20` | 462–472 | تابع | `ola_policy_for` | 0 | ⬜ |
| `CORE-015.21` | 474–490 | تابع | `ola_deadlines` | 0 | ⬜ |
| `CORE-015.22` | 492–505 | تابع | `next_business_start` | 0 | ⬜ |
| `CORE-015.23` | 507–521 | تابع | `business_due_at` | 0 | ⬜ |
| `CORE-015.24` | 523–545 | تابع | `business_elapsed_minutes` | 0 | ⬜ |
| `CORE-015.25` | 547–563 | تابع | `ticket_sla_cycle_start` | 0 | ⬜ |
| `CORE-015.26` | 565–597 | تابع | `ticket_sla_transition` | 5 | ⬜ |
| `CORE-015.27` | 599–646 | تابع | `ticket_ola_transition` | 0 | ⬜ |
| `CORE-015.28` | 648–655 | تابع | `record_asset_history` | 1 | ⬜ |
| `CORE-015.29` | 657–660 | تابع | `status_label` | 0 | ⬜ |
| `CORE-015.30` | 662–665 | تابع | `priority_label` | 0 | ⬜ |
| `CORE-015.31` | 667–670 | تابع | `ticket_type_label` | 0 | ⬜ |

</details>

### زمان‌بندها (cron/) — `CRON`


<details>
<summary><b>CRON-001</b> — <code>cron/check_logging_health.php</code> (240 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-001.01` | 1–240 | ناحیه | `بلوک رویه‌ای 1-240` | **13** | ⬜ |

</details>

<details>
<summary><b>CRON-002</b> — <code>cron/deliver_logging_issue_notifications.php</code> (93 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-002.01` | 1–93 | ناحیه | `بلوک رویه‌ای 1-93` | 6 | ⬜ |

</details>

<details>
<summary><b>CRON-003</b> — <code>cron/domain_scan_worker.php</code> (65 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-003.01` | 1–65 | ناحیه | `بلوک رویه‌ای 1-65` | 0 | ⬜ |

</details>

<details>
<summary><b>CRON-004</b> — <code>cron/export_logging_agent_bundle.php</code> (55 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-004.01` | 1–55 | ناحیه | `بلوک رویه‌ای 1-55` | 7 | ⬜ |

</details>

<details>
<summary><b>CRON-005</b> — <code>cron/export_logging_integrity.php</code> (262 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-005.01` | 1–262 | ناحیه | `بلوک رویه‌ای 1-262` | **8** | ⬜ |

</details>

<details>
<summary><b>CRON-006</b> — <code>cron/food_ticket_worker.php</code> (313 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-006.01` | 1–313 | ناحیه | `بلوک رویه‌ای 1-313` | **14** | ⬜ |

</details>

<details>
<summary><b>CRON-007</b> — <code>cron/ldap_sync.php</code> (28 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-007.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |

</details>

<details>
<summary><b>CRON-008</b> — <code>cron/prune_activity_logs.php</code> (24 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-008.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |

</details>

<details>
<summary><b>CRON-009</b> — <code>cron/rebuild_logging_issues.php</code> (53 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-009.01` | 1–53 | ناحیه | `بلوک رویه‌ای 1-53` | **12** | ⬜ |

</details>

<details>
<summary><b>CRON-010</b> — <code>cron/replay_logging_spool.php</code> (82 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-010.01` | 1–82 | ناحیه | `بلوک رویه‌ای 1-82` | 7 | ⬜ |

</details>

<details>
<summary><b>CRON-011</b> — <code>cron/sla_maintenance.php</code> (94 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-011.01` | 1–94 | ناحیه | `بلوک رویه‌ای 1-94` | 5 | ⬜ |

</details>

<details>
<summary><b>CRON-012</b> — <code>cron/ticket_auto_close.php</code> (78 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-012.01` | 1–78 | ناحیه | `بلوک رویه‌ای 1-78` | 5 | ⬜ |

</details>

<details>
<summary><b>CRON-013</b> — <code>cron/verify_logging_integrity.php</code> (27 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-013.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 0 | ⬜ |

</details>

<details>
<summary><b>CRON-014</b> — <code>cron/verify_logging_release.php</code> (26 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-014.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 4 | ⬜ |

</details>

### رابط برنامه (api/) — `API`


<details>
<summary><b>API-001</b> — <code>api/inventory.php</code> (206 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `API-001.01` | 1–10 | ناحیه | `بلوک رویه‌ای 1-10` | 0 | ⬜ |
| `API-001.02` | 11–16 | تابع | `inventory_api_response` | 0 | ⬜ |
| `API-001.03` | 18–22 | تابع | `inventory_api_text` | 0 | ⬜ |
| `API-001.04` | 24–28 | تابع | `inventory_api_json` | 0 | ⬜ |
| `API-001.05` | 30–47 | تابع | `inventory_api_asset_tag` | 0 | ⬜ |
| `API-001.06` | 49–61 | تابع | `inventory_api_manual_fields` | 0 | ⬜ |
| `API-001.07` | 62–206 | ناحیه | `بلوک رویه‌ای 62-206` | 6 | ⬜ |

</details>

