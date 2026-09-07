# 🌿 Sentosa Wellness & Massage Spa Web Application

Sistem Informasi & Manajemen Reservasi Layanan Spa / Pijat Modern (*Online/Home Service & Offline/On-site Spa*) berbasis Web Responsif dengan arsitektur **Multi-Role (Pelanggan, Terapis, Administrator)**, kalkulasi rating otomatis berbasis ulasan nyata pelanggan, dan portal kerja mandiri untuk terapis.

> **Mendukung Dual-Deployment**:
> 1. ⚡ **Vercel / Netlify / GitHub Pages (100% Serverless Static HTML + LocalStorage)**: Siap di-deploy ke Vercel tanpa perlu server PHP maupun database server.
> 2. 🐘 **Apache + PHP + MySQL (XAMPP / cPanel Hosting)**: Fullstack PHP Native dengan database relasional MySQL.

---

## 🛠 Stack Teknologi

- **Frontend**: HTML5, Tailwind CSS (CDN modern), JavaScript Vanilla (ES6+), FontAwesome 6 Free, Google Fonts (*Plus Jakarta Sans* & *Playfair Display*).
- **Client Store (Vercel)**: `localStorage` State Management dengan seed data bawaan (`js/store.js` & `js/components.js`).
- **Backend (Opsional XAMPP)**: PHP Native (PHP 8+, OOP, PDO MySQL dengan Prepared Statements anti-SQL Injection).
- **Database (Opsional XAMPP)**: MySQL / MariaDB (`massage_booking_db`).

---

## 🚀 Panduan Deployment ke Vercel (1-Klik Deploy)

Aplikasi ini telah dikonfigurasi secara lengkap dengan file `.html` dan `vercel.json` sehingga **100% siap langsung dideploy ke Vercel**:

1. **Push Proyek ke Repositori GitHub**:
   ```bash
   git init
   git add .
   git commit -m "feat: deploy ready for vercel with static html architecture"
   git branch -M main
   git remote add origin https://github.com/USERNAME/sentosa-wellness-spa.git
   git push -u origin main
   ```

