@extends('layouts.master')

@section('title', 'Jadwal Pelatihan')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    #edit_asset_select_wrap { position: relative; }
    #edit_asset_select_wrap .select2-container { width: 100% !important; }
    #edit_asset_select_wrap .select2-dropdown { z-index: 1065; }
    .schedule-table { width: 100%; table-layout: fixed; }
    .schedule-table__time { width: 170px; }
    .schedule-table__actions { width: 82px; }
    .schedule-table td { white-space: normal; vertical-align: middle; }
    .schedule-table__content, .schedule-table__content * { overflow-wrap: anywhere; }
    .schedule-action-buttons { display: flex; justify-content: center; gap: .35rem; }
    .schedule-action-buttons .btn-icon { flex: 0 0 32px; width: 32px; height: 32px; padding: 0; }
    @media (max-width: 767.98px) {
        .schedule-table { min-width: 560px; }
        .schedule-table__time { width: 145px; }
        .schedule-table__actions { width: 74px; }
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h4 class="fw-bold py-1 mb-0">
        <span class="text-muted fw-light">Pelatihan /</span> Jadwal: {{ $training->nama_pelatihan }}
    </h4>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportSchedule">
            <i class="bx bx-file me-1"></i> Import Excel
        </button>
        <a href="{{ route('schedules.pdf', $training->id) }}" class="btn btn-danger btn-sm shadow-sm" target="_blank">
            <i class="bx bxs-file-pdf me-1"></i> Unduh PDF
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible" role="alert">
        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm alert-dismissible" role="alert">
        <i class="bx bx-error-circle me-1"></i> <strong>Gagal:</strong> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <!-- KOLOM KIRI: FORM TAMBAH MANUAL -->
    <div class="col-12 col-xl-5 mb-4">
        <div class="card shadow-sm border-0">
            <h5 class="card-header border-bottom">Tambah Sesi Jadwal</h5>
            <div class="card-body pt-3">
                <form action="{{ route('schedules.store', $training->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3"><label class="form-label fw-bold">Jenis Jadwal</label><select name="schedule_type" id="create_schedule_type" class="form-select"><option value="learning" @selected(old('schedule_type','learning')==='learning')>Sesi Pembelajaran</option><option value="break" @selected(old('schedule_type')==='break')>Istirahat / Jeda</option></select><small class="text-muted">Istirahat tidak dihitung sebagai JP/OJ dan tidak memerlukan pengajar atau aset.</small></div>
                    <div class="row g-3 mb-3 duration-grid">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="create_start" class="form-control" value="{{ old('start_time') }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-bold">Jam Selesai <span id="create_end_badge" class="badge bg-label-secondary fw-normal">Otomatis</span></label>
                            <input type="time" name="break_end_time" id="create_end" class="form-control bg-light" readonly tabindex="-1">
                        </div>
                        <div class="col-7 col-sm-6 create-learning-field">
                            <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jp" id="create_jp" class="form-control" value="{{ old('jp', 1) }}" min="1" max="24" required>
                        </div>
                        <div class="col-5 col-sm-6 create-learning-field">
                            <label class="form-label fw-bold">Satuan Durasi</label>
                            <select name="duration_unit" id="create_duration_unit" class="form-select"><option value="JP" @selected(old('duration_unit','JP')==='JP')>JP (45 menit)</option><option value="OJ" @selected(old('duration_unit')==='OJ')>OJ (60 menit)</option></select>
                        </div>
                        <div class="col-12 create-learning-field"><small id="create_duration_help" class="text-muted"><i class="bx bx-calculator me-1"></i>JP: 1 unit = 45 menit. OJ: 1 unit = 60 menit.</small></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Materi / Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="activity" class="form-control" value="{{ old('activity') }}" placeholder="Contoh: Pengantar Digitalisasi" required>
                    </div>
                    
                    <!-- INPUT PILIH PENGAJAR -->
                    <div class="mb-3 create-learning-section">
                        <label class="form-label fw-bold text-info"><i class="bx bx-chalkboard me-1"></i>Tenaga Pengajar / Fasilitator</label>
                        <select name="pengajar_id" class="form-select select2">
                            <option value="">-- Tanpa Pengajar / Sesi Mandiri --</option>
                            @foreach($pengajars as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->name }} {{ $p->nip_nik ? "({$p->nip_nik})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Hanya menampilkan akun Narasumber yang telah disetujui dan memiliki akses Pengajar.</div>
                    </div>

                    <div class="mb-3 create-learning-section">
                        <label class="form-label fw-bold">Tempat / Ruangan</label>
                        <select name="venue_type" id="create_venue_type" class="form-select mb-2">
                            <option value="internal">Internal BPSDM</option>
                            <option value="external">Eksternal</option>
                        </select>
                        <div id="create_internal">
                            <select name="asset_ids[]" class="form-select select2-assets" multiple>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->name }} — {{ $asset->location }} ({{ $asset->capacity ?: '-' }} orang)</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Bisa memilih satu atau beberapa ruangan/aset dalam satu pengajuan.</small>
                            @if($reusableLoanRequests->isNotEmpty())
                            <div class="alert alert-info py-2 px-3 mt-3 mb-2"><i class="bx bx-copy me-1"></i>Jika surat dan data pemohon sama dengan sesi sebelumnya pada pelatihan ini, pilih pengajuan sebelumnya agar tidak perlu mengunggah dan mengisi ulang.</div>
                            <div class="mt-2"><label class="form-label fw-bold">Data Peminjaman</label><select name="reuse_loan_request_id" id="reuse_loan_request_id" class="form-select"><option value="">Buat pengajuan dengan surat baru</option>
                                @foreach($reusableLoanRequests as $previousLoan)@php $previousSchedule=$previousLoan->requestable;$previousAssets=collect($previousLoan->asset_ids)->map(fn($assetId)=>$assets->firstWhere('id',$assetId)?->name)->filter()->join(', ');@endphp
                                <option value="{{$previousLoan->id}}" data-purpose="{{e($previousLoan->purpose)}}" data-contact="{{e($previousLoan->contact_person)}}" @selected(old('reuse_loan_request_id')==$previousLoan->id)>
                                    {{\Carbon\Carbon::parse($previousSchedule?->date)->format('d M Y')}} - {{$previousSchedule?->activity}} ({{$previousAssets}})
                                </option>@endforeach
                            </select></div>
                            @else
                            <div class="alert alert-secondary py-2 px-3 mt-3 mb-2"><i class="bx bx-file me-1"></i><strong>Belum ada data peminjaman sebelumnya pada pelatihan ini.</strong><div class="small mt-1">Unggah surat PDF dan isi data pemohon untuk sesi pertama. Data tersebut dapat digunakan ulang saat membuat sesi berikutnya.</div></div>
                            @endif
                            <div id="new_loan_data">
                                <div class="mt-3"><label class="form-label fw-bold">Surat Peminjaman <span class="text-danger">*</span></label><input type="file" name="loan_letter" class="form-control" accept="application/pdf,.pdf"><small class="text-muted">PDF maksimal 5 MB. Satu surat berlaku untuk seluruh aset yang dipilih.</small></div>
                                <div class="mt-3"><label class="form-label fw-bold">Keperluan Peminjaman</label><textarea name="loan_purpose" class="form-control" rows="2">{{ old('loan_purpose') }}</textarea></div>
                                <div class="mt-3"><label class="form-label fw-bold">Kontak Pemohon</label><input name="loan_contact" class="form-control" value="{{ old('loan_contact') }}" placeholder="Nama / nomor yang dapat dihubungi"></div>
                            </div>
                            <div id="reused_loan_summary" class="alert alert-success mt-3 mb-0 d-none"><i class="bx bx-check-circle me-1"></i><strong>Data lama akan digunakan otomatis.</strong><div class="small mt-1"><span id="reused_purpose"></span><br><span id="reused_contact"></span></div></div>
                        </div>
                        <div id="create_external" class="d-none">
                            <input name="external_place" class="form-control mb-2" value="{{ old('external_place') }}" placeholder="Nama/alamat tempat eksternal (opsional jika menggunakan Zoom)">
                            <div class="input-group">
                                <span class="input-group-text text-success"><i class="bx bx-video"></i></span>
                                <input type="url" name="link_zoom" class="form-control" value="{{ old('link_zoom') }}" placeholder="https://zoom.us/j/...">
                            </div>
                            <small class="text-muted">Isi tempat eksternal, tautan Zoom, atau keduanya.</small>
                        </div>
                    </div>

                    <div class="mb-3 create-learning-section">
                        <label class="form-label fw-bold text-primary">Penanggung Jawab (PIC) <span class="text-danger">*</span></label>
                        <input type="text" name="pic" class="form-control" placeholder="Nama PIC Kelas / Panitia" value="{{ old('pic', auth()->user()->name) }}" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="bx bx-plus me-1"></i> Simpan Jadwal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: TABEL DAFTAR JADWAL -->
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle schedule-table">
                    <thead class="table-light">
                        <tr>
                            <th class="schedule-table__time">Waktu & Durasi</th>
                            <th>Materi, Pengajar & Link</th> 
                            <th class="schedule-table__actions text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($schedules as $s)
                        @php $isBreak=($s->schedule_type??'learning')==='break'; @endphp
                        <tr>
                            <td class="schedule-table__time">
                                <span class="badge bg-label-secondary">
                                    {{ \Carbon\Carbon::parse($s->date)->translatedFormat('d M Y') }}
                                </span><br>
                                <small class="fw-bold text-dark">{{ $s->start_time }} - {{ $s->end_time }}</small>
                                @if($isBreak)
                                    <span class="badge bg-label-warning ms-1"><i class="bx bx-coffee me-1"></i>Istirahat</span>
                                @elseif($s->jp)
                                    <span class="badge bg-label-primary ms-1">{{ $s->duration_label }}</span>
                                @endif
                            </td>
                            <td class="schedule-table__content">
                                <div class="fw-bold text-dark mb-1">{{ $s->activity }}</div>
                                <div class="d-flex flex-column gap-1">
                                    @if($isBreak)
                                        <small class="text-warning fw-semibold"><i class="bx bx-coffee me-1"></i>Jeda kegiatan - tidak dihitung JP/OJ</small>
                                    @elseif($s->pengajar)
                                        <small class="text-info fw-semibold">
                                            <i class="bx bx-chalkboard me-1"></i>Pengajar: {{ $s->pengajar->name }}
                                        </small>
                                    @else
                                        <small class="text-muted"><i class="bx bx-minus me-1"></i>Tanpa Pengajar Khusus</small>
                                    @endif
                                    
                                    @unless($isBreak)<div class="d-flex align-items-center gap-2">
                                        <small class="text-muted">
                                            <i class="bx bx-user me-1"></i>PIC: {{ $s->pic }}
                                        </small>
                                        @if($s->link_zoom)
                                            <a href="{{ $s->link_zoom }}" target="_blank" class="badge bg-label-success text-decoration-none">
                                                <i class="bx bx-video me-1"></i> Buka Zoom
                                            </a>
                                        @endif
                                    </div>
                                    <small class="text-primary">
                                        <i class="bx bx-map me-1"></i>
                                        @if($s->venue_type === 'internal')
                                            @php $loan=$s->assetLoanRequest;$loanStatus=$loan?->status; @endphp
                                            {{ $s->bookings->pluck('asset.name')->filter()->join(', ') ?: 'Menunggu persetujuan aset' }}
                                            @if($loanStatus)
                                                <span class="badge bg-label-{{ ['approved'=>'success','pending'=>'warning','revision'=>'info','rejected'=>'danger'][$loanStatus] ?? 'secondary' }} ms-1">
                                                    {{ ['approved'=>'Disetujui','pending'=>'Menunggu','revision'=>'Perlu Perbaikan','rejected'=>'Ditolak'][$loanStatus] ?? ucfirst($loanStatus) }}
                                                </span>
                                                @if($loan->review_note)<small class="d-block text-danger mt-1">Catatan: {{ $loan->review_note }}</small>@endif
                                                <a href="{{ route('asset-loans.document',$loan) }}" target="_blank" class="small d-block mt-1"><i class="bx bxs-file-pdf"></i> Lihat surat</a>
                                            @endif
                                        @else
                                            {{ $s->external_place ?: 'Lokasi eksternal belum diisi' }}
                                        @endif
                                    </small>@endunless
                                </div>
                            </td>
                            <td class="schedule-table__actions">
                                <div class="schedule-action-buttons">
                                    <!-- TOMBOL EDIT -->
                                    <button class="btn btn-sm btn-icon btn-outline-warning" 
                                        onclick="editSchedule({{ json_encode($s) }})"
                                        data-bs-toggle="modal" data-bs-target="#modalEditSchedule" title="Edit Jadwal">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <!-- TOMBOL HAPUS -->
                                    <form action="{{ route('schedules.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus sesi jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Jadwal">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bx bx-calendar-x fs-1 d-block mb-2"></i>
                                Belum ada sesi jadwal untuk pelatihan ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL IMPORT JADWAL EXCEL -->
