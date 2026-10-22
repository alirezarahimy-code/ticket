# گزارش مدیریتی آزمون گستردهٔ سامانه

**وضعیت جاری پس از اصلاحات (۱۴۰۵/۰۷/۳۰):** CI شمارهٔ [39866986761](https://github.com/alirezarahimy-code/ticket/actions/runs/39866986761) پس از اصلاحات اجرا شد. Python (۱۳۸ تست + ۱۸ subtest)، هر پنج اسکریپت Node، smoke مسیرها/API (۷۴/۷۴) و workload نوشتنی مصنوعی موفق شدند؛ اما **دروازهٔ CI قرمز است** چون تست‌های آفلاین PHP و ۷ assertion در `tests/smoke.php` شکست دارند. بنابراین کل سوئیت قبول نیست. هیچ production/staging هدف قرار نگرفت و هنوز پوشش end-to-end همهٔ نقش‌ها، عملیات، workerها و وابستگی‌های بیرونی وجود ندارد.

نتایج اجرای پیشین `39862237034` نیز برای ردیابی تاریخی پایین‌تر حفظ شده‌اند؛ آن اجرا پیش از اصلاحات بود.

- شاخه: `arena/8edbdf95-ticket`
- commit کد آزمون: `5a4910f`؛ نتایج CI ثبت‌شده در `03fe741`
- اجرای GitHub Actions: [39862237034](https://github.com/alirezarahimy-code/ticket/actions/runs/39862237034)
- محیط اجرا: runner موقت، PHP 8.2 و MariaDB موقت `ticket_benchmark`؛ فقط حساب‌ها و داده‌های مصنوعی.
- **هیچ مقصد production یا staging، حساب عملیاتی یا دادهٔ واقعی استفاده نشد.** دیتابیس CI پس از اجرا از بین می‌رود.

## خلاصهٔ اجرای پیشین — CI شمارهٔ 39862237034

| بخش | نتیجه | شواهد و دامنه |
|---|---|---|
| صفحه‌های برنامه | قبولِ مسیرهای منتخب | ۳۷ مسیر صفحه با نشست ادمین مصنوعی؛ شامل ورود، داشبورد، درخواست/صف، گزارش و تحلیل، supervisor، تنظیمات، کاربران محلی، دارایی و Inventory، غذا، CD/DVD، تردد، سازمان، حاکمیت، خدمات، دانش‌نامه، اعلان‌ها، پشتیبان‌گیری، سلامت و لاگ‌ها |
| APIهای خواندنی | قبولِ مسیرهای منتخب | ۲۶ GET در APIهای غذا، سفارش غذا، منو و تردد؛ payloadهای JSON و HTTP بررسی شدند |
| مجوزها | قبولِ مرزهای آزمایش‌شده | ۳ رد دسترسی مورد انتظار برای کاربر عادی مصنوعی: تنظیمات، مدیریت فیش غذا و درخواست غذای مهمان بدون انتساب معاونت؛ تنها دو نقش ادمین اصلی و کاربر عادی runtime-smoke شدند |
| نوشتن غذا | قبول | ساخت و لغو سفارش مصنوعی، سپس خواندن مجدد رکورد و تأیید `cancelled`؛ ساخت گروه غذای مصنوعی، خواندن وضعیت غیبت و حذف گروه از طریق handler واقعی |
| آزمون نوشتنی هم‌زمان تیکت/CD/DVD | قبول در دامنهٔ آزموده‌شده | ۲۵۰ نشست هم‌زمان و ۳۲ worker؛ ۲۵۰ تیکت ساخته و حل شد، شماره‌ها یکتا؛ ۳۱۰ رکورد رویداد CD/DVD و رقابت‌های شمارهٔ تکراری بدون شکست invariant |
| PHP | قبول | lint فایل‌های PHP رهگیری‌شده، تست‌های PHP مستقل/آفلاین و `tests/smoke.php` روی دیتابیس موقت موفق شدند |
| JavaScript / Node | قبول | تست‌های Node در `tests/` و `tools/` با jsdom اجرا شدند و step موفق بود |
| Python ایستا | **ناموفق** | اجرای اصلی در مرحلهٔ collection متوقف می‌شود (۲ خطای import-time). اجرای تشخیصی با `--continue-on-collection-errors`: ۱۳۱ passed، ۸ failed، ۲ errors و ۱۷ subtest موفق |
| وابستگی‌های بیرونی/workerها | اجرا‌نشده | شرح محدودیت‌ها در پایین؛ نتیجهٔ مثبت برای این اجزا ثبت نشده است |

## جزئیات اجرایی و سلامت

### مسیرهای برنامه و API

- route smoke: **۷۴/۷۴ بررسی قبول**؛ ۳۷ صفحه، ۲۶ API خواندنی و بررسی مجوزهای کاربر عادی.
- سه پاسخ HTTP خطای ۴۰۳، رد دسترسیِ از پیش انتظاررفته بودند؛ **خطای HTTP غیرمنتظره: صفر**.
- یک JSON خطا مربوط به رد دسترسیِ درخواست غذای مهمان برای حساب بدون معاونت بود؛ این رفتار مورد انتظار بود.
- در لاگ سرور route smoke: **۰ خطای PHP و ۰ خطای SQL/PDO**.
- latency این smoke ترتیبی: میانه **۱۹٫۴۳ ms**، بیشینه **۱۲۹٫۵۳ ms**؛ این اعداد معیار ظرفیت یا آزمون بار نیستند.

### سفارش غذا و گروه غذا

- سفارش روی روز/آیتم تقویم مصنوعی از طریق API واقعی ایجاد شد، لغو شد و `my-orders` وضعیت نهایی `cancelled` را برگرداند.
- گروه غذای مصنوعی با L_UID یکتا ساخته شد؛ API وضعیت غیبت همان گروه را برگرداند؛ سپس گروه از handler حذف شد.
- این مسیرها فقط یک چرخهٔ پایه را پوشش می‌دهند؛ ثبت/ویرایش برنامهٔ منو، درخواست مهمان، bulk/emergency و تمام مسیرهای مدیریتی runtime به‌طور کامل پوشش داده نشده‌اند.

### بار نوشتنی تیکت و CD/DVD

- زمان: **۳۴٫۵۲ ثانیه**؛ ۲۵۰ نشست، ۳۲ worker؛ **۱٬۸۷۰ عملیات منطقی**، حدود **۵۴٫۱۷ عملیات منطقی در ثانیه**.
- ۲۵۰/۲۵۰ تیکت ساخته و به وضعیت حل‌شده رسید؛ ۲۵۰ شمارهٔ یکتا؛ ۵۰۰ پیام و ۱٬۰۰۰ رویداد.
- ۳۱۰ رویداد CD/DVD ثبت شد؛ رقابت‌های شماره/وضعیت invariant نقض نکردند.
- خطاهای HTTP یا تأیید برنامه در workload: صفر؛ خطا/هشدار PHP در workload: صفر؛ هشدار کندی query/page: صفر؛ خطای غیرمنتظرهٔ ثبت‌شده در برنامه: صفر.
- این نتیجه **فقط** handlerهای تیکت و CD/DVD و عملیات غذا/گروه ذکرشده را شامل می‌شود؛ تعمیم آن به همهٔ write handlerها مجاز نیست.

## خطاهای تست ایستای Python در اجرای CI قبلی — سابقهٔ تشخیصی

اجرای تشخیصی قبلی با `--continue-on-collection-errors` این موارد را نشان داد. آن‌ها در تغییرات جاری با هم‌راستاسازی قرارداد تست‌ها و اصلاح اختلاف‌های واقعی بررسی شدند؛ وضعیت پس از اصلاحات در بخش «بررسی محلی پس از اصلاحات» آمده است.

1. `tests/test_org_chart_experts_static.py` — انتظار URL نسخهٔ قدیمی CSS (`3.4.42`) در حالی‌که index از `3.4.43` استفاده می‌کند.
2. `tests/test_ticket_cancellation_static.py` — تست fallback فقط bind قدیمیِ یک پارامتر را انتظار دارد؛ کد جاری سه پارامتر شامل محدودیت واحد/معاونت bind می‌کند.
3. `tests/test_food_orders_static.py::test_panel_assets_are_synchronized` — فایل CSS پنل سفارش غذا با فایل `assets/` همگام نیست؛ نیازمند تصمیم دربارهٔ منبع مرجع و بررسی UI است.
4. `test_proxy_calendar_reads_only_the_verified_recipient_and_marks_their_order` و `test_proxy_order_list_uses_only_the_verified_recipient_month` — تست‌ها امضای قدیمی `food_order_month_status` را می‌خواهند؛ کد جاری پارامترهای وعدهٔ غذا را هم می‌فرستد.
5. `test_proxy_cancel_is_server_scoped_and_reverifies_the_recipient` — انتظار امضای قدیمی تابع قفل روز را دارد؛ کد جاری `mealType` اختیاری دارد.
6. `test_proxy_order_submission_targets_selected_employee_and_reports_result` — انتظار الگوی refresh قدیمی را دارد؛ کد جاری پس از سفارش، دادهٔ ماه و سفارش‌های من را بازخوانی می‌کند.
7. `test_table_definitions_match_across_upgrade_and_fresh_install` — مقایسهٔ baseline قدیمی migration 1.38 را با schema نهایی انجام می‌دهد؛ ستون `food_catalog.category` در migration مورخ 2026-10-13 افزوده شده است.
8. `tests/test_traffic_destination_approver_static.py::test_release_manifest_and_download_page_list_new_destination_migration` — assertion هنوز انتشار R9 را «بستهٔ جاری» می‌داند، اما صفحهٔ دانلود شاخه به R31 اشاره می‌کند؛ مستندات انتشار هم‌راستا نیستند.
9. `tests/test_triangle_effect_static.py::test_release_manifest_is_migration_free` — مقدار ثابت قدیمی `1.59.4` را انتظار دارد، درحالی‌که `VERSION` برابر `1.61.10` است.

دو مورد اول خطای collection هستند؛ باقی موارد assertionهای ناموفق‌اند. بعضی نشانهٔ تست‌های عقب‌مانده از تغییر نسخه/امضای API هستند و بعضی، از جمله اختلاف CSS و مستندات انتشار، ممکن است ناهماهنگی واقعی باشند. آن‌ها را خودکار به «false positive» تبدیل نکردم و اصلاح کد یا تست را بدون بررسی رفتار مورد انتظار انجام ندادم.

## راستی‌آزمایی پس از اصلاحات — محلی و CI، ۲۰۲۶-۱۰-۲۳

CI پس از push، روی runner موقت با PHP 8.2 و MariaDB دورریختنی اجرا شد؛ همهٔ داده‌ها و حساب‌ها مصنوعی بودند. خروجی‌های runtime/بار به‌روز در `benchmark-results/full-system-route-smoke.md` و `benchmark-results/benchmark-operations.md` هستند.

| لایه | نتیجهٔ پس از اصلاحات |
|---|---|
| Python | **۱۳۸ passed، ۱۸ subtests passed**؛ step در CI موفق شد. اجرای محلی نیز همین نتیجه را داد. |
| JavaScript runtime | هر پنج اسکریپت `tests/test_*_node.js` و `tools/test_*_node.js` موفق؛ شامل ۱۲ بررسی fresh-state، ۴۱ بررسی UI و ۴۶ بررسی پنل چاپگر/کارکنان. |
| HTTP/API | **۷۴/۷۴** مسیر/بررسی قبول؛ ۳۷ صفحه، ۲۶ API خواندنی و ۳ رد دسترسی موردانتظار. خطای HTTP غیرمنتظره صفر؛ لاگ route smoke: صفر PHP و صفر DB/SQL. |
| PHP offline و DB smoke | **ناموفق:** چند تست آفلاین PHP شکست خوردند و `tests/smoke.php` هنوز ۷ assertion ناموفق دارد؛ موارد شناخته‌شده در ادامه‌اند. بنابراین quality gate CI قرمز است. |
| workload نوشتنی | قبول در دامنهٔ محدود: ۲۵۰ نشست، ۳۲ worker، ۱٬۸۷۰ عملیات در ۳۵٫۹۴ ثانیه، حدود ۵۲٫۰۳ عملیات/ثانیه؛ صفر خطای HTTP/تأیید و صفر هشدار PHP در workload. |
| latency | در route smoke ترتیبی: میانهٔ ۲۲٫۳۳ ms، بیشینهٔ ۱۷۹٫۸۹ ms؛ معیار بار/ظرفیت نیست. در workload: زمان کل ۳۵٫۹۴ ثانیه؛ آمار workload در گزارش خودش است. |
| JavaScript syntax و patch | `node --check` برای assetها/اسکریپت‌های تغییرکرده و `git diff --check` موفق شدند. |

## شکست‌های باقی‌مانده در CI پس از اصلاحات

- `tests/smoke.php`: هفت بررسی ایستا هنوز شکست می‌خورند: «پنل کامل داخل مسیر اصلی»، «تعیین ادمین اصلی از تنظیمات»، «تب‌های تنظیمات اصلی با گروه‌بندی و چیدمان فرم‌ها»، «تشخیص مرحله‌ای ورود LDAP»، «شماره‌گذاری درخواست»، «تنظیم نام سامانه» و «پوشه ذخیره پیوست». بررسی CSRF/CSP پس از اصلاح مسیر منبع پاس شد؛ warning ناشی از درون‌یابی `$groupEventIds` نیز اصلاح شد.
- تست‌های آفلاین PHP ناموفق در run `39866986761`: `tools/test_fresh_db_offline.php` (۲ assertion)، `tools/test_food_personnel_link_offline.php` (تابع `xlsx_build` تعریف نشده در harness)، `tools/test_food_dupes_offline.php` (۱ assertion)، `tools/test_employees_import_offline.php` (ستون `must_change_password` در fixture نیست)، `tools/test_column_detect_offline.php` و `tests/test_food_ticket_logic.php` (تابع `app_error_log` در harness تعریف نشده)، `tools/test_brand_neutral_offline.php` (جست‌وجو وارد `node_modules` و متن تاریخی مستندات می‌شود)، `tools/test_api_contract_offline.php` (۳ assertion)، `tests/tts_normalize_test.php` (۱ مورد)، و `tests/SecurityTest.php` (انتظار پاک‌شدن ۱۳ جدول باقی‌مانده از rebuild).
- failureها می‌توانند از fixture/includeهای ناقص، assertionهای عقب‌مانده یا اختلاف واقعی در قرارداد محصول ناشی شوند؛ هنوز برای همه علت قطعی یا اصلاحی اعمال نشده است. CI quality gate در نتیجه شکست دارد؛ تست‌های موفق HTTP/workload این شکست‌ها را نمی‌پوشانند.

اصلاحات پوشش داده‌شده: assertionهای کهنهٔ cache-buster و API غذا با رفتار/قرارداد جاری هم‌راستا شدند؛ تست فهرست سفارش نیابتی اکنون قرارداد واقعیِ POST، تأیید کد ملی، محدودیت کارمند/بازه و payload UI را بررسی می‌کند؛ `install-empty-db.sql` هم‌راستا شد تا fresh install ستون `food_catalog.category` را داشته باشد؛ CSS پنل سفارش غذا با stylesheet مرجع همگام شد و cache-buster تغییر کرد؛ مستندات انتشار به R31 به‌روز شدند؛ assertionهای `tests/smoke.php` برای منبع واقعی CSRF/CSP و literal رویداد گروه اصلاح شدند. نتایج فوق قبولی کل آزمون‌های runtime یا همهٔ عملیات برنامه را ثابت نمی‌کنند.

## محدودیت‌ها و موارد اجرا‌نشده

- دسترسی واقعی LDAP/Active Directory، WMI/WinRM و اسکن شبکه اجرا نشد؛ هیچ میزبان دامنه‌ای هدف قرار نگرفت.
- منبع Microsoft Access/ODBC، چاپگر فیزیکی، چاپ واقعی، سخت‌افزار و مسیر واقعی جمع‌آوری Inventory استفاده نشد؛ هیچ print preview گزارش نشده است.
- provider بیرونی AI، ایمیل، پیامک و سایر سرویس‌های بیرونی فراخوانی نشدند؛ فقط صفحه‌ها/APIهای امن و وابستگی‌های خالی/ایزوله بررسی شدند.
- workerهای cron/queue از جمله SLA/OLA، همگام‌سازی LDAP و worker چاپ غذا اجرا نشدند؛ تست‌های ایستا یا بررسی وجود فایل جای اجرای واقعی را نمی‌گیرند.
- role matrix کامل نیست: runtime فقط ادمین اصلی و کاربر عادی را آزمود؛ supervisor، مدیر واحد، کارشناس، کاربر محلی و نگهبان در این اجراها وارد نشدند.
- APIهای نوشتنی همهٔ ماژول‌ها، import/exportها، backup/restore و تمام چرخه‌های Change/Problem/Traffic/Inventory به‌صورت end-to-end اجرا نشده‌اند.
- `traffic-control-print` عمداً برای پرهیز از ادعای اجرای print preview در smoke گنجانده نشد.

## جمع‌بندی مدیریتی و حدود استنباط

پس از اصلاحات، CI جدید مسیرهای نماینده و APIهای منتخب را ۷۴/۷۴ و workload نوشتنی محدود را قبول کرد؛ Python و JavaScript نیز پاس شدند. بااین‌حال PHP offline و `tests/smoke.php` شکست دارند، پس نتیجهٔ کل سوئیت **ناموفق/نیازمند پیگیری** است. این آزمون‌ها فقط حساب و دادهٔ مصنوعی در runner/MariaDB دورریختنی به کار بردند؛ production/staging، حساب یا دادهٔ عملیاتی هدف قرار نگرفت. این گزارش ادعای پوشش کامل همهٔ عملیات، نقش‌ها، workerها، سخت‌افزار یا سرویس‌های بیرونی ندارد.

گزارش فنی route smoke: `benchmark-results/full-system-route-smoke.md` و JSON متناظر. گزارش workload: `benchmark-results/benchmark-operations.md` و JSON متناظر. فایل‌های log و JUnit پایتون در artifact همان اجرای GitHub Actions نگهداری می‌شوند.
