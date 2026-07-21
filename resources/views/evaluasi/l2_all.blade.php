@extends('layouts.master')

@section('title', 'Daftar Pelatihan - Level 2')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Evaluasi /</span> Level 2: Learning (Pre/Post Test)
</h4>

<div class="card">
    <h5 class="card-header">Pilih Pelatihan untuk Input Nilai</h5>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Pelatihan</th>
                    <th>Penyelenggara</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($trainings as $t)
                <tr>
                    <td>
                        <strong>{{ $t->nama_pelatihan }}</strong><br>
                        <small class="text-muted text-uppercase">{{ $t->model }}</small>
                    </td>
                    <td>
                        <small class="text-wrap" style="width: 250px; display: block; line-height: 1.2">
                            {{ $t->bidang }}
                        </small>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($t->tgl_mulai)->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('evall2.index', $t->id) }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-edit-alt me-1"></i> Kelola Nilai
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data pelatihan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection