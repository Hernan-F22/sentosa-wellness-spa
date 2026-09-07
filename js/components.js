/**
 * Sentosa Spa - Shared UI Components & Modals
 * Pure Vanilla JavaScript
 */

(function () {
    // Detect root path based on whether current location is inside /admin/
    const isSubfolder = window.location.pathname.includes('/admin/');
    const ROOT_PATH = isSubfolder ? '..' : '.';

    // 1. RENDER NAVBAR
    function renderNavbar() {
        const container = document.getElementById('navbar-container');
        if (!container) return;

        const currentUser = SentosaStore.getCurrentUser();
        const isAdmin = currentUser && currentUser.role === 'admin';
        const isTherapist = currentUser && currentUser.role === 'therapist';

        container.innerHTML = `
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-stone-200/80 transition-all duration-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Brand Logo -->
                    <a href="${ROOT_PATH}/index.html" class="flex items-center space-x-3 group">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-800 text-white flex items-center justify-center shadow-md shadow-emerald-900/10 group-hover:bg-emerald-900 transition-colors">
                            <i class="fa-solid fa-spa text-2xl text-emerald-300"></i>
                        </div>
                        <div>
                            <span class="block font-serif text-2xl font-bold tracking-tight text-stone-900 leading-none">Sentosa</span>
                            <span class="block text-[11px] font-semibold uppercase tracking-widest text-emerald-700 mt-1">Wellness & Massage</span>
                        </div>
                    </a>

                    <!-- Desktop Navigation Links -->
                    <nav class="hidden md:flex items-center space-x-8">
                        <a href="${ROOT_PATH}/index.html#home" class="text-stone-600 hover:text-emerald-800 font-medium text-sm transition-colors">Beranda</a>
                        <a href="${ROOT_PATH}/index.html#services" class="text-stone-600 hover:text-emerald-800 font-medium text-sm transition-colors">Menu Layanan</a>
                        <a href="${ROOT_PATH}/index.html#therapists" class="text-stone-600 hover:text-emerald-800 font-medium text-sm transition-colors">Terapis</a>
                        <a href="${ROOT_PATH}/index.html#features" class="text-stone-600 hover:text-emerald-800 font-medium text-sm transition-colors">Keunggulan</a>
                        <a href="${ROOT_PATH}/index.html#faq" class="text-stone-600 hover:text-emerald-800 font-medium text-sm transition-colors">Bantuan / FAQ</a>
                    </nav>

                    <!-- Actions & User Menu -->
                    <div class="hidden md:flex items-center space-x-3">
                        <!-- Cek Status Booking Cepat -->
                        <button onclick="openCheckBookingModal()" class="text-stone-600 hover:text-emerald-800 text-sm font-medium px-3 py-2 rounded-lg hover:bg-stone-100 transition-colors flex items-center space-x-1.5" title="Cek Status Reservasi Anda">
                            <i class="fa-solid fa-receipt text-emerald-700"></i>
                            <span>Cek Booking</span>
                        </button>

                        ${isTherapist ? `
                            <a href="${ROOT_PATH}/therapist-portal.html" class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200 transition-colors shadow-sm">
                                <i class="fa-solid fa-clipboard-user text-emerald-600"></i>
                                <span>Portal Terapis</span>
                            </a>
                        ` : ''}

                        ${isAdmin ? `
                            <a href="${ROOT_PATH}/admin/dashboard.html" class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition-colors">
                                <i class="fa-solid fa-gauge-high text-emerald-800"></i>
                                <span>Admin Panel</span>
                            </a>
                        ` : ''}

                        ${currentUser ? `
                            <div class="relative group">
                                <button class="flex items-center space-x-2.5 px-3 py-2 rounded-xl bg-stone-100 hover:bg-stone-200/80 transition-colors text-stone-800 text-sm font-medium">
                                    <span class="w-7 h-7 rounded-full bg-emerald-800 text-white flex items-center justify-center text-xs font-bold uppercase">
                                        ${currentUser.name.charAt(0)}
                                    </span>
                                    <span class="max-w-[120px] truncate">${currentUser.name}</span>
                                    <i class="fa-solid fa-chevron-down text-xs text-stone-500"></i>
                                </button>

                                <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-stone-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <div class="px-4 py-2 border-b border-stone-100">
                                        <p class="text-xs text-stone-500">Masuk sebagai:</p>
                                        <p class="text-sm font-semibold text-stone-800 truncate">${currentUser.email}</p>
                                        <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold uppercase rounded-full ${isAdmin ? 'bg-amber-100 text-amber-800' : (isTherapist ? 'bg-teal-100 text-teal-800' : 'bg-emerald-100 text-emerald-800')}">
                                            ${isAdmin ? 'Administrator' : (isTherapist ? 'Terapis Berlisensi' : 'Pelanggan')}
                                        </span>
                                    </div>

                                    ${isTherapist ? `
                                        <a href="${ROOT_PATH}/therapist-portal.html" class="flex items-center px-4 py-2.5 text-sm font-bold text-emerald-800 bg-emerald-50/70 hover:bg-emerald-100 transition-colors">
                                            <i class="fa-solid fa-clipboard-user w-5 text-emerald-600"></i>
                                            <span>Jadwal & Tugas Terapis</span>
                                        </a>
                                    ` : ''}

                                    <a href="${ROOT_PATH}/my-bookings.html" class="flex items-center px-4 py-2.5 text-sm text-stone-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                                        <i class="fa-solid fa-calendar-days w-5 text-emerald-700"></i>
                                        <span>${isTherapist ? 'Riwayat Reservasi Saya' : 'Riwayat Booking Saya'}</span>
                                    </a>

                                    ${isAdmin ? `
                                        <a href="${ROOT_PATH}/admin/dashboard.html" class="flex items-center px-4 py-2.5 text-sm text-stone-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors border-t border-stone-100">
                                            <i class="fa-solid fa-gauge-high w-5 text-emerald-700"></i>
                                            <span>Admin Panel</span>
                                        </a>
                                    ` : ''}

                                    <button onclick="handleLogout()" class="w-full text-left flex items-center px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition-colors border-t border-stone-100">
                                        <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
                                        <span>Keluar (Logout)</span>
                                    </button>
                                </div>
                            </div>
                        ` : `
                            <button onclick="openAuthModal('login')" class="text-stone-700 hover:text-emerald-800 font-semibold text-sm px-4 py-2 rounded-xl transition-colors">
                                Masuk
                            </button>
                        `}

                        <!-- Tombol CTA Booking -->
                        <a href="${ROOT_PATH}/booking.html" class="inline-flex items-center space-x-2 bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md shadow-emerald-900/20 hover:shadow-lg transition-all duration-200 transform active:scale-95">
                            <i class="fa-solid fa-calendar-check text-emerald-300"></i>
                            <span>Reservasi</span>
                        </a>
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <div class="flex items-center space-x-2 md:hidden">
                        <a href="${ROOT_PATH}/booking.html" class="bg-emerald-800 text-white text-xs font-semibold px-3 py-2 rounded-lg">
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
                <a href="${ROOT_PATH}/index.html#home" onclick="toggleMobileMenu()" class="block py-2 text-base font-medium text-stone-700 hover:text-emerald-800">Beranda</a>
                <a href="${ROOT_PATH}/index.html#services" onclick="toggleMobileMenu()" class="block py-2 text-base font-medium text-stone-700 hover:text-emerald-800">Menu Layanan</a>
                <a href="${ROOT_PATH}/index.html#therapists" onclick="toggleMobileMenu()" class="block py-2 text-base font-medium text-stone-700 hover:text-emerald-800">Terapis</a>
                <a href="${ROOT_PATH}/index.html#features" onclick="toggleMobileMenu()" class="block py-2 text-base font-medium text-stone-700 hover:text-emerald-800">Keunggulan</a>
                <a href="${ROOT_PATH}/index.html#faq" onclick="toggleMobileMenu()" class="block py-2 text-base font-medium text-stone-700 hover:text-emerald-800">Bantuan & FAQ</a>

                <div class="pt-3 border-t border-stone-100 space-y-2">
                    <button onclick="openCheckBookingModal(); toggleMobileMenu();" class="w-full text-left py-2 text-sm font-medium text-stone-700 flex items-center space-x-2">
                        <i class="fa-solid fa-receipt text-emerald-700"></i>
                        <span>Cek Status Booking</span>
                    </button>

                    ${currentUser ? `
                        ${isTherapist ? `
                            <a href="${ROOT_PATH}/therapist-portal.html" class="block py-2 text-sm font-bold text-emerald-800 bg-emerald-50 px-3 rounded-lg border border-emerald-200">
                                <i class="fa-solid fa-clipboard-user mr-2 text-emerald-600"></i> Portal Jadwal & Tugas Terapis
                            </a>
                        ` : ''}
                        <a href="${ROOT_PATH}/my-bookings.html" class="block py-2 text-sm font-semibold text-stone-800 hover:text-emerald-800">
                            <i class="fa-solid fa-calendar-days mr-2 text-emerald-700"></i> ${isTherapist ? 'Riwayat Reservasi Saya' : 'Riwayat Booking Saya'}
                        </a>
                        ${isAdmin ? `
                            <a href="${ROOT_PATH}/admin/dashboard.html" class="block py-2 text-sm font-semibold text-emerald-800">
                                <i class="fa-solid fa-gauge-high mr-2"></i> Ke Admin Panel
                            </a>
                        ` : ''}
                        <button onclick="handleLogout()" class="w-full text-left py-2 text-sm font-semibold text-rose-600">
                            <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Keluar (Logout)
                        </button>
                    ` : `
                        <button onclick="openAuthModal('login'); toggleMobileMenu();" class="w-full text-left py-2 text-sm font-semibold text-emerald-800">
                            <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Masuk / Daftar Akun
                        </button>
                    `}
                </div>
            </div>
        </header>
        `;
    }

    // 2. RENDER FOOTER
    function renderFooter() {
        const container = document.getElementById('footer-container');
        if (!container) return;

        container.innerHTML = `
        <footer class="bg-stone-900 text-stone-300 pt-16 pb-12 border-t border-stone-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center">
                                <i class="fa-solid fa-spa text-xl text-emerald-200"></i>
                            </div>
                            <span class="font-serif text-2xl font-bold text-white">Sentosa</span>
                        </div>
                        <p class="text-xs text-stone-400 leading-relaxed">
                            Layanan pijat dan refleksi profesional terstandarisasi hotel bintang lima. Kenyamanan relaksasi di rumah Anda (*Home Service*) atau di studio klinik spa kami.
                        </p>
                        <div class="flex items-center space-x-3 pt-2 text-stone-400">
                            <a href="#" class="w-8 h-8 rounded-lg bg-stone-800 hover:bg-emerald-800 hover:text-white flex items-center justify-center transition-colors"><i class="fa-brands fa-instagram text-xs"></i></a>
                            <a href="#" class="w-8 h-8 rounded-lg bg-stone-800 hover:bg-emerald-800 hover:text-white flex items-center justify-center transition-colors"><i class="fa-brands fa-whatsapp text-xs"></i></a>
                            <a href="#" class="w-8 h-8 rounded-lg bg-stone-800 hover:bg-emerald-800 hover:text-white flex items-center justify-center transition-colors"><i class="fa-brands fa-facebook-f text-xs"></i></a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Navigasi Cepat</h4>
                        <ul class="space-y-2.5 text-xs text-stone-400">
                            <li><a href="${ROOT_PATH}/index.html#home" class="hover:text-emerald-400 transition-colors">Beranda Utama</a></li>
                            <li><a href="${ROOT_PATH}/index.html#services" class="hover:text-emerald-400 transition-colors">Menu Layanan Pijat</a></li>
                            <li><a href="${ROOT_PATH}/index.html#therapists" class="hover:text-emerald-400 transition-colors">Daftar Terapis Bersertifikat</a></li>
                            <li><a href="${ROOT_PATH}/booking.html" class="hover:text-emerald-400 transition-colors font-bold text-emerald-400">Booking Reservasi Sekarang</a></li>
                            <li><a href="javascript:void(0)" onclick="openCheckBookingModal()" class="hover:text-emerald-400 transition-colors">Pelacak Resi Booking</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Layanan Unggulan</h4>
                        <ul class="space-y-2.5 text-xs text-stone-400">
                            <li>Traditional Javanese Massage (90 Menit)</li>
                            <li>Deep Tissue & Shiatsu Therapy (90 Menit)</li>
                            <li>Reflexology & Foot Acupressure (60 Menit)</li>
                            <li>Aromatherapy & Herbal Compress (120 Menit)</li>
                            <li>Express Back, Neck & Shoulder Relief</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Kontak & Jam Kerja</h4>
                        <ul class="space-y-3 text-xs text-stone-400">
                            <li class="flex items-start space-x-2.5">
                                <i class="fa-solid fa-location-dot text-emerald-500 mt-0.5"></i>
                                <span>Jl. Senopati No. 45, Kebayoran Baru, Jakarta Selatan</span>
                            </li>
                            <li class="flex items-center space-x-2.5">
                                <i class="fa-brands fa-whatsapp text-emerald-500 text-sm"></i>
                                <span>0812-3456-7890 (Customer Care 24 Jam)</span>
                            </li>
                            <li class="flex items-center space-x-2.5">
                                <i class="fa-solid fa-clock text-emerald-500"></i>
                                <span>Setiap Hari: 08.00 - 22.00 WIB</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="pt-8 border-t border-stone-800/80 text-center text-xs text-stone-500 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p>&copy; 2026 Sentosa Wellness & Massage Spa. All rights reserved.</p>
                    <p class="text-[11px] text-stone-600">Deploy Ready on Vercel • Client-Side LocalStorage Architecture</p>
                </div>
            </div>
        </footer>

        <!-- Floating WhatsApp Widget -->
        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Sentosa%20Spa,%20saya%20ingin%20tanya%20mengenai%20reservasi%20layanan%20pijat" target="_blank" class="fixed bottom-6 right-6 z-40 bg-emerald-600 hover:bg-emerald-700 text-white w-14 h-14 rounded-full shadow-2xl flex items-center justify-center text-2xl transition-all duration-200 transform hover:scale-110 active:scale-95 group" title="Chat WhatsApp Customer Care">
            <i class="fa-brands fa-whatsapp"></i>
            <span class="absolute right-16 bg-stone-900 text-white text-xs px-3 py-1.5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg pointer-events-none">
                Butuh Bantuan? Chat Kami
            </span>
        </a>
        `;
    }

    // 3. RENDER MODALS (AUTH & TRACKING)
    function renderModals() {
        const container = document.getElementById('modals-container');
        if (!container) return;

        container.innerHTML = `
        <!-- Modal Login & Register -->
        <div id="authModal" class="fixed inset-0 z-50 hidden bg-stone-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-stone-100 overflow-hidden transform transition-all duration-300">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-xl bg-emerald-800 text-white flex items-center justify-center">
                                <i class="fa-solid fa-spa text-emerald-300 text-sm"></i>
                            </div>
                            <span class="font-serif text-lg font-bold text-stone-900">Sentosa Spa</span>
                        </div>
                        <button onclick="closeAuthModal()" class="w-8 h-8 rounded-full bg-stone-100 text-stone-500 hover:text-stone-800 hover:bg-stone-200 flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <!-- Auth Tabs -->
                    <div class="flex border-b border-stone-200 mb-6">
                        <button id="tabLoginBtn" onclick="switchAuthTab('login')" class="flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-emerald-800 text-emerald-900 transition-colors">
                            Masuk
                        </button>
                        <button id="tabRegisterBtn" onclick="switchAuthTab('register')" class="flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-transparent text-stone-400 hover:text-stone-600 transition-colors">
                            Daftar Baru
                        </button>
                    </div>

                    <!-- Form Login -->
                    <form id="loginForm" onsubmit="submitLogin(event)" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Email atau No. WhatsApp</label>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-3.5 top-3 text-stone-400 text-sm"></i>
                                <input type="text" id="loginIdentifier" required placeholder="nama@email.com atau 0812..." class="w-full pl-10 pr-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-800 focus:outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Password</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3.5 top-3 text-stone-400 text-sm"></i>
                                <input type="password" id="loginPassword" required placeholder="••••••••" class="w-full pl-10 pr-10 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-800 focus:outline-none transition-all">
                                <button type="button" onclick="togglePasswordVisibility('loginPassword', 'loginEyeIcon')" class="absolute right-3 top-3 text-stone-400 hover:text-stone-700">
                                    <i class="fa-solid fa-eye text-sm" id="loginEyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="loginSubmitBtn" class="w-full py-3 bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-bold rounded-xl shadow-md transition-all flex items-center justify-center space-x-2">
                            <span>Masuk Sekarang</span>
                        </button>
                    </form>

                    <!-- Form Register -->
                    <form id="registerForm" onsubmit="submitRegister(event)" class="space-y-3.5 hidden">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Nama Lengkap</label>
                            <input type="text" id="regName" required placeholder="Nama Anda" class="w-full px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-800 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Alamat Email</label>
                            <input type="email" id="regEmail" required placeholder="nama@email.com" class="w-full px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-800 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Nomor WhatsApp</label>
                            <input type="tel" id="regPhone" required placeholder="081234567890" class="w-full px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-800 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Password</label>
                            <input type="password" id="regPassword" required placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-800 focus:outline-none">
                        </div>
                        <button type="submit" id="regSubmitBtn" class="w-full py-3 bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-bold rounded-xl shadow-md transition-all flex items-center justify-center space-x-2">
                            <span>Buat Akun Pelanggan</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Cek Status Booking Cepat -->
        <div id="checkBookingModal" class="fixed inset-0 z-50 hidden bg-stone-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-stone-100 overflow-hidden transform transition-all duration-300">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-xl bg-emerald-800 text-white flex items-center justify-center">
                                <i class="fa-solid fa-receipt text-emerald-300 text-sm"></i>
                            </div>
                            <h3 class="font-serif text-lg font-bold text-stone-900">Lacak Reservasi</h3>
                        </div>
                        <button onclick="closeCheckBookingModal()" class="w-8 h-8 rounded-full bg-stone-100 text-stone-500 hover:text-stone-800 hover:bg-stone-200 flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                    <p class="text-xs text-stone-500 mb-5">
                        Masukkan kode reservasi unik Anda (contoh: <code>BKG-260901-7891</code>) untuk melihat status konfirmasi dan jadwal terapis.
                    </p>

                    <form onsubmit="submitCheckBooking(event)" class="space-y-4">
                        <div>
                            <input type="text" id="checkBookingCodeInput" required placeholder="BKG-XXXXXX-XXXX" class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-sm font-mono tracking-wider uppercase focus:ring-2 focus:ring-emerald-800 focus:outline-none">
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold rounded-xl shadow transition-all">
                            Periksa Status Sekarang
                        </button>
                    </form>

                    <!-- Container Hasil Pencarian -->
                    <div id="checkBookingResult" class="mt-5 hidden pt-5 border-t border-stone-200"></div>
                </div>
            </div>
        </div>
        `;
    }

    // 4. MODAL LOGIC & ACTIONS
    window.openAuthModal = function (tab = 'login') {
        const modal = document.getElementById('authModal');
        if (modal) {
            modal.classList.remove('hidden');
            switchAuthTab(tab);
        }
    };

    window.closeAuthModal = function () {
        const modal = document.getElementById('authModal');
        if (modal) modal.classList.add('hidden');
    };

    window.switchAuthTab = function (tab) {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const tabLoginBtn = document.getElementById('tabLoginBtn');
        const tabRegisterBtn = document.getElementById('tabRegisterBtn');

        if (!loginForm || !registerForm) return;

        if (tab === 'login') {
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
            tabLoginBtn.className = 'flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-emerald-800 text-emerald-900 transition-colors';
            tabRegisterBtn.className = 'flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-transparent text-stone-400 hover:text-stone-600 transition-colors';
        } else {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            tabLoginBtn.className = 'flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-transparent text-stone-400 hover:text-stone-600 transition-colors';
            tabRegisterBtn.className = 'flex-1 pb-3 text-sm font-semibold text-center border-b-2 border-emerald-800 text-emerald-900 transition-colors';
        }
    };

    window.submitLogin = function (e) {
        e.preventDefault();
        const ident = document.getElementById('loginIdentifier').value;
        const pass = document.getElementById('loginPassword').value;

        const res = SentosaStore.login(ident, pass);
        if (res.success) {
            SentosaStore.showToast(res.message, 'success');
            closeAuthModal();

            setTimeout(() => {
                if (res.user.role === 'admin') {
                    window.location.href = `${ROOT_PATH}/admin/dashboard.html`;
                } else if (res.user.role === 'therapist') {
                    window.location.href = `${ROOT_PATH}/therapist-portal.html`;
                } else {
                    window.location.reload();
                }
            }, 500);
        } else {
            SentosaStore.showToast(res.message, 'error');
        }
    };

    window.submitRegister = function (e) {
        e.preventDefault();
        const name = document.getElementById('regName').value;
        const email = document.getElementById('regEmail').value;
        const phone = document.getElementById('regPhone').value;
        const pass = document.getElementById('regPassword').value;

        const res = SentosaStore.register(name, email, phone, pass);
        if (res.success) {
            SentosaStore.showToast(res.message, 'success');
            closeAuthModal();
            setTimeout(() => window.location.reload(), 600);
        } else {
            SentosaStore.showToast(res.message, 'error');
        }
    };

    window.handleLogout = function () {
        SentosaStore.logout();
        SentosaStore.showToast('Anda telah berhasil keluar.', 'info');
        setTimeout(() => {
            window.location.href = `${ROOT_PATH}/index.html`;
        }, 500);
    };

    window.togglePasswordVisibility = function (inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    };

    window.toggleMobileMenu = function () {
        const menu = document.getElementById('mobileMenu');
        const icon = document.getElementById('menuIcon');
        if (!menu) return;

        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            if (icon) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            }
        } else {
            menu.classList.add('hidden');
            if (icon) {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        }
    };

    // Tracking Modal Logic
    window.openCheckBookingModal = function () {
        const modal = document.getElementById('checkBookingModal');
        if (modal) modal.classList.remove('hidden');
    };

    window.closeCheckBookingModal = function () {
        const modal = document.getElementById('checkBookingModal');
        if (modal) modal.classList.add('hidden');
    };

    window.submitCheckBooking = function (e) {
        e.preventDefault();
        const code = document.getElementById('checkBookingCodeInput').value.trim();
        const resBox = document.getElementById('checkBookingResult');
        if (!resBox) return;

        const booking = SentosaStore.getBookingByCode(code);
        if (!booking) {
            resBox.classList.remove('hidden');
            resBox.innerHTML = `
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-center text-xs text-rose-700">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> Reservasi dengan kode <strong>${code}</strong> tidak ditemukan.
                </div>
            `;
            return;
        }

        const statusBadges = {
            pending: '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menunggu Konfirmasi</span>',
            confirmed: '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Dikonfirmasi</span>',
            on_process: '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 animate-pulse">Sedang Berlangsung</span>',
            completed: '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Selesai</span>',
            cancelled: '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Dibatalkan</span>'
        };

        resBox.classList.remove('hidden');
        resBox.innerHTML = `
            <div class="space-y-3 bg-stone-50 p-4 rounded-2xl border border-stone-200 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-mono font-bold text-stone-900">${booking.booking_code}</span>
                    ${statusBadges[booking.status] || booking.status}
                </div>
                <div><span class="text-stone-500 block text-[10px] uppercase font-bold">Layanan:</span>${booking.service_name} (${booking.duration} Menit)</div>
                <div><span class="text-stone-500 block text-[10px] uppercase font-bold">Jadwal:</span>${SentosaStore.formatDateTime(booking.schedule_datetime)}</div>
                <div><span class="text-stone-500 block text-[10px] uppercase font-bold">Terapis:</span>${booking.therapist_name ? `${booking.therapist_name} (${booking.therapist_gender === 'female' ? 'Wanita' : 'Pria'})` : '<em class="text-stone-400">Sedang Ditugaskan oleh Admin</em>'}</div>
                <div><span class="text-stone-500 block text-[10px] uppercase font-bold">Total Biaya:</span><strong>${SentosaStore.formatRupiah(booking.total_price)}</strong> (${booking.payment_status === 'paid' ? '<span class="text-emerald-700 font-bold">Lunas</span>' : '<span class="text-amber-700">Belum Bayar</span>'})</div>
                ${booking.booking_type === 'home_service' && booking.address ? `<div><span class="text-stone-500 block text-[10px] uppercase font-bold">Alamat:</span>${booking.address}</div>` : ''}
            </div>
        `;
    };

    // Auto-init UI components
    document.addEventListener('DOMContentLoaded', () => {
        renderNavbar();
        renderFooter();
        renderModals();
    });
})();
