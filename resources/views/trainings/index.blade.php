@extends('layouts.master')

@section('title', 'Daftar Pelatihan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Daftar Pelatihan</h4>
            <p class="text-muted mb-0">Manajemen dan pemantauan seluruh program pelatihan aktif</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle shadow" type="button" data-bs-toggle="dropdown">
                <i class="bx bx-plus me-1"></i> Buat Pelatihan
            </button>
            <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item" href="{{ route('trainings.create', ['model' => 'standar']) }}"><i class="bx bx-chalkboard me-2"></i>Model Standar</a></li>
                <li><a class="dropdown-item" href="{{ route('trainings.create', ['model' => 'blended']) }}"><i class="bx bx-sync me-2"></i>Model Blended Learning</a></li>
            </ul>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center border-0 shadow-sm mb-4" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search & Filter Bar (Opsional tapi keren) -->
    <div class="card mb-4 border-0 shadow-none bg-transparent">
        <div class="row g-3">
            <div class="col-md-12">
                <div class="input-group input-group-merge shadow-sm">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" class="form-control" placeholder="Cari nama pelatihan, lokasi atau bidang...">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <small class="text-muted">
            Menampilkan {{ $trainings->firstItem() ?? 0 }}-{{ $trainings->lastItem() ?? 0 }}
            dari {{ $trainings->total() }} pelatihan
        </small>
    </div>

    <!-- Main List -->
    <div class="row pb-5">
        @forelse($trainings as $t)
        <div class="col-12 mb-3">
            <div class="card border-0 shadow-sm card-training-row">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <!-- 1. Info Pelatihan -->
                        <div class="col-lg-4 border-end-lg">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge {{ $t->model == 'blended' ? 'bg-label-warning' : 'bg-label-info' }} btn-xs text-uppercase me-2" style="font-size: 10px;">
                                    {{ $t->model }}
                                </span>
                                <small class="text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;">ANGKATAN {{ $t->angkatan }}</small>
                            </div>
                            <h5 class="fw-bold mb-1 text-dark">{{ $t->nama_pelatihan }}</h5>
                            <div class="d-flex align-items-center">
                                <i class="bx bx-map-pin text-danger me-1" style="font-size: 14px;"></i>
                                <small class="text-muted">{{ $t->lokasi }}</small>
                            </div>
                        </div>

                        <!-- 2. Bidang & Jadwal -->
                        <div class="col-lg-3 py-2 py-lg-0 border-end-lg px-lg-4">
                            <small class="text-muted d-block mb-1 text-uppercase fw-semibold" style="font-size: 10px;">Penyelenggara</small>
                            <p class="mb-2 text-dark small fw-bold text-wrap" style="line-height: 1.3;">{{ $t->bidang }}</p>
                            <div class="d-flex gap-3">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 10px;">Mulai</small>
                                    <small class="fw-bold text-dark">{{ \Carbon\Carbon::parse($t->tgl_mulai)->format('d/m/Y') }}</small>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 10px;">Selesai</small>
                                    <small class="fw-bold text-dark">{{ \Carbon\Carbon::parse($t->tgl_selesai)->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Status & Live Activity (HIGHLIGHT) -->
                        <div class="col-lg-3 py-2 py-lg-0 px-lg-4">
                            @php $current = $t->current_activity; @endphp
                            @if($current)
                                <small class="text-success fw-bold d-flex align-items-center mb-1" style="font-size: 10px;">
                                    <span class="live-pulse me-2"></span> SEDANG BERLANGSUNG
                                </small>
                                <div class="bg-label-success p-2 rounded border border-success border-dashed">
                                    <h6 class="mb-0 fw-bold text-dark small text-wrap">{{ $current->activity }}</h6>
                                    <small class="text-muted">{{ substr($current->start_time, 0, 5) }} - {{ substr($current->end_time, 0, 5) }}</small>
                                </div>
                            @else
                                <small class="text-muted d-block mb-1" style="font-size: 10px;">Status Pelatihan</small>
                                @php $sisa = $t->sisa_hari; @endphp
                                @if($sisa < 0)
                                    <span class="badge bg-label-danger w-100 py-2"><i class="bx bx-check-double me-1"></i> Pelatihan Selesai</span>
                                @elseif($sisa == 0)
                                    <span class="badge bg-label-warning w-100 py-2 animate__animated animate__flash animate__infinite">Hari Terakhir</span>
                                @else
                                    <span class="badge bg-label-success w-100 py-2">{{ $sisa }} Hari Lagi</span>
                                @endif
                            @endif
                        </div>

                        <!-- 4. Aksi -->
                        <div class="col-lg-2 text-center text-lg-end mt-3 mt-lg-0">
                            <div class="d-flex justify-content-lg-end align-items-center gap-2">
                                <a href="{{ route('trainings.manage', $t->id) }}" class="btn btn-primary btn-sm px-3">
                                    <i class="bx bx-cog me-1"></i> Kelola
                                </a>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end shadow-lg">
                                        <a class="dropdown-item text-warning" href="{{ route('trainings.edit', $t->id) }}">
                                            <i class="bx bx-edit-alt me-2"></i> Edit Pelatihan
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('trainings.destroy', $t->id) }}" method="POST" class="delete-training-form" data-training-name="{{ $t->nama_pelatihan }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bx bx-trash me-2"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-5 text-center text-muted">
                    <i class="bx bx-book-open fs-1 d-block mb-2"></i>
                    Belum ada data pelatihan.
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($trainings->hasPages())
        <div class="d-flex justify-content-center mb-4">
            {{ $trainings->onEachSide(1)->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="deleteTrainingModal" tabindex="-1" aria-labelledby="deleteTrainingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-label-danger px-4 pt-4 pb-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="delete-warning-icon"><i class="bx bx-trash"></i></span>
                    <div>
                        <small class="text-danger fw-bold text-uppercase">Tindakan permanen</small>
                        <h5 class="modal-title fw-bold mb-0" id="deleteTrainingModalLabel">Hapus Pelatihan?</h5>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-2">Anda akan menghapus pelatihan:</p>
                <div class="border border-danger rounded-3 p-3 bg-label-danger mb-4">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bx bx-book-open text-danger fs-4"></i>
                        <strong class="text-dark" id="deleteTrainingName">-</strong>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">Seluruh data berikut akan ikut dihapus:</h6>
                <div class="row g-2 mb-4">
                    @foreach([
                        ['bx-group', 'Peserta & profil alumni'],
                        ['bx-fingerprint', 'Presensi peserta'],
                        ['bx-bar-chart-alt-2', 'Evaluasi L1–L4'],
                        ['bx-task-x', 'Monitoring & tindak lanjut'],
                        ['bx-calendar-x', 'Jadwal & administrasi pengajar'],
                        ['bx-conversation', 'Forum pelatihan'],
                        ['bx-folder-minus', 'Folder & seluruh dokumen'],
                        ['bx-cube', 'Reservasi aset pelatihan'],
                    ] as [$icon, $label])
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2 small text-muted p-2 rounded bg-light h-100">
                                <i class="bx {{ $icon }} text-danger"></i><span>{{ $label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="alert alert-success border-0 d-flex align-items-start mb-0">
                    <i class="bx bx-shield-quarter fs-4 me-2"></i>
                    <div><strong>Akun user tetap aman.</strong><br><small>Akun pengguna dan data mereka pada pelatihan lain tidak akan dihapus.</small></div>
                </div>
            </div>
            <div class="modal-footer border-top px-4 py-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteTraining">
                    <i class="bx bx-trash me-1"></i>Ya, Hapus Seluruh Data
                </button>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
    /* Card Row Effect */
    .card-training-row {
        transition: all 0.3s ease;
        border: 1px solid transparent !important;
    }
    .card-training-row:hover {
        transform: scale(1.01);
        border-color: #696cff !important;
        box-shadow: 0 10px 20px rgba(105, 108, 255, 0.1) !important;
    }

    /* Vertical Divider for Desktop */
    @media (min-width: 992px) {
        .border-end-lg {
            border-right: 1px solid #eee;
        }
    }

    /* Live Pulse Indicator */
    .live-pulse {
        width: 8px;
        height: 8px;
        background: #71dd37;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 rgba(113, 221, 55, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(113, 221, 55, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(113, 221, 55, 0); }
        100% { box-shadow: 0 0 0 0 rgba(113, 221, 55, 0); }
    }

    .text-wrap {
        white-space: normal !important;
    }

    .shadow-xs {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .card-training-row {
        position: relative;
        /* Pastikan container tidak memotong elemen yang keluar jalur */
        overflow: visible !important; 
    }

    /* Saat dropdown diklik, naikkan posisi tumpukan kartu tersebut */
    .card-training-row:focus-within {
        z-index: 1050;
    }

    /* Memperbaiki tampilan dropdown menu agar lebih menonjol */
    .dropdown-menu {
        z-index: 1100 !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        border: 1px solid #e7e7ff;
    }
    
    /* Warna tombol hapus agar lebih tegas */
    .dropdown-item.text-danger:hover {
        background-color: #ffebee !important;
    }

    .delete-warning-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 52px;
        color: #ff3e1d;
        background: rgba(255, 62, 29, .14);
        font-size: 1.7rem;
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('deleteTrainingModal');
    const modal = new bootstrap.Modal(modalElement);
    const trainingName = document.getElementById('deleteTrainingName');
    const confirmButton = document.getElementById('confirmDeleteTraining');
    let selectedForm = null;

    document.querySelectorAll('.delete-training-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            selectedForm = form;
            trainingName.textContent = form.dataset.trainingName || 'Pelatihan ini';
            confirmButton.disabled = false;
            confirmButton.innerHTML = '<i class="bx bx-trash me-1"></i>Ya, Hapus Seluruh Data';
            modal.show();
        });
    });

    confirmButton.addEventListener('click', function () {
        if (!selectedForm) return;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus...';
        selectedForm.submit();
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        selectedForm = null;
    });
});
</script>
@endpush
@endsection
