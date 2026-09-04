<?php
// ==========================================================
// API Terapis (Daftar & Status Ketersediaan)
// ==========================================================

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
startSession();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$input = array_merge($_GET, $_POST, $_REQUEST, $jsonInput);

$db = Database::getConnection();

// --- 1. GET: Daftar Terapis ---
if ($method === 'GET') {
    $gender = $_GET['gender'] ?? '';
    $availableOnly = isset($_GET['available_only']) ? (int)$_GET['available_only'] : 0;

    $query = "SELECT t.id, t.user_id, t.specialization, t.gender, t.is_available, t.rating,
                     u.name, u.phone, u.email,
                     COUNT(r.id) as total_reviews
              FROM therapists t
              JOIN users u ON t.user_id = u.id
              LEFT JOIN reviews r ON t.id = r.therapist_id
              WHERE 1=1";
    $params = [];

    if ($availableOnly === 1) {
        $query .= " AND t.is_available = 1";
    }

    if (in_array($gender, ['male', 'female'])) {
        $query .= " AND t.gender = ?";
        $params[] = $gender;
    }

    $query .= " GROUP BY t.id, t.user_id, t.specialization, t.gender, t.is_available, t.rating, u.name, u.phone, u.email";
    $query .= " ORDER BY t.is_available DESC, t.rating DESC, u.name ASC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $therapists = $stmt->fetchAll();

    foreach ($therapists as &$t) {
        $t['gender_label'] = ($t['gender'] === 'female') ? 'Wanita' : 'Pria';
        $t['status_label'] = ($t['is_available'] == 1) ? 'Tersedia' : 'Sedang Libur / Istirahat';
        $t['rating_formatted'] = number_format((float)$t['rating'], 1);
        $t['total_reviews'] = (int)($t['total_reviews'] ?? 0);
    }

    jsonResponse([
        'success' => true,
        'count'   => count($therapists),
        'data'    => $therapists
    ]);
}

