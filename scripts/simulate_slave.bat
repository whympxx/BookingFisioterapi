@echo off
setlocal enabledelayedexpansion

echo ===============================================
echo    SIMULASI SLAVE DATABASE
echo ===============================================
echo.

set MYSQL_DIR=C:\xampp\mysql\bin
set MASTER_HOST=localhost
set MASTER_PORT=3306

echo Membuat simulasi slave database...
echo (Menggunakan database terpisah dalam instance yang sama)

echo.
echo Membuat database slave_fisioterapi...

%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "DROP DATABASE IF EXISTS slave_fisioterapi;"
%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "CREATE DATABASE slave_fisioterapi;"

if %errorlevel% equ 0 (
    echo ✓ Database slave_fisioterapi berhasil dibuat
) else (
    echo ✗ Gagal membuat database slave_fisioterapi
    pause
    exit /b 1
)

echo.
echo Mengimpor data dari backup...

if not exist "slave_replica.sql" (
    echo ✗ File slave_replica.sql tidak ditemukan
    echo Jalankan simple_slave_setup.bat terlebih dahulu
    pause
    exit /b 1
)

%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root slave_fisioterapi < slave_replica.sql

if %errorlevel% equ 0 (
    echo ✓ Data berhasil diimpor ke slave_fisioterapi
) else (
    echo ✗ Gagal mengimpor data ke slave_fisioterapi
    pause
    exit /b 1
)

echo.
echo Membuat user untuk akses slave...

%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "CREATE USER IF NOT EXISTS 'slave_user'@'localhost' IDENTIFIED BY 'slave_password';"
%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "GRANT SELECT ON slave_fisioterapi.* TO 'slave_user'@'localhost';"
%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root -e "FLUSH PRIVILEGES;"

echo ✓ User slave_user berhasil dibuat

echo.
echo Testing koneksi ke slave database...

%MYSQL_DIR%\mysql.exe -h %MASTER_HOST% -P %MASTER_PORT% -u slave_user -plave_password -e "USE slave_fisioterapi; SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema='slave_fisioterapi';"

if %errorlevel% equ 0 (
    echo ✓ Slave database siap dan dapat diakses
) else (
    echo ✗ Gagal mengakses slave database
)

echo.
echo ===============================================
echo    SLAVE DATABASE SIMULATION READY
echo ===============================================
echo.
echo Master Database: db_fisioterapi (Read/Write)
echo Slave Database:  slave_fisioterapi (Read Only)
echo.
echo Untuk testing, gunakan:
echo - Master: mysql -u root db_fisioterapi
echo - Slave:  mysql -u slave_user -plave_password slave_fisioterapi
echo.

pause
