# رجیستر شواهد فاز ۲ — استقلال ماژول مرکز درخواست‌ها

**نسخه:** ۱٫۰ | **تاریخ:** ۲۰۲۶-۱۱-۰۷ | **شاخه:** `arena/22d4eba7-ticket` | **کامیت پایه:** `d30d7d` (از branch main)؛ کامیت آخر کار فاز ۲: `d076963`

**دامنه:** این رجیستر مستندِ شواهد «فاز ۲ — استقلال ماژول» است. ادعای تکمیل انتقال، تأیید نصب تولید، انتشار یا تأیید نهایی کاربر (R6) را ندارد. `main` تغییر نکرده است.

---

## ۱. هدف فاز ۲

کاهش وابستگی فیزیکی و رفتاری ماژول به `index.php` با استخراج کد مرکز‌درخواست به مسیرهای مالکیت ماژول، **بدون شکستن رفتار مستندشده** و با حفظ URLها و پروسهٔ فرانت‌اند.

## ۲. برش‌های اجراشده (هر کدام با کامیت جدا؛ همه push شده‌اند)

| برش | موضوع | مقصد | کامیت |
|---|---|---|---|
| A | توابع دامنهٔ RC: `store_attachment`، `fetch_ticket`، `can_view_ticket`، `ticket_org_manager_rank_sql`، `choose_handling_agent`، `ticket_allowed_transitions`، `ticket_requester_cancelable_statuses`، `ticket_requester_can_cancel_directly`، `ticket_requester_can_cancel`، `notify_ticket_resolved`، `report_query_filters` | `modules/request-center/lib/functions.php` | (برش A) |
| B | chrome مشترک: `render_header`، `render_footer` + helperهای فهرست/یادآوری غذا | `lib/render.php` (مشترک، چون chrome سراسری است) | (برش B) |
| C | اندپوینت GET دانلود پیوست (`request_center_download_attachment`) با قاعدهٔ رابطه‌محور `ticket_can_see_internal` + اندپوینت خروجی Excel (`request_center_export_report`) | `modules/request-center/lib/endpoints.php` | (برش C) |
| D | پرس‌وجوهای دادهٔ داشبورد (`request_center_dashboard_rows`) | `modules/request-center/lib/endpoints.php` | (برش D) |
| E | slice جست‌وجوی سراسری درخواست (`request_center_global_search`) | `modules/request-center/lib/endpoints.php` | `3c25542` |
| — | اصلاح نگهبان‌های استاتیک three_views به مسیر ماژول | `tests/test_ticket_three_views_static.py` | `d849412` |
| — | دروازهٔ **Phase-2 smoke** در CI Contract | `tests/test_request_center_phase2_smoke_mariadb.php` + workflow | `d076963` |
| — | اصلاح رگرسیون آزمون T04 (تولید config) | `tests/test_request_center_web_ddl_runtime.php` | `fdf7260` |

هر برش پس از انتقال با بررسی «بایت‌به‌بایت» تأیید شد (تنها تغییر اجباری، تغییر نام پارامتر `$searchUser` → `$user` در برش E به‌خاطر امضای تابع؛ SQL دقیقاً یکسان است). قطر `index.php` از ۲۹۸۹ خط به **۲۴۹۵ خط** رسید.

## ۳. وضعیت Bridgeها (تصمیم عمدی)

UI صفحات RC و chrome همچنان از `index.php` با فراخوان‌های نازک به توابع ماژول اجرا می‌شود. حذف Bridgeها و استقلال runtime کامل، یک تصمیم معماری جداست و در فاز ۲ انجام **نمی‌شود**؛ دلیل آن حفظ رفتار اکنون تست‌شده و جلوگیری از تغییر هم‌زمان مالکیت و رفتار است.

دروازهٔ ورودی مستقل (`request-center.php`) عمداً **بدون** `ticket_number_ensure_schema()` نگه داشته شد؛ اجرای DDL در ورودی مستقل، قرارداد zero-DDL و fail-closed روی schema ناقص را می‌شکند.

