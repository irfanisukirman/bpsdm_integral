@extends('layouts.master')

@section('title', 'Pengaturan Profil')

@section('content')
@php
    $participantProfile = $user->primaryRole() === 'participant';
    $profileLocked = $participantProfile && !$errors->any();
@endphp
<div class="container-xxl flex-grow-1 container-p-y" @if($participantProfile) data-profile-mode="{{ $profileLocked ? 'view' : 'edit' }}" @endif>
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Akun /</span> Pengaturan Profil
    </h4>

    <!-- Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 shadow-sm mb-4" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('profile_saved'))
        <div class="modal fade" id="profileSavedModal" tabindex="-1" aria-labelledby="profileSavedTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-4 text-center">
                        <i class="bx bx-check-circle fs-1 text-success mb-2" aria-hidden="true"></i>
                        <h5 id="profileSavedTitle">Berhasil disimpan</h5>
                        <p class="mb-3">{{ session('profile_saved') }}</p>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible border-0 shadow-sm mb-4" role="alert">
            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible border-0 shadow-sm mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="bx bx-error-circle me-1"></i>Profil belum dapat disimpan</div>
            <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <!-- CARD 1: DETAIL PROFIL -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0">Detail Profil Pengguna</h5>
                    @if($participantProfile)
                        <div class="d-flex align-items-center gap-2">
                            <span id="profileModeLabel" class="badge {{ $profileLocked ? 'bg-label-secondary' : 'bg-label-success' }}" role="status">{{ $profileLocked ? 'Mode baca' : 'Mode edit' }}</span>
                            <button id="editProfileButton" type="button" class="btn btn-primary btn-sm"
                                aria-controls="profileFields profilePasswordFields" @unless($profileLocked) hidden @endunless>
                                <i class="bx bx-edit me-1" aria-hidden="true"></i>Edit Profil
                            </button>
                        </div>
                    @endif
                </div>
                
                <form id="formAccountSettings" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf 
                    @method('PUT')
                    <fieldset id="profileFields" @disabled($profileLocked)>
                    
                    <!-- Bagian Foto Profil -->
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            @php $profilePhotoUrl = $user->profile_photo ? asset('storage/' . $user->profile_photo) : $user->avatar; @endphp
                            <img @if($profilePhotoUrl) src="{{ $profilePhotoUrl }}" @endif alt="Foto profil" class="rounded shadow" height="100" width="100" id="uploadedAvatar" style="object-fit: cover;" @unless($profilePhotoUrl) hidden @endunless>
                            @unless($profilePhotoUrl)
                                <div class="avatar avatar-xl" id="profileAvatarPlaceholder">
                                    <span class="avatar-initial rounded bg-label-primary" style="font-size: 40px;">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endunless

                            <div class="button-wrapper" data-profile-edit-only>
                                <button type="button" id="chooseProfilePhoto" class="btn btn-primary mb-2">Pilih Foto Baru</button>
                                <input type="file" id="profilePhotoPicker" hidden accept="image/png,image/jpeg,image/webp">
                                <input type="file" id="profilePhotoUpload" name="profile_photo" hidden>
                                <p class="text-muted mb-0 small">JPG, PNG, atau WebP. Maksimal 5 MB.</p>
                                <p id="profilePhotoStatus" class="small mb-0 mt-1" role="status"></p>
                                @error('profile_photo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-0" />

                    <div class="card-body">
                        <h6 class="text-primary mb-3 fw-bold text-uppercase"><i class="bx bx-user me-1"></i> Informasi Dasar</h6>
                        @php
                            $roleLabels = \App\Support\UserRoles::LABELS;
                            $assignedRoles = $user->assignedRoles();
                            $hasMultipleRoles = count($assignedRoles) > 1;
                            $showEvaluatorMembership = $isEvaluationTeamMember && !in_array('evaluator', $assignedRoles, true);
                            $accountTypeLabel = match($user->user_type) {
                                'narasumber' => 'Narasumber',
                                'mitra' => 'Mitra',
                                default => $roleLabels[$user->primaryRole()] ?? 'Peserta',
                            };
                        @endphp
                        @if($hasMultipleRoles || $showEvaluatorMembership || !in_array($user->role, ['superadmin', 'admin_bidang', 'admin_aset'], true))
                            <div class="alert alert-{{ $user->user_type_status === 'pending' ? 'warning' : 'info' }} d-flex align-items-center gap-2 py-2">
                                <i class="bx {{ $user->user_type === 'narasumber' ? 'bx-chalkboard' : ($user->user_type === 'mitra' ? 'bx-handshake' : 'bx-user') }} fs-5"></i>
                                <div>
                                    <strong>Jenis akun: {{ $accountTypeLabel }}</strong>
                                    @if($hasMultipleRoles)
                                        <small class="d-block mt-1">Peran tersedia: {{ collect($assignedRoles)->map(fn ($role) => $roleLabels[$role] ?? $role)->implode(', ') }}</small>
                                        <small class="d-block">Peran aktif: {{ $roleLabels[$user->role] ?? $user->role }}</small>
                                    @endif
                                    @if($showEvaluatorMembership)
                                        <small class="d-block mt-1">Penugasan tambahan: Evaluator Pasca (anggota tim evaluasi)</small>
                                    @endif
                                    @if($user->user_type_status === 'pending')
                                        <small class="d-block">Pengajuan sedang menunggu persetujuan superadmin.</small>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input class="form-control" type="text" name="name" value="{{ old('name', $user->name) }}" required />
                            </div>
                            @if($user->primaryRole() === 'participant')
                            <div class="mb-3 col-md-6">
                                <label for="profileNip" class="form-label fw-bold">NIP <span class="text-danger">*</span></label>
                                <input id="profileNip" class="form-control" type="text" name="nip" inputmode="numeric" pattern="[0-9]{18}" minlength="18" maxlength="18" value="{{ old('nip', $user->nip ?? (preg_match('/^[0-9]{18}$/', $user->nip_nik ?? '') ? $user->nip_nik : '')) }}" required />
                                <div class="form-text">Nomor Induk Pegawai, 18 digit.</div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="profileNik" class="form-label fw-bold">NIK <span class="text-danger">*</span></label>
                                <input id="profileNik" class="form-control" type="text" name="nik" inputmode="numeric" pattern="[0-9]{16}" minlength="16" maxlength="16" value="{{ old('nik', $user->nik ?? (preg_match('/^[0-9]{16}$/', $user->nip_nik ?? '') ? $user->nip_nik : '')) }}" required />
                                <div class="form-text">Nomor Induk Kependudukan sesuai KTP, 16 digit.</div>
                            </div>
                            @else
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">NIP / NIK</label>
                                <input class="form-control" type="text" name="nip_nik" value="{{ old('nip_nik', $user->nip_nik) }}" required />
                            </div>
                            @endif
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Username (Login ID)</label>
                                <input class="form-control bg-light" type="text" value="{{ $user->username }}" disabled />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Nomor WhatsApp</label>
                                <input class="form-control" type="text" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="Laki-Laki" {{ $user->gender == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                                    <option value="Perempuan" {{ $user->gender == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Status Kepegawaian</label>
                                <select name="employment_status_id" class="form-select" required>
                                    <option value="">-- Pilih Status --</option>
                                    @foreach($employmentStatuses as $status)
                                        <option value="{{$status->id}}" @selected((string)old('employment_status_id',$user->employment_status_id)===(string)$status->id)>{{$status->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Tempat Lahir</label>
                                <input class="form-control" type="text" name="birth_place" value="{{old('birth_place',$user->birth_place)}}" placeholder="Contoh: Bandung" required>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Tanggal Lahir</label>
                                <input class="form-control" type="date" name="birth_date" value="{{old('birth_date',$user->birth_date?->format('Y-m-d'))}}" max="{{today()->toDateString()}}" required>
                            </div>
                        </div>

                        <hr class="my-4" />
                        <h6 class="text-primary mb-3 fw-bold text-uppercase"><i class="bx bx-briefcase me-1"></i> Informasi Pekerjaan</h6>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Jabatan</label>
                                <input class="form-control" type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" placeholder="Contoh: Analis SDM" />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Golongan</label>
                                @php $selectedGolongan=old('golongan',$user->golongan); @endphp
                                <select name="golongan" class="form-select">
                                    <option value="">-- Tidak memiliki golongan --</option>
                                    @foreach(['I/a','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c','V','VI','VII','VIII','IX','X','XI','XII','XIII','XIV'] as $golongan)
                                        <option value="{{$golongan}}" @selected($selectedGolongan===$golongan)>{{$golongan}}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih golongan saat ini sesuai data kepegawaian.</div>
                            </div>
                            @if($user->primaryRole() === 'participant')
                                @include('profile.partials.institution-fields')
                            @else
                            <div class="mb-3 col-12">
                                <label class="form-label fw-bold">Instansi / Unit Kerja</label>
                                <select class="form-select" name="institution_id" required><option value="">-- Pilih Instansi --</option>@foreach($institutions as $institution)<option value="{{$institution->id}}" @selected((string)old('institution_id',$user->institution_id)===(string)$institution->id)>{{$institution->name}}</option>@endforeach</select>
                            </div>
                            @endif
                        </div>

                        <hr class="my-4" />
                        <h6 class="text-primary mb-3 fw-bold text-uppercase"><i class="bx bx-map me-1"></i> Data Wilayah Domisili</h6>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Provinsi</label>
                                <select id="provinsi" name="provinsi" class="form-select border-primary">
                                    <option value="{{ old('provinsi', $user->provinsi) }}">{{ old('provinsi', $user->provinsi) }}</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Kabupaten / Kota</label>
                                <select id="kota" name="kota" class="form-select border-primary">
                                    <option value="{{ old('kota', $user->kota) }}">{{ old('kota', $user->kota) }}</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Kecamatan</label>
                                <select id="kecamatan" name="kecamatan" class="form-select border-primary">
                                    <option value="{{ old('kecamatan', $user->kecamatan) }}">{{ old('kecamatan', $user->kecamatan) }}</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Kelurahan / Desa</label>
                                <select id="kelurahan" name="kelurahan" class="form-select border-primary">
                                    <option value="{{ old('kelurahan', $user->kelurahan) }}">{{ old('kelurahan', $user->kelurahan) }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Alamat Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-search-alt"></i></span>
                                    <input type="text" name="address" id="addressSearch" class="form-control" value="{{old('address',$user->address)}}" placeholder="Contoh: Jl. Nihmat, Bandung" autocomplete="street-address" required>
                                    <button type="button" id="searchAddressButton" class="btn btn-primary" data-profile-edit-only><i class="bx bx-search me-1"></i>Cari Alamat</button>
                                </div>
                                <div class="form-text" data-profile-edit-only>Tekan Cari Alamat, lalu pilih hasil yang sesuai. Peta dan koordinat akan diarahkan otomatis.</div>
                                <div id="addressSearchResults" class="list-group mt-2 shadow-sm d-none" style="max-height:260px;overflow-y:auto"></div>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <div>
                                    <label class="form-label fw-bold mb-0">Titik Lokasi Desa/Kelurahan</label>
                                    <div class="form-text" data-profile-edit-only>Klik titik domisili pada peta agar sebaran alumni tampil lebih akurat.</div>
                                </div>
                                <button type="button" id="useCurrentLocation" class="btn btn-sm btn-outline-primary" data-profile-edit-only>
                                    <i class="bx bx-current-location me-1"></i>Gunakan Lokasi Saya
                                </button>
                            </div>
                            <div id="profileEditLocationMap" class="rounded border" style="height: 360px;"></div>
                            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $user->latitude) }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $user->longitude) }}">
                            <div id="coordinateStatus" class="small mt-2 text-muted">Belum ada titik lokasi tersimpan.</div>
                        </div>

                        <div class="mt-4 pt-3 border-top" data-profile-edit-only>
                            <button type="submit" class="btn btn-primary me-2 shadow-sm">SIMPAN PERUBAHAN PROFIL</button>
                            @if($participantProfile)
                                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">BATAL</a>
                            @else
                                <button type="reset" class="btn btn-outline-secondary">BATAL</button>
                            @endif
                        </div>
                    </div>
                    </fieldset>
                </form>
            </div>

            <!-- CARD 2: GANTI PASSWORD -->
            <div class="card shadow-sm border-0">
                <h5 class="card-header border-bottom">Ganti Password</h5>
                <div class="card-body pt-4">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf 
                        @method('PUT')
                        <fieldset id="profilePasswordFields" @disabled($profileLocked)>
                        <div class="row">
                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label class="form-label fw-bold">Password Baru</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" name="new_password" placeholder="••••••••" required />
                                    <span class="input-group-text cursor-pointer" data-profile-edit-only><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label class="form-label fw-bold">Konfirmasi Password Baru</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" name="new_password_confirmation" placeholder="••••••••" required />
                                    <span class="input-group-text cursor-pointer" data-profile-edit-only><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning fw-bold" data-profile-edit-only>PERBARUI PASSWORD</button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="profilePhotoCropModal" tabindex="-1" aria-labelledby="profilePhotoCropTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profilePhotoCropTitle">Potong Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <canvas id="profilePhotoCanvas" width="512" height="512" class="d-block rounded mx-auto mb-3" style="width: min(100%, 320px); height: auto;" aria-label="Pratinjau potongan foto profil"></canvas>
                <label for="profilePhotoZoom" class="form-label">Perbesar</label>
                <input type="range" id="profilePhotoZoom" class="form-range" min="100" max="300" value="100">
                <label for="profilePhotoX" class="form-label">Geser kiri / kanan</label>
                <input type="range" id="profilePhotoX" class="form-range" min="-100" max="100" value="0">
                <label for="profilePhotoY" class="form-label">Geser atas / bawah</label>
                <input type="range" id="profilePhotoY" class="form-range" min="-100" max="100" value="0">
                <p id="profilePhotoCropError" class="small text-danger mb-0" role="alert"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="applyProfilePhoto" class="btn btn-primary">Gunakan Foto</button>
            </div>
        </div>
    </div>
</div>

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #editProfileButton[hidden], [data-profile-mode="view"] :is([data-profile-edit-only], .form-text) { display: none !important; }
    [data-profile-mode="view"] fieldset :is(.form-control, .form-select):disabled {
        opacity: 1;
        background-color: var(--integral-raised, #f4f7f5) !important;
        color: var(--integral-text, #263c31) !important;
        border-color: var(--integral-border, #dbe7df) !important;
        cursor: default;
    }
    [data-profile-mode="view"] fieldset .form-select:disabled { background-image: none; }
</style>
@endpush

@push('js')
<script src="{{ asset('assets/js/integral-profile-photo.js') }}"></script>
<script>
    (() => {
        const editor = document.querySelector('[data-profile-mode]');
        if (!editor) return;
        const editButton = document.getElementById('editProfileButton');
        const profileFields = document.getElementById('profileFields');
        const passwordFields = document.getElementById('profilePasswordFields');
        const badge = document.getElementById('profileModeLabel');

        editButton.addEventListener('click', () => {
            editor.dataset.profileMode = 'edit';
            profileFields.disabled = false;
            passwordFields.disabled = false;
            editButton.hidden = true;
            badge.textContent = 'Mode edit';
            badge.classList.replace('bg-label-secondary', 'bg-label-success');
            profileFields.dispatchEvent(new Event('profile-edit-enabled'));
            profileFields.querySelector('[name="name"]').focus();
        });
        for (const fields of [profileFields, passwordFields]) {
            fields.form.addEventListener('submit', event => {
                if (fields.disabled) event.preventDefault();
            });
        }
        const saved = document.getElementById('profileSavedModal');
        if (saved) bootstrap.Modal.getOrCreateInstance(saved).show();
    })();
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const baseUrl = "https://www.emsifa.com/api-wilayah-indonesia/api/";
    const userProv = @json(old('provinsi', $user->provinsi));
    const userKota = @json(old('kota', $user->kota));
    const userKec = @json(old('kecamatan', $user->kecamatan));
    const userKel = @json(old('kelurahan', $user->kelurahan));

    const profileFields = document.getElementById('profileFields');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const coordinateStatus = document.getElementById('coordinateStatus');
    const initialLat = Number.parseFloat(latitudeInput.value);
    const initialLng = Number.parseFloat(longitudeInput.value);
    const hasInitialPoint = Number.isFinite(initialLat) && Number.isFinite(initialLng);
    const locationMap = typeof L === 'undefined' ? null
        : L.map('profileEditLocationMap').setView(hasInitialPoint ? [initialLat, initialLng] : [-2.5, 118], hasInitialPoint ? 15 : 5);
    let locationMarker = null;

    if (locationMap) {
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(locationMap);
    }

    function setLocationPoint(lat, lng, zoom = true, initial = false) {
        if (profileFields.disabled && !initial) return;
        const point = [Number(lat), Number(lng)];
        latitudeInput.value = point[0].toFixed(7);
        longitudeInput.value = point[1].toFixed(7);
        if (locationMap) {
            if (locationMarker) {
                locationMarker.setLatLng(point);
            } else {
                locationMarker = L.marker(point, { draggable: !profileFields.disabled }).addTo(locationMap);
                locationMarker.on('dragend', event => {
                    const position = event.target.getLatLng();
                    setLocationPoint(position.lat, position.lng, false);
                });
            }
            if (zoom) locationMap.setView(point, 16);
        }
        coordinateStatus.className = 'small mt-2 text-success';
        coordinateStatus.innerHTML = '<i class="bx bx-check-circle me-1"></i>Titik tersimpan: ' + latitudeInput.value + ', ' + longitudeInput.value;
    }

    locationMap?.on('click', event => setLocationPoint(event.latlng.lat, event.latlng.lng, false));
    profileFields.addEventListener('profile-edit-enabled', () => locationMarker?.dragging?.enable());
    if (hasInitialPoint) setLocationPoint(initialLat, initialLng, false, true);

@include('profile.partials.address-search-script')

    document.getElementById('useCurrentLocation').addEventListener('click', function() {
        if (!navigator.geolocation) return alert('Browser tidak mendukung deteksi lokasi.');
        this.disabled = true;
        navigator.geolocation.getCurrentPosition(
            position => {
                setLocationPoint(position.coords.latitude, position.coords.longitude);
                this.disabled = false;
            },
            () => {
                alert('Lokasi tidak dapat dibaca. Izinkan akses lokasi atau klik titik pada peta.');
                this.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    $(document).ready(function() {
        // --- 1. Load Provinsi Awal ---
        fetch(`${baseUrl}provinces.json`)
            .then(res => res.json())
            .then(data => {
                let html = '<option value="">-- Pilih Provinsi --</option>';
                data.forEach(p => {
                    html += `<option value="${p.name}" data-id="${p.id}" ${p.name == userProv ? 'selected' : ''}>${p.name}</option>`;
                });
                $('#provinsi').html(html);
                
                // Jika ada data lama, pancing load Kota
                if(userProv) $('#provinsi').trigger('change');
            });

        // --- 2. Load Kota (Dependent) ---
        $('#provinsi').on('change', function() {
            const provId = $(this).find(':selected').data('id');
            if(!provId) return;
            
            fetch(`${baseUrl}regencies/${provId}.json`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">-- Pilih Kota --</option>';
                    data.forEach(r => {
                        html += `<option value="${r.name}" data-id="${r.id}" ${r.name == userKota ? 'selected' : ''}>${r.name}</option>`;
                    });
                    $('#kota').html(html);
                    if(userKota) $('#kota').trigger('change');
                });
        });

        // --- 3. Load Kecamatan (Dependent) ---
        $('#kota').on('change', function() {
            const kotaId = $(this).find(':selected').data('id');
            if(!kotaId) return;

            fetch(`${baseUrl}districts/${kotaId}.json`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">-- Pilih Kecamatan --</option>';
                    data.forEach(d => {
                        html += `<option value="${d.name}" data-id="${d.id}" ${d.name == userKec ? 'selected' : ''}>${d.name}</option>`;
                    });
                    $('#kecamatan').html(html);
                    if(userKec) $('#kecamatan').trigger('change');
                });
        });

        // --- 4. Load Kelurahan (Dependent) ---
        $('#kecamatan').on('change', function() {
            const kecId = $(this).find(':selected').data('id');
            if(!kecId) return;

            fetch(`${baseUrl}villages/${kecId}.json`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">-- Pilih Kelurahan --</option>';
                    data.forEach(v => {
                        html += `<option value="${v.name}" ${v.name == userKel ? 'selected' : ''}>${v.name}</option>`;
                    });
                    $('#kelurahan').html(html);
                });
        });
    });
</script>
<style>
    body { background-color: #f5f5f9; }
    .form-control:focus { border-color: #696cff; box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.1); }
    .bg-label-primary { background-color: #e7e7ff !important; color: #696cff !important; }
</style>
@endpush
@endsection
