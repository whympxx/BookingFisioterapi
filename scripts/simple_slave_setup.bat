@echo off
setlocal enabledelayedexpansion

echo ===============================================
echo    SIMPLE SLAVE DATABASE SETUP
echo ===============================================
echo.

set MYSQL_DIR=C:\xampp\mysql\bin
set MASTER_HOST=localhost
set MASTER_PORT=3306
set SLAVE_PORT=3307

echo Menyalin database dari master untuk replikasi...

REM Cek koneksi master
echo Testing koneksi ke master database...
%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "SELECT 'Master connected' AS status;"

if %errorlevel% neq 0 (
    echo ✗ Tidak dapat terhubung ke master database
    pause
    exit /b 1
)

echo ✓ Koneksi ke master berhasil

echo.
echo Mengkonfigurasi master untuk replikasi...

%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "CREATE USER IF NOT EXISTS 'replication_user'@'localhost' IDENTIFIED BY 'replication_password';"
%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "GRANT REPLICATION SLAVE ON *.* TO 'replication_user'@'localhost';"
%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "FLUSH PRIVILEGES;"

echo ✓ User replikasi dibuat

REM Buat backup database
echo.
echo Membuat backup database fisioterapi...
%MYSQL_DIR%\mysqldump.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root --single-transaction --routines --triggers --master-data=2 db_fisioterapi > slave_replica.sql

if %errorlevel% equ 0 (
    echo ✓ Backup database berhasil dibuat
) else (
    echo ✗ Gagal membuat backup database
    pause
    exit /b 1
)

echo.
echo ===============================================
echo    SETUP SLAVE INSTANCE MANUAL
echo ===============================================
echo.
echo LANGKAH SELANJUTNYA:
echo 1. Buka XAMPP Control Panel
echo 2. Stop MySQL service yang sedang berjalan
echo 3. Edit file C:\xampp\mysql\bin\my.ini
echo 4. Tambahkan konfigurasi berikut di bagian [mysqld]:
echo.
echo    # Master Configuration
echo    server-id = 1
echo    log-bin = mysql-bin
echo    binlog-format = ROW
echo.
echo 5. Restart MySQL service
echo 6. Jalankan script test_replication.bat untuk menguji
echo.
echo File backup slave_replica.sql sudah siap untuk import
echo.

echo Tekan Enter untuk melanjutkan...
pause >nul