// --- Operasi Modifikasi Data Terapis (Admin atau Terapis Ybs) ---
$user = getCurrentUser();
if (!$user) {
    jsonResponse(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
}

// --- 2. Toggle Status Ketersediaan (is_available) ---
if ($action === 'toggle_availability' || $action === 'toggle_status') {
    $therapistId = (int)($input['therapist_id'] ?? ($input['id'] ?? 0));

    if ($therapistId <= 0) {
        // Jika terapis login sendiri, ambil ID miliknya
        if ($user['role'] === 'therapist') {
            $tStmt = $db->prepare("SELECT id FROM therapists WHERE user_id = ?");
            $tStmt->execute([$user['id']]);
            $therapistId = (int)$tStmt->fetchColumn();
        }
    }

    if ($therapistId <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID terapis tidak valid.'], 400);
    }

    // Hanya admin atau terapis bersangkutan yang boleh mengubah
    if ($user['role'] !== 'admin') {
        $checkOwner = $db->prepare("SELECT id FROM therapists WHERE id = ? AND user_id = ?");
        $checkOwner->execute([$therapistId, $user['id']]);
        if (!$checkOwner->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
    }

    // Ambil status saat ini jika status baru tidak dispesifikasikan
    if (isset($input['is_available'])) {
        $newStatus = (int)$input['is_available'] ? 1 : 0;
    } else {
        $curStmt = $db->prepare("SELECT is_available FROM therapists WHERE id = ?");
        $curStmt->execute([$therapistId]);
        $cur = (int)$curStmt->fetchColumn();
        $newStatus = ($cur === 1) ? 0 : 1;
    }

    $updateStmt = $db->prepare("UPDATE therapists SET is_available = ? WHERE id = ?");
    $updateStmt->execute([$newStatus, $therapistId]);

    jsonResponse([
        'success'      => true,
        'message'      => 'Status ketersediaan terapis berhasil diperbarui.',
        'is_available' => $newStatus,
        'status_label' => ($newStatus === 1) ? 'Tersedia' : 'Sedang Libur / Istirahat'
    ]);
}

// --- 3. Tambah Terapis Baru (Khusus Admin) ---
if ($action === 'create' && $user['role'] === 'admin') {
    $name = trim($input['name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $email = trim($input['email'] ?? '');
    $specialization = trim($input['specialization'] ?? '');
    $gender = ($input['gender'] ?? 'female') === 'male' ? 'male' : 'female';
    // Rating awal terapis baru adalah 5.00, selanjutnya murni dihitung dari ulasan nyata pelanggan
    $rating = 5.00;
    $isAvailable = isset($input['is_available']) ? (int)$input['is_available'] : 1;

    if (empty($name) || empty($phone) || empty($specialization)) {
        jsonResponse(['success' => false, 'message' => 'Nama, nomor WhatsApp, dan spesialisasi wajib diisi.'], 400);
    }

    if (empty($email)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $email = $slug . rand(10, 99) . '@spa.com';
    }

    // Cek duplikasi email
    $checkEmail = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $checkEmail->execute([$email]);
    if ($checkEmail->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email ini sudah terdaftar oleh pengguna lain.'], 400);
    }

    // Cek duplikasi nomor telepon
    $checkPhone = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
    $checkPhone->execute([$phone]);
    if ($checkPhone->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Nomor WhatsApp ini sudah terdaftar.'], 400);
    }

    // Password Akun Login Terapis
    $password = trim($input['password'] ?? '');
    if (empty($password)) {
        $password = 'therapist123'; // Password default jika tidak diisi oleh admin
    } elseif (strlen($password) < 6) {
        jsonResponse(['success' => false, 'message' => 'Password login minimal 6 karakter.'], 400);
    }
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $db->beginTransaction();
    try {
        // Buat user terapis
        $uStmt = $db->prepare("INSERT INTO users (name, email, phone, password, role, created_at) VALUES (?, ?, ?, ?, 'therapist', NOW())");
        $uStmt->execute([$name, $email, $phone, $hashedPassword]);
        $newUserId = (int)$db->lastInsertId();

        // Buat record profil terapis
        $tStmt = $db->prepare("INSERT INTO therapists (user_id, specialization, gender, is_available, rating, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $tStmt->execute([$newUserId, $specialization, $gender, $isAvailable, $rating]);
        $newTherapistId = (int)$db->lastInsertId();

        $db->commit();

        jsonResponse([
            'success'      => true,
            'message'      => "Terapis {$name} berhasil ditambahkan!\nEmail Login: {$email}\nPassword: {$password}",
            'therapist_id' => $newTherapistId,
            'credentials'  => [
                'email'    => $email,
                'password' => $password
            ]
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Gagal menambahkan terapis: ' . $e->getMessage()], 500);
    }
}

// --- 4. Update Profil / Data Terapis (Khusus Admin) ---
if ($action === 'update' && $user['role'] === 'admin') {
    $therapistId = (int)($input['id'] ?? 0);
    $name = trim($input['name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $email = trim($input['email'] ?? '');
    $specialization = trim($input['specialization'] ?? '');
    $gender = ($input['gender'] ?? 'female') === 'male' ? 'male' : 'female';
    $isAvailable = isset($input['is_available']) ? (int)$input['is_available'] : 1;

    if ($therapistId <= 0 || empty($specialization)) {
        jsonResponse(['success' => false, 'message' => 'Data terapis tidak lengkap.'], 400);
    }

    $tCheck = $db->prepare("SELECT user_id FROM therapists WHERE id = ?");
    $tCheck->execute([$therapistId]);
    $existing = $tCheck->fetch();
    if (!$existing) {
        jsonResponse(['success' => false, 'message' => 'Terapis tidak ditemukan.'], 404);
    }
    $userId = (int)$existing['user_id'];

    // Cek duplikasi email jika diisi
    if (!empty($email)) {
        $checkEmail = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $checkEmail->execute([$email, $userId]);
        if ($checkEmail->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Email sudah digunakan oleh akun lain.'], 400);
        }
    }

    // Cek duplikasi nomor telepon jika diisi
    if (!empty($phone)) {
        $checkPhone = $db->prepare("SELECT id FROM users WHERE phone = ? AND id != ? LIMIT 1");
        $checkPhone->execute([$phone, $userId]);
        if ($checkPhone->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Nomor WhatsApp sudah digunakan oleh akun lain.'], 400);
        }
    }

    $db->beginTransaction();
    try {
        if (!empty($name) || !empty($phone) || !empty($email)) {
            $uUpdate = $db->prepare("UPDATE users SET name = COALESCE(NULLIF(?, ''), name), phone = COALESCE(NULLIF(?, ''), phone), email = COALESCE(NULLIF(?, ''), email) WHERE id = ?");
            $uUpdate->execute([$name, $phone, $email, $userId]);
        }

        // Update Password jika diisi oleh admin
        $newPassword = trim($input['password'] ?? '');
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                jsonResponse(['success' => false, 'message' => 'Password baru minimal 6 karakter.'], 400);
            }
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $pwUpdate = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $pwUpdate->execute([$newHash, $userId]);
        }

        // Catatan: Rating terapis tidak dapat diubah manual oleh admin, melainkan dihitung murni dari ulasan pelanggan
        $tUpdate = $db->prepare("UPDATE therapists SET specialization = ?, gender = ?, is_available = ? WHERE id = ?");
        $tUpdate->execute([$specialization, $gender, $isAvailable, $therapistId]);

        $db->commit();

        jsonResponse([
            'success' => true,
            'message' => 'Data profil terapis berhasil diperbarui.'
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Gagal memperbarui terapis: ' . $e->getMessage()], 500);
    }
}

// --- 5. Hapus Terapis (Khusus Admin) ---
if ($action === 'delete' && $user['role'] === 'admin') {
    $therapistId = (int)($input['id'] ?? 0);

    if ($therapistId <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID terapis tidak valid.'], 400);
    }

    $tCheck = $db->prepare("SELECT t.id, t.user_id, u.name FROM therapists t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
    $tCheck->execute([$therapistId]);
    $therapist = $tCheck->fetch();

    if (!$therapist) {
        jsonResponse(['success' => false, 'message' => 'Terapis tidak ditemukan.'], 404);
    }

    // Cek apakah terapis masih memiliki jadwal aktif
    $activeCheck = $db->prepare("SELECT COUNT(*) FROM bookings WHERE therapist_id = ? AND status IN ('confirmed', 'on_process')");
    $activeCheck->execute([$therapistId]);
    $activeBookings = (int)$activeCheck->fetchColumn();

    if ($activeBookings > 0) {
        jsonResponse([
            'success' => false,
            'message' => "Terapis ini masih memiliki {$activeBookings} reservasi aktif. Silakan alihkan terapis pada reservasi tersebut terlebih dahulu sebelum menghapus."
        ], 400);
    }

    $db->beginTransaction();
    try {
        // Set therapist_id pada riwayat booking lama menjadi NULL agar riwayat pesanan tetap utuh
        $db->prepare("UPDATE bookings SET therapist_id = NULL WHERE therapist_id = ?")->execute([$therapistId]);

        // Hapus akun user terapis (foreign key ON DELETE CASCADE akan otomatis menghapus record di therapists)
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$therapist['user_id']]);

        $db->commit();

        jsonResponse([
            'success' => true,
            'message' => "Terapis {$therapist['name']} berhasil dihapus dari sistem."
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Gagal menghapus terapis: ' . $e->getMessage()], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Aksi tidak didukung.'], 400);
