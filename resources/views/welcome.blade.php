<!DOCTYPE html>
<html lang="id" class="light-style">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Integral | Technology</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    
    <!-- Animate.css & AOS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }

        :root {
            --integral-primary: #696cff;
            --integral-dark: #233446;
            --integral-gradient: linear-gradient(135deg, #696cff 0%, #30336b 100%);
        }

        body {
            font-family: 'Public Sans', sans-serif;
            background-color: #fff;
            color: #566a7f;
        }

        /* Modern Glassmorphism Navbar */
        .navbar-custom {
            padding: 15px 0;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1050;
            transition: all 0.3s ease;
        }

        .navbar-custom.scrolled {
            padding: 10px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Hero Section */
        .hero-section {
            padding-top: 140px;
            padding-bottom: 80px;
            background: radial-gradient(circle at 10% 20%, rgba(105, 108, 255, 0.05) 0%, rgba(255, 255, 255, 1) 90.2%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-title {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.1;
            color: var(--integral-dark);
            margin-bottom: 1.5rem;
        }

        .hero-title span {
            color: var(--integral-primary);
        }

        /* Floating Animation untuk Ilustrator Baru */
        .hero-img {
            max-width: 100%;
            height: auto;
            animation: floating 3.5s ease-in-out infinite;
            filter: drop-shadow(0 20px 30px rgba(105, 108, 255, 0.2));
        }

        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-25px); }
            100% { transform: translateY(0px); }
        }

        /* Feature Cards */
        .feature-card {
            border: 1px solid #f0f2f4;
            padding: 40px;
            border-radius: 20px;
            background: #fff;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(105, 108, 255, 0.12);
            border-color: var(--integral-primary);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background: var(--integral-gradient);
            color: #fff;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin-bottom: 25px;
            box-shadow: 0 10px 20px rgba(105, 108, 255, 0.3);
        }

        /* Custom Button */
        .btn-integral {
            padding: 12px 35px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            letter-spacing: 0.5px;
        }

        .btn-primary-gradient {
            background: var(--integral-gradient);
            color: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(105, 108, 255, 0.4);
        }

        .btn-primary-gradient:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(105, 108, 255, 0.5);
            color: #fff;
        }

        /* Section Title */
        .section-tag {
            font-weight: 700;
            color: var(--integral-primary);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.85rem;
            margin-bottom: 10px;
            display: block;
        }

        .footer {
            background-color: var(--integral-dark);
            padding: 80px 0 30px;
            color: #fff;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .hero-title { font-size: 2.5rem; text-align: center; }
            .hero-subtitle { text-align: center; }
            .hero-btns { justify-content: center; }
        }
        /* Animasi Titik Merah Live */
        .live-pulse-red {
            width: 12px;
            height: 12px;
            background: #ff3e1d;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 rgba(255, 62, 29, 0.4);
            animation: pulse-red 1.5s infinite;
            vertical-align: middle;
        }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(255, 62, 29, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 62, 29, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 62, 29, 0); }
        }

        .bg-label-white {
            background-color: rgba(255,255,255,0.2) !important;
            color: #fff !important;
        }

        .icon-box-white {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            color: #fff;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Styling Dasar Link */
    .navbar-nav .nav-link {
        position: relative;
        color: var(--integral-dark) !important;
        transition: all 0.3s ease;
    }

    /* Efek Hover Warna Teks */
    .navbar-nav .nav-link:hover {
        color: var(--integral-primary) !important;
        transform: translateY(-2px); /* Sedikit mengangkat teks */
    }

    /* Menciptakan Garis Bawah yang Animatif */
    .navbar-nav .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 5px;
        left: 50%;
        background-color: var(--integral-primary);
        transition: all 0.3s ease;
        transform: translateX(-50%);
        border-radius: 2px;
    }

    /* Garis memanjang saat di-hover */
    .navbar-nav .nav-link:hover::after {
        width: 60%;
    }

    /* Interaksi Tombol Utama (Button) */
    .btn-integral-primary {
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .btn-integral-primary:hover {
        transform: scale(1.05) translateY(-3px);
        box-shadow: 0 10px 20px rgba(105, 108, 255, 0.4) !important;
    }
    .active-link {
        color: var(--integral-primary) !important;
    }
    .active-link::after {
        width: 60% !important;
    }
    
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#top">
                <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" alt="Logo" width="38" class="me-2">
                <span class="fw-bold text-dark h4 mb-0" style="letter-spacing: 1px;">INTEGRAL</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3" href="#top">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3" href="#stats">Statistik</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3" href="#jadwal-hari-ini">Jadwal Hari Ini</a>
                    </li>
                </ul>
                
                <div class="ms-auto">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-integral btn-primary-gradient shadow-sm">
                            <i class="bx bx-home-circle me-1"></i> Masuk Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-integral btn-primary-gradient shadow-sm">
                            <i class="bx bx-log-in-circle me-1"></i> Masuk Sistem
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section" id="top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 animate__animated animate__fadeInLeft">
                    <span class="section-tag">Integral Technology</span>
                    <h1 class="hero-title">BPSDM<span> Provinsi</span> Jawa Barat</h1>
                    <p class="fs-5 mb-5 text-muted" style="line-height: 1.6;">
                        Sistem pengelolaan pelatihan terintegrasi. Solusi cerdas untuk Jadwal Pelatihan, Monitoring, Presensi,Kelola Alumni dan Evaluasi Dampak Kirkpatrick 360°.
                    </p>
                    <div class="d-flex gap-3 hero-btns">
                        <a href="{{ route('login') }}" class="btn btn-integral btn-primary-gradient btn-lg shadow">Mulai Sekarang</a>
                        <a href="#features" class="btn btn-outline-secondary btn-lg btn-integral">Pelajari Fitur <i class="bx bx-down-arrow-alt ms-1"></i></a>
                    </div>
                </div>
                <div class="col-lg-6 text-center mt-5 mt-lg-0 animate__animated animate__fadeInRight">
                    <!-- ILUSTRATOR BARU -->
                    <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786891751/pngegg_aolaux.png" 
                         alt="Integral Technology" class="hero-img img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section id="features" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <span class="section-tag">Kecanggihan Sistem</span>
                <h2 class="fw-extrabold text-dark h1">Ekosistem Terintegrasi</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Kami menyatukan seluruh rangkaian pengembangan kompetensi dalam satu dasbor kendali yang efisien.</p>
            </div>
            <div class="row g-4 mt-2">
                <!-- Kalender -->
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon-box"><i class="bx bx-calendar-event"></i></div>
                        <h4 class="fw-bold text-dark">Kalender Pintar</h4>
                        <p class="text-muted">Manajemen jadwal pelatihan Standar & Blended Learning secara sistematis per bidang kerja.</p>
                    </div>
                </div>
                <!-- Monitoring -->
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon-box"><i class="bx bx-shield-quarter"></i></div>
                        <h4 class="fw-bold text-dark">Real-time Monitoring</h4>
                        <p class="text-muted">Pemantauan tahapan penyelenggaraan dengan sistem notifikasi tindak lanjut otomatis antar bidang.</p>
                    </div>
                </div>
                <!-- Evaluasi -->
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon-box"><i class="bx bx-analyse"></i></div>
                        <h4 class="fw-bold text-dark">Analitik L3 & L4</h4>
                        <p class="text-muted">Evaluasi dampak pelatihan (Kirkpatrick 360°) untuk mengukur perubahan perilaku dan hasil kerja.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="stats" class="py-5" style="background: linear-gradient(135deg, #696cff 0%, #30336b 100%); position: relative; overflow: hidden;">
        <!-- Dekorasi Background -->
        <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        
        <div class="container py-5 position-relative" style="z-index: 2;">
            <div class="row text-center">
                <!-- Statistik 1: Total Pelatihan -->
                <div class="col-6 col-md-3 mb-4 mb-md-0 animate__animated animate__fadeInUp">
                    <div class="p-3">
                        <div class="icon-box-white mx-auto mb-3">
                            <i class="bx bx-collection"></i>
                        </div>
                        <h2 class="text-white fw-bold counter" data-target="{{ $stats['total_training'] }}">0</h2>
                        <p class="text-white opacity-75 text-uppercase small fw-bold">Total Pelatihan</p>
                    </div>
                </div>

                <!-- Statistik 2: Total Peserta -->
                <div class="col-6 col-md-3 mb-4 mb-md-0 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                    <div class="p-3">
                        <div class="icon-box-white mx-auto mb-3">
                            <i class="bx bx-group"></i>
                        </div>
                        <h2 class="text-white fw-bold counter" data-target="{{ $stats['total_participants'] }}">0</h2>
                        <p class="text-white opacity-75 text-uppercase small fw-bold">Alumni Terdaftar</p>
                    </div>
                </div>

                <!-- Statistik 3: Kepuasan L1 -->
                <div class="col-6 col-md-3 mb-4 mb-md-0 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                    <div class="p-3">
                        <div class="icon-box-white mx-auto mb-3">
                            <i class="bx bx-smile"></i>
                        </div>
                        <h2 class="text-white fw-bold"><span class="counter" data-target="{{ $stats['satisfaction_rate'] }}">0</span><small class="fs-4">%</small></h2>
                        <p class="text-white opacity-75 text-uppercase small fw-bold">Indeks Kepuasan</p>
                    </div>
                </div>

                <!-- Statistik 4: Dampak L3/L4 -->
                <div class="col-6 col-md-3 mb-4 mb-md-0 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                    <div class="p-3">
                        <div class="icon-box-white mx-auto mb-3">
                            <i class="bx bx-trending-up"></i>
                        </div>
                        <h2 class="text-white fw-bold counter" data-target="{{ $stats['impact_score'] }}">0</h2>
                        <p class="text-white opacity-75 text-uppercase small fw-bold">Skor Dampak (L3/L4)</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SEKSI JADWAL PELATIHAN HARI INI --}}
    <section id="jadwal-hari-ini" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5 animate__animated animate__fadeIn">
                <span class="section-tag">Live Report</span>
                <h2 class="fw-bold text-dark">Jadwal Pelatihan Hari Ini</h2>
                <p class="text-muted">Pantau kegiatan yang sedang berlangsung secara real-time</p>
                <div class="badge bg-label-primary px-3 py-2">
                    <i class="bx bx-calendar me-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </div>
            </div>

            <div class="row justify-content-center">
                @forelse($trainingsToday as $t)
                    <div class="col-lg-10 mb-4">
                        <div class="card border-0 shadow-sm overflow-hidden animate__animated animate__fadeInUp">
                            <div class="row g-0">
                                <!-- Sisi Kiri: Info Pelatihan -->
                                <div class="col-md-4 bg-primary p-4 text-white d-flex flex-column justify-content-center">
                                    <small class="text-uppercase opacity-75 fw-bold" style="letter-spacing: 1px;">Pelatihan</small>
                                    <h4 class="text-white fw-bold mb-2">{{ $t->nama_pelatihan }}</h4>
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-white text-primary me-2">Angkatan {{ $t->angkatan }}</span>
                                        <span class="badge bg-label-white">{{ strtoupper($t->model) }}</span>
                                    </div>
                                    <hr class="opacity-25">
                                    <div class="small">
                                        <i class="bx bx-time-five me-1"></i> {{ \Carbon\Carbon::parse($t->tgl_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($t->tgl_selesai)->format('d M Y') }}
                                    </div>
                                </div>

                                <!-- Sisi Kanan: Kegiatan Saat Ini -->
                                <div class="col-md-8 p-4 bg-white">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="text-muted mb-1 text-uppercase small fw-bold">Kegiatan Jam Berjalan:</h6>
                                            @php $current = $t->current_activity; @endphp
                                            @if($current)
                                                <h3 class="fw-bold text-dark mb-0">
                                                    <span class="live-pulse-red me-2"></span>
                                                    {{ $current->activity }}
                                                </h3>
                                                <p class="text-primary fw-bold mt-1">
                                                    <i class="bx bx-time me-1"></i> {{ substr($current->start_time, 0, 5) }} - {{ substr($current->end_time, 0, 5) }} WIB
                                                </p>
                                            @else
                                                <h4 class="text-muted italic">Istirahat / Tidak ada jadwal jam ini</h4>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">Status:</small>
                                            @php $sisa = $t->sisa_hari; @endphp
                                            <span class="badge {{ $sisa <= 1 ? 'bg-label-danger' : 'bg-label-success' }} fw-bold">
                                                {{ $sisa == 0 ? 'HARI TERAKHIR' : $sisa . ' HARI TERSISA' }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 pt-2 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="bx bx-map-pin me-1 text-danger"></i> {{ $t->lokasi }}
                                            </div>
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                                Absen Sekarang <i class="bx bx-right-arrow-alt ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-md-6 text-center py-5 glass-effect">
                        <i class="bx bx-calendar-x display-1 text-muted opacity-25"></i>
                        <h5 class="mt-3 text-muted">Tidak ada jadwal pelatihan yang aktif hari ini.</h5>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row mb-5 align-items-center">
                <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-3">
                        <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" width="50" style="filter: brightness(0) invert(1);">
                        <h3 class="text-white mb-0 ms-3 fw-bold">INTEGRAL</h3>
                    </div>
                    <p class="opacity-75">Badan Pengembangan Sumber Daya Manusia (BPSDM)<br>Pemerintah Provinsi Jawa Barat</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <h6 class="text-white fw-bold mb-3">TEMUKAN KAMI</h6>
                    <div class="d-flex justify-content-center justify-content-md-end gap-3">
                        <a href="#" class="btn btn-icon btn-outline-white rounded-circle"><i class="bx bxl-facebook text-white"></i></a>
                        <a href="#" class="btn btn-icon btn-outline-white rounded-circle"><i class="bx bxl-instagram text-white"></i></a>
                        <a href="#" class="btn btn-icon btn-outline-white rounded-circle"><i class="bx bxl-twitter text-white"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-light opacity-25">
            <div class="text-center pt-3">
                <p class="small opacity-50 mb-0">&copy; {{ date('Y') }} INTEGRAL TECH. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>

    <script>
        // Navbar Scroll Effect
        window.onscroll = function() {
            const nav = document.getElementById('mainNavbar');
            if (window.pageYOffset > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        };

        window.addEventListener('scroll', () => {
            let sections = document.querySelectorAll('section, header');
            let navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            
            sections.forEach(section => {
                let top = window.scrollY;
                let offset = section.offsetTop - 150;
                let height = section.offsetHeight;
                let id = section.getAttribute('id');

                if (top >= offset && top < offset + height) {
                    navLinks.forEach(link => {
                        link.classList.remove('active-link');
                        if (link.getAttribute('href') == '#' + id) {
                            link.classList.add('active-link');
                        }
                    });
                }
            });
        });

        const counters = document.querySelectorAll('.counter');
        const speed = 200; // Semakin besar angka, semakin lambat

        const runCounter = () => {
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText;
                    const inc = target / speed;

                    if (count < target) {
                        counter.innerText = Math.ceil(count + inc);
                        setTimeout(updateCount, 15);
                    } else {
                        counter.innerText = target;
                    }
                };
                updateCount();
            });
        };

        // Trigger counter saat seksi terlihat (Intersection Observer)
        const statsSection = document.querySelector('#stats');
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                runCounter();
                observer.unobserve(statsSection);
            }
        }, { threshold: 0.5 });

        observer.observe(statsSection);
    </script>
</body>
</html>