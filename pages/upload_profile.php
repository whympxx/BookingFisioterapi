<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/../includes/db.php';

$username = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file['error'] === 0 && in_array($ext, $allowed) && $file['size'] <= 2*1024*1024) {
        $newName = $username . '_' . time() . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $uploadPath = $uploadDir . $newName;
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Update database
            $stmt = mysqli_prepare($conn, "UPDATE user SET profile_pic = ? WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "ss", $newName, $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Gagal upload file.';
        }
    } else {
        $error = 'File tidak valid (hanya jpg, jpeg, png, gif, max 2MB).';
    }
} else {
    $error = 'Tidak ada file yang diupload.';
}
if (isset($error)) {
    echo "<script>alert('$error');window.location='dashboard.php';</script>";
} 