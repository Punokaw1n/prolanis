@echo off
setlocal enabledelayedexpansion
title BERSIHKAN DATA PASIEN UJI COBA
color 0c

echo ===============================================================================
echo            PEMBERSIH DATA PASIEN UJI COBA - PUSKESMAS KALIJUDAN
echo ===============================================================================
echo.
echo PERINGATAN:
echo Tindakan ini akan MENGHAPUS SEMUA DATA PASIEN, RIWAYAT PEMERIKSAAN, DAN RESEP.
echo Akun login dokter, perawat, dan apoteker TETAP AMAN dan TIDAK AKAN TERHAPUS.
echo.
set /p CONFIRM=Apakah Anda yakin ingin mengosongkan data pasien uji coba? (Y/N): 
if /I not "!CONFIRM!"=="Y" (
    echo Operasi dibatalkan.
    pause
    exit /b
)

echo.
echo Sedang membersihkan data pasien uji coba...

:: Deteksi PHP Executable
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

cd /d "%~dp0"
"%PHP_BIN%" artisan tinker --execute "\Illuminate\Support\Facades\Schema::disableForeignKeyConstraints(); \App\Models\ObatPrb::truncate(); \App\Models\RiwayatPengambilanObatPrb::truncate(); \App\Models\PasienPrb::truncate(); \App\Models\PemberianObat::truncate(); \App\Models\Pemeriksaan::truncate(); \Illuminate\Support\Facades\DB::table('jadwal_layanan_pasien')->truncate(); \Illuminate\Support\Facades\DB::table('persetujuan')->truncate(); \App\Models\Pasien::truncate(); \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints(); echo 'BERSIH_SUKSES';"

echo.
echo ===============================================================================
echo   BERHASIL! Data pasien dan riwayat pemeriksaan sudah BERSIH TOTAL (0 data).
echo   Sistem siap digunakan untuk melayani pasien asli Puskesmas!
echo ===============================================================================
echo.
pause