## ۴. نتایج CI (شاخهٔ کار، آخرین وضعیت)

| گردش‌کار | Run | نتیجه |
|---|---|---|
| Request Center T12 Scenario Characterization | همهٔ pushهای فاز ۲ (شامل `d076963`) | **success** در هر برش |
| Request Center Contract (ماتریس 10.4.32 / 10.11) | `42377354049` | قرمز **فقط** به‌خاطر گام T07 replay تاریخی (معادل D1 مصوب؛ جزئیات §۵) |
| Request Center Contract — گام Phase-2 smoke (جدید) | همان run | **PASS** در هر دو MariaDB |
| Request Center Contract — گام T04 web DDL runtime | همان run | **PASS** (لاگ: healthy index.php=200 با DDL=5/DML=4؛ RC مستقل=200 zero-DDL؛ fail-closed=503 zero-DDL روی نبود `tickets.requesting_unit_id`؛ legacy ستون را برگرداند؛ probe کامل؛ «T04 DIAGNOSTIC COMPLETE») |
| گروهای استاتیک safety (workflow/cancellation/three_views/lifecycle_guide/ldap) | محلی | **33 passed** |

## ۵. قرمزهای شناخته‌شده و دامنهٔ آن‌ها

1. **T07 full-database clone migration replay (001–033) در Contract قرمز است — عمدا.** مطابق تصمیم D1 مصوب مالک، replay تاریخی خارج از دامنهٔ RC است؛ «red» این گام یک وضعیت شناخته‌شده و ثابت شاخه و main است، نه رگرسیون فاز ۲. هیچ تغییری برای سبزکردن مصنوعی این گام انجام نشد.
2. **`tests/test_food_day_visit_static.py`** در pytest سراسری خطای collection دارد؛ مربوط به ماژول غذا (خارج از دامنهٔ RC) و نامربوط به برش‌های فاز ۲.
3. **هیچ گام دیگری قرمز نیست.** T04 و smoke جدید و T12 و پنج مجموعهٔ safety-static سبز هستند.

## ۶. باگ واقعی پیدا و اصلاح‌شده در همین فاز

فایل `tests/test_request_center_web_ddl_runtime.php` از commit `da1fa4d` با `"<?php\\nreturn "` نوشته می‌شد (بک‌اسلش و حرف n در خروجی)؛ فایل config تولیدشده Parse error می‌داد و توالی fixture می‌مرد. بازتولید محلی با php-wasm ثبت شد؛ اصلاح با `fdf7260` منتشر شد و T04 از آن پس تا انتها اجرا و سبز شد.

## ۷. حدود و ادعاهای رد‌شده

- استقلال runtime کاملِ POST/ایجاد درخواست هنوز برقرار نیست؛ فرم‌ها و مسیر نوشتن روی front controller قدیمی با Bridge ماژول اجرا می‌شوند.
- هیچ ادعایی دربارهٔ سازگاری نسخهٔ عملیاتی MariaDB مشتری، نصب تازه یا ارتقا وجود ندارد؛ فراخوان production انجام نشده است.
- هیچ‌یک از جداول/مهاجرت‌ها تغییر نکرده‌اند؛ این فاز فقط انتقال کد است.
- ادعای «Phase 2 complete / Phase 2 verified» در هیچ سند دیگری بدون تأیید کاربر (R6) درج نشده است.

## ۸. گام‌های بعدی پیشنهادی (پس از تأیید کاربر)

1. فاز ۳: چرخهٔ عمر Bridgeها — استخراج قرارداد ورودی POST مستقل RC (خروج کامل از scope front controller) با حفظ redirects/CSRF/exceptions؛ با گیت T12 و smoke جدید.
2. فاز ۴: تقویم SLA/OLA.
3. فاز ۵: UI/بسته نهایی و تهیهٔ بستهٔ انتقال.
4. ثبت نهایی در `docs/architecture/REQUEST-CENTER-ARCHITECTURE.md` با شماره نسخهٔ جدید پس از تأیید R6.
