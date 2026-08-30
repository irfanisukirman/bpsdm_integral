@extends('layouts.master')

@section('title', 'Riwayat Pelatihan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Akun /</span> Riwayat Pelatihan Saya
    </h4>

    <div class="card shadow-sm overflow-hidden">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pelatihan yang Diikuti</h5>
            <span class="badge bg-label-primary">NIP: {{ auth()->user()->nip_nik }}</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover history-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="history-number">No</th>
                        <th>Nama Pelatihan</th>
                        <th>Penyelenggara</th>
                        <th>Waktu Pelaksanaan</th>
                        <th class="text-center history-action-column">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($history as $index => $h)
                    <tr>
                        <td>{{ $index + 1 }}</td>
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
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bx bx-history display-1 mb-3"></i>
                                <h5>Belum Ada Riwayat</h5>
                                <p>Anda belum terdaftar dalam pelatihan apapun.</p>
                                <a href="{{ route('participant.trainings') }}" class="btn btn-outline-primary btn-sm">Cari Pelatihan</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
