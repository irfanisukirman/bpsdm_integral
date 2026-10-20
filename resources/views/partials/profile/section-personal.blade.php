{{--
    Section 2 - Data Personal.
    Dipakai bersama oleh auth.register dan participant.complete_profile.

    Parameter:
      $profile      App\Models\User (boleh kosong untuk pengguna baru)
      $lockUserType true bila pilihan "Daftar Sebagai" harus dikunci
      $hidden       true bila container ini bukan langkah wizard yang aktif
--}}
<div class="card shadow-sm border-0{{ ($hidden ?? false) ? ' d-none' : '' }} mb-4" data-section="personal">
    <div class="card-body p-4">
        <div class="form-section-title">
            <i class="bx bx-user-pin fs-4 me-2"></i> Data Personal
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">Daftar Sebagai <span class="required-star">*</span></label>
            @if($lockUserType ?? false)
                <input type="hidden" name="user_type" value="peserta">
            @endif
            <select name="user_type" id="userTypeSelect" class="form-select form-select-lg border-primary" required @disabled($lockUserType ?? false)>
                <option value="">-- Pilih tujuan pendaftaran --</option>
                <option value="peserta" @selected(old('user_type', $profile->user_type) === 'peserta')>Peserta Pelatihan</option>
                <option value="narasumber" @selected(old('user_type', $profile->user_type) === 'narasumber')>Narasumber / Pengajar</option>
                <option value="mitra" @selected(old('user_type', $profile->user_type) === 'mitra')>Mitra Kerja Sama</option>
            </select>
            <div id="userTypeExplanation" class="alert alert-danger py-2 mt-3 mb-0 small d-none" role="alert"></div>
            <div class="form-text mt-2"><i class="bx bx-shield-quarter me-1"></i>Akun admin dan superadmin tidak dapat dibuat melalui registrasi publik.</div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">NIP / NIK <span class="required-star">*</span></label>
                <input type="text"
                       name="nip_nik"
                       class="form-control"
                       placeholder="Contoh: 19950303..."
                       value="{{ old('nip_nik', $profile->nip_nik) }}"
                       autocomplete="off"
                       required>
                <div class="form-text small text-info">NIP asli untuk sinkronisasi riwayat pelatihan. NIP/NIK ini juga dipakai untuk masuk ke sistem.</div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Nomor WhatsApp <span class="required-star">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bxl-whatsapp text-success"></i></span>
                    <input type="tel"
                           name="whatsapp"
                           class="form-control"
                           placeholder="62812345678"
                           value="{{ old('whatsapp', $profile->whatsapp) }}"
                           autocomplete="off"
                           required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Gender <span class="required-star">*</span></label>
                <select name="gender" class="form-select" required>
                    <option value="">-- Pilih Gender --</option>
                    <option value="Laki-Laki" @selected(old('gender', $profile->gender) === 'Laki-Laki')>Laki-Laki</option>
                    <option value="Perempuan" @selected(old('gender', $profile->gender) === 'Perempuan')>Perempuan</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tempat Lahir <span class="required-star">*</span></label>
                <input type="text" name="birth_place" class="form-control" value="{{ old('birth_place', $profile->birth_place) }}" placeholder="Contoh: Bandung" autocomplete="off" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Lahir <span class="required-star">*</span></label>
                <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $profile->birth_date?->format('Y-m-d')) }}" max="{{ today()->toDateString() }}" required>
            </div>
        </div>
    </div>
</div>