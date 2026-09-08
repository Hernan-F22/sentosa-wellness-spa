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
            'role'  => $user['role'],
            'avatar_url' => $user['avatar_url'] ?? null
        ];

        // Jika terapis, sertakan ID terapis & avatar_url
        if ($user['role'] === 'therapist') {
            $tStmt = $db->prepare("SELECT id, specialization, gender, is_available, rating, avatar_url FROM therapists WHERE user_id = ?");
            $tStmt->execute([$user['id']]);
            $therapist = $tStmt->fetch();
            if ($therapist) {
                $_SESSION['user']['therapist_id'] = (int)$therapist['id'];
                $_SESSION['user']['specialization'] = $therapist['specialization'];
                if (!empty($therapist['avatar_url'])) {
                    $_SESSION['user']['avatar_url'] = $therapist['avatar_url'];
                }
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

    case 'update_profile':
        if ($method !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $currentUser = getCurrentUser();
        if (!$currentUser) {
            jsonResponse(['success' => false, 'message' => 'Sesi Anda telah berakhir. Silakan login terlebih dahulu.'], 401);
        }

        $name = trim($input['name'] ?? $currentUser['name']);
        $email = trim($input['email'] ?? $currentUser['email']);
        $phone = trim($input['phone'] ?? $currentUser['phone']);
        $avatarUrl = array_key_exists('avatar_url', $input) ? (trim($input['avatar_url'] ?? '') ?: null) : ($currentUser['avatar_url'] ?? null);

        if (empty($name) || empty($email) || empty($phone)) {
            jsonResponse(['success' => false, 'message' => 'Nama, email, dan nomor telepon tidak boleh kosong.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Format email tidak valid.'], 400);
        }

        // Cek duplikasi email pada akun lain
        $chkEmail = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $chkEmail->execute([$email, $currentUser['id']]);
        if ($chkEmail->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Email ini sudah digunakan oleh akun lain.'], 400);
        }

        // Cek duplikasi nomor telepon pada akun lain
        $chkPhone = $db->prepare("SELECT id FROM users WHERE phone = ? AND id != ? LIMIT 1");
        $chkPhone->execute([$phone, $currentUser['id']]);
        if ($chkPhone->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Nomor telepon ini sudah digunakan oleh akun lain.'], 400);
        }

        // Update tabel users
        $updateStmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, avatar_url = ? WHERE id = ?");
        $updateStmt->execute([$name, $email, $phone, $avatarUrl, $currentUser['id']]);

        // Jika terapis, perbarui juga therapists.avatar_url
        if ($currentUser['role'] === 'therapist') {
            $tUpdate = $db->prepare("UPDATE therapists SET avatar_url = ? WHERE user_id = ?");
            $tUpdate->execute([$avatarUrl, $currentUser['id']]);
        }

        // Perbarui data sesi
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['avatar_url'] = $avatarUrl;

        jsonResponse([
            'success' => true,
            'message' => 'Profil berhasil diperbarui!',
            'user'    => $_SESSION['user']
        ]);
        break;

    case 'me':
        $currentUser = getCurrentUser();
        if ($currentUser) {
            // Ambil data terbaru dari tabel users
            $uStmt = $db->prepare("SELECT id, name, email, phone, role, avatar_url FROM users WHERE id = ?");
            $uStmt->execute([$currentUser['id']]);
            $freshUser = $uStmt->fetch();
            if ($freshUser) {
                $currentUser['name'] = $freshUser['name'];
                $currentUser['email'] = $freshUser['email'];
                $currentUser['phone'] = $freshUser['phone'];
                $currentUser['role'] = $freshUser['role'];
                $currentUser['avatar_url'] = $freshUser['avatar_url'] ?? null;
            }

            if ($currentUser['role'] === 'therapist') {
                $tStmt = $db->prepare("SELECT id, specialization, gender, is_available, rating, avatar_url FROM therapists WHERE user_id = ?");
                $tStmt->execute([$currentUser['id']]);
                $therapist = $tStmt->fetch();
                if ($therapist) {
                    $currentUser['therapist_id'] = (int)$therapist['id'];
                    $currentUser['specialization'] = $therapist['specialization'];
                    if (!empty($therapist['avatar_url'])) {
                        $currentUser['avatar_url'] = $therapist['avatar_url'];
                    }
                }
            }
            $_SESSION['user'] = $currentUser;

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
