<?php
// ==========================================================
// Portal Khusus Terapis: Jadwal Tugas, Status & Ulasan
// ==========================================================
require_once __DIR__ . '/config/database.php';

$currentUser = requireLogin();

// Validasi Role: Hanya Terapis (atau Admin yang sedang meninjau)
if ($currentUser['role'] !== 'therapist' && $currentUser['role'] !== 'admin') {
    header('Location: ' . getBaseUrl() . '/index.php');
    exit;
}

$db = Database::getConnection();
$userId = (int)$currentUser['id'];

// Ambil profil data terapis
if ($currentUser['role'] === 'admin') {
    // Jika ada parameter therapist_id di URL, simpan ke session admin
    if (isset($_GET['therapist_id']) && (int)$_GET['therapist_id'] > 0) {
        $_SESSION['admin_preview_therapist_id'] = (int)$_GET['therapist_id'];
    }

    $tTargetId = (int)($_GET['therapist_id'] ?? ($_SESSION['admin_preview_therapist_id'] ?? 0));
    if ($tTargetId > 0) {
        $tStmt = $db->prepare("SELECT t.*, u.name, u.email, u.phone, COUNT(r.id) as total_reviews
                               FROM therapists t
                               JOIN users u ON t.user_id = u.id
                               LEFT JOIN reviews r ON t.id = r.therapist_id
                               WHERE t.id = ?
                               GROUP BY t.id, t.user_id, t.specialization, t.gender, t.is_available, t.rating, t.created_at, u.name, u.email, u.phone");
        $tStmt->execute([$tTargetId]);
    } else {
        $tStmt = $db->query("SELECT t.*, u.name, u.email, u.phone, COUNT(r.id) as total_reviews
                             FROM therapists t
                             JOIN users u ON t.user_id = u.id
                             LEFT JOIN reviews r ON t.id = r.therapist_id
                             GROUP BY t.id, t.user_id, t.specialization, t.gender, t.is_available, t.rating, t.created_at, u.name, u.email, u.phone
                             ORDER BY t.id ASC LIMIT 1");
    }
} else {
    $tStmt = $db->prepare("SELECT t.*, u.name, u.email, u.phone, COUNT(r.id) as total_reviews
                           FROM therapists t
                           JOIN users u ON t.user_id = u.id
                           LEFT JOIN reviews r ON t.id = r.therapist_id
                           WHERE t.user_id = ?
                           GROUP BY t.id, t.user_id, t.specialization, t.gender, t.is_available, t.rating, t.created_at, u.name, u.email, u.phone");
    $tStmt->execute([$userId]);
}

$therapist = $tStmt->fetch();

if (!$therapist) {
    die("<div style='font-family:sans-serif;padding:40px;text-align:center;'><h3>Profil Terapis Belum Ada</h3><p>Belum ada data terapis di sistem atau akun Anda belum terhubung dengan data terapis.</p><p><a href='" . getBaseUrl() . "/admin/dashboard.php' style='color:#065f46;'>Ke Dashboard Admin</a></p></div>");
}

$therapistId = (int)$therapist['id'];

// Perbarui session preview admin dengan ID terapis yang aktif
if ($currentUser['role'] === 'admin') {
    $_SESSION['admin_preview_therapist_id'] = $therapistId;
}

$paramSuffix = ($currentUser['role'] === 'admin') ? '&therapist_id=' . $therapistId : '';
$pageTitle = 'Portal Terapis - ' . htmlspecialchars($therapist['name']) . ' - ' . APP_NAME;

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/navbar.php';

// Filter Tab
$filterTab = $_GET['tab'] ?? 'active';
$validTabs = ['active', 'completed', 'all', 'reviews'];
if (!in_array($filterTab, $validTabs)) {
    $filterTab = 'active';
}

// Hitung Statistik Tugas Terapis
$statsQuery = "SELECT 
    COUNT(*) as total_tasks,
    SUM(CASE WHEN status IN ('confirmed', 'on_process') THEN 1 ELSE 0 END) as active_tasks,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks
    FROM bookings WHERE therapist_id = ?";
$statsStmt = $db->prepare($statsQuery);
$statsStmt->execute([$therapistId]);
$stats = $statsStmt->fetch();

$totalTasks = (int)($stats['total_tasks'] ?? 0);
$activeTasks = (int)($stats['active_tasks'] ?? 0);
$completedTasks = (int)($stats['completed_tasks'] ?? 0);
$totalReviews = (int)($therapist['total_reviews'] ?? 0);
$therapistRating = number_format((float)$therapist['rating'], 1);

// Query Daftar Penugasan Reservasi
$tasksQuery = "SELECT b.*, 
                      s.name as service_name, s.duration_minutes, s.price as service_price, s.image_url, s.type as service_type,
                      cu.name as customer_name, cu.phone as customer_phone, cu.email as customer_email,
                      r.rating as review_rating, r.comment as review_comment, r.created_at as review_created_at
               FROM bookings b
               JOIN services s ON b.service_id = s.id
               JOIN users cu ON b.customer_id = cu.id
               LEFT JOIN reviews r ON b.id = r.booking_id
               WHERE b.therapist_id = ?";

$taskParams = [$therapistId];

if ($filterTab === 'active') {
    $tasksQuery .= " AND b.status IN ('confirmed', 'on_process')";
    $tasksQuery .= " ORDER BY b.schedule_datetime ASC";
} elseif ($filterTab === 'completed') {
    $tasksQuery .= " AND b.status = 'completed'";
    $tasksQuery .= " ORDER BY b.schedule_datetime DESC";
} else {
    $tasksQuery .= " ORDER BY b.schedule_datetime DESC";
}

$taskStmt = $db->prepare($tasksQuery);
$taskStmt->execute($taskParams);
$tasks = $taskStmt->fetchAll();

// Query Ulasan Pelanggan untuk Terapis ini
$reviewsQuery = "SELECT r.*, cu.name as customer_name, s.name as service_name, b.booking_code
                 FROM reviews r
                 JOIN users cu ON r.customer_id = cu.id
                 JOIN bookings b ON r.booking_id = b.id
                 JOIN services s ON b.service_id = s.id
                 WHERE r.therapist_id = ?
                 ORDER BY r.created_at DESC";
$revStmt = $db->prepare($reviewsQuery);
$revStmt->execute([$therapistId]);
$therapistReviews = $revStmt->fetchAll();

// Daftar semua terapis jika yang mengakses adalah admin
$allTherapistsList = [];
if ($currentUser['role'] === 'admin') {
    $allTherapistsList = $db->query("SELECT t.id, u.name, t.specialization FROM therapists t JOIN users u ON t.user_id = u.id ORDER BY u.name ASC")->fetchAll();
}
?>

<div class="min-h-screen bg-stone-50/80 py-8 sm:py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-xs text-stone-500 mb-3">
            <a href="index.php" class="hover:text-emerald-700 transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-stone-800 font-semibold">Portal Khusus Terapis</span>
        </div>

        <?php if ($currentUser['role'] === 'admin'): ?>
            <!-- Admin Preview Switcher Banner -->
            <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm shadow-sm">
                <div class="flex items-center space-x-3 text-amber-900">
                    <div class="w-9 h-9 rounded-xl bg-amber-200/80 flex items-center justify-center text-amber-800 shrink-0">
                        <i class="fa-solid fa-user-shield text-base"></i>
                    </div>
                    <div>
                        <span class="font-bold">Mode Pratinjau Administrator</span>
                        <p class="text-xs text-amber-700">Melihat tampilan portal terapis untuk <strong><?= htmlspecialchars($therapist['name']) ?></strong>.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 flex-wrap">
                    <label for="adminSelectTherapist" class="text-xs font-semibold text-stone-600">Ganti Terapis:</label>
                    <select id="adminSelectTherapist" onchange="window.location.href='therapist-portal.php?therapist_id=' + this.value + '&tab=<?= $filterTab ?>'" class="text-xs bg-white border border-stone-300 rounded-lg px-2.5 py-1.5 font-medium text-stone-800 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        <?php foreach ($allTherapistsList as $at): ?>
                            <option value="<?= $at['id'] ?>" <?= ($at['id'] == $therapistId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($at['name']) ?> (<?= htmlspecialchars($at['specialization']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="admin/dashboard.php" class="text-xs bg-amber-600 hover:bg-amber-700 text-white font-bold px-3 py-1.5 rounded-lg transition-colors">
                        Dashboard Admin
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Banner Profil Terapis & Kontrol Status Ketersediaan -->
        <div class="bg-gradient-to-r from-stone-900 via-stone-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-stone-950/10 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center space-x-4 sm:space-x-5">
                <div class="relative">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-emerald-800 text-white flex items-center justify-center text-2xl sm:text-3xl font-bold font-serif shadow-inner border border-white/20">
                        <?= strtoupper(substr($therapist['name'], 0, 1)) ?>
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center text-[11px] shadow <?= ($therapist['gender'] === 'female') ? 'bg-pink-500 text-white' : 'bg-blue-500 text-white' ?>" title="<?= ($therapist['gender'] === 'female') ? 'Wanita' : 'Pria' ?>">
                        <i class="fa-solid <?= ($therapist['gender'] === 'female') ? 'fa-venus' : 'fa-mars' ?>"></i>
                    </span>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold font-serif"><?= htmlspecialchars($therapist['name']) ?></h1>
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold tracking-wider uppercase border border-emerald-500/30">
                            Terapis Berlisensi
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-stone-300 mt-1 font-medium">
                        <i class="fa-solid fa-spa text-emerald-400 mr-1.5"></i><?= htmlspecialchars($therapist['specialization']) ?>
                    </p>
                    <p class="text-xs text-stone-400 mt-1 flex items-center space-x-3">
                        <span><i class="fa-brands fa-whatsapp text-emerald-400 mr-1"></i><?= htmlspecialchars($therapist['phone']) ?></span>
                        <span>•</span>
                        <span><i class="fa-regular fa-envelope text-emerald-400 mr-1"></i><?= htmlspecialchars($therapist['email']) ?></span>
                    </p>
                </div>
            </div>

            <!-- Toggle Ketersediaan Mandiri Terapis -->
            <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/15 flex items-center justify-between md:flex-col md:items-end gap-3 shrink-0">
                <div class="text-left md:text-right">
                    <span class="text-[10px] uppercase tracking-wider text-stone-400 font-bold block">Status Kerja Anda</span>
                    <span class="text-sm font-bold text-white flex items-center space-x-1.5 mt-0.5" id="availabilityLabel">
                        <span class="w-2.5 h-2.5 rounded-full <?= ($therapist['is_available'] == 1) ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' ?>" id="statusDot"></span>
                        <span id="statusText"><?= ($therapist['is_available'] == 1) ? 'Tersedia (Siap Tugas)' : 'Sedang Libur / Istirahat' ?></span>
                    </span>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="toggleAvailabilityInput" <?= ($therapist['is_available'] == 1) ? 'checked' : '' ?> onchange="toggleTherapistAvailability(<?= $therapistId ?>)" class="sr-only peer">
                    <div class="w-12 h-6 bg-stone-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>
        </div>

        <!-- Kartu Metrik Kinerja Terapis -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-8">
            <!-- Tugas Aktif -->
            <a href="therapist-portal.php?tab=active<?= $paramSuffix ?>" class="p-4 rounded-2xl border transition-all <?= ($filterTab === 'active') ? 'bg-white border-blue-600 shadow-md ring-2 ring-blue-600/10' : 'bg-white/70 border-stone-200/80 hover:bg-white' ?>">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-blue-700 uppercase tracking-wider">Tugas Aktif</span>
                    <i class="fa-solid fa-clock-rotate-left text-blue-500 text-sm"></i>
                </div>
                <span class="text-2xl font-bold text-blue-900 mt-1 block"><?= $activeTasks ?></span>
                <span class="text-[11px] text-stone-500">Sesi menunggu & berjalan</span>
            </a>

            <!-- Tugas Selesai -->
            <a href="therapist-portal.php?tab=completed<?= $paramSuffix ?>" class="p-4 rounded-2xl border transition-all <?= ($filterTab === 'completed') ? 'bg-white border-emerald-600 shadow-md ring-2 ring-emerald-600/10' : 'bg-white/70 border-stone-200/80 hover:bg-white' ?>">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Tuntas</span>
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                </div>
                <span class="text-2xl font-bold text-emerald-900 mt-1 block"><?= $completedTasks ?></span>
                <span class="text-[11px] text-stone-500">Sesi perawatan selesai</span>
            </a>

            <!-- Total Penugasan -->
            <a href="therapist-portal.php?tab=all<?= $paramSuffix ?>" class="p-4 rounded-2xl border transition-all <?= ($filterTab === 'all') ? 'bg-white border-stone-800 shadow-md ring-2 ring-stone-800/10' : 'bg-white/70 border-stone-200/80 hover:bg-white' ?>">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-stone-600 uppercase tracking-wider">Total Tugas</span>
                    <i class="fa-solid fa-calendar-days text-stone-400 text-sm"></i>
                </div>
                <span class="text-2xl font-bold text-stone-900 mt-1 block"><?= $totalTasks ?></span>
                <span class="text-[11px] text-stone-500">Semua riwayat jadwal</span>
            </a>

            <!-- Rating Pelanggan -->
            <a href="therapist-portal.php?tab=reviews<?= $paramSuffix ?>" class="p-4 rounded-2xl border transition-all <?= ($filterTab === 'reviews') ? 'bg-white border-amber-500 shadow-md ring-2 ring-amber-500/10' : 'bg-white/70 border-stone-200/80 hover:bg-white' ?>">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Rating Pelanggan</span>
                    <i class="fa-solid fa-star text-amber-500 text-sm"></i>
                </div>
                <div class="flex items-center space-x-1.5 mt-1">
                    <span class="text-2xl font-bold text-amber-800"><?= $therapistRating ?></span>
                    <span class="text-xs text-amber-600 font-bold">★</span>
                </div>
                <span class="text-[11px] text-stone-500"><?= $totalReviews ?> ulasan pelanggan</span>
            </a>
        </div>

        <!-- Filter Tab Buttons -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-3 mb-6 border-b border-stone-200/80 scrollbar-none">
            <a href="therapist-portal.php?tab=active<?= $paramSuffix ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= ($filterTab === 'active') ? 'bg-blue-800 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-200/70' ?>">
                <i class="fa-solid fa-list-check mr-1.5"></i> Tugas Aktif & Jadwal (<?= $activeTasks ?>)
            </a>
            <a href="therapist-portal.php?tab=completed<?= $paramSuffix ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= ($filterTab === 'completed') ? 'bg-emerald-800 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-200/70' ?>">
                <i class="fa-solid fa-circle-check mr-1.5"></i> Tugas Selesai (<?= $completedTasks ?>)
            </a>
            <a href="therapist-portal.php?tab=all<?= $paramSuffix ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= ($filterTab === 'all') ? 'bg-stone-800 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-200/70' ?>">
                <i class="fa-solid fa-receipt mr-1.5"></i> Semua Jadwal (<?= $totalTasks ?>)
            </a>
            <a href="therapist-portal.php?tab=reviews<?= $paramSuffix ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= ($filterTab === 'reviews') ? 'bg-amber-600 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-200/70' ?>">
                <i class="fa-solid fa-star mr-1.5 text-amber-300"></i> Ulasan & Testimoni Pelanggan (<?= $totalReviews ?>)
            </a>
        </div>

        <!-- KONTEN TAB 1, 2, 3: DAFTAR PENUGASAN RESERVASI -->
        <?php if ($filterTab !== 'reviews'): ?>
            <?php if (empty($tasks)): ?>
                <div class="bg-white rounded-3xl border border-stone-200/80 p-12 text-center max-w-md mx-auto my-6 shadow-sm">
                    <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center mx-auto text-2xl mb-4">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h3 class="font-serif text-lg font-bold text-stone-900">Tidak Ada Tugas Pada Tab Ini</h3>
                    <p class="text-xs text-stone-500 mt-1 leading-relaxed">
                        <?= ($filterTab === 'active') ? 'Saat ini Anda tidak memiliki jadwal penugasan sesi yang sedang aktif.' : 'Tidak ada riwayat penugasan pada kategori ini.' ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="space-y-5">
                    <?php foreach ($tasks as $t): ?>
                        <?php
                        $isHomeService = ($t['booking_type'] === 'home_service');
                        $statusStyles = [
                            'pending'    => ['label' => 'Menunggu Konfirmasi', 'bg' => 'bg-amber-50 text-amber-800 border-amber-200', 'icon' => 'fa-clock'],
                            'confirmed'  => ['label' => 'Terkonfirmasi (Siap Sesi)', 'bg' => 'bg-blue-50 text-blue-800 border-blue-200', 'icon' => 'fa-calendar-check'],
                            'on_process' => ['label' => 'Sesi Sedang Berlangsung', 'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200', 'icon' => 'fa-spinner fa-spin'],
                            'completed'  => ['label' => 'Selesai', 'bg' => 'bg-stone-100 text-stone-700 border-stone-200', 'icon' => 'fa-certificate'],
                            'cancelled'  => ['label' => 'Dibatalkan', 'bg' => 'bg-rose-50 text-rose-800 border-rose-200', 'icon' => 'fa-circle-xmark'],
                        ];
                        $st = $statusStyles[$t['status']] ?? $statusStyles['pending'];
                        ?>

                        <div class="bg-white rounded-3xl border border-stone-200/80 p-5 sm:p-6 shadow-sm hover:shadow-md transition-all">
                            <!-- Card Header: Kode Booking, Waktu Reservasi & Status -->
                            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-stone-100">
                                <div class="flex items-center space-x-3">
                                    <span class="text-xs text-stone-400 font-medium">Kode:</span>
                                    <span class="font-mono font-bold text-sm text-stone-900 bg-stone-100 px-2.5 py-1 rounded-lg tracking-wider"><?= htmlspecialchars($t['booking_code']) ?></span>
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold <?= $isHomeService ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= $isHomeService ? '<i class="fa-solid fa-house-chimney-medical mr-1"></i>Home Service' : '<i class="fa-solid fa-shop mr-1"></i>Di Klinik Sentosa' ?>
                                    </span>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $st['bg'] ?>">
                                        <i class="fa-solid <?= $st['icon'] ?> text-[10px]"></i>
                                        <span><?= $st['label'] ?></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Detail Tugas: Layanan, Jadwal & Informasi Pelanggan -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-5">
                                <!-- Info Layanan -->
                                <div class="flex items-start space-x-4 md:col-span-1">
                                    <img src="<?= htmlspecialchars($t['image_url'] ?: 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=400') ?>"
                                        alt="<?= htmlspecialchars($t['service_name']) ?>"
                                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shrink-0 border border-stone-100 shadow-sm">
                                    <div>
                                        <h4 class="font-serif font-bold text-stone-900 text-base leading-snug"><?= htmlspecialchars($t['service_name']) ?></h4>
                                        <div class="flex items-center space-x-2 mt-1 text-xs text-stone-500">
                                            <span><i class="fa-regular fa-clock text-emerald-600 mr-1"></i>Durasi: <?= $t['duration'] ?> Menit</span>
                                        </div>
                                        <span class="text-xs font-semibold text-emerald-700 block mt-1"><?= formatRupiah($t['total_price']) ?> (<?= ($t['payment_status'] === 'paid') ? 'Lunas' : 'Belum Bayar' ?>)</span>
                                    </div>
                                </div>

                                <!-- Waktu & Alamat Kunjungan -->
                                <div class="space-y-2.5 md:col-span-1 border-t md:border-t-0 md:border-l border-stone-100 pt-4 md:pt-0 md:pl-6 text-xs">
                                    <div>
                                        <span class="text-stone-400 font-medium block mb-0.5">Jadwal Jam Pelayanan:</span>
                                        <div class="font-bold text-stone-900 text-sm flex items-center space-x-1.5">
                                            <i class="fa-solid fa-calendar-day text-emerald-700"></i>
                                            <span><?= formatDateTimeId($t['schedule_datetime']) ?></span>
                                        </div>
                                    </div>

                                    <?php if ($isHomeService && !empty($t['address'])): ?>
                                        <div class="pt-1">
                                            <span class="text-stone-400 font-medium block mb-0.5">Alamat Kunjungan Rumah Pelanggan:</span>
                                            <p class="text-stone-800 bg-amber-50/70 p-2.5 rounded-xl border border-amber-200/80 leading-relaxed font-medium">
                                                <i class="fa-solid fa-location-dot text-rose-500 mr-1.5"></i><?= htmlspecialchars($t['address']) ?>
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <div class="pt-1 text-stone-600">
                                            <span class="text-stone-400 font-medium block mb-0.5">Lokasi Perawatan:</span>
                                            <p class="bg-stone-50 p-2 rounded-xl border border-stone-100 font-medium">
                                                <i class="fa-solid fa-door-open text-emerald-600 mr-1"></i>Ruang Terapi Sentosa Spa (Klinik)
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($t['notes'])): ?>
                                        <div class="pt-1">
                                            <span class="text-stone-400 font-medium block mb-0.5">Catatan Khusus dari Pelanggan:</span>
                                            <p class="text-stone-700 italic bg-stone-50 p-2 rounded-xl border border-stone-100">
                                                "<?= htmlspecialchars($t['notes']) ?>"
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Informasi Pelanggan & WhatsApp Contact -->
                                <div class="space-y-3 md:col-span-1 border-t md:border-t-0 md:border-l border-stone-100 pt-4 md:pt-0 md:pl-6 text-xs">
                                    <span class="text-stone-400 font-medium block mb-1">Pelanggan yang Dilayani:</span>
                                    <div class="bg-stone-50 p-3.5 rounded-2xl border border-stone-100 space-y-2">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-full bg-brand-800 text-white flex items-center justify-center font-bold text-sm">
                                                <?= strtoupper(substr($t['customer_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-stone-900 text-sm"><?= htmlspecialchars($t['customer_name']) ?></h5>
                                                <span class="text-stone-500 text-[11px] block"><?= htmlspecialchars($t['customer_phone'] ?: '-') ?></span>
                                            </div>
                                        </div>

                                        <?php if (!empty($t['customer_phone'])): ?>
                                            <?php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $t['customer_phone']);
                                            if (substr($cleanPhone, 0, 1) === '0') {
                                                $cleanPhone = '62' . substr($cleanPhone, 1);
                                            }
                                            $waGreeting = rawurlencode("Halo Kak {$t['customer_name']}, saya {$therapist['name']} terapis dari Sentosa Spa yang akan melayani reservasi Anda:\n\n*Layanan:* {$t['service_name']}\n*Jadwal:* " . formatDateTimeId($t['schedule_datetime']) . "\n\nMohon konfirmasinya ya Kak. Terima kasih!");
                                            ?>
                                            <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $waGreeting ?>" target="_blank" class="w-full mt-2 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors flex items-center justify-center space-x-1.5 shadow-sm">
                                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                                <span>Hubungi Pelanggan via WA</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Jika ada ulasan dari pelanggan pada booking ini -->
                            <?php if (!empty($t['review_rating'])): ?>
                                <div class="mb-4 p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-bold text-amber-950">Feedback Bintang dari Pelanggan:</span>
                                            <div class="flex text-amber-500 text-xs">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fa-<?= ($i <= (int)$t['review_rating']) ? 'solid' : 'regular' ?> fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="font-bold text-xs text-amber-900"><?= number_format((float)$t['review_rating'], 1) ?> / 5.0</span>
                                        </div>
                                        <?php if (!empty($t['review_comment'])): ?>
                                            <p class="text-xs text-stone-700 italic leading-relaxed">"<?= htmlspecialchars($t['review_comment']) ?>"</p>
                                        <?php endif; ?>
                                    </div>
                                    <span class="inline-flex items-center text-[10px] text-emerald-800 font-semibold bg-emerald-100/90 border border-emerald-200 px-2.5 py-0.5 rounded-full self-start sm:self-center">
                                        <i class="fa-solid fa-circle-check mr-1 text-xs text-emerald-600"></i> Ulasan Nyata
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- Tombol Tindakan Operasional Sesi Terapis -->
                            <div class="pt-4 border-t border-stone-100 flex flex-wrap items-center justify-between gap-3">
                                <span class="text-xs text-stone-400">
                                    Dibuat: <?= formatDateTimeId($t['created_at']) ?>
                                </span>

                                <div class="flex items-center space-x-2">
                                    <?php if ($t['status'] === 'confirmed'): ?>
                                        <button onclick="changeTaskStatus(<?= (int)$t['id'] ?>, 'on_process', '<?= htmlspecialchars($t['booking_code']) ?>')" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition-all flex items-center space-x-1.5">
                                            <i class="fa-solid fa-play text-xs"></i>
                                            <span>Mulai Sesi Perawatan</span>
                                        </button>
                                    <?php elseif ($t['status'] === 'on_process'): ?>
                                        <button onclick="changeTaskStatus(<?= (int)$t['id'] ?>, 'completed', '<?= htmlspecialchars($t['booking_code']) ?>')" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-950/20 transition-all flex items-center space-x-1.5 animate-pulse hover:animate-none">
                                            <i class="fa-solid fa-check-circle text-xs"></i>
                                            <span>Selesaikan Sesi Perawatan</span>
                                        </button>
                                    <?php elseif ($t['status'] === 'completed'): ?>
                                        <span class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-semibold border border-emerald-200">
                                            <i class="fa-solid fa-check-double text-emerald-600"></i>
                                            <span>Sesi Telah Tuntas Dilayani</span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- KONTEN TAB 4: ULASAN & TESTIMONI PELANGGAN -->
        <?php else: ?>
            <div class="space-y-6">
                <!-- Header Box Ulasan -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-serif text-lg font-bold text-stone-900">Ulasan & Kepuasan Pelanggan</h3>
                        <p class="text-xs text-stone-500 mt-1">Ulasan nyata yang diberikan oleh pelanggan setelah Anda menyelesaikan sesi perawatan.</p>
                    </div>
                    <div class="flex items-center space-x-3 bg-amber-50 p-4 rounded-2xl border border-amber-200 shrink-0">
                        <div class="text-3xl font-bold text-amber-800 font-serif"><?= $therapistRating ?></div>
                        <div>
                            <div class="flex text-amber-500 text-xs">
                                <?php
                                $roundR = round((float)$therapist['rating']);
                                for ($i = 1; $i <= 5; $i++):
                                ?>
                                    <i class="fa-<?= ($i <= $roundR) ? 'solid' : 'regular' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-[11px] text-amber-900 font-bold block mt-0.5"><?= $totalReviews ?> ulasan terverifikasi</span>
                        </div>
                    </div>
                </div>

                <!-- Daftar Ulasan -->
                <?php if (empty($therapistReviews)): ?>
                    <div class="bg-white rounded-3xl border border-stone-200/80 p-12 text-center max-w-md mx-auto my-6 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto text-2xl mb-4">
                            <i class="fa-solid fa-star-half-stroke"></i>
                        </div>
                        <h3 class="font-serif text-lg font-bold text-stone-900">Belum Ada Ulasan Masuk</h3>
                        <p class="text-xs text-stone-500 mt-1 leading-relaxed">
                            Setelah Anda menyelesaikan sesi perawatan, pelanggan akan diminta memberikan rating bintang dan testimoni pengalamannya.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <?php foreach ($therapistReviews as $rev): ?>
                            <div class="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-full bg-brand-800 text-white flex items-center justify-center font-bold text-sm">
                                                <?= strtoupper(substr($rev['customer_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-stone-900 text-sm"><?= htmlspecialchars($rev['customer_name']) ?></h4>
                                                <span class="text-xs text-stone-400"><?= htmlspecialchars($rev['service_name']) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex text-amber-500 text-xs">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa-<?= ($i <= (int)$rev['rating']) ? 'solid' : 'regular' ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>

                                    <?php if (!empty($rev['comment'])): ?>
                                        <p class="text-xs text-stone-700 italic bg-stone-50 p-3 rounded-2xl border border-stone-100 leading-relaxed">
                                            "<?= htmlspecialchars($rev['comment']) ?>"
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="pt-3 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-400">
                                    <span>Kode: <?= htmlspecialchars($rev['booking_code']) ?></span>
                                    <span><?= formatDateTimeId($rev['created_at']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    // 1. Toggle Ketersediaan Kerja Terapis
    async function toggleTherapistAvailability(therapistId) {
        const checkbox = document.getElementById('toggleAvailabilityInput');
        const statusText = document.getElementById('statusText');
        const statusDot = document.getElementById('statusDot');
        const isChecked = checkbox.checked ? 1 : 0;

        try {
            const res = await fetch('api/therapists.php?action=toggle_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    id: therapistId,
                    is_available: isChecked
                })
            });

            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                if (isChecked) {
                    statusText.textContent = 'Tersedia (Siap Tugas)';
                    statusDot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse';
                } else {
                    statusText.textContent = 'Sedang Libur / Istirahat';
                    statusDot.className = 'w-2.5 h-2.5 rounded-full bg-rose-400';
                }
            } else {
                showToast(data.message || 'Gagal mengubah status.', 'error');
                checkbox.checked = !isChecked;
            }
        } catch (e) {
            showToast('Terjadi gangguan koneksi.', 'error');
            checkbox.checked = !isChecked;
        }
    }

    // 2. Ubah Status Tugas Sesi (Mulai Sesi -> on_process, Selesaikan Sesi -> completed)
    async function changeTaskStatus(bookingId, newStatus, code) {
        let confirmMsg = '';
        if (newStatus === 'on_process') {
            confirmMsg = `Apakah Anda siap memulai sesi perawatan untuk pesanan ${code}?`;
        } else if (newStatus === 'completed') {
            confirmMsg = `Apakah sesi perawatan untuk ${code} telah selesai dengan memuaskan?\n\nSetelah selesai, pelanggan akan dapat memberikan rating & ulasan.`;
        }

        if (confirmMsg && !confirm(confirmMsg)) {
            return;
        }

        try {
            const res = await fetch('api/booking.php?action=update_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    id: bookingId,
                    status: newStatus
                })
            });

            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Status tugas berhasil diperbarui!', 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Gagal memperbarui status.', 'error');
            }
        } catch (e) {
            showToast('Terjadi gangguan jaringan.', 'error');
        }
    }
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>