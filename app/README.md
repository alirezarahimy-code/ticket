# پوشهٔ `app/` — بازماندهٔ یک اسکلت MVC که هرگز اجرا نشد

**این پوشه کد زندهٔ سامانه نیست.** اگر دنبال منطق واقعی می‌گردید، اینجا نیست.

## ماجرا چه بود

زمانی یک اسکلت MVC (شامل `Controllers/`، `Core/Router.php`، `Models/`،
`Services/` و `routes/web.php`) در مخزن ساخته شد، ولی هیچ‌وقت به مسیر اجرا وصل
نشد: نه `composer.json` و نه `vendor/autoload.php` وجود دارد، هیچ فایلی آن
کلاس‌ها را `require` نمی‌کند، و `index.php` همچنان با الگوی قدیمی
`?page=…` کار می‌کند.

خودِ تست معماری مخزن هم این را ثبت کرده بود:
`tests/ArchitectureTest.php` صریحاً بررسی می‌کند که `index.php` به
«unused MVC scaffold» مسیریابی **نکند**.

### چرا صرفِ ماندنش خطرناک بود

`app/Services/AuthService.php` یک نسخهٔ دوم از منطق ورود داشت — با
`session_regenerate_id(true)` و `$_SESSION['user_id']` — که دقیقاً شبیه کد زنده
به نظر می‌رسید. هر کسی که برای رفع یک اشکال امنیتی دنبال «جای ورود کاربر»
می‌گشت، می‌توانست این فایل را اصلاح کند و با خیال راحت برود، در حالی که کد
واقعی (`lib/auth-scope.php`) دست‌نخورده مانده بود.

## در نسخهٔ ۱.۶۲.۸۳ چه شد

این‌ها حذف شدند:

```
app/Controllers/     app/Core/     app/Models/     app/Services/
app/Views/dashboard/ app/Views/ticket/            routes/
```

و فقط این دو فایل ماندند:

```
app/Views/layout/header.php
app/Views/layout/footer.php
```

**چرا این دو ماندند؟** چون در manifest **۱۶ بستهٔ منتشرشده** فهرست شده‌اند.
بسته‌های منتشرشده مصنوعات منجمدند و چند تست نگهبان بررسی می‌کنند که هر فایلِ
نام‌برده در manifest هنوز در مخزن موجود باشد. حذف این دو یعنی دروغ‌شدن آن
manifestها. این دو فایل صرفاً قالب نمایشی‌اند و هیچ منطقی ندارند.

## کد واقعی کجاست

| موضوع | فایل واقعی |
|---|---|
| ورود کاربر و سطح دسترسی | `lib/auth-scope.php` |
| شروع نشست | `session_bootstrap.php` |
| مسیریابی صفحه‌ها | `index.php` (الگوی `?page=…`) |
| دسترسی به دیتابیس | `lib/db-core.php` |
| منطق درخواست‌ها | `actions/ticket-actions.php` و `lib/tickets-sla.php` |

نقشهٔ کامل: `docs/ARCHITECTURE-FA.md` و `docs/system-map/`.
