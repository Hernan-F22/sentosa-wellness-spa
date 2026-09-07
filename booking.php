<?php
// ==========================================================
// Halaman Booking Wizard: Multi-Step Reservation & WA Checkout
// ==========================================================
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Form Reservasi Layanan Pijat - ' . APP_NAME;
require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/navbar.php';

$db = Database::getConnection();

// Ambil semua layanan aktif
$servicesStmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY price ASC");
$allServices = $servicesStmt->fetchAll();

// Ambil semua terapis
$therapistsStmt = $db->query("SELECT t.*, u.name, u.phone, COUNT(r.id) as total_reviews 
                              FROM therapists t 
                              JOIN users u ON t.user_id = u.id 
                              LEFT JOIN reviews r ON t.id = r.therapist_id
                              GROUP BY t.id, t.user_id, t.specialization, t.gender, t.is_available, t.rating, t.created_at, u.name, u.phone
                              ORDER BY t.is_available DESC, t.rating DESC");
$allTherapists = $therapistsStmt->fetchAll();

// Cek parameter pre-selected dari URL
$preSelectedServiceId = (int)($_GET['service_id'] ?? 0);
$preSelectedTherapistId = (int)($_GET['therapist_id'] ?? 0);
$preSelectedType = $_GET['type'] ?? '';

$currentUser = getCurrentUser();
?>

<div class="min-h-screen bg-stone-50 py-10 lg:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Title -->
        <div class="text-center mb-10">
            <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold tracking-wider uppercase mb-2">
                Sistem Reservasi Praktis
            </span>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900">Pemesanan Sesi Pijat & Wellness</h1>
            <p class="text-stone-600 text-sm sm:text-base mt-2">Lengkapi tahapan di bawah ini untuk mengamankan slot jadwal terapis Anda.</p>
        </div>

        <!-- WIZARD CARD -->
        <div class="bg-white rounded-3xl border border-stone-200/80 shadow-xl overflow-hidden">

            <!-- Step Indicators -->
            <div class="bg-stone-900 px-6 py-4 border-b border-stone-800 text-white">
                <div class="flex items-center justify-between max-w-2xl mx-auto">
                    <!-- Step 1 Indicator -->
                    <div class="flex items-center space-x-2 step-indicator active" id="ind-1">
                        <span class="w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs font-bold ring-4 ring-brand-900/50 ind-circle">1</span>
                        <span class="text-xs font-medium hidden sm:inline ind-text">Lokasi</span>
                    </div>
                    <div class="w-8 sm:w-12 h-0.5 bg-stone-700 step-line" id="line-1"></div>

                    <!-- Step 2 Indicator -->
                    <div class="flex items-center space-x-2 step-indicator" id="ind-2">
                        <span class="w-8 h-8 rounded-full bg-stone-800 text-stone-400 flex items-center justify-center text-xs font-bold ind-circle">2</span>
                        <span class="text-xs font-medium text-stone-400 hidden sm:inline ind-text">Menu Pijat</span>
                    </div>
                    <div class="w-8 sm:w-12 h-0.5 bg-stone-700 step-line" id="line-2"></div>

                    <!-- Step 3 Indicator -->
                    <div class="flex items-center space-x-2 step-indicator" id="ind-3">
                        <span class="w-8 h-8 rounded-full bg-stone-800 text-stone-400 flex items-center justify-center text-xs font-bold ind-circle">3</span>
                        <span class="text-xs font-medium text-stone-400 hidden sm:inline ind-text">Terapis</span>
                    </div>
                    <div class="w-8 sm:w-12 h-0.5 bg-stone-700 step-line" id="line-3"></div>

                    <!-- Step 4 Indicator -->
                    <div class="flex items-center space-x-2 step-indicator" id="ind-4">
                        <span class="w-8 h-8 rounded-full bg-stone-800 text-stone-400 flex items-center justify-center text-xs font-bold ind-circle">4</span>
                        <span class="text-xs font-medium text-stone-400 hidden sm:inline ind-text">Waktu</span>
                    </div>
                    <div class="w-8 sm:w-12 h-0.5 bg-stone-700 step-line" id="line-4"></div>

                    <!-- Step 5 Indicator -->
                    <div class="flex items-center space-x-2 step-indicator" id="ind-5">
                        <span class="w-8 h-8 rounded-full bg-stone-800 text-stone-400 flex items-center justify-center text-xs font-bold ind-circle">5</span>
                        <span class="text-xs font-medium text-stone-400 hidden sm:inline ind-text">Konfirmasi</span>
                    </div>
                </div>
            </div>

            <!-- Form Content Body -->
            <form id="bookingWizardForm" onsubmit="handleWizardSubmit(event)" class="p-6 sm:p-10">

                <!-- STEP 1: LOKASI KUNJUNGAN HOME SERVICE -->
                <div class="step-panel" id="stepPanel-1">
                    <input type="hidden" name="booking_type" value="home_service">

                    <h2 class="font-serif text-2xl font-bold text-stone-900 mb-2">Lokasi Kunjungan Home Service</h2>
                    <p class="text-stone-500 text-xs sm:text-sm mb-6">Sentosa Spa melayani terapi pijat panggilan eksklusif langsung ke rumah, apartemen, atau kamar hotel Anda.</p>

                    <!-- Banner Info Home Service 100% -->
                    <div class="p-5 rounded-2xl border-2 border-emerald-600 bg-emerald-50/60 mb-6 flex items-start space-x-4 shadow-sm">
                        <div class="w-12 h-12 rounded-2xl bg-brand-800 text-white flex items-center justify-center text-2xl shrink-0 shadow-md">
                            <i class="fa-solid fa-house-chimney-medical text-emerald-300"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <h3 class="font-bold text-stone-900 text-base">Layanan Pijat Panggilan (100% Home Service)</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200 text-emerald-800">Khusus Panggilan</span>
                            </div>
                            <p class="text-xs text-stone-600 mt-1.5 leading-relaxed">
                                Terapis profesional kami akan tiba di lokasi Anda membawa matras steril, handuk bersih, aromaterapi hangat, dan perlengkapan spa higienis lengkap. Anda cukup rileks di kediaman Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Input Alamat Kunjungan -->
                    <div id="addressSection" class="space-y-3 mb-6">
                        <div class="flex items-center space-x-2 text-stone-900 font-bold text-sm">
                            <i class="fa-solid fa-location-dot text-emerald-700"></i>
                            <span>Alamat Lengkap Kunjungan (Rumah / Apartemen / Hotel) <span class="text-rose-500">*</span></span>
                        </div>
                        <div>
                            <textarea id="inputAddress" rows="3" required placeholder="Contoh: Jl. Senopati No. 12, Kebayoran Baru, Jakarta Selatan. Pagar warna hitam di seberang Lawson (Sebutkan nama hotel & no. kamar jika menginap)." class="w-full px-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all"></textarea>
                        </div>
                        <p class="text-[11px] text-stone-500 flex items-center space-x-1">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span>Gratis biaya transport untuk wilayah Jakarta Selatan dan radius 10 km.</span>
                        </p>
                    </div>
                </div>

                <!-- STEP 2: PILIH MENU LAYANAN -->
                <div class="step-panel hidden" id="stepPanel-2">
                    <h2 class="font-serif text-2xl font-bold text-stone-900 mb-2">Pilih Menu Layanan Pijat</h2>
                    <p class="text-stone-500 text-xs sm:text-sm mb-6">Pilih salah satu paket perawatan yang sesuai dengan kebutuhan tubuh Anda.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="servicesGrid">
                        <?php foreach ($allServices as $s): ?>
                            <label class="service-option-card relative flex items-start p-4 rounded-2xl border-2 cursor-pointer transition-all border-stone-200 hover:border-emerald-600 bg-white"
                                data-service-id="<?= $s['id'] ?>"
                                data-service-type="<?= $s['type'] ?>"
                                data-price="<?= $s['price'] ?>"
                                data-duration="<?= $s['duration_minutes'] ?>"
                                data-name="<?= htmlspecialchars($s['name']) ?>">
                                <input type="radio" name="selected_service" value="<?= $s['id'] ?>" class="sr-only" <?= ($preSelectedServiceId == $s['id']) ? 'checked' : '' ?>>

                                <img src="<?= htmlspecialchars($s['image_url'] ?: 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=400') ?>"
                                    alt="<?= htmlspecialchars($s['name']) ?>"
                                    class="w-20 h-20 rounded-xl object-cover shrink-0 mr-4 border border-stone-100">

                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-stone-900 text-sm"><?= htmlspecialchars($s['name']) ?></h4>
                                        <span class="w-5 h-5 rounded-full border-2 border-stone-300 flex items-center justify-center srv-check">
                                            <span class="w-2.5 h-2.5 rounded-full bg-transparent"></span>
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-3 text-xs text-stone-500 mt-1">
                                        <span><i class="fa-regular fa-clock text-emerald-600 mr-1"></i><?= $s['duration_minutes'] ?> Menit</span>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                            <i class="fa-solid fa-house-chimney mr-1"></i>Home Service
                                        </span>
                                    </div>
                                    <p class="text-xs font-bold text-stone-900 mt-2"><?= formatRupiah($s['price']) ?></p>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- STEP 3: PREFERENSI TERAPIS -->
                <div class="step-panel hidden" id="stepPanel-3">
                    <h2 class="font-serif text-2xl font-bold text-stone-900 mb-2">Preferensi Terapis</h2>
                    <p class="text-stone-500 text-xs sm:text-sm mb-6">Pilih preferensi gender atau tentukan terapis favorit yang Anda inginkan.</p>

                    <!-- Pilihan Gender -->
                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-2">Preferensi Gender Terapis</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="gender-btn flex items-center justify-center p-3 rounded-xl border-2 border-emerald-700 bg-emerald-50 text-emerald-900 cursor-pointer text-xs font-bold" id="genderAny">
                                <input type="radio" name="gender_pref" value="any" checked onchange="handleGenderFilter()" class="sr-only">
                                <i class="fa-solid fa-users mr-2"></i> Bebas / Siapa Saja
                            </label>
                            <label class="gender-btn flex items-center justify-center p-3 rounded-xl border-2 border-stone-200 bg-white text-stone-700 cursor-pointer text-xs font-bold" id="genderFemale">
                                <input type="radio" name="gender_pref" value="female" onchange="handleGenderFilter()" class="sr-only">
                                <i class="fa-solid fa-venus mr-2 text-pink-600"></i> Terapis Wanita
                            </label>
                            <label class="gender-btn flex items-center justify-center p-3 rounded-xl border-2 border-stone-200 bg-white text-stone-700 cursor-pointer text-xs font-bold" id="genderMale">
                                <input type="radio" name="gender_pref" value="male" onchange="handleGenderFilter()" class="sr-only">
                                <i class="fa-solid fa-mars mr-2 text-blue-600"></i> Terapis Pria
                            </label>
                        </div>
                    </div>

                    <!-- Pilihan Spesifik Terapis (Opsional) -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider">Pilih Terapis Spesifik (Opsional)</label>
                            <span class="text-xs text-stone-500">Biarkan "Otomatis Ditentukan" jika fleksibel</span>
                        </div>

                        <div class="space-y-3" id="therapistsList">
                            <!-- Opsi Auto Assign -->
                            <label class="therapist-radio-card flex items-center justify-between p-4 rounded-2xl border-2 border-emerald-700 bg-emerald-50/60 cursor-pointer">
                                <input type="radio" name="selected_therapist" value="" checked class="sr-only">
                                <div class="flex items-center space-x-3">
                                    <div class="w-11 h-11 rounded-full bg-brand-800 text-white flex items-center justify-center text-lg">
                                        <i class="fa-solid fa-wand-magic-sparkles text-emerald-300"></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-stone-900 text-sm block">Tentukan Otomatis oleh Sistem (Rekomendasi)</span>
                                        <span class="text-xs text-stone-500">Admin akan menugaskan terapis terbaik yang paling dekat dan tersedia.</span>
                                    </div>
                                </div>
                                <span class="w-5 h-5 rounded-full border-2 border-emerald-700 flex items-center justify-center th-check">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-700"></span>
                                </span>
                            </label>

                            <!-- Daftar Terapis Perorangan -->
                            <?php foreach ($allTherapists as $th): ?>
                                <label class="therapist-radio-card flex items-center justify-between p-4 rounded-2xl border-2 border-stone-200 bg-white hover:border-stone-300 cursor-pointer <?= ($th['is_available'] == 0) ? 'opacity-60 pointer-events-none' : '' ?>"
                                    data-gender="<?= $th['gender'] ?>"
                                    data-available="<?= $th['is_available'] ?>">
                                    <input type="radio" name="selected_therapist" value="<?= $th['id'] ?>" class="sr-only" <?= ($preSelectedTherapistId == $th['id'] && $th['is_available'] == 1) ? 'checked' : '' ?> <?= ($th['is_available'] == 0) ? 'disabled' : '' ?>>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-11 h-11 rounded-full bg-stone-200 text-stone-700 flex items-center justify-center text-base font-bold font-serif">
                                            <?= substr($th['name'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <span class="font-bold text-stone-900 text-sm"><?= htmlspecialchars($th['name']) ?></span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= ($th['gender'] === 'female') ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' ?>">
                                                    <?= ($th['gender'] === 'female') ? 'Wanita' : 'Pria' ?>
                                                </span>
                                                <span class="text-xs text-amber-600 font-bold"><i class="fa-solid fa-star text-[10px]"></i> <?= number_format((float)$th['rating'], 1) ?> <span class="text-stone-400 font-normal text-[10px]">(<?= (int)$th['total_reviews'] ?>)</span></span>
                                            </div>
                                            <span class="text-xs text-stone-500 block line-clamp-1"><?= htmlspecialchars($th['specialization']) ?></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <?php if ($th['is_available'] == 1): ?>
                                            <span class="text-xs text-emerald-700 font-semibold hidden sm:inline">Tersedia</span>
                                        <?php else: ?>
                                            <span class="text-xs text-stone-400 italic">Sedang Libur</span>
                                        <?php endif; ?>
                                        <span class="w-5 h-5 rounded-full border-2 border-stone-300 flex items-center justify-center th-check">
                                            <span class="w-2.5 h-2.5 rounded-full bg-transparent"></span>
                                        </span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: PILIH TANGGAL & SLOT JAM -->
                <div class="step-panel hidden" id="stepPanel-4">
                    <h2 class="font-serif text-2xl font-bold text-stone-900 mb-2">Pilih Tanggal & Waktu Sesi</h2>
                    <p class="text-stone-500 text-xs sm:text-sm mb-6">Sistem akan secara otomatis memeriksa ketersediaan slot jam dan jadwal terapis.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                        <!-- Date Picker -->
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-2">Tanggal Reservasi <span class="text-rose-500">*</span></label>
                            <input type="date" id="inputScheduleDate" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" onchange="loadTimeSlots()" class="w-full px-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all font-medium">
                        </div>

                        <!-- Time Slots Container -->
                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider">Slot Jam Tersedia <span class="text-rose-500">*</span></label>
                                <span id="slotStatusNotice" class="text-xs text-stone-500 italic">Memuat slot...</span>
                            </div>

                            <div id="timeSlotsGrid" class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                                <!-- Dynamic filled via JS -->
                            </div>
                        </div>
                    </div>

                    <!-- Summary Note -->
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-xs text-emerald-900 flex items-start space-x-3">
                        <i class="fa-solid fa-clock-rotate-left text-base text-emerald-700 mt-0.5"></i>
                        <div>
                            <span class="font-bold">Estimasi Durasi & Persiapan:</span>
                            <p class="mt-0.5 text-stone-600">Durasi pijat disesuaikan dengan pilihan paket layanan Anda. Untuk layanan Home Service, terapis akan tiba sekitar 10-15 menit sebelum jam yang ditentukan untuk persiapan higienis.</p>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: KONTAK PEMESAN & KONFIRMASI -->
                <div class="step-panel hidden" id="stepPanel-5">
                    <h2 class="font-serif text-2xl font-bold text-stone-900 mb-2">Informasi Pemesan & Catatan</h2>
                    <p class="text-stone-500 text-xs sm:text-sm mb-6">Pastikan nomor WhatsApp aktif untuk menerima konfirmasi dan koordinasi kedatangan terapis.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Nama Lengkap Pemesan <span class="text-rose-500">*</span></label>
                            <input type="text" id="inputCustomerName" required value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" placeholder="Contoh: Rian Pratama" class="w-full px-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp Aktif <span class="text-rose-500">*</span></label>
                            <input type="tel" id="inputCustomerPhone" required value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" placeholder="Contoh: 081298765432" class="w-full px-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Alamat Email (Opsional)</label>
                            <input type="email" id="inputCustomerEmail" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" placeholder="nama@email.com" class="w-full px-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Catatan Khusus / Keluhan Tubuh (Opsional)</label>
                            <textarea id="inputNotes" rows="2" placeholder="Misal: Fokus pijat bagian pundak dan pinggang, hindari tekanan terlalu kuat, atau info patokan rumah." class="w-full px-4 py-3 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all"></textarea>
                        </div>
                    </div>

                    <!-- Ringkasan Reservasi Box -->
                    <div class="bg-stone-50 rounded-2xl p-5 border border-stone-200 space-y-3">
                        <h4 class="font-serif text-base font-bold text-stone-900 border-b border-stone-200 pb-2 flex items-center justify-between">
                            <span>Ringkasan Pesanan Anda</span>
                            <span class="text-xs font-sans text-stone-500 font-normal">Cek kembali sebelum kirim</span>
                        </h4>
                        <div class="grid grid-cols-2 gap-3 text-xs sm:text-sm">
                            <div>
                                <span class="text-stone-400 block text-xs">Tipe Reservasi:</span>
                                <span class="font-semibold text-stone-800" id="summaryType">-</span>
                            </div>
                            <div>
                                <span class="text-stone-400 block text-xs">Layanan:</span>
                                <span class="font-semibold text-stone-800" id="summaryService">-</span>
                            </div>
                            <div>
                                <span class="text-stone-400 block text-xs">Jadwal Sesi:</span>
                                <span class="font-semibold text-stone-800" id="summarySchedule">-</span>
                            </div>
                            <div>
                                <span class="text-stone-400 block text-xs">Terapis:</span>
                                <span class="font-semibold text-stone-800" id="summaryTherapist">Otomatis Ditentukan</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-stone-200 flex items-center justify-between">
                            <span class="font-bold text-stone-900">Total Biaya Layanan:</span>
                            <span class="text-xl font-bold text-emerald-800" id="summaryPrice">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- STEP 6: SUKSES / STRUK & DIRECT WHATSAPP ACTION -->
                <div class="step-panel hidden" id="stepPanel-6">
                    <div class="text-center py-6">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-3xl mx-auto mb-4 animate-bounce">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h2 class="font-serif text-3xl font-bold text-stone-900">Reservasi Berhasil Diajukan!</h2>
                        <p class="text-stone-600 text-sm mt-2 max-w-md mx-auto">
                            Kode reservasi unik Anda telah berhasil diterbitkan. Silakan klik tombol di bawah untuk konfirmasi instan dengan Admin via WhatsApp.
                        </p>

                        <!-- Kode Booking Display -->
                        <div class="my-6 inline-flex items-center space-x-3 bg-stone-100 px-5 py-3 rounded-2xl border border-stone-200">
                            <span class="text-xs text-stone-500 font-semibold uppercase">Kode Booking:</span>
                            <span class="font-mono text-xl font-bold text-brand-900 tracking-wider" id="successBookingCode">BKG-XXXX</span>
                            <button type="button" onclick="copyBookingCode()" class="text-stone-500 hover:text-stone-900 p-1 rounded-lg" title="Salin Kode">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </div>

                        <!-- Direct WhatsApp Action Button -->
                        <div class="max-w-md mx-auto space-y-3">
                            <a id="successWaBtn" href="#" target="_blank" class="w-full py-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-base rounded-2xl shadow-xl shadow-emerald-900/20 transition-all transform hover:scale-[1.02] flex items-center justify-center space-x-3">
                                <i class="fa-brands fa-whatsapp text-2xl"></i>
                                <span>Kirim Konfirmasi ke WhatsApp Admin</span>
                            </a>

                            <div class="flex items-center justify-center space-x-4 pt-4 text-xs font-semibold text-stone-600">
                                <button type="button" onclick="openCheckBookingWithCode()" class="hover:text-emerald-800">
                                    <i class="fa-solid fa-receipt mr-1"></i> Lihat Status Pemesanan
                                </button>
                                <span>•</span>
                                <a href="index.php" class="hover:text-emerald-800">
                                    <i class="fa-solid fa-house mr-1"></i> Kembali ke Beranda
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Action Buttons (Next / Prev) -->
                <div class="mt-10 pt-6 border-t border-stone-100 flex items-center justify-between" id="wizardNavButtons">
                    <button type="button" id="prevBtn" onclick="navigateStep(-1)" class="px-6 py-3 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-100 font-semibold text-sm transition-all hidden">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Sebelumnya
                    </button>

                    <div class="ml-auto">
                        <button type="button" id="nextBtn" onclick="navigateStep(1)" class="px-8 py-3.5 bg-brand-800 hover:bg-brand-900 text-white font-semibold text-sm rounded-xl shadow-md shadow-brand-900/20 transition-all flex items-center space-x-2">
                            <span>Lanjutkan</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>

                        <button type="submit" id="submitBookingBtn" class="px-8 py-3.5 bg-brand-800 hover:bg-brand-900 text-white font-semibold text-sm rounded-xl shadow-md shadow-brand-900/20 transition-all hidden flex items-center space-x-2">
                            <span>Ajukan Reservasi</span>
                            <i class="fa-solid fa-calendar-check text-xs"></i>
                        </button>
                    </div>
                </div>

            </form>

        </div>

    </div>
</div>

<script>
    // State Wizard
    let currentStep = 1;
    const totalSteps = 5;
    let selectedTimeSlot = '';
    let latestCreatedBookingCode = '';

    // Inisialisasi saat DOM siap
    document.addEventListener('DOMContentLoaded', () => {
        handleTypeChange();
        initServiceSelection();
        initTherapistSelection();
        loadTimeSlots();
    });

    // 1. Step 1: Type change handler (100% Home Service)
    function handleTypeChange() {
        filterServicesByType('home_service');
    }

    // Filter layanan sesuai tipe booking
    function filterServicesByType(bookingType) {
        const cards = document.querySelectorAll('.service-option-card');
        let hasSelectedVisible = false;

        cards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            // Seluruh layanan tersedia untuk Home Service
            card.classList.remove('hidden');
            if (radio && radio.checked) hasSelectedVisible = true;
        });

        // Jika belum ada layanan yang terpilih, pilih yang pertama kelihatan
        if (!hasSelectedVisible) {
            const firstVisible = Array.from(cards).find(c => !c.classList.contains('hidden'));
            if (firstVisible) {
                firstVisible.querySelector('input[type="radio"]').checked = true;
                highlightSelectedService();
            }
        }
    }

    // Inisialisasi Service Selection Card
    function initServiceSelection() {
        const cards = document.querySelectorAll('.service-option-card');
        cards.forEach(card => {
            card.addEventListener('click', () => {
                cards.forEach(c => {
                    c.classList.remove('border-emerald-600', 'bg-emerald-50/50');
                    c.classList.add('border-stone-200', 'bg-white');
                    c.querySelector('.srv-check span').className = 'w-2.5 h-2.5 rounded-full bg-transparent';
                });
                card.classList.add('border-emerald-600', 'bg-emerald-50/50');
                card.classList.remove('border-stone-200', 'bg-white');
                card.querySelector('.srv-check span').className = 'w-2.5 h-2.5 rounded-full bg-emerald-700';
                card.querySelector('input[type="radio"]').checked = true;

                loadTimeSlots();
            });
        });
        highlightSelectedService();
    }

    function highlightSelectedService() {
        const checked = document.querySelector('input[name="selected_service"]:checked');
        if (checked) {
            const card = checked.closest('.service-option-card');
            if (card) {
                card.classList.add('border-emerald-600', 'bg-emerald-50/50');
                card.querySelector('.srv-check span').className = 'w-2.5 h-2.5 rounded-full bg-emerald-700';
            }
        }
    }

    // Step 3: Gender & Therapist selection
    function handleGenderFilter() {
        const gender = document.querySelector('input[name="gender_pref"]:checked').value;
        const btnAny = document.getElementById('genderAny');
        const btnFemale = document.getElementById('genderFemale');
        const btnMale = document.getElementById('genderMale');

        [btnAny, btnFemale, btnMale].forEach(b => {
            b.className = 'gender-btn flex items-center justify-center p-3 rounded-xl border-2 border-stone-200 bg-white text-stone-700 cursor-pointer text-xs font-bold';
        });

        if (gender === 'any') btnAny.className = 'gender-btn flex items-center justify-center p-3 rounded-xl border-2 border-emerald-700 bg-emerald-50 text-emerald-900 cursor-pointer text-xs font-bold';
        if (gender === 'female') btnFemale.className = 'gender-btn flex items-center justify-center p-3 rounded-xl border-2 border-pink-700 bg-pink-50 text-pink-900 cursor-pointer text-xs font-bold';
        if (gender === 'male') btnMale.className = 'gender-btn flex items-center justify-center p-3 rounded-xl border-2 border-blue-700 bg-blue-50 text-blue-900 cursor-pointer text-xs font-bold';

        // Filter individual therapist cards
        const thCards = document.querySelectorAll('.therapist-radio-card[data-gender]');
        thCards.forEach(c => {
            const thGender = c.dataset.gender;
            if (gender === 'any' || thGender === gender) {
                c.classList.remove('hidden');
            } else {
                c.classList.add('hidden');
            }
        });

        loadTimeSlots();
    }

    function initTherapistSelection() {
        const cards = document.querySelectorAll('.therapist-radio-card');
        cards.forEach(card => {
            card.addEventListener('click', (e) => {
                if (card.classList.contains('pointer-events-none')) return;
                cards.forEach(c => {
                    c.classList.remove('border-emerald-700', 'bg-emerald-50/60');
                    c.classList.add('border-stone-200', 'bg-white');
                    const thSpan = c.querySelector('.th-check span');
                    if (thSpan) thSpan.className = 'w-2.5 h-2.5 rounded-full bg-transparent';
                });
                card.classList.add('border-emerald-700', 'bg-emerald-50/60');
                card.classList.remove('border-stone-200', 'bg-white');
                const activeSpan = card.querySelector('.th-check span');
                if (activeSpan) activeSpan.className = 'w-2.5 h-2.5 rounded-full bg-emerald-700';

                const radio = card.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;

                loadTimeSlots();
            });
        });
    }

    // Step 4: Dynamic Time Slot Availability Checker
    async function loadTimeSlots() {
        const dateInput = document.getElementById('inputScheduleDate');
        const date = dateInput ? dateInput.value : '';
        const serviceInput = document.querySelector('input[name="selected_service"]:checked');
        const therapistInput = document.querySelector('input[name="selected_therapist"]:checked');
        const genderPref = document.querySelector('input[name="gender_pref"]:checked')?.value || 'any';

        const serviceId = serviceInput ? serviceInput.value : 1;
        const therapistId = therapistInput ? therapistInput.value : '';

        const grid = document.getElementById('timeSlotsGrid');
        const notice = document.getElementById('slotStatusNotice');

        if (!grid) return;
        if (notice) notice.textContent = 'Memeriksa slot...';
        grid.innerHTML = '<div class="col-span-4 text-center py-4 text-stone-400 text-xs"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Memuat jadwal...</div>';

        try {
            const queryUrl = `api/booking.php?action=check_slots&date=${encodeURIComponent(date)}&service_id=${serviceId}&therapist_id=${therapistId}&gender_preference=${genderPref}`;
            const res = await fetch(queryUrl);
            const json = await res.json();

            if (json.success && json.slots) {
                grid.innerHTML = '';
                const availableCount = json.slots.filter(s => s.available).length;
                if (notice) notice.textContent = `${availableCount} Slot Jam Tersedia`;

                json.slots.forEach(slot => {
                    const btn = document.createElement('button');
                    btn.type = 'button';

                    if (slot.available) {
                        const isSelected = (selectedTimeSlot === slot.time);
                        btn.className = `p-3 rounded-xl border text-xs font-bold transition-all ${
                            isSelected 
                            ? 'border-emerald-700 bg-brand-800 text-white shadow-md' 
                            : 'border-stone-200 bg-white hover:border-emerald-600 hover:bg-emerald-50 text-stone-800'
                        }`;
                        btn.innerHTML = `<span>${slot.time} WIB</span>`;
                        btn.onclick = () => selectTimeSlot(slot.time, btn);
                    } else {
                        btn.className = 'p-3 rounded-xl border border-stone-200 bg-stone-100 text-stone-400 text-xs font-medium cursor-not-allowed';
                        btn.disabled = true;
                        btn.innerHTML = `<span>${slot.time}</span><span class="block text-[9px] text-stone-400 mt-0.5">${slot.reason || 'Penuh'}</span>`;
                    }
                    grid.appendChild(btn);
                });
            } else {
                if (notice) notice.textContent = 'Gagal memuat jadwal';
            }
        } catch (e) {
            if (notice) notice.textContent = 'Error koneksi jadwal';
        }
    }

    function selectTimeSlot(time, btnEl) {
        selectedTimeSlot = time;
        const allBtns = document.getElementById('timeSlotsGrid')?.querySelectorAll('button:not([disabled])') || [];
        allBtns.forEach(b => {
            b.className = 'p-3 rounded-xl border border-stone-200 bg-white hover:border-emerald-600 hover:bg-emerald-50 text-stone-800 text-xs font-bold transition-all';
        });
        btnEl.className = 'p-3 rounded-xl border border-emerald-700 bg-brand-800 text-white shadow-md text-xs font-bold transition-all';
    }

    // Step Navigation Logic
    function navigateStep(direction) {
        // Validasi sebelum maju ke langkah berikutnya
        if (direction === 1) {
            if (currentStep === 1) {
                const addr = document.getElementById('inputAddress').value.trim();
                if (!addr) {
                    showToast('Silakan masukkan alamat lengkap untuk Home Service.', 'warning');
                    document.getElementById('inputAddress').focus();
                    return;
                }
            } else if (currentStep === 2) {
                const srv = document.querySelector('input[name="selected_service"]:checked');
                if (!srv) {
                    showToast('Silakan pilih salah satu menu layanan pijat.', 'warning');
                    return;
                }
            } else if (currentStep === 4) {
                if (!selectedTimeSlot) {
                    showToast('Silakan klik salah satu slot jam yang tersedia.', 'warning');
                    return;
                }
            }
        }

        const nextStep = currentStep + direction;
        if (nextStep < 1 || nextStep > totalSteps) return;

        // Sembunyikan panel lama dan tampilkan panel baru
        const currentPanel = document.getElementById(`stepPanel-${currentStep}`);
        const nextPanel = document.getElementById(`stepPanel-${nextStep}`);
        if (currentPanel) currentPanel.classList.add('hidden');
        if (nextPanel) nextPanel.classList.remove('hidden');

        // Update state step
        currentStep = nextStep;

        // Update Navigasi Tombol
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBookingBtn');

        if (currentStep === 1) {
            if (prevBtn) prevBtn.classList.add('hidden');
        } else {
            if (prevBtn) prevBtn.classList.remove('hidden');
        }

        if (currentStep === totalSteps) {
            if (nextBtn) nextBtn.classList.add('hidden');
            if (submitBtn) submitBtn.classList.remove('hidden');
            populateSummary();
        } else {
            if (nextBtn) nextBtn.classList.remove('hidden');
            if (submitBtn) submitBtn.classList.add('hidden');
        }

        // Jika memasuki Step 4, perbarui slot jam secara langsung
        if (currentStep === 4) {
            loadTimeSlots();
        }

        // Update Step Indicators (dengan proteksi null-safe)
        try {
            updateStepIndicators(currentStep);
        } catch (err) {
            console.error('Indicator update error:', err);
        }

        window.scrollTo({
            top: 180,
            behavior: 'smooth'
        });
    }

    function updateStepIndicators(step) {
        for (let i = 1; i <= totalSteps; i++) {
            const ind = document.getElementById(`ind-${i}`);
            if (!ind) continue;

            const circle = ind.querySelector('.ind-circle') || ind.firstElementChild;
            const text = ind.querySelector('.ind-text') || ind.lastElementChild;
            const line = document.getElementById(`line-${i}`);

            if (circle) {
                if (i < step) {
                    // Completed
                    circle.className = 'w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold ind-circle';
                    circle.innerHTML = '<i class="fa-solid fa-check"></i>';
                } else if (i === step) {
                    // Active
                    circle.className = 'w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs font-bold ring-4 ring-brand-900/50 ind-circle';
                    circle.innerHTML = i;
                } else {
                    // Upcoming
                    circle.className = 'w-8 h-8 rounded-full bg-stone-800 text-stone-400 flex items-center justify-center text-xs font-bold ind-circle';
                    circle.innerHTML = i;
                }
            }

            if (text) {
                if (i < step) {
                    text.className = 'text-xs font-medium text-emerald-400 hidden sm:inline ind-text';
                } else if (i === step) {
                    text.className = 'text-xs font-bold text-white hidden sm:inline ind-text';
                } else {
                    text.className = 'text-xs font-medium text-stone-400 hidden sm:inline ind-text';
                }
            }

            if (line) {
                if (i < step) {
                    line.className = 'w-8 sm:w-12 h-0.5 bg-emerald-600';
                } else {
                    line.className = 'w-8 sm:w-12 h-0.5 bg-stone-700';
                }
            }
        }
    }

    // Mengisi Kotak Ringkasan pada Langkah 5
    function populateSummary() {
        const srvRadio = document.querySelector('input[name="selected_service"]:checked');
        const srvCard = srvRadio ? srvRadio.closest('.service-option-card') : null;

        const thRadio = document.querySelector('input[name="selected_therapist"]:checked');
        const thCard = thRadio ? thRadio.closest('.therapist-radio-card') : null;

        const date = document.getElementById('inputScheduleDate').value;
        const time = selectedTimeSlot;

        document.getElementById('summaryType').textContent = 'Home Service (Panggilan ke Alamat)';

        if (srvCard) {
            document.getElementById('summaryService').textContent = `${srvCard.dataset.name} (${srvCard.dataset.duration} Menit)`;
            const price = parseFloat(srvCard.dataset.price);
            document.getElementById('summaryPrice').textContent = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(price);
        }

        document.getElementById('summarySchedule').textContent = `${date} pada jam ${time} WIB`;

        if (thRadio && thRadio.value !== '') {
            const thName = thCard.querySelector('h4, span.font-bold').textContent;
            document.getElementById('summaryTherapist').textContent = thName;
        } else {
            const gender = document.querySelector('input[name="gender_pref"]:checked').value;
            const genderLabel = (gender === 'female') ? 'Terapis Wanita' : ((gender === 'male') ? 'Terapis Pria' : 'Bebas');
            document.getElementById('summaryTherapist').textContent = `Otomatis Ditentukan (${genderLabel})`;
        }
    }

    // Submit Form Reservasi
    async function handleWizardSubmit(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBookingBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Mengirim Reservasi...';

        const bookingType = 'home_service';
        const address = document.getElementById('inputAddress').value.trim();
        const serviceId = document.querySelector('input[name="selected_service"]:checked').value;
        const therapistId = document.querySelector('input[name="selected_therapist"]:checked')?.value || null;
        const genderPref = document.querySelector('input[name="gender_pref"]:checked')?.value || 'any';

        const scheduleDate = document.getElementById('inputScheduleDate').value;
        const scheduleTime = selectedTimeSlot;

        const customerName = document.getElementById('inputCustomerName').value.trim();
        const customerPhone = document.getElementById('inputCustomerPhone').value.trim();
        const customerEmail = document.getElementById('inputCustomerEmail').value.trim();
        const notes = document.getElementById('inputNotes').value.trim();

        if (!customerName || !customerPhone) {
            showToast('Nama lengkap dan nomor WhatsApp wajib diisi.', 'warning');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }

        const payload = {
            booking_type: bookingType,
            address: address,
            service_id: parseInt(serviceId),
            therapist_id: therapistId ? parseInt(therapistId) : null,
            therapist_preference_gender: genderPref,
            schedule_date: scheduleDate,
            schedule_time: scheduleTime,
            customer_name: customerName,
            customer_phone: customerPhone,
            customer_email: customerEmail,
            notes: notes
        };

        try {
            const res = await fetch('api/booking.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success && data.data) {
                showToast(data.message, 'success');
                latestCreatedBookingCode = data.data.booking_code;

                // Tampilkan Layar Sukses (Step 6)
                document.getElementById(`stepPanel-${currentStep}`).classList.add('hidden');
                document.getElementById('stepPanel-6').classList.remove('hidden');
                document.getElementById('wizardNavButtons').classList.add('hidden');

                document.getElementById('successBookingCode').textContent = data.data.booking_code;
                document.getElementById('successWaBtn').href = data.data.whatsapp_url;

                // Scroll to top card
                window.scrollTo({
                    top: 150,
                    behavior: 'smooth'
                });
            } else {
                showToast(data.message || 'Gagal membuat reservasi.', 'error');
            }
        } catch (err) {
            showToast('Terjadi kesalahan komunikasi dengan server.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    function copyBookingCode() {
        if (!latestCreatedBookingCode) return;
        navigator.clipboard.writeText(latestCreatedBookingCode);
        showToast('Kode booking berhasil disalin!', 'success');
    }

    function openCheckBookingWithCode() {
        if (!latestCreatedBookingCode) return;
        openCheckBookingModal();
        document.getElementById('checkBookingCode').value = latestCreatedBookingCode;
        submitCheckBooking(new Event('submit'));
    }
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>