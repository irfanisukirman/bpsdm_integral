@extends('layouts.master')

@section('title', 'Hasil Pencarian Global')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Pencarian /</span> Hasil untuk: <span class="text-primary">"{{ $query }}"</span>
        </h4>
        <button onclick="window.history.back()" class="btn btn-sm btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </button>
    </div>

    <div class="row">
        <!-- SEKSI PELATIHAN -->
        <div class="col-12 mb-4">
            <div class="card shadow-none border">
                <div class="card-header bg-label-primary py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-collection me-2"></i>Data Pelatihan</h5>
                    <span class="badge bg-primary">{{ $trainings->count() }} Ditemukan</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Pelatihan</th>
                                <th>Model & Metode</th>
                                <th>Status</th>
                                <th class="text-center">Lihat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trainings as $t)
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark">{{ $t->nama_pelatihan }}</span><br>
                                    <small class="text-muted">Angkatan {{ $t->angkatan }}</small>
                                </td>
                                <td>
                                    <small class="d-block text-uppercase fw-bold">{{ $t->model }}</small>
                                    <span class="badge bg-label-secondary btn-xs">{{ $t->metode }}</span>
                                </td>
                                <td>
                                    @if($t->sisa_hari < 0)
                                        <span class="badge bg-label-danger">Selesai</span>
                                    @else
                                        <span class="badge bg-label-success">Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{-- Hanya Link Navigasi, Tanpa Tombol Edit/Hapus --}}
                                    <a href="{{ route('trainings.index') }}" class="btn btn-sm btn-icon btn-label-primary">
                                        <i class="bx bx-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4">Tidak ada data pelatihan yang cocok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SEKSI PESERTA -->
        <div class="col-md-7 mb-4">
            <div class="card h-100 shadow-none border">
                <div class="card-header bg-label-success py-3">
                    <h5 class="mb-0 text-success fw-bold"><i class="bx bx-user me-2"></i>Data Peserta</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                            @forelse($participants as $p)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-label-success">{{ substr($p->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block text-dark">{{ $p->name }}</span>
                                            <small class="text-muted">{{ $p->nip_nik }} | {{ $p->instansi }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="d-block fw-bold text-primary">Pelatihan:</small>
                                    <small class="text-truncate d-inline-block" style="max-width: 150px;">{{ $p->training->nama_pelatihan }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr><td class="text-center py-5 text-muted">Data peserta tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SEKSI DOKUMEN -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 shadow-none border">
                <div class="card-header bg-label-warning py-3">
                    <h5 class="mb-0 text-warning fw-bold"><i class="bx bx-file me-2"></i>Berkas & Dokumen</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody>
                            @forelse($files as $f)
                            <tr>
                                <td>
                                    <span class="fw-bold d-block text-dark text-truncate" style="max-width: 200px;">{{ $f->display_name }}</span>
                                    <small class="text-muted">{{ strtoupper($f->file_type) }} | {{ round($f->file_size / 1024, 1) }} KB</small>
                                </td>
                                <td class="text-end">
                                    {{-- Hanya Tombol Download, Tanpa Tombol Hapus --}}
                                    <a href="{{ asset('storage/' . $f->file_path) }}" target="_blank" class="btn btn-sm btn-icon btn-primary shadow-sm">
                                        <i class="bx bx-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td class="text-center py-5 text-muted">Berkas tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection