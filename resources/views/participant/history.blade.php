@extends('layouts.master')
@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Akun /</span> Riwayat Pelatihan Saya</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pelatihan yang Diikuti (NIP: {{ auth()->user()->nip_nik }})</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nama Pelatihan</th>
                    <th>Penyelenggara</th>
                    <th>Waktu</th>
                    <th>Sertifikat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $h)
                <tr>
                    <td><strong>{{ $h->training->nama_pelatihan }}</strong></td>
                    <td>{{ $h->training->bidang }}</td>
                    <td>{{ \Carbon\Carbon::parse($h->training->tgl_mulai)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge bg-label-success">Tersedia</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <i class="bx bx-info-circle h1 d-block mb-2 text-muted"></i>
                        <p>Belum ada riwayat pelatihan yang tercatat untuk NIP Anda.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection