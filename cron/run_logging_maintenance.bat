@echo off
setlocal
set "APP_ROOT=%~dp0.."
for %%I in ("%APP_ROOT%") do set "APP_ROOT=%%~fI"

if not defined PHP_EXE set "PHP_EXE=C:\PHP\php.exe"
rem 1.62.88: XAMPP most often installs PHP outside C:\PHP and outside PATH.
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

rem 1.62.88: this helper is optional; a missing file must not fail the whole task.
set "UPDATE_AUDIT_EXIT=0"
if exist "%APP_ROOT%\cron\reconcile_update_audit.php" (
    "%PHP_EXE%" "%APP_ROOT%\cron\reconcile_update_audit.php" >> "%APP_ROOT%\storage\logs\update-audit-reconcile-task.log" 2>&1
    set "UPDATE_AUDIT_EXIT=%ERRORLEVEL%"
)
"%PHP_EXE%" "%APP_ROOT%\cron\replay_logging_spool.php" >> "%APP_ROOT%\storage\logs\logging-worker-task.log" 2>&1
set "REPLAY_EXIT=%ERRORLEVEL%"
"%PHP_EXE%" "%APP_ROOT%\cron\rebuild_logging_issues.php" 500 >> "%APP_ROOT%\storage\logs\logging-issue-rebuild-task.log" 2>&1
set "REBUILD_EXIT=%ERRORLEVEL%"
"%PHP_EXE%" "%APP_ROOT%\cron\deliver_logging_issue_notifications.php" 100 >> "%APP_ROOT%\storage\logs\logging-notification-task.log" 2>&1
set "NOTIFY_EXIT=%ERRORLEVEL%"
"%PHP_EXE%" "%APP_ROOT%\cron\check_logging_health.php" >> "%APP_ROOT%\storage\logs\logging-health-task.log" 2>&1
set "HEALTH_EXIT=%ERRORLEVEL%"

if not "%REPLAY_EXIT%"=="0" exit /b %REPLAY_EXIT%
if not "%REBUILD_EXIT%"=="0" exit /b %REBUILD_EXIT%
if not "%NOTIFY_EXIT%"=="0" exit /b %NOTIFY_EXIT%
if not "%UPDATE_AUDIT_EXIT%"=="0" exit /b %UPDATE_AUDIT_EXIT%
exit /b %HEALTH_EXIT%
