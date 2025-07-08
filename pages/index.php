<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
?>

<?php include __DIR__ . '/../includes/db.php'; ?>
<?php
// Check if database connection is working
if (!isset($conn) || !$conn) {
    die("Database connection error. Please check your configuration.");
}

// Debug information (remove in production)
$debug = false; // Set to true to see debug info
if ($debug) {
    echo "<!-- Debug: Session user: " . (isset($_SESSION['user']) ? $_SESSION['user'] : 'not set') . " -->";
    echo "<!-- Debug: Session admin: " . (isset($_SESSION['admin']) ? $_SESSION['admin'] : 'not set') . " -->";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Fisioterapi - Form Pemesanan</title>
  <link rel="stylesheet" href="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/BookingFisioterapi/assets/booking.css">
  <link rel="stylesheet" href="/BookingFisioterapi/assets/chatbot.css">
</head>
<body>
  <!-- Header Section -->
  <div class="header-section">
    <div class="container">
      <div class="header-content">
        <div class="logo-section">
          <i class="fas fa-heartbeat logo-icon"></i>
          <h1 class="logo-text">FisioCare</h1>
        </div>
        <div class="header-info">
          <p class="welcome-text">Selamat datang di sistem booking fisioterapi</p>
          <p class="subtitle">Pesan layanan fisioterapi Anda dengan mudah dan cepat</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-container">
    <div class="booking-container">
      <!-- Back Button -->
      <div class="back-section">
        <a href="dashboard.php" class="btn btn-back">
          <i class="fas fa-arrow-left arrow-icon"></i>
          Kembali ke Dashboard
        </a>
      </div>

      <!-- Booking Card -->
      <div class="booking-card">
        <div class="card-header">
          <div class="header-icon">
            <i class="fas fa-calendar-check"></i>
          </div>
          <h2>Form Booking Pelayanan Fisioterapi</h2>
          <p class="card-subtitle">Isi form di bawah ini untuk memesan layanan fisioterapi</p>
        </div>

        <form action="process_booking.php" method="post" class="booking-form">
          <!-- Personal Information Section -->
          <div class="form-section">
            <h3 class="section-title">
              <i class="fas fa-user"></i>
              Informasi Pribadi
            </h3>
            <div class="form-group">
              <label for="nama" class="form-label">
                <i class="fas fa-user-circle"></i>
                Nama Lengkap
              </label>
              <input type="text" id="nama" name="nama" class="form-control with-icon" placeholder="Masukkan nama lengkap Anda" required>
            </div>
            <div class="form-group">
              <label for="email" class="form-label">
                <i class="fas fa-envelope"></i>
                Email
              </label>
              <input type="email" id="email" name="email" class="form-control with-icon" placeholder="contoh@email.com" required>
            </div>
            <div class="form-group">
              <label for="no_hp" class="form-label">
                <i class="fas fa-phone"></i>
                Nomor HP
              </label>
              <input type="tel" id="no_hp" name="no_hp" class="form-control with-icon" placeholder="08xxxxxxxxxx" required>
            </div>
          </div>

          <!-- Appointment Section -->
          <div class="form-section">
            <h3 class="section-title">
              <i class="fas fa-clock"></i>
              Jadwal Konsultasi
            </h3>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="tanggal_booking" class="form-label">
                  <i class="fas fa-calendar"></i>
                  Tanggal
                </label>
                <input type="date" id="tanggal_booking" name="tanggal_booking" class="form-control" required>
              </div>
              <div class="form-group col-md-6">
                <label for="waktu_booking" class="form-label">
                  <i class="fas fa-clock"></i>
                  Waktu
                </label>
                <input type="time" id="waktu_booking" name="waktu_booking" class="form-control" required>
              </div>
            </div>
          </div>

          <!-- Service Section -->
          <div class="form-section">
            <h3 class="section-title">
              <i class="fas fa-stethoscope"></i>
              Pilihan Layanan
            </h3>
            <div class="form-group">
              <label for="layanan" class="form-label">
                <i class="fas fa-list"></i>
                Jenis Layanan
              </label>
              <select id="layanan" name="layanan" class="form-control" required>
                <option value="">Pilih layanan fisioterapi</option>
                <option value="Terapi Lutut">Terapi Lutut</option>
                <option value="Rehabilitasi Pasca Cedera">Rehabilitasi Pasca Cedera</option>
                <option value="Pijat Medis">Pijat Medis</option>
                <option value="Terapi Tulang Belakang">Terapi Tulang Belakang</option>
                <option value="Terapi Stroke">Terapi Stroke</option>
                <option value="Terapi Anak">Terapi Anak</option>
              </select>
            </div>
          </div>

          <!-- Notes Section -->
          <div class="form-section">
            <h3 class="section-title">
              <i class="fas fa-notes-medical"></i>
              Catatan Tambahan
            </h3>
            <div class="form-group">
              <label for="catatan" class="form-label">
                <i class="fas fa-edit"></i>
                Catatan (Opsional)
              </label>
              <textarea id="catatan" name="catatan" class="form-control" placeholder="Berikan informasi tambahan tentang kondisi atau keluhan Anda..." rows="4"></textarea>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="form-actions text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg shadow-sm transition">
              <i class="fas fa-check-circle"></i>
              Konfirmasi Booking
            </button>
          </div>
        </form>
      </div>

      <!-- Info Cards -->
      <div class="info-cards row mt-5">
        <div class="info-card col-md-4 mb-3 animated-card">
          <div class="info-icon bg-primary text-white mb-2"><i class="fas fa-shield-alt"></i></div>
          <h4>Terpercaya</h4>
          <p>Layanan fisioterapi profesional dengan tenaga ahli berpengalaman</p>
        </div>
        <div class="info-card col-md-4 mb-3 animated-card">
          <div class="info-icon bg-success text-white mb-2"><i class="fas fa-clock"></i></div>
          <h4>Fleksibel</h4>
          <p>Jadwal yang dapat disesuaikan dengan kebutuhan Anda</p>
        </div>
        <div class="info-card col-md-4 mb-3 animated-card">
          <div class="info-icon bg-info text-white mb-2"><i class="fas fa-home"></i></div>
          <h4>Nyaman</h4>
          <p>Layanan dapat dilakukan di rumah atau di klinik</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer mt-auto py-3 bg-light border-top shadow-sm">
    <div class="container text-center">
      <p class="mb-0">&copy; 2024 FisioCare. Semua hak dilindungi.</p>
    </div>
  </footer>

  <!-- Chatbot Toggle Button -->
  <div class="chatbot-toggle" id="chatbotToggle">
    <i class="fas fa-comments"></i>
  </div>

  <!-- Chatbot Modal -->
  <div class="chatbot-modal" id="chatbotModal">
    <div class="chatbot-header">
      <h6><i class="fas fa-robot mr-2"></i>Asisten Fisioterapi</h6>
      <button class="chatbot-close" id="chatbotClose">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="chatbot-messages" id="chatbotMessages">
      <!-- Messages will be added here -->
    </div>
    <div class="chatbot-input">
      <form id="chatbotForm">
        <input type="text" id="chatbotInput" placeholder="Ketik pertanyaan Anda..." autocomplete="off">
        <button type="submit">
          <i class="fas fa-paper-plane"></i>
        </button>
      </form>
    </div>
  </div>

  <script src="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/js/jquery-3.5.1.min.js"></script>
  <script src="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/js/bootstrap.bundle.min.js"></script>
  <script src="/BookingFisioterapi/assets/chatbot.js"></script>
  <script>
    // Set minimum date to today
    document.addEventListener('DOMContentLoaded', function() {
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('tanggal_booking').min = today;
      
      // Set default time to current time + 1 hour
      const now = new Date();
      now.setHours(now.getHours() + 1);
      const timeString = now.toTimeString().slice(0, 5);
      document.getElementById('waktu_booking').value = timeString;
    });

    // Form validation
    document.querySelector('.booking-form').addEventListener('submit', function(e) {
      const nama = document.getElementById('nama').value.trim();
      const email = document.getElementById('email').value.trim();
      const no_hp = document.getElementById('no_hp').value.trim();
      const layanan = document.getElementById('layanan').value;
      const tanggal = document.getElementById('tanggal_booking').value;
      const waktu = document.getElementById('waktu_booking').value;

      if (!nama || !email || !no_hp || !layanan || !tanggal || !waktu) {
        e.preventDefault();
        alert('Mohon lengkapi semua field yang wajib diisi.');
        return;
      }

      // Email validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Format email tidak valid.');
        return;
      }

      // Phone validation
      const phoneRegex = /^08[0-9]{8,11}$/;
      if (!phoneRegex.test(no_hp)) {
        e.preventDefault();
        alert('Format nomor HP tidak valid. Gunakan format 08xxxxxxxxxx');
        return;
      }

      // Date validation
      const selectedDate = new Date(tanggal);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      if (selectedDate < today) {
        e.preventDefault();
        alert('Tanggal booking tidak boleh di masa lalu.');
        return;
      }
    });
  </script>
</body>
</html>
