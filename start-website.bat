@echo off
setlocal

set "ROOT=%~dp0"
set "PHP=C:\xampp\php\php.exe"
set "URL=http://localhost:8000/index.php?page=courses"

if not exist "%PHP%" (
    set "PHP=php"
)

echo Starting CENTEXS ITOP Management System...
echo Root: %ROOT%

title CENTEXS ITOP Management System
start "ITOP PHP Server" /B "%PHP%" -S localhost:8000 -t "%ROOT%"

ping 127.0.0.1 -n 2 >nul
start "" "%URL%"

echo.
echo The website should open in your browser.
echo Server URL: %URL%
echo Close this window or press Ctrl+C in the server window to stop the site.
