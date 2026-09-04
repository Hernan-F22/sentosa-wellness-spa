# 🌿 Sentosa Wellness & Massage Spa Web Application

Sistem Informasi & Manajemen Reservasi Layanan Spa / Pijat Modern (*Online/Home Service & Offline/On-site Spa*) berbasis Web Responsif dengan arsitektur **Multi-Role (Pelanggan, Terapis, Administrator)**, kalkulasi rating otomatis berbasis ulasan nyata pelanggan, dan portal kerja mandiri untuk terapis.

---

## 🛠 Stack Teknologi

- **Frontend**: HTML5, Tailwind CSS (CDN modern), JavaScript Vanilla (ES6+ Fetch API), FontAwesome 6 Free, Google Fonts (*Plus Jakarta Sans* & *Playfair Display*).
- **Backend**: PHP Native (PHP 8+, OOP, PDO MySQL dengan Prepared Statements anti-SQL Injection).
- **Database**: MySQL / MariaDB (Database: `massage_booking_db`).
- **Autentikasi**: Session-based Authentication dengan Role-Based Access Control (RBAC) & enkripsi password `password_hash()` (Bcrypt).

---

## 🚀 Panduan Menjalankan di XAMPP

1. **Jalankan XAMPP Control Panel**:
   - Pastikan modul **Apache** dan **MySQL** berstatus **Running** (Hijau).

2. **Koneksi Database**:
   - Sistem dilengkapi fitur **Auto-Schema Migration** (`ensureSchema()` pada `config/database.php`) yang otomatis memastikan tabel dan kolom terbaru (termasuk tabel relasional `reviews`) siap pakai saat aplikasi pertama kali dibuka.
   - Skrip SQL lengkap juga tersedia di [`database.sql`](database.sql) jika ingin diimpor ulang secara manual melalui phpMyAdmin (`http://localhost/phpmyadmin`).

