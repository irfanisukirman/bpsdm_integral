@extends('layouts.master')

@section('title', 'Daftar Kehadiran Pelatihan')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Pelaksanaan /</span> Daftar Kehadiran Pelatihan
</h4>

<div class="card">
    <h5 class="card-header">Pilih Pelatihan untuk Kelola Absensi</h5>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nama Pelatihan</th>
                    <th>Penyelenggara</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($trainings as $t)
                <tr>
                    <td><strong>{{ $t->nama_pelatihan }}</strong></td>
                    <td><small>{{ $t->bidang }}</small></td>
                    <td>{{ \Carbon\Carbon::parse($t->tgl_mulai)->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('attendance.index', $t->id) }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-user-check me-1"></i> Kelola Absensi
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">Tidak ada data pelatihan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection