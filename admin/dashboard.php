<?php
// ==========================================================
// Admin Dashboard: Manajemen Jadwal, Terapis & Finansial
// ==========================================================
require_once __DIR__ . '/../config/database.php';

startSession();
$currentUser = getCurrentUser();
$isAdmin = ($currentUser && $currentUser['role'] === 'admin');

$pageTitle = 'Admin Dashboard - ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Google Fonts & FontAwesome 6 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    },
                    colors: {
                        brand: {
                            800: '#166534',
                            900: '#14532d',
                            950: '#052e16',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-stone-100 text-stone-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-50 flex flex-col space-y-3 pointer-events-none max-w-sm w-full"></div>

    <script>
        function showToast(message, type = 'success', duration = 3000) {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-center p-4 mb-2 text-sm rounded-xl shadow-lg transform transition-all duration-300 translate-y-[-10px] opacity-0 ${
                type === 'success' ? 'bg-emerald-900 text-emerald-100 border-l-4 border-emerald-400' :
                type === 'error' ? 'bg-rose-900 text-rose-100 border-l-4 border-rose-400' :
                type === 'warning' ? 'bg-amber-900 text-amber-100 border-l-4 border-amber-400' :
                'bg-stone-900 text-stone-100 border-l-4 border-stone-400'
            }`;
            const iconClass = type === 'success' ? 'fa-circle-check text-emerald-400' :
                type === 'error' ? 'fa-circle-exclamation text-rose-400' :
                type === 'warning' ? 'fa-triangle-exclamation text-amber-400' :
                'fa-circle-info text-blue-400';
            toast.innerHTML = `
                <i class="fa-solid ${iconClass} text-lg mr-3"></i>
                <div class="flex-1 font-medium">${message}</div>
                <button onclick="this.parentElement.remove()" class="text-white/60 hover:text-white ml-2 text-base"><i class="fa-solid fa-xmark"></i></button>
            `;
            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-[-10px]', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    </script>

    <?php if (!$isAdmin): ?>
        <!-- FORM LOGIN KHUSUS ADMIN JIKA BELUM LOGIN -->
        <div class="min-h-screen flex items-center justify-center p-4 bg-stone-900">
            <div class="max-w-md w-full bg-white rounded-3xl p-8 shadow-2xl border border-stone-800 space-y-6">
                <div class="text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-800 text-white flex items-center justify-center text-2xl shadow-lg mb-3">
                        <i class="fa-solid fa-shield-halved text-emerald-300"></i>
                    </div>
                    <h2 class="font-serif text-2xl font-bold text-stone-900">Sentosa Spa Admin Panel</h2>
                    <p class="text-xs text-stone-500 mt-1">Silakan masuk menggunakan akun Administrator.</p>
                </div>

                <form onsubmit="handleAdminLogin(event)" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Email / No. Telepon Admin</label>
                        <input type="text" id="adminIdentifier" placeholder="admin@spa.com atau no. telepon" required class="w-full px-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Kata Sandi</label>
                        <input type="password" id="adminPassword" placeholder="••••••••" required class="w-full px-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                    </div>

                    <button type="submit" id="adminLoginBtn" class="w-full py-3 bg-brand-800 hover:bg-brand-900 text-white font-semibold text-sm rounded-xl shadow-md transition-all">
                        Masuk Dashboard
                    </button>
                </form>

                <div class="text-center pt-2">
                    <a href="../index.php" class="text-xs font-semibold text-stone-500 hover:text-stone-800">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Website Utama
                    </a>
                </div>
            </div>
        </div>

        <script>
            async function handleAdminLogin(e) {
                e.preventDefault();
                const btn = document.getElementById('adminLoginBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Verifikasi...';

                const identifier = document.getElementById('adminIdentifier').value.trim();
                const password = document.getElementById('adminPassword').value;

                try {
                    const res = await fetch('../api/auth.php?action=login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            identifier,
                            password
                        })
                    });
                    const data = await res.json();
                    if (data.success && data.user && data.user.role === 'admin') {
                        showToast('Login admin berhasil!', 'success');
                        setTimeout(() => window.location.reload(), 500);
                    } else if (data.success) {
                        showToast('Akun ini bukan Administrator.', 'error');
                    } else {
                        showToast(data.message || 'Login gagal.', 'error');
                    }
                } catch (err) {
                    showToast('Gagal terhubung ke server.', 'error');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = 'Masuk Dashboard';
                }
            }
        </script>
</body>

</html>
<?php exit; ?>
<?php endif; ?>

<!-- JIKA SUDAH LOGIN ADMIN: TAMPILKAN PANEL LENGKAP -->
<div class="min-h-screen flex flex-col">

    <!-- Top Navbar Admin -->
    <header class="bg-stone-900 text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand -->
                <div class="flex items-center space-x-3">
                    <a href="dashboard.php" class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-brand-800 text-white flex items-center justify-center">
                            <i class="fa-solid fa-spa text-emerald-300"></i>
                        </div>
                        <span class="font-serif font-bold text-lg tracking-tight">Sentosa Spa <span class="text-xs font-sans font-normal px-2 py-0.5 rounded bg-emerald-900 text-emerald-300 ml-1">Admin</span></span>
                    </a>
                </div>

                <!-- Navigation Tabs -->
                <nav class="hidden md:flex items-center space-x-1">
                    <button onclick="switchTab('overview')" id="navTabOverview" class="admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-colors bg-brand-800 text-white">
                        <i class="fa-solid fa-chart-pie mr-1.5"></i> Ringkasan
                    </button>
                    <button onclick="switchTab('bookings')" id="navTabBookings" class="admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-colors text-stone-300 hover:text-white hover:bg-stone-800">
                        <i class="fa-solid fa-calendar-check mr-1.5"></i> Reservasi
                    </button>
                    <button onclick="switchTab('services')" id="navTabServices" class="admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-colors text-stone-300 hover:text-white hover:bg-stone-800">
                        <i class="fa-solid fa-hand-sparkles mr-1.5"></i> Layanan
                    </button>
                    <button onclick="switchTab('therapists')" id="navTabTherapists" class="admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-colors text-stone-300 hover:text-white hover:bg-stone-800">
                        <i class="fa-solid fa-user-group mr-1.5"></i> Tim Terapis
                    </button>
                </nav>

                <!-- Right Profile & Logout -->
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="openChangePasswordModal()" class="inline-flex items-center space-x-1.5 text-xs text-amber-300 hover:text-white bg-stone-800 hover:bg-stone-700 px-3 py-1.5 rounded-lg transition-colors" title="Ganti Kata Sandi Administrator">
                        <i class="fa-solid fa-key text-amber-400"></i>
                        <span class="hidden sm:inline font-medium">Ganti Sandi</span>
                    </button>
                    <a href="../index.php" target="_blank" class="hidden sm:inline-flex items-center space-x-1 text-xs text-stone-300 hover:text-white bg-stone-800 px-3 py-1.5 rounded-lg transition-colors" title="Buka website publik di tab baru">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        <span>Lihat Website</span>
                    </a>
                    <button onclick="handleAdminLogout()" class="text-xs text-rose-300 hover:text-rose-100 bg-rose-950/60 hover:bg-rose-900/80 px-3 py-1.5 rounded-lg transition-colors flex items-center space-x-1.5">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Subnav -->
        <div class="md:hidden flex overflow-x-auto px-4 py-2 border-t border-stone-800 space-x-2 scrollbar-none">
            <button onclick="switchTab('overview')" id="mNavOverview" class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap bg-brand-800 text-white">Ringkasan</button>
            <button onclick="switchTab('bookings')" id="mNavBookings" class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap text-stone-400">Reservasi</button>
            <button onclick="switchTab('services')" id="mNavServices" class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap text-stone-400">Layanan</button>
            <button onclick="switchTab('therapists')" id="mNavTherapists" class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap text-stone-400">Terapis</button>
            <button onclick="openChangePasswordModal()" class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap text-amber-300 bg-stone-800 flex items-center space-x-1">
                <i class="fa-solid fa-key text-xs"></i>
                <span>Ganti Sandi</span>
            </button>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">

        <!-- ========================================================== -->
        <!-- TAB 1: OVERVIEW & STATS -->
        <!-- ========================================================== -->
        <div id="tabContentOverview" class="space-y-8">
            <!-- Page Header with Refresh -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="font-serif text-2xl sm:text-3xl font-bold text-stone-900">Ringkasan Metrik & Finansial</h1>
                    <p class="text-stone-500 text-xs sm:text-sm mt-0.5">Pemantauan real-time performa bisnis dan reservasi harian Sentosa Spa.</p>
                </div>
                <button onclick="loadDashboardStats()" class="self-start sm:self-auto px-4 py-2 bg-white hover:bg-stone-50 border border-stone-200 text-stone-700 text-xs font-bold rounded-xl shadow-sm transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-rotate text-emerald-700" id="refreshStatsIcon"></i>
                    <span>Perbarui Data</span>
                </button>
            </div>

            <!-- KPI Metric Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Card 1: Hari Ini -->
                <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-800 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                    <div>
                        <span class="text-xs text-stone-500 font-semibold uppercase tracking-wider block">Booking Hari Ini</span>
                        <span class="text-2xl font-bold text-stone-900" id="statTodayCount">0</span>
                        <span class="text-[11px] text-stone-400 block mt-0.5">Jadwal sesi hari ini</span>
                    </div>
                </div>

                <!-- Card 2: Sesi Berjalan -->
                <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-800 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-spa animate-spin"></i>
                    </div>
                    <div>
                        <span class="text-xs text-stone-500 font-semibold uppercase tracking-wider block">Sesi Aktif</span>
                        <span class="text-2xl font-bold text-stone-900" id="statActiveCount">0</span>
                        <span class="text-[11px] text-stone-400 block mt-0.5">Sedang dalam proses pijat</span>
                    </div>
                </div>

                <!-- Card 3: Menunggu Konfirmasi -->
                <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <span class="text-xs text-stone-500 font-semibold uppercase tracking-wider block">Perlu Konfirmasi</span>
                        <span class="text-2xl font-bold text-amber-700" id="statPendingCount">0</span>
                        <span class="text-[11px] text-stone-400 block mt-0.5">Status pending masuk</span>
                    </div>
                </div>

                <!-- Card 4: Total Pendapatan Lunas -->
                <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-money-bill-trend-up"></i>
                    </div>
                    <div>
                        <span class="text-xs text-stone-500 font-semibold uppercase tracking-wider block">Pendapatan Masuk</span>
                        <span class="text-xl font-bold text-emerald-800" id="statGrossRevenue">Rp 0</span>
                        <span class="text-[11px] text-stone-400 block mt-0.5">Total transaksi terbayar</span>
                    </div>
                </div>
            </div>

            <!-- Status & Breakdown Details -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Status Distribution -->
                <div class="bg-white rounded-2xl p-6 border border-stone-200/80 shadow-sm space-y-4">
                    <h3 class="font-serif text-lg font-bold text-stone-900 border-b border-stone-100 pb-3 flex items-center justify-between">
                        <span>Status Seluruh Reservasi</span>
                        <i class="fa-solid fa-chart-column text-stone-400 text-sm"></i>
                    </h3>
                    <div class="space-y-3 text-xs" id="statusDistributionBox">
                        <!-- Dynamic populated -->
                    </div>
                </div>

                <!-- Tipe Reservasi Distribution -->
                <div class="bg-white rounded-2xl p-6 border border-stone-200/80 shadow-sm space-y-4">
                    <h3 class="font-serif text-lg font-bold text-stone-900 border-b border-stone-100 pb-3 flex items-center justify-between">
                        <span>Distribusi Layanan</span>
                        <i class="fa-solid fa-pie-chart text-stone-400 text-sm"></i>
                    </h3>
                    <div class="space-y-4 pt-2">
                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-amber-800"><i class="fa-solid fa-house mr-1"></i> Home Service</span>
                                <span id="distHomeCount">0 Pesanan</span>
                            </div>
                            <div class="w-full h-2.5 rounded-full bg-stone-100 overflow-hidden">
                                <div id="distHomeBar" class="h-full bg-amber-500 rounded-full" style="width: 50%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-emerald-800"><i class="fa-solid fa-shop mr-1"></i> On-site Klinik</span>
                                <span id="distClinicCount">0 Pesanan</span>
                            </div>
                            <div class="w-full h-2.5 rounded-full bg-stone-100 overflow-hidden">
                                <div id="distClinicBar" class="h-full bg-emerald-600 rounded-full" style="width: 50%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Terapis Status Banner -->
                    <div class="mt-6 p-4 rounded-xl bg-stone-50 border border-stone-200/60 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-stone-500 block">Kesiapan Terapis:</span>
                            <span class="text-sm font-bold text-stone-900" id="statTherapistRatio">0 / 0 Aktif Bertugas</span>
                        </div>
                        <button onclick="switchTab('therapists')" class="text-xs font-bold text-emerald-800 hover:underline">
                            Kelola <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Quick Action Shortcuts -->
                <div class="bg-gradient-to-br from-brand-900 to-emerald-950 text-white rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 block mb-1">Aksi Cepat</span>
                        <h3 class="font-serif text-xl font-bold">Pusat Manajemen</h3>
                        <p class="text-xs text-emerald-200 mt-2 leading-relaxed">
                            Kelola penugasan terapis yang siap jalan, update harga layanan spa, atau periksa pesanan pelanggan yang baru masuk.
                        </p>
                    </div>

                    <div class="space-y-2 pt-6">
                        <button onclick="switchTab('bookings');" class="w-full py-2.5 px-4 bg-white hover:bg-stone-100 text-stone-900 text-xs font-bold rounded-xl shadow transition-colors text-left flex items-center justify-between">
                            <span><i class="fa-solid fa-list-check text-emerald-700 mr-2"></i> Buka Daftar Reservasi</span>
                            <i class="fa-solid fa-arrow-right text-[10px] text-stone-400"></i>
                        </button>
                        <button onclick="openAddServiceModal()" class="w-full py-2.5 px-4 bg-emerald-800/80 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow transition-colors text-left flex items-center justify-between">
                            <span><i class="fa-solid fa-plus text-emerald-300 mr-2"></i> Tambah Menu Layanan Baru</span>
                            <i class="fa-solid fa-arrow-right text-[10px] text-emerald-400"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table Preview -->
            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-stone-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-serif text-lg font-bold text-stone-900">Reservasi Terbaru Masuk</h3>
                        <p class="text-xs text-stone-500">5 transaksi pemesanan terakhir pelanggan.</p>
                    </div>
                    <button onclick="switchTab('bookings')" class="text-xs font-bold text-emerald-800 hover:underline">
                        Lihat Semua Reservasi <i class="fa-solid fa-arrow-right ml-1"></i>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-stone-600">
                        <thead class="bg-stone-50 uppercase tracking-wider text-[10px] text-stone-500 border-b border-stone-200">
                            <tr>
                                <th class="py-3.5 px-4">Kode Booking</th>
                                <th class="py-3.5 px-4">Pelanggan</th>
                                <th class="py-3.5 px-4">Layanan & Tipe</th>
                                <th class="py-3.5 px-4">Jadwal Sesi</th>
                                <th class="py-3.5 px-4">Terapis</th>
                                <th class="py-3.5 px-4">Total</th>
                                <th class="py-3.5 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody id="recentBookingsTbody" class="divide-y divide-stone-100">
                            <!-- Dynamic populated -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- TAB 2: MANAJEMEN RESERVASI (BOOKINGS) -->
        <!-- ========================================================== -->
        <div id="tabContentBookings" class="space-y-6 hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="font-serif text-2xl sm:text-3xl font-bold text-stone-900">Manajemen Reservasi</h1>
                    <p class="text-stone-500 text-xs sm:text-sm mt-0.5">Kelola seluruh pesanan pelanggan, tugaskan terapis, dan ubah status sesi.</p>
                </div>
                <button onclick="loadAllBookings()" class="self-start sm:self-auto px-4 py-2 bg-white hover:bg-stone-50 border border-stone-200 text-stone-700 text-xs font-bold rounded-xl shadow-sm transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-rotate text-emerald-700"></i>
                    <span>Refresh Data</span>
                </button>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-[11px] font-bold text-stone-500 uppercase tracking-wider mb-1">Cari Kode / Nama / No HP</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-stone-400 text-xs"></i>
                        <input type="text" id="filterSearch" placeholder="Cari..." oninput="debounceFilterBookings()" class="w-full pl-9 pr-3 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-stone-500 uppercase tracking-wider mb-1">Status Sesi</label>
                    <select id="filterStatus" onchange="loadAllBookings()" class="w-full px-3 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all font-medium">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Konfirmasi (Pending)</option>
                        <option value="confirmed">Dikonfirmasi (Confirmed)</option>
                        <option value="on_process">Sedang Berjalan (On Process)</option>
                        <option value="completed">Selesai (Completed)</option>
                        <option value="cancelled">Dibatalkan (Cancelled)</option>
                    </select>
                </div>

                <!-- Tipe Booking -->
                <div>
                    <label class="block text-[11px] font-bold text-stone-500 uppercase tracking-wider mb-1">Tipe Reservasi</label>
                    <select id="filterType" onchange="loadAllBookings()" class="w-full px-3 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all font-medium">
                        <option value="">Semua Tipe</option>
                        <option value="home_service">Home Service</option>
                        <option value="clinic">On-site Klinik</option>
                    </select>
                </div>

                <!-- Tanggal -->
                <div>
                    <label class="block text-[11px] font-bold text-stone-500 uppercase tracking-wider mb-1">Filter Tanggal</label>
                    <input type="date" id="filterDate" onchange="loadAllBookings()" class="w-full px-3 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all font-medium">
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-stone-600">
                        <thead class="bg-stone-50 uppercase tracking-wider text-[10px] text-stone-500 border-b border-stone-200">
                            <tr>
                                <th class="py-3.5 px-4">Kode & Waktu</th>
                                <th class="py-3.5 px-4">Pelanggan</th>
                                <th class="py-3.5 px-4">Layanan & Tipe</th>
                                <th class="py-3.5 px-4">Terapis</th>
                                <th class="py-3.5 px-4">Total & Bayar</th>
                                <th class="py-3.5 px-4">Status Sesi</th>
                                <th class="py-3.5 px-4 text-center">Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody id="bookingsTbody" class="divide-y divide-stone-100">
                            <!-- Dynamic populated -->
                        </tbody>
                    </table>
                </div>
                <div id="bookingsEmptyMsg" class="hidden p-8 text-center text-stone-400 text-xs">
                    Tidak ada data reservasi yang sesuai dengan filter.
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- TAB 3: KATALOG LAYANAN (SERVICES) -->
        <!-- ========================================================== -->
        <div id="tabContentServices" class="space-y-6 hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="font-serif text-2xl sm:text-3xl font-bold text-stone-900">Katalog Layanan Pijat</h1>
                    <p class="text-stone-500 text-xs sm:text-sm mt-0.5">Kelola menu pijat, tarif harga, durasi sesi, dan tipe layanan.</p>
                </div>
                <button onclick="openAddServiceModal()" class="self-start sm:self-auto px-5 py-2.5 bg-brand-800 hover:bg-brand-900 text-white text-xs font-bold rounded-xl shadow transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-plus text-emerald-300"></i>
                    <span>Tambah Layanan Baru</span>
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-stone-600">
                        <thead class="bg-stone-50 uppercase tracking-wider text-[10px] text-stone-500 border-b border-stone-200">
                            <tr>
                                <th class="py-3.5 px-4">Layanan</th>
                                <th class="py-3.5 px-4">Durasi</th>
                                <th class="py-3.5 px-4">Tarif</th>
                                <th class="py-3.5 px-4">Tipe Layanan</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="servicesTbody" class="divide-y divide-stone-100">
                            <!-- Dynamic populated -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- TAB 4: TIM TERAPIS (THERAPISTS) -->
        <!-- ========================================================== -->
        <div id="tabContentTherapists" class="space-y-6 hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="font-serif text-2xl sm:text-3xl font-bold text-stone-900">Manajemen Tim Terapis</h1>
                    <p class="text-stone-500 text-xs sm:text-sm mt-0.5">Atur status ketersediaan terapis (Tersedia / Libur), gender, dan spesialisasi.</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="openAddTherapistModal()" class="px-4 py-2 bg-brand-800 hover:bg-brand-900 text-white text-xs font-bold rounded-xl shadow-md shadow-brand-900/10 transition-all flex items-center space-x-2">
                        <i class="fa-solid fa-user-plus text-emerald-300"></i>
                        <span>Tambah Terapis Baru</span>
                    </button>
                    <button onclick="loadAllTherapists()" class="px-3.5 py-2 bg-white hover:bg-stone-50 border border-stone-200 text-stone-700 text-xs font-bold rounded-xl shadow-sm transition-all flex items-center space-x-1.5">
                        <i class="fa-solid fa-rotate text-emerald-700"></i>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="therapistsCardsContainer">
                <!-- Dynamic populated -->
            </div>
        </div>

    </main>

</div>

<!-- ========================================================== -->
<!-- MODAL 1: ASSIGN THERAPIST -->
<!-- ========================================================== -->
<div id="assignModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div onclick="closeAssignModal()" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-stone-100 z-10 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h3 class="font-serif text-lg font-bold text-stone-900">Penugasan Terapis</h3>
                <button onclick="closeAssignModal()" class="text-stone-400 hover:text-stone-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <p class="text-xs text-stone-500" id="assignModalSubtext">Pilih terapis yang ditugaskan untuk reservasi ini.</p>

            <form onsubmit="submitAssignTherapist(event)" class="space-y-4">
                <input type="hidden" id="assignBookingId">

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Pilih Terapis Aktif</label>
                    <select id="assignTherapistSelect" required class="w-full px-3 py-2.5 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:outline-none">
                        <!-- Dynamic loaded -->
                    </select>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="assignAutoConfirm" checked class="w-4 h-4 rounded text-emerald-700 focus:ring-emerald-600">
                    <label for="assignAutoConfirm" class="text-xs text-stone-700 font-medium cursor-pointer">Otomatis ubah status booking menjadi <strong>Confirmed</strong></label>
                </div>

                <div class="pt-2 flex space-x-2">
                    <button type="button" onclick="closeAssignModal()" class="flex-1 py-2.5 rounded-xl border border-stone-200 text-stone-600 font-semibold text-xs">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-brand-800 hover:bg-brand-900 text-white font-semibold text-xs shadow-md">Simpan Penugasan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL 2: DETAIL BOOKING & ALAMAT HOME SERVICE -->
<!-- ========================================================== -->
<div id="bookingDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div onclick="closeBookingDetailModal()" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-stone-100 z-10 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h3 class="font-serif text-lg font-bold text-stone-900" id="detailModalCode">Detail Reservasi</h3>
                <button onclick="closeBookingDetailModal()" class="text-stone-400 hover:text-stone-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div id="detailModalBody" class="space-y-3 text-xs text-stone-600">
                <!-- Dynamic -->
            </div>
            <div class="pt-2 flex justify-end">
                <button type="button" onclick="closeBookingDetailModal()" class="px-5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold text-xs">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL 3: TAMBAH / EDIT LAYANAN -->
<!-- ========================================================== -->
<div id="serviceModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div onclick="closeServiceModal()" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-stone-100 z-10 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h3 class="font-serif text-lg font-bold text-stone-900" id="serviceModalTitle">Tambah Layanan Baru</h3>
                <button onclick="closeServiceModal()" class="text-stone-400 hover:text-stone-700"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form onsubmit="submitServiceForm(event)" class="space-y-3.5">
                <input type="hidden" id="srvFormId">

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Nama Layanan</label>
                    <input type="text" id="srvFormName" required placeholder="Contoh: Balinese Deep Tissue" class="w-full px-3.5 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Durasi (Menit)</label>
                        <input type="number" id="srvFormDuration" min="15" step="15" value="60" required class="w-full px-3.5 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Tarif Harga (Rp)</label>
                        <input type="number" id="srvFormPrice" min="10000" step="5000" value="150000" required class="w-full px-3.5 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Tipe Layanan</label>
                    <select id="srvFormType" class="w-full px-3.5 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none font-medium">
                        <option value="both">Klinik & Home Service (Bisa Keduanya)</option>
                        <option value="home_service">Khusus Home Service</option>
                        <option value="clinic_only">Hanya di Klinik / Studio</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">URL Gambar (Unsplash/Foto)</label>
                    <input type="url" id="srvFormImage" placeholder="https://images.unsplash.com/..." class="w-full px-3.5 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Deskripsi Manfaat Layanan</label>
                    <textarea id="srvFormDesc" rows="3" placeholder="Jelaskan teknik pijatan, fokus otot, dan khasiat..." class="w-full px-3.5 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none"></textarea>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" id="srvFormActive" checked class="w-4 h-4 rounded text-emerald-700 focus:ring-emerald-600">
                    <label for="srvFormActive" class="text-xs text-stone-700 font-medium cursor-pointer">Layanan Aktif (Dapat Dipesan Pelanggan)</label>
                </div>

                <div class="pt-3 flex space-x-2">
                    <button type="button" onclick="closeServiceModal()" class="flex-1 py-2.5 rounded-xl border border-stone-200 text-stone-600 font-semibold text-xs">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-brand-800 hover:bg-brand-900 text-white font-semibold text-xs shadow-md">Simpan Layanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================== -->
<!-- MODAL 4: FORM TAMBAH / EDIT TERAPIS -->
<!-- ========================================================== -->
<div id="therapistModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div onclick="closeTherapistModal()" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-stone-100 z-10 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h3 class="font-serif text-lg font-bold text-stone-900" id="therapistModalTitle">Tambah Terapis Baru</h3>
                <button onclick="closeTherapistModal()" class="text-stone-400 hover:text-stone-700"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form onsubmit="submitTherapistForm(event)" class="space-y-4">
                <input type="hidden" id="thFormId">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Nama Lengkap Terapis <span class="text-rose-500">*</span></label>
                        <input type="text" id="thFormName" required placeholder="Contoh: Rina Kusuma" class="w-full px-3.5 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Gender Terapis <span class="text-rose-500">*</span></label>
                        <select id="thFormGender" required class="w-full px-3.5 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none">
                            <option value="female">Wanita</option>
                            <option value="male">Pria</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Nomor WhatsApp <span class="text-rose-500">*</span></label>
                        <input type="tel" id="thFormPhone" required placeholder="Contoh: 081234567895" class="w-full px-3.5 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Spesialisasi Keahlian Pijat <span class="text-rose-500">*</span></label>
                        <input type="text" id="thFormSpecialization" required placeholder="Contoh: Balinese Deep Tissue, Aromaterapi" class="w-full px-3.5 py-2 text-xs bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none">
                    </div>
                </div>

                <!-- Box Kredensial Login Terapis -->
                <div class="p-4 bg-stone-50 rounded-2xl border border-stone-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-stone-800 font-bold text-xs">
                            <i class="fa-solid fa-key text-emerald-700"></i>
                            <span>Akun Login Terapis (Digunakan untuk Masuk Sistem)</span>
                        </div>
                        <span class="text-[10px] text-stone-400 font-medium">Role: Terapis</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 uppercase tracking-wider mb-1">Email Akun Login <span class="text-rose-500">*</span></label>
                            <input type="email" id="thFormEmail" required placeholder="rina@spa.com" class="w-full px-3 py-2 text-xs bg-white border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 uppercase tracking-wider mb-1" id="thFormPasswordLabel">Password Login</label>
                            <div class="relative">
                                <input type="text" id="thFormPassword" placeholder="therapist123" class="w-full px-3 py-2 text-xs bg-white border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:outline-none pr-9 font-mono">
                                <button type="button" onclick="toggleTherapistPasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-stone-700 focus:outline-none" title="Tampilkan / Sembunyikan Password">
                                    <i class="fa-regular fa-eye-slash" id="thFormPasswordIcon"></i>
                                </button>
                            </div>
                            <span class="text-[10px] text-stone-400 block mt-1" id="thFormPasswordHelp">Default: <strong>therapist123</strong> (min. 6 karakter)</span>
                        </div>
                    </div>
                </div>

                <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-2xl">
                    <div class="flex items-center space-x-2 text-amber-900 font-bold text-xs mb-1">
                        <i class="fa-solid fa-star text-amber-500"></i>
                        <span>Kalkulasi Rating Pelanggan Otomatis</span>
                    </div>
                    <p class="text-[11px] text-stone-600 leading-relaxed">
                        Rating dihitung otomatis dari rata-rata ulasan yang diberikan oleh pelanggan yang telah menyelesaikan sesi perawatan. Admin tidak dapat menginput rating manual untuk menjaga orisinalitas penilaian.
                    </p>
                    <div class="mt-2 text-xs font-bold text-stone-800 pt-2 border-t border-amber-200/60 flex items-center justify-between">
                        <span>Status Rating Terapis:</span>
                        <span class="text-amber-700 bg-amber-100/80 px-2.5 py-0.5 rounded-lg font-mono font-bold" id="thFormRatingBadge">5.0 ★ (0 ulasan)</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" id="thFormAvailable" checked class="w-4 h-4 rounded text-emerald-700 focus:ring-emerald-600">
                    <label for="thFormAvailable" class="text-xs text-stone-700 font-medium cursor-pointer">Status Tersedia (Siap Menerima Reservasi)</label>
                </div>

                <div class="pt-3 flex space-x-2">
                    <button type="button" onclick="closeTherapistModal()" class="flex-1 py-2.5 rounded-xl border border-stone-200 text-stone-600 font-semibold text-xs">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-brand-800 hover:bg-brand-900 text-white font-semibold text-xs shadow-md">Simpan Terapis</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ganti Kata Sandi Admin -->
<div id="changePasswordModal" class="hidden fixed inset-0 z-50 bg-stone-900/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-stone-200">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shadow-sm">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 class="font-serif font-bold text-stone-900 text-lg">Ganti Kata Sandi</h3>
                    <p class="text-xs text-stone-500">Perbarui kata sandi akun administrator.</p>
                </div>
            </div>
            <button onclick="closeChangePasswordModal()" class="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 flex items-center justify-center text-sm transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form onsubmit="handleAdminChangePassword(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Kata Sandi Saat Ini</label>
                <input type="password" id="cpCurrentPassword" required placeholder="••••••••" class="w-full px-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Kata Sandi Baru (Min. 6 Karakter)</label>
                <input type="password" id="cpNewPassword" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1">Konfirmasi Kata Sandi Baru</label>
                <input type="password" id="cpConfirmPassword" required minlength="6" placeholder="Ulangi kata sandi baru" class="w-full px-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
            </div>

            <div class="pt-3 flex space-x-2">
                <button type="button" onclick="closeChangePasswordModal()" class="flex-1 py-2.5 rounded-xl border border-stone-200 text-stone-600 font-semibold text-xs hover:bg-stone-50 transition-all">Batal</button>
                <button type="submit" id="cpSubmitBtn" class="flex-1 py-2.5 rounded-xl bg-brand-800 hover:bg-brand-900 text-white font-semibold text-xs shadow-md transition-all">Simpan Sandi</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================== -->
<!-- JAVASCRIPT LOGIC ADMIN PANEL -->
<!-- ========================================================== -->
<script>
    let cachedTherapists = [];
    let filterDebounceTimer = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadDashboardStats();
        loadAllTherapists();
    });

    // Tab Switching
    function switchTab(tabId) {
        const tabs = ['overview', 'bookings', 'services', 'therapists'];
        tabs.forEach(t => {
            const content = document.getElementById(`tabContent${t.charAt(0).toUpperCase() + t.slice(1)}`);
            const navBtn = document.getElementById(`navTab${t.charAt(0).toUpperCase() + t.slice(1)}`);
            const mBtn = document.getElementById(`mNav${t.charAt(0).toUpperCase() + t.slice(1)}`);

            if (t === tabId) {
                content.classList.remove('hidden');
                if (navBtn) navBtn.className = 'admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-colors bg-brand-800 text-white';
                if (mBtn) mBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap bg-brand-800 text-white';
            } else {
                content.classList.add('hidden');
                if (navBtn) navBtn.className = 'admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-colors text-stone-300 hover:text-white hover:bg-stone-800';
                if (mBtn) mBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap text-stone-400';
            }
        });

        if (tabId === 'overview') loadDashboardStats();
        if (tabId === 'bookings') loadAllBookings();
        if (tabId === 'services') loadAllServices();
        if (tabId === 'therapists') loadAllTherapists();
    }

    // 1. STATS & OVERVIEW LOADER
    async function loadDashboardStats() {
        const icon = document.getElementById('refreshStatsIcon');
        if (icon) icon.classList.add('fa-spin');

        try {
            const res = await fetch('../api/stats.php');
            const data = await res.json();

            if (data.success) {
                const m = data.metrics;
                document.getElementById('statTodayCount').textContent = m.total_today;
                document.getElementById('statActiveCount').textContent = m.active_sessions;
                document.getElementById('statPendingCount').textContent = m.pending_bookings;
                document.getElementById('statGrossRevenue').textContent = m.gross_revenue_format;
                document.getElementById('statTherapistRatio').textContent = `${m.therapists_available} / ${m.therapists_total} Aktif`;

                // Status breakdown list
                const sb = data.status_breakdown;
                const statusBox = document.getElementById('statusDistributionBox');
                statusBox.innerHTML = `
                        <div class="flex justify-between items-center py-1.5 border-b border-stone-100">
                            <span class="flex items-center space-x-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span><span>Menunggu Konfirmasi</span></span>
                            <span class="font-bold text-stone-900">${sb.pending}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-stone-100">
                            <span class="flex items-center space-x-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span><span>Dikonfirmasi</span></span>
                            <span class="font-bold text-stone-900">${sb.confirmed}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-stone-100">
                            <span class="flex items-center space-x-2"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span><span>Sedang Pijat</span></span>
                            <span class="font-bold text-stone-900">${sb.on_process}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-stone-100">
                            <span class="flex items-center space-x-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span>Selesai</span></span>
                            <span class="font-bold text-stone-900">${sb.completed}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5">
                            <span class="flex items-center space-x-2"><span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span><span>Dibatalkan</span></span>
                            <span class="font-bold text-stone-900">${sb.cancelled}</span>
                        </div>
                    `;

                // Type breakdown
                const tb = data.type_breakdown;
                const totalType = (tb.home_service + tb.clinic) || 1;
                const homePct = Math.round((tb.home_service / totalType) * 100);
                const clinicPct = 100 - homePct;

                document.getElementById('distHomeCount').textContent = `${tb.home_service} Pesanan (${homePct}%)`;
                document.getElementById('distHomeBar').style.width = `${homePct}%`;
                document.getElementById('distClinicCount').textContent = `${tb.clinic} Pesanan (${clinicPct}%)`;
                document.getElementById('distClinicBar').style.width = `${clinicPct}%`;

                // Recent bookings
                const recentTbody = document.getElementById('recentBookingsTbody');
                recentTbody.innerHTML = '';
                if (data.recent_bookings && data.recent_bookings.length > 0) {
                    data.recent_bookings.forEach(b => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-stone-50/80 transition-colors';
                        tr.innerHTML = `
                                <td class="py-3 px-4 font-mono font-bold text-brand-900">${b.booking_code}</td>
                                <td class="py-3 px-4 font-semibold text-stone-800">${b.customer_name}</td>
                                <td class="py-3 px-4">
                                    <span class="block font-medium">${b.service_name}</span>
                                    <span class="text-[10px] ${b.booking_type === 'home_service' ? 'text-amber-700' : 'text-emerald-700'} font-semibold uppercase">${b.booking_type === 'home_service' ? 'Home Service' : 'Klinik'}</span>
                                </td>
                                <td class="py-3 px-4 text-stone-500">${b.schedule_formatted}</td>
                                <td class="py-3 px-4">${b.therapist_name ? `<span class="font-medium text-stone-800"><i class="fa-solid fa-user-check text-emerald-600 mr-1"></i>${b.therapist_name}</span>` : '<span class="italic text-stone-400">Belum Ditugaskan</span>'}</td>
                                <td class="py-3 px-4 font-bold text-stone-900">${b.price_formatted}</td>
                                <td class="py-3 px-4">${renderStatusBadge(b.status)}</td>
                            `;
                        recentTbody.appendChild(tr);
                    });
                } else {
                    recentTbody.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-stone-400">Belum ada data reservasi.</td></tr>';
                }
            }
        } catch (err) {
            showToast('Gagal memuat statistik.', 'error');
        } finally {
            if (icon) icon.classList.remove('fa-spin');
        }
    }

    // 2. BOOKINGS MANAGEMENT
    function debounceFilterBookings() {
        clearTimeout(filterDebounceTimer);
        filterDebounceTimer = setTimeout(loadAllBookings, 300);
    }

    async function loadAllBookings() {
        const search = document.getElementById('filterSearch').value.trim();
        const status = document.getElementById('filterStatus').value;
        const type = document.getElementById('filterType').value;
        const date = document.getElementById('filterDate').value;

        const tbody = document.getElementById('bookingsTbody');
        const emptyMsg = document.getElementById('bookingsEmptyMsg');
        tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-stone-400"><i class="fa-solid fa-spinner fa-spin text-lg mr-2"></i>Memuat reservasi...</td></tr>';
        emptyMsg.classList.add('hidden');

        try {
            const query = `../api/booking.php?action=list&search=${encodeURIComponent(search)}&status=${status}&booking_type=${type}&date=${date}`;
            const res = await fetch(query);
            const json = await res.json();

            tbody.innerHTML = '';
            if (json.success && json.data && json.data.length > 0) {
                json.data.forEach(b => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-stone-50/80 transition-colors';

                    const isHome = (b.booking_type === 'home_service');
                    const typeBadge = isHome ?
                        '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800"><i class="fa-solid fa-house mr-1"></i>Home Service</span>' :
                        '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800"><i class="fa-solid fa-shop mr-1"></i>Klinik</span>';

                    const payBadge = (b.payment_status === 'paid') ?
                        '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Lunas</span>' :
                        '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Belum Bayar</span>';

                    tr.innerHTML = `
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-brand-900 block">${b.booking_code}</span>
                                <span class="text-[11px] text-stone-500">${b.schedule_formatted}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-stone-800 block">${b.customer_name}</span>
                                <a href="https://wa.me/${b.customer_phone.replace(/[^0-9]/g, '')}" target="_blank" class="text-emerald-700 hover:underline text-[11px] flex items-center space-x-1 mt-0.5">
                                    <i class="fa-brands fa-whatsapp"></i>
                                    <span>${b.customer_phone}</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-stone-900 block">${b.service_name} (${b.duration_minutes || b.duration}m)</span>
                                <div class="mt-1">${typeBadge}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                ${b.therapist_name ? `
                                    <div class="font-semibold text-stone-800">${b.therapist_name}</div>
                                    <button onclick="openAssignModal(${b.id}, '${b.booking_code}', ${b.therapist_id})" class="text-[10px] text-emerald-800 hover:underline">Ubah Terapis</button>
                                ` : `
                                    <button onclick="openAssignModal(${b.id}, '${b.booking_code}')" class="px-2.5 py-1 rounded-lg bg-emerald-800 hover:bg-brand-900 text-white font-bold text-[10px] transition-colors flex items-center space-x-1">
                                        <i class="fa-solid fa-user-plus"></i>
                                        <span>Tugaskan</span>
                                    </button>
                                `}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-stone-900 block">${b.price_formatted}</span>
                                <div class="mt-1 cursor-pointer" onclick="togglePaymentStatus(${b.id}, '${b.payment_status}')" title="Klik untuk ubah status bayar">
                                    ${payBadge}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                ${renderStatusBadge(b.status)}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Quick Status Change Select -->
                                    <select onchange="updateBookingStatus(${b.id}, this.value)" class="text-[11px] bg-stone-100 border border-stone-200 rounded-lg px-2 py-1 focus:ring-1 focus:ring-brand-800 focus:outline-none">
                                        <option value="" disabled selected>Ubah Status</option>
                                        <option value="pending" ${b.status === 'pending' ? 'selected' : ''}>Pending</option>
                                        <option value="confirmed" ${b.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                        <option value="on_process" ${b.status === 'on_process' ? 'selected' : ''}>On Process</option>
                                        <option value="completed" ${b.status === 'completed' ? 'selected' : ''}>Completed</option>
                                        <option value="cancelled" ${b.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                    </select>

                                    <!-- View Detail Button -->
                                    <button onclick='viewBookingDetail(${JSON.stringify(b)})' class="p-1.5 text-stone-500 hover:text-stone-900 rounded-lg hover:bg-stone-200/60" title="Lihat Detail & Catatan">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button onclick="deleteBookingConfirm(${b.id}, '${b.booking_code}')" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Reservasi">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        `;
                    tbody.appendChild(tr);
                });
            } else {
                emptyMsg.classList.remove('hidden');
            }
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-rose-500">Gagal memuat daftar reservasi.</td></tr>';
        }
    }

    function renderStatusBadge(status) {
        const badges = {
            'pending': '<span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">Menunggu</span>',
            'confirmed': '<span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800">Confirmed</span>',
            'on_process': '<span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-purple-100 text-purple-800 animate-pulse">Sedang Pijat</span>',
            'completed': '<span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Selesai</span>',
            'cancelled': '<span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-800">Batal</span>'
        };
        return badges[status] || status;
    }

    async function deleteBookingConfirm(id, code) {
        if (!confirm(`Apakah Anda yakin ingin menghapus data reservasi "${code}" secara permanen?`)) {
            return;
        }
        try {
            const res = await fetch('../api/booking.php?action=delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                loadAllBookings();
                loadDashboardStats();
            } else {
                showToast(data.message || 'Gagal menghapus reservasi.', 'error');
            }
        } catch (e) {
            showToast('Terjadi kesalahan jaringan saat menghapus reservasi.', 'error');
        }
    }

    async function updateBookingStatus(id, newStatus) {
        if (!newStatus) return;
        try {
            const res = await fetch('../api/booking.php?action=update_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id,
                    status: newStatus
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                loadAllBookings();
                loadDashboardStats();
            } else {
                showToast(data.message, 'error');
            }
        } catch (e) {
            showToast('Gagal mengubah status booking.', 'error');
        }
    }

    async function togglePaymentStatus(id, currentStatus) {
        const newStatus = (currentStatus === 'paid') ? 'unpaid' : 'paid';
        try {
            const res = await fetch('../api/booking.php?action=update_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id,
                    payment_status: newStatus
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast(`Status pembayaran diubah menjadi: ${newStatus.toUpperCase()}`, 'success');
                loadAllBookings();
                loadDashboardStats();
            }
        } catch (e) {
            showToast('Gagal mengubah status bayar.', 'error');
        }
    }

    // 3. ASSIGN THERAPIST MODAL
    function openAssignModal(bookingId, bookingCode, currentTherapistId = null) {
        document.getElementById('assignBookingId').value = bookingId;
        document.getElementById('assignModalSubtext').textContent = `Menugaskan terapis untuk kode ${bookingCode}`;

        const select = document.getElementById('assignTherapistSelect');
        select.innerHTML = '';

        cachedTherapists.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = `${t.name} (${t.gender === 'female' ? 'Wanita' : 'Pria'}) - ${t.is_available == 1 ? 'Tersedia' : 'Sedang Libur'}`;
            if (currentTherapistId && t.id == currentTherapistId) opt.selected = true;
            select.appendChild(opt);
        });

        document.getElementById('assignModal').classList.remove('hidden');
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
    }

    async function submitAssignTherapist(e) {
        e.preventDefault();
        const booking_id = parseInt(document.getElementById('assignBookingId').value);
        const therapist_id = parseInt(document.getElementById('assignTherapistSelect').value);
        const auto_confirm = document.getElementById('assignAutoConfirm').checked;

        try {
            const res = await fetch('../api/booking.php?action=assign_therapist', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    booking_id,
                    therapist_id,
                    auto_confirm
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                closeAssignModal();
                loadAllBookings();
                loadDashboardStats();
            } else {
                showToast(data.message, 'error');
            }
        } catch (e) {
            showToast('Gagal menugaskan terapis.', 'error');
        }
    }

    // 4. VIEW BOOKING DETAIL MODAL
    function viewBookingDetail(b) {
        document.getElementById('detailModalCode').textContent = `Detail: ${b.booking_code}`;
        const body = document.getElementById('detailModalBody');
        body.innerHTML = `
                <div class="grid grid-cols-2 gap-3 bg-stone-50 p-4 rounded-xl border border-stone-200">
                    <div><span class="text-stone-400 block text-[11px]">Nama Pemesan</span><span class="font-bold text-stone-900">${b.customer_name}</span></div>
                    <div><span class="text-stone-400 block text-[11px]">No WhatsApp</span><span class="font-bold text-stone-900">${b.customer_phone}</span></div>
                    <div><span class="text-stone-400 block text-[11px]">Layanan Pijat</span><span class="font-bold text-stone-900">${b.service_name}</span></div>
                    <div><span class="text-stone-400 block text-[11px]">Jadwal</span><span class="font-bold text-stone-900">${b.schedule_formatted}</span></div>
                    <div><span class="text-stone-400 block text-[11px]">Tipe</span><span class="font-bold ${b.booking_type === 'home_service' ? 'text-amber-800' : 'text-emerald-800'}">${b.booking_type === 'home_service' ? 'Home Service' : 'On-site Klinik'}</span></div>
                    <div><span class="text-stone-400 block text-[11px]">Total Biaya</span><span class="font-bold text-stone-900">${b.price_formatted} (${b.payment_status})</span></div>
                </div>
                ${b.address ? `
                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                        <span class="text-[11px] font-bold text-amber-900 block mb-1"><i class="fa-solid fa-map-pin mr-1"></i> Alamat Kunjungan Home Service:</span>
                        <p class="text-stone-700">${b.address}</p>
                    </div>
                ` : ''}
                ${b.notes ? `
                    <div class="p-4 bg-stone-50 rounded-xl border border-stone-200">
                        <span class="text-[11px] font-bold text-stone-900 block mb-1"><i class="fa-regular fa-note-sticky mr-1"></i> Catatan Pelanggan / Keluhan:</span>
                        <p class="text-stone-700">${b.notes}</p>
                    </div>
                ` : ''}
            `;
        document.getElementById('bookingDetailModal').classList.remove('hidden');
    }

    function closeBookingDetailModal() {
        document.getElementById('bookingDetailModal').classList.add('hidden');
    }

    // 5. SERVICES CRUD
    async function loadAllServices() {
        const tbody = document.getElementById('servicesTbody');
        tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-stone-400"><i class="fa-solid fa-spinner fa-spin text-lg mr-2"></i>Memuat layanan...</td></tr>';

        try {
            const res = await fetch('../api/services.php?active_only=0');
            const json = await res.json();
            tbody.innerHTML = '';

            if (json.success && json.data) {
                json.data.forEach(s => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-stone-50/80 transition-colors';
                    tr.innerHTML = `
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-3">
                                    <img src="${s.image_url || 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=100'}" class="w-10 h-10 rounded-xl object-cover border border-stone-200">
                                    <div>
                                        <span class="font-bold text-stone-900 block">${s.name}</span>
                                        <span class="text-[11px] text-stone-500 line-clamp-1 max-w-xs">${s.description || '-'}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-semibold">${s.duration_minutes} Menit</td>
                            <td class="py-3 px-4 font-bold text-stone-900">${s.price_formatted}</td>
                            <td class="py-3 px-4 font-medium text-stone-700">
                                ${s.type === 'both' ? 'Klinik & Home Service' : (s.type === 'home_service' ? 'Home Service' : 'Di Klinik Saja')}
                            </td>
                            <td class="py-3 px-4">
                                ${s.is_active == 1 
                                    ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>' 
                                    : '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-stone-200 text-stone-600">Nonaktif</span>'}
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <button onclick='editService(${JSON.stringify(s)})' class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700">Edit</button>
                                <button onclick="deleteService(${s.id})" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700">Hapus</button>
                            </td>
                        `;
                    tbody.appendChild(tr);
                });
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-rose-500">Gagal memuat layanan.</td></tr>';
        }
    }

    function openAddServiceModal() {
        document.getElementById('serviceModalTitle').textContent = 'Tambah Layanan Baru';
        document.getElementById('srvFormId').value = '';
        document.getElementById('srvFormName').value = '';
        document.getElementById('srvFormDuration').value = '60';
        document.getElementById('srvFormPrice').value = '150000';
        document.getElementById('srvFormType').value = 'both';
        document.getElementById('srvFormImage').value = '';
        document.getElementById('srvFormDesc').value = '';
        document.getElementById('srvFormActive').checked = true;

        document.getElementById('serviceModal').classList.remove('hidden');
    }

    function editService(s) {
        document.getElementById('serviceModalTitle').textContent = 'Edit Layanan';
        document.getElementById('srvFormId').value = s.id;
        document.getElementById('srvFormName').value = s.name;
        document.getElementById('srvFormDuration').value = s.duration_minutes;
        document.getElementById('srvFormPrice').value = s.price;
        document.getElementById('srvFormType').value = s.type;
        document.getElementById('srvFormImage').value = s.image_url || '';
        document.getElementById('srvFormDesc').value = s.description || '';
        document.getElementById('srvFormActive').checked = (s.is_active == 1);

        document.getElementById('serviceModal').classList.remove('hidden');
    }

    function closeServiceModal() {
        document.getElementById('serviceModal').classList.add('hidden');
    }

    async function submitServiceForm(e) {
        e.preventDefault();
        const id = document.getElementById('srvFormId').value;
        const name = document.getElementById('srvFormName').value.trim();
        const duration_minutes = parseInt(document.getElementById('srvFormDuration').value);
        const price = parseFloat(document.getElementById('srvFormPrice').value);
        const type = document.getElementById('srvFormType').value;
        const image_url = document.getElementById('srvFormImage').value.trim();
        const description = document.getElementById('srvFormDesc').value.trim();
        const is_active = document.getElementById('srvFormActive').checked ? 1 : 0;

        const payload = {
            id,
            name,
            duration_minutes,
            price,
            type,
            image_url,
            description,
            is_active
        };
        const url = id ? '../api/services.php?action=update' : '../api/services.php?action=create';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                closeServiceModal();
                loadAllServices();
            } else {
                showToast(data.message, 'error');
            }
        } catch (e) {
            showToast('Gagal menyimpan layanan.', 'error');
        }
    }

    async function deleteService(id) {
        if (!confirm('Yakin ingin menghapus/menonaktifkan layanan ini?')) return;
        try {
            const res = await fetch('../api/services.php?action=delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                loadAllServices();
            }
        } catch (e) {
            showToast('Gagal menghapus layanan.', 'error');
        }
    }

    // Helper Escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // 6. THERAPISTS MANAGEMENT (CRUD)
    async function loadAllTherapists() {
        try {
            const res = await fetch('../api/therapists.php');
            const json = await res.json();

            if (json.success && json.data) {
                cachedTherapists = json.data;
                const container = document.getElementById('therapistsCardsContainer');
                if (!container) return;
                container.innerHTML = '';

                if (json.data.length === 0) {
                    container.innerHTML = `
                        <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-stone-200 text-stone-400">
                            <i class="fa-solid fa-user-xmark text-3xl mb-2 text-stone-300"></i>
                            <p class="text-xs">Belum ada terapis terdaftar. Silakan klik "Tambah Terapis Baru".</p>
                        </div>
                    `;
                    return;
                }

                json.data.forEach(t => {
                    const card = document.createElement('div');
                    card.className = 'bg-white rounded-2xl p-6 border border-stone-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition-all';

                    const isAvailable = (t.is_available == 1);
                    const genderBadge = (t.gender === 'female') ?
                        '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-pink-100 text-pink-700">Wanita</span>' :
                        '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Pria</span>';

                    card.innerHTML = `
                            <div>
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 rounded-full bg-brand-800 text-white flex items-center justify-center font-serif text-lg font-bold">
                                            ${escapeHtml(t.name.charAt(0))}
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-stone-900 text-sm">${escapeHtml(t.name)}</h4>
                                            <div class="flex items-center space-x-1.5 mt-0.5">
                                                ${genderBadge}
                                                <span class="text-amber-600 text-xs font-bold"><i class="fa-solid fa-star text-[10px]"></i> ${t.rating_formatted} <span class="text-stone-400 font-normal text-[11px]">(${t.total_reviews || 0} ulasan)</span></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons: View Portal, Edit & Delete -->
                                    <div class="flex items-center space-x-1">
                                        <a href="../therapist-portal.php?therapist_id=${t.id}" target="_blank" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 flex items-center justify-center transition-colors" title="Buka Portal Tugas Terapis Ini">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                        </a>
                                        <button onclick='openEditTherapistModal(${JSON.stringify(t)})' class="w-8 h-8 rounded-lg bg-stone-50 hover:bg-stone-100 text-stone-600 hover:text-stone-900 flex items-center justify-center transition-colors" title="Edit Profil Terapis">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                        <button onclick="deleteTherapist(${t.id}, '${escapeHtml(t.name)}')" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors" title="Hapus Terapis">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-2 text-xs text-stone-600 mb-6">
                                    <div><span class="text-stone-400 block text-[10px] uppercase font-bold">Spesialisasi:</span>${escapeHtml(t.specialization)}</div>
                                    <div><span class="text-stone-400 block text-[10px] uppercase font-bold">WhatsApp:</span>${escapeHtml(t.phone)}</div>
                                    <div><span class="text-stone-400 block text-[10px] uppercase font-bold">Email:</span>${escapeHtml(t.email || '-')}</div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-stone-100 flex items-center justify-between">
                                <span class="text-xs font-semibold ${isAvailable ? 'text-emerald-700' : 'text-stone-400'}">
                                    <span class="w-2 h-2 inline-block rounded-full mr-1.5 ${isAvailable ? 'bg-emerald-500' : 'bg-stone-300'}"></span>
                                    ${isAvailable ? 'Tersedia' : 'Sedang Libur'}
                                </span>

                                <button onclick="toggleTherapistAvailability(${t.id}, ${isAvailable ? 0 : 1})" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors ${isAvailable ? 'bg-amber-100 hover:bg-amber-200 text-amber-800' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800'}">
                                    ${isAvailable ? 'Set Libur' : 'Aktifkan'}
                                </button>
                            </div>
                        `;
                    container.appendChild(card);
                });
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function toggleTherapistAvailability(id, newStatus) {
        try {
            const res = await fetch('../api/therapists.php?action=toggle_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id,
                    is_available: newStatus
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                loadAllTherapists();
                loadDashboardStats();
            }
        } catch (e) {
            showToast('Gagal mengubah ketersediaan terapis.', 'error');
        }
    }

    function toggleTherapistPasswordVisibility() {
        const input = document.getElementById('thFormPassword');
        const icon = document.getElementById('thFormPasswordIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-regular fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-regular fa-eye';
        }
    }

    function openAddTherapistModal() {
        document.getElementById('therapistModalTitle').textContent = 'Tambah Terapis Baru';
        document.getElementById('thFormId').value = '';
        document.getElementById('thFormName').value = '';
        document.getElementById('thFormPhone').value = '';
        document.getElementById('thFormEmail').value = '';
        document.getElementById('thFormPassword').value = 'therapist123';
        document.getElementById('thFormPassword').type = 'text';
        document.getElementById('thFormPasswordIcon').className = 'fa-regular fa-eye-slash';
        document.getElementById('thFormPasswordLabel').innerHTML = 'Password Login <span class="text-rose-500">*</span>';
        document.getElementById('thFormPasswordHelp').innerHTML = 'Default: <strong>therapist123</strong> (dapat Anda ganti sekarang)';
        document.getElementById('thFormGender').value = 'female';
        document.getElementById('thFormSpecialization').value = '';
        document.getElementById('thFormRatingBadge').textContent = '5.0 ★ (Terapis Baru)';
        document.getElementById('thFormAvailable').checked = true;

        document.getElementById('therapistModal').classList.remove('hidden');
    }

    function openEditTherapistModal(t) {
        document.getElementById('therapistModalTitle').textContent = `Edit Terapis: ${t.name}`;
        document.getElementById('thFormId').value = t.id;
        document.getElementById('thFormName').value = t.name;
        document.getElementById('thFormPhone').value = t.phone;
        document.getElementById('thFormEmail').value = t.email || '';
        document.getElementById('thFormPassword').value = '';
        document.getElementById('thFormPassword').type = 'password';
        document.getElementById('thFormPasswordIcon').className = 'fa-regular fa-eye';
        document.getElementById('thFormPasswordLabel').textContent = 'Ganti Password (Opsional)';
        document.getElementById('thFormPasswordHelp').textContent = 'Kosongkan jika tidak ingin mengubah password login terapis.';
        document.getElementById('thFormGender').value = t.gender;
        document.getElementById('thFormSpecialization').value = t.specialization;
        document.getElementById('thFormRatingBadge').textContent = `${t.rating_formatted} ★ (${t.total_reviews || 0} ulasan pelanggan)`;
        document.getElementById('thFormAvailable').checked = (t.is_available == 1);

        document.getElementById('therapistModal').classList.remove('hidden');
    }

    function closeTherapistModal() {
        document.getElementById('therapistModal').classList.add('hidden');
    }

    async function submitTherapistForm(e) {
        e.preventDefault();
        const id = document.getElementById('thFormId').value;
        const name = document.getElementById('thFormName').value.trim();
        const phone = document.getElementById('thFormPhone').value.trim();
        const email = document.getElementById('thFormEmail').value.trim();
        const password = document.getElementById('thFormPassword').value.trim();
        const gender = document.getElementById('thFormGender').value;
        const specialization = document.getElementById('thFormSpecialization').value.trim();
        const is_available = document.getElementById('thFormAvailable').checked ? 1 : 0;

        if (password && password.length < 6) {
            showToast('Password minimal 6 karakter.', 'error');
            return;
        }

        const payload = {
            id,
            name,
            phone,
            email,
            password,
            gender,
            specialization,
            is_available
        };

        const url = id ? '../api/therapists.php?action=update' : '../api/therapists.php?action=create';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message.replace(/\n/g, ' • '), 'success');
                if (!id && data.credentials) {
                    alert(`Terapis Berhasil Didaftarkan!\n\nInformasi Akun Login Terapis:\nEmail: ${data.credentials.email}\nPassword: ${data.credentials.password}\n\nSilakan berikan informasi ini kepada terapis bersangkutan.`);
                }
                closeTherapistModal();
                loadAllTherapists();
                loadDashboardStats();
            } else {
                showToast(data.message, 'error');
            }
        } catch (e) {
            showToast('Gagal menyimpan data terapis.', 'error');
        }
    }

    async function deleteTherapist(id, name) {
        if (!confirm(`Apakah Anda yakin ingin menghapus terapis "${name}" dari sistem?\n\nCatatan: Terapis yang memiliki jadwal aktif tidak dapat dihapus.`)) {
            return;
        }

        try {
            const res = await fetch('../api/therapists.php?action=delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                loadAllTherapists();
                loadDashboardStats();
            } else {
                showToast(data.message, 'error');
            }
        } catch (e) {
            showToast('Gagal menghapus terapis.', 'error');
        }
    }

    async function handleAdminLogout() {
        try {
            await fetch('../api/auth.php?action=logout');
            window.location.reload();
        } catch (e) {
            window.location.reload();
        }
    }

    function openChangePasswordModal() {
        document.getElementById('cpCurrentPassword').value = '';
        document.getElementById('cpNewPassword').value = '';
        document.getElementById('cpConfirmPassword').value = '';
        document.getElementById('changePasswordModal').classList.remove('hidden');
    }

    function closeChangePasswordModal() {
        document.getElementById('changePasswordModal').classList.add('hidden');
    }

    async function handleAdminChangePassword(e) {
        e.preventDefault();
        const current_password = document.getElementById('cpCurrentPassword').value;
        const new_password = document.getElementById('cpNewPassword').value;
        const confirm_password = document.getElementById('cpConfirmPassword').value;

        if (new_password !== confirm_password) {
            showToast('Konfirmasi kata sandi baru tidak cocok.', 'error');
            return;
        }

        const btn = document.getElementById('cpSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';

        try {
            const res = await fetch('../api/auth.php?action=change_password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ current_password, new_password, confirm_password })
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                closeChangePasswordModal();
            } else {
                showToast(data.message || 'Gagal mengubah kata sandi.', 'error');
            }
        } catch (err) {
            showToast('Terjadi kesalahan jaringan saat memperbarui kata sandi.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Simpan Sandi';
        }
    }
</script>
</body>

</html>