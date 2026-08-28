@extends('layouts.master')

@section('title', 'Pengaturan Profil')

@push('css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        border-color: #d9dee3;
        border-radius: 0.375rem;
        min-height: 38px;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Akun /</span> Pengaturan Profil</h4>
        
        {{-- TOMBOL BUKA MODAL JIKA ROLE ADALAH PENGAJAR --}}
        @if($user->role === 'pengajar')
            <button type="button" class="btn btn-info shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditPengajar">
                <i class="bx bx-edit me-1"></i> Edit Data Pengajar & Berkas
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible" role="alert">
            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ALERT JIKA ADA ERROR DARI VALIDASI MODAL --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible mb-4" role="alert">
            <h6 class="alert-heading fw-bold mb-1"><i class="bx bx-error-circle me-1"></i> Terjadi kesalahan:</h6>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            
            {{-- KHUSUS PENGAJAR: CARD RINGKASAN DATA PENGAJAR & DOKUMEN --}}
            @if($user->role === 'pengajar')
            <div class="card mb-4 border-start border-info border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title text-info mb-0">
                            <i class="bx bx-chalkboard me-1"></i> Data Tenaga Pengajar & Keuangan
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalEditPengajar">
                            <i class="bx bx-edit-alt me-1"></i> Perbarui Data Ini
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <small class="text-muted d-block">Pangkat / Golongan:</small>
                            <span class="fw-bold text-dark">{{ $user->pengajar->pangkat_golongan ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <small class="text-muted d-block">Bank & No. Rekening:</small>
                            <span class="fw-bold text-dark">{{ $user->pengajar->nama_bank ?? '-' }} ({{ $user->pengajar->nomor_rekening ?? '-' }})</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <small class="text-muted d-block">Rekening A/N:</small>
                            <span class="fw-bold text-dark">{{ $user->pengajar->nama_rekening ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <small class="text-muted d-block">Status Kelengkapan Berkas:</small>
                            @php
                                $hasCv = $user->pengajar && $user->pengajar->cv_path;
                                $hasSertifikat = $user->pengajar && $user->pengajar->sertifikat_path;
                                $hasSurat = $user->pengajar && $user->pengajar->surat_tugas_path;
                            @endphp
                            <div class="d-flex gap-1 mt-1">
                                <span class="badge {{ $hasCv ? 'bg-label-success' : 'bg-label-secondary' }}" title="CV">CV</span>
                                <span class="badge {{ $hasSertifikat ? 'bg-label-success' : 'bg-label-secondary' }}" title="Sertifikat">Sertifikat</span>
                                <span class="badge {{ $hasSurat ? 'bg-label-success' : 'bg-label-secondary' }}" title="Surat Tugas">Surat Tugas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- CARD DETAIL PROFIL UMUM --}}
            <div class="card mb-4">
                <h5 class="card-header">Detail Akun</h5>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="user-avatar" class="d-block rounded shadow" height="100" width="100" id="uploadedAvatar" style="object-fit: cover;" />
                            @else
                                <div class="avatar avatar-xl me-2">
                                    <span class="avatar-initial rounded bg-label-primary" style="font-size: 40px;">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif

                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block text-uppercase small fw-bold">Ganti Foto</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="upload" name="profile_photo" class="account-file-input" hidden accept="image/png, image/jpeg" />
                                </label>
                                <p class="text-muted mb-0 small">Hanya JPG atau PNG. Maksimal 2MB.</p>
                            </div>
                        </div>
                    </div>
                    <hr class="my-0" />
                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input class="form-control" type="text" name="name" value="{{ $user->name }}" required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold">WhatsApp</label>
                                <input class="form-control" type="text" name="whatsapp" value="{{ $user->whatsapp }}" required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold text-muted">Username (Login)</label>
                                <input class="form-control bg-light" type="text" value="{{ $user->username }}" disabled />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label fw-bold text-muted">Role</label>
                                <input class="form-control bg-light" type="text" value="{{ strtoupper(str_replace('_', ' ', $user->role)) }}" disabled />
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan Akun</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- CARD GANTI PASSWORD --}}
            <div class="card">
                <h5 class="card-header border-bottom mb-3">Keamanan Akun</h5>
                <div class="card-body">
                    <div class="alert alert-warning mb-4">
                        <i class="bx bx-info-circle me-1"></i>
                        Jika Anda ingin mengganti password, silakan masukkan password baru di bawah ini.
                    </div>
                    
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf @method('PUT')
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
                        <div class="mt-2">
                            <button type="submit" class="btn btn-warning fw-bold shadow-sm">
                                <i class="bx bx-lock-open-alt me-1"></i> PERBARUI PASSWORD
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL KHUSUS EDIT PENGAJAR (PROFIL, REKENING, & DOKUMEN)                 --}}
{{-- ========================================================================= --}}
@if($user->role === 'pengajar')
<div class="modal fade" id="modalEditPengajar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf @method('PUT')
            
            {{-- Input hidden agar data name & whatsapp tetap terkirim --}}
            <input type="hidden" name="name" value="{{ $user->name }}">
            <input type="hidden" name="whatsapp" value="{{ $user->whatsapp }}">

            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="bx bx-user-check me-1"></i> Edit Data Pengajar, Rekening & Berkas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                
                {{-- 1. DATA KEPEGAWAIAN --}}
                <h6 class="fw-bold text-info border-bottom pb-2 mb-3">
                    <i class="bx bx-id-card me-1"></i> 1. Profil & Kepegawaian
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">NIP / NIK <span class="text-danger">*</span></label>
                        <input type="text" name="nip_nik" class="form-control" value="{{ old('nip_nik', $user->nip_nik) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jabatan Fungsional <span class="text-danger">*</span></label>
                        <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $user->jabatan) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Pangkat / Golongan <span class="text-danger">*</span></label>
                        <input type="text" name="pangkat_golongan" class="form-control" value="{{ old('pangkat_golongan', $user->pengajar->pangkat_golongan ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Instansi / Unit Kerja <span class="text-danger">*</span></label>
                        <input type="text" name="instansi" class="form-control" value="{{ old('instansi', $user->pengajar->instansi ?? $user->instansi) }}" required>
                    </div>
                </div>

                {{-- 2. INFORMASI REKENING --}}
                <h6 class="fw-bold text-info border-bottom pb-2 mb-3">
                    <i class="bx bx-wallet me-1"></i> 2. Informasi Rekening & Honor
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nomor NPWP</label>
                        <input type="text" name="npwp" class="form-control" value="{{ old('npwp', $user->pengajar->npwp ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="number" name="nomor_rekening" class="form-control" value="{{ old('nomor_rekening', $user->pengajar->nomor_rekening ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bank Asal Rekening <span class="text-danger">*</span></label>
                        <select name="nama_bank" id="select_bank_modal" class="form-select select2-bank" required>
                            <option value="{{ $user->pengajar->nama_bank ?? '' }}" selected>
                                {{ $user->pengajar->nama_bank ?? '-- Pilih Bank --' }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Rekening Atas Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama_rekening" class="form-control" value="{{ old('nama_rekening', $user->pengajar->nama_rekening ?? '') }}" required>
                    </div>
                </div>

                {{-- 3. KELENGKAPAN DOKUMEN (PDF MAX 5MB) --}}
                <h6 class="fw-bold text-info border-bottom pb-2 mb-3">
                    <i class="bx bx-file me-1"></i> 3. Berkas & Dokumen Pengajar (PDF Max 5MB)
                </h6>
                <div class="row g-3">
                    <!-- File CV -->
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Curriculum Vitae (CV)</label>
                            @if($user->pengajar && $user->pengajar->cv_path)
                                <a href="{{ asset('storage/' . $user->pengajar->cv_path) }}" target="_blank" class="badge bg-label-primary text-decoration-none">
                                    <i class="bx bx-show me-1"></i> Lihat CV Saat Ini
                                </a>
                            @endif
                        </div>
                        <input type="file" name="file_cv" class="form-control" accept=".pdf">
                        <div class="form-text small">Kosongkan jika tidak ingin mengubah CV.</div>
                    </div>

                    <!-- File Sertifikat -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Sertifikat ToT / Kompetensi</label>
                            @if($user->pengajar && $user->pengajar->sertifikat_path)
                                <a href="{{ asset('storage/' . $user->pengajar->sertifikat_path) }}" target="_blank" class="badge bg-label-primary text-decoration-none">
                                    <i class="bx bx-show me-1"></i> Lihat File
                                </a>
                            @endif
                        </div>
                        <input type="file" name="file_sertifikat" class="form-control" accept=".pdf">
                        <div class="form-text small">Format PDF maksimal 5MB.</div>
                    </div>

                    <!-- File Surat Tugas -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Surat Tugas Pengajar</label>
                            @if($user->pengajar && $user->pengajar->surat_tugas_path)
                                <a href="{{ asset('storage/' . $user->pengajar->surat_tugas_path) }}" target="_blank" class="badge bg-label-primary text-decoration-none">
                                    <i class="bx bx-show me-1"></i> Lihat File
                                </a>
                            @endif
                        </div>
                        <input type="file" name="file_surat_tugas" class="form-control" accept=".pdf">
                        <div class="form-text small">Format PDF maksimal 5MB.</div>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info text-white shadow-sm">
                    <i class="bx bx-save me-1"></i> Simpan Perubahan Data Pengajar
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('js')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    const $bankSelect = $('#select_bank_modal');
    const currentBank = "{{ $user->pengajar->nama_bank ?? '' }}";
    const apiBankUrl = 'https://raw.githubusercontent.com/riod94/list-bank-indonesia/master/bank.json';

    const fallbackBanks = [
        "BANK BJB", "BANK BJB SYARIAH", "BANK BRI", "BANK MANDIRI", "BANK BNI", "BANK BCA", 
        "BANK SYARIAH INDONESIA (BSI)", "BANK CIMB NIAGA", "BANK PERMATA", "BANK DANAMON", 
        "BANK TABUNGAN NEGARA (BTN)", "BANK PANIN", "BANK MEGA", "BANK DKI", "BANK JATENG", "BANK JATIM"
    ];

    // Inisialisasi Select2 di dalam Modal
    function initBankSelect2() {
        $bankSelect.select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalEditPengajar'), // Wajib agar dropdown Select2 bekerja di dalam modal Bootstrap
            placeholder: '-- Pilih / Cari Bank --',
            allowClear: true,
            width: '100%'
        });
    }

    function renderOptions(banks) {
        let options = '<option value="">-- Pilih Bank Asal Rekening --</option>';
        banks.forEach(bank => {
            let bankName = typeof bank === 'object' ? (bank.namaBank || bank.name || bank.label) : bank;
            if (bankName) {
                bankName = bankName.toUpperCase();
                const isSelected = (currentBank && currentBank.toUpperCase() === bankName) ? 'selected' : '';
                options += `<option value="${bankName}" ${isSelected}>${bankName}</option>`;
            }
        });
        $bankSelect.html(options);
        initBankSelect2();
    }

    // Ambil data Bank
    fetch(apiBankUrl)
        .then(response => response.json())
        .then(data => {
            const list = Array.isArray(data) ? data : (data.data || data.banks || []);
            renderOptions(list);
        })
        .catch(err => {
            console.warn('API luar gagal, menggunakan fallback:', err);
            renderOptions(fallbackBanks);
        });
});
</script>
@endpush