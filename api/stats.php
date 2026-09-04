<?php
// ==========================================================
// API Stats: Ringkasan Finansial & Analitik Reservasi (Admin)
// ==========================================================

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
startSession();

$user = getCurrentUser();
if (!$user || $user['role'] !== 'admin') {
    jsonResponse(['success' => false, 'message' => 'Akses ditolak. Khusus Administrator.'], 403);
}

$db = Database::getConnection();

// 1. Total Booking Hari Ini
$todayStmt = $db->query("SELECT COUNT(*) FROM bookings WHERE DATE(schedule_datetime) = CURDATE()");
$totalToday = (int)$todayStmt->fetchColumn();

// 2. Sesi Aktif Saat Ini (On Process)
$activeStmt = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'on_process'");
$activeSessions = (int)$activeStmt->fetchColumn();

// 3. Menunggu Konfirmasi (Pending)
$pendingStmt = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
$pendingBookings = (int)$pendingStmt->fetchColumn();

// 4. Finansial: Total Pendapatan
// Total Pendapatan Masuk (Semua yang Paid)
$paidGrossStmt = $db->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE payment_status = 'paid'");
$grossRevenue = (float)$paidGrossStmt->fetchColumn();

// Pendapatan Bersih (Completed & Paid)
$netStmt = $db->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status = 'completed' AND payment_status = 'paid'");
$netRevenue = (float)$netStmt->fetchColumn();

// Pendapatan Hari Ini
$todayRevStmt = $db->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE payment_status = 'paid' AND (DATE(created_at) = CURDATE() OR DATE(schedule_datetime) = CURDATE())");
$todayRevenue = (float)$todayRevStmt->fetchColumn();

// 5. Distribusi Status Reservasi
$statusStmt = $db->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
$statusCounts = [
    'pending'    => 0,
    'confirmed'  => 0,
    'on_process' => 0,
    'completed'  => 0,
    'cancelled'  => 0
];
while ($row = $statusStmt->fetch()) {
    $statusCounts[$row['status']] = (int)$row['count'];
}

// 6. Distribusi Tipe Layanan (Home Service vs Clinic)
$typeStmt = $db->query("SELECT booking_type, COUNT(*) as count FROM bookings GROUP BY booking_type");
$typeCounts = [
    'home_service' => 0,
    'clinic'       => 0
];
while ($row = $typeStmt->fetch()) {
    $typeCounts[$row['booking_type']] = (int)$row['count'];
}

// 7. Booking Terbaru (5 data terakhir)
$recentStmt = $db->query("SELECT b.id, b.booking_code, b.booking_type, b.schedule_datetime, b.total_price, b.status, b.payment_status,
                                 s.name as service_name, u.name as customer_name, tu.name as therapist_name
                          FROM bookings b
                          JOIN services s ON b.service_id = s.id
                          JOIN users u ON b.customer_id = u.id
                          LEFT JOIN therapists t ON b.therapist_id = t.id
                          LEFT JOIN users tu ON t.user_id = tu.id
                          ORDER BY b.id DESC LIMIT 5");
$recentBookings = $recentStmt->fetchAll();
foreach ($recentBookings as &$rb) {
    $rb['schedule_formatted'] = formatDateTimeId($rb['schedule_datetime']);
    $rb['price_formatted'] = formatRupiah($rb['total_price']);
}

// 8. Terapis Tersedia vs Total Terapis
$tStatStmt = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available
    FROM therapists");
$therapistStats = $tStatStmt->fetch();

jsonResponse([
    'success' => true,
    'metrics' => [
        'total_today'          => $totalToday,
        'active_sessions'      => $activeSessions,
        'pending_bookings'     => $pendingBookings,
        'gross_revenue'        => $grossRevenue,
        'gross_revenue_format' => formatRupiah($grossRevenue),
        'net_revenue'          => $netRevenue,
        'net_revenue_format'   => formatRupiah($netRevenue),
        'today_revenue'        => $todayRevenue,
        'today_revenue_format' => formatRupiah($todayRevenue),
        'therapists_total'     => (int)($therapistStats['total'] ?? 0),
        'therapists_available' => (int)($therapistStats['available'] ?? 0)
    ],
    'status_breakdown' => $statusCounts,
    'type_breakdown'   => $typeCounts,
    'recent_bookings'  => $recentBookings
]);
