@extends('layouts.master')

@section('title', 'Jadwal Mengajar Saya')

@push('css')
<style>
    .table-responsive-custom {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        width: 100%;
    }
    .table-responsive-custom table {
        min-width: 800px;
    }
    .text-wrap-cell {
        white-space: normal !important;
        word-break: break-word;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header Halaman -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Portal Pengajar /</span> Jadwal Mengajar Saya
            </h4>
            <p class="text-muted small mb-0">
                Daftar seluruh pelatihan dan rincian sesi materi yang ditugaskan kepada Anda.
            </p>
        </div>
        <div>
            <span class="badge bg-label-info px-3 py-2 fs-6">
                <i class="bx bx-chalkboard me-1"></i> Total {{ $myTrainings->count() }} Pelatihan Ditugaskan
            </span>
        </div>
    </div>

    <!-- TABEL DIKLAT / PELATIHAN YANG DIAJARKAN -->
    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark">
                <i class="bx bx-calendar-event me-2 text-primary"></i>Diklat / Pelatihan yang Saya Ajarkan
            </h5>
        </div>
        
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 250px;">Nama Pelatihan & Jadwal</th>
                        <th style="min-width: 150px;">Bidang</th>
                        <th style="min-width: 280px;">Sesi yang Ditugaskan</th>
                        <th style="min-width: 130px;">Status</th>
                        <th style="min-width: 140px;" class="text-center">Jadwal Lengkap</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($myTrainings as $t)
                    <tr>
                        <!-- Kolom Nama Pelatihan -->
                        <td class="text-wrap-cell">
                            <strong class="text-dark d-block mb-1 fs-6">{{ $t->nama_pelatihan }}</strong>
                            <small class="text-muted d-block">
                                <i class="bx bx-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($t->tgl_mulai)->translatedFormat('d M Y') }} s.d {{ \Carbon\Carbon::parse($t->tgl_selesai)->translatedFormat('d M Y') }}
                            </small>
                            <small class="text-muted d-block">
                                <i class="bx bx-map-pin me-1"></i>{{ $t->lokasi ?? 'BPSDM Jabar' }}
                            </small>
                        </td>

                        <!-- Kolom Bidang -->
                        <td class="text-wrap-cell">
                            <span class="badge bg-label-primary text-wrap mb-1">{{ $t->bidang }}</span><br>
                            <small class="text-muted">Model: {{ ucfirst($t->model ?? 'Standar') }}</small>
                        </td>

                        <!-- Kolom Rincian Sesi -->
                        <td class="text-wrap-cell">
                            <ul class="list-unstyled mb-0 small">
                                @forelse($t->schedules as $s)
                                    <li class="mb-2 pb-1 border-bottom border-light">
                                        <div class="d-flex align-items-start">
                                            <i class="bx bx-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                                            <div>
                                                <strong>{{ \Carbon\Carbon::parse($s->date)->translatedFormat('d M') }} ({{ $s->start_time }} - {{ $s->end_time }}):</strong><br>
                                                <span class="text-dark">{{ $s->activity }}</span>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="text-muted fst-italic">Belum ada rincian sesi.</li>
                                @endforelse
                            </ul>
                        </td>

                        <!-- Kolom Status -->
                        <td>
                            @if(now() < $t->tgl_mulai)
                                <span class="badge bg-label-info">Mendatang</span>
                            @elseif(now() >= $t->tgl_mulai && now() <= $t->tgl_selesai)
                                <span class="badge bg-label-success">Sedang Berjalan</span>
                            @else
                                <span class="badge bg-label-secondary">Selesai</span>
                            @endif
                        </td>

                        <!-- Kolom Unduh PDF -->
                        <td class="text-center">
                            <a href="{{ route('schedules.pdf', $t->id) }}" class="btn btn-sm btn-outline-danger shadow-sm" target="_blank" title="Unduh Jadwal PDF">
                                <i class="bx bxs-file-pdf me-1"></i> PDF Jadwal
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bx bx-info-circle fs-1 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-bold mb-1">Belum Ada Jadwal Mengajar</h6>
                            <p class="small text-muted mb-0">Anda belum ditugaskan mengajar pada sesi pelatihan apapun.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection