<?php
/**
 * API endpoint for database operations
 * Handles database queries for Vercel deployment
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../includes/db_vercel.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action);
            break;
        case 'POST':
            handlePostRequest($action);
            break;
        case 'PUT':
            handlePutRequest($action);
            break;
        case 'DELETE':
            handleDeleteRequest($action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGetRequest($action) {
    global $conn;
    
    switch ($action) {
        case 'bookings':
            $result = mysqli_query($conn, "SELECT * FROM booking ORDER BY id DESC");
            $bookings = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $bookings[] = $row;
            }
            echo json_encode($bookings);
            break;
            
        case 'users':
            $result = mysqli_query($conn, "SELECT id, username, email FROM user ORDER BY id DESC");
            $users = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
            echo json_encode($users);
            break;
            
        case 'stats':
            $stats = [
                'total_bookings' => getCount('booking'),
                'total_users' => getCount('user'),
                'total_admins' => getCount('admin')
            ];
            echo json_encode($stats);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
    }
}

function handlePostRequest($action) {
    global $conn;
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'booking':
            $stmt = mysqli_prepare($conn, "INSERT INTO booking (nama, email, no_hp, tanggal_booking, waktu_booking, layanan, catatan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssssss", 
                $input['nama'], 
                $input['email'], 
                $input['no_hp'], 
                $input['tanggal_booking'], 
                $input['waktu_booking'], 
                $input['layanan'], 
                $input['catatan'], 
                $input['status']
            );
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
            } else {
                throw new Exception('Failed to create booking');
            }
            break;
            
        case 'user':
            $stmt = mysqli_prepare($conn, "INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", 
                $input['username'], 
                $input['email'], 
                password_hash($input['password'], PASSWORD_DEFAULT)
            );
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
            } else {
                throw new Exception('Failed to create user');
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
    }
}

function handlePutRequest($action) {
    global $conn;
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'booking':
            $stmt = mysqli_prepare($conn, "UPDATE booking SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $input['status'], $input['id']);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to update booking');
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
    }
}

function handleDeleteRequest($action) {
    global $conn;
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'booking':
            $stmt = mysqli_prepare($conn, "DELETE FROM booking WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $input['id']);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to delete booking');
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
    }
}

function getCount($table) {
    global $conn;
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table`");
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}
?>
