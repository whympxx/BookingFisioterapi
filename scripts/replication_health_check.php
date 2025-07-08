<?php
/**
 * SISTEM HEALTH CHECK OTOMATIS REPLIKASI
 * Melakukan pemeriksaan kesehatan replikasi secara berkala
 */

require_once __DIR__ . '/../includes/db_replication.php';

class ReplicationHealthChecker {
    private $db_replication;
    private $log_file;
    private $alert_threshold;
    private $critical_threshold;
    
    public function __construct() {
        $this->db_replication = new DatabaseReplication();
        $this->log_file = __DIR__ . '/../logs/health_check.log';
        $this->alert_threshold = 10; // detik
        $this->critical_threshold = 60; // detik
        
        // Pastikan direktori log ada
        $log_dir = dirname($this->log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
    }
    
    /**
     * Jalankan health check lengkap
     */
    public function runHealthCheck() {
        $results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_status' => 'good',
            'checks' => []
        ];
        
        $this->log("=== Starting Health Check ===");
        
        // Test koneksi master
        $master_check = $this->checkMasterConnection();
        $results['checks']['master'] = $master_check;
        
        if (!$master_check['success']) {
            $results['overall_status'] = 'critical';
        }
        
        // Test koneksi slave
        $slave_checks = $this->checkSlaveConnections();
        $results['checks']['slaves'] = $slave_checks;
        
        $active_slaves = 0;
        foreach ($slave_checks as $slave_check) {
            if ($slave_check['success']) {
                $active_slaves++;
                
                // Cek lag replikasi
                if (isset($slave_check['lag'])) {
                    if ($slave_check['lag'] > $this->critical_threshold) {
                        $results['overall_status'] = 'critical';
                    } elseif ($slave_check['lag'] > $this->alert_threshold && $results['overall_status'] !== 'critical') {
                        $results['overall_status'] = 'warning';
                    }
                }
            }
        }
        
        // Jika tidak ada slave yang aktif
        if ($active_slaves === 0 && $results['overall_status'] !== 'critical') {
            $results['overall_status'] = 'warning';
        }
        
        // Test konsistensi data
        $consistency_check = $this->checkDataConsistency();
        $results['checks']['consistency'] = $consistency_check;
        
        if (!$consistency_check['consistent']) {
            if ($results['overall_status'] === 'good') {
                $results['overall_status'] = 'warning';
            }
        }
        
        // Test performa
        $performance_check = $this->checkPerformance();
        $results['checks']['performance'] = $performance_check;
        
        $this->log("Health Check completed. Overall status: " . $results['overall_status']);
        
        // Simpan hasil ke file
        $this->saveResults($results);
        
        // Kirim alert jika diperlukan
        if ($results['overall_status'] !== 'good') {
            $this->sendAlert($results);
        }
        
        return $results;
    }
    
