@extends('layouts.master')

@section('title', 'Pengaturan Profil')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible border-0 shadow-sm mb-4" role="alert">
            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <!-- CARD 1: DETAIL PROFIL -->
            <div class="card mb-4 shadow-sm border-0">
                <h5 class="card-header border-bottom">Detail Profil Pengguna</h5>
                
                <form id="formAccountSettings" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf 
                    @method('PUT')
                    
                    <!-- Bagian Foto Profil -->
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="user-avatar" class="d-block rounded shadow" height="100" width="100" id="uploadedAvatar" style="object-fit: cover;" />
                            @elseif($user->avatar)
                                <img src="{{ $user->avatar }}" alt="user-avatar" class="d-block rounded shadow" height="100" width="100" id="uploadedAvatar" style="object-fit: cover;" />
                            @else
                                <div class="avatar avatar-xl">
                                    <span class="avatar-initial rounded bg-label-primary" style="font-size: 40px;">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif

                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block text-uppercase small fw-bold">Unggah Foto Baru</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="upload" name="profile_photo" class="account-file-input" hidden accept="image/png, image/jpeg" />
                                </label>
                                <p class="text-muted mb-0 small">Format: JPG atau PNG. Ukuran Maksimal: 2MB.</p>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-0" />

                    <div class="card-body">
                        <h6 class="text-primary mb-3 fw-bold text-uppercase"><i class="bx bx-user me-1"></i> Informasi Dasar</h6>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input class="form-control" type="text" name="name" value="{{ old('name', $user->name) }}" required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">NIP / NIK</label>
                                <input class="form-control border-primary" type="text" name="nip_nik" value="{{ old('nip_nik', $user->nip_nik) }}" required />
                            </div>
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
                                <select name="status_kepegawaian" class="form-select">
                                    <option value="PNS" {{ $user->status_kepegawaian == 'PNS' ? 'selected' : '' }}>PNS</option>
                                    <option value="PPPK" {{ $user->status_kepegawaian == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                                    <option value="Non-ASN" {{ $user->status_kepegawaian == 'Non-ASN' ? 'selected' : '' }}>Non-ASN</option>
                                </select>
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
                                <label class="form-label fw-bold">Instansi / Unit Kerja</label>
                                <input class="form-control" type="text" name="instansi" value="{{ old('instansi', $user->instansi) }}" placeholder="Contoh: BPSDM Provinsi Jawa Barat" />
                            </div>
                        </div>

                        <hr class="my-4" />
                        <h6 class="text-primary mb-3 fw-bold text-uppercase"><i class="bx bx-map me-1"></i> Data Wilayah Domisili</h6>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Provinsi</label>
                                <select id="provinsi" name="provinsi" class="form-select border-primary">
                                    <option value="{{ $user->provinsi }}">{{ $user->provinsi }}</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Kabupaten / Kota</label>
                                <select id="kota" name="kota" class="form-select border-primary">
                                    <option value="{{ $user->kota }}">{{ $user->kota }}</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Kecamatan</label>
                                <select id="kecamatan" name="kecamatan" class="form-select border-primary">
                                    <option value="{{ $user->kecamatan }}">{{ $user->kecamatan }}</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">Kelurahan / Desa</label>
                                <select id="kelurahan" name="kelurahan" class="form-select border-primary">
                                    <option value="{{ $user->kelurahan }}">{{ $user->kelurahan }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <div>
                                    <label class="form-label fw-bold mb-0">Titik Lokasi Desa/Kelurahan</label>
                                    <div class="form-text">Klik titik domisili pada peta agar sebaran alumni tampil lebih akurat.</div>
                                </div>
                                <button type="button" id="useCurrentLocation" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-current-location me-1"></i>Gunakan Lokasi Saya
                                </button>
                            </div>
                            <div id="profileEditLocationMap" class="rounded border" style="height: 360px;"></div>
                            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $user->latitude) }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $user->longitude) }}">
                            <div id="coordinateStatus" class="small mt-2 text-muted">Belum ada titik lokasi tersimpan.</div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary me-2 shadow-sm">SIMPAN PERUBAHAN PROFIL</button>
                            <button type="reset" class="btn btn-outline-secondary">BATAL</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- CARD 2: GANTI PASSWORD -->
            <div class="card shadow-sm border-0">
                <h5 class="card-header border-bottom">Ganti Password</h5>
                <div class="card-body pt-4">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf 
                        @method('PUT')
                        <div class="row">
                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label class="form-label fw-bold">Password Baru</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" name="new_password" placeholder="••••••••" required />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-3 col-md-6 form-password-toggle">
                                <label class="form-label fw-bold">Konfirmasi Password Baru</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" name="new_password_confirmation" placeholder="••••••••" required />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning fw-bold">PERBARUI PASSWORD</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const baseUrl = "https://www.emsifa.com/api-wilayah-indonesia/api/";
    const userProv = "{{ $user->provinsi }}";
    const userKota = "{{ $user->kota }}";
    const userKec = "{{ $user->kecamatan }}";
    const userKel = "{{ $user->kelurahan }}";

    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const coordinateStatus = document.getElementById('coordinateStatus');
    const initialLat = Number.parseFloat(latitudeInput.value);
    const initialLng = Number.parseFloat(longitudeInput.value);
    const hasInitialPoint = Number.isFinite(initialLat) && Number.isFinite(initialLng);
    const locationMap = L.map('profileEditLocationMap').setView(hasInitialPoint ? [initialLat, initialLng] : [-2.5, 118], hasInitialPoint ? 15 : 5);
    let locationMarker = null;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(locationMap);

    function setLocationPoint(lat, lng, zoom = true) {
        const point = [Number(lat), Number(lng)];
        latitudeInput.value = point[0].toFixed(7);
        longitudeInput.value = point[1].toFixed(7);
        if (locationMarker) locationMarker.setLatLng(point);
        else locationMarker = L.marker(point, { draggable: true }).addTo(locationMap);
        locationMarker.off('dragend').on('dragend', event => {
            const position = event.target.getLatLng();
            setLocationPoint(position.lat, position.lng, false);
        });
        if (zoom) locationMap.setView(point, 16);
        coordinateStatus.className = 'small mt-2 text-success';
        coordinateStatus.innerHTML = '<i class="bx bx-check-circle me-1"></i>Titik tersimpan: ' + latitudeInput.value + ', ' + longitudeInput.value;
    }

    locationMap.on('click', event => setLocationPoint(event.latlng.lat, event.latlng.lng, false));
    if (hasInitialPoint) setLocationPoint(initialLat, initialLng, false);

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
