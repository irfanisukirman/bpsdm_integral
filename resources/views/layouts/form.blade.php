<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    
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
    @stack('form_css')
</head>
<body style="background-color: #f5f5f9;">
    <div class="container-xxl flex-grow-1 container-p-y py-4">

        <!-- WRAPPER RAMPING & POSISI TENGAH (CENTERED) -->
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9 col-md-11 col-12">

                <!-- ======================================================== -->
                <!-- HEADER HALAMAN FORM                                      -->
                <!-- ======================================================== -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                    <div>
                        <h4 class="fw-bold py-1 mb-1">
                            <span class="text-muted fw-light">@yield('module_name', 'Sistem') /</span> @yield('page_title', 'Form Pengisian Data')
                        </h4>
                        <p class="text-muted small mb-0">
                            @yield('page_description', 'Silakan lengkapi seluruh data di bawah ini dengan benar.')
                        </p>
                    </div>
                    <div>
                        <a href="@yield('back_url', url()->previous())" class="btn btn-outline-secondary btn-sm shadow-sm">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <!-- ======================================================== -->
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
                      id="reusableForm">
                    @csrf
                    @yield('form_method')

                    {{-- KONTEN / INPUTAN FORM DARI VIEW ANAK MASUK KE SINI --}}
                    @yield('form_content')

                    <!-- TOMBOL SUBMIT & RESET BAWAH -->
                    <div class="card shadow-sm border-0 mb-5">
                        <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <small class="text-muted">
                                <span class="required-star">*</span> Pastikan data yang dimasukkan sudah benar sebelum disimpan.
                            </small>
                            <div class="d-flex gap-2">
                                @section('form_buttons')
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="bx bx-refresh me-1"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary shadow" id="btnSubmitForm">
                                        <i class="bx bx-save me-1"></i> @yield('submit_text', 'Simpan Data')
                                    </button>
                                @show
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>

    </div>

    <!-- Core Scripts -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {
        if ($('.select2').length) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }

        $('#reusableForm').on('submit', function() {
            const btn = $('#btnSubmitForm');
            btn.prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...');
        });
    });
    </script>
    @stack('form_js')
</body>
</html>