    /**
     * Cek koneksi master
     */
    private function checkMasterConnection() {
        $result = [
            'success' => false,
            'message' => '',
            'response_time' => 0
        ];
        
        $start_time = microtime(true);
        
        try {
            $conn = $this->db_replication->getMasterConnection();
            
            if ($conn) {
                // Test query sederhana
                $test_result = mysqli_query($conn, "SELECT 1");
                
                if ($test_result) {
                    $result['success'] = true;
                    $result['message'] = 'Master connection OK';
                } else {
                    $result['message'] = 'Master query failed: ' . mysqli_error($conn);
                }
            } else {
                $result['message'] = 'Failed to connect to master';
            }
        } catch (Exception $e) {
            $result['message'] = 'Master connection error: ' . $e->getMessage();
        }
        
        $result['response_time'] = round((microtime(true) - $start_time) * 1000, 2);
        
        $this->log("Master check: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . " - " . $result['message']);
        
        return $result;
    }
    
    /**
     * Cek koneksi semua slave
     */
    private function checkSlaveConnections() {
        $status = $this->db_replication->getReplicationStatus();
        $results = [];
        
        foreach ($status['slaves'] as $slave) {
            $result = [
                'host' => $slave['host'],
                'success' => false,
                'message' => '',
                'lag' => null,
                'io_running' => false,
                'sql_running' => false
            ];
            
            if ($slave['status'] === 'connected') {
                $result['success'] = true;
                $result['message'] = 'Slave connection OK';
                
                if ($slave['replication']) {
                    $result['io_running'] = ($slave['replication']['Slave_IO_Running'] === 'Yes');
                    $result['sql_running'] = ($slave['replication']['Slave_SQL_Running'] === 'Yes');
                    $result['lag'] = $slave['replication']['Seconds_Behind_Master'];
                    
                    if (!$result['io_running'] || !$result['sql_running']) {
                        $result['message'] = 'Replication threads not running';
                        $result['success'] = false;
                    } elseif ($result['lag'] > $this->critical_threshold) {
                        $result['message'] = "High replication lag: {$result['lag']} seconds";
                    } elseif ($result['lag'] > $this->alert_threshold) {
                        $result['message'] = "Moderate replication lag: {$result['lag']} seconds";
                    }
                }
            } else {
                $result['message'] = 'Failed to connect to slave';
            }
            
            $this->log("Slave {$slave['host']} check: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . " - " . $result['message']);
            
            $results[] = $result;
        }
        
        return $results;
    }
    
    /**
     * Cek konsistensi data antara master dan slave
     */
    private function checkDataConsistency() {
        $result = [
            'consistent' => true,
            'differences' => [],
            'tables_checked' => []
        ];
        
        try {
            // Ambil koneksi
            $master_conn = $this->db_replication->getMasterConnection();
            $slave_conn = $this->db_replication->getSlaveConnection();
            
            if (!$master_conn || !$slave_conn) {
                $result['consistent'] = false;
                $result['differences'][] = 'Cannot establish connections for consistency check';
                return $result;
            }
            
            // Daftar tabel yang akan dicek
            $tables = ['booking', 'user', 'admin'];
            
            foreach ($tables as $table) {
                $master_count = $this->getTableRowCount($master_conn, $table);
                $slave_count = $this->getTableRowCount($slave_conn, $table);
                
                $result['tables_checked'][$table] = [
                    'master_count' => $master_count,
                    'slave_count' => $slave_count,
                    'consistent' => ($master_count === $slave_count)
                ];
                
                if ($master_count !== $slave_count) {
                    $result['consistent'] = false;
                    $result['differences'][] = "Table $table: Master=$master_count, Slave=$slave_count";
                }
            }
            
        } catch (Exception $e) {
            $result['consistent'] = false;
            $result['differences'][] = 'Consistency check error: ' . $e->getMessage();
        }
        
        $this->log("Data consistency check: " . ($result['consistent'] ? 'CONSISTENT' : 'INCONSISTENT'));
        
        return $result;
    }
    
    /**
     * Cek performa sistem
     */
    private function checkPerformance() {
        $result = [
            'master_qps' => 0,
            'master_connections' => 0,
            'disk_space' => 0,
            'warnings' => []
        ];
        
        try {
            $master_conn = $this->db_replication->getMasterConnection();
            
            if ($master_conn) {
                // Queries per second
                $uptime_result = mysqli_query($master_conn, "SHOW GLOBAL STATUS LIKE 'Uptime'");
                $questions_result = mysqli_query($master_conn, "SHOW GLOBAL STATUS LIKE 'Questions'");
                
                if ($uptime_result && $questions_result) {
                    $uptime = mysqli_fetch_assoc($uptime_result)['Value'];
                    $questions = mysqli_fetch_assoc($questions_result)['Value'];
                    
                    if ($uptime > 0) {
                        $result['master_qps'] = round($questions / $uptime, 2);
                    }
                }
                
                // Active connections
                $conn_result = mysqli_query($master_conn, "SHOW GLOBAL STATUS LIKE 'Threads_connected'");
                if ($conn_result) {
                    $result['master_connections'] = mysqli_fetch_assoc($conn_result)['Value'];
                }
                
                // Warning thresholds
                if ($result['master_qps'] > 1000) {
                    $result['warnings'][] = "High query rate: {$result['master_qps']} QPS";
                }
                
                if ($result['master_connections'] > 100) {
                    $result['warnings'][] = "High connection count: {$result['master_connections']}";
                }
            }
            
            // Disk space check
            $backup_dir = __DIR__ . '/../backups';
            if (is_dir($backup_dir)) {
                $free_bytes = disk_free_space($backup_dir);
                $total_bytes = disk_total_space($backup_dir);
                
                if ($total_bytes > 0) {
                    $result['disk_space'] = round(($free_bytes / $total_bytes) * 100, 2);
                    
                    if ($result['disk_space'] < 10) {
                        $result['warnings'][] = "Low disk space: {$result['disk_space']}% free";
                    }
                }
            }
            
        } catch (Exception $e) {
            $result['warnings'][] = 'Performance check error: ' . $e->getMessage();
        }
        
        $this->log("Performance check completed. QPS: {$result['master_qps']}, Connections: {$result['master_connections']}");
        
        return $result;
    }
    
    /**
     * Hitung jumlah baris dalam tabel
     */
    private function getTableRowCount($conn, $table) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table`");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return (int)$row['count'];
        }
        return 0;
    }
    
    /**
     * Simpan hasil health check
     */
    private function saveResults($results) {
        $results_file = __DIR__ . '/../logs/health_check_results.json';
        
        // Simpan hasil terbaru
        file_put_contents($results_file, json_encode($results, JSON_PRETTY_PRINT));
        
        // Simpan ke history
        $history_file = __DIR__ . '/../logs/health_check_history.log';
        $history_entry = date('Y-m-d H:i:s') . " - " . $results['overall_status'] . " - " . json_encode($results) . "\n";
        file_put_contents($history_file, $history_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Kirim alert jika ada masalah
     */
    private function sendAlert($results) {
        $alert_message = "REPLICATION ALERT - Status: " . strtoupper($results['overall_status']) . "\n";
        $alert_message .= "Time: " . $results['timestamp'] . "\n";
        $alert_message .= "Details:\n";
        
        foreach ($results['checks'] as $check_type => $check_data) {
            if ($check_type === 'master' && !$check_data['success']) {
                $alert_message .= "- Master: " . $check_data['message'] . "\n";
            } elseif ($check_type === 'slaves') {
                foreach ($check_data as $slave) {
                    if (!$slave['success']) {
                        $alert_message .= "- Slave {$slave['host']}: " . $slave['message'] . "\n";
                    }
                }
            } elseif ($check_type === 'consistency' && !$check_data['consistent']) {
                $alert_message .= "- Data inconsistency detected\n";
                foreach ($check_data['differences'] as $diff) {
                    $alert_message .= "  * $diff\n";
                }
            }
        }
        
        // Simpan alert ke file
        $alert_file = __DIR__ . '/../logs/alerts.log';
        file_put_contents($alert_file, $alert_message . "\n", FILE_APPEND | LOCK_EX);
        
        $this->log("ALERT SENT: " . $results['overall_status']);
        
        // Di sini bisa ditambahkan integrasi dengan sistem notifikasi
        // seperti email, Slack, atau SMS
    }
    
    /**
     * Log pesan
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message\n";
        file_put_contents($this->log_file, $log_message, FILE_APPEND | LOCK_EX);
    }
}

// Jika script dijalankan langsung
if (php_sapi_name() === 'cli' || (!isset($_SERVER['HTTP_HOST']))) {
    $checker = new ReplicationHealthChecker();
    $results = $checker->runHealthCheck();
    
    echo "Health Check Completed\n";
    echo "Overall Status: " . strtoupper($results['overall_status']) . "\n";
    echo "Timestamp: " . $results['timestamp'] . "\n";
    
    if ($results['overall_status'] !== 'good') {
        echo "\nIssues found:\n";
        foreach ($results['checks'] as $check_type => $check_data) {
            if ($check_type === 'master' && !$check_data['success']) {
                echo "- Master: " . $check_data['message'] . "\n";
            }
        }
    }
}
?>
