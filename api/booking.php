<?php
// ==========================================================
// API Booking: Reservasi, Cek Slot Jadwal, Status & Assignment
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

// --- 1. ACTION: check_slots (Cek Ketersediaan Slot Jam) ---
if ($action === 'check_slots') {
    $date = trim($input['date'] ?? date('Y-m-d'));
    $serviceId = (int)($input['service_id'] ?? 1);
    $therapistId = !empty($input['therapist_id']) ? (int)$input['therapist_id'] : null;
    $genderPref = $input['gender_preference'] ?? 'any';

    // Ambil durasi layanan
    $srvStmt = $db->prepare("SELECT duration_minutes FROM services WHERE id = ?");
    $srvStmt->execute([$serviceId]);
    $duration = (int)$srvStmt->fetchColumn() ?: 60;

    // Slot jam standar klinik / home service (09:00 - 20:00)
    $availableSlots = [
        '09:00',
        '10:30',
        '12:00',
        '13:30',
        '15:00',
        '16:30',
        '18:00',
        '19:30',
        '21:00'
    ];

    // Ambil total terapis yang aktif (dan sesuai filter gender jika ada)
    $tQuery = "SELECT id, gender FROM therapists WHERE is_available = 1";
    $tParams = [];
    if (in_array($genderPref, ['male', 'female'])) {
        $tQuery .= " AND gender = ?";
        $tParams[] = $genderPref;
    }
    $tStmt = $db->prepare($tQuery);
    $tStmt->execute($tParams);
    $activeTherapists = $tStmt->fetchAll();
    $totalActiveTherapists = count($activeTherapists);
    $activeTherapistIds = array_column($activeTherapists, 'id');

    $results = [];
    $today = date('Y-m-d');
    $currentHourMin = date('H:i');

    foreach ($availableSlots as $slotTime) {
        $slotStartStr = "$date $slotTime:00";
        $slotStartTs = strtotime($slotStartStr);
        $slotEndTs = $slotStartTs + ($duration * 60);
        $slotEndStr = date('Y-m-d H:i:s', $slotEndTs);

        // Jika tanggal sudah lewat atau tanggal hari ini dan jam sudah lewat, tandai tidak tersedia
        $isPast = ($date < $today) || ($date === $today && $slotTime <= $currentHourMin);

        if ($isPast) {
            $results[] = [
                'time'      => $slotTime,
                'available' => false,
                'reason'    => 'Waktu sudah lewat'
            ];
            continue;
        }

        // Cek reservasi aktif pada rentang waktu ini
        // Bentrok jika: (b.schedule_datetime < :end AND DATE_ADD(b.schedule_datetime, INTERVAL b.duration MINUTE) > :start)
        if ($therapistId) {
            $bQuery = "SELECT COUNT(*) FROM bookings 
                       WHERE therapist_id = ? 
                         AND status NOT IN ('cancelled')
                         AND (schedule_datetime < ? AND DATE_ADD(schedule_datetime, INTERVAL duration MINUTE) > ?)";
            $bStmt = $db->prepare($bQuery);
            $bStmt->execute([$therapistId, $slotEndStr, $slotStartStr]);
            $isBusy = (int)$bStmt->fetchColumn() > 0;

            $results[] = [
                'time'      => $slotTime,
                'available' => !$isBusy,
                'reason'    => $isBusy ? 'Terapis memiliki jadwal lain' : 'Tersedia'
            ];
        } else {
            // Jika tidak pilih terapis spesifik, cek apakah masih ada terapis aktif yang bebas
            if ($totalActiveTherapists === 0) {
                $results[] = [
                    'time'      => $slotTime,
                    'available' => false,
                    'reason'    => 'Tidak ada terapis aktif yang sesuai'
                ];
                continue;
            }

            // Ambil ID terapis yang sibuk pada slot ini
            $inClause = implode(',', array_fill(0, count($activeTherapistIds), '?'));
            $bQuery = "SELECT DISTINCT therapist_id FROM bookings 
                       WHERE therapist_id IN ($inClause)
                         AND status NOT IN ('cancelled')
                         AND (schedule_datetime < ? AND DATE_ADD(schedule_datetime, INTERVAL duration MINUTE) > ?)";

            $bParams = array_merge($activeTherapistIds, [$slotEndStr, $slotStartStr]);
            $bStmt = $db->prepare($bQuery);
            $bStmt->execute($bParams);
            $busyTherapistIds = $bStmt->fetchAll(PDO::FETCH_COLUMN);

            $freeTherapistsCount = count($activeTherapistIds) - count($busyTherapistIds);

            $results[] = [
                'time'            => $slotTime,
                'available'       => ($freeTherapistsCount > 0),
                'free_therapists' => $freeTherapistsCount,
                'reason'          => ($freeTherapistsCount > 0) ? "$freeTherapistsCount Terapis Tersedia" : 'Slot penuh'
            ];
        }
    }

    jsonResponse([
        'success'   => true,
        'date'      => $date,
        'duration'  => $duration,
        'slots'     => $results
    ]);
}

