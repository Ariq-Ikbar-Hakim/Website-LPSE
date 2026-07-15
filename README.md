# APEL BAJA – Tender (PPK)
**Aplikasi Pelayanan Pengadaan Barang/Jasa – LPSE Kabupaten Bangkalan**

## Cara Instalasi

1. **Import database**  
   Buka phpMyAdmin atau MySQL CLI, lalu jalankan:
   ```sql
   source database.sql
   ```

2. **Konfigurasi koneksi** di `config.php`:
   ```php
   $host = 'localhost';
   $db   = 'apelbaja_ppk';
   $user = 'root';
   $pass = '';
   ```

3. **Folder uploads** pastikan writable:
   ```
   uploads/sk/
   uploads/lampiran/
   ```

4. **Akses** di browser: `http://localhost/LPSE/`

## Akun Demo
- **NIP**: `123456789012345678`  
- **Password**: `password`

## Daftar Halaman

| File | Keterangan |
|------|-----------|
| `login.php` | Halaman login dengan captcha |
| `register.php` | Registrasi akun baru PPK |
| `index.php` | Dashboard utama |
| `managemen_user.php` | Upload SK OPD / Verifikasi |
| `usulan.php` | Daftar usulan paket dengan status tabs |
| `buat_paket.php` | Form buat usulan paket baru |
| `paket_detail.php` | Detail paket (7 tab: Detail, Lampiran, Approval, PPK, Tim, Berita Acara, Log) |
| `pengembalian_paket.php` | Paket yang dikembalikan (koreksi) |
| `logout.php` | Logout |
