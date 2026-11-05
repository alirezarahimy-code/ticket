# معماری هدف (To-Be) — ارتقای تدریجی سامانه تیکت

- مبنا: مخزن GitHub نسخه 1.62.82.
- راهبرد: Modular Monolith با مرزهای Clean/Hexagonal در هر ماژول.
- الزام: حفظ URLها، actionها، مجوزها، داده‌ها، خروجی‌ها و گردش کار فعلی.
- خارج از هدف: بازنویسی یکباره، انتقال فوری به Laravel، microservices یا تغییر بزرگ schema.

## 1. تصمیم معماری
سامانه به مونولیت ماژولار تبدیل می‌شود؛ PHP، MySQL/MariaDB و Apache/XAMPP در فازهای نخست حفظ می‌شوند. ماژول‌ها واحد deploy مستقل نیستند، اما مالکیت منطق، داده، قرارداد و تست مشخص خواهند داشت.

اصول:
1. رفتار فعلی قرارداد است و با characterization tests تثبیت می‌شود.
2. هر ماژول مالک منطق دامنه خود است.
3. Controller فقط ورودی/خروجی را مدیریت می‌کند؛ business rule و SQL در آن قرار نمی‌گیرد.
4. Use Case هماهنگ‌کننده عملیات و transaction است.
5. Repository مسئول persistence و PDO در Infrastructure است.
6. مجوز و scope در سطح عملیات و رکورد اعمال می‌شوند.
7. migrationها افزایشی، قابل مشاهده و دارای preflight هستند.
8. زیرسامانه‌های حساس غذا، تردد، Logging و SystemUpdate مرزهای روشن دارند.
9. هر فاز با تست، PR کوچک و staging acceptance بسته می‌شود.

## 2. نمای کلان

Browser / Local clients / Scheduled jobs
→ HTTP/CLI Entry
→ Router + Request Context
→ Middleware: Session, Auth, CSRF, Permission, Scope, Validation
→ Presentation Controllers
→ Application Use Cases
→ Domain Rules / Events
→ Ports: Repository, Clock, FileStore, Queue, Identity
→ Infrastructure: PDO/MySQL, LDAP, Local FS, Windows jobs
→ MySQL/MariaDB + Local File System

## 3. ساختار پوشه پیشنهادی
این ساختار مقصد است؛ نباید همه فایل‌ها یکباره به آن منتقل شوند.

app/
- Core/: Bootstrap, Routing, Http, Middleware, Database, Authorization, Validation, Events, Jobs, Exceptions
- Modules/
  - Identity/
  - Organization/
  - AccessControl/
  - Ticket/
  - ServiceCatalog/
  - SlaOla/
  - AssetCmdb/
  - Governance/
  - Notification/
  - Logging/
  - Knowledge/
  - Food/
  - Traffic/
  - MediaControl/
  - Reporting/
  - SystemUpdate/
- Shared/: Contracts, DTO, ValueObjects, Support
routes/: web.php, api.php
database/: migrations, seeders, schema
resources/: views, js, css
public/: index.php
storage/: logs, cache, queue, updates, backups
cron/, workers/, tests/

