-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Sep 2026 pada 13.18
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `massage_booking_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_code` varchar(30) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `therapist_id` int(11) DEFAULT NULL,
  `service_id` int(11) NOT NULL,
  `booking_type` enum('home_service','clinic') NOT NULL DEFAULT 'clinic',
  `address` text DEFAULT NULL,
  `schedule_datetime` datetime NOT NULL,
  `duration` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','on_process','completed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 60,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('home_service','clinic_only','both') NOT NULL DEFAULT 'both',
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `services`
--

INSERT INTO `services` (`id`, `name`, `duration_minutes`, `price`, `description`, `type`, `image_url`, `is_active`, `created_at`) VALUES
(1, 'Traditional Javanese Massage', 90, 185000.00, 'Pijat relaksasi tubuh tradisional khas Jawa dengan teknik urut dan minyak esensial alami untuk melancarkan sirkulasi darah serta meredakan ketegangan otot.', 'both', 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800&auto=format&fit=crop&q=80', 1, '2026-09-03 10:07:27'),
(2, 'Deep Tissue & Shiatsu Therapy', 90, 225000.00, 'Kombinasi teknik pijatan dengan tekanan mendalam dan akupresur titik saraf Jepang. Sangat efektif untuk mengatasi otot kaku kronis, leher kaku, dan migrain.', 'clinic_only', 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=800&auto=format&fit=crop&q=80', 1, '2026-09-03 10:07:27'),
(3, 'Reflexology & Foot Acupressure', 60, 125000.00, 'Terapi pemijatan titik-titik refleksi pada telapak kaki dan tangan untuk menstimulasi organ vital tubuh, meredakan stres, dan memulihkan stamina.', 'both', 'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?w=800&auto=format&fit=crop&q=80', 1, '2026-09-03 10:07:27'),
(4, 'Aromatherapy & Herbal Warm Compress', 120, 280000.00, 'Perawatan tubuh menyeluruh menggunakan paduan minyak aroma terapi lavender/eucalyptus dan kompres rempah herbal hangat nusantara untuk detoksifikasi optimal.', 'both', 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?w=800&auto=format&fit=crop&q=80', 1, '2026-09-03 10:07:27'),
(5, 'Express Back, Neck & Shoulder Relief', 45, 95000.00, 'Pijat intensif fokus pada leher, bahu, dan punggung atas. Solusi kilat dan manjur untuk Anda yang lelah bekerja di depan meja laptop seharian.', 'both', 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800&auto=format&fit=crop&q=80', 1, '2026-09-03 10:07:27'),
(6, 'Post-Workout Sports Massage', 75, 195000.00, 'Terapi relaksasi khusus bagi penggiat olahraga untuk meregangkan serat otot tegang, mencegah kram, dan mempercepat pembuangan asam laktat.', 'clinic_only', 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=800&auto=format&fit=crop&q=80', 1, '2026-09-03 10:07:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `therapists`
--

CREATE TABLE `therapists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `gender` enum('male','female') NOT NULL DEFAULT 'female',
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `rating` decimal(3,2) NOT NULL DEFAULT 5.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer','therapist') NOT NULL DEFAULT 'customer',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator Spa', 'adminspa', '085659719922', '$2y$10$7/HidAd81BFSG8GFxJeoL.MoBArMDYGgwYK3x68e1WVwQorYz3vm2', 'admin', '2026-09-03 10:07:27');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `fk_bookings_customer` (`customer_id`),
  ADD KEY `fk_bookings_therapist` (`therapist_id`),
  ADD KEY `fk_bookings_service` (`service_id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD KEY `fk_reviews_customer` (`customer_id`),
  ADD KEY `fk_reviews_therapist` (`therapist_id`);

--
-- Indeks untuk tabel `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `therapists`
--
ALTER TABLE `therapists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `therapists`
--
ALTER TABLE `therapists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `fk_bookings_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `therapists`
--
ALTER TABLE `therapists`
  ADD CONSTRAINT `fk_therapists_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
