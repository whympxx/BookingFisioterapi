# Panduan Deployment ke AwardSpace

## 📋 Langkah-langkah Deployment

### 1. Persiapan Akun AwardSpace

1. **Daftar Akun**:
   - Kunjungi https://www.awardspace.com
   - Klik "Sign Up" untuk membuat akun gratis
   - Pilih paket "Free Hosting"
   - Isi formulir pendaftaran dengan data yang valid

2. **Verifikasi Email**:
   - Cek email untuk link verifikasi
   - Klik link verifikasi untuk aktivasi akun

3. **Login ke Control Panel**:
   - Login dengan kredensial yang telah dibuat
   - Akses control panel hosting

### 2. Setup Database

1. **Buat Database MySQL**:
   - Di control panel, cari menu "MySQL Databases"
   - Klik "Create Database"
   - Buat database baru dengan nama: `db_fisioterapi`

2. **Catat Kredensial Database**:
   - Database Host: (biasanya seperti `mysql.awardspace.com`)
   - Username: (akan diberikan oleh AwardSpace)
   - Password: (password yang Anda buat)
   - Database Name: (nama database yang dibuat)

3. **Import Database Schema**:
   - Buka phpMyAdmin dari control panel
   - Pilih database yang telah dibuat
   - Klik tab "Import"
   - Upload file `database/schema.sql`
   - Klik "Go" untuk mengimport

### 3. Konfigurasi File

1. **Update Database Configuration**:
   - Edit file `includes/db_awardspace.php`
   - Ganti placeholder dengan kredensial database yang sebenarnya:
   ```php
   $host = "mysql.awardspace.com";        // Sesuaikan dengan host AwardSpace
   $user = "your_username";               // Username database Anda
   $password = "your_password";           // Password database Anda
   $database = "db_fisioterapi";          // Nama database Anda
   ```

2. **Update Include Files**:
   - Edit semua file PHP yang menggunakan `includes/db.php`
   - Ganti dengan `includes/db_awardspace.php`

### 4. Upload Files

1. **File Manager Method**:
   - Login ke control panel AwardSpace
   - Buka "File Manager"
   - Navigate ke folder `public_html`
   - Upload semua file proyek

2. **FTP Method** (Alternatif):
   - Gunakan FTP client seperti FileZilla
   - Host: ftp.your-domain.awardspace.com
   - Username: FTP username dari AwardSpace
   - Password: FTP password dari AwardSpace
   - Upload semua file ke folder `public_html`

### 5. File yang Harus Diupload

```
BookingFisioterapi/
├── assets/                 # Semua file CSS, JS, images
├── includes/
│   ├── db_awardspace.php   # Database config untuk AwardSpace
│   └── db_replication.php  # Class replikasi (optional)
├── pages/                  # Semua file PHP halaman
│   ├── index.php          # Halaman utama
│   ├── login.php          # Halaman login
│   ├── register.php       # Halaman registrasi
│   ├── dashboard.php      # Dashboard user
│   ├── admin.php          # Admin panel
│   └── 404.php            # Error page
├── database/
│   └── schema.sql         # Database schema (untuk import)
├── .htaccess              # Apache configuration
├── index.php              # Root redirect file
└── test_connection.php    # Test database connection
```

### 6. Testing

1. **Test Database Connection**:
   - Buka `https://your-domain.awardspace.com/test_connection.php`
   - Pastikan koneksi database berhasil
   - Periksa apakah semua tabel sudah ada

2. **Test Website**:
   - Buka `https://your-domain.awardspace.com`
   - Test semua fitur utama:
     - Registrasi user
     - Login
     - Booking
     - Admin panel

3. **Remove Test Files**:
   - Hapus `test_connection.php` setelah testing selesai

### 7. Domain Setup

1. **Subdomain Gratis**:
   - AwardSpace memberikan subdomain gratis
   - Format: `username.awardspace.com`

2. **Custom Domain** (Optional):
   - Jika memiliki domain sendiri
   - Update DNS records ke server AwardSpace
   - Konfigurasi di control panel

### 8. Troubleshooting

#### Database Connection Failed
- Pastikan kredensial database benar
- Cek apakah database sudah dibuat
- Periksa host database yang benar

#### File Upload Issues
- Pastikan file diupload ke folder `public_html`
- Cek permission file (755 untuk folder, 644 untuk file)
- Pastikan tidak ada file yang corrupt

#### PHP Errors
- Aktifkan error reporting untuk debugging
- Cek PHP version compatibility
- Periksa syntax error di file PHP

### 9. Maintenance

1. **Backup Regular**:
   - Backup database secara berkala
   - Backup file website
   - Gunakan tools backup di control panel

2. **Monitor Performance**:
   - Cek website statistics
   - Monitor resource usage
   - Optimasi jika diperlukan

### 10. Limitations (Free Plan)

- **Storage**: 1GB disk space
- **Bandwidth**: 5GB per month
- **Database**: 1 MySQL database
- **Email**: 1 email account
- **Ads**: Banner ads akan ditampilkan

## 🔗 Useful Links

- AwardSpace Control Panel: https://www.awardspace.com/control-panel
- AwardSpace Documentation: https://www.awardspace.com/help
- Support: https://www.awardspace.com/support

## 📞 Support

Jika mengalami masalah:
1. Cek dokumentasi AwardSpace
2. Buat ticket support di AwardSpace
3. Periksa forum komunitas AwardSpace
