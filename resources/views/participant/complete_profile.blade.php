@extends('layouts.auth')

@section('content')
<div class="container-xxl py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="text-white mb-0">LENGKAPI PROFIL PESERTA</h4>
                    <p class="mb-0 opacity-75">Silakan lengkapi data identitas Anda di sistem INTEGRAL</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('participant.profile.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">NIP / NIK (Wajib)</label>
                                <input type="text" name="nip_nik" class="form-control border-primary" placeholder="Masukkan NIP untuk sinkronisasi data" required>
                                <div class="form-text">Pastikan sesuai dengan NIP yang didaftarkan oleh Admin.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nomor WhatsApp</label>
                                <input type="number" name="whatsapp" class="form-control" placeholder="62812..." required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="Laki-Laki">Laki-Laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Status Kepegawaian</label>
                                <select name="status_kepegawaian" class="form-select" required>
                                    <option value="PNS">PNS</option>
                                    <option value="PPPK">PPPK</option>
                                    <option value="Non-ASN">Non-ASN</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="text-primary mb-3"><i class="bx bx-map me-1"></i>Wilayah Domisili / Instansi</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provinsi</label>
                                <select id="provinsi" name="provinsi" class="form-select border-primary" required>
                                    <option value="">Memuat Provinsi...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kabupaten / Kota</label>
                                <select id="kabupaten" name="kabupaten_kota" class="form-select border-primary" required disabled>
                                    <option value="">Pilih Provinsi Terlebih Dahulu</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-4 shadow">SIMPAN PROFIL & MASUK KE DASHBOARD</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT DROPDOWN WILAYAH TREE MODEL --}}
<script>
    const provSelect = document.getElementById('provinsi');
    const kabSelect = document.getElementById('kabupaten');

    // 1. Load Semua Provinsi se-Indonesia
    fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json`)
    .then(response => response.json())
    .then(provinces => {
        let options = '<option value="">-- Pilih Provinsi --</option>';
        provinces.forEach(item => {
            options += `<option data-id="${item.id}" value="${item.name}">${item.name}</option>`;
        });
        provSelect.innerHTML = options;
    });

    // 2. Load Kabupaten berdasarkan ID Provinsi
    provSelect.addEventListener('change', function() {
        const provinceId = this.options[this.selectedIndex].getAttribute('data-id');
        
        if (provinceId) {
            kabSelect.disabled = false;
            kabSelect.innerHTML = '<option value="">Memuat Kabupaten/Kota...</option>';
            
            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`)
            .then(response => response.json())
            .then(regencies => {
                let options = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                regencies.forEach(item => {
                    options += `<option value="${item.name}">${item.name}</option>`;
                });
                kabSelect.innerHTML = options;
            });
        } else {
            kabSelect.disabled = true;
            kabSelect.innerHTML = '<option value="">Pilih Provinsi Terlebih Dahulu</option>';
        }
    });
</script>
@endsection