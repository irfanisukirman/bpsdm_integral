@extends('layouts.master')

@section('title', 'Riwayat Pelatihan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 py-3 mb-3">
        <div><h4 class="fw-bold mb-1"><span class="text-muted fw-light">Akun /</span> Riwayat Pelatihan Saya</h4><p class="text-muted mb-0">Dokumentasi pelatihan yang telah diselesaikan beserta sertifikat final.</p></div>
        <span class="badge bg-label-primary px-3 py-2"><i class="bx bx-id-card me-1"></i>NIP/NIK: {{ auth()->user()->nip_nik ?: '-' }}</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><span class="avatar-initial rounded bg-label-primary p-2 d-inline-flex mb-2"><i class="bx bx-book-reader fs-4"></i></span><small class="text-muted d-block">Pelatihan Selesai</small><h3 class="fw-bold mb-0">{{ $summary['trainings'] }} <small class="fs-6 text-muted">pelatihan</small></h3></div></div></div>
        <div class="col-6 col-lg-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><span class="avatar-initial rounded bg-label-success p-2 d-inline-flex mb-2"><i class="bx bx-medal fs-4"></i></span><small class="text-muted d-block">Sertifikat Tersedia</small><h3 class="fw-bold mb-0">{{ $summary['certificates'] }} <small class="fs-6 text-muted">file</small></h3></div></div></div>
        <div class="col-12 col-lg-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><span class="avatar-initial rounded bg-label-info p-2 d-inline-flex mb-2"><i class="bx bx-calendar fs-4"></i></span><small class="text-muted d-block">Tahun Terakhir</small><h3 class="fw-bold mb-0">{{ $summary['latest_year'] ?: '-' }}</h3></div></div></div>
    </div>

    <div class="card shadow-sm overflow-hidden">
        <div class="card-header border-bottom d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div><h5 class="mb-1 fw-bold">Daftar Pelatihan yang Diikuti</h5><small class="text-muted">Menampilkan pelatihan yang administrasi dan evaluasinya telah lengkap.</small></div>
            <form method="GET" class="d-flex gap-2 history-search">
                <div class="input-group"><span class="input-group-text bg-white"><i class="bx bx-search"></i></span><input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Cari pelatihan, bidang, atau tahun..."></div>
                <button class="btn btn-primary">Cari</button>
                @if($search!=='')<a href="{{route('participant.history')}}" class="btn btn-outline-secondary" title="Reset"><i class="bx bx-reset"></i></a>@endif
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover history-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="history-number">No</th>
                        <th>Nama Pelatihan</th>
                        <th>Penyelenggara</th>
                        <th>Waktu Pelaksanaan</th>
                        <th>Status</th>
                        <th class="text-center">Sertifikat</th>
                        <th class="text-center history-action-column">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($history as $index => $h)
                    <tr>
                        <td>{{ ($history->firstItem() ?? 1) + $index }}</td>
                        <td>
                            <span class="fw-bold text-dark">{{ $h->training->nama_pelatihan }}</span><br>
                            <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $h->training->model }}</small>
                        </td>
                        <td><small>{{ $h->training->bidang }}</small></td>
                        <td>
                            <div class="d-flex flex-column">
                                <small class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($h->training->tgl_mulai)->format('d/m/Y') }}</small>
                                <small class="text-muted" style="font-size: 10px;">s.d {{ \Carbon\Carbon::parse($h->training->tgl_selesai)->format('d/m/Y') }}</small>
                            </div>
                        </td>
                        <td><span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Selesai</span></td>
                        <td class="text-center">
                            @if($h->certificate?->final_file_path)
                                <a href="{{route('participant-certificates.download',$h->certificate)}}" class="btn btn-sm btn-icon btn-success" title="Download sertifikat PDF"><i class="bx bx-download"></i></a>
                            @else
                                <button class="btn btn-sm btn-icon btn-outline-secondary" disabled title="Sertifikat belum tersedia"><i class="bx bx-lock-alt"></i></button>
                            @endif
                        </td>
                        <td class="text-center history-action-cell">
                            {{-- TOMBOL AKSI DETAIL --}}
                            <div class="d-flex justify-content-center">
                                <a href="{{ route('participant.training.show', $h->training->id) }}" class="btn btn-sm btn-primary text-nowrap">
                                    <i class="bx bx-show-alt me-1"></i>Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bx bx-history display-1 mb-3"></i>
                                <h5>{{ $search !== '' ? 'Riwayat Tidak Ditemukan' : 'Belum Ada Riwayat' }}</h5>
                                <p>{{ $search !== '' ? 'Tidak ada pelatihan yang cocok dengan pencarian.' : 'Anda belum memiliki pelatihan yang telah diselesaikan.' }}</p>
                                <a href="{{ route('participant.trainings') }}" class="btn btn-outline-primary btn-sm">Cari Pelatihan</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($history->hasPages())<div class="card-footer border-top d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2"><small class="text-muted">Menampilkan {{ $history->firstItem() }}-{{ $history->lastItem() }} dari {{ $history->total() }} riwayat</small><div>{{ $history->onEachSide(1)->links() }}</div></div>@endif
    </div>
</div>

<style>
    .table td {
        vertical-align: top;
        padding: 1rem 0.75rem !important;
    }
    .history-table {
        width: 100%;
    }
    .history-table th,
    .history-table td {
        white-space: normal;
        overflow-wrap: anywhere;
    }
    .history-number {
        width: 64px;
    }
    .history-action-column,
    .history-action-cell {
        width: 130px;
        min-width: 130px;
    }
    .history-search { width: min(100%, 520px); }
    .history-action-cell {
        white-space: nowrap !important;
    }
    @media (max-width: 767.98px) {
        .history-table {
            min-width: 720px;
        }
        .history-action-column,
        .history-action-cell {
            position: sticky;
            right: 0;
            background: #fff;
            box-shadow: -6px 0 10px rgba(67, 89, 113, .06);
            z-index: 1;
        }
        .history-table thead .history-action-column {
            background: #f5f5f9;
            z-index: 2;
        }
    }
</style>
@endsection
