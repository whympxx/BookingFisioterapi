<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/../includes/db.php';

// Ambil data user
$username = $_SESSION['user'];
$stmt = mysqli_prepare($conn, "SELECT username, email, profile_pic FROM user WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
$profilePic = $user['profile_pic'] ? '/BookingFisioterapi/uploads/' . $user['profile_pic'] : '/BookingFisioterapi/assets/img/default_profile.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Fisioterapi - Fisioterapi Unggul Sehat</title>
    <link rel="stylesheet" href="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/BookingFisioterapi/assets/layanan.css">
    <link rel="stylesheet" href="/BookingFisioterapi/assets/chatbot.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="/BookingFisioterapi/assets/img/ikon_fisioterapi.jpg" alt="Logo" class="navbar-logo me-2">
                <span class="brand-text">Fisioterapi Unggul Sehat</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="layanan.php">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Booking</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-toggle="dropdown">
                            <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profil" class="profile-pic mr-2">
                            <span><?= htmlspecialchars($user['username']) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" id="gantiFotoBtn"><i class="fas fa-camera mr-2"></i>Ganti Foto</a></li>
                            <li><a class="dropdown-item" href="ubah_password.php"><i class="fas fa-key mr-2"></i>Ubah Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="hero-title">Layanan Fisioterapi Profesional</h1>
                    <p class="hero-subtitle">Kami menyediakan berbagai layanan fisioterapi berkualitas tinggi dengan tenaga ahli berpengalaman untuk membantu pemulihan dan kesehatan Anda.</p>
                    <a href="index.php" class="btn btn-primary btn-lg">Booking Sekarang</a>
                </div>
                <div class="col-lg-6">
                    <img src="/BookingFisioterapi/assets/img/hero-fisioterapi.jpg" alt="Fisioterapi" class="hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Layanan Kami</h2>
                <p class="section-subtitle">Pilih layanan yang sesuai dengan kebutuhan Anda</p>
            </div>
            
            <div class="row g-4">
                <!-- Terapi Lutut -->
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="/BookingFisioterapi/assets/img/terapi_lutut.jpg" alt="Terapi Lutut">
                            <div class="service-overlay">
                                <i class="fas fa-knee"></i>
                            </div>
                        </div>
                        <div class="service-content">
                            <h3>Terapi Lutut</h3>
                            <p>Perawatan khusus untuk masalah pada lutut, termasuk nyeri, cedera, dan pemulihan pasca operasi.</p>
                            <ul class="service-features">
                                <li><i class="fas fa-check"></i> Penanganan nyeri lutut</li>
                                <li><i class="fas fa-check"></i> Rehabilitasi pasca operasi</li>
                                <li><i class="fas fa-check"></i> Penguatan otot lutut</li>
                                <li><i class="fas fa-check"></i> Konsultasi ahli</li>
                            </ul>
                            <div class="service-price">
                                <span class="price">Rp 150.000</span>
                                <span class="duration">/ sesi</span>
                            </div>
                            <a href="index.php" class="btn btn-outline-primary w-100">Booking Layanan</a>
                        </div>
                    </div>
                </div>

                <!-- Rehabilitasi Pasca Cedera -->
                <div class="col-lg-4 col-md-6">
                    <div class="service-card featured">
                        <div class="service-badge">Terpopuler</div>
                        <div class="service-image">
                            <img src="/BookingFisioterapi/assets/img/rehab_cedera.jpg" alt="Rehabilitasi Pasca Cedera">
                            <div class="service-overlay">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                        </div>
                        <div class="service-content">
                            <h3>Rehabilitasi Pasca Cedera</h3>
                            <p>Program pemulihan komprehensif untuk cedera olahraga, kecelakaan, atau kondisi medis lainnya.</p>
                            <ul class="service-features">
                                <li><i class="fas fa-check"></i> Program pemulihan personal</li>
                                <li><i class="fas fa-check"></i> Terapi manual</li>
                                <li><i class="fas fa-check"></i> Latihan fungsional</li>
                                <li><i class="fas fa-check"></i> Monitoring progress</li>
                            </ul>
                            <div class="service-price">
                                <span class="price">Rp 200.000</span>
                                <span class="duration">/ sesi</span>
                            </div>
                            <a href="index.php" class="btn btn-primary w-100">Booking Layanan</a>
                        </div>
                    </div>
                </div>

                <!-- Pijat Medis -->
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="/BookingFisioterapi/assets/img/pijat_medis.jpg" alt="Pijat Medis">
                            <div class="service-overlay">
                                <i class="fas fa-hands"></i>
                            </div>
                        </div>
                        <div class="service-content">
                            <h3>Pijat Medis</h3>
                            <p>Pijat terapeutik untuk meredakan ketegangan otot, meningkatkan sirkulasi darah, dan relaksasi.</p>
                            <ul class="service-features">
                                <li><i class="fas fa-check"></i> Pijat relaksasi</li>
                                <li><i class="fas fa-check"></i> Pijat terapeutik</li>
                                <li><i class="fas fa-check"></i> Reduksi ketegangan</li>
                                <li><i class="fas fa-check"></i> Peningkatan fleksibilitas</li>
                            </ul>
                            <div class="service-price">
                                <span class="price">Rp 120.000</span>
                                <span class="duration">/ sesi</span>
                            </div>
                            <a href="index.php" class="btn btn-outline-primary w-100">Booking Layanan</a>
                        </div>
                    </div>
                </div>

                <!-- Terapi Tulang Belakang -->
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="/BookingFisioterapi/assets/img/terapi_tulang_belakang.jpg" alt="Terapi Tulang Belakang">
                            <div class="service-overlay">
                                <i class="fas fa-spine"></i>
                            </div>
                        </div>
                        <div class="service-content">
                            <h3>Terapi Tulang Belakang</h3>
                            <p>Penanganan khusus untuk masalah tulang belakang seperti nyeri punggung, skoliosis, dan herniated disc.</p>
                            <ul class="service-features">
                                <li><i class="fas fa-check"></i> Penanganan nyeri punggung</li>
                                <li><i class="fas fa-check"></i> Terapi skoliosis</li>
                                <li><i class="fas fa-check"></i> Postur correction</li>
                                <li><i class="fas fa-check"></i> Core strengthening</li>
                            </ul>
                            <div class="service-price">
                                <span class="price">Rp 180.000</span>
                                <span class="duration">/ sesi</span>
                            </div>
                            <a href="index.php" class="btn btn-outline-primary w-100">Booking Layanan</a>
                        </div>
                    </div>
                </div>

                <!-- Terapi Stroke -->
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="/BookingFisioterapi/assets/img/terapi_stroke.jpg" alt="Terapi Stroke">
                            <div class="service-overlay">
                                <i class="fas fa-brain"></i>
                            </div>
                        </div>
                        <div class="service-content">
                            <h3>Terapi Stroke</h3>
                            <p>Program rehabilitasi komprehensif untuk pasien pasca stroke dengan fokus pada pemulihan fungsi motorik.</p>
                            <ul class="service-features">
                                <li><i class="fas fa-check"></i> Rehabilitasi motorik</li>
                                <li><i class="fas fa-check"></i> Latihan keseimbangan</li>
                                <li><i class="fas fa-check"></i> Terapi wicara</li>
                                <li><i class="fas fa-check"></i> Aktivitas sehari-hari</li>
                            </ul>
                            <div class="service-price">
                                <span class="price">Rp 250.000</span>
                                <span class="duration">/ sesi</span>
                            </div>
                            <a href="index.php" class="btn btn-outline-primary w-100">Booking Layanan</a>
                        </div>
                    </div>
                </div>

                <!-- Terapi Anak -->
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="/BookingFisioterapi/assets/img/terapi_anak.jpg" alt="Terapi Anak">
                            <div class="service-overlay">
                                <i class="fas fa-child"></i>
                            </div>
                        </div>
                        <div class="service-content">
                            <h3>Terapi Anak</h3>
                            <p>Layanan fisioterapi khusus untuk anak-anak dengan gangguan perkembangan motorik dan sensorik.</p>
                            <ul class="service-features">
                                <li><i class="fas fa-check"></i> Terapi perkembangan</li>
                                <li><i class="fas fa-check"></i> Latihan motorik</li>
                                <li><i class="fas fa-check"></i> Sensori integrasi</li>
                                <li><i class="fas fa-check"></i> Kerjasama orang tua</li>
                            </ul>
                            <div class="service-price">
                                <span class="price">Rp 160.000</span>
                                <span class="duration">/ sesi</span>
                            </div>
                            <a href="index.php" class="btn btn-outline-primary w-100">Booking Layanan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Mengapa Memilih Kami?</h2>
                <p class="section-subtitle">Kami berkomitmen memberikan layanan terbaik untuk kesehatan Anda</p>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h4>Tenaga Ahli</h4>
                        <p>Fisioterapis berpengalaman dan bersertifikat</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Fleksibel</h4>
                        <p>Jadwal yang dapat disesuaikan dengan kebutuhan Anda</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h4>Home Visit</h4>
                        <p>Layanan fisioterapi di rumah untuk kenyamanan Anda</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Perawatan Personal</h4>
                        <p>Program terapi yang disesuaikan dengan kondisi Anda</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2>Siap untuk Memulai Perjalanan Pemulihan Anda?</h2>
                    <p class="mb-4">Hubungi kami sekarang untuk konsultasi gratis dan booking layanan fisioterapi yang sesuai dengan kebutuhan Anda.</p>
                    <div class="cta-buttons">
                        <a href="index.php" class="btn btn-primary btn-lg mr-3">Booking Sekarang</a>
                        <a href="tel:+6281234567890" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-phone mr-2"></i>Hubungi Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <h5>Fisioterapi Unggul Sehat</h5>
                    <p>Menyediakan layanan fisioterapi profesional untuk kesehatan dan pemulihan optimal Anda.</p>
                </div>
                <div class="col-lg-4">
                    <h5>Kontak</h5>
                    <p><i class="fas fa-phone mr-2"></i>+62 812-3456-7890</p>
                    <p><i class="fas fa-envelope mr-2"></i>info@fisioterapiunggul.com</p>
                    <p><i class="fas fa-map-marker-alt mr-2"></i>Jl. Kesehatan No. 123, Jakarta</p>
                </div>
                <div class="col-lg-4">
                    <h5>Jam Operasional</h5>
                    <p>Senin - Jumat: 08:00 - 20:00</p>
                    <p>Sabtu: 08:00 - 17:00</p>
                    <p>Minggu: 09:00 - 15:00</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; 2024 Fisioterapi Unggul Sehat. All rights reserved.</p>
            </div>
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

    <!-- Modal Upload Foto Profil -->
    <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="profileModalLabel">Ganti Foto Profil</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <form action="upload_profile.php" method="POST" enctype="multipart/form-data" id="profileForm">
                        <div class="form-group mb-2">
                            <input type="file" class="form-control-file" id="profile_pic" name="profile_pic" accept="image/*" required>
                            <small class="form-text text-muted">JPG, JPEG, PNG, GIF. Maks 2MB.</small>
                        </div>
                        <div class="form-group">
                            <img id="preview" src="" alt="Preview" style="max-width: 120px; max-height: 120px; display: none; margin: 0 auto 10px auto; border-radius: 50%; border: 2px solid #007bff;" class="shadow">
                        </div>
                        <div class="modal-footer p-0 pt-2 border-0 d-flex justify-content-center">
                            <button type="button" class="btn btn-light mr-2" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/js/jquery-3.5.1.min.js"></script>
    <script src="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/js/bootstrap.bundle.min.js"></script>
    <script src="/BookingFisioterapi/assets/chatbot.js"></script>
    <script>
        // Profile dropdown functionality
        $(document).ready(function() {
            $('#gantiFotoBtn').on('click', function(e) {
                e.preventDefault();
                $('#profileModal').modal('show');
            });
            // Enable dropdown
            $('.dropdown-toggle').dropdown();
        });

        // Preview gambar sebelum upload
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Validasi file sebelum submit
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('profile_pic');
            const file = fileInput.files[0];
            if (!file) {
                e.preventDefault();
                alert('Silakan pilih file terlebih dahulu.');
                return;
            }
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                e.preventDefault();
                alert('Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                e.preventDefault();
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                return;
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html> 