# نسخهٔ ۱.۶۳.۲ — نمایش سراسری اعلان تغییرات (r24)

بستهٔ تجمعی r24 بر پایهٔ r23 است و روی نسخه‌های ۱.۶۳، ۱.۶۳.۱ و نصب‌های ۱.۶۳.۲-r21/r22/r23 قابل نصب مجدد/ترمیم است. مهاجرت دیتابیس ندارد. پیش از نصب از فایل‌ها و پایگاه‌داده پشتیبان بگیرید.

## فایل r24

- [دانلود مستقیم ZIP از Release رسمی GitHub](https://github.com/alirezarahimy-code/ticket/releases/download/v1.63.2-r24/ticket-update-1.63-to-1.63.2-r24-global-notice-fix-20261106.zip)
- [صفحهٔ Release](https://github.com/alirezarahimy-code/ticket/releases/tag/v1.63.2-r24)
- [پیش‌نمایش صفحهٔ دانلود در مخزن](https://github.com/alirezarahimy-code/ticket/blob/arena/c417681f-ticket/downloads/index.html)
- [SHA-256 در مخزن GitHub](https://raw.githubusercontent.com/alirezarahimy-code/ticket/arena/c417681f-ticket/downloads/ticket-update-1.63-to-1.63.2-r24-global-notice-fix-20261106.zip.sha256)
- [گزارش تغییرات و آزمون‌ها](https://raw.githubusercontent.com/alirezarahimy-code/ticket/arena/c417681f-ticket/downloads/CHANGE-REPORT-FA-1.63.2-r24-global-notice-fix.md)

- حجم: **1,068,781 بایت**
- فایل‌های نصب: **۱۰۳**
- SHA-256: `ca6816b3259a8f6cb308af2cc3b84cc72abc587e91f74395f0e0707bc6e09275`

## اصلاح تأییدشده

- در کد r23 بنر تغییرات و هشدار نسخه فقط در محتوای `settingsForm()` قرار داشت؛ داشبورد و سایر نماها از آن استفاده نمی‌کردند. r24 بنرها را در `panelViewHtml()` مشترک می‌سازد و `render()` را برای همهٔ نماها از آن عبور می‌دهد. بنر از داخل محتوای تنظیمات برداشته شده تا دوباره تکرار نشود.
- شناسهٔ اعلان r24 مستقل (`1.63.2-r24`) است؛ دیده/بسته‌شدن اعلان‌های قبلی آن را پنهان نمی‌کند.
- نسخهٔ پنل رابط و PHP به **1.37.57** افزایش یافته است.
- اصلاح چاپ گزارش غایبین r23 حفظ شده است.

**محدودیت مهم:** این اصلاح نمایش اعلان را رفع می‌کند؛ علت ماندن پیام «در حال آماده‌سازی محیط مدیریت...» را ثابت یا رفع نمی‌کند. PHP CLI در محیط ساخت نصب نیست و اجرای end-to-end روی PHP/DB مقصد یا بررسی Console/Network واقعی انجام نشده است.

## آزمون‌ها

- `node tests/test_selftest_panel_render.js`: **۵۷ PASS، ۰ FAIL**. شامل رندر بنر قبل از محتوای داشبورد، مسیر render سراسری، جلوگیری از تکرار در تنظیمات، شناسهٔ اعلان و رفتار dismissal. این آزمون توابع JS را با VM اجرا می‌کند و مرورگر واقعی نیست.
- `node tests/js/food-absent-report.test.js`: PASS.
- تابع `test_standalone_absent_report_panel_and_chatbot_contract` از `tests/test_food_absent_report_static.py` با `runpy`: PASS.
- `node --check` روی سه اسکریپت inline پنل: PASS (بررسی syntax، نه رفتار مرورگر).
- سازندهٔ بسته `testzip()`، تعداد و فهرست manifest و SHA-256 همهٔ اعضا را بررسی کرد؛ تست‌های ساختار تاریخی r23 و r24 اجرا شدند.
- pytest/PHP CLI، اجرای PHP/DB مقصد، Console/Network مرورگر و دستگاه چاپ زنده در این محیط در دسترس/آزموده نبودند.

Release رسمی r24 با برچسب `v1.63.2-r24` به‌صورت مستقل از Release قدیمی `v1.63.2` ارائه می‌شود؛ نسخهٔ قدیمی برای سابقه همچنان r21 را نگه می‌دارد. درستی asset و لینک مستقیم پس از اجرای workflow بررسی می‌شود؛ این بررسی جای آزمون روی سرور نصب‌شده را نمی‌گیرد.
