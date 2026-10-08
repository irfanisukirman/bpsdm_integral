@extends('layouts.form')

@section('form_title', 'Pendaftaran Akun')
@section('module_name', 'Registrasi Pengguna')
@section('page_title', 'Pendaftaran Akun')
@section('page_description', 'Lengkapi setiap section. Lingkaran hijau menandakan section yang sudah terisi.')
@section('form_action', route('register'))
@section('submit_text', 'Daftar & Buat Akun')
@section('back_url', route('login'))

@section('form_content')

    @php
        $steps = [
            ['key' => 'account', 'label' => 'Akun', 'fields' => ['name', 'email', 'email_confirmation', 'password', 'password_confirmation']],
            ['key' => 'personal', 'label' => 'Personal', 'fields' => ['user_type', 'nip_nik', 'whatsapp', 'gender', 'birth_place', 'birth_date']],
            ['key' => 'instansi', 'label' => 'Instansi (ASN)', 'fields' => ['instansi']],
            ['key' => 'pekerjaan', 'label' => 'Pekerjaan (ASN)', 'fields' => ['jabatan', 'status_kepegawaian']],
            ['key' => 'detail', 'label' => 'Detail', 'fields' => ['provinsi', 'kota', 'kecamatan', 'kelurahan', 'address', 'latitude', 'longitude']],
        ];
        $activeStep = \App\Support\FormStepper::initialStep($steps);
        $errorCounts = \App\Support\FormStepper::errorCountsPerStep($errors, $steps);

        // Layout menerima $errors sebagai data eksplisit, jadi harus ditugaskan
        // ulang di scope view ini: error langkah yang belum dibuka disembunyikan.
        $errors = \App\Support\FormStepper::errorsUpToStep($errors, $steps, $activeStep);
    @endphp

    @include('partials.profile.stepper', [
        'steps' => $steps,
        'activeStep' => $activeStep,
        'errorCounts' => $errorCounts,
    ])

    {{-- 1. AKUN --}}
    <div class="card shadow-sm border-0{{ $activeStep !== 0 ? ' d-none' : '' }} mb-4" data-section="account">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-user fs-4 me-2"></i> Akun
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Lengkap <span class="required-star">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Nama sesuai identitas resmi" autocomplete="name" required>
                <div class="form-text">Nama ini tampil pada sertifikat dan daftar peserta.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="required-star">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="nama@domain.go.id" autocomplete="email" required>
                    </div>
                    <div class="form-text">Dipakai untuk pemberitahuan sistem.</div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Konfirmasi Email <span class="required-star">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-check-double"></i></span>
                        <input type="email" name="email_confirmation" class="form-control" value="{{ old('email_confirmation') }}" placeholder="Ulangi email" autocomplete="off" required>
                    </div>
                    <div class="form-text">Ulangi email untuk memastikan tidak salah ketik.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <span class="required-star">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                        <input id="registerPassword" type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Konfirmasi Password <span class="required-star">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" autocomplete="new-password" required>
                    </div>
                    <div class="form-text">Jangan gunakan NIP/NIK sebagai password.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. PERSONAL | 3. INSTANSI (ASN) | 4. PEKERJAAN (ASN) | 5. DETAIL
         (dipakai bersama dengan form pelengkapan profil peserta) --}}
    @include('partials.profile.section-personal', [
        'profile' => $user,
        'lockUserType' => false,
        'hidden' => $activeStep !== 1,
    ])

    @include('partials.profile.section-instansi', [
        'profile' => $user,
        'hidden' => $activeStep !== 2,
    ])

    @include('partials.profile.section-pekerjaan', [
        'profile' => $user,
        'golonganOptions' => $golonganOptions,
        'hidden' => $activeStep !== 3,
    ])

    @include('partials.profile.section-detail', [
        'profile' => $user,
        'addressSearchUrl' => route('register.address-search'),
        'hidden' => $activeStep !== 4,
    ])

    @include('partials.profile.wizard-nav')

@endsection

@push('form_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('form_js')
@include('partials.profile.location-script', [
    'profile' => $user,
    'addressSearchUrl' => route('register.address-search'),
])
@endpush

<script>
$(document).ready(function() {
    const email = document.querySelector('input[name="email"]');
    const emailConfirmation = document.querySelector('input[name="email_confirmation"]');
    const password = document.getElementById('registerPassword');
    const passwordConfirmation = password.closest('.row').querySelector('input[name="password_confirmation"]');

    function validateEmailConfirmation() {
        const mismatch = emailConfirmation.value.length > 0 && email.value.trim().toLowerCase() !== emailConfirmation.value.trim().toLowerCase();
        emailConfirmation.setCustomValidity(mismatch ? 'Konfirmasi email tidak sama dengan email.' : '');
    }

    function validatePasswordConfirmation() {
        const mismatch = passwordConfirmation.value.length > 0 && password.value !== passwordConfirmation.value;
        passwordConfirmation.setCustomValidity(mismatch ? 'Konfirmasi password tidak sama dengan password baru.' : '');
    }

    [email, emailConfirmation].forEach(el => el && el.addEventListener('input', validateEmailConfirmation));
    [password, passwordConfirmation].forEach(el => el && el.addEventListener('input', validatePasswordConfirmation));
});
</script>