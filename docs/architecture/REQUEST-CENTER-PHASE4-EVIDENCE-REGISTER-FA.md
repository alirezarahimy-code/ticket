# فاز ۴ — تقویم کارشناسان و برنامه‌ریزی رسیدگی

> **وضعیت: تحویل‌شده برای بررسی مالک (R6 فاز ۴) — ۲۰۲۶-۱۱-۰۷.** این فاز بدون تأیید مالک به `main` یا release/VERSION وارد نمی‌شود. نگشودگی فازهای ۱–۳ پابرجا است؛ هر لمس آن‌ها فقط با دستور صریح مالک.

**نسخه:** ۱٫۰ | **تاریخ:** ۲۰۲۶-۱۱-۰۷ | **شاخه:** `arena/22d4eba7-ticket` | **کامیت آخر:** در همین پکیج (`git log`)

**دامنه:** این سند شواهد فاز ۴ را ثبت می‌کند؛ معادل «فاز سه» سند مرجع (تقویم کارشناسان و برنامه‌ریزی رسیدگی). هیچ تغییری در نسخهٔ سامانه (VERSION)، release، تولید، Logging یا دامنهٔ خارج از ماژول مرکز درخواست انجام نشده است.

---

## ۱. تعریف فاز و مبنای سند معماری

| ردیف | الزام سند مرجع (§۱۰ — «فاز چهار مالک») | وضعیت |
|---|---|---|
| ۱ | استخراج/ساخت مؤلفهٔ عمومی تقویم پس از ممیزی | ✅ `Assets/request-center.css` طراحی مستقل (`.rc-shell`)؛ مؤلفهٔ ماهانهٔ شمسی با today/holiday/weekend و view فهرست روزها |
| ۲ | مدل مستقل نوبت‌های رسیدگی و Migration | ✅ `request_center_appointments` (migration ۰۳۵ از قبل موجود؛ **همین فاز** در `install-empty-db.sql` نیز منعکس شد) |
| ۳ | تقویم شخصی + تیمی | ✅ agent: حوزهٔ خودش؛ supervisor/primary_admin: نمای «تیم» + کارت «ظرفیت و تراکم تیم» |
| ۴ | صف برنامه‌ریزی و ظرفیت | ✅ کارت «نوبت‌های آیندهٔ ۱۴ روز» + کارت «ظرفیت و تراکم تیم» (نمایش مشاهده‌ای؛ **هیچ تصمیم صف/تخصیص خودکاری صادر نمی‌شود**) |
| ۵ | کنترل تراکم و تداخل با تراکنش و قفل | ✅ create درون تراکنش + `SELECT ... FOR UPDATE` (fallback غیرقفلی فقط روی SQLite آفلاین)؛ شمارنده‌ها و cancel/complete نیز اکنون تراکنشی و قفلی‌اند |
| ۶ | ثبت تغییرات و تاریخچهٔ نوبت | ✅ `activity_log` برای create/cancel/complete (`rc_appointment_*` با `module='request-center'` و meta)؛ اعلان کاربر با `notify_user` + `idempotency_key` (sha256) |
| ۷ | اتصال به تعطیلات، ساعات کاری، تاریخ شمسی و منطقه زمانی از قرارداد معتبر | ✅ Gregorian ذخیره + helperهای `jalali_*` برای نمایش کامل؛ مرز tz = `Asia/Tehran` (db-core)؛ تعطیلات از جدول `holidays` فعال؛ روزهای تعطیل هفتگی از `sla_business_hours()['weekend']` (پیش‌فرض پنجشنبه/جمعه = ۴/۵)؛ ثبت بیرون از تقویم فقط برای مدیر با انتخاب صریح |
| ۸ | اتصال اعلان‌ها و مجوزهای تقویم | ✅ complete/cancel/create: مالکیت‌سد + سطح دسترسی `supervisor.panel`/primary_admin؛ همین سطوح تنها می‌توانند برای کارشناس دیگر نوبت بسازند |
| ۹ | آزمون هم‌زمانی و سناریوهای برنامه‌ریزی | ✅ `tests/test_request_center_appointments_sqlite.php` از ۱۲ به ۲۴ قلم (create/overlap/adjacent/multi-assignee/kind/backwards) + complete/cancel-owner/elevated/double-transition + listRange + capacitySummary + countsForRange |

