<?php
session_start();
include __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u = $_POST['username'];
    $p = $_POST['password'];
    $role = $_POST['role'];
    if ($role === 'admin') {
        $q = mysqli_query($conn, "SELECT * FROM admin WHERE username='$u'");
        $data = mysqli_fetch_assoc($q);
        if ($data && password_verify($p, $data['password'])) {
            $_SESSION['admin'] = $u;
            header('Location: admin.php');
            exit();
        } else {
            $error = "Login admin gagal!";
        }
    } else if ($role === 'user') {
        $stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $u);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        if ($data && password_verify($p, $data['password'])) {
            $_SESSION['user'] = $u;
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Login user gagal!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/BookingFisioterapi/assets/login.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            margin: auto;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 32px 28px 24px 28px;
            background: #fff;
        }
        .login-card .logo {
            display: block;
            margin: 0 auto 18px auto;
            width: 80px;
        }
        .login-card h2 {
            text-align: center;
            margin-bottom: 24px;
            font-weight: 700;
            color: #2d3a4b;
        }
        .login-card .form-control {
            border-radius: 8px;
        }
        .login-card .btn-primary {
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .login-card .btn-primary:hover {
            background: #2d3a4b;
        }
        .error-message {
            color: #fff;
            background: #e74c3c;
            border-radius: 6px;
            padding: 8px 0;
            margin-top: 12px;
            text-align: center;
            font-size: 0.95em;
        }
        .login-card .links {
            text-align: center;
            margin-top: 18px;
        }
        .login-card .links a {
            color: #2d3a4b;
            text-decoration: none;
            margin: 0 6px;
            transition: color 0.2s;
        }
        .login-card .links a:hover {
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="login-card">
    <img src="/BookingFisioterapi/assets/img/ikon_fisioterapi.jpg" alt="Logo" class="logo">
    <h2>Login</h2>
    <form method="post">
      <div class="form-group">
        <input type="text" name="username" class="form-control" required placeholder="Username">
      </div>
      <div class="form-group">
        <input type="password" name="password" class="form-control" required placeholder="Password">
      </div>
      <div class="form-group">
        <select name="role" class="form-control" required>
          <option value="admin">Admin</option>
          <option value="user">User</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Login</button>
      <?php if (isset($error) && $error): ?>
        <div class="error-message mt-2"><?= $error ?></div>
      <?php endif; ?>
    </form>
    <div class="links">
      <a href="lupa_password.php">Lupa Password?</a> |
      <a href="register.php">Belum punya akun? Registrasi</a>
    </div>
</div>
<script src="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/js/jquery-3.5.1.min.js"></script>
<script src="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
