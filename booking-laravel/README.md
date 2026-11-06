# Booking Fisioterapi (Laravel)

Versi Laravel 12 dari aplikasi booking fisioterapi di folder `pages/`.

## Cara menjalankan (XAMPP)

1. Jalankan **MySQL** dari XAMPP Control Panel.
2. Buat database kosong `db_fisioterapi_laravel` (lewat phpMyAdmin), atau ubah `DB_DATABASE` di `.env`.
3. Dari folder `booking-laravel`:

   ```bash
   php artisan migrate --seed
   php artisan serve
   ```

4. Buka http://127.0.0.1:8000

Akun admin awal: **admin / admin123**. Segera ganti lewat menu *Ubah Password*.

Tes otomatis: `php artisan test`

## Lupa password

Link reset dikirim lewat email. Selama `MAIL_MAILER=log`, email tidak benar-benar terkirim:
isinya (termasuk link reset) ditulis ke `storage/logs/laravel.log`. Isi pengaturan SMTP di `.env`
(misalnya Gmail/Mailtrap) agar email benar-benar terkirim.

## Halaman

| URL | Fungsi | Akses |
| --- | --- | --- |
| `/` | Halaman utama: model 3D, layanan, cara booking, terapis, ulasan, FAQ | Publik |
| `/layanan` | Daftar layanan + harga | Publik |
| `/login`, `/register`, `/lupa-password` | Masuk, daftar, reset password | Tamu |
| `/dashboard` | Ringkasan booking, jadwal mendatang, pengingat ulasan | Pasien |
| `/booking` | Pilih layanan → terapis → slot jam tersedia → data pasien | Pasien |
| `/booking/{id}` | Tiket booking + QR code, cetak/PDF, beri ulasan | Pasien |
| `/riwayat-booking` | Riwayat, filter status, batalkan | Pasien |
| `/profil` | Nama lengkap, email, no HP, foto profil | Pasien |
| `/admin` | Dasbor: statistik, pendapatan, grafik, jadwal hari ini | Admin |
| `/admin/booking` | Cari/filter booking, tetapkan terapis, ubah status | Admin |
| `/admin/layanan` | Tambah/ubah layanan & harga, aktif/nonaktif | Admin |
| `/admin/terapis` | Tambah/ubah fisioterapis, aktif/nonaktif | Admin |
| `/admin/admin-baru` | Tambah admin | Admin |
| `/booking/{id}/bayar` | Bayar online: QRIS, GoPay, ShopeePay, VA BCA/BNI/BRI/Mandiri, transfer manual | Pasien |
| `/terapis`, `/terapis/{id}` | Daftar & profil fisioterapis, ulasan, tombol booking | Publik |
| `/obat` | Obat & suplemen sesuai keluhan, dengan peringatan kondisi (hamil, maag, dll.) | Publik |
| `/panel-terapis` | Jadwal pasien, catatan terapi, anjuran obat, tandai sesi selesai | Terapis |
| `/admin/pembayaran` | Pantau pembayaran, verifikasi/tolak bukti transfer | Admin |
| `/admin/obat` | Tambah/ubah obat, sembunyikan | Admin |

Akun terapis awal: `andini`, `bima`, `citra`, `dimas` dengan password **terapis123**.
Login lewat halaman `/login` yang sama. Admin bisa membuat/mengubah akun terapis di menu Terapis.

## Pembayaran online

- `PAYMENT_DRIVER=demo` (bawaan): tanpa payment gateway. Nomor VA/QR hanya contoh dan ada tombol
  "Simulasikan pembayaran berhasil" untuk mencoba alur. **Jangan dipakai saat website online.**
- `PAYMENT_DRIVER=midtrans`: pembayaran sungguhan lewat Midtrans Snap. Daftar di midtrans.com, isi
  `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` di `.env` (sandbox dulu), lalu set URL notifikasi
  di dashboard Midtrans ke `https://domain-anda/pembayaran/notifikasi`.
- Transfer manual: pasien unggah bukti, admin memverifikasi di `/admin/pembayaran`.
- Bayar tunai di klinik: admin klik "Tandai lunas (tunai)" di `/admin/booking`.
- Setelah lunas, booking otomatis berstatus *Dikonfirmasi*. Harga dikunci saat booking dibuat.
- Rekening klinik untuk transfer manual ada di `config/pembayaran.php`.