// --- 2. ACTION: create (Buat Reservasi Baru) ---
if ($action === 'create' || ($method === 'POST' && empty($action))) {
    $bookingType = $input['booking_type'] ?? 'clinic';
    if (!in_array($bookingType, ['home_service', 'clinic'])) {
        $bookingType = 'clinic';
    }

    $address = trim($input['address'] ?? '');
    if ($bookingType === 'home_service' && empty($address)) {
        jsonResponse(['success' => false, 'message' => 'Alamat lengkap wajib diisi untuk layanan Home Service.'], 400);
    }

    $serviceId = (int)($input['service_id'] ?? 0);
    if ($serviceId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Silakan pilih layanan yang diinginkan.'], 400);
    }

    // Ambil detail layanan
    $srvStmt = $db->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
    $srvStmt->execute([$serviceId]);
    $service = $srvStmt->fetch();

    if (!$service) {
        jsonResponse(['success' => false, 'message' => 'Layanan yang dipilih tidak aktif atau tidak ditemukan.'], 404);
    }

    // Validasi kompatibilitas tipe layanan
    if ($service['type'] === 'clinic_only' && $bookingType === 'home_service') {
        jsonResponse(['success' => false, 'message' => 'Layanan ini hanya dapat dinikmati di Klinik/Studio.'], 400);
    }
    if ($service['type'] === 'home_service' && $bookingType === 'clinic') {
        jsonResponse(['success' => false, 'message' => 'Layanan ini khusus untuk Home Service.'], 400);
    }

    // Jadwal reservasi
    $scheduleDate = trim($input['schedule_date'] ?? '');
    $scheduleTime = trim($input['schedule_time'] ?? '');
    if (empty($scheduleDate) || empty($scheduleTime)) {
        jsonResponse(['success' => false, 'message' => 'Tanggal dan jam jadwal reservasi wajib dipilih.'], 400);
    }

    $scheduleDatetime = date('Y-m-d H:i:s', strtotime("$scheduleDate $scheduleTime:00"));
    if (strtotime($scheduleDatetime) < time() - 300) {
        jsonResponse(['success' => false, 'message' => 'Waktu reservasi tidak boleh di masa lampau.'], 400);
    }

    $therapistId = !empty($input['therapist_id']) ? (int)$input['therapist_id'] : null;
    $notes = trim($input['notes'] ?? '');
    $duration = (int)$service['duration_minutes'];
    $totalPrice = (float)$service['price'];

    // Menentukan Customer ID
    $currentUser = getCurrentUser();
    $customerId = null;

    if ($currentUser) {
        $customerId = (int)$currentUser['id'];
    } else {
        // Reservasi oleh tamu (guest)
        $custName = trim($input['customer_name'] ?? '');
        $custPhone = trim($input['customer_phone'] ?? '');
        $custEmail = trim($input['customer_email'] ?? '');

        if (empty($custName) || empty($custPhone)) {
            jsonResponse(['success' => false, 'message' => 'Nama lengkap dan nomor WhatsApp wajib diisi.'], 400);
        }

        if (empty($custEmail)) {
            $custEmail = 'guest_' . preg_replace('/[^0-9]/', '', $custPhone) . '@sentosaspa.com';
        }

        // Cek apakah email atau nomor HP sudah terdaftar di users
        $uStmt = $db->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
        $uStmt->execute([$custEmail, $custPhone]);
        $existingUser = $uStmt->fetch();

        if ($existingUser) {
            $customerId = (int)$existingUser['id'];
        } else {
            // Buat akun customer baru otomatis
            $randomPass = substr(bin2hex(random_bytes(4)), 0, 8);
            $hashedPass = password_hash($randomPass, PASSWORD_BCRYPT);

            $createU = $db->prepare("INSERT INTO users (name, email, phone, password, role, created_at) VALUES (?, ?, ?, ?, 'customer', NOW())");
            $createU->execute([$custName, $custEmail, $custPhone, $hashedPass]);
            $customerId = (int)$db->lastInsertId();
        }
    }

    // Ambil data customer untuk ringkasan
    $cStmt = $db->prepare("SELECT name, phone, email FROM users WHERE id = ?");
    $cStmt->execute([$customerId]);
    $customer = $cStmt->fetch();

    // Buat kode booking unik
    $bookingCode = generateBookingCode();

    // Insert ke tabel bookings
    $insStmt = $db->prepare("INSERT INTO bookings 
        (booking_code, customer_id, therapist_id, service_id, booking_type, address, schedule_datetime, duration, total_price, status, payment_status, notes, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid', ?, NOW())");

    $insStmt->execute([
        $bookingCode,
        $customerId,
        $therapistId,
        $serviceId,
        $bookingType,
        $address,
        $scheduleDatetime,
        $duration,
        $totalPrice,
        $notes
    ]);

    $newBookingId = (int)$db->lastInsertId();

    // Format teks untuk integrasi WhatsApp Admin
    $tipeText = ($bookingType === 'home_service') ? "Home Service (Panggilan ke Tempat)" : "Datang ke Klinik/Studio";
    $lokasiInfo = ($bookingType === 'home_service') ? "\n📍 *Alamat:* " . $address : "";
    $jadwalFormatted = formatDateTimeId($scheduleDatetime);
    $hargaFormatted = formatRupiah($totalPrice);

    $waMessage = "🌿 *KONFIRMASI BOOKING SENTOSA SPA* 🌿\n\n"
        . "Halo Admin, saya ingin konfirmasi pesanan reservasi:\n\n"
        . "🔖 *Kode Booking:* {$bookingCode}\n"
        . "👤 *Nama Pelanggan:* {$customer['name']}\n"
        . "📱 *WhatsApp:* {$customer['phone']}\n"
        . "💆 *Layanan:* {$service['name']} ({$duration} Menit)\n"
        . "🏷 *Tipe Reservasi:* {$tipeText}{$lokasiInfo}\n"
        . "🗓 *Jadwal:* {$jadwalFormatted}\n"
        . "💰 *Total Biaya:* {$hargaFormatted}\n";

    if (!empty($notes)) {
        $waMessage .= "📝 *Catatan Tambahan:* {$notes}\n";
    }

    $waMessage .= "\nMohon konfirmasi ketersediaan jadwal terapis. Terima kasih!";

    $waUrl = "https://wa.me/" . APP_PHONE . "?text=" . urlencode($waMessage);

    jsonResponse([
        'success'      => true,
        'message'      => 'Reservasi berhasil dibuat! Silakan klik tombol WhatsApp untuk konfirmasi instan.',
        'data'         => [
            'id'                => $newBookingId,
            'booking_code'      => $bookingCode,
            'customer_name'     => $customer['name'],
            'service_name'      => $service['name'],
            'booking_type'      => $bookingType,
            'schedule_datetime' => $scheduleDatetime,
            'schedule_formatted' => $jadwalFormatted,
            'total_price'       => $totalPrice,
            'price_formatted'   => $hargaFormatted,
            'whatsapp_url'      => $waUrl
        ]
    ], 201);
}

// --- 3. ACTION: list / get (Daftar & Detail Booking) ---
if ($action === 'list' || ($method === 'GET' && empty($action))) {
    // Cek jika pencarian berdasarkan kode booking publik
    if (!empty($_GET['code'])) {
        $code = trim($_GET['code']);
        $stmt = $db->prepare("SELECT b.*, s.name as service_name, s.duration_minutes, s.image_url,
                                     u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                                     tu.name as therapist_name, t.specialization as therapist_specialization, t.gender as therapist_gender
                              FROM bookings b
                              JOIN services s ON b.service_id = s.id
                              JOIN users u ON b.customer_id = u.id
                              LEFT JOIN therapists t ON b.therapist_id = t.id
                              LEFT JOIN users tu ON t.user_id = tu.id
                              WHERE b.booking_code = ?");
        $stmt->execute([$code]);
        $booking = $stmt->fetch();

        if (!$booking) {
            jsonResponse(['success' => false, 'message' => 'Kode booking tidak ditemukan.'], 404);
        }

        $booking['schedule_formatted'] = formatDateTimeId($booking['schedule_datetime']);
        $booking['price_formatted'] = formatRupiah($booking['total_price']);
        jsonResponse(['success' => true, 'data' => $booking]);
    }

    // Untuk daftar booking, periksa otentikasi
    $user = getCurrentUser();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Silakan login.'], 401);
    }

    $status = $_GET['status'] ?? '';
    $paymentStatus = $_GET['payment_status'] ?? '';
    $bookingType = $_GET['booking_type'] ?? '';
    $date = $_GET['date'] ?? '';
    $search = trim($_GET['search'] ?? '');

    $query = "SELECT b.*, s.name as service_name, s.duration_minutes, s.image_url,
                     u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                     tu.name as therapist_name, t.specialization as therapist_specialization, t.gender as therapist_gender
              FROM bookings b
              JOIN services s ON b.service_id = s.id
              JOIN users u ON b.customer_id = u.id
              LEFT JOIN therapists t ON b.therapist_id = t.id
              LEFT JOIN users tu ON t.user_id = tu.id
              WHERE 1=1";
    $params = [];

    // Jika customer biasa, hanya boleh lihat pesanannya sendiri
    if ($user['role'] === 'customer') {
        $query .= " AND b.customer_id = ?";
        $params[] = $user['id'];
    } elseif ($user['role'] === 'therapist') {
        // Terapis melihat booking yang ditugaskan ke dirinya
        $query .= " AND b.therapist_id = (SELECT id FROM therapists WHERE user_id = ?)";
        $params[] = $user['id'];
    }

    // Filter tambahan (Admin)
    if (!empty($status)) {
        $query .= " AND b.status = ?";
        $params[] = $status;
    }
    if (!empty($paymentStatus)) {
        $query .= " AND b.payment_status = ?";
        $params[] = $paymentStatus;
    }
    if (!empty($bookingType)) {
        $query .= " AND b.booking_type = ?";
        $params[] = $bookingType;
    }
    if (!empty($date)) {
        $query .= " AND DATE(b.schedule_datetime) = ?";
        $params[] = $date;
    }
    if (!empty($search)) {
        $query .= " AND (b.booking_code LIKE ? OR u.name LIKE ? OR u.phone LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $query .= " ORDER BY b.schedule_datetime DESC, b.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    foreach ($bookings as &$item) {
        $item['schedule_formatted'] = formatDateTimeId($item['schedule_datetime']);
        $item['price_formatted'] = formatRupiah($item['total_price']);
    }

    jsonResponse([
        'success' => true,
        'count'   => count($bookings),
        'data'    => $bookings
    ]);
}

// --- 4. ACTION: update_status (Perbarui Status Booking & Pembayaran) ---
if ($action === 'update_status') {
    $user = getCurrentUser();
    if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'therapist')) {
        jsonResponse(['success' => false, 'message' => 'Akses ditolak.'], 403);
    }

    $id = (int)($input['id'] ?? 0);
    $status = $input['status'] ?? null;
    $paymentStatus = $input['payment_status'] ?? null;

    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID booking tidak valid.'], 400);
    }

    // Jika user adalah terapis, pastikan booking ini ditugaskan kepadanya
    if ($user['role'] === 'therapist') {
        $myTStmt = $db->prepare("SELECT id FROM therapists WHERE user_id = ?");
        $myTStmt->execute([$user['id']]);
        $myTherapistId = (int)$myTStmt->fetchColumn();

        $bCheck = $db->prepare("SELECT therapist_id FROM bookings WHERE id = ?");
        $bCheck->execute([$id]);
        $bookingTherapistId = (int)$bCheck->fetchColumn();

        if ($bookingTherapistId !== $myTherapistId) {
            jsonResponse(['success' => false, 'message' => 'Anda hanya berhak mengelola reservasi yang ditugaskan kepada Anda.'], 403);
        }
    }

    $updates = [];
    $params = [];

    if ($status && in_array($status, ['pending', 'confirmed', 'on_process', 'completed', 'cancelled'])) {
        $updates[] = "status = ?";
        $params[] = $status;
    }

    if ($paymentStatus && in_array($paymentStatus, ['unpaid', 'paid'])) {
        $updates[] = "payment_status = ?";
        $params[] = $paymentStatus;
    }

    if (empty($updates)) {
        jsonResponse(['success' => false, 'message' => 'Tidak ada perubahan status yang dikirim.'], 400);
    }

    $params[] = $id;
    $sql = "UPDATE bookings SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    jsonResponse([
        'success' => true,
        'message' => 'Status booking berhasil diperbarui.'
    ]);
}

