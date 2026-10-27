# گزارش رسمی محتویات نسخهٔ ۱.۶۲.۳۶

**وضعیت این گزارش:** snapshot کد و بستهٔ ترمیم ۱.۶۲.۳۶ در مخزن آماده شده است. ساخت و انتشار در مخزن به معنی نصب روی سرور زنده نیست؛ نصب سرور باید جداگانه از updater انجام و سپس بررسی شود.

## مبنای بازسازی

نسخه از فایل‌های بستهٔ تجمعی ۱.۶۲.۲۴ و لایه‌های بعدی ۱.۶۲.۲۵، ۱.۶۲.۲۶، ۱.۶۲.۲۷، ۱.۶۲.۲۸، ۱.۶۲.۳۵ و ۱.۶۲.۳۶ بازسازی شده است. فایل‌های بستهٔ ترمیم قبلی که از snapshot ناقص `62dc749` آمده بودند، مبنای این نسخه نیستند.

- `VERSION`: برابر `1.62.36`
- payload بسته: ۷۱ مسیر فایل
- migrationهای updater: پنج مورد، از ۰۰۹ تا ۰۱۳
- نسخهٔ بسته برای نصب مجدد: فقط روی سامانه‌ای که نسخهٔ جاری آن ۱.۶۲.۳۶ است.
- بسته پیش از نصب از فایل‌ها و پایگاه‌داده backup می‌گیرد؛ migrationهای ثبت‌نشده را اجرا می‌کند.
- فایل‌های فهرست‌شده کامل جایگزین می‌شوند و ممکن است سفارشی‌سازی همان فایل‌ها را بازنویسی کنند.

## فهرست قابلیت‌ها و گردش‌های کاری

### پنل غذا و سفارش

1. داشبورد Capitalio و فایل‌های CSS/JavaScript نمودار و آمار آن.
2. تم شب/روز و اتصال تم سراسری پنل غذا.
3. عملیات گروهی پرسنل/سفارش، همراه با انتخاب، بررسی وضعیت و نمایش نتیجه.
4. گزارش سفارش به تفکیک گروه، تاریخ و وعده.
5. انتخاب و نگهداری فیلدهای گزارش سفارش روزانه.
6. جست‌وجوی پرسنل، انتخاب افراد و گزارش/خروجی سفارش در بازهٔ انتخابی.
7. گزارش فیش چاپ‌شده/تحویل‌شدهٔ کارکنان و مهمان، همراه با نگاشت منو از برنامهٔ همان روز.
8. مهلت اضطراری سفارش یا لغو برای روز و وعدهٔ مشخص، با مشاهده، اعطا و لغو.
9. ثبت دستی حضور و فیش برای کارمند/مهمان و یک یا چند نفر، با کنترل دسترسی و ثبت علت.
10. همسان‌سازی وضعیت فیش، تحویل و غیبت‌های متناقض؛ حفظ وضعیت صف چاپ و اصلاح رکوردهای قبلی.
11. فایل‌های تقویم، مرتب‌سازی، ذخیره‌سازی پنل، هویت بصری و پنل گروه‌ها.

### سامانه، سازمان و دارایی‌ها

12. فرم و فهرست شناسنامهٔ فنی دارایی، توابع کمکی، وضعیت گردآوری اطلاعات و سبک‌های فرم.
13. خروجی و نمایش خوانای گزارش سلامت/لاگ سامانه.
14. یادآور بازرسی غذا برای نقش‌های مربوط.
15. اولویت مدیر سازمانی در تخصیص مسئول درخواست و ابزارهای اصلاح نقش مدیران.
16. پشتیبانی واحدهای مستقل نمایندگی در نمودار سازمانی.
17. اتصال حساب دامین به کد پرسنلی غذا و امکان آزمون انتخابی اسکن دامنه.
18. فایل‌های گردش ترافیک، اطلاع‌رسانی، مجوزها، ثبت رویداد، حریم دسترسی و SLA.
19. ماژول‌های اکشن برای حساب، دارایی، پشتیبان‌گیری، عملیات گروهی، CD/DVD، تعطیلات، اعلان، سازمان، نقش، خدمات، تنظیمات، سوپروایزر، درخواست و ساختار کاربران.

### جزئیات migrationها

- `009-traffic-destination-multi-approver.sql`: افزودن تأییدکنندهٔ دوم و سوم برای مقصد ترافیک.
- `010-org-liaison-units.sql`: افزودن جایگاه چپ/راست برای واحد نمایندگی مستقل در نمودار سازمانی.
- `011-manual-attendance-delivery.sql`: ثبت وضعیت تحویل برای فیش دستی چاپ‌شدهٔ قبلی.
- `012-manual-attendance-clears-absences.sql`: ایجاد جدول غیبت در صورت نبودن و پاک‌کردن غیبت متناقض با تردد دستی.
- `013-manual-attendance-record-backfill.sql`: تکمیل backfill فیش/تحویل و پاک‌سازی غیبت‌های متناقض، با حفظ وضعیت صف چاپ.

