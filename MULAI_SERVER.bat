@echo off
setlocal enabledelayedexpansion
title SERVER SISTEM INFORMASI PROLANIS PUSKESMAS KALIJUDAN
color 0b

echo ===============================================================================
echo            SERVER SISTEM INFORMASI PROLANIS - PUSKESMAS KALIJUDAN
echo ===============================================================================
echo.

:: 1. Deteksi dan Pastikan Database MySQL Menyala
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | findstr /I "mysqld.exe" >nul
if errorlevel 1 (
    echo [INFO] Menyalakan Database MySQL otomatis...
    if exist "C:\laragon\laragon.exe" (
        start "" "C:\laragon\laragon.exe"
        timeout /t 3 /nobreak >nul
    )
)

:: 2. Deteksi PHP Executable (utamakan Laragon jika ada, atau fallback ke sistem)
set "PHP_BIN=php"
if exist "C:\laragon\bin\php" (
    for /f "delims=" %%I in ('dir /b /ad /o-n "C:\laragon\bin\php\php-*" 2^>nul') do (
        if not defined PHP_DIR (
            set "PHP_DIR=%%I"
            if exist "C:\laragon\bin\php\%%I\php.exe" (
                set "PHP_BIN=C:\laragon\bin\php\%%I\php.exe"
            )
        )
    )
)

:: 2. Deteksi Alamat IP Lokal Komputer Ini
set "LOCAL_IP=127.0.0.1"
for /f "tokens=4" %%a in ('route print 0.0.0.0 ^| findstr "\<0.0.0.0\>" 2^>nul') do (
    if not "%%a"=="" set "LOCAL_IP=%%a"
)

echo [STATUS] Server Web Prolanis sedang aktif...
echo.
echo -------------------------------------------------------------------------------
echo  PETUNJUK AKSES UNTUK PETUGAS PUSKESMAS:
echo -------------------------------------------------------------------------------
echo  1. Akses dari komputer INI (Server):
echo     Buka browser:  http://localhost:8000
echo.
echo  2. Akses dari komputer LAIN (Dokter / Apotek / Pendaftaran via LAN):
echo     Buka browser:  http://!LOCAL_IP!:8000
echo -------------------------------------------------------------------------------
echo.
echo  PENTING:
echo  * Jangan tutup jendela hitam ini selama jam pelayanan berlangsung!
echo  * Cukup klik MINIMIZE (-) agar tetap berjalan di latar belakang.
echo ===============================================================================
echo.

cd /d "%~dp0"
"%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8000

pause

