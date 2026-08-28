@extends('layouts.form')

{{-- Konfigurasi Header & Form --}}
@section('form_title', 'Setup Profil Pengajar')
@section('module_name', 'Tenaga Pengajar')
@section('page_title', 'Lengkapi Profil & Keamanan Akun')
@section('page_description', 'Silakan perbarui password default dan lengkapi data profil, rekening honor, serta dokumen pengajar Anda.')
@section('form_action', route('pengajar.setup.store'))
@section('form_enctype', 'multipart/form-data') {{-- Wajib untuk upload file --}}
@section('submit_text', 'Simpan Profil & Masuk Dashboard')

{{-- ISI KONTEN INPUT FORM --}}
@section('form_content')

    <!-- KARTU 1: KEAMANAN AKUN -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-lock-alt fs-4 me-2"></i> 1. Pembaruan Password
            </div>
            <div class="alert alert-warning py-2 small mb-4">
                <i class="bx bx-info-circle me-1"></i> Anda login menggunakan password default dari Superadmin. Wajib ubah password demi keamanan akun Anda.
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password Baru <span class="required-star">*</span></label>
                    <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ulangi Password Baru <span class="required-star">*</span></label>
                    <input type="password" name="new_password_confirmation" class="form-control" placeholder="Ulangi password baru" required minlength="6">
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU 2: PROFIL UTAMA -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-user fs-4 me-2"></i> 2. Profil Utama Kepegawaian
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Nama Lengkap (Beserta Gelar) <span class="required-star">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIP / NIK <span class="required-star">*</span></label>
                    <input type="text" name="nip_nik" class="form-control" value="{{ old('nip_nik', auth()->user()->nip_nik) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jabatan Fungsional <span class="required-star">*</span></label>
                    <select name="jabatan" class="form-select" required>
                        <option value="">-- Pilih Jabatan Fungsional --</option>
                        <option value="Widyaiswara Ahli Pertama" {{ old('jabatan', auth()->user()->jabatan) == 'Widyaiswara Ahli Pertama' ? 'selected' : '' }}>
                            Widyaiswara Ahli Pertama
                        </option>
                        <option value="Widyaiswara Ahli Muda" {{ old('jabatan', auth()->user()->jabatan) == 'Widyaiswara Ahli Muda' ? 'selected' : '' }}>
                            Widyaiswara Ahli Muda
                        </option>
                        <option value="Widyaiswara Ahli Madya" {{ old('jabatan', auth()->user()->jabatan) == 'Widyaiswara Ahli Madya' ? 'selected' : '' }}>
                            Widyaiswara Ahli Madya
                        </option>
                        <option value="Widyaiswara Ahli Utama" {{ old('jabatan', auth()->user()->jabatan) == 'Widyaiswara Ahli Utama' ? 'selected' : '' }}>
                            Widyaiswara Ahli Utama
                        </option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pangkat / Golongan <span class="required-star">*</span></label>
                    <input type="text" name="pangkat_golongan" class="form-control" placeholder="Contoh: Pembina Tingkat I (IV/b)" value="{{ old('pangkat_golongan') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Instansi / Unit Kerja <span class="required-star">*</span></label>
                    <input type="text" name="instansi" class="form-control" placeholder="Contoh: BPSDM Provinsi Jawa Barat" value="{{ old('instansi', auth()->user()->instansi) }}" required>
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU 3: PROFILE KEUANGAN -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-wallet fs-4 me-2"></i> 3. Profile Keuangan <small class="text-muted text-lowercase fw-normal">(Untuk keperluan pembayaran honor)</small>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor NPWP</label>
                    <input type="text" name="npwp" class="form-control" placeholder="Masukkan nomor NPWP" value="{{ old('npwp') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Rekening <span class="required-star">*</span></label>
                    <input type="number" name="nomor_rekening" class="form-control" placeholder="Contoh: 1234567890" value="{{ old('nomor_rekening') }}" required>
                </div>

                <!-- DROPDOWN BANK (SEARCHABLE DENGAN API) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank Asal Rekening <span class="required-star">*</span></label>
                    <select name="nama_bank" id="nama_bank" class="form-select select2" required>
                        <option value="">-- Sedang Memuat Daftar Bank... --</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Rekening Atas Nama <span class="required-star">*</span></label>
                    <input type="text" name="nama_rekening" class="form-control" placeholder="Sesuai buku tabungan" value="{{ old('nama_rekening') }}" required>
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU 4: DOKUMEN ADMINISTRASI PENGAJAR -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="form-section-title">
                <i class="bx bx-file fs-4 me-2"></i> 4. Dokumen & Berkas Pengajar
            </div>
            <div class="alert alert-info py-2 small mb-4">
                <i class="bx bx-info-circle me-1"></i> Semua berkas wajib dalam format <strong>.PDF</strong> dengan ukuran maksimal <strong>5 MB</strong> per file.
            </div>

            <div class="row">
                <!-- 1. CV Pengajar -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">Curriculum Vitae (CV) Pengajar</label>
                    <input type="file" name="file_cv" class="form-control" accept=".pdf">
                    <div class="form-text small">Unggah CV terbaru Anda dalam format PDF.</div>
                </div>

                <!-- 2. Sertifikat ToT / Keahlian -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sertifikat ToT / Sertifikat Kompetensi</label>
                    <input type="file" name="file_sertifikat" class="form-control" accept=".pdf">
                    <div class="form-text small">Sertifikat Training of Trainer atau bukti keahlian mengajar.</div>
                </div>

                <!-- 3. Surat Tugas Pengajar -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Surat Tugas Pengajar</label>
                    <input type="file" name="file_surat_tugas" class="form-control" accept=".pdf">
                    <div class="form-text small">Surat tugas dari instansi pengirim / penyelenggara.</div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('form_js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bankSelect = document.getElementById('nama_bank');
    const oldBankValue = "{{ old('nama_bank') }}";
    const apiBankUrl = 'https://raw.githubusercontent.com/riod94/list-bank-indonesia/master/bank.json';

    const fallbackBanks = [
        "BANK BJB", "BANK BJB SYARIAH", "BANK BRI", "BANK MANDIRI", "BANK BNI", "BANK BCA", 
        "BANK SYARIAH INDONESIA (BSI)", "BANK CIMB NIAGA", "BANK PERMATA", "BANK DANAMON", 
        "BANK TABUNGAN NEGARA (BTN)", "BANK PANIN", "BANK MEGA", "BANK DKI", "BANK JATENG", "BANK JATIM"
    ];

    function initSelect2() {
        $('#nama_bank').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Ketik untuk mencari Bank --',
            allowClear: true,
            width: '100%'
        });
    }

    function renderBankOptions(banks) {
        let options = '<option value="">-- Pilih Bank Asal Rekening --</option>';
        banks.forEach(bank => {
            let bankName = typeof bank === 'object' ? (bank.namaBank || bank.name || bank.label) : bank;
            if (bankName) {
                bankName = bankName.toUpperCase();
                const isSelected = (oldBankValue && oldBankValue.toUpperCase() === bankName) ? 'selected' : '';
                options += `<option value="${bankName}" ${isSelected}>${bankName}</option>`;
            }
        });
        bankSelect.innerHTML = options;
        initSelect2();
    }

    fetch(apiBankUrl)
        .then(response => response.json())
        .then(data => {
            const list = Array.isArray(data) ? data : (data.data || data.banks || []);
            renderBankOptions(list);
        })
        .catch(error => {
            console.warn('API luar gagal, menggunakan fallback:', error);
            renderBankOptions(fallbackBanks);
        });
});
</script>
@endpush