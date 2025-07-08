@echo off
echo ====================================
echo   RESTORE DATABASE FISIOTERAPI
echo ====================================
echo.

set MYSQL_USER=root
set MYSQL_PASSWORD=
set DATABASE_NAME=db_fisioterapi
set BACKUP_DIR=C:\xampp\htdocs\BookingFisioterapi\backups

echo Daftar file backup yang tersedia:
echo.
dir "%BACKUP_DIR%\*.sql" /B
echo.

set /p BACKUP_FILE="Masukkan nama file backup (tanpa path): "

if not exist "%BACKUP_DIR%\%BACKUP_FILE%" (
    echo [ERROR] File backup tidak ditemukan!
    echo Periksa nama file dan coba lagi.
    pause
    exit /b 1
)

echo.
echo Memulai restore database %DATABASE_NAME%...
echo File backup: %BACKUP_DIR%\%BACKUP_FILE%

C:\xampp\mysql\bin\mysql.exe -u %MYSQL_USER% -p%MYSQL_PASSWORD% %DATABASE_NAME% < "%BACKUP_DIR%\%BACKUP_FILE%"

if %errorlevel% equ 0 (
    echo.
    echo [SUCCESS] Restore berhasil!
    echo Database %DATABASE_NAME% telah dipulihkan.
) else (
    echo.
    echo [ERROR] Restore gagal! Periksa koneksi database dan format file.
)

echo.
echo Tekan Enter untuk menutup...
pause >nul
