-- ==========================================================
-- Database Schema for Massage Booking & Management System
-- Database Name: massage_booking_db
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `massage_booking_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `massage_booking_db`;

-- 1. Tabel Users
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `therapists`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(30) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'customer', 'therapist') NOT NULL DEFAULT 'customer',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Services
CREATE TABLE `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `duration_minutes` INT NOT NULL DEFAULT 60,
    `price` DECIMAL(10,2) NOT NULL,
    `description` TEXT NULL,
    `type` ENUM('home_service', 'clinic_only', 'both') NOT NULL DEFAULT 'both',
    `image_url` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Therapists
CREATE TABLE `therapists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `specialization` VARCHAR(255) NOT NULL,
    `gender` ENUM('male', 'female') NOT NULL DEFAULT 'female',
    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
    `rating` DECIMAL(3,2) NOT NULL DEFAULT 5.00,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_therapists_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel Bookings
CREATE TABLE `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_code` VARCHAR(30) NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `therapist_id` INT NULL,
    `service_id` INT NOT NULL,
    `booking_type` ENUM('home_service', 'clinic') NOT NULL DEFAULT 'clinic',
    `address` TEXT NULL,
    `schedule_datetime` DATETIME NOT NULL,
    `duration` INT NOT NULL,
    `total_price` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'confirmed', 'on_process', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `payment_status` ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid',
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bookings_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel Reviews (Rating & Ulasan dari Pelanggan)
CREATE TABLE `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `therapist_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reviews_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_customer` FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED DATA (DATA AWAL)
-- Password:
--   admin@spa.com : admin123
--   therapist / customer : user123
-- ==========================================================

-- Seed Users
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator Spa', 'admin@spa.com', '081234567890', '$2y$10$E8104iS3vr3pnFnnZnF9POLFRtUFlPj4HOxCCBIC87qoUk8g9oPp2', 'admin', NOW()),
(2, 'Siti Nurhaliza', 'siti@spa.com', '081234567891', '$2y$10$rF6M3Kf42CFmppGAleqC8Ocz8ffewHG43iy9VMDeD8z8TC551U65.', 'therapist', NOW()),
(3, 'Budi Santoso', 'budi@spa.com', '081234567892', '$2y$10$rF6M3Kf42CFmppGAleqC8Ocz8ffewHG43iy9VMDeD8z8TC551U65.', 'therapist', NOW()),
(4, 'Dewi Lestari', 'dewi@spa.com', '081234567893', '$2y$10$rF6M3Kf42CFmppGAleqC8Ocz8ffewHG43iy9VMDeD8z8TC551U65.', 'therapist', NOW()),
(5, 'Ahmad Fauzi', 'ahmad@spa.com', '081234567894', '$2y$10$rF6M3Kf42CFmppGAleqC8Ocz8ffewHG43iy9VMDeD8z8TC551U65.', 'therapist', NOW()),
(6, 'Bambang Wijaya', 'bambang@gmail.com', '081298765432', '$2y$10$rF6M3Kf42CFmppGAleqC8Ocz8ffewHG43iy9VMDeD8z8TC551U65.', 'customer', NOW()),
(7, 'Sarah Amalia', 'sarah@gmail.com', '081277665544', '$2y$10$rF6M3Kf42CFmppGAleqC8Ocz8ffewHG43iy9VMDeD8z8TC551U65.', 'customer', NOW());

-- Seed Therapists
INSERT INTO `therapists` (`id`, `user_id`, `specialization`, `gender`, `is_available`, `rating`, `created_at`) VALUES
(1, 2, 'Pijat Tradisional Jawa & Refleksi Kaki Relaksasi', 'female', 1, 4.95, NOW()),
(2, 3, 'Deep Tissue Massage & Shiatsu Terapi Otot Kaku', 'male', 1, 4.88, NOW()),
(3, 4, 'Aromatherapy Herbal & Hot Stone Wellness Spa', 'female', 1, 4.98, NOW()),
(4, 5, 'Sports Massage & Peregangan Cedera Ringan', 'male', 1, 4.85, NOW());

