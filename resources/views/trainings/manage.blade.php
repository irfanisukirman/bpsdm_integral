@extends('layouts.master')

@section('title', 'Kelola Pelatihan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Pelatihan /</span> Kelola Data
        </h4>
        <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Banner Info Pelatihan -->
    <div class="card mb-4 bg-primary">
        <div class="card-body py-4">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-xl bg-label-white p-2 rounded me-3 shadow">
                    <i class="bx bx-buildings h2 mb-0 text-white"></i>
                </div>
                <div>
                    <h4 class="text-white mb-1 fw-bold text-uppercase">{{ $training->nama_pelatihan }}</h4>
                    <p class="text-white mb-0 opacity-75">Angkatan {{ $training->angkatan }} | {{ $training->bidang }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- SEKSI 1: MANAJEMEN DATA (UTAMA) -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border shadow-none">
                <div class="card-header border-bottom bg-light py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-data me-2"></i>Manajemen Data</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="list-group list-group-flush mb-4">
                        <a href="{{ route('trainings.schedules', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-3 mb-2 rounded border shadow-xs">
                            <div class="avatar bg-label-primary me-3 p-2 rounded"><i class="bx bx-calendar h4 mb-0"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Jadwal Pelatihan</h6>
                                <small class="text-muted">Kelola sesi & pengajar</small>
                            </div>
                        </a>
                        <a href="{{ route('trainings.participants', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-3 mb-2 rounded border shadow-xs">
                            <div class="avatar bg-label-success me-3 p-2 rounded"><i class="bx bx-group h4 mb-0"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Daftar Peserta</h6>
                                <small class="text-muted">{{ $training->participants_count }} Peserta terdaftar</small>
                            </div>
                        </a>
                        <a href="{{ route('attendance.index', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-3 rounded border shadow-xs">
                            <div class="avatar bg-label-info me-3 p-2 rounded"><i class="bx bx-user-check h4 mb-0"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Kehadiran / Absensi</h6>
                                <small class="text-muted">Pantau presensi harian</small>
                            </div>
                        </a>
                    </div>

                    <h6 class="small text-muted text-uppercase fw-bold mt-2">Export & Dokumen Data</h6>
                    <div class="d-grid gap-2">
                        {{-- Download Jadwal (PDF) --}}
                        <a href="{{ route('schedules.pdf', $training->id) }}" class="btn btn-sm btn-outline-danger text-start">
                            <i class="bx bxs-file-pdf me-2"></i> Download Jadwal (PDF)
                        </a>
                        
                        {{-- Export Data Peserta (XLSX) --}}
                        <a href="{{ route('participants.export_data', $training->id) }}" class="btn btn-sm btn-outline-success text-start">
                            <i class="bx bxs-spreadsheet me-2"></i> Export Data Peserta (Excel)
                        </a>
                        
                        {{-- Download Rekap Kehadiran (XLSX) --}}
                        <a href="{{ route('attendance.excel.all', $training->id) }}" class="btn btn-sm btn-outline-primary text-start">
                            <i class="bx bx-table me-2"></i> Download Rekap Kehadiran (Excel)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEKSI 2: AKSES & MONITORING -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border shadow-none">
                <div class="card-header border-bottom bg-light py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-shield-quarter me-2"></i>Akses & Monitoring</h5>
                </div>
                <div class="card-body pt-4">
                    <h6 class="small text-muted text-uppercase fw-bold mb-3">Kontrol Akses Peserta</h6>
                    <div class="p-3 mb-3 border rounded bg-label-primary shadow-xs">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1">Kode Undangan (Token)</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 fw-bold text-primary">{{ $training->invitation_code }}</h4>
                            <button class="btn btn-primary btn-sm" onclick="copyInvitation('{{ $training->invitation_code }}', this)">
                                <i class="bx bx-copy me-1"></i> Salin
                            </button>
                        </div>
                    </div>
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalLms{{ $training->id }}" class="btn btn-outline-dark btn-sm w-100 mb-4">
                        <i class="bx bx-link-external me-1"></i> {{ $training->link_lms ? 'Update Link LMS' : 'Input Link LMS' }}
                    </a>

                    {{-- PEMBATAS --}}
                    <hr class="my-4">

                    <h6 class="small text-muted text-uppercase fw-bold mb-3">Instrumen Monitoring</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('monitoring.fill', $training->id) }}" class="btn btn-info shadow-sm mb-2">
                            <i class="bx bx-edit-alt me-1"></i> ISI INSTRUMEN MONITORING
                        </a>
                        <a href="{{ route('monitoring.export.laporan', $training->id) }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bx bxs-file-doc me-2 text-primary"></i> Download Laporan Monitoring
                        </a>
                        <a href="{{ route('monitoring.export.tindaklanjut', $training->id) }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bx bxs-file-doc me-2 text-warning"></i> Download Laporan Tindak Lanjut
                        </a>
                        <a href="{{ route('monitoring.export.rekap', $training->id) }}" class="btn btn-sm btn-outline-secondary text-start">
                            <i class="bx bxs-spreadsheet me-2 text-success"></i> Download Ceklis Harian (Excel)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEKSI 3: EVALUASI & LAPORAN AKHIR -->
        <div class="col-md-12 col-lg-4">
            <div class="card h-100 border shadow-none">
                <div class="card-header border-bottom bg-light py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-bar-chart-alt-2 me-2"></i>Evaluasi & Dampak</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="list-group list-group-flush mb-4">
                        <a href="{{ route('evall1.index', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-2 mb-2 rounded border">
                            <i class="bx bx-smile me-3 text-warning h4 mb-0"></i>
                            <div><h6 class="mb-0 small fw-bold">Level 1: Reaksi</h6></div>
                        </a>
                        <a href="{{ route('evall2.index', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-2 mb-2 rounded border">
                            <i class="bx bx-book-open me-3 text-success h4 mb-0"></i>
                            <div><h6 class="mb-0 small fw-bold">Level 2: Learning</h6></div>
                        </a>
                        <a href="{{ route('evall34.index', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-2 rounded border">
                            <i class="bx bx-trending-up me-3 text-primary h4 mb-0"></i>
                            <div><h6 class="mb-0 small fw-bold">Level 3 & 4: Dampak</h6></div>
                        </a>
                    </div>

                    <h6 class="small text-muted text-uppercase fw-bold mt-2">Laporan Rekapitulasi & Surat</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('trainings.export_evaluation', $training->id) }}" class="btn btn-sm btn-outline-success text-start">
                            <i class="bx bxs-file-export me-2"></i> Hasil L1 & L2 (Excel)
                        </a>
                        <a href="{{ route('evall34.export', $training->id) }}" class="btn btn-sm btn-outline-primary text-start">
                            <i class="bx bxs-spreadsheet me-2"></i> Laporan Statistik L3 & L4 (Excel)
                        </a>
                        <a href="{{ route('evall34.export_word', $training->id) }}" class="btn btn-sm btn-outline-info text-start">
                            <i class="bx bxs-file-doc me-2"></i> Laporan Akhir Dampak (Word)
                        </a>
                        <a href="{{ route('evall34.export_invitation', $training->id) }}" class="btn btn-sm btn-outline-danger text-start">
                            <i class="bx bxs-envelope me-2"></i> Undangan Evaluasi Pasca (Word)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL LMS --}}
<div class="modal fade" id="modalLms{{ $training->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('trainings.set_lms', $training->id) }}" method="POST" class="modal-content shadow-lg">
            @csrf @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Tautan Akses Pelatihan (LMS)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="mb-0">
                    <label class="form-label fw-bold">Link URL (Zoom/LMS/Drive)</label>
                    <input type="url" name="link_lms" class="form-control" placeholder="https://..." value="{{ $training->link_lms }}" required>
                    <div class="form-text mt-2 small text-muted">Pastikan URL lengkap diawali dengan http:// atau https://</div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Tautan</button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
function copyInvitation(code, btn) {
    navigator.clipboard.writeText(code).then(() => {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bx bx-check me-1"></i> Tersalin';
        btn.classList.replace('btn-primary', 'btn-success');
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.replace('btn-success', 'btn-primary');
        }, 2000);
    });
}
</script>
@endpush

<style>
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .btn-group-vertical > .btn { text-align: left !important; }
    .list-group-item-action:hover { background-color: #f8f9ff; border-color: #696cff !important; z-index: 1; }
</style>
@endsection