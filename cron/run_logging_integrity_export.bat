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
if "%LOGGING_INTEGRITY_KEY%"=="" exit /b 2
if "%LOGGING_INTEGRITY_BACKUP_DIR%"=="" exit /b 2

rem Create yesterday's immutable base snapshot first.
"%PHP_EXE%" "%APP_ROOT%\cron\export_logging_integrity.php"
if errorlevel 1 exit /b %ERRORLEVEL%

rem Reconcile the previous seven dates for rows replayed after their original daily snapshot.
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$php=$env:PHP_EXE; $script=Join-Path $env:APP_ROOT 'cron/export_logging_integrity.php'; for($i=1; $i -le 7; $i++){ $d=(Get-Date).Date.AddDays(-$i).ToString('yyyy-MM-dd'); & $php $script $d --reconcile-only; if($LASTEXITCODE -ne 0){ exit $LASTEXITCODE } }; $backup=$env:LOGGING_INTEGRITY_BACKUP_DIR; $verify=Join-Path $env:APP_ROOT 'cron/verify_logging_integrity.php'; for($i=1; $i -le 7; $i++){ $d=(Get-Date).Date.AddDays(-$i).ToString('yyyy-MM-dd'); $base=Join-Path $backup ('logging-integrity-' + $d + '.jsonl'); if(Test-Path -LiteralPath $base){ & $php $verify $base; if($LASTEXITCODE -ne 0){ exit $LASTEXITCODE }; Get-ChildItem -LiteralPath $backup -Filter ('logging-integrity-' + $d + '.late-*.jsonl') -File | ForEach-Object { & $php $verify $_.FullName; if($LASTEXITCODE -ne 0){ exit $LASTEXITCODE } } } }"
exit /b %ERRORLEVEL%
