<?php
// ==========================================================
// Views Partial: Modal Autentikasi & Modal Cek Booking
// ==========================================================
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/database.php';
}

/** @var string|null $baseUrl */
$baseUrl = isset($baseUrl) ? $baseUrl : (function_exists('getBaseUrl') ? getBaseUrl() : '');
$modalRoot = !empty($baseUrl) ? $baseUrl : '.';
?>

<!-- 1. MODAL LOGIN & REGISTER -->
<div id="authModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div onclick="closeAuthModal()" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm transition-opacity"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-stone-100">
            <!-- Close Button -->
            <button onclick="closeAuthModal()" class="absolute top-5 right-5 text-stone-400 hover:text-stone-700 w-8 h-8 flex items-center justify-center rounded-full hover:bg-stone-100 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>

            <div class="p-6 sm:p-8">
                <!-- Tabs -->
                <div class="flex border-b border-stone-200 mb-6">
                    <button id="tabLoginBtn" onclick="switchAuthTab('login')" class="flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-brand-800 text-brand-900 transition-colors">
                        Masuk Akun
                    </button>
                    <button id="tabRegisterBtn" onclick="switchAuthTab('register')" class="flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-transparent text-stone-400 hover:text-stone-600 transition-colors">
                        Daftar Baru
                    </button>
                </div>

                <!-- FORM LOGIN -->
                <form id="loginForm" onsubmit="submitLogin(event)" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Email / No. WhatsApp</label>
                        <div class="relative">
                            <i class="fa-regular fa-envelope absolute left-3.5 top-3.5 text-stone-400 text-sm"></i>
                            <input type="text" id="loginIdentifier" required placeholder="admin@spa.com atau 08123..." class="w-full pl-10 pr-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Kata Sandi (Password)</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3.5 top-3.5 text-stone-400 text-sm"></i>
                            <input type="password" id="loginPassword" required placeholder="••••••••" class="w-full pl-10 pr-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                        </div>
                    </div>

                    <div class="p-3 bg-emerald-50 rounded-xl text-[11px] text-emerald-800 space-y-1 border border-emerald-100">
                        <div class="font-bold flex items-center space-x-1">
                            <i class="fa-solid fa-key"></i>
                            <span>Akun Demo Cepat:</span>
                        </div>
                        <div><strong>Admin:</strong> admin@spa.com (pass: admin123)</div>
                        <div><strong>Pelanggan:</strong> bambang@gmail.com (pass: user123)</div>
                    </div>

                    <button type="submit" id="loginSubmitBtn" class="w-full py-3 bg-brand-800 hover:bg-brand-900 text-white font-semibold text-sm rounded-xl shadow-md shadow-brand-900/20 transition-all flex items-center justify-center space-x-2">
                        <span>Masuk Sekarang</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>

                <!-- FORM REGISTER -->
                <form id="registerForm" onsubmit="submitRegister(event)" class="space-y-4 hidden">
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                        <input type="text" id="regName" required placeholder="Contoh: Budi Santoso" class="w-full px-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                        <input type="email" id="regEmail" required placeholder="nama@email.com" class="w-full px-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp Aktif</label>
                        <input type="tel" id="regPhone" required placeholder="Contoh: 08123456789" class="w-full px-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 uppercase tracking-wider mb-1.5">Kata Sandi (Min 6 Karakter)</label>
                        <input type="password" id="regPassword" required minlength="6" placeholder="••••••••" class="w-full px-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all">
                    </div>

                    <button type="submit" id="regSubmitBtn" class="w-full py-3 bg-brand-800 hover:bg-brand-900 text-white font-semibold text-sm rounded-xl shadow-md shadow-brand-900/20 transition-all flex items-center justify-center space-x-2">
                        <span>Daftar Akun Baru</span>
                        <i class="fa-solid fa-user-plus text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- 2. MODAL CEK STATUS BOOKING -->
