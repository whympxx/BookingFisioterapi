@echo off
echo ====================================
echo   BACKUP DATABASE FISIOTERAPI
echo ====================================
echo.

set MYSQL_USER=root
set MYSQL_PASSWORD=
set DATABASE_NAME=db_fisioterapi
set BACKUP_DIR=C:\xampp\htdocs\BookingFisioterapi\backups
set DATE=%date:~10,4%%date:~4,2%%date:~7,2%
set TIME=%time:~0,2%%time:~3,2%%time:~6,2%
set BACKUP_FILE=%BACKUP_DIR%\backup_%DATABASE_NAME%_%DATE%_%TIME%.sql

echo Membuat direktori backup jika belum ada...
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo Memulai backup database %DATABASE_NAME%...
echo File backup: %BACKUP_FILE%

C:\xampp\mysql\bin\mysqldump.exe -u %MYSQL_USER% -p%MYSQL_PASSWORD% %DATABASE_NAME% > "%BACKUP_FILE%"

if %errorlevel% equ 0 (
    echo.
    echo [SUCCESS] Backup berhasil dibuat!
    echo Lokasi file: %BACKUP_FILE%
    echo Ukuran file: 
    dir "%BACKUP_FILE%" | find /i ".sql"
) else (
    echo.
    echo [ERROR] Backup gagal! Periksa koneksi database.
)

echo.
echo Tekan Enter untuk menutup...
pause >nul
