<?php
// ==========================================================
// Views Partial: Navbar
// ==========================================================
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/database.php';
}
startSession();

$currentUser = getCurrentUser();
$isAdmin = ($currentUser && $currentUser['role'] === 'admin');
$isTherapist = ($currentUser && $currentUser['role'] === 'therapist');

/** @var string|null $baseUrl */
$baseUrl = isset($baseUrl) ? $baseUrl : (function_exists('getBaseUrl') ? getBaseUrl() : '');
$navRoot = !empty($baseUrl) ? $baseUrl : '.';
?>
<header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-stone-200/80 transition-all duration-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Brand Logo -->
            <a href="<?= $navRoot ?>/index.php" class="flex items-center space-x-3 group">
                <div class="w-11 h-11 rounded-2xl bg-brand-800 text-white flex items-center justify-center shadow-md shadow-brand-900/10 group-hover:bg-brand-900 transition-colors">
                    <i class="fa-solid fa-spa text-2xl text-emerald-300"></i>
                </div>
                <div>
                    <span class="block font-serif text-2xl font-bold tracking-tight text-stone-900 leading-none">Sentosa</span>
                    <span class="block text-[11px] font-semibold uppercase tracking-widest text-emerald-700 mt-1">Wellness & Massage</span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="<?= $navRoot ?>/index.php#home" class="text-stone-600 hover:text-brand-800 font-medium text-sm transition-colors">Beranda</a>
                <a href="<?= $navRoot ?>/index.php#services" class="text-stone-600 hover:text-brand-800 font-medium text-sm transition-colors">Menu Layanan</a>
                <a href="<?= $navRoot ?>/index.php#therapists" class="text-stone-600 hover:text-brand-800 font-medium text-sm transition-colors">Terapis</a>
                <a href="<?= $navRoot ?>/index.php#features" class="text-stone-600 hover:text-brand-800 font-medium text-sm transition-colors">Keunggulan</a>
                <a href="<?= $navRoot ?>/index.php#faq" class="text-stone-600 hover:text-brand-800 font-medium text-sm transition-colors">Bantuan / FAQ</a>
            </nav>

            <!-- Actions & User Menu -->
            <div class="hidden md:flex items-center space-x-3">
                <!-- Cek Status Booking Cepat -->
                <button onclick="openCheckBookingModal()" class="text-stone-600 hover:text-brand-800 text-sm font-medium px-3 py-2 rounded-lg hover:bg-stone-100 transition-colors flex items-center space-x-1.5" title="Cek Status Reservasi Anda">
                    <i class="fa-solid fa-receipt text-emerald-700"></i>
                    <span>Cek Booking</span>
                </button>

                <?php if ($isTherapist): ?>
                    <!-- Shortcut Khusus Terapis -->
                    <a href="<?= $navRoot ?>/therapist-portal.php" class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200 transition-colors shadow-sm">
                        <i class="fa-solid fa-clipboard-user text-emerald-600"></i>
                        <span>Portal Terapis</span>
                    </a>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <!-- Shortcut Khusus Admin -->
                    <a href="<?= $navRoot ?>/admin/dashboard.php" class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition-colors">
                        <i class="fa-solid fa-gauge-high text-brand-800"></i>
                        <span>Admin Panel</span>
                    </a>
                <?php endif; ?>

                <?php if ($currentUser): ?>
                    <!-- Dropdown / Info Pengguna Login -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2.5 px-3 py-2 rounded-xl bg-stone-100 hover:bg-stone-200/80 transition-colors text-stone-800 text-sm font-medium">
                            <span class="w-7 h-7 rounded-full bg-brand-800 text-white flex items-center justify-center text-xs font-bold uppercase overflow-hidden">
                                <?php if (!empty($currentUser['avatar_url'])): ?>
                                    <img src="<?= htmlspecialchars($currentUser['avatar_url']) ?>" alt="<?= htmlspecialchars($currentUser['name']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?= substr($currentUser['name'], 0, 1) ?>
                                <?php endif; ?>
                            </span>
                            <span class="max-w-[120px] truncate"><?= htmlspecialchars($currentUser['name']) ?></span>
                            <i class="fa-solid fa-chevron-down text-xs text-stone-500"></i>
                        </button>

                        <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-stone-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="px-4 py-2 border-b border-stone-100">
                                <p class="text-xs text-stone-500">Masuk sebagai:</p>
                                <p class="text-sm font-semibold text-stone-800 truncate"><?= htmlspecialchars($currentUser['email']) ?></p>
                                <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold uppercase rounded-full <?= $isAdmin ? 'bg-amber-100 text-amber-800' : ($isTherapist ? 'bg-teal-100 text-teal-800' : 'bg-emerald-100 text-emerald-800') ?>">
                                    <?= $isAdmin ? 'Administrator' : ($isTherapist ? 'Terapis Berlisensi' : 'Pelanggan') ?>
                                </span>
                            </div>

                            <?php if ($isTherapist): ?>
                                <a href="<?= $navRoot ?>/therapist-portal.php" class="flex items-center px-4 py-2.5 text-sm font-bold text-emerald-800 bg-emerald-50/70 hover:bg-emerald-100 transition-colors">
                                    <i class="fa-solid fa-clipboard-user w-5 text-emerald-600"></i>
                                    <span>Jadwal & Tugas Terapis</span>
                                </a>
                            <?php endif; ?>

                            <a href="<?= $navRoot ?>/my-bookings.php" class="flex items-center px-4 py-2.5 text-sm text-stone-700 hover:bg-emerald-50 hover:text-brand-800 transition-colors">
                                <i class="fa-solid fa-calendar-days w-5 text-emerald-700"></i>
                                <span><?= $isTherapist ? 'Riwayat Reservasi Saya' : 'Riwayat Booking Saya' ?></span>
                            </a>

                            <a href="<?= $navRoot ?>/my-bookings.php?action=edit_profile" class="flex items-center px-4 py-2.5 text-sm text-stone-700 hover:bg-emerald-50 hover:text-brand-800 transition-colors">
                                <i class="fa-solid fa-user-pen w-5 text-emerald-700"></i>
                                <span>Edit Profil & Sandi</span>
                            </a>

                            <?php if ($isAdmin): ?>
                                <a href="<?= $navRoot ?>/admin/dashboard.php" class="flex items-center px-4 py-2.5 text-sm text-stone-700 hover:bg-emerald-50 hover:text-brand-800 transition-colors border-t border-stone-100">
                                    <i class="fa-solid fa-gauge-high w-5 text-emerald-700"></i>
                                    <span>Admin Panel</span>
                                </a>
                            <?php endif; ?>

                            <button onclick="handleLogout()" class="w-full text-left flex items-center px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition-colors border-t border-stone-100">
                                <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
                                <span>Keluar (Logout)</span>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Tombol Masuk Modal -->
                    <button onclick="openAuthModal('login')" class="text-stone-700 hover:text-brand-800 font-semibold text-sm px-4 py-2 rounded-xl transition-colors">
                        Masuk
                    </button>
                <?php endif; ?>

                <!-- Tombol CTA Booking -->
                <a href="<?= $navRoot ?>/booking.php" class="inline-flex items-center space-x-2 bg-brand-800 hover:bg-brand-900 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md shadow-brand-900/20 hover:shadow-lg transition-all duration-200 transform active:scale-95">
                    <i class="fa-solid fa-calendar-check text-emerald-300"></i>
                    <span>Reservasi</span>
                </a>
            </div>

            <!-- Mobile Hamburger Button -->
            <div class="flex items-center space-x-2 md:hidden">
                <a href="<?= $navRoot ?>/booking.php" class="bg-brand-800 text-white text-xs font-semibold px-3 py-2 rounded-lg">
                    Reservasi
                </a>
                <button id="mobileMenuBtn" onclick="toggleMobileMenu()" class="p-2 text-stone-600 hover:text-stone-900 focus:outline-none rounded-lg hover:bg-stone-100">
                    <i class="fa-solid fa-bars text-xl" id="menuIcon"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-stone-200 bg-white px-4 pt-2 pb-6 space-y-3 shadow-lg">
        <a href="<?= $navRoot ?>/index.php#home" onclick="toggleMobileMenu()" class="block py-2 text-base font-medium text-stone-700 hover:text-brand-800">Beranda</a>
        <a href="<?= $navRoot ?>/index.php#services" onclick="toggleMobileMenu()" class="block py-2 text-base font-medium text-stone-700 hover:text-brand-800">Menu Layanan</a>
        <a href="<?= $navRoot ?>/index.php#therapists" onclick="toggleMobileMenu()" class="block py-2 text-base font-medium text-stone-700 hover:text-brand-800">Terapis</a>
        <a href="<?= $navRoot ?>/index.php#features" onclick="toggleMobileMenu()" class="block py-2 text-base font-medium text-stone-700 hover:text-brand-800">Keunggulan</a>
        <a href="<?= $navRoot ?>/index.php#faq" onclick="toggleMobileMenu()" class="block py-2 text-base font-medium text-stone-700 hover:text-brand-800">Bantuan & FAQ</a>

        <div class="pt-3 border-t border-stone-100 space-y-2">
            <button onclick="openCheckBookingModal(); toggleMobileMenu();" class="w-full text-left py-2 text-sm font-medium text-stone-700 flex items-center space-x-2">
                <i class="fa-solid fa-receipt text-emerald-700"></i>
                <span>Cek Status Booking</span>
            </button>

            <?php if ($currentUser): ?>
                <?php if ($isTherapist): ?>
                    <a href="<?= $navRoot ?>/therapist-portal.php" class="block py-2 text-sm font-bold text-emerald-800 bg-emerald-50 px-3 rounded-lg border border-emerald-200">
                        <i class="fa-solid fa-clipboard-user mr-2 text-emerald-600"></i> Portal Jadwal & Tugas Terapis
                    </a>
                <?php endif; ?>
                <a href="<?= $navRoot ?>/my-bookings.php" class="block py-2 text-sm font-semibold text-stone-800 hover:text-emerald-800">
                    <i class="fa-solid fa-calendar-days mr-2 text-emerald-700"></i> <?= $isTherapist ? 'Riwayat Reservasi Saya' : 'Riwayat Booking Saya' ?>
                </a>
                <a href="<?= $navRoot ?>/my-bookings.php?action=edit_profile" class="block py-2 text-sm font-semibold text-stone-800 hover:text-emerald-800">
                    <i class="fa-solid fa-user-pen mr-2 text-emerald-700"></i> Edit Profil & Sandi
                </a>
                <?php if ($isAdmin): ?>
                    <a href="<?= $navRoot ?>/admin/dashboard.php" class="block py-2 text-sm font-semibold text-emerald-800">
                        <i class="fa-solid fa-gauge-high mr-2"></i> Ke Admin Panel
                    </a>
                <?php endif; ?>
                <button onclick="handleLogout()" class="w-full text-left py-2 text-sm font-semibold text-rose-600">
                    <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Keluar (Logout)
                </button>
            <?php else: ?>
                <button onclick="openAuthModal('login'); toggleMobileMenu();" class="w-full text-center py-2.5 text-sm font-semibold rounded-xl bg-stone-100 text-stone-800">
                    Masuk ke Akun
                </button>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        const icon = document.getElementById('menuIcon');
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            icon.classList.replace('fa-bars', 'fa-xmark');
        } else {
            menu.classList.add('hidden');
            icon.classList.replace('fa-xmark', 'fa-bars');
        }
    }

    async function handleLogout() {
        try {
            const res = await fetch('<?= $navRoot ?>/api/auth.php?action=logout', {
                credentials: 'same-origin'
            });
            const data = await res.json();
            if (data.success) {
                showToast('Berhasil logout.', 'success');
                setTimeout(() => window.location.reload(), 800);
            }
        } catch (e) {
            window.location.reload();
        }
    }
</script>