هر migration فقط در صورت ثبت‌نشدن برای نسخهٔ ۱.۶۲.۳۶ اجرا می‌شود. SQLهای مربوطه برای اجرای تکراری طراحی شده‌اند؛ بااین‌حال backup پیش از نصب الزامی است.

## فهرست دقیق ۷۱ مسیر داخل `files/`

```text
VERSION
actions/account-actions.php
actions/asset-actions.php
actions/backup-actions.php
actions/bulk-actions.php
actions/cd-dvd-actions.php
actions/food-brand-actions.php
actions/holiday-actions.php
actions/notification-actions.php
actions/org-actions.php
actions/role-actions.php
actions/service-actions.php
actions/settings-actions.php
actions/supervisor-actions.php
actions/ticket-actions.php
actions/user-structure-actions.php
app/Views/layout/footer.php
app/Views/layout/header.php
asset-inventory-page.php
asset-profile.php
assets/app.js
assets/food-menu.css
assets/food-menu.js
assets/organization.js
assets/style.css
assets/theme-mode.css
assets/traffic-control.js
bootstrap.php
chatbot.php
food-order.php
food-ticket-groups.php
food-ticket-netprint.php
food-ticket-templates.php
food-ticket-web-host.php
food-ticket-web/assets/food-brand.js
food-ticket-web/assets/food-capitalio-dashboard-r42.css
food-ticket-web/assets/food-capitalio-dashboard-r42.js
food-ticket-web/assets/food-designer.js
food-ticket-web/assets/food-groups.js
food-ticket-web/assets/food-manual-attendance.js
food-ticket-web/assets/food-menu.css
food-ticket-web/assets/food-menu.js
food-ticket-web/assets/food-order-calendar.js
food-ticket-web/assets/food-order.css
food-ticket-web/assets/food-ticket-global-theme-r42.css
food-ticket-web/assets/food-ticket-persistence.js
food-ticket-web/assets/food-ticket-theme.css
food-ticket-web/assets/jalali-calendar.js
food-ticket-web/assets/list-sort.js
food-ticket-web/index.html
food-ticket.php
governance.php
help-guide.php
index.php
install-empty-db.sql
lib/activity-audit.php
lib/auth-scope.php
lib/db-core.php
lib/ldap.php
lib/logging-csrf.php
lib/notify-users.php
lib/settings-effects.php
lib/tickets-sla.php
migrations/009-traffic-destination-multi-approver.sql
migrations/010-org-liaison-units.sql
ola.php
organization.php
permissions.php
schema.sql
system-health.php
traffic-control.php
```

## بسته و checksum

- بستهٔ ترمیم: [`downloads/ticket-repair-1.62.36-full-source-restore-20261027.zip`](../downloads/ticket-repair-1.62.36-full-source-restore-20261027.zip)
- SHA-256: `a59fb34071a9ea60ee92bca255bcbecdcf8060dc6543c66e6a632dba2d78b429`
- بستهٔ افزایشی اصلی ۱.۶۲.۳۶ همچنان جداست و پیش‌نیاز آن ۱.۶۲.۳۵ است؛ آن را با بستهٔ ترمیم کامل اشتباه نگیرید.

## راستی‌آزمایی انجام‌شده

- مقایسهٔ بایت‌به‌بایت payload با بازسازی لایه‌ای آرشیوهای مرجع.
- بررسی ساختار ZIP و SHA-256.
- آزمون‌های متمرکز بازسازی/بسته و سازگاری snapshot، به‌همراه آزمون‌های ماژول‌های مرتبط با نسخه: **۱۳ قبول**.
- بررسی نحوی ۱۴ فایل JavaScript payload با Node.js: **موفق**.
- اجرای کل suite در مخزن: **۸۴ قبول، ۲۰ ناموفق و ۴ subtest موفق**. شکست‌های باقیمانده در چند بررسی ایستای قدیمی سفارش غذا، گزارش تحویل، افکت‌ها و سلامت سامانه‌اند؛ بعضی هنوز ساختار تک‌فایلی پیش از بازسازی ۱.۶۲.۲۰ یا نسخه/تعریف‌های قدیمی را فرض می‌کنند، اما همهٔ آن‌ها جداگانه ممیزی و رفع نشده‌اند و نباید نادیده گرفته شوند.
- PHP CLI در محیط حاضر نبود، بنابراین `php -l` اجرا نشد.