<div class="modal fade" id="modalImportSchedule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('schedules.import', $training->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="bx bx-file me-1"></i> Import Jadwal dari Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bx bx-info-circle me-1"></i> Template kini mendukung pengisian <strong>Link Zoom</strong> dan pencocokan otomatis <strong>Tenaga Pengajar</strong>.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File Excel (.xlsx / .xls) <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2">
                    <span class="small text-muted">Belum punya formatnya?</span>
                    <a href="{{ route('schedules.template') }}" class="btn btn-sm btn-outline-success">
                        <i class="bx bx-download me-1"></i> Unduh Template Excel
                    </a>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Mulai Import</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT JADWAL -->
<div class="modal fade" id="modalEditSchedule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="formEditSchedule" class="modal-content" enctype="multipart/form-data">
            @csrf 
            @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Edit Sesi Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="edit_date" class="form-control" required>
                </div>
                <div class="mb-3"><label class="form-label fw-bold">Jenis Jadwal</label><select name="schedule_type" id="edit_schedule_type" class="form-select"><option value="learning">Sesi Pembelajaran</option><option value="break">Istirahat / Jeda</option></select></div>
                <div class="row g-3 mb-3 duration-grid">
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="edit_start" class="form-control" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-bold">Jam Selesai <span id="edit_end_badge" class="badge bg-label-secondary fw-normal">Otomatis</span></label>
                        <input type="time" name="break_end_time" id="edit_end" class="form-control bg-light" readonly tabindex="-1">
                    </div>
                    <div class="col-7 col-sm-6 edit-learning-field">
                        <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="jp" id="edit_jp" class="form-control" min="1" max="24" required>
                    </div>
                    <div class="col-5 col-sm-6 edit-learning-field">
                        <label class="form-label fw-bold">Satuan Durasi</label>
                        <select name="duration_unit" id="edit_duration_unit" class="form-select"><option value="JP">JP (45 menit)</option><option value="OJ">OJ (60 menit)</option></select>
                    </div>
                    <div class="col-12 edit-learning-field"><small class="text-muted"><i class="bx bx-calculator me-1"></i>JP: 1 unit = 45 menit. OJ: 1 unit = 60 menit.</small></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Materi / Kegiatan <span class="text-danger">*</span></label>
                    <input type="text" name="activity" id="edit_activity" class="form-control" required>
                </div>
                
                <!-- DROPDOWN PENGAJAR DI MODAL EDIT -->
                <div class="mb-3 edit-learning-section">
                    <label class="form-label fw-bold text-info"><i class="bx bx-chalkboard me-1"></i>Tenaga Pengajar / Fasilitator</label>
                    <select name="pengajar_id" id="edit_pengajar_id" class="form-select select2-modal">
                        <option value="">-- Tanpa Pengajar / Sesi Mandiri --</option>
                        @foreach($pengajars as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->name }} {{ $p->nip_nik ? "({$p->nip_nik})" : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text small">Hanya menampilkan akun Narasumber yang telah disetujui dan memiliki akses Pengajar.</div>
                </div>

                <div class="mb-3 edit-learning-section">
                    <label class="form-label fw-bold">Tempat / Ruangan</label>
                    <select name="venue_type" id="edit_venue_type" class="form-select mb-2">
                        <option value="internal">Internal BPSDM</option>
                        <option value="external">Eksternal</option>
                    </select>
                    <div id="edit_internal">
                        <div id="edit_asset_select_wrap">
                            <label for="edit_asset_ids" class="form-label small fw-semibold mb-1">Aset/Ruangan Internal</label>
                        <select name="asset_ids[]" id="edit_asset_ids" class="form-select select2-edit-assets" multiple>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->name }} — {{ $asset->location }}</option>
                            @endforeach
                        </select>
                            <small class="text-muted d-block mt-1">Pilih satu atau beberapa ruangan yang digunakan.</small>
                        </div>
                        <div class="alert alert-info py-2 px-3 mt-3 mb-2"><i class="bx bx-info-circle me-1"></i>Satu surat, keperluan, dan kontak berlaku untuk seluruh aset yang dipilih.</div>
                        <div class="mt-2"><label class="form-label fw-bold">Ganti Surat Peminjaman (PDF)</label><input type="file" name="loan_letter" class="form-control" accept="application/pdf,.pdf"><small class="text-muted">Kosongkan jika surat lama tetap digunakan. Perubahan akan dikirim ulang untuk persetujuan.</small></div>
                        <div class="mt-3"><label class="form-label fw-bold">Keperluan Peminjaman</label><textarea name="loan_purpose" id="edit_loan_purpose" class="form-control" rows="2"></textarea></div>
                        <div class="mt-3"><label class="form-label fw-bold">Kontak Pemohon</label><input name="loan_contact" id="edit_loan_contact" class="form-control"></div>
                    </div>
                    <div id="edit_external" class="d-none">
                        <input name="external_place" id="edit_external_place" class="form-control mb-2" placeholder="Nama/alamat tempat eksternal (opsional jika menggunakan Zoom)">
                        <div class="input-group">
                            <span class="input-group-text text-success"><i class="bx bx-video"></i></span>
                            <input type="url" name="link_zoom" id="edit_link_zoom" class="form-control" placeholder="https://zoom.us/j/...">
                        </div>
                        <small class="text-muted">Isi tempat eksternal, tautan Zoom, atau keduanya.</small>
                    </div>
                </div>

                <div class="mb-3 edit-learning-section">
                    <label class="form-label fw-bold text-primary">Penanggung Jawab (PIC) <span class="text-danger">*</span></label>
                    <input type="text" name="pic" id="edit_pic" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Pilih Pengajar --',
            allowClear: true,
            width: '100%'
        });

        $('.select2-modal').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalEditSchedule'),
            placeholder: '-- Pilih Pengajar --',
            allowClear: true,
            width: '100%'
        });
        $('.select2-assets').select2({ theme: 'bootstrap-5', placeholder: 'Pilih aset/ruangan', width: '100%' });
        $('.select2-edit-assets').select2({ theme: 'bootstrap-5', dropdownParent: $('#edit_asset_select_wrap'), placeholder: 'Pilih aset/ruangan', closeOnSelect: false, width: '100%' });

        function calculateEndTime(prefix) {
            const start = $('#' + prefix + '_start').val();
            const jp = parseInt($('#' + prefix + '_jp').val(), 10);
            if (!start || !jp || jp < 1) {
                $('#' + prefix + '_end').val('');
                return;
            }
            const [hours, minutes] = start.split(':').map(Number);
            const unit = ($('#' + prefix + '_duration_unit').val() || 'JP').toUpperCase();
            const totalMinutes = (hours * 60 + minutes + (jp * (unit === 'OJ' ? 60 : 45))) % (24 * 60);
            const endHours = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
            const endMinutes = String(totalMinutes % 60).padStart(2, '0');
            $('#' + prefix + '_end').val(`${endHours}:${endMinutes}`);
        }

        $('#create_start, #create_jp, #create_duration_unit').on('input change', () => calculateEndTime('create'));
        $('#edit_start, #edit_jp, #edit_duration_unit').on('input change', () => calculateEndTime('edit'));
        calculateEndTime('create');

        function toggleVenue(prefix) {
            const internal = $('#' + prefix + '_venue_type').val() === 'internal';
            $('#' + prefix + '_internal').toggleClass('d-none', !internal);
            $('#' + prefix + '_external').toggleClass('d-none', internal);
        }
        $('#create_venue_type').on('change', () => toggleVenue('create'));
        $('#edit_venue_type').on('change', () => toggleVenue('edit'));
        toggleVenue('create');
        function toggleScheduleType(prefix) {
            const isBreak = $('#' + prefix + '_schedule_type').val() === 'break';
            $('.' + prefix + '-learning-field, .' + prefix + '-learning-section').toggleClass('d-none', isBreak)
                .find(':input').prop('disabled', isBreak);
            const end = $('#' + prefix + '_end');
            end.prop('readonly', !isBreak).toggleClass('bg-light', !isBreak).attr('tabindex', isBreak ? 0 : -1);
            $('#' + prefix + '_end_badge').text(isBreak ? 'Manual' : 'Otomatis')
                .toggleClass('bg-label-warning', isBreak).toggleClass('bg-label-secondary', !isBreak);
            if (!isBreak) calculateEndTime(prefix);
        }
        $('#create_schedule_type').on('change', () => toggleScheduleType('create'));
        $('#edit_schedule_type').on('change', () => toggleScheduleType('edit'));
        toggleScheduleType('create');
        function toggleReuseLoan() {
            const option = $('#reuse_loan_request_id option:selected');
            const reused = Boolean(option.val());
            $('#new_loan_data').toggleClass('d-none', reused);
            $('#reused_loan_summary').toggleClass('d-none', !reused);
            $('#reused_purpose').text('Keperluan: ' + (option.data('purpose') || '-'));
            $('#reused_contact').text('Kontak: ' + (option.data('contact') || '-'));
        }
        $('#reuse_loan_request_id').on('change', toggleReuseLoan);
        toggleReuseLoan();
    });

    function editSchedule(data) {
        const url = "{{ url('schedules') }}/" + data.id;
        $('#formEditSchedule').attr('action', url);

        $('#edit_date').val(data.date);
        $('#edit_start').val(data.start_time);
        $('#edit_end').val(data.end_time);
        $('#edit_activity').val(data.activity);
        $('#edit_schedule_type').val(data.schedule_type || 'learning');
        $('#edit_jp').val(data.jp);
        $('#edit_duration_unit').val(data.duration_unit || 'JP');
        $('#edit_link_zoom').val(data.link_zoom || '');
        const jp = parseInt(data.jp, 10);
        if (data.start_time && jp) {
            const [hours, minutes] = data.start_time.substring(0, 5).split(':').map(Number);
            const totalMinutes = (hours * 60 + minutes + (jp * ((data.duration_unit || 'JP') === 'OJ' ? 60 : 45))) % (24 * 60);
            $('#edit_end').val(`${String(Math.floor(totalMinutes / 60)).padStart(2, '0')}:${String(totalMinutes % 60).padStart(2, '0')}`);
        }
        $('#edit_pic').val(data.pic);
        $('#edit_pengajar_id').val(data.pengajar_id).trigger('change');
        $('#edit_venue_type').val(data.venue_type || 'external');
        $('#edit_external_place').val(data.external_place || '');
        const requestedAssets = data.asset_loan_request?.asset_ids || (data.bookings || []).map(item => item.asset_id);
        $('#edit_asset_ids').val(requestedAssets.map(item => String(item))).trigger('change');
        $('#edit_loan_purpose').val(data.asset_loan_request?.purpose || '');
        $('#edit_loan_contact').val(data.asset_loan_request?.contact_person || '');
        const internal = $('#edit_venue_type').val() === 'internal';
        $('#edit_internal').toggleClass('d-none', !internal);
        $('#edit_external').toggleClass('d-none', internal);
        $('#edit_schedule_type').trigger('change');
    }
</script>
@endpush
