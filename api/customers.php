<?php
// ==========================================================
// API Manajemen Pelanggan (Customer Management)
// Khusus Akses: Administrator
// ==========================================================

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
startSession();

$currentUser = getCurrentUser();
if (!$currentUser || $currentUser['role'] !== 'admin') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Fitur ini hanya untuk Administrator.'], 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$input = array_merge($_GET, $_POST, $_REQUEST, $jsonInput);

$db = Database::getConnection();

// --- 1. GET: Daftar Pelanggan dengan Agregat Booking ---
if ($method === 'GET' && ($action === '' || $action === 'list')) {
    $search = trim($_GET['search'] ?? '');

    $query = "SELECT u.id, u.name, u.email, u.phone, u.avatar_url, u.created_at,
                     COUNT(b.id) AS total_bookings,
                     COALESCE(SUM(CASE WHEN b.status = 'completed' THEN b.total_price ELSE 0 END), 0) AS total_spent,
                     MAX(b.created_at) AS last_booking_at,
                     SUM(CASE WHEN b.status IN ('pending', 'confirmed', 'on_process') THEN 1 ELSE 0 END) AS active_bookings
              FROM users u
              LEFT JOIN bookings b ON u.id = b.customer_id
              WHERE u.role = 'customer'";
    $params = [];

    if ($search !== '') {
        $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $query .= " GROUP BY u.id, u.name, u.email, u.phone, u.avatar_url, u.created_at";
    $query .= " ORDER BY u.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();

    foreach ($customers as &$c) {
        $c['id'] = (int)$c['id'];
        $c['total_bookings'] = (int)$c['total_bookings'];
        $c['total_spent'] = (float)$c['total_spent'];
        $c['active_bookings'] = (int)$c['active_bookings'];
        $c['formatted_spent'] = 'Rp ' . number_format($c['total_spent'], 0, ',', '.');
    }

    jsonResponse([
        'success' => true,
        'count'   => count($customers),
        'data'    => $customers
    ]);
}

// --- 2. POST: Tambah Pelanggan Baru (action=create) ---
if ($action === 'create') {
    if ($method !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $password = trim($input['password'] ?? 'user123');
    $avatarUrl = trim($input['avatar_url'] ?? '') ?: null;

    if (empty($name) || empty($email) || empty($phone)) {
        jsonResponse(['success' => false, 'message' => 'Nama, email, dan nomor telepon wajib diisi.'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Format email tidak valid.'], 400);
    }

    if (strlen($password) < 6) {
        jsonResponse(['success' => false, 'message' => 'Password minimal harus 6 karakter.'], 400);
    }

    // Cek duplikasi email
    $chkEmail = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $chkEmail->execute([$email]);
    if ($chkEmail->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email ini sudah terdaftar.'], 400);
    }

    // Cek duplikasi no telepon
    $chkPhone = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
    $chkPhone->execute([$phone]);
    if ($chkPhone->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Nomor telepon ini sudah terdaftar.'], 400);
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, role, avatar_url, created_at) VALUES (?, ?, ?, ?, 'customer', ?, NOW())");
    $stmt->execute([$name, $email, $phone, $hashed, $avatarUrl]);
    $newId = (int)$db->lastInsertId();

    jsonResponse([
        'success' => true,
        'message' => 'Data pelanggan baru berhasil ditambahkan.',
        'customer_id' => $newId
    ], 201);
}

// --- 3. POST: Edit Pelanggan (action=update) ---
if ($action === 'update') {
    if ($method !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    $customerId = (int)($input['id'] ?? ($input['customer_id'] ?? 0));
    if ($customerId <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID pelanggan tidak valid.'], 400);
    }

    // Cek user exists dan role = customer
    $cStmt = $db->prepare("SELECT id, role, password FROM users WHERE id = ? LIMIT 1");
    $cStmt->execute([$customerId]);
    $existing = $cStmt->fetch();
    if (!$existing || $existing['role'] !== 'customer') {
        jsonResponse(['success' => false, 'message' => 'Pelanggan tidak ditemukan.'], 404);
    }

    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $password = trim($input['password'] ?? '');
    $avatarUrl = array_key_exists('avatar_url', $input) ? (trim($input['avatar_url'] ?? '') ?: null) : null;

    if (empty($name) || empty($email) || empty($phone)) {
        jsonResponse(['success' => false, 'message' => 'Nama, email, dan nomor telepon tidak boleh kosong.'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Format email tidak valid.'], 400);
    }

    // Cek duplikasi email
    $chkEmail = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
    $chkEmail->execute([$email, $customerId]);
    if ($chkEmail->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email ini sudah digunakan pelanggan lain.'], 400);
    }

    // Cek duplikasi phone
    $chkPhone = $db->prepare("SELECT id FROM users WHERE phone = ? AND id != ? LIMIT 1");
    $chkPhone->execute([$phone, $customerId]);
    if ($chkPhone->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Nomor telepon ini sudah digunakan pelanggan lain.'], 400);
    }

    if (!empty($password)) {
        if (strlen($password) < 6) {
            jsonResponse(['success' => false, 'message' => 'Kata sandi baru minimal 6 karakter.'], 400);
        }
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        if (array_key_exists('avatar_url', $input)) {
            $uStmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ?, avatar_url = ? WHERE id = ?");
            $uStmt->execute([$name, $email, $phone, $hashed, $avatarUrl, $customerId]);
        } else {
            $uStmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
            $uStmt->execute([$name, $email, $phone, $hashed, $customerId]);
        }
    } else {
        if (array_key_exists('avatar_url', $input)) {
            $uStmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, avatar_url = ? WHERE id = ?");
            $uStmt->execute([$name, $email, $phone, $avatarUrl, $customerId]);
        } else {
            $uStmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            $uStmt->execute([$name, $email, $phone, $customerId]);
        }
    }

    jsonResponse([
        'success' => true,
        'message' => 'Data pelanggan berhasil diperbarui.'
    ]);
}

// --- 4. POST: Hapus Pelanggan (action=delete) ---
if ($action === 'delete') {
    if ($method !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    $customerId = (int)($input['id'] ?? ($input['customer_id'] ?? 0));
    if ($customerId <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID pelanggan tidak valid.'], 400);
    }

    // Cek pelanggan
    $cStmt = $db->prepare("SELECT id, name, role FROM users WHERE id = ? LIMIT 1");
    $cStmt->execute([$customerId]);
    $cust = $cStmt->fetch();
    if (!$cust || $cust['role'] !== 'customer') {
        jsonResponse(['success' => false, 'message' => 'Data pelanggan tidak ditemukan.'], 404);
    }

    // Cek pesanan aktif
    $actStmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ? AND status IN ('pending', 'confirmed', 'on_process')");
    $actStmt->execute([$customerId]);
    $activeCount = (int)$actStmt->fetchColumn();

    if ($activeCount > 0) {
        jsonResponse([
            'success' => false,
            'message' => 'Pelanggan "' . htmlspecialchars($cust['name']) . '" tidak dapat dihapus karena masih memiliki ' . $activeCount . ' pesanan aktif (Menunggu / Dikonfirmasi / Proses).'
        ], 400);
    }

    // Hapus pelanggan (foreign key ON DELETE CASCADE akan membersihkan histori jika ada)
    $delStmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $delStmt->execute([$customerId]);

    jsonResponse([
        'success' => true,
        'message' => 'Pelanggan "' . htmlspecialchars($cust['name']) . '" berhasil dihapus beserta histori pesanannya.'
    ]);
}

jsonResponse(['success' => false, 'message' => 'Action tidak dikenali.'], 404);
