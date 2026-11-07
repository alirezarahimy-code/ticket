# فاز ۳ — چرخهٔ عمر Bridgeها (استقلال اجرای POST مرکز درخواست)

**نسخه:** ۱٫۰ | **تاریخ:** ۲۰۲۶-۱۱-۰۷ | **شاخه:** `arena/22d4eba7-ticket` | **کامیت آخر:** (در همین پکیج)

**دامنه:** این سند وضعیت فاز ۳ را ثبت می‌کند: استخراج قرارداد مشترک POST و اجرای واقعی رجیستری اکشن‌های POST مرکز درخواست از مرز ماژول، با حفظ **بایت‌به‌بایت معنای پروتکل legacy**. هیچ UI، URL قابل مشاهده، Schema، Migration، logging schema یا خارج از دامنه تغییر نکرده است.

---

## ۱. ممیزی Bridgeهای POST پیش از فاز ۳

### ۱٫۱ محل و نحوهٔ اجرا (قبل)

```
index.php
  └─ if ($_SERVER['REQUEST_METHOD'] === 'POST')                    (ناحیهٔ ۴)
       require_csrf()                                               (خط مقرر)
       logging_begin_operation('post:' . $action)
       $GLOBALS['activity_post'] = [...] + register_shutdown_function(…)
       try {
           … بارگذاری گروه‌های اکشن (غیر RC) …
           require modules/request-center/Actions/dispatch.php      (RC: ۱۹ اکشن)
           … بارگذاری باقی گروه‌ها (غیر RC) …
       } catch (Throwable $e) {
           rollback مرحله‌ای + activity_log('post_action_failed') + app_error_log + flash + redirect('index.php?page='.$page)
       }
```

- مالکیت کد RC در `Actions/*.php` از قبل ماژولی بود؛ اما **محیط اجرا (context) کاملاً به حوزهٔ متغیر `index.php` وابسته بود**: csrf، shutdown-log، catch-transaction-redirect. خروج از scope front controller عملاً ممکن نبود.
- `appointment-actions.php` از این رجیستری مستثنی است: از شاخهٔ تقویم `modules/request-center/Pages/index.php` اجرا می‌شود و خودش `require_csrf()` می‌کند؛ رفتار آن بدون تغییر ماند.

### ۱٫۲ وابستگی‌های حوزهٔ legacy (کدام‌ متغیر/تابع محیطی)

| وابستگی | منبع | نحوهٔ تأمین در برش جدید |
|---|---|---|
| `require_csrf()` | `lib/logging-csrf.php` | بدون تغییر — در `post_context_begin()` صدا زده می‌شود |
| `logging_begin_operation` | `lib/logging-core.php` (اختیاری/function_exists) | همان نگه‌داریِ شرط `function_exists` حفظ شد |
| `activity_post` Global + shutdown | inline در `index.php` | به `lib/http-post.php::post_context_begin()` منتقل و در هر دو مسیر یکسان |
| `db()->inTransaction()` / `db_rollback()` | `lib/db-core.php` | بدون تغییر |
| `activity_log('post_action_failed')` | `lib/activity-audit.php` | بدون تغییر — در `post_context_on_error()` |
| `app_error_log`، `flash`، `redirect`، `$_POST['group_id']` (استثنای settings) | libهای مشترک | بدون تغییر — شرط `page === 'settings'` عیناً حفظ شد |
| normalize `$page` (`dashboard` پیش‌فرض) | خط ۴۱۸ `index.php` | به `post_context_on_error()` پاس داده می‌شود (redirect هدف یکسان) |
| ۱۹ نام اکشن RC | استخراج با grep از رجیستری Handlerها | ثابت `REQUEST_CENTER_POST_ACTIONS` در `post-handler.php` |

خروج هر یک از اجزای بالا از پروتکل، ردّ فاز را در پی خواهد داشت؛ بنابراین هیچ‌یک تغییر نکردند — فقط **جابه‌جایی فیزیکی** به‌همراه فراخوانی یکسان انجام شد.

## ۲. برش‌های اجراشده در این فاز

### ۲٫۱ برش A — قرارداد مشترک POST (`lib/http-post.php` — فایل جدید)

`post_context_begin(): string` و `post_context_on_error(Throwable, string): never`؛ هر خط از کد پروتکل legacy عیناً (فقط در ناحیهٔ تابع باقی‌مانده). `index.php` در ناحیهٔ ۴ اکنون:

```php
require_once __DIR__ . '/lib/http-post.php';
post_context_begin();
```
و در `catch`:
```php
post_context_on_error($exception, $page);
```
- تنها تغییر رفتاری: هیچ‌یک. (تأیید با diff دیده‌شدن; `register_shutdown_function` خط ~۴۳۹ که مربوط به org-sync است دست‌نخورده.)

### ۲٫۲ برش B — اجراکنندهٔ POST رجیستری RC در مرز ماژول (`modules/request-center/post-handler.php` — فایل جدید)

- `request_center_is_post_action(string): bool` و `request_center_handle_post(): bool`
- در صورت تطابق با رجیستری: `bootstrap.php` خودکفا بارگذاری، سپس `WorkflowService.php` و `lib/functions.php` (با ترتیب legacy)، سپس `lib/http-post.php` و `dispatch.php`؛ خروجی همیشه `redirect()/exit` (مثل legacy).
- در **نبود تطابق**: بدون هیچ side effect مقدار `false` برمی‌گرداند.
- fallback دفاعی در صورت واگرایی رجیستری/دیسپچر: `post_context_on_error(RuntimeException(...))` — همان قرارداد خطای legacy.

