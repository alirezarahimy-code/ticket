# نقشهٔ کد سامانه — فهرست آدرس‌دهی

> این فایل **تولید می‌شود**. دستی ویرایشش نکنید.
>
> بازتولید: `php tools/code_index.php > docs/system-map/BLOCKS.json` سپس `python tools/render_code_map.py`

## وضعیت بازبینی

| سنجه | مقدار |
|---|---|
| فایل‌های کد تولیدی | 695 |
| خطوط | 175,021 |
| آدرس‌ها (بلوک) | 8,291 |
| موضوع‌های قابل جست‌وجو | 41 صفحه + 102 مجوز + 85 جدول |
| بازبینی‌شده | 0 آدرس (0.0%) — 0 خط (0.0%) |
| باقی‌مانده | 8,291 آدرس |

## 🔎 فهرست موضوعی — از اینجا شروع کنید

دنبال بخشی از برنامه می‌گردید؟ **اینجا `Ctrl+F` بزنید**، نه در کد. نام فارسی بخش را بنویسید و مستقیم به آدرسش بروید.

### صفحه‌های سامانه

| بخش | نام فنی | مجوز لازم | آدرس‌های اصلی | کل |
|---|---|---|---|---|
| **audit** | `audit` | — | `PAGE-033.56` <sub>index.php:1006</sub><br>`CORE-011.33` <sub>lib/logging-audit.php:508</sub><br>`CORE-001.14` <sub>lib/activity-audit.php:457</sub><br>`CORE-017.03` <sub>lib/logging-replay.php:45</sub> | 28 |
| **chatbot api** | `chatbot-api` | — | `PAGE-033.34` <sub>index.php:810</sub> | 3 |
| **domain scan** | `domain-scan` | `domain.scan` | `PAGE-033.69` <sub>index.php:1576</sub><br>`PAGE-011.47` <sub>domain-scan.php:1810</sub><br>`PAGE-011.20` <sub>domain-scan.php:425</sub><br>`PAGE-011.17` <sub>domain-scan.php:346</sub> | 59 |
| **food personnel link** | `food-personnel-link` | — | `PAGE-033.32` <sub>index.php:728</sub><br>`PAGE-014.09` <sub>food-personnel-link.php:205</sub><br>`PAGE-014.04` <sub>food-personnel-link.php:39</sub><br>`PAGE-014.10` <sub>food-personnel-link.php:317</sub> | 31 |
| **food ticket** | `food-ticket` | — | `PAGE-033.28` <sub>index.php:481</sub><br>`PAGE-033.38` <sub>index.php:866</sub><br>`PAGE-033.45` <sub>index.php:919</sub><br>`TOOL-062.04` <sub>tools/food_system_router.php:21</sub> | 155 |
| **governance** | `governance` | — | `PAGE-055.03` <sub>modules/request-center/Pages/governance.php:17</sub><br>`PAGE-033.44` <sub>index.php:915</sub><br>`PAGE-077.20` <sub>request-center.php:208</sub><br>`PAGE-069.11` <sub>modules/request-center/lib/governance.php:65</sub> | 35 |
| **help** | `help` | — | `PAGE-033.52` <sub>index.php:951</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 5 |
| **local accounts** | `local-accounts` | — | `PAGE-033.42` <sub>index.php:894</sub><br>`PAGE-036.03` <sub>local-accounts.php:25</sub><br>`PAGE-036.05` <sub>local-accounts.php:81</sub><br>`PAGE-036.06` <sub>local-accounts.php:138</sub> | 11 |
| **logging dashboard** | `logging-dashboard` | — | `PAGE-033.57` <sub>index.php:1015</sub><br>`ASSET-017.01` <sub>assets/logging-dashboard.css:1</sub><br>`PAGE-037.04` <sub>logging-dashboard.php:114</sub><br>`PAGE-037.02` <sub>logging-dashboard.php:4</sub> | 11 |
| **release notes** | `release-notes` | — | `PAGE-033.53` <sub>index.php:970</sub><br>`CORE-020.07` <sub>lib/release-notes.php:177</sub><br>`CORE-020.03` <sub>lib/release-notes.php:112</sub><br>`CORE-020.01` <sub>lib/release-notes.php:1</sub> | 12 |
| **system health** | `system-health` | — | `PAGE-033.58` <sub>index.php:1019</sub><br>`PAGE-079.28` <sub>system-health.php:947</sub><br>`PAGE-079.03` <sub>system-health.php:25</sub><br>`PAGE-079.04` <sub>system-health.php:43</sub> | 36 |
| **اعلان‌ها** | `notifications` | — | `PAGE-033.55` <sub>index.php:988</sub><br>`ACT-010.03` <sub>actions/notification-actions.php:23</sub><br>`PAGE-033.54` <sub>index.php:980</sub><br>`DB-004.30` <sub>install-empty-db.sql:443</sub> | 51 |
| **به‌روزرسانی و پشتیبان‌گیری** | `backup` | `backup.view` | `PAGE-005.04` <sub>backup.php:32</sub><br>`PAGE-005.02` <sub>backup.php:6</sub><br>`PAGE-005.03` <sub>backup.php:12</sub><br>`PAGE-005.05` <sub>backup.php:78</sub> | 45 |
| **تاریخچه CD/DVD** | `cd-dvd-history` | `cddvd.history` | `PAGE-033.49` <sub>index.php:940</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 12 |
| **تاریخچه دارایی** | `asset-history` | `asset.history` | `PAGE-033.68` <sub>index.php:1558</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 5 |
| **تحلیل و چارت‌ها** | `analytics` | `analytics.view` | `PAGE-051.03` <sub>modules/request-center/Pages/analytics.php:12</sub><br>`PAGE-077.18` <sub>request-center.php:200</sub><br>`PAGE-051.01` <sub>modules/request-center/Pages/analytics.php:1</sub><br>`PAGE-051.02` <sub>modules/request-center/Pages/analytics.php:4</sub> | 15 |
| **تست جمع‌آوری اطلاعات** | `inventory-diagnostics` | `inventory.diagnostics` | `PAGE-033.67` <sub>index.php:1526</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 8 |
| **تغییر رمز عبور** | `change-password` | — | `PAGE-033.26` <sub>index.php:456</sub><br>`PAGE-033.41` <sub>index.php:891</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 8 |
| **تقویم SLA** | `holidays` | `holidays.view` | `ACT-009.07` <sub>actions/holiday-actions.php:102</sub><br>`PAGE-033.65` <sub>index.php:1418</sub><br>`DB-004.35` <sub>install-empty-db.sql:517</sub><br>`DB-007.26` <sub>migrations/003-sync-schema-151.sql:386</sub> | 46 |
| **تنظیمات سامانه** | `settings` | — | `PAGE-033.73` <sub>index.php:1957</sub><br>`PAGE-033.40` <sub>index.php:882</sub><br>`DB-004.04` <sub>install-empty-db.sql:22</sub><br>`DB-007.02` <sub>migrations/003-sync-schema-151.sql:5</sub> | 73 |
| **ثبت درخواست** | `new-ticket` | — | `PAGE-053.03` <sub>modules/request-center/Pages/create.php:19</sub><br>`PAGE-077.12` <sub>request-center.php:176</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 16 |
| **جزئیات درخواست** | `ticket` | — | `PAGE-054.03` <sub>modules/request-center/Pages/detail.php:17</sub><br>`PAGE-077.13` <sub>request-center.php:180</sub><br>`CRON-013.01` <sub>cron/ticket_auto_close.php:1</sub><br>`PAGE-068.27` <sub>modules/request-center/lib/functions.php:718</sub> | 46 |
| **جست‌وجو** | `search` | — | `PAGE-033.63` <sub>index.php:1219</sub><br>`PAGE-033.62` <sub>index.php:1211</sub><br>`PAGE-030.03` <sub>global-search.php:31</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 49 |
| **دارایی‌ها** | `assets` | `assets.view` | `ACT-014.09` <sub>actions/settings-actions.php:319</sub><br>`ACT-003.08` <sub>actions/asset-actions.php:200</sub><br>`PAGE-033.70` <sub>index.php:1695</sub><br>`DB-004.36` <sub>install-empty-db.sql:527</sub> | 205 |
| **داشبورد** | `dashboard` | `dash.view` | `PAGE-033.75` <sub>index.php:2482</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub><br>`CORE-022.16` <sub>lib/settings-effects.php:277</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 27 |
| **دانش‌نامه** | `knowledge` | `knowledge.view` | `PAGE-057.03` <sub>modules/request-center/Pages/knowledge.php:16</sub><br>`PAGE-077.19` <sub>request-center.php:204</sub><br>`PAGE-057.01` <sub>modules/request-center/Pages/knowledge.php:1</sub><br>`PAGE-057.02` <sub>modules/request-center/Pages/knowledge.php:4</sub> | 14 |
| **سفارش غذا** | `food-order` | — | `PAGE-033.29` <sub>index.php:495</sub><br>`PAGE-033.46` <sub>index.php:924</sub><br>`PAGE-033.47` <sub>index.php:932</sub><br>`TOOL-062.03` <sub>tools/food_system_router.php:18</sub> | 217 |
| **شناسنامه دارایی** | `asset` | `asset.view` | `PAGE-033.71` <sub>index.php:1739</sub><br>`PAGE-002.02` <sub>asset-inventory-page.php:4</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 11 |
| **شناسنامه سیستم‌ها** | `inventory` | `inventory.view` | `PAGE-035.32` <sub>inventory.php:770</sub><br>`PAGE-035.30` <sub>inventory.php:680</sub><br>`PAGE-035.02` <sub>inventory.php:7</sub><br>`PAGE-035.17` <sub>inventory.php:265</sub> | 62 |
| **صف کاری** | `queue` | `queue.view` | `PAGE-059.03` <sub>modules/request-center/Pages/queue.php:12</sub><br>`PAGE-077.14` <sub>request-center.php:184</sub><br>`PAGE-059.01` <sub>modules/request-center/Pages/queue.php:1</sub><br>`PAGE-059.02` <sub>modules/request-center/Pages/queue.php:4</sub> | 40 |
| **لاگ سامانه** | `activity-log` | — | `PAGE-033.60` <sub>index.php:1029</sub><br>`CORE-021.03` <sub>lib/render.php:171</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 13 |
| **مدیریت بات** | `chatbot-admin` | `chatbot.manage` | `PAGE-033.64` <sub>index.php:1315</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 8 |
| **مدیریت سازمان** | `organization` | `org.view` | `PAGE-074.39` <sub>organization.php:486</sub><br>`PAGE-033.72` <sub>index.php:1766</sub><br>`PAGE-033.27` <sub>index.php:467</sub><br>`PAGE-074.02` <sub>organization.php:4</sub> | 194 |
| **پروفایل کاربری** | `profile` | `profile.edit` | `PAGE-033.43` <sub>index.php:897</sub><br>`PAGE-004.16` <sub>asset-profile.php:509</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 13 |
| **پنل سوپروایزر** | `supervisor` | `supervisor.panel` | `PAGE-063.03` <sub>modules/request-center/Pages/supervisor.php:16</sub><br>`PAGE-045.03` <sub>modules/request-center/Actions/supervisor-actions.php:26</sub><br>`PAGE-077.15` <sub>request-center.php:188</sub><br>`PAGE-063.01` <sub>modules/request-center/Pages/supervisor.php:1</sub> | 107 |
| **چاپ کنترل تردد** | `traffic-control-print` | `traffic.view` | `PAGE-033.51` <sub>index.php:947</sub> | 7 |
| **کاتالوگ خدمات** | `services` | `services.view` | `PAGE-061.03` <sub>modules/request-center/Pages/services.php:12</sub><br>`PAGE-077.16` <sub>request-center.php:192</sub><br>`PAGE-061.06` <sub>modules/request-center/Pages/services.php:147</sub><br>`PAGE-061.07` <sub>modules/request-center/Pages/services.php:171</sub> | 26 |
| **کنترل CD/DVD** | `cd-dvd` | `cddvd.view` | `PAGE-033.48` <sub>index.php:936</sub><br>`PAGE-007.33` <sub>cd-dvd.php:406</sub><br>`PAGE-007.19` <sub>cd-dvd.php:153</sub><br>`PAGE-007.39` <sub>cd-dvd.php:555</sub> | 72 |
| **کنترل تردد** | `traffic-control` | `traffic.view` | `PAGE-033.30` <sub>index.php:507</sub><br>`PAGE-033.50` <sub>index.php:944</sub><br>`PAGE-081.63` <sub>traffic-control.php:981</sub><br>`PAGE-081.16` <sub>traffic-control.php:119</sub> | 103 |
| **گزارش خطا** | `logs` | — | `PAGE-033.74` <sub>index.php:2460</sub><br>`PAGE-033.59` <sub>index.php:1023</sub><br>`CORE-021.03` <sub>lib/render.php:171</sub><br>`CORE-022.05` <sub>lib/settings-effects.php:57</sub> | 19 |
| **گزارش‌ها** | `reports` | `reports.view` | `PAGE-060.03` <sub>modules/request-center/Pages/reports.php:12</sub><br>`PAGE-077.17` <sub>request-center.php:196</sub><br>`PAGE-060.01` <sub>modules/request-center/Pages/reports.php:1</sub><br>`PAGE-060.02` <sub>modules/request-center/Pages/reports.php:4</sub> | 23 |

### مجوزها و دسترسی‌ها

<details>
<summary>۱۰۱ مجوز — باز کنید</summary>

| گروه | مجوز | کد | آدرس‌های اصلی | کل |
|---|---|---|---|---|
| داشبورد و پایه | **مشاهدهٔ داشبورد** | `dash.view` | `PAGE-075.26` <sub>permissions.php:488</sub><br>`PAGE-075.03` <sub>permissions.php:28</sub><br>`TOOL-087.12` <sub>tools/test_access_coverage_offline.php:108</sub> | 10 |
| داشبورد و پایه | **جست‌وجوی سراسری** | `search.global` | `PAGE-033.62` <sub>index.php:1211</sub> | 3 |
| داشبورد و پایه | **اعلان‌های من** | `notif.view` | `PAGE-033.54` <sub>index.php:980</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 6 |
| داشبورد و پایه | **ویرایش پروفایل خودم** | `profile.edit` | `PAGE-075.03` <sub>permissions.php:28</sub><br>`PAGE-033.34` <sub>index.php:810</sub><br>`PAGE-075.16` <sub>permissions.php:351</sub> | 3 |
| داشبورد و پایه | **استفاده از بات پاسخ‌گو** | `chatbot.use` | `PAGE-009.27` <sub>chatbot.php:694</sub><br>`PAGE-009.123` <sub>chatbot.php:3200</sub> | 5 |
| درخواست | **ثبت درخواست جدید** | `ticket.create` | `PAGE-046.03` <sub>modules/request-center/Actions/ticket-actions.php:29</sub><br>`PAGE-046.04` <sub>modules/request-center/Actions/ticket-actions.php:262</sub><br>`PAGE-054.03` <sub>modules/request-center/Pages/detail.php:17</sub> | 20 |
| درخواست | **مشاهدهٔ درخواست‌های خودم** | `ticket.view_own` | `PAGE-054.03` <sub>modules/request-center/Pages/detail.php:17</sub><br>`CORE-004.14` <sub>lib/auth-scope.php:199</sub><br>`CORE-004.23` <sub>lib/auth-scope.php:593</sub> | 22 |
| درخواست | **مشاهدهٔ درخواست‌های واحد/زیرمجموعه** | `ticket.view_unit` | `PAGE-054.03` <sub>modules/request-center/Pages/detail.php:17</sub><br>`CORE-004.14` <sub>lib/auth-scope.php:199</sub><br>`CORE-004.23` <sub>lib/auth-scope.php:593</sub> | 25 |
| درخواست | **مشاهدهٔ همهٔ درخواست‌ها** | `ticket.view_all` | `PAGE-054.03` <sub>modules/request-center/Pages/detail.php:17</sub><br>`CORE-004.14` <sub>lib/auth-scope.php:199</sub><br>`CORE-004.23` <sub>lib/auth-scope.php:593</sub> | 17 |
| درخواست | **پاسخ/مکالمه در درخواست** | `ticket.reply` | `PAGE-046.07` <sub>modules/request-center/Actions/ticket-actions.php:533</sub><br>`PAGE-054.03` <sub>modules/request-center/Pages/detail.php:17</sub><br>`PAGE-046.08` <sub>modules/request-center/Actions/ticket-actions.php:607</sub> | 10 |
| درخواست | **ویرایش** | `ticket.edit` | `PAGE-046.10` <sub>modules/request-center/Actions/ticket-actions.php:972</sub><br>`PAGE-046.12` <sub>modules/request-center/Actions/ticket-actions.php:1305</sub><br>`PAGE-054.03` <sub>modules/request-center/Pages/detail.php:17</sub> | 30 |
| درخواست | **ارجاع درخواست به کارشناس** | `ticket.assign` | `CORE-004.15` <sub>lib/auth-scope.php:296</sub> | 11 |
| درخواست | **ویرایش** | `ticket.bulk` | `PAGE-040.04` <sub>modules/request-center/Actions/bulk-actions.php:44</sub> | 4 |
| درخواست | **امتیازدهی به درخواست** | `ticket.rate` | `PAGE-054.03` <sub>modules/request-center/Pages/detail.php:17</sub><br>`PAGE-045.06` <sub>modules/request-center/Actions/supervisor-actions.php:503</sub> | 6 |
| درخواست | **اتصال دارایی به درخواست** | `ticket.attach_asset` | `PAGE-040.03` <sub>modules/request-center/Actions/bulk-actions.php:24</sub><br>`PAGE-075.03` <sub>permissions.php:28</sub><br>`PAGE-075.16` <sub>permissions.php:351</sub> | 3 |
| درخواست | **صف کاری** | `queue.view` | `PAGE-059.03` <sub>modules/request-center/Pages/queue.php:12</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 25 |
| درخواست | **پنل سوپروایزر** | `supervisor.panel` | `PAGE-063.03` <sub>modules/request-center/Pages/supervisor.php:16</sub><br>`PAGE-045.03` <sub>modules/request-center/Actions/supervisor-actions.php:26</sub><br>`PAGE-045.04` <sub>modules/request-center/Actions/supervisor-actions.php:175</sub> | 34 |
| درخواست | **ویرایش** | `supervisor.decide` | `PAGE-063.03` <sub>modules/request-center/Pages/supervisor.php:16</sub><br>`PAGE-045.04` <sub>modules/request-center/Actions/supervisor-actions.php:175</sub><br>`TOOL-123.07` <sub>tools/test_key_roles_composition_offline.php:59</sub> | 15 |
| درخواست | **بازگشایی/برگشت درخواست** | `supervisor.reopen` | `PAGE-063.03` <sub>modules/request-center/Pages/supervisor.php:16</sub><br>`PAGE-045.05` <sub>modules/request-center/Actions/supervisor-actions.php:406</sub><br>`TOOL-123.07` <sub>tools/test_key_roles_composition_offline.php:59</sub> | 9 |
| خدمات و دسته‌بندی | **مشاهدهٔ خدمات** | `services.view` | `PAGE-075.03` <sub>permissions.php:28</sub><br>`PAGE-030.03` <sub>global-search.php:31</sub><br>`PAGE-033.34` <sub>index.php:810</sub> | 5 |
| خدمات و دسته‌بندی | **ویرایش** | `services.manage` | `PAGE-044.03` <sub>modules/request-center/Actions/service-actions.php:29</sub><br>`PAGE-044.07` <sub>modules/request-center/Actions/service-actions.php:155</sub><br>`PAGE-075.03` <sub>permissions.php:28</sub> | 8 |
| خدمات و دسته‌بندی | **مدیریت دسته‌بندی خدمات** | `service_cat.manage` | `PAGE-075.03` <sub>permissions.php:28</sub><br>`ACT-017.03` <sub>actions/user-structure-actions.php:29</sub><br>`PAGE-075.16` <sub>permissions.php:351</sub> | 3 |
| خدمات و دسته‌بندی | **مدیریت فیلدهای خدمت** | `service_field.manage` | `PAGE-044.04` <sub>modules/request-center/Actions/service-actions.php:69</sub> | 3 |
| دانش‌نامه | **مشاهدهٔ دانش‌نامه** | `knowledge.view` | `PAGE-075.03` <sub>permissions.php:28</sub><br>`PAGE-030.03` <sub>global-search.php:31</sub><br>`PAGE-033.34` <sub>index.php:810</sub> | 5 |
| دانش‌نامه | **ویرایش** | `knowledge.manage` | `PAGE-044.09` <sub>modules/request-center/Actions/service-actions.php:206</sub> | 4 |
| کنترل CD/DVD | **مشاهدهٔ فرم کنترل CD/DVD** | `cddvd.view` | `CORE-021.02` <sub>lib/render.php:14</sub> | 11 |
| کنترل CD/DVD | **ثبت خروج رسانه** | `cddvd.submit_out` | `PAGE-075.03` <sub>permissions.php:28</sub><br>`PAGE-007.17` <sub>cd-dvd.php:128</sub><br>`PAGE-075.15` <sub>permissions.php:345</sub> | 4 |
| کنترل CD/DVD | **ثبت ورود رسانه (بازرسی)** | `cddvd.submit_in` | `PAGE-075.03` <sub>permissions.php:28</sub><br>`TEST-151.05` <sub>tests/test_permissions_followup_static.py:30</sub><br>`PAGE-007.16` <sub>cd-dvd.php:120</sub> | 4 |
| کنترل CD/DVD | **ویرایش** | `cddvd.edit` | `PAGE-033.73` <sub>index.php:1957</sub><br>`PAGE-075.26` <sub>permissions.php:488</sub><br>`PAGE-075.03` <sub>permissions.php:28</sub> | 9 |
| کنترل CD/DVD | **مشاهدهٔ همهٔ ثبت‌ها (نه فقط واحد خود)** | `cddvd.view_all` | `PAGE-075.03` <sub>permissions.php:28</sub><br>`PAGE-007.11` <sub>cd-dvd.php:65</sub><br>`PAGE-075.16` <sub>permissions.php:351</sub> | 3 |
| کنترل CD/DVD | **تاریخچهٔ سریال** | `cddvd.history` | `PAGE-075.03` <sub>permissions.php:28</sub><br>`PAGE-030.03` <sub>global-search.php:31</sub><br>`PAGE-033.34` <sub>index.php:810</sub> | 4 |
| کنترل CD/DVD | **خروجی Excel/CSV** | `cddvd.export` | `PAGE-033.31` <sub>index.php:635</sub><br>`PAGE-075.03` <sub>permissions.php:28</sub><br>`PAGE-007.13` <sub>cd-dvd.php:87</sub> | 4 |
| کنترل تردد مراجعین | **مشاهدهٔ کنترل تردد** | `traffic.view` | `PAGE-081.63` <sub>traffic-control.php:981</sub><br>`PAGE-081.59` <sub>traffic-control.php:888</sub><br>`PAGE-082.23` <sub>traffic-import.php:853</sub> | 19 |
| کنترل تردد مراجعین | **ثبت، ویرایش و ورود گروهی سوابق** | `traffic.manage` | `PAGE-081.63` <sub>traffic-control.php:981</sub><br>`PAGE-081.54` <sub>traffic-control.php:786</sub><br>`PAGE-082.23` <sub>traffic-import.php:853</sub> | 10 |
| کنترل تردد مراجعین | **مقصدهای ملاقات** | `traffic.destinations` | `PAGE-081.63` <sub>traffic-control.php:981</sub><br>`PAGE-081.05` <sub>traffic-control.php:16</sub><br>`TEST-151.04` <sub>tests/test_permissions_followup_static.py:12</sub> | 6 |
| کنترل تردد مراجعین | **ویرایش** | `traffic.destinations_edit` | `PAGE-081.06` <sub>traffic-control.php:21</sub><br>`TEST-151.04` <sub>tests/test_permissions_followup_static.py:12</sub> | 4 |
| دارایی و شناسنامه فنی | **فهرست شناسنامه‌ها** | `assets.view` | `PAGE-033.70` <sub>index.php:1695</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 10 |
| دارایی و شناسنامه فنی | **فقط دارایی‌های واحد خودم** | `assets.own_unit` | `CORE-004.26` <sub>lib/auth-scope.php:665</sub> | 4 |
| دارایی و شناسنامه فنی | **مشاهدهٔ شناسنامهٔ دارایی** | `asset.view` | `PAGE-075.03` <sub>permissions.php:28</sub><br>`PAGE-033.34` <sub>index.php:810</sub><br>`PAGE-075.08` <sub>permissions.php:247</sub> | 4 |
| دارایی و شناسنامه فنی | **ویرایش** | `asset.edit` | `ACT-003.14` <sub>actions/asset-actions.php:512</sub> | 4 |
| دارایی و شناسنامه فنی | **استخراج سخت‌افزار (همین سرور)** | `asset.extract` | `ACT-003.03` <sub>actions/asset-actions.php:36</sub> | 3 |
| دارایی و شناسنامه فنی | **ویرایش** | `asset.remote_extract` | `ACT-003.12` <sub>actions/asset-actions.php:436</sub><br>`ACT-003.06` <sub>actions/asset-actions.php:141</sub><br>`ACT-003.07` <sub>actions/asset-actions.php:158</sub> | 10 |
| دارایی و شناسنامه فنی | **آماده‌سازی سیستم‌های مقصد (فعال‌سازی WinRM)** | `asset.remote_prepare` | `ACT-003.11` <sub>actions/asset-actions.php:371</sub><br>`ACT-003.10` <sub>actions/asset-actions.php:295</sub> | 7 |
| دارایی و شناسنامه فنی | **تاریخچهٔ دارایی** | `asset.history` | `PAGE-033.68` <sub>index.php:1558</sub> | 4 |
| دارایی و شناسنامه فنی | **پینگ سیستم‌های انتخابی** | `asset.ping` | `ACT-003.08` <sub>actions/asset-actions.php:200</sub><br>`PAGE-033.70` <sub>index.php:1695</sub> | 4 |
| دارایی و شناسنامه فنی | **فهرست موجودی** | `inventory.view` | `ACT-003.14` <sub>actions/asset-actions.php:512</sub><br>`PAGE-033.66` <sub>index.php:1429</sub> | 6 |
| دارایی و شناسنامه فنی | **تشخیص موجودی** | `inventory.diagnostics` | `PAGE-033.67` <sub>index.php:1526</sub> | 6 |
| دارایی و شناسنامه فنی | **ورود کامپیوترهای دامنه** | `domain.import` | `ACT-003.05` <sub>actions/asset-actions.php:133</sub> | 6 |
| دارایی و شناسنامه فنی | **اسکن دامنه** | `domain.scan` | `PAGE-033.69` <sub>index.php:1576</sub> | 10 |
| گزارش و تحلیل | **گزارش‌ها** | `reports.view` | `PAGE-060.03` <sub>modules/request-center/Pages/reports.php:12</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 11 |
| گزارش و تحلیل | **چارت‌ها** | `analytics.view` | `PAGE-051.03` <sub>modules/request-center/Pages/analytics.php:12</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 9 |
| گزارش و تحلیل | **ممیزی** | `audit.view` | `PAGE-033.56` <sub>index.php:1006</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 7 |
| گزارش و تحلیل | **گزارش خطا** | `logs.view` | `PAGE-033.74` <sub>index.php:2460</sub><br>`PAGE-033.59` <sub>index.php:1023</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 14 |
| گزارش و تحلیل | **سلامت سامانه و خطاهای فنی** | `system.health` | `PAGE-079.28` <sub>system-health.php:947</sub><br>`PAGE-079.30` <sub>system-health.php:1118</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 11 |
| گزارش و تحلیل | **ویرایش وضعیت و نگهداری لاگ** | `system.health_edit` | `PAGE-079.30` <sub>system-health.php:1118</sub><br>`PAGE-079.25` <sub>system-health.php:787</sub> | 5 |
| گزارش و تحلیل | **فعالیت کاربران** | `logs.activity` | `PAGE-033.59` <sub>index.php:1023</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 7 |
| گزارش و تحلیل | **خروجی فعالیت کاربران** | `logs.activity_export` | `PAGE-033.30` <sub>index.php:507</sub><br>`PAGE-033.60` <sub>index.php:1029</sub><br>`PAGE-075.03` <sub>permissions.php:28</sub> | 5 |
| سامانه مدیریت غذا | **داشبورد** | `food.dashboard` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.09` <sub>food-ticket.php:78</sub> | 9 |
| سامانه مدیریت غذا | **پایش لحظه‌ای** | `food.monitor` | `PAGE-033.38` <sub>index.php:866</sub><br>`PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub> | 11 |
| سامانه مدیریت غذا | **کارکنان** | `food.employees` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.09` <sub>food-ticket.php:78</sub> | 8 |
| سامانه مدیریت غذا | **ویرایش** | `food.employees_edit` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.10` <sub>food-ticket.php:86</sub> | 5 |
| سامانه مدیریت غذا | **برنامه غذایی** | `food.menu` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-013.108` <sub>food-order.php:3586</sub><br>`PAGE-013.103` <sub>food-order.php:3272</sub> | 16 |
| سامانه مدیریت غذا | **ویرایش** | `food.menu_edit` | `PAGE-015.22` <sub>food-reconciliation.php:785</sub><br>`PAGE-013.74` <sub>food-order.php:2267</sub><br>`PAGE-029.139` <sub>food-ticket.php:5862</sub> | 25 |
| سامانه مدیریت غذا | **گروه‌های غذا** | `food.groups` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.09` <sub>food-ticket.php:78</sub> | 12 |
| سامانه مدیریت غذا | **ویرایش** | `food.groups_edit` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.10` <sub>food-ticket.php:86</sub><br>`PAGE-019.14` <sub>food-ticket-groups.php:630</sub> | 10 |
| سامانه مدیریت غذا | **گزارش‌ها** | `food.reports` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-013.108` <sub>food-order.php:3586</sub><br>`PAGE-015.25` <sub>food-reconciliation.php:991</sub> | 23 |
| تنظیمات سیستم مدیریت غذا | **اتصال پایگاه‌ها و تردد** | `food.db` | `PAGE-025.02` <sub>food-ticket-selftest.php:20</sub><br>`PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub> | 12 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.db_edit` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.10` <sub>food-ticket.php:86</sub> | 9 |
| تنظیمات سیستم مدیریت غذا | **چاپگر سیستم** | `food.printer` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.09` <sub>food-ticket.php:78</sub> | 10 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.printer_edit` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.10` <sub>food-ticket.php:86</sub> | 6 |
| تنظیمات سیستم مدیریت غذا | **کارت مهمان** | `food.guest` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.09` <sub>food-ticket.php:78</sub> | 10 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.guest_edit` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.10` <sub>food-ticket.php:86</sub> | 7 |
| تنظیمات سیستم مدیریت غذا | **طراحی فیش** | `food.design` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.09` <sub>food-ticket.php:78</sub> | 7 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.design_edit` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.10` <sub>food-ticket.php:86</sub> | 6 |
| تنظیمات سیستم مدیریت غذا | **سلامت سیستم** | `food.health` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.09` <sub>food-ticket.php:78</sub> | 6 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.health_edit` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.10` <sub>food-ticket.php:86</sub> | 5 |
| تنظیمات سیستم مدیریت غذا | **ثبت دستی فیش/تردد** | `food.manual_attendance` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-029.09` <sub>food-ticket.php:78</sub> | 8 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.manual_attendance_edit` | `PAGE-019.65` <sub>food-ticket-groups.php:2913</sub><br>`PAGE-019.74` <sub>food-ticket-groups.php:3559</sub><br>`PAGE-029.139` <sub>food-ticket.php:5862</sub> | 9 |
| تنظیمات سیستم مدیریت غذا | **مدیریت مهلت اضطراری سفارش غذا** | `food.emergency_window` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-013.108` <sub>food-order.php:3586</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub> | 8 |
| تنظیمات سیستم مدیریت غذا | **ویرایش** | `food.emergency_window_edit` | `PAGE-029.139` <sub>food-ticket.php:5862</sub><br>`PAGE-029.12` <sub>food-ticket.php:137</sub><br>`PAGE-013.53` <sub>food-order.php:1410</sub> | 9 |
| سفارش غذای کارکنان | **ثبت و مشاهدهٔ سفارش خود** | `foodorder.self` | `PAGE-033.46` <sub>index.php:924</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 12 |
| سازمان | **مشاهدهٔ چارت سازمانی** | `org.view` | `PAGE-033.72` <sub>index.php:1766</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 7 |
| سازمان | **ویرایش** | `org.manage` | `ACT-011.13` <sub>actions/org-actions.php:382</sub><br>`ACT-011.18` <sub>actions/org-actions.php:617</sub><br>`ACT-011.14` <sub>actions/org-actions.php:409</sub> | 20 |
| تنظیمات و سیستم | **تنظیمات عمومی و افکت نمایشی** | `settings.general` | `PAGE-033.73` <sub>index.php:1957</sub><br>`PAGE-033.40` <sub>index.php:882</sub><br>`CORE-022.17` <sub>lib/settings-effects.php:291</sub> | 13 |
| تنظیمات و سیستم | **ویرایش تنظیمات عمومی و افکت نمایشی** | `settings.general_edit` | `ACT-014.08` <sub>actions/settings-actions.php:165</sub><br>`PAGE-033.73` <sub>index.php:1957</sub><br>`ACT-014.07` <sub>actions/settings-actions.php:91</sub> | 6 |
| تنظیمات و سیستم | **اتصال دامین و اسکن دامنه** | `settings.domain` | `PAGE-033.73` <sub>index.php:1957</sub><br>`PAGE-033.40` <sub>index.php:882</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 10 |
| تنظیمات و سیستم | **ویرایش** | `settings.domain_edit` | `ACT-014.08` <sub>actions/settings-actions.php:165</sub><br>`PAGE-033.73` <sub>index.php:1957</sub><br>`ACT-014.06` <sub>actions/settings-actions.php:77</sub> | 5 |
| تنظیمات و سیستم | **مدیریت کاربران و دسترسی‌ها** | `settings.users` | `PAGE-033.73` <sub>index.php:1957</sub><br>`PAGE-033.40` <sub>index.php:882</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 8 |
| تنظیمات و سیستم | **ویرایش** | `settings.users_edit` | `PAGE-033.73` <sub>index.php:1957</sub><br>`ACT-017.08` <sub>actions/user-structure-actions.php:358</sub><br>`ACT-014.05` <sub>actions/settings-actions.php:69</sub> | 6 |
| تنظیمات و سیستم | **نقش‌های کلیدی** | `settings.key_roles` | `PAGE-033.73` <sub>index.php:1957</sub><br>`PAGE-033.40` <sub>index.php:882</sub> | 5 |
| تنظیمات و سیستم | **ویرایش** | `settings.key_roles_edit` | `PAGE-033.73` <sub>index.php:1957</sub> | 4 |
| تنظیمات و سیستم | **پشتیبان‌گیری/بازگردانی** | `backup.view` | `PAGE-033.61` <sub>index.php:1172</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 5 |
| تنظیمات و سیستم | **ویرایش** | `backup.manage` | `ACT-004.02` <sub>actions/backup-actions.php:18</sub><br>`ACT-004.03` <sub>actions/backup-actions.php:25</sub><br>`PAGE-033.61` <sub>index.php:1172</sub> | 7 |
| تنظیمات و سیستم | **مشاهدهٔ به‌روزرسانی سامانه** | `update.view` | `PAGE-033.61` <sub>index.php:1172</sub><br>`PAGE-075.03` <sub>permissions.php:28</sub> | 2 |
| تنظیمات و سیستم | **اجرای به‌روزرسانی سامانه** | `update.apply` | `ACT-004.04` <sub>actions/backup-actions.php:41</sub> | 3 |
| تنظیمات و سیستم | **مدیریت پرسش‌ها و پاسخ‌های بات** | `chatbot.manage` | `PAGE-033.64` <sub>index.php:1315</sub><br>`PAGE-033.34` <sub>index.php:810</sub><br>`CORE-021.02` <sub>lib/render.php:14</sub> | 4 |
| تنظیمات و سیستم | **تنظیمات OLA/SLA** | `ola.view` | `PAGE-058.03` <sub>modules/request-center/Pages/ola.php:17</sub> | 5 |
| تنظیمات و سیستم | **ویرایش** | `ola.manage` | `PAGE-043.03` <sub>modules/request-center/Actions/ola-actions.php:17</sub> | 5 |
| تنظیمات و سیستم | **تغییر و مشکل** | `governance.view` | `PAGE-055.03` <sub>modules/request-center/Pages/governance.php:17</sub> | 7 |
| تنظیمات و سیستم | **ویرایش** | `governance.manage` | `PAGE-042.04` <sub>modules/request-center/Actions/governance-actions.php:23</sub> | 8 |
| تنظیمات و سیستم | **مدیریت تعطیلات** | `holidays.view` | `PAGE-033.65` <sub>index.php:1418</sub><br>`TEST-090.33` <sub>tests/test_food_worker_window_static.py:318</sub> | 6 |
| تنظیمات و سیستم | **ویرایش** | `holidays.manage` | `ACT-009.07` <sub>actions/holiday-actions.php:102</sub> | 13 |

</details>

### جدول‌های پایگاه داده

<details>
<summary>۸۴ جدول — باز کنید</summary>

| جدول | آدرس‌های اصلی | کل |
|---|---|---|
| `activity_logs` | `DB-004.28` <sub>install-empty-db.sql:397</sub><br>`DB-007.19` <sub>migrations/003-sync-schema-151.sql:298</sub><br>`DB-044.25` <sub>schema.sql:376</sub> | 70 |
| `api_rate_limits` | `DB-004.67` <sub>install-empty-db.sql:1140</sub><br>`DB-007.58` <sub>migrations/003-sync-schema-151.sql:949</sub><br>`DB-044.67` <sub>schema.sql:1153</sub> | 8 |
| `asset_dedupe_backup_016` | `DB-020.20` <sub>migrations/016-auto-asset-finish.sql:53</sub><br>`DB-020.22` <sub>migrations/016-auto-asset-finish.sql:57</sub><br>`DB-020.19` <sub>migrations/016-auto-asset-finish.sql:52</sub> | 4 |
| `asset_graphics_adapters` | `DB-004.40` <sub>install-empty-db.sql:616</sub><br>`DB-007.30` <sub>migrations/003-sync-schema-151.sql:475</sub><br>`DB-044.37` <sub>schema.sql:595</sub> | 16 |
| `asset_history_events` | `DB-004.43` <sub>install-empty-db.sql:658</sub><br>`DB-007.33` <sub>migrations/003-sync-schema-151.sql:514</sub><br>`DB-044.40` <sub>schema.sql:637</sub> | 18 |
| `asset_inventory_history` | `DB-004.42` <sub>install-empty-db.sql:647</sub><br>`DB-007.32` <sub>migrations/003-sync-schema-151.sql:504</sub><br>`DB-044.39` <sub>schema.sql:626</sub> | 17 |
| `asset_memory_modules` | `DB-004.38` <sub>install-empty-db.sql:585</sub><br>`DB-007.28` <sub>migrations/003-sync-schema-151.sql:446</sub><br>`DB-044.35` <sub>schema.sql:564</sub> | 23 |
| `asset_peripherals` | `DB-004.41` <sub>install-empty-db.sql:629</sub><br>`DB-007.31` <sub>migrations/003-sync-schema-151.sql:487</sub><br>`DB-044.38` <sub>schema.sql:608</sub> | 22 |
| `asset_profiles` | `DB-004.45` <sub>install-empty-db.sql:689</sub><br>`DB-007.35` <sub>migrations/003-sync-schema-151.sql:543</sub><br>`DB-044.42` <sub>schema.sql:668</sub> | 103 |
| `asset_relations` | `DB-004.44` <sub>install-empty-db.sql:676</sub><br>`DB-007.34` <sub>migrations/003-sync-schema-151.sql:531</sub><br>`DB-044.41` <sub>schema.sql:655</sub> | 14 |
| `asset_storage_devices` | `DB-004.39` <sub>install-empty-db.sql:600</sub><br>`DB-007.29` <sub>migrations/003-sync-schema-151.sql:460</sub><br>`DB-044.36` <sub>schema.sql:579</sub> | 23 |
| `assets` | `ACT-014.09` <sub>actions/settings-actions.php:319</sub><br>`ACT-003.08` <sub>actions/asset-actions.php:200</sub><br>`PAGE-033.70` <sub>index.php:1695</sub> | 205 |
| `categories` | `DB-004.21` <sub>install-empty-db.sql:244</sub><br>`DB-007.12` <sub>migrations/003-sync-schema-151.sql:153</sub><br>`DB-044.18` <sub>schema.sql:223</sub> | 39 |
| `cd_dvd_records` | `DB-004.16` <sub>install-empty-db.sql:144</sub><br>`DB-007.09` <sub>migrations/003-sync-schema-151.sql:87</sub><br>`DB-044.13` <sub>schema.sql:123</sub> | 40 |
| `cd_dvd_types` | `DB-004.15` <sub>install-empty-db.sql:136</sub><br>`DB-044.12` <sub>schema.sql:115</sub><br>`DB-079.02` <sub>upgrade-1.8-cd-dvd.sql:2</sub> | 8 |
| `change_approvals` | `DB-004.50` <sub>install-empty-db.sql:830</sub><br>`DB-007.39` <sub>migrations/003-sync-schema-151.sql:678</sub><br>`DB-044.47` <sub>schema.sql:809</sub> | 14 |
| `change_records` | `DB-004.49` <sub>install-empty-db.sql:801</sub><br>`DB-007.38` <sub>migrations/003-sync-schema-151.sql:650</sub><br>`DB-044.46` <sub>schema.sql:780</sub> | 30 |
| `change_ticket_links` | `DB-004.51` <sub>install-empty-db.sql:842</sub><br>`DB-007.40` <sub>migrations/003-sync-schema-151.sql:689</sub><br>`DB-044.48` <sub>schema.sql:821</sub> | 15 |
| `chatbot_faq` | `DB-004.107` <sub>install-empty-db.sql:1348</sub><br>`DB-007.54` <sub>migrations/003-sync-schema-151.sql:914</sub><br>`DB-044.63` <sub>schema.sql:1113</sub> | 13 |
| `chatbot_logs` | `DB-004.108` <sub>install-empty-db.sql:1360</sub><br>`DB-007.55` <sub>migrations/003-sync-schema-151.sql:925</sub><br>`DB-044.64` <sub>schema.sql:1125</sub> | 14 |
| `departments` | `DB-004.05` <sub>install-empty-db.sql:27</sub><br>`DB-007.03` <sub>migrations/003-sync-schema-151.sql:9</sub><br>`DB-044.02` <sub>schema.sql:6</sub> | 75 |
| `domain_scan_queue` | `DB-004.69` <sub>install-empty-db.sql:1162</sub><br>`DB-007.60` <sub>migrations/003-sync-schema-151.sql:968</sub><br>`DB-044.69` <sub>schema.sql:1175</sub> | 19 |
| `domain_scan_runs` | `DB-004.68` <sub>install-empty-db.sql:1149</sub><br>`DB-007.59` <sub>migrations/003-sync-schema-151.sql:956</sub><br>`DB-044.68` <sub>schema.sql:1162</sub> | 22 |
| `food_calendar` | `DB-004.100` <sub>install-empty-db.sql:1260</sub><br>`DB-007.65` <sub>migrations/003-sync-schema-151.sql:1029</sub><br>`DB-044.95` <sub>schema.sql:1271</sub> | 39 |
| `food_calendar_items` | `DB-004.101` <sub>install-empty-db.sql:1275</sub><br>`DB-007.66` <sub>migrations/003-sync-schema-151.sql:1042</sub><br>`DB-044.96` <sub>schema.sql:1286</sub> | 47 |
| `food_catalog` | `DB-004.99` <sub>install-empty-db.sql:1248</sub><br>`DB-007.63` <sub>migrations/003-sync-schema-151.sql:1000</sub><br>`DB-044.93` <sub>schema.sql:1239</sub> | 51 |
| `food_guest_requests` | `DB-004.110` <sub>install-empty-db.sql:1382</sub><br>`DB-007.64` <sub>migrations/003-sync-schema-151.sql:1011</sub><br>`DB-044.94` <sub>schema.sql:1251</sub> | 40 |
| `food_meal_reconciliations` | `DB-004.111` <sub>install-empty-db.sql:1406</sub><br>`DB-027.02` <sub>migrations/022-food-meal-reconciliation-invoices.sql:4</sub><br>`DB-044.100` <sub>schema.sql:1357</sub> | 8 |
| `food_monthly_invoices` | `DB-004.112` <sub>install-empty-db.sql:1438</sub><br>`DB-027.03` <sub>migrations/022-food-meal-reconciliation-invoices.sql:31</sub><br>`DB-044.101` <sub>schema.sql:1389</sub> | 7 |
| `food_order_cancel_requests` | `DB-004.104` <sub>install-empty-db.sql:1324</sub><br>`DB-044.99` <sub>schema.sql:1335</sub> | 10 |
| `food_order_logs` | `DB-004.103` <sub>install-empty-db.sql:1306</sub><br>`DB-007.68` <sub>migrations/003-sync-schema-151.sql:1070</sub><br>`DB-044.98` <sub>schema.sql:1317</sub> | 28 |
| `food_orders` | `DB-004.102` <sub>install-empty-db.sql:1290</sub><br>`DB-007.67` <sub>migrations/003-sync-schema-151.sql:1056</sub><br>`DB-044.97` <sub>schema.sql:1301</sub> | 82 |
| `food_ticket_card_map` | `DB-004.89` <sub>install-empty-db.sql:1197</sub><br>`DB-007.61` <sub>migrations/003-sync-schema-151.sql:980</sub><br>`DB-044.89` <sub>schema.sql:1210</sub> | 14 |
| `food_ticket_config` | `DB-004.54` <sub>install-empty-db.sql:888</sub><br>`DB-007.43` <sub>migrations/003-sync-schema-151.sql:732</sub><br>`DB-044.51` <sub>schema.sql:867</sub> | 53 |
| `food_ticket_daily_absence` | `DB-004.61` <sub>install-empty-db.sql:1046</sub><br>`DB-007.50` <sub>migrations/003-sync-schema-151.sql:863</sub><br>`DB-016.02` <sub>migrations/012-manual-attendance-clears-absences.sql:4</sub> | 40 |
| `food_ticket_day_visits` | `DB-004.62` <sub>install-empty-db.sql:1062</sub><br>`DB-044.59` <sub>schema.sql:1041</sub> | 16 |
| `food_ticket_events` | `DB-004.55` <sub>install-empty-db.sql:919</sub><br>`DB-007.44` <sub>migrations/003-sync-schema-151.sql:750</sub><br>`DB-044.52` <sub>schema.sql:898</sub> | 166 |
| `food_ticket_group_members` | `DB-004.59` <sub>install-empty-db.sql:1007</sub><br>`DB-007.48` <sub>migrations/003-sync-schema-151.sql:829</sub><br>`DB-044.56` <sub>schema.sql:986</sub> | 22 |
| `food_ticket_group_runs` | `DB-004.60` <sub>install-empty-db.sql:1019</sub><br>`DB-007.49` <sub>migrations/003-sync-schema-151.sql:839</sub><br>`DB-044.57` <sub>schema.sql:998</sub> | 41 |
| `food_ticket_group_uid_history` | `DB-004.63` <sub>install-empty-db.sql:1075</sub><br>`DB-007.51` <sub>migrations/003-sync-schema-151.sql:877</sub><br>`DB-044.60` <sub>schema.sql:1054</sub> | 16 |
| `food_ticket_groups` | `DB-004.58` <sub>install-empty-db.sql:988</sub><br>`DB-007.47` <sub>migrations/003-sync-schema-151.sql:813</sub><br>`DB-044.55` <sub>schema.sql:967</sub> | 37 |
| `food_ticket_guest_cards` | `DB-004.57` <sub>install-empty-db.sql:974</sub><br>`DB-007.46` <sub>migrations/003-sync-schema-151.sql:800</sub><br>`DB-044.54` <sub>schema.sql:953</sub> | 24 |
| `food_ticket_print_log` | `DB-004.64` <sub>install-empty-db.sql:1086</sub><br>`DB-007.52` <sub>migrations/003-sync-schema-151.sql:887</sub><br>`DB-044.61` <sub>schema.sql:1065</sub> | 24 |
| `food_ticket_templates` | `DB-004.90` <sub>install-empty-db.sql:1207</sub><br>`DB-007.62` <sub>migrations/003-sync-schema-151.sql:989</sub><br>`DB-044.90` <sub>schema.sql:1220</sub> | 21 |
| `food_ticket_worker_status` | `DB-004.56` <sub>install-empty-db.sql:958</sub><br>`DB-007.45` <sub>migrations/003-sync-schema-151.sql:787</sub><br>`DB-044.53` <sub>schema.sql:937</sub> | 21 |
| `handling_units` | `DB-004.13` <sub>install-empty-db.sql:123</sub><br>`DB-007.07` <sub>migrations/003-sync-schema-151.sql:72</sub><br>`DB-044.10` <sub>schema.sql:102</sub> | 49 |
| `holidays` | `ACT-009.07` <sub>actions/holiday-actions.php:102</sub><br>`PAGE-033.65` <sub>index.php:1418</sub><br>`DB-004.35` <sub>install-empty-db.sql:517</sub> | 46 |
| `knowledge_articles` | `DB-004.34` <sub>install-empty-db.sql:503</sub><br>`DB-007.25` <sub>migrations/003-sync-schema-151.sql:373</sub><br>`DB-044.31` <sub>schema.sql:482</sub> | 17 |
| `log_issue_events` | `DB-031.05` <sub>migrations/026-logging-legacy-result-unknown.sql:38</sub><br>`DB-004.119` <sub>install-empty-db.sql:1554</sub><br>`DB-044.108` <sub>schema.sql:1505</sub> | 15 |
| `log_issue_history` | `DB-004.118` <sub>install-empty-db.sql:1538</sub><br>`DB-031.04` <sub>migrations/026-logging-legacy-result-unknown.sql:20</sub><br>`DB-044.107` <sub>schema.sql:1489</sub> | 12 |
| `log_issue_notifications` | `DB-004.120` <sub>install-empty-db.sql:1561</sub><br>`DB-031.06` <sub>migrations/026-logging-legacy-result-unknown.sql:56</sub><br>`DB-044.109` <sub>schema.sql:1512</sub> | 14 |
| `log_issues` | `DB-004.116` <sub>install-empty-db.sql:1502</sub><br>`DB-030.02` <sub>migrations/025-logging-issues-alerts.sql:4</sub><br>`DB-044.105` <sub>schema.sql:1453</sub> | 33 |
| `logging_health_alerts` | `DB-004.117` <sub>install-empty-db.sql:1528</sub><br>`DB-030.03` <sub>migrations/025-logging-issues-alerts.sql:29</sub><br>`DB-044.106` <sub>schema.sql:1479</sub> | 14 |
| `login_attempts` | `DB-004.31` <sub>install-empty-db.sql:461</sub><br>`DB-007.22` <sub>migrations/003-sync-schema-151.sql:343</sub><br>`DB-044.28` <sub>schema.sql:440</sub> | 14 |
| `notifications` | `PAGE-033.55` <sub>index.php:988</sub><br>`ACT-010.03` <sub>actions/notification-actions.php:23</sub><br>`PAGE-033.54` <sub>index.php:980</sub> | 51 |
| `ola_policies` | `DB-004.46` <sub>install-empty-db.sql:771</sub><br>`DB-007.36` <sub>migrations/003-sync-schema-151.sql:624</sub><br>`DB-044.43` <sub>schema.sql:750</sub> | 28 |
| `org_companies` | `DB-004.06` <sub>install-empty-db.sql:37</sub><br>`DB-023.02` <sub>migrations/018-org-company-separation.sql:3</sub><br>`DB-044.03` <sub>schema.sql:16</sub> | 13 |
| `org_unit_managers` | `DB-004.10` <sub>install-empty-db.sql:77</sub><br>`DB-007.05` <sub>migrations/003-sync-schema-151.sql:32</sub><br>`DB-044.07` <sub>schema.sql:56</sub> | 38 |
| `org_units` | `DB-004.07` <sub>install-empty-db.sql:51</sub><br>`DB-007.04` <sub>migrations/003-sync-schema-151.sql:18</sub><br>`DB-044.04` <sub>schema.sql:30</sub> | 93 |
| `problem_records` | `DB-004.52` <sub>install-empty-db.sql:853</sub><br>`DB-007.41` <sub>migrations/003-sync-schema-151.sql:699</sub><br>`DB-044.49` <sub>schema.sql:832</sub> | 25 |
| `problem_ticket_links` | `DB-004.53` <sub>install-empty-db.sql:877</sub><br>`DB-007.42` <sub>migrations/003-sync-schema-151.sql:722</sub><br>`DB-044.50` <sub>schema.sql:856</sub> | 14 |
| `request_center_appointments` | `DB-004.122` <sub>install-empty-db.sql:1590</sub><br>`DB-040.02` <sub>migrations/035-request-center-appointments.sql:14</sub><br>`DB-044.111` <sub>schema.sql:1542</sub> | 12 |
| `role_permissions` | `DB-004.29` <sub>install-empty-db.sql:436</sub><br>`DB-007.20` <sub>migrations/003-sync-schema-151.sql:323</sub><br>`DB-044.26` <sub>schema.sql:415</sub> | 21 |
| `schema_updates` | `DB-004.121` <sub>install-empty-db.sql:1580</sub><br>`DB-044.110` <sub>schema.sql:1531</sub><br>`CORE-023.22` <sub>lib/system-alarms.php:603</sub> | 35 |
| `service_catalog` | `DB-004.22` <sub>install-empty-db.sql:253</sub><br>`DB-007.13` <sub>migrations/003-sync-schema-151.sql:161</sub><br>`DB-044.19` <sub>schema.sql:232</sub> | 64 |
| `service_catalog_fields` | `DB-004.23` <sub>install-empty-db.sql:279</sub><br>`DB-007.14` <sub>migrations/003-sync-schema-151.sql:186</sub><br>`DB-044.20` <sub>schema.sql:258</sub> | 18 |
| `settings` | `PAGE-033.73` <sub>index.php:1957</sub><br>`PAGE-033.40` <sub>index.php:882</sub><br>`DB-004.04` <sub>install-empty-db.sql:22</sub> | 73 |
| `system_incidents` | `DB-004.109` <sub>install-empty-db.sql:1373</sub><br>`DB-007.56` <sub>migrations/003-sync-schema-151.sql:937</sub><br>`DB-044.65` <sub>schema.sql:1138</sub> | 9 |
| `system_logs` | `DB-004.65` <sub>install-empty-db.sql:1102</sub><br>`DB-007.53` <sub>migrations/003-sync-schema-151.sql:902</sub><br>`DB-044.62` <sub>schema.sql:1081</sub> | 67 |
| `ticket_attachments` | `DB-004.27` <sub>install-empty-db.sql:386</sub><br>`DB-007.18` <sub>migrations/003-sync-schema-151.sql:288</sub><br>`DB-044.24` <sub>schema.sql:365</sub> | 19 |
| `ticket_events` | `DB-004.33` <sub>install-empty-db.sql:479</sub><br>`DB-007.24` <sub>migrations/003-sync-schema-151.sql:359</sub><br>`DB-044.30` <sub>schema.sql:458</sub> | 50 |
| `ticket_messages` | `DB-004.25` <sub>install-empty-db.sql:360</sub><br>`DB-007.16` <sub>migrations/003-sync-schema-151.sql:264</sub><br>`DB-044.22` <sub>schema.sql:339</sub> | 28 |
| `ticket_number_seq` | `DB-004.66` <sub>install-empty-db.sql:1134</sub><br>`DB-007.57` <sub>migrations/003-sync-schema-151.sql:945</sub><br>`DB-040.03` <sub>migrations/035-request-center-appointments.sql:36</sub> | 26 |
| `ticket_ola_alerts` | `DB-004.47` <sub>install-empty-db.sql:789</sub><br>`DB-007.37` <sub>migrations/003-sync-schema-151.sql:641</sub><br>`DB-044.44` <sub>schema.sql:768</sub> | 14 |
| `ticket_ratings` | `DB-004.26` <sub>install-empty-db.sql:372</sub><br>`DB-007.17` <sub>migrations/003-sync-schema-151.sql:275</sub><br>`DB-044.23` <sub>schema.sql:351</sub> | 22 |
| `ticket_sla_alerts` | `DB-004.32` <sub>install-empty-db.sql:469</sub><br>`DB-007.23` <sub>migrations/003-sync-schema-151.sql:350</sub><br>`DB-044.29` <sub>schema.sql:448</sub> | 15 |
| `tickets` | `DB-004.24` <sub>install-empty-db.sql:292</sub><br>`DB-007.15` <sub>migrations/003-sync-schema-151.sql:198</sub><br>`DB-044.21` <sub>schema.sql:271</sub> | 216 |
| `tmp_asset_dedupe_map` | `DB-020.17` <sub>migrations/016-auto-asset-finish.sql:39</sub><br>`DB-020.18` <sub>migrations/016-auto-asset-finish.sql:45</sub><br>`DB-020.16` <sub>migrations/016-auto-asset-finish.sql:38</sub> | 8 |
| `traffic_destinations` | `DB-004.17` <sub>install-empty-db.sql:177</sub><br>`DB-007.10` <sub>migrations/003-sync-schema-151.sql:119</sub><br>`DB-044.14` <sub>schema.sql:156</sub> | 31 |
| `traffic_visit_history` | `DB-004.19` <sub>install-empty-db.sql:217</sub><br>`DB-011.02` <sub>migrations/007-traffic-visit-history.sql:2</sub><br>`DB-044.16` <sub>schema.sql:196</sub> | 10 |
| `traffic_visits` | `DB-004.18` <sub>install-empty-db.sql:188</sub><br>`DB-007.11` <sub>migrations/003-sync-schema-151.sql:127</sub><br>`DB-044.15` <sub>schema.sql:167</sub> | 59 |
| `user_access_group_members` | `DB-004.114` <sub>install-empty-db.sql:1474</sub><br>`DB-028.03` <sub>migrations/023-user-access-groups.sql:17</sub><br>`DB-044.103` <sub>schema.sql:1425</sub> | 12 |
| `user_access_group_permissions` | `DB-004.115` <sub>install-empty-db.sql:1486</sub><br>`DB-028.04` <sub>migrations/023-user-access-groups.sql:29</sub><br>`DB-044.104` <sub>schema.sql:1437</sub> | 12 |
| `user_access_groups` | `DB-004.113` <sub>install-empty-db.sql:1460</sub><br>`DB-028.02` <sub>migrations/023-user-access-groups.sql:3</sub><br>`DB-044.102` <sub>schema.sql:1411</sub> | 10 |
| `users` | `ACT-011.14` <sub>actions/org-actions.php:409</sub><br>`DB-004.11` <sub>install-empty-db.sql:85</sub><br>`DB-007.06` <sub>migrations/003-sync-schema-151.sql:39</sub> | 322 |

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
| `TEST` | TEST | 253 | 1,967 | 28,924 |
| `TOOL` | TOOL | 152 | 1,452 | 40,114 |
| `DB` | DB | 86 | 1,385 | 8,274 |
| `PAGE` | صفحات (ریشه) | 84 | 1,609 | 57,771 |
| `ASSET` | دارایی‌های ایستا (assets/) | 33 | 843 | 15,021 |
| `WEB` | پنل وب غذا (food-ticket-web/) | 29 | 497 | 10,057 |
| `CORE` | موتور مشترک (lib/) | 24 | 391 | 9,760 |
| `ACT` | کنش‌ها (actions/) | 17 | 99 | 3,130 |
| `CRON` | زمان‌بندها (cron/) | 15 | 39 | 1,675 |
| `API` | رابط برنامه (api/) | 2 | 9 | 295 |

## نشانه‌های ریسک

امتیاز ریسک فقط **ترتیب بازبینی** را تعیین می‌کند؛ قضاوت نهایی نیست. یک بلوک پرامتیاز می‌تواند کاملاً سالم باشد و یک بلوک کم‌امتیاز باگ حیاتی داشته باشد.

| نشانه | معنی | بلوک |
|---|---|---|
| `write_no_tx` | نوشتن بدون تراکنش | 952 |
| `error_suppression` | سرکوب @ | 388 |
| `dangerous_call` | فراخوانی خطرناک | 280 |
| `sql_concat` | الحاق در SQL | 236 |
| `raw_input` | ورودی خام | 221 |
| `long` | >۱۲۰ خط | 175 |
| `silent_catch` | catch بی‌صدا | 163 |
| `unescaped_echo` | چاپ بدون فرار | 39 |
| `very_long` | >۳۰۰ خط | 29 |

## ترتیب بازبینی — ۴۰ آدرس پرخطر

| # | آدرس | مسیر:خط | لنگر | ریسک | نشانه‌ها | وضعیت |
|---|---|---|---|---|---|---|
| 1 | `TEST-199.36` | `tests/test_request_center_t12_scenarios.php:799` | مسیر: rated | 20 | catch بی‌صدا، الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 2 | `TOOL-043.01` | `tools/diag_connection.php:1` | بلوک رویه‌ای 1-258 | 20 | catch بی‌صدا، الحاق در SQL، چاپ بدون فرار، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 3 | `PAGE-025.02` | `food-ticket-selftest.php:20` | food_ticket_selftest_run | 19 | catch بی‌صدا، الحاق در SQL، سرکوب @، فراخوانی خطرناک، >۳۰۰ خط | ⬜ در نوبت |
| 4 | `TOOL-002.01` | `tools/asset_dedupe.php:1` | بلوک رویه‌ای 1-301 | 19 | catch بی‌صدا، الحاق در SQL، چاپ بدون فرار، فراخوانی خطرناک، >۳۰۰ خط | ⬜ در نوبت |
| 5 | `TEST-220.13` | `tests/test_system_update_migration_failure_gates.php:147` | بلوک رویه‌ای 147-232 | 17 | catch بی‌صدا، الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 6 | `PAGE-033.73` | `index.php:1957` | مسیر: settings | 16 | catch بی‌صدا، الحاق در SQL، ورودی خام، سرکوب @، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 7 | `TEST-192.08` | `tests/test_request_center_phase2_smoke_mariadb.php:158` | بلوک رویه‌ای 158-949 | 16 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 8 | `TOOL-109.01` | `tools/test_food_group_representative.php:1` | بلوک رویه‌ای 1-517 | 16 | catch بی‌صدا، الحاق در SQL، چاپ بدون فرار، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 9 | `ACT-014.09` | `actions/settings-actions.php:319` | مسیر: reset_assets | 15 | catch بی‌صدا، الحاق در SQL، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 10 | `TOOL-055.01` | `tools/explain_alarms.php:1` | بلوک رویه‌ای 1-219 | 15 | catch بی‌صدا، چاپ بدون فرار، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 11 | `PAGE-024.29` | `food-ticket-runtime.php:1211` | food_ticket_events_ensure_source_delet | 14 | catch بی‌صدا، الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 12 | `PAGE-029.91` | `food-ticket.php:2577` | food_ticket_cleanup_source_rows | 14 | catch بی‌صدا، الحاق در SQL، سرکوب @، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 13 | `PAGE-029.136` | `food-ticket.php:4969` | food_ticket_api_upload_access_file | 14 | الحاق در SQL، ورودی خام، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 14 | `PAGE-029.137` | `food-ticket.php:5039` | food_ticket_api_handle | 14 | الحاق در SQL، ورودی خام، سرکوب @، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 15 | `PAGE-054.03` | `modules/request-center/Pages/detail.php:17` | مسیر: ticket | 14 | الحاق در SQL، ورودی خام، سرکوب @، نوشتن بدون تراکنش، >۳۰۰ خط | ⬜ در نوبت |
| 16 | `TEST-187.03` | `tests/test_request_center_claim_concurrency_mariadb.php:38` | بلوک رویه‌ای 38-198 | 14 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 17 | `TOOL-104.10` | `tools/test_employees_import_offline.php:121` | بلوک رویه‌ای 121-276 | 14 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 18 | `PAGE-024.22` | `food-ticket-runtime.php:833` | food_ticket_send_windows_graphic | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 19 | `PAGE-029.41` | `food-ticket.php:821` | food_ticket_access_rows_powershell | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 20 | `PAGE-029.43` | `food-ticket.php:1039` | food_ticket_access_delete_powershell_e | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 21 | `PAGE-029.92` | `food-ticket.php:2781` | food_ticket_retry_failed | 13 | catch بی‌صدا، الحاق در SQL، سرکوب @، نوشتن بدون تراکنش | ⬜ در نوبت |
| 22 | `TEST-120.08` | `tests/test_logging_mysql_integration.php:229` | Verify migration 025 changes the live  | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 23 | `TOOL-003.07` | `tools/asset_reset.php:169` | ۶) حذف | 13 | catch بی‌صدا، الحاق در SQL، فراخوانی خطرناک | ⬜ در نوبت |
| 24 | `TOOL-044.10` | `tools/diag_extract.php:265` | اگر مسیر جایگزینی پیدا شد ولی در tools | 13 | الحاق در SQL، سرکوب @، فراخوانی خطرناک، >۱۲۰ خط | ⬜ در نوبت |
| 25 | `PAGE-009.27` | `chatbot.php:694` | chatbot_api_handle | 12 | ورودی خام، چاپ بدون فرار، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 26 | `CRON-009.01` | `cron/rebuild_logging_issues.php:1` | بلوک رویه‌ای 1-53 | 12 | الحاق در SQL، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 27 | `TEST-120.06` | `tests/test_logging_mysql_integration.php:136` | Migration 025 must remain safe when th | 12 | الحاق در SQL، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 28 | `TEST-198.11` | `tests/test_request_center_t12_preflight.php:210` | بلوک رویه‌ای 210-322 | 12 | catch بی‌صدا، سرکوب @، فراخوانی خطرناک، نوشتن بدون تراکنش | ⬜ در نوبت |
| 29 | `TOOL-044.11` | `tools/diag_extract.php:406` | آیا Apache با LocalSystem بالا آمده؟ ر | 12 | الحاق در SQL، سرکوب @، فراخوانی خطرناک | ⬜ در نوبت |
| 30 | `TOOL-050.01` | `tools/diag_source_row_delete.php:1` | بلوک رویه‌ای 1-128 | 12 | الحاق در SQL، ورودی خام، سرکوب @، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 31 | `PAGE-024.18` | `food-ticket-runtime.php:552` | food_ticket_process_print_queue | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 32 | `WEB-003.01` | `food-ticket-web/assets/food-capitalio-dashboard-4179.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 33 | `WEB-005.01` | `food-ticket-web/assets/food-capitalio-dashboard-r37.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 34 | `WEB-005.04` | `food-ticket-web/assets/food-capitalio-dashboard-r37.js:187` | assert on the output | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 35 | `WEB-007.01` | `food-ticket-web/assets/food-capitalio-dashboard-r38.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 36 | `WEB-007.04` | `food-ticket-web/assets/food-capitalio-dashboard-r38.js:187` | assert on the output | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 37 | `WEB-009.01` | `food-ticket-web/assets/food-capitalio-dashboard-r39.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 38 | `WEB-009.04` | `food-ticket-web/assets/food-capitalio-dashboard-r39.js:187` | assert on the output | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 39 | `WEB-012.01` | `food-ticket-web/assets/food-capitalio-dashboard-r41.js:1` | مقدمهٔ فایل | 11 | catch بی‌صدا، الحاق در SQL، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |
| 40 | `WEB-012.04` | `food-ticket-web/assets/food-capitalio-dashboard-r41.js:187` | assert on the output | 11 | الحاق در SQL، فراخوانی خطرناک، نوشتن بدون تراکنش، >۱۲۰ خط | ⬜ در نوبت |

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
<summary><b>TEST-002</b> — <code>tests/SecurityTest.php</code> (252 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-002.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-002.02` | 4–247 | کلاس | `SecurityTest` | 3 | ⬜ |
| `TEST-002.03` | 249–252 | ناحیه | `Run tests` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-003</b> — <code>tests/download_page_helpers.py</code> (118 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-003.01` | 1–29 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-003.02` | 30–34 | بلوک | `version_tuple` | 0 | ⬜ |
| `TEST-003.03` | 35–61 | بلوک | `update_packages` | 0 | ⬜ |
| `TEST-003.04` | 62–79 | بلوک | `published_package_names` | 0 | ⬜ |
| `TEST-003.05` | 80–88 | بلوک | `published_packages` | 0 | ⬜ |
| `TEST-003.06` | 89–95 | بلوک | `latest_package` | 0 | ⬜ |
| `TEST-003.07` | 96–99 | بلوک | `latest_package_name` | 0 | ⬜ |
| `TEST-003.08` | 100–114 | بلوک | `check_download_page_lists_only_latest_package` | 0 | ⬜ |
| `TEST-003.09` | 115–118 | بلوک | `read` | 0 | ⬜ |

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
<summary><b>TEST-008</b> — <code>tests/fixtures/request-center-appointment-worker.php</code> (43 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-008.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `TEST-008.02` | 18–43 | ناحیه | `پیوستن به «سد هم‌زمانی»: آخر آماده‌شدنِ همه را سیگ` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-009</b> — <code>tests/fixtures/request-center-claim-worker.php</code> (68 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-009.01` | 1–68 | ناحیه | `بلوک رویه‌ای 1-68` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-010</b> — <code>tests/fixtures/request-center-ddl-probe.php</code> (12 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-010.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-010.02` | 6–6 | ناحیه | `CI-only diagnostic endpoint. tests/.htaccess denie` | 0 | ⬜ |
| `TEST-010.03` | 7–12 | ناحیه | `The PHP built-in server used by the disposable CI ` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-011</b> — <code>tests/fixtures/request-center-session-seed.php</code> (21 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-011.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-012</b> — <code>tests/fixtures/request-center-t12-session-seed.php</code> (30 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-012.01` | 1–30 | ناحیه | `بلوک رویه‌ای 1-30` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-013</b> — <code>tests/js/food-aggregate-report.test.js</code> (190 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-013.01` | 1–72 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-013.02` | 73–190 | بلوک | `fire` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-014</b> — <code>tests/js/food-bulk-past-window.test.js</code> (33 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-014.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-014.02` | 10–33 | بلوک | `canSelect` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-015</b> — <code>tests/js/food-calendar-order-count.test.js</code> (95 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-015.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-015.02` | 8–26 | بلوک | `R` | 0 | ⬜ |
| `TEST-015.03` | 27–46 | بلوک | `canViewCounts` | 0 | ⬜ |
| `TEST-015.04` | 47–47 | بلوک | `esc` | 0 | ⬜ |
| `TEST-015.05` | 48–95 | بلوک | `faDigits` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-016</b> — <code>tests/js/food-dashboard-count.test.js</code> (27 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-016.01` | 1–27 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-017</b> — <code>tests/js/food-menu-by-menu.test.js</code> (44 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-017.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-017.02` | 14–14 | بلوک | `esc` | 0 | ⬜ |
| `TEST-017.03` | 15–44 | بلوک | `faDigits` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-018</b> — <code>tests/js/food-order-name-sort.test.js</code> (33 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-018.01` | 1–33 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-019</b> — <code>tests/js/food-report-fixes.test.js</code> (32 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-019.01` | 1–32 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-020</b> — <code>tests/js/food-report-relocation.test.js</code> (36 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-020.01` | 1–36 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-021</b> — <code>tests/js/list-sort-export.test.js</code> (63 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-021.01` | 1–30 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `TEST-021.02` | 31–31 | بلوک | `click` | 0 | ⬜ |
| `TEST-021.03` | 32–63 | بلوک | `sortBtn` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-022</b> — <code>tests/js/list-sort-rowclasses.test.js</code> (39 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-022.01` | 1–39 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-023</b> — <code>tests/js/list-sort.test.js</code> (111 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-023.01` | 1–32 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `TEST-023.02` | 33–35 | بلوک | `rowTexts` | 0 | ⬜ |
| `TEST-023.03` | 36–41 | بلوک | `clickHeader` | 0 | ⬜ |
| `TEST-023.04` | 42–111 | بلوک | `headerBtns` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-024</b> — <code>tests/request_center_action_helpers.py</code> (23 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-024.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-024.02` | 14–23 | بلوک | `action_source` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-025</b> — <code>tests/smoke.php</code> (271 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-025.01` | 1–271 | ناحیه | `بلوک رویه‌ای 1-271` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-026</b> — <code>tests/test_admin_layout_design_static.py</code> (100 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-026.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-026.02` | 7–7 | بلوک | `AdminLayoutDesignStaticTests` | 0 | ⬜ |
| `TEST-026.03` | 8–18 | بلوک | `test_layout_editor_is_admin_only_and_saves_validat` | 0 | ⬜ |
| `TEST-026.04` | 19–31 | بلوک | `test_design_view_is_available_in_shared_pages_and_` | 0 | ⬜ |
| `TEST-026.05` | 32–78 | بلوک | `test_elements_support_pixel_drag_keyboard_offsets_` | 0 | ⬜ |
| `TEST-026.06` | 79–91 | بلوک | `test_pixel_offsets_are_not_capped_at_five_hundred` | 0 | ⬜ |
| `TEST-026.07` | 92–100 | بلوک | `test_order_table_has_ninth_action_track_and_keeps_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-027</b> — <code>tests/test_asset_identity_ui_static.py</code> (44 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-027.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-027.02` | 6–44 | بلوک | `read` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-028</b> — <code>tests/test_asset_inventory_flow_sqlite.php</code> (418 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-028.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `TEST-028.02` | 29–29 | تابع | `db` | 0 | ⬜ |
| `TEST-028.03` | 30–30 | تابع | `setting` | 0 | ⬜ |
| `TEST-028.04` | 31–31 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-028.05` | 32–32 | تابع | `system_log` | 0 | ⬜ |
| `TEST-028.06` | 33–43 | تابع | `asset_profile_auto_values` | 0 | ⬜ |
| `TEST-028.07` | 44–48 | تابع | `asset_profile_apply_auto` | 0 | ⬜ |
| `TEST-028.08` | 49–54 | ناحیه | `بلوک رویه‌ای 49-54` | 0 | ⬜ |
| `TEST-028.09` | 55–65 | تابع | `check` | 0 | ⬜ |
| `TEST-028.10` | 66–75 | تابع | `source_insert_sql` | 1 | ⬜ |
| `TEST-028.11` | 76–182 | تابع | `make_asset_inventory` | 0 | ⬜ |
| `TEST-028.12` | 184–227 | تابع | `create_sqlite_schema` | 4 | ⬜ |
| `TEST-028.13` | 228–230 | ناحیه | `بلوک رویه‌ای 228-230` | 0 | ⬜ |
| `TEST-028.14` | 231–259 | ناحیه | `1) Real domain-stub -> production PowerShell mappe` | 1 | ⬜ |
| `TEST-028.15` | 260–283 | ناحیه | `2) Replace RAM + disk and change network address; ` | 0 | ⬜ |
| `TEST-028.16` | 284–295 | ناحیه | `Change one piece only: disk details must be carrie` | 0 | ⬜ |
| `TEST-028.17` | 296–303 | ناحیه | `Failed RAM query must preserve its last-known chil` | 0 | ⬜ |
| `TEST-028.18` | 304–313 | ناحیه | `3) Machine-identity lookup: serial first, then MAC` | 0 | ⬜ |
| `TEST-028.19` | 314–334 | ناحیه | `4) Exercise the exact production SQL and bind list` | 1 | ⬜ |
| `TEST-028.20` | 335–380 | ناحیه | `5) Exercise inventory_upsert_server_asset() itself` | 1 | ⬜ |
| `TEST-028.21` | 381–408 | ناحیه | `6) Actual local network probes, not a browser prev` | 1 | ⬜ |
| `TEST-028.22` | 409–418 | ناحیه | `Explicit environment boundary: never report the Wi` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-029</b> — <code>tests/test_asset_inventory_form_compat_static.py</code> (71 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-029.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-029.02` | 16–29 | بلوک | `test_renderer_normalizes_legacy_and_current_argume` | 0 | ⬜ |
| `TEST-029.03` | 30–39 | بلوک | `test_current_index_call_uses_the_11_argument_layou` | 0 | ⬜ |
| `TEST-029.04` | 40–48 | بلوک | `test_16224_archive_has_the_legacy_argument_that_ca` | 1 | ⬜ |
| `TEST-029.05` | 49–71 | بلوک | `test_form_hotfix_is_a_valid_incremental_upgrade_fr` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-030</b> — <code>tests/test_asset_profile_mapping_static.py</code> (67 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-030.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-030.02` | 7–67 | بلوک | `read` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-031</b> — <code>tests/test_asset_scope_permissions.php</code> (75 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-031.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `TEST-031.02` | 7–10 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `TEST-031.03` | 11–14 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `TEST-031.04` | 15–18 | تابع | `user_can` | 0 | ⬜ |
| `TEST-031.05` | 19–75 | ناحیه | `بلوک رویه‌ای 19-75` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-032</b> — <code>tests/test_backup_restore_archive.php</code> (120 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-032.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `TEST-032.02` | 17–21 | تابع | `restore_test_assert` | 0 | ⬜ |
| `TEST-032.03` | 23–40 | تابع | `restore_test_zip` | 0 | ⬜ |
| `TEST-032.04` | 42–49 | تابع | `restore_test_call` | 0 | ⬜ |
| `TEST-032.05` | 51–60 | تابع | `restore_test_reject` | 0 | ⬜ |
| `TEST-032.06` | 61–120 | ناحیه | `بلوک رویه‌ای 61-120` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-033</b> — <code>tests/test_backup_restore_mariadb_e2e.php</code> (159 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-033.01` | 1–34 | ناحیه | `بلوک رویه‌ای 1-34` | 0 | ⬜ |
| `TEST-033.02` | 35–38 | تابع | `cfg` | 0 | ⬜ |
| `TEST-033.03` | 40–46 | تابع | `t08_check` | 0 | ⬜ |
| `TEST-033.04` | 48–61 | تابع | `t08_remove_tree` | 7 | ⬜ |
| `TEST-033.05` | 62–159 | ناحیه | `بلوک رویه‌ای 62-159` | **10** | ⬜ |

</details>

<details>
<summary><b>TEST-034</b> — <code>tests/test_backup_restore_static.py</code> (36 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-034.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-034.02` | 6–16 | بلوک | `test_destructive_restore_requires_primary_admin_an` | 0 | ⬜ |
| `TEST-034.03` | 17–29 | بلوک | `test_upload_restore_preflights_paths_sizes_collisi` | 0 | ⬜ |
| `TEST-034.04` | 30–36 | بلوک | `test_backup_download_path_is_confined_to_backup_ro` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-035</b> — <code>tests/test_cache_pin_consistency_static.py</code> (118 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-035.01` | 1–49 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-035.02` | 50–67 | بلوک | `live_versions` | 0 | ⬜ |
| `TEST-035.03` | 68–118 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-036</b> — <code>tests/test_cd_dashboard_report_browser.js</code> (24 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-036.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-037</b> — <code>tests/test_cd_dvd_exit_sheet_required_static.py</code> (63 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-037.01` | 1–17 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-037.02` | 18–19 | بلوک | `ExitSheetRequiredTest` | 0 | ⬜ |
| `TEST-037.03` | 20–23 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-037.04` | 24–29 | بلوک | `_live_form_exit_sheet_input` | 0 | ⬜ |
| `TEST-037.05` | 30–35 | بلوک | `test_live_form_exit_sheet_is_required` | 0 | ⬜ |
| `TEST-037.06` | 36–39 | بلوک | `test_live_form_exit_sheet_disabled_for_in_directio` | 0 | ⬜ |
| `TEST-037.07` | 40–44 | بلوک | `test_server_rejects_empty_exit_sheet_on_out` | 0 | ⬜ |
| `TEST-037.08` | 45–51 | بلوک | `test_js_disables_and_toggles_required_with_directi` | 0 | ⬜ |
| `TEST-037.09` | 52–56 | بلوک | `test_js_submit_handler_focuses_empty_required_fiel` | 0 | ⬜ |
| `TEST-037.10` | 57–63 | بلوک | `test_live_renderer_uses_fresh_cache_buster` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-038</b> — <code>tests/test_cd_dvd_incoming_and_permissions_static.py</code> (46 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-038.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-038.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-038.03` | 11–11 | بلوک | `CdDvdIncomingAndPermissionTests` | 0 | ⬜ |
| `TEST-038.04` | 12–22 | بلوک | `test_outgoing_choice_and_default_follow_role_permi` | 0 | ⬜ |
| `TEST-038.05` | 23–36 | بلوک | `test_incoming_form_has_internal_external_selection` | 0 | ⬜ |
| `TEST-038.06` | 37–46 | بلوک | `test_external_person_is_required_and_internal_iden` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-039</b> — <code>tests/test_cd_traffic_browser.js</code> (37 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-039.01` | 1–37 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-040</b> — <code>tests/test_change_history_through_16259.py</code> (84 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-040.01` | 1–11 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-040.02` | 12–15 | بلوک | `git` | 0 | ⬜ |
| `TEST-040.03` | 16–33 | بلوک | `expected_paths` | 0 | ⬜ |
| `TEST-040.04` | 34–35 | بلوک | `HistoricalIndexTest` | 0 | ⬜ |
| `TEST-040.05` | 36–38 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-040.06` | 39–44 | بلوک | `test_version_boundary_and_future_session_pointer` | 0 | ⬜ |
| `TEST-040.07` | 45–55 | بلوک | `test_complete_tree_inventory_without_opening_zip` | 0 | ⬜ |
| `TEST-040.08` | 56–73 | بلوک | `test_all_reachable_commits_if_full_history_availab` | 0 | ⬜ |
| `TEST-040.09` | 74–84 | بلوک | `test_pull_requests_and_comment_scope` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-041</b> — <code>tests/test_chatbot_relevance.py</code> (124 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-041.01` | 1–23 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-041.02` | 24–30 | بلوک | `normalize` | 0 | ⬜ |
| `TEST-041.03` | 31–39 | بلوک | `tokens` | 0 | ⬜ |
| `TEST-041.04` | 40–43 | بلوک | `contains_word` | 0 | ⬜ |
| `TEST-041.05` | 44–79 | بلوک | `guide_index` | 0 | ⬜ |
| `TEST-041.06` | 80–100 | بلوک | `search` | 0 | ⬜ |
| `TEST-041.07` | 101–124 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-042</b> — <code>tests/test_chatbot_tts_static.py</code> (145 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-042.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-042.02` | 9–145 | بلوک | `check` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-043</b> — <code>tests/test_chatbot_widget_static.py</code> (77 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-043.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-043.02` | 9–77 | بلوک | `check` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-044</b> — <code>tests/test_delivery_link_contract_static.py</code> (68 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-044.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-044.02` | 25–28 | بلوک | `read` | 0 | ⬜ |
| `TEST-044.03` | 29–36 | بلوک | `section` | 0 | ⬜ |
| `TEST-044.04` | 37–48 | بلوک | `test_contract_document_is_complete` | 0 | ⬜ |
| `TEST-044.05` | 49–53 | بلوک | `test_all_agent_entrypoints_reference_the_contract` | 0 | ⬜ |
| `TEST-044.06` | 54–61 | بلوک | `test_boot_prints_the_rule_as_a_standing_user_rule` | 0 | ⬜ |
| `TEST-044.07` | 62–68 | بلوک | `test_existing_publishing_policy_stays_in_force` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-045</b> — <code>tests/test_duty_split_and_work_hours_static.py</code> (247 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-045.01` | 1–52 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-045.02` | 53–57 | بلوک | `read` | 0 | ⬜ |
| `TEST-045.03` | 58–65 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-045.04` | 66–75 | بلوک | `php_function` | 0 | ⬜ |
| `TEST-045.05` | 76–93 | بلوک | `test_requester_guard_runs_before_primary_admin_sho` | 0 | ⬜ |
| `TEST-045.06` | 94–104 | بلوک | `test_eligibility_gate_compares_candidate_against_r` | 0 | ⬜ |
| `TEST-045.07` | 105–115 | بلوک | `test_self_assignment_has_configurable_escape_hatch` | 0 | ⬜ |
| `TEST-045.08` | 116–130 | بلوک | `test_escape_hatch_defaults_to_closed` | 0 | ⬜ |
| `TEST-045.09` | 131–150 | بلوک | `test_escape_hatch_tolerates_missing_config_layer` | 0 | ⬜ |
| `TEST-045.10` | 151–159 | بلوک | `test_business_hours_have_single_source` | 0 | ⬜ |
| `TEST-045.11` | 160–171 | بلوک | `test_no_hardcoded_clock_hours_remain` | 0 | ⬜ |
| `TEST-045.12` | 172–183 | بلوک | `test_no_hardcoded_weekend_days_remain` | 0 | ⬜ |
| `TEST-045.13` | 184–194 | بلوک | `test_work_hours_guard_against_inverted_range` | 0 | ⬜ |
| `TEST-045.14` | 195–205 | بلوک | `test_work_hours_guard_against_full_week_closure` | 0 | ⬜ |
| `TEST-045.15` | 206–226 | بلوک | `test_work_hours_are_editable_from_calendar_page` | 0 | ⬜ |
| `TEST-045.16` | 227–239 | بلوک | `test_work_hours_action_is_permission_guarded` | 0 | ⬜ |
| `TEST-045.17` | 240–247 | بلوک | `test_calendar_page_text_is_not_stale` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-046</b> — <code>tests/test_fa_digits_output.py</code> (121 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-046.01` | 1–22 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-046.02` | 23–34 | بلوک | `convert` | 0 | ⬜ |
| `TEST-046.03` | 35–45 | بلوک | `test_helper_exists_and_registered` | 0 | ⬜ |
| `TEST-046.04` | 46–52 | بلوک | `test_text_nodes_convert_to_persian_digits` | 0 | ⬜ |
| `TEST-046.05` | 53–60 | بلوک | `test_attributes_and_form_values_stay_latin` | 0 | ⬜ |
| `TEST-046.06` | 61–78 | بلوک | `test_script_style_pre_code_textarea_and_comment_st` | 0 | ⬜ |
| `TEST-046.07` | 79–85 | بلوک | `test_machine_tokens_stay_latin_inside_text` | 0 | ⬜ |
| `TEST-046.08` | 86–93 | بلوک | `test_mixed_tag_heavy_page_keeps_layout_valid` | 0 | ⬜ |
| `TEST-046.09` | 94–98 | بلوک | `test_ticket_codes_convert_as_farsi_display` | 0 | ⬜ |
| `TEST-046.10` | 99–121 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-047</b> — <code>tests/test_food_aggregate_report_static.py</code> (149 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-047.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-047.02` | 25–28 | بلوک | `main` | 0 | ⬜ |
| `TEST-047.03` | 29–149 | بلوک | `check` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-048</b> — <code>tests/test_food_auto_receipts_scope_static.py</code> (71 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-048.01` | 1–19 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-048.02` | 20–24 | بلوک | `test_staff_lunch_requires_closed_canteen_day` | 0 | ⬜ |
| `TEST-048.03` | 25–29 | بلوک | `test_guest_lunch_requires_closed_canteen_day` | 0 | ⬜ |
| `TEST-048.04` | 30–38 | بلوک | `test_dinner_is_not_gated_by_holiday` | 0 | ⬜ |
| `TEST-048.05` | 39–48 | بلوک | `test_auto_receipts_only_run_in_end_of_day_pass` | 0 | ⬜ |
| `TEST-048.06` | 49–52 | بلوک | `test_auto_receipts_are_marked_delivered_for_staff` | 0 | ⬜ |
| `TEST-048.07` | 53–60 | بلوک | `test_only_migration_037_flips_legacy_auto_rows` | 0 | ⬜ |
| `TEST-048.08` | 61–66 | بلوک | `test_delivery_report_includes_auto_rows` | 0 | ⬜ |
| `TEST-048.09` | 67–71 | بلوک | `test_holiday_source_is_single_calendar` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-049</b> — <code>tests/test_food_bulk_operations_static.py</code> (105 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-049.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-049.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-049.03` | 11–11 | بلوک | `FoodBulkOperationsStaticTests` | 0 | ⬜ |
| `TEST-049.04` | 12–29 | بلوک | `test_bulk_api_and_food_menu_view_are_restricted_to` | 0 | ⬜ |
| `TEST-049.05` | 30–42 | بلوک | `test_snapshot_includes_active_local_or_domain_staf` | 0 | ⬜ |
| `TEST-049.06` | 43–69 | بلوک | `test_past_bulk_order_and_cancel_require_active_eme` | 0 | ⬜ |
| `TEST-049.07` | 70–77 | بلوک | `test_past_bulk_ui_is_enabled_only_for_order_or_can` | 0 | ⬜ |
| `TEST-049.08` | 78–88 | بلوک | `test_canteen_change_uses_manager_override_but_keep` | 0 | ⬜ |
| `TEST-049.09` | 89–105 | بلوک | `test_bulk_tab_date_picker_and_removed_calendar_eme` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-050</b> — <code>tests/test_food_cancel_request_timestamp.py</code> (36 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-050.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-050.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-050.03` | 12–12 | بلوک | `FoodCancelRequestTimestampTests` | 0 | ⬜ |
| `TEST-050.04` | 13–18 | بلوک | `test_request_time_is_saved_and_included_in_audit_a` | 5 | ⬜ |
| `TEST-050.05` | 19–36 | بلوک | `test_dashboard_and_manager_request_report_show_req` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-051</b> — <code>tests/test_food_cap_and_receipt_unit.php</code> (73 خط، 28 آدرس، بازبینی‌شده 0/28)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-051.01` | 1–2 | ناحیه | `بلوک رویه‌ای 1-2` | 0 | ⬜ |
| `TEST-051.02` | 3–4 | ناحیه | `Behavioural tests of the real engine/order source ` | 0 | ⬜ |
| `TEST-051.03` | 5–5 | تابع | `food_ticket_source_table` | 0 | ⬜ |
| `TEST-051.04` | 6–6 | تابع | `food_order_schema_ensure` | 0 | ⬜ |
| `TEST-051.05` | 7–7 | تابع | `food_order_mode` | 0 | ⬜ |
| `TEST-051.06` | 8–8 | تابع | `food_order_iso_date` | 0 | ⬜ |
| `TEST-051.07` | 9–9 | تابع | `food_order_digits` | 0 | ⬜ |
| `TEST-051.08` | 10–10 | تابع | `food_ticket_digits` | 0 | ⬜ |
| `TEST-051.09` | 11–11 | تابع | `food_ticket_code` | 0 | ⬜ |
| `TEST-051.10` | 12–12 | تابع | `food_ticket_card_key` | 0 | ⬜ |
| `TEST-051.11` | 13–13 | تابع | `food_ticket_parse_date` | 0 | ⬜ |
| `TEST-051.12` | 14–14 | تابع | `food_ticket_parse_time` | 0 | ⬜ |
| `TEST-051.13` | 15–15 | تابع | `food_ticket_source_key` | 0 | ⬜ |
| `TEST-051.14` | 16–16 | تابع | `food_ticket_existing_source` | 0 | ⬜ |
| `TEST-051.15` | 17–17 | تابع | `food_ticket_existing_ticket` | 0 | ⬜ |
| `TEST-051.16` | 18–18 | تابع | `food_ticket_card_equals` | 0 | ⬜ |
| `TEST-051.17` | 19–19 | تابع | `food_ticket_guest_card` | 0 | ⬜ |
| `TEST-051.18` | 20–20 | تابع | `food_guest_requested_total` | 0 | ⬜ |
| `TEST-051.19` | 21–21 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-051.20` | 22–22 | تابع | `food_ticket_config` | 0 | ⬜ |
| `TEST-051.21` | 23–23 | تابع | `setting` | 0 | ⬜ |
| `TEST-051.22` | 24–24 | تابع | `setting_forget_cache` | 0 | ⬜ |
| `TEST-051.23` | 25–36 | کلاس | `ReceiptStmt` | 0 | ⬜ |
| `TEST-051.24` | 37–40 | کلاس | `ReceiptDB` | 0 | ⬜ |
| `TEST-051.25` | 41–41 | تابع | `db` | 0 | ⬜ |
| `TEST-051.26` | 42–43 | ناحیه | `بلوک رویه‌ای 42-43` | 0 | ⬜ |
| `TEST-051.27` | 44–44 | تابع | `chk` | 0 | ⬜ |
| `TEST-051.28` | 45–73 | ناحیه | `بلوک رویه‌ای 45-73` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-052</b> — <code>tests/test_food_dashboard_bulk_theme_fix_package.py</code> (48 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-052.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-052.02` | 11–48 | بلوک | `test_food_dashboard_bulk_theme_package_is_cumulati` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-053</b> — <code>tests/test_food_dashboard_css_isolation.py</code> (42 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-053.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-053.02` | 11–42 | بلوک | `test_r40_dashboard_grids_are_isolated_from_legacy_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-054</b> — <code>tests/test_food_dashboard_r41_features.py</code> (59 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-054.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-054.02` | 13–47 | بلوک | `test_r41_is_integrated_and_self_contained` | 0 | ⬜ |
| `TEST-054.03` | 48–59 | بلوک | `test_r41_theme_reveal_respects_reduced_motion_fall` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-055</b> — <code>tests/test_food_dashboard_update_package.py</code> (58 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-055.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-055.02` | 11–58 | بلوک | `test_r34_food_dashboard_is_integrated_and_uses_foo` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-056</b> — <code>tests/test_food_day_visit_static.py</code> (105 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-056.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-056.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-056.03` | 10–105 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-057</b> — <code>tests/test_food_delivery_comparison_node.js</code> (12 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-057.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-058</b> — <code>tests/test_food_delivery_comparison_unit.php</code> (25 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-058.01` | 1–2 | ناحیه | `بلوک رویه‌ای 1-2` | 0 | ⬜ |
| `TEST-058.02` | 3–13 | کلاس | `DeliveryStmt` | 0 | ⬜ |
| `TEST-058.03` | 14–14 | کلاس | `DeliveryDB` | 0 | ⬜ |
| `TEST-058.04` | 15–15 | تابع | `db` | 0 | ⬜ |
| `TEST-058.05` | 16–16 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-058.06` | 17–17 | ناحیه | `بلوک رویه‌ای 17-17` | 0 | ⬜ |
| `TEST-058.07` | 18–18 | تابع | `chk` | 0 | ⬜ |
| `TEST-058.08` | 19–25 | ناحیه | `بلوک رویه‌ای 19-25` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-059</b> — <code>tests/test_food_emergency_order_package.py</code> (43 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-059.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-059.02` | 11–43 | بلوک | `test_r43_package_is_limited_and_integral` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-060</b> — <code>tests/test_food_emergency_order_window_static.py</code> (68 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-060.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-060.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-060.03` | 11–11 | بلوک | `EmergencyOrderWindowTests` | 0 | ⬜ |
| `TEST-060.04` | 12–24 | بلوک | `test_grant_is_single_day_bounded_and_reasoned` | 0 | ⬜ |
| `TEST-060.05` | 25–35 | بلوک | `test_only_active_window_bypasses_past_or_closed_da` | 0 | ⬜ |
| `TEST-060.06` | 36–47 | بلوک | `test_manager_api_and_calendar_controls_are_permiss` | 0 | ⬜ |
| `TEST-060.07` | 48–55 | بلوک | `test_employee_ui_requires_backend_window_for_late_` | 0 | ⬜ |
| `TEST-060.08` | 56–68 | بلوک | `test_order_sort_uses_surname_then_first_name` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-061</b> — <code>tests/test_food_employee_local_static.py</code> (40 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-061.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-061.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-061.03` | 10–40 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-062</b> — <code>tests/test_food_group_typography_static.py</code> (48 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-062.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-062.02` | 7–7 | بلوک | `FoodGroupTypographyStaticTests` | 0 | ⬜ |
| `TEST-062.03` | 8–20 | بلوک | `test_group_print_model_uses_separate_menu_and_memb` | 0 | ⬜ |
| `TEST-062.04` | 21–31 | بلوک | `test_group_menu_counts_and_menu_to_member_spacing_` | 0 | ⬜ |
| `TEST-062.05` | 32–48 | بلوک | `test_group_designer_exposes_per_style_font_control` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-063</b> — <code>tests/test_food_guest_browser.js</code> (17 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-063.01` | 1–17 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-064</b> — <code>tests/test_food_guest_cap_path_unit.php</code> (44 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-064.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-064.02` | 4–4 | تابع | `db_table_exists` | 0 | ⬜ |
| `TEST-064.03` | 5–5 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-064.04` | 6–6 | تابع | `user_can` | 0 | ⬜ |
| `TEST-064.05` | 7–21 | کلاس | `CapStatement` | 1 | ⬜ |
| `TEST-064.06` | 22–22 | کلاس | `CapDatabase` | 4 | ⬜ |
| `TEST-064.07` | 23–23 | تابع | `db` | 0 | ⬜ |
| `TEST-064.08` | 24–25 | ناحیه | `بلوک رویه‌ای 24-25` | 0 | ⬜ |
| `TEST-064.09` | 26–26 | تابع | `chk` | 0 | ⬜ |
| `TEST-064.10` | 27–44 | ناحیه | `بلوک رویه‌ای 27-44` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-065</b> — <code>tests/test_food_guest_cap_race_node.js</code> (14 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-065.01` | 1–14 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-066</b> — <code>tests/test_food_guest_cap_ui_node.js</code> (25 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-066.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-066.02` | 7–7 | بلوک | `wait` | 0 | ⬜ |
| `TEST-066.03` | 8–20 | بلوک | `run` | 4 | ⬜ |
| `TEST-066.04` | 21–25 | بلوک | `testCardLimit` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-067</b> — <code>tests/test_food_guest_manager_bulk_tab.py</code> (59 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-067.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-067.02` | 8–9 | بلوک | `FoodGuestManagerBulkTabTests` | 0 | ⬜ |
| `TEST-067.03` | 10–16 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-067.04` | 17–30 | بلوک | `test_guest_manager_routes_require_emergency_permis` | 1 | ⬜ |
| `TEST-067.05` | 31–38 | بلوک | `test_support_view_includes_historical_requests_but` | 0 | ⬜ |
| `TEST-067.06` | 39–51 | بلوک | `test_guest_manager_tab_and_actions_are_inside_grou` | 1 | ⬜ |
| `TEST-067.07` | 52–59 | بلوک | `test_served_asset_copy_and_cache_buster_match` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-068</b> — <code>tests/test_food_guest_personal_scope.php</code> (78 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-068.01` | 1–2 | ناحیه | `بلوک رویه‌ای 1-2` | 0 | ⬜ |
| `TEST-068.02` | 3–3 | ناحیه | `Behavioural test of the real functions with an in-` | 0 | ⬜ |
| `TEST-068.03` | 4–4 | تابع | `user_can` | 0 | ⬜ |
| `TEST-068.04` | 5–5 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-068.05` | 7–42 | کلاس | `GuestStatement` | 0 | ⬜ |
| `TEST-068.06` | 43–51 | کلاس | `GuestDatabase` | 0 | ⬜ |
| `TEST-068.07` | 52–52 | تابع | `db` | 0 | ⬜ |
| `TEST-068.08` | 53–53 | ناحیه | `بلوک رویه‌ای 53-53` | 0 | ⬜ |
| `TEST-068.09` | 54–54 | تابع | `check_guest` | 0 | ⬜ |
| `TEST-068.10` | 55–55 | تابع | `denied` | 0 | ⬜ |
| `TEST-068.11` | 56–59 | ناحیه | `بلوک رویه‌ای 56-59` | 0 | ⬜ |
| `TEST-068.12` | 60–78 | ناحیه | `Many other people's rows before our own prove that` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-069</b> — <code>tests/test_food_guest_personal_scope_static.py</code> (30 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-069.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-069.02` | 7–22 | بلوک | `test_personal_and_privileged_guest_routes_are_isol` | 1 | ⬜ |
| `TEST-069.03` | 23–30 | بلوک | `test_guest_assets_cache_busted_and_css_mirrored` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-070</b> — <code>tests/test_food_guest_personal_ui_node.js</code> (51 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-070.01` | 1–51 | بلوک | `مقدمهٔ فایل` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-071</b> — <code>tests/test_food_guest_request_manager_actions.py</code> (61 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-071.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-071.02` | 7–8 | بلوک | `FoodGuestRequestManagerActionTests` | 0 | ⬜ |
| `TEST-071.03` | 9–12 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-071.04` | 13–19 | بلوک | `test_owner_and_support_manager_permissions_are_enf` | 0 | ⬜ |
| `TEST-071.05` | 20–28 | بلوک | `test_update_delete_and_cancel_have_separate_api_ro` | 1 | ⬜ |
| `TEST-071.06` | 29–37 | بلوک | `test_ui_renders_authorized_edit_cancel_delete_acti` | 1 | ⬜ |
| `TEST-071.07` | 38–42 | بلوک | `test_current_day_deadline_and_past_date_are_guarde` | 0 | ⬜ |
| `TEST-071.08` | 43–61 | بلوک | `test_past_guest_requests_can_be_deleted_without_by` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-072</b> — <code>tests/test_food_manual_attendance_static.py</code> (97 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-072.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-072.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-072.03` | 11–11 | بلوک | `FoodManualAttendanceStaticTests` | 0 | ⬜ |
| `TEST-072.04` | 12–25 | بلوک | `test_manual_attendance_is_in_settings_and_removed_` | 0 | ⬜ |
| `TEST-072.05` | 26–35 | بلوک | `test_manual_write_requires_group_edit_permission_a` | 0 | ⬜ |
| `TEST-072.06` | 36–51 | بلوک | `test_list_contains_only_active_people_with_the_sam` | 0 | ⬜ |
| `TEST-072.07` | 52–73 | بلوک | `test_single_and_bulk_save_support_ticket_and_no_ti` | 0 | ⬜ |
| `TEST-072.08` | 74–85 | بلوک | `test_ui_has_live_search_selection_results_and_inde` | 0 | ⬜ |
| `TEST-072.09` | 86–97 | بلوک | `test_monitoring_distinguishes_no_ticket_from_ticke` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-073</b> — <code>tests/test_food_meals_static.py</code> (57 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-073.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-073.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-073.03` | 12–12 | بلوک | `FoodMealSeparationTests` | 0 | ⬜ |
| `TEST-073.04` | 13–19 | بلوک | `test_fresh_schemas_keep_legacy_rows_as_lunch_and_a` | 0 | ⬜ |
| `TEST-073.05` | 20–29 | بلوک | `test_upgrade_migration_adds_lunch_defaults_and_rep` | 0 | ⬜ |
| `TEST-073.06` | 30–37 | بلوک | `test_api_filters_calendar_orders_and_statistics_by` | 2 | ⬜ |
| `TEST-073.07` | 38–47 | بلوک | `test_order_ui_and_manager_expose_meal_switch_and_s` | 0 | ⬜ |
| `TEST-073.08` | 48–57 | بلوک | `test_receipt_order_sources_are_explicitly_lunch_on` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-074</b> — <code>tests/test_food_monitoring_order_comparison_unit.php</code> (21 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-074.01` | 1–2 | ناحیه | `بلوک رویه‌ای 1-2` | 0 | ⬜ |
| `TEST-074.02` | 3–3 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-074.03` | 4–8 | کلاس | `MonitorStmt` | 0 | ⬜ |
| `TEST-074.04` | 9–9 | کلاس | `MonitorDB` | 0 | ⬜ |
| `TEST-074.05` | 10–10 | تابع | `db` | 0 | ⬜ |
| `TEST-074.06` | 11–12 | ناحیه | `بلوک رویه‌ای 11-12` | 0 | ⬜ |
| `TEST-074.07` | 13–13 | تابع | `chk` | 0 | ⬜ |
| `TEST-074.08` | 14–21 | ناحیه | `بلوک رویه‌ای 14-21` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-075</b> — <code>tests/test_food_order_calendar_node.js</code> (28 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-075.01` | 1–28 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-076</b> — <code>tests/test_food_order_selection_rerender_node.js</code> (12 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-076.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-077</b> — <code>tests/test_food_orders_static.py</code> (860 خط، 46 آدرس، بازبینی‌شده 0/46)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-077.01` | 1–16 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-077.02` | 17–20 | بلوک | `read` | 0 | ⬜ |
| `TEST-077.03` | 21–24 | بلوک | `strip_sql_comments` | 0 | ⬜ |
| `TEST-077.04` | 25–32 | بلوک | `create_tables` | 0 | ⬜ |
| `TEST-077.05` | 33–33 | بلوک | `FoodOrdersStaticTests` | 0 | ⬜ |
| `TEST-077.06` | 34–41 | بلوک | `test_upgrade_creates_exactly_five_new_tables_only` | 0 | ⬜ |
| `TEST-077.07` | 42–53 | بلوک | `test_rollback_drops_only_the_five_module_tables` | 0 | ⬜ |
| `TEST-077.08` | 54–60 | بلوک | `test_fresh_install_schemas_include_same_five_table` | 0 | ⬜ |
| `TEST-077.09` | 61–88 | بلوک | `test_table_definitions_match_across_upgrade_and_fr` | 0 | ⬜ |
| `TEST-077.10` | 89–97 | بلوک | `test_runtime_schema_helper_is_read_only` | 0 | ⬜ |
| `TEST-077.11` | 98–127 | بلوک | `test_guest_requests_allow_today_only_through_a_con` | 0 | ⬜ |
| `TEST-077.12` | 128–140 | بلوک | `test_worker_has_one_internal_order_map_and_no_acce` | 0 | ⬜ |
| `TEST-077.13` | 141–156 | بلوک | `test_legacy_access_order_fields_are_not_live_confi` | 0 | ⬜ |
| `TEST-077.14` | 157–165 | بلوک | `test_legacy_order_mode_cannot_be_changed_through_t` | 0 | ⬜ |
| `TEST-077.15` | 166–180 | بلوک | `test_employee_search_and_order_visibility_are_scop` | 0 | ⬜ |
| `TEST-077.16` | 181–191 | بلوک | `test_proxy_order_is_gated_by_national_code_and_rat` | 0 | ⬜ |
| `TEST-077.17` | 192–231 | بلوک | `test_panel_and_employee_assets_are_present_and_loc` | 0 | ⬜ |
| `TEST-077.18` | 232–241 | بلوک | `test_daily_report_fields_and_repeated_compact_reco` | 0 | ⬜ |
| `TEST-077.19` | 242–251 | بلوک | `test_food_group_daily_order_report_uses_dated_memb` | 0 | ⬜ |
| `TEST-077.20` | 252–262 | بلوک | `test_food_statistics_are_grouped_by_day_with_jalal` | 0 | ⬜ |
| `TEST-077.21` | 263–271 | بلوک | `test_food_dashboard_prints_each_printed_event_once` | 0 | ⬜ |
| `TEST-077.22` | 272–280 | بلوک | `test_food_panel_theme_toggle_updates_global_mode` | 0 | ⬜ |
| `TEST-077.23` | 281–295 | بلوک | `test_selected_staff_report_loads_a_checklist_and_p` | 0 | ⬜ |
| `TEST-077.24` | 296–305 | بلوک | `test_global_dark_theme_covers_food_forms_and_sched` | 0 | ⬜ |
| `TEST-077.25` | 306–309 | بلوک | `test_panel_assets_are_synchronized` | 0 | ⬜ |
| `TEST-077.26` | 310–316 | بلوک | `test_day_impact_confirmation_locks_and_reuses_the_` | 0 | ⬜ |
| `TEST-077.27` | 317–333 | بلوک | `test_selected_employee_orders_report_has_search_da` | 0 | ⬜ |
| `TEST-077.28` | 334–374 | بلوک | `test_emergency_order_window_is_under_system_settin` | 0 | ⬜ |
| `TEST-077.29` | 375–448 | بلوک | `test_daily_orders_and_statistics_modes_are_separat` | 0 | ⬜ |
| `TEST-077.30` | 449–469 | بلوک | `test_day_status_form_drops_reason_but_backend_stil` | 0 | ⬜ |
| `TEST-077.31` | 470–496 | بلوک | `test_proxy_calendar_reads_only_the_verified_recipi` | 0 | ⬜ |
| `TEST-077.32` | 497–537 | بلوک | `test_proxy_order_list_uses_only_the_verified_recip` | 5 | ⬜ |
| `TEST-077.33` | 538–559 | بلوک | `test_proxy_cancel_is_server_scoped_and_reverifies_` | 1 | ⬜ |
| `TEST-077.34` | 560–578 | بلوک | `test_proxy_cancellation_has_visible_confirmation_a` | 0 | ⬜ |
| `TEST-077.35` | 579–615 | بلوک | `test_excel_date_range_metadata_and_print_scope_are` | 2 | ⬜ |
| `TEST-077.36` | 616–624 | بلوک | `test_personal_order_table_uses_row_number_instead_` | 0 | ⬜ |
| `TEST-077.37` | 625–636 | بلوک | `test_order_source_explanations_are_not_rendered_in` | 0 | ⬜ |
| `TEST-077.38` | 637–665 | بلوک | `test_admin_order_edit_route_and_controls_are_remov` | 0 | ⬜ |
| `TEST-077.39` | 666–682 | بلوک | `test_order_highlight_colors_and_both_datepicker_ar` | 0 | ⬜ |
| `TEST-077.40` | 683–706 | بلوک | `test_employee_onboarding_is_initial_gate_and_day_d` | 0 | ⬜ |
| `TEST-077.41` | 707–730 | بلوک | `test_proxy_order_submission_targets_selected_emplo` | 1 | ⬜ |
| `TEST-077.42` | 731–749 | بلوک | `test_proxy_recipient_is_verified_server_side_and_e` | 0 | ⬜ |
| `TEST-077.43` | 750–764 | بلوک | `test_adjacent_calendar_days_are_disabled_and_manag` | 0 | ⬜ |
| `TEST-077.44` | 765–830 | بلوک | `test_closed_day_cancel_request_and_masked_national` | 0 | ⬜ |
| `TEST-077.45` | 831–840 | بلوک | `test_proxy_order_rate_allows_many_people` | 0 | ⬜ |
| `TEST-077.46` | 841–860 | بلوک | `test_order_list_default_range_includes_next_jalali` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-078</b> — <code>tests/test_food_panel_full_node.js</code> (5 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-078.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-079</b> — <code>tests/test_food_permissions_all_roles_static.py</code> (48 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-079.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-079.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-079.03` | 11–11 | بلوک | `FoodPermissionsAllRolesTests` | 0 | ⬜ |
| `TEST-079.04` | 12–19 | بلوک | `test_food_permission_catalog_is_rendered_for_every` | 0 | ⬜ |
| `TEST-079.05` | 20–28 | بلوک | `test_food_grants_are_not_stripped_when_saving_cust` | 0 | ⬜ |
| `TEST-079.06` | 29–37 | بلوک | `test_old_restriction_migration_no_longer_deletes_f` | 1 | ⬜ |
| `TEST-079.07` | 38–48 | بلوک | `test_domain_and_local_users_share_role_permission_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-080</b> — <code>tests/test_food_personnel_link_local_accounts_static.py</code> (75 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-080.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-080.02` | 9–10 | بلوک | `FoodPersonnelLocalLinkStaticTests` | 0 | ⬜ |
| `TEST-080.03` | 11–15 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-080.04` | 16–21 | بلوک | `test_candidates_include_local_food_staff_not_admin` | 0 | ⬜ |
| `TEST-080.05` | 22–29 | بلوک | `test_merge_accepts_local_staff_account_and_keeps_b` | 1 | ⬜ |
| `TEST-080.06` | 30–38 | بلوک | `test_manual_code_lookup_supports_335_after_ad_code` | 0 | ⬜ |
| `TEST-080.07` | 39–66 | بلوک | `test_domain_entered_employee_code_is_authoritative` | 0 | ⬜ |
| `TEST-080.08` | 67–75 | بلوک | `test_offline_merge_covers_references_and_report` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-081</b> — <code>tests/test_food_printed_delivery_report_static.py</code> (69 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-081.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-081.02` | 10–13 | بلوک | `read` | 0 | ⬜ |
| `TEST-081.03` | 14–14 | بلوک | `PrintedDeliveryReportStaticTests` | 0 | ⬜ |
| `TEST-081.04` | 15–34 | بلوک | `test_report_uses_printed_delivered_employee_events` | 0 | ⬜ |
| `TEST-081.05` | 35–45 | بلوک | `test_report_api_is_restricted_to_food_order_close` | 0 | ⬜ |
| `TEST-081.06` | 46–58 | بلوک | `test_report_is_appended_after_statistics_and_match` | 0 | ⬜ |
| `TEST-081.07` | 59–69 | بلوک | `test_mirrored_assets_and_cache_versions_are_update` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-082</b> — <code>tests/test_food_queue_reconciliation_unit.php</code> (64 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-082.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-082.02` | 4–4 | تابع | `food_order_internal_food_for_employee` | 0 | ⬜ |
| `TEST-082.03` | 5–5 | تابع | `food_ticket_group_event_ids_for_run` | 0 | ⬜ |
| `TEST-082.04` | 6–6 | تابع | `food_ticket_group_print_mode` | 0 | ⬜ |
| `TEST-082.05` | 7–7 | تابع | `db_table_exists` | 0 | ⬜ |
| `TEST-082.06` | 8–8 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-082.07` | 9–9 | تابع | `food_ticket_tpl_print_network` | 0 | ⬜ |
| `TEST-082.08` | 10–33 | کلاس | `QueueStmt` | 1 | ⬜ |
| `TEST-082.09` | 34–37 | کلاس | `QueueDB` | 0 | ⬜ |
| `TEST-082.10` | 38–38 | تابع | `db` | 0 | ⬜ |
| `TEST-082.11` | 39–40 | ناحیه | `بلوک رویه‌ای 39-40` | 0 | ⬜ |
| `TEST-082.12` | 41–41 | تابع | `chk` | 0 | ⬜ |
| `TEST-082.13` | 42–42 | تابع | `event` | 0 | ⬜ |
| `TEST-082.14` | 43–43 | تابع | `reset_test` | 0 | ⬜ |
| `TEST-082.15` | 44–44 | تابع | `process` | 0 | ⬜ |
| `TEST-082.16` | 45–64 | ناحیه | `بلوک رویه‌ای 45-64` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-083</b> — <code>tests/test_food_report_ui_static.py</code> (116 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-083.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-083.02` | 9–12 | بلوک | `read` | 0 | ⬜ |
| `TEST-083.03` | 13–13 | بلوک | `FoodReportUiStaticTests` | 0 | ⬜ |
| `TEST-083.04` | 14–24 | بلوک | `test_all_groups_are_available_and_group_report_sup` | 0 | ⬜ |
| `TEST-083.05` | 25–33 | بلوک | `test_daily_order_field_choices_persist_per_user_in` | 0 | ⬜ |
| `TEST-083.06` | 34–44 | بلوک | `test_food_report_prints_clone_only_result_lists` | 0 | ⬜ |
| `TEST-083.07` | 45–63 | بلوک | `test_multicolumn_reports_have_dividers_and_larger_` | 0 | ⬜ |
| `TEST-083.08` | 64–79 | بلوک | `test_employee_statistics_serials_run_down_each_col` | 0 | ⬜ |
| `TEST-083.09` | 80–90 | بلوک | `test_food_statistics_layout_depends_on_the_selecte` | 0 | ⬜ |
| `TEST-083.10` | 91–98 | بلوک | `test_repeated_report_blocks_keep_only_their_bounda` | 0 | ⬜ |
| `TEST-083.11` | 99–116 | بلوک | `test_settings_code_link_is_modal_not_an_inline_sec` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-084</b> — <code>tests/test_food_temporary_group_transfer_static.py</code> (71 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-084.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-084.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-084.03` | 11–11 | بلوک | `FoodTemporaryGroupTransferStaticTests` | 0 | ⬜ |
| `TEST-084.04` | 12–25 | بلوک | `test_manager_transfer_uses_day_visit_record_and_ne` | 1 | ⬜ |
| `TEST-084.05` | 26–35 | بلوک | `test_moved_permanent_member_is_excluded_from_home_` | 0 | ⬜ |
| `TEST-084.06` | 36–45 | بلوک | `test_absence_and_group_views_mark_temporary_member` | 0 | ⬜ |
| `TEST-084.07` | 46–57 | بلوک | `test_bulk_ui_allows_temporary_transfer_and_return_` | 0 | ⬜ |
| `TEST-084.08` | 58–71 | بلوک | `test_return_removes_daily_transfer_with_audit_and_` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-085</b> — <code>tests/test_food_ticket_config_paths.php</code> (39 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-085.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-085.02` | 6–10 | کلاس | `FoodTicketFakeStatement` | 0 | ⬜ |
| `TEST-085.03` | 12–16 | کلاس | `FoodTicketFakeDb` | 0 | ⬜ |
| `TEST-085.04` | 17–24 | ناحیه | `بلوک رویه‌ای 17-24` | 0 | ⬜ |
| `TEST-085.05` | 25–25 | تابع | `db` | 0 | ⬜ |
| `TEST-085.06` | 26–26 | تابع | `cfg` | 0 | ⬜ |
| `TEST-085.07` | 27–39 | ناحیه | `بلوک رویه‌ای 27-39` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-086</b> — <code>tests/test_food_ticket_diagnostic_log_levels_static.py</code> (63 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-086.01` | 1–14 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-086.02` | 15–28 | بلوک | `test_successful_print_diagnostics_are_debug_not_er` | 0 | ⬜ |
| `TEST-086.03` | 29–37 | بلوک | `test_template_adjustment_is_debug_and_render_const` | 0 | ⬜ |
| `TEST-086.04` | 38–63 | بلوک | `test_combined_hotfix_includes_inventory_compatibil` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-087</b> — <code>tests/test_food_ticket_logic.php</code> (52 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-087.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-087.02` | 4–4 | تابع | `db` | 0 | ⬜ |
| `TEST-087.03` | 5–5 | تابع | `db_table_exists` | 0 | ⬜ |
| `TEST-087.04` | 6–6 | تابع | `cfg` | 0 | ⬜ |
| `TEST-087.05` | 7–7 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-087.06` | 8–13 | ناحیه | `بلوک رویه‌ای 8-13` | 0 | ⬜ |
| `TEST-087.07` | 14–18 | تابع | `expect` | 0 | ⬜ |
| `TEST-087.08` | 19–52 | ناحیه | `بلوک رویه‌ای 19-52` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-088</b> — <code>tests/test_food_update_package_assets.py</code> (70 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-088.01` | 1–41 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-088.02` | 42–60 | بلوک | `test_food_runtime_recovery_bundle_is_complete_and_` | 0 | ⬜ |
| `TEST-088.03` | 61–70 | بلوک | `test_hotfix_package_is_intact_and_panel_is_mirrore` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-089</b> — <code>tests/test_food_worker_window.php</code> (141 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-089.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `TEST-089.02` | 16–19 | تابع | `cfg` | 0 | ⬜ |
| `TEST-089.03` | 20–23 | ناحیه | `بلوک رویه‌ای 20-23` | 0 | ⬜ |
| `TEST-089.04` | 24–31 | تابع | `food_ticket_auto_receipt_holiday` | 0 | ⬜ |
| `TEST-089.05` | 32–35 | ناحیه | `بلوک رویه‌ای 32-35` | 0 | ⬜ |
| `TEST-089.06` | 36–43 | تابع | `expect` | 0 | ⬜ |
| `TEST-089.07` | 45–52 | تابع | `state_at` | 0 | ⬜ |
| `TEST-089.08` | 54–60 | ناحیه | `۱) پارس روزهای هفته` | 0 | ⬜ |
| `TEST-089.09` | 61–66 | ناحیه | `۲) پارس ساعت` | 0 | ⬜ |
| `TEST-089.10` | 67–81 | ناحیه | `۳) مرزهای پنجره: ۱۱:۰۰–۱۴:۰۰ با حاشیهٔ ۳۰ و ۱۵ ⇒ م` | 0 | ⬜ |
| `TEST-089.11` | 82–91 | ناحیه | `۴) حاشیهٔ صفر هم باید کار کند (کاربر می‌تواند «تا ` | 0 | ⬜ |
| `TEST-089.12` | 92–98 | ناحیه | `۵) روزهای غیرکاری و تعطیلات` | 0 | ⬜ |
| `TEST-089.13` | 99–104 | ناحیه | `۶) خاموش بودن پنجره = رفتار نسخهٔ قبلی (۲۴/۷)` | 0 | ⬜ |
| `TEST-089.14` | 105–112 | ناحیه | `۷) شروع بعدی` | 0 | ⬜ |
| `TEST-089.15` | 113–124 | ناحیه | `۸) ریتم پایش` | 0 | ⬜ |
| `TEST-089.16` | 125–141 | ناحیه | `۹) پایداری: تنظیمات ناقص نباید fatal بدهد` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-090</b> — <code>tests/test_food_worker_window_static.py</code> (464 خط، 50 آدرس، بازبینی‌شده 0/50)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-090.01` | 1–42 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-090.02` | 43–50 | بلوک | `_css_rule` | 0 | ⬜ |
| `TEST-090.03` | 51–59 | بلوک | `test_migration_031_is_the_only_new_file` | 0 | ⬜ |
| `TEST-090.04` | 60–64 | بلوک | `test_migration_adds_every_window_column` | 0 | ⬜ |
| `TEST-090.05` | 65–72 | بلوک | `test_migration_is_idempotent_for_every_column` | 0 | ⬜ |
| `TEST-090.06` | 73–90 | بلوک | `test_migration_defaults_match_php_defaults` | 0 | ⬜ |
| `TEST-090.07` | 91–100 | بلوک | `test_fresh_install_schema_has_every_window_column` | 0 | ⬜ |
| `TEST-090.08` | 101–119 | بلوک | `test_fresh_install_defaults_match_the_migration` | 0 | ⬜ |
| `TEST-090.09` | 120–126 | بلوک | `test_updater_enforces_the_incremental_prerequisite` | 0 | ⬜ |
| `TEST-090.10` | 127–137 | بلوک | `test_updater_prerequisite_has_an_explicit_escape_h` | 2 | ⬜ |
| `TEST-090.11` | 138–146 | بلوک | `test_window_disabled_by_default_so_upgrades_keep_o` | 0 | ⬜ |
| `TEST-090.12` | 147–159 | بلوک | `test_window_module_exposes_the_public_helpers` | 0 | ⬜ |
| `TEST-090.13` | 160–165 | بلوک | `test_window_module_is_loaded_before_config_default` | 0 | ⬜ |
| `TEST-090.14` | 166–172 | بلوک | `test_config_reads_window_columns_defensively` | 0 | ⬜ |
| `TEST-090.15` | 173–180 | بلوک | `test_window_reuses_the_single_holiday_calendar` | 0 | ⬜ |
| `TEST-090.16` | 181–185 | بلوک | `test_window_save_only_writes_existing_columns` | 1 | ⬜ |
| `TEST-090.17` | 186–194 | بلوک | `test_save_config_persists_window_without_breaking_` | 0 | ⬜ |
| `TEST-090.18` | 195–201 | بلوک | `test_worker_gates_access_behind_the_window` | 0 | ⬜ |
| `TEST-090.19` | 202–209 | بلوک | `test_worker_never_kills_itself_outside_the_window` | 0 | ⬜ |
| `TEST-090.20` | 210–219 | بلوک | `test_worker_does_not_drain_queue_outside_the_stric` | 0 | ⬜ |
| `TEST-090.21` | 220–225 | بلوک | `test_queue_only_mode_never_touches_access` | 0 | ⬜ |
| `TEST-090.22` | 226–234 | بلوک | `test_worker_does_nothing_outside_the_window_even_a` | 0 | ⬜ |
| `TEST-090.23` | 235–244 | بلوک | `test_worker_end_of_day_pass_is_the_only_exception` | 0 | ⬜ |
| `TEST-090.24` | 245–256 | بلوک | `test_auto_receipts_are_not_blocked_by_the_window` | 0 | ⬜ |
| `TEST-090.25` | 257–266 | بلوک | `test_worker_no_longer_reconnects_mysql_every_cycle` | 0 | ⬜ |
| `TEST-090.26` | 267–274 | بلوک | `test_worker_caches_settings_with_a_ttl` | 0 | ⬜ |
| `TEST-090.27` | 275–282 | بلوک | `test_odbc_handle_is_pooled_and_releasable` | 0 | ⬜ |
| `TEST-090.28` | 283–288 | بلوک | `test_pool_is_released_on_error_and_when_the_window` | 0 | ⬜ |
| `TEST-090.29` | 289–292 | بلوک | `test_pool_handle_expires_so_a_dead_connection_cann` | 0 | ⬜ |
| `TEST-090.30` | 293–299 | بلوک | `test_worker_has_three_distinct_poll_rhythms` | 0 | ⬜ |
| `TEST-090.31` | 300–308 | بلوک | `test_idle_sleep_is_chunked_so_max_minutes_still_wo` | 0 | ⬜ |
| `TEST-090.32` | 309–317 | بلوک | `test_holiday_board_markup_lives_in_one_place` | 0 | ⬜ |
| `TEST-090.33` | 318–324 | بلوک | `test_settings_page_has_a_holidays_tab` | 0 | ⬜ |
| `TEST-090.34` | 325–330 | بلوک | `test_holiday_tab_respects_the_view_permission` | 0 | ⬜ |
| `TEST-090.35` | 331–337 | بلوک | `test_holiday_actions_return_to_the_originating_tab` | 0 | ⬜ |
| `TEST-090.36` | 338–343 | بلوک | `test_every_holiday_form_carries_return_to` | 0 | ⬜ |
| `TEST-090.37` | 344–355 | بلوک | `test_year_board_select_attribute_is_not_broken` | 0 | ⬜ |
| `TEST-090.38` | 356–369 | بلوک | `test_panel_exposes_window_fields` | 0 | ⬜ |
| `TEST-090.39` | 370–380 | بلوک | `test_week_days_render_on_one_row_not_stacked` | 0 | ⬜ |
| `TEST-090.40` | 381–389 | بلوک | `test_every_week_day_is_still_rendered` | 0 | ⬜ |
| `TEST-090.41` | 390–399 | بلوک | `test_six_window_fields_sit_in_two_rows_of_three` | 0 | ⬜ |
| `TEST-090.42` | 400–409 | بلوک | `test_time_inputs_are_forced_to_24_hour` | 0 | ⬜ |
| `TEST-090.43` | 410–414 | بلوک | `test_time_picker_icon_stays_attached_to_its_field` | 0 | ⬜ |
| `TEST-090.44` | 415–422 | بلوک | `test_database_time_value_is_trimmed_for_the_time_i` | 0 | ⬜ |
| `TEST-090.45` | 423–430 | بلوک | `test_panel_version_was_bumped_with_the_panel_marku` | 0 | ⬜ |
| `TEST-090.46` | 431–441 | بلوک | `test_help_guide_explains_which_package_to_install` | 1 | ⬜ |
| `TEST-090.47` | 442–448 | بلوک | `test_panel_sends_window_payload_on_save` | 0 | ⬜ |
| `TEST-090.48` | 449–453 | بلوک | `test_panel_reads_window_state_from_the_api` | 0 | ⬜ |
| `TEST-090.49` | 454–458 | بلوک | `test_panel_warns_when_the_migration_has_not_run` | 0 | ⬜ |
| `TEST-090.50` | 459–464 | بلوک | `test_window_api_fields_require_the_db_edit_permiss` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-091</b> — <code>tests/test_fresh_install_schema_parity.py</code> (325 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-091.01` | 1–30 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-091.02` | 31–39 | بلوک | `_migration_files` | 0 | ⬜ |
| `TEST-091.03` | 40–46 | بلوک | `_strip_sql_comments` | 0 | ⬜ |
| `TEST-091.04` | 47–50 | بلوک | `_schema_text` | 0 | ⬜ |
| `TEST-091.05` | 51–59 | بلوک | `_created_tables` | 0 | ⬜ |
| `TEST-091.06` | 60–93 | بلوک | `_table_bodies` | 0 | ⬜ |
| `TEST-091.07` | 94–115 | بلوک | `_added_columns` | 0 | ⬜ |
| `TEST-091.08` | 116–139 | بلوک | `_added_indexes` | 0 | ⬜ |
| `TEST-091.09` | 140–145 | بلوک | `_migration_corpus` | 0 | ⬜ |
| `TEST-091.10` | 146–147 | بلوک | `-` | 0 | ⬜ |
| `TEST-091.11` | 148–150 | بلوک | `-` | 0 | ⬜ |
| `TEST-091.12` | 151–155 | بلوک | `test_migration_files_exist` | 0 | ⬜ |
| `TEST-091.13` | 156–167 | بلوک | `test_every_migration_table_exists_in_both_schema_f` | 0 | ⬜ |
| `TEST-091.14` | 168–187 | بلوک | `test_every_migration_column_exists_in_both_schema_` | 0 | ⬜ |
| `TEST-091.15` | 188–216 | بلوک | `test_every_migration_index_exists_in_both_schema_f` | 0 | ⬜ |
| `TEST-091.16` | 217–225 | بلوک | `test_both_schema_files_define_the_same_tables` | 0 | ⬜ |
| `TEST-091.17` | 226–247 | بلوک | `test_both_schema_files_define_identical_table_bodi` | 0 | ⬜ |
| `TEST-091.18` | 248–276 | بلوک | `test_schema_executes_in_order_without_forward_fore` | 0 | ⬜ |
| `TEST-091.19` | 277–315 | بلوک | `test_both_schema_files_seed_the_same_structural_ro` | 1 | ⬜ |
| `TEST-091.20` | 316–325 | بلوک | `test_full_rebuild_drops_every_table_in_both_schema` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-092</b> — <code>tests/test_global_theme_mode.py</code> (69 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-092.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-092.02` | 6–30 | بلوک | `test_global_theme_is_attached_to_all_main_shells` | 1 | ⬜ |
| `TEST-092.03` | 31–50 | بلوک | `test_food_panel_and_standalone_modules_follow_the_` | 0 | ⬜ |
| `TEST-092.04` | 51–69 | بلوک | `test_independent_php_renderers_use_shared_mode` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-093</b> — <code>tests/test_group_ticket_design_repair_static.py</code> (39 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-093.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-093.02` | 6–14 | بلوک | `test_group_design_is_saved_and_read_back` | 0 | ⬜ |
| `TEST-093.03` | 15–25 | بلوک | `test_group_preview_and_physical_print_share_group_` | 0 | ⬜ |
| `TEST-093.04` | 26–39 | بلوک | `test_main_page_routes_group_save_to_integrated_api` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-094</b> — <code>tests/test_hot_schema_guard_unit.php</code> (66 خط، 18 آدرس، بازبینی‌شده 0/18)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-094.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-094.02` | 4–4 | ناحیه | `Offline behavioural tests: no real configuration, ` | 0 | ⬜ |
| `TEST-094.03` | 5–5 | ناحیه | `تابع ticket_hot_schema_ensure() از متن فایل بیرون ` | 0 | ⬜ |
| `TEST-094.04` | 6–6 | ناحیه | `ساختگی اجرا می‌شود؛ هدف این است که «ترتیب $ready ن` | 0 | ⬜ |
| `TEST-094.05` | 7–10 | ناحیه | `واقعی سنجیده شود، نه با خواندن متن.` | 0 | ⬜ |
| `TEST-094.06` | 11–15 | تابع | `check_hot` | 0 | ⬜ |
| `TEST-094.07` | 16–21 | ناحیه | `بلوک رویه‌ای 16-21` | 0 | ⬜ |
| `TEST-094.08` | 22–22 | تابع | `db_in_transaction` | 0 | ⬜ |
| `TEST-094.09` | 23–23 | تابع | `db_add_missing_column` | 0 | ⬜ |
| `TEST-094.10` | 24–24 | تابع | `db_ensure_enum_values` | 0 | ⬜ |
| `TEST-094.11` | 25–25 | تابع | `system_log` | 0 | ⬜ |
| `TEST-094.12` | 26–26 | تابع | `register_shutdown_function_stub` | 0 | ⬜ |
| `TEST-094.13` | 28–32 | ناحیه | `تابع هدف را از فایل واقعی بیرون می‌کشیم تا نسخهٔ د` | 0 | ⬜ |
| `TEST-094.14` | 33–36 | ناحیه | `فقط ثبت shutdown به نسخهٔ قابل‌مشاهده تغییر می‌کند` | 4 | ⬜ |
| `TEST-094.15` | 37–44 | ناحیه | `۱) داخل تراکنش: هیچ DDL نباید اجرا شود` | 0 | ⬜ |
| `TEST-094.16` | 45–50 | ناحیه | `۲) فراخوانی دوبارهٔ داخل تراکنش: ثبت تکراری ممنوع` | 0 | ⬜ |
| `TEST-094.17` | 51–58 | ناحیه | `۳) دام اصلی: پس از پایان تراکنش مهاجرت باید واقعاً` | 0 | ⬜ |
| `TEST-094.18` | 59–66 | ناحیه | `۴) پس از موفقیت، دوباره اجرا نمی‌شود` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-095</b> — <code>tests/test_hot_schema_transaction_guard_static.py</code> (174 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-095.01` | 1–45 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-095.02` | 46–50 | بلوک | `read` | 0 | ⬜ |
| `TEST-095.03` | 51–57 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-095.04` | 58–67 | بلوک | `php_function` | 0 | ⬜ |
| `TEST-095.05` | 68–71 | بلوک | `hot_body` | 0 | ⬜ |
| `TEST-095.06` | 72–79 | بلوک | `test_hot_schema_checks_for_an_open_transaction` | 0 | ⬜ |
| `TEST-095.07` | 80–93 | بلوک | `test_transaction_check_comes_before_any_ddl` | 0 | ⬜ |
| `TEST-095.08` | 94–111 | بلوک | `test_ready_flag_is_not_set_while_deferring` | 0 | ⬜ |
| `TEST-095.09` | 112–119 | بلوک | `test_deferred_migration_is_scheduled` | 0 | ⬜ |
| `TEST-095.10` | 120–128 | بلوک | `test_shutdown_handler_is_registered_only_once` | 0 | ⬜ |
| `TEST-095.11` | 129–135 | بلوک | `test_deferral_is_logged` | 0 | ⬜ |
| `TEST-095.12` | 136–148 | بلوک | `test_existing_guard_is_not_regressed` | 0 | ⬜ |
| `TEST-095.13` | 149–159 | بلوک | `test_guarded_helper_stays_free_of_static_state` | 0 | ⬜ |
| `TEST-095.14` | 160–174 | بلوک | `test_bootstrap_still_runs_migration_outside_any_tr` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-096</b> — <code>tests/test_inventory_truth_static.py</code> (208 خط، 18 آدرس، بازبینی‌شده 0/18)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-096.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-096.02` | 9–12 | بلوک | `read` | 0 | ⬜ |
| `TEST-096.03` | 13–13 | بلوک | `InventoryTruthStaticTests` | 0 | ⬜ |
| `TEST-096.04` | 14–25 | بلوک | `test_login_identity_does_not_claim_hardware_collec` | 1 | ⬜ |
| `TEST-096.05` | 26–32 | بلوک | `test_agent_api_rejects_payload_without_hardware_ro` | 0 | ⬜ |
| `TEST-096.06` | 33–39 | بلوک | `test_domain_scan_does_not_save_identity_only_respo` | 0 | ⬜ |
| `TEST-096.07` | 40–46 | بلوک | `test_diagnostics_report_hardware_score_and_local_a` | 0 | ⬜ |
| `TEST-096.08` | 47–73 | بلوک | `test_legacy_local_collection_backend_remains_but_i` | 0 | ⬜ |
| `TEST-096.09` | 74–80 | بلوک | `test_winrm_only_targets_are_detected_when_icmp_is_` | 0 | ⬜ |
| `TEST-096.10` | 81–95 | بلوک | `test_one_host_credential_test_does_not_write_inven` | 0 | ⬜ |
| `TEST-096.11` | 96–103 | بلوک | `test_winrm_guidance_configures_targets_not_only_th` | 0 | ⬜ |
| `TEST-096.12` | 104–120 | بلوک | `test_scan_credentials_are_per_run_encrypted_and_no` | 0 | ⬜ |
| `TEST-096.13` | 121–130 | بلوک | `test_full_domain_scan_is_not_limited_to_a_single_h` | 0 | ⬜ |
| `TEST-096.14` | 131–147 | بلوک | `test_extended_windows_inventory_is_collected_and_p` | 0 | ⬜ |
| `TEST-096.15` | 148–157 | بلوک | `test_domain_reinventory_refreshes_only_machine_der` | 0 | ⬜ |
| `TEST-096.16` | 158–167 | بلوک | `test_internet_access_is_not_inferred_from_a_defaul` | 0 | ⬜ |
| `TEST-096.17` | 168–195 | بلوک | `test_successful_empty_categories_clear_but_failed_` | 0 | ⬜ |
| `TEST-096.18` | 196–208 | بلوک | `test_food_panel_and_ldap_slow_paths_emit_stage_tim` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-097</b> — <code>tests/test_it_expert_routing_offline.php</code> (251 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-097.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `TEST-097.02` | 20–33 | تابع | `ok` | 0 | ⬜ |
| `TEST-097.03` | 34–36 | ناحیه | `بلوک رویه‌ای 34-36` | 0 | ⬜ |
| `TEST-097.04` | 37–43 | ناحیه | `فهرست تنظیمات — همان چیزی که دیتابیس در settings ن` | 0 | ⬜ |
| `TEST-097.05` | 44–47 | تابع | `key_role_user_ids` | 0 | ⬜ |
| `TEST-097.06` | 48–51 | تابع | `user_is_key_role` | 0 | ⬜ |
| `TEST-097.07` | 52–56 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `TEST-097.08` | 57–60 | تابع | `is_it_agent` | 0 | ⬜ |
| `TEST-097.09` | 61–64 | تابع | `ticket_notification_user_can` | 0 | ⬜ |
| `TEST-097.10` | 65–65 | تابع | `cfg` | 0 | ⬜ |
| `TEST-097.11` | 66–69 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `TEST-097.12` | 70–73 | تابع | `is_staff_role` | 0 | ⬜ |
| `TEST-097.13` | 76–111 | کلاس | `FakeStmt` | 0 | ⬜ |
| `TEST-097.14` | 112–116 | کلاس | `FakeDb` | 0 | ⬜ |
| `TEST-097.15` | 117–117 | تابع | `db` | 0 | ⬜ |
| `TEST-097.16` | 118–118 | تابع | `ticket_org_manager_rank_sql` | 0 | ⬜ |
| `TEST-097.17` | 119–119 | تابع | `ticket_org_auto_role_sql` | 0 | ⬜ |
| `TEST-097.18` | 120–124 | ناحیه | `بلوک رویه‌ای 120-124` | 0 | ⬜ |
| `TEST-097.19` | 125–146 | تابع | `load_choose_handling_agent` | 4 | ⬜ |
| `TEST-097.20` | 147–205 | ناحیه | `بلوک رویه‌ای 147-205` | 0 | ⬜ |
| `TEST-097.21` | 206–210 | ناحیه | `کارشناس IT نباید مسئول تیکت پشتیبانی شود` | 0 | ⬜ |
| `TEST-097.22` | 211–251 | ناحیه | `و برعکس` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-098</b> — <code>tests/test_ldap_secret_and_legacy_status_static.py</code> (213 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-098.01` | 1–50 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-098.02` | 51–55 | بلوک | `read` | 0 | ⬜ |
| `TEST-098.03` | 56–62 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-098.04` | 63–72 | بلوک | `php_function` | 0 | ⬜ |
| `TEST-098.05` | 73–78 | بلوک | `test_ldap_secret_helpers_exist` | 0 | ⬜ |
| `TEST-098.06` | 79–89 | بلوک | `test_settings_action_never_saves_the_raw_password` | 0 | ⬜ |
| `TEST-098.07` | 90–98 | بلوک | `test_single_read_point_decrypts` | 0 | ⬜ |
| `TEST-098.08` | 99–109 | بلوک | `test_encryption_reuses_the_repository_pattern` | 0 | ⬜ |
| `TEST-098.09` | 110–122 | بلوک | `test_missing_app_key_keeps_todays_behaviour` | 0 | ⬜ |
| `TEST-098.10` | 123–130 | بلوک | `test_legacy_plaintext_values_still_readable` | 0 | ⬜ |
| `TEST-098.11` | 131–139 | بلوک | `test_failed_decryption_fails_closed` | 0 | ⬜ |
| `TEST-098.12` | 140–160 | بلوک | `test_envelope_layout_offsets_are_consistent` | 0 | ⬜ |
| `TEST-098.13` | 161–171 | بلوک | `test_failed_decryption_is_logged` | 0 | ⬜ |
| `TEST-098.14` | 172–182 | بلوک | `transition_map` | 0 | ⬜ |
| `TEST-098.15` | 183–193 | بلوک | `test_nothing_transitions_into_pending` | 0 | ⬜ |
| `TEST-098.16` | 194–205 | بلوک | `test_pending_always_has_a_way_out` | 0 | ⬜ |
| `TEST-098.17` | 206–213 | بلوک | `test_pending_is_documented_as_legacy` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-099</b> — <code>tests/test_ldap_secret_unit.php</code> (54 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-099.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-099.02` | 4–4 | ناحیه | `Offline behavioural tests: no real configuration, ` | 0 | ⬜ |
| `TEST-099.03` | 5–5 | ناحیه | `توجه: در این محیط افزونهٔ openssl در دسترس نیست، پ` | 0 | ⬜ |
| `TEST-099.04` | 6–6 | ناحیه | `مسیر «کلید موجود نیست» را می‌رود. دقیقاً همان مسیر` | 0 | ⬜ |
| `TEST-099.05` | 7–10 | ناحیه | `app.key اجرا می‌شود و شکستنش یعنی از کار افتادن ور` | 0 | ⬜ |
| `TEST-099.06` | 11–15 | تابع | `check_ldap` | 0 | ⬜ |
| `TEST-099.07` | 16–17 | ناحیه | `بلوک رویه‌ای 16-17` | 0 | ⬜ |
| `TEST-099.08` | 18–18 | تابع | `cfg` | 0 | ⬜ |
| `TEST-099.09` | 19–19 | ناحیه | `بلوک رویه‌ای 19-19` | 0 | ⬜ |
| `TEST-099.10` | 20–20 | تابع | `system_log` | 0 | ⬜ |
| `TEST-099.11` | 21–21 | تابع | `setting` | 0 | ⬜ |
| `TEST-099.12` | 22–24 | ناحیه | `بلوک رویه‌ای 22-24` | 0 | ⬜ |
| `TEST-099.13` | 25–29 | ناحیه | `سازگاری رو به عقب: نصبی که app.key ندارد` | 0 | ⬜ |
| `TEST-099.14` | 30–35 | ناحیه | `مقدار قدیمیِ متن ساده باید همچنان خوانده شود` | 0 | ⬜ |
| `TEST-099.15` | 36–43 | ناحیه | `شکست بسته: پاکت غیرقابل رمزگشایی هرگز به سرور دامی` | 0 | ⬜ |
| `TEST-099.16` | 44–54 | ناحیه | `نقطهٔ خواندن: ldap_cfg باید از همین مسیر رد شود` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-100</b> — <code>tests/test_logging_acceptance_harness_static.py</code> (39 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-100.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-100.02` | 6–14 | بلوک | `test_ci_provisions_disposable_mariadb_and_windows_` | 0 | ⬜ |
| `TEST-100.03` | 15–24 | بلوک | `test_local_database_harness_is_ephemeral_and_clean` | 0 | ⬜ |
| `TEST-100.04` | 25–33 | بلوک | `test_windows_acl_harness_has_positive_negative_and` | 0 | ⬜ |
| `TEST-100.05` | 34–39 | بلوک | `test_acceptance_docs_do_not_equate_ci_with_target_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-101</b> — <code>tests/test_logging_architecture_static.py</code> (53 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-101.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-101.02` | 6–13 | بلوک | `test_central_core_is_loaded_before_legacy_logging_` | 0 | ⬜ |
| `TEST-101.03` | 14–21 | بلوک | `test_database_migration_adds_correlation_to_all_ev` | 0 | ⬜ |
| `TEST-101.04` | 22–34 | بلوک | `test_spool_worker_is_cli_only_and_replay_is_idempo` | 1 | ⬜ |
| `TEST-101.05` | 35–43 | بلوک | `test_request_timeline_combines_system_activity_and` | 0 | ⬜ |
| `TEST-101.06` | 44–53 | بلوک | `test_untrusted_forwarded_headers_are_not_used_as_c` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-102</b> — <code>tests/test_logging_audit_migration_registry.php</code> (72 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-102.01` | 1–9 | ناحیه | `بلوک رویه‌ای 1-9` | 0 | ⬜ |
| `TEST-102.02` | 10–13 | تابع | `db_table_exists` | 0 | ⬜ |
| `TEST-102.03` | 15–21 | کلاس | `AuditMigrationStatement` | 0 | ⬜ |
| `TEST-102.04` | 22–32 | کلاس | `AuditMigrationDatabase` | 5 | ⬜ |
| `TEST-102.05` | 33–37 | تابع | `db` | 0 | ⬜ |
| `TEST-102.06` | 38–40 | ناحیه | `بلوک رویه‌ای 38-40` | 0 | ⬜ |
| `TEST-102.07` | 41–48 | تابع | `audit_assert` | 0 | ⬜ |
| `TEST-102.08` | 49–72 | ناحیه | `بلوک رویه‌ای 49-72` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-103</b> — <code>tests/test_logging_audit_outage_spool.php</code> (40 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-103.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-103.02` | 6–6 | تابع | `cfg` | 0 | ⬜ |
| `TEST-103.03` | 7–7 | تابع | `db` | 0 | ⬜ |
| `TEST-103.04` | 8–40 | ناحیه | `بلوک رویه‌ای 8-40` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-104</b> — <code>tests/test_logging_audit_regressions.php</code> (48 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-104.01` | 1–48 | ناحیه | `بلوک رویه‌ای 1-48` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-105</b> — <code>tests/test_logging_audit_required_transaction.php</code> (87 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-105.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-105.02` | 6–17 | کلاس | `AtomicAuditPDO` | 0 | ⬜ |
| `TEST-105.03` | 18–30 | کلاس | `AtomicAuditStatement` | 1 | ⬜ |
| `TEST-105.04` | 31–33 | ناحیه | `بلوک رویه‌ای 31-33` | 0 | ⬜ |
| `TEST-105.05` | 34–34 | تابع | `db` | 0 | ⬜ |
| `TEST-105.06` | 35–87 | ناحیه | `بلوک رویه‌ای 35-87` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-106</b> — <code>tests/test_logging_audit_static.py</code> (271 خط، 33 آدرس، بازبینی‌شده 0/33)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-106.01` | 1–23 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-106.02` | 24–27 | بلوک | `read` | 0 | ⬜ |
| `TEST-106.03` | 28–29 | بلوک | `-` | 0 | ⬜ |
| `TEST-106.04` | 30–32 | بلوک | `-` | 0 | ⬜ |
| `TEST-106.05` | 33–39 | بلوک | `test_audit_module_exists_inside_the_logging_librar` | 0 | ⬜ |
| `TEST-106.06` | 40–47 | بلوک | `test_audit_records_through_the_standard_logging_en` | 0 | ⬜ |
| `TEST-106.07` | 48–59 | بلوک | `test_audit_feeds_the_existing_health_alert_channel` | 0 | ⬜ |
| `TEST-106.08` | 60–66 | بلوک | `test_health_cron_runs_the_audit` | 0 | ⬜ |
| `TEST-106.09` | 67–77 | بلوک | `test_audit_failure_is_never_silent` | 0 | ⬜ |
| `TEST-106.10` | 78–87 | بلوک | `test_no_parallel_audit_tool_remains` | 0 | ⬜ |
| `TEST-106.11` | 88–95 | بلوک | `test_cli_is_a_thin_wrapper_not_a_second_implementa` | 0 | ⬜ |
| `TEST-106.12` | 96–97 | بلوک | `-` | 0 | ⬜ |
| `TEST-106.13` | 98–100 | بلوک | `-` | 0 | ⬜ |
| `TEST-106.14` | 101–107 | بلوک | `test_audit_event_codes_are_registered_in_the_catal` | 0 | ⬜ |
| `TEST-106.15` | 108–117 | بلوک | `test_audit_catalog_entries_are_complete` | 0 | ⬜ |
| `TEST-106.16` | 118–128 | بلوک | `test_structural_problems_are_critical_and_regressi` | 0 | ⬜ |
| `TEST-106.17` | 129–134 | بلوک | `test_audit_codes_are_used_by_the_module` | 0 | ⬜ |
| `TEST-106.18` | 135–136 | بلوک | `-` | 0 | ⬜ |
| `TEST-106.19` | 137–139 | بلوک | `-` | 0 | ⬜ |
| `TEST-106.20` | 140–148 | بلوک | `test_structural_checks_cover_the_defect_that_went_` | 0 | ⬜ |
| `TEST-106.21` | 149–155 | بلوک | `test_checks_inspect_the_live_database_not_just_fil` | 0 | ⬜ |
| `TEST-106.22` | 156–165 | بلوک | `test_every_check_explains_why_it_matters` | 0 | ⬜ |
| `TEST-106.23` | 166–174 | بلوک | `test_structural_checks_have_no_budget` | 0 | ⬜ |
| `TEST-106.24` | 175–183 | بلوک | `test_baseline_lives_in_storage_not_in_the_reposito` | 0 | ⬜ |
| `TEST-106.25` | 184–191 | بلوک | `test_ci_runs_the_audit_without_a_database` | 0 | ⬜ |
| `TEST-106.26` | 192–193 | بلوک | `-` | 0 | ⬜ |
| `TEST-106.27` | 194–196 | بلوک | `-` | 0 | ⬜ |
| `TEST-106.28` | 197–211 | بلوک | `test_cli_check_mode_can_actually_fail` | 0 | ⬜ |
| `TEST-106.29` | 212–218 | بلوک | `test_summarize_is_shared_by_both_paths` | 0 | ⬜ |
| `TEST-106.30` | 219–227 | بلوک | `test_empty_baseline_is_not_a_silent_no_op` | 0 | ⬜ |
| `TEST-106.31` | 228–252 | بلوک | `test_audit_writes_its_own_heartbeat` | 0 | ⬜ |
| `TEST-106.32` | 253–261 | بلوک | `test_audit_detects_its_own_outage_after_recovery` | 0 | ⬜ |
| `TEST-106.33` | 262–271 | بلوک | `test_every_defined_check_is_registered` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-107</b> — <code>tests/test_logging_audit_transaction_status.php</code> (46 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-107.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-107.02` | 6–13 | کلاس | `AuditStatusPDO` | 0 | ⬜ |
| `TEST-107.03` | 14–18 | کلاس | `AuditStatusStatement` | 0 | ⬜ |
| `TEST-107.04` | 19–20 | ناحیه | `بلوک رویه‌ای 19-20` | 0 | ⬜ |
| `TEST-107.05` | 21–21 | تابع | `db` | 0 | ⬜ |
| `TEST-107.06` | 22–46 | ناحیه | `بلوک رویه‌ای 22-46` | **8** | ⬜ |

</details>

<details>
<summary><b>TEST-108</b> — <code>tests/test_logging_completion_static.py</code> (73 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-108.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-108.02` | 5–7 | بلوک | `read` | 0 | ⬜ |
| `TEST-108.03` | 8–14 | بلوک | `test_catalog_contains_user_admin_and_technical_gui` | 0 | ⬜ |
| `TEST-108.04` | 15–22 | بلوک | `test_issue_aggregator_is_integrated_and_idempotent` | 0 | ⬜ |
| `TEST-108.05` | 23–30 | بلوک | `test_runtime_logging_does_not_execute_ddl_for_log_` | 0 | ⬜ |
| `TEST-108.06` | 31–37 | بلوک | `test_health_dashboard_has_issue_filters_and_health` | 0 | ⬜ |
| `TEST-108.07` | 38–45 | بلوک | `test_windows_scheduled_jobs_and_retention_classes_` | 0 | ⬜ |
| `TEST-108.08` | 46–49 | بلوک | `test_program_version_is_user_requested_value` | 0 | ⬜ |
| `TEST-108.09` | 50–60 | بلوک | `test_alerts_are_rate_limited_and_delivered_to_prim` | 0 | ⬜ |
| `TEST-108.10` | 61–66 | بلوک | `test_user_facing_error_text_and_fatal_output_are_s` | 0 | ⬜ |
| `TEST-108.11` | 67–73 | بلوک | `test_replay_is_streamed_and_writer_lock_is_shared` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-109</b> — <code>tests/test_logging_core_offline.php</code> (95 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-109.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `TEST-109.02` | 8–14 | تابع | `cfg` | 0 | ⬜ |
| `TEST-109.03` | 15–95 | ناحیه | `بلوک رویه‌ای 15-95` | **8** | ⬜ |

</details>

<details>
<summary><b>TEST-110</b> — <code>tests/test_logging_correlation_pipeline.php</code> (90 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-110.01` | 1–8 | ناحیه | `بلوک رویه‌ای 1-8` | 0 | ⬜ |
| `TEST-110.02` | 9–16 | کلاس | `LoggingCorrelationFakeStatement` | 0 | ⬜ |
| `TEST-110.03` | 17–25 | کلاس | `LoggingCorrelationFakePDO` | 0 | ⬜ |
| `TEST-110.04` | 26–26 | ناحیه | `بلوک رویه‌ای 26-26` | 0 | ⬜ |
| `TEST-110.05` | 27–30 | تابع | `db` | 0 | ⬜ |
| `TEST-110.06` | 31–90 | ناحیه | `بلوک رویه‌ای 31-90` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-111</b> — <code>tests/test_logging_dashboard_offline.php</code> (24 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-111.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-111.02` | 4–4 | تابع | `e` | 0 | ⬜ |
| `TEST-111.03` | 5–5 | تابع | `db` | 0 | ⬜ |
| `TEST-111.04` | 6–6 | تابع | `logging_emergency_failure` | 0 | ⬜ |
| `TEST-111.05` | 7–7 | تابع | `logging_spool_stats` | 0 | ⬜ |
| `TEST-111.06` | 8–8 | تابع | `system_health_checks` | 0 | ⬜ |
| `TEST-111.07` | 9–24 | ناحیه | `بلوک رویه‌ای 9-24` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-112</b> — <code>tests/test_logging_dashboard_static.py</code> (35 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-112.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-112.02` | 10–17 | بلوک | `test_dashboard_is_permission_gated_and_routed` | 0 | ⬜ |
| `TEST-112.03` | 18–23 | بلوک | `test_dashboard_uses_real_logging_sources_and_local` | 0 | ⬜ |
| `TEST-112.04` | 24–31 | بلوک | `test_dashboard_handles_unknown_database_state_and_` | 0 | ⬜ |
| `TEST-112.05` | 32–35 | بلوک | `test_dashboard_auto_refresh_is_bounded_and_visibil` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-113</b> — <code>tests/test_logging_diagnostics_offline.php</code> (27 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-113.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-114</b> — <code>tests/test_logging_hardening_static.py</code> (67 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-114.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-114.02` | 6–16 | بلوک | `test_spool_stream_is_allowlisted_and_partial_write` | 0 | ⬜ |
| `TEST-114.03` | 17–29 | بلوک | `test_activity_normalization_keeps_safe_values_and_` | 0 | ⬜ |
| `TEST-114.04` | 30–43 | بلوک | `test_logging_transaction_contracts_are_preserved` | 0 | ⬜ |
| `TEST-114.05` | 44–52 | بلوک | `test_retention_and_migration_remain_conservative_a` | 0 | ⬜ |
| `TEST-114.06` | 53–57 | بلوک | `test_unknown_error_title_has_mbstring_fallback` | 0 | ⬜ |
| `TEST-114.07` | 58–67 | بلوک | `test_full_rebuild_drops_every_schema_table` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-115</b> — <code>tests/test_logging_integrity_chain.py</code> (138 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-115.01` | 1–16 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-115.02` | 17–32 | بلوک | `_write_snapshot` | 0 | ⬜ |
| `TEST-115.03` | 33–36 | بلوک | `_verify` | 0 | ⬜ |
| `TEST-115.04` | 37–44 | بلوک | `test_consecutive_daily_exports_verify_as_a_chain` | 0 | ⬜ |
| `TEST-115.05` | 45–53 | بلوک | `test_missing_previous_day_is_rejected` | 0 | ⬜ |
| `TEST-115.06` | 54–63 | بلوک | `test_tampered_intermediate_archive_breaks_the_enti` | 0 | ⬜ |
| `TEST-115.07` | 64–78 | بلوک | `test_legacy_format_one_archive_remains_verifiable` | 0 | ⬜ |
| `TEST-115.08` | 79–103 | بلوک | `_write_supplement` | 0 | ⬜ |
| `TEST-115.09` | 104–115 | بلوک | `test_late_arrival_supplements_verify_against_immut` | 0 | ⬜ |
| `TEST-115.10` | 116–128 | بلوک | `test_late_arrival_supplement_detects_missing_prede` | 4 | ⬜ |
| `TEST-115.11` | 129–138 | بلوک | `test_late_arrival_supplement_detects_tampering` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-116</b> — <code>tests/test_logging_integrity_late_arrival.php</code> (27 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-116.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-117</b> — <code>tests/test_logging_integrity_offline.php</code> (36 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-117.01` | 1–36 | ناحیه | `بلوک رویه‌ای 1-36` | 6 | ⬜ |

</details>

<details>
<summary><b>TEST-118</b> — <code>tests/test_logging_integrity_static.py</code> (33 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-118.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-118.02` | 8–19 | بلوک | `test_integrity_export_and_verification_contracts` | 0 | ⬜ |
| `TEST-118.03` | 20–27 | بلوک | `test_integrity_supplement_is_linked_to_base_and_or` | 0 | ⬜ |
| `TEST-118.04` | 28–33 | بلوک | `test_integrity_export_scheduled_job_reconciles_sev` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-119</b> — <code>tests/test_logging_issue_aggregator.php</code> (109 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-119.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `TEST-119.02` | 8–24 | کلاس | `LoggingTestPDO` | 4 | ⬜ |
| `TEST-119.03` | 26–36 | کلاس | `LoggingTestStatement` | 0 | ⬜ |
| `TEST-119.04` | 37–38 | ناحیه | `بلوک رویه‌ای 37-38` | 0 | ⬜ |
| `TEST-119.05` | 39–39 | تابع | `db` | 0 | ⬜ |
| `TEST-119.06` | 40–109 | ناحیه | `بلوک رویه‌ای 40-109` | **8** | ⬜ |

</details>

<details>
<summary><b>TEST-120</b> — <code>tests/test_logging_mysql_integration.php</code> (258 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-120.01` | 1–35 | ناحیه | `بلوک رویه‌ای 1-35` | 5 | ⬜ |
| `TEST-120.02` | 36–36 | تابع | `db` | 0 | ⬜ |
| `TEST-120.03` | 37–46 | تابع | `execute_migration_statement` | 0 | ⬜ |
| `TEST-120.04` | 47–117 | تابع | `split_sql_statements` | 0 | ⬜ |
| `TEST-120.05` | 118–135 | ناحیه | `بلوک رویه‌ای 118-135` | 0 | ⬜ |
| `TEST-120.06` | 136–215 | ناحیه | `Migration 025 must remain safe when the updater re` | **12** | ⬜ |
| `TEST-120.07` | 216–228 | ناحیه | `The replay worker atomically renames the source fi` | 1 | ⬜ |
| `TEST-120.08` | 229–258 | ناحیه | `Verify migration 025 changes the live notification` | **13** | ⬜ |

</details>

<details>
<summary><b>TEST-121</b> — <code>tests/test_logging_phase56_static.py</code> (30 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-121.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-121.02` | 3–5 | بلوک | `test_diagnostics_engine_is_loaded_after_logging_co` | 0 | ⬜ |
| `TEST-121.03` | 6–11 | بلوک | `test_diagnostic_engine_has_stable_rules_and_confid` | 0 | ⬜ |
| `TEST-121.04` | 12–18 | بلوک | `test_agent_bundle_is_cli_only_and_redacted` | 0 | ⬜ |
| `TEST-121.05` | 19–24 | بلوک | `test_release_preflight_is_read_only_and_reports_de` | 1 | ⬜ |
| `TEST-121.06` | 25–30 | بلوک | `test_health_dashboard_displays_diagnostic_advice` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-122</b> — <code>tests/test_logging_phase73_regressions.php</code> (34 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-122.01` | 1–34 | ناحیه | `بلوک رویه‌ای 1-34` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-123</b> — <code>tests/test_logging_phase74_features.php</code> (31 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-123.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `TEST-123.02` | 7–7 | تابع | `cfg` | 0 | ⬜ |
| `TEST-123.03` | 8–31 | ناحیه | `بلوک رویه‌ای 8-31` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-124</b> — <code>tests/test_logging_phase74_package.py</code> (19 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-124.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-124.02` | 5–7 | بلوک | `read` | 0 | ⬜ |
| `TEST-124.03` | 8–19 | بلوک | `test_full_source_package_is_complete_and_versioned` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-125</b> — <code>tests/test_logging_phase76_static.py</code> (74 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-125.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-125.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-125.03` | 10–18 | بلوک | `test_required_audit_derives_module_and_target_cont` | 0 | ⬜ |
| `TEST-125.04` | 19–29 | بلوک | `test_holiday_mutations_are_audited_in_the_same_tra` | 1 | ⬜ |
| `TEST-125.05` | 30–38 | بلوک | `test_cddvd_governance_ldap_and_profile_events_have` | 0 | ⬜ |
| `TEST-125.06` | 39–45 | بلوک | `test_metadata_allowlists_preserve_operational_cont` | 0 | ⬜ |
| `TEST-125.07` | 46–52 | بلوک | `test_admin_category_no_longer_uses_null_module_as_` | 0 | ⬜ |
| `TEST-125.08` | 53–62 | بلوک | `test_ticket_timeline_supports_search_and_safe_deta` | 2 | ⬜ |
| `TEST-125.09` | 63–69 | بلوک | `test_circuit_open_does_not_spool_success_audit_out` | 0 | ⬜ |
| `TEST-125.10` | 70–74 | بلوک | `test_new_event_codes_have_catalog_definitions` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-126</b> — <code>tests/test_logging_replay_attempts.php</code> (101 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-126.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `TEST-126.02` | 7–14 | کلاس | `ReplayAttemptsPDO` | 0 | ⬜ |
| `TEST-126.03` | 15–32 | کلاس | `ReplayAttemptsStatement` | 1 | ⬜ |
| `TEST-126.04` | 33–33 | ناحیه | `بلوک رویه‌ای 33-33` | 0 | ⬜ |
| `TEST-126.05` | 34–34 | تابع | `db` | 0 | ⬜ |
| `TEST-126.06` | 35–56 | ناحیه | `بلوک رویه‌ای 35-56` | 0 | ⬜ |
| `TEST-126.07` | 57–62 | ناحیه | `Seven additional real permanent-data failures reac` | 0 | ⬜ |
| `TEST-126.08` | 63–73 | ناحیه | `Unparseable JSON cannot carry an attempts field; i` | 0 | ⬜ |
| `TEST-126.09` | 74–101 | ناحیه | `Schema errors must stay active and must not consum` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-127</b> — <code>tests/test_logging_replay_offline.php</code> (180 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-127.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `TEST-127.02` | 8–23 | کلاس | `ReplayFakePDO` | 0 | ⬜ |
| `TEST-127.03` | 24–64 | کلاس | `ReplayFakeStatement` | 1 | ⬜ |
| `TEST-127.04` | 65–65 | ناحیه | `بلوک رویه‌ای 65-65` | 0 | ⬜ |
| `TEST-127.05` | 66–66 | تابع | `db` | 0 | ⬜ |
| `TEST-127.06` | 67–98 | ناحیه | `بلوک رویه‌ای 67-98` | 1 | ⬜ |
| `TEST-127.07` | 99–112 | ناحیه | `رکورد poison با هفت retry قبلی باید به Dead Letter` | 1 | ⬜ |
| `TEST-127.08` | 113–128 | ناحیه | `قطع دیتابیس در Worker باید رکورد را در retry نگه د` | 5 | ⬜ |
| `TEST-127.09` | 129–149 | ناحیه | `خط معتبر با خطای DB حتی پس از بیش از ۸ تلاش نباید ` | 7 | ⬜ |
| `TEST-127.10` | 150–158 | ناحیه | `فایل .processing باقی‌مانده از Crash بدون نیاز به ` | 0 | ⬜ |
| `TEST-127.11` | 159–180 | ناحیه | `حین پردازش فایل .processing، writer باید بتواند فا` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-128</b> — <code>tests/test_logging_sanitizers.php</code> (36 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-128.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `TEST-128.02` | 7–7 | تابع | `cfg` | 0 | ⬜ |
| `TEST-128.03` | 8–36 | ناحیه | `بلوک رویه‌ای 8-36` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-129</b> — <code>tests/test_logging_spool_capacity.php</code> (29 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-129.01` | 1–29 | ناحیه | `بلوک رویه‌ای 1-29` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-130</b> — <code>tests/test_logging_spool_concurrency.php</code> (48 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-130.01` | 1–48 | ناحیه | `بلوک رویه‌ای 1-48` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-131</b> — <code>tests/test_logging_system_contract.py</code> (284 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-131.01` | 1–96 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-131.02` | 97–100 | بلوک | `read` | 0 | ⬜ |
| `TEST-131.03` | 101–107 | بلوک | `app_php_files` | 0 | ⬜ |
| `TEST-131.04` | 108–114 | بلوک | `all_php_files` | 0 | ⬜ |
| `TEST-131.05` | 115–122 | بلوک | `test_every_logging_runtime_file_is_present` | 0 | ⬜ |
| `TEST-131.06` | 123–145 | بلوک | `test_logging_migrations_exist_and_numbers_are_uniq` | 0 | ⬜ |
| `TEST-131.07` | 146–150 | بلوک | `test_bootstrap_loads_the_logging_core_before_its_d` | 0 | ⬜ |
| `TEST-131.08` | 151–168 | بلوک | `require_line` | 0 | ⬜ |
| `TEST-131.09` | 169–181 | بلوک | `test_no_php_file_requires_a_file_that_does_not_exi` | 0 | ⬜ |
| `TEST-131.10` | 182–205 | بلوک | `test_every_logging_function_called_is_defined_or_g` | 0 | ⬜ |
| `TEST-131.11` | 206–217 | بلوک | `test_security_log_access_denied_is_available_where` | 0 | ⬜ |
| `TEST-131.12` | 218–228 | بلوک | `test_logging_update_must_not_delete_1_62_75_featur` | 0 | ⬜ |
| `TEST-131.13` | 229–238 | بلوک | `test_logging_dashboard_is_reachable_from_the_repor` | 0 | ⬜ |
| `TEST-131.14` | 239–252 | بلوک | `test_columns_written_by_the_logging_core_exist_aft` | 0 | ⬜ |
| `TEST-131.15` | 253–284 | بلوک | `schema_columns` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-132</b> — <code>tests/test_logging_ticket_event_replay.php</code> (72 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-132.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `TEST-132.02` | 8–19 | کلاس | `TicketReplayFakePDO` | 0 | ⬜ |
| `TEST-132.03` | 20–38 | کلاس | `TicketReplayFakeStatement` | 1 | ⬜ |
| `TEST-132.04` | 39–39 | ناحیه | `بلوک رویه‌ای 39-39` | 0 | ⬜ |
| `TEST-132.05` | 40–40 | تابع | `db` | 0 | ⬜ |
| `TEST-132.06` | 41–72 | ناحیه | `بلوک رویه‌ای 41-72` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-133</b> — <code>tests/test_logging_trace_coverage.php</code> (50 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-133.01` | 1–50 | ناحیه | `بلوک رویه‌ای 1-50` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-134</b> — <code>tests/test_manual_no_print_food_ticket_static.py</code> (131 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-134.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-134.02` | 14–24 | بلوک | `test_wrong_emergency_implementation_is_removed_fro` | 0 | ⬜ |
| `TEST-134.03` | 25–131 | بلوک | `test_existing_settings_manual_attendance_bug_fix_p` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-135</b> — <code>tests/test_mariadb_fresh_install.php</code> (134 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-135.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `TEST-135.02` | 21–35 | ناحیه | `مسیر: fresh-install\|migrations-applied` | 0 | ⬜ |
| `TEST-135.03` | 36–46 | تابع | `check` | 0 | ⬜ |
| `TEST-135.04` | 47–73 | ناحیه | `بلوک رویه‌ای 47-73` | 4 | ⬜ |
| `TEST-135.05` | 74–98 | ناحیه | `مسیر: fresh-install` | 5 | ⬜ |
| `TEST-135.06` | 99–134 | ناحیه | `مسیر: migrations-applied` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-136</b> — <code>tests/test_migration_registry_seed.py</code> (55 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-136.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-136.02` | 14–20 | بلوک | `_version_tuple` | 0 | ⬜ |
| `TEST-136.03` | 21–55 | بلوک | `test_fresh_install_schemas_seed_every_forward_migr` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-137</b> — <code>tests/test_migration_retention_1_62_76.py</code> (29 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-137.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-137.02` | 16–20 | بلوک | `test_historical_logging_migrations_are_retained_in` | 0 | ⬜ |
| `TEST-137.03` | 21–29 | بلوک | `test_update_migration_policy_is_explicit_and_non_e` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-138</b> — <code>tests/test_no_duplicate_php_functions.py</code> (82 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-138.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-138.02` | 25–25 | بلوک | `NoDuplicatePhpFunctionsTest` | 0 | ⬜ |
| `TEST-138.03` | 26–50 | بلوک | `test_no_duplicate_function_declarations` | 0 | ⬜ |
| `TEST-138.04` | 51–59 | بلوک | `test_identity_file_has_redeclare_guard` | 0 | ⬜ |
| `TEST-138.05` | 60–68 | بلوک | `test_asset_profile_has_reload_guard` | 0 | ⬜ |
| `TEST-138.06` | 69–82 | بلوک | `test_index_uses_require_once_for_sensitive_modules` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-139</b> — <code>tests/test_org_chart_browser.js</code> (59 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-139.01` | 1–31 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-139.02` | 32–36 | بلوک | `verifyTop` | 0 | ⬜ |
| `TEST-139.03` | 37–49 | بلوک | `verifyBottom` | 0 | ⬜ |
| `TEST-139.04` | 50–59 | بلوک | `verifyEdge` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-140</b> — <code>tests/test_org_chart_experts_static.py</code> (147 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-140.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-140.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-140.03` | 12–147 | بلوک | `check` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-141</b> — <code>tests/test_org_chart_five_fixes_static.py</code> (105 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-141.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-141.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-141.03` | 10–19 | بلوک | `test_space_drag_is_local_and_uses_immediate_scroll` | 0 | ⬜ |
| `TEST-141.04` | 20–25 | بلوک | `test_empty_expert_cards_are_not_rendered_by_defaul` | 0 | ⬜ |
| `TEST-141.05` | 26–39 | بلوک | `test_role_repairs_and_routing_diagnostics_live_in_` | 0 | ⬜ |
| `TEST-141.06` | 40–50 | بلوک | `test_each_top_level_branch_has_an_inherited_distin` | 0 | ⬜ |
| `TEST-141.07` | 51–60 | بلوک | `test_add_submission_is_locked_in_ui_and_replay_tok` | 0 | ⬜ |
| `TEST-141.08` | 61–77 | بلوک | `test_exports_cover_full_chart_and_include_print_pr` | 0 | ⬜ |
| `TEST-141.09` | 78–87 | بلوک | `test_server_actions_refresh_chart_data_without_rel` | 0 | ⬜ |
| `TEST-141.10` | 88–96 | بلوک | `test_ceo_peer_units_accept_and_display_experts_ins` | 0 | ⬜ |
| `TEST-141.11` | 97–105 | بلوک | `test_space_repeats_are_prevented_and_wires_use_mea` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-142</b> — <code>tests/test_org_chart_user_report_fixes_static.py</code> (63 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-142.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-142.02` | 5–7 | بلوک | `read` | 0 | ⬜ |
| `TEST-142.03` | 8–18 | بلوک | `test_print_fits_the_complete_tree_and_hides_screen` | 0 | ⬜ |
| `TEST-142.04` | 19–25 | بلوک | `test_search_matches_units_managers_and_experts_the` | 0 | ⬜ |
| `TEST-142.05` | 26–34 | بلوک | `test_toolbar_is_outside_the_horizontal_scroller_an` | 0 | ⬜ |
| `TEST-142.06` | 35–41 | بلوک | `test_company_switch_uses_csp_safe_change_handler_a` | 0 | ⬜ |
| `TEST-142.07` | 42–49 | بلوک | `test_restore_input_stays_hidden_until_restore_butt` | 0 | ⬜ |
| `TEST-142.08` | 50–63 | بلوک | `test_action_request_has_explicit_company_url_and_e` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-143</b> — <code>tests/test_org_company_separation_static.py</code> (104 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-143.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-143.02` | 5–7 | بلوک | `read` | 0 | ⬜ |
| `TEST-143.03` | 8–18 | بلوک | `test_company_migration_is_separate_from_page_load_` | 0 | ⬜ |
| `TEST-143.04` | 19–35 | بلوک | `test_org_chart_is_filtered_to_selected_company_and` | 0 | ⬜ |
| `TEST-143.05` | 36–53 | بلوک | `test_company_status_is_derived_from_tree_membershi` | 1 | ⬜ |
| `TEST-143.06` | 54–68 | بلوک | `test_primary_admin_can_edit_display_only_company_n` | 1 | ⬜ |
| `TEST-143.07` | 69–77 | بلوک | `test_manager_positions_map_to_existing_global_mana` | 0 | ⬜ |
| `TEST-143.08` | 78–91 | بلوک | `test_backup_restore_and_reset_are_scoped_to_one_co` | 1 | ⬜ |
| `TEST-143.09` | 92–104 | بلوک | `test_shared_service_department_model_is_not_compan` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-144</b> — <code>tests/test_org_latest_chart_requests_static.py</code> (76 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-144.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-144.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-144.03` | 10–17 | بلوک | `test_export_menu_stacks_above_search_toolbar` | 0 | ⬜ |
| `TEST-144.04` | 18–33 | بلوک | `test_pdf_print_uses_full_chart_image_and_single_la` | 0 | ⬜ |
| `TEST-144.05` | 34–45 | بلوک | `test_deputy_and_department_cards_have_only_request` | 0 | ⬜ |
| `TEST-144.06` | 46–51 | بلوک | `test_partner_company_tree_suppresses_independent_u` | 0 | ⬜ |
| `TEST-144.07` | 52–76 | بلوک | `test_fixed_company_labels_show_in_selector_and_roo` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-145</b> — <code>tests/test_org_root_sort_static.py</code> (20 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-145.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-145.02` | 7–20 | بلوک | `test_root_sort_includes_management_and_company_and` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-146</b> — <code>tests/test_org_root_sort_unit.php</code> (37 خط، 24 آدرس، بازبینی‌شده 0/24)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-146.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-146.02` | 4–4 | کلاس | `DoneRedirect` | 0 | ⬜ |
| `TEST-146.03` | 5–9 | کلاس | `SortStmt` | 1 | ⬜ |
| `TEST-146.04` | 10–17 | کلاس | `SortDB` | 0 | ⬜ |
| `TEST-146.05` | 18–18 | تابع | `db` | 0 | ⬜ |
| `TEST-146.06` | 19–19 | تابع | `org_has_company_schema` | 0 | ⬜ |
| `TEST-146.07` | 19–19 | تابع | `org_company_context_id` | 0 | ⬜ |
| `TEST-146.08` | 20–20 | تابع | `org_company_root_title` | 2 | ⬜ |
| `TEST-146.09` | 20–20 | تابع | `post_value` | 2 | ⬜ |
| `TEST-146.10` | 21–21 | تابع | `org_unit_by_id` | 0 | ⬜ |
| `TEST-146.11` | 22–22 | تابع | `require_permission` | 0 | ⬜ |
| `TEST-146.12` | 22–22 | تابع | `user_can_manage_organization` | 0 | ⬜ |
| `TEST-146.13` | 23–23 | تابع | `org_has_sort_order` | 0 | ⬜ |
| `TEST-146.14` | 23–23 | تابع | `org_ceo_unit_id` | 0 | ⬜ |
| `TEST-146.15` | 24–24 | تابع | `org_snapshot` | 0 | ⬜ |
| `TEST-146.16` | 24–24 | تابع | `org_record_undo` | 0 | ⬜ |
| `TEST-146.17` | 24–24 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-146.18` | 24–24 | تابع | `save_audit_required` | 0 | ⬜ |
| `TEST-146.19` | 24–24 | تابع | `flash` | 0 | ⬜ |
| `TEST-146.20` | 25–25 | تابع | `org_company_redirect_url` | 0 | ⬜ |
| `TEST-146.21` | 25–25 | تابع | `redirect` | 0 | ⬜ |
| `TEST-146.22` | 26–29 | تابع | `runSort` | 0 | ⬜ |
| `TEST-146.23` | 30–30 | تابع | `chk` | 0 | ⬜ |
| `TEST-146.24` | 31–37 | ناحیه | `بلوک رویه‌ای 31-37` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-147</b> — <code>tests/test_org_tree_dynamic_static.py</code> (79 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-147.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-147.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-147.03` | 11–19 | بلوک | `test_additive_sort_migration_is_not_run_on_page_lo` | 1 | ⬜ |
| `TEST-147.04` | 20–31 | بلوک | `test_recursive_tree_and_nested_department_validati` | 0 | ⬜ |
| `TEST-147.05` | 32–45 | بلوک | `test_undo_redo_is_delta_scoped_and_does_not_restor` | 0 | ⬜ |
| `TEST-147.06` | 46–57 | بلوک | `test_existing_data_fields_and_privacy_boundary_are` | 0 | ⬜ |
| `TEST-147.07` | 58–70 | بلوک | `test_ui_keeps_fixed_sidebars_focus_pan_and_four_de` | 0 | ⬜ |
| `TEST-147.08` | 71–79 | بلوک | `test_soft_delete_and_shared_food_users_are_protect` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-148</b> — <code>tests/test_org_wires_node.js</code> (30 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-148.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-148.02` | 9–9 | بلوک | `box` | 0 | ⬜ |
| `TEST-148.03` | 10–30 | بلوک | `node` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-149</b> — <code>tests/test_particle_effect_runtime_node.js</code> (172 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-149.01` | 1–8 | بلوک | `Runtime smoke test for the standalone particle can` | 0 | ⬜ |
| `TEST-149.02` | 9–66 | بلوک | `FakeElement` | 0 | ⬜ |
| `TEST-149.03` | 67–172 | بلوک | `makeEnvironment` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-150</b> — <code>tests/test_particle_effect_static.py</code> (99 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-150.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-150.02` | 9–10 | بلوک | `ParticleEffectReleaseTests` | 0 | ⬜ |
| `TEST-150.03` | 11–16 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-150.04` | 17–28 | بلوک | `test_automatic_login_and_dashboard_particle_loader` | 0 | ⬜ |
| `TEST-150.05` | 29–47 | بلوک | `test_particle_settings_are_sanitized_and_match_eng` | 0 | ⬜ |
| `TEST-150.06` | 48–65 | بلوک | `test_two_accessible_tabs_use_the_existing_shared_3` | 0 | ⬜ |
| `TEST-150.07` | 66–81 | بلوک | `test_active_effect_and_both_configs_are_saved_with` | 0 | ⬜ |
| `TEST-150.08` | 82–88 | بلوک | `test_particle_preview_controller_has_live_controls` | 1 | ⬜ |
| `TEST-150.09` | 89–99 | بلوک | `test_login_dashboard_and_usage_guidance_are_explic` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-151</b> — <code>tests/test_permissions_followup_static.py</code> (58 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-151.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-151.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-151.03` | 11–11 | بلوک | `PermissionFollowupStaticTests` | 0 | ⬜ |
| `TEST-151.04` | 12–29 | بلوک | `test_health_and_traffic_destinations_have_view_edi` | 0 | ⬜ |
| `TEST-151.05` | 30–45 | بلوک | `test_cd_dvd_incoming_defaults_and_direct_edit_allo` | 1 | ⬜ |
| `TEST-151.06` | 46–50 | بلوک | `test_primary_admin_promotion_is_not_delegated` | 0 | ⬜ |
| `TEST-151.07` | 51–58 | بلوک | `test_proxy_permission_is_not_listed_as_an_independ` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-152</b> — <code>tests/test_release_1_62_10_org_liaison_static.py</code> (121 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-152.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-152.02` | 14–17 | بلوک | `read` | 0 | ⬜ |
| `TEST-152.03` | 18–31 | بلوک | `test_migration_adds_chart_side_idempotently` | 1 | ⬜ |
| `TEST-152.04` | 32–50 | بلوک | `test_index_php_supports_liaison_node` | 0 | ⬜ |
| `TEST-152.05` | 51–64 | بلوک | `test_organization_js_renders_liaison_band` | 0 | ⬜ |
| `TEST-152.06` | 65–78 | بلوک | `test_css_positions_liaisons_without_connectors` | 0 | ⬜ |
| `TEST-152.07` | 79–85 | بلوک | `test_help_guide_covers_liaison_units` | 0 | ⬜ |
| `TEST-152.08` | 86–115 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-152.09` | 116–121 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-153</b> — <code>tests/test_release_1_62_11_code_navigation_static.py</code> (96 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-153.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-153.02` | 14–17 | بلوک | `read` | 0 | ⬜ |
| `TEST-153.03` | 18–33 | بلوک | `test_index_has_navigation_banners` | 0 | ⬜ |
| `TEST-153.04` | 34–40 | بلوک | `test_bootstrap_has_function_family_toc` | 0 | ⬜ |
| `TEST-153.05` | 41–49 | بلوک | `test_architecture_and_audit_docs_exist` | 0 | ⬜ |
| `TEST-153.06` | 50–54 | بلوک | `test_safety_snapshot_documented_in_handoff` | 0 | ⬜ |
| `TEST-153.07` | 55–90 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-153.08` | 91–96 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-154</b> — <code>tests/test_release_1_62_12_org_actions_split_static.py</code> (107 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-154.01` | 1–23 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-154.02` | 24–27 | بلوک | `read` | 0 | ⬜ |
| `TEST-154.03` | 28–38 | بلوک | `test_index_requires_org_actions_file_in_place` | 0 | ⬜ |
| `TEST-154.04` | 39–52 | بلوک | `test_org_actions_file_contains_all_thirteen_action` | 0 | ⬜ |
| `TEST-154.05` | 53–63 | بلوک | `test_no_include_semantics_traps_in_moved_block` | 0 | ⬜ |
| `TEST-154.06` | 64–71 | بلوک | `test_liaison_behavior_strings_survived_the_move` | 0 | ⬜ |
| `TEST-154.07` | 72–101 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-154.08` | 102–107 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-155</b> — <code>tests/test_release_1_62_13_ticket_asset_actions_static.py</code> (143 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-155.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-155.02` | 25–28 | بلوک | `read` | 0 | ⬜ |
| `TEST-155.03` | 29–34 | بلوک | `body_of` | 0 | ⬜ |
| `TEST-155.04` | 35–49 | بلوک | `test_index_requires_both_files_in_place_and_in_ord` | 0 | ⬜ |
| `TEST-155.05` | 50–68 | بلوک | `test_ticket_actions_file_sections_and_persian_desc` | 1 | ⬜ |
| `TEST-155.06` | 69–89 | بلوک | `test_asset_actions_file_sections_and_persian_descr` | 0 | ⬜ |
| `TEST-155.07` | 90–122 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-155.08` | 123–128 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |
| `TEST-155.09` | 129–143 | بلوک | `test_fix1_dashboard_files_and_all_panel_asset_refs` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-156</b> — <code>tests/test_release_1_62_14_service_actions_static.py</code> (137 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-156.01` | 1–32 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-156.02` | 33–36 | بلوک | `read` | 0 | ⬜ |
| `TEST-156.03` | 37–50 | بلوک | `test_index_requires_service_actions_in_original_or` | 0 | ⬜ |
| `TEST-156.04` | 51–67 | بلوک | `test_service_actions_file_structure_and_persian_de` | 0 | ⬜ |
| `TEST-156.05` | 68–122 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-156.06` | 123–131 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-156.07` | 132–137 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-157</b> — <code>tests/test_release_1_62_15_service_modal_fix_static.py</code> (122 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-157.01` | 1–18 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-157.02` | 19–22 | بلوک | `read` | 0 | ⬜ |
| `TEST-157.03` | 23–34 | بلوک | `test_modal_layer_keeps_high_z_index_against_generi` | 0 | ⬜ |
| `TEST-157.04` | 35–43 | بلوک | `test_backdrop_click_closes_service_modal_like_othe` | 0 | ⬜ |
| `TEST-157.05` | 44–52 | بلوک | `test_cache_bust_versions_bumped` | 0 | ⬜ |
| `TEST-157.06` | 53–107 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-157.07` | 108–116 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-157.08` | 117–122 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-158</b> — <code>tests/test_release_1_62_16_role_settings_actions_static.py</code> (142 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-158.01` | 1–41 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-158.02` | 42–45 | بلوک | `read` | 0 | ⬜ |
| `TEST-158.03` | 46–58 | بلوک | `test_index_requires_both_files_in_original_order` | 0 | ⬜ |
| `TEST-158.04` | 59–75 | بلوک | `test_action_files_structure_and_persian_descriptio` | 0 | ⬜ |
| `TEST-158.05` | 76–127 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-158.06` | 128–136 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-158.07` | 137–142 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-159</b> — <code>tests/test_release_1_62_17_supervisor_holiday_actions_static.py</code> (149 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-159.01` | 1–36 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-159.02` | 37–40 | بلوک | `read` | 0 | ⬜ |
| `TEST-159.03` | 41–59 | بلوک | `test_index_requires_both_files_in_original_order` | 0 | ⬜ |
| `TEST-159.04` | 60–66 | بلوک | `test_food_cancellation_action_stays_with_food_modu` | 0 | ⬜ |
| `TEST-159.05` | 67–82 | بلوک | `test_action_files_structure_and_persian_descriptio` | 0 | ⬜ |
| `TEST-159.06` | 83–134 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-159.07` | 135–143 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-159.08` | 144–149 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-160</b> — <code>tests/test_release_1_62_18_account_misc_actions_static.py</code> (230 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-160.01` | 1–26 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-160.02` | 27–30 | بلوک | `read` | 0 | ⬜ |
| `TEST-160.03` | 31–51 | بلوک | `test_index_requires_all_four_files_in_original_ord` | 0 | ⬜ |
| `TEST-160.04` | 52–66 | بلوک | `test_action_files_structure_and_persian_descriptio` | 0 | ⬜ |
| `TEST-160.05` | 67–215 | بلوک | `test_version_manifest_and_package` | 2 | ⬜ |
| `TEST-160.06` | 216–224 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-160.07` | 225–230 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-161</b> — <code>tests/test_release_1_62_19_bulk_user_structure_actions_static.py</code> (143 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-161.01` | 1–28 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-161.02` | 29–32 | بلوک | `read` | 0 | ⬜ |
| `TEST-161.03` | 33–51 | بلوک | `test_index_requires_both_files_in_original_order` | 0 | ⬜ |
| `TEST-161.04` | 52–66 | بلوک | `test_action_files_structure_and_persian_descriptio` | 0 | ⬜ |
| `TEST-161.05` | 67–118 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-161.06` | 119–128 | بلوک | `test_backup_group_moved_in_1_62_20` | 0 | ⬜ |
| `TEST-161.07` | 129–137 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-161.08` | 138–143 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-162</b> — <code>tests/test_release_1_62_20_backup_split_bootstrap_lib_static.py</code> (201 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-162.01` | 1–44 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-162.02` | 45–48 | بلوک | `read` | 0 | ⬜ |
| `TEST-162.03` | 49–65 | بلوک | `test_backup_actions_moved_out_of_index` | 0 | ⬜ |
| `TEST-162.04` | 66–78 | بلوک | `test_index_require_order_keeps_backup_group_positi` | 0 | ⬜ |
| `TEST-162.05` | 79–92 | بلوک | `test_bootstrap_is_loader_and_lib_order_is_preserve` | 0 | ⬜ |
| `TEST-162.06` | 93–106 | بلوک | `test_lib_files_structure_and_strictness` | 0 | ⬜ |
| `TEST-162.07` | 107–123 | بلوک | `test_key_function_families_live_in_expected_lib_mo` | 0 | ⬜ |
| `TEST-162.08` | 124–135 | بلوک | `test_dead_functions_are_fully_removed` | 0 | ⬜ |
| `TEST-162.09` | 136–188 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-162.10` | 189–197 | بلوک | `test_panel_asset_refs_are_all_packaged` | 0 | ⬜ |
| `TEST-162.11` | 198–201 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-163</b> — <code>tests/test_release_1_62_21_food_printed_delivery_static.py</code> (95 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-163.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-163.02` | 14–41 | بلوک | `test_release_package_is_cumulative_from_16220_and_` | 0 | ⬜ |
| `TEST-163.03` | 42–61 | بلوک | `test_api_reads_event_records_with_the_requested_st` | 0 | ⬜ |
| `TEST-163.04` | 62–84 | بلوک | `test_report_is_after_statistics_with_selectable_fi` | 0 | ⬜ |
| `TEST-163.05` | 85–95 | بلوک | `test_packaged_panel_refs_resolve_and_assets_are_mi` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-164</b> — <code>tests/test_release_1_62_24_cumulative_static.py</code> (116 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-164.01` | 1–14 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-164.02` | 15–43 | بلوک | `test_cumulative_package_is_valid_and_targets_16220` | 0 | ⬜ |
| `TEST-164.03` | 44–58 | بلوک | `test_cumulative_bundle_contains_the_complete_16224` | 0 | ⬜ |
| `TEST-164.04` | 59–91 | بلوک | `test_cumulative_bundle_contains_16221_food_report_` | 0 | ⬜ |
| `TEST-164.05` | 92–104 | بلوک | `test_cumulative_bundle_contains_16222_through_1622` | 0 | ⬜ |
| `TEST-164.06` | 105–113 | بلوک | `test_update_manifest_paths_are_safe_for_the_app_up` | 0 | ⬜ |
| `TEST-164.07` | 114–116 | بلوک | `test_download_page_points_to_log_based_profile_and` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-165</b> — <code>tests/test_release_1_62_36_manual_attendance_reconciliation.py</code> (91 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-165.01` | 1–19 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-165.02` | 20–41 | بلوک | `test_16236_package_and_checksum_are_integral` | 0 | ⬜ |
| `TEST-165.03` | 42–57 | بلوک | `test_migration_backfills_delivery_and_clears_same_` | 0 | ⬜ |
| `TEST-165.04` | 58–77 | بلوک | `test_runtime_reconciliation_updates_stored_rows_wh` | 1 | ⬜ |
| `TEST-165.05` | 78–81 | بلوک | `test_download_page_points_to_16236` | 0 | ⬜ |
| `TEST-165.06` | 82–91 | بلوک | `test_system_updater_executes_manifest_migrations_f` | 6 | ⬜ |

</details>

<details>
<summary><b>TEST-166</b> — <code>tests/test_release_1_62_4_fixes_static.py</code> (73 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-166.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-166.02` | 11–14 | بلوک | `read` | 0 | ⬜ |
| `TEST-166.03` | 15–25 | بلوک | `test_dark_mode_covers_food_planning_forms_in_both_` | 0 | ⬜ |
| `TEST-166.04` | 26–36 | بلوک | `test_theme_css_cache_versions_are_bumped` | 0 | ⬜ |
| `TEST-166.05` | 37–45 | بلوک | `test_traffic_edit_history_lists_only_real_updates` | 0 | ⬜ |
| `TEST-166.06` | 46–56 | بلوک | `test_food_dashboard_host_keeps_full_width_and_no_s` | 0 | ⬜ |
| `TEST-166.07` | 57–73 | بلوک | `test_manual_ticket_list_has_select_all_and_no_defa` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-167</b> — <code>tests/test_release_1_62_5_theme_toggle_static.py</code> (43 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-167.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-167.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-167.03` | 12–22 | بلوک | `test_app_js_theme_toggle_is_robust` | 0 | ⬜ |
| `TEST-167.04` | 23–30 | بلوک | `test_all_host_shells_load_the_new_app_js` | 0 | ⬜ |
| `TEST-167.05` | 31–43 | بلوک | `test_release_package_ships_app_js` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-168</b> — <code>tests/test_release_1_62_60_package.py</code> (42 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-168.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-168.02` | 13–13 | بلوک | `ReleasePackageTest` | 0 | ⬜ |
| `TEST-168.03` | 14–42 | بلوک | `test_manifest_cumulative_files_and_hash` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-169</b> — <code>tests/test_release_1_62_61_package.py</code> (29 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-169.01` | 1–11 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-169.02` | 12–29 | بلوک | `test_cumulative_guest_fix_and_immutable_baseline` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-170</b> — <code>tests/test_release_1_62_6_auto_assign_static.py</code> (50 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-170.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-170.02` | 14–22 | بلوک | `test_org_managers_are_deprioritized_in_auto_assign` | 0 | ⬜ |
| `TEST-170.03` | 23–30 | بلوک | `test_support_manager_repair_action_exists` | 0 | ⬜ |
| `TEST-170.04` | 31–37 | بلوک | `test_auto_assign_diagnostics_card_is_rendered` | 0 | ⬜ |
| `TEST-170.05` | 38–50 | بلوک | `test_help_guide_explains_ticket_assignee` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-171</b> — <code>tests/test_release_1_62_73_dashboard_r2_package.py</code> (102 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-171.01` | 1–29 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-171.02` | 30–30 | بلوک | `DashboardR2ReleasePackageTest` | 0 | ⬜ |
| `TEST-171.03` | 31–81 | بلوک | `test_cumulative_package_contents_checksum_and_hist` | 0 | ⬜ |
| `TEST-171.04` | 82–102 | بلوک | `test_dashboard_r2_archive_stays_intact` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-172</b> — <code>tests/test_release_1_62_73_package.py</code> (149 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-172.01` | 1–59 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-172.02` | 60–60 | بلوک | `ReleasePackageTest` | 0 | ⬜ |
| `TEST-172.03` | 61–125 | بلوک | `test_cumulative_manifest_contents_and_immutable_ba` | 0 | ⬜ |
| `TEST-172.04` | 126–149 | بلوک | `test_dashboard_cleanup_archive_stays_intact` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-173</b> — <code>tests/test_release_1_62_74_package.py</code> (243 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-173.01` | 1–157 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TEST-173.02` | 158–158 | بلوک | `FoodReconciliationReleasePackageTest` | 0 | ⬜ |
| `TEST-173.03` | 159–222 | بلوک | `test_cumulative_package_manifest_payload_and_check` | 0 | ⬜ |
| `TEST-173.04` | 223–243 | بلوک | `test_meal_reconciliation_archive_stays_intact` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-174</b> — <code>tests/test_release_1_62_75_package.py</code> (239 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-174.01` | 1–159 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TEST-174.02` | 160–160 | بلوک | `PublicAccessGroupsReleasePackageTest` | 0 | ⬜ |
| `TEST-174.03` | 161–218 | بلوک | `test_cumulative_payload_migration_and_checksum` | 0 | ⬜ |
| `TEST-174.04` | 219–239 | بلوک | `test_public_access_groups_archive_stays_intact` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-175</b> — <code>tests/test_release_1_62_7_traffic_history_events_static.py</code> (73 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-175.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-175.02` | 11–14 | بلوک | `read` | 0 | ⬜ |
| `TEST-175.03` | 15–20 | بلوک | `test_release_1_62_7_manifest_version` | 0 | ⬜ |
| `TEST-175.04` | 21–31 | بلوک | `test_history_helpers_accept_all_events_flag` | 0 | ⬜ |
| `TEST-175.05` | 32–38 | بلوک | `test_changes_tab_reads_events_param_and_passes_it_` | 2 | ⬜ |
| `TEST-175.06` | 39–49 | بلوک | `test_changes_tab_has_filter_toggle_and_pagination_` | 3 | ⬜ |
| `TEST-175.07` | 50–55 | بلوک | `test_notes_promise_no_data_is_deleted` | 0 | ⬜ |
| `TEST-175.08` | 56–61 | بلوک | `test_help_guide_documents_the_toggle` | 0 | ⬜ |
| `TEST-175.09` | 62–66 | بلوک | `test_filter_buttons_have_styles` | 0 | ⬜ |
| `TEST-175.10` | 67–73 | بلوک | `test_update_manifest_is_present_and_consistent` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-176</b> — <code>tests/test_release_1_62_88_package_integrity.py</code> (48 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-176.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-176.02` | 8–14 | بلوک | `test_builder_requires_exact_base_and_versions` | 0 | ⬜ |
| `TEST-176.03` | 15–26 | بلوک | `test_manifest_and_archive_are_checked_bidirectiona` | 0 | ⬜ |
| `TEST-176.04` | 27–32 | بلوک | `test_checksum_is_created_from_closed_final_archive` | 0 | ⬜ |
| `TEST-176.05` | 33–38 | بلوک | `test_032_is_excluded_from_executable_migrations` | 0 | ⬜ |
| `TEST-176.06` | 39–43 | بلوک | `test_report_does_not_claim_all_migrations_are_addi` | 0 | ⬜ |
| `TEST-176.07` | 44–48 | بلوک | `test_package_excludes_obsolete_release_helpers` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-177</b> — <code>tests/test_release_1_62_8_traffic_autofill_multi_approver_static.py</code> (128 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-177.01` | 1–11 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-177.02` | 12–15 | بلوک | `read` | 0 | ⬜ |
| `TEST-177.03` | 16–22 | بلوک | `test_version_is_1_62_8` | 0 | ⬜ |
| `TEST-177.04` | 23–24 | بلوک | `بخش ۱: تکمیل خودکار با کد ملی / نام / تلفن` | 0 | ⬜ |
| `TEST-177.05` | 25–35 | بلوک | `test_lookup_api_accepts_code_phone_and_name` | 0 | ⬜ |
| `TEST-177.06` | 36–44 | بلوک | `test_lookup_javascript_triggers_on_name_and_phone` | 0 | ⬜ |
| `TEST-177.07` | 45–46 | بلوک | `بخش ۲: ۱ تا ۳ تأییدکننده برای هر مقصد` | 0 | ⬜ |
| `TEST-177.08` | 47–56 | بلوک | `test_multi_approver_columns_exist_in_schema_and_mi` | 0 | ⬜ |
| `TEST-177.09` | 57–68 | بلوک | `test_destination_forms_accept_three_approvers` | 1 | ⬜ |
| `TEST-177.10` | 69–77 | بلوک | `test_server_validates_approver_against_destination` | 0 | ⬜ |
| `TEST-177.11` | 78–88 | بلوک | `test_forms_render_approver_select_with_data_approv` | 0 | ⬜ |
| `TEST-177.12` | 89–100 | بلوک | `test_bulk_preview_button_moved_next_to_clear_selec` | 0 | ⬜ |
| `TEST-177.13` | 101–106 | بلوک | `test_help_guide_documents_both_features` | 0 | ⬜ |
| `TEST-177.14` | 107–112 | بلوک | `test_update_manifest_lists_migration` | 0 | ⬜ |
| `TEST-177.15` | 113–128 | بلوک | `test_current_package_passes_system_update_rules` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-178</b> — <code>tests/test_release_1_62_93_food_reconciliation_leftover_static.py</code> (267 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-178.01` | 1–36 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-178.02` | 37–37 | بلوک | `Migration036Tests` | 0 | ⬜ |
| `TEST-178.03` | 38–54 | بلوک | `test_migration_is_additive_and_idempotent` | 1 | ⬜ |
| `TEST-178.04` | 55–67 | بلوک | `test_fresh_install_schemas_match_migrated_schema` | 0 | ⬜ |
| `TEST-178.05` | 68–68 | بلوک | `ReconciliationPhpTests` | 0 | ⬜ |
| `TEST-178.06` | 69–95 | بلوک | `test_backend_exposes_new_contract_only` | 0 | ⬜ |
| `TEST-178.07` | 96–102 | بلوک | `test_export_headers_use_new_columns` | 0 | ⬜ |
| `TEST-178.08` | 103–107 | بلوک | `test_remaining_basis_is_received_minus_served` | 0 | ⬜ |
| `TEST-178.09` | 108–108 | بلوک | `ReconciliationJsTests` | 0 | ⬜ |
| `TEST-178.10` | 109–113 | بلوک | `test_js_copies_stay_byte_identical` | 0 | ⬜ |
| `TEST-178.11` | 114–139 | بلوک | `test_modal_and_new_columns_present` | 0 | ⬜ |
| `TEST-178.12` | 140–142 | بلوک | `OwnerItemsTests` | 0 | ⬜ |
| `TEST-178.13` | 143–156 | بلوک | `test_every_food_list_splits_name_and_has_sortable_` | 0 | ⬜ |
| `TEST-178.14` | 157–174 | بلوک | `test_same_day_cancel_request_cutoff_only_today_and` | 0 | ⬜ |
| `TEST-178.15` | 175–182 | بلوک | `test_bulk_cancel_tab_fields_are_compact_single_lin` | 0 | ⬜ |
| `TEST-178.16` | 183–199 | بلوک | `test_group_ticket_double_copy_is_group_only` | 0 | ⬜ |
| `TEST-178.17` | 200–208 | بلوک | `test_group_visit_cancel_button_compact_and_single_` | 0 | ⬜ |
| `TEST-178.18` | 209–209 | بلوک | `PackageTests` | 0 | ⬜ |
| `TEST-178.19` | 210–257 | بلوک | `test_package_exists_and_is_consistent` | 0 | ⬜ |
| `TEST-178.20` | 258–267 | بلوک | `test_change_report_present` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-179</b> — <code>tests/test_release_1_62_94_static.py</code> (234 خط، 18 آدرس، بازبینی‌شده 0/18)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-179.01` | 1–39 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-179.02` | 40–43 | بلوک | `read` | 0 | ⬜ |
| `TEST-179.03` | 44–44 | بلوک | `GuestReconciliationColumnTests` | 0 | ⬜ |
| `TEST-179.04` | 45–53 | بلوک | `test_daily_table_headers_exact` | 0 | ⬜ |
| `TEST-179.05` | 54–75 | بلوک | `test_daily_table_row_cell_count_matches_headers` | 0 | ⬜ |
| `TEST-179.06` | 76–85 | بلوک | `test_export_headers_exact_22_columns` | 0 | ⬜ |
| `TEST-179.07` | 86–100 | بلوک | `test_export_monthly_row_indices_fit_22_columns` | 0 | ⬜ |
| `TEST-179.08` | 101–105 | بلوک | `test_total_employee_orders_exposed_in_report_and_e` | 0 | ⬜ |
| `TEST-179.09` | 106–112 | بلوک | `test_gap_basis_text_documents_total_demand` | 0 | ⬜ |
| `TEST-179.10` | 113–116 | بلوک | `test_js_copies_stay_byte_identical` | 0 | ⬜ |
| `TEST-179.11` | 117–117 | بلوک | `OrgTreeAccessGroupTests` | 0 | ⬜ |
| `TEST-179.12` | 118–127 | بلوک | `test_org_edit_gate_honors_permissions_and_groups` | 0 | ⬜ |
| `TEST-179.13` | 128–141 | بلوک | `test_liaison_third_slot_beside_ceo` | 0 | ⬜ |
| `TEST-179.14` | 142–142 | بلوک | `OfficialHolidayOrderTests` | 0 | ⬜ |
| `TEST-179.15` | 143–161 | بلوک | `test_official_holiday_no_longer_blocks_menu_render` | 1 | ⬜ |
| `TEST-179.16` | 162–162 | بلوک | `PackageTests` | 0 | ⬜ |
| `TEST-179.17` | 163–218 | بلوک | `test_package_exists_and_is_consistent` | 0 | ⬜ |
| `TEST-179.18` | 219–234 | بلوک | `test_change_report_present_and_explicit` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-180</b> — <code>tests/test_release_1_62_9_manual_ticket_date_guest_static.py</code> (171 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-180.01` | 1–17 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-180.02` | 18–23 | بلوک | `read` | 0 | ⬜ |
| `TEST-180.03` | 24–35 | بلوک | `test_backend_accepts_past_dates_and_rejects_future` | 0 | ⬜ |
| `TEST-180.04` | 36–42 | بلوک | `test_route_passes_selected_date_instead_of_today_g` | 0 | ⬜ |
| `TEST-180.05` | 43–48 | بلوک | `test_save_many_forwards_date_to_each_save` | 0 | ⬜ |
| `TEST-180.06` | 49–62 | بلوک | `test_ui_has_jalali_date_field_with_calendar` | 0 | ⬜ |
| `TEST-180.07` | 63–80 | بلوک | `test_guest_row_is_first_and_guest_save_path_exists` | 0 | ⬜ |
| `TEST-180.08` | 81–88 | بلوک | `test_save_many_accepts_guest_id_zero` | 0 | ⬜ |
| `TEST-180.09` | 89–97 | بلوک | `test_ui_pins_guest_row_and_sends_id_zero` | 0 | ⬜ |
| `TEST-180.10` | 98–105 | بلوک | `test_guest_event_schema_support_already_exists` | 0 | ⬜ |
| `TEST-180.11` | 106–115 | بلوک | `test_destination_forms_fit_four_fields_on_one_line` | 0 | ⬜ |
| `TEST-180.12` | 116–122 | بلوک | `test_help_guide_documents_new_manual_ticket_featur` | 0 | ⬜ |
| `TEST-180.13` | 123–165 | بلوک | `test_version_manifest_and_package` | 0 | ⬜ |
| `TEST-180.14` | 166–171 | بلوک | `test_download_page_lists_only_latest_package` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-181</b> — <code>tests/test_release_1_63_2_r21_package.py</code> (111 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-181.01` | 1–36 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-181.02` | 37–111 | بلوک | `test_1632_r21_is_a_valid_cumulative_installable_pa` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-182</b> — <code>tests/test_release_notes_popup.js</code> (190 خط، 18 آدرس، بازبینی‌شده 0/18)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-182.01` | 1–28 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-182.02` | 29–38 | بلوک | `check` | 0 | ⬜ |
| `TEST-182.03` | 39–43 | بلوک | `تولید HTML از خودِ PHP (منبع حقیقت)` | 0 | ⬜ |
| `TEST-182.04` | 44–44 | بلوک | `e` | 0 | ⬜ |
| `TEST-182.05` | 45–55 | بلوک | `csp_nonce` | 0 | ⬜ |
| `TEST-182.06` | 56–59 | بلوک | `استخراج اسکریپت پاپ‌آپ` | 0 | ⬜ |
| `TEST-182.07` | 60–74 | بلوک | `CSS: عنصر پایه نباید display داشته باشد (ریشهٔ باگ` | 0 | ⬜ |
| `TEST-182.08` | 75–75 | بلوک | `شبیه‌سازی DOM + localStorage` | 0 | ⬜ |
| `TEST-182.09` | 76–124 | بلوک | `makeEnv` | 1 | ⬜ |
| `TEST-182.10` | 125–132 | بلوک | `runScript` | 0 | ⬜ |
| `TEST-182.11` | 133–138 | بلوک | `۱) نمایش اول` | 0 | ⬜ |
| `TEST-182.12` | 139–145 | بلوک | `۲) بستن با دکمه` | 0 | ⬜ |
| `TEST-182.13` | 146–150 | بلوک | `۳) بار دوم در یک بارگذاری تازه: چون dismissed=1 اس` | 0 | ⬜ |
| `TEST-182.14` | 151–160 | بلوک | `۴) سقف دو نمایش بدون بستن` | 0 | ⬜ |
| `TEST-182.15` | 161–167 | بلوک | `۵) Esc` | 0 | ⬜ |
| `TEST-182.16` | 168–174 | بلوک | `۶) localStorage مسدود (حالت ناشناس/سیاست سازمانی)` | 0 | ⬜ |
| `TEST-182.17` | 175–180 | بلوک | `۷) کلیک روی پس‌زمینه` | 0 | ⬜ |
| `TEST-182.18` | 181–190 | بلوک | `خلاصه` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-183</b> — <code>tests/test_repair_16236_full_source_restore.py</code> (163 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-183.01` | 1–34 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-183.02` | 35–44 | بلوک | `reconstructed_release_files` | 0 | ⬜ |
| `TEST-183.03` | 45–54 | بلوک | `reconstructed_migrations` | 0 | ⬜ |
| `TEST-183.04` | 55–80 | بلوک | `test_repair_package_rebuilds_the_release_overlay_n` | 0 | ⬜ |
| `TEST-183.05` | 81–90 | بلوک | `test_repair_package_carries_all_idempotent_migrati` | 0 | ⬜ |
| `TEST-183.06` | 91–127 | بلوک | `test_restored_package_has_the_missing_ui_and_serve` | 0 | ⬜ |
| `TEST-183.07` | 128–140 | بلوک | `test_download_pages_point_to_corrected_package_and` | 0 | ⬜ |
| `TEST-183.08` | 141–158 | بلوک | `test_repo_snapshot_matches_the_reconstructed_16236` | 0 | ⬜ |
| `TEST-183.09` | 159–163 | بلوک | `test_restore_package_checksum_matches` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-184</b> — <code>tests/test_request_center_access_matrix_static.py</code> (215 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-184.01` | 1–22 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-184.02` | 23–26 | بلوک | `read` | 0 | ⬜ |
| `TEST-184.03` | 27–45 | بلوک | `test_all_pages_are_covered_by_a_server_side_guard` | 0 | ⬜ |
| `TEST-184.04` | 46–55 | بلوک | `test_pages_have_no_unguarded_module_requests` | 0 | ⬜ |
| `TEST-184.05` | 56–67 | بلوک | `test_calendar_page_requires_elevated_or_own_scope` | 0 | ⬜ |
| `TEST-184.06` | 68–76 | بلوک | `test_detail_page_uses_scope_enforced_read_and_hide` | 0 | ⬜ |
| `TEST-184.07` | 77–85 | بلوک | `test_module_index_enforces_workspace_allowlist` | 0 | ⬜ |
| `TEST-184.08` | 86–94 | بلوک | `test_actions_require_app_root_guard` | 0 | ⬜ |
| `TEST-184.09` | 95–109 | بلوک | `test_rc_post_protocol_enforces_csrf_at_the_door` | 0 | ⬜ |
| `TEST-184.10` | 110–125 | بلوک | `test_appointment_actions_wire_complete_and_notific` | 0 | ⬜ |
| `TEST-184.11` | 126–139 | بلوک | `test_entry_routes_calendar_post_regression_is_pinn` | 0 | ⬜ |
| `TEST-184.12` | 140–145 | بلوک | `test_calendar_items_read_is_scope_enforced_in_repo` | 0 | ⬜ |
| `TEST-184.13` | 146–167 | بلوک | `test_pages_include_only_modules_are_guarded_and_ht` | 0 | ⬜ |
| `TEST-184.14` | 168–184 | بلوک | `test_support_manager_defaults_do_not_grant_supervi` | 0 | ⬜ |
| `TEST-184.15` | 185–195 | بلوک | `test_export_decisions_gate_is_supervisor_panel_or_` | 2 | ⬜ |
| `TEST-184.16` | 196–215 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-185</b> — <code>tests/test_request_center_appointment_concurrency_mariadb.php</code> (220 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-185.01` | 1–36 | ناحیه | `بلوک رویه‌ای 1-36` | 0 | ⬜ |
| `TEST-185.02` | 37–43 | تابع | `t13_check` | 0 | ⬜ |
| `TEST-185.03` | 44–220 | ناحیه | `بلوک رویه‌ای 44-220` | **10** | ⬜ |

</details>

<details>
<summary><b>TEST-186</b> — <code>tests/test_request_center_appointments_sqlite.php</code> (240 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-186.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-186.02` | 6–12 | تابع | `check_request_center` | 0 | ⬜ |
| `TEST-186.03` | 13–79 | ناحیه | `بلوک رویه‌ای 13-79` | 5 | ⬜ |
| `TEST-186.04` | 80–132 | ناحیه | `——— فاز ۴: اتمام، محدوده، ظرفیت و شمارشگرها ———` | 0 | ⬜ |
| `TEST-186.05` | 133–144 | ناحیه | `——— تاریخ/ساعت نامعتبر باید reject شود (رفع شکاف c` | 0 | ⬜ |
| `TEST-186.06` | 145–196 | ناحیه | `——— اولویت ۳: ساعت کاری روز + سقف ظرفیت روزانه (کا` | 5 | ⬜ |
| `TEST-186.07` | 197–240 | ناحیه | `——— createBatch «همه یا هیچ» (تصمیم مالک ۲۰۲۶-۱۱-۱` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-187</b> — <code>tests/test_request_center_claim_concurrency_mariadb.php</code> (198 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-187.01` | 1–30 | ناحیه | `بلوک رویه‌ای 1-30` | 0 | ⬜ |
| `TEST-187.02` | 31–37 | تابع | `t10_check` | 0 | ⬜ |
| `TEST-187.03` | 38–198 | ناحیه | `بلوک رویه‌ای 38-198` | **14** | ⬜ |

</details>

<details>
<summary><b>TEST-188</b> — <code>tests/test_request_center_first_slice_static.py</code> (132 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-188.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-188.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-188.03` | 11–11 | بلوک | `RequestCenterFirstSliceTests` | 0 | ⬜ |
| `TEST-188.04` | 12–21 | بلوک | `test_independent_entrypoint_bootstraps_and_authent` | 0 | ⬜ |
| `TEST-188.05` | 22–27 | بلوک | `test_repository_uses_existing_visibility_contracts` | 0 | ⬜ |
| `TEST-188.06` | 28–32 | بلوک | `test_internal_messages_are_filtered_in_sql` | 0 | ⬜ |
| `TEST-188.07` | 33–47 | بلوک | `test_read_slice_has_no_schema_or_write_operations` | 0 | ⬜ |
| `TEST-188.08` | 48–55 | بلوک | `test_transition_policy_is_owned_by_module_service` | 1 | ⬜ |
| `TEST-188.09` | 56–90 | بلوک | `test_migrated_status_writes_use_the_central_workfl` | 1 | ⬜ |
| `TEST-188.10` | 91–97 | بلوک | `test_user_supplied_text_is_escaped_and_attachment_` | 0 | ⬜ |
| `TEST-188.11` | 98–110 | بلوک | `test_workspace_views_are_permission_gated_and_scop` | 3 | ⬜ |
| `TEST-188.12` | 111–121 | بلوک | `test_not_found_and_detail_navigation_preserve_work` | 0 | ⬜ |
| `TEST-188.13` | 122–132 | بلوک | `test_legacy_workflow_is_not_removed_by_first_slice` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-189</b> — <code>tests/test_request_center_mariadb_ddl_gates.php</code> (108 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-189.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TEST-189.02` | 22–28 | تابع | `tcheck` | 0 | ⬜ |
| `TEST-189.03` | 30–37 | تابع | `tableExists` | 0 | ⬜ |
| `TEST-189.04` | 38–108 | ناحیه | `بلوک رویه‌ای 38-108` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-190</b> — <code>tests/test_request_center_metadata_lock_mariadb.php</code> (83 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-190.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `TEST-190.02` | 21–27 | تابع | `t09_check` | 0 | ⬜ |
| `TEST-190.03` | 29–36 | تابع | `t09_column_exists` | 0 | ⬜ |
| `TEST-190.04` | 37–83 | ناحیه | `بلوک رویه‌ای 37-83` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-191</b> — <code>tests/test_request_center_migration_037_mariadb.php</code> (76 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-191.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `TEST-191.02` | 16–22 | تابع | `migration_check` | 0 | ⬜ |
| `TEST-191.03` | 23–76 | ناحیه | `بلوک رویه‌ای 23-76` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-192</b> — <code>tests/test_request_center_phase2_smoke_mariadb.php</code> (949 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-192.01` | 1–32 | ناحیه | `بلوک رویه‌ای 1-32` | 0 | ⬜ |
| `TEST-192.02` | 33–43 | تابع | `smoke_latin_digits` | 0 | ⬜ |
| `TEST-192.03` | 45–51 | تابع | `smoke_check` | 0 | ⬜ |
| `TEST-192.04` | 53–78 | تابع | `smoke_http_post` | 6 | ⬜ |
| `TEST-192.05` | 80–123 | تابع | `smoke_http_multipart_post` | 6 | ⬜ |
| `TEST-192.06` | 125–136 | تابع | `smoke_wait_server` | 1 | ⬜ |
| `TEST-192.07` | 138–157 | تابع | `smoke_http` | 6 | ⬜ |
| `TEST-192.08` | 158–949 | ناحیه | `بلوک رویه‌ای 158-949` | **16** | ⬜ |

</details>

<details>
<summary><b>TEST-193</b> — <code>tests/test_request_center_phase3_lifecycle_static.py</code> (619 خط، 44 آدرس، بازبینی‌شده 0/44)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-193.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-193.02` | 7–10 | بلوک | `source` | 0 | ⬜ |
| `TEST-193.03` | 11–24 | بلوک | `test_lifecycle_pages_are_routed_inside_request_cen` | 0 | ⬜ |
| `TEST-193.04` | 25–52 | بلوک | `test_attachment_and_report_endpoints_are_independe` | 4 | ⬜ |
| `TEST-193.05` | 53–79 | بلوک | `test_lifecycle_actions_return_to_independent_works` | 2 | ⬜ |
| `TEST-193.06` | 80–98 | بلوک | `test_create_request_uses_one_time_session_token_an` | 0 | ⬜ |
| `TEST-193.07` | 99–109 | بلوک | `test_independent_runtime_has_no_schema_ensure_or_a` | 0 | ⬜ |
| `TEST-193.08` | 110–131 | بلوک | `test_sla_ola_history_is_visible_to_staff_without_e` | 0 | ⬜ |
| `TEST-193.09` | 132–141 | بلوک | `test_internal_note_content_is_not_copied_to_asset_` | 0 | ⬜ |
| `TEST-193.10` | 142–151 | بلوک | `test_sla_ola_alert_claims_are_audited_before_commi` | 1 | ⬜ |
| `TEST-193.11` | 152–161 | بلوک | `test_resolution_notification_warns_about_automatic` | 0 | ⬜ |
| `TEST-193.12` | 162–177 | بلوک | `test_automatic_closure_uses_required_system_audit_` | 1 | ⬜ |
| `TEST-193.13` | 178–186 | بلوک | `test_bulk_updates_skip_cancellation_pending_reques` | 0 | ⬜ |
| `TEST-193.14` | 187–200 | بلوک | `test_rejected_state_is_preflighted_and_excluded_fr` | 0 | ⬜ |
| `TEST-193.15` | 201–206 | بلوک | `test_ticket_maintenance_jobs_reject_web_before_sch` | 0 | ⬜ |
| `TEST-193.16` | 207–220 | بلوک | `test_requester_and_supervisor_reopen_reasons_are_v` | 0 | ⬜ |
| `TEST-193.17` | 221–230 | بلوک | `test_supervisor_reopen_requires_a_reason_server_si` | 0 | ⬜ |
| `TEST-193.18` | 231–240 | بلوک | `test_supervisor_reopen_guards_snapshot_fields_used` | 0 | ⬜ |
| `TEST-193.19` | 241–250 | بلوک | `test_supervisor_reopen_ui_requires_an_explicit_rea` | 0 | ⬜ |
| `TEST-193.20` | 251–262 | بلوک | `test_attachment_files_follow_database_transaction_` | 0 | ⬜ |
| `TEST-193.21` | 263–276 | بلوک | `test_public_management_and_bulk_replies_start_firs` | 0 | ⬜ |
| `TEST-193.22` | 277–286 | بلوک | `test_requester_cancellation_ui_and_service_share_o` | 0 | ⬜ |
| `TEST-193.23` | 287–304 | بلوک | `test_every_lifecycle_transition_carries_requester_` | 0 | ⬜ |
| `TEST-193.24` | 305–313 | بلوک | `test_supervisor_assignment_has_real_mariadb_http_c` | 0 | ⬜ |
| `TEST-193.25` | 314–337 | بلوک | `test_lifecycle_message_and_cancellation_writes_use` | 1 | ⬜ |
| `TEST-193.26` | 338–351 | بلوک | `test_rejection_is_distinct_terminal_state_with_aud` | 0 | ⬜ |
| `TEST-193.27` | 352–374 | بلوک | `test_supervisor_workspace_owns_assignment_and_canc` | 0 | ⬜ |
| `TEST-193.28` | 375–387 | بلوک | `test_supervisor_workspace_shows_scoped_workload_an` | 0 | ⬜ |
| `TEST-193.29` | 388–398 | بلوک | `test_supervisor_decisions_cannot_be_made_on_own_re` | 0 | ⬜ |
| `TEST-193.30` | 399–407 | بلوک | `test_bulk_status_changes_require_and_audit_a_reque` | 0 | ⬜ |
| `TEST-193.31` | 408–421 | بلوک | `test_assignment_and_priority_changes_notify_ticket` | 0 | ⬜ |
| `TEST-193.32` | 422–438 | بلوک | `test_lifecycle_notifications_are_idempotent_and_fa` | 0 | ⬜ |
| `TEST-193.33` | 439–451 | بلوک | `test_sla_and_ola_alerts_revalidate_current_deadlin` | 6 | ⬜ |
| `TEST-193.34` | 452–466 | بلوک | `test_rejection_schema_is_migration_owned_not_runti` | 0 | ⬜ |
| `TEST-193.35` | 467–476 | بلوک | `test_supervisor_decision_queue_preserves_paginatio` | 0 | ⬜ |
| `TEST-193.36` | 477–487 | بلوک | `test_lifecycle_post_errors_preserve_supervisor_and` | 0 | ⬜ |
| `TEST-193.37` | 488–501 | بلوک | `test_report_to_detail_navigation_preserves_report_` | 2 | ⬜ |
| `TEST-193.38` | 502–514 | بلوک | `test_calendar_ticket_links_return_to_originating_c` | 2 | ⬜ |
| `TEST-193.39` | 515–561 | بلوک | `test_four_workspaces_expose_their_real_lifecycle_o` | 0 | ⬜ |
| `TEST-193.40` | 562–567 | بلوک | `test_internal_note_write_uses_the_same_visibility_` | 0 | ⬜ |
| `TEST-193.41` | 568–576 | بلوک | `test_independent_post_runner_establishes_authentic` | 0 | ⬜ |
| `TEST-193.42` | 577–584 | بلوک | `test_ticket_notification_targets_use_the_core_noti` | 0 | ⬜ |
| `TEST-193.43` | 585–612 | بلوک | `test_core_has_no_request_center_references` | 0 | ⬜ |
| `TEST-193.44` | 613–619 | بلوک | `test_request_center_schema_preflight_runs_before_o` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-194</b> — <code>tests/test_request_center_phase3_workspaces_static.py</code> (67 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-194.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-194.02` | 6–9 | بلوک | `source` | 0 | ⬜ |
| `TEST-194.03` | 10–20 | بلوک | `test_requester_workspace_exposes_owned_lifecycle_a` | 0 | ⬜ |
| `TEST-194.04` | 21–32 | بلوک | `test_agent_workspace_exposes_claim_reply_internal_` | 0 | ⬜ |
| `TEST-194.05` | 33–43 | بلوک | `test_supervisor_workspace_exposes_assignment_and_d` | 0 | ⬜ |
| `TEST-194.06` | 44–67 | بلوک | `test_admin_workspace_has_scoped_audit_and_separate` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-195</b> — <code>tests/test_request_center_repository_sqlite.php</code> (118 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-195.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-195.02` | 6–9 | تابع | `ticket_scope` | 5 | ⬜ |
| `TEST-195.03` | 11–14 | تابع | `ticket_user_can_view` | 0 | ⬜ |
| `TEST-195.04` | 16–22 | تابع | `check_request_center` | 0 | ⬜ |
| `TEST-195.05` | 23–118 | ناحیه | `بلوک رویه‌ای 23-118` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-196</b> — <code>tests/test_request_center_schema_preflight_mariadb.php</code> (197 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-196.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-196.02` | 4–7 | تابع | `ticket_scope` | 5 | ⬜ |
| `TEST-196.03` | 9–12 | تابع | `ticket_user_can_view` | 0 | ⬜ |
| `TEST-196.04` | 14–20 | تابع | `check_request_center` | 0 | ⬜ |
| `TEST-196.05` | 21–35 | ناحیه | `بلوک رویه‌ای 21-35` | 0 | ⬜ |
| `TEST-196.06` | 36–197 | ناحیه | `Mirror the organization provider's install-time sc` | 5 | ⬜ |

</details>

<details>
<summary><b>TEST-197</b> — <code>tests/test_request_center_t07_migration_clone.php</code> (468 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-197.01` | 1–32 | ناحیه | `بلوک رویه‌ای 1-32` | 0 | ⬜ |
| `TEST-197.02` | 33–37 | تابع | `t07_fail` | 0 | ⬜ |
| `TEST-197.03` | 39–45 | تابع | `t07_check` | 0 | ⬜ |
| `TEST-197.04` | 47–50 | تابع | `t07_ident` | 0 | ⬜ |
| `TEST-197.05` | 52–94 | تابع | `t07_mysql_file` | 4 | ⬜ |
| `TEST-197.06` | 96–102 | تابع | `t07_tables` | 0 | ⬜ |
| `TEST-197.07` | 104–154 | تابع | `t07_fingerprint` | 5 | ⬜ |
| `TEST-197.08` | 156–165 | تابع | `t07_registry_rows` | 0 | ⬜ |
| `TEST-197.09` | 167–180 | تابع | `t07_service_catalog_distribution` | 0 | ⬜ |
| `TEST-197.10` | 182–219 | تابع | `t07_known_deltas` | 0 | ⬜ |
| `TEST-197.11` | 220–240 | ناحیه | `بلوک رویه‌ای 220-240` | 4 | ⬜ |
| `TEST-197.12` | 241–241 | ناحیه | `Strip only database-selection statements from the ` | 0 | ⬜ |
| `TEST-197.13` | 242–410 | ناحیه | `is forced into the disposable clone regardless of ` | 7 | ⬜ |
| `TEST-197.14` | 411–411 | ناحیه | `Subtract the explicitly-tolerated historical delta` | 0 | ⬜ |
| `TEST-197.15` | 412–436 | ناحیه | `final full-clone comparison; everything else remai` | 0 | ⬜ |
| `TEST-197.16` | 437–437 | ناحیه | `Cross-check the actual 1.62.88 release manifest. T` | 0 | ⬜ |
| `TEST-197.17` | 438–468 | ناحیه | `excludes migration 032; all manifest migrations mu` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-198</b> — <code>tests/test_request_center_t12_preflight.php</code> (322 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-198.01` | 1–33 | ناحیه | `بلوک رویه‌ای 1-33` | 0 | ⬜ |
| `TEST-198.02` | 34–37 | تابع | `t12_fail` | 0 | ⬜ |
| `TEST-198.03` | 39–45 | تابع | `t12_check` | 0 | ⬜ |
| `TEST-198.04` | 47–50 | تابع | `t12_ident` | 0 | ⬜ |
| `TEST-198.05` | 52–88 | تابع | `t12_mysql_file` | 4 | ⬜ |
| `TEST-198.06` | 90–109 | تابع | `t12_http` | 1 | ⬜ |
| `TEST-198.07` | 111–144 | تابع | `t12_start_server` | 4 | ⬜ |
| `TEST-198.08` | 146–161 | تابع | `t12_schema_fingerprint` | 0 | ⬜ |
| `TEST-198.09` | 163–181 | تابع | `t12_session` | 0 | ⬜ |
| `TEST-198.10` | 183–209 | تابع | `t12_config` | 0 | ⬜ |
| `TEST-198.11` | 210–322 | ناحیه | `بلوک رویه‌ای 210-322` | **12** | ⬜ |

</details>

<details>
<summary><b>TEST-199</b> — <code>tests/test_request_center_t12_scenarios.php</code> (1,105 خط، 36 آدرس، بازبینی‌شده 0/36)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-199.01` | 1–35 | ناحیه | `بلوک رویه‌ای 1-35` | 0 | ⬜ |
| `TEST-199.02` | 36–36 | تابع | `t12s_fail` | 0 | ⬜ |
| `TEST-199.03` | 37–37 | تابع | `t12s_check` | 0 | ⬜ |
| `TEST-199.04` | 38–38 | تابع | `t12s_ident` | 0 | ⬜ |
| `TEST-199.05` | 39–39 | تابع | `t12s_log` | 0 | ⬜ |
| `TEST-199.06` | 41–55 | تابع | `t12s_mysql_file` | 4 | ⬜ |
| `TEST-199.07` | 57–65 | تابع | `t12s_http` | 1 | ⬜ |
| `TEST-199.08` | 67–88 | تابع | `t12s_start_server` | 5 | ⬜ |
| `TEST-199.09` | 90–102 | تابع | `t12s_config` | 0 | ⬜ |
| `TEST-199.10` | 104–108 | تابع | `t12s_db` | 0 | ⬜ |
| `TEST-199.11` | 110–134 | تابع | `t12s_seed` | 5 | ⬜ |
| `TEST-199.12` | 136–148 | تابع | `t12s_web_users` | 4 | ⬜ |
| `TEST-199.13` | 150–158 | تابع | `t12s_rebuild` | **9** | ⬜ |
| `TEST-199.14` | 160–167 | تابع | `t12s_session` | 0 | ⬜ |
| `TEST-199.15` | 169–175 | تابع | `t12s_post` | 0 | ⬜ |
| `TEST-199.16` | 176–177 | ناحیه | `بلوک رویه‌ای 176-177` | 0 | ⬜ |
| `TEST-199.17` | 178–183 | تابع | `t12s_latin_body` | 0 | ⬜ |
| `TEST-199.18` | 185–211 | تابع | `t12s_snapshot_tickets` | 5 | ⬜ |
| `TEST-199.19` | 213–225 | تابع | `t12s_snapshot_table` | 5 | ⬜ |
| `TEST-199.20` | 227–240 | تابع | `t12s_snapshot` | 0 | ⬜ |
| `TEST-199.21` | 242–247 | تابع | `t12s_ts_diff_tol` | 0 | ⬜ |
| `TEST-199.22` | 249–379 | تابع | `t12s_compare` | 7 | ⬜ |
| `TEST-199.23` | 381–395 | تابع | `t12s_rebuild_all` | 4 | ⬜ |
| `TEST-199.24` | 397–408 | تابع | `t12s_sessions` | 0 | ⬜ |
| `TEST-199.25` | 410–410 | تابع | `t12s_clone_log_tail` | 1 | ⬜ |
| `TEST-199.26` | 412–441 | تابع | `t12s_create_ticket` | 0 | ⬜ |
| `TEST-199.27` | 443–446 | تابع | `t12s_pdo` | 0 | ⬜ |
| `TEST-199.28` | 448–453 | تابع | `t12s_expect_redirect` | 0 | ⬜ |
| `TEST-199.29` | 455–475 | تابع | `t12s_run_pair` | 0 | ⬜ |
| `TEST-199.30` | 477–559 | تابع | `t12s_finish` | 1 | ⬜ |
| `TEST-199.31` | 562–637 | تابع | `t12s_finish_feature_only` | 1 | ⬜ |
| `TEST-199.32` | 638–655 | ناحیه | `بلوک رویه‌ای 638-655` | 1 | ⬜ |
| `TEST-199.33` | 656–656 | ناحیه | `Both databases must exist BEFORE the web servers s` | 0 | ⬜ |
| `TEST-199.34` | 657–657 | ناحیه | `bootstrap performs a schema check on EVERY request` | 0 | ⬜ |
| `TEST-199.35` | 658–798 | ناحیه | `while a clone database is absent.` | **11** | ⬜ |
| `TEST-199.36` | 799–1105 | ناحیه | `مسیر: rated` | **20** | ⬜ |

</details>

<details>
<summary><b>TEST-200</b> — <code>tests/test_request_center_web_ddl_runtime.php</code> (262 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-200.01` | 1–31 | ناحیه | `بلوک رویه‌ای 1-31` | 5 | ⬜ |
| `TEST-200.02` | 32–38 | تابع | `t04_check` | 0 | ⬜ |
| `TEST-200.03` | 40–46 | تابع | `t04_reset_log` | 4 | ⬜ |
| `TEST-200.04` | 48–69 | تابع | `t04_log_window` | 1 | ⬜ |
| `TEST-200.05` | 71–90 | تابع | `t04_http` | 6 | ⬜ |
| `TEST-200.06` | 92–103 | تابع | `t04_wait_server` | 1 | ⬜ |
| `TEST-200.07` | 104–262 | ناحیه | `بلوک رویه‌ای 104-262` | **9** | ⬜ |

</details>

<details>
<summary><b>TEST-201</b> — <code>tests/test_request_center_workflow_service_mariadb.php</code> (266 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-201.01` | 1–29 | ناحیه | `بلوک رویه‌ای 1-29` | 0 | ⬜ |
| `TEST-201.02` | 30–36 | تابع | `mariadb_workflow_check` | 0 | ⬜ |
| `TEST-201.03` | 37–102 | ناحیه | `بلوک رویه‌ای 37-102` | 4 | ⬜ |
| `TEST-201.04` | 103–103 | ناحیه | `The MySQL driver branch appends FOR UPDATE; succes` | 1 | ⬜ |
| `TEST-201.05` | 104–120 | ناحیه | `that the production locking query is accepted by t` | 0 | ⬜ |
| `TEST-201.06` | 121–137 | ناحیه | `A caller-owned rollback must undo both the status ` | 0 | ⬜ |
| `TEST-201.07` | 138–198 | ناحیه | `A stale status and a stale optimistic field must b` | 4 | ⬜ |
| `TEST-201.08` | 199–209 | ناحیه | `Cancellation-pending blocks ordinary transitions e` | 0 | ⬜ |
| `TEST-201.09` | 210–220 | ناحیه | `Column names are never accepted from untrusted cal` | 5 | ⬜ |
| `TEST-201.10` | 221–266 | ناحیه | `Conversation writes and cancellation metadata shar` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-202</b> — <code>tests/test_request_center_workflow_service_sqlite.php</code> (411 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-202.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-202.02` | 6–12 | تابع | `workflow_check` | 0 | ⬜ |
| `TEST-202.03` | 13–411 | ناحیه | `بلوک رویه‌ای 13-411` | 7 | ⬜ |

</details>

<details>
<summary><b>TEST-203</b> — <code>tests/test_role_permissions_ui_static.py</code> (31 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-203.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-203.02` | 6–9 | بلوک | `read` | 0 | ⬜ |
| `TEST-203.03` | 10–31 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-204</b> — <code>tests/test_route_security_static.py</code> (124 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-204.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-204.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-204.03` | 12–17 | بلوک | `section` | 0 | ⬜ |
| `TEST-204.04` | 18–25 | بلوک | `test_new_ticket_page_requires_ticket_create_permis` | 0 | ⬜ |
| `TEST-204.05` | 26–38 | بلوک | `test_post_action_dispatch_enforces_csrf_before_han` | 2 | ⬜ |
| `TEST-204.06` | 39–46 | بلوک | `test_food_ticket_api_requires_login_and_csrf_for_p` | 0 | ⬜ |
| `TEST-204.07` | 47–55 | بلوک | `test_food_order_api_requires_authenticated_user_an` | 0 | ⬜ |
| `TEST-204.08` | 56–63 | بلوک | `test_sensitive_download_and_export_actions_have_ex` | 2 | ⬜ |
| `TEST-204.09` | 64–87 | بلوک | `test_action_handlers_refuse_direct_http_access` | 0 | ⬜ |
| `TEST-204.10` | 88–96 | بلوک | `test_self_service_password_change_is_audited_atomi` | 0 | ⬜ |
| `TEST-204.11` | 97–117 | بلوک | `test_internal_modules_refuse_direct_http_access` | 1 | ⬜ |
| `TEST-204.12` | 118–124 | بلوک | `test_it_ticket_asset_reference_obeys_asset_own_uni` | 2 | ⬜ |

</details>

<details>
<summary><b>TEST-205</b> — <code>tests/test_schema_heal_static.py</code> (46 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-205.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-205.02` | 8–11 | بلوک | `read` | 0 | ⬜ |
| `TEST-205.03` | 12–46 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-206</b> — <code>tests/test_selftest_panel_render.js</code> (281 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-206.01` | 1–21 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-206.02` | 22–31 | بلوک | `check` | 0 | ⬜ |
| `TEST-206.03` | 32–32 | بلوک | `استخراج یک تابع/ثابت با شمارش آکولاد.` | 0 | ⬜ |
| `TEST-206.04` | 33–99 | بلوک | `extract` | 0 | ⬜ |
| `TEST-206.05` | 100–105 | بلوک | `۱) حالت اولیه: دکمهٔ اجرا` | 0 | ⬜ |
| `TEST-206.06` | 106–110 | بلوک | `۲) حالت بارگذاری` | 0 | ⬜ |
| `TEST-206.07` | 111–116 | بلوک | `۳) حالت خطا` | 0 | ⬜ |
| `TEST-206.08` | 117–150 | بلوک | `۴) پاسخ سالم کامل (شبیه خروجی واقعی سرور)` | 0 | ⬜ |
| `TEST-206.09` | 151–177 | بلوک | `۵) پاسخ با ایراد: کلاس رنگی و راه‌حل` | 0 | ⬜ |
| `TEST-206.10` | 178–190 | بلوک | `۶) پنجرهٔ غیرفعال` | 0 | ⬜ |
| `TEST-206.11` | 191–205 | بلوک | `۷) امنیت: HTML تزریقی باید escape شود` | 0 | ⬜ |
| `TEST-206.12` | 206–210 | بلوک | `۸) خود صفحه: جای درست قرارگیری` | 0 | ⬜ |
| `TEST-206.13` | 211–257 | بلوک | `۹) بنر «تغییرات این به‌روزرسانی» (مستقل از ۱.۶۳)` | 0 | ⬜ |
| `TEST-206.14` | 258–267 | بلوک | `escape امنیتی بنر` | 0 | ⬜ |
| `TEST-206.15` | 268–281 | بلوک | `سیم‌کشی در صفحه` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-207</b> — <code>tests/test_session_boot_static.py</code> (143 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-207.01` | 1–49 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-207.02` | 50–53 | بلوک | `main` | 0 | ⬜ |
| `TEST-207.03` | 54–143 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-208</b> — <code>tests/test_session_csrf_hardening_static.py</code> (189 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-208.01` | 1–25 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-208.02` | 26–30 | بلوک | `read` | 0 | ⬜ |
| `TEST-208.03` | 31–42 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-208.04` | 43–51 | بلوک | `csrf_token_code` | 0 | ⬜ |
| `TEST-208.05` | 52–63 | بلوک | `test_csrf_token_never_adopts_a_value_supplied_by_t` | 0 | ⬜ |
| `TEST-208.06` | 64–71 | بلوک | `test_csrf_token_is_generated_with_a_cryptographic_` | 0 | ⬜ |
| `TEST-208.07` | 72–91 | بلوک | `test_csrf_token_is_seeded_while_the_session_is_sti` | 0 | ⬜ |
| `TEST-208.08` | 92–98 | بلوک | `test_legacy_csrf_cookie_is_actively_cleared` | 0 | ⬜ |
| `TEST-208.09` | 99–108 | بلوک | `test_require_csrf_still_compares_in_constant_time` | 0 | ⬜ |
| `TEST-208.10` | 109–122 | بلوک | `test_session_idle_timeout_exists_and_is_configurab` | 0 | ⬜ |
| `TEST-208.11` | 123–136 | بلوک | `test_idle_timeout_is_wired_into_bootstrap_after_co` | 0 | ⬜ |
| `TEST-208.12` | 137–146 | بلوک | `test_idle_timeout_only_affects_logged_in_sessions` | 0 | ⬜ |
| `TEST-208.13` | 147–155 | بلوک | `test_idle_timeout_can_be_switched_off_without_edit` | 0 | ⬜ |
| `TEST-208.14` | 156–167 | بلوک | `test_session_cookie_keeps_its_hardening_flags` | 0 | ⬜ |
| `TEST-208.15` | 168–173 | بلوک | `test_dead_duplicate_auth_layer_is_gone` | 0 | ⬜ |
| `TEST-208.16` | 174–189 | بلوک | `test_no_php_file_references_the_removed_app_namesp` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-209</b> — <code>tests/test_skill_manifest_static.py</code> (196 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-209.01` | 1–51 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-209.02` | 52–67 | بلوک | `sections` | 0 | ⬜ |
| `TEST-209.03` | 68–79 | بلوک | `foreign_characters` | 0 | ⬜ |
| `TEST-209.04` | 80–83 | بلوک | `main` | 0 | ⬜ |
| `TEST-209.05` | 84–89 | بلوک | `check` | 0 | ⬜ |
| `TEST-209.06` | 90–103 | بلوک | `وجود فایلها` | 0 | ⬜ |
| `TEST-209.07` | 104–118 | بلوک | `شمارش و پیوستگی شناسهها` | 0 | ⬜ |
| `TEST-209.08` | 119–132 | بلوک | `ساختار تکتک مهارتها` | 0 | ⬜ |
| `TEST-209.09` | 133–143 | بلوک | `جدول مانیفست` | 0 | ⬜ |
| `TEST-209.10` | 144–157 | بلوک | `صداقت دربارهٔ حافظه` | 0 | ⬜ |
| `TEST-209.11` | 158–166 | بلوک | `گردشکار` | 0 | ⬜ |
| `TEST-209.12` | 167–170 | بلوک | `خودآزمون، گزارش و قواعد` | 0 | ⬜ |
| `TEST-209.13` | 171–176 | بلوک | `پیوند AGENTS.md` | 0 | ⬜ |
| `TEST-209.14` | 177–181 | بلوک | `آلودگی خط نوشتاری` | 0 | ⬜ |
| `TEST-209.15` | 182–196 | بلوک | `نتیجه` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-210</b> — <code>tests/test_startup_performance_static.py</code> (51 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-210.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-210.02` | 6–51 | بلوک | `read` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-211</b> — <code>tests/test_system_health_accuracy_static.py</code> (99 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-211.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-211.02` | 16–33 | بلوک | `test_event_totals_are_independent_of_the_display_l` | 0 | ⬜ |
| `TEST-211.03` | 34–47 | بلوک | `test_export_reports_full_counts_and_discloses_trun` | 0 | ⬜ |
| `TEST-211.04` | 48–58 | بلوک | `test_safe_reference_uses_only_class_basename_line_` | 0 | ⬜ |
| `TEST-211.05` | 59–72 | بلوک | `test_future_exception_logs_include_a_validated_err` | 0 | ⬜ |
| `TEST-211.06` | 73–99 | بلوک | `test_hotfix_package_is_a_valid_upgrade_from_16224` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-212</b> — <code>tests/test_system_health_audit_visibility_static.py</code> (81 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-212.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-212.02` | 16–20 | بلوک | `_checks_body` | 0 | ⬜ |
| `TEST-212.03` | 21–26 | بلوک | `test_audit_status_is_reported_on_the_health_page` | 0 | ⬜ |
| `TEST-212.04` | 27–32 | بلوک | `test_health_page_loads_the_audit_module` | 0 | ⬜ |
| `TEST-212.05` | 33–44 | بلوک | `test_missing_heartbeat_is_not_reported_as_healthy` | 0 | ⬜ |
| `TEST-212.06` | 45–53 | بلوک | `test_stale_audit_is_a_failure_not_a_warning` | 0 | ⬜ |
| `TEST-212.07` | 54–62 | بلوک | `test_structural_problems_outrank_regressions` | 0 | ⬜ |
| `TEST-212.08` | 63–71 | بلوک | `test_probe_failure_is_surfaced_not_swallowed` | 0 | ⬜ |
| `TEST-212.09` | 72–81 | بلوک | `test_baseline_message_does_not_demand_server_acces` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-213</b> — <code>tests/test_system_health_log_button_static.py</code> (39 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-213.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-213.02` | 10–22 | بلوک | `test_current_system_logs_page_shows_safe_export_an` | 0 | ⬜ |
| `TEST-213.03` | 23–39 | بلوک | `test_update_zip_contains_button_in_the_actual_unif` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-214</b> — <code>tests/test_system_health_readability_static.py</code> (63 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-214.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-214.02` | 11–24 | بلوک | `test_health_status_rules_include_warnings_and_basi` | 0 | ⬜ |
| `TEST-214.03` | 25–40 | بلوک | `test_health_ui_and_export_use_plain_language_witho` | 0 | ⬜ |
| `TEST-214.04` | 41–48 | بلوک | `test_system_logs_page_displays_safe_export_and_fri` | 0 | ⬜ |
| `TEST-214.05` | 49–63 | بلوک | `test_16224_package_contains_the_readable_health_an` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-215</b> — <code>tests/test_system_health_safe_export_static.py</code> (63 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-215.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-215.02` | 9–15 | بلوک | `test_safe_export_is_reachable_and_labelled_for_peo` | 0 | ⬜ |
| `TEST-215.03` | 16–28 | بلوک | `test_export_omits_user_linked_and_raw_log_fields` | 0 | ⬜ |
| `TEST-215.04` | 29–42 | بلوک | `test_humanizer_uses_safe_categories_and_common_mas` | 0 | ⬜ |
| `TEST-215.05` | 43–48 | بلوک | `test_health_warning_is_explained_without_claiming_` | 0 | ⬜ |
| `TEST-215.06` | 49–63 | بلوک | `test_published_update_contains_the_readable_safe_e` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-216</b> — <code>tests/test_system_map_blocks.py</code> (347 خط، 34 آدرس، بازبینی‌شده 0/34)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-216.01` | 1–32 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-216.02` | 33–43 | بلوک | `_tracked_production_files` | 0 | ⬜ |
| `TEST-216.03` | 44–59 | بلوک | `fingerprint` | 0 | ⬜ |
| `TEST-216.04` | 60–67 | بلوک | `index` | 0 | ⬜ |
| `TEST-216.05` | 68–74 | بلوک | `sources` | 0 | ⬜ |
| `TEST-216.06` | 75–76 | بلوک | `-` | 0 | ⬜ |
| `TEST-216.07` | 77–79 | بلوک | `-` | 0 | ⬜ |
| `TEST-216.08` | 80–89 | بلوک | `test_every_production_file_is_indexed` | 0 | ⬜ |
| `TEST-216.09` | 90–96 | بلوک | `test_index_has_no_phantom_files` | 0 | ⬜ |
| `TEST-216.10` | 97–102 | بلوک | `test_file_ids_are_unique` | 0 | ⬜ |
| `TEST-216.11` | 103–113 | بلوک | `test_block_ids_are_unique` | 0 | ⬜ |
| `TEST-216.12` | 114–115 | بلوک | `-` | 0 | ⬜ |
| `TEST-216.13` | 116–118 | بلوک | `-` | 0 | ⬜ |
| `TEST-216.14` | 119–132 | بلوک | `test_file_fingerprints_are_current` | 0 | ⬜ |
| `TEST-216.15` | 133–149 | بلوک | `test_block_fingerprints_are_current` | 0 | ⬜ |
| `TEST-216.16` | 150–161 | بلوک | `test_block_ranges_are_inside_the_file` | 0 | ⬜ |
| `TEST-216.17` | 162–163 | بلوک | `-` | 0 | ⬜ |
| `TEST-216.18` | 164–166 | بلوک | `-` | 0 | ⬜ |
| `TEST-216.19` | 167–210 | بلوک | `test_every_nonblank_line_is_covered_exactly_once` | 0 | ⬜ |
| `TEST-216.20` | 211–233 | بلوک | `test_symbol_blocks_end_on_a_closing_brace` | 0 | ⬜ |
| `TEST-216.21` | 234–235 | بلوک | `-` | 0 | ⬜ |
| `TEST-216.22` | 236–238 | بلوک | `-` | 0 | ⬜ |
| `TEST-216.23` | 239–244 | بلوک | `test_ledger_exists_and_is_valid_json` | 0 | ⬜ |
| `TEST-216.24` | 245–254 | بلوک | `test_ledger_entries_point_at_real_addresses` | 0 | ⬜ |
| `TEST-216.25` | 255–271 | بلوک | `test_ledger_flags_blocks_changed_since_review` | 0 | ⬜ |
| `TEST-216.26` | 272–286 | بلوک | `test_human_readable_map_is_in_sync` | 0 | ⬜ |
| `TEST-216.27` | 287–288 | بلوک | `-` | 0 | ⬜ |
| `TEST-216.28` | 289–294 | بلوک | `-` | 0 | ⬜ |
| `TEST-216.29` | 295–299 | بلوک | `topics` | 0 | ⬜ |
| `TEST-216.30` | 300–312 | بلوک | `test_every_page_of_the_system_has_a_topic` | 0 | ⬜ |
| `TEST-216.31` | 313–320 | بلوک | `test_every_permission_has_a_topic` | 0 | ⬜ |
| `TEST-216.32` | 321–328 | بلوک | `test_every_topic_resolves_to_at_least_one_address` | 0 | ⬜ |
| `TEST-216.33` | 329–339 | بلوک | `test_topic_addresses_exist_in_the_block_index` | 0 | ⬜ |
| `TEST-216.34` | 340–347 | بلوک | `test_boot_file_points_agents_at_the_map` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-217</b> — <code>tests/test_system_map_static.py</code> (172 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-217.01` | 1–33 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-217.02` | 34–38 | بلوک | `load` | 0 | ⬜ |
| `TEST-217.03` | 39–42 | بلوک | `main` | 0 | ⬜ |
| `TEST-217.04` | 43–156 | بلوک | `check` | 0 | ⬜ |
| `TEST-217.05` | 157–172 | بلوک | `نتیجه` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-218</b> — <code>tests/test_system_update_archive_paths.php</code> (41 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-218.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 1 | ⬜ |
| `TEST-218.02` | 6–6 | تابع | `db` | 0 | ⬜ |
| `TEST-218.03` | 7–7 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-218.04` | 8–8 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-218.05` | 9–9 | تابع | `cfg` | 0 | ⬜ |
| `TEST-218.06` | 10–41 | ناحیه | `بلوک رویه‌ای 10-41` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-219</b> — <code>tests/test_system_update_archive_zip.php</code> (164 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-219.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `TEST-219.02` | 6–6 | تابع | `db` | 0 | ⬜ |
| `TEST-219.03` | 7–7 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-219.04` | 8–8 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-219.05` | 9–9 | تابع | `cfg` | 0 | ⬜ |
| `TEST-219.06` | 10–11 | ناحیه | `بلوک رویه‌ای 10-11` | 1 | ⬜ |
| `TEST-219.07` | 12–12 | ناحیه | `The fixture package version tracks the repository ` | 0 | ⬜ |
| `TEST-219.08` | 13–20 | ناحیه | `a frozen literal goes stale the moment main moves ` | 0 | ⬜ |
| `TEST-219.09` | 21–43 | تابع | `make_test_zip` | 5 | ⬜ |
| `TEST-219.10` | 45–63 | تابع | `assert_zip_rejected` | 5 | ⬜ |
| `TEST-219.11` | 64–90 | ناحیه | `بلوک رویه‌ای 64-90` | 5 | ⬜ |
| `TEST-219.12` | 91–100 | تابع | `package_test_rejects` | 0 | ⬜ |
| `TEST-219.13` | 101–164 | ناحیه | `بلوک رویه‌ای 101-164` | **8** | ⬜ |

</details>

<details>
<summary><b>TEST-220</b> — <code>tests/test_system_update_migration_failure_gates.php</code> (232 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-220.01` | 1–47 | ناحیه | `بلوک رویه‌ای 1-47` | 5 | ⬜ |
| `TEST-220.02` | 48–51 | تابع | `db` | 0 | ⬜ |
| `TEST-220.03` | 53–56 | تابع | `cfg` | 0 | ⬜ |
| `TEST-220.04` | 58–61 | تابع | `backup_mysql_binary` | 0 | ⬜ |
| `TEST-220.05` | 63–66 | تابع | `create_backup_bundle` | 0 | ⬜ |
| `TEST-220.06` | 68–86 | تابع | `backup_run_process` | 4 | ⬜ |
| `TEST-220.07` | 88–91 | تابع | `save_audit_required` | 0 | ⬜ |
| `TEST-220.08` | 92–95 | ناحیه | `بلوک رویه‌ای 92-95` | 1 | ⬜ |
| `TEST-220.09` | 96–102 | تابع | `rc_update_check` | 0 | ⬜ |
| `TEST-220.10` | 104–127 | تابع | `rc_update_package` | 1 | ⬜ |
| `TEST-220.11` | 129–137 | تابع | `rc_update_upload` | 0 | ⬜ |
| `TEST-220.12` | 139–146 | تابع | `rc_update_table_exists` | 0 | ⬜ |
| `TEST-220.13` | 147–232 | ناحیه | `بلوک رویه‌ای 147-232` | **17** | ⬜ |

</details>

<details>
<summary><b>TEST-221</b> — <code>tests/test_system_update_migration_registry.php</code> (68 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-221.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `TEST-221.02` | 7–30 | کلاس | `MigrationRegistryFakeStatement` | 0 | ⬜ |
| `TEST-221.03` | 32–41 | کلاس | `MigrationRegistryFakeDb` | 0 | ⬜ |
| `TEST-221.04` | 42–44 | ناحیه | `بلوک رویه‌ای 42-44` | 0 | ⬜ |
| `TEST-221.05` | 45–48 | تابع | `db` | 0 | ⬜ |
| `TEST-221.06` | 49–49 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-221.07` | 50–50 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-221.08` | 51–51 | تابع | `cfg` | 0 | ⬜ |
| `TEST-221.09` | 52–68 | ناحیه | `بلوک رویه‌ای 52-68` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-222</b> — <code>tests/test_system_update_requires_version.php</code> (96 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-222.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `TEST-222.02` | 20–20 | ناحیه | `system-update.php در سطح فایل فقط تابع تعریف می‌کن` | 1 | ⬜ |
| `TEST-222.03` | 21–24 | تابع | `db` | 0 | ⬜ |
| `TEST-222.04` | 25–27 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-222.05` | 28–31 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-222.06` | 32–35 | تابع | `cfg` | 0 | ⬜ |
| `TEST-222.07` | 36–39 | ناحیه | `بلوک رویه‌ای 36-39` | 1 | ⬜ |
| `TEST-222.08` | 40–47 | تابع | `expect` | 0 | ⬜ |
| `TEST-222.09` | 49–53 | تابع | `allowed` | 0 | ⬜ |
| `TEST-222.10` | 55–59 | ناحیه | `پیش‌نیاز برآورده` | 0 | ⬜ |
| `TEST-222.11` | 60–64 | ناحیه | `پیش‌نیاز برآورده نیست` | 0 | ⬜ |
| `TEST-222.12` | 65–71 | ناحیه | `متن خطا باید قابل اقدام باشد` | 0 | ⬜ |
| `TEST-222.13` | 72–78 | ناحیه | `سازگاری با بسته‌های قدیمی` | 0 | ⬜ |
| `TEST-222.14` | 79–83 | ناحیه | `درهای خروج` | 0 | ⬜ |
| `TEST-222.15` | 84–87 | ناحیه | `نسخه‌های با تعداد جزء متفاوت` | 0 | ⬜ |
| `TEST-222.16` | 88–96 | ناحیه | `بستهٔ واقعی این نسخه` | 3 | ⬜ |

</details>

<details>
<summary><b>TEST-223</b> — <code>tests/test_technical_profile_form_refresh_static.py</code> (70 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-223.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-223.02` | 16–39 | بلوک | `test_current_profile_module_has_complete_form_and_` | 0 | ⬜ |
| `TEST-223.03` | 40–70 | بلوک | `test_form_hotfix_packages_the_renderer_styles_and_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-224</b> — <code>tests/test_ticket_auto_assignment_static.py</code> (24 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-224.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-224.02` | 7–7 | بلوک | `TicketAutoAssignmentStaticTests` | 0 | ⬜ |
| `TEST-224.03` | 8–16 | بلوک | `test_regular_managers_are_limited_to_the_ticket_de` | 0 | ⬜ |
| `TEST-224.04` | 17–24 | بلوک | `test_ticket_creation_passes_its_resolved_departmen` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-225</b> — <code>tests/test_ticket_cancellation_static.py</code> (176 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-225.01` | 1–23 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-225.02` | 24–146 | بلوک | `check` | 7 | ⬜ |
| `TEST-225.03` | 147–176 | بلوک | `strip_php_comments` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-226</b> — <code>tests/test_ticket_lifecycle_guide_static.py</code> (42 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-226.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-226.02` | 7–8 | بلوک | `TicketLifecycleGuideStaticTests` | 0 | ⬜ |
| `TEST-226.03` | 9–11 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-226.04` | 12–16 | بلوک | `test_role_guide_is_linked_from_the_toc` | 0 | ⬜ |
| `TEST-226.05` | 17–34 | بلوک | `test_lifecycle_explains_roles_and_closure_path` | 0 | ⬜ |
| `TEST-226.06` | 35–42 | بلوک | `test_resolved_ticket_instructions_use_the_actual_r` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-227</b> — <code>tests/test_ticket_number_autofill_unit.php</code> (159 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-227.01` | 1–29 | ناحیه | `بلوک رویه‌ای 1-29` | 0 | ⬜ |
| `TEST-227.02` | 30–40 | تابع | `tn_missing_rows` | 0 | ⬜ |
| `TEST-227.03` | 42–105 | کلاس | `TnStatement` | 1 | ⬜ |
| `TEST-227.04` | 107–113 | کلاس | `TnDb` | 4 | ⬜ |
| `TEST-227.05` | 115–115 | تابع | `db` | 0 | ⬜ |
| `TEST-227.06` | 116–116 | تابع | `setting` | 0 | ⬜ |
| `TEST-227.07` | 117–117 | تابع | `save_setting` | 0 | ⬜ |
| `TEST-227.08` | 118–118 | تابع | `db_in_transaction` | 0 | ⬜ |
| `TEST-227.09` | 119–119 | تابع | `cfg` | 0 | ⬜ |
| `TEST-227.10` | 120–122 | ناحیه | `بلوک رویه‌ای 120-122` | 0 | ⬜ |
| `TEST-227.11` | 123–127 | تابع | `tn_check` | 0 | ⬜ |
| `TEST-227.12` | 129–132 | ناحیه | `۱) کف شمارنده از بزرگ‌ترین شمارهٔ موجود خوانده می‌` | 0 | ⬜ |
| `TEST-227.13` | 133–147 | ناحیه | `۲) ترمیم کامل بدون برخورد یکتایی` | 0 | ⬜ |
| `TEST-227.14` | 148–150 | ناحیه | `۳) هر ردیف دقیقاً یک تلاش: یعنی هیچ برخوردی رخ ندا` | 1 | ⬜ |
| `TEST-227.15` | 151–153 | ناحیه | `۴) نشانگر ثبت می‌شود تا اسکن در هر بارگذاری صفحه ت` | 0 | ⬜ |
| `TEST-227.16` | 154–159 | ناحیه | `۵) شمارنده برای تولید شمارهٔ بعدی به‌روز می‌شود` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-228</b> — <code>tests/test_ticket_number_repair_static.py</code> (151 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-228.01` | 1–16 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-228.02` | 17–21 | بلوک | `read` | 0 | ⬜ |
| `TEST-228.03` | 22–28 | بلوک | `autofill_body` | 0 | ⬜ |
| `TEST-228.04` | 29–39 | بلوک | `test_autofill_seeds_counter_from_existing_maximum` | 0 | ⬜ |
| `TEST-228.05` | 40–51 | بلوک | `test_sequence_floor_consults_both_counter_and_exis` | 0 | ⬜ |
| `TEST-228.06` | 52–59 | بلوک | `test_autofill_isolates_each_row_so_one_failure_doe` | 0 | ⬜ |
| `TEST-228.07` | 60–65 | بلوک | `test_autofill_reads_in_batches_and_cannot_loop_for` | 0 | ⬜ |
| `TEST-228.08` | 66–76 | بلوک | `test_completion_marker_is_not_trapped_in_the_empty` | 0 | ⬜ |
| `TEST-228.09` | 77–85 | بلوک | `test_counter_is_persisted_back_to_the_sequence_tab` | 0 | ⬜ |
| `TEST-228.10` | 86–98 | بلوک | `test_fallback_number_is_not_a_fake_lookalike` | 0 | ⬜ |
| `TEST-228.11` | 99–109 | بلوک | `test_chatbot_still_recognises_the_internal_referen` | 0 | ⬜ |
| `TEST-228.12` | 110–126 | بلوک | `test_list_pages_prime_the_number_cache_instead_of_` | 0 | ⬜ |
| `TEST-228.13` | 127–137 | بلوک | `test_cache_prime_helper_exists_and_tolerates_bad_r` | 0 | ⬜ |
| `TEST-228.14` | 138–144 | بلوک | `test_bulk_update_has_a_selection_cap` | 0 | ⬜ |
| `TEST-228.15` | 145–151 | بلوک | `test_bulk_update_reports_tickets_that_were_already` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-229</b> — <code>tests/test_ticket_routing_unit.php</code> (187 خط، 38 آدرس، بازبینی‌شده 0/38)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-229.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-229.02` | 4–5 | ناحیه | `Offline behavioural tests: no real configuration, ` | 0 | ⬜ |
| `TEST-229.03` | 6–9 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `TEST-229.04` | 10–10 | تابع | `is_staff_role` | 0 | ⬜ |
| `TEST-229.05` | 11–11 | تابع | `is_assignable_role` | 0 | ⬜ |
| `TEST-229.06` | 12–12 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `TEST-229.07` | 13–13 | تابع | `is_it_agent` | 0 | ⬜ |
| `TEST-229.08` | 14–16 | تابع | `user_can` | 0 | ⬜ |
| `TEST-229.09` | 17–17 | تابع | `permission_roles` | 0 | ⬜ |
| `TEST-229.10` | 18–18 | تابع | `permission_table_ready` | 0 | ⬜ |
| `TEST-229.11` | 19–19 | تابع | `role_permissions_custom` | 0 | ⬜ |
| `TEST-229.12` | 20–20 | تابع | `role_permission_effective_codes` | 0 | ⬜ |
| `TEST-229.13` | 21–34 | کلاس | `FakeTicketStatement` | 0 | ⬜ |
| `TEST-229.14` | 35–38 | کلاس | `FakeTicketDatabase` | 0 | ⬜ |
| `TEST-229.15` | 39–39 | تابع | `db` | 0 | ⬜ |
| `TEST-229.16` | 40–41 | ناحیه | `بلوک رویه‌ای 40-41` | 0 | ⬜ |
| `TEST-229.17` | 42–45 | تابع | `check_ticket` | 0 | ⬜ |
| `TEST-229.18` | 46–102 | ناحیه | `بلوک رویه‌ای 46-102` | 0 | ⬜ |
| `TEST-229.19` | 103–103 | ناحیه | `تفکیک وظایف و سه نما (۱.۶۲.۸۴)` | 0 | ⬜ |
| `TEST-229.20` | 104–108 | ناحیه | `سناریو: مدیر حوزه (کاربر ۴، نقش manager، همان گروه` | 0 | ⬜ |
| `TEST-229.21` | 109–111 | ناحیه | `همان مدیر روی درخواست دیگران همچنان سرپرست است.` | 0 | ⬜ |
| `TEST-229.22` | 112–114 | ناحیه | `کارشناس مسئول: نمای کارشناس، نه سرپرست.` | 0 | ⬜ |
| `TEST-229.23` | 115–117 | ناحیه | `ثبت‌کنندهٔ عادی.` | 0 | ⬜ |
| `TEST-229.24` | 118–134 | ناحیه | `استثنای مهم: کسی که هم ثبت‌کننده و هم کارشناس مسئو` | 0 | ⬜ |
| `TEST-229.25` | 135–135 | ناحیه | `تفکیک وظایف در لحظهٔ ارجاع و تقویم کاری (بستهٔ ۶ —` | 0 | ⬜ |
| `TEST-229.26` | 136–136 | ناحیه | `بستهٔ ۵ نمای پس از ارجاع را بست؛ اینجا خودِ لحظهٔ ` | 0 | ⬜ |
| `TEST-229.27` | 137–138 | ناحیه | `$users[2] کارشناس پشتیبانی فعال و واجد شرایط است (` | 0 | ⬜ |
| `TEST-229.28` | 139–142 | ناحیه | `۱) کارشناس روی درخواست دیگری همچنان واجد شرایط است` | 0 | ⬜ |
| `TEST-229.29` | 143–146 | ناحیه | `۲) همان کارشناس روی درخواستی که خودش ثبت کرده، دیگ` | 0 | ⬜ |
| `TEST-229.30` | 147–163 | ناحیه | `۳) ادمین اصلی هم مستثنا نیست؛ میان‌بُر نقش نباید گ` | 0 | ⬜ |
| `TEST-229.31` | 164–164 | ناحیه | `۴) درخواست بدون ثبت‌کنندهٔ مشخص (requester_id = 0)` | 0 | ⬜ |
| `TEST-229.32` | 165–168 | ناحیه | `choose_handling_agent() در حالت پیش‌فرض دقیقاً همی` | 0 | ⬜ |
| `TEST-229.33` | 169–169 | ناحیه | `۵) سازگاری با بستهٔ ۵: کسی که هم ثبت‌کننده و هم مس` | 0 | ⬜ |
| `TEST-229.34` | 170–174 | ناحیه | `از دست نمی‌دهد. گارد تازه فقط ارجاعِ تازه را می‌بن` | 0 | ⬜ |
| `TEST-229.35` | 175–175 | ناحیه | `۶) دریچهٔ اضطراری واحد تک‌نفره: وقتی security.allo` | 0 | ⬜ |
| `TEST-229.36` | 176–176 | ناحیه | `همان کاربر دوباره واجد شرایط می‌شود. cfg() از پیکر` | 0 | ⬜ |
| `TEST-229.37` | 177–182 | ناحیه | `چون کلید پیش‌فرض تعریف نشده، همهٔ بررسی‌های بالاتر` | 0 | ⬜ |
| `TEST-229.38` | 183–186 | تابع | `cfg` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-230</b> — <code>tests/test_ticket_scope_sql_node.js</code> (22 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-230.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-230.02` | 6–6 | بلوک | `user_is_primary_admin` | 0 | ⬜ |
| `TEST-230.03` | 7–7 | بلوک | `is_global_ticket_role` | 0 | ⬜ |
| `TEST-230.04` | 8–8 | بلوک | `user_can` | 0 | ⬜ |
| `TEST-230.05` | 9–9 | بلوک | `is_it_agent` | 0 | ⬜ |
| `TEST-230.06` | 10–10 | بلوک | `S` | 0 | ⬜ |
| `TEST-230.07` | 11–11 | بلوک | `D` | 0 | ⬜ |
| `TEST-230.08` | 12–22 | بلوک | `db` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-231</b> — <code>tests/test_ticket_sla_unit.php</code> (90 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-231.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-231.02` | 4–5 | ناحیه | `Tests use an in-memory event stub, never the produ` | 0 | ⬜ |
| `TEST-231.03` | 6–6 | تابع | `cfg` | 0 | ⬜ |
| `TEST-231.04` | 7–7 | تابع | `ticket_is_waiting_status` | 0 | ⬜ |
| `TEST-231.05` | 8–8 | تابع | `is_sla_holiday` | 0 | ⬜ |
| `TEST-231.06` | 9–13 | کلاس | `SlaFakeStatement` | 0 | ⬜ |
| `TEST-231.07` | 14–16 | کلاس | `SlaFakeDb` | 0 | ⬜ |
| `TEST-231.08` | 17–17 | تابع | `db` | 0 | ⬜ |
| `TEST-231.09` | 18–18 | ناحیه | `بلوک رویه‌ای 18-18` | 0 | ⬜ |
| `TEST-231.10` | 19–22 | تابع | `sla_check` | 0 | ⬜ |
| `TEST-231.11` | 23–90 | ناحیه | `بلوک رویه‌ای 23-90` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-232</b> — <code>tests/test_ticket_three_views_static.py</code> (159 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-232.01` | 1–30 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-232.02` | 31–35 | بلوک | `read` | 0 | ⬜ |
| `TEST-232.03` | 36–42 | بلوک | `php_function` | 0 | ⬜ |
| `TEST-232.04` | 43–50 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-232.05` | 51–64 | بلوک | `test_handler_level_denies_supervisor_authority_ove` | 0 | ⬜ |
| `TEST-232.06` | 65–78 | بلوک | `test_a_requester_who_is_also_the_assigned_agent_ca` | 0 | ⬜ |
| `TEST-232.07` | 79–89 | بلوک | `test_reply_guard_keeps_its_explicit_requester_chec` | 0 | ⬜ |
| `TEST-232.08` | 90–102 | بلوک | `test_a_single_function_answers_which_view_to_rende` | 0 | ⬜ |
| `TEST-232.09` | 103–114 | بلوک | `test_the_ticket_page_uses_the_single_view_function` | 0 | ⬜ |
| `TEST-232.10` | 115–127 | بلوک | `test_internal_notes_are_hidden_from_the_requester_` | 0 | ⬜ |
| `TEST-232.11` | 128–136 | بلوک | `test_message_list_no_longer_decides_by_global_role` | 0 | ⬜ |
| `TEST-232.12` | 137–150 | بلوک | `test_internal_attachment_download_uses_the_same_ru` | 0 | ⬜ |
| `TEST-232.13` | 151–159 | بلوک | `test_scope_isolation_rules_are_untouched` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-233</b> — <code>tests/test_ticket_tree_visibility_unit.php</code> (101 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-233.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TEST-233.02` | 4–4 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `TEST-233.03` | 5–5 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `TEST-233.04` | 6–6 | تابع | `is_it_agent` | 0 | ⬜ |
| `TEST-233.05` | 7–7 | تابع | `user_can` | 0 | ⬜ |
| `TEST-233.06` | 8–8 | تابع | `is_assignable_role` | 0 | ⬜ |
| `TEST-233.07` | 9–18 | کلاس | `ScopeStmt` | 0 | ⬜ |
| `TEST-233.08` | 19–22 | کلاس | `ScopeDB` | 0 | ⬜ |
| `TEST-233.09` | 23–23 | تابع | `db` | 0 | ⬜ |
| `TEST-233.10` | 24–24 | ناحیه | `بلوک رویه‌ای 24-24` | 0 | ⬜ |
| `TEST-233.11` | 25–25 | تابع | `ok` | 0 | ⬜ |
| `TEST-233.12` | 26–35 | ناحیه | `بلوک رویه‌ای 26-35` | 0 | ⬜ |
| `TEST-233.13` | 36–46 | ناحیه | `مرز دوم: درخواست منتسب به زیردرخت (requesting_unit` | 0 | ⬜ |
| `TEST-233.14` | 47–61 | ناحیه | `مدیر واحدِ صرف (بدون handling_unit_code): مرزِ اخت` | 0 | ⬜ |
| `TEST-233.15` | 62–75 | ناحیه | `مرز دوم: درخواست منتسب به زیردرخت چارت (requesting` | 0 | ⬜ |
| `TEST-233.16` | 76–76 | ناحیه | `مورد ۳: نقش جدید اولویت دارد؛ چارت دیگر مرز او نیس` | 0 | ⬜ |
| `TEST-233.17` | 77–84 | ناحیه | `مدیر تولید در چارت است ولی بعداً «ادمین اصلی» (adm` | 0 | ⬜ |
| `TEST-233.18` | 85–89 | ناحیه | `همان مدیر، ولی با نقش اصلی primary_admin به‌جای پر` | 0 | ⬜ |
| `TEST-233.19` | 90–90 | ناحیه | `رئیس چارت که فقط دسترسی موردی (مثلا از گروه‌ها) گر` | 0 | ⬜ |
| `TEST-233.20` | 91–94 | ناحیه | `اولویت با خودِ نقش است، نه پرمیشن‌های گرنت‌شده.` | 0 | ⬜ |
| `TEST-233.21` | 95–101 | ناحیه | `اگر نقش واقعاً supervisor باشد، اولویت با نقش است ` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-234</b> — <code>tests/test_ticket_workflow_safety_static.py</code> (109 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-234.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-234.02` | 7–10 | بلوک | `source` | 0 | ⬜ |
| `TEST-234.03` | 11–14 | بلوک | `section` | 0 | ⬜ |
| `TEST-234.04` | 15–15 | بلوک | `TicketWorkflowSafetyTests` | 0 | ⬜ |
| `TEST-234.05` | 16–26 | بلوک | `test_notification_candidates_are_checked_for_actua` | 0 | ⬜ |
| `TEST-234.06` | 27–40 | بلوک | `test_assignment_checks_existing_assignee_and_role_` | 1 | ⬜ |
| `TEST-234.07` | 41–52 | بلوک | `test_requester_and_nonhandler_reply_guards` | 2 | ⬜ |
| `TEST-234.08` | 53–65 | بلوک | `test_concurrent_rating_and_auto_close_revalidate_u` | 1 | ⬜ |
| `TEST-234.09` | 66–71 | بلوک | `test_claim_uses_full_assignee_eligibility` | 0 | ⬜ |
| `TEST-234.10` | 72–78 | بلوک | `test_supervisor_actions_check_ticket_visibility` | 0 | ⬜ |
| `TEST-234.11` | 79–86 | بلوک | `test_autoclose_candidates_do_not_starve_on_current` | 0 | ⬜ |
| `TEST-234.12` | 87–92 | بلوک | `test_reopen_sla_cycle_is_used_on_priority_change` | 0 | ⬜ |
| `TEST-234.13` | 93–109 | بلوک | `test_direct_cancel_and_bulk_defend_stale_assignmen` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-235</b> — <code>tests/test_traffic_69_link_static.py</code> (25 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-235.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-235.02` | 5–14 | بلوک | `test_migration_is_additive_idempotent_and_does_not` | 0 | ⬜ |
| `TEST-235.03` | 15–25 | بلوک | `test_shared_preview_export_and_real_destination_so` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-236</b> — <code>tests/test_traffic_70_upgrade_static.py</code> (20 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-236.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-236.02` | 5–12 | بلوک | `test_cumulative_migration_only_backfills_when_019_` | 0 | ⬜ |
| `TEST-236.03` | 13–20 | بلوک | `test_report_has_cache_busted_popup_assets` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-237</b> — <code>tests/test_traffic_destination_approver_static.py</code> (54 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-237.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-237.02` | 7–13 | بلوک | `test_destination_approver_is_stored_in_all_schema_` | 0 | ⬜ |
| `TEST-237.03` | 14–31 | بلوک | `test_destination_admin_can_create_edit_and_resolve` | 2 | ⬜ |
| `TEST-237.04` | 32–44 | بلوک | `test_approver_autofills_for_new_and_edit_forms_and` | 0 | ⬜ |
| `TEST-237.05` | 45–54 | بلوک | `test_release_manifest_and_download_page_list_new_d` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-238</b> — <code>tests/test_traffic_destination_popup_browser.js</code> (66 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-238.01` | 1–28 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-238.02` | 29–66 | بلوک | `geometry` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-239</b> — <code>tests/test_traffic_edit_history_static.py</code> (71 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-239.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-239.02` | 6–14 | بلوک | `test_traffic_edit_form_includes_visit_date_and_bot` | 0 | ⬜ |
| `TEST-239.03` | 15–25 | بلوک | `test_update_and_exit_changes_are_written_transacti` | 0 | ⬜ |
| `TEST-239.04` | 26–35 | بلوک | `test_history_schema_exists_in_migration_and_fresh_` | 0 | ⬜ |
| `TEST-239.05` | 36–42 | بلوک | `test_food_print_page_treats_layout_design_as_optio` | 0 | ⬜ |
| `TEST-239.06` | 43–50 | بلوک | `test_shared_header_does_not_fail_when_layout_desig` | 0 | ⬜ |
| `TEST-239.07` | 51–58 | بلوک | `test_food_panel_skips_invalid_empty_search_replace` | 0 | ⬜ |
| `TEST-239.08` | 59–71 | بلوک | `test_traffic_edit_times_use_unambiguous_24_hour_te` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-240</b> — <code>tests/test_traffic_form_render_node.js</code> (16 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-240.01` | 1–16 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-241</b> — <code>tests/test_traffic_import_static.py</code> (72 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-241.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-241.02` | 7–10 | بلوک | `read` | 0 | ⬜ |
| `TEST-241.03` | 11–11 | بلوک | `TrafficImportStaticTest` | 0 | ⬜ |
| `TEST-241.04` | 12–25 | بلوک | `test_import_page_is_direct_only_and_requires_exist` | 0 | ⬜ |
| `TEST-241.05` | 26–43 | بلوک | `test_post_import_is_csrf_protected_and_confirmatio` | 2 | ⬜ |
| `TEST-241.06` | 44–58 | بلوک | `test_parser_limits_duplicate_protection_and_privac` | 0 | ⬜ |
| `TEST-241.07` | 59–72 | بلوک | `test_help_and_chatbot_surface_the_hidden_workflow` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-242</b> — <code>tests/test_traffic_live_node.js</code> (41 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-242.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-242.02` | 14–41 | بلوک | `reply` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-243</b> — <code>tests/test_traffic_live_report_unit.php</code> (74 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-243.01` | 1–8 | ناحیه | `بلوک رویه‌ای 1-8` | 0 | ⬜ |
| `TEST-243.02` | 9–9 | تابع | `db` | 0 | ⬜ |
| `TEST-243.03` | 10–10 | تابع | `save_audit` | 0 | ⬜ |
| `TEST-243.04` | 11–11 | تابع | `save_audit_required` | 0 | ⬜ |
| `TEST-243.05` | 12–12 | تابع | `log_sort_order` | 0 | ⬜ |
| `TEST-243.06` | 13–13 | تابع | `e` | 0 | ⬜ |
| `TEST-243.07` | 14–14 | تابع | `user_can` | 0 | ⬜ |
| `TEST-243.08` | 15–15 | تابع | `valid_iranian_national_code` | 0 | ⬜ |
| `TEST-243.09` | 16–16 | تابع | `csrf_field` | 0 | ⬜ |
| `TEST-243.10` | 17–17 | کلاس | `ExportDone` | 0 | ⬜ |
| `TEST-243.11` | 18–18 | تابع | `excel_download` | 0 | ⬜ |
| `TEST-243.12` | 19–20 | ناحیه | `بلوک رویه‌ای 19-20` | 0 | ⬜ |
| `TEST-243.13` | 21–21 | تابع | `check` | 0 | ⬜ |
| `TEST-243.14` | 22–74 | ناحیه | `بلوک رویه‌ای 22-74` | **8** | ⬜ |

</details>

<details>
<summary><b>TEST-244</b> — <code>tests/test_traffic_phone_and_inspection_reminder_static.py</code> (55 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-244.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-244.02` | 6–20 | بلوک | `test_traffic_phone_has_fixed_09_and_requires_exact` | 0 | ⬜ |
| `TEST-244.03` | 21–33 | بلوک | `test_inspection_only_reminder_is_scheduled_daily_a` | 0 | ⬜ |
| `TEST-244.04` | 34–41 | بلوک | `test_reminder_visual_is_centered_and_alarm_styled` | 0 | ⬜ |
| `TEST-244.05` | 42–55 | بلوک | `test_only_primary_admin_can_toggle_reminder_below_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-245</b> — <code>tests/test_traffic_report_fields_node.js</code> (35 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-245.01` | 1–35 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |

</details>

<details>
<summary><b>TEST-246</b> — <code>tests/test_transaction_truthfulness_static.py</code> (165 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-246.01` | 1–33 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-246.02` | 34–38 | بلوک | `read` | 0 | ⬜ |
| `TEST-246.03` | 39–49 | بلوک | `strip_php_comments` | 0 | ⬜ |
| `TEST-246.04` | 50–56 | بلوک | `function_body` | 0 | ⬜ |
| `TEST-246.05` | 57–62 | بلوک | `function_code` | 0 | ⬜ |
| `TEST-246.06` | 63–83 | بلوک | `test_commit_returns_true_when_the_work_is_already_` | 0 | ⬜ |
| `TEST-246.07` | 84–90 | بلوک | `test_commit_still_reports_a_genuine_failure_as_fal` | 0 | ⬜ |
| `TEST-246.08` | 91–112 | بلوک | `test_a_strict_commit_helper_exists_and_raises_a_pe` | 0 | ⬜ |
| `TEST-246.09` | 113–121 | بلوک | `test_every_ticket_write_path_uses_the_strict_commi` | 0 | ⬜ |
| `TEST-246.10` | 122–134 | بلوک | `test_strict_commit_calls_name_the_action_for_diagn` | 0 | ⬜ |
| `TEST-246.11` | 135–144 | بلوک | `test_other_modules_keep_their_existing_commit_cont` | 0 | ⬜ |
| `TEST-246.12` | 145–159 | بلوک | `test_auto_assignment_does_not_invent_a_state_the_t` | 0 | ⬜ |
| `TEST-246.13` | 160–165 | بلوک | `test_creation_event_records_the_real_initial_statu` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-247</b> — <code>tests/test_triangle_effect_static.py</code> (102 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-247.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-247.02` | 9–10 | بلوک | `TriangleEffectReleaseTests` | 0 | ⬜ |
| `TEST-247.03` | 11–20 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-247.04` | 21–33 | بلوک | `test_catalog_has_37_valid_non_print_routes` | 0 | ⬜ |
| `TEST-247.05` | 34–51 | بلوک | `test_configuration_keys_match_the_engine` | 0 | ⬜ |
| `TEST-247.06` | 52–59 | بلوک | `test_settings_are_saved_with_existing_permission_a` | 0 | ⬜ |
| `TEST-247.07` | 60–68 | بلوک | `test_engine_respects_reduced_motion_and_has_explic` | 0 | ⬜ |
| `TEST-247.08` | 69–81 | بلوک | `test_interaction_controls_are_wired` | 1 | ⬜ |
| `TEST-247.09` | 82–90 | بلوک | `test_help_and_chatbot_cover_the_feature` | 0 | ⬜ |
| `TEST-247.10` | 91–102 | بلوک | `test_release_manifest_is_migration_free` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-248</b> — <code>tests/test_tts_normalize_static.py</code> (56 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-248.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-248.02` | 13–56 | بلوک | `check` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-249</b> — <code>tests/test_update_manifest_guard_static.py</code> (59 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-249.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-249.02` | 6–13 | بلوک | `test_update_installer_checks_zip_open_and_extracti` | 1 | ⬜ |
| `TEST-249.03` | 14–22 | بلوک | `test_update_installer_rejects_traversal_duplicates` | 1 | ⬜ |
| `TEST-249.04` | 23–29 | بلوک | `test_manifest_file_list_and_optional_sha256_are_ve` | 1 | ⬜ |
| `TEST-249.05` | 30–42 | بلوک | `test_cumulative_update_contains_security_scope_run` | 0 | ⬜ |
| `TEST-249.06` | 43–59 | بلوک | `test_cumulative_update_includes_all_logging_migrat` | 1 | ⬜ |

</details>

<details>
<summary><b>TEST-250</b> — <code>tests/test_user_access_groups_static.py</code> (90 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-250.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-250.02` | 7–8 | بلوک | `UserAccessGroupStaticTests` | 0 | ⬜ |
| `TEST-250.03` | 9–16 | بلوک | `setUpClass` | 0 | ⬜ |
| `TEST-250.04` | 17–30 | بلوک | `test_group_permissions_are_additive_and_primary_ad` | 0 | ⬜ |
| `TEST-250.05` | 31–42 | بلوک | `test_schema_supports_many_groups_per_user_and_casc` | 1 | ⬜ |
| `TEST-250.06` | 43–59 | بلوک | `test_create_update_and_delete_are_primary_admin_on` | 0 | ⬜ |
| `TEST-250.07` | 60–80 | بلوک | `test_settings_ui_reuses_permission_catalog_and_mem` | 0 | ⬜ |
| `TEST-250.08` | 81–90 | بلوک | `test_special_role_constraints_and_documentation_ar` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-251</b> — <code>tests/test_version_source_of_truth_static.py</code> (232 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-251.01` | 1–59 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TEST-251.02` | 60–64 | بلوک | `version_tuple` | 0 | ⬜ |
| `TEST-251.03` | 65–68 | بلوک | `repo_version` | 0 | ⬜ |
| `TEST-251.04` | 69–101 | بلوک | `scan_packages` | 0 | ⬜ |
| `TEST-251.05` | 102–112 | بلوک | `scan_published_packages` | 0 | ⬜ |
| `TEST-251.06` | 113–127 | بلوک | `resolve_repo_path` | 0 | ⬜ |
| `TEST-251.07` | 128–132 | بلوک | `newest_package_for` | 0 | ⬜ |
| `TEST-251.08` | 133–133 | بلوک | `VersionSourceOfTruthTests` | 0 | ⬜ |
| `TEST-251.09` | 134–148 | بلوک | `test_repo_version_is_not_behind_the_newest_publish` | 0 | ⬜ |
| `TEST-251.10` | 149–188 | بلوک | `test_repo_matches_the_newest_package_of_the_curren` | 0 | ⬜ |
| `TEST-251.11` | 189–217 | بلوک | `test_package_manifest_version_matches_its_own_vers` | 0 | ⬜ |
| `TEST-251.12` | 218–232 | بلوک | `test_docs_do_not_advertise_an_older_version_than_v` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-252</b> — <code>tests/test_windows_logging_static.py</code> (78 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-252.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TEST-252.02` | 6–18 | بلوک | `test_windows_logging_acceptance_assets_exist` | 0 | ⬜ |
| `TEST-252.03` | 19–26 | بلوک | `test_acl_verifier_is_read_only_and_strict_mode_is_` | 0 | ⬜ |
| `TEST-252.04` | 27–32 | بلوک | `test_acl_verifier_detects_interactive_and_guest_pr` | 0 | ⬜ |
| `TEST-252.05` | 33–39 | بلوک | `test_web_protection_verifier_preserves_application` | 0 | ⬜ |
| `TEST-252.06` | 40–46 | بلوک | `test_acl_setter_uses_least_privilege_for_service_a` | 0 | ⬜ |
| `TEST-252.07` | 47–53 | بلوک | `test_acl_setter_handles_script_whatif_without_null` | 0 | ⬜ |
| `TEST-252.08` | 54–61 | بلوک | `test_installed_acceptance_script_is_version_pinned` | 0 | ⬜ |
| `TEST-252.09` | 62–78 | بلوک | `test_installed_acceptance_checks_complete_logging_` | 0 | ⬜ |

</details>

<details>
<summary><b>TEST-253</b> — <code>tests/tts_normalize_test.php</code> (189 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TEST-253.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `TEST-253.02` | 14–17 | تابع | `setting` | 0 | ⬜ |
| `TEST-253.03` | 18–19 | ناحیه | `بلوک رویه‌ای 18-19` | 0 | ⬜ |
| `TEST-253.04` | 20–23 | تابع | `valid_choice` | 0 | ⬜ |
| `TEST-253.05` | 24–25 | ناحیه | `بلوک رویه‌ای 24-25` | 0 | ⬜ |
| `TEST-253.06` | 26–28 | تابع | `app_error_log` | 0 | ⬜ |
| `TEST-253.07` | 29–30 | ناحیه | `بلوک رویه‌ای 29-30` | 0 | ⬜ |
| `TEST-253.08` | 31–34 | تابع | `e` | 0 | ⬜ |
| `TEST-253.09` | 35–36 | ناحیه | `بلوک رویه‌ای 35-36` | 0 | ⬜ |
| `TEST-253.10` | 37–40 | تابع | `csrf_field` | 0 | ⬜ |
| `TEST-253.11` | 41–52 | ناحیه | `بلوک رویه‌ای 41-52` | 0 | ⬜ |
| `TEST-253.12` | 53–58 | تابع | `tts_bare` | 0 | ⬜ |
| `TEST-253.13` | 60–70 | تابع | `expect_true` | 0 | ⬜ |
| `TEST-253.14` | 72–75 | تابع | `expect_eq` | 0 | ⬜ |
| `TEST-253.15` | 77–80 | تابع | `expect_contains` | 0 | ⬜ |
| `TEST-253.16` | 82–85 | تابع | `expect_not_contains` | 0 | ⬜ |
| `TEST-253.17` | 86–189 | ناحیه | `بلوک رویه‌ای 86-189` | 0 | ⬜ |

</details>

### TOOL — `TOOL`


<details>
<summary><b>TOOL-001</b> — <code>tools/apply_migration_033.php</code> (171 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-001.01` | 1–126 | ناحیه | `بلوک رویه‌ای 1-126` | 6 | ⬜ |
| `TOOL-001.02` | 127–155 | ناحیه | `حذف کامنت‌ها برای اجرای تمیز (فایل فقط یک UPDATE د` | **10** | ⬜ |
| `TOOL-001.03` | 156–171 | ناحیه | `ثبت در schema_updates — همان کاری که صفحهٔ به‌روزر` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-002</b> — <code>tools/asset_dedupe.php</code> (301 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-002.01` | 1–301 | ناحیه | `بلوک رویه‌ای 1-301` | **19** | ⬜ |

</details>

<details>
<summary><b>TOOL-003</b> — <code>tools/asset_reset.php</code> (206 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-003.01` | 1–59 | ناحیه | `بلوک رویه‌ای 1-59` | 1 | ⬜ |
| `TOOL-003.02` | 60–79 | ناحیه | `۱) وضعیت فعلی` | 0 | ⬜ |
| `TOOL-003.03` | 80–93 | ناحیه | `۲) رکوردهایی که کلید هویت ندارند (منبع تکرار)` | 4 | ⬜ |
| `TOOL-003.04` | 94–112 | ناحیه | `۳) رکوردهای تکراری بر اساس نام` | 4 | ⬜ |
| `TOOL-003.05` | 113–125 | ناحیه | `۴) رکوردهایی با IP به‌عنوان نام` | 4 | ⬜ |
| `TOOL-003.06` | 126–168 | ناحیه | `۵) بکاپ` | 2 | ⬜ |
| `TOOL-003.07` | 169–206 | ناحیه | `۶) حذف` | **13** | ⬜ |

</details>

<details>
<summary><b>TOOL-004</b> — <code>tools/asset_reset_restore.php</code> (159 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-004.01` | 1–159 | ناحیه | `بلوک رویه‌ای 1-159` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-005</b> — <code>tools/backfill_food_auto_receipts.php</code> (222 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-005.01` | 1–70 | ناحیه | `بلوک رویه‌ای 1-70` | 0 | ⬜ |
| `TOOL-005.02` | 71–71 | ناحیه | `«امروز» را عمداً نمی‌گیریم: گذر پایان‌روز خودش ساع` | 0 | ⬜ |
| `TOOL-005.03` | 72–222 | ناحیه | `و تا آن ساعت سفارش‌ها ممکن است هنوز تغییر کنند.` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-006</b> — <code>tools/backup-files.php</code> (106 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-006.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 1 | ⬜ |
| `TOOL-006.02` | 21–35 | ناحیه | `کلید سادهٔ دسترسی — بعد از گرفتن بکاپ، این فایل را` | 2 | ⬜ |
| `TOOL-006.03` | 36–97 | ناحیه | `پوشههایی که نباید داخل بکاپ بروند (حجیم، موقتی، یا` | 0 | ⬜ |
| `TOOL-006.04` | 98–106 | ناحیه | `هدر دانلود` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-007</b> — <code>tools/benchmark_250.py</code> (333 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-007.01` | 1–26 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-007.02` | 27–34 | بلوک | `percentile` | 0 | ⬜ |
| `TOOL-007.03` | 35–35 | بلوک | `Metrics` | 0 | ⬜ |
| `TOOL-007.04` | 36–43 | بلوک | `__init__` | 0 | ⬜ |
| `TOOL-007.05` | 44–50 | بلوک | `record` | 0 | ⬜ |
| `TOOL-007.06` | 51–70 | بلوک | `sample_php` | 0 | ⬜ |
| `TOOL-007.07` | 71–98 | بلوک | `request` | 0 | ⬜ |
| `TOOL-007.08` | 99–157 | بلوک | `user_flow` | 0 | ⬜ |
| `TOOL-007.09` | 158–333 | بلوک | `main` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-008</b> — <code>tools/benchmark_db_report.php</code> (53 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-008.01` | 1–53 | ناحیه | `بلوک رویه‌ای 1-53` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-009</b> — <code>tools/benchmark_operations.py</code> (485 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-009.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-009.02` | 25–31 | بلوک | `percentile` | 0 | ⬜ |
| `TOOL-009.03` | 32–32 | بلوک | `Recorder` | 0 | ⬜ |
| `TOOL-009.04` | 33–40 | بلوک | `__init__` | 0 | ⬜ |
| `TOOL-009.05` | 41–48 | بلوک | `add` | 0 | ⬜ |
| `TOOL-009.06` | 49–65 | بلوک | `sample_php` | 0 | ⬜ |
| `TOOL-009.07` | 66–93 | بلوک | `request` | 0 | ⬜ |
| `TOOL-009.08` | 94–101 | بلوک | `hidden_value` | 0 | ⬜ |
| `TOOL-009.09` | 102–113 | بلوک | `selected_option_value` | 0 | ⬜ |
| `TOOL-009.10` | 114–117 | بلوک | `input_value` | 0 | ⬜ |
| `TOOL-009.11` | 118–121 | بلوک | `is_login_page` | 0 | ⬜ |
| `TOOL-009.12` | 122–136 | بلوک | `media_payload` | 1 | ⬜ |
| `TOOL-009.13` | 137–193 | بلوک | `main` | 0 | ⬜ |
| `TOOL-009.14` | 194–284 | بلوک | `worker` | 0 | ⬜ |
| `TOOL-009.15` | 285–485 | بلوک | `post_cd` | 2 | ⬜ |

</details>

<details>
<summary><b>TOOL-010</b> — <code>tools/benchmark_operations_report.php</code> (124 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-010.01` | 1–124 | ناحیه | `بلوک رویه‌ای 1-124` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-011</b> — <code>tools/benchmark_operations_seed.php</code> (52 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-011.01` | 1–52 | ناحیه | `بلوک رویه‌ای 1-52` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-012</b> — <code>tools/benchmark_seed.php</code> (140 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-012.01` | 1–35 | ناحیه | `بلوک رویه‌ای 1-35` | 1 | ⬜ |
| `TOOL-012.02` | 36–140 | ناحیه | `Match the filesystem prerequisite created by insta` | **10** | ⬜ |

</details>

<details>
<summary><b>TOOL-013</b> — <code>tools/benchmark_system_resilience.py</code> (368 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-013.01` | 1–51 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-013.02` | 52–84 | بلوک | `api_routes` | 0 | ⬜ |
| `TOOL-013.03` | 85–85 | بلوک | `ResourceSampler` | 0 | ⬜ |
| `TOOL-013.04` | 86–90 | بلوک | `__init__` | 0 | ⬜ |
| `TOOL-013.05` | 91–107 | بلوک | `run` | 0 | ⬜ |
| `TOOL-013.06` | 108–114 | بلوک | `percentile` | 0 | ⬜ |
| `TOOL-013.07` | 115–178 | بلوک | `main` | 0 | ⬜ |
| `TOOL-013.08` | 179–368 | بلوک | `worker` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-014</b> — <code>tools/build_1_62_93_food_reconciliation_update.py</code> (194 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-014.01` | 1–54 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-014.02` | 55–58 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-014.03` | 59–73 | بلوک | `safe_archive_path` | 0 | ⬜ |
| `TOOL-014.04` | 74–106 | بلوک | `validate_archive` | 0 | ⬜ |
| `TOOL-014.05` | 107–159 | بلوک | `build` | 1 | ⬜ |
| `TOOL-014.06` | 160–194 | بلوک | `entry` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-015</b> — <code>tools/build_1_62_94_update.py</code> (302 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-015.01` | 1–64 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-015.02` | 65–68 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-015.03` | 69–72 | بلوک | `read_sidecar` | 0 | ⬜ |
| `TOOL-015.04` | 73–85 | بلوک | `verify_interim` | 0 | ⬜ |
| `TOOL-015.05` | 86–89 | بلوک | `git_bytes` | 0 | ⬜ |
| `TOOL-015.06` | 90–117 | بلوک | `reconstruct_base` | 0 | ⬜ |
| `TOOL-015.07` | 118–143 | بلوک | `apply_index_php_delta` | 0 | ⬜ |
| `TOOL-015.08` | 144–158 | بلوک | `safe_archive_path` | 0 | ⬜ |
| `TOOL-015.09` | 159–172 | بلوک | `check_requires_exist` | 0 | ⬜ |
| `TOOL-015.10` | 173–184 | بلوک | `check_no_foreign_content` | 0 | ⬜ |
| `TOOL-015.11` | 185–232 | بلوک | `build` | 1 | ⬜ |
| `TOOL-015.12` | 233–263 | بلوک | `entry` | 4 | ⬜ |
| `TOOL-015.13` | 264–302 | بلوک | `validate_archive` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-016</b> — <code>tools/build_1_62_95_update.py</code> (299 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-016.01` | 1–61 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-016.02` | 62–65 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-016.03` | 66–69 | بلوک | `read_sidecar` | 0 | ⬜ |
| `TOOL-016.04` | 70–81 | بلوک | `verify_interim` | 0 | ⬜ |
| `TOOL-016.05` | 82–109 | بلوک | `reconstruct_base` | 0 | ⬜ |
| `TOOL-016.06` | 110–124 | بلوک | `safe_archive_path` | 0 | ⬜ |
| `TOOL-016.07` | 125–138 | بلوک | `check_requires_exist` | 0 | ⬜ |
| `TOOL-016.08` | 139–150 | بلوک | `check_no_foreign_content` | 0 | ⬜ |
| `TOOL-016.09` | 151–173 | بلوک | `report_worktree_vs_base` | 0 | ⬜ |
| `TOOL-016.10` | 174–205 | بلوک | `validate_archive` | 0 | ⬜ |
| `TOOL-016.11` | 206–265 | بلوک | `build` | 1 | ⬜ |
| `TOOL-016.12` | 266–299 | بلوک | `entry` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-017</b> — <code>tools/build_1_62_97_update.py</code> (352 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-017.01` | 1–73 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-017.02` | 74–118 | بلوک | `line_schema_payload` | 1 | ⬜ |
| `TOOL-017.03` | 119–122 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-017.04` | 123–126 | بلوک | `sha256_path` | 0 | ⬜ |
| `TOOL-017.05` | 127–294 | بلوک | `build_report` | 2 | ⬜ |
| `TOOL-017.06` | 295–327 | بلوک | `main` | 1 | ⬜ |
| `TOOL-017.07` | 328–352 | بلوک | `add` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-018</b> — <code>tools/build_1_63_1_update.py</code> (861 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-018.01` | 1–163 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TOOL-018.02` | 164–167 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-018.03` | 168–171 | بلوک | `sha256_path` | 0 | ⬜ |
| `TOOL-018.04` | 172–195 | بلوک | `installed_163` | 0 | ⬜ |
| `TOOL-018.05` | 196–263 | بلوک | `main` | 0 | ⬜ |
| `TOOL-018.06` | 264–293 | بلوک | `add` | 4 | ⬜ |
| `TOOL-018.07` | 294–340 | بلوک | `check_doc_checksums` | 0 | ⬜ |
| `TOOL-018.08` | 341–544 | بلوک | `build_report` | 2 | ⬜ |
| `TOOL-018.09` | 545–861 | بلوک | `ticket_assignee_is_eligible` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-019</b> — <code>tools/build_1_63_1_update_r14.py</code> (1,048 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-019.01` | 1–212 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TOOL-019.02` | 213–216 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-019.03` | 217–220 | بلوک | `sha256_path` | 0 | ⬜ |
| `TOOL-019.04` | 221–244 | بلوک | `installed_163` | 0 | ⬜ |
| `TOOL-019.05` | 245–312 | بلوک | `main` | 0 | ⬜ |
| `TOOL-019.06` | 313–342 | بلوک | `add` | 4 | ⬜ |
| `TOOL-019.07` | 343–389 | بلوک | `check_doc_checksums` | 0 | ⬜ |
| `TOOL-019.08` | 390–603 | بلوک | `build_report` | 2 | ⬜ |
| `TOOL-019.09` | 604–1048 | بلوک | `ticket_assignee_is_eligible` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-020</b> — <code>tools/build_1_63_1_update_r15.py</code> (1,240 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-020.01` | 1–236 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TOOL-020.02` | 237–240 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-020.03` | 241–244 | بلوک | `sha256_path` | 0 | ⬜ |
| `TOOL-020.04` | 245–268 | بلوک | `installed_163` | 0 | ⬜ |
| `TOOL-020.05` | 269–336 | بلوک | `main` | 0 | ⬜ |
| `TOOL-020.06` | 337–366 | بلوک | `add` | 4 | ⬜ |
| `TOOL-020.07` | 367–413 | بلوک | `check_doc_checksums` | 0 | ⬜ |
| `TOOL-020.08` | 414–644 | بلوک | `build_report` | 2 | ⬜ |
| `TOOL-020.09` | 645–1240 | بلوک | `ticket_assignee_is_eligible` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-021</b> — <code>tools/build_1_63_1_update_r16.py</code> (1,341 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-021.01` | 1–240 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TOOL-021.02` | 241–244 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-021.03` | 245–248 | بلوک | `sha256_path` | 0 | ⬜ |
| `TOOL-021.04` | 249–272 | بلوک | `installed_163` | 0 | ⬜ |
| `TOOL-021.05` | 273–340 | بلوک | `main` | 0 | ⬜ |
| `TOOL-021.06` | 341–370 | بلوک | `add` | 4 | ⬜ |
| `TOOL-021.07` | 371–417 | بلوک | `check_doc_checksums` | 0 | ⬜ |
| `TOOL-021.08` | 418–658 | بلوک | `build_report` | 2 | ⬜ |
| `TOOL-021.09` | 659–1341 | بلوک | `ticket_assignee_is_eligible` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-022</b> — <code>tools/build_1_63_1_update_r17.py</code> (1,471 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-022.01` | 1–290 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TOOL-022.02` | 291–294 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-022.03` | 295–298 | بلوک | `sha256_path` | 0 | ⬜ |
| `TOOL-022.04` | 299–322 | بلوک | `installed_163` | 0 | ⬜ |
| `TOOL-022.05` | 323–390 | بلوک | `main` | 0 | ⬜ |
| `TOOL-022.06` | 391–420 | بلوک | `add` | 4 | ⬜ |
| `TOOL-022.07` | 421–467 | بلوک | `check_doc_checksums` | 0 | ⬜ |
| `TOOL-022.08` | 468–788 | بلوک | `build_report` | 4 | ⬜ |
| `TOOL-022.09` | 789–1471 | بلوک | `ticket_assignee_is_eligible` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-023</b> — <code>tools/build_1_63_1_update_r19.py</code> (1,486 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-023.01` | 1–305 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `TOOL-023.02` | 306–309 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-023.03` | 310–313 | بلوک | `sha256_path` | 0 | ⬜ |
| `TOOL-023.04` | 314–337 | بلوک | `installed_163` | 0 | ⬜ |
| `TOOL-023.05` | 338–405 | بلوک | `main` | 0 | ⬜ |
| `TOOL-023.06` | 406–435 | بلوک | `add` | 4 | ⬜ |
| `TOOL-023.07` | 436–482 | بلوک | `check_doc_checksums` | 0 | ⬜ |
| `TOOL-023.08` | 483–803 | بلوک | `build_report` | 4 | ⬜ |
| `TOOL-023.09` | 804–1486 | بلوک | `ticket_assignee_is_eligible` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-024</b> — <code>tools/build_1_63_1_update_r20.py</code> (219 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-024.01` | 1–65 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-024.02` | 66–69 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-024.03` | 70–108 | بلوک | `report_text` | 0 | ⬜ |
| `TOOL-024.04` | 109–132 | بلوک | `load_base_payload` | 0 | ⬜ |
| `TOOL-024.05` | 133–183 | بلوک | `main` | 0 | ⬜ |
| `TOOL-024.06` | 184–219 | بلوک | `add` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-025</b> — <code>tools/build_1_63_2_update_r21.py</code> (278 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-025.01` | 1–85 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-025.02` | 86–89 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-025.03` | 90–96 | بلوک | `read_source` | 0 | ⬜ |
| `TOOL-025.04` | 97–136 | بلوک | `report_text` | 1 | ⬜ |
| `TOOL-025.05` | 137–167 | بلوک | `render_download_page` | 1 | ⬜ |
| `TOOL-025.06` | 168–231 | بلوک | `main` | 0 | ⬜ |
| `TOOL-025.07` | 232–278 | بلوک | `add` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-026</b> — <code>tools/build_asset_names_package.py</code> (300 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-026.01` | 1–65 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-026.02` | 66–69 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-026.03` | 70–77 | بلوک | `sha256_path` | 1 | ⬜ |
| `TOOL-026.04` | 78–198 | بلوک | `build_report` | 1 | ⬜ |
| `TOOL-026.05` | 199–235 | بلوک | `main` | 0 | ⬜ |
| `TOOL-026.06` | 236–259 | بلوک | `add` | 0 | ⬜ |
| `TOOL-026.07` | 260–300 | بلوک | `check_doc_checksums` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-027</b> — <code>tools/build_cd_report_org_package.py</code> (415 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-027.01` | 1–101 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-027.02` | 102–105 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-027.03` | 106–113 | بلوک | `sha256_path` | 1 | ⬜ |
| `TOOL-027.04` | 114–352 | بلوک | `build_report` | 2 | ⬜ |
| `TOOL-027.05` | 353–389 | بلوک | `main` | 0 | ⬜ |
| `TOOL-027.06` | 390–415 | بلوک | `add` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-028</b> — <code>tools/build_cumulative_update.py</code> (279 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-028.01` | 1–88 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-028.02` | 89–102 | بلوک | `safe_relative_path` | 0 | ⬜ |
| `TOOL-028.03` | 103–106 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-028.04` | 107–152 | بلوک | `load_base_package` | 1 | ⬜ |
| `TOOL-028.05` | 153–190 | بلوک | `render_download_index` | 1 | ⬜ |
| `TOOL-028.06` | 191–266 | بلوک | `build` | 1 | ⬜ |
| `TOOL-028.07` | 267–279 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-029</b> — <code>tools/build_full_1_62_94.py</code> (227 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-029.01` | 1–43 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-029.02` | 44–47 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-029.03` | 48–60 | بلوک | `verify_interim` | 0 | ⬜ |
| `TOOL-029.04` | 61–75 | بلوک | `safe_archive_path` | 0 | ⬜ |
| `TOOL-029.05` | 76–101 | بلوک | `reconstruct_base` | 0 | ⬜ |
| `TOOL-029.06` | 102–125 | بلوک | `strip_rc_leak` | 0 | ⬜ |
| `TOOL-029.07` | 126–140 | بلوک | `check_no_request_center` | 0 | ⬜ |
| `TOOL-029.08` | 141–175 | بلوک | `provenance_note` | 1 | ⬜ |
| `TOOL-029.09` | 176–190 | بلوک | `build` | 0 | ⬜ |
| `TOOL-029.10` | 191–227 | بلوک | `entry` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-030</b> — <code>tools/build_full_1_62_95.py</code> (278 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-030.01` | 1–61 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-030.02` | 62–65 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-030.03` | 66–80 | بلوک | `safe_archive_path` | 0 | ⬜ |
| `TOOL-030.04` | 81–84 | بلوک | `head_sha` | 0 | ⬜ |
| `TOOL-030.05` | 85–101 | بلوک | `reconstruct_base` | 0 | ⬜ |
| `TOOL-030.06` | 102–122 | بلوک | `check_no_foreign_line` | 0 | ⬜ |
| `TOOL-030.07` | 123–138 | بلوک | `add_reports` | 0 | ⬜ |
| `TOOL-030.08` | 139–153 | بلوک | `add_seeds` | 1 | ⬜ |
| `TOOL-030.09` | 154–212 | بلوک | `provenance_note` | 1 | ⬜ |
| `TOOL-030.10` | 213–233 | بلوک | `build` | 0 | ⬜ |
| `TOOL-030.11` | 234–278 | بلوک | `entry` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-031</b> — <code>tools/build_full_1_62_97.py</code> (257 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-031.01` | 1–62 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-031.02` | 63–80 | بلوک | `delta_files` | 0 | ⬜ |
| `TOOL-031.03` | 81–84 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-031.04` | 85–99 | بلوک | `safe_archive_path` | 0 | ⬜ |
| `TOOL-031.05` | 100–146 | بلوک | `provenance_note` | 1 | ⬜ |
| `TOOL-031.06` | 147–212 | بلوک | `build` | 0 | ⬜ |
| `TOOL-031.07` | 213–257 | بلوک | `entry` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-032</b> — <code>tools/build_full_1_63_1.py</code> (228 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-032.01` | 1–47 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-032.02` | 48–51 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-032.03` | 52–66 | بلوک | `safe_archive_path` | 0 | ⬜ |
| `TOOL-032.04` | 67–86 | بلوک | `layer` | 0 | ⬜ |
| `TOOL-032.05` | 87–132 | بلوک | `provenance_note` | 1 | ⬜ |
| `TOOL-032.06` | 133–228 | بلوک | `build` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-033</b> — <code>tools/build_full_1_63_1_r14.py</code> (204 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-033.01` | 1–83 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-033.02` | 84–87 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-033.03` | 88–91 | بلوک | `git` | 0 | ⬜ |
| `TOOL-033.04` | 92–97 | بلوک | `excluded` | 0 | ⬜ |
| `TOOL-033.05` | 98–204 | بلوک | `main` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-034</b> — <code>tools/build_manual_guest_package.py</code> (328 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-034.01` | 1–50 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-034.02` | 51–54 | بلوک | `sha256_bytes` | 0 | ⬜ |
| `TOOL-034.03` | 55–62 | بلوک | `sha256_path` | 1 | ⬜ |
| `TOOL-034.04` | 63–232 | بلوک | `build_report` | 1 | ⬜ |
| `TOOL-034.05` | 233–269 | بلوک | `main` | 0 | ⬜ |
| `TOOL-034.06` | 270–293 | بلوک | `add` | 0 | ⬜ |
| `TOOL-034.07` | 294–328 | بلوک | `check_doc_checksums` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-035</b> — <code>tools/build_rc_update.py</code> (297 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-035.01` | 1–136 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TOOL-035.02` | 137–140 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-035.03` | 141–156 | بلوک | `git` | 0 | ⬜ |
| `TOOL-035.04` | 157–167 | بلوک | `changed_files` | 0 | ⬜ |
| `TOOL-035.05` | 168–182 | بلوک | `safe_archive_path` | 0 | ⬜ |
| `TOOL-035.06` | 183–215 | بلوک | `validate_archive` | 0 | ⬜ |
| `TOOL-035.07` | 216–262 | بلوک | `build` | 1 | ⬜ |
| `TOOL-035.08` | 263–297 | بلوک | `entry` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-036</b> — <code>tools/build_system_map.py</code> (471 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-036.01` | 1–187 | بلوک | `مقدمهٔ فایل` | 2 | ⬜ |
| `TOOL-036.02` | 188–205 | بلوک | `iter_files` | 0 | ⬜ |
| `TOOL-036.03` | 206–217 | بلوک | `categorize` | 0 | ⬜ |
| `TOOL-036.04` | 218–224 | بلوک | `read_text` | 0 | ⬜ |
| `TOOL-036.05` | 225–228 | بلوک | `count_lines` | 0 | ⬜ |
| `TOOL-036.06` | 229–246 | بلوک | `استخراج نمادها` | 0 | ⬜ |
| `TOOL-036.07` | 247–289 | بلوک | `extract_symbols` | 0 | ⬜ |
| `TOOL-036.08` | 290–295 | بلوک | `استخراج روتها` | 0 | ⬜ |
| `TOOL-036.09` | 296–301 | بلوک | `extract_routes` | 0 | ⬜ |
| `TOOL-036.10` | 302–320 | بلوک | `add` | 0 | ⬜ |
| `TOOL-036.11` | 321–324 | بلوک | `استخراج جداول` | 0 | ⬜ |
| `TOOL-036.12` | 325–331 | بلوک | `extract_tables` | 0 | ⬜ |
| `TOOL-036.13` | 332–332 | بلوک | `بستههای آپدیت` | 0 | ⬜ |
| `TOOL-036.14` | 333–353 | بلوک | `extract_updates` | 0 | ⬜ |
| `TOOL-036.15` | 354–360 | بلوک | `tsv` | 0 | ⬜ |
| `TOOL-036.16` | 361–427 | بلوک | `build` | 0 | ⬜ |
| `TOOL-036.17` | 428–471 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-037</b> — <code>tools/build_update_1_62_88.py</code> (194 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-037.01` | 1–44 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `TOOL-037.02` | 45–48 | بلوک | `sha256` | 0 | ⬜ |
| `TOOL-037.03` | 49–54 | بلوک | `git` | 0 | ⬜ |
| `TOOL-037.04` | 55–64 | بلوک | `changed_files` | 0 | ⬜ |
| `TOOL-037.05` | 65–78 | بلوک | `safe_archive_path` | 0 | ⬜ |
| `TOOL-037.06` | 79–113 | بلوک | `validate_archive` | 0 | ⬜ |
| `TOOL-037.07` | 114–158 | بلوک | `build` | 1 | ⬜ |
| `TOOL-037.08` | 159–194 | بلوک | `entry` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-038</b> — <code>tools/ci_load_schema.php</code> (107 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-038.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `TOOL-038.02` | 26–31 | تابع | `ci_error` | 0 | ⬜ |
| `TOOL-038.03` | 32–50 | ناحیه | `بلوک رویه‌ای 32-50` | 1 | ⬜ |
| `TOOL-038.04` | 51–107 | ناحیه | `تقسیم بر اساس «;» با رعایت رشته‌های تک‌نقل‌قول (دو` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-039</b> — <code>tools/code_index.php</code> (571 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-039.01` | 1–33 | ناحیه | `بلوک رویه‌ای 1-33` | 0 | ⬜ |
| `TOOL-039.02` | 34–42 | تابع | `ci_strlen` | 0 | ⬜ |
| `TOOL-039.03` | 44–51 | تابع | `ci_substr` | 0 | ⬜ |
| `TOOL-039.04` | 53–65 | تابع | `ci_area` | 0 | ⬜ |
| `TOOL-039.05` | 67–73 | تابع | `ci_fingerprint` | 0 | ⬜ |
| `TOOL-039.06` | 75–112 | تابع | `ci_anchor_label` | 0 | ⬜ |
| `TOOL-039.07` | 114–160 | تابع | `ci_risk` | 0 | ⬜ |
| `TOOL-039.08` | 162–363 | تابع | `ci_extract_php` | 7 | ⬜ |
| `TOOL-039.09` | 365–395 | تابع | `ci_extract_plain` | 0 | ⬜ |
| `TOOL-039.10` | 397–418 | تابع | `ci_absorb_docblock` | 0 | ⬜ |
| `TOOL-039.11` | 420–449 | تابع | `ci_extract_sql` | 1 | ⬜ |
| `TOOL-039.12` | 451–479 | تابع | `ci_blocks_from_anchors` | 0 | ⬜ |
| `TOOL-039.13` | 481–484 | تابع | `ci_slice` | 0 | ⬜ |
| `TOOL-039.14` | 486–494 | تابع | `ci_token_line` | 0 | ⬜ |
| `TOOL-039.15` | 496–513 | تابع | `ci_next_name` | 0 | ⬜ |
| `TOOL-039.16` | 515–553 | تابع | `ci_build` | 0 | ⬜ |
| `TOOL-039.17` | 555–571 | ناحیه | `اجرای مستقیم از خط فرمان (در php-wasm این بخش اجرا` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-040</b> — <code>tools/db_health_report.php</code> (129 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-040.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-040.02` | 22–25 | تابع | `dbh_line` | 3 | ⬜ |
| `TOOL-040.03` | 26–35 | ناحیه | `بلوک رویه‌ای 26-35` | 0 | ⬜ |
| `TOOL-040.04` | 36–51 | ناحیه | `۱) اندازه و تعداد ردیف جداول` | 0 | ⬜ |
| `TOOL-040.05` | 52–69 | ناحیه | `۲) کوئری‌های کند ۷ روز اخیر (از لاگ فنی؛ کوئری‌های` | 0 | ⬜ |
| `TOOL-040.06` | 70–129 | ناحیه | `۳) EXPLAIN کوئری‌های داغ` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-041</b> — <code>tools/diag_asset_duplicates.php</code> (79 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-041.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `TOOL-041.02` | 14–14 | تابع | `out` | 3 | ⬜ |
| `TOOL-041.03` | 15–15 | تابع | `q` | 0 | ⬜ |
| `TOOL-041.04` | 16–23 | ناحیه | `بلوک رویه‌ای 16-23` | 0 | ⬜ |
| `TOOL-041.05` | 24–29 | ناحیه | `۱) تکرار بر اساس hostname دقیق` | 0 | ⬜ |
| `TOOL-041.06` | 30–48 | ناحیه | `۲) تکرار بر اساس نام نمایشی نرمالشده (همون چی که ت` | 0 | ⬜ |
| `TOOL-041.07` | 49–54 | ناحیه | `۳) پراکندگی منبع` | 0 | ⬜ |
| `TOOL-041.08` | 55–59 | ناحیه | `۴) چند ردیف با hostname خالی` | 0 | ⬜ |
| `TOOL-041.09` | 60–66 | ناحیه | `۵) source نامعتبر` | 0 | ⬜ |
| `TOOL-041.10` | 67–72 | ناحیه | `۶) asset_profiles یتیم / خالی` | 0 | ⬜ |
| `TOOL-041.11` | 73–79 | ناحیه | `۷) تگهای خودکار DOMAIN- و CLIENT-` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-042</b> — <code>tools/diag_asset_profile.php</code> (163 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-042.01` | 1–163 | ناحیه | `بلوک رویه‌ای 1-163` | **9** | ⬜ |

</details>

<details>
<summary><b>TOOL-043</b> — <code>tools/diag_connection.php</code> (258 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-043.01` | 1–258 | ناحیه | `بلوک رویه‌ای 1-258` | **20** | ⬜ |

</details>

<details>
<summary><b>TOOL-044</b> — <code>tools/diag_extract.php</code> (853 خط، 19 آدرس، بازبینی‌شده 0/19)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-044.01` | 1–122 | ناحیه | `بلوک رویه‌ای 1-122` | 1 | ⬜ |
| `TOOL-044.02` | 123–126 | تابع | `setting` | 0 | ⬜ |
| `TOOL-044.03` | 127–134 | ناحیه | `بلوک رویه‌ای 127-134` | 0 | ⬜ |
| `TOOL-044.04` | 135–184 | تابع | `diag_read_password` | 5 | ⬜ |
| `TOOL-044.05` | 185–201 | ناحیه | `بلوک رویه‌ای 185-201` | 1 | ⬜ |
| `TOOL-044.06` | 202–217 | تابع | `diag_line` | 0 | ⬜ |
| `TOOL-044.07` | 219–231 | تابع | `diag_tcp` | 1 | ⬜ |
| `TOOL-044.08` | 232–234 | ناحیه | `بلوک رویه‌ای 232-234` | 0 | ⬜ |
| `TOOL-044.09` | 235–264 | ناحیه | `۰) محیط اجرا` | 0 | ⬜ |
| `TOOL-044.10` | 265–405 | ناحیه | `اگر مسیر جایگزینی پیدا شد ولی در tools\ نیست، به س` | **13** | ⬜ |
| `TOOL-044.11` | 406–487 | ناحیه | `آیا Apache با LocalSystem بالا آمده؟ روی XAMPP پیش` | **12** | ⬜ |
| `TOOL-044.12` | 488–496 | ناحیه | `۱) نام و DNS` | 2 | ⬜ |
| `TOOL-044.13` | 497–508 | ناحیه | `۲-۴) پورت‌ها` | 0 | ⬜ |
| `TOOL-044.14` | 509–537 | ناحیه | `۵) اتصال واقعی CIM با هر پروتکل` | 0 | ⬜ |
| `TOOL-044.15` | 538–543 | ناحیه | `مسیر: explicit` | 0 | ⬜ |
| `TOOL-044.16` | 544–590 | ناحیه | `مسیر: explicit` | 7 | ⬜ |
| `TOOL-044.17` | 591–595 | ناحیه | `مسیر: explicit` | 0 | ⬜ |
| `TOOL-044.18` | 596–629 | ناحیه | `مسیر: implicit` | 0 | ⬜ |
| `TOOL-044.19` | 630–853 | ناحیه | `۶) SMB با اعتبارنامه — اثبات ادمین محلی بودن` | **8** | ⬜ |

</details>

<details>
<summary><b>TOOL-045</b> — <code>tools/diag_food_duplicates.php</code> (31 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-045.01` | 1–31 | ناحیه | `بلوک رویه‌ای 1-31` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-046</b> — <code>tools/diag_food_match.php</code> (111 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-046.01` | 1–42 | ناحیه | `بلوک رویه‌ای 1-42` | 0 | ⬜ |
| `TOOL-046.02` | 43–57 | ناحیه | `کاربران` | 0 | ⬜ |
| `TOOL-046.03` | 58–91 | ناحیه | `SOURCE_TABLE` | 0 | ⬜ |
| `TOOL-046.04` | 92–111 | ناحیه | `سفارش داخلی` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-047</b> — <code>tools/diag_food_ticket.php</code> (103 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-047.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `TOOL-047.02` | 27–48 | ناحیه | `Internal orders diagnostic (aggregate only; no Acc` | 0 | ⬜ |
| `TOOL-047.03` | 49–67 | ناحیه | `User lookup 377` | 0 | ⬜ |
| `TOOL-047.04` | 68–103 | ناحیه | `نمونهٔ ردیف‌های منبع تردد` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-048</b> — <code>tools/diag_netprint.php</code> (112 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-048.01` | 1–112 | ناحیه | `بلوک رویه‌ای 1-112` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-049</b> — <code>tools/diag_print_margin.php</code> (313 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-049.01` | 1–49 | ناحیه | `بلوک رویه‌ای 1-49` | 3 | ⬜ |
| `TOOL-049.02` | 50–77 | ناحیه | `۱) تنظیمات چاپ` | 1 | ⬜ |
| `TOOL-049.03` | 78–103 | ناحیه | `۲) مسیر فعال چاپ` | 4 | ⬜ |
| `TOOL-049.04` | 104–129 | ناحیه | `۲.۱) ترازِ دقیق لبه‌ها (۱.۳۷): شروع چاپ = لبهٔ کاغ` | 0 | ⬜ |
| `TOOL-049.05` | 130–158 | ناحیه | `۳) قالب فعال و فاصلهٔ بالای محتوا` | 0 | ⬜ |
| `TOOL-049.06` | 159–186 | ناحیه | `مدل نهایی (پس از حذف عناصر بدون مقدار و تراز خودکا` | 0 | ⬜ |
| `TOOL-049.07` | 187–247 | ناحیه | `۴) رسم واقعی و شمارش ردیف‌های سفید` | 1 | ⬜ |
| `TOOL-049.08` | 248–313 | ناحیه | `۵) اندازه‌گیری واقعی درایور ویندوز (بدون چاپ)` | 7 | ⬜ |

</details>

<details>
<summary><b>TOOL-050</b> — <code>tools/diag_source_row_delete.php</code> (128 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-050.01` | 1–128 | ناحیه | `بلوک رویه‌ای 1-128` | **12** | ⬜ |

</details>

<details>
<summary><b>TOOL-051</b> — <code>tools/diagnose_food_full.php</code> (1,150 خط، 39 آدرس، بازبینی‌شده 0/39)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-051.01` | 1–41 | ناحیه | `بلوک رویه‌ای 1-41` | 5 | ⬜ |
| `TOOL-051.02` | 42–63 | ناحیه | `۰) پیش‌شرط: config.php` | 0 | ⬜ |
| `TOOL-051.03` | 64–64 | ناحیه | `توابع کمکی دیتابیس (اطلاعات فقط از information_sch` | 0 | ⬜ |
| `TOOL-051.04` | 65–73 | تابع | `diag_table_exists` | 0 | ⬜ |
| `TOOL-051.05` | 74–82 | تابع | `diag_columns` | 0 | ⬜ |
| `TOOL-051.06` | 83–90 | تابع | `diag_mask_row` | 0 | ⬜ |
| `TOOL-051.07` | 92–111 | ناحیه | `۱) PHP و محیط` | 1 | ⬜ |
| `TOOL-051.08` | 112–158 | ناحیه | `۲) فایل‌های کلیدی و قفل‌ها` | 7 | ⬜ |
| `TOOL-051.09` | 159–159 | ناحیه | `۲ای) فهرست همهٔ فایل‌های PHP ریشه + هش sha256 فایل` | 0 | ⬜ |
| `TOOL-051.10` | 160–187 | ناحیه | `هدف: تشخیص «آپدیت ناقص» (فایل‌های چند نسخه قاطی) و` | 0 | ⬜ |
| `TOOL-051.11` | 188–319 | ناحیه | `۳) خودِ Worker: heartbeat / تسک / لاگ` | **8** | ⬜ |
| `TOOL-051.12` | 320–320 | ناحیه | `۳ب) probe قفل‌ها` | 0 | ⬜ |
| `TOOL-051.13` | 321–338 | ناحیه | `اگر قفل print-queue در دست کسی باشد، Worker ساکت (` | 3 | ⬜ |
| `TOOL-051.14` | 339–339 | ناحیه | `food-worker-process.lock نمونهٔ عادی است که Worker` | 0 | ⬜ |
| `TOOL-051.15` | 340–352 | ناحیه | `«قفل است» در اینجا یعنی Worker حداقل یک‌بار اجرا ش` | 3 | ⬜ |
| `TOOL-051.16` | 353–587 | ناحیه | `۴) دیتابیس (فقط SELECT)` | **10** | ⬜ |
| `TOOL-051.17` | 588–595 | ناحیه | `۵) چاپگر (فقط تست اتصال، بدون چاپ)` | 0 | ⬜ |
| `TOOL-051.18` | 596–614 | ناحیه | `مسیر: tcp_raw` | 1 | ⬜ |
| `TOOL-051.19` | 615–625 | ناحیه | `۶) منبع تردد (فقط وجود فایل)` | 0 | ⬜ |
| `TOOL-051.20` | 626–708 | ناحیه | `۴ب) تحلیل عمیق «در صف چاپ»` | **9** | ⬜ |
| `TOOL-051.21` | 709–726 | ناحیه | `لاگ چاپ امروز به تفکیک وضعیت` | 5 | ⬜ |
| `TOOL-051.22` | 727–727 | ناحیه | `۴پ) ردیابی «فیش گروهی» پشت ردیف‌های pending` | 0 | ⬜ |
| `TOOL-051.23` | 728–728 | ناحیه | `فرضیهٔ اصلی: ردیف‌های pending اعضای اجراهای گروهی‌` | 0 | ⬜ |
| `TOOL-051.24` | 729–794 | ناحیه | `مسیر چاپ گروهی برای runهای غیر done ساکت برمی‌گردد` | 5 | ⬜ |
| `TOOL-051.25` | 795–812 | ناحیه | `[food-groups] در app.log (اگر خطای عضوی رخ داده با` | 1 | ⬜ |
| `TOOL-051.26` | 813–813 | ناحیه | `۴ت) دقیقاً همان کوئری انتخاب Worker (read-only؛ LI` | 0 | ⬜ |
| `TOOL-051.27` | 814–952 | ناحیه | `هدف: ببینیم ۳۰ ردیف قدیمیِ قابل‌ادعا کدام‌اند و طب` | 6 | ⬜ |
| `TOOL-051.28` | 953–953 | ناحیه | `۴خ) پیش‌نمایشِ «تطبیق سفارش» برای نمونه‌ای از pend` | 0 | ⬜ |
| `TOOL-051.29` | 954–954 | ناحیه | `دقیقاً همان تابع prepare_queued_food را روی نمونه ` | 0 | ⬜ |
| `TOOL-051.30` | 955–991 | ناحیه | `اگر «سفارش فعلی با فیش فرق می‌کند» دیده شود، هر pe` | 0 | ⬜ |
| `TOOL-051.31` | 992–992 | ناحیه | `۴چ) آخرین وضعیت هر رویداد در food_ticket_print_log` | 0 | ⬜ |
| `TOOL-051.32` | 993–1022 | ناحیه | `ردیف‌های 'printing' که هرگز به 'printed'/'failed' ` | 0 | ⬜ |
| `TOOL-051.33` | 1023–1045 | ناحیه | `۷) پروسه‌های php/httpd با زمان شروع` | 7 | ⬜ |
| `TOOL-051.34` | 1046–1046 | ناحیه | `۸) لاگ امروز سیستم (۱۵ خط آخر app.log + خط خام آخر` | 0 | ⬜ |
| `TOOL-051.35` | 1047–1077 | ناحیه | `خط کامل آخر را بدون برش چاپ می‌کنیم تا کلید "queue` | 6 | ⬜ |
| `TOOL-051.36` | 1078–1099 | ناحیه | `خط خام آخر لاگ worker (بدون برش) — کلید "queue" دا` | 1 | ⬜ |
| `TOOL-051.37` | 1100–1100 | ناحیه | `۱۰) تاریخچهٔ queue در لاگ امروز (همهٔ چرخه‌ها)` | 0 | ⬜ |
| `TOOL-051.38` | 1101–1144 | ناحیه | `هر خط چرخه که "queue":{...} دارد را می‌خوانیم؛ توز` | 1 | ⬜ |
| `TOOL-051.39` | 1145–1150 | ناحیه | `۱۱) جمع‌بندی` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-052</b> — <code>tools/diagnose_food_worker.php</code> (895 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-052.01` | 1–37 | ناحیه | `بلوک رویه‌ای 1-37` | 5 | ⬜ |
| `TOOL-052.02` | 38–59 | ناحیه | `۰) پیش‌شرط: config.php` | 0 | ⬜ |
| `TOOL-052.03` | 60–60 | ناحیه | `توابع کمکی دیتابیس (اطلاعات فقط از information_sch` | 0 | ⬜ |
| `TOOL-052.04` | 61–69 | تابع | `diag_table_exists` | 0 | ⬜ |
| `TOOL-052.05` | 70–78 | تابع | `diag_columns` | 0 | ⬜ |
| `TOOL-052.06` | 79–86 | تابع | `diag_mask_row` | 0 | ⬜ |
| `TOOL-052.07` | 88–107 | ناحیه | `۱) PHP و محیط` | 1 | ⬜ |
| `TOOL-052.08` | 108–154 | ناحیه | `۲) فایل‌های کلیدی و قفل‌ها` | 7 | ⬜ |
| `TOOL-052.09` | 155–286 | ناحیه | `۳) خودِ Worker: heartbeat / تسک / لاگ` | **8** | ⬜ |
| `TOOL-052.10` | 287–287 | ناحیه | `۳ب) probe قفل‌ها` | 0 | ⬜ |
| `TOOL-052.11` | 288–305 | ناحیه | `اگر قفل print-queue در دست کسی باشد، Worker ساکت (` | 3 | ⬜ |
| `TOOL-052.12` | 306–306 | ناحیه | `food-worker-process.lock نمونهٔ عادی است که Worker` | 0 | ⬜ |
| `TOOL-052.13` | 307–319 | ناحیه | `«قفل است» در اینجا یعنی Worker حداقل یک‌بار اجرا ش` | 3 | ⬜ |
| `TOOL-052.14` | 320–516 | ناحیه | `۴) دیتابیس (فقط SELECT)` | **10** | ⬜ |
| `TOOL-052.15` | 517–524 | ناحیه | `۵) چاپگر (فقط تست اتصال، بدون چاپ)` | 0 | ⬜ |
| `TOOL-052.16` | 525–543 | ناحیه | `مسیر: tcp_raw` | 1 | ⬜ |
| `TOOL-052.17` | 544–554 | ناحیه | `۶) منبع تردد (فقط وجود فایل)` | 0 | ⬜ |
| `TOOL-052.18` | 555–637 | ناحیه | `۴ب) تحلیل عمیق «در صف چاپ»` | **9** | ⬜ |
| `TOOL-052.19` | 638–655 | ناحیه | `لاگ چاپ امروز به تفکیک وضعیت` | 5 | ⬜ |
| `TOOL-052.20` | 656–656 | ناحیه | `۴پ) ردیابی «فیش گروهی» پشت ردیف‌های pending` | 0 | ⬜ |
| `TOOL-052.21` | 657–657 | ناحیه | `فرضیهٔ اصلی: ردیف‌های pending اعضای اجراهای گروهی‌` | 0 | ⬜ |
| `TOOL-052.22` | 658–723 | ناحیه | `مسیر چاپ گروهی برای runهای غیر done ساکت برمی‌گردد` | 5 | ⬜ |
| `TOOL-052.23` | 724–741 | ناحیه | `[food-groups] در app.log (اگر خطای عضوی رخ داده با` | 1 | ⬜ |
| `TOOL-052.24` | 742–742 | ناحیه | `۴ت) دقیقاً همان کوئری انتخاب Worker (read-only؛ LI` | 0 | ⬜ |
| `TOOL-052.25` | 743–823 | ناحیه | `هدف: ببینیم ۳۰ ردیف قدیمیِ قابل‌ادعا کدام‌اند و طب` | 5 | ⬜ |
| `TOOL-052.26` | 824–846 | ناحیه | `۷) پروسه‌های php/httpd با زمان شروع` | 7 | ⬜ |
| `TOOL-052.27` | 847–847 | ناحیه | `۸) لاگ امروز سیستم (۱۵ خط آخر app.log + خط خام آخر` | 0 | ⬜ |
| `TOOL-052.28` | 848–867 | ناحیه | `خط کامل آخر را بدون برش چاپ می‌کنیم تا کلید "queue` | 1 | ⬜ |
| `TOOL-052.29` | 868–889 | ناحیه | `خط خام آخر لاگ worker (بدون برش) — کلید "queue" دا` | 1 | ⬜ |
| `TOOL-052.30` | 890–895 | ناحیه | `۱۰) جمع‌بندی` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-053</b> — <code>tools/diagnose_ldap.php</code> (111 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-053.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 0 | ⬜ |
| `TOOL-053.02` | 28–36 | ناحیه | `۱) افزونه LDAP` | 0 | ⬜ |
| `TOOL-053.03` | 37–59 | ناحیه | `۲) تنظیمات` | 0 | ⬜ |
| `TOOL-053.04` | 60–71 | ناحیه | `۳) اتصال` | 3 | ⬜ |
| `TOOL-053.05` | 72–88 | ناحیه | `۴) Bind` | 2 | ⬜ |
| `TOOL-053.06` | 89–111 | ناحیه | `۵) جست‌وجو` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-054</b> — <code>tools/diagnose_login_and_assets.php</code> (210 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-054.01` | 1–35 | ناحیه | `بلوک رویه‌ای 1-35` | 0 | ⬜ |
| `TOOL-054.02` | 36–45 | تابع | `dline` | 0 | ⬜ |
| `TOOL-054.03` | 46–121 | ناحیه | `بلوک رویه‌ای 46-121` | 3 | ⬜ |
| `TOOL-054.04` | 122–210 | ناحیه | `حساب‌هایی که نام کاربری‌شان «محلی» ثبت شده — ورود ` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-055</b> — <code>tools/explain_alarms.php</code> (219 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-055.01` | 1–219 | ناحیه | `بلوک رویه‌ای 1-219` | **15** | ⬜ |

</details>

<details>
<summary><b>TOOL-056</b> — <code>tools/export_domain_users.php</code> (87 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-056.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-056.02` | 4–65 | تابع | `dud_export` | 0 | ⬜ |
| `TOOL-056.03` | 67–82 | تابع | `dud_main` | 0 | ⬜ |
| `TOOL-056.04` | 83–87 | ناحیه | `بلوک رویه‌ای 83-87` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-057</b> — <code>tools/find-in-system.py</code> (236 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-057.01` | 1–33 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-057.02` | 34–43 | بلوک | `normalize` | 0 | ⬜ |
| `TOOL-057.03` | 44–51 | بلوک | `load` | 0 | ⬜ |
| `TOOL-057.04` | 52–74 | بلوک | `score` | 0 | ⬜ |
| `TOOL-057.05` | 75–92 | بلوک | `top` | 0 | ⬜ |
| `TOOL-057.06` | 93–117 | بلوک | `print_features` | 0 | ⬜ |
| `TOOL-057.07` | 118–128 | بلوک | `print_section` | 0 | ⬜ |
| `TOOL-057.08` | 129–236 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-058</b> — <code>tools/fix_absence_ticket_mismatch.php</code> (227 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-058.01` | 1–93 | ناحیه | `بلوک رویه‌ای 1-93` | 0 | ⬜ |
| `TOOL-058.02` | 94–106 | تابع | `fx_name` | 0 | ⬜ |
| `TOOL-058.03` | 107–109 | ناحیه | `بلوک رویه‌ای 107-109` | 0 | ⬜ |
| `TOOL-058.04` | 110–122 | ناحیه | `۱) غایب با فیش چاپ‌شده` | 0 | ⬜ |
| `TOOL-058.05` | 123–183 | ناحیه | `۲) فیش چاپ‌شده بدون هیچ ردیف غیبت` | 0 | ⬜ |
| `TOOL-058.06` | 184–227 | ناحیه | `اعمال` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-059</b> — <code>tools/fix_food_columns.php</code> (140 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-059.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `TOOL-059.02` | 27–57 | ناحیه | `پیام روشن اگر فایل تنظیمات نیست (bootstrap در CLI ` | 0 | ⬜ |
| `TOOL-059.03` | 58–63 | ناحیه | `۱) وجود جدول` | 0 | ⬜ |
| `TOOL-059.04` | 64–77 | ناحیه | `۲) فهرست زندهٔ ستون‌ها (بدون تکیه بر کش)` | 0 | ⬜ |
| `TOOL-059.05` | 78–117 | ناحیه | `۳) تشخیص توابع سامانه (همان تابعی که دکمهٔ تحویل ا` | 4 | ⬜ |
| `TOOL-059.06` | 118–140 | ناحیه | `۴) بررسی پس از اصلاح (زنده)` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-060</b> — <code>tools/fix_manual_guest_tickets.php</code> (236 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-060.01` | 1–42 | ناحیه | `بلوک رویه‌ای 1-42` | 0 | ⬜ |
| `TOOL-060.02` | 43–43 | ناحیه | `غذای همان الگوی cron/food_ticket_worker.php بارگذا` | 0 | ⬜ |
| `TOOL-060.03` | 44–65 | ناحیه | `غذا را نمی‌آورد و بدون آن هیچ‌کدام از توابع food_t` | 0 | ⬜ |
| `TOOL-060.04` | 66–87 | ناحیه | `آرگومان‌ها` | 0 | ⬜ |
| `TOOL-060.05` | 88–115 | ناحیه | `واکشی ردیف‌های نامزد` | 0 | ⬜ |
| `TOOL-060.06` | 116–135 | ناحیه | `فیلتر نهایی: چاپ فیزیکی درخواست نشده باشد` | 0 | ⬜ |
| `TOOL-060.07` | 136–158 | ناحیه | `ردیف‌هایی که در صف چاپ‌اند/چاپ فیزیکی خواسته‌اند (` | 0 | ⬜ |
| `TOOL-060.08` | 159–186 | ناحیه | `نمایش برنامه` | 0 | ⬜ |
| `TOOL-060.09` | 187–219 | ناحیه | `اعمال` | 0 | ⬜ |
| `TOOL-060.10` | 220–236 | ناحیه | `اثبات بعد از اعمال` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-061</b> — <code>tools/fix_ticket_assignments.php</code> (174 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-061.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 0 | ⬜ |
| `TOOL-061.02` | 28–30 | ناحیه | `choose_handling_agent در ماژول مرکز درخواست‌هاست، ` | 0 | ⬜ |
| `TOOL-061.03` | 31–31 | ناحیه | `کنسول ویندوز (cmd) با code page 65001 کار می‌کند؛ ` | 0 | ⬜ |
| `TOOL-061.04` | 32–50 | ناحیه | `به‌صورت Ù†Ø§Ù… نمایش داده می‌شوند و کاربر فکر می‌ک` | 5 | ⬜ |
| `TOOL-061.05` | 51–174 | ناحیه | `۱) تیکت‌های بازی که مسئولشان ادمین اصلی است ولی در` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-062</b> — <code>tools/food_system_router.php</code> (28 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-062.01` | 1–11 | ناحیه | `بلوک رویه‌ای 1-11` | 2 | ⬜ |
| `TOOL-062.02` | 12–17 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `TOOL-062.03` | 18–20 | ناحیه | `مسیر: food-order` | 0 | ⬜ |
| `TOOL-062.04` | 21–28 | ناحیه | `مسیر: food-ticket` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-063</b> — <code>tools/food_system_seed.php</code> (134 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-063.01` | 1–85 | ناحیه | `بلوک رویه‌ای 1-85` | 0 | ⬜ |
| `TOOL-063.02` | 86–86 | ناحیه | `Prebuild application sessions instead of using the` | 0 | ⬜ |
| `TOOL-063.03` | 87–134 | ناحیه | `unrelated client-inventory collection. Each sessio` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-064</b> — <code>tools/food_system_stress.py</code> (479 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-064.01` | 1–25 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-064.02` | 26–26 | بلوک | `Metrics` | 0 | ⬜ |
| `TOOL-064.03` | 27–31 | بلوک | `__init__` | 0 | ⬜ |
| `TOOL-064.04` | 32–37 | بلوک | `add` | 0 | ⬜ |
| `TOOL-064.05` | 38–42 | بلوک | `fail` | 0 | ⬜ |
| `TOOL-064.06` | 43–49 | بلوک | `api_path` | 1 | ⬜ |
| `TOOL-064.07` | 50–85 | بلوک | `request` | 0 | ⬜ |
| `TOOL-064.08` | 86–90 | بلوک | `expect` | 0 | ⬜ |
| `TOOL-064.09` | 91–102 | بلوک | `parse_json` | 0 | ⬜ |
| `TOOL-064.10` | 103–111 | بلوک | `find_order` | 0 | ⬜ |
| `TOOL-064.11` | 112–149 | بلوک | `self_order_flow` | 0 | ⬜ |
| `TOOL-064.12` | 150–155 | بلوک | `manager_crud` | 0 | ⬜ |
| `TOOL-064.13` | 156–233 | بلوک | `post` | 1 | ⬜ |
| `TOOL-064.14` | 234–237 | بلوک | `text_preview` | 0 | ⬜ |
| `TOOL-064.15` | 238–276 | بلوک | `bulk_lifecycle` | 0 | ⬜ |
| `TOOL-064.16` | 277–301 | بلوک | `build_read_routes` | 0 | ⬜ |
| `TOOL-064.17` | 302–321 | بلوک | `manager_read_worker` | 0 | ⬜ |
| `TOOL-064.18` | 322–331 | بلوک | `percentile` | 0 | ⬜ |
| `TOOL-064.19` | 332–401 | بلوک | `write_report` | 0 | ⬜ |
| `TOOL-064.20` | 402–479 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-065</b> — <code>tools/food_system_verify.php</code> (96 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-065.01` | 1–96 | ناحیه | `بلوک رویه‌ای 1-96` | 5 | ⬜ |

</details>

<details>
<summary><b>TOOL-066</b> — <code>tools/full_system_route_smoke.py</code> (697 خط، 18 آدرس، بازبینی‌شده 0/18)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-066.01` | 1–26 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-066.02` | 27–27 | بلوک | `RouteSmoke` | 0 | ⬜ |
| `TOOL-066.03` | 28–37 | بلوک | `__init__` | 0 | ⬜ |
| `TOOL-066.04` | 38–67 | بلوک | `request` | 0 | ⬜ |
| `TOOL-066.05` | 68–83 | بلوک | `login` | 0 | ⬜ |
| `TOOL-066.06` | 84–107 | بلوک | `pages` | 0 | ⬜ |
| `TOOL-066.07` | 108–114 | بلوک | `request_xlsx` | 0 | ⬜ |
| `TOOL-066.08` | 115–166 | بلوک | `exports_and_cron_boundaries` | 0 | ⬜ |
| `TOOL-066.09` | 167–184 | بلوک | `get_json` | 0 | ⬜ |
| `TOOL-066.10` | 185–243 | بلوک | `apis` | 0 | ⬜ |
| `TOOL-066.11` | 244–325 | بلوک | `role_matrix` | 0 | ⬜ |
| `TOOL-066.12` | 326–381 | بلوک | `inventory_api_lifecycle` | 0 | ⬜ |
| `TOOL-066.13` | 382–387 | بلوک | `_synthetic_iranian_national_code` | 0 | ⬜ |
| `TOOL-066.14` | 388–458 | بلوک | `traffic_control_lifecycle` | 1 | ⬜ |
| `TOOL-066.15` | 459–496 | بلوک | `food_ticket_group_lifecycle` | 1 | ⬜ |
| `TOOL-066.16` | 497–509 | بلوک | `requester_permission_checks` | 0 | ⬜ |
| `TOOL-066.17` | 510–562 | بلوک | `food_order_lifecycle` | 0 | ⬜ |
| `TOOL-066.18` | 563–697 | بلوک | `main` | 2 | ⬜ |

</details>

<details>
<summary><b>TOOL-067</b> — <code>tools/install-1.50.php</code> (92 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-067.01` | 1–42 | ناحیه | `بلوک رویه‌ای 1-42` | 1 | ⬜ |
| `TOOL-067.02` | 43–49 | ناحیه | `اگر دیتابیس از قبل کامل است، چیزی تغییر نمی‌کند و ` | 0 | ⬜ |
| `TOOL-067.03` | 50–54 | ناحیه | `۱. پشتیبان` | 4 | ⬜ |
| `TOOL-067.04` | 55–75 | ناحیه | `۲. نقش‌ها: مقادیر فعلی ENUM حفظ می‌شود و فقط نقش‌ه` | 4 | ⬜ |
| `TOOL-067.05` | 76–83 | ناحیه | `۳. ستون اجبار تغییر رمز` | 4 | ⬜ |
| `TOOL-067.06` | 84–92 | ناحیه | `۴. بررسی نهایی` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-068</b> — <code>tools/lint-php-syntax.php</code> (25 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-068.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-069</b> — <code>tools/logging_stress_worker.php</code> (107 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-069.01` | 1–107 | ناحیه | `بلوک رویه‌ای 1-107` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-070</b> — <code>tools/migrate-legacy-attachments.php</code> (42 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-070.01` | 1–42 | ناحیه | `بلوک رویه‌ای 1-42` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-071</b> — <code>tools/normalize_asset_hostnames.php</code> (203 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-071.01` | 1–46 | ناحیه | `بلوک رویه‌ای 1-46` | 0 | ⬜ |
| `TOOL-071.02` | 47–47 | ناحیه | `from-inventory : نام واقعی را از hardware_json برد` | 0 | ⬜ |
| `TOOL-071.03` | 48–61 | ناحیه | `پیش از این، فقط «نمایش» تمیز می‌شد و نام جعلی در پ` | 0 | ⬜ |
| `TOOL-071.04` | 62–84 | تابع | `normalize_should_rewrite` | 0 | ⬜ |
| `TOOL-071.05` | 85–108 | ناحیه | `بلوک رویه‌ای 85-108` | 0 | ⬜ |
| `TOOL-071.06` | 109–109 | ناحیه | `نام واقعی از hardware_json` | 0 | ⬜ |
| `TOOL-071.07` | 110–110 | ناحیه | `اگر اسکن دامنه نام واقعی ویندوز را ثبت کرده باشد (` | 0 | ⬜ |
| `TOOL-071.08` | 111–203 | ناحیه | ``hardware.computer.Name`)، همان اولویت دارد و جای ` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-072</b> — <code>tools/php_syntax_check.py</code> (30 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-072.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-072.02` | 16–30 | بلوک | `walk` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-073</b> — <code>tools/preview_asset_names.php</code> (126 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-073.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-073.02` | 22–42 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-073.03` | 43–44 | ناحیه | `بلوک رویه‌ای 43-44` | 0 | ⬜ |
| `TOOL-073.04` | 45–48 | تابع | `setting` | 0 | ⬜ |
| `TOOL-073.05` | 49–52 | تابع | `e` | 0 | ⬜ |
| `TOOL-073.06` | 53–126 | ناحیه | `بلوک رویه‌ای 53-126` | 5 | ⬜ |

</details>

<details>
<summary><b>TOOL-074</b> — <code>tools/preview_food_import.php</code> (68 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-074.01` | 1–68 | ناحیه | `بلوک رویه‌ای 1-68` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-075</b> — <code>tools/prewindow_check.php</code> (357 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-075.01` | 1–45 | ناحیه | `بلوک رویه‌ای 1-45` | 5 | ⬜ |
| `TOOL-075.02` | 46–77 | ناحیه | `آرگومان‌ها` | 0 | ⬜ |
| `TOOL-075.03` | 78–107 | ناحیه | `۱) پنجرهٔ کاری: فردا ساعت ۱۱ در باز می‌شود؟` | 4 | ⬜ |
| `TOOL-075.04` | 108–186 | ناحیه | `حالت مشترک: خروجی دقیقاً همان گزارش پنل (حکم یکسان` | 0 | ⬜ |
| `TOOL-075.05` | 187–196 | ناحیه | `۲) پردازشگر و Worker` | 0 | ⬜ |
| `TOOL-075.06` | 197–220 | ناحیه | `heartbeat: Worker باید هر چند ثانیه/دقیقه بنویسد` | 1 | ⬜ |
| `TOOL-075.07` | 221–289 | ناحیه | `۳) صف چاپ: چه چیزی فردا چاپ می‌شود؟` | 6 | ⬜ |
| `TOOL-075.08` | 290–311 | ناحیه | `۴) پرینتر: فقط تست TCP، بدون ارسال بایت` | 1 | ⬜ |
| `TOOL-075.09` | 312–340 | ناحیه | `۵) آزمایش خشکِ مسیر چاپ (رندر فیش، بدون ارسال)` | 0 | ⬜ |
| `TOOL-075.10` | 341–357 | ناحیه | `۶) حکم نهایی` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-076</b> — <code>tools/print_calibration.php</code> (137 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-076.01` | 1–65 | ناحیه | `بلوک رویه‌ای 1-65` | 0 | ⬜ |
| `TOOL-076.02` | 66–78 | ناحیه | `تنظیمات فعلی و اینکه چه چیزی روی این کارت اثر می‌گ` | 0 | ⬜ |
| `TOOL-076.03` | 79–102 | ناحیه | `ساخت کارت با همان تابع مشترک چاپ (منبع یکتا: food_` | 2 | ⬜ |
| `TOOL-076.04` | 103–112 | ناحیه | `ارسال به چاپگر` | 0 | ⬜ |
| `TOOL-076.05` | 113–137 | ناحیه | `مسیر: windows_share` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-077</b> — <code>tools/print_test.php</code> (51 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-077.01` | 1–51 | ناحیه | `بلوک رویه‌ای 1-51` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-078</b> — <code>tools/render_code_map.py</code> (153 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-078.01` | 1–57 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-078.02` | 58–153 | بلوک | `addr_cell` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-079</b> — <code>tools/repair_source_rows.php</code> (99 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-079.01` | 1–99 | ناحیه | `بلوک رویه‌ای 1-99` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-080</b> — <code>tools/session-boot.py</code> (347 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-080.01` | 1–49 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-080.02` | 50–56 | بلوک | `read` | 0 | ⬜ |
| `TOOL-080.03` | 57–66 | بلوک | `run` | 0 | ⬜ |
| `TOOL-080.04` | 67–67 | بلوک | `git_info` | 0 | ⬜ |
| `TOOL-080.05` | 68–87 | بلوک | `q` | 0 | ⬜ |
| `TOOL-080.06` | 88–105 | بلوک | `extract_section` | 0 | ⬜ |
| `TOOL-080.07` | 106–115 | بلوک | `skill_rows` | 0 | ⬜ |
| `TOOL-080.08` | 116–120 | بلوک | `bullets` | 0 | ⬜ |
| `TOOL-080.09` | 121–137 | بلوک | `refresh_map_if_stale` | 0 | ⬜ |
| `TOOL-080.10` | 138–169 | بلوک | `block_index_status` | 0 | ⬜ |
| `TOOL-080.11` | 170–181 | بلوک | `run_guard_tests` | 0 | ⬜ |
| `TOOL-080.12` | 182–237 | بلوک | `build_context` | 0 | ⬜ |
| `TOOL-080.13` | 238–323 | بلوک | `print_briefing` | 0 | ⬜ |
| `TOOL-080.14` | 324–347 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-081</b> — <code>tools/set_attendance_password.php</code> (110 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-081.01` | 1–110 | ناحیه | `بلوک رویه‌ای 1-110` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-082</b> — <code>tools/set_print_gap.php</code> (42 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-082.01` | 1–42 | ناحیه | `بلوک رویه‌ای 1-42` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-083</b> — <code>tools/sync_domain_users.php</code> (41 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-083.01` | 1–41 | ناحیه | `بلوک رویه‌ای 1-41` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-084</b> — <code>tools/system_audit.php</code> (93 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-084.01` | 1–93 | ناحیه | `بلوک رویه‌ای 1-93` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-085</b> — <code>tools/system_loadtest.php</code> (711 خط، 25 آدرس، بازبینی‌شده 0/25)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-085.01` | 1–38 | ناحیه | `بلوک رویه‌ای 1-38` | 0 | ⬜ |
| `TOOL-085.02` | 39–61 | ناحیه | `پارامترها` | 0 | ⬜ |
| `TOOL-085.03` | 62–81 | ناحیه | `بوت‌استرپ` | 1 | ⬜ |
| `TOOL-085.04` | 82–94 | ناحیه | `ساختار گزارش` | 0 | ⬜ |
| `TOOL-085.05` | 95–100 | تابع | `out` | 3 | ⬜ |
| `TOOL-085.06` | 101–107 | تابع | `head` | 0 | ⬜ |
| `TOOL-085.07` | 108–116 | تابع | `pad` | 0 | ⬜ |
| `TOOL-085.08` | 117–121 | تابع | `row` | 0 | ⬜ |
| `TOOL-085.09` | 122–126 | تابع | `sect` | 0 | ⬜ |
| `TOOL-085.10` | 127–135 | تابع | `safe` | 0 | ⬜ |
| `TOOL-085.11` | 136–139 | ناحیه | `بلوک رویه‌ای 136-139` | 0 | ⬜ |
| `TOOL-085.12` | 140–174 | ناحیه | `بخش A — محیط` | 3 | ⬜ |
| `TOOL-085.13` | 175–175 | ناحیه | `بخش B — کاوش قابلیت‌ها` | 0 | ⬜ |
| `TOOL-085.14` | 176–176 | ناحیه | `این بخش مهم‌ترین قسمت برای تشخیص نسخه است: دقیقاً ` | 0 | ⬜ |
| `TOOL-085.15` | 177–207 | ناحیه | `اصلاحات روی این نصب حاضرند و کدام نیستند.` | 0 | ⬜ |
| `TOOL-085.16` | 208–252 | ناحیه | `بخش C — پایگاه داده و اسکیما` | 5 | ⬜ |
| `TOOL-085.17` | 253–253 | ناحیه | `بخش D — یکپارچگی شمارهٔ درخواست (فقط‌خواندنی)` | 0 | ⬜ |
| `TOOL-085.18` | 254–298 | ناحیه | `این بخش همان باگی را می‌سنجد که بیشترین اثر را روی` | 0 | ⬜ |
| `TOOL-085.19` | 299–299 | ناحیه | `بخش E — کارایی صفحه‌ها (فقط‌خواندنی)` | 0 | ⬜ |
| `TOOL-085.20` | 300–343 | ناحیه | `الگوی کوئری‌های فهرست را با داده‌های واقعی زمان می` | 0 | ⬜ |
| `TOOL-085.21` | 344–371 | ناحیه | `بخش F — ایندکس‌های جدول درخواست` | 0 | ⬜ |
| `TOOL-085.22` | 372–405 | ناحیه | `بخش G — سلامت لاگ و ممیزی` | 6 | ⬜ |
| `TOOL-085.23` | 406–456 | ناحیه | `بخش H — منطق دسترسی (فقط‌خواندنی، بدون نوشتن)` | 0 | ⬜ |
| `TOOL-085.24` | 457–677 | ناحیه | `بخش I — حالت نوشتن: ساخت کاربر و درخواست` | **9** | ⬜ |
| `TOOL-085.25` | 678–711 | ناحیه | `جمع‌بندی` | 2 | ⬜ |

</details>

<details>
<summary><b>TOOL-086</b> — <code>tools/test_1_62_97_drift_offline.php</code> (267 خط، 24 آدرس، بازبینی‌شده 0/24)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-086.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `TOOL-086.02` | 27–37 | تابع | `check` | 0 | ⬜ |
| `TOOL-086.03` | 38–50 | ناحیه | `بلوک رویه‌ای 38-50` | 1 | ⬜ |
| `TOOL-086.04` | 51–63 | تابع | `zipFile` | 0 | ⬜ |
| `TOOL-086.05` | 64–87 | ناحیه | `بلوک رویه‌ای 64-87` | 0 | ⬜ |
| `TOOL-086.06` | 88–89 | ناحیه | `(الف) بلوک بارگذاری کتابخانه در food-ticket.php` | 0 | ⬜ |
| `TOOL-086.07` | 90–96 | ناحیه | `آزمون جامع worker/چاپ/مدیریت — همان منطقی که ابزار` | 0 | ⬜ |
| `TOOL-086.08` | 97–100 | ناحیه | `(ب) خط مسیر API در نقشهٔ دسترسی` | 0 | ⬜ |
| `TOOL-086.09` | 101–104 | ناحیه | `(ج) بلوک هندلر API` | 0 | ⬜ |
| `TOOL-086.10` | 105–120 | ناحیه | `مسیر: REQUEST_METHOD\|GET\|GET` | 0 | ⬜ |
| `TOOL-086.11` | 121–122 | ناحیه | `(د) بلوک بارگذاری «تغییرات این به‌روزرسانی»` | 0 | ⬜ |
| `TOOL-086.12` | 123–128 | ناحیه | `«تغییرات این به‌روزرسانی» داخل خود پنل (مستقل از i` | 0 | ⬜ |
| `TOOL-086.13` | 129–136 | ناحیه | `(ه) کلید releaseNotes در payload نسخهٔ پنل` | 0 | ⬜ |
| `TOOL-086.14` | 137–151 | ناحیه | `(و) کلید releaseNotes در payload تنظیمات` | 0 | ⬜ |
| `TOOL-086.15` | 152–157 | ناحیه | `بلوک notesRequire یک خط خالی انتهایی جا می‌گذارد؛ ` | 0 | ⬜ |
| `TOOL-086.16` | 158–158 | ناحیه | `بلوک‌های releaseNotes در payloadها یک خط خالی جا م` | 0 | ⬜ |
| `TOOL-086.17` | 159–182 | ناحیه | `(هر دو بلوک با کامنت متفاوت شروع می‌شوند، پس به مت` | 0 | ⬜ |
| `TOOL-086.18` | 183–194 | ناحیه | `کارت آزمون: تابع‌های ST_LABELS/ST_CLASS/selftestBo` | 0 | ⬜ |
| `TOOL-086.19` | 195–196 | ناحیه | `فراخوانی کارت از داخل صفحه را بردار تا خط به حالت ` | 0 | ⬜ |
| `TOOL-086.20` | 197–203 | ناحیه | `دو اکشن کلیک را بردار.` | 0 | ⬜ |
| `TOOL-086.21` | 204–211 | ناحیه | `بلوک بنر «تغییرات این به‌روزرسانی» (تابع releaseNo` | 0 | ⬜ |
| `TOOL-086.22` | 212–219 | ناحیه | `تابع بارگذاری یک‌بارهٔ بنر هم باید برود (جدا از خو` | 0 | ⬜ |
| `TOOL-086.23` | 220–220 | ناحیه | `اکشن بستن بنر — با regex برداشته می‌شود تا با تغیی` | 0 | ⬜ |
| `TOOL-086.24` | 221–267 | ناحیه | `حافظهٔ درون‌برنامه‌ای) نشکند؛ فقط تا پایان همان if` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-087</b> — <code>tools/test_access_coverage_offline.php</code> (273 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-087.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-087.02` | 22–22 | ناحیه | `تزریق حداقلی محیط: DB وجود ندارد` | 0 | ⬜ |
| `TOOL-087.03` | 23–26 | تابع | `db` | 0 | ⬜ |
| `TOOL-087.04` | 28–31 | تابع | `setting` | 0 | ⬜ |
| `TOOL-087.05` | 33–35 | تابع | `save_setting` | 0 | ⬜ |
| `TOOL-087.06` | 37–58 | ناحیه | `پیش‌بررسی: این آزمون باید در ریشهٔ سامانه (کنار in` | 0 | ⬜ |
| `TOOL-087.07` | 59–69 | تابع | `check` | 0 | ⬜ |
| `TOOL-087.08` | 70–90 | ناحیه | `بلوک رویه‌ای 70-90` | 0 | ⬜ |
| `TOOL-087.09` | 91–103 | ناحیه | `بدون دیتابیس، جدول role_permissions آماده نیست ⇒ م` | 0 | ⬜ |
| `TOOL-087.10` | 104–105 | ناحیه | `نقش دست‌نخورده ⇒ پیش‌فرض کد (حتی اگر ردیف‌های قدیم` | 0 | ⬜ |
| `TOOL-087.11` | 106–107 | ناحیه | `نقش ذخیره‌شده ⇒ همان مقادیر فرم، حتی اگر با پیش‌فر` | 0 | ⬜ |
| `TOOL-087.12` | 108–141 | ناحیه | `ذخیرهٔ «هیچ‌کدام» یک تصمیم عمدی است و باید محترم ب` | 1 | ⬜ |
| `TOOL-087.13` | 142–142 | ناحیه | `این فهرست عمدی است: کدهایی که فقط «برچسب» هستند و ` | 0 | ⬜ |
| `TOOL-087.14` | 143–186 | ناحیه | `افزودن کد بی‌اثر جدید یا وصل‌کردن یکی از این‌ها با` | 0 | ⬜ |
| `TOOL-087.15` | 187–187 | ناحیه | `از ۱.۶۲.۱۸: index.php از طریق post_context_begin()` | 0 | ⬜ |
| `TOOL-087.16` | 188–239 | ناحیه | `زنجیرهٔ واقعی سنجیده می‌شود: opener → include http` | 2 | ⬜ |
| `TOOL-087.17` | 240–273 | ناحیه | `صفحات «تغییر و مشکل» و «OLA» در ۲۰۲۶-۱۱-۱۴ به ماژو` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-088</b> — <code>tools/test_alarm_selfheal_offline.php</code> (215 خط، 25 آدرس، بازبینی‌شده 0/25)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-088.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `TOOL-088.02` | 19–32 | تابع | `ok` | 0 | ⬜ |
| `TOOL-088.03` | 33–37 | ناحیه | `بلوک رویه‌ای 33-37` | 0 | ⬜ |
| `TOOL-088.04` | 38–38 | ناحیه | `آژیر سلامت در دو خط، دو جای مختلف است:` | 0 | ⬜ |
| `TOOL-088.05` | 39–39 | ناحیه | `۱.۶۲ → lib/logging-audit.php  (توابع logging_audit` | 0 | ⬜ |
| `TOOL-088.06` | 40–40 | ناحیه | `۱.۶۳ → lib/system-alarms.php  (توابع system_alarm_` | 0 | ⬜ |
| `TOOL-088.07` | 41–62 | ناحیه | `تست باید هر دو را بشناسد، وگرنه در یکی از دو خط با` | 0 | ⬜ |
| `TOOL-088.08` | 63–63 | ناحیه | `این بخش به schema.sql و migrations/ نیاز دارد که د` | 0 | ⬜ |
| `TOOL-088.09` | 64–64 | ناحیه | `نصب نه. پس اگر فایل‌ها نبودند، [SKIP] می‌دهیم نه [` | 0 | ⬜ |
| `TOOL-088.10` | 65–101 | ناحیه | `که test_selftest_offline و test_prewindow_check_of` | 1 | ⬜ |
| `TOOL-088.11` | 102–102 | ناحیه | `ماکت: APP_ROOT نمی‌تواند ثابت باشد، پس تابع را با ` | 0 | ⬜ |
| `TOOL-088.12` | 103–104 | ناحیه | `و منطق را از سورس بازتولید می‌کنیم تا رفتار واقعی ` | 0 | ⬜ |
| `TOOL-088.13` | 105–105 | ناحیه | `الگوی ساخت پوشه بین دو خط متفاوت است (۱.۶۲: mkdir(` | 0 | ⬜ |
| `TOOL-088.14` | 106–106 | ناحیه | `۱.۶۳: mkdir($full, 0750, true) با تلاش دوم 0775). ` | 0 | ⬜ |
| `TOOL-088.15` | 107–115 | ناحیه | `واقعی پوشه در ادامه سنجیده می‌شود — که مهم‌تر از ش` | 0 | ⬜ |
| `TOOL-088.16` | 116–122 | ناحیه | `آزمون واقعی: ساخت پوشه در مسیر موقت` | 1 | ⬜ |
| `TOOL-088.17` | 123–131 | ناحیه | `و پوشه‌ای که والدش وجود ندارد هم با recursive ساخت` | 1 | ⬜ |
| `TOOL-088.18` | 132–152 | تابع | `db` | 0 | ⬜ |
| `TOOL-088.19` | 153–158 | ناحیه | `بلوک رویه‌ای 153-158` | 0 | ⬜ |
| `TOOL-088.20` | 159–162 | ناحیه | `نسخه از آخرین ردیف خوانده می‌شود، نه هارد‌کد` | 0 | ⬜ |
| `TOOL-088.21` | 163–182 | ناحیه | `فراخوانی با آرایهٔ خالی نباید کاری بکند` | 1 | ⬜ |
| `TOOL-088.22` | 183–191 | ناحیه | `فایل bat ویندوزی باید CRLF داشته باشد، وگرنه برخی ` | 0 | ⬜ |
| `TOOL-088.23` | 192–192 | ناحیه | `۱.۶۳ متن هشدار کرون را در system-alarms.php دارد؛ ` | 0 | ⬜ |
| `TOOL-088.24` | 193–205 | ناحیه | `اگر فایل هست، اشاره باید موجود باشد؛ اگر نیست، این` | 0 | ⬜ |
| `TOOL-088.25` | 206–215 | ناحیه | `پاک‌سازی` | 7 | ⬜ |

</details>

<details>
<summary><b>TOOL-089</b> — <code>tools/test_api_contract_offline.php</code> (244 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-089.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `TOOL-089.02` | 29–39 | تابع | `check` | 0 | ⬜ |
| `TOOL-089.03` | 40–132 | ناحیه | `بلوک رویه‌ای 40-132` | 1 | ⬜ |
| `TOOL-089.04` | 133–137 | ناحیه | `مسیرهای ورود عمومی صریحاً پیش از نگاشت مجوز بررسی ` | 0 | ⬜ |
| `TOOL-089.05` | 138–140 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `TOOL-089.06` | 141–143 | ناحیه | `مسیر: login\|sso-login` | 0 | ⬜ |
| `TOOL-089.07` | 144–244 | ناحیه | `مسیر: config` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-090</b> — <code>tools/test_asset_dedupe_offline.php</code> (156 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-090.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 1 | ⬜ |
| `TOOL-090.02` | 20–20 | تابع | `setting` | 0 | ⬜ |
| `TOOL-090.03` | 21–26 | ناحیه | `بلوک رویه‌ای 21-26` | 0 | ⬜ |
| `TOOL-090.04` | 27–37 | تابع | `check` | 0 | ⬜ |
| `TOOL-090.05` | 39–48 | تابع | `source_insert_sql` | 1 | ⬜ |
| `TOOL-090.06` | 49–51 | ناحیه | `بلوک رویه‌ای 49-51` | 0 | ⬜ |
| `TOOL-090.07` | 52–91 | ناحیه | `همان پیاده‌سازی production: نام، سریال، MAC و اتصا` | 0 | ⬜ |
| `TOOL-090.08` | 92–101 | ناحیه | `۱۲۵ ردیف: ۳۶ جفت تکراری که فقط از روی MAC پیدا می‌` | 0 | ⬜ |
| `TOOL-090.09` | 102–122 | ناحیه | `توابع واقعی هویت.` | 0 | ⬜ |
| `TOOL-090.10` | 123–156 | ناحیه | `دقیقاً SQL و پارامترهایی که هر دو مسیر درج تولید د` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-091</b> — <code>tools/test_asset_extraction_offline.php</code> (143 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-091.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 1 | ⬜ |
| `TOOL-091.02` | 26–36 | تابع | `check` | 0 | ⬜ |
| `TOOL-091.03` | 37–58 | ناحیه | `بلوک رویه‌ای 37-58` | 1 | ⬜ |
| `TOOL-091.04` | 59–59 | ناحیه | `بارگذاری اختیاری: اگر این آزمون خارج از درخت کامل ` | 0 | ⬜ |
| `TOOL-091.05` | 60–77 | ناحیه | `جداشده) اجرا شود، بخش‌های ۴ و ۵ رد می‌شوند نه اینک` | 0 | ⬜ |
| `TOOL-091.06` | 78–143 | ناحیه | `خروجی نمونه با همان ساختاری که tools/collect_serve` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-092</b> — <code>tools/test_asset_hostname_cleanup_offline.php</code> (249 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-092.01` | 1–23 | ناحیه | `بلوک رویه‌ای 1-23` | 0 | ⬜ |
| `TOOL-092.02` | 24–27 | تابع | `setting` | 0 | ⬜ |
| `TOOL-092.03` | 28–31 | تابع | `e` | 0 | ⬜ |
| `TOOL-092.04` | 33–57 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-092.05` | 58–68 | ناحیه | `بلوک رویه‌ای 58-68` | 0 | ⬜ |
| `TOOL-092.06` | 69–90 | ناحیه | `۱.۶۳.۱-r17 — الگوی MAC (ستون «نام» آلوده به MAC)` | **8** | ⬜ |
| `TOOL-092.07` | 91–101 | تابع | `check` | 0 | ⬜ |
| `TOOL-092.08` | 103–106 | تابع | `set_style` | 0 | ⬜ |
| `TOOL-092.09` | 107–179 | ناحیه | `بلوک رویه‌ای 107-179` | 0 | ⬜ |
| `TOOL-092.10` | 180–180 | ناحیه | `۱.۶۳.۱-r15 — رفتار عوض شد و آگاهانه. قبلاً این ورو` | 0 | ⬜ |
| `TOOL-092.11` | 181–181 | ناحیه | `برمی‌گرداند و همان IP در ستون «نام» می‌نشست — همان` | 0 | ⬜ |
| `TOOL-092.12` | 182–182 | ناحیه | `داد («client 192.168.1.25»). اسم واقعی ماشین این ن` | 0 | ⬜ |
| `TOOL-092.13` | 183–183 | ناحیه | `سامانه هنگام ورود کاربر ساخته. حالا خالی برمی‌گردد` | 0 | ⬜ |
| `TOOL-092.14` | 184–249 | ناحیه | `Win32_ComputerSystem.Name پر شود و تا آن موقع «نام` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-093</b> — <code>tools/test_asset_name_exact_offline.php</code> (243 خط، 19 آدرس، بازبینی‌شده 0/19)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-093.01` | 1–29 | ناحیه | `بلوک رویه‌ای 1-29` | 0 | ⬜ |
| `TOOL-093.02` | 30–40 | تابع | `chk` | 0 | ⬜ |
| `TOOL-093.03` | 41–46 | ناحیه | `بلوک رویه‌ای 41-46` | 0 | ⬜ |
| `TOOL-093.04` | 47–55 | ناحیه | `نام‌های الگوی کارفرما: mba4-01 تا mba4-150` | 0 | ⬜ |
| `TOOL-093.05` | 56–92 | ناحیه | `نام‌های واقعی دیگر` | 0 | ⬜ |
| `TOOL-093.06` | 93–96 | تابع | `setting` | 0 | ⬜ |
| `TOOL-093.07` | 97–97 | ناحیه | `بلوک رویه‌ای 97-97` | 0 | ⬜ |
| `TOOL-093.08` | 98–101 | تابع | `e` | 0 | ⬜ |
| `TOOL-093.09` | 102–103 | ناحیه | `بلوک رویه‌ای 102-103` | 0 | ⬜ |
| `TOOL-093.10` | 104–136 | ناحیه | `پیش‌فرض (تنظیم ذخیره نشده)` | 0 | ⬜ |
| `TOOL-093.11` | 137–176 | ناحیه | `الگوی کارفرما: هر ۱۵۰ نام باید دقیقاً خودش برگردد` | 0 | ⬜ |
| `TOOL-093.12` | 177–215 | ناحیه | `۱.۶۳.۱-r15 — IP خالص به‌عنوان نام نمایش داده نمی‌ش` | 0 | ⬜ |
| `TOOL-093.13` | 216–216 | ناحیه | `۱.۶۳.۱-r15 — دسترسی نمایشی از asset_display_name ب` | 0 | ⬜ |
| `TOOL-093.14` | 217–217 | ناحیه | `asset_display_name عمداً در حالت «نام واقعی نداریم` | 0 | ⬜ |
| `TOOL-093.15` | 218–218 | ناحیه | `و asset_name_or همان را به برچسب صادقانه «نام ثبت ` | 0 | ⬜ |
| `TOOL-093.16` | 219–225 | ناحیه | `هر دو از یک قاعده تغذیه می‌شوند، پس وجود هرکدام کا` | 0 | ⬜ |
| `TOOL-093.17` | 226–227 | ناحیه | `فرم ثبت تیکت: گزینه‌ها با asset_display_name ساخته` | 0 | ⬜ |
| `TOOL-093.18` | 228–234 | ناحیه | `کد واقعی: echo '<option value="' . (int) $asset['i` | 0 | ⬜ |
| `TOOL-093.19` | 235–243 | ناحیه | `جزئیات شناسنامه هم نام نمایشی دارد (پیش‌تر خام بود` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-094</b> — <code>tools/test_asset_names_offline.php</code> (227 خط، 24 آدرس، بازبینی‌شده 0/24)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-094.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `TOOL-094.02` | 21–31 | تابع | `check` | 0 | ⬜ |
| `TOOL-094.03` | 33–55 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-094.04` | 56–59 | ناحیه | `بلوک رویه‌ای 56-59` | 0 | ⬜ |
| `TOOL-094.05` | 60–61 | ناحیه | `محیط حداقلی: setting() و e() و mb_* (همان چیزهایی ` | 0 | ⬜ |
| `TOOL-094.06` | 62–65 | تابع | `setting` | 0 | ⬜ |
| `TOOL-094.07` | 66–69 | تابع | `e` | 0 | ⬜ |
| `TOOL-094.08` | 70–73 | تابع | `set_style` | 0 | ⬜ |
| `TOOL-094.09` | 75–83 | ناحیه | `ثابت الگوی IPv4 از فایل ماژول (تابع نیست، grabFunc` | 0 | ⬜ |
| `TOOL-094.10` | 84–110 | ناحیه | `۱.۶۳.۱-r17 — الگوی MAC (ستون «نام» آلوده به MAC)` | 4 | ⬜ |
| `TOOL-094.11` | 111–125 | ناحیه | `۱.۶۳.۱-r15 — IP دیگر هرگز در ستون «نام» نمی‌نشیند؛` | 0 | ⬜ |
| `TOOL-094.12` | 126–148 | ناحیه | `۱.۶۳.۳: پیش‌فرض از short_lower به short عوض شد تا ` | 0 | ⬜ |
| `TOOL-094.13` | 149–149 | ناحیه | `۱.۶۳.۱-r15 — دسترسی نمایشی از asset_display_name ب` | 0 | ⬜ |
| `TOOL-094.14` | 150–150 | ناحیه | `asset_display_name در حالت «نام واقعی نداریم» عمدا` | 0 | ⬜ |
| `TOOL-094.15` | 151–184 | ناحیه | `سلول رابط کاربری خالی می‌شد. این دو تابع از یک قاع` | 0 | ⬜ |
| `TOOL-094.16` | 185–185 | ناحیه | `۱.۶۳.۳: پیش‌فرض «حفظ حروف اصلی» است تا نام با برچس` | 0 | ⬜ |
| `TOOL-094.17` | 186–186 | ناحیه | `برای سنجش «پیش‌فرض»، باید تنظیم ذخیره‌شده برداشته ` | 0 | ⬜ |
| `TOOL-094.18` | 187–199 | ناحیه | `مقدار قبلی (short_lower) را می‌سنجد نه رفتار پیش‌ف` | 0 | ⬜ |
| `TOOL-094.19` | 200–206 | ناحیه | `پیش‌فرض ۱.۶۳.۳ = حفظ حروف؛ پس «CLIENT» با همان حرو` | 0 | ⬜ |
| `TOOL-094.20` | 207–207 | ناحیه | `۱.۶۳.۱-r15 — رفتار عوض شد و **آگاهانه**: قبلاً این` | 0 | ⬜ |
| `TOOL-094.21` | 208–208 | ناحیه | `تبدیل می‌شد و همان IP در ستون «نام» می‌نشست. کاربر` | 0 | ⬜ |
| `TOOL-094.22` | 209–209 | ناحیه | `به شکل «client 192.168.1.25» می‌بیند. اسم واقعی ما` | 0 | ⬜ |
| `TOOL-094.23` | 210–210 | ناحیه | `خودِ سامانه هنگام ورود کاربر ساخته. حالا خالی برمی` | 0 | ⬜ |
| `TOOL-094.24` | 211–227 | ناحیه | `«نام ثبت نشده» و از Win32_ComputerSystem.Name پر ش` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-095</b> — <code>tools/test_asset_profile_offline.php</code> (316 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-095.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `TOOL-095.02` | 16–26 | تابع | `check` | 0 | ⬜ |
| `TOOL-095.03` | 28–37 | ناحیه | `استاب‌های حداقلی تا فایل‌های واقعی بدون DB بارگذار` | 0 | ⬜ |
| `TOOL-095.04` | 38–38 | تابع | `asset_profile_printer_kind` | 0 | ⬜ |
| `TOOL-095.05` | 39–40 | ناحیه | `بلوک رویه‌ای 39-40` | 0 | ⬜ |
| `TOOL-095.06` | 41–134 | تابع | `sample_powershell_payload` | 0 | ⬜ |
| `TOOL-095.07` | 135–137 | ناحیه | `بلوک رویه‌ای 135-137` | 0 | ⬜ |
| `TOOL-095.08` | 138–138 | ناحیه | `در مسیر واقعی دامنه، نام کامپیوتر پیش از فراخوانی ` | 0 | ⬜ |
| `TOOL-095.09` | 139–305 | ناحیه | `در مسیر «همین سرور» از gethostname استفاده می‌شود؛` | 1 | ⬜ |
| `TOOL-095.10` | 306–316 | ناحیه | `فیلدهایی که با این payload نمونه طبعاً خالی می‌مان` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-096</b> — <code>tools/test_assignee_callable_offline.php</code> (150 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-096.01` | 1–22 | ناحیه | `بلوک رویه‌ای 1-22` | 0 | ⬜ |
| `TOOL-096.02` | 23–36 | تابع | `ok` | 0 | ⬜ |
| `TOOL-096.03` | 37–60 | ناحیه | `بلوک رویه‌ای 37-60` | 0 | ⬜ |
| `TOOL-096.04` | 61–64 | ناحیه | `محیط حداقلی برای بارگذاری auth-scope` | 0 | ⬜ |
| `TOOL-096.05` | 65–68 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `TOOL-096.06` | 69–72 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `TOOL-096.07` | 73–73 | تابع | `is_it_agent` | 0 | ⬜ |
| `TOOL-096.08` | 74–74 | تابع | `is_staff_role` | 0 | ⬜ |
| `TOOL-096.09` | 75–75 | تابع | `user_can` | 0 | ⬜ |
| `TOOL-096.10` | 76–76 | تابع | `ticket_notification_user_can` | 0 | ⬜ |
| `TOOL-096.11` | 77–77 | تابع | `cfg` | 0 | ⬜ |
| `TOOL-096.12` | 78–97 | ناحیه | `بلوک رویه‌ای 78-97` | 0 | ⬜ |
| `TOOL-096.13` | 98–109 | ناحیه | `و با نام تابع پیش‌فرض` | 0 | ⬜ |
| `TOOL-096.14` | 110–121 | ناحیه | `و با callable واقعی` | 0 | ⬜ |
| `TOOL-096.15` | 122–150 | ناحیه | `و نام تابع ناموجود → fallback، نه خطا` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-097</b> — <code>tools/test_backfill_auto_receipts_offline.php</code> (272 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-097.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-097.02` | 4–5 | ناحیه | `آزمون آفلاین «ثبت خودکار فیش روزهای گذشته».` | 0 | ⬜ |
| `TOOL-097.03` | 6–6 | ناحیه | `چه چیزی را تضمین می‌کند؟` | 0 | ⬜ |
| `TOOL-097.04` | 7–7 | ناحیه | `۱) مسیر API فقط با مجوز edit منو (food.menu_edit) ` | 0 | ⬜ |
| `TOOL-097.05` | 8–8 | ناحیه | `۲) اعتبارسنجی بازه: تاریخ نامعتبر، برعکس، و بیش از` | 0 | ⬜ |
| `TOOL-097.06` | 9–9 | ناحیه | `۳) روز جاری هرگز در بازهٔ خودکار پذیرفته نمی‌شود (` | 0 | ⬜ |
| `TOOL-097.07` | 10–10 | ناحیه | `۴) موتور مشترک (food_ticket_auto_receipts_run) هما` | 0 | ⬜ |
| `TOOL-097.08` | 11–11 | ناحیه | `ناهار فقط تعطیل، غیبت رد، فیش واقعی رد، کلید یکتا ` | 0 | ⬜ |
| `TOOL-097.09` | 12–13 | ناحیه | `۵) ابزار CLI و کارت پنل، هر دو همین تابع مشترک را ` | 0 | ⬜ |
| `TOOL-097.10` | 14–19 | ناحیه | `دیتابیس SQLite درون‌حافظه است؛ هیچ اتصال واقعی و ه` | 0 | ⬜ |
| `TOOL-097.11` | 20–30 | تابع | `ok` | 0 | ⬜ |
| `TOOL-097.12` | 32–54 | تابع | `pick` | 0 | ⬜ |
| `TOOL-097.13` | 55–73 | ناحیه | `بلوک رویه‌ای 55-73` | 0 | ⬜ |
| `TOOL-097.14` | 74–74 | ناحیه | `خواندن ساده برای اطمینان از اینکه نام مسیر داخل فض` | 0 | ⬜ |
| `TOOL-097.15` | 75–83 | ناحیه | `وگرنه به ماژول برنامهٔ غذایی پاس می‌شود و هرگز به ` | 0 | ⬜ |
| `TOOL-097.16` | 84–130 | ناحیه | `هندلر باید تابع واقعی سامانه را صدا بزند، نه منطق ` | 0 | ⬜ |
| `TOOL-097.17` | 131–131 | ناحیه | `فایل موتور یک‌بار و فقط یک‌بار بارگذاری می‌شود؛ fo` | 0 | ⬜ |
| `TOOL-097.18` | 132–133 | ناحیه | `هم داخل همان فایل است، پس نه eval لازم است نه تعری` | 0 | ⬜ |
| `TOOL-097.19` | 134–137 | تابع | `food_ticket_holiday` | 0 | ⬜ |
| `TOOL-097.20` | 138–139 | ناحیه | `بلوک رویه‌ای 138-139` | 0 | ⬜ |
| `TOOL-097.21` | 140–142 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-097.22` | 143–172 | ناحیه | `بلوک رویه‌ای 143-172` | 0 | ⬜ |
| `TOOL-097.23` | 173–173 | ناحیه | `ساختار مو‌به‌مو مطابق food_ticket_auto_receipt_sch` | 0 | ⬜ |
| `TOOL-097.24` | 174–197 | ناحیه | `همان هفت جدول و همان ستون‌هایی که موتور می‌خواهد.` | 5 | ⬜ |
| `TOOL-097.25` | 198–206 | ناحیه | `سفارش‌ها: ۱ نوامبر (یکشنبه) دو شام، ۵ نوامبر (پنجش` | 5 | ⬜ |
| `TOOL-097.26` | 207–210 | تابع | `db` | 0 | ⬜ |
| `TOOL-097.27` | 211–226 | ناحیه | `بلوک رویه‌ای 211-226` | 0 | ⬜ |
| `TOOL-097.28` | 227–231 | ناحیه | `روز کاری: ناهار نباید ثبت شود.` | 0 | ⬜ |
| `TOOL-097.29` | 232–234 | ناحیه | `فیش واقعی چاپ‌شده: نباید دوباره ساخته شود.` | 5 | ⬜ |
| `TOOL-097.30` | 235–272 | ناحیه | `۵ نوامبر را تعطیل علامت بزن تا مسیر ناهار آزموده ش` | 5 | ⬜ |

</details>

<details>
<summary><b>TOOL-098</b> — <code>tools/test_brand_neutral_offline.php</code> (150 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-098.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 0 | ⬜ |
| `TOOL-098.02` | 28–39 | تابع | `check` | 0 | ⬜ |
| `TOOL-098.03` | 40–150 | ناحیه | `بلوک رویه‌ای 40-150` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-099</b> — <code>tools/test_cd_dvd_user_picker_offline.php</code> (69 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-099.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-099.02` | 22–32 | تابع | `check` | 0 | ⬜ |
| `TOOL-099.03` | 33–69 | ناحیه | `بلوک رویه‌ای 33-69` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-100</b> — <code>tools/test_column_detect_offline.php</code> (285 خط، 19 آدرس، بازبینی‌شده 0/19)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-100.01` | 1–30 | ناحیه | `بلوک رویه‌ای 1-30` | 0 | ⬜ |
| `TOOL-100.02` | 31–31 | ناحیه | `Isolated functions log expected ALTER failures thr` | 0 | ⬜ |
| `TOOL-100.03` | 32–35 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-100.04` | 37–48 | تابع | `check` | 0 | ⬜ |
| `TOOL-100.05` | 50–72 | تابع | `grab` | 0 | ⬜ |
| `TOOL-100.06` | 74–96 | کلاس | `FakeStatement` | 0 | ⬜ |
| `TOOL-100.07` | 98–130 | کلاس | `FakeDb` | 4 | ⬜ |
| `TOOL-100.08` | 131–132 | ناحیه | `بلوک رویه‌ای 131-132` | 0 | ⬜ |
| `TOOL-100.09` | 133–137 | تابع | `db` | 0 | ⬜ |
| `TOOL-100.10` | 138–144 | ناحیه | `بلوک رویه‌ای 138-144` | 4 | ⬜ |
| `TOOL-100.11` | 145–146 | ناحیه | `دیتابیس زنده در دسترس نیست → مسیر دوم (db_table_co` | 0 | ⬜ |
| `TOOL-100.12` | 147–151 | تابع | `db_table_columns` | 0 | ⬜ |
| `TOOL-100.13` | 152–161 | ناحیه | `بلوک رویه‌ای 152-161` | 0 | ⬜ |
| `TOOL-100.14` | 162–162 | ناحیه | `تابع قبلی قابل بازتعریف نیست؛ همان تابع از مسیر دو` | 0 | ⬜ |
| `TOOL-100.15` | 163–164 | ناحیه | `(این جدول در آزمون‌های آفلاین با SQLite ساخته می‌ش` | 0 | ⬜ |
| `TOOL-100.16` | 165–235 | ناحیه | `شبیه‌سازی شکل فهرست با یک آرایه موقت: تابع با (arr` | 4 | ⬜ |
| `TOOL-100.17` | 236–247 | ناحیه | `۷-الف) ستون نیست، ALTER موفق ⇒ باید true برگردد (ق` | 0 | ⬜ |
| `TOOL-100.18` | 248–254 | ناحیه | `۷-ب) ستون هست ⇒ هیچ ALTER نباید زده شود (سناریوی و` | 0 | ⬜ |
| `TOOL-100.19` | 255–285 | ناحیه | `۷-ج) ستون نیست و دسترسی ALTER هم نیست ⇒ پیام روشن ` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-101</b> — <code>tools/test_cron_synthetic.php</code> (214 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-101.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `TOOL-101.02` | 26–34 | تابع | `cron_assert` | 0 | ⬜ |
| `TOOL-101.03` | 36–53 | تابع | `cron_run` | 4 | ⬜ |
| `TOOL-101.04` | 55–80 | تابع | `cron_insert_ticket` | 6 | ⬜ |
| `TOOL-101.05` | 82–89 | تابع | `cron_run_assert` | 0 | ⬜ |
| `TOOL-101.06` | 90–214 | ناحیه | `بلوک رویه‌ای 90-214` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-102</b> — <code>tools/test_date_input_offline.php</code> (153 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-102.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-102.02` | 22–59 | تابع | `extractFunction` | 0 | ⬜ |
| `TOOL-102.03` | 60–64 | ناحیه | `بلوک رویه‌ای 60-64` | 0 | ⬜ |
| `TOOL-102.04` | 65–67 | ناحیه | `مبدل خود سامانه با نام مرجع (تا مسیر پشتیبان محلی ` | 4 | ⬜ |
| `TOOL-102.05` | 68–75 | ناحیه | `توابع فایل فیش غذا (food_ticket_parse_date در نبود` | 4 | ⬜ |
| `TOOL-102.06` | 76–86 | تابع | `check` | 0 | ⬜ |
| `TOOL-102.07` | 87–153 | ناحیه | `بلوک رویه‌ای 87-153` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-103</b> — <code>tools/test_delivery_flow_offline.php</code> (222 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-103.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `TOOL-103.02` | 29–40 | تابع | `check` | 0 | ⬜ |
| `TOOL-103.03` | 41–42 | ناحیه | `بلوک رویه‌ای 41-42` | 0 | ⬜ |
| `TOOL-103.04` | 43–79 | کلاس | `MysqlishSqlite` | 4 | ⬜ |
| `TOOL-103.05` | 80–91 | ناحیه | `بلوک رویه‌ای 80-91` | 5 | ⬜ |
| `TOOL-103.06` | 92–96 | تابع | `db` | 0 | ⬜ |
| `TOOL-103.07` | 98–107 | تابع | `db_table_columns` | 0 | ⬜ |
| `TOOL-103.08` | 108–110 | ناحیه | `بلوک رویه‌ای 108-110` | 0 | ⬜ |
| `TOOL-103.09` | 111–114 | تابع | `activity_log` | 0 | ⬜ |
| `TOOL-103.10` | 116–138 | تابع | `grab` | 0 | ⬜ |
| `TOOL-103.11` | 139–198 | ناحیه | `بلوک رویه‌ای 139-198` | 5 | ⬜ |
| `TOOL-103.12` | 199–222 | ناحیه | `در SQLite امکان ALTER ADD COLUMN هست → سامانه خودش` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-104</b> — <code>tools/test_employees_import_offline.php</code> (276 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-104.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `TOOL-104.02` | 27–37 | تابع | `check` | 0 | ⬜ |
| `TOOL-104.03` | 38–40 | ناحیه | `بلوک رویه‌ای 38-40` | 0 | ⬜ |
| `TOOL-104.04` | 41–41 | ناحیه | `تابع bootstrap که ایمپورت برای نرمال‌سازی کد پرسنل` | 0 | ⬜ |
| `TOOL-104.05` | 42–47 | تابع | `normalize_employee_number` | 0 | ⬜ |
| `TOOL-104.06` | 48–50 | ناحیه | `بلوک رویه‌ای 48-50` | 0 | ⬜ |
| `TOOL-104.07` | 51–73 | تابع | `db` | 4 | ⬜ |
| `TOOL-104.08` | 74–76 | ناحیه | `بلوک رویه‌ای 74-76` | 0 | ⬜ |
| `TOOL-104.09` | 77–120 | تابع | `make_xlsx` | 0 | ⬜ |
| `TOOL-104.10` | 121–276 | ناحیه | `بلوک رویه‌ای 121-276` | **14** | ⬜ |

</details>

<details>
<summary><b>TOOL-105</b> — <code>tools/test_expert_scope_runtime.php</code> (81 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-105.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-105.02` | 22–22 | ناحیه | `اگر پایگاه‌داده در دسترس نیست، این تست باید **رد ش` | 0 | ⬜ |
| `TOOL-105.03` | 23–23 | ناحیه | `دلیل: در CI و روی کلون تازه، سرویس دیتابیس معمولاً` | 0 | ⬜ |
| `TOOL-105.04` | 24–43 | ناحیه | `کل مجموعهٔ آزمون‌ها را قرمز می‌کند، در حالی که هیچ` | 0 | ⬜ |
| `TOOL-105.05` | 44–44 | تابع | `ok` | 0 | ⬜ |
| `TOOL-105.06` | 45–45 | تابع | `setset` | 1 | ⬜ |
| `TOOL-105.07` | 46–81 | ناحیه | `بلوک رویه‌ای 46-81` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-106</b> — <code>tools/test_explain_alarms_offline.php</code> (97 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-106.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 1 | ⬜ |
| `TOOL-106.02` | 19–29 | تابع | `check` | 0 | ⬜ |
| `TOOL-106.03` | 30–40 | ناحیه | `بلوک رویه‌ای 30-40` | 3 | ⬜ |
| `TOOL-106.04` | 41–41 | ناحیه | `فقط «کد» را می‌سنجیم نه متن توضیحی: کامنت‌های // و` | 0 | ⬜ |
| `TOOL-106.05` | 42–44 | ناحیه | `را کنار می‌گذاریم، بعد دنبال SQL نوشتنی و توابع نو` | 0 | ⬜ |
| `TOOL-106.06` | 45–51 | ناحیه | `fwrite(STDERR, ...) نوشتن در سیستم نیست، فقط پیام ` | 4 | ⬜ |
| `TOOL-106.07` | 52–97 | ناحیه | `SQL نوشتنی فقط اگر خارج از رشتهٔ توضیحی باشد (در e` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-107</b> — <code>tools/test_food_dupes_offline.php</code> (168 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-107.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-107.02` | 4–4 | ناحیه | `آزمون آفلاین: (۱) خطای ltrim با کلید عددی در نقشهٔ` | 0 | ⬜ |
| `TOOL-107.03` | 5–8 | ناحیه | `توابع از فایل‌های اصلی استخراج می‌شوند و دیتابیس S` | 0 | ⬜ |
| `TOOL-107.04` | 9–29 | تابع | `extract_php_function` | 0 | ⬜ |
| `TOOL-107.05` | 30–39 | ناحیه | `بلوک رویه‌ای 30-39` | 4 | ⬜ |
| `TOOL-107.06` | 40–43 | تابع | `db` | 0 | ⬜ |
| `TOOL-107.07` | 44–47 | تابع | `food_order_schema_ensure` | 0 | ⬜ |
| `TOOL-107.08` | 48–51 | تابع | `cfg` | 0 | ⬜ |
| `TOOL-107.09` | 52–55 | تابع | `lac_default_hash` | 0 | ⬜ |
| `TOOL-107.10` | 56–70 | ناحیه | `بلوک رویه‌ای 56-70` | 4 | ⬜ |
| `TOOL-107.11` | 71–81 | تابع | `check` | 0 | ⬜ |
| `TOOL-107.12` | 83–100 | ناحیه | `بخش ۱: نقشهٔ سفارش با کلید عددی و کد ملی تکراری (ق` | 5 | ⬜ |
| `TOOL-107.13` | 101–168 | ناحیه | `بخش ۲: کد ملی یکتا در ثبت و ویرایش کارمند` | **10** | ⬜ |

</details>

<details>
<summary><b>TOOL-108</b> — <code>tools/test_food_group_logic_offline.php</code> (133 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-108.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `TOOL-108.02` | 19–20 | ناحیه | `جایگزین‌های حداقلی توابع کمکی (همان قرارداد food-t` | 0 | ⬜ |
| `TOOL-108.03` | 21–29 | تابع | `food_ticket_normalize_digits` | 0 | ⬜ |
| `TOOL-108.04` | 30–31 | ناحیه | `بلوک رویه‌ای 30-31` | 0 | ⬜ |
| `TOOL-108.05` | 32–46 | تابع | `food_ticket_code` | 0 | ⬜ |
| `TOOL-108.06` | 47–48 | ناحیه | `بلوک رویه‌ای 47-48` | 0 | ⬜ |
| `TOOL-108.07` | 49–52 | تابع | `food_ticket_digits` | 0 | ⬜ |
| `TOOL-108.08` | 53–82 | ناحیه | `بلوک رویه‌ای 53-82` | 0 | ⬜ |
| `TOOL-108.09` | 83–99 | ناحیه | `۱) محاسبهٔ حاضر/غایب/واجد شرایط/بدون سفارش/فیش‌گرف` | 0 | ⬜ |
| `TOOL-108.10` | 100–103 | ناحیه | `۲) عضوی که قبلاً فیش گرفته دوباره واجد شرایط نمی‌ش` | 0 | ⬜ |
| `TOOL-108.11` | 104–110 | ناحیه | `۳) استقلال تاریخ‌ها: با همان اعضا و بدون غیبت، در ` | 0 | ⬜ |
| `TOOL-108.12` | 111–114 | ناحیه | `۴) خالص بودن تابع: اجرای دوباره با همان ورودی‌ها ن` | 0 | ⬜ |
| `TOOL-108.13` | 115–118 | ناحیه | `۵) گروه بدون عضو` | 0 | ⬜ |
| `TOOL-108.14` | 119–122 | ناحیه | `۶) غیبت بر سفارش اولویت دارد (کسی که سفارش دارد ول` | 0 | ⬜ |
| `TOOL-108.15` | 123–125 | ناحیه | `۷) نرمال‌سازی کد ملی (فارسی/صفر ابتدایی) در کلید ف` | 0 | ⬜ |
| `TOOL-108.16` | 126–126 | ناحیه | `نکته: موتور فیش (food_ticket_decide_punch) هم از f` | 0 | ⬜ |
| `TOOL-108.17` | 127–133 | ناحیه | `حفظ می‌کند؛ پس کلیدهای گروهی و فردی دقیقاً یکسان س` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-109</b> — <code>tools/test_food_group_representative.php</code> (517 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-109.01` | 1–517 | ناحیه | `بلوک رویه‌ای 1-517` | **16** | ⬜ |

</details>

<details>
<summary><b>TOOL-110</b> — <code>tools/test_food_groups.php</code> (205 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-110.01` | 1–205 | ناحیه | `بلوک رویه‌ای 1-205` | **10** | ⬜ |

</details>

<details>
<summary><b>TOOL-111</b> — <code>tools/test_food_guest_request_deadline_offline.php</code> (80 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-111.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-111.02` | 4–6 | ناحیه | `آزمون آفلاین مهلت ثبت سفارش مهمان در همان روز؛ بدو` | 0 | ⬜ |
| `TOOL-111.03` | 7–27 | تابع | `extract_php_function` | 0 | ⬜ |
| `TOOL-111.04` | 28–29 | ناحیه | `بلوک رویه‌ای 28-29` | 0 | ⬜ |
| `TOOL-111.05` | 30–33 | تابع | `setting` | 0 | ⬜ |
| `TOOL-111.06` | 34–48 | ناحیه | `بلوک رویه‌ای 34-48` | 4 | ⬜ |
| `TOOL-111.07` | 49–54 | تابع | `check` | 0 | ⬜ |
| `TOOL-111.08` | 55–58 | تابع | `moment` | 0 | ⬜ |
| `TOOL-111.09` | 59–80 | ناحیه | `بلوک رویه‌ای 59-80` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-112</b> — <code>tools/test_food_guest_requests.php</code> (141 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-112.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-112.02` | 4–4 | ناحیه | `آزمون واقعی (نیاز به دیتابیس تست و ITSM_CONFIG_PAT` | 0 | ⬜ |
| `TOOL-112.03` | 5–17 | ناحیه | `لغو، گزارش با فیلتر معاونت، و سقف حالت «درخواست مه` | 0 | ⬜ |
| `TOOL-112.04` | 18–23 | تابع | `check` | 0 | ⬜ |
| `TOOL-112.05` | 24–32 | تابع | `expect_error` | 0 | ⬜ |
| `TOOL-112.06` | 33–40 | ناحیه | `بلوک رویه‌ای 33-40` | 5 | ⬜ |
| `TOOL-112.07` | 41–73 | ناحیه | `چارت آزمایشی: دو معاونت؛ زیر معاونت اول یک اداره (` | 1 | ⬜ |
| `TOOL-112.08` | 74–96 | ناحیه | `مقدار ارسالی فرم برای معاونت نادیده گرفته می‌شود` | 0 | ⬜ |
| `TOOL-112.09` | 97–101 | ناحیه | `سقف حالت درخواست مهمان` | 0 | ⬜ |
| `TOOL-112.10` | 102–117 | ناحیه | `گزارش با فیلتر معاونت` | 0 | ⬜ |
| `TOOL-112.11` | 118–132 | ناحیه | `فهرست: معاونت کاربر در پاسخ` | 0 | ⬜ |
| `TOOL-112.12` | 133–141 | ناحیه | `پاک‌سازی` | 5 | ⬜ |

</details>

<details>
<summary><b>TOOL-113</b> — <code>tools/test_food_order_range_offline.php</code> (100 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-113.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-113.02` | 4–4 | ناحیه | `آزمون آفلاین بازهٔ فهرست سفارش‌های غذا (my-orders ` | 0 | ⬜ |
| `TOOL-113.03` | 5–8 | ناحیه | `توابع را مستقیم از فایل‌های اصلی استخراج می‌کند تا` | 0 | ⬜ |
| `TOOL-113.04` | 9–30 | تابع | `extract_php_function` | 0 | ⬜ |
| `TOOL-113.05` | 31–44 | ناحیه | `بلوک رویه‌ای 31-44` | 4 | ⬜ |
| `TOOL-113.06` | 45–55 | تابع | `check` | 0 | ⬜ |
| `TOOL-113.07` | 56–64 | تابع | `throws` | 0 | ⬜ |
| `TOOL-113.08` | 66–75 | ناحیه | `۱. تبدیل تاریخ ورودی` | 0 | ⬜ |
| `TOOL-113.09` | 76–88 | ناحیه | `۲. پیش‌فرض: از امروز تا پایان ماه شمسی بعد (سفارش ` | 5 | ⬜ |
| `TOOL-113.10` | 89–100 | ناحیه | `۳. بازهٔ دلخواه` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-114</b> — <code>tools/test_food_org_groups_offline.php</code> (100 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-114.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `TOOL-114.02` | 17–21 | تابع | `check` | 0 | ⬜ |
| `TOOL-114.03` | 22–50 | ناحیه | `بلوک رویه‌ای 22-50` | 0 | ⬜ |
| `TOOL-114.04` | 51–51 | ناحیه | `خلاصه/فوتر حذف شد، ولی تضمین «هر ردیف در یک گروه» ` | 0 | ⬜ |
| `TOOL-114.05` | 52–52 | ناحیه | `split() هر ردیف را بی‌قید در یکی از سبدها می‌ریزد ` | 0 | ⬜ |
| `TOOL-114.06` | 53–81 | ناحیه | `خودش می‌سازد؛ پس شمار ردیف‌های گروه‌ها همیشه = شما` | 0 | ⬜ |
| `TOOL-114.07` | 82–100 | ناحیه | `خواستهٔ کاربر: «بینشون دیگه هیچی نباشه» — نه چیپ خ` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-115</b> — <code>tools/test_food_personnel_link_offline.php</code> (275 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-115.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-115.02` | 4–4 | ناحیه | `آزمون آفلاین اتصال کاربران دامین به پرسنل غذا (SQL` | 0 | ⬜ |
| `TOOL-115.03` | 5–12 | ناحیه | `تطبیق نام، همنام‌ها، کد ملی متفاوت، ادغام، بازگشت ` | 0 | ⬜ |
| `TOOL-115.04` | 13–23 | تابع | `check` | 0 | ⬜ |
| `TOOL-115.05` | 24–46 | ناحیه | `بلوک رویه‌ای 24-46` | 5 | ⬜ |
| `TOOL-115.06` | 47–53 | ناحیه | `کاربران دامین` | 0 | ⬜ |
| `TOOL-115.07` | 54–60 | ناحیه | `پرسنل غذا` | 0 | ⬜ |
| `TOOL-115.08` | 61–64 | ناحیه | `سفارش‌های نمونه: پرسنل غذای ۱۲ (رضا احمدی) سفارش د` | 5 | ⬜ |
| `TOOL-115.09` | 65–77 | ناحیه | `تطبیق` | 0 | ⬜ |
| `TOOL-115.10` | 78–82 | ناحیه | `مجوز انتخاب` | 0 | ⬜ |
| `TOOL-115.11` | 83–94 | ناحیه | `ادغام ناموفق (تداخل سفارش در یک روز) → بدون تغییر` | 5 | ⬜ |
| `TOOL-115.12` | 95–111 | ناحیه | `اجرای واقعی` | 0 | ⬜ |
| `TOOL-115.13` | 112–115 | ناحیه | `انتخاب تکراری همان پرسنل غذا برای دو کاربر` | 0 | ⬜ |
| `TOOL-115.14` | 116–119 | ناحیه | `انتخاب ناسازگار در اجرا رد می‌شود` | 0 | ⬜ |
| `TOOL-115.15` | 120–122 | ناحیه | `نام کاربری‌های موقت نباید بماند` | 0 | ⬜ |
| `TOOL-115.16` | 123–151 | ناحیه | `گزارش` | 5 | ⬜ |
| `TOOL-115.17` | 152–170 | ناحیه | `کاربر دامین غیرفعال: نه تطبیق، نه گزارش تطبیق` | 5 | ⬜ |
| `TOOL-115.18` | 171–198 | ناحیه | `اتصال دستی با کد پرسنلی (بدون تطبیق نام)` | 5 | ⬜ |
| `TOOL-115.19` | 199–221 | ناحیه | `اتصال حساب محلی کارکنان (کد AD به‌دلیل unique بودن` | 5 | ⬜ |
| `TOOL-115.20` | 222–235 | ناحیه | `کد دستی دامین بر نام اولویت دارد و کد هم‌ارز با صف` | 5 | ⬜ |
| `TOOL-115.21` | 236–243 | ناحیه | `کد دستی جدید باید مقدار قدیمی و اشتباه دامین را جا` | 5 | ⬜ |
| `TOOL-115.22` | 244–275 | ناحیه | `هم‌زمانی رکورد واقعی پرسنل غذا و حساب ورود محلی بر` | 4 | ⬜ |

</details>

<details>
<summary><b>TOOL-116</b> — <code>tools/test_food_templates.php</code> (270 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-116.01` | 1–270 | ناحیه | `بلوک رویه‌ای 1-270` | **9** | ⬜ |

</details>

<details>
<summary><b>TOOL-117</b> — <code>tools/test_fresh_db_offline.php</code> (225 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-117.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `TOOL-117.02` | 26–36 | تابع | `check` | 0 | ⬜ |
| `TOOL-117.03` | 38–58 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-117.04` | 60–81 | کلاس | `FakeStmt` | 0 | ⬜ |
| `TOOL-117.05` | 83–107 | کلاس | `FreshFakeDb` | 0 | ⬜ |
| `TOOL-117.06` | 108–109 | ناحیه | `بلوک رویه‌ای 108-109` | 0 | ⬜ |
| `TOOL-117.07` | 110–113 | تابع | `db` | 0 | ⬜ |
| `TOOL-117.08` | 114–117 | تابع | `db_table_exists` | 0 | ⬜ |
| `TOOL-117.09` | 118–121 | تابع | `setting` | 0 | ⬜ |
| `TOOL-117.10` | 122–125 | تابع | `save_setting` | 0 | ⬜ |
| `TOOL-117.11` | 126–128 | تابع | `system_log` | 0 | ⬜ |
| `TOOL-117.12` | 129–209 | ناحیه | `بلوک رویه‌ای 129-209` | 5 | ⬜ |
| `TOOL-117.13` | 210–218 | تابع | `food_ticket_web_host_bootstrap_standalone` | 4 | ⬜ |
| `TOOL-117.14` | 219–225 | ناحیه | `بلوک رویه‌ای 219-225` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-118</b> — <code>tools/test_fresh_state_node.js</code> (126 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-118.01` | 1–32 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-118.02` | 33–42 | بلوک | `check` | 0 | ⬜ |
| `TOOL-118.03` | 43–43 | بلوک | `اجرای گارد با کش مرورگر و توکن سرور مشخص.` | 0 | ⬜ |
| `TOOL-118.04` | 44–81 | بلوک | `boot` | 1 | ⬜ |
| `TOOL-118.05` | 82–90 | بلوک | `۱) توکن متفاوت → دور ریخته شود` | 0 | ⬜ |
| `TOOL-118.06` | 91–95 | بلوک | `۲) دیتابیس خام → دور ریخته شود (حتی با توکن یکسان)` | 0 | ⬜ |
| `TOOL-118.07` | 96–99 | بلوک | `۳) توکن یکسان و دیتابیس پر → کش معتبر بماند` | 0 | ⬜ |
| `TOOL-118.08` | 100–105 | بلوک | `۴) resetLocalState آرایه‌ها را خالی می‌کند` | 0 | ⬜ |
| `TOOL-118.09` | 106–126 | بلوک | `۵) applyDbIdentity با پاسخ زندهٔ سرور` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-119</b> — <code>tools/test_global_search_offline.php</code> (122 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-119.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `TOOL-119.02` | 27–37 | تابع | `check` | 0 | ⬜ |
| `TOOL-119.03` | 38–122 | ناحیه | `بلوک رویه‌ای 38-122` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-120</b> — <code>tools/test_group_flow_offline.php</code> (709 خط، 57 آدرس، بازبینی‌شده 0/57)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-120.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `TOOL-120.02` | 26–30 | کلاس | `OfflinePdoException` | 0 | ⬜ |
| `TOOL-120.03` | 32–119 | کلاس | `OfflineStmt` | 0 | ⬜ |
| `TOOL-120.04` | 121–187 | کلاس | `OfflinePdo` | 4 | ⬜ |
| `TOOL-120.05` | 188–197 | ناحیه | `بلوک رویه‌ای 188-197` | 0 | ⬜ |
| `TOOL-120.06` | 198–204 | تابع | `db` | 0 | ⬜ |
| `TOOL-120.07` | 206–209 | تابع | `activity_log` | 0 | ⬜ |
| `TOOL-120.08` | 211–214 | تابع | `system_log` | 0 | ⬜ |
| `TOOL-120.09` | 216–224 | تابع | `db_table_columns` | 0 | ⬜ |
| `TOOL-120.10` | 226–230 | تابع | `food_ticket_normalize_digits` | 0 | ⬜ |
| `TOOL-120.11` | 231–248 | تابع | `food_ticket_code` | 0 | ⬜ |
| `TOOL-120.12` | 249–252 | تابع | `food_ticket_digits` | 0 | ⬜ |
| `TOOL-120.13` | 253–260 | تابع | `food_ticket_card_key` | 0 | ⬜ |
| `TOOL-120.14` | 261–266 | تابع | `food_ticket_card_equals` | 0 | ⬜ |
| `TOOL-120.15` | 267–279 | تابع | `food_ticket_parse_date` | 0 | ⬜ |
| `TOOL-120.16` | 280–289 | تابع | `food_ticket_parse_time` | 0 | ⬜ |
| `TOOL-120.17` | 290–298 | تابع | `food_ticket_source_key` | 0 | ⬜ |
| `TOOL-120.18` | 299–304 | تابع | `food_ticket_normalize_event_type` | 0 | ⬜ |
| `TOOL-120.19` | 305–309 | تابع | `food_ticket_normalize_print_status` | 0 | ⬜ |
| `TOOL-120.20` | 310–323 | تابع | `food_ticket_add_event` | 1 | ⬜ |
| `TOOL-120.21` | 324–329 | تابع | `food_ticket_existing_source` | 0 | ⬜ |
| `TOOL-120.22` | 330–335 | تابع | `food_ticket_existing_ticket` | 0 | ⬜ |
| `TOOL-120.23` | 336–339 | تابع | `food_ticket_config` | 0 | ⬜ |
| `TOOL-120.24` | 340–343 | تابع | `food_ticket_odbc` | 0 | ⬜ |
| `TOOL-120.25` | 344–347 | تابع | `food_ticket_order_map_lazy` | 0 | ⬜ |
| `TOOL-120.26` | 348–351 | تابع | `food_ticket_template` | 0 | ⬜ |
| `TOOL-120.27` | 352–356 | تابع | `food_ticket_enqueue_print` | 1 | ⬜ |
| `TOOL-120.28` | 357–360 | تابع | `food_ticket_process_print_queue_locked` | 0 | ⬜ |
| `TOOL-120.29` | 361–386 | تابع | `food_ticket_decide_for_user` | 6 | ⬜ |
| `TOOL-120.30` | 387–390 | تابع | `food_ticket_delivery_supported` | 0 | ⬜ |
| `TOOL-120.31` | 391–394 | تابع | `food_ticket_events_ensure_delivery` | 0 | ⬜ |
| `TOOL-120.32` | 395–398 | تابع | `user_can` | 0 | ⬜ |
| `TOOL-120.33` | 399–400 | ناحیه | `بلوک رویه‌ای 399-400` | 0 | ⬜ |
| `TOOL-120.34` | 401–404 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-120.35` | 405–469 | ناحیه | `بلوک رویه‌ای 405-469` | 5 | ⬜ |
| `TOOL-120.36` | 470–480 | ناحیه | `۱) ساخت گروه با L_UID` | 0 | ⬜ |
| `TOOL-120.37` | 481–485 | ناحیه | `۳) شناسایی با L_UID (با نرمال‌سازی صفر ابتدایی)` | 0 | ⬜ |
| `TOOL-120.38` | 486–494 | ناحیه | `۴) رد L_UID تکراری` | 0 | ⬜ |
| `TOOL-120.39` | 495–503 | ناحیه | `۵) رد عضویت تکراری در دو گروه` | 0 | ⬜ |
| `TOOL-120.40` | 504–514 | ناحیه | `۶) غیبت روزانه (Exception) + قفل‌نبودن قبل از تردد` | 0 | ⬜ |
| `TOOL-120.41` | 515–545 | ناحیه | `۷) تردد نماینده (L_UID) → فیش اعضای واجد شرایط` | 0 | ⬜ |
| `TOOL-120.42` | 546–565 | ناحیه | `۸) قفل بعد از تردد + رد ویرایش کاربر عادی + اجازهٔ` | 0 | ⬜ |
| `TOOL-120.43` | 566–578 | ناحیه | `۹) تردد مجدد همان نماینده → تکرار، بدون فیش جدید` | 0 | ⬜ |
| `TOOL-120.44` | 579–584 | ناحیه | `۱۰) کشیدن دوبارهٔ همان ردیف (≤۴ ثانیه) → نادیده` | 0 | ⬜ |
| `TOOL-120.45` | 585–588 | ناحیه | `۱۱) استقلال تاریخ‌ها: فردا غیبتی ندارد` | 0 | ⬜ |
| `TOOL-120.46` | 589–595 | ناحیه | `۱۲) API وضعیت غیبت (داخل فرم گروه‌ها)` | 0 | ⬜ |
| `TOOL-120.47` | 596–602 | ناحیه | `۱۳) ذخیرهٔ گروهی غیبت از طریق API` | 0 | ⬜ |
| `TOOL-120.48` | 603–606 | ناحیه | `۱۴) حذف همهٔ غیبت‌ها (همه حاضر)` | 0 | ⬜ |
| `TOOL-120.49` | 607–621 | ناحیه | `۱۵) تردد با L_UID ناشناخته (سابقهٔ نمایندگی دارد)` | 0 | ⬜ |
| `TOOL-120.50` | 622–642 | ناحیه | `۱۵-۵) آمار بازرسی: غایب‌کردن بعد از صدور فیش → توق` | 1 | ⬜ |
| `TOOL-120.51` | 643–647 | ناحیه | `فیش متوقف‌شده در فهرست چاپ مجدد خطاها نیست (فقط pe` | 0 | ⬜ |
| `TOOL-120.52` | 648–660 | ناحیه | `«حاضر» شدن دوباره → برگشت به صف چاپ` | 0 | ⬜ |
| `TOOL-120.53` | 661–667 | ناحیه | `فیش چاپ‌شده هرگز با غیبت تغییر نمی‌کند` | 1 | ⬜ |
| `TOOL-120.54` | 668–674 | ناحیه | `۱۶) گروه غیرفعال → هیچ فیش جدیدی` | 0 | ⬜ |
| `TOOL-120.55` | 675–677 | ناحیه | `۱۷) کسی که امروز از مسیر عادی فیش گرفته، از مسیر گ` | 0 | ⬜ |
| `TOOL-120.56` | 678–700 | ناحیه | `فیش فردی امروز (مثل تردد عادی همان کارمند)` | 0 | ⬜ |
| `TOOL-120.57` | 701–709 | ناحیه | `۱۸) دسترسی‌ها/پاک‌سازی داده` | 5 | ⬜ |

</details>

<details>
<summary><b>TOOL-121</b> — <code>tools/test_guest_cap_offline.php</code> (90 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-121.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `TOOL-121.02` | 17–18 | ناحیه | `تنظیمات تست: مقدار را از $GLOBALS['test_settings']` | 0 | ⬜ |
| `TOOL-121.03` | 19–22 | تابع | `setting` | 0 | ⬜ |
| `TOOL-121.04` | 23–25 | ناحیه | `بلوک رویه‌ای 23-25` | 0 | ⬜ |
| `TOOL-121.05` | 26–31 | تابع | `check` | 0 | ⬜ |
| `TOOL-121.06` | 32–34 | ناحیه | `بلوک رویه‌ای 32-34` | 0 | ⬜ |
| `TOOL-121.07` | 35–37 | ناحیه | `۱) ساعت ۱۲:۱۰ — ۴ نفر وارد شده‌اند، هیچ‌کس قبل از ` | 0 | ⬜ |
| `TOOL-121.08` | 38–42 | ناحیه | `۲) ۱۲:۳۰ — دو نفر که فیش گرفته‌اند بعد از ۱۲ خارج ` | 0 | ⬜ |
| `TOOL-121.09` | 43–46 | ناحیه | `۳) ۱۲:۴۵ — یک نفر جدید وارد می‌شود ⇒ ورودها ۵ ⇒ سق` | 0 | ⬜ |
| `TOOL-121.10` | 47–50 | ناحیه | `۴) خروج قبل از ساعت قطع سقف را کم می‌کند.` | 0 | ⬜ |
| `TOOL-121.11` | 51–54 | ناحیه | `۵) کسی نیامده ⇒ بسته؛ سقف هرگز منفی نمی‌شود.` | 0 | ⬜ |
| `TOOL-121.12` | 55–59 | ناحیه | `۶) تردد در دسترس نیست ⇒ سقف ثابت قدیمی.` | 0 | ⬜ |
| `TOOL-121.13` | 60–63 | ناحیه | `۷) سقف هر کارت همچنان اعمال می‌شود.` | 0 | ⬜ |
| `TOOL-121.14` | 64–65 | ناحیه | `۸) پیش‌فرض‌ها بدون تنظیمات: حالت ثابت و ساعت قطع ۱` | 0 | ⬜ |
| `TOOL-121.15` | 66–73 | ناحیه | `بازهٔ ۱۱ تا ۱۵: قبل از ۱۱ و از ۱۵ به بعد سقف صفر ا` | 0 | ⬜ |
| `TOOL-121.16` | 74–90 | ناحیه | `۹) ساعت‌های قابل تنظیم: شروع، قطع و پایان از فرم ک` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-122</b> — <code>tools/test_it_expert_routing_offline.php</code> (283 خط، 25 آدرس، بازبینی‌شده 0/25)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-122.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `TOOL-122.02` | 20–33 | تابع | `ok` | 0 | ⬜ |
| `TOOL-122.03` | 34–36 | ناحیه | `بلوک رویه‌ای 34-36` | 0 | ⬜ |
| `TOOL-122.04` | 37–43 | ناحیه | `فهرست تنظیمات — همان چیزی که دیتابیس در settings ن` | 0 | ⬜ |
| `TOOL-122.05` | 44–47 | تابع | `key_role_user_ids` | 0 | ⬜ |
| `TOOL-122.06` | 48–51 | تابع | `user_is_key_role` | 0 | ⬜ |
| `TOOL-122.07` | 52–56 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `TOOL-122.08` | 57–60 | تابع | `is_it_agent` | 0 | ⬜ |
| `TOOL-122.09` | 61–64 | تابع | `ticket_notification_user_can` | 0 | ⬜ |
| `TOOL-122.10` | 65–65 | تابع | `cfg` | 0 | ⬜ |
| `TOOL-122.11` | 66–69 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `TOOL-122.12` | 70–73 | تابع | `is_staff_role` | 0 | ⬜ |
| `TOOL-122.13` | 76–111 | کلاس | `FakeStmt` | 0 | ⬜ |
| `TOOL-122.14` | 112–116 | کلاس | `FakeDb` | 0 | ⬜ |
| `TOOL-122.15` | 117–117 | تابع | `db` | 0 | ⬜ |
| `TOOL-122.16` | 118–118 | تابع | `ticket_org_manager_rank_sql` | 0 | ⬜ |
| `TOOL-122.17` | 119–119 | تابع | `ticket_org_auto_role_sql` | 0 | ⬜ |
| `TOOL-122.18` | 120–124 | ناحیه | `بلوک رویه‌ای 120-124` | 0 | ⬜ |
| `TOOL-122.19` | 125–171 | تابع | `load_choose_handling_agent` | 4 | ⬜ |
| `TOOL-122.20` | 172–230 | ناحیه | `بلوک رویه‌ای 172-230` | 0 | ⬜ |
| `TOOL-122.21` | 231–235 | ناحیه | `کارشناس IT نباید مسئول تیکت پشتیبانی شود` | 0 | ⬜ |
| `TOOL-122.22` | 236–251 | ناحیه | `و برعکس` | 0 | ⬜ |
| `TOOL-122.23` | 252–252 | ناحیه | `قانون لازم‌الاجرا (تصمیم مالک، ۲۰۲۶-۱۱-۱۶): «هیچ ا` | 0 | ⬜ |
| `TOOL-122.24` | 253–253 | ناحیه | `مسئول انجام تیکت شود.» پیش از این تست، خلاف این را` | 0 | ⬜ |
| `TOOL-122.25` | 254–283 | ناحیه | `پشتیبان ادمین اصلی بود — یعنی همان باگی که مالک با` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-123</b> — <code>tools/test_key_roles_composition_offline.php</code> (194 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-123.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `TOOL-123.02` | 20–33 | تابع | `ok` | 0 | ⬜ |
| `TOOL-123.03` | 34–39 | تابع | `skip` | 0 | ⬜ |
| `TOOL-123.04` | 40–44 | ناحیه | `بلوک رویه‌ای 40-44` | 0 | ⬜ |
| `TOOL-123.05` | 45–53 | ناحیه | `کارشناس IT نباید primary_admin شود (اصلاح ۱.۶۲.۹۸)` | 1 | ⬜ |
| `TOOL-123.06` | 54–58 | ناحیه | `کارشناس پشتیبانی: هیچ UPDATE role ای برایش نباید ب` | 1 | ⬜ |
| `TOOL-123.07` | 59–176 | ناحیه | `سوپروایز: افزودن role="supervisor" نباید باشد` | 1 | ⬜ |
| `TOOL-123.08` | 177–178 | ناحیه | `شبیه‌سازی سبک: کاربری در هر دو فهرست` | 0 | ⬜ |
| `TOOL-123.09` | 179–179 | تابع | `key_role_user_ids` | 0 | ⬜ |
| `TOOL-123.10` | 180–194 | ناحیه | `بلوک رویه‌ای 180-194` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-124</b> — <code>tools/test_live_ui_node.js</code> (322 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-124.01` | 1–37 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `TOOL-124.02` | 38–47 | بلوک | `check` | 0 | ⬜ |
| `TOOL-124.03` | 48–48 | بلوک | `اجرای app.js در یک صفحهٔ ساختگی و برگرداندن window` | 0 | ⬜ |
| `TOOL-124.04` | 49–59 | بلوک | `boot` | 4 | ⬜ |
| `TOOL-124.05` | 60–65 | بلوک | `pressKey` | 0 | ⬜ |
| `TOOL-124.06` | 66–69 | بلوک | `rowVisible` | 0 | ⬜ |
| `TOOL-124.07` | 70–150 | بلوک | `الف) صفحهٔ شناسنامه‌های فنی` | 0 | ⬜ |
| `TOOL-124.08` | 151–250 | بلوک | `ب) کمبوی زندهٔ «سیستم مرتبط»` | 0 | ⬜ |
| `TOOL-124.09` | 251–254 | بلوک | `۱.۳۷.۲: قاعدهٔ حوزه‌محور برای «سیستم مرتبط»` | 0 | ⬜ |
| `TOOL-124.10` | 255–322 | بلوک | `submitForm` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-125</b> — <code>tools/test_login_and_asset_names_r15_offline.php</code> (374 خط، 51 آدرس، بازبینی‌شده 0/51)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-125.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `TOOL-125.02` | 19–30 | تابع | `check` | 0 | ⬜ |
| `TOOL-125.03` | 31–52 | ناحیه | `بلوک رویه‌ای 31-52` | 0 | ⬜ |
| `TOOL-125.04` | 53–76 | ناحیه | `اثبات رفتاری: توکن غلط ⇒ خروج، نه ادامهٔ اجرا` | 5 | ⬜ |
| `TOOL-125.05` | 77–77 | ناحیه | `ماژول نام را مستقل بارگذاری می‌کنیم (تنظیمات را شب` | 0 | ⬜ |
| `TOOL-125.06` | 78–81 | تابع | `setting` | 0 | ⬜ |
| `TOOL-125.07` | 82–102 | ناحیه | `بلوک رویه‌ای 82-102` | 0 | ⬜ |
| `TOOL-125.08` | 103–107 | ناحیه | `نام‌های واقعی باید دست‌نخورده بمانند — دقیقاً همان` | 0 | ⬜ |
| `TOOL-125.09` | 108–123 | ناحیه | `نام کامل دامنه در حالت full واقعاً کامل است` | 0 | ⬜ |
| `TOOL-125.10` | 124–158 | ناحیه | `اثبات رفتاری برای تابع تشخیص جعلی` | 5 | ⬜ |
| `TOOL-125.11` | 159–187 | ناحیه | `اثبات رفتاری: پرچم Secure برای هر مقدار تنظیم` | 5 | ⬜ |
| `TOOL-125.12` | 188–188 | ناحیه | `به‌جای تطبیق رشته در سورس (که کوتیشن‌های فرار مشکل` | 0 | ⬜ |
| `TOOL-125.13` | 189–199 | ناحیه | `فرم را از index.php بیرون می‌کشیم و رفتارش را می‌س` | 0 | ⬜ |
| `TOOL-125.14` | 200–200 | ناحیه | `asset-names.php همین بالاتر (بخش ۲) کامل بارگذاری ` | 0 | ⬜ |
| `TOOL-125.15` | 201–201 | ناحیه | `دوباره‌سازی توابع نه لازم است و نه درست — بازتعریف` | 0 | ⬜ |
| `TOOL-125.16` | 202–204 | ناحیه | `جای نسخهٔ واقعی می‌گذاشت و همان باگ را پنهان می‌کر` | 0 | ⬜ |
| `TOOL-125.17` | 205–230 | ناحیه | `دقیقاً همان چیزی که کاربر دید: نام + IP + MAC در ی` | 0 | ⬜ |
| `TOOL-125.18` | 231–231 | ناحیه | `باگ واقعی: Read-Host داخل shell_exec هیچ کنسولی ند` | 0 | ⬜ |
| `TOOL-125.19` | 232–236 | ناحیه | `برمی‌گرداند — رمز خالی و پیام گمراه‌کنندهٔ «اعتبار` | 4 | ⬜ |
| `TOOL-125.20` | 237–239 | ناحیه | `باگ واقعی: کدهای ANSI در cmd.exe قدیمی به‌صورت ←[3` | 0 | ⬜ |
| `TOOL-125.21` | 240–245 | ناحیه | `دکمهٔ اجرا: php.exe روی IIS در PATH نیست` | 0 | ⬜ |
| `TOOL-125.22` | 246–252 | ناحیه | `سند دستور اجرا` | 0 | ⬜ |
| `TOOL-125.23` | 253–253 | ناحیه | `روی XAMPP، Apache با LocalSystem اجرا می‌شود و PHP` | 0 | ⬜ |
| `TOOL-125.24` | 254–254 | ناحیه | `پس استخراج از راه دور از پنل هیچ دسترسی شبکه‌ای ند` | 0 | ⬜ |
| `TOOL-125.25` | 255–258 | ناحیه | `تشخیص داده و صریح گفته شود، نه اینکه کاربر دنبال ف` | 0 | ⬜ |
| `TOOL-125.26` | 259–261 | ناحیه | `نشت رمز: خط فرمان فرآیند برای هر کاربری در Task Ma` | 0 | ⬜ |
| `TOOL-125.27` | 262–263 | ناحیه | `اثر جانبی روی کلاینت: پوشهٔ آزمون باید پاک شود.` | 0 | ⬜ |
| `TOOL-125.28` | 264–267 | ناحیه | `سند باید XAMPP را اول حساب کند، نه IIS` | 0 | ⬜ |
| `TOOL-125.29` | 268–274 | ناحیه | `فایل batch باید xampp را قبل از بقیه بگردد` | 0 | ⬜ |
| `TOOL-125.30` | 275–275 | ناحیه | `کاربر «diag_extract.php ندارم» گفت. فایل فقط داخل ` | 0 | ⬜ |
| `TOOL-125.31` | 276–276 | ناحیه | `اما بدتر: ابزار bootstrap را اجباری require می‌کرد` | 0 | ⬜ |
| `TOOL-125.32` | 277–281 | ناحیه | `بود می‌مرد — یعنی دقیقاً وقتی که به تشخیص نیاز است` | 0 | ⬜ |
| `TOOL-125.33` | 282–292 | ناحیه | `هیچ وابستگی دیگری نباید باشد: یک فایل، اجرا در هر ` | 1 | ⬜ |
| `TOOL-125.34` | 293–293 | ناحیه | `خروجی واقعی نشان داد tools\collect_via_smb.ps1 روی` | 0 | ⬜ |
| `TOOL-125.35` | 294–297 | ناحیه | `روش دوم SMB برای همهٔ مقصدها بی‌صدا شکست می‌خورد.` | 0 | ⬜ |
| `TOOL-125.36` | 298–302 | ناحیه | `0x80090311 در خروجی واقعی آمد و هیچ نگاشتی نداشت` | 0 | ⬜ |
| `TOOL-125.37` | 303–305 | ناحیه | `عضویت دامنه کافی نیست: باید خودِ DC هم دیده شود` | 0 | ⬜ |
| `TOOL-125.38` | 306–309 | ناحیه | `نام سرویس Apache روی Server 2016 با Apache2.4 ثابت` | 0 | ⬜ |
| `TOOL-125.39` | 310–311 | ناحیه | `«نتیجه خوانده نشد» باید خودش علت را بگوید` | 0 | ⬜ |
| `TOOL-125.40` | 312–318 | ناحیه | `نصب‌کنندهٔ فایل‌ها` | 2 | ⬜ |
| `TOOL-125.41` | 319–319 | ناحیه | `کاربر گفت: سرور عضو دامنه، حساب ادمین دامنه، رمز د` | 0 | ⬜ |
| `TOOL-125.42` | 320–324 | ناحیه | `پس هیچ پیامی نباید «پیش‌نیاز را بررسی کنید» بگوید ` | 0 | ⬜ |
| `TOOL-125.43` | 325–326 | ناحیه | `«Access is denied» با هویت سرور، به استخراج پنل رب` | 0 | ⬜ |
| `TOOL-125.44` | 327–334 | ناحیه | `پیام‌های قطعی «ادمین نیست» باید تفکیک شوند` | 1 | ⬜ |
| `TOOL-125.45` | 335–337 | ناحیه | `نبود فایل SMB نباید بی‌صدا باشد` | 0 | ⬜ |
| `TOOL-125.46` | 338–342 | ناحیه | `جمع‌بندی پویا` | 0 | ⬜ |
| `TOOL-125.47` | 343–343 | ناحیه | `باگ واقعی: lib/logging-csrf.php هندلر سراسری استثن` | 0 | ⬜ |
| `TOOL-125.48` | 344–344 | ناحیه | `با «[FATAL] PDOException …» خارج می‌شود. پس try/ca` | 0 | ⬜ |
| `TOOL-125.49` | 345–349 | ناحیه | `هیچ‌وقت اجرا نمی‌شد و ابزار خودش می‌مرد.` | 0 | ⬜ |
| `TOOL-125.50` | 350–350 | ناحیه | `باگ سوم: bootstrap.php اگر config.php نباشد header` | 0 | ⬜ |
| `TOOL-125.51` | 351–374 | ناحیه | `می‌میرد. ابزار باید خودش این را پیش از لود کردن بب` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-126</b> — <code>tools/test_manual_guest_delivery_offline.php</code> (302 خط، 24 آدرس، بازبینی‌شده 0/24)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-126.01` | 1–40 | ناحیه | `بلوک رویه‌ای 1-40` | 0 | ⬜ |
| `TOOL-126.02` | 41–51 | تابع | `chk` | 0 | ⬜ |
| `TOOL-126.03` | 52–65 | ناحیه | `بلوک رویه‌ای 52-65` | 0 | ⬜ |
| `TOOL-126.04` | 66–90 | تابع | `body_of` | 0 | ⬜ |
| `TOOL-126.05` | 91–99 | ناحیه | `بلوک رویه‌ای 91-99` | 0 | ⬜ |
| `TOOL-126.06` | 100–113 | ناحیه | `۱) print_status` | 0 | ⬜ |
| `TOOL-126.07` | 114–123 | ناحیه | `۲) delivery_status = delivered` | 0 | ⬜ |
| `TOOL-126.08` | 124–142 | ناحیه | `۳) meal_type` | 0 | ⬜ |
| `TOOL-126.09` | 143–160 | ناحیه | `۴) مسیر کارمند دست‌نخورده` | 0 | ⬜ |
| `TOOL-126.10` | 161–166 | ناحیه | `۵) فراخوانی مهمان وعده را می‌فرستد` | 0 | ⬜ |
| `TOOL-126.11` | 167–181 | ناحیه | `۶) خواندن وعده از بدنهٔ درخواست` | 0 | ⬜ |
| `TOOL-126.12` | 182–182 | ناحیه | `۷) قرارداد گزارش‌ها` | 0 | ⬜ |
| `TOOL-126.13` | 183–196 | ناحیه | `اگر روزی شرط گزارش عوض شود، این آزمون می‌گوید قرار` | 0 | ⬜ |
| `TOOL-126.14` | 197–218 | ناحیه | `۸) رابط کاربری: وعده و برچسب درست` | 0 | ⬜ |
| `TOOL-126.15` | 219–219 | ناحیه | `۹) تاریخچهٔ ثبت دستی وعده را برمی‌گرداند` | 0 | ⬜ |
| `TOOL-126.16` | 220–220 | ناحیه | `کوئری با کوتیشن فرارکرده نوشته می‌شود، پس فقط وجود` | 0 | ⬜ |
| `TOOL-126.17` | 221–230 | ناحیه | `می‌شود و آزمون به جزئیات نحوی کوتیشن وابسته نیست.` | 0 | ⬜ |
| `TOOL-126.18` | 231–231 | ناحیه | `۱۰) تعمیر خودکار مهمان‌های قدیمی` | 0 | ⬜ |
| `TOOL-126.19` | 232–232 | ناحیه | `کاربر نباید مجبور باشد برای ۶ مهمانِ ثبت‌شده ابزار` | 0 | ⬜ |
| `TOOL-126.20` | 233–233 | ناحیه | `گزارش تحویل باز می‌شود، reconcile_range صدا زده می` | 0 | ⬜ |
| `TOOL-126.21` | 234–268 | ناحیه | `قدیمیِ خراب را هم خودش درست کند — با همان گاردهای ` | 0 | ⬜ |
| `TOOL-126.22` | 269–269 | ناحیه | `گزارش تحویل باید این همگام‌سازی را صدا بزند — وگرن` | 0 | ⬜ |
| `TOOL-126.23` | 270–279 | ناحیه | `اجرا نمی‌شود و کاربر همچنان به ابزار CLI وابسته اس` | 0 | ⬜ |
| `TOOL-126.24` | 280–302 | ناحیه | `۱۱) ابزار تعمیر` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-127</b> — <code>tools/test_module_grant_offline.php</code> (154 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-127.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `TOOL-127.02` | 17–28 | ناحیه | `کش نقش‌ها را به‌جای دیتابیس پر می‌کنیم: بازرس فقط ` | 0 | ⬜ |
| `TOOL-127.03` | 29–34 | تابع | `check` | 0 | ⬜ |
| `TOOL-127.04` | 35–39 | ناحیه | `بلوک رویه‌ای 35-39` | 0 | ⬜ |
| `TOOL-127.05` | 40–99 | ناحیه | `مسیرهای فیش را از خود فایل می‌خوانیم تا آزمون با ت` | 0 | ⬜ |
| `TOOL-127.06` | 100–100 | تابع | `food_ticket_is_primary_admin` | 0 | ⬜ |
| `TOOL-127.07` | 101–126 | ناحیه | `بلوک رویه‌ای 101-126` | 5 | ⬜ |
| `TOOL-127.08` | 127–134 | ناحیه | `پیش‌فرض نقش user: بخش‌های چاپ فیش و نظارت برداشته ` | 0 | ⬜ |
| `TOOL-127.09` | 135–154 | ناحیه | `چاپ فیش فقط برای ادمین اصلی، مدیر پشتیبانی (محدود)` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-128</b> — <code>tools/test_org_role_sync_offline.php</code> (84 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-128.01` | 1–1 | ناحیه | `بلوک رویه‌ای 1-1` | 0 | ⬜ |
| `TOOL-128.02` | 2–12 | ناحیه | `آزمون همگام‌سازی نقش با سمت‌های چارت سازمانی (روی ` | 0 | ⬜ |
| `TOOL-128.03` | 13–13 | تابع | `db` | 0 | ⬜ |
| `TOOL-128.04` | 14–16 | ناحیه | `بلوک رویه‌ای 14-16` | 0 | ⬜ |
| `TOOL-128.05` | 17–17 | تابع | `check` | 0 | ⬜ |
| `TOOL-128.06` | 18–18 | تابع | `roleOf` | 0 | ⬜ |
| `TOOL-128.07` | 19–41 | ناحیه | `بلوک رویه‌ای 19-41` | 6 | ⬜ |
| `TOOL-128.08` | 42–47 | ناحیه | `۱) کاربر «کاربر» معاون می‌شود → مدیر` | 1 | ⬜ |
| `TOOL-128.09` | 48–52 | ناحیه | `۲) کارشناس هم با سمت معاونت به مدیر تبدیل می‌شود (` | 1 | ⬜ |
| `TOOL-128.10` | 53–57 | ناحیه | `۳) برداشتن سمت → بازگشت به نقش قبلی` | 1 | ⬜ |
| `TOOL-128.11` | 58–66 | ناحیه | `۴) بازرس و سوپروایزر با سمت مدیریتی تغییر نمی‌کنند` | 1 | ⬜ |
| `TOOL-128.12` | 67–74 | ناحیه | `۵) عضو مدیریت (جدول چند مدیر) هم مدیر می‌شود` | 1 | ⬜ |
| `TOOL-128.13` | 75–79 | ناحیه | `۶) مدیر دستی (غیر خودکار) با برداشتن سمت‌ها دست‌نخ` | 1 | ⬜ |
| `TOOL-128.14` | 80–84 | ناحیه | `پاکسازی فقط داده‌های همین اجرای مصنوعی؛ سایر واحده` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-129</b> — <code>tools/test_orphan_ticket_offline.php</code> (409 خط، 43 آدرس، بازبینی‌شده 0/43)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-129.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `TOOL-129.02` | 4–5 | ناحیه | `آزمون آفلاین «فیش یتیم» — فیشی که در صف چاپ ماند و` | 0 | ⬜ |
| `TOOL-129.03` | 6–6 | ناحیه | `رخداد واقعی (۱۴۰۵/۰۸/۲۵): پرینتر خراب شد و کارکنان` | 0 | ⬜ |
| `TOOL-129.04` | 7–7 | ناحیه | `چاپ ماندند. کاربر خواست همان روز را دستی ثبت کند، ` | 0 | ⬜ |
| `TOOL-129.05` | 8–8 | ناحیه | `۱) فرانت آن‌ها را «دارای فیش این روز» نشان می‌داد ` | 0 | ⬜ |
| `TOOL-129.06` | 9–9 | ناحیه | `۲) سرور هر رویداد غیر از no_food را «تکراری» می‌دا` | 0 | ⬜ |
| `TOOL-129.07` | 10–10 | ناحیه | `۳) تصمیم‌گیر موتور هم repeat برمی‌گرداند.` | 0 | ⬜ |
| `TOOL-129.08` | 11–11 | ناحیه | `۴) گزارش تحویل آن‌ها را نشان نمی‌داد (چون تحویل ثب` | 0 | ⬜ |
| `TOOL-129.09` | 12–13 | ناحیه | `نتیجه: غذا نه در گزارش بود و نه راهی برای اصلاح.` | 0 | ⬜ |
| `TOOL-129.10` | 14–14 | ناحیه | `این آزمون تضمین می‌کند:` | 0 | ⬜ |
| `TOOL-129.11` | 15–15 | ناحیه | `الف) فیش واقعاً چاپ‌شده «یتیم» شمرده نمی‌شود (دست ` | 0 | ⬜ |
| `TOOL-129.12` | 16–16 | ناحیه | `ب) فیش در صف (pending/printing/print_error/failed/` | 0 | ⬜ |
| `TOOL-129.13` | 17–17 | ناحیه | `ج) هر چهار لایه همان قاعده را دارند — نه سه‌تای هم` | 0 | ⬜ |
| `TOOL-129.14` | 18–23 | ناحیه | `د) تبدیل همان ردیف انجام می‌شود، نه ساخت ردیف دوم.` | 0 | ⬜ |
| `TOOL-129.15` | 24–32 | تابع | `ok` | 0 | ⬜ |
| `TOOL-129.16` | 33–48 | ناحیه | `بلوک رویه‌ای 33-48` | 0 | ⬜ |
| `TOOL-129.17` | 49–54 | ناحیه | `تابع را از فایل اصلی استخراج و همان‌جا اجرا می‌کنی` | 0 | ⬜ |
| `TOOL-129.18` | 55–62 | ناحیه | `چاپ‌شده ⇒ فیش واقعی، دست نزن` | 0 | ⬜ |
| `TOOL-129.19` | 63–76 | ناحیه | `در صف چاپ ⇒ یتیم` | 0 | ⬜ |
| `TOOL-129.20` | 77–83 | ناحیه | `ورودی نامعتبر` | 0 | ⬜ |
| `TOOL-129.21` | 84–92 | ناحیه | `لایهٔ ۱ — تشخیص تکراری در food_ticket_groups` | 0 | ⬜ |
| `TOOL-129.22` | 93–96 | ناحیه | `لایهٔ ۲ — گارد ticket_key` | 0 | ⬜ |
| `TOOL-129.23` | 97–104 | ناحیه | `لایهٔ ۳ — تبدیل همان ردیف` | 0 | ⬜ |
| `TOOL-129.24` | 105–125 | ناحیه | `لایهٔ ۴ — تصمیم‌گیر موتور` | 0 | ⬜ |
| `TOOL-129.25` | 126–128 | ناحیه | `برچسب وضعیت نباید «دارای فیش این روز» باشد` | 0 | ⬜ |
| `TOOL-129.26` | 129–129 | ناحیه | `auto_printed هم فیش واقعی است؛ پیش‌تر فقط 'printed` | 0 | ⬜ |
| `TOOL-129.27` | 130–135 | ناحیه | `چاپ خودکار به شاخهٔ آخر می‌افتاد و «تردد این روز ث` | 0 | ⬜ |
| `TOOL-129.28` | 136–140 | ناحیه | `payload سرور باید delivery_status بدهد` | 0 | ⬜ |
| `TOOL-129.29` | 141–141 | ناحیه | `ردیفِ نمایشی باید «فیش‌دار» باشد، نه صرفاً آخرین ر` | 0 | ⬜ |
| `TOOL-129.30` | 142–148 | ناحیه | `فیش صبح داشته باشد و تردد گروه (repeat) بعدازظهر ب` | 0 | ⬜ |
| `TOOL-129.31` | 149–149 | ناحیه | `رخداد واقعی یعنی ده‌ها نفر یک‌جا در صف مانده‌اند. ` | 0 | ⬜ |
| `TOOL-129.32` | 150–152 | ناحیه | `ثبت دستی است که کاربر نمی‌خواهد؛ یک دکمه باید همه ` | 0 | ⬜ |
| `TOOL-129.33` | 153–153 | ناحیه | `شمارش از کل کارکنان است، نه از نتیجهٔ جستجو: اگر ک` | 0 | ⬜ |
| `TOOL-129.34` | 154–170 | ناحیه | `باشد، ردیف یتیم بیرون از نتیجه می‌ماند و دکمه ناپد` | 0 | ⬜ |
| `TOOL-129.35` | 171–171 | ناحیه | `اگر index.html همان ?v= قدیمی را نگه دارد، مرورگر ` | 0 | ⬜ |
| `TOOL-129.36` | 172–172 | ناحیه | `می‌خواند و کاربر چک‌باکس‌های قفل را می‌بیند — یعنی` | 0 | ⬜ |
| `TOOL-129.37` | 173–218 | ناحیه | `ولی در عمل هنوز هست. این بررسی مانع تکرار آن است.` | 0 | ⬜ |
| `TOOL-129.38` | 219–219 | ناحیه | `این پنج مورد با آزمون زنده روی نصب واقعی پیدا شدند` | 0 | ⬜ |
| `TOOL-129.39` | 220–388 | ناحیه | `کافی بود که «ثبت گروهی» کار نکند، بدون این‌که پیام` | 2 | ⬜ |
| `TOOL-129.40` | 389–389 | ناحیه | `گزارش همچنان فقط «تحویل‌شده» را نشان می‌دهد. فیش ی` | 0 | ⬜ |
| `TOOL-129.41` | 390–390 | ناحیه | `چون تحویل نشده. اصلاح این است که کاربر بتواند ثبتش` | 0 | ⬜ |
| `TOOL-129.42` | 391–391 | ناحیه | `food-order.php عمداً در بستهٔ افزایشی نیست (تغییر ` | 0 | ⬜ |
| `TOOL-129.43` | 392–409 | ناحیه | `بررسی می‌شود؛ وگرنه اعلام می‌شود که بررسی نشد — نه` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-130</b> — <code>tools/test_panel_printer_node.js</code> (526 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-130.01` | 1–39 | بلوک | `مقدمهٔ فایل` | 4 | ⬜ |
| `TOOL-130.02` | 40–66 | بلوک | `check` | 0 | ⬜ |
| `TOOL-130.03` | 67–67 | بلوک | `تبدیل مقدار ذخیره‌شده به ساختار align_state سرور (` | 0 | ⬜ |
| `TOOL-130.04` | 68–112 | بلوک | `alignStateFrom` | 1 | ⬜ |
| `TOOL-130.05` | 113–148 | بلوک | `response` | 4 | ⬜ |
| `TOOL-130.06` | 149–186 | بلوک | `method` | 0 | ⬜ |
| `TOOL-130.07` | 187–187 | بلوک | `headers` | 0 | ⬜ |
| `TOOL-130.08` | 188–268 | بلوک | `fd` | 1 | ⬜ |
| `TOOL-130.09` | 269–272 | بلوک | `nextTick` | 0 | ⬜ |
| `TOOL-130.10` | 273–273 | بلوک | `تا وقتی شرط درست شود (یا مهلت تمام شود) صبر می‌کند` | 0 | ⬜ |
| `TOOL-130.11` | 274–376 | بلوک | `waitFor` | 0 | ⬜ |
| `TOOL-130.12` | 377–519 | بلوک | `alignBody` | 2 | ⬜ |
| `TOOL-130.13` | 520–526 | بلوک | `finish` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-131</b> — <code>tools/test_prewindow_check_offline.php</code> (128 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-131.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `TOOL-131.02` | 17–23 | تابع | `check` | 0 | ⬜ |
| `TOOL-131.03` | 24–65 | ناحیه | `بلوک رویه‌ای 24-65` | 0 | ⬜ |
| `TOOL-131.04` | 66–66 | ناحیه | `فقط روزهایی که در تقویم این نصب «تعطیل» نیستند؛ تع` | 0 | ⬜ |
| `TOOL-131.05` | 67–128 | ناحیه | `و بررسی جداگانه‌ای دارند (تست ۱۶).` | 1 | ⬜ |

</details>

<details>
<summary><b>TOOL-132</b> — <code>tools/test_primary_admin_routing_runtime.php</code> (96 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-132.01` | 1–39 | ناحیه | `بلوک رویه‌ای 1-39` | 0 | ⬜ |
| `TOOL-132.02` | 40–53 | ناحیه | `دو کاربر آزمون؛ پاک‌سازی در پایان.` | 5 | ⬜ |
| `TOOL-132.03` | 54–54 | تابع | `ok` | 0 | ⬜ |
| `TOOL-132.04` | 55–55 | تابع | `setset` | 1 | ⬜ |
| `TOOL-132.05` | 57–58 | ناحیه | `علی = ادمین اصلی که در فهرست کارشناس آی تی هم هست` | 0 | ⬜ |
| `TOOL-132.06` | 59–96 | ناحیه | `رضا = ادمین اصلی که در هیچ فهرستی نیست` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-133</b> — <code>tools/test_print_bottom_fit_offline.php</code> (372 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-133.01` | 1–4 | ناحیه | `بلوک رویه‌ای 1-4` | 0 | ⬜ |
| `TOOL-133.02` | 5–8 | تابع | `system_log` | 0 | ⬜ |
| `TOOL-133.03` | 9–33 | ناحیه | `بلوک رویه‌ای 9-33` | 0 | ⬜ |
| `TOOL-133.04` | 34–37 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-133.05` | 38–41 | ناحیه | `بلوک رویه‌ای 38-41` | 0 | ⬜ |
| `TOOL-133.06` | 42–52 | تابع | `check` | 0 | ⬜ |
| `TOOL-133.07` | 54–76 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-133.08` | 78–83 | تابع | `evalOnce` | 4 | ⬜ |
| `TOOL-133.09` | 84–111 | ناحیه | `بلوک رویه‌ای 84-111` | 0 | ⬜ |
| `TOOL-133.10` | 112–343 | ناحیه | `همان وضعیت شکایت: طرح ۲۰ میلی‌متر بالا خالی داشته ` | **8** | ⬜ |
| `TOOL-133.11` | 344–372 | ناحیه | `پروفایل پیلود: باید دو خط لاگ با فرمان عقب‌راندن و` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-134</b> — <code>tools/test_print_top_trim_offline.php</code> (170 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-134.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `TOOL-134.02` | 26–36 | تابع | `check` | 0 | ⬜ |
| `TOOL-134.03` | 38–60 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-134.04` | 61–68 | ناحیه | `بلوک رویه‌ای 61-68` | 4 | ⬜ |
| `TOOL-134.05` | 69–117 | ناحیه | `همان وضعیت قالب پیش‌فرضِ بدون لوگو: اولین متن در y` | 0 | ⬜ |
| `TOOL-134.06` | 118–118 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-134.07` | 119–170 | ناحیه | `بلوک رویه‌ای 119-170` | 7 | ⬜ |

</details>

<details>
<summary><b>TOOL-135</b> — <code>tools/test_release_134_offline.php</code> (177 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-135.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `TOOL-135.02` | 19–29 | تابع | `check` | 0 | ⬜ |
| `TOOL-135.03` | 31–51 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-135.04` | 52–88 | ناحیه | `بلوک رویه‌ای 52-88` | 1 | ⬜ |
| `TOOL-135.05` | 89–89 | ناحیه | `آزمون واقعی food_ticket_group_resolve: ردیفی که فق` | 0 | ⬜ |
| `TOOL-135.06` | 90–93 | تابع | `food_ticket_code` | 0 | ⬜ |
| `TOOL-135.07` | 94–97 | تابع | `food_ticket_card_key` | 0 | ⬜ |
| `TOOL-135.08` | 98–101 | تابع | `food_ticket_group_find_by_uid` | 0 | ⬜ |
| `TOOL-135.09` | 102–110 | ناحیه | `بلوک رویه‌ای 102-110` | 4 | ⬜ |
| `TOOL-135.10` | 111–114 | تابع | `cfg` | 0 | ⬜ |
| `TOOL-135.11` | 115–118 | تابع | `food_ticket_config` | 0 | ⬜ |
| `TOOL-135.12` | 119–122 | تابع | `setting` | 0 | ⬜ |
| `TOOL-135.13` | 123–125 | ناحیه | `بلوک رویه‌ای 123-125` | 4 | ⬜ |
| `TOOL-135.14` | 126–130 | تابع | `food_ticket_env_value` | 0 | ⬜ |
| `TOOL-135.15` | 131–177 | ناحیه | `بلوک رویه‌ای 131-177` | 7 | ⬜ |

</details>

<details>
<summary><b>TOOL-136</b> — <code>tools/test_release_notes_offline.php</code> (176 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-136.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 1 | ⬜ |
| `TOOL-136.02` | 17–27 | تابع | `check` | 0 | ⬜ |
| `TOOL-136.03` | 28–29 | ناحیه | `بلوک رویه‌ای 28-29` | 0 | ⬜ |
| `TOOL-136.04` | 30–30 | تابع | `e` | 0 | ⬜ |
| `TOOL-136.05` | 31–32 | ناحیه | `بلوک رویه‌ای 31-32` | 0 | ⬜ |
| `TOOL-136.06` | 33–33 | تابع | `csp_nonce` | 0 | ⬜ |
| `TOOL-136.07` | 34–47 | ناحیه | `بلوک رویه‌ای 34-47` | 0 | ⬜ |
| `TOOL-136.08` | 48–48 | ناحیه | `قاعدهٔ واقعی: نزولی بودن نسخه‌ها — نه «اولی حتماً ` | 0 | ⬜ |
| `TOOL-136.09` | 49–49 | ناحیه | `(۱.۶۳.۱، ۱.۶۴…) این تست باید سبز بماند؛ پین‌کردن ب` | 0 | ⬜ |
| `TOOL-136.10` | 50–104 | ناحیه | `به «نگهبان گذشته» تبدیل می‌کند نه «نگهبان قاعده».` | 0 | ⬜ |
| `TOOL-136.11` | 105–105 | ناحیه | `ریشهٔ باگ: روی خودِ عنصرِ کارت `display:flex` گذاش` | 0 | ⬜ |
| `TOOL-136.12` | 106–107 | ناحیه | `(که فقط display:none سطح مرورگر است) غلبه می‌کرد و` | 0 | ⬜ |
| `TOOL-136.13` | 108–152 | ناحیه | `فقط قاعدهٔ پایه سنجیده می‌شود، نه @media print.` | 0 | ⬜ |
| `TOOL-136.14` | 153–176 | ناحیه | `مجموع آیتم‌های **همهٔ** نسخه‌ها — با هر نسخهٔ تازه` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-137</b> — <code>tools/test_role_permissions_offline.php</code> (207 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-137.01` | 1–31 | ناحیه | `بلوک رویه‌ای 1-31` | 0 | ⬜ |
| `TOOL-137.02` | 32–42 | تابع | `check` | 0 | ⬜ |
| `TOOL-137.03` | 44–68 | کلاس | `MysqlishSqlite` | 5 | ⬜ |
| `TOOL-137.04` | 69–74 | ناحیه | `بلوک رویه‌ای 69-74` | 4 | ⬜ |
| `TOOL-137.05` | 75–79 | تابع | `db` | 0 | ⬜ |
| `TOOL-137.06` | 80–82 | ناحیه | `بلوک رویه‌ای 80-82` | 0 | ⬜ |
| `TOOL-137.07` | 83–86 | تابع | `setting` | 0 | ⬜ |
| `TOOL-137.08` | 87–90 | تابع | `save_setting` | 0 | ⬜ |
| `TOOL-137.09` | 91–93 | ناحیه | `بلوک رویه‌ای 91-93` | 0 | ⬜ |
| `TOOL-137.10` | 94–102 | تابع | `seedRows` | 1 | ⬜ |
| `TOOL-137.11` | 104–111 | تابع | `rowsOf` | 0 | ⬜ |
| `TOOL-137.12` | 113–118 | تابع | `sortedCopy` | 0 | ⬜ |
| `TOOL-137.13` | 119–145 | ناحیه | `بلوک رویه‌ای 119-145` | 0 | ⬜ |
| `TOOL-137.14` | 146–181 | ناحیه | `حتی اگر پیش‌فرض کد عوض شود، نقش ویرایش‌شده دست‌نخو` | 0 | ⬜ |
| `TOOL-137.15` | 182–183 | ناحیه | `نقش user: ردیف‌هایش دقیقاً همان پیش‌فرض قدیم است ⇒` | 0 | ⬜ |
| `TOOL-137.16` | 184–207 | ناحیه | `نقش support_manager: یک کد عمداً برداشته شده ⇒ بای` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-138</b> — <code>tools/test_scan_errors_offline.php</code> (238 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-138.01` | 1–14 | ناحیه | `بلوک رویه‌ای 1-14` | 0 | ⬜ |
| `TOOL-138.02` | 15–15 | ناحیه | `domain-scan.php بدون APP_ROOT بی‌صدا خارج می‌شود؛ ` | 0 | ⬜ |
| `TOOL-138.03` | 16–19 | ناحیه | `بدون هیچ خروجی و با کد خروج صفر تمام می‌شد و عملاً` | 0 | ⬜ |
| `TOOL-138.04` | 20–20 | ناحیه | `آزمون کاملاً آفلاین است: setting() (که در حالت عاد` | 0 | ⬜ |
| `TOOL-138.05` | 21–22 | ناحیه | `با مقدار پیش‌فرض جایگزین می‌کنیم تا domain_scan_fa` | 0 | ⬜ |
| `TOOL-138.06` | 23–26 | تابع | `setting` | 0 | ⬜ |
| `TOOL-138.07` | 27–31 | ناحیه | `بلوک رویه‌ای 27-31` | 0 | ⬜ |
| `TOOL-138.08` | 32–42 | تابع | `check` | 0 | ⬜ |
| `TOOL-138.09` | 43–95 | ناحیه | `بلوک رویه‌ای 43-95` | 0 | ⬜ |
| `TOOL-138.10` | 96–96 | ناحیه | `نام‌های نمایشی با حالت پیش‌فرض short_lower کوچک می` | 0 | ⬜ |
| `TOOL-138.11` | 97–120 | ناحیه | `پس انتظار تست هم شکل نمایشی است، نه نام خام ردیف.` | 0 | ⬜ |
| `TOOL-138.12` | 121–128 | ناحیه | `فرم تلاش دوباره در index.php است و هندلرش (domain_` | 0 | ⬜ |
| `TOOL-138.13` | 129–129 | ناحیه | `ALL_PROTOCOLS_FAILED ممکن است در متن خود «Access i` | 0 | ⬜ |
| `TOOL-138.14` | 130–238 | ناحیه | `باید به کد سرتاسری all_protocols نگاشت شود، نه acc` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-139</b> — <code>tools/test_schema_baseline_offline.php</code> (137 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-139.01` | 1–23 | ناحیه | `بلوک رویه‌ای 1-23` | 0 | ⬜ |
| `TOOL-139.02` | 24–34 | تابع | `check` | 0 | ⬜ |
| `TOOL-139.03` | 35–69 | ناحیه | `بلوک رویه‌ای 35-69` | 2 | ⬜ |
| `TOOL-139.04` | 70–86 | ناحیه | `مهاجرت‌هایی که ALTER/CREATE دارند و در baseline نی` | 0 | ⬜ |
| `TOOL-139.05` | 87–87 | ناحیه | `قاعدهٔ کلی — بدون استثنا: هر مهاجرت ساختاریِ روی د` | 0 | ⬜ |
| `TOOL-139.06` | 88–88 | ناحیه | `باشد. «ثبت‌نشده ماندن» همان هشدار کاذب است، حتی اگ` | 0 | ⬜ |
| `TOOL-139.07` | 89–95 | ناحیه | `باشد؛ چون فایلش به‌هرحال روی همین دیسک نشسته و گار` | 0 | ⬜ |
| `TOOL-139.08` | 96–96 | ناحیه | `۰۳۵: جدولش را install-empty-db می‌سازد ولی ردیف ثب` | 0 | ⬜ |
| `TOOL-139.09` | 97–97 | ناحیه | `فقط وقتی سنجیده می‌شود که فایلش (یا جدولش) در همین` | 0 | ⬜ |
| `TOOL-139.10` | 98–137 | ناحیه | `هیچ‌کدام نیست و همان‌جا هم هشدار کاذبی در کار نیست` | 2 | ⬜ |

</details>

<details>
<summary><b>TOOL-140</b> — <code>tools/test_selftest_offline.php</code> (282 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-140.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-140.02` | 22–32 | تابع | `check` | 0 | ⬜ |
| `TOOL-140.03` | 34–45 | کلاس | `FakeStmt` | 0 | ⬜ |
| `TOOL-140.04` | 47–66 | کلاس | `FakeDb` | 0 | ⬜ |
| `TOOL-140.05` | 67–69 | ناحیه | `بلوک رویه‌ای 67-69` | 0 | ⬜ |
| `TOOL-140.06` | 70–73 | تابع | `db` | 0 | ⬜ |
| `TOOL-140.07` | 74–75 | ناحیه | `بلوک رویه‌ای 74-75` | 0 | ⬜ |
| `TOOL-140.08` | 76–81 | تابع | `db_table_exists` | 0 | ⬜ |
| `TOOL-140.09` | 82–83 | ناحیه | `بلوک رویه‌ای 82-83` | 0 | ⬜ |
| `TOOL-140.10` | 84–87 | تابع | `cfg` | 0 | ⬜ |
| `TOOL-140.11` | 88–89 | ناحیه | `بلوک رویه‌ای 88-89` | 0 | ⬜ |
| `TOOL-140.12` | 90–90 | تابع | `user_can` | 0 | ⬜ |
| `TOOL-140.13` | 91–282 | ناحیه | `بلوک رویه‌ای 91-282` | 6 | ⬜ |

</details>

<details>
<summary><b>TOOL-141</b> — <code>tools/test_smb_collect_offline.php</code> (77 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-141.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `TOOL-141.02` | 22–77 | ناحیه | `عملگر «>» در PowerShell 5.1 خروجی را UTF-16LE می‌ن` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-142</b> — <code>tools/test_source_delete_offline.php</code> (218 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-142.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `TOOL-142.02` | 25–28 | تابع | `app_error_log` | 0 | ⬜ |
| `TOOL-142.03` | 29–32 | ناحیه | `بلوک رویه‌ای 29-32` | 0 | ⬜ |
| `TOOL-142.04` | 33–70 | تابع | `extractFunction` | 0 | ⬜ |
| `TOOL-142.05` | 71–95 | ناحیه | `بلوک رویه‌ای 71-95` | 4 | ⬜ |
| `TOOL-142.06` | 96–106 | تابع | `check` | 0 | ⬜ |
| `TOOL-142.07` | 107–218 | ناحیه | `بلوک رویه‌ای 107-218` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-143</b> — <code>tools/test_source_time_delete_offline.php</code> (98 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-143.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `TOOL-143.02` | 19–42 | ناحیه | `استخراج تابع‌های خالصِ ساخت شرط، بدون بارگذاری کل ` | **9** | ⬜ |
| `TOOL-143.03` | 43–43 | تابع | `food_ticket_normalize_digits` | 0 | ⬜ |
| `TOOL-143.04` | 44–98 | ناحیه | `بلوک رویه‌ای 44-98` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-144</b> — <code>tools/test_supervisor_grant_runtime.php</code> (101 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-144.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `TOOL-144.02` | 25–25 | ناحیه | `اگر پایگاه‌داده در دسترس نیست، این تست باید **رد ش` | 0 | ⬜ |
| `TOOL-144.03` | 26–26 | ناحیه | `دلیل: در CI و روی کلون تازه، سرویس دیتابیس معمولاً` | 0 | ⬜ |
| `TOOL-144.04` | 27–45 | ناحیه | `کل مجموعهٔ آزمون‌ها را قرمز می‌کند، در حالی که هیچ` | 0 | ⬜ |
| `TOOL-144.05` | 46–50 | تابع | `ok` | 0 | ⬜ |
| `TOOL-144.06` | 52–55 | تابع | `setset` | 1 | ⬜ |
| `TOOL-144.07` | 57–62 | ناحیه | `صحنه: کاربر ۹۹ با نقش واقعی «agent»، فقط در فهرست ` | 0 | ⬜ |
| `TOOL-144.08` | 63–63 | ناحیه | `نقش «user» عمداً: نقش agent خودش queue.view دارد و` | 0 | ⬜ |
| `TOOL-144.09` | 64–64 | ناحیه | `که آن دسترسی از نقش آمده یا از فهرست سوپروایزر. با` | 0 | ⬜ |
| `TOOL-144.10` | 65–73 | ناحیه | `حتماً از اعطای فهرست آمده است.` | 0 | ⬜ |
| `TOOL-144.11` | 74–101 | ناحیه | `تصمیم مالک (۲۰۲۶-۱۱-۱۶): «دسترسی‌های اضافی سوپروای` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-145</b> — <code>tools/test_ticket_txn_offline.php</code> (241 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-145.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `TOOL-145.02` | 29–39 | تابع | `check` | 0 | ⬜ |
| `TOOL-145.03` | 41–61 | تابع | `grabFunction` | 0 | ⬜ |
| `TOOL-145.04` | 63–74 | کلاس | `FakeStmt` | 0 | ⬜ |
| `TOOL-145.05` | 76–131 | کلاس | `FakeDb` | 4 | ⬜ |
| `TOOL-145.06` | 132–133 | ناحیه | `بلوک رویه‌ای 132-133` | 0 | ⬜ |
| `TOOL-145.07` | 134–137 | تابع | `db` | 0 | ⬜ |
| `TOOL-145.08` | 138–138 | ناحیه | `بلوک رویه‌ای 138-138` | 0 | ⬜ |
| `TOOL-145.09` | 139–142 | تابع | `system_log` | 0 | ⬜ |
| `TOOL-145.10` | 143–143 | ناحیه | `بلوک رویه‌ای 143-143` | 0 | ⬜ |
| `TOOL-145.11` | 144–147 | تابع | `__test_register_shutdown` | 0 | ⬜ |
| `TOOL-145.12` | 148–214 | ناحیه | `بلوک رویه‌ای 148-214` | 4 | ⬜ |
| `TOOL-145.13` | 215–215 | ناحیه | `۱.۳۷.۲: مرز بدنه با «اکشن بعدی» تعیین می‌شود، نه ب` | 0 | ⬜ |
| `TOOL-145.14` | 216–241 | ناحیه | `کامنت/اعتبارسنجی در create_ticket این آزمون را بی‌` | 3 | ⬜ |

</details>

<details>
<summary><b>TOOL-146</b> — <code>tools/test_traffic_form_offline.php</code> (75 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-146.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `TOOL-146.02` | 13–14 | ناحیه | `تابع اعتبارسنجی کد ملی در index.php است؛ اینجا فقط` | 0 | ⬜ |
| `TOOL-146.03` | 15–27 | تابع | `valid_iranian_national_code` | 0 | ⬜ |
| `TOOL-146.04` | 28–33 | ناحیه | `بلوک رویه‌ای 28-33` | 0 | ⬜ |
| `TOOL-146.05` | 34–39 | تابع | `check` | 0 | ⬜ |
| `TOOL-146.06` | 41–49 | تابع | `tryValidate` | 0 | ⬜ |
| `TOOL-146.07` | 51–55 | ناحیه | `کد ملی اشتباه ⇒ فیلد national_code` | 0 | ⬜ |
| `TOOL-146.08` | 56–59 | ناحیه | `کد ملی با ارقام فارسی و معتبر ⇒ بدون خطا` | 0 | ⬜ |
| `TOOL-146.09` | 60–63 | ناحیه | `نام خالی ⇒ فیلد full_name` | 0 | ⬜ |
| `TOOL-146.10` | 64–67 | ناحیه | `ورودی درست ⇒ بدون خطا (با کد ملی موردقبول stub)` | 0 | ⬜ |
| `TOOL-146.11` | 68–75 | ناحیه | `خطای عمومی (بدون فیلد) ⇒ TrafficInputException با ` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-147</b> — <code>tools/test_traffic_import_offline.php</code> (179 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-147.01` | 1–179 | ناحیه | `بلوک رویه‌ای 1-179` | **9** | ⬜ |

</details>

<details>
<summary><b>TOOL-148</b> — <code>tools/test_ui_features_offline.php</code> (45 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-148.01` | 1–45 | ناحیه | `بلوک رویه‌ای 1-45` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-149</b> — <code>tools/test_web_host_offline.php</code> (142 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-149.01` | 1–58 | ناحیه | `بلوک رویه‌ای 1-58` | 0 | ⬜ |
| `TOOL-149.02` | 59–83 | ناحیه | `۱) نسخهٔ فعلی روی دیسک: رفتار «قبل» نباید تغییر کن` | 0 | ⬜ |
| `TOOL-149.03` | 84–107 | ناحیه | `۲) نسخهٔ تازه‌تر پنل: شمارهٔ نسخه‌ها و نام فایل‌ها` | 0 | ⬜ |
| `TOOL-149.04` | 108–114 | ناحیه | `۳) نسخه‌ای که لنگر brand را ندارد (نام برند عوض شد` | 0 | ⬜ |
| `TOOL-149.05` | 115–120 | ناحیه | `۴) خروجی دوباره میزبانی شود → تزریق مضاعف/پیشوند م` | 0 | ⬜ |
| `TOOL-149.06` | 121–127 | ناحیه | `۵) حالت پنل مستقل (C#): مسیر ../assets/style.css ب` | 0 | ⬜ |
| `TOOL-149.07` | 128–142 | ناحیه | `۶) HTML حداقلی/شکسته: نباید خطا بدهد و باید بوت‌اس` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-150</b> — <code>tools/test_xlsx_style_offline.php</code> (169 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-150.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `TOOL-150.02` | 18–24 | تابع | `check` | 0 | ⬜ |
| `TOOL-150.03` | 26–94 | ناحیه | `ساخت یک فایل واقعی مثل خروجی شناسنامه` | 0 | ⬜ |
| `TOOL-150.04` | 95–130 | ناحیه | `عرض خودکار وقتی widths داده نشود` | 7 | ⬜ |
| `TOOL-150.05` | 131–148 | ناحیه | `simplexml ممکن است در CLI نصب نباشد؛ اعتبار XML را` | 0 | ⬜ |
| `TOOL-150.06` | 149–169 | ناحیه | `اعتبار همهٔ اجزای XML` | 5 | ⬜ |

</details>

<details>
<summary><b>TOOL-151</b> — <code>tools/topic_index.py</code> (209 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-151.01` | 1–26 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `TOOL-151.02` | 27–30 | بلوک | `read` | 0 | ⬜ |
| `TOOL-151.03` | 31–32 | بلوک | `-` | 0 | ⬜ |
| `TOOL-151.04` | 33–34 | بلوک | `-` | 0 | ⬜ |
| `TOOL-151.05` | 35–51 | بلوک | `page_labels` | 0 | ⬜ |
| `TOOL-151.06` | 52–67 | بلوک | `permission_catalog` | 0 | ⬜ |
| `TOOL-151.07` | 68–75 | بلوک | `page_permissions` | 0 | ⬜ |
| `TOOL-151.08` | 76–87 | بلوک | `db_tables` | 0 | ⬜ |
| `TOOL-151.09` | 88–89 | بلوک | `-` | 0 | ⬜ |
| `TOOL-151.10` | 90–97 | بلوک | `-` | 1 | ⬜ |
| `TOOL-151.11` | 98–117 | بلوک | `build_inverted` | 0 | ⬜ |
| `TOOL-151.12` | 118–157 | بلوک | `rank` | 1 | ⬜ |
| `TOOL-151.13` | 158–209 | بلوک | `main` | 0 | ⬜ |

</details>

<details>
<summary><b>TOOL-152</b> — <code>tools/verify_1_62_97.php</code> (166 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `TOOL-152.01` | 1–43 | ناحیه | `بلوک رویه‌ای 1-43` | 1 | ⬜ |
| `TOOL-152.02` | 44–65 | ناحیه | `۰) نسخهٔ نصب‌شده` | 1 | ⬜ |
| `TOOL-152.03` | 66–92 | ناحیه | `۱) کتابخانه` | 1 | ⬜ |
| `TOOL-152.04` | 93–109 | ناحیه | `۲) food-ticket.php` | 0 | ⬜ |
| `TOOL-152.05` | 110–128 | ناحیه | `۳) پنل` | 0 | ⬜ |
| `TOOL-152.06` | 129–151 | ناحیه | `۴) آیا چیزی به VERSION گره خورده؟` | 0 | ⬜ |
| `TOOL-152.07` | 152–166 | ناحیه | `۵) نتیجه` | 0 | ⬜ |

</details>

### DB — `DB`


<details>
<summary><b>DB-001</b> — <code>00_DIAGNOSE_TRAFFIC_FK.sql</code> (24 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-001.01` | 1–24 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-002</b> — <code>docs/architecture/035-request-center-appointments-rollback.sql</code> (46 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-002.01` | 1–46 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-003</b> — <code>docs/architecture/rebuild-final.sql</code> (98 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-003.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-003.02` | 10–98 | بلوک | `DROP TABLE IF` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-004</b> — <code>install-empty-db.sql</code> (1,653 خط، 122 آدرس، بازبینی‌شده 0/122)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-004.01` | 1–2 | بلوک | `=` | 0 | ⬜ |
| `DB-004.02` | 3–19 | بلوک | `=` | 0 | ⬜ |
| `DB-004.03` | 20–21 | بلوک | `=` | 0 | ⬜ |
| `DB-004.04` | 22–26 | بلوک | `CREATE TABLE settings` | 0 | ⬜ |
| `DB-004.05` | 27–36 | بلوک | `CREATE TABLE departments` | 0 | ⬜ |
| `DB-004.06` | 37–50 | بلوک | `CREATE TABLE org_companies` | 1 | ⬜ |
| `DB-004.07` | 51–69 | بلوک | `CREATE TABLE org_units` | 0 | ⬜ |
| `DB-004.08` | 70–72 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-004.09` | 73–76 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-004.10` | 77–84 | بلوک | `CREATE TABLE org_unit_managers` | 0 | ⬜ |
| `DB-004.11` | 85–120 | بلوک | `CREATE TABLE users` | 1 | ⬜ |
| `DB-004.12` | 121–122 | بلوک | `ALTER TABLE departments` | 1 | ⬜ |
| `DB-004.13` | 123–133 | بلوک | `CREATE TABLE handling_units` | 1 | ⬜ |
| `DB-004.14` | 134–135 | بلوک | `ALTER TABLE users` | 1 | ⬜ |
| `DB-004.15` | 136–143 | بلوک | `CREATE TABLE cd_dvd_types` | 1 | ⬜ |
| `DB-004.16` | 144–176 | بلوک | `CREATE TABLE cd_dvd_records` | 1 | ⬜ |
| `DB-004.17` | 177–187 | بلوک | `CREATE TABLE traffic_destinations` | 0 | ⬜ |
| `DB-004.18` | 188–216 | بلوک | `CREATE TABLE traffic_visits` | 1 | ⬜ |
| `DB-004.19` | 217–233 | بلوک | `CREATE TABLE traffic_visit_history` | 1 | ⬜ |
| `DB-004.20` | 234–243 | بلوک | `INSERT INTO traffic_destinations` | 1 | ⬜ |
| `DB-004.21` | 244–252 | بلوک | `CREATE TABLE categories` | 0 | ⬜ |
| `DB-004.22` | 253–278 | بلوک | `CREATE TABLE service_catalog` | 1 | ⬜ |
| `DB-004.23` | 279–291 | بلوک | `CREATE TABLE service_catalog_fields` | 1 | ⬜ |
| `DB-004.24` | 292–359 | بلوک | `CREATE TABLE tickets` | 1 | ⬜ |
| `DB-004.25` | 360–371 | بلوک | `CREATE TABLE ticket_messages` | 1 | ⬜ |
| `DB-004.26` | 372–385 | بلوک | `CREATE TABLE ticket_ratings` | 1 | ⬜ |
| `DB-004.27` | 386–396 | بلوک | `CREATE TABLE ticket_attachments` | 1 | ⬜ |
| `DB-004.28` | 397–435 | بلوک | `CREATE TABLE activity_logs` | 1 | ⬜ |
| `DB-004.29` | 436–442 | بلوک | `CREATE TABLE role_permissions` | 0 | ⬜ |
| `DB-004.30` | 443–460 | بلوک | `CREATE TABLE notifications` | 1 | ⬜ |
| `DB-004.31` | 461–468 | بلوک | `CREATE TABLE login_attempts` | 1 | ⬜ |
| `DB-004.32` | 469–478 | بلوک | `CREATE TABLE ticket_sla_alerts` | 1 | ⬜ |
| `DB-004.33` | 479–502 | بلوک | `CREATE TABLE ticket_events` | 1 | ⬜ |
| `DB-004.34` | 503–516 | بلوک | `CREATE TABLE knowledge_articles` | 1 | ⬜ |
| `DB-004.35` | 517–526 | بلوک | `CREATE TABLE holidays` | 1 | ⬜ |
| `DB-004.36` | 527–582 | بلوک | `CREATE TABLE assets` | 1 | ⬜ |
| `DB-004.37` | 583–584 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-004.38` | 585–599 | بلوک | `CREATE TABLE asset_memory_modules` | 1 | ⬜ |
| `DB-004.39` | 600–615 | بلوک | `CREATE TABLE asset_storage_devices` | 1 | ⬜ |
| `DB-004.40` | 616–628 | بلوک | `CREATE TABLE asset_graphics_adapters` | 1 | ⬜ |
| `DB-004.41` | 629–646 | بلوک | `CREATE TABLE asset_peripherals` | 1 | ⬜ |
| `DB-004.42` | 647–657 | بلوک | `CREATE TABLE asset_inventory_history` | 1 | ⬜ |
| `DB-004.43` | 658–675 | بلوک | `CREATE TABLE asset_history_events` | 1 | ⬜ |
| `DB-004.44` | 676–688 | بلوک | `CREATE TABLE asset_relations` | 1 | ⬜ |
| `DB-004.45` | 689–770 | بلوک | `CREATE TABLE asset_profiles` | 1 | ⬜ |
| `DB-004.46` | 771–788 | بلوک | `CREATE TABLE ola_policies` | 1 | ⬜ |
| `DB-004.47` | 789–798 | بلوک | `CREATE TABLE ticket_ola_alerts` | 1 | ⬜ |
| `DB-004.48` | 799–800 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-004.49` | 801–829 | بلوک | `CREATE TABLE change_records` | 1 | ⬜ |
| `DB-004.50` | 830–841 | بلوک | `CREATE TABLE change_approvals` | 1 | ⬜ |
| `DB-004.51` | 842–852 | بلوک | `CREATE TABLE change_ticket_links` | 1 | ⬜ |
| `DB-004.52` | 853–876 | بلوک | `CREATE TABLE problem_records` | 1 | ⬜ |
| `DB-004.53` | 877–887 | بلوک | `CREATE TABLE problem_ticket_links` | 1 | ⬜ |
| `DB-004.54` | 888–918 | بلوک | `CREATE TABLE food_ticket_config` | 1 | ⬜ |
| `DB-004.55` | 919–957 | بلوک | `CREATE TABLE food_ticket_events` | 1 | ⬜ |
| `DB-004.56` | 958–973 | بلوک | `CREATE TABLE food_ticket_worker_status` | 1 | ⬜ |
| `DB-004.57` | 974–987 | بلوک | `CREATE TABLE food_ticket_guest_cards` | 1 | ⬜ |
| `DB-004.58` | 988–1006 | بلوک | `CREATE TABLE food_ticket_groups` | 1 | ⬜ |
| `DB-004.59` | 1007–1018 | بلوک | `CREATE TABLE food_ticket_group_members` | 1 | ⬜ |
| `DB-004.60` | 1019–1045 | بلوک | `CREATE TABLE food_ticket_group_runs` | 1 | ⬜ |
| `DB-004.61` | 1046–1061 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-004.62` | 1062–1074 | بلوک | `CREATE TABLE food_ticket_day_visits` | 1 | ⬜ |
| `DB-004.63` | 1075–1085 | بلوک | `CREATE TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-004.64` | 1086–1101 | بلوک | `CREATE TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-004.65` | 1102–1133 | بلوک | `CREATE TABLE system_logs` | 0 | ⬜ |
| `DB-004.66` | 1134–1139 | بلوک | `CREATE TABLE ticket_number_seq` | 0 | ⬜ |
| `DB-004.67` | 1140–1148 | بلوک | `CREATE TABLE api_rate_limits` | 1 | ⬜ |
| `DB-004.68` | 1149–1161 | بلوک | `CREATE TABLE domain_scan_runs` | 1 | ⬜ |
| `DB-004.69` | 1162–1175 | بلوک | `CREATE TABLE domain_scan_queue` | 1 | ⬜ |
| `DB-004.70` | 1176–1176 | بلوک | `CREATE INDEX idx_tickets_status_priority` | 0 | ⬜ |
| `DB-004.71` | 1177–1177 | بلوک | `CREATE INDEX idx_users_is_active` | 0 | ⬜ |
| `DB-004.72` | 1178–1178 | بلوک | `CREATE INDEX idx_assets_lifecycle_status` | 0 | ⬜ |
| `DB-004.73` | 1179–1179 | بلوک | `CREATE INDEX idx_ola_priority_active` | 0 | ⬜ |
| `DB-004.74` | 1180–1180 | بلوک | `CREATE INDEX idx_food_ticket_events_event_type` | 0 | ⬜ |
| `DB-004.75` | 1181–1181 | بلوک | `CREATE INDEX idx_login_attempts_locked_until` | 0 | ⬜ |
| `DB-004.76` | 1182–1182 | بلوک | `CREATE INDEX idx_ticket_sla_alerts_alert_type` | 0 | ⬜ |
| `DB-004.77` | 1183–1183 | بلوک | `CREATE INDEX idx_ticket_ola_alerts_alert_type` | 0 | ⬜ |
| `DB-004.78` | 1184–1184 | بلوک | `CREATE INDEX idx_ticket_events_event_type` | 0 | ⬜ |
| `DB-004.79` | 1185–1185 | بلوک | `CREATE INDEX idx_knowledge_articles_is_published` | 0 | ⬜ |
| `DB-004.80` | 1186–1186 | بلوک | `CREATE INDEX idx_holidays_is_active` | 0 | ⬜ |
| `DB-004.81` | 1187–1187 | بلوک | `CREATE INDEX idx_departments_is_active` | 0 | ⬜ |
| `DB-004.82` | 1188–1188 | بلوک | `CREATE INDEX idx_handling_units_is_active` | 0 | ⬜ |
| `DB-004.83` | 1189–1189 | بلوک | `CREATE INDEX idx_categories_group_active` | 0 | ⬜ |
| `DB-004.84` | 1190–1190 | بلوک | `CREATE INDEX idx_service_catalog_group_active` | 0 | ⬜ |
| `DB-004.85` | 1191–1191 | بلوک | `CREATE INDEX idx_cd_dvd_records_media` | 0 | ⬜ |
| `DB-004.86` | 1192–1192 | بلوک | `CREATE INDEX idx_traffic_destinations_is_active` | 0 | ⬜ |
| `DB-004.87` | 1193–1193 | بلوک | `CREATE INDEX idx_domain_scan_runs_status` | 0 | ⬜ |
| `DB-004.88` | 1194–1196 | بلوک | `CREATE INDEX idx_domain_scan_queue_status` | 0 | ⬜ |
| `DB-004.89` | 1197–1206 | بلوک | `CREATE TABLE food_ticket_card_map` | 1 | ⬜ |
| `DB-004.90` | 1207–1219 | بلوک | `CREATE TABLE food_ticket_templates` | 1 | ⬜ |
| `DB-004.91` | 1220–1221 | بلوک | `=` | 0 | ⬜ |
| `DB-004.92` | 1222–1222 | بلوک | `=` | 0 | ⬜ |
| `DB-004.93` | 1223–1227 | بلوک | `INSERT INTO settings` | 1 | ⬜ |
| `DB-004.94` | 1228–1230 | بلوک | `INSERT INTO settings` | 1 | ⬜ |
| `DB-004.95` | 1231–1232 | بلوک | `=` | 0 | ⬜ |
| `DB-004.96` | 1233–1240 | بلوک | `=` | 0 | ⬜ |
| `DB-004.97` | 1241–1242 | بلوک | `=` | 0 | ⬜ |
| `DB-004.98` | 1243–1247 | بلوک | `=` | 0 | ⬜ |
| `DB-004.99` | 1248–1259 | بلوک | `CREATE TABLE food_catalog` | 1 | ⬜ |
| `DB-004.100` | 1260–1274 | بلوک | `CREATE TABLE food_calendar` | 1 | ⬜ |
| `DB-004.101` | 1275–1289 | بلوک | `CREATE TABLE food_calendar_items` | 1 | ⬜ |
| `DB-004.102` | 1290–1305 | بلوک | `CREATE TABLE food_orders` | 1 | ⬜ |
| `DB-004.103` | 1306–1323 | بلوک | `CREATE TABLE food_order_logs` | 1 | ⬜ |
| `DB-004.104` | 1324–1341 | بلوک | `CREATE TABLE food_order_cancel_requests` | 1 | ⬜ |
| `DB-004.105` | 1342–1345 | بلوک | `=` | 0 | ⬜ |
| `DB-004.106` | 1346–1347 | بلوک | `=` | 0 | ⬜ |
| `DB-004.107` | 1348–1359 | بلوک | `CREATE TABLE chatbot_faq` | 0 | ⬜ |
| `DB-004.108` | 1360–1372 | بلوک | `CREATE TABLE chatbot_logs` | 0 | ⬜ |
| `DB-004.109` | 1373–1381 | بلوک | `CREATE TABLE system_incidents` | 1 | ⬜ |
| `DB-004.110` | 1382–1405 | بلوک | `CREATE TABLE food_guest_requests` | 1 | ⬜ |
| `DB-004.111` | 1406–1437 | بلوک | `CREATE TABLE food_meal_reconciliations` | 1 | ⬜ |
| `DB-004.112` | 1438–1459 | بلوک | `CREATE TABLE food_monthly_invoices` | 0 | ⬜ |
| `DB-004.113` | 1460–1473 | بلوک | `CREATE TABLE user_access_groups` | 1 | ⬜ |
| `DB-004.114` | 1474–1485 | بلوک | `CREATE TABLE user_access_group_members` | 1 | ⬜ |
| `DB-004.115` | 1486–1501 | بلوک | `CREATE TABLE user_access_group_permissions` | 1 | ⬜ |
| `DB-004.116` | 1502–1527 | بلوک | `CREATE TABLE log_issues` | 1 | ⬜ |
| `DB-004.117` | 1528–1537 | بلوک | `CREATE TABLE logging_health_alerts` | 1 | ⬜ |
| `DB-004.118` | 1538–1553 | بلوک | `CREATE TABLE log_issue_history` | 0 | ⬜ |
| `DB-004.119` | 1554–1560 | بلوک | `CREATE TABLE log_issue_events` | 0 | ⬜ |
| `DB-004.120` | 1561–1579 | بلوک | `CREATE TABLE log_issue_notifications` | 1 | ⬜ |
| `DB-004.121` | 1580–1589 | بلوک | `CREATE TABLE schema_updates` | 0 | ⬜ |
| `DB-004.122` | 1590–1653 | بلوک | `CREATE TABLE request_center_appointments` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-005</b> — <code>migrations/001-local-accounts.sql</code> (7 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-005.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-005.02` | 3–4 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-005.03` | 5–7 | بلوک | `ALTER TABLE users` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-006</b> — <code>migrations/002-traffic-notices.sql</code> (6 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-006.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-006.02` | 4–4 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-006.03` | 5–6 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-007</b> — <code>migrations/003-sync-schema-151.sql</code> (1,771 خط، 750 آدرس، بازبینی‌شده 0/750)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-007.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-007.02` | 5–8 | بلوک | `CREATE TABLE settings` | 0 | ⬜ |
| `DB-007.03` | 9–17 | بلوک | `CREATE TABLE departments` | 0 | ⬜ |
| `DB-007.04` | 18–31 | بلوک | `CREATE TABLE org_units` | 0 | ⬜ |
| `DB-007.05` | 32–38 | بلوک | `CREATE TABLE org_unit_managers` | 0 | ⬜ |
| `DB-007.06` | 39–71 | بلوک | `CREATE TABLE users` | 1 | ⬜ |
| `DB-007.07` | 72–81 | بلوک | `CREATE TABLE handling_units` | 1 | ⬜ |
| `DB-007.08` | 82–86 | بلوک | `CREATE TABLE cd_dvd_types` | 0 | ⬜ |
| `DB-007.09` | 87–118 | بلوک | `CREATE TABLE cd_dvd_records` | 1 | ⬜ |
| `DB-007.10` | 119–126 | بلوک | `CREATE TABLE traffic_destinations` | 0 | ⬜ |
| `DB-007.11` | 127–152 | بلوک | `CREATE TABLE traffic_visits` | 1 | ⬜ |
| `DB-007.12` | 153–160 | بلوک | `CREATE TABLE categories` | 0 | ⬜ |
| `DB-007.13` | 161–185 | بلوک | `CREATE TABLE service_catalog` | 1 | ⬜ |
| `DB-007.14` | 186–197 | بلوک | `CREATE TABLE service_catalog_fields` | 1 | ⬜ |
| `DB-007.15` | 198–263 | بلوک | `CREATE TABLE tickets` | 1 | ⬜ |
| `DB-007.16` | 264–274 | بلوک | `CREATE TABLE ticket_messages` | 1 | ⬜ |
| `DB-007.17` | 275–287 | بلوک | `CREATE TABLE ticket_ratings` | 1 | ⬜ |
| `DB-007.18` | 288–297 | بلوک | `CREATE TABLE ticket_attachments` | 1 | ⬜ |
| `DB-007.19` | 298–322 | بلوک | `CREATE TABLE activity_logs` | 1 | ⬜ |
| `DB-007.20` | 323–328 | بلوک | `CREATE TABLE role_permissions` | 0 | ⬜ |
| `DB-007.21` | 329–342 | بلوک | `CREATE TABLE notifications` | 1 | ⬜ |
| `DB-007.22` | 343–349 | بلوک | `CREATE TABLE login_attempts` | 1 | ⬜ |
| `DB-007.23` | 350–358 | بلوک | `CREATE TABLE ticket_sla_alerts` | 1 | ⬜ |
| `DB-007.24` | 359–372 | بلوک | `CREATE TABLE ticket_events` | 1 | ⬜ |
| `DB-007.25` | 373–385 | بلوک | `CREATE TABLE knowledge_articles` | 1 | ⬜ |
| `DB-007.26` | 386–394 | بلوک | `CREATE TABLE holidays` | 1 | ⬜ |
| `DB-007.27` | 395–445 | بلوک | `CREATE TABLE assets` | 1 | ⬜ |
| `DB-007.28` | 446–459 | بلوک | `CREATE TABLE asset_memory_modules` | 1 | ⬜ |
| `DB-007.29` | 460–474 | بلوک | `CREATE TABLE asset_storage_devices` | 1 | ⬜ |
| `DB-007.30` | 475–486 | بلوک | `CREATE TABLE asset_graphics_adapters` | 1 | ⬜ |
| `DB-007.31` | 487–503 | بلوک | `CREATE TABLE asset_peripherals` | 1 | ⬜ |
| `DB-007.32` | 504–513 | بلوک | `CREATE TABLE asset_inventory_history` | 1 | ⬜ |
| `DB-007.33` | 514–530 | بلوک | `CREATE TABLE asset_history_events` | 1 | ⬜ |
| `DB-007.34` | 531–542 | بلوک | `CREATE TABLE asset_relations` | 1 | ⬜ |
| `DB-007.35` | 543–623 | بلوک | `CREATE TABLE asset_profiles` | 1 | ⬜ |
| `DB-007.36` | 624–640 | بلوک | `CREATE TABLE ola_policies` | 1 | ⬜ |
| `DB-007.37` | 641–649 | بلوک | `CREATE TABLE ticket_ola_alerts` | 1 | ⬜ |
| `DB-007.38` | 650–677 | بلوک | `CREATE TABLE change_records` | 1 | ⬜ |
| `DB-007.39` | 678–688 | بلوک | `CREATE TABLE change_approvals` | 1 | ⬜ |
| `DB-007.40` | 689–698 | بلوک | `CREATE TABLE change_ticket_links` | 1 | ⬜ |
| `DB-007.41` | 699–721 | بلوک | `CREATE TABLE problem_records` | 1 | ⬜ |
| `DB-007.42` | 722–731 | بلوک | `CREATE TABLE problem_ticket_links` | 1 | ⬜ |
| `DB-007.43` | 732–749 | بلوک | `CREATE TABLE food_ticket_config` | 1 | ⬜ |
| `DB-007.44` | 750–786 | بلوک | `CREATE TABLE food_ticket_events` | 1 | ⬜ |
| `DB-007.45` | 787–799 | بلوک | `CREATE TABLE food_ticket_worker_status` | 1 | ⬜ |
| `DB-007.46` | 800–812 | بلوک | `CREATE TABLE food_ticket_guest_cards` | 1 | ⬜ |
| `DB-007.47` | 813–828 | بلوک | `CREATE TABLE food_ticket_groups` | 1 | ⬜ |
| `DB-007.48` | 829–838 | بلوک | `CREATE TABLE food_ticket_group_members` | 1 | ⬜ |
| `DB-007.49` | 839–862 | بلوک | `CREATE TABLE food_ticket_group_runs` | 1 | ⬜ |
| `DB-007.50` | 863–876 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-007.51` | 877–886 | بلوک | `CREATE TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-007.52` | 887–901 | بلوک | `CREATE TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-007.53` | 902–913 | بلوک | `CREATE TABLE system_logs` | 0 | ⬜ |
| `DB-007.54` | 914–924 | بلوک | `CREATE TABLE chatbot_faq` | 0 | ⬜ |
| `DB-007.55` | 925–936 | بلوک | `CREATE TABLE chatbot_logs` | 0 | ⬜ |
| `DB-007.56` | 937–944 | بلوک | `CREATE TABLE system_incidents` | 1 | ⬜ |
| `DB-007.57` | 945–948 | بلوک | `CREATE TABLE ticket_number_seq` | 0 | ⬜ |
| `DB-007.58` | 949–955 | بلوک | `CREATE TABLE api_rate_limits` | 1 | ⬜ |
| `DB-007.59` | 956–967 | بلوک | `CREATE TABLE domain_scan_runs` | 1 | ⬜ |
| `DB-007.60` | 968–979 | بلوک | `CREATE TABLE domain_scan_queue` | 1 | ⬜ |
| `DB-007.61` | 980–988 | بلوک | `CREATE TABLE food_ticket_card_map` | 1 | ⬜ |
| `DB-007.62` | 989–999 | بلوک | `CREATE TABLE food_ticket_templates` | 1 | ⬜ |
| `DB-007.63` | 1000–1010 | بلوک | `CREATE TABLE food_catalog` | 1 | ⬜ |
| `DB-007.64` | 1011–1028 | بلوک | `CREATE TABLE food_guest_requests` | 1 | ⬜ |
| `DB-007.65` | 1029–1041 | بلوک | `CREATE TABLE food_calendar` | 1 | ⬜ |
| `DB-007.66` | 1042–1055 | بلوک | `CREATE TABLE food_calendar_items` | 1 | ⬜ |
| `DB-007.67` | 1056–1069 | بلوک | `CREATE TABLE food_orders` | 1 | ⬜ |
| `DB-007.68` | 1070–1086 | بلوک | `CREATE TABLE food_order_logs` | 1 | ⬜ |
| `DB-007.69` | 1087–1087 | بلوک | `ALTER TABLE settings` | 0 | ⬜ |
| `DB-007.70` | 1088–1088 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-007.71` | 1089–1089 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-007.72` | 1090–1090 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-007.73` | 1091–1091 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-007.74` | 1092–1092 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-007.75` | 1093–1093 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-007.76` | 1094–1094 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-007.77` | 1095–1095 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-007.78` | 1096–1096 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-007.79` | 1097–1097 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-007.80` | 1098–1098 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-007.81` | 1099–1099 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-007.82` | 1100–1100 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-007.83` | 1101–1101 | بلوک | `ALTER TABLE org_unit_managers` | 0 | ⬜ |
| `DB-007.84` | 1102–1102 | بلوک | `ALTER TABLE org_unit_managers` | 0 | ⬜ |
| `DB-007.85` | 1103–1103 | بلوک | `ALTER TABLE org_unit_managers` | 0 | ⬜ |
| `DB-007.86` | 1104–1104 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.87` | 1105–1105 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.88` | 1106–1106 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.89` | 1107–1107 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.90` | 1108–1108 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.91` | 1109–1109 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.92` | 1110–1110 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.93` | 1111–1111 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.94` | 1112–1112 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.95` | 1113–1113 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.96` | 1114–1114 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.97` | 1115–1115 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.98` | 1116–1116 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.99` | 1117–1117 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.100` | 1118–1118 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.101` | 1119–1119 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.102` | 1120–1120 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.103` | 1121–1121 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.104` | 1122–1122 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.105` | 1123–1123 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.106` | 1124–1124 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.107` | 1125–1125 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.108` | 1126–1126 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.109` | 1127–1127 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-007.110` | 1128–1128 | بلوک | `ALTER TABLE handling_units` | 0 | ⬜ |
| `DB-007.111` | 1129–1129 | بلوک | `ALTER TABLE handling_units` | 0 | ⬜ |
| `DB-007.112` | 1130–1130 | بلوک | `ALTER TABLE handling_units` | 0 | ⬜ |
| `DB-007.113` | 1131–1131 | بلوک | `ALTER TABLE handling_units` | 0 | ⬜ |
| `DB-007.114` | 1132–1132 | بلوک | `ALTER TABLE handling_units` | 0 | ⬜ |
| `DB-007.115` | 1133–1133 | بلوک | `ALTER TABLE cd_dvd_types` | 0 | ⬜ |
| `DB-007.116` | 1134–1134 | بلوک | `ALTER TABLE cd_dvd_types` | 0 | ⬜ |
| `DB-007.117` | 1135–1135 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.118` | 1136–1136 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.119` | 1137–1137 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.120` | 1138–1138 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.121` | 1139–1139 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.122` | 1140–1140 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.123` | 1141–1141 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.124` | 1142–1142 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.125` | 1143–1143 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.126` | 1144–1144 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.127` | 1145–1145 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.128` | 1146–1146 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.129` | 1147–1147 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.130` | 1148–1148 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.131` | 1149–1149 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.132` | 1150–1150 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.133` | 1151–1151 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.134` | 1152–1152 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.135` | 1153–1153 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.136` | 1154–1154 | بلوک | `ALTER TABLE cd_dvd_records` | 0 | ⬜ |
| `DB-007.137` | 1155–1155 | بلوک | `ALTER TABLE cd_dvd_records` | 1 | ⬜ |
| `DB-007.138` | 1156–1156 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |
| `DB-007.139` | 1157–1157 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |
| `DB-007.140` | 1158–1158 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |
| `DB-007.141` | 1159–1159 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |
| `DB-007.142` | 1160–1160 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.143` | 1161–1161 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.144` | 1162–1162 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.145` | 1163–1163 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.146` | 1164–1164 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.147` | 1165–1165 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.148` | 1166–1166 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.149` | 1167–1167 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.150` | 1168–1168 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.151` | 1169–1169 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.152` | 1170–1170 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.153` | 1171–1171 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.154` | 1172–1172 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.155` | 1173–1173 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.156` | 1174–1174 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.157` | 1175–1175 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.158` | 1176–1176 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |
| `DB-007.159` | 1177–1177 | بلوک | `ALTER TABLE traffic_visits` | 1 | ⬜ |
| `DB-007.160` | 1178–1178 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-007.161` | 1179–1179 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-007.162` | 1180–1180 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-007.163` | 1181–1181 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-007.164` | 1182–1182 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-007.165` | 1183–1183 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.166` | 1184–1184 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.167` | 1185–1185 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.168` | 1186–1186 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.169` | 1187–1187 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.170` | 1188–1188 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.171` | 1189–1189 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.172` | 1190–1190 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.173` | 1191–1191 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.174` | 1192–1192 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |
| `DB-007.175` | 1193–1193 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.176` | 1194–1194 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.177` | 1195–1195 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.178` | 1196–1196 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-007.179` | 1197–1197 | بلوک | `ALTER TABLE service_catalog` | 1 | ⬜ |
| `DB-007.180` | 1198–1198 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-007.181` | 1199–1199 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-007.182` | 1200–1200 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-007.183` | 1201–1201 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-007.184` | 1202–1202 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-007.185` | 1203–1203 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-007.186` | 1204–1204 | بلوک | `ALTER TABLE service_catalog_fields` | 0 | ⬜ |
| `DB-007.187` | 1205–1205 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.188` | 1206–1206 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.189` | 1207–1207 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.190` | 1208–1208 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.191` | 1209–1209 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.192` | 1210–1210 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.193` | 1211–1211 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.194` | 1212–1212 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.195` | 1213–1213 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.196` | 1214–1214 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.197` | 1215–1215 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.198` | 1216–1216 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.199` | 1217–1217 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.200` | 1218–1218 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.201` | 1219–1219 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.202` | 1220–1220 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.203` | 1221–1221 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.204` | 1222–1222 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.205` | 1223–1223 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.206` | 1224–1224 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.207` | 1225–1225 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.208` | 1226–1226 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.209` | 1227–1227 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.210` | 1228–1228 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.211` | 1229–1229 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.212` | 1230–1230 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.213` | 1231–1231 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.214` | 1232–1232 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.215` | 1233–1233 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.216` | 1234–1234 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.217` | 1235–1235 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.218` | 1236–1236 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.219` | 1237–1237 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.220` | 1238–1238 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.221` | 1239–1239 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.222` | 1240–1240 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.223` | 1241–1241 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.224` | 1242–1242 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.225` | 1243–1243 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.226` | 1244–1244 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-007.227` | 1245–1245 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-007.228` | 1246–1246 | بلوک | `ALTER TABLE ticket_messages` | 0 | ⬜ |
| `DB-007.229` | 1247–1247 | بلوک | `ALTER TABLE ticket_messages` | 0 | ⬜ |
| `DB-007.230` | 1248–1248 | بلوک | `ALTER TABLE ticket_messages` | 0 | ⬜ |
| `DB-007.231` | 1249–1249 | بلوک | `ALTER TABLE ticket_messages` | 0 | ⬜ |
| `DB-007.232` | 1250–1250 | بلوک | `ALTER TABLE ticket_messages` | 0 | ⬜ |
| `DB-007.233` | 1251–1251 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-007.234` | 1252–1252 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-007.235` | 1253–1253 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-007.236` | 1254–1254 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-007.237` | 1255–1255 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-007.238` | 1256–1256 | بلوک | `ALTER TABLE ticket_ratings` | 0 | ⬜ |
| `DB-007.239` | 1257–1257 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-007.240` | 1258–1258 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-007.241` | 1259–1259 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-007.242` | 1260–1260 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-007.243` | 1261–1261 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-007.244` | 1262–1262 | بلوک | `ALTER TABLE ticket_attachments` | 0 | ⬜ |
| `DB-007.245` | 1263–1263 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.246` | 1264–1264 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.247` | 1265–1265 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.248` | 1266–1266 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.249` | 1267–1267 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.250` | 1268–1268 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.251` | 1269–1269 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.252` | 1270–1270 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.253` | 1271–1271 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.254` | 1272–1272 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.255` | 1273–1273 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.256` | 1274–1274 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.257` | 1275–1275 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.258` | 1276–1276 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.259` | 1277–1277 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.260` | 1278–1278 | بلوک | `ALTER TABLE activity_logs` | 0 | ⬜ |
| `DB-007.261` | 1279–1279 | بلوک | `ALTER TABLE role_permissions` | 0 | ⬜ |
| `DB-007.262` | 1280–1280 | بلوک | `ALTER TABLE role_permissions` | 0 | ⬜ |
| `DB-007.263` | 1281–1281 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-007.264` | 1282–1282 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-007.265` | 1283–1283 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-007.266` | 1284–1284 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-007.267` | 1285–1285 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-007.268` | 1286–1286 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-007.269` | 1287–1287 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-007.270` | 1288–1288 | بلوک | `ALTER TABLE notifications` | 0 | ⬜ |
| `DB-007.271` | 1289–1289 | بلوک | `ALTER TABLE login_attempts` | 0 | ⬜ |
| `DB-007.272` | 1290–1290 | بلوک | `ALTER TABLE login_attempts` | 0 | ⬜ |
| `DB-007.273` | 1291–1291 | بلوک | `ALTER TABLE login_attempts` | 0 | ⬜ |
| `DB-007.274` | 1292–1292 | بلوک | `ALTER TABLE login_attempts` | 1 | ⬜ |
| `DB-007.275` | 1293–1293 | بلوک | `ALTER TABLE ticket_sla_alerts` | 0 | ⬜ |
| `DB-007.276` | 1294–1294 | بلوک | `ALTER TABLE ticket_sla_alerts` | 0 | ⬜ |
| `DB-007.277` | 1295–1295 | بلوک | `ALTER TABLE ticket_sla_alerts` | 0 | ⬜ |
| `DB-007.278` | 1296–1296 | بلوک | `ALTER TABLE ticket_sla_alerts` | 0 | ⬜ |
| `DB-007.279` | 1297–1297 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-007.280` | 1298–1298 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-007.281` | 1299–1299 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-007.282` | 1300–1300 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-007.283` | 1301–1301 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-007.284` | 1302–1302 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-007.285` | 1303–1303 | بلوک | `ALTER TABLE ticket_events` | 0 | ⬜ |
| `DB-007.286` | 1304–1304 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-007.287` | 1305–1305 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-007.288` | 1306–1306 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-007.289` | 1307–1307 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-007.290` | 1308–1308 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-007.291` | 1309–1309 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-007.292` | 1310–1310 | بلوک | `ALTER TABLE knowledge_articles` | 0 | ⬜ |
| `DB-007.293` | 1311–1311 | بلوک | `ALTER TABLE knowledge_articles` | 1 | ⬜ |
| `DB-007.294` | 1312–1312 | بلوک | `ALTER TABLE holidays` | 0 | ⬜ |
| `DB-007.295` | 1313–1313 | بلوک | `ALTER TABLE holidays` | 0 | ⬜ |
| `DB-007.296` | 1314–1314 | بلوک | `ALTER TABLE holidays` | 0 | ⬜ |
| `DB-007.297` | 1315–1315 | بلوک | `ALTER TABLE holidays` | 0 | ⬜ |
| `DB-007.298` | 1316–1316 | بلوک | `ALTER TABLE holidays` | 0 | ⬜ |
| `DB-007.299` | 1317–1317 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.300` | 1318–1318 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.301` | 1319–1319 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.302` | 1320–1320 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.303` | 1321–1321 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.304` | 1322–1322 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.305` | 1323–1323 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.306` | 1324–1324 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.307` | 1325–1325 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.308` | 1326–1326 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.309` | 1327–1327 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.310` | 1328–1328 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.311` | 1329–1329 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.312` | 1330–1330 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.313` | 1331–1331 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.314` | 1332–1332 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.315` | 1333–1333 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.316` | 1334–1334 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.317` | 1335–1335 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.318` | 1336–1336 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.319` | 1337–1337 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.320` | 1338–1338 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.321` | 1339–1339 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.322` | 1340–1340 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.323` | 1341–1341 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.324` | 1342–1342 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.325` | 1343–1343 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.326` | 1344–1344 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.327` | 1345–1345 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.328` | 1346–1346 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.329` | 1347–1347 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.330` | 1348–1348 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.331` | 1349–1349 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.332` | 1350–1350 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.333` | 1351–1351 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.334` | 1352–1352 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.335` | 1353–1353 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.336` | 1354–1354 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.337` | 1355–1355 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.338` | 1356–1356 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.339` | 1357–1357 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.340` | 1358–1358 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.341` | 1359–1359 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-007.342` | 1360–1360 | بلوک | `ALTER TABLE assets` | 1 | ⬜ |
| `DB-007.343` | 1361–1361 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-007.344` | 1362–1362 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-007.345` | 1363–1363 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-007.346` | 1364–1364 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-007.347` | 1365–1365 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-007.348` | 1366–1366 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-007.349` | 1367–1367 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-007.350` | 1368–1368 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-007.351` | 1369–1369 | بلوک | `ALTER TABLE asset_memory_modules` | 0 | ⬜ |
| `DB-007.352` | 1370–1370 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-007.353` | 1371–1371 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-007.354` | 1372–1372 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-007.355` | 1373–1373 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-007.356` | 1374–1374 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-007.357` | 1375–1375 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-007.358` | 1376–1376 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-007.359` | 1377–1377 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-007.360` | 1378–1378 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-007.361` | 1379–1379 | بلوک | `ALTER TABLE asset_storage_devices` | 0 | ⬜ |
| `DB-007.362` | 1380–1380 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-007.363` | 1381–1381 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-007.364` | 1382–1382 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-007.365` | 1383–1383 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-007.366` | 1384–1384 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-007.367` | 1385–1385 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-007.368` | 1386–1386 | بلوک | `ALTER TABLE asset_graphics_adapters` | 0 | ⬜ |
| `DB-007.369` | 1387–1387 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.370` | 1388–1388 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.371` | 1389–1389 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.372` | 1390–1390 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.373` | 1391–1391 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.374` | 1392–1392 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.375` | 1393–1393 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.376` | 1394–1394 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.377` | 1395–1395 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.378` | 1396–1396 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.379` | 1397–1397 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.380` | 1398–1398 | بلوک | `ALTER TABLE asset_peripherals` | 0 | ⬜ |
| `DB-007.381` | 1399–1399 | بلوک | `ALTER TABLE asset_inventory_history` | 0 | ⬜ |
| `DB-007.382` | 1400–1400 | بلوک | `ALTER TABLE asset_inventory_history` | 0 | ⬜ |
| `DB-007.383` | 1401–1401 | بلوک | `ALTER TABLE asset_inventory_history` | 0 | ⬜ |
| `DB-007.384` | 1402–1402 | بلوک | `ALTER TABLE asset_inventory_history` | 0 | ⬜ |
| `DB-007.385` | 1403–1403 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-007.386` | 1404–1404 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-007.387` | 1405–1405 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-007.388` | 1406–1406 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-007.389` | 1407–1407 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-007.390` | 1408–1408 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-007.391` | 1409–1409 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-007.392` | 1410–1410 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-007.393` | 1411–1411 | بلوک | `ALTER TABLE asset_history_events` | 0 | ⬜ |
| `DB-007.394` | 1412–1412 | بلوک | `ALTER TABLE asset_relations` | 0 | ⬜ |
| `DB-007.395` | 1413–1413 | بلوک | `ALTER TABLE asset_relations` | 0 | ⬜ |
| `DB-007.396` | 1414–1414 | بلوک | `ALTER TABLE asset_relations` | 0 | ⬜ |
| `DB-007.397` | 1415–1415 | بلوک | `ALTER TABLE asset_relations` | 0 | ⬜ |
| `DB-007.398` | 1416–1416 | بلوک | `ALTER TABLE asset_relations` | 0 | ⬜ |
| `DB-007.399` | 1417–1417 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.400` | 1418–1418 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.401` | 1419–1419 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.402` | 1420–1420 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.403` | 1421–1421 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.404` | 1422–1422 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.405` | 1423–1423 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.406` | 1424–1424 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.407` | 1425–1425 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.408` | 1426–1426 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.409` | 1427–1427 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.410` | 1428–1428 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.411` | 1429–1429 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.412` | 1430–1430 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.413` | 1431–1431 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.414` | 1432–1432 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.415` | 1433–1433 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.416` | 1434–1434 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.417` | 1435–1435 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.418` | 1436–1436 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.419` | 1437–1437 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.420` | 1438–1438 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.421` | 1439–1439 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.422` | 1440–1440 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.423` | 1441–1441 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.424` | 1442–1442 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.425` | 1443–1443 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.426` | 1444–1444 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.427` | 1445–1445 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.428` | 1446–1446 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.429` | 1447–1447 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.430` | 1448–1448 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.431` | 1449–1449 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.432` | 1450–1450 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.433` | 1451–1451 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.434` | 1452–1452 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.435` | 1453–1453 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.436` | 1454–1454 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.437` | 1455–1455 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.438` | 1456–1456 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.439` | 1457–1457 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.440` | 1458–1458 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.441` | 1459–1459 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.442` | 1460–1460 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.443` | 1461–1461 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.444` | 1462–1462 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.445` | 1463–1463 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.446` | 1464–1464 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.447` | 1465–1465 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.448` | 1466–1466 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.449` | 1467–1467 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.450` | 1468–1468 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.451` | 1469–1469 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.452` | 1470–1470 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.453` | 1471–1471 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.454` | 1472–1472 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.455` | 1473–1473 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.456` | 1474–1474 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.457` | 1475–1475 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.458` | 1476–1476 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.459` | 1477–1477 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.460` | 1478–1478 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.461` | 1479–1479 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.462` | 1480–1480 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.463` | 1481–1481 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.464` | 1482–1482 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.465` | 1483–1483 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.466` | 1484–1484 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.467` | 1485–1485 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.468` | 1486–1486 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.469` | 1487–1487 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.470` | 1488–1488 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.471` | 1489–1489 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.472` | 1490–1490 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |
| `DB-007.473` | 1491–1491 | بلوک | `ALTER TABLE asset_profiles` | 1 | ⬜ |
| `DB-007.474` | 1492–1492 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-007.475` | 1493–1493 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-007.476` | 1494–1494 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-007.477` | 1495–1495 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-007.478` | 1496–1496 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-007.479` | 1497–1497 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-007.480` | 1498–1498 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-007.481` | 1499–1499 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-007.482` | 1500–1500 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-007.483` | 1501–1501 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-007.484` | 1502–1502 | بلوک | `ALTER TABLE ola_policies` | 1 | ⬜ |
| `DB-007.485` | 1503–1503 | بلوک | `ALTER TABLE ticket_ola_alerts` | 0 | ⬜ |
| `DB-007.486` | 1504–1504 | بلوک | `ALTER TABLE ticket_ola_alerts` | 0 | ⬜ |
| `DB-007.487` | 1505–1505 | بلوک | `ALTER TABLE ticket_ola_alerts` | 0 | ⬜ |
| `DB-007.488` | 1506–1506 | بلوک | `ALTER TABLE ticket_ola_alerts` | 0 | ⬜ |
| `DB-007.489` | 1507–1507 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.490` | 1508–1508 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.491` | 1509–1509 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.492` | 1510–1510 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.493` | 1511–1511 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.494` | 1512–1512 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.495` | 1513–1513 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.496` | 1514–1514 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.497` | 1515–1515 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.498` | 1516–1516 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.499` | 1517–1517 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.500` | 1518–1518 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.501` | 1519–1519 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.502` | 1520–1520 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.503` | 1521–1521 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.504` | 1522–1522 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.505` | 1523–1523 | بلوک | `ALTER TABLE change_records` | 0 | ⬜ |
| `DB-007.506` | 1524–1524 | بلوک | `ALTER TABLE change_records` | 1 | ⬜ |
| `DB-007.507` | 1525–1525 | بلوک | `ALTER TABLE change_approvals` | 0 | ⬜ |
| `DB-007.508` | 1526–1526 | بلوک | `ALTER TABLE change_approvals` | 0 | ⬜ |
| `DB-007.509` | 1527–1527 | بلوک | `ALTER TABLE change_approvals` | 0 | ⬜ |
| `DB-007.510` | 1528–1528 | بلوک | `ALTER TABLE change_approvals` | 0 | ⬜ |
| `DB-007.511` | 1529–1529 | بلوک | `ALTER TABLE change_approvals` | 0 | ⬜ |
| `DB-007.512` | 1530–1530 | بلوک | `ALTER TABLE change_ticket_links` | 0 | ⬜ |
| `DB-007.513` | 1531–1531 | بلوک | `ALTER TABLE change_ticket_links` | 0 | ⬜ |
| `DB-007.514` | 1532–1532 | بلوک | `ALTER TABLE change_ticket_links` | 0 | ⬜ |
| `DB-007.515` | 1533–1533 | بلوک | `ALTER TABLE change_ticket_links` | 0 | ⬜ |
| `DB-007.516` | 1534–1534 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.517` | 1535–1535 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.518` | 1536–1536 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.519` | 1537–1537 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.520` | 1538–1538 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.521` | 1539–1539 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.522` | 1540–1540 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.523` | 1541–1541 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.524` | 1542–1542 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.525` | 1543–1543 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.526` | 1544–1544 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.527` | 1545–1545 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.528` | 1546–1546 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |
| `DB-007.529` | 1547–1547 | بلوک | `ALTER TABLE problem_records` | 1 | ⬜ |
| `DB-007.530` | 1548–1548 | بلوک | `ALTER TABLE problem_ticket_links` | 0 | ⬜ |
| `DB-007.531` | 1549–1549 | بلوک | `ALTER TABLE problem_ticket_links` | 0 | ⬜ |
| `DB-007.532` | 1550–1550 | بلوک | `ALTER TABLE problem_ticket_links` | 0 | ⬜ |
| `DB-007.533` | 1551–1551 | بلوک | `ALTER TABLE problem_ticket_links` | 0 | ⬜ |
| `DB-007.534` | 1552–1552 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.535` | 1553–1553 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.536` | 1554–1554 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.537` | 1555–1555 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.538` | 1556–1556 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.539` | 1557–1557 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.540` | 1558–1558 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.541` | 1559–1559 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.542` | 1560–1560 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.543` | 1561–1561 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.544` | 1562–1562 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.545` | 1563–1563 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.546` | 1564–1564 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-007.547` | 1565–1565 | بلوک | `ALTER TABLE food_ticket_config` | 1 | ⬜ |
| `DB-007.548` | 1566–1566 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.549` | 1567–1567 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.550` | 1568–1568 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.551` | 1569–1569 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.552` | 1570–1570 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.553` | 1571–1571 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.554` | 1572–1572 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.555` | 1573–1573 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.556` | 1574–1574 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.557` | 1575–1575 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.558` | 1576–1576 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.559` | 1577–1577 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.560` | 1578–1578 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.561` | 1579–1579 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.562` | 1580–1580 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.563` | 1581–1581 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.564` | 1582–1582 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.565` | 1583–1583 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.566` | 1584–1584 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.567` | 1585–1585 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.568` | 1586–1586 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.569` | 1587–1587 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.570` | 1588–1588 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.571` | 1589–1589 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.572` | 1590–1590 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.573` | 1591–1591 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.574` | 1592–1592 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.575` | 1593–1593 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-007.576` | 1594–1594 | بلوک | `ALTER TABLE food_ticket_events` | 1 | ⬜ |
| `DB-007.577` | 1595–1595 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-007.578` | 1596–1596 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-007.579` | 1597–1597 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-007.580` | 1598–1598 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-007.581` | 1599–1599 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-007.582` | 1600–1600 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-007.583` | 1601–1601 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-007.584` | 1602–1602 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-007.585` | 1603–1603 | بلوک | `ALTER TABLE food_ticket_worker_status` | 0 | ⬜ |
| `DB-007.586` | 1604–1604 | بلوک | `ALTER TABLE food_ticket_worker_status` | 1 | ⬜ |
| `DB-007.587` | 1605–1605 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-007.588` | 1606–1606 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-007.589` | 1607–1607 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-007.590` | 1608–1608 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-007.591` | 1609–1609 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-007.592` | 1610–1610 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 0 | ⬜ |
| `DB-007.593` | 1611–1611 | بلوک | `ALTER TABLE food_ticket_guest_cards` | 1 | ⬜ |
| `DB-007.594` | 1612–1612 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-007.595` | 1613–1613 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-007.596` | 1614–1614 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-007.597` | 1615–1615 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-007.598` | 1616–1616 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-007.599` | 1617–1617 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-007.600` | 1618–1618 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-007.601` | 1619–1619 | بلوک | `ALTER TABLE food_ticket_groups` | 1 | ⬜ |
| `DB-007.602` | 1620–1620 | بلوک | `ALTER TABLE food_ticket_group_members` | 0 | ⬜ |
| `DB-007.603` | 1621–1621 | بلوک | `ALTER TABLE food_ticket_group_members` | 0 | ⬜ |
| `DB-007.604` | 1622–1622 | بلوک | `ALTER TABLE food_ticket_group_members` | 0 | ⬜ |
| `DB-007.605` | 1623–1623 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.606` | 1624–1624 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.607` | 1625–1625 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.608` | 1626–1626 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.609` | 1627–1627 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.610` | 1628–1628 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.611` | 1629–1629 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.612` | 1630–1630 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.613` | 1631–1631 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.614` | 1632–1632 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.615` | 1633–1633 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.616` | 1634–1634 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.617` | 1635–1635 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.618` | 1636–1636 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.619` | 1637–1637 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.620` | 1638–1638 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.621` | 1639–1639 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.622` | 1640–1640 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-007.623` | 1641–1641 | بلوک | `ALTER TABLE food_ticket_group_runs` | 1 | ⬜ |
| `DB-007.624` | 1642–1642 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-007.625` | 1643–1643 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-007.626` | 1644–1644 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-007.627` | 1645–1645 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-007.628` | 1646–1646 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-007.629` | 1647–1647 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 0 | ⬜ |
| `DB-007.630` | 1648–1648 | بلوک | `ALTER TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-007.631` | 1649–1649 | بلوک | `ALTER TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-007.632` | 1650–1650 | بلوک | `ALTER TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-007.633` | 1651–1651 | بلوک | `ALTER TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-007.634` | 1652–1652 | بلوک | `ALTER TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-007.635` | 1653–1653 | بلوک | `ALTER TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-007.636` | 1654–1654 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-007.637` | 1655–1655 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-007.638` | 1656–1656 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-007.639` | 1657–1657 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-007.640` | 1658–1658 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-007.641` | 1659–1659 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-007.642` | 1660–1660 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-007.643` | 1661–1661 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-007.644` | 1662–1662 | بلوک | `ALTER TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-007.645` | 1663–1663 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-007.646` | 1664–1664 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-007.647` | 1665–1665 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-007.648` | 1666–1666 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-007.649` | 1667–1667 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-007.650` | 1668–1668 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-007.651` | 1669–1669 | بلوک | `ALTER TABLE system_logs` | 0 | ⬜ |
| `DB-007.652` | 1670–1670 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-007.653` | 1671–1671 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-007.654` | 1672–1672 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-007.655` | 1673–1673 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-007.656` | 1674–1674 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-007.657` | 1675–1675 | بلوک | `ALTER TABLE chatbot_faq` | 0 | ⬜ |
| `DB-007.658` | 1676–1676 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-007.659` | 1677–1677 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-007.660` | 1678–1678 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-007.661` | 1679–1679 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-007.662` | 1680–1680 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-007.663` | 1681–1681 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-007.664` | 1682–1682 | بلوک | `ALTER TABLE chatbot_logs` | 0 | ⬜ |
| `DB-007.665` | 1683–1683 | بلوک | `ALTER TABLE system_incidents` | 0 | ⬜ |
| `DB-007.666` | 1684–1684 | بلوک | `ALTER TABLE system_incidents` | 0 | ⬜ |
| `DB-007.667` | 1685–1685 | بلوک | `ALTER TABLE system_incidents` | 0 | ⬜ |
| `DB-007.668` | 1686–1686 | بلوک | `ALTER TABLE system_incidents` | 0 | ⬜ |
| `DB-007.669` | 1687–1687 | بلوک | `ALTER TABLE system_incidents` | 1 | ⬜ |
| `DB-007.670` | 1688–1688 | بلوک | `ALTER TABLE ticket_number_seq` | 0 | ⬜ |
| `DB-007.671` | 1689–1689 | بلوک | `ALTER TABLE api_rate_limits` | 0 | ⬜ |
| `DB-007.672` | 1690–1690 | بلوک | `ALTER TABLE api_rate_limits` | 0 | ⬜ |
| `DB-007.673` | 1691–1691 | بلوک | `ALTER TABLE api_rate_limits` | 1 | ⬜ |
| `DB-007.674` | 1692–1692 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-007.675` | 1693–1693 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-007.676` | 1694–1694 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-007.677` | 1695–1695 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-007.678` | 1696–1696 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-007.679` | 1697–1697 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-007.680` | 1698–1698 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-007.681` | 1699–1699 | بلوک | `ALTER TABLE domain_scan_runs` | 0 | ⬜ |
| `DB-007.682` | 1700–1700 | بلوک | `ALTER TABLE domain_scan_runs` | 1 | ⬜ |
| `DB-007.683` | 1701–1701 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-007.684` | 1702–1702 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-007.685` | 1703–1703 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-007.686` | 1704–1704 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-007.687` | 1705–1705 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-007.688` | 1706–1706 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-007.689` | 1707–1707 | بلوک | `ALTER TABLE domain_scan_queue` | 0 | ⬜ |
| `DB-007.690` | 1708–1708 | بلوک | `ALTER TABLE domain_scan_queue` | 1 | ⬜ |
| `DB-007.691` | 1709–1709 | بلوک | `ALTER TABLE food_ticket_card_map` | 0 | ⬜ |
| `DB-007.692` | 1710–1710 | بلوک | `ALTER TABLE food_ticket_card_map` | 0 | ⬜ |
| `DB-007.693` | 1711–1711 | بلوک | `ALTER TABLE food_ticket_card_map` | 0 | ⬜ |
| `DB-007.694` | 1712–1712 | بلوک | `ALTER TABLE food_ticket_card_map` | 0 | ⬜ |
| `DB-007.695` | 1713–1713 | بلوک | `ALTER TABLE food_ticket_card_map` | 1 | ⬜ |
| `DB-007.696` | 1714–1714 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-007.697` | 1715–1715 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-007.698` | 1716–1716 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-007.699` | 1717–1717 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-007.700` | 1718–1718 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-007.701` | 1719–1719 | بلوک | `ALTER TABLE food_ticket_templates` | 0 | ⬜ |
| `DB-007.702` | 1720–1720 | بلوک | `ALTER TABLE food_ticket_templates` | 1 | ⬜ |
| `DB-007.703` | 1721–1721 | بلوک | `ALTER TABLE food_catalog` | 0 | ⬜ |
| `DB-007.704` | 1722–1722 | بلوک | `ALTER TABLE food_catalog` | 0 | ⬜ |
| `DB-007.705` | 1723–1723 | بلوک | `ALTER TABLE food_catalog` | 0 | ⬜ |
| `DB-007.706` | 1724–1724 | بلوک | `ALTER TABLE food_catalog` | 0 | ⬜ |
| `DB-007.707` | 1725–1725 | بلوک | `ALTER TABLE food_catalog` | 0 | ⬜ |
| `DB-007.708` | 1726–1726 | بلوک | `ALTER TABLE food_catalog` | 1 | ⬜ |
| `DB-007.709` | 1727–1727 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.710` | 1728–1728 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.711` | 1729–1729 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.712` | 1730–1730 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.713` | 1731–1731 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.714` | 1732–1732 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.715` | 1733–1733 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.716` | 1734–1734 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.717` | 1735–1735 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.718` | 1736–1736 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.719` | 1737–1737 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.720` | 1738–1738 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |
| `DB-007.721` | 1739–1739 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-007.722` | 1740–1740 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-007.723` | 1741–1741 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-007.724` | 1742–1742 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-007.725` | 1743–1743 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-007.726` | 1744–1744 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-007.727` | 1745–1745 | بلوک | `ALTER TABLE food_calendar` | 0 | ⬜ |
| `DB-007.728` | 1746–1746 | بلوک | `ALTER TABLE food_calendar` | 1 | ⬜ |
| `DB-007.729` | 1747–1747 | بلوک | `ALTER TABLE food_calendar_items` | 0 | ⬜ |
| `DB-007.730` | 1748–1748 | بلوک | `ALTER TABLE food_calendar_items` | 0 | ⬜ |
| `DB-007.731` | 1749–1749 | بلوک | `ALTER TABLE food_calendar_items` | 0 | ⬜ |
| `DB-007.732` | 1750–1750 | بلوک | `ALTER TABLE food_calendar_items` | 0 | ⬜ |
| `DB-007.733` | 1751–1751 | بلوک | `ALTER TABLE food_calendar_items` | 0 | ⬜ |
| `DB-007.734` | 1752–1752 | بلوک | `ALTER TABLE food_calendar_items` | 1 | ⬜ |
| `DB-007.735` | 1753–1753 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-007.736` | 1754–1754 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-007.737` | 1755–1755 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-007.738` | 1756–1756 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-007.739` | 1757–1757 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-007.740` | 1758–1758 | بلوک | `ALTER TABLE food_orders` | 0 | ⬜ |
| `DB-007.741` | 1759–1759 | بلوک | `ALTER TABLE food_orders` | 1 | ⬜ |
| `DB-007.742` | 1760–1760 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-007.743` | 1761–1761 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-007.744` | 1762–1762 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-007.745` | 1763–1763 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-007.746` | 1764–1764 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-007.747` | 1765–1765 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-007.748` | 1766–1766 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-007.749` | 1767–1769 | بلوک | `ALTER TABLE food_order_logs` | 0 | ⬜ |
| `DB-007.750` | 1770–1771 | بلوک | `ALTER TABLE traffic_visits` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-008</b> — <code>migrations/004-ticket-cancellation.sql</code> (47 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-008.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-008.02` | 3–45 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-008.03` | 46–47 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-009</b> — <code>migrations/005-normalize-pending-status.sql</code> (9 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-009.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-009.02` | 6–9 | بلوک | `UPDATE tickets` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-010</b> — <code>migrations/006-food-meal-type.sql</code> (55 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-010.01` | 1–55 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-011</b> — <code>migrations/007-traffic-visit-history.sql</code> (18 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-011.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-011.02` | 2–18 | بلوک | `CREATE TABLE traffic_visit_history` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-012</b> — <code>migrations/008-traffic-destination-approver.sql</code> (3 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-012.01` | 1–3 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-013</b> — <code>migrations/009-traffic-destination-multi-approver.sql</code> (6 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-013.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-013.02` | 2–3 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |
| `DB-013.03` | 4–6 | بلوک | `ALTER TABLE traffic_destinations` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-014</b> — <code>migrations/010-org-liaison-units.sql</code> (6 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-014.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-014.02` | 4–6 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-015</b> — <code>migrations/011-manual-attendance-delivery.sql</code> (11 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-015.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-015.02` | 3–11 | بلوک | `UPDATE food_ticket_events` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-016</b> — <code>migrations/012-manual-attendance-clears-absences.sql</code> (25 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-016.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-016.02` | 4–25 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-017</b> — <code>migrations/013-manual-attendance-record-backfill.sql</code> (60 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-017.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-017.02` | 5–6 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-017.03` | 7–8 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-017.04` | 9–11 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-017.05` | 12–24 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-017.06` | 25–60 | بلوک | `UPDATE food_ticket_events` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-018</b> — <code>migrations/014-asset-identity-dedupe.sql</code> (54 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-018.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-018.02` | 6–30 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-018.03` | 31–34 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.04` | 35–35 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.05` | 36–36 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.06` | 37–37 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.07` | 38–39 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.08` | 40–41 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.09` | 42–44 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.10` | 45–45 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.11` | 46–46 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.12` | 47–47 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.13` | 48–48 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.14` | 49–49 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.15` | 50–51 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.16` | 52–52 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-018.17` | 53–54 | بلوک | `UPDATE assets` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-019</b> — <code>migrations/015-asset-identity-hotfix.sql</code> (38 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-019.01` | 1–25 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-019.02` | 26–30 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-019.03` | 31–34 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-019.04` | 35–35 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-019.05` | 36–36 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-019.06` | 37–38 | بلوک | `UPDATE assets` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-020</b> — <code>migrations/016-auto-asset-finish.sql</code> (141 خط، 25 آدرس، بازبینی‌شده 0/25)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-020.01` | 1–19 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-020.02` | 20–21 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.03` | 22–22 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.04` | 23–23 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.05` | 24–24 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.06` | 25–25 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.07` | 26–27 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.08` | 28–28 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.09` | 29–29 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.10` | 30–30 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.11` | 31–31 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.12` | 32–32 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.13` | 33–33 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.14` | 34–34 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.15` | 35–37 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.16` | 38–38 | بلوک | `DROP TABLE IF EXISTS tmp_asset_dedupe_map` | 0 | ⬜ |
| `DB-020.17` | 39–44 | بلوک | `CREATE TABLE tmp_asset_dedupe_map` | 0 | ⬜ |
| `DB-020.18` | 45–51 | بلوک | `INSERT INTO tmp_asset_dedupe_map` | 1 | ⬜ |
| `DB-020.19` | 52–52 | بلوک | `DROP TABLE IF EXISTS asset_dedupe_backup_016` | 0 | ⬜ |
| `DB-020.20` | 53–54 | بلوک | `CREATE TABLE asset_dedupe_backup_016` | 0 | ⬜ |
| `DB-020.21` | 55–56 | بلوک | `ALTER TABLE asset_dedupe_backup_016` | 0 | ⬜ |
| `DB-020.22` | 57–61 | بلوک | `UPDATE asset_dedupe_backup_016` | 1 | ⬜ |
| `DB-020.23` | 62–87 | بلوک | `UPDATE assets` | 1 | ⬜ |
| `DB-020.24` | 88–138 | بلوک | `CREATE PROCEDURE sp_asset_dedupe_relink` | 1 | ⬜ |
| `DB-020.25` | 139–141 | بلوک | `DROP TABLE IF EXISTS tmp_asset_dedupe_map` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-021</b> — <code>migrations/017-org-tree-order-rollback.sql</code> (5 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-021.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-021.02` | 3–5 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-022</b> — <code>migrations/017-org-tree-order.sql</code> (5 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-022.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-022.02` | 3–5 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-023</b> — <code>migrations/018-org-company-separation.sql</code> (34 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-023.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `DB-023.02` | 3–16 | بلوک | `CREATE TABLE org_companies` | 1 | ⬜ |
| `DB-023.03` | 17–17 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-023.04` | 18–18 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-023.05` | 19–19 | بلوک | `ALTER TABLE org_units` | 0 | ⬜ |
| `DB-023.06` | 20–22 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-023.07` | 23–23 | بلوک | `UPDATE org_units` | 1 | ⬜ |
| `DB-023.08` | 24–26 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-023.09` | 27–30 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-023.10` | 31–34 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-024</b> — <code>migrations/019-traffic-destination-link.sql</code> (12 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-024.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-024.02` | 10–12 | بلوک | `UPDATE traffic_visits` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-025</b> — <code>migrations/020-traffic-destination-upgrade.sql</code> (12 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-025.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-026</b> — <code>migrations/021-food-auto-receipts.sql</code> (51 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-026.01` | 1–51 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-027</b> — <code>migrations/022-food-meal-reconciliation-invoices.sql</code> (51 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-027.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-027.02` | 4–30 | بلوک | `CREATE TABLE food_meal_reconciliations` | 1 | ⬜ |
| `DB-027.03` | 31–51 | بلوک | `CREATE TABLE food_monthly_invoices` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-028</b> — <code>migrations/023-user-access-groups.sql</code> (39 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-028.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-028.02` | 3–16 | بلوک | `CREATE TABLE user_access_groups` | 1 | ⬜ |
| `DB-028.03` | 17–28 | بلوک | `CREATE TABLE user_access_group_members` | 1 | ⬜ |
| `DB-028.04` | 29–39 | بلوک | `CREATE TABLE user_access_group_permissions` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-029</b> — <code>migrations/024-central-logging.sql</code> (83 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-029.01` | 1–83 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-030</b> — <code>migrations/025-logging-issues-alerts.sql</code> (51 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-030.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-030.02` | 4–28 | بلوک | `CREATE TABLE log_issues` | 1 | ⬜ |
| `DB-030.03` | 29–51 | بلوک | `CREATE TABLE logging_health_alerts` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-031</b> — <code>migrations/026-logging-legacy-result-unknown.sql</code> (72 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-031.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-031.02` | 3–14 | بلوک | `UPDATE activity_logs` | 1 | ⬜ |
| `DB-031.03` | 15–19 | بلوک | `UPDATE log_issues` | 1 | ⬜ |
| `DB-031.04` | 20–37 | بلوک | `CREATE TABLE log_issue_history` | 0 | ⬜ |
| `DB-031.05` | 38–55 | بلوک | `CREATE TABLE log_issue_events` | 1 | ⬜ |
| `DB-031.06` | 56–72 | بلوک | `CREATE TABLE log_issue_notifications` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-032</b> — <code>migrations/027-logging-correlation-ids.sql</code> (59 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-032.01` | 1–55 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-032.02` | 56–56 | بلوک | `UPDATE system_logs` | 1 | ⬜ |
| `DB-032.03` | 57–57 | بلوک | `UPDATE activity_logs` | 1 | ⬜ |
| `DB-032.04` | 58–59 | بلوک | `UPDATE ticket_events` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-033</b> — <code>migrations/028-logging-fingerprint-v2.sql</code> (15 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-033.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-034</b> — <code>migrations/029-activity-result-default-unknown.sql</code> (16 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-034.01` | 1–16 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-035</b> — <code>migrations/030-logging-notification-idempotency-and-ticket-history.sql</code> (48 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-035.01` | 1–48 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-036</b> — <code>migrations/031-food-worker-window.sql</code> (100 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-036.01` | 1–100 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-037</b> — <code>migrations/032-schema-updates-baseline.sql</code> (36 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-037.01` | 1–36 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-038</b> — <code>migrations/033-logging-legacy-context-collation.sql</code> (9 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-038.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-038.02` | 5–9 | بلوک | `UPDATE log_issues` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-039</b> — <code>migrations/034-logging-escalation.sql</code> (34 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-039.01` | 1–34 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-040</b> — <code>migrations/035-request-center-appointments.sql</code> (195 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-040.01` | 1–13 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `DB-040.02` | 14–35 | بلوک | `CREATE TABLE request_center_appointments` | 1 | ⬜ |
| `DB-040.03` | 36–118 | بلوک | `CREATE TABLE ticket_number_seq` | 0 | ⬜ |
| `DB-040.04` | 119–159 | بلوک | `INSERT INTO categories` | 1 | ⬜ |
| `DB-040.05` | 160–195 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-041</b> — <code>migrations/036-food-meal-leftover-disposition.sql</code> (10 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-041.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-041.02` | 4–10 | بلوک | `ALTER TABLE food_meal_reconciliations` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-042</b> — <code>migrations/037-food-auto-receipts-delivered.sql</code> (12 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-042.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-042.02` | 6–12 | بلوک | `UPDATE food_ticket_events` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-043</b> — <code>rollback-1.38-food-orders.sql</code> (10 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-043.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-043.02` | 5–5 | بلوک | `DROP TABLE IF EXISTS food_order_logs` | 0 | ⬜ |
| `DB-043.03` | 6–6 | بلوک | `DROP TABLE IF EXISTS food_orders` | 0 | ⬜ |
| `DB-043.04` | 7–7 | بلوک | `DROP TABLE IF EXISTS food_calendar_items` | 0 | ⬜ |
| `DB-043.05` | 8–8 | بلوک | `DROP TABLE IF EXISTS food_calendar` | 0 | ⬜ |
| `DB-043.06` | 9–10 | بلوک | `DROP TABLE IF EXISTS food_catalog` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-044</b> — <code>schema.sql</code> (1,605 خط، 111 آدرس، بازبینی‌شده 0/111)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-044.01` | 1–5 | بلوک | `CREATE TABLE settings` | 0 | ⬜ |
| `DB-044.02` | 6–15 | بلوک | `CREATE TABLE departments` | 0 | ⬜ |
| `DB-044.03` | 16–29 | بلوک | `CREATE TABLE org_companies` | 1 | ⬜ |
| `DB-044.04` | 30–48 | بلوک | `CREATE TABLE org_units` | 0 | ⬜ |
| `DB-044.05` | 49–51 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-044.06` | 52–55 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-044.07` | 56–63 | بلوک | `CREATE TABLE org_unit_managers` | 0 | ⬜ |
| `DB-044.08` | 64–99 | بلوک | `CREATE TABLE users` | 1 | ⬜ |
| `DB-044.09` | 100–101 | بلوک | `ALTER TABLE departments` | 1 | ⬜ |
| `DB-044.10` | 102–112 | بلوک | `CREATE TABLE handling_units` | 1 | ⬜ |
| `DB-044.11` | 113–114 | بلوک | `ALTER TABLE users` | 1 | ⬜ |
| `DB-044.12` | 115–122 | بلوک | `CREATE TABLE cd_dvd_types` | 1 | ⬜ |
| `DB-044.13` | 123–155 | بلوک | `CREATE TABLE cd_dvd_records` | 1 | ⬜ |
| `DB-044.14` | 156–166 | بلوک | `CREATE TABLE traffic_destinations` | 0 | ⬜ |
| `DB-044.15` | 167–195 | بلوک | `CREATE TABLE traffic_visits` | 1 | ⬜ |
| `DB-044.16` | 196–212 | بلوک | `CREATE TABLE traffic_visit_history` | 1 | ⬜ |
| `DB-044.17` | 213–222 | بلوک | `INSERT INTO traffic_destinations` | 1 | ⬜ |
| `DB-044.18` | 223–231 | بلوک | `CREATE TABLE categories` | 0 | ⬜ |
| `DB-044.19` | 232–257 | بلوک | `CREATE TABLE service_catalog` | 1 | ⬜ |
| `DB-044.20` | 258–270 | بلوک | `CREATE TABLE service_catalog_fields` | 1 | ⬜ |
| `DB-044.21` | 271–338 | بلوک | `CREATE TABLE tickets` | 1 | ⬜ |
| `DB-044.22` | 339–350 | بلوک | `CREATE TABLE ticket_messages` | 1 | ⬜ |
| `DB-044.23` | 351–364 | بلوک | `CREATE TABLE ticket_ratings` | 1 | ⬜ |
| `DB-044.24` | 365–375 | بلوک | `CREATE TABLE ticket_attachments` | 1 | ⬜ |
| `DB-044.25` | 376–414 | بلوک | `CREATE TABLE activity_logs` | 1 | ⬜ |
| `DB-044.26` | 415–421 | بلوک | `CREATE TABLE role_permissions` | 0 | ⬜ |
| `DB-044.27` | 422–439 | بلوک | `CREATE TABLE notifications` | 1 | ⬜ |
| `DB-044.28` | 440–447 | بلوک | `CREATE TABLE login_attempts` | 1 | ⬜ |
| `DB-044.29` | 448–457 | بلوک | `CREATE TABLE ticket_sla_alerts` | 1 | ⬜ |
| `DB-044.30` | 458–481 | بلوک | `CREATE TABLE ticket_events` | 1 | ⬜ |
| `DB-044.31` | 482–495 | بلوک | `CREATE TABLE knowledge_articles` | 1 | ⬜ |
| `DB-044.32` | 496–505 | بلوک | `CREATE TABLE holidays` | 1 | ⬜ |
| `DB-044.33` | 506–561 | بلوک | `CREATE TABLE assets` | 1 | ⬜ |
| `DB-044.34` | 562–563 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-044.35` | 564–578 | بلوک | `CREATE TABLE asset_memory_modules` | 1 | ⬜ |
| `DB-044.36` | 579–594 | بلوک | `CREATE TABLE asset_storage_devices` | 1 | ⬜ |
| `DB-044.37` | 595–607 | بلوک | `CREATE TABLE asset_graphics_adapters` | 1 | ⬜ |
| `DB-044.38` | 608–625 | بلوک | `CREATE TABLE asset_peripherals` | 1 | ⬜ |
| `DB-044.39` | 626–636 | بلوک | `CREATE TABLE asset_inventory_history` | 1 | ⬜ |
| `DB-044.40` | 637–654 | بلوک | `CREATE TABLE asset_history_events` | 1 | ⬜ |
| `DB-044.41` | 655–667 | بلوک | `CREATE TABLE asset_relations` | 1 | ⬜ |
| `DB-044.42` | 668–749 | بلوک | `CREATE TABLE asset_profiles` | 1 | ⬜ |
| `DB-044.43` | 750–767 | بلوک | `CREATE TABLE ola_policies` | 1 | ⬜ |
| `DB-044.44` | 768–777 | بلوک | `CREATE TABLE ticket_ola_alerts` | 1 | ⬜ |
| `DB-044.45` | 778–779 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-044.46` | 780–808 | بلوک | `CREATE TABLE change_records` | 1 | ⬜ |
| `DB-044.47` | 809–820 | بلوک | `CREATE TABLE change_approvals` | 1 | ⬜ |
| `DB-044.48` | 821–831 | بلوک | `CREATE TABLE change_ticket_links` | 1 | ⬜ |
| `DB-044.49` | 832–855 | بلوک | `CREATE TABLE problem_records` | 1 | ⬜ |
| `DB-044.50` | 856–866 | بلوک | `CREATE TABLE problem_ticket_links` | 1 | ⬜ |
| `DB-044.51` | 867–897 | بلوک | `CREATE TABLE food_ticket_config` | 1 | ⬜ |
| `DB-044.52` | 898–936 | بلوک | `CREATE TABLE food_ticket_events` | 1 | ⬜ |
| `DB-044.53` | 937–952 | بلوک | `CREATE TABLE food_ticket_worker_status` | 1 | ⬜ |
| `DB-044.54` | 953–966 | بلوک | `CREATE TABLE food_ticket_guest_cards` | 1 | ⬜ |
| `DB-044.55` | 967–985 | بلوک | `CREATE TABLE food_ticket_groups` | 1 | ⬜ |
| `DB-044.56` | 986–997 | بلوک | `CREATE TABLE food_ticket_group_members` | 1 | ⬜ |
| `DB-044.57` | 998–1024 | بلوک | `CREATE TABLE food_ticket_group_runs` | 1 | ⬜ |
| `DB-044.58` | 1025–1040 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-044.59` | 1041–1053 | بلوک | `CREATE TABLE food_ticket_day_visits` | 1 | ⬜ |
| `DB-044.60` | 1054–1064 | بلوک | `CREATE TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-044.61` | 1065–1080 | بلوک | `CREATE TABLE food_ticket_print_log` | 0 | ⬜ |
| `DB-044.62` | 1081–1112 | بلوک | `CREATE TABLE system_logs` | 0 | ⬜ |
| `DB-044.63` | 1113–1124 | بلوک | `CREATE TABLE chatbot_faq` | 0 | ⬜ |
| `DB-044.64` | 1125–1137 | بلوک | `CREATE TABLE chatbot_logs` | 0 | ⬜ |
| `DB-044.65` | 1138–1146 | بلوک | `CREATE TABLE system_incidents` | 1 | ⬜ |
| `DB-044.66` | 1147–1152 | بلوک | `CREATE TABLE ticket_number_seq` | 0 | ⬜ |
| `DB-044.67` | 1153–1161 | بلوک | `CREATE TABLE api_rate_limits` | 1 | ⬜ |
| `DB-044.68` | 1162–1174 | بلوک | `CREATE TABLE domain_scan_runs` | 1 | ⬜ |
| `DB-044.69` | 1175–1188 | بلوک | `CREATE TABLE domain_scan_queue` | 1 | ⬜ |
| `DB-044.70` | 1189–1189 | بلوک | `CREATE INDEX idx_tickets_status_priority` | 0 | ⬜ |
| `DB-044.71` | 1190–1190 | بلوک | `CREATE INDEX idx_users_is_active` | 0 | ⬜ |
| `DB-044.72` | 1191–1191 | بلوک | `CREATE INDEX idx_assets_lifecycle_status` | 0 | ⬜ |
| `DB-044.73` | 1192–1192 | بلوک | `CREATE INDEX idx_ola_priority_active` | 0 | ⬜ |
| `DB-044.74` | 1193–1193 | بلوک | `CREATE INDEX idx_food_ticket_events_event_type` | 0 | ⬜ |
| `DB-044.75` | 1194–1194 | بلوک | `CREATE INDEX idx_login_attempts_locked_until` | 0 | ⬜ |
| `DB-044.76` | 1195–1195 | بلوک | `CREATE INDEX idx_ticket_sla_alerts_alert_type` | 0 | ⬜ |
| `DB-044.77` | 1196–1196 | بلوک | `CREATE INDEX idx_ticket_ola_alerts_alert_type` | 0 | ⬜ |
| `DB-044.78` | 1197–1197 | بلوک | `CREATE INDEX idx_ticket_events_event_type` | 0 | ⬜ |
| `DB-044.79` | 1198–1198 | بلوک | `CREATE INDEX idx_knowledge_articles_is_published` | 0 | ⬜ |
| `DB-044.80` | 1199–1199 | بلوک | `CREATE INDEX idx_holidays_is_active` | 0 | ⬜ |
| `DB-044.81` | 1200–1200 | بلوک | `CREATE INDEX idx_departments_is_active` | 0 | ⬜ |
| `DB-044.82` | 1201–1201 | بلوک | `CREATE INDEX idx_handling_units_is_active` | 0 | ⬜ |
| `DB-044.83` | 1202–1202 | بلوک | `CREATE INDEX idx_categories_group_active` | 0 | ⬜ |
| `DB-044.84` | 1203–1203 | بلوک | `CREATE INDEX idx_service_catalog_group_active` | 0 | ⬜ |
| `DB-044.85` | 1204–1204 | بلوک | `CREATE INDEX idx_cd_dvd_records_media` | 0 | ⬜ |
| `DB-044.86` | 1205–1205 | بلوک | `CREATE INDEX idx_traffic_destinations_is_active` | 0 | ⬜ |
| `DB-044.87` | 1206–1206 | بلوک | `CREATE INDEX idx_domain_scan_runs_status` | 0 | ⬜ |
| `DB-044.88` | 1207–1209 | بلوک | `CREATE INDEX idx_domain_scan_queue_status` | 0 | ⬜ |
| `DB-044.89` | 1210–1219 | بلوک | `CREATE TABLE food_ticket_card_map` | 1 | ⬜ |
| `DB-044.90` | 1220–1231 | بلوک | `CREATE TABLE food_ticket_templates` | 1 | ⬜ |
| `DB-044.91` | 1232–1233 | بلوک | `=` | 0 | ⬜ |
| `DB-044.92` | 1234–1238 | بلوک | `=` | 0 | ⬜ |
| `DB-044.93` | 1239–1250 | بلوک | `CREATE TABLE food_catalog` | 1 | ⬜ |
| `DB-044.94` | 1251–1270 | بلوک | `CREATE TABLE food_guest_requests` | 1 | ⬜ |
| `DB-044.95` | 1271–1285 | بلوک | `CREATE TABLE food_calendar` | 1 | ⬜ |
| `DB-044.96` | 1286–1300 | بلوک | `CREATE TABLE food_calendar_items` | 1 | ⬜ |
| `DB-044.97` | 1301–1316 | بلوک | `CREATE TABLE food_orders` | 1 | ⬜ |
| `DB-044.98` | 1317–1334 | بلوک | `CREATE TABLE food_order_logs` | 1 | ⬜ |
| `DB-044.99` | 1335–1356 | بلوک | `CREATE TABLE food_order_cancel_requests` | 1 | ⬜ |
| `DB-044.100` | 1357–1388 | بلوک | `CREATE TABLE food_meal_reconciliations` | 1 | ⬜ |
| `DB-044.101` | 1389–1410 | بلوک | `CREATE TABLE food_monthly_invoices` | 0 | ⬜ |
| `DB-044.102` | 1411–1424 | بلوک | `CREATE TABLE user_access_groups` | 1 | ⬜ |
| `DB-044.103` | 1425–1436 | بلوک | `CREATE TABLE user_access_group_members` | 1 | ⬜ |
| `DB-044.104` | 1437–1452 | بلوک | `CREATE TABLE user_access_group_permissions` | 1 | ⬜ |
| `DB-044.105` | 1453–1478 | بلوک | `CREATE TABLE log_issues` | 1 | ⬜ |
| `DB-044.106` | 1479–1488 | بلوک | `CREATE TABLE logging_health_alerts` | 1 | ⬜ |
| `DB-044.107` | 1489–1504 | بلوک | `CREATE TABLE log_issue_history` | 0 | ⬜ |
| `DB-044.108` | 1505–1511 | بلوک | `CREATE TABLE log_issue_events` | 0 | ⬜ |
| `DB-044.109` | 1512–1530 | بلوک | `CREATE TABLE log_issue_notifications` | 1 | ⬜ |
| `DB-044.110` | 1531–1541 | بلوک | `CREATE TABLE schema_updates` | 0 | ⬜ |
| `DB-044.111` | 1542–1605 | بلوک | `CREATE TABLE request_center_appointments` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-045</b> — <code>upgrade-1.1-departments.sql</code> (16 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-045.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-045.02` | 2–9 | بلوک | `CREATE TABLE departments` | 0 | ⬜ |
| `DB-045.03` | 10–10 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-045.04` | 11–11 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-045.05` | 12–12 | بلوک | `ALTER TABLE users` | 1 | ⬜ |
| `DB-045.06` | 13–13 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-045.07` | 14–14 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-045.08` | 15–16 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-046</b> — <code>upgrade-1.10-priority.sql</code> (11 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-046.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-046.02` | 2–2 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |
| `DB-046.03` | 3–3 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-046.04` | 4–4 | بلوک | `UPDATE ola_policies` | 1 | ⬜ |
| `DB-046.05` | 5–6 | بلوک | `UPDATE problem_records` | 1 | ⬜ |
| `DB-046.06` | 7–7 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-046.07` | 8–8 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-046.08` | 9–9 | بلوک | `ALTER TABLE ola_policies` | 0 | ⬜ |
| `DB-046.09` | 10–11 | بلوک | `ALTER TABLE problem_records` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-047</b> — <code>upgrade-1.11-user-profile.sql</code> (5 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-047.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-047.02` | 2–2 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-047.03` | 3–3 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-047.04` | 4–5 | بلوک | `ALTER TABLE users` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-048</b> — <code>upgrade-1.12-food-ticket-management.sql</code> (16 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-048.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-048.02` | 3–16 | بلوک | `CREATE TABLE food_ticket_guest_cards` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-049</b> — <code>upgrade-1.13-asset-profile.sql</code> (93 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-049.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-049.02` | 4–10 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-049.03` | 11–93 | بلوک | `CREATE TABLE asset_profiles` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-050</b> — <code>upgrade-1.13-food-groups.sql</code> (50 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-050.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-050.02` | 4–16 | بلوک | `CREATE TABLE food_ticket_groups` | 1 | ⬜ |
| `DB-050.03` | 17–28 | بلوک | `CREATE TABLE food_ticket_group_members` | 1 | ⬜ |
| `DB-050.04` | 29–50 | بلوک | `CREATE TABLE food_ticket_group_runs` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-051</b> — <code>upgrade-1.14-system-logs.sql</code> (14 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-051.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-051.02` | 2–14 | بلوک | `CREATE TABLE system_logs` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-052</b> — <code>upgrade-1.15-plaque-fields.sql</code> (13 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-052.01` | 1–6 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-052.02` | 7–13 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-053</b> — <code>upgrade-1.16-ticket-number.sql</code> (12 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-053.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-053.02` | 6–7 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-053.03` | 8–12 | بلوک | `CREATE TABLE ticket_number_seq` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-054</b> — <code>upgrade-1.17-service-catalog.sql</code> (45 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-054.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-054.02` | 5–45 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-055</b> — <code>upgrade-1.18-service-categories.sql</code> (61 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-055.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-055.02` | 4–4 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-055.03` | 5–5 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-055.04` | 6–7 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-055.05` | 8–27 | بلوک | `INSERT INTO categories` | 1 | ⬜ |
| `DB-055.06` | 28–61 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-056</b> — <code>upgrade-1.19-food-ticket-queue.sql</code> (60 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-056.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-056.02` | 2–44 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-056.03` | 45–60 | بلوک | `CREATE TABLE food_ticket_print_log` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-057</b> — <code>upgrade-1.2-itsm.sql</code> (63 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-057.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-057.02` | 2–2 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-057.03` | 3–3 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-057.04` | 4–4 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-057.05` | 5–5 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-057.06` | 6–6 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-057.07` | 7–7 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-057.08` | 8–8 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-057.09` | 9–9 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-057.10` | 10–10 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-057.11` | 11–12 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-057.12` | 13–27 | بلوک | `CREATE TABLE ticket_events` | 1 | ⬜ |
| `DB-057.13` | 28–52 | بلوک | `CREATE TABLE assets` | 1 | ⬜ |
| `DB-057.14` | 53–63 | بلوک | `CREATE TABLE asset_inventory_history` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-058</b> — <code>upgrade-1.20-domain-scan.sql</code> (37 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-058.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-058.02` | 2–14 | بلوک | `CREATE TABLE domain_scan_runs` | 1 | ⬜ |
| `DB-058.03` | 15–27 | بلوک | `CREATE TABLE domain_scan_queue` | 1 | ⬜ |
| `DB-058.04` | 28–37 | بلوک | `ALTER TABLE asset_profiles` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-059</b> — <code>upgrade-1.21-organization.sql</code> (49 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-059.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-059.02` | 2–16 | بلوک | `CREATE TABLE org_units` | 0 | ⬜ |
| `DB-059.03` | 17–24 | بلوک | `CREATE TABLE org_unit_managers` | 0 | ⬜ |
| `DB-059.04` | 25–29 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-059.05` | 30–34 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-059.06` | 35–42 | بلوک | `INSERT INTO org_units` | 1 | ⬜ |
| `DB-059.07` | 43–49 | بلوک | `UPDATE users` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-060</b> — <code>upgrade-1.22-permissions.sql</code> (16 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-060.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-060.02` | 4–7 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-060.03` | 8–9 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-060.04` | 10–16 | بلوک | `CREATE TABLE role_permissions` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-061</b> — <code>upgrade-1.23-activity-log.sql</code> (55 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-061.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-061.02` | 5–32 | بلوک | `CREATE TABLE activity_logs` | 1 | ⬜ |
| `DB-061.03` | 33–44 | بلوک | `INSERT INTO activity_logs` | 1 | ⬜ |
| `DB-061.04` | 45–47 | بلوک | `DROP TABLE IF EXISTS audit_logs` | 0 | ⬜ |
| `DB-061.05` | 48–51 | بلوک | `INSERT INTO settings` | 1 | ⬜ |
| `DB-061.06` | 52–55 | بلوک | `INSERT INTO settings` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-062</b> — <code>upgrade-1.24-traffic-control.sql</code> (55 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-062.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-062.02` | 10–10 | بلوک | `DROP TABLE IF EXISTS traffic_visits` | 0 | ⬜ |
| `DB-062.03` | 11–12 | بلوک | `DROP TABLE IF EXISTS traffic_destinations` | 0 | ⬜ |
| `DB-062.04` | 13–20 | بلوک | `CREATE TABLE traffic_destinations` | 0 | ⬜ |
| `DB-062.05` | 21–46 | بلوک | `CREATE TABLE traffic_visits` | 1 | ⬜ |
| `DB-062.06` | 47–55 | بلوک | `INSERT INTO traffic_destinations` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-063</b> — <code>upgrade-1.25-performance-indexes.sql</code> (36 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-063.01` | 1–7 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-063.02` | 8–36 | بلوک | `CREATE PROCEDURE itsm_add_index` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-064</b> — <code>upgrade-1.26-ticket-requesting-unit.sql</code> (28 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-064.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-064.02` | 9–14 | بلوک | `CREATE PROCEDURE itsm_ensure_req_unit` | 0 | ⬜ |
| `DB-064.03` | 15–20 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-064.04` | 21–28 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-065</b> — <code>upgrade-1.27-food-ticket-fixes.sql</code> (17 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-065.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-065.02` | 5–11 | بلوک | `UPDATE food_ticket_config` | 1 | ⬜ |
| `DB-065.03` | 12–17 | بلوک | `UPDATE food_ticket_config` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-066</b> — <code>upgrade-1.3-supervisor-inventory.sql</code> (14 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-066.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-066.02` | 2–2 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-066.03` | 3–3 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-066.04` | 4–4 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-066.05` | 5–5 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-066.06` | 6–6 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-066.07` | 7–7 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-066.08` | 8–8 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-066.09` | 9–9 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-066.10` | 10–10 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-066.11` | 11–11 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-066.12` | 12–12 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-066.13` | 13–14 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-067</b> — <code>upgrade-1.30-food-ticket-card-map.sql</code> (11 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-067.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-067.02` | 2–11 | بلوک | `CREATE TABLE food_ticket_card_map` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-068</b> — <code>upgrade-1.31-food-ticket-templates.sql</code> (14 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-068.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-068.02` | 2–14 | بلوک | `CREATE TABLE food_ticket_templates` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-069</b> — <code>upgrade-1.32-food-group-representative-absence.sql</code> (90 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-069.01` | 1–2 | بلوک | `=` | 0 | ⬜ |
| `DB-069.02` | 3–8 | بلوک | `=` | 0 | ⬜ |
| `DB-069.03` | 9–13 | بلوک | `=` | 0 | ⬜ |
| `DB-069.04` | 14–21 | بلوک | `ALTER TABLE food_ticket_groups` | 0 | ⬜ |
| `DB-069.05` | 22–28 | بلوک | `ALTER TABLE food_ticket_group_runs` | 0 | ⬜ |
| `DB-069.06` | 29–46 | بلوک | `CREATE TABLE food_ticket_daily_absence` | 1 | ⬜ |
| `DB-069.07` | 47–60 | بلوک | `CREATE TABLE food_ticket_group_uid_history` | 0 | ⬜ |
| `DB-069.08` | 61–66 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-069.09` | 67–71 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-069.10` | 72–73 | بلوک | `=` | 0 | ⬜ |
| `DB-069.11` | 74–88 | بلوک | `=` | 1 | ⬜ |
| `DB-069.12` | 89–90 | بلوک | `=` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-070</b> — <code>upgrade-1.33-source-delete-truth.sql</code> (46 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-070.01` | 1–16 | بلوک | `=` | 1 | ⬜ |
| `DB-070.02` | 17–18 | بلوک | `=` | 0 | ⬜ |
| `DB-070.03` | 19–23 | بلوک | `ALTER TABLE food_ticket_events` | 0 | ⬜ |
| `DB-070.04` | 24–25 | بلوک | `CREATE INDEX idx_food_events_src_deleted` | 0 | ⬜ |
| `DB-070.05` | 26–27 | بلوک | `=` | 0 | ⬜ |
| `DB-070.06` | 28–38 | بلوک | `=` | 0 | ⬜ |
| `DB-070.07` | 39–40 | بلوک | `=` | 0 | ⬜ |
| `DB-070.08` | 41–44 | بلوک | `=` | 0 | ⬜ |
| `DB-070.09` | 45–46 | بلوک | `=` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-071</b> — <code>upgrade-1.38-food-orders.sql</code> (78 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-071.01` | 1–4 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-071.02` | 5–15 | بلوک | `CREATE TABLE food_catalog` | 1 | ⬜ |
| `DB-071.03` | 16–30 | بلوک | `CREATE TABLE food_calendar` | 1 | ⬜ |
| `DB-071.04` | 31–45 | بلوک | `CREATE TABLE food_calendar_items` | 1 | ⬜ |
| `DB-071.05` | 46–61 | بلوک | `CREATE TABLE food_orders` | 1 | ⬜ |
| `DB-071.06` | 62–78 | بلوک | `CREATE TABLE food_order_logs` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-072</b> — <code>upgrade-1.39-food-guest-requests.sql</code> (19 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-072.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-072.02` | 3–19 | بلوک | `CREATE TABLE food_guest_requests` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-073</b> — <code>upgrade-1.4-final.sql</code> (113 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-073.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-073.02` | 2–2 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-073.03` | 3–3 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-073.04` | 4–4 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-073.05` | 5–5 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-073.06` | 6–6 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-073.07` | 7–7 | بلوک | `ALTER TABLE departments` | 0 | ⬜ |
| `DB-073.08` | 8–8 | بلوک | `ALTER TABLE departments` | 1 | ⬜ |
| `DB-073.09` | 9–9 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-073.10` | 10–10 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-073.11` | 11–11 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-073.12` | 12–12 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.13` | 13–13 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.14` | 14–14 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.15` | 15–15 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.16` | 16–16 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.17` | 17–17 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.18` | 18–18 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.19` | 19–19 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.20` | 20–20 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.21` | 21–21 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.22` | 22–22 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.23` | 23–23 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.24` | 24–24 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-073.25` | 25–37 | بلوک | `CREATE TABLE ticket_ratings` | 1 | ⬜ |
| `DB-073.26` | 38–51 | بلوک | `CREATE TABLE asset_memory_modules` | 1 | ⬜ |
| `DB-073.27` | 52–66 | بلوک | `CREATE TABLE asset_storage_devices` | 1 | ⬜ |
| `DB-073.28` | 67–78 | بلوک | `CREATE TABLE asset_graphics_adapters` | 1 | ⬜ |
| `DB-073.29` | 79–95 | بلوک | `CREATE TABLE asset_peripherals` | 1 | ⬜ |
| `DB-073.30` | 96–113 | بلوک | `CREATE TABLE asset_history_events` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-074</b> — <code>upgrade-1.40-food-guest-deputy.sql</code> (6 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-074.01` | 1–2 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-074.02` | 3–6 | بلوک | `ALTER TABLE food_guest_requests` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-075</b> — <code>upgrade-1.5-offline-operations.sql</code> (112 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-075.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-075.02` | 2–17 | بلوک | `CREATE TABLE service_catalog` | 1 | ⬜ |
| `DB-075.03` | 18–30 | بلوک | `CREATE TABLE service_catalog_fields` | 1 | ⬜ |
| `DB-075.04` | 31–31 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-075.05` | 32–32 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-075.06` | 33–33 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-075.07` | 34–34 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-075.08` | 35–35 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-075.09` | 36–36 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-075.10` | 37–37 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-075.11` | 38–38 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-075.12` | 39–39 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-075.13` | 40–40 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-075.14` | 41–42 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-075.15` | 43–57 | بلوک | `CREATE TABLE notifications` | 1 | ⬜ |
| `DB-075.16` | 58–65 | بلوک | `CREATE TABLE login_attempts` | 1 | ⬜ |
| `DB-075.17` | 66–75 | بلوک | `CREATE TABLE ticket_sla_alerts` | 1 | ⬜ |
| `DB-075.18` | 76–89 | بلوک | `CREATE TABLE knowledge_articles` | 1 | ⬜ |
| `DB-075.19` | 90–99 | بلوک | `CREATE TABLE holidays` | 1 | ⬜ |
| `DB-075.20` | 100–112 | بلوک | `CREATE TABLE asset_relations` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-076</b> — <code>upgrade-1.6-hardening.sql</code> (19 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-076.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-076.02` | 2–9 | بلوک | `CREATE TABLE login_attempts` | 1 | ⬜ |
| `DB-076.03` | 10–19 | بلوک | `CREATE TABLE ticket_sla_alerts` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-077</b> — <code>upgrade-1.7-governance.sql</code> (88 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-077.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-077.02` | 2–30 | بلوک | `CREATE TABLE change_records` | 1 | ⬜ |
| `DB-077.03` | 31–42 | بلوک | `CREATE TABLE change_approvals` | 1 | ⬜ |
| `DB-077.04` | 43–53 | بلوک | `CREATE TABLE change_ticket_links` | 1 | ⬜ |
| `DB-077.05` | 54–77 | بلوک | `CREATE TABLE problem_records` | 1 | ⬜ |
| `DB-077.06` | 78–88 | بلوک | `CREATE TABLE problem_ticket_links` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-078</b> — <code>upgrade-1.8-cd-dvd-identity.sql</code> (6 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-078.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-078.02` | 2–6 | بلوک | `ALTER TABLE cd_dvd_records` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-079</b> — <code>upgrade-1.8-cd-dvd.sql</code> (44 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-079.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-079.02` | 2–9 | بلوک | `CREATE TABLE cd_dvd_types` | 1 | ⬜ |
| `DB-079.03` | 10–44 | بلوک | `CREATE TABLE cd_dvd_records` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-080</b> — <code>upgrade-1.8-final.sql</code> (47 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-080.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-080.02` | 2–8 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-080.03` | 9–17 | بلوک | `ALTER TABLE assets` | 0 | ⬜ |
| `DB-080.04` | 18–35 | بلوک | `CREATE TABLE ola_policies` | 1 | ⬜ |
| `DB-080.05` | 36–45 | بلوک | `CREATE TABLE ticket_ola_alerts` | 1 | ⬜ |
| `DB-080.06` | 46–47 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-081</b> — <code>upgrade-1.8-routing.sql</code> (46 خط، 25 آدرس، بازبینی‌شده 0/25)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-081.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-081.02` | 2–12 | بلوک | `CREATE TABLE handling_units` | 1 | ⬜ |
| `DB-081.03` | 13–13 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-081.04` | 14–15 | بلوک | `ALTER TABLE users` | 1 | ⬜ |
| `DB-081.05` | 16–16 | بلوک | `ALTER TABLE categories` | 0 | ⬜ |
| `DB-081.06` | 17–17 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-081.07` | 18–18 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-081.08` | 19–19 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-081.09` | 20–20 | بلوک | `ALTER TABLE service_catalog` | 0 | ⬜ |
| `DB-081.10` | 21–21 | بلوک | `ALTER TABLE service_catalog` | 1 | ⬜ |
| `DB-081.11` | 22–22 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-081.12` | 23–23 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-081.13` | 24–24 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-081.14` | 25–25 | بلوک | `ALTER TABLE tickets` | 0 | ⬜ |
| `DB-081.15` | 26–27 | بلوک | `ALTER TABLE tickets` | 1 | ⬜ |
| `DB-081.16` | 28–29 | بلوک | `INSERT INTO handling_units` | 1 | ⬜ |
| `DB-081.17` | 30–30 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-081.18` | 31–31 | بلوک | `UPDATE service_catalog` | 1 | ⬜ |
| `DB-081.19` | 32–33 | بلوک | `UPDATE tickets` | 1 | ⬜ |
| `DB-081.20` | 34–35 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |
| `DB-081.21` | 36–37 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |
| `DB-081.22` | 38–39 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |
| `DB-081.23` | 40–41 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |
| `DB-081.24` | 42–43 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |
| `DB-081.25` | 44–46 | بلوک | `INSERT INTO service_catalog` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-082</b> — <code>upgrade-1.9-food-ticket.sql</code> (80 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-082.01` | 1–14 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-082.02` | 15–37 | بلوک | `CREATE TABLE food_ticket_config` | 1 | ⬜ |
| `DB-082.03` | 38–64 | بلوک | `CREATE TABLE food_ticket_events` | 1 | ⬜ |
| `DB-082.04` | 65–80 | بلوک | `CREATE TABLE food_ticket_worker_status` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-083</b> — <code>upgrade-food-drop-legacy-orders-columns.sql</code> (6 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-083.01` | 1–3 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-083.02` | 4–4 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |
| `DB-083.03` | 5–6 | بلوک | `ALTER TABLE food_ticket_config` | 0 | ⬜ |

</details>

<details>
<summary><b>DB-084</b> — <code>upgrade-food-employee-auth-source.sql</code> (14 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-084.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-084.02` | 6–8 | بلوک | `ALTER TABLE users` | 0 | ⬜ |
| `DB-084.03` | 9–14 | بلوک | `UPDATE users` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-085</b> — <code>upgrade-remove-legacy-admin-role.sql</code> (11 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-085.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-085.02` | 6–8 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-085.03` | 9–11 | بلوک | `UPDATE users` | 1 | ⬜ |

</details>

<details>
<summary><b>DB-086</b> — <code>upgrade-unique-employee-number.sql</code> (16 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `DB-086.01` | 1–10 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `DB-086.02` | 11–11 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-086.03` | 12–14 | بلوک | `UPDATE users` | 1 | ⬜ |
| `DB-086.04` | 15–16 | بلوک | `ALTER TABLE users` | 0 | ⬜ |

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
<summary><b>PAGE-002</b> — <code>asset-inventory-page.php</code> (89 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-002.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-002.02` | 4–88 | تابع | `inventory_render_assets_content` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-003</b> — <code>asset-names.php</code> (538 خط، 25 آدرس، بازبینی‌شده 0/25)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-003.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `PAGE-003.02` | 29–45 | تابع | `asset_hostname_style` | 0 | ⬜ |
| `PAGE-003.03` | 46–67 | ناحیه | `بلوک رویه‌ای 46-67` | 0 | ⬜ |
| `PAGE-003.04` | 68–72 | تابع | `asset_is_mac` | 0 | ⬜ |
| `PAGE-003.05` | 74–88 | تابع | `asset_strip_mac` | 0 | ⬜ |
| `PAGE-003.06` | 90–94 | تابع | `asset_is_ip` | 0 | ⬜ |
| `PAGE-003.07` | 96–100 | تابع | `asset_is_domain_name` | 0 | ⬜ |
| `PAGE-003.08` | 102–124 | تابع | `asset_strip_ip` | 0 | ⬜ |
| `PAGE-003.09` | 126–205 | تابع | `asset_name_core` | 0 | ⬜ |
| `PAGE-003.10` | 207–261 | تابع | `asset_display_name` | 0 | ⬜ |
| `PAGE-003.11` | 263–274 | تابع | `asset_is_auto_domain_tag` | 0 | ⬜ |
| `PAGE-003.12` | 276–290 | تابع | `asset_name_or` | 0 | ⬜ |
| `PAGE-003.13` | 292–312 | تابع | `asset_name_title` | 0 | ⬜ |
| `PAGE-003.14` | 314–319 | تابع | `asset_name_attr` | 0 | ⬜ |
| `PAGE-003.15` | 321–335 | تابع | `asset_tag_label` | 0 | ⬜ |
| `PAGE-003.16` | 336–338 | ناحیه | `بلوک رویه‌ای 336-338` | 0 | ⬜ |
| `PAGE-003.17` | 339–375 | تابع | `asset_identity_key` | 0 | ⬜ |
| `PAGE-003.18` | 377–409 | تابع | `asset_resolve_id` | 0 | ⬜ |
| `PAGE-003.19` | 410–411 | ناحیه | `بلوک رویه‌ای 410-411` | 0 | ⬜ |
| `PAGE-003.20` | 412–440 | تابع | `asset_identity_serial_value` | 0 | ⬜ |
| `PAGE-003.21` | 442–457 | تابع | `asset_identity_mac_value` | 0 | ⬜ |
| `PAGE-003.22` | 459–474 | تابع | `asset_stable_identity` | 0 | ⬜ |
| `PAGE-003.23` | 476–516 | تابع | `asset_resolve_by_machine` | 0 | ⬜ |
| `PAGE-003.24` | 518–536 | تابع | `asset_sync_identity_key` | 1 | ⬜ |
| `PAGE-003.25` | 537–538 | ناحیه | `بلوک رویه‌ای 537-538` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-004</b> — <code>asset-profile.php</code> (1,106 خط، 23 آدرس، بازبینی‌شده 0/23)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-004.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-004.02` | 8–26 | تابع | `asset_profile_column_sql` | 0 | ⬜ |
| `PAGE-004.03` | 28–65 | تابع | `asset_profile_ensure_schema` | **9** | ⬜ |
| `PAGE-004.04` | 67–219 | تابع | `asset_profile_sections` | 1 | ⬜ |
| `PAGE-004.05` | 221–234 | تابع | `asset_profile_all_columns` | 0 | ⬜ |
| `PAGE-004.06` | 236–246 | تابع | `asset_profile_field` | 0 | ⬜ |
| `PAGE-004.07` | 248–266 | تابع | `asset_profile_export_columns` | 0 | ⬜ |
| `PAGE-004.08` | 268–281 | تابع | `asset_profile_export_value` | 0 | ⬜ |
| `PAGE-004.09` | 283–291 | تابع | `asset_profile_fetch` | 0 | ⬜ |
| `PAGE-004.10` | 293–360 | تابع | `asset_profile_fill_from_asset` | 0 | ⬜ |
| `PAGE-004.11` | 362–407 | تابع | `asset_profile_collection_status_summary` | 0 | ⬜ |
| `PAGE-004.12` | 409–435 | تابع | `asset_profile_map` | 5 | ⬜ |
| `PAGE-004.13` | 437–446 | تابع | `asset_profile_input_value` | 0 | ⬜ |
| `PAGE-004.14` | 448–497 | تابع | `asset_profile_render_fields` | 5 | ⬜ |
| `PAGE-004.15` | 499–507 | تابع | `asset_profile_render_sections` | 0 | ⬜ |
| `PAGE-004.16` | 509–561 | تابع | `asset_profile_save` | 6 | ⬜ |
| `PAGE-004.17` | 563–574 | تابع | `asset_profile_printer_port_is_physical` | 0 | ⬜ |
| `PAGE-004.18` | 576–607 | تابع | `asset_profile_is_virtual_printer` | 0 | ⬜ |
| `PAGE-004.19` | 609–619 | تابع | `asset_profile_printer_kind` | 0 | ⬜ |
| `PAGE-004.20` | 621–639 | تابع | `asset_profile_real_printers` | 0 | ⬜ |
| `PAGE-004.21` | 641–659 | تابع | `asset_profile_computer_type` | 0 | ⬜ |
| `PAGE-004.22` | 661–1031 | تابع | `asset_profile_auto_values` | 5 | ⬜ |
| `PAGE-004.23` | 1033–1105 | تابع | `asset_profile_apply_auto` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-005</b> — <code>backup.php</code> (442 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-005.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-005.02` | 6–10 | تابع | `backup_root_path` | 0 | ⬜ |
| `PAGE-005.03` | 12–30 | تابع | `backup_mysql_binary` | 0 | ⬜ |
| `PAGE-005.04` | 32–76 | تابع | `backup_run_process` | 4 | ⬜ |
| `PAGE-005.05` | 78–126 | تابع | `backup_uploads` | 0 | ⬜ |
| `PAGE-005.06` | 128–183 | تابع | `backup_system_files_zip` | 0 | ⬜ |
| `PAGE-005.07` | 185–222 | تابع | `create_backup_bundle` | 0 | ⬜ |
| `PAGE-005.08` | 224–240 | تابع | `backup_runs` | 0 | ⬜ |
| `PAGE-005.09` | 242–255 | تابع | `backup_directory_size` | 0 | ⬜ |
| `PAGE-005.10` | 257–264 | تابع | `backup_download_path` | 0 | ⬜ |
| `PAGE-005.11` | 266–290 | تابع | `backup_upload_archive_entry` | 0 | ⬜ |
| `PAGE-005.12` | 292–351 | تابع | `backup_validate_upload_archive` | 0 | ⬜ |
| `PAGE-005.13` | 353–420 | تابع | `restore_upload_archive` | 1 | ⬜ |
| `PAGE-005.14` | 422–441 | تابع | `restore_sql_dump` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-006</b> — <code>bootstrap.php</code> (93 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-006.01` | 1–38 | ناحیه | `بلوک رویه‌ای 1-38` | 0 | ⬜ |
| `PAGE-006.02` | 39–41 | ناحیه | `هسته، پیکربندی، HTTP امن و پایگاه داده (شرح در سرآ` | 0 | ⬜ |
| `PAGE-006.03` | 42–45 | ناحیه | `هستهٔ مرکزی ثبت رخداد و هم‌بستگی درخواست` | 0 | ⬜ |
| `PAGE-006.04` | 46–47 | ناحیه | `پیام‌های فلش، لاگ سیستمی و CSRF (شرح در سرآیند هما` | 0 | ⬜ |
| `PAGE-006.05` | 48–48 | ناحیه | `ماژول تشدید شدت (افزوده‌شده در 1.62.89) — طبق قواع` | 0 | ⬜ |
| `PAGE-006.06` | 49–49 | ناحیه | `اگر فایل موجود نباشد سامانه بدون آن اجرا می‌شود؛ s` | 0 | ⬜ |
| `PAGE-006.07` | 50–56 | ناحیه | `این ماژول را با function_exists گارد می‌کند و هیچ ` | 0 | ⬜ |
| `PAGE-006.08` | 57–57 | ناحیه | `سخت‌سازی نشست` | 0 | ⬜ |
| `PAGE-006.09` | 58–58 | ناحیه | `اینجا اجرا می‌شود چون cfg() در lib/db-core.php تعر` | 0 | ⬜ |
| `PAGE-006.10` | 59–59 | ناحیه | `lib/auth-scope.php باشد تا نشستِ منقضی اصلاً به لا` | 0 | ⬜ |
| `PAGE-006.11` | 60–63 | ناحیه | `security.session_idle_days: روزهای بی‌فعالیت تا خر` | 0 | ⬜ |
| `PAGE-006.12` | 64–66 | ناحیه | `نشست کاربر، سطوح دسترسی و دامنهٔ دید (شرح در سرآین` | 0 | ⬜ |
| `PAGE-006.13` | 67–69 | ناحیه | `تنظیمات، افکت‌های نمایشی و نقش‌های کلیدی (شرح در س` | 0 | ⬜ |
| `PAGE-006.14` | 70–72 | ناحیه | `پیکربندی و همگام‌سازی دامین (LDAP) (شرح در سرآیند ` | 0 | ⬜ |
| `PAGE-006.15` | 73–75 | ناحیه | `شمارهٔ درخواست، تقویم شمسی و SLA/OLA (شرح در سرآین` | 0 | ⬜ |
| `PAGE-006.16` | 76–78 | ناحیه | `اعلان‌ها، ورود دامین و کاربران (شرح در سرآیند همان` | 0 | ⬜ |
| `PAGE-006.17` | 79–81 | ناحیه | `ثبت فعالیت، حسابرسی و رویداد درخواست‌ها (شرح در سر` | 0 | ⬜ |
| `PAGE-006.18` | 82–85 | ناحیه | `Shared request input normalization and allowlisted` | 0 | ⬜ |
| `PAGE-006.19` | 86–86 | ناحیه | `لایهٔ نمایشی سراسری: فارسی‌سازی اعداد در خروجی HTM` | 0 | ⬜ |
| `PAGE-006.20` | 87–87 | ناحیه | `متنِ داخل صفحه‌ها هنگام ارسال تبدیل می‌شود؛ اتربیو` | 0 | ⬜ |
| `PAGE-006.21` | 88–93 | ناحیه | `پاسخ‌های غیر HTML دست‌نخورده می‌مانند. می‌توان با ` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-007</b> — <code>cd-dvd.php</code> (898 خط، 44 آدرس، بازبینی‌شده 0/44)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-007.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-007.02` | 6–9 | تابع | `cd_dvd_media_bounds` | 0 | ⬜ |
| `PAGE-007.03` | 11–15 | تابع | `cd_dvd_normalize_serial` | 0 | ⬜ |
| `PAGE-007.04` | 17–20 | تابع | `cd_dvd_internal_number` | 0 | ⬜ |
| `PAGE-007.05` | 22–25 | تابع | `cd_dvd_serial_valid` | 0 | ⬜ |
| `PAGE-007.06` | 27–35 | تابع | `cd_dvd_serial_matches_media` | 0 | ⬜ |
| `PAGE-007.07` | 37–40 | تابع | `cd_dvd_is_primary_admin` | 0 | ⬜ |
| `PAGE-007.08` | 42–52 | تابع | `cd_dvd_inspection_user_ids` | 0 | ⬜ |
| `PAGE-007.09` | 54–58 | تابع | `cd_dvd_inspection_user_id` | 0 | ⬜ |
| `PAGE-007.10` | 60–63 | تابع | `cd_dvd_is_inspection_user` | 0 | ⬜ |
| `PAGE-007.11` | 65–71 | تابع | `cd_dvd_can_see_all` | 0 | ⬜ |
| `PAGE-007.12` | 73–85 | تابع | `cd_dvd_can_edit_records` | 0 | ⬜ |
| `PAGE-007.13` | 87–93 | تابع | `cd_dvd_can_export` | 0 | ⬜ |
| `PAGE-007.14` | 95–106 | تابع | `cd_dvd_can_view_record` | 0 | ⬜ |
| `PAGE-007.15` | 108–118 | تابع | `cd_dvd_audit_snapshot` | 0 | ⬜ |
| `PAGE-007.16` | 120–126 | تابع | `cd_dvd_can_register_in` | 0 | ⬜ |
| `PAGE-007.17` | 128–134 | تابع | `cd_dvd_can_submit_out` | 0 | ⬜ |
| `PAGE-007.18` | 136–151 | تابع | `cd_dvd_available` | 0 | ⬜ |
| `PAGE-007.19` | 153–162 | تابع | `cd_dvd_scope` | 5 | ⬜ |
| `PAGE-007.20` | 164–169 | تابع | `cd_dvd_can_manage_record` | 0 | ⬜ |
| `PAGE-007.21` | 171–174 | تابع | `cd_dvd_types` | 0 | ⬜ |
| `PAGE-007.22` | 176–179 | تابع | `cd_dvd_active_users` | 0 | ⬜ |
| `PAGE-007.23` | 181–191 | تابع | `cd_dvd_deputy_options` | 0 | ⬜ |
| `PAGE-007.24` | 193–202 | تابع | `cd_dvd_routing_department_id` | 0 | ⬜ |
| `PAGE-007.25` | 204–222 | تابع | `cd_dvd_next_serial` | 0 | ⬜ |
| `PAGE-007.26` | 224–236 | تابع | `cd_dvd_last_record` | 0 | ⬜ |
| `PAGE-007.27` | 238–243 | تابع | `cd_dvd_fetch_record` | 0 | ⬜ |
| `PAGE-007.28` | 245–259 | تابع | `cd_dvd_parse_date` | 0 | ⬜ |
| `PAGE-007.29` | 261–265 | تابع | `cd_dvd_default_date` | 0 | ⬜ |
| `PAGE-007.30` | 267–383 | تابع | `cd_dvd_validate_payload` | 0 | ⬜ |
| `PAGE-007.31` | 385–394 | تابع | `cd_dvd_acquire_write_lock` | 0 | ⬜ |
| `PAGE-007.32` | 396–404 | تابع | `cd_dvd_release_write_lock` | 4 | ⬜ |
| `PAGE-007.33` | 406–454 | تابع | `cd_dvd_create_record` | 7 | ⬜ |
| `PAGE-007.34` | 456–477 | تابع | `cd_dvd_update_record` | 2 | ⬜ |
| `PAGE-007.35` | 479–497 | تابع | `cd_dvd_delete_record` | 2 | ⬜ |
| `PAGE-007.36` | 499–534 | تابع | `cd_dvd_query_records` | 0 | ⬜ |
| `PAGE-007.37` | 536–541 | تابع | `cd_dvd_personal_incoming` | 0 | ⬜ |
| `PAGE-007.38` | 543–553 | تابع | `cd_dvd_latest_records` | 0 | ⬜ |
| `PAGE-007.39` | 555–574 | تابع | `cd_dvd_stats` | 5 | ⬜ |
| `PAGE-007.40` | 576–589 | تابع | `cd_dvd_export` | 0 | ⬜ |
| `PAGE-007.41` | 591–619 | تابع | `cd_dvd_render_surface` | 2 | ⬜ |
| `PAGE-007.42` | 621–736 | تابع | `cd_dvd_render_page_legacy` | 2 | ⬜ |
| `PAGE-007.43` | 738–875 | تابع | `cd_dvd_render_page` | 3 | ⬜ |
| `PAGE-007.44` | 877–897 | تابع | `cd_dvd_render_history` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-008</b> — <code>chatbot-tts-lexicon.php</code> (15 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-008.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-009</b> — <code>chatbot.php</code> (3,404 خط، 123 آدرس، بازبینی‌شده 0/123)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-009.01` | 1–14 | ناحیه | `بلوک رویه‌ای 1-14` | 0 | ⬜ |
| `PAGE-009.02` | 15–27 | تابع | `chatbot_normalize` | 0 | ⬜ |
| `PAGE-009.03` | 29–33 | تابع | `chatbot_compact` | 0 | ⬜ |
| `PAGE-009.04` | 35–46 | تابع | `chatbot_keywords` | 0 | ⬜ |
| `PAGE-009.05` | 48–87 | تابع | `chatbot_score` | 0 | ⬜ |
| `PAGE-009.06` | 88–100 | ناحیه | `بلوک رویه‌ای 88-100` | 0 | ⬜ |
| `PAGE-009.07` | 101–111 | تابع | `chatbot_has_any` | 0 | ⬜ |
| `PAGE-009.08` | 113–125 | تابع | `chatbot_reply` | 1 | ⬜ |
| `PAGE-009.09` | 127–138 | تابع | `chatbot_flow_active` | 0 | ⬜ |
| `PAGE-009.10` | 140–165 | تابع | `chatbot_flow_step` | 0 | ⬜ |
| `PAGE-009.11` | 167–179 | تابع | `chatbot_ticket_ref` | 0 | ⬜ |
| `PAGE-009.12` | 181–193 | تابع | `chatbot_ticket_status` | 5 | ⬜ |
| `PAGE-009.13` | 195–199 | تابع | `chatbot_ticket_line` | 0 | ⬜ |
| `PAGE-009.14` | 201–401 | تابع | `chatbot_answer` | 1 | ⬜ |
| `PAGE-009.15` | 402–442 | ناحیه | `بلوک رویه‌ای 402-442` | 0 | ⬜ |
| `PAGE-009.16` | 443–465 | تابع | `chatbot_expand_words` | 0 | ⬜ |
| `PAGE-009.17` | 467–481 | تابع | `chatbot_edit_distance` | 0 | ⬜ |
| `PAGE-009.18` | 483–496 | تابع | `chatbot_typo_match` | 0 | ⬜ |
| `PAGE-009.19` | 497–500 | ناحیه | `بلوک رویه‌ای 497-500` | 0 | ⬜ |
| `PAGE-009.20` | 501–508 | تابع | `chatbot_contains_word` | 0 | ⬜ |
| `PAGE-009.21` | 510–524 | تابع | `chatbot_guide_tokens` | 0 | ⬜ |
| `PAGE-009.22` | 526–609 | تابع | `chatbot_guide_index` | 1 | ⬜ |
| `PAGE-009.23` | 611–643 | تابع | `chatbot_guide_search` | 0 | ⬜ |
| `PAGE-009.24` | 645–670 | تابع | `chatbot_guide_suggestions` | 0 | ⬜ |
| `PAGE-009.25` | 672–677 | تابع | `chatbot_guide_reply` | 0 | ⬜ |
| `PAGE-009.26` | 679–692 | تابع | `chatbot_guide_answer` | 0 | ⬜ |
| `PAGE-009.27` | 694–777 | تابع | `chatbot_api_handle` | **12** | ⬜ |
| `PAGE-009.28` | 779–809 | تابع | `chatbot_save_faq` | 1 | ⬜ |
| `PAGE-009.29` | 811–866 | تابع | `chatbot_save_tts` | 0 | ⬜ |
| `PAGE-009.30` | 868–890 | تابع | `chatbot_widget_defaults` | 0 | ⬜ |
| `PAGE-009.31` | 892–940 | تابع | `chatbot_widget_config` | 0 | ⬜ |
| `PAGE-009.32` | 942–984 | تابع | `chatbot_save_widget` | 0 | ⬜ |
| `PAGE-009.33` | 986–1000 | تابع | `chatbot_tts_config` | 0 | ⬜ |
| `PAGE-009.34` | 1002–1010 | تابع | `chatbot_tts_proc_available` | 0 | ⬜ |
| `PAGE-009.35` | 1012–1038 | تابع | `chatbot_tts_find_espeak` | 0 | ⬜ |
| `PAGE-009.36` | 1040–1063 | تابع | `chatbot_tts_find_piper` | 0 | ⬜ |
| `PAGE-009.37` | 1065–1098 | تابع | `chatbot_tts_piper_model_rank` | 0 | ⬜ |
| `PAGE-009.38` | 1100–1141 | تابع | `chatbot_tts_piper_model` | 1 | ⬜ |
| `PAGE-009.39` | 1143–1173 | تابع | `chatbot_tts_active` | 0 | ⬜ |
| `PAGE-009.40` | 1175–1181 | تابع | `chatbot_tts_length_scale` | 0 | ⬜ |
| `PAGE-009.41` | 1183–1210 | تابع | `chatbot_tts_status` | 0 | ⬜ |
| `PAGE-009.42` | 1212–1215 | تابع | `chatbot_tts_rules_version` | 0 | ⬜ |
| `PAGE-009.43` | 1217–1226 | تابع | `chatbot_tts_word_replace` | 0 | ⬜ |
| `PAGE-009.44` | 1228–1235 | تابع | `chatbot_tts_ascii_digits` | 0 | ⬜ |
| `PAGE-009.45` | 1237–1243 | تابع | `chatbot_tts_ascii_letters` | 0 | ⬜ |
| `PAGE-009.46` | 1245–1259 | تابع | `chatbot_tts_digits_spoken` | 0 | ⬜ |
| `PAGE-009.47` | 1261–1287 | تابع | `chatbot_tts_three_digit` | 0 | ⬜ |
| `PAGE-009.48` | 1289–1314 | تابع | `chatbot_tts_int_to_words` | 0 | ⬜ |
| `PAGE-009.49` | 1316–1333 | تابع | `chatbot_tts_number_token` | 0 | ⬜ |
| `PAGE-009.50` | 1335–1345 | تابع | `chatbot_tts_number_int_string` | 0 | ⬜ |
| `PAGE-009.51` | 1347–1359 | تابع | `chatbot_tts_ordinal` | 0 | ⬜ |
| `PAGE-009.52` | 1361–1365 | تابع | `chatbot_tts_month_name` | 0 | ⬜ |
| `PAGE-009.53` | 1367–1400 | تابع | `chatbot_tts_dates_times` | 0 | ⬜ |
| `PAGE-009.54` | 1402–1442 | تابع | `chatbot_tts_ids` | 0 | ⬜ |
| `PAGE-009.55` | 1444–1472 | تابع | `chatbot_tts_numbers_to_words` | 0 | ⬜ |
| `PAGE-009.56` | 1474–1484 | تابع | `chatbot_tts_letter_name` | 0 | ⬜ |
| `PAGE-009.57` | 1486–1497 | تابع | `chatbot_tts_log_unknown` | 2 | ⬜ |
| `PAGE-009.58` | 1499–1522 | تابع | `chatbot_tts_spell_acronyms` | 0 | ⬜ |
| `PAGE-009.59` | 1524–1549 | تابع | `chatbot_tts_unknown_latin` | 0 | ⬜ |
| `PAGE-009.60` | 1551–1564 | تابع | `chatbot_tts_symbols` | 0 | ⬜ |
| `PAGE-009.61` | 1566–1573 | تابع | `chatbot_tts_strip_markdown` | 0 | ⬜ |
| `PAGE-009.62` | 1575–1580 | تابع | `chatbot_tts_zwnj_policy` | 1 | ⬜ |
| `PAGE-009.63` | 1582–1592 | تابع | `chatbot_tts_apply_zwnj` | 1 | ⬜ |
| `PAGE-009.64` | 1594–1601 | تابع | `chatbot_tts_he_yezafe` | 0 | ⬜ |
| `PAGE-009.65` | 1603–1629 | تابع | `chatbot_tts_lexicon` | 0 | ⬜ |
| `PAGE-009.66` | 1631–1635 | تابع | `chatbot_tts_lexicon_hash` | 0 | ⬜ |
| `PAGE-009.67` | 1637–1648 | تابع | `chatbot_tts_lexicon_apply` | 0 | ⬜ |
| `PAGE-009.68` | 1650–1653 | تابع | `chatbot_tts_spoken_path` | 0 | ⬜ |
| `PAGE-009.69` | 1655–1663 | تابع | `chatbot_tts_spoken_map` | 1 | ⬜ |
| `PAGE-009.70` | 1665–1682 | تابع | `chatbot_tts_spoken_set` | 2 | ⬜ |
| `PAGE-009.71` | 1684–1695 | تابع | `chatbot_tts_spoken_for` | 0 | ⬜ |
| `PAGE-009.72` | 1697–1708 | تابع | `chatbot_tts_clean` | 0 | ⬜ |
| `PAGE-009.73` | 1710–1724 | تابع | `chatbot_tts_trim_length` | 0 | ⬜ |
| `PAGE-009.74` | 1726–1771 | تابع | `chatbot_tts_colloquial` | 0 | ⬜ |
| `PAGE-009.75` | 1773–1790 | تابع | `chatbot_tts_phrase` | 0 | ⬜ |
| `PAGE-009.76` | 1792–1826 | تابع | `chatbot_tts_limit_phrase` | 0 | ⬜ |
| `PAGE-009.77` | 1828–1835 | تابع | `chatbot_tts_vocalize` | 0 | ⬜ |
| `PAGE-009.78` | 1837–1850 | تابع | `chatbot_tts_ezafe_skip_first` | 0 | ⬜ |
| `PAGE-009.79` | 1852–1863 | تابع | `chatbot_tts_ezafe_skip_second` | 0 | ⬜ |
| `PAGE-009.80` | 1865–1876 | تابع | `chatbot_tts_silent_heh` | 0 | ⬜ |
| `PAGE-009.81` | 1878–1889 | تابع | `chatbot_tts_ezafe_nouns` | 0 | ⬜ |
| `PAGE-009.82` | 1891–1902 | تابع | `chatbot_tts_ezafe_pairs_builtin` | 0 | ⬜ |
| `PAGE-009.83` | 1904–1924 | تابع | `chatbot_tts_overlay_map` | 0 | ⬜ |
| `PAGE-009.84` | 1926–1938 | تابع | `chatbot_tts_ezafe_pairs` | 0 | ⬜ |
| `PAGE-009.85` | 1940–1947 | تابع | `chatbot_tts_ezafe_mode` | 0 | ⬜ |
| `PAGE-009.86` | 1949–1953 | تابع | `chatbot_tts_word_bare` | 0 | ⬜ |
| `PAGE-009.87` | 1955–1974 | تابع | `chatbot_tts_mark_ezafe_first` | 0 | ⬜ |
| `PAGE-009.88` | 1976–2026 | تابع | `chatbot_tts_ezafe` | 0 | ⬜ |
| `PAGE-009.89` | 2028–2032 | تابع | `chatbot_tts_colloquial_enabled` | 0 | ⬜ |
| `PAGE-009.90` | 2034–2066 | تابع | `chatbot_tts_prepare` | 0 | ⬜ |
| `PAGE-009.91` | 2068–2087 | تابع | `chatbot_tts_lab_variant` | 0 | ⬜ |
| `PAGE-009.92` | 2089–2115 | تابع | `chatbot_tts_lab_harakat` | 0 | ⬜ |
| `PAGE-009.93` | 2117–2155 | تابع | `chatbot_tts_stages` | 0 | ⬜ |
| `PAGE-009.94` | 2157–2173 | تابع | `chatbot_tts_espeak_data_path` | 0 | ⬜ |
| `PAGE-009.95` | 2175–2204 | تابع | `chatbot_tts_ipa` | 0 | ⬜ |
| `PAGE-009.96` | 2206–2257 | تابع | `chatbot_tts_model_json_report` | 0 | ⬜ |
| `PAGE-009.97` | 2259–2263 | تابع | `chatbot_tts_lab_filename` | 0 | ⬜ |
| `PAGE-009.98` | 2265–2291 | تابع | `chatbot_tts_lab_run` | 2 | ⬜ |
| `PAGE-009.99` | 2293–2297 | تابع | `chatbot_tts_voice_id` | 0 | ⬜ |
| `PAGE-009.100` | 2299–2312 | تابع | `chatbot_tts_wait_exit` | 2 | ⬜ |
| `PAGE-009.101` | 2314–2344 | تابع | `chatbot_tts_taskkill` | 7 | ⬜ |
| `PAGE-009.102` | 2346–2434 | تابع | `chatbot_tts_exec` | 7 | ⬜ |
| `PAGE-009.103` | 2436–2440 | تابع | `chatbot_tts_is_valid_wav` | 0 | ⬜ |
| `PAGE-009.104` | 2442–2464 | تابع | `chatbot_tts_run_espeak` | 6 | ⬜ |
| `PAGE-009.105` | 2466–2513 | تابع | `chatbot_tts_run_piper` | 7 | ⬜ |
| `PAGE-009.106` | 2515–2529 | تابع | `chatbot_tts_probe_piper` | 0 | ⬜ |
| `PAGE-009.107` | 2531–2543 | تابع | `chatbot_tts_probe_engine` | 0 | ⬜ |
| `PAGE-009.108` | 2544–2545 | ناحیه | `بلوک رویه‌ای 2544-2545` | 0 | ⬜ |
| `PAGE-009.109` | 2546–2592 | تابع | `chatbot_tts_voice_status` | 3 | ⬜ |
| `PAGE-009.110` | 2594–2620 | تابع | `chatbot_tts_chunks` | 0 | ⬜ |
| `PAGE-009.111` | 2622–2657 | تابع | `chatbot_tts_wav_parse` | 0 | ⬜ |
| `PAGE-009.112` | 2659–2698 | تابع | `chatbot_tts_wav_concat` | 0 | ⬜ |
| `PAGE-009.113` | 2700–2757 | تابع | `chatbot_tts_say` | 7 | ⬜ |
| `PAGE-009.114` | 2759–2788 | تابع | `chatbot_tts_lab_default_phrases` | 0 | ⬜ |
| `PAGE-009.115` | 2790–2850 | تابع | `chatbot_tts_render_lab` | 0 | ⬜ |
| `PAGE-009.116` | 2852–2873 | تابع | `chatbot_tts_write_overlay` | 2 | ⬜ |
| `PAGE-009.117` | 2875–2898 | تابع | `chatbot_tts_corpus_texts` | 4 | ⬜ |
| `PAGE-009.118` | 2900–2904 | تابع | `chatbot_tts_propose_pair` | 0 | ⬜ |
| `PAGE-009.119` | 2906–2945 | تابع | `chatbot_tts_mine_pairs` | 0 | ⬜ |
| `PAGE-009.120` | 2947–2982 | تابع | `chatbot_tts_save_mined_pairs` | 0 | ⬜ |
| `PAGE-009.121` | 2984–3004 | تابع | `chatbot_tts_render_pair_miner` | 0 | ⬜ |
| `PAGE-009.122` | 3006–3198 | تابع | `chatbot_render_admin` | **8** | ⬜ |
| `PAGE-009.123` | 3200–3403 | تابع | `chatbot_render_widget` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-010</b> — <code>config.sample.php</code> (57 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-010.01` | 1–57 | ناحیه | `بلوک رویه‌ای 1-57` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-011</b> — <code>domain-scan.php</code> (1,996 خط، 50 آدرس، بازبینی‌شده 0/50)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-011.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `PAGE-011.02` | 7–48 | تابع | `domain_scan_ensure_schema` | 5 | ⬜ |
| `PAGE-011.03` | 50–54 | تابع | `domain_scan_timeout_ms` | 0 | ⬜ |
| `PAGE-011.04` | 56–59 | تابع | `domain_scan_account_label` | 0 | ⬜ |
| `PAGE-011.05` | 61–64 | تابع | `domain_scan_https_request` | 0 | ⬜ |
| `PAGE-011.06` | 66–75 | تابع | `domain_scan_validate_credentials` | 0 | ⬜ |
| `PAGE-011.07` | 77–90 | تابع | `domain_scan_credential_key` | 0 | ⬜ |
| `PAGE-011.08` | 92–119 | تابع | `domain_scan_store_run_credentials` | 0 | ⬜ |
| `PAGE-011.09` | 121–152 | تابع | `domain_scan_run_credentials` | 0 | ⬜ |
| `PAGE-011.10` | 154–166 | تابع | `domain_scan_clear_run_credentials` | 0 | ⬜ |
| `PAGE-011.11` | 168–183 | تابع | `domain_scan_ad_time` | 0 | ⬜ |
| `PAGE-011.12` | 185–253 | تابع | `ldap_domain_computers` | 3 | ⬜ |
| `PAGE-011.13` | 255–275 | تابع | `domain_scan_name_is_placeholder` | 0 | ⬜ |
| `PAGE-011.14` | 277–309 | تابع | `domain_scan_asset_for` | 1 | ⬜ |
| `PAGE-011.15` | 311–320 | تابع | `domain_scan_ad_values` | 0 | ⬜ |
| `PAGE-011.16` | 322–344 | تابع | `domain_scan_start` | 1 | ⬜ |
| `PAGE-011.17` | 346–382 | تابع | `domain_scan_start_selected` | 6 | ⬜ |
| `PAGE-011.18` | 384–415 | تابع | `domain_scan_import` | 1 | ⬜ |
| `PAGE-011.19` | 417–423 | تابع | `domain_scan_powershell_path` | 0 | ⬜ |
| `PAGE-011.20` | 425–501 | تابع | `domain_scan_run_processes` | 7 | ⬜ |
| `PAGE-011.21` | 503–510 | تابع | `domain_scan_resolve_ipv4` | 1 | ⬜ |
| `PAGE-011.22` | 512–519 | تابع | `domain_scan_ping_command` | 0 | ⬜ |
| `PAGE-011.23` | 521–539 | تابع | `domain_scan_icmp_round` | 0 | ⬜ |
| `PAGE-011.24` | 541–613 | تابع | `domain_scan_tcp_probe` | 3 | ⬜ |
| `PAGE-011.25` | 615–687 | تابع | `domain_scan_ping_hosts` | 1 | ⬜ |
| `PAGE-011.26` | 689–697 | تابع | `domain_scan_online` | 0 | ⬜ |
| `PAGE-011.27` | 699–717 | تابع | `domain_scan_mark_reachability` | 1 | ⬜ |
| `PAGE-011.28` | 719–732 | تابع | `domain_scan_queue_fallback_ips` | 0 | ⬜ |
| `PAGE-011.29` | 734–846 | تابع | `domain_scan_friendly_error` | 1 | ⬜ |
| `PAGE-011.30` | 848–1049 | تابع | `domain_scan_error_catalog` | 1 | ⬜ |
| `PAGE-011.31` | 1051–1130 | تابع | `domain_scan_error_code` | 0 | ⬜ |
| `PAGE-011.32` | 1132–1169 | تابع | `domain_scan_failure_summary` | 0 | ⬜ |
| `PAGE-011.33` | 1171–1191 | تابع | `domain_scan_retry_failed` | 1 | ⬜ |
| `PAGE-011.34` | 1193–1216 | تابع | `domain_scan_local_names` | 2 | ⬜ |
| `PAGE-011.35` | 1218–1234 | تابع | `domain_scan_preflight` | 0 | ⬜ |
| `PAGE-011.36` | 1236–1355 | تابع | `domain_scan_remote_inventory_many` | 5 | ⬜ |
| `PAGE-011.37` | 1357–1361 | تابع | `domain_scan_smb_script_path` | 0 | ⬜ |
| `PAGE-011.38` | 1363–1472 | تابع | `domain_scan_remote_inventory_via_smb` | 0 | ⬜ |
| `PAGE-011.39` | 1474–1485 | تابع | `domain_scan_remote_inventory` | 0 | ⬜ |
| `PAGE-011.40` | 1487–1490 | تابع | `domain_scan_prepare_script_path` | 0 | ⬜ |
| `PAGE-011.41` | 1492–1495 | تابع | `domain_scan_prepare_gpo_script_path` | 0 | ⬜ |
| `PAGE-011.42` | 1497–1577 | تابع | `domain_scan_prepare_domain_gpo` | 0 | ⬜ |
| `PAGE-011.43` | 1579–1582 | تابع | `domain_scan_prepare_target_script_path` | 0 | ⬜ |
| `PAGE-011.44` | 1584–1598 | تابع | `domain_scan_prepare_preflight` | 0 | ⬜ |
| `PAGE-011.45` | 1600–1669 | تابع | `domain_scan_prepare_targets` | 0 | ⬜ |
| `PAGE-011.46` | 1671–1808 | تابع | `domain_scan_store_inventory` | 2 | ⬜ |
| `PAGE-011.47` | 1810–1969 | تابع | `domain_scan_process` | **9** | ⬜ |
| `PAGE-011.48` | 1971–1978 | تابع | `domain_scan_run` | 0 | ⬜ |
| `PAGE-011.49` | 1980–1987 | تابع | `domain_scan_queue_rows` | 5 | ⬜ |
| `PAGE-011.50` | 1989–1995 | تابع | `domain_scan_latest_run` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-012</b> — <code>food-order-import.php</code> (477 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-012.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-012.02` | 4–35 | تابع | `food_order_import_column_keys` | 0 | ⬜ |
| `PAGE-012.03` | 36–38 | ناحیه | `بلوک رویه‌ای 36-38` | 0 | ⬜ |
| `PAGE-012.04` | 39–46 | تابع | `food_order_import_key` | 0 | ⬜ |
| `PAGE-012.05` | 48–113 | تابع | `food_order_import_read` | 0 | ⬜ |
| `PAGE-012.06` | 115–130 | تابع | `food_order_import_date` | 0 | ⬜ |
| `PAGE-012.07` | 132–151 | تابع | `food_order_import_time` | 0 | ⬜ |
| `PAGE-012.08` | 153–159 | تابع | `food_order_import_norm_food` | 0 | ⬜ |
| `PAGE-012.09` | 161–312 | تابع | `food_order_import_plan` | 6 | ⬜ |
| `PAGE-012.10` | 314–367 | تابع | `food_order_import_apply` | 0 | ⬜ |
| `PAGE-012.11` | 369–372 | تابع | `food_order_import_h` | 0 | ⬜ |
| `PAGE-012.12` | 374–476 | تابع | `food_order_import_handle` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-013</b> — <code>food-order.php</code> (3,856 خط، 109 آدرس، بازبینی‌شده 0/109)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-013.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-013.02` | 6–42 | تابع | `food_order_schema_ensure` | 5 | ⬜ |
| `PAGE-013.03` | 44–52 | تابع | `food_order_normalize_digits` | 0 | ⬜ |
| `PAGE-013.04` | 54–57 | تابع | `food_order_digits` | 0 | ⬜ |
| `PAGE-013.05` | 59–95 | تابع | `food_order_iso_date` | 0 | ⬜ |
| `PAGE-013.06` | 97–105 | تابع | `food_order_timezone` | 0 | ⬜ |
| `PAGE-013.07` | 107–110 | تابع | `food_order_now` | 0 | ⬜ |
| `PAGE-013.08` | 112–115 | تابع | `food_order_today` | 0 | ⬜ |
| `PAGE-013.09` | 117–125 | تابع | `food_order_jalali_label` | 0 | ⬜ |
| `PAGE-013.10` | 127–156 | تابع | `food_order_jalali_month_range` | 0 | ⬜ |
| `PAGE-013.11` | 158–163 | تابع | `food_order_is_primary_admin` | 0 | ⬜ |
| `PAGE-013.12` | 165–168 | تابع | `food_order_user_can` | 0 | ⬜ |
| `PAGE-013.13` | 170–175 | تابع | `food_order_require_permission` | 0 | ⬜ |
| `PAGE-013.14` | 177–186 | تابع | `food_order_can_view_order_counts` | 0 | ⬜ |
| `PAGE-013.15` | 188–193 | تابع | `food_order_require_primary_admin` | 0 | ⬜ |
| `PAGE-013.16` | 195–208 | تابع | `food_order_day_visit_subject` | 0 | ⬜ |
| `PAGE-013.17` | 210–221 | تابع | `food_order_log` | 1 | ⬜ |
| `PAGE-013.18` | 223–238 | تابع | `food_order_audit` | 0 | ⬜ |
| `PAGE-013.19` | 240–246 | تابع | `food_order_raw_mode` | 0 | ⬜ |
| `PAGE-013.20` | 248–267 | تابع | `food_order_mode` | 0 | ⬜ |
| `PAGE-013.21` | 269–278 | تابع | `food_order_user_row` | 0 | ⬜ |
| `PAGE-013.22` | 280–286 | تابع | `food_order_full_name` | 0 | ⬜ |
| `PAGE-013.23` | 287–288 | ناحیه | `بلوک رویه‌ای 287-288` | 0 | ⬜ |
| `PAGE-013.24` | 289–302 | تابع | `food_person_name_split` | 0 | ⬜ |
| `PAGE-013.25` | 303–304 | ناحیه | `بلوک رویه‌ای 303-304` | 0 | ⬜ |
| `PAGE-013.26` | 305–319 | تابع | `food_cancel_request_same_day_cutoff` | 0 | ⬜ |
| `PAGE-013.27` | 321–326 | تابع | `food_cancel_request_same_day_is_open` | 0 | ⬜ |
| `PAGE-013.28` | 328–349 | تابع | `food_order_employee_search` | 5 | ⬜ |
| `PAGE-013.29` | 351–375 | تابع | `food_order_report_employee_search` | 5 | ⬜ |
| `PAGE-013.30` | 377–381 | تابع | `food_order_name_key` | 0 | ⬜ |
| `PAGE-013.31` | 383–397 | تابع | `food_order_catalog_has_category` | 0 | ⬜ |
| `PAGE-013.32` | 399–415 | تابع | `food_order_catalog` | 0 | ⬜ |
| `PAGE-013.33` | 417–427 | تابع | `food_order_meal_type` | 0 | ⬜ |
| `PAGE-013.34` | 429–558 | تابع | `food_order_month_status` | 6 | ⬜ |
| `PAGE-013.35` | 560–593 | تابع | `food_order_list_date` | 0 | ⬜ |
| `PAGE-013.36` | 595–651 | تابع | `food_order_list_range` | 4 | ⬜ |
| `PAGE-013.37` | 653–706 | تابع | `food_order_my_orders` | 5 | ⬜ |
| `PAGE-013.38` | 708–743 | تابع | `food_order_report_descriptions` | 0 | ⬜ |
| `PAGE-013.39` | 745–787 | تابع | `food_order_manager_orders` | 0 | ⬜ |
| `PAGE-013.40` | 789–832 | تابع | `food_order_report_rows_range` | 0 | ⬜ |
| `PAGE-013.41` | 834–898 | تابع | `food_order_group_orders` | 0 | ⬜ |
| `PAGE-013.42` | 900–926 | تابع | `food_order_report_summary` | 5 | ⬜ |
| `PAGE-013.43` | 928–992 | تابع | `food_order_statistics_range` | 0 | ⬜ |
| `PAGE-013.44` | 993–994 | ناحیه | `بلوک رویه‌ای 993-994` | 0 | ⬜ |
| `PAGE-013.45` | 995–1061 | تابع | `food_order_org_map` | 0 | ⬜ |
| `PAGE-013.46` | 1063–1198 | تابع | `food_order_printed_delivery_report` | 5 | ⬜ |
| `PAGE-013.47` | 1200–1253 | تابع | `food_order_export_printed_delivery_report` | 0 | ⬜ |
| `PAGE-013.48` | 1255–1307 | تابع | `food_order_selected_orders_range` | 5 | ⬜ |
| `PAGE-013.49` | 1309–1352 | تابع | `food_order_export_selected_orders_range` | 0 | ⬜ |
| `PAGE-013.50` | 1354–1371 | تابع | `food_order_emergency_window_payload` | 0 | ⬜ |
| `PAGE-013.51` | 1373–1385 | تابع | `food_order_emergency_window_state` | 0 | ⬜ |
| `PAGE-013.52` | 1387–1408 | تابع | `food_order_emergency_window_states` | 5 | ⬜ |
| `PAGE-013.53` | 1410–1463 | تابع | `food_order_emergency_window_grant` | 0 | ⬜ |
| `PAGE-013.54` | 1465–1500 | تابع | `food_order_emergency_window_revoke` | 0 | ⬜ |
| `PAGE-013.55` | 1502–1525 | تابع | `food_order_ensure_orderable_day` | 1 | ⬜ |
| `PAGE-013.56` | 1527–1550 | تابع | `food_order_lock_cancellable_day` | 1 | ⬜ |
| `PAGE-013.57` | 1552–1567 | تابع | `food_order_verify_employee_national_code` | 1 | ⬜ |
| `PAGE-013.58` | 1569–1676 | تابع | `food_order_create_or_change` | 0 | ⬜ |
| `PAGE-013.59` | 1678–1722 | تابع | `food_order_cancel` | 0 | ⬜ |
| `PAGE-013.60` | 1724–1793 | تابع | `food_order_cancel_proxy` | 0 | ⬜ |
| `PAGE-013.61` | 1795–1825 | تابع | `food_order_cancel_request_ensure` | 5 | ⬜ |
| `PAGE-013.62` | 1827–1832 | تابع | `food_order_require_cancel_reviewer` | 0 | ⬜ |
| `PAGE-013.63` | 1834–1837 | تابع | `food_order_cancel_inbox_url` | 0 | ⬜ |
| `PAGE-013.64` | 1839–1856 | تابع | `food_order_user_is_cancel_reviewer` | 0 | ⬜ |
| `PAGE-013.65` | 1858–1873 | تابع | `food_order_cancel_reviewer_ids` | 0 | ⬜ |
| `PAGE-013.66` | 1875–1907 | تابع | `food_order_notify_cancel_reviewers` | 0 | ⬜ |
| `PAGE-013.67` | 1909–1912 | تابع | `food_order_notify_support_managers` | 0 | ⬜ |
| `PAGE-013.68` | 1914–1947 | تابع | `food_order_render_pending_cancels_card` | 0 | ⬜ |
| `PAGE-013.69` | 1949–1974 | تابع | `food_order_pending_cancel_map` | 0 | ⬜ |
| `PAGE-013.70` | 1976–2065 | تابع | `food_order_cancel_request_create` | 4 | ⬜ |
| `PAGE-013.71` | 2067–2106 | تابع | `food_order_cancel_request_list` | 0 | ⬜ |
| `PAGE-013.72` | 2108–2181 | تابع | `food_order_cancel_request_decide` | 4 | ⬜ |
| `PAGE-013.73` | 2183–2265 | تابع | `food_order_catalog_save` | 0 | ⬜ |
| `PAGE-013.74` | 2267–2395 | تابع | `food_order_save_day` | 6 | ⬜ |
| `PAGE-013.75` | 2397–2417 | تابع | `food_order_status_summary` | 0 | ⬜ |
| `PAGE-013.76` | 2419–2427 | تابع | `food_order_api_body` | 0 | ⬜ |
| `PAGE-013.77` | 2429–2438 | تابع | `food_order_send_json` | 0 | ⬜ |
| `PAGE-013.78` | 2440–2447 | تابع | `food_order_safe_error` | 0 | ⬜ |
| `PAGE-013.79` | 2449–2457 | تابع | `food_order_export_safe_value` | 0 | ⬜ |
| `PAGE-013.80` | 2459–2526 | تابع | `food_order_export_manager_orders` | 0 | ⬜ |
| `PAGE-013.81` | 2528–2549 | تابع | `food_order_send_xlsx` | 0 | ⬜ |
| `PAGE-013.82` | 2551–2602 | تابع | `food_order_export_statistics_range` | 5 | ⬜ |
| `PAGE-013.83` | 2604–2608 | تابع | `food_guest_request_roles` | 0 | ⬜ |
| `PAGE-013.84` | 2610–2619 | تابع | `food_guest_request_is_reviewer` | 0 | ⬜ |
| `PAGE-013.85` | 2621–2626 | تابع | `food_guest_request_allowed` | 0 | ⬜ |
| `PAGE-013.86` | 2628–2640 | تابع | `food_guest_request_can_manage` | 0 | ⬜ |
| `PAGE-013.87` | 2642–2651 | تابع | `food_guest_request_change_window_open` | 0 | ⬜ |
| `PAGE-013.88` | 2653–2667 | تابع | `food_guest_request_same_day_cutoff` | 0 | ⬜ |
| `PAGE-013.89` | 2669–2674 | تابع | `food_guest_request_same_day_is_open` | 0 | ⬜ |
| `PAGE-013.90` | 2676–2694 | تابع | `food_guest_request_date_error` | 0 | ⬜ |
| `PAGE-013.91` | 2696–2710 | تابع | `food_guest_requested_total` | 0 | ⬜ |
| `PAGE-013.92` | 2712–2740 | تابع | `food_guest_schema_missing` | 0 | ⬜ |
| `PAGE-013.93` | 2742–2746 | تابع | `food_guest_schema_ready` | 0 | ⬜ |
| `PAGE-013.94` | 2748–2754 | تابع | `food_guest_schema_ensure` | 0 | ⬜ |
| `PAGE-013.95` | 2756–2766 | تابع | `food_guest_deputies` | 0 | ⬜ |
| `PAGE-013.96` | 2768–2817 | تابع | `food_guest_user_deputy_id` | 4 | ⬜ |
| `PAGE-013.97` | 2819–2913 | تابع | `food_guest_request_list` | 0 | ⬜ |
| `PAGE-013.98` | 2915–2963 | تابع | `food_guest_request_create` | 1 | ⬜ |
| `PAGE-013.99` | 2965–2997 | تابع | `food_guest_request_cancel` | 0 | ⬜ |
| `PAGE-013.100` | 2999–3062 | تابع | `food_guest_request_update` | 0 | ⬜ |
| `PAGE-013.101` | 3064–3098 | تابع | `food_guest_request_delete` | 0 | ⬜ |
| `PAGE-013.102` | 3100–3269 | تابع | `food_order_api_handle` | 4 | ⬜ |
| `PAGE-013.103` | 3272–3343 | تابع | `food_order_emergency_handle` | 2 | ⬜ |
| `PAGE-013.104` | 3345–3364 | تابع | `food_order_save_cancel_request_cutoff` | 0 | ⬜ |
| `PAGE-013.105` | 3366–3480 | تابع | `food_order_bulk_snapshot` | 0 | ⬜ |
| `PAGE-013.106` | 3482–3567 | تابع | `food_order_bulk_apply` | 0 | ⬜ |
| `PAGE-013.107` | 3569–3584 | تابع | `food_order_bulk_api_handle` | 2 | ⬜ |
| `PAGE-013.108` | 3586–3822 | تابع | `food_order_menu_api_handle` | 4 | ⬜ |
| `PAGE-013.109` | 3824–3855 | تابع | `food_order_render_page` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-014</b> — <code>food-personnel-link.php</code> (818 خط، 27 آدرس، بازبینی‌شده 0/27)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-014.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `PAGE-014.02` | 20–27 | تابع | `fpl_norm_name` | 0 | ⬜ |
| `PAGE-014.03` | 29–37 | تابع | `fpl_person_name` | 0 | ⬜ |
| `PAGE-014.04` | 39–62 | تابع | `fpl_reference_columns` | 4 | ⬜ |
| `PAGE-014.05` | 64–86 | تابع | `fpl_manual_reference_columns` | 0 | ⬜ |
| `PAGE-014.06` | 88–97 | تابع | `fpl_column_exists` | 0 | ⬜ |
| `PAGE-014.07` | 99–187 | تابع | `fpl_candidates` | 0 | ⬜ |
| `PAGE-014.08` | 189–203 | تابع | `fpl_pair_is_allowed` | 0 | ⬜ |
| `PAGE-014.09` | 205–315 | تابع | `fpl_merge` | 5 | ⬜ |
| `PAGE-014.10` | 317–323 | تابع | `fpl_backup_users` | 4 | ⬜ |
| `PAGE-014.11` | 325–330 | تابع | `fpl_normalize_code` | 0 | ⬜ |
| `PAGE-014.12` | 332–345 | تابع | `fpl_codes_equal` | 0 | ⬜ |
| `PAGE-014.13` | 347–353 | تابع | `fpl_is_local_food_account` | 0 | ⬜ |
| `PAGE-014.14` | 355–364 | تابع | `fpl_food_personnel_code` | 0 | ⬜ |
| `PAGE-014.15` | 366–379 | تابع | `fpl_food_matches_code` | 0 | ⬜ |
| `PAGE-014.16` | 381–401 | تابع | `fpl_code_candidate_rows` | 0 | ⬜ |
| `PAGE-014.17` | 403–425 | تابع | `fpl_food_code_matches` | 0 | ⬜ |
| `PAGE-014.18` | 427–437 | تابع | `fpl_find_food_by_code` | 0 | ⬜ |
| `PAGE-014.19` | 439–461 | تابع | `fpl_select_food_matches` | 0 | ⬜ |
| `PAGE-014.20` | 463–541 | تابع | `fpl_apply` | 0 | ⬜ |
| `PAGE-014.21` | 543–548 | تابع | `fpl_manual_input` | 0 | ⬜ |
| `PAGE-014.22` | 550–661 | تابع | `fpl_render_request` | 2 | ⬜ |
| `PAGE-014.23` | 663–684 | تابع | `fpl_backup_tables` | 0 | ⬜ |
| `PAGE-014.24` | 686–768 | تابع | `fpl_report_rows` | 0 | ⬜ |
| `PAGE-014.25` | 770–790 | تابع | `fpl_similar_food_names` | 0 | ⬜ |
| `PAGE-014.26` | 792–811 | تابع | `fpl_report_xlsx_bytes` | 0 | ⬜ |
| `PAGE-014.27` | 813–817 | تابع | `fpl_report_csv` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-015</b> — <code>food-reconciliation.php</code> (1,022 خط، 25 آدرس، بازبینی‌شده 0/25)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-015.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `PAGE-015.02` | 13–32 | تابع | `food_reconciliation_schema_ensure` | 5 | ⬜ |
| `PAGE-015.03` | 34–63 | تابع | `food_reconciliation_calculate` | 0 | ⬜ |
| `PAGE-015.04` | 65–103 | تابع | `food_reconciliation_served_counts` | 0 | ⬜ |
| `PAGE-015.05` | 105–119 | تابع | `food_reconciliation_absentee_counts` | 0 | ⬜ |
| `PAGE-015.06` | 121–146 | تابع | `food_reconciliation_disposition_summary` | 0 | ⬜ |
| `PAGE-015.07` | 148–166 | تابع | `food_reconciliation_count` | 0 | ⬜ |
| `PAGE-015.08` | 168–184 | تابع | `food_reconciliation_month` | 0 | ⬜ |
| `PAGE-015.09` | 186–201 | تابع | `food_reconciliation_month_range` | 0 | ⬜ |
| `PAGE-015.10` | 203–213 | تابع | `food_reconciliation_month_key` | 0 | ⬜ |
| `PAGE-015.11` | 215–227 | تابع | `food_reconciliation_date_range` | 0 | ⬜ |
| `PAGE-015.12` | 229–236 | تابع | `food_reconciliation_meal_filter` | 0 | ⬜ |
| `PAGE-015.13` | 238–259 | تابع | `food_reconciliation_order_counts` | 0 | ⬜ |
| `PAGE-015.14` | 261–368 | تابع | `food_reconciliation_rows` | 0 | ⬜ |
| `PAGE-015.15` | 370–392 | تابع | `food_reconciliation_invoice_rows` | 0 | ⬜ |
| `PAGE-015.16` | 394–477 | تابع | `food_reconciliation_report` | 0 | ⬜ |
| `PAGE-015.17` | 479–486 | تابع | `food_reconciliation_audit` | 0 | ⬜ |
| `PAGE-015.18` | 488–524 | تابع | `food_reconciliation_disposition_parse` | 0 | ⬜ |
| `PAGE-015.19` | 526–698 | تابع | `food_reconciliation_save` | 1 | ⬜ |
| `PAGE-015.20` | 700–703 | تابع | `food_reconciliation_upload_directory` | 0 | ⬜ |
| `PAGE-015.21` | 705–783 | تابع | `food_reconciliation_validate_file` | 1 | ⬜ |
| `PAGE-015.22` | 785–869 | تابع | `food_reconciliation_upload_invoice` | **8** | ⬜ |
| `PAGE-015.23` | 871–903 | تابع | `food_reconciliation_download_invoice` | 0 | ⬜ |
| `PAGE-015.24` | 905–989 | تابع | `food_reconciliation_export` | 0 | ⬜ |
| `PAGE-015.25` | 991–1021 | تابع | `food_reconciliation_api_handle` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-016</b> — <code>food-ticket-auto-receipts.php</code> (355 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-016.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-016.02` | 4–21 | تابع | `food_ticket_auto_receipt_label` | 0 | ⬜ |
| `PAGE-016.03` | 23–37 | تابع | `food_ticket_auto_receipt_date` | 0 | ⬜ |
| `PAGE-016.04` | 39–55 | تابع | `food_ticket_auto_receipt_holiday` | 0 | ⬜ |
| `PAGE-016.05` | 57–71 | تابع | `food_ticket_auto_receipt_table_exists` | 0 | ⬜ |
| `PAGE-016.06` | 73–86 | تابع | `food_ticket_auto_receipt_columns` | 0 | ⬜ |
| `PAGE-016.07` | 88–122 | تابع | `food_ticket_auto_receipt_schema_ready` | 0 | ⬜ |
| `PAGE-016.08` | 124–165 | تابع | `food_ticket_auto_receipt_insert` | 1 | ⬜ |
| `PAGE-016.09` | 167–354 | تابع | `food_ticket_auto_receipts_run` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-017</b> — <code>food-ticket-engine.php</code> (1,108 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-017.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `PAGE-017.02` | 13–20 | تابع | `food_ticket_norm_personnel` | 0 | ⬜ |
| `PAGE-017.03` | 22–52 | تابع | `food_ticket_user_index` | 0 | ⬜ |
| `PAGE-017.04` | 54–84 | تابع | `food_ticket_find_user_strict` | 0 | ⬜ |
| `PAGE-017.05` | 86–131 | تابع | `food_ticket_engine_delete_source` | 0 | ⬜ |
| `PAGE-017.06` | 133–142 | تابع | `food_ticket_build_order_map` | 0 | ⬜ |
| `PAGE-017.07` | 144–154 | تابع | `food_ticket_order_map_lazy` | 0 | ⬜ |
| `PAGE-017.08` | 156–178 | تابع | `food_ticket_card_map_ensure` | 5 | ⬜ |
| `PAGE-017.09` | 180–200 | تابع | `food_ticket_card_map_load` | 0 | ⬜ |
| `PAGE-017.10` | 202–217 | تابع | `food_ticket_card_map_lookup` | 0 | ⬜ |
| `PAGE-017.11` | 219–237 | تابع | `food_ticket_card_map_learn` | 1 | ⬜ |
| `PAGE-017.12` | 239–270 | تابع | `food_ticket_process_print_queue_locked` | 3 | ⬜ |
| `PAGE-017.13` | 272–324 | تابع | `food_ticket_find_twin_event` | 0 | ⬜ |
| `PAGE-017.14` | 326–347 | تابع | `food_ticket_sort_punches` | 0 | ⬜ |
| `PAGE-017.15` | 348–352 | ناحیه | `بلوک رویه‌ای 348-352` | 1 | ⬜ |
| `PAGE-017.16` | 353–370 | تابع | `food_guest_cap_mode` | 0 | ⬜ |
| `PAGE-017.17` | 372–380 | تابع | `food_guest_cap_clock` | 0 | ⬜ |
| `PAGE-017.18` | 382–387 | تابع | `food_guest_cap_start` | 0 | ⬜ |
| `PAGE-017.19` | 389–394 | تابع | `food_guest_cap_end` | 0 | ⬜ |
| `PAGE-017.20` | 396–401 | تابع | `food_guest_cap_cutoff` | 0 | ⬜ |
| `PAGE-017.21` | 403–411 | تابع | `food_guest_variable_cap_from_counts` | 0 | ⬜ |
| `PAGE-017.22` | 413–426 | تابع | `food_guest_variable_cap_at` | 0 | ⬜ |
| `PAGE-017.23` | 428–448 | تابع | `food_ticket_guest_variable_cap` | 0 | ⬜ |
| `PAGE-017.24` | 449–454 | ناحیه | `بلوک رویه‌ای 449-454` | 0 | ⬜ |
| `PAGE-017.25` | 455–461 | تابع | `food_ticket_guest_issued_total` | 0 | ⬜ |
| `PAGE-017.26` | 463–471 | تابع | `food_ticket_guest_cap_reached` | 0 | ⬜ |
| `PAGE-017.27` | 473–651 | تابع | `food_ticket_decide_punch` | 7 | ⬜ |
| `PAGE-017.28` | 653–775 | تابع | `food_ticket_decide_for_user` | 2 | ⬜ |
| `PAGE-017.29` | 777–844 | تابع | `food_ticket_read_source_rows_today` | 0 | ⬜ |
| `PAGE-017.30` | 846–1107 | تابع | `food_ticket_process_batch_v2` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-018</b> — <code>food-ticket-env.php</code> (267 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-018.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `PAGE-018.02` | 25–59 | تابع | `food_ticket_env_value` | 0 | ⬜ |
| `PAGE-018.03` | 61–65 | تابع | `food_ticket_env_source_table` | 0 | ⬜ |
| `PAGE-018.04` | 68–72 | تابع | `food_ticket_legacy_key` | 0 | ⬜ |
| `PAGE-018.05` | 74–80 | تابع | `food_ticket_legacy_display_map` | 0 | ⬜ |
| `PAGE-018.06` | 82–92 | تابع | `food_ticket_legacy_brand_tokens` | 0 | ⬜ |
| `PAGE-018.07` | 94–100 | تابع | `food_ticket_legacy_brand_words` | 0 | ⬜ |
| `PAGE-018.08` | 102–115 | تابع | `food_ticket_is_legacy_brand_name` | 0 | ⬜ |
| `PAGE-018.09` | 117–121 | تابع | `food_ticket_route_is_test_attendance` | 0 | ⬜ |
| `PAGE-018.10` | 123–131 | تابع | `food_ticket_printer_profile` | 0 | ⬜ |
| `PAGE-018.11` | 133–175 | تابع | `food_ticket_db_column` | 0 | ⬜ |
| `PAGE-018.12` | 177–207 | تابع | `food_ticket_config_has_column` | 0 | ⬜ |
| `PAGE-018.13` | 209–227 | تابع | `food_ticket_config_alias_row` | 0 | ⬜ |
| `PAGE-018.14` | 229–241 | تابع | `food_ticket_setting_password_enc` | 0 | ⬜ |
| `PAGE-018.15` | 243–256 | تابع | `food_ticket_source_table` | 0 | ⬜ |
| `PAGE-018.16` | 258–266 | تابع | `food_ticket_env_legacy_paths` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-019</b> — <code>food-ticket-groups.php</code> (3,803 خط، 75 آدرس، بازبینی‌شده 0/75)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-019.01` | 1–30 | ناحیه | `بلوک رویه‌ای 1-30` | 0 | ⬜ |
| `PAGE-019.02` | 31–60 | تابع | `food_ticket_audit` | 0 | ⬜ |
| `PAGE-019.03` | 62–157 | تابع | `food_ticket_groups_ensure` | **10** | ⬜ |
| `PAGE-019.04` | 159–219 | تابع | `food_ticket_groups_migrate` | 4 | ⬜ |
| `PAGE-019.05` | 221–222 | ناحیه | `غیبت روزانه` | 0 | ⬜ |
| `PAGE-019.06` | 223–249 | تابع | `food_ticket_absence_ensure` | 5 | ⬜ |
| `PAGE-019.07` | 251–427 | تابع | `food_ticket_manual_attendance_reconcile_range` | 7 | ⬜ |
| `PAGE-019.08` | 429–432 | تابع | `food_ticket_manual_attendance_reconcile_date` | 0 | ⬜ |
| `PAGE-019.09` | 434–461 | تابع | `food_ticket_absence_load` | 0 | ⬜ |
| `PAGE-019.10` | 463–481 | تابع | `food_ticket_group_absence_locked` | 0 | ⬜ |
| `PAGE-019.11` | 483–555 | تابع | `food_ticket_absence_set` | 1 | ⬜ |
| `PAGE-019.12` | 557–603 | تابع | `food_ticket_absence_clear_group` | 1 | ⬜ |
| `PAGE-019.13` | 605–628 | تابع | `food_ticket_absence_held_count` | 5 | ⬜ |
| `PAGE-019.14` | 630–636 | تابع | `food_ticket_can_override_absence` | 0 | ⬜ |
| `PAGE-019.15` | 638–639 | ناحیه | `گروه‌ها: بارگذاری و شناسایی` | 0 | ⬜ |
| `PAGE-019.16` | 640–669 | تابع | `food_ticket_groups_load` | 0 | ⬜ |
| `PAGE-019.17` | 671–687 | تابع | `food_ticket_group_find_by_uid` | 0 | ⬜ |
| `PAGE-019.18` | 689–707 | تابع | `food_ticket_group_find_by_card` | 0 | ⬜ |
| `PAGE-019.19` | 709–727 | تابع | `food_ticket_group_resolve` | 0 | ⬜ |
| `PAGE-019.20` | 729–741 | تابع | `food_ticket_group_find_by_card_any` | 0 | ⬜ |
| `PAGE-019.21` | 743–753 | تابع | `food_ticket_group_date_ok` | 0 | ⬜ |
| `PAGE-019.22` | 755–758 | تابع | `food_ticket_group_member_key` | 0 | ⬜ |
| `PAGE-019.23` | 760–778 | تابع | `food_ticket_group_event_ids_for_run` | 0 | ⬜ |
| `PAGE-019.24` | 780–781 | ناحیه | `محاسبهٔ واجد شرایط (هستهٔ منطق، بدون DB)` | 0 | ⬜ |
| `PAGE-019.25` | 782–856 | تابع | `food_ticket_group_plan` | 0 | ⬜ |
| `PAGE-019.26` | 857–862 | ناحیه | `بلوک رویه‌ای 857-862` | 0 | ⬜ |
| `PAGE-019.27` | 863–876 | تابع | `food_person_name_split` | 0 | ⬜ |
| `PAGE-019.28` | 877–878 | ناحیه | `بلوک رویه‌ای 877-878` | 0 | ⬜ |
| `PAGE-019.29` | 879–893 | تابع | `food_ticket_group_members` | 0 | ⬜ |
| `PAGE-019.30` | 895–910 | تابع | `food_ticket_user_group_id` | 0 | ⬜ |
| `PAGE-019.31` | 912–933 | تابع | `food_ticket_day_visit_users` | 0 | ⬜ |
| `PAGE-019.32` | 935–973 | تابع | `food_ticket_group_members_for_date` | 0 | ⬜ |
| `PAGE-019.33` | 975–993 | تابع | `food_ticket_day_visit_row` | 0 | ⬜ |
| `PAGE-019.34` | 995–1020 | تابع | `food_ticket_visit_change_hhmm` | 0 | ⬜ |
| `PAGE-019.35` | 1022–1032 | تابع | `food_ticket_visit_change_now` | 0 | ⬜ |
| `PAGE-019.36` | 1034–1043 | تابع | `food_ticket_visit_change_is_open` | 0 | ⬜ |
| `PAGE-019.37` | 1045–1053 | تابع | `food_ticket_visit_change_guard` | 0 | ⬜ |
| `PAGE-019.38` | 1055–1061 | تابع | `food_ticket_can_cancel_day_visit` | 0 | ⬜ |
| `PAGE-019.39` | 1063–1129 | تابع | `food_ticket_day_visit_state` | 0 | ⬜ |
| `PAGE-019.40` | 1131–1252 | تابع | `food_ticket_day_visit_save` | 2 | ⬜ |
| `PAGE-019.41` | 1254–1313 | تابع | `food_ticket_day_visit_clear` | 1 | ⬜ |
| `PAGE-019.42` | 1315–1331 | تابع | `food_ticket_api_cancel_day_visit` | 0 | ⬜ |
| `PAGE-019.43` | 1333–1364 | تابع | `food_ticket_group_ticketed_keys` | 5 | ⬜ |
| `PAGE-019.44` | 1366–1386 | تابع | `food_ticket_group_order_map` | 0 | ⬜ |
| `PAGE-019.45` | 1388–1402 | تابع | `food_ticket_group_food_lookup` | 0 | ⬜ |
| `PAGE-019.46` | 1404–1442 | تابع | `food_ticket_group_overview` | 0 | ⬜ |
| `PAGE-019.47` | 1444–1445 | ناحیه | `پردازش تردد نماینده` | 0 | ⬜ |
| `PAGE-019.48` | 1446–1773 | تابع | `food_ticket_group_try_process` | 4 | ⬜ |
| `PAGE-019.49` | 1775–1838 | تابع | `food_ticket_hold_ticket_for_absence` | 6 | ⬜ |
| `PAGE-019.50` | 1840–1889 | تابع | `food_ticket_release_held_ticket` | 6 | ⬜ |
| `PAGE-019.51` | 1891–1918 | تابع | `food_ticket_events_ensure_hold_status` | **8** | ⬜ |
| `PAGE-019.52` | 1920–1921 | ناحیه | `تاریخچهٔ L_UID نماینده‌ها و تردد با شناسهٔ ناشناخت` | 0 | ⬜ |
| `PAGE-019.53` | 1922–1936 | تابع | `food_ticket_group_uid_history_record` | 1 | ⬜ |
| `PAGE-019.54` | 1938–1953 | تابع | `food_ticket_group_uid_history_has` | 0 | ⬜ |
| `PAGE-019.55` | 1955–2020 | تابع | `food_ticket_group_try_unknown` | **10** | ⬜ |
| `PAGE-019.56` | 2022–2024 | ناحیه | `API مدیریت گروه‌ها` | 0 | ⬜ |
| `PAGE-019.57` | 2025–2029 | تابع | `food_ticket_manual_attendance_today` | 0 | ⬜ |
| `PAGE-019.58` | 2031–2049 | تابع | `food_ticket_manual_attendance_date` | 0 | ⬜ |
| `PAGE-019.59` | 2051–2085 | تابع | `food_ticket_event_is_orphan_ticket` | 0 | ⬜ |
| `PAGE-019.60` | 2087–2098 | تابع | `food_ticket_manual_attendance_guest_row` | 0 | ⬜ |
| `PAGE-019.61` | 2100–2268 | تابع | `food_ticket_api_manual_attendance` | 1 | ⬜ |
| `PAGE-019.62` | 2270–2320 | تابع | `food_ticket_manual_attendance_clear_absence` | 1 | ⬜ |
| `PAGE-019.63` | 2322–2792 | تابع | `food_ticket_api_manual_attendance_save` | 4 | ⬜ |
| `PAGE-019.64` | 2794–2911 | تابع | `food_ticket_api_manual_attendance_save_guest` | 0 | ⬜ |
| `PAGE-019.65` | 2913–2994 | تابع | `food_ticket_api_manual_attendance_save_many` | **9** | ⬜ |
| `PAGE-019.66` | 2996–3097 | تابع | `food_ticket_api_groups` | 0 | ⬜ |
| `PAGE-019.67` | 3099–3328 | تابع | `food_ticket_api_save_group` | 5 | ⬜ |
| `PAGE-019.68` | 3329–3333 | ناحیه | `بلوک رویه‌ای 3329-3333` | 0 | ⬜ |
| `PAGE-019.69` | 3334–3352 | تابع | `food_ticket_jalali_label` | 0 | ⬜ |
| `PAGE-019.70` | 3354–3444 | تابع | `food_ticket_api_absence_status` | 0 | ⬜ |
| `PAGE-019.71` | 3446–3511 | تابع | `food_ticket_api_absence_save` | 0 | ⬜ |
| `PAGE-019.72` | 3513–3529 | تابع | `food_ticket_api_absence_clear` | 0 | ⬜ |
| `PAGE-019.73` | 3531–3557 | تابع | `food_ticket_api_delivery` | 0 | ⬜ |
| `PAGE-019.74` | 3559–3771 | تابع | `food_ticket_cancel_ticket_for_user` | 7 | ⬜ |
| `PAGE-019.75` | 3773–3802 | تابع | `food_ticket_api_delete_group` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-020</b> — <code>food-ticket-import.php</code> (574 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-020.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `PAGE-020.02` | 18–28 | تابع | `food_ticket_import_aliases` | 0 | ⬜ |
| `PAGE-020.03` | 30–41 | تابع | `food_ticket_import_normalize` | 0 | ⬜ |
| `PAGE-020.04` | 43–49 | تابع | `food_ticket_import_key` | 0 | ⬜ |
| `PAGE-020.05` | 51–55 | تابع | `food_ticket_import_digits` | 0 | ⬜ |
| `PAGE-020.06` | 57–69 | تابع | `food_ticket_import_column_index` | 0 | ⬜ |
| `PAGE-020.07` | 71–96 | تابع | `food_ticket_import_shared_strings` | 1 | ⬜ |
| `PAGE-020.08` | 98–171 | تابع | `food_ticket_import_read_xlsx` | 1 | ⬜ |
| `PAGE-020.09` | 173–204 | تابع | `food_ticket_import_csv_delimiter_counts` | 0 | ⬜ |
| `PAGE-020.10` | 206–242 | تابع | `food_ticket_import_read_csv` | 2 | ⬜ |
| `PAGE-020.11` | 244–276 | تابع | `food_ticket_import_map_columns` | 0 | ⬜ |
| `PAGE-020.12` | 278–321 | تابع | `food_ticket_import_parse_file` | 0 | ⬜ |
| `PAGE-020.13` | 323–354 | تابع | `food_ticket_import_validate_row` | 0 | ⬜ |
| `PAGE-020.14` | 356–476 | تابع | `food_ticket_import_apply` | 2 | ⬜ |
| `PAGE-020.15` | 478–508 | تابع | `food_ticket_import_summary` | 0 | ⬜ |
| `PAGE-020.16` | 510–573 | تابع | `food_ticket_employees_import_handle` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-021</b> — <code>food-ticket-netprint.php</code> (1,299 خط، 35 آدرس، بازبینی‌شده 0/35)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-021.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `PAGE-021.02` | 17–75 | تابع | `food_ticket_np_settings` | 1 | ⬜ |
| `PAGE-021.03` | 77–95 | تابع | `food_ticket_np_find_font` | 0 | ⬜ |
| `PAGE-021.04` | 97–98 | ناحیه | `شکل‌دهی حروف فارسی/عربی` | 0 | ⬜ |
| `PAGE-021.05` | 99–137 | تابع | `food_ticket_np_tables` | 0 | ⬜ |
| `PAGE-021.06` | 139–152 | تابع | `food_ticket_np_codepoints` | 0 | ⬜ |
| `PAGE-021.07` | 154–204 | تابع | `food_ticket_np_shape` | 0 | ⬜ |
| `PAGE-021.08` | 206–222 | تابع | `food_ticket_np_class` | 0 | ⬜ |
| `PAGE-021.09` | 224–288 | تابع | `food_ticket_np_visual` | 0 | ⬜ |
| `PAGE-021.10` | 290–291 | ناحیه | `رسم تصویر فیش` | 0 | ⬜ |
| `PAGE-021.11` | 292–301 | تابع | `food_ticket_np_measure` | 0 | ⬜ |
| `PAGE-021.12` | 303–417 | تابع | `food_ticket_np_render` | 0 | ⬜ |
| `PAGE-021.13` | 419–431 | تابع | `food_ticket_print_trim_info` | 0 | ⬜ |
| `PAGE-021.14` | 433–523 | تابع | `food_ticket_np_trim_edges` | 0 | ⬜ |
| `PAGE-021.15` | 525–535 | تابع | `food_ticket_np_trim_top` | 0 | ⬜ |
| `PAGE-021.16` | 537–542 | تابع | `food_ticket_np_json_file` | 0 | ⬜ |
| `PAGE-021.17` | 544–602 | تابع | `food_ticket_np_write_json` | 3 | ⬜ |
| `PAGE-021.18` | 604–675 | تابع | `food_ticket_np_align_state` | 0 | ⬜ |
| `PAGE-021.19` | 677–712 | تابع | `food_ticket_np_reverse_feed` | 0 | ⬜ |
| `PAGE-021.20` | 714–722 | تابع | `food_ticket_print_align_info` | 0 | ⬜ |
| `PAGE-021.21` | 724–834 | تابع | `food_ticket_np_escpos` | 0 | ⬜ |
| `PAGE-021.22` | 836–854 | تابع | `food_ticket_np_payload` | 2 | ⬜ |
| `PAGE-021.23` | 856–896 | تابع | `food_ticket_np_send` | 3 | ⬜ |
| `PAGE-021.24` | 898–899 | ناحیه | `چاپ شبکه با چیدمان قالب فیش (همان ظاهر چاپ ویندوز)` | 0 | ⬜ |
| `PAGE-021.25` | 900–933 | تابع | `food_ticket_np_find_font_for` | 0 | ⬜ |
| `PAGE-021.26` | 935–945 | تابع | `food_ticket_np_metrics` | 0 | ⬜ |
| `PAGE-021.27` | 947–978 | تابع | `food_ticket_np_wrap` | 0 | ⬜ |
| `PAGE-021.28` | 980–999 | تابع | `food_ticket_np_layout` | 0 | ⬜ |
| `PAGE-021.29` | 1001–1143 | تابع | `food_ticket_np_render_layout` | 2 | ⬜ |
| `PAGE-021.30` | 1145–1188 | تابع | `food_ticket_np_target` | 4 | ⬜ |
| `PAGE-021.31` | 1190–1235 | تابع | `food_ticket_np_calibration_image` | 2 | ⬜ |
| `PAGE-021.32` | 1237–1266 | تابع | `food_ticket_np_calibration_print` | 2 | ⬜ |
| `PAGE-021.33` | 1268–1283 | تابع | `food_ticket_np_image_payload` | 2 | ⬜ |
| `PAGE-021.34` | 1285–1291 | تابع | `food_ticket_np_send_event` | 0 | ⬜ |
| `PAGE-021.35` | 1293–1298 | تابع | `food_ticket_np_send_text` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-022</b> — <code>food-ticket-order-source.php</code> (158 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-022.01` | 1–9 | ناحیه | `بلوک رویه‌ای 1-9` | 0 | ⬜ |
| `PAGE-022.02` | 10–88 | تابع | `food_order_internal_map` | 0 | ⬜ |
| `PAGE-022.03` | 90–113 | تابع | `food_order_internal_food_for_employee` | 0 | ⬜ |
| `PAGE-022.04` | 115–157 | تابع | `food_order_internal_items` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-023</b> — <code>food-ticket-release-notes.php</code> (47 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-023.01` | 1–14 | ناحیه | `بلوک رویه‌ای 1-14` | 0 | ⬜ |
| `PAGE-023.02` | 15–34 | تابع | `food_ticket_release_notes_all` | 0 | ⬜ |
| `PAGE-023.03` | 36–44 | تابع | `food_ticket_release_notes_current` | 0 | ⬜ |
| `PAGE-023.04` | 45–47 | ناحیه | `بلوک رویه‌ای 45-47` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-024</b> — <code>food-ticket-runtime.php</code> (1,386 خط، 35 آدرس، بازبینی‌شده 0/35)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-024.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-024.02` | 4–24 | تابع | `food_ticket_setting` | 0 | ⬜ |
| `PAGE-024.03` | 26–32 | تابع | `food_ticket_set_setting` | 6 | ⬜ |
| `PAGE-024.04` | 34–47 | تابع | `food_ticket_meal_windows` | 0 | ⬜ |
| `PAGE-024.05` | 49–64 | تابع | `food_ticket_active_meal` | 0 | ⬜ |
| `PAGE-024.06` | 66–94 | تابع | `food_ticket_template_default` | 0 | ⬜ |
| `PAGE-024.07` | 96–110 | تابع | `food_ticket_template` | 0 | ⬜ |
| `PAGE-024.08` | 112–124 | تابع | `food_ticket_save_template` | 1 | ⬜ |
| `PAGE-024.09` | 126–213 | تابع | `food_ticket_render_from_template` | 0 | ⬜ |
| `PAGE-024.10` | 215–253 | تابع | `food_ticket_print_log` | 1 | ⬜ |
| `PAGE-024.11` | 255–258 | تابع | `food_ticket_new_job_id` | 0 | ⬜ |
| `PAGE-024.12` | 260–293 | تابع | `food_ticket_enqueue_print` | 1 | ⬜ |
| `PAGE-024.13` | 295–298 | تابع | `food_ticket_can_delete_source_after_print` | 0 | ⬜ |
| `PAGE-024.14` | 300–331 | تابع | `food_ticket_prepare_queued_food` | 0 | ⬜ |
| `PAGE-024.15` | 333–346 | تابع | `food_ticket_apply_queued_food` | 1 | ⬜ |
| `PAGE-024.16` | 348–356 | تابع | `food_ticket_verify_queued_food` | 0 | ⬜ |
| `PAGE-024.17` | 358–550 | تابع | `food_ticket_print_group_run` | 7 | ⬜ |
| `PAGE-024.18` | 552–711 | تابع | `food_ticket_process_print_queue` | **11** | ⬜ |
| `PAGE-024.19` | 713–720 | تابع | `food_ticket_fa_digits` | 0 | ⬜ |
| `PAGE-024.20` | 722–795 | تابع | `food_ticket_graphic_rows` | 0 | ⬜ |
| `PAGE-024.21` | 797–831 | تابع | `food_ticket_template_logo_base64` | 2 | ⬜ |
| `PAGE-024.22` | 833–1031 | تابع | `food_ticket_send_windows_graphic` | **13** | ⬜ |
| `PAGE-024.23` | 1033–1087 | تابع | `food_ticket_send_raw_payload` | 0 | ⬜ |
| `PAGE-024.24` | 1088–1093 | ناحیه | `بلوک رویه‌ای 1088-1093` | 0 | ⬜ |
| `PAGE-024.25` | 1094–1146 | تابع | `food_ticket_table_has_column` | 4 | ⬜ |
| `PAGE-024.26` | 1148–1160 | تابع | `food_ticket_delivery_supported` | 0 | ⬜ |
| `PAGE-024.27` | 1162–1189 | تابع | `food_ticket_events_ensure_delivery` | 4 | ⬜ |
| `PAGE-024.28` | 1191–1209 | تابع | `food_ticket_source_delete_supported` | 0 | ⬜ |
| `PAGE-024.29` | 1211–1243 | تابع | `food_ticket_events_ensure_source_delete` | **14** | ⬜ |
| `PAGE-024.30` | 1244–1245 | ناحیه | `بلوک رویه‌ای 1244-1245` | 0 | ⬜ |
| `PAGE-024.31` | 1246–1259 | تابع | `food_ticket_mark_delivered_on_print` | 1 | ⬜ |
| `PAGE-024.32` | 1261–1297 | تابع | `food_ticket_deliver_event` | 5 | ⬜ |
| `PAGE-024.33` | 1299–1330 | تابع | `food_ticket_undeliver_event` | 5 | ⬜ |
| `PAGE-024.34` | 1332–1363 | تابع | `food_ticket_status_counts` | 5 | ⬜ |
| `PAGE-024.35` | 1365–1385 | تابع | `food_ticket_self_test_logic` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-025</b> — <code>food-ticket-selftest.php</code> (774 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-025.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 1 | ⬜ |
| `PAGE-025.02` | 20–697 | تابع | `food_ticket_selftest_run` | **19** | ⬜ |
| `PAGE-025.03` | 699–707 | تابع | `food_ticket_selftest_section` | 0 | ⬜ |
| `PAGE-025.04` | 709–721 | تابع | `food_ticket_selftest_check` | 0 | ⬜ |
| `PAGE-025.05` | 723–738 | تابع | `food_ticket_selftest_age_label` | 0 | ⬜ |
| `PAGE-025.06` | 740–771 | تابع | `food_ticket_selftest_text` | 0 | ⬜ |
| `PAGE-025.07` | 772–774 | ناحیه | `بلوک رویه‌ای 772-774` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-026</b> — <code>food-ticket-templates.php</code> (2,399 خط، 65 آدرس، بازبینی‌شده 0/65)</summary>

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
| `PAGE-026.35` | 1018–1314 | تابع | `food_ticket_tpl_model` | 7 | ⬜ |
| `PAGE-026.36` | 1316–1335 | تابع | `food_ticket_tpl_first_ink_mm` | 0 | ⬜ |
| `PAGE-026.37` | 1337–1357 | تابع | `food_ticket_tpl_last_ink_mm` | 0 | ⬜ |
| `PAGE-026.38` | 1359–1396 | تابع | `food_ticket_tpl_fit_bottom` | 0 | ⬜ |
| `PAGE-026.39` | 1398–1432 | تابع | `food_ticket_tpl_trim_top` | 0 | ⬜ |
| `PAGE-026.40` | 1434–1442 | تابع | `food_ticket_tpl_last_info` | 0 | ⬜ |
| `PAGE-026.41` | 1444–1448 | تابع | `food_ticket_tpl_log_suffix` | 0 | ⬜ |
| `PAGE-026.42` | 1450–1451 | ناحیه | `مسیر ۲: چاپ مستقیم شبکه (GD + ESC/POS)` | 0 | ⬜ |
| `PAGE-026.43` | 1452–1481 | تابع | `food_ticket_tpl_net_cfg` | 1 | ⬜ |
| `PAGE-026.44` | 1483–1495 | تابع | `food_ticket_tpl_head_dots` | 0 | ⬜ |
| `PAGE-026.45` | 1497–1538 | تابع | `food_ticket_tpl_dither` | 0 | ⬜ |
| `PAGE-026.46` | 1540–1699 | تابع | `food_ticket_tpl_render_gd` | 2 | ⬜ |
| `PAGE-026.47` | 1701–1714 | تابع | `food_ticket_tpl_binarize` | 0 | ⬜ |
| `PAGE-026.48` | 1716–1749 | تابع | `food_ticket_tpl_network_payload` | 0 | ⬜ |
| `PAGE-026.49` | 1751–1756 | تابع | `food_ticket_tpl_print_network` | 0 | ⬜ |
| `PAGE-026.50` | 1758–1777 | تابع | `food_ticket_tpl_preview` | 0 | ⬜ |
| `PAGE-026.51` | 1779–1780 | ناحیه | `مسیر ۱: صف چاپ ویندوز (GDI+ / PrintDocument)` | 0 | ⬜ |
| `PAGE-026.52` | 1781–1997 | تابع | `food_ticket_tpl_windows_script` | 6 | ⬜ |
| `PAGE-026.53` | 1999–2059 | تابع | `food_ticket_tpl_print_windows` | 7 | ⬜ |
| `PAGE-026.54` | 2061–2062 | ناحیه | `API طراح (food_api=templates*)` | 0 | ⬜ |
| `PAGE-026.55` | 2063–2070 | تابع | `food_ticket_tpl_asset_url` | 0 | ⬜ |
| `PAGE-026.56` | 2072–2084 | تابع | `food_ticket_tpl_brand_payload` | 0 | ⬜ |
| `PAGE-026.57` | 2086–2151 | تابع | `food_ticket_tpl_group_ensure_title` | 0 | ⬜ |
| `PAGE-026.58` | 2153–2175 | تابع | `food_ticket_tpl_group_template` | 0 | ⬜ |
| `PAGE-026.59` | 2177–2193 | تابع | `food_ticket_tpl_list_payload` | 0 | ⬜ |
| `PAGE-026.60` | 2195–2202 | تابع | `food_ticket_group_double_copy_enabled` | 0 | ⬜ |
| `PAGE-026.61` | 2204–2211 | تابع | `food_ticket_group_print_mode` | 0 | ⬜ |
| `PAGE-026.62` | 2213–2231 | تابع | `food_ticket_tpl_sample_group_event` | 0 | ⬜ |
| `PAGE-026.63` | 2233–2248 | تابع | `food_ticket_tpl_sample_event` | 0 | ⬜ |
| `PAGE-026.64` | 2250–2290 | تابع | `food_ticket_tpl_handle_logo_upload` | **9** | ⬜ |
| `PAGE-026.65` | 2292–2398 | تابع | `food_ticket_templates_api` | 1 | ⬜ |

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
<summary><b>PAGE-029</b> — <code>food-ticket.php</code> (6,041 خط، 139 آدرس، بازبینی‌شده 0/139)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-029.01` | 1–8 | ناحیه | `بلوک رویه‌ای 1-8` | 0 | ⬜ |
| `PAGE-029.02` | 9–9 | ناحیه | `پنجرهٔ کاری باید پیش از food_ticket_config() در دس` | 0 | ⬜ |
| `PAGE-029.03` | 10–40 | ناحیه | `به آرایهٔ تنظیمات اضافه شوند (مهاجرت ۰۳۱).` | 0 | ⬜ |
| `PAGE-029.04` | 41–44 | ناحیه | `آزمون جامع worker/چاپ/مدیریت — همان منطقی که ابزار` | 0 | ⬜ |
| `PAGE-029.05` | 45–62 | ناحیه | `«تغییرات این به‌روزرسانی» داخل خود پنل (مستقل از i` | 0 | ⬜ |
| `PAGE-029.06` | 63–66 | تابع | `food_ticket_panel_version` | 0 | ⬜ |
| `PAGE-029.07` | 68–71 | تابع | `food_ticket_is_primary_admin` | 0 | ⬜ |
| `PAGE-029.08` | 73–76 | تابع | `food_ticket_is_support_manager` | 0 | ⬜ |
| `PAGE-029.09` | 78–84 | تابع | `food_ticket_is_allowed` | 0 | ⬜ |
| `PAGE-029.10` | 86–129 | تابع | `food_ticket_route_edit_permission` | 1 | ⬜ |
| `PAGE-029.11` | 131–135 | تابع | `food_ticket_route_edit_always` | 1 | ⬜ |
| `PAGE-029.12` | 137–292 | تابع | `food_ticket_route_allowed` | 2 | ⬜ |
| `PAGE-029.13` | 294–302 | تابع | `require_food_ticket_access` | 0 | ⬜ |
| `PAGE-029.14` | 304–315 | تابع | `food_ticket_web_url` | 0 | ⬜ |
| `PAGE-029.15` | 317–325 | تابع | `food_ticket_windows_service_base_url` | 0 | ⬜ |
| `PAGE-029.16` | 327–340 | تابع | `food_ticket_sso_token` | 0 | ⬜ |
| `PAGE-029.17` | 342–364 | تابع | `food_ticket_sso_consume_jti` | 7 | ⬜ |
| `PAGE-029.18` | 366–377 | تابع | `food_ticket_main_url` | 0 | ⬜ |
| `PAGE-029.19` | 379–396 | تابع | `food_ticket_brand_params` | 0 | ⬜ |
| `PAGE-029.20` | 398–413 | تابع | `food_ticket_resolve_path` | 0 | ⬜ |
| `PAGE-029.21` | 415–425 | تابع | `food_ticket_attendance_default_path` | 0 | ⬜ |
| `PAGE-029.22` | 427–430 | تابع | `food_ticket_attendance_factory_password` | 0 | ⬜ |
| `PAGE-029.23` | 432–491 | تابع | `food_ticket_config` | 5 | ⬜ |
| `PAGE-029.24` | 494–506 | تابع | `food_ticket_normalize_printer_mode` | 0 | ⬜ |
| `PAGE-029.25` | 508–525 | تابع | `food_ticket_normalize_printer_host` | 0 | ⬜ |
| `PAGE-029.26` | 527–537 | تابع | `food_ticket_printer_label` | 0 | ⬜ |
| `PAGE-029.27` | 539–560 | تابع | `food_ticket_ensure_printer_mode_column` | 4 | ⬜ |
| `PAGE-029.28` | 562–570 | تابع | `food_ticket_installed` | 0 | ⬜ |
| `PAGE-029.29` | 572–575 | تابع | `food_ticket_key` | 0 | ⬜ |
| `PAGE-029.30` | 577–586 | تابع | `food_ticket_encrypt` | 0 | ⬜ |
| `PAGE-029.31` | 588–599 | تابع | `food_ticket_decrypt` | 0 | ⬜ |
| `PAGE-029.32` | 601–649 | تابع | `food_ticket_attendance_password` | 4 | ⬜ |
| `PAGE-029.33` | 651–667 | تابع | `food_ticket_attendance_password_source` | 4 | ⬜ |
| `PAGE-029.34` | 669–675 | تابع | `food_ticket_ident` | 0 | ⬜ |
| `PAGE-029.35` | 677–697 | تابع | `food_ticket_odbc_pool` | 0 | ⬜ |
| `PAGE-029.36` | 699–710 | تابع | `food_ticket_odbc_release` | 1 | ⬜ |
| `PAGE-029.37` | 712–741 | تابع | `food_ticket_odbc_pool_handle` | 0 | ⬜ |
| `PAGE-029.38` | 743–799 | تابع | `food_ticket_odbc` | 1 | ⬜ |
| `PAGE-029.39` | 801–804 | تابع | `food_ticket_is_ps_access` | 0 | ⬜ |
| `PAGE-029.40` | 806–819 | تابع | `food_ticket_access_close` | 1 | ⬜ |
| `PAGE-029.41` | 821–1030 | تابع | `food_ticket_access_rows_powershell` | **13** | ⬜ |
| `PAGE-029.42` | 1033–1037 | تابع | `food_ticket_access_delete_powershell` | 0 | ⬜ |
| `PAGE-029.43` | 1039–1155 | تابع | `food_ticket_access_delete_powershell_ex` | **13** | ⬜ |
| `PAGE-029.44` | 1157–1160 | تابع | `food_ticket_normalize_column` | 0 | ⬜ |
| `PAGE-029.45` | 1162–1171 | تابع | `food_ticket_row_value` | 0 | ⬜ |
| `PAGE-029.46` | 1174–1233 | تابع | `food_ticket_access_rows` | **8** | ⬜ |
| `PAGE-029.47` | 1235–1240 | تابع | `food_ticket_normalize_digits` | 0 | ⬜ |
| `PAGE-029.48` | 1242–1273 | تابع | `food_ticket_iso_date_input` | 0 | ⬜ |
| `PAGE-029.49` | 1275–1329 | تابع | `food_ticket_jalali_to_gregorian` | 0 | ⬜ |
| `PAGE-029.50` | 1331–1430 | تابع | `food_ticket_parse_date` | 0 | ⬜ |
| `PAGE-029.51` | 1432–1481 | تابع | `food_ticket_parse_time` | 0 | ⬜ |
| `PAGE-029.52` | 1483–1537 | تابع | `food_ticket_source_key` | 0 | ⬜ |
| `PAGE-029.53` | 1539–1565 | تابع | `food_ticket_code` | 0 | ⬜ |
| `PAGE-029.54` | 1567–1570 | تابع | `food_ticket_digits` | 0 | ⬜ |
| `PAGE-029.55` | 1572–1587 | تابع | `food_ticket_find_user` | 0 | ⬜ |
| `PAGE-029.56` | 1590–1614 | تابع | `food_ticket_guest_cards` | 4 | ⬜ |
| `PAGE-029.57` | 1616–1627 | تابع | `food_ticket_card_key` | 0 | ⬜ |
| `PAGE-029.58` | 1629–1646 | تابع | `food_ticket_card_equals` | 0 | ⬜ |
| `PAGE-029.59` | 1648–1674 | تابع | `food_ticket_guest_card` | 4 | ⬜ |
| `PAGE-029.60` | 1676–1679 | تابع | `food_ticket_is_guest` | 0 | ⬜ |
| `PAGE-029.61` | 1681–1686 | تابع | `food_ticket_existing_source` | 0 | ⬜ |
| `PAGE-029.62` | 1688–1693 | تابع | `food_ticket_existing_ticket` | 0 | ⬜ |
| `PAGE-029.63` | 1695–1719 | تابع | `food_ticket_normalize_event_type` | 1 | ⬜ |
| `PAGE-029.64` | 1721–1729 | تابع | `food_ticket_normalize_print_status` | 0 | ⬜ |
| `PAGE-029.65` | 1731–1748 | تابع | `food_ticket_event_meal_type_supported` | 0 | ⬜ |
| `PAGE-029.66` | 1750–1779 | تابع | `food_ticket_add_event` | 6 | ⬜ |
| `PAGE-029.67` | 1781–1790 | تابع | `food_ticket_update_event` | 1 | ⬜ |
| `PAGE-029.68` | 1792–1891 | تابع | `food_ticket_source_delete_where` | 0 | ⬜ |
| `PAGE-029.69` | 1893–1933 | تابع | `food_ticket_source_primary_key` | 0 | ⬜ |
| `PAGE-029.70` | 1935–1957 | تابع | `food_ticket_source_type_kind` | 0 | ⬜ |
| `PAGE-029.71` | 1959–1974 | تابع | `food_ticket_source_is_temporal` | 0 | ⬜ |
| `PAGE-029.72` | 1976–2002 | تابع | `food_ticket_source_column_kind` | 0 | ⬜ |
| `PAGE-029.73` | 2004–2029 | تابع | `food_ticket_source_seconds_of_day` | 0 | ⬜ |
| `PAGE-029.74` | 2031–2053 | تابع | `food_ticket_source_time_literal_sod` | 0 | ⬜ |
| `PAGE-029.75` | 2055–2072 | تابع | `food_ticket_source_time_between` | 0 | ⬜ |
| `PAGE-029.76` | 2074–2103 | تابع | `food_ticket_source_time_where` | 0 | ⬜ |
| `PAGE-029.77` | 2105–2120 | تابع | `food_ticket_source_time_literal` | 0 | ⬜ |
| `PAGE-029.78` | 2122–2137 | تابع | `food_ticket_source_sql_literal` | 0 | ⬜ |
| `PAGE-029.79` | 2139–2154 | تابع | `food_ticket_source_column_sql_literal` | 0 | ⬜ |
| `PAGE-029.80` | 2156–2185 | تابع | `food_ticket_source_delete_candidates` | 0 | ⬜ |
| `PAGE-029.81` | 2187–2259 | تابع | `food_ticket_source_minimal_where` | 0 | ⬜ |
| `PAGE-029.82` | 2261–2355 | تابع | `food_ticket_delete_source` | **9** | ⬜ |
| `PAGE-029.83` | 2357–2387 | تابع | `food_ticket_mark_source_delete` | 6 | ⬜ |
| `PAGE-029.84` | 2389–2410 | تابع | `food_ticket_text` | 0 | ⬜ |
| `PAGE-029.85` | 2411–2416 | ناحیه | `بلوک رویه‌ای 2411-2416` | 0 | ⬜ |
| `PAGE-029.86` | 2417–2464 | تابع | `food_ticket_list_windows_printers` | 7 | ⬜ |
| `PAGE-029.87` | 2466–2484 | تابع | `food_ticket_send_to_printer` | 0 | ⬜ |
| `PAGE-029.88` | 2486–2541 | تابع | `food_ticket_send_windows` | 7 | ⬜ |
| `PAGE-029.89` | 2543–2566 | تابع | `food_ticket_print_event` | 1 | ⬜ |
| `PAGE-029.90` | 2568–2575 | تابع | `food_ticket_process_batch` | 0 | ⬜ |
| `PAGE-029.91` | 2577–2779 | تابع | `food_ticket_cleanup_source_rows` | **14** | ⬜ |
| `PAGE-029.92` | 2781–2833 | تابع | `food_ticket_retry_failed` | **13** | ⬜ |
| `PAGE-029.93` | 2835–2850 | تابع | `food_ticket_worker_heartbeat` | 1 | ⬜ |
| `PAGE-029.94` | 2852–2888 | تابع | `food_ticket_test_connections` | 0 | ⬜ |
| `PAGE-029.95` | 2890–2957 | تابع | `food_ticket_save_config` | 6 | ⬜ |
| `PAGE-029.96` | 2959–2982 | تابع | `food_ticket_handle_post` | 0 | ⬜ |
| `PAGE-029.97` | 2984–2992 | تابع | `food_ticket_label` | 0 | ⬜ |
| `PAGE-029.98` | 2994–3144 | تابع | `food_ticket_cp1256_to_utf8` | 6 | ⬜ |
| `PAGE-029.99` | 3146–3192 | تابع | `food_ticket_utf8` | 2 | ⬜ |
| `PAGE-029.100` | 3194–3215 | تابع | `food_ticket_api_json` | 3 | ⬜ |
| `PAGE-029.101` | 3217–3225 | تابع | `food_ticket_api_body` | 0 | ⬜ |
| `PAGE-029.102` | 3227–3254 | تابع | `food_ticket_api_printer_state` | 0 | ⬜ |
| `PAGE-029.103` | 3256–3302 | تابع | `food_ticket_api_print_diag` | 2 | ⬜ |
| `PAGE-029.104` | 3304–3406 | تابع | `food_ticket_api_config` | 0 | ⬜ |
| `PAGE-029.105` | 3407–3412 | ناحیه | `بلوک رویه‌ای 3407-3412` | 0 | ⬜ |
| `PAGE-029.106` | 3413–3477 | تابع | `food_ticket_api_guest_requests_report` | 0 | ⬜ |
| `PAGE-029.107` | 3478–3483 | ناحیه | `بلوک رویه‌ای 3478-3483` | 0 | ⬜ |
| `PAGE-029.108` | 3484–3495 | تابع | `food_ticket_aggregate_last_name` | 0 | ⬜ |
| `PAGE-029.109` | 3496–3505 | تابع | `food_ticket_aggregate_sort_people` | 0 | ⬜ |
| `PAGE-029.110` | 3506–3659 | تابع | `food_ticket_aggregate_diagnostics` | 6 | ⬜ |
| `PAGE-029.111` | 3661–3838 | تابع | `food_ticket_api_aggregate_report` | 6 | ⬜ |
| `PAGE-029.112` | 3840–3855 | تابع | `food_ticket_aggregate_columns` | 0 | ⬜ |
| `PAGE-029.113` | 3857–3914 | تابع | `food_ticket_aggregate_export` | 5 | ⬜ |
| `PAGE-029.114` | 3916–3937 | تابع | `food_ticket_aggregate_row` | 0 | ⬜ |
| `PAGE-029.115` | 3939–4046 | تابع | `food_ticket_api_absent` | 0 | ⬜ |
| `PAGE-029.116` | 4048–4157 | تابع | `food_ticket_api_monitoring` | 5 | ⬜ |
| `PAGE-029.117` | 4160–4172 | تابع | `food_ticket_date_keys_for_iso` | 0 | ⬜ |
| `PAGE-029.118` | 4175–4201 | تابع | `food_ticket_order_date_key` | 0 | ⬜ |
| `PAGE-029.119` | 4203–4215 | تابع | `food_ticket_api_orders` | 0 | ⬜ |
| `PAGE-029.120` | 4217–4321 | تابع | `food_ticket_ensure_employee_local_accounts` | 6 | ⬜ |
| `PAGE-029.121` | 4323–4344 | تابع | `food_ticket_api_employees` | 0 | ⬜ |
| `PAGE-029.122` | 4346–4349 | تابع | `food_ticket_normalize_guest_card` | 0 | ⬜ |
| `PAGE-029.123` | 4351–4370 | تابع | `food_ticket_api_guest_cards` | 1 | ⬜ |
| `PAGE-029.124` | 4372–4379 | تابع | `food_ticket_sync_guest_card_config` | 1 | ⬜ |
| `PAGE-029.125` | 4381–4455 | تابع | `food_ticket_api_save_employee` | 1 | ⬜ |
| `PAGE-029.126` | 4457–4478 | تابع | `food_ticket_api_save_guest_card` | 1 | ⬜ |
| `PAGE-029.127` | 4480–4503 | تابع | `food_ticket_api_delete_guest_card` | 1 | ⬜ |
| `PAGE-029.128` | 4505–4515 | تابع | `food_ticket_save_guest_cap_mode` | 0 | ⬜ |
| `PAGE-029.129` | 4517–4673 | تابع | `food_ticket_api_save_config` | 1 | ⬜ |
| `PAGE-029.130` | 4674–4680 | ناحیه | `بلوک رویه‌ای 4674-4680` | 0 | ⬜ |
| `PAGE-029.131` | 4681–4767 | تابع | `food_ticket_browse_list` | 3 | ⬜ |
| `PAGE-029.132` | 4769–4793 | تابع | `food_ticket_php_native_browse` | 2 | ⬜ |
| `PAGE-029.133` | 4795–4802 | تابع | `food_ticket_windows_browse` | 0 | ⬜ |
| `PAGE-029.134` | 4805–4873 | تابع | `food_ticket_api_health` | 0 | ⬜ |
| `PAGE-029.135` | 4875–4967 | تابع | `food_ticket_api_self_test` | 0 | ⬜ |
| `PAGE-029.136` | 4969–5037 | تابع | `food_ticket_api_upload_access_file` | **14** | ⬜ |
| `PAGE-029.137` | 5039–5844 | تابع | `food_ticket_api_handle` | **14** | ⬜ |
| `PAGE-029.138` | 5846–5860 | تابع | `food_ticket_render_safe_replace` | 0 | ⬜ |
| `PAGE-029.139` | 5862–6040 | تابع | `food_ticket_render_page` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-030</b> — <code>global-search.php</code> (123 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-030.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `PAGE-030.02` | 16–29 | تابع | `global_search_normalize` | 0 | ⬜ |
| `PAGE-030.03` | 31–74 | تابع | `global_search_catalog` | 0 | ⬜ |
| `PAGE-030.04` | 76–122 | تابع | `global_search_sections` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-031</b> — <code>help-guide.php</code> (723 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-031.01` | 1–723 | ناحیه | `بلوک رویه‌ای 1-723` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-032</b> — <code>holiday-board.php</code> (76 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-032.01` | 1–24 | ناحیه | `بلوک رویه‌ای 1-24` | 0 | ⬜ |
| `PAGE-032.02` | 25–51 | تابع | `holiday_board_work_hours_form` | 0 | ⬜ |
| `PAGE-032.03` | 53–75 | تابع | `holiday_board_render` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-033</b> — <code>index.php</code> (2,560 خط، 77 آدرس، بازبینی‌شده 0/77)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-033.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `PAGE-033.02` | 6–6 | ناحیه | `Keep legacy attachment URLs, but execute the read ` | 0 | ⬜ |
| `PAGE-033.03` | 7–22 | ناحیه | `endpoint before loading unrelated legacy modules o` | 2 | ⬜ |
| `PAGE-033.04` | 23–23 | ناحیه | `Early compatibility bridge: serve Request Center l` | 0 | ⬜ |
| `PAGE-033.05` | 24–59 | ناحیه | `independent entry before loading legacy modules or` | 3 | ⬜ |
| `PAGE-033.06` | 60–60 | ناحیه | `تبدیل رکوردهای قدیمی غذا فقط هنگام بارگذاری فهرست ` | 0 | ⬜ |
| `PAGE-033.07` | 61–66 | ناحیه | `نه در هر درخواست و نه قبل از نمایش فرم ورود.` | 2 | ⬜ |
| `PAGE-033.08` | 67–71 | تابع | `csv_cell` | 0 | ⬜ |
| `PAGE-033.09` | 73–76 | تابع | `excel_cell` | 0 | ⬜ |
| `PAGE-033.10` | 78–140 | تابع | `excel_download` | 4 | ⬜ |
| `PAGE-033.11` | 142–150 | تابع | `log_sort_order` | 0 | ⬜ |
| `PAGE-033.12` | 152–171 | تابع | `log_sort_header_row` | 0 | ⬜ |
| `PAGE-033.13` | 173–192 | تابع | `log_bounded_date_range` | 0 | ⬜ |
| `PAGE-033.14` | 194–259 | تابع | `activity_log_filters` | 2 | ⬜ |
| `PAGE-033.15` | 261–281 | تابع | `activity_log_meta_lines` | 0 | ⬜ |
| `PAGE-033.16` | 283–297 | تابع | `imported_calendar_date` | 0 | ⬜ |
| `PAGE-033.17` | 299–302 | تابع | `calendar_unescape` | 0 | ⬜ |
| `PAGE-033.18` | 304–375 | تابع | `parse_calendar_import` | 0 | ⬜ |
| `PAGE-033.19` | 376–378 | ناحیه | `بلوک رویه‌ای 376-378` | 0 | ⬜ |
| `PAGE-033.20` | 379–402 | تابع | `store_profile_photo` | 1 | ⬜ |
| `PAGE-033.21` | 404–416 | تابع | `valid_iranian_national_code` | 0 | ⬜ |
| `PAGE-033.22` | 417–425 | ناحیه | `بلوک رویه‌ای 417-425` | 2 | ⬜ |
| `PAGE-033.23` | 426–445 | ناحیه | `ناحیهٔ ۱: خروج از حساب (قبل از هر چیز)` | 2 | ⬜ |
| `PAGE-033.24` | 446–447 | ناحیه | `ناحیهٔ ۲: تعیین صفحهٔ جاری و پیش‌شرط‌های آن` | 2 | ⬜ |
| `PAGE-033.25` | 448–455 | ناحیه | `۱.۵۰: کاربر محلی/بازرسی به سفارش غذا هدایت می‌شود؛` | 2 | ⬜ |
| `PAGE-033.26` | 456–466 | ناحیه | `مسیر: must_change_password\|change-password` | 2 | ⬜ |
| `PAGE-033.27` | 467–480 | ناحیه | `مسیر: REQUEST_METHOD\|POST\|organization` | 0 | ⬜ |
| `PAGE-033.28` | 481–494 | ناحیه | `مسیر: food-ticket\|food_api` | 2 | ⬜ |
| `PAGE-033.29` | 495–506 | ناحیه | `مسیر: food-order\|food_api` | 2 | ⬜ |
| `PAGE-033.30` | 507–634 | ناحیه | `مسیر: traffic-control\|traffic_api` | **9** | ⬜ |
| `PAGE-033.31` | 635–727 | ناحیه | `خروجی XLSX از نمای مرتب‌شدهٔ لیست در مرورگر (فقط د` | 7 | ⬜ |
| `PAGE-033.32` | 728–733 | ناحیه | `مسیر: food-personnel-link` | 0 | ⬜ |
| `PAGE-033.33` | 734–809 | ناحیه | `ناحیهٔ ۴: پردازش همهٔ اکشن‌های POST (74 اکشن، گروه` | 2 | ⬜ |
| `PAGE-033.34` | 810–862 | ناحیه | `مسیر: chatbot-api` | 0 | ⬜ |
| `PAGE-033.35` | 863–863 | ناحیه | `۱.۳۷.۷ — دروازهٔ «سامانه مدیریت غذا» با فهرست کدها` | 0 | ⬜ |
| `PAGE-033.36` | 864–864 | ناحیه | `food_ticket_is_allowed() (هر کد food.*) نمایش داده` | 0 | ⬜ |
| `PAGE-033.37` | 865–865 | ناحیه | `می‌شد؛ یعنی نقشی که مثلاً فقط food.orders داشت، لی` | 0 | ⬜ |
| `PAGE-033.38` | 866–873 | ناحیه | `مسیر: food-ticket\|food_ticket_is_allowed\|food.mo` | 0 | ⬜ |
| `PAGE-033.39` | 874–881 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `PAGE-033.40` | 882–890 | ناحیه | `مسیر: settings\|settings.general\|settings.domain` | 0 | ⬜ |
| `PAGE-033.41` | 891–893 | ناحیه | `مسیر: change-password` | 0 | ⬜ |
| `PAGE-033.42` | 894–896 | ناحیه | `مسیر: local-accounts` | 0 | ⬜ |
| `PAGE-033.43` | 897–914 | ناحیه | `مسیر: profile` | 0 | ⬜ |
| `PAGE-033.44` | 915–918 | ناحیه | `مسیر: governance` | 0 | ⬜ |
| `PAGE-033.45` | 919–923 | ناحیه | `مسیر: food-ticket` | 0 | ⬜ |
| `PAGE-033.46` | 924–931 | ناحیه | `مسیر: food-order\|foodorder.self` | 0 | ⬜ |
| `PAGE-033.47` | 932–935 | ناحیه | `مسیر: food-order` | 0 | ⬜ |
| `PAGE-033.48` | 936–939 | ناحیه | `مسیر: cd-dvd` | 0 | ⬜ |
| `PAGE-033.49` | 940–943 | ناحیه | `مسیر: cd-dvd-history` | 0 | ⬜ |
| `PAGE-033.50` | 944–946 | ناحیه | `مسیر: traffic-control` | 0 | ⬜ |
| `PAGE-033.51` | 947–950 | ناحیه | `مسیر: traffic-control-print` | 2 | ⬜ |
| `PAGE-033.52` | 951–969 | ناحیه | `مسیر: help` | 2 | ⬜ |
| `PAGE-033.53` | 970–979 | ناحیه | `مسیر: release-notes` | 0 | ⬜ |
| `PAGE-033.54` | 980–987 | ناحیه | `مسیر: notifications\|notif.view` | 0 | ⬜ |
| `PAGE-033.55` | 988–1005 | ناحیه | `مسیر: notifications` | 5 | ⬜ |
| `PAGE-033.56` | 1006–1014 | ناحیه | `مسیر: audit` | 0 | ⬜ |
| `PAGE-033.57` | 1015–1018 | ناحیه | `مسیر: logging-dashboard` | 0 | ⬜ |
| `PAGE-033.58` | 1019–1022 | ناحیه | `مسیر: system-health` | 0 | ⬜ |
| `PAGE-033.59` | 1023–1028 | ناحیه | `مسیر: logs` | 0 | ⬜ |
| `PAGE-033.60` | 1029–1171 | ناحیه | `مسیر: activity-log` | **8** | ⬜ |
| `PAGE-033.61` | 1172–1210 | ناحیه | `مسیر: backup` | 6 | ⬜ |
| `PAGE-033.62` | 1211–1218 | ناحیه | `مسیر: search\|search.global` | 0 | ⬜ |
| `PAGE-033.63` | 1219–1314 | ناحیه | `مسیر: search` | 7 | ⬜ |
| `PAGE-033.64` | 1315–1417 | ناحیه | `مسیر: chatbot-admin` | 5 | ⬜ |
| `PAGE-033.65` | 1418–1428 | ناحیه | `مسیر: holidays` | 0 | ⬜ |
| `PAGE-033.66` | 1429–1525 | ناحیه | `مسیر: inventory` | 7 | ⬜ |
| `PAGE-033.67` | 1526–1557 | ناحیه | `مسیر: inventory-diagnostics` | 2 | ⬜ |
| `PAGE-033.68` | 1558–1575 | ناحیه | `مسیر: asset-history` | 7 | ⬜ |
| `PAGE-033.69` | 1576–1694 | ناحیه | `مسیر: domain-scan` | 2 | ⬜ |
| `PAGE-033.70` | 1695–1738 | ناحیه | `مسیر: assets` | 7 | ⬜ |
| `PAGE-033.71` | 1739–1765 | ناحیه | `مسیر: asset` | 7 | ⬜ |
| `PAGE-033.72` | 1766–1956 | ناحیه | `مسیر: organization` | 3 | ⬜ |
| `PAGE-033.73` | 1957–2459 | ناحیه | `مسیر: settings` | **16** | ⬜ |
| `PAGE-033.74` | 2460–2481 | ناحیه | `مسیر: logs` | 2 | ⬜ |
| `PAGE-033.75` | 2482–2486 | ناحیه | `مسیر: dashboard` | 0 | ⬜ |
| `PAGE-033.76` | 2487–2487 | ناحیه | `بازگردانی کارت‌ها و فهرست درخواست‌ها در داشبورد (ت` | 0 | ⬜ |
| `PAGE-033.77` | 2488–2560 | ناحیه | `موج استقلال (2066cb0) این بلوک را حذف کرده بود؛ با` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-034</b> — <code>install.php</code> (368 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-034.01` | 1–23 | ناحیه | `بلوک رویه‌ای 1-23` | 2 | ⬜ |
| `PAGE-034.02` | 24–27 | تابع | `ih` | 0 | ⬜ |
| `PAGE-034.03` | 29–35 | تابع | `install_csrf` | 0 | ⬜ |
| `PAGE-034.04` | 37–47 | تابع | `install_layout` | 0 | ⬜ |
| `PAGE-034.05` | 49–52 | تابع | `install_error` | 0 | ⬜ |
| `PAGE-034.06` | 54–59 | تابع | `take_install_error` | 0 | ⬜ |
| `PAGE-034.07` | 61–66 | تابع | `mysql_from_session` | 0 | ⬜ |
| `PAGE-034.08` | 68–87 | تابع | `ldap_probe` | 2 | ⬜ |
| `PAGE-034.09` | 88–97 | ناحیه | `بلوک رویه‌ای 88-97` | 2 | ⬜ |
| `PAGE-034.10` | 98–126 | ناحیه | `شرط مسیریابی` | 3 | ⬜ |
| `PAGE-034.11` | 127–137 | ناحیه | `مسیر: test_db` | 0 | ⬜ |
| `PAGE-034.12` | 138–151 | ناحیه | `شرط مسیریابی` | 2 | ⬜ |
| `PAGE-034.13` | 152–351 | ناحیه | `مسیر: test_ldap` | **10** | ⬜ |
| `PAGE-034.14` | 352–360 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `PAGE-034.15` | 361–368 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-035</b> — <code>inventory.php</code> (905 خط، 34 آدرس، بازبینی‌شده 0/34)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-035.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |
| `PAGE-035.02` | 7–14 | تابع | `inventory_command` | 5 | ⬜ |
| `PAGE-035.03` | 16–27 | تابع | `inventory_json` | 0 | ⬜ |
| `PAGE-035.04` | 29–33 | تابع | `inventory_last_error` | 0 | ⬜ |
| `PAGE-035.05` | 35–41 | تابع | `inventory_set_error` | 0 | ⬜ |
| `PAGE-035.06` | 43–46 | تابع | `inventory_server_script_path` | 0 | ⬜ |
| `PAGE-035.07` | 48–56 | تابع | `inventory_local_hostnames` | 0 | ⬜ |
| `PAGE-035.08` | 58–62 | تابع | `inventory_server_asset_tag` | 0 | ⬜ |
| `PAGE-035.09` | 64–72 | تابع | `inventory_asset_is_local` | 0 | ⬜ |
| `PAGE-035.10` | 74–96 | تابع | `inventory_run_powershell_file` | 0 | ⬜ |
| `PAGE-035.11` | 98–127 | تابع | `inventory_diagnostics` | 0 | ⬜ |
| `PAGE-035.12` | 129–137 | تابع | `inventory_value` | 0 | ⬜ |
| `PAGE-035.13` | 139–145 | تابع | `inventory_rows` | 0 | ⬜ |
| `PAGE-035.14` | 147–211 | تابع | `inventory_collection_status_keys` | 0 | ⬜ |
| `PAGE-035.15` | 213–225 | تابع | `inventory_collection_category_succeeded` | 0 | ⬜ |
| `PAGE-035.16` | 227–263 | تابع | `inventory_merge_missing_values` | 0 | ⬜ |
| `PAGE-035.17` | 265–283 | تابع | `inventory_asset_insert_params` | 1 | ⬜ |
| `PAGE-035.18` | 285–313 | تابع | `inventory_saved_asset_snapshot` | 0 | ⬜ |
| `PAGE-035.19` | 315–359 | تابع | `inventory_apply_collection_status_metadata` | 0 | ⬜ |
| `PAGE-035.20` | 361–369 | تابع | `inventory_asset_exists` | 0 | ⬜ |
| `PAGE-035.21` | 371–407 | تابع | `sync_inventory_rows` | 1 | ⬜ |
| `PAGE-035.22` | 409–419 | تابع | `inventory_hardware_score` | 0 | ⬜ |
| `PAGE-035.23` | 421–435 | تابع | `inventory_collect_server_reliable` | 1 | ⬜ |
| `PAGE-035.24` | 437–449 | تابع | `inventory_server_asset_id` | 0 | ⬜ |
| `PAGE-035.25` | 451–469 | تابع | `inventory_server_profile_is_empty` | 0 | ⬜ |
| `PAGE-035.26` | 471–490 | تابع | `inventory_auto_extract_server` | 0 | ⬜ |
| `PAGE-035.27` | 492–527 | تابع | `inventory_base_inventory` | 0 | ⬜ |
| `PAGE-035.28` | 529–660 | تابع | `inventory_apply_powershell_data` | 1 | ⬜ |
| `PAGE-035.29` | 662–678 | تابع | `collect_server_inventory` | 0 | ⬜ |
| `PAGE-035.30` | 680–752 | تابع | `inventory_upsert_server_asset` | 6 | ⬜ |
| `PAGE-035.31` | 754–768 | تابع | `inventory_collect_initial_asset` | 0 | ⬜ |
| `PAGE-035.32` | 770–822 | تابع | `inventory_reverse_name` | 7 | ⬜ |
| `PAGE-035.33` | 824–898 | تابع | `inventory_collect_client_asset` | 1 | ⬜ |
| `PAGE-035.34` | 900–904 | تابع | `inventory_client_ip` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-036</b> — <code>local-accounts.php</code> (243 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-036.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `PAGE-036.02` | 19–23 | تابع | `lac_default_hash` | 0 | ⬜ |
| `PAGE-036.03` | 25–73 | تابع | `lac_ensure_schema` | 4 | ⬜ |
| `PAGE-036.04` | 75–79 | تابع | `lac_is_local_role` | 0 | ⬜ |
| `PAGE-036.05` | 81–136 | تابع | `lac_change_password_request` | 2 | ⬜ |
| `PAGE-036.06` | 138–242 | تابع | `lac_admin_request` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-037</b> — <code>logging-dashboard.php</code> (187 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-037.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-037.02` | 4–95 | تابع | `logging_dashboard_fetch` | 1 | ⬜ |
| `PAGE-037.03` | 97–112 | تابع | `logging_dashboard_svg_trend` | 0 | ⬜ |
| `PAGE-037.04` | 114–186 | تابع | `logging_dashboard_render` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-038</b> — <code>modules/request-center/Actions/admin-actions.php</code> (384 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-038.01` | 1–9 | ناحیه | `بلوک رویه‌ای 1-9` | 0 | ⬜ |
| `PAGE-038.02` | 10–66 | ناحیه | `مسیر: save_rc_work_hours` | 2 | ⬜ |
| `PAGE-038.03` | 67–147 | ناحیه | `مسیر: create_ola_policy` | 2 | ⬜ |
| `PAGE-038.04` | 148–244 | ناحیه | `مسیر: update_ola_policy` | 2 | ⬜ |
| `PAGE-038.05` | 245–306 | ناحیه | `مسیر: save_rc_capacity` | 2 | ⬜ |
| `PAGE-038.06` | 307–350 | ناحیه | `مسیر: rc_holiday_save` | 0 | ⬜ |
| `PAGE-038.07` | 351–384 | ناحیه | `مسیر: rc_holiday_delete` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-039</b> — <code>modules/request-center/Actions/appointment-actions.php</code> (440 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-039.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `PAGE-039.02` | 18–25 | ناحیه | `محافظ: این فایل فقط از طریق شاخهٔ تقویم ماژول بارگ` | 0 | ⬜ |
| `PAGE-039.03` | 26–47 | ناحیه | `مدیر واحدِ صرف هیچ دسترسی به تقویم/برنامهٔ رسیدگی ` | 0 | ⬜ |
| `PAGE-039.04` | 48–48 | ناحیه | `مقصد بازگشت پس از عملیات: کلاینت می‌تواند نماهنگ ش` | 0 | ⬜ |
| `PAGE-039.05` | 49–297 | ناحیه | `ماه و روز جاری را ارسال کند تا کاربر دقیقاً به هما` | 7 | ⬜ |
| `PAGE-039.06` | 298–422 | ناحیه | `——— ثبت گروهی نوبت‌ها (نمای روز تقویم): یک تاریخ، ` | 7 | ⬜ |
| `PAGE-039.07` | 423–440 | ناحیه | `——— ثبت تکی نوبت (فرم کلاسیک) ———` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-040</b> — <code>modules/request-center/Actions/bulk-actions.php</code> (202 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-040.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `PAGE-040.02` | 17–23 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `PAGE-040.03` | 24–43 | ناحیه | `مسیر: add_asset_relation` | **8** | ⬜ |
| `PAGE-040.04` | 44–202 | ناحیه | `مسیر: bulk_update_tickets` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-041</b> — <code>modules/request-center/Actions/dispatch.php</code> (29 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-041.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `PAGE-041.02` | 16–29 | ناحیه | `محافظ: رجیستری اکشن‌ها فقط داخل حوزهٔ اجرایی ماژول` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-042</b> — <code>modules/request-center/Actions/governance-actions.php</code> (247 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-042.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `PAGE-042.02` | 17–17 | ناحیه | `رجیستری RC همهٔ فایل‌های اکشن را بدون قید بارگذاری` | 0 | ⬜ |
| `PAGE-042.03` | 18–22 | ناحیه | `خودِ تغییر و مشکل اعمال می‌شود تا اکشن‌های نامرتبط` | 0 | ⬜ |
| `PAGE-042.04` | 23–27 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `PAGE-042.05` | 28–65 | ناحیه | `مسیر: create_change` | 2 | ⬜ |
| `PAGE-042.06` | 66–95 | ناحیه | `مسیر: update_change` | 2 | ⬜ |
| `PAGE-042.07` | 96–114 | ناحیه | `مسیر: submit_change` | 2 | ⬜ |
| `PAGE-042.08` | 115–137 | ناحیه | `مسیر: cab_decision` | 2 | ⬜ |
| `PAGE-042.09` | 138–161 | ناحیه | `مسیر: change_transition` | 7 | ⬜ |
| `PAGE-042.10` | 162–192 | ناحیه | `مسیر: create_problem` | 2 | ⬜ |
| `PAGE-042.11` | 193–222 | ناحیه | `مسیر: update_problem` | 2 | ⬜ |
| `PAGE-042.12` | 223–247 | ناحیه | `مسیر: link_governance_ticket` | 7 | ⬜ |

</details>

<details>
<summary><b>PAGE-043</b> — <code>modules/request-center/Actions/ola-actions.php</code> (54 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-043.01` | 1–14 | ناحیه | `بلوک رویه‌ای 1-14` | 0 | ⬜ |
| `PAGE-043.02` | 15–16 | ناحیه | `گیت فقط برای اکشن‌های خود OLA؛ رجیستری RC همهٔ فای` | 0 | ⬜ |
| `PAGE-043.03` | 17–21 | ناحیه | `شرط مسیریابی` | 0 | ⬜ |
| `PAGE-043.04` | 22–46 | ناحیه | `مسیر: save_ola_policy` | 3 | ⬜ |
| `PAGE-043.05` | 47–54 | ناحیه | `مسیر: toggle_ola_policy` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-044</b> — <code>modules/request-center/Actions/service-actions.php</code> (229 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-044.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `PAGE-044.02` | 22–28 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `PAGE-044.03` | 29–68 | ناحیه | `مسیر: add_service` | 2 | ⬜ |
| `PAGE-044.04` | 69–93 | ناحیه | `مسیر: add_service_field` | 2 | ⬜ |
| `PAGE-044.05` | 94–132 | ناحیه | `مسیر: edit_service` | 0 | ⬜ |
| `PAGE-044.06` | 133–154 | ناحیه | `مسیر: delete_service` | 0 | ⬜ |
| `PAGE-044.07` | 155–185 | ناحیه | `مسیر: save_service_category` | 2 | ⬜ |
| `PAGE-044.08` | 186–205 | ناحیه | `مسیر: delete_service_category` | 0 | ⬜ |
| `PAGE-044.09` | 206–229 | ناحیه | `مسیر: add_knowledge` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-045</b> — <code>modules/request-center/Actions/supervisor-actions.php</code> (544 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-045.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `PAGE-045.02` | 19–25 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `PAGE-045.03` | 26–174 | ناحیه | `مسیر: supervisor_assign_ticket` | 3 | ⬜ |
| `PAGE-045.04` | 175–405 | ناحیه | `مسیر: supervisor_decision` | 3 | ⬜ |
| `PAGE-045.05` | 406–502 | ناحیه | `مسیر: supervisor_reopen` | 2 | ⬜ |
| `PAGE-045.06` | 503–544 | ناحیه | `مسیر: rate_ticket` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-046</b> — <code>modules/request-center/Actions/ticket-actions.php</code> (1,420 خط، 12 آدرس، بازبینی‌شده 0/12)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-046.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `PAGE-046.02` | 22–28 | ناحیه | `محافظ: فایل اکشن فقط در runtime احرازشدهٔ سامانه ب` | 0 | ⬜ |
| `PAGE-046.03` | 29–261 | ناحیه | `مسیر: create_ticket` | **8** | ⬜ |
| `PAGE-046.04` | 262–357 | ناحیه | `مسیر: cancel_ticket` | 2 | ⬜ |
| `PAGE-046.05` | 358–472 | ناحیه | `مسیر: decide_ticket_cancellation` | 2 | ⬜ |
| `PAGE-046.06` | 473–532 | ناحیه | `مسیر: claim_ticket` | 2 | ⬜ |
| `PAGE-046.07` | 533–606 | ناحیه | `مسیر: reopen_ticket` | 2 | ⬜ |
| `PAGE-046.08` | 607–797 | ناحیه | `مسیر: reply` | 3 | ⬜ |
| `PAGE-046.09` | 798–971 | ناحیه | `مسیر: unit_head_ticket_update` | 3 | ⬜ |
| `PAGE-046.10` | 972–1205 | ناحیه | `مسیر: update_ticket` | 3 | ⬜ |
| `PAGE-046.11` | 1206–1304 | ناحیه | `مسیر: update_due_date` | 2 | ⬜ |
| `PAGE-046.12` | 1305–1420 | ناحیه | `مسیر: agent_transfer_ticket` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-047</b> — <code>modules/request-center/AppointmentRepository.php</code> (727 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-047.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-047.02` | 4–726 | کلاس | `AppointmentRepository` | **8** | ⬜ |

</details>

<details>
<summary><b>PAGE-048</b> — <code>modules/request-center/Assets/request-center.css</code> (241 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-048.01` | 1–66 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `PAGE-048.02` | 67–80 | بلوک | `تقویم وسط صفحه (مثل الگوی تقویم سفارش غذا)` | 0 | ⬜ |
| `PAGE-048.03` | 81–185 | بلوک | `نمای ماهانه — «همان تقویم سفارش غذا» ولی برای درخو` | 2 | ⬜ |
| `PAGE-048.04` | 186–210 | بلوک | `نمای روز: انتخاب درخواست‌ها و تعیین ساعت` | 2 | ⬜ |
| `PAGE-048.05` | 211–224 | بلوک | `حالت‌های UX` | 1 | ⬜ |
| `PAGE-048.06` | 225–237 | بلوک | `ظرفیت رسیدگی روز (نمای روز)` | 0 | ⬜ |
| `PAGE-048.07` | 238–241 | بلوک | `روزهای تعطیل هفتگی — شنبه تا جمعه، همیشه در یک خط` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-049</b> — <code>modules/request-center/Assets/request-center.js</code> (47 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-049.01` | 1–47 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-050</b> — <code>modules/request-center/Pages/admin.php</code> (384 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-050.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-050.02` | 4–28 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 2 | ⬜ |
| `PAGE-050.03` | 29–29 | ناحیه | `The primary-admin workspace exposes a scoped opera` | 0 | ⬜ |
| `PAGE-050.04` | 30–40 | ناحیه | `Never render event details here: they may contain ` | 5 | ⬜ |
| `PAGE-050.05` | 41–55 | ناحیه | `Configuration changes are stored in the central au` | 0 | ⬜ |
| `PAGE-050.06` | 56–69 | ناحیه | `ظرفیت نوبت‌ها — مقادیر فعلی برای فرم «ظرفیت روزانه` | 0 | ⬜ |
| `PAGE-050.07` | 70–113 | ناحیه | `تعطیلات رسمی — فهرست برای کارت مدیریت تعطیلات سمت ` | 0 | ⬜ |
| `PAGE-050.08` | 114–384 | ناحیه | `فقط مقصدهایی را نشان بده که همین حساب مجوز ورود به` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-051</b> — <code>modules/request-center/Pages/analytics.php</code> (63 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-051.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-051.02` | 4–11 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-051.03` | 12–63 | ناحیه | `مسیر: analytics` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-052</b> — <code>modules/request-center/Pages/calendar.php</code> (1,184 خط، 19 آدرس، بازبینی‌شده 0/19)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-052.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-052.02` | 4–39 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-052.03` | 40–44 | ناحیه | `——— امروز (شمسی + میلادی) ———` | 0 | ⬜ |
| `PAGE-052.04` | 45–88 | ناحیه | `——— پارامتر ماه شمسی ———` | 2 | ⬜ |
| `PAGE-052.05` | 89–101 | ناحیه | `مسیر: day` | 5 | ⬜ |
| `PAGE-052.06` | 102–121 | ناحیه | `مسیر: day` | 0 | ⬜ |
| `PAGE-052.07` | 122–133 | ناحیه | `مسیر: day` | 0 | ⬜ |
| `PAGE-052.08` | 134–174 | ناحیه | `——— بارگذاری رویدادهای موعد (خواندنی) ———` | 0 | ⬜ |
| `PAGE-052.09` | 175–220 | ناحیه | `——— بارگذاری نوبت‌های رسیدگی (کنشی) ———` | 0 | ⬜ |
| `PAGE-052.10` | 221–244 | ناحیه | `——— تعطیلات رسمی در این بازه ———` | 4 | ⬜ |
| `PAGE-052.11` | 245–248 | ناحیه | `——— ساعت کاری/تعطیل هفتگی (ISO-8601: ۵شنبه و جمعه ` | 0 | ⬜ |
| `PAGE-052.12` | 249–259 | ناحیه | `——— ظرفیت تیم (فقط مدیر) یا خلاصهٔ شخصی ———` | 0 | ⬜ |
| `PAGE-052.13` | 260–279 | ناحیه | `——— ظرفیت امروز (سقف روزانهٔ کارشناس/دایرهٔ تیم) —` | 0 | ⬜ |
| `PAGE-052.14` | 280–289 | ناحیه | `——— نوبت‌های امروز برای تایم‌لاین کوچک ———` | 0 | ⬜ |
| `PAGE-052.15` | 290–330 | ناحیه | `——— نمای روز: تاریخ شمسی + درخواست‌های قابل نوبت‌گ` | **9** | ⬜ |
| `PAGE-052.16` | 331–340 | ناحیه | `——— ظرفیت رسیدگی روزِ انتخاب‌شده (برای نمای روز): ` | 0 | ⬜ |
| `PAGE-052.17` | 341–394 | ناحیه | `——— دادهٔ پاپ‌آپ روز (نمای ماهانه): رویدادهای هر ر` | 0 | ⬜ |
| `PAGE-052.18` | 395–437 | ناحیه | `——— کمک‌گرهای نمایش ———` | 2 | ⬜ |
| `PAGE-052.19` | 438–1184 | ناحیه | `پوستهٔ مرکزی سامانه (render_header) + تقویم شمسی د` | 4 | ⬜ |

</details>

<details>
<summary><b>PAGE-053</b> — <code>modules/request-center/Pages/create.php</code> (331 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-053.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-053.02` | 4–18 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-053.03` | 19–331 | ناحیه | `مسیر: new-ticket` | **11** | ⬜ |

</details>

<details>
<summary><b>PAGE-054</b> — <code>modules/request-center/Pages/detail.php</code> (644 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-054.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-054.02` | 4–16 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-054.03` | 17–644 | ناحیه | `مسیر: ticket` | **14** | ⬜ |

</details>

<details>
<summary><b>PAGE-055</b> — <code>modules/request-center/Pages/governance.php</code> (160 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-055.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-055.02` | 4–16 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-055.03` | 17–160 | ناحیه | `مسیر: governance` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-056</b> — <code>modules/request-center/Pages/index.php</code> (429 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-056.01` | 1–48 | ناحیه | `بلوک رویه‌ای 1-48` | 0 | ⬜ |
| `PAGE-056.02` | 49–49 | ناحیه | `مدیر واحدِ صرف (مدیر چارت بدون شرایط کارشناسی/پرسن` | 0 | ⬜ |
| `PAGE-056.03` | 50–100 | ناحیه | `جای او «فضای مدیر واحد» است تا تیکت‌های ارجاع‌یافت` | 2 | ⬜ |
| `PAGE-056.04` | 101–117 | ناحیه | `Dedicated role workspaces are routed before the ge` | 2 | ⬜ |
| `PAGE-056.05` | 118–118 | ناحیه | `One canonical operational detail page serves every` | 0 | ⬜ |
| `PAGE-056.06` | 119–119 | ناحیه | `Forward older workspace links/bookmarks containing` | 0 | ⬜ |
| `PAGE-056.07` | 120–147 | ناحیه | `validated list context instead of rendering a dupl` | 2 | ⬜ |
| `PAGE-056.08` | 148–224 | ناحیه | `پوستهٔ مرکزی سامانه (render_header) + ناوبری ماژول` | 7 | ⬜ |
| `PAGE-056.09` | 225–429 | ناحیه | `هشدارهای تخطی SLA/OLA (ثبت‌شده توسط cron) در محدود` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-057</b> — <code>modules/request-center/Pages/knowledge.php</code> (52 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-057.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-057.02` | 4–15 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-057.03` | 16–52 | ناحیه | `مسیر: knowledge` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-058</b> — <code>modules/request-center/Pages/ola.php</code> (63 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-058.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-058.02` | 4–16 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-058.03` | 17–63 | ناحیه | `مسیر: ola` | 7 | ⬜ |

</details>

<details>
<summary><b>PAGE-059</b> — <code>modules/request-center/Pages/queue.php</code> (133 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-059.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-059.02` | 4–11 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-059.03` | 12–133 | ناحیه | `مسیر: queue` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-060</b> — <code>modules/request-center/Pages/reports.php</code> (101 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-060.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-060.02` | 4–11 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-060.03` | 12–101 | ناحیه | `مسیر: reports` | 7 | ⬜ |

</details>

<details>
<summary><b>PAGE-061</b> — <code>modules/request-center/Pages/services.php</code> (280 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-061.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-061.02` | 4–11 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-061.03` | 12–127 | ناحیه | `مسیر: services` | **8** | ⬜ |
| `PAGE-061.04` | 128–136 | ناحیه | `سربرگ‌ها (تب‌ها)` | 0 | ⬜ |
| `PAGE-061.05` | 137–146 | ناحیه | `تب ۱: تعریف خدمت (فقط فرم ایجاد خدمت)` | 0 | ⬜ |
| `PAGE-061.06` | 147–170 | ناحیه | `تب ۲: لیست خدمات + فیلتر` | 6 | ⬜ |
| `PAGE-061.07` | 171–184 | ناحیه | `تب ۳: دسته‌بندی‌ها` | 6 | ⬜ |
| `PAGE-061.08` | 185–280 | ناحیه | `پنجره‌های ویرایش/فیلد (rendered outside the tab pa` | 6 | ⬜ |

</details>

<details>
<summary><b>PAGE-062</b> — <code>modules/request-center/Pages/supervisor-workspace.php</code> (128 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-062.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-062.02` | 4–19 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 0 | ⬜ |
| `PAGE-062.03` | 20–128 | ناحیه | `هشدارهای فعال SLA/OLA که cron ثبت کرده (محدود به م` | 7 | ⬜ |

</details>

<details>
<summary><b>PAGE-063</b> — <code>modules/request-center/Pages/supervisor.php</code> (192 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-063.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-063.02` | 4–15 | ناحیه | `محافظ: این صفحه فقط از طریق ورودی مستقل Request Ce` | 0 | ⬜ |
| `PAGE-063.03` | 16–192 | ناحیه | `مسیر: supervisor` | **10** | ⬜ |

</details>

<details>
<summary><b>PAGE-064</b> — <code>modules/request-center/Pages/unit.php</code> (186 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-064.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-064.02` | 4–186 | ناحیه | `محافظ: این فایل فقط از طریق نقاط ورود معتبر (index` | 4 | ⬜ |

</details>

<details>
<summary><b>PAGE-065</b> — <code>modules/request-center/RequestCenterRepository.php</code> (657 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-065.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-065.02` | 4–656 | کلاس | `RequestCenterRepository` | **8** | ⬜ |

</details>

<details>
<summary><b>PAGE-066</b> — <code>modules/request-center/WorkflowService.php</code> (388 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-066.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-066.02` | 4–34 | کلاس | `RequestCenterSqlExpression` | 0 | ⬜ |
| `PAGE-066.03` | 36–387 | کلاس | `RequestCenterWorkflowService` | **9** | ⬜ |

</details>

<details>
<summary><b>PAGE-067</b> — <code>modules/request-center/lib/endpoints.php</code> (237 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-067.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-067.02` | 4–72 | تابع | `request_center_excel_download` | 4 | ⬜ |
| `PAGE-067.03` | 74–105 | تابع | `request_center_export_report` | 7 | ⬜ |
| `PAGE-067.04` | 108–174 | تابع | `request_center_export_decisions` | 7 | ⬜ |
| `PAGE-067.05` | 177–202 | تابع | `request_center_download_attachment` | 2 | ⬜ |
| `PAGE-067.06` | 204–223 | تابع | `request_center_dashboard_rows` | 5 | ⬜ |
| `PAGE-067.07` | 225–236 | تابع | `request_center_global_search` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-068</b> — <code>modules/request-center/lib/functions.php</code> (889 خط، 32 آدرس، بازبینی‌شده 0/32)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-068.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-068.02` | 4–102 | تابع | `store_attachment` | **11** | ⬜ |
| `PAGE-068.03` | 104–109 | تابع | `fetch_ticket` | 0 | ⬜ |
| `PAGE-068.04` | 111–114 | تابع | `can_view_ticket` | 0 | ⬜ |
| `PAGE-068.05` | 115–119 | ناحیه | `بلوک رویه‌ای 115-119` | 0 | ⬜ |
| `PAGE-068.06` | 120–138 | تابع | `ticket_org_auto_role_sql` | 0 | ⬜ |
| `PAGE-068.07` | 140–155 | تابع | `ticket_org_manager_rank_sql` | 0 | ⬜ |
| `PAGE-068.08` | 157–165 | تابع | `key_role_designated_agent_ids` | 0 | ⬜ |
| `PAGE-068.09` | 167–269 | تابع | `choose_handling_agent` | 5 | ⬜ |
| `PAGE-068.10` | 271–279 | تابع | `ticket_allowed_transitions` | 0 | ⬜ |
| `PAGE-068.11` | 280–284 | تابع | `ticket_requester_cancelable_statuses` | 0 | ⬜ |
| `PAGE-068.12` | 286–289 | تابع | `ticket_cancellation_decision_statuses` | 0 | ⬜ |
| `PAGE-068.13` | 291–297 | تابع | `ticket_requester_can_cancel_directly` | 0 | ⬜ |
| `PAGE-068.14` | 299–305 | تابع | `ticket_requester_can_cancel` | 0 | ⬜ |
| `PAGE-068.15` | 307–316 | تابع | `notify_ticket_resolved` | 0 | ⬜ |
| `PAGE-068.16` | 318–372 | تابع | `report_query_filters` | 2 | ⬜ |
| `PAGE-068.17` | 374–429 | تابع | `request_center_assert_create_schema` | 0 | ⬜ |
| `PAGE-068.18` | 431–449 | تابع | `request_center_ticket_number_peek` | 0 | ⬜ |
| `PAGE-068.19` | 451–475 | تابع | `request_center_org_deputy_units` | 5 | ⬜ |
| `PAGE-068.20` | 477–493 | تابع | `request_center_status_label` | 0 | ⬜ |
| `PAGE-068.21` | 495–535 | تابع | `request_center_ticket_number_next` | 0 | ⬜ |
| `PAGE-068.22` | 537–570 | تابع | `request_center_notify_user` | 4 | ⬜ |
| `PAGE-068.23` | 572–616 | تابع | `request_center_notify_ticket_parties` | 4 | ⬜ |
| `PAGE-068.24` | 618–661 | تابع | `request_center_notify_ticket_staff` | 4 | ⬜ |
| `PAGE-068.25` | 663–703 | تابع | `request_center_notify_ticket_resolved` | 4 | ⬜ |
| `PAGE-068.26` | 705–716 | تابع | `request_center_can_supervise_ticket` | 0 | ⬜ |
| `PAGE-068.27` | 718–778 | تابع | `request_center_ticket_return_url` | 7 | ⬜ |
| `PAGE-068.28` | 781–795 | تابع | `request_center_unit_head_scope_ids` | 0 | ⬜ |
| `PAGE-068.29` | 797–809 | تابع | `request_center_is_unit_head` | 0 | ⬜ |
| `PAGE-068.30` | 811–835 | تابع | `request_center_is_org_unit_head` | 0 | ⬜ |
| `PAGE-068.31` | 838–865 | تابع | `request_center_manager_ticket_in_subtree` | 5 | ⬜ |
| `PAGE-068.32` | 867–888 | تابع | `request_center_manager_can_request_cancel` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-069</b> — <code>modules/request-center/lib/governance.php</code> (155 خط، 19 آدرس، بازبینی‌شده 0/19)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-069.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-069.02` | 4–9 | ناحیه | `محافظ: helperهای حاکمیت ITSM؛ فقط از ورودی‌های معت` | 0 | ⬜ |
| `PAGE-069.03` | 10–20 | تابع | `gov_post` | 2 | ⬜ |
| `PAGE-069.04` | 22–25 | تابع | `gov_choice` | 0 | ⬜ |
| `PAGE-069.05` | 27–30 | تابع | `gov_change_number` | 0 | ⬜ |
| `PAGE-069.06` | 32–35 | تابع | `gov_problem_number` | 0 | ⬜ |
| `PAGE-069.07` | 37–40 | تابع | `gov_risk_label` | 0 | ⬜ |
| `PAGE-069.08` | 42–45 | تابع | `gov_change_status_label` | 0 | ⬜ |
| `PAGE-069.09` | 47–50 | تابع | `gov_problem_status_label` | 0 | ⬜ |
| `PAGE-069.10` | 52–63 | تابع | `gov_parse_datetime` | 0 | ⬜ |
| `PAGE-069.11` | 65–78 | تابع | `gov_scope` | 5 | ⬜ |
| `PAGE-069.12` | 80–88 | تابع | `gov_fetch_ticket` | 0 | ⬜ |
| `PAGE-069.13` | 90–99 | تابع | `gov_can_view_ticket` | 0 | ⬜ |
| `PAGE-069.14` | 101–106 | تابع | `gov_fetch_change` | 0 | ⬜ |
| `PAGE-069.15` | 108–113 | تابع | `gov_fetch_problem` | 0 | ⬜ |
| `PAGE-069.16` | 115–124 | تابع | `gov_can_view_record` | 0 | ⬜ |
| `PAGE-069.17` | 126–129 | تابع | `gov_datetime_input` | 0 | ⬜ |
| `PAGE-069.18` | 131–142 | تابع | `gov_ticket_options` | 0 | ⬜ |
| `PAGE-069.19` | 144–154 | تابع | `gov_notify_linked_tickets` | 5 | ⬜ |

</details>

<details>
<summary><b>PAGE-070</b> — <code>modules/request-center/lib/html-feedback.php</code> (43 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-070.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-070.02` | 4–42 | تابع | `request_center_render_flashes` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-071</b> — <code>modules/request-center/lib/http-errors.php</code> (112 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-071.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `PAGE-071.02` | 16–95 | تابع | `request_center_error_page` | 1 | ⬜ |
| `PAGE-071.03` | 97–110 | تابع | `request_center_direct_access_guard` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-072</b> — <code>modules/request-center/lib/navigation.php</code> (112 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-072.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-072.02` | 4–111 | تابع | `request_center_render_navigation` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-073</b> — <code>modules/request-center/post-handler.php</code> (193 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-073.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 0 | ⬜ |
| `PAGE-073.02` | 28–31 | تابع | `request_center_is_post_action` | 0 | ⬜ |
| `PAGE-073.03` | 32–37 | ناحیه | `بلوک رویه‌ای 32-37` | 2 | ⬜ |
| `PAGE-073.04` | 38–131 | تابع | `request_center_post_error` | **11** | ⬜ |
| `PAGE-073.05` | 133–192 | تابع | `request_center_handle_post` | 2 | ⬜ |

</details>

<details>
<summary><b>PAGE-074</b> — <code>organization.php</code> (797 خط، 52 آدرس، بازبینی‌شده 0/52)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-074.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-074.02` | 4–66 | تابع | `org_ensure_schema` | **9** | ⬜ |
| `PAGE-074.03` | 69–90 | تابع | `org_ticket_schema_ensure` | 4 | ⬜ |
| `PAGE-074.04` | 92–103 | تابع | `org_has_company_schema` | 0 | ⬜ |
| `PAGE-074.05` | 105–114 | تابع | `org_companies_all` | 0 | ⬜ |
| `PAGE-074.06` | 116–125 | تابع | `org_company_display_name` | 0 | ⬜ |
| `PAGE-074.07` | 127–134 | تابع | `org_company_user_label` | 0 | ⬜ |
| `PAGE-074.08` | 136–144 | تابع | `org_company_by_id` | 0 | ⬜ |
| `PAGE-074.09` | 146–152 | تابع | `org_user_company_memberships` | 0 | ⬜ |
| `PAGE-074.10` | 154–158 | تابع | `org_user_company_id` | 0 | ⬜ |
| `PAGE-074.11` | 160–164 | تابع | `org_user_available_for_company` | 0 | ⬜ |
| `PAGE-074.12` | 166–171 | تابع | `org_user_company_membership_join_sql` | 5 | ⬜ |
| `PAGE-074.13` | 173–178 | تابع | `org_company_member_user_ids` | 5 | ⬜ |
| `PAGE-074.14` | 180–195 | تابع | `org_company_context_id` | 0 | ⬜ |
| `PAGE-074.15` | 197–206 | تابع | `org_current_company_id` | 0 | ⬜ |
| `PAGE-074.16` | 208–212 | تابع | `org_company_root_type` | 0 | ⬜ |
| `PAGE-074.17` | 214–218 | تابع | `org_company_root_title` | 0 | ⬜ |
| `PAGE-074.18` | 220–233 | تابع | `org_has_sort_order` | 0 | ⬜ |
| `PAGE-074.19` | 235–243 | تابع | `org_next_sort_order` | 0 | ⬜ |
| `PAGE-074.20` | 245–254 | تابع | `org_units_all` | 0 | ⬜ |
| `PAGE-074.21` | 256–272 | تابع | `org_deputy_units` | 5 | ⬜ |
| `PAGE-074.22` | 274–299 | تابع | `org_liaison_units` | 4 | ⬜ |
| `PAGE-074.23` | 301–334 | تابع | `org_unit_routing_department_id` | 1 | ⬜ |
| `PAGE-074.24` | 336–349 | تابع | `org_ceo_unit_id` | 0 | ⬜ |
| `PAGE-074.25` | 351–359 | تابع | `org_unit_accepts_experts` | 0 | ⬜ |
| `PAGE-074.26` | 361–367 | تابع | `org_unit_by_id` | 0 | ⬜ |
| `PAGE-074.27` | 369–375 | تابع | `org_unit_by_company` | 0 | ⬜ |
| `PAGE-074.28` | 377–381 | تابع | `org_company_redirect_url` | 0 | ⬜ |
| `PAGE-074.29` | 383–389 | تابع | `org_unit_children` | 5 | ⬜ |
| `PAGE-074.30` | 391–406 | تابع | `org_unit_subtree_ids` | 0 | ⬜ |
| `PAGE-074.31` | 408–422 | تابع | `org_unit_path_names` | 0 | ⬜ |
| `PAGE-074.32` | 424–429 | تابع | `org_unit_manager_ids` | 0 | ⬜ |
| `PAGE-074.33` | 431–435 | تابع | `org_unit_is_primary_admin` | 0 | ⬜ |
| `PAGE-074.34` | 437–440 | تابع | `user_org_unit_id` | 0 | ⬜ |
| `PAGE-074.35` | 442–447 | تابع | `user_is_unit_manager` | 0 | ⬜ |
| `PAGE-074.36` | 449–465 | تابع | `user_managed_unit_ids` | 0 | ⬜ |
| `PAGE-074.37` | 467–476 | تابع | `user_org_scope_unit_ids` | 0 | ⬜ |
| `PAGE-074.38` | 478–484 | تابع | `user_is_org_manager_of` | 0 | ⬜ |
| `PAGE-074.39` | 486–502 | تابع | `user_can_manage_organization` | 0 | ⬜ |
| `PAGE-074.40` | 504–527 | تابع | `org_snapshot` | 5 | ⬜ |
| `PAGE-074.41` | 529–536 | تابع | `org_snapshot_maps` | 0 | ⬜ |
| `PAGE-074.42` | 538–559 | تابع | `org_snapshot_row_signature` | 0 | ⬜ |
| `PAGE-074.43` | 561–578 | تابع | `org_snapshot_delta` | 0 | ⬜ |
| `PAGE-074.44` | 580–593 | تابع | `org_record_undo` | 0 | ⬜ |
| `PAGE-074.45` | 595–601 | تابع | `org_undo_state` | 0 | ⬜ |
| `PAGE-074.46` | 603–678 | تابع | `org_restore_snapshot` | 5 | ⬜ |
| `PAGE-074.47` | 680–689 | تابع | `org_position_unit_types` | 0 | ⬜ |
| `PAGE-074.48` | 691–694 | تابع | `org_exempt_roles` | 0 | ⬜ |
| `PAGE-074.49` | 696–714 | تابع | `org_ensure_role_sync_columns` | **8** | ⬜ |
| `PAGE-074.50` | 716–732 | تابع | `org_position_user_ids` | 4 | ⬜ |
| `PAGE-074.51` | 734–739 | تابع | `org_expert_user_ids` | 0 | ⬜ |
| `PAGE-074.52` | 741–796 | تابع | `org_sync_position_roles` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-075</b> — <code>permissions.php</code> (981 خط، 52 آدرس، بازبینی‌شده 0/52)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-075.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-075.02` | 4–26 | تابع | `permission_roles` | 0 | ⬜ |
| `PAGE-075.03` | 28–215 | تابع | `permission_catalog` | 2 | ⬜ |
| `PAGE-075.04` | 217–226 | تابع | `permission_all_codes` | 0 | ⬜ |
| `PAGE-075.05` | 227–231 | ناحیه | `بلوک رویه‌ای 227-231` | 0 | ⬜ |
| `PAGE-075.06` | 232–236 | تابع | `permission_cddvd_direct_edit_roles` | 1 | ⬜ |
| `PAGE-075.07` | 238–245 | تابع | `permission_filter_role_codes` | 0 | ⬜ |
| `PAGE-075.08` | 247–280 | تابع | `permission_edit_map` | 0 | ⬜ |
| `PAGE-075.09` | 282–292 | تابع | `role_permissions_add_parents` | 0 | ⬜ |
| `PAGE-075.10` | 293–296 | ناحیه | `بلوک رویه‌ای 293-296` | 0 | ⬜ |
| `PAGE-075.11` | 297–301 | تابع | `print_fish_allowed_roles` | 0 | ⬜ |
| `PAGE-075.12` | 303–312 | تابع | `print_fish_role_targets` | 0 | ⬜ |
| `PAGE-075.13` | 314–324 | تابع | `print_fish_role_codes` | 0 | ⬜ |
| `PAGE-075.14` | 326–343 | تابع | `permission_defaults` | 0 | ⬜ |
| `PAGE-075.15` | 345–349 | تابع | `user_default_removed_codes` | 0 | ⬜ |
| `PAGE-075.16` | 351–371 | تابع | `permission_defaults_raw` | 0 | ⬜ |
| `PAGE-075.17` | 373–386 | تابع | `permission_table_ready` | 0 | ⬜ |
| `PAGE-075.18` | 387–390 | ناحیه | `بلوک رویه‌ای 387-390` | 0 | ⬜ |
| `PAGE-075.19` | 391–407 | تابع | `role_permissions_custom` | 0 | ⬜ |
| `PAGE-075.20` | 409–415 | تابع | `role_permissions_mark_custom` | 0 | ⬜ |
| `PAGE-075.21` | 417–421 | تابع | `role_permissions_normalize_codes` | 0 | ⬜ |
| `PAGE-075.22` | 423–449 | تابع | `permission_food_legacy_codes` | 0 | ⬜ |
| `PAGE-075.23` | 451–465 | تابع | `role_permission_effective_codes` | 0 | ⬜ |
| `PAGE-075.24` | 467–475 | تابع | `role_permissions_same_set` | 0 | ⬜ |
| `PAGE-075.25` | 477–486 | تابع | `role_permissions_flush_cache` | 0 | ⬜ |
| `PAGE-075.26` | 488–584 | تابع | `role_permissions_migrate_once` | **9** | ⬜ |
| `PAGE-075.27` | 586–607 | تابع | `role_permission_codes` | 0 | ⬜ |
| `PAGE-075.28` | 609–622 | تابع | `role_permission_set` | 0 | ⬜ |
| `PAGE-075.29` | 624–666 | تابع | `user_access_group_permission_set` | 0 | ⬜ |
| `PAGE-075.30` | 668–679 | تابع | `user_access_group_permissions_flush_cache` | 1 | ⬜ |
| `PAGE-075.31` | 681–691 | تابع | `role_permissions_seeded` | 0 | ⬜ |
| `PAGE-075.32` | 693–727 | تابع | `role_permissions_seed` | 0 | ⬜ |
| `PAGE-075.33` | 729–762 | تابع | `role_permissions_save` | 1 | ⬜ |
| `PAGE-075.34` | 764–783 | تابع | `role_permissions_reset` | 1 | ⬜ |
| `PAGE-075.35` | 785–788 | تابع | `user_role_code` | 0 | ⬜ |
| `PAGE-075.36` | 790–794 | تابع | `user_is_primary_admin` | 0 | ⬜ |
| `PAGE-075.37` | 796–834 | تابع | `user_can` | 0 | ⬜ |
| `PAGE-075.38` | 836–853 | تابع | `user_can_supervisor_grants` | 0 | ⬜ |
| `PAGE-075.39` | 855–868 | تابع | `user_can_expert_grants` | 0 | ⬜ |
| `PAGE-075.40` | 870–878 | تابع | `user_can_any` | 0 | ⬜ |
| `PAGE-075.41` | 880–889 | تابع | `require_permission` | 0 | ⬜ |
| `PAGE-075.42` | 891–899 | تابع | `require_any_permission` | 0 | ⬜ |
| `PAGE-075.43` | 901–928 | تابع | `permission_icon` | 0 | ⬜ |
| `PAGE-075.44` | 930–934 | تابع | `staff_role_codes` | 0 | ⬜ |
| `PAGE-075.45` | 936–939 | تابع | `is_staff_role` | 0 | ⬜ |
| `PAGE-075.46` | 941–945 | تابع | `admin_role_codes` | 0 | ⬜ |
| `PAGE-075.47` | 947–950 | تابع | `is_admin_role` | 0 | ⬜ |
| `PAGE-075.48` | 952–957 | تابع | `global_ticket_role_codes` | 0 | ⬜ |
| `PAGE-075.49` | 959–962 | تابع | `is_global_ticket_role` | 0 | ⬜ |
| `PAGE-075.50` | 964–969 | تابع | `is_department_scoped_role` | 0 | ⬜ |
| `PAGE-075.51` | 971–975 | تابع | `assignable_role_codes` | 0 | ⬜ |
| `PAGE-075.52` | 977–980 | تابع | `is_assignable_role` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-076</b> — <code>preview/food-release-download/vite.config.js</code> (93 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-076.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `PAGE-076.02` | 16–93 | بلوک | `pathname` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-077</b> — <code>request-center.php</code> (261 خط، 28 آدرس، بازبینی‌شده 0/28)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-077.01` | 1–14 | ناحیه | `بلوک رویه‌ای 1-14` | 0 | ⬜ |
| `PAGE-077.02` | 15–15 | ناحیه | `خطاهای غیرمنتظره در هیچ مسیری نباید به صفحهٔ سفید ` | 0 | ⬜ |
| `PAGE-077.03` | 16–27 | ناحیه | `قبل از هر چیز لایهٔ خطای ماژول را مطمئن کن؛ خودش ب` | 2 | ⬜ |
| `PAGE-077.04` | 28–28 | ناحیه | `Attachment downloads are Request Center-owned read` | 0 | ⬜ |
| `PAGE-077.05` | 29–79 | ناحیه | `legacy front controller's global schema setup.` | 2 | ⬜ |
| `PAGE-077.06` | 80–80 | ناحیه | `The existing appointment endpoint remains on its e` | 0 | ⬜ |
| `PAGE-077.07` | 81–120 | ناحیه | `It is intentionally not expanded as part of lifecy` | 2 | ⬜ |
| `PAGE-077.08` | 121–121 | ناحیه | `Route lifecycle pages through the module. Do not l` | 0 | ⬜ |
| `PAGE-077.09` | 122–154 | ناحیه | `controller for these pages: it is a compatibility ` | 2 | ⬜ |
| `PAGE-077.10` | 155–158 | تابع | `chatbot_render_widget` | 0 | ⬜ |
| `PAGE-077.11` | 159–175 | ناحیه | `بلوک رویه‌ای 159-175` | 2 | ⬜ |
| `PAGE-077.12` | 176–179 | ناحیه | `مسیر: new-ticket` | 0 | ⬜ |
| `PAGE-077.13` | 180–183 | ناحیه | `مسیر: ticket` | 0 | ⬜ |
| `PAGE-077.14` | 184–187 | ناحیه | `مسیر: queue` | 0 | ⬜ |
| `PAGE-077.15` | 188–191 | ناحیه | `مسیر: supervisor` | 0 | ⬜ |
| `PAGE-077.16` | 192–195 | ناحیه | `مسیر: services` | 0 | ⬜ |
| `PAGE-077.17` | 196–199 | ناحیه | `مسیر: reports` | 0 | ⬜ |
| `PAGE-077.18` | 200–203 | ناحیه | `مسیر: analytics` | 0 | ⬜ |
| `PAGE-077.19` | 204–207 | ناحیه | `مسیر: knowledge` | 0 | ⬜ |
| `PAGE-077.20` | 208–211 | ناحیه | `مسیر: governance` | 0 | ⬜ |
| `PAGE-077.21` | 212–217 | ناحیه | `مسیر: ola` | 0 | ⬜ |
| `PAGE-077.22` | 218–226 | ناحیه | `Workspace dashboards and the scoped read interface` | 0 | ⬜ |
| `PAGE-077.23` | 227–227 | ناحیه | `Compatibility bridge for older bookmarks and syste` | 0 | ⬜ |
| `PAGE-077.24` | 228–228 | ناحیه | `محدود به هدف‌های شناخته‌شدهٔ پوستهٔ اصلی سامانه: م` | 0 | ⬜ |
| `PAGE-077.25` | 229–229 | ناحیه | `و بدون اجازهٔ load شدن هستهٔ legacy از مسیر RC اجر` | 0 | ⬜ |
| `PAGE-077.26` | 230–234 | ناحیه | `New lifecycle navigation and successful RC operati` | 0 | ⬜ |
| `PAGE-077.27` | 235–245 | ناحیه | `فهرست صریح صفحه‌های legacy که در خود index.php پشت` | 0 | ⬜ |
| `PAGE-077.28` | 246–261 | ناحیه | `خروجی‌ها/دانلودهای legacy (همان‌هایی که در مخزن اس` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-078</b> — <code>session_bootstrap.php</code> (162 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-078.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-078.02` | 4–117 | تابع | `app_session_start` | 5 | ⬜ |
| `PAGE-078.03` | 119–161 | تابع | `app_session_enforce_idle` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-079</b> — <code>system-health.php</code> (1,340 خط، 30 آدرس، بازبینی‌شده 0/30)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-079.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `PAGE-079.02` | 13–23 | تابع | `system_health_ensure` | 0 | ⬜ |
| `PAGE-079.03` | 25–41 | تابع | `system_health_issue_filter` | 5 | ⬜ |
| `PAGE-079.04` | 43–57 | تابع | `system_health_issue_rows` | 5 | ⬜ |
| `PAGE-079.05` | 59–69 | تابع | `system_health_issue_total` | 0 | ⬜ |
| `PAGE-079.06` | 71–84 | تابع | `system_health_issue_history` | 5 | ⬜ |
| `PAGE-079.07` | 86–94 | تابع | `system_health_issue_history_total` | 0 | ⬜ |
| `PAGE-079.08` | 96–99 | تابع | `system_health_fingerprint` | 0 | ⬜ |
| `PAGE-079.09` | 101–121 | تابع | `system_health_table_label` | 0 | ⬜ |
| `PAGE-079.10` | 123–162 | تابع | `system_health_human_module` | 0 | ⬜ |
| `PAGE-079.11` | 164–174 | تابع | `system_health_human_level` | 0 | ⬜ |
| `PAGE-079.12` | 176–225 | تابع | `system_health_humanize_event` | 0 | ⬜ |
| `PAGE-079.13` | 227–250 | تابع | `system_health_event_reference` | 0 | ⬜ |
| `PAGE-079.14` | 252–270 | تابع | `system_health_request_label` | 0 | ⬜ |
| `PAGE-079.15` | 272–319 | تابع | `system_health_summary` | 0 | ⬜ |
| `PAGE-079.16` | 321–338 | تابع | `system_health_apply_check_state` | 0 | ⬜ |
| `PAGE-079.17` | 340–403 | تابع | `system_health_groups` | 0 | ⬜ |
| `PAGE-079.18` | 405–430 | تابع | `system_health_event_totals` | 0 | ⬜ |
| `PAGE-079.19` | 432–700 | تابع | `system_health_checks` | 4 | ⬜ |
| `PAGE-079.20` | 701–706 | ناحیه | `بلوک رویه‌ای 701-706` | 0 | ⬜ |
| `PAGE-079.21` | 707–737 | تابع | `sys_maint_cache_candidates` | 0 | ⬜ |
| `PAGE-079.22` | 739–755 | تابع | `sys_maint_cache_purge` | 5 | ⬜ |
| `PAGE-079.23` | 757–775 | تابع | `sys_maint_analyze_tables` | 0 | ⬜ |
| `PAGE-079.24` | 777–785 | تابع | `sys_maint_table_overview` | 0 | ⬜ |
| `PAGE-079.25` | 787–866 | تابع | `system_health_handle_post` | 2 | ⬜ |
| `PAGE-079.26` | 868–884 | تابع | `system_health_export_safe_text` | 0 | ⬜ |
| `PAGE-079.27` | 886–944 | تابع | `system_health_export_technical_xlsx` | 4 | ⬜ |
| `PAGE-079.28` | 947–1091 | تابع | `system_health_export_safe_xlsx` | 7 | ⬜ |
| `PAGE-079.29` | 1094–1116 | تابع | `system_health_request_timeline` | 0 | ⬜ |
| `PAGE-079.30` | 1118–1339 | تابع | `system_health_render` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-080</b> — <code>system-update.php</code> (415 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-080.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `PAGE-080.02` | 18–23 | تابع | `system_update_current_version` | 0 | ⬜ |
| `PAGE-080.03` | 25–34 | تابع | `system_update_ensure_table` | 4 | ⬜ |
| `PAGE-080.04` | 36–44 | تابع | `system_update_migration_applied` | 0 | ⬜ |
| `PAGE-080.05` | 46–77 | تابع | `system_update_requires_violation` | 0 | ⬜ |
| `PAGE-080.06` | 79–102 | تابع | `system_update_archive_path_is_safe` | 0 | ⬜ |
| `PAGE-080.07` | 104–150 | تابع | `system_update_validate_zip_entries` | 0 | ⬜ |
| `PAGE-080.08` | 152–191 | تابع | `system_update_verify_staged_package` | 0 | ⬜ |
| `PAGE-080.09` | 193–205 | تابع | `system_update_extract_package` | 0 | ⬜ |
| `PAGE-080.10` | 207–294 | تابع | `system_update_read_package` | 0 | ⬜ |
| `PAGE-080.11` | 295–297 | ناحیه | `بلوک رویه‌ای 295-297` | 0 | ⬜ |
| `PAGE-080.12` | 298–322 | تابع | `system_update_files_snapshot` | 1 | ⬜ |
| `PAGE-080.13` | 324–414 | تابع | `system_update_apply` | 1 | ⬜ |

</details>

<details>
<summary><b>PAGE-081</b> — <code>traffic-control.php</code> (1,445 خط، 67 آدرس، بازبینی‌شده 0/67)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-081.01` | 1–7 | ناحیه | `بلوک رویه‌ای 1-7` | 0 | ⬜ |
| `PAGE-081.02` | 8–8 | ناحیه | `کنترل تردد مراجعین و میهمانان — پورت‌شده از کنترل_` | 0 | ⬜ |
| `PAGE-081.03` | 9–10 | ناحیه | `همه‌ی توابع با پیشوند traffic_ برای جلوگیری از تدا` | 0 | ⬜ |
| `PAGE-081.04` | 11–14 | تابع | `traffic_can_manage` | 0 | ⬜ |
| `PAGE-081.05` | 16–19 | تابع | `traffic_can_view_destinations` | 0 | ⬜ |
| `PAGE-081.06` | 21–24 | تابع | `traffic_can_manage_destinations` | 0 | ⬜ |
| `PAGE-081.07` | 26–30 | تابع | `traffic_destinations` | 0 | ⬜ |
| `PAGE-081.08` | 32–35 | تابع | `traffic_approvers` | 0 | ⬜ |
| `PAGE-081.09` | 37–48 | تابع | `traffic_destination_row_approvers` | 0 | ⬜ |
| `PAGE-081.10` | 50–75 | تابع | `traffic_destination_approvers` | 0 | ⬜ |
| `PAGE-081.11` | 77–85 | تابع | `traffic_destination_id` | 0 | ⬜ |
| `PAGE-081.12` | 87–101 | تابع | `traffic_destination_approver` | 0 | ⬜ |
| `PAGE-081.13` | 103–103 | ناحیه | `شماره برگ به‌صورت تاریخ‌شمسی+شماره‌ترتیبی روزانه، ` | 0 | ⬜ |
| `PAGE-081.14` | 104–112 | تابع | `traffic_next_serial` | 0 | ⬜ |
| `PAGE-081.15` | 114–117 | تابع | `traffic_find_last_visit` | 0 | ⬜ |
| `PAGE-081.16` | 119–129 | تابع | `traffic_find_last_visit_by` | 5 | ⬜ |
| `PAGE-081.17` | 131–137 | تابع | `traffic_fetch_record` | 0 | ⬜ |
| `PAGE-081.18` | 139–155 | تابع | `traffic_search_condition` | 0 | ⬜ |
| `PAGE-081.19` | 157–171 | تابع | `traffic_query_records` | 5 | ⬜ |
| `PAGE-081.20` | 173–191 | تابع | `traffic_stats` | 0 | ⬜ |
| `PAGE-081.21` | 193–201 | تابع | `traffic_empty_stats` | 0 | ⬜ |
| `PAGE-081.22` | 203–226 | تابع | `traffic_report_criteria` | 0 | ⬜ |
| `PAGE-081.23` | 228–241 | تابع | `traffic_report_records` | 0 | ⬜ |
| `PAGE-081.24` | 243–250 | تابع | `traffic_report_columns` | 0 | ⬜ |
| `PAGE-081.25` | 252–260 | تابع | `traffic_report_selected_fields` | 0 | ⬜ |
| `PAGE-081.26` | 262–275 | تابع | `traffic_report_item` | 0 | ⬜ |
| `PAGE-081.27` | 277–301 | تابع | `traffic_export_report` | 0 | ⬜ |
| `PAGE-081.28` | 303–313 | کلاس | `TrafficInputException` | 0 | ⬜ |
| `PAGE-081.29` | 315–323 | تابع | `traffic_phone_normalized` | 0 | ⬜ |
| `PAGE-081.30` | 325–328 | تابع | `traffic_phone_digits` | 0 | ⬜ |
| `PAGE-081.31` | 330–343 | تابع | `traffic_phone_tail` | 0 | ⬜ |
| `PAGE-081.32` | 345–373 | تابع | `traffic_validate_payload` | 0 | ⬜ |
| `PAGE-081.33` | 375–383 | تابع | `traffic_visit_snapshot` | 0 | ⬜ |
| `PAGE-081.34` | 385–407 | تابع | `traffic_record_history` | 1 | ⬜ |
| `PAGE-081.35` | 409–419 | تابع | `traffic_history_records` | 5 | ⬜ |
| `PAGE-081.36` | 421–424 | تابع | `traffic_history_count` | 0 | ⬜ |
| `PAGE-081.37` | 426–435 | تابع | `traffic_history_field_label` | 0 | ⬜ |
| `PAGE-081.38` | 437–446 | تابع | `traffic_normalize_time_input` | 0 | ⬜ |
| `PAGE-081.39` | 448–466 | تابع | `traffic_history_value` | 0 | ⬜ |
| `PAGE-081.40` | 468–488 | تابع | `traffic_create_record` | 2 | ⬜ |
| `PAGE-081.41` | 490–544 | تابع | `traffic_update_record` | 2 | ⬜ |
| `PAGE-081.42` | 546–569 | تابع | `traffic_delete_record` | 2 | ⬜ |
| `PAGE-081.43` | 571–620 | تابع | `traffic_set_exit` | 2 | ⬜ |
| `PAGE-081.44` | 622–648 | تابع | `traffic_ensure_notice_schema` | 4 | ⬜ |
| `PAGE-081.45` | 649–651 | ناحیه | `بلوک رویه‌ای 649-651` | 0 | ⬜ |
| `PAGE-081.46` | 652–681 | تابع | `traffic_notice_create` | 2 | ⬜ |
| `PAGE-081.47` | 683–694 | تابع | `traffic_destination_posted_approvers` | 2 | ⬜ |
| `PAGE-081.48` | 696–714 | تابع | `traffic_save_destination` | 3 | ⬜ |
| `PAGE-081.49` | 716–742 | تابع | `traffic_update_destination` | 3 | ⬜ |
| `PAGE-081.50` | 744–752 | تابع | `traffic_delete_destination` | 3 | ⬜ |
| `PAGE-081.51` | 754–763 | تابع | `traffic_form_old_values` | 2 | ⬜ |
| `PAGE-081.52` | 765–778 | تابع | `traffic_remember_form_error` | 2 | ⬜ |
| `PAGE-081.53` | 780–784 | تابع | `traffic_open_visits` | 0 | ⬜ |
| `PAGE-081.54` | 786–857 | تابع | `traffic_handle_post` | 2 | ⬜ |
| `PAGE-081.55` | 859–859 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-081.56` | 860–860 | ناحیه | `جست‌وجوی زنده با کد ملی (AJAX) — معادل txtNational` | 0 | ⬜ |
| `PAGE-081.57` | 861–861 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-081.58` | 862–886 | تابع | `traffic_history_rows_html` | 0 | ⬜ |
| `PAGE-081.59` | 888–976 | تابع | `traffic_api_handle` | 2 | ⬜ |
| `PAGE-081.60` | 978–978 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-081.61` | 979–979 | ناحیه | `صفحه‌ی اصلی: زیرفرم‌های داشبورد / ثبت تردد / سوابق` | 0 | ⬜ |
| `PAGE-081.62` | 980–980 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-081.63` | 981–1377 | تابع | `traffic_render_page` | 6 | ⬜ |
| `PAGE-081.64` | 1379–1379 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-081.65` | 1380–1380 | ناحیه | `چاپ برگ ملاقات — معادل PrintTemplate + cmdPrintPas` | 0 | ⬜ |
| `PAGE-081.66` | 1381–1381 | ناحیه | `-` | 0 | ⬜ |
| `PAGE-081.67` | 1382–1444 | تابع | `traffic_render_print` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-082</b> — <code>traffic-import.php</code> (1,032 خط، 27 آدرس، بازبینی‌شده 0/27)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-082.01` | 1–17 | ناحیه | `بلوک رویه‌ای 1-17` | 0 | ⬜ |
| `PAGE-082.02` | 18–41 | تابع | `traffic_import_column_aliases` | 0 | ⬜ |
| `PAGE-082.03` | 43–82 | تابع | `traffic_import_detect_header` | 0 | ⬜ |
| `PAGE-082.04` | 84–100 | تابع | `traffic_import_date_from_parts` | 0 | ⬜ |
| `PAGE-082.05` | 102–107 | تابع | `traffic_import_seconds_to_time` | 0 | ⬜ |
| `PAGE-082.06` | 109–166 | تابع | `traffic_import_parse_date_value` | 0 | ⬜ |
| `PAGE-082.07` | 168–220 | تابع | `traffic_import_parse_time` | 0 | ⬜ |
| `PAGE-082.08` | 222–251 | تابع | `traffic_import_parse_bool` | 0 | ⬜ |
| `PAGE-082.09` | 253–256 | تابع | `traffic_import_text_length` | 0 | ⬜ |
| `PAGE-082.10` | 258–264 | تابع | `traffic_import_optional_marker` | 0 | ⬜ |
| `PAGE-082.11` | 266–419 | تابع | `traffic_import_parse_sheet` | 1 | ⬜ |
| `PAGE-082.12` | 421–453 | تابع | `traffic_import_xlsx_archive_error` | 0 | ⬜ |
| `PAGE-082.13` | 455–521 | تابع | `traffic_import_csv_shape_error` | 1 | ⬜ |
| `PAGE-082.14` | 523–588 | تابع | `traffic_import_read_upload` | 0 | ⬜ |
| `PAGE-082.15` | 590–598 | تابع | `traffic_import_identity` | 0 | ⬜ |
| `PAGE-082.16` | 600–603 | تابع | `traffic_import_serial_key` | 0 | ⬜ |
| `PAGE-082.17` | 605–612 | تابع | `traffic_import_generated_serial` | 0 | ⬜ |
| `PAGE-082.18` | 614–618 | تابع | `traffic_import_generated_serial_prefix` | 0 | ⬜ |
| `PAGE-082.19` | 620–730 | تابع | `traffic_import_build_plan` | 1 | ⬜ |
| `PAGE-082.20` | 732–801 | تابع | `traffic_import_plan_from_database` | 6 | ⬜ |
| `PAGE-082.21` | 803–836 | تابع | `traffic_import_preview_session_data` | 0 | ⬜ |
| `PAGE-082.22` | 838–851 | تابع | `traffic_import_current_preview` | 0 | ⬜ |
| `PAGE-082.23` | 853–942 | تابع | `traffic_import_handle_post` | 2 | ⬜ |
| `PAGE-082.24` | 944–985 | تابع | `traffic_import_apply_plan_in_transaction` | 1 | ⬜ |
| `PAGE-082.25` | 987–993 | تابع | `traffic_import_template_headers` | 0 | ⬜ |
| `PAGE-082.26` | 995–1006 | تابع | `traffic_import_template_csv_bytes` | 0 | ⬜ |
| `PAGE-082.27` | 1008–1031 | تابع | `traffic_import_download_template` | 3 | ⬜ |

</details>

<details>
<summary><b>PAGE-083</b> — <code>user-access-groups.php</code> (209 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-083.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-083.02` | 4–28 | تابع | `user_access_groups_schema_ready` | 0 | ⬜ |
| `PAGE-083.03` | 30–49 | تابع | `user_access_groups_all` | 0 | ⬜ |
| `PAGE-083.04` | 51–60 | تابع | `user_access_groups_admin_users` | 0 | ⬜ |
| `PAGE-083.05` | 62–76 | تابع | `user_access_group_member_set` | 0 | ⬜ |
| `PAGE-083.06` | 78–96 | تابع | `user_access_groups_member_sets` | 0 | ⬜ |
| `PAGE-083.07` | 98–117 | تابع | `user_access_groups_permission_sets` | 0 | ⬜ |
| `PAGE-083.08` | 119–133 | تابع | `user_access_group_permission_set_for_group` | 0 | ⬜ |
| `PAGE-083.09` | 135–138 | تابع | `user_access_group_role_label` | 0 | ⬜ |
| `PAGE-083.10` | 140–199 | تابع | `user_access_group_permission_matrix_markup` | 0 | ⬜ |
| `PAGE-083.11` | 201–208 | تابع | `user_access_groups_redirect` | 0 | ⬜ |

</details>

<details>
<summary><b>PAGE-084</b> — <code>xlsx-writer.php</code> (250 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `PAGE-084.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `PAGE-084.02` | 4–27 | تابع | `xlsx_col_name` | 0 | ⬜ |
| `PAGE-084.03` | 29–39 | تابع | `xlsx_clean_text` | 0 | ⬜ |
| `PAGE-084.04` | 41–48 | تابع | `xlsx_sheet_name` | 0 | ⬜ |
| `PAGE-084.05` | 50–145 | تابع | `xlsx_build` | 6 | ⬜ |
| `PAGE-084.06` | 147–150 | تابع | `xlsx_xml` | 0 | ⬜ |
| `PAGE-084.07` | 152–236 | تابع | `xlsx_sheet_xml` | 0 | ⬜ |
| `PAGE-084.08` | 238–249 | تابع | `xlsx_send` | 3 | ⬜ |

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
<summary><b>ASSET-003</b> — <code>assets/app.js</code> (1,478 خط، 68 آدرس، بازبینی‌شده 0/68)</summary>

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
| `ASSET-003.49` | 1001–1008 | بلوک | `parseJalali` | 0 | ⬜ |
| `ASSET-003.50` | 1009–1019 | بلوک | `weekendIsoRaw` | 0 | ⬜ |
| `ASSET-003.51` | 1020–1022 | بلوک | `renderCalendar` | 0 | ⬜ |
| `ASSET-003.52` | 1023–1028 | بلوک | `startIndex` | 0 | ⬜ |
| `ASSET-003.53` | 1029–1056 | بلوک | `weekdayIndex` | 0 | ⬜ |
| `ASSET-003.54` | 1057–1069 | بلوک | `activate` | 4 | ⬜ |
| `ASSET-003.55` | 1070–1084 | بلوک | `hash` | 0 | ⬜ |
| `ASSET-003.56` | 1085–1090 | بلوک | `visibleBoxes` | 0 | ⬜ |
| `ASSET-003.57` | 1091–1117 | بلوک | `sync` | 0 | ⬜ |
| `ASSET-003.58` | 1118–1128 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-003.59` | 1129–1165 | بلوک | `apply` | 0 | ⬜ |
| `ASSET-003.60` | 1166–1198 | بلوک | `updateSelection` | 0 | ⬜ |
| `ASSET-003.61` | 1199–1260 | بلوک | `syncOrgFields` | 1 | ⬜ |
| `ASSET-003.62` | 1261–1298 | بلوک | `closeModal` | 0 | ⬜ |
| `ASSET-003.63` | 1299–1321 | بلوک | `activate` | 0 | ⬜ |
| `ASSET-003.64` | 1322–1355 | بلوک | `syncItem` | 0 | ⬜ |
| `ASSET-003.65` | 1356–1365 | بلوک | `پینگ سیستم‌های انتخاب‌شده: ارسال تکه‌تکه (۴۰ تایی)` | 0 | ⬜ |
| `ASSET-003.66` | 1366–1372 | بلوک | `setBadge` | 0 | ⬜ |
| `ASSET-003.67` | 1373–1390 | بلوک | `refreshOnlineTotal` | 0 | ⬜ |
| `ASSET-003.68` | 1391–1478 | بلوک | `next` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-004</b> — <code>assets/cd-dvd.js</code> (470 خط، 16 آدرس، بازبینی‌شده 0/16)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-004.01` | 1–43 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-004.02` | 44–71 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-004.03` | 72–86 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-004.04` | 87–97 | بلوک | `matchesSearch` | 0 | ⬜ |
| `ASSET-004.05` | 98–104 | بلوک | `searchUnchanged` | 0 | ⬜ |
| `ASSET-004.06` | 105–139 | بلوک | `filterUsers` | 0 | ⬜ |
| `ASSET-004.07` | 140–160 | بلوک | `selectUser` | 0 | ⬜ |
| `ASSET-004.08` | 161–225 | بلوک | `initializeUserPicker` | 0 | ⬜ |
| `ASSET-004.09` | 226–229 | بلوک | `getSelectedMedia` | 0 | ⬜ |
| `ASSET-004.10` | 230–233 | بلوک | `mediaBounds` | 0 | ⬜ |
| `ASSET-004.11` | 234–314 | بلوک | `updateForm` | 0 | ⬜ |
| `ASSET-004.12` | 315–374 | بلوک | `handleMediaChange` | 0 | ⬜ |
| `ASSET-004.13` | 375–404 | بلوک | `activateTab` | 0 | ⬜ |
| `ASSET-004.14` | 405–419 | بلوک | `printCdDvdList` | 0 | ⬜ |
| `ASSET-004.15` | 420–437 | بلوک | `cleanup` | 0 | ⬜ |
| `ASSET-004.16` | 438–470 | بلوک | `printCdDvdHistory` | 4 | ⬜ |

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
<summary><b>ASSET-007</b> — <code>assets/food-menu.css</code> (417 خط، 19 آدرس، بازبینی‌شده 0/19)</summary>

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
| `ASSET-007.16` | 355–378 | بلوک | `تعیین تکلیف غذای باقیمانده (تطبیق روزانه)` | 1 | ⬜ |
| `ASSET-007.17` | 379–405 | بلوک | `1.62.93 follow-up: cancel-request cutoff + compact` | 1 | ⬜ |
| `ASSET-007.18` | 406–413 | بلوک | `تفکیک غذای هم‌گروه: «چلوکباب ۴ · قیمه ۲»` | 0 | ⬜ |
| `ASSET-007.19` | 414–417 | بلوک | `جدول داخل هر گروه، مثل گروه منو بدون حاشیهٔ اضافه` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-008</b> — <code>assets/food-menu.js</code> (2,820 خط، 183 آدرس، بازبینی‌شده 0/183)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-008.01` | 1–107 | بلوک | `مدیر برنامهٔ غذایی در پنل چاپ فیش.` | 0 | ⬜ |
| `ASSET-008.02` | 108–112 | بلوک | `esc` | 0 | ⬜ |
| `ASSET-008.03` | 113–113 | بلوک | `faDigits` | 0 | ⬜ |
| `ASSET-008.04` | 114–118 | بلوک | `asciiDigits` | 0 | ⬜ |
| `ASSET-008.05` | 119–128 | بلوک | `isoFromJalali` | 0 | ⬜ |
| `ASSET-008.06` | 129–135 | بلوک | `jalaliFromIso` | 0 | ⬜ |
| `ASSET-008.07` | 136–142 | بلوک | `normalizeIsoDate` | 0 | ⬜ |
| `ASSET-008.08` | 143–146 | بلوک | `jalaliMonthFromIso` | 0 | ⬜ |
| `ASSET-008.09` | 147–150 | بلوک | `localTodayIso` | 0 | ⬜ |
| `ASSET-008.10` | 151–151 | بلوک | `monthKey` | 0 | ⬜ |
| `ASSET-008.11` | 152–152 | بلوک | `monthTitle` | 0 | ⬜ |
| `ASSET-008.12` | 153–153 | بلوک | `getDay` | 0 | ⬜ |
| `ASSET-008.13` | 154–157 | بلوک | `activeOrderCount` | 0 | ⬜ |
| `ASSET-008.14` | 158–167 | بلوک | `holiday` | 0 | ⬜ |
| `ASSET-008.15` | 168–173 | بلوک | `isWeekendIso` | 0 | ⬜ |
| `ASSET-008.16` | 174–178 | بلوک | `holidayOrWeekend` | 0 | ⬜ |
| `ASSET-008.17` | 179–184 | بلوک | `buildUrl` | 0 | ⬜ |
| `ASSET-008.18` | 185–202 | بلوک | `request` | 0 | ⬜ |
| `ASSET-008.19` | 203–209 | بلوک | `notify` | 0 | ⬜ |
| `ASSET-008.20` | 210–215 | بلوک | `navTabs` | 0 | ⬜ |
| `ASSET-008.21` | 216–225 | بلوک | `cancelCount` | 0 | ⬜ |
| `ASSET-008.22` | 226–258 | بلوک | `renderCalendar` | 0 | ⬜ |
| `ASSET-008.23` | 259–276 | بلوک | `renderDayEditor` | 0 | ⬜ |
| `ASSET-008.24` | 277–286 | بلوک | `choiceHtml` | 0 | ⬜ |
| `ASSET-008.25` | 287–312 | بلوک | `foods` | 0 | ⬜ |
| `ASSET-008.26` | 313–318 | بلوک | `categoryOptions` | 0 | ⬜ |
| `ASSET-008.27` | 319–327 | بلوک | `catalogCategoryNames` | 0 | ⬜ |
| `ASSET-008.28` | 328–337 | بلوک | `foodCardHtml` | 0 | ⬜ |
| `ASSET-008.29` | 338–380 | بلوک | `renderCatalog` | 0 | ⬜ |
| `ASSET-008.30` | 381–392 | بلوک | `readSavedOrderReportFields` | 0 | ⬜ |
| `ASSET-008.31` | 393–398 | بلوک | `saveOrderReportFields` | 4 | ⬜ |
| `ASSET-008.32` | 399–403 | بلوک | `selectedOrderColumns` | 0 | ⬜ |
| `ASSET-008.33` | 404–421 | بلوک | `orderFieldValue` | 0 | ⬜ |
| `ASSET-008.34` | 422–431 | بلوک | `orderSortValue` | 0 | ⬜ |
| `ASSET-008.35` | 432–447 | بلوک | `sortOrderRows` | 0 | ⬜ |
| `ASSET-008.36` | 448–485 | بلوک | `orderTableHtml` | 0 | ⬜ |
| `ASSET-008.37` | 486–522 | بلوک | `orderByMenuHtml` | 0 | ⬜ |
| `ASSET-008.38` | 523–534 | بلوک | `readDeliveredReportFields` | 0 | ⬜ |
| `ASSET-008.39` | 535–539 | بلوک | `saveDeliveredReportFields` | 4 | ⬜ |
| `ASSET-008.40` | 540–554 | بلوک | `deliveredReportFieldValue` | 0 | ⬜ |
| `ASSET-008.41` | 555–558 | بلوک | `selectedDeliveredReportColumns` | 0 | ⬜ |
| `ASSET-008.42` | 559–571 | بلوک | `sortDeliveredReportRows` | 0 | ⬜ |
| `ASSET-008.43` | 572–602 | بلوک | `deliveredReportTableHtml` | 0 | ⬜ |
| `ASSET-008.44` | 603–605 | بلوک | `deliveredReportByMenuHtml` | 0 | ⬜ |
| `ASSET-008.45` | 606–631 | بلوک | `key` | 0 | ⬜ |
| `ASSET-008.46` | 632–645 | بلوک | `deliveredReportByOrgHtml` | 0 | ⬜ |
| `ASSET-008.47` | 646–654 | بلوک | `requestOrgMap` | 0 | ⬜ |
| `ASSET-008.48` | 655–659 | بلوک | `deliveredReportOrderedRows` | 0 | ⬜ |
| `ASSET-008.49` | 660–672 | بلوک | `key` | 0 | ⬜ |
| `ASSET-008.50` | 673–676 | بلوک | `deliveryDateForReport` | 0 | ⬜ |
| `ASSET-008.51` | 677–696 | بلوک | `renderPrintedDeliveryReport` | 0 | ⬜ |
| `ASSET-008.52` | 697–732 | بلوک | `loadPrintedDeliveryReport` | 0 | ⬜ |
| `ASSET-008.53` | 733–749 | بلوک | `exportPrintedDeliveryReport` | 0 | ⬜ |
| `ASSET-008.54` | 750–754 | بلوک | `printPrintedDeliveryReport` | 0 | ⬜ |
| `ASSET-008.55` | 755–760 | بلوک | `summaryHtml` | 0 | ⬜ |
| `ASSET-008.56` | 761–769 | بلوک | `nameSplit` | 0 | ⬜ |
| `ASSET-008.57` | 770–777 | بلوک | `personSortValue` | 0 | ⬜ |
| `ASSET-008.58` | 778–788 | بلوک | `sortByState` | 0 | ⬜ |
| `ASSET-008.59` | 789–794 | بلوک | `sortStateToggle` | 0 | ⬜ |
| `ASSET-008.60` | 795–798 | بلوک | `sortStateMark` | 0 | ⬜ |
| `ASSET-008.61` | 799–802 | بلوک | `rowSummary` | 0 | ⬜ |
| `ASSET-008.62` | 803–808 | بلوک | `renderOrders` | 0 | ⬜ |
| `ASSET-008.63` | 809–829 | بلوک | `viewButton` | 0 | ⬜ |
| `ASSET-008.64` | 830–843 | بلوک | `ordersByOrgHtml` | 0 | ⬜ |
| `ASSET-008.65` | 844–852 | بلوک | `renderGroupOrders` | 0 | ⬜ |
| `ASSET-008.66` | 853–877 | بلوک | `tableFor` | 0 | ⬜ |
| `ASSET-008.67` | 878–885 | بلوک | `renderCancelRequests` | 0 | ⬜ |
| `ASSET-008.68` | 886–898 | بلوک | `splitRow` | 0 | ⬜ |
| `ASSET-008.69` | 899–908 | بلوک | `cancelValue` | 0 | ⬜ |
| `ASSET-008.70` | 909–911 | بلوک | `cmp` | 0 | ⬜ |
| `ASSET-008.71` | 912–930 | بلوک | `cancelHead` | 0 | ⬜ |
| `ASSET-008.72` | 931–949 | بلوک | `renderStatistics` | 0 | ⬜ |
| `ASSET-008.73` | 950–959 | بلوک | `mark` | 0 | ⬜ |
| `ASSET-008.74` | 960–983 | بلوک | `peopleSortMark` | 0 | ⬜ |
| `ASSET-008.75` | 984–1009 | بلوک | `foods` | 0 | ⬜ |
| `ASSET-008.76` | 1010–1018 | بلوک | `normalizeJalaliMonth` | 0 | ⬜ |
| `ASSET-008.77` | 1019–1028 | بلوک | `reconciliationGapCell` | 0 | ⬜ |
| `ASSET-008.78` | 1029–1037 | بلوک | `reconciliationRemainingCell` | 0 | ⬜ |
| `ASSET-008.79` | 1038–1044 | بلوک | `reconciliationLeftoverCell` | 0 | ⬜ |
| `ASSET-008.80` | 1045–1048 | بلوک | `reconciliationNumber` | 0 | ⬜ |
| `ASSET-008.81` | 1049–1053 | بلوک | `reconciliationAmount` | 0 | ⬜ |
| `ASSET-008.82` | 1054–1128 | بلوک | `renderReconciliation` | 0 | ⬜ |
| `ASSET-008.83` | 1129–1134 | بلوک | `renderLeftoverModal` | 0 | ⬜ |
| `ASSET-008.84` | 1135–1154 | بلوک | `row` | 0 | ⬜ |
| `ASSET-008.85` | 1155–1155 | بلوک | `leftoverPreviewFor` | 0 | ⬜ |
| `ASSET-008.86` | 1156–1171 | بلوک | `items` | 0 | ⬜ |
| `ASSET-008.87` | 1172–1177 | بلوک | `leftoverInlineError` | 0 | ⬜ |
| `ASSET-008.88` | 1178–1221 | بلوک | `confirmLeftoverModal` | 0 | ⬜ |
| `ASSET-008.89` | 1222–1229 | بلوک | `submitReconciliationBody` | 0 | ⬜ |
| `ASSET-008.90` | 1230–1236 | بلوک | `message` | 0 | ⬜ |
| `ASSET-008.91` | 1237–1245 | بلوک | `postReconciliation` | 0 | ⬜ |
| `ASSET-008.92` | 1246–1286 | بلوک | `loadReconciliation` | 0 | ⬜ |
| `ASSET-008.93` | 1287–1292 | بلوک | `downloadReconciliation` | 0 | ⬜ |
| `ASSET-008.94` | 1293–1293 | بلوک | `selectReconciliationRow` | 0 | ⬜ |
| `ASSET-008.95` | 1294–1310 | بلوک | `item` | 0 | ⬜ |
| `ASSET-008.96` | 1311–1344 | بلوک | `saveReconciliationForm` | 0 | ⬜ |
| `ASSET-008.97` | 1345–1381 | بلوک | `uploadReconciliationInvoice` | 0 | ⬜ |
| `ASSET-008.98` | 1382–1385 | بلوک | `bulkOperationTitle` | 0 | ⬜ |
| `ASSET-008.99` | 1386–1390 | بلوک | `bulkSelectedPeople` | 0 | ⬜ |
| `ASSET-008.100` | 1391–1394 | بلوک | `bulkTargetValue` | 0 | ⬜ |
| `ASSET-008.101` | 1395–1401 | بلوک | `bulkCanSelect` | 0 | ⬜ |
| `ASSET-008.102` | 1402–1412 | بلوک | `group` | 0 | ⬜ |
| `ASSET-008.103` | 1413–1429 | بلوک | `saveCancelCutoff` | 0 | ⬜ |
| `ASSET-008.104` | 1430–1458 | بلوک | `loadBulkSnapshot` | 0 | ⬜ |
| `ASSET-008.105` | 1459–1473 | بلوک | `bulkTargetOptions` | 0 | ⬜ |
| `ASSET-008.106` | 1474–1489 | بلوک | `bulkPersonStatus` | 0 | ⬜ |
| `ASSET-008.107` | 1490–1511 | بلوک | `group` | 0 | ⬜ |
| `ASSET-008.108` | 1512–1517 | بلوک | `renderBulkResults` | 0 | ⬜ |
| `ASSET-008.109` | 1518–1525 | بلوک | `renderBulkModal` | 0 | ⬜ |
| `ASSET-008.110` | 1526–1543 | بلوک | `renderBulkOperations` | 0 | ⬜ |
| `ASSET-008.111` | 1544–1549 | بلوک | `rows` | 0 | ⬜ |
| `ASSET-008.112` | 1550–1562 | بلوک | `bulkValue` | 0 | ⬜ |
| `ASSET-008.113` | 1563–1568 | بلوک | `bulkHead` | 0 | ⬜ |
| `ASSET-008.114` | 1569–1582 | بلوک | `selected` | 0 | ⬜ |
| `ASSET-008.115` | 1583–1593 | بلوک | `previewDisabled` | 0 | ⬜ |
| `ASSET-008.116` | 1594–1604 | بلوک | `bulkTogglePerson` | 1 | ⬜ |
| `ASSET-008.117` | 1605–1606 | بلوک | `bulkSelectEligible` | 0 | ⬜ |
| `ASSET-008.118` | 1607–1612 | بلوک | `eligible` | 0 | ⬜ |
| `ASSET-008.119` | 1613–1615 | بلوک | `submitBulkOperation` | 0 | ⬜ |
| `ASSET-008.120` | 1616–1635 | بلوک | `ids` | 0 | ⬜ |
| `ASSET-008.121` | 1636–1641 | بلوک | `mealTypeSwitch` | 0 | ⬜ |
| `ASSET-008.122` | 1642–1649 | بلوک | `embeddedPanel` | 0 | ⬜ |
| `ASSET-008.123` | 1650–1669 | بلوک | `loadEmbedded` | 0 | ⬜ |
| `ASSET-008.124` | 1670–1677 | بلوک | `renderFocusKeyOf` | 0 | ⬜ |
| `ASSET-008.125` | 1678–1689 | بلوک | `captureRenderFocus` | 4 | ⬜ |
| `ASSET-008.126` | 1690–1702 | بلوک | `restoreRenderFocus` | 4 | ⬜ |
| `ASSET-008.127` | 1703–1714 | بلوک | `render` | 0 | ⬜ |
| `ASSET-008.128` | 1715–1721 | بلوک | `notices` | 0 | ⬜ |
| `ASSET-008.129` | 1722–1733 | بلوک | `loadStatus` | 0 | ⬜ |
| `ASSET-008.130` | 1734–1740 | بلوک | `loadCatalog` | 0 | ⬜ |
| `ASSET-008.131` | 1741–1748 | بلوک | `loadMonth` | 0 | ⬜ |
| `ASSET-008.132` | 1749–1770 | بلوک | `loadOrders` | 0 | ⬜ |
| `ASSET-008.133` | 1771–1791 | بلوک | `loadFoodGroups` | 0 | ⬜ |
| `ASSET-008.134` | 1792–1822 | بلوک | `loadGroupOrders` | 0 | ⬜ |
| `ASSET-008.135` | 1823–1839 | بلوک | `loadCancelRequests` | 0 | ⬜ |
| `ASSET-008.136` | 1840–1858 | بلوک | `loadGuestRequests` | 0 | ⬜ |
| `ASSET-008.137` | 1859–1859 | بلوک | `beginGuestEdit` | 0 | ⬜ |
| `ASSET-008.138` | 1860–1875 | بلوک | `row` | 0 | ⬜ |
| `ASSET-008.139` | 1876–1881 | بلوک | `cancelGuestEdit` | 0 | ⬜ |
| `ASSET-008.140` | 1882–1913 | بلوک | `saveGuestEdit` | 1 | ⬜ |
| `ASSET-008.141` | 1914–1914 | بلوک | `guestRequestAction` | 0 | ⬜ |
| `ASSET-008.142` | 1915–1937 | بلوک | `row` | 1 | ⬜ |
| `ASSET-008.143` | 1938–1940 | بلوک | `renderGuestRequestManager` | 0 | ⬜ |
| `ASSET-008.144` | 1941–1963 | بلوک | `rows` | 1 | ⬜ |
| `ASSET-008.145` | 1964–1978 | بلوک | `decideCancelRequest` | 0 | ⬜ |
| `ASSET-008.146` | 1979–1985 | بلوک | `loadStatistics` | 0 | ⬜ |
| `ASSET-008.147` | 1986–2019 | بلوک | `selectedIds` | 0 | ⬜ |
| `ASSET-008.148` | 2020–2047 | بلوک | `searchSelectedReportEmployees` | 0 | ⬜ |
| `ASSET-008.149` | 2048–2074 | بلوک | `refreshAll` | 0 | ⬜ |
| `ASSET-008.150` | 2075–2119 | بلوک | `saveDay` | 0 | ⬜ |
| `ASSET-008.151` | 2120–2145 | بلوک | `saveCatalog` | 0 | ⬜ |
| `ASSET-008.152` | 2146–2165 | بلوک | `orderExportRows` | 0 | ⬜ |
| `ASSET-008.153` | 2166–2182 | بلوک | `downloadOrdersCsv` | 0 | ⬜ |
| `ASSET-008.154` | 2183–2187 | بلوک | `downloadOrders` | 0 | ⬜ |
| `ASSET-008.155` | 2188–2189 | بلوک | `printScopedReport` | 0 | ⬜ |
| `ASSET-008.156` | 2190–2200 | بلوک | `target` | 0 | ⬜ |
| `ASSET-008.157` | 2201–2218 | بلوک | `cleanup` | 0 | ⬜ |
| `ASSET-008.158` | 2219–2223 | بلوک | `printOrders` | 0 | ⬜ |
| `ASSET-008.159` | 2224–2230 | بلوک | `downloadStatistics` | 0 | ⬜ |
| `ASSET-008.160` | 2231–2235 | بلوک | `printStatistics` | 0 | ⬜ |
| `ASSET-008.161` | 2236–2243 | بلوک | `selectDay` | 0 | ⬜ |
| `ASSET-008.162` | 2244–2249 | بلوک | `changeMonth` | 0 | ⬜ |
| `ASSET-008.163` | 2250–2286 | بلوک | `switchTab` | 0 | ⬜ |
| `ASSET-008.164` | 2287–2300 | بلوک | `emergencyEnsurePeople` | 0 | ⬜ |
| `ASSET-008.165` | 2301–2309 | بلوک | `emergencyPanel` | 0 | ⬜ |
| `ASSET-008.166` | 2310–2344 | بلوک | `msgs` | 0 | ⬜ |
| `ASSET-008.167` | 2345–2350 | بلوک | `emergencyLoadOrders` | 0 | ⬜ |
| `ASSET-008.168` | 2351–2395 | بلوک | `daySearchInput` | 4 | ⬜ |
| `ASSET-008.169` | 2396–2406 | بلوک | `emergencyClick` | 0 | ⬜ |
| `ASSET-008.170` | 2407–2435 | بلوک | `person` | 0 | ⬜ |
| `ASSET-008.171` | 2436–2483 | بلوک | `eventClick` | 0 | ⬜ |
| `ASSET-008.172` | 2484–2593 | بلوک | `person` | 1 | ⬜ |
| `ASSET-008.173` | 2594–2602 | بلوک | `dayPickFor` | 0 | ⬜ |
| `ASSET-008.174` | 2603–2609 | بلوک | `trackDayPick` | 0 | ⬜ |
| `ASSET-008.175` | 2610–2617 | بلوک | `orderedDayFoodIds` | 0 | ⬜ |
| `ASSET-008.176` | 2618–2654 | بلوک | `eventChange` | 0 | ⬜ |
| `ASSET-008.177` | 2655–2688 | بلوک | `person` | 0 | ⬜ |
| `ASSET-008.178` | 2689–2771 | بلوک | `eventSubmit` | 0 | ⬜ |
| `ASSET-008.179` | 2772–2777 | بلوک | `spanDays` | 0 | ⬜ |
| `ASSET-008.180` | 2778–2806 | بلوک | `mount` | 0 | ⬜ |
| `ASSET-008.181` | 2807–2807 | بلوک | `reportInputFocused` | 0 | ⬜ |
| `ASSET-008.182` | 2808–2810 | بلوک | `safeReportRender` | 0 | ⬜ |
| `ASSET-008.183` | 2811–2820 | بلوک | `html` | 0 | ⬜ |

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
<summary><b>ASSET-011</b> — <code>assets/food-order.css</code> (218 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-011.01` | 1–111 | بلوک | `سفارش غذا — از همان رنگ‌ها، کارت‌ها و فرم‌های asse` | 2 | ⬜ |
| `ASSET-011.02` | 112–116 | بلوک | `تأیید عملیات در DOM پایدار می‌ماند تا بازخوانی تقو` | 0 | ⬜ |
| `ASSET-011.03` | 117–169 | بلوک | `انتخاب شخص پیش از فعال‌شدن تقویم؛ منوی اصلی سامانه` | 1 | ⬜ |
| `ASSET-011.04` | 170–175 | بلوک | `نام غذا در سلول تقویم در یک خط نمایش داده می‌شود؛ ` | 0 | ⬜ |
| `ASSET-011.05` | 176–178 | بلوک | `Separate lunch and dinner without changing the exi` | 0 | ⬜ |
| `ASSET-011.06` | 179–179 | بلوک | `فهرست شخصی درخواست مهمان: فضای محدود، خوانا و مستق` | 0 | ⬜ |
| `ASSET-011.07` | 180–191 | بلوک | `A single compact row per request. The table scroll` | 0 | ⬜ |
| `ASSET-011.08` | 192–214 | بلوک | `Daily food reconciliation and monthly invoice reco` | 2 | ⬜ |
| `ASSET-011.09` | 215–218 | بلوک | `1.62.93 follow-up: single-line sortable header but` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-012</b> — <code>assets/food-order.js</code> (1,213 خط، 74 آدرس، بازبینی‌شده 0/74)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-012.01` | 1–14 | بلوک | `صفحهٔ کارمند: ثبت سفارش برای خود/نیابت، بدون افشای` | 0 | ⬜ |
| `ASSET-012.02` | 15–66 | بلوک | `apiBase` | 0 | ⬜ |
| `ASSET-012.03` | 67–71 | بلوک | `esc` | 0 | ⬜ |
| `ASSET-012.04` | 72–72 | بلوک | `faDigits` | 0 | ⬜ |
| `ASSET-012.05` | 73–78 | بلوک | `normalizedDigits` | 0 | ⬜ |
| `ASSET-012.06` | 79–82 | بلوک | `normalizeSearch` | 0 | ⬜ |
| `ASSET-012.07` | 83–88 | بلوک | `employeeMatches` | 0 | ⬜ |
| `ASSET-012.08` | 89–89 | بلوک | `monthKey` | 0 | ⬜ |
| `ASSET-012.09` | 90–90 | بلوک | `monthLabel` | 0 | ⬜ |
| `ASSET-012.10` | 91–96 | بلوک | `isoToJalali` | 0 | ⬜ |
| `ASSET-012.11` | 97–106 | بلوک | `holidayTitle` | 0 | ⬜ |
| `ASSET-012.12` | 107–114 | بلوک | `holidayInfo` | 0 | ⬜ |
| `ASSET-012.13` | 115–117 | بلوک | `isPast` | 0 | ⬜ |
| `ASSET-012.14` | 118–128 | بلوک | `tehranNowMinutes` | 0 | ⬜ |
| `ASSET-012.15` | 129–133 | بلوک | `cancelCutoffMinutes` | 4 | ⬜ |
| `ASSET-012.16` | 134–137 | بلوک | `todayCancelRequestClosed` | 0 | ⬜ |
| `ASSET-012.17` | 138–143 | بلوک | `personSplit` | 0 | ⬜ |
| `ASSET-012.18` | 144–153 | بلوک | `tableSortValue` | 0 | ⬜ |
| `ASSET-012.19` | 154–155 | بلوک | `sortedTableRows` | 0 | ⬜ |
| `ASSET-012.20` | 156–156 | بلوک | `dir` | 0 | ⬜ |
| `ASSET-012.21` | 157–164 | بلوک | `key` | 0 | ⬜ |
| `ASSET-012.22` | 165–169 | بلوک | `foSortTh` | 0 | ⬜ |
| `ASSET-012.23` | 170–182 | بلوک | `request` | 0 | ⬜ |
| `ASSET-012.24` | 183–192 | بلوک | `toast` | 0 | ⬜ |
| `ASSET-012.25` | 193–196 | بلوک | `visitParams` | 0 | ⬜ |
| `ASSET-012.26` | 197–204 | بلوک | `visitPayload` | 0 | ⬜ |
| `ASSET-012.27` | 205–216 | بلوک | `loadVisit` | 0 | ⬜ |
| `ASSET-012.28` | 217–223 | بلوک | `openVisitDialog` | 0 | ⬜ |
| `ASSET-012.29` | 224–227 | بلوک | `closeVisitDialog` | 0 | ⬜ |
| `ASSET-012.30` | 228–244 | بلوک | `saveVisit` | 0 | ⬜ |
| `ASSET-012.31` | 245–258 | بلوک | `cancelVisit` | 0 | ⬜ |
| `ASSET-012.32` | 259–310 | بلوک | `renderVisitDialog` | 0 | ⬜ |
| `ASSET-012.33` | 311–330 | بلوک | `loadMonth` | 0 | ⬜ |
| `ASSET-012.34` | 331–347 | بلوک | `loadMyOrders` | 0 | ⬜ |
| `ASSET-012.35` | 348–377 | بلوک | `loadProxyOrders` | 0 | ⬜ |
| `ASSET-012.36` | 378–385 | بلوک | `applyListRange` | 0 | ⬜ |
| `ASSET-012.37` | 386–399 | بلوک | `renderListFilter` | 0 | ⬜ |
| `ASSET-012.38` | 400–409 | بلوک | `dayStatus` | 0 | ⬜ |
| `ASSET-012.39` | 410–415 | بلوک | `guestOrderButton` | 0 | ⬜ |
| `ASSET-012.40` | 416–450 | بلوک | `renderCalendar` | 0 | ⬜ |
| `ASSET-012.41` | 451–464 | بلوک | `renderOrders` | 0 | ⬜ |
| `ASSET-012.42` | 465–466 | بلوک | `renderCancelRequestControl` | 0 | ⬜ |
| `ASSET-012.43` | 467–486 | بلوک | `closed` | 0 | ⬜ |
| `ASSET-012.44` | 487–498 | بلوک | `renderProxyCancelControl` | 0 | ⬜ |
| `ASSET-012.45` | 499–500 | بلوک | `renderProxyOrders` | 0 | ⬜ |
| `ASSET-012.46` | 501–537 | بلوک | `rows` | 0 | ⬜ |
| `ASSET-012.47` | 538–541 | بلوک | `currentDay` | 0 | ⬜ |
| `ASSET-012.48` | 542–551 | بلوک | `modalReadOnlyMessage` | 0 | ⬜ |
| `ASSET-012.49` | 552–563 | بلوک | `renderEmployeeChoices` | 0 | ⬜ |
| `ASSET-012.50` | 564–587 | بلوک | `renderRecipientDialog` | 0 | ⬜ |
| `ASSET-012.51` | 588–598 | بلوک | `renderRecipientBanner` | 0 | ⬜ |
| `ASSET-012.52` | 599–640 | بلوک | `renderModal` | 0 | ⬜ |
| `ASSET-012.53` | 641–675 | بلوک | `render` | 0 | ⬜ |
| `ASSET-012.54` | 676–681 | بلوک | `setMonth` | 0 | ⬜ |
| `ASSET-012.55` | 682–690 | بلوک | `openDay` | 0 | ⬜ |
| `ASSET-012.56` | 691–699 | بلوک | `closeModal` | 0 | ⬜ |
| `ASSET-012.57` | 700–704 | بلوک | `setModalError` | 0 | ⬜ |
| `ASSET-012.58` | 705–715 | بلوک | `focusSearchInput` | 4 | ⬜ |
| `ASSET-012.59` | 716–733 | بلوک | `activateRecipient` | 0 | ⬜ |
| `ASSET-012.60` | 734–753 | بلوک | `startRecipientChoice` | 0 | ⬜ |
| `ASSET-012.61` | 754–772 | بلوک | `dismissRecipientFlow` | 0 | ⬜ |
| `ASSET-012.62` | 773–790 | بلوک | `beginProxyChoice` | 0 | ⬜ |
| `ASSET-012.63` | 791–810 | بلوک | `loadEmployeeChoices` | 0 | ⬜ |
| `ASSET-012.64` | 811–824 | بلوک | `selectProxyPerson` | 0 | ⬜ |
| `ASSET-012.65` | 825–844 | بلوک | `updateProxySearch` | 4 | ⬜ |
| `ASSET-012.66` | 845–874 | بلوک | `confirmProxyRecipient` | 0 | ⬜ |
| `ASSET-012.67` | 875–930 | بلوک | `saveOrder` | 0 | ⬜ |
| `ASSET-012.68` | 931–942 | بلوک | `requestProxyCancelConfirmation` | 0 | ⬜ |
| `ASSET-012.69` | 943–948 | بلوک | `dismissProxyCancelConfirmation` | 0 | ⬜ |
| `ASSET-012.70` | 949–981 | بلوک | `cancelProxyOrder` | 0 | ⬜ |
| `ASSET-012.71` | 982–995 | بلوک | `cancelOrder` | 0 | ⬜ |
| `ASSET-012.72` | 996–998 | بلوک | `markCancelPending` | 0 | ⬜ |
| `ASSET-012.73` | 999–1010 | بلوک | `patch` | 0 | ⬜ |
| `ASSET-012.74` | 1011–1213 | بلوک | `requestCancelOrder` | 5 | ⬜ |

</details>

<details>
<summary><b>ASSET-013</b> — <code>assets/food-org-groups.js</code> (293 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-013.01` | 1–25 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-013.02` | 26–37 | بلوک | `typeLabel` | 0 | ⬜ |
| `ASSET-013.03` | 38–52 | بلوک | `typeOrder` | 0 | ⬜ |
| `ASSET-013.04` | 53–83 | بلوک | `load` | 0 | ⬜ |
| `ASSET-013.05` | 84–93 | بلوک | `invalidate` | 0 | ⬜ |
| `ASSET-013.06` | 94–114 | بلوک | `keysForRow` | 0 | ⬜ |
| `ASSET-013.07` | 115–123 | بلوک | `digits` | 0 | ⬜ |
| `ASSET-013.08` | 124–141 | بلوک | `groupOf` | 0 | ⬜ |
| `ASSET-013.09` | 142–181 | بلوک | `split` | 0 | ⬜ |
| `ASSET-013.10` | 182–198 | بلوک | `groupHeaderHtml` | 0 | ⬜ |
| `ASSET-013.11` | 199–224 | بلوک | `foodBreakdown` | 0 | ⬜ |
| `ASSET-013.12` | 225–239 | بلوک | `uniquePeopleCount` | 0 | ⬜ |
| `ASSET-013.13` | 240–245 | بلوک | `faNum` | 0 | ⬜ |
| `ASSET-013.14` | 246–258 | بلوک | `escapeHtml` | 0 | ⬜ |
| `ASSET-013.15` | 259–293 | بلوک | `render` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-014</b> — <code>assets/inspection-food-reminder.js</code> (114 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-014.01` | 1–17 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-014.02` | 18–21 | بلوک | `nowSeconds` | 0 | ⬜ |
| `ASSET-014.03` | 22–39 | بلوک | `dayKey` | 0 | ⬜ |
| `ASSET-014.04` | 40–47 | بلوک | `wasShown` | 0 | ⬜ |
| `ASSET-014.05` | 48–53 | بلوک | `markShown` | 4 | ⬜ |
| `ASSET-014.06` | 54–76 | بلوک | `playAlarm` | 4 | ⬜ |
| `ASSET-014.07` | 77–86 | بلوک | `show` | 0 | ⬜ |
| `ASSET-014.08` | 87–92 | بلوک | `hide` | 0 | ⬜ |
| `ASSET-014.09` | 93–114 | بلوک | `schedule` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-015</b> — <code>assets/jalali-calendar.js</code> (347 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-015.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-015.02` | 13–14 | بلوک | `pad` | 0 | ⬜ |
| `ASSET-015.03` | 15–44 | بلوک | `j2g` | 0 | ⬜ |
| `ASSET-015.04` | 45–66 | بلوک | `g2j` | 0 | ⬜ |
| `ASSET-015.05` | 67–73 | بلوک | `monthLength` | 0 | ⬜ |
| `ASSET-015.06` | 74–81 | بلوک | `parseJalali` | 0 | ⬜ |
| `ASSET-015.07` | 82–85 | بلوک | `formatJalali` | 0 | ⬜ |
| `ASSET-015.08` | 86–99 | بلوک | `holidaySet` | 0 | ⬜ |
| `ASSET-015.09` | 100–100 | بلوک | `weekendDays` | 0 | ⬜ |
| `ASSET-015.10` | 101–109 | بلوک | `raw` | 0 | ⬜ |
| `ASSET-015.11` | 110–115 | بلوک | `isWeekendJalali` | 0 | ⬜ |
| `ASSET-015.12` | 116–120 | بلوک | `todayJ` | 0 | ⬜ |
| `ASSET-015.13` | 121–134 | بلوک | `bindPicker` | 0 | ⬜ |
| `ASSET-015.14` | 135–135 | بلوک | `close` | 0 | ⬜ |
| `ASSET-015.15` | 136–137 | بلوک | `open` | 0 | ⬜ |
| `ASSET-015.16` | 138–143 | بلوک | `render` | 0 | ⬜ |
| `ASSET-015.17` | 144–215 | بلوک | `startIndex` | 0 | ⬜ |
| `ASSET-015.18` | 216–227 | بلوک | `installOutsideClickHandler` | 0 | ⬜ |
| `ASSET-015.19` | 228–234 | بلوک | `bindYearBoard` | 0 | ⬜ |
| `ASSET-015.20` | 235–243 | بلوک | `render` | 0 | ⬜ |
| `ASSET-015.21` | 244–280 | بلوک | `startIndex` | 0 | ⬜ |
| `ASSET-015.22` | 281–347 | بلوک | `enhanceAllDateFields` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-016</b> — <code>assets/list-sort.js</code> (323 خط، 23 آدرس، بازبینی‌شده 0/23)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-016.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-016.02` | 16–19 | بلوک | `collator` | 0 | ⬜ |
| `ASSET-016.03` | 20–32 | بلوک | `injectStyle` | 0 | ⬜ |
| `ASSET-016.04` | 33–41 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-016.05` | 42–44 | بلوک | `pad` | 0 | ⬜ |
| `ASSET-016.06` | 45–55 | بلوک | `sortKey` | 4 | ⬜ |
| `ASSET-016.07` | 56–64 | بلوک | `compare` | 0 | ⬜ |
| `ASSET-016.08` | 65–84 | بلوک | `collect` | 0 | ⬜ |
| `ASSET-016.09` | 85–98 | بلوک | `hasAny` | 0 | ⬜ |
| `ASSET-016.10` | 99–112 | بلوک | `anchorFor` | 0 | ⬜ |
| `ASSET-016.11` | 113–140 | بلوک | `applySort` | 0 | ⬜ |
| `ASSET-016.12` | 141–156 | بلوک | `updateHeaders` | 0 | ⬜ |
| `ASSET-016.13` | 157–190 | بلوک | `attach` | 0 | ⬜ |
| `ASSET-016.14` | 191–205 | بلوک | `enhance` | 0 | ⬜ |
| `ASSET-016.15` | 206–215 | بلوک | `scheduleEnhance` | 0 | ⬜ |
| `ASSET-016.16` | 216–234 | بلوک | `buildView` | 0 | ⬜ |
| `ASSET-016.17` | 235–241 | بلوک | `fileLabel` | 0 | ⬜ |
| `ASSET-016.18` | 242–252 | بلوک | `saveBlob` | 0 | ⬜ |
| `ASSET-016.19` | 253–269 | بلوک | `exportXlsx` | 0 | ⬜ |
| `ASSET-016.20` | 270–277 | بلوک | `exportContainer` | 0 | ⬜ |
| `ASSET-016.21` | 278–291 | بلوک | `activeListFor` | 0 | ⬜ |
| `ASSET-016.22` | 292–302 | بلوک | `onExportClick` | 0 | ⬜ |
| `ASSET-016.23` | 303–323 | بلوک | `start` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-017</b> — <code>assets/logging-dashboard.css</code> (19 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-017.01` | 1–19 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |

</details>

<details>
<summary><b>ASSET-018</b> — <code>assets/logging-dashboard.js</code> (20 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-018.01` | 1–20 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-019</b> — <code>assets/organization.js</code> (1,039 خط، 104 آدرس، بازبینی‌شده 0/104)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-019.01` | 1–5 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-019.02` | 6–7 | بلوک | `users` | 0 | ⬜ |
| `ASSET-019.03` | 8–8 | بلوک | `$` | 0 | ⬜ |
| `ASSET-019.04` | 9–9 | بلوک | `$$` | 0 | ⬜ |
| `ASSET-019.05` | 10–14 | بلوک | `esc` | 0 | ⬜ |
| `ASSET-019.06` | 15–15 | بلوک | `initial` | 0 | ⬜ |
| `ASSET-019.07` | 16–37 | بلوک | `iconFor` | 1 | ⬜ |
| `ASSET-019.08` | 38–38 | بلوک | `unit` | 0 | ⬜ |
| `ASSET-019.09` | 39–39 | بلوک | `person` | 0 | ⬜ |
| `ASSET-019.10` | 40–40 | بلوک | `rootType` | 0 | ⬜ |
| `ASSET-019.11` | 41–41 | بلوک | `rootLabel` | 0 | ⬜ |
| `ASSET-019.12` | 42–48 | بلوک | `root` | 4 | ⬜ |
| `ASSET-019.13` | 49–59 | بلوک | `freeNodeKey` | 0 | ⬜ |
| `ASSET-019.14` | 60–64 | بلوک | `freePosition` | 0 | ⬜ |
| `ASSET-019.15` | 65–74 | بلوک | `applyFreePositions` | 0 | ⬜ |
| `ASSET-019.16` | 75–77 | بلوک | `saveFreePositions` | 4 | ⬜ |
| `ASSET-019.17` | 78–81 | بلوک | `children` | 0 | ⬜ |
| `ASSET-019.18` | 82–86 | بلوک | `descendantIds` | 0 | ⬜ |
| `ASSET-019.19` | 87–88 | بلوک | `managedUnit` | 0 | ⬜ |
| `ASSET-019.20` | 89–91 | بلوک | `liaisonAt` | 0 | ⬜ |
| `ASSET-019.21` | 92–102 | بلوک | `roleOf` | 0 | ⬜ |
| `ASSET-019.22` | 103–114 | بلوک | `positionOf` | 0 | ⬜ |
| `ASSET-019.23` | 115–118 | بلوک | `sourceLabel` | 0 | ⬜ |
| `ASSET-019.24` | 119–119 | بلوک | `allDepartments` | 0 | ⬜ |
| `ASSET-019.25` | 120–122 | بلوک | `expertHosts` | 0 | ⬜ |
| `ASSET-019.26` | 123–123 | بلوک | `expertsIn` | 0 | ⬜ |
| `ASSET-019.27` | 124–124 | بلوک | `poolUsers` | 0 | ⬜ |
| `ASSET-019.28` | 125–125 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-019.29` | 126–129 | بلوک | `match` | 0 | ⬜ |
| `ASSET-019.30` | 130–138 | بلوک | `toast` | 0 | ⬜ |
| `ASSET-019.31` | 139–178 | بلوک | `submit` | 0 | ⬜ |
| `ASSET-019.32` | 179–182 | بلوک | `closeConfirm` | 0 | ⬜ |
| `ASSET-019.33` | 183–193 | بلوک | `askConfirm` | 0 | ⬜ |
| `ASSET-019.34` | 194–197 | بلوک | `closeModal` | 0 | ⬜ |
| `ASSET-019.35` | 198–211 | بلوک | `openModal` | 0 | ⬜ |
| `ASSET-019.36` | 212–247 | بلوک | `renderModal` | 0 | ⬜ |
| `ASSET-019.37` | 248–257 | بلوک | `renderLeftSidebar` | 0 | ⬜ |
| `ASSET-019.38` | 258–258 | بلوک | `stat` | 0 | ⬜ |
| `ASSET-019.39` | 259–263 | بلوک | `quick` | 0 | ⬜ |
| `ASSET-019.40` | 264–284 | بلوک | `renderPoolSidebar` | 0 | ⬜ |
| `ASSET-019.41` | 285–285 | بلوک | `nodeMatch` | 0 | ⬜ |
| `ASSET-019.42` | 286–286 | بلوک | `nodeMenuButton` | 0 | ⬜ |
| `ASSET-019.43` | 287–290 | بلوک | `renderCeoAddActions` | 0 | ⬜ |
| `ASSET-019.44` | 291–296 | بلوک | `renderCeo` | 0 | ⬜ |
| `ASSET-019.45` | 297–310 | بلوک | `renderLiaisonSide` | 0 | ⬜ |
| `ASSET-019.46` | 311–316 | بلوک | `renderOffice` | 0 | ⬜ |
| `ASSET-019.47` | 317–322 | بلوک | `renderLiaisonBand` | 0 | ⬜ |
| `ASSET-019.48` | 323–333 | بلوک | `renderDeputy` | 0 | ⬜ |
| `ASSET-019.49` | 334–343 | بلوک | `renderManagement` | 0 | ⬜ |
| `ASSET-019.50` | 344–350 | بلوک | `renderDepartment` | 0 | ⬜ |
| `ASSET-019.51` | 351–355 | بلوک | `expertNode` | 0 | ⬜ |
| `ASSET-019.52` | 356–363 | بلوک | `renderExperts` | 0 | ⬜ |
| `ASSET-019.53` | 364–364 | بلوک | `branchColorAt` | 0 | ⬜ |
| `ASSET-019.54` | 365–374 | بلوک | `renderUnitBranch` | 0 | ⬜ |
| `ASSET-019.55` | 375–389 | بلوک | `renderCeoBranch` | 0 | ⬜ |
| `ASSET-019.56` | 390–396 | بلوک | `markTree` | 0 | ⬜ |
| `ASSET-019.57` | 397–415 | بلوک | `markOrphanTree` | 0 | ⬜ |
| `ASSET-019.58` | 416–437 | بلوک | `expandCanvasForNegativeCards` | 0 | ⬜ |
| `ASSET-019.59` | 438–449 | بلوک | `preserveAxis` | 0 | ⬜ |
| `ASSET-019.60` | 450–456 | بلوک | `drawOrgWires` | 0 | ⬜ |
| `ASSET-019.61` | 457–462 | بلوک | `rel` | 0 | ⬜ |
| `ASSET-019.62` | 463–471 | بلوک | `path` | 0 | ⬜ |
| `ASSET-019.63` | 472–498 | بلوک | `officeJoinX` | 0 | ⬜ |
| `ASSET-019.64` | 499–512 | بلوک | `bindDeputyReorder` | 0 | ⬜ |
| `ASSET-019.65` | 513–520 | بلوک | `onDeputyMove` | 0 | ⬜ |
| `ASSET-019.66` | 521–525 | بلوک | `clearDeputyDrag` | 0 | ⬜ |
| `ASSET-019.67` | 526–538 | بلوک | `onDeputyUp` | 0 | ⬜ |
| `ASSET-019.68` | 539–539 | بلوک | `cancelDeputyDrag` | 0 | ⬜ |
| `ASSET-019.69` | 540–560 | بلوک | `renderChart` | 0 | ⬜ |
| `ASSET-019.70` | 561–564 | بلوک | `openManagerPicker` | 0 | ⬜ |
| `ASSET-019.71` | 565–570 | بلوک | `addExperts` | 0 | ⬜ |
| `ASSET-019.72` | 571–576 | بلوک | `quickAddDepartment` | 0 | ⬜ |
| `ASSET-019.73` | 577–588 | بلوک | `quickAddExpert` | 0 | ⬜ |
| `ASSET-019.74` | 589–619 | بلوک | `showInline` | 0 | ⬜ |
| `ASSET-019.75` | 620–661 | بلوک | `showInlineEdit` | 0 | ⬜ |
| `ASSET-019.76` | 662–662 | بلوک | `showInlineAddDeputy` | 0 | ⬜ |
| `ASSET-019.77` | 663–664 | بلوک | `showInlineAddDepartment` | 0 | ⬜ |
| `ASSET-019.78` | 665–672 | بلوک | `openExpertManager` | 0 | ⬜ |
| `ASSET-019.79` | 673–683 | بلوک | `close` | 0 | ⬜ |
| `ASSET-019.80` | 684–689 | بلوک | `removeAllExperts` | 0 | ⬜ |
| `ASSET-019.81` | 690–695 | بلوک | `removeUnit` | 0 | ⬜ |
| `ASSET-019.82` | 696–700 | بلوک | `assigned` | 1 | ⬜ |
| `ASSET-019.83` | 701–707 | بلوک | `addOrEditOffice` | 0 | ⬜ |
| `ASSET-019.84` | 708–730 | بلوک | `renderMenu` | 1 | ⬜ |
| `ASSET-019.85` | 731–735 | بلوک | `removeManager` | 0 | ⬜ |
| `ASSET-019.86` | 736–750 | بلوک | `handleAction` | 0 | ⬜ |
| `ASSET-019.87` | 751–822 | بلوک | `bindDropTargets` | 0 | ⬜ |
| `ASSET-019.88` | 823–837 | بلوک | `beginFreeMove` | 4 | ⬜ |
| `ASSET-019.89` | 838–841 | بلوک | `moveFreeCard` | 0 | ⬜ |
| `ASSET-019.90` | 842–851 | بلوک | `dx` | 0 | ⬜ |
| `ASSET-019.91` | 852–872 | بلوک | `endFreeCard` | 0 | ⬜ |
| `ASSET-019.92` | 873–906 | بلوک | `cancelFreeCard` | 0 | ⬜ |
| `ASSET-019.93` | 907–912 | بلوک | `endPan` | 0 | ⬜ |
| `ASSET-019.94` | 913–918 | بلوک | `askReset` | 0 | ⬜ |
| `ASSET-019.95` | 919–921 | بلوک | `downloadBlob` | 0 | ⬜ |
| `ASSET-019.96` | 922–926 | بلوک | `exportJson` | 0 | ⬜ |
| `ASSET-019.97` | 927–932 | بلوک | `exportExcel` | 0 | ⬜ |
| `ASSET-019.98` | 933–963 | بلوک | `createChartImageBlob` | 0 | ⬜ |
| `ASSET-019.99` | 964–971 | بلوک | `exportPng` | 0 | ⬜ |
| `ASSET-019.100` | 972–999 | بلوک | `printWholeChart` | 0 | ⬜ |
| `ASSET-019.101` | 1000–1000 | بلوک | `closeExportMenu` | 0 | ⬜ |
| `ASSET-019.102` | 1001–1015 | بلوک | `loadBackupFile` | 0 | ⬜ |
| `ASSET-019.103` | 1016–1022 | بلوک | `exportByType` | 0 | ⬜ |
| `ASSET-019.104` | 1023–1039 | بلوک | `renderAll` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-020</b> — <code>assets/particle-network.js</code> (480 خط، 32 آدرس، بازبینی‌شده 0/32)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-020.01` | 1–34 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `ASSET-020.02` | 35–38 | بلوک | `clamp` | 0 | ⬜ |
| `ASSET-020.03` | 39–51 | بلوک | `asBoolean` | 0 | ⬜ |
| `ASSET-020.04` | 52–83 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-020.05` | 84–88 | بلوک | `parseColor` | 0 | ⬜ |
| `ASSET-020.06` | 89–90 | بلوک | `inertInstance` | 0 | ⬜ |
| `ASSET-020.07` | 91–91 | بلوک | `update` | 1 | ⬜ |
| `ASSET-020.08` | 92–95 | بلوک | `destroy` | 0 | ⬜ |
| `ASSET-020.09` | 96–147 | بلوک | `create` | 0 | ⬜ |
| `ASSET-020.10` | 148–155 | بلوک | `dimensions` | 0 | ⬜ |
| `ASSET-020.11` | 156–167 | بلوک | `makeParticle` | 0 | ⬜ |
| `ASSET-020.12` | 168–189 | بلوک | `render` | 0 | ⬜ |
| `ASSET-020.13` | 190–190 | بلوک | `midX` | 0 | ⬜ |
| `ASSET-020.14` | 191–193 | بلوک | `midY` | 0 | ⬜ |
| `ASSET-020.15` | 194–216 | بلوک | `alpha` | 0 | ⬜ |
| `ASSET-020.16` | 217–241 | بلوک | `cursorAlpha` | 0 | ⬜ |
| `ASSET-020.17` | 242–258 | بلوک | `resize` | 0 | ⬜ |
| `ASSET-020.18` | 259–262 | بلوک | `canAnimate` | 0 | ⬜ |
| `ASSET-020.19` | 263–270 | بلوک | `stop` | 0 | ⬜ |
| `ASSET-020.20` | 271–288 | بلوک | `animate` | 0 | ⬜ |
| `ASSET-020.21` | 289–297 | بلوک | `start` | 0 | ⬜ |
| `ASSET-020.22` | 298–305 | بلوک | `localPosition` | 0 | ⬜ |
| `ASSET-020.23` | 306–321 | بلوک | `onMove` | 0 | ⬜ |
| `ASSET-020.24` | 322–328 | بلوک | `onLeave` | 0 | ⬜ |
| `ASSET-020.25` | 329–332 | بلوک | `onResize` | 0 | ⬜ |
| `ASSET-020.26` | 333–342 | بلوک | `onVisibility` | 0 | ⬜ |
| `ASSET-020.27` | 343–377 | بلوک | `onMotionPreference` | 0 | ⬜ |
| `ASSET-020.28` | 378–405 | بلوک | `update` | 1 | ⬜ |
| `ASSET-020.29` | 406–443 | بلوک | `destroy` | 0 | ⬜ |
| `ASSET-020.30` | 444–455 | بلوک | `bootBackground` | 0 | ⬜ |
| `ASSET-020.31` | 456–469 | بلوک | `mount` | 0 | ⬜ |
| `ASSET-020.32` | 470–480 | بلوک | `boot` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-021</b> — <code>assets/particle-settings.js</code> (158 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-021.01` | 1–4 | بلوک | `کنترل تب ذرات معلق و پیش‌نمایش زندهٔ آن در تنظیمات` | 0 | ⬜ |
| `ASSET-021.02` | 5–40 | بلوک | `init` | 0 | ⬜ |
| `ASSET-021.03` | 41–45 | بلوک | `activeEffect` | 0 | ⬜ |
| `ASSET-021.04` | 46–66 | بلوک | `readConfig` | 0 | ⬜ |
| `ASSET-021.05` | 67–84 | بلوک | `updateOutputs` | 4 | ⬜ |
| `ASSET-021.06` | 85–90 | بلوک | `mountPreview` | 0 | ⬜ |
| `ASSET-021.07` | 91–97 | بلوک | `destroyPreview` | 0 | ⬜ |
| `ASSET-021.08` | 98–104 | بلوک | `syncPreview` | 1 | ⬜ |
| `ASSET-021.09` | 105–158 | بلوک | `applyPreset` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-022</b> — <code>assets/server-sort.js</code> (67 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-022.01` | 1–8 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-022.02` | 9–14 | بلوک | `regionOf` | 0 | ⬜ |
| `ASSET-022.03` | 15–20 | بلوک | `sortableSections` | 0 | ⬜ |
| `ASSET-022.04` | 21–67 | بلوک | `findFresh` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-023</b> — <code>assets/style.css</code> (1,869 خط، 50 آدرس، بازبینی‌شده 0/50)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-023.01` | 1–156 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `ASSET-023.02` | 157–277 | بلوک | `سازمانی کرم، طلایی و سبز زمردی` | 4 | ⬜ |
| `ASSET-023.03` | 278–318 | بلوک | `۱.۶۳.۴: پیام «پیدا نشد» و برجسته‌سازی عبارت جست‌وج` | 3 | ⬜ |
| `ASSET-023.04` | 319–438 | بلوک | `CD/DVD and food-ticket reference forms` | 3 | ⬜ |
| `ASSET-023.05` | 439–503 | بلوک | `Refined organization header and visual identity co` | 3 | ⬜ |
| `ASSET-023.06` | 504–547 | بلوک | `Application navigation styled after the supplied m` | 2 | ⬜ |
| `ASSET-023.07` | 548–560 | بلوک | `Reusable decorative arcs: each size and placement ` | 1 | ⬜ |
| `ASSET-023.08` | 561–608 | بلوک | `Asset profile / شناسنامه حرفه‌ای` | 1 | ⬜ |
| `ASSET-023.09` | 609–656 | بلوک | `تقویم جلالی مشترک` | 0 | ⬜ |
| `ASSET-023.10` | 657–669 | بلوک | `سالانه تعطیلات` | 0 | ⬜ |
| `ASSET-023.11` | 670–866 | بلوک | `طراح فیش` | 5 | ⬜ |
| `ASSET-023.12` | 867–956 | بلوک | `چارت سازمانی تعاملی` | 2 | ⬜ |
| `ASSET-023.13` | 957–992 | بلوک | `نسخهٔ فرم سازمان با تم اصلی سامانه` | 3 | ⬜ |
| `ASSET-023.14` | 993–1004 | بلوک | `کنترل تردد مراجعین و میهمانان` | 1 | ⬜ |
| `ASSET-023.15` | 1005–1219 | بلوک | `کنترل تردد: نمودار خوانا، فرم جمع‌وجور و داشبورد چ` | 5 | ⬜ |
| `ASSET-023.16` | 1220–1224 | بلوک | `کارت‌های آماری داشبورد که با کلیک به گزارش فیلترشد` | 0 | ⬜ |
| `ASSET-023.17` | 1225–1235 | بلوک | `۱.۳۷ — کمبوی زندهٔ «سیستم مرتبط» در فرم ثبت درخواس` | 0 | ⬜ |
| `ASSET-023.18` | 1236–1240 | بلوک | `۱.۳۷ — فیلتر زندهٔ «شناسنامه‌های فنی سیستم»` | 0 | ⬜ |
| `ASSET-023.19` | 1241–1249 | بلوک | `۱.۳۷ — کارت «دلایل شکست استخراج و راه‌حل» در صفحهٔ` | 0 | ⬜ |
| `ASSET-023.20` | 1250–1263 | بلوک | `۱.۳۷ — کادر «تراز دقیق لبه‌های کاغذ» و گزارش آزمون` | 0 | ⬜ |
| `ASSET-023.21` | 1264–1269 | بلوک | `۱.۳۷.۲ — خطای درون‌خطی فیلد (مثلاً الزام «سیستم مر` | 0 | ⬜ |
| `ASSET-023.22` | 1270–1276 | بلوک | `۱.۳۷.۴ — کادرهای تازهٔ پنل غذا و جدول نتیجهٔ ایمپو` | 0 | ⬜ |
| `ASSET-023.23` | 1277–1290 | بلوک | `۱.۳۷.۵ — نام کوتاه کامپیوترها: نشانِ کوچکِ «دامنه»` | 0 | ⬜ |
| `ASSET-023.24` | 1291–1348 | بلوک | `تم‌های قابل انتخاب کاربر — فقط رنگ و تزئینات؛ هیچ ` | 2 | ⬜ |
| `ASSET-023.25` | 1349–1366 | بلوک | `نقش‌های تزئینی اختصاصی هر تم (pseudo-elements و شک` | 0 | ⬜ |
| `ASSET-023.26` | 1367–1386 | بلوک | `سامانه: سلول‌های جدول و ردیف‌های فهرست داده‌ای در ` | 0 | ⬜ |
| `ASSET-023.27` | 1387–1432 | بلوک | `چاپ CD/DVD فقط از یک کپی موقتِ فهرست انتخاب‌شده ان` | 0 | ⬜ |
| `ASSET-023.28` | 1433–1438 | بلوک | `ویرایش زیر هر بخش: تورفتگی و حالت غیرفعال وقتی خود` | 0 | ⬜ |
| `ASSET-023.29` | 1439–1441 | بلوک | `حوزهٔ خدمت: فیلدهای مربوط به حوزهٔ دیگر کاملاً پنه` | 0 | ⬜ |
| `ASSET-023.30` | 1442–1444 | بلوک | `منوی واحد: اگر بلند بود، داخلی اسکرول می‌شود و از ` | 0 | ⬜ |
| `ASSET-023.31` | 1445–1448 | بلوک | `افکت نمایشی — صفحه‌های انتخاب‌شده` | 0 | ⬜ |
| `ASSET-023.32` | 1449–1496 | بلوک | `افکت نمایشی — پنل تنظیمات` | 3 | ⬜ |
| `ASSET-023.33` | 1497–1515 | بلوک | `فضای بیشتر و کنترل‌های خواناتر برای چارت سازمانی د` | 2 | ⬜ |
| `ASSET-023.34` | 1516–1545 | بلوک | `Traffic record correction and immutable edit-histo` | 1 | ⬜ |
| `ASSET-023.35` | 1546–1549 | بلوک | `A text field is used instead of locale-dependent n` | 0 | ⬜ |
| `ASSET-023.36` | 1550–1552 | بلوک | `تاریخچهٔ تغییرات در نمایشگرهای رومیزی دو ستونه و د` | 0 | ⬜ |
| `ASSET-023.37` | 1553–1578 | بلوک | `۱.۶۲.۹: چهار فیلد (عنوان + ۳ تأییدکننده) در یک خط؛` | 2 | ⬜ |
| `ASSET-023.38` | 1579–1584 | بلوک | `Fixed 09 prefix; only the remaining nine phone dig` | 0 | ⬜ |
| `ASSET-023.39` | 1585–1601 | بلوک | `Inspection-only daily food-absence reminder.` | 0 | ⬜ |
| `ASSET-023.40` | 1602–1606 | بلوک | `Keep the organization workspace viewport-sized, bu` | 0 | ⬜ |
| `ASSET-023.41` | 1607–1625 | بلوک | `Settings users: keep each account row tidy and ope` | 1 | ⬜ |
| `ASSET-023.42` | 1626–1701 | بلوک | `درخت سازمان پویا: چهار معاونت در هر ردیف، اداره‌ها` | 3 | ⬜ |
| `ASSET-023.43` | 1702–1707 | بلوک | `چارت سازمان: پیمایش با Space+drag بدون smooth-scro` | 0 | ⬜ |
| `ASSET-023.44` | 1708–1716 | بلوک | `هر شاخهٔ معاونت رنگ مخصوص خود را به کارت‌ها، کارشن` | 0 | ⬜ |
| `ASSET-023.45` | 1717–1761 | بلوک | `چارت سازمان: منوی خروجی، واحدهای هم‌تراز و نماهای ` | 0 | ⬜ |
| `ASSET-023.46` | 1762–1768 | بلوک | `چندشرکتی: انتخاب‌گر شرکت در چارت` | 1 | ⬜ |
| `ASSET-023.47` | 1769–1806 | بلوک | `اصلاحات چارت: ابزار ثابت، خروجی چاپ کامل و ورودی ب` | 1 | ⬜ |
| `ASSET-023.48` | 1807–1820 | بلوک | `چاپ مبتنی بر تصویر کامل و نام شرکت در کارت ریشه` | 0 | ⬜ |
| `ASSET-023.49` | 1821–1831 | بلوک | `فیلد نام شرکت‌ها صرفاً برچسب نمایشی انتخاب‌گر و کا` | 2 | ⬜ |
| `ASSET-023.50` | 1832–1869 | بلوک | `گروه‌های عمومی دسترسی: همان برد نقش‌ها با ویرایش ع` | 2 | ⬜ |

</details>

<details>
<summary><b>ASSET-024</b> — <code>assets/system-alarm.js</code> (121 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-024.01` | 1–19 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-024.02` | 20–23 | بلوک | `textOf` | 0 | ⬜ |
| `ASSET-024.03` | 24–28 | بلوک | `showModal` | 0 | ⬜ |
| `ASSET-024.04` | 29–33 | بلوک | `hideModal` | 0 | ⬜ |
| `ASSET-024.05` | 34–72 | بلوک | `render` | 0 | ⬜ |
| `ASSET-024.06` | 73–121 | بلوک | `poll` | 4 | ⬜ |

</details>

<details>
<summary><b>ASSET-025</b> — <code>assets/theme-mode.css</code> (252 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-025.01` | 1–140 | بلوک | `Global day/night mode for every screen rendered by` | 3 | ⬜ |
| `ASSET-025.02` | 141–211 | بلوک | `Cohesive dark surfaces for embedded food forms and` | 0 | ⬜ |
| `ASSET-025.03` | 212–242 | بلوک | `v1.62.4: پوشش کامل حالت شب برای فرم‌های برنامه غذا` | 0 | ⬜ |
| `ASSET-025.04` | 243–252 | بلوک | `v1.62.4: سفارش غذا — سطح‌هایی که رنگ روشن ثابت یا ` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-026</b> — <code>assets/topbar-chrome.css</code> (21 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-026.01` | 1–9 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-026.02` | 10–15 | بلوک | `بازگشت به سامانهٔ اصلی از مرکز درخواست‌ها — لینک خ` | 0 | ⬜ |
| `ASSET-026.03` | 16–17 | بلوک | `ناوبری بعد از ابزارهای برند و جست‌وجو در ترتیب لای` | 0 | ⬜ |
| `ASSET-026.04` | 18–21 | بلوک | `لینک «تغییرات سامانه» در فوتر (۱.۶۳ — صفحهٔ تغییرا` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-027</b> — <code>assets/traffic-control.js</code> (488 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-027.01` | 1–20 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `ASSET-027.02` | 21–30 | بلوک | `fireChange` | 0 | ⬜ |
| `ASSET-027.03` | 31–36 | بلوک | `setHint` | 0 | ⬜ |
| `ASSET-027.04` | 37–41 | بلوک | `clearHint` | 0 | ⬜ |
| `ASSET-027.05` | 42–49 | بلوک | `asciiDigitsOnly` | 0 | ⬜ |
| `ASSET-027.06` | 50–99 | بلوک | `lookupBy` | 0 | ⬜ |
| `ASSET-027.07` | 100–116 | بلوک | `lookup` | 0 | ⬜ |
| `ASSET-027.08` | 117–141 | بلوک | `lookupByPhone` | 0 | ⬜ |
| `ASSET-027.09` | 142–156 | بلوک | `parseApprovers` | 4 | ⬜ |
| `ASSET-027.10` | 157–196 | بلوک | `populate` | 0 | ⬜ |
| `ASSET-027.11` | 197–237 | بلوک | `updateDestinationSummary` | 0 | ⬜ |
| `ASSET-027.12` | 238–241 | بلوک | `جست‌وجوی آنی در سوابق تردد: به‌محض تایپ، ردیف‌های ` | 0 | ⬜ |
| `ASSET-027.13` | 242–262 | بلوک | `liveList` | 4 | ⬜ |
| `ASSET-027.14` | 263–263 | بلوک | `selectedFields` | 0 | ⬜ |
| `ASSET-027.15` | 264–290 | بلوک | `renderReport` | 4 | ⬜ |
| `ASSET-027.16` | 291–299 | بلوک | `schedule` | 0 | ⬜ |
| `ASSET-027.17` | 300–392 | بلوک | `load` | 1 | ⬜ |
| `ASSET-027.18` | 393–420 | بلوک | `apply` | 0 | ⬜ |
| `ASSET-027.19` | 421–458 | بلوک | `asciiDigits` | 4 | ⬜ |
| `ASSET-027.20` | 459–466 | بلوک | `asciiDigits` | 0 | ⬜ |
| `ASSET-027.21` | 467–488 | بلوک | `sync` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-028</b> — <code>assets/traffic-history-1.62.72.css</code> (7 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-028.01` | 1–7 | بلوک | `Only traffic history; preserve other tabs.` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-029</b> — <code>assets/traffic-records-1.62.68.css</code> (21 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-029.01` | 1–21 | بلوک | `Scoped to the CD/DVD history only: no effect on th` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-030</b> — <code>assets/traffic-records-1.62.69.css</code> (50 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-030.01` | 1–50 | بلوک | `Wide layouts are confined to these two pages/tabs,` | 3 | ⬜ |

</details>

<details>
<summary><b>ASSET-031</b> — <code>assets/traffic-report-1.62.70.css</code> (22 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-031.01` | 1–22 | بلوک | `گزارش تردد: مقصد در کنار تاریخ‌ها و گزینه‌ها به صو` | 1 | ⬜ |

</details>

<details>
<summary><b>ASSET-032</b> — <code>assets/triangle-mesh.js</code> (645 خط، 38 آدرس، بازبینی‌شده 0/38)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-032.01` | 1–33 | بلوک | `مقدمهٔ فایل` | 1 | ⬜ |
| `ASSET-032.02` | 34–37 | بلوک | `clamp` | 0 | ⬜ |
| `ASSET-032.03` | 38–46 | بلوک | `parseColor` | 4 | ⬜ |
| `ASSET-032.04` | 47–59 | بلوک | `asBoolean` | 0 | ⬜ |
| `ASSET-032.05` | 60–87 | بلوک | `normalize` | 0 | ⬜ |
| `ASSET-032.06` | 88–89 | بلوک | `inertInstance` | 0 | ⬜ |
| `ASSET-032.07` | 90–90 | بلوک | `update` | 1 | ⬜ |
| `ASSET-032.08` | 91–91 | بلوک | `ripple` | 0 | ⬜ |
| `ASSET-032.09` | 92–95 | بلوک | `destroy` | 0 | ⬜ |
| `ASSET-032.10` | 96–164 | بلوک | `create` | 0 | ⬜ |
| `ASSET-032.11` | 165–170 | بلوک | `applyMask` | 0 | ⬜ |
| `ASSET-032.12` | 171–178 | بلوک | `size` | 0 | ⬜ |
| `ASSET-032.13` | 179–225 | بلوک | `rebuild` | 0 | ⬜ |
| `ASSET-032.14` | 226–239 | بلوک | `below` | 0 | ⬜ |
| `ASSET-032.15` | 240–307 | بلوک | `render` | 0 | ⬜ |
| `ASSET-032.16` | 308–326 | بلوک | `hasMotion` | 0 | ⬜ |
| `ASSET-032.17` | 327–370 | بلوک | `physics` | 0 | ⬜ |
| `ASSET-032.18` | 371–376 | بلوک | `elapsed` | 0 | ⬜ |
| `ASSET-032.19` | 377–391 | بلوک | `amplitude` | 0 | ⬜ |
| `ASSET-032.20` | 392–399 | بلوک | `stop` | 0 | ⬜ |
| `ASSET-032.21` | 400–415 | بلوک | `animate` | 0 | ⬜ |
| `ASSET-032.22` | 416–424 | بلوک | `start` | 0 | ⬜ |
| `ASSET-032.23` | 425–432 | بلوک | `localPosition` | 0 | ⬜ |
| `ASSET-032.24` | 433–440 | بلوک | `addRipple` | 0 | ⬜ |
| `ASSET-032.25` | 441–457 | بلوک | `onMove` | 0 | ⬜ |
| `ASSET-032.26` | 458–469 | بلوک | `onDown` | 0 | ⬜ |
| `ASSET-032.27` | 470–476 | بلوک | `onUp` | 0 | ⬜ |
| `ASSET-032.28` | 477–481 | بلوک | `onLeave` | 0 | ⬜ |
| `ASSET-032.29` | 482–488 | بلوک | `onOut` | 0 | ⬜ |
| `ASSET-032.30` | 489–496 | بلوک | `onVisibility` | 0 | ⬜ |
| `ASSET-032.31` | 497–502 | بلوک | `onResize` | 0 | ⬜ |
| `ASSET-032.32` | 503–538 | بلوک | `onMotionPreference` | 0 | ⬜ |
| `ASSET-032.33` | 539–562 | بلوک | `update` | 1 | ⬜ |
| `ASSET-032.34` | 563–565 | بلوک | `ripple` | 0 | ⬜ |
| `ASSET-032.35` | 566–605 | بلوک | `destroy` | 0 | ⬜ |
| `ASSET-032.36` | 606–617 | بلوک | `bootBackground` | 0 | ⬜ |
| `ASSET-032.37` | 618–632 | بلوک | `mount` | 0 | ⬜ |
| `ASSET-032.38` | 633–645 | بلوک | `boot` | 0 | ⬜ |

</details>

<details>
<summary><b>ASSET-033</b> — <code>assets/triangle-settings.js</code> (267 خط، 13 آدرس، بازبینی‌شده 0/13)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ASSET-033.01` | 1–35 | بلوک | `کنترل پیش‌نمایش زنده و فهرست صفحه‌های افکت نمایشی` | 0 | ⬜ |
| `ASSET-033.02` | 36–52 | بلوک | `init` | 0 | ⬜ |
| `ASSET-033.03` | 53–74 | بلوک | `readConfig` | 0 | ⬜ |
| `ASSET-033.04` | 75–93 | بلوک | `updateOutputs` | 0 | ⬜ |
| `ASSET-033.05` | 94–97 | بلوک | `activeEffect` | 0 | ⬜ |
| `ASSET-033.06` | 98–103 | بلوک | `mountTrianglePreview` | 0 | ⬜ |
| `ASSET-033.07` | 104–110 | بلوک | `destroyTrianglePreview` | 0 | ⬜ |
| `ASSET-033.08` | 111–117 | بلوک | `syncPreview` | 1 | ⬜ |
| `ASSET-033.09` | 118–151 | بلوک | `activateEffectTab` | 0 | ⬜ |
| `ASSET-033.10` | 152–157 | بلوک | `next` | 0 | ⬜ |
| `ASSET-033.11` | 158–172 | بلوک | `updatePageCount` | 4 | ⬜ |
| `ASSET-033.12` | 173–178 | بلوک | `filterPages` | 0 | ⬜ |
| `ASSET-033.13` | 179–267 | بلوک | `text` | 0 | ⬜ |

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
<summary><b>WEB-016</b> — <code>food-ticket-web/assets/food-designer.js</code> (897 خط، 64 آدرس، بازبینی‌شده 0/64)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-016.01` | 1–28 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-016.02` | 29–29 | بلوک | `esc` | 0 | ⬜ |
| `WEB-016.03` | 30–30 | بلوک | `say` | 4 | ⬜ |
| `WEB-016.04` | 31–31 | بلوک | `callApi` | 0 | ⬜ |
| `WEB-016.05` | 32–32 | بلوک | `root` | 0 | ⬜ |
| `WEB-016.06` | 33–33 | بلوک | `clone` | 0 | ⬜ |
| `WEB-016.07` | 34–34 | بلوک | `r1` | 0 | ⬜ |
| `WEB-016.08` | 35–35 | بلوک | `num` | 0 | ⬜ |
| `WEB-016.09` | 36–37 | بلوک | `faDigits` | 0 | ⬜ |
| `WEB-016.10` | 38–41 | بلوک | `FIELD_BY_KEY` | 0 | ⬜ |
| `WEB-016.11` | 42–116 | بلوک | `el` | 0 | ⬜ |
| `WEB-016.12` | 117–117 | بلوک | `current` | 0 | ⬜ |
| `WEB-016.13` | 118–118 | بلوک | `selected` | 0 | ⬜ |
| `WEB-016.14` | 119–126 | بلوک | `paperH` | 0 | ⬜ |
| `WEB-016.15` | 127–130 | بلوک | `workFrom` | 0 | ⬜ |
| `WEB-016.16` | 131–140 | بلوک | `setWork` | 0 | ⬜ |
| `WEB-016.17` | 141–143 | بلوک | `activeRow` | 0 | ⬜ |
| `WEB-016.18` | 144–165 | بلوک | `applyList` | 0 | ⬜ |
| `WEB-016.19` | 166–177 | بلوک | `load` | 0 | ⬜ |
| `WEB-016.20` | 178–180 | بلوک | `html` | 0 | ⬜ |
| `WEB-016.21` | 181–187 | بلوک | `mount` | 0 | ⬜ |
| `WEB-016.22` | 188–192 | بلوک | `draw` | 0 | ⬜ |
| `WEB-016.23` | 193–205 | بلوک | `sampleText` | 0 | ⬜ |
| `WEB-016.24` | 206–210 | بلوک | `logoUrl` | 0 | ⬜ |
| `WEB-016.25` | 211–212 | بلوک | `elementHtml` | 0 | ⬜ |
| `WEB-016.26` | 213–231 | بلوک | `pxPt` | 0 | ⬜ |
| `WEB-016.27` | 232–235 | بلوک | `imgAlign` | 0 | ⬜ |
| `WEB-016.28` | 236–257 | بلوک | `groupPreviewLines` | 0 | ⬜ |
| `WEB-016.29` | 258–266 | بلوک | `groupTextHeight` | 0 | ⬜ |
| `WEB-016.30` | 267–314 | بلوک | `groupCanvasHtml` | 0 | ⬜ |
| `WEB-016.31` | 315–328 | بلوک | `canvasHtml` | 0 | ⬜ |
| `WEB-016.32` | 329–332 | بلوک | `designTabsHtml` | 0 | ⬜ |
| `WEB-016.33` | 333–341 | بلوک | `tabsHtml` | 0 | ⬜ |
| `WEB-016.34` | 342–354 | بلوک | `fieldListHtml` | 0 | ⬜ |
| `WEB-016.35` | 355–362 | بلوک | `layersHtml` | 0 | ⬜ |
| `WEB-016.36` | 363–365 | بلوک | `groupTypographyPropsHtml` | 0 | ⬜ |
| `WEB-016.37` | 366–379 | بلوک | `inp` | 0 | ⬜ |
| `WEB-016.38` | 380–386 | بلوک | `propsHtml` | 0 | ⬜ |
| `WEB-016.39` | 387–396 | بلوک | `inp` | 0 | ⬜ |
| `WEB-016.40` | 397–408 | بلوک | `labelBlock` | 1 | ⬜ |
| `WEB-016.41` | 409–412 | بلوک | `paperHtml` | 0 | ⬜ |
| `WEB-016.42` | 413–413 | بلوک | `logoThumb` | 0 | ⬜ |
| `WEB-016.43` | 414–434 | بلوک | `warn58` | 0 | ⬜ |
| `WEB-016.44` | 435–448 | بلوک | `groupModeHtml` | 0 | ⬜ |
| `WEB-016.45` | 449–498 | بلوک | `groupLivePreviewHtml` | 0 | ⬜ |
| `WEB-016.46` | 499–530 | بلوک | `previewHtml` | 0 | ⬜ |
| `WEB-016.47` | 531–535 | بلوک | `body` | 0 | ⬜ |
| `WEB-016.48` | 536–554 | بلوک | `isActive` | 0 | ⬜ |
| `WEB-016.49` | 555–570 | بلوک | `css` | 1 | ⬜ |
| `WEB-016.50` | 571–577 | بلوک | `touch` | 0 | ⬜ |
| `WEB-016.51` | 578–583 | بلوک | `schedulePreview` | 0 | ⬜ |
| `WEB-016.52` | 584–600 | بلوک | `runPreview` | 0 | ⬜ |
| `WEB-016.53` | 601–604 | بلوک | `guardDirty` | 0 | ⬜ |
| `WEB-016.54` | 605–617 | بلوک | `switchDesignTab` | 0 | ⬜ |
| `WEB-016.55` | 618–634 | بلوک | `addElement` | 0 | ⬜ |
| `WEB-016.56` | 635–653 | بلوک | `saveGroup` | 0 | ⬜ |
| `WEB-016.57` | 654–670 | بلوک | `save` | 0 | ⬜ |
| `WEB-016.58` | 671–680 | بلوک | `postAction` | 0 | ⬜ |
| `WEB-016.59` | 681–697 | بلوک | `uploadLogo` | 4 | ⬜ |
| `WEB-016.60` | 698–836 | بلوک | `within` | 6 | ⬜ |
| `WEB-016.61` | 837–837 | بلوک | `dx` | 0 | ⬜ |
| `WEB-016.62` | 838–871 | بلوک | `snap` | 0 | ⬜ |
| `WEB-016.63` | 872–883 | بلوک | `openNewDialog` | 0 | ⬜ |
| `WEB-016.64` | 884–897 | بلوک | `key` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-017</b> — <code>food-ticket-web/assets/food-groups.js</code> (591 خط، 40 آدرس، بازبینی‌شده 0/40)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-017.01` | 1–22 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-017.02` | 23–23 | بلوک | `esc` | 0 | ⬜ |
| `WEB-017.03` | 24–24 | بلوک | `say` | 4 | ⬜ |
| `WEB-017.04` | 25–25 | بلوک | `box` | 0 | ⬜ |
| `WEB-017.05` | 26–26 | بلوک | `callApi` | 0 | ⬜ |
| `WEB-017.06` | 27–33 | بلوک | `todayIso` | 0 | ⬜ |
| `WEB-017.07` | 34–50 | بلوک | `draw` | 4 | ⬜ |
| `WEB-017.08` | 51–77 | بلوک | `load` | 4 | ⬜ |
| `WEB-017.09` | 78–89 | بلوک | `apply` | 0 | ⬜ |
| `WEB-017.10` | 90–90 | بلوک | `userLabel` | 0 | ⬜ |
| `WEB-017.11` | 91–96 | بلوک | `badges` | 0 | ⬜ |
| `WEB-017.12` | 97–100 | بلوک | `form` | 0 | ⬜ |
| `WEB-017.13` | 101–102 | بلوک | `q` | 0 | ⬜ |
| `WEB-017.14` | 103–108 | بلوک | `mine` | 0 | ⬜ |
| `WEB-017.15` | 109–129 | بلوک | `visitors` | 0 | ⬜ |
| `WEB-017.16` | 130–146 | بلوک | `list` | 1 | ⬜ |
| `WEB-017.17` | 147–150 | بلوک | `isoToday` | 0 | ⬜ |
| `WEB-017.18` | 151–161 | بلوک | `jalaliLabel` | 0 | ⬜ |
| `WEB-017.19` | 162–162 | بلوک | `isoFromJalaliInput` | 0 | ⬜ |
| `WEB-017.20` | 163–176 | بلوک | `raw` | 0 | ⬜ |
| `WEB-017.21` | 177–187 | بلوک | `mergeAbsence` | 0 | ⬜ |
| `WEB-017.22` | 188–207 | بلوک | `absencePanel` | 0 | ⬜ |
| `WEB-017.23` | 208–208 | بلوک | `disabled` | 0 | ⬜ |
| `WEB-017.24` | 209–249 | بلوک | `badges` | 0 | ⬜ |
| `WEB-017.25` | 250–260 | بلوک | `runs` | 0 | ⬜ |
| `WEB-017.26` | 261–267 | بلوک | `splitName` | 0 | ⬜ |
| `WEB-017.27` | 268–282 | بلوک | `membersSortList` | 0 | ⬜ |
| `WEB-017.28` | 283–287 | بلوک | `sortHead` | 0 | ⬜ |
| `WEB-017.29` | 288–294 | بلوک | `foodChipCell` | 0 | ⬜ |
| `WEB-017.30` | 295–308 | بلوک | `membersTable` | 0 | ⬜ |
| `WEB-017.31` | 309–315 | بلوک | `membersPopup` | 0 | ⬜ |
| `WEB-017.32` | 316–342 | بلوک | `block` | 0 | ⬜ |
| `WEB-017.33` | 343–351 | بلوک | `body` | 0 | ⬜ |
| `WEB-017.34` | 352–361 | بلوک | `html` | 0 | ⬜ |
| `WEB-017.35` | 362–373 | بلوک | `collect` | 0 | ⬜ |
| `WEB-017.36` | 374–472 | بلوک | `loadAbsence` | 0 | ⬜ |
| `WEB-017.37` | 473–493 | بلوک | `guest` | 0 | ⬜ |
| `WEB-017.38` | 494–533 | بلوک | `openEdit` | 1 | ⬜ |
| `WEB-017.39` | 534–560 | بلوک | `reason` | 0 | ⬜ |
| `WEB-017.40` | 561–591 | بلوک | `reason` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-018</b> — <code>food-ticket-web/assets/food-manual-attendance.js</code> (526 خط، 26 آدرس، بازبینی‌شده 0/26)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-018.01` | 1–11 | بلوک | `ثبت دستی فیش — زیرمنوی سلامت سیستم در تنظیمات پنل ` | 0 | ⬜ |
| `WEB-018.02` | 12–12 | بلوک | `esc` | 0 | ⬜ |
| `WEB-018.03` | 13–13 | بلوک | `say` | 4 | ⬜ |
| `WEB-018.04` | 14–14 | بلوک | `callApi` | 0 | ⬜ |
| `WEB-018.05` | 15–20 | بلوک | `isoToday` | 0 | ⬜ |
| `WEB-018.06` | 21–21 | بلوک | `isGuestRow` | 0 | ⬜ |
| `WEB-018.07` | 22–22 | بلوک | `canIssueManualTicket` | 0 | ⬜ |
| `WEB-018.08` | 23–29 | بلوک | `isReplaceableNoTicket` | 0 | ⬜ |
| `WEB-018.09` | 30–48 | بلوک | `isOrphanTicket` | 0 | ⬜ |
| `WEB-018.10` | 49–55 | بلوک | `isCancelableTicket` | 0 | ⬜ |
| `WEB-018.11` | 56–60 | بلوک | `isReplaceableOrphan` | 0 | ⬜ |
| `WEB-018.12` | 61–63 | بلوک | `selectableRow` | 0 | ⬜ |
| `WEB-018.13` | 64–64 | بلوک | `manualDateIso` | 0 | ⬜ |
| `WEB-018.14` | 65–75 | بلوک | `jalaliFromIso` | 0 | ⬜ |
| `WEB-018.15` | 76–91 | بلوک | `isoFromDateInput` | 0 | ⬜ |
| `WEB-018.16` | 92–103 | بلوک | `pad` | 0 | ⬜ |
| `WEB-018.17` | 104–133 | بلوک | `loadManualAttendance` | 0 | ⬜ |
| `WEB-018.18` | 134–165 | بلوک | `manualStatus` | 0 | ⬜ |
| `WEB-018.19` | 166–173 | بلوک | `manualAttendancePanel` | 0 | ⬜ |
| `WEB-018.20` | 174–208 | بلوک | `canSelect` | 0 | ⬜ |
| `WEB-018.21` | 209–297 | بلوک | `orphanDiag` | 0 | ⬜ |
| `WEB-018.22` | 298–301 | بلوک | `manualAttendanceHtml` | 0 | ⬜ |
| `WEB-018.23` | 302–309 | بلوک | `drawManualAttendance` | 4 | ⬜ |
| `WEB-018.24` | 310–459 | بلوک | `applyManualDateInput` | 2 | ⬜ |
| `WEB-018.25` | 460–477 | بلوک | `reason` | 0 | ⬜ |
| `WEB-018.26` | 478–526 | بلوک | `message` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-019</b> — <code>food-ticket-web/assets/food-menu.css</code> (417 خط، 19 آدرس، بازبینی‌شده 0/19)</summary>

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
| `WEB-019.16` | 355–378 | بلوک | `تعیین تکلیف غذای باقیمانده (تطبیق روزانه)` | 1 | ⬜ |
| `WEB-019.17` | 379–405 | بلوک | `1.62.93 follow-up: cancel-request cutoff + compact` | 1 | ⬜ |
| `WEB-019.18` | 406–413 | بلوک | `تفکیک غذای هم‌گروه: «چلوکباب ۴ · قیمه ۲»` | 0 | ⬜ |
| `WEB-019.19` | 414–417 | بلوک | `جدول داخل هر گروه، مثل گروه منو بدون حاشیهٔ اضافه` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-020</b> — <code>food-ticket-web/assets/food-menu.js</code> (2,820 خط، 183 آدرس، بازبینی‌شده 0/183)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-020.01` | 1–107 | بلوک | `مدیر برنامهٔ غذایی در پنل چاپ فیش.` | 0 | ⬜ |
| `WEB-020.02` | 108–112 | بلوک | `esc` | 0 | ⬜ |
| `WEB-020.03` | 113–113 | بلوک | `faDigits` | 0 | ⬜ |
| `WEB-020.04` | 114–118 | بلوک | `asciiDigits` | 0 | ⬜ |
| `WEB-020.05` | 119–128 | بلوک | `isoFromJalali` | 0 | ⬜ |
| `WEB-020.06` | 129–135 | بلوک | `jalaliFromIso` | 0 | ⬜ |
| `WEB-020.07` | 136–142 | بلوک | `normalizeIsoDate` | 0 | ⬜ |
| `WEB-020.08` | 143–146 | بلوک | `jalaliMonthFromIso` | 0 | ⬜ |
| `WEB-020.09` | 147–150 | بلوک | `localTodayIso` | 0 | ⬜ |
| `WEB-020.10` | 151–151 | بلوک | `monthKey` | 0 | ⬜ |
| `WEB-020.11` | 152–152 | بلوک | `monthTitle` | 0 | ⬜ |
| `WEB-020.12` | 153–153 | بلوک | `getDay` | 0 | ⬜ |
| `WEB-020.13` | 154–157 | بلوک | `activeOrderCount` | 0 | ⬜ |
| `WEB-020.14` | 158–167 | بلوک | `holiday` | 0 | ⬜ |
| `WEB-020.15` | 168–173 | بلوک | `isWeekendIso` | 0 | ⬜ |
| `WEB-020.16` | 174–178 | بلوک | `holidayOrWeekend` | 0 | ⬜ |
| `WEB-020.17` | 179–184 | بلوک | `buildUrl` | 0 | ⬜ |
| `WEB-020.18` | 185–202 | بلوک | `request` | 0 | ⬜ |
| `WEB-020.19` | 203–209 | بلوک | `notify` | 0 | ⬜ |
| `WEB-020.20` | 210–215 | بلوک | `navTabs` | 0 | ⬜ |
| `WEB-020.21` | 216–225 | بلوک | `cancelCount` | 0 | ⬜ |
| `WEB-020.22` | 226–258 | بلوک | `renderCalendar` | 0 | ⬜ |
| `WEB-020.23` | 259–276 | بلوک | `renderDayEditor` | 0 | ⬜ |
| `WEB-020.24` | 277–286 | بلوک | `choiceHtml` | 0 | ⬜ |
| `WEB-020.25` | 287–312 | بلوک | `foods` | 0 | ⬜ |
| `WEB-020.26` | 313–318 | بلوک | `categoryOptions` | 0 | ⬜ |
| `WEB-020.27` | 319–327 | بلوک | `catalogCategoryNames` | 0 | ⬜ |
| `WEB-020.28` | 328–337 | بلوک | `foodCardHtml` | 0 | ⬜ |
| `WEB-020.29` | 338–380 | بلوک | `renderCatalog` | 0 | ⬜ |
| `WEB-020.30` | 381–392 | بلوک | `readSavedOrderReportFields` | 0 | ⬜ |
| `WEB-020.31` | 393–398 | بلوک | `saveOrderReportFields` | 4 | ⬜ |
| `WEB-020.32` | 399–403 | بلوک | `selectedOrderColumns` | 0 | ⬜ |
| `WEB-020.33` | 404–421 | بلوک | `orderFieldValue` | 0 | ⬜ |
| `WEB-020.34` | 422–431 | بلوک | `orderSortValue` | 0 | ⬜ |
| `WEB-020.35` | 432–447 | بلوک | `sortOrderRows` | 0 | ⬜ |
| `WEB-020.36` | 448–485 | بلوک | `orderTableHtml` | 0 | ⬜ |
| `WEB-020.37` | 486–522 | بلوک | `orderByMenuHtml` | 0 | ⬜ |
| `WEB-020.38` | 523–534 | بلوک | `readDeliveredReportFields` | 0 | ⬜ |
| `WEB-020.39` | 535–539 | بلوک | `saveDeliveredReportFields` | 4 | ⬜ |
| `WEB-020.40` | 540–554 | بلوک | `deliveredReportFieldValue` | 0 | ⬜ |
| `WEB-020.41` | 555–558 | بلوک | `selectedDeliveredReportColumns` | 0 | ⬜ |
| `WEB-020.42` | 559–571 | بلوک | `sortDeliveredReportRows` | 0 | ⬜ |
| `WEB-020.43` | 572–602 | بلوک | `deliveredReportTableHtml` | 0 | ⬜ |
| `WEB-020.44` | 603–605 | بلوک | `deliveredReportByMenuHtml` | 0 | ⬜ |
| `WEB-020.45` | 606–631 | بلوک | `key` | 0 | ⬜ |
| `WEB-020.46` | 632–645 | بلوک | `deliveredReportByOrgHtml` | 0 | ⬜ |
| `WEB-020.47` | 646–654 | بلوک | `requestOrgMap` | 0 | ⬜ |
| `WEB-020.48` | 655–659 | بلوک | `deliveredReportOrderedRows` | 0 | ⬜ |
| `WEB-020.49` | 660–672 | بلوک | `key` | 0 | ⬜ |
| `WEB-020.50` | 673–676 | بلوک | `deliveryDateForReport` | 0 | ⬜ |
| `WEB-020.51` | 677–696 | بلوک | `renderPrintedDeliveryReport` | 0 | ⬜ |
| `WEB-020.52` | 697–732 | بلوک | `loadPrintedDeliveryReport` | 0 | ⬜ |
| `WEB-020.53` | 733–749 | بلوک | `exportPrintedDeliveryReport` | 0 | ⬜ |
| `WEB-020.54` | 750–754 | بلوک | `printPrintedDeliveryReport` | 0 | ⬜ |
| `WEB-020.55` | 755–760 | بلوک | `summaryHtml` | 0 | ⬜ |
| `WEB-020.56` | 761–769 | بلوک | `nameSplit` | 0 | ⬜ |
| `WEB-020.57` | 770–777 | بلوک | `personSortValue` | 0 | ⬜ |
| `WEB-020.58` | 778–788 | بلوک | `sortByState` | 0 | ⬜ |
| `WEB-020.59` | 789–794 | بلوک | `sortStateToggle` | 0 | ⬜ |
| `WEB-020.60` | 795–798 | بلوک | `sortStateMark` | 0 | ⬜ |
| `WEB-020.61` | 799–802 | بلوک | `rowSummary` | 0 | ⬜ |
| `WEB-020.62` | 803–808 | بلوک | `renderOrders` | 0 | ⬜ |
| `WEB-020.63` | 809–829 | بلوک | `viewButton` | 0 | ⬜ |
| `WEB-020.64` | 830–843 | بلوک | `ordersByOrgHtml` | 0 | ⬜ |
| `WEB-020.65` | 844–852 | بلوک | `renderGroupOrders` | 0 | ⬜ |
| `WEB-020.66` | 853–877 | بلوک | `tableFor` | 0 | ⬜ |
| `WEB-020.67` | 878–885 | بلوک | `renderCancelRequests` | 0 | ⬜ |
| `WEB-020.68` | 886–898 | بلوک | `splitRow` | 0 | ⬜ |
| `WEB-020.69` | 899–908 | بلوک | `cancelValue` | 0 | ⬜ |
| `WEB-020.70` | 909–911 | بلوک | `cmp` | 0 | ⬜ |
| `WEB-020.71` | 912–930 | بلوک | `cancelHead` | 0 | ⬜ |
| `WEB-020.72` | 931–949 | بلوک | `renderStatistics` | 0 | ⬜ |
| `WEB-020.73` | 950–959 | بلوک | `mark` | 0 | ⬜ |
| `WEB-020.74` | 960–983 | بلوک | `peopleSortMark` | 0 | ⬜ |
| `WEB-020.75` | 984–1009 | بلوک | `foods` | 0 | ⬜ |
| `WEB-020.76` | 1010–1018 | بلوک | `normalizeJalaliMonth` | 0 | ⬜ |
| `WEB-020.77` | 1019–1028 | بلوک | `reconciliationGapCell` | 0 | ⬜ |
| `WEB-020.78` | 1029–1037 | بلوک | `reconciliationRemainingCell` | 0 | ⬜ |
| `WEB-020.79` | 1038–1044 | بلوک | `reconciliationLeftoverCell` | 0 | ⬜ |
| `WEB-020.80` | 1045–1048 | بلوک | `reconciliationNumber` | 0 | ⬜ |
| `WEB-020.81` | 1049–1053 | بلوک | `reconciliationAmount` | 0 | ⬜ |
| `WEB-020.82` | 1054–1128 | بلوک | `renderReconciliation` | 0 | ⬜ |
| `WEB-020.83` | 1129–1134 | بلوک | `renderLeftoverModal` | 0 | ⬜ |
| `WEB-020.84` | 1135–1154 | بلوک | `row` | 0 | ⬜ |
| `WEB-020.85` | 1155–1155 | بلوک | `leftoverPreviewFor` | 0 | ⬜ |
| `WEB-020.86` | 1156–1171 | بلوک | `items` | 0 | ⬜ |
| `WEB-020.87` | 1172–1177 | بلوک | `leftoverInlineError` | 0 | ⬜ |
| `WEB-020.88` | 1178–1221 | بلوک | `confirmLeftoverModal` | 0 | ⬜ |
| `WEB-020.89` | 1222–1229 | بلوک | `submitReconciliationBody` | 0 | ⬜ |
| `WEB-020.90` | 1230–1236 | بلوک | `message` | 0 | ⬜ |
| `WEB-020.91` | 1237–1245 | بلوک | `postReconciliation` | 0 | ⬜ |
| `WEB-020.92` | 1246–1286 | بلوک | `loadReconciliation` | 0 | ⬜ |
| `WEB-020.93` | 1287–1292 | بلوک | `downloadReconciliation` | 0 | ⬜ |
| `WEB-020.94` | 1293–1293 | بلوک | `selectReconciliationRow` | 0 | ⬜ |
| `WEB-020.95` | 1294–1310 | بلوک | `item` | 0 | ⬜ |
| `WEB-020.96` | 1311–1344 | بلوک | `saveReconciliationForm` | 0 | ⬜ |
| `WEB-020.97` | 1345–1381 | بلوک | `uploadReconciliationInvoice` | 0 | ⬜ |
| `WEB-020.98` | 1382–1385 | بلوک | `bulkOperationTitle` | 0 | ⬜ |
| `WEB-020.99` | 1386–1390 | بلوک | `bulkSelectedPeople` | 0 | ⬜ |
| `WEB-020.100` | 1391–1394 | بلوک | `bulkTargetValue` | 0 | ⬜ |
| `WEB-020.101` | 1395–1401 | بلوک | `bulkCanSelect` | 0 | ⬜ |
| `WEB-020.102` | 1402–1412 | بلوک | `group` | 0 | ⬜ |
| `WEB-020.103` | 1413–1429 | بلوک | `saveCancelCutoff` | 0 | ⬜ |
| `WEB-020.104` | 1430–1458 | بلوک | `loadBulkSnapshot` | 0 | ⬜ |
| `WEB-020.105` | 1459–1473 | بلوک | `bulkTargetOptions` | 0 | ⬜ |
| `WEB-020.106` | 1474–1489 | بلوک | `bulkPersonStatus` | 0 | ⬜ |
| `WEB-020.107` | 1490–1511 | بلوک | `group` | 0 | ⬜ |
| `WEB-020.108` | 1512–1517 | بلوک | `renderBulkResults` | 0 | ⬜ |
| `WEB-020.109` | 1518–1525 | بلوک | `renderBulkModal` | 0 | ⬜ |
| `WEB-020.110` | 1526–1543 | بلوک | `renderBulkOperations` | 0 | ⬜ |
| `WEB-020.111` | 1544–1549 | بلوک | `rows` | 0 | ⬜ |
| `WEB-020.112` | 1550–1562 | بلوک | `bulkValue` | 0 | ⬜ |
| `WEB-020.113` | 1563–1568 | بلوک | `bulkHead` | 0 | ⬜ |
| `WEB-020.114` | 1569–1582 | بلوک | `selected` | 0 | ⬜ |
| `WEB-020.115` | 1583–1593 | بلوک | `previewDisabled` | 0 | ⬜ |
| `WEB-020.116` | 1594–1604 | بلوک | `bulkTogglePerson` | 1 | ⬜ |
| `WEB-020.117` | 1605–1606 | بلوک | `bulkSelectEligible` | 0 | ⬜ |
| `WEB-020.118` | 1607–1612 | بلوک | `eligible` | 0 | ⬜ |
| `WEB-020.119` | 1613–1615 | بلوک | `submitBulkOperation` | 0 | ⬜ |
| `WEB-020.120` | 1616–1635 | بلوک | `ids` | 0 | ⬜ |
| `WEB-020.121` | 1636–1641 | بلوک | `mealTypeSwitch` | 0 | ⬜ |
| `WEB-020.122` | 1642–1649 | بلوک | `embeddedPanel` | 0 | ⬜ |
| `WEB-020.123` | 1650–1669 | بلوک | `loadEmbedded` | 0 | ⬜ |
| `WEB-020.124` | 1670–1677 | بلوک | `renderFocusKeyOf` | 0 | ⬜ |
| `WEB-020.125` | 1678–1689 | بلوک | `captureRenderFocus` | 4 | ⬜ |
| `WEB-020.126` | 1690–1702 | بلوک | `restoreRenderFocus` | 4 | ⬜ |
| `WEB-020.127` | 1703–1714 | بلوک | `render` | 0 | ⬜ |
| `WEB-020.128` | 1715–1721 | بلوک | `notices` | 0 | ⬜ |
| `WEB-020.129` | 1722–1733 | بلوک | `loadStatus` | 0 | ⬜ |
| `WEB-020.130` | 1734–1740 | بلوک | `loadCatalog` | 0 | ⬜ |
| `WEB-020.131` | 1741–1748 | بلوک | `loadMonth` | 0 | ⬜ |
| `WEB-020.132` | 1749–1770 | بلوک | `loadOrders` | 0 | ⬜ |
| `WEB-020.133` | 1771–1791 | بلوک | `loadFoodGroups` | 0 | ⬜ |
| `WEB-020.134` | 1792–1822 | بلوک | `loadGroupOrders` | 0 | ⬜ |
| `WEB-020.135` | 1823–1839 | بلوک | `loadCancelRequests` | 0 | ⬜ |
| `WEB-020.136` | 1840–1858 | بلوک | `loadGuestRequests` | 0 | ⬜ |
| `WEB-020.137` | 1859–1859 | بلوک | `beginGuestEdit` | 0 | ⬜ |
| `WEB-020.138` | 1860–1875 | بلوک | `row` | 0 | ⬜ |
| `WEB-020.139` | 1876–1881 | بلوک | `cancelGuestEdit` | 0 | ⬜ |
| `WEB-020.140` | 1882–1913 | بلوک | `saveGuestEdit` | 1 | ⬜ |
| `WEB-020.141` | 1914–1914 | بلوک | `guestRequestAction` | 0 | ⬜ |
| `WEB-020.142` | 1915–1937 | بلوک | `row` | 1 | ⬜ |
| `WEB-020.143` | 1938–1940 | بلوک | `renderGuestRequestManager` | 0 | ⬜ |
| `WEB-020.144` | 1941–1963 | بلوک | `rows` | 1 | ⬜ |
| `WEB-020.145` | 1964–1978 | بلوک | `decideCancelRequest` | 0 | ⬜ |
| `WEB-020.146` | 1979–1985 | بلوک | `loadStatistics` | 0 | ⬜ |
| `WEB-020.147` | 1986–2019 | بلوک | `selectedIds` | 0 | ⬜ |
| `WEB-020.148` | 2020–2047 | بلوک | `searchSelectedReportEmployees` | 0 | ⬜ |
| `WEB-020.149` | 2048–2074 | بلوک | `refreshAll` | 0 | ⬜ |
| `WEB-020.150` | 2075–2119 | بلوک | `saveDay` | 0 | ⬜ |
| `WEB-020.151` | 2120–2145 | بلوک | `saveCatalog` | 0 | ⬜ |
| `WEB-020.152` | 2146–2165 | بلوک | `orderExportRows` | 0 | ⬜ |
| `WEB-020.153` | 2166–2182 | بلوک | `downloadOrdersCsv` | 0 | ⬜ |
| `WEB-020.154` | 2183–2187 | بلوک | `downloadOrders` | 0 | ⬜ |
| `WEB-020.155` | 2188–2189 | بلوک | `printScopedReport` | 0 | ⬜ |
| `WEB-020.156` | 2190–2200 | بلوک | `target` | 0 | ⬜ |
| `WEB-020.157` | 2201–2218 | بلوک | `cleanup` | 0 | ⬜ |
| `WEB-020.158` | 2219–2223 | بلوک | `printOrders` | 0 | ⬜ |
| `WEB-020.159` | 2224–2230 | بلوک | `downloadStatistics` | 0 | ⬜ |
| `WEB-020.160` | 2231–2235 | بلوک | `printStatistics` | 0 | ⬜ |
| `WEB-020.161` | 2236–2243 | بلوک | `selectDay` | 0 | ⬜ |
| `WEB-020.162` | 2244–2249 | بلوک | `changeMonth` | 0 | ⬜ |
| `WEB-020.163` | 2250–2286 | بلوک | `switchTab` | 0 | ⬜ |
| `WEB-020.164` | 2287–2300 | بلوک | `emergencyEnsurePeople` | 0 | ⬜ |
| `WEB-020.165` | 2301–2309 | بلوک | `emergencyPanel` | 0 | ⬜ |
| `WEB-020.166` | 2310–2344 | بلوک | `msgs` | 0 | ⬜ |
| `WEB-020.167` | 2345–2350 | بلوک | `emergencyLoadOrders` | 0 | ⬜ |
| `WEB-020.168` | 2351–2395 | بلوک | `daySearchInput` | 4 | ⬜ |
| `WEB-020.169` | 2396–2406 | بلوک | `emergencyClick` | 0 | ⬜ |
| `WEB-020.170` | 2407–2435 | بلوک | `person` | 0 | ⬜ |
| `WEB-020.171` | 2436–2483 | بلوک | `eventClick` | 0 | ⬜ |
| `WEB-020.172` | 2484–2593 | بلوک | `person` | 1 | ⬜ |
| `WEB-020.173` | 2594–2602 | بلوک | `dayPickFor` | 0 | ⬜ |
| `WEB-020.174` | 2603–2609 | بلوک | `trackDayPick` | 0 | ⬜ |
| `WEB-020.175` | 2610–2617 | بلوک | `orderedDayFoodIds` | 0 | ⬜ |
| `WEB-020.176` | 2618–2654 | بلوک | `eventChange` | 0 | ⬜ |
| `WEB-020.177` | 2655–2688 | بلوک | `person` | 0 | ⬜ |
| `WEB-020.178` | 2689–2771 | بلوک | `eventSubmit` | 0 | ⬜ |
| `WEB-020.179` | 2772–2777 | بلوک | `spanDays` | 0 | ⬜ |
| `WEB-020.180` | 2778–2806 | بلوک | `mount` | 0 | ⬜ |
| `WEB-020.181` | 2807–2807 | بلوک | `reportInputFocused` | 0 | ⬜ |
| `WEB-020.182` | 2808–2810 | بلوک | `safeReportRender` | 0 | ⬜ |
| `WEB-020.183` | 2811–2820 | بلوک | `html` | 0 | ⬜ |

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
<summary><b>WEB-022</b> — <code>food-ticket-web/assets/food-order.css</code> (218 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-022.01` | 1–111 | بلوک | `سفارش غذا — از همان رنگ‌ها، کارت‌ها و فرم‌های asse` | 2 | ⬜ |
| `WEB-022.02` | 112–116 | بلوک | `تأیید عملیات در DOM پایدار می‌ماند تا بازخوانی تقو` | 0 | ⬜ |
| `WEB-022.03` | 117–169 | بلوک | `انتخاب شخص پیش از فعال‌شدن تقویم؛ منوی اصلی سامانه` | 1 | ⬜ |
| `WEB-022.04` | 170–175 | بلوک | `نام غذا در سلول تقویم در یک خط نمایش داده می‌شود؛ ` | 0 | ⬜ |
| `WEB-022.05` | 176–178 | بلوک | `Separate lunch and dinner without changing the exi` | 0 | ⬜ |
| `WEB-022.06` | 179–179 | بلوک | `فهرست شخصی درخواست مهمان: فضای محدود، خوانا و مستق` | 0 | ⬜ |
| `WEB-022.07` | 180–191 | بلوک | `A single compact row per request. The table scroll` | 0 | ⬜ |
| `WEB-022.08` | 192–214 | بلوک | `Daily food reconciliation and monthly invoice reco` | 2 | ⬜ |
| `WEB-022.09` | 215–218 | بلوک | `1.62.93 follow-up: single-line sortable header but` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-023</b> — <code>food-ticket-web/assets/food-org-groups.js</code> (293 خط، 15 آدرس، بازبینی‌شده 0/15)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-023.01` | 1–25 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-023.02` | 26–37 | بلوک | `typeLabel` | 0 | ⬜ |
| `WEB-023.03` | 38–52 | بلوک | `typeOrder` | 0 | ⬜ |
| `WEB-023.04` | 53–83 | بلوک | `load` | 0 | ⬜ |
| `WEB-023.05` | 84–93 | بلوک | `invalidate` | 0 | ⬜ |
| `WEB-023.06` | 94–114 | بلوک | `keysForRow` | 0 | ⬜ |
| `WEB-023.07` | 115–123 | بلوک | `digits` | 0 | ⬜ |
| `WEB-023.08` | 124–141 | بلوک | `groupOf` | 0 | ⬜ |
| `WEB-023.09` | 142–181 | بلوک | `split` | 0 | ⬜ |
| `WEB-023.10` | 182–198 | بلوک | `groupHeaderHtml` | 0 | ⬜ |
| `WEB-023.11` | 199–224 | بلوک | `foodBreakdown` | 0 | ⬜ |
| `WEB-023.12` | 225–239 | بلوک | `uniquePeopleCount` | 0 | ⬜ |
| `WEB-023.13` | 240–245 | بلوک | `faNum` | 0 | ⬜ |
| `WEB-023.14` | 246–258 | بلوک | `escapeHtml` | 0 | ⬜ |
| `WEB-023.15` | 259–293 | بلوک | `render` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-024</b> — <code>food-ticket-web/assets/food-ticket-global-theme-r42.css</code> (169 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-024.01` | 1–85 | بلوک | `R42 shared system color mode for the full food-tic` | 1 | ⬜ |
| `WEB-024.02` | 86–128 | بلوک | `R44: theme-aware food planning forms, schedules an` | 0 | ⬜ |
| `WEB-024.03` | 129–159 | بلوک | `v1.62.4: پوشش کامل حالت شب برای فرم‌های برنامه غذا` | 0 | ⬜ |
| `WEB-024.04` | 160–169 | بلوک | `v1.62.4: سفارش غذا — سطح‌هایی که رنگ روشن ثابت یا ` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-025</b> — <code>food-ticket-web/assets/food-ticket-persistence.js</code> (24 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-025.01` | 1–5 | بلوک | `Food Ticket persistence helpers — guest cards & em` | 0 | ⬜ |
| `WEB-025.02` | 6–9 | بلوک | `apiGuestCards` | 0 | ⬜ |
| `WEB-025.03` | 10–24 | بلوک | `apiEmployees` | 1 | ⬜ |

</details>

<details>
<summary><b>WEB-026</b> — <code>food-ticket-web/assets/food-ticket-theme.css</code> (435 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-026.01` | 1–263 | بلوک | `مقدمهٔ فایل` | 3 | ⬜ |
| `WEB-026.02` | 264–276 | بلوک | `چاپ گزارش: فقط جدول و آمار، بدون منو و دکمه‌ها` | 0 | ⬜ |
| `WEB-026.03` | 277–291 | بلوک | `Center-align data tables and grid-based list rows ` | 0 | ⬜ |
| `WEB-026.04` | 292–321 | بلوک | `Print only the report currently open in its previe` | 0 | ⬜ |
| `WEB-026.05` | 322–334 | بلوک | `گروه‌های غذا: مهلت تغییر سلف جدا و برجسته از سایر ` | 0 | ⬜ |
| `WEB-026.06` | 335–360 | بلوک | `مودال لیست اعضا به body منتقل می‌شود تا همیشه بالا` | 1 | ⬜ |
| `WEB-026.07` | 361–421 | بلوک | `داشبورد مدیریت غذا؛ چیدمان الهام‌گرفته از نمونهٔ ت` | 2 | ⬜ |
| `WEB-026.08` | 422–435 | بلوک | `1.62.93 follow-up: single-line member tables + com` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-027</b> — <code>food-ticket-web/assets/jalali-calendar.js</code> (347 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-027.01` | 1–12 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-027.02` | 13–14 | بلوک | `pad` | 0 | ⬜ |
| `WEB-027.03` | 15–44 | بلوک | `j2g` | 0 | ⬜ |
| `WEB-027.04` | 45–66 | بلوک | `g2j` | 0 | ⬜ |
| `WEB-027.05` | 67–73 | بلوک | `monthLength` | 0 | ⬜ |
| `WEB-027.06` | 74–81 | بلوک | `parseJalali` | 0 | ⬜ |
| `WEB-027.07` | 82–85 | بلوک | `formatJalali` | 0 | ⬜ |
| `WEB-027.08` | 86–99 | بلوک | `holidaySet` | 0 | ⬜ |
| `WEB-027.09` | 100–100 | بلوک | `weekendDays` | 0 | ⬜ |
| `WEB-027.10` | 101–109 | بلوک | `raw` | 0 | ⬜ |
| `WEB-027.11` | 110–115 | بلوک | `isWeekendJalali` | 0 | ⬜ |
| `WEB-027.12` | 116–120 | بلوک | `todayJ` | 0 | ⬜ |
| `WEB-027.13` | 121–134 | بلوک | `bindPicker` | 0 | ⬜ |
| `WEB-027.14` | 135–135 | بلوک | `close` | 0 | ⬜ |
| `WEB-027.15` | 136–137 | بلوک | `open` | 0 | ⬜ |
| `WEB-027.16` | 138–143 | بلوک | `render` | 0 | ⬜ |
| `WEB-027.17` | 144–215 | بلوک | `startIndex` | 0 | ⬜ |
| `WEB-027.18` | 216–227 | بلوک | `installOutsideClickHandler` | 0 | ⬜ |
| `WEB-027.19` | 228–234 | بلوک | `bindYearBoard` | 0 | ⬜ |
| `WEB-027.20` | 235–243 | بلوک | `render` | 0 | ⬜ |
| `WEB-027.21` | 244–280 | بلوک | `startIndex` | 0 | ⬜ |
| `WEB-027.22` | 281–347 | بلوک | `enhanceAllDateFields` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-028</b> — <code>food-ticket-web/assets/list-sort.js</code> (323 خط، 23 آدرس، بازبینی‌شده 0/23)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-028.01` | 1–15 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-028.02` | 16–19 | بلوک | `collator` | 0 | ⬜ |
| `WEB-028.03` | 20–32 | بلوک | `injectStyle` | 0 | ⬜ |
| `WEB-028.04` | 33–41 | بلوک | `normalize` | 0 | ⬜ |
| `WEB-028.05` | 42–44 | بلوک | `pad` | 0 | ⬜ |
| `WEB-028.06` | 45–55 | بلوک | `sortKey` | 4 | ⬜ |
| `WEB-028.07` | 56–64 | بلوک | `compare` | 0 | ⬜ |
| `WEB-028.08` | 65–84 | بلوک | `collect` | 0 | ⬜ |
| `WEB-028.09` | 85–98 | بلوک | `hasAny` | 0 | ⬜ |
| `WEB-028.10` | 99–112 | بلوک | `anchorFor` | 0 | ⬜ |
| `WEB-028.11` | 113–140 | بلوک | `applySort` | 0 | ⬜ |
| `WEB-028.12` | 141–156 | بلوک | `updateHeaders` | 0 | ⬜ |
| `WEB-028.13` | 157–190 | بلوک | `attach` | 0 | ⬜ |
| `WEB-028.14` | 191–205 | بلوک | `enhance` | 0 | ⬜ |
| `WEB-028.15` | 206–215 | بلوک | `scheduleEnhance` | 0 | ⬜ |
| `WEB-028.16` | 216–234 | بلوک | `buildView` | 0 | ⬜ |
| `WEB-028.17` | 235–241 | بلوک | `fileLabel` | 0 | ⬜ |
| `WEB-028.18` | 242–252 | بلوک | `saveBlob` | 0 | ⬜ |
| `WEB-028.19` | 253–269 | بلوک | `exportXlsx` | 0 | ⬜ |
| `WEB-028.20` | 270–277 | بلوک | `exportContainer` | 0 | ⬜ |
| `WEB-028.21` | 278–291 | بلوک | `activeListFor` | 0 | ⬜ |
| `WEB-028.22` | 292–302 | بلوک | `onExportClick` | 0 | ⬜ |
| `WEB-028.23` | 303–323 | بلوک | `start` | 0 | ⬜ |

</details>

<details>
<summary><b>WEB-029</b> — <code>food-ticket-web/template-bridge.js</code> (27 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `WEB-029.01` | 1–1 | بلوک | `مقدمهٔ فایل` | 0 | ⬜ |
| `WEB-029.02` | 2–2 | بلوک | `apiBase` | 0 | ⬜ |
| `WEB-029.03` | 3–3 | بلوک | `csrf` | 0 | ⬜ |
| `WEB-029.04` | 4–13 | بلوک | `loadTemplate` | 0 | ⬜ |
| `WEB-029.05` | 14–27 | بلوک | `saveTemplate` | 0 | ⬜ |

</details>

### موتور مشترک (lib/) — `CORE`


<details>
<summary><b>CORE-001</b> — <code>lib/activity-audit.php</code> (601 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-001.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-001.02` | 14–27 | تابع | `activity_logs_ensure` | 0 | ⬜ |
| `CORE-001.03` | 29–58 | تابع | `activity_client_ip` | 0 | ⬜ |
| `CORE-001.04` | 60–88 | تابع | `activity_computer_name` | 1 | ⬜ |
| `CORE-001.05` | 90–300 | تابع | `activity_action_label` | 1 | ⬜ |
| `CORE-001.06` | 302–310 | تابع | `activity_default_result` | 0 | ⬜ |
| `CORE-001.07` | 312–354 | تابع | `activity_log` | 0 | ⬜ |
| `CORE-001.08` | 355–361 | ناحیه | `بلوک رویه‌ای 355-361` | 0 | ⬜ |
| `CORE-001.09` | 362–396 | تابع | `activity_log_view` | 2 | ⬜ |
| `CORE-001.10` | 398–410 | تابع | `activity_logs_prune` | 0 | ⬜ |
| `CORE-001.11` | 411–417 | ناحیه | `بلوک رویه‌ای 411-417` | 0 | ⬜ |
| `CORE-001.12` | 418–449 | تابع | `ops_low_value_tables_prune` | 0 | ⬜ |
| `CORE-001.13` | 451–455 | تابع | `activity_log_retention_days` | 0 | ⬜ |
| `CORE-001.14` | 457–509 | تابع | `activity_audit_context` | 0 | ⬜ |
| `CORE-001.15` | 511–526 | تابع | `save_audit` | 0 | ⬜ |
| `CORE-001.16` | 528–549 | تابع | `save_audit_required` | 0 | ⬜ |
| `CORE-001.17` | 551–600 | تابع | `log_ticket_event` | 1 | ⬜ |

</details>

<details>
<summary><b>CORE-002</b> — <code>lib/asset-dedupe.php</code> (95 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-002.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `CORE-002.02` | 4–17 | تابع | `asset_dedupe_find_root` | 0 | ⬜ |
| `CORE-002.03` | 19–37 | تابع | `asset_dedupe_filter_mac_groups` | 0 | ⬜ |
| `CORE-002.04` | 39–94 | تابع | `asset_dedupe_group_rows` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-003</b> — <code>lib/asset-identity.php</code> (4 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-003.01` | 1–4 | ناحیه | `بلوک رویه‌ای 1-4` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-004</b> — <code>lib/auth-scope.php</code> (703 خط، 26 آدرس، بازبینی‌شده 0/26)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-004.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-004.02` | 14–28 | تابع | `current_user` | 0 | ⬜ |
| `CORE-004.03` | 30–44 | تابع | `login_user` | 0 | ⬜ |
| `CORE-004.04` | 46–53 | تابع | `require_login` | 0 | ⬜ |
| `CORE-004.05` | 55–63 | تابع | `require_admin` | 0 | ⬜ |
| `CORE-004.06` | 65–76 | تابع | `require_primary_admin` | 0 | ⬜ |
| `CORE-004.07` | 78–86 | تابع | `require_staff` | 0 | ⬜ |
| `CORE-004.08` | 88–96 | تابع | `require_supervisor` | 0 | ⬜ |
| `CORE-004.09` | 98–132 | تابع | `ticket_manager_scope_unit_ids` | 0 | ⬜ |
| `CORE-004.10` | 134–160 | تابع | `ticket_sole_unit_head` | 0 | ⬜ |
| `CORE-004.11` | 162–171 | تابع | `ticket_requester_tree_predicate` | 5 | ⬜ |
| `CORE-004.12` | 173–190 | تابع | `ticket_attribution_predicate` | 0 | ⬜ |
| `CORE-004.13` | 192–197 | تابع | `ticket_attribution_in_unit_ids` | 0 | ⬜ |
| `CORE-004.14` | 199–293 | تابع | `ticket_user_can_view` | 5 | ⬜ |
| `CORE-004.15` | 296–378 | تابع | `ticket_handler_level` | 5 | ⬜ |
| `CORE-004.16` | 380–397 | تابع | `ticket_view_mode` | 0 | ⬜ |
| `CORE-004.17` | 399–420 | تابع | `ticket_can_see_internal` | 0 | ⬜ |
| `CORE-004.18` | 422–502 | تابع | `ticket_assignee_is_eligible` | 0 | ⬜ |
| `CORE-004.19` | 504–543 | تابع | `ticket_assignee_group_ok` | 0 | ⬜ |
| `CORE-004.20` | 545–574 | تابع | `ticket_rating_is_current` | 4 | ⬜ |
| `CORE-004.21` | 576–580 | تابع | `ticket_user_is_requester` | 0 | ⬜ |
| `CORE-004.22` | 582–591 | تابع | `ticket_reply_starts_work` | 0 | ⬜ |
| `CORE-004.23` | 593–650 | تابع | `ticket_scope` | 5 | ⬜ |
| `CORE-004.24` | 652–656 | تابع | `ticket_is_waiting_status` | 0 | ⬜ |
| `CORE-004.25` | 658–663 | تابع | `user_service_group` | 0 | ⬜ |
| `CORE-004.26` | 665–701 | تابع | `asset_scope` | 5 | ⬜ |

</details>

<details>
<summary><b>CORE-005</b> — <code>lib/db-core.php</code> (638 خط، 36 آدرس، بازبینی‌شده 0/36)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-005.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-005.02` | 14–18 | تابع | `csp_nonce` | 0 | ⬜ |
| `CORE-005.03` | 20–70 | ناحیه | `Enforce HTTPS in production` | 0 | ⬜ |
| `CORE-005.04` | 71–82 | تابع | `cfg` | 0 | ⬜ |
| `CORE-005.05` | 83–85 | ناحیه | `بلوک رویه‌ای 83-85` | 0 | ⬜ |
| `CORE-005.06` | 86–112 | کلاس | `AppPDO` | 4 | ⬜ |
| `CORE-005.07` | 114–141 | کلاس | `AppSlowStatement` | 4 | ⬜ |
| `CORE-005.08` | 143–191 | تابع | `db` | **8** | ⬜ |
| `CORE-005.09` | 193–219 | تابع | `db_table_exists` | 0 | ⬜ |
| `CORE-005.10` | 221–240 | تابع | `db_table_columns` | 0 | ⬜ |
| `CORE-005.11` | 242–246 | تابع | `db_missing_columns` | 0 | ⬜ |
| `CORE-005.12` | 248–256 | تابع | `db_column_present` | 0 | ⬜ |
| `CORE-005.13` | 258–274 | تابع | `db_add_missing_column` | 4 | ⬜ |
| `CORE-005.14` | 276–318 | تابع | `db_ensure_enum_values` | **8** | ⬜ |
| `CORE-005.15` | 320–323 | تابع | `client_ip` | 0 | ⬜ |
| `CORE-005.16` | 325–325 | ناحیه | `Rate limiting for API endpoints` | 0 | ⬜ |
| `CORE-005.17` | 326–326 | ناحیه | `بازمی‌گرداند true اگر مجاز باشد. اگر $retryAfter د` | 0 | ⬜ |
| `CORE-005.18` | 327–354 | تابع | `api_rate_limit` | 1 | ⬜ |
| `CORE-005.19` | 356–369 | تابع | `check_api_rate_limit` | 0 | ⬜ |
| `CORE-005.20` | 371–374 | تابع | `login_rate_key` | 0 | ⬜ |
| `CORE-005.21` | 376–395 | تابع | `login_rate_allowed` | 1 | ⬜ |
| `CORE-005.22` | 397–425 | تابع | `record_login_failure` | 1 | ⬜ |
| `CORE-005.23` | 427–434 | تابع | `clear_login_failures` | 1 | ⬜ |
| `CORE-005.24` | 436–445 | تابع | `app_version` | 1 | ⬜ |
| `CORE-005.25` | 447–450 | تابع | `e` | 0 | ⬜ |
| `CORE-005.26` | 452–457 | تابع | `app_theme_mode` | 2 | ⬜ |
| `CORE-005.27` | 459–465 | تابع | `app_theme_toggle_markup` | 0 | ⬜ |
| `CORE-005.28` | 467–471 | تابع | `redirect` | 0 | ⬜ |
| `CORE-005.29` | 473–501 | تابع | `db_install_token` | 4 | ⬜ |
| `CORE-005.30` | 503–507 | تابع | `db_install_token_forget` | 0 | ⬜ |
| `CORE-005.31` | 509–514 | تابع | `db_install_fresh_tables` | 0 | ⬜ |
| `CORE-005.32` | 516–553 | تابع | `db_install_state` | 5 | ⬜ |
| `CORE-005.33` | 555–570 | تابع | `db_in_transaction` | 0 | ⬜ |
| `CORE-005.34` | 572–602 | تابع | `db_commit` | 0 | ⬜ |
| `CORE-005.35` | 604–621 | تابع | `db_commit_or_fail` | 0 | ⬜ |
| `CORE-005.36` | 623–636 | تابع | `db_rollback` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-006</b> — <code>lib/fa-digits.php</code> (199 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-006.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `CORE-006.02` | 22–26 | تابع | `fa_digits_map` | 0 | ⬜ |
| `CORE-006.03` | 28–69 | تابع | `fa_digits_text` | 0 | ⬜ |
| `CORE-006.04` | 71–75 | تابع | `fa_digits_skip_tags` | 0 | ⬜ |
| `CORE-006.05` | 77–167 | تابع | `fa_digits_html` | 0 | ⬜ |
| `CORE-006.06` | 169–198 | تابع | `fa_digits_output_callback` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-007</b> — <code>lib/http-input.php</code> (25 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-007.01` | 1–12 | ناحیه | `بلوک رویه‌ای 1-12` | 0 | ⬜ |
| `CORE-007.02` | 13–16 | تابع | `post_value` | 2 | ⬜ |
| `CORE-007.03` | 17–19 | ناحیه | `بلوک رویه‌ای 17-19` | 0 | ⬜ |
| `CORE-007.04` | 20–23 | تابع | `valid_choice` | 0 | ⬜ |
| `CORE-007.05` | 24–25 | ناحیه | `بلوک رویه‌ای 24-25` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-008</b> — <code>lib/http-post.php</code> (91 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-008.01` | 1–14 | ناحیه | `بلوک رویه‌ای 1-14` | 0 | ⬜ |
| `CORE-008.02` | 15–47 | تابع | `post_context_begin` | 2 | ⬜ |
| `CORE-008.03` | 49–90 | تابع | `post_context_on_error` | 6 | ⬜ |

</details>

<details>
<summary><b>CORE-009</b> — <code>lib/ldap.php</code> (412 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-009.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-009.02` | 14–32 | تابع | `ldap_secret_key` | 0 | ⬜ |
| `CORE-009.03` | 34–51 | تابع | `ldap_secret_encrypt` | 0 | ⬜ |
| `CORE-009.04` | 53–90 | تابع | `ldap_secret_decrypt` | 0 | ⬜ |
| `CORE-009.05` | 92–116 | تابع | `ldap_cfg` | 0 | ⬜ |
| `CORE-009.06` | 118–133 | تابع | `ldap_role_for_groups` | 0 | ⬜ |
| `CORE-009.07` | 135–145 | تابع | `ldap_person_filter` | 0 | ⬜ |
| `CORE-009.08` | 147–162 | تابع | `ldap_auth_log` | 1 | ⬜ |
| `CORE-009.09` | 164–240 | تابع | `ldap_diagnose` | 3 | ⬜ |
| `CORE-009.10` | 242–410 | تابع | `ldap_sync_all_users` | 5 | ⬜ |

</details>

<details>
<summary><b>CORE-010</b> — <code>lib/log-catalog.php</code> (50 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-010.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `CORE-010.02` | 4–49 | تابع | `logging_catalog_definitions` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-011</b> — <code>lib/logging-audit.php</code> (643 خط، 33 آدرس، بازبینی‌شده 0/33)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-011.01` | 1–45 | ناحیه | `بلوک رویه‌ای 1-45` | 1 | ⬜ |
| `CORE-011.02` | 46–50 | تابع | `logging_audit_baseline_path` | 0 | ⬜ |
| `CORE-011.03` | 52–59 | تابع | `logging_audit_structural_checks` | 0 | ⬜ |
| `CORE-011.04` | 61–82 | تابع | `logging_audit_required_tables` | 0 | ⬜ |
| `CORE-011.05` | 84–100 | تابع | `logging_audit_required_columns` | 0 | ⬜ |
| `CORE-011.06` | 102–106 | تابع | `logging_audit_finding` | 0 | ⬜ |
| `CORE-011.07` | 108–124 | تابع | `logging_audit_table_exists` | 0 | ⬜ |
| `CORE-011.08` | 126–136 | تابع | `logging_audit_table_columns` | 0 | ⬜ |
| `CORE-011.09` | 138–138 | ناحیه | `-` | 0 | ⬜ |
| `CORE-011.10` | 139–139 | ناحیه | `بررسی‌های ساختاری — روی پایگاه دادهٔ زندهٔ همین نص` | 0 | ⬜ |
| `CORE-011.11` | 140–141 | ناحیه | `-` | 0 | ⬜ |
| `CORE-011.12` | 142–157 | تابع | `logging_audit_check_db_schema_tables` | 0 | ⬜ |
| `CORE-011.13` | 159–180 | تابع | `logging_audit_check_db_schema_columns` | 0 | ⬜ |
| `CORE-011.14` | 182–223 | تابع | `logging_audit_check_db_pending_migrations` | 0 | ⬜ |
| `CORE-011.15` | 225–248 | تابع | `logging_audit_check_filesystem_writable` | 1 | ⬜ |
| `CORE-011.16` | 250–250 | ناحیه | `-` | 0 | ⬜ |
| `CORE-011.17` | 251–251 | ناحیه | `بررسی‌های رفتاری — سلامت کد در حال اجرا` | 0 | ⬜ |
| `CORE-011.18` | 252–253 | ناحیه | `-` | 0 | ⬜ |
| `CORE-011.19` | 254–276 | تابع | `logging_audit_check_spool_backlog` | 0 | ⬜ |
| `CORE-011.20` | 278–317 | تابع | `logging_audit_check_unresolved_issues` | 0 | ⬜ |
| `CORE-011.21` | 319–342 | تابع | `logging_audit_check_worker_heartbeats` | 1 | ⬜ |
| `CORE-011.22` | 344–344 | ناحیه | `-` | 0 | ⬜ |
| `CORE-011.23` | 345–345 | ناحیه | `اجرا` | 0 | ⬜ |
| `CORE-011.24` | 346–347 | ناحیه | `-` | 0 | ⬜ |
| `CORE-011.25` | 348–352 | تابع | `logging_audit_heartbeat_path` | 0 | ⬜ |
| `CORE-011.26` | 354–382 | تابع | `logging_audit_check_audit_continuity` | 1 | ⬜ |
| `CORE-011.27` | 384–398 | تابع | `logging_audit_write_heartbeat` | 2 | ⬜ |
| `CORE-011.28` | 400–432 | تابع | `logging_audit_summarize` | 0 | ⬜ |
| `CORE-011.29` | 434–447 | تابع | `logging_audit_checks` | 0 | ⬜ |
| `CORE-011.30` | 449–457 | تابع | `logging_audit_load_baseline` | 1 | ⬜ |
| `CORE-011.31` | 459–472 | تابع | `logging_audit_save_baseline` | 2 | ⬜ |
| `CORE-011.32` | 474–506 | تابع | `logging_audit_run` | 0 | ⬜ |
| `CORE-011.33` | 508–642 | تابع | `logging_audit_run_and_record` | 7 | ⬜ |

</details>

<details>
<summary><b>CORE-012</b> — <code>lib/logging-core.php</code> (1,561 خط، 66 آدرس، بازبینی‌شده 0/66)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-012.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `CORE-012.02` | 16–20 | ناحیه | `سقف سخت صف اضطراری؛ فقط هنگام اختلال دیتابیس استفا` | 0 | ⬜ |
| `CORE-012.03` | 21–36 | تابع | `logging_uuid` | 0 | ⬜ |
| `CORE-012.04` | 38–45 | تابع | `logging_deterministic_uuid` | 0 | ⬜ |
| `CORE-012.05` | 47–50 | تابع | `logging_is_uuid_v4` | 0 | ⬜ |
| `CORE-012.06` | 52–55 | تابع | `logging_is_uuid` | 0 | ⬜ |
| `CORE-012.07` | 57–63 | تابع | `logging_request_id` | 0 | ⬜ |
| `CORE-012.08` | 65–69 | تابع | `logging_trace_id` | 0 | ⬜ |
| `CORE-012.09` | 71–75 | تابع | `logging_operation_id` | 0 | ⬜ |
| `CORE-012.10` | 77–87 | تابع | `logging_begin_execution` | 0 | ⬜ |
| `CORE-012.11` | 89–96 | تابع | `logging_begin_operation` | 0 | ⬜ |
| `CORE-012.12` | 98–101 | تابع | `logging_operation_name` | 0 | ⬜ |
| `CORE-012.13` | 103–113 | تابع | `logging_correlation_context` | 0 | ⬜ |
| `CORE-012.14` | 115–119 | تابع | `logging_parent_event_id` | 0 | ⬜ |
| `CORE-012.15` | 121–131 | تابع | `logging_bootstrap` | 0 | ⬜ |
| `CORE-012.16` | 133–141 | تابع | `logging_catalog_aliases` | 0 | ⬜ |
| `CORE-012.17` | 143–151 | تابع | `logging_event_catalog` | 0 | ⬜ |
| `CORE-012.18` | 153–173 | تابع | `logging_catalog_resolve` | 0 | ⬜ |
| `CORE-012.19` | 175–320 | تابع | `logging_action_event_definition` | 1 | ⬜ |
| `CORE-012.20` | 322–329 | تابع | `logging_normalize_digits` | 0 | ⬜ |
| `CORE-012.21` | 331–341 | تابع | `logging_is_valid_iranian_national_code` | 0 | ⬜ |
| `CORE-012.22` | 343–346 | تابع | `logging_strict_national_code_enabled` | 0 | ⬜ |
| `CORE-012.23` | 348–369 | تابع | `logging_sensitive_key` | 0 | ⬜ |
| `CORE-012.24` | 371–379 | تابع | `logging_utf8_substr` | 0 | ⬜ |
| `CORE-012.25` | 381–389 | تابع | `logging_utf8_strlen` | 0 | ⬜ |
| `CORE-012.26` | 391–409 | تابع | `logging_sanitize_text` | 0 | ⬜ |
| `CORE-012.27` | 411–417 | تابع | `logging_sanitize_single_line` | 0 | ⬜ |
| `CORE-012.28` | 418–419 | ناحیه | `بلوک رویه‌ای 418-419` | 0 | ⬜ |
| `CORE-012.29` | 420–429 | تابع | `logging_fingerprint_v2` | 0 | ⬜ |
| `CORE-012.30` | 431–436 | تابع | `logging_fingerprint_v3` | 0 | ⬜ |
| `CORE-012.31` | 438–443 | تابع | `logging_fingerprint_current` | 0 | ⬜ |
| `CORE-012.32` | 445–469 | تابع | `logging_exception_trace` | 0 | ⬜ |
| `CORE-012.33` | 471–632 | تابع | `logging_sanitize_event_meta` | 1 | ⬜ |
| `CORE-012.34` | 634–672 | تابع | `logging_sanitize_value` | 0 | ⬜ |
| `CORE-012.35` | 674–682 | تابع | `logging_safe_request_uri` | 0 | ⬜ |
| `CORE-012.36` | 684–780 | تابع | `logging_spool_write` | 3 | ⬜ |
| `CORE-012.37` | 782–786 | تابع | `logging_spool_stats_cache_invalidate` | 5 | ⬜ |
| `CORE-012.38` | 788–806 | تابع | `logging_spool_stats` | 3 | ⬜ |
| `CORE-012.39` | 808–869 | تابع | `logging_spool_stats_uncached` | 3 | ⬜ |
| `CORE-012.40` | 871–925 | تابع | `logging_increment_counter` | 3 | ⬜ |
| `CORE-012.41` | 927–959 | تابع | `logging_health_counters` | 1 | ⬜ |
| `CORE-012.42` | 961–982 | تابع | `logging_atomic_replace_file` | 7 | ⬜ |
| `CORE-012.43` | 984–1024 | تابع | `logging_worker_heartbeat` | 7 | ⬜ |
| `CORE-012.44` | 1026–1073 | تابع | `logging_emergency_failure` | **11** | ⬜ |
| `CORE-012.45` | 1075–1078 | تابع | `logging_emergency_failure_rotation_warning` | 0 | ⬜ |
| `CORE-012.46` | 1080–1084 | تابع | `logging_persistence_status` | 0 | ⬜ |
| `CORE-012.47` | 1086–1103 | تابع | `logging_record_failure` | 0 | ⬜ |
| `CORE-012.48` | 1105–1109 | تابع | `logging_json` | 0 | ⬜ |
| `CORE-012.49` | 1112–1154 | تابع | `logging_append_legacy_file` | 7 | ⬜ |
| `CORE-012.50` | 1156–1174 | تابع | `logging_replay_insert` | 6 | ⬜ |
| `CORE-012.51` | 1176–1237 | تابع | `logging_issue_upsert` | **8** | ⬜ |
| `CORE-012.52` | 1239–1252 | تابع | `logging_db_error_is_transient` | 0 | ⬜ |
| `CORE-012.53` | 1254–1270 | تابع | `logging_replay_error_classify` | 0 | ⬜ |
| `CORE-012.54` | 1272–1276 | تابع | `logging_db_circuit_path` | 0 | ⬜ |
| `CORE-012.55` | 1278–1283 | تابع | `logging_db_circuit_release_probe` | 2 | ⬜ |
| `CORE-012.56` | 1285–1297 | تابع | `logging_db_circuit_with_lock` | 3 | ⬜ |
| `CORE-012.57` | 1299–1320 | تابع | `logging_db_circuit_should_skip` | 3 | ⬜ |
| `CORE-012.58` | 1322–1337 | تابع | `logging_db_circuit_failure` | 7 | ⬜ |
| `CORE-012.59` | 1339–1345 | تابع | `logging_db_circuit_success` | **9** | ⬜ |
| `CORE-012.60` | 1347–1363 | تابع | `logging_duplicate_event_already_stored` | 5 | ⬜ |
| `CORE-012.61` | 1365–1412 | تابع | `logging_store_system` | 1 | ⬜ |
| `CORE-012.62` | 1414–1445 | تابع | `logging_normalize_activity_event` | 0 | ⬜ |
| `CORE-012.63` | 1447–1515 | تابع | `logging_store_activity` | 1 | ⬜ |
| `CORE-012.64` | 1517–1531 | تابع | `log_event` | 0 | ⬜ |
| `CORE-012.65` | 1532–1534 | ناحیه | `بلوک رویه‌ای 1532-1534` | 0 | ⬜ |
| `CORE-012.66` | 1535–1560 | تابع | `security_log_access_denied` | 4 | ⬜ |

</details>

<details>
<summary><b>CORE-013</b> — <code>lib/logging-csrf.php</code> (637 خط، 24 آدرس، بازبینی‌شده 0/24)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-013.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-013.02` | 14–17 | تابع | `flash` | 0 | ⬜ |
| `CORE-013.03` | 19–54 | تابع | `ticket_service_ensure_schema` | 5 | ⬜ |
| `CORE-013.04` | 56–76 | تابع | `ticket_service_categories` | 0 | ⬜ |
| `CORE-013.05` | 78–98 | تابع | `ticket_service_category_map` | 0 | ⬜ |
| `CORE-013.06` | 100–112 | تابع | `system_logs_ensure` | 0 | ⬜ |
| `CORE-013.07` | 114–157 | تابع | `system_log` | 0 | ⬜ |
| `CORE-013.08` | 159–206 | تابع | `system_error_explain` | 0 | ⬜ |
| `CORE-013.09` | 208–212 | تابع | `system_error_mask` | 0 | ⬜ |
| `CORE-013.10` | 214–258 | تابع | `system_error_record` | 0 | ⬜ |
| `CORE-013.11` | 260–348 | ناحیه | `هندلرهای سراسری برای HTTP و CLI؛ خروجی هر محیط مست` | 2 | ⬜ |
| `CORE-013.12` | 349–368 | تابع | `app_error_log` | 0 | ⬜ |
| `CORE-013.13` | 370–396 | تابع | `system_log_meta_html` | 0 | ⬜ |
| `CORE-013.14` | 398–421 | تابع | `db_delete_in_batches` | 6 | ⬜ |
| `CORE-013.15` | 423–447 | تابع | `system_logs_prune` | 0 | ⬜ |
| `CORE-013.16` | 449–470 | تابع | `system_logs_recent` | 5 | ⬜ |
| `CORE-013.17` | 472–477 | تابع | `take_flash` | 0 | ⬜ |
| `CORE-013.18` | 479–482 | تابع | `csrf_field` | 0 | ⬜ |
| `CORE-013.19` | 484–510 | تابع | `csrf_token` | 0 | ⬜ |
| `CORE-013.20` | 512–529 | تابع | `csrf_discard_legacy_cookie` | 2 | ⬜ |
| `CORE-013.21` | 531–566 | تابع | `require_csrf` | 6 | ⬜ |
| `CORE-013.22` | 568–580 | تابع | `csr_request_wants_json` | 0 | ⬜ |
| `CORE-013.23` | 582–592 | تابع | `csrf_rotate_after_mismatch` | 0 | ⬜ |
| `CORE-013.24` | 594–635 | تابع | `csrf_redirect_back` | 2 | ⬜ |

</details>

<details>
<summary><b>CORE-014</b> — <code>lib/logging-diagnostics.php</code> (118 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-014.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `CORE-014.02` | 4–16 | تابع | `logging_diagnostic_rules` | 0 | ⬜ |
| `CORE-014.03` | 18–51 | تابع | `logging_diagnostic_analyze` | 0 | ⬜ |
| `CORE-014.04` | 53–63 | تابع | `logging_diagnostic_safe_value` | 1 | ⬜ |
| `CORE-014.05` | 65–77 | تابع | `logging_diagnostic_safe_stack` | 0 | ⬜ |
| `CORE-014.06` | 79–96 | تابع | `logging_diagnostic_similarity` | 0 | ⬜ |
| `CORE-014.07` | 98–117 | تابع | `logging_diagnostic_redact_event` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-015</b> — <code>lib/logging-escalation.php</code> (100 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-015.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `CORE-015.02` | 19–27 | تابع | `logging_escalation_thresholds` | 0 | ⬜ |
| `CORE-015.03` | 29–57 | تابع | `logging_escalation_count` | 4 | ⬜ |
| `CORE-015.04` | 59–65 | تابع | `logging_escalation_is_critical` | 0 | ⬜ |
| `CORE-015.05` | 67–100 | تابع | `logging_smart_summary` | 4 | ⬜ |

</details>

<details>
<summary><b>CORE-016</b> — <code>lib/logging-integrity.php</code> (238 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-016.01` | 1–3 | ناحیه | `بلوک رویه‌ای 1-3` | 0 | ⬜ |
| `CORE-016.02` | 4–31 | تابع | `logging_integrity_manifest_hmac` | 0 | ⬜ |
| `CORE-016.03` | 33–55 | تابع | `logging_integrity_verify_archive_file` | 1 | ⬜ |
| `CORE-016.04` | 57–64 | تابع | `logging_integrity_has_older_snapshot` | 0 | ⬜ |
| `CORE-016.05` | 66–99 | تابع | `logging_integrity_verify_predecessor_chain` | 1 | ⬜ |
| `CORE-016.06` | 101–114 | تابع | `logging_integrity_verify_daily_manifest_error` | 0 | ⬜ |
| `CORE-016.07` | 116–119 | تابع | `logging_integrity_verify_daily_manifest` | 0 | ⬜ |
| `CORE-016.08` | 121–159 | تابع | `logging_integrity_read_archive_index` | 1 | ⬜ |
| `CORE-016.09` | 161–178 | تابع | `logging_integrity_merge_archive_indexes` | 0 | ⬜ |
| `CORE-016.10` | 180–198 | تابع | `logging_integrity_row_is_late` | 0 | ⬜ |
| `CORE-016.11` | 200–237 | تابع | `logging_integrity_verify_supplement_chain` | 2 | ⬜ |

</details>

<details>
<summary><b>CORE-017</b> — <code>lib/logging-replay.php</code> (261 خط، 3 آدرس، بازبینی‌شده 0/3)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-017.01` | 1–5 | ناحیه | `بلوک رویه‌ای 1-5` | 0 | ⬜ |
| `CORE-017.02` | 6–43 | تابع | `logging_replay_poison_attempts` | 7 | ⬜ |
| `CORE-017.03` | 45–260 | تابع | `logging_replay_process_directory` | **9** | ⬜ |

</details>

<details>
<summary><b>CORE-018</b> — <code>lib/notify-users.php</code> (525 خط، 20 آدرس، بازبینی‌شده 0/20)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-018.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-018.02` | 14–34 | تابع | `notify_user` | 1 | ⬜ |
| `CORE-018.03` | 36–49 | تابع | `notification_target_url` | 0 | ⬜ |
| `CORE-018.04` | 51–74 | تابع | `ticket_notification_user_can` | 0 | ⬜ |
| `CORE-018.05` | 76–96 | تابع | `ticket_notification_recipients` | 5 | ⬜ |
| `CORE-018.06` | 98–103 | تابع | `ticket_unassigned_notify_candidates` | 0 | ⬜ |
| `CORE-018.07` | 105–125 | تابع | `notify_ticket_parties` | 0 | ⬜ |
| `CORE-018.08` | 127–146 | تابع | `notify_ticket_staff` | 0 | ⬜ |
| `CORE-018.09` | 148–173 | تابع | `notification_user_sees_food_cancel` | 0 | ⬜ |
| `CORE-018.10` | 175–182 | تابع | `notification_visibility_sql` | 0 | ⬜ |
| `CORE-018.11` | 184–192 | تابع | `notification_ticket_visibility` | 5 | ⬜ |
| `CORE-018.12` | 194–215 | تابع | `notification_header_summary` | 5 | ⬜ |
| `CORE-018.13` | 217–222 | تابع | `is_sla_holiday` | 0 | ⬜ |
| `CORE-018.14` | 224–252 | تابع | `ldap_authenticate` | 0 | ⬜ |
| `CORE-018.15` | 254–426 | تابع | `ldap_authenticate_measured` | 4 | ⬜ |
| `CORE-018.16` | 428–433 | تابع | `normalize_employee_number` | 0 | ⬜ |
| `CORE-018.17` | 435–435 | ناحیه | `شناسه کاربری که این کد پرسنلی را دارد (۰ یعنی آزاد` | 0 | ⬜ |
| `CORE-018.18` | 436–444 | تابع | `employee_number_owner` | 0 | ⬜ |
| `CORE-018.19` | 446–512 | تابع | `upsert_domain_user` | 1 | ⬜ |
| `CORE-018.20` | 514–523 | تابع | `link_agent_assets_to_user` | 1 | ⬜ |

</details>

<details>
<summary><b>CORE-019</b> — <code>lib/org-grouping.php</code> (298 خط، 17 آدرس، بازبینی‌شده 0/17)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-019.01` | 1–22 | ناحیه | `بلوک رویه‌ای 1-22` | 0 | ⬜ |
| `CORE-019.02` | 23–27 | تابع | `org_group_root_types` | 0 | ⬜ |
| `CORE-019.03` | 28–30 | ناحیه | `بلوک رویه‌ای 28-30` | 0 | ⬜ |
| `CORE-019.04` | 31–55 | تابع | `org_group_company_roots` | 0 | ⬜ |
| `CORE-019.05` | 56–58 | ناحیه | `بلوک رویه‌ای 56-58` | 0 | ⬜ |
| `CORE-019.06` | 59–94 | تابع | `org_group_unit_index` | 5 | ⬜ |
| `CORE-019.07` | 95–97 | ناحیه | `بلوک رویه‌ای 95-97` | 0 | ⬜ |
| `CORE-019.08` | 98–205 | تابع | `org_group_for_unit` | 0 | ⬜ |
| `CORE-019.09` | 206–208 | ناحیه | `بلوک رویه‌ای 206-208` | 0 | ⬜ |
| `CORE-019.10` | 209–221 | تابع | `org_group_chain_path` | 0 | ⬜ |
| `CORE-019.11` | 222–224 | ناحیه | `بلوک رویه‌ای 222-224` | 0 | ⬜ |
| `CORE-019.12` | 225–272 | تابع | `org_group_meta_for_users` | 5 | ⬜ |
| `CORE-019.13` | 273–275 | ناحیه | `بلوک رویه‌ای 273-275` | 0 | ⬜ |
| `CORE-019.14` | 276–281 | تابع | `org_group_sort_key` | 0 | ⬜ |
| `CORE-019.15` | 282–284 | ناحیه | `بلوک رویه‌ای 282-284` | 0 | ⬜ |
| `CORE-019.16` | 285–296 | تابع | `org_group_type_label` | 0 | ⬜ |
| `CORE-019.17` | 297–298 | ناحیه | `بلوک رویه‌ای 297-298` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-020</b> — <code>lib/release-notes.php</code> (370 خط، 10 آدرس، بازبینی‌شده 0/10)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-020.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 0 | ⬜ |
| `CORE-020.02` | 27–110 | تابع | `release_notes_all` | 0 | ⬜ |
| `CORE-020.03` | 112–116 | تابع | `release_notes_version_key` | 1 | ⬜ |
| `CORE-020.04` | 118–135 | تابع | `release_notes_current` | 0 | ⬜ |
| `CORE-020.05` | 137–161 | تابع | `release_notes_history` | 0 | ⬜ |
| `CORE-020.06` | 163–175 | تابع | `release_notes_render_items` | 0 | ⬜ |
| `CORE-020.07` | 177–265 | تابع | `release_notes_dashboard_card` | 4 | ⬜ |
| `CORE-020.08` | 267–320 | تابع | `release_notes_history_html` | 0 | ⬜ |
| `CORE-020.09` | 322–338 | تابع | `release_notes_page_html` | 0 | ⬜ |
| `CORE-020.10` | 340–369 | تابع | `release_notes_page_single` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-021</b> — <code>lib/render.php</code> (280 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-021.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-021.02` | 14–169 | تابع | `render_header` | 4 | ⬜ |
| `CORE-021.03` | 171–178 | تابع | `list_sort_page_off` | 2 | ⬜ |
| `CORE-021.04` | 180–201 | تابع | `inspection_food_reminder_markup` | 0 | ⬜ |
| `CORE-021.05` | 203–233 | تابع | `help_tabs_nav_html` | 0 | ⬜ |
| `CORE-021.06` | 235–279 | تابع | `render_footer` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-022</b> — <code>lib/settings-effects.php</code> (389 خط، 21 آدرس، بازبینی‌شده 0/21)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-022.01` | 1–13 | ناحیه | `بلوک رویه‌ای 1-13` | 0 | ⬜ |
| `CORE-022.02` | 14–36 | تابع | `setting` | 0 | ⬜ |
| `CORE-022.03` | 38–44 | تابع | `setting_forget_cache` | 0 | ⬜ |
| `CORE-022.04` | 46–55 | تابع | `save_setting` | 1 | ⬜ |
| `CORE-022.05` | 57–113 | تابع | `triangle_effect_page_catalog` | 0 | ⬜ |
| `CORE-022.06` | 115–124 | تابع | `triangle_effect_valid_keys` | 0 | ⬜ |
| `CORE-022.07` | 126–142 | تابع | `triangle_effect_config_spec` | 0 | ⬜ |
| `CORE-022.08` | 144–175 | تابع | `triangle_effect_sanitize_config` | 0 | ⬜ |
| `CORE-022.09` | 177–181 | تابع | `display_effect_type` | 0 | ⬜ |
| `CORE-022.10` | 183–196 | تابع | `particle_effect_config_spec` | 0 | ⬜ |
| `CORE-022.11` | 198–227 | تابع | `particle_effect_sanitize_config` | 0 | ⬜ |
| `CORE-022.12` | 229–234 | تابع | `particle_effect_config` | 0 | ⬜ |
| `CORE-022.13` | 236–244 | تابع | `particle_effect_enabled_for_current_page` | 0 | ⬜ |
| `CORE-022.14` | 246–251 | تابع | `triangle_effect_config` | 0 | ⬜ |
| `CORE-022.15` | 253–275 | تابع | `triangle_effect_enabled_pages` | 0 | ⬜ |
| `CORE-022.16` | 277–289 | تابع | `triangle_effect_current_page_key` | 2 | ⬜ |
| `CORE-022.17` | 291–321 | تابع | `triangle_effect_script_tag` | 0 | ⬜ |
| `CORE-022.18` | 323–353 | تابع | `particle_effect_script_tag` | 0 | ⬜ |
| `CORE-022.19` | 355–365 | تابع | `key_role_setting_map` | 0 | ⬜ |
| `CORE-022.20` | 367–382 | تابع | `key_role_user_ids` | 0 | ⬜ |
| `CORE-022.21` | 384–387 | تابع | `user_is_key_role` | 0 | ⬜ |

</details>

<details>
<summary><b>CORE-023</b> — <code>lib/system-alarms.php</code> (631 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-023.01` | 1–31 | ناحیه | `بلوک رویه‌ای 1-31` | 0 | ⬜ |
| `CORE-023.02` | 32–35 | تابع | `system_alarm_root` | 0 | ⬜ |
| `CORE-023.03` | 37–47 | تابع | `system_alarm_setting` | 0 | ⬜ |
| `CORE-023.04` | 49–55 | تابع | `system_alarm_roles` | 0 | ⬜ |
| `CORE-023.05` | 57–67 | تابع | `system_alarm_user_can_see` | 0 | ⬜ |
| `CORE-023.06` | 69–69 | تابع | `system_alarm_cache_path` | 0 | ⬜ |
| `CORE-023.07` | 70–70 | تابع | `system_alarm_state_path` | 0 | ⬜ |
| `CORE-023.08` | 71–71 | تابع | `system_alarm_ack_path` | 0 | ⬜ |
| `CORE-023.09` | 72–72 | تابع | `system_alarm_notify_path` | 0 | ⬜ |
| `CORE-023.10` | 74–89 | تابع | `system_alarm_read_json` | 1 | ⬜ |
| `CORE-023.11` | 91–108 | تابع | `system_alarm_write_json_atomic` | 3 | ⬜ |
| `CORE-023.12` | 110–113 | تابع | `system_alarm_item` | 0 | ⬜ |
| `CORE-023.13` | 115–123 | تابع | `system_alarm_self_failed` | 0 | ⬜ |
| `CORE-023.14` | 125–338 | تابع | `system_alarm_collect_cheap` | 3 | ⬜ |
| `CORE-023.15` | 340–395 | تابع | `system_alarm_probe_printer` | 1 | ⬜ |
| `CORE-023.16` | 397–438 | تابع | `system_alarm_collect` | 0 | ⬜ |
| `CORE-023.17` | 440–456 | تابع | `system_alarms_cached` | 0 | ⬜ |
| `CORE-023.18` | 458–473 | تابع | `system_alarms_visible` | 0 | ⬜ |
| `CORE-023.19` | 475–536 | تابع | `system_alarm_acknowledge` | 4 | ⬜ |
| `CORE-023.20` | 538–572 | تابع | `system_alarm_chrome` | 0 | ⬜ |
| `CORE-023.21` | 574–601 | تابع | `system_alarm_baseline_migrations` | 0 | ⬜ |
| `CORE-023.22` | 603–630 | تابع | `system_alarm_backfill_schema_updates` | 5 | ⬜ |

</details>

<details>
<summary><b>CORE-024</b> — <code>lib/tickets-sla.php</code> (891 خط، 36 آدرس، بازبینی‌شده 0/36)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CORE-024.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `CORE-024.02` | 20–24 | تابع | `ticket_number_cache_set` | 0 | ⬜ |
| `CORE-024.03` | 26–50 | تابع | `ticket_number` | 0 | ⬜ |
| `CORE-024.04` | 52–72 | تابع | `ticket_number_cache_prime` | 0 | ⬜ |
| `CORE-024.05` | 74–83 | تابع | `ticket_number_prefix` | 0 | ⬜ |
| `CORE-024.06` | 85–106 | تابع | `ticket_number_ensure_schema` | 0 | ⬜ |
| `CORE-024.07` | 108–205 | تابع | `ticket_hot_schema_ensure` | 4 | ⬜ |
| `CORE-024.08` | 207–261 | تابع | `ticket_number_ensure_schema_run` | **8** | ⬜ |
| `CORE-024.09` | 263–299 | تابع | `ticket_number_next` | 0 | ⬜ |
| `CORE-024.10` | 301–313 | تابع | `ticket_number_peek` | 0 | ⬜ |
| `CORE-024.11` | 315–340 | تابع | `ticket_number_sequence_floor` | 4 | ⬜ |
| `CORE-024.12` | 342–360 | تابع | `ticket_number_sequence_persist` | 5 | ⬜ |
| `CORE-024.13` | 362–456 | تابع | `ticket_number_autofill` | **10** | ⬜ |
| `CORE-024.14` | 458–462 | تابع | `is_it_agent` | 0 | ⬜ |
| `CORE-024.15` | 464–492 | تابع | `jalali_to_gregorian` | 0 | ⬜ |
| `CORE-024.16` | 494–520 | تابع | `gregorian_to_jalali` | 0 | ⬜ |
| `CORE-024.17` | 522–531 | تابع | `jalali_date` | 0 | ⬜ |
| `CORE-024.18` | 533–552 | تابع | `jalali_input_to_gregorian` | 0 | ⬜ |
| `CORE-024.19` | 554–569 | تابع | `find_or_create_department` | 1 | ⬜ |
| `CORE-024.20` | 571–574 | تابع | `persian_date` | 0 | ⬜ |
| `CORE-024.21` | 576–579 | تابع | `priority_sla_minutes` | 0 | ⬜ |
| `CORE-024.22` | 581–604 | تابع | `ensure_default_ola_policies` | 5 | ⬜ |
| `CORE-024.23` | 606–616 | تابع | `ola_policy_for` | 0 | ⬜ |
| `CORE-024.24` | 618–634 | تابع | `ola_deadlines` | 0 | ⬜ |
| `CORE-024.25` | 636–689 | تابع | `sla_business_hours` | 0 | ⬜ |
| `CORE-024.26` | 691–701 | تابع | `sla_business_hours_forget_cache` | 0 | ⬜ |
| `CORE-024.27` | 703–717 | تابع | `next_business_start` | 0 | ⬜ |
| `CORE-024.28` | 719–739 | تابع | `business_due_at` | 0 | ⬜ |
| `CORE-024.29` | 741–764 | تابع | `business_elapsed_minutes` | 0 | ⬜ |
| `CORE-024.30` | 766–782 | تابع | `ticket_sla_cycle_start` | 0 | ⬜ |
| `CORE-024.31` | 784–816 | تابع | `ticket_sla_transition` | 5 | ⬜ |
| `CORE-024.32` | 818–865 | تابع | `ticket_ola_transition` | 0 | ⬜ |
| `CORE-024.33` | 867–874 | تابع | `record_asset_history` | 1 | ⬜ |
| `CORE-024.34` | 876–879 | تابع | `status_label` | 0 | ⬜ |
| `CORE-024.35` | 881–884 | تابع | `priority_label` | 0 | ⬜ |
| `CORE-024.36` | 886–889 | تابع | `ticket_type_label` | 0 | ⬜ |

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
<summary><b>ACT-002</b> — <code>actions/account-actions.php</code> (144 خط، 5 آدرس، بازبینی‌شده 0/5)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-002.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `ACT-002.02` | 17–23 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-002.03` | 24–113 | ناحیه | `مسیر: login` | 3 | ⬜ |
| `ACT-002.04` | 114–121 | ناحیه | `مسیر: save_theme` | 0 | ⬜ |
| `ACT-002.05` | 122–144 | ناحیه | `مسیر: save_profile` | 1 | ⬜ |

</details>

<details>
<summary><b>ACT-003</b> — <code>actions/asset-actions.php</code> (588 خط، 14 آدرس، بازبینی‌شده 0/14)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-003.01` | 1–28 | ناحیه | `بلوک رویه‌ای 1-28` | 0 | ⬜ |
| `ACT-003.02` | 29–35 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-003.03` | 36–66 | ناحیه | `مسیر: collect_inventory` | 1 | ⬜ |
| `ACT-003.04` | 67–132 | ناحیه | `مسیر: asset_profile_selftest` | 4 | ⬜ |
| `ACT-003.05` | 133–140 | ناحیه | `مسیر: import_domain_assets` | 0 | ⬜ |
| `ACT-003.06` | 141–157 | ناحیه | `مسیر: domain_scan_start` | 2 | ⬜ |
| `ACT-003.07` | 158–199 | ناحیه | `مسیر: domain_scan_batch` | 2 | ⬜ |
| `ACT-003.08` | 200–276 | ناحیه | `مسیر: ping_assets` | **8** | ⬜ |
| `ACT-003.09` | 277–294 | ناحیه | `مسیر: domain_scan_retry` | 2 | ⬜ |
| `ACT-003.10` | 295–370 | ناحیه | `مسیر: domain_scan_prepare` | 4 | ⬜ |
| `ACT-003.11` | 371–435 | ناحیه | `مسیر: domain_scan_prepare_gpo\|domain_scan_prepare` | **9** | ⬜ |
| `ACT-003.12` | 436–493 | ناحیه | `مسیر: domain_scan_test_selected` | **8** | ⬜ |
| `ACT-003.13` | 494–511 | ناحیه | `مسیر: domain_scan_selected` | 2 | ⬜ |
| `ACT-003.14` | 512–588 | ناحیه | `مسیر: save_inventory_form` | 7 | ⬜ |

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
<summary><b>ACT-008</b> — <code>actions/food-order-actions.php</code> (31 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-008.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `ACT-008.02` | 17–31 | ناحیه | `مسیر: food_order_cancel_decide` | 2 | ⬜ |

</details>

<details>
<summary><b>ACT-009</b> — <code>actions/holiday-actions.php</code> (194 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-009.01` | 1–19 | ناحیه | `بلوک رویه‌ای 1-19` | 0 | ⬜ |
| `ACT-009.02` | 20–25 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-009.03` | 26–26 | ناحیه | `مقصد بازگشت پس از هر اکشن: صفحهٔ مستقل تقویم، یا ت` | 0 | ⬜ |
| `ACT-009.04` | 27–34 | ناحیه | `وقتی فرم از همان تب ارسال شده باشد. هر مقدار دیگری` | 2 | ⬜ |
| `ACT-009.05` | 35–75 | ناحیه | `مسیر: toggle_holiday` | 0 | ⬜ |
| `ACT-009.06` | 76–101 | ناحیه | `مسیر: add_holiday` | 0 | ⬜ |
| `ACT-009.07` | 102–131 | ناحیه | `مسیر: import_holidays` | 0 | ⬜ |
| `ACT-009.08` | 132–173 | ناحیه | `مسیر: save_work_hours` | 2 | ⬜ |
| `ACT-009.09` | 174–194 | ناحیه | `مسیر: delete_holiday` | 2 | ⬜ |

</details>

<details>
<summary><b>ACT-010</b> — <code>actions/notification-actions.php</code> (35 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-010.01` | 1–15 | ناحیه | `بلوک رویه‌ای 1-15` | 0 | ⬜ |
| `ACT-010.02` | 16–22 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-010.03` | 23–28 | ناحیه | `مسیر: mark_all_notifications` | 1 | ⬜ |
| `ACT-010.04` | 29–35 | ناحیه | `مسیر: mark_notification` | 3 | ⬜ |

</details>

<details>
<summary><b>ACT-011</b> — <code>actions/org-actions.php</code> (843 خط، 22 آدرس، بازبینی‌شده 0/22)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-011.01` | 1–16 | ناحیه | `بلوک رویه‌ای 1-16` | 0 | ⬜ |
| `ACT-011.02` | 17–22 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-011.03` | 23–24 | ناحیه | `درخواست‌های UI چارت برای جلوگیری از ثبت دوباره در ` | 0 | ⬜ |
| `ACT-011.04` | 25–46 | ناحیه | `شرط مسیریابی` | 2 | ⬜ |
| `ACT-011.05` | 47–68 | ناحیه | `شرط مسیریابی` | 7 | ⬜ |
| `ACT-011.06` | 69–205 | ناحیه | `مسیر: save_org_node` | 4 | ⬜ |
| `ACT-011.07` | 206–223 | ناحیه | `مسیر: undo_org_action` | 0 | ⬜ |
| `ACT-011.08` | 224–242 | ناحیه | `مسیر: redo_org_action` | 0 | ⬜ |
| `ACT-011.09` | 243–272 | ناحیه | `مسیر: sort_org_root_units` | 2 | ⬜ |
| `ACT-011.10` | 273–309 | ناحیه | `مسیر: sort_org_deputies` | 2 | ⬜ |
| `ACT-011.11` | 310–354 | ناحیه | `مسیر: assign_org_manager` | 0 | ⬜ |
| `ACT-011.12` | 355–381 | ناحیه | `مسیر: remove_org_manager` | 0 | ⬜ |
| `ACT-011.13` | 382–408 | ناحیه | `مسیر: remove_org_experts` | **8** | ⬜ |
| `ACT-011.14` | 409–445 | ناحیه | `مسیر: save_org_assign_users` | 7 | ⬜ |
| `ACT-011.15` | 446–564 | ناحیه | `مسیر: restore_org_backup` | 5 | ⬜ |
| `ACT-011.16` | 565–588 | ناحیه | `مسیر: reset_org_structure` | 5 | ⬜ |
| `ACT-011.17` | 589–616 | ناحیه | `مسیر: save_org_ceo` | 1 | ⬜ |
| `ACT-011.18` | 617–695 | ناحیه | `مسیر: save_org_unit` | **8** | ⬜ |
| `ACT-011.19` | 696–731 | ناحیه | `مسیر: delete_org_unit` | 5 | ⬜ |
| `ACT-011.20` | 732–765 | ناحیه | `مسیر: save_user_org` | 1 | ⬜ |
| `ACT-011.21` | 766–805 | ناحیه | `مسیر: repair_org_manager_admin_roles` | 7 | ⬜ |
| `ACT-011.22` | 806–843 | ناحیه | `مسیر: repair_org_manager_support_roles` | 7 | ⬜ |

</details>

<details>
<summary><b>ACT-012</b> — <code>actions/role-actions.php</code> (188 خط، 6 آدرس، بازبینی‌شده 0/6)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-012.01` | 1–18 | ناحیه | `بلوک رویه‌ای 1-18` | 0 | ⬜ |
| `ACT-012.02` | 19–25 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-012.03` | 26–125 | ناحیه | `مسیر: save_key_roles` | 7 | ⬜ |
| `ACT-012.04` | 126–149 | ناحیه | `مسیر: save_role_permissions` | 2 | ⬜ |
| `ACT-012.05` | 150–169 | ناحیه | `مسیر: reset_role_defaults` | 0 | ⬜ |
| `ACT-012.06` | 170–188 | ناحیه | `مسیر: reset_role_permissions` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-013</b> — <code>actions/service-actions.php</code> (6 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-013.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-014</b> — <code>actions/settings-actions.php</code> (370 خط، 9 آدرس، بازبینی‌شده 0/9)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-014.01` | 1–20 | ناحیه | `بلوک رویه‌ای 1-20` | 0 | ⬜ |
| `ACT-014.02` | 21–27 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-014.03` | 28–54 | ناحیه | `مسیر: save_org_company_names` | 2 | ⬜ |
| `ACT-014.04` | 55–68 | ناحیه | `مسیر: prune_activity_logs` | 0 | ⬜ |
| `ACT-014.05` | 69–76 | ناحیه | `مسیر: sync_ldap_users` | 0 | ⬜ |
| `ACT-014.06` | 77–90 | ناحیه | `مسیر: diagnose_ldap` | 0 | ⬜ |
| `ACT-014.07` | 91–164 | ناحیه | `مسیر: save_triangle_effect` | 2 | ⬜ |
| `ACT-014.08` | 165–318 | ناحیه | `مسیر: save_settings` | 5 | ⬜ |
| `ACT-014.09` | 319–370 | ناحیه | `مسیر: reset_assets` | **15** | ⬜ |

</details>

<details>
<summary><b>ACT-015</b> — <code>actions/supervisor-actions.php</code> (6 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-015.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-016</b> — <code>actions/ticket-actions.php</code> (6 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-016.01` | 1–6 | ناحیه | `بلوک رویه‌ای 1-6` | 0 | ⬜ |

</details>

<details>
<summary><b>ACT-017</b> — <code>actions/user-structure-actions.php</code> (373 خط، 8 آدرس، بازبینی‌شده 0/8)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `ACT-017.01` | 1–21 | ناحیه | `بلوک رویه‌ای 1-21` | 0 | ⬜ |
| `ACT-017.02` | 22–28 | ناحیه | `محافظ: این فایل فقط از طریق index.php بارگذاری می‌` | 0 | ⬜ |
| `ACT-017.03` | 29–39 | ناحیه | `مسیر: add_category` | 1 | ⬜ |
| `ACT-017.04` | 40–53 | ناحیه | `مسیر: add_department` | 1 | ⬜ |
| `ACT-017.05` | 54–159 | ناحیه | `مسیر: link_domain_food_by_code` | 7 | ⬜ |
| `ACT-017.06` | 160–318 | ناحیه | `مسیر: update_user_role` | 3 | ⬜ |
| `ACT-017.07` | 319–357 | ناحیه | `مسیر: save_department_members` | 7 | ⬜ |
| `ACT-017.08` | 358–373 | ناحیه | `مسیر: save_cd_dvd_settings` | 2 | ⬜ |

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
<summary><b>CRON-006</b> — <code>cron/food_ticket_worker.php</code> (325 خط، 11 آدرس، بازبینی‌شده 0/11)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-006.01` | 1–25 | ناحیه | `بلوک رویه‌ای 1-25` | 0 | ⬜ |
| `CRON-006.02` | 26–26 | ناحیه | `max-minutes=N : بعد از N دقیقه با کد ۰ خارج می‌شود` | 0 | ⬜ |
| `CRON-006.03` | 27–40 | ناحیه | `(چرخش فایل لاگ روزانه + جلوگیری از نشت حافظه). پیش` | 1 | ⬜ |
| `CRON-006.04` | 41–41 | ناحیه | `فقط یک worker زنده مجاز است (قفل تا پایان عمر پردا` | 0 | ⬜ |
| `CRON-006.05` | 42–53 | ناحیه | `کد خروج ۳ = نمونهٔ دیگری در حال اجراست؛ wrapper در` | 3 | ⬜ |
| `CRON-006.06` | 54–67 | ناحیه | `حذف لاگ‌های قدیمی‌تر از ۱۴ روز` | **9** | ⬜ |
| `CRON-006.07` | 68–68 | ناحیه | `پنجرهٔ کاری و کش تنظیمات` | 0 | ⬜ |
| `CRON-006.08` | 69–69 | ناحیه | `تنظیمات تا این تعداد ثانیه کش می‌شوند. قبلاً هر چر` | 0 | ⬜ |
| `CRON-006.09` | 70–70 | ناحیه | `خوانده می‌شد؛ وقتی چرخه هر نیم ثانیه تکرار شود این` | 0 | ⬜ |
| `CRON-006.10` | 71–74 | ناحیه | `اضافه می‌شود. ۱۰ ثانیه آن‌قدر کوتاه هست که تغییر ت` | 0 | ⬜ |
| `CRON-006.11` | 75–325 | ناحیه | `مقادیر اولیه تا بخش خواب حتی در مسیرهای خطا هم متغ` | 6 | ⬜ |

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
<summary><b>CRON-011</b> — <code>cron/sla_maintenance.php</code> (155 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-011.01` | 1–155 | ناحیه | `بلوک رویه‌ای 1-155` | 6 | ⬜ |

</details>

<details>
<summary><b>CRON-012</b> — <code>cron/system_alarm_tick.php</code> (113 خط، 4 آدرس، بازبینی‌شده 0/4)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-012.01` | 1–29 | ناحیه | `بلوک رویه‌ای 1-29` | 0 | ⬜ |
| `CRON-012.02` | 30–37 | ناحیه | `۱) probeهای سنگین (مثل تست اتصال پرینتر) — نتیجه د` | 0 | ⬜ |
| `CRON-012.03` | 38–46 | ناحیه | `۲) heartbeat برای پایش خودِ دیده‌بانی (فایل-بیس، ه` | 0 | ⬜ |
| `CRON-012.04` | 47–113 | ناحیه | `۳) جمع‌آوری آلارم‌ها (cheap + probe) و اعلان داخل ` | 0 | ⬜ |

</details>

<details>
<summary><b>CRON-013</b> — <code>cron/ticket_auto_close.php</code> (121 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-013.01` | 1–121 | ناحیه | `بلوک رویه‌ای 1-121` | 6 | ⬜ |

</details>

<details>
<summary><b>CRON-014</b> — <code>cron/verify_logging_integrity.php</code> (27 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-014.01` | 1–27 | ناحیه | `بلوک رویه‌ای 1-27` | 0 | ⬜ |

</details>

<details>
<summary><b>CRON-015</b> — <code>cron/verify_logging_release.php</code> (26 خط، 1 آدرس، بازبینی‌شده 0/1)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `CRON-015.01` | 1–26 | ناحیه | `بلوک رویه‌ای 1-26` | 4 | ⬜ |

</details>

### رابط برنامه (api/) — `API`


<details>
<summary><b>API-001</b> — <code>api/inventory.php</code> (225 خط، 7 آدرس، بازبینی‌شده 0/7)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `API-001.01` | 1–10 | ناحیه | `بلوک رویه‌ای 1-10` | 0 | ⬜ |
| `API-001.02` | 11–16 | تابع | `inventory_api_response` | 0 | ⬜ |
| `API-001.03` | 18–22 | تابع | `inventory_api_text` | 0 | ⬜ |
| `API-001.04` | 24–28 | تابع | `inventory_api_json` | 0 | ⬜ |
| `API-001.05` | 30–47 | تابع | `inventory_api_asset_tag` | 0 | ⬜ |
| `API-001.06` | 49–61 | تابع | `inventory_api_manual_fields` | 0 | ⬜ |
| `API-001.07` | 62–225 | ناحیه | `بلوک رویه‌ای 62-225` | 6 | ⬜ |

</details>

<details>
<summary><b>API-002</b> — <code>api/system-alarms.php</code> (70 خط، 2 آدرس، بازبینی‌شده 0/2)</summary>

| آدرس | خط | نوع | لنگر | ریسک | وضعیت |
|---|---|---|---|---|---|
| `API-002.01` | 1–56 | ناحیه | `بلوک رویه‌ای 1-56` | 0 | ⬜ |
| `API-002.02` | 57–70 | ناحیه | `CSRF برای درخواست JSON: هدر X-CSRF-Token باید با ت` | 0 | ⬜ |

</details>

