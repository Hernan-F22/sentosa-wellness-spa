<?php
// ==========================================================
// Portal Pelanggan: Riwayat Reservasi & Status Booking Saya
// ==========================================================
require_once __DIR__ . '/config/database.php';

// Wajib Login untuk mengakses portal pelanggan
$currentUser = requireLogin();
$pageTitle = 'Riwayat Booking Saya - ' . APP_NAME;

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/navbar.php';

$db = Database::getConnection();
$customerId = (int)$currentUser['id'];

// Filter Status
$filterStatus = $_GET['status'] ?? 'all';
$validFilters = ['all', 'active', 'completed', 'cancelled'];
if (!in_array($filterStatus, $validFilters)) {
    $filterStatus = 'all';
}

// Hitung Statistik Booking Customer
$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status IN ('pending', 'confirmed', 'on_process') THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
    FROM bookings WHERE customer_id = ?";
$statsStmt = $db->prepare($statsQuery);
$statsStmt->execute([$customerId]);
$stats = $statsStmt->fetch();

$totalCount = (int)($stats['total'] ?? 0);
$activeCount = (int)($stats['active_count'] ?? 0);
$completedCount = (int)($stats['completed_count'] ?? 0);
$cancelledCount = (int)($stats['cancelled_count'] ?? 0);

// Query Data Booking
$query = "SELECT b.*, 
                 s.name as service_name, s.duration_minutes, s.price as service_price, s.image_url, s.type as service_type,
                 tu.name as therapist_name, tu.phone as therapist_phone,
                 t.specialization as therapist_specialization, t.gender as therapist_gender, t.rating as therapist_rating,
                 r.id as review_id, r.rating as review_rating, r.comment as review_comment, r.created_at as review_created_at
          FROM bookings b
          JOIN services s ON b.service_id = s.id
          LEFT JOIN therapists t ON b.therapist_id = t.id
          LEFT JOIN users tu ON t.user_id = tu.id
          LEFT JOIN reviews r ON b.id = r.booking_id
          WHERE b.customer_id = ?";

$params = [$customerId];

if ($filterStatus === 'active') {
    $query .= " AND b.status IN ('pending', 'confirmed', 'on_process')";
} elseif ($filterStatus === 'completed') {
    $query .= " AND b.status = 'completed'";
} elseif ($filterStatus === 'cancelled') {
    $query .= " AND b.status = 'cancelled'";
}

