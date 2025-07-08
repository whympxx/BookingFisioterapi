@echo off
setlocal enabledelayedexpansion

echo ===============================================
echo    SETUP SLAVE DATABASE FISIOTERAPI
echo ===============================================
echo.

set MYSQL_BASE_DIR=C:\xampp\mysql
set SLAVE_DATA_DIR=C:\xampp\mysql_slave
set SLAVE_CONFIG=%~dp0..\config\mysql_slave.cnf
set MASTER_HOST=localhost
set MASTER_PORT=3306
set SLAVE_PORT=3307

echo Membuat direktori untuk slave database...

REM Buat direktori slave
if not exist "%SLAVE_DATA_DIR%" mkdir "%SLAVE_DATA_DIR%"
if not exist "%SLAVE_DATA_DIR%\data" mkdir "%SLAVE_DATA_DIR%\data"
if not exist "%SLAVE_DATA_DIR%\logs" mkdir "%SLAVE_DATA_DIR%\logs"

echo ✓ Direktori slave database dibuat

REM Cek apakah slave sudah diinisialisasi
if not exist "%SLAVE_DATA_DIR%\data\mysql" (
    echo Menginisialisasi database slave...
    
    REM Initialize database
"%MYSQL_BASE_DIR%\bin\mysqld.exe" --defaults-file="%SLAVE_CONFIG%" --initialize-insecure --console
    
    if !errorlevel! equ 0 (
        echo ✓ Database slave berhasil diinisialisasi
    ) else (
        echo ✗ Gagal menginisialisasi database slave
        pause
        exit /b 1
    )
) else (
    echo ✓ Database slave sudah diinisialisasi sebelumnya
)

echo.
echo Memulai MySQL slave instance...
echo Port: %SLAVE_PORT%
echo Config: %SLAVE_CONFIG%
echo.

REM Start slave MySQL instance
start "MySQL Slave" "%MYSQL_BASE_DIR%\bin\mysqld.exe" --defaults-file="%SLAVE_CONFIG%" --console

REM Tunggu sebentar untuk startup
echo Menunggu slave database startup...
timeout /t 10 /nobreak > nul

REM Test koneksi ke slave
echo Testing koneksi ke slave database...
"%MYSQL_BASE_DIR%\bin\mysql.exe" -h %MASTER_HOST% -P %SLAVE_PORT% -u root -e "SELECT 'Slave database is running' AS status;" 2>nul

if !errorlevel! equ 0 (
    echo ✓ Slave database berhasil dijalankan dan dapat diakses
    echo.
    echo ===============================================
    echo             SLAVE DATABASE INFO
    echo ===============================================
    echo Host: %MASTER_HOST%
    echo Port: %SLAVE_PORT%
    echo Config: %SLAVE_CONFIG%
    echo Data Directory: %SLAVE_DATA_DIR%\data
    echo Logs Directory: %SLAVE_DATA_DIR%\logs
    echo ===============================================
    echo.
    echo Slave database siap untuk dikonfigurasi replikasi.
    echo Gunakan script setup_replication.bat untuk mengkonfigurasi replikasi.
    echo.
    echo Untuk menghentikan slave:
    echo "%MYSQL_BASE_DIR%\bin\mysqladmin.exe" -h %MASTER_HOST% -P %SLAVE_PORT% -u root shutdown
    echo.
) else (
    echo ✗ Gagal mengakses slave database
    echo Periksa log error di: %SLAVE_DATA_DIR%\logs\mysql_error.log
)

echo Tekan Enter untuk melanjutkan...
pause >nul
