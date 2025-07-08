<?php
/**
 * Database configuration for Vercel deployment
 * Using environment variables for database connection
 */

// Get environment variables or use defaults for development
$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_NAME'] ?? 'db_fisioterapi';

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Function to get database connection
function getConnection() {
    global $conn;
    return $conn;
}

// Function to close connection
function closeConnection() {
    global $conn;
    if ($conn) {
        mysqli_close($conn);
    }
}
?>