<div id="checkBookingModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div onclick="closeCheckBookingModal()" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm transition-opacity"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-stone-100">
            <button onclick="closeCheckBookingModal()" class="absolute top-5 right-5 text-stone-400 hover:text-stone-700 w-8 h-8 flex items-center justify-center rounded-full hover:bg-stone-100 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>

            <div class="p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-serif text-xl font-bold text-stone-900">Lacak Status Reservasi</h3>
                        <p class="text-xs text-stone-500">Ketikkan kode booking yang Anda terima saat reservasi.</p>
                    </div>
                </div>

                <form onsubmit="submitCheckBooking(event)" class="space-y-4">
                    <div class="flex space-x-2">
                        <input type="text" id="checkBookingCode" required placeholder="Contoh: BKG-260901-7891" class="flex-1 uppercase font-mono px-4 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl focus:ring-2 focus:ring-brand-800 focus:bg-white focus:outline-none transition-all tracking-wider">
                        <button type="submit" class="px-5 py-2.5 bg-brand-800 hover:bg-brand-900 text-white text-sm font-semibold rounded-xl transition-colors">
                            Cari
                        </button>
                    </div>
                </form>

                <!-- Container Hasil Pencarian -->
                <div id="bookingResultCard" class="hidden mt-6 pt-6 border-t border-stone-100 space-y-4">
                    <!-- Dynamic filled via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openAuthModal(tab = 'login') {
        document.getElementById('authModal').classList.remove('hidden');
        switchAuthTab(tab);
    }

    function closeAuthModal() {
        document.getElementById('authModal').classList.add('hidden');
    }

    function switchAuthTab(tab) {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const tabLoginBtn = document.getElementById('tabLoginBtn');
        const tabRegisterBtn = document.getElementById('tabRegisterBtn');

        if (tab === 'login') {
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
            tabLoginBtn.className = 'flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-brand-800 text-brand-900 transition-colors';
            tabRegisterBtn.className = 'flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-transparent text-stone-400 hover:text-stone-600 transition-colors';
        } else {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            tabLoginBtn.className = 'flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-transparent text-stone-400 hover:text-stone-600 transition-colors';
            tabRegisterBtn.className = 'flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-brand-800 text-brand-900 transition-colors';
        }
    }

    async function submitLogin(e) {
        e.preventDefault();
        const btn = document.getElementById('loginSubmitBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memproses...';

        const identifier = document.getElementById('loginIdentifier').value;
        const password = document.getElementById('loginPassword').value;

        try {
            const res = await fetch('<?= $modalRoot ?>/api/auth.php?action=login', {
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

            if (data.success) {
                showToast(data.message, 'success');
                closeAuthModal();
                if (data.user && data.user.role === 'admin') {
                    window.location.href = '<?= $modalRoot ?>/admin/dashboard.php';
                } else if (data.user && data.user.role === 'therapist') {
                    window.location.href = '<?= $modalRoot ?>/therapist-portal.php';
                } else {
                    window.location.reload();
                }
            } else {
                showToast(data.message || 'Login gagal.', 'error');
            }
        } catch (err) {
            showToast('Terjadi gangguan koneksi.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    async function submitRegister(e) {
        e.preventDefault();
        const btn = document.getElementById('regSubmitBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Mendaftarkan...';

        const name = document.getElementById('regName').value;
        const email = document.getElementById('regEmail').value;
        const phone = document.getElementById('regPhone').value;
        const password = document.getElementById('regPassword').value;

        try {
            const res = await fetch('<?= $modalRoot ?>/api/auth.php?action=register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name,
                    email,
                    phone,
                    password
                })
            });
            const data = await res.json();

            if (data.success) {
                showToast(data.message, 'success');
                closeAuthModal();
                window.location.reload();
            } else {
                showToast(data.message || 'Pendaftaran gagal.', 'error');
            }
        } catch (err) {
            showToast('Terjadi gangguan jaringan.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    function openCheckBookingModal() {
        document.getElementById('checkBookingModal').classList.remove('hidden');
        document.getElementById('bookingResultCard').classList.add('hidden');
    }

    function closeCheckBookingModal() {
        document.getElementById('checkBookingModal').classList.add('hidden');
    }

    async function submitCheckBooking(e) {
        e.preventDefault();
        const code = document.getElementById('checkBookingCode').value.trim();
        const card = document.getElementById('bookingResultCard');

        if (!code) return;

        card.innerHTML = '<div class="text-center py-6 text-stone-500"><i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-brand-800"></i><p class="text-xs">Mencari data reservasi...</p></div>';
        card.classList.remove('hidden');

        try {
            const res = await fetch(`<?= $modalRoot ?>/api/booking.php?action=list&code=${encodeURIComponent(code)}`);
            const json = await res.json();

            if (json.success && json.data) {
                const b = json.data;
                const statusBadges = {
                    'pending': '<span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Menunggu Konfirmasi</span>',
                    'confirmed': '<span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Telah Dikonfirmasi</span>',
                    'on_process': '<span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Sedang Berjalan</span>',
                    'completed': '<span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Selesai</span>',
                    'cancelled': '<span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-100 text-rose-800">Dibatalkan</span>'
                };

                const paymentBadge = (b.payment_status === 'paid') ?
                    '<span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-100 text-emerald-800">Lunas</span>' :
                    '<span class="px-2 py-0.5 text-xs font-bold rounded bg-amber-100 text-amber-800">Belum Bayar</span>';

                const tipeText = (b.booking_type === 'home_service') ?
                    '<span class="text-amber-800 font-medium"><i class="fa-solid fa-house-chimney mr-1"></i> Home Service</span>' :
                    '<span class="text-emerald-800 font-medium"><i class="fa-solid fa-shop mr-1"></i> On-site Klinik</span>';

                card.innerHTML = `
                    <div class="bg-stone-50 rounded-2xl p-5 border border-stone-200 text-sm space-y-3">
                        <div class="flex justify-between items-start border-b border-stone-200 pb-3">
                            <div>
                                <span class="text-xs text-stone-500 block">Kode Reservasi</span>
                                <span class="font-mono font-bold text-base text-brand-900">${b.booking_code}</span>
                            </div>
                            <div class="text-right">
                                ${statusBadges[b.status] || b.status}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <span class="text-stone-400 block">Nama Pemesan</span>
                                <span class="font-semibold text-stone-800">${b.customer_name}</span>
                            </div>
                            <div>
                                <span class="text-stone-400 block">Tipe Layanan</span>
                                ${tipeText}
                            </div>
                            <div class="col-span-2">
                                <span class="text-stone-400 block">Menu Pijat</span>
                                <span class="font-semibold text-stone-900">${b.service_name} (${b.duration} Menit)</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-stone-400 block">Jadwal Sesi</span>
                                <span class="font-semibold text-stone-900">${b.schedule_formatted}</span>
                            </div>
                            ${b.therapist_name ? `
                            <div class="col-span-2">
                                <span class="text-stone-400 block">Terapis Bertugas</span>
                                <span class="font-semibold text-stone-900"><i class="fa-solid fa-user-check text-emerald-600 mr-1"></i> ${b.therapist_name} (${b.therapist_specialization || 'Terapis Berlisensi'})</span>
                            </div>
                            ` : `
                            <div class="col-span-2">
                                <span class="text-stone-400 block">Terapis</span>
                                <span class="italic text-stone-500"><i class="fa-solid fa-hourglass-half mr-1"></i> Sedang dijadwalkan oleh admin</span>
                            </div>
                            `}
                            ${b.address ? `
                            <div class="col-span-2">
                                <span class="text-stone-400 block">Alamat Panggilan</span>
                                <span class="text-stone-700">${b.address}</span>
                            </div>
                            ` : ''}
                        </div>

                        <div class="flex justify-between items-center pt-3 border-t border-stone-200">
                            <div>
                                <span class="text-xs text-stone-500 block">Total Biaya & Pembayaran</span>
                                <span class="font-bold text-stone-900">${b.price_formatted}</span>
                            </div>
                            <div>
                                ${paymentBadge}
                            </div>
                        </div>
                    </div>
                `;
            } else {
                card.innerHTML = `
                    <div class="p-4 rounded-xl bg-rose-50 text-rose-700 text-xs border border-rose-200 flex items-center space-x-2">
                        <i class="fa-solid fa-circle-exclamation text-base"></i>
                        <span>${json.message || 'Kode booking tidak ditemukan. Mohon periksa kembali.'}</span>
                    </div>
                `;
            }
        } catch (err) {
            card.innerHTML = '<div class="p-4 rounded-xl bg-rose-50 text-rose-700 text-xs">Gagal memuat status booking.</div>';
        }
    }
</script>