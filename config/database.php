<?php
// ==========================================================
// Database & Application Configuration
// ==========================================================

// Atur Timezone Default Sistem ke Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

// Definisi Konstanta Aplikasi
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'massage_booking_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Sentosa Wellness & Massage Spa');
define('APP_TAGLINE', 'Layanan Pijat & Spa Panggilan Profesional Berstandar Bintang Lima (Home Service)');
define('APP_PHONE', '6285659719922'); // Format nomor WhatsApp internasional tanpa tanda +
define('APP_ADDRESS', 'Jl. Senopati No. 45, Kebayoran Baru, Jakarta Selatan');
define('APP_EMAIL', 'info@sentosaspa.com');
define('APP_HOURS', 'Setiap Hari: 08:00 - 22:00 WIB');

/**
 * Class Database - Mengelola koneksi PDO ke MySQL
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    DB_HOST,
                    DB_PORT,
                    DB_NAME,
                    DB_CHARSET
                );

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . ", time_zone = '+07:00'"
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                self::ensureSchema(self::$instance);
            } catch (PDOException $e) {
                // Jika database belum ada, coba buat otomatis dan impor schema
                if ($e->getCode() == 1049) {
                    self::autoInitializeDatabase();
                    return self::getConnection();
                }

                // Log error dan tampilkan response bersahabat
                error_log("Database Connection Error: " . $e->getMessage());
                if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Gagal terhubung ke database. Pastikan MySQL XAMPP sudah aktif.'
                    ]);
                    exit;
                }
                die("<h3>Koneksi Database Gagal</h3><p>Pastikan Apache & MySQL di XAMPP Control Panel sudah berjalan (Running).</p><p>Detail: " . htmlspecialchars($e->getMessage()) . "</p>");
            }
        }

        return self::$instance;
    }

    /**
     * Otomatis inisialisasi database jika database belum dibuat di MySQL
     */
    private static function autoInitializeDatabase(): void
    {
        try {
            $rootDsn = sprintf("mysql:host=%s;port=%s;charset=%s", DB_HOST, DB_PORT, DB_CHARSET);
            $pdo = new PDO($rootDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $sqlFile = dirname(__DIR__) . '/database.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $pdo->exec($sql);
            }
        } catch (Exception $ex) {
            error_log("Auto DB Init Error: " . $ex->getMessage());
        }
    }

    /**
     * Memastikan skema tabel tambahan (seperti reviews) selalu tersedia
     */
    private static function ensureSchema(PDO $pdo): void
    {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `reviews` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        } catch (Exception $ex) {
            // Abaikan jika sudah ada atau terhambat constraint
        }

        try {
            $pdo->exec("ALTER TABLE `therapists` ADD COLUMN `avatar_url` VARCHAR(500) NULL AFTER `rating`");
        } catch (Exception $ex) {
            // Abaikan jika sudah ada kolom avatar_url
        }
    }
}

/**
 * Memulai session PHP secara aman jika belum aktif
 */
function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Parameter session cookie yang aman
        session_set_cookie_params([
            'lifetime' => 86400 * 7, // 7 hari
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

/**
 * Mengirim output response JSON standar
 */
function jsonResponse(array $data, int $statusCode = 200): void
{
    // Bersihkan buffer output sebelum kirim JSON
    if (ob_get_level()) {
        ob_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Sanitasi string input untuk mencegah XSS
 */
function sanitize(?string $input): string
{
    if ($input === null) return '';
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Mendapatkan data pengguna yang sedang login saat ini
 */
function getCurrentUser(): ?array
{
    startSession();
    return $_SESSION['user'] ?? null;
}

/**
 * Verifikasi role pengguna
 */
function requireLogin(): array
{
    $user = getCurrentUser();
    if (!$user) {
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
            jsonResponse(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
        }
        $redirectBase = function_exists('getBaseUrl') ? getBaseUrl() : '';
        $redirectUrl = ($redirectBase ?: '.') . '/index.php?login_required=1';
        header("Location: $redirectUrl");
        exit;
    }
    return $user;
}

function requireAdmin(): array
{
    $user = requireLogin();
    if (($user['role'] ?? '') !== 'admin') {
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
            jsonResponse(['success' => false, 'message' => 'Akses ditolak. Fitur ini khusus Administrator.'], 403);
        }
        $redirectBase = function_exists('getBaseUrl') ? getBaseUrl() : '';
        $redirectUrl = ($redirectBase ?: '.') . '/index.php?unauthorized=1';
        header("Location: $redirectUrl");
        exit;
    }
    return $user;
}

/**
 * Format mata uang Rupiah
 */
function formatRupiah(float|int|string $amount): string
{
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

/**
 * Format tanggal dan waktu Bahasa Indonesia
 */
function formatDateTimeId(string $datetime): string
{
    $timestamp = strtotime($datetime);
    if (!$timestamp) return $datetime;

    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $namaHari = $hari[date('w', $timestamp)];
    $tgl = date('j', $timestamp);
    $namaBulan = $bulan[(int)date('n', $timestamp)];
    $thn = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);

    return "$namaHari, $tgl $namaBulan $thn - $jam WIB";
}

/**
 * Generator Kode Booking Unik: BKG-YYMMDD-XXXX
 */
function generateBookingCode(): string
{
    $datePart = date('ymd');
    $randPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    return "BKG-{$datePart}-{$randPart}";
}

/**
 * Mendapatkan base path URL aplikasi web secara dinamis
 */
function getBaseUrl(): string
{
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    if (basename($scriptDir) === 'admin' || basename($scriptDir) === 'api' || basename($scriptDir) === 'views') {
        $scriptDir = dirname($scriptDir);
    }
    if ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') {
        $scriptDir = '';
    }
    return $scriptDir;
}
