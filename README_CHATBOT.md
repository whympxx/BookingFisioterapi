# Chatbot Fisioterapi - Dokumentasi Fitur

## Deskripsi
Chatbot Fisioterapi adalah fitur asisten virtual yang dapat menjawab pertanyaan user secara otomatis mengenai layanan fisioterapi, harga, jadwal, dan informasi lainnya. Chatbot ini dirancang untuk memberikan pengalaman yang interaktif dan informatif bagi pengguna.

## Fitur Utama

### 1. Interface Chatbot
- **Floating Button**: Tombol chat yang mengambang di pojok kanan bawah
- **Modal Chat**: Window chat yang dapat dibuka/tutup
- **Responsive Design**: Menyesuaikan dengan ukuran layar (desktop/mobile)

### 2. Kemampuan Chatbot
- **Auto-Response**: Menjawab pertanyaan secara otomatis
- **Quick Replies**: Tombol jawaban cepat untuk pertanyaan umum
- **Typing Indicator**: Animasi "sedang mengetik" untuk pengalaman yang lebih natural
- **Message History**: Menyimpan riwayat percakapan dalam sesi

### 3. Topik yang Dapat Dijawab
- **Layanan Fisioterapi**: Informasi lengkap tentang semua layanan
- **Harga**: Daftar harga setiap layanan
- **Cara Booking**: Panduan lengkap cara melakukan booking
- **Jam Operasional**: Jadwal buka klinik
- **Lokasi**: Alamat dan area layanan
- **Home Visit**: Informasi layanan kunjungan rumah
- **Kontak**: Nomor telepon, email, dan alamat
- **Tim Dokter**: Informasi tentang fisioterapis
- **Cara Pembayaran**: Metode pembayaran yang diterima
- **Jaminan**: Garansi dan jaminan layanan
- **Kondisi Darurat**: Panduan untuk kondisi darurat

## Struktur File

### 1. CSS (`assets/chatbot.css`)
```css
/* Styling untuk chatbot */
.chatbot-toggle { /* Tombol floating */ }
.chatbot-modal { /* Window chat */ }
.chatbot-messages { /* Area pesan */ }
.message { /* Styling pesan */ }
.quick-replies { /* Tombol jawaban cepat */ }
.typing-indicator { /* Animasi typing */ }
```

### 2. JavaScript (`assets/chatbot.js`)
```javascript
class Chatbot {
    constructor() { /* Inisialisasi */ }
    init() { /* Setup event listeners */ }
    generateResponse() { /* Logika jawaban */ }
    // ... method lainnya
}
```

### 3. HTML Elements
```html
<!-- Tombol toggle chatbot -->
<div class="chatbot-toggle" id="chatbotToggle">
    <i class="fas fa-comments"></i>
</div>

<!-- Modal chatbot -->
<div class="chatbot-modal" id="chatbotModal">
    <!-- Header, messages, input -->
</div>
```

## Implementasi di Halaman

### 1. Dashboard (`pages/dashboard.php`)
- Chatbot sudah terintegrasi dengan styling inline
- Menyertakan semua fitur chatbot

### 2. Layanan (`pages/layanan.php`)
- Menggunakan file CSS dan JS terpisah
- Chatbot dapat membantu menjelaskan layanan

### 3. Booking (`pages/index.php`)
- Chatbot dapat membantu proses booking
- Memberikan panduan cara booking

## Cara Menambahkan Chatbot ke Halaman Baru

### 1. Tambahkan CSS
```html
<link rel="stylesheet" href="/BookingFisioterapi/assets/chatbot.css">
```

### 2. Tambahkan HTML Elements
```html
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
```

### 3. Tambahkan JavaScript
```html
<script src="/BookingFisioterapi/assets/chatbot.js"></script>
```

## Kustomisasi

### 1. Menambah Jawaban Baru
Edit file `assets/chatbot.js`, tambahkan di object `responses`:
```javascript
'keyword_baru': 'Jawaban untuk keyword baru',
```

### 2. Mengubah Styling
Edit file `assets/chatbot.css`:
```css
.chatbot-toggle {
    /* Ubah warna, ukuran, posisi */
}
```

### 3. Mengubah Quick Replies
Edit di method `addWelcomeMessage()`:
```javascript
quickReplies: [
    'Pertanyaan baru 1',
    'Pertanyaan baru 2',
    // ...
]
```

## Responsivitas

### Desktop (≥768px)
- Modal: 350px width, 500px height
- Posisi: bottom-right
- Tombol: 60px diameter

### Mobile (<768px)
- Modal: 90% width, 60vh height
- Posisi: center-bottom
- Tombol: 50px diameter

### Small Mobile (<480px)
- Modal: 95% width, 70vh height
- Font size: lebih kecil
- Spacing: lebih compact

## Animasi

### 1. Pulse Animation
- Tombol berkedip saat pertama kali dimuat
- Berhenti setelah chat dibuka

### 2. Typing Indicator
- 3 titik yang beranimasi
- Menunjukkan bot sedang "mengetik"

### 3. Hover Effects
- Tombol membesar saat di-hover
- Quick replies berubah warna

## Troubleshooting

### 1. Chatbot Tidak Muncul
- Pastikan file CSS dan JS ter-load
- Cek console browser untuk error
- Pastikan ID elements sesuai

### 2. Jawaban Tidak Muncul
- Cek keyword matching di `generateResponse()`
- Pastikan format response benar
- Cek console untuk error JavaScript

### 3. Styling Tidak Sesuai
- Pastikan CSS ter-load setelah Bootstrap
- Cek specificity CSS rules
- Pastikan class names sesuai

## Pengembangan Selanjutnya

### 1. Fitur yang Bisa Ditambahkan
- **Voice Chat**: Input suara untuk pertanyaan
- **File Upload**: Upload gambar untuk konsultasi
- **Multi-language**: Dukungan bahasa lain
- **AI Integration**: Integrasi dengan AI service
- **Analytics**: Tracking penggunaan chatbot

### 2. Database Integration
- Menyimpan riwayat chat ke database
- Analytics penggunaan
- Feedback system

### 3. Admin Panel
- Manajemen jawaban chatbot
- Monitoring penggunaan
- Custom responses

## Kontribusi
Untuk menambahkan fitur atau memperbaiki bug, silakan:
1. Fork repository
2. Buat branch baru
3. Commit perubahan
4. Push dan buat Pull Request

## Lisensi
Fitur chatbot ini dikembangkan untuk sistem booking fisioterapi dan dapat digunakan secara bebas untuk tujuan pendidikan dan komersial. 