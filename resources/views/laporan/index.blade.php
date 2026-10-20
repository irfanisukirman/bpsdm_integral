@extends('layouts.master')

@section('title', 'Laporan')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Laporan /</span> Laporan ASN P3K-PW
</h4>

<div class="card shadow-sm border-0">
    <div class="card-body p-4 p-md-5 text-center">
        <i class="bx bx-file text-primary" style="font-size: 3rem;"></i>
        <h5 class="fw-bold mt-3 mb-2">Halaman Laporan</h5>
        <p class="text-muted mb-4">
            Isi dan format laporan masih menunggu konfirmasi, jadi halaman ini masih kosong sementara.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <span class="badge bg-label-primary">{{ auth()->user()->instansi ?: 'Instansi belum diisi' }}</span>
            <span class="badge bg-label-success">{{ auth()->user()->status_kepegawaian ?: '-' }}</span>
        </div>
    </div>
</div>
@endsection
