@echo off
rem ============================================================================================
rem  GET-DIAG-EXTRACT.bat — دریافت tools\diag_extract.php بدون دانلود دستی
rem
rem  چرا این فایل: فایل تشخیصی در نصب ۱.۶۳ وجود ندارد و فقط داخل بستهٔ
rem  به‌روزرسانی است. اگر بسته را دانلود و از حالت فشرده درآورده‌اید، این
rem  فایل را کنار پوشهٔ tools سامانه بگذارید و اجرا کنید؛ خودش فایل را از
rem  همان بستهٔ کنارش برمی‌دارد. اگر بسته را ندارید، آدرس دانلود را چاپ
rem  می‌کند.
rem
rem  استفاده:  GET-DIAG-EXTRACT.bat  [مسیر-پوشهٔ-سامانه]
rem ============================================================================================
setlocal EnableExtensions EnableDelayedExpansion

rem پوشهٔ سامانه: آرگومان اول، یا پوشهٔ والدِ همین فایل
set "APP_DIR=%~1"
if not defined APP_DIR set "APP_DIR=%~dp0.."
for %%I in ("%APP_DIR%") do set "APP_DIR=%%~fI"

echo.
echo   پوشهٔ سامانه: "!APP_DIR!"
echo.

if not exist "!APP_DIR!\index.php" (
    echo   ✗ در این پوشه index.php نیست. مطمئن شوید پوشهٔ درست را داده‌اید:
    echo       GET-DIAG-EXTRACT.bat  C:\xampp\htdocs\ticket
    echo.
    exit /b 2
)

if not exist "!APP_DIR!\tools" mkdir "!APP_DIR!\tools"

rem ۱) اگر فایل از قبل هست، کاری لازم نیست
if exist "!APP_DIR!\tools\diag_extract.php" (
    echo   ✓ tools\diag_extract.php از قبل هست — به‌روزرسانی نمی‌شود.
    echo     برای اجرا:
    echo       C:\xampp\php\php.exe tools\diag_extract.php BAZRESI-PC
    echo.
    exit /b 0
)

rem ۲) کنار همین فایل هست؟ (بستهٔ استخراج‌شده)
if exist "%~dp0diag_extract.php" (
    copy /Y "%~dp0diag_extract.php" "!APP_DIR!\tools\diag_extract.php" >nul
    echo   ✓ از پوشهٔ کنار همین فایل کپی شد.
    goto :done
)
if exist "%~dp0tools\diag_extract.php" (
    copy /Y "%~dp0tools\diag_extract.php" "!APP_DIR!\tools\diag_extract.php" >nul
    echo   ✓ از .\tools کنار همین فایل کپی شد.
    goto :done
)
if exist "%~dp0files\tools\diag_extract.php" (
    copy /Y "%~dp0files\tools\diag_extract.php" "!APP_DIR!\tools\diag_extract.php" >nul
    echo   ✓ از .\files\tools کنار همین فایل کپی شد (ساختار بستهٔ به‌روزرسانی).
    goto :done
)

echo   فایل پیدا نشد. دو راه:
echo.
echo   ۱^) همین فایل را داخل پوشهٔ استخراج‌شدهٔ بسته بگذارید و دوباره اجرا کنید.
echo.
echo   ۲^) دستی دانلود کنید ^(لینک مستقیم — با کلیک، مرورگر ذخیره‌اش می‌کند^):
echo        https://github.com/alirezarahimy-code/ticket/raw/publish-1.63.1/tools/diag_extract.php
echo.
echo      سپس فایل را در این مسیر بگذارید:
echo        "!APP_DIR!\tools\diag_extract.php"
echo.
exit /b 3

:done
echo.
echo   ✓ آماده است. حالا اجرا کنید:
echo       C:\xampp\php\php.exe tools\diag_extract.php BAZRESI-PC
echo.
endlocal & exit /b 0
