<?php
session_start();
$role = '';
$username = '';
if (isset($_SESSION['admin'])) {
    $role = 'admin';
    $username = $_SESSION['admin'];
} elseif (isset($_SESSION['user'])) {
    $role = 'user';
    $username = $_SESSION['user'];
} else {
    header('Location: login.php');
    exit;
}
include '../includes/db.php';

$notif = '';
$notif_class = 'text-info';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lama = $_POST['password_lama'];
    $baru = $_POST['password_baru'];
    $ulang = $_POST['password_ulang'];

    // Ambil password lama dengan prepared statement
    if ($role === 'admin') {
        $stmt = mysqli_prepare($conn, "SELECT password FROM admin WHERE username = ?");
    } else {
        $stmt = mysqli_prepare($conn, "SELECT password FROM user WHERE username = ?");
    }
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$data || !password_verify($lama, $data['password'])) {
        $notif = "Password lama salah!";
        $notif_class = 'text-danger';
    } elseif ($baru != $ulang) {
        $notif = "Password baru tidak cocok!";
        $notif_class = 'text-danger';
    } elseif (strlen($baru) < 6) {
        $notif = "Password baru minimal 6 karakter!";
        $notif_class = 'text-danger';
    } else {
        $baru_hash = password_hash($baru, PASSWORD_DEFAULT);
        if ($role === 'admin') {
            $stmt = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE username = ?");
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE user SET password = ? WHERE username = ?");
        }
        mysqli_stmt_bind_param($stmt, "ss", $baru_hash, $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $notif = "Password berhasil diperbarui.";
        $notif_class = 'text-success';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Ubah Password</title>
  <link rel="stylesheet" href="../assets/ubah_password.css">
</head>
<body>
<div class="container mt-5">
  <h3>Ubah Password <?= $role === 'admin' ? 'Admin' : 'User' ?></h3>
  <form method="post">
    <div class="form-group">
      <label>Password Lama</label>
      <input type="password" name="password_lama" class="form-control" required>
    </div>
    <div class="form-group">
      <label>Password Baru</label>
      <input type="password" name="password_baru" class="form-control" required>
    </div>
    <div class="form-group">
      <label>Ulangi Password Baru</label>
      <input type="password" name="password_ulang" class="form-control" required>
    </div>
    <div class="d-flex mt-3">
      <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
      <a href="<?= $role === 'admin' ? 'admin.php' : 'dashboard.php' ?>" class="btn btn-secondary">Kembali</a>
    </div>
  </form>
  <?php if ($notif): ?>
    <p class="mt-3 <?= $notif_class ?>"><?php echo htmlspecialchars($notif); ?></p>
  <?php endif; ?>
</div>
</body>
</html>
