<?php
// ==========================================================
// API Autentikasi: Login, Register, Logout, Me
// ==========================================================

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
startSession();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Ambil input JSON jika ada
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$input = array_merge($_POST, $jsonInput);

if ($action === '') {
    // Default action by method
    if ($method === 'GET') {
        $action = 'me';
    }
}

$db = Database::getConnection();

switch ($action) {
    case 'login':
        if ($method !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $identifier = trim($input['identifier'] ?? ($input['email'] ?? ''));
        $password = $input['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Email/No. Telepon dan password wajib diisi.'], 400);
        }

        $stmt = $db->prepare("SELECT * FROM users WHERE email = :id_email OR phone = :id_phone LIMIT 1");
        $stmt->execute([':id_email' => $identifier, ':id_phone' => $identifier]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            jsonResponse(['success' => false, 'message' => 'Kombinasi email/nomor telepon dan password salah.'], 401);
        }

        // Simpan sesi
        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role'  => $user['role']
        ];

        // Jika terapis, sertakan ID terapis
        if ($user['role'] === 'therapist') {
            $tStmt = $db->prepare("SELECT id, specialization, gender, is_available, rating FROM therapists WHERE user_id = ?");
            $tStmt->execute([$user['id']]);
            $therapist = $tStmt->fetch();
            if ($therapist) {
                $_SESSION['user']['therapist_id'] = (int)$therapist['id'];
                $_SESSION['user']['specialization'] = $therapist['specialization'];
            }
        }

        jsonResponse([
            'success' => true,
            'message' => 'Login berhasil! Selamat datang, ' . htmlspecialchars($user['name']),
            'user'    => $_SESSION['user']
        ]);
        break;

    case 'register':
        if ($method !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($name) || empty($email) || empty($phone) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Semua kolom pendaftaran wajib diisi.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Format email tidak valid.'], 400);
        }

        if (strlen($password) < 6) {
            jsonResponse(['success' => false, 'message' => 'Password minimal harus 6 karakter.'], 400);
        }

        // Cek duplikasi email
        $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.'], 400);
        }

        // Cek duplikasi no telepon
        $checkPhone = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
        $checkPhone->execute([$phone]);
        if ($checkPhone->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Nomor telepon ini sudah terdaftar.'], 400);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $insertStmt = $db->prepare("INSERT INTO users (name, email, phone, password, role, created_at) VALUES (?, ?, ?, ?, 'customer', NOW())");
        $insertStmt->execute([$name, $email, $phone, $hashedPassword]);
        $newUserId = (int)$db->lastInsertId();

        // Otomatis login setelah registrasi
        $_SESSION['user'] = [
            'id'    => $newUserId,
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
            'role'  => 'customer'
        ];

        jsonResponse([
            'success' => true,
            'message' => 'Pendaftaran berhasil! Akun Anda siap digunakan.',
            'user'    => $_SESSION['user']
        ], 201);
        break;

    case 'logout':
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        jsonResponse([
            'success' => true,
            'message' => 'Anda telah berhasil keluar (logout).'
        ]);
    case 'change_password':
        if ($method !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $currentUser = getCurrentUser();
        if (!$currentUser) {
            jsonResponse(['success' => false, 'message' => 'Sesi Anda telah berakhir. Silakan login terlebih dahulu.'], 401);
        }

        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            jsonResponse(['success' => false, 'message' => 'Kata sandi saat ini dan kata sandi baru wajib diisi.'], 400);
        }

        if (strlen($newPassword) < 6) {
            jsonResponse(['success' => false, 'message' => 'Kata sandi baru minimal harus 6 karakter.'], 400);
        }

        if (!empty($confirmPassword) && $newPassword !== $confirmPassword) {
            jsonResponse(['success' => false, 'message' => 'Konfirmasi kata sandi baru tidak cocok.'], 400);
        }

        // Cek kecocokan password saat ini di database
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$currentUser['id']]);
        $userRow = $stmt->fetch();

        if (!$userRow || !password_verify($currentPassword, $userRow['password'])) {
            jsonResponse(['success' => false, 'message' => 'Kata sandi saat ini tidak sesuai.'], 400);
        }

        // Update password baru dengan hash bcrypt
        $newHashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$newHashed, $currentUser['id']]);

        jsonResponse([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui! Silakan gunakan kata sandi baru untuk login berikutnya.'
        ]);
        break;

    case 'me':
        $currentUser = getCurrentUser();
        if ($currentUser) {
            jsonResponse([
                'success'   => true,
                'logged_in' => true,
                'user'      => $currentUser
            ]);
        } else {
            jsonResponse([
                'success'   => true,
                'logged_in' => false,
                'user'      => null
            ]);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Action tidak dikenali.'], 404);
        break;
}
