@echo off
setlocal
cd /d "%~dp0"

if not exist "food_ticket_config.json" (
  copy /y "config.example.json" "food_ticket_config.json" >nul
  if errorlevel 1 (
    echo Could not create food_ticket_config.json. Run install_prerequisites.bat first.
    pause
    exit /b 1
  )
  echo Fill in the administrator passwords, Access paths, and printer in food_ticket_config.json, then run this file again.
  pause
  exit /b 1
)

where dotnet >nul 2>nul
if errorlevel 1 (
  echo .NET 8 SDK was not found. Run install_prerequisites.bat first.
  pause
  exit /b 1
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "try { $c = Get-Content -Raw 'food_ticket_config.json' | ConvertFrom-Json; $a = @($c.admins | Where-Object { -not [string]::IsNullOrWhiteSpace($_.username) -and -not [string]::IsNullOrWhiteSpace($_.password) }); if ($a.Count -gt 0) { exit 0 } } catch {}; exit 1"
if errorlevel 1 (
  echo Set a non-empty password for at least one administrator in food_ticket_config.json before starting the service.
  pause
  exit /b 1
)

dotnet run --project "FoodTicketServer.csproj" --configuration Release --no-restore
if errorlevel 1 (
  echo The service stopped with an error. Review the messages above.
  pause
  exit /b 1
)
