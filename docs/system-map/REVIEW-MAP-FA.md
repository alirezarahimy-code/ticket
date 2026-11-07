# نقشهٔ کد سامانه — فهرست آدرس‌دهی

> این فایل **تولید می‌شود**. دستی ویرایشش نکنید.
>
> بازتولید: `php tools/code_index.php > docs/system-map/BLOCKS.json` سپس `python tools/render_code_map.py`

## وضعیت بازبینی

| سنجه | مقدار |
|---|---|
| فایل‌های کد تولیدی | 574 |
| خطوط | 129,118 |
| آدرس‌ها (بلوک) | 6,791 |
| موضوع‌های قابل جست‌وجو | 40 صفحه + 101 مجوز + 84 جدول |
| بازبینی‌شده | 0 آدرس (0.0%) — 0 خط (0.0%) |
| باقی‌مانده | 6,791 آدرس |

## 🔎 فهرست موضوعی — از اینجا شروع کنید

دنبال بخشی از برنامه می‌گردید؟ **اینجا `Ctrl+F` بزنید**، نه در کد. نام فارسی بخش را بنویسید و مستقیم به آدرسش بروید.

### صفحه‌های سامانه

| بخش | نام فنی | مجوز لازم | آدرس‌های اصلی | کل |
|---|---|---|---|---|
| **audit** | `audit` | — | `PAGE-034.70` <sub>index.php:1534</sub><br>`CORE-007.33` <sub>lib/logging-audit.php:508</sub><br>`CORE-001.14` <sub>lib/activity-audit.php:456</sub><br>`CORE-013.03` <sub>lib/logging-replay.php:45</sub> | 28 |
| **chatbot api** | `chatbot-api` | — | `PAGE-034.48` <sub>index.php:1321</sub> | 2 |
| **domain scan** | `domain-scan` | `domain.scan` | `PAGE-034.84` <sub>index.php:2117</sub><br>`PAGE-013.37` <sub>domain-scan.php:1157</sub><br>`PAGE-013.19` <sub>domain-scan.php:390</sub><br>`PAGE-013.16` <sub>domain-scan.php:311</sub> | 48 |
| **food personnel link** | `food-personnel-link` | — | `PAGE-034.44` <sub>index.php:1189</sub><br>`PAGE-016.09` <sub>food-personnel-link.php:205</sub><br>`PAGE-016.04` <sub>food-personnel-link.php:39</sub><br>`PAGE-016.10` <sub>food-personnel-link.php:317</sub> | 30 |
| **food ticket** | `food-ticket` | — | `PAGE-034.40` <sub>index.php:902</sub><br>`PAGE-034.52` <sub>index.php:1377</sub><br>`PAGE-034.59` <sub>index.php:1430</sub><br>`TOOL-029.04` <sub>tools/food_system_router.php:21</sub> | 147 |
| **governance** | `governance` | — | `PAGE-031.32` <sub>governance.php:402</sub><br>`PAGE-034.58` <sub>index.php:1426</sub><br>`PAGE-031.29` <sub>governance.php:317</sub><br>`PAGE-031.10` <sub>governance.php:54</sub> | 42 |
| **help** | `help` | — | `PAGE-034.66` <sub>index.php:1463</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 4 |
| **local accounts** | `local-accounts` | — | `PAGE-034.56` <sub>index.php:1405</sub><br>`PAGE-037.03` <sub>local-accounts.php:25</sub><br>`PAGE-037.05` <sub>local-accounts.php:81</sub><br>`PAGE-037.06` <sub>local-accounts.php:138</sub> | 10 |
| **logging dashboard** | `logging-dashboard` | — | `PAGE-034.71` <sub>index.php:1543</sub><br>`ASSET-016.01` <sub>assets/logging-dashboard.css:1</sub><br>`PAGE-038.04` <sub>logging-dashboard.php:114</sub><br>`PAGE-038.02` <sub>logging-dashboard.php:4</sub> | 10 |
| **system health** | `system-health` | — | `PAGE-034.72` <sub>index.php:1547</sub><br>`PAGE-064.28` <sub>system-health.php:947</sub><br>`PAGE-064.03` <sub>system-health.php:25</sub><br>`PAGE-064.04` <sub>system-health.php:43</sub> | 35 |
| **اعلان‌ها** | `notifications` | — | `PAGE-034.69` <sub>index.php:1516</sub><br>`ACT-009.03` <sub>actions/notification-actions.php:23</sub><br>`PAGE-034.68` <sub>index.php:1508</sub><br>`DB-002.30` <sub>install-empty-db.sql:442</sub> | 41 |
| **به‌روزرسانی و پشتیبان‌گیری** | `backup` | `backup.view` | `PAGE-007.04` <sub>backup.php:32</sub><br>`PAGE-007.02` <sub>backup.php:6</sub><br>`PAGE-007.03` <sub>backup.php:12</sub><br>`PAGE-007.05` <sub>backup.php:78</sub> | 42 |
| **تاریخچه CD/DVD** | `cd-dvd-history` | `cddvd.history` | `PAGE-034.63` <sub>index.php:1451</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 11 |
| **تاریخچه دارایی** | `asset-history` | `asset.history` | `PAGE-034.83` <sub>index.php:2099</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 4 |
| **تحلیل و چارت‌ها** | `analytics` | `analytics.view` | `PAGE-045.02` <sub>modules/request-center/Pages/analytics.php:6</sub><br>`PAGE-045.01` <sub>modules/request-center/Pages/analytics.php:1</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 8 |
| **تست جمع‌آوری اطلاعات** | `inventory-diagnostics` | `inventory.diagnostics` | `PAGE-034.82` <sub>index.php:2067</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 7 |
| **تغییر رمز عبور** | `change-password` | — | `PAGE-034.38` <sub>index.php:877</sub><br>`PAGE-034.55` <sub>index.php:1402</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 7 |
| **تقویم SLA** | `holidays` | `holidays.view` | `ACT-008.07` <sub>actions/holiday-actions.php:102</sub><br>`PAGE-034.79` <sub>index.php:1942</sub><br>`DB-002.35` <sub>install-empty-db.sql:516</sub><br>`DB-005.26` <sub>migrations/003-sync-schema-151.sql:386</sub> | 34 |
| **تنظیمات سامانه** | `settings` | — | `PAGE-034.88` <sub>index.php:2465</sub><br>`PAGE-034.47` <sub>index.php:1310</sub><br>`PAGE-034.54` <sub>index.php:1393</sub><br>`DB-002.04` <sub>install-empty-db.sql:22</sub> | 61 |
| **ثبت درخواست** | `new-ticket` | — | `PAGE-047.02` <sub>modules/request-center/Pages/create.php:12</sub><br>`PAGE-034.80` <sub>index.php:1953</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 10 |
| **جزئیات درخواست** | `ticket` | — | `PAGE-048.02` <sub>modules/request-center/Pages/detail.php:11</sub><br>`PAGE-026.09` <sub>food-ticket-templates.php:153</sub><br>`PAGE-026.24` <sub>food-ticket-templates.php:502</sub><br>`PAGE-026.56` <sub>food-ticket-templates.php:2059</sub> | 26 |
| **جست‌وجو** | `search` | — | `PAGE-034.77` <sub>index.php:1747</sub><br>`PAGE-034.76` <sub>index.php:1739</sub><br>`PAGE-030.03` <sub>global-search.php:31</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 44 |
| **دارایی‌ها** | `assets` | `assets.view` | `ACT-003.08` <sub>actions/asset-actions.php:190</sub><br>`PAGE-034.85` <sub>index.php:2195</sub><br>`DB-002.36` <sub>install-empty-db.sql:526</sub><br>`DB-005.27` <sub>migrations/003-sync-schema-151.sql:395</sub> | 177 |
| **داشبورد** | `dashboard` | `dash.view` | `PAGE-034.90` <sub>index.php:2957</sub><br>`CORE-015.16` <sub>lib/settings-effects.php:277</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 27 |
| **دانش‌نامه** | `knowledge` | `knowledge.view` | `PAGE-034.67` <sub>index.php:1471</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 8 |
| **سفارش غذا** | `food-order` | — | `PAGE-034.41` <sub>index.php:916</sub><br>`PAGE-034.60` <sub>index.php:1435</sub><br>`PAGE-034.61` <sub>index.php:1443</sub><br>`TOOL-029.03` <sub>tools/food_system_router.php:18</sub> | 196 |
| **شناسنامه دارایی** | `asset` | `asset.view` | `PAGE-034.86` <sub>index.php:2240</sub><br>`PAGE-004.02` <sub>asset-inventory-page.php:4</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 11 |
| **شناسنامه سیستم‌ها** | `inventory` | `inventory.view` | `PAGE-036.31` <sub>inventory.php:704</sub><br>`PAGE-036.29` <sub>inventory.php:636</sub><br>`PAGE-036.02` <sub>inventory.php:7</sub><br>`PAGE-036.20` <sub>inventory.php:346</sub> | 56 |
| **صف کاری** | `queue` | `queue.view` | `PAGE-050.02` <sub>modules/request-center/Pages/queue.php:6</sub><br>`PAGE-050.01` <sub>modules/request-center/Pages/queue.php:1</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 19 |
| **لاگ سامانه** | `activity-log` | — | `PAGE-034.74` <sub>index.php:1557</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 12 |
| **مدیریت بات** | `chatbot-admin` | `chatbot.manage` | `PAGE-034.78` <sub>index.php:1839</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 7 |
| **مدیریت سازمان** | `organization` | `org.view` | `PAGE-058.39` <sub>organization.php:486</sub><br>`PAGE-034.87` <sub>index.php:2274</sub><br>`PAGE-034.39` <sub>index.php:888</sub><br>`PAGE-058.02` <sub>organization.php:4</sub> | 193 |
| **پروفایل کاربری** | `profile` | `profile.edit` | `PAGE-034.57` <sub>index.php:1408</sub><br>`PAGE-006.16` <sub>asset-profile.php:494</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 12 |
| **پنل سوپروایزر** | `supervisor` | `supervisor.panel` | `PAGE-054.02` <sub>modules/request-center/Pages/supervisor.php:9</sub><br>`PAGE-054.01` <sub>modules/request-center/Pages/supervisor.php:1</sub><br>`PAGE-053.01` <sub>modules/request-center/Pages/supervisor-workspace.php:1</sub><br>`CORE-003.08` <sub>lib/auth-scope.php:88</sub> | 70 |
| **چاپ کنترل تردد** | `traffic-control-print` | `traffic.view` | `PAGE-034.65` <sub>index.php:1458</sub> | 6 |
| **کاتالوگ خدمات** | `services` | `services.view` | `PAGE-052.02` <sub>modules/request-center/Pages/services.php:6</sub><br>`PAGE-052.05` <sub>modules/request-center/Pages/services.php:111</sub><br>`PAGE-052.06` <sub>modules/request-center/Pages/services.php:135</sub><br>`PAGE-052.07` <sub>modules/request-center/Pages/services.php:149</sub> | 16 |
| **کنترل CD/DVD** | `cd-dvd` | `cddvd.view` | `PAGE-034.62` <sub>index.php:1447</sub><br>`PAGE-009.43` <sub>cd-dvd.php:735</sub><br>`PAGE-009.33` <sub>cd-dvd.php:403</sub><br>`PAGE-009.19` <sub>cd-dvd.php:153</sub> | 68 |
| **کنترل تردد** | `traffic-control` | `traffic.view` | `PAGE-034.42` <sub>index.php:928</sub><br>`PAGE-034.64` <sub>index.php:1455</sub><br>`PAGE-066.63` <sub>traffic-control.php:981</sub><br>`PAGE-066.16` <sub>traffic-control.php:119</sub> | 102 |
| **گزارش خطا** | `logs` | — | `PAGE-034.89` <sub>index.php:2935</sub><br>`PAGE-034.73` <sub>index.php:1551</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 16 |
| **گزارش‌ها** | `reports` | `reports.view` | `PAGE-051.02` <sub>modules/request-center/Pages/reports.php:6</sub><br>`PAGE-051.01` <sub>modules/request-center/Pages/reports.php:1</sub><br>`CORE-015.05` <sub>lib/settings-effects.php:57</sub> | 16 |

### مجوزها و دسترسی‌ها

<details>
<summary>۱۰۱ مجوز — باز کنید</summary>

| گروه | مجوز | کد | آدرس‌های اصلی | کل |
|---|---|---|---|---|
| داشبورد و پایه | **مشاهدهٔ داشبورد** | `dash.view` | `PAGE-059.26` <sub>permissions.php:487</sub><br>`PAGE-059.03` <sub>permissions.php:28</sub><br>`TOOL-049.12` <sub>tools/test_access_coverage_offline.php:108</sub> | 10 |
| داشبورد و پایه | **جست‌وجوی سراسری** | `search.global` | `PAGE-034.76` <sub>index.php:1739</sub> | 3 |
| داشبورد و پایه | **اعلان‌های من** | `notif.view` | `PAGE-034.68` <sub>index.php:1508</sub> | 6 |
| داشبورد و پایه | **ویرایش پروفایل خودم** | `profile.edit` | `PAGE-059.03` <sub>permissions.php:28</sub><br>`PAGE-034.48` <sub>index.php:1321</sub><br>`PAGE-059.16` <sub>permissions.php:350</sub> | 3 |
| داشبورد و پایه | **استفاده از بات پاسخ‌گو** | `chatbot.use` | `PAGE-011.27` <sub>chatbot.php:700</sub><br>`PAGE-011.123` <sub>chatbot.php:3206</sub> | 5 |
| درخواست | **ثبت درخواست جدید** | `ticket.create` | `PAGE-042.03` <sub>modules/request-center/Actions/ticket-actions.php:30</sub><br>`PAGE-042.04` <sub>modules/request-center/Actions/ticket-actions.php:222</sub><br>`PAGE-047.02` <sub>modules/request-center/Pages/create.php:12</sub> | 16 |
| درخواست | **مشاهدهٔ درخواست‌های خودم** | `ticket.view_own` | `CORE-003.11` <sub>lib/auth-scope.php:145</sub><br>`CORE-003.19` <sub>lib/auth-scope.php:370</sub> | 8 |
| درخواست | **مشاهدهٔ درخواست‌های واحد/زیرمجموعه** | `ticket.view_unit` | `CORE-003.11` <sub>lib/auth-scope.php:145</sub><br>`CORE-003.19` <sub>lib/auth-scope.php:370</sub> | 9 |
| درخواست | **مشاهدهٔ همهٔ درخواست‌ها** | `ticket.view_all` | `CORE-003.11` <sub>lib/auth-scope.php:145</sub><br>`CORE-003.19` <sub>lib/auth-scope.php:370</sub> | 6 |
| درخواست | **پاسخ/مکالمه در درخواست** | `ticket.reply` | `PAGE-042.07` <sub>modules/request-center/Actions/ticket-actions.php:406</sub><br>`PAGE-048.02` <sub>modules/request-center/Pages/detail.php:11</sub><br>`PAGE-042.08` <sub>modules/request-center/Actions/ticket-actions.php:456</sub> | 7 |
| درخواست | **ویرایش** | `ticket.edit` | `PAGE-042.09` <sub>modules/request-center/Actions/ticket-actions.php:605</sub><br>`PAGE-048.02` <sub>modules/request-center/Pages/detail.php:11</sub><br>`PAGE-042.08` <sub>modules/request-center/Actions/ticket-actions.php:456</sub> | 13 |
| درخواست | **ارجاع درخواست به کارشناس** | `ticket.assign` | `CORE-003.12` <sub>lib/auth-scope.php:196</sub> | 7 |
| درخواست | **ویرایش** | `ticket.bulk` | `PAGE-039.04` <sub>modules/request-center/Actions/bulk-actions.php:44</sub> | 4 |
| درخواست | **امتیازدهی به درخواست** | `ticket.rate` | `PAGE-048.02` <sub>modules/request-center/Pages/detail.php:11</sub><br>`PAGE-041.06` <sub>modules/request-center/Actions/supervisor-actions.php:164</sub> | 6 |
| درخواست | **اتصال دارایی به درخواست** | `ticket.attach_asset` | `PAGE-039.03` <sub>modules/request-center/Actions/bulk-actions.php:24</sub><br>`PAGE-059.03` <sub>permissions.php:28</sub><br>`PAGE-059.16` <sub>permissions.php:350</sub> | 3 |
| درخواست | **صف کاری** | `queue.view` | `PAGE-050.02` <sub>modules/request-center/Pages/queue.php:6</sub> | 12 |
| درخواست | **پنل سوپروایزر** | `supervisor.panel` | `PAGE-054.02` <sub>modules/request-center/Pages/supervisor.php:9</sub><br>`PAGE-041.04` <sub>modules/request-center/Actions/supervisor-actions.php:40</sub><br>`PAGE-041.05` <sub>modules/request-center/Actions/supervisor-actions.php:114</sub> | 13 |
| درخواست | **ویرایش** | `supervisor.decide` | `PAGE-041.04` <sub>modules/request-center/Actions/supervisor-actions.php:40</sub> | 4 |
| درخواست | **بازگشایی/برگشت درخواست** | `supervisor.reopen` | `PAGE-041.05` <sub>modules/request-center/Actions/supervisor-actions.php:114</sub> | 3 |
| خدمات و دسته‌بندی | **مشاهدهٔ خدمات** | `services.view` | `PAGE-059.03` <sub>permissions.php:28</sub><br>`PAGE-030.03` <sub>global-search.php:31</sub><br>`PAGE-034.48` <sub>index.php:1321</sub> | 5 |
| خدمات و دسته‌بندی | **ویرایش** | `services.manage` | `PAGE-040.03` <sub>modules/request-center/Actions/service-actions.php:29</sub><br>`PAGE-040.07` <sub>modules/request-center/Actions/service-actions.php:155</sub><br>`PAGE-059.03` <sub>permissions.php:28</sub> | 8 |
| خدمات و دسته‌بندی | **مدیریت دسته‌بندی خدمات** | `service_cat.manage` | `PAGE-059.03` <sub>permissions.php:28</sub><br>`ACT-016.03` <sub>actions/user-structure-actions.php:29</sub><br>`PAGE-059.16` <sub>permissions.php:350</sub> | 3 |
| خدمات و دسته‌بندی | **مدیریت فیلدهای خدمت** | `service_field.manage` | `PAGE-040.04` <sub>modules/request-center/Actions/service-actions.php:69</sub> | 3 |
| دانش‌نامه | **مشاهدهٔ دانش‌نامه** | `knowledge.view` | `PAGE-059.03` <sub>permissions.php:28</sub><br>`PAGE-030.03` <sub>global-search.php:31</sub><br>`PAGE-034.48` <sub>index.php:1321</sub> | 5 |
| دانش‌نامه | **ویرایش** | `knowledge.manage` | `PAGE-040.09` <sub>modules/request-center/Actions/service-actions.php:206</sub> | 4 |
| کنترل CD/DVD | **مشاهدهٔ فرم کنترل CD/DVD** | `cddvd.view` | `PAGE-034.77` <sub>index.php:1747</sub><br>`PAGE-034.30` <sub>index.php:626</sub><br>`TEST-023.01` <sub>tests/smoke.php:1</sub> | 10 |
| کنترل CD/DVD | **ثبت خروج رسانه** | `cddvd.submit_out` | `PAGE-059.03` <sub>permissions.php:28</sub><br>`PAGE-009.17` <sub>cd-dvd.php:128</sub><br>`PAGE-059.15` <sub>permissions.php:344</sub> | 4 |
| کنترل CD/DVD | **ثبت ورود رسانه (بازرسی)** | `cddvd.submit_in` | `PAGE-059.03` <sub>permissions.php:28</sub><br>`TEST-142.05` <sub>tests/test_permissions_followup_static.py:30</sub><br>`PAGE-009.16` <sub>cd-dvd.php:120</sub> | 4 |
| کنترل CD/DVD | **ویرایش** | `cddvd.edit` | `PAGE-034.88` <sub>index.php:2465</sub><br>`PAGE-059.26` <sub>permissions.php:487</sub><br>`PAGE-059.03` <sub>permissions.php:28</sub> | 9 |
| کنترل CD/DVD | **مشاهدهٔ همهٔ ثبت‌ها (نه فقط واحد خود)** | `cddvd.view_all` | `PAGE-059.03` <sub>permissions.php:28</sub><br>`PAGE-009.11` <sub>cd-dvd.php:65</sub><br>`PAGE-059.16` <sub>permissions.php:350</sub> | 3 |
| کنترل CD/DVD | **تاریخچهٔ سریال** | `cddvd.history` | `PAGE-059.03` <sub>permissions.php:28</sub><br>`PAGE-030.03` <sub>global-search.php:31</sub><br>`PAGE-034.48` <sub>index.php:1321</sub> | 4 |
| کنترل CD/DVD | **خروجی Excel/CSV** | `cddvd.export` | `PAGE-034.43` <sub>index.php:1074</sub><br>`PAGE-059.03` <sub>permissions.php:28</sub><br>`PAGE-009.13` <sub>cd-dvd.php:87</sub> | 4 |
| کنترل تردد مراجعین | **مشاهدهٔ کنترل تردد** | `traffic.view` | `PAGE-066.63` <sub>traffic-control.php:981</sub><br>`PAGE-066.59` <sub>traffic-control.php:888</sub><br>`PAGE-067.23` <sub>traffic-import.php:853</sub> | 18 |
| کنترل تردد مراجعین | **ثبت، ویرایش و ورود گروهی سوابق** | `traffic.manage` | `PAGE-066.63` <sub>traffic-control.php:981</sub><br>`PAGE-066.54` <sub>traffic-control.php:786</sub><br>`PAGE-067.23` <sub>traffic-import.php:853</sub> | 10 |
| کنترل تردد مراجعین | **مقصدهای ملاقات** | `traffic.destinations` | `PAGE-066.63` <sub>traffic-control.php:981</sub><br>`PAGE-066.05` <sub>traffic-control.php:16</sub><br>`TEST-142.04` <sub>tests/test_permissions_followup_static.py:12</sub> | 6 |
| کنترل تردد مراجعین | **ویرایش** | `traffic.destinations_edit` | `PAGE-066.06` <sub>traffic-control.php:21</sub><br>`TEST-142.04` <sub>tests/test_permissions_followup_static.py:12</sub> | 4 |
| دارایی و شناسنامه فنی | **فهرست شناسنامه‌ها** | `assets.view` | `PAGE-034.85` <sub>index.php:2195</sub> | 9 |
| دارایی و شناسنامه فنی | **فقط دارایی‌های واحد خودم** | `assets.own_unit` | `CORE-003.22` <sub>lib/auth-scope.php:405</sub> | 4 |
| دارایی و شناسنامه فنی | **مشاهدهٔ شناسنامهٔ دارایی** | `asset.view` | `PAGE-059.03` <sub>permissions.php:28</sub><br>`PAGE-034.48` <sub>index.php:1321</sub><br>`PAGE-059.08` <sub>permissions.php:246</sub> | 4 |
| دارایی و شناسنامه فنی | **ویرایش** | `asset.edit` | `ACT-003.12` <sub>actions/asset-actions.php:361</sub> | 4 |
| دارایی و شناسنامه فنی | **استخراج سخت‌افزار (همین سرور)** | `asset.extract` | `PAGE-034.85` <sub>index.php:2195</sub><br>`PAGE-034.86` <sub>index.php:2240</sub><br>`ACT-003.03` <sub>actions/asset-actions.php:33</sub> | 6 |
| دارایی و شناسنامه فنی | **ویرایش** | `asset.remote_extract` | `ACT-003.10` <sub>actions/asset-actions.php:285</sub><br>`ACT-003.06` <sub>actions/asset-actions.php:131</sub><br>`ACT-003.07` <sub>actions/asset-actions.php:148</sub> | 10 |
| دارایی و شناسنامه فنی | **تاریخچهٔ دارایی** | `asset.history` | `PAGE-034.83` <sub>index.php:2099</sub> | 4 |
| دارایی و شناسنامه فنی | **پینگ سیستم‌های انتخابی** | `asset.ping` | `ACT-003.08` <sub>actions/asset-actions.php:190</sub><br>`PAGE-034.85` <sub>index.php:2195</sub> | 4 |
| دارایی و شناسنامه فنی | **فهرست موجودی** | `inventory.view` | `ACT-003.12` <sub>actions/asset-actions.php:361</sub><br>`PAGE-034.81` <sub>index.php:1966</sub> | 6 |
| دارایی و شناسنامه فنی | **تشخیص موجودی** | `inventory.diagnostics` | `PAGE-034.82` <sub>index.php:2067</sub> | 6 |
| دارایی و شناسنامه فنی | **ورود کامپیوترهای دامنه** | `domain.import` | `ACT-003.05` <sub>actions/asset-actions.php:123</sub> | 6 |
| دارایی و شناسنامه فنی | **اسکن دامنه** | `domain.scan` | `PAGE-034.84` <sub>index.php:2117</sub> | 10 |
| گزارش و تحلیل | **گزارش‌ها** | `reports.view` | `PAGE-051.02` <sub>modules/request-center/Pages/reports.php:6</sub> | 7 |
| گزارش و تحلیل | **چارت‌ها** | `analytics.view` | `PAGE-045.02` <sub>modules/request-center/Pages/analytics.php:6</sub> | 6 |
| گزارش و تحلیل | **ممیزی** | `audit.view` | `PAGE-034.70` <sub>index.php:1534</sub> | 7 |
| گزارش و تحلیل | **گزارش خطا** | `logs.view` | `PAGE-034.89` <sub>index.php:2935</sub><br>`PAGE-034.73` <sub>index.php:1551</sub> | 12 |
| گزارش و تحلیل | **سلامت سامانه و خطاهای فنی** | `system.health` | `PAGE-064.28` <sub>system-health.php:947</sub><br>`PAGE-064.30` <sub>system-health.php:1118</sub> | 11 |
| گزارش و تحلیل | **ویرایش وضعیت و نگهداری لاگ** | `system.health_edit` | `PAGE-064.30` <sub>system-health.php:1118</sub><br>`PAGE-064.25` <sub>system-health.php:787</sub> | 5 |
| گزارش و تحلیل | **فعالیت کاربران** | `logs.activity` | `PAGE-034.73` <sub>index.php:1551</sub> | 7 |
| گزارش و تحلیل | **خروجی فعالیت کاربران** | `logs.activity_export` | `PAGE-034.42` <sub>index.php:928</sub><br>`PAGE-034.74` <sub>index.php:1557</sub><br>`PAGE-059.03` <sub>permissions.php:28</sub> | 5 |
| سامانه مدیریت غذا | **داشبورد** | `food.dashboard` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.07` <sub>food-ticket.php:70</sub> | 9 |
| سامانه مدیریت غذا | **پایش لحظه‌ای** | `food.monitor` | `PAGE-034.52` <sub>index.php:1377</sub><br>`PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub> | 11 |
| سامانه مدیریت غذا | **کارکنان** | `food.employees` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.07` <sub>food-ticket.php:70</sub> | 8 |
| سامانه مدیریت غذا | **ویرایش** | `food.employees_edit` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.08` <sub>food-ticket.php:78</sub> | 5 |
| سامانه مدیریت غذا | **برنامه غذایی** | `food.menu` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-015.100` <sub>food-order.php:3422</sub><br>`PAGE-015.96` <sub>food-order.php:3133</sub> | 16 |
| سامانه مدیریت غذا | **ویرایش** | `food.menu_edit` | `PAGE-017.18` <sub>food-reconciliation.php:640</sub><br>`PAGE-015.67` <sub>food-order.php:2130</sub><br>`PAGE-029.132` <sub>food-ticket.php:5499</sub> | 24 |
| سامانه مدیریت غذا | **گروه‌های غذا** | `food.groups` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.07` <sub>food-ticket.php:70</sub> | 12 |
| سامانه مدیریت غذا | **ویرایش** | `food.groups_edit` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.08` <sub>food-ticket.php:78</sub><br>`PAGE-021.14` <sub>food-ticket-groups.php:511</sub> | 10 |
| سامانه مدیریت غذا | **گزارش‌ها** | `food.reports` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-015.100` <sub>food-order.php:3422</sub><br>`PAGE-017.21` <sub>food-reconciliation.php:835</sub> | 23 |
| تنظیمات سیستم مدیریت غذا | **اتصال پایگاه‌ها و تردد** | `food.db` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.07` <sub>food-ticket.php:70</sub> | 10 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.db_edit` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.08` <sub>food-ticket.php:78</sub> | 9 |
| تنظیمات سیستم مدیریت غذا | **چاپگر سیستم** | `food.printer` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.07` <sub>food-ticket.php:70</sub> | 10 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.printer_edit` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.08` <sub>food-ticket.php:78</sub> | 6 |
| تنظیمات سیستم مدیریت غذا | **کارت مهمان** | `food.guest` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.07` <sub>food-ticket.php:70</sub> | 10 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.guest_edit` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.08` <sub>food-ticket.php:78</sub> | 7 |
| تنظیمات سیستم مدیریت غذا | **طراحی فیش** | `food.design` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.07` <sub>food-ticket.php:70</sub> | 7 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.design_edit` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.08` <sub>food-ticket.php:78</sub> | 6 |
| تنظیمات سیستم مدیریت غذا | **سلامت سیستم** | `food.health` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.07` <sub>food-ticket.php:70</sub> | 6 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.health_edit` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.08` <sub>food-ticket.php:78</sub> | 5 |
| تنظیمات سیستم مدیریت غذا | **ثبت دستی فیش/تردد** | `food.manual_attendance` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-029.07` <sub>food-ticket.php:70</sub> | 7 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.manual_attendance_edit` | `PAGE-021.61` <sub>food-ticket-groups.php:2320</sub><br>`PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub> | 8 |
| تنظیمات سیستم مدیریت غذا | **مدیریت مهلت اضطراری سفارش غذا** | `food.emergency_window` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-015.100` <sub>food-order.php:3422</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub> | 8 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.emergency_window_edit` | `PAGE-029.132` <sub>food-ticket.php:5499</sub><br>`PAGE-029.10` <sub>food-ticket.php:125</sub><br>`PAGE-015.46` <sub>food-order.php:1284</sub> | 9 |
| سفارش غذای کارکنان | **ثبت و مشاهدهٔ سفارش خود** | `foodorder.self` | `PAGE-034.60` <sub>index.php:1435</sub> | 11 |
| سازمان | **مشاهدهٔ چارت سازمانی** | `org.view` | `PAGE-034.87` <sub>index.php:2274</sub> | 7 |
| سازمان | **ویرایش** | `org.manage` | `ACT-010.13` <sub>actions/org-actions.php:382</sub><br>`ACT-010.18` <sub>actions/org-actions.php:617</sub><br>`ACT-010.14` <sub>actions/org-actions.php:409</sub> | 17 |
| تنظیمات و سیستم | **تنظیمات عمومی و افکت نمایشی** | `settings.general` | `PAGE-034.88` <sub>index.php:2465</sub><br>`PAGE-034.54` <sub>index.php:1393</sub><br>`CORE-015.17` <sub>lib/settings-effects.php:291</sub> | 11 |
| تنظیمات و سیستم | **ویرایش تنظیمات عمومی و افکت نمایشی** | `settings.general_edit` | `ACT-013.08` <sub>actions/settings-actions.php:165</sub><br>`PAGE-034.88` <sub>index.php:2465</sub><br>`ACT-013.07` <sub>actions/settings-actions.php:91</sub> | 6 |
| تنظیمات و سیستم | **اتصال دامین و اسکن دامنه** | `settings.domain` | `PAGE-034.88` <sub>index.php:2465</sub><br>`PAGE-034.54` <sub>index.php:1393</sub> | 10 |
| تنظیمات و سیستم | **ویرایش** | `settings.domain_edit` | `ACT-013.08` <sub>actions/settings-actions.php:165</sub><br>`PAGE-034.88` <sub>index.php:2465</sub><br>`ACT-013.06` <sub>actions/settings-actions.php:77</sub> | 5 |
| تنظیمات و سیستم | **مدیریت کاربران و دسترسی‌ها** | `settings.users` | `PAGE-034.88` <sub>index.php:2465</sub><br>`PAGE-034.54` <sub>index.php:1393</sub> | 7 |
| تنظیمات و سیستم | **ویرایش** | `settings.users_edit` | `PAGE-034.88` <sub>index.php:2465</sub><br>`ACT-016.08` <sub>actions/user-structure-actions.php:358</sub><br>`ACT-013.05` <sub>actions/settings-actions.php:69</sub> | 6 |
| تنظیمات و سیستم | **نقش‌های کلیدی** | `settings.key_roles` | `PAGE-034.88` <sub>index.php:2465</sub><br>`PAGE-034.54` <sub>index.php:1393</sub> | 5 |
| تنظیمات و سیستم | **ویرایش** | `settings.key_roles_edit` | `PAGE-034.88` <sub>index.php:2465</sub> | 4 |
| تنظیمات و سیستم | **پشتیبان‌گیری/بازگردانی** | `backup.view` | `PAGE-034.75` <sub>index.php:1700</sub> | 5 |
| تنظیمات و سیستم | **ویرایش** | `backup.manage` | `ACT-004.02` <sub>actions/backup-actions.php:18</sub><br>`ACT-004.03` <sub>actions/backup-actions.php:25</sub><br>`PAGE-034.75` <sub>index.php:1700</sub> | 7 |
| تنظیمات و سیستم | **مشاهدهٔ به‌روزرسانی سامانه** | `update.view` | `PAGE-034.75` <sub>index.php:1700</sub><br>`PAGE-059.03` <sub>permissions.php:28</sub> | 2 |
| تنظیمات و سیستم | **اجرای به‌روزرسانی سامانه** | `update.apply` | `ACT-004.04` <sub>actions/backup-actions.php:41</sub> | 3 |
| تنظیمات و سیستم | **مدیریت پرسش‌ها و پاسخ‌های بات** | `chatbot.manage` | `PAGE-034.78` <sub>index.php:1839</sub><br>`PAGE-034.48` <sub>index.php:1321</sub> | 4 |
| تنظیمات و سیستم | **تنظیمات OLA/SLA** | `ola.view` | `PAGE-057.07` <sub>ola.php:32</sub> | 5 |
| تنظیمات و سیستم | **ویرایش** | `ola.manage` | `PAGE-057.07` <sub>ola.php:32</sub> | 5 |
| تنظیمات و سیستم | **تغییر و مشکل** | `governance.view` | `PAGE-031.24` <sub>governance.php:174</sub> | 6 |
| تنظیمات و سیستم | **ویرایش** | `governance.manage` | `PAGE-031.24` <sub>governance.php:174</sub> | 8 |
| تنظیمات و سیستم | **مدیریت تعطیلات** | `holidays.view` | `PAGE-034.79` <sub>index.php:1942</sub><br>`TEST-082.33` <sub>tests/test_food_worker_window_static.py:306</sub> | 6 |
| تنظیمات و سیستم | **ویرایش** | `holidays.manage` | `ACT-008.07` <sub>actions/holiday-actions.php:102</sub> | 13 |

</details>

### جدول‌های پایگاه داده

<details>
<summary>۸۴ جدول — باز کنید</summary>

| جدول | آدرس‌های اصلی | کل |
|---|---|---|
| `activity_logs` | `DB-002.28` <sub>install-empty-db.sql:396</sub><br>`DB-005.19` <sub>migrations/003-sync-schema-151.sql:298</sub><br>`DB-040.25` <sub>schema.sql:375</sub> | 61 |
| `api_rate_limits` | `DB-002.67` <sub>install-empty-db.sql:1134</sub><br>`DB-005.58` <sub>migrations/003-sync-schema-151.sql:949</sub><br>`DB-040.67` <sub>schema.sql:1147</sub> | 8 |
| `asset_dedupe_backup_016` | `DB-018.20` <sub>migrations/016-auto-asset-finish.sql:53</sub><br>`DB-018.22` <sub>migrations/016-auto-asset-finish.sql:57</sub><br>`DB-018.19` <sub>migrations/016-auto-asset-finish.sql:52</sub> | 4 |
| `asset_graphics_adapters` | `DB-002.40` <sub>install-empty-db.sql:615</sub><br>`DB-005.30` <sub>migrations/003-sync-schema-151.sql:475</sub><br>`DB-040.37` <sub>schema.sql:594</sub> | 13 |
| `asset_history_events` | `DB-002.43` <sub>install-empty-db.sql:657</sub><br>`DB-005.33` <sub>migrations/003-sync-schema-151.sql:514</sub><br>`DB-040.40` <sub>schema.sql:636</sub> | 16 |
| `asset_inventory_history` | `DB-002.42` <sub>install-empty-db.sql:646</sub><br>`DB-005.32` <sub>migrations/003-sync-schema-151.sql:504</sub><br>`DB-040.39` <sub>schema.sql:625</sub> | 12 |
| `asset_memory_modules` | `DB-002.38` <sub>install-empty-db.sql:584</sub><br>`DB-005.28` <sub>migrations/003-sync-schema-151.sql:446</sub><br>`DB-040.35` <sub>schema.sql:563</sub> | 16 |
| `asset_peripherals` | `DB-002.41` <sub>install-empty-db.sql:628</sub><br>`DB-005.31` <sub>migrations/003-sync-schema-151.sql:487</sub><br>`DB-040.38` <sub>schema.sql:607</sub> | 19 |
| `asset_profiles` | `DB-002.45` <sub>install-empty-db.sql:688</sub><br>`DB-005.35` <sub>migrations/003-sync-schema-151.sql:543</sub><br>`DB-040.42` <sub>schema.sql:667</sub> | 96 |
| `asset_relations` | `DB-002.44` <sub>install-empty-db.sql:675</sub><br>`DB-005.34` <sub>migrations/003-sync-schema-151.sql:531</sub><br>`DB-040.41` <sub>schema.sql:654</sub> | 12 |
| `asset_storage_devices` | `DB-002.39` <sub>install-empty-db.sql:599</sub><br>`DB-005.29` <sub>migrations/003-sync-schema-151.sql:460</sub><br>`DB-040.36` <sub>schema.sql:578</sub> | 17 |
| `assets` | `ACT-003.08` <sub>actions/asset-actions.php:190</sub><br>`PAGE-034.85` <sub>index.php:2195</sub><br>`DB-002.36` <sub>install-empty-db.sql:526</sub> | 177 |
| `categories` | `DB-002.21` <sub>install-empty-db.sql:244</sub><br>`DB-005.12` <sub>migrations/003-sync-schema-151.sql:153</sub><br>`DB-040.18` <sub>schema.sql:223</sub> | 31 |
| `cd_dvd_records` | `DB-002.16` <sub>install-empty-db.sql:144</sub><br>`DB-005.09` <sub>migrations/003-sync-schema-151.sql:87</sub><br>`DB-040.13` <sub>schema.sql:123</sub> | 40 |
| `cd_dvd_types` | `DB-002.15` <sub>install-empty-db.sql:136</sub><br>`DB-040.12` <sub>schema.sql:115</sub><br>`DB-075.02` <sub>upgrade-1.8-cd-dvd.sql:2</sub> | 8 |
| `change_approvals` | `DB-002.50` <sub>install-empty-db.sql:829</sub><br>`DB-005.39` <sub>migrations/003-sync-schema-151.sql:678</sub><br>`DB-040.47` <sub>schema.sql:808</sub> | 15 |
| `change_records` | `DB-002.49` <sub>install-empty-db.sql:800</sub><br>`DB-005.38` <sub>migrations/003-sync-schema-151.sql:650</sub><br>`DB-040.46` <sub>schema.sql:779</sub> | 31 |
| `change_ticket_links` | `DB-002.51` <sub>install-empty-db.sql:841</sub><br>`DB-005.40` <sub>migrations/003-sync-schema-151.sql:689</sub><br>`DB-040.48` <sub>schema.sql:820</sub> | 15 |
| `chatbot_faq` | `DB-002.107` <sub>install-empty-db.sql:1342</sub><br>`DB-005.54` <sub>migrations/003-sync-schema-151.sql:914</sub><br>`DB-040.63` <sub>schema.sql:1107</sub> | 13 |
| `chatbot_logs` | `DB-002.108` <sub>install-empty-db.sql:1354</sub><br>`DB-005.55` <sub>migrations/003-sync-schema-151.sql:925</sub><br>`DB-040.64` <sub>schema.sql:1119</sub> | 14 |
| `departments` | `DB-002.05` <sub>install-empty-db.sql:27</sub><br>`DB-005.03` <sub>migrations/003-sync-schema-151.sql:9</sub><br>`DB-040.02` <sub>schema.sql:6</sub> | 65 |
| `domain_scan_queue` | `DB-002.69` <sub>install-empty-db.sql:1156</sub><br>`DB-005.60` <sub>migrations/003-sync-schema-151.sql:968</sub><br>`DB-040.69` <sub>schema.sql:1169</sub> | 19 |
| `domain_scan_runs` | `DB-002.68` <sub>install-empty-db.sql:1143</sub><br>`DB-005.59` <sub>migrations/003-sync-schema-151.sql:956</sub><br>`DB-040.68` <sub>schema.sql:1156</sub> | 22 |
| `food_calendar` | `DB-002.100` <sub>install-empty-db.sql:1254</sub><br>`DB-005.65` <sub>migrations/003-sync-schema-151.sql:1029</sub><br>`DB-040.95` <sub>schema.sql:1265</sub> | 39 |
| `food_calendar_items` | `DB-002.101` <sub>install-empty-db.sql:1269</sub><br>`DB-005.66` <sub>migrations/003-sync-schema-151.sql:1042</sub><br>`DB-040.96` <sub>schema.sql:1280</sub> | 46 |
| `food_catalog` | `DB-002.99` <sub>install-empty-db.sql:1242</sub><br>`DB-005.63` <sub>migrations/003-sync-schema-151.sql:1000</sub><br>`DB-040.93` <sub>schema.sql:1233</sub> | 50 |
| `food_guest_requests` | `DB-002.110` <sub>install-empty-db.sql:1376</sub><br>`DB-005.64` <sub>migrations/003-sync-schema-151.sql:1011</sub><br>`DB-040.94` <sub>schema.sql:1245</sub> | 39 |
| `food_meal_reconciliations` | `DB-002.111` <sub>install-empty-db.sql:1400</sub><br>`DB-025.02` <sub>migrations/022-food-meal-reconciliation-invoices.sql:4</sub><br>`DB-040.100` <sub>schema.sql:1351</sub> | 6 |
| `food_monthly_invoices` | `DB-002.112` <sub>install-empty-db.sql:1427</sub><br>`DB-025.03` <sub>migrations/022-food-meal-reconciliation-invoices.sql:31</sub><br>`DB-040.101` <sub>schema.sql:1378</sub> | 7 |
| `food_order_cancel_requests` | `DB-002.104` <sub>install-empty-db.sql:1318</sub><br>`DB-040.99` <sub>schema.sql:1329</sub> | 10 |
| `food_order_logs` | `DB-002.103` <sub>install-empty-db.sql:1300</sub><br>`DB-005.68` <sub>migrations/003-sync-schema-151.sql:1070</sub><br>`DB-040.98` <sub>schema.sql:1311</sub> | 28 |
| `food_orders` | `DB-002.102` <sub>install-empty-db.sql:1284</sub><br>`DB-005.67` <sub>migrations/003-sync-schema-151.sql:1056</sub><br>`DB-040.97` <sub>schema.sql:1295</sub> | 76 |
| `food_ticket_card_map` | `DB-002.89` <sub>install-empty-db.sql:1191</sub><br>`DB-005.61` <sub>migrations/003-sync-schema-151.sql:980</sub><br>`DB-040.89` <sub>schema.sql:1204</sub> | 14 |
| `food_ticket_config` | `DB-002.54` <sub>install-empty-db.sql:887</sub><br>`DB-005.43` <sub>migrations/003-sync-schema-151.sql:732</sub><br>`DB-040.51` <sub>schema.sql:866</sub> | 46 |
| `food_ticket_daily_absence` | `DB-002.61` <sub>install-empty-db.sql:1045</sub><br>`DB-005.50` <sub>migrations/003-sync-schema-151.sql:863</sub><br>`DB-014.02` <sub>migrations/012-manual-attendance-clears-absences.sql:4</sub> | 33 |
| `food_ticket_day_visits` | `DB-002.62` <sub>install-empty-db.sql:1061</sub><br>`DB-040.59` <sub>schema.sql:1040</sub> | 16 |
| `food_ticket_events` | `DB-002.55` <sub>install-empty-db.sql:918</sub><br>`DB-005.44` <sub>migrations/003-sync-schema-151.sql:750</sub><br>`DB-040.52` <sub>schema.sql:897</sub> | 139 |
| `food_ticket_group_members` | `DB-002.59` <sub>install-empty-db.sql:1006</sub><br>`DB-005.48` <sub>migrations/003-sync-schema-151.sql:829</sub><br>`DB-040.56` <sub>schema.sql:985</sub> | 21 |
| `food_ticket_group_runs` | `DB-002.60` <sub>install-empty-db.sql:1018</sub><br>`DB-005.49` <sub>migrations/003-sync-schema-151.sql:839</sub><br>`DB-040.57` <sub>schema.sql:997</sub> | 38 |
| `food_ticket_group_uid_history` | `DB-002.63` <sub>install-empty-db.sql:1074</sub><br>`DB-005.51` <sub>migrations/003-sync-schema-151.sql:877</sub><br>`DB-040.60` <sub>schema.sql:1053</sub> | 16 |
| `food_ticket_groups` | `DB-002.58` <sub>install-empty-db.sql:987</sub><br>`DB-005.47` <sub>migrations/003-sync-schema-151.sql:813</sub><br>`DB-040.55` <sub>schema.sql:966</sub> | 37 |
| `food_ticket_guest_cards` | `DB-002.57` <sub>install-empty-db.sql:973</sub><br>`DB-005.46` <sub>migrations/003-sync-schema-151.sql:800</sub><br>`DB-040.54` <sub>schema.sql:952</sub> | 24 |
| `food_ticket_print_log` | `DB-002.64` <sub>install-empty-db.sql:1085</sub><br>`DB-005.52` <sub>migrations/003-sync-schema-151.sql:887</sub><br>`DB-040.61` <sub>schema.sql:1064</sub> | 19 |
| `food_ticket_templates` | `DB-002.90` <sub>install-empty-db.sql:1201</sub><br>`DB-005.62` <sub>migrations/003-sync-schema-151.sql:989</sub><br>`DB-040.90` <sub>schema.sql:1214</sub> | 21 |
| `food_ticket_worker_status` | `DB-002.56` <sub>install-empty-db.sql:957</sub><br>`DB-005.45` <sub>migrations/003-sync-schema-151.sql:787</sub><br>`DB-040.53` <sub>schema.sql:936</sub> | 17 |
| `handling_units` | `DB-002.13` <sub>install-empty-db.sql:123</sub><br>`DB-005.07` <sub>migrations/003-sync-schema-151.sql:72</sub><br>`DB-040.10` <sub>schema.sql:102</sub> | 40 |
| `holidays` | `ACT-008.07` <sub>actions/holiday-actions.php:102</sub><br>`PAGE-034.79` <sub>index.php:1942</sub><br>`DB-002.35` <sub>install-empty-db.sql:516</sub> | 34 |
| `knowledge_articles` | `DB-002.34` <sub>install-empty-db.sql:502</sub><br>`DB-005.25` <sub>migrations/003-sync-schema-151.sql:373</sub><br>`DB-040.31` <sub>schema.sql:481</sub> | 17 |
| `log_issue_events` | `DB-029.05` <sub>migrations/026-logging-legacy-result-unknown.sql:38</sub><br>`DB-002.119` <sub>install-empty-db.sql:1543</sub><br>`DB-040.108` <sub>schema.sql:1494</sub> | 15 |
| `log_issue_history` | `DB-002.118` <sub>install-empty-db.sql:1527</sub><br>`DB-029.04` <sub>migrations/026-logging-legacy-result-unknown.sql:20</sub><br>`DB-040.107` <sub>schema.sql:1478</sub> | 12 |
| `log_issue_notifications` | `DB-002.120` <sub>install-empty-db.sql:1550</sub><br>`DB-029.06` <sub>migrations/026-logging-legacy-result-unknown.sql:56</sub><br>`DB-040.109` <sub>schema.sql:1501</sub> | 14 |
| `log_issues` | `DB-002.116` <sub>install-empty-db.sql:1491</sub><br>`DB-028.02` <sub>migrations/025-logging-issues-alerts.sql:4</sub><br>`DB-040.105` <sub>schema.sql:1442</sub> | 28 |
| `logging_health_alerts` | `DB-002.117` <sub>install-empty-db.sql:1517</sub><br>`DB-028.03` <sub>migrations/025-logging-issues-alerts.sql:29</sub><br>`DB-040.106` <sub>schema.sql:1468</sub> | 13 |
| `login_attempts` | `DB-002.31` <sub>install-empty-db.sql:460</sub><br>`DB-005.22` <sub>migrations/003-sync-schema-151.sql:343</sub><br>`DB-040.28` <sub>schema.sql:439</sub> | 14 |
| `notifications` | `PAGE-034.69` <sub>index.php:1516</sub><br>`ACT-009.03` <sub>actions/notification-actions.php:23</sub><br>`PAGE-034.68` <sub>index.php:1508</sub> | 41 |
| `ola_policies` | `DB-002.46` <sub>install-empty-db.sql:770</sub><br>`DB-005.36` <sub>migrations/003-sync-schema-151.sql:624</sub><br>`DB-040.43` <sub>schema.sql:749</sub> | 24 |
| `org_companies` | `DB-002.06` <sub>install-empty-db.sql:37</sub><br>`DB-021.02` <sub>migrations/018-org-company-separation.sql:3</sub><br>`DB-040.03` <sub>schema.sql:16</sub> | 11 |
| `org_unit_managers` | `DB-002.10` <sub>install-empty-db.sql:77</sub><br>`DB-005.05` <sub>migrations/003-sync-schema-151.sql:32</sub><br>`DB-040.07` <sub>schema.sql:56</sub> | 36 |
| `org_units` | `DB-002.07` <sub>install-empty-db.sql:51</sub><br>`DB-005.04` <sub>migrations/003-sync-schema-151.sql:18</sub><br>`DB-040.04` <sub>schema.sql:30</sub> | 89 |
| `problem_records` | `DB-002.52` <sub>install-empty-db.sql:852</sub><br>`DB-005.41` <sub>migrations/003-sync-schema-151.sql:699</sub><br>`DB-040.49` <sub>schema.sql:831</sub> | 26 |
| `problem_ticket_links` | `DB-002.53` <sub>install-empty-db.sql:876</sub><br>`DB-005.42` <sub>migrations/003-sync-schema-151.sql:722</sub><br>`DB-040.50` <sub>schema.sql:855</sub> | 14 |
| `role_permissions` | `DB-002.29` <sub>install-empty-db.sql:435</sub><br>`DB-005.20` <sub>migrations/003-sync-schema-151.sql:323</sub><br>`DB-040.26` <sub>schema.sql:414</sub> | 21 |
| `schema_updates` | `DB-002.121` <sub>install-empty-db.sql:1569</sub><br>`DB-040.110` <sub>schema.sql:1520</sub><br>`CORE-007.04` <sub>lib/logging-audit.php:61</sub> | 21 |
| `service_catalog` | `DB-002.22` <sub>install-empty-db.sql:253</sub><br>`DB-005.13` <sub>migrations/003-sync-schema-151.sql:161</sub><br>`DB-040.19` <sub>schema.sql:232</sub> | 57 |
| `service_catalog_fields` | `DB-002.23` <sub>install-empty-db.sql:279</sub><br>`DB-005.14` <sub>migrations/003-sync-schema-151.sql:186</sub><br>`DB-040.20` <sub>schema.sql:258</sub> | 16 |
| `settings` | `PAGE-034.88` <sub>index.php:2465</sub><br>`PAGE-034.47` <sub>index.php:1310</sub><br>`PAGE-034.54` <sub>index.php:1393</sub> | 61 |
| `system_incidents` | `DB-002.109` <sub>install-empty-db.sql:1367</sub><br>`DB-005.56` <sub>migrations/003-sync-schema-151.sql:937</sub><br>`DB-040.65` <sub>schema.sql:1132</sub> | 9 |
| `system_logs` | `DB-002.65` <sub>install-empty-db.sql:1101</sub><br>`DB-005.53` <sub>migrations/003-sync-schema-151.sql:902</sub><br>`DB-040.62` <sub>schema.sql:1080</sub> | 65 |
| `ticket_attachments` | `DB-002.27` <sub>install-empty-db.sql:385</sub><br>`DB-005.18` <sub>migrations/003-sync-schema-151.sql:288</sub><br>`DB-040.24` <sub>schema.sql:364</sub> | 16 |
| `ticket_events` | `DB-002.33` <sub>install-empty-db.sql:478</sub><br>`DB-005.24` <sub>migrations/003-sync-schema-151.sql:359</sub><br>`DB-040.30` <sub>schema.sql:457</sub> | 40 |
| `ticket_messages` | `DB-002.25` <sub>install-empty-db.sql:359</sub><br>`DB-005.16` <sub>migrations/003-sync-schema-151.sql:264</sub><br>`DB-040.22` <sub>schema.sql:338</sub> | 29 |
| `ticket_number_seq` | `DB-002.66` <sub>install-empty-db.sql:1128</sub><br>`DB-005.57` <sub>migrations/003-sync-schema-151.sql:945</sub><br>`DB-040.66` <sub>schema.sql:1141</sub> | 19 |
| `ticket_ola_alerts` | `DB-002.47` <sub>install-empty-db.sql:788</sub><br>`DB-005.37` <sub>migrations/003-sync-schema-151.sql:641</sub><br>`DB-040.44` <sub>schema.sql:767</sub> | 12 |
| `ticket_ratings` | `DB-002.26` <sub>install-empty-db.sql:371</sub><br>`DB-005.17` <sub>migrations/003-sync-schema-151.sql:275</sub><br>`DB-040.23` <sub>schema.sql:350</sub> | 18 |
| `ticket_sla_alerts` | `DB-002.32` <sub>install-empty-db.sql:468</sub><br>`DB-005.23` <sub>migrations/003-sync-schema-151.sql:350</sub><br>`DB-040.29` <sub>schema.sql:447</sub> | 13 |
| `tickets` | `DB-002.24` <sub>install-empty-db.sql:292</sub><br>`DB-005.15` <sub>migrations/003-sync-schema-151.sql:198</sub><br>`DB-040.21` <sub>schema.sql:271</sub> | 184 |
| `tmp_asset_dedupe_map` | `DB-018.17` <sub>migrations/016-auto-asset-finish.sql:39</sub><br>`DB-018.18` <sub>migrations/016-auto-asset-finish.sql:45</sub><br>`DB-018.16` <sub>migrations/016-auto-asset-finish.sql:38</sub> | 8 |
| `traffic_destinations` | `DB-002.17` <sub>install-empty-db.sql:177</sub><br>`DB-005.10` <sub>migrations/003-sync-schema-151.sql:119</sub><br>`DB-040.14` <sub>schema.sql:156</sub> | 31 |
| `traffic_visit_history` | `DB-002.19` <sub>install-empty-db.sql:217</sub><br>`DB-009.02` <sub>migrations/007-traffic-visit-history.sql:2</sub><br>`DB-040.16` <sub>schema.sql:196</sub> | 10 |
| `traffic_visits` | `DB-002.18` <sub>install-empty-db.sql:188</sub><br>`DB-005.11` <sub>migrations/003-sync-schema-151.sql:127</sub><br>`DB-040.15` <sub>schema.sql:167</sub> | 59 |
| `user_access_group_members` | `DB-002.114` <sub>install-empty-db.sql:1463</sub><br>`DB-026.03` <sub>migrations/023-user-access-groups.sql:17</sub><br>`DB-040.103` <sub>schema.sql:1414</sub> | 12 |
| `user_access_group_permissions` | `DB-002.115` <sub>install-empty-db.sql:1475</sub><br>`DB-026.04` <sub>migrations/023-user-access-groups.sql:29</sub><br>`DB-040.104` <sub>schema.sql:1426</sub> | 12 |
| `user_access_groups` | `DB-002.113` <sub>install-empty-db.sql:1449</sub><br>`DB-026.02` <sub>migrations/023-user-access-groups.sql:3</sub><br>`DB-040.102` <sub>schema.sql:1400</sub> | 10 |
| `users` | `ACT-010.14` <sub>actions/org-actions.php:409</sub><br>`DB-002.11` <sub>install-empty-db.sql:85</sub><br>`DB-005.06` <sub>migrations/003-sync-schema-151.sql:39</sub> | 282 |

</details>

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
| `TEST` | TEST | 231 | 1,662 | 22,298 |
| `TOOL` | TOOL | 87 | 694 | 17,403 |
| `DB` | DB | 82 | 1,373 | 7,929 |
| `PAGE` | صفحات (ریشه) | 69 | 1,412 | 46,629 |
| `ASSET` | دارایی‌های ایستا (assets/) | 30 | 767 | 13,946 |
| `WEB` | پنل وب غذا (food-ticket-web/) | 28 | 436 | 9,060 |
| `ACT` | کنش‌ها (actions/) | 16 | 94 | 2,803 |
| `CORE` | موتور مشترک (lib/) | 16 | 311 | 7,385 |
| `CRON` | زمان‌بندها (cron/) | 14 | 35 | 1,459 |
| `API` | رابط برنامه (api/) | 1 | 7 | 206 |

## نشانه‌های ریسک

امتیاز ریسک فقط **ترتیب بازبینی** را تعیین می‌کند؛ قضاوت نهایی نیست. یک بلوک پرامتیاز می‌تواند کاملاً سالم باشد و یک بلوک کم‌امتیاز باگ حیاتی داشته باشد.

| نشانه | معنی | بلوک |
|---|---|---|
| `write_no_tx` | نوشتن بدون تراکنش | 836 |
| `error_suppression` | سرکوب @ | 287 |
| `dangerous_call` | فراخوانی خطرناک | 211 |
| `sql_concat` | الحاق در SQL | 186 |
| `raw_input` | ورودی خام | 181 |
| `silent_catch` | catch بی‌صدا | 131 |
| `long` | >۱۲۰ خط | 127 |
| `unescaped_echo` | چاپ بدون فرار | 36 |
| `very_long` | >۳۰۰ خط | 8 |

## ترتیب بازبینی — ۴۰ آدرس پرخطر

| # | آدرس | مسیر:خط | لنگر | ریسک | نشانه‌ها | وضعیت |
|---|---|---|---|---|---|---|
| 1 | `TOOL-018.01` | `tools/diag_connection.php:1` | بلوک رویه‌ای 1-258 | 20 | catch بی‌صدا، الحاق در SQL، چاپ بدون فرار، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 2 | `TEST-198.13` | `tests/test_system_update_migration_failure_gates.php:147` | بلوک رویه‌ای 147-232 | 17 | catch بی‌صدا، الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 3 | `TOOL-060.01` | `tools/test_food_group_representative.php:1` | بلوک رویه‌ای 1-517 | 16 | catch بی‌صدا، الحاق در SQL، چاپ بدون فرار، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 4 | `PAGE-025.29` | `food-ticket-runtime.php:1193` | food_ticket_events_ensure_source_delet | 14 | catch بی‌صدا، الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 5 | `PAGE-029.84` | `food-ticket.php:2379` | food_ticket_cleanup_source_rows | 14 | catch بی‌صدا، الحاق در SQL، سرکوب @، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 6 | `PAGE-029.129` | `food-ticket.php:4737` | food_ticket_api_upload_access_file | 14 | الحاق در SQL، ورودی خام، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 7 | `PAGE-029.130` | `food-ticket.php:4807` | food_ticket_api_handle | 14 | الحاق در SQL، ورودی خام، سرکوب @، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 8 | `TEST-171.03` | `tests/test_request_center_claim_concurrency_mariadb.php:38` | بلوک رویه‌ای 38-198 | 14 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 9 | `TOOL-057.10` | `tools/test_employees_import_offline.php:121` | بلوک رویه‌ای 121-276 | 14 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 10 | `PAGE-025.22` | `food-ticket-runtime.php:815` | food_ticket_send_windows_graphic | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 11 | `PAGE-029.39` | `food-ticket.php:795` | food_ticket_access_rows_powershell | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 12 | `PAGE-029.41` | `food-ticket.php:1013` | food_ticket_access_delete_powershell_e | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 13 | `PAGE-029.85` | `food-ticket.php:2583` | food_ticket_retry_failed | 13 | catch بی‌صدا، الحاق در SQL، سرکوب @، نوشتن بدون تراکنش | ⬜ در نوبت |
| 14 | `TEST-111.08` | `tests/test_logging_mysql_integration.php:227` | Verify migration 025 changes the live  | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 15 | `TOOL-001.01` | `tools/asset_dedupe.php:1` | بلوک رویه‌ای 1-230 | 13 | الحاق در SQL، چاپ بدون فرار، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 16 | `PAGE-011.27` | `chatbot.php:700` | chatbot_api_handle | 12 | ورودی خام، چاپ بدون فرار، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 17 | `CRON-009.01` | `cron/rebuild_logging_issues.php:1` | بلوک رویه‌ای 1-53 | 12 | الحاق در SQL، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 18 | `TEST-111.06` | `tests/test_logging_mysql_integration.php:135` | Migration 025 must remain safe when th | 12 | الحاق در SQL، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 19 | `TEST-178.11` | `tests/test_request_center_t12_preflight.php:210` | بلوک رویه‌ای 210-322 | 12 | catch بی‌صدا، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 20 | `TOOL-024.01` | `tools/diag_source_row_delete.php:1` | بلوک رویه‌ای 1-128 | 12 | الحاق در SQL، ورودی خام، سرکوب @، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 21 | `PAGE-025.18` | `food-ticket-runtime.php:539` | food_ticket_process_print_queue | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 22 | `WEB-003.01` | `food-ticket-web/assets/food-capitalio-dashboard-4179.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 23 | `WEB-005.01` | `food-ticket-web/assets/food-capitalio-dashboard-r37.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 24 | `WEB-005.04` | `food-ticket-web/assets/food-capitalio-dashboard-r37.js:187` | assert on the output | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 25 | `WEB-007.01` | `food-ticket-web/assets/food-capitalio-dashboard-r38.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 26 | `WEB-007.04` | `food-ticket-web/assets/food-capitalio-dashboard-r38.js:187` | assert on the output | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 27 | `WEB-009.01` | `food-ticket-web/assets/food-capitalio-dashboard-r39.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 28 | `WEB-009.04` | `food-ticket-web/assets/food-capitalio-dashboard-r39.js:187` | assert on the output | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 29 | `WEB-012.01` | `food-ticket-web/assets/food-capitalio-dashboard-r41.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 30 | `WEB-012.04` | `food-ticket-web/assets/food-capitalio-dashboard-r41.js:187` | assert on the output | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 31 | `WEB-014.01` | `food-ticket-web/assets/food-capitalio-dashboard-r42.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 32 | `WEB-014.04` | `food-ticket-web/assets/food-capitalio-dashboard-r42.js:187` | assert on the output | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 33 | `PAGE-034.88` | `index.php:2465` | مسیر: settings | 11 | الحاق در SQL، ورودی خام، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 34 | `CORE-008.44` | `lib/logging-core.php:1025` | logging_emergency_failure | 11 | catch بی‌صدا، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 35 | `PAGE-021.03` | `food-ticket-groups.php:62` | food_ticket_groups_ensure | 10 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 36 | `PAGE-021.52` | `food-ticket-groups.php:1737` | food_ticket_group_try_unknown | 10 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش | ⬜ در نوبت |
| 37 | `PAGE-035.13` | `install.php:152` | مسیر: test_ldap | 10 | ورودی خام، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 38 | `CORE-016.13` | `lib/tickets-sla.php:362` | ticket_number_autofill | 10 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش | ⬜ در نوبت |
| 39 | `TEST-029.05` | `tests/test_backup_restore_mariadb_e2e.php:62` | بلوک رویه‌ای 62-159 | 10 | catch بی‌صدا، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 40 | `TOOL-008.02` | `tools/benchmark_seed.php:36` | Match the filesystem prerequisite crea | 10 | الحاق در SQL، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |

## فهرست کامل آدرس‌ها


### TEST — `TEST`


<details>
<summary><b>TEST-001</b> — <code>tests/ArchitectureTest.php</code> (178 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-001.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-001.02` | 4–173 | کلاس | `ArchitectureTest` | 3 | ⬜ |
| `TEST-001.03` | 175–178 | ناحیه | `Run tests` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-002</b> — <code>tests/SecurityTest.php</code> (250 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-002.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-002.02` | 4–245 | کلاس | `SecurityTest` | 3 | ⬜ |
| `TEST-002.03` | 247–250 | ناحیه | `Run tests` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-003</b> — <code>tests/download_page_helpers.py</code> (96 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-003.01` | 1–26 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-003.02` | 27–31 | بلوک | `version_tuple` | 0 | ⬜ |
| `TEST-003.03` | 32–58 | بلوک | `update_packages` | 0 | ⬜ |
| `TEST-003.04` | 59–67 | بلوک | `latest_package` | 0 | ⬜ |
| `TEST-003.05` | 68–71 | بلوک | `latest_package_name` | 0 | ⬜ |
| `TEST-003.06` | 72–92 | بلوک | `check_download_page_lists_only_latest_package` | 0 | ⬜ |
| `TEST-003.07` | 93–96 | بلوک | `read` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-004</b> — <code>tests/fixture_cd_dashboard_render.php</code> (10 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-004.01` | 1–2 | ناحیه | `بلوک رویه‌ای 1-2` | 0 | ⬜ |
| `TEST-004.02` | 3–3 | تابع | `e` | 0 | ⬜ |
| `TEST-004.03` | 4–4 | تابع | `valid_choice` | 0 | ⬜ |
| `TEST-004.04` | 5–5 | تابع | `cd_dvd_can_submit_out` | 0 | ⬜ |
| `TEST-004.05` | 5–5 | تابع | `cd_dvd_can_register_in` | 0 | ⬜ |
| `TEST-004.06` | 6–6 | تابع | `cd_dvd_query_records` | 0 | ⬜ |
| `TEST-004.07` | 7–7 | تابع | `cd_dvd_stats` | 0 | ⬜ |
| `TEST-004.08` | 8–10 | ناحیه | `بلوک رویه‌ای 8-10` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-005</b> — <code>tests/fixture_cd_history_render.php</code> (13 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-005.01` | 1–2 | ناحیه | `بلوک رویه‌ای 1-2` | 0 | ⬜ |
| `TEST-005.02` | 3–3 | تابع | `e` | 0 | ⬜ |
| `TEST-005.03` | 4–4 | تابع | `csrf_field` | 0 | ⬜ |
| `TEST-005.04` | 5–5 | تابع | `cd_dvd_can_manage_record` | 0 | ⬜ |
| `TEST-005.05` | 6–13 | ناحیه | `بلوک رویه‌ای 6-13` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-006</b> — <code>tests/fixture_traffic_render.php</code> (19 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-006.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 5 | ⬜ |
| `TEST-006.02` | 7–7 | تابع | `db` | 0 | ⬜ |
| `TEST-006.03` | 8–8 | تابع | `user_can` | 0 | ⬜ |
| `TEST-006.04` | 9–9 | تابع | `valid_choice` | 0 | ⬜ |
| `TEST-006.05` | 10–10 | تابع | `e` | 0 | ⬜ |
| `TEST-006.06` | 11–11 | تابع | `csrf_field` | 0 | ⬜ |
| `TEST-006.07` | 12–12 | تابع | `csp_nonce` | 0 | ⬜ |
| `TEST-006.08` | 13–13 | تابع | `render_header` | 0 | ⬜ |
| `TEST-006.09` | 14–14 | تابع | `render_footer` | 0 | ⬜ |
| `TEST-006.10` | 15–15 | تابع | `flash` | 0 | ⬜ |
| `TEST-006.11` | 16–19 | ناحیه | `بلوک رویه‌ای 16-19` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-007</b> — <code>tests/fixtures/jalali-php-convert.php</code> (51 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-007.01` | 1–51 | ناحیه | `بلوک رویه‌ای 1-51` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-008</b> — <code>tests/fixtures/request-center-claim-worker.php</code> (68 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-008.01` | 1–68 | ناحیه | `بلوک رویه‌ای 1-68` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-009</b> — <code>tests/fixtures/request-center-ddl-probe.php</code> (12 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-009.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-009.02` | 6–6 | ناحیه | `CI-only diagnostic endpoint. tests/.htaccess denie` | 0 | ⬜ |
| `TEST-009.03` | 7–12 | ناحیه | `The PHP built-in server used by the disposable CI ` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-010</b> — <code>tests/fixtures/request-center-session-seed.php</code> (21 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-010.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-011</b> — <code>tests/fixtures/request-center-t12-session-seed.php</code> (30 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-011.01` | 1–30 | ناحیه | `بلوک رویه‌ای 1-30` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-012</b> — <code>tests/js/food-aggregate-report.test.js</code> (189 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-012.01` | 1–72 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-012.02` | 73–189 | بلوک | `fire` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-013</b> — <code>tests/js/food-bulk-past-window.test.js</code> (33 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-013.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-013.02` | 10–33 | بلوک | `canSelect` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-014</b> — <code>tests/js/food-calendar-order-count.test.js</code> (95 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-014.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-014.02` | 8–26 | بلوک | `R` | 0 | ⬜ |
| `TEST-014.03` | 27–46 | بلوک | `canViewCounts` | 0 | ⬜ |
| `TEST-014.04` | 47–47 | بلوک | `esc` | 0 | ⬜ |
| `TEST-014.05` | 48–95 | بلوک | `faDigits` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-015</b> — <code>tests/js/food-dashboard-count.test.js</code> (27 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-015.01` | 1–27 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-016</b> — <code>tests/js/food-menu-by-menu.test.js</code> (44 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-016.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-016.02` | 14–14 | بلوک | `esc` | 0 | ⬜ |
| `TEST-016.03` | 15–44 | بلوک | `faDigits` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-017</b> — <code>tests/js/food-order-name-sort.test.js</code> (33 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-017.01` | 1–33 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-018</b> — <code>tests/js/food-report-fixes.test.js</code> (32 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-018.01` | 1–32 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-019</b> — <code>tests/js/food-report-relocation.test.js</code> (36 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-019.01` | 1–36 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-020</b> — <code>tests/js/list-sort-export.test.js</code> (63 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-020.01` | 1–30 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `TEST-020.02` | 31–31 | بلوک | `click` | 0 | ⬜ |
| `TEST-020.03` | 32–63 | بلوک | `sortBtn` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-021</b> — <code>tests/js/list-sort-rowclasses.test.js</code> (39 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-021.01` | 1–39 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-022</b> — <code>tests/js/list-sort.test.js</code> (111 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-022.01` | 1–32 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `TEST-022.02` | 33–35 | بلوک | `rowTexts` | 0 | ⬜ |
| `TEST-022.03` | 36–41 | بلوک | `clickHeader` | 0 | ⬜ |
| `TEST-022.04` | 42–111 | بلوک | `headerBtns` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-023</b> — <code>tests/smoke.php</code> (271 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-023.01` | 1–271 | ناحیه | `بلوک رویه‌ای 1-271` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-024</b> — <code>tests/test_admin_layout_design_static.py</code> (99 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-024.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-024.02` | 7–7 | بلوک | `AdminLayoutDesignStaticTests` | 0 | ⬜ |
| `TEST-024.03` | 8–18 | بلوک | `test_layout_editor_is_admin_only_and_saves_validat` | 0 | ⬜ |
| `TEST-024.04` | 19–30 | بلوک | `test_design_view_is_available_in_shared_pages_and_` | 0 | ⬜ |
| `TEST-024.05` | 31–77 | بلوک | `test_elements_support_pixel_drag_keyboard_offsets_` | 0 | ⬜ |
| `TEST-024.06` | 78–90 | بلوک | `test_pixel_offsets_are_not_capped_at_five_hundred` | 0 | ⬜ |
| `TEST-024.07` | 91–99 | بلوک | `test_order_table_has_ninth_action_track_and_keeps_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-025</b> — <code>tests/test_asset_inventory_form_compat_static.py</code> (71 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-025.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-025.02` | 16–29 | بلوک | `test_renderer_normalizes_legacy_and_current_argume` | 0 | ⬜ |
| `TEST-025.03` | 30–39 | بلوک | `test_current_index_call_uses_the_12_argument_layou` | 0 | ⬜ |
| `TEST-025.04` | 40–48 | بلوک | `test_16224_archive_has_the_legacy_argument_that_ca` | 1 | ⬜ |
| `TEST-025.05` | 49–71 | بلوک | `test_form_hotfix_is_a_valid_incremental_upgrade_fr` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-026</b> — <code>tests/test_asset_profile_mapping_static.py</code> (67 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-026.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-026.02` | 7–67 | بلوک | `read` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-027</b> — <code>tests/test_asset_scope_permissions.php</code> (75 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-027.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `TEST-027.02` | 7–10 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `TEST-027.03` | 11–14 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `TEST-027.04` | 15–18 | تابع | `user_can` | 0 | ⬜ |
| `TEST-027.05` | 19–75 | ناحیه | `بلوک رویه‌ای 19-75` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-028</b> — <code>tests/test_backup_restore_archive.php</code> (120 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-028.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `TEST-028.02` | 17–21 | تابع | `restore_test_assert` | 0 | ⬜ |
| `TEST-028.03` | 23–40 | تابع | `restore_test_zip` | 0 | ⬜ |
| `TEST-028.04` | 42–49 | تابع | `restore_test_call` | 0 | ⬜ |
| `TEST-028.05` | 51–60 | تابع | `restore_test_reject` | 0 | ⬜ |
| `TEST-028.06` | 61–120 | ناحیه | `بلوک رویه‌ای 61-120` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-029</b> — <code>tests/test_backup_restore_mariadb_e2e.php</code> (159 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-029.01` | 1–34 | ناحیه | `بلوک رویه‌ای 1-34` | 0 | ⬜ |
| `TEST-029.02` | 35–38 | تابع | `cfg` | 0 | ⬜ |
| `TEST-029.03` | 40–46 | تابع | `t08_check` | 0 | ⬜ |
| `TEST-029.04` | 48–61 | تابع | `t08_remove_tree` | 7 | ⬜ |
| `TEST-029.05` | 62–159 | ناحیه | `بلوک رویه‌ای 62-159` | **10** | ⬜ |

</details>

<details>
<summary><b>TEST-030</b> — <code>tests/test_backup_restore_static.py</code> (36 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-030.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-030.02` | 6–16 | بلوک | `test_destructive_restore_requires_primary_admin_an` | 0 | ⬜ |
| `TEST-030.03` | 17–29 | بلوک | `test_upload_restore_preflights_paths_sizes_collisi` | 0 | ⬜ |
| `TEST-030.04` | 30–36 | بلوک | `test_backup_download_path_is_confined_to_backup_ro` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-031</b> — <code>tests/test_cache_pin_consistency_static.py</code> (118 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-031.01` | 1–49 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-031.02` | 50–67 | بلوک | `live_versions` | 0 | ⬜ |
| `TEST-031.03` | 68–118 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-032</b> — <code>tests/test_cd_dashboard_report_browser.js</code> (24 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-032.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-033</b> — <code>tests/test_cd_dvd_incoming_and_permissions_static.py</code> (46 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-033.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-033.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-033.03` | 11–11 | بلوک | `CdDvdIncomingAndPermissionTests` | 0 | ⬜ |
| `TEST-033.04` | 12–22 | بلوک | `test_outgoing_choice_and_default_follow_role_permi` | 0 | ⬜ |
| `TEST-033.05` | 23–36 | بلوک | `test_incoming_form_has_internal_external_selection` | 0 | ⬜ |
| `TEST-033.06` | 37–46 | بلوک | `test_external_person_is_required_and_internal_iden` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-034</b> — <code>tests/test_cd_traffic_browser.js</code> (37 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-034.01` | 1–37 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-035</b> — <code>tests/test_change_history_through_16259.py</code> (84 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-035.01` | 1–11 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-035.02` | 12–15 | بلوک | `git` | 0 | ⬜ |
| `TEST-035.03` | 16–33 | بلوک | `expected_paths` | 0 | ⬜ |
| `TEST-035.04` | 34–35 | بلوک | `HistoricalIndexTest` | 0 | ⬜ |
| `TEST-035.05` | 36–38 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-035.06` | 39–44 | بلوک | `test_version_boundary_and_future_session_pointer` | 0 | ⬜ |
| `TEST-035.07` | 45–55 | بلوک | `test_complete_tree_inventory_without_opening_zip` | 0 | ⬜ |
| `TEST-035.08` | 56–73 | بلوک | `test_all_reachable_commits_if_full_history_availab` | 0 | ⬜ |
| `TEST-035.09` | 74–84 | بلوک | `test_pull_requests_and_comment_scope` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-036</b> — <code>tests/test_chatbot_relevance.py</code> (124 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-036.01` | 1–23 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-036.02` | 24–30 | بلوک | `normalize` | 0 | ⬜ |
| `TEST-036.03` | 31–39 | بلوک | `tokens` | 0 | ⬜ |
| `TEST-036.04` | 40–43 | بلوک | `contains_word` | 0 | ⬜ |
| `TEST-036.05` | 44–79 | بلوک | `guide_index` | 0 | ⬜ |
| `TEST-036.06` | 80–100 | بلوک | `search` | 0 | ⬜ |
| `TEST-036.07` | 101–124 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-037</b> — <code>tests/test_chatbot_tts_static.py</code> (145 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-037.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-037.02` | 9–145 | بلوک | `check` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-038</b> — <code>tests/test_chatbot_widget_static.py</code> (77 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-038.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-038.02` | 9–77 | بلوک | `check` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-039</b> — <code>tests/test_duty_split_and_work_hours_static.py</code> (247 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-039.01` | 1–52 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-039.02` | 53–57 | بلوک | `read` | 0 | ⬜ |
| `TEST-039.03` | 58–65 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-039.04` | 66–75 | بلوک | `php_function` | 0 | ⬜ |
| `TEST-039.05` | 76–93 | بلوک | `test_requester_guard_runs_before_primary_admin_sho` | 0 | ⬜ |
| `TEST-039.06` | 94–104 | بلوک | `test_eligibility_gate_compares_candidate_against_r` | 0 | ⬜ |
| `TEST-039.07` | 105–115 | بلوک | `test_self_assignment_has_configurable_escape_hatch` | 0 | ⬜ |
| `TEST-039.08` | 116–130 | بلوک | `test_escape_hatch_defaults_to_closed` | 0 | ⬜ |
| `TEST-039.09` | 131–150 | بلوک | `test_escape_hatch_tolerates_missing_config_layer` | 0 | ⬜ |
| `TEST-039.10` | 151–159 | بلوک | `test_business_hours_have_single_source` | 0 | ⬜ |
| `TEST-039.11` | 160–171 | بلوک | `test_no_hardcoded_clock_hours_remain` | 0 | ⬜ |
| `TEST-039.12` | 172–183 | بلوک | `test_no_hardcoded_weekend_days_remain` | 0 | ⬜ |
| `TEST-039.13` | 184–194 | بلوک | `test_work_hours_guard_against_inverted_range` | 0 | ⬜ |
| `TEST-039.14` | 195–205 | بلوک | `test_work_hours_guard_against_full_week_closure` | 0 | ⬜ |
| `TEST-039.15` | 206–226 | بلوک | `test_work_hours_are_editable_from_calendar_page` | 0 | ⬜ |
| `TEST-039.16` | 227–239 | بلوک | `test_work_hours_action_is_permission_guarded` | 0 | ⬜ |
| `TEST-039.17` | 240–247 | بلوک | `test_calendar_page_text_is_not_stale` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-040</b> — <code>tests/test_food_aggregate_report_static.py</code> (149 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-040.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-040.02` | 25–28 | بلوک | `main` | 0 | ⬜ |
| `TEST-040.03` | 29–149 | بلوک | `check` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-041</b> — <code>tests/test_food_bulk_operations_static.py</code> (105 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-041.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-041.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-041.03` | 11–11 | بلوک | `FoodBulkOperationsStaticTests` | 0 | ⬜ |
| `TEST-041.04` | 12–29 | بلوک | `test_bulk_api_and_food_menu_view_are_restricted_to` | 0 | ⬜ |
| `TEST-041.05` | 30–42 | بلوک | `test_snapshot_includes_active_local_or_domain_staf` | 0 | ⬜ |
| `TEST-041.06` | 43–69 | بلوک | `test_past_bulk_order_and_cancel_require_active_eme` | 0 | ⬜ |
| `TEST-041.07` | 70–77 | بلوک | `test_past_bulk_ui_is_enabled_only_for_order_or_can` | 0 | ⬜ |
| `TEST-041.08` | 78–88 | بلوک | `test_canteen_change_uses_manager_override_but_keep` | 0 | ⬜ |
| `TEST-041.09` | 89–105 | بلوک | `test_bulk_tab_date_picker_and_removed_calendar_eme` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-042</b> — <code>tests/test_food_cancel_request_timestamp.py</code> (33 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-042.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-042.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-042.03` | 11–11 | بلوک | `FoodCancelRequestTimestampTests` | 0 | ⬜ |
| `TEST-042.04` | 12–17 | بلوک | `test_request_time_is_saved_and_included_in_audit_a` | 5 | ⬜ |
| `TEST-042.05` | 18–33 | بلوک | `test_dashboard_and_manager_request_report_show_req` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-043</b> — <code>tests/test_food_cap_and_receipt_unit.php</code> (73 خط، 28 آدرس، بازبینی‌شده 0/28)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-043.01` | 1–2 | ناحیه | `بلوک رویه‌ای 1-2` | 0 | ⬜ |
| `TEST-043.02` | 3–4 | ناحیه | `Behavioural tests of the real engine/order source ` | 0 | ⬜ |
| `TEST-043.03` | 5–5 | تابع | `food_ticket_source_table` | 0 | ⬜ |
| `TEST-043.04` | 6–6 | تابع | `food_order_schema_ensure` | 0 | ⬜ |
| `TEST-043.05` | 7–7 | تابع | `food_order_mode` | 0 | ⬜ |
| `TEST-043.06` | 8–8 | تابع | `food_order_iso_date` | 0 | ⬜ |
| `TEST-043.07` | 9–9 | تابع | `food_order_digits` | 0 | ⬜ |
| `TEST-043.08` | 10–10 | تابع | `food_ticket_digits` | 0 | ⬜ |
| `TEST-043.09` | 11–11 | تابع | `food_ticket_code` | 0 | ⬜ |
| `TEST-043.10` | 12–12 | تابع | `food_ticket_card_key` | 0 | ⬜ |
| `TEST-043.11` | 13–13 | تابع | `food_ticket_parse_date` | 0 | ⬜ |
| `TEST-043.12` | 14–14 | تابع | `food_ticket_parse_time` | 0 | ⬜ |
| `TEST-043.13` | 15–15 | تابع | `food_ticket_source_key` | 0 | ⬜ |
| `TEST-043.14` | 16–16 | تابع | `food_ticket_existing_source` | 0 | ⬜ |
| `TEST-043.15` | 17–17 | تابع | `food_ticket_existing_ticket` | 0 | ⬜ |
| `TEST-043.16` | 18–18 | تابع | `food_ticket_card_equals` | 0 | ⬜ |
| `TEST-043.17` | 19–19 | تابع | `food_ticket_guest_card` | 0 | ⬜ |
| `TEST-043.18` | 20–20 | تابع | `food_guest_requested_total` | 0 | ⬜ |
| `TEST-043.19` | 21–21 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-043.20` | 22–22 | تابع | `food_ticket_config` | 0 | ⬜ |
| `TEST-043.21` | 23–23 | تابع | `setting` | 0 | ⬜ |
| `TEST-043.22` | 24–24 | تابع | `setting_forget_cache` | 0 | ⬜ |
| `TEST-043.23` | 25–36 | کلاس | `ReceiptStmt` | 0 | ⬜ |
| `TEST-043.24` | 37–40 | کلاس | `ReceiptDB` | 0 | ⬜ |
| `TEST-043.25` | 41–41 | تابع | `db` | 0 | ⬜ |
| `TEST-043.26` | 42–43 | ناحیه | `بلوک رویه‌ای 42-43` | 0 | ⬜ |
| `TEST-043.27` | 44–44 | تابع | `chk` | 0 | ⬜ |
| `TEST-043.28` | 45–73 | ناحیه | `بلوک رویه‌ای 45-73` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-044</b> — <code>tests/test_food_dashboard_bulk_theme_fix_package.py</code> (48 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-044.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-044.02` | 11–48 | بلوک | `test_food_dashboard_bulk_theme_package_is_cumulati` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-045</b> — <code>tests/test_food_dashboard_css_isolation.py</code> (42 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-045.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-045.02` | 11–42 | بلوک | `test_r40_dashboard_grids_are_isolated_from_legacy_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-046</b> — <code>tests/test_food_dashboard_r41_features.py</code> (59 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-046.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-046.02` | 13–47 | بلوک | `test_r41_is_integrated_and_self_contained` | 0 | ⬜ |
| `TEST-046.03` | 48–59 | بلوک | `test_r41_theme_reveal_respects_reduced_motion_fall` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-047</b> — <code>tests/test_food_dashboard_update_package.py</code> (58 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-047.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-047.02` | 11–58 | بلوک | `test_r34_food_dashboard_is_integrated_and_uses_foo` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-048</b> — <code>tests/test_food_day_visit_static.py</code> (105 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-048.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-048.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-048.03` | 10–105 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-049</b> — <code>tests/test_food_delivery_comparison_node.js</code> (12 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-049.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-050</b> — <code>tests/test_food_delivery_comparison_unit.php</code> (25 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-050.01` | 1–2 | ناحیه | `بلوک رویه‌ای 1-2` | 0 | ⬜ |
| `TEST-050.02` | 3–13 | کلاس | `DeliveryStmt` | 0 | ⬜ |
| `TEST-050.03` | 14–14 | کلاس | `DeliveryDB` | 0 | ⬜ |
| `TEST-050.04` | 15–15 | تابع | `db` | 0 | ⬜ |
| `TEST-050.05` | 16–16 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-050.06` | 17–17 | ناحیه | `بلوک رویه‌ای 17-17` | 0 | ⬜ |
| `TEST-050.07` | 18–18 | تابع | `chk` | 0 | ⬜ |
| `TEST-050.08` | 19–25 | ناحیه | `بلوک رویه‌ای 19-25` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-051</b> — <code>tests/test_food_emergency_order_package.py</code> (43 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-051.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-051.02` | 11–43 | بلوک | `test_r43_package_is_limited_and_integral` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-052</b> — <code>tests/test_food_emergency_order_window_static.py</code> (68 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-052.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-052.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-052.03` | 11–11 | بلوک | `EmergencyOrderWindowTests` | 0 | ⬜ |
| `TEST-052.04` | 12–24 | بلوک | `test_grant_is_single_day_bounded_and_reasoned` | 0 | ⬜ |
| `TEST-052.05` | 25–35 | بلوک | `test_only_active_window_bypasses_past_or_closed_da` | 0 | ⬜ |
| `TEST-052.06` | 36–47 | بلوک | `test_manager_api_and_calendar_controls_are_permiss` | 0 | ⬜ |
| `TEST-052.07` | 48–55 | بلوک | `test_employee_ui_requires_backend_window_for_late_` | 0 | ⬜ |
| `TEST-052.08` | 56–68 | بلوک | `test_order_sort_uses_surname_then_first_name` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-053</b> — <code>tests/test_food_employee_local_static.py</code> (40 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-053.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-053.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-053.03` | 10–40 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-054</b> — <code>tests/test_food_group_typography_static.py</code> (47 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-054.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-054.02` | 7–7 | بلوک | `FoodGroupTypographyStaticTests` | 0 | ⬜ |
| `TEST-054.03` | 8–19 | بلوک | `test_group_print_model_uses_separate_menu_and_memb` | 0 | ⬜ |
| `TEST-054.04` | 20–30 | بلوک | `test_group_menu_counts_and_menu_to_member_spacing_` | 0 | ⬜ |
| `TEST-054.05` | 31–47 | بلوک | `test_group_designer_exposes_per_style_font_control` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-055</b> — <code>tests/test_food_guest_browser.js</code> (17 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-055.01` | 1–17 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-056</b> — <code>tests/test_food_guest_cap_path_unit.php</code> (44 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-056.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-056.02` | 4–4 | تابع | `db_table_exists` | 0 | ⬜ |
| `TEST-056.03` | 5–5 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-056.04` | 6–6 | تابع | `user_can` | 0 | ⬜ |
| `TEST-056.05` | 7–21 | کلاس | `CapStatement` | 1 | ⬜ |
| `TEST-056.06` | 22–22 | کلاس | `CapDatabase` | 4 | ⬜ |
| `TEST-056.07` | 23–23 | تابع | `db` | 0 | ⬜ |
| `TEST-056.08` | 24–25 | ناحیه | `بلوک رویه‌ای 24-25` | 0 | ⬜ |
| `TEST-056.09` | 26–26 | تابع | `chk` | 0 | ⬜ |
| `TEST-056.10` | 27–44 | ناحیه | `بلوک رویه‌ای 27-44` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-057</b> — <code>tests/test_food_guest_cap_race_node.js</code> (14 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-057.01` | 1–14 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-058</b> — <code>tests/test_food_guest_cap_ui_node.js</code> (25 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-058.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-058.02` | 7–7 | بلوک | `wait` | 0 | ⬜ |
| `TEST-058.03` | 8–20 | بلوک | `run` | 4 | ⬜ |
| `TEST-058.04` | 21–25 | بلوک | `testCardLimit` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-059</b> — <code>tests/test_food_guest_manager_bulk_tab.py</code> (58 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-059.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-059.02` | 7–8 | بلوک | `FoodGuestManagerBulkTabTests` | 0 | ⬜ |
| `TEST-059.03` | 9–15 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-059.04` | 16–29 | بلوک | `test_guest_manager_routes_require_emergency_permis` | 1 | ⬜ |
| `TEST-059.05` | 30–37 | بلوک | `test_support_view_includes_historical_requests_but` | 0 | ⬜ |
| `TEST-059.06` | 38–50 | بلوک | `test_guest_manager_tab_and_actions_are_inside_grou` | 1 | ⬜ |
| `TEST-059.07` | 51–58 | بلوک | `test_served_asset_copy_and_cache_buster_match` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-060</b> — <code>tests/test_food_guest_personal_scope.php</code> (78 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-060.01` | 1–2 | ناحیه | `بلوک رویه‌ای 1-2` | 0 | ⬜ |
| `TEST-060.02` | 3–3 | ناحیه | `Behavioural test of the real functions with an in-` | 0 | ⬜ |
| `TEST-060.03` | 4–4 | تابع | `user_can` | 0 | ⬜ |
| `TEST-060.04` | 5–5 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-060.05` | 7–42 | کلاس | `GuestStatement` | 0 | ⬜ |
| `TEST-060.06` | 43–51 | کلاس | `GuestDatabase` | 0 | ⬜ |
| `TEST-060.07` | 52–52 | تابع | `db` | 0 | ⬜ |
| `TEST-060.08` | 53–53 | ناحیه | `بلوک رویه‌ای 53-53` | 0 | ⬜ |
| `TEST-060.09` | 54–54 | تابع | `check_guest` | 0 | ⬜ |
| `TEST-060.10` | 55–55 | تابع | `denied` | 0 | ⬜ |
| `TEST-060.11` | 56–59 | ناحیه | `بلوک رویه‌ای 56-59` | 0 | ⬜ |
| `TEST-060.12` | 60–78 | ناحیه | `Many other people's rows before our own prove that` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-061</b> — <code>tests/test_food_guest_personal_scope_static.py</code> (27 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-061.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-061.02` | 6–21 | بلوک | `test_personal_and_privileged_guest_routes_are_isol` | 1 | ⬜ |
| `TEST-061.03` | 22–27 | بلوک | `test_guest_assets_cache_busted_and_css_mirrored` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-062</b> — <code>tests/test_food_guest_personal_ui_node.js</code> (51 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-062.01` | 1–51 | بلوک | `مقدمهٔ فایل` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-063</b> — <code>tests/test_food_guest_request_manager_actions.py</code> (61 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-063.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-063.02` | 7–8 | بلوک | `FoodGuestRequestManagerActionTests` | 0 | ⬜ |
| `TEST-063.03` | 9–12 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-063.04` | 13–19 | بلوک | `test_owner_and_support_manager_permissions_are_enf` | 0 | ⬜ |
| `TEST-063.05` | 20–28 | بلوک | `test_update_delete_and_cancel_have_separate_api_ro` | 1 | ⬜ |
| `TEST-063.06` | 29–37 | بلوک | `test_ui_renders_authorized_edit_cancel_delete_acti` | 1 | ⬜ |
| `TEST-063.07` | 38–42 | بلوک | `test_current_day_deadline_and_past_date_are_guarde` | 0 | ⬜ |
| `TEST-063.08` | 43–61 | بلوک | `test_past_guest_requests_can_be_deleted_without_by` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-064</b> — <code>tests/test_food_manual_attendance_static.py</code> (96 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-064.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-064.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-064.03` | 11–11 | بلوک | `FoodManualAttendanceStaticTests` | 0 | ⬜ |
| `TEST-064.04` | 12–25 | بلوک | `test_manual_attendance_is_in_settings_and_removed_` | 0 | ⬜ |
| `TEST-064.05` | 26–35 | بلوک | `test_manual_write_requires_group_edit_permission_a` | 0 | ⬜ |
| `TEST-064.06` | 36–50 | بلوک | `test_list_contains_only_active_people_with_the_sam` | 0 | ⬜ |
| `TEST-064.07` | 51–72 | بلوک | `test_single_and_bulk_save_support_ticket_and_no_ti` | 0 | ⬜ |
| `TEST-064.08` | 73–84 | بلوک | `test_ui_has_live_search_selection_results_and_inde` | 0 | ⬜ |
| `TEST-064.09` | 85–96 | بلوک | `test_monitoring_distinguishes_no_ticket_from_ticke` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-065</b> — <code>tests/test_food_meals_static.py</code> (55 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-065.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-065.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-065.03` | 12–12 | بلوک | `FoodMealSeparationTests` | 0 | ⬜ |
| `TEST-065.04` | 13–19 | بلوک | `test_fresh_schemas_keep_legacy_rows_as_lunch_and_a` | 0 | ⬜ |
| `TEST-065.05` | 20–29 | بلوک | `test_upgrade_migration_adds_lunch_defaults_and_rep` | 0 | ⬜ |
| `TEST-065.06` | 30–37 | بلوک | `test_api_filters_calendar_orders_and_statistics_by` | 2 | ⬜ |
| `TEST-065.07` | 38–47 | بلوک | `test_order_ui_and_manager_expose_meal_switch_and_s` | 0 | ⬜ |
| `TEST-065.08` | 48–55 | بلوک | `test_receipt_order_sources_are_explicitly_lunch_on` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-066</b> — <code>tests/test_food_monitoring_order_comparison_unit.php</code> (21 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-066.01` | 1–2 | ناحیه | `بلوک رویه‌ای 1-2` | 0 | ⬜ |
| `TEST-066.02` | 3–3 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-066.03` | 4–8 | کلاس | `MonitorStmt` | 0 | ⬜ |
| `TEST-066.04` | 9–9 | کلاس | `MonitorDB` | 0 | ⬜ |
| `TEST-066.05` | 10–10 | تابع | `db` | 0 | ⬜ |
| `TEST-066.06` | 11–12 | ناحیه | `بلوک رویه‌ای 11-12` | 0 | ⬜ |
| `TEST-066.07` | 13–13 | تابع | `chk` | 0 | ⬜ |
| `TEST-066.08` | 14–21 | ناحیه | `بلوک رویه‌ای 14-21` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-067</b> — <code>tests/test_food_order_calendar_node.js</code> (28 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-067.01` | 1–28 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-068</b> — <code>tests/test_food_order_selection_rerender_node.js</code> (12 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-068.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-069</b> — <code>tests/test_food_orders_static.py</code> (859 خط، 46 آدرس، بازبینی‌شده 0/46)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-069.01` | 1–16 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-069.02` | 17–20 | بلوک | `read` | 0 | ⬜ |
| `TEST-069.03` | 21–24 | بلوک | `strip_sql_comments` | 0 | ⬜ |
| `TEST-069.04` | 25–32 | بلوک | `create_tables` | 0 | ⬜ |
| `TEST-069.05` | 33–33 | بلوک | `FoodOrdersStaticTests` | 0 | ⬜ |
| `TEST-069.06` | 34–41 | بلوک | `test_upgrade_creates_exactly_five_new_tables_only` | 0 | ⬜ |
| `TEST-069.07` | 42–53 | بلوک | `test_rollback_drops_only_the_five_module_tables` | 0 | ⬜ |
| `TEST-069.08` | 54–60 | بلوک | `test_fresh_install_schemas_include_same_five_table` | 0 | ⬜ |
| `TEST-069.09` | 61–88 | بلوک | `test_table_definitions_match_across_upgrade_and_fr` | 0 | ⬜ |
| `TEST-069.10` | 89–97 | بلوک | `test_runtime_schema_helper_is_read_only` | 0 | ⬜ |
| `TEST-069.11` | 98–127 | بلوک | `test_guest_requests_allow_today_only_through_a_con` | 0 | ⬜ |
| `TEST-069.12` | 128–140 | بلوک | `test_worker_has_one_internal_order_map_and_no_acce` | 0 | ⬜ |
| `TEST-069.13` | 141–156 | بلوک | `test_legacy_access_order_fields_are_not_live_confi` | 0 | ⬜ |
| `TEST-069.14` | 157–165 | بلوک | `test_legacy_order_mode_cannot_be_changed_through_t` | 0 | ⬜ |
| `TEST-069.15` | 166–180 | بلوک | `test_employee_search_and_order_visibility_are_scop` | 0 | ⬜ |
| `TEST-069.16` | 181–191 | بلوک | `test_proxy_order_is_gated_by_national_code_and_rat` | 0 | ⬜ |
| `TEST-069.17` | 192–231 | بلوک | `test_panel_and_employee_assets_are_present_and_loc` | 0 | ⬜ |
| `TEST-069.18` | 232–241 | بلوک | `test_daily_report_fields_and_repeated_compact_reco` | 0 | ⬜ |
| `TEST-069.19` | 242–251 | بلوک | `test_food_group_daily_order_report_uses_dated_memb` | 0 | ⬜ |
| `TEST-069.20` | 252–262 | بلوک | `test_food_statistics_are_grouped_by_day_with_jalal` | 0 | ⬜ |
| `TEST-069.21` | 263–271 | بلوک | `test_food_dashboard_prints_each_printed_event_once` | 0 | ⬜ |
| `TEST-069.22` | 272–280 | بلوک | `test_food_panel_theme_toggle_updates_global_mode` | 0 | ⬜ |
| `TEST-069.23` | 281–295 | بلوک | `test_selected_staff_report_loads_a_checklist_and_p` | 0 | ⬜ |
| `TEST-069.24` | 296–305 | بلوک | `test_global_dark_theme_covers_food_forms_and_sched` | 0 | ⬜ |
| `TEST-069.25` | 306–309 | بلوک | `test_panel_assets_are_synchronized` | 0 | ⬜ |
| `TEST-069.26` | 310–316 | بلوک | `test_day_impact_confirmation_locks_and_reuses_the_` | 0 | ⬜ |
| `TEST-069.27` | 317–333 | بلوک | `test_selected_employee_orders_report_has_search_da` | 0 | ⬜ |
| `TEST-069.28` | 334–374 | بلوک | `test_emergency_order_window_is_under_system_settin` | 0 | ⬜ |
| `TEST-069.29` | 375–448 | بلوک | `test_daily_orders_and_statistics_modes_are_separat` | 0 | ⬜ |
| `TEST-069.30` | 449–469 | بلوک | `test_day_status_form_drops_reason_but_backend_stil` | 0 | ⬜ |
| `TEST-069.31` | 470–496 | بلوک | `test_proxy_calendar_reads_only_the_verified_recipi` | 0 | ⬜ |
| `TEST-069.32` | 497–536 | بلوک | `test_proxy_order_list_uses_only_the_verified_recip` | 5 | ⬜ |
| `TEST-069.33` | 537–558 | بلوک | `test_proxy_cancel_is_server_scoped_and_reverifies_` | 1 | ⬜ |
| `TEST-069.34` | 559–577 | بلوک | `test_proxy_cancellation_has_visible_confirmation_a` | 0 | ⬜ |
| `TEST-069.35` | 578–614 | بلوک | `test_excel_date_range_metadata_and_print_scope_are` | 2 | ⬜ |
| `TEST-069.36` | 615–623 | بلوک | `test_personal_order_table_uses_row_number_instead_` | 0 | ⬜ |
| `TEST-069.37` | 624–635 | بلوک | `test_order_source_explanations_are_not_rendered_in` | 0 | ⬜ |
| `TEST-069.38` | 636–664 | بلوک | `test_admin_order_edit_route_and_controls_are_remov` | 0 | ⬜ |
| `TEST-069.39` | 665–681 | بلوک | `test_order_highlight_colors_and_both_datepicker_ar` | 0 | ⬜ |
| `TEST-069.40` | 682–705 | بلوک | `test_employee_onboarding_is_initial_gate_and_day_d` | 0 | ⬜ |
| `TEST-069.41` | 706–729 | بلوک | `test_proxy_order_submission_targets_selected_emplo` | 1 | ⬜ |
| `TEST-069.42` | 730–748 | بلوک | `test_proxy_recipient_is_verified_server_side_and_e` | 0 | ⬜ |
| `TEST-069.43` | 749–763 | بلوک | `test_adjacent_calendar_days_are_disabled_and_manag` | 0 | ⬜ |
| `TEST-069.44` | 764–829 | بلوک | `test_closed_day_cancel_request_and_masked_national` | 0 | ⬜ |
| `TEST-069.45` | 830–839 | بلوک | `test_proxy_order_rate_allows_many_people` | 0 | ⬜ |
| `TEST-069.46` | 840–859 | بلوک | `test_order_list_default_range_includes_next_jalali` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-070</b> — <code>tests/test_food_panel_full_node.js</code> (5 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-070.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-071</b> — <code>tests/test_food_permissions_all_roles_static.py</code> (48 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-071.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-071.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-071.03` | 11–11 | بلوک | `FoodPermissionsAllRolesTests` | 0 | ⬜ |
| `TEST-071.04` | 12–19 | بلوک | `test_food_permission_catalog_is_rendered_for_every` | 0 | ⬜ |
| `TEST-071.05` | 20–28 | بلوک | `test_food_grants_are_not_stripped_when_saving_cust` | 0 | ⬜ |
| `TEST-071.06` | 29–37 | بلوک | `test_old_restriction_migration_no_longer_deletes_f` | 1 | ⬜ |
| `TEST-071.07` | 38–48 | بلوک | `test_domain_and_local_users_share_role_permission_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-072</b> — <code>tests/test_food_personnel_link_local_accounts_static.py</code> (75 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-072.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-072.02` | 9–10 | بلوک | `FoodPersonnelLocalLinkStaticTests` | 0 | ⬜ |
| `TEST-072.03` | 11–15 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-072.04` | 16–21 | بلوک | `test_candidates_include_local_food_staff_not_admin` | 0 | ⬜ |
| `TEST-072.05` | 22–29 | بلوک | `test_merge_accepts_local_staff_account_and_keeps_b` | 1 | ⬜ |
| `TEST-072.06` | 30–38 | بلوک | `test_manual_code_lookup_supports_335_after_ad_code` | 0 | ⬜ |
| `TEST-072.07` | 39–66 | بلوک | `test_domain_entered_employee_code_is_authoritative` | 0 | ⬜ |
| `TEST-072.08` | 67–75 | بلوک | `test_offline_merge_covers_references_and_report` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-073</b> — <code>tests/test_food_printed_delivery_report_static.py</code> (68 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-073.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-073.02` | 9–12 | بلوک | `read` | 0 | ⬜ |
| `TEST-073.03` | 13–13 | بلوک | `PrintedDeliveryReportStaticTests` | 0 | ⬜ |
| `TEST-073.04` | 14–33 | بلوک | `test_report_uses_printed_delivered_employee_events` | 0 | ⬜ |
| `TEST-073.05` | 34–44 | بلوک | `test_report_api_is_restricted_to_food_order_close` | 0 | ⬜ |
| `TEST-073.06` | 45–57 | بلوک | `test_report_is_appended_after_statistics_and_match` | 0 | ⬜ |
| `TEST-073.07` | 58–68 | بلوک | `test_mirrored_assets_and_cache_versions_are_update` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-074</b> — <code>tests/test_food_queue_reconciliation_unit.php</code> (64 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-074.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-074.02` | 4–4 | تابع | `food_order_internal_food_for_employee` | 0 | ⬜ |
| `TEST-074.03` | 5–5 | تابع | `food_ticket_group_event_ids_for_run` | 0 | ⬜ |
| `TEST-074.04` | 6–6 | تابع | `food_ticket_group_print_mode` | 0 | ⬜ |
| `TEST-074.05` | 7–7 | تابع | `db_table_exists` | 0 | ⬜ |
| `TEST-074.06` | 8–8 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-074.07` | 9–9 | تابع | `food_ticket_tpl_print_network` | 0 | ⬜ |
| `TEST-074.08` | 10–33 | کلاس | `QueueStmt` | 1 | ⬜ |
| `TEST-074.09` | 34–37 | کلاس | `QueueDB` | 0 | ⬜ |
| `TEST-074.10` | 38–38 | تابع | `db` | 0 | ⬜ |
| `TEST-074.11` | 39–40 | ناحیه | `بلوک رویه‌ای 39-40` | 0 | ⬜ |
| `TEST-074.12` | 41–41 | تابع | `chk` | 0 | ⬜ |
| `TEST-074.13` | 42–42 | تابع | `event` | 0 | ⬜ |
| `TEST-074.14` | 43–43 | تابع | `reset_test` | 0 | ⬜ |
| `TEST-074.15` | 44–44 | تابع | `process` | 0 | ⬜ |
| `TEST-074.16` | 45–64 | ناحیه | `بلوک رویه‌ای 45-64` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-075</b> — <code>tests/test_food_report_ui_static.py</code> (115 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-075.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-075.02` | 9–12 | بلوک | `read` | 0 | ⬜ |
| `TEST-075.03` | 13–13 | بلوک | `FoodReportUiStaticTests` | 0 | ⬜ |
| `TEST-075.04` | 14–24 | بلوک | `test_all_groups_are_available_and_group_report_sup` | 0 | ⬜ |
| `TEST-075.05` | 25–33 | بلوک | `test_daily_order_field_choices_persist_per_user_in` | 0 | ⬜ |
| `TEST-075.06` | 34–44 | بلوک | `test_food_report_prints_clone_only_result_lists` | 0 | ⬜ |
| `TEST-075.07` | 45–63 | بلوک | `test_multicolumn_reports_have_dividers_and_larger_` | 0 | ⬜ |
| `TEST-075.08` | 64–79 | بلوک | `test_employee_statistics_serials_run_down_each_col` | 0 | ⬜ |
| `TEST-075.09` | 80–90 | بلوک | `test_food_statistics_layout_depends_on_the_selecte` | 0 | ⬜ |
| `TEST-075.10` | 91–98 | بلوک | `test_repeated_report_blocks_keep_only_their_bounda` | 0 | ⬜ |
| `TEST-075.11` | 99–115 | بلوک | `test_settings_code_link_is_modal_not_an_inline_sec` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-076</b> — <code>tests/test_food_temporary_group_transfer_static.py</code> (71 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-076.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-076.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-076.03` | 11–11 | بلوک | `FoodTemporaryGroupTransferStaticTests` | 0 | ⬜ |
| `TEST-076.04` | 12–25 | بلوک | `test_manager_transfer_uses_day_visit_record_and_ne` | 1 | ⬜ |
| `TEST-076.05` | 26–35 | بلوک | `test_moved_permanent_member_is_excluded_from_home_` | 0 | ⬜ |
| `TEST-076.06` | 36–45 | بلوک | `test_absence_and_group_views_mark_temporary_member` | 0 | ⬜ |
| `TEST-076.07` | 46–57 | بلوک | `test_bulk_ui_allows_temporary_transfer_and_return_` | 0 | ⬜ |
| `TEST-076.08` | 58–71 | بلوک | `test_return_removes_daily_transfer_with_audit_and_` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-077</b> — <code>tests/test_food_ticket_config_paths.php</code> (39 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-077.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-077.02` | 6–10 | کلاس | `FoodTicketFakeStatement` | 0 | ⬜ |
| `TEST-077.03` | 12–16 | کلاس | `FoodTicketFakeDb` | 0 | ⬜ |
| `TEST-077.04` | 17–24 | ناحیه | `بلوک رویه‌ای 17-24` | 0 | ⬜ |
| `TEST-077.05` | 25–25 | تابع | `db` | 0 | ⬜ |
| `TEST-077.06` | 26–26 | تابع | `cfg` | 0 | ⬜ |
| `TEST-077.07` | 27–39 | ناحیه | `بلوک رویه‌ای 27-39` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-078</b> — <code>tests/test_food_ticket_diagnostic_log_levels_static.py</code> (63 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-078.01` | 1–14 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-078.02` | 15–28 | بلوک | `test_successful_print_diagnostics_are_debug_not_er` | 0 | ⬜ |
| `TEST-078.03` | 29–37 | بلوک | `test_template_adjustment_is_debug_and_render_const` | 0 | ⬜ |
| `TEST-078.04` | 38–63 | بلوک | `test_combined_hotfix_includes_inventory_compatibil` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-079</b> — <code>tests/test_food_ticket_logic.php</code> (52 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-079.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-079.02` | 4–4 | تابع | `db` | 0 | ⬜ |
| `TEST-079.03` | 5–5 | تابع | `db_table_exists` | 0 | ⬜ |
| `TEST-079.04` | 6–6 | تابع | `cfg` | 0 | ⬜ |
| `TEST-079.05` | 7–7 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-079.06` | 8–13 | ناحیه | `بلوک رویه‌ای 8-13` | 0 | ⬜ |
| `TEST-079.07` | 14–18 | تابع | `expect` | 0 | ⬜ |
| `TEST-079.08` | 19–52 | ناحیه | `بلوک رویه‌ای 19-52` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-080</b> — <code>tests/test_food_update_package_assets.py</code> (70 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-080.01` | 1–41 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-080.02` | 42–60 | بلوک | `test_food_runtime_recovery_bundle_is_complete_and_` | 0 | ⬜ |
| `TEST-080.03` | 61–70 | بلوک | `test_hotfix_package_is_intact_and_panel_is_mirrore` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-081</b> — <code>tests/test_food_worker_window.php</code> (141 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-081.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `TEST-081.02` | 16–19 | تابع | `cfg` | 0 | ⬜ |
| `TEST-081.03` | 20–23 | ناحیه | `بلوک رویه‌ای 20-23` | 0 | ⬜ |
| `TEST-081.04` | 24–31 | تابع | `food_ticket_auto_receipt_holiday` | 0 | ⬜ |
| `TEST-081.05` | 32–35 | ناحیه | `بلوک رویه‌ای 32-35` | 0 | ⬜ |
| `TEST-081.06` | 36–43 | تابع | `expect` | 0 | ⬜ |
| `TEST-081.07` | 45–52 | تابع | `state_at` | 0 | ⬜ |
| `TEST-081.08` | 54–60 | ناحیه | `۱) پارس روزهای هفته` | 0 | ⬜ |
| `TEST-081.09` | 61–66 | ناحیه | `۲) پارس ساعت` | 0 | ⬜ |
| `TEST-081.10` | 67–81 | ناحیه | `۳) مرزهای پنجره: ۱۱:۰۰–۱۴:۰۰ با حاشیهٔ ۳۰ و ۱۵ ⇒ م` | 0 | ⬜ |
| `TEST-081.11` | 82–91 | ناحیه | `۴) حاشیهٔ صفر هم باید کار کند (کاربر می‌تواند «تا ` | 0 | ⬜ |
| `TEST-081.12` | 92–98 | ناحیه | `۵) روزهای غیرکاری و تعطیلات` | 0 | ⬜ |
| `TEST-081.13` | 99–104 | ناحیه | `۶) خاموش بودن پنجره = رفتار نسخهٔ قبلی (۲۴/۷)` | 0 | ⬜ |
| `TEST-081.14` | 105–112 | ناحیه | `۷) شروع بعدی` | 0 | ⬜ |
| `TEST-081.15` | 113–124 | ناحیه | `۸) ریتم پایش` | 0 | ⬜ |
| `TEST-081.16` | 125–141 | ناحیه | `۹) پایداری: تنظیمات ناقص نباید fatal بدهد` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-082</b> — <code>tests/test_food_worker_window_static.py</code> (452 خط، 50 آدرس، بازبینی‌شده 0/50)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-082.01` | 1–42 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-082.02` | 43–50 | بلوک | `_css_rule` | 0 | ⬜ |
| `TEST-082.03` | 51–59 | بلوک | `test_migration_031_is_the_only_new_file` | 0 | ⬜ |
| `TEST-082.04` | 60–64 | بلوک | `test_migration_adds_every_window_column` | 0 | ⬜ |
| `TEST-082.05` | 65–72 | بلوک | `test_migration_is_idempotent_for_every_column` | 0 | ⬜ |
| `TEST-082.06` | 73–90 | بلوک | `test_migration_defaults_match_php_defaults` | 0 | ⬜ |
| `TEST-082.07` | 91–100 | بلوک | `test_fresh_install_schema_has_every_window_column` | 0 | ⬜ |
| `TEST-082.08` | 101–119 | بلوک | `test_fresh_install_defaults_match_the_migration` | 0 | ⬜ |
| `TEST-082.09` | 120–126 | بلوک | `test_updater_enforces_the_incremental_prerequisite` | 0 | ⬜ |
| `TEST-082.10` | 127–137 | بلوک | `test_updater_prerequisite_has_an_explicit_escape_h` | 2 | ⬜ |
| `TEST-082.11` | 138–146 | بلوک | `test_window_disabled_by_default_so_upgrades_keep_o` | 0 | ⬜ |
| `TEST-082.12` | 147–159 | بلوک | `test_window_module_exposes_the_public_helpers` | 0 | ⬜ |
| `TEST-082.13` | 160–165 | بلوک | `test_window_module_is_loaded_before_config_default` | 0 | ⬜ |
| `TEST-082.14` | 166–172 | بلوک | `test_config_reads_window_columns_defensively` | 0 | ⬜ |
| `TEST-082.15` | 173–180 | بلوک | `test_window_reuses_the_single_holiday_calendar` | 0 | ⬜ |
| `TEST-082.16` | 181–185 | بلوک | `test_window_save_only_writes_existing_columns` | 1 | ⬜ |
| `TEST-082.17` | 186–194 | بلوک | `test_save_config_persists_window_without_breaking_` | 0 | ⬜ |
| `TEST-082.18` | 195–200 | بلوک | `test_worker_gates_access_behind_the_window` | 0 | ⬜ |
| `TEST-082.19` | 201–208 | بلوک | `test_worker_never_kills_itself_outside_the_window` | 0 | ⬜ |
| `TEST-082.20` | 209–215 | بلوک | `test_worker_still_drains_the_print_queue_outside_t` | 0 | ⬜ |
| `TEST-082.21` | 216–221 | بلوک | `test_queue_only_mode_never_touches_access` | 0 | ⬜ |
| `TEST-082.22` | 222–227 | بلوک | `test_worker_sweeps_once_after_every_restart` | 0 | ⬜ |
| `TEST-082.23` | 228–234 | بلوک | `test_worker_runs_a_final_sweep_when_the_window_clo` | 0 | ⬜ |
| `TEST-082.24` | 235–244 | بلوک | `test_auto_receipts_are_not_blocked_by_the_window` | 0 | ⬜ |
| `TEST-082.25` | 245–254 | بلوک | `test_worker_no_longer_reconnects_mysql_every_cycle` | 0 | ⬜ |
| `TEST-082.26` | 255–262 | بلوک | `test_worker_caches_settings_with_a_ttl` | 0 | ⬜ |
| `TEST-082.27` | 263–270 | بلوک | `test_odbc_handle_is_pooled_and_releasable` | 0 | ⬜ |
| `TEST-082.28` | 271–276 | بلوک | `test_pool_is_released_on_error_and_when_the_window` | 0 | ⬜ |
| `TEST-082.29` | 277–280 | بلوک | `test_pool_handle_expires_so_a_dead_connection_cann` | 0 | ⬜ |
| `TEST-082.30` | 281–287 | بلوک | `test_worker_has_three_distinct_poll_rhythms` | 0 | ⬜ |
| `TEST-082.31` | 288–296 | بلوک | `test_idle_sleep_is_chunked_so_max_minutes_still_wo` | 0 | ⬜ |
| `TEST-082.32` | 297–305 | بلوک | `test_holiday_board_markup_lives_in_one_place` | 0 | ⬜ |
| `TEST-082.33` | 306–312 | بلوک | `test_settings_page_has_a_holidays_tab` | 0 | ⬜ |
| `TEST-082.34` | 313–318 | بلوک | `test_holiday_tab_respects_the_view_permission` | 0 | ⬜ |
| `TEST-082.35` | 319–325 | بلوک | `test_holiday_actions_return_to_the_originating_tab` | 0 | ⬜ |
| `TEST-082.36` | 326–331 | بلوک | `test_every_holiday_form_carries_return_to` | 0 | ⬜ |
| `TEST-082.37` | 332–343 | بلوک | `test_year_board_select_attribute_is_not_broken` | 0 | ⬜ |
| `TEST-082.38` | 344–357 | بلوک | `test_panel_exposes_window_fields` | 0 | ⬜ |
| `TEST-082.39` | 358–368 | بلوک | `test_week_days_render_on_one_row_not_stacked` | 0 | ⬜ |
| `TEST-082.40` | 369–377 | بلوک | `test_every_week_day_is_still_rendered` | 0 | ⬜ |
| `TEST-082.41` | 378–387 | بلوک | `test_six_window_fields_sit_in_two_rows_of_three` | 0 | ⬜ |
| `TEST-082.42` | 388–397 | بلوک | `test_time_inputs_are_forced_to_24_hour` | 0 | ⬜ |
| `TEST-082.43` | 398–402 | بلوک | `test_time_picker_icon_stays_attached_to_its_field` | 0 | ⬜ |
| `TEST-082.44` | 403–410 | بلوک | `test_database_time_value_is_trimmed_for_the_time_i` | 0 | ⬜ |
| `TEST-082.45` | 411–418 | بلوک | `test_panel_version_was_bumped_with_the_panel_marku` | 0 | ⬜ |
| `TEST-082.46` | 419–429 | بلوک | `test_help_guide_explains_which_package_to_install` | 1 | ⬜ |
| `TEST-082.47` | 430–436 | بلوک | `test_panel_sends_window_payload_on_save` | 0 | ⬜ |
| `TEST-082.48` | 437–441 | بلوک | `test_panel_reads_window_state_from_the_api` | 0 | ⬜ |
| `TEST-082.49` | 442–446 | بلوک | `test_panel_warns_when_the_migration_has_not_run` | 0 | ⬜ |
| `TEST-082.50` | 447–452 | بلوک | `test_window_api_fields_require_the_db_edit_permiss` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-083</b> — <code>tests/test_fresh_install_schema_parity.py</code> (315 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-083.01` | 1–30 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-083.02` | 31–39 | بلوک | `_migration_files` | 0 | ⬜ |
| `TEST-083.03` | 40–46 | بلوک | `_strip_sql_comments` | 0 | ⬜ |
| `TEST-083.04` | 47–50 | بلوک | `_schema_text` | 0 | ⬜ |
| `TEST-083.05` | 51–59 | بلوک | `_created_tables` | 0 | ⬜ |
| `TEST-083.06` | 60–93 | بلوک | `_table_bodies` | 0 | ⬜ |
| `TEST-083.07` | 94–115 | بلوک | `_added_columns` | 0 | ⬜ |
| `TEST-083.08` | 116–139 | بلوک | `_added_indexes` | 0 | ⬜ |
| `TEST-083.09` | 140–145 | بلوک | `_migration_corpus` | 0 | ⬜ |
| `TEST-083.10` | 146–147 | بلوک | `-` | 0 | ⬜ |
| `TEST-083.11` | 148–150 | بلوک | `-` | 0 | ⬜ |
| `TEST-083.12` | 151–155 | بلوک | `test_migration_files_exist` | 0 | ⬜ |
| `TEST-083.13` | 156–167 | بلوک | `test_every_migration_table_exists_in_both_schema_f` | 0 | ⬜ |
| `TEST-083.14` | 168–187 | بلوک | `test_every_migration_column_exists_in_both_schema_` | 0 | ⬜ |
| `TEST-083.15` | 188–206 | بلوک | `test_every_migration_index_exists_in_both_schema_f` | 0 | ⬜ |
| `TEST-083.16` | 207–215 | بلوک | `test_both_schema_files_define_the_same_tables` | 0 | ⬜ |
| `TEST-083.17` | 216–237 | بلوک | `test_both_schema_files_define_identical_table_bodi` | 0 | ⬜ |
| `TEST-083.18` | 238–266 | بلوک | `test_schema_executes_in_order_without_forward_fore` | 0 | ⬜ |
| `TEST-083.19` | 267–305 | بلوک | `test_both_schema_files_seed_the_same_structural_ro` | 1 | ⬜ |
| `TEST-083.20` | 306–315 | بلوک | `test_full_rebuild_drops_every_table_in_both_schema` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-084</b> — <code>tests/test_global_theme_mode.py</code> (63 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-084.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-084.02` | 6–26 | بلوک | `test_global_theme_is_attached_to_all_main_shells` | 1 | ⬜ |
| `TEST-084.03` | 27–46 | بلوک | `test_food_panel_and_standalone_modules_follow_the_` | 0 | ⬜ |
| `TEST-084.04` | 47–63 | بلوک | `test_independent_php_renderers_use_shared_mode` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-085</b> — <code>tests/test_group_ticket_design_repair_static.py</code> (39 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-085.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-085.02` | 6–14 | بلوک | `test_group_design_is_saved_and_read_back` | 0 | ⬜ |
| `TEST-085.03` | 15–25 | بلوک | `test_group_preview_and_physical_print_share_group_` | 0 | ⬜ |
| `TEST-085.04` | 26–39 | بلوک | `test_main_page_routes_group_save_to_integrated_api` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-086</b> — <code>tests/test_hot_schema_guard_unit.php</code> (66 خط، 18 آدرس، بازبینی‌شده 0/18)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-086.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-086.02` | 4–4 | ناحیه | `Offline behavioural tests: no real configuration, ` | 0 | ⬜ |
| `TEST-086.03` | 5–5 | ناحیه | `تابع ticket_hot_schema_ensure() از متن فایل بیرون ` | 0 | ⬜ |
| `TEST-086.04` | 6–6 | ناحیه | `ساختگی اجرا می‌شود؛ هدف این است که «ترتیب $ready ن` | 0 | ⬜ |
| `TEST-086.05` | 7–10 | ناحیه | `واقعی سنجیده شود، نه با خواندن متن.` | 0 | ⬜ |
| `TEST-086.06` | 11–15 | تابع | `check_hot` | 0 | ⬜ |
| `TEST-086.07` | 16–21 | ناحیه | `بلوک رویه‌ای 16-21` | 0 | ⬜ |
| `TEST-086.08` | 22–22 | تابع | `db_in_transaction` | 0 | ⬜ |
| `TEST-086.09` | 23–23 | تابع | `db_add_missing_column` | 0 | ⬜ |
| `TEST-086.10` | 24–24 | تابع | `db_ensure_enum_values` | 0 | ⬜ |
| `TEST-086.11` | 25–25 | تابع | `system_log` | 0 | ⬜ |
| `TEST-086.12` | 26–26 | تابع | `register_shutdown_function_stub` | 0 | ⬜ |
| `TEST-086.13` | 28–32 | ناحیه | `تابع هدف را از فایل واقعی بیرون می‌کشیم تا نسخهٔ د` | 0 | ⬜ |
| `TEST-086.14` | 33–36 | ناحیه | `فقط ثبت shutdown به نسخهٔ قابل‌مشاهده تغییر می‌کند` | 4 | ⬜ |
| `TEST-086.15` | 37–44 | ناحیه | `۱) داخل تراکنش: هیچ DDL نباید اجرا شود` | 0 | ⬜ |
| `TEST-086.16` | 45–50 | ناحیه | `۲) فراخوانی دوبارهٔ داخل تراکنش: ثبت تکراری ممنوع` | 0 | ⬜ |
| `TEST-086.17` | 51–58 | ناحیه | `۳) دام اصلی: پس از پایان تراکنش مهاجرت باید واقعاً` | 0 | ⬜ |
| `TEST-086.18` | 59–66 | ناحیه | `۴) پس از موفقیت، دوباره اجرا نمی‌شود` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-087</b> — <code>tests/test_hot_schema_transaction_guard_static.py</code> (174 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-087.01` | 1–45 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-087.02` | 46–50 | بلوک | `read` | 0 | ⬜ |
| `TEST-087.03` | 51–57 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-087.04` | 58–67 | بلوک | `php_function` | 0 | ⬜ |
| `TEST-087.05` | 68–71 | بلوک | `hot_body` | 0 | ⬜ |
| `TEST-087.06` | 72–79 | بلوک | `test_hot_schema_checks_for_an_open_transaction` | 0 | ⬜ |
| `TEST-087.07` | 80–93 | بلوک | `test_transaction_check_comes_before_any_ddl` | 0 | ⬜ |
| `TEST-087.08` | 94–111 | بلوک | `test_ready_flag_is_not_set_while_deferring` | 0 | ⬜ |
| `TEST-087.09` | 112–119 | بلوک | `test_deferred_migration_is_scheduled` | 0 | ⬜ |
| `TEST-087.10` | 120–128 | بلوک | `test_shutdown_handler_is_registered_only_once` | 0 | ⬜ |
| `TEST-087.11` | 129–135 | بلوک | `test_deferral_is_logged` | 0 | ⬜ |
| `TEST-087.12` | 136–148 | بلوک | `test_existing_guard_is_not_regressed` | 0 | ⬜ |
| `TEST-087.13` | 149–159 | بلوک | `test_guarded_helper_stays_free_of_static_state` | 0 | ⬜ |
| `TEST-087.14` | 160–174 | بلوک | `test_bootstrap_still_runs_migration_outside_any_tr` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-088</b> — <code>tests/test_inventory_truth_static.py</code> (198 خط، 18 آدرس، بازبینی‌شده 0/18)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-088.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-088.02` | 9–12 | بلوک | `read` | 0 | ⬜ |
| `TEST-088.03` | 13–13 | بلوک | `InventoryTruthStaticTests` | 0 | ⬜ |
| `TEST-088.04` | 14–25 | بلوک | `test_login_identity_does_not_claim_hardware_collec` | 1 | ⬜ |
| `TEST-088.05` | 26–32 | بلوک | `test_agent_api_rejects_payload_without_hardware_ro` | 0 | ⬜ |
| `TEST-088.06` | 33–39 | بلوک | `test_domain_scan_does_not_save_identity_only_respo` | 0 | ⬜ |
| `TEST-088.07` | 40–46 | بلوک | `test_diagnostics_report_hardware_score_and_local_a` | 0 | ⬜ |
| `TEST-088.08` | 47–63 | بلوک | `test_local_extraction_button_uses_php_host_and_onl` | 0 | ⬜ |
| `TEST-088.09` | 64–70 | بلوک | `test_winrm_only_targets_are_detected_when_icmp_is_` | 0 | ⬜ |
| `TEST-088.10` | 71–85 | بلوک | `test_one_host_credential_test_does_not_write_inven` | 0 | ⬜ |
| `TEST-088.11` | 86–93 | بلوک | `test_winrm_guidance_configures_targets_not_only_th` | 0 | ⬜ |
| `TEST-088.12` | 94–110 | بلوک | `test_scan_credentials_are_per_run_encrypted_and_no` | 0 | ⬜ |
| `TEST-088.13` | 111–120 | بلوک | `test_full_domain_scan_is_not_limited_to_a_single_h` | 0 | ⬜ |
| `TEST-088.14` | 121–137 | بلوک | `test_extended_windows_inventory_is_collected_and_p` | 0 | ⬜ |
| `TEST-088.15` | 138–147 | بلوک | `test_domain_reinventory_refreshes_only_machine_der` | 0 | ⬜ |
| `TEST-088.16` | 148–157 | بلوک | `test_internet_access_is_not_inferred_from_a_defaul` | 0 | ⬜ |
| `TEST-088.17` | 158–185 | بلوک | `test_successful_empty_categories_clear_but_failed_` | 0 | ⬜ |
| `TEST-088.18` | 186–198 | بلوک | `test_food_panel_and_ldap_slow_paths_emit_stage_tim` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-089</b> — <code>tests/test_ldap_secret_and_legacy_status_static.py</code> (213 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-089.01` | 1–50 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-089.02` | 51–55 | بلوک | `read` | 0 | ⬜ |
| `TEST-089.03` | 56–62 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-089.04` | 63–72 | بلوک | `php_function` | 0 | ⬜ |
| `TEST-089.05` | 73–78 | بلوک | `test_ldap_secret_helpers_exist` | 0 | ⬜ |
| `TEST-089.06` | 79–89 | بلوک | `test_settings_action_never_saves_the_raw_password` | 0 | ⬜ |
| `TEST-089.07` | 90–98 | بلوک | `test_single_read_point_decrypts` | 0 | ⬜ |
| `TEST-089.08` | 99–109 | بلوک | `test_encryption_reuses_the_repository_pattern` | 0 | ⬜ |
| `TEST-089.09` | 110–122 | بلوک | `test_missing_app_key_keeps_todays_behaviour` | 0 | ⬜ |
| `TEST-089.10` | 123–130 | بلوک | `test_legacy_plaintext_values_still_readable` | 0 | ⬜ |
| `TEST-089.11` | 131–139 | بلوک | `test_failed_decryption_fails_closed` | 0 | ⬜ |
| `TEST-089.12` | 140–160 | بلوک | `test_envelope_layout_offsets_are_consistent` | 0 | ⬜ |
| `TEST-089.13` | 161–171 | بلوک | `test_failed_decryption_is_logged` | 0 | ⬜ |
| `TEST-089.14` | 172–182 | بلوک | `transition_map` | 0 | ⬜ |
| `TEST-089.15` | 183–193 | بلوک | `test_nothing_transitions_into_pending` | 0 | ⬜ |
| `TEST-089.16` | 194–205 | بلوک | `test_pending_always_has_a_way_out` | 0 | ⬜ |
| `TEST-089.17` | 206–213 | بلوک | `test_pending_is_documented_as_legacy` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-090</b> — <code>tests/test_ldap_secret_unit.php</code> (54 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-090.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-090.02` | 4–4 | ناحیه | `Offline behavioural tests: no real configuration, ` | 0 | ⬜ |
| `TEST-090.03` | 5–5 | ناحیه | `توجه: در این محیط افزونهٔ openssl در دسترس نیست، پ` | 0 | ⬜ |
| `TEST-090.04` | 6–6 | ناحیه | `مسیر «کلید موجود نیست» را می‌رود. دقیقاً همان مسیر` | 0 | ⬜ |
| `TEST-090.05` | 7–10 | ناحیه | `app.key اجرا می‌شود و شکستنش یعنی از کار افتادن ور` | 0 | ⬜ |
| `TEST-090.06` | 11–15 | تابع | `check_ldap` | 0 | ⬜ |
| `TEST-090.07` | 16–17 | ناحیه | `بلوک رویه‌ای 16-17` | 0 | ⬜ |
| `TEST-090.08` | 18–18 | تابع | `cfg` | 0 | ⬜ |
| `TEST-090.09` | 19–19 | ناحیه | `بلوک رویه‌ای 19-19` | 0 | ⬜ |
| `TEST-090.10` | 20–20 | تابع | `system_log` | 0 | ⬜ |
| `TEST-090.11` | 21–21 | تابع | `setting` | 0 | ⬜ |
| `TEST-090.12` | 22–24 | ناحیه | `بلوک رویه‌ای 22-24` | 0 | ⬜ |
| `TEST-090.13` | 25–29 | ناحیه | `سازگاری رو به عقب: نصبی که app.key ندارد` | 0 | ⬜ |
| `TEST-090.14` | 30–35 | ناحیه | `مقدار قدیمیِ متن ساده باید همچنان خوانده شود` | 0 | ⬜ |
| `TEST-090.15` | 36–43 | ناحیه | `شکست بسته: پاکت غیرقابل رمزگشایی هرگز به سرور دامی` | 0 | ⬜ |
| `TEST-090.16` | 44–54 | ناحیه | `نقطهٔ خواندن: ldap_cfg باید از همین مسیر رد شود` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-091</b> — <code>tests/test_logging_acceptance_harness_static.py</code> (39 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-091.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-091.02` | 6–14 | بلوک | `test_ci_provisions_disposable_mariadb_and_windows_` | 0 | ⬜ |
| `TEST-091.03` | 15–24 | بلوک | `test_local_database_harness_is_ephemeral_and_clean` | 0 | ⬜ |
| `TEST-091.04` | 25–33 | بلوک | `test_windows_acl_harness_has_positive_negative_and` | 0 | ⬜ |
| `TEST-091.05` | 34–39 | بلوک | `test_acceptance_docs_do_not_equate_ci_with_target_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-092</b> — <code>tests/test_logging_architecture_static.py</code> (53 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-092.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-092.02` | 6–13 | بلوک | `test_central_core_is_loaded_before_legacy_logging_` | 0 | ⬜ |
| `TEST-092.03` | 14–21 | بلوک | `test_database_migration_adds_correlation_to_all_ev` | 0 | ⬜ |
| `TEST-092.04` | 22–34 | بلوک | `test_spool_worker_is_cli_only_and_replay_is_idempo` | 1 | ⬜ |
| `TEST-092.05` | 35–43 | بلوک | `test_request_timeline_combines_system_activity_and` | 0 | ⬜ |
| `TEST-092.06` | 44–53 | بلوک | `test_untrusted_forwarded_headers_are_not_used_as_c` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-093</b> — <code>tests/test_logging_audit_migration_registry.php</code> (72 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-093.01` | 1–9 | ناحیه | `بلوک رویه‌ای 1-9` | 0 | ⬜ |
| `TEST-093.02` | 10–13 | تابع | `db_table_exists` | 0 | ⬜ |
| `TEST-093.03` | 15–21 | کلاس | `AuditMigrationStatement` | 0 | ⬜ |
| `TEST-093.04` | 22–32 | کلاس | `AuditMigrationDatabase` | 5 | ⬜ |
| `TEST-093.05` | 33–37 | تابع | `db` | 0 | ⬜ |
| `TEST-093.06` | 38–40 | ناحیه | `بلوک رویه‌ای 38-40` | 0 | ⬜ |
| `TEST-093.07` | 41–48 | تابع | `audit_assert` | 0 | ⬜ |
| `TEST-093.08` | 49–72 | ناحیه | `بلوک رویه‌ای 49-72` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-094</b> — <code>tests/test_logging_audit_outage_spool.php</code> (40 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-094.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-094.02` | 6–6 | تابع | `cfg` | 0 | ⬜ |
| `TEST-094.03` | 7–7 | تابع | `db` | 0 | ⬜ |
| `TEST-094.04` | 8–40 | ناحیه | `بلوک رویه‌ای 8-40` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-095</b> — <code>tests/test_logging_audit_regressions.php</code> (48 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-095.01` | 1–48 | ناحیه | `بلوک رویه‌ای 1-48` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-096</b> — <code>tests/test_logging_audit_required_transaction.php</code> (87 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-096.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-096.02` | 6–17 | کلاس | `AtomicAuditPDO` | 0 | ⬜ |
| `TEST-096.03` | 18–30 | کلاس | `AtomicAuditStatement` | 1 | ⬜ |
| `TEST-096.04` | 31–33 | ناحیه | `بلوک رویه‌ای 31-33` | 0 | ⬜ |
| `TEST-096.05` | 34–34 | تابع | `db` | 0 | ⬜ |
| `TEST-096.06` | 35–87 | ناحیه | `بلوک رویه‌ای 35-87` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-097</b> — <code>tests/test_logging_audit_static.py</code> (271 خط، 33 آدرس، بازبینی‌شده 0/33)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-097.01` | 1–23 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-097.02` | 24–27 | بلوک | `read` | 0 | ⬜ |
| `TEST-097.03` | 28–29 | بلوک | `-` | 0 | ⬜ |
| `TEST-097.04` | 30–32 | بلوک | `-` | 0 | ⬜ |
| `TEST-097.05` | 33–39 | بلوک | `test_audit_module_exists_inside_the_logging_librar` | 0 | ⬜ |
| `TEST-097.06` | 40–47 | بلوک | `test_audit_records_through_the_standard_logging_en` | 0 | ⬜ |
| `TEST-097.07` | 48–59 | بلوک | `test_audit_feeds_the_existing_health_alert_channel` | 0 | ⬜ |
| `TEST-097.08` | 60–66 | بلوک | `test_health_cron_runs_the_audit` | 0 | ⬜ |
| `TEST-097.09` | 67–77 | بلوک | `test_audit_failure_is_never_silent` | 0 | ⬜ |
| `TEST-097.10` | 78–87 | بلوک | `test_no_parallel_audit_tool_remains` | 0 | ⬜ |
| `TEST-097.11` | 88–95 | بلوک | `test_cli_is_a_thin_wrapper_not_a_second_implementa` | 0 | ⬜ |
| `TEST-097.12` | 96–97 | بلوک | `-` | 0 | ⬜ |
| `TEST-097.13` | 98–100 | بلوک | `-` | 0 | ⬜ |
| `TEST-097.14` | 101–107 | بلوک | `test_audit_event_codes_are_registered_in_the_catal` | 0 | ⬜ |
| `TEST-097.15` | 108–117 | بلوک | `test_audit_catalog_entries_are_complete` | 0 | ⬜ |
| `TEST-097.16` | 118–128 | بلوک | `test_structural_problems_are_critical_and_regressi` | 0 | ⬜ |
| `TEST-097.17` | 129–134 | بلوک | `test_audit_codes_are_used_by_the_module` | 0 | ⬜ |
| `TEST-097.18` | 135–136 | بلوک | `-` | 0 | ⬜ |
| `TEST-097.19` | 137–139 | بلوک | `-` | 0 | ⬜ |
| `TEST-097.20` | 140–148 | بلوک | `test_structural_checks_cover_the_defect_that_went_` | 0 | ⬜ |
| `TEST-097.21` | 149–155 | بلوک | `test_checks_inspect_the_live_database_not_just_fil` | 0 | ⬜ |
| `TEST-097.22` | 156–165 | بلوک | `test_every_check_explains_why_it_matters` | 0 | ⬜ |
| `TEST-097.23` | 166–174 | بلوک | `test_structural_checks_have_no_budget` | 0 | ⬜ |
| `TEST-097.24` | 175–183 | بلوک | `test_baseline_lives_in_storage_not_in_the_reposito` | 0 | ⬜ |
| `TEST-097.25` | 184–191 | بلوک | `test_ci_runs_the_audit_without_a_database` | 0 | ⬜ |
| `TEST-097.26` | 192–193 | بلوک | `-` | 0 | ⬜ |
| `TEST-097.27` | 194–196 | بلوک | `-` | 0 | ⬜ |
| `TEST-097.28` | 197–211 | بلوک | `test_cli_check_mode_can_actually_fail` | 0 | ⬜ |
| `TEST-097.29` | 212–218 | بلوک | `test_summarize_is_shared_by_both_paths` | 0 | ⬜ |
| `TEST-097.30` | 219–227 | بلوک | `test_empty_baseline_is_not_a_silent_no_op` | 0 | ⬜ |
| `TEST-097.31` | 228–252 | بلوک | `test_audit_writes_its_own_heartbeat` | 0 | ⬜ |
| `TEST-097.32` | 253–261 | بلوک | `test_audit_detects_its_own_outage_after_recovery` | 0 | ⬜ |
| `TEST-097.33` | 262–271 | بلوک | `test_every_defined_check_is_registered` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-098</b> — <code>tests/test_logging_audit_transaction_status.php</code> (46 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-098.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-098.02` | 6–13 | کلاس | `AuditStatusPDO` | 0 | ⬜ |
| `TEST-098.03` | 14–18 | کلاس | `AuditStatusStatement` | 0 | ⬜ |
| `TEST-098.04` | 19–20 | ناحیه | `بلوک رویه‌ای 19-20` | 0 | ⬜ |
| `TEST-098.05` | 21–21 | تابع | `db` | 0 | ⬜ |
| `TEST-098.06` | 22–46 | ناحیه | `بلوک رویه‌ای 22-46` | **8** | ⬜ |

</details>

<details>
<summary><b>TEST-099</b> — <code>tests/test_logging_completion_static.py</code> (73 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-099.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-099.02` | 5–7 | بلوک | `read` | 0 | ⬜ |
| `TEST-099.03` | 8–14 | بلوک | `test_catalog_contains_user_admin_and_technical_gui` | 0 | ⬜ |
| `TEST-099.04` | 15–22 | بلوک | `test_issue_aggregator_is_integrated_and_idempotent` | 0 | ⬜ |
| `TEST-099.05` | 23–30 | بلوک | `test_runtime_logging_does_not_execute_ddl_for_log_` | 0 | ⬜ |
| `TEST-099.06` | 31–37 | بلوک | `test_health_dashboard_has_issue_filters_and_health` | 0 | ⬜ |
| `TEST-099.07` | 38–45 | بلوک | `test_windows_scheduled_jobs_and_retention_classes_` | 0 | ⬜ |
| `TEST-099.08` | 46–49 | بلوک | `test_program_version_is_user_requested_value` | 0 | ⬜ |
| `TEST-099.09` | 50–60 | بلوک | `test_alerts_are_rate_limited_and_delivered_to_prim` | 0 | ⬜ |
| `TEST-099.10` | 61–66 | بلوک | `test_user_facing_error_text_and_fatal_output_are_s` | 0 | ⬜ |
| `TEST-099.11` | 67–73 | بلوک | `test_replay_is_streamed_and_writer_lock_is_shared` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-100</b> — <code>tests/test_logging_core_offline.php</code> (95 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-100.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `TEST-100.02` | 8–14 | تابع | `cfg` | 0 | ⬜ |
| `TEST-100.03` | 15–95 | ناحیه | `بلوک رویه‌ای 15-95` | **8** | ⬜ |

</details>

<details>
<summary><b>TEST-101</b> — <code>tests/test_logging_correlation_pipeline.php</code> (90 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-101.01` | 1–8 | ناحیه | `بلوک رویه‌ای 1-8` | 0 | ⬜ |
| `TEST-101.02` | 9–16 | کلاس | `LoggingCorrelationFakeStatement` | 0 | ⬜ |
| `TEST-101.03` | 17–25 | کلاس | `LoggingCorrelationFakePDO` | 0 | ⬜ |
| `TEST-101.04` | 26–26 | ناحیه | `بلوک رویه‌ای 26-26` | 0 | ⬜ |
| `TEST-101.05` | 27–30 | تابع | `db` | 0 | ⬜ |
| `TEST-101.06` | 31–90 | ناحیه | `بلوک رویه‌ای 31-90` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-102</b> — <code>tests/test_logging_dashboard_offline.php</code> (24 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-102.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-102.02` | 4–4 | تابع | `e` | 0 | ⬜ |
| `TEST-102.03` | 5–5 | تابع | `db` | 0 | ⬜ |
| `TEST-102.04` | 6–6 | تابع | `logging_emergency_failure` | 0 | ⬜ |
| `TEST-102.05` | 7–7 | تابع | `logging_spool_stats` | 0 | ⬜ |
| `TEST-102.06` | 8–8 | تابع | `system_health_checks` | 0 | ⬜ |
| `TEST-102.07` | 9–24 | ناحیه | `بلوک رویه‌ای 9-24` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-103</b> — <code>tests/test_logging_dashboard_static.py</code> (34 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-103.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-103.02` | 10–16 | بلوک | `test_dashboard_is_permission_gated_and_routed` | 0 | ⬜ |
| `TEST-103.03` | 17–22 | بلوک | `test_dashboard_uses_real_logging_sources_and_local` | 0 | ⬜ |
| `TEST-103.04` | 23–30 | بلوک | `test_dashboard_handles_unknown_database_state_and_` | 0 | ⬜ |
| `TEST-103.05` | 31–34 | بلوک | `test_dashboard_auto_refresh_is_bounded_and_visibil` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-104</b> — <code>tests/test_logging_diagnostics_offline.php</code> (27 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-104.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-105</b> — <code>tests/test_logging_hardening_static.py</code> (67 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-105.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-105.02` | 6–16 | بلوک | `test_spool_stream_is_allowlisted_and_partial_write` | 0 | ⬜ |
| `TEST-105.03` | 17–29 | بلوک | `test_activity_normalization_keeps_safe_values_and_` | 0 | ⬜ |
| `TEST-105.04` | 30–43 | بلوک | `test_logging_transaction_contracts_are_preserved` | 0 | ⬜ |
| `TEST-105.05` | 44–52 | بلوک | `test_retention_and_migration_remain_conservative_a` | 0 | ⬜ |
| `TEST-105.06` | 53–57 | بلوک | `test_unknown_error_title_has_mbstring_fallback` | 0 | ⬜ |
| `TEST-105.07` | 58–67 | بلوک | `test_full_rebuild_drops_every_schema_table` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-106</b> — <code>tests/test_logging_integrity_chain.py</code> (138 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-106.01` | 1–16 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-106.02` | 17–32 | بلوک | `_write_snapshot` | 0 | ⬜ |
| `TEST-106.03` | 33–36 | بلوک | `_verify` | 0 | ⬜ |
| `TEST-106.04` | 37–44 | بلوک | `test_consecutive_daily_exports_verify_as_a_chain` | 0 | ⬜ |
| `TEST-106.05` | 45–53 | بلوک | `test_missing_previous_day_is_rejected` | 0 | ⬜ |
| `TEST-106.06` | 54–63 | بلوک | `test_tampered_intermediate_archive_breaks_the_enti` | 0 | ⬜ |
| `TEST-106.07` | 64–78 | بلوک | `test_legacy_format_one_archive_remains_verifiable` | 0 | ⬜ |
| `TEST-106.08` | 79–103 | بلوک | `_write_supplement` | 0 | ⬜ |
| `TEST-106.09` | 104–115 | بلوک | `test_late_arrival_supplements_verify_against_immut` | 0 | ⬜ |
| `TEST-106.10` | 116–128 | بلوک | `test_late_arrival_supplement_detects_missing_prede` | 4 | ⬜ |
| `TEST-106.11` | 129–138 | بلوک | `test_late_arrival_supplement_detects_tampering` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-107</b> — <code>tests/test_logging_integrity_late_arrival.php</code> (27 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-107.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-108</b> — <code>tests/test_logging_integrity_offline.php</code> (36 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-108.01` | 1–36 | ناحیه | `بلوک رویه‌ای 1-36` | 6 | ⬜ |

</details>

<details>
<summary><b>TEST-109</b> — <code>tests/test_logging_integrity_static.py</code> (33 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-109.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-109.02` | 8–19 | بلوک | `test_integrity_export_and_verification_contracts` | 0 | ⬜ |
| `TEST-109.03` | 20–27 | بلوک | `test_integrity_supplement_is_linked_to_base_and_or` | 0 | ⬜ |
| `TEST-109.04` | 28–33 | بلوک | `test_integrity_export_scheduled_job_reconciles_sev` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-110</b> — <code>tests/test_logging_issue_aggregator.php</code> (109 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-110.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `TEST-110.02` | 8–24 | کلاس | `LoggingTestPDO` | 4 | ⬜ |
| `TEST-110.03` | 26–36 | کلاس | `LoggingTestStatement` | 0 | ⬜ |
| `TEST-110.04` | 37–38 | ناحیه | `بلوک رویه‌ای 37-38` | 0 | ⬜ |
| `TEST-110.05` | 39–39 | تابع | `db` | 0 | ⬜ |
| `TEST-110.06` | 40–109 | ناحیه | `بلوک رویه‌ای 40-109` | **8** | ⬜ |

</details>

<details>
<summary><b>TEST-111</b> — <code>tests/test_logging_mysql_integration.php</code> (256 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-111.01` | 1–34 | ناحیه | `بلوک رویه‌ای 1-34` | 5 | ⬜ |
| `TEST-111.02` | 35–35 | تابع | `db` | 0 | ⬜ |
| `TEST-111.03` | 36–45 | تابع | `execute_migration_statement` | 0 | ⬜ |
| `TEST-111.04` | 46–116 | تابع | `split_sql_statements` | 0 | ⬜ |
| `TEST-111.05` | 117–134 | ناحیه | `بلوک رویه‌ای 117-134` | 0 | ⬜ |
| `TEST-111.06` | 135–213 | ناحیه | `Migration 025 must remain safe when the updater re` | **12** | ⬜ |
| `TEST-111.07` | 214–226 | ناحیه | `The replay worker atomically renames the source fi` | 1 | ⬜ |
| `TEST-111.08` | 227–256 | ناحیه | `Verify migration 025 changes the live notification` | **13** | ⬜ |

</details>

<details>
<summary><b>TEST-112</b> — <code>tests/test_logging_phase56_static.py</code> (30 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-112.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-112.02` | 3–5 | بلوک | `test_diagnostics_engine_is_loaded_after_logging_co` | 0 | ⬜ |
| `TEST-112.03` | 6–11 | بلوک | `test_diagnostic_engine_has_stable_rules_and_confid` | 0 | ⬜ |
| `TEST-112.04` | 12–18 | بلوک | `test_agent_bundle_is_cli_only_and_redacted` | 0 | ⬜ |
| `TEST-112.05` | 19–24 | بلوک | `test_release_preflight_is_read_only_and_reports_de` | 1 | ⬜ |
| `TEST-112.06` | 25–30 | بلوک | `test_health_dashboard_displays_diagnostic_advice` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-113</b> — <code>tests/test_logging_phase73_regressions.php</code> (34 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-113.01` | 1–34 | ناحیه | `بلوک رویه‌ای 1-34` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-114</b> — <code>tests/test_logging_phase74_features.php</code> (31 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-114.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `TEST-114.02` | 7–7 | تابع | `cfg` | 0 | ⬜ |
| `TEST-114.03` | 8–31 | ناحیه | `بلوک رویه‌ای 8-31` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-115</b> — <code>tests/test_logging_phase74_package.py</code> (19 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-115.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-115.02` | 5–7 | بلوک | `read` | 0 | ⬜ |
| `TEST-115.03` | 8–19 | بلوک | `test_full_source_package_is_complete_and_versioned` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-116</b> — <code>tests/test_logging_phase76_static.py</code> (74 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-116.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-116.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-116.03` | 10–18 | بلوک | `test_required_audit_derives_module_and_target_cont` | 0 | ⬜ |
| `TEST-116.04` | 19–29 | بلوک | `test_holiday_mutations_are_audited_in_the_same_tra` | 1 | ⬜ |
| `TEST-116.05` | 30–38 | بلوک | `test_cddvd_governance_ldap_and_profile_events_have` | 0 | ⬜ |
| `TEST-116.06` | 39–45 | بلوک | `test_metadata_allowlists_preserve_operational_cont` | 0 | ⬜ |
| `TEST-116.07` | 46–52 | بلوک | `test_admin_category_no_longer_uses_null_module_as_` | 0 | ⬜ |
| `TEST-116.08` | 53–62 | بلوک | `test_ticket_timeline_supports_search_and_safe_deta` | 2 | ⬜ |
| `TEST-116.09` | 63–69 | بلوک | `test_circuit_open_does_not_spool_success_audit_out` | 0 | ⬜ |
| `TEST-116.10` | 70–74 | بلوک | `test_new_event_codes_have_catalog_definitions` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-117</b> — <code>tests/test_logging_replay_attempts.php</code> (101 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-117.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `TEST-117.02` | 7–14 | کلاس | `ReplayAttemptsPDO` | 0 | ⬜ |
| `TEST-117.03` | 15–32 | کلاس | `ReplayAttemptsStatement` | 1 | ⬜ |
| `TEST-117.04` | 33–33 | ناحیه | `بلوک رویه‌ای 33-33` | 0 | ⬜ |
| `TEST-117.05` | 34–34 | تابع | `db` | 0 | ⬜ |
| `TEST-117.06` | 35–56 | ناحیه | `بلوک رویه‌ای 35-56` | 0 | ⬜ |
| `TEST-117.07` | 57–62 | ناحیه | `Seven additional real permanent-data failures reac` | 0 | ⬜ |
| `TEST-117.08` | 63–73 | ناحیه | `Unparseable JSON cannot carry an attempts field; i` | 0 | ⬜ |
| `TEST-117.09` | 74–101 | ناحیه | `Schema errors must stay active and must not consum` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-118</b> — <code>tests/test_logging_replay_offline.php</code> (180 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-118.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `TEST-118.02` | 8–23 | کلاس | `ReplayFakePDO` | 0 | ⬜ |
| `TEST-118.03` | 24–64 | کلاس | `ReplayFakeStatement` | 1 | ⬜ |
| `TEST-118.04` | 65–65 | ناحیه | `بلوک رویه‌ای 65-65` | 0 | ⬜ |
| `TEST-118.05` | 66–66 | تابع | `db` | 0 | ⬜ |
| `TEST-118.06` | 67–98 | ناحیه | `بلوک رویه‌ای 67-98` | 1 | ⬜ |
| `TEST-118.07` | 99–112 | ناحیه | `رکورد poison با هفت retry قبلی باید به Dead Letter` | 1 | ⬜ |
| `TEST-118.08` | 113–128 | ناحیه | `قطع دیتابیس در Worker باید رکورد را در retry نگه د` | 5 | ⬜ |
| `TEST-118.09` | 129–149 | ناحیه | `خط معتبر با خطای DB حتی پس از بیش از ۸ تلاش نباید ` | 7 | ⬜ |
| `TEST-118.10` | 150–158 | ناحیه | `فایل .processing باقی‌مانده از Crash بدون نیاز به ` | 0 | ⬜ |
| `TEST-118.11` | 159–180 | ناحیه | `حین پردازش فایل .processing، writer باید بتواند فا` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-119</b> — <code>tests/test_logging_sanitizers.php</code> (36 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-119.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `TEST-119.02` | 7–7 | تابع | `cfg` | 0 | ⬜ |
| `TEST-119.03` | 8–36 | ناحیه | `بلوک رویه‌ای 8-36` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-120</b> — <code>tests/test_logging_spool_capacity.php</code> (29 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-120.01` | 1–29 | ناحیه | `بلوک رویه‌ای 1-29` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-121</b> — <code>tests/test_logging_spool_concurrency.php</code> (48 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-121.01` | 1–48 | ناحیه | `بلوک رویه‌ای 1-48` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-122</b> — <code>tests/test_logging_system_contract.py</code> (275 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-122.01` | 1–90 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-122.02` | 91–94 | بلوک | `read` | 0 | ⬜ |
| `TEST-122.03` | 95–101 | بلوک | `app_php_files` | 0 | ⬜ |
| `TEST-122.04` | 102–108 | بلوک | `all_php_files` | 0 | ⬜ |
| `TEST-122.05` | 109–116 | بلوک | `test_every_logging_runtime_file_is_present` | 0 | ⬜ |
| `TEST-122.06` | 117–139 | بلوک | `test_logging_migrations_exist_and_numbers_are_uniq` | 0 | ⬜ |
| `TEST-122.07` | 140–144 | بلوک | `test_bootstrap_loads_the_logging_core_before_its_d` | 0 | ⬜ |
| `TEST-122.08` | 145–162 | بلوک | `require_line` | 0 | ⬜ |
| `TEST-122.09` | 163–175 | بلوک | `test_no_php_file_requires_a_file_that_does_not_exi` | 0 | ⬜ |
| `TEST-122.10` | 176–199 | بلوک | `test_every_logging_function_called_is_defined_or_g` | 0 | ⬜ |
| `TEST-122.11` | 200–211 | بلوک | `test_security_log_access_denied_is_available_where` | 0 | ⬜ |
| `TEST-122.12` | 212–222 | بلوک | `test_logging_update_must_not_delete_1_62_75_featur` | 0 | ⬜ |
| `TEST-122.13` | 223–229 | بلوک | `test_logging_dashboard_is_reachable_from_the_repor` | 0 | ⬜ |
| `TEST-122.14` | 230–243 | بلوک | `test_columns_written_by_the_logging_core_exist_aft` | 0 | ⬜ |
| `TEST-122.15` | 244–275 | بلوک | `schema_columns` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-123</b> — <code>tests/test_logging_ticket_event_replay.php</code> (72 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-123.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `TEST-123.02` | 8–19 | کلاس | `TicketReplayFakePDO` | 0 | ⬜ |
| `TEST-123.03` | 20–38 | کلاس | `TicketReplayFakeStatement` | 1 | ⬜ |
| `TEST-123.04` | 39–39 | ناحیه | `بلوک رویه‌ای 39-39` | 0 | ⬜ |
| `TEST-123.05` | 40–40 | تابع | `db` | 0 | ⬜ |
| `TEST-123.06` | 41–72 | ناحیه | `بلوک رویه‌ای 41-72` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-124</b> — <code>tests/test_logging_trace_coverage.php</code> (50 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-124.01` | 1–50 | ناحیه | `بلوک رویه‌ای 1-50` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-125</b> — <code>tests/test_manual_no_print_food_ticket_static.py</code> (131 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-125.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-125.02` | 14–24 | بلوک | `test_wrong_emergency_implementation_is_removed_fro` | 0 | ⬜ |
| `TEST-125.03` | 25–131 | بلوک | `test_existing_settings_manual_attendance_bug_fix_p` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-126</b> — <code>tests/test_mariadb_fresh_install.php</code> (134 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-126.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `TEST-126.02` | 21–35 | ناحیه | `مسیر: fresh-install\|migrations-applied` | 0 | ⬜ |
| `TEST-126.03` | 36–46 | تابع | `check` | 0 | ⬜ |
| `TEST-126.04` | 47–73 | ناحیه | `بلوک رویه‌ای 47-73` | 4 | ⬜ |
| `TEST-126.05` | 74–98 | ناحیه | `مسیر: fresh-install` | 5 | ⬜ |
| `TEST-126.06` | 99–134 | ناحیه | `مسیر: migrations-applied` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-127</b> — <code>tests/test_migration_registry_seed.py</code> (40 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-127.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-127.02` | 14–40 | بلوک | `test_fresh_install_schemas_seed_every_forward_migr` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-128</b> — <code>tests/test_migration_retention_1_62_76.py</code> (29 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-128.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-128.02` | 16–20 | بلوک | `test_historical_logging_migrations_are_retained_in` | 0 | ⬜ |
| `TEST-128.03` | 21–29 | بلوک | `test_update_migration_policy_is_explicit_and_non_e` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-129</b> — <code>tests/test_no_duplicate_php_functions.py</code> (73 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-129.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-129.02` | 25–25 | بلوک | `NoDuplicatePhpFunctionsTest` | 0 | ⬜ |
| `TEST-129.03` | 26–41 | بلوک | `test_no_duplicate_function_declarations` | 0 | ⬜ |
| `TEST-129.04` | 42–50 | بلوک | `test_identity_file_has_redeclare_guard` | 0 | ⬜ |
| `TEST-129.05` | 51–59 | بلوک | `test_asset_profile_has_reload_guard` | 0 | ⬜ |
| `TEST-129.06` | 60–73 | بلوک | `test_index_uses_require_once_for_sensitive_modules` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-130</b> — <code>tests/test_org_chart_browser.js</code> (59 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-130.01` | 1–31 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-130.02` | 32–36 | بلوک | `verifyTop` | 0 | ⬜ |
| `TEST-130.03` | 37–49 | بلوک | `verifyBottom` | 0 | ⬜ |
| `TEST-130.04` | 50–59 | بلوک | `verifyEdge` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-131</b> — <code>tests/test_org_chart_experts_static.py</code> (147 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-131.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-131.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-131.03` | 12–147 | بلوک | `check` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-132</b> — <code>tests/test_org_chart_five_fixes_static.py</code> (105 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-132.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-132.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-132.03` | 10–19 | بلوک | `test_space_drag_is_local_and_uses_immediate_scroll` | 0 | ⬜ |
| `TEST-132.04` | 20–25 | بلوک | `test_empty_expert_cards_are_not_rendered_by_defaul` | 0 | ⬜ |
| `TEST-132.05` | 26–39 | بلوک | `test_role_repairs_and_routing_diagnostics_live_in_` | 0 | ⬜ |
| `TEST-132.06` | 40–50 | بلوک | `test_each_top_level_branch_has_an_inherited_distin` | 0 | ⬜ |
| `TEST-132.07` | 51–60 | بلوک | `test_add_submission_is_locked_in_ui_and_replay_tok` | 0 | ⬜ |
| `TEST-132.08` | 61–77 | بلوک | `test_exports_cover_full_chart_and_include_print_pr` | 0 | ⬜ |
| `TEST-132.09` | 78–87 | بلوک | `test_server_actions_refresh_chart_data_without_rel` | 0 | ⬜ |
| `TEST-132.10` | 88–96 | بلوک | `test_ceo_peer_units_accept_and_display_experts_ins` | 0 | ⬜ |
| `TEST-132.11` | 97–105 | بلوک | `test_space_repeats_are_prevented_and_wires_use_mea` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-133</b> — <code>tests/test_org_chart_user_report_fixes_static.py</code> (63 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-133.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-133.02` | 5–7 | بلوک | `read` | 0 | ⬜ |
| `TEST-133.03` | 8–18 | بلوک | `test_print_fits_the_complete_tree_and_hides_screen` | 0 | ⬜ |
| `TEST-133.04` | 19–25 | بلوک | `test_search_matches_units_managers_and_experts_the` | 0 | ⬜ |
| `TEST-133.05` | 26–34 | بلوک | `test_toolbar_is_outside_the_horizontal_scroller_an` | 0 | ⬜ |
| `TEST-133.06` | 35–41 | بلوک | `test_company_switch_uses_csp_safe_change_handler_a` | 0 | ⬜ |
| `TEST-133.07` | 42–49 | بلوک | `test_restore_input_stays_hidden_until_restore_butt` | 0 | ⬜ |
| `TEST-133.08` | 50–63 | بلوک | `test_action_request_has_explicit_company_url_and_e` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-134</b> — <code>tests/test_org_company_separation_static.py</code> (104 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-134.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-134.02` | 5–7 | بلوک | `read` | 0 | ⬜ |
| `TEST-134.03` | 8–18 | بلوک | `test_company_migration_is_separate_from_page_load_` | 0 | ⬜ |
| `TEST-134.04` | 19–35 | بلوک | `test_org_chart_is_filtered_to_selected_company_and` | 0 | ⬜ |
| `TEST-134.05` | 36–53 | بلوک | `test_company_status_is_derived_from_tree_membershi` | 1 | ⬜ |
| `TEST-134.06` | 54–68 | بلوک | `test_primary_admin_can_edit_display_only_company_n` | 1 | ⬜ |
| `TEST-134.07` | 69–77 | بلوک | `test_manager_positions_map_to_existing_global_mana` | 0 | ⬜ |
| `TEST-134.08` | 78–91 | بلوک | `test_backup_restore_and_reset_are_scoped_to_one_co` | 1 | ⬜ |
| `TEST-134.09` | 92–104 | بلوک | `test_shared_service_department_model_is_not_compan` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-135</b> — <code>tests/test_org_latest_chart_requests_static.py</code> (76 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-135.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-135.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-135.03` | 10–17 | بلوک | `test_export_menu_stacks_above_search_toolbar` | 0 | ⬜ |
| `TEST-135.04` | 18–33 | بلوک | `test_pdf_print_uses_full_chart_image_and_single_la` | 0 | ⬜ |
| `TEST-135.05` | 34–45 | بلوک | `test_deputy_and_department_cards_have_only_request` | 0 | ⬜ |
| `TEST-135.06` | 46–51 | بلوک | `test_partner_company_tree_suppresses_independent_u` | 0 | ⬜ |
| `TEST-135.07` | 52–76 | بلوک | `test_fixed_company_labels_show_in_selector_and_roo` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-136</b> — <code>tests/test_org_root_sort_static.py</code> (20 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-136.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-136.02` | 7–20 | بلوک | `test_root_sort_includes_management_and_company_and` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-137</b> — <code>tests/test_org_root_sort_unit.php</code> (37 خط، 24 آدرس، بازبینی‌شده 0/24)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-137.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-137.02` | 4–4 | کلاس | `DoneRedirect` | 0 | ⬜ |
| `TEST-137.03` | 5–9 | کلاس | `SortStmt` | 1 | ⬜ |
| `TEST-137.04` | 10–17 | کلاس | `SortDB` | 0 | ⬜ |
| `TEST-137.05` | 18–18 | تابع | `db` | 0 | ⬜ |
| `TEST-137.06` | 19–19 | تابع | `org_has_company_schema` | 0 | ⬜ |
| `TEST-137.07` | 19–19 | تابع | `org_company_context_id` | 0 | ⬜ |
| `TEST-137.08` | 20–20 | تابع | `org_company_root_title` | 2 | ⬜ |
| `TEST-137.09` | 20–20 | تابع | `post_value` | 2 | ⬜ |
| `TEST-137.10` | 21–21 | تابع | `org_unit_by_id` | 0 | ⬜ |
| `TEST-137.11` | 22–22 | تابع | `require_permission` | 0 | ⬜ |
| `TEST-137.12` | 22–22 | تابع | `user_can_manage_organization` | 0 | ⬜ |
| `TEST-137.13` | 23–23 | تابع | `org_has_sort_order` | 0 | ⬜ |
| `TEST-137.14` | 23–23 | تابع | `org_ceo_unit_id` | 0 | ⬜ |
| `TEST-137.15` | 24–24 | تابع | `org_snapshot` | 0 | ⬜ |
| `TEST-137.16` | 24–24 | تابع | `org_record_undo` | 0 | ⬜ |
| `TEST-137.17` | 24–24 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-137.18` | 24–24 | تابع | `save_audit_required` | 0 | ⬜ |
| `TEST-137.19` | 24–24 | تابع | `flash` | 0 | ⬜ |
| `TEST-137.20` | 25–25 | تابع | `org_company_redirect_url` | 0 | ⬜ |
| `TEST-137.21` | 25–25 | تابع | `redirect` | 0 | ⬜ |
| `TEST-137.22` | 26–29 | تابع | `runSort` | 0 | ⬜ |
| `TEST-137.23` | 30–30 | تابع | `chk` | 0 | ⬜ |
| `TEST-137.24` | 31–37 | ناحیه | `بلوک رویه‌ای 31-37` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-138</b> — <code>tests/test_org_tree_dynamic_static.py</code> (79 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-138.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-138.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-138.03` | 11–19 | بلوک | `test_additive_sort_migration_is_not_run_on_page_lo` | 1 | ⬜ |
| `TEST-138.04` | 20–31 | بلوک | `test_recursive_tree_and_nested_department_validati` | 0 | ⬜ |
| `TEST-138.05` | 32–45 | بلوک | `test_undo_redo_is_delta_scoped_and_does_not_restor` | 0 | ⬜ |
| `TEST-138.06` | 46–57 | بلوک | `test_existing_data_fields_and_privacy_boundary_are` | 0 | ⬜ |
| `TEST-138.07` | 58–70 | بلوک | `test_ui_keeps_fixed_sidebars_focus_pan_and_four_de` | 0 | ⬜ |
| `TEST-138.08` | 71–79 | بلوک | `test_soft_delete_and_shared_food_users_are_protect` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-139</b> — <code>tests/test_org_wires_node.js</code> (30 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-139.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-139.02` | 9–9 | بلوک | `box` | 0 | ⬜ |
| `TEST-139.03` | 10–30 | بلوک | `node` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-140</b> — <code>tests/test_particle_effect_runtime_node.js</code> (172 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-140.01` | 1–8 | بلوک | `Runtime smoke test for the standalone particle can` | 0 | ⬜ |
| `TEST-140.02` | 9–66 | بلوک | `FakeElement` | 0 | ⬜ |
| `TEST-140.03` | 67–172 | بلوک | `makeEnvironment` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-141</b> — <code>tests/test_particle_effect_static.py</code> (96 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-141.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-141.02` | 9–10 | بلوک | `ParticleEffectReleaseTests` | 0 | ⬜ |
| `TEST-141.03` | 11–16 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-141.04` | 17–25 | بلوک | `test_automatic_login_and_dashboard_particle_loader` | 0 | ⬜ |
| `TEST-141.05` | 26–44 | بلوک | `test_particle_settings_are_sanitized_and_match_eng` | 0 | ⬜ |
| `TEST-141.06` | 45–62 | بلوک | `test_two_accessible_tabs_use_the_existing_shared_3` | 0 | ⬜ |
| `TEST-141.07` | 63–78 | بلوک | `test_active_effect_and_both_configs_are_saved_with` | 0 | ⬜ |
| `TEST-141.08` | 79–85 | بلوک | `test_particle_preview_controller_has_live_controls` | 1 | ⬜ |
| `TEST-141.09` | 86–96 | بلوک | `test_login_dashboard_and_usage_guidance_are_explic` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-142</b> — <code>tests/test_permissions_followup_static.py</code> (58 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-142.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-142.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-142.03` | 11–11 | بلوک | `PermissionFollowupStaticTests` | 0 | ⬜ |
| `TEST-142.04` | 12–29 | بلوک | `test_health_and_traffic_destinations_have_view_edi` | 0 | ⬜ |
| `TEST-142.05` | 30–45 | بلوک | `test_cd_dvd_incoming_defaults_and_direct_edit_allo` | 1 | ⬜ |
| `TEST-142.06` | 46–50 | بلوک | `test_primary_admin_promotion_is_not_delegated` | 0 | ⬜ |
| `TEST-142.07` | 51–58 | بلوک | `test_proxy_permission_is_not_listed_as_an_independ` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-143</b> — <code>tests/test_release_1_62_10_org_liaison_static.py</code> (120 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-143.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-143.02` | 14–17 | بلوک | `read` | 0 | ⬜ |
| `TEST-143.03` | 18–31 | بلوک | `test_migration_adds_chart_side_idempotently` | 1 | ⬜ |
| `TEST-143.04` | 32–49 | بلوک | `test_index_php_supports_liaison_node` | 0 | ⬜ |
| `TEST-143.05` | 50–63 | بلوک | `test_organization_js_renders_liaison_band` | 0 | ⬜ |
| `TEST-143.06` | 64–77 | بلوک | `test_css_positions_liaisons_without_connectors` | 0 | ⬜ |
| `TEST-143.07` | 78–84 | بلوک | `test_help_guide_covers_liaison_units` | 0 | ⬜ |
| `TEST-143.08` | 85–114 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-143.09` | 115–120 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-144</b> — <code>tests/test_release_1_62_11_code_navigation_static.py</code> (95 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-144.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-144.02` | 14–17 | بلوک | `read` | 0 | ⬜ |
| `TEST-144.03` | 18–32 | بلوک | `test_index_has_navigation_banners` | 0 | ⬜ |
| `TEST-144.04` | 33–39 | بلوک | `test_bootstrap_has_function_family_toc` | 0 | ⬜ |
| `TEST-144.05` | 40–48 | بلوک | `test_architecture_and_audit_docs_exist` | 0 | ⬜ |
| `TEST-144.06` | 49–53 | بلوک | `test_safety_snapshot_documented_in_handoff` | 0 | ⬜ |
| `TEST-144.07` | 54–89 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-144.08` | 90–95 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-145</b> — <code>tests/test_release_1_62_12_org_actions_split_static.py</code> (107 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-145.01` | 1–23 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-145.02` | 24–27 | بلوک | `read` | 0 | ⬜ |
| `TEST-145.03` | 28–38 | بلوک | `test_index_requires_org_actions_file_in_place` | 0 | ⬜ |
| `TEST-145.04` | 39–52 | بلوک | `test_org_actions_file_contains_all_thirteen_action` | 0 | ⬜ |
| `TEST-145.05` | 53–63 | بلوک | `test_no_include_semantics_traps_in_moved_block` | 0 | ⬜ |
| `TEST-145.06` | 64–71 | بلوک | `test_liaison_behavior_strings_survived_the_move` | 0 | ⬜ |
| `TEST-145.07` | 72–101 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-145.08` | 102–107 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-146</b> — <code>tests/test_release_1_62_13_ticket_asset_actions_static.py</code> (135 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-146.01` | 1–23 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-146.02` | 24–27 | بلوک | `read` | 0 | ⬜ |
| `TEST-146.03` | 28–33 | بلوک | `body_of` | 0 | ⬜ |
| `TEST-146.04` | 34–45 | بلوک | `test_index_requires_both_files_in_place_and_in_ord` | 0 | ⬜ |
| `TEST-146.05` | 46–64 | بلوک | `test_ticket_actions_file_sections_and_persian_desc` | 1 | ⬜ |
| `TEST-146.06` | 65–81 | بلوک | `test_asset_actions_file_sections_and_persian_descr` | 0 | ⬜ |
| `TEST-146.07` | 82–114 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-146.08` | 115–120 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |
| `TEST-146.09` | 121–135 | بلوک | `test_fix1_dashboard_files_and_all_panel_asset_refs` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-147</b> — <code>tests/test_release_1_62_14_service_actions_static.py</code> (120 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-147.01` | 1–31 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-147.02` | 32–35 | بلوک | `read` | 0 | ⬜ |
| `TEST-147.03` | 36–50 | بلوک | `test_index_requires_service_actions_in_original_or` | 0 | ⬜ |
| `TEST-147.04` | 51–67 | بلوک | `test_service_actions_file_structure_and_persian_de` | 0 | ⬜ |
| `TEST-147.05` | 68–105 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-147.06` | 106–114 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-147.07` | 115–120 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-148</b> — <code>tests/test_release_1_62_15_service_modal_fix_static.py</code> (105 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-148.01` | 1–18 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-148.02` | 19–22 | بلوک | `read` | 0 | ⬜ |
| `TEST-148.03` | 23–34 | بلوک | `test_modal_layer_keeps_high_z_index_against_generi` | 0 | ⬜ |
| `TEST-148.04` | 35–43 | بلوک | `test_backdrop_click_closes_service_modal_like_othe` | 0 | ⬜ |
| `TEST-148.05` | 44–52 | بلوک | `test_cache_bust_versions_bumped` | 0 | ⬜ |
| `TEST-148.06` | 53–90 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-148.07` | 91–99 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-148.08` | 100–105 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-149</b> — <code>tests/test_release_1_62_16_role_settings_actions_static.py</code> (123 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-149.01` | 1–39 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-149.02` | 40–43 | بلوک | `read` | 0 | ⬜ |
| `TEST-149.03` | 44–56 | بلوک | `test_index_requires_both_files_in_original_order` | 0 | ⬜ |
| `TEST-149.04` | 57–73 | بلوک | `test_action_files_structure_and_persian_descriptio` | 0 | ⬜ |
| `TEST-149.05` | 74–108 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-149.06` | 109–117 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-149.07` | 118–123 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-150</b> — <code>tests/test_release_1_62_17_supervisor_holiday_actions_static.py</code> (119 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-150.01` | 1–34 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-150.02` | 35–38 | بلوک | `read` | 0 | ⬜ |
| `TEST-150.03` | 39–53 | بلوک | `test_index_requires_both_files_in_original_order` | 0 | ⬜ |
| `TEST-150.04` | 54–69 | بلوک | `test_action_files_structure_and_persian_descriptio` | 0 | ⬜ |
| `TEST-150.05` | 70–104 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-150.06` | 105–113 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-150.07` | 114–119 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-151</b> — <code>tests/test_release_1_62_18_account_misc_actions_static.py</code> (211 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-151.01` | 1–26 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-151.02` | 27–30 | بلوک | `read` | 0 | ⬜ |
| `TEST-151.03` | 31–49 | بلوک | `test_index_requires_all_four_files_in_original_ord` | 0 | ⬜ |
| `TEST-151.04` | 50–64 | بلوک | `test_action_files_structure_and_persian_descriptio` | 0 | ⬜ |
| `TEST-151.05` | 65–196 | بلوک | `test_version_manifest_and_package` | 2 | ⬜ |
| `TEST-151.06` | 197–205 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-151.07` | 206–211 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-152</b> — <code>tests/test_release_1_62_19_bulk_user_structure_actions_static.py</code> (120 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-152.01` | 1–27 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-152.02` | 28–31 | بلوک | `read` | 0 | ⬜ |
| `TEST-152.03` | 32–45 | بلوک | `test_index_requires_both_files_in_original_order` | 0 | ⬜ |
| `TEST-152.04` | 46–60 | بلوک | `test_action_files_structure_and_persian_descriptio` | 0 | ⬜ |
| `TEST-152.05` | 61–95 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-152.06` | 96–105 | بلوک | `test_backup_group_moved_in_1_62_20` | 0 | ⬜ |
| `TEST-152.07` | 106–114 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-152.08` | 115–120 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-153</b> — <code>tests/test_release_1_62_20_backup_split_bootstrap_lib_static.py</code> (183 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-153.01` | 1–44 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-153.02` | 45–48 | بلوک | `read` | 0 | ⬜ |
| `TEST-153.03` | 49–65 | بلوک | `test_backup_actions_moved_out_of_index` | 0 | ⬜ |
| `TEST-153.04` | 66–77 | بلوک | `test_index_require_order_keeps_backup_group_positi` | 0 | ⬜ |
| `TEST-153.05` | 78–91 | بلوک | `test_bootstrap_is_loader_and_lib_order_is_preserve` | 0 | ⬜ |
| `TEST-153.06` | 92–105 | بلوک | `test_lib_files_structure_and_strictness` | 0 | ⬜ |
| `TEST-153.07` | 106–122 | بلوک | `test_key_function_families_live_in_expected_lib_mo` | 0 | ⬜ |
| `TEST-153.08` | 123–134 | بلوک | `test_dead_functions_are_fully_removed` | 0 | ⬜ |
| `TEST-153.09` | 135–170 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-153.10` | 171–179 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-153.11` | 180–183 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-154</b> — <code>tests/test_release_1_62_21_food_printed_delivery_static.py</code> (95 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-154.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-154.02` | 14–41 | بلوک | `test_release_package_is_cumulative_from_16220_and_` | 0 | ⬜ |
| `TEST-154.03` | 42–61 | بلوک | `test_api_reads_event_records_with_the_requested_st` | 0 | ⬜ |
| `TEST-154.04` | 62–84 | بلوک | `test_report_is_after_statistics_with_selectable_fi` | 0 | ⬜ |
| `TEST-154.05` | 85–95 | بلوک | `test_packaged_panel_refs_resolve_and_assets_are_mi` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-155</b> — <code>tests/test_release_1_62_24_cumulative_static.py</code> (116 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-155.01` | 1–14 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-155.02` | 15–43 | بلوک | `test_cumulative_package_is_valid_and_targets_16220` | 0 | ⬜ |
| `TEST-155.03` | 44–58 | بلوک | `test_cumulative_bundle_contains_the_complete_16224` | 0 | ⬜ |
| `TEST-155.04` | 59–91 | بلوک | `test_cumulative_bundle_contains_16221_food_report_` | 0 | ⬜ |
| `TEST-155.05` | 92–104 | بلوک | `test_cumulative_bundle_contains_16222_through_1622` | 0 | ⬜ |
| `TEST-155.06` | 105–113 | بلوک | `test_update_manifest_paths_are_safe_for_the_app_up` | 0 | ⬜ |
| `TEST-155.07` | 114–116 | بلوک | `test_download_page_points_to_log_based_profile_and` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-156</b> — <code>tests/test_release_1_62_36_manual_attendance_reconciliation.py</code> (87 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-156.01` | 1–19 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-156.02` | 20–41 | بلوک | `test_16236_package_and_checksum_are_integral` | 0 | ⬜ |
| `TEST-156.03` | 42–57 | بلوک | `test_migration_backfills_delivery_and_clears_same_` | 0 | ⬜ |
| `TEST-156.04` | 58–73 | بلوک | `test_runtime_reconciliation_updates_stored_rows_wh` | 1 | ⬜ |
| `TEST-156.05` | 74–77 | بلوک | `test_download_page_points_to_16236` | 0 | ⬜ |
| `TEST-156.06` | 78–87 | بلوک | `test_system_updater_executes_manifest_migrations_f` | 6 | ⬜ |

</details>

<details>
<summary><b>TEST-157</b> — <code>tests/test_release_1_62_4_fixes_static.py</code> (72 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-157.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-157.02` | 10–13 | بلوک | `read` | 0 | ⬜ |
| `TEST-157.03` | 14–24 | بلوک | `test_dark_mode_covers_food_planning_forms_in_both_` | 0 | ⬜ |
| `TEST-157.04` | 25–35 | بلوک | `test_theme_css_cache_versions_are_bumped` | 0 | ⬜ |
| `TEST-157.05` | 36–44 | بلوک | `test_traffic_edit_history_lists_only_real_updates` | 0 | ⬜ |
| `TEST-157.06` | 45–55 | بلوک | `test_food_dashboard_host_keeps_full_width_and_no_s` | 0 | ⬜ |
| `TEST-157.07` | 56–72 | بلوک | `test_manual_ticket_list_has_select_all_and_no_defa` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-158</b> — <code>tests/test_release_1_62_5_theme_toggle_static.py</code> (40 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-158.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-158.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-158.03` | 12–22 | بلوک | `test_app_js_theme_toggle_is_robust` | 0 | ⬜ |
| `TEST-158.04` | 23–27 | بلوک | `test_all_host_shells_load_the_new_app_js` | 0 | ⬜ |
| `TEST-158.05` | 28–40 | بلوک | `test_release_package_ships_app_js` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-159</b> — <code>tests/test_release_1_62_60_package.py</code> (42 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-159.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-159.02` | 13–13 | بلوک | `ReleasePackageTest` | 0 | ⬜ |
| `TEST-159.03` | 14–42 | بلوک | `test_manifest_cumulative_files_and_hash` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-160</b> — <code>tests/test_release_1_62_61_package.py</code> (29 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-160.01` | 1–11 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-160.02` | 12–29 | بلوک | `test_cumulative_guest_fix_and_immutable_baseline` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-161</b> — <code>tests/test_release_1_62_6_auto_assign_static.py</code> (47 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-161.01` | 1–11 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-161.02` | 12–19 | بلوک | `test_org_managers_are_deprioritized_in_auto_assign` | 0 | ⬜ |
| `TEST-161.03` | 20–27 | بلوک | `test_support_manager_repair_action_exists` | 0 | ⬜ |
| `TEST-161.04` | 28–34 | بلوک | `test_auto_assign_diagnostics_card_is_rendered` | 0 | ⬜ |
| `TEST-161.05` | 35–47 | بلوک | `test_help_guide_explains_ticket_assignee` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-162</b> — <code>tests/test_release_1_62_73_dashboard_r2_package.py</code> (102 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-162.01` | 1–29 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-162.02` | 30–30 | بلوک | `DashboardR2ReleasePackageTest` | 0 | ⬜ |
| `TEST-162.03` | 31–81 | بلوک | `test_cumulative_package_contents_checksum_and_hist` | 0 | ⬜ |
| `TEST-162.04` | 82–102 | بلوک | `test_dashboard_r2_archive_stays_intact` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-163</b> — <code>tests/test_release_1_62_73_package.py</code> (145 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-163.01` | 1–57 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-163.02` | 58–58 | بلوک | `ReleasePackageTest` | 0 | ⬜ |
| `TEST-163.03` | 59–121 | بلوک | `test_cumulative_manifest_contents_and_immutable_ba` | 0 | ⬜ |
| `TEST-163.04` | 122–145 | بلوک | `test_dashboard_cleanup_archive_stays_intact` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-164</b> — <code>tests/test_release_1_62_74_package.py</code> (231 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-164.01` | 1–148 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TEST-164.02` | 149–149 | بلوک | `FoodReconciliationReleasePackageTest` | 0 | ⬜ |
| `TEST-164.03` | 150–210 | بلوک | `test_cumulative_package_manifest_payload_and_check` | 0 | ⬜ |
| `TEST-164.04` | 211–231 | بلوک | `test_meal_reconciliation_archive_stays_intact` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-165</b> — <code>tests/test_release_1_62_75_package.py</code> (234 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-165.01` | 1–154 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TEST-165.02` | 155–155 | بلوک | `PublicAccessGroupsReleasePackageTest` | 0 | ⬜ |
| `TEST-165.03` | 156–213 | بلوک | `test_cumulative_payload_migration_and_checksum` | 0 | ⬜ |
| `TEST-165.04` | 214–234 | بلوک | `test_public_access_groups_archive_stays_intact` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-166</b> — <code>tests/test_release_1_62_7_traffic_history_events_static.py</code> (73 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-166.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-166.02` | 11–14 | بلوک | `read` | 0 | ⬜ |
| `TEST-166.03` | 15–20 | بلوک | `test_release_1_62_7_manifest_version` | 0 | ⬜ |
| `TEST-166.04` | 21–31 | بلوک | `test_history_helpers_accept_all_events_flag` | 0 | ⬜ |
| `TEST-166.05` | 32–38 | بلوک | `test_changes_tab_reads_events_param_and_passes_it_` | 2 | ⬜ |
| `TEST-166.06` | 39–49 | بلوک | `test_changes_tab_has_filter_toggle_and_pagination_` | 3 | ⬜ |
| `TEST-166.07` | 50–55 | بلوک | `test_notes_promise_no_data_is_deleted` | 0 | ⬜ |
| `TEST-166.08` | 56–61 | بلوک | `test_help_guide_documents_the_toggle` | 0 | ⬜ |
| `TEST-166.09` | 62–66 | بلوک | `test_filter_buttons_have_styles` | 0 | ⬜ |
| `TEST-166.10` | 67–73 | بلوک | `test_update_manifest_is_present_and_consistent` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-167</b> — <code>tests/test_release_1_62_88_package_integrity.py</code> (48 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-167.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-167.02` | 8–14 | بلوک | `test_builder_requires_exact_base_and_versions` | 0 | ⬜ |
| `TEST-167.03` | 15–26 | بلوک | `test_manifest_and_archive_are_checked_bidirectiona` | 0 | ⬜ |
| `TEST-167.04` | 27–32 | بلوک | `test_checksum_is_created_from_closed_final_archive` | 0 | ⬜ |
| `TEST-167.05` | 33–38 | بلوک | `test_032_is_excluded_from_executable_migrations` | 0 | ⬜ |
| `TEST-167.06` | 39–43 | بلوک | `test_report_does_not_claim_all_migrations_are_addi` | 0 | ⬜ |
| `TEST-167.07` | 44–48 | بلوک | `test_package_excludes_obsolete_release_helpers` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-168</b> — <code>tests/test_release_1_62_8_traffic_autofill_multi_approver_static.py</code> (127 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-168.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-168.02` | 11–14 | بلوک | `read` | 0 | ⬜ |
| `TEST-168.03` | 15–21 | بلوک | `test_version_is_1_62_8` | 0 | ⬜ |
| `TEST-168.04` | 22–23 | بلوک | `بخش ۱: تکمیل خودکار با کد ملی / نام / تلفن` | 0 | ⬜ |
| `TEST-168.05` | 24–34 | بلوک | `test_lookup_api_accepts_code_phone_and_name` | 0 | ⬜ |
| `TEST-168.06` | 35–43 | بلوک | `test_lookup_javascript_triggers_on_name_and_phone` | 0 | ⬜ |
| `TEST-168.07` | 44–45 | بلوک | `بخش ۲: ۱ تا ۳ تأییدکننده برای هر مقصد` | 0 | ⬜ |
| `TEST-168.08` | 46–55 | بلوک | `test_multi_approver_columns_exist_in_schema_and_mi` | 0 | ⬜ |
| `TEST-168.09` | 56–67 | بلوک | `test_destination_forms_accept_three_approvers` | 1 | ⬜ |
| `TEST-168.10` | 68–76 | بلوک | `test_server_validates_approver_against_destination` | 0 | ⬜ |
| `TEST-168.11` | 77–87 | بلوک | `test_forms_render_approver_select_with_data_approv` | 0 | ⬜ |
| `TEST-168.12` | 88–99 | بلوک | `test_bulk_preview_button_moved_next_to_clear_selec` | 0 | ⬜ |
| `TEST-168.13` | 100–105 | بلوک | `test_help_guide_documents_both_features` | 0 | ⬜ |
| `TEST-168.14` | 106–111 | بلوک | `test_update_manifest_lists_migration` | 0 | ⬜ |
| `TEST-168.15` | 112–127 | بلوک | `test_current_package_passes_system_update_rules` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-169</b> — <code>tests/test_release_1_62_9_manual_ticket_date_guest_static.py</code> (171 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-169.01` | 1–17 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-169.02` | 18–23 | بلوک | `read` | 0 | ⬜ |
| `TEST-169.03` | 24–35 | بلوک | `test_backend_accepts_past_dates_and_rejects_future` | 0 | ⬜ |
| `TEST-169.04` | 36–42 | بلوک | `test_route_passes_selected_date_instead_of_today_g` | 0 | ⬜ |
| `TEST-169.05` | 43–48 | بلوک | `test_save_many_forwards_date_to_each_save` | 0 | ⬜ |
| `TEST-169.06` | 49–62 | بلوک | `test_ui_has_jalali_date_field_with_calendar` | 0 | ⬜ |
| `TEST-169.07` | 63–80 | بلوک | `test_guest_row_is_first_and_guest_save_path_exists` | 0 | ⬜ |
| `TEST-169.08` | 81–88 | بلوک | `test_save_many_accepts_guest_id_zero` | 0 | ⬜ |
| `TEST-169.09` | 89–97 | بلوک | `test_ui_pins_guest_row_and_sends_id_zero` | 0 | ⬜ |
| `TEST-169.10` | 98–105 | بلوک | `test_guest_event_schema_support_already_exists` | 0 | ⬜ |
| `TEST-169.11` | 106–115 | بلوک | `test_destination_forms_fit_four_fields_on_one_line` | 0 | ⬜ |
| `TEST-169.12` | 116–122 | بلوک | `test_help_guide_documents_new_manual_ticket_featur` | 0 | ⬜ |
| `TEST-169.13` | 123–165 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-169.14` | 166–171 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-170</b> — <code>tests/test_repair_16236_full_source_restore.py</code> (163 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-170.01` | 1–34 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-170.02` | 35–44 | بلوک | `reconstructed_release_files` | 0 | ⬜ |
| `TEST-170.03` | 45–54 | بلوک | `reconstructed_migrations` | 0 | ⬜ |
| `TEST-170.04` | 55–80 | بلوک | `test_repair_package_rebuilds_the_release_overlay_n` | 0 | ⬜ |
| `TEST-170.05` | 81–90 | بلوک | `test_repair_package_carries_all_idempotent_migrati` | 0 | ⬜ |
| `TEST-170.06` | 91–127 | بلوک | `test_restored_package_has_the_missing_ui_and_serve` | 0 | ⬜ |
| `TEST-170.07` | 128–140 | بلوک | `test_download_pages_point_to_corrected_package_and` | 0 | ⬜ |
| `TEST-170.08` | 141–158 | بلوک | `test_repo_snapshot_matches_the_reconstructed_16236` | 0 | ⬜ |
| `TEST-170.09` | 159–163 | بلوک | `test_restore_package_checksum_matches` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-171</b> — <code>tests/test_request_center_claim_concurrency_mariadb.php</code> (198 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-171.01` | 1–30 | ناحیه | `بلوک رویه‌ای 1-30` | 0 | ⬜ |
| `TEST-171.02` | 31–37 | تابع | `t10_check` | 0 | ⬜ |
| `TEST-171.03` | 38–198 | ناحیه | `بلوک رویه‌ای 38-198` | **14** | ⬜ |

</details>

<details>
<summary><b>TEST-172</b> — <code>tests/test_request_center_first_slice_static.py</code> (131 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-172.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-172.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-172.03` | 11–11 | بلوک | `RequestCenterFirstSliceTests` | 0 | ⬜ |
| `TEST-172.04` | 12–21 | بلوک | `test_independent_entrypoint_bootstraps_and_authent` | 0 | ⬜ |
| `TEST-172.05` | 22–27 | بلوک | `test_repository_uses_existing_visibility_contracts` | 0 | ⬜ |
| `TEST-172.06` | 28–32 | بلوک | `test_internal_messages_are_filtered_in_sql` | 0 | ⬜ |
| `TEST-172.07` | 33–47 | بلوک | `test_read_slice_has_no_schema_or_write_operations` | 0 | ⬜ |
| `TEST-172.08` | 48–55 | بلوک | `test_transition_policy_is_owned_by_module_service` | 1 | ⬜ |
| `TEST-172.09` | 56–89 | بلوک | `test_migrated_status_writes_use_the_central_workfl` | 1 | ⬜ |
| `TEST-172.10` | 90–96 | بلوک | `test_user_supplied_text_is_escaped_and_attachment_` | 0 | ⬜ |
| `TEST-172.11` | 97–109 | بلوک | `test_workspace_views_are_permission_gated_and_scop` | 3 | ⬜ |
| `TEST-172.12` | 110–120 | بلوک | `test_not_found_and_detail_navigation_preserve_work` | 3 | ⬜ |
| `TEST-172.13` | 121–131 | بلوک | `test_legacy_workflow_is_not_removed_by_first_slice` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-173</b> — <code>tests/test_request_center_mariadb_ddl_gates.php</code> (108 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-173.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TEST-173.02` | 22–28 | تابع | `tcheck` | 0 | ⬜ |
| `TEST-173.03` | 30–37 | تابع | `tableExists` | 0 | ⬜ |
| `TEST-173.04` | 38–108 | ناحیه | `بلوک رویه‌ای 38-108` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-174</b> — <code>tests/test_request_center_metadata_lock_mariadb.php</code> (83 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-174.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `TEST-174.02` | 21–27 | تابع | `t09_check` | 0 | ⬜ |
| `TEST-174.03` | 29–36 | تابع | `t09_column_exists` | 0 | ⬜ |
| `TEST-174.04` | 37–83 | ناحیه | `بلوک رویه‌ای 37-83` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-175</b> — <code>tests/test_request_center_repository_sqlite.php</code> (104 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-175.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-175.02` | 6–9 | تابع | `ticket_scope` | 5 | ⬜ |
| `TEST-175.03` | 11–14 | تابع | `ticket_user_can_view` | 0 | ⬜ |
| `TEST-175.04` | 16–22 | تابع | `check_request_center` | 0 | ⬜ |
| `TEST-175.05` | 23–104 | ناحیه | `بلوک رویه‌ای 23-104` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-176</b> — <code>tests/test_request_center_schema_preflight_mariadb.php</code> (146 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-176.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-176.02` | 4–7 | تابع | `ticket_scope` | 5 | ⬜ |
| `TEST-176.03` | 9–12 | تابع | `ticket_user_can_view` | 0 | ⬜ |
| `TEST-176.04` | 14–20 | تابع | `check_request_center` | 0 | ⬜ |
| `TEST-176.05` | 21–146 | ناحیه | `بلوک رویه‌ای 21-146` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-177</b> — <code>tests/test_request_center_t07_migration_clone.php</code> (399 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-177.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TEST-177.02` | 22–26 | تابع | `t07_fail` | 0 | ⬜ |
| `TEST-177.03` | 28–34 | تابع | `t07_check` | 0 | ⬜ |
| `TEST-177.04` | 36–39 | تابع | `t07_ident` | 0 | ⬜ |
| `TEST-177.05` | 41–83 | تابع | `t07_mysql_file` | 4 | ⬜ |
| `TEST-177.06` | 85–91 | تابع | `t07_tables` | 0 | ⬜ |
| `TEST-177.07` | 93–143 | تابع | `t07_fingerprint` | 5 | ⬜ |
| `TEST-177.08` | 145–154 | تابع | `t07_registry_rows` | 0 | ⬜ |
| `TEST-177.09` | 156–169 | تابع | `t07_service_catalog_distribution` | 0 | ⬜ |
| `TEST-177.10` | 170–187 | ناحیه | `بلوک رویه‌ای 170-187` | 4 | ⬜ |
| `TEST-177.11` | 188–188 | ناحیه | `Strip only database-selection statements from the ` | 0 | ⬜ |
| `TEST-177.12` | 189–367 | ناحیه | `is forced into the disposable clone regardless of ` | 7 | ⬜ |
| `TEST-177.13` | 368–368 | ناحیه | `Cross-check the actual 1.62.88 release manifest. T` | 0 | ⬜ |
| `TEST-177.14` | 369–399 | ناحیه | `excludes migration 032; all manifest migrations mu` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-178</b> — <code>tests/test_request_center_t12_preflight.php</code> (322 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-178.01` | 1–33 | ناحیه | `بلوک رویه‌ای 1-33` | 0 | ⬜ |
| `TEST-178.02` | 34–37 | تابع | `t12_fail` | 0 | ⬜ |
| `TEST-178.03` | 39–45 | تابع | `t12_check` | 0 | ⬜ |
| `TEST-178.04` | 47–50 | تابع | `t12_ident` | 0 | ⬜ |
| `TEST-178.05` | 52–88 | تابع | `t12_mysql_file` | 4 | ⬜ |
| `TEST-178.06` | 90–109 | تابع | `t12_http` | 1 | ⬜ |
| `TEST-178.07` | 111–144 | تابع | `t12_start_server` | 4 | ⬜ |
| `TEST-178.08` | 146–161 | تابع | `t12_schema_fingerprint` | 0 | ⬜ |
| `TEST-178.09` | 163–181 | تابع | `t12_session` | 0 | ⬜ |
| `TEST-178.10` | 183–209 | تابع | `t12_config` | 0 | ⬜ |
| `TEST-178.11` | 210–322 | ناحیه | `بلوک رویه‌ای 210-322` | **12** | ⬜ |

</details>

<details>
<summary><b>TEST-179</b> — <code>tests/test_request_center_web_ddl_runtime.php</code> (282 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-179.01` | 1–34 | ناحیه | `بلوک رویه‌ای 1-34` | 0 | ⬜ |
| `TEST-179.02` | 35–41 | تابع | `t04_check` | 0 | ⬜ |
| `TEST-179.03` | 43–49 | تابع | `t04_reset_log` | 4 | ⬜ |
| `TEST-179.04` | 51–72 | تابع | `t04_log_window` | 1 | ⬜ |
| `TEST-179.05` | 74–93 | تابع | `t04_http` | 6 | ⬜ |
| `TEST-179.06` | 95–106 | تابع | `t04_wait_server` | 1 | ⬜ |
| `TEST-179.07` | 107–282 | ناحیه | `بلوک رویه‌ای 107-282` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-180</b> — <code>tests/test_request_center_workflow_service_mariadb.php</code> (151 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-180.01` | 1–29 | ناحیه | `بلوک رویه‌ای 1-29` | 0 | ⬜ |
| `TEST-180.02` | 30–36 | تابع | `mariadb_workflow_check` | 0 | ⬜ |
| `TEST-180.03` | 37–74 | ناحیه | `بلوک رویه‌ای 37-74` | 5 | ⬜ |
| `TEST-180.04` | 75–75 | ناحیه | `The MySQL driver branch appends FOR UPDATE; succes` | 1 | ⬜ |
| `TEST-180.05` | 76–92 | ناحیه | `that the production locking query is accepted by t` | 0 | ⬜ |
| `TEST-180.06` | 93–109 | ناحیه | `A caller-owned rollback must undo both the status ` | 0 | ⬜ |
| `TEST-180.07` | 110–127 | ناحیه | `A stale status and a stale optimistic field must b` | 0 | ⬜ |
| `TEST-180.08` | 128–138 | ناحیه | `Cancellation-pending blocks ordinary transitions e` | 0 | ⬜ |
| `TEST-180.09` | 139–151 | ناحیه | `Column names are never accepted from untrusted cal` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-181</b> — <code>tests/test_request_center_workflow_service_sqlite.php</code> (190 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-181.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-181.02` | 6–12 | تابع | `workflow_check` | 0 | ⬜ |
| `TEST-181.03` | 13–190 | ناحیه | `بلوک رویه‌ای 13-190` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-182</b> — <code>tests/test_role_permissions_ui_static.py</code> (31 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-182.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-182.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-182.03` | 10–31 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-183</b> — <code>tests/test_route_security_static.py</code> (116 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-183.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-183.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-183.03` | 12–17 | بلوک | `section` | 0 | ⬜ |
| `TEST-183.04` | 18–23 | بلوک | `test_new_ticket_page_requires_ticket_create_permis` | 0 | ⬜ |
| `TEST-183.05` | 24–30 | بلوک | `test_post_action_dispatch_enforces_csrf_before_han` | 2 | ⬜ |
| `TEST-183.06` | 31–38 | بلوک | `test_food_ticket_api_requires_login_and_csrf_for_p` | 0 | ⬜ |
| `TEST-183.07` | 39–47 | بلوک | `test_food_order_api_requires_authenticated_user_an` | 0 | ⬜ |
| `TEST-183.08` | 48–55 | بلوک | `test_sensitive_download_and_export_actions_have_ex` | 2 | ⬜ |
| `TEST-183.09` | 56–79 | بلوک | `test_action_handlers_refuse_direct_http_access` | 0 | ⬜ |
| `TEST-183.10` | 80–88 | بلوک | `test_self_service_password_change_is_audited_atomi` | 0 | ⬜ |
| `TEST-183.11` | 89–109 | بلوک | `test_internal_modules_refuse_direct_http_access` | 1 | ⬜ |
| `TEST-183.12` | 110–116 | بلوک | `test_it_ticket_asset_reference_obeys_asset_own_uni` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-184</b> — <code>tests/test_schema_heal_static.py</code> (46 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-184.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-184.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-184.03` | 12–46 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-185</b> — <code>tests/test_session_boot_static.py</code> (143 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-185.01` | 1–49 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-185.02` | 50–53 | بلوک | `main` | 0 | ⬜ |
| `TEST-185.03` | 54–143 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-186</b> — <code>tests/test_session_csrf_hardening_static.py</code> (189 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-186.01` | 1–25 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-186.02` | 26–30 | بلوک | `read` | 0 | ⬜ |
| `TEST-186.03` | 31–42 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-186.04` | 43–51 | بلوک | `csrf_token_code` | 0 | ⬜ |
| `TEST-186.05` | 52–63 | بلوک | `test_csrf_token_never_adopts_a_value_supplied_by_t` | 0 | ⬜ |
| `TEST-186.06` | 64–71 | بلوک | `test_csrf_token_is_generated_with_a_cryptographic_` | 0 | ⬜ |
| `TEST-186.07` | 72–91 | بلوک | `test_csrf_token_is_seeded_while_the_session_is_sti` | 0 | ⬜ |
| `TEST-186.08` | 92–98 | بلوک | `test_legacy_csrf_cookie_is_actively_cleared` | 0 | ⬜ |
| `TEST-186.09` | 99–108 | بلوک | `test_require_csrf_still_compares_in_constant_time` | 0 | ⬜ |
| `TEST-186.10` | 109–122 | بلوک | `test_session_idle_timeout_exists_and_is_configurab` | 0 | ⬜ |
| `TEST-186.11` | 123–136 | بلوک | `test_idle_timeout_is_wired_into_bootstrap_after_co` | 0 | ⬜ |
| `TEST-186.12` | 137–146 | بلوک | `test_idle_timeout_only_affects_logged_in_sessions` | 0 | ⬜ |
| `TEST-186.13` | 147–155 | بلوک | `test_idle_timeout_can_be_switched_off_without_edit` | 0 | ⬜ |
| `TEST-186.14` | 156–167 | بلوک | `test_session_cookie_keeps_its_hardening_flags` | 0 | ⬜ |
| `TEST-186.15` | 168–173 | بلوک | `test_dead_duplicate_auth_layer_is_gone` | 0 | ⬜ |
| `TEST-186.16` | 174–189 | بلوک | `test_no_php_file_references_the_removed_app_namesp` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-187</b> — <code>tests/test_skill_manifest_static.py</code> (196 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-187.01` | 1–51 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-187.02` | 52–67 | بلوک | `sections` | 0 | ⬜ |
| `TEST-187.03` | 68–79 | بلوک | `foreign_characters` | 0 | ⬜ |
| `TEST-187.04` | 80–83 | بلوک | `main` | 0 | ⬜ |
| `TEST-187.05` | 84–89 | بلوک | `check` | 0 | ⬜ |
| `TEST-187.06` | 90–103 | بلوک | `وجود فایلها` | 0 | ⬜ |
| `TEST-187.07` | 104–118 | بلوک | `شمارش و پیوستگی شناسهها` | 0 | ⬜ |
| `TEST-187.08` | 119–132 | بلوک | `ساختار تکتک مهارتها` | 0 | ⬜ |
| `TEST-187.09` | 133–143 | بلوک | `جدول مانیفست` | 0 | ⬜ |
| `TEST-187.10` | 144–157 | بلوک | `صداقت دربارهٔ حافظه` | 0 | ⬜ |
| `TEST-187.11` | 158–166 | بلوک | `گردشکار` | 0 | ⬜ |
| `TEST-187.12` | 167–170 | بلوک | `خودآزمون، گزارش و قواعد` | 0 | ⬜ |
| `TEST-187.13` | 171–176 | بلوک | `پیوند AGENTS.md` | 0 | ⬜ |
| `TEST-187.14` | 177–181 | بلوک | `آلودگی خط نوشتاری` | 0 | ⬜ |
| `TEST-187.15` | 182–196 | بلوک | `نتیجه` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-188</b> — <code>tests/test_startup_performance_static.py</code> (51 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-188.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-188.02` | 6–51 | بلوک | `read` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-189</b> — <code>tests/test_system_health_accuracy_static.py</code> (99 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-189.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-189.02` | 16–33 | بلوک | `test_event_totals_are_independent_of_the_display_l` | 0 | ⬜ |
| `TEST-189.03` | 34–47 | بلوک | `test_export_reports_full_counts_and_discloses_trun` | 0 | ⬜ |
| `TEST-189.04` | 48–58 | بلوک | `test_safe_reference_uses_only_class_basename_line_` | 0 | ⬜ |
| `TEST-189.05` | 59–72 | بلوک | `test_future_exception_logs_include_a_validated_err` | 0 | ⬜ |
| `TEST-189.06` | 73–99 | بلوک | `test_hotfix_package_is_a_valid_upgrade_from_16224` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-190</b> — <code>tests/test_system_health_audit_visibility_static.py</code> (81 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-190.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-190.02` | 16–20 | بلوک | `_checks_body` | 0 | ⬜ |
| `TEST-190.03` | 21–26 | بلوک | `test_audit_status_is_reported_on_the_health_page` | 0 | ⬜ |
| `TEST-190.04` | 27–32 | بلوک | `test_health_page_loads_the_audit_module` | 0 | ⬜ |
| `TEST-190.05` | 33–44 | بلوک | `test_missing_heartbeat_is_not_reported_as_healthy` | 0 | ⬜ |
| `TEST-190.06` | 45–53 | بلوک | `test_stale_audit_is_a_failure_not_a_warning` | 0 | ⬜ |
| `TEST-190.07` | 54–62 | بلوک | `test_structural_problems_outrank_regressions` | 0 | ⬜ |
| `TEST-190.08` | 63–71 | بلوک | `test_probe_failure_is_surfaced_not_swallowed` | 0 | ⬜ |
| `TEST-190.09` | 72–81 | بلوک | `test_baseline_message_does_not_demand_server_acces` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-191</b> — <code>tests/test_system_health_log_button_static.py</code> (39 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-191.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-191.02` | 10–22 | بلوک | `test_current_system_logs_page_shows_safe_export_an` | 0 | ⬜ |
| `TEST-191.03` | 23–39 | بلوک | `test_update_zip_contains_button_in_the_actual_unif` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-192</b> — <code>tests/test_system_health_readability_static.py</code> (63 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-192.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-192.02` | 11–24 | بلوک | `test_health_status_rules_include_warnings_and_basi` | 0 | ⬜ |
| `TEST-192.03` | 25–40 | بلوک | `test_health_ui_and_export_use_plain_language_witho` | 0 | ⬜ |
| `TEST-192.04` | 41–48 | بلوک | `test_system_logs_page_displays_safe_export_and_fri` | 0 | ⬜ |
| `TEST-192.05` | 49–63 | بلوک | `test_16224_package_contains_the_readable_health_an` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-193</b> — <code>tests/test_system_health_safe_export_static.py</code> (63 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-193.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-193.02` | 9–15 | بلوک | `test_safe_export_is_reachable_and_labelled_for_peo` | 0 | ⬜ |
| `TEST-193.03` | 16–28 | بلوک | `test_export_omits_user_linked_and_raw_log_fields` | 0 | ⬜ |
| `TEST-193.04` | 29–42 | بلوک | `test_humanizer_uses_safe_categories_and_common_mas` | 0 | ⬜ |
| `TEST-193.05` | 43–48 | بلوک | `test_health_warning_is_explained_without_claiming_` | 0 | ⬜ |
| `TEST-193.06` | 49–63 | بلوک | `test_published_update_contains_the_readable_safe_e` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-194</b> — <code>tests/test_system_map_blocks.py</code> (347 خط، 34 آدرس، بازبینی‌شده 0/34)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-194.01` | 1–32 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-194.02` | 33–43 | بلوک | `_tracked_production_files` | 0 | ⬜ |
| `TEST-194.03` | 44–59 | بلوک | `fingerprint` | 0 | ⬜ |
| `TEST-194.04` | 60–67 | بلوک | `index` | 0 | ⬜ |
| `TEST-194.05` | 68–74 | بلوک | `sources` | 0 | ⬜ |
| `TEST-194.06` | 75–76 | بلوک | `-` | 0 | ⬜ |
| `TEST-194.07` | 77–79 | بلوک | `-` | 0 | ⬜ |
| `TEST-194.08` | 80–89 | بلوک | `test_every_production_file_is_indexed` | 0 | ⬜ |
| `TEST-194.09` | 90–96 | بلوک | `test_index_has_no_phantom_files` | 0 | ⬜ |
| `TEST-194.10` | 97–102 | بلوک | `test_file_ids_are_unique` | 0 | ⬜ |
| `TEST-194.11` | 103–113 | بلوک | `test_block_ids_are_unique` | 0 | ⬜ |
| `TEST-194.12` | 114–115 | بلوک | `-` | 0 | ⬜ |
| `TEST-194.13` | 116–118 | بلوک | `-` | 0 | ⬜ |
| `TEST-194.14` | 119–132 | بلوک | `test_file_fingerprints_are_current` | 0 | ⬜ |
| `TEST-194.15` | 133–149 | بلوک | `test_block_fingerprints_are_current` | 0 | ⬜ |
| `TEST-194.16` | 150–161 | بلوک | `test_block_ranges_are_inside_the_file` | 0 | ⬜ |
| `TEST-194.17` | 162–163 | بلوک | `-` | 0 | ⬜ |
| `TEST-194.18` | 164–166 | بلوک | `-` | 0 | ⬜ |
| `TEST-194.19` | 167–210 | بلوک | `test_every_nonblank_line_is_covered_exactly_once` | 0 | ⬜ |
| `TEST-194.20` | 211–233 | بلوک | `test_symbol_blocks_end_on_a_closing_brace` | 0 | ⬜ |
| `TEST-194.21` | 234–235 | بلوک | `-` | 0 | ⬜ |
| `TEST-194.22` | 236–238 | بلوک | `-` | 0 | ⬜ |
| `TEST-194.23` | 239–244 | بلوک | `test_ledger_exists_and_is_valid_json` | 0 | ⬜ |
| `TEST-194.24` | 245–254 | بلوک | `test_ledger_entries_point_at_real_addresses` | 0 | ⬜ |
| `TEST-194.25` | 255–271 | بلوک | `test_ledger_flags_blocks_changed_since_review` | 0 | ⬜ |
| `TEST-194.26` | 272–286 | بلوک | `test_human_readable_map_is_in_sync` | 0 | ⬜ |
| `TEST-194.27` | 287–288 | بلوک | `-` | 0 | ⬜ |
| `TEST-194.28` | 289–294 | بلوک | `-` | 0 | ⬜ |
| `TEST-194.29` | 295–299 | بلوک | `topics` | 0 | ⬜ |
| `TEST-194.30` | 300–312 | بلوک | `test_every_page_of_the_system_has_a_topic` | 0 | ⬜ |
| `TEST-194.31` | 313–320 | بلوک | `test_every_permission_has_a_topic` | 0 | ⬜ |
| `TEST-194.32` | 321–328 | بلوک | `test_every_topic_resolves_to_at_least_one_address` | 0 | ⬜ |
| `TEST-194.33` | 329–339 | بلوک | `test_topic_addresses_exist_in_the_block_index` | 0 | ⬜ |
| `TEST-194.34` | 340–347 | بلوک | `test_boot_file_points_agents_at_the_map` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-195</b> — <code>tests/test_system_map_static.py</code> (172 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-195.01` | 1–33 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-195.02` | 34–38 | بلوک | `load` | 0 | ⬜ |
| `TEST-195.03` | 39–42 | بلوک | `main` | 0 | ⬜ |
| `TEST-195.04` | 43–156 | بلوک | `check` | 0 | ⬜ |
| `TEST-195.05` | 157–172 | بلوک | `نتیجه` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-196</b> — <code>tests/test_system_update_archive_paths.php</code> (41 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-196.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 1 | ⬜ |
| `TEST-196.02` | 6–6 | تابع | `db` | 0 | ⬜ |
| `TEST-196.03` | 7–7 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-196.04` | 8–8 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-196.05` | 9–9 | تابع | `cfg` | 0 | ⬜ |
| `TEST-196.06` | 10–41 | ناحیه | `بلوک رویه‌ای 10-41` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-197</b> — <code>tests/test_system_update_archive_zip.php</code> (156 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-197.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-197.02` | 6–6 | تابع | `db` | 0 | ⬜ |
| `TEST-197.03` | 7–7 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-197.04` | 8–8 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-197.05` | 9–9 | تابع | `cfg` | 0 | ⬜ |
| `TEST-197.06` | 10–16 | ناحیه | `بلوک رویه‌ای 10-16` | 1 | ⬜ |
| `TEST-197.07` | 17–39 | تابع | `make_test_zip` | 5 | ⬜ |
| `TEST-197.08` | 41–59 | تابع | `assert_zip_rejected` | 5 | ⬜ |
| `TEST-197.09` | 60–82 | ناحیه | `بلوک رویه‌ای 60-82` | 5 | ⬜ |
| `TEST-197.10` | 83–92 | تابع | `package_test_rejects` | 0 | ⬜ |
| `TEST-197.11` | 93–156 | ناحیه | `بلوک رویه‌ای 93-156` | **8** | ⬜ |

</details>

<details>
<summary><b>TEST-198</b> — <code>tests/test_system_update_migration_failure_gates.php</code> (232 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-198.01` | 1–47 | ناحیه | `بلوک رویه‌ای 1-47` | 5 | ⬜ |
| `TEST-198.02` | 48–51 | تابع | `db` | 0 | ⬜ |
| `TEST-198.03` | 53–56 | تابع | `cfg` | 0 | ⬜ |
| `TEST-198.04` | 58–61 | تابع | `backup_mysql_binary` | 0 | ⬜ |
| `TEST-198.05` | 63–66 | تابع | `create_backup_bundle` | 0 | ⬜ |
| `TEST-198.06` | 68–86 | تابع | `backup_run_process` | 4 | ⬜ |
| `TEST-198.07` | 88–91 | تابع | `save_audit_required` | 0 | ⬜ |
| `TEST-198.08` | 92–95 | ناحیه | `بلوک رویه‌ای 92-95` | 1 | ⬜ |
| `TEST-198.09` | 96–102 | تابع | `rc_update_check` | 0 | ⬜ |
| `TEST-198.10` | 104–127 | تابع | `rc_update_package` | 1 | ⬜ |
| `TEST-198.11` | 129–137 | تابع | `rc_update_upload` | 0 | ⬜ |
| `TEST-198.12` | 139–146 | تابع | `rc_update_table_exists` | 0 | ⬜ |
| `TEST-198.13` | 147–232 | ناحیه | `بلوک رویه‌ای 147-232` | **17** | ⬜ |

</details>

<details>
<summary><b>TEST-199</b> — <code>tests/test_system_update_migration_registry.php</code> (68 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-199.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `TEST-199.02` | 7–30 | کلاس | `MigrationRegistryFakeStatement` | 0 | ⬜ |
| `TEST-199.03` | 32–41 | کلاس | `MigrationRegistryFakeDb` | 0 | ⬜ |
| `TEST-199.04` | 42–44 | ناحیه | `بلوک رویه‌ای 42-44` | 0 | ⬜ |
| `TEST-199.05` | 45–48 | تابع | `db` | 0 | ⬜ |
| `TEST-199.06` | 49–49 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-199.07` | 50–50 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-199.08` | 51–51 | تابع | `cfg` | 0 | ⬜ |
| `TEST-199.09` | 52–68 | ناحیه | `بلوک رویه‌ای 52-68` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-200</b> — <code>tests/test_system_update_requires_version.php</code> (96 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-200.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `TEST-200.02` | 20–20 | ناحیه | `system-update.php در سطح فایل فقط تابع تعریف می‌کن` | 1 | ⬜ |
| `TEST-200.03` | 21–24 | تابع | `db` | 0 | ⬜ |
| `TEST-200.04` | 25–27 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-200.05` | 28–31 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-200.06` | 32–35 | تابع | `cfg` | 0 | ⬜ |
| `TEST-200.07` | 36–39 | ناحیه | `بلوک رویه‌ای 36-39` | 1 | ⬜ |
| `TEST-200.08` | 40–47 | تابع | `expect` | 0 | ⬜ |
| `TEST-200.09` | 49–53 | تابع | `allowed` | 0 | ⬜ |
| `TEST-200.10` | 55–59 | ناحیه | `پیش‌نیاز برآورده` | 0 | ⬜ |
| `TEST-200.11` | 60–64 | ناحیه | `پیش‌نیاز برآورده نیست` | 0 | ⬜ |
| `TEST-200.12` | 65–71 | ناحیه | `متن خطا باید قابل اقدام باشد` | 0 | ⬜ |
| `TEST-200.13` | 72–78 | ناحیه | `سازگاری با بسته‌های قدیمی` | 0 | ⬜ |
| `TEST-200.14` | 79–83 | ناحیه | `درهای خروج` | 0 | ⬜ |
| `TEST-200.15` | 84–87 | ناحیه | `نسخه‌های با تعداد جزء متفاوت` | 0 | ⬜ |
| `TEST-200.16` | 88–96 | ناحیه | `بستهٔ واقعی این نسخه` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-201</b> — <code>tests/test_technical_profile_form_refresh_static.py</code> (68 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-201.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-201.02` | 16–37 | بلوک | `test_current_profile_module_has_complete_form_and_` | 0 | ⬜ |
| `TEST-201.03` | 38–68 | بلوک | `test_form_hotfix_packages_the_renderer_styles_and_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-202</b> — <code>tests/test_ticket_auto_assignment_static.py</code> (26 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-202.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-202.02` | 7–7 | بلوک | `TicketAutoAssignmentStaticTests` | 0 | ⬜ |
| `TEST-202.03` | 8–17 | بلوک | `test_regular_managers_are_limited_to_the_ticket_de` | 0 | ⬜ |
| `TEST-202.04` | 18–26 | بلوک | `test_ticket_creation_passes_its_resolved_departmen` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-203</b> — <code>tests/test_ticket_cancellation_static.py</code> (140 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-203.01` | 1–22 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-203.02` | 23–140 | بلوک | `check` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-204</b> — <code>tests/test_ticket_lifecycle_guide_static.py</code> (42 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-204.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-204.02` | 7–8 | بلوک | `TicketLifecycleGuideStaticTests` | 0 | ⬜ |
| `TEST-204.03` | 9–11 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-204.04` | 12–16 | بلوک | `test_role_guide_is_linked_from_the_toc` | 0 | ⬜ |
| `TEST-204.05` | 17–34 | بلوک | `test_lifecycle_explains_roles_and_closure_path` | 0 | ⬜ |
| `TEST-204.06` | 35–42 | بلوک | `test_resolved_ticket_instructions_use_the_actual_r` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-205</b> — <code>tests/test_ticket_number_autofill_unit.php</code> (159 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-205.01` | 1–29 | ناحیه | `بلوک رویه‌ای 1-29` | 0 | ⬜ |
| `TEST-205.02` | 30–40 | تابع | `tn_missing_rows` | 0 | ⬜ |
| `TEST-205.03` | 42–105 | کلاس | `TnStatement` | 1 | ⬜ |
| `TEST-205.04` | 107–113 | کلاس | `TnDb` | 4 | ⬜ |
| `TEST-205.05` | 115–115 | تابع | `db` | 0 | ⬜ |
| `TEST-205.06` | 116–116 | تابع | `setting` | 0 | ⬜ |
| `TEST-205.07` | 117–117 | تابع | `save_setting` | 0 | ⬜ |
| `TEST-205.08` | 118–118 | تابع | `db_in_transaction` | 0 | ⬜ |
| `TEST-205.09` | 119–119 | تابع | `cfg` | 0 | ⬜ |
| `TEST-205.10` | 120–122 | ناحیه | `بلوک رویه‌ای 120-122` | 0 | ⬜ |
| `TEST-205.11` | 123–127 | تابع | `tn_check` | 0 | ⬜ |
| `TEST-205.12` | 129–132 | ناحیه | `۱) کف شمارنده از بزرگ‌ترین شمارهٔ موجود خوانده می‌` | 0 | ⬜ |
| `TEST-205.13` | 133–147 | ناحیه | `۲) ترمیم کامل بدون برخورد یکتایی` | 0 | ⬜ |
| `TEST-205.14` | 148–150 | ناحیه | `۳) هر ردیف دقیقاً یک تلاش: یعنی هیچ برخوردی رخ ندا` | 1 | ⬜ |
| `TEST-205.15` | 151–153 | ناحیه | `۴) نشانگر ثبت می‌شود تا اسکن در هر بارگذاری صفحه ت` | 0 | ⬜ |
| `TEST-205.16` | 154–159 | ناحیه | `۵) شمارنده برای تولید شمارهٔ بعدی به‌روز می‌شود` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-206</b> — <code>tests/test_ticket_number_repair_static.py</code> (146 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-206.01` | 1–16 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-206.02` | 17–21 | بلوک | `read` | 0 | ⬜ |
| `TEST-206.03` | 22–28 | بلوک | `autofill_body` | 0 | ⬜ |
| `TEST-206.04` | 29–39 | بلوک | `test_autofill_seeds_counter_from_existing_maximum` | 0 | ⬜ |
| `TEST-206.05` | 40–51 | بلوک | `test_sequence_floor_consults_both_counter_and_exis` | 0 | ⬜ |
| `TEST-206.06` | 52–59 | بلوک | `test_autofill_isolates_each_row_so_one_failure_doe` | 0 | ⬜ |
| `TEST-206.07` | 60–65 | بلوک | `test_autofill_reads_in_batches_and_cannot_loop_for` | 0 | ⬜ |
| `TEST-206.08` | 66–76 | بلوک | `test_completion_marker_is_not_trapped_in_the_empty` | 0 | ⬜ |
| `TEST-206.09` | 77–85 | بلوک | `test_counter_is_persisted_back_to_the_sequence_tab` | 0 | ⬜ |
| `TEST-206.10` | 86–98 | بلوک | `test_fallback_number_is_not_a_fake_lookalike` | 0 | ⬜ |
| `TEST-206.11` | 99–109 | بلوک | `test_chatbot_still_recognises_the_internal_referen` | 0 | ⬜ |
| `TEST-206.12` | 110–121 | بلوک | `test_list_pages_prime_the_number_cache_instead_of_` | 0 | ⬜ |
| `TEST-206.13` | 122–132 | بلوک | `test_cache_prime_helper_exists_and_tolerates_bad_r` | 0 | ⬜ |
| `TEST-206.14` | 133–139 | بلوک | `test_bulk_update_has_a_selection_cap` | 0 | ⬜ |
| `TEST-206.15` | 140–146 | بلوک | `test_bulk_update_reports_tickets_that_were_already` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-207</b> — <code>tests/test_ticket_routing_unit.php</code> (182 خط، 38 آدرس، بازبینی‌شده 0/38)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-207.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-207.02` | 4–5 | ناحیه | `Offline behavioural tests: no real configuration, ` | 0 | ⬜ |
| `TEST-207.03` | 6–9 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `TEST-207.04` | 10–10 | تابع | `is_staff_role` | 0 | ⬜ |
| `TEST-207.05` | 11–11 | تابع | `is_assignable_role` | 0 | ⬜ |
| `TEST-207.06` | 12–12 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `TEST-207.07` | 13–13 | تابع | `is_it_agent` | 0 | ⬜ |
| `TEST-207.08` | 14–16 | تابع | `user_can` | 0 | ⬜ |
| `TEST-207.09` | 17–17 | تابع | `permission_roles` | 0 | ⬜ |
| `TEST-207.10` | 18–18 | تابع | `permission_table_ready` | 0 | ⬜ |
| `TEST-207.11` | 19–19 | تابع | `role_permissions_custom` | 0 | ⬜ |
| `TEST-207.12` | 20–20 | تابع | `role_permission_effective_codes` | 0 | ⬜ |
| `TEST-207.13` | 21–34 | کلاس | `FakeTicketStatement` | 0 | ⬜ |
| `TEST-207.14` | 35–38 | کلاس | `FakeTicketDatabase` | 0 | ⬜ |
| `TEST-207.15` | 39–39 | تابع | `db` | 0 | ⬜ |
| `TEST-207.16` | 40–41 | ناحیه | `بلوک رویه‌ای 40-41` | 0 | ⬜ |
| `TEST-207.17` | 42–45 | تابع | `check_ticket` | 0 | ⬜ |
| `TEST-207.18` | 46–102 | ناحیه | `بلوک رویه‌ای 46-102` | 0 | ⬜ |
| `TEST-207.19` | 103–103 | ناحیه | `تفکیک وظایف و سه نما (۱.۶۲.۸۴)` | 0 | ⬜ |
| `TEST-207.20` | 104–108 | ناحیه | `سناریو: مدیر حوزه (کاربر ۴، نقش manager، همان گروه` | 0 | ⬜ |
| `TEST-207.21` | 109–111 | ناحیه | `همان مدیر روی درخواست دیگران همچنان سرپرست است.` | 0 | ⬜ |
| `TEST-207.22` | 112–114 | ناحیه | `کارشناس مسئول: نمای کارشناس، نه سرپرست.` | 0 | ⬜ |
| `TEST-207.23` | 115–117 | ناحیه | `ثبت‌کنندهٔ عادی.` | 0 | ⬜ |
| `TEST-207.24` | 118–134 | ناحیه | `استثنای مهم: کسی که هم ثبت‌کننده و هم کارشناس مسئو` | 0 | ⬜ |
| `TEST-207.25` | 135–135 | ناحیه | `تفکیک وظایف در لحظهٔ ارجاع و تقویم کاری (بستهٔ ۶ —` | 0 | ⬜ |
| `TEST-207.26` | 136–136 | ناحیه | `بستهٔ ۵ نمای پس از ارجاع را بست؛ اینجا خودِ لحظهٔ ` | 0 | ⬜ |
| `TEST-207.27` | 137–138 | ناحیه | `$users[2] کارشناس پشتیبانی فعال و واجد شرایط است (` | 0 | ⬜ |
| `TEST-207.28` | 139–142 | ناحیه | `۱) کارشناس روی درخواست دیگری همچنان واجد شرایط است` | 0 | ⬜ |
| `TEST-207.29` | 143–146 | ناحیه | `۲) همان کارشناس روی درخواستی که خودش ثبت کرده، دیگ` | 0 | ⬜ |
| `TEST-207.30` | 147–158 | ناحیه | `۳) ادمین اصلی هم مستثنا نیست؛ میان‌بُر نقش نباید گ` | 0 | ⬜ |
| `TEST-207.31` | 159–159 | ناحیه | `۴) درخواست بدون ثبت‌کنندهٔ مشخص (requester_id = 0)` | 0 | ⬜ |
| `TEST-207.32` | 160–163 | ناحیه | `choose_handling_agent() در حالت پیش‌فرض دقیقاً همی` | 0 | ⬜ |
| `TEST-207.33` | 164–164 | ناحیه | `۵) سازگاری با بستهٔ ۵: کسی که هم ثبت‌کننده و هم مس` | 0 | ⬜ |
| `TEST-207.34` | 165–169 | ناحیه | `از دست نمی‌دهد. گارد تازه فقط ارجاعِ تازه را می‌بن` | 0 | ⬜ |
| `TEST-207.35` | 170–170 | ناحیه | `۶) دریچهٔ اضطراری واحد تک‌نفره: وقتی security.allo` | 0 | ⬜ |
| `TEST-207.36` | 171–171 | ناحیه | `همان کاربر دوباره واجد شرایط می‌شود. cfg() از پیکر` | 0 | ⬜ |
| `TEST-207.37` | 172–177 | ناحیه | `چون کلید پیش‌فرض تعریف نشده، همهٔ بررسی‌های بالاتر` | 0 | ⬜ |
| `TEST-207.38` | 178–181 | تابع | `cfg` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-208</b> — <code>tests/test_ticket_scope_sql_node.js</code> (22 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-208.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-208.02` | 6–6 | بلوک | `user_is_primary_admin` | 0 | ⬜ |
| `TEST-208.03` | 7–7 | بلوک | `is_global_ticket_role` | 0 | ⬜ |
| `TEST-208.04` | 8–8 | بلوک | `user_can` | 0 | ⬜ |
| `TEST-208.05` | 9–9 | بلوک | `is_it_agent` | 0 | ⬜ |
| `TEST-208.06` | 10–10 | بلوک | `S` | 0 | ⬜ |
| `TEST-208.07` | 11–11 | بلوک | `D` | 0 | ⬜ |
| `TEST-208.08` | 12–22 | بلوک | `db` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-209</b> — <code>tests/test_ticket_sla_unit.php</code> (52 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-209.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-209.02` | 4–5 | ناحیه | `Tests use an in-memory event stub, never the produ` | 0 | ⬜ |
| `TEST-209.03` | 6–6 | تابع | `cfg` | 0 | ⬜ |
| `TEST-209.04` | 7–7 | تابع | `ticket_is_waiting_status` | 0 | ⬜ |
| `TEST-209.05` | 8–8 | تابع | `is_sla_holiday` | 0 | ⬜ |
| `TEST-209.06` | 9–12 | کلاس | `SlaFakeStatement` | 0 | ⬜ |
| `TEST-209.07` | 13–15 | کلاس | `SlaFakeDb` | 0 | ⬜ |
| `TEST-209.08` | 16–16 | تابع | `db` | 0 | ⬜ |
| `TEST-209.09` | 17–17 | ناحیه | `بلوک رویه‌ای 17-17` | 0 | ⬜ |
| `TEST-209.10` | 18–21 | تابع | `sla_check` | 0 | ⬜ |
| `TEST-209.11` | 22–52 | ناحیه | `بلوک رویه‌ای 22-52` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-210</b> — <code>tests/test_ticket_three_views_static.py</code> (155 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-210.01` | 1–30 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-210.02` | 31–35 | بلوک | `read` | 0 | ⬜ |
| `TEST-210.03` | 36–42 | بلوک | `php_function` | 0 | ⬜ |
| `TEST-210.04` | 43–50 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-210.05` | 51–64 | بلوک | `test_handler_level_denies_supervisor_authority_ove` | 0 | ⬜ |
| `TEST-210.06` | 65–76 | بلوک | `test_a_requester_who_is_also_the_assigned_agent_ca` | 0 | ⬜ |
| `TEST-210.07` | 77–87 | بلوک | `test_reply_guard_keeps_its_explicit_requester_chec` | 0 | ⬜ |
| `TEST-210.08` | 88–100 | بلوک | `test_a_single_function_answers_which_view_to_rende` | 0 | ⬜ |
| `TEST-210.09` | 101–112 | بلوک | `test_the_ticket_page_uses_the_single_view_function` | 0 | ⬜ |
| `TEST-210.10` | 113–125 | بلوک | `test_internal_notes_are_hidden_from_the_requester_` | 0 | ⬜ |
| `TEST-210.11` | 126–133 | بلوک | `test_message_list_no_longer_decides_by_global_role` | 0 | ⬜ |
| `TEST-210.12` | 134–146 | بلوک | `test_internal_attachment_download_uses_the_same_ru` | 0 | ⬜ |
| `TEST-210.13` | 147–155 | بلوک | `test_scope_isolation_rules_are_untouched` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-211</b> — <code>tests/test_ticket_tree_visibility_unit.php</code> (41 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-211.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-211.02` | 4–4 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `TEST-211.03` | 5–5 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `TEST-211.04` | 6–6 | تابع | `is_it_agent` | 0 | ⬜ |
| `TEST-211.05` | 7–7 | تابع | `user_can` | 0 | ⬜ |
| `TEST-211.06` | 8–17 | کلاس | `ScopeStmt` | 0 | ⬜ |
| `TEST-211.07` | 18–21 | کلاس | `ScopeDB` | 0 | ⬜ |
| `TEST-211.08` | 22–22 | تابع | `db` | 0 | ⬜ |
| `TEST-211.09` | 23–23 | ناحیه | `بلوک رویه‌ای 23-23` | 0 | ⬜ |
| `TEST-211.10` | 24–24 | تابع | `ok` | 0 | ⬜ |
| `TEST-211.11` | 25–41 | ناحیه | `بلوک رویه‌ای 25-41` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-212</b> — <code>tests/test_ticket_workflow_safety_static.py</code> (109 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-212.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-212.02` | 7–10 | بلوک | `source` | 0 | ⬜ |
| `TEST-212.03` | 11–14 | بلوک | `section` | 0 | ⬜ |
| `TEST-212.04` | 15–15 | بلوک | `TicketWorkflowSafetyTests` | 0 | ⬜ |
| `TEST-212.05` | 16–26 | بلوک | `test_notification_candidates_are_checked_for_actua` | 0 | ⬜ |
| `TEST-212.06` | 27–40 | بلوک | `test_assignment_checks_existing_assignee_and_role_` | 1 | ⬜ |
| `TEST-212.07` | 41–52 | بلوک | `test_requester_and_nonhandler_reply_guards` | 2 | ⬜ |
| `TEST-212.08` | 53–65 | بلوک | `test_concurrent_rating_and_auto_close_revalidate_u` | 1 | ⬜ |
| `TEST-212.09` | 66–71 | بلوک | `test_claim_uses_full_assignee_eligibility` | 0 | ⬜ |
| `TEST-212.10` | 72–78 | بلوک | `test_supervisor_actions_check_ticket_visibility` | 0 | ⬜ |
| `TEST-212.11` | 79–86 | بلوک | `test_autoclose_candidates_do_not_starve_on_current` | 0 | ⬜ |
| `TEST-212.12` | 87–92 | بلوک | `test_reopen_sla_cycle_is_used_on_priority_change` | 0 | ⬜ |
| `TEST-212.13` | 93–109 | بلوک | `test_direct_cancel_and_bulk_defend_stale_assignmen` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-213</b> — <code>tests/test_traffic_69_link_static.py</code> (25 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-213.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-213.02` | 5–14 | بلوک | `test_migration_is_additive_idempotent_and_does_not` | 0 | ⬜ |
| `TEST-213.03` | 15–25 | بلوک | `test_shared_preview_export_and_real_destination_so` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-214</b> — <code>tests/test_traffic_70_upgrade_static.py</code> (20 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-214.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-214.02` | 5–12 | بلوک | `test_cumulative_migration_only_backfills_when_019_` | 0 | ⬜ |
| `TEST-214.03` | 13–20 | بلوک | `test_report_has_cache_busted_popup_assets` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-215</b> — <code>tests/test_traffic_destination_approver_static.py</code> (54 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-215.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-215.02` | 7–13 | بلوک | `test_destination_approver_is_stored_in_all_schema_` | 0 | ⬜ |
| `TEST-215.03` | 14–31 | بلوک | `test_destination_admin_can_create_edit_and_resolve` | 2 | ⬜ |
| `TEST-215.04` | 32–44 | بلوک | `test_approver_autofills_for_new_and_edit_forms_and` | 0 | ⬜ |
| `TEST-215.05` | 45–54 | بلوک | `test_release_manifest_and_download_page_list_new_d` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-216</b> — <code>tests/test_traffic_destination_popup_browser.js</code> (66 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-216.01` | 1–28 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-216.02` | 29–66 | بلوک | `geometry` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-217</b> — <code>tests/test_traffic_edit_history_static.py</code> (70 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-217.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-217.02` | 6–14 | بلوک | `test_traffic_edit_form_includes_visit_date_and_bot` | 0 | ⬜ |
| `TEST-217.03` | 15–25 | بلوک | `test_update_and_exit_changes_are_written_transacti` | 0 | ⬜ |
| `TEST-217.04` | 26–35 | بلوک | `test_history_schema_exists_in_migration_and_fresh_` | 0 | ⬜ |
| `TEST-217.05` | 36–42 | بلوک | `test_food_print_page_treats_layout_design_as_optio` | 0 | ⬜ |
| `TEST-217.06` | 43–49 | بلوک | `test_shared_header_does_not_fail_when_layout_desig` | 0 | ⬜ |
| `TEST-217.07` | 50–57 | بلوک | `test_food_panel_skips_invalid_empty_search_replace` | 0 | ⬜ |
| `TEST-217.08` | 58–70 | بلوک | `test_traffic_edit_times_use_unambiguous_24_hour_te` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-218</b> — <code>tests/test_traffic_form_render_node.js</code> (16 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-218.01` | 1–16 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-219</b> — <code>tests/test_traffic_import_static.py</code> (68 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-219.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-219.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-219.03` | 11–11 | بلوک | `TrafficImportStaticTest` | 0 | ⬜ |
| `TEST-219.04` | 12–25 | بلوک | `test_import_page_is_direct_only_and_requires_exist` | 0 | ⬜ |
| `TEST-219.05` | 26–39 | بلوک | `test_post_import_is_csrf_protected_and_confirmatio` | 0 | ⬜ |
| `TEST-219.06` | 40–54 | بلوک | `test_parser_limits_duplicate_protection_and_privac` | 0 | ⬜ |
| `TEST-219.07` | 55–68 | بلوک | `test_help_and_chatbot_surface_the_hidden_workflow` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-220</b> — <code>tests/test_traffic_live_node.js</code> (41 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-220.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-220.02` | 14–41 | بلوک | `reply` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-221</b> — <code>tests/test_traffic_live_report_unit.php</code> (74 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-221.01` | 1–8 | ناحیه | `بلوک رویه‌ای 1-8` | 0 | ⬜ |
| `TEST-221.02` | 9–9 | تابع | `db` | 0 | ⬜ |
| `TEST-221.03` | 10–10 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-221.04` | 11–11 | تابع | `save_audit_required` | 0 | ⬜ |
| `TEST-221.05` | 12–12 | تابع | `log_sort_order` | 0 | ⬜ |
| `TEST-221.06` | 13–13 | تابع | `e` | 0 | ⬜ |
| `TEST-221.07` | 14–14 | تابع | `user_can` | 0 | ⬜ |
| `TEST-221.08` | 15–15 | تابع | `valid_iranian_national_code` | 0 | ⬜ |
| `TEST-221.09` | 16–16 | تابع | `csrf_field` | 0 | ⬜ |
| `TEST-221.10` | 17–17 | کلاس | `ExportDone` | 0 | ⬜ |
| `TEST-221.11` | 18–18 | تابع | `excel_download` | 0 | ⬜ |
| `TEST-221.12` | 19–20 | ناحیه | `بلوک رویه‌ای 19-20` | 0 | ⬜ |
| `TEST-221.13` | 21–21 | تابع | `check` | 0 | ⬜ |
| `TEST-221.14` | 22–74 | ناحیه | `بلوک رویه‌ای 22-74` | **8** | ⬜ |

</details>

<details>
<summary><b>TEST-222</b> — <code>tests/test_traffic_phone_and_inspection_reminder_static.py</code> (55 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-222.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-222.02` | 6–20 | بلوک | `test_traffic_phone_has_fixed_09_and_requires_exact` | 0 | ⬜ |
| `TEST-222.03` | 21–33 | بلوک | `test_inspection_only_reminder_is_scheduled_daily_a` | 0 | ⬜ |
| `TEST-222.04` | 34–41 | بلوک | `test_reminder_visual_is_centered_and_alarm_styled` | 0 | ⬜ |
| `TEST-222.05` | 42–55 | بلوک | `test_only_primary_admin_can_toggle_reminder_below_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-223</b> — <code>tests/test_traffic_report_fields_node.js</code> (35 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-223.01` | 1–35 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-224</b> — <code>tests/test_transaction_truthfulness_static.py</code> (162 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-224.01` | 1–30 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-224.02` | 31–35 | بلوک | `read` | 0 | ⬜ |
| `TEST-224.03` | 36–46 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-224.04` | 47–53 | بلوک | `function_body` | 0 | ⬜ |
| `TEST-224.05` | 54–59 | بلوک | `function_code` | 0 | ⬜ |
| `TEST-224.06` | 60–80 | بلوک | `test_commit_returns_true_when_the_work_is_already_` | 0 | ⬜ |
| `TEST-224.07` | 81–87 | بلوک | `test_commit_still_reports_a_genuine_failure_as_fal` | 0 | ⬜ |
| `TEST-224.08` | 88–109 | بلوک | `test_a_strict_commit_helper_exists_and_raises_a_pe` | 0 | ⬜ |
| `TEST-224.09` | 110–118 | بلوک | `test_every_ticket_write_path_uses_the_strict_commi` | 0 | ⬜ |
| `TEST-224.10` | 119–131 | بلوک | `test_strict_commit_calls_name_the_action_for_diagn` | 0 | ⬜ |
| `TEST-224.11` | 132–141 | بلوک | `test_other_modules_keep_their_existing_commit_cont` | 0 | ⬜ |
| `TEST-224.12` | 142–156 | بلوک | `test_auto_assignment_does_not_invent_a_state_the_t` | 0 | ⬜ |
| `TEST-224.13` | 157–162 | بلوک | `test_creation_event_records_the_real_initial_statu` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-225</b> — <code>tests/test_triangle_effect_static.py</code> (102 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-225.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-225.02` | 9–10 | بلوک | `TriangleEffectReleaseTests` | 0 | ⬜ |
| `TEST-225.03` | 11–20 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-225.04` | 21–33 | بلوک | `test_catalog_has_37_valid_non_print_routes` | 0 | ⬜ |
| `TEST-225.05` | 34–51 | بلوک | `test_configuration_keys_match_the_engine` | 0 | ⬜ |
| `TEST-225.06` | 52–59 | بلوک | `test_settings_are_saved_with_existing_permission_a` | 0 | ⬜ |
| `TEST-225.07` | 60–68 | بلوک | `test_engine_respects_reduced_motion_and_has_explic` | 0 | ⬜ |
| `TEST-225.08` | 69–81 | بلوک | `test_interaction_controls_are_wired` | 1 | ⬜ |
| `TEST-225.09` | 82–90 | بلوک | `test_help_and_chatbot_cover_the_feature` | 0 | ⬜ |
| `TEST-225.10` | 91–102 | بلوک | `test_release_manifest_is_migration_free` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-226</b> — <code>tests/test_tts_normalize_static.py</code> (56 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-226.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-226.02` | 13–56 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-227</b> — <code>tests/test_update_manifest_guard_static.py</code> (59 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-227.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-227.02` | 6–13 | بلوک | `test_update_installer_checks_zip_open_and_extracti` | 1 | ⬜ |
| `TEST-227.03` | 14–22 | بلوک | `test_update_installer_rejects_traversal_duplicates` | 1 | ⬜ |
| `TEST-227.04` | 23–29 | بلوک | `test_manifest_file_list_and_optional_sha256_are_ve` | 1 | ⬜ |
| `TEST-227.05` | 30–42 | بلوک | `test_cumulative_update_contains_security_scope_run` | 0 | ⬜ |
| `TEST-227.06` | 43–59 | بلوک | `test_cumulative_update_includes_all_logging_migrat` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-228</b> — <code>tests/test_user_access_groups_static.py</code> (90 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-228.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-228.02` | 7–8 | بلوک | `UserAccessGroupStaticTests` | 0 | ⬜ |
| `TEST-228.03` | 9–16 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-228.04` | 17–30 | بلوک | `test_group_permissions_are_additive_and_primary_ad` | 0 | ⬜ |
| `TEST-228.05` | 31–42 | بلوک | `test_schema_supports_many_groups_per_user_and_casc` | 1 | ⬜ |
| `TEST-228.06` | 43–59 | بلوک | `test_create_update_and_delete_are_primary_admin_on` | 0 | ⬜ |
| `TEST-228.07` | 60–80 | بلوک | `test_settings_ui_reuses_permission_catalog_and_mem` | 0 | ⬜ |
| `TEST-228.08` | 81–90 | بلوک | `test_special_role_constraints_and_documentation_ar` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-229</b> — <code>tests/test_version_source_of_truth_static.py</code> (208 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-229.01` | 1–48 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-229.02` | 49–53 | بلوک | `version_tuple` | 0 | ⬜ |
| `TEST-229.03` | 54–57 | بلوک | `repo_version` | 0 | ⬜ |
| `TEST-229.04` | 58–90 | بلوک | `scan_packages` | 0 | ⬜ |
| `TEST-229.05` | 91–105 | بلوک | `resolve_repo_path` | 0 | ⬜ |
| `TEST-229.06` | 106–110 | بلوک | `newest_package_for` | 0 | ⬜ |
| `TEST-229.07` | 111–111 | بلوک | `VersionSourceOfTruthTests` | 0 | ⬜ |
| `TEST-229.08` | 112–126 | بلوک | `test_repo_version_is_not_behind_the_newest_publish` | 0 | ⬜ |
| `TEST-229.09` | 127–164 | بلوک | `test_repo_matches_the_newest_package_of_the_curren` | 0 | ⬜ |
| `TEST-229.10` | 165–193 | بلوک | `test_package_manifest_version_matches_its_own_vers` | 0 | ⬜ |
| `TEST-229.11` | 194–208 | بلوک | `test_docs_do_not_advertise_an_older_version_than_v` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-230</b> — <code>tests/test_windows_logging_static.py</code> (78 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-230.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-230.02` | 6–18 | بلوک | `test_windows_logging_acceptance_assets_exist` | 0 | ⬜ |
| `TEST-230.03` | 19–26 | بلوک | `test_acl_verifier_is_read_only_and_strict_mode_is_` | 0 | ⬜ |
| `TEST-230.04` | 27–32 | بلوک | `test_acl_verifier_detects_interactive_and_guest_pr` | 0 | ⬜ |
| `TEST-230.05` | 33–39 | بلوک | `test_web_protection_verifier_preserves_application` | 0 | ⬜ |
| `TEST-230.06` | 40–46 | بلوک | `test_acl_setter_uses_least_privilege_for_service_a` | 0 | ⬜ |
| `TEST-230.07` | 47–53 | بلوک | `test_acl_setter_handles_script_whatif_without_null` | 0 | ⬜ |
| `TEST-230.08` | 54–61 | بلوک | `test_installed_acceptance_script_is_version_pinned` | 0 | ⬜ |
| `TEST-230.09` | 62–78 | بلوک | `test_installed_acceptance_checks_complete_logging_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-231</b> — <code>tests/tts_normalize_test.php</code> (189 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-231.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `TEST-231.02` | 14–17 | تابع | `setting` | 0 | ⬜ |
| `TEST-231.03` | 18–19 | ناحیه | `بلوک رویه‌ای 18-19` | 0 | ⬜ |
| `TEST-231.04` | 20–23 | تابع | `valid_choice` | 0 | ⬜ |
| `TEST-231.05` | 24–25 | ناحیه | `بلوک رویه‌ای 24-25` | 0 | ⬜ |
| `TEST-231.06` | 26–28 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-231.07` | 29–30 | ناحیه | `بلوک رویه‌ای 29-30` | 0 | ⬜ |
| `TEST-231.08` | 31–34 | تابع | `e` | 0 | ⬜ |
| `TEST-231.09` | 35–36 | ناحیه | `بلوک رویه‌ای 35-36` | 0 | ⬜ |
| `TEST-231.10` | 37–40 | تابع | `csrf_field` | 0 | ⬜ |
| `TEST-231.11` | 41–52 | ناحیه | `بلوک رویه‌ای 41-52` | 0 | ⬜ |
| `TEST-231.12` | 53–58 | تابع | `tts_bare` | 0 | ⬜ |
| `TEST-231.13` | 60–70 | تابع | `expect_true` | 0 | ⬜ |
| `TEST-231.14` | 72–75 | تابع | `expect_eq` | 0 | ⬜ |
| `TEST-231.15` | 77–80 | تابع | `expect_contains` | 0 | ⬜ |
| `TEST-231.16` | 82–85 | تابع | `expect_not_contains` | 0 | ⬜ |
| `TEST-231.17` | 86–189 | ناحیه | `بلوک رویه‌ای 86-189` | 0 | ⬜ |

</details>

### TOOL — `TOOL`


<details>
<summary><b>TOOL-001</b> — <code>tools/asset_dedupe.php</code> (230 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-001.01` | 1–230 | ناحیه | `بلوک رویه‌ای 1-230` | **13** | ⬜ |

</details>

<details>
<summary><b>TOOL-002</b> — <code>tools/backup-files.php</code> (106 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-002.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 1 | ⬜ |
| `TOOL-002.02` | 21–35 | ناحیه | `کلید سادهٔ دسترسی — بعد از گرفتن بکاپ، این فایل را` | 2 | ⬜ |
| `TOOL-002.03` | 36–97 | ناحیه | `پوشههایی که نباید داخل بکاپ بروند (حجیم، موقتی، یا` | 0 | ⬜ |
| `TOOL-002.04` | 98–106 | ناحیه | `هدر دانلود` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-003</b> — <code>tools/benchmark_250.py</code> (333 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-003.01` | 1–26 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-003.02` | 27–34 | بلوک | `percentile` | 0 | ⬜ |
| `TOOL-003.03` | 35–35 | بلوک | `Metrics` | 0 | ⬜ |
| `TOOL-003.04` | 36–43 | بلوک | `__init__` | 0 | ⬜ |
| `TOOL-003.05` | 44–50 | بلوک | `record` | 0 | ⬜ |
| `TOOL-003.06` | 51–70 | بلوک | `sample_php` | 0 | ⬜ |
| `TOOL-003.07` | 71–98 | بلوک | `request` | 0 | ⬜ |
| `TOOL-003.08` | 99–157 | بلوک | `user_flow` | 0 | ⬜ |
| `TOOL-003.09` | 158–333 | بلوک | `main` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-004</b> — <code>tools/benchmark_db_report.php</code> (53 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-004.01` | 1–53 | ناحیه | `بلوک رویه‌ای 1-53` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-005</b> — <code>tools/benchmark_operations.py</code> (485 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-005.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-005.02` | 25–31 | بلوک | `percentile` | 0 | ⬜ |
| `TOOL-005.03` | 32–32 | بلوک | `Recorder` | 0 | ⬜ |
| `TOOL-005.04` | 33–40 | بلوک | `__init__` | 0 | ⬜ |
| `TOOL-005.05` | 41–48 | بلوک | `add` | 0 | ⬜ |
| `TOOL-005.06` | 49–65 | بلوک | `sample_php` | 0 | ⬜ |
| `TOOL-005.07` | 66–93 | بلوک | `request` | 0 | ⬜ |
| `TOOL-005.08` | 94–101 | بلوک | `hidden_value` | 0 | ⬜ |
| `TOOL-005.09` | 102–113 | بلوک | `selected_option_value` | 0 | ⬜ |
| `TOOL-005.10` | 114–117 | بلوک | `input_value` | 0 | ⬜ |
| `TOOL-005.11` | 118–121 | بلوک | `is_login_page` | 0 | ⬜ |
| `TOOL-005.12` | 122–136 | بلوک | `media_payload` | 1 | ⬜ |
| `TOOL-005.13` | 137–193 | بلوک | `main` | 0 | ⬜ |
| `TOOL-005.14` | 194–284 | بلوک | `worker` | 0 | ⬜ |
| `TOOL-005.15` | 285–485 | بلوک | `post_cd` | 2 | ⬜ |

</details>

<details>
<summary><b>TOOL-006</b> — <code>tools/benchmark_operations_report.php</code> (124 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-006.01` | 1–124 | ناحیه | `بلوک رویه‌ای 1-124` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-007</b> — <code>tools/benchmark_operations_seed.php</code> (52 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-007.01` | 1–52 | ناحیه | `بلوک رویه‌ای 1-52` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-008</b> — <code>tools/benchmark_seed.php</code> (140 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-008.01` | 1–35 | ناحیه | `بلوک رویه‌ای 1-35` | 1 | ⬜ |
| `TOOL-008.02` | 36–140 | ناحیه | `Match the filesystem prerequisite created by insta` | **10** | ⬜ |

</details>

<details>
<summary><b>TOOL-009</b> — <code>tools/benchmark_system_resilience.py</code> (368 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-009.01` | 1–51 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-009.02` | 52–84 | بلوک | `api_routes` | 0 | ⬜ |
| `TOOL-009.03` | 85–85 | بلوک | `ResourceSampler` | 0 | ⬜ |
| `TOOL-009.04` | 86–90 | بلوک | `__init__` | 0 | ⬜ |
| `TOOL-009.05` | 91–107 | بلوک | `run` | 0 | ⬜ |
| `TOOL-009.06` | 108–114 | بلوک | `percentile` | 0 | ⬜ |
| `TOOL-009.07` | 115–178 | بلوک | `main` | 0 | ⬜ |
| `TOOL-009.08` | 179–368 | بلوک | `worker` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-010</b> — <code>tools/build_cumulative_update.py</code> (274 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-010.01` | 1–83 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-010.02` | 84–97 | بلوک | `safe_relative_path` | 0 | ⬜ |
| `TOOL-010.03` | 98–101 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-010.04` | 102–147 | بلوک | `load_base_package` | 1 | ⬜ |
| `TOOL-010.05` | 148–185 | بلوک | `render_download_index` | 1 | ⬜ |
| `TOOL-010.06` | 186–261 | بلوک | `build` | 1 | ⬜ |
| `TOOL-010.07` | 262–274 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-011</b> — <code>tools/build_system_map.py</code> (475 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-011.01` | 1–187 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TOOL-011.02` | 188–205 | بلوک | `iter_files` | 0 | ⬜ |
| `TOOL-011.03` | 206–217 | بلوک | `categorize` | 0 | ⬜ |
| `TOOL-011.04` | 218–224 | بلوک | `read_text` | 0 | ⬜ |
| `TOOL-011.05` | 225–228 | بلوک | `count_lines` | 0 | ⬜ |
| `TOOL-011.06` | 229–246 | بلوک | `استخراج نمادها` | 0 | ⬜ |
| `TOOL-011.07` | 247–289 | بلوک | `extract_symbols` | 0 | ⬜ |
| `TOOL-011.08` | 290–296 | بلوک | `استخراج روتها` | 0 | ⬜ |
| `TOOL-011.09` | 297–302 | بلوک | `extract_routes` | 0 | ⬜ |
| `TOOL-011.10` | 303–324 | بلوک | `add` | 0 | ⬜ |
| `TOOL-011.11` | 325–328 | بلوک | `استخراج جداول` | 0 | ⬜ |
| `TOOL-011.12` | 329–335 | بلوک | `extract_tables` | 0 | ⬜ |
| `TOOL-011.13` | 336–336 | بلوک | `بستههای آپدیت` | 0 | ⬜ |
| `TOOL-011.14` | 337–357 | بلوک | `extract_updates` | 0 | ⬜ |
| `TOOL-011.15` | 358–364 | بلوک | `tsv` | 0 | ⬜ |
| `TOOL-011.16` | 365–431 | بلوک | `build` | 0 | ⬜ |
| `TOOL-011.17` | 432–475 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-012</b> — <code>tools/build_update_1_62_88.py</code> (194 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-012.01` | 1–44 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-012.02` | 45–48 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-012.03` | 49–54 | بلوک | `git` | 0 | ⬜ |
| `TOOL-012.04` | 55–64 | بلوک | `changed_files` | 0 | ⬜ |
| `TOOL-012.05` | 65–78 | بلوک | `safe_archive_path` | 0 | ⬜ |
| `TOOL-012.06` | 79–113 | بلوک | `validate_archive` | 0 | ⬜ |
| `TOOL-012.07` | 114–158 | بلوک | `build` | 1 | ⬜ |
| `TOOL-012.08` | 159–194 | بلوک | `entry` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-013</b> — <code>tools/ci_load_schema.php</code> (107 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-013.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `TOOL-013.02` | 26–31 | تابع | `ci_error` | 0 | ⬜ |
| `TOOL-013.03` | 32–50 | ناحیه | `بلوک رویه‌ای 32-50` | 1 | ⬜ |
| `TOOL-013.04` | 51–107 | ناحیه | `تقسیم بر اساس «;» با رعایت رشته‌های تک‌نقل‌قول (دو` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-014</b> — <code>tools/code_index.php</code> (571 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-014.01` | 1–33 | ناحیه | `بلوک رویه‌ای 1-33` | 0 | ⬜ |
| `TOOL-014.02` | 34–42 | تابع | `ci_strlen` | 0 | ⬜ |
| `TOOL-014.03` | 44–51 | تابع | `ci_substr` | 0 | ⬜ |
| `TOOL-014.04` | 53–65 | تابع | `ci_area` | 0 | ⬜ |
| `TOOL-014.05` | 67–73 | تابع | `ci_fingerprint` | 0 | ⬜ |
| `TOOL-014.06` | 75–112 | تابع | `ci_anchor_label` | 0 | ⬜ |
| `TOOL-014.07` | 114–160 | تابع | `ci_risk` | 0 | ⬜ |
| `TOOL-014.08` | 162–363 | تابع | `ci_extract_php` | 7 | ⬜ |
| `TOOL-014.09` | 365–395 | تابع | `ci_extract_plain` | 0 | ⬜ |
| `TOOL-014.10` | 397–418 | تابع | `ci_absorb_docblock` | 0 | ⬜ |
| `TOOL-014.11` | 420–449 | تابع | `ci_extract_sql` | 1 | ⬜ |
| `TOOL-014.12` | 451–479 | تابع | `ci_blocks_from_anchors` | 0 | ⬜ |
| `TOOL-014.13` | 481–484 | تابع | `ci_slice` | 0 | ⬜ |
| `TOOL-014.14` | 486–494 | تابع | `ci_token_line` | 0 | ⬜ |
| `TOOL-014.15` | 496–513 | تابع | `ci_next_name` | 0 | ⬜ |
| `TOOL-014.16` | 515–553 | تابع | `ci_build` | 0 | ⬜ |
| `TOOL-014.17` | 555–571 | ناحیه | `اجرای مستقیم از خط فرمان (در php-wasm این بخش اجرا` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-015</b> — <code>tools/db_health_report.php</code> (129 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-015.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-015.02` | 22–25 | تابع | `dbh_line` | 3 | ⬜ |
| `TOOL-015.03` | 26–35 | ناحیه | `بلوک رویه‌ای 26-35` | 0 | ⬜ |
| `TOOL-015.04` | 36–51 | ناحیه | `۱) اندازه و تعداد ردیف جداول` | 0 | ⬜ |
| `TOOL-015.05` | 52–69 | ناحیه | `۲) کوئری‌های کند ۷ روز اخیر (از لاگ فنی؛ کوئری‌های` | 0 | ⬜ |
| `TOOL-015.06` | 70–129 | ناحیه | `۳) EXPLAIN کوئری‌های داغ` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-016</b> — <code>tools/diag_asset_duplicates.php</code> (79 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-016.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `TOOL-016.02` | 14–14 | تابع | `out` | 3 | ⬜ |
| `TOOL-016.03` | 15–15 | تابع | `q` | 0 | ⬜ |
| `TOOL-016.04` | 16–23 | ناحیه | `بلوک رویه‌ای 16-23` | 0 | ⬜ |
| `TOOL-016.05` | 24–29 | ناحیه | `۱) تکرار بر اساس hostname دقیق` | 0 | ⬜ |
| `TOOL-016.06` | 30–48 | ناحیه | `۲) تکرار بر اساس نام نمایشی نرمالشده (همون چی که ت` | 0 | ⬜ |
| `TOOL-016.07` | 49–54 | ناحیه | `۳) پراکندگی منبع` | 0 | ⬜ |
| `TOOL-016.08` | 55–59 | ناحیه | `۴) چند ردیف با hostname خالی` | 0 | ⬜ |
| `TOOL-016.09` | 60–66 | ناحیه | `۵) source نامعتبر` | 0 | ⬜ |
| `TOOL-016.10` | 67–72 | ناحیه | `۶) asset_profiles یتیم / خالی` | 0 | ⬜ |
| `TOOL-016.11` | 73–79 | ناحیه | `۷) تگهای خودکار DOMAIN- و CLIENT-` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-017</b> — <code>tools/diag_asset_profile.php</code> (163 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-017.01` | 1–163 | ناحیه | `بلوک رویه‌ای 1-163` | **9** | ⬜ |

</details>

<details>
<summary><b>TOOL-018</b> — <code>tools/diag_connection.php</code> (258 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-018.01` | 1–258 | ناحیه | `بلوک رویه‌ای 1-258` | **20** | ⬜ |

</details>

<details>
<summary><b>TOOL-019</b> — <code>tools/diag_food_duplicates.php</code> (31 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-019.01` | 1–31 | ناحیه | `بلوک رویه‌ای 1-31` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-020</b> — <code>tools/diag_food_match.php</code> (111 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-020.01` | 1–42 | ناحیه | `بلوک رویه‌ای 1-42` | 0 | ⬜ |
| `TOOL-020.02` | 43–57 | ناحیه | `کاربران` | 0 | ⬜ |
| `TOOL-020.03` | 58–91 | ناحیه | `SOURCE_TABLE` | 0 | ⬜ |
| `TOOL-020.04` | 92–111 | ناحیه | `سفارش داخلی` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-021</b> — <code>tools/diag_food_ticket.php</code> (103 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-021.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `TOOL-021.02` | 27–48 | ناحیه | `Internal orders diagnostic (aggregate only; no Acc` | 0 | ⬜ |
| `TOOL-021.03` | 49–67 | ناحیه | `User lookup 377` | 0 | ⬜ |
| `TOOL-021.04` | 68–103 | ناحیه | `نمونهٔ ردیف‌های منبع تردد` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-022</b> — <code>tools/diag_netprint.php</code> (112 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-022.01` | 1–112 | ناحیه | `بلوک رویه‌ای 1-112` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-023</b> — <code>tools/diag_print_margin.php</code> (313 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-023.01` | 1–49 | ناحیه | `بلوک رویه‌ای 1-49` | 3 | ⬜ |
| `TOOL-023.02` | 50–77 | ناحیه | `۱) تنظیمات چاپ` | 1 | ⬜ |
| `TOOL-023.03` | 78–103 | ناحیه | `۲) مسیر فعال چاپ` | 4 | ⬜ |
| `TOOL-023.04` | 104–129 | ناحیه | `۲.۱) ترازِ دقیق لبه‌ها (۱.۳۷): شروع چاپ = لبهٔ کاغ` | 0 | ⬜ |
| `TOOL-023.05` | 130–158 | ناحیه | `۳) قالب فعال و فاصلهٔ بالای محتوا` | 0 | ⬜ |
| `TOOL-023.06` | 159–186 | ناحیه | `مدل نهایی (پس از حذف عناصر بدون مقدار و تراز خودکا` | 0 | ⬜ |
| `TOOL-023.07` | 187–247 | ناحیه | `۴) رسم واقعی و شمارش ردیف‌های سفید` | 1 | ⬜ |
| `TOOL-023.08` | 248–313 | ناحیه | `۵) اندازه‌گیری واقعی درایور ویندوز (بدون چاپ)` | 7 | ⬜ |

</details>

<details>
<summary><b>TOOL-024</b> — <code>tools/diag_source_row_delete.php</code> (128 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-024.01` | 1–128 | ناحیه | `بلوک رویه‌ای 1-128` | **12** | ⬜ |

</details>

<details>
<summary><b>TOOL-025</b> — <code>tools/diagnose_ldap.php</code> (111 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-025.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 0 | ⬜ |
| `TOOL-025.02` | 28–36 | ناحیه | `۱) افزونه LDAP` | 0 | ⬜ |
| `TOOL-025.03` | 37–59 | ناحیه | `۲) تنظیمات` | 0 | ⬜ |
| `TOOL-025.04` | 60–71 | ناحیه | `۳) اتصال` | 3 | ⬜ |
| `TOOL-025.05` | 72–88 | ناحیه | `۴) Bind` | 2 | ⬜ |
| `TOOL-025.06` | 89–111 | ناحیه | `۵) جست‌وجو` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-026</b> — <code>tools/export_domain_users.php</code> (87 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-026.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-026.02` | 4–65 | تابع | `dud_export` | 0 | ⬜ |
| `TOOL-026.03` | 67–82 | تابع | `dud_main` | 0 | ⬜ |
| `TOOL-026.04` | 83–87 | ناحیه | `بلوک رویه‌ای 83-87` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-027</b> — <code>tools/find-in-system.py</code> (236 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-027.01` | 1–33 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-027.02` | 34–43 | بلوک | `normalize` | 0 | ⬜ |
| `TOOL-027.03` | 44–51 | بلوک | `load` | 0 | ⬜ |
| `TOOL-027.04` | 52–74 | بلوک | `score` | 0 | ⬜ |
| `TOOL-027.05` | 75–92 | بلوک | `top` | 0 | ⬜ |
| `TOOL-027.06` | 93–117 | بلوک | `print_features` | 0 | ⬜ |
| `TOOL-027.07` | 118–128 | بلوک | `print_section` | 0 | ⬜ |
| `TOOL-027.08` | 129–236 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-028</b> — <code>tools/fix_food_columns.php</code> (140 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-028.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `TOOL-028.02` | 27–57 | ناحیه | `پیام روشن اگر فایل تنظیمات نیست (bootstrap در CLI ` | 0 | ⬜ |
| `TOOL-028.03` | 58–63 | ناحیه | `۱) وجود جدول` | 0 | ⬜ |
| `TOOL-028.04` | 64–77 | ناحیه | `۲) فهرست زندهٔ ستون‌ها (بدون تکیه بر کش)` | 0 | ⬜ |
| `TOOL-028.05` | 78–117 | ناحیه | `۳) تشخیص توابع سامانه (همان تابعی که دکمهٔ تحویل ا` | 4 | ⬜ |
| `TOOL-028.06` | 118–140 | ناحیه | `۴) بررسی پس از اصلاح (زنده)` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-029</b> — <code>tools/food_system_router.php</code> (28 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-029.01` | 1–11 | ناحیه | `بلوک رویه‌ای 1-11` | 2 | ⬜ |
| `TOOL-029.02` | 12–17 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `TOOL-029.03` | 18–20 | ناحیه | `مسیر: food-order` | 0 | ⬜ |
| `TOOL-029.04` | 21–28 | ناحیه | `مسیر: food-ticket` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-030</b> — <code>tools/food_system_seed.php</code> (134 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-030.01` | 1–85 | ناحیه | `بلوک رویه‌ای 1-85` | 0 | ⬜ |
| `TOOL-030.02` | 86–86 | ناحیه | `Prebuild application sessions instead of using the` | 0 | ⬜ |
| `TOOL-030.03` | 87–134 | ناحیه | `unrelated client-inventory collection. Each sessio` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-031</b> — <code>tools/food_system_stress.py</code> (479 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-031.01` | 1–25 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-031.02` | 26–26 | بلوک | `Metrics` | 0 | ⬜ |
| `TOOL-031.03` | 27–31 | بلوک | `__init__` | 0 | ⬜ |
| `TOOL-031.04` | 32–37 | بلوک | `add` | 0 | ⬜ |
| `TOOL-031.05` | 38–42 | بلوک | `fail` | 0 | ⬜ |
| `TOOL-031.06` | 43–49 | بلوک | `api_path` | 1 | ⬜ |
| `TOOL-031.07` | 50–85 | بلوک | `request` | 0 | ⬜ |
| `TOOL-031.08` | 86–90 | بلوک | `expect` | 0 | ⬜ |
| `TOOL-031.09` | 91–102 | بلوک | `parse_json` | 0 | ⬜ |
| `TOOL-031.10` | 103–111 | بلوک | `find_order` | 0 | ⬜ |
| `TOOL-031.11` | 112–149 | بلوک | `self_order_flow` | 0 | ⬜ |
| `TOOL-031.12` | 150–155 | بلوک | `manager_crud` | 0 | ⬜ |
| `TOOL-031.13` | 156–233 | بلوک | `post` | 1 | ⬜ |
| `TOOL-031.14` | 234–237 | بلوک | `text_preview` | 0 | ⬜ |
| `TOOL-031.15` | 238–276 | بلوک | `bulk_lifecycle` | 0 | ⬜ |
| `TOOL-031.16` | 277–301 | بلوک | `build_read_routes` | 0 | ⬜ |
| `TOOL-031.17` | 302–321 | بلوک | `manager_read_worker` | 0 | ⬜ |
| `TOOL-031.18` | 322–331 | بلوک | `percentile` | 0 | ⬜ |
| `TOOL-031.19` | 332–401 | بلوک | `write_report` | 0 | ⬜ |
| `TOOL-031.20` | 402–479 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-032</b> — <code>tools/food_system_verify.php</code> (96 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-032.01` | 1–96 | ناحیه | `بلوک رویه‌ای 1-96` | 5 | ⬜ |

</details>

<details>
<summary><b>TOOL-033</b> — <code>tools/full_system_route_smoke.py</code> (697 خط، 18 آدرس، بازبینی‌شده 0/18)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-033.01` | 1–26 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-033.02` | 27–27 | بلوک | `RouteSmoke` | 0 | ⬜ |
| `TOOL-033.03` | 28–37 | بلوک | `__init__` | 0 | ⬜ |
| `TOOL-033.04` | 38–67 | بلوک | `request` | 0 | ⬜ |
| `TOOL-033.05` | 68–83 | بلوک | `login` | 0 | ⬜ |
| `TOOL-033.06` | 84–107 | بلوک | `pages` | 0 | ⬜ |
| `TOOL-033.07` | 108–114 | بلوک | `request_xlsx` | 0 | ⬜ |
| `TOOL-033.08` | 115–166 | بلوک | `exports_and_cron_boundaries` | 0 | ⬜ |
| `TOOL-033.09` | 167–184 | بلوک | `get_json` | 0 | ⬜ |
| `TOOL-033.10` | 185–243 | بلوک | `apis` | 0 | ⬜ |
| `TOOL-033.11` | 244–325 | بلوک | `role_matrix` | 0 | ⬜ |
| `TOOL-033.12` | 326–381 | بلوک | `inventory_api_lifecycle` | 0 | ⬜ |
| `TOOL-033.13` | 382–387 | بلوک | `_synthetic_iranian_national_code` | 0 | ⬜ |
| `TOOL-033.14` | 388–458 | بلوک | `traffic_control_lifecycle` | 1 | ⬜ |
| `TOOL-033.15` | 459–496 | بلوک | `food_ticket_group_lifecycle` | 1 | ⬜ |
| `TOOL-033.16` | 497–509 | بلوک | `requester_permission_checks` | 0 | ⬜ |
| `TOOL-033.17` | 510–562 | بلوک | `food_order_lifecycle` | 0 | ⬜ |
| `TOOL-033.18` | 563–697 | بلوک | `main` | 2 | ⬜ |

</details>

<details>
<summary><b>TOOL-034</b> — <code>tools/install-1.50.php</code> (92 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-034.01` | 1–42 | ناحیه | `بلوک رویه‌ای 1-42` | 1 | ⬜ |
| `TOOL-034.02` | 43–49 | ناحیه | `اگر دیتابیس از قبل کامل است، چیزی تغییر نمی‌کند و ` | 0 | ⬜ |
| `TOOL-034.03` | 50–54 | ناحیه | `۱. پشتیبان` | 4 | ⬜ |
| `TOOL-034.04` | 55–75 | ناحیه | `۲. نقش‌ها: مقادیر فعلی ENUM حفظ می‌شود و فقط نقش‌ه` | 4 | ⬜ |
| `TOOL-034.05` | 76–83 | ناحیه | `۳. ستون اجبار تغییر رمز` | 4 | ⬜ |
| `TOOL-034.06` | 84–92 | ناحیه | `۴. بررسی نهایی` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-035</b> — <code>tools/lint-php-syntax.php</code> (25 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-035.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-036</b> — <code>tools/logging_stress_worker.php</code> (107 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-036.01` | 1–107 | ناحیه | `بلوک رویه‌ای 1-107` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-037</b> — <code>tools/migrate-legacy-attachments.php</code> (42 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-037.01` | 1–42 | ناحیه | `بلوک رویه‌ای 1-42` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-038</b> — <code>tools/preview_asset_names.php</code> (126 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-038.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-038.02` | 22–42 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-038.03` | 43–44 | ناحیه | `بلوک رویه‌ای 43-44` | 0 | ⬜ |
| `TOOL-038.04` | 45–48 | تابع | `setting` | 0 | ⬜ |
| `TOOL-038.05` | 49–52 | تابع | `e` | 0 | ⬜ |
| `TOOL-038.06` | 53–126 | ناحیه | `بلوک رویه‌ای 53-126` | 5 | ⬜ |

</details>

<details>
<summary><b>TOOL-039</b> — <code>tools/preview_food_import.php</code> (68 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-039.01` | 1–68 | ناحیه | `بلوک رویه‌ای 1-68` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-040</b> — <code>tools/print_calibration.php</code> (137 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-040.01` | 1–65 | ناحیه | `بلوک رویه‌ای 1-65` | 0 | ⬜ |
| `TOOL-040.02` | 66–78 | ناحیه | `تنظیمات فعلی و اینکه چه چیزی روی این کارت اثر می‌گ` | 0 | ⬜ |
| `TOOL-040.03` | 79–102 | ناحیه | `ساخت کارت با همان تابع مشترک چاپ (منبع یکتا: food_` | 2 | ⬜ |
| `TOOL-040.04` | 103–112 | ناحیه | `ارسال به چاپگر` | 0 | ⬜ |
| `TOOL-040.05` | 113–137 | ناحیه | `مسیر: windows_share` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-041</b> — <code>tools/print_test.php</code> (51 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-041.01` | 1–51 | ناحیه | `بلوک رویه‌ای 1-51` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-042</b> — <code>tools/render_code_map.py</code> (153 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-042.01` | 1–57 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-042.02` | 58–153 | بلوک | `addr_cell` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-043</b> — <code>tools/session-boot.py</code> (347 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-043.01` | 1–49 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-043.02` | 50–56 | بلوک | `read` | 0 | ⬜ |
| `TOOL-043.03` | 57–66 | بلوک | `run` | 0 | ⬜ |
| `TOOL-043.04` | 67–67 | بلوک | `git_info` | 0 | ⬜ |
| `TOOL-043.05` | 68–87 | بلوک | `q` | 0 | ⬜ |
| `TOOL-043.06` | 88–105 | بلوک | `extract_section` | 0 | ⬜ |
| `TOOL-043.07` | 106–115 | بلوک | `skill_rows` | 0 | ⬜ |
| `TOOL-043.08` | 116–120 | بلوک | `bullets` | 0 | ⬜ |
| `TOOL-043.09` | 121–137 | بلوک | `refresh_map_if_stale` | 0 | ⬜ |
| `TOOL-043.10` | 138–169 | بلوک | `block_index_status` | 0 | ⬜ |
| `TOOL-043.11` | 170–181 | بلوک | `run_guard_tests` | 0 | ⬜ |
| `TOOL-043.12` | 182–237 | بلوک | `build_context` | 0 | ⬜ |
| `TOOL-043.13` | 238–323 | بلوک | `print_briefing` | 0 | ⬜ |
| `TOOL-043.14` | 324–347 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-044</b> — <code>tools/set_attendance_password.php</code> (110 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-044.01` | 1–110 | ناحیه | `بلوک رویه‌ای 1-110` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-045</b> — <code>tools/set_print_gap.php</code> (42 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-045.01` | 1–42 | ناحیه | `بلوک رویه‌ای 1-42` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-046</b> — <code>tools/sync_domain_users.php</code> (41 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-046.01` | 1–41 | ناحیه | `بلوک رویه‌ای 1-41` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-047</b> — <code>tools/system_audit.php</code> (93 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-047.01` | 1–93 | ناحیه | `بلوک رویه‌ای 1-93` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-048</b> — <code>tools/system_loadtest.php</code> (711 خط، 25 آدرس، بازبینی‌شده 0/25)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-048.01` | 1–38 | ناحیه | `بلوک رویه‌ای 1-38` | 0 | ⬜ |
| `TOOL-048.02` | 39–61 | ناحیه | `پارامترها` | 0 | ⬜ |
| `TOOL-048.03` | 62–81 | ناحیه | `بوت‌استرپ` | 1 | ⬜ |
| `TOOL-048.04` | 82–94 | ناحیه | `ساختار گزارش` | 0 | ⬜ |
| `TOOL-048.05` | 95–100 | تابع | `out` | 3 | ⬜ |
| `TOOL-048.06` | 101–107 | تابع | `head` | 0 | ⬜ |
| `TOOL-048.07` | 108–116 | تابع | `pad` | 0 | ⬜ |
| `TOOL-048.08` | 117–121 | تابع | `row` | 0 | ⬜ |
| `TOOL-048.09` | 122–126 | تابع | `sect` | 0 | ⬜ |
| `TOOL-048.10` | 127–135 | تابع | `safe` | 0 | ⬜ |
| `TOOL-048.11` | 136–139 | ناحیه | `بلوک رویه‌ای 136-139` | 0 | ⬜ |
| `TOOL-048.12` | 140–174 | ناحیه | `بخش A — محیط` | 3 | ⬜ |
| `TOOL-048.13` | 175–175 | ناحیه | `بخش B — کاوش قابلیت‌ها` | 0 | ⬜ |
| `TOOL-048.14` | 176–176 | ناحیه | `این بخش مهم‌ترین قسمت برای تشخیص نسخه است: دقیقاً ` | 0 | ⬜ |
| `TOOL-048.15` | 177–207 | ناحیه | `اصلاحات روی این نصب حاضرند و کدام نیستند.` | 0 | ⬜ |
| `TOOL-048.16` | 208–252 | ناحیه | `بخش C — پایگاه داده و اسکیما` | 5 | ⬜ |
| `TOOL-048.17` | 253–253 | ناحیه | `بخش D — یکپارچگی شمارهٔ درخواست (فقط‌خواندنی)` | 0 | ⬜ |
| `TOOL-048.18` | 254–298 | ناحیه | `این بخش همان باگی را می‌سنجد که بیشترین اثر را روی` | 0 | ⬜ |
| `TOOL-048.19` | 299–299 | ناحیه | `بخش E — کارایی صفحه‌ها (فقط‌خواندنی)` | 0 | ⬜ |
| `TOOL-048.20` | 300–343 | ناحیه | `الگوی کوئری‌های فهرست را با داده‌های واقعی زمان می` | 0 | ⬜ |
| `TOOL-048.21` | 344–371 | ناحیه | `بخش F — ایندکس‌های جدول درخواست` | 0 | ⬜ |
| `TOOL-048.22` | 372–405 | ناحیه | `بخش G — سلامت لاگ و ممیزی` | 6 | ⬜ |
| `TOOL-048.23` | 406–456 | ناحیه | `بخش H — منطق دسترسی (فقط‌خواندنی، بدون نوشتن)` | 0 | ⬜ |
| `TOOL-048.24` | 457–677 | ناحیه | `بخش I — حالت نوشتن: ساخت کاربر و درخواست` | **9** | ⬜ |
| `TOOL-048.25` | 678–711 | ناحیه | `جمع‌بندی` | 2 | ⬜ |

</details>

<details>
<summary><b>TOOL-049</b> — <code>tools/test_access_coverage_offline.php</code> (252 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-049.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-049.02` | 22–22 | ناحیه | `تزریق حداقلی محیط: DB وجود ندارد` | 0 | ⬜ |
| `TOOL-049.03` | 23–26 | تابع | `db` | 0 | ⬜ |
| `TOOL-049.04` | 28–31 | تابع | `setting` | 0 | ⬜ |
| `TOOL-049.05` | 33–35 | تابع | `save_setting` | 0 | ⬜ |
| `TOOL-049.06` | 37–58 | ناحیه | `پیش‌بررسی: این آزمون باید در ریشهٔ سامانه (کنار in` | 0 | ⬜ |
| `TOOL-049.07` | 59–69 | تابع | `check` | 0 | ⬜ |
| `TOOL-049.08` | 70–90 | ناحیه | `بلوک رویه‌ای 70-90` | 0 | ⬜ |
| `TOOL-049.09` | 91–103 | ناحیه | `بدون دیتابیس، جدول role_permissions آماده نیست ⇒ م` | 0 | ⬜ |
| `TOOL-049.10` | 104–105 | ناحیه | `نقش دست‌نخورده ⇒ پیش‌فرض کد (حتی اگر ردیف‌های قدیم` | 0 | ⬜ |
| `TOOL-049.11` | 106–107 | ناحیه | `نقش ذخیره‌شده ⇒ همان مقادیر فرم، حتی اگر با پیش‌فر` | 0 | ⬜ |
| `TOOL-049.12` | 108–141 | ناحیه | `ذخیرهٔ «هیچ‌کدام» یک تصمیم عمدی است و باید محترم ب` | 1 | ⬜ |
| `TOOL-049.13` | 142–142 | ناحیه | `این فهرست عمدی است: کدهایی که فقط «برچسب» هستند و ` | 0 | ⬜ |
| `TOOL-049.14` | 143–252 | ناحیه | `افزودن کد بی‌اثر جدید یا وصل‌کردن یکی از این‌ها با` | 2 | ⬜ |

</details>

<details>
<summary><b>TOOL-050</b> — <code>tools/test_api_contract_offline.php</code> (244 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-050.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `TOOL-050.02` | 29–39 | تابع | `check` | 0 | ⬜ |
| `TOOL-050.03` | 40–132 | ناحیه | `بلوک رویه‌ای 40-132` | 1 | ⬜ |
| `TOOL-050.04` | 133–137 | ناحیه | `مسیرهای ورود عمومی صریحاً پیش از نگاشت مجوز بررسی ` | 0 | ⬜ |
| `TOOL-050.05` | 138–140 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `TOOL-050.06` | 141–143 | ناحیه | `مسیر: login\|sso-login` | 0 | ⬜ |
| `TOOL-050.07` | 144–244 | ناحیه | `مسیر: config` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-051</b> — <code>tools/test_asset_names_offline.php</code> (156 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-051.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `TOOL-051.02` | 21–31 | تابع | `check` | 0 | ⬜ |
| `TOOL-051.03` | 33–55 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-051.04` | 56–59 | ناحیه | `بلوک رویه‌ای 56-59` | 0 | ⬜ |
| `TOOL-051.05` | 60–61 | ناحیه | `محیط حداقلی: setting() و e() و mb_* (همان چیزهایی ` | 0 | ⬜ |
| `TOOL-051.06` | 62–65 | تابع | `setting` | 0 | ⬜ |
| `TOOL-051.07` | 66–69 | تابع | `e` | 0 | ⬜ |
| `TOOL-051.08` | 70–73 | تابع | `set_style` | 0 | ⬜ |
| `TOOL-051.09` | 74–156 | ناحیه | `بلوک رویه‌ای 74-156` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-052</b> — <code>tools/test_brand_neutral_offline.php</code> (150 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-052.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 0 | ⬜ |
| `TOOL-052.02` | 28–39 | تابع | `check` | 0 | ⬜ |
| `TOOL-052.03` | 40–150 | ناحیه | `بلوک رویه‌ای 40-150` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-053</b> — <code>tools/test_column_detect_offline.php</code> (285 خط، 19 آدرس، بازبینی‌شده 0/19)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-053.01` | 1–30 | ناحیه | `بلوک رویه‌ای 1-30` | 0 | ⬜ |
| `TOOL-053.02` | 31–31 | ناحیه | `Isolated functions log expected ALTER failures thr` | 0 | ⬜ |
| `TOOL-053.03` | 32–35 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-053.04` | 37–48 | تابع | `check` | 0 | ⬜ |
| `TOOL-053.05` | 50–72 | تابع | `grab` | 0 | ⬜ |
| `TOOL-053.06` | 74–96 | کلاس | `FakeStatement` | 0 | ⬜ |
| `TOOL-053.07` | 98–130 | کلاس | `FakeDb` | 4 | ⬜ |
| `TOOL-053.08` | 131–132 | ناحیه | `بلوک رویه‌ای 131-132` | 0 | ⬜ |
| `TOOL-053.09` | 133–137 | تابع | `db` | 0 | ⬜ |
| `TOOL-053.10` | 138–144 | ناحیه | `بلوک رویه‌ای 138-144` | 4 | ⬜ |
| `TOOL-053.11` | 145–146 | ناحیه | `دیتابیس زنده در دسترس نیست → مسیر دوم (db_table_co` | 0 | ⬜ |
| `TOOL-053.12` | 147–151 | تابع | `db_table_columns` | 0 | ⬜ |
| `TOOL-053.13` | 152–161 | ناحیه | `بلوک رویه‌ای 152-161` | 0 | ⬜ |
| `TOOL-053.14` | 162–162 | ناحیه | `تابع قبلی قابل بازتعریف نیست؛ همان تابع از مسیر دو` | 0 | ⬜ |
| `TOOL-053.15` | 163–164 | ناحیه | `(این جدول در آزمون‌های آفلاین با SQLite ساخته می‌ش` | 0 | ⬜ |
| `TOOL-053.16` | 165–235 | ناحیه | `شبیه‌سازی شکل فهرست با یک آرایه موقت: تابع با (arr` | 4 | ⬜ |
| `TOOL-053.17` | 236–247 | ناحیه | `۷-الف) ستون نیست، ALTER موفق ⇒ باید true برگردد (ق` | 0 | ⬜ |
| `TOOL-053.18` | 248–254 | ناحیه | `۷-ب) ستون هست ⇒ هیچ ALTER نباید زده شود (سناریوی و` | 0 | ⬜ |
| `TOOL-053.19` | 255–285 | ناحیه | `۷-ج) ستون نیست و دسترسی ALTER هم نیست ⇒ پیام روشن ` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-054</b> — <code>tools/test_cron_synthetic.php</code> (214 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-054.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `TOOL-054.02` | 26–34 | تابع | `cron_assert` | 0 | ⬜ |
| `TOOL-054.03` | 36–53 | تابع | `cron_run` | 4 | ⬜ |
| `TOOL-054.04` | 55–80 | تابع | `cron_insert_ticket` | 6 | ⬜ |
| `TOOL-054.05` | 82–89 | تابع | `cron_run_assert` | 0 | ⬜ |
| `TOOL-054.06` | 90–214 | ناحیه | `بلوک رویه‌ای 90-214` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-055</b> — <code>tools/test_date_input_offline.php</code> (153 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-055.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-055.02` | 22–59 | تابع | `extractFunction` | 0 | ⬜ |
| `TOOL-055.03` | 60–64 | ناحیه | `بلوک رویه‌ای 60-64` | 0 | ⬜ |
| `TOOL-055.04` | 65–67 | ناحیه | `مبدل خود سامانه با نام مرجع (تا مسیر پشتیبان محلی ` | 4 | ⬜ |
| `TOOL-055.05` | 68–75 | ناحیه | `توابع فایل فیش غذا (food_ticket_parse_date در نبود` | 4 | ⬜ |
| `TOOL-055.06` | 76–86 | تابع | `check` | 0 | ⬜ |
| `TOOL-055.07` | 87–153 | ناحیه | `بلوک رویه‌ای 87-153` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-056</b> — <code>tools/test_delivery_flow_offline.php</code> (222 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-056.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `TOOL-056.02` | 29–40 | تابع | `check` | 0 | ⬜ |
| `TOOL-056.03` | 41–42 | ناحیه | `بلوک رویه‌ای 41-42` | 0 | ⬜ |
| `TOOL-056.04` | 43–79 | کلاس | `MysqlishSqlite` | 4 | ⬜ |
| `TOOL-056.05` | 80–91 | ناحیه | `بلوک رویه‌ای 80-91` | 5 | ⬜ |
| `TOOL-056.06` | 92–96 | تابع | `db` | 0 | ⬜ |
| `TOOL-056.07` | 98–107 | تابع | `db_table_columns` | 0 | ⬜ |
| `TOOL-056.08` | 108–110 | ناحیه | `بلوک رویه‌ای 108-110` | 0 | ⬜ |
| `TOOL-056.09` | 111–114 | تابع | `activity_log` | 0 | ⬜ |
| `TOOL-056.10` | 116–138 | تابع | `grab` | 0 | ⬜ |
| `TOOL-056.11` | 139–198 | ناحیه | `بلوک رویه‌ای 139-198` | 5 | ⬜ |
| `TOOL-056.12` | 199–222 | ناحیه | `در SQLite امکان ALTER ADD COLUMN هست → سامانه خودش` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-057</b> — <code>tools/test_employees_import_offline.php</code> (276 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-057.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `TOOL-057.02` | 27–37 | تابع | `check` | 0 | ⬜ |
| `TOOL-057.03` | 38–40 | ناحیه | `بلوک رویه‌ای 38-40` | 0 | ⬜ |
| `TOOL-057.04` | 41–41 | ناحیه | `تابع bootstrap که ایمپورت برای نرمال‌سازی کد پرسنل` | 0 | ⬜ |
| `TOOL-057.05` | 42–47 | تابع | `normalize_employee_number` | 0 | ⬜ |
| `TOOL-057.06` | 48–50 | ناحیه | `بلوک رویه‌ای 48-50` | 0 | ⬜ |
| `TOOL-057.07` | 51–73 | تابع | `db` | 4 | ⬜ |
| `TOOL-057.08` | 74–76 | ناحیه | `بلوک رویه‌ای 74-76` | 0 | ⬜ |
| `TOOL-057.09` | 77–120 | تابع | `make_xlsx` | 0 | ⬜ |
| `TOOL-057.10` | 121–276 | ناحیه | `بلوک رویه‌ای 121-276` | **14** | ⬜ |

</details>

<details>
<summary><b>TOOL-058</b> — <code>tools/test_food_dupes_offline.php</code> (168 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-058.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-058.02` | 4–4 | ناحیه | `آزمون آفلاین: (۱) خطای ltrim با کلید عددی در نقشهٔ` | 0 | ⬜ |
| `TOOL-058.03` | 5–8 | ناحیه | `توابع از فایل‌های اصلی استخراج می‌شوند و دیتابیس S` | 0 | ⬜ |
| `TOOL-058.04` | 9–29 | تابع | `extract_php_function` | 0 | ⬜ |
| `TOOL-058.05` | 30–39 | ناحیه | `بلوک رویه‌ای 30-39` | 4 | ⬜ |
| `TOOL-058.06` | 40–43 | تابع | `db` | 0 | ⬜ |
| `TOOL-058.07` | 44–47 | تابع | `food_order_schema_ensure` | 0 | ⬜ |
| `TOOL-058.08` | 48–51 | تابع | `cfg` | 0 | ⬜ |
| `TOOL-058.09` | 52–55 | تابع | `lac_default_hash` | 0 | ⬜ |
| `TOOL-058.10` | 56–70 | ناحیه | `بلوک رویه‌ای 56-70` | 4 | ⬜ |
| `TOOL-058.11` | 71–81 | تابع | `check` | 0 | ⬜ |
| `TOOL-058.12` | 83–100 | ناحیه | `بخش ۱: نقشهٔ سفارش با کلید عددی و کد ملی تکراری (ق` | 5 | ⬜ |
| `TOOL-058.13` | 101–168 | ناحیه | `بخش ۲: کد ملی یکتا در ثبت و ویرایش کارمند` | **10** | ⬜ |

</details>

<details>
<summary><b>TOOL-059</b> — <code>tools/test_food_group_logic_offline.php</code> (133 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-059.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `TOOL-059.02` | 19–20 | ناحیه | `جایگزین‌های حداقلی توابع کمکی (همان قرارداد food-t` | 0 | ⬜ |
| `TOOL-059.03` | 21–29 | تابع | `food_ticket_normalize_digits` | 0 | ⬜ |
| `TOOL-059.04` | 30–31 | ناحیه | `بلوک رویه‌ای 30-31` | 0 | ⬜ |
| `TOOL-059.05` | 32–46 | تابع | `food_ticket_code` | 0 | ⬜ |
| `TOOL-059.06` | 47–48 | ناحیه | `بلوک رویه‌ای 47-48` | 0 | ⬜ |
| `TOOL-059.07` | 49–52 | تابع | `food_ticket_digits` | 0 | ⬜ |
| `TOOL-059.08` | 53–82 | ناحیه | `بلوک رویه‌ای 53-82` | 0 | ⬜ |
| `TOOL-059.09` | 83–99 | ناحیه | `۱) محاسبهٔ حاضر/غایب/واجد شرایط/بدون سفارش/فیش‌گرف` | 0 | ⬜ |
| `TOOL-059.10` | 100–103 | ناحیه | `۲) عضوی که قبلاً فیش گرفته دوباره واجد شرایط نمی‌ش` | 0 | ⬜ |
| `TOOL-059.11` | 104–110 | ناحیه | `۳) استقلال تاریخ‌ها: با همان اعضا و بدون غیبت، در ` | 0 | ⬜ |
| `TOOL-059.12` | 111–114 | ناحیه | `۴) خالص بودن تابع: اجرای دوباره با همان ورودی‌ها ن` | 0 | ⬜ |
| `TOOL-059.13` | 115–118 | ناحیه | `۵) گروه بدون عضو` | 0 | ⬜ |
| `TOOL-059.14` | 119–122 | ناحیه | `۶) غیبت بر سفارش اولویت دارد (کسی که سفارش دارد ول` | 0 | ⬜ |
| `TOOL-059.15` | 123–125 | ناحیه | `۷) نرمال‌سازی کد ملی (فارسی/صفر ابتدایی) در کلید ف` | 0 | ⬜ |
| `TOOL-059.16` | 126–126 | ناحیه | `نکته: موتور فیش (food_ticket_decide_punch) هم از f` | 0 | ⬜ |
| `TOOL-059.17` | 127–133 | ناحیه | `حفظ می‌کند؛ پس کلیدهای گروهی و فردی دقیقاً یکسان س` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-060</b> — <code>tools/test_food_group_representative.php</code> (517 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-060.01` | 1–517 | ناحیه | `بلوک رویه‌ای 1-517` | **16** | ⬜ |

</details>

<details>
<summary><b>TOOL-061</b> — <code>tools/test_food_groups.php</code> (205 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-061.01` | 1–205 | ناحیه | `بلوک رویه‌ای 1-205` | **10** | ⬜ |

</details>

<details>
<summary><b>TOOL-062</b> — <code>tools/test_food_guest_request_deadline_offline.php</code> (80 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-062.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-062.02` | 4–6 | ناحیه | `آزمون آفلاین مهلت ثبت سفارش مهمان در همان روز؛ بدو` | 0 | ⬜ |
| `TOOL-062.03` | 7–27 | تابع | `extract_php_function` | 0 | ⬜ |
| `TOOL-062.04` | 28–29 | ناحیه | `بلوک رویه‌ای 28-29` | 0 | ⬜ |
| `TOOL-062.05` | 30–33 | تابع | `setting` | 0 | ⬜ |
| `TOOL-062.06` | 34–48 | ناحیه | `بلوک رویه‌ای 34-48` | 4 | ⬜ |
| `TOOL-062.07` | 49–54 | تابع | `check` | 0 | ⬜ |
| `TOOL-062.08` | 55–58 | تابع | `moment` | 0 | ⬜ |
| `TOOL-062.09` | 59–80 | ناحیه | `بلوک رویه‌ای 59-80` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-063</b> — <code>tools/test_food_guest_requests.php</code> (141 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-063.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-063.02` | 4–4 | ناحیه | `آزمون واقعی (نیاز به دیتابیس تست و ITSM_CONFIG_PAT` | 0 | ⬜ |
| `TOOL-063.03` | 5–17 | ناحیه | `لغو، گزارش با فیلتر معاونت، و سقف حالت «درخواست مه` | 0 | ⬜ |
| `TOOL-063.04` | 18–23 | تابع | `check` | 0 | ⬜ |
| `TOOL-063.05` | 24–32 | تابع | `expect_error` | 0 | ⬜ |
| `TOOL-063.06` | 33–40 | ناحیه | `بلوک رویه‌ای 33-40` | 5 | ⬜ |
| `TOOL-063.07` | 41–73 | ناحیه | `چارت آزمایشی: دو معاونت؛ زیر معاونت اول یک اداره (` | 1 | ⬜ |
| `TOOL-063.08` | 74–96 | ناحیه | `مقدار ارسالی فرم برای معاونت نادیده گرفته می‌شود` | 0 | ⬜ |
| `TOOL-063.09` | 97–101 | ناحیه | `سقف حالت درخواست مهمان` | 0 | ⬜ |
| `TOOL-063.10` | 102–117 | ناحیه | `گزارش با فیلتر معاونت` | 0 | ⬜ |
| `TOOL-063.11` | 118–132 | ناحیه | `فهرست: معاونت کاربر در پاسخ` | 0 | ⬜ |
| `TOOL-063.12` | 133–141 | ناحیه | `پاک‌سازی` | 5 | ⬜ |

</details>

<details>
<summary><b>TOOL-064</b> — <code>tools/test_food_order_range_offline.php</code> (100 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-064.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-064.02` | 4–4 | ناحیه | `آزمون آفلاین بازهٔ فهرست سفارش‌های غذا (my-orders ` | 0 | ⬜ |
| `TOOL-064.03` | 5–8 | ناحیه | `توابع را مستقیم از فایل‌های اصلی استخراج می‌کند تا` | 0 | ⬜ |
| `TOOL-064.04` | 9–30 | تابع | `extract_php_function` | 0 | ⬜ |
| `TOOL-064.05` | 31–44 | ناحیه | `بلوک رویه‌ای 31-44` | 4 | ⬜ |
| `TOOL-064.06` | 45–55 | تابع | `check` | 0 | ⬜ |
| `TOOL-064.07` | 56–64 | تابع | `throws` | 0 | ⬜ |
| `TOOL-064.08` | 66–75 | ناحیه | `۱. تبدیل تاریخ ورودی` | 0 | ⬜ |
| `TOOL-064.09` | 76–88 | ناحیه | `۲. پیش‌فرض: از امروز تا پایان ماه شمسی بعد (سفارش ` | 5 | ⬜ |
| `TOOL-064.10` | 89–100 | ناحیه | `۳. بازهٔ دلخواه` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-065</b> — <code>tools/test_food_personnel_link_offline.php</code> (275 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-065.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-065.02` | 4–4 | ناحیه | `آزمون آفلاین اتصال کاربران دامین به پرسنل غذا (SQL` | 0 | ⬜ |
| `TOOL-065.03` | 5–12 | ناحیه | `تطبیق نام، همنام‌ها، کد ملی متفاوت، ادغام، بازگشت ` | 0 | ⬜ |
| `TOOL-065.04` | 13–23 | تابع | `check` | 0 | ⬜ |
| `TOOL-065.05` | 24–46 | ناحیه | `بلوک رویه‌ای 24-46` | 5 | ⬜ |
| `TOOL-065.06` | 47–53 | ناحیه | `کاربران دامین` | 0 | ⬜ |
| `TOOL-065.07` | 54–60 | ناحیه | `پرسنل غذا` | 0 | ⬜ |
| `TOOL-065.08` | 61–64 | ناحیه | `سفارش‌های نمونه: پرسنل غذای ۱۲ (رضا احمدی) سفارش د` | 5 | ⬜ |
| `TOOL-065.09` | 65–77 | ناحیه | `تطبیق` | 0 | ⬜ |
| `TOOL-065.10` | 78–82 | ناحیه | `مجوز انتخاب` | 0 | ⬜ |
| `TOOL-065.11` | 83–94 | ناحیه | `ادغام ناموفق (تداخل سفارش در یک روز) → بدون تغییر` | 5 | ⬜ |
| `TOOL-065.12` | 95–111 | ناحیه | `اجرای واقعی` | 0 | ⬜ |
| `TOOL-065.13` | 112–115 | ناحیه | `انتخاب تکراری همان پرسنل غذا برای دو کاربر` | 0 | ⬜ |
| `TOOL-065.14` | 116–119 | ناحیه | `انتخاب ناسازگار در اجرا رد می‌شود` | 0 | ⬜ |
| `TOOL-065.15` | 120–122 | ناحیه | `نام کاربری‌های موقت نباید بماند` | 0 | ⬜ |
| `TOOL-065.16` | 123–151 | ناحیه | `گزارش` | 5 | ⬜ |
| `TOOL-065.17` | 152–170 | ناحیه | `کاربر دامین غیرفعال: نه تطبیق، نه گزارش تطبیق` | 5 | ⬜ |
| `TOOL-065.18` | 171–198 | ناحیه | `اتصال دستی با کد پرسنلی (بدون تطبیق نام)` | 5 | ⬜ |
| `TOOL-065.19` | 199–221 | ناحیه | `اتصال حساب محلی کارکنان (کد AD به‌دلیل unique بودن` | 5 | ⬜ |
| `TOOL-065.20` | 222–235 | ناحیه | `کد دستی دامین بر نام اولویت دارد و کد هم‌ارز با صف` | 5 | ⬜ |
| `TOOL-065.21` | 236–243 | ناحیه | `کد دستی جدید باید مقدار قدیمی و اشتباه دامین را جا` | 5 | ⬜ |
| `TOOL-065.22` | 244–275 | ناحیه | `هم‌زمانی رکورد واقعی پرسنل غذا و حساب ورود محلی بر` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-066</b> — <code>tools/test_food_templates.php</code> (270 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-066.01` | 1–270 | ناحیه | `بلوک رویه‌ای 1-270` | **9** | ⬜ |

</details>

<details>
<summary><b>TOOL-067</b> — <code>tools/test_fresh_db_offline.php</code> (225 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-067.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `TOOL-067.02` | 26–36 | تابع | `check` | 0 | ⬜ |
| `TOOL-067.03` | 38–58 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-067.04` | 60–81 | کلاس | `FakeStmt` | 0 | ⬜ |
| `TOOL-067.05` | 83–107 | کلاس | `FreshFakeDb` | 0 | ⬜ |
| `TOOL-067.06` | 108–109 | ناحیه | `بلوک رویه‌ای 108-109` | 0 | ⬜ |
| `TOOL-067.07` | 110–113 | تابع | `db` | 0 | ⬜ |
| `TOOL-067.08` | 114–117 | تابع | `db_table_exists` | 0 | ⬜ |
| `TOOL-067.09` | 118–121 | تابع | `setting` | 0 | ⬜ |
| `TOOL-067.10` | 122–125 | تابع | `save_setting` | 0 | ⬜ |
| `TOOL-067.11` | 126–128 | تابع | `system_log` | 0 | ⬜ |
| `TOOL-067.12` | 129–209 | ناحیه | `بلوک رویه‌ای 129-209` | 5 | ⬜ |
| `TOOL-067.13` | 210–218 | تابع | `food_ticket_web_host_bootstrap_standalone` | 4 | ⬜ |
| `TOOL-067.14` | 219–225 | ناحیه | `بلوک رویه‌ای 219-225` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-068</b> — <code>tools/test_fresh_state_node.js</code> (126 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-068.01` | 1–32 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-068.02` | 33–42 | بلوک | `check` | 0 | ⬜ |
| `TOOL-068.03` | 43–43 | بلوک | `اجرای گارد با کش مرورگر و توکن سرور مشخص.` | 0 | ⬜ |
| `TOOL-068.04` | 44–81 | بلوک | `boot` | 1 | ⬜ |
| `TOOL-068.05` | 82–90 | بلوک | `۱) توکن متفاوت → دور ریخته شود` | 0 | ⬜ |
| `TOOL-068.06` | 91–95 | بلوک | `۲) دیتابیس خام → دور ریخته شود (حتی با توکن یکسان)` | 0 | ⬜ |
| `TOOL-068.07` | 96–99 | بلوک | `۳) توکن یکسان و دیتابیس پر → کش معتبر بماند` | 0 | ⬜ |
| `TOOL-068.08` | 100–105 | بلوک | `۴) resetLocalState آرایه‌ها را خالی می‌کند` | 0 | ⬜ |
| `TOOL-068.09` | 106–126 | بلوک | `۵) applyDbIdentity با پاسخ زندهٔ سرور` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-069</b> — <code>tools/test_global_search_offline.php</code> (122 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-069.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `TOOL-069.02` | 27–37 | تابع | `check` | 0 | ⬜ |
| `TOOL-069.03` | 38–122 | ناحیه | `بلوک رویه‌ای 38-122` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-070</b> — <code>tools/test_group_flow_offline.php</code> (709 خط، 57 آدرس، بازبینی‌شده 0/57)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-070.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `TOOL-070.02` | 26–30 | کلاس | `OfflinePdoException` | 0 | ⬜ |
| `TOOL-070.03` | 32–119 | کلاس | `OfflineStmt` | 0 | ⬜ |
| `TOOL-070.04` | 121–187 | کلاس | `OfflinePdo` | 4 | ⬜ |
| `TOOL-070.05` | 188–197 | ناحیه | `بلوک رویه‌ای 188-197` | 0 | ⬜ |
| `TOOL-070.06` | 198–204 | تابع | `db` | 0 | ⬜ |
| `TOOL-070.07` | 206–209 | تابع | `activity_log` | 0 | ⬜ |
| `TOOL-070.08` | 211–214 | تابع | `system_log` | 0 | ⬜ |
| `TOOL-070.09` | 216–224 | تابع | `db_table_columns` | 0 | ⬜ |
| `TOOL-070.10` | 226–230 | تابع | `food_ticket_normalize_digits` | 0 | ⬜ |
| `TOOL-070.11` | 231–248 | تابع | `food_ticket_code` | 0 | ⬜ |
| `TOOL-070.12` | 249–252 | تابع | `food_ticket_digits` | 0 | ⬜ |
| `TOOL-070.13` | 253–260 | تابع | `food_ticket_card_key` | 0 | ⬜ |
| `TOOL-070.14` | 261–266 | تابع | `food_ticket_card_equals` | 0 | ⬜ |
| `TOOL-070.15` | 267–279 | تابع | `food_ticket_parse_date` | 0 | ⬜ |
| `TOOL-070.16` | 280–289 | تابع | `food_ticket_parse_time` | 0 | ⬜ |
| `TOOL-070.17` | 290–298 | تابع | `food_ticket_source_key` | 0 | ⬜ |
| `TOOL-070.18` | 299–304 | تابع | `food_ticket_normalize_event_type` | 0 | ⬜ |
| `TOOL-070.19` | 305–309 | تابع | `food_ticket_normalize_print_status` | 0 | ⬜ |
| `TOOL-070.20` | 310–323 | تابع | `food_ticket_add_event` | 1 | ⬜ |
| `TOOL-070.21` | 324–329 | تابع | `food_ticket_existing_source` | 0 | ⬜ |
| `TOOL-070.22` | 330–335 | تابع | `food_ticket_existing_ticket` | 0 | ⬜ |
| `TOOL-070.23` | 336–339 | تابع | `food_ticket_config` | 0 | ⬜ |
| `TOOL-070.24` | 340–343 | تابع | `food_ticket_odbc` | 0 | ⬜ |
| `TOOL-070.25` | 344–347 | تابع | `food_ticket_order_map_lazy` | 0 | ⬜ |
| `TOOL-070.26` | 348–351 | تابع | `food_ticket_template` | 0 | ⬜ |
| `TOOL-070.27` | 352–356 | تابع | `food_ticket_enqueue_print` | 1 | ⬜ |
| `TOOL-070.28` | 357–360 | تابع | `food_ticket_process_print_queue_locked` | 0 | ⬜ |
| `TOOL-070.29` | 361–386 | تابع | `food_ticket_decide_for_user` | 6 | ⬜ |
| `TOOL-070.30` | 387–390 | تابع | `food_ticket_delivery_supported` | 0 | ⬜ |
| `TOOL-070.31` | 391–394 | تابع | `food_ticket_events_ensure_delivery` | 0 | ⬜ |
| `TOOL-070.32` | 395–398 | تابع | `user_can` | 0 | ⬜ |
| `TOOL-070.33` | 399–400 | ناحیه | `بلوک رویه‌ای 399-400` | 0 | ⬜ |
| `TOOL-070.34` | 401–404 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-070.35` | 405–469 | ناحیه | `بلوک رویه‌ای 405-469` | 5 | ⬜ |
| `TOOL-070.36` | 470–480 | ناحیه | `۱) ساخت گروه با L_UID` | 0 | ⬜ |
| `TOOL-070.37` | 481–485 | ناحیه | `۳) شناسایی با L_UID (با نرمال‌سازی صفر ابتدایی)` | 0 | ⬜ |
| `TOOL-070.38` | 486–494 | ناحیه | `۴) رد L_UID تکراری` | 0 | ⬜ |
| `TOOL-070.39` | 495–503 | ناحیه | `۵) رد عضویت تکراری در دو گروه` | 0 | ⬜ |
| `TOOL-070.40` | 504–514 | ناحیه | `۶) غیبت روزانه (Exception) + قفل‌نبودن قبل از تردد` | 0 | ⬜ |
| `TOOL-070.41` | 515–545 | ناحیه | `۷) تردد نماینده (L_UID) → فیش اعضای واجد شرایط` | 0 | ⬜ |
| `TOOL-070.42` | 546–565 | ناحیه | `۸) قفل بعد از تردد + رد ویرایش کاربر عادی + اجازهٔ` | 0 | ⬜ |
| `TOOL-070.43` | 566–578 | ناحیه | `۹) تردد مجدد همان نماینده → تکرار، بدون فیش جدید` | 0 | ⬜ |
| `TOOL-070.44` | 579–584 | ناحیه | `۱۰) کشیدن دوبارهٔ همان ردیف (≤۴ ثانیه) → نادیده` | 0 | ⬜ |
| `TOOL-070.45` | 585–588 | ناحیه | `۱۱) استقلال تاریخ‌ها: فردا غیبتی ندارد` | 0 | ⬜ |
| `TOOL-070.46` | 589–595 | ناحیه | `۱۲) API وضعیت غیبت (داخل فرم گروه‌ها)` | 0 | ⬜ |
| `TOOL-070.47` | 596–602 | ناحیه | `۱۳) ذخیرهٔ گروهی غیبت از طریق API` | 0 | ⬜ |
| `TOOL-070.48` | 603–606 | ناحیه | `۱۴) حذف همهٔ غیبت‌ها (همه حاضر)` | 0 | ⬜ |
| `TOOL-070.49` | 607–621 | ناحیه | `۱۵) تردد با L_UID ناشناخته (سابقهٔ نمایندگی دارد)` | 0 | ⬜ |
| `TOOL-070.50` | 622–642 | ناحیه | `۱۵-۵) آمار بازرسی: غایب‌کردن بعد از صدور فیش → توق` | 1 | ⬜ |
| `TOOL-070.51` | 643–647 | ناحیه | `فیش متوقف‌شده در فهرست چاپ مجدد خطاها نیست (فقط pe` | 0 | ⬜ |
| `TOOL-070.52` | 648–660 | ناحیه | `«حاضر» شدن دوباره → برگشت به صف چاپ` | 0 | ⬜ |
| `TOOL-070.53` | 661–667 | ناحیه | `فیش چاپ‌شده هرگز با غیبت تغییر نمی‌کند` | 1 | ⬜ |
| `TOOL-070.54` | 668–674 | ناحیه | `۱۶) گروه غیرفعال → هیچ فیش جدیدی` | 0 | ⬜ |
| `TOOL-070.55` | 675–677 | ناحیه | `۱۷) کسی که امروز از مسیر عادی فیش گرفته، از مسیر گ` | 0 | ⬜ |
| `TOOL-070.56` | 678–700 | ناحیه | `فیش فردی امروز (مثل تردد عادی همان کارمند)` | 0 | ⬜ |
| `TOOL-070.57` | 701–709 | ناحیه | `۱۸) دسترسی‌ها/پاک‌سازی داده` | 5 | ⬜ |

</details>

<details>
<summary><b>TOOL-071</b> — <code>tools/test_guest_cap_offline.php</code> (90 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-071.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `TOOL-071.02` | 17–18 | ناحیه | `تنظیمات تست: مقدار را از $GLOBALS['test_settings']` | 0 | ⬜ |
| `TOOL-071.03` | 19–22 | تابع | `setting` | 0 | ⬜ |
| `TOOL-071.04` | 23–25 | ناحیه | `بلوک رویه‌ای 23-25` | 0 | ⬜ |
| `TOOL-071.05` | 26–31 | تابع | `check` | 0 | ⬜ |
| `TOOL-071.06` | 32–34 | ناحیه | `بلوک رویه‌ای 32-34` | 0 | ⬜ |
| `TOOL-071.07` | 35–37 | ناحیه | `۱) ساعت ۱۲:۱۰ — ۴ نفر وارد شده‌اند، هیچ‌کس قبل از ` | 0 | ⬜ |
| `TOOL-071.08` | 38–42 | ناحیه | `۲) ۱۲:۳۰ — دو نفر که فیش گرفته‌اند بعد از ۱۲ خارج ` | 0 | ⬜ |
| `TOOL-071.09` | 43–46 | ناحیه | `۳) ۱۲:۴۵ — یک نفر جدید وارد می‌شود ⇒ ورودها ۵ ⇒ سق` | 0 | ⬜ |
| `TOOL-071.10` | 47–50 | ناحیه | `۴) خروج قبل از ساعت قطع سقف را کم می‌کند.` | 0 | ⬜ |
| `TOOL-071.11` | 51–54 | ناحیه | `۵) کسی نیامده ⇒ بسته؛ سقف هرگز منفی نمی‌شود.` | 0 | ⬜ |
| `TOOL-071.12` | 55–59 | ناحیه | `۶) تردد در دسترس نیست ⇒ سقف ثابت قدیمی.` | 0 | ⬜ |
| `TOOL-071.13` | 60–63 | ناحیه | `۷) سقف هر کارت همچنان اعمال می‌شود.` | 0 | ⬜ |
| `TOOL-071.14` | 64–65 | ناحیه | `۸) پیش‌فرض‌ها بدون تنظیمات: حالت ثابت و ساعت قطع ۱` | 0 | ⬜ |
| `TOOL-071.15` | 66–73 | ناحیه | `بازهٔ ۱۱ تا ۱۵: قبل از ۱۱ و از ۱۵ به بعد سقف صفر ا` | 0 | ⬜ |
| `TOOL-071.16` | 74–90 | ناحیه | `۹) ساعت‌های قابل تنظیم: شروع، قطع و پایان از فرم ک` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-072</b> — <code>tools/test_live_ui_node.js</code> (322 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-072.01` | 1–37 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `TOOL-072.02` | 38–47 | بلوک | `check` | 0 | ⬜ |
| `TOOL-072.03` | 48–48 | بلوک | `اجرای app.js در یک صفحهٔ ساختگی و برگرداندن window` | 0 | ⬜ |
| `TOOL-072.04` | 49–59 | بلوک | `boot` | 4 | ⬜ |
| `TOOL-072.05` | 60–65 | بلوک | `pressKey` | 0 | ⬜ |
| `TOOL-072.06` | 66–69 | بلوک | `rowVisible` | 0 | ⬜ |
| `TOOL-072.07` | 70–150 | بلوک | `الف) صفحهٔ شناسنامه‌های فنی` | 0 | ⬜ |
| `TOOL-072.08` | 151–250 | بلوک | `ب) کمبوی زندهٔ «سیستم مرتبط»` | 0 | ⬜ |
| `TOOL-072.09` | 251–254 | بلوک | `۱.۳۷.۲: قاعدهٔ حوزه‌محور برای «سیستم مرتبط»` | 0 | ⬜ |
| `TOOL-072.10` | 255–322 | بلوک | `submitForm` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-073</b> — <code>tools/test_module_grant_offline.php</code> (154 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-073.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `TOOL-073.02` | 17–28 | ناحیه | `کش نقش‌ها را به‌جای دیتابیس پر می‌کنیم: بازرس فقط ` | 0 | ⬜ |
| `TOOL-073.03` | 29–34 | تابع | `check` | 0 | ⬜ |
| `TOOL-073.04` | 35–39 | ناحیه | `بلوک رویه‌ای 35-39` | 0 | ⬜ |
| `TOOL-073.05` | 40–99 | ناحیه | `مسیرهای فیش را از خود فایل می‌خوانیم تا آزمون با ت` | 0 | ⬜ |
| `TOOL-073.06` | 100–100 | تابع | `food_ticket_is_primary_admin` | 0 | ⬜ |
| `TOOL-073.07` | 101–126 | ناحیه | `بلوک رویه‌ای 101-126` | 5 | ⬜ |
| `TOOL-073.08` | 127–134 | ناحیه | `پیش‌فرض نقش user: بخش‌های چاپ فیش و نظارت برداشته ` | 0 | ⬜ |
| `TOOL-073.09` | 135–154 | ناحیه | `چاپ فیش فقط برای ادمین اصلی، مدیر پشتیبانی (محدود)` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-074</b> — <code>tools/test_org_role_sync_offline.php</code> (84 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-074.01` | 1–1 | ناحیه | `بلوک رویه‌ای 1-1` | 0 | ⬜ |
| `TOOL-074.02` | 2–12 | ناحیه | `آزمون همگام‌سازی نقش با سمت‌های چارت سازمانی (روی ` | 0 | ⬜ |
| `TOOL-074.03` | 13–13 | تابع | `db` | 0 | ⬜ |
| `TOOL-074.04` | 14–16 | ناحیه | `بلوک رویه‌ای 14-16` | 0 | ⬜ |
| `TOOL-074.05` | 17–17 | تابع | `check` | 0 | ⬜ |
| `TOOL-074.06` | 18–18 | تابع | `roleOf` | 0 | ⬜ |
| `TOOL-074.07` | 19–41 | ناحیه | `بلوک رویه‌ای 19-41` | 6 | ⬜ |
| `TOOL-074.08` | 42–47 | ناحیه | `۱) کاربر «کاربر» معاون می‌شود → مدیر` | 1 | ⬜ |
| `TOOL-074.09` | 48–52 | ناحیه | `۲) کارشناس هم با سمت معاونت به مدیر تبدیل می‌شود (` | 1 | ⬜ |
| `TOOL-074.10` | 53–57 | ناحیه | `۳) برداشتن سمت → بازگشت به نقش قبلی` | 1 | ⬜ |
| `TOOL-074.11` | 58–66 | ناحیه | `۴) بازرس و سوپروایزر با سمت مدیریتی تغییر نمی‌کنند` | 1 | ⬜ |
| `TOOL-074.12` | 67–74 | ناحیه | `۵) عضو مدیریت (جدول چند مدیر) هم مدیر می‌شود` | 1 | ⬜ |
| `TOOL-074.13` | 75–79 | ناحیه | `۶) مدیر دستی (غیر خودکار) با برداشتن سمت‌ها دست‌نخ` | 1 | ⬜ |
| `TOOL-074.14` | 80–84 | ناحیه | `پاکسازی فقط داده‌های همین اجرای مصنوعی؛ سایر واحده` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-075</b> — <code>tools/test_panel_printer_node.js</code> (526 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-075.01` | 1–39 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `TOOL-075.02` | 40–66 | بلوک | `check` | 0 | ⬜ |
| `TOOL-075.03` | 67–67 | بلوک | `تبدیل مقدار ذخیره‌شده به ساختار align_state سرور (` | 0 | ⬜ |
| `TOOL-075.04` | 68–112 | بلوک | `alignStateFrom` | 1 | ⬜ |
| `TOOL-075.05` | 113–148 | بلوک | `response` | 4 | ⬜ |
| `TOOL-075.06` | 149–186 | بلوک | `method` | 0 | ⬜ |
| `TOOL-075.07` | 187–187 | بلوک | `headers` | 0 | ⬜ |
| `TOOL-075.08` | 188–268 | بلوک | `fd` | 1 | ⬜ |
| `TOOL-075.09` | 269–272 | بلوک | `nextTick` | 0 | ⬜ |
| `TOOL-075.10` | 273–273 | بلوک | `تا وقتی شرط درست شود (یا مهلت تمام شود) صبر می‌کند` | 0 | ⬜ |
| `TOOL-075.11` | 274–376 | بلوک | `waitFor` | 0 | ⬜ |
| `TOOL-075.12` | 377–519 | بلوک | `alignBody` | 2 | ⬜ |
| `TOOL-075.13` | 520–526 | بلوک | `finish` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-076</b> — <code>tools/test_print_bottom_fit_offline.php</code> (372 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-076.01` | 1–4 | ناحیه | `بلوک رویه‌ای 1-4` | 0 | ⬜ |
| `TOOL-076.02` | 5–8 | تابع | `system_log` | 0 | ⬜ |
| `TOOL-076.03` | 9–33 | ناحیه | `بلوک رویه‌ای 9-33` | 0 | ⬜ |
| `TOOL-076.04` | 34–37 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-076.05` | 38–41 | ناحیه | `بلوک رویه‌ای 38-41` | 0 | ⬜ |
| `TOOL-076.06` | 42–52 | تابع | `check` | 0 | ⬜ |
| `TOOL-076.07` | 54–76 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-076.08` | 78–83 | تابع | `evalOnce` | 4 | ⬜ |
| `TOOL-076.09` | 84–111 | ناحیه | `بلوک رویه‌ای 84-111` | 0 | ⬜ |
| `TOOL-076.10` | 112–343 | ناحیه | `همان وضعیت شکایت: طرح ۲۰ میلی‌متر بالا خالی داشته ` | **8** | ⬜ |
| `TOOL-076.11` | 344–372 | ناحیه | `پروفایل پیلود: باید دو خط لاگ با فرمان عقب‌راندن و` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-077</b> — <code>tools/test_print_top_trim_offline.php</code> (170 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-077.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `TOOL-077.02` | 26–36 | تابع | `check` | 0 | ⬜ |
| `TOOL-077.03` | 38–60 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-077.04` | 61–68 | ناحیه | `بلوک رویه‌ای 61-68` | 4 | ⬜ |
| `TOOL-077.05` | 69–117 | ناحیه | `همان وضعیت قالب پیش‌فرضِ بدون لوگو: اولین متن در y` | 0 | ⬜ |
| `TOOL-077.06` | 118–118 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-077.07` | 119–170 | ناحیه | `بلوک رویه‌ای 119-170` | 7 | ⬜ |

</details>

<details>
<summary><b>TOOL-078</b> — <code>tools/test_release_134_offline.php</code> (177 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-078.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `TOOL-078.02` | 19–29 | تابع | `check` | 0 | ⬜ |
| `TOOL-078.03` | 31–51 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-078.04` | 52–88 | ناحیه | `بلوک رویه‌ای 52-88` | 1 | ⬜ |
| `TOOL-078.05` | 89–89 | ناحیه | `آزمون واقعی food_ticket_group_resolve: ردیفی که فق` | 0 | ⬜ |
| `TOOL-078.06` | 90–93 | تابع | `food_ticket_code` | 0 | ⬜ |
| `TOOL-078.07` | 94–97 | تابع | `food_ticket_card_key` | 0 | ⬜ |
| `TOOL-078.08` | 98–101 | تابع | `food_ticket_group_find_by_uid` | 0 | ⬜ |
| `TOOL-078.09` | 102–110 | ناحیه | `بلوک رویه‌ای 102-110` | 4 | ⬜ |
| `TOOL-078.10` | 111–114 | تابع | `cfg` | 0 | ⬜ |
| `TOOL-078.11` | 115–118 | تابع | `food_ticket_config` | 0 | ⬜ |
| `TOOL-078.12` | 119–122 | تابع | `setting` | 0 | ⬜ |
| `TOOL-078.13` | 123–125 | ناحیه | `بلوک رویه‌ای 123-125` | 4 | ⬜ |
| `TOOL-078.14` | 126–130 | تابع | `food_ticket_env_value` | 0 | ⬜ |
| `TOOL-078.15` | 131–177 | ناحیه | `بلوک رویه‌ای 131-177` | 7 | ⬜ |

</details>

<details>
<summary><b>TOOL-079</b> — <code>tools/test_role_permissions_offline.php</code> (207 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-079.01` | 1–31 | ناحیه | `بلوک رویه‌ای 1-31` | 0 | ⬜ |
| `TOOL-079.02` | 32–42 | تابع | `check` | 0 | ⬜ |
| `TOOL-079.03` | 44–68 | کلاس | `MysqlishSqlite` | 5 | ⬜ |
| `TOOL-079.04` | 69–74 | ناحیه | `بلوک رویه‌ای 69-74` | 4 | ⬜ |
| `TOOL-079.05` | 75–79 | تابع | `db` | 0 | ⬜ |
| `TOOL-079.06` | 80–82 | ناحیه | `بلوک رویه‌ای 80-82` | 0 | ⬜ |
| `TOOL-079.07` | 83–86 | تابع | `setting` | 0 | ⬜ |
| `TOOL-079.08` | 87–90 | تابع | `save_setting` | 0 | ⬜ |
| `TOOL-079.09` | 91–93 | ناحیه | `بلوک رویه‌ای 91-93` | 0 | ⬜ |
| `TOOL-079.10` | 94–102 | تابع | `seedRows` | 1 | ⬜ |
| `TOOL-079.11` | 104–111 | تابع | `rowsOf` | 0 | ⬜ |
| `TOOL-079.12` | 113–118 | تابع | `sortedCopy` | 0 | ⬜ |
| `TOOL-079.13` | 119–145 | ناحیه | `بلوک رویه‌ای 119-145` | 0 | ⬜ |
| `TOOL-079.14` | 146–181 | ناحیه | `حتی اگر پیش‌فرض کد عوض شود، نقش ویرایش‌شده دست‌نخو` | 0 | ⬜ |
| `TOOL-079.15` | 182–183 | ناحیه | `نقش user: ردیف‌هایش دقیقاً همان پیش‌فرض قدیم است ⇒` | 0 | ⬜ |
| `TOOL-079.16` | 184–207 | ناحیه | `نقش support_manager: یک کد عمداً برداشته شده ⇒ بای` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-080</b> — <code>tools/test_scan_errors_offline.php</code> (115 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-080.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `TOOL-080.02` | 19–29 | تابع | `check` | 0 | ⬜ |
| `TOOL-080.03` | 30–115 | ناحیه | `بلوک رویه‌ای 30-115` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-081</b> — <code>tools/test_source_delete_offline.php</code> (187 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-081.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `TOOL-081.02` | 25–28 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-081.03` | 29–32 | ناحیه | `بلوک رویه‌ای 29-32` | 0 | ⬜ |
| `TOOL-081.04` | 33–70 | تابع | `extractFunction` | 0 | ⬜ |
| `TOOL-081.05` | 71–90 | ناحیه | `بلوک رویه‌ای 71-90` | 4 | ⬜ |
| `TOOL-081.06` | 91–101 | تابع | `check` | 0 | ⬜ |
| `TOOL-081.07` | 102–187 | ناحیه | `بلوک رویه‌ای 102-187` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-082</b> — <code>tools/test_ticket_txn_offline.php</code> (234 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-082.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `TOOL-082.02` | 29–39 | تابع | `check` | 0 | ⬜ |
| `TOOL-082.03` | 41–61 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-082.04` | 63–74 | کلاس | `FakeStmt` | 0 | ⬜ |
| `TOOL-082.05` | 76–131 | کلاس | `FakeDb` | 4 | ⬜ |
| `TOOL-082.06` | 132–133 | ناحیه | `بلوک رویه‌ای 132-133` | 0 | ⬜ |
| `TOOL-082.07` | 134–137 | تابع | `db` | 0 | ⬜ |
| `TOOL-082.08` | 138–138 | ناحیه | `بلوک رویه‌ای 138-138` | 0 | ⬜ |
| `TOOL-082.09` | 139–142 | تابع | `system_log` | 0 | ⬜ |
| `TOOL-082.10` | 143–143 | ناحیه | `بلوک رویه‌ای 143-143` | 0 | ⬜ |
| `TOOL-082.11` | 144–147 | تابع | `__test_register_shutdown` | 0 | ⬜ |
| `TOOL-082.12` | 148–214 | ناحیه | `بلوک رویه‌ای 148-214` | 4 | ⬜ |
| `TOOL-082.13` | 215–215 | ناحیه | `۱.۳۷.۲: مرز بدنه با «اکشن بعدی» تعیین می‌شود، نه ب` | 0 | ⬜ |
| `TOOL-082.14` | 216–234 | ناحیه | `کامنت/اعتبارسنجی در create_ticket این آزمون را بی‌` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-083</b> — <code>tools/test_traffic_form_offline.php</code> (75 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-083.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `TOOL-083.02` | 13–14 | ناحیه | `تابع اعتبارسنجی کد ملی در index.php است؛ اینجا فقط` | 0 | ⬜ |
| `TOOL-083.03` | 15–27 | تابع | `valid_iranian_national_code` | 0 | ⬜ |
| `TOOL-083.04` | 28–33 | ناحیه | `بلوک رویه‌ای 28-33` | 0 | ⬜ |
| `TOOL-083.05` | 34–39 | تابع | `check` | 0 | ⬜ |
| `TOOL-083.06` | 41–49 | تابع | `tryValidate` | 0 | ⬜ |
| `TOOL-083.07` | 51–55 | ناحیه | `کد ملی اشتباه ⇒ فیلد national_code` | 0 | ⬜ |
| `TOOL-083.08` | 56–59 | ناحیه | `کد ملی با ارقام فارسی و معتبر ⇒ بدون خطا` | 0 | ⬜ |
| `TOOL-083.09` | 60–63 | ناحیه | `نام خالی ⇒ فیلد full_name` | 0 | ⬜ |
| `TOOL-083.10` | 64–67 | ناحیه | `ورودی درست ⇒ بدون خطا (با کد ملی موردقبول stub)` | 0 | ⬜ |
| `TOOL-083.11` | 68–75 | ناحیه | `خطای عمومی (بدون فیلد) ⇒ TrafficInputException با ` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-084</b> — <code>tools/test_traffic_import_offline.php</code> (179 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-084.01` | 1–179 | ناحیه | `بلوک رویه‌ای 1-179` | **9** | ⬜ |

</details>

<details>
<summary><b>TOOL-085</b> — <code>tools/test_ui_features_offline.php</code> (45 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-085.01` | 1–45 | ناحیه | `بلوک رویه‌ای 1-45` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-086</b> — <code>tools/test_web_host_offline.php</code> (142 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-086.01` | 1–58 | ناحیه | `بلوک رویه‌ای 1-58` | 0 | ⬜ |
| `TOOL-086.02` | 59–83 | ناحیه | `۱) نسخهٔ فعلی روی دیسک: رفتار «قبل» نباید تغییر کن` | 0 | ⬜ |
| `TOOL-086.03` | 84–107 | ناحیه | `۲) نسخهٔ تازه‌تر پنل: شمارهٔ نسخه‌ها و نام فایل‌ها` | 0 | ⬜ |
| `TOOL-086.04` | 108–114 | ناحیه | `۳) نسخه‌ای که لنگر brand را ندارد (نام برند عوض شد` | 0 | ⬜ |
| `TOOL-086.05` | 115–120 | ناحیه | `۴) خروجی دوباره میزبانی شود → تزریق مضاعف/پیشوند م` | 0 | ⬜ |
| `TOOL-086.06` | 121–127 | ناحیه | `۵) حالت پنل مستقل (C#): مسیر ../assets/style.css ب` | 0 | ⬜ |
| `TOOL-086.07` | 128–142 | ناحیه | `۶) HTML حداقلی/شکسته: نباید خطا بدهد و باید بوت‌اس` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-087</b> — <code>tools/topic_index.py</code> (209 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-087.01` | 1–26 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-087.02` | 27–30 | بلوک | `read` | 0 | ⬜ |
| `TOOL-087.03` | 31–32 | بلوک | `-` | 0 | ⬜ |
| `TOOL-087.04` | 33–34 | بلوک | `-` | 0 | ⬜ |
| `TOOL-087.05` | 35–51 | بلوک | `page_labels` | 0 | ⬜ |
| `TOOL-087.06` | 52–67 | بلوک | `permission_catalog` | 0 | ⬜ |
| `TOOL-087.07` | 68–75 | بلوک | `page_permissions` | 0 | ⬜ |
| `TOOL-087.08` | 76–87 | بلوک | `db_tables` | 0 | ⬜ |
| `TOOL-087.09` | 88–89 | بلوک | `-` | 0 | ⬜ |
| `TOOL-087.10` | 90–97 | بلوک | `-` | 1 | ⬜ |
| `TOOL-087.11` | 98–117 | بلوک | `build_inverted` | 0 | ⬜ |
| `TOOL-087.12` | 118–157 | بلوک | `rank` | 1 | ⬜ |
| `TOOL-087.13` | 158–209 | بلوک | `main` | 0 | ⬜ |

</details>

### DB — `DB`


<details>
<summary><b>DB-001</b> — <code>00_DIAGNOSE_TRAFFIC_FK.sql</code> (24 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-001.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-002</b> — <code>install-empty-db.sql</code> (1,613 خط، 121 آدرس، بازبینی‌شده 0/121)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-002.01` | 1–2 | بلوک | `=` | 0 | ⬜ |
| `DB-002.02` | 3–19 | بلوک | `=` | 0 | ⬜ |
| `DB-002.03` | 20–21 | بلوک | `=` | 0 | ⬜ |
| `DB-002.04` | 22–26 | بلوک | `CREATE TABLE settings` | 0 | ⬜ |
| `DB-002.05` | 27–36 | بلوک | `CREATE TABLE departments` | 0 | ⬜ |
| `DB-002.06` | 37–50 | بلوک | `CREATE TABLE org_companies` | 1 | ⬜ |
| `DB-002.07` | 51–69 | بلوک | `CREATE TABLE org_units` | 0 | ⬜ |
| `DB-002.08` | 70–72 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-002.09` | 73–76 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-002.10` | 77–84 | بلوک | `CREATE TABLE org_unit_managers` | 0 | ⬜ |
| `DB-002.11` | 85–120 | بلوک | `CREATE TABLE users` | 1 | ⬜ |
| `DB-002.12` | 121–122 | بلوک | `ALTER TABLE departments` | 1 | ⬜ |
| `DB-002.13` | 123–133 | بلوک | `CREATE TABLE handling_units` | 1 | ⬜ |
| `DB-002.14` | 134–135 | بلوک | `ALTER TABLE users` | 1 | ⬜ |
| `DB-002.15` | 136–143 | بلوک | `CREATE TABLE cd_dvd_types` | 1 | ⬜ |
| `DB-002.16` | 144–176 | بلوک | `CREATE TABLE cd_dvd_records` | 1 | ⬜ |
| `DB-002.17` | 177–187 | بلوک | `CREATE TABLE traffic_destinations` | 0 | ⬜ |
| `DB-002.18` | 188–216 | بلوک | `CREATE TABLE traffic_visits` | 1 | ⬜ |
| `DB-002.19` | 217–233 | بلوک | `CREATE TABLE traffic_visit_history` | 1 | ⬜ |
| `DB-002.20` | 234–243 | بلوک | `INSERT INTO traffic_destinations` | 1 | ⬜ |
| `DB-002.21` | 244–252 | بلوک | `CREATE TABLE categories` | 0 | ⬜ |
| `DB-002.22` | 253–278 | بلوک | `CREATE TABLE service_catalog` | 1 | ⬜ |
| `DB-002.23` | 279–291 | بلوک | `CREATE TABLE service_catalog_fields` | 1 | ⬜ |
| `DB-002.24` | 292–358 | بلوک | `CREATE TABLE tickets` | 1 | ⬜ |
| `DB-002.25` | 359–370 | بلوک | `CREATE TABLE ticket_messages` | 1 | ⬜ |
| `DB-002.26` | 371–384 | بلوک | `CREATE TABLE ticket_ratings` | 1 | ⬜ |
| `DB-002.27` | 385–395 | بلوک | `CREATE TABLE ticket_attachments` | 1 | ⬜ |
| `DB-002.28` | 396–434 | بلوک | `CREATE TABLE activity_logs` | 1 | ⬜ |
| `DB-002.29` | 435–441 | بلوک | `CREATE TABLE role_permissions` | 0 | ⬜ |
| `DB-002.30` | 442–459 | بلوک | `CREATE TABLE notifications` | 1 | ⬜ |
| `DB-002.31` | 460–467 | بلوک | `CREATE TABLE login_attempts` | 1 | ⬜ |
| `DB-002.32` | 468–477 | بلوک | `CREATE TABLE ticket_sla_alerts` | 1 | ⬜ |
| `DB-002.33` | 478–501 | بلوک | `CREATE TABLE ticket_events` | 1 | ⬜ |
| `DB-002.34` | 502–515 | بلوک | `CREATE TABLE knowledge_articles` | 1 | ⬜ |
| `DB-002.35` | 516–525 | بلوک | `CREATE TABLE holidays` | 1 | ⬜ |
| `DB-002.36` | 526–581 | بلوک | `CREATE TABLE assets` | 1 | ⬜ |
| `DB-002.37` | 582–583 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-002.38` | 584–598 | بلوک | `CREATE TABLE asset_memory_modules` | 1 | ⬜ |
| `DB-002.39` | 599–614 | بلوک | `CREATE TABLE asset_storage_devices` | 1 | ⬜ |
| `DB-002.40` | 615–627 | بلوک | `CREATE TABLE asset_graphics_adapters` | 1 | ⬜ |
| `DB-002.41` | 628–645 | بلوک | `CREATE TABLE asset_peripherals` | 1 | ⬜ |
| `DB-002.42` | 646–656 | بلوک | `CREATE TABLE asset_inventory_history` | 1 | ⬜ |
| `DB-002.43` | 657–674 | بلوک | `CREATE TABLE asset_history_events` | 1 | ⬜ |
| `DB-002.44` | 675–687 | بلوک | `CREATE TABLE asset_relations` | 1 | ⬜ |
| `DB-002.45` | 688–769 | بلوک | `CREATE TABLE asset_profiles` | 1 | ⬜ |
| `DB-002.46` | 770–787 | بلوک | `CREATE TABLE ola_policies` | 1 | ⬜ |
| `DB-002.47` | 788–797 | بلوک | `CREATE TABLE ticket_ola_alerts` | 1 | ⬜ |
| `DB-002.48` | 798–799 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-002.49` | 800–828 | بلوک | `CREATE TABLE change_records` | 1 | ⬜ |
| `DB-002.50` | 829–840 | بلوک | `CREATE TABLE change_approvals` | 1 | ⬜ |
| `DB-002.51` | 841–851 | بلوک | `CREATE TABLE change_ticket_links` | 1 | ⬜ |
| `DB-002.52` | 852–875 | بلوک | `CREATE TABLE problem_records` | 1 | ⬜ |
| `DB-002.53` | 876–886 | بلوک | `CREATE TABLE problem_ticket_links` | 1 | ⬜ |
| `DB-002.54` | 887–917 | بلوک | `CREATE TABLE food_ticket_config` | 1 | ⬜ |
| `DB-002.55` | 918–956 | بلوک | `CREATE TABLE food_ticket_events` | 1 | ⬜ |
| `DB-002.56` | 957–972 | بلوک | `CREATE TABLE food_ticket_worker_status` | 1 | ⬜ |
| `DB-002.57` | 973–986 | بلوک | `CREATE TABLE food_ticket_guest_cards` | 1 | ⬜ |
| `DB-002.58` | 987–1005 | بلوک | `CREATE TABLE food_ticket_groups` | 1 | ⬜ |
| `DB-002.59` | 1006–1017 | بلوک | `CREATE TABLE food_ticket_group_members` | 1 | ⬜ |
| `DB-002.60` | 1018–1044 | بلوک | `CREATE TABLE food_ticket_group_runs` | 1 | ⬜ |
| `DB-002.61` | 1045–1060 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-002.62` | 1061–1073 | بلوک | `CREATE TABLE food_ticket_day_visits` | 1 | ⬜ |
| `DB-002.63` | 1074–1084 | بلوک | `CREATE TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-002.64` | 1085–1100 | بلوک | `CREATE TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-002.65` | 1101–1127 | بلوک | `CREATE TABLE system_logs` | 0 | ⬜ |
| `DB-002.66` | 1128–1133 | بلوک | `CREATE TABLE ticket_number_seq` | 0 | ⬜ |
| `DB-002.67` | 1134–1142 | بلوک | `CREATE TABLE api_rate_limits` | 1 | ⬜ |
| `DB-002.68` | 1143–1155 | بلوک | `CREATE TABLE domain_scan_runs` | 1 | ⬜ |
| `DB-002.69` | 1156–1169 | بلوک | `CREATE TABLE domain_scan_queue` | 1 | ⬜ |
| `DB-002.70` | 1170–1170 | بلوک | `CREATE INDEX idx_tickets_status_priority` | 0 | ⬜ |
| `DB-002.71` | 1171–1171 | بلوک | `CREATE INDEX idx_users_is_active` | 0 | ⬜ |
| `DB-002.72` | 1172–1172 | بلوک | `CREATE INDEX idx_assets_lifecycle_status` | 0 | ⬜ |
| `DB-002.73` | 1173–1173 | بلوک | `CREATE INDEX idx_ola_priority_active` | 0 | ⬜ |
| `DB-002.74` | 1174–1174 | بلوک | `CREATE INDEX idx_food_ticket_events_event_type` | 0 | ⬜ |
| `DB-002.75` | 1175–1175 | بلوک | `CREATE INDEX idx_login_attempts_locked_until` | 0 | ⬜ |
| `DB-002.76` | 1176–1176 | بلوک | `CREATE INDEX idx_ticket_sla_alerts_alert_type` | 0 | ⬜ |
| `DB-002.77` | 1177–1177 | بلوک | `CREATE INDEX idx_ticket_ola_alerts_alert_type` | 0 | ⬜ |
| `DB-002.78` | 1178–1178 | بلوک | `CREATE INDEX idx_ticket_events_event_type` | 0 | ⬜ |
| `DB-002.79` | 1179–1179 | بلوک | `CREATE INDEX idx_knowledge_articles_is_published` | 0 | ⬜ |
| `DB-002.80` | 1180–1180 | بلوک | `CREATE INDEX idx_holidays_is_active` | 0 | ⬜ |
| `DB-002.81` | 1181–1181 | بلوک | `CREATE INDEX idx_departments_is_active` | 0 | ⬜ |
| `DB-002.82` | 1182–1182 | بلوک | `CREATE INDEX idx_handling_units_is_active` | 0 | ⬜ |
| `DB-002.83` | 1183–1183 | بلوک | `CREATE INDEX idx_categories_group_active` | 0 | ⬜ |
| `DB-002.84` | 1184–1184 | بلوک | `CREATE INDEX idx_service_catalog_group_active` | 0 | ⬜ |
| `DB-002.85` | 1185–1185 | بلوک | `CREATE INDEX idx_cd_dvd_records_media` | 0 | ⬜ |
| `DB-002.86` | 1186–1186 | بلوک | `CREATE INDEX idx_traffic_destinations_is_active` | 0 | ⬜ |
| `DB-002.87` | 1187–1187 | بلوک | `CREATE INDEX idx_domain_scan_runs_status` | 0 | ⬜ |
| `DB-002.88` | 1188–1190 | بلوک | `CREATE INDEX idx_domain_scan_queue_status` | 0 | ⬜ |
| `DB-002.89` | 1191–1200 | بلوک | `CREATE TABLE food_ticket_card_map` | 1 | ⬜ |
| `DB-002.90` | 1201–1213 | بلوک | `CREATE TABLE food_ticket_templates` | 1 | ⬜ |
| `DB-002.91` | 1214–1215 | بلوک | `=` | 0 | ⬜ |
| `DB-002.92` | 1216–1216 | بلوک | `=` | 0 | ⬜ |
| `DB-002.93` | 1217–1221 | بلوک | `INSERT INTO settings` | 1 | ⬜ |
| `DB-002.94` | 1222–1224 | بلوک | `INSERT INTO settings` | 1 | ⬜ |
| `DB-002.95` | 1225–1226 | بلوک | `=` | 0 | ⬜ |
| `DB-002.96` | 1227–1234 | بلوک | `=` | 0 | ⬜ |
| `DB-002.97` | 1235–1236 | بلوک | `=` | 0 | ⬜ |
| `DB-002.98` | 1237–1241 | بلوک | `=` | 0 | ⬜ |
| `DB-002.99` | 1242–1253 | بلوک | `CREATE TABLE food_catalog` | 1 | ⬜ |
| `DB-002.100` | 1254–1268 | بلوک | `CREATE TABLE food_calendar` | 1 | ⬜ |
| `DB-002.101` | 1269–1283 | بلوک | `CREATE TABLE food_calendar_items` | 1 | ⬜ |
| `DB-002.102` | 1284–1299 | بلوک | `CREATE TABLE food_orders` | 1 | ⬜ |
| `DB-002.103` | 1300–1317 | بلوک | `CREATE TABLE food_order_logs` | 1 | ⬜ |
| `DB-002.104` | 1318–1335 | بلوک | `CREATE TABLE food_order_cancel_requests` | 1 | ⬜ |
| `DB-002.105` | 1336–1339 | بلوک | `=` | 0 | ⬜ |
| `DB-002.106` | 1340–1341 | بلوک | `=` | 0 | ⬜ |
| `DB-002.107` | 1342–1353 | بلوک | `CREATE TABLE chatbot_faq` | 0 | ⬜ |
| `DB-002.108` | 1354–1366 | بلوک | `CREATE TABLE chatbot_logs` | 0 | ⬜ |
| `DB-002.109` | 1367–1375 | بلوک | `CREATE TABLE system_incidents` | 1 | ⬜ |
| `DB-002.110` | 1376–1399 | بلوک | `CREATE TABLE food_guest_requests` | 1 | ⬜ |
| `DB-002.111` | 1400–1426 | بلوک | `CREATE TABLE food_meal_reconciliations` | 1 | ⬜ |
| `DB-002.112` | 1427–1448 | بلوک | `CREATE TABLE food_monthly_invoices` | 0 | ⬜ |
| `DB-002.113` | 1449–1462 | بلوک | `CREATE TABLE user_access_groups` | 1 | ⬜ |
| `DB-002.114` | 1463–1474 | بلوک | `CREATE TABLE user_access_group_members` | 1 | ⬜ |
| `DB-002.115` | 1475–1490 | بلوک | `CREATE TABLE user_access_group_permissions` | 1 | ⬜ |
| `DB-002.116` | 1491–1516 | بلوک | `CREATE TABLE log_issues` | 1 | ⬜ |
| `DB-002.117` | 1517–1526 | بلوک | `CREATE TABLE logging_health_alerts` | 1 | ⬜ |
| `DB-002.118` | 1527–1542 | بلوک | `CREATE TABLE log_issue_history` | 0 | ⬜ |
| `DB-002.119` | 1543–1549 | بلوک | `CREATE TABLE log_issue_events` | 0 | ⬜ |
| `DB-002.120` | 1550–1568 | بلوک | `CREATE TABLE log_issue_notifications` | 1 | ⬜ |
| `DB-002.121` | 1569–1613 | بلوک | `CREATE TABLE schema_updates` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-003</b> — <code>migrations/001-local-accounts.sql</code> (7 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-003.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-003.02` | 3–4 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-003.03` | 5–7 | بلوک | `ALTER TABLE users` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-004</b> — <code>migrations/002-traffic-notices.sql</code> (6 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-004.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-004.02` | 4–4 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-004.03` | 5–6 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-005</b> — <code>migrations/003-sync-schema-151.sql</code> (1,771 خط، 750 آدرس، بازبینی‌شده 0/750)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-005.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-005.02` | 5–8 | بلوک | `CREATE TABLE settings` | 0 | ⬜ |
| `DB-005.03` | 9–17 | بلوک | `CREATE TABLE departments` | 0 | ⬜ |
| `DB-005.04` | 18–31 | بلوک | `CREATE TABLE org_units` | 0 | ⬜ |
| `DB-005.05` | 32–38 | بلوک | `CREATE TABLE org_unit_managers` | 0 | ⬜ |
| `DB-005.06` | 39–71 | بلوک | `CREATE TABLE users` | 1 | ⬜ |
| `DB-005.07` | 72–81 | بلوک | `CREATE TABLE handling_units` | 1 | ⬜ |
| `DB-005.08` | 82–86 | بلوک | `CREATE TABLE cd_dvd_types` | 0 | ⬜ |
| `DB-005.09` | 87–118 | بلوک | `CREATE TABLE cd_dvd_records` | 1 | ⬜ |
| `DB-005.10` | 119–126 | بلوک | `CREATE TABLE traffic_destinations` | 0 | ⬜ |
| `DB-005.11` | 127–152 | بلوک | `CREATE TABLE traffic_visits` | 1 | ⬜ |
| `DB-005.12` | 153–160 | بلوک | `CREATE TABLE categories` | 0 | ⬜ |
| `DB-005.13` | 161–185 | بلوک | `CREATE TABLE service_catalog` | 1 | ⬜ |
| `DB-005.14` | 186–197 | بلوک | `CREATE TABLE service_catalog_fields` | 1 | ⬜ |
| `DB-005.15` | 198–263 | بلوک | `CREATE TABLE tickets` | 1 | ⬜ |
| `DB-005.16` | 264–274 | بلوک | `CREATE TABLE ticket_messages` | 1 | ⬜ |
| `DB-005.17` | 275–287 | بلوک | `CREATE TABLE ticket_ratings` | 1 | ⬜ |
| `DB-005.18` | 288–297 | بلوک | `CREATE TABLE ticket_attachments` | 1 | ⬜ |
| `DB-005.19` | 298–322 | بلوک | `CREATE TABLE activity_logs` | 1 | ⬜ |
| `DB-005.20` | 323–328 | بلوک | `CREATE TABLE role_permissions` | 0 | ⬜ |
| `DB-005.21` | 329–342 | بلوک | `CREATE TABLE notifications` | 1 | ⬜ |
| `DB-005.22` | 343–349 | بلوک | `CREATE TABLE login_attempts` | 1 | ⬜ |
| `DB-005.23` | 350–358 | بلوک | `CREATE TABLE ticket_sla_alerts` | 1 | ⬜ |
| `DB-005.24` | 359–372 | بلوک | `CREATE TABLE ticket_events` | 1 | ⬜ |
| `DB-005.25` | 373–385 | بلوک | `CREATE TABLE knowledge_articles` | 1 | ⬜ |
| `DB-005.26` | 386–394 | بلوک | `CREATE TABLE holidays` | 1 | ⬜ |
| `DB-005.27` | 395–445 | بلوک | `CREATE TABLE assets` | 1 | ⬜ |
| `DB-005.28` | 446–459 | بلوک | `CREATE TABLE asset_memory_modules` | 1 | ⬜ |
| `DB-005.29` | 460–474 | بلوک | `CREATE TABLE asset_storage_devices` | 1 | ⬜ |
| `DB-005.30` | 475–486 | بلوک | `CREATE TABLE asset_graphics_adapters` | 1 | ⬜ |
| `DB-005.31` | 487–503 | بلوک | `CREATE TABLE asset_peripherals` | 1 | ⬜ |
| `DB-005.32` | 504–513 | بلوک | `CREATE TABLE asset_inventory_history` | 1 | ⬜ |
| `DB-005.33` | 514–530 | بلوک | `CREATE TABLE asset_history_events` | 1 | ⬜ |
| `DB-005.34` | 531–542 | بلوک | `CREATE TABLE asset_relations` | 1 | ⬜ |
| `DB-005.35` | 543–623 | بلوک | `CREATE TABLE asset_profiles` | 1 | ⬜ |
| `DB-005.36` | 624–640 | بلوک | `CREATE TABLE ola_policies` | 1 | ⬜ |
| `DB-005.37` | 641–649 | بلوک | `CREATE TABLE ticket_ola_alerts` | 1 | ⬜ |
| `DB-005.38` | 650–677 | بلوک | `CREATE TABLE change_records` | 1 | ⬜ |
| `DB-005.39` | 678–688 | بلوک | `CREATE TABLE change_approvals` | 1 | ⬜ |
| `DB-005.40` | 689–698 | بلوک | `CREATE TABLE change_ticket_links` | 1 | ⬜ |
| `DB-005.41` | 699–721 | بلوک | `CREATE TABLE problem_records` | 1 | ⬜ |
| `DB-005.42` | 722–731 | بلوک | `CREATE TABLE problem_ticket_links` | 1 | ⬜ |
| `DB-005.43` | 732–749 | بلوک | `CREATE TABLE food_ticket_config` | 1 | ⬜ |
| `DB-005.44` | 750–786 | بلوک | `CREATE TABLE food_ticket_events` | 1 | ⬜ |
| `DB-005.45` | 787–799 | بلوک | `CREATE TABLE food_ticket_worker_status` | 1 | ⬜ |
| `DB-005.46` | 800–812 | بلوک | `CREATE TABLE food_ticket_guest_cards` | 1 | ⬜ |
| `DB-005.47` | 813–828 | بلوک | `CREATE TABLE food_ticket_groups` | 1 | ⬜ |
| `DB-005.48` | 829–838 | بلوک | `CREATE TABLE food_ticket_group_members` | 1 | ⬜ |
| `DB-005.49` | 839–862 | بلوک | `CREATE TABLE food_ticket_group_runs` | 1 | ⬜ |
| `DB-005.50` | 863–876 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-005.51` | 877–886 | بلوک | `CREATE TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-005.52` | 887–901 | بلوک | `CREATE TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-005.53` | 902–913 | بلوک | `CREATE TABLE system_logs` | 0 | ⬜ |
| `DB-005.54` | 914–924 | بلوک | `CREATE TABLE chatbot_faq` | 0 | ⬜ |
| `DB-005.55` | 925–936 | بلوک | `CREATE TABLE chatbot_logs` | 0 | ⬜ |
| `DB-005.56` | 937–944 | بلوک | `CREATE TABLE system_incidents` | 1 | ⬜ |
| `DB-005.57` | 945–948 | بلوک | `CREATE TABLE ticket_number_seq` | 0 | ⬜ |
| `DB-005.58` | 949–955 | بلوک | `CREATE TABLE api_rate_limits` | 1 | ⬜ |
| `DB-005.59` | 956–967 | بلوک | `CREATE TABLE domain_scan_runs` | 1 | ⬜ |
| `DB-005.60` | 968–979 | بلوک | `CREATE TABLE domain_scan_queue` | 1 | ⬜ |
| `DB-005.61` | 980–988 | بلوک | `CREATE TABLE food_ticket_card_map` | 1 | ⬜ |
| `DB-005.62` | 989–999 | بلوک | `CREATE TABLE food_ticket_templates` | 1 | ⬜ |
| `DB-005.63` | 1000–1010 | بلوک | `CREATE TABLE food_catalog` | 1 | ⬜ |
| `DB-005.64` | 1011–1028 | بلوک | `CREATE TABLE food_guest_requests` | 1 | ⬜ |
| `DB-005.65` | 1029–1041 | بلوک | `CREATE TABLE food_calendar` | 1 | ⬜ |
| `DB-005.66` | 1042–1055 | بلوک | `CREATE TABLE food_calendar_items` | 1 | ⬜ |
| `DB-005.67` | 1056–1069 | بلوک | `CREATE TABLE food_orders` | 1 | ⬜ |
| `DB-005.68` | 1070–1086 | بلوک | `CREATE TABLE food_order_logs` | 1 | ⬜ |
| `DB-005.69` | 1087–1087 | بلوک | `ALTER TABLE settings` | 0 | ⬜ |
| `DB-005.70` | 1088–1088 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-005.71` | 1089–1089 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-005.72` | 1090–1090 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-005.73` | 1091–1091 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-005.74` | 1092–1092 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-005.75` | 1093–1093 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-005.76` | 1094–1094 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-005.77` | 1095–1095 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-005.78` | 1096–1096 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-005.79` | 1097–1097 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-005.80` | 1098–1098 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-005.81` | 1099–1099 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-005.82` | 1100–1100 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-005.83` | 1101–1101 | بلوک | `ALTER TABLE org_unit_managers` | 0 | ⬜ |
| `DB-005.84` | 1102–1102 | بلوک | `ALTER TABLE org_unit_managers` | 0 | ⬜ |
| `DB-005.85` | 1103–1103 | بلوک | `ALTER TABLE org_unit_managers` | 0 | ⬜ |
| `DB-005.86` | 1104–1104 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.87` | 1105–1105 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.88` | 1106–1106 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.89` | 1107–1107 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.90` | 1108–1108 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.91` | 1109–1109 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.92` | 1110–1110 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.93` | 1111–1111 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.94` | 1112–1112 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.95` | 1113–1113 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.96` | 1114–1114 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.97` | 1115–1115 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.98` | 1116–1116 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.99` | 1117–1117 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.100` | 1118–1118 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.101` | 1119–1119 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.102` | 1120–1120 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.103` | 1121–1121 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.104` | 1122–1122 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.105` | 1123–1123 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.106` | 1124–1124 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.107` | 1125–1125 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.108` | 1126–1126 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.109` | 1127–1127 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.110` | 1128–1128 | بلوک | `ALTER TABLE handling_units` | 0 | ⬜ |
| `DB-005.111` | 1129–1129 | بلوک | `ALTER TABLE handling_units` | 0 | ⬜ |
| `DB-005.112` | 1130–1130 | بلوک | `ALTER TABLE handling_units` | 0 | ⬜ |
| `DB-005.113` | 1131–1131 | بلوک | `ALTER TABLE handling_units` | 0 | ⬜ |
| `DB-005.114` | 1132–1132 | بلوک | `ALTER TABLE handling_units` | 0 | ⬜ |
| `DB-005.115` | 1133–1133 | بلوک | `ALTER TABLE cd_dvd_types` | 0 | ⬜ |
| `DB-005.116` | 1134–1134 | بلوک | `ALTER TABLE cd_dvd_types` | 0 | ⬜ |
| `DB-005.117` | 1135–1135 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.118` | 1136–1136 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.119` | 1137–1137 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.120` | 1138–1138 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.121` | 1139–1139 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.122` | 1140–1140 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.123` | 1141–1141 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.124` | 1142–1142 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.125` | 1143–1143 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.126` | 1144–1144 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.127` | 1145–1145 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.128` | 1146–1146 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.129` | 1147–1147 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.130` | 1148–1148 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.131` | 1149–1149 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.132` | 1150–1150 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.133` | 1151–1151 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.134` | 1152–1152 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.135` | 1153–1153 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.136` | 1154–1154 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-005.137` | 1155–1155 | بلوک | `ALTER TABLE cd_dvd_records` | 1 | ⬜ |
| `DB-005.138` | 1156–1156 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |
| `DB-005.139` | 1157–1157 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |
| `DB-005.140` | 1158–1158 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |
| `DB-005.141` | 1159–1159 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |
| `DB-005.142` | 1160–1160 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.143` | 1161–1161 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.144` | 1162–1162 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.145` | 1163–1163 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.146` | 1164–1164 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.147` | 1165–1165 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.148` | 1166–1166 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.149` | 1167–1167 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.150` | 1168–1168 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.151` | 1169–1169 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.152` | 1170–1170 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.153` | 1171–1171 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.154` | 1172–1172 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.155` | 1173–1173 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.156` | 1174–1174 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.157` | 1175–1175 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.158` | 1176–1176 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-005.159` | 1177–1177 | بلوک | `ALTER TABLE traffic_visits` | 1 | ⬜ |
| `DB-005.160` | 1178–1178 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-005.161` | 1179–1179 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-005.162` | 1180–1180 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-005.163` | 1181–1181 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-005.164` | 1182–1182 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-005.165` | 1183–1183 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.166` | 1184–1184 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.167` | 1185–1185 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.168` | 1186–1186 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.169` | 1187–1187 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.170` | 1188–1188 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.171` | 1189–1189 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.172` | 1190–1190 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.173` | 1191–1191 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.174` | 1192–1192 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |
| `DB-005.175` | 1193–1193 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.176` | 1194–1194 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.177` | 1195–1195 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.178` | 1196–1196 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-005.179` | 1197–1197 | بلوک | `ALTER TABLE service_catalog` | 1 | ⬜ |
| `DB-005.180` | 1198–1198 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-005.181` | 1199–1199 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-005.182` | 1200–1200 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-005.183` | 1201–1201 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-005.184` | 1202–1202 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-005.185` | 1203–1203 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-005.186` | 1204–1204 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-005.187` | 1205–1205 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.188` | 1206–1206 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.189` | 1207–1207 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.190` | 1208–1208 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.191` | 1209–1209 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.192` | 1210–1210 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.193` | 1211–1211 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.194` | 1212–1212 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.195` | 1213–1213 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.196` | 1214–1214 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.197` | 1215–1215 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.198` | 1216–1216 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.199` | 1217–1217 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.200` | 1218–1218 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.201` | 1219–1219 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.202` | 1220–1220 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.203` | 1221–1221 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.204` | 1222–1222 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.205` | 1223–1223 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.206` | 1224–1224 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.207` | 1225–1225 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.208` | 1226–1226 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.209` | 1227–1227 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.210` | 1228–1228 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.211` | 1229–1229 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.212` | 1230–1230 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.213` | 1231–1231 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.214` | 1232–1232 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.215` | 1233–1233 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.216` | 1234–1234 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.217` | 1235–1235 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.218` | 1236–1236 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.219` | 1237–1237 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.220` | 1238–1238 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.221` | 1239–1239 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.222` | 1240–1240 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.223` | 1241–1241 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.224` | 1242–1242 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.225` | 1243–1243 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.226` | 1244–1244 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-005.227` | 1245–1245 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-005.228` | 1246–1246 | بلوک | `ALTER TABLE ticket_messages` | 0 | ⬜ |
| `DB-005.229` | 1247–1247 | بلوک | `ALTER TABLE ticket_messages` | 0 | ⬜ |
| `DB-005.230` | 1248–1248 | بلوک | `ALTER TABLE ticket_messages` | 0 | ⬜ |
| `DB-005.231` | 1249–1249 | بلوک | `ALTER TABLE ticket_messages` | 0 | ⬜ |
| `DB-005.232` | 1250–1250 | بلوک | `ALTER TABLE ticket_messages` | 0 | ⬜ |
| `DB-005.233` | 1251–1251 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-005.234` | 1252–1252 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-005.235` | 1253–1253 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-005.236` | 1254–1254 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-005.237` | 1255–1255 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-005.238` | 1256–1256 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-005.239` | 1257–1257 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-005.240` | 1258–1258 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-005.241` | 1259–1259 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-005.242` | 1260–1260 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-005.243` | 1261–1261 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-005.244` | 1262–1262 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-005.245` | 1263–1263 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.246` | 1264–1264 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.247` | 1265–1265 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.248` | 1266–1266 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.249` | 1267–1267 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.250` | 1268–1268 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.251` | 1269–1269 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.252` | 1270–1270 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.253` | 1271–1271 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.254` | 1272–1272 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.255` | 1273–1273 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.256` | 1274–1274 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.257` | 1275–1275 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.258` | 1276–1276 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.259` | 1277–1277 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.260` | 1278–1278 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-005.261` | 1279–1279 | بلوک | `ALTER TABLE role_permissions` | 0 | ⬜ |
| `DB-005.262` | 1280–1280 | بلوک | `ALTER TABLE role_permissions` | 0 | ⬜ |
| `DB-005.263` | 1281–1281 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-005.264` | 1282–1282 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-005.265` | 1283–1283 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-005.266` | 1284–1284 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-005.267` | 1285–1285 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-005.268` | 1286–1286 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-005.269` | 1287–1287 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-005.270` | 1288–1288 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-005.271` | 1289–1289 | بلوک | `ALTER TABLE login_attempts` | 0 | ⬜ |
| `DB-005.272` | 1290–1290 | بلوک | `ALTER TABLE login_attempts` | 0 | ⬜ |
| `DB-005.273` | 1291–1291 | بلوک | `ALTER TABLE login_attempts` | 0 | ⬜ |
| `DB-005.274` | 1292–1292 | بلوک | `ALTER TABLE login_attempts` | 1 | ⬜ |
| `DB-005.275` | 1293–1293 | بلوک | `ALTER TABLE ticket_sla_alerts` | 0 | ⬜ |
| `DB-005.276` | 1294–1294 | بلوک | `ALTER TABLE ticket_sla_alerts` | 0 | ⬜ |
| `DB-005.277` | 1295–1295 | بلوک | `ALTER TABLE ticket_sla_alerts` | 0 | ⬜ |
| `DB-005.278` | 1296–1296 | بلوک | `ALTER TABLE ticket_sla_alerts` | 0 | ⬜ |
| `DB-005.279` | 1297–1297 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-005.280` | 1298–1298 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-005.281` | 1299–1299 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-005.282` | 1300–1300 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-005.283` | 1301–1301 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-005.284` | 1302–1302 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-005.285` | 1303–1303 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-005.286` | 1304–1304 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-005.287` | 1305–1305 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-005.288` | 1306–1306 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-005.289` | 1307–1307 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-005.290` | 1308–1308 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-005.291` | 1309–1309 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-005.292` | 1310–1310 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-005.293` | 1311–1311 | بلوک | `ALTER TABLE knowledge_articles` | 1 | ⬜ |
| `DB-005.294` | 1312–1312 | بلوک | `ALTER TABLE holidays` | 0 | ⬜ |
| `DB-005.295` | 1313–1313 | بلوک | `ALTER TABLE holidays` | 0 | ⬜ |
| `DB-005.296` | 1314–1314 | بلوک | `ALTER TABLE holidays` | 0 | ⬜ |
| `DB-005.297` | 1315–1315 | بلوک | `ALTER TABLE holidays` | 0 | ⬜ |
| `DB-005.298` | 1316–1316 | بلوک | `ALTER TABLE holidays` | 0 | ⬜ |
| `DB-005.299` | 1317–1317 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.300` | 1318–1318 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.301` | 1319–1319 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.302` | 1320–1320 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.303` | 1321–1321 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.304` | 1322–1322 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.305` | 1323–1323 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.306` | 1324–1324 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.307` | 1325–1325 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.308` | 1326–1326 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.309` | 1327–1327 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.310` | 1328–1328 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.311` | 1329–1329 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.312` | 1330–1330 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.313` | 1331–1331 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.314` | 1332–1332 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.315` | 1333–1333 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.316` | 1334–1334 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.317` | 1335–1335 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.318` | 1336–1336 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.319` | 1337–1337 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.320` | 1338–1338 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.321` | 1339–1339 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.322` | 1340–1340 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.323` | 1341–1341 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.324` | 1342–1342 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.325` | 1343–1343 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.326` | 1344–1344 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.327` | 1345–1345 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.328` | 1346–1346 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.329` | 1347–1347 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.330` | 1348–1348 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.331` | 1349–1349 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.332` | 1350–1350 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.333` | 1351–1351 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.334` | 1352–1352 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.335` | 1353–1353 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.336` | 1354–1354 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.337` | 1355–1355 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.338` | 1356–1356 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.339` | 1357–1357 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.340` | 1358–1358 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.341` | 1359–1359 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-005.342` | 1360–1360 | بلوک | `ALTER TABLE assets` | 1 | ⬜ |
| `DB-005.343` | 1361–1361 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-005.344` | 1362–1362 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-005.345` | 1363–1363 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-005.346` | 1364–1364 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-005.347` | 1365–1365 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-005.348` | 1366–1366 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-005.349` | 1367–1367 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-005.350` | 1368–1368 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-005.351` | 1369–1369 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-005.352` | 1370–1370 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-005.353` | 1371–1371 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-005.354` | 1372–1372 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-005.355` | 1373–1373 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-005.356` | 1374–1374 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-005.357` | 1375–1375 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-005.358` | 1376–1376 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-005.359` | 1377–1377 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-005.360` | 1378–1378 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-005.361` | 1379–1379 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-005.362` | 1380–1380 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-005.363` | 1381–1381 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-005.364` | 1382–1382 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-005.365` | 1383–1383 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-005.366` | 1384–1384 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-005.367` | 1385–1385 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-005.368` | 1386–1386 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-005.369` | 1387–1387 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.370` | 1388–1388 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.371` | 1389–1389 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.372` | 1390–1390 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.373` | 1391–1391 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.374` | 1392–1392 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.375` | 1393–1393 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.376` | 1394–1394 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.377` | 1395–1395 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.378` | 1396–1396 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.379` | 1397–1397 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.380` | 1398–1398 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-005.381` | 1399–1399 | بلوک | `ALTER TABLE asset_inventory_history` | 0 | ⬜ |
| `DB-005.382` | 1400–1400 | بلوک | `ALTER TABLE asset_inventory_history` | 0 | ⬜ |
| `DB-005.383` | 1401–1401 | بلوک | `ALTER TABLE asset_inventory_history` | 0 | ⬜ |
| `DB-005.384` | 1402–1402 | بلوک | `ALTER TABLE asset_inventory_history` | 0 | ⬜ |
| `DB-005.385` | 1403–1403 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-005.386` | 1404–1404 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-005.387` | 1405–1405 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-005.388` | 1406–1406 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-005.389` | 1407–1407 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-005.390` | 1408–1408 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-005.391` | 1409–1409 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-005.392` | 1410–1410 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-005.393` | 1411–1411 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-005.394` | 1412–1412 | بلوک | `ALTER TABLE asset_relations` | 0 | ⬜ |
| `DB-005.395` | 1413–1413 | بلوک | `ALTER TABLE asset_relations` | 0 | ⬜ |
| `DB-005.396` | 1414–1414 | بلوک | `ALTER TABLE asset_relations` | 0 | ⬜ |
| `DB-005.397` | 1415–1415 | بلوک | `ALTER TABLE asset_relations` | 0 | ⬜ |
| `DB-005.398` | 1416–1416 | بلوک | `ALTER TABLE asset_relations` | 0 | ⬜ |
| `DB-005.399` | 1417–1417 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.400` | 1418–1418 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.401` | 1419–1419 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.402` | 1420–1420 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.403` | 1421–1421 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.404` | 1422–1422 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.405` | 1423–1423 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.406` | 1424–1424 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.407` | 1425–1425 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.408` | 1426–1426 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.409` | 1427–1427 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.410` | 1428–1428 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.411` | 1429–1429 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.412` | 1430–1430 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.413` | 1431–1431 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.414` | 1432–1432 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.415` | 1433–1433 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.416` | 1434–1434 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.417` | 1435–1435 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.418` | 1436–1436 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.419` | 1437–1437 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.420` | 1438–1438 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.421` | 1439–1439 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.422` | 1440–1440 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.423` | 1441–1441 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.424` | 1442–1442 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.425` | 1443–1443 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.426` | 1444–1444 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.427` | 1445–1445 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.428` | 1446–1446 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.429` | 1447–1447 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.430` | 1448–1448 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.431` | 1449–1449 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.432` | 1450–1450 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.433` | 1451–1451 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.434` | 1452–1452 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.435` | 1453–1453 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.436` | 1454–1454 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.437` | 1455–1455 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.438` | 1456–1456 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.439` | 1457–1457 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.440` | 1458–1458 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.441` | 1459–1459 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.442` | 1460–1460 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.443` | 1461–1461 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.444` | 1462–1462 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.445` | 1463–1463 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.446` | 1464–1464 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.447` | 1465–1465 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.448` | 1466–1466 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.449` | 1467–1467 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.450` | 1468–1468 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.451` | 1469–1469 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.452` | 1470–1470 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.453` | 1471–1471 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.454` | 1472–1472 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.455` | 1473–1473 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.456` | 1474–1474 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.457` | 1475–1475 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.458` | 1476–1476 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.459` | 1477–1477 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.460` | 1478–1478 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.461` | 1479–1479 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.462` | 1480–1480 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.463` | 1481–1481 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.464` | 1482–1482 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.465` | 1483–1483 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.466` | 1484–1484 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.467` | 1485–1485 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.468` | 1486–1486 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.469` | 1487–1487 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.470` | 1488–1488 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.471` | 1489–1489 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.472` | 1490–1490 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-005.473` | 1491–1491 | بلوک | `ALTER TABLE asset_profiles` | 1 | ⬜ |
| `DB-005.474` | 1492–1492 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-005.475` | 1493–1493 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-005.476` | 1494–1494 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-005.477` | 1495–1495 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-005.478` | 1496–1496 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-005.479` | 1497–1497 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-005.480` | 1498–1498 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-005.481` | 1499–1499 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-005.482` | 1500–1500 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-005.483` | 1501–1501 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-005.484` | 1502–1502 | بلوک | `ALTER TABLE ola_policies` | 1 | ⬜ |
| `DB-005.485` | 1503–1503 | بلوک | `ALTER TABLE ticket_ola_alerts` | 0 | ⬜ |
| `DB-005.486` | 1504–1504 | بلوک | `ALTER TABLE ticket_ola_alerts` | 0 | ⬜ |
| `DB-005.487` | 1505–1505 | بلوک | `ALTER TABLE ticket_ola_alerts` | 0 | ⬜ |
| `DB-005.488` | 1506–1506 | بلوک | `ALTER TABLE ticket_ola_alerts` | 0 | ⬜ |
| `DB-005.489` | 1507–1507 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.490` | 1508–1508 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.491` | 1509–1509 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.492` | 1510–1510 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.493` | 1511–1511 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.494` | 1512–1512 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.495` | 1513–1513 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.496` | 1514–1514 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.497` | 1515–1515 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.498` | 1516–1516 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.499` | 1517–1517 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.500` | 1518–1518 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.501` | 1519–1519 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.502` | 1520–1520 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.503` | 1521–1521 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.504` | 1522–1522 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.505` | 1523–1523 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-005.506` | 1524–1524 | بلوک | `ALTER TABLE change_records` | 1 | ⬜ |
| `DB-005.507` | 1525–1525 | بلوک | `ALTER TABLE change_approvals` | 0 | ⬜ |
| `DB-005.508` | 1526–1526 | بلوک | `ALTER TABLE change_approvals` | 0 | ⬜ |
| `DB-005.509` | 1527–1527 | بلوک | `ALTER TABLE change_approvals` | 0 | ⬜ |
| `DB-005.510` | 1528–1528 | بلوک | `ALTER TABLE change_approvals` | 0 | ⬜ |
| `DB-005.511` | 1529–1529 | بلوک | `ALTER TABLE change_approvals` | 0 | ⬜ |
| `DB-005.512` | 1530–1530 | بلوک | `ALTER TABLE change_ticket_links` | 0 | ⬜ |
| `DB-005.513` | 1531–1531 | بلوک | `ALTER TABLE change_ticket_links` | 0 | ⬜ |
| `DB-005.514` | 1532–1532 | بلوک | `ALTER TABLE change_ticket_links` | 0 | ⬜ |
| `DB-005.515` | 1533–1533 | بلوک | `ALTER TABLE change_ticket_links` | 0 | ⬜ |
| `DB-005.516` | 1534–1534 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.517` | 1535–1535 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.518` | 1536–1536 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.519` | 1537–1537 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.520` | 1538–1538 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.521` | 1539–1539 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.522` | 1540–1540 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.523` | 1541–1541 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.524` | 1542–1542 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.525` | 1543–1543 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.526` | 1544–1544 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.527` | 1545–1545 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.528` | 1546–1546 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-005.529` | 1547–1547 | بلوک | `ALTER TABLE problem_records` | 1 | ⬜ |
| `DB-005.530` | 1548–1548 | بلوک | `ALTER TABLE problem_ticket_links` | 0 | ⬜ |
| `DB-005.531` | 1549–1549 | بلوک | `ALTER TABLE problem_ticket_links` | 0 | ⬜ |
| `DB-005.532` | 1550–1550 | بلوک | `ALTER TABLE problem_ticket_links` | 0 | ⬜ |
| `DB-005.533` | 1551–1551 | بلوک | `ALTER TABLE problem_ticket_links` | 0 | ⬜ |
| `DB-005.534` | 1552–1552 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.535` | 1553–1553 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.536` | 1554–1554 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.537` | 1555–1555 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.538` | 1556–1556 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.539` | 1557–1557 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.540` | 1558–1558 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.541` | 1559–1559 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.542` | 1560–1560 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.543` | 1561–1561 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.544` | 1562–1562 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.545` | 1563–1563 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.546` | 1564–1564 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-005.547` | 1565–1565 | بلوک | `ALTER TABLE food_ticket_config` | 1 | ⬜ |
| `DB-005.548` | 1566–1566 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.549` | 1567–1567 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.550` | 1568–1568 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.551` | 1569–1569 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.552` | 1570–1570 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.553` | 1571–1571 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.554` | 1572–1572 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.555` | 1573–1573 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.556` | 1574–1574 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.557` | 1575–1575 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.558` | 1576–1576 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.559` | 1577–1577 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.560` | 1578–1578 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.561` | 1579–1579 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.562` | 1580–1580 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.563` | 1581–1581 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.564` | 1582–1582 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.565` | 1583–1583 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.566` | 1584–1584 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.567` | 1585–1585 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.568` | 1586–1586 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.569` | 1587–1587 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.570` | 1588–1588 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.571` | 1589–1589 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.572` | 1590–1590 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.573` | 1591–1591 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.574` | 1592–1592 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.575` | 1593–1593 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-005.576` | 1594–1594 | بلوک | `ALTER TABLE food_ticket_events` | 1 | ⬜ |
| `DB-005.577` | 1595–1595 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-005.578` | 1596–1596 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-005.579` | 1597–1597 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-005.580` | 1598–1598 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-005.581` | 1599–1599 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-005.582` | 1600–1600 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-005.583` | 1601–1601 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-005.584` | 1602–1602 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-005.585` | 1603–1603 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-005.586` | 1604–1604 | بلوک | `ALTER TABLE food_ticket_worker_status` | 1 | ⬜ |
| `DB-005.587` | 1605–1605 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-005.588` | 1606–1606 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-005.589` | 1607–1607 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-005.590` | 1608–1608 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-005.591` | 1609–1609 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-005.592` | 1610–1610 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-005.593` | 1611–1611 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 1 | ⬜ |
| `DB-005.594` | 1612–1612 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-005.595` | 1613–1613 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-005.596` | 1614–1614 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-005.597` | 1615–1615 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-005.598` | 1616–1616 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-005.599` | 1617–1617 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-005.600` | 1618–1618 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-005.601` | 1619–1619 | بلوک | `ALTER TABLE food_ticket_groups` | 1 | ⬜ |
| `DB-005.602` | 1620–1620 | بلوک | `ALTER TABLE food_ticket_group_members` | 0 | ⬜ |
| `DB-005.603` | 1621–1621 | بلوک | `ALTER TABLE food_ticket_group_members` | 0 | ⬜ |
| `DB-005.604` | 1622–1622 | بلوک | `ALTER TABLE food_ticket_group_members` | 0 | ⬜ |
| `DB-005.605` | 1623–1623 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.606` | 1624–1624 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.607` | 1625–1625 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.608` | 1626–1626 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.609` | 1627–1627 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.610` | 1628–1628 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.611` | 1629–1629 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.612` | 1630–1630 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.613` | 1631–1631 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.614` | 1632–1632 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.615` | 1633–1633 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.616` | 1634–1634 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.617` | 1635–1635 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.618` | 1636–1636 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.619` | 1637–1637 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.620` | 1638–1638 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.621` | 1639–1639 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.622` | 1640–1640 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-005.623` | 1641–1641 | بلوک | `ALTER TABLE food_ticket_group_runs` | 1 | ⬜ |
| `DB-005.624` | 1642–1642 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-005.625` | 1643–1643 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-005.626` | 1644–1644 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-005.627` | 1645–1645 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-005.628` | 1646–1646 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-005.629` | 1647–1647 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-005.630` | 1648–1648 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-005.631` | 1649–1649 | بلوک | `ALTER TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-005.632` | 1650–1650 | بلوک | `ALTER TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-005.633` | 1651–1651 | بلوک | `ALTER TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-005.634` | 1652–1652 | بلوک | `ALTER TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-005.635` | 1653–1653 | بلوک | `ALTER TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-005.636` | 1654–1654 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-005.637` | 1655–1655 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-005.638` | 1656–1656 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-005.639` | 1657–1657 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-005.640` | 1658–1658 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-005.641` | 1659–1659 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-005.642` | 1660–1660 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-005.643` | 1661–1661 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-005.644` | 1662–1662 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-005.645` | 1663–1663 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-005.646` | 1664–1664 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-005.647` | 1665–1665 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-005.648` | 1666–1666 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-005.649` | 1667–1667 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-005.650` | 1668–1668 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-005.651` | 1669–1669 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-005.652` | 1670–1670 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-005.653` | 1671–1671 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-005.654` | 1672–1672 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-005.655` | 1673–1673 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-005.656` | 1674–1674 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-005.657` | 1675–1675 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-005.658` | 1676–1676 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-005.659` | 1677–1677 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-005.660` | 1678–1678 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-005.661` | 1679–1679 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-005.662` | 1680–1680 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-005.663` | 1681–1681 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-005.664` | 1682–1682 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-005.665` | 1683–1683 | بلوک | `ALTER TABLE system_incidents` | 0 | ⬜ |
| `DB-005.666` | 1684–1684 | بلوک | `ALTER TABLE system_incidents` | 0 | ⬜ |
| `DB-005.667` | 1685–1685 | بلوک | `ALTER TABLE system_incidents` | 0 | ⬜ |
| `DB-005.668` | 1686–1686 | بلوک | `ALTER TABLE system_incidents` | 0 | ⬜ |
| `DB-005.669` | 1687–1687 | بلوک | `ALTER TABLE system_incidents` | 1 | ⬜ |
| `DB-005.670` | 1688–1688 | بلوک | `ALTER TABLE ticket_number_seq` | 0 | ⬜ |
| `DB-005.671` | 1689–1689 | بلوک | `ALTER TABLE api_rate_limits` | 0 | ⬜ |
| `DB-005.672` | 1690–1690 | بلوک | `ALTER TABLE api_rate_limits` | 0 | ⬜ |
| `DB-005.673` | 1691–1691 | بلوک | `ALTER TABLE api_rate_limits` | 1 | ⬜ |
| `DB-005.674` | 1692–1692 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-005.675` | 1693–1693 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-005.676` | 1694–1694 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-005.677` | 1695–1695 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-005.678` | 1696–1696 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-005.679` | 1697–1697 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-005.680` | 1698–1698 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-005.681` | 1699–1699 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-005.682` | 1700–1700 | بلوک | `ALTER TABLE domain_scan_runs` | 1 | ⬜ |
| `DB-005.683` | 1701–1701 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-005.684` | 1702–1702 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-005.685` | 1703–1703 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-005.686` | 1704–1704 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-005.687` | 1705–1705 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-005.688` | 1706–1706 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-005.689` | 1707–1707 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-005.690` | 1708–1708 | بلوک | `ALTER TABLE domain_scan_queue` | 1 | ⬜ |
| `DB-005.691` | 1709–1709 | بلوک | `ALTER TABLE food_ticket_card_map` | 0 | ⬜ |
| `DB-005.692` | 1710–1710 | بلوک | `ALTER TABLE food_ticket_card_map` | 0 | ⬜ |
| `DB-005.693` | 1711–1711 | بلوک | `ALTER TABLE food_ticket_card_map` | 0 | ⬜ |
| `DB-005.694` | 1712–1712 | بلوک | `ALTER TABLE food_ticket_card_map` | 0 | ⬜ |
| `DB-005.695` | 1713–1713 | بلوک | `ALTER TABLE food_ticket_card_map` | 1 | ⬜ |
| `DB-005.696` | 1714–1714 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-005.697` | 1715–1715 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-005.698` | 1716–1716 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-005.699` | 1717–1717 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-005.700` | 1718–1718 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-005.701` | 1719–1719 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-005.702` | 1720–1720 | بلوک | `ALTER TABLE food_ticket_templates` | 1 | ⬜ |
| `DB-005.703` | 1721–1721 | بلوک | `ALTER TABLE food_catalog` | 0 | ⬜ |
| `DB-005.704` | 1722–1722 | بلوک | `ALTER TABLE food_catalog` | 0 | ⬜ |
| `DB-005.705` | 1723–1723 | بلوک | `ALTER TABLE food_catalog` | 0 | ⬜ |
| `DB-005.706` | 1724–1724 | بلوک | `ALTER TABLE food_catalog` | 0 | ⬜ |
| `DB-005.707` | 1725–1725 | بلوک | `ALTER TABLE food_catalog` | 0 | ⬜ |
| `DB-005.708` | 1726–1726 | بلوک | `ALTER TABLE food_catalog` | 1 | ⬜ |
| `DB-005.709` | 1727–1727 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.710` | 1728–1728 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.711` | 1729–1729 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.712` | 1730–1730 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.713` | 1731–1731 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.714` | 1732–1732 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.715` | 1733–1733 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.716` | 1734–1734 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.717` | 1735–1735 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.718` | 1736–1736 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.719` | 1737–1737 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.720` | 1738–1738 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-005.721` | 1739–1739 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-005.722` | 1740–1740 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-005.723` | 1741–1741 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-005.724` | 1742–1742 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-005.725` | 1743–1743 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-005.726` | 1744–1744 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-005.727` | 1745–1745 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-005.728` | 1746–1746 | بلوک | `ALTER TABLE food_calendar` | 1 | ⬜ |
| `DB-005.729` | 1747–1747 | بلوک | `ALTER TABLE food_calendar_items` | 0 | ⬜ |
| `DB-005.730` | 1748–1748 | بلوک | `ALTER TABLE food_calendar_items` | 0 | ⬜ |
| `DB-005.731` | 1749–1749 | بلوک | `ALTER TABLE food_calendar_items` | 0 | ⬜ |
| `DB-005.732` | 1750–1750 | بلوک | `ALTER TABLE food_calendar_items` | 0 | ⬜ |
| `DB-005.733` | 1751–1751 | بلوک | `ALTER TABLE food_calendar_items` | 0 | ⬜ |
| `DB-005.734` | 1752–1752 | بلوک | `ALTER TABLE food_calendar_items` | 1 | ⬜ |
| `DB-005.735` | 1753–1753 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-005.736` | 1754–1754 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-005.737` | 1755–1755 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-005.738` | 1756–1756 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-005.739` | 1757–1757 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-005.740` | 1758–1758 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-005.741` | 1759–1759 | بلوک | `ALTER TABLE food_orders` | 1 | ⬜ |
| `DB-005.742` | 1760–1760 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-005.743` | 1761–1761 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-005.744` | 1762–1762 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-005.745` | 1763–1763 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-005.746` | 1764–1764 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-005.747` | 1765–1765 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-005.748` | 1766–1766 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-005.749` | 1767–1769 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-005.750` | 1770–1771 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-006</b> — <code>migrations/004-ticket-cancellation.sql</code> (47 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-006.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-006.02` | 3–45 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-006.03` | 46–47 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-007</b> — <code>migrations/005-normalize-pending-status.sql</code> (9 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-007.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-007.02` | 6–9 | بلوک | `UPDATE tickets` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-008</b> — <code>migrations/006-food-meal-type.sql</code> (55 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-008.01` | 1–55 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-009</b> — <code>migrations/007-traffic-visit-history.sql</code> (18 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-009.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-009.02` | 2–18 | بلوک | `CREATE TABLE traffic_visit_history` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-010</b> — <code>migrations/008-traffic-destination-approver.sql</code> (3 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-010.01` | 1–3 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-011</b> — <code>migrations/009-traffic-destination-multi-approver.sql</code> (6 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-011.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-011.02` | 2–3 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |
| `DB-011.03` | 4–6 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-012</b> — <code>migrations/010-org-liaison-units.sql</code> (6 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-012.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-012.02` | 4–6 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-013</b> — <code>migrations/011-manual-attendance-delivery.sql</code> (11 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-013.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-013.02` | 3–11 | بلوک | `UPDATE food_ticket_events` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-014</b> — <code>migrations/012-manual-attendance-clears-absences.sql</code> (25 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-014.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-014.02` | 4–25 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-015</b> — <code>migrations/013-manual-attendance-record-backfill.sql</code> (60 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-015.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-015.02` | 5–6 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-015.03` | 7–8 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-015.04` | 9–11 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-015.05` | 12–24 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-015.06` | 25–60 | بلوک | `UPDATE food_ticket_events` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-016</b> — <code>migrations/014-asset-identity-dedupe.sql</code> (54 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-016.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-016.02` | 6–30 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-016.03` | 31–34 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.04` | 35–35 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.05` | 36–36 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.06` | 37–37 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.07` | 38–39 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.08` | 40–41 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.09` | 42–44 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.10` | 45–45 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.11` | 46–46 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.12` | 47–47 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.13` | 48–48 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.14` | 49–49 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.15` | 50–51 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.16` | 52–52 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-016.17` | 53–54 | بلوک | `UPDATE assets` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-017</b> — <code>migrations/015-asset-identity-hotfix.sql</code> (38 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-017.01` | 1–25 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-017.02` | 26–30 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-017.03` | 31–34 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-017.04` | 35–35 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-017.05` | 36–36 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-017.06` | 37–38 | بلوک | `UPDATE assets` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-018</b> — <code>migrations/016-auto-asset-finish.sql</code> (141 خط، 25 آدرس، بازبینی‌شده 0/25)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-018.01` | 1–19 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-018.02` | 20–21 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.03` | 22–22 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.04` | 23–23 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.05` | 24–24 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.06` | 25–25 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.07` | 26–27 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.08` | 28–28 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.09` | 29–29 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.10` | 30–30 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.11` | 31–31 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.12` | 32–32 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.13` | 33–33 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.14` | 34–34 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.15` | 35–37 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.16` | 38–38 | بلوک | `DROP TABLE IF EXISTS tmp_asset_dedupe_map` | 0 | ⬜ |
| `DB-018.17` | 39–44 | بلوک | `CREATE TABLE tmp_asset_dedupe_map` | 0 | ⬜ |
| `DB-018.18` | 45–51 | بلوک | `INSERT INTO tmp_asset_dedupe_map` | 1 | ⬜ |
| `DB-018.19` | 52–52 | بلوک | `DROP TABLE IF EXISTS asset_dedupe_backup_016` | 0 | ⬜ |
| `DB-018.20` | 53–54 | بلوک | `CREATE TABLE asset_dedupe_backup_016` | 0 | ⬜ |
| `DB-018.21` | 55–56 | بلوک | `ALTER TABLE asset_dedupe_backup_016` | 0 | ⬜ |
| `DB-018.22` | 57–61 | بلوک | `UPDATE asset_dedupe_backup_016` | 1 | ⬜ |
| `DB-018.23` | 62–87 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.24` | 88–138 | بلوک | `CREATE PROCEDURE sp_asset_dedupe_relink` | 1 | ⬜ |
| `DB-018.25` | 139–141 | بلوک | `DROP TABLE IF EXISTS tmp_asset_dedupe_map` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-019</b> — <code>migrations/017-org-tree-order-rollback.sql</code> (5 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-019.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-019.02` | 3–5 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-020</b> — <code>migrations/017-org-tree-order.sql</code> (5 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-020.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-020.02` | 3–5 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-021</b> — <code>migrations/018-org-company-separation.sql</code> (34 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-021.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `DB-021.02` | 3–16 | بلوک | `CREATE TABLE org_companies` | 1 | ⬜ |
| `DB-021.03` | 17–17 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-021.04` | 18–18 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-021.05` | 19–19 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-021.06` | 20–22 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-021.07` | 23–23 | بلوک | `UPDATE org_units` | 1 | ⬜ |
| `DB-021.08` | 24–26 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-021.09` | 27–30 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-021.10` | 31–34 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-022</b> — <code>migrations/019-traffic-destination-link.sql</code> (12 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-022.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-022.02` | 10–12 | بلوک | `UPDATE traffic_visits` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-023</b> — <code>migrations/020-traffic-destination-upgrade.sql</code> (12 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-023.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-024</b> — <code>migrations/021-food-auto-receipts.sql</code> (51 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-024.01` | 1–51 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-025</b> — <code>migrations/022-food-meal-reconciliation-invoices.sql</code> (51 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-025.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-025.02` | 4–30 | بلوک | `CREATE TABLE food_meal_reconciliations` | 1 | ⬜ |
| `DB-025.03` | 31–51 | بلوک | `CREATE TABLE food_monthly_invoices` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-026</b> — <code>migrations/023-user-access-groups.sql</code> (39 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-026.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-026.02` | 3–16 | بلوک | `CREATE TABLE user_access_groups` | 1 | ⬜ |
| `DB-026.03` | 17–28 | بلوک | `CREATE TABLE user_access_group_members` | 1 | ⬜ |
| `DB-026.04` | 29–39 | بلوک | `CREATE TABLE user_access_group_permissions` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-027</b> — <code>migrations/024-central-logging.sql</code> (83 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-027.01` | 1–83 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-028</b> — <code>migrations/025-logging-issues-alerts.sql</code> (51 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-028.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-028.02` | 4–28 | بلوک | `CREATE TABLE log_issues` | 1 | ⬜ |
| `DB-028.03` | 29–51 | بلوک | `CREATE TABLE logging_health_alerts` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-029</b> — <code>migrations/026-logging-legacy-result-unknown.sql</code> (72 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-029.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-029.02` | 3–14 | بلوک | `UPDATE activity_logs` | 1 | ⬜ |
| `DB-029.03` | 15–19 | بلوک | `UPDATE log_issues` | 1 | ⬜ |
| `DB-029.04` | 20–37 | بلوک | `CREATE TABLE log_issue_history` | 0 | ⬜ |
| `DB-029.05` | 38–55 | بلوک | `CREATE TABLE log_issue_events` | 1 | ⬜ |
| `DB-029.06` | 56–72 | بلوک | `CREATE TABLE log_issue_notifications` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-030</b> — <code>migrations/027-logging-correlation-ids.sql</code> (59 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-030.01` | 1–55 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-030.02` | 56–56 | بلوک | `UPDATE system_logs` | 1 | ⬜ |
| `DB-030.03` | 57–57 | بلوک | `UPDATE activity_logs` | 1 | ⬜ |
| `DB-030.04` | 58–59 | بلوک | `UPDATE ticket_events` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-031</b> — <code>migrations/028-logging-fingerprint-v2.sql</code> (15 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-031.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-032</b> — <code>migrations/029-activity-result-default-unknown.sql</code> (16 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-032.01` | 1–16 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-033</b> — <code>migrations/030-logging-notification-idempotency-and-ticket-history.sql</code> (48 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-033.01` | 1–48 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-034</b> — <code>migrations/031-food-worker-window.sql</code> (100 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-034.01` | 1–100 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-035</b> — <code>migrations/032-schema-updates-baseline.sql</code> (36 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-035.01` | 1–36 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-036</b> — <code>migrations/033-logging-legacy-context-collation.sql</code> (9 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-036.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-036.02` | 5–9 | بلوک | `UPDATE log_issues` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-037</b> — <code>migrations/034-logging-escalation.sql</code> (34 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-037.01` | 1–34 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-038</b> — <code>rebuild-final.sql</code> (97 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-038.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-038.02` | 10–97 | بلوک | `DROP TABLE IF` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-039</b> — <code>rollback-1.38-food-orders.sql</code> (10 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-039.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-039.02` | 5–5 | بلوک | `DROP TABLE IF EXISTS food_order_logs` | 0 | ⬜ |
| `DB-039.03` | 6–6 | بلوک | `DROP TABLE IF EXISTS food_orders` | 0 | ⬜ |
| `DB-039.04` | 7–7 | بلوک | `DROP TABLE IF EXISTS food_calendar_items` | 0 | ⬜ |
| `DB-039.05` | 8–8 | بلوک | `DROP TABLE IF EXISTS food_calendar` | 0 | ⬜ |
| `DB-039.06` | 9–10 | بلوک | `DROP TABLE IF EXISTS food_catalog` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-040</b> — <code>schema.sql</code> (1,564 خط، 110 آدرس، بازبینی‌شده 0/110)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-040.01` | 1–5 | بلوک | `CREATE TABLE settings` | 0 | ⬜ |
| `DB-040.02` | 6–15 | بلوک | `CREATE TABLE departments` | 0 | ⬜ |
| `DB-040.03` | 16–29 | بلوک | `CREATE TABLE org_companies` | 1 | ⬜ |
| `DB-040.04` | 30–48 | بلوک | `CREATE TABLE org_units` | 0 | ⬜ |
| `DB-040.05` | 49–51 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-040.06` | 52–55 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-040.07` | 56–63 | بلوک | `CREATE TABLE org_unit_managers` | 0 | ⬜ |
| `DB-040.08` | 64–99 | بلوک | `CREATE TABLE users` | 1 | ⬜ |
| `DB-040.09` | 100–101 | بلوک | `ALTER TABLE departments` | 1 | ⬜ |
| `DB-040.10` | 102–112 | بلوک | `CREATE TABLE handling_units` | 1 | ⬜ |
| `DB-040.11` | 113–114 | بلوک | `ALTER TABLE users` | 1 | ⬜ |
| `DB-040.12` | 115–122 | بلوک | `CREATE TABLE cd_dvd_types` | 1 | ⬜ |
| `DB-040.13` | 123–155 | بلوک | `CREATE TABLE cd_dvd_records` | 1 | ⬜ |
| `DB-040.14` | 156–166 | بلوک | `CREATE TABLE traffic_destinations` | 0 | ⬜ |
| `DB-040.15` | 167–195 | بلوک | `CREATE TABLE traffic_visits` | 1 | ⬜ |
| `DB-040.16` | 196–212 | بلوک | `CREATE TABLE traffic_visit_history` | 1 | ⬜ |
| `DB-040.17` | 213–222 | بلوک | `INSERT INTO traffic_destinations` | 1 | ⬜ |
| `DB-040.18` | 223–231 | بلوک | `CREATE TABLE categories` | 0 | ⬜ |
| `DB-040.19` | 232–257 | بلوک | `CREATE TABLE service_catalog` | 1 | ⬜ |
| `DB-040.20` | 258–270 | بلوک | `CREATE TABLE service_catalog_fields` | 1 | ⬜ |
| `DB-040.21` | 271–337 | بلوک | `CREATE TABLE tickets` | 1 | ⬜ |
| `DB-040.22` | 338–349 | بلوک | `CREATE TABLE ticket_messages` | 1 | ⬜ |
| `DB-040.23` | 350–363 | بلوک | `CREATE TABLE ticket_ratings` | 1 | ⬜ |
| `DB-040.24` | 364–374 | بلوک | `CREATE TABLE ticket_attachments` | 1 | ⬜ |
| `DB-040.25` | 375–413 | بلوک | `CREATE TABLE activity_logs` | 1 | ⬜ |
| `DB-040.26` | 414–420 | بلوک | `CREATE TABLE role_permissions` | 0 | ⬜ |
| `DB-040.27` | 421–438 | بلوک | `CREATE TABLE notifications` | 1 | ⬜ |
| `DB-040.28` | 439–446 | بلوک | `CREATE TABLE login_attempts` | 1 | ⬜ |
| `DB-040.29` | 447–456 | بلوک | `CREATE TABLE ticket_sla_alerts` | 1 | ⬜ |
| `DB-040.30` | 457–480 | بلوک | `CREATE TABLE ticket_events` | 1 | ⬜ |
| `DB-040.31` | 481–494 | بلوک | `CREATE TABLE knowledge_articles` | 1 | ⬜ |
| `DB-040.32` | 495–504 | بلوک | `CREATE TABLE holidays` | 1 | ⬜ |
| `DB-040.33` | 505–560 | بلوک | `CREATE TABLE assets` | 1 | ⬜ |
| `DB-040.34` | 561–562 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-040.35` | 563–577 | بلوک | `CREATE TABLE asset_memory_modules` | 1 | ⬜ |
| `DB-040.36` | 578–593 | بلوک | `CREATE TABLE asset_storage_devices` | 1 | ⬜ |
| `DB-040.37` | 594–606 | بلوک | `CREATE TABLE asset_graphics_adapters` | 1 | ⬜ |
| `DB-040.38` | 607–624 | بلوک | `CREATE TABLE asset_peripherals` | 1 | ⬜ |
| `DB-040.39` | 625–635 | بلوک | `CREATE TABLE asset_inventory_history` | 1 | ⬜ |
| `DB-040.40` | 636–653 | بلوک | `CREATE TABLE asset_history_events` | 1 | ⬜ |
| `DB-040.41` | 654–666 | بلوک | `CREATE TABLE asset_relations` | 1 | ⬜ |
| `DB-040.42` | 667–748 | بلوک | `CREATE TABLE asset_profiles` | 1 | ⬜ |
| `DB-040.43` | 749–766 | بلوک | `CREATE TABLE ola_policies` | 1 | ⬜ |
| `DB-040.44` | 767–776 | بلوک | `CREATE TABLE ticket_ola_alerts` | 1 | ⬜ |
| `DB-040.45` | 777–778 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-040.46` | 779–807 | بلوک | `CREATE TABLE change_records` | 1 | ⬜ |
| `DB-040.47` | 808–819 | بلوک | `CREATE TABLE change_approvals` | 1 | ⬜ |
| `DB-040.48` | 820–830 | بلوک | `CREATE TABLE change_ticket_links` | 1 | ⬜ |
| `DB-040.49` | 831–854 | بلوک | `CREATE TABLE problem_records` | 1 | ⬜ |
| `DB-040.50` | 855–865 | بلوک | `CREATE TABLE problem_ticket_links` | 1 | ⬜ |
| `DB-040.51` | 866–896 | بلوک | `CREATE TABLE food_ticket_config` | 1 | ⬜ |
| `DB-040.52` | 897–935 | بلوک | `CREATE TABLE food_ticket_events` | 1 | ⬜ |
| `DB-040.53` | 936–951 | بلوک | `CREATE TABLE food_ticket_worker_status` | 1 | ⬜ |
| `DB-040.54` | 952–965 | بلوک | `CREATE TABLE food_ticket_guest_cards` | 1 | ⬜ |
| `DB-040.55` | 966–984 | بلوک | `CREATE TABLE food_ticket_groups` | 1 | ⬜ |
| `DB-040.56` | 985–996 | بلوک | `CREATE TABLE food_ticket_group_members` | 1 | ⬜ |
| `DB-040.57` | 997–1023 | بلوک | `CREATE TABLE food_ticket_group_runs` | 1 | ⬜ |
| `DB-040.58` | 1024–1039 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-040.59` | 1040–1052 | بلوک | `CREATE TABLE food_ticket_day_visits` | 1 | ⬜ |
| `DB-040.60` | 1053–1063 | بلوک | `CREATE TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-040.61` | 1064–1079 | بلوک | `CREATE TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-040.62` | 1080–1106 | بلوک | `CREATE TABLE system_logs` | 0 | ⬜ |
| `DB-040.63` | 1107–1118 | بلوک | `CREATE TABLE chatbot_faq` | 0 | ⬜ |
| `DB-040.64` | 1119–1131 | بلوک | `CREATE TABLE chatbot_logs` | 0 | ⬜ |
| `DB-040.65` | 1132–1140 | بلوک | `CREATE TABLE system_incidents` | 1 | ⬜ |
| `DB-040.66` | 1141–1146 | بلوک | `CREATE TABLE ticket_number_seq` | 0 | ⬜ |
| `DB-040.67` | 1147–1155 | بلوک | `CREATE TABLE api_rate_limits` | 1 | ⬜ |
| `DB-040.68` | 1156–1168 | بلوک | `CREATE TABLE domain_scan_runs` | 1 | ⬜ |
| `DB-040.69` | 1169–1182 | بلوک | `CREATE TABLE domain_scan_queue` | 1 | ⬜ |
| `DB-040.70` | 1183–1183 | بلوک | `CREATE INDEX idx_tickets_status_priority` | 0 | ⬜ |
| `DB-040.71` | 1184–1184 | بلوک | `CREATE INDEX idx_users_is_active` | 0 | ⬜ |
| `DB-040.72` | 1185–1185 | بلوک | `CREATE INDEX idx_assets_lifecycle_status` | 0 | ⬜ |
| `DB-040.73` | 1186–1186 | بلوک | `CREATE INDEX idx_ola_priority_active` | 0 | ⬜ |
| `DB-040.74` | 1187–1187 | بلوک | `CREATE INDEX idx_food_ticket_events_event_type` | 0 | ⬜ |
| `DB-040.75` | 1188–1188 | بلوک | `CREATE INDEX idx_login_attempts_locked_until` | 0 | ⬜ |
| `DB-040.76` | 1189–1189 | بلوک | `CREATE INDEX idx_ticket_sla_alerts_alert_type` | 0 | ⬜ |
| `DB-040.77` | 1190–1190 | بلوک | `CREATE INDEX idx_ticket_ola_alerts_alert_type` | 0 | ⬜ |
| `DB-040.78` | 1191–1191 | بلوک | `CREATE INDEX idx_ticket_events_event_type` | 0 | ⬜ |
| `DB-040.79` | 1192–1192 | بلوک | `CREATE INDEX idx_knowledge_articles_is_published` | 0 | ⬜ |
| `DB-040.80` | 1193–1193 | بلوک | `CREATE INDEX idx_holidays_is_active` | 0 | ⬜ |
| `DB-040.81` | 1194–1194 | بلوک | `CREATE INDEX idx_departments_is_active` | 0 | ⬜ |
| `DB-040.82` | 1195–1195 | بلوک | `CREATE INDEX idx_handling_units_is_active` | 0 | ⬜ |
| `DB-040.83` | 1196–1196 | بلوک | `CREATE INDEX idx_categories_group_active` | 0 | ⬜ |
| `DB-040.84` | 1197–1197 | بلوک | `CREATE INDEX idx_service_catalog_group_active` | 0 | ⬜ |
| `DB-040.85` | 1198–1198 | بلوک | `CREATE INDEX idx_cd_dvd_records_media` | 0 | ⬜ |
| `DB-040.86` | 1199–1199 | بلوک | `CREATE INDEX idx_traffic_destinations_is_active` | 0 | ⬜ |
| `DB-040.87` | 1200–1200 | بلوک | `CREATE INDEX idx_domain_scan_runs_status` | 0 | ⬜ |
| `DB-040.88` | 1201–1203 | بلوک | `CREATE INDEX idx_domain_scan_queue_status` | 0 | ⬜ |
| `DB-040.89` | 1204–1213 | بلوک | `CREATE TABLE food_ticket_card_map` | 1 | ⬜ |
| `DB-040.90` | 1214–1225 | بلوک | `CREATE TABLE food_ticket_templates` | 1 | ⬜ |
| `DB-040.91` | 1226–1227 | بلوک | `=` | 0 | ⬜ |
| `DB-040.92` | 1228–1232 | بلوک | `=` | 0 | ⬜ |
| `DB-040.93` | 1233–1244 | بلوک | `CREATE TABLE food_catalog` | 1 | ⬜ |
| `DB-040.94` | 1245–1264 | بلوک | `CREATE TABLE food_guest_requests` | 1 | ⬜ |
| `DB-040.95` | 1265–1279 | بلوک | `CREATE TABLE food_calendar` | 1 | ⬜ |
| `DB-040.96` | 1280–1294 | بلوک | `CREATE TABLE food_calendar_items` | 1 | ⬜ |
| `DB-040.97` | 1295–1310 | بلوک | `CREATE TABLE food_orders` | 1 | ⬜ |
| `DB-040.98` | 1311–1328 | بلوک | `CREATE TABLE food_order_logs` | 1 | ⬜ |
| `DB-040.99` | 1329–1350 | بلوک | `CREATE TABLE food_order_cancel_requests` | 1 | ⬜ |
| `DB-040.100` | 1351–1377 | بلوک | `CREATE TABLE food_meal_reconciliations` | 1 | ⬜ |
| `DB-040.101` | 1378–1399 | بلوک | `CREATE TABLE food_monthly_invoices` | 0 | ⬜ |
| `DB-040.102` | 1400–1413 | بلوک | `CREATE TABLE user_access_groups` | 1 | ⬜ |
| `DB-040.103` | 1414–1425 | بلوک | `CREATE TABLE user_access_group_members` | 1 | ⬜ |
| `DB-040.104` | 1426–1441 | بلوک | `CREATE TABLE user_access_group_permissions` | 1 | ⬜ |
| `DB-040.105` | 1442–1467 | بلوک | `CREATE TABLE log_issues` | 1 | ⬜ |
| `DB-040.106` | 1468–1477 | بلوک | `CREATE TABLE logging_health_alerts` | 1 | ⬜ |
| `DB-040.107` | 1478–1493 | بلوک | `CREATE TABLE log_issue_history` | 0 | ⬜ |
| `DB-040.108` | 1494–1500 | بلوک | `CREATE TABLE log_issue_events` | 0 | ⬜ |
| `DB-040.109` | 1501–1519 | بلوک | `CREATE TABLE log_issue_notifications` | 1 | ⬜ |
| `DB-040.110` | 1520–1564 | بلوک | `CREATE TABLE schema_updates` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-041</b> — <code>upgrade-1.1-departments.sql</code> (16 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-041.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-041.02` | 2–9 | بلوک | `CREATE TABLE departments` | 0 | ⬜ |
| `DB-041.03` | 10–10 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-041.04` | 11–11 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-041.05` | 12–12 | بلوک | `ALTER TABLE users` | 1 | ⬜ |
| `DB-041.06` | 13–13 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-041.07` | 14–14 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-041.08` | 15–16 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-042</b> — <code>upgrade-1.10-priority.sql</code> (11 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-042.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-042.02` | 2–2 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |
| `DB-042.03` | 3–3 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-042.04` | 4–4 | بلوک | `UPDATE ola_policies` | 1 | ⬜ |
| `DB-042.05` | 5–6 | بلوک | `UPDATE problem_records` | 1 | ⬜ |
| `DB-042.06` | 7–7 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-042.07` | 8–8 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-042.08` | 9–9 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-042.09` | 10–11 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-043</b> — <code>upgrade-1.11-user-profile.sql</code> (5 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-043.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-043.02` | 2–2 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-043.03` | 3–3 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-043.04` | 4–5 | بلوک | `ALTER TABLE users` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-044</b> — <code>upgrade-1.12-food-ticket-management.sql</code> (16 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-044.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-044.02` | 3–16 | بلوک | `CREATE TABLE food_ticket_guest_cards` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-045</b> — <code>upgrade-1.13-asset-profile.sql</code> (93 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-045.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-045.02` | 4–10 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-045.03` | 11–93 | بلوک | `CREATE TABLE asset_profiles` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-046</b> — <code>upgrade-1.13-food-groups.sql</code> (50 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-046.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-046.02` | 4–16 | بلوک | `CREATE TABLE food_ticket_groups` | 1 | ⬜ |
| `DB-046.03` | 17–28 | بلوک | `CREATE TABLE food_ticket_group_members` | 1 | ⬜ |
| `DB-046.04` | 29–50 | بلوک | `CREATE TABLE food_ticket_group_runs` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-047</b> — <code>upgrade-1.14-system-logs.sql</code> (14 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-047.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-047.02` | 2–14 | بلوک | `CREATE TABLE system_logs` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-048</b> — <code>upgrade-1.15-plaque-fields.sql</code> (13 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-048.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-048.02` | 7–13 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-049</b> — <code>upgrade-1.16-ticket-number.sql</code> (12 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-049.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-049.02` | 6–7 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-049.03` | 8–12 | بلوک | `CREATE TABLE ticket_number_seq` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-050</b> — <code>upgrade-1.17-service-catalog.sql</code> (45 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-050.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-050.02` | 5–45 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-051</b> — <code>upgrade-1.18-service-categories.sql</code> (61 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-051.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-051.02` | 4–4 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-051.03` | 5–5 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-051.04` | 6–7 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-051.05` | 8–27 | بلوک | `INSERT INTO categories` | 1 | ⬜ |
| `DB-051.06` | 28–61 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-052</b> — <code>upgrade-1.19-food-ticket-queue.sql</code> (60 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-052.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-052.02` | 2–44 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-052.03` | 45–60 | بلوک | `CREATE TABLE food_ticket_print_log` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-053</b> — <code>upgrade-1.2-itsm.sql</code> (63 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-053.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-053.02` | 2–2 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-053.03` | 3–3 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-053.04` | 4–4 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-053.05` | 5–5 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-053.06` | 6–6 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-053.07` | 7–7 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-053.08` | 8–8 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-053.09` | 9–9 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-053.10` | 10–10 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-053.11` | 11–12 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-053.12` | 13–27 | بلوک | `CREATE TABLE ticket_events` | 1 | ⬜ |
| `DB-053.13` | 28–52 | بلوک | `CREATE TABLE assets` | 1 | ⬜ |
| `DB-053.14` | 53–63 | بلوک | `CREATE TABLE asset_inventory_history` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-054</b> — <code>upgrade-1.20-domain-scan.sql</code> (37 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-054.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-054.02` | 2–14 | بلوک | `CREATE TABLE domain_scan_runs` | 1 | ⬜ |
| `DB-054.03` | 15–27 | بلوک | `CREATE TABLE domain_scan_queue` | 1 | ⬜ |
| `DB-054.04` | 28–37 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-055</b> — <code>upgrade-1.21-organization.sql</code> (49 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-055.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-055.02` | 2–16 | بلوک | `CREATE TABLE org_units` | 0 | ⬜ |
| `DB-055.03` | 17–24 | بلوک | `CREATE TABLE org_unit_managers` | 0 | ⬜ |
| `DB-055.04` | 25–29 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-055.05` | 30–34 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-055.06` | 35–42 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-055.07` | 43–49 | بلوک | `UPDATE users` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-056</b> — <code>upgrade-1.22-permissions.sql</code> (16 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-056.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-056.02` | 4–7 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-056.03` | 8–9 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-056.04` | 10–16 | بلوک | `CREATE TABLE role_permissions` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-057</b> — <code>upgrade-1.23-activity-log.sql</code> (55 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-057.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-057.02` | 5–32 | بلوک | `CREATE TABLE activity_logs` | 1 | ⬜ |
| `DB-057.03` | 33–44 | بلوک | `INSERT INTO activity_logs` | 1 | ⬜ |
| `DB-057.04` | 45–47 | بلوک | `DROP TABLE IF EXISTS audit_logs` | 0 | ⬜ |
| `DB-057.05` | 48–51 | بلوک | `INSERT INTO settings` | 1 | ⬜ |
| `DB-057.06` | 52–55 | بلوک | `INSERT INTO settings` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-058</b> — <code>upgrade-1.24-traffic-control.sql</code> (55 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-058.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-058.02` | 10–10 | بلوک | `DROP TABLE IF EXISTS traffic_visits` | 0 | ⬜ |
| `DB-058.03` | 11–12 | بلوک | `DROP TABLE IF EXISTS traffic_destinations` | 0 | ⬜ |
| `DB-058.04` | 13–20 | بلوک | `CREATE TABLE traffic_destinations` | 0 | ⬜ |
| `DB-058.05` | 21–46 | بلوک | `CREATE TABLE traffic_visits` | 1 | ⬜ |
| `DB-058.06` | 47–55 | بلوک | `INSERT INTO traffic_destinations` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-059</b> — <code>upgrade-1.25-performance-indexes.sql</code> (36 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-059.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-059.02` | 8–36 | بلوک | `CREATE PROCEDURE itsm_add_index` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-060</b> — <code>upgrade-1.26-ticket-requesting-unit.sql</code> (28 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-060.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-060.02` | 9–14 | بلوک | `CREATE PROCEDURE itsm_ensure_req_unit` | 0 | ⬜ |
| `DB-060.03` | 15–20 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-060.04` | 21–28 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-061</b> — <code>upgrade-1.27-food-ticket-fixes.sql</code> (17 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-061.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-061.02` | 5–11 | بلوک | `UPDATE food_ticket_config` | 1 | ⬜ |
| `DB-061.03` | 12–17 | بلوک | `UPDATE food_ticket_config` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-062</b> — <code>upgrade-1.3-supervisor-inventory.sql</code> (14 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-062.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-062.02` | 2–2 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-062.03` | 3–3 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-062.04` | 4–4 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-062.05` | 5–5 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-062.06` | 6–6 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-062.07` | 7–7 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-062.08` | 8–8 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-062.09` | 9–9 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-062.10` | 10–10 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-062.11` | 11–11 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-062.12` | 12–12 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-062.13` | 13–14 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-063</b> — <code>upgrade-1.30-food-ticket-card-map.sql</code> (11 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-063.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-063.02` | 2–11 | بلوک | `CREATE TABLE food_ticket_card_map` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-064</b> — <code>upgrade-1.31-food-ticket-templates.sql</code> (14 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-064.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-064.02` | 2–14 | بلوک | `CREATE TABLE food_ticket_templates` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-065</b> — <code>upgrade-1.32-food-group-representative-absence.sql</code> (90 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-065.01` | 1–2 | بلوک | `=` | 0 | ⬜ |
| `DB-065.02` | 3–8 | بلوک | `=` | 0 | ⬜ |
| `DB-065.03` | 9–13 | بلوک | `=` | 0 | ⬜ |
| `DB-065.04` | 14–21 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-065.05` | 22–28 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-065.06` | 29–46 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-065.07` | 47–60 | بلوک | `CREATE TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-065.08` | 61–66 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-065.09` | 67–71 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-065.10` | 72–73 | بلوک | `=` | 0 | ⬜ |
| `DB-065.11` | 74–88 | بلوک | `=` | 1 | ⬜ |
| `DB-065.12` | 89–90 | بلوک | `=` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-066</b> — <code>upgrade-1.33-source-delete-truth.sql</code> (46 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-066.01` | 1–16 | بلوک | `=` | 1 | ⬜ |
| `DB-066.02` | 17–18 | بلوک | `=` | 0 | ⬜ |
| `DB-066.03` | 19–23 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-066.04` | 24–25 | بلوک | `CREATE INDEX idx_food_events_src_deleted` | 0 | ⬜ |
| `DB-066.05` | 26–27 | بلوک | `=` | 0 | ⬜ |
| `DB-066.06` | 28–38 | بلوک | `=` | 0 | ⬜ |
| `DB-066.07` | 39–40 | بلوک | `=` | 0 | ⬜ |
| `DB-066.08` | 41–44 | بلوک | `=` | 0 | ⬜ |
| `DB-066.09` | 45–46 | بلوک | `=` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-067</b> — <code>upgrade-1.38-food-orders.sql</code> (78 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-067.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-067.02` | 5–15 | بلوک | `CREATE TABLE food_catalog` | 1 | ⬜ |
| `DB-067.03` | 16–30 | بلوک | `CREATE TABLE food_calendar` | 1 | ⬜ |
| `DB-067.04` | 31–45 | بلوک | `CREATE TABLE food_calendar_items` | 1 | ⬜ |
| `DB-067.05` | 46–61 | بلوک | `CREATE TABLE food_orders` | 1 | ⬜ |
| `DB-067.06` | 62–78 | بلوک | `CREATE TABLE food_order_logs` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-068</b> — <code>upgrade-1.39-food-guest-requests.sql</code> (19 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-068.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-068.02` | 3–19 | بلوک | `CREATE TABLE food_guest_requests` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-069</b> — <code>upgrade-1.4-final.sql</code> (113 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-069.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-069.02` | 2–2 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-069.03` | 3–3 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-069.04` | 4–4 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-069.05` | 5–5 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-069.06` | 6–6 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-069.07` | 7–7 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-069.08` | 8–8 | بلوک | `ALTER TABLE departments` | 1 | ⬜ |
| `DB-069.09` | 9–9 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-069.10` | 10–10 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-069.11` | 11–11 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-069.12` | 12–12 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.13` | 13–13 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.14` | 14–14 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.15` | 15–15 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.16` | 16–16 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.17` | 17–17 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.18` | 18–18 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.19` | 19–19 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.20` | 20–20 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.21` | 21–21 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.22` | 22–22 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.23` | 23–23 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.24` | 24–24 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-069.25` | 25–37 | بلوک | `CREATE TABLE ticket_ratings` | 1 | ⬜ |
| `DB-069.26` | 38–51 | بلوک | `CREATE TABLE asset_memory_modules` | 1 | ⬜ |
| `DB-069.27` | 52–66 | بلوک | `CREATE TABLE asset_storage_devices` | 1 | ⬜ |
| `DB-069.28` | 67–78 | بلوک | `CREATE TABLE asset_graphics_adapters` | 1 | ⬜ |
| `DB-069.29` | 79–95 | بلوک | `CREATE TABLE asset_peripherals` | 1 | ⬜ |
| `DB-069.30` | 96–113 | بلوک | `CREATE TABLE asset_history_events` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-070</b> — <code>upgrade-1.40-food-guest-deputy.sql</code> (6 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-070.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-070.02` | 3–6 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-071</b> — <code>upgrade-1.5-offline-operations.sql</code> (112 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-071.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-071.02` | 2–17 | بلوک | `CREATE TABLE service_catalog` | 1 | ⬜ |
| `DB-071.03` | 18–30 | بلوک | `CREATE TABLE service_catalog_fields` | 1 | ⬜ |
| `DB-071.04` | 31–31 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-071.05` | 32–32 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-071.06` | 33–33 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-071.07` | 34–34 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-071.08` | 35–35 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-071.09` | 36–36 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-071.10` | 37–37 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-071.11` | 38–38 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-071.12` | 39–39 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-071.13` | 40–40 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-071.14` | 41–42 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-071.15` | 43–57 | بلوک | `CREATE TABLE notifications` | 1 | ⬜ |
| `DB-071.16` | 58–65 | بلوک | `CREATE TABLE login_attempts` | 1 | ⬜ |
| `DB-071.17` | 66–75 | بلوک | `CREATE TABLE ticket_sla_alerts` | 1 | ⬜ |
| `DB-071.18` | 76–89 | بلوک | `CREATE TABLE knowledge_articles` | 1 | ⬜ |
| `DB-071.19` | 90–99 | بلوک | `CREATE TABLE holidays` | 1 | ⬜ |
| `DB-071.20` | 100–112 | بلوک | `CREATE TABLE asset_relations` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-072</b> — <code>upgrade-1.6-hardening.sql</code> (19 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-072.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-072.02` | 2–9 | بلوک | `CREATE TABLE login_attempts` | 1 | ⬜ |
| `DB-072.03` | 10–19 | بلوک | `CREATE TABLE ticket_sla_alerts` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-073</b> — <code>upgrade-1.7-governance.sql</code> (88 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-073.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-073.02` | 2–30 | بلوک | `CREATE TABLE change_records` | 1 | ⬜ |
| `DB-073.03` | 31–42 | بلوک | `CREATE TABLE change_approvals` | 1 | ⬜ |
| `DB-073.04` | 43–53 | بلوک | `CREATE TABLE change_ticket_links` | 1 | ⬜ |
| `DB-073.05` | 54–77 | بلوک | `CREATE TABLE problem_records` | 1 | ⬜ |
| `DB-073.06` | 78–88 | بلوک | `CREATE TABLE problem_ticket_links` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-074</b> — <code>upgrade-1.8-cd-dvd-identity.sql</code> (6 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-074.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-074.02` | 2–6 | بلوک | `ALTER TABLE cd_dvd_records` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-075</b> — <code>upgrade-1.8-cd-dvd.sql</code> (44 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-075.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-075.02` | 2–9 | بلوک | `CREATE TABLE cd_dvd_types` | 1 | ⬜ |
| `DB-075.03` | 10–44 | بلوک | `CREATE TABLE cd_dvd_records` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-076</b> — <code>upgrade-1.8-final.sql</code> (47 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-076.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-076.02` | 2–8 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-076.03` | 9–17 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-076.04` | 18–35 | بلوک | `CREATE TABLE ola_policies` | 1 | ⬜ |
| `DB-076.05` | 36–45 | بلوک | `CREATE TABLE ticket_ola_alerts` | 1 | ⬜ |
| `DB-076.06` | 46–47 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-077</b> — <code>upgrade-1.8-routing.sql</code> (46 خط، 25 آدرس، بازبینی‌شده 0/25)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-077.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-077.02` | 2–12 | بلوک | `CREATE TABLE handling_units` | 1 | ⬜ |
| `DB-077.03` | 13–13 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-077.04` | 14–15 | بلوک | `ALTER TABLE users` | 1 | ⬜ |
| `DB-077.05` | 16–16 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-077.06` | 17–17 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-077.07` | 18–18 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-077.08` | 19–19 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-077.09` | 20–20 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-077.10` | 21–21 | بلوک | `ALTER TABLE service_catalog` | 1 | ⬜ |
| `DB-077.11` | 22–22 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-077.12` | 23–23 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-077.13` | 24–24 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-077.14` | 25–25 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-077.15` | 26–27 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-077.16` | 28–29 | بلوک | `INSERT INTO handling_units` | 1 | ⬜ |
| `DB-077.17` | 30–30 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-077.18` | 31–31 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |
| `DB-077.19` | 32–33 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-077.20` | 34–35 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |
| `DB-077.21` | 36–37 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |
| `DB-077.22` | 38–39 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |
| `DB-077.23` | 40–41 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |
| `DB-077.24` | 42–43 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |
| `DB-077.25` | 44–46 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-078</b> — <code>upgrade-1.9-food-ticket.sql</code> (80 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-078.01` | 1–14 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-078.02` | 15–37 | بلوک | `CREATE TABLE food_ticket_config` | 1 | ⬜ |
| `DB-078.03` | 38–64 | بلوک | `CREATE TABLE food_ticket_events` | 1 | ⬜ |
| `DB-078.04` | 65–80 | بلوک | `CREATE TABLE food_ticket_worker_status` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-079</b> — <code>upgrade-food-drop-legacy-orders-columns.sql</code> (6 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-079.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-079.02` | 4–4 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-079.03` | 5–6 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-080</b> — <code>upgrade-food-employee-auth-source.sql</code> (14 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-080.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-080.02` | 6–8 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-080.03` | 9–14 | بلوک | `UPDATE users` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-081</b> — <code>upgrade-remove-legacy-admin-role.sql</code> (11 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-081.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-081.02` | 6–8 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-081.03` | 9–11 | بلوک | `UPDATE users` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-082</b> — <code>upgrade-unique-employee-number.sql</code> (16 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-082.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-082.02` | 11–11 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-082.03` | 12–14 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-082.04` | 15–16 | بلوک | `ALTER TABLE users` | 0 | ⬜ |

</details>

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
<summary><b>PAGE-002</b> — <code>app/Views/layout/footer.php</code> (9 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-002.01` | 1–9 | ناحیه | `بلوک رویه‌ای 1-9` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-003</b> — <code>app/Views/layout/header.php</code> (57 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-003.01` | 1–57 | ناحیه | `بلوک رویه‌ای 1-57` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-004</b> — <code>asset-inventory-page.php</code> (90 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-004.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-004.02` | 4–89 | تابع | `inventory_render_assets_content` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-005</b> — <code>asset-names.php</code> (199 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-005.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `PAGE-005.02` | 25–30 | تابع | `asset_hostname_style` | 0 | ⬜ |
| `PAGE-005.03` | 32–62 | تابع | `asset_name_core` | 0 | ⬜ |
| `PAGE-005.04` | 64–80 | تابع | `asset_display_name` | 0 | ⬜ |
| `PAGE-005.05` | 82–93 | تابع | `asset_is_auto_domain_tag` | 0 | ⬜ |
| `PAGE-005.06` | 95–108 | تابع | `asset_name_title` | 0 | ⬜ |
| `PAGE-005.07` | 110–115 | تابع | `asset_name_attr` | 0 | ⬜ |
| `PAGE-005.08` | 117–131 | تابع | `asset_tag_label` | 0 | ⬜ |
| `PAGE-005.09` | 132–134 | ناحیه | `بلوک رویه‌ای 132-134` | 0 | ⬜ |
| `PAGE-005.10` | 135–151 | تابع | `asset_identity_key` | 0 | ⬜ |
| `PAGE-005.11` | 153–185 | تابع | `asset_resolve_id` | 0 | ⬜ |
| `PAGE-005.12` | 187–197 | تابع | `asset_sync_identity_key` | 1 | ⬜ |
| `PAGE-005.13` | 198–199 | ناحیه | `بلوک رویه‌ای 198-199` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-006</b> — <code>asset-profile.php</code> (1,037 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-006.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-006.02` | 8–26 | تابع | `asset_profile_column_sql` | 0 | ⬜ |
| `PAGE-006.03` | 28–65 | تابع | `asset_profile_ensure_schema` | **9** | ⬜ |
| `PAGE-006.04` | 67–213 | تابع | `asset_profile_sections` | 1 | ⬜ |
| `PAGE-006.05` | 215–228 | تابع | `asset_profile_all_columns` | 0 | ⬜ |
| `PAGE-006.06` | 230–240 | تابع | `asset_profile_field` | 0 | ⬜ |
| `PAGE-006.07` | 242–260 | تابع | `asset_profile_export_columns` | 0 | ⬜ |
| `PAGE-006.08` | 262–275 | تابع | `asset_profile_export_value` | 0 | ⬜ |
| `PAGE-006.09` | 277–285 | تابع | `asset_profile_fetch` | 0 | ⬜ |
| `PAGE-006.10` | 287–345 | تابع | `asset_profile_fill_from_asset` | 0 | ⬜ |
| `PAGE-006.11` | 347–392 | تابع | `asset_profile_collection_status_summary` | 0 | ⬜ |
| `PAGE-006.12` | 394–420 | تابع | `asset_profile_map` | 5 | ⬜ |
| `PAGE-006.13` | 422–431 | تابع | `asset_profile_input_value` | 0 | ⬜ |
| `PAGE-006.14` | 433–482 | تابع | `asset_profile_render_fields` | 5 | ⬜ |
| `PAGE-006.15` | 484–492 | تابع | `asset_profile_render_sections` | 0 | ⬜ |
| `PAGE-006.16` | 494–546 | تابع | `asset_profile_save` | 6 | ⬜ |
| `PAGE-006.17` | 548–559 | تابع | `asset_profile_printer_port_is_physical` | 0 | ⬜ |
| `PAGE-006.18` | 561–592 | تابع | `asset_profile_is_virtual_printer` | 0 | ⬜ |
| `PAGE-006.19` | 594–604 | تابع | `asset_profile_printer_kind` | 0 | ⬜ |
| `PAGE-006.20` | 606–624 | تابع | `asset_profile_real_printers` | 0 | ⬜ |
| `PAGE-006.21` | 626–962 | تابع | `asset_profile_auto_values` | 5 | ⬜ |
| `PAGE-006.22` | 964–1036 | تابع | `asset_profile_apply_auto` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-007</b> — <code>backup.php</code> (442 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-007.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-007.02` | 6–10 | تابع | `backup_root_path` | 0 | ⬜ |
| `PAGE-007.03` | 12–30 | تابع | `backup_mysql_binary` | 0 | ⬜ |
| `PAGE-007.04` | 32–76 | تابع | `backup_run_process` | 4 | ⬜ |
| `PAGE-007.05` | 78–126 | تابع | `backup_uploads` | 0 | ⬜ |
| `PAGE-007.06` | 128–183 | تابع | `backup_system_files_zip` | 0 | ⬜ |
| `PAGE-007.07` | 185–222 | تابع | `create_backup_bundle` | 0 | ⬜ |
| `PAGE-007.08` | 224–240 | تابع | `backup_runs` | 0 | ⬜ |
| `PAGE-007.09` | 242–255 | تابع | `backup_directory_size` | 0 | ⬜ |
| `PAGE-007.10` | 257–264 | تابع | `backup_download_path` | 0 | ⬜ |
| `PAGE-007.11` | 266–290 | تابع | `backup_upload_archive_entry` | 0 | ⬜ |
| `PAGE-007.12` | 292–351 | تابع | `backup_validate_upload_archive` | 0 | ⬜ |
| `PAGE-007.13` | 353–420 | تابع | `restore_upload_archive` | 1 | ⬜ |
| `PAGE-007.14` | 422–441 | تابع | `restore_sql_dump` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-008</b> — <code>bootstrap.php</code> (82 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-008.01` | 1–38 | ناحیه | `بلوک رویه‌ای 1-38` | 0 | ⬜ |
| `PAGE-008.02` | 39–41 | ناحیه | `هسته، پیکربندی، HTTP امن و پایگاه داده (شرح در سرآ` | 0 | ⬜ |
| `PAGE-008.03` | 42–45 | ناحیه | `هستهٔ مرکزی ثبت رخداد و هم‌بستگی درخواست` | 0 | ⬜ |
| `PAGE-008.04` | 46–47 | ناحیه | `پیام‌های فلش، لاگ سیستمی و CSRF (شرح در سرآیند هما` | 0 | ⬜ |
| `PAGE-008.05` | 48–48 | ناحیه | `ماژول تشدید شدت (افزوده‌شده در 1.62.89) — طبق قواع` | 0 | ⬜ |
| `PAGE-008.06` | 49–49 | ناحیه | `اگر فایل موجود نباشد سامانه بدون آن اجرا می‌شود؛ s` | 0 | ⬜ |
| `PAGE-008.07` | 50–56 | ناحیه | `این ماژول را با function_exists گارد می‌کند و هیچ ` | 0 | ⬜ |
| `PAGE-008.08` | 57–57 | ناحیه | `سخت‌سازی نشست` | 0 | ⬜ |
| `PAGE-008.09` | 58–58 | ناحیه | `اینجا اجرا می‌شود چون cfg() در lib/db-core.php تعر` | 0 | ⬜ |
| `PAGE-008.10` | 59–59 | ناحیه | `lib/auth-scope.php باشد تا نشستِ منقضی اصلاً به لا` | 0 | ⬜ |
| `PAGE-008.11` | 60–63 | ناحیه | `security.session_idle_days: روزهای بی‌فعالیت تا خر` | 0 | ⬜ |
| `PAGE-008.12` | 64–66 | ناحیه | `نشست کاربر، سطوح دسترسی و دامنهٔ دید (شرح در سرآین` | 0 | ⬜ |
| `PAGE-008.13` | 67–69 | ناحیه | `تنظیمات، افکت‌های نمایشی و نقش‌های کلیدی (شرح در س` | 0 | ⬜ |
| `PAGE-008.14` | 70–72 | ناحیه | `پیکربندی و همگام‌سازی دامین (LDAP) (شرح در سرآیند ` | 0 | ⬜ |
| `PAGE-008.15` | 73–75 | ناحیه | `شمارهٔ درخواست، تقویم شمسی و SLA/OLA (شرح در سرآین` | 0 | ⬜ |
| `PAGE-008.16` | 76–78 | ناحیه | `اعلان‌ها، ورود دامین و کاربران (شرح در سرآیند همان` | 0 | ⬜ |
| `PAGE-008.17` | 79–82 | ناحیه | `ثبت فعالیت، حسابرسی و رویداد درخواست‌ها (شرح در سر` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-009</b> — <code>cd-dvd.php</code> (895 خط، 44 آدرس، بازبینی‌شده 0/44)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-009.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-009.02` | 6–9 | تابع | `cd_dvd_media_bounds` | 0 | ⬜ |
| `PAGE-009.03` | 11–15 | تابع | `cd_dvd_normalize_serial` | 0 | ⬜ |
| `PAGE-009.04` | 17–20 | تابع | `cd_dvd_internal_number` | 0 | ⬜ |
| `PAGE-009.05` | 22–25 | تابع | `cd_dvd_serial_valid` | 0 | ⬜ |
| `PAGE-009.06` | 27–35 | تابع | `cd_dvd_serial_matches_media` | 0 | ⬜ |
| `PAGE-009.07` | 37–40 | تابع | `cd_dvd_is_primary_admin` | 0 | ⬜ |
| `PAGE-009.08` | 42–52 | تابع | `cd_dvd_inspection_user_ids` | 0 | ⬜ |
| `PAGE-009.09` | 54–58 | تابع | `cd_dvd_inspection_user_id` | 0 | ⬜ |
| `PAGE-009.10` | 60–63 | تابع | `cd_dvd_is_inspection_user` | 0 | ⬜ |
| `PAGE-009.11` | 65–71 | تابع | `cd_dvd_can_see_all` | 0 | ⬜ |
| `PAGE-009.12` | 73–85 | تابع | `cd_dvd_can_edit_records` | 0 | ⬜ |
| `PAGE-009.13` | 87–93 | تابع | `cd_dvd_can_export` | 0 | ⬜ |
| `PAGE-009.14` | 95–106 | تابع | `cd_dvd_can_view_record` | 0 | ⬜ |
| `PAGE-009.15` | 108–118 | تابع | `cd_dvd_audit_snapshot` | 0 | ⬜ |
| `PAGE-009.16` | 120–126 | تابع | `cd_dvd_can_register_in` | 0 | ⬜ |
| `PAGE-009.17` | 128–134 | تابع | `cd_dvd_can_submit_out` | 0 | ⬜ |
| `PAGE-009.18` | 136–151 | تابع | `cd_dvd_available` | 0 | ⬜ |
| `PAGE-009.19` | 153–162 | تابع | `cd_dvd_scope` | 5 | ⬜ |
| `PAGE-009.20` | 164–169 | تابع | `cd_dvd_can_manage_record` | 0 | ⬜ |
| `PAGE-009.21` | 171–174 | تابع | `cd_dvd_types` | 0 | ⬜ |
| `PAGE-009.22` | 176–179 | تابع | `cd_dvd_active_users` | 0 | ⬜ |
| `PAGE-009.23` | 181–191 | تابع | `cd_dvd_deputy_options` | 0 | ⬜ |
| `PAGE-009.24` | 193–202 | تابع | `cd_dvd_routing_department_id` | 0 | ⬜ |
| `PAGE-009.25` | 204–222 | تابع | `cd_dvd_next_serial` | 0 | ⬜ |
| `PAGE-009.26` | 224–236 | تابع | `cd_dvd_last_record` | 0 | ⬜ |
| `PAGE-009.27` | 238–243 | تابع | `cd_dvd_fetch_record` | 0 | ⬜ |
| `PAGE-009.28` | 245–259 | تابع | `cd_dvd_parse_date` | 0 | ⬜ |
| `PAGE-009.29` | 261–265 | تابع | `cd_dvd_default_date` | 0 | ⬜ |
| `PAGE-009.30` | 267–380 | تابع | `cd_dvd_validate_payload` | 0 | ⬜ |
| `PAGE-009.31` | 382–391 | تابع | `cd_dvd_acquire_write_lock` | 0 | ⬜ |
| `PAGE-009.32` | 393–401 | تابع | `cd_dvd_release_write_lock` | 4 | ⬜ |
| `PAGE-009.33` | 403–451 | تابع | `cd_dvd_create_record` | 7 | ⬜ |
| `PAGE-009.34` | 453–474 | تابع | `cd_dvd_update_record` | 2 | ⬜ |
| `PAGE-009.35` | 476–494 | تابع | `cd_dvd_delete_record` | 2 | ⬜ |
| `PAGE-009.36` | 496–531 | تابع | `cd_dvd_query_records` | 0 | ⬜ |
| `PAGE-009.37` | 533–538 | تابع | `cd_dvd_personal_incoming` | 0 | ⬜ |
| `PAGE-009.38` | 540–550 | تابع | `cd_dvd_latest_records` | 0 | ⬜ |
| `PAGE-009.39` | 552–571 | تابع | `cd_dvd_stats` | 5 | ⬜ |
| `PAGE-009.40` | 573–586 | تابع | `cd_dvd_export` | 0 | ⬜ |
| `PAGE-009.41` | 588–616 | تابع | `cd_dvd_render_surface` | 2 | ⬜ |
| `PAGE-009.42` | 618–733 | تابع | `cd_dvd_render_page_legacy` | 2 | ⬜ |
| `PAGE-009.43` | 735–872 | تابع | `cd_dvd_render_page` | **8** | ⬜ |
| `PAGE-009.44` | 874–894 | تابع | `cd_dvd_render_history` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-010</b> — <code>chatbot-tts-lexicon.php</code> (15 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-010.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-011</b> — <code>chatbot.php</code> (3,413 خط، 123 آدرس، بازبینی‌شده 0/123)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-011.01` | 1–14 | ناحیه | `بلوک رویه‌ای 1-14` | 0 | ⬜ |
| `PAGE-011.02` | 15–27 | تابع | `chatbot_normalize` | 0 | ⬜ |
| `PAGE-011.03` | 29–33 | تابع | `chatbot_compact` | 0 | ⬜ |
| `PAGE-011.04` | 35–46 | تابع | `chatbot_keywords` | 0 | ⬜ |
| `PAGE-011.05` | 48–87 | تابع | `chatbot_score` | 0 | ⬜ |
| `PAGE-011.06` | 88–100 | ناحیه | `بلوک رویه‌ای 88-100` | 0 | ⬜ |
| `PAGE-011.07` | 101–111 | تابع | `chatbot_has_any` | 0 | ⬜ |
| `PAGE-011.08` | 113–125 | تابع | `chatbot_reply` | 1 | ⬜ |
| `PAGE-011.09` | 127–138 | تابع | `chatbot_flow_active` | 0 | ⬜ |
| `PAGE-011.10` | 140–168 | تابع | `chatbot_flow_step` | 0 | ⬜ |
| `PAGE-011.11` | 170–182 | تابع | `chatbot_ticket_ref` | 0 | ⬜ |
| `PAGE-011.12` | 184–196 | تابع | `chatbot_ticket_status` | 5 | ⬜ |
| `PAGE-011.13` | 198–202 | تابع | `chatbot_ticket_line` | 0 | ⬜ |
| `PAGE-011.14` | 204–407 | تابع | `chatbot_answer` | 1 | ⬜ |
| `PAGE-011.15` | 408–448 | ناحیه | `بلوک رویه‌ای 408-448` | 0 | ⬜ |
| `PAGE-011.16` | 449–471 | تابع | `chatbot_expand_words` | 0 | ⬜ |
| `PAGE-011.17` | 473–487 | تابع | `chatbot_edit_distance` | 0 | ⬜ |
| `PAGE-011.18` | 489–502 | تابع | `chatbot_typo_match` | 0 | ⬜ |
| `PAGE-011.19` | 503–506 | ناحیه | `بلوک رویه‌ای 503-506` | 0 | ⬜ |
| `PAGE-011.20` | 507–514 | تابع | `chatbot_contains_word` | 0 | ⬜ |
| `PAGE-011.21` | 516–530 | تابع | `chatbot_guide_tokens` | 0 | ⬜ |
| `PAGE-011.22` | 532–615 | تابع | `chatbot_guide_index` | 1 | ⬜ |
| `PAGE-011.23` | 617–649 | تابع | `chatbot_guide_search` | 0 | ⬜ |
| `PAGE-011.24` | 651–676 | تابع | `chatbot_guide_suggestions` | 0 | ⬜ |
| `PAGE-011.25` | 678–683 | تابع | `chatbot_guide_reply` | 0 | ⬜ |
| `PAGE-011.26` | 685–698 | تابع | `chatbot_guide_answer` | 0 | ⬜ |
| `PAGE-011.27` | 700–783 | تابع | `chatbot_api_handle` | **12** | ⬜ |
| `PAGE-011.28` | 785–815 | تابع | `chatbot_save_faq` | 1 | ⬜ |
| `PAGE-011.29` | 817–872 | تابع | `chatbot_save_tts` | 0 | ⬜ |
| `PAGE-011.30` | 874–896 | تابع | `chatbot_widget_defaults` | 0 | ⬜ |
| `PAGE-011.31` | 898–946 | تابع | `chatbot_widget_config` | 0 | ⬜ |
| `PAGE-011.32` | 948–990 | تابع | `chatbot_save_widget` | 0 | ⬜ |
| `PAGE-011.33` | 992–1006 | تابع | `chatbot_tts_config` | 0 | ⬜ |
| `PAGE-011.34` | 1008–1016 | تابع | `chatbot_tts_proc_available` | 0 | ⬜ |
| `PAGE-011.35` | 1018–1044 | تابع | `chatbot_tts_find_espeak` | 0 | ⬜ |
| `PAGE-011.36` | 1046–1069 | تابع | `chatbot_tts_find_piper` | 0 | ⬜ |
| `PAGE-011.37` | 1071–1104 | تابع | `chatbot_tts_piper_model_rank` | 0 | ⬜ |
| `PAGE-011.38` | 1106–1147 | تابع | `chatbot_tts_piper_model` | 1 | ⬜ |
| `PAGE-011.39` | 1149–1179 | تابع | `chatbot_tts_active` | 0 | ⬜ |
| `PAGE-011.40` | 1181–1187 | تابع | `chatbot_tts_length_scale` | 0 | ⬜ |
| `PAGE-011.41` | 1189–1216 | تابع | `chatbot_tts_status` | 0 | ⬜ |
| `PAGE-011.42` | 1218–1221 | تابع | `chatbot_tts_rules_version` | 0 | ⬜ |
| `PAGE-011.43` | 1223–1232 | تابع | `chatbot_tts_word_replace` | 0 | ⬜ |
| `PAGE-011.44` | 1234–1241 | تابع | `chatbot_tts_ascii_digits` | 0 | ⬜ |
| `PAGE-011.45` | 1243–1249 | تابع | `chatbot_tts_ascii_letters` | 0 | ⬜ |
| `PAGE-011.46` | 1251–1265 | تابع | `chatbot_tts_digits_spoken` | 0 | ⬜ |
| `PAGE-011.47` | 1267–1293 | تابع | `chatbot_tts_three_digit` | 0 | ⬜ |
| `PAGE-011.48` | 1295–1320 | تابع | `chatbot_tts_int_to_words` | 0 | ⬜ |
| `PAGE-011.49` | 1322–1339 | تابع | `chatbot_tts_number_token` | 0 | ⬜ |
| `PAGE-011.50` | 1341–1351 | تابع | `chatbot_tts_number_int_string` | 0 | ⬜ |
| `PAGE-011.51` | 1353–1365 | تابع | `chatbot_tts_ordinal` | 0 | ⬜ |
| `PAGE-011.52` | 1367–1371 | تابع | `chatbot_tts_month_name` | 0 | ⬜ |
| `PAGE-011.53` | 1373–1406 | تابع | `chatbot_tts_dates_times` | 0 | ⬜ |
| `PAGE-011.54` | 1408–1448 | تابع | `chatbot_tts_ids` | 0 | ⬜ |
| `PAGE-011.55` | 1450–1478 | تابع | `chatbot_tts_numbers_to_words` | 0 | ⬜ |
| `PAGE-011.56` | 1480–1490 | تابع | `chatbot_tts_letter_name` | 0 | ⬜ |
| `PAGE-011.57` | 1492–1503 | تابع | `chatbot_tts_log_unknown` | 2 | ⬜ |
| `PAGE-011.58` | 1505–1528 | تابع | `chatbot_tts_spell_acronyms` | 0 | ⬜ |
| `PAGE-011.59` | 1530–1555 | تابع | `chatbot_tts_unknown_latin` | 0 | ⬜ |
| `PAGE-011.60` | 1557–1570 | تابع | `chatbot_tts_symbols` | 0 | ⬜ |
| `PAGE-011.61` | 1572–1579 | تابع | `chatbot_tts_strip_markdown` | 0 | ⬜ |
| `PAGE-011.62` | 1581–1586 | تابع | `chatbot_tts_zwnj_policy` | 1 | ⬜ |
| `PAGE-011.63` | 1588–1598 | تابع | `chatbot_tts_apply_zwnj` | 1 | ⬜ |
| `PAGE-011.64` | 1600–1607 | تابع | `chatbot_tts_he_yezafe` | 0 | ⬜ |
| `PAGE-011.65` | 1609–1635 | تابع | `chatbot_tts_lexicon` | 0 | ⬜ |
| `PAGE-011.66` | 1637–1641 | تابع | `chatbot_tts_lexicon_hash` | 0 | ⬜ |
| `PAGE-011.67` | 1643–1654 | تابع | `chatbot_tts_lexicon_apply` | 0 | ⬜ |
| `PAGE-011.68` | 1656–1659 | تابع | `chatbot_tts_spoken_path` | 0 | ⬜ |
| `PAGE-011.69` | 1661–1669 | تابع | `chatbot_tts_spoken_map` | 1 | ⬜ |
| `PAGE-011.70` | 1671–1688 | تابع | `chatbot_tts_spoken_set` | 2 | ⬜ |
| `PAGE-011.71` | 1690–1701 | تابع | `chatbot_tts_spoken_for` | 0 | ⬜ |
| `PAGE-011.72` | 1703–1714 | تابع | `chatbot_tts_clean` | 0 | ⬜ |
| `PAGE-011.73` | 1716–1730 | تابع | `chatbot_tts_trim_length` | 0 | ⬜ |
| `PAGE-011.74` | 1732–1777 | تابع | `chatbot_tts_colloquial` | 0 | ⬜ |
| `PAGE-011.75` | 1779–1796 | تابع | `chatbot_tts_phrase` | 0 | ⬜ |
| `PAGE-011.76` | 1798–1832 | تابع | `chatbot_tts_limit_phrase` | 0 | ⬜ |
| `PAGE-011.77` | 1834–1841 | تابع | `chatbot_tts_vocalize` | 0 | ⬜ |
| `PAGE-011.78` | 1843–1856 | تابع | `chatbot_tts_ezafe_skip_first` | 0 | ⬜ |
| `PAGE-011.79` | 1858–1869 | تابع | `chatbot_tts_ezafe_skip_second` | 0 | ⬜ |
| `PAGE-011.80` | 1871–1882 | تابع | `chatbot_tts_silent_heh` | 0 | ⬜ |
| `PAGE-011.81` | 1884–1895 | تابع | `chatbot_tts_ezafe_nouns` | 0 | ⬜ |
| `PAGE-011.82` | 1897–1908 | تابع | `chatbot_tts_ezafe_pairs_builtin` | 0 | ⬜ |
| `PAGE-011.83` | 1910–1930 | تابع | `chatbot_tts_overlay_map` | 0 | ⬜ |
| `PAGE-011.84` | 1932–1944 | تابع | `chatbot_tts_ezafe_pairs` | 0 | ⬜ |
| `PAGE-011.85` | 1946–1953 | تابع | `chatbot_tts_ezafe_mode` | 0 | ⬜ |
| `PAGE-011.86` | 1955–1959 | تابع | `chatbot_tts_word_bare` | 0 | ⬜ |
| `PAGE-011.87` | 1961–1980 | تابع | `chatbot_tts_mark_ezafe_first` | 0 | ⬜ |
| `PAGE-011.88` | 1982–2032 | تابع | `chatbot_tts_ezafe` | 0 | ⬜ |
| `PAGE-011.89` | 2034–2038 | تابع | `chatbot_tts_colloquial_enabled` | 0 | ⬜ |
| `PAGE-011.90` | 2040–2072 | تابع | `chatbot_tts_prepare` | 0 | ⬜ |
| `PAGE-011.91` | 2074–2093 | تابع | `chatbot_tts_lab_variant` | 0 | ⬜ |
| `PAGE-011.92` | 2095–2121 | تابع | `chatbot_tts_lab_harakat` | 0 | ⬜ |
| `PAGE-011.93` | 2123–2161 | تابع | `chatbot_tts_stages` | 0 | ⬜ |
| `PAGE-011.94` | 2163–2179 | تابع | `chatbot_tts_espeak_data_path` | 0 | ⬜ |
| `PAGE-011.95` | 2181–2210 | تابع | `chatbot_tts_ipa` | 0 | ⬜ |
| `PAGE-011.96` | 2212–2263 | تابع | `chatbot_tts_model_json_report` | 0 | ⬜ |
| `PAGE-011.97` | 2265–2269 | تابع | `chatbot_tts_lab_filename` | 0 | ⬜ |
| `PAGE-011.98` | 2271–2297 | تابع | `chatbot_tts_lab_run` | 2 | ⬜ |
| `PAGE-011.99` | 2299–2303 | تابع | `chatbot_tts_voice_id` | 0 | ⬜ |
| `PAGE-011.100` | 2305–2318 | تابع | `chatbot_tts_wait_exit` | 2 | ⬜ |
| `PAGE-011.101` | 2320–2350 | تابع | `chatbot_tts_taskkill` | 7 | ⬜ |
| `PAGE-011.102` | 2352–2440 | تابع | `chatbot_tts_exec` | 7 | ⬜ |
| `PAGE-011.103` | 2442–2446 | تابع | `chatbot_tts_is_valid_wav` | 0 | ⬜ |
| `PAGE-011.104` | 2448–2470 | تابع | `chatbot_tts_run_espeak` | 6 | ⬜ |
| `PAGE-011.105` | 2472–2519 | تابع | `chatbot_tts_run_piper` | 7 | ⬜ |
| `PAGE-011.106` | 2521–2535 | تابع | `chatbot_tts_probe_piper` | 0 | ⬜ |
| `PAGE-011.107` | 2537–2549 | تابع | `chatbot_tts_probe_engine` | 0 | ⬜ |
| `PAGE-011.108` | 2550–2551 | ناحیه | `بلوک رویه‌ای 2550-2551` | 0 | ⬜ |
| `PAGE-011.109` | 2552–2598 | تابع | `chatbot_tts_voice_status` | 3 | ⬜ |
| `PAGE-011.110` | 2600–2626 | تابع | `chatbot_tts_chunks` | 0 | ⬜ |
| `PAGE-011.111` | 2628–2663 | تابع | `chatbot_tts_wav_parse` | 0 | ⬜ |
| `PAGE-011.112` | 2665–2704 | تابع | `chatbot_tts_wav_concat` | 0 | ⬜ |
| `PAGE-011.113` | 2706–2763 | تابع | `chatbot_tts_say` | 7 | ⬜ |
| `PAGE-011.114` | 2765–2794 | تابع | `chatbot_tts_lab_default_phrases` | 0 | ⬜ |
| `PAGE-011.115` | 2796–2856 | تابع | `chatbot_tts_render_lab` | 0 | ⬜ |
| `PAGE-011.116` | 2858–2879 | تابع | `chatbot_tts_write_overlay` | 2 | ⬜ |
| `PAGE-011.117` | 2881–2904 | تابع | `chatbot_tts_corpus_texts` | 4 | ⬜ |
| `PAGE-011.118` | 2906–2910 | تابع | `chatbot_tts_propose_pair` | 0 | ⬜ |
| `PAGE-011.119` | 2912–2951 | تابع | `chatbot_tts_mine_pairs` | 0 | ⬜ |
| `PAGE-011.120` | 2953–2988 | تابع | `chatbot_tts_save_mined_pairs` | 0 | ⬜ |
| `PAGE-011.121` | 2990–3010 | تابع | `chatbot_tts_render_pair_miner` | 0 | ⬜ |
| `PAGE-011.122` | 3012–3204 | تابع | `chatbot_render_admin` | **8** | ⬜ |
| `PAGE-011.123` | 3206–3412 | تابع | `chatbot_render_widget` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-012</b> — <code>config.sample.php</code> (57 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-012.01` | 1–57 | ناحیه | `بلوک رویه‌ای 1-57` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-013</b> — <code>domain-scan.php</code> (1,281 خط، 40 آدرس، بازبینی‌شده 0/40)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-013.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `PAGE-013.02` | 7–48 | تابع | `domain_scan_ensure_schema` | 5 | ⬜ |
| `PAGE-013.03` | 50–54 | تابع | `domain_scan_timeout_ms` | 0 | ⬜ |
| `PAGE-013.04` | 56–59 | تابع | `domain_scan_account_label` | 0 | ⬜ |
| `PAGE-013.05` | 61–64 | تابع | `domain_scan_https_request` | 0 | ⬜ |
| `PAGE-013.06` | 66–75 | تابع | `domain_scan_validate_credentials` | 0 | ⬜ |
| `PAGE-013.07` | 77–84 | تابع | `domain_scan_credential_key` | 0 | ⬜ |
| `PAGE-013.08` | 86–113 | تابع | `domain_scan_store_run_credentials` | 0 | ⬜ |
| `PAGE-013.09` | 115–146 | تابع | `domain_scan_run_credentials` | 0 | ⬜ |
| `PAGE-013.10` | 148–160 | تابع | `domain_scan_clear_run_credentials` | 0 | ⬜ |
| `PAGE-013.11` | 162–177 | تابع | `domain_scan_ad_time` | 0 | ⬜ |
| `PAGE-013.12` | 179–247 | تابع | `ldap_domain_computers` | 3 | ⬜ |
| `PAGE-013.13` | 249–274 | تابع | `domain_scan_asset_for` | 1 | ⬜ |
| `PAGE-013.14` | 276–285 | تابع | `domain_scan_ad_values` | 0 | ⬜ |
| `PAGE-013.15` | 287–309 | تابع | `domain_scan_start` | 1 | ⬜ |
| `PAGE-013.16` | 311–347 | تابع | `domain_scan_start_selected` | 6 | ⬜ |
| `PAGE-013.17` | 349–380 | تابع | `domain_scan_import` | 1 | ⬜ |
| `PAGE-013.18` | 382–388 | تابع | `domain_scan_powershell_path` | 0 | ⬜ |
| `PAGE-013.19` | 390–466 | تابع | `domain_scan_run_processes` | 7 | ⬜ |
| `PAGE-013.20` | 468–475 | تابع | `domain_scan_resolve_ipv4` | 1 | ⬜ |
| `PAGE-013.21` | 477–484 | تابع | `domain_scan_ping_command` | 0 | ⬜ |
| `PAGE-013.22` | 486–504 | تابع | `domain_scan_icmp_round` | 0 | ⬜ |
| `PAGE-013.23` | 506–578 | تابع | `domain_scan_tcp_probe` | 3 | ⬜ |
| `PAGE-013.24` | 580–652 | تابع | `domain_scan_ping_hosts` | 1 | ⬜ |
| `PAGE-013.25` | 654–662 | تابع | `domain_scan_online` | 0 | ⬜ |
| `PAGE-013.26` | 664–682 | تابع | `domain_scan_mark_reachability` | 1 | ⬜ |
| `PAGE-013.27` | 684–716 | تابع | `domain_scan_friendly_error` | 1 | ⬜ |
| `PAGE-013.28` | 718–848 | تابع | `domain_scan_error_catalog` | 1 | ⬜ |
| `PAGE-013.29` | 850–882 | تابع | `domain_scan_error_code` | 0 | ⬜ |
| `PAGE-013.30` | 884–921 | تابع | `domain_scan_failure_summary` | 0 | ⬜ |
| `PAGE-013.31` | 923–943 | تابع | `domain_scan_retry_failed` | 1 | ⬜ |
| `PAGE-013.32` | 945–968 | تابع | `domain_scan_local_names` | 2 | ⬜ |
| `PAGE-013.33` | 970–986 | تابع | `domain_scan_preflight` | 0 | ⬜ |
| `PAGE-013.34` | 988–1093 | تابع | `domain_scan_remote_inventory_many` | 5 | ⬜ |
| `PAGE-013.35` | 1095–1106 | تابع | `domain_scan_remote_inventory` | 0 | ⬜ |
| `PAGE-013.36` | 1108–1155 | تابع | `domain_scan_store_inventory` | 1 | ⬜ |
| `PAGE-013.37` | 1157–1254 | تابع | `domain_scan_process` | **8** | ⬜ |
| `PAGE-013.38` | 1256–1263 | تابع | `domain_scan_run` | 0 | ⬜ |
| `PAGE-013.39` | 1265–1272 | تابع | `domain_scan_queue_rows` | 5 | ⬜ |
| `PAGE-013.40` | 1274–1280 | تابع | `domain_scan_latest_run` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-014</b> — <code>food-order-import.php</code> (477 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-014.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-014.02` | 4–35 | تابع | `food_order_import_column_keys` | 0 | ⬜ |
| `PAGE-014.03` | 36–38 | ناحیه | `بلوک رویه‌ای 36-38` | 0 | ⬜ |
| `PAGE-014.04` | 39–46 | تابع | `food_order_import_key` | 0 | ⬜ |
| `PAGE-014.05` | 48–113 | تابع | `food_order_import_read` | 0 | ⬜ |
| `PAGE-014.06` | 115–130 | تابع | `food_order_import_date` | 0 | ⬜ |
| `PAGE-014.07` | 132–151 | تابع | `food_order_import_time` | 0 | ⬜ |
| `PAGE-014.08` | 153–159 | تابع | `food_order_import_norm_food` | 0 | ⬜ |
| `PAGE-014.09` | 161–312 | تابع | `food_order_import_plan` | 6 | ⬜ |
| `PAGE-014.10` | 314–367 | تابع | `food_order_import_apply` | 0 | ⬜ |
| `PAGE-014.11` | 369–372 | تابع | `food_order_import_h` | 0 | ⬜ |
| `PAGE-014.12` | 374–476 | تابع | `food_order_import_handle` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-015</b> — <code>food-order.php</code> (3,684 خط، 101 آدرس، بازبینی‌شده 0/101)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-015.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-015.02` | 6–42 | تابع | `food_order_schema_ensure` | 5 | ⬜ |
| `PAGE-015.03` | 44–52 | تابع | `food_order_normalize_digits` | 0 | ⬜ |
| `PAGE-015.04` | 54–57 | تابع | `food_order_digits` | 0 | ⬜ |
| `PAGE-015.05` | 59–95 | تابع | `food_order_iso_date` | 0 | ⬜ |
| `PAGE-015.06` | 97–105 | تابع | `food_order_timezone` | 0 | ⬜ |
| `PAGE-015.07` | 107–110 | تابع | `food_order_now` | 0 | ⬜ |
| `PAGE-015.08` | 112–115 | تابع | `food_order_today` | 0 | ⬜ |
| `PAGE-015.09` | 117–125 | تابع | `food_order_jalali_label` | 0 | ⬜ |
| `PAGE-015.10` | 127–156 | تابع | `food_order_jalali_month_range` | 0 | ⬜ |
| `PAGE-015.11` | 158–163 | تابع | `food_order_is_primary_admin` | 0 | ⬜ |
| `PAGE-015.12` | 165–168 | تابع | `food_order_user_can` | 0 | ⬜ |
| `PAGE-015.13` | 170–175 | تابع | `food_order_require_permission` | 0 | ⬜ |
| `PAGE-015.14` | 177–186 | تابع | `food_order_can_view_order_counts` | 0 | ⬜ |
| `PAGE-015.15` | 188–193 | تابع | `food_order_require_primary_admin` | 0 | ⬜ |
| `PAGE-015.16` | 195–208 | تابع | `food_order_day_visit_subject` | 0 | ⬜ |
| `PAGE-015.17` | 210–221 | تابع | `food_order_log` | 1 | ⬜ |
| `PAGE-015.18` | 223–238 | تابع | `food_order_audit` | 0 | ⬜ |
| `PAGE-015.19` | 240–246 | تابع | `food_order_raw_mode` | 0 | ⬜ |
| `PAGE-015.20` | 248–267 | تابع | `food_order_mode` | 0 | ⬜ |
| `PAGE-015.21` | 269–278 | تابع | `food_order_user_row` | 0 | ⬜ |
| `PAGE-015.22` | 280–286 | تابع | `food_order_full_name` | 0 | ⬜ |
| `PAGE-015.23` | 288–309 | تابع | `food_order_employee_search` | 5 | ⬜ |
| `PAGE-015.24` | 311–335 | تابع | `food_order_report_employee_search` | 5 | ⬜ |
| `PAGE-015.25` | 337–341 | تابع | `food_order_name_key` | 0 | ⬜ |
| `PAGE-015.26` | 343–357 | تابع | `food_order_catalog_has_category` | 0 | ⬜ |
| `PAGE-015.27` | 359–375 | تابع | `food_order_catalog` | 0 | ⬜ |
| `PAGE-015.28` | 377–387 | تابع | `food_order_meal_type` | 0 | ⬜ |
| `PAGE-015.29` | 389–518 | تابع | `food_order_month_status` | 6 | ⬜ |
| `PAGE-015.30` | 520–553 | تابع | `food_order_list_date` | 0 | ⬜ |
| `PAGE-015.31` | 555–611 | تابع | `food_order_list_range` | 4 | ⬜ |
| `PAGE-015.32` | 613–666 | تابع | `food_order_my_orders` | 5 | ⬜ |
| `PAGE-015.33` | 668–703 | تابع | `food_order_report_descriptions` | 0 | ⬜ |
| `PAGE-015.34` | 705–747 | تابع | `food_order_manager_orders` | 0 | ⬜ |
| `PAGE-015.35` | 749–790 | تابع | `food_order_report_rows_range` | 0 | ⬜ |
| `PAGE-015.36` | 792–856 | تابع | `food_order_group_orders` | 0 | ⬜ |
| `PAGE-015.37` | 858–884 | تابع | `food_order_report_summary` | 5 | ⬜ |
| `PAGE-015.38` | 886–948 | تابع | `food_order_statistics_range` | 0 | ⬜ |
| `PAGE-015.39` | 950–1077 | تابع | `food_order_printed_delivery_report` | 5 | ⬜ |
| `PAGE-015.40` | 1079–1130 | تابع | `food_order_export_printed_delivery_report` | 0 | ⬜ |
| `PAGE-015.41` | 1132–1182 | تابع | `food_order_selected_orders_range` | 5 | ⬜ |
| `PAGE-015.42` | 1184–1226 | تابع | `food_order_export_selected_orders_range` | 0 | ⬜ |
| `PAGE-015.43` | 1228–1245 | تابع | `food_order_emergency_window_payload` | 0 | ⬜ |
| `PAGE-015.44` | 1247–1259 | تابع | `food_order_emergency_window_state` | 0 | ⬜ |
| `PAGE-015.45` | 1261–1282 | تابع | `food_order_emergency_window_states` | 5 | ⬜ |
| `PAGE-015.46` | 1284–1337 | تابع | `food_order_emergency_window_grant` | 0 | ⬜ |
| `PAGE-015.47` | 1339–1374 | تابع | `food_order_emergency_window_revoke` | 0 | ⬜ |
| `PAGE-015.48` | 1376–1399 | تابع | `food_order_ensure_orderable_day` | 1 | ⬜ |
| `PAGE-015.49` | 1401–1424 | تابع | `food_order_lock_cancellable_day` | 1 | ⬜ |
| `PAGE-015.50` | 1426–1441 | تابع | `food_order_verify_employee_national_code` | 1 | ⬜ |
| `PAGE-015.51` | 1443–1550 | تابع | `food_order_create_or_change` | 0 | ⬜ |
| `PAGE-015.52` | 1552–1596 | تابع | `food_order_cancel` | 0 | ⬜ |
| `PAGE-015.53` | 1598–1667 | تابع | `food_order_cancel_proxy` | 0 | ⬜ |
| `PAGE-015.54` | 1669–1699 | تابع | `food_order_cancel_request_ensure` | 5 | ⬜ |
| `PAGE-015.55` | 1701–1706 | تابع | `food_order_require_cancel_reviewer` | 0 | ⬜ |
| `PAGE-015.56` | 1708–1711 | تابع | `food_order_cancel_inbox_url` | 0 | ⬜ |
| `PAGE-015.57` | 1713–1730 | تابع | `food_order_user_is_cancel_reviewer` | 0 | ⬜ |
| `PAGE-015.58` | 1732–1747 | تابع | `food_order_cancel_reviewer_ids` | 0 | ⬜ |
| `PAGE-015.59` | 1749–1781 | تابع | `food_order_notify_cancel_reviewers` | 0 | ⬜ |
| `PAGE-015.60` | 1783–1786 | تابع | `food_order_notify_support_managers` | 0 | ⬜ |
| `PAGE-015.61` | 1788–1821 | تابع | `food_order_render_pending_cancels_card` | 0 | ⬜ |
| `PAGE-015.62` | 1823–1848 | تابع | `food_order_pending_cancel_map` | 0 | ⬜ |
| `PAGE-015.63` | 1850–1934 | تابع | `food_order_cancel_request_create` | 4 | ⬜ |
| `PAGE-015.64` | 1936–1969 | تابع | `food_order_cancel_request_list` | 0 | ⬜ |
| `PAGE-015.65` | 1971–2044 | تابع | `food_order_cancel_request_decide` | 4 | ⬜ |
| `PAGE-015.66` | 2046–2128 | تابع | `food_order_catalog_save` | 0 | ⬜ |
| `PAGE-015.67` | 2130–2258 | تابع | `food_order_save_day` | 6 | ⬜ |
| `PAGE-015.68` | 2260–2280 | تابع | `food_order_status_summary` | 0 | ⬜ |
| `PAGE-015.69` | 2282–2290 | تابع | `food_order_api_body` | 0 | ⬜ |
| `PAGE-015.70` | 2292–2301 | تابع | `food_order_send_json` | 0 | ⬜ |
| `PAGE-015.71` | 2303–2310 | تابع | `food_order_safe_error` | 0 | ⬜ |
| `PAGE-015.72` | 2312–2320 | تابع | `food_order_export_safe_value` | 0 | ⬜ |
| `PAGE-015.73` | 2322–2387 | تابع | `food_order_export_manager_orders` | 0 | ⬜ |
| `PAGE-015.74` | 2389–2410 | تابع | `food_order_send_xlsx` | 0 | ⬜ |
| `PAGE-015.75` | 2412–2463 | تابع | `food_order_export_statistics_range` | 5 | ⬜ |
| `PAGE-015.76` | 2465–2469 | تابع | `food_guest_request_roles` | 0 | ⬜ |
| `PAGE-015.77` | 2471–2480 | تابع | `food_guest_request_is_reviewer` | 0 | ⬜ |
| `PAGE-015.78` | 2482–2487 | تابع | `food_guest_request_allowed` | 0 | ⬜ |
| `PAGE-015.79` | 2489–2501 | تابع | `food_guest_request_can_manage` | 0 | ⬜ |
| `PAGE-015.80` | 2503–2512 | تابع | `food_guest_request_change_window_open` | 0 | ⬜ |
| `PAGE-015.81` | 2514–2528 | تابع | `food_guest_request_same_day_cutoff` | 0 | ⬜ |
| `PAGE-015.82` | 2530–2535 | تابع | `food_guest_request_same_day_is_open` | 0 | ⬜ |
| `PAGE-015.83` | 2537–2555 | تابع | `food_guest_request_date_error` | 0 | ⬜ |
| `PAGE-015.84` | 2557–2571 | تابع | `food_guest_requested_total` | 0 | ⬜ |
| `PAGE-015.85` | 2573–2601 | تابع | `food_guest_schema_missing` | 0 | ⬜ |
| `PAGE-015.86` | 2603–2607 | تابع | `food_guest_schema_ready` | 0 | ⬜ |
| `PAGE-015.87` | 2609–2615 | تابع | `food_guest_schema_ensure` | 0 | ⬜ |
| `PAGE-015.88` | 2617–2627 | تابع | `food_guest_deputies` | 0 | ⬜ |
| `PAGE-015.89` | 2629–2678 | تابع | `food_guest_user_deputy_id` | 4 | ⬜ |
| `PAGE-015.90` | 2680–2774 | تابع | `food_guest_request_list` | 0 | ⬜ |
| `PAGE-015.91` | 2776–2824 | تابع | `food_guest_request_create` | 1 | ⬜ |
| `PAGE-015.92` | 2826–2858 | تابع | `food_guest_request_cancel` | 0 | ⬜ |
| `PAGE-015.93` | 2860–2923 | تابع | `food_guest_request_update` | 0 | ⬜ |
| `PAGE-015.94` | 2925–2959 | تابع | `food_guest_request_delete` | 0 | ⬜ |
| `PAGE-015.95` | 2961–3130 | تابع | `food_order_api_handle` | 4 | ⬜ |
| `PAGE-015.96` | 3133–3204 | تابع | `food_order_emergency_handle` | 2 | ⬜ |
| `PAGE-015.97` | 3206–3316 | تابع | `food_order_bulk_snapshot` | 0 | ⬜ |
| `PAGE-015.98` | 3318–3403 | تابع | `food_order_bulk_apply` | 0 | ⬜ |
| `PAGE-015.99` | 3405–3420 | تابع | `food_order_bulk_api_handle` | 2 | ⬜ |
| `PAGE-015.100` | 3422–3650 | تابع | `food_order_menu_api_handle` | 4 | ⬜ |
| `PAGE-015.101` | 3652–3683 | تابع | `food_order_render_page` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-016</b> — <code>food-personnel-link.php</code> (818 خط، 27 آدرس، بازبینی‌شده 0/27)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-016.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `PAGE-016.02` | 20–27 | تابع | `fpl_norm_name` | 0 | ⬜ |
| `PAGE-016.03` | 29–37 | تابع | `fpl_person_name` | 0 | ⬜ |
| `PAGE-016.04` | 39–62 | تابع | `fpl_reference_columns` | 4 | ⬜ |
| `PAGE-016.05` | 64–86 | تابع | `fpl_manual_reference_columns` | 0 | ⬜ |
| `PAGE-016.06` | 88–97 | تابع | `fpl_column_exists` | 0 | ⬜ |
| `PAGE-016.07` | 99–187 | تابع | `fpl_candidates` | 0 | ⬜ |
| `PAGE-016.08` | 189–203 | تابع | `fpl_pair_is_allowed` | 0 | ⬜ |
| `PAGE-016.09` | 205–315 | تابع | `fpl_merge` | 5 | ⬜ |
| `PAGE-016.10` | 317–323 | تابع | `fpl_backup_users` | 4 | ⬜ |
| `PAGE-016.11` | 325–330 | تابع | `fpl_normalize_code` | 0 | ⬜ |
| `PAGE-016.12` | 332–345 | تابع | `fpl_codes_equal` | 0 | ⬜ |
| `PAGE-016.13` | 347–353 | تابع | `fpl_is_local_food_account` | 0 | ⬜ |
| `PAGE-016.14` | 355–364 | تابع | `fpl_food_personnel_code` | 0 | ⬜ |
| `PAGE-016.15` | 366–379 | تابع | `fpl_food_matches_code` | 0 | ⬜ |
| `PAGE-016.16` | 381–401 | تابع | `fpl_code_candidate_rows` | 0 | ⬜ |
| `PAGE-016.17` | 403–425 | تابع | `fpl_food_code_matches` | 0 | ⬜ |
| `PAGE-016.18` | 427–437 | تابع | `fpl_find_food_by_code` | 0 | ⬜ |
| `PAGE-016.19` | 439–461 | تابع | `fpl_select_food_matches` | 0 | ⬜ |
| `PAGE-016.20` | 463–541 | تابع | `fpl_apply` | 0 | ⬜ |
| `PAGE-016.21` | 543–548 | تابع | `fpl_manual_input` | 0 | ⬜ |
| `PAGE-016.22` | 550–661 | تابع | `fpl_render_request` | 2 | ⬜ |
| `PAGE-016.23` | 663–684 | تابع | `fpl_backup_tables` | 0 | ⬜ |
| `PAGE-016.24` | 686–768 | تابع | `fpl_report_rows` | 0 | ⬜ |
| `PAGE-016.25` | 770–790 | تابع | `fpl_similar_food_names` | 0 | ⬜ |
| `PAGE-016.26` | 792–811 | تابع | `fpl_report_xlsx_bytes` | 0 | ⬜ |
| `PAGE-016.27` | 813–817 | تابع | `fpl_report_csv` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-017</b> — <code>food-reconciliation.php</code> (866 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-017.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `PAGE-017.02` | 13–26 | تابع | `food_reconciliation_schema_ensure` | 5 | ⬜ |
| `PAGE-017.03` | 28–72 | تابع | `food_reconciliation_calculate` | 0 | ⬜ |
| `PAGE-017.04` | 74–92 | تابع | `food_reconciliation_count` | 0 | ⬜ |
| `PAGE-017.05` | 94–110 | تابع | `food_reconciliation_month` | 0 | ⬜ |
| `PAGE-017.06` | 112–127 | تابع | `food_reconciliation_month_range` | 0 | ⬜ |
| `PAGE-017.07` | 129–139 | تابع | `food_reconciliation_month_key` | 0 | ⬜ |
| `PAGE-017.08` | 141–153 | تابع | `food_reconciliation_date_range` | 0 | ⬜ |
| `PAGE-017.09` | 155–162 | تابع | `food_reconciliation_meal_filter` | 0 | ⬜ |
| `PAGE-017.10` | 164–185 | تابع | `food_reconciliation_order_counts` | 0 | ⬜ |
| `PAGE-017.11` | 187–285 | تابع | `food_reconciliation_rows` | 0 | ⬜ |
| `PAGE-017.12` | 287–309 | تابع | `food_reconciliation_invoice_rows` | 0 | ⬜ |
| `PAGE-017.13` | 311–404 | تابع | `food_reconciliation_report` | 0 | ⬜ |
| `PAGE-017.14` | 406–413 | تابع | `food_reconciliation_audit` | 0 | ⬜ |
| `PAGE-017.15` | 415–553 | تابع | `food_reconciliation_save` | 1 | ⬜ |
| `PAGE-017.16` | 555–558 | تابع | `food_reconciliation_upload_directory` | 0 | ⬜ |
| `PAGE-017.17` | 560–638 | تابع | `food_reconciliation_validate_file` | 1 | ⬜ |
| `PAGE-017.18` | 640–724 | تابع | `food_reconciliation_upload_invoice` | **8** | ⬜ |
| `PAGE-017.19` | 726–758 | تابع | `food_reconciliation_download_invoice` | 0 | ⬜ |
| `PAGE-017.20` | 760–833 | تابع | `food_reconciliation_export` | 0 | ⬜ |
| `PAGE-017.21` | 835–865 | تابع | `food_reconciliation_api_handle` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-018</b> — <code>food-ticket-auto-receipts.php</code> (352 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-018.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-018.02` | 4–21 | تابع | `food_ticket_auto_receipt_label` | 0 | ⬜ |
| `PAGE-018.03` | 23–35 | تابع | `food_ticket_auto_receipt_date` | 0 | ⬜ |
| `PAGE-018.04` | 37–53 | تابع | `food_ticket_auto_receipt_holiday` | 0 | ⬜ |
| `PAGE-018.05` | 55–69 | تابع | `food_ticket_auto_receipt_table_exists` | 0 | ⬜ |
| `PAGE-018.06` | 71–84 | تابع | `food_ticket_auto_receipt_columns` | 0 | ⬜ |
| `PAGE-018.07` | 86–120 | تابع | `food_ticket_auto_receipt_schema_ready` | 0 | ⬜ |
| `PAGE-018.08` | 122–163 | تابع | `food_ticket_auto_receipt_insert` | 1 | ⬜ |
| `PAGE-018.09` | 165–351 | تابع | `food_ticket_auto_receipts_run` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-019</b> — <code>food-ticket-engine.php</code> (1,102 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-019.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `PAGE-019.02` | 13–20 | تابع | `food_ticket_norm_personnel` | 0 | ⬜ |
| `PAGE-019.03` | 22–52 | تابع | `food_ticket_user_index` | 0 | ⬜ |
| `PAGE-019.04` | 54–84 | تابع | `food_ticket_find_user_strict` | 0 | ⬜ |
| `PAGE-019.05` | 86–131 | تابع | `food_ticket_engine_delete_source` | 0 | ⬜ |
| `PAGE-019.06` | 133–142 | تابع | `food_ticket_build_order_map` | 0 | ⬜ |
| `PAGE-019.07` | 144–154 | تابع | `food_ticket_order_map_lazy` | 0 | ⬜ |
| `PAGE-019.08` | 156–178 | تابع | `food_ticket_card_map_ensure` | 5 | ⬜ |
| `PAGE-019.09` | 180–200 | تابع | `food_ticket_card_map_load` | 0 | ⬜ |
| `PAGE-019.10` | 202–217 | تابع | `food_ticket_card_map_lookup` | 0 | ⬜ |
| `PAGE-019.11` | 219–237 | تابع | `food_ticket_card_map_learn` | 1 | ⬜ |
| `PAGE-019.12` | 239–270 | تابع | `food_ticket_process_print_queue_locked` | 3 | ⬜ |
| `PAGE-019.13` | 272–324 | تابع | `food_ticket_find_twin_event` | 0 | ⬜ |
| `PAGE-019.14` | 326–347 | تابع | `food_ticket_sort_punches` | 0 | ⬜ |
| `PAGE-019.15` | 348–352 | ناحیه | `بلوک رویه‌ای 348-352` | 1 | ⬜ |
| `PAGE-019.16` | 353–370 | تابع | `food_guest_cap_mode` | 0 | ⬜ |
| `PAGE-019.17` | 372–380 | تابع | `food_guest_cap_clock` | 0 | ⬜ |
| `PAGE-019.18` | 382–387 | تابع | `food_guest_cap_start` | 0 | ⬜ |
| `PAGE-019.19` | 389–394 | تابع | `food_guest_cap_end` | 0 | ⬜ |
| `PAGE-019.20` | 396–401 | تابع | `food_guest_cap_cutoff` | 0 | ⬜ |
| `PAGE-019.21` | 403–411 | تابع | `food_guest_variable_cap_from_counts` | 0 | ⬜ |
| `PAGE-019.22` | 413–426 | تابع | `food_guest_variable_cap_at` | 0 | ⬜ |
| `PAGE-019.23` | 428–448 | تابع | `food_ticket_guest_variable_cap` | 0 | ⬜ |
| `PAGE-019.24` | 449–454 | ناحیه | `بلوک رویه‌ای 449-454` | 0 | ⬜ |
| `PAGE-019.25` | 455–461 | تابع | `food_ticket_guest_issued_total` | 0 | ⬜ |
| `PAGE-019.26` | 463–471 | تابع | `food_ticket_guest_cap_reached` | 0 | ⬜ |
| `PAGE-019.27` | 473–651 | تابع | `food_ticket_decide_punch` | 7 | ⬜ |
| `PAGE-019.28` | 653–769 | تابع | `food_ticket_decide_for_user` | 1 | ⬜ |
| `PAGE-019.29` | 771–838 | تابع | `food_ticket_read_source_rows_today` | 0 | ⬜ |
| `PAGE-019.30` | 840–1101 | تابع | `food_ticket_process_batch_v2` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-020</b> — <code>food-ticket-env.php</code> (267 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-020.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `PAGE-020.02` | 25–59 | تابع | `food_ticket_env_value` | 0 | ⬜ |
| `PAGE-020.03` | 61–65 | تابع | `food_ticket_env_source_table` | 0 | ⬜ |
| `PAGE-020.04` | 68–72 | تابع | `food_ticket_legacy_key` | 0 | ⬜ |
| `PAGE-020.05` | 74–80 | تابع | `food_ticket_legacy_display_map` | 0 | ⬜ |
| `PAGE-020.06` | 82–92 | تابع | `food_ticket_legacy_brand_tokens` | 0 | ⬜ |
| `PAGE-020.07` | 94–100 | تابع | `food_ticket_legacy_brand_words` | 0 | ⬜ |
| `PAGE-020.08` | 102–115 | تابع | `food_ticket_is_legacy_brand_name` | 0 | ⬜ |
| `PAGE-020.09` | 117–121 | تابع | `food_ticket_route_is_test_attendance` | 0 | ⬜ |
| `PAGE-020.10` | 123–131 | تابع | `food_ticket_printer_profile` | 0 | ⬜ |
| `PAGE-020.11` | 133–175 | تابع | `food_ticket_db_column` | 0 | ⬜ |
| `PAGE-020.12` | 177–207 | تابع | `food_ticket_config_has_column` | 0 | ⬜ |
| `PAGE-020.13` | 209–227 | تابع | `food_ticket_config_alias_row` | 0 | ⬜ |
| `PAGE-020.14` | 229–241 | تابع | `food_ticket_setting_password_enc` | 0 | ⬜ |
| `PAGE-020.15` | 243–256 | تابع | `food_ticket_source_table` | 0 | ⬜ |
| `PAGE-020.16` | 258–266 | تابع | `food_ticket_env_legacy_paths` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-021</b> — <code>food-ticket-groups.php</code> (2,971 خط، 70 آدرس، بازبینی‌شده 0/70)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-021.01` | 1–30 | ناحیه | `بلوک رویه‌ای 1-30` | 0 | ⬜ |
| `PAGE-021.02` | 31–60 | تابع | `food_ticket_audit` | 0 | ⬜ |
| `PAGE-021.03` | 62–157 | تابع | `food_ticket_groups_ensure` | **10** | ⬜ |
| `PAGE-021.04` | 159–219 | تابع | `food_ticket_groups_migrate` | 4 | ⬜ |
| `PAGE-021.05` | 221–222 | ناحیه | `غیبت روزانه` | 0 | ⬜ |
| `PAGE-021.06` | 223–249 | تابع | `food_ticket_absence_ensure` | 5 | ⬜ |
| `PAGE-021.07` | 251–308 | تابع | `food_ticket_manual_attendance_reconcile_range` | 1 | ⬜ |
| `PAGE-021.08` | 310–313 | تابع | `food_ticket_manual_attendance_reconcile_date` | 0 | ⬜ |
| `PAGE-021.09` | 315–342 | تابع | `food_ticket_absence_load` | 0 | ⬜ |
| `PAGE-021.10` | 344–362 | تابع | `food_ticket_group_absence_locked` | 0 | ⬜ |
| `PAGE-021.11` | 364–436 | تابع | `food_ticket_absence_set` | 1 | ⬜ |
| `PAGE-021.12` | 438–484 | تابع | `food_ticket_absence_clear_group` | 1 | ⬜ |
| `PAGE-021.13` | 486–509 | تابع | `food_ticket_absence_held_count` | 5 | ⬜ |
| `PAGE-021.14` | 511–517 | تابع | `food_ticket_can_override_absence` | 0 | ⬜ |
| `PAGE-021.15` | 519–520 | ناحیه | `گروه‌ها: بارگذاری و شناسایی` | 0 | ⬜ |
| `PAGE-021.16` | 521–550 | تابع | `food_ticket_groups_load` | 0 | ⬜ |
| `PAGE-021.17` | 552–568 | تابع | `food_ticket_group_find_by_uid` | 0 | ⬜ |
| `PAGE-021.18` | 570–588 | تابع | `food_ticket_group_find_by_card` | 0 | ⬜ |
| `PAGE-021.19` | 590–608 | تابع | `food_ticket_group_resolve` | 0 | ⬜ |
| `PAGE-021.20` | 610–622 | تابع | `food_ticket_group_find_by_card_any` | 0 | ⬜ |
| `PAGE-021.21` | 624–634 | تابع | `food_ticket_group_date_ok` | 0 | ⬜ |
| `PAGE-021.22` | 636–639 | تابع | `food_ticket_group_member_key` | 0 | ⬜ |
| `PAGE-021.23` | 641–659 | تابع | `food_ticket_group_event_ids_for_run` | 0 | ⬜ |
| `PAGE-021.24` | 661–662 | ناحیه | `محاسبهٔ واجد شرایط (هستهٔ منطق، بدون DB)` | 0 | ⬜ |
| `PAGE-021.25` | 663–735 | تابع | `food_ticket_group_plan` | 0 | ⬜ |
| `PAGE-021.26` | 737–755 | تابع | `food_ticket_group_members` | 0 | ⬜ |
| `PAGE-021.27` | 757–772 | تابع | `food_ticket_user_group_id` | 0 | ⬜ |
| `PAGE-021.28` | 774–795 | تابع | `food_ticket_day_visit_users` | 0 | ⬜ |
| `PAGE-021.29` | 797–835 | تابع | `food_ticket_group_members_for_date` | 0 | ⬜ |
| `PAGE-021.30` | 837–855 | تابع | `food_ticket_day_visit_row` | 0 | ⬜ |
| `PAGE-021.31` | 857–882 | تابع | `food_ticket_visit_change_hhmm` | 0 | ⬜ |
| `PAGE-021.32` | 884–894 | تابع | `food_ticket_visit_change_now` | 0 | ⬜ |
| `PAGE-021.33` | 896–905 | تابع | `food_ticket_visit_change_is_open` | 0 | ⬜ |
| `PAGE-021.34` | 907–915 | تابع | `food_ticket_visit_change_guard` | 0 | ⬜ |
| `PAGE-021.35` | 917–923 | تابع | `food_ticket_can_cancel_day_visit` | 0 | ⬜ |
| `PAGE-021.36` | 925–991 | تابع | `food_ticket_day_visit_state` | 0 | ⬜ |
| `PAGE-021.37` | 993–1114 | تابع | `food_ticket_day_visit_save` | 2 | ⬜ |
| `PAGE-021.38` | 1116–1175 | تابع | `food_ticket_day_visit_clear` | 1 | ⬜ |
| `PAGE-021.39` | 1177–1193 | تابع | `food_ticket_api_cancel_day_visit` | 0 | ⬜ |
| `PAGE-021.40` | 1195–1226 | تابع | `food_ticket_group_ticketed_keys` | 5 | ⬜ |
| `PAGE-021.41` | 1228–1248 | تابع | `food_ticket_group_order_map` | 0 | ⬜ |
| `PAGE-021.42` | 1250–1264 | تابع | `food_ticket_group_food_lookup` | 0 | ⬜ |
| `PAGE-021.43` | 1266–1304 | تابع | `food_ticket_group_overview` | 0 | ⬜ |
| `PAGE-021.44` | 1306–1307 | ناحیه | `پردازش تردد نماینده` | 0 | ⬜ |
| `PAGE-021.45` | 1308–1555 | تابع | `food_ticket_group_try_process` | 2 | ⬜ |
| `PAGE-021.46` | 1557–1620 | تابع | `food_ticket_hold_ticket_for_absence` | 6 | ⬜ |
| `PAGE-021.47` | 1622–1671 | تابع | `food_ticket_release_held_ticket` | 6 | ⬜ |
| `PAGE-021.48` | 1673–1700 | تابع | `food_ticket_events_ensure_hold_status` | **8** | ⬜ |
| `PAGE-021.49` | 1702–1703 | ناحیه | `تاریخچهٔ L_UID نماینده‌ها و تردد با شناسهٔ ناشناخت` | 0 | ⬜ |
| `PAGE-021.50` | 1704–1718 | تابع | `food_ticket_group_uid_history_record` | 1 | ⬜ |
| `PAGE-021.51` | 1720–1735 | تابع | `food_ticket_group_uid_history_has` | 0 | ⬜ |
| `PAGE-021.52` | 1737–1802 | تابع | `food_ticket_group_try_unknown` | **10** | ⬜ |
| `PAGE-021.53` | 1804–1806 | ناحیه | `API مدیریت گروه‌ها` | 0 | ⬜ |
| `PAGE-021.54` | 1807–1811 | تابع | `food_ticket_manual_attendance_today` | 0 | ⬜ |
| `PAGE-021.55` | 1813–1831 | تابع | `food_ticket_manual_attendance_date` | 0 | ⬜ |
| `PAGE-021.56` | 1833–1844 | تابع | `food_ticket_manual_attendance_guest_row` | 0 | ⬜ |
| `PAGE-021.57` | 1846–1929 | تابع | `food_ticket_api_manual_attendance` | 0 | ⬜ |
| `PAGE-021.58` | 1931–1981 | تابع | `food_ticket_manual_attendance_clear_absence` | 1 | ⬜ |
| `PAGE-021.59` | 1983–2228 | تابع | `food_ticket_api_manual_attendance_save` | 2 | ⬜ |
| `PAGE-021.60` | 2230–2318 | تابع | `food_ticket_api_manual_attendance_save_guest` | 0 | ⬜ |
| `PAGE-021.61` | 2320–2396 | تابع | `food_ticket_api_manual_attendance_save_many` | **9** | ⬜ |
| `PAGE-021.62` | 2398–2496 | تابع | `food_ticket_api_groups` | 0 | ⬜ |
| `PAGE-021.63` | 2498–2727 | تابع | `food_ticket_api_save_group` | 5 | ⬜ |
| `PAGE-021.64` | 2728–2732 | ناحیه | `بلوک رویه‌ای 2728-2732` | 0 | ⬜ |
| `PAGE-021.65` | 2733–2751 | تابع | `food_ticket_jalali_label` | 0 | ⬜ |
| `PAGE-021.66` | 2753–2826 | تابع | `food_ticket_api_absence_status` | 0 | ⬜ |
| `PAGE-021.67` | 2828–2893 | تابع | `food_ticket_api_absence_save` | 0 | ⬜ |
| `PAGE-021.68` | 2895–2911 | تابع | `food_ticket_api_absence_clear` | 0 | ⬜ |
| `PAGE-021.69` | 2913–2939 | تابع | `food_ticket_api_delivery` | 0 | ⬜ |
| `PAGE-021.70` | 2941–2970 | تابع | `food_ticket_api_delete_group` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-022</b> — <code>food-ticket-import.php</code> (574 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-022.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `PAGE-022.02` | 18–28 | تابع | `food_ticket_import_aliases` | 0 | ⬜ |
| `PAGE-022.03` | 30–41 | تابع | `food_ticket_import_normalize` | 0 | ⬜ |
| `PAGE-022.04` | 43–49 | تابع | `food_ticket_import_key` | 0 | ⬜ |
| `PAGE-022.05` | 51–55 | تابع | `food_ticket_import_digits` | 0 | ⬜ |
| `PAGE-022.06` | 57–69 | تابع | `food_ticket_import_column_index` | 0 | ⬜ |
| `PAGE-022.07` | 71–96 | تابع | `food_ticket_import_shared_strings` | 1 | ⬜ |
| `PAGE-022.08` | 98–171 | تابع | `food_ticket_import_read_xlsx` | 1 | ⬜ |
| `PAGE-022.09` | 173–204 | تابع | `food_ticket_import_csv_delimiter_counts` | 0 | ⬜ |
| `PAGE-022.10` | 206–242 | تابع | `food_ticket_import_read_csv` | 2 | ⬜ |
| `PAGE-022.11` | 244–276 | تابع | `food_ticket_import_map_columns` | 0 | ⬜ |
| `PAGE-022.12` | 278–321 | تابع | `food_ticket_import_parse_file` | 0 | ⬜ |
| `PAGE-022.13` | 323–354 | تابع | `food_ticket_import_validate_row` | 0 | ⬜ |
| `PAGE-022.14` | 356–476 | تابع | `food_ticket_import_apply` | 2 | ⬜ |
| `PAGE-022.15` | 478–508 | تابع | `food_ticket_import_summary` | 0 | ⬜ |
| `PAGE-022.16` | 510–573 | تابع | `food_ticket_employees_import_handle` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-023</b> — <code>food-ticket-netprint.php</code> (1,299 خط، 35 آدرس، بازبینی‌شده 0/35)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-023.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `PAGE-023.02` | 17–75 | تابع | `food_ticket_np_settings` | 1 | ⬜ |
| `PAGE-023.03` | 77–95 | تابع | `food_ticket_np_find_font` | 0 | ⬜ |
| `PAGE-023.04` | 97–98 | ناحیه | `شکل‌دهی حروف فارسی/عربی` | 0 | ⬜ |
| `PAGE-023.05` | 99–137 | تابع | `food_ticket_np_tables` | 0 | ⬜ |
| `PAGE-023.06` | 139–152 | تابع | `food_ticket_np_codepoints` | 0 | ⬜ |
| `PAGE-023.07` | 154–204 | تابع | `food_ticket_np_shape` | 0 | ⬜ |
| `PAGE-023.08` | 206–222 | تابع | `food_ticket_np_class` | 0 | ⬜ |
| `PAGE-023.09` | 224–288 | تابع | `food_ticket_np_visual` | 0 | ⬜ |
| `PAGE-023.10` | 290–291 | ناحیه | `رسم تصویر فیش` | 0 | ⬜ |
| `PAGE-023.11` | 292–301 | تابع | `food_ticket_np_measure` | 0 | ⬜ |
| `PAGE-023.12` | 303–417 | تابع | `food_ticket_np_render` | 0 | ⬜ |
| `PAGE-023.13` | 419–431 | تابع | `food_ticket_print_trim_info` | 0 | ⬜ |
| `PAGE-023.14` | 433–523 | تابع | `food_ticket_np_trim_edges` | 0 | ⬜ |
| `PAGE-023.15` | 525–535 | تابع | `food_ticket_np_trim_top` | 0 | ⬜ |
| `PAGE-023.16` | 537–542 | تابع | `food_ticket_np_json_file` | 0 | ⬜ |
| `PAGE-023.17` | 544–602 | تابع | `food_ticket_np_write_json` | 3 | ⬜ |
| `PAGE-023.18` | 604–675 | تابع | `food_ticket_np_align_state` | 0 | ⬜ |
| `PAGE-023.19` | 677–712 | تابع | `food_ticket_np_reverse_feed` | 0 | ⬜ |
| `PAGE-023.20` | 714–722 | تابع | `food_ticket_print_align_info` | 0 | ⬜ |
| `PAGE-023.21` | 724–834 | تابع | `food_ticket_np_escpos` | 0 | ⬜ |
| `PAGE-023.22` | 836–854 | تابع | `food_ticket_np_payload` | 2 | ⬜ |
| `PAGE-023.23` | 856–896 | تابع | `food_ticket_np_send` | 3 | ⬜ |
| `PAGE-023.24` | 898–899 | ناحیه | `چاپ شبکه با چیدمان قالب فیش (همان ظاهر چاپ ویندوز)` | 0 | ⬜ |
| `PAGE-023.25` | 900–933 | تابع | `food_ticket_np_find_font_for` | 0 | ⬜ |
| `PAGE-023.26` | 935–945 | تابع | `food_ticket_np_metrics` | 0 | ⬜ |
| `PAGE-023.27` | 947–978 | تابع | `food_ticket_np_wrap` | 0 | ⬜ |
| `PAGE-023.28` | 980–999 | تابع | `food_ticket_np_layout` | 0 | ⬜ |
| `PAGE-023.29` | 1001–1143 | تابع | `food_ticket_np_render_layout` | 2 | ⬜ |
| `PAGE-023.30` | 1145–1188 | تابع | `food_ticket_np_target` | 4 | ⬜ |
| `PAGE-023.31` | 1190–1235 | تابع | `food_ticket_np_calibration_image` | 2 | ⬜ |
| `PAGE-023.32` | 1237–1266 | تابع | `food_ticket_np_calibration_print` | 2 | ⬜ |
| `PAGE-023.33` | 1268–1283 | تابع | `food_ticket_np_image_payload` | 2 | ⬜ |
| `PAGE-023.34` | 1285–1291 | تابع | `food_ticket_np_send_event` | 0 | ⬜ |
| `PAGE-023.35` | 1293–1298 | تابع | `food_ticket_np_send_text` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-024</b> — <code>food-ticket-order-source.php</code> (116 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-024.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-024.02` | 4–52 | تابع | `food_order_internal_map` | 0 | ⬜ |
| `PAGE-024.03` | 54–71 | تابع | `food_order_internal_food_for_employee` | 0 | ⬜ |
| `PAGE-024.04` | 73–115 | تابع | `food_order_internal_items` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-025</b> — <code>food-ticket-runtime.php</code> (1,368 خط، 35 آدرس، بازبینی‌شده 0/35)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-025.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-025.02` | 4–24 | تابع | `food_ticket_setting` | 0 | ⬜ |
| `PAGE-025.03` | 26–32 | تابع | `food_ticket_set_setting` | 6 | ⬜ |
| `PAGE-025.04` | 34–47 | تابع | `food_ticket_meal_windows` | 0 | ⬜ |
| `PAGE-025.05` | 49–64 | تابع | `food_ticket_active_meal` | 0 | ⬜ |
| `PAGE-025.06` | 66–94 | تابع | `food_ticket_template_default` | 0 | ⬜ |
| `PAGE-025.07` | 96–110 | تابع | `food_ticket_template` | 0 | ⬜ |
| `PAGE-025.08` | 112–124 | تابع | `food_ticket_save_template` | 1 | ⬜ |
| `PAGE-025.09` | 126–210 | تابع | `food_ticket_render_from_template` | 0 | ⬜ |
| `PAGE-025.10` | 212–250 | تابع | `food_ticket_print_log` | 1 | ⬜ |
| `PAGE-025.11` | 252–255 | تابع | `food_ticket_new_job_id` | 0 | ⬜ |
| `PAGE-025.12` | 257–290 | تابع | `food_ticket_enqueue_print` | 1 | ⬜ |
| `PAGE-025.13` | 292–295 | تابع | `food_ticket_can_delete_source_after_print` | 0 | ⬜ |
| `PAGE-025.14` | 297–328 | تابع | `food_ticket_prepare_queued_food` | 0 | ⬜ |
| `PAGE-025.15` | 330–343 | تابع | `food_ticket_apply_queued_food` | 1 | ⬜ |
| `PAGE-025.16` | 345–353 | تابع | `food_ticket_verify_queued_food` | 0 | ⬜ |
| `PAGE-025.17` | 355–537 | تابع | `food_ticket_print_group_run` | 7 | ⬜ |
| `PAGE-025.18` | 539–693 | تابع | `food_ticket_process_print_queue` | **11** | ⬜ |
| `PAGE-025.19` | 695–702 | تابع | `food_ticket_fa_digits` | 0 | ⬜ |
| `PAGE-025.20` | 704–777 | تابع | `food_ticket_graphic_rows` | 0 | ⬜ |
| `PAGE-025.21` | 779–813 | تابع | `food_ticket_template_logo_base64` | 2 | ⬜ |
| `PAGE-025.22` | 815–1013 | تابع | `food_ticket_send_windows_graphic` | **13** | ⬜ |
| `PAGE-025.23` | 1015–1069 | تابع | `food_ticket_send_raw_payload` | 0 | ⬜ |
| `PAGE-025.24` | 1070–1075 | ناحیه | `بلوک رویه‌ای 1070-1075` | 0 | ⬜ |
| `PAGE-025.25` | 1076–1128 | تابع | `food_ticket_table_has_column` | 4 | ⬜ |
| `PAGE-025.26` | 1130–1142 | تابع | `food_ticket_delivery_supported` | 0 | ⬜ |
| `PAGE-025.27` | 1144–1171 | تابع | `food_ticket_events_ensure_delivery` | 4 | ⬜ |
| `PAGE-025.28` | 1173–1191 | تابع | `food_ticket_source_delete_supported` | 0 | ⬜ |
| `PAGE-025.29` | 1193–1225 | تابع | `food_ticket_events_ensure_source_delete` | **14** | ⬜ |
| `PAGE-025.30` | 1226–1227 | ناحیه | `بلوک رویه‌ای 1226-1227` | 0 | ⬜ |
| `PAGE-025.31` | 1228–1241 | تابع | `food_ticket_mark_delivered_on_print` | 1 | ⬜ |
| `PAGE-025.32` | 1243–1279 | تابع | `food_ticket_deliver_event` | 5 | ⬜ |
| `PAGE-025.33` | 1281–1312 | تابع | `food_ticket_undeliver_event` | 5 | ⬜ |
| `PAGE-025.34` | 1314–1345 | تابع | `food_ticket_status_counts` | 5 | ⬜ |
| `PAGE-025.35` | 1347–1367 | تابع | `food_ticket_self_test_logic` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-026</b> — <code>food-ticket-templates.php</code> (2,364 خط، 64 آدرس، بازبینی‌شده 0/64)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-026.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `PAGE-026.02` | 27–28 | ناحیه | `کاتالوگ فیلدها و فونت‌ها` | 0 | ⬜ |
| `PAGE-026.03` | 29–54 | تابع | `food_ticket_tpl_catalog` | 0 | ⬜ |
| `PAGE-026.04` | 56–69 | تابع | `food_ticket_tpl_font_files` | 0 | ⬜ |
| `PAGE-026.05` | 71–110 | تابع | `food_ticket_tpl_find_font` | 0 | ⬜ |
| `PAGE-026.06` | 112–113 | ناحیه | `دیتابیس` | 0 | ⬜ |
| `PAGE-026.07` | 114–143 | تابع | `food_ticket_tpl_ensure` | **9** | ⬜ |
| `PAGE-026.08` | 145–151 | تابع | `food_ticket_tpl_num` | 0 | ⬜ |
| `PAGE-026.09` | 153–222 | تابع | `food_ticket_tpl_normalize` | 0 | ⬜ |
| `PAGE-026.10` | 224–271 | تابع | `food_ticket_tpl_default_template` | 0 | ⬜ |
| `PAGE-026.11` | 273–285 | تابع | `food_ticket_tpl_default_row` | 0 | ⬜ |
| `PAGE-026.12` | 287–300 | تابع | `food_ticket_tpl_row_decode` | 0 | ⬜ |
| `PAGE-026.13` | 302–310 | تابع | `food_ticket_tpl_list` | 0 | ⬜ |
| `PAGE-026.14` | 312–322 | تابع | `food_ticket_tpl_get` | 0 | ⬜ |
| `PAGE-026.15` | 324–332 | تابع | `food_ticket_tpl_force` | 0 | ⬜ |
| `PAGE-026.16` | 334–362 | تابع | `food_ticket_tpl_active` | 0 | ⬜ |
| `PAGE-026.17` | 364–375 | تابع | `food_ticket_tpl_seed_if_empty` | 0 | ⬜ |
| `PAGE-026.18` | 377–429 | تابع | `food_ticket_tpl_save` | 4 | ⬜ |
| `PAGE-026.19` | 431–448 | تابع | `food_ticket_tpl_activate` | 4 | ⬜ |
| `PAGE-026.20` | 450–476 | تابع | `food_ticket_tpl_delete` | 0 | ⬜ |
| `PAGE-026.21` | 478–479 | ناحیه | `نام و لوگوی سامانه` | 0 | ⬜ |
| `PAGE-026.22` | 480–491 | تابع | `food_ticket_tpl_setting` | 0 | ⬜ |
| `PAGE-026.23` | 493–500 | تابع | `food_ticket_tpl_system_name` | 0 | ⬜ |
| `PAGE-026.24` | 502–516 | تابع | `food_ticket_tpl_logo_setting` | 0 | ⬜ |
| `PAGE-026.25` | 518–552 | تابع | `food_ticket_tpl_logo_bytes` | 2 | ⬜ |
| `PAGE-026.26` | 554–555 | ناحیه | `مقدار فیلدها` | 0 | ⬜ |
| `PAGE-026.27` | 556–559 | تابع | `food_ticket_tpl_fa` | 0 | ⬜ |
| `PAGE-026.28` | 561–588 | تابع | `food_ticket_tpl_department` | 4 | ⬜ |
| `PAGE-026.29` | 590–637 | تابع | `food_ticket_tpl_values` | 0 | ⬜ |
| `PAGE-026.30` | 639–646 | تابع | `food_ticket_tpl_qr_payload` | 0 | ⬜ |
| `PAGE-026.31` | 648–649 | ناحیه | `QR Code (بایت‌مد، سطح M، نسخهٔ ۱ تا ۱۰)` | 0 | ⬜ |
| `PAGE-026.32` | 650–997 | تابع | `food_ticket_tpl_qr_matrix` | 3 | ⬜ |
| `PAGE-026.33` | 999–1000 | ناحیه | `مدل چاپ (مستقل از نوع چاپگر)` | 0 | ⬜ |
| `PAGE-026.34` | 1001–1016 | تابع | `food_ticket_tpl_text_height_mm` | 0 | ⬜ |
| `PAGE-026.35` | 1018–1301 | تابع | `food_ticket_tpl_model` | 7 | ⬜ |
| `PAGE-026.36` | 1303–1322 | تابع | `food_ticket_tpl_first_ink_mm` | 0 | ⬜ |
| `PAGE-026.37` | 1324–1344 | تابع | `food_ticket_tpl_last_ink_mm` | 0 | ⬜ |
| `PAGE-026.38` | 1346–1383 | تابع | `food_ticket_tpl_fit_bottom` | 0 | ⬜ |
| `PAGE-026.39` | 1385–1419 | تابع | `food_ticket_tpl_trim_top` | 0 | ⬜ |
| `PAGE-026.40` | 1421–1429 | تابع | `food_ticket_tpl_last_info` | 0 | ⬜ |
| `PAGE-026.41` | 1431–1435 | تابع | `food_ticket_tpl_log_suffix` | 0 | ⬜ |
| `PAGE-026.42` | 1437–1438 | ناحیه | `مسیر ۲: چاپ مستقیم شبکه (GD + ESC/POS)` | 0 | ⬜ |
| `PAGE-026.43` | 1439–1468 | تابع | `food_ticket_tpl_net_cfg` | 1 | ⬜ |
| `PAGE-026.44` | 1470–1482 | تابع | `food_ticket_tpl_head_dots` | 0 | ⬜ |
| `PAGE-026.45` | 1484–1525 | تابع | `food_ticket_tpl_dither` | 0 | ⬜ |
| `PAGE-026.46` | 1527–1686 | تابع | `food_ticket_tpl_render_gd` | 2 | ⬜ |
| `PAGE-026.47` | 1688–1701 | تابع | `food_ticket_tpl_binarize` | 0 | ⬜ |
| `PAGE-026.48` | 1703–1736 | تابع | `food_ticket_tpl_network_payload` | 0 | ⬜ |
| `PAGE-026.49` | 1738–1743 | تابع | `food_ticket_tpl_print_network` | 0 | ⬜ |
| `PAGE-026.50` | 1745–1764 | تابع | `food_ticket_tpl_preview` | 0 | ⬜ |
| `PAGE-026.51` | 1766–1767 | ناحیه | `مسیر ۱: صف چاپ ویندوز (GDI+ / PrintDocument)` | 0 | ⬜ |
| `PAGE-026.52` | 1768–1984 | تابع | `food_ticket_tpl_windows_script` | 6 | ⬜ |
| `PAGE-026.53` | 1986–2046 | تابع | `food_ticket_tpl_print_windows` | 7 | ⬜ |
| `PAGE-026.54` | 2048–2049 | ناحیه | `API طراح (food_api=templates*)` | 0 | ⬜ |
| `PAGE-026.55` | 2050–2057 | تابع | `food_ticket_tpl_asset_url` | 0 | ⬜ |
| `PAGE-026.56` | 2059–2071 | تابع | `food_ticket_tpl_brand_payload` | 0 | ⬜ |
| `PAGE-026.57` | 2073–2138 | تابع | `food_ticket_tpl_group_ensure_title` | 0 | ⬜ |
| `PAGE-026.58` | 2140–2162 | تابع | `food_ticket_tpl_group_template` | 0 | ⬜ |
| `PAGE-026.59` | 2164–2179 | تابع | `food_ticket_tpl_list_payload` | 0 | ⬜ |
| `PAGE-026.60` | 2181–2188 | تابع | `food_ticket_group_print_mode` | 0 | ⬜ |
| `PAGE-026.61` | 2190–2208 | تابع | `food_ticket_tpl_sample_group_event` | 0 | ⬜ |
| `PAGE-026.62` | 2210–2225 | تابع | `food_ticket_tpl_sample_event` | 0 | ⬜ |
| `PAGE-026.63` | 2227–2267 | تابع | `food_ticket_tpl_handle_logo_upload` | **9** | ⬜ |
| `PAGE-026.64` | 2269–2363 | تابع | `food_ticket_templates_api` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-027</b> — <code>food-ticket-web-host.php</code> (201 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-027.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `PAGE-027.02` | 21–44 | تابع | `food_ticket_web_host_bootstrap` | 0 | ⬜ |
| `PAGE-027.03` | 46–200 | تابع | `food_ticket_web_host_html` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-028</b> — <code>food-ticket-window.php</code> (336 خط، 19 آدرس، بازبینی‌شده 0/19)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-028.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `PAGE-028.02` | 20–34 | تابع | `food_ticket_window_timezone` | 0 | ⬜ |
| `PAGE-028.03` | 35–37 | ناحیه | `بلوک رویه‌ای 35-37` | 0 | ⬜ |
| `PAGE-028.04` | 38–52 | تابع | `food_ticket_window_defaults` | 0 | ⬜ |
| `PAGE-028.05` | 53–55 | ناحیه | `بلوک رویه‌ای 53-55` | 0 | ⬜ |
| `PAGE-028.06` | 56–79 | تابع | `food_ticket_window_days` | 0 | ⬜ |
| `PAGE-028.07` | 80–82 | ناحیه | `بلوک رویه‌ای 80-82` | 0 | ⬜ |
| `PAGE-028.08` | 83–94 | تابع | `food_ticket_window_time` | 0 | ⬜ |
| `PAGE-028.09` | 95–97 | ناحیه | `بلوک رویه‌ای 95-97` | 0 | ⬜ |
| `PAGE-028.10` | 98–129 | تابع | `food_ticket_window_is_holiday` | 0 | ⬜ |
| `PAGE-028.11` | 130–132 | ناحیه | `بلوک رویه‌ای 130-132` | 0 | ⬜ |
| `PAGE-028.12` | 133–140 | تابع | `food_ticket_window_day_is_active` | 0 | ⬜ |
| `PAGE-028.13` | 141–143 | ناحیه | `بلوک رویه‌ای 141-143` | 0 | ⬜ |
| `PAGE-028.14` | 144–227 | تابع | `food_ticket_window_state` | 0 | ⬜ |
| `PAGE-028.15` | 228–230 | ناحیه | `بلوک رویه‌ای 228-230` | 0 | ⬜ |
| `PAGE-028.16` | 231–305 | تابع | `food_ticket_window_save` | 1 | ⬜ |
| `PAGE-028.17` | 306–308 | ناحیه | `بلوک رویه‌ای 306-308` | 0 | ⬜ |
| `PAGE-028.18` | 309–334 | تابع | `food_ticket_window_next_open` | 0 | ⬜ |
| `PAGE-028.19` | 335–336 | ناحیه | `بلوک رویه‌ای 335-336` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-029</b> — <code>food-ticket.php</code> (5,678 خط، 132 آدرس، بازبینی‌شده 0/132)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-029.01` | 1–8 | ناحیه | `بلوک رویه‌ای 1-8` | 0 | ⬜ |
| `PAGE-029.02` | 9–9 | ناحیه | `پنجرهٔ کاری باید پیش از food_ticket_config() در دس` | 0 | ⬜ |
| `PAGE-029.03` | 10–54 | ناحیه | `به آرایهٔ تنظیمات اضافه شوند (مهاجرت ۰۳۱).` | 0 | ⬜ |
| `PAGE-029.04` | 55–58 | تابع | `food_ticket_panel_version` | 0 | ⬜ |
| `PAGE-029.05` | 60–63 | تابع | `food_ticket_is_primary_admin` | 0 | ⬜ |
| `PAGE-029.06` | 65–68 | تابع | `food_ticket_is_support_manager` | 0 | ⬜ |
| `PAGE-029.07` | 70–76 | تابع | `food_ticket_is_allowed` | 0 | ⬜ |
| `PAGE-029.08` | 78–117 | تابع | `food_ticket_route_edit_permission` | 1 | ⬜ |
| `PAGE-029.09` | 119–123 | تابع | `food_ticket_route_edit_always` | 1 | ⬜ |
| `PAGE-029.10` | 125–266 | تابع | `food_ticket_route_allowed` | 2 | ⬜ |
| `PAGE-029.11` | 268–276 | تابع | `require_food_ticket_access` | 0 | ⬜ |
| `PAGE-029.12` | 278–289 | تابع | `food_ticket_web_url` | 0 | ⬜ |
| `PAGE-029.13` | 291–299 | تابع | `food_ticket_windows_service_base_url` | 0 | ⬜ |
| `PAGE-029.14` | 301–314 | تابع | `food_ticket_sso_token` | 0 | ⬜ |
| `PAGE-029.15` | 316–338 | تابع | `food_ticket_sso_consume_jti` | 7 | ⬜ |
| `PAGE-029.16` | 340–351 | تابع | `food_ticket_main_url` | 0 | ⬜ |
| `PAGE-029.17` | 353–370 | تابع | `food_ticket_brand_params` | 0 | ⬜ |
| `PAGE-029.18` | 372–387 | تابع | `food_ticket_resolve_path` | 0 | ⬜ |
| `PAGE-029.19` | 389–399 | تابع | `food_ticket_attendance_default_path` | 0 | ⬜ |
| `PAGE-029.20` | 401–404 | تابع | `food_ticket_attendance_factory_password` | 0 | ⬜ |
| `PAGE-029.21` | 406–465 | تابع | `food_ticket_config` | 5 | ⬜ |
| `PAGE-029.22` | 468–480 | تابع | `food_ticket_normalize_printer_mode` | 0 | ⬜ |
| `PAGE-029.23` | 482–499 | تابع | `food_ticket_normalize_printer_host` | 0 | ⬜ |
| `PAGE-029.24` | 501–511 | تابع | `food_ticket_printer_label` | 0 | ⬜ |
| `PAGE-029.25` | 513–534 | تابع | `food_ticket_ensure_printer_mode_column` | 4 | ⬜ |
| `PAGE-029.26` | 536–544 | تابع | `food_ticket_installed` | 0 | ⬜ |
| `PAGE-029.27` | 546–549 | تابع | `food_ticket_key` | 0 | ⬜ |
| `PAGE-029.28` | 551–560 | تابع | `food_ticket_encrypt` | 0 | ⬜ |
| `PAGE-029.29` | 562–573 | تابع | `food_ticket_decrypt` | 0 | ⬜ |
| `PAGE-029.30` | 575–623 | تابع | `food_ticket_attendance_password` | 4 | ⬜ |
| `PAGE-029.31` | 625–641 | تابع | `food_ticket_attendance_password_source` | 4 | ⬜ |
| `PAGE-029.32` | 643–649 | تابع | `food_ticket_ident` | 0 | ⬜ |
| `PAGE-029.33` | 651–671 | تابع | `food_ticket_odbc_pool` | 0 | ⬜ |
| `PAGE-029.34` | 673–684 | تابع | `food_ticket_odbc_release` | 1 | ⬜ |
| `PAGE-029.35` | 686–715 | تابع | `food_ticket_odbc_pool_handle` | 0 | ⬜ |
| `PAGE-029.36` | 717–773 | تابع | `food_ticket_odbc` | 1 | ⬜ |
| `PAGE-029.37` | 775–778 | تابع | `food_ticket_is_ps_access` | 0 | ⬜ |
| `PAGE-029.38` | 780–793 | تابع | `food_ticket_access_close` | 1 | ⬜ |
| `PAGE-029.39` | 795–1004 | تابع | `food_ticket_access_rows_powershell` | **13** | ⬜ |
| `PAGE-029.40` | 1007–1011 | تابع | `food_ticket_access_delete_powershell` | 0 | ⬜ |
| `PAGE-029.41` | 1013–1129 | تابع | `food_ticket_access_delete_powershell_ex` | **13** | ⬜ |
| `PAGE-029.42` | 1131–1134 | تابع | `food_ticket_normalize_column` | 0 | ⬜ |
| `PAGE-029.43` | 1136–1145 | تابع | `food_ticket_row_value` | 0 | ⬜ |
| `PAGE-029.44` | 1148–1207 | تابع | `food_ticket_access_rows` | **8** | ⬜ |
| `PAGE-029.45` | 1209–1214 | تابع | `food_ticket_normalize_digits` | 0 | ⬜ |
| `PAGE-029.46` | 1216–1247 | تابع | `food_ticket_iso_date_input` | 0 | ⬜ |
| `PAGE-029.47` | 1249–1303 | تابع | `food_ticket_jalali_to_gregorian` | 0 | ⬜ |
| `PAGE-029.48` | 1305–1404 | تابع | `food_ticket_parse_date` | 0 | ⬜ |
| `PAGE-029.49` | 1406–1455 | تابع | `food_ticket_parse_time` | 0 | ⬜ |
| `PAGE-029.50` | 1457–1511 | تابع | `food_ticket_source_key` | 0 | ⬜ |
| `PAGE-029.51` | 1513–1539 | تابع | `food_ticket_code` | 0 | ⬜ |
| `PAGE-029.52` | 1541–1544 | تابع | `food_ticket_digits` | 0 | ⬜ |
| `PAGE-029.53` | 1546–1561 | تابع | `food_ticket_find_user` | 0 | ⬜ |
| `PAGE-029.54` | 1564–1588 | تابع | `food_ticket_guest_cards` | 4 | ⬜ |
| `PAGE-029.55` | 1590–1601 | تابع | `food_ticket_card_key` | 0 | ⬜ |
| `PAGE-029.56` | 1603–1620 | تابع | `food_ticket_card_equals` | 0 | ⬜ |
| `PAGE-029.57` | 1622–1648 | تابع | `food_ticket_guest_card` | 4 | ⬜ |
| `PAGE-029.58` | 1650–1653 | تابع | `food_ticket_is_guest` | 0 | ⬜ |
| `PAGE-029.59` | 1655–1660 | تابع | `food_ticket_existing_source` | 0 | ⬜ |
| `PAGE-029.60` | 1662–1667 | تابع | `food_ticket_existing_ticket` | 0 | ⬜ |
| `PAGE-029.61` | 1669–1693 | تابع | `food_ticket_normalize_event_type` | 1 | ⬜ |
| `PAGE-029.62` | 1695–1703 | تابع | `food_ticket_normalize_print_status` | 0 | ⬜ |
| `PAGE-029.63` | 1705–1722 | تابع | `food_ticket_event_meal_type_supported` | 0 | ⬜ |
| `PAGE-029.64` | 1724–1753 | تابع | `food_ticket_add_event` | 6 | ⬜ |
| `PAGE-029.65` | 1755–1764 | تابع | `food_ticket_update_event` | 1 | ⬜ |
| `PAGE-029.66` | 1766–1835 | تابع | `food_ticket_source_delete_where` | 0 | ⬜ |
| `PAGE-029.67` | 1837–1877 | تابع | `food_ticket_source_primary_key` | 0 | ⬜ |
| `PAGE-029.68` | 1879–1901 | تابع | `food_ticket_source_type_kind` | 0 | ⬜ |
| `PAGE-029.69` | 1903–1918 | تابع | `food_ticket_source_is_temporal` | 0 | ⬜ |
| `PAGE-029.70` | 1920–1935 | تابع | `food_ticket_source_time_literal` | 0 | ⬜ |
| `PAGE-029.71` | 1937–1952 | تابع | `food_ticket_source_sql_literal` | 0 | ⬜ |
| `PAGE-029.72` | 1954–1969 | تابع | `food_ticket_source_column_sql_literal` | 0 | ⬜ |
| `PAGE-029.73` | 1971–2000 | تابع | `food_ticket_source_delete_candidates` | 0 | ⬜ |
| `PAGE-029.74` | 2002–2061 | تابع | `food_ticket_source_minimal_where` | 0 | ⬜ |
| `PAGE-029.75` | 2063–2157 | تابع | `food_ticket_delete_source` | **9** | ⬜ |
| `PAGE-029.76` | 2159–2189 | تابع | `food_ticket_mark_source_delete` | 6 | ⬜ |
| `PAGE-029.77` | 2191–2212 | تابع | `food_ticket_text` | 0 | ⬜ |
| `PAGE-029.78` | 2213–2218 | ناحیه | `بلوک رویه‌ای 2213-2218` | 0 | ⬜ |
| `PAGE-029.79` | 2219–2266 | تابع | `food_ticket_list_windows_printers` | 7 | ⬜ |
| `PAGE-029.80` | 2268–2286 | تابع | `food_ticket_send_to_printer` | 0 | ⬜ |
| `PAGE-029.81` | 2288–2343 | تابع | `food_ticket_send_windows` | 7 | ⬜ |
| `PAGE-029.82` | 2345–2368 | تابع | `food_ticket_print_event` | 1 | ⬜ |
| `PAGE-029.83` | 2370–2377 | تابع | `food_ticket_process_batch` | 0 | ⬜ |
| `PAGE-029.84` | 2379–2581 | تابع | `food_ticket_cleanup_source_rows` | **14** | ⬜ |
| `PAGE-029.85` | 2583–2635 | تابع | `food_ticket_retry_failed` | **13** | ⬜ |
| `PAGE-029.86` | 2637–2652 | تابع | `food_ticket_worker_heartbeat` | 1 | ⬜ |
| `PAGE-029.87` | 2654–2690 | تابع | `food_ticket_test_connections` | 0 | ⬜ |
| `PAGE-029.88` | 2692–2759 | تابع | `food_ticket_save_config` | 6 | ⬜ |
| `PAGE-029.89` | 2761–2784 | تابع | `food_ticket_handle_post` | 0 | ⬜ |
| `PAGE-029.90` | 2786–2794 | تابع | `food_ticket_label` | 0 | ⬜ |
| `PAGE-029.91` | 2796–2946 | تابع | `food_ticket_cp1256_to_utf8` | 6 | ⬜ |
| `PAGE-029.92` | 2948–2994 | تابع | `food_ticket_utf8` | 2 | ⬜ |
| `PAGE-029.93` | 2996–3017 | تابع | `food_ticket_api_json` | 3 | ⬜ |
| `PAGE-029.94` | 3019–3027 | تابع | `food_ticket_api_body` | 0 | ⬜ |
| `PAGE-029.95` | 3029–3056 | تابع | `food_ticket_api_printer_state` | 0 | ⬜ |
| `PAGE-029.96` | 3058–3104 | تابع | `food_ticket_api_print_diag` | 2 | ⬜ |
| `PAGE-029.97` | 3106–3200 | تابع | `food_ticket_api_config` | 0 | ⬜ |
| `PAGE-029.98` | 3201–3206 | ناحیه | `بلوک رویه‌ای 3201-3206` | 0 | ⬜ |
| `PAGE-029.99` | 3207–3271 | تابع | `food_ticket_api_guest_requests_report` | 0 | ⬜ |
| `PAGE-029.100` | 3272–3277 | ناحیه | `بلوک رویه‌ای 3272-3277` | 0 | ⬜ |
| `PAGE-029.101` | 3278–3285 | تابع | `food_ticket_aggregate_last_name` | 0 | ⬜ |
| `PAGE-029.102` | 3286–3295 | تابع | `food_ticket_aggregate_sort_people` | 0 | ⬜ |
| `PAGE-029.103` | 3296–3445 | تابع | `food_ticket_aggregate_diagnostics` | 6 | ⬜ |
| `PAGE-029.104` | 3447–3607 | تابع | `food_ticket_api_aggregate_report` | 1 | ⬜ |
| `PAGE-029.105` | 3609–3623 | تابع | `food_ticket_aggregate_columns` | 0 | ⬜ |
| `PAGE-029.106` | 3625–3682 | تابع | `food_ticket_aggregate_export` | 5 | ⬜ |
| `PAGE-029.107` | 3684–3705 | تابع | `food_ticket_aggregate_row` | 0 | ⬜ |
| `PAGE-029.108` | 3707–3814 | تابع | `food_ticket_api_absent` | 0 | ⬜ |
| `PAGE-029.109` | 3816–3925 | تابع | `food_ticket_api_monitoring` | 5 | ⬜ |
| `PAGE-029.110` | 3928–3940 | تابع | `food_ticket_date_keys_for_iso` | 0 | ⬜ |
| `PAGE-029.111` | 3943–3969 | تابع | `food_ticket_order_date_key` | 0 | ⬜ |
| `PAGE-029.112` | 3971–3983 | تابع | `food_ticket_api_orders` | 0 | ⬜ |
| `PAGE-029.113` | 3985–4089 | تابع | `food_ticket_ensure_employee_local_accounts` | 6 | ⬜ |
| `PAGE-029.114` | 4091–4112 | تابع | `food_ticket_api_employees` | 0 | ⬜ |
| `PAGE-029.115` | 4114–4117 | تابع | `food_ticket_normalize_guest_card` | 0 | ⬜ |
| `PAGE-029.116` | 4119–4138 | تابع | `food_ticket_api_guest_cards` | 1 | ⬜ |
| `PAGE-029.117` | 4140–4147 | تابع | `food_ticket_sync_guest_card_config` | 1 | ⬜ |
| `PAGE-029.118` | 4149–4223 | تابع | `food_ticket_api_save_employee` | 1 | ⬜ |
| `PAGE-029.119` | 4225–4246 | تابع | `food_ticket_api_save_guest_card` | 1 | ⬜ |
| `PAGE-029.120` | 4248–4271 | تابع | `food_ticket_api_delete_guest_card` | 1 | ⬜ |
| `PAGE-029.121` | 4273–4283 | تابع | `food_ticket_save_guest_cap_mode` | 0 | ⬜ |
| `PAGE-029.122` | 4285–4441 | تابع | `food_ticket_api_save_config` | 1 | ⬜ |
| `PAGE-029.123` | 4442–4448 | ناحیه | `بلوک رویه‌ای 4442-4448` | 0 | ⬜ |
| `PAGE-029.124` | 4449–4535 | تابع | `food_ticket_browse_list` | 3 | ⬜ |
| `PAGE-029.125` | 4537–4561 | تابع | `food_ticket_php_native_browse` | 2 | ⬜ |
| `PAGE-029.126` | 4563–4570 | تابع | `food_ticket_windows_browse` | 0 | ⬜ |
| `PAGE-029.127` | 4573–4641 | تابع | `food_ticket_api_health` | 0 | ⬜ |
| `PAGE-029.128` | 4643–4735 | تابع | `food_ticket_api_self_test` | 0 | ⬜ |
| `PAGE-029.129` | 4737–4805 | تابع | `food_ticket_api_upload_access_file` | **14** | ⬜ |
| `PAGE-029.130` | 4807–5481 | تابع | `food_ticket_api_handle` | **14** | ⬜ |
| `PAGE-029.131` | 5483–5497 | تابع | `food_ticket_render_safe_replace` | 0 | ⬜ |
| `PAGE-029.132` | 5499–5677 | تابع | `food_ticket_render_page` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-030</b> — <code>global-search.php</code> (122 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-030.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `PAGE-030.02` | 16–29 | تابع | `global_search_normalize` | 0 | ⬜ |
| `PAGE-030.03` | 31–73 | تابع | `global_search_catalog` | 0 | ⬜ |
| `PAGE-030.04` | 75–121 | تابع | `global_search_sections` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-031</b> — <code>governance.php</code> (546 خط، 32 آدرس، بازبینی‌شده 0/32)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-031.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-031.02` | 6–9 | تابع | `gov_post` | 2 | ⬜ |
| `PAGE-031.03` | 11–14 | تابع | `gov_choice` | 0 | ⬜ |
| `PAGE-031.04` | 16–19 | تابع | `gov_change_number` | 0 | ⬜ |
| `PAGE-031.05` | 21–24 | تابع | `gov_problem_number` | 0 | ⬜ |
| `PAGE-031.06` | 26–29 | تابع | `gov_risk_label` | 0 | ⬜ |
| `PAGE-031.07` | 31–34 | تابع | `gov_change_status_label` | 0 | ⬜ |
| `PAGE-031.08` | 36–39 | تابع | `gov_problem_status_label` | 0 | ⬜ |
| `PAGE-031.09` | 41–52 | تابع | `gov_parse_datetime` | 0 | ⬜ |
| `PAGE-031.10` | 54–67 | تابع | `gov_scope` | 5 | ⬜ |
| `PAGE-031.11` | 69–77 | تابع | `gov_fetch_ticket` | 0 | ⬜ |
| `PAGE-031.12` | 79–88 | تابع | `gov_can_view_ticket` | 0 | ⬜ |
| `PAGE-031.13` | 90–95 | تابع | `gov_fetch_change` | 0 | ⬜ |
| `PAGE-031.14` | 97–102 | تابع | `gov_fetch_problem` | 0 | ⬜ |
| `PAGE-031.15` | 104–113 | تابع | `gov_can_view_record` | 0 | ⬜ |
| `PAGE-031.16` | 115–118 | تابع | `gov_datetime_input` | 0 | ⬜ |
| `PAGE-031.17` | 120–131 | تابع | `gov_ticket_options` | 0 | ⬜ |
| `PAGE-031.18` | 133–143 | تابع | `gov_notify_linked_tickets` | 5 | ⬜ |
| `PAGE-031.19` | 145–163 | تابع | `gov_header` | 0 | ⬜ |
| `PAGE-031.20` | 165–169 | تابع | `gov_footer` | 0 | ⬜ |
| `PAGE-031.21` | 170–171 | ناحیه | `بلوک رویه‌ای 170-171` | 0 | ⬜ |
| `PAGE-031.22` | 172–172 | ناحیه | `۱.۳۷.۷ — هم‌راستا با منو و کاتالوگ: ورود به «تغییر` | 0 | ⬜ |
| `PAGE-031.23` | 173–173 | ناحیه | `می‌خواهد. پیش‌فرض همهٔ نقش‌های کارمندی این کد را د` | 0 | ⬜ |
| `PAGE-031.24` | 174–206 | ناحیه | `بگیرد اثر می‌کند.` | 0 | ⬜ |
| `PAGE-031.25` | 207–244 | ناحیه | `مسیر: create_change` | 2 | ⬜ |
| `PAGE-031.26` | 245–274 | ناحیه | `مسیر: update_change` | 2 | ⬜ |
| `PAGE-031.27` | 275–293 | ناحیه | `مسیر: submit_change` | 2 | ⬜ |
| `PAGE-031.28` | 294–316 | ناحیه | `مسیر: cab_decision` | 2 | ⬜ |
| `PAGE-031.29` | 317–340 | ناحیه | `مسیر: change_transition` | 7 | ⬜ |
| `PAGE-031.30` | 341–371 | ناحیه | `مسیر: create_problem` | 2 | ⬜ |
| `PAGE-031.31` | 372–401 | ناحیه | `مسیر: update_problem` | 2 | ⬜ |
| `PAGE-031.32` | 402–546 | ناحیه | `مسیر: link_governance_ticket` | **8** | ⬜ |

</details>

<details>
<summary><b>PAGE-032</b> — <code>help-guide.php</code> (722 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-032.01` | 1–722 | ناحیه | `بلوک رویه‌ای 1-722` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-033</b> — <code>holiday-board.php</code> (76 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-033.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `PAGE-033.02` | 25–51 | تابع | `holiday_board_work_hours_form` | 0 | ⬜ |
| `PAGE-033.03` | 53–75 | تابع | `holiday_board_render` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-034</b> — <code>index.php</code> (2,996 خط، 90 آدرس، بازبینی‌شده 0/90)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-034.01` | 1–31 | ناحیه | `بلوک رویه‌ای 1-31` | 1 | ⬜ |
| `PAGE-034.02` | 32–32 | ناحیه | `تبدیل رکوردهای قدیمی غذا فقط هنگام بارگذاری فهرست ` | 0 | ⬜ |
| `PAGE-034.03` | 33–38 | ناحیه | `نه در هر درخواست و نه قبل از نمایش فرم ورود.` | 2 | ⬜ |
| `PAGE-034.04` | 39–42 | تابع | `post_value` | 2 | ⬜ |
| `PAGE-034.05` | 44–48 | تابع | `csv_cell` | 0 | ⬜ |
| `PAGE-034.06` | 50–53 | تابع | `excel_cell` | 0 | ⬜ |
| `PAGE-034.07` | 55–116 | تابع | `excel_download` | 4 | ⬜ |
| `PAGE-034.08` | 118–121 | تابع | `valid_choice` | 0 | ⬜ |
| `PAGE-034.09` | 123–131 | تابع | `log_sort_order` | 0 | ⬜ |
| `PAGE-034.10` | 133–152 | تابع | `log_sort_header_row` | 0 | ⬜ |
| `PAGE-034.11` | 154–173 | تابع | `log_bounded_date_range` | 0 | ⬜ |
| `PAGE-034.12` | 175–240 | تابع | `activity_log_filters` | 2 | ⬜ |
| `PAGE-034.13` | 242–262 | تابع | `activity_log_meta_lines` | 0 | ⬜ |
| `PAGE-034.14` | 264–278 | تابع | `imported_calendar_date` | 0 | ⬜ |
| `PAGE-034.15` | 280–283 | تابع | `calendar_unescape` | 0 | ⬜ |
| `PAGE-034.16` | 285–356 | تابع | `parse_calendar_import` | 0 | ⬜ |
| `PAGE-034.17` | 358–411 | تابع | `store_attachment` | 6 | ⬜ |
| `PAGE-034.18` | 413–436 | تابع | `store_profile_photo` | 1 | ⬜ |
| `PAGE-034.19` | 438–450 | تابع | `valid_iranian_national_code` | 0 | ⬜ |
| `PAGE-034.20` | 452–457 | تابع | `fetch_ticket` | 0 | ⬜ |
| `PAGE-034.21` | 459–462 | تابع | `can_view_ticket` | 0 | ⬜ |
| `PAGE-034.22` | 464–483 | تابع | `ticket_org_manager_rank_sql` | 0 | ⬜ |
| `PAGE-034.23` | 485–527 | تابع | `choose_handling_agent` | 5 | ⬜ |
| `PAGE-034.24` | 529–537 | تابع | `ticket_allowed_transitions` | 0 | ⬜ |
| `PAGE-034.25` | 538–542 | تابع | `ticket_requester_cancelable_statuses` | 0 | ⬜ |
| `PAGE-034.26` | 544–550 | تابع | `ticket_requester_can_cancel_directly` | 0 | ⬜ |
| `PAGE-034.27` | 552–558 | تابع | `ticket_requester_can_cancel` | 0 | ⬜ |
| `PAGE-034.28` | 560–569 | تابع | `notify_ticket_resolved` | 0 | ⬜ |
| `PAGE-034.29` | 570–624 | تابع | `report_query_filters` | 2 | ⬜ |
| `PAGE-034.30` | 626–775 | تابع | `render_header` | 4 | ⬜ |
| `PAGE-034.31` | 777–784 | تابع | `list_sort_page_off` | 2 | ⬜ |
| `PAGE-034.32` | 786–807 | تابع | `inspection_food_reminder_markup` | 0 | ⬜ |
| `PAGE-034.33` | 809–840 | تابع | `render_footer` | 0 | ⬜ |
| `PAGE-034.34` | 841–846 | ناحیه | `بلوک رویه‌ای 841-846` | 2 | ⬜ |
| `PAGE-034.35` | 847–866 | ناحیه | `ناحیهٔ ۱: خروج از حساب (قبل از هر چیز)` | 2 | ⬜ |
| `PAGE-034.36` | 867–868 | ناحیه | `ناحیهٔ ۲: تعیین صفحهٔ جاری و پیش‌شرط‌های آن` | 2 | ⬜ |
| `PAGE-034.37` | 869–876 | ناحیه | `۱.۵۰: کاربر محلی/بازرسی به سفارش غذا هدایت می‌شود؛` | 2 | ⬜ |
| `PAGE-034.38` | 877–887 | ناحیه | `مسیر: must_change_password\|change-password` | 2 | ⬜ |
| `PAGE-034.39` | 888–901 | ناحیه | `مسیر: REQUEST_METHOD\|POST\|organization` | 0 | ⬜ |
| `PAGE-034.40` | 902–915 | ناحیه | `مسیر: food-ticket\|food_api` | 2 | ⬜ |
| `PAGE-034.41` | 916–927 | ناحیه | `مسیر: food-order\|food_api` | 2 | ⬜ |
| `PAGE-034.42` | 928–1073 | ناحیه | `مسیر: traffic-control\|traffic_api` | **9** | ⬜ |
| `PAGE-034.43` | 1074–1188 | ناحیه | `خروجی XLSX از نمای مرتب‌شدهٔ لیست در مرورگر (فقط د` | 7 | ⬜ |
| `PAGE-034.44` | 1189–1194 | ناحیه | `مسیر: food-personnel-link` | 0 | ⬜ |
| `PAGE-034.45` | 1195–1198 | ناحیه | `ناحیهٔ ۴: پردازش همهٔ اکشن‌های POST (74 اکشن، گروه` | 2 | ⬜ |
| `PAGE-034.46` | 1199–1309 | ناحیه | `مسیر: logging_begin_operation` | 6 | ⬜ |
| `PAGE-034.47` | 1310–1320 | ناحیه | `مسیر: settings\|create_access_group\|save_access_g` | 2 | ⬜ |
| `PAGE-034.48` | 1321–1373 | ناحیه | `مسیر: chatbot-api` | 0 | ⬜ |
| `PAGE-034.49` | 1374–1374 | ناحیه | `۱.۳۷.۷ — دروازهٔ «سامانه مدیریت غذا» با فهرست کدها` | 0 | ⬜ |
| `PAGE-034.50` | 1375–1375 | ناحیه | `food_ticket_is_allowed() (هر کد food.*) نمایش داده` | 0 | ⬜ |
| `PAGE-034.51` | 1376–1376 | ناحیه | `می‌شد؛ یعنی نقشی که مثلاً فقط food.orders داشت، لی` | 0 | ⬜ |
| `PAGE-034.52` | 1377–1384 | ناحیه | `مسیر: food-ticket\|food_ticket_is_allowed\|food.mo` | 0 | ⬜ |
| `PAGE-034.53` | 1385–1392 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `PAGE-034.54` | 1393–1401 | ناحیه | `مسیر: settings\|settings.general\|settings.domain` | 0 | ⬜ |
| `PAGE-034.55` | 1402–1404 | ناحیه | `مسیر: change-password` | 0 | ⬜ |
| `PAGE-034.56` | 1405–1407 | ناحیه | `مسیر: local-accounts` | 0 | ⬜ |
| `PAGE-034.57` | 1408–1425 | ناحیه | `مسیر: profile` | 0 | ⬜ |
| `PAGE-034.58` | 1426–1429 | ناحیه | `مسیر: governance` | 0 | ⬜ |
| `PAGE-034.59` | 1430–1434 | ناحیه | `مسیر: food-ticket` | 0 | ⬜ |
| `PAGE-034.60` | 1435–1442 | ناحیه | `مسیر: food-order\|foodorder.self` | 0 | ⬜ |
| `PAGE-034.61` | 1443–1446 | ناحیه | `مسیر: food-order` | 0 | ⬜ |
| `PAGE-034.62` | 1447–1450 | ناحیه | `مسیر: cd-dvd` | 0 | ⬜ |
| `PAGE-034.63` | 1451–1454 | ناحیه | `مسیر: cd-dvd-history` | 0 | ⬜ |
| `PAGE-034.64` | 1455–1457 | ناحیه | `مسیر: traffic-control` | 0 | ⬜ |
| `PAGE-034.65` | 1458–1462 | ناحیه | `مسیر: traffic-control-print` | 2 | ⬜ |
| `PAGE-034.66` | 1463–1470 | ناحیه | `مسیر: help` | 0 | ⬜ |
| `PAGE-034.67` | 1471–1507 | ناحیه | `مسیر: knowledge` | 2 | ⬜ |
| `PAGE-034.68` | 1508–1515 | ناحیه | `مسیر: notifications\|notif.view` | 0 | ⬜ |
| `PAGE-034.69` | 1516–1533 | ناحیه | `مسیر: notifications` | 5 | ⬜ |
| `PAGE-034.70` | 1534–1542 | ناحیه | `مسیر: audit` | 0 | ⬜ |
| `PAGE-034.71` | 1543–1546 | ناحیه | `مسیر: logging-dashboard` | 0 | ⬜ |
| `PAGE-034.72` | 1547–1550 | ناحیه | `مسیر: system-health` | 0 | ⬜ |
| `PAGE-034.73` | 1551–1556 | ناحیه | `مسیر: logs` | 0 | ⬜ |
| `PAGE-034.74` | 1557–1699 | ناحیه | `مسیر: activity-log` | **8** | ⬜ |
| `PAGE-034.75` | 1700–1738 | ناحیه | `مسیر: backup` | 6 | ⬜ |
| `PAGE-034.76` | 1739–1746 | ناحیه | `مسیر: search\|search.global` | 0 | ⬜ |
| `PAGE-034.77` | 1747–1838 | ناحیه | `مسیر: search` | 7 | ⬜ |
| `PAGE-034.78` | 1839–1941 | ناحیه | `مسیر: chatbot-admin` | 5 | ⬜ |
| `PAGE-034.79` | 1942–1952 | ناحیه | `مسیر: holidays` | 0 | ⬜ |
| `PAGE-034.80` | 1953–1965 | ناحیه | `مسیر: new-ticket\|ticket.create` | 0 | ⬜ |
| `PAGE-034.81` | 1966–2066 | ناحیه | `مسیر: inventory` | 7 | ⬜ |
| `PAGE-034.82` | 2067–2098 | ناحیه | `مسیر: inventory-diagnostics` | 2 | ⬜ |
| `PAGE-034.83` | 2099–2116 | ناحیه | `مسیر: asset-history` | 7 | ⬜ |
| `PAGE-034.84` | 2117–2194 | ناحیه | `مسیر: domain-scan` | 2 | ⬜ |
| `PAGE-034.85` | 2195–2239 | ناحیه | `مسیر: assets` | 7 | ⬜ |
| `PAGE-034.86` | 2240–2273 | ناحیه | `مسیر: asset` | 7 | ⬜ |
| `PAGE-034.87` | 2274–2464 | ناحیه | `مسیر: organization` | 3 | ⬜ |
| `PAGE-034.88` | 2465–2934 | ناحیه | `مسیر: settings` | **11** | ⬜ |
| `PAGE-034.89` | 2935–2956 | ناحیه | `مسیر: logs` | 2 | ⬜ |
| `PAGE-034.90` | 2957–2996 | ناحیه | `مسیر: dashboard` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-035</b> — <code>install.php</code> (368 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-035.01` | 1–23 | ناحیه | `بلوک رویه‌ای 1-23` | 2 | ⬜ |
| `PAGE-035.02` | 24–27 | تابع | `ih` | 0 | ⬜ |
| `PAGE-035.03` | 29–35 | تابع | `install_csrf` | 0 | ⬜ |
| `PAGE-035.04` | 37–47 | تابع | `install_layout` | 0 | ⬜ |
| `PAGE-035.05` | 49–52 | تابع | `install_error` | 0 | ⬜ |
| `PAGE-035.06` | 54–59 | تابع | `take_install_error` | 0 | ⬜ |
| `PAGE-035.07` | 61–66 | تابع | `mysql_from_session` | 0 | ⬜ |
| `PAGE-035.08` | 68–87 | تابع | `ldap_probe` | 2 | ⬜ |
| `PAGE-035.09` | 88–97 | ناحیه | `بلوک رویه‌ای 88-97` | 2 | ⬜ |
| `PAGE-035.10` | 98–126 | ناحیه | `شرط مسیریابی` | 3 | ⬜ |
| `PAGE-035.11` | 127–137 | ناحیه | `مسیر: test_db` | 0 | ⬜ |
| `PAGE-035.12` | 138–151 | ناحیه | `شرط مسیریابی` | 2 | ⬜ |
| `PAGE-035.13` | 152–351 | ناحیه | `مسیر: test_ldap` | **10** | ⬜ |
| `PAGE-035.14` | 352–360 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `PAGE-035.15` | 361–368 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-036</b> — <code>inventory.php</code> (836 خط، 33 آدرس، بازبینی‌شده 0/33)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-036.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `PAGE-036.02` | 7–14 | تابع | `inventory_command` | 5 | ⬜ |
| `PAGE-036.03` | 16–27 | تابع | `inventory_json` | 0 | ⬜ |
| `PAGE-036.04` | 29–33 | تابع | `inventory_last_error` | 0 | ⬜ |
| `PAGE-036.05` | 35–41 | تابع | `inventory_set_error` | 0 | ⬜ |
| `PAGE-036.06` | 43–46 | تابع | `inventory_server_script_path` | 0 | ⬜ |
| `PAGE-036.07` | 48–56 | تابع | `inventory_local_hostnames` | 0 | ⬜ |
| `PAGE-036.08` | 58–62 | تابع | `inventory_server_asset_tag` | 0 | ⬜ |
| `PAGE-036.09` | 64–72 | تابع | `inventory_asset_is_local` | 0 | ⬜ |
| `PAGE-036.10` | 74–96 | تابع | `inventory_run_powershell_file` | 0 | ⬜ |
| `PAGE-036.11` | 98–127 | تابع | `inventory_diagnostics` | 0 | ⬜ |
| `PAGE-036.12` | 129–137 | تابع | `inventory_value` | 0 | ⬜ |
| `PAGE-036.13` | 139–145 | تابع | `inventory_rows` | 0 | ⬜ |
| `PAGE-036.14` | 147–206 | تابع | `inventory_collection_status_keys` | 0 | ⬜ |
| `PAGE-036.15` | 208–220 | تابع | `inventory_collection_category_succeeded` | 0 | ⬜ |
| `PAGE-036.16` | 222–258 | تابع | `inventory_merge_missing_values` | 0 | ⬜ |
| `PAGE-036.17` | 260–288 | تابع | `inventory_saved_asset_snapshot` | 0 | ⬜ |
| `PAGE-036.18` | 290–334 | تابع | `inventory_apply_collection_status_metadata` | 0 | ⬜ |
| `PAGE-036.19` | 336–344 | تابع | `inventory_asset_exists` | 0 | ⬜ |
| `PAGE-036.20` | 346–382 | تابع | `sync_inventory_rows` | 1 | ⬜ |
| `PAGE-036.21` | 384–394 | تابع | `inventory_hardware_score` | 0 | ⬜ |
| `PAGE-036.22` | 396–410 | تابع | `inventory_collect_server_reliable` | 1 | ⬜ |
| `PAGE-036.23` | 412–424 | تابع | `inventory_server_asset_id` | 0 | ⬜ |
| `PAGE-036.24` | 426–444 | تابع | `inventory_server_profile_is_empty` | 0 | ⬜ |
| `PAGE-036.25` | 446–465 | تابع | `inventory_auto_extract_server` | 0 | ⬜ |
| `PAGE-036.26` | 467–502 | تابع | `inventory_base_inventory` | 0 | ⬜ |
| `PAGE-036.27` | 504–616 | تابع | `inventory_apply_powershell_data` | 0 | ⬜ |
| `PAGE-036.28` | 618–634 | تابع | `collect_server_inventory` | 0 | ⬜ |
| `PAGE-036.29` | 636–686 | تابع | `inventory_upsert_server_asset` | 6 | ⬜ |
| `PAGE-036.30` | 688–702 | تابع | `inventory_collect_initial_asset` | 0 | ⬜ |
| `PAGE-036.31` | 704–756 | تابع | `inventory_reverse_name` | 7 | ⬜ |
| `PAGE-036.32` | 758–829 | تابع | `inventory_collect_client_asset` | 1 | ⬜ |
| `PAGE-036.33` | 831–835 | تابع | `inventory_client_ip` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-037</b> — <code>local-accounts.php</code> (243 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-037.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `PAGE-037.02` | 19–23 | تابع | `lac_default_hash` | 0 | ⬜ |
| `PAGE-037.03` | 25–73 | تابع | `lac_ensure_schema` | 4 | ⬜ |
| `PAGE-037.04` | 75–79 | تابع | `lac_is_local_role` | 0 | ⬜ |
| `PAGE-037.05` | 81–136 | تابع | `lac_change_password_request` | 2 | ⬜ |
| `PAGE-037.06` | 138–242 | تابع | `lac_admin_request` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-038</b> — <code>logging-dashboard.php</code> (187 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-038.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-038.02` | 4–95 | تابع | `logging_dashboard_fetch` | 1 | ⬜ |
| `PAGE-038.03` | 97–112 | تابع | `logging_dashboard_svg_trend` | 0 | ⬜ |
| `PAGE-038.04` | 114–186 | تابع | `logging_dashboard_render` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-039</b> — <code>modules/request-center/Actions/bulk-actions.php</code> (141 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-039.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `PAGE-039.02` | 17–23 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `PAGE-039.03` | 24–43 | ناحیه | `مسیر: add_asset_relation` | **8** | ⬜ |
| `PAGE-039.04` | 44–141 | ناحیه | `مسیر: bulk_update_tickets` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-040</b> — <code>modules/request-center/Actions/service-actions.php</code> (229 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-040.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `PAGE-040.02` | 22–28 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `PAGE-040.03` | 29–68 | ناحیه | `مسیر: add_service` | 2 | ⬜ |
| `PAGE-040.04` | 69–93 | ناحیه | `مسیر: add_service_field` | 2 | ⬜ |
| `PAGE-040.05` | 94–132 | ناحیه | `مسیر: edit_service` | 0 | ⬜ |
| `PAGE-040.06` | 133–154 | ناحیه | `مسیر: delete_service` | 0 | ⬜ |
| `PAGE-040.07` | 155–185 | ناحیه | `مسیر: save_service_category` | 2 | ⬜ |
| `PAGE-040.08` | 186–205 | ناحیه | `مسیر: delete_service_category` | 0 | ⬜ |
| `PAGE-040.09` | 206–229 | ناحیه | `مسیر: add_knowledge` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-041</b> — <code>modules/request-center/Actions/supervisor-actions.php</code> (205 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-041.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `PAGE-041.02` | 19–25 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `PAGE-041.03` | 26–39 | ناحیه | `مسیر: food_order_cancel_decide` | 2 | ⬜ |
| `PAGE-041.04` | 40–113 | ناحیه | `مسیر: supervisor_decision` | 7 | ⬜ |
| `PAGE-041.05` | 114–163 | ناحیه | `مسیر: supervisor_reopen` | 2 | ⬜ |
| `PAGE-041.06` | 164–205 | ناحیه | `مسیر: rate_ticket` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-042</b> — <code>modules/request-center/Actions/ticket-actions.php</code> (753 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-042.01` | 1–22 | ناحیه | `بلوک رویه‌ای 1-22` | 0 | ⬜ |
| `PAGE-042.02` | 23–29 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `PAGE-042.03` | 30–221 | ناحیه | `مسیر: create_ticket` | **8** | ⬜ |
| `PAGE-042.04` | 222–283 | ناحیه | `مسیر: cancel_ticket` | 7 | ⬜ |
| `PAGE-042.05` | 284–365 | ناحیه | `مسیر: decide_ticket_cancellation` | 2 | ⬜ |
| `PAGE-042.06` | 366–405 | ناحیه | `مسیر: claim_ticket` | 2 | ⬜ |
| `PAGE-042.07` | 406–455 | ناحیه | `مسیر: reopen_ticket` | 7 | ⬜ |
| `PAGE-042.08` | 456–604 | ناحیه | `مسیر: reply` | 3 | ⬜ |
| `PAGE-042.09` | 605–753 | ناحیه | `مسیر: update_ticket` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-043</b> — <code>modules/request-center/Assets/request-center.css</code> (2 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-043.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-044</b> — <code>modules/request-center/Pages/admin.php</code> (83 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-044.01` | 1–83 | ناحیه | `بلوک رویه‌ای 1-83` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-045</b> — <code>modules/request-center/Pages/analytics.php</code> (49 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-045.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-045.02` | 6–49 | ناحیه | `مسیر: analytics` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-046</b> — <code>modules/request-center/Pages/calendar.php</code> (130 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-046.01` | 1–130 | ناحیه | `بلوک رویه‌ای 1-130` | **8** | ⬜ |

</details>

<details>
<summary><b>PAGE-047</b> — <code>modules/request-center/Pages/create.php</code> (147 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-047.01` | 1–11 | ناحیه | `بلوک رویه‌ای 1-11` | 0 | ⬜ |
| `PAGE-047.02` | 12–147 | ناحیه | `مسیر: new-ticket` | **8** | ⬜ |

</details>

<details>
<summary><b>PAGE-048</b> — <code>modules/request-center/Pages/detail.php</code> (246 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-048.01` | 1–10 | ناحیه | `بلوک رویه‌ای 1-10` | 0 | ⬜ |
| `PAGE-048.02` | 11–246 | ناحیه | `مسیر: ticket` | **8** | ⬜ |

</details>

<details>
<summary><b>PAGE-049</b> — <code>modules/request-center/Pages/index.php</code> (342 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-049.01` | 1–94 | ناحیه | `بلوک رویه‌ای 1-94` | 2 | ⬜ |
| `PAGE-049.02` | 95–342 | ناحیه | `Dedicated role workspaces are routed before the ge` | **8** | ⬜ |

</details>

<details>
<summary><b>PAGE-050</b> — <code>modules/request-center/Pages/queue.php</code> (39 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-050.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-050.02` | 6–39 | ناحیه | `مسیر: queue` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-051</b> — <code>modules/request-center/Pages/reports.php</code> (67 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-051.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-051.02` | 6–67 | ناحیه | `مسیر: reports` | 7 | ⬜ |

</details>

<details>
<summary><b>PAGE-052</b> — <code>modules/request-center/Pages/services.php</code> (176 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-052.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-052.02` | 6–91 | ناحیه | `مسیر: services` | 7 | ⬜ |
| `PAGE-052.03` | 92–100 | ناحیه | `سربرگ‌ها (تب‌ها)` | 0 | ⬜ |
| `PAGE-052.04` | 101–110 | ناحیه | `تب ۱: تعریف خدمت (فقط فرم ایجاد خدمت)` | 0 | ⬜ |
| `PAGE-052.05` | 111–134 | ناحیه | `تب ۲: لیست خدمات + فیلتر` | 6 | ⬜ |
| `PAGE-052.06` | 135–148 | ناحیه | `تب ۳: دسته‌بندی‌ها` | 6 | ⬜ |
| `PAGE-052.07` | 149–176 | ناحیه | `پنجره‌های ویرایش/فیلد (rendered outside the tab pa` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-053</b> — <code>modules/request-center/Pages/supervisor-workspace.php</code> (66 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-053.01` | 1–66 | ناحیه | `بلوک رویه‌ای 1-66` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-054</b> — <code>modules/request-center/Pages/supervisor.php</code> (53 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-054.01` | 1–8 | ناحیه | `بلوک رویه‌ای 1-8` | 0 | ⬜ |
| `PAGE-054.02` | 9–53 | ناحیه | `مسیر: supervisor` | 7 | ⬜ |

</details>

<details>
<summary><b>PAGE-055</b> — <code>modules/request-center/RequestCenterRepository.php</code> (444 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-055.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-055.02` | 4–443 | کلاس | `RequestCenterRepository` | **8** | ⬜ |

</details>

<details>
<summary><b>PAGE-056</b> — <code>modules/request-center/WorkflowService.php</code> (257 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-056.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-056.02` | 4–34 | کلاس | `RequestCenterSqlExpression` | 0 | ⬜ |
| `PAGE-056.03` | 36–256 | کلاس | `RequestCenterWorkflowService` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-057</b> — <code>ola.php</code> (112 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-057.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-057.02` | 6–9 | تابع | `ola_post` | 2 | ⬜ |
| `PAGE-057.03` | 11–22 | تابع | `ola_header` | 0 | ⬜ |
| `PAGE-057.04` | 24–28 | تابع | `ola_footer` | 0 | ⬜ |
| `PAGE-057.05` | 30–30 | ناحیه | `۱.۳۷.۷ — این صفحه با require_admin() باز می‌شد، ام` | 0 | ⬜ |
| `PAGE-057.06` | 31–31 | ناحیه | `کد «ola.manage» بود؛ هر دو یکی شد (ادمین اصلی همهٔ` | 0 | ⬜ |
| `PAGE-057.07` | 32–50 | ناحیه | `قابل دادن/گرفتن است).` | 0 | ⬜ |
| `PAGE-057.08` | 51–72 | ناحیه | `مسیر: save_ola_policy` | 3 | ⬜ |
| `PAGE-057.09` | 73–112 | ناحیه | `مسیر: toggle_ola_policy` | **8** | ⬜ |

</details>

<details>
<summary><b>PAGE-058</b> — <code>organization.php</code> (787 خط، 52 آدرس، بازبینی‌شده 0/52)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-058.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-058.02` | 4–66 | تابع | `org_ensure_schema` | **9** | ⬜ |
| `PAGE-058.03` | 69–90 | تابع | `org_ticket_schema_ensure` | 4 | ⬜ |
| `PAGE-058.04` | 92–103 | تابع | `org_has_company_schema` | 0 | ⬜ |
| `PAGE-058.05` | 105–114 | تابع | `org_companies_all` | 0 | ⬜ |
| `PAGE-058.06` | 116–125 | تابع | `org_company_display_name` | 0 | ⬜ |
| `PAGE-058.07` | 127–134 | تابع | `org_company_user_label` | 0 | ⬜ |
| `PAGE-058.08` | 136–144 | تابع | `org_company_by_id` | 0 | ⬜ |
| `PAGE-058.09` | 146–152 | تابع | `org_user_company_memberships` | 0 | ⬜ |
| `PAGE-058.10` | 154–158 | تابع | `org_user_company_id` | 0 | ⬜ |
| `PAGE-058.11` | 160–164 | تابع | `org_user_available_for_company` | 0 | ⬜ |
| `PAGE-058.12` | 166–171 | تابع | `org_user_company_membership_join_sql` | 5 | ⬜ |
| `PAGE-058.13` | 173–178 | تابع | `org_company_member_user_ids` | 5 | ⬜ |
| `PAGE-058.14` | 180–195 | تابع | `org_company_context_id` | 0 | ⬜ |
| `PAGE-058.15` | 197–206 | تابع | `org_current_company_id` | 0 | ⬜ |
| `PAGE-058.16` | 208–212 | تابع | `org_company_root_type` | 0 | ⬜ |
| `PAGE-058.17` | 214–218 | تابع | `org_company_root_title` | 0 | ⬜ |
| `PAGE-058.18` | 220–233 | تابع | `org_has_sort_order` | 0 | ⬜ |
| `PAGE-058.19` | 235–243 | تابع | `org_next_sort_order` | 0 | ⬜ |
| `PAGE-058.20` | 245–254 | تابع | `org_units_all` | 0 | ⬜ |
| `PAGE-058.21` | 256–272 | تابع | `org_deputy_units` | 5 | ⬜ |
| `PAGE-058.22` | 274–299 | تابع | `org_liaison_units` | 4 | ⬜ |
| `PAGE-058.23` | 301–334 | تابع | `org_unit_routing_department_id` | 1 | ⬜ |
| `PAGE-058.24` | 336–349 | تابع | `org_ceo_unit_id` | 0 | ⬜ |
| `PAGE-058.25` | 351–359 | تابع | `org_unit_accepts_experts` | 0 | ⬜ |
| `PAGE-058.26` | 361–367 | تابع | `org_unit_by_id` | 0 | ⬜ |
| `PAGE-058.27` | 369–375 | تابع | `org_unit_by_company` | 0 | ⬜ |
| `PAGE-058.28` | 377–381 | تابع | `org_company_redirect_url` | 0 | ⬜ |
| `PAGE-058.29` | 383–389 | تابع | `org_unit_children` | 5 | ⬜ |
| `PAGE-058.30` | 391–406 | تابع | `org_unit_subtree_ids` | 0 | ⬜ |
| `PAGE-058.31` | 408–422 | تابع | `org_unit_path_names` | 0 | ⬜ |
| `PAGE-058.32` | 424–429 | تابع | `org_unit_manager_ids` | 0 | ⬜ |
| `PAGE-058.33` | 431–435 | تابع | `org_unit_is_primary_admin` | 0 | ⬜ |
| `PAGE-058.34` | 437–440 | تابع | `user_org_unit_id` | 0 | ⬜ |
| `PAGE-058.35` | 442–447 | تابع | `user_is_unit_manager` | 0 | ⬜ |
| `PAGE-058.36` | 449–465 | تابع | `user_managed_unit_ids` | 0 | ⬜ |
| `PAGE-058.37` | 467–476 | تابع | `user_org_scope_unit_ids` | 0 | ⬜ |
| `PAGE-058.38` | 478–484 | تابع | `user_is_org_manager_of` | 0 | ⬜ |
| `PAGE-058.39` | 486–492 | تابع | `user_can_manage_organization` | 0 | ⬜ |
| `PAGE-058.40` | 494–517 | تابع | `org_snapshot` | 5 | ⬜ |
| `PAGE-058.41` | 519–526 | تابع | `org_snapshot_maps` | 0 | ⬜ |
| `PAGE-058.42` | 528–549 | تابع | `org_snapshot_row_signature` | 0 | ⬜ |
| `PAGE-058.43` | 551–568 | تابع | `org_snapshot_delta` | 0 | ⬜ |
| `PAGE-058.44` | 570–583 | تابع | `org_record_undo` | 0 | ⬜ |
| `PAGE-058.45` | 585–591 | تابع | `org_undo_state` | 0 | ⬜ |
| `PAGE-058.46` | 593–668 | تابع | `org_restore_snapshot` | 5 | ⬜ |
| `PAGE-058.47` | 670–679 | تابع | `org_position_unit_types` | 0 | ⬜ |
| `PAGE-058.48` | 681–684 | تابع | `org_exempt_roles` | 0 | ⬜ |
| `PAGE-058.49` | 686–704 | تابع | `org_ensure_role_sync_columns` | **8** | ⬜ |
| `PAGE-058.50` | 706–722 | تابع | `org_position_user_ids` | 4 | ⬜ |
| `PAGE-058.51` | 724–729 | تابع | `org_expert_user_ids` | 0 | ⬜ |
| `PAGE-058.52` | 731–786 | تابع | `org_sync_position_roles` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-059</b> — <code>permissions.php</code> (912 خط، 50 آدرس، بازبینی‌شده 0/50)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-059.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-059.02` | 4–26 | تابع | `permission_roles` | 0 | ⬜ |
| `PAGE-059.03` | 28–214 | تابع | `permission_catalog` | 2 | ⬜ |
| `PAGE-059.04` | 216–225 | تابع | `permission_all_codes` | 0 | ⬜ |
| `PAGE-059.05` | 226–230 | ناحیه | `بلوک رویه‌ای 226-230` | 0 | ⬜ |
| `PAGE-059.06` | 231–235 | تابع | `permission_cddvd_direct_edit_roles` | 1 | ⬜ |
| `PAGE-059.07` | 237–244 | تابع | `permission_filter_role_codes` | 0 | ⬜ |
| `PAGE-059.08` | 246–279 | تابع | `permission_edit_map` | 0 | ⬜ |
| `PAGE-059.09` | 281–291 | تابع | `role_permissions_add_parents` | 0 | ⬜ |
| `PAGE-059.10` | 292–295 | ناحیه | `بلوک رویه‌ای 292-295` | 0 | ⬜ |
| `PAGE-059.11` | 296–300 | تابع | `print_fish_allowed_roles` | 0 | ⬜ |
| `PAGE-059.12` | 302–311 | تابع | `print_fish_role_targets` | 0 | ⬜ |
| `PAGE-059.13` | 313–323 | تابع | `print_fish_role_codes` | 0 | ⬜ |
| `PAGE-059.14` | 325–342 | تابع | `permission_defaults` | 0 | ⬜ |
| `PAGE-059.15` | 344–348 | تابع | `user_default_removed_codes` | 0 | ⬜ |
| `PAGE-059.16` | 350–370 | تابع | `permission_defaults_raw` | 0 | ⬜ |
| `PAGE-059.17` | 372–385 | تابع | `permission_table_ready` | 0 | ⬜ |
| `PAGE-059.18` | 386–389 | ناحیه | `بلوک رویه‌ای 386-389` | 0 | ⬜ |
| `PAGE-059.19` | 390–406 | تابع | `role_permissions_custom` | 0 | ⬜ |
| `PAGE-059.20` | 408–414 | تابع | `role_permissions_mark_custom` | 0 | ⬜ |
| `PAGE-059.21` | 416–420 | تابع | `role_permissions_normalize_codes` | 0 | ⬜ |
| `PAGE-059.22` | 422–448 | تابع | `permission_food_legacy_codes` | 0 | ⬜ |
| `PAGE-059.23` | 450–464 | تابع | `role_permission_effective_codes` | 0 | ⬜ |
| `PAGE-059.24` | 466–474 | تابع | `role_permissions_same_set` | 0 | ⬜ |
| `PAGE-059.25` | 476–485 | تابع | `role_permissions_flush_cache` | 0 | ⬜ |
| `PAGE-059.26` | 487–573 | تابع | `role_permissions_migrate_once` | **9** | ⬜ |
| `PAGE-059.27` | 575–596 | تابع | `role_permission_codes` | 0 | ⬜ |
| `PAGE-059.28` | 598–611 | تابع | `role_permission_set` | 0 | ⬜ |
| `PAGE-059.29` | 613–655 | تابع | `user_access_group_permission_set` | 0 | ⬜ |
| `PAGE-059.30` | 657–668 | تابع | `user_access_group_permissions_flush_cache` | 1 | ⬜ |
| `PAGE-059.31` | 670–680 | تابع | `role_permissions_seeded` | 0 | ⬜ |
| `PAGE-059.32` | 682–716 | تابع | `role_permissions_seed` | 0 | ⬜ |
| `PAGE-059.33` | 718–751 | تابع | `role_permissions_save` | 1 | ⬜ |
| `PAGE-059.34` | 753–772 | تابع | `role_permissions_reset` | 1 | ⬜ |
| `PAGE-059.35` | 774–777 | تابع | `user_role_code` | 0 | ⬜ |
| `PAGE-059.36` | 779–783 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `PAGE-059.37` | 785–799 | تابع | `user_can` | 0 | ⬜ |
| `PAGE-059.38` | 801–809 | تابع | `user_can_any` | 0 | ⬜ |
| `PAGE-059.39` | 811–820 | تابع | `require_permission` | 0 | ⬜ |
| `PAGE-059.40` | 822–830 | تابع | `require_any_permission` | 0 | ⬜ |
| `PAGE-059.41` | 832–859 | تابع | `permission_icon` | 0 | ⬜ |
| `PAGE-059.42` | 861–865 | تابع | `staff_role_codes` | 0 | ⬜ |
| `PAGE-059.43` | 867–870 | تابع | `is_staff_role` | 0 | ⬜ |
| `PAGE-059.44` | 872–876 | تابع | `admin_role_codes` | 0 | ⬜ |
| `PAGE-059.45` | 878–881 | تابع | `is_admin_role` | 0 | ⬜ |
| `PAGE-059.46` | 883–888 | تابع | `global_ticket_role_codes` | 0 | ⬜ |
| `PAGE-059.47` | 890–893 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `PAGE-059.48` | 895–900 | تابع | `is_department_scoped_role` | 0 | ⬜ |
| `PAGE-059.49` | 902–906 | تابع | `assignable_role_codes` | 0 | ⬜ |
| `PAGE-059.50` | 908–911 | تابع | `is_assignable_role` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-060</b> — <code>preview/food-release-download/vite.config.js</code> (57 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-060.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `PAGE-060.02` | 14–57 | بلوک | `pathname` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-061</b> — <code>request-center.php</code> (14 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-061.01` | 1–14 | ناحیه | `بلوک رویه‌ای 1-14` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-062</b> — <code>routes/web.php</code> (36 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-062.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-062.02` | 8–11 | ناحیه | `Dashboard` | 0 | ⬜ |
| `PAGE-062.03` | 12–17 | ناحیه | `Tickets` | 0 | ⬜ |
| `PAGE-062.04` | 18–22 | ناحیه | `Users` | 0 | ⬜ |
| `PAGE-062.05` | 23–26 | ناحیه | `Settings` | 0 | ⬜ |
| `PAGE-062.06` | 27–30 | ناحیه | `Reports` | 0 | ⬜ |
| `PAGE-062.07` | 31–36 | ناحیه | `API` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-063</b> — <code>session_bootstrap.php</code> (112 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-063.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-063.02` | 4–67 | تابع | `app_session_start` | 3 | ⬜ |
| `PAGE-063.03` | 69–111 | تابع | `app_session_enforce_idle` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-064</b> — <code>system-health.php</code> (1,340 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-064.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `PAGE-064.02` | 13–23 | تابع | `system_health_ensure` | 0 | ⬜ |
| `PAGE-064.03` | 25–41 | تابع | `system_health_issue_filter` | 5 | ⬜ |
| `PAGE-064.04` | 43–57 | تابع | `system_health_issue_rows` | 5 | ⬜ |
| `PAGE-064.05` | 59–69 | تابع | `system_health_issue_total` | 0 | ⬜ |
| `PAGE-064.06` | 71–84 | تابع | `system_health_issue_history` | 5 | ⬜ |
| `PAGE-064.07` | 86–94 | تابع | `system_health_issue_history_total` | 0 | ⬜ |
| `PAGE-064.08` | 96–99 | تابع | `system_health_fingerprint` | 0 | ⬜ |
| `PAGE-064.09` | 101–121 | تابع | `system_health_table_label` | 0 | ⬜ |
| `PAGE-064.10` | 123–162 | تابع | `system_health_human_module` | 0 | ⬜ |
| `PAGE-064.11` | 164–174 | تابع | `system_health_human_level` | 0 | ⬜ |
| `PAGE-064.12` | 176–225 | تابع | `system_health_humanize_event` | 0 | ⬜ |
| `PAGE-064.13` | 227–250 | تابع | `system_health_event_reference` | 0 | ⬜ |
| `PAGE-064.14` | 252–270 | تابع | `system_health_request_label` | 0 | ⬜ |
| `PAGE-064.15` | 272–319 | تابع | `system_health_summary` | 0 | ⬜ |
| `PAGE-064.16` | 321–338 | تابع | `system_health_apply_check_state` | 0 | ⬜ |
| `PAGE-064.17` | 340–403 | تابع | `system_health_groups` | 0 | ⬜ |
| `PAGE-064.18` | 405–430 | تابع | `system_health_event_totals` | 0 | ⬜ |
| `PAGE-064.19` | 432–700 | تابع | `system_health_checks` | 4 | ⬜ |
| `PAGE-064.20` | 701–706 | ناحیه | `بلوک رویه‌ای 701-706` | 0 | ⬜ |
| `PAGE-064.21` | 707–737 | تابع | `sys_maint_cache_candidates` | 0 | ⬜ |
| `PAGE-064.22` | 739–755 | تابع | `sys_maint_cache_purge` | 5 | ⬜ |
| `PAGE-064.23` | 757–775 | تابع | `sys_maint_analyze_tables` | 0 | ⬜ |
| `PAGE-064.24` | 777–785 | تابع | `sys_maint_table_overview` | 0 | ⬜ |
| `PAGE-064.25` | 787–866 | تابع | `system_health_handle_post` | 2 | ⬜ |
| `PAGE-064.26` | 868–884 | تابع | `system_health_export_safe_text` | 0 | ⬜ |
| `PAGE-064.27` | 886–944 | تابع | `system_health_export_technical_xlsx` | 4 | ⬜ |
| `PAGE-064.28` | 947–1091 | تابع | `system_health_export_safe_xlsx` | 7 | ⬜ |
| `PAGE-064.29` | 1094–1116 | تابع | `system_health_request_timeline` | 0 | ⬜ |
| `PAGE-064.30` | 1118–1339 | تابع | `system_health_render` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-065</b> — <code>system-update.php</code> (415 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-065.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `PAGE-065.02` | 18–23 | تابع | `system_update_current_version` | 0 | ⬜ |
| `PAGE-065.03` | 25–34 | تابع | `system_update_ensure_table` | 4 | ⬜ |
| `PAGE-065.04` | 36–44 | تابع | `system_update_migration_applied` | 0 | ⬜ |
| `PAGE-065.05` | 46–77 | تابع | `system_update_requires_violation` | 0 | ⬜ |
| `PAGE-065.06` | 79–102 | تابع | `system_update_archive_path_is_safe` | 0 | ⬜ |
| `PAGE-065.07` | 104–150 | تابع | `system_update_validate_zip_entries` | 0 | ⬜ |
| `PAGE-065.08` | 152–191 | تابع | `system_update_verify_staged_package` | 0 | ⬜ |
| `PAGE-065.09` | 193–205 | تابع | `system_update_extract_package` | 0 | ⬜ |
| `PAGE-065.10` | 207–294 | تابع | `system_update_read_package` | 0 | ⬜ |
| `PAGE-065.11` | 295–297 | ناحیه | `بلوک رویه‌ای 295-297` | 0 | ⬜ |
| `PAGE-065.12` | 298–322 | تابع | `system_update_files_snapshot` | 1 | ⬜ |
| `PAGE-065.13` | 324–414 | تابع | `system_update_apply` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-066</b> — <code>traffic-control.php</code> (1,445 خط، 67 آدرس، بازبینی‌شده 0/67)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-066.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-066.02` | 8–8 | ناحیه | `کنترل تردد مراجعین و میهمانان — پورت‌شده از کنترل_` | 0 | ⬜ |
| `PAGE-066.03` | 9–10 | ناحیه | `همه‌ی توابع با پیشوند traffic_ برای جلوگیری از تدا` | 0 | ⬜ |
| `PAGE-066.04` | 11–14 | تابع | `traffic_can_manage` | 0 | ⬜ |
| `PAGE-066.05` | 16–19 | تابع | `traffic_can_view_destinations` | 0 | ⬜ |
| `PAGE-066.06` | 21–24 | تابع | `traffic_can_manage_destinations` | 0 | ⬜ |
| `PAGE-066.07` | 26–30 | تابع | `traffic_destinations` | 0 | ⬜ |
| `PAGE-066.08` | 32–35 | تابع | `traffic_approvers` | 0 | ⬜ |
| `PAGE-066.09` | 37–48 | تابع | `traffic_destination_row_approvers` | 0 | ⬜ |
| `PAGE-066.10` | 50–75 | تابع | `traffic_destination_approvers` | 0 | ⬜ |
| `PAGE-066.11` | 77–85 | تابع | `traffic_destination_id` | 0 | ⬜ |
| `PAGE-066.12` | 87–101 | تابع | `traffic_destination_approver` | 0 | ⬜ |
| `PAGE-066.13` | 103–103 | ناحیه | `شماره برگ به‌صورت تاریخ‌شمسی+شماره‌ترتیبی روزانه، ` | 0 | ⬜ |
| `PAGE-066.14` | 104–112 | تابع | `traffic_next_serial` | 0 | ⬜ |
| `PAGE-066.15` | 114–117 | تابع | `traffic_find_last_visit` | 0 | ⬜ |
| `PAGE-066.16` | 119–129 | تابع | `traffic_find_last_visit_by` | 5 | ⬜ |
| `PAGE-066.17` | 131–137 | تابع | `traffic_fetch_record` | 0 | ⬜ |
| `PAGE-066.18` | 139–155 | تابع | `traffic_search_condition` | 0 | ⬜ |
| `PAGE-066.19` | 157–171 | تابع | `traffic_query_records` | 5 | ⬜ |
| `PAGE-066.20` | 173–191 | تابع | `traffic_stats` | 0 | ⬜ |
| `PAGE-066.21` | 193–201 | تابع | `traffic_empty_stats` | 0 | ⬜ |
| `PAGE-066.22` | 203–226 | تابع | `traffic_report_criteria` | 0 | ⬜ |
| `PAGE-066.23` | 228–241 | تابع | `traffic_report_records` | 0 | ⬜ |
| `PAGE-066.24` | 243–250 | تابع | `traffic_report_columns` | 0 | ⬜ |
| `PAGE-066.25` | 252–260 | تابع | `traffic_report_selected_fields` | 0 | ⬜ |
| `PAGE-066.26` | 262–275 | تابع | `traffic_report_item` | 0 | ⬜ |
| `PAGE-066.27` | 277–301 | تابع | `traffic_export_report` | 0 | ⬜ |
| `PAGE-066.28` | 303–313 | کلاس | `TrafficInputException` | 0 | ⬜ |
| `PAGE-066.29` | 315–323 | تابع | `traffic_phone_normalized` | 0 | ⬜ |
| `PAGE-066.30` | 325–328 | تابع | `traffic_phone_digits` | 0 | ⬜ |
| `PAGE-066.31` | 330–343 | تابع | `traffic_phone_tail` | 0 | ⬜ |
| `PAGE-066.32` | 345–373 | تابع | `traffic_validate_payload` | 0 | ⬜ |
| `PAGE-066.33` | 375–383 | تابع | `traffic_visit_snapshot` | 0 | ⬜ |
| `PAGE-066.34` | 385–407 | تابع | `traffic_record_history` | 1 | ⬜ |
| `PAGE-066.35` | 409–419 | تابع | `traffic_history_records` | 5 | ⬜ |
| `PAGE-066.36` | 421–424 | تابع | `traffic_history_count` | 0 | ⬜ |
| `PAGE-066.37` | 426–435 | تابع | `traffic_history_field_label` | 0 | ⬜ |
| `PAGE-066.38` | 437–446 | تابع | `traffic_normalize_time_input` | 0 | ⬜ |
| `PAGE-066.39` | 448–466 | تابع | `traffic_history_value` | 0 | ⬜ |
| `PAGE-066.40` | 468–488 | تابع | `traffic_create_record` | 2 | ⬜ |
| `PAGE-066.41` | 490–544 | تابع | `traffic_update_record` | 2 | ⬜ |
| `PAGE-066.42` | 546–569 | تابع | `traffic_delete_record` | 2 | ⬜ |
| `PAGE-066.43` | 571–620 | تابع | `traffic_set_exit` | 2 | ⬜ |
| `PAGE-066.44` | 622–648 | تابع | `traffic_ensure_notice_schema` | 4 | ⬜ |
| `PAGE-066.45` | 649–651 | ناحیه | `بلوک رویه‌ای 649-651` | 0 | ⬜ |
| `PAGE-066.46` | 652–681 | تابع | `traffic_notice_create` | 2 | ⬜ |
| `PAGE-066.47` | 683–694 | تابع | `traffic_destination_posted_approvers` | 2 | ⬜ |
| `PAGE-066.48` | 696–714 | تابع | `traffic_save_destination` | 3 | ⬜ |
| `PAGE-066.49` | 716–742 | تابع | `traffic_update_destination` | 3 | ⬜ |
| `PAGE-066.50` | 744–752 | تابع | `traffic_delete_destination` | 3 | ⬜ |
| `PAGE-066.51` | 754–763 | تابع | `traffic_form_old_values` | 2 | ⬜ |
| `PAGE-066.52` | 765–778 | تابع | `traffic_remember_form_error` | 2 | ⬜ |
| `PAGE-066.53` | 780–784 | تابع | `traffic_open_visits` | 0 | ⬜ |
| `PAGE-066.54` | 786–857 | تابع | `traffic_handle_post` | 2 | ⬜ |
| `PAGE-066.55` | 859–859 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-066.56` | 860–860 | ناحیه | `جست‌وجوی زنده با کد ملی (AJAX) — معادل txtNational` | 0 | ⬜ |
| `PAGE-066.57` | 861–861 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-066.58` | 862–886 | تابع | `traffic_history_rows_html` | 0 | ⬜ |
| `PAGE-066.59` | 888–976 | تابع | `traffic_api_handle` | 2 | ⬜ |
| `PAGE-066.60` | 978–978 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-066.61` | 979–979 | ناحیه | `صفحه‌ی اصلی: زیرفرم‌های داشبورد / ثبت تردد / سوابق` | 0 | ⬜ |
| `PAGE-066.62` | 980–980 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-066.63` | 981–1377 | تابع | `traffic_render_page` | 6 | ⬜ |
| `PAGE-066.64` | 1379–1379 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-066.65` | 1380–1380 | ناحیه | `چاپ برگ ملاقات — معادل PrintTemplate + cmdPrintPas` | 0 | ⬜ |
| `PAGE-066.66` | 1381–1381 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-066.67` | 1382–1444 | تابع | `traffic_render_print` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-067</b> — <code>traffic-import.php</code> (1,032 خط، 27 آدرس، بازبینی‌شده 0/27)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-067.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `PAGE-067.02` | 18–41 | تابع | `traffic_import_column_aliases` | 0 | ⬜ |
| `PAGE-067.03` | 43–82 | تابع | `traffic_import_detect_header` | 0 | ⬜ |
| `PAGE-067.04` | 84–100 | تابع | `traffic_import_date_from_parts` | 0 | ⬜ |
| `PAGE-067.05` | 102–107 | تابع | `traffic_import_seconds_to_time` | 0 | ⬜ |
| `PAGE-067.06` | 109–166 | تابع | `traffic_import_parse_date_value` | 0 | ⬜ |
| `PAGE-067.07` | 168–220 | تابع | `traffic_import_parse_time` | 0 | ⬜ |
| `PAGE-067.08` | 222–251 | تابع | `traffic_import_parse_bool` | 0 | ⬜ |
| `PAGE-067.09` | 253–256 | تابع | `traffic_import_text_length` | 0 | ⬜ |
| `PAGE-067.10` | 258–264 | تابع | `traffic_import_optional_marker` | 0 | ⬜ |
| `PAGE-067.11` | 266–419 | تابع | `traffic_import_parse_sheet` | 1 | ⬜ |
| `PAGE-067.12` | 421–453 | تابع | `traffic_import_xlsx_archive_error` | 0 | ⬜ |
| `PAGE-067.13` | 455–521 | تابع | `traffic_import_csv_shape_error` | 1 | ⬜ |
| `PAGE-067.14` | 523–588 | تابع | `traffic_import_read_upload` | 0 | ⬜ |
| `PAGE-067.15` | 590–598 | تابع | `traffic_import_identity` | 0 | ⬜ |
| `PAGE-067.16` | 600–603 | تابع | `traffic_import_serial_key` | 0 | ⬜ |
| `PAGE-067.17` | 605–612 | تابع | `traffic_import_generated_serial` | 0 | ⬜ |
| `PAGE-067.18` | 614–618 | تابع | `traffic_import_generated_serial_prefix` | 0 | ⬜ |
| `PAGE-067.19` | 620–730 | تابع | `traffic_import_build_plan` | 1 | ⬜ |
| `PAGE-067.20` | 732–801 | تابع | `traffic_import_plan_from_database` | 6 | ⬜ |
| `PAGE-067.21` | 803–836 | تابع | `traffic_import_preview_session_data` | 0 | ⬜ |
| `PAGE-067.22` | 838–851 | تابع | `traffic_import_current_preview` | 0 | ⬜ |
| `PAGE-067.23` | 853–942 | تابع | `traffic_import_handle_post` | 2 | ⬜ |
| `PAGE-067.24` | 944–985 | تابع | `traffic_import_apply_plan_in_transaction` | 1 | ⬜ |
| `PAGE-067.25` | 987–993 | تابع | `traffic_import_template_headers` | 0 | ⬜ |
| `PAGE-067.26` | 995–1006 | تابع | `traffic_import_template_csv_bytes` | 0 | ⬜ |
| `PAGE-067.27` | 1008–1031 | تابع | `traffic_import_download_template` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-068</b> — <code>user-access-groups.php</code> (209 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-068.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-068.02` | 4–28 | تابع | `user_access_groups_schema_ready` | 0 | ⬜ |
| `PAGE-068.03` | 30–49 | تابع | `user_access_groups_all` | 0 | ⬜ |
| `PAGE-068.04` | 51–60 | تابع | `user_access_groups_admin_users` | 0 | ⬜ |
| `PAGE-068.05` | 62–76 | تابع | `user_access_group_member_set` | 0 | ⬜ |
| `PAGE-068.06` | 78–96 | تابع | `user_access_groups_member_sets` | 0 | ⬜ |
| `PAGE-068.07` | 98–117 | تابع | `user_access_groups_permission_sets` | 0 | ⬜ |
| `PAGE-068.08` | 119–133 | تابع | `user_access_group_permission_set_for_group` | 0 | ⬜ |
| `PAGE-068.09` | 135–138 | تابع | `user_access_group_role_label` | 0 | ⬜ |
| `PAGE-068.10` | 140–199 | تابع | `user_access_group_permission_matrix_markup` | 0 | ⬜ |
| `PAGE-068.11` | 201–208 | تابع | `user_access_groups_redirect` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-069</b> — <code>xlsx-writer.php</code> (221 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-069.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-069.02` | 4–27 | تابع | `xlsx_col_name` | 0 | ⬜ |
| `PAGE-069.03` | 29–33 | تابع | `xlsx_clean_text` | 0 | ⬜ |
| `PAGE-069.04` | 35–42 | تابع | `xlsx_sheet_name` | 0 | ⬜ |
| `PAGE-069.05` | 44–137 | تابع | `xlsx_build` | 6 | ⬜ |
| `PAGE-069.06` | 139–142 | تابع | `xlsx_xml` | 0 | ⬜ |
| `PAGE-069.07` | 144–207 | تابع | `xlsx_sheet_xml` | 0 | ⬜ |
| `PAGE-069.08` | 209–220 | تابع | `xlsx_send` | 3 | ⬜ |

</details>

### دارایی‌های ایستا (assets/) — `ASSET`


<details>
<summary><b>ASSET-001</b> — <code>assets/admin-layout-design.css</code> (79 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-001.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `ASSET-001.02` | 2–29 | بلوک | `Streamlined form layout editor: common controls st` | 1 | ⬜ |
| `ASSET-001.03` | 30–40 | بلوک | `Access-like visual design surface with a floating ` | 0 | ⬜ |
| `ASSET-001.04` | 41–54 | بلوک | `Pixel-position controls for Access-style visual fo` | 0 | ⬜ |
| `ASSET-001.05` | 55–64 | بلوک | `Eight Access-style resize grips around the selecte` | 0 | ⬜ |
| `ASSET-001.06` | 65–79 | بلوک | `The side design panel, not its launcher button, is` | 0 | ⬜ |

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
<summary><b>ASSET-003</b> — <code>assets/app.js</code> (1,468 خط، 67 آدرس، بازبینی‌شده 0/67)</summary>

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
| `ASSET-003.17` | 209–228 | بلوک | `bindSettingsTabs` | 0 | ⬜ |
| `ASSET-003.18` | 229–249 | بلوک | `activate` | 0 | ⬜ |
| `ASSET-003.19` | 250–256 | بلوک | `bindRolesBoard` | 0 | ⬜ |
| `ASSET-003.20` | 257–270 | بلوک | `activate` | 0 | ⬜ |
| `ASSET-003.21` | 271–287 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-003.22` | 288–305 | بلوک | `refreshGroup` | 0 | ⬜ |
| `ASSET-003.23` | 306–319 | بلوک | `syncEdits` | 0 | ⬜ |
| `ASSET-003.24` | 320–434 | بلوک | `refreshChanges` | 0 | ⬜ |
| `ASSET-003.25` | 435–495 | بلوک | `refreshMembers` | 0 | ⬜ |
| `ASSET-003.26` | 496–499 | بلوک | `activeInputs` | 0 | ⬜ |
| `ASSET-003.27` | 500–560 | بلوک | `applyCopy` | 0 | ⬜ |
| `ASSET-003.28` | 561–573 | بلوک | `bindSettingsUserSearch` | 0 | ⬜ |
| `ASSET-003.29` | 574–576 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-003.30` | 577–594 | بلوک | `update` | 1 | ⬜ |
| `ASSET-003.31` | 595–600 | بلوک | `lacNorm` | 0 | ⬜ |
| `ASSET-003.32` | 601–629 | بلوک | `lacFilter` | 0 | ⬜ |
| `ASSET-003.33` | 630–649 | بلوک | `getGroup` | 0 | ⬜ |
| `ASSET-003.34` | 650–679 | بلوک | `rebuildServiceSelect` | 0 | ⬜ |
| `ASSET-003.35` | 680–684 | بلوک | `toggleServiceFields` | 0 | ⬜ |
| `ASSET-003.36` | 685–688 | بلوک | `updateTicketNumber` | 0 | ⬜ |
| `ASSET-003.37` | 689–697 | بلوک | `filterCategories` | 0 | ⬜ |
| `ASSET-003.38` | 698–761 | بلوک | `syncTicketForm` | 0 | ⬜ |
| `ASSET-003.39` | 762–776 | بلوک | `comboNormalize` | 0 | ⬜ |
| `ASSET-003.40` | 777–782 | بلوک | `comboClose` | 0 | ⬜ |
| `ASSET-003.41` | 783–789 | بلوک | `comboHighlight` | 0 | ⬜ |
| `ASSET-003.42` | 790–805 | بلوک | `comboPick` | 0 | ⬜ |
| `ASSET-003.43` | 806–946 | بلوک | `comboRender` | 1 | ⬜ |
| `ASSET-003.44` | 947–947 | بلوک | `pad` | 0 | ⬜ |
| `ASSET-003.45` | 948–965 | بلوک | `jalaliToGregorian` | 0 | ⬜ |
| `ASSET-003.46` | 966–971 | بلوک | `leap` | 0 | ⬜ |
| `ASSET-003.47` | 972–993 | بلوک | `gregorianToJalali` | 0 | ⬜ |
| `ASSET-003.48` | 994–1000 | بلوک | `monthLength` | 0 | ⬜ |
| `ASSET-003.49` | 1001–1009 | بلوک | `parseJalali` | 0 | ⬜ |
| `ASSET-003.50` | 1010–1012 | بلوک | `renderCalendar` | 0 | ⬜ |
| `ASSET-003.51` | 1013–1018 | بلوک | `startIndex` | 0 | ⬜ |
| `ASSET-003.52` | 1019–1046 | بلوک | `weekdayIndex` | 0 | ⬜ |
| `ASSET-003.53` | 1047–1059 | بلوک | `activate` | 4 | ⬜ |
| `ASSET-003.54` | 1060–1074 | بلوک | `hash` | 0 | ⬜ |
| `ASSET-003.55` | 1075–1080 | بلوک | `visibleBoxes` | 0 | ⬜ |
| `ASSET-003.56` | 1081–1107 | بلوک | `sync` | 0 | ⬜ |
| `ASSET-003.57` | 1108–1118 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-003.58` | 1119–1155 | بلوک | `apply` | 0 | ⬜ |
| `ASSET-003.59` | 1156–1188 | بلوک | `updateSelection` | 0 | ⬜ |
| `ASSET-003.60` | 1189–1250 | بلوک | `syncOrgFields` | 1 | ⬜ |
| `ASSET-003.61` | 1251–1288 | بلوک | `closeModal` | 0 | ⬜ |
| `ASSET-003.62` | 1289–1311 | بلوک | `activate` | 0 | ⬜ |
| `ASSET-003.63` | 1312–1345 | بلوک | `syncItem` | 0 | ⬜ |
| `ASSET-003.64` | 1346–1355 | بلوک | `پینگ سیستم‌های انتخاب‌شده: ارسال تکه‌تکه (۴۰ تایی)` | 0 | ⬜ |
| `ASSET-003.65` | 1356–1362 | بلوک | `setBadge` | 0 | ⬜ |
| `ASSET-003.66` | 1363–1380 | بلوک | `refreshOnlineTotal` | 0 | ⬜ |
| `ASSET-003.67` | 1381–1468 | بلوک | `next` | 0 | ⬜ |

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
<summary><b>ASSET-006</b> — <code>assets/custom.css</code> (218 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-006.01` | 1–2 | بلوک | `Custom Styles for Persian Ticketing System` | 0 | ⬜ |
| `ASSET-006.02` | 3–30 | بلوک | `Dashboard Grid` | 0 | ⬜ |
| `ASSET-006.03` | 31–45 | بلوک | `Page Header` | 0 | ⬜ |
| `ASSET-006.04` | 46–79 | بلوک | `Forms` | 0 | ⬜ |
| `ASSET-006.05` | 80–105 | بلوک | `Buttons` | 0 | ⬜ |
| `ASSET-006.06` | 106–121 | بلوک | `Badges` | 0 | ⬜ |
| `ASSET-006.07` | 122–135 | بلوک | `Ticket List` | 0 | ⬜ |
| `ASSET-006.08` | 136–158 | بلوک | `Ticket Detail` | 0 | ⬜ |
| `ASSET-006.09` | 159–178 | بلوک | `Ticket Reply` | 0 | ⬜ |
| `ASSET-006.10` | 179–195 | بلوک | `Filters` | 0 | ⬜ |
| `ASSET-006.11` | 196–205 | بلوک | `Footer` | 0 | ⬜ |
| `ASSET-006.12` | 206–218 | بلوک | `Responsive` | 1 | ⬜ |

</details>

<details>
<summary><b>ASSET-007</b> — <code>assets/food-menu.css</code> (354 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-007.01` | 1–69 | بلوک | `مدیریت برنامه غذایی — بدون وابستگی بیرونی، هم‌خوان` | 0 | ⬜ |
| `ASSET-007.02` | 70–205 | بلوک | `ستون ردیف همیشه یکخطی بماند — حتی اعداد سه رقمی` | 4 | ⬜ |
| `ASSET-007.03` | 206–212 | بلوک | `تمام جدول‌ها و ردیف‌های فهرستی برنامه در پنل، وسط‌` | 0 | ⬜ |
| `ASSET-007.04` | 213–227 | بلوک | `بانک غذا: دسته‌بندی، جستجوی زنده، و فهرست یک‌خطی` | 0 | ⬜ |
| `ASSET-007.05` | 228–240 | بلوک | `لیست کارکنان (غیبت و اعضای گروه): ۴ ستون، تیک قبل ` | 3 | ⬜ |
| `ASSET-007.06` | 241–244 | بلوک | `جعبهٔ انتخاب غذا: هم‌اندازهٔ فرم غیبت` | 0 | ⬜ |
| `ASSET-007.07` | 245–254 | بلوک | `بانک غذا: هر غذا در یک خط (نام \| دسته‌بندی \| وضع` | 2 | ⬜ |
| `ASSET-007.08` | 255–265 | بلوک | `۱.۵۲: دسته‌بندی‌های ریشه در بانک غذا (+ باز / - جم` | 1 | ⬜ |
| `ASSET-007.09` | 266–271 | بلوک | `۱.۵۲: سفارش اضطراری — مراحل` | 1 | ⬜ |
| `ASSET-007.10` | 272–275 | بلوک | `جستجوی زنده غذا در ویرایش برنامهٔ روز` | 0 | ⬜ |
| `ASSET-007.11` | 276–282 | بلوک | `۱.۵۶ — مرتب‌سازی ستون‌ها و گروه‌بندی سفارش‌های روز` | 0 | ⬜ |
| `ASSET-007.12` | 283–286 | بلوک | `عملیات گروهی سفارش/تغییر سلف برای مدیران غذا` | 1 | ⬜ |
| `ASSET-007.13` | 287–328 | بلوک | `ثبت دستی تردد در تنظیمات سیستم` | 1 | ⬜ |
| `ASSET-007.14` | 329–346 | بلوک | `Selectable employee checklist used by the live, bo` | 1 | ⬜ |
| `ASSET-007.15` | 347–354 | بلوک | `بلوکبندی ۲/۳ ستونه: همهٔ ستونها دقیقاً همعرض، بدون` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-008</b> — <code>assets/food-menu.js</code> (2,479 خط، 152 آدرس، بازبینی‌شده 0/152)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-008.01` | 1–100 | بلوک | `مدیر برنامهٔ غذایی در پنل چاپ فیش.` | 0 | ⬜ |
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
| `ASSET-008.92` | 1331–1350 | بلوک | `loadEmbedded` | 0 | ⬜ |
| `ASSET-008.93` | 1351–1358 | بلوک | `renderFocusKeyOf` | 0 | ⬜ |
| `ASSET-008.94` | 1359–1370 | بلوک | `captureRenderFocus` | 4 | ⬜ |
| `ASSET-008.95` | 1371–1383 | بلوک | `restoreRenderFocus` | 4 | ⬜ |
| `ASSET-008.96` | 1384–1395 | بلوک | `render` | 0 | ⬜ |
| `ASSET-008.97` | 1396–1402 | بلوک | `notices` | 0 | ⬜ |
| `ASSET-008.98` | 1403–1414 | بلوک | `loadStatus` | 0 | ⬜ |
| `ASSET-008.99` | 1415–1421 | بلوک | `loadCatalog` | 0 | ⬜ |
| `ASSET-008.100` | 1422–1429 | بلوک | `loadMonth` | 0 | ⬜ |
| `ASSET-008.101` | 1430–1451 | بلوک | `loadOrders` | 0 | ⬜ |
| `ASSET-008.102` | 1452–1472 | بلوک | `loadFoodGroups` | 0 | ⬜ |
| `ASSET-008.103` | 1473–1503 | بلوک | `loadGroupOrders` | 0 | ⬜ |
| `ASSET-008.104` | 1504–1520 | بلوک | `loadCancelRequests` | 0 | ⬜ |
| `ASSET-008.105` | 1521–1539 | بلوک | `loadGuestRequests` | 0 | ⬜ |
| `ASSET-008.106` | 1540–1540 | بلوک | `beginGuestEdit` | 0 | ⬜ |
| `ASSET-008.107` | 1541–1556 | بلوک | `row` | 0 | ⬜ |
| `ASSET-008.108` | 1557–1562 | بلوک | `cancelGuestEdit` | 0 | ⬜ |
| `ASSET-008.109` | 1563–1594 | بلوک | `saveGuestEdit` | 1 | ⬜ |
| `ASSET-008.110` | 1595–1595 | بلوک | `guestRequestAction` | 0 | ⬜ |
| `ASSET-008.111` | 1596–1618 | بلوک | `row` | 1 | ⬜ |
| `ASSET-008.112` | 1619–1621 | بلوک | `renderGuestRequestManager` | 0 | ⬜ |
| `ASSET-008.113` | 1622–1644 | بلوک | `rows` | 1 | ⬜ |
| `ASSET-008.114` | 1645–1659 | بلوک | `decideCancelRequest` | 0 | ⬜ |
| `ASSET-008.115` | 1660–1666 | بلوک | `loadStatistics` | 0 | ⬜ |
| `ASSET-008.116` | 1667–1700 | بلوک | `selectedIds` | 0 | ⬜ |
| `ASSET-008.117` | 1701–1728 | بلوک | `searchSelectedReportEmployees` | 0 | ⬜ |
| `ASSET-008.118` | 1729–1755 | بلوک | `refreshAll` | 0 | ⬜ |
| `ASSET-008.119` | 1756–1800 | بلوک | `saveDay` | 0 | ⬜ |
| `ASSET-008.120` | 1801–1826 | بلوک | `saveCatalog` | 0 | ⬜ |
| `ASSET-008.121` | 1827–1846 | بلوک | `orderExportRows` | 0 | ⬜ |
| `ASSET-008.122` | 1847–1863 | بلوک | `downloadOrdersCsv` | 0 | ⬜ |
| `ASSET-008.123` | 1864–1868 | بلوک | `downloadOrders` | 0 | ⬜ |
| `ASSET-008.124` | 1869–1870 | بلوک | `printScopedReport` | 0 | ⬜ |
| `ASSET-008.125` | 1871–1881 | بلوک | `target` | 0 | ⬜ |
| `ASSET-008.126` | 1882–1899 | بلوک | `cleanup` | 0 | ⬜ |
| `ASSET-008.127` | 1900–1904 | بلوک | `printOrders` | 0 | ⬜ |
| `ASSET-008.128` | 1905–1911 | بلوک | `downloadStatistics` | 0 | ⬜ |
| `ASSET-008.129` | 1912–1916 | بلوک | `printStatistics` | 0 | ⬜ |
| `ASSET-008.130` | 1917–1924 | بلوک | `selectDay` | 0 | ⬜ |
| `ASSET-008.131` | 1925–1930 | بلوک | `changeMonth` | 0 | ⬜ |
| `ASSET-008.132` | 1931–1967 | بلوک | `switchTab` | 0 | ⬜ |
| `ASSET-008.133` | 1968–1981 | بلوک | `emergencyEnsurePeople` | 0 | ⬜ |
| `ASSET-008.134` | 1982–1990 | بلوک | `emergencyPanel` | 0 | ⬜ |
| `ASSET-008.135` | 1991–2025 | بلوک | `msgs` | 0 | ⬜ |
| `ASSET-008.136` | 2026–2031 | بلوک | `emergencyLoadOrders` | 0 | ⬜ |
| `ASSET-008.137` | 2032–2076 | بلوک | `daySearchInput` | 4 | ⬜ |
| `ASSET-008.138` | 2077–2087 | بلوک | `emergencyClick` | 0 | ⬜ |
| `ASSET-008.139` | 2088–2116 | بلوک | `person` | 0 | ⬜ |
| `ASSET-008.140` | 2117–2155 | بلوک | `eventClick` | 0 | ⬜ |
| `ASSET-008.141` | 2156–2251 | بلوک | `person` | 1 | ⬜ |
| `ASSET-008.142` | 2252–2260 | بلوک | `dayPickFor` | 0 | ⬜ |
| `ASSET-008.143` | 2261–2267 | بلوک | `trackDayPick` | 0 | ⬜ |
| `ASSET-008.144` | 2268–2275 | بلوک | `orderedDayFoodIds` | 0 | ⬜ |
| `ASSET-008.145` | 2276–2299 | بلوک | `eventChange` | 0 | ⬜ |
| `ASSET-008.146` | 2300–2333 | بلوک | `person` | 0 | ⬜ |
| `ASSET-008.147` | 2334–2416 | بلوک | `eventSubmit` | 0 | ⬜ |
| `ASSET-008.148` | 2417–2422 | بلوک | `spanDays` | 0 | ⬜ |
| `ASSET-008.149` | 2423–2451 | بلوک | `mount` | 0 | ⬜ |
| `ASSET-008.150` | 2452–2452 | بلوک | `reportInputFocused` | 0 | ⬜ |
| `ASSET-008.151` | 2453–2469 | بلوک | `safeReportRender` | 0 | ⬜ |
| `ASSET-008.152` | 2470–2479 | بلوک | `html` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-009</b> — <code>assets/food-order-calendar.js</code> (103 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-009.01` | 1–7 | بلوک | `Shared Jalali calendar helpers for the employee fo` | 0 | ⬜ |
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
| `ASSET-010.01` | 1–7 | بلوک | `سفارش غذای مهمان (فقط مدیران و بالاتر): از دکمهٔ ت` | 0 | ⬜ |
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
<summary><b>ASSET-011</b> — <code>assets/food-order.css</code> (213 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-011.01` | 1–110 | بلوک | `سفارش غذا — از همان رنگ‌ها، کارت‌ها و فرم‌های asse` | 2 | ⬜ |
| `ASSET-011.02` | 111–115 | بلوک | `تأیید عملیات در DOM پایدار می‌ماند تا بازخوانی تقو` | 0 | ⬜ |
| `ASSET-011.03` | 116–168 | بلوک | `انتخاب شخص پیش از فعال‌شدن تقویم؛ منوی اصلی سامانه` | 1 | ⬜ |
| `ASSET-011.04` | 169–174 | بلوک | `نام غذا در سلول تقویم در یک خط نمایش داده می‌شود؛ ` | 0 | ⬜ |
| `ASSET-011.05` | 175–177 | بلوک | `Separate lunch and dinner without changing the exi` | 0 | ⬜ |
| `ASSET-011.06` | 178–178 | بلوک | `فهرست شخصی درخواست مهمان: فضای محدود، خوانا و مستق` | 0 | ⬜ |
| `ASSET-011.07` | 179–190 | بلوک | `A single compact row per request. The table scroll` | 0 | ⬜ |
| `ASSET-011.08` | 191–213 | بلوک | `Daily food reconciliation and monthly invoice reco` | 2 | ⬜ |

</details>

<details>
<summary><b>ASSET-012</b> — <code>assets/food-order.js</code> (1,115 خط، 64 آدرس، بازبینی‌شده 0/64)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-012.01` | 1–14 | بلوک | `صفحهٔ کارمند: ثبت سفارش برای خود/نیابت، بدون افشای` | 0 | ⬜ |
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
<summary><b>ASSET-014</b> — <code>assets/jalali-calendar.js</code> (347 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-014.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-014.02` | 13–14 | بلوک | `pad` | 0 | ⬜ |
| `ASSET-014.03` | 15–44 | بلوک | `j2g` | 0 | ⬜ |
| `ASSET-014.04` | 45–66 | بلوک | `g2j` | 0 | ⬜ |
| `ASSET-014.05` | 67–73 | بلوک | `monthLength` | 0 | ⬜ |
| `ASSET-014.06` | 74–81 | بلوک | `parseJalali` | 0 | ⬜ |
| `ASSET-014.07` | 82–85 | بلوک | `formatJalali` | 0 | ⬜ |
| `ASSET-014.08` | 86–99 | بلوک | `holidaySet` | 0 | ⬜ |
| `ASSET-014.09` | 100–100 | بلوک | `weekendDays` | 0 | ⬜ |
| `ASSET-014.10` | 101–109 | بلوک | `raw` | 0 | ⬜ |
| `ASSET-014.11` | 110–115 | بلوک | `isWeekendJalali` | 0 | ⬜ |
| `ASSET-014.12` | 116–120 | بلوک | `todayJ` | 0 | ⬜ |
| `ASSET-014.13` | 121–134 | بلوک | `bindPicker` | 0 | ⬜ |
| `ASSET-014.14` | 135–135 | بلوک | `close` | 0 | ⬜ |
| `ASSET-014.15` | 136–137 | بلوک | `open` | 0 | ⬜ |
| `ASSET-014.16` | 138–143 | بلوک | `render` | 0 | ⬜ |
| `ASSET-014.17` | 144–215 | بلوک | `startIndex` | 0 | ⬜ |
| `ASSET-014.18` | 216–227 | بلوک | `installOutsideClickHandler` | 0 | ⬜ |
| `ASSET-014.19` | 228–234 | بلوک | `bindYearBoard` | 0 | ⬜ |
| `ASSET-014.20` | 235–243 | بلوک | `render` | 0 | ⬜ |
| `ASSET-014.21` | 244–280 | بلوک | `startIndex` | 0 | ⬜ |
| `ASSET-014.22` | 281–347 | بلوک | `enhanceAllDateFields` | 0 | ⬜ |

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
<summary><b>ASSET-016</b> — <code>assets/logging-dashboard.css</code> (19 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-016.01` | 1–19 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

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
| `ASSET-020.01` | 1–4 | بلوک | `کنترل تب ذرات معلق و پیش‌نمایش زندهٔ آن در تنظیمات` | 0 | ⬜ |
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
<summary><b>ASSET-022</b> — <code>assets/style.css</code> (1,865 خط، 49 آدرس، بازبینی‌شده 0/49)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-022.01` | 1–156 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `ASSET-022.02` | 157–316 | بلوک | `سازمانی کرم، طلایی و سبز زمردی` | 4 | ⬜ |
| `ASSET-022.03` | 317–436 | بلوک | `CD/DVD and food-ticket reference forms` | 3 | ⬜ |
| `ASSET-022.04` | 437–501 | بلوک | `Refined organization header and visual identity co` | 3 | ⬜ |
| `ASSET-022.05` | 502–545 | بلوک | `Application navigation styled after the supplied m` | 2 | ⬜ |
| `ASSET-022.06` | 546–558 | بلوک | `Reusable decorative arcs: each size and placement ` | 1 | ⬜ |
| `ASSET-022.07` | 559–606 | بلوک | `Asset profile / شناسنامه حرفه‌ای` | 1 | ⬜ |
| `ASSET-022.08` | 607–654 | بلوک | `تقویم جلالی مشترک` | 0 | ⬜ |
| `ASSET-022.09` | 655–667 | بلوک | `سالانه تعطیلات` | 0 | ⬜ |
| `ASSET-022.10` | 668–864 | بلوک | `طراح فیش` | 5 | ⬜ |
| `ASSET-022.11` | 865–954 | بلوک | `چارت سازمانی تعاملی` | 2 | ⬜ |
| `ASSET-022.12` | 955–990 | بلوک | `نسخهٔ فرم سازمان با تم اصلی سامانه` | 3 | ⬜ |
| `ASSET-022.13` | 991–1002 | بلوک | `کنترل تردد مراجعین و میهمانان` | 1 | ⬜ |
| `ASSET-022.14` | 1003–1217 | بلوک | `کنترل تردد: نمودار خوانا، فرم جمع‌وجور و داشبورد چ` | 5 | ⬜ |
| `ASSET-022.15` | 1218–1222 | بلوک | `کارت‌های آماری داشبورد که با کلیک به گزارش فیلترشد` | 0 | ⬜ |
| `ASSET-022.16` | 1223–1233 | بلوک | `۱.۳۷ — کمبوی زندهٔ «سیستم مرتبط» در فرم ثبت درخواس` | 0 | ⬜ |
| `ASSET-022.17` | 1234–1238 | بلوک | `۱.۳۷ — فیلتر زندهٔ «شناسنامه‌های فنی سیستم»` | 0 | ⬜ |
| `ASSET-022.18` | 1239–1247 | بلوک | `۱.۳۷ — کارت «دلایل شکست استخراج و راه‌حل» در صفحهٔ` | 0 | ⬜ |
| `ASSET-022.19` | 1248–1261 | بلوک | `۱.۳۷ — کادر «تراز دقیق لبه‌های کاغذ» و گزارش آزمون` | 0 | ⬜ |
| `ASSET-022.20` | 1262–1267 | بلوک | `۱.۳۷.۲ — خطای درون‌خطی فیلد (مثلاً الزام «سیستم مر` | 0 | ⬜ |
| `ASSET-022.21` | 1268–1274 | بلوک | `۱.۳۷.۴ — کادرهای تازهٔ پنل غذا و جدول نتیجهٔ ایمپو` | 0 | ⬜ |
| `ASSET-022.22` | 1275–1288 | بلوک | `۱.۳۷.۵ — نام کوتاه کامپیوترها: نشانِ کوچکِ «دامنه»` | 0 | ⬜ |
| `ASSET-022.23` | 1289–1346 | بلوک | `تم‌های قابل انتخاب کاربر — فقط رنگ و تزئینات؛ هیچ ` | 2 | ⬜ |
| `ASSET-022.24` | 1347–1364 | بلوک | `نقش‌های تزئینی اختصاصی هر تم (pseudo-elements و شک` | 0 | ⬜ |
| `ASSET-022.25` | 1365–1384 | بلوک | `سامانه: سلول‌های جدول و ردیف‌های فهرست داده‌ای در ` | 0 | ⬜ |
| `ASSET-022.26` | 1385–1430 | بلوک | `چاپ CD/DVD فقط از یک کپی موقتِ فهرست انتخاب‌شده ان` | 0 | ⬜ |
| `ASSET-022.27` | 1431–1436 | بلوک | `ویرایش زیر هر بخش: تورفتگی و حالت غیرفعال وقتی خود` | 0 | ⬜ |
| `ASSET-022.28` | 1437–1439 | بلوک | `حوزهٔ خدمت: فیلدهای مربوط به حوزهٔ دیگر کاملاً پنه` | 0 | ⬜ |
| `ASSET-022.29` | 1440–1442 | بلوک | `منوی واحد: اگر بلند بود، داخلی اسکرول می‌شود و از ` | 0 | ⬜ |
| `ASSET-022.30` | 1443–1446 | بلوک | `افکت نمایشی — صفحه‌های انتخاب‌شده` | 0 | ⬜ |
| `ASSET-022.31` | 1447–1494 | بلوک | `افکت نمایشی — پنل تنظیمات` | 3 | ⬜ |
| `ASSET-022.32` | 1495–1513 | بلوک | `فضای بیشتر و کنترل‌های خواناتر برای چارت سازمانی د` | 2 | ⬜ |
| `ASSET-022.33` | 1514–1543 | بلوک | `Traffic record correction and immutable edit-histo` | 1 | ⬜ |
| `ASSET-022.34` | 1544–1547 | بلوک | `A text field is used instead of locale-dependent n` | 0 | ⬜ |
| `ASSET-022.35` | 1548–1550 | بلوک | `تاریخچهٔ تغییرات در نمایشگرهای رومیزی دو ستونه و د` | 0 | ⬜ |
| `ASSET-022.36` | 1551–1576 | بلوک | `۱.۶۲.۹: چهار فیلد (عنوان + ۳ تأییدکننده) در یک خط؛` | 2 | ⬜ |
| `ASSET-022.37` | 1577–1582 | بلوک | `Fixed 09 prefix; only the remaining nine phone dig` | 0 | ⬜ |
| `ASSET-022.38` | 1583–1599 | بلوک | `Inspection-only daily food-absence reminder.` | 0 | ⬜ |
| `ASSET-022.39` | 1600–1604 | بلوک | `Keep the organization workspace viewport-sized, bu` | 0 | ⬜ |
| `ASSET-022.40` | 1605–1623 | بلوک | `Settings users: keep each account row tidy and ope` | 1 | ⬜ |
| `ASSET-022.41` | 1624–1698 | بلوک | `درخت سازمان پویا: چهار معاونت در هر ردیف، اداره‌ها` | 3 | ⬜ |
| `ASSET-022.42` | 1699–1704 | بلوک | `چارت سازمان: پیمایش با Space+drag بدون smooth-scro` | 0 | ⬜ |
| `ASSET-022.43` | 1705–1713 | بلوک | `هر شاخهٔ معاونت رنگ مخصوص خود را به کارت‌ها، کارشن` | 0 | ⬜ |
| `ASSET-022.44` | 1714–1758 | بلوک | `چارت سازمان: منوی خروجی، واحدهای هم‌تراز و نماهای ` | 0 | ⬜ |
| `ASSET-022.45` | 1759–1765 | بلوک | `چندشرکتی: انتخاب‌گر شرکت در چارت` | 1 | ⬜ |
| `ASSET-022.46` | 1766–1803 | بلوک | `اصلاحات چارت: ابزار ثابت، خروجی چاپ کامل و ورودی ب` | 1 | ⬜ |
| `ASSET-022.47` | 1804–1817 | بلوک | `چاپ مبتنی بر تصویر کامل و نام شرکت در کارت ریشه` | 0 | ⬜ |
| `ASSET-022.48` | 1818–1828 | بلوک | `فیلد نام شرکت‌ها صرفاً برچسب نمایشی انتخاب‌گر و کا` | 2 | ⬜ |
| `ASSET-022.49` | 1829–1865 | بلوک | `گروه‌های عمومی دسترسی: همان برد نقش‌ها با ویرایش ع` | 2 | ⬜ |

</details>

<details>
<summary><b>ASSET-023</b> — <code>assets/theme-mode.css</code> (252 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-023.01` | 1–140 | بلوک | `Global day/night mode for every screen rendered by` | 3 | ⬜ |
| `ASSET-023.02` | 141–211 | بلوک | `Cohesive dark surfaces for embedded food forms and` | 0 | ⬜ |
| `ASSET-023.03` | 212–242 | بلوک | `v1.62.4: پوشش کامل حالت شب برای فرم‌های برنامه غذا` | 0 | ⬜ |
| `ASSET-023.04` | 243–252 | بلوک | `v1.62.4: سفارش غذا — سطح‌هایی که رنگ روشن ثابت یا ` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-024</b> — <code>assets/traffic-control.js</code> (488 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

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
| `ASSET-024.11` | 197–237 | بلوک | `updateDestinationSummary` | 0 | ⬜ |
| `ASSET-024.12` | 238–241 | بلوک | `جست‌وجوی آنی در سوابق تردد: به‌محض تایپ، ردیف‌های ` | 0 | ⬜ |
| `ASSET-024.13` | 242–262 | بلوک | `liveList` | 4 | ⬜ |
| `ASSET-024.14` | 263–263 | بلوک | `selectedFields` | 0 | ⬜ |
| `ASSET-024.15` | 264–290 | بلوک | `renderReport` | 4 | ⬜ |
| `ASSET-024.16` | 291–299 | بلوک | `schedule` | 0 | ⬜ |
| `ASSET-024.17` | 300–392 | بلوک | `load` | 1 | ⬜ |
| `ASSET-024.18` | 393–420 | بلوک | `apply` | 0 | ⬜ |
| `ASSET-024.19` | 421–458 | بلوک | `asciiDigits` | 4 | ⬜ |
| `ASSET-024.20` | 459–466 | بلوک | `asciiDigits` | 0 | ⬜ |
| `ASSET-024.21` | 467–488 | بلوک | `sync` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-025</b> — <code>assets/traffic-history-1.62.72.css</code> (7 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-025.01` | 1–7 | بلوک | `Only traffic history; preserve other tabs.` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-026</b> — <code>assets/traffic-records-1.62.68.css</code> (21 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-026.01` | 1–21 | بلوک | `Scoped to the CD/DVD history only: no effect on th` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-027</b> — <code>assets/traffic-records-1.62.69.css</code> (50 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-027.01` | 1–50 | بلوک | `Wide layouts are confined to these two pages/tabs,` | 3 | ⬜ |

</details>

<details>
<summary><b>ASSET-028</b> — <code>assets/traffic-report-1.62.70.css</code> (22 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-028.01` | 1–22 | بلوک | `گزارش تردد: مقصد در کنار تاریخ‌ها و گزینه‌ها به صو` | 1 | ⬜ |

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
| `ASSET-030.01` | 1–35 | بلوک | `کنترل پیش‌نمایش زنده و فهرست صفحه‌های افکت نمایشی` | 0 | ⬜ |
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
<summary><b>WEB-002</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-4179.css</code> (3 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-002.01` | 1–2 | بلوک | `! tailwindcss v4.3.3 \| MIT License \| https://tai` | 3 | ⬜ |
| `WEB-002.02` | 3–3 | بلوک | `$vite$:1` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-003</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-4179.js</code> (319 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-003.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-003.02` | 159–177 | بلوک | `fetchData` | 1 | ⬜ |
| `WEB-003.03` | 178–186 | بلوک | `assert on the output` | 1 | ⬜ |
| `WEB-003.04` | 187–319 | بلوک | `assert on the output` | 6 | ⬜ |

</details>

<details>
<summary><b>WEB-004</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r37.css</code> (3 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-004.01` | 1–2 | بلوک | `! tailwindcss v4.3.3 \| MIT License \| https://tai` | 3 | ⬜ |
| `WEB-004.02` | 3–3 | بلوک | `$vite$:1` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-005</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r37.js</code> (359 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-005.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-005.02` | 159–177 | بلوک | `fetchData` | 1 | ⬜ |
| `WEB-005.03` | 178–186 | بلوک | `assert on the output` | 1 | ⬜ |
| `WEB-005.04` | 187–359 | بلوک | `assert on the output` | **11** | ⬜ |

</details>

<details>
<summary><b>WEB-006</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r38.css</code> (3 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-006.01` | 1–2 | بلوک | `! tailwindcss v4.3.3 \| MIT License \| https://tai` | 3 | ⬜ |
| `WEB-006.02` | 3–3 | بلوک | `$vite$:1` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-007</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r38.js</code> (359 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-007.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-007.02` | 159–177 | بلوک | `fetchData` | 1 | ⬜ |
| `WEB-007.03` | 178–186 | بلوک | `assert on the output` | 1 | ⬜ |
| `WEB-007.04` | 187–359 | بلوک | `assert on the output` | **11** | ⬜ |

</details>

<details>
<summary><b>WEB-008</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r39.css</code> (25 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-008.01` | 1–2 | بلوک | `! tailwindcss v4.3.3 \| MIT License \| https://tai` | 3 | ⬜ |
| `WEB-008.02` | 3–3 | بلوک | `$vite$:1` | 0 | ⬜ |
| `WEB-008.03` | 4–25 | بلوک | `Keep the dashboard toolbar within its actual colum` | 1 | ⬜ |

</details>

<details>
<summary><b>WEB-009</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r39.js</code> (359 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-009.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-009.02` | 159–177 | بلوک | `fetchData` | 1 | ⬜ |
| `WEB-009.03` | 178–186 | بلوک | `assert on the output` | 1 | ⬜ |
| `WEB-009.04` | 187–359 | بلوک | `assert on the output` | **11** | ⬜ |

</details>

<details>
<summary><b>WEB-010</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r40.css</code> (36 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-010.01` | 1–2 | بلوک | `! tailwindcss v4.3.3 \| MIT License \| https://tai` | 3 | ⬜ |
| `WEB-010.02` | 3–3 | بلوک | `$vite$:1` | 0 | ⬜ |
| `WEB-010.03` | 4–36 | بلوک | `Keep the dashboard toolbar within its actual colum` | 1 | ⬜ |

</details>

<details>
<summary><b>WEB-011</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r41.css</code> (46 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-011.01` | 1–2 | بلوک | `! tailwindcss v4.3.3 \| MIT License \| https://tai` | 3 | ⬜ |
| `WEB-011.02` | 3–3 | بلوک | `$vite$:1` | 0 | ⬜ |
| `WEB-011.03` | 4–36 | بلوک | `Keep the dashboard toolbar within its actual colum` | 1 | ⬜ |
| `WEB-011.04` | 37–46 | بلوک | `R41: animate dashboard theme changes as a viewport` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-012</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r41.js</code> (359 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-012.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-012.02` | 159–177 | بلوک | `fetchData` | 1 | ⬜ |
| `WEB-012.03` | 178–186 | بلوک | `assert on the output` | 1 | ⬜ |
| `WEB-012.04` | 187–359 | بلوک | `assert on the output` | **11** | ⬜ |

</details>

<details>
<summary><b>WEB-013</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r42.css</code> (46 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-013.01` | 1–2 | بلوک | `! tailwindcss v4.3.3 \| MIT License \| https://tai` | 3 | ⬜ |
| `WEB-013.02` | 3–3 | بلوک | `$vite$:1` | 0 | ⬜ |
| `WEB-013.03` | 4–36 | بلوک | `Keep the dashboard toolbar within its actual colum` | 1 | ⬜ |
| `WEB-013.04` | 37–46 | بلوک | `R41: animate dashboard theme changes as a viewport` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-014</b> — <code>food-ticket-web/assets/food-capitalio-dashboard-r42.js</code> (359 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-014.01` | 1–158 | بلوک | `مقدمهٔ فایل` | **11** | ⬜ |
| `WEB-014.02` | 159–177 | بلوک | `fetchData` | 1 | ⬜ |
| `WEB-014.03` | 178–186 | بلوک | `assert on the output` | 1 | ⬜ |
| `WEB-014.04` | 187–359 | بلوک | `assert on the output` | **11** | ⬜ |

</details>

<details>
<summary><b>WEB-015</b> — <code>food-ticket-web/assets/food-capitalio-dashboard.css</code> (336 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-015.01` | 1–170 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `WEB-015.02` | 171–336 | بلوک | `Capitalio-inspired full-page dashboard shell and t` | 4 | ⬜ |

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
<summary><b>WEB-017</b> — <code>food-ticket-web/assets/food-groups.js</code> (518 خط، 36 آدرس، بازبینی‌شده 0/36)</summary>

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
| `WEB-017.26` | 253–263 | بلوک | `personLine` | 0 | ⬜ |
| `WEB-017.27` | 264–270 | بلوک | `membersPopup` | 0 | ⬜ |
| `WEB-017.28` | 271–292 | بلوک | `block` | 0 | ⬜ |
| `WEB-017.29` | 293–301 | بلوک | `body` | 0 | ⬜ |
| `WEB-017.30` | 302–311 | بلوک | `html` | 0 | ⬜ |
| `WEB-017.31` | 312–323 | بلوک | `collect` | 0 | ⬜ |
| `WEB-017.32` | 324–399 | بلوک | `loadAbsence` | 0 | ⬜ |
| `WEB-017.33` | 400–420 | بلوک | `guest` | 0 | ⬜ |
| `WEB-017.34` | 421–460 | بلوک | `openEdit` | 1 | ⬜ |
| `WEB-017.35` | 461–487 | بلوک | `reason` | 0 | ⬜ |
| `WEB-017.36` | 488–518 | بلوک | `reason` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-018</b> — <code>food-ticket-web/assets/food-manual-attendance.js</code> (333 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-018.01` | 1–11 | بلوک | `ثبت دستی فیش — زیرمنوی سلامت سیستم در تنظیمات پنل ` | 0 | ⬜ |
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
<summary><b>WEB-019</b> — <code>food-ticket-web/assets/food-menu.css</code> (354 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-019.01` | 1–69 | بلوک | `مدیریت برنامه غذایی — بدون وابستگی بیرونی، هم‌خوان` | 0 | ⬜ |
| `WEB-019.02` | 70–205 | بلوک | `ستون ردیف همیشه یکخطی بماند — حتی اعداد سه رقمی` | 4 | ⬜ |
| `WEB-019.03` | 206–212 | بلوک | `تمام جدول‌ها و ردیف‌های فهرستی برنامه در پنل، وسط‌` | 0 | ⬜ |
| `WEB-019.04` | 213–227 | بلوک | `بانک غذا: دسته‌بندی، جستجوی زنده، و فهرست یک‌خطی` | 0 | ⬜ |
| `WEB-019.05` | 228–240 | بلوک | `لیست کارکنان (غیبت و اعضای گروه): ۴ ستون، تیک قبل ` | 3 | ⬜ |
| `WEB-019.06` | 241–244 | بلوک | `جعبهٔ انتخاب غذا: هم‌اندازهٔ فرم غیبت` | 0 | ⬜ |
| `WEB-019.07` | 245–254 | بلوک | `بانک غذا: هر غذا در یک خط (نام \| دسته‌بندی \| وضع` | 2 | ⬜ |
| `WEB-019.08` | 255–265 | بلوک | `۱.۵۲: دسته‌بندی‌های ریشه در بانک غذا (+ باز / - جم` | 1 | ⬜ |
| `WEB-019.09` | 266–271 | بلوک | `۱.۵۲: سفارش اضطراری — مراحل` | 1 | ⬜ |
| `WEB-019.10` | 272–275 | بلوک | `جستجوی زنده غذا در ویرایش برنامهٔ روز` | 0 | ⬜ |
| `WEB-019.11` | 276–282 | بلوک | `۱.۵۶ — مرتب‌سازی ستون‌ها و گروه‌بندی سفارش‌های روز` | 0 | ⬜ |
| `WEB-019.12` | 283–286 | بلوک | `عملیات گروهی سفارش/تغییر سلف برای مدیران غذا` | 1 | ⬜ |
| `WEB-019.13` | 287–328 | بلوک | `ثبت دستی تردد در تنظیمات سیستم` | 1 | ⬜ |
| `WEB-019.14` | 329–346 | بلوک | `Selectable employee checklist used by the live, bo` | 1 | ⬜ |
| `WEB-019.15` | 347–354 | بلوک | `بلوکبندی ۲/۳ ستونه: همهٔ ستونها دقیقاً همعرض، بدون` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-020</b> — <code>food-ticket-web/assets/food-menu.js</code> (2,479 خط، 152 آدرس، بازبینی‌شده 0/152)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-020.01` | 1–100 | بلوک | `مدیر برنامهٔ غذایی در پنل چاپ فیش.` | 0 | ⬜ |
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
| `WEB-020.92` | 1331–1350 | بلوک | `loadEmbedded` | 0 | ⬜ |
| `WEB-020.93` | 1351–1358 | بلوک | `renderFocusKeyOf` | 0 | ⬜ |
| `WEB-020.94` | 1359–1370 | بلوک | `captureRenderFocus` | 4 | ⬜ |
| `WEB-020.95` | 1371–1383 | بلوک | `restoreRenderFocus` | 4 | ⬜ |
| `WEB-020.96` | 1384–1395 | بلوک | `render` | 0 | ⬜ |
| `WEB-020.97` | 1396–1402 | بلوک | `notices` | 0 | ⬜ |
| `WEB-020.98` | 1403–1414 | بلوک | `loadStatus` | 0 | ⬜ |
| `WEB-020.99` | 1415–1421 | بلوک | `loadCatalog` | 0 | ⬜ |
| `WEB-020.100` | 1422–1429 | بلوک | `loadMonth` | 0 | ⬜ |
| `WEB-020.101` | 1430–1451 | بلوک | `loadOrders` | 0 | ⬜ |
| `WEB-020.102` | 1452–1472 | بلوک | `loadFoodGroups` | 0 | ⬜ |
| `WEB-020.103` | 1473–1503 | بلوک | `loadGroupOrders` | 0 | ⬜ |
| `WEB-020.104` | 1504–1520 | بلوک | `loadCancelRequests` | 0 | ⬜ |
| `WEB-020.105` | 1521–1539 | بلوک | `loadGuestRequests` | 0 | ⬜ |
| `WEB-020.106` | 1540–1540 | بلوک | `beginGuestEdit` | 0 | ⬜ |
| `WEB-020.107` | 1541–1556 | بلوک | `row` | 0 | ⬜ |
| `WEB-020.108` | 1557–1562 | بلوک | `cancelGuestEdit` | 0 | ⬜ |
| `WEB-020.109` | 1563–1594 | بلوک | `saveGuestEdit` | 1 | ⬜ |
| `WEB-020.110` | 1595–1595 | بلوک | `guestRequestAction` | 0 | ⬜ |
| `WEB-020.111` | 1596–1618 | بلوک | `row` | 1 | ⬜ |
| `WEB-020.112` | 1619–1621 | بلوک | `renderGuestRequestManager` | 0 | ⬜ |
| `WEB-020.113` | 1622–1644 | بلوک | `rows` | 1 | ⬜ |
| `WEB-020.114` | 1645–1659 | بلوک | `decideCancelRequest` | 0 | ⬜ |
| `WEB-020.115` | 1660–1666 | بلوک | `loadStatistics` | 0 | ⬜ |
| `WEB-020.116` | 1667–1700 | بلوک | `selectedIds` | 0 | ⬜ |
| `WEB-020.117` | 1701–1728 | بلوک | `searchSelectedReportEmployees` | 0 | ⬜ |
| `WEB-020.118` | 1729–1755 | بلوک | `refreshAll` | 0 | ⬜ |
| `WEB-020.119` | 1756–1800 | بلوک | `saveDay` | 0 | ⬜ |
| `WEB-020.120` | 1801–1826 | بلوک | `saveCatalog` | 0 | ⬜ |
| `WEB-020.121` | 1827–1846 | بلوک | `orderExportRows` | 0 | ⬜ |
| `WEB-020.122` | 1847–1863 | بلوک | `downloadOrdersCsv` | 0 | ⬜ |
| `WEB-020.123` | 1864–1868 | بلوک | `downloadOrders` | 0 | ⬜ |
| `WEB-020.124` | 1869–1870 | بلوک | `printScopedReport` | 0 | ⬜ |
| `WEB-020.125` | 1871–1881 | بلوک | `target` | 0 | ⬜ |
| `WEB-020.126` | 1882–1899 | بلوک | `cleanup` | 0 | ⬜ |
| `WEB-020.127` | 1900–1904 | بلوک | `printOrders` | 0 | ⬜ |
| `WEB-020.128` | 1905–1911 | بلوک | `downloadStatistics` | 0 | ⬜ |
| `WEB-020.129` | 1912–1916 | بلوک | `printStatistics` | 0 | ⬜ |
| `WEB-020.130` | 1917–1924 | بلوک | `selectDay` | 0 | ⬜ |
| `WEB-020.131` | 1925–1930 | بلوک | `changeMonth` | 0 | ⬜ |
| `WEB-020.132` | 1931–1967 | بلوک | `switchTab` | 0 | ⬜ |
| `WEB-020.133` | 1968–1981 | بلوک | `emergencyEnsurePeople` | 0 | ⬜ |
| `WEB-020.134` | 1982–1990 | بلوک | `emergencyPanel` | 0 | ⬜ |
| `WEB-020.135` | 1991–2025 | بلوک | `msgs` | 0 | ⬜ |
| `WEB-020.136` | 2026–2031 | بلوک | `emergencyLoadOrders` | 0 | ⬜ |
| `WEB-020.137` | 2032–2076 | بلوک | `daySearchInput` | 4 | ⬜ |
| `WEB-020.138` | 2077–2087 | بلوک | `emergencyClick` | 0 | ⬜ |
| `WEB-020.139` | 2088–2116 | بلوک | `person` | 0 | ⬜ |
| `WEB-020.140` | 2117–2155 | بلوک | `eventClick` | 0 | ⬜ |
| `WEB-020.141` | 2156–2251 | بلوک | `person` | 1 | ⬜ |
| `WEB-020.142` | 2252–2260 | بلوک | `dayPickFor` | 0 | ⬜ |
| `WEB-020.143` | 2261–2267 | بلوک | `trackDayPick` | 0 | ⬜ |
| `WEB-020.144` | 2268–2275 | بلوک | `orderedDayFoodIds` | 0 | ⬜ |
| `WEB-020.145` | 2276–2299 | بلوک | `eventChange` | 0 | ⬜ |
| `WEB-020.146` | 2300–2333 | بلوک | `person` | 0 | ⬜ |
| `WEB-020.147` | 2334–2416 | بلوک | `eventSubmit` | 0 | ⬜ |
| `WEB-020.148` | 2417–2422 | بلوک | `spanDays` | 0 | ⬜ |
| `WEB-020.149` | 2423–2451 | بلوک | `mount` | 0 | ⬜ |
| `WEB-020.150` | 2452–2452 | بلوک | `reportInputFocused` | 0 | ⬜ |
| `WEB-020.151` | 2453–2469 | بلوک | `safeReportRender` | 0 | ⬜ |
| `WEB-020.152` | 2470–2479 | بلوک | `html` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-021</b> — <code>food-ticket-web/assets/food-order-calendar.js</code> (103 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-021.01` | 1–7 | بلوک | `Shared Jalali calendar helpers for the employee fo` | 0 | ⬜ |
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
<summary><b>WEB-022</b> — <code>food-ticket-web/assets/food-order.css</code> (213 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-022.01` | 1–110 | بلوک | `سفارش غذا — از همان رنگ‌ها، کارت‌ها و فرم‌های asse` | 2 | ⬜ |
| `WEB-022.02` | 111–115 | بلوک | `تأیید عملیات در DOM پایدار می‌ماند تا بازخوانی تقو` | 0 | ⬜ |
| `WEB-022.03` | 116–168 | بلوک | `انتخاب شخص پیش از فعال‌شدن تقویم؛ منوی اصلی سامانه` | 1 | ⬜ |
| `WEB-022.04` | 169–174 | بلوک | `نام غذا در سلول تقویم در یک خط نمایش داده می‌شود؛ ` | 0 | ⬜ |
| `WEB-022.05` | 175–177 | بلوک | `Separate lunch and dinner without changing the exi` | 0 | ⬜ |
| `WEB-022.06` | 178–178 | بلوک | `فهرست شخصی درخواست مهمان: فضای محدود، خوانا و مستق` | 0 | ⬜ |
| `WEB-022.07` | 179–190 | بلوک | `A single compact row per request. The table scroll` | 0 | ⬜ |
| `WEB-022.08` | 191–213 | بلوک | `Daily food reconciliation and monthly invoice reco` | 2 | ⬜ |

</details>

<details>
<summary><b>WEB-023</b> — <code>food-ticket-web/assets/food-ticket-global-theme-r42.css</code> (169 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-023.01` | 1–85 | بلوک | `R42 shared system color mode for the full food-tic` | 1 | ⬜ |
| `WEB-023.02` | 86–128 | بلوک | `R44: theme-aware food planning forms, schedules an` | 0 | ⬜ |
| `WEB-023.03` | 129–159 | بلوک | `v1.62.4: پوشش کامل حالت شب برای فرم‌های برنامه غذا` | 0 | ⬜ |
| `WEB-023.04` | 160–169 | بلوک | `v1.62.4: سفارش غذا — سطح‌هایی که رنگ روشن ثابت یا ` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-024</b> — <code>food-ticket-web/assets/food-ticket-persistence.js</code> (24 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-024.01` | 1–5 | بلوک | `Food Ticket persistence helpers — guest cards & em` | 0 | ⬜ |
| `WEB-024.02` | 6–9 | بلوک | `apiGuestCards` | 0 | ⬜ |
| `WEB-024.03` | 10–24 | بلوک | `apiEmployees` | 1 | ⬜ |

</details>

<details>
<summary><b>WEB-025</b> — <code>food-ticket-web/assets/food-ticket-theme.css</code> (421 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-025.01` | 1–263 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |
| `WEB-025.02` | 264–276 | بلوک | `چاپ گزارش: فقط جدول و آمار، بدون منو و دکمه‌ها` | 0 | ⬜ |
| `WEB-025.03` | 277–291 | بلوک | `Center-align data tables and grid-based list rows ` | 0 | ⬜ |
| `WEB-025.04` | 292–321 | بلوک | `Print only the report currently open in its previe` | 0 | ⬜ |
| `WEB-025.05` | 322–334 | بلوک | `گروه‌های غذا: مهلت تغییر سلف جدا و برجسته از سایر ` | 0 | ⬜ |
| `WEB-025.06` | 335–360 | بلوک | `مودال لیست اعضا به body منتقل می‌شود تا همیشه بالا` | 1 | ⬜ |
| `WEB-025.07` | 361–421 | بلوک | `داشبورد مدیریت غذا؛ چیدمان الهام‌گرفته از نمونهٔ ت` | 2 | ⬜ |

</details>

<details>
<summary><b>WEB-026</b> — <code>food-ticket-web/assets/jalali-calendar.js</code> (347 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-026.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-026.02` | 13–14 | بلوک | `pad` | 0 | ⬜ |
| `WEB-026.03` | 15–44 | بلوک | `j2g` | 0 | ⬜ |
| `WEB-026.04` | 45–66 | بلوک | `g2j` | 0 | ⬜ |
| `WEB-026.05` | 67–73 | بلوک | `monthLength` | 0 | ⬜ |
| `WEB-026.06` | 74–81 | بلوک | `parseJalali` | 0 | ⬜ |
| `WEB-026.07` | 82–85 | بلوک | `formatJalali` | 0 | ⬜ |
| `WEB-026.08` | 86–99 | بلوک | `holidaySet` | 0 | ⬜ |
| `WEB-026.09` | 100–100 | بلوک | `weekendDays` | 0 | ⬜ |
| `WEB-026.10` | 101–109 | بلوک | `raw` | 0 | ⬜ |
| `WEB-026.11` | 110–115 | بلوک | `isWeekendJalali` | 0 | ⬜ |
| `WEB-026.12` | 116–120 | بلوک | `todayJ` | 0 | ⬜ |
| `WEB-026.13` | 121–134 | بلوک | `bindPicker` | 0 | ⬜ |
| `WEB-026.14` | 135–135 | بلوک | `close` | 0 | ⬜ |
| `WEB-026.15` | 136–137 | بلوک | `open` | 0 | ⬜ |
| `WEB-026.16` | 138–143 | بلوک | `render` | 0 | ⬜ |
| `WEB-026.17` | 144–215 | بلوک | `startIndex` | 0 | ⬜ |
| `WEB-026.18` | 216–227 | بلوک | `installOutsideClickHandler` | 0 | ⬜ |
| `WEB-026.19` | 228–234 | بلوک | `bindYearBoard` | 0 | ⬜ |
| `WEB-026.20` | 235–243 | بلوک | `render` | 0 | ⬜ |
| `WEB-026.21` | 244–280 | بلوک | `startIndex` | 0 | ⬜ |
| `WEB-026.22` | 281–347 | بلوک | `enhanceAllDateFields` | 0 | ⬜ |

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
<summary><b>ACT-001</b> — <code>actions/access-group-actions.php</code> (198 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-001.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 1 | ⬜ |
| `ACT-001.02` | 14–46 | ناحیه | `مسیر: create_access_group` | 2 | ⬜ |
| `ACT-001.03` | 47–169 | ناحیه | `مسیر: save_access_group` | 3 | ⬜ |
| `ACT-001.04` | 170–198 | ناحیه | `مسیر: delete_access_group` | 2 | ⬜ |

</details>

<details>
<summary><b>ACT-002</b> — <code>actions/account-actions.php</code> (125 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-002.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `ACT-002.02` | 17–23 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-002.03` | 24–94 | ناحیه | `مسیر: login` | 3 | ⬜ |
| `ACT-002.04` | 95–102 | ناحیه | `مسیر: save_theme` | 0 | ⬜ |
| `ACT-002.05` | 103–125 | ناحیه | `مسیر: save_profile` | 1 | ⬜ |

</details>

<details>
<summary><b>ACT-003</b> — <code>actions/asset-actions.php</code> (432 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-003.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `ACT-003.02` | 26–32 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-003.03` | 33–63 | ناحیه | `مسیر: collect_inventory` | 1 | ⬜ |
| `ACT-003.04` | 64–122 | ناحیه | `مسیر: asset_profile_selftest` | 4 | ⬜ |
| `ACT-003.05` | 123–130 | ناحیه | `مسیر: import_domain_assets` | 0 | ⬜ |
| `ACT-003.06` | 131–147 | ناحیه | `مسیر: domain_scan_start` | 2 | ⬜ |
| `ACT-003.07` | 148–189 | ناحیه | `مسیر: domain_scan_batch` | 2 | ⬜ |
| `ACT-003.08` | 190–266 | ناحیه | `مسیر: ping_assets` | **8** | ⬜ |
| `ACT-003.09` | 267–284 | ناحیه | `مسیر: domain_scan_retry` | 2 | ⬜ |
| `ACT-003.10` | 285–342 | ناحیه | `مسیر: domain_scan_test_selected` | **8** | ⬜ |
| `ACT-003.11` | 343–360 | ناحیه | `مسیر: domain_scan_selected` | 2 | ⬜ |
| `ACT-003.12` | 361–432 | ناحیه | `مسیر: save_inventory_form` | 7 | ⬜ |

</details>

<details>
<summary><b>ACT-004</b> — <code>actions/backup-actions.php</code> (54 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-004.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 1 | ⬜ |
| `ACT-004.02` | 18–24 | ناحیه | `مسیر: create_backup` | 0 | ⬜ |
| `ACT-004.03` | 25–40 | ناحیه | `مسیر: restore_backup` | 0 | ⬜ |
| `ACT-004.04` | 41–54 | ناحیه | `مسیر: system_update` | 3 | ⬜ |

</details>

<details>
<summary><b>ACT-005</b> — <code>actions/bulk-actions.php</code> (6 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-005.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-006</b> — <code>actions/cd-dvd-actions.php</code> (42 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-006.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `ACT-006.02` | 17–23 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-006.03` | 24–29 | ناحیه | `مسیر: cd_dvd_create` | 0 | ⬜ |
| `ACT-006.04` | 30–35 | ناحیه | `مسیر: cd_dvd_update` | 0 | ⬜ |
| `ACT-006.05` | 36–42 | ناحیه | `مسیر: cd_dvd_delete` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-007</b> — <code>actions/food-brand-actions.php</code> (46 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-007.01` | 1–14 | ناحیه | `بلوک رویه‌ای 1-14` | 0 | ⬜ |
| `ACT-007.02` | 15–21 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-007.03` | 22–46 | ناحیه | `مسیر: save_food_ticket_brand` | 1 | ⬜ |

</details>

<details>
<summary><b>ACT-008</b> — <code>actions/holiday-actions.php</code> (194 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-008.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `ACT-008.02` | 20–25 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-008.03` | 26–26 | ناحیه | `مقصد بازگشت پس از هر اکشن: صفحهٔ مستقل تقویم، یا ت` | 0 | ⬜ |
| `ACT-008.04` | 27–34 | ناحیه | `وقتی فرم از همان تب ارسال شده باشد. هر مقدار دیگری` | 2 | ⬜ |
| `ACT-008.05` | 35–75 | ناحیه | `مسیر: toggle_holiday` | 0 | ⬜ |
| `ACT-008.06` | 76–101 | ناحیه | `مسیر: add_holiday` | 0 | ⬜ |
| `ACT-008.07` | 102–131 | ناحیه | `مسیر: import_holidays` | 0 | ⬜ |
| `ACT-008.08` | 132–173 | ناحیه | `مسیر: save_work_hours` | 2 | ⬜ |
| `ACT-008.09` | 174–194 | ناحیه | `مسیر: delete_holiday` | 2 | ⬜ |

</details>

<details>
<summary><b>ACT-009</b> — <code>actions/notification-actions.php</code> (35 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-009.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `ACT-009.02` | 16–22 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-009.03` | 23–28 | ناحیه | `مسیر: mark_all_notifications` | 1 | ⬜ |
| `ACT-009.04` | 29–35 | ناحیه | `مسیر: mark_notification` | 3 | ⬜ |

</details>

<details>
<summary><b>ACT-010</b> — <code>actions/org-actions.php</code> (843 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-010.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `ACT-010.02` | 17–22 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-010.03` | 23–24 | ناحیه | `درخواست‌های UI چارت برای جلوگیری از ثبت دوباره در ` | 0 | ⬜ |
| `ACT-010.04` | 25–46 | ناحیه | `شرط مسیریابی` | 2 | ⬜ |
| `ACT-010.05` | 47–68 | ناحیه | `شرط مسیریابی` | 7 | ⬜ |
| `ACT-010.06` | 69–205 | ناحیه | `مسیر: save_org_node` | 4 | ⬜ |
| `ACT-010.07` | 206–223 | ناحیه | `مسیر: undo_org_action` | 0 | ⬜ |
| `ACT-010.08` | 224–242 | ناحیه | `مسیر: redo_org_action` | 0 | ⬜ |
| `ACT-010.09` | 243–272 | ناحیه | `مسیر: sort_org_root_units` | 2 | ⬜ |
| `ACT-010.10` | 273–309 | ناحیه | `مسیر: sort_org_deputies` | 2 | ⬜ |
| `ACT-010.11` | 310–354 | ناحیه | `مسیر: assign_org_manager` | 0 | ⬜ |
| `ACT-010.12` | 355–381 | ناحیه | `مسیر: remove_org_manager` | 0 | ⬜ |
| `ACT-010.13` | 382–408 | ناحیه | `مسیر: remove_org_experts` | **8** | ⬜ |
| `ACT-010.14` | 409–445 | ناحیه | `مسیر: save_org_assign_users` | 7 | ⬜ |
| `ACT-010.15` | 446–564 | ناحیه | `مسیر: restore_org_backup` | 5 | ⬜ |
| `ACT-010.16` | 565–588 | ناحیه | `مسیر: reset_org_structure` | 5 | ⬜ |
| `ACT-010.17` | 589–616 | ناحیه | `مسیر: save_org_ceo` | 1 | ⬜ |
| `ACT-010.18` | 617–695 | ناحیه | `مسیر: save_org_unit` | **8** | ⬜ |
| `ACT-010.19` | 696–731 | ناحیه | `مسیر: delete_org_unit` | 5 | ⬜ |
| `ACT-010.20` | 732–765 | ناحیه | `مسیر: save_user_org` | 1 | ⬜ |
| `ACT-010.21` | 766–805 | ناحیه | `مسیر: repair_org_manager_admin_roles` | 7 | ⬜ |
| `ACT-010.22` | 806–843 | ناحیه | `مسیر: repair_org_manager_support_roles` | 7 | ⬜ |

</details>

<details>
<summary><b>ACT-011</b> — <code>actions/role-actions.php</code> (162 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-011.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `ACT-011.02` | 19–25 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-011.03` | 26–99 | ناحیه | `مسیر: save_key_roles` | 7 | ⬜ |
| `ACT-011.04` | 100–123 | ناحیه | `مسیر: save_role_permissions` | 2 | ⬜ |
| `ACT-011.05` | 124–143 | ناحیه | `مسیر: reset_role_defaults` | 0 | ⬜ |
| `ACT-011.06` | 144–162 | ناحیه | `مسیر: reset_role_permissions` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-012</b> — <code>actions/service-actions.php</code> (6 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-012.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-013</b> — <code>actions/settings-actions.php</code> (275 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-013.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `ACT-013.02` | 21–27 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-013.03` | 28–54 | ناحیه | `مسیر: save_org_company_names` | 2 | ⬜ |
| `ACT-013.04` | 55–68 | ناحیه | `مسیر: prune_activity_logs` | 0 | ⬜ |
| `ACT-013.05` | 69–76 | ناحیه | `مسیر: sync_ldap_users` | 0 | ⬜ |
| `ACT-013.06` | 77–90 | ناحیه | `مسیر: diagnose_ldap` | 0 | ⬜ |
| `ACT-013.07` | 91–164 | ناحیه | `مسیر: save_triangle_effect` | 2 | ⬜ |
| `ACT-013.08` | 165–275 | ناحیه | `مسیر: save_settings` | 4 | ⬜ |

</details>

<details>
<summary><b>ACT-014</b> — <code>actions/supervisor-actions.php</code> (6 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-014.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-015</b> — <code>actions/ticket-actions.php</code> (6 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-015.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-016</b> — <code>actions/user-structure-actions.php</code> (373 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-016.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `ACT-016.02` | 22–28 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-016.03` | 29–39 | ناحیه | `مسیر: add_category` | 1 | ⬜ |
| `ACT-016.04` | 40–53 | ناحیه | `مسیر: add_department` | 1 | ⬜ |
| `ACT-016.05` | 54–159 | ناحیه | `مسیر: link_domain_food_by_code` | 7 | ⬜ |
| `ACT-016.06` | 160–318 | ناحیه | `مسیر: update_user_role` | 3 | ⬜ |
| `ACT-016.07` | 319–357 | ناحیه | `مسیر: save_department_members` | 7 | ⬜ |
| `ACT-016.08` | 358–373 | ناحیه | `مسیر: save_cd_dvd_settings` | 2 | ⬜ |

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
<summary><b>CORE-003</b> — <code>lib/auth-scope.php</code> (443 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

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
| `CORE-003.12` | 196–238 | تابع | `ticket_handler_level` | 0 | ⬜ |
| `CORE-003.13` | 240–257 | تابع | `ticket_view_mode` | 0 | ⬜ |
| `CORE-003.14` | 259–276 | تابع | `ticket_can_see_internal` | 0 | ⬜ |
| `CORE-003.15` | 278–321 | تابع | `ticket_assignee_is_eligible` | 0 | ⬜ |
| `CORE-003.16` | 323–352 | تابع | `ticket_rating_is_current` | 4 | ⬜ |
| `CORE-003.17` | 354–358 | تابع | `ticket_user_is_requester` | 0 | ⬜ |
| `CORE-003.18` | 360–368 | تابع | `ticket_reply_starts_work` | 0 | ⬜ |
| `CORE-003.19` | 370–390 | تابع | `ticket_scope` | 5 | ⬜ |
| `CORE-003.20` | 392–396 | تابع | `ticket_is_waiting_status` | 0 | ⬜ |
| `CORE-003.21` | 398–403 | تابع | `user_service_group` | 0 | ⬜ |
| `CORE-003.22` | 405–441 | تابع | `asset_scope` | 5 | ⬜ |

</details>

<details>
<summary><b>CORE-004</b> — <code>lib/db-core.php</code> (612 خط، 36 آدرس، بازبینی‌شده 0/36)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-004.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-004.02` | 14–18 | تابع | `csp_nonce` | 0 | ⬜ |
| `CORE-004.03` | 20–44 | ناحیه | `Enforce HTTPS in production` | 0 | ⬜ |
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
| `CORE-004.16` | 299–299 | ناحیه | `Rate limiting for API endpoints` | 0 | ⬜ |
| `CORE-004.17` | 300–300 | ناحیه | `بازمی‌گرداند true اگر مجاز باشد. اگر $retryAfter د` | 0 | ⬜ |
| `CORE-004.18` | 301–328 | تابع | `api_rate_limit` | 1 | ⬜ |
| `CORE-004.19` | 330–343 | تابع | `check_api_rate_limit` | 0 | ⬜ |
| `CORE-004.20` | 345–348 | تابع | `login_rate_key` | 0 | ⬜ |
| `CORE-004.21` | 350–369 | تابع | `login_rate_allowed` | 1 | ⬜ |
| `CORE-004.22` | 371–399 | تابع | `record_login_failure` | 1 | ⬜ |
| `CORE-004.23` | 401–408 | تابع | `clear_login_failures` | 1 | ⬜ |
| `CORE-004.24` | 410–419 | تابع | `app_version` | 1 | ⬜ |
| `CORE-004.25` | 421–424 | تابع | `e` | 0 | ⬜ |
| `CORE-004.26` | 426–431 | تابع | `app_theme_mode` | 2 | ⬜ |
| `CORE-004.27` | 433–439 | تابع | `app_theme_toggle_markup` | 0 | ⬜ |
| `CORE-004.28` | 441–445 | تابع | `redirect` | 0 | ⬜ |
| `CORE-004.29` | 447–475 | تابع | `db_install_token` | 4 | ⬜ |
| `CORE-004.30` | 477–481 | تابع | `db_install_token_forget` | 0 | ⬜ |
| `CORE-004.31` | 483–488 | تابع | `db_install_fresh_tables` | 0 | ⬜ |
| `CORE-004.32` | 490–527 | تابع | `db_install_state` | 5 | ⬜ |
| `CORE-004.33` | 529–544 | تابع | `db_in_transaction` | 0 | ⬜ |
| `CORE-004.34` | 546–576 | تابع | `db_commit` | 0 | ⬜ |
| `CORE-004.35` | 578–595 | تابع | `db_commit_or_fail` | 0 | ⬜ |
| `CORE-004.36` | 597–610 | تابع | `db_rollback` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-005</b> — <code>lib/ldap.php</code> (412 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-005.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-005.02` | 14–32 | تابع | `ldap_secret_key` | 0 | ⬜ |
| `CORE-005.03` | 34–51 | تابع | `ldap_secret_encrypt` | 0 | ⬜ |
| `CORE-005.04` | 53–90 | تابع | `ldap_secret_decrypt` | 0 | ⬜ |
| `CORE-005.05` | 92–116 | تابع | `ldap_cfg` | 0 | ⬜ |
| `CORE-005.06` | 118–133 | تابع | `ldap_role_for_groups` | 0 | ⬜ |
| `CORE-005.07` | 135–145 | تابع | `ldap_person_filter` | 0 | ⬜ |
| `CORE-005.08` | 147–162 | تابع | `ldap_auth_log` | 1 | ⬜ |
| `CORE-005.09` | 164–240 | تابع | `ldap_diagnose` | 3 | ⬜ |
| `CORE-005.10` | 242–410 | تابع | `ldap_sync_all_users` | 5 | ⬜ |

</details>

<details>
<summary><b>CORE-006</b> — <code>lib/log-catalog.php</code> (49 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-006.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `CORE-006.02` | 4–48 | تابع | `logging_catalog_definitions` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-007</b> — <code>lib/logging-audit.php</code> (643 خط، 33 آدرس، بازبینی‌شده 0/33)</summary>

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
| `CORE-007.09` | 138–138 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.10` | 139–139 | ناحیه | `بررسی‌های ساختاری — روی پایگاه دادهٔ زندهٔ همین نص` | 0 | ⬜ |
| `CORE-007.11` | 140–141 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.12` | 142–157 | تابع | `logging_audit_check_db_schema_tables` | 0 | ⬜ |
| `CORE-007.13` | 159–180 | تابع | `logging_audit_check_db_schema_columns` | 0 | ⬜ |
| `CORE-007.14` | 182–223 | تابع | `logging_audit_check_db_pending_migrations` | 0 | ⬜ |
| `CORE-007.15` | 225–248 | تابع | `logging_audit_check_filesystem_writable` | 1 | ⬜ |
| `CORE-007.16` | 250–250 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.17` | 251–251 | ناحیه | `بررسی‌های رفتاری — سلامت کد در حال اجرا` | 0 | ⬜ |
| `CORE-007.18` | 252–253 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.19` | 254–276 | تابع | `logging_audit_check_spool_backlog` | 0 | ⬜ |
| `CORE-007.20` | 278–317 | تابع | `logging_audit_check_unresolved_issues` | 0 | ⬜ |
| `CORE-007.21` | 319–342 | تابع | `logging_audit_check_worker_heartbeats` | 1 | ⬜ |
| `CORE-007.22` | 344–344 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.23` | 345–345 | ناحیه | `اجرا` | 0 | ⬜ |
| `CORE-007.24` | 346–347 | ناحیه | `-` | 0 | ⬜ |
| `CORE-007.25` | 348–352 | تابع | `logging_audit_heartbeat_path` | 0 | ⬜ |
| `CORE-007.26` | 354–382 | تابع | `logging_audit_check_audit_continuity` | 1 | ⬜ |
| `CORE-007.27` | 384–398 | تابع | `logging_audit_write_heartbeat` | 2 | ⬜ |
| `CORE-007.28` | 400–432 | تابع | `logging_audit_summarize` | 0 | ⬜ |
| `CORE-007.29` | 434–447 | تابع | `logging_audit_checks` | 0 | ⬜ |
| `CORE-007.30` | 449–457 | تابع | `logging_audit_load_baseline` | 1 | ⬜ |
| `CORE-007.31` | 459–472 | تابع | `logging_audit_save_baseline` | 2 | ⬜ |
| `CORE-007.32` | 474–506 | تابع | `logging_audit_run` | 0 | ⬜ |
| `CORE-007.33` | 508–642 | تابع | `logging_audit_run_and_record` | 7 | ⬜ |

</details>

<details>
<summary><b>CORE-008</b> — <code>lib/logging-core.php</code> (1,560 خط، 66 آدرس، بازبینی‌شده 0/66)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-008.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `CORE-008.02` | 16–20 | ناحیه | `سقف سخت صف اضطراری؛ فقط هنگام اختلال دیتابیس استفا` | 0 | ⬜ |
| `CORE-008.03` | 21–36 | تابع | `logging_uuid` | 0 | ⬜ |
| `CORE-008.04` | 38–45 | تابع | `logging_deterministic_uuid` | 0 | ⬜ |
| `CORE-008.05` | 47–50 | تابع | `logging_is_uuid_v4` | 0 | ⬜ |
| `CORE-008.06` | 52–55 | تابع | `logging_is_uuid` | 0 | ⬜ |
| `CORE-008.07` | 57–63 | تابع | `logging_request_id` | 0 | ⬜ |
| `CORE-008.08` | 65–69 | تابع | `logging_trace_id` | 0 | ⬜ |
| `CORE-008.09` | 71–75 | تابع | `logging_operation_id` | 0 | ⬜ |
| `CORE-008.10` | 77–87 | تابع | `logging_begin_execution` | 0 | ⬜ |
| `CORE-008.11` | 89–96 | تابع | `logging_begin_operation` | 0 | ⬜ |
| `CORE-008.12` | 98–101 | تابع | `logging_operation_name` | 0 | ⬜ |
| `CORE-008.13` | 103–113 | تابع | `logging_correlation_context` | 0 | ⬜ |
| `CORE-008.14` | 115–119 | تابع | `logging_parent_event_id` | 0 | ⬜ |
| `CORE-008.15` | 121–131 | تابع | `logging_bootstrap` | 0 | ⬜ |
| `CORE-008.16` | 133–141 | تابع | `logging_catalog_aliases` | 0 | ⬜ |
| `CORE-008.17` | 143–151 | تابع | `logging_event_catalog` | 0 | ⬜ |
| `CORE-008.18` | 153–173 | تابع | `logging_catalog_resolve` | 0 | ⬜ |
| `CORE-008.19` | 175–319 | تابع | `logging_action_event_definition` | 1 | ⬜ |
| `CORE-008.20` | 321–328 | تابع | `logging_normalize_digits` | 0 | ⬜ |
| `CORE-008.21` | 330–340 | تابع | `logging_is_valid_iranian_national_code` | 0 | ⬜ |
| `CORE-008.22` | 342–345 | تابع | `logging_strict_national_code_enabled` | 0 | ⬜ |
| `CORE-008.23` | 347–368 | تابع | `logging_sensitive_key` | 0 | ⬜ |
| `CORE-008.24` | 370–378 | تابع | `logging_utf8_substr` | 0 | ⬜ |
| `CORE-008.25` | 380–388 | تابع | `logging_utf8_strlen` | 0 | ⬜ |
| `CORE-008.26` | 390–408 | تابع | `logging_sanitize_text` | 0 | ⬜ |
| `CORE-008.27` | 410–416 | تابع | `logging_sanitize_single_line` | 0 | ⬜ |
| `CORE-008.28` | 417–418 | ناحیه | `بلوک رویه‌ای 417-418` | 0 | ⬜ |
| `CORE-008.29` | 419–428 | تابع | `logging_fingerprint_v2` | 0 | ⬜ |
| `CORE-008.30` | 430–435 | تابع | `logging_fingerprint_v3` | 0 | ⬜ |
| `CORE-008.31` | 437–442 | تابع | `logging_fingerprint_current` | 0 | ⬜ |
| `CORE-008.32` | 444–468 | تابع | `logging_exception_trace` | 0 | ⬜ |
| `CORE-008.33` | 470–631 | تابع | `logging_sanitize_event_meta` | 1 | ⬜ |
| `CORE-008.34` | 633–671 | تابع | `logging_sanitize_value` | 0 | ⬜ |
| `CORE-008.35` | 673–681 | تابع | `logging_safe_request_uri` | 0 | ⬜ |
| `CORE-008.36` | 683–779 | تابع | `logging_spool_write` | 3 | ⬜ |
| `CORE-008.37` | 781–785 | تابع | `logging_spool_stats_cache_invalidate` | 5 | ⬜ |
| `CORE-008.38` | 787–805 | تابع | `logging_spool_stats` | 3 | ⬜ |
| `CORE-008.39` | 807–868 | تابع | `logging_spool_stats_uncached` | 3 | ⬜ |
| `CORE-008.40` | 870–924 | تابع | `logging_increment_counter` | 3 | ⬜ |
| `CORE-008.41` | 926–958 | تابع | `logging_health_counters` | 1 | ⬜ |
| `CORE-008.42` | 960–981 | تابع | `logging_atomic_replace_file` | 7 | ⬜ |
| `CORE-008.43` | 983–1023 | تابع | `logging_worker_heartbeat` | 7 | ⬜ |
| `CORE-008.44` | 1025–1072 | تابع | `logging_emergency_failure` | **11** | ⬜ |
| `CORE-008.45` | 1074–1077 | تابع | `logging_emergency_failure_rotation_warning` | 0 | ⬜ |
| `CORE-008.46` | 1079–1083 | تابع | `logging_persistence_status` | 0 | ⬜ |
| `CORE-008.47` | 1085–1102 | تابع | `logging_record_failure` | 0 | ⬜ |
| `CORE-008.48` | 1104–1108 | تابع | `logging_json` | 0 | ⬜ |
| `CORE-008.49` | 1111–1153 | تابع | `logging_append_legacy_file` | 7 | ⬜ |
| `CORE-008.50` | 1155–1173 | تابع | `logging_replay_insert` | 6 | ⬜ |
| `CORE-008.51` | 1175–1236 | تابع | `logging_issue_upsert` | **8** | ⬜ |
| `CORE-008.52` | 1238–1251 | تابع | `logging_db_error_is_transient` | 0 | ⬜ |
| `CORE-008.53` | 1253–1269 | تابع | `logging_replay_error_classify` | 0 | ⬜ |
| `CORE-008.54` | 1271–1275 | تابع | `logging_db_circuit_path` | 0 | ⬜ |
| `CORE-008.55` | 1277–1282 | تابع | `logging_db_circuit_release_probe` | 2 | ⬜ |
| `CORE-008.56` | 1284–1296 | تابع | `logging_db_circuit_with_lock` | 3 | ⬜ |
| `CORE-008.57` | 1298–1319 | تابع | `logging_db_circuit_should_skip` | 3 | ⬜ |
| `CORE-008.58` | 1321–1336 | تابع | `logging_db_circuit_failure` | 7 | ⬜ |
| `CORE-008.59` | 1338–1344 | تابع | `logging_db_circuit_success` | **9** | ⬜ |
| `CORE-008.60` | 1346–1362 | تابع | `logging_duplicate_event_already_stored` | 5 | ⬜ |
| `CORE-008.61` | 1364–1411 | تابع | `logging_store_system` | 1 | ⬜ |
| `CORE-008.62` | 1413–1444 | تابع | `logging_normalize_activity_event` | 0 | ⬜ |
| `CORE-008.63` | 1446–1514 | تابع | `logging_store_activity` | 1 | ⬜ |
| `CORE-008.64` | 1516–1530 | تابع | `log_event` | 0 | ⬜ |
| `CORE-008.65` | 1531–1533 | ناحیه | `بلوک رویه‌ای 1531-1533` | 0 | ⬜ |
| `CORE-008.66` | 1534–1559 | تابع | `security_log_access_denied` | 4 | ⬜ |

</details>

<details>
<summary><b>CORE-009</b> — <code>lib/logging-csrf.php</code> (544 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-009.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-009.02` | 14–17 | تابع | `flash` | 0 | ⬜ |
| `CORE-009.03` | 19–54 | تابع | `ticket_service_ensure_schema` | 5 | ⬜ |
| `CORE-009.04` | 56–76 | تابع | `ticket_service_categories` | 0 | ⬜ |
| `CORE-009.05` | 78–98 | تابع | `ticket_service_category_map` | 0 | ⬜ |
| `CORE-009.06` | 100–112 | تابع | `system_logs_ensure` | 0 | ⬜ |
| `CORE-009.07` | 114–157 | تابع | `system_log` | 0 | ⬜ |
| `CORE-009.08` | 159–206 | تابع | `system_error_explain` | 0 | ⬜ |
| `CORE-009.09` | 208–212 | تابع | `system_error_mask` | 0 | ⬜ |
| `CORE-009.10` | 214–258 | تابع | `system_error_record` | 0 | ⬜ |
| `CORE-009.11` | 260–348 | ناحیه | `هندلرهای سراسری برای HTTP و CLI؛ خروجی هر محیط مست` | 2 | ⬜ |
| `CORE-009.12` | 349–368 | تابع | `app_error_log` | 0 | ⬜ |
| `CORE-009.13` | 370–396 | تابع | `system_log_meta_html` | 0 | ⬜ |
| `CORE-009.14` | 398–421 | تابع | `db_delete_in_batches` | 6 | ⬜ |
| `CORE-009.15` | 423–447 | تابع | `system_logs_prune` | 0 | ⬜ |
| `CORE-009.16` | 449–470 | تابع | `system_logs_recent` | 5 | ⬜ |
| `CORE-009.17` | 472–477 | تابع | `take_flash` | 0 | ⬜ |
| `CORE-009.18` | 479–482 | تابع | `csrf_field` | 0 | ⬜ |
| `CORE-009.19` | 484–510 | تابع | `csrf_token` | 0 | ⬜ |
| `CORE-009.20` | 512–529 | تابع | `csrf_discard_legacy_cookie` | 2 | ⬜ |
| `CORE-009.21` | 531–542 | تابع | `require_csrf` | 6 | ⬜ |

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
<summary><b>CORE-011</b> — <code>lib/logging-escalation.php</code> (100 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-011.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `CORE-011.02` | 19–27 | تابع | `logging_escalation_thresholds` | 0 | ⬜ |
| `CORE-011.03` | 29–57 | تابع | `logging_escalation_count` | 4 | ⬜ |
| `CORE-011.04` | 59–65 | تابع | `logging_escalation_is_critical` | 0 | ⬜ |
| `CORE-011.05` | 67–100 | تابع | `logging_smart_summary` | 4 | ⬜ |

</details>

<details>
<summary><b>CORE-012</b> — <code>lib/logging-integrity.php</code> (238 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-012.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `CORE-012.02` | 4–31 | تابع | `logging_integrity_manifest_hmac` | 0 | ⬜ |
| `CORE-012.03` | 33–55 | تابع | `logging_integrity_verify_archive_file` | 1 | ⬜ |
| `CORE-012.04` | 57–64 | تابع | `logging_integrity_has_older_snapshot` | 0 | ⬜ |
| `CORE-012.05` | 66–99 | تابع | `logging_integrity_verify_predecessor_chain` | 1 | ⬜ |
| `CORE-012.06` | 101–114 | تابع | `logging_integrity_verify_daily_manifest_error` | 0 | ⬜ |
| `CORE-012.07` | 116–119 | تابع | `logging_integrity_verify_daily_manifest` | 0 | ⬜ |
| `CORE-012.08` | 121–159 | تابع | `logging_integrity_read_archive_index` | 1 | ⬜ |
| `CORE-012.09` | 161–178 | تابع | `logging_integrity_merge_archive_indexes` | 0 | ⬜ |
| `CORE-012.10` | 180–198 | تابع | `logging_integrity_row_is_late` | 0 | ⬜ |
| `CORE-012.11` | 200–237 | تابع | `logging_integrity_verify_supplement_chain` | 2 | ⬜ |

</details>

<details>
<summary><b>CORE-013</b> — <code>lib/logging-replay.php</code> (261 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-013.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `CORE-013.02` | 6–43 | تابع | `logging_replay_poison_attempts` | 7 | ⬜ |
| `CORE-013.03` | 45–260 | تابع | `logging_replay_process_directory` | **9** | ⬜ |

</details>

<details>
<summary><b>CORE-014</b> — <code>lib/notify-users.php</code> (524 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-014.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-014.02` | 14–34 | تابع | `notify_user` | 1 | ⬜ |
| `CORE-014.03` | 36–48 | تابع | `notification_target_url` | 0 | ⬜ |
| `CORE-014.04` | 50–73 | تابع | `ticket_notification_user_can` | 0 | ⬜ |
| `CORE-014.05` | 75–95 | تابع | `ticket_notification_recipients` | 5 | ⬜ |
| `CORE-014.06` | 97–102 | تابع | `ticket_unassigned_notify_candidates` | 0 | ⬜ |
| `CORE-014.07` | 104–124 | تابع | `notify_ticket_parties` | 0 | ⬜ |
| `CORE-014.08` | 126–145 | تابع | `notify_ticket_staff` | 0 | ⬜ |
| `CORE-014.09` | 147–172 | تابع | `notification_user_sees_food_cancel` | 0 | ⬜ |
| `CORE-014.10` | 174–181 | تابع | `notification_visibility_sql` | 0 | ⬜ |
| `CORE-014.11` | 183–191 | تابع | `notification_ticket_visibility` | 5 | ⬜ |
| `CORE-014.12` | 193–214 | تابع | `notification_header_summary` | 5 | ⬜ |
| `CORE-014.13` | 216–221 | تابع | `is_sla_holiday` | 0 | ⬜ |
| `CORE-014.14` | 223–251 | تابع | `ldap_authenticate` | 0 | ⬜ |
| `CORE-014.15` | 253–425 | تابع | `ldap_authenticate_measured` | 4 | ⬜ |
| `CORE-014.16` | 427–432 | تابع | `normalize_employee_number` | 0 | ⬜ |
| `CORE-014.17` | 434–434 | ناحیه | `شناسه کاربری که این کد پرسنلی را دارد (۰ یعنی آزاد` | 0 | ⬜ |
| `CORE-014.18` | 435–443 | تابع | `employee_number_owner` | 0 | ⬜ |
| `CORE-014.19` | 445–511 | تابع | `upsert_domain_user` | 1 | ⬜ |
| `CORE-014.20` | 513–522 | تابع | `link_agent_assets_to_user` | 1 | ⬜ |

</details>

<details>
<summary><b>CORE-015</b> — <code>lib/settings-effects.php</code> (386 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-015.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-015.02` | 14–36 | تابع | `setting` | 0 | ⬜ |
| `CORE-015.03` | 38–44 | تابع | `setting_forget_cache` | 0 | ⬜ |
| `CORE-015.04` | 46–55 | تابع | `save_setting` | 1 | ⬜ |
| `CORE-015.05` | 57–113 | تابع | `triangle_effect_page_catalog` | 0 | ⬜ |
| `CORE-015.06` | 115–124 | تابع | `triangle_effect_valid_keys` | 0 | ⬜ |
| `CORE-015.07` | 126–142 | تابع | `triangle_effect_config_spec` | 0 | ⬜ |
| `CORE-015.08` | 144–175 | تابع | `triangle_effect_sanitize_config` | 0 | ⬜ |
| `CORE-015.09` | 177–181 | تابع | `display_effect_type` | 0 | ⬜ |
| `CORE-015.10` | 183–196 | تابع | `particle_effect_config_spec` | 0 | ⬜ |
| `CORE-015.11` | 198–227 | تابع | `particle_effect_sanitize_config` | 0 | ⬜ |
| `CORE-015.12` | 229–234 | تابع | `particle_effect_config` | 0 | ⬜ |
| `CORE-015.13` | 236–244 | تابع | `particle_effect_enabled_for_current_page` | 0 | ⬜ |
| `CORE-015.14` | 246–251 | تابع | `triangle_effect_config` | 0 | ⬜ |
| `CORE-015.15` | 253–275 | تابع | `triangle_effect_enabled_pages` | 0 | ⬜ |
| `CORE-015.16` | 277–289 | تابع | `triangle_effect_current_page_key` | 2 | ⬜ |
| `CORE-015.17` | 291–321 | تابع | `triangle_effect_script_tag` | 0 | ⬜ |
| `CORE-015.18` | 323–353 | تابع | `particle_effect_script_tag` | 0 | ⬜ |
| `CORE-015.19` | 355–362 | تابع | `key_role_setting_map` | 0 | ⬜ |
| `CORE-015.20` | 364–379 | تابع | `key_role_user_ids` | 0 | ⬜ |
| `CORE-015.21` | 381–384 | تابع | `user_is_key_role` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-016</b> — <code>lib/tickets-sla.php</code> (891 خط، 36 آدرس، بازبینی‌شده 0/36)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-016.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `CORE-016.02` | 20–24 | تابع | `ticket_number_cache_set` | 0 | ⬜ |
| `CORE-016.03` | 26–50 | تابع | `ticket_number` | 0 | ⬜ |
| `CORE-016.04` | 52–72 | تابع | `ticket_number_cache_prime` | 0 | ⬜ |
| `CORE-016.05` | 74–83 | تابع | `ticket_number_prefix` | 0 | ⬜ |
| `CORE-016.06` | 85–106 | تابع | `ticket_number_ensure_schema` | 0 | ⬜ |
| `CORE-016.07` | 108–205 | تابع | `ticket_hot_schema_ensure` | 4 | ⬜ |
| `CORE-016.08` | 207–261 | تابع | `ticket_number_ensure_schema_run` | **8** | ⬜ |
| `CORE-016.09` | 263–299 | تابع | `ticket_number_next` | 0 | ⬜ |
| `CORE-016.10` | 301–313 | تابع | `ticket_number_peek` | 0 | ⬜ |
| `CORE-016.11` | 315–340 | تابع | `ticket_number_sequence_floor` | 4 | ⬜ |
| `CORE-016.12` | 342–360 | تابع | `ticket_number_sequence_persist` | 5 | ⬜ |
| `CORE-016.13` | 362–456 | تابع | `ticket_number_autofill` | **10** | ⬜ |
| `CORE-016.14` | 458–462 | تابع | `is_it_agent` | 0 | ⬜ |
| `CORE-016.15` | 464–492 | تابع | `jalali_to_gregorian` | 0 | ⬜ |
| `CORE-016.16` | 494–520 | تابع | `gregorian_to_jalali` | 0 | ⬜ |
| `CORE-016.17` | 522–531 | تابع | `jalali_date` | 0 | ⬜ |
| `CORE-016.18` | 533–552 | تابع | `jalali_input_to_gregorian` | 0 | ⬜ |
| `CORE-016.19` | 554–569 | تابع | `find_or_create_department` | 1 | ⬜ |
| `CORE-016.20` | 571–574 | تابع | `persian_date` | 0 | ⬜ |
| `CORE-016.21` | 576–579 | تابع | `priority_sla_minutes` | 0 | ⬜ |
| `CORE-016.22` | 581–604 | تابع | `ensure_default_ola_policies` | 5 | ⬜ |
| `CORE-016.23` | 606–616 | تابع | `ola_policy_for` | 0 | ⬜ |
| `CORE-016.24` | 618–634 | تابع | `ola_deadlines` | 0 | ⬜ |
| `CORE-016.25` | 636–689 | تابع | `sla_business_hours` | 0 | ⬜ |
| `CORE-016.26` | 691–701 | تابع | `sla_business_hours_forget_cache` | 0 | ⬜ |
| `CORE-016.27` | 703–717 | تابع | `next_business_start` | 0 | ⬜ |
| `CORE-016.28` | 719–739 | تابع | `business_due_at` | 0 | ⬜ |
| `CORE-016.29` | 741–764 | تابع | `business_elapsed_minutes` | 0 | ⬜ |
| `CORE-016.30` | 766–782 | تابع | `ticket_sla_cycle_start` | 0 | ⬜ |
| `CORE-016.31` | 784–816 | تابع | `ticket_sla_transition` | 5 | ⬜ |
| `CORE-016.32` | 818–865 | تابع | `ticket_ola_transition` | 0 | ⬜ |
| `CORE-016.33` | 867–874 | تابع | `record_asset_history` | 1 | ⬜ |
| `CORE-016.34` | 876–879 | تابع | `status_label` | 0 | ⬜ |
| `CORE-016.35` | 881–884 | تابع | `priority_label` | 0 | ⬜ |
| `CORE-016.36` | 886–889 | تابع | `ticket_type_label` | 0 | ⬜ |

</details>

### زمان‌بندها (cron/) — `CRON`


<details>
<summary><b>CRON-001</b> — <code>cron/check_logging_health.php</code> (240 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-001.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 2 | ⬜ |
| `CRON-001.02` | 19–113 | ناحیه | `File-based heartbeats remain available during data` | **8** | ⬜ |
| `CRON-001.03` | 114–138 | ناحیه | `Queue age and terminal failures are monitored sepa` | 4 | ⬜ |
| `CRON-001.04` | 139–152 | ناحیه | `تشخیص اختلاف timezone؛ این بررسی هیچ timezone یا د` | 4 | ⬜ |
| `CRON-001.05` | 153–153 | ناحیه | `ممیزی سلامت سامانه: ساختار پایگاه دادهٔ همین نصب، ` | 0 | ⬜ |
| `CRON-001.06` | 154–154 | ناحیه | `مشکلات باز و ضربان Workerها. عمداً همین‌جا اجرا می` | 0 | ⬜ |
| `CRON-001.07` | 155–155 | ناحیه | `جدا، تا هشدارهایش از همان مسیر همیشگی (logging_hea` | 0 | ⬜ |
| `CRON-001.08` | 156–223 | ناحیه | `ادمین) عبور کند و سامانهٔ موازی ساخته نشود.` | 1 | ⬜ |
| `CRON-001.09` | 224–240 | ناحیه | `وضعیت‌هایی که برطرف شده‌اند، بدون حذف تاریخچه به ح` | **9** | ⬜ |

</details>

<details>
<summary><b>CRON-002</b> — <code>cron/deliver_logging_issue_notifications.php</code> (93 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-002.01` | 1–93 | ناحیه | `بلوک رویه‌ای 1-93` | 6 | ⬜ |

</details>

<details>
<summary><b>CRON-003</b> — <code>cron/domain_scan_worker.php</code> (71 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-003.01` | 1–71 | ناحیه | `بلوک رویه‌ای 1-71` | 4 | ⬜ |

</details>

<details>
<summary><b>CRON-004</b> — <code>cron/export_logging_agent_bundle.php</code> (55 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-004.01` | 1–55 | ناحیه | `بلوک رویه‌ای 1-55` | 7 | ⬜ |

</details>

<details>
<summary><b>CRON-005</b> — <code>cron/export_logging_integrity.php</code> (262 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-005.01` | 1–79 | ناحیه | `بلوک رویه‌ای 1-79` | 3 | ⬜ |
| `CRON-005.02` | 80–80 | ناحیه | `Freeze replay and spool writers while checking bac` | 0 | ⬜ |
| `CRON-005.03` | 81–81 | ناحیه | `from an immutable daily snapshot. A non-empty queu` | 0 | ⬜ |
| `CRON-005.04` | 82–262 | ناحیه | `blocks export conservatively; operators must drain` | **8** | ⬜ |

</details>

<details>
<summary><b>CRON-006</b> — <code>cron/food_ticket_worker.php</code> (319 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-006.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `CRON-006.02` | 26–26 | ناحیه | `max-minutes=N : بعد از N دقیقه با کد ۰ خارج می‌شود` | 0 | ⬜ |
| `CRON-006.03` | 27–40 | ناحیه | `(چرخش فایل لاگ روزانه + جلوگیری از نشت حافظه). پیش` | 1 | ⬜ |
| `CRON-006.04` | 41–41 | ناحیه | `فقط یک worker زنده مجاز است (قفل تا پایان عمر پردا` | 0 | ⬜ |
| `CRON-006.05` | 42–53 | ناحیه | `کد خروج ۳ = نمونهٔ دیگری در حال اجراست؛ wrapper در` | 3 | ⬜ |
| `CRON-006.06` | 54–69 | ناحیه | `حذف لاگ‌های قدیمی‌تر از ۱۴ روز` | **9** | ⬜ |
| `CRON-006.07` | 70–70 | ناحیه | `پنجرهٔ کاری و کش تنظیمات` | 0 | ⬜ |
| `CRON-006.08` | 71–71 | ناحیه | `تنظیمات تا این تعداد ثانیه کش می‌شوند. قبلاً هر چر` | 0 | ⬜ |
| `CRON-006.09` | 72–72 | ناحیه | `خوانده می‌شد؛ وقتی چرخه هر نیم ثانیه تکرار شود این` | 0 | ⬜ |
| `CRON-006.10` | 73–78 | ناحیه | `اضافه می‌شود. ۱۰ ثانیه آن‌قدر کوتاه هست که تغییر ت` | 0 | ⬜ |
| `CRON-006.11` | 79–319 | ناحیه | `مقادیر اولیه تا بخش خواب حتی در مسیرهای خطا هم متغ` | 6 | ⬜ |

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
<summary><b>CRON-012</b> — <code>cron/ticket_auto_close.php</code> (85 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-012.01` | 1–85 | ناحیه | `بلوک رویه‌ای 1-85` | 5 | ⬜ |

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

