@echo off
echo ===============================================
echo   SETUP TASK SCHEDULER UNTUK HEALTH CHECK
echo ===============================================
echo.

set SCRIPT_PATH=C:\xampp\htdocs\BookingFisioterapi\scripts\replication_health_check.php
set PHP_PATH=C:\xampp\php\php.exe
set TASK_NAME=ReplicationHealthCheck

echo Membuat scheduled task untuk health check otomatis...
echo Script: %SCRIPT_PATH%
echo PHP: %PHP_PATH%
echo Task Name: %TASK_NAME%
echo.

REM Hapus task yang sudah ada (jika ada)
schtasks /delete /tn "%TASK_NAME%" /f >nul 2>&1

REM Buat task baru yang berjalan setiap 5 menit
schtasks /create /tn "%TASK_NAME%" ^
    /tr "\"%PHP_PATH%\" \"%SCRIPT_PATH%\"" ^
    /sc minute /mo 5 ^
    /ru "SYSTEM" ^
    /rl highest ^
    /f

if %errorlevel% equ 0 (
    echo [SUCCESS] Task scheduler berhasil dibuat!
    echo Task akan berjalan setiap 5 menit untuk melakukan health check.
    echo.
    echo Untuk melihat status task:
    echo schtasks /query /tn "%TASK_NAME%"
    echo.
    echo Untuk menjalankan task sekarang:
    echo schtasks /run /tn "%TASK_NAME%"
    echo.
    echo Untuk menghapus task:
    echo schtasks /delete /tn "%TASK_NAME%" /f
    
    REM Jalankan task sekarang untuk test
    echo Menjalankan health check pertama kali...
    schtasks /run /tn "%TASK_NAME%"
    
    if !errorlevel! equ 0 (
        echo [SUCCESS] Health check pertama berhasil dijalankan!
    ) else (
        echo [WARNING] Gagal menjalankan health check. Periksa konfigurasi.
    )
    
) else (
    echo [ERROR] Gagal membuat task scheduler!
    echo Pastikan Anda menjalankan sebagai Administrator.
)

echo.
echo ===============================================
echo Tekan Enter untuk menutup...
pause >nul
