@echo off
setlocal
cd /d "%~dp0"

where dotnet >nul 2>nul
if errorlevel 1 (
  echo .NET 8 SDK was not found. Install it from https://dotnet.microsoft.com/download/dotnet/8.0
  pause
  exit /b 1
)

dotnet --list-sdks | findstr /r "^8\." >nul
if errorlevel 1 (
  echo .NET 8 SDK is required. Install it from https://dotnet.microsoft.com/download/dotnet/8.0
  pause
  exit /b 1
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "$d = Get-OdbcDriver -ErrorAction SilentlyContinue | Where-Object { $_.Name -eq 'Microsoft Access Driver (*.mdb, *.accdb)' }; if (-not $d) { Write-Output 'WARNING: Microsoft Access ODBC driver was not found. Install the matching Access Database Engine before using Access files.' } else { Write-Output ('Access ODBC driver found: ' + (($d | ForEach-Object Name) -join ', ')) }"

if not exist "food_ticket_config.json" (
  copy /y "config.example.json" "food_ticket_config.json" >nul
  if errorlevel 1 (
    echo Could not create food_ticket_config.json from the sample.
    pause
    exit /b 1
  )
  echo Created food_ticket_config.json. Fill in the administrator passwords, Access paths, and printer before starting.
)

dotnet restore "FoodTicketServer.csproj"
if errorlevel 1 (
  echo Restore failed. Check internet access and the .NET SDK installation.
  pause
  exit /b 1
)

echo Prerequisites checked and project packages restored.
pause
exit /b 0
