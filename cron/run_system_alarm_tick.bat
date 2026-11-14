@echo off
rem آژیر سلامت — tick هر ۵ دقیقه (Task Scheduler: هر ۵ دقیقه، حتی اگر کاربر وارد نشده باشد)
setlocal
set "APP_ROOT=%~dp0.."
for %%I in ("%APP_ROOT%") do set "APP_ROOT=%%~fI"

if not defined PHP_EXE set "PHP_EXE=C:\PHP\php.exe"
if not exist "%PHP_EXE%" if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if not exist "%PHP_EXE%" if exist "D:\xampp\php\php.exe" set "PHP_EXE=D:\xampp\php\php.exe"
if not exist "%PHP_EXE%" (
    for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE_FOUND set "PHP_EXE_FOUND=%%P"
    if defined PHP_EXE_FOUND set "PHP_EXE=%PHP_EXE_FOUND%"
)
if not exist "%PHP_EXE%" (
    echo PHP executable not found. Set PHP_EXE environment variable.
    exit /b 2
)

if not exist "%APP_ROOT%\storage\logs" mkdir "%APP_ROOT%\storage\logs"

"%PHP_EXE%" "%APP_ROOT%\cron\system_alarm_tick.php" >> "%APP_ROOT%\storage\logs\system-alarm-task.log" 2>&1
exit /b %ERRORLEVEL%
