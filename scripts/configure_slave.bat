@echo off
setlocal enabledelayedexpansion

echo ===============================================
echo    KONFIGURASI SLAVE DATABASE
echo ===============================================
echo.

set MYSQL_DIR=C:\xampp\mysql\bin
set MASTER_HOST=localhost
set MASTER_PORT=3306
set SLAVE_HOST=localhost
set SLAVE_PORT=3307

echo Mengkonfigurasi slave database untuk replikasi...

REM Cek koneksi ke slave
echo Testing koneksi ke slave database...
%MYSQL_DIR%\mysql.exe -h %SLAVE_HOST% -P %SLAVE_PORT% -u root -e "SELECT 'Slave connected' AS status;"

if %errorlevel% neq 0 (
    echo ✗ Tidak dapat terhubung ke slave database
    echo Pastikan slave database sudah berjalan di port %SLAVE_PORT%
    pause
    exit /b 1
)

echo ✓ Koneksi ke slave database berhasil

echo.
echo Membuat database db_fisioterapi di slave...

%MYSQL_DIR%\mysql.exe -h %SLAVE_HOST% -P %SLAVE_PORT% -u root -e "CREATE DATABASE IF NOT EXISTS db_fisioterapi;"

if %errorlevel% equ 0 (
    echo ✓ Database db_fisioterapi dibuat di slave
) else (
    echo ✗ Gagal membuat database di slave
)

echo.
echo Mengambil dump dari master database...

%MYSQL_DIR%\mysqldump.exe -h %MASTER_HOST% -P %MASTER_PORT% -u root --single-transaction --routines --triggers db_fisioterapi > slave_initial_data.sql

if %errorlevel% equ 0 (
    echo ✓ Dump master database berhasil dibuat
) else (
    echo ✗ Gagal membuat dump master database
    pause
    exit /b 1
)

echo.
echo Mengimpor data ke slave database...

%MYSQL_DIR%\mysql.exe -h %SLAVE_HOST% -P %SLAVE_PORT% -u root db_fisioterapi < slave_initial_data.sql

if %errorlevel% equ 0 (
    echo ✓ Data berhasil diimpor ke slave
) else (
    echo ✗ Gagal mengimpor data ke slave
    pause
    exit /b 1
)

echo.
echo Membaca master status...

if not exist "master_status.tmp" (
    echo ✗ File master_status.tmp tidak ditemukan
    echo Jalankan configure_master.bat terlebih dahulu
    pause
    exit /b 1
)

REM Baca master log file dan position (simplified - dalam praktik nyata perlu parsing yang lebih robust)
echo Pastikan Anda mencatat MASTER_LOG_FILE dan MASTER_LOG_POS dari configure_master.bat
echo.

set /p MASTER_LOG_FILE="Masukkan MASTER_LOG_FILE (contoh: mysql-bin.000001): "
set /p MASTER_LOG_POS="Masukkan MASTER_LOG_POS (contoh: 154): "

if "%MASTER_LOG_FILE%"=="" (
    set MASTER_LOG_FILE=mysql-bin.000001
)

if "%MASTER_LOG_POS%"=="" (
    set MASTER_LOG_POS=154
)

echo.
echo Mengkonfigurasi replikasi slave...
echo Master Log File: %MASTER_LOG_FILE%
echo Master Log Pos: %MASTER_LOG_POS%

%MYSQL_DIR%\mysql.exe -h %SLAVE_HOST% -P %SLAVE_PORT% -u root -e "STOP SLAVE;"

%MYSQL_DIR%\mysql.exe -h %SLAVE_HOST% -P %SLAVE_PORT% -u root -e "CHANGE MASTER TO MASTER_HOST='%MASTER_HOST%', MASTER_PORT=%MASTER_PORT%, MASTER_USER='replication_user', MASTER_PASSWORD='replication_password', MASTER_LOG_FILE='%MASTER_LOG_FILE%', MASTER_LOG_POS=%MASTER_LOG_POS%;"

%MYSQL_DIR%\mysql.exe -h %SLAVE_HOST% -P %SLAVE_PORT% -u root -e "START SLAVE;"

if %errorlevel% equ 0 (
    echo ✓ Slave replikasi berhasil dikonfigurasi dan dimulai
) else (
    echo ✗ Gagal mengkonfigurasi slave replikasi
    pause
    exit /b 1
)

echo.
echo Memeriksa status slave...

%MYSQL_DIR%\mysql.exe -h %SLAVE_HOST% -P %SLAVE_PORT% -u root -e "SHOW SLAVE STATUS\G" > slave_status.tmp

echo Status slave:
type slave_status.tmp | findstr "Slave_IO_Running\|Slave_SQL_Running\|Last_Error\|Seconds_Behind_Master"

echo.
echo ✓ Konfigurasi slave selesai!
echo.
echo Status lengkap disimpan dalam: slave_status.tmp
echo Sekarang jalankan unlock_master.bat untuk melepas lock di master
echo.

del slave_initial_data.sql

pause
