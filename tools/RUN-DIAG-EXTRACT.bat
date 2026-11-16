@echo off
rem ============================================================================================
rem  RUN-DIAG-EXTRACT.bat — اجرای ابزار تشخیص استخراج بدون نیاز به پیدا کردن php.exe
rem  ۱.۶۳.۱-r17-ب
rem
rem  چرا این فایل: روی سرورهای IIS، php.exe اغلب در PATH نیست و کاربر باید مسیر
rem  C:\Program Files\PHP\... را دستی پیدا کند. همان کاری که cron/*.bat در همین
rem  پروژه می‌کند: اول PATH، بعد پوشه‌های رایج، بعد php.exe کنار IIS.
rem
rem  استفاده:
rem     RUN-DIAG-EXTRACT.bat BAZRESI-PC
rem     RUN-DIAG-EXTRACT.bat BAZRESI-PC CORP\scanuser
rem ============================================================================================
setlocal EnableExtensions EnableDelayedExpansion

if "%~1"=="" (
    echo.
    echo   استفاده:  RUN-DIAG-EXTRACT.bat  ^<نام-کامپیوتر^>  [DOMAIN\user]
    echo   نمونه:    RUN-DIAG-EXTRACT.bat  BAZRESI-PC
    echo   نمونه:    RUN-DIAG-EXTRACT.bat  BAZRESI-PC  CORP\scanuser
    echo.
    exit /b 2
)

set "PHP_EXE="

rem ۱) PATH
for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"

rem ۲) پوشه‌های رایج نصب — XAMPP اول، چون این سامانه برای XAMPP نوشته شده
rem    و XAMPP پیش‌فرض PHP را به PATH اضافه نمی‌کند.
if not defined PHP_EXE if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if not defined PHP_EXE if exist "D:\xampp\php\php.exe" set "PHP_EXE=D:\xampp\php\php.exe"
if not defined PHP_EXE if exist "E:\xampp\php\php.exe" set "PHP_EXE=E:\xampp\php\php.exe"
if not defined PHP_EXE if exist "C:\wamp64\bin\php\php.exe" set "PHP_EXE=C:\wamp64\bin\php\php.exe"
if not defined PHP_EXE if exist "C:\wamp\bin\php\php.exe" set "PHP_EXE=C:\wamp\bin\php\php.exe"
if not defined PHP_EXE if exist "C:\PHP\php.exe" set "PHP_EXE=C:\PHP\php.exe"
if not defined PHP_EXE if exist "C:\php\php.exe" set "PHP_EXE=C:\php\php.exe"
if not defined PHP_EXE if exist "C:\Program Files\PHP\php.exe" set "PHP_EXE=C:\Program Files\PHP\php.exe"
if not defined PHP_EXE if exist "C:\Program Files (x86)\PHP\php.exe" set "PHP_EXE=C:\Program Files (x86)\PHP\php.exe"

rem ۲.ب) WAMP چند نسخه دارد: bin\php\php7.4.33\php.exe
if not defined PHP_EXE (
    for /d %%D in ("C:\wamp64\bin\php\*") do (
        if not defined PHP_EXE if exist "%%~fD\php.exe" set "PHP_EXE=%%~fD\php.exe"
    )
)

rem ۳) هر نسخهٔ نصب‌شده زیر Program Files\PHP
if not defined PHP_EXE (
    for /d %%D in ("C:\Program Files\PHP\*") do (
        if not defined PHP_EXE if exist "%%~fD\php.exe" set "PHP_EXE=%%~fD\php.exe"
    )
)

if not defined PHP_EXE (
    echo.
    echo   php.exe پیدا نشد — XAMPP نه در C: و نه در D: نصب نشده، یا مسیرش جای دیگری است.
    echo   مسیر xampp خود را ببینید:  dir C:\xampp\php\php.exe
    echo   ۱^) اگر PHP نصب است، مسیرش را دستی بدهید:
    echo        set "PHP_EXE=C:\Program Files\PHP\v8.2\php.exe"
    echo        "%%PHP_EXE%%"  tools\diag_extract.php  %*
    echo   ۲^) یا در IIS Manager ^(IIS ^> PHP Manager^) مسیر PHP را ببینید.
    echo.
    exit /b 3
)

echo   php.exe: "!PHP_EXE!"
echo.
"!PHP_EXE!" "%~dp0diag_extract.php" %*

rem کد خروج را نگه دار تا در اسکریپت‌های دیگر قابل استفاده باشد.
endlocal & exit /b %ERRORLEVEL%
