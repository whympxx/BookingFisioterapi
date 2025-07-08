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

// Check if user exists
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle profile picture with better fallback
$profilePic = '/BookingFisioterapi/assets/img/ikon_fisioterapi.jpg'; // Default fallback
if ($user['profile_pic'] && !empty($user['profile_pic'])) {
    $uploadPath = '/BookingFisioterapi/uploads/' . $user['profile_pic'];
    // Check if file exists
    if (file_exists(__DIR__ . '/../uploads/' . $user['profile_pic'])) {
        $profilePic = $uploadPath;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - Fisioterapi Unggul Sehat</title>
    <link rel="stylesheet" href="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/BookingFisioterapi/assets/dashboard.css">
    <style>
        /* Chatbot Styles */
        .chatbot-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0, 123, 255, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .chatbot-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0, 123, 255, 0.4);
        }

        .chatbot-toggle.pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .chatbot-modal {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: none;
            flex-direction: column;
            z-index: 1001;
            overflow: hidden;
        }

        .chatbot-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chatbot-header h6 {
            margin: 0;
            font-weight: 600;
        }

        .chatbot-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
        }

        .chatbot-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }

        .message.user {
            justify-content: flex-end;
        }

        .message-content {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
        }

        .message.bot .message-content {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 18px 18px 18px 5px;
        }

        .message.user .message-content {
            background: #007bff;
            color: white;
            border-radius: 18px 18px 5px 18px;
        }

        .message-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin: 0 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
        }

        .message.bot .message-avatar {
            background: #007bff;
        }

        .message.user .message-avatar {
            background: #28a745;
        }

        .chatbot-input {
            padding: 15px;
            border-top: 1px solid #e9ecef;
            background: white;
        }

        .chatbot-input form {
            display: flex;
            gap: 10px;
        }

        .chatbot-input input {
            flex: 1;
            border: 1px solid #ced4da;
            border-radius: 20px;
            padding: 8px 15px;
            outline: none;
        }

        .chatbot-input input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .chatbot-input button {
            background: #007bff;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chatbot-input button:hover {
            background: #0056b3;
        }

        .typing-indicator {
            display: none;
            padding: 10px 15px;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 18px 18px 18px 5px;
            margin-bottom: 15px;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #6c757d;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes typing {
            0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }

        .quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .quick-reply {
            background: #e9ecef;
            border: none;
            border-radius: 15px;
            padding: 5px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quick-reply:hover {
            background: #007bff;
            color: white;
        }

        @media (max-width: 768px) {
            .chatbot-modal {
                width: 90%;
                right: 5%;
                left: 5%;
                height: 60vh;
                bottom: 80px;
            }
            
            .chatbot-toggle {
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="/BookingFisioterapi/assets/img/ikon_fisioterapi.jpg" alt="Logo" class="navbar-logo mr-2">
                <span class="brand-text">Fisioterapi Unggul Sehat</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="layanan.php">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="riwayat_booking.php">Riwayat Booking</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-toggle="dropdown">
                            <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profil <?= htmlspecialchars($user['username']) ?>" class="profile-pic mr-2" onerror="this.src='/BookingFisioterapi/assets/img/ikon_fisioterapi.jpg'">
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

    <?php if (isset($_GET['msg'])): ?>
        <div class="container mt-5 pt-4">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle mr-2"></i>
                <?= htmlspecialchars($_GET['msg']) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="hero-title">Selamat Datang di Fisioterapi Unggul Sehat</h1>
                    <p class="hero-subtitle">Kami menyediakan layanan fisioterapi profesional untuk membantu pemulihan dan kesehatan optimal Anda. Mulai perjalanan kesehatan Anda bersama kami.</p>
                    <div class="hero-buttons">
                        <a href="index.php" class="btn btn-primary btn-lg mr-3">Booking Sekarang</a>
                        <a href="layanan.php" class="btn btn-outline-primary btn-lg">Lihat Layanan</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="/BookingFisioterapi/assets/img/dashboard-fisioterapi.jpg" alt="Fisioterapi" class="hero-image" loading="lazy" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Services Section -->
    <section class="services-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Layanan Unggulan Kami</h2>
                <p class="section-subtitle">Pilih layanan yang sesuai dengan kebutuhan kesehatan Anda</p>
            </div>
            
            <div class="row">
                <!-- Terapi Lutut -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="/BookingFisioterapi/assets/img/terapi_lutut.jpg" alt="Terapi Lutut" loading="lazy" onerror="this.src='/BookingFisioterapi/assets/img/ikon_fisioterapi.jpg'">
                            <div class="service-overlay">
                                <i class="fas fa-bone"></i>
                            </div>
                        </div>
                        <div class="service-content">
                            <h3>Terapi Lutut</h3>
                            <p>Perawatan khusus untuk masalah pada lutut, termasuk nyeri, cedera, dan pemulihan pasca operasi.</p>
                            <ul class="service-features">
                                <li><i class="fas fa-check"></i> Penanganan nyeri lutut</li>
                                <li><i class="fas fa-check"></i> Rehabilitasi pasca operasi</li>
                                <li><i class="fas fa-check"></i> Penguatan otot lutut</li>
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
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card featured">
                        <div class="service-badge">Terpopuler</div>
                        <div class="service-image">
                            <img src="/BookingFisioterapi/assets/img/rehab_cedera.jpg" alt="Rehabilitasi Pasca Cedera" loading="lazy" onerror="this.src='/BookingFisioterapi/assets/img/ikon_fisioterapi.jpg'">
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
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-card">
                        <div class="service-image">
                            <img src="/BookingFisioterapi/assets/img/pijat_medis.jpg" alt="Pijat Medis" loading="lazy" onerror="this.src='/BookingFisioterapi/assets/img/ikon_fisioterapi.jpg'">
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
                            </ul>
                            <div class="service-price">
                                <span class="price">Rp 120.000</span>
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
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">
                        <i class="fas fa-camera mr-2"></i>Ganti Foto Profil
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="upload_profile.php" method="POST" enctype="multipart/form-data" id="profileForm">
                        <div class="form-group">
                            <label for="profile_pic" class="form-label">Pilih Foto</label>
                            <input type="file" class="form-control-file" id="profile_pic" name="profile_pic" accept="image/*" required>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Format: JPG, JPEG, PNG, GIF. Maksimal 2MB.
                            </small>
                        </div>
                        <div class="form-group text-center">
                            <img id="preview" src="" alt="Preview" style="max-width: 150px; max-height: 150px; display: none; border-radius: 50%; border: 3px solid #007bff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" class="mb-3">
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i>Batal
                            </button>
                            <button type="submit" class="btn btn-primary" id="uploadBtn">
                                <i class="fas fa-upload mr-1"></i>Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/js/jquery-3.5.1.min.js"></script>
    <script src="/BookingFisioterapi/assets/bootstrap-4.6.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chatbot functionality
        class Chatbot {
            constructor() {
                this.messages = [];
                this.isOpen = false;
                this.init();
            }

            init() {
                this.setupEventListeners();
                this.addWelcomeMessage();
                this.startPulseAnimation();
            }

            setupEventListeners() {
                document.getElementById('chatbotToggle').addEventListener('click', () => this.toggleChat());
                document.getElementById('chatbotClose').addEventListener('click', () => this.closeChat());
                document.getElementById('chatbotForm').addEventListener('submit', (e) => this.handleSubmit(e));
                document.getElementById('chatbotInput').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.handleSubmit(e);
                    }
                });
            }

            toggleChat() {
                if (this.isOpen) {
                    this.closeChat();
                } else {
                    this.openChat();
                }
            }

            openChat() {
                document.getElementById('chatbotModal').style.display = 'flex';
                this.isOpen = true;
                document.getElementById('chatbotInput').focus();
                this.stopPulseAnimation();
            }

            closeChat() {
                document.getElementById('chatbotModal').style.display = 'none';
                this.isOpen = false;
            }

            addWelcomeMessage() {
                const welcomeMessage = {
                    type: 'bot',
                    content: 'Halo! Saya asisten fisioterapi Anda. Saya siap membantu menjawab pertanyaan seputar layanan fisioterapi, jadwal, harga, dan informasi lainnya. Apa yang bisa saya bantu?',
                    quickReplies: [
                        'Layanan apa saja yang tersedia?',
                        'Berapa harga layanan?',
                        'Bagaimana cara booking?',
                        'Jam operasional?',
                        'Lokasi klinik?'
                    ]
                };
                this.addMessage(welcomeMessage);
            }

            addMessage(message) {
                this.messages.push(message);
                this.renderMessage(message);
                this.scrollToBottom();
            }

            renderMessage(message) {
                const messagesContainer = document.getElementById('chatbotMessages');
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${message.type}`;

                const avatar = document.createElement('div');
                avatar.className = 'message-avatar';
                avatar.innerHTML = message.type === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>';

                const content = document.createElement('div');
                content.className = 'message-content';
                content.textContent = message.content;

                messageDiv.appendChild(avatar);
                messageDiv.appendChild(content);

                if (message.quickReplies && message.type === 'bot') {
                    const quickRepliesDiv = document.createElement('div');
                    quickRepliesDiv.className = 'quick-replies';
                    message.quickReplies.forEach(reply => {
                        const button = document.createElement('button');
                        button.className = 'quick-reply';
                        button.textContent = reply;
                        button.addEventListener('click', () => this.handleQuickReply(reply));
                        quickRepliesDiv.appendChild(button);
                    });
                    messageDiv.appendChild(quickRepliesDiv);
                }

                messagesContainer.appendChild(messageDiv);
            }

            handleQuickReply(reply) {
                this.addMessage({ type: 'user', content: reply });
                this.processUserInput(reply);
            }

            handleSubmit(e) {
                e.preventDefault();
                const input = document.getElementById('chatbotInput');
                const message = input.value.trim();
                
                if (message) {
                    this.addMessage({ type: 'user', content: message });
                    this.processUserInput(message);
                    input.value = '';
                }
            }

            processUserInput(input) {
                this.showTypingIndicator();
                
                setTimeout(() => {
                    this.hideTypingIndicator();
                    const response = this.generateResponse(input.toLowerCase());
                    this.addMessage({ type: 'bot', content: response });
                }, 1000 + Math.random() * 1000);
            }

            generateResponse(input) {
                const responses = {
                    'layanan': 'Kami menyediakan berbagai layanan fisioterapi:\n\n• Terapi Lutut (Rp 150.000/sesi)\n• Rehabilitasi Pasca Cedera (Rp 200.000/sesi)\n• Pijat Medis (Rp 120.000/sesi)\n• Terapi Tulang Belakang (Rp 180.000/sesi)\n• Terapi Stroke (Rp 250.000/sesi)\n• Terapi Anak (Rp 160.000/sesi)\n\nSemua layanan dilakukan oleh fisioterapis berpengalaman dan bersertifikat.',
                    
                    'harga': 'Berikut adalah daftar harga layanan kami:\n\n• Terapi Lutut: Rp 150.000/sesi\n• Rehabilitasi Pasca Cedera: Rp 200.000/sesi\n• Pijat Medis: Rp 120.000/sesi\n• Terapi Tulang Belakang: Rp 180.000/sesi\n• Terapi Stroke: Rp 250.000/sesi\n• Terapi Anak: Rp 160.000/sesi\n\nHarga sudah termasuk konsultasi dan home visit. Pembayaran dapat dilakukan setelah sesi selesai.',
                    
                    'booking': 'Untuk melakukan booking, Anda dapat:\n\n1. Klik tombol "Booking Sekarang" di halaman utama\n2. Pilih layanan yang diinginkan\n3. Pilih tanggal dan waktu yang tersedia\n4. Isi form data diri dan keluhan\n5. Konfirmasi booking\n\nAtau hubungi kami langsung di +62 812-3456-7890 untuk booking via telepon.',
                    
                    'jam': 'Jam operasional klinik kami:\n\n• Senin - Jumat: 08:00 - 20:00\n• Sabtu: 08:00 - 17:00\n• Minggu: 09:00 - 15:00\n\nKami juga menyediakan layanan home visit dengan jadwal yang lebih fleksibel.',
                    
                    'lokasi': 'Klinik Fisioterapi Unggul Sehat berlokasi di:\n\nJl. Kesehatan No. 123, Jakarta\n\nKami juga menyediakan layanan home visit untuk area Jakarta dan sekitarnya. Jarak maksimal home visit adalah 15 km dari klinik.',
                    
                    'terapi lutut': 'Terapi Lutut kami meliputi:\n\n• Penanganan nyeri lutut akut dan kronis\n• Rehabilitasi pasca operasi lutut\n• Penguatan otot lutut dan sekitarnya\n• Latihan keseimbangan dan koordinasi\n• Edukasi pencegahan cedera\n\nDurasi: 60-90 menit per sesi\nHarga: Rp 150.000/sesi',
                    
                    'rehabilitasi': 'Rehabilitasi Pasca Cedera mencakup:\n\n• Program pemulihan personal sesuai kondisi\n• Terapi manual dan manipulasi\n• Latihan fungsional dan penguatan\n• Modalitas fisioterapi (ultrasound, TENS, dll)\n• Edukasi dan pencegahan\n\nDurasi: 90-120 menit per sesi\nHarga: Rp 200.000/sesi',
                    
                    'pijat': 'Pijat Medis kami meliputi:\n\n• Pijat relaksasi untuk mengurangi stres\n• Pijat terapeutik untuk masalah otot\n• Pijat refleksi untuk sirkulasi darah\n• Teknik pijat sesuai keluhan\n\nDurasi: 60 menit per sesi\nHarga: Rp 120.000/sesi',
                    
                    'home visit': 'Layanan Home Visit kami:\n\n• Fisioterapis datang ke rumah Anda\n• Peralatan lengkap dibawa\n• Jadwal fleksibel sesuai kebutuhan\n• Biaya tambahan transportasi Rp 50.000\n• Maksimal jarak 15 km dari klinik\n\nCocok untuk pasien yang sulit bergerak atau sibuk.',
                    
                    'kontak': 'Anda dapat menghubungi kami melalui:\n\n• Telepon: +62 812-3456-7890\n• WhatsApp: +62 812-3456-7890\n• Email: info@fisioterapiunggul.com\n• Alamat: Jl. Kesehatan No. 123, Jakarta\n\nKami siap melayani pertanyaan dan booking 24/7.',
                    
                    'dokter': 'Tim fisioterapis kami:\n\n• Semua fisioterapis bersertifikat\n• Pengalaman minimal 5 tahun\n• Spesialisasi berbagai bidang\n• Terus mengikuti pelatihan terbaru\n• Berpengalaman di rumah sakit dan klinik\n\nKami memastikan kualitas layanan terbaik untuk Anda.',
                    
                    'bayar': 'Cara pembayaran:\n\n• Tunai setelah sesi selesai\n• Transfer bank (BCA, Mandiri, BNI)\n• E-wallet (GoPay, OVO, DANA)\n• Kartu debit/credit\n\nTidak ada biaya pendaftaran atau administrasi.',
                    
                    'jaminan': 'Kami memberikan jaminan:\n\n• Konsultasi gratis untuk sesi pertama\n• Garansi kepuasan layanan\n• Program terapi yang disesuaikan\n• Evaluasi berkala kemajuan\n• Dukungan pasca terapi\n\nKami berkomitmen memberikan hasil terbaik.',
                    
                    'darurat': 'Untuk kondisi darurat:\n\n• Hubungi 119 untuk ambulans\n• Kunjungi UGD terdekat\n• Jangan melakukan terapi sendiri\n• Konsultasi dokter terlebih dahulu\n\nFisioterapi bukan untuk kondisi darurat medis.',
                    
                    'default': 'Maaf, saya tidak memahami pertanyaan Anda. Silakan tanyakan tentang:\n\n• Layanan fisioterapi yang tersedia\n• Harga dan cara pembayaran\n• Cara booking dan jadwal\n• Lokasi dan jam operasional\n• Home visit dan layanan khusus\n\nAtau hubungi kami langsung di +62 812-3456-7890 untuk bantuan lebih lanjut.'
                };

                for (const [keyword, response] of Object.entries(responses)) {
                    if (input.includes(keyword)) {
                        return response;
                    }
                }

                return responses.default;
            }

            showTypingIndicator() {
                const messagesContainer = document.getElementById('chatbotMessages');
                const typingDiv = document.createElement('div');
                typingDiv.className = 'typing-indicator';
                typingDiv.id = 'typingIndicator';
                typingDiv.innerHTML = `
                    <div class="typing-dots">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                `;
                messagesContainer.appendChild(typingDiv);
                this.scrollToBottom();
            }

            hideTypingIndicator() {
                const typingIndicator = document.getElementById('typingIndicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }

            scrollToBottom() {
                const messagesContainer = document.getElementById('chatbotMessages');
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            startPulseAnimation() {
                document.getElementById('chatbotToggle').classList.add('pulse');
            }

            stopPulseAnimation() {
                document.getElementById('chatbotToggle').classList.remove('pulse');
            }
        }

        // Profile dropdown functionality
        $(document).ready(function() {
            // Initialize chatbot
            const chatbot = new Chatbot();
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Profile modal functionality
            $('#gantiFotoBtn').on('click', function(e) {
                e.preventDefault();
                $('#profileModal').modal('show');
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        // Preview gambar sebelum upload
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('preview');
            const uploadBtn = document.getElementById('uploadBtn');
            
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.');
                    this.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    this.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });

        // Form submission with loading state
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('profile_pic');
            const uploadBtn = document.getElementById('uploadBtn');
            const file = fileInput.files[0];
            
            if (!file) {
                e.preventDefault();
                alert('Silakan pilih file terlebih dahulu.');
                return;
            }
            
            // Show loading state
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Uploading...';
            uploadBtn.disabled = true;
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
        
        // Add fade-in animation to elements
        function checkFadeIn() {
            const elements = document.querySelectorAll('.service-card, .feature-card');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < window.innerHeight - elementVisible) {
                    element.classList.add('fade-in', 'visible');
                }
            });
        }
        
        // Check on scroll and load
        window.addEventListener('scroll', checkFadeIn);
        window.addEventListener('load', checkFadeIn);
    </script>
</body>
</html>  