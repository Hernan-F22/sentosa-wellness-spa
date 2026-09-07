<?php
// ==========================================================
// Views Partial: Header (Meta, Stylesheets, Fonts, Toast)
// ==========================================================
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/database.php';
}
startSession();
$currentUser = getCurrentUser();
$pageTitle = $pageTitle ?? APP_NAME . ' - ' . APP_TAGLINE;
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
// Pastikan baseUrl menunjuk ke root jika sedang di subfolder seperti /admin
if (basename($baseUrl) === 'admin') {
    $baseUrl = dirname($baseUrl);
}
if ($baseUrl === '/' || $baseUrl === '\\') {
    $baseUrl = '';
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Meta SEO & Theme -->
    <meta name="description" content="Layanan pijat dan spa panggilan profesional berstandar bintang lima (100% Home Service). Terapis terpercaya datang langsung ke rumah, apartemen, atau kamar hotel Anda.">
    <meta name="theme-color" content="#064e3b">

    <!-- Google Fonts: Plus Jakarta Sans (Modern Clean) & Playfair Display (Luxury Wellness) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                            950: '#052e16',
                        },
                        amber: {
                            warm: '#d97706',
                            gold: '#b45309',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom smooth scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-stone-50 text-stone-800 font-sans antialiased min-h-screen flex flex-col selection:bg-brand-100 selection:text-brand-900">

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-50 flex flex-col space-y-3 pointer-events-none max-w-sm w-full"></div>

    <script>
        // Global Toast Notification Helper
        function showToast(message, type = 'success', duration = 3500) {
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
                <button onclick="this.parentElement.remove()" class="text-white/60 hover:text-white ml-2 text-base">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            container.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-[-10px]', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });

            // Auto dismiss
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // Tangani parameter redirect notifikasi
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.get('login_required') === '1') {
                showToast('Silakan masuk ke akun Anda terlebih dahulu.', 'warning');
                if (typeof openAuthModal === 'function') {
                    setTimeout(() => openAuthModal('login'), 300);
                }
            } else if (params.get('unauthorized') === '1') {
                showToast('Akses ditolak. Fitur ini memerlukan hak akses khusus.', 'error');
            }
        });
    </script>