// --- 5. ACTION: assign_therapist (Tetapkan Terapis oleh Admin) ---
if ($action === 'assign_therapist') {
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Akses ditolak. Fitur ini khusus Administrator.'], 403);
    }

    $bookingId = (int)($input['booking_id'] ?? 0);
    $therapistId = (int)($input['therapist_id'] ?? 0);
    $autoConfirm = !empty($input['auto_confirm']);

    if ($bookingId <= 0 || $therapistId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Booking ID dan Therapist ID wajib disertakan.'], 400);
    }

    // Pastikan terapis valid dan aktif
    $tStmt = $db->prepare("SELECT id FROM therapists WHERE id = ?");
    $tStmt->execute([$therapistId]);
    if (!$tStmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Terapis tidak ditemukan.'], 404);
    }

    $updateSql = "UPDATE bookings SET therapist_id = ?";
    $params = [$therapistId];

    if ($autoConfirm) {
        $updateSql .= ", status = 'confirmed'";
    }

    $updateSql .= " WHERE id = ?";
    $params[] = $bookingId;

    $stmt = $db->prepare($updateSql);
    $stmt->execute($params);

    jsonResponse([
        'success' => true,
        'message' => 'Terapis berhasil ditugaskan untuk reservasi ini.'
    ]);
}

