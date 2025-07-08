<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

if ($_POST['action'] !== 'test') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

try {
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "db_fisioterapi";
    
    $conn = mysqli_connect($host, $user, $password, $database);
    
    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    // Generate test data
    $test_name = 'AJAX Test ' . date('H:i:s');
    $test_email = 'ajax.test.' . time() . '@example.com';
    $test_phone = '081' . rand(100000000, 999999999);
    
    // Insert test booking
    $query = "INSERT INTO booking (nama, email, no_hp, tanggal_booking, waktu_booking, layanan, status) 
              VALUES (?, ?, ?, CURDATE(), CURTIME(), 'AJAX Test Replication', 'Menunggu')";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $test_name, $test_email, $test_phone);
    
    if (mysqli_stmt_execute($stmt)) {
        $inserted_id = mysqli_insert_id($conn);
        
        // Wait a moment for replication
        usleep(100000); // 100ms
        
        // Check if replicated to slave
        $slave_conn = mysqli_connect($host, $user, $password, "slave_fisioterapi");
        if ($slave_conn) {
            $check_query = "SELECT COUNT(*) as count FROM booking WHERE nama = ?";
            $check_stmt = mysqli_prepare($slave_conn, $check_query);
            mysqli_stmt_bind_param($check_stmt, "s", $test_name);
            mysqli_stmt_execute($check_stmt);
            $result = mysqli_stmt_get_result($check_stmt);
            $row = mysqli_fetch_assoc($result);
            
            if ($row['count'] > 0) {
                // Cleanup test data
                $cleanup_master = mysqli_prepare($conn, "DELETE FROM booking WHERE id = ?");
                mysqli_stmt_bind_param($cleanup_master, "i", $inserted_id);
                mysqli_stmt_execute($cleanup_master);
                
                $cleanup_slave = mysqli_prepare($slave_conn, "DELETE FROM booking WHERE nama = ?");
                mysqli_stmt_bind_param($cleanup_slave, "s", $test_name);
                mysqli_stmt_execute($cleanup_slave);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Test replikasi berhasil! Data tersinkronisasi dengan baik ke slave database.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Test replikasi gagal! Data tidak tersinkronisasi ke slave database.'
                ]);
            }
            
            mysqli_close($slave_conn);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Tidak dapat terhubung ke slave database untuk verifikasi.'
            ]);
        }
    } else {
        throw new Exception('Failed to insert test data: ' . mysqli_error($conn));
    }
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
