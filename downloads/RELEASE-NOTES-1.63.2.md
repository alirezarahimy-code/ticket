# نسخهٔ ۱.۶۳.۲ — رفع اختلال پنل و اعلان تغییرات (r23)

بستهٔ تجمعی r23 روی نسخه‌های ۱.۶۳، ۱.۶۳.۱ و ۱.۶۳.۲-r22 نصب مجدد/ترمیم می‌شود. مهاجرت دیتابیس ندارد. پیش از نصب از فایل‌ها و پایگاه‌داده پشتیبان بگیرید.

## فایل‌های r23

- [دانلود بستهٔ r23 از پیش‌نمایش (ZIP)](ticket-update-1.63-to-1.63.2-r23-panel-render-notice-fix-20261106.zip)
- [دانلود مستقیم از Release رسمی GitHub (ZIP)](https://github.com/alirezarahimy-code/ticket/releases/download/v1.63.2-r23/ticket-update-1.63-to-1.63.2-r23-panel-render-notice-fix-20261106.zip)
- [SHA-256 در مخزن GitHub](https://raw.githubusercontent.com/alirezarahimy-code/ticket/arena/c417681f-ticket/downloads/ticket-update-1.63-to-1.63.2-r23-panel-render-notice-fix-20261106.zip.sha256)
- [گزارش ممیزی و آزمون‌ها در مخزن GitHub](https://raw.githubusercontent.com/alirezarahimy-code/ticket/arena/c417681f-ticket/downloads/CHANGE-REPORT-FA-1.63.2-r23-panel-render-notice-fix.md)

- حجم: **1,067,898 بایت**
- فایل‌های نصب: **۱۰۳**
- SHA-256: `152105045f89adea9bb6233e5f5fa4351f893ca533713f0d772707480f304984`

## چه چیزی را اصلاح می‌کند؟

- HTML چاپ گزارش دیگر تگ script توکار ندارد؛ ادامهٔ کد پنل نباید پس از پیام «در حال آماده‌سازی» به‌صورت متن در صفحه ظاهر شود.
- اعلان r23 شناسهٔ مستقلی از ۱.۶۳.۲ قبلی دارد؛ دیده/بسته‌شدن اعلان‌های r21/r22 آن را پنهان نمی‌کند.
- شمارندهٔ نمایش برای هر بازدید صفحه یک‌بار ثبت می‌شود، نه با هر بار رندر رابط.
- نسخهٔ پنل به ۱.۳۷.۵۶ افزایش یافته تا کش قدیمی تشخیص داده شود.

`tests/test_selftest_panel_render.js` **۵۴ بررسی موفق**؛ آزمون ایستای مسیر GET `/api/config` نیز تأیید می‌کند که یادداشت جاری از `food_ticket_api_config()` به پاسخ API می‌رسد. آزمون چاپ/مرز اسکریپت، lint PHP-WASM و آزمون میزبان پنل نیز موفق‌اند. آزمون زندهٔ پایگاه داده و بازبینی Excel در Office انجام نشده‌اند.

Release رسمی r23 با برچسب `v1.63.2-r23` منتشر شد. Release قدیمی `v1.63.2` همچنان asset r21 را برای سابقه نگه می‌دارد. این نتیجه آزمون زندهٔ سرور مقصد نیست؛ نصب و پاسخ واقعی API آن سرور در این محیط بررسی نشده است.
