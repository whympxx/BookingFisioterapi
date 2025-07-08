<?php
/**
 * TEST KONEKSI DATABASE DASAR
 */

echo "=== Testing Database Connection ===\n";

// Test koneksi database utama
$host = "localhost";
$user = "root";
$password = "";
$database = "db_fisioterapi";

echo "Testing connection to: $host:3306/$database\n";

$conn = mysqli_connect($host, $user, $password, $database);

if ($conn) {
    echo "✓ Connection successful!\n";
    
    // Test query sederhana
    $result = mysqli_query($conn, "SELECT DATABASE() as db_name, VERSION() as version, NOW() as `current_time`");
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✓ Query test successful!\n";
        echo "  Database: " . $row['db_name'] . "\n";
        echo "  Version: " . $row['version'] . "\n";
        echo "  Current Time: " . $row['current_time'] . "\n";
    } else {
        echo "✗ Query test failed: " . mysqli_error($conn) . "\n";
    }
    
    // Test apakah tabel penting ada
    $tables = ['booking', 'user', 'admin'];
    echo "\nChecking essential tables:\n";
    
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if ($result && mysqli_num_rows($result) > 0) {
            echo "✓ Table '$table' exists\n";
            
            // Hitung jumlah record
            $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table`");
            if ($count_result) {
                $count = mysqli_fetch_assoc($count_result)['count'];
                echo "  Records: $count\n";
            }
        } else {
            echo "✗ Table '$table' not found\n";
        }
    }
    
    mysqli_close($conn);
} else {
    echo "✗ Connection failed: " . mysqli_connect_error() . "\n";
    exit(1);
}

echo "\n=== Database Connection Test Complete ===\n";
?>
