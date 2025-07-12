<?php
/**
 * Database configuration for AwardSpace hosting
 * Update these values with your AwardSpace database credentials
 */

// AwardSpace Database Configuration
$host = "your-database-host.awardspace.com";     // Akan diberikan oleh AwardSpace
$user = "your-database-username";                // Username database AwardSpace
$password = "your-database-password";            // Password database AwardSpace  
$database = "your-database-name";                // Nama database AwardSpace

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

// Test connection (optional - remove after testing)
function testConnection() {
    global $conn;
    if ($conn) {
        echo "Database connection successful!";
        return true;
    } else {
        echo "Database connection failed: " . mysqli_connect_error();
        return false;
    }
}
?>
