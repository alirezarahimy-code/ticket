@echo off
setlocal
set "SCRIPT=%~dp0Write-LoggingEmergencyEvent.ps1"
powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -File "%SCRIPT%" -AppRoot "%~dp0.."
exit /b %ERRORLEVEL%
