@extends('layouts.master')
@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Peserta /</span> Daftar Pelatihan Tersedia</h4>

<div class="row g-4">
    @foreach($trainings as $t)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-none border">
            <div class="card-body">
                <div class="badge bg-label-primary mb-2">{{ $t->bidang }}</div>
                <h5 class="card-title fw-bold text-dark">{{ $t->nama_pelatihan }}</h5>
                <p class="card-text small text-muted">
                    <i class="bx bx-calendar me-1"></i> {{ \Carbon\Carbon::parse($t->tgl_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($t->tgl_selesai)->format('d M Y') }}<br>
                    <i class="bx bx-map-pin me-1"></i> {{ $t->lokasi }}
                </p>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-label-info">Model: {{ strtoupper($t->model) }}</span>
                    <button class="btn btn-sm btn-primary">Lihat Detail</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection