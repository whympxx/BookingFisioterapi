<?php
/**
 * DATABASE REPLICATION MANAGER
 * Mengelola koneksi database dengan load balancing dan failover
 */

class DatabaseReplication {
    private $master_config;
    private $slave_configs;
    private $master_conn;
    private $slave_conns = [];
    private $current_slave_index = 0;
    private $max_retries = 3;
    private $connection_timeout = 5;
    
    public function __construct() {
        $this->master_config = [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'database' => 'db_fisioterapi',
            'port' => 3306
        ];
        
        $this->slave_configs = [
            [
                'host' => 'localhost',
                'user' => 'root',
                'password' => '',
                'database' => 'slave_fisioterapi',
                'port' => 3306
            ],
            [
                'host' => '192.168.1.102',
                'user' => 'root',
                'password' => '',
                'database' => 'db_fisioterapi',
                'port' => 3306
            ]
        ];
    }
    
    /**
     * Mendapatkan koneksi master untuk operasi write
     */
    public function getMasterConnection() {
        if (!$this->master_conn || !$this->isConnectionAlive($this->master_conn)) {
            $this->master_conn = $this->createConnection($this->master_config);
        }
        return $this->master_conn;
    }
    
    /**
     * Mendapatkan koneksi slave untuk operasi read dengan load balancing
     */
    public function getSlaveConnection() {
        // Coba koneksi slave yang tersedia dengan round-robin
        $attempts = 0;
        $total_slaves = count($this->slave_configs);
        
        while ($attempts < $total_slaves) {
            $slave_index = ($this->current_slave_index + $attempts) % $total_slaves;
            
            if (!isset($this->slave_conns[$slave_index]) || 
                !$this->isConnectionAlive($this->slave_conns[$slave_index])) {
                $this->slave_conns[$slave_index] = $this->createConnection($this->slave_configs[$slave_index]);
            }
            
            if ($this->slave_conns[$slave_index]) {
                $this->current_slave_index = ($slave_index + 1) % $total_slaves;
                return $this->slave_conns[$slave_index];
            }
            
            $attempts++;
        }
        
        // Jika semua slave gagal, fallback ke master
        $this->logError("All slave connections failed, falling back to master");
        return $this->getMasterConnection();
    }
    
    /**
     * Membuat koneksi database dengan retry mechanism
     */
    private function createConnection($config) {
        $retries = 0;
        
        while ($retries < $this->max_retries) {
            try {
                $conn = mysqli_connect(
                    $config['host'], 
                    $config['user'], 
                    $config['password'], 
                    $config['database'],
                    $config['port']
                );
                
                if ($conn) {
                    mysqli_set_charset($conn, 'utf8');
                    return $conn;
                }
            } catch (Exception $e) {
                $this->logError("Connection failed: " . $e->getMessage());
            }
            
            $retries++;
            if ($retries < $this->max_retries) {
                sleep(1); // Wait 1 second before retry
            }
        }
        
        $this->logError("Failed to connect to {$config['host']} after {$this->max_retries} attempts");
        return false;
    }
    
    /**
     * Mengecek apakah koneksi masih hidup
     */
    private function isConnectionAlive($conn) {
        return $conn && mysqli_ping($conn);
    }
    
    /**
     * Eksekusi query SELECT dengan load balancing
     */
    public function executeSelect($query, $params = []) {
        $conn = $this->getSlaveConnection();
        return $this->executeQuery($conn, $query, $params);
    }
    
    /**
     * Eksekusi query INSERT/UPDATE/DELETE pada master
     */
    public function executeWrite($query, $params = []) {
        $conn = $this->getMasterConnection();
        return $this->executeQuery($conn, $query, $params);
    }
    
    /**
     * Eksekusi query dengan prepared statement
     */
    private function executeQuery($conn, $query, $params = []) {
        if (!$conn) {
            return false;
        }
        
        if (empty($params)) {
            return mysqli_query($conn, $query);
        }
        
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            
            $result = mysqli_stmt_execute($stmt);
            
            if (strpos(strtoupper(trim($query)), 'SELECT') === 0) {
                $result = mysqli_stmt_get_result($stmt);
            }
            
            mysqli_stmt_close($stmt);
            return $result;
        }
        
        return false;
    }
    
    /**
     * Mendapatkan status replikasi
     */
    public function getReplicationStatus() {
        $status = [
            'master' => $this->getMasterStatus(),
            'slaves' => $this->getSlaveStatus()
        ];
        
        return $status;
    }
    
    /**
     * Mendapatkan status master
     */
    private function getMasterStatus() {
        $conn = $this->getMasterConnection();
        if (!$conn) {
            return ['status' => 'disconnected'];
        }
        
        $result = mysqli_query($conn, "SHOW MASTER STATUS");
        $master_status = $result ? mysqli_fetch_assoc($result) : null;
        
        return [
            'status' => 'connected',
            'host' => $this->master_config['host'],
            'binary_log' => $master_status
        ];
    }
    
    /**
     * Mendapatkan status semua slave
     */
    private function getSlaveStatus() {
        $slaves_status = [];
        
        foreach ($this->slave_configs as $index => $config) {
            $conn = $this->createConnection($config);
            
            if ($conn) {
                $result = mysqli_query($conn, "SHOW SLAVE STATUS");
                $slave_status = $result ? mysqli_fetch_assoc($result) : null;
                
                $slaves_status[] = [
                    'index' => $index,
                    'host' => $config['host'],
                    'status' => 'connected',
                    'replication' => $slave_status
                ];
                
                mysqli_close($conn);
            } else {
                $slaves_status[] = [
                    'index' => $index,
                    'host' => $config['host'],
                    'status' => 'disconnected',
                    'replication' => null
                ];
            }
        }
        
        return $slaves_status;
    }
    
    /**
     * Log error ke file
     */
    private function logError($message) {
        $log_file = __DIR__ . '/../logs/replication_errors.log';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message\n";
        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Tutup semua koneksi
     */
    public function closeConnections() {
        if ($this->master_conn) {
            mysqli_close($this->master_conn);
        }
        
        foreach ($this->slave_conns as $conn) {
            if ($conn) {
                mysqli_close($conn);
            }
        }
    }
    
    /**
     * Destruktor untuk membersihkan koneksi
     */
    public function __destruct() {
        $this->closeConnections();
    }
}

// Singleton instance
$db_replication = new DatabaseReplication();
?>
