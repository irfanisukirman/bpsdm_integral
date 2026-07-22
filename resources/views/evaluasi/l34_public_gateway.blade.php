<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Survei Dampak Pelatihan</title>
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <!-- Animate.css untuk efek masuk -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        /* Background Gradient Modern */
        body {
            background: linear-gradient(135deg, #696cff 0%, #30336b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        /* Styling Kartu Pilihan */
        .choice-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 2px solid transparent !important;
            border-radius: 15px !important;
            overflow: hidden;
            position: relative;
        }

        /* Efek Hover: Mengangkat kartu, menambah shadow, dan mengubah border */
        .choice-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2) !important;
            background-color: #fcfcff !important;
        }

        .card-mandiri:hover { border-color: #696cff !important; }
        .card-atasan:hover { border-color: #03c3ec !important; }
        .card-rekan:hover { border-color: #71dd37 !important; }

        /* Animasi Icon saat Hover */
        .choice-card:hover .bx {
            transform: scale(1.2) rotate(5deg);
            transition: all 0.3s ease;
        }

        /* Lingkaran Dekoratif di belakang kartu */
        .choice-card::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 100px;
            height: 100px;
            background: rgba(105, 108, 255, 0.05);
            border-radius: 50%;
            transition: all 0.5s ease;
        }

        .choice-card:hover::after {
            width: 150px;
            height: 150px;
            top: -30px;
            right: -30px;
            background: rgba(105, 108, 255, 0.1);
        }

        /* Label Peran */
        .role-label {
            font-weight: 700;
            color: #566a7f;
            margin-top: 10px;
            display: block;
        }

        /* Tanda Panah yang muncul saat hover */
        .go-arrow {
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s ease;
            color: #696cff;
        }

        .choice-card:hover .go-arrow {
            opacity: 1;
            transform: translateX(0);
        }

        .main-card {
            border-radius: 20px !important;
        }
    </style>
</head>
<body>
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-9 animate__animated animate__fadeInUp">
                <div class="card main-card shadow-lg border-0">
                    <div class="card-body p-5">
                        <!-- Header Section -->
                        <div class="mb-5 text-center">
                            <div class="avatar avatar-xl mb-3 mx-auto">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <i class="bx bx-analyse bx-md"></i>
                                </span>
                            </div>
                            <h2 class="fw-bold text-dark">Survei Evaluasi Pasca Pelatihan</h2>
                            <p class="text-muted fs-5">Instrumen Pengukuran Perilaku (Level 3) & Dampak (Level 4)</p>
                            <div class="badge bg-label-primary px-3 py-2 fs-6">
                                <i class="bx bx-book-bookmark me-1"></i> {{ $training->nama_pelatihan }}
                            </div>
                        </div>

                        <hr class="my-5 opacity-50">

                        <h5 class="mb-4 fw-semibold text-center text-dark">Silakan pilih status Anda untuk memulai penilaian:</h5>

                        <div class="row g-4">
                            <!-- PILIHAN: ALUMNI -->
                            <div class="col-md-4">
                                <a href="{{ route('public.l34.form', [$training->id, 'mandiri']) }}" 
                                   class="card h-100 text-decoration-none border shadow-none choice-card card-mandiri text-center">
                                    <div class="card-body py-5">
                                        <div class="mb-3">
                                            <i class="bx bx-user-pin text-primary" style="font-size: 4rem;"></i>
                                        </div>
                                        <span class="role-label h6">PESERTA / ALUMNI</span>
                                        <div class="mt-3 go-arrow">
                                            <span>Mulai <i class="bx bx-right-arrow-alt"></i></span>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- PILIHAN: ATASAN -->
                            <div class="col-md-4">
                                <a href="{{ route('public.l34.form', [$training->id, 'atasan']) }}" 
                                   class="card h-100 text-decoration-none border shadow-none choice-card card-atasan text-center">
                                    <div class="card-body py-5">
                                        <div class="mb-3">
                                            <i class="bx bx-briefcase text-info" style="font-size: 4rem;"></i>
                                        </div>
                                        <span class="role-label h6">ATASAN LANGSUNG</span>
                                        <div class="mt-3 go-arrow">
                                            <span class="text-info">Mulai <i class="bx bx-right-arrow-alt"></i></span>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- PILIHAN: REKAN -->
                            <div class="col-md-4">
                                <a href="{{ route('public.l34.form', [$training->id, 'rekan']) }}" 
                                   class="card h-100 text-decoration-none border shadow-none choice-card card-rekan text-center">
                                    <div class="card-body py-5">
                                        <div class="mb-3">
                                            <i class="bx bx-group text-success" style="font-size: 4rem;"></i>
                                        </div>
                                        <span class="role-label h6">REKAN KERJA</span>
                                        <div class="mt-3 go-arrow">
                                            <span class="text-success">Mulai <i class="bx bx-right-arrow-alt"></i></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Footer Info -->
                        <div class="mt-5 text-center">
                            <p class="text-muted small">
                                <i class="bx bx-shield-quarter me-1"></i> 
                                Kerahasiaan data Anda akan kami jaga sepenuhnya sesuai kebijakan privasi.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Branding Bottom -->
                <div class="text-center mt-4 text-white opacity-75">
                    <small>Sistem Informasi Monitoring & Evaluasi Pelatihan (SIM-PEL) &copy; {{ date('Y') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
</body>
</html>