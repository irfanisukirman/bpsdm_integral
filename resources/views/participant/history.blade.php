@extends('layouts.master')

@section('title', 'Riwayat Pelatihan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Akun /</span> Riwayat Pelatihan Saya
    </h4>

    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pelatihan yang Diikuti</h5>
            <span class="badge bg-label-primary">NIP: {{ auth()->user()->nip_nik }}</span>
        </div>
        
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="40%">Nama Pelatihan</th>
                        <th width="25%">Penyelenggara</th>
                        <th width="15%">Waktu Pelaksanaan</th>
                        <th width="15%" class="text-center">Aksi</th>
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
                        <td class="text-center">
                            {{-- TOMBOL AKSI DETAIL --}}
                            <a href="{{ route('participant.training.show', $h->training->id) }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-show-alt me-1"></i> Detail
                            </a>
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
</style>
@endsection