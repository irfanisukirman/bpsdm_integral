@extends('layouts.form')

{{-- Konfigurasi Header & Metadata Form --}}
@section('form_title', 'Lengkapi Profil Peserta')
@section('module_name', 'Portal Peserta')
@section('page_title', 'Lengkapi Profil Peserta')
@section('page_description', 'Silakan lengkapi data identitas dan wilayah kerja Anda untuk integrasi sistem INTEGRAL.')
@section('form_action', route('participant.profile.store'))
@section('submit_text', 'Simpan Profil & Lanjutkan')

{{-- KONTEN INPUTAN FORM --}}
@section('form_content')

    <!-- KARTU 1: IDENTITAS & KEPEGAWAIAN -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-user-pin fs-4 me-2"></i> 1. Identitas Utama & Kepegawaian
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
                    <select name="status_kepegawaian" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="PNS" {{ old('status_kepegawaian') == 'PNS' ? 'selected' : '' }}>PNS</option>
                        <option value="PPPK" {{ old('status_kepegawaian') == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                        <option value="Non-ASN" {{ old('status_kepegawaian') == 'Non-ASN' ? 'selected' : '' }}>Non-ASN</option>
                    </select>
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

                <!-- Instansi -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Instansi / Unit Kerja <span class="required-star">*</span></label>
                    <input type="text" 
                           name="instansi" 
                           class="form-control" 
                           placeholder="Contoh: BPSDM Provinsi Jawa Barat" 
                           value="{{ old('instansi') }}" 
                           required>
                </div>
            </div>

            <div class="mt-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <label class="form-label mb-0">Titik Lokasi Desa/Kelurahan <span class="required-star">*</span></label>
                        <div class="form-text">Klik lokasi domisili pada peta atau gunakan lokasi perangkat.</div>
                    </div>
                    <button type="button" id="useCurrentLocation" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-current-location me-1"></i>Gunakan Lokasi Saya
                    </button>
                </div>
                <div id="profileLocationMap" class="rounded border" style="height: 360px;"></div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', auth()->user()->latitude) }}" required>
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', auth()->user()->longitude) }}" required>
                <div id="coordinateStatus" class="small mt-2 text-muted">Belum ada titik dipilih.</div>
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
                        <label class="form-label">Provinsi</label>
                        <select id="provinsi" name="provinsi" class="form-select" required>
                            <option value="">-- Pilih Provinsi --</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kabupaten / Kota</label>
                        {{-- NAMA INPUT HARUS 'kota' --}}
                        <select id="kabupaten" name="kota" class="form-select" required disabled>
                            <option value="">Pilih Provinsi Dahulu</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kecamatan</label>
                    <select id="kecamatan" name="kecamatan" class="form-select border-primary" required disabled></select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kelurahan / Desa</label>
                    <select id="kelurahan" name="kelurahan" class="form-select border-primary" required disabled></select>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('form_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

{{-- SCRIPT FETCH API + SELECT2 SEARCH WILAYAH INDONESIA --}}
@push('form_js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>

    
$(document).ready(function() {
    const $provSelect = $('#provinsi');
    const $kabSelect = $('#kabupaten');
    const $kecSelect = $('#kecamatan');
    const $kelSelect = $('#kelurahan');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const coordinateStatus = document.getElementById('coordinateStatus');
    const initialLat = parseFloat(latitudeInput.value);
    const initialLng = parseFloat(longitudeInput.value);
    const hasInitialPoint = Number.isFinite(initialLat) && Number.isFinite(initialLng);
    const locationMap = L.map('profileLocationMap').setView(hasInitialPoint ? [initialLat, initialLng] : [-2.5, 118], hasInitialPoint ? 15 : 5);
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
        locationMarker.off('dragend').on('dragend', function(event) {
            const position = event.target.getLatLng();
            setLocationPoint(position.lat, position.lng, false);
        });
        if (zoom) locationMap.setView(point, 16);
        coordinateStatus.className = 'small mt-2 text-success';
        coordinateStatus.innerHTML = `<i class="bx bx-check-circle me-1"></i>Titik tersimpan: ${latitudeInput.value}, ${longitudeInput.value}`;
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

    // Inisialisasi Select2 ke semua dropdown wilayah
    function initSelect2(element, placeholderText) {
        element.select2({
            theme: 'bootstrap-5',
            placeholder: placeholderText,
            allowClear: true,
            width: '100%'
        });
    }

    initSelect2($provSelect, '-- Pilih / Cari Provinsi --');
    initSelect2($kabSelect, 'Pilih Provinsi Terlebih Dahulu');
    initSelect2($kecSelect, 'Pilih Kabupaten Terlebih Dahulu');
    initSelect2($kelSelect, 'Pilih Kecamatan Terlebih Dahulu');

    // 1. Load Semua Provinsi
    // 1. Load Provinsi
    fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json`)
    .then(r => r.json())
    .then(data => {
        data.forEach(item => {
            let opt = document.createElement('option');
            opt.value = item.name; // Simpan Nama
            opt.dataset.id = item.id; // Simpan ID untuk fetch berikutnya
            opt.innerHTML = item.name;
            provSelect.appendChild(opt);
        });
    });

    // 2. Load Kota saat Provinsi Berubah
    provSelect.addEventListener('change', function() {
        const provId = this.options[this.selectedIndex].dataset.id;
        kabSelect.innerHTML = '<option value="">Memuat...</option>';
        kabSelect.disabled = false;

        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provId}.json`)
        .then(r => r.json())
        .then(data => {
            kabSelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
            data.forEach(item => {
                let opt = document.createElement('option');
                opt.value = item.name; // <--- KUNCI: Value adalah NAMA, bukan ID
                opt.dataset.id = item.id;
                opt.innerHTML = item.name;
                kabSelect.appendChild(opt);
            });
        });
    });

    // 3. Event saat Kabupaten/Kota Dipilih
    $kabSelect.on('change', function() {
        const regencyId = $(this).find(':selected').attr('data-id');

        // Reset dropdown kecamatan & kelurahan
        $kecSelect.html('<option value="">Pilih Kabupaten Terlebih Dahulu</option>').prop('disabled', true).trigger('change.select2');
        $kelSelect.html('<option value="">Pilih Kecamatan Terlebih Dahulu</option>').prop('disabled', true).trigger('change.select2');

        if (regencyId) {
            $kecSelect.html('<option value="">Memuat Kecamatan...</option>').trigger('change.select2');

            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${regencyId}.json`)
                .then(response => response.json())
                .then(districts => {
                    let options = '<option value="">-- Pilih / Cari Kecamatan --</option>';
                    districts.forEach(item => {
                        options += `<option data-id="${item.id}" value="${item.name}">${item.name}</option>`;
                    });
                    $kecSelect.html(options).prop('disabled', false).trigger('change.select2');
                });
        }
    });

    // 4. Event saat Kecamatan Dipilih
    $kecSelect.on('change', function() {
        const districtId = $(this).find(':selected').attr('data-id');

        // Reset dropdown kelurahan
        $kelSelect.html('<option value="">Pilih Kecamatan Terlebih Dahulu</option>').prop('disabled', true).trigger('change.select2');

        if (districtId) {
            $kelSelect.html('<option value="">Memuat Kelurahan/Desa...</option>').trigger('change.select2');

            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`)
                .then(response => response.json())
                .then(villages => {
                    let options = '<option value="">-- Pilih / Cari Kelurahan/Desa --</option>';
                    villages.forEach(item => {
                        options += `<option value="${item.name}">${item.name}</option>`;
                    });
                    $kelSelect.html(options).prop('disabled', false).trigger('change.select2');
                });
        }
    });
});
</script>
@endpush