-- Seed Services
INSERT INTO `services` (`id`, `name`, `duration_minutes`, `price`, `description`, `type`, `image_url`, `is_active`, `created_at`) VALUES
(1, 'Traditional Javanese Massage', 90, 185000.00, 'Pijat relaksasi tubuh tradisional khas Jawa dengan teknik urut dan minyak esensial alami untuk melancarkan sirkulasi darah serta meredakan ketegangan otot.', 'both', 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800&auto=format&fit=crop&q=80', 1, NOW()),
(2, 'Deep Tissue & Shiatsu Therapy', 90, 225000.00, 'Kombinasi teknik pijatan dengan tekanan mendalam dan akupresur titik saraf Jepang. Sangat efektif untuk mengatasi otot kaku kronis, leher kaku, dan migrain.', 'clinic_only', 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=800&auto=format&fit=crop&q=80', 1, NOW()),
(3, 'Reflexology & Foot Acupressure', 60, 125000.00, 'Terapi pemijatan titik-titik refleksi pada telapak kaki dan tangan untuk menstimulasi organ vital tubuh, meredakan stres, dan memulihkan stamina.', 'both', 'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?w=800&auto=format&fit=crop&q=80', 1, NOW()),
(4, 'Aromatherapy & Herbal Warm Compress', 120, 280000.00, 'Perawatan tubuh menyeluruh menggunakan paduan minyak aroma terapi lavender/eucalyptus dan kompres rempah herbal hangat nusantara untuk detoksifikasi optimal.', 'both', 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?w=800&auto=format&fit=crop&q=80', 1, NOW()),
(5, 'Express Back, Neck & Shoulder Relief', 45, 95000.00, 'Pijat intensif fokus pada leher, bahu, dan punggung atas. Solusi kilat dan manjur untuk Anda yang lelah bekerja di depan meja laptop seharian.', 'both', 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800&auto=format&fit=crop&q=80', 1, NOW()),
(6, 'Post-Workout Sports Massage', 75, 195000.00, 'Terapi relaksasi khusus bagi penggiat olahraga untuk meregangkan serat otot tegang, mencegah kram, dan mempercepat pembuangan asam laktat.', 'clinic_only', 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=800&auto=format&fit=crop&q=80', 1, NOW());

-- Seed Bookings
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `therapist_id`, `service_id`, `booking_type`, `address`, `schedule_datetime`, `duration`, `total_price`, `status`, `payment_status`, `notes`, `created_at`) VALUES
(1, 'BKG-260901-7891', 6, 1, 1, 'home_service', 'Jl. Gandaria Tengah II No. 14, Kebayoran Baru, Jakarta Selatan (Pagar Hitam, seberang masjid)', DATE_ADD(NOW(), INTERVAL 1 DAY), 90, 185000.00, 'confirmed', 'paid', 'Mohon fokus pijat area belikat dan pinggang kiri.', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 'BKG-260902-3412', 7, 2, 2, 'clinic', NULL, DATE_ADD(NOW(), INTERVAL 2 HOUR), 90, 225000.00, 'on_process', 'paid', 'Tekanan pijatan preferensi sedang ke kuat.', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(3, 'BKG-260902-9981', 6, NULL, 3, 'home_service', 'Apartemen Sudirman Tower Lt. 12 Unit 12B, Jakarta Pusat', DATE_ADD(NOW(), INTERVAL 2 DAY), 60, 125000.00, 'pending', 'unpaid', 'Terapis mohon bawa minyak kayu putih bila ada.', NOW()),
(4, 'BKG-260831-1120', 7, 3, 4, 'clinic', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), 120, 280000.00, 'completed', 'paid', 'Sangat puas dengan pelayanannya.', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Seed Reviews
INSERT INTO `reviews` (`id`, `booking_id`, `customer_id`, `therapist_id`, `rating`, `comment`, `created_at`) VALUES
(1, 4, 7, 3, 5, 'Pelayanan Ibu Dewi sangat profesional! Pijatan aromaterapi dan batu hangatnya benar-benar meredakan pegal setelah perjalanan jauh. Sangat direkomendasikan!', DATE_SUB(NOW(), INTERVAL 1 DAY));
