# خواندن خروجی تشخیص — گزارش واقعی BAZRESI-PC

**نسخه:** ۱.۶۳.۱-r17-ه · **مبنا:** خروجی `diag_extract.php` روی سرور `Patsar.Local`

این سند خروجی واقعی را می‌خواند: چه چیزی درست است، چه چیزی ریشهٔ مشکل است، و دقیقاً چه باید کرد.

---

## خلاصه در یک نگاه

| سنجه | نتیجه | معنا |
|---|---|---|
| سیستم‌عامل سرور | Windows 10.0 (Server 2016) | ✅ |
| عضو دامنه | **Patsar.Local** | ✅ **فرض من غلط بود** — سرور عضو دامنه است |
| TCP 445 | **باز، ۳–۱۱ ms** | ✅ مسیر SMB از نظر شبکه آماده است |
| TCP 135 | باز، ۱ ms | ✅ RPC Endpoint Mapper |
| TCP 5985 | بسته | ❌ WinRM روی مقصد نیست — طبیعی است |
| DCOM | `Access is denied` | ❌ توکن فیلتر شده |
| WinRM | `0x80090311` | ❌ **no logon servers** |
| **اسکریپت SMB** | **پیدا نشد** | ❌ **ریشهٔ اصلی — این را کسی ندیده بود** |

---

## ۱. مهم‌ترین یافته: فایل `collect_via_smb.ps1` روی سرور نیست

```
✗ اسکریپت SMB         tools/collect_via_smb.ps1
```

این خط را در خروجی‌تان داشتید و از کنارش رد شدید — ولی **همین ریشهٔ اصلی است.**

`domain_scan_remote_inventory_via_smb()` در همان خط اول `is_file()` می‌زند. اگر فایل نباشد، **هر مقصدی** بدون استثنا خطا می‌گیرد:

```php
$script = domain_scan_smb_script_path();
if ($problem === null && !is_file($script)) {
    $problem = 'اسکریپت tools/collect_via_smb.ps1 پیدا نشد.';
}
```

و این خطا به پیام عمومی «استخراج ناموفق» تبدیل می‌شود. یعنی:

> **تمام کاری که در r17 برای روش SMB کردم، روی سرور شما هرگز اجرا نشده.** نه چون کد غلط بود — چون فایلش نبود.

فایل **داخل بسته هست** (`files/tools/collect_via_smb.ps1`، ۹٫۳۶۴ بایت) ولی در `C:\xampp\htdocs\ticket\tools\` نیست.

### رفع — یکی از این دو

**الف) نصب‌کننده** (توصیه می‌شود — همهٔ فایل‌ها را می‌گذارد و بررسی می‌کند):

```
INSTALL-UPDATE-FILES.bat C:\xampp\htdocs\ticket
```

**ب) دستی:**

```cmd
copy "مسیر-بسته\files\tools\collect_via_smb.ps1" "C:\xampp\htdocs\ticket\tools\"
```

بعد دوباره `diag_extract.php` را اجرا کنید — این خط باید `✓` شود.

---

## ۲. `0x80090311` — خطای جدیدی که پیش از این نگاشت نداشت

```
Wsman: WinRM cannot process the request. The following error with errorcode
0x80090311 occurred while using Kerberos authentication: There are currently
no logon servers available...
```

این کد یعنی **`SEC_E_NO_CREDENTIALS`**: ویندوز نتوانست بلیت Kerberos بگیرد.

دو علت ممکن، به ترتیب احتمال:

**الف) سرور سامانه کنترل‌کنندهٔ دامنه را نمی‌بیند.** عضو دامنه است (`Patsar.Local`) ولی DC در دسترسش نیست. علامت کلاسیکش: DNS سرور به DNS اینترنت یا روتر اشاره می‌کند، نه به DC.

آزمون روی سرور سامانه:

```cmd
nltest /dsgetdc:patsar.local
```

اگر پاسخ نداد یا خطا داد، همین است.

**ب) نام کاربری حساب دامنه نبود.** در خروجی دوم نوشته بودید `CORP\scanuser` — ولی دامنهٔ واقعی شما **`Patsar.Local`** است، نه `CORP`. آن حساب اصلاً وجود ندارد.

آزمون درست:

```cmd
C:\xampp\php\php.exe tools\diag_extract.php BAZRESI-PC Patsar\scanuser
```

> بخش ۰.۵ ابزار حالا خودش DC را می‌سنجد و اگر پیدا نشد، صریح می‌گوید.

---

## ۳. `DCOM: Access is denied` — و چرا این اصلاً مهم نیست

```
✗ Dcom    Access is denied.
```

بسته رسیده، احراز هویت شده، ولی توکن مدیریتی فیلتر شده (UAC از راه دور) یا حساب روی مقصد در Administrators نیست.

**ولی این مسیر برای شما لازم نیست.** TCP 445 باز است و SMB آماده — فقط فایلش نبود.

---

## ۴. حساب سرویس Apache پیدا نشد — خودِ این هم یافته است

```
✗ حساب سرویس Apache    سرویس Apache2.4 پیدا نشد — شاید نام دیگری دارد
```

دو حالت:

**الف) نام سرویس چیز دیگری است.** روی Server 2016 با XAMPP، نام ممکن است `Apache2.4` نباشد. ابزار حالا **خودش لیست سرویس‌های xampp/apache را چاپ می‌کند** تا حدس نزنید.

**ب) Apache به‌عنوان سرویس نصب نشده** و از XAMPP Control Panel اجرا می‌شود. در این حالت **هیچ سرویسی وجود ندارد** و هویت Apache همان کاربری است که Control Panel را باز کرده.

برای دیدن هویت واقعی Apache:

```
Task Manager → Details → ستون User Name را اضافه کنید → httpd.exe
```

**این مهم است:** چون حساب کاربری `PSAdmin` تنها هویت **cmd** شماست، نه هویت Apache. استخراج از پنل با هویت Apache اجرا می‌شود.

---

## ۵. ترتیب درست کار

```
۱. INSTALL-UPDATE-FILES.bat C:\xampp\htdocs\ticket
     ← فایل گم‌شده را سر جایش می‌گذارد

۲. C:\xampp\php\php.exe tools\diag_extract.php BAZRESI-PC Patsar\scanuser
     ← با نام دامنهٔ درست، و بخش ۴ این بار اجرا می‌شود

۳. اگر بخش ۴ «موفق» شد → روش SMB کار می‌کند؛ از پنل استخراج کنید
   اگر «ناموفق» شد → حساب Patsar\scanuser روی BAZRESI-PC در Administrators نیست
```

**نکتهٔ کلیدی:** اگر بخش ۴ سبز شود، استخراج از پنل هم کار می‌کند — حتی با Apache که هویتش LocalSystem یا هر چیز دیگر است، چون SMB با اعتبارنامة خودتان به مقصد وصل می‌شود.

---

## ۶. کارت‌های زرد در پنل (SMB «باز» ولی استخراج ناموفق)

از تصویری که فرستادید: `PC-023` تا `PC-028`، ستون SMB **باز** ولی WinRM و DCOM **بسته**.

SMB باز است. پس اگر فایل `collect_via_smb.ps1` سر جایش باشد و حساب روی آن مقصدها ادمین محلی باشد، همین کارت‌ها باید سبز شوند.

اگر بعد از گام ۱ سبز نشدند، برای یکی از آن‌ها:

```cmd
C:\xampp\php\php.exe tools\diag_extract.php PC-023 Patsar\scanuser
```

بخش ۴ می‌گوید حساب ادمین محلی هست یا نه — همان شرطی که TNI هم دارد.
