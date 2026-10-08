@extends('layouts.form')

{{-- Konfigurasi Header & Metadata Form --}}
@section('form_title', 'Lengkapi Profil Pengguna')
@section('module_name', 'Registrasi Pengguna')
@section('page_title', 'Lengkapi Profil Pengguna')
@section('page_description', 'Silakan lengkapi data identitas, instansi, pekerjaan, dan wilayah kerja Anda untuk integrasi sistem INTEGRAL.')
@section('form_action', route('participant.profile.store'))
@section('submit_text', 'Simpan Profil & Lanjutkan')

{{-- KONTEN INPUTAN FORM --}}
@section('form_content')

    @php
        $steps = [];
        if ($user->must_change_password) {
            $steps[] = ['key' => 'password', 'label' => 'Password', 'fields' => ['password', 'password_confirmation']];
        }
        $steps[] = ['key' => 'personal', 'label' => 'Personal', 'fields' => ['user_type', 'nip_nik', 'whatsapp', 'gender', 'birth_place', 'birth_date']];
        $steps[] = ['key' => 'instansi', 'label' => 'Instansi (ASN)', 'fields' => ['instansi']];
        $steps[] = ['key' => 'pekerjaan', 'label' => 'Pekerjaan (ASN)', 'fields' => ['jabatan', 'status_kepegawaian']];
        $steps[] = ['key' => 'detail', 'label' => 'Detail', 'fields' => ['provinsi', 'kota', 'kecamatan', 'kelurahan', 'address', 'latitude', 'longitude']];

        $activeStep = \App\Support\FormStepper::initialStep($steps);
        $personalStep = $user->must_change_password ? 1 : 0;
        $errorCounts = \App\Support\FormStepper::errorCountsPerStep($errors, $steps);

        $errors = \App\Support\FormStepper::errorsUpToStep($errors, $steps, $activeStep);
    @endphp

    @if($user->must_complete_profile || $user->must_change_password)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="d-flex gap-3"><i class="bx bx-shield-quarter fs-3"></i><div><strong>Akun hasil import peserta</strong><div class="small mt-1">Username login Anda adalah NIP/NIK <strong>{{ $user->nip_nik }}</strong>. Lengkapi seluruh profil dan buat password baru sebelum mengakses pelatihan.</div></div></div>
        </div>
    @endif

    @include('partials.profile.stepper', [
        'steps' => $steps,
        'activeStep' => $activeStep,
        'errorCounts' => $errorCounts,
    ])

    @if($user->must_change_password)
        <div class="card shadow-sm border-0{{ $activeStep !== 0 ? ' d-none' : '' }} mb-4" data-section="password">
            <div class="card-body p-4">
                <div class="form-section-title"><i class="bx bx-lock-alt fs-4 me-2"></i> Password Baru</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password baru <span class="required-star">*</span></label>
                        <input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password" required>
                        <div class="form-text">Minimal 8 karakter dan jangan gunakan NIP/NIK sebagai password.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Konfirmasi password <span class="required-star">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password" required>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Data profil dibagi per section dan dipakai bersama dengan form
         pendaftaran publik (auth.register) --}}
    @include('partials.profile.section-personal', [
        'profile' => $user,
        'lockUserType' => (bool) $user->must_complete_profile,
        'hidden' => $activeStep !== $personalStep,
    ])

    @include('partials.profile.section-instansi', [
        'profile' => $user,
        'hidden' => $activeStep !== $personalStep + 1,
    ])

    @include('partials.profile.section-pekerjaan', [
        'profile' => $user,
        'hidden' => $activeStep !== $personalStep + 2,
    ])

    @include('partials.profile.section-detail', [
        'profile' => $user,
        'hidden' => $activeStep !== $personalStep + 3,
    ])

    @include('partials.profile.wizard-nav')

@endsection

@push('form_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('form_js')
@include('partials.profile.location-script', ['profile' => $user])
@endpush