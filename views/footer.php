<?php
// ==========================================================
// Views Partial: Footer & Floating WhatsApp Widget
// ==========================================================
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/database.php';
}

/** @var string|null $baseUrl */
$baseUrl = isset($baseUrl) ? $baseUrl : (function_exists('getBaseUrl') ? getBaseUrl() : '');
$footRoot = !empty($baseUrl) ? $baseUrl : '.';
?>
<footer class="bg-stone-900 text-stone-300 mt-auto border-t border-stone-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            <!-- Brand Column -->
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-700 text-white flex items-center justify-center">
                        <i class="fa-solid fa-spa text-xl text-emerald-300"></i>
                    </div>
                    <div>
                        <span class="block font-serif text-2xl font-bold text-white leading-none">Sentosa</span>
                        <span class="block text-[10px] font-bold uppercase tracking-widest text-emerald-400 mt-1">Wellness & Spa</span>
                    </div>
                </div>
                <p class="text-stone-400 text-sm leading-relaxed">
                    Menghadirkan relaksasi alami dan kesegaran tubuh paripurna langsung ke kediaman, apartemen, atau kamar hotel Anda dengan standar higienis dan kenyamanan bintang lima (100% Home Service).
                </p>
                <div class="flex space-x-4 pt-2">
                    <a href="#" class="w-9 h-9 rounded-full bg-stone-800 hover:bg-brand-700 flex items-center justify-center text-stone-300 hover:text-white transition-colors">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-stone-800 hover:bg-brand-700 flex items-center justify-center text-stone-300 hover:text-white transition-colors">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-stone-800 hover:bg-brand-700 flex items-center justify-center text-stone-300 hover:text-white transition-colors">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                </div>
            </div>

            <!-- Layanan Pijat -->
            <div>
                <h4 class="text-white font-semibold text-base mb-4 tracking-wide uppercase text-xs text-emerald-400">Pilihan Layanan</h4>
                <ul class="space-y-2.5 text-sm text-stone-400">
                    <li><a href="<?= $footRoot ?>/booking.php?service_id=1" class="hover:text-emerald-400 transition-colors">Traditional Javanese Massage</a></li>
                    <li><a href="<?= $footRoot ?>/booking.php?service_id=2" class="hover:text-emerald-400 transition-colors">Deep Tissue & Shiatsu Therapy</a></li>
                    <li><a href="<?= $footRoot ?>/booking.php?service_id=3" class="hover:text-emerald-400 transition-colors">Reflexology & Foot Acupressure</a></li>
                    <li><a href="<?= $footRoot ?>/booking.php?service_id=4" class="hover:text-emerald-400 transition-colors">Aromatherapy Herbal Compress</a></li>
                    <li><a href="<?= $footRoot ?>/booking.php?service_id=5" class="hover:text-emerald-400 transition-colors">Express Back & Shoulder Relief</a></li>
                </ul>
            </div>

            <!-- Jam & Jangkauan -->
            <div>
                <h4 class="text-white font-semibold text-base mb-4 tracking-wide uppercase text-xs text-emerald-400">Operasional & Area</h4>
                <div class="space-y-3 text-sm text-stone-400">
                    <div class="flex items-start space-x-3">
                        <i class="fa-regular fa-clock text-emerald-400 mt-1"></i>
                        <div>
                            <span class="block text-white font-medium">Jam Layanan:</span>
                            <span><?= APP_HOURS ?></span>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fa-solid fa-map-location-dot text-emerald-400 mt-1"></i>
                        <div>
                            <span class="block text-white font-medium">Cakupan Home Service:</span>
                            <span>Jakarta Selatan, Jakarta Pusat, Jakarta Barat, Tangerang Selatan</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kontak & Lokasi Studio -->
            <div>
                <h4 class="text-white font-semibold text-base mb-4 tracking-wide uppercase text-xs text-emerald-400">Kontak Studio</h4>
                <ul class="space-y-3 text-sm text-stone-400">
                    <li class="flex items-start space-x-3">
                        <i class="fa-solid fa-location-dot text-emerald-400 mt-1"></i>
                        <span><?= APP_ADDRESS ?></span>
                    </li>
                    <li class="flex items-center space-x-3">
                        <i class="fa-solid fa-phone text-emerald-400"></i>
                        <span>+<?= APP_PHONE ?></span>
                    </li>
                    <li class="flex items-center space-x-3">
                        <i class="fa-solid fa-envelope text-emerald-400"></i>
                        <span><?= APP_EMAIL ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-stone-800 text-center text-xs text-stone-400 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Hak Cipta Dilindungi.</p>
            <div class="flex space-x-6">
                <a href="#" class="hover:text-stone-300">Syarat & Ketentuan</a>
                <a href="#" class="hover:text-stone-300">Kebijakan Privasi</a>
                <a href="<?= $footRoot ?>/admin/dashboard.php" class="hover:text-emerald-400 text-stone-400">Portal Admin</a>
            </div>
        </div>
    </div>
</footer>

<!-- Floating WhatsApp Action Button -->
<a href="https://wa.me/<?= APP_PHONE ?>?text=<?= urlencode('Halo Admin Sentosa Spa, saya ingin bertanya seputar layanan dan reservasi pijat.') ?>"
    target="_blank"
    rel="noopener noreferrer"
    class="fixed bottom-6 right-6 z-40 bg-emerald-700 hover:bg-emerald-800 text-white rounded-full p-4 shadow-2xl flex items-center space-x-3 group transition-all duration-300 hover:scale-105"
    title="Konsultasi Langsung via WhatsApp">
    <i class="fa-brands fa-whatsapp text-2xl"></i>
    <span class="max-w-0 overflow-hidden whitespace-nowrap group-hover:max-w-xs transition-all duration-300 ease-in-out text-sm font-semibold pr-1">
        Chat WhatsApp Admin
    </span>
</a>

<!-- Include Modals -->
<?php require_once __DIR__ . '/auth_modal.php'; ?>

</body>

</html>