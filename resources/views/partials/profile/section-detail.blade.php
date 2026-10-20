{{--
    Section 5 - Detail: wilayah domisili/kerja dan titik lokasi.
    Parameter:
      $profile          App\Models\User (boleh kosong)
      $addressSearchUrl endpoint pencarian alamat
--}}
<div class="card shadow-sm border-0{{ ($hidden ?? false) ? ' d-none' : '' }} mb-4" data-section="detail">
    <div class="card-body p-4">
        <div class="form-section-title">
            <i class="bx bx-map-pin fs-4 me-2"></i> Detail
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Provinsi <span class="required-star">*</span></label>
                <select id="provinsi" name="provinsi" class="form-select" required>
                    <option value="">-- Pilih Provinsi --</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Kabupaten / Kota <span class="required-star">*</span></label>
                {{-- Nama input harus 'kota' agar sesuai kolom users --}}
                <select id="kabupaten" name="kota" class="form-select" required disabled>
                    <option value="">Pilih Provinsi Dahulu</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Kecamatan <span class="required-star">*</span></label>
                <select id="kecamatan" name="kecamatan" class="form-select" required disabled></select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Kelurahan / Desa <span class="required-star">*</span></label>
                <select id="kelurahan" name="kelurahan" class="form-select" required disabled></select>
            </div>
        </div>

        <hr class="my-4">

        <div class="mb-3">
            <label class="form-label fw-bold">Alamat Lengkap <span class="required-star">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bx bx-search-alt"></i></span>
                <input type="text" name="address" id="addressSearch" class="form-control" value="{{ old('address', $profile->address) }}" placeholder="Contoh: Jl. Nihmat, Bandung" autocomplete="street-address" required>
                <button type="button" id="searchAddressButton" class="btn btn-primary"><i class="bx bx-search me-1"></i>Cari Alamat</button>
            </div>
            <div class="form-text">Tekan Cari Alamat, lalu pilih hasil yang sesuai. Peta dan koordinat akan diarahkan otomatis.</div>
            <div id="addressSearchResults" class="list-group mt-2 shadow-sm d-none" style="max-height:260px;overflow-y:auto"></div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <label class="form-label mb-0">Pilih Titik Lokasi <span class="required-star">*</span></label>
                <div class="form-text">Setelah memilih wilayah, peta akan menuju area kelurahan. Klik posisi tempat tinggal Anda pada peta.</div>
            </div>
            <button type="button" id="useCurrentLocation" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-current-location me-1"></i>Gunakan Lokasi Saya
            </button>
        </div>

        <div id="profileLocationMap" class="rounded border" style="height: 360px;"></div>
        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $profile->latitude) }}" required>
        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $profile->longitude) }}" required>
        <div id="coordinateStatus" class="small mt-2 text-muted">Pilih wilayah terlebih dahulu, kemudian tentukan titik pada peta.</div>
    </div>
</div>