// --- 6. ACTION: cancel (Batalkan Reservasi oleh Pelanggan) ---
if ($action === 'cancel') {
    $user = getCurrentUser();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
    }

    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID reservasi tidak valid.'], 400);
    }

    $bStmt = $db->prepare("SELECT * FROM bookings WHERE id = ?");
    $bStmt->execute([$id]);
    $booking = $bStmt->fetch();

    if (!$booking) {
        jsonResponse(['success' => false, 'message' => 'Data reservasi tidak ditemukan.'], 404);
    }

    // Hanya pemilik booking atau admin yang berhak membatalkan
    if ($user['role'] !== 'admin' && (int)$booking['customer_id'] !== (int)$user['id']) {
        jsonResponse(['success' => false, 'message' => 'Anda tidak memiliki hak untuk membatalkan reservasi ini.'], 403);
    }

    // Hanya reservasi berstatus pending atau confirmed yang bisa dibatalkan secara mandiri
    if (!in_array($booking['status'], ['pending', 'confirmed'])) {
        jsonResponse(['success' => false, 'message' => 'Hanya reservasi berstatus Menunggu Konfirmasi atau Terkonfirmasi yang dapat dibatalkan.'], 400);
    }

    $updateStmt = $db->prepare("UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
    $updateStmt->execute([$id]);

    jsonResponse([
        'success' => true,
        'message' => 'Reservasi berhasil dibatalkan.'
    ]);
}

