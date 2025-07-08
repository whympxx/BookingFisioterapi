@echo off
echo ===============================================
echo    UNLOCK MASTER DATABASE
echo ===============================================
echo.

set MYSQL_DIR=C:\xampp\mysql\bin
set MASTER_HOST=localhost
set MASTER_PORT=3306

echo Membuka kunci tabel master database...

%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "UNLOCK TABLES;"

if %errorlevel% equ 0 (
    echo ✓ Master database berhasil di-unlock
    echo ✓ Replikasi sekarang aktif dan siap digunakan
) else (
    echo ✗ Gagal meng-unlock master database
)

echo.
echo ===============================================
echo    REPLIKASI DATABASE SIAP!
echo ===============================================
echo.
echo Master Database: %MASTER_HOST%:%MASTER_PORT%
echo Slave Database:  localhost:3307
echo.
echo Anda sekarang dapat:
echo 1. Menguji replikasi dengan menambah data di master
echo 2. Memeriksa status replikasi dengan monitoring script
echo 3. Menggunakan load balancing untuk aplikasi
echo.

pause
