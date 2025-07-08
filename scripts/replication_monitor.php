<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * MONITORING REPLIKASI DATABASE - ENHANCED VERSION
 * Script untuk memantau status replikasi dengan fitur advanced
 */

require_once '../includes/db_replication.php';

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
        'name' => 'Local Slave'
    ]
];

function connectDatabase($config) {
    try {
        // Set connection timeout
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);
        
        if (!$conn) {
            return false;
        }
        
        // Test the connection
        if (!mysqli_ping($conn)) {
            mysqli_close($conn);
            return false;
        }
        
        return $conn;
    } catch (mysqli_sql_exception $e) {
        // Log error but don't break the page
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

function getSlaveStatus($conn) {
    $result = mysqli_query($conn, "SHOW SLAVE STATUS");
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

function getReplicationHealth($master_conn, $slave_conn) {
    $health = [];
    
    // Cek koneksi
    $health['master_connected'] = ($master_conn !== false);
    $health['slave_connected'] = ($slave_conn !== false);
    
    if (!$health['master_connected'] || !$health['slave_connected']) {
        $health['status'] = 'critical';
        return $health;
    }
    
    // Cek replikasi
    $slave_status = getSlaveStatus($slave_conn);
    if ($slave_status) {
        $io_running = $slave_status['Slave_IO_Running'] === 'Yes';
        $sql_running = $slave_status['Slave_SQL_Running'] === 'Yes';
        $lag = $slave_status['Seconds_Behind_Master'];
        
        $health['io_running'] = $io_running;
        $health['sql_running'] = $sql_running;
        $health['lag'] = $lag;
        
        if ($io_running && $sql_running) {
            if ($lag === null) {
                $health['status'] = 'warning';
            } elseif ($lag <= 5) {
                $health['status'] = 'good';
            } else {
                $health['status'] = 'warning';
            }
        } else {
            $health['status'] = 'critical';
        }
    } else {
        $health['status'] = 'critical';
    }
    
    return $health;
}

function getPerformanceMetrics($conn) {
    $metrics = [];
    
    if (!$conn) return $metrics;
    
    // Queries per second
    $result = mysqli_query($conn, "SHOW GLOBAL STATUS LIKE 'Questions'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $metrics['total_queries'] = $row['Value'];
    }
    
    // Uptime
    $result = mysqli_query($conn, "SHOW GLOBAL STATUS LIKE 'Uptime'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $metrics['uptime'] = $row['Value'];
        if ($metrics['uptime'] > 0) {
            $metrics['qps'] = round($metrics['total_queries'] / $metrics['uptime'], 2);
        }
    }
    
    // Connections
    $result = mysqli_query($conn, "SHOW GLOBAL STATUS LIKE 'Threads_connected'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $metrics['connections'] = $row['Value'];
    }
    
    return $metrics;
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
    <title>Monitoring Replikasi Database</title>
    <link rel="stylesheet" href="../assets/bootstrap-4.6.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .status-good { color: #28a745; font-weight: bold; }
        .status-bad { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .bg-master { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-slave { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h1 class="text-center mb-4">Monitoring Replikasi Database</h1>
        
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
                            echo '<strong>Jumlah Record Booking:</strong> ' . $master_booking_count;
                            
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
                                $slave_status = getSlaveStatus($slave_conn);
                                $slave_booking_count = getTableCount($slave_conn, 'booking');

                                echo '<div class="status-good">✓ Koneksi Berhasil</div>';
                                echo '<hr>';

                                if ($slave_status) {
                                    $io_running = $slave_status['Slave_IO_Running'];
                                    $sql_running = $slave_status['Slave_SQL_Running'];

                                    echo '<strong>Slave IO Running:</strong> ';
                                    echo ($io_running == 'Yes') ? '<span class="status-good">Yes</span>' : '<span class="status-bad">No</span>';
                                    echo '<br>';

                                    echo '<strong>Slave SQL Running:</strong> ';
                                    echo ($sql_running == 'Yes') ? '<span class="status-good">Yes</span>' : '<span class="status-bad">No</span>';
                                    echo '<br>';

                                    echo '<strong>Master Log File:</strong> ' . $slave_status['Master_Log_File'] . '<br>';
                                    echo '<strong>Read Master Log Pos:</strong> ' . $slave_status['Read_Master_Log_Pos'] . '<br>';

                                    if ($slave_status['Last_Error']) {
                                        echo '<div class="status-bad">Error: ' . $slave_status['Last_Error'] . '</div>';
                                    }

                                    echo '<hr>';
                                    echo '<strong>Seconds Behind Master:</strong> ';
                                    $lag = $slave_status['Seconds_Behind_Master'];
                                    if ($lag === null) {
                                        echo '<span class="status-bad">Unknown</span>';
                                    } elseif ($lag == 0) {
                                        echo '<span class="status-good">0 (Sinkron)</span>';
                                    } else {
                                        echo '<span class="status-warning">' . $lag . ' detik</span>';
                                    }
                                } else {
                                    echo '<div class="status-bad">✗ Replikasi tidak dikonfigurasi</div>';
                                }

                                echo '<hr>';
                                echo '<strong>Jumlah Record Booking:</strong> ' . $slave_booking_count;

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
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Perbandingan Data</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if (isset($master_booking_count) && isset($slave_booking_count)) {
                            echo '<div class="row text-center">';
                            echo '<div class="col-md-4">';
                            echo '<h6>Master Records</h6>';
                            echo '<h2 class="text-primary">' . $master_booking_count . '</h2>';
                            echo '</div>';
                            echo '<div class="col-md-4">';
                            echo '<h6>Slave Records</h6>';
                            echo '<h2 class="text-info">' . $slave_booking_count . '</h2>';
                            echo '</div>';
                            echo '<div class="col-md-4">';
                            echo '<h6>Status Sinkronisasi</h6>';
                            if ($master_booking_count == $slave_booking_count) {
                                echo '<h2 class="status-good">✓ Sinkron</h2>';
                            } else {
                                echo '<h2 class="status-bad">✗ Tidak Sinkron</h2>';
                                echo '<small>Selisih: ' . abs($master_booking_count - $slave_booking_count) . ' record</small>';
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh Status
            </button>
            <a href="../pages/admin.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Admin
            </a>
        </div>
    </div>
    
    <script src="../assets/bootstrap-4.6.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh setiap 30 detik
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