**گیت‌های عبور سند مرجع:**

| گیت | نتیجهٔ شواهد |
|---|---|
| تداخل = ۰ در رقابت هم‌زمان | ایجاد نوبتِ هم‌پوشان برای همان کارشناس پذیرفته نمی‌شود (تست آفلاین ۲۴ قلم سبز؛ قفل `FOR UPDATE` روی MariaDB — تا وقتی روی CI اجرا نشود «پیش‌نیاز: تا اجرای Contract روی این PR») |
| round-trip شمسی در مرزها | نمونه‌برداری ۱٬۶۹۵ تاریخ مرزی/کبیسه (اول/آخر ماه هر ماه از ۱۳۰۰ تا ۱۵۰۰ + تمام کل ماه اسفند ۱۳۹۵–۱۴۳۰): **تطبیق کامل PHP↔JS، صفر خطا**. sweep کامل روی CI (workflow T11) وابسته به اجرای همان جا |
| صفر دسترسی غیرمجاز | نمای تقویم موروثی `ticket_scope`؛ موعدهای SLA/OLA فقط خواندنی؛ نوبت‌ها: مالکیت‌سد در cancel/complete + assignee allowlist برای ثبت به نام دیگران |
| بدون صدمه به SLA/OLA | هیچ ستون/وضعیت SLA/OLA/ticket تغییر نمی‌کند؛ نوبت فقط زمان‌بندی خودِ کارشناس است؛ `sla_paused_at` (تعویق موعد) فقط نمایش است |

---

## ۲. ممیزی و کشف‌های این فاز (پیش از کد)

۱. صفحهٔ قبلی تقویم: Gregorian-only، `format('F Y')` میلادی، بدون شمسی/هفت‌رنگ/تعطیل/KPI/ظرفیت/چاپ — برچسب‌های هدر «.bar» وجود خارجی نداشتند (صفحه واقعاً «صفحهٔ اول که از front controller کلاً بدون CSS داخلی اجرا می‌شد» بود؛ ظاهر نهائی به شانس محتوای مرورگر وابسته بود).
۲. **بازگشت (regression) واقعی کشف شد:** پس از فاز ۳، هر POST بدون `action` ساختهٔ تقویم (فرم ثبت/لغو نوبت) به `request-center/post-handler.php` می‌رفت و **هرگز** به `appointment-actions.php` نمی‌رسید — یعنی ثبت/لغو نوبت وعدهٔ «موجود و سالم» باطل بود و بی‌صدا در dashboard می‌نشست. اصلاح در `request-center.php`: برای `calendar=1` POST مستقیماً به `Pages/index.php` (شاخهٔ POST موجود با `require appointment-actions.php`) می‌رود؛ سایر POSTهای رجیسترشدهٔ RC همچنان به post-handler و بقیه به `index.php` می‌فرستند.
۳. aggregate نصب تازه (`install-empty-db.sql`) جدول `request_center_appointments` را نداشت → اضافه شد (idempotent؛ هم‌بردار با Migration ۰۳۵). هیچ seed به `schema_updates` افزوده نشد (درستی عمداً حفظ شد: aggregate شامل اثرات ۰۳۳/۰۳۴ نیست؛ runner مسئولیّت اعمال Migrationهای ۰۳۳–۰۳۵ را دارد).

## ۳. برش‌های اجراشده (این فاز)

### برش C1 — Repository (`modules/request-center/AppointmentRepository.php`)

