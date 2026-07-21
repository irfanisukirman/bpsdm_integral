<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Survei Dampak Pelatihan</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
</head>
<body class="bg-primary">
    <div class="container-xxl py-5">
        <div class="row justify-content-center text-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="bx bx-analyse bx-lg text-primary mb-3"></i>
                            <h3>Survei Evaluasi Pasca Pelatihan</h3>
                            <p class="text-muted">Instrumen Pengukuran Perilaku (Level 3) & Dampak (Level 4)</p>
                            <h5 class="fw-bold">{{ $training->nama_pelatihan }}</h5>
                        </div>
                        <hr class="my-5">
                        <h6 class="mb-4">Sesuai ketentuan Kirkpatrick, silakan pilih status Anda:</h6>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <a href="{{ route('public.l34.form', [$training->id, 'mandiri']) }}" class="card border border-primary h-100 text-decoration-none hover-shadow">
                                    <div class="card-body">
                                        <i class="bx bx-user-pin bx-md mb-2"></i>
                                        <h6 class="mb-0">Saya adalah Peserta/Alumni</h6>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('public.l34.form', [$training->id, 'atasan']) }}" class="card border border-info h-100 text-decoration-none">
                                    <div class="card-body">
                                        <i class="bx bx-briefcase bx-md mb-2 text-info"></i>
                                        <h6 class="mb-0">Saya adalah Atasan Langsung</h6>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('public.l34.form', [$training->id, 'rekan']) }}" class="card border border-success h-100 text-decoration-none">
                                    <div class="card-body">
                                        <i class="bx bx-group bx-md mb-2 text-success"></i>
                                        <h6 class="mb-0">Saya adalah Rekan Kerja</h6>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>