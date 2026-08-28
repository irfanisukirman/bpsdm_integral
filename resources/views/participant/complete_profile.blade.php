@extends('layouts.auth')

@section('title', 'Lengkapi Profil Peserta')

@section('content')
    <div class="container-xxl py-5">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-8">
                <div class="card shadow-lg border-0">
                    <!-- Header Branding -->
                    <div class="card-header bg-primary text-white text-center py-4">
                        <div class="mb-2">
                            <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png"
                                alt="Integral Logo" style="width: 50px; filter: brightness(0) invert(1);">
                        </div>
                        <h4 class="text-white mb-1 fw-bold text-uppercase">Lengkapi Profil Peserta</h4>
                        <p class="mb-0 opacity-75 small">Silakan lengkapi data identitas Anda untuk integrasi sistem
                            INTEGRAL</p>
                    </div>

                    <div class="card-body p-4">
                        <!-- Alert Error Validasi -->
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm mb-4">
                                <div class="fw-bold mb-1"><i class="bx bx-error-circle me-1"></i> Gagal menyimpan:</div>
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('participant.profile.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Identitas Utama -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">NIP / NIK (Wajib)</label>
                                    <input type="text" name="nip_nik" class="form-control border-primary"
                                        placeholder="Contoh: 19950303..."
                                        value="{{ old('nip_nik', auth()->user()->nip_nik) }}" required>
                                    <div class="form-text small text-info">Gunakan NIP asli Anda untuk sinkronisasi riwayat
                                        pelatihan.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nomor WhatsApp</label>
                                    <input type="number" name="whatsapp" class="form-control border-primary"
                                        placeholder="62812345678" value="{{ old('whatsapp', auth()->user()->whatsapp) }}"
                                        required>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Gender & Status -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Gender</label>
                                    <select name="gender" class="form-select border-primary" required>
                                        <option value="">-- Pilih Gender --</option>
                                        <option value="Laki-Laki" {{ old('gender', auth()->user()->gender) == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                                        <option value="Perempuan" {{ old('gender', auth()->user()->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Status Kepegawaian</label>
                                    <select name="status_kepegawaian" class="form-select border-primary" required>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="PNS" {{ old('status_kepegawaian') == 'PNS' ? 'selected' : '' }}>PNS
                                        </option>
                                        <option value="PPPK" {{ old('status_kepegawaian') == 'PPPK' ? 'selected' : '' }}>PPPK
                                        </option>
                                        <option value="Non-ASN" {{ old('status_kepegawaian') == 'Non-ASN' ? 'selected' : '' }}>Non-ASN</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Jabatan & Instansi -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Jabatan Saat Ini</label>
                                    <input type="text" name="jabatan" class="form-control border-primary"
                                        placeholder="Contoh: Analis SDM Aparatur" value="{{ old('jabatan') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Instansi / Unit Kerja</label>
                                    <input type="text" name="instansi" class="form-control border-primary"
                                        placeholder="Contoh: BPSDM Provinsi Jawa Barat" value="{{ old('instansi') }}"
                                        required>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="text-primary mb-3 fw-bold"><i class="bx bx-map me-1"></i>Wilayah Domisili / Kerja
                            </h6>

                            <div class="row">
                                <!-- Dropdown Provinsi -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Provinsi <span class="text-danger">*</span></label>
                                    <select id="provinsi" name="provinsi" class="form-select border-primary" required>
                                        <option value="">Memuat Provinsi...</option>
                                    </select>
                                </div>

                                <!-- Dropdown Kota -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Kota <span
                                            class="text-danger">*</span></label>
                                    <select id="kota" name="kota" class="form-select border-primary" required disabled>
                                        <option value="">Pilih Provinsi Dahulu</option>
                                    </select>
                                </div>

                                <!-- Dropdown Kecamatan -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Kecamatan <span class="text-danger">*</span></label>
                                    <select id="kecamatan" name="kecamatan" class="form-select border-primary" required
                                        disabled>
                                        <option value="">Pilih Kota Dahulu</option>
                                    </select>
                                </div>

                                <!-- Dropdown Kelurahan -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Kelurahan / Desa <span
                                            class="text-danger">*</span></label>
                                    <select id="kelurahan" name="kelurahan" class="form-select border-primary" required
                                        disabled>
                                        <option value="">Pilih Kecamatan Dahulu</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100 shadow">
                                    <i class="bx bx-save me-1"></i> SIMPAN PROFIL & LANJUTKAN
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer bg-light text-center py-3 border-top">
                        <small class="text-muted">INTEGRAL © {{ date('Y') }} | BPSDM Provinsi Jawa Barat</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT DROPDOWN WILAYAH INDONESIA --}}
    <script>
        const apiProvinsi = "https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json";
        const apiKota = "https://www.emsifa.com/api-wilayah-indonesia/api/regencies/";
        const apiKecamatan = "https://www.emsifa.com/api-wilayah-indonesia/api/districts/";
        const apiKelurahan = "https://www.emsifa.com/api-wilayah-indonesia/api/villages/";

        const provSelect = document.getElementById('provinsi');
        const kotaSelect = document.getElementById('kota');
        const kecSelect = document.getElementById('kecamatan');
        const kelSelect = document.getElementById('kelurahan');

        // 1. Load Semua Provinsi
        fetch(apiProvinsi)
            .then(response => response.json())
            .then(provinces => {
                let options = '<option value="">-- Pilih Provinsi --</option>';
                provinces.forEach(item => {
                    options += `<option data-id="${item.id}" value="${item.name}">${item.name}</option>`;
                });
                provSelect.innerHTML = options;
            });

        // 2. Load Kota berdasarkan ID Provinsi
        provSelect.addEventListener('change', function () {
            const provinceId = this.options[this.selectedIndex].getAttribute('data-id');

            kecSelect.disabled = true;
            kecSelect.innerHTML = '<option value="">Pilih Kota Dahulu</option>';
            kelSelect.disabled = true;
            kelSelect.innerHTML = '<option value="">Pilih Kecamatan Dahulu</option>';

            if (provinceId) {
                kotaSelect.disabled = false;
                kotaSelect.innerHTML = '<option value="">Memuat Kota...</option>';

                fetch(`${apiKota}${provinceId}.json`)
                    .then(response => response.json())
                    .then(regencies => {
                        let options = '<option value="">-- Pilih Kota --</option>';
                        regencies.forEach(item => {
                            options += `<option data-id="${item.id}" value="${item.name}">${item.name}</option>`;
                        });
                        kotaSelect.innerHTML = options;
                    });
            } else {
                kotaSelect.disabled = true;
                kotaSelect.innerHTML = '<option value="">Pilih Provinsi Dahulu</option>';
            }
        });

        // 3. Load Kecamatan berdasarkan ID Kota
        kotaSelect.addEventListener('change', function () {
            const cityId = this.options[this.selectedIndex].getAttribute('data-id');

            kelSelect.disabled = true;
            kelSelect.innerHTML = '<option value="">Pilih Kecamatan Dahulu</option>';

            if (cityId) {
                kecSelect.disabled = false;
                kecSelect.innerHTML = '<option value="">Memuat Kecamatan...</option>';

                fetch(`${apiKecamatan}${cityId}.json`)
                    .then(response => response.json())
                    .then(districts => {
                        let options = '<option value="">-- Pilih Kecamatan --</option>';
                        districts.forEach(item => {
                            options += `<option data-id="${item.id}" value="${item.name}">${item.name}</option>`;
                        });
                        kecSelect.innerHTML = options;
                    });
            } else {
                kecSelect.disabled = true;
                kecSelect.innerHTML = '<option value="">Pilih Kota Dahulu</option>';
            }
        });

        // 4. Load Kelurahan berdasarkan ID Kecamatan
        kecSelect.addEventListener('change', function () {
            const districtId = this.options[this.selectedIndex].getAttribute('data-id');

            if (districtId) {
                kelSelect.disabled = false;
                kelSelect.innerHTML = '<option value="">Memuat Kelurahan...</option>';

                fetch(`${apiKelurahan}${districtId}.json`)
                    .then(response => response.json())
                    .then(villages => {
                        let options = '<option value="">-- Pilih Kelurahan --</option>';
                        villages.forEach(item => {
                            options += `<option data-id="${item.id}" value="${item.name}">${item.name}</option>`;
                        });
                        kelSelect.innerHTML = options;
                    });
            } else {
                kelSelect.disabled = true;
                kelSelect.innerHTML = '<option value="">Pilih Kecamatan Dahulu</option>';
            }
        });
    </script>

    <style>
        body {
            background: #f5f5f9;
        }

        .form-select-lg {
            font-size: 1rem;
        }
    </style>
@endsection