# BookingFisioterapi

Sistem booking fisioterapi dengan fitur replikasi database untuk high availability dan load balancing.

## Fitur Utama

- **Sistem Booking**: Manajemen booking pasien fisioterapi
- **User Management**: Registrasi dan login user
- **Admin Panel**: Dashboard admin untuk manajemen sistem
- **Database Replication**: Replikasi database real-time menggunakan trigger
- **Health Monitoring**: Sistem monitoring kesehatan replikasi
- **Backup System**: Backup otomatis dengan kompresi
- **Load Balancing**: Distribusi beban read/write queries

## Teknologi yang Digunakan

- **Frontend**: HTML, CSS, JavaScript, Bootstrap 4.6.2
- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Replication**: Trigger-based replication
- **Web Server**: Apache (XAMPP)

## Struktur Proyek

```
BookingFisioterapi/
├── assets/                 # CSS, JS, dan asset lainnya
├── config/                 # File konfigurasi
├── includes/               # File include dan class
├── pages/                  # Halaman web utama
├── scripts/                # Script untuk replikasi dan monitoring
├── test/                   # File testing
└── uploads/                # Upload file user
```

## Instalasi

1. **Clone repository**:
   ```bash
   git clone git@github.com:whympxx/BookingFisioterapi.git
   cd BookingFisioterapi
   ```

2. **Setup database**:
   - Buat database `db_fisioterapi`
   - Import struktur database (lihat scripts/setup_database.sql)

3. **Konfigurasi**:
   - Edit file `includes/db.php` untuk koneksi database
   - Sesuaikan konfigurasi di `includes/db_replication.php`

4. **Setup replikasi**:
   ```bash
   # Jalankan script setup replikasi
   mysql -u root < scripts/setup_simple_replication.sql
   ```

5. **Akses aplikasi**:
   - Buka `http://localhost/BookingFisioterapi/pages/index.php`

## Fitur Replikasi Database

### Trigger-based Replication
- Sinkronisasi real-time antara master dan slave database
- Automatic failover jika master database down
- Load balancing untuk query SELECT

### Monitoring
- Dashboard monitoring: `scripts/replication_monitor_enhanced.php`
- Health check otomatis: `scripts/replication_health_check.php`
- Log replikasi real-time

### Backup System
- Backup otomatis dengan kompresi
- Rotasi backup berdasarkan umur file
- Metadata backup untuk tracking

## Penggunaan

### Admin Panel
- Login sebagai admin untuk mengakses dashboard
- Monitor status replikasi dan kesehatan sistem
- Kelola user dan booking

### User Interface
- Registrasi dan login user
- Booking layanan fisioterapi
- View riwayat booking

### Monitoring
- Akses `scripts/replication_monitor_enhanced.php` untuk monitoring
- Test replikasi menggunakan tombol "Test Replication"
- View log events replikasi

## Testing

### Test Replikasi
```bash
# Test manual
scripts/test_real_replication.bat

# Test via web interface
# Akses monitoring dan klik "Test Replication"
```

### Health Check
```bash
# Jalankan health check
php scripts/replication_health_check.php
```

## Kontribusi

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## Struktur Database

### Tabel Utama
- `booking`: Data booking pasien
- `user`: Data user sistem
- `admin`: Data admin sistem

### Tabel Replikasi
- `replication_log`: Log events replikasi
- `slave_fisioterapi.*`: Database slave

## Lisensi

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

Jika ada pertanyaan atau masalah, silakan buat issue di repository ini.

## Changelog

### v1.0.0
- Initial release
- Basic booking system
- Trigger-based replication
- Health monitoring
- Enhanced backup system

---

**Developed by**: [whympxx](https://github.com/whympxx)
