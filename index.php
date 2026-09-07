<?php
// ==========================================================
// Landing Page Publik: Sentosa Wellness & Massage Spa
// ==========================================================
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Sentosa Spa - Layanan Pijat Tradisional & Modern Home Service';
require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/navbar.php';

$db = Database::getConnection();

// Ambil Layanan Aktif untuk Initial Render
$servicesStmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY price ASC");
$services = $servicesStmt->fetchAll();

// Ambil Terapis untuk Showcase
$therapistsStmt = $db->query("SELECT t.*, u.name, u.phone, COUNT(r.id) as total_reviews 
                              FROM therapists t 
                              JOIN users u ON t.user_id = u.id 
                              LEFT JOIN reviews r ON t.id = r.therapist_id
                              GROUP BY t.id, t.user_id, t.specialization, t.gender, t.is_available, t.rating, t.created_at, u.name, u.phone
                              ORDER BY t.is_available DESC, t.rating DESC");
$therapists = $therapistsStmt->fetchAll();
?>

<!-- 1. HERO SECTION -->
<section id="home" class="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-stone-50 to-white pt-12 pb-20 lg:pt-20 lg:pb-32">
    <!-- Decorative background elements -->
    <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-emerald-100/60 blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 left-0 -ml-20 w-80 h-80 rounded-full bg-amber-100/50 blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            <!-- Left Text Content -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-emerald-100/80 text-emerald-900 border border-emerald-200/60 text-xs font-semibold tracking-wide">
                    <i class="fa-solid fa-sparkles text-emerald-600"></i>
                    <span>Solusi Relaksasi Holistik Tubuh & Pikiran</span>
                </div>

                <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold text-stone-900 leading-[1.15] tracking-tight">
                    Sentuhan Alami untuk <br class="hidden sm:inline">
                    <span class="text-emerald-800 italic font-normal">Kebugaran Paripurna</span> Anda
                </h1>

                <p class="text-stone-600 text-base sm:text-lg leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Layanan pijat dan spa panggilan profesional berstandar bintang lima. Nikmati kenyamanan terapi relaksasi langsung di kediaman Anda, apartemen, atau hotel tanpa perlu repot keluar rumah (100% Home Service).
                </p>

                <!-- CTA Button Group -->
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="booking.php" class="w-full sm:w-auto px-8 py-4 bg-brand-800 hover:bg-brand-900 text-white font-semibold rounded-2xl shadow-xl shadow-brand-900/20 hover:shadow-2xl transition-all duration-200 transform hover:-translate-y-0.5 flex items-center justify-center space-x-3">
                        <i class="fa-solid fa-calendar-check text-emerald-300"></i>
                        <span>Booking Jadwal Sekarang</span>
                    </a>
                    <a href="#services" class="w-full sm:w-auto px-7 py-4 bg-white hover:bg-stone-100 text-stone-700 font-semibold rounded-2xl border border-stone-200 shadow-sm transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-list-ul text-stone-500"></i>
                        <span>Lihat Menu Layanan</span>
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="pt-6 border-t border-stone-200/80 grid grid-cols-3 gap-4 text-center lg:text-left">
                    <div>
                        <div class="text-2xl sm:text-3xl font-bold text-stone-900 font-serif">4.9/5</div>
                        <div class="text-xs text-stone-500 mt-0.5"><i class="fa-solid fa-star text-amber-500 mr-1"></i>Rating Kepuasan</div>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-bold text-stone-900 font-serif">100%</div>
                        <div class="text-xs text-stone-500 mt-0.5"><i class="fa-solid fa-certificate text-emerald-700 mr-1"></i>Terapis Bersertifikat</div>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-bold text-stone-900 font-serif">15.000+</div>
                        <div class="text-xs text-stone-500 mt-0.5"><i class="fa-solid fa-heart text-rose-500 mr-1"></i>Sesi Terlayani</div>
                    </div>
                </div>
            </div>

            <!-- Right Visual Image & Badges -->
            <div class="lg:col-span-5 relative">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-white aspect-[4/5] object-cover">
                        <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=1000&auto=format&fit=crop&q=80"
                            alt="Luxury Spa Treatment"
                            class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950/60 via-transparent to-transparent"></div>
                    </div>

                    <!-- Floating Card 1: Home Service -->
                    <div class="absolute -bottom-6 -left-6 sm:bottom-6 sm:-left-8 bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-stone-100 flex items-center space-x-3.5 max-w-xs">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-house-chimney-medical"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-stone-900">Home Service Siap Panggil</span>
                            <span class="block text-[11px] text-stone-500">Terapis bawa matras, aroma, & handuk steril</span>
                        </div>
                    </div>

                    <!-- Floating Card 2: Siap Panggil -->
                    <div class="absolute -top-6 -right-4 bg-white/95 backdrop-blur-md px-4 py-2.5 rounded-2xl shadow-lg border border-stone-100 flex items-center space-x-2 text-xs font-semibold text-emerald-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Terapis Siap Panggil Hari Ini</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 2. CARA KERJA (HOW IT WORKS) -->
<section class="py-16 bg-white border-y border-stone-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-widest text-emerald-700">Mudah & Cepat</span>
            <h2 class="font-serif text-3xl font-bold text-stone-900 mt-2">Reservasi Dalam 3 Langkah Sederhana</h2>
            <p class="text-stone-600 text-sm mt-2">Pesan relaksasi idaman tanpa menunggu lama atau repot keluar rumah.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Step 1 -->
            <div class="relative p-6 rounded-3xl bg-stone-50 border border-stone-200/60 hover:shadow-lg transition-all text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl mb-4 font-serif font-bold">
                    1
                </div>
                <h3 class="text-lg font-bold text-stone-900 mb-2">Tentukan Lokasi & Layanan</h3>
                <p class="text-stone-600 text-sm leading-relaxed">
                    Tentukan alamat kunjungan Anda (Rumah / Apartemen / Hotel), pilih menu pijat favorit, preferensi terapis, dan tanggal booking.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="relative p-6 rounded-3xl bg-stone-50 border border-stone-200/60 hover:shadow-lg transition-all text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-2xl mb-4 font-serif font-bold">
                    2
                </div>
                <h3 class="text-lg font-bold text-stone-900 mb-2">Konfirmasi WhatsApp Instan</h3>
                <p class="text-stone-600 text-sm leading-relaxed">
                    Sistem langsung membuat rincian resmi dan menghubungkan Anda dengan admin via WhatsApp untuk validasi jadwal terapis.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="relative p-6 rounded-3xl bg-stone-50 border border-stone-200/60 hover:shadow-lg transition-all text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl mb-4 font-serif font-bold">
                    3
                </div>
                <h3 class="text-lg font-bold text-stone-900 mb-2">Nikmati Terapi Pijat</h3>
                <p class="text-stone-600 text-sm leading-relaxed">
                    Terapis profesional kami tiba tepat waktu di lokasi Anda membawa peralatan lengkap, matras steril, dan aromaterapi siap pakai.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 3. MENU LAYANAN (SERVICES CATALOG) -->
<section id="services" class="py-20 bg-stone-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-700">Katalog Perawatan</span>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900 mt-2">Menu Layanan Eksklusif</h2>
                <p class="text-stone-600 text-sm sm:text-base mt-2 max-w-xl">
                    Kombinasi terapi tradisional warisan leluhur dan teknik modern untuk melenyapkan pegal, stres, serta ketegangan urat saraf.
                </p>
            </div>

            <!-- Home Service Exclusive Indicator -->
            <div class="mt-6 md:mt-0 inline-flex items-center space-x-2 px-4 py-2.5 rounded-2xl bg-emerald-100/80 text-emerald-900 border border-emerald-200/70 text-xs font-bold self-start">
                <i class="fa-solid fa-house-chimney-medical text-emerald-700"></i>
                <span>100% Home Service (Panggilan ke Tempat Anda)</span>
            </div>
        </div>

        <!-- Services Grid -->
        <div id="servicesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($services as $srv): ?>
                <div class="service-card group bg-white rounded-3xl overflow-hidden border border-stone-200/80 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col"
                    data-type="<?= $srv['type'] ?>">

                    <!-- Image with badges -->
                    <div class="relative h-56 overflow-hidden bg-stone-100">
                        <img src="<?= htmlspecialchars($srv['image_url'] ?: 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800') ?>"
                            alt="<?= htmlspecialchars($srv['name']) ?>"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                        <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                            <!-- Duration Badge -->
                            <span class="px-3 py-1 rounded-full bg-stone-900/80 backdrop-blur-md text-white text-xs font-semibold flex items-center space-x-1">
                                <i class="fa-regular fa-clock text-emerald-400"></i>
                                <span><?= $srv['duration_minutes'] ?> Menit</span>
                            </span>

                            <!-- Service Type Badge -->
                            <span class="px-3 py-1 rounded-full bg-emerald-900/80 backdrop-blur-md text-emerald-200 text-xs font-semibold flex items-center space-x-1">
                                <i class="fa-solid fa-house-chimney mr-1"></i>
                                <span>Home Service</span>
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-serif text-xl font-bold text-stone-900 group-hover:text-emerald-800 transition-colors">
                                <?= htmlspecialchars($srv['name']) ?>
                            </h3>
                            <p class="text-stone-600 text-xs sm:text-sm mt-2 line-clamp-3 leading-relaxed">
                                <?= htmlspecialchars($srv['description']) ?>
                            </p>
                        </div>

                        <div class="pt-6 mt-6 border-t border-stone-100 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] uppercase tracking-wider text-stone-500 font-semibold block">Tarif Layanan</span>
                                <span class="text-xl font-bold text-stone-900"><?= formatRupiah($srv['price']) ?></span>
                            </div>
                            <a href="booking.php?service_id=<?= $srv['id'] ?>" class="px-4 py-2.5 bg-brand-800 hover:bg-brand-900 text-white text-xs font-semibold rounded-xl shadow-md shadow-brand-900/10 transition-all flex items-center space-x-1.5">
                                <span>Reservasi</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- 4. DAFTAR TERAPIS (THERAPISTS SHOWCASE) -->
<section id="therapists" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-widest text-emerald-700">Tenaga Ahli Berlisensi</span>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900 mt-2">Tim Terapis Profesional Kami</h2>
            <p class="text-stone-600 text-sm mt-2">
                Setiap terapis telah melalui seleksi ketat, verifikasi identitas resmi, pelatihan anatomi otot, serta uji higienitas berkala.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($therapists as $t): ?>
                <div class="p-6 rounded-3xl bg-stone-50 border border-stone-200 hover:border-emerald-700 hover:shadow-xl transition-all text-center flex flex-col items-center">

                    <!-- Avatar with Gender Badge -->
                    <div class="relative mb-4">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-brand-800 to-emerald-600 text-white flex items-center justify-center text-3xl font-serif font-bold shadow-md">
                            <?= substr($t['name'], 0, 1) ?>
                        </div>
                        <span class="absolute bottom-0 right-0 w-7 h-7 rounded-full flex items-center justify-center text-xs shadow-md <?= ($t['gender'] === 'female') ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' ?>" title="Gender: <?= ($t['gender'] === 'female') ? 'Wanita' : 'Pria' ?>">
                            <i class="fa-solid <?= ($t['gender'] === 'female') ? 'fa-venus' : 'fa-mars' ?>"></i>
                        </span>
                    </div>

                    <h3 class="font-bold text-stone-900 text-base"><?= htmlspecialchars($t['name']) ?></h3>
                    <p class="text-xs text-stone-500 font-medium mt-0.5">Terapis <?= ($t['gender'] === 'female') ? 'Wanita' : 'Pria' ?></p>

                    <div class="my-3 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold flex items-center space-x-1.5">
                        <i class="fa-solid fa-star text-amber-500"></i>
                        <span><?= number_format((float)$t['rating'], 1) ?></span>
                        <span class="text-stone-400 font-normal text-[11px]">(<?= (int)$t['total_reviews'] ?> ulasan)</span>
                    </div>

                    <p class="text-stone-600 text-xs text-center line-clamp-2 leading-relaxed mb-4">
                        <?= htmlspecialchars($t['specialization']) ?>
                    </p>

                    <!-- Availability Status -->
                    <div class="mt-auto w-full pt-3 border-t border-stone-200/60 flex items-center justify-between text-xs">
                        <span class="flex items-center space-x-1.5 <?= ($t['is_available'] == 1) ? 'text-emerald-700 font-semibold' : 'text-stone-400' ?>">
                            <span class="w-2 h-2 rounded-full <?= ($t['is_available'] == 1) ? 'bg-emerald-500 animate-pulse' : 'bg-stone-300' ?>"></span>
                            <span><?= ($t['is_available'] == 1) ? 'Tersedia' : 'Sedang Libur' ?></span>
                        </span>

                        <?php if ($t['is_available'] == 1): ?>
                            <a href="booking.php?therapist_id=<?= $t['id'] ?>" class="text-emerald-800 hover:text-emerald-950 font-bold">
                                Pilih <i class="fa-solid fa-angle-right text-[10px]"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- 5. KEUNGGULAN (FEATURES) -->
<section id="features" class="py-20 bg-stone-900 text-stone-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-400">Kenapa Memilih Kami?</span>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold text-white mt-2 leading-tight">
                    Komitmen Nyata untuk Kenyamanan, Keamanan & Privasi Anda
                </h2>
                <p class="text-stone-400 text-sm sm:text-base mt-4 leading-relaxed">
                    Kami memahami pentingnya rasa aman saat menerima terapis di hunian pribadi Anda maupun saat berkunjung ke spa kami.
                </p>

                <div class="mt-8 space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-900/60 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-base">Verifikasi Keamanan Berlapis</h4>
                            <p class="text-stone-400 text-xs sm:text-sm mt-1">
                                Seluruh terapis terikat kontrak hukum, terverifikasi KTP/SKCK, serta memakai seragam dan ID resmi.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-900/60 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-pump-soap"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-base">Perlengkapan Steril & Bebas Ribet</h4>
                            <p class="text-stone-400 text-xs sm:text-sm mt-1">
                                Untuk Home Service, terapis membawa matras lipat higienis, sprei sekali pakai, kain penutup steril, dan minyak aromaterapi esensial murni.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-900/60 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-base">Transparansi Harga Tanpa Biaya Tersembunyi</h4>
                            <p class="text-stone-400 text-xs sm:text-sm mt-1">
                                Bebas biaya transportasi terapis untuk area Jakarta Selatan dan sekitarnya. Biaya yang tertera adalah biaya final.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-stone-800">
                    <img src="https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=1000&auto=format&fit=crop&q=80"
                        alt="Spa Ambient"
                        class="w-full h-auto object-cover">
                </div>
            </div>
        </div>

    </div>
</section>

<!-- 6. FAQ (FREQUENTLY ASKED QUESTIONS) -->
<section id="faq" class="py-20 bg-stone-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12">
            <span class="text-xs font-bold uppercase tracking-widest text-emerald-700">Informasi Penting</span>
            <h2 class="font-serif text-3xl font-bold text-stone-900 mt-2">Pertanyaan yang Sering Diajukan (FAQ)</h2>
        </div>

        <div class="space-y-4">
            <!-- FAQ 1 -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-5 shadow-sm">
                <h4 class="font-bold text-stone-900 text-sm sm:text-base flex items-center justify-between cursor-pointer" onclick="toggleFaq(this)">
                    <span>Apakah terapis Home Service membawa perlengkapan sendiri?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-stone-400 transition-transform"></i>
                </h4>
                <p class="text-stone-600 text-xs sm:text-sm mt-3 leading-relaxed hidden">
                    Ya, terapis kami membawa perlengkapan lengkap meliputi matras busa portabel bersih, sprei/alas higienis sekali pakai (disposable), handuk bersih, pilihan minyak aroma terapi esensial, dan aromaterapi herbal. Anda cukup menyediakan ruangan yang nyaman.
                </p>
            </div>

            <!-- FAQ 2 -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-5 shadow-sm">
                <h4 class="font-bold text-stone-900 text-sm sm:text-base flex items-center justify-between cursor-pointer" onclick="toggleFaq(this)">
                    <span>Berapa lama sebelum jadwal saya harus melakukan reservasi?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-stone-400 transition-transform"></i>
                </h4>
                <p class="text-stone-600 text-xs sm:text-sm mt-3 leading-relaxed hidden">
                    Kami menyarankan untuk melakukan reservasi minimal 2 jam sebelumnya untuk memastikan kesiapan jadwal dan mobilitas terapis ke lokasi Anda. Anda juga dapat memesan untuk hari-hari berikutnya.
                </p>
            </div>

            <!-- FAQ 3 -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-5 shadow-sm">
                <h4 class="font-bold text-stone-900 text-sm sm:text-base flex items-center justify-between cursor-pointer" onclick="toggleFaq(this)">
                    <span>Apakah saya bisa memilih gender terapis (Pria / Wanita)?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-stone-400 transition-transform"></i>
                </h4>
                <p class="text-stone-600 text-xs sm:text-sm mt-3 leading-relaxed hidden">
                    Tentu saja! Pada form booking, Anda memiliki kebebasan penuh untuk memilih preferensi terapis Wanita, Pria, atau Bebas, serta dapat memilih terapis spesifik dari daftar yang tersedia.
                </p>
            </div>

            <!-- FAQ 4 -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-5 shadow-sm">
                <h4 class="font-bold text-stone-900 text-sm sm:text-base flex items-center justify-between cursor-pointer" onclick="toggleFaq(this)">
                    <span>Bagaimana cara pembayaran dan konfirmasinya?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-stone-400 transition-transform"></i>
                </h4>
                <p class="text-stone-600 text-xs sm:text-sm mt-3 leading-relaxed hidden">
                    Setelah mengisi form pemesanan, Anda akan menerima Kode Booking dan tautan instan ke WhatsApp Admin kami. Pembayaran dapat dilakukan via transfer bank, QRIS, atau tunai (cash) langsung setelah sesi pemijatan selesai.
                </p>
            </div>
        </div>

    </div>
</section>

<!-- 7. CTA BANNER -->
<section class="py-16 bg-gradient-to-r from-brand-900 via-emerald-800 to-brand-950 text-white relative overflow-hidden">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 space-y-6">
        <h2 class="font-serif text-3xl sm:text-4xl font-bold tracking-tight">
            Lepaskan Semua Beban & Penat Hari Ini
        </h2>
        <p class="text-emerald-100 text-sm sm:text-base max-w-xl mx-auto">
            Jadwalkan sesi pemijatan Anda sekarang dalam hitungan detik. Rasakan kebugaran tubuh optimal bersama tim terapis terpercaya Sentosa Spa.
        </p>
        <div class="pt-2">
            <a href="booking.php" class="inline-flex items-center space-x-2.5 px-8 py-4 bg-white hover:bg-stone-100 text-brand-900 font-bold rounded-2xl shadow-xl transition-all transform hover:scale-105">
                <i class="fa-solid fa-calendar-plus text-emerald-700 text-lg"></i>
                <span>Reservasi Sekarang</span>
            </a>
        </div>
    </div>
</section>

<script>
    // Toggle FAQ Accordion
    function toggleFaq(el) {
        const p = el.nextElementSibling;
        const icon = el.querySelector('i');
        if (p.classList.contains('hidden')) {
            p.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            p.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>