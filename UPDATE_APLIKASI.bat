@echo off
setlocal enabledelayedexpansion
title UPDATE SISTEM PROLANIS DARI GITHUB
color 0b

echo ===============================================================================
echo            MEMPERBARUI SISTEM INFORMASI PROLANIS DARI GITHUB
echo ===============================================================================
echo.

cd /d "%~dp0"

:: 1. Deteksi Git Executable (cek PATH sistem, jika tidak ada cari di Laragon / Program Files)
set "GIT_BIN=git"
where git >nul 2>&1
if errorlevel 1 (
    if exist "C:\laragon\bin\git\cmd\git.exe" (
        set "GIT_BIN=C:\laragon\bin\git\cmd\git.exe"
    ) else if exist "C:\laragon\bin\git\bin\git.exe" (
        set "GIT_BIN=C:\laragon\bin\git\bin\git.exe"
    ) else if exist "C:\Program Files\Git\cmd\git.exe" (
        set "GIT_BIN=C:\Program Files\Git\cmd\git.exe"
    ) else if exist "%LOCALAPPDATA%\Programs\Git\cmd\git.exe" (
        set "GIT_BIN=%LOCALAPPDATA%\Programs\Git\cmd\git.exe"
    ) else (
        echo [GAGAL] Git tidak ditemukan di komputer ini!
        echo Pastikan Laragon sudah terpasang di C:\laragon atau install Git dari https://git-scm.com
        echo.
        pause
        exit /b 1
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

echo [1/3] Mengambil pembaruan kode terbaru dari GitHub...
"%GIT_BIN%" fetch origin main
if errorlevel 1 (
    echo.
    echo [GAGAL] Tidak dapat terhubung ke GitHub!
    echo Pastikan komputer ini terhubung ke jaringan internet.
    echo.
    pause
    exit /b 1
)

:: Paksa sinkronisasi bersih ke origin/main agar tidak terhalang bentrok file lokal
"%GIT_BIN%" reset --hard origin/main
if errorlevel 1 (
    echo.
    echo [INFO] Mencoba sinkronisasi cadangan via git pull...
    "%GIT_BIN%" pull origin main
)

echo.
echo [2/3] Memeriksa pembaruan struktur database...
"%PHP_BIN%" artisan migrate --force

echo.
echo [3/3] Membersihkan cache aplikasi...
"%PHP_BIN%" artisan optimize:clear

echo.
echo ===============================================================================
echo   PEMBARUAN BERHASIL SELESAI!
echo   Silakan refresh (F5) halaman browser untuk melihat fitur / perbaikan terbaru.
echo ===============================================================================
echo.
pause
