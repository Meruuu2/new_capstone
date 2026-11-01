@echo off
setlocal
cd /d "%~dp0\.."

echo UA FMS — Electron launchers (replaces pywebview on Windows)
echo.

where node >nul 2>&1
if errorlevel 1 (
  echo [ERROR] Node.js is not on PATH. Install from https://nodejs.org/
  goto :end
)

cd launchers\electron

if not exist package.json (
  echo [ERROR] launchers\electron\package.json not found.
  goto :end
)

call npm install
if errorlevel 1 (
  echo [ERROR] npm install failed.
  goto :end
)

echo Building portable .exe files (Admin, College, Org^)...
call npm run dist:all
if errorlevel 1 (
  echo [ERROR] electron-builder failed.
  goto :end
)

echo.
echo Done. Portable executables are in:
echo   launchers\electron\dist-electron\
echo.

:end
pause
endlocal
