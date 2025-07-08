@echo off
setlocal enabledelayedexpansion

echo ===============================================
echo    BACKUP OTOMATIS DATABASE FISIOTERAPI
echo    Enhanced Version dengan Kompresi
echo ===============================================
echo.

REM Konfigurasi
set MYSQL_USER=root
set MYSQL_PASSWORD=
set DATABASE_NAME=db_fisioterapi
set BACKUP_BASE_DIR=C:\xampp\htdocs\BookingFisioterapi\backups
set LOG_FILE=%BACKUP_BASE_DIR%\backup.log
set MAX_BACKUP_DAYS=7
set COMPRESSION_ENABLED=true

REM Generate timestamp
for /f "tokens=1-3 delims=/ " %%a in ('date /t') do (
    set DATE=%%c%%a%%b
)
for /f "tokens=1-2 delims=: " %%a in ('time /t') do (
    set TIME=%%a%%b
)
set TIMESTAMP=%DATE%_%TIME: =0%
set BACKUP_DIR=%BACKUP_BASE_DIR%\%TIMESTAMP%
set BACKUP_FILE=%BACKUP_DIR%\%DATABASE_NAME%_full.sql
set COMPRESSED_FILE=%BACKUP_DIR%\%DATABASE_NAME%_full.zip

echo [%date% %time%] Memulai proses backup... >> "%LOG_FILE%"

REM Buat direktori backup
if not exist "%BACKUP_BASE_DIR%" mkdir "%BACKUP_BASE_DIR%"
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo Membuat backup database %DATABASE_NAME%...
echo Lokasi: %BACKUP_FILE%

REM Backup database dengan extended-insert untuk efisiensi
C:\xampp\mysql\bin\mysqldump.exe ^
    --user=%MYSQL_USER% ^
    --password=%MYSQL_PASSWORD% ^
    --single-transaction ^
    --routines ^
    --triggers ^
    --extended-insert ^
    --complete-insert ^
    --add-drop-table ^
    --add-locks ^
    --disable-keys ^
    --quick ^
    --lock-tables=false ^
    %DATABASE_NAME% > "%BACKUP_FILE%"

if %errorlevel% equ 0 (
    echo [SUCCESS] Backup SQL berhasil dibuat!
    echo [%date% %time%] Backup SQL berhasil: %BACKUP_FILE% >> "%LOG_FILE%"
    
    REM Kompresi backup jika diaktifkan
    if "%COMPRESSION_ENABLED%"=="true" (
        echo Mengkompresi backup...
        
        REM Gunakan PowerShell untuk kompresi
        powershell -command "Compress-Archive -Path '%BACKUP_FILE%' -DestinationPath '%COMPRESSED_FILE%' -Force"
        
        if !errorlevel! equ 0 (
            echo [SUCCESS] Backup berhasil dikompres!
            echo [%date% %time%] Backup dikompres: %COMPRESSED_FILE% >> "%LOG_FILE%"
            
            REM Hapus file SQL asli setelah kompresi
            del "%BACKUP_FILE%"
            
            REM Tampilkan ukuran file
            echo Ukuran file backup:
            dir "%COMPRESSED_FILE%" | find /i ".zip"
        ) else (
            echo [WARNING] Kompresi gagal, backup SQL tetap tersedia
            echo [%date% %time%] Kompresi gagal >> "%LOG_FILE%"
        )
    )
    
    REM Backup struktur database saja (untuk referensi cepat)
    echo Membuat backup struktur database...
    set STRUCTURE_FILE=%BACKUP_DIR%\%DATABASE_NAME%_structure.sql
    C:\xampp\mysql\bin\mysqldump.exe ^
        --user=%MYSQL_USER% ^
        --password=%MYSQL_PASSWORD% ^
        --no-data ^
        --routines ^
        --triggers ^
        %DATABASE_NAME% > "!STRUCTURE_FILE!"
    
    if !errorlevel! equ 0 (
        echo [SUCCESS] Backup struktur berhasil dibuat!
        echo [%date% %time%] Backup struktur: !STRUCTURE_FILE! >> "%LOG_FILE%"
    )
    
    REM Generate metadata file
    echo Membuat file metadata...
    set METADATA_FILE=%BACKUP_DIR%\backup_info.txt
    echo Backup Information > "!METADATA_FILE!"
    echo ================== >> "!METADATA_FILE!"
    echo Database: %DATABASE_NAME% >> "!METADATA_FILE!"
    echo Timestamp: %TIMESTAMP% >> "!METADATA_FILE!"
    echo Date: %date% >> "!METADATA_FILE!"
    echo Time: %time% >> "!METADATA_FILE!"
    echo Compression: %COMPRESSION_ENABLED% >> "!METADATA_FILE!"
    echo Host: %COMPUTERNAME% >> "!METADATA_FILE!"
    echo User: %USERNAME% >> "!METADATA_FILE!"
    
    REM Hitung jumlah tabel
    for /f %%i in ('C:\xampp\mysql\bin\mysql.exe -u %MYSQL_USER% -p%MYSQL_PASSWORD% -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='%DATABASE_NAME%'" -s -N') do (
        echo Tables Count: %%i >> "!METADATA_FILE!"
    )
    
    echo [SUCCESS] File metadata dibuat: !METADATA_FILE!
    
) else (
    echo [ERROR] Backup gagal! Periksa koneksi database.
    echo [%date% %time%] Backup gagal! Error code: %errorlevel% >> "%LOG_FILE%"
    exit /b 1
)

REM Cleanup backup lama
echo.
echo Membersihkan backup lama (lebih dari %MAX_BACKUP_DAYS% hari)...
forfiles /p "%BACKUP_BASE_DIR%" /c "cmd /c if @isdir==TRUE (echo Menghapus direktori lama: @path && rmdir /s /q @path)" /m *.* /d -%MAX_BACKUP_DAYS% 2>nul

if !errorlevel! equ 0 (
    echo [SUCCESS] Cleanup backup lama selesai!
    echo [%date% %time%] Cleanup backup lama selesai >> "%LOG_FILE%"
) else (
    echo [INFO] Tidak ada backup lama yang perlu dihapus
)

REM Summary
echo.
echo ===============================================
echo             BACKUP SUMMARY
echo ===============================================
echo Database: %DATABASE_NAME%
echo Backup Directory: %BACKUP_DIR%
echo Compression: %COMPRESSION_ENABLED%
echo Log File: %LOG_FILE%
echo Status: SUCCESS
echo ===============================================

echo [%date% %time%] Backup selesai dengan sukses >> "%LOG_FILE%"

echo.
echo Tekan Enter untuk menutup...
pause >nul
