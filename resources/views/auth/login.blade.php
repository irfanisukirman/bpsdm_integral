@extends('layouts.auth')

@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
        
        <!-- Tombol Kembali ke Beranda (Diletakkan di luar Card agar terlihat modern) -->
        <div class="mb-3 animate__animated animate__fadeInLeft">
            <a href="{{ url('/') }}" class="d-flex align-items-center text-muted fw-semibold">
                <i class="bx bx-chevron-left fs-4"></i>
                <span>Kembali ke Beranda</span>
            </a>
        </div>

        <!-- Login Card -->
        <div class="card shadow-lg border-0 animate__animated animate__fadeInUp" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px;">
            <div class="card-body p-3 p-sm-4 p-md-5">
                <!-- Branding Logo -->
                <div class="app-brand justify-content-center mb-4">
                    <a href="/" class="app-brand-link gap-2">
                        <span class="app-brand-logo demo">
                            <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" alt="Integral Logo" style="width: 55px; filter: drop-shadow(0px 4px 10px rgba(105, 108, 255, 0.3));">
                        </span>
                    </a>
                </div>
                
                <div class="text-center mb-4">
                    <h4 class="mb-1 fw-bold text-dark" style="letter-spacing: -0.5px;">Integral <span class="text-primary">Technology</span></h4>
                    <p class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 1px;">Sistem Pengelolaan Terintegrasi</p>
                </div>
                
                {{-- Alert Error --}}
                @if(session('error'))
                    <div class="alert alert-danger border-0 small d-flex align-items-center animate__animated animate__shakeX" role="alert">
                        <i class="bx bx-error-circle me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">Username / NIP</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" placeholder="Masukkan ID Anda" autofocus value="{{ old('username') }}" style="border-left: none;" />
                        </div>
                        @error('username')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <div class="d-flex justify-content-between">
                            <label class="form-label fw-bold" for="password">Password</label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                                <small class="text-primary fw-semibold">Lupa Password?</small>
                            </button>
                        </div>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="••••••••" style="border-left: none; border-right: none;" />
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        </div>
                        @error('password')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <button class="btn btn-primary d-grid w-100 py-2 fw-bold shadow-sm btn-login" type="submit">
                            MASUK KE SISTEM
                        </button>
                    </div>
                </form>

                <div class="divider my-4">
                    <div class="divider-text text-muted small text-uppercase">Atau akses cepat</div>
                </div>

                {{-- TOMBOL GOOGLE MODERN --}}
                <div class="d-flex justify-content-center">
                    <a href="{{ route('auth.google') }}" class="btn btn-outline-white w-100 d-flex align-items-center justify-content-center py-2 google-btn shadow-xs">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google Logo" style="width: 18px;" class="me-2">
                        <span class="fw-bold text-dark" style="font-size: 0.9rem;">Google Account</span>
                    </a>
                </div>

                <div class="text-center mt-5">
                    <p class="mb-0 small text-muted">
                        BPSDM Provinsi Jawa Barat &copy; {{ date('Y') }}
                    </p>
                    <small class="fw-bold text-primary" style="font-size: 10px; letter-spacing: 2px;">INTEGRAL TECH</small>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $passwordAdmins = [
        ['name' => 'Sembiru', 'phone' => '6281382830814'],
        ['name' => 'Alam', 'phone' => '6281809597757'],
        ['name' => 'Rizky', 'phone' => '6281295317499'],
    ];
