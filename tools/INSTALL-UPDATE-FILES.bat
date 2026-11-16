@echo off
setlocal EnableExtensions EnableDelayedExpansion
rem ============================================================================================
rem  INSTALL-UPDATE-FILES.bat — نصب مطمئن فایل‌های بستهٔ به‌روزرسانی
rem
rem  چرا: کاربر ۷۱ فایل بسته را داشت ولی tools\collect_via_smb.ps1 روی سرورش
rem  نبود — و بی‌آن، «روش دوم SMB» برای همهٔ کلاینت‌ها بی‌صدا شکست می‌خورد.
rem  این اسکریپت همهٔ فایل‌های اجرایی بسته را در جای درستشان می‌گذارد و
rem  بعد بررسی می‌کند که هر کدام واقعاً سر جایشان هستند.
rem
rem  استفاده:  این فایل را داخل پوشهٔ استخراج‌شدهٔ بسته بگذارید و اجرا کنید
rem            INSTALL-UPDATE-FILES.bat C:\xampp\htdocs\ticket
rem ============================================================================================

set "APP_DIR=%~1"
if not defined APP_DIR (
    echo.
    echo   استفاده:  INSTALL-UPDATE-FILES.bat  ^<مسیر-پوشهٔ-سامانه^>
    echo   نمونه:    INSTALL-UPDATE-FILES.bat  C:\xampp\htdocs\ticket
    echo.
    exit /b 2
)
for %%I in ("%APP_DIR%") do set "APP_DIR=%%~fI"

echo.
echo   پوشهٔ سامانه: "!APP_DIR!"
echo.

if not exist "!APP_DIR!\index.php" (
    echo   ✗ در این پوشه index.php نیست — پوشهٔ درست را بدهید.
    exit /b 2
)

rem پوشهٔ فایل‌های بسته: کنار همین فایل، یا ./files
set "SRC="
if exist "%~dp0files\tools\collect_via_smb.ps1" set "SRC=%~dp0files"
if not defined SRC if exist "%~dp0tools\collect_via_smb.ps1" set "SRC=%~dp0"

if not defined SRC (
    echo   ✗ پوشهٔ فایل‌های بسته پیدا نشد.
    echo     این فایل را داخل پوشهٔ استخراج‌شدهٔ بسته بگذارید و دوباره اجرا کنید.
    echo     باید این مسیر باشد:  ^<پوشهٔ-بسته^>\files\tools\collect_via_smb.ps1
    exit /b 3
)

echo   منبع: "!SRC!"
echo.

set "COPY_FAIL=0"
for %%F in (tools\collect_via_smb.ps1 tools\collect_server_inventory.ps1 tools\diag_extract.php) do (
    if exist "!SRC!\%%F" (
        if not exist "!APP_DIR!\tools" mkdir "!APP_DIR!\tools" >nul 2>&1
        copy /Y "!SRC!\%%F" "!APP_DIR!\%%F" >nul 2>&1
        if exist "!APP_DIR!\%%F" (
            echo   ✓ %%~nxF
        ) else (
            echo   ✗ %%~nxF  — کپی نشد
            set "COPY_FAIL=1"
        )
    ) else (
        echo   ! %%~nxF  — در بسته نیست، رد شد
    )
)

rem فایل‌های PHP اصلی سامانه
if exist "!SRC!\domain-scan.php" (
    copy /Y "!SRC!\domain-scan.php" "!APP_DIR!\domain-scan.php" >nul 2>&1
    if exist "!APP_DIR!\domain-scan.php" (echo   ✓ domain-scan.php) else (echo   ✗ domain-scan.php & set "COPY_FAIL=1")
)

echo.
echo   ── بررسی نهایی ──
set "MISSING=0"
for %%F in (tools\collect_via_smb.ps1 tools\collect_server_inventory.ps1 tools\diag_extract.php domain-scan.php) do (
    if not exist "!APP_DIR!\%%F" (
        echo   ✗ نبود: %%F
        set "MISSING=1"
    )
)
if "!MISSING!"=="0" (
    echo   ✓ همهٔ فایل‌های لازم سر جایشان هستند.
) else (
    echo   ⚠ فایل‌های بالا را دستی از بسته کپی کنید.
)

echo.
echo   حالا ابزار تشخیص را اجرا کنید:
echo       C:\xampp\php\php.exe tools\diag_extract.php BAZRESI-PC
echo.
endlocal & exit /b 0
