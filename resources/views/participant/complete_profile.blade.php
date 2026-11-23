@extends('layouts.profile-onboarding')

{{-- Konfigurasi Header & Metadata Form --}}
@section('form_title', 'Lengkapi Profil Pengguna')
@section('module_name', 'Registrasi Pengguna')
@section('page_title', 'Lengkapi Profil Pengguna')
@section('page_description', 'Lengkapi data identitas, kepegawaian, dan alamat tempat tinggal Anda.')
@section('form_action', route('participant.profile.store'))
@section('submit_text', 'Simpan Profil & Lanjutkan')

{{-- KONTEN INPUTAN FORM --}}
@section('form_content')

    <section class="profile-step" data-step="0" aria-label="Identitas pribadi">
    @if($user->must_complete_profile || $user->must_change_password)
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="d-flex gap-3"><i class="bx bx-shield-quarter fs-3"></i><div><strong>Akun hasil import peserta</strong><div class="small mt-1">Username login Anda adalah NIP/NIK <strong>{{ $user->nip_nik }}</strong>. Lengkapi seluruh profil dan buat password baru sebelum mengakses pelatihan.</div></div></div>
    </div>
    @endif

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
                <i class="bx bx-user-pin fs-4 me-2"></i> Identitas pribadi
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="profileNip" class="form-label">NIP <span class="required-star">*</span></label>
                    <input id="profileNip" type="text" name="nip" class="form-control" inputmode="numeric" pattern="[0-9]{18}" minlength="18" maxlength="18" value="{{ old('nip', $user->nip ?? (preg_match('/^[0-9]{18}$/', $user->nip_nik ?? '') ? $user->nip_nik : '')) }}" placeholder="Masukkan 18 digit NIP" required>
                    <div class="form-text">Nomor Induk Pegawai, wajib 18 digit.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="profileNik" class="form-label">NIK <span class="required-star">*</span></label>
                    <input id="profileNik" type="text" name="nik" class="form-control" inputmode="numeric" pattern="[0-9]{16}" minlength="16" maxlength="16" value="{{ old('nik', $user->nik ?? (preg_match('/^[0-9]{16}$/', $user->nip_nik ?? '') ? $user->nip_nik : '')) }}" placeholder="Masukkan 16 digit NIK" required>
                    <div class="form-text">Nomor Induk Kependudukan sesuai KTP, wajib 16 digit.</div>
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

        </div>
    </div>
    </section>
    <section class="profile-step" data-step="1" aria-label="Kepegawaian" hidden>
    <div class="card shadow-sm border-0 mb-4"><div class="card-body p-4">
    <div class="form-section-title"><i class="bx bx-briefcase me-2"></i>Kepegawaian & instansi</div>
            <div class="row">
                <!-- Jabatan -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jabatan Saat Ini <span class="required-star">*</span></label>
                    <input type="text" 
                           name="jabatan" 
                           class="form-control" 
                           placeholder="Contoh: Analis SDM Aparatur" 
                           value="{{ old('jabatan', $user->jabatan) }}" 
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
                @include('profile.partials.institution-fields')
            </div>

        </div>
    </div>

    </section>
    <section class="profile-step" data-step="2" aria-label="Wilayah tempat tinggal" hidden>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-map-pin fs-4 me-2"></i> Wilayah tempat tinggal saat ini
            </div>
            <p class="alert alert-info">Pilih provinsi, kabupaten/kota, kecamatan, dan kelurahan/desa sesuai tempat Anda tinggal sehari-hari. Data instansi atau unit kerja diisi pada Langkah 2.</p>

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

    </section>
    <section class="profile-step" data-step="3" aria-label="Alamat dan lokasi" hidden>
    <!-- KARTU 3: TITIK LOKASI -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-map fs-4 me-2"></i> Alamat & titik lokasi tempat tinggal
            </div>
            <div class="mb-3">
                <label for="addressSearch" class="form-label fw-bold">Alamat Lengkap Tempat Tinggal <span class="required-star">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-search-alt"></i></span>
                    <input type="text" name="address" id="addressSearch" class="form-control" value="{{old('address',$user->address)}}" placeholder="Cari nama jalan atau desa, contoh: Jalan Dago / Desa Ciburial" autocomplete="street-address" required>
                    <button type="button" id="searchAddressButton" class="btn btn-primary"><i class="bx bx-search me-1"></i>Cari Alamat</button>
                </div>
                <div class="form-text">Cari nama jalan, desa, atau alamat lengkap, lalu pilih hasilnya. Titik pada peta akan terisi otomatis dan dapat disesuaikan.</div>
                <div id="addressSearchResults" class="list-group mt-2 shadow-sm d-none" style="max-height:260px;overflow-y:auto"></div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <label class="form-label mb-0">Pilih Titik Lokasi <span class="required-star">*</span></label>
                    <div class="form-text">Peta otomatis membuka wilayah yang dipilih di Langkah 3. Klik titik tempat tinggal langsung di peta jika alamat sulit ditemukan lewat pencarian.</div>
                </div>
                <button type="button" id="useCurrentLocation" class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-current-location me-1"></i>Gunakan Lokasi Saya
                </button>
            </div>
            <div id="profileLocationMap" class="rounded border" style="height: 360px;"></div>
            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', auth()->user()->latitude) }}" required>
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', auth()->user()->longitude) }}" required>
            <div id="coordinateStatus" class="small mt-2 text-muted" role="status">Peta akan membuka wilayah tempat tinggal Anda.</div>
        </div>
    </div>

    </section>
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
    const provSelect = $provSelect.get(0);
    const kabSelect = $kabSelect.get(0);
    const savedProvince = @json(old('provinsi', auth()->user()->provinsi));
    const savedRegency = @json(old('kota', auth()->user()->kota));
    const savedDistrict = @json(old('kecamatan', auth()->user()->kecamatan));
    const savedVillage = @json(old('kelurahan', auth()->user()->kelurahan));
    const wilayahBaseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/api';
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const coordinateStatus = document.getElementById('coordinateStatus');
    const initialLat = parseFloat(latitudeInput.value);
    const initialLng = parseFloat(longitudeInput.value);
    const hasInitialPoint = Number.isFinite(initialLat) && Number.isFinite(initialLng);
    const locationMap = L.map('profileLocationMap').setView(hasInitialPoint ? [initialLat, initialLng] : [-2.5, 118], hasInitialPoint ? 15 : 5);
    const mapStep = locationMap.getContainer().closest('.profile-step');
    let locationMarker = null;
    let locationSearchController = null;
    let focusedArea = null;
    const savedArea = JSON.stringify([savedVillage, savedDistrict, savedRegency, savedProvince]);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(locationMap);

    function setLocationPoint(lat, lng, zoom = true) {
        locationSearchController?.abort();
        if (!mapStep.hidden) focusedArea = JSON.stringify(selectedArea());
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

@include('profile.partials.address-search-script')

    function selectedArea() {
        return [$kelSelect.val(), $kecSelect.val(), $kabSelect.val(), $provSelect.val()];
    }

    function normalizeArea(name) {
        return name.toLowerCase().trim().replace(/^(kelurahan|desa|kecamatan|kabupaten|kota|provinsi)\s+/, '');
    }

    async function focusSelectedVillage() {
        const area = selectedArea();
        if (mapStep.hidden || !area.every(Boolean)) return;

        const areaKey = JSON.stringify(area);
        if (areaKey === focusedArea) return;
        if (hasInitialPoint && locationMarker && areaKey === savedArea && focusedArea === null) {
            focusedArea = areaKey;
            return;
        }

        locationSearchController?.abort();
        const controller = new AbortController();
        locationSearchController = controller;
        coordinateStatus.className = 'small mt-2 text-primary';
        coordinateStatus.textContent = `Membuka wilayah ${area[0]} pada peta…`;

        try {
            const searches = area.map((_, index) => ({ index, parts: area.slice(index) }));
            searches.splice(1, 0, { index: 0, parts: [area[0], area[2], area[3]] });
            for (const { index, parts } of searches) {
                const name = area[index];
                const query = [...parts, 'Indonesia'].join(', ');
                const params = new URLSearchParams({ q: query.slice(0, 200) });
                const response = await fetch(`${addressSearchUrl}?${params}`, {
                    signal: controller.signal,
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) break;
                const { data } = await response.json();
                if (controller.signal.aborted || mapStep.hidden || areaKey !== JSON.stringify(selectedArea())) return;

                const result = data.find(item => normalizeArea(item.name) === normalizeArea(name));
                if (!result) continue;

                const bounds = Array.isArray(result.boundingbox) ? result.boundingbox.map(Number) : [];
                if (bounds.length === 4 && bounds.every(Number.isFinite) && bounds[0] < bounds[1] && bounds[2] < bounds[3]) {
                    locationMap.fitBounds([[bounds[0], bounds[2]], [bounds[1], bounds[3]]], {
                        maxZoom: 16,
                        padding: [24, 24],
                    });
                } else {
                    locationMap.setView([result.lat, result.lon], [15, 13, 11, 8][index]);
                }
                focusedArea = areaKey;
                coordinateStatus.className = 'small mt-2 text-info';
                coordinateStatus.textContent = index === 0
                    ? `Wilayah ${name} ditampilkan. Klik titik tempat tinggal Anda pada peta.`
                    : `Wilayah ${name} ditampilkan. Geser atau perbesar peta untuk memilih titik tempat tinggal Anda.`;
                return;
            }
        } catch (error) {
            if (controller.signal.aborted) return;
        }
        if (controller.signal.aborted || mapStep.hidden || areaKey !== JSON.stringify(selectedArea())) return;
        coordinateStatus.className = 'small mt-2 text-warning';
        coordinateStatus.textContent = 'Wilayah belum dapat dibuka otomatis. Geser peta atau gunakan tombol Gunakan Lokasi Saya saat berada di tempat tinggal.';
    }

    if (hasInitialPoint) {
        setLocationPoint(initialLat, initialLng, false);
        focusedArea = null;
    }

    function updateVisibleMap() {
        if (mapStep.hidden) {
            locationSearchController?.abort();
            return;
        }
        requestAnimationFrame(() => {
            if (mapStep.hidden) return;
            locationMap.invalidateSize();
            focusSelectedVillage();
        });
    }

    document.addEventListener('integral:profile-step', updateVisibleMap);
    updateVisibleMap();

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
    $provSelect.html('<option value="">Memuat data provinsi...</option>').prop('disabled', true).trigger('change.select2');
    fetch(`${wilayahBaseUrl}/provinces.json`)
    .then(r => {
        if (!r.ok) throw new Error('Gagal mengambil data provinsi.');
        return r.json();
    })
    .then(data => {
        provSelect.innerHTML = '<option value="">-- Pilih / Cari Provinsi --</option>';
        data.forEach(item => {
            let opt = document.createElement('option');
            opt.value = item.name;
            opt.dataset.id = item.id;
            opt.textContent = item.name;
            opt.selected = savedProvince === item.name;
            provSelect.appendChild(opt);
        });
        $provSelect.prop('disabled', false).trigger('change.select2');
        if (savedProvince) $provSelect.trigger('change');
    })
    .catch(error => {
        console.error(error);
        $provSelect.html('<option value="">Data provinsi gagal dimuat</option>').prop('disabled', true).trigger('change.select2');
        $('#wilayahLoadError').remove();
        $provSelect.closest('.card-body').prepend('<div id="wilayahLoadError" class="alert alert-danger py-2"><i class="bx bx-error-circle me-1"></i>Data wilayah gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.</div>');
    });

    // 2. Load Kota saat Provinsi Berubah
    $provSelect.on('change', function() {
        const provId = $(this).find(':selected').attr('data-id');
        kabSelect.innerHTML = '<option value="">Memuat...</option>';
        kabSelect.disabled = false;
        $kabSelect.trigger('change.select2');

        if (!provId) {
            $kabSelect.html('<option value="">Pilih Provinsi Dahulu</option>').prop('disabled', true).trigger('change.select2');
            $kecSelect.html('<option value="">Pilih Kabupaten Terlebih Dahulu</option>').prop('disabled', true).trigger('change.select2');
            $kelSelect.html('<option value="">Pilih Kecamatan Terlebih Dahulu</option>').prop('disabled', true).trigger('change.select2');
            return;
        }
        fetch(`${wilayahBaseUrl}/regencies/${provId}.json`)
        .then(r => {
            if (!r.ok) throw new Error('Gagal mengambil kabupaten/kota.');
            return r.json();
        })
        .then(data => {
            kabSelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
            data.forEach(item => {
                let opt = document.createElement('option');
                opt.value = item.name;
                opt.dataset.id = item.id;
                opt.textContent = item.name;
                opt.selected = savedRegency === item.name;
                kabSelect.appendChild(opt);
            });
            $kabSelect.prop('disabled', false).trigger('change.select2');
            if (savedRegency) $kabSelect.trigger('change');
        })
        .catch(error => {
            console.error(error);
            $kabSelect.html('<option value="">Kabupaten/kota gagal dimuat</option>').prop('disabled', true).trigger('change.select2');
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

            fetch(`${wilayahBaseUrl}/districts/${regencyId}.json`)
                .then(response => {
                    if (!response.ok) throw new Error('Gagal mengambil kecamatan.');
                    return response.json();
                })
                .then(districts => {
                    let options = '<option value="">-- Pilih / Cari Kecamatan --</option>';
                    districts.forEach(item => {
                        options += `<option data-id="${item.id}" value="${item.name}" ${savedDistrict === item.name ? 'selected' : ''}>${item.name}</option>`;
                    });
                    $kecSelect.html(options).prop('disabled', false).trigger('change.select2');
                    if (savedDistrict) $kecSelect.trigger('change');
                })
                .catch(error => {
                    console.error(error);
                    $kecSelect.html('<option value="">Kecamatan gagal dimuat</option>').prop('disabled', true).trigger('change.select2');
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

            fetch(`${wilayahBaseUrl}/villages/${districtId}.json`)
                .then(response => {
                    if (!response.ok) throw new Error('Gagal mengambil kelurahan/desa.');
                    return response.json();
                })
                .then(villages => {
                    let options = '<option value="">-- Pilih / Cari Kelurahan/Desa --</option>';
                    villages.forEach(item => {
                        options += `<option value="${item.name}" ${savedVillage === item.name ? 'selected' : ''}>${item.name}</option>`;
                    });
                    $kelSelect.html(options).prop('disabled', false).trigger('change.select2');
                    if (savedVillage) $kelSelect.trigger('change');
                })
                .catch(error => {
                    console.error(error);
                    $kelSelect.html('<option value="">Kelurahan/desa gagal dimuat</option>').prop('disabled', true).trigger('change.select2');
                });
        }
    });

    $kelSelect.on('change', updateVisibleMap);
});
</script>
@endpush
