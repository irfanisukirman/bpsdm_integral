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
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title"><i class="bx bx-lock-alt fs-4 me-2"></i>Password Baru</div>
            <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Password baru <span class="required-star">*</span></label><input type="password" name="password" class="form-control" minlength="8" required><div class="form-text">Minimal 8 karakter dan jangan gunakan NIP/NIK sebagai password.</div></div><div class="col-md-6 mb-3"><label class="form-label">Konfirmasi password <span class="required-star">*</span></label><input type="password" name="password_confirmation" class="form-control" minlength="8" required></div></div>
        </div>
    </div>
    @endif
    <!-- KARTU 1: IDENTITAS & KEPEGAWAIAN -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-user-pin fs-4 me-2"></i> 1. Identitas Utama & Kepegawaian
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Daftar Sebagai <span class="required-star">*</span></label>
                @if($user->must_complete_profile)<input type="hidden" name="user_type" value="peserta">@endif
                <select name="user_type" id="userTypeSelect" class="form-select form-select-lg border-primary" required @disabled($user->must_complete_profile)>
                    <option value="">-- Pilih tujuan pendaftaran --</option>
                    <option value="peserta" @selected(old('user_type', $user->user_type)==='peserta')>Peserta Pelatihan</option>
                    <option value="narasumber" @selected(old('user_type', $user->user_type)==='narasumber')>Narasumber / Pengajar</option>
                    <option value="mitra" @selected(old('user_type', $user->user_type)==='mitra')>Mitra Kerja Sama</option>
                </select>
                <div id="userTypeExplanation" class="alert alert-danger py-2 mt-3 mb-0 small d-none" role="alert"></div>
                <div class="form-text mt-2"><i class="bx bx-shield-quarter me-1"></i>Akun admin dan superadmin tidak dapat dibuat melalui registrasi publik.</div>
            </div>

            <div class="row">
                <!-- NIP / NIK -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIP / NIK <span class="required-star">*</span></label>
                    <input type="text" 
                           name="nip_nik" 
                           class="form-control" 
                           placeholder="Contoh: 19950303..." 
                           value="{{ old('nip_nik', auth()->user()->nip_nik) }}" 
                           required>
                    <div class="form-text small text-info">Gunakan NIP asli Anda untuk sinkronisasi riwayat pelatihan.</div>
                </div>

                <!-- Nomor WhatsApp -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor WhatsApp <span class="required-star">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bxl-whatsapp text-success"></i></span>
                        <input type="number" 
                               name="whatsapp" 
                               class="form-control" 
                               placeholder="62812345678" 
                               value="{{ old('whatsapp', auth()->user()->whatsapp) }}" 
                               required>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Gender -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gender <span class="required-star">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="">-- Pilih Gender --</option>
                        <option value="Laki-Laki" {{ old('gender', auth()->user()->gender) == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="Perempuan" {{ old('gender', auth()->user()->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <!-- Status Kepegawaian -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Kepegawaian <span class="required-star">*</span></label>
                    <select name="employment_status_id" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
                        @foreach($employmentStatuses as $status)<option value="{{$status->id}}" @selected((string) old('employment_status_id', $user->employment_status_id) === (string) $status->id)>{{$status->name}}</option>@endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tempat Lahir <span class="required-star">*</span></label>
                    <input type="text" name="birth_place" class="form-control" value="{{old('birth_place',$user->birth_place)}}" placeholder="Contoh: Bandung" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Lahir <span class="required-star">*</span></label>
                    <input type="date" name="birth_date" class="form-control" value="{{old('birth_date',$user->birth_date?->format('Y-m-d'))}}" max="{{today()->toDateString()}}" required>
                </div>
            </div>

            <div class="row">
                <!-- Jabatan -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jabatan Saat Ini <span class="required-star">*</span></label>
                    <input type="text" 
                           name="jabatan" 
                           class="form-control" 
                           placeholder="Contoh: Analis SDM Aparatur" 
                           value="{{ old('jabatan') }}" 
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Golongan</label>
                    @php $selectedGolongan=old('golongan',$user->golongan); @endphp
                    <select name="golongan" class="form-select">
                        <option value="">-- Tidak memiliki golongan --</option>
                        @foreach(['I/a','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c','V','VI','VII','VIII','IX','X','XI','XII','XIII','XIV'] as $golongan)
                            <option value="{{$golongan}}" @selected($selectedGolongan===$golongan)>{{$golongan}}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Pilih golongan saat ini sesuai data kepegawaian.</div>
                </div>
                <!-- Instansi -->
                <div class="col-12 mb-3">
                    <label class="form-label">Instansi / Unit Kerja <span class="required-star">*</span></label>
                    <select name="institution_id" class="form-select" required><option value="">-- Pilih Instansi --</option>@foreach($institutions as $institution)<option value="{{$institution->id}}" @selected((string) old('institution_id', $user->institution_id) === (string) $institution->id)>{{$institution->name}}</option>@endforeach</select>
                </div>
            </div>

        </div>
    </div>

    <!-- KARTU 2: WILAYAH KERJA / DOMISILI (DENGAN PENCARIAN SELECT2) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-map-pin fs-4 me-2"></i> 2. Wilayah Domisili / Kerja
            </div>

            <div class="row">
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