- `listRange(?assigneeId, fromTs, toTs, filters{kind?,status?})` با join به `tickets(ticket_no,subject)` و `users(full_name)` — شِمای تغذیهٔ شبکهٔ ماه و تایم‌لاین امروز.
- `countsForRange(?assigneeId, fromTs, toTs, now) → {active, today, overdue, done}` برای نوار KPI.
- `capacitySummary(fromTs, toTs, ?now) → rows(assignee_id, total, done_count, overdue_planned)` برای «ظرفیت این ماه» (حذف نوبت‌های لغوشده؛ فقط مشاهده).
- `complete(appointmentId, actorId, elevated)` — نقش‌آفرینی کامل مشابه `cancel()`: تراکنش، قفل، گذار `planned → done` با `WHERE status = 'planned'` (کاهش اثر رقابت هم‌زمان)، سد مالکیت.
- `cancel()` از read-then-write **تبدیل به تراکنش+`FOR UPDATE`** شد (مشابه create/complete) — پیش‌تر بدون تراکنش بود.
- `assignableStaff(limit)` — فهرست فقط‌خواندنی کارشناس‌های فعال (مجوزهای همین سامانه) برای انتخاب در فرم مدیر.
- هر دو مسیر `FOR UPDATE` با try/catch + fallback بدون قفل برای SQLite آفلاین (MariaDB قفلی است).

### برش C2 — اکشن POST تقویم (`modules/request-center/Actions/appointment-actions.php`)

- اکشن جدید `rc_appointment_complete` (اتمام نوبت) + پیام flash `appointments=completed`.
- **نگهبان تقویم کاری:** ثبت نوبتِ دارای تقاطع با روز تعطیل رسمی (`holidays` فعال) یا تعطیل هفتگی (`sla_business_hours()['weekend']`، ISO-8601؛ پیش‌فرض پنجشنبه/جمعه) **رد می‌شود** مگر مدیر + انتخاب صریح «ثبت حتی بیرون از تقویم کاری/تعطیلات».
- بررسی سلامت کارشناس هدف (وجود + `is_active=1`) پیش از ثبت برای کارشناس دیگر.
- **اعلان‌ها با `notify_user`** (با `idempotency_key = sha256('appointment|<id>|<event>|<assignee>')`) و **audit** با `activity_log('rc_appointment_create|cancel|complete')` برای هر سه عمل — هرگز SQL خام اعلان‌ها.
- متن خطاها با زبان فارسی و اشاره کننده به گزینهٔ دقیقِ مدیر (عدم «سوتفاهم پیام» از نسخهٔ اول).

### برش C3 — رابط کاربری «تقویم شمسی کارشناسان» (`Pages/calendar.php` + `Assets/request-center.css`)

- **شمسی کامل:** هفته با ترتیب شنبه→جمعه؛ اعداد روزها فارسی؛ نام ماه فارسی؛ ناوبری `?jmonth=YYYY-MM` + «این ماه/امروز»؛ `?month=YYYY-MM` میلادی به‌صورت عقب‌رو سازگار همچنان پذیرفته می‌شود و به ماه شمسی معادل برمی‌گردد؛ میلادی داخلی همچنان dir=ltr برای خوانایی.
- **دو نما:** «نمای ماهانه» (شبکهٔ رنگی) و «فهرست روزها» (agenda — مناسب چاپ/موبایل).
- **نوار KPI:** رویدادهای این ماه (پس از فیلتر) / رویدادهای امروز / نوبت‌های معوق / نوبت‌های انجام‌شده.
- **فیلتر نوع رویداد:** همه / موعد SLA·OLA / چهار نوع نوبت (رسیدگی، بازدید، انتظار، پیگیری) — چیپ‌ها.
- **تعطیلات:** از جدول `holidays` فعال در بازه؛ روزهای تعطیل هفتگی با پس‌زمینهٔ مجزا؛ روزهای بیرون از ماه فروکشی؛ امروز با حلقهٔ آبی + چیپ «امروز».
- **سمت مدیر:** کارت «ظرفیت و تراکم تیم» با نوار پیشرفت، آواتار حروفی رنگی، شمارندهٔ معوقِ هر کارشناس؛ کارت «نوبت‌های آیندهٔ ۱۴ روز»؛ انتخاب کارشناس از فهرست واقعی؛ تیک ثبت بیرون از تقویم.
- **سمت کارشناس:** جمع‌بندی شخصی (فعال/معوق/انجام‌شده + تعداد نوبت برنامه‌ریزی‌شده).
- **تایم‌لاین «امروز در یک نگاه»** شامل موعدها و نوبت‌ها با دکمه‌های «انجام شد/لغو» گروهی برای هر نوبت برنامه‌ریزی‌شده.
- **PRINT** آماده (`@media print`): هدر/نوار/قسمت‌ها پنهان، فقط جدول ماه.
- RTL و اعداد فارسی در متن نمایشی؛ چیدمان کاملاً CSS (بدون جاوااسکریپت جدید)؛ `focus` دوست: کنتراست کافی، `aria-label` روی دکمه‌های آیکونی.

