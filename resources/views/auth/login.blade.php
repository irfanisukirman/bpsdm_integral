@extends('layouts.auth')

@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
        <!-- Register Card -->
        <div class="card shadow-lg border-0">
            <div class="card-body">
                <!-- Logo -->
                <div class="app-brand justify-content-center mb-4">
                    <a href="/" class="app-brand-link gap-2">
                        <span class="app-brand-logo demo">
                            <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" alt="Integral Logo" style="width: 50px;">
                        </span>
                        <span class="app-brand-text demo text-body fw-bolder text-uppercase" style="font-size: 1.5rem; letter-spacing: 1px;">INTEGRAL</span>
                    </a>
                </div>
                <!-- /Logo -->
                
                <h4 class="mb-2 fw-bold text-center">Selamat Datang! 👋</h4>
                <p class="mb-4 text-center text-muted">Silakan masuk untuk mengakses sistem pelatihan Anda.</p>

                {{-- Alert Error jika gagal --}}
                @if(session('error'))
                    <div class="alert alert-danger border-0 small py-2 mb-3">
                        <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                    </div>
                @endif

                <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label">Username / NIP</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" placeholder="Masukkan username" autofocus value="{{ old('username') }}" />
                        @error('username')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3 form-password-toggle">
                        <div class="d-flex justify-content-between">
                            <label class="form-label" for="password">Password</label>
                            <a href="#">
                                <small>Lupa Password?</small>
                            </a>
                        </div>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="••••••••" />
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary d-grid w-100 shadow-sm py-2" type="submit">Sign in</button>
                    </div>
                </form>

                {{-- DIVIDER --}}
                <div class="divider my-4">
                    <div class="divider-text text-muted">Atau login melalui</div>
                </div>

                {{-- TOMBOL GOOGLE --}}
                <div class="d-flex justify-content-center">
                    <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center py-2 google-btn">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google Logo" style="width: 18px;" class="me-2">
                        <span class="fw-semibold">Google Account</span>
                    </a>
                </div>

                <p class="text-center mt-4 mb-0">
                    <small class="text-muted">Sim-Pel &copy; {{ date('Y') }}</small>
                </p>
            </div>
        </div>
        <!-- /Register Card -->
    </div>
</div>

<style>
    body {
        background: linear-gradient(135deg, #696cff 0%, #43459d 100%);
    }
    .authentication-wrapper.authentication-basic .authentication-inner {
        max-width: 420px;
    }
    .card {
        border-radius: 15px;
    }
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
    }
    .divider::before, .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #d9dee3;
    }
    .divider:not(:empty)::before {
        margin-right: .5rem;
    }
    .divider:not(:empty)::after {
        margin-left: .5rem;
    }
    .google-btn {
        transition: all 0.2s;
        border-color: #d9dee3;
    }
    .google-btn:hover {
        background-color: #f8f9fa !important;
        border-color: #696cff !important;
        color: #696cff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
</style>
@endsection