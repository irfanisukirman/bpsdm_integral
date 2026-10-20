{{--
    Section 3 - Instansi (ASN).
    Parameter: $profile App\Models\User

    Daftar opsi diambil dari config('wilayah.perangkat_daerah') yang sama dengan
    dropdown Perangkat Daerah pada form Hotline, sehingga data instansi konsisten.
--}}
@php
    $perangkatDaerah = config('wilayah.perangkat_daerah', []);
    $currentInstansi = old('instansi', $profile->instansi);

    // Instansi lama yang tidak ada di daftar tetap dipertahankan sebagai opsi
    // terpilih supaya dataexisting tidak hilang.
    $instansiLainnya = ($currentInstansi && !in_array($currentInstansi, $perangkatDaerah, true))
        ? [$currentInstansi]
        : [];
@endphp
<div class="card shadow-sm border-0{{ ($hidden ?? false) ? ' d-none' : '' }} mb-4" data-section="instansi">
    <div class="card-body p-4">
        <div class="form-section-title">
            <i class="bx bx-buildings fs-4 me-2"></i> Instansi (ASN)
        </div>

        <div class="mb-3">
            <label class="form-label" for="registerInstansi">Instansi / Unit Kerja <span class="required-star">*</span></label>
            <select name="instansi"
                    id="registerInstansi"
                    class="form-select"
                    required>
                <option value="">-- Pilih Perangkat Daerah --</option>
                @foreach($instansiLainnya as $namaLain)
                    <option value="{{ $namaLain }}" selected>{{ $namaLain }} (di luar daftar)</option>
                @endforeach
                @foreach($perangkatDaerah as $nama)
                    <option value="{{ $nama }}" @selected($currentInstansi === $nama)>{{ $nama }}</option>
                @endforeach
            </select>
            <div class="form-text">Pilih nama perangkat daerah/unit kerja yang menjadi tempat Anda berdiket, terdaftar di wilayah Jawa Barat.</div>
        </div>
    </div>
</div>