### برش C4 — نصب تازه و سازگاری

- `install-empty-db.sql`: بلاک `CREATE TABLE IF NOT EXISTS request_center_appointments` قبل از کامنت baseline افزوده شد (با همتایش در `migrations/035` موازی و idempotent).

### برش C5 — ثبت گیت‌ها و آزمون‌ها

- `tests/test_request_center_appointments_sqlite.php`: بخش «فاز ۴» افزوده شد (۲۴ قلم سبز).
- `.github/workflows/request-center-contract.yml`:
  - `paths` — تیست جدید اضافه شد؛
  - `php -l` برای سه فایل تغییر یافته؛
  - گام جدید «Appointment lifecycle, conflict and capacity contract (SQLite)».

### برش C6 — رگرسیون فاز ۳ (رفع — ثبت فراوان)

شرح در §۲٫۲؛ فایل `request-center.php` مبتنی بر `$_GET['calendar'] === '1'` اکنون POST را به `Pages/index.php` می‌رساند (مسیر GET ماژول از قبل همین بود؛ اکنون POST هم یکسان رفتار می‌کند).

## ۴. شناسه‌های کشف‌شده در بررسی (این فاز)

| # | مورد | شدت | وضعیت |
|---|---|---|---|
| D-01 | POST تقویم هرگز به handler مقصود نمی‌رسید (فاز ۳ side-effect) | بحرانی (ثبت/لغو نوبت بی‌صدا) | **رفع شد** |
| D-02 | `cancel()` بدون تراکنش و قفل بود (read-then-write)؛ گذار وضعیت هم‌زمان می‌توانست اثربخشی مشترک بدهد | متوسط | رفع شد |
| D-03 | تاریخ در صفحه فقط میلادی و Gregorian — شمسی نقض شده بود | بحرانی (الزام محصول) | رفع شد |
| D-04 | aggregate نصب تازه بدون جدول نوبت‌ها | متوسط | رفع شد |
| D-05 | `PREPARE ... FOR UPDATE` بیرون `try` → روی SQLite فاجعه می‌شد | متوسط (فقط test env) | رفع شد |
| D-06 | `NOW()` در SQL ظرفیت → غیرقابل اجرا روی SQLite | کم | رفع شد (پارامتر زمان) |
| D-07 | چاپ صفحه غیرممکن (هدر/نوارها حجم داشتند) | کم | رفع شد (`@media print`) |
| D-08 | اعداد لاتین در تمام نمایش (غیر فارسی) | کم (راحتی فارسی‌زبان) | بهبود یافت (فارسی‌گردانی اعداد در KPI/روز/ماه/٪) |

## ۵. تحویل به مالک (R6 فاز ۴) — دستور مسیر

۱. این فاز به شاخهٔ `arena/22d4eba7-ticket` کامیت و به `feature/request-center-module-1.62.88` (مثل فازها ۱–۳) پوش می‌شود تا **PR #30** به‌روز شود؛ **به `main` merge نمی‌شود**.
۲. پس از تأیید مالک (R6 نهایی برای فاز ۴)، همین فایل به «بسته‌شده» علامت می‌خورد (مشابه فاز ۳ نسخهٔ ۱٫۱).
۳. اقدام فاز ۵ (UX نهایی چهار Workspace، نسخه، release Notes/tag، بحث VERSION) **برای فاز ۵ نگه داشته شده**؛ هیچ تکه‌ای از آن در این فاز دیده نمی‌شود.

