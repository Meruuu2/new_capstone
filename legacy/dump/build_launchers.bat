@echo off
setlocal

echo Building UA Facility Management System Launchers (PyInstaller + pywebview^)...
echo.

REM Repository root (parent of dump/)
cd /d "%~dp0\.."

REM Go into launchers folder
cd launchers

REM Check that Python is available
python --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Python is not installed or not on PATH.
    echo Please install Python from https://www.python.org/ and try again.
    goto :end
)

REM Check that PyInstaller is available (via python -m)
python -m PyInstaller --version >nul 2>&1
if errorlevel 1 (
    echo [INFO] PyInstaller not found. Installing...
    python -m pip install pyinstaller
    if errorlevel 1 (
        echo [ERROR] Failed to install PyInstaller.
        goto :end
    )
)

REM Check that pywebview is available
python -c "import webview" >nul 2>&1
if errorlevel 1 (
    echo [INFO] pywebview not found. Installing...
    python -m pip install pywebview
    if errorlevel 1 (
        echo [ERROR] Failed to install pywebview.
        goto :end
    )
)

echo Building Admin Launcher...
python -m PyInstaller --onefile --name "UA-FMS-Admin-Portal" admin_launcher.py
if errorlevel 1 (
    echo [ERROR] Failed to build Admin launcher.
    goto :end
)
echo.

echo Building College Staff Launcher...
python -m PyInstaller --onefile --name "UA-FMS-College-Portal" college_launcher.py
if errorlevel 1 (
    echo [ERROR] Failed to build College Staff launcher.
    goto :end
)
echo.

echo Building Organization Staff Launcher...
python -m PyInstaller --onefile --name "UA-FMS-Org-Portal" org_launcher.py
if errorlevel 1 (
    echo [ERROR] Failed to build Organization Staff launcher.
    goto :end
)
echo.

echo Build Complete!
echo.
echo Launchers created in launchers\dist\ folder:
echo - UA-FMS-Admin-Portal.exe
echo - UA-FMS-College-Portal.exe
echo - UA-FMS-Org-Portal.exe
echo.

:end
pause
endlocal