3. **Akses Aplikasi**:
   - **Halaman Utama Publik**: [http://localhost/web_pjt/](http://localhost/web_pjt/)
   - **Form Reservasi Interaktif**: [http://localhost/web_pjt/booking.php](http://localhost/web_pjt/booking.php)
   - **Riwayat Reservasi Pelanggan**: [http://localhost/web_pjt/my-bookings.php](http://localhost/web_pjt/my-bookings.php)
   - **Portal Khusus Terapis**: [http://localhost/web_pjt/therapist-portal.php](http://localhost/web_pjt/therapist-portal.php)
   - **Panel Dashboard Administrator**: [http://localhost/web_pjt/admin/dashboard.php](http://localhost/web_pjt/admin/dashboard.php)

---

## 🔑 Akun Uji Coba Bawaan (Demo Credentials)

| Peran (*Role*) | Email / Identitas Login | Password | Akses & Fungsionalitas |
|---|---|---|---|
| **Administrator** | `admin@spa.com` / `081234567890` | `admin123` | Akses penuh Admin Dashboard, kelola reservasi, katalog layanan, CRUD terapis + buat password akun, analitik pendapatan, dan pratinjau portal terapis. |
| **Pelanggan (*Customer*)** | `bambang@gmail.com` / `081298765432` | `user123` | Reservasi layanan, pelacakan status pesanan, melihat riwayat booking pribadi, dan memberikan penilaian bintang & ulasan setelah sesi tuntas. |
| **Terapis Wanita (Siti)** | `siti@spa.com` | `user123` | Portal kerja mandiri, daftar penugasan sesi klien, WhatsApp direct chat klien, tombol mulai & selesaikan sesi, toggle ketersediaan kerja, dan ulasan kepuasan klien. |
| **Terapis Pria (Budi)** | `budi@spa.com` | `user123` | Spesialis Deep Tissue Massage & Shiatsu dengan portal penugasan terapis mandiri. |
| **Terapis Wanita (Dewi)** | `dewi@spa.com` | `user123` | Spesialis Balinese & Hot Stone Massage dengan portal penugasan terapis mandiri. |
| **Terapis Pria (Ahmad)** | `ahmad@spa.com` | `user123` | Spesialis Sport Massage & Refleksi dengan portal penugasan terapis mandiri. |

> **Catatan Akun Terapis Baru**: Ketika Admin menambahkan terapis baru melalui Dashboard Admin, Admin dapat langsung menentukan alamat email dan password login khusus untuk terapis tersebut (default: `therapist123` atau custom).

---

## 📂 Struktur Direktori & Arsitektur File

```
web_pjt/
├── config/
│   └── database.php          # Koneksi PDO aman, session security, auto-schema migration & helper format
├── api/
│   ├── auth.php              # API Autentikasi (login, register, logout, me, session check)
│   ├── services.php          # API CRUD katalog layanan pijat & filter kategori
│   ├── therapists.php        # API CRUD tim terapis, akun login terapis, reset password, & toggle ketersediaan
│   ├── booking.php           # API Reservasi, kalkulasi slot jam, penetapan terapis, update status, & submit review
│   └── stats.php             # API Analitik metrik finansial, status rasio, dan ringkasan operasional
├── views/
│   ├── header.php            # Head HTML, Tailwind CDN, FontAwesome, Google Fonts, Flash Toast
│   ├── navbar.php            # Navigasi responsif (desktop & mobile) dengan deteksi role dinamis
│   ├── footer.php            # Footer informasi spa & Floating WhatsApp Quick Chat
│   └── auth_modal.php        # Modal Login/Register dan Modal Pelacak Resi Reservasi Cepat
├── admin/
│   └── dashboard.php         # Panel Admin lengkap (Overview, Bookings, Services, Tim Terapis, & Keuangan)
├── index.php                 # Landing page publik estetika luxury wellness modern
├── booking.php               # Wizard 5 langkah pemesanan reservasi online & integrasi WhatsApp
├── my-bookings.php           # Halaman riwayat reservasi pelanggan & modal interaktif rating/ulasan
├── therapist-portal.php      # Portal khusus terapis: jadwal tugas, WhatsApp klien, kontrol sesi, & testimoni
├── database.sql              # Skrip SQL lengkap dengan relasi Foreign Key (users, services, therapists, bookings, reviews)
└── README.md                 # Dokumentasi teknis & operasional aplikasi
```

---

## ✨ Fitur-Fitur Unggulan Sistem

### 1. Sisi Pelanggan (*Customer Experience*)
- **Landing Page Mewah & Informatif**: Showcase katalog layanan lengkap, galeri tim terapis bersertifikat beserta rating riil, sertifikasi higienis, FAQ interaktif, dan ulasan pelanggan.
- **Wizard Reservasi 5 Langkah**:
  1. Pilih metode: **Home Service** (panggilan ke rumah/hotel dengan input alamat lengkap) atau **Datang ke Klinik (*On-site*)**.
  2. Pilih menu layanan pijat beserta durasi menit dan kalkulasi tarif otomatis.
  3. Pilih preferensi gender terapis (Wanita / Pria / Bebas) atau pilih terapis favorit secara spesifik.
  4. Pemilihan tanggal dan slot jam dengan verifikasi ketersediaan terapis secara real-time.
  5. Pengisian data pemesan & catatan keluhan tubuh khusus.
- **Konfirmasi & Struk Digital WhatsApp**: Struk digital dengan kode unik `BKG-XXXXXX` dan tombol sekali klik untuk mengirim format konfirmasi langsung ke nomor WhatsApp Admin.
- **Pelacak Resi Instan**: Cek status reservasi kapan saja hanya dengan memasukkan kode booking tanpa harus login.
- **Halaman Riwayat Reservasi Saya (`my-bookings.php`)**: Pelanggan login dapat memantau seluruh status pesanannya (*Menunggu Konfirmasi*, *Dikonfirmasi*, *Sedang Berlangsung*, *Selesai*, *Dibatalkan*).
- **Penilaian & Ulasan Otentik (1–5 Bintang)**: Setelah sesi selesai (`completed`), pelanggan dapat memberikan rating bintang interaktif dan testimoni langsung untuk terapis yang melayaninya.

---

### 2. Sisi Terapis (*Therapist Portal*)
- **Tampilan Terpisah & Berbeda dari Pelanggan (`therapist-portal.php`)**: Terapis yang login tidak lagi diarahkan ke halaman pelanggan, melainkan ke portal kerja operasional khusus.
- **Manajemen Tugas Sesi**:
  - Tab *Tugas Aktif*, *Tugas Selesai*, dan *Semua Riwayat Jadwal*.
  - Menampilkan identitas pelanggan, jenis layanan, tanggal & jam sesi, durasi menit, serta alamat penjemputan (jika Home Service).
- **Direct WhatsApp Klien**: Terapis dapat langsung menghubungi pelanggan via tombol WhatsApp dengan pesan pengantar otomatis.
- **Kontrol Operasional Sesi Mandiri**:
  - Tombol **[▶ Mulai Sesi Perawatan]** untuk mengubah status menjadi `on_process`.
  - Tombol **[✓ Selesaikan Sesi Perawatan]** untuk menuntaskan sesi menjadi `completed`.
- **Toggle Status Ketersediaan Kerja Mandiri**: Terapis dapat mengaktifkan status *Tersedia (Siap Tugas)* atau *Sedang Libur / Istirahat* sendiri dari dashboard tanpa perlu meminta tolong admin.
- **Tab Ulasan & Kepuasan Pelanggan**: Membaca testimoni langsung dan memantau rata-rata rating kepuasan yang diberikan pelanggan.

---

### 3. Sisi Administrator (*Admin Management*)
- **Ringkasan Analitik Finansial**: Metrik total pendapatan kotor, estimasi pendapatan bersih, rasio terapis aktif, reservasi pending, dan sesi berjalan.
- **Manajemen Reservasi Lengkap**:
  - Filter berdasarkan status reservasi, tipe layanan, tanggal, dan pencarian instan.
  - Penetapan terapis (*Assign Therapist*) pada pesanan masuk.
  - Pembaruan status reservasi dan status pembayaran (*Lunas* / *Belum Bayar*).
  - Modal penampil detail alamat Home Service dan catatan keluhan tubuh klien.
- **Katalog Layanan Pijat**: Tambah layanan baru, edit tarif, durasi menit, kategori layanan, gambar etalase, dan deskripsi manfaat.
- **Manajemen Tim Terapis (CRUD Lengkap + Akun Login)**:
  - Tambah terapis baru dengan formulir pembuatan akun login (Email & Password custom, tombol intip password mata interaktif, dan dialog konfirmasi kredensial siap kirim).
  - Edit spesialisasi, jenis kelamin, nomor telepon, dan fitur **Reset Password**.
  - Hapus terapis dengan proteksi konfirmasi.
  - Tombol cepat **Buka Portal Terapis** untuk mengintip tampilan kerja masing-masing terapis.
- **Mode Pratinjau Administrator di Portal Terapis**: Admin dapat meninjau portal terapis mana pun melalui dropdown selector dinamis dengan *Session Memory* yang konsisten.

---

### 4. Sistem Rating Otomatis (*Customer-Driven Calculation*)
- **Kalkulasi Matematis Otomatis**: Rating terapis dihitung murni dari rata-rata ulasan bintang yang masuk:
  $$\text{Rating} = \frac{\sum \text{Bintang Ulasan Pelanggan}}{\text{Total Jumlah Ulasan}}$$
- **Anti-Manipulasi**: Kolom input manual rating oleh admin telah dikunci untuk menjamin orisinalitas penilaian.
- **Anti-Spam / Anti-Duplikasi**: Kolom `booking_id` pada tabel `reviews` bersifat `UNIQUE`, menjamin 1 transaksi reservasi hanya dapat diulas 1 kali.

---

## 🔒 Keamanan Sistem

1. **SQL Injection Prevention**: 100% query database menggunakan PDO Prepared Statements dengan parameter binding terisolasi.
2. **XSS Protection**: Seluruh output dinamis disanitasi menggunakan `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
3. **Password Security**: Menggunakan algoritma standar industri PHP `password_hash($pass, PASSWORD_BCRYPT)`.
4. **Role-Based Protection**: Endpoint API dan halaman portal dilindungi pengecekan peran pengguna (`requireLogin()`, `requireAdmin()`, kepemilikan ID terapis).

---

## 📄 Lisensi
Dikembangkan untuk keperluan operasional Sentosa Wellness & Massage Spa.
