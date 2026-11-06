// Asisten chat sederhana. Data layanan, harga, dan kontak dikirim dari server
// (window.CHATBOT_DATA) supaya jawaban selalu sama dengan halaman Layanan.
(function () {
    const data = window.CHATBOT_DATA;
    if (!data) return;

    const k = data.klinik;
    const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
    const jamText = Object.entries(k.jam).map(([hari, jam]) => '• ' + hari + ': ' + jam).join('\n');
    const daftarHarga = data.layanan.map((s) => '• ' + s.nama + ': ' + s.harga + '/sesi').join('\n');

    function normalize(text) {
        return ' ' + text.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim() + ' ';
    }

    // Cocokkan kata utuh, supaya "jam" tidak ikut cocok dengan "jaminan".
    function has(text, words) {
        return words.some((w) => text.includes(' ' + w + ' '));
    }

    function serviceAnswer(s) {
        return s.nama + '\n\n' + s.deskripsi + '\n\n' +
            s.fitur.map((f) => '• ' + f).join('\n') +
            '\n\nDurasi: ' + s.durasi + ' per sesi\nHarga: ' + s.harga + '/sesi';
    }

    // Urutan penting: topik yang lebih spesifik dicek lebih dulu.
    const intents = [
        { words: ['darurat', 'emergency', 'ambulans', 'gawat'], answer: () =>
            'Untuk kondisi darurat:\n\n• Hubungi 119 untuk ambulans\n• Kunjungi UGD terdekat\n• Jangan melakukan terapi sendiri\n\nFisioterapi bukan untuk kondisi darurat medis.' },
        { words: ['home', 'rumah', 'visit'], answer: () =>
            'Layanan Home Visit kami:\n\n• Fisioterapis datang ke rumah Anda\n• Peralatan lengkap dibawa\n• Biaya tambahan transportasi ' + rupiah(k.biayaHomeVisit) +
            '\n• Maksimal jarak ' + k.jarakHomeVisit + ' km dari klinik\n\nSebutkan permintaan home visit di kolom catatan saat booking.' },
        { words: ['harga', 'biaya', 'tarif', 'berapa'], answer: () =>
            'Daftar harga layanan kami:\n\n' + daftarHarga + '\n\nHarga sudah termasuk konsultasi. Home visit dikenakan biaya transport tambahan ' + rupiah(k.biayaHomeVisit) + '.' },
        { words: ['bayar', 'pembayaran', 'transfer', 'tunai'], answer: () =>
            'Cara pembayaran:\n\n• Tunai setelah sesi selesai\n• Transfer bank (BCA, Mandiri, BNI)\n• E-wallet (GoPay, OVO, DANA)\n• Kartu debit/kredit\n\nTidak ada biaya pendaftaran atau administrasi.' },
        { words: ['booking', 'pesan', 'daftar', 'reservasi', 'janji'], answer: () =>
            'Cara booking:\n\n1. Buka menu "Booking"\n2. Isi data pasien\n3. Pilih layanan\n4. Pilih tanggal dan jam (dalam jam operasional)\n5. Klik "Konfirmasi Booking"\n\nStatus booking bisa dicek di menu "Riwayat Booking". Booking yang masih "Menunggu" bisa dibatalkan sendiri.' },
        { words: ['batal', 'membatalkan', 'cancel'], answer: () =>
            'Booking berstatus "Menunggu" bisa dibatalkan dari menu "Riwayat Booking". Jika sudah dikonfirmasi, hubungi kami di ' + k.telepon + '.' },
        { words: ['jaminan', 'garansi'], answer: () =>
            'Kami memberikan:\n\n• Konsultasi pertama gratis\n• Program terapi yang disesuaikan\n• Evaluasi berkala kemajuan\n• Dukungan pasca terapi' },
        { words: ['jam', 'buka', 'tutup', 'operasional', 'jadwal'], answer: () =>
            'Jam operasional klinik kami:\n\n' + jamText + '\n\nBooking hanya bisa dibuat di dalam jam operasional.' },
        { words: ['lokasi', 'alamat', 'dimana', 'mana'], answer: () =>
            k.nama + ' berlokasi di:\n\n' + k.alamat + '\n\nKami juga melayani home visit hingga ' + k.jarakHomeVisit + ' km dari klinik.' },
        { words: ['kontak', 'telepon', 'telp', 'whatsapp', 'wa', 'email', 'hubungi'], answer: () =>
            'Hubungi kami melalui:\n\n• Telepon/WhatsApp: ' + k.telepon + '\n• Email: ' + k.email + '\n• Alamat: ' + k.alamat + '\n\nKami membalas pesan selama jam operasional.' },
        { words: ['dokter', 'terapis', 'fisioterapis', 'tenaga'], answer: () =>
            'Tim fisioterapis kami bersertifikat, berpengalaman minimal 5 tahun, dan rutin mengikuti pelatihan terbaru.' },
        { words: ['konsultasi'], answer: () =>
            'Konsultasi meliputi pemeriksaan kondisi fisik, analisis keluhan, penentuan program terapi, dan edukasi pencegahan. Konsultasi pertama GRATIS.' },
        { words: ['layanan', 'terapi', 'service'], answer: () =>
            'Layanan fisioterapi kami:\n\n' + daftarHarga + '\n\nKetik nama layanan (misal "stroke") untuk detailnya.' },
    ];

    function generateResponse(input) {
        const text = normalize(input);

        // Pertanyaan tentang layanan tertentu dijawab dengan detail layanan itu.
        const service = data.layanan.find((s) => {
            const key = s.nama.toLowerCase().replace(/^terapi\s+/, '').split(' ')[0];
            return text.includes(' ' + key + ' ') || text.includes(' ' + s.nama.toLowerCase() + ' ');
        });
        if (service && !has(text, ['harga', 'biaya', 'tarif'])) return serviceAnswer(service);
        if (service) return service.nama + ': ' + service.harga + '/sesi (' + service.durasi + ').';

        const intent = intents.find((i) => has(text, i.words));
        if (intent) return intent.answer();

        return 'Maaf, saya belum memahami pertanyaan Anda. Silakan tanyakan tentang:\n\n• Layanan dan harga\n• Cara booking dan pembatalan\n• Jam operasional dan lokasi\n• Home visit dan pembayaran\n\nAtau hubungi kami di ' + k.telepon + '.';
    }

    const el = (id) => document.getElementById(id);
    const messages = el('chatbotMessages');
    let isOpen = false;

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function addMessage(type, content, quickReplies) {
        const wrap = document.createElement('div');
        wrap.className = 'message ' + type;

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.innerHTML = type === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>';

        const body = document.createElement('div');
        body.className = 'message-content';
        body.textContent = content;

        wrap.appendChild(avatar);
        wrap.appendChild(body);

        if (quickReplies) {
            const replies = document.createElement('div');
            replies.className = 'quick-replies';
            quickReplies.forEach((reply) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'quick-reply';
                btn.textContent = reply;
                btn.addEventListener('click', () => ask(reply));
                replies.appendChild(btn);
            });
            wrap.appendChild(replies);
        }

        messages.appendChild(wrap);
        scrollToBottom();
    }

    function ask(question) {
        addMessage('user', question);
        const typing = document.createElement('div');
        typing.className = 'typing-indicator';
        typing.innerHTML = '<div class="typing-dots"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div>';
        messages.appendChild(typing);
        scrollToBottom();

        setTimeout(() => {
            typing.remove();
            addMessage('bot', generateResponse(question));
        }, 600 + Math.random() * 600);
    }

    function setOpen(open) {
        isOpen = open;
        el('chatbotModal').style.display = open ? 'flex' : 'none';
        if (open) {
            el('chatbotToggle').classList.remove('pulse');
            el('chatbotInput').focus();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        el('chatbotToggle').classList.add('pulse');
        el('chatbotToggle').addEventListener('click', () => setOpen(!isOpen));
        el('chatbotClose').addEventListener('click', () => setOpen(false));
        el('chatbotForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const input = el('chatbotInput');
            const question = input.value.trim();
            if (question) {
                ask(question);
                input.value = '';
            }
        });

        addMessage('bot',
            'Halo! Saya asisten ' + k.nama + '. Saya bisa membantu menjawab pertanyaan seputar layanan, jadwal, harga, dan informasi lainnya. Apa yang bisa saya bantu?',
            ['Layanan apa saja yang tersedia?', 'Berapa harga layanan?', 'Bagaimana cara booking?', 'Jam operasional?', 'Lokasi klinik?']);
    });

    // Diekspor untuk pengujian manual di console.
    window.chatbotRespond = generateResponse;
})();
