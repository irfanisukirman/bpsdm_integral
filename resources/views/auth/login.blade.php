@extends('layouts.auth')

@section('title', 'Masuk Integral')
@section('body_class', 'integral-login-page')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/integral-fonts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/integral-login.css') }}?v=20261118">
<link rel="stylesheet" href="{{ asset('assets/css/integral-public-motion.css') }}?v=20261118-page-logo">
@endpush

@section('content')
<main class="login-shell">
    <a href="{{ url('/') }}" class="login-back"><i class="bx bx-arrow-back"></i>Kembali ke Beranda</a>
    <div class="login-panel">
        <section class="login-identity" aria-labelledby="loginIdentityTitle">
            <a href="{{ url('/') }}" class="login-brand"><img src="{{ asset('assets/img/favicon/inte.png') }}" alt="Logo Integral" width="44" height="44"><span>INTEGRAL<small>BPSDM Provinsi Jawa Barat</small></span></a>
            <div class="login-identity-copy">
                <span class="login-eyebrow">SATU PLATFORM, LAYANAN TERINTEGRASI</span>
                <h1 id="loginIdentityTitle">Terhubung untuk<br><span>kompetensi yang lebih baik.</span></h1>
                <p>Kelola perjalanan pelatihan Anda, dari persiapan dan pelaksanaan hingga evaluasi dan pelaporan.</p>
            </div>
            <div class="login-art"><img src="{{ asset('assets/img/illustrations/integrated.png') }}" alt="Ilustrasi layanan pelatihan Integral" width="560" height="400"></div>
            <div class="login-identity-note"><i class="bx bx-check-shield"></i>Pelatihan terkelola. Kompetensi berkembang.</div>
        </section>
        <section class="login-form-panel" aria-labelledby="loginFormTitle">
            <div class="login-form-heading"><span class="login-eyebrow">SELAMAT DATANG KEMBALI</span><h2 id="loginFormTitle">Masuk ke Integral</h2><p>Gunakan akun Anda untuk melanjutkan.</p></div>
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
                        <label for="username" class="form-label fw-bold">NIP/NIK, Email Evaluator, atau Username</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" autocomplete="username" required placeholder="Masukkan NIP/NIK, email evaluator, atau username" autofocus value="{{ old('username') }}" style="border-left: none;" />
                        </div>
                        @error('username')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <div class="d-flex justify-content-between">
                            <label class="form-label fw-bold" for="loginPassword">Password</label>
                            @if($loginHelp->is_active)
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                                <small class="text-primary fw-semibold">Lupa Password?</small>
                            </button>
                            @endif
                        </div>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                            <input id="loginPassword" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="current-password" required placeholder="Password" style="border-left:none;border-right:none" />
                            <button type="button" class="input-group-text cursor-pointer border-start-0" id="toggleLoginPassword" aria-label="Tampilkan password" aria-pressed="false"><i class="bx bx-show fs-5"></i></button>
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
                        <span class="fw-bold text-dark" style="font-size: 0.9rem;">Masuk dengan Google</span>
                    </a>
                </div>

            <p class="login-copyright">BPSDM Provinsi Jawa Barat &copy; {{ date('Y') }}<br><span>INTEGRAL ? Sistem Pengelolaan Terintegrasi</span></p>
        </section>
    </div>
</main>

@if($loginHelp->is_active)
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg overflow-hidden">
  <div class="modal-header border-0 bg-label-primary px-4 pt-4"><div><span class="badge bg-primary mb-2"><i class="bx bx-lock-open me-1"></i>Bantuan Akun</span><h5 class="modal-title fw-bold" id="forgotPasswordTitle">{{ $loginHelp->title }}</h5><p class="text-muted small mb-0">{{ $loginHelp->description }}</p></div><button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button></div>
  <div class="modal-body p-4">
   <div class="alert alert-info small"><i class="bx bx-info-circle me-1"></i>WhatsApp akan terbuka dengan formulir yang sudah tersedia. Lengkapi seluruh data tersebut sebelum mengirim pesan.</div>
   <div class="d-grid gap-2">
    @forelse($loginHelp->contacts ?: [] as $admin)
     @php $waMessage = str_replace('{ADMIN}', $admin['name'], $loginHelp->message_template); @endphp
     <a href="https://wa.me/{{$admin['phone']}}?text={{rawurlencode($waMessage)}}" target="_blank" rel="noopener" class="btn btn-outline-success d-flex align-items-center text-start p-3 rounded-3">
      <span class="avatar avatar-md me-3"><span class="avatar-initial rounded-circle bg-label-success"><i class="bx bxl-whatsapp fs-4"></i></span></span><span class="flex-grow-1"><strong class="d-block text-dark">{{$admin['name']}}</strong><small class="text-muted">+{{substr($admin['phone'],0,2)}} {{substr($admin['phone'],2)}}</small></span><i class="bx bx-chevron-right fs-4"></i>
     </a>
    @empty
      <div class="text-center text-muted py-4"><i class="bx bx-info-circle fs-1 d-block mb-2"></i>Kontak bantuan belum tersedia. Silakan hubungi pengelola sistem.</div>
     @endforelse
   </div>
  </div><div class="modal-footer border-0 pt-0 px-4 pb-4"><button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Batal</button></div>
 </div></div>
</div>
@endif
<script src="{{ asset('assets/js/integral-public-motion.js') }}?v=20261118-page-logo"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){const input=document.getElementById('loginPassword'),button=document.getElementById('toggleLoginPassword');button?.addEventListener('click',function(){const visible=input.type==='text';input.type=visible?'password':'text';this.setAttribute('aria-label',visible?'Tampilkan password':'Sembunyikan password');this.setAttribute('aria-pressed',String(!visible));this.querySelector('i').className=visible?'bx bx-show fs-5':'bx bx-hide fs-5';input.focus();});});
</script>
@endsection
