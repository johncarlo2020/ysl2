@echo off
:: ============================================================
::  Build RFID Relay launcher into a standalone .exe
::  Run this script once on your dev machine.
::  Requires Python 3.8+ installed and on PATH.
:: ============================================================

echo [1/3] Installing Python build dependencies...
pip install pyinstaller pystray pillow

echo.
echo [2/3] Building "RFID Relay.exe" ...
pyinstaller ^
  --onefile ^
  --noconsole ^
  --name "RFID Relay" ^
  --hidden-import=winreg ^
  --hidden-import=pystray ^
  --hidden-import=PIL ^
  --hidden-import=PIL.Image ^
  --hidden-import=PIL.ImageDraw ^
  launcher.py

echo.
echo [3/3] Done!
echo.
echo ============================================================
echo  Distribute these files together in ONE folder:
echo ============================================================
echo   dist\RFID Relay.exe   ^<-- the launcher (double-click to run)
echo   server.js
echo   package.json
echo   hub.js       (optional)
echo   relay.js     (optional)
echo.
echo  What the launcher installs automatically (first run):
echo   - Node.js LTS             (via Windows Package Manager)
echo   - VS Build Tools C++      (via Windows Package Manager, ~2 GB)
echo   - Smart Card service      (for ACR122U RFID reader)
echo   - npm packages            (nfc-pcsc and dependencies)
echo.
echo  NOTE: The user needs Windows 10/11 with winget available.
echo        The launcher will request Administrator rights on first run.
echo        The ACR122U USB driver is installed automatically by Windows
echo        when the reader is first plugged in (no extra driver needed).
echo ============================================================
pause
