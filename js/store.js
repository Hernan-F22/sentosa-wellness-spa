/**
 * Sentosa Spa - Client-Side Store & State Management
 * Pure Vanilla JavaScript + LocalStorage
 * Replaces PHP backend for 100% serverless deployment on Vercel
 */

(function () {
    const STORAGE_PREFIX = 'sentosa_';

    // 1. DATA AWAL (SEED DATA)
    const SEED_USERS = [
        {
            id: 1,
            name: 'Administrator Spa',
            email: 'admin@spa.com',
            phone: '081234567890',
            password: 'admin123',
            role: 'admin',
            created_at: '2026-08-01 10:00:00'
        },
        {
            id: 2,
            name: 'Siti Nurhaliza',
            email: 'siti@spa.com',
            phone: '081234567801',
            password: 'user123',
            role: 'therapist',
            created_at: '2026-08-05 09:00:00'
        },
        {
            id: 3,
            name: 'Budi Santoso',
            email: 'budi@spa.com',
            phone: '081234567802',
            password: 'user123',
            role: 'therapist',
            created_at: '2026-08-05 09:00:00'
        },
        {
            id: 4,
            name: 'Dewi Lestari',
            email: 'dewi@spa.com',
            phone: '081234567803',
            password: 'user123',
            role: 'therapist',
            created_at: '2026-08-05 09:00:00'
        },
        {
            id: 5,
            name: 'Ahmad Fauzi',
            email: 'ahmad@spa.com',
            phone: '081234567804',
            password: 'user123',
            role: 'therapist',
            created_at: '2026-08-05 09:00:00'
        },
        {
            id: 6,
            name: 'Bambang Wijaya',
            email: 'bambang@gmail.com',
            phone: '081298765432',
            password: 'user123',
            role: 'customer',
            created_at: '2026-08-10 11:30:00'
        },
        {
            id: 7,
            name: 'Sarah Amalia',
            email: 'sarah@gmail.com',
            phone: '081388776655',
            password: 'user123',
            role: 'customer',
            created_at: '2026-08-12 14:20:00'
        }
    ];

    const SEED_THERAPISTS = [
        {
            id: 1,
            user_id: 2,
            specialization: 'Pijat Tradisional Jawa & Refleksi Kaki Relaksasi',
            gender: 'female',
            is_available: 1,
            rating: 4.95,
            created_at: '2026-08-05 09:00:00'
        },
        {
            id: 2,
            user_id: 3,
            specialization: 'Deep Tissue Massage & Shiatsu Terapi Otot Kaku',
            gender: 'male',
            is_available: 1,
            rating: 4.88,
            created_at: '2026-08-05 09:00:00'
        },
        {
            id: 3,
            user_id: 4,
            specialization: 'Aromatherapy Herbal & Hot Stone Wellness Spa',
            gender: 'female',
            is_available: 1,
            rating: 4.98,
            created_at: '2026-08-05 09:00:00'
        },
        {
            id: 4,
            user_id: 5,
            specialization: 'Sports Massage & Peregangan Cedera Ringan',
            gender: 'male',
            is_available: 1,
            rating: 4.85,
            created_at: '2026-08-05 09:00:00'
        }
    ];

    const SEED_SERVICES = [
        {
            id: 1,
            name: 'Traditional Javanese Massage',
            duration_minutes: 90,
            price: 185000,
            description: 'Pijat relaksasi tubuh tradisional khas Jawa dengan teknik urut dan minyak esensial alami untuk melancarkan sirkulasi darah serta meredakan ketegangan otot.',
            type: 'both',
            image_url: 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800&auto=format&fit=crop&q=80',
            is_active: 1,
            created_at: '2026-08-01 10:00:00'
        },
        {
            id: 2,
            name: 'Deep Tissue & Shiatsu Therapy',
            duration_minutes: 90,
            price: 225000,
            description: 'Kombinasi teknik pijatan dengan tekanan mendalam dan akupresur titik saraf Jepang. Sangat efektif untuk mengatasi otot kaku kronis, leher kaku, dan migrain.',
            type: 'clinic_only',
            image_url: 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=800&auto=format&fit=crop&q=80',
            is_active: 1,
            created_at: '2026-08-01 10:00:00'
        },
        {
            id: 3,
            name: 'Reflexology & Foot Acupressure',
            duration_minutes: 60,
            price: 125000,
            description: 'Terapi pemijatan titik-titik refleksi pada telapak kaki dan tangan untuk menstimulasi organ vital tubuh, meredakan stres, dan memulihkan stamina.',
            type: 'both',
            image_url: 'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?w=800&auto=format&fit=crop&q=80',
            is_active: 1,
            created_at: '2026-08-01 10:00:00'
        },
        {
            id: 4,
            name: 'Aromatherapy & Herbal Warm Compress',
            duration_minutes: 120,
            price: 280000,
            description: 'Perawatan tubuh menyeluruh menggunakan paduan minyak aroma terapi lavender/eucalyptus dan kompres rempah herbal hangat nusantara untuk detoksifikasi optimal.',
            type: 'both',
            image_url: 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?w=800&auto=format&fit=crop&q=80',
            is_active: 1,
            created_at: '2026-08-01 10:00:00'
        },
        {
            id: 5,
            name: 'Express Back, Neck & Shoulder Relief',
            duration_minutes: 45,
            price: 95000,
            description: 'Pijat intensif fokus pada leher, bahu, dan punggung atas. Solusi kilat dan manjur untuk Anda yang lelah bekerja di depan meja laptop seharian.',
            type: 'both',
            image_url: 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800&auto=format&fit=crop&q=80',
            is_active: 1,
            created_at: '2026-08-01 10:00:00'
        },
        {
            id: 6,
            name: 'Post-Workout Sports Massage',
            duration_minutes: 75,
            price: 195000,
            description: 'Terapi relaksasi khusus bagi penggiat olahraga untuk meregangkan serat otot tegang, mencegah kram, dan mempercepat pembuangan asam laktat.',
            type: 'clinic_only',
            image_url: 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=800&auto=format&fit=crop&q=80',
            is_active: 1,
            created_at: '2026-08-01 10:00:00'
        }
    ];

    const SEED_BOOKINGS = [
        {
            id: 1,
            booking_code: 'BKG-260901-7891',
            customer_id: 6,
            therapist_id: 1,
            service_id: 1,
            booking_type: 'home_service',
            address: 'Jl. Gandaria Tengah II No. 14, Kebayoran Baru, Jakarta Selatan (Pagar Hitam, seberang masjid)',
            schedule_datetime: '2026-09-08 14:00:00',
            duration: 90,
            total_price: 185000,
            status: 'confirmed',
            payment_status: 'paid',
            notes: 'Mohon fokus pijat area belikat dan pinggang kiri.',
            created_at: '2026-09-01 10:00:00'
        },
        {
            id: 2,
            booking_code: 'BKG-260902-3412',
            customer_id: 7,
            therapist_id: 2,
            service_id: 2,
            booking_type: 'clinic',
            address: null,
            schedule_datetime: '2026-09-08 16:30:00',
            duration: 90,
            total_price: 225000,
            status: 'on_process',
            payment_status: 'paid',
            notes: 'Tekanan pijatan preferensi sedang ke kuat.',
            created_at: '2026-09-02 11:30:00'
        },
        {
            id: 3,
            booking_code: 'BKG-260902-9981',
            customer_id: 6,
            therapist_id: null,
            service_id: 3,
            booking_type: 'home_service',
            address: 'Apartemen Sudirman Tower Lt. 12 Unit 12B, Jakarta Pusat',
            schedule_datetime: '2026-09-09 10:00:00',
            duration: 60,
            total_price: 125000,
            status: 'pending',
            payment_status: 'unpaid',
            notes: 'Terapis mohon bawa minyak kayu putih bila ada.',
            created_at: '2026-09-02 12:00:00'
        },
        {
            id: 4,
            booking_code: 'BKG-260831-1120',
            customer_id: 7,
            therapist_id: 3,
            service_id: 4,
            booking_type: 'clinic',
            address: null,
            schedule_datetime: '2026-08-31 15:00:00',
            duration: 120,
            total_price: 280000,
            status: 'completed',
            payment_status: 'paid',
            notes: 'Sangat puas dengan pelayanannya.',
            created_at: '2026-08-30 09:00:00'
        }
    ];

    const SEED_REVIEWS = [
        {
            id: 1,
            booking_id: 4,
            customer_id: 7,
            therapist_id: 3,
            rating: 5,
            comment: 'Pelayanan Ibu Dewi sangat profesional! Pijatan aromaterapi dan batu hangatnya benar-benar meredakan pegal setelah perjalanan jauh. Sangat direkomendasikan!',
            created_at: '2026-08-31 18:00:00'
        }
    ];

    // 2. HELPER LOCALSTORAGE
    function getItem(key, defaultValue = null) {
        try {
            const val = localStorage.getItem(STORAGE_PREFIX + key);
            return val ? JSON.parse(val) : defaultValue;
        } catch (e) {
            console.error('Error reading localStorage', e);
            return defaultValue;
        }
    }

    function setItem(key, value) {
        try {
            localStorage.setItem(STORAGE_PREFIX + key, JSON.stringify(value));
        } catch (e) {
            console.error('Error writing to localStorage', e);
        }
    }

    // 3. STORE INITIALIZATION
    function initStore() {
        if (!localStorage.getItem(STORAGE_PREFIX + 'initialized_v1')) {
            setItem('users', SEED_USERS);
            setItem('therapists', SEED_THERAPISTS);
            setItem('services', SEED_SERVICES);
            setItem('bookings', SEED_BOOKINGS);
            setItem('reviews', SEED_REVIEWS);
            localStorage.setItem(STORAGE_PREFIX + 'initialized_v1', 'true');
        }
    }

    // 4. AUTHENTICATION & USER MANAGEMENT
    function getCurrentUser() {
        return getItem('current_user', null);
    }

    function setCurrentUser(user) {
        if (user) {
            setItem('current_user', user);
        } else {
            localStorage.removeItem(STORAGE_PREFIX + 'current_user');
        }
    }

    function login(identifier, password) {
        initStore();
        const users = getItem('users', []);
        const cleanIdent = String(identifier).trim().toLowerCase();
        const cleanPass = String(password).trim();

        const user = users.find(u =>
            (u.email.toLowerCase() === cleanIdent || u.phone.replace(/[^0-9]/g, '') === cleanIdent.replace(/[^0-9]/g, '')) &&
            u.password === cleanPass
        );

        if (!user) {
            return { success: false, message: 'Email/No. HP atau password salah.' };
        }

        const sessionUser = {
            id: user.id,
            name: user.name,
            email: user.email,
            phone: user.phone,
            role: user.role
        };
        setCurrentUser(sessionUser);

        return {
            success: true,
            user: sessionUser,
            message: `Selamat datang kembali, ${user.name}!`
        };
    }

    function register(name, email, phone, password) {
        initStore();
        const users = getItem('users', []);
        const cleanEmail = String(email).trim().toLowerCase();

        if (users.some(u => u.email.toLowerCase() === cleanEmail)) {
            return { success: false, message: 'Alamat email sudah terdaftar. Silakan gunakan email lain atau login.' };
        }

        const newUser = {
            id: Date.now(),
            name: String(name).trim(),
            email: cleanEmail,
            phone: String(phone).trim(),
            password: String(password).trim(),
            role: 'customer',
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
        };

        users.push(newUser);
        setItem('users', users);

        const sessionUser = {
            id: newUser.id,
            name: newUser.name,
            email: newUser.email,
            phone: newUser.phone,
            role: newUser.role
        };
        setCurrentUser(sessionUser);

        return {
            success: true,
            user: sessionUser,
            message: 'Pendaftaran akun berhasil! Anda otomatis masuk.'
        };
    }

    function logout() {
        setCurrentUser(null);
        localStorage.removeItem(STORAGE_PREFIX + 'admin_preview_therapist_id');
        return { success: true, message: 'Anda telah berhasil keluar.' };
    }

    function changePassword(userId, currentPassword, newPassword) {
        initStore();
        const users = getItem('users', []);
        const userIndex = users.findIndex(u => u.id === Number(userId));
        if (userIndex === -1) {
            return { success: false, message: 'Pengguna tidak ditemukan.' };
        }
        if (users[userIndex].password !== String(currentPassword).trim()) {
            return { success: false, message: 'Kata sandi saat ini salah.' };
        }
        if (!newPassword || newPassword.length < 6) {
            return { success: false, message: 'Kata sandi baru minimal harus 6 karakter.' };
        }
        users[userIndex].password = String(newPassword).trim();
        setItem('users', users);
        return { success: true, message: 'Kata sandi berhasil diperbarui!' };
    }

    // 5. SERVICES
    function getServices(filterActiveOnly = false) {
        initStore();
        let services = getItem('services', []);
        if (filterActiveOnly) {
            services = services.filter(s => s.is_active == 1);
        }
        return services;
    }

    function getServiceById(id) {
        const services = getServices();
        return services.find(s => s.id == id) || null;
    }

    function saveService(data) {
        initStore();
        const services = getItem('services', []);
        if (data.id) {
            const index = services.findIndex(s => s.id == data.id);
            if (index !== -1) {
                services[index] = { ...services[index], ...data };
                setItem('services', services);
                return { success: true, service: services[index], message: 'Layanan berhasil diperbarui.' };
            }
        }
        const newService = {
            ...data,
            id: Date.now(),
            is_active: data.is_active !== undefined ? data.is_active : 1,
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
        };
        services.push(newService);
        setItem('services', services);
        return { success: true, service: newService, message: 'Layanan baru berhasil ditambahkan.' };
    }

    function deleteService(id) {
        initStore();
        let services = getItem('services', []);
        services = services.filter(s => s.id != id);
        setItem('services', services);
        return { success: true, message: 'Layanan berhasil dihapus.' };
    }

    // 6. THERAPISTS & AUTOMATIC RATING CALCULATION
    function getTherapists() {
        initStore();
        const therapists = getItem('therapists', []);
        const users = getItem('users', []);
        const reviews = getItem('reviews', []);

        return therapists.map(t => {
            const user = users.find(u => u.id == t.user_id) || {};
            const tReviews = reviews.filter(r => r.therapist_id == t.id);
            const totalReviews = tReviews.length;

            let rating = 5.0;
            if (totalReviews > 0) {
                const sum = tReviews.reduce((acc, curr) => acc + Number(curr.rating), 0);
                rating = Number((sum / totalReviews).toFixed(2));
            } else if (t.rating) {
                rating = Number(t.rating);
            }

            return {
                ...t,
                name: user.name || 'Terapis',
                email: user.email || '',
                phone: user.phone || '',
                rating: rating,
                rating_formatted: rating.toFixed(1),
                total_reviews: totalReviews
            };
        });
    }

    function getTherapistById(id) {
        const therapists = getTherapists();
        return therapists.find(t => t.id == id) || null;
    }

    function getTherapistByUserId(userId) {
        const therapists = getTherapists();
        return therapists.find(t => t.user_id == userId) || null;
    }

    function saveTherapist(data) {
        initStore();
        const therapists = getItem('therapists', []);
        const users = getItem('users', []);

        if (data.id) {
            // Update existing therapist
            const tIndex = therapists.findIndex(t => t.id == data.id);
            if (tIndex !== -1) {
                const t = therapists[tIndex];
                const uIndex = users.findIndex(u => u.id == t.user_id);
                if (uIndex !== -1) {
                    users[uIndex].name = data.name || users[uIndex].name;
                    users[uIndex].email = data.email || users[uIndex].email;
                    users[uIndex].phone = data.phone || users[uIndex].phone;
                    if (data.password && data.password.trim().length > 0) {
                        users[uIndex].password = data.password.trim();
                    }
                }
                therapists[tIndex].specialization = data.specialization || t.specialization;
                therapists[tIndex].gender = data.gender || t.gender;
                if (data.is_available !== undefined) {
                    therapists[tIndex].is_available = data.is_available;
                }
                setItem('users', users);
                setItem('therapists', therapists);
                return { success: true, message: 'Data terapis berhasil diperbarui.' };
            }
        }

        // Create new therapist + user
        const newUserId = Date.now();
        const newTherapistId = (therapists.length > 0 ? Math.max(...therapists.map(t => t.id)) : 0) + 1;
        const newPassword = (data.password && data.password.trim()) ? data.password.trim() : 'therapist123';

        const newUser = {
            id: newUserId,
            name: String(data.name).trim(),
            email: String(data.email).trim().toLowerCase(),
            phone: String(data.phone).trim(),
            password: newPassword,
            role: 'therapist',
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
        };

        const newTherapist = {
            id: newTherapistId,
            user_id: newUserId,
            specialization: String(data.specialization).trim(),
            gender: data.gender || 'female',
            is_available: 1,
            rating: 5.0,
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
        };

        users.push(newUser);
        therapists.push(newTherapist);
        setItem('users', users);
        setItem('therapists', therapists);

        return {
            success: true,
            therapist: newTherapist,
            credentials: { email: newUser.email, password: newPassword },
            message: 'Terapis dan akun login berhasil ditambahkan!'
        };
    }

    function deleteTherapist(id) {
        initStore();
        let therapists = getItem('therapists', []);
        let users = getItem('users', []);

        const target = therapists.find(t => t.id == id);
        if (target) {
            users = users.filter(u => u.id != target.user_id);
            therapists = therapists.filter(t => t.id != id);
            setItem('users', users);
            setItem('therapists', therapists);
            return { success: true, message: 'Terapis berhasil dihapus.' };
        }
        return { success: false, message: 'Terapis tidak ditemukan.' };
    }

    function toggleTherapistAvailability(id, status) {
        initStore();
        const therapists = getItem('therapists', []);
        const t = therapists.find(item => item.id == id);
        if (t) {
            t.is_available = (status === undefined) ? (t.is_available == 1 ? 0 : 1) : (status ? 1 : 0);
            setItem('therapists', therapists);
            return {
                success: true,
                is_available: t.is_available,
                message: `Status kerja ${t.is_available == 1 ? 'Tersedia' : 'Sedang Libur'} berhasil disimpan.`
            };
        }
        return { success: false, message: 'Terapis tidak ditemukan.' };
    }

    // 7. BOOKINGS & ASSIGNMENT
    function getBookings(filter = {}) {
        initStore();
        const bookings = getItem('bookings', []);
        const services = getItem('services', []);
        const therapists = getTherapists();
        const users = getItem('users', []);
        const reviews = getItem('reviews', []);

        let enriched = bookings.map(b => {
            const s = services.find(item => item.id == b.service_id) || {};
            const t = therapists.find(item => item.id == b.therapist_id) || null;
            const c = users.find(item => item.id == b.customer_id) || {};
            const r = reviews.find(item => item.booking_id == b.id) || null;

            return {
                ...b,
                service_name: s.name || 'Layanan Spa',
                duration_minutes: s.duration_minutes || b.duration,
                service_price: s.price || b.total_price,
                service_image: s.image_url || '',
                service_type: s.type || 'both',
                customer_name: c.name || 'Pelanggan',
                customer_email: c.email || '',
                customer_phone: c.phone || '',
                therapist_name: t ? t.name : null,
                therapist_phone: t ? t.phone : null,
                therapist_gender: t ? t.gender : null,
                review: r
            };
        });

        // Filter status
        if (filter.status && filter.status !== 'all') {
            enriched = enriched.filter(b => b.status === filter.status);
        }
        // Filter customer_id
        if (filter.customer_id) {
            enriched = enriched.filter(b => b.customer_id == filter.customer_id);
        }
        // Filter therapist_id
        if (filter.therapist_id) {
            enriched = enriched.filter(b => b.therapist_id == filter.therapist_id);
        }

        // Sort desc by schedule_datetime
        enriched.sort((a, b) => new Date(b.schedule_datetime) - new Date(a.schedule_datetime));

        return enriched;
    }

    function getBookingByCode(code) {
        const bookings = getBookings();
        const cleanCode = String(code).trim().toUpperCase();
        return bookings.find(b => b.booking_code.toUpperCase() === cleanCode) || null;
    }

    function createBooking(data) {
        initStore();
        const bookings = getItem('bookings', []);

        const now = new Date();
        const year = String(now.getFullYear()).substring(2);
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const randomNum = Math.floor(1000 + Math.random() * 9000);
        const bookingCode = `BKG-${year}${month}${day}-${randomNum}`;

        const newBooking = {
            id: Date.now(),
            booking_code: bookingCode,
            customer_id: data.customer_id,
            therapist_id: data.therapist_id || null,
            service_id: Number(data.service_id),
            booking_type: data.booking_type || 'clinic',
            address: data.address || null,
            schedule_datetime: data.schedule_datetime,
            duration: Number(data.duration) || 60,
            total_price: Number(data.total_price) || 0,
            status: data.therapist_id ? 'confirmed' : 'pending',
            payment_status: 'unpaid',
            notes: data.notes || '',
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
        };

        bookings.unshift(newBooking);
        setItem('bookings', bookings);

        return {
            success: true,
            booking: newBooking,
            message: 'Reservasi Anda berhasil dibuat!'
        };
    }

    function updateBookingStatus(id, newStatus) {
        initStore();
        const bookings = getItem('bookings', []);
        const b = bookings.find(item => item.id == id);
        if (b) {
            b.status = newStatus;
            if (newStatus === 'completed') {
                b.payment_status = 'paid';
            }
            setItem('bookings', bookings);
            return { success: true, booking: b, message: `Status berhasil diubah menjadi ${newStatus}.` };
        }
        return { success: false, message: 'Pesanan tidak ditemukan.' };
    }

    function updatePaymentStatus(id, newStatus) {
        initStore();
        const bookings = getItem('bookings', []);
        const b = bookings.find(item => item.id == id);
        if (b) {
            b.payment_status = newStatus;
            setItem('bookings', bookings);
            return { success: true, message: `Status pembayaran berhasil diubah menjadi ${newStatus}.` };
        }
        return { success: false, message: 'Pesanan tidak ditemukan.' };
    }

    function assignTherapist(bookingId, therapistId) {
        initStore();
        const bookings = getItem('bookings', []);
        const b = bookings.find(item => item.id == bookingId);
        if (b) {
            b.therapist_id = Number(therapistId);
            if (b.status === 'pending') {
                b.status = 'confirmed';
            }
            setItem('bookings', bookings);
            return { success: true, message: 'Terapis berhasil ditugaskan ke pesanan ini.' };
        }
        return { success: false, message: 'Pesanan tidak ditemukan.' };
    }

    // 8. REVIEWS & TESTIMONIALS
    function getReviews(therapistId = null) {
        initStore();
        const reviews = getItem('reviews', []);
        const users = getItem('users', []);
        const bookings = getItem('bookings', []);
        const services = getItem('services', []);

        let list = reviews.map(r => {
            const u = users.find(user => user.id == r.customer_id) || {};
            const b = bookings.find(book => book.id == r.booking_id) || {};
            const s = services.find(srv => srv.id == b.service_id) || {};
            return {
                ...r,
                customer_name: u.name || 'Pelanggan Terverifikasi',
                service_name: s.name || 'Layanan Spa',
                booking_code: b.booking_code || ''
            };
        });

        if (therapistId) {
            list = list.filter(r => r.therapist_id == therapistId);
        }

        list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        return list;
    }

    function submitReview(bookingId, rating, comment) {
        initStore();
        const reviews = getItem('reviews', []);
        const bookings = getItem('bookings', []);
        const therapists = getItem('therapists', []);

        const b = bookings.find(item => item.id == bookingId);
        if (!b) {
            return { success: false, message: 'Reservasi tidak ditemukan.' };
        }
        if (b.status !== 'completed') {
            return { success: false, message: 'Hanya pesanan yang sudah selesai yang dapat diberikan ulasan.' };
        }
        if (reviews.some(r => r.booking_id == bookingId)) {
            return { success: false, message: 'Anda sudah memberikan ulasan untuk reservasi ini.' };
        }

        const newReview = {
            id: Date.now(),
            booking_id: Number(bookingId),
            customer_id: b.customer_id,
            therapist_id: b.therapist_id,
            rating: Number(rating),
            comment: String(comment || '').trim(),
            created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
        };

        reviews.push(newReview);
        setItem('reviews', reviews);

        // Update therapist rating calculation in therapists table
        if (b.therapist_id) {
            const tReviews = reviews.filter(r => r.therapist_id == b.therapist_id);
            const sum = tReviews.reduce((acc, curr) => acc + Number(curr.rating), 0);
            const newAvg = Number((sum / tReviews.length).toFixed(2));

            const t = therapists.find(item => item.id == b.therapist_id);
            if (t) {
                t.rating = newAvg;
                setItem('therapists', therapists);
            }
        }

        return {
            success: true,
            review: newReview,
            message: 'Terima kasih! Ulasan dan rating bintang Anda berhasil dikirim.'
        };
    }

    // 9. OVERVIEW STATS (FINANCIAL & OPERATIONAL)
    function getStats() {
        initStore();
        const bookings = getItem('bookings', []);
        const therapists = getItem('therapists', []);

        const totalBookings = bookings.length;
        const pendingBookings = bookings.filter(b => b.status === 'pending').length;
        const activeBookings = bookings.filter(b => b.status === 'confirmed' || b.status === 'on_process').length;
        const completedBookings = bookings.filter(b => b.status === 'completed').length;
        const cancelledBookings = bookings.filter(b => b.status === 'cancelled').length;

        const grossRevenue = bookings
            .filter(b => b.payment_status === 'paid' && b.status !== 'cancelled')
            .reduce((sum, b) => sum + Number(b.total_price), 0);

        // Estimasi komisi bersih klinik 40%, terapis 60%
        const netRevenue = Math.round(grossRevenue * 0.4);

        const totalTherapists = therapists.length;
        const availableTherapists = therapists.filter(t => t.is_available == 1).length;

        return {
            totalBookings,
            pendingBookings,
            activeBookings,
            completedBookings,
            cancelledBookings,
            grossRevenue,
            netRevenue,
            totalTherapists,
            availableTherapists
        };
    }

    // 10. FORMATTERS & UI HELPERS
    function formatRupiah(amount) {
        return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-5 right-5 z-[9999] flex flex-col space-y-2 pointer-events-none max-w-sm w-full px-4';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `pointer-events-auto transform transition-all duration-300 ease-out translate-y-4 opacity-0 p-4 rounded-2xl shadow-xl flex items-center space-x-3 text-sm font-medium ${
            type === 'success' ? 'bg-emerald-900 text-white border border-emerald-700' :
            type === 'error' ? 'bg-rose-900 text-white border border-rose-700' :
            'bg-stone-900 text-white border border-stone-700'
        }`;

        const icon = type === 'success' ? 'fa-circle-check text-emerald-400' :
                     type === 'error' ? 'fa-circle-xmark text-rose-400' : 'fa-circle-info text-blue-400';

        toast.innerHTML = `
            <i class="fa-solid ${icon} text-lg shrink-0"></i>
            <span class="flex-1">${message}</span>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('translate-y-4', 'opacity-0');
        }, 10);

        setTimeout(() => {
            toast.classList.add('translate-y-4', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // Export to Window
    window.SentosaStore = {
        initStore,
        getCurrentUser,
        setCurrentUser,
        login,
        register,
        logout,
        changePassword,
        getServices,
        getServiceById,
        saveService,
        deleteService,
        getTherapists,
        getTherapistById,
        getTherapistByUserId,
        saveTherapist,
        deleteTherapist,
        toggleTherapistAvailability,
        getBookings,
        getBookingByCode,
        createBooking,
        updateBookingStatus,
        updatePaymentStatus,
        assignTherapist,
        getReviews,
        submitReview,
        getStats,
        formatRupiah,
        formatDateTime,
        showToast
    };

    // Auto initialize on script load
    initStore();
})();
