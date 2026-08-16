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
                    <h4 class="text-white mb-1 fw-bold">{{ $training->nama_pelatihan }}</h4>
                    <p class="text-white mb-0 opacity-75">Angkatan {{ $training->angkatan }} | {{ $training->bidang }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- SEKSI 1: MANAJEMEN DATA (6 Kolom) -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border shadow-none">
                <div class="card-header border-bottom bg-light py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-data me-2"></i>Manajemen Data</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('trainings.schedules', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-3 mb-2 rounded border shadow-xs">
                            <div class="avatar bg-label-primary me-3 p-2 rounded"><i class="bx bx-calendar h4 mb-0"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Buat Jadwal</h6>
                                <small class="text-muted">Kelola sesi dan narasumber</small>
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
                </div>
            </div>
        </div>

        <!-- SEKSI 2: PENGATURAN AKSES & MONITORING -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border shadow-none">
                <div class="card-header border-bottom bg-light py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-cog me-2"></i>Akses & Monitoring</h5>
                </div>
                <div class="card-body pt-4">
                    {{-- Tombol Copy Kode Undangan --}}
                    <div class="p-3 mb-3 border rounded bg-label-primary">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1">Kode Undangan (Token)</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 fw-bold text-primary">{{ $training->invitation_code }}</h4>
                            <button class="btn btn-primary btn-sm" onclick="copyInvitation('{{ $training->invitation_code }}', this)">
                                <i class="bx bx-copy me-1"></i> Salin
                            </button>
                        </div>
                    </div>

                    <div class="list-group list-group-flush">
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalLms{{ $training->id }}" class="list-group-item list-group-item-action d-flex align-items-center p-3 mb-2 rounded border shadow-xs">
                            <div class="avatar bg-label-dark me-3 p-2 rounded"><i class="bx bx-link-external h4 mb-0"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Input Link LMS</h6>
                                <small class="text-muted">Akses ruang belajar digital</small>
                            </div>
                        </a>
                        <a href="{{ route('monitoring.fill', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-3 rounded border shadow-xs">
                            <div class="avatar bg-label-info me-3 p-2 rounded"><i class="bx bx-desktop h4 mb-0"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Isi Monitoring</h6>
                                <small class="text-muted">Pemantauan tahapan pelatihan</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEKSI 3: EVALUASI & LAPORAN -->
        <div class="col-md-12 col-lg-4">
            <div class="card h-100 border shadow-none">
                <div class="card-header border-bottom bg-light py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-bar-chart-alt-2 me-2"></i>Evaluasi & Laporan</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('evall1.index', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-2 mb-2 rounded border">
                            <i class="bx bx-smile me-3 text-warning h4 mb-0"></i>
                            <div><h6 class="mb-0 small fw-bold">Level 1: Reaksi</h6></div>
                        </a>
                        <a href="{{ route('evall2.index', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-2 mb-2 rounded border">
                            <i class="bx bx-book-open me-3 text-success h4 mb-0"></i>
                            <div><h6 class="mb-0 small fw-bold">Level 2: Learning</h6></div>
                        </a>
                        <a href="{{ route('evall34.index', $training->id) }}" class="list-group-item list-group-item-action d-flex align-items-center p-2 mb-3 rounded border">
                            <i class="bx bx-trending-up me-3 text-primary h4 mb-0"></i>
                            <div><h6 class="mb-0 small fw-bold">Level 3 & 4: Dampak</h6></div>
                        </a>
                    </div>

                    <h6 class="small text-muted text-uppercase fw-bold mt-2">Export Dokumen (Excel/Word)</h6>
                    <div class="d-grid gap-2 mt-2">
                        <a href="{{ route('trainings.export_evaluation', $training->id) }}" class="btn btn-sm btn-outline-success">
                            <i class="bx bx-download me-1"></i> Hasil L1 & L2 (Excel)
                        </a>
                        <a href="{{ route('evall34.export', $training->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-spreadsheet me-1"></i> Laporan L3 & L4 (Excel)
                        </a>
                        <a href="{{ route('evall34.export_word', $training->id) }}" class="btn btn-sm btn-outline-info">
                            <i class="bx bx-file me-1"></i> Laporan Akhir (Word)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL LMS TETAP SAMA --}}
<div class="modal fade" id="modalLms{{ $training->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('trainings.set_lms', $training->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Pengaturan Link LMS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">URL Learning Management System (LMS)</label>
                    <input type="url" name="link_lms" class="form-control" placeholder="https://..." value="{{ $training->link_lms }}" required>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="submit" class="btn btn-primary w-100">Simpan Link</button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
function copyInvitation(code, btn) {
    navigator.clipboard.writeText(code).then(() => {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bx bx-check me-1"></i> Disalin';
        btn.classList.replace('btn-primary', 'btn-success');
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.replace('btn-success', 'btn-primary');
        }, 2000);
    });
}
</script>
@endpush
@endsection