<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <!-- Title Dinamis -->
    <title>@yield('form_title', 'Form Pengisian Data') | Sistem Pelatihan</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        .form-section-title {
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #696cff;
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e7e7ff;
        }
        .form-label {
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 0.35rem;
        }
        .required-star {
            color: #ff3e1d;
            font-weight: bold;
        }
        .select2-container--bootstrap-5 .select2-selection {
            border-color: #d9dee3;
            border-radius: 0.375rem;
            min-height: 38px;
        }
    </style>
    <script src="{{ asset('assets/js/integral-theme.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/integral-fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/integral-profile-onboarding.css') }}">
    @stack('form_css')
</head>
<body class="profile-onboarding">
    <div class="container-xxl flex-grow-1 container-p-y py-4">

        <!-- WRAPPER RAMPING & POSISI TENGAH (CENTERED) -->
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9 col-md-11 col-12">

                <header class="profile-heading">
                    <a class="profile-brand" href="{{ url('/') }}"><img src="{{ asset('assets/img/favicon/inte.png') }}" width="40" height="40" alt=""> INTEGRAL</a>
                    <span class="profile-eyebrow">SELAMAT DATANG</span>
                    <h1>Lengkapi profil Anda</h1>
                    <p>Isi data Anda selangkah demi selangkah untuk mulai mengikuti pelatihan.</p>
                </header>
                <ol class="profile-progress" aria-label="Tahapan pengisian profil">
                    @foreach(['Identitas', 'Kepegawaian', 'Wilayah Tinggal', 'Alamat & Peta'] as $stepLabel)
                    <li><button type="button" class="profile-step-link" data-go-step="{{ $loop->index }}" @if($loop->first) aria-current="step" @endif><span>{{ $loop->iteration }}</span><strong>{{ $stepLabel }}</strong></button></li>
                    @endforeach
                </ol>
                <!-- ALERT ERROR VALIDASI GLOBAL                              -->
                <!-- ======================================================== -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible shadow-sm border-0 mb-4" role="alert">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bx bx-error-circle fs-4 me-2"></i>
                            <h6 class="alert-heading fw-bold mb-0">Terdapat kesalahan pada isian form:</h6>
                        </div>
                        <ul class="mb-0 ps-4 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- ======================================================== -->
                <!-- ELEMEN FORM UTAMA                                        -->
                <!-- ======================================================== -->
                <form action="@yield('form_action')" 
                      method="@yield('form_method_raw', 'POST')" 
                      enctype="@yield('form_enctype', 'application/x-www-form-urlencoded')" 
                      id="reusableForm" novalidate data-error-fields='@json($errors->keys())'>
                    @csrf
                    @yield('form_method')

                    {{-- KONTEN / INPUTAN FORM DARI VIEW ANAK MASUK KE SINI --}}
                    @yield('form_content')

                    <div id="profileStepError" class="alert alert-danger" role="alert" hidden></div>
                    <footer class="profile-footer">
                        <p id="profileStepStatus" aria-live="polite">Langkah 1 dari 4</p>
                        <div class="profile-buttons">
                            <button type="button" class="btn btn-outline-secondary" id="profilePrevious" hidden><i class="bx bx-arrow-back me-1"></i>Sebelumnya</button>
                            <button type="button" class="btn btn-primary" id="profileNext">Lanjut <i class="bx bx-right-arrow-alt ms-1"></i></button>
                            <button type="submit" class="btn btn-primary" id="btnSubmitForm" hidden><i class="bx bx-check me-1"></i>Simpan Profil & Lanjutkan</button>
                        </div>
                    </footer>
                    <p class="profile-note"><i class="bx bx-shield-quarter me-1"></i>Pendaftaran otomatis sebagai peserta. Peran tambahan dikelola oleh admin.</p>
                </form>

            </div>
        </div>

    </div>

    <!-- Core Scripts -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('assets/js/integral-profile-onboarding.js') }}"></script>
    @stack('form_js')
</body>
</html>
