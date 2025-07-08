@echo off
echo ===============================================
echo    TEST REAL-TIME REPLICATION
echo ===============================================
echo.

set MYSQL_DIR=C:\xampp\mysql\bin

echo 1. Menampilkan jumlah record awal...
echo Master booking count:
%MYSQL_DIR%\mysql.exe -u root -e "SELECT COUNT(*) as count FROM db_fisioterapi.booking;"

echo Slave booking count:
%MYSQL_DIR%\mysql.exe -u root -e "SELECT COUNT(*) as count FROM slave_fisioterapi.booking;"

echo.
echo 2. Menambahkan data baru ke MASTER...
%MYSQL_DIR%\mysql.exe -u root -e "INSERT INTO db_fisioterapi.booking (nama, email, no_hp, tanggal_booking, waktu_booking, layanan, status) VALUES ('Real Time Test', 'realtime@test.com', '08555666777', CURDATE(), CURTIME(), 'Test Real-Time Replication', 'Menunggu');"

echo Data berhasil ditambahkan ke master!

echo.
echo 3. Memeriksa apakah data otomatis tersinkron ke SLAVE...
timeout /t 2 /nobreak > nul

echo Master booking count after insert:
%MYSQL_DIR%\mysql.exe -u root -e "SELECT COUNT(*) as count FROM db_fisioterapi.booking;"

echo Slave booking count after insert:
%MYSQL_DIR%\mysql.exe -u root -e "SELECT COUNT(*) as count FROM slave_fisioterapi.booking;"

echo.
echo 4. Menampilkan data terbaru di kedua database...
echo Master data:
%MYSQL_DIR%\mysql.exe -u root -e "SELECT id, nama, email, tanggal_booking, status FROM db_fisioterapi.booking ORDER BY id DESC LIMIT 1;"

echo Slave data:
%MYSQL_DIR%\mysql.exe -u root -e "SELECT id, nama, email, tanggal_booking, status FROM slave_fisioterapi.booking ORDER BY id DESC LIMIT 1;"

echo.
echo 5. Test UPDATE operation...
%MYSQL_DIR%\mysql.exe -u root -e "UPDATE db_fisioterapi.booking SET status = 'Dikonfirmasi' WHERE nama = 'Real Time Test';"

timeout /t 1 /nobreak > nul

echo Master status after update:
%MYSQL_DIR%\mysql.exe -u root -e "SELECT nama, status FROM db_fisioterapi.booking WHERE nama = 'Real Time Test';"

echo Slave status after update:
%MYSQL_DIR%\mysql.exe -u root -e "SELECT nama, status FROM slave_fisioterapi.booking WHERE nama = 'Real Time Test';"

echo.
echo 6. Test DELETE operation...
%MYSQL_DIR%\mysql.exe -u root -e "DELETE FROM db_fisioterapi.booking WHERE nama = 'Real Time Test';"

timeout /t 1 /nobreak > nul

echo Records after delete:
echo Master:
%MYSQL_DIR%\mysql.exe -u root -e "SELECT COUNT(*) as count FROM db_fisioterapi.booking WHERE nama = 'Real Time Test';"

echo Slave:
%MYSQL_DIR%\mysql.exe -u root -e "SELECT COUNT(*) as count FROM slave_fisioterapi.booking WHERE nama = 'Real Time Test';"

echo.
echo ===============================================
echo    REAL-TIME REPLICATION TEST COMPLETE!
echo ===============================================
echo.

pause
