<?php
// ==========================================================
// API Layanan (Services CRUD & Filter)
// ==========================================================

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
startSession();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$input = array_merge($_REQUEST, $jsonInput);

$db = Database::getConnection();

// --- 1. GET: Ambil Data Layanan ---
if ($method === 'GET') {
    // Ambil single service jika ada param id
    if (!empty($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch();

        if (!$service) {
            jsonResponse(['success' => false, 'message' => 'Layanan tidak ditemukan.'], 404);
        }

        $service['price_formatted'] = formatRupiah($service['price']);
        jsonResponse(['success' => true, 'data' => $service]);
    }

    // Filter berdasarkan tipe layanan
    $type = $_GET['type'] ?? 'all';
    $activeOnly = isset($_GET['active_only']) ? (int)$_GET['active_only'] : 1;

    $query = "SELECT * FROM services WHERE 1=1";
    $params = [];

    if ($activeOnly === 1) {
        $query .= " AND is_active = 1";
    }

    if ($type === 'home_service') {
        $query .= " AND type IN ('home_service', 'both')";
    } elseif ($type === 'clinic' || $type === 'clinic_only') {
        $query .= " AND type IN ('clinic_only', 'both')";
    }

    $query .= " ORDER BY price ASC, id ASC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $services = $stmt->fetchAll();

    foreach ($services as &$item) {
        $item['price_formatted'] = formatRupiah($item['price']);
        $item['duration_text'] = $item['duration_minutes'] . ' Menit';
    }

    jsonResponse([
        'success' => true,
        'count'   => count($services),
        'data'    => $services
    ]);
}

// --- Operasi Modifikasi Data (Wajib Admin) ---
$user = getCurrentUser();
if (!$user || $user['role'] !== 'admin') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Fitur ini memerlukan otentikasi Administrator.'], 403);
}

// --- 2. POST: Tambah Layanan Baru ---
if ($method === 'POST' && ($action === 'create' || empty($action))) {
    $name = trim($input['name'] ?? '');
    $duration = (int)($input['duration_minutes'] ?? 60);
    $price = (float)($input['price'] ?? 0);
    $description = trim($input['description'] ?? '');
    $type = $input['type'] ?? 'both';
    $imageUrl = trim($input['image_url'] ?? '');
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

    if (empty($name) || $price <= 0 || $duration <= 0) {
        jsonResponse(['success' => false, 'message' => 'Nama, durasi (> 0), dan harga (> 0) wajib diisi.'], 400);
    }

    if (!in_array($type, ['home_service', 'clinic_only', 'both'])) {
        $type = 'both';
    }

    if (empty($imageUrl)) {
        $imageUrl = 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800&auto=format&fit=crop&q=80';
    }

    $stmt = $db->prepare("INSERT INTO services (name, duration_minutes, price, description, type, image_url, is_active, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $duration, $price, $description, $type, $imageUrl, $isActive]);
    $newId = (int)$db->lastInsertId();

    jsonResponse([
        'success' => true,
        'message' => 'Layanan baru berhasil ditambahkan.',
        'data'    => ['id' => $newId, 'name' => $name]
    ], 201);
}

// --- 3. POST / PUT: Update Layanan ---
if (($method === 'POST' && $action === 'update') || $method === 'PUT') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID layanan tidak valid.'], 400);
    }

    // Pastikan layanan ada
    $check = $db->prepare("SELECT id FROM services WHERE id = ?");
    $check->execute([$id]);
    if (!$check->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Layanan tidak ditemukan.'], 404);
    }

    $name = trim($input['name'] ?? '');
    $duration = (int)($input['duration_minutes'] ?? 60);
    $price = (float)($input['price'] ?? 0);
    $description = trim($input['description'] ?? '');
    $type = $input['type'] ?? 'both';
    $imageUrl = trim($input['image_url'] ?? '');
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

    if (empty($name) || $price <= 0 || $duration <= 0) {
        jsonResponse(['success' => false, 'message' => 'Nama, durasi (> 0), dan harga (> 0) wajib diisi.'], 400);
    }

    if (!in_array($type, ['home_service', 'clinic_only', 'both'])) {
        $type = 'both';
    }

    $stmt = $db->prepare("UPDATE services SET name = ?, duration_minutes = ?, price = ?, description = ?, type = ?, image_url = ?, is_active = ? WHERE id = ?");
    $stmt->execute([$name, $duration, $price, $description, $type, $imageUrl, $isActive, $id]);

    jsonResponse([
        'success' => true,
        'message' => 'Data layanan berhasil diperbarui.'
    ]);
}

// --- 4. POST / DELETE: Hapus / Toggle Status Layanan ---
if (($method === 'POST' && $action === 'delete') || $method === 'DELETE') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID layanan tidak valid.'], 400);
    }

    // Cek apakah layanan sedang dipakai di tabel bookings
    $checkBookings = $db->prepare("SELECT COUNT(*) FROM bookings WHERE service_id = ?");
    $checkBookings->execute([$id]);
    $isUsed = (int)$checkBookings->fetchColumn() > 0;

    if ($isUsed) {
        // Jika sudah ada riwayat booking, lakukan soft delete (is_active = 0) demi integritas data
        $stmt = $db->prepare("UPDATE services SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse([
            'success' => true,
            'message' => 'Layanan memiliki riwayat pemesanan, status layanan berhasil diubah menjadi Nonaktif.'
        ]);
    } else {
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse([
            'success' => true,
            'message' => 'Layanan berhasil dihapus secara permanen.'
        ]);
    }
}

jsonResponse(['success' => false, 'message' => 'Aksi tidak valid atau metode tidak didukung.'], 400);