### ۲٫۳ برش C — ورودی مستقل RC اکنون POST-aware (`request-center.php`)

```php
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_once __DIR__ . '/modules/request-center/post-handler.php';
    if (request_center_is_post_action((string) ($_POST['action'] ?? ''))) {
        request_center_handle_post();  // پایان مسیر با redirect/exit
        exit;
    }
    require __DIR__ . '/index.php';    // forward درجا؛ بدون redirect، payload کامل
    exit;
}
```
- UI فعلی هنوز به `index.php` پست می‌زند (URL/رفتار بدون تغییر). مسیر مستقل، **ظرفیت جدید آزموده‌شدهٔ ماژول** است؛ سوئیچ‌کردن فرم‌های واقعی یک decision جدا و در کنار smokeهای بعد فاز است.
- ورودی GET ماژول همچنان **zero-DDL** است؛ مسیر POST طبق طراحی legacy ممکن است ensure-های موجود را اجرا کند.

### ۲٫۴ برش D — دروازهٔ smoke برای مسیر مستقل (افزودن به `tests/test_request_center_phase2_smoke_mariadb.php`)

اقلام جدید (روی همان دیتابیس disposable CI):

| # | مورد | انتظار |
|---|---|---|
| 5 | POST بدون `csrf` به `request-center.php` (`create_ticket`) | **HTTP 419** (قرارداد legacy) |
| 6 | `create_ticket` کامل با csrf از entry مستقل | **302** + `Location: index.php?page=ticket&id=`؛ ردیف `tickets` در DB ساخته شده |
| 7 | اکشن غیربوزینس (`zz_not_an_action`) به entry مستقل | **200** + forward درجا؛ payload از دست نمی‌رود |

موارد ۱–۴ پیشین (dashboard/search/export/download) بدون تغییر باقی ماندند.

## ۳. تضمین رفتار legacy

- `index.php` همچنان رجیستری RC را **مستقیماً** با همان `try/catch` و همان ترتیب بارگذاری اجرا می‌کند؛ فقط بدنهٔ پروتکل از مسیر `lib/http-post.php` می‌گذرد. diff ناحیهٔ ۴ در `index.php` فقط ۹ سؤال replace است (نشان داده‌شده در diff کامیتِ فاز ۳).
- هیچ مسیر تولیدِ کاربر (فرم‌ها، URLها) به `request-center.php` منتقل نشده است.
- redirect هدف روی خطا `index.php?page=<normalized $page>` دقیقاً همان پارامتر قبلی است (نه `$_GET['page']`).

## ۴. راستی‌آزمایی انجام‌شده روی commit‌های این فاز

| دروازه | انتظار/نتیجه |
|---|---|
| `php -l` روی فایل‌های جدید/تغییریافته | سبز |
| تطبیق خط‌به‌خط بلوک‌های منتقل‌شدهٔ پروتکل با نسخهٔ قبل از کامیت | قبل و بعد یکسان دیده‌شد (BEGIN block همه خط‌ها در helper حاضر؛ catch markers حاضر؛ `register_shutdown_function`های نامرتبط حفظ شدند) |
| گوشت replay آزمایشی T12 (`--run-changed` روی push) | انتظار سبز؛ نتیجه در Run مربوطه ثبت می‌شود |
| Contract (ماتریس هر دو MariaDB) | گام‌های T01–T06 سبز؛ گام **Phase-2 smoke** اکنون موارد ۱–۷ را پوشش می‌دهد؛ قرمزی باقی‌مانده **فقط** T07 replay تاریخی (D1) |
| پنج مجموعهٔ safety-static محلی | 33 passed |

## ۵. حدود و ادعاهای رد‌شده

- این فاز **استقلال runtime کامل** را تضمین نمی‌کند؛ راکت POST هنوز به همان helperهای مشترک (auth/scope/csrf/db) وابسته است — این وابستگی طراحی مورد قبول است.
- فرم‌های UI هنوز به `index.php` ارسال می‌شود؛ هدایت واقعی فرم‌ها به ورودی مستقل یا حذف `index.php`، موضوع فازهای بعد است.
- تلاش برای **سبزکردن مصنوعی T07** انجام نشده و نخواهد شد.
- هیچ تغییر Schema/Migration/rolling-update و هیچ نصب تولیدی انجام نشده است؛ صحت روی MariaDB عملیاتی ثبت نشده است.
- «Phase 3 complete» در هیچ سند بالادستی این‌جا ثبت نشده؛ تصمیم نهایی با تأیید شماست.

## ۶. خروجی برای فاز بعد (پس از تأیید)

1. (اختیاری/پس از تأیید) سوئیچ فرم‌های POST ماژول (مثل فرم تقویم/امتیازدهی) به ورودی مستقل + smoke منفی/مثبت اختصاصی هر فرم.
2. اجرای کاندید ۱ تا ۳ ورودی POST مستقل پشت پرچم تنظیمات (به‌صورت opt-in) — در صورت تمایل شما.
3. سپس رفع باقی‌ماندهٔ To-Be چرخهٔ عمر (SLA/OLA روی همهٔ transitionها + Audit یکپارچه) یا فاز تقویم — به انتخاب شما.