## Obat & anjuran

- Data di `database/seeders/MedicineSeeder.php`, keluhan & kondisi di `config/obat.php`.
- Foto bawaan ada di `public/assets/img/obat/`, dengan nama file = slug obat (mis. `paracetamol-500-mg.png`).
  Foto yang diunggah admin di menu Obat disimpan di `public/uploads/obat/`.
- Obat yang dianjurkan terapis (dengan jumlahnya) **otomatis masuk tagihan pasien**: total = sesi terapi + obat.
  Harga obat dikunci saat dianjurkan. Bila sesi sudah lunas lalu terapis menambah obat, pasien membayar
  sisa tagihannya saja (online atau tunai di kasir). Tagihan terbuka dengan nominal lama dibatalkan otomatis.
- Harga & satuan obat diatur admin di menu Obat (harga awal di seeder hanya perkiraan).
- Fisioterapis hanya bisa menganjurkan obat bebas, bebas terbatas, dan suplemen. Obat keras
  tampil di katalog dengan tanda "Resep" tetapi tidak bisa dianjurkan dari panel terapis.
- Isi obat bersifat edukasi; minta dokter/apoteker memeriksa ulang sebelum dipakai untuk pasien.
| `/ubah-password` | Ganti password | Semua yang login |

## Deploy ke Vercel

- Runtime PHP: `vercel-php` (lihat `vercel.json`); semua permintaan masuk lewat `api/index.php`,
  file statis dilayani langsung dari `public/`. Region fungsi: Singapura (`sin1`).
- Database: Postgres dari Neon (integrasi Vercel Marketplace). `DATABASE_URL` diisi otomatis
  oleh integrasi; `DB_CONNECTION=pgsql`.
- Disk server Vercel hanya-baca, jadi `UPLOAD_DRIVER=database`: foto & bukti transfer disimpan di tabel
  `uploads` dan dibuka lewat `/berkas/{id}/{nama}` (bukti transfer hanya untuk pemilik & admin).
  Bisa juga `UPLOAD_DRIVER=blob` + `BLOB_READ_WRITE_TOKEN` untuk Vercel Blob.
- Cache/view Laravel ditulis ke `/tmp` (env `VIEW_COMPILED_PATH`, `APP_*_CACHE`), log ke `stderr`.
- Migrasi dijalankan dari komputer lokal ke database Neon:

  ```bash
  vercel env pull .env.vercel --environment=production
  # isi DB_URL dengan DATABASE_URL dari .env.vercel, lalu:
  php -d extension=pdo_pgsql artisan migrate --force --seed
  ```

- Deploy: `vercel deploy --prod` dari folder `booking-laravel`.

## Aturan jadwal

- Slot per 1 jam di dalam jam operasional (`config/klinik.php`).
- Maksimal 3 pasien per slot (`kapasitas_per_slot`), dan satu terapis hanya 1 pasien per slot.
- Booking yang dibatalkan membebaskan slotnya kembali.

## Tampilan & animasi

- Tema: `public/assets/css/theme.css` (warna teal medis, font Plus Jakarta Sans).
- Model 3D tulang belakang: `public/assets/js/spine3d.js` (Three.js r128). Jika browser tidak
  mendukung WebGL, foto biasa yang tampil. Animasi dimatikan otomatis bila pengguna memilih
  "kurangi gerakan" di pengaturan perangkatnya.
- Kartu miring 3D mengikuti kursor, kartu terapis berbalik 3D, animasi muncul saat scroll:
  `public/assets/js/app.js`.

## Perubahan struktur data

- Tabel `user` + `admin` digabung menjadi `users` dengan kolom `role`.
- Tabel `services` baru: satu sumber nama layanan, harga, durasi, dan fitur
  (dipakai form booking, halaman layanan, dashboard, dan chatbot).
- `bookings` punya `user_id` dan `service_id`, serta status baru `Dibatalkan`.
- Info klinik (telepon, alamat, jam buka) ada di `config/klinik.php`.

Fitur cluster/replikasi database dan REST API di folder `api/` tidak ikut dipindahkan.
