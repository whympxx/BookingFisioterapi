@echo off
echo ===============================================
echo    KONFIGURASI MASTER DATABASE
echo ===============================================
echo.

set MYSQL_DIR=C:\xampp\mysql\bin
set MASTER_HOST=localhost
set MASTER_PORT=3306

echo Mengkonfigurasi master database untuk replikasi...

REM Cek apakah master sudah dikonfigurasi dengan server-id
echo Checking master configuration...

%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "SHOW VARIABLES LIKE 'server_id';"

if %errorlevel% neq 0 (
    echo ✗ Tidak dapat terhubung ke master database
    pause
    exit /b 1
)

echo.
echo Membuat user replikasi...

%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "CREATE USER IF NOT EXISTS 'replication_user'@'localhost' IDENTIFIED BY 'replication_password';"
%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "GRANT REPLICATION SLAVE ON *.* TO 'replication_user'@'localhost';"
%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "FLUSH PRIVILEGES;"

if %errorlevel% equ 0 (
    echo ✓ User replikasi berhasil dibuat
) else (
    echo ✗ Gagal membuat user replikasi
)

echo.
echo Mengaktifkan binary logging dan mendapatkan status master...

%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "FLUSH TABLES WITH READ LOCK;"
%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "SHOW MASTER STATUS;" > master_status.tmp

echo Current master status:
type master_status.tmp

echo.
echo ✓ Master database dikonfigurasi untuk replikasi

echo.
echo PENTING: 
echo 1. Catat MASTER_LOG_FILE dan MASTER_LOG_POS dari output di atas
echo 2. Sekarang jalankan script configure_slave.bat untuk mengkonfigurasi slave
echo 3. Setelah slave dikonfigurasi, jalankan: unlock_master.bat
echo.

echo Master status disimpan dalam file: master_status.tmp
echo.

pause
