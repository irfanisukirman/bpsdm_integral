<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>INTEGRAL | Tech System BPSDM Jabar</title>
    
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
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --integral-primary: #696cff;
            --integral-dark: #233446;
        }

        body {
            font-family: 'Public Sans', sans-serif;
            background-color: #fff;
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #f5f7ff 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            color: var(--integral-dark);
            margin-bottom: 1.5rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: #566a7f;
            margin-bottom: 2.5rem;
        }

        /* Branding */
        .brand-logo {
            width: 60px;
            margin-bottom: 20px;
        }

        /* Buttons */
        .btn-integral {
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-integral-primary {
            background-color: var(--integral-primary);
            color: #fff;
            box-shadow: 0 4px 15px rgba(105, 108, 255, 0.4);
        }

        .btn-integral-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(105, 108, 255, 0.5);
            color: #fff;
        }

        /* Features */
        .feature-card {
            border: none;
            padding: 30px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(105, 108, 255, 0.15);
        }

        .icon-box {
            width: 60px;
            height: 60px;
            background-color: rgba(105, 108, 255, 0.1);
            color: var(--integral-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin-bottom: 20px;
        }

        /* Navbar */
        .navbar-custom {
            padding: 20px 0;
            background: transparent;
            position: absolute;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .footer {
            padding: 50px 0;
            background-color: var(--integral-dark);
            color: #fff;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" alt="Logo" width="40" class="me-2">
                <span class="fw-bold text-dark h4 mb-0">INTEGRAL</span>
            </a>
            <div class="ms-auto">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-integral btn-integral-primary">
                        <i class="bx bx-home-circle me-1"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-integral btn-integral-primary">
                        <i class="bx bx-log-in-circle me-1"></i> Masuk Sistem
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 animate__animated animate__fadeInLeft">
                    <h1 class="hero-title">Integral <span class="text-primary">Technology</span></h1>
                    <p class="hero-subtitle text-uppercase fw-bold text-primary mb-2" style="letter-spacing: 2px;">BPSDM Provinsi Jawa Barat</p>
                    <p class="fs-5 text-muted mb-5">
                        Sistem Pengelolaan Pelatihan Terintegrasi: Kalender Pelatihan, Monitoring Penyelenggaraan, 
                        hingga Evaluasi Dampak Model Kirkpatrick 360°.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('login') }}" class="btn btn-integral btn-integral-primary btn-lg">Mulai Sekarang</a>
                        <a href="#features" class="btn btn-outline-secondary btn-lg btn-integral">Pelajari Fitur</a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block animate__animated animate__fadeInRight">
                    <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" alt="Hero Image" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section id="features" class="py-5 mb-5">
        <div class="container py-5">
            <div class="text-center mb-5 animate__animated animate__fadeInUp">
                <h2 class="fw-bold">Solusi Terintegrasi</h2>
                <p class="text-muted">Satu platform untuk seluruh siklus pengembangan kompetensi</p>
            </div>
            <div class="row g-4">
                <!-- Kalender -->
                <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                    <div class="feature-card">
                        <div class="icon-box"><i class="bx bx-calendar"></i></div>
                        <h5 class="fw-bold">Kalender Pelatihan</h5>
                        <p class="text-muted small">Manajemen jadwal pelatihan Standar & Blended Learning secara sistematis per bidang.</p>
                    </div>
                </div>
                <!-- Monitoring -->
                <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                    <div class="feature-card">
                        <div class="icon-box"><i class="bx bx-desktop"></i></div>
                        <h5 class="fw-bold">Monitoring & Tindak Lanjut</h5>
                        <p class="text-muted small">Pemantauan penyelenggaraan real-time dengan sistem notifikasi tindak lanjut antar bidang.</p>
                    </div>
                </div>
                <!-- Evaluasi -->
                <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                    <div class="feature-card">
                        <div class="icon-box"><i class="bx bx-bar-chart-alt-2"></i></div>
                        <h5 class="fw-bold">Evaluasi Kirkpatrick</h5>
                        <p class="text-muted small">Analisis dampak pelatihan Level 1-4 dengan metode penilaian 360° dari Alumni, Atasan, dan Rekan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container text-center">
            <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" alt="Logo" width="50" class="mb-4" style="filter: brightness(0) invert(1);">
            <h5 class="text-white fw-bold mb-3 text-uppercase">Integral Technology</h5>
            <p class="opacity-75 mb-4">Badan Pengembangan Sumber Daya Manusia (BPSDM)<br>Pemerintah Provinsi Jawa Barat</p>
            <div class="d-flex justify-content-center gap-3 mb-4">
                <a href="#" class="text-white h4"><i class="bx bxl-facebook"></i></a>
                <a href="#" class="text-white h4"><i class="bx bxl-instagram"></i></a>
                <a href="#" class="text-white h4"><i class="bx bxl-youtube"></i></a>
            </div>
            <hr class="bg-light opacity-25">
            <p class="small opacity-50 mb-0">&copy; {{ date('Y') }} INTEGRAL. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    @if(session('success'))
        <script>
            window.onload = function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Selesai',
                    text: "{{ session('success') }}",
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        </script>
    @endif
</body>
</html>