2. **Deploy di Vercel**:
   - Masuk ke dashboard [Vercel](https://vercel.com).
   - Klik **"Add New..."** > **"Project"**.
   - Hubungkan dengan repositori GitHub Anda (`sentosa-wellness-spa`).
   - Pada bagian **Framework Preset**, pilih **"Other"** (Root directory dibiarkan default `./`).
   - Klik **"Deploy"**.

3. **Selesai!**
   - Website Anda langsung aktif di URL `https://sentosa-wellness-spa.vercel.app`.
   - Seluruh halaman bekerja tanpa server:
     - `index.html` (Landing Page & Menu Katalog)
     - `booking.html` (Wizard Reservasi 5 Langkah & WhatsApp Struk)
     - `my-bookings.html` (Riwayat Booking Pelanggan & Penilaian Bintang Ulasan)
     - `therapist-portal.html` (Portal Jadwal Tugas Terapis & Operasional Sesi)
     - `admin/dashboard.html` (Panel Admin, Metrik Finansial, Layanan, Terapis)

---

## 💻 Panduan Menjalankan Lokal di XAMPP

1. **Jalankan Apache & MySQL** di XAMPP Control Panel.
2. **Koneksi Database**:
   - Skrip SQL lengkap tersedia di [`database.sql`](database.sql) (Database: `massage_booking_db`).
3. **Buka Browser**:
   - Versi Statis (Vercel view): [http://localhost/web_pjt/index.html](http://localhost/web_pjt/index.html)
   - Versi PHP MySQL: [http://localhost/web_pjt/index.php](http://localhost/web_pjt/index.php)

---

## 🔑 Akun Demo Bawaan (Demo Credentials)

| Peran (*Role*) | Email / Identitas Login | Password | Akses & Fungsionalitas |
|---|---|---|---|
| **Administrator** | `admin@spa.com` / `081234567890` | `admin123` | Akses penuh Admin Dashboard, kelola reservasi, katalog layanan, CRUD terapis + buat password akun, analitik pendapatan, dan pratinjau portal terapis. |
| **Pelanggan (*Customer*)** | `bambang@gmail.com` / `081298765432` | `user123` | Reservasi layanan, pelacakan status pesanan, melihat riwayat booking pribadi, dan memberikan penilaian bintang & ulasan setelah sesi tuntas. |
| **Terapis Wanita (Siti)** | `siti@spa.com` | `user123` | Portal kerja mandiri, daftar penugasan sesi klien, WhatsApp direct chat klien, tombol mulai & selesaikan sesi, toggle ketersediaan kerja, dan ulasan kepuasan klien. |
| **Terapis Pria (Budi)** | `budi@spa.com` | `user123` | Spesialis Deep Tissue Massage & Shiatsu dengan portal penugasan terapis mandiri. |
| **Terapis Wanita (Dewi)** | `dewi@spa.com` | `user123` | Spesialis Balinese & Hot Stone Massage dengan portal penugasan terapis mandiri. |
| **Terapis Pria (Ahmad)** | `ahmad@spa.com` | `user123` | Spesialis Sport Massage & Refleksi dengan portal penugasan terapis mandiri. |

---

## 📂 Struktur Direktori

```
web_pjt/
├── js/
│   ├── store.js              # State store client-side (LocalStorage) untuk Vercel
│   └── components.js         # Shared UI: Navbar dinamis, Footer, Auth Modal, Pelacak Resi
├── admin/
│   ├── dashboard.html        # Dashboard Admin versi Vercel (HTML + JS)
│   └── dashboard.php         # Dashboard Admin versi XAMPP (PHP)
├── index.html                # Landing page utama publik (Vercel)
├── booking.html              # Form wizard reservasi 5 langkah (Vercel)
├── my-bookings.html          # Riwayat booking pelanggan & rating ulasan (Vercel)
├── therapist-portal.html     # Portal operasional kerja terapis (Vercel)
├── vercel.json               # Konfigurasi routing clean URLs Vercel
├── index.php                 # Landing page versi PHP
├── booking.php               # Booking wizard versi PHP
├── my-bookings.php           # My bookings versi PHP
├── therapist-portal.php      # Portal terapis versi PHP
├── database.sql              # Skrip SQL skema relasi & seed data
└── README.md                 # Dokumentasi teknis & panduan deployment
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
- **Halaman Riwayat Reservasi Saya (`my-bookings.html`)**: Pelanggan login dapat memantau seluruh status pesanannya (*Menunggu Konfirmasi*, *Dikonfirmasi*, *Sedang Berlangsung*, *Selesai*, *Dibatalkan*).
- **Penilaian & Ulasan Otentik (1–5 Bintang)**: Setelah sesi selesai (`completed`), pelanggan dapat memberikan rating bintang interaktif dan testimoni langsung untuk terapis yang melayaninya.

### 2. Sisi Terapis (*Therapist Portal*)
- **Tampilan Terpisah & Berbeda dari Pelanggan (`therapist-portal.html`)**: Terapis diarahkan ke portal kerja operasional khusus.
- **Manajemen Tugas Sesi**: Tab *Tugas Aktif*, *Tugas Selesai*, dan *Semua Riwayat Jadwal*.
- **Direct WhatsApp Klien**: Terapis dapat langsung menghubungi pelanggan via WhatsApp.
- **Kontrol Operasional Sesi Mandiri**: Tombol **[▶ Mulai Sesi Perawatan]** (`on_process`) dan **[✓ Selesaikan Sesi Perawatan]** (`completed`).
- **Toggle Status Ketersediaan Kerja Mandiri**: Switch *Tersedia (Siap Tugas)* atau *Sedang Libur / Istirahat*.
- **Tab Ulasan & Kepuasan Pelanggan**: Membaca testimoni langsung dan memantau rata-rata rating kepuasan yang diberikan pelanggan.

### 3. Sisi Administrator (*Admin Management*)
- **Ringkasan Analitik Finansial**: Metrik total pendapatan kotor, estimasi pendapatan bersih, rasio terapis aktif, reservasi pending, dan sesi berjalan.
- **Manajemen Reservasi Lengkap**: Filter status, pencarian instan, penetapan terapis (*Assign Therapist*), dan pembaruan status pembayaran (*Lunas* / *Belum Bayar*).
- **Katalog Layanan Pijat**: Tambah layanan baru, edit tarif, durasi menit, kategori layanan, gambar etalase, dan deskripsi manfaat.
- **Manajemen Tim Terapis (CRUD Lengkap + Akun Login)**:
  - Tambah terapis baru dengan formulir pembuatan akun login (Email & Password custom, tombol intip password mata interaktif).
  - Edit spesialisasi, nomor telepon, dan fitur **Reset Password**.
  - Tombol cepat **Buka Portal Terapis** untuk mengintip tampilan kerja masing-masing terapis.
- **Mode Pratinjau Administrator di Portal Terapis**: Admin dapat meninjau portal terapis mana pun melalui dropdown selector dinamis.

### 4. Sistem Rating Otomatis (*Customer-Driven Calculation*)
- **Kalkulasi Matematis Otomatis**: Rating terapis dihitung murni dari rata-rata ulasan bintang pelanggan yang masuk:
  $$\text{Rating} = \frac{\sum \text{Bintang Ulasan Pelanggan}}{\text{Total Jumlah Ulasan}}$$
- **Anti-Manipulasi**: Kolom input manual rating oleh admin telah dikunci untuk menjamin orisinalitas penilaian.
- **Anti-Spam / Anti-Duplikasi**: 1 nomor reservasi hanya dapat diulas 1 kali.

---

## 📄 Lisensi
Dikembangkan untuk keperluan operasional Sentosa Wellness & Massage Spa.
