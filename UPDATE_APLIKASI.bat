@echo off
title UPDATE SISTEM PROLANIS DARI GITHUB
color 0b

echo ===============================================================================
echo            MEMPERBARUI SISTEM INFORMASI PROLANIS DARI GITHUB
echo ===============================================================================
echo.

cd /d "%~dp0"

echo [1/3] Mengambil pembaruan kode terbaru dari GitHub...
git pull
if errorlevel 1 (
    echo.
    echo [GAGAL] Tidak dapat terhubung ke GitHub!
    echo Pastikan komputer ini terhubung ke jaringan internet.
    echo.
    pause
    exit /b
)

echo.
echo [2/3] Memeriksa pembaruan struktur database...
php artisan migrate --force

echo.
echo [3/3] Membersihkan cache aplikasi...
php artisan optimize:clear

echo.
echo ===============================================================================
echo   PEMBARUAN BERHASIL SELESAI!
echo   Silakan refresh (F5) halaman browser untuk melihat fitur / perbaikan terbaru.
echo ===============================================================================
echo.
pause