### سازگاری با کد فعلی
- index.php به‌تدریج Front Controller نازک می‌شود.
- actions/* ابتدا Adapter/Controller می‌شوند؛ نام action و قرارداد ورودی حفظ می‌شود.
- lib/* ابتدا در جای خود می‌ماند و از طریق Facade/Adapter فراخوانی می‌شود.
- فایل‌های حوزه‌ای ریشه تا روشن شدن مالکیت و تست جابه‌جا نمی‌شوند.
- scaffold موجود app/ بدون ارزیابی ادغام نمی‌شود.

## 4. مرز ماژول‌ها و مالکیت

| ماژول | مسئولیت | وابستگی‌های مجاز |
|---|---|---|
| Identity | حساب، نشست، ورود محلی/LDAP | AccessControl |
| Organization | شرکت، درخت واحد، مدیر و انتساب | Identity |
| AccessControl | نقش، مجوز، گروه، policy و scope | Identity, Organization |
| Ticket | درخواست، گفتگو، گردش وضعیت، ارجاع و لغو | Identity, Organization, Catalog, SLA, Asset |
| ServiceCatalog | خدمت، دسته، فرم و فیلد پویا | AccessControl |
| SlaOla | تقویم، ساعات کاری، مهلت و escalation | Ticket |
| AssetCmdb | دارایی، شناسنامه، inventory و روابط CI | Identity, Organization |
| Governance | Change، Problem، CAB و Known Error | Ticket, Identity |
| Notification | اعلان داخلی، صف و تحویل | Identity, Ticket, Logging |
| Logging | رخداد، correlation، integrity، replay و health | Shared contracts |
| Knowledge | راهنما، مقاله، جست‌وجو و دستیار | AccessControl |
| Food | سفارش، گروه، چاپ و تطبیق منبع | Identity adapter, AccessControl |
| Traffic | تردد، مقصد و زنجیره تأیید | Identity, Organization |
| MediaControl | گردش CD/DVD | Identity, Organization |
| Reporting | گزارش و export با scope اجباری | Query services ماژول‌ها |
| SystemUpdate | manifest، preflight، backup، migration، stage و verify | Logging, Infrastructure |

مالکیت نهایی جدول‌ها باید با inventory واقعی تثبیت شود؛ در فاز اول جدول‌ها rename یا split نمی‌شوند.

## 5. قراردادهای فنی

### Routing و HTTP
- هر route شناسه پایدار، method، permission و handler مشخص دارد.
- routeهای فعلی alias سازگار خواهند داشت.
- Controller درخواست را parse و validate می‌کند و Use Case را فرا می‌خواند.
- redirect، flash، CSV/XLSX و status code فعلی تا پایان مهاجرت حفظ می‌شوند.
- route index از تعریف route تولید و route تکراری/بی‌مالک/بدون permission را رد می‌کند.

### Application
Use Caseهای مشخص مانند CreateTicket، AssignTicket، TransitionTicket، RequestCancellation، SaveOrganizationUnit، CreateChangeRequest و ApplyUpdatePackage تعریف می‌شوند. هر Use Case مجوز، scope، validation، transaction، audit و notification را هماهنگ می‌کند.

### Domain
قواعد وضعیت تیکت، SLA/OLA، سازمان و مجوزها مستقل از $_POST، $_SESSION، PDO و HTML می‌شوند.

### Persistence
- MySQL/MariaDB و PDO حفظ می‌شوند.
- Repository فقط داخل محدوده ماژول قرار می‌گیرد.
- گزارش‌های سنگین Query Service مستقل دارند.
- Foreign key و unique constraint مرجع یکپارچگی داده‌اند.
- ORM یا تعویض DB در فاز نخست لازم نیست.

### Events و Queue
رویدادهای نسخه‌دار مانند TicketCreated، TicketAssigned، TicketResolved، ChangeApproved و AssetInventoryReceived تعریف می‌شوند. ابتدا dispatcher درون‌پردازه‌ای و outbox ساده MySQL کافی است؛ broker خارجی فقط در صورت اثبات نیاز وارد می‌شود.

## 6. امنیت هدف
- Authentication، Authorization، CSRF، rate limit و scope در middlewareهای مستقل قرار می‌گیرند.
- policyها نام‌دار و قابل تست هستند.
- queryهای وابسته به کاربر باید scope را در SQL/query service اعمال کنند؛ کنترل UI کافی نیست.
- upload با allowlist نوع/MIME، محدودیت حجم، نام تصادفی، storage خارج webroot و دانلود مجازشده انجام می‌شود.
- نشست پس از login و تغییر سطح دسترسی regenerate می‌شود؛ cookie امن، SameSite و timeout تعریف می‌شود.
- secrets خارج از webroot و Git نگهداری می‌شوند.
- audit business از log فنی جداست و اطلاعات محرمانه/توکن ثبت نمی‌شود.

## 7. پایگاه داده و migration
1. schema.sql و install-empty-db.sql از یک منبع تولید شوند یا parity test کامل داشته باشند.
2. هر migration شناسه یکتا، پیش‌نیاز، checksum، زمان و نتیجه ثبت کند.
3. migrationها کوچک و افزایشی باشند.
4. عملیات destructive فقط با backup تأییدشده و تایید صریح انجام شود.
5. DDL در مسیر HTTP عادی ممنوع شود؛ startup فقط check/read-only انجام دهد.
6. backfill داده جدا و batch/checkpointدار باشد.
7. برای DDL غیرقابل rollback، forward-fix و restore plan تعریف شود.
8. قبل/بعد migration، row count، constraint، index و invariants بررسی شوند.

انتقال: همه *_ensure_schema()ها inventory شوند؛ با migration متناظر تطبیق یابند؛ در دوره سازگاری فقط check/read-only انجام دهند؛ پس از پوشش نسخه‌های پشتیبانی‌شده DDL runtime حذف شود.

## 8. System Update هدف
1. اعتبارسنجی upload، حجم و نوع.
2. بررسی ZIP و تعداد/اندازه کل ورودی‌ها.
3. رد absolute path، traversal، symlink، مسیر خارج allowlist و نام تکراری.
4. اعتبارسنجی manifest/version و تطبیق دقیق فایل‌های اعلام‌شده.
5. SHA-256 هر فایل و preflight نسخه/فضا/مجوز/DB.
6. backup با شناسه و بررسی وجود/اندازه خروجی.
7. extraction به stage خارج webroot و کنترل تمام return valueها.
8. اجرای migration و ثبت مرحله‌ای.
9. کپی اتمیک فایل‌ها با temp + rename در حد فایل‌سیستم.
10. smoke checks و verify نسخه/manifest.
11. ثبت VERSION فقط پس از موفقیت gateها.
12. در خطا توقف، حفظ شواهد و اجرای restore/forward-recovery plan؛ عدم اعلام موفقیت کاذب.

مسیرهای config، secrets، storage، uploads و هر مسیر خارج root مجاز نباید از بسته جایگزین شوند.

## 9. Observability و عملیات
- لاگ ساخت‌یافته با request_id/correlation_id.
- audit business مستقل با actor/action/target/result/time.
- health checks تفکیک‌شده readiness، liveness و dependency.
- queue depth، retry، dead-letter و replay با idempotency key.
- backup و retention تعریف‌شده و آزمون restore دوره‌ای.
- runbook برای ارتقا، rollback، خرابی DB، پرشدن دیسک و بازیابی صف.
- شاخص‌ها: error rate، p95 latency، DB latency، queue age، failure اعلان و backup success.

## 10. راهبرد تست
| سطح | هدف | مثال |
|---|---|---|
| Characterization | تثبیت رفتار فعلی | route، redirect، transition، خروجی |
| Unit | قواعد خالص | SLA، policy، transition |
| Integration | DB و transaction | create/assign/close و migration |
| Contract | سازگاری ماژول‌ها | route/action/permission/manifest |
| Security | رفتار واقعی | CSRF، IDOR، upload، scope، session |
| Acceptance | سناریوی کاربر | ثبت تا بستن درخواست |
| Operational | Windows | NTFS ACL، backup/restore، updater |
| Performance | ظرفیت | queue، dashboard، report، concurrency |

SKIP هرگز PASS محسوب نمی‌شود؛ Windows acceptance باید با فایل‌های موجود و runner واقعی هم‌تراز باشد.

## 11. نقشه مهاجرت

| فاز | کار | معیار خروج |
|---|---|---|
| 0 | baseline route/action/permission، parity schema، characterization tests | baseline تکرارپذیر |
| 1 | سخت‌سازی updater و اصلاح Logging acceptance | انتشار fail-closed و CI معتبر |
| 2 | Router، RequestContext، middleware و error handling با adapter | ورودی استاندارد، رفتار قبلی |
| 3 | Ticket Domain/Application/Repository | تست سناریوهای تیکت سبز |
| 4 | Identity، Organization و AccessControl | ماتریس نقش/گروه/scope سبز |
| 5 | Catalog، Asset و Governance | مالکیت و event contract روشن |
| 6 | Notification، Logging و workers | صف idempotent و عملیات قابل پایش |
| 7 | Food، Traffic و MediaControl | قراردادهای ورودی/چاپ حفظ شده |
| 8 | UI و Reporting | route/DOM contract و پذیرش کاربر |
| 9 | پاک‌سازی legacy | هیچ مصرف‌کننده‌ای از adapter قدیمی باقی نمانده |

هر فاز branch مستقل، PR کوچک، CI سبز و staging acceptance دارد. هیچ مرحله‌ای مستقیم روی production اجرا نمی‌شود.

## 12. معیار پذیرش نهایی
- routeهای قدیمی حفظ یا با تصمیم نسخه‌دار جایگزین شده‌اند.
- هیچ Use Case بدون permission/scope روشن نیست.
- عملیات چندمرحله‌ای transaction و idempotency تعریف‌شده دارند.
- DDL در مسیر HTTP عادی اجرا نمی‌شود.
- نصب تازه و ارتقا به schema هم‌ساختار می‌رسند.
- updater روی ZIP ناقص/خراب/دستکاری‌شده fail-closed می‌کند.
- CI شامل Linux و Windows acceptance معتبر است.
- restore بکاپ در محیط آزمایشی اثبات شده است.
- هر ماژول مالک داده، API داخلی، event و تست مشخص دارد.
- رفتارهای اصلی با characterization و acceptance tests پوشش داده شده‌اند.

## 13. تصمیم‌های تعویق‌افتاده
Laravel/framework migration، microservices، ORM، تعویض DB، تغییر بزرگ schema، بازطراحی کامل UI و broker جدید تا زمانی که نیاز و business case با داده ثابت نشده، انجام نمی‌شوند.