// --- 6.1. ACTION: delete (Hapus Data Reservasi oleh Admin) ---
if ($action === 'delete') {
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Akses ditolak. Fitur ini khusus Administrator.'], 403);
    }

    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID reservasi tidak valid.'], 400);
    }

    $bStmt = $db->prepare("SELECT id, booking_code FROM bookings WHERE id = ?");
    $bStmt->execute([$id]);
    $booking = $bStmt->fetch();

    if (!$booking) {
        jsonResponse(['success' => false, 'message' => 'Data reservasi tidak ditemukan.'], 404);
    }

    $deleteStmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
    $deleteStmt->execute([$id]);

    jsonResponse([
        'success' => true,
        'message' => 'Reservasi ' . $booking['booking_code'] . ' berhasil dihapus.'
    ]);
}

// --- 7. ACTION: submit_review (Beri Rating & Ulasan oleh Pelanggan) ---
if ($action === 'submit_review') {
    $user = getCurrentUser();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Silakan login terlebih dahulu untuk memberikan ulasan.'], 401);
    }

    $bookingId = (int)($input['booking_id'] ?? ($input['id'] ?? 0));
    $rating = (int)($input['rating'] ?? 0);
    $comment = trim($input['comment'] ?? '');

    if ($bookingId <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID reservasi tidak valid.'], 400);
    }

    if ($rating < 1 || $rating > 5) {
        jsonResponse(['success' => false, 'message' => 'Rating harus bernilai antara 1 sampai 5 bintang.'], 400);
    }

    // Ambil detail reservasi
    $bStmt = $db->prepare("SELECT * FROM bookings WHERE id = ?");
    $bStmt->execute([$bookingId]);
    $booking = $bStmt->fetch();

    if (!$booking) {
        jsonResponse(['success' => false, 'message' => 'Data reservasi tidak ditemukan.'], 404);
    }

    // Hak akses: hanya customer yang bersangkutan atau admin
    if ($user['role'] !== 'admin' && (int)$booking['customer_id'] !== (int)$user['id']) {
        jsonResponse(['success' => false, 'message' => 'Anda tidak memiliki hak untuk mengulas pesanan ini.'], 403);
    }

    // Wajib berstatus completed
    if ($booking['status'] !== 'completed') {
        jsonResponse(['success' => false, 'message' => 'Ulasan hanya dapat diberikan setelah sesi pijat selesai (Status: Selesai).'], 400);
    }

    // Wajib memiliki terapis
    $therapistId = (int)$booking['therapist_id'];
    if ($therapistId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Pesanan ini tidak memiliki terapis terdaftar untuk diulas.'], 400);
    }

    // Cek apakah sudah pernah diulas
    $checkReview = $db->prepare("SELECT id FROM reviews WHERE booking_id = ?");
    $checkReview->execute([$bookingId]);
    if ($checkReview->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Anda sudah memberikan ulasan untuk pesanan ini.'], 400);
    }

    $db->beginTransaction();
    try {
        // Simpan review baru
        $rStmt = $db->prepare("INSERT INTO reviews (booking_id, customer_id, therapist_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $rStmt->execute([$bookingId, (int)$booking['customer_id'], $therapistId, $rating, !empty($comment) ? $comment : null]);

        // Kalkulasi ulang rating terapis secara otomatis (ROUND(AVG(rating), 2))
        $calcStmt = $db->prepare("SELECT ROUND(AVG(rating), 2) as avg_rating, COUNT(id) as total_reviews FROM reviews WHERE therapist_id = ?");
        $calcStmt->execute([$therapistId]);
        $calc = $calcStmt->fetch();
        $newAvgRating = (float)($calc['avg_rating'] ?? 5.00);
        $totalReviews = (int)($calc['total_reviews'] ?? 0);

        // Update rating terapis
        $tUpdate = $db->prepare("UPDATE therapists SET rating = ? WHERE id = ?");
        $tUpdate->execute([$newAvgRating, $therapistId]);

        $db->commit();

        jsonResponse([
            'success'       => true,
            'message'       => 'Terima kasih! Ulasan dan rating Anda berhasil disimpan.',
            'therapist_id'  => $therapistId,
            'rating'        => $newAvgRating,
            'rating_fmt'    => number_format($newAvgRating, 1),
            'total_reviews' => $totalReviews
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Gagal menyimpan ulasan: ' . $e->getMessage()], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Aksi tidak didukung.'], 400);

