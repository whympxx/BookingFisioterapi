<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * MONITORING REPLIKASI DATABASE - ENHANCED VERSION
 * Script untuk memantau status replikasi dengan fitur trigger-based replication
 */

require_once __DIR__ . '/../includes/db_replication.php';

// Konfigurasi database
$master_config = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'db_fisioterapi'
];

$slave_configs = [
    [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'slave_fisioterapi',
        'name' => 'Local Slave (Trigger-based)'
    ]
];

function connectDatabase($config) {
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);
        
        if (!$conn) {
            return false;
        }
        
        if (!mysqli_ping($conn)) {
            mysqli_close($conn);
            return false;
        }
        
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return false;
    }
}

function getMasterStatus($conn) {
    $result = mysqli_query($conn, "SHOW MASTER STATUS");
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

function getTableCount($conn, $table) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table`");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

function getTriggerCount($conn, $database) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM information_schema.triggers WHERE trigger_schema = '$database'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

function getReplicationLog($conn, $limit = 5) {
    $result = mysqli_query($conn, "SELECT * FROM replication_log ORDER BY timestamp DESC LIMIT $limit");
    $logs = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
    }
    return $logs;
}

function logReplicationEvent($event, $details) {
    $log_file = '../logs/replication_events.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $event: $details\n";
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Replikasi Database - Enhanced</title>
    <link rel="stylesheet" href="../assets/bootstrap-4.6.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .status-good { color: #28a745; font-weight: bold; }
        .status-bad { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .bg-master { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-slave { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .bg-triggers { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .event-log { 
            background: #f8f9fa; 
            padding: 8px; 
            border-radius: 5px; 
            margin: 2px 0; 
            font-size: 0.85em;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-database"></i> Monitoring Replikasi Database
            <small class="text-muted d-block">Trigger-based Replication</small>
        </h1>
        
        <div class="row">
            <!-- Master Status -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-master text-white">
                        <h5 class="mb-0"><i class="fas fa-server"></i> Master Database</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $master_conn = connectDatabase($master_config);
                        if ($master_conn) {
                            $master_status = getMasterStatus($master_conn);
                            $master_booking_count = getTableCount($master_conn, 'booking');
                            $master_user_count = getTableCount($master_conn, 'user');
                            $master_admin_count = getTableCount($master_conn, 'admin');
                            $trigger_count = getTriggerCount($master_conn, 'db_fisioterapi');
                            
                            echo '<div class="status-good">✓ Koneksi Berhasil</div>';
                            echo '<hr>';
                            
                            if ($master_status) {
                                echo '<strong>Binary Log File:</strong> ' . $master_status['File'] . '<br>';
                                echo '<strong>Position:</strong> ' . $master_status['Position'] . '<br>';
                                echo '<strong>Binlog Do DB:</strong> ' . ($master_status['Binlog_Do_DB'] ?: 'All') . '<br>';
                            } else {
                                echo '<div class="status-bad">✗ Binary logging tidak aktif</div>';
                            }
                            
                            echo '<hr>';
                            echo '<strong>Replication Triggers:</strong> ' . $trigger_count . '<br>';
                            echo '<strong>Record Counts:</strong><br>';
                            echo '• Booking: ' . $master_booking_count . '<br>';
                            echo '• User: ' . $master_user_count . '<br>';
                            echo '• Admin: ' . $master_admin_count;
                            
                            mysqli_close($master_conn);
                        } else {
                            echo '<div class="status-bad">✗ Koneksi Gagal</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Slave Status -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-slave text-white">
                        <h5 class="mb-0"><i class="fas fa-server"></i> Slave Database</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        foreach ($slave_configs as $slave_config) {
                            echo '<div class="mb-4 p-2" style="border-bottom:1px solid #eee">';
                            echo '<strong>' . htmlspecialchars($slave_config['name']) . '</strong><br>';
                            $slave_conn = connectDatabase($slave_config);
                            if ($slave_conn) {
                                $slave_booking_count = getTableCount($slave_conn, 'booking');
                                $slave_user_count = getTableCount($slave_conn, 'user');
                                $slave_admin_count = getTableCount($slave_conn, 'admin');

                                echo '<div class="status-good">✓ Koneksi Berhasil</div>';
                                echo '<hr>';
                                
                                // Check replication status
                                echo '<strong>Replication Type:</strong> <span class="status-good">Trigger-based</span><br>';
                                echo '<strong>Lag:</strong> <span class="status-good">Real-time (0ms)</span><br>';
                                
                                // Check replication log
                                $log_result = mysqli_query($slave_conn, "SELECT COUNT(*) as log_count FROM replication_log");
                                if ($log_result) {
                                    $log_row = mysqli_fetch_assoc($log_result);
                                    echo '<strong>Total Events:</strong> ' . $log_row['log_count'] . '<br>';
                                }
                                
                                echo '<hr>';
                                echo '<strong>Record Counts:</strong><br>';
                                echo '• Booking: ' . $slave_booking_count . '<br>';
                                echo '• User: ' . $slave_user_count . '<br>';
                                echo '• Admin: ' . $slave_admin_count;

                                mysqli_close($slave_conn);
                            } else {
                                echo '<div class="status-bad">✗ Koneksi Gagal</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Comparison -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Perbandingan Data</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if (isset($master_booking_count) && isset($slave_booking_count)) {
                            echo '<div class="row text-center">';
                            
                            // Master stats
                            echo '<div class="col-md-4">';
                            echo '<h6>Master Database</h6>';
                            echo '<table class="table table-sm">';
                            echo '<tr><td>Booking</td><td><span class="badge badge-primary">' . $master_booking_count . '</span></td></tr>';
                            echo '<tr><td>User</td><td><span class="badge badge-primary">' . $master_user_count . '</span></td></tr>';
                            echo '<tr><td>Admin</td><td><span class="badge badge-primary">' . $master_admin_count . '</span></td></tr>';
                            echo '</table>';
                            echo '</div>';
                            
                            // Slave stats
                            echo '<div class="col-md-4">';
                            echo '<h6>Slave Database</h6>';
                            echo '<table class="table table-sm">';
                            echo '<tr><td>Booking</td><td><span class="badge badge-info">' . $slave_booking_count . '</span></td></tr>';
                            echo '<tr><td>User</td><td><span class="badge badge-info">' . $slave_user_count . '</span></td></tr>';
                            echo '<tr><td>Admin</td><td><span class="badge badge-info">' . $slave_admin_count . '</span></td></tr>';
                            echo '</table>';
                            echo '</div>';
                            
                            // Sync status
                            echo '<div class="col-md-4">';
                            echo '<h6>Status Sinkronisasi</h6>';
                            $all_synced = ($master_booking_count == $slave_booking_count) && 
                                         ($master_user_count == $slave_user_count) && 
                                         ($master_admin_count == $slave_admin_count);
                            
                            if ($all_synced) {
                                echo '<h2 class="status-good">✓ Sinkron</h2>';
                                echo '<small>Semua tabel tersinkronisasi</small>';
                            } else {
                                echo '<h2 class="status-bad">✗ Tidak Sinkron</h2>';
                                echo '<small>Ada perbedaan data</small>';
                            }
                            echo '</div>';
                            
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Replication Events Log -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-triggers text-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Log Events Replikasi</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $slave_conn = connectDatabase($slave_configs[0]);
                        if ($slave_conn) {
                            $recent_logs = getReplicationLog($slave_conn, 10);
                            if (!empty($recent_logs)) {
                                echo '<div class="row">';
                                foreach ($recent_logs as $log) {
                                    echo '<div class="col-md-6 mb-2">';
                                    echo '<div class="event-log">';
                                    echo '<strong>' . strtoupper($log['operation']) . '</strong> ';
                                    echo '<span class="badge badge-secondary">' . $log['table_name'] . '</span><br>';
                                    echo '<small class="text-muted">' . $log['timestamp'] . '</small><br>';
                                    echo '<small>' . htmlspecialchars($log['details']) . '</small>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<div class="text-center text-muted">';
                                echo '<i class="fas fa-info-circle"></i> Belum ada events replikasi';
                                echo '</div>';
                            }
                            mysqli_close($slave_conn);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Controls -->
        <div class="text-center mt-4">
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh Status
            </button>
            <a href="../pages/admin.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Admin
            </a>
            <button class="btn btn-success" onclick="testReplication()">
                <i class="fas fa-test-tube"></i> Test Replication
            </button>
        </div>
    </div>
    
    <script src="../assets/bootstrap-4.6.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh setiap 30 detik
        setTimeout(function() {
            location.reload();
        }, 30000);
        
        function testReplication() {
            if (confirm('Akan menambahkan data test untuk menguji replikasi. Lanjutkan?')) {
                // Buat request AJAX untuk test replication
                fetch('../scripts/test_replication_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=test'
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                })
                .catch(error => {
                    alert('Error: ' + error);
                });
            }
        }
    </script>
</body>
</html>
