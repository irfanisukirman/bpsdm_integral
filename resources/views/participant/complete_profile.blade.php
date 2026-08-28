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
        </div>
    </div>

    <!-- KARTU 2: WILAYAH KERJA / DOMISILI (DENGAN PENCARIAN SELECT2) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-map-pin fs-4 me-2"></i> 2. Wilayah Domisili / Kerja
            </div>

            <div class="row">
                <!-- Provinsi -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Provinsi <span class="required-star">*</span></label>
                    <select id="provinsi" name="provinsi" class="form-select select2-wilayah" required>
                        <option value="">Memuat Provinsi...</option>
                    </select>
                </div>

                <!-- Kabupaten / Kota -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kabupaten / Kota <span class="required-star">*</span></label>
                    <select id="kabupaten" name="kabupaten_kota" class="form-select select2-wilayah" required disabled>
                        <option value="">Pilih Provinsi Terlebih Dahulu</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <!-- Kecamatan -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kecamatan <span class="required-star">*</span></label>
                    <select id="kecamatan" name="kecamatan" class="form-select select2-wilayah" required disabled>
                        <option value="">Pilih Kabupaten Terlebih Dahulu</option>
                    </select>
                </div>

                <!-- Kelurahan / Desa -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kelurahan / Desa <span class="required-star">*</span></label>
                    <select id="kelurahan" name="kelurahan" class="form-select select2-wilayah" required disabled>
                        <option value="">Pilih Kecamatan Terlebih Dahulu</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

@endsection

{{-- SCRIPT FETCH API + SELECT2 SEARCH WILAYAH INDONESIA --}}
@push('form_js')
<script>
$(document).ready(function() {
    const $provSelect = $('#provinsi');
    const $kabSelect = $('#kabupaten');
    const $kecSelect = $('#kecamatan');
    const $kelSelect = $('#kelurahan');

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
    fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json`)
        .then(response => response.json())
        .then(provinces => {
            let options = '<option value="">-- Pilih / Cari Provinsi --</option>';
            provinces.forEach(item => {
                options += `<option data-id="${item.id}" value="${item.name}">${item.name}</option>`;
            });
            $provSelect.html(options).trigger('change.select2');
        })
        .catch(err => {
            console.error('Gagal memuat provinsi:', err);
            $provSelect.html('<option value="">Gagal Memuat Provinsi</option>').trigger('change.select2');
        });

    // 2. Event saat Provinsi Dipilih
    $provSelect.on('change', function() {
        const provinceId = $(this).find(':selected').attr('data-id');
        
        // Reset dropdown anak-anaknya
        $kabSelect.html('<option value="">Pilih Provinsi Terlebih Dahulu</option>').prop('disabled', true).trigger('change.select2');
        $kecSelect.html('<option value="">Pilih Kabupaten Terlebih Dahulu</option>').prop('disabled', true).trigger('change.select2');
        $kelSelect.html('<option value="">Pilih Kecamatan Terlebih Dahulu</option>').prop('disabled', true).trigger('change.select2');

        if (provinceId) {
            $kabSelect.html('<option value="">Memuat Kabupaten/Kota...</option>').trigger('change.select2');
            
            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`)
                .then(response => response.json())
                .then(regencies => {
                    let options = '<option value="">-- Pilih / Cari Kabupaten/Kota --</option>';
                    regencies.forEach(item => {
                        options += `<option data-id="${item.id}" value="${item.name}">${item.name}</option>`;
                    });
                    $kabSelect.html(options).prop('disabled', false).trigger('change.select2');
                });
        }
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