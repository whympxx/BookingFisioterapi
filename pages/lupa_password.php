<?php
include __DIR__ . '/../includes/db.php';

$notif = '';
$new_password = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];
    if ($role === 'admin') {
        $q = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");
        $data = mysqli_fetch_assoc($q);
        if ($data) {
            $new_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "ss", $new_password_hash, $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $notif = 'Password admin berhasil direset.';
        } else {
            $notif = 'Username admin tidak ditemukan!';
        }
    } else if ($role === 'user') {
        $q = mysqli_query($conn, "SELECT * FROM user WHERE username='$username'");
        $data = mysqli_fetch_assoc($q);
        if ($data) {
            $new_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE user SET password = ? WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "ss", $new_password_hash, $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $notif = 'Password user berhasil direset.';
        } else {
            $notif = 'Username user tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lupa Password</title>
    <link rel="stylesheet" href="/BookingFisioterapi/assets/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h3>Lupa Password</h3>
    <form method="post">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role" class="form-control" required>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
    <?php if ($notif): ?>
        <div class="mt-3 alert alert-info"><?php echo htmlspecialchars($notif); ?></div>
    <?php endif; ?>
    <?php if ($new_password): ?>
        <div class="mt-3 alert alert-success">Password baru: <b><?php echo htmlspecialchars($new_password); ?></b><br>
        <span class="text-danger">Segera login dan ganti password Anda melalui menu Ubah Password!</span></div>
    <?php endif; ?>
    <a href="login.php" class="btn btn-link mt-3">Kembali ke Login</a>
</div>
</body>
</html> 