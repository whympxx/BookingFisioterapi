<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../includes/db.php';

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data booking dari database
$result = mysqli_query($conn, "SELECT * FROM booking ORDER BY id DESC");
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
$no = 1;
?>
<!DOCTYPE html>
<html>
<head>
  <title>Data Booking</title>
  <link rel="stylesheet" href="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/BookingFisioterapi/assets/admin.css">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .admin-header {
      background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
      color: #fff;
      padding: 30px 0 20px 0;
      margin-bottom: 30px;
      border-radius: 0 0 20px 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.07);
    }
    .btn-custom {
      background: #007bff;
      color: #fff;
      border-radius: 20px;
      padding: 8px 22px;
      margin-right: 10px;
      transition: background 0.2s;
      border: none;
      font-weight: 500;
    }
    .btn-custom:hover {
      background: #0056b3;
      color: #fff;
    }
    .card {
      border-radius: 18px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .table thead th {
      background: #007bff;
      color: #fff;
      border: none;
    }
    .table-hover tbody tr:hover {
      background-color: #e9f5ff;
    }
    .table td, .table th {
      vertical-align: middle;
    }
    select, button[type=submit] {
      border-radius: 8px;
      padding: 4px 10px;
      border: 1px solid #ced4da;
    }
    button[type=submit] {
      background: #28a745;
      color: #fff;
      border: none;
      margin-left: 5px;
      transition: background 0.2s;
    }
    button[type=submit]:hover {
      background: #218838;
    }
  </style>
</head>
<body>
  <div class="admin-header text-center">
    <h1 class="mb-2">Panel Admin Booking Fisioterapi</h1>
    <div>
      <a href="index.php" class="btn btn-custom">Kembali</a>
      <a href="ubah_password.php" class="btn btn-custom">Ubah Password</a>
    </div>
  </div>
  <div class="container mb-5">
    <div class="card p-4">
      <h3 class="mb-4">Daftar Booking Pasien</h3>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>No</th><th>Nama</th><th>Email</th><th>No HP</th>
              <th>Tanggal</th><th>Waktu</th><th>Layanan</th><th>Catatan</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
              <tr>
                <td><?= $no ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                <td><?= htmlspecialchars($row['tanggal_booking']) ?></td>
                <td><?= htmlspecialchars($row['waktu_booking']) ?></td>
                <td><?= htmlspecialchars($row['layanan']) ?></td>
                <td><?= htmlspecialchars($row['catatan']) ?></td>
                <td>
                  <form method="post" action="update_status.php" class="d-flex align-items-center">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <select name="status" class="mr-2">
                      <option value="Menunggu"<?= $row['status']=='Menunggu' ? ' selected' : '' ?>>Menunggu</option>
                      <option value="Dikonfirmasi"<?= $row['status']=='Dikonfirmasi' ? ' selected' : '' ?>>Dikonfirmasi</option>
                      <option value="Selesai"<?= $row['status']=='Selesai' ? ' selected' : '' ?>>Selesai</option>
                    </select>
                    <button type="submit">Update</button>
                  </form>
                </td>
              </tr>
            <?php $no++; } ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center">Tidak ada data booking.</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
