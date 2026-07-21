@extends('layouts.auth')

@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
        <div class="card">
            <div class="card-body">
                <div class="app-brand justify-content-center">
                    <span class="app-brand-text demo text-body fw-bolder text-uppercase">SIM-PEL</span>
                </div>
                <h4 class="mb-2">Selamat Datang! 👋</h4>
                <p class="mb-4">Silakan login untuk mengelola sistem pelatihan.</p>

                <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" placeholder="Masukkan username" autofocus value="{{ old('username') }}" />
                        @error('username')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="••••••••" />
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection