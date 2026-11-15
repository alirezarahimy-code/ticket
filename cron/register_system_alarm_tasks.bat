@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion
title ثبت کرون دیده‌بانی سلامت — Ticket

rem ═══════════════════════════════════════════════════════════════════
rem  ثبت خودکار وظایف زمان‌بندی‌شدهٔ «آژیر سلامت»
rem
rem  چرا این فایل: هشدار «دیده‌بانی خودکار سلامت به‌روز نیست» می‌گفت
rem  کرون ثبت نشده و کاربر باید دستی schtasks اجرا می‌کرد. این اسکریپت
rem  همان کار را با یک کلیک انجام می‌دهد.
rem
rem  اجرا: روی این فایل دوبار کلیک کنید، یا در CMD با دسترسی Administrator:
rem        cron\register_system_alarm_tasks.bat
rem ═══════════════════════════════════════════════════════════════════

set "APP_ROOT=%~dp0.."
for %%I in ("%APP_ROOT%") do set "APP_ROOT=%%~fI"

echo.
echo ─── ثبت کرون آژیر سلامت ───
echo پوشهٔ برنامه: %APP_ROOT%
echo.

rem ── ۱) پیدا کردن PHP ────────────────────────────────────────────
if not defined PHP_EXE (
    if exist "C:\PHP\php.exe"       set "PHP_EXE=C:\PHP\php.exe"
    if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
    if exist "D:\xampp\php\php.exe" set "PHP_EXE=D:\xampp\php\php.exe"
    if exist "C:\php\php.exe"       set "PHP_EXE=C:\php\php.exe"
)
if not defined PHP_EXE (
    for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
)
if not defined PHP_EXE (
    echo [خطا] php.exe پیدا نشد.
    echo        مسیر آن را دستی تنظیم کنید و دوباره اجرا کنید:
    echo            set PHP_EXE=C:\مسیر\php.exe
    echo            cron\register_system_alarm_tasks.bat
    echo.
    pause
    exit /b 2
)
echo PHP: !PHP_EXE!
echo.

rem ── ۲) بررسی وجود اسکریپت‌های کرون ──────────────────────────────
if not exist "%APP_ROOT%\cron\system_alarm_tick.php" (
    echo [خطا] فایل cron\system_alarm_tick.php پیدا نشد.
    echo        این اسکریپت باید در ریشهٔ نصب تیکت اجرا شود.
    pause
    exit /b 3
)

rem ── ۳) حذف ثبت قبلی (تا دوباره اجرا شدن مشکل ایجاد نکند) ─────────
schtasks /query /tn "TicketSystemAlarmTick"    >nul 2>&1 && (
    schtasks /delete /tn "TicketSystemAlarmTick" /f >nul 2>&1
)
schtasks /query /tn "TicketSystemAlarmMorning" >nul 2>&1 && (
    schtasks /delete /tn "TicketSystemAlarmMorning" /f >nul 2>&1
)

rem ── ۴) ثبت tick هر ۵ دقیقه ──────────────────────────────────────
echo [۱/۲] ثبت tick هر ۵ دقیقه…
schtasks /create ^
  /tn "TicketSystemAlarmTick" ^
  /tr "\"!PHP_EXE!\" \"%APP_ROOT%\cron\system_alarm_tick.php\"" ^
  /sc minute /mo 5 ^
  /ru "SYSTEM" ^
  /rl HIGHEST ^
  /f >nul 2>&1

if errorlevel 1 (
    echo        با کاربر SYSTEM نشد؛ با کاربر جاری تلاش می‌شود…
    schtasks /create ^
      /tn "TicketSystemAlarmTick" ^
      /tr "\"!PHP_EXE!\" \"%APP_ROOT%\cron\system_alarm_tick.php\"" ^
      /sc minute /mo 5 ^
      /rl HIGHEST ^
      /f
)

rem ── ۵) ثبت پیش‌چک ۰۸:۰۰ ─────────────────────────────────────────
echo [۲/۲] ثبت پیش‌چک روزانه ۰۸:۰۰…
schtasks /create ^
  /tn "TicketSystemAlarmMorning" ^
  /tr "\"!PHP_EXE!\" \"%APP_ROOT%\cron\system_alarm_tick.php\" --morning" ^
  /sc daily /st 08:00 ^
  /ru "SYSTEM" ^
  /rl HIGHEST ^
  /f >nul 2>&1

if errorlevel 1 (
    schtasks /create ^
      /tn "TicketSystemAlarmMorning" ^
      /tr "\"!PHP_EXE!\" \"%APP_ROOT%\cron\system_alarm_tick.php\" --morning" ^
      /sc daily /st 08:00 ^
      /rl HIGHEST ^
      /f
)

rem ── ۶) اجرای فوری، تا هشدار همین حالا برطرف شود ──────────────────
echo.
echo اجرای فوری یک بار (تا هشدار پاک شود)…
"!PHP_EXE!" "%APP_ROOT%\cron\system_alarm_tick.php" >nul 2>&1
if errorlevel 1 (
    echo        اجرای فوری با خطا تمام شد — جزئیات در storage\logs
) else (
    echo        انجام شد.
)

rem ── ۷) گزارش ─────────────────────────────────────────────────────
echo.
echo ─── وضعیت ───
schtasks /query /tn "TicketSystemAlarmTick"    /fo LIST 2>nul | findstr /i "TaskName Status Next"
schtasks /query /tn "TicketSystemAlarmMorning" /fo LIST 2>nul | findstr /i "TaskName Status Next"
echo.
echo تمام شد. صفحهٔ «سلامت سامانه» را تازه‌سازی کنید؛ هشدار باید رفته باشد.
echo اگر نرفت، در Task Scheduler وظیفه‌های TicketSystemAlarm* را بررسی کنید.
echo.
pause
