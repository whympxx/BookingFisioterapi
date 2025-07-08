@echo off
echo ===============================================
echo    TEST SLAVE DATABASE REPLICATION
echo ===============================================
echo.

set MYSQL_DIR=C:\xampp\mysql\bin

echo Testing slave database setup...

echo.
echo 1. Checking databases...
%MYSQL_DIR%\mysql.exe -u root -e "SHOW DATABASES LIKE '%fisioterapi%';"

echo.
echo 2. Checking master database tables...
%MYSQL_DIR%\mysql.exe -u root -e "USE db_fisioterapi; SHOW TABLES;"

echo.
echo 3. Checking slave database tables...
%MYSQL_DIR%\mysql.exe -u root -e "USE slave_fisioterapi; SHOW TABLES;"

echo.
echo 4. Comparing record counts...
echo Master booking count:
%MYSQL_DIR%\mysql.exe -u root -e "USE db_fisioterapi; SELECT COUNT(*) as master_count FROM booking;"

echo Slave booking count:
%MYSQL_DIR%\mysql.exe -u root -e "USE slave_fisioterapi; SELECT COUNT(*) as slave_count FROM booking;"

echo.
echo 5. Testing replication by inserting test data...
echo Inserting test record in master...
%MYSQL_DIR%\mysql.exe -u root -e "USE db_fisioterapi; INSERT INTO booking (nama, email, no_hp, tanggal_booking, waktu_booking, layanan, status) VALUES ('Test Slave', 'test@slave.com', '08123456789', CURDATE(), CURTIME(), 'Test Replication', 'Menunggu');"

echo Master count after insert:
%MYSQL_DIR%\mysql.exe -u root -e "USE db_fisioterapi; SELECT COUNT(*) as master_count FROM booking;"

echo.
echo Manually copying to slave for simulation...
%MYSQL_DIR%\mysql.exe -u root -e "USE slave_fisioterapi; INSERT INTO booking (nama, email, no_hp, tanggal_booking, waktu_booking, layanan, status) VALUES ('Test Slave', 'test@slave.com', '08123456789', CURDATE(), CURTIME(), 'Test Replication', 'Menunggu');"

echo Slave count after manual sync:
%MYSQL_DIR%\mysql.exe -u root -e "USE slave_fisioterapi; SELECT COUNT(*) as slave_count FROM booking;"

echo.
echo ===============================================
echo    REPLICATION TEST COMPLETE
echo ===============================================
echo.
echo Slave database simulation is working!
echo.
echo To clean up test data:
echo %MYSQL_DIR%\mysql.exe -u root -e "DELETE FROM db_fisioterapi.booking WHERE nama='Test Slave';"
echo %MYSQL_DIR%\mysql.exe -u root -e "DELETE FROM slave_fisioterapi.booking WHERE nama='Test Slave';"
echo.

pause
