<?php
/**
 * TEST DATABASE REPLICATION CLASS
 */

require_once __DIR__ . '/../includes/db_replication.php';

echo "=== Testing Database Replication Class ===\n";

try {
    $db_replication = new DatabaseReplication();
    echo "✓ DatabaseReplication class instantiated successfully\n";
    
    // Test master connection
    echo "\n--- Testing Master Connection ---\n";
    $master_conn = $db_replication->getMasterConnection();
    
    if ($master_conn) {
        echo "✓ Master connection successful\n";
        
        // Test query sederhana di master
        $result = mysqli_query($master_conn, "SELECT 'Master connection test' as test_message");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "✓ Master query test: " . $row['test_message'] . "\n";
        }
    } else {
        echo "✗ Master connection failed\n";
    }
    
    // Test slave connection (akan fallback ke master jika slave tidak tersedia)
    echo "\n--- Testing Slave Connection ---\n";
    $slave_conn = $db_replication->getSlaveConnection();
    
    if ($slave_conn) {
        echo "✓ Slave connection successful (may fallback to master)\n";
        
        // Test query sederhana di slave
        $result = mysqli_query($slave_conn, "SELECT 'Slave connection test' as test_message");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "✓ Slave query test: " . $row['test_message'] . "\n";
        }
    } else {
        echo "✗ Slave connection failed\n";
    }
    
    // Test executeSelect method
    echo "\n--- Testing executeSelect Method ---\n";
    $select_result = $db_replication->executeSelect("SELECT COUNT(*) as total_bookings FROM booking");
    
    if ($select_result) {
        $row = mysqli_fetch_assoc($select_result);
        echo "✓ executeSelect test successful\n";
        echo "  Total bookings: " . $row['total_bookings'] . "\n";
    } else {
        echo "✗ executeSelect test failed\n";
    }
    
    // Test executeWrite method (insert dummy data)
    echo "\n--- Testing executeWrite Method ---\n";
    $test_insert = $db_replication->executeWrite("INSERT INTO booking (nama, email, no_hp, tanggal_booking, waktu_booking, layanan, status) VALUES ('Test Patient', 'test@example.com', '08123456789', CURDATE(), CURTIME(), 'Test Service', 'Menunggu')");
    
    if ($test_insert) {
        echo "✓ executeWrite test successful (test record inserted)\n";
        
        // Cleanup - hapus test record
        $cleanup = $db_replication->executeWrite("DELETE FROM booking WHERE nama = 'Test Patient'");
        if ($cleanup) {
            echo "✓ Test record cleaned up\n";
        }
    } else {
        echo "✗ executeWrite test failed\n";
    }
    
    // Test replication status
    echo "\n--- Testing Replication Status ---\n";
    $replication_status = $db_replication->getReplicationStatus();
    
    if ($replication_status) {
        echo "✓ getReplicationStatus successful\n";
        
        // Master status
        $master_status = $replication_status['master'];
        echo "  Master Status: " . $master_status['status'] . "\n";
        echo "  Master Host: " . $master_status['host'] . "\n";
        
        // Slave status
        $slaves_status = $replication_status['slaves'];
        echo "  Number of slaves configured: " . count($slaves_status) . "\n";
        
        foreach ($slaves_status as $index => $slave) {
            echo "  Slave " . ($index + 1) . " (" . $slave['host'] . "): " . $slave['status'] . "\n";
        }
    } else {
        echo "✗ getReplicationStatus failed\n";
    }
    
    echo "\n✓ All DatabaseReplication class tests completed\n";
    
} catch (Exception $e) {
    echo "✗ Error testing DatabaseReplication class: " . $e->getMessage() . "\n";
}

echo "\n=== Database Replication Class Test Complete ===\n";
?>
