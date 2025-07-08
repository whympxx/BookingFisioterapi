<?php
include __DIR__ . '/../includes/db.php';

// Validasi sederhana
if (
    empty($_POST['nama']) ||
    empty($_POST['email']) ||
    empty($_POST['no_hp']) ||
    empty($_POST['tanggal_booking']) ||
    empty($_POST['waktu_booking']) ||
    empty($_POST['layanan'])
) {
    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Gagal</title>
        <link rel="stylesheet" href="../assets/booking_result.css">
    </head>
    <body>
        <div class="booking-result-container">
            <div class="error">Semua field wajib diisi!</div>
            <a href="index.php">Kembali</a>
        </div>
    </body>
    </html>';
    exit;
}

$nama = $_POST['nama'];
$email = $_POST['email'];
$no_hp = $_POST['no_hp'];
$tanggal = $_POST['tanggal_booking'];
$waktu = $_POST['waktu_booking'];
$layanan = $_POST['layanan'];
$catatan = isset($_POST['catatan']) ? $_POST['catatan'] : '';

$stmt = mysqli_prepare($conn, "INSERT INTO booking (nama, email, no_hp, tanggal_booking, waktu_booking, layanan, catatan) VALUES (?, ?, ?, ?, ?, ?, ?)");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssss", $nama, $email, $no_hp, $tanggal, $waktu, $layanan, $catatan);
    if (mysqli_stmt_execute($stmt)) {
        echo '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Booking Berhasil</title>
            <link rel="stylesheet" href="../assets/booking_result.css">
        </head>
        <body>
            <div class="booking-result-container">
                <div class="success">Booking berhasil!</div>
                <a href="index.php">Kembali</a>
            </div>
        </body>
        </html>';
    } else {
        echo '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Booking Gagal</title>
            <link rel="stylesheet" href="../assets/booking_result.css">
        </head>
        <body>
            <div class="booking-result-container">
                <div class="error">Terjadi kesalahan saat booking. Silakan coba lagi.</div>
                <a href="index.php">Kembali</a>
            </div>
        </body>
        </html>';
    }
    mysqli_stmt_close($stmt);
} else {
    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Gagal</title>
        <link rel="stylesheet" href="../assets/booking_result.css">
    </head>
    <body>
        <div class="booking-result-container">
            <div class="error">Terjadi kesalahan pada server. Silakan coba lagi.</div>
            <a href="index.php">Kembali</a>
        </div>
    </body>
    </html>';
}

mysqli_close($conn);
?>
