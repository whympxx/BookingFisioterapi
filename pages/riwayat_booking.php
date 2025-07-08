<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/../includes/db.php';

// Ambil data user
$username = $_SESSION['user'];
$stmt = mysqli_prepare($conn, "SELECT email FROM user WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
$email = $user ? $user['email'] : '';

// Ambil riwayat booking user
$bookings = [];
if ($email) {
    $q = mysqli_prepare($conn, "SELECT tanggal_booking, waktu_booking, layanan, catatan, status FROM booking WHERE email = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($q, "s", $email);
    mysqli_stmt_execute($q);
    $res = mysqli_stmt_get_result($q);
    while ($row = mysqli_fetch_assoc($res)) {
        $bookings[] = $row;
    }
    mysqli_stmt_close($q);
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Booking Saya</title>
    <link rel="stylesheet" href="../assets/bootstrap-4.6.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/riwayat_booking.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="riwayat-header text-center py-4 mb-4">
        <h1 class="mb-2">Riwayat Booking Fisioterapi</h1>
        <a href="dashboard.php" class="btn btn-outline-primary">Kembali ke Dashboard</a>
    </div>
    <div class="container mb-5">
        <div class="card shadow-lg p-4">
            <h3 class="mb-4 text-center">Daftar Booking Anda</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover riwayat-table">
                    <thead class="thead-dark">
                        <tr>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Layanan</th>
                            <th>Catatan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($bookings) > 0): ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td><?= htmlspecialchars($b['tanggal_booking']) ?></td>
                                    <td><?= htmlspecialchars($b['waktu_booking']) ?></td>
                                    <td><?= htmlspecialchars($b['layanan']) ?></td>
                                    <td><?= htmlspecialchars($b['catatan']) ?></td>
                                    <td>
                                        <?php if ($b['status'] == 'Menunggu'): ?>
                                            <span class="badge badge-warning">Menunggu</span>
                                        <?php elseif ($b['status'] == 'Dikonfirmasi'): ?>
                                            <span class="badge badge-info">Dikonfirmasi</span>
                                        <?php elseif ($b['status'] == 'Selesai'): ?>
                                            <span class="badge badge-success">Selesai</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada riwayat booking.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <footer class="footer mt-auto py-3 bg-light border-top shadow-sm">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 FisioCare. Semua hak dilindungi.</p>
        </div>
    </footer>
</body>
</html> 