@endphp
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg overflow-hidden">
  <div class="modal-header border-0 bg-label-primary px-4 pt-4"><div><span class="badge bg-primary mb-2"><i class="bx bx-lock-open me-1"></i>Bantuan Akun</span><h5 class="modal-title fw-bold" id="forgotPasswordTitle">Hubungi Admin via WhatsApp</h5><p class="text-muted small mb-0">Pilih salah satu admin untuk meminta bantuan reset password.</p></div><button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button></div>
  <div class="modal-body p-4">
   <div class="alert alert-info small"><i class="bx bx-info-circle me-1"></i>WhatsApp akan terbuka dengan formulir yang sudah tersedia. Lengkapi seluruh data tersebut sebelum mengirim pesan.</div>
   <div class="d-grid gap-2">
    @foreach($passwordAdmins as $admin)
     @php $waMessage = "Halo Admin {$admin['name']}, saya ingin meminta bantuan reset password akun INTEGRAL.\n\nNama Lengkap:\nNIP/NIK:\nEmail Terdaftar:\n\nMohon bantuannya. Terima kasih."; @endphp
     <a href="https://wa.me/{{$admin['phone']}}?text={{rawurlencode($waMessage)}}" target="_blank" rel="noopener" class="btn btn-outline-success d-flex align-items-center text-start p-3 rounded-3">
      <span class="avatar avatar-md me-3"><span class="avatar-initial rounded-circle bg-label-success"><i class="bx bxl-whatsapp fs-4"></i></span></span><span class="flex-grow-1"><strong class="d-block text-dark">{{$admin['name']}}</strong><small class="text-muted">+{{substr($admin['phone'],0,2)}} {{substr($admin['phone'],2)}}</small></span><i class="bx bx-chevron-right fs-4"></i>
     </a>
    @endforeach
   </div>
  </div><div class="modal-footer border-0 pt-0 px-4 pb-4"><button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Batal</button></div>
 </div></div>
</div>
<!-- Background Elements -->
<div class="bg-shape-1 d-none d-sm-block"></div>
<div class="bg-shape-2 d-none d-sm-block"></div>

<style>
    body {
        background-color: #f4f7ff;
        background-image: radial-gradient(#696cff 0.5px, transparent 0.5px);
        background-size: 30px 30px;
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    /* Responsivitas Kontainer Utama */
    .authentication-inner {
        max-width: 450px !important;
        width: 100%;
        margin: 0 auto;
        padding: 0 1.2rem;
        position: relative;
        z-index: 10;
    }

    @media (max-width: 576px) {
        .hero-title { font-size: 1.5rem; }
        .card-body { padding: 1.5rem !important; }
        .authentication-wrapper.authentication-basic .authentication-inner { padding: 0 1rem; }
    }

    /* Dekorasi Lingkaran di Background (Hidden on Mobile for Performance) */
    .bg-shape-1 {
        position: fixed;
        top: -100px;
        right: -100px;
        width: 400px;
        height: 400px;
        background: linear-gradient(135deg, rgba(105, 108, 255, 0.2) 0%, rgba(105, 108, 255, 0) 100%);
        border-radius: 50%;
        z-index: -1;
    }

    .bg-shape-2 {
        position: fixed;
        bottom: -150px;
        left: -150px;
        width: 500px;
        height: 500px;
        background: linear-gradient(135deg, rgba(3, 195, 236, 0.15) 0%, rgba(3, 195, 236, 0) 100%);
        border-radius: 50%;
        z-index: -1;
    }

    /* Input Focus Styling */
    .input-group-text {
        background-color: #fcfcff;
        border-color: #d9dee3;
        color: #696cff;
    }
    .form-control:focus, .form-control:focus + .input-group-text {
        border-color: #696cff !important;
    }

    /* Button Login Animation */
    .btn-login {
        background: linear-gradient(45deg, #696cff, #43459d);
        border: none;
        transition: all 0.3s;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(105, 108, 255, 0.3) !important;
    }

    /* Google Button */
    .google-btn {
        border: 1px solid #d9dee3;
        background: white;
        transition: all 0.2s;
    }
    .google-btn:hover {
        background-color: #f8f9fa !important;
        border-color: #696cff !important;
        transform: scale(1.01);
    }

    /* Divider */
    .divider-text {
        background-color: transparent !important;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .divider::before, .divider::after {
        border-color: #d9dee3 !important;
    }
    
    .shadow-xs {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
</style>
@endsection