## ۶. شناخته‌شده‌ها و بدهی‌ها (صادقانه)

- ورودی تاریخ/زمان فرم «ثبت نوبت» datetime-local مرورگر است (میلادی)؛ مرز شمسی در ذخیره‌سازی/نمایش به‌طور کامل رعایت و در کل پشتیبان `jalali_*` یکطرفه حفظ شده ولی ورود از صفحه، تعمداً بدون double-conversion است تا دور قوی از خطای انسانی نشود. در فاز ۵ می‌توان آن را با تقویم شمسی تعاملی یکرویه جایگزین کرد.
- «تداخل=۰» در رقابت واقعی فقط در MariaDB اثبات می‌شود؛ SQLite آفلاین fallback غیرقفلی دارد. Contract CI باید روی این PR سبز اجرا شود (به‌جز T07 تاریخی که با تصمیم D1 قرمز خواهد بود).
- aggregate schema اثر Migrationهای ۰۳۳/۰۳۴ را ندارد (عمد). Migrationها باید از runner اعمال شوند.
- ظرفیت تیم فقط **مشاهده** است؛ هیچ Auto-Dispatch/توازن بار در این فاز اعمال نمی‌شود (بیرون از دامنه).
- اعلان برای کارشناسِ رزروشده همیشه فرستاده نمی‌شود (صرفاً وقتی رزروکننده ≠ کارشناس) — برای جلوگیری از صدای اضافه برای نوبت‌های شخصی.

## ۷. فایل‌های تغییر یافتهٔ این فاز

| مسیر | نوع | اثر |
|---|---|---|
| `modules/request-center/Pages/calendar.php` | بازنویسی کامل | صفحهٔ شمسی + طراحی + نماها + KPI + تایم‌لاین |
| `modules/request-center/Assets/request-center.css` | گسترش (حفظ بلوک اولیه) | design system ایزولهٔ `.rc-shell` |
| `modules/request-center/AppointmentRepository.php` | گسترش + سخت‌سازی | listRange/countsForRange/capacitySummary/complete/assignableStaff؛ cancel تراکنشی |
| `modules/request-center/Actions/appointment-actions.php` | بازنویسی | complete + notifies + audit + نگهبان تقویم |
| `request-center.php` | اصلاح | مسیر POST تقویم (calendar=1) — رفع رگرسیون فاز ۳ |
| `install-empty-db.sql` | افزودن بلاک | هم‌بردار idempotent Migration ۰۳۵ |
| `tests/test_request_center_appointments_sqlite.php` | گسترش | ۲۴ قلم — چرخهٔ عمر کامل نوبت |
| `.github/workflows/request-center-contract.yml` | افزودن ۳ خط‌path/lint/گام | ثبت گیت‌های فاز ۴ در Contract |
| این سند | جدید | شواهد فاز ۴ |

## ۸. شواهد اجراشده (خلاصه)

- `php -l` روی همهٔ فایل‌های تغییر یافته: سبز.
- `tests/test_request_center_appointments_sqlite.php` → **۲۴/۲۴ سبز** (create، overlap، adjacent، cross-assignee، invalid kind/backwards، listUpcoming، listRange (خود/تیم/فیلتر kind/status)، complete (غیرمالک/مالک/تکراری)، capacitySummary (حذف لغوشده‌ها)، countsForRange، سد برداشت سبد).
- `ArchitectureTest.php`: ۵/۵ سبز — هیچ مرز معماری شکسته نشد.
- نمونهٔ آفلاین شمسی/Gregorian برای مرزهای ماه/سال/کبیسه (JS ↔ PHP): **۱٬۶۹۵ تاریخ — صفر خطا**.
- پیش‌نمایش محلی (fixture SQLite در حافظه، بدون اتصال به هیچ دیتابیس واقعی؛ رندر واقعی صفحه با داده‌های نمونه: ماه جاری ۱۴۰۵): خروجی مستقل `.arena/rc-preview/index.html` (فایل scratch؛ commit نمی‌شود).

