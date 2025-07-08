# Halaman Layanan Fisioterapi

## Deskripsi
Halaman layanan fisioterapi yang profesional dengan tampilan modern dan responsif. Halaman ini menampilkan berbagai layanan fisioterapi yang tersedia dengan gambar, deskripsi, dan harga.

## File yang Dibuat

### 1. `pages/layanan.php`
Halaman utama layanan fisioterapi dengan fitur:
- **Hero Section**: Bagian pembuka dengan gradient background dan call-to-action
- **Services Section**: Menampilkan 6 layanan fisioterapi:
  - Terapi Lutut
  - Rehabilitasi Pasca Cedera (Featured/Popular)
  - Pijat Medis
  - Terapi Tulang Belakang
  - Terapi Stroke
  - Terapi Anak
- **Why Choose Us**: Bagian keunggulan klinik
- **CTA Section**: Call-to-action untuk booking
- **Footer**: Informasi kontak dan jam operasional

### 2. `assets/layanan.css`
File CSS dengan styling profesional:
- **Responsive Design**: Menyesuaikan dengan berbagai ukuran layar
- **Modern UI**: Gradient, shadow, dan animasi hover
- **Professional Color Scheme**: Menggunakan warna biru (#3498db) sebagai primary color
- **Smooth Animations**: Transisi dan efek hover yang halus
- **Typography**: Menggunakan font Montserrat untuk tampilan modern

## Fitur Halaman

### Navbar
- Logo dan nama klinik
- Menu navigasi (Beranda, Layanan, Booking)
- Profile dropdown dengan foto profil
- Responsive mobile menu

### Service Cards
Setiap kartu layanan memiliki:
- Gambar layanan dengan efek hover
- Judul dan deskripsi layanan
- Daftar fitur/fasilitas
- Harga per sesi
- Tombol booking

### Interactive Elements
- Hover effects pada kartu layanan
- Smooth scrolling
- Modal untuk upload foto profil
- Responsive buttons

## Gambar yang Diperlukan

Halaman ini memerlukan beberapa gambar yang perlu ditambahkan ke folder `assets/img/`:

1. `hero-fisioterapi.jpg` - Gambar utama untuk hero section
2. `terapi_tulang_belakang.jpg` - Gambar untuk layanan terapi tulang belakang
3. `terapi_stroke.jpg` - Gambar untuk layanan terapi stroke
4. `terapi_anak.jpg` - Gambar untuk layanan terapi anak

**Catatan**: Saat ini file-file gambar tersebut adalah placeholder. Silakan ganti dengan gambar asli yang sesuai.

## Cara Menggunakan

1. **Akses Halaman**: Buka `pages/layanan.php` melalui browser
2. **Navigasi**: Gunakan menu navbar untuk berpindah antar halaman
3. **Booking**: Klik tombol "Booking Layanan" pada kartu layanan yang diinginkan
4. **Profile**: Klik foto profil untuk mengakses menu dropdown

## Integrasi dengan Sistem

Halaman ini terintegrasi dengan:
- **Session Management**: Menggunakan session PHP untuk autentikasi
- **Database**: Terhubung dengan database untuk data user
- **Upload System**: Modal untuk upload foto profil
- **Navigation**: Terhubung dengan halaman lain dalam sistem

## Responsive Design

Halaman ini responsif untuk:
- **Desktop**: Layout 3 kolom untuk service cards
- **Tablet**: Layout 2 kolom untuk service cards
- **Mobile**: Layout 1 kolom untuk service cards

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Internet Explorer 11+

## Dependencies

- Bootstrap 4.6.2
- jQuery 3.5.1
- Font Awesome 5.15.4
- Google Fonts (Montserrat)

## Customization

Untuk menyesuaikan halaman:

1. **Warna**: Ubah variabel CSS di `assets/layanan.css`
2. **Layanan**: Edit array layanan di `pages/layanan.php`
3. **Gambar**: Ganti file gambar di `assets/img/`
4. **Konten**: Edit teks dan deskripsi sesuai kebutuhan

## Performance

- Gambar dioptimasi dengan lazy loading
- CSS dan JS di-minify untuk performa optimal
- Responsive images untuk berbagai ukuran layar 