@echo off
setlocal
set "APP_ROOT=%~dp0.."
for %%I in ("%APP_ROOT%") do set "APP_ROOT=%%~fI"
if not defined PHP_EXE set "PHP_EXE=C:\PHP\php.exe"
if not exist "%PHP_EXE%" (
    for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE_FOUND set "PHP_EXE_FOUND=%%P"
    if defined PHP_EXE_FOUND set "PHP_EXE=%PHP_EXE_FOUND%"
)
if not exist "%PHP_EXE%" exit /b 2
"%PHP_EXE%" "%APP_ROOT%\cron\prune_activity_logs.php" >> "%APP_ROOT%\storage\logs\logging-retention-task.log" 2>&1
exit /b %ERRORLEVEL%
