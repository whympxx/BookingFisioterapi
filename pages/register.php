<?php
include __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    
    if ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak sama!";
    } else {
        // Cek apakah username sudah ada
        $stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_fetch_assoc($result)) {
            $error = "Username sudah terdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO user (username, password, email) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $email);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Registrasi gagal!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrasi User</title>
    <link rel="stylesheet" type="text/css" href="/BookingFisioterapi/assets/register.css">
</head>
<body>
<div class="register-container">
    <h2>Registrasi User</h2>
    <form method="post" class="register-form">
      <input type="text" name="username" required placeholder="Username"><br>
      <input type="email" name="email" required placeholder="Email"><br>
      <input type="password" name="password" required placeholder="Password"><br>
      <input type="password" name="confirm_password" required placeholder="Konfirmasi Password"><br>
      <button type="submit">Register</button>
      <div class="error-message"><?= isset($error) ? $error : '' ?></div>
      <div class="success-message"><?= isset($success) ? $success : '' ?></div>
    </form>
    <div style="margin-top:10px;">
      <a href="login.php">Sudah punya akun? Login</a>
    </div>
</div>
</body>
</html> 