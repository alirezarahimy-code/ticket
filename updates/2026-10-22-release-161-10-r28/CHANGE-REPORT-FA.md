# گزارش اصلاحیهٔ Ticket 1.61.10 — تکمیل اسکن شبکه‌ای R28

**تاریخ:** ۲۰۲۶/۱۰/۲۲
**پایه:** R27
**مهاجرت دیتابیس:** ندارد

## تغییرات روند استخراج شبکه‌ای

1. در فهرست دارایی‌ها دکمهٔ «آزمون حساب روی یک سیستم» اضافه شد. کاربر دقیقاً یک رایانه را انتخاب و حساب ویندوز را وارد می‌کند؛ PowerShell/CIM/WMI اتصال و دریافت دادهٔ واقعی را آزمایش می‌کند، اما هیچ اطلاعاتی در شناسنامه ذخیره نمی‌شود. پس از موفقیت می‌توان اسکن گروهی را اجرا کرد.
2. تشخیص آنلاین‌بودن مقصدها علاوه بر ICMP، SMB/RPC و پورت WinRM یعنی TCP 5985 را نیز بررسی می‌کند. در نتیجه، بسته‌بودن ping به‌تنهایی باعث «آفلاین» شناخته‌شدن مقصدی که WinRM پاسخ می‌دهد نمی‌شود.
3. خطاهای WinRM جداگانه راهنمایی می‌شوند. مستندات رابط اصلاح شد تا روشن باشد WinRM باید روی **کلاینت‌های مقصد** فعال باشد، نه فقط روی سرور PHP. قاعدهٔ فایروال نیز باید از میزبان اسکن تا مقصد اجازهٔ اتصال بدهد.
4. متن رابط تصریح می‌کند یک حساب دامنهٔ دارای دسترسی Administrators روی مقصدها کافی است؛ نیازی به ساخت حساب محلی جداگانه برای هر رایانه نیست.
5. راهنمای عملیاتی فارسی افزوده شد: تفاوت روش TNI با پیاده‌سازی CIM/WMI در Ticket، تنظیمات GPO/فایروال، روند تست یک مقصد و منابع رسمی Microsoft و TNI.

## نتیجهٔ بررسی منابع

صفحهٔ محصول TNI تجربهٔ اسکن بدون عامل دائمی و استفاده از رمز ادمین را معرفی می‌کند؛ مستند فنی خود TNI توضیح می‌دهد که روش Windows آن از عامل موقت در `ADMIN$`/SMB یا WMI/RPC استفاده می‌کند و همچنان به مجوز، سرویس و دسترسی شبکه نیاز دارد. پیاده‌سازی Ticket عامل اختصاصی TNI را نصب نمی‌کند؛ از PowerShell CIM/WMI با DCOM یا WSMan استفاده می‌کند. بنابراین حساب Domain Admin می‌تواند حساب واحد اسکن باشد، به شرط آن‌که روی مقصدها دسترسی مؤثر Administrators داشته باشد و مسیر/فایروال لازم باز باشد.

## آزمون و محدودیت

- ۹ آزمون ایستای متمرکز موجودی سخت‌افزار موفق شدند.
- نحو PHP فایل‌های تغییریافته با parser بررسی شد؛ `git diff --check` و `node --check assets/app.js` نیز موفق بودند.
- در این محیط اتصال واقعی به دامین Windows در دسترس نیست؛ آزمون شبکه‌ای نهایی باید از میزبان Windows پنل روی یک رایانهٔ واقعی انجام شود.
- هیچ migration، تغییر دادهٔ تولیدی یا استقرار تولیدی انجام نشده است.

## منابع اصلی

- [محصول Total Network Inventory](https://www.softinventive.com/products/total-network-inventory)
- [روش‌های فنی SMB و WMI/RPC در TNI](https://docs.softinventive.com/tni/techpaper/scanning-microsoft-windows-assets/)
- [راهنمای دسترسی و فایروال TNI](https://docs.softinventive.com/tni/faq/scan-questions/)
- [Microsoft: WMI راه‌دور با PowerShell](https://learn.microsoft.com/en-us/windows/win32/wmisdk/connecting-to-wmi-on-a-remote-computer-by-using-powershell)
- [Microsoft: امنیت و پورت‌های WinRM](https://learn.microsoft.com/en-us/powershell/scripting/security/remoting/winrm-security?view=powershell-7.6)
- [راهنمای کامل نصب و مجوزهای PowerShell Remoting](https://learn.microsoft.com/en-us/powershell/module/microsoft.powershell.core/about/about_remote_requirements?view=powershell-7.6)