$query .= " ORDER BY b.created_at DESC, b.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<div class="min-h-screen bg-stone-50/80 py-8 sm:py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb & Header Portal -->
        <div class="mb-8">
            <div class="flex items-center space-x-2 text-xs text-stone-500 mb-2">
                <a href="index.php" class="hover:text-emerald-700 transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-stone-800 font-semibold">Riwayat Booking Saya</span>
            </div>

            <!-- Profile Summary Card -->
            <div class="bg-gradient-to-r from-brand-900 via-brand-800 to-emerald-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-brand-950/10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center space-x-4 sm:space-x-5">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-white flex items-center justify-center text-2xl sm:text-3xl font-bold font-serif shadow-inner">
                        <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h1 class="text-xl sm:text-2xl font-bold font-serif"><?= htmlspecialchars($currentUser['name']) ?></h1>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-400/20 text-emerald-300 text-[10px] font-semibold tracking-wide uppercase border border-emerald-400/30">Pelanggan</span>
                        </div>
                        <p class="text-xs sm:text-sm text-stone-300 mt-1 flex items-center space-x-4">
                            <span><i class="fa-regular fa-envelope mr-1.5 text-emerald-400"></i><?= htmlspecialchars($currentUser['email']) ?></span>
                            <?php if (!empty($currentUser['phone'])): ?>
                                <span><i class="fa-brands fa-whatsapp mr-1.5 text-emerald-400"></i><?= htmlspecialchars($currentUser['phone']) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Action CTA -->
                <div class="flex items-center space-x-3">
                    <a href="booking.php" class="px-5 py-3 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white text-xs sm:text-sm font-semibold shadow-lg shadow-emerald-950/20 transition-all flex items-center space-x-2">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Reservasi Baru</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Counter Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-8">
            <a href="my-bookings.php?status=all" class="p-4 rounded-2xl border transition-all <?= ($filterStatus === 'all') ? 'bg-white border-brand-800 shadow-md ring-2 ring-brand-800/10' : 'bg-white/60 border-stone-200/80 hover:bg-white' ?>">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Semua</span>
                    <i class="fa-solid fa-receipt text-stone-400 text-sm"></i>
                </div>
                <span class="text-2xl font-bold text-stone-900 mt-1 block"><?= $totalCount ?></span>
            </a>

            <a href="my-bookings.php?status=active" class="p-4 rounded-2xl border transition-all <?= ($filterStatus === 'active') ? 'bg-white border-blue-600 shadow-md ring-2 ring-blue-600/10' : 'bg-white/60 border-stone-200/80 hover:bg-white' ?>">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-blue-700 uppercase tracking-wider">Aktif & Jadwal</span>
                    <i class="fa-solid fa-calendar-check text-blue-500 text-sm"></i>
                </div>
                <span class="text-2xl font-bold text-blue-800 mt-1 block"><?= $activeCount ?></span>
            </a>

            <a href="my-bookings.php?status=completed" class="p-4 rounded-2xl border transition-all <?= ($filterStatus === 'completed') ? 'bg-white border-emerald-600 shadow-md ring-2 ring-emerald-600/10' : 'bg-white/60 border-stone-200/80 hover:bg-white' ?>">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Selesai</span>
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                </div>
                <span class="text-2xl font-bold text-emerald-800 mt-1 block"><?= $completedCount ?></span>
            </a>

            <a href="my-bookings.php?status=cancelled" class="p-4 rounded-2xl border transition-all <?= ($filterStatus === 'cancelled') ? 'bg-white border-rose-600 shadow-md ring-2 ring-rose-600/10' : 'bg-white/60 border-stone-200/80 hover:bg-white' ?>">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Dibatalkan</span>
                    <i class="fa-solid fa-circle-xmark text-rose-400 text-sm"></i>
                </div>
                <span class="text-2xl font-bold text-rose-800 mt-1 block"><?= $cancelledCount ?></span>
            </a>
        </div>

        <!-- Filter Tab Buttons -->
        <div class="flex items-center space-x-2 border-b border-stone-200 pb-4 mb-6 overflow-x-auto">
            <a href="my-bookings.php?status=all" class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= ($filterStatus === 'all') ? 'bg-stone-900 text-white' : 'text-stone-600 hover:bg-stone-200/60' ?>">
                Semua Pesanan (<?= $totalCount ?>)
            </a>
            <a href="my-bookings.php?status=active" class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= ($filterStatus === 'active') ? 'bg-blue-800 text-white' : 'text-stone-600 hover:bg-stone-200/60' ?>">
                Aktif & Mendatang (<?= $activeCount ?>)
            </a>
            <a href="my-bookings.php?status=completed" class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= ($filterStatus === 'completed') ? 'bg-emerald-800 text-white' : 'text-stone-600 hover:bg-stone-200/60' ?>">
                Selesai (<?= $completedCount ?>)
            </a>
            <a href="my-bookings.php?status=cancelled" class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= ($filterStatus === 'cancelled') ? 'bg-rose-800 text-white' : 'text-stone-600 hover:bg-stone-200/60' ?>">
                Dibatalkan (<?= $cancelledCount ?>)
            </a>
        </div>

        <!-- Daftar Kartu Riwayat Booking -->
        <?php if (empty($bookings)): ?>
            <div class="bg-white rounded-3xl border border-stone-200/80 p-12 text-center max-w-md mx-auto my-6 shadow-sm">
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center mx-auto text-2xl mb-4">
                    <i class="fa-solid fa-spa"></i>
                </div>
                <h3 class="font-serif text-lg font-bold text-stone-900">Belum Ada Riwayat Reservasi</h3>
                <p class="text-xs text-stone-500 mt-1 mb-6 leading-relaxed">
                    <?= ($filterStatus !== 'all') ? 'Tidak ada pesanan dengan filter status ini.' : 'Anda belum pernah melakukan pemesanan layanan pijat. Rilekskan tubuh dan pikiran Anda bersama terapis profesional kami.' ?>
                </p>
                <a href="booking.php" class="inline-flex items-center space-x-2 px-6 py-3 bg-brand-800 hover:bg-brand-900 text-white text-xs font-semibold rounded-xl shadow-md transition-all">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Buat Reservasi Sekarang</span>
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4 sm:space-y-5">
                <?php foreach ($bookings as $b): ?>
                    <?php
                    // Helper Status Style
                    $statusConfig = [
                        'pending'    => ['label' => 'Menunggu Konfirmasi', 'bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'icon' => 'fa-clock'],
                        'confirmed'  => ['label' => 'Terkonfirmasi', 'bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'border' => 'border-blue-200', 'icon' => 'fa-circle-check'],
                        'on_process' => ['label' => 'Sesi Berlangsung', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200', 'icon' => 'fa-spinner fa-spin'],
                        'completed'  => ['label' => 'Selesai', 'bg' => 'bg-emerald-100/70', 'text' => 'text-emerald-900', 'border' => 'border-emerald-300', 'icon' => 'fa-certificate'],
                        'cancelled'  => ['label' => 'Dibatalkan', 'bg' => 'bg-rose-50', 'text' => 'text-rose-800', 'border' => 'border-rose-200', 'icon' => 'fa-circle-xmark'],
                    ];
                    $st = $statusConfig[$b['status']] ?? $statusConfig['pending'];
                    $isHomeService = ($b['booking_type'] === 'home_service');
                    ?>

                    <div class="bg-white rounded-3xl border border-stone-200/80 p-5 sm:p-6 shadow-sm hover:shadow-md transition-all">
                        <!-- Top Header: Kode Booking & Status Badges -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-stone-100">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-stone-400 font-medium">Kode:</span>
                                    <span class="font-mono font-bold text-sm text-stone-900 bg-stone-100 px-2.5 py-1 rounded-lg tracking-wider"><?= htmlspecialchars($b['booking_code']) ?></span>
                                    <button onclick="copyCode('<?= htmlspecialchars($b['booking_code']) ?>')" class="text-stone-400 hover:text-stone-700 text-xs p-1" title="Salin Kode Booking">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                                <span class="text-stone-300 hidden sm:inline">•</span>
                                <span class="text-xs text-stone-400 hidden sm:inline"><?= formatDateTimeId($b['created_at']) ?></span>
                            </div>

                            <div class="flex items-center space-x-2">
                                <!-- Status Reservasi -->
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold border <?= $st['bg'] ?> <?= $st['text'] ?> <?= $st['border'] ?>">
                                    <i class="fa-solid <?= $st['icon'] ?> text-[10px]"></i>
                                    <span><?= $st['label'] ?></span>
                                </span>

                                <!-- Status Pembayaran -->
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase <?= ($b['payment_status'] === 'paid') ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                    <?= ($b['payment_status'] === 'paid') ? 'Lunas' : 'Belum Bayar' ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Main Content -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-5">
                            <!-- Service Details -->
                            <div class="flex items-start space-x-4 md:col-span-1">
                                <img src="<?= htmlspecialchars($b['image_url'] ?: 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=400') ?>"
                                    alt="<?= htmlspecialchars($b['service_name']) ?>"
                                    class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shrink-0 border border-stone-100 shadow-sm">
                                <div>
                                    <h4 class="font-serif font-bold text-stone-900 text-base leading-snug"><?= htmlspecialchars($b['service_name']) ?></h4>
                                    <div class="flex items-center space-x-2 mt-1 text-xs text-stone-500">
                                        <span><i class="fa-regular fa-clock text-emerald-600 mr-1"></i><?= $b['duration'] ?> Menit</span>
                                        <span>•</span>
                                        <span class="font-semibold text-emerald-700"><?= formatRupiah($b['total_price']) ?></span>
                                    </div>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-semibold <?= $isHomeService ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= $isHomeService ? '<i class="fa-solid fa-house-chimney-medical mr-1"></i>Home Service' : '<i class="fa-solid fa-shop mr-1"></i>Di Klinik Sentosa' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Jadwal & Alamat -->
                            <div class="space-y-2 md:col-span-1 border-t md:border-t-0 md:border-l border-stone-100 pt-4 md:pt-0 md:pl-6 text-xs">
                                <div>
                                    <span class="text-stone-400 font-medium block mb-0.5">Jadwal Sesi Perawatan:</span>
                                    <div class="font-bold text-stone-900 text-sm flex items-center space-x-1.5">
                                        <i class="fa-solid fa-calendar-day text-emerald-700"></i>
                                        <span><?= formatDateTimeId($b['schedule_datetime']) ?></span>
                                    </div>
                                </div>

                                <?php if ($isHomeService && !empty($b['address'])): ?>
                                    <div class="pt-2">
                                        <span class="text-stone-400 font-medium block mb-0.5">Alamat Kunjungan Terapis:</span>
                                        <p class="text-stone-700 italic bg-stone-50 p-2 rounded-xl border border-stone-200/60 line-clamp-2">
                                            <?= htmlspecialchars($b['address']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($b['notes'])): ?>
                                    <div class="pt-1">
                                        <span class="text-stone-400 font-medium block mb-0.5">Catatan Khusus:</span>
                                        <p class="text-stone-600 italic">"<?= htmlspecialchars($b['notes']) ?>"</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Terapis yang Ditugaskan -->
                            <div class="space-y-3 md:col-span-1 border-t md:border-t-0 md:border-l border-stone-100 pt-4 md:pt-0 md:pl-6 text-xs">
                                <span class="text-stone-400 font-medium block mb-1">Terapis Pelaksana:</span>
                                <?php if (!empty($b['therapist_name'])): ?>
                                    <div class="flex items-center space-x-3 bg-stone-50 p-3 rounded-2xl border border-stone-100">
                                        <div class="w-10 h-10 rounded-full bg-brand-800 text-white flex items-center justify-center font-bold text-sm font-serif shrink-0">
                                            <?= substr($b['therapist_name'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <span class="font-bold text-stone-900 text-sm"><?= htmlspecialchars($b['therapist_name']) ?></span>
                                                <span class="text-[10px] text-amber-600 font-bold"><i class="fa-solid fa-star text-[9px]"></i> <?= number_format((float)($b['therapist_rating'] ?? 4.9), 1) ?></span>
                                            </div>
                                            <span class="text-[11px] text-stone-500 block truncate"><?= htmlspecialchars($b['therapist_specialization'] ?? 'Terapis Berlisensi') ?></span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="p-3 rounded-2xl bg-amber-50/60 border border-amber-100 text-amber-900">
                                        <div class="font-semibold flex items-center space-x-1.5">
                                            <i class="fa-solid fa-user-clock text-amber-600"></i>
                                            <span>Belum Ditugaskan</span>
                                        </div>
                                        <p class="text-[11px] text-stone-500 mt-1">Admin akan menugaskan terapis terbaik sebelum jadwal sesi tiba.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($b['status'] === 'completed' && !empty($b['review_rating'])): ?>
                            <!-- Kotak Ulasan yang Sudah Diberikan Pelanggan -->
                            <div class="mb-4 p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold text-amber-950">Ulasan & Penilaian Anda:</span>
                                        <div class="flex text-amber-500 text-xs">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa-<?= ($i <= (int)$b['review_rating']) ? 'solid' : 'regular' ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="font-bold text-xs text-amber-900"><?= number_format((float)$b['review_rating'], 1) ?> / 5.0</span>
                                    </div>
                                    <?php if (!empty($b['review_comment'])): ?>
                                        <p class="text-xs text-stone-700 italic leading-relaxed">"<?= htmlspecialchars($b['review_comment']) ?>"</p>
                                    <?php endif; ?>
                                </div>
                                <span class="inline-flex items-center text-[11px] text-emerald-800 font-semibold bg-emerald-100/90 border border-emerald-200 px-3 py-1 rounded-full shrink-0 self-start sm:self-center">
                                    <i class="fa-solid fa-circle-check mr-1.5 text-xs text-emerald-600"></i> Ulasan Terverifikasi
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- Card Footer Action Buttons -->
                        <div class="pt-4 border-t border-stone-100 flex flex-wrap items-center justify-between gap-3">
                            <!-- WhatsApp Admin Direct Action -->
                            <?php
                            $waMessage = rawurlencode("Halo Admin Sentosa Spa, saya ingin konfirmasi/bertanya mengenai reservasi saya:\n\n*Kode Booking:* {$b['booking_code']}\n*Layanan:* {$b['service_name']}\n*Jadwal:* " . formatDateTimeId($b['schedule_datetime']) . "\n*Atas Nama:* {$currentUser['name']}");
                            ?>
                            <a href="https://wa.me/<?= APP_PHONE ?>?text=<?= $waMessage ?>" target="_blank" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition-colors">
                                <i class="fa-brands fa-whatsapp text-sm text-emerald-600"></i>
                                <span>Hubungi Admin WA</span>
                            </a>

                            <div class="flex items-center space-x-2">
                                <!-- Tombol Batalkan jika status masih pending -->
                                <?php if ($b['status'] === 'pending'): ?>
                                    <button onclick="cancelMyBooking(<?= (int)$b['id'] ?>, '<?= htmlspecialchars($b['booking_code']) ?>')" class="px-3 py-2 rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-semibold transition-colors">
                                        <i class="fa-solid fa-ban mr-1"></i> Batalkan
                                    </button>
                                <?php endif; ?>

                                <!-- Tombol Beri Ulasan & Rating (Khusus Completed & Belum Direview) -->
                                <?php if ($b['status'] === 'completed' && empty($b['review_rating']) && !empty($b['therapist_id'])): ?>
                                    <button onclick="openReviewModal(<?= (int)$b['id'] ?>, '<?= htmlspecialchars(addslashes($b['therapist_name'] ?? 'Terapis')) ?>', '<?= htmlspecialchars(addslashes($b['service_name'])) ?>')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold shadow-sm transition-all flex items-center space-x-1.5 animate-pulse hover:animate-none">
                                        <i class="fa-solid fa-star text-amber-200 text-xs"></i>
                                        <span>Beri Rating & Ulasan</span>
                                    </button>
                                <?php endif; ?>

                                <!-- Pesan Ulang Layanan Ini -->
                                <a href="booking.php?service_id=<?= (int)$b['service_id'] ?>" class="px-4 py-2 rounded-xl bg-brand-800 hover:bg-brand-900 text-white text-xs font-semibold shadow-sm transition-colors flex items-center space-x-1.5">
                                    <i class="fa-solid fa-arrow-rotate-right text-[10px]"></i>
                                    <span>Pesan Ulang</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL: BERI RATING & ULASAN TERAPIS -->
<!-- ========================================================== -->
<div id="reviewModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Backdrop -->
        <div onclick="closeReviewModal()" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm transition-opacity"></div>

        <!-- Dialog Box -->
        <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-stone-100 z-10 space-y-5 transform transition-all">
            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-stone-100 pb-4">
                <div>
                    <h3 class="font-serif text-lg font-bold text-stone-900">Beri Rating & Ulasan</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Penilaian Anda sangat berharga bagi kualitas layanan terapis kami.</p>
                </div>
                <button onclick="closeReviewModal()" class="text-stone-400 hover:text-stone-700 w-8 h-8 rounded-full hover:bg-stone-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Target Summary -->
            <div class="bg-stone-50 p-3.5 rounded-2xl border border-stone-100 flex items-center space-x-3 text-xs">
                <div class="w-10 h-10 rounded-xl bg-brand-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                    <i class="fa-solid fa-spa"></i>
                </div>
                <div>
                    <span class="text-stone-400 text-[10px] uppercase font-bold block">Terapis & Layanan:</span>
                    <span class="font-bold text-stone-900" id="revTherapistName">Terapis</span>
                    <span class="text-stone-400 mx-1">•</span>
                    <span class="text-stone-600" id="revServiceName">Layanan</span>
                </div>
            </div>

            <!-- Form Review -->
            <form onsubmit="submitReviewForm(event)" class="space-y-4">
                <input type="hidden" id="revBookingId" value="">
                <input type="hidden" id="revRatingValue" value="5">

                <!-- Interactive Star Rating -->
                <div class="text-center py-3 bg-amber-50/50 rounded-2xl border border-amber-100">
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-2">Berapa bintang untuk pengalaman Anda?</label>
                    <div class="flex items-center justify-center space-x-2 text-3xl text-stone-300" id="starRatingContainer">
                        <button type="button" class="star-btn hover:scale-110 transition-transform focus:outline-none" data-val="1" onclick="setStarRating(1)" onmouseenter="previewStars(1)" onmouseleave="resetPreviewStars()"><i class="fa-solid fa-star text-amber-400"></i></button>
                        <button type="button" class="star-btn hover:scale-110 transition-transform focus:outline-none" data-val="2" onclick="setStarRating(2)" onmouseenter="previewStars(2)" onmouseleave="resetPreviewStars()"><i class="fa-solid fa-star text-amber-400"></i></button>
                        <button type="button" class="star-btn hover:scale-110 transition-transform focus:outline-none" data-val="3" onclick="setStarRating(3)" onmouseenter="previewStars(3)" onmouseleave="resetPreviewStars()"><i class="fa-solid fa-star text-amber-400"></i></button>
                        <button type="button" class="star-btn hover:scale-110 transition-transform focus:outline-none" data-val="4" onclick="setStarRating(4)" onmouseenter="previewStars(4)" onmouseleave="resetPreviewStars()"><i class="fa-solid fa-star text-amber-400"></i></button>
                        <button type="button" class="star-btn hover:scale-110 transition-transform focus:outline-none" data-val="5" onclick="setStarRating(5)" onmouseenter="previewStars(5)" onmouseleave="resetPreviewStars()"><i class="fa-solid fa-star text-amber-400"></i></button>
                    </div>
                    <div class="mt-2 font-bold text-xs text-amber-900" id="starRatingLabel">⭐⭐⭐⭐⭐ Luar Biasa Memuaskan (5/5)</div>
                </div>

                <!-- Review Text Area -->
                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Ceritakan Pengalaman Pijat Anda <span class="text-stone-400 font-normal lowercase">(opsional)</span></label>
                    <textarea id="revComment" rows="3" placeholder="Contoh: Terapis sangat ramah, pijatannya pas dan membuat otot rileks, suasana sangat nyaman..." class="w-full px-3.5 py-2.5 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition-all leading-relaxed"></textarea>
                </div>

                <!-- Buttons -->
                <div class="pt-2 flex space-x-2">
                    <button type="button" onclick="closeReviewModal()" class="flex-1 py-2.5 rounded-xl border border-stone-200 hover:bg-stone-50 text-stone-600 font-semibold text-xs transition-colors">Batal</button>
                    <button type="submit" id="btnSubmitReview" class="flex-1 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-900/10 transition-colors flex items-center justify-center space-x-1.5">
                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                        <span>Kirim Penilaian</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function copyCode(code) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(() => {
                showToast('Kode booking berhasil disalin: ' + code, 'success');
            }).catch(() => {
                promptCopy(code);
            });
        } else {
            promptCopy(code);
        }
    }

    function promptCopy(code) {
        window.prompt('Salin kode booking Anda:', code);
    }

    async function cancelMyBooking(bookingId, code) {
        if (!confirm(`Apakah Anda yakin ingin membatalkan pesanan ${code}?`)) {
            return;
        }

        try {
            const res = await fetch('api/booking.php?action=cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    id: bookingId
                })
            });

            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Pesanan berhasil dibatalkan.', 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Gagal membatalkan pesanan.', 'error');
            }
        } catch (e) {
            showToast('Terjadi gangguan koneksi.', 'error');
        }
    }

    // Review Modal & Rating Logic
    let currentRating = 5;
    const ratingLabels = {
        1: '⭐ Kecewa / Perlu Ditingkatkan (1/5)',
        2: '⭐⭐ Kurang Memuaskan (2/5)',
        3: '⭐⭐⭐ Cukup Baik & Sesuai (3/5)',
        4: '⭐⭐⭐⭐ Sangat Bagus & Rileks (4/5)',
        5: '⭐⭐⭐⭐⭐ Luar Biasa Memuaskan (5/5)'
    };

    function openReviewModal(bookingId, therapistName, serviceName) {
        document.getElementById('revBookingId').value = bookingId;
        document.getElementById('revTherapistName').textContent = therapistName;
        document.getElementById('revServiceName').textContent = serviceName;
        document.getElementById('revComment').value = '';
        setStarRating(5);
        document.getElementById('reviewModal').classList.remove('hidden');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }

    function setStarRating(val) {
        currentRating = val;
        document.getElementById('revRatingValue').value = val;
        renderStars(val);
    }

    function previewStars(val) {
        renderStars(val);
    }

    function resetPreviewStars() {
        renderStars(currentRating);
    }

    function renderStars(val) {
        const buttons = document.querySelectorAll('#starRatingContainer .star-btn');
        buttons.forEach((btn, index) => {
            const starIcon = btn.querySelector('i');
            if (index < val) {
                starIcon.className = 'fa-solid fa-star text-amber-400';
            } else {
                starIcon.className = 'fa-regular fa-star text-stone-300';
            }
        });
        document.getElementById('starRatingLabel').textContent = ratingLabels[val] || '';
    }

    async function submitReviewForm(e) {
        e.preventDefault();
        const bookingId = parseInt(document.getElementById('revBookingId').value);
        const rating = parseInt(document.getElementById('revRatingValue').value) || 5;
        const comment = document.getElementById('revComment').value.trim();
        const submitBtn = document.getElementById('btnSubmitReview');

        if (!bookingId) {
            showToast('ID reservasi tidak valid.', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';

        try {
            const res = await fetch('api/booking.php?action=submit_review', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    booking_id: bookingId,
                    rating: rating,
                    comment: comment
                })
            });

            const data = await res.json();
            if (data.success) {
                showToast(data.message || 'Ulasan berhasil disimpan!', 'success');
                closeReviewModal();
                setTimeout(() => window.location.reload(), 900);
            } else {
                showToast(data.message || 'Gagal mengirim ulasan.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane text-[10px]"></i> <span>Kirim Penilaian</span>';
            }
        } catch (err) {
            showToast('Terjadi gangguan jaringan.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane text-[10px]"></i> <span>Kirim Penilaian</span>';
        }
    }
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>