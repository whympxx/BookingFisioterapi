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
            
            'stroke': 'Terapi Stroke kami meliputi:\n\n• Rehabilitasi fungsi motorik\n• Latihan keseimbangan dan koordinasi\n• Terapi wicara dan menelan\n• Latihan aktivitas sehari-hari\n• Edukasi keluarga\n\nDurasi: 90-120 menit per sesi\nHarga: Rp 250.000/sesi',
            
            'tulang belakang': 'Terapi Tulang Belakang mencakup:\n\n• Penanganan nyeri punggung\n• Terapi postur tubuh\n• Latihan penguatan otot\n• Terapi manual dan manipulasi\n• Edukasi ergonomi\n\nDurasi: 60-90 menit per sesi\nHarga: Rp 180.000/sesi',
            
            'anak': 'Terapi Anak meliputi:\n\n• Terapi untuk keterlambatan perkembangan\n• Latihan motorik halus dan kasar\n• Terapi sensori integrasi\n• Latihan keseimbangan\n• Kerjasama dengan orang tua\n\nDurasi: 45-60 menit per sesi\nHarga: Rp 160.000/sesi',
            
            'konsultasi': 'Konsultasi fisioterapi meliputi:\n\n• Pemeriksaan kondisi fisik\n• Analisis keluhan dan riwayat\n• Penentuan program terapi\n• Edukasi pencegahan\n• Evaluasi kemajuan\n\nKonsultasi pertama GRATIS untuk semua layanan.',
            
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

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const chatbot = new Chatbot();
}); 