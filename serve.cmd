@echo off
REM ===========================================================================
REM  Start the local dev server so it can accept file uploads.
REM
REM  Why this exists
REM  ---------------
REM  `php artisan serve` launches the built-in server through Symfony Process and
REM  rebuilds the child environment from $_ENV, forwarding only a fixed allowlist
REM  (Illuminate\Foundation\Console\ServeCommand::$passthroughVariables). That
REM  list contains no TMP or TEMP - and $_ENV is empty in CLI PHP anyway, since
REM  variables_order defaults to "GPCS".
REM
REM  With no TMP/TEMP, PHP on Windows falls back to sys_get_temp_dir(), which
REM  resolved to C:\WINDOWS - not writable. Every upload then failed with
REM  "unable to create a temporary file", product images included.
REM
REM  This runs the same built-in server directly and pins upload_tmp_dir, which
REM  bypasses the environment pass-through entirely.
REM
REM  Usage:  serve.cmd         (port 8000)
REM          serve.cmd 8080    (custom port)
REM
REM  Note: `composer run dev` still calls `php artisan serve`, so uploads will
REM  fail there too. Use this script, or add
REM      upload_tmp_dir = "C:\Users\<you>\AppData\Local\Temp"
REM  to php.ini if you would rather keep using artisan serve.
REM ===========================================================================

setlocal

if "%~1"=="" (set "PORT=8000") else (set "PORT=%~1")

set "TMP=%LOCALAPPDATA%\Temp"
set "TEMP=%LOCALAPPDATA%\Temp"

cd /d "%~dp0public"

echo Starting Shivayra on http://127.0.0.1:%PORT%  (uploads enabled)
php -d upload_tmp_dir="%LOCALAPPDATA%\Temp" -S 127.0.0.1:%PORT% "